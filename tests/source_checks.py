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
        self.assertIn('session_regenerate_id(true)', source)
        self.assertIn('SELECT user_id', source)
    def test_database_credentials_are_environment_configured(self):
        source=(ROOT/'db_connect.php').read_text()
        self.assertIn("getenv('DB_USER')",source)
        self.assertIn("set_charset('utf8mb4')",source)
        self.assertNotIn('die(',source)
    def test_mutation_guards_precede_database_connections(self):
        for name in ['logincode.php','signupcode.php','submit_post.php','delete_post.php','logout.php']:
            source=(ROOT/name).read_text()
            self.assertIn("'/security.php'",source)
            self.assertIn('require_post_request();',source)
            if "'/db_connect.php'" in source:
                self.assertLess(source.index("'/security.php'"),source.index("'/db_connect.php'"))
    def test_csrf_fields_are_in_all_post_forms(self):
        import re
        for name in ['login.php','body.php','posts.php']:
            source=(ROOT/name).read_text()
            forms=len(re.findall(r'<form\b[^>]*method=["\']post["\']',source,re.I))
            self.assertEqual(forms,source.count('csrf_field();'),name)
    def test_delete_does_not_persist_request_payload(self):
        self.assertNotIn('file_put_contents', (ROOT/'delete_post.php').read_text())
if __name__ == '__main__': unittest.main()
