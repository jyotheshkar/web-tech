"""Source checks supplement PHP lint; they do not replace MySQL integration tests."""
from pathlib import Path
import unittest
ROOT = Path(__file__).resolve().parents[1]
class HeaderTests(unittest.TestCase):
    def test_controllers_do_not_emit_html_before_headers(self):
        for name in ['db_connect.php','logincode.php','signupcode.php','logout.php','submit_post.php','delete_post.php','posts.php','myposts.php','body.php','friends.php']:
            with self.subTest(name=name): self.assertTrue((ROOT/name).read_text().startswith('<?php'))
    def test_authentication_logic_is_preserved(self):
        source=(ROOT/'logincode.php').read_text()
        self.assertIn('password_verify(', source)
        self.assertIn('session_regenerate_id(', source)
        self.assertIn('SELECT user_id', source)
    def test_database_credentials_are_environment_configured(self):
        source=(ROOT/'db_connect.php').read_text()
        self.assertIn("getenv('DB_USER')",source)
        self.assertIn("set_charset('utf8mb4')",source)
        self.assertNotIn('die(',source)
    def test_delete_does_not_persist_request_payload(self):
        self.assertNotIn('file_put_contents', (ROOT/'delete_post.php').read_text())
if __name__ == '__main__': unittest.main()
