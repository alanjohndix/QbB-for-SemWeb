<?php
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'constants.inc.php';
require_once MORIARTY_DIR . 'httprequest.class.php';
require_once MORIARTY_TEST_DIR . 'fakecredentials.class.php';
require_once MORIARTY_TEST_DIR . 'fakecache.class.php';

class HttpRequestTest extends PHPUnit_Framework_TestCase {

  function test_set_accept() {
    $req = new HttpRequest("GET", "http://example.org/");
    $req->set_accept("text/plain");

    $this->assertTrue( in_array('Accept: text/plain', $req->get_headers() ) );
  }

  function test_set_accept_overwrites_existing_value() {
    $req = new HttpRequest("GET", "http://example.org/");
    $req->set_accept("text/plain");
    $req->set_accept("application/xml");

    $this->assertFalse( in_array('Accept: text/plain', $req->get_headers() ) );
  }


  function test_set_content_type() {
    $req = new HttpRequest("GET", "http://example.org/");
    $req->set_content_type("text/plain");

    $this->assertTrue( in_array('Content-Type: text/plain', $req->get_headers() ) );
  }

  function test_set_content_type_overwrites_existing_value() {
    $req = new HttpRequest("GET", "http://example.org/");
    $req->set_content_type("text/plain");
    $req->set_content_type("application/xml");

    $this->assertFalse( in_array('Content-Type: text/plain', $req->get_headers() ) );
  }

  function test_set_body_does_not_set_content_length_since_this_breaks_http_digest_auth() {
    $req = new HttpRequest("GET", "http://example.org/");
    $req->set_body("now is the time");

    $this->assertFalse( in_array('Content-Length: 15', $req->get_headers() ) );
  }

  function test_parse_response_parses_all_responses() {
    $req = new HttpRequest("GET", "http://example.org/");

    $server_response = 'HTTP/1.1 401 Unauthorized
Via: 1.1 DORY
Connection: Keep-Alive
Proxy-Support: Session-Based-Authentication
Connection: Proxy-Support
Content-Length: 12
Date: Wed, 03 Oct 2007 00:15:59 GMT
Content-Type: text/plain; charset=UTF-8
WWW-Authenticate: Digest realm=&quot;bigfoot&quot;, domain=&quot;null&quot;, nonce=&quot;8Q84YxUBAABJP5W9FaNm7Fli2QGGO99o&quot;, algorithm=MD5, qop=&quot;auth&quot;

HTTP/1.1 200 OK
Via: 1.1 DORY
Connection: Keep-Alive
Proxy-Connection: Keep-Alive
Content-Length: 2103
Date: Wed, 03 Oct 2007 00:15:59 GMT
content-type: text/html; charset=UTF-8
Server: Bigfoot/5.282.18209
Cache-Control: max-age=7200, must-revalidate

foo';

    list($response_code,$response_headers,$response_body) = $req->parse_response($server_response);
    $this->assertEquals( 200, $response_code);

  }


  function test_constructor_can_set_auth_from_credentials() {
    $req = new HttpRequest("GET", "http://example.org/", new FakeCredentials());
    $this->assertEquals( "user", $req->credentials->get_username() );
    $this->assertEquals( "pwd", $req->credentials->get_password() );
  }


  function test_response_is_cacheable_is_false_when_cache_control_private() {
    $response = new HttpResponse(200);
    $response->headers['cache-control'] = "private";

    $this->assertFalse( $response->is_cacheable() );
  }

  function test_response_is_cacheable_is_true_when_no_cache_control_header() {
    $response = new HttpResponse(200);
    $this->assertTrue( $response->is_cacheable() );
  }

  function test_response_is_cacheable_is_false_when_cache_control_no_cache() {
    $response = new HttpResponse(200);
    $response->headers['cache-control'] = "no-cache";

    $this->assertFalse( $response->is_cacheable() );
  }

  function test_response_is_cacheable_is_false_when_cache_control_no_store() {
    $response = new HttpResponse(200);
    $response->headers['cache-control'] = "no-store";

    $this->assertFalse( $response->is_cacheable() );
  }


  function test_cache_id() {
    $request = new HttpRequest('GET', 'http://example.org/');
    $request->set_accept('*/*');
    
    $expected_id = md5('<http://example.org/>*/*'); 
    
    $this->assertEquals( $expected_id, $request->cache_id($request));
    
  }

  function test_cache_id_adds_missing_directory_separator() {
    $request = new HttpRequest('GET', 'http://example.org/');
    $request->set_accept('*/*');
    
    $expected_id = md5('<http://example.org/>*/*'); 
    
    $this->assertEquals( $expected_id, $request->cache_id($request));
  }


  function test_cache_id_normalises_accept_header() {
    $request1 = new HttpRequest('GET', 'http://example.org/');
    $request1->set_accept('text/html,application/xml');

    $request2 = new HttpRequest('GET', 'http://example.org/');
    $request2->set_accept('application/xml,text/html');

    $this->assertEquals($request1->cache_id($request1), $request2->cache_id($request2));
  }
  
  function test_cache_id_uses_request_body() {
    $request1 = new HttpRequest('GET', 'http://example.org/');
    $request1->set_accept('*/*');
    $request1->set_body('foo');
    
    $request2 = new HttpRequest('GET', 'http://example.org/');
    $request2->set_accept('*/*');
    $request2->set_body('bar');

    $this->assertNotEquals($request1->cache_id($request1), $request2->cache_id($request2));
   
  }
  
  function test_cache_entry_exists_and_no_validation_required() {
    $response = new HttpResponse();
    $response->status_code = 200;
    $response->body = 'scooby';

    $request = new HttpRequest('GET', 'http://example.org/');

    $cache = new FakeCache();
    $cache->save($response, $request->cache_id());

    $request->set_cache($cache);    
    $request->always_validate_cache(FALSE);
    
    $new_response = $request->execute();  
    
    $this->assertEquals('scooby', $new_response->body);
  
    
  }
  
}
?>
