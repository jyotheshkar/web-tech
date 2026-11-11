"""Real local PHP HTTP tests. Database-free guards must reject before SQL access."""
import base64
import http.cookiejar
import json
import os
from pathlib import Path
import re
import socket
import subprocess
import tempfile
import time
import unittest
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode
from urllib.request import HTTPCookieProcessor, Request, build_opener

ROOT = Path(__file__).resolve().parents[1]
PNG = base64.b64decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aZ1sAAAAASUVORK5CYII=')

class HttpSecurityTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.temp = tempfile.TemporaryDirectory()
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0)); port = sock.getsockname()[1]
        cls.base = f'http://127.0.0.1:{port}/'
        env = os.environ.copy(); env.pop('DB_USER', None)
        env['FRIENDZONE_TEST_MODE'] = '1'; env['SESSION_COOKIE_SECURE'] = '0'
        env['UPLOAD_DIRECTORY'] = str(Path(cls.temp.name)/'uploads')
        cls.server = subprocess.Popen(['php', '-d', f'session.save_path={cls.temp.name}', '-S', f'127.0.0.1:{port}', '-t', str(ROOT)], env=env, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        for _ in range(100):
            try:
                build_opener().open(cls.base+'login.php', timeout=1).close(); return
            except (URLError, OSError): time.sleep(0.05)
        cls.server.terminate(); cls.temp.cleanup()
        raise RuntimeError('PHP test server failed to start')
    @classmethod
    def tearDownClass(cls):
        cls.server.terminate(); cls.server.wait(timeout=5); cls.temp.cleanup()
    def setUp(self):
        self.jar = http.cookiejar.CookieJar()
        self.client = build_opener(HTTPCookieProcessor(self.jar))
    def request(self, path, data=None, headers=None):
        req=Request(self.base+path, data=data, headers=headers or {})
        try: response=self.client.open(req, timeout=5)
        except HTTPError as error: response=error
        return response.status, response.headers, response.read()
    def token(self):
        status, headers, body=self.request('login.php')
        self.assertEqual(status,200)
        tokens=re.findall(rb'name="_csrf" value="([a-f0-9]{64})"',body)
        self.assertEqual(len(tokens),2)
        self.assertEqual(tokens[0],tokens[1])
        return tokens[0].decode()
    def test_cookie_and_forms_contract(self):
        status, headers, body=self.request('login.php')
        self.assertEqual(status,200)
        cookie=headers.get('Set-Cookie','')
        self.assertIn('HttpOnly',cookie); self.assertIn('SameSite=Lax',cookie)
        self.assertEqual(headers.get('X-Content-Type-Options'),'nosniff')
        self.token()
    def test_mutations_reject_missing_or_array_token_before_database(self):
        self.token()
        for route in ['logincode.php','signupcode.php','submit_post.php','delete_post.php','posts.php','logout.php']:
            for data in [b'action=test',b'_csrf[]=bad']:
                status,_,body=self.request(route,data,{'Content-Type':'application/x-www-form-urlencoded'})
                self.assertEqual(status,403,(route,body))
                self.assertNotIn(b'Database',body)
    def test_mutation_endpoints_are_post_only(self):
        for route in ['logincode.php','signupcode.php','submit_post.php','delete_post.php','logout.php']:
            status,headers,_=self.request(route)
            self.assertEqual(status,405,route);self.assertEqual(headers.get('Allow'),'POST')
    def test_valid_token_does_not_bypass_authentication(self):
        token=self.token()
        status,_,_=self.request('submit_post.php',urlencode({'_csrf':token,'postContent':'hello'}).encode())
        self.assertEqual(status,401)
        status,_,body=self.request('delete_post.php',b'{"post_id":1}',{'Content-Type':'application/json','X-CSRF-Token':token})
        self.assertEqual(status,401)
        self.assertFalse(json.loads(body)['success'])
    def test_valid_http_upload_is_reencoded_and_privately_served(self):
        _,_,body=self.request('tests/upload_harness.php')
        token=json.loads(body)['token']
        boundary='friendzone-test-boundary'
        marker=b'<?php echo "SHOULD_NOT_SURVIVE_REENCODING"; ?>'
        body=(f'--{boundary}\r\nContent-Disposition: form-data; name="_csrf"\r\n\r\n{token}\r\n'
              f'--{boundary}\r\nContent-Disposition: form-data; name="fixture"; filename="evil.php.png"\r\nContent-Type: text/plain\r\n\r\n').encode()+PNG+marker+f'\r\n--{boundary}--\r\n'.encode()
        status,_,response=self.request('tests/upload_harness.php',body,{'Content-Type':f'multipart/form-data; boundary={boundary}'})
        self.assertEqual(status,200,response)
        image=json.loads(response)['image']
        self.assertRegex(image,r'^media\.php\?file=[a-f0-9]{32}\.png$')
        status,headers,payload=self.request(image)
        self.assertEqual(status,200);self.assertEqual(headers.get('Content-Type'),'image/png')
        self.assertTrue(payload.startswith(b'\x89PNG'))
        self.assertNotIn(marker,payload)
        outsider=build_opener()
        with self.assertRaises(HTTPError) as caught: outsider.open(self.base+image)
        self.assertEqual(caught.exception.code,401)
    def test_media_denies_unauthenticated_requests(self):
        status,_,_=self.request('media.php?file='+'a'*32+'.png')
        self.assertEqual(status,401)

if __name__ == '__main__': unittest.main()
