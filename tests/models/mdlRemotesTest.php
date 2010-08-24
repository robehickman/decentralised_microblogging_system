<?php
/*
 * Copyright 2010 Robert Hickman
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

require_once 'PHPUnit/Framework.php';

require_once 'config_tests.php';
require_once 'src/common.php';
require_once 'src/database.php';
require_once 'app/models/users.php';
require_once 'app/helpers/users.php';
require_once 'app/helpers/messages.php';
require_once 'app/helpers/crypto.php';
require_once 'app/models/remotes.php';

class mdlRemotesTest extends PHPUnit_Framework_TestCase 
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Misc
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    protected function get_priv_key()
    {
        return "-----BEGIN PRIVATE KEY-----
MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAN3DRc17UNVRe1sV
bdSIgC3Y/CKTPez89NLHNmLaJw8VYkdSvy4NfIf9BMSSayLIm6QXdNWIxYplwlZF
6mlVAI6PP5vbRR/qzp54U4VNoCRF12kHSn3/wBH6jFl8Ruu2NGidj9T3f13R9sD6
gngdFLuctW6qSSrkm5rp/AiAJqTDAgMBAAECgYEA1zjQ4d+wT5dI1NkzQnVHkTdp
XFTyYLIPGRFl4wI9rhHe08Gm8Zb9KS2SFwTTHWr8QoDRTvvyW6LuvLQWECwC4IkY
2Ln2h504euexq3uB0H5lRuoCnyGAhGO4Eq61a7IddONbSDFk4SQQUXMTugSO/4pf
zkAwpG+x+EXS5hoHgcECQQD9rjO+hzWE7aK8DfHokU0pje9Fx3b5YIzkthiKidoL
5q6z5HdaHKbkL2SuprNC6imB2CLriaN8utNgIGr8IdH5AkEA38pcG1P9aaEyN0Ei
tXRIsUQHVrulK8ICe/iVohfks21uLUl/eDUP2/uZAqo1vdfNXLLKVh8HglHtfHNC
BJFbmwJAS/PqXjNRXNlhjfiG42ENv1FVwIi2IHg99wRmWKRpeoK47/eJnJtThKKD
/J7AsVo2zz+NRQRSxLeTLgbGwXLG0QJAXI10SlkSFP11pyRpFmJhLe7UmipAxTgX
ju3f9ImtLAe16UTcUvqe9Hu5bEC4uSrm48+NntB2ao83iKCiTQFQwQJAYEHZrTPL
w/r42/2V0Z0QfOfsN+9v/kONfYz652FEOZRThtMcJtgSlwLzoYi3DWCb8dfQER9C
2dQi8UTDxyiX0w==
-----END PRIVATE KEY-----";
    }

    protected function get_pub_key()
    {
        return "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDdw0XNe1DVUXtbFW3UiIAt2Pwi
kz3s/PTSxzZi2icPFWJHUr8uDXyH/QTEkmsiyJukF3TViMWKZcJWReppVQCOjz+b
20Uf6s6eeFOFTaAkRddpB0p9/8AR+oxZfEbrtjRonY/U939d0fbA+oJ4HRS7nLVu
qkkq5Jua6fwIgCakwwIDAQAB
-----END PUBLIC KEY-----";
    }

    protected function get_messages()
    {
        return array(
            array(
                'Time'    => '44444444444',
                'Message' => 'Message by fred'),
            array(
                'Time'    => '44444444447',
                'Message' => 'Message 2 by fred'));
    } 

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test http_request
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_http_request_get()
    {
        $data = rand(0, 100000);

        $rmt = new mdl_remotes();
        $result = $rmt->http_request(APP_ROOT .
            "tests/models/network_helpers/test_http_request.php?d=$data");

        $this->assertEquals(sha1($data), $result);
    }

// +++++++++++++++++++++
    function test_http_request_post()
    {
        $data1 = rand(0, 100000);
        $data2 = rand(0, 100000);

        $rmt = new mdl_remotes();
        $result = $rmt->http_request(APP_ROOT .
            "tests/models/network_helpers/test_http_request.php",
            array('d1' => $data1, 'd2' => $data2));

        $this->assertEquals(sha1($data1 . $data2), $result);
    }

// +++++++++++++++++++++
    function test_http_request_invalid_url()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $result = $rmt->http_request('invalid_url');
    }

// +++++++++++++++++++++
    function test_http_request_dead_url()
    {
        $this->setExpectedException('dead_url_exception');

        $rmt = new mdl_remotes();
        $result = $rmt->http_request('http://v9dvRO7IwXszP2MVQO1SaSr.com');
    }


/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test make_messages_xml method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_make_messages_xml()
    {
        $bio = '';
        for($i = 0; $i < 160; $i ++)
            $bio .= 'あ';

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            $bio,
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            $this->get_messages(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

    // load into simplexml to check for malformed xml errors
        $parsed_xml = @simplexml_load_string($xml);

        $this->assertFalse(!$parsed_xml);

    // validate signature
        $signature_str = $parsed_xml->protocol_version . base64_decode($parsed_xml->head->by_user) .
            base64_decode($parsed_xml->head->user_bio) . base64_decode($parsed_xml->head->user_avatar) .
            base64_decode($parsed_xml->head->user_profile) . base64_decode($parsed_xml->head->message_pingback) .
            base64_decode($parsed_xml->head->relation_pingback);

        foreach($parsed_xml->message as $message)
            $signature_str .= (base64_decode($message->time) . base64_decode($message->message));


        $pubkeyid = openssl_get_publickey(base64_decode($parsed_xml->head->user_pub_key));
        $result = openssl_verify($signature_str, base64_decode($parsed_xml->head->data_sig), $pubkeyid); 
        openssl_free_key($pubkeyid);

        $this->assertEquals($result, 1);
    }

// +++++++++++++++++++++
    function test_make_messages_xml_invalid_username()
    {
        $this->setExpectedException('invalid_username_exception');
        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('ed',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');
    }

// +++++++++++++++++++++
    function test_make_messages_xml_invalid_pubkey()
    {
        $this->setExpectedException('invalid_public_key_exception');
        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            '',
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');
    }

// +++++++++++++++++++++
    function test_make_messages_xml_invalid_privkey()
    {
        $this->setExpectedException('invalid_private_key_exception');
        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            '',
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');
    }

// +++++++++++++++++++++
    function test_make_messages_xml_invalid_bio()
    {
        $this->setExpectedException('invalid_bio_exception');

        // Generate a bio that is too long, max 160
        $bio = "";

        for($i = 0; $i < 161; $i ++)
            $bio .= "あ";

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            $bio,
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');
    }

// +++++++++++++++++++++
    function test_make_messages_xml_no_bio()
    {
        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            '',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');
    }

// +++++++++++++++++++++
    function test_make_messages_xml_invalid_avatar()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            'invalid_avatar',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');
    }

// +++++++++++++++++++++
    function test_make_messages_xml_invalid_profile()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            '',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');
    }

// +++++++++++++++++++++
    function test_make_messages_xml_invalid_message_pingback()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/sue',
            array(),
            '',
            'http://localhost/relations/ping');
    }

// +++++++++++++++++++++
    function test_make_messages_xml_invalid_relation_pingback()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/sue',
            array(),
            'http://localhost/messages/ping',
            '');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test get_message_stream method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_get_message_stream_valid()
    {
        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml);
    }

// +++++++++++++++++++++
    function test_get_message_stream_nonexisting_user()
    {
        $this->setExpectedException('no_such_user_exception');

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, 'User does not exist on this node');
    }

// +++++++++++++++++++++
    function test_get_message_stream_malformed()
    {
        $this->setExpectedException('malformed_xml_exception');

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, 'invalid_xml');
    }

// +++++++++++++++++++++
    function test_get_message_stream_invalid_protocall_version()
    {
        $this->setExpectedException('invalid_protocol_version_exception');

        $xml = "<?xml version=\"1.0\"?>
        <messages>
            <protocol_version>eeeee</protocol_version>
        </messages>";

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml);
    }

// +++++++++++++++++++++
    function test_get_message_stream_msg_from_the_future()
    {
        $this->setExpectedException('messages_from_the_future_exception');

        $xml = "<?xml version=\"1.0\"?>
        <messages>
            <protocol_version>1000.0</protocol_version>
        </messages>";

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml);
    }

// +++++++++++++++++++++
    function test_get_message_stream_invalid_username()
    {
        $this->setExpectedException('invalid_username_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $xml = preg_replace('/<by_user>.+<\/by_user>/', '<by_user></by_user>', $xml);

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml, false);
    }

// +++++++++++++++++++++
    function test_get_message_stream_invalid_pub_key()
    {
        $this->setExpectedException('invalid_public_key_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $xml = preg_replace('/<user_pub_key>.+<\/user_pub_key>/',
            "<user_pub_key>invalid_public_key</user_pub_key>", $xml);

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml);
    }

// +++++++++++++++++++++
    function test_get_message_stream_invalid_bio()
    {
        $this->setExpectedException('invalid_bio_exception');

        $bio = "";

        for($i = 0; $i < 161; $i ++)
            $bio .= 'a';

        $bio = base64_encode($bio);

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $xml = preg_replace('/<user_bio>.+<\/user_bio>/', "<user_bio>$bio</user_bio>", $xml);

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml, false);
    }

// +++++++++++++++++++++
    function test_get_message_stream_no_bio()
    {
        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $xml = preg_replace('/<user_bio>.+<\/user_bio>/', "<user_bio></user_bio>", $xml);

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml, false);
    }

// +++++++++++++++++++++
    function test_get_message_stream_invalid_avatar()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $xml = preg_replace('/<user_avatar>.+<\/user_avatar>/', "<user_avatar>invalid</user_avatar>", $xml);

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml, false);
    }

// +++++++++++++++++++++
    function test_get_message_stream_invalid_profile()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $xml = preg_replace('/<user_profile>.+<\/user_profile>/', "<user_profile>invalid</user_profile>", $xml);

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml, false);
    }

// +++++++++++++++++++++
    function test_get_message_stream_invalid_message_pingback()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $xml = preg_replace('/<message_pingback>.+<\/message_pingback>/',
            "<message_pingback>invalid</message_pingback>", $xml);

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml, false);
    }

// +++++++++++++++++++++
    function test_get_message_stream_invalid_relation_pingback()
    {
        $this->setExpectedException('invalid_url_exception');

        $rmt = new mdl_remotes();
        $xml = $rmt->make_messages_xml('fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'A human called fred',
            APP_ROOT . 'media/default_avatar.jpg',
            'http://localhost/users/profile/fred',
            array(),
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        $xml = preg_replace('/<relation_pingback>.+<\/relation_pingback>/',
            "<relation_pingback>invalid</relation_pingback>", $xml);

        $rmt = new mdl_remotes();
        $rmt->get_message_stream(APP_ROOT, $xml, false);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test send_ping method
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_send_ping_valid()
    {
        $rmt = new mdl_remotes();
        $result = $rmt->send_ping(APP_ROOT .
            'tests/models/network_helpers/test_ping.php',
            'test', 'fred', $this->get_pub_key(), $this->get_priv_key(),
            'this_is_some_data');

        $this->assertEquals(sha1('test' . 'fred' .
            'this_is_some_data'), $result);
    }

// +++++++++++++++++++++
    function test_send_ping_varify_signature()
    {
        $rmt = new mdl_remotes();
        $xml = $result = $rmt->send_ping(APP_ROOT .
            'tests/models/network_helpers/test_ping.php',
            'test', 'fred', $this->get_pub_key(), $this->get_priv_key(),
            'this_is_some_data', true);


        $parsed_xml = @simplexml_load_string($xml);

        $this->assertFalse(!$parsed_xml);

    // validate signature
        $signature_str = $parsed_xml->protocol_version . base64_decode($parsed_xml->type) .
            base64_decode($parsed_xml->user) . base64_decode($parsed_xml->data);

        $pubkeyid = openssl_get_publickey(base64_decode($parsed_xml->user_pub_key));
        $result = openssl_verify($signature_str, base64_decode($parsed_xml->data_sig), $pubkeyid); 
        openssl_free_key($pubkeyid);

        $this->assertEquals($result, 1);
    }

// +++++++++++++++++++++
    function test_send_ping_invalid_username()
    {
        $this->setExpectedException('invalid_username_exception');

        $rmt = new mdl_remotes();
        $result = $rmt->send_ping(APP_ROOT .
            'tests/models/network_helpers/test_ping.php',
            'test', 'ed', $this->get_pub_key(), $this->get_priv_key(),
            'this_is_some_data');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test decode_ping message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_decode_ping_valid()
    {
        $rmt = new mdl_remotes();

        $xml = $rmt->send_ping(
            'http://localhost',
            'test',
            'fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'this is some data',
            true);

        $result = $rmt->decode_ping($xml);

        $this->assertTrue($result->type == 'test');
        $this->assertTrue($result->user == 'fred');
        $this->assertTrue($result->data == 'this is some data');
    }

// +++++++++++++++++++++
    function test_decode_ping_malformed()
    {
        $this->setExpectedException('malformed_xml_exception');

        $rmt = new mdl_remotes();
        $result = $rmt->decode_ping('invalid_xml');
    }

// +++++++++++++++++++++
    function test_decode_ping_invalid_protocol_version()
    {
        $this->setExpectedException('invalid_protocol_version_exception');

        $rmt = new mdl_remotes();

        $xml = $rmt->send_ping(
            'http://localhost',
            'test',
            'fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'this is some data',
            true);

        $xml = preg_replace('/<protocol_version>.+<\/protocol_version>/',
            '<protocol_version>1000</protocol_version>', $xml);

        $result = $rmt->decode_ping($xml, false);
    }

// +++++++++++++++++++++
    function test_decode_ping_messages_from_the_future()
    {
        $this->setExpectedException('messages_from_the_future_exception');

        $rmt = new mdl_remotes();

        $xml = $rmt->send_ping(
            'http://localhost',
            'test',
            'fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'this is some data',
            true);

        $xml = preg_replace('/<protocol_version>.+<\/protocol_version>/',
            '<protocol_version>1000.0</protocol_version>', $xml);

        $result = $rmt->decode_ping($xml, false);
    }

// +++++++++++++++++++++
    function test_decode_ping_invalid_username()
    {
        $this->setExpectedException('invalid_username_exception');

        $rmt = new mdl_remotes();

        $xml = $rmt->send_ping(
            'http://localhost',
            'test',
            'fred',
            $this->get_pub_key(),
            $this->get_priv_key(),
            'this is some data',
            true);

        $xml = preg_replace('/<user>.+<\/user>/', '<user>+_</user>', $xml);

        $result = $rmt->decode_ping($xml, false);
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test make_ping_responce message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_make_ping_responce_success()
    {
        $rmt = new mdl_remotes();
        $xml = $rmt->make_ping_response('success');

    // load into simplexml to check for malformed xml errors
        $parsed_xml = @simplexml_load_string($xml);

        $this->assertFalse(!$parsed_xml);

        $this->assertTrue($parsed_xml->state == 'success');

    }

// +++++++++++++++++++++
    function test_make_ping_responce_fail()
    {
        $rmt = new mdl_remotes();
        $xml = $rmt->make_ping_response('fail', 'error message');

    // load into simplexml to check for malformed xml errors
        $parsed_xml = @simplexml_load_string($xml);

        $this->assertFalse(!$parsed_xml);

        $this->assertTrue($parsed_xml->state == 'fail');
        $this->assertTrue($parsed_xml->error_msg == 'error message');
    }

// +++++++++++++++++++++
    function test_make_ping_responce_invalid_state()
    {
        $this->setExpectedException('invalid_state_exception');

        $rmt = new mdl_remotes();
        $rmt->make_ping_response('invalid');
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Test decode_ping_responce message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function test_decode_ping_responce_valid()
    {
        $xml = "<?xml version=\"1.0\"?>
            <response>
                <protocol_version>0.1</protocol_version>
                <state>success</state>
                <error_msg></error_msg>
            </response>";

        $rmt = new mdl_remotes();
        $result = $rmt->decode_ping_response($xml);

        $this->assertTrue($result->state == 'success');
    }

// +++++++++++++++++++++
    function test_decode_ping_responce_malformed()
    {
        $this->setExpectedException('malformed_xml_exception');

        $rmt = new mdl_remotes();
        $rmt->decode_ping_response('invalid_xml');
    }

// +++++++++++++++++++++
    function test_decode_ping_responce_messages_from_the_future()
    {
        $this->setExpectedException('messages_from_the_future_exception');

        $xml = "<?xml version=\"1.0\"?>
            <response>
                <protocol_version>1000.0</protocol_version>
                <state>success</state>
                <error_msg></error_msg>
            </response>";

        $rmt = new mdl_remotes();
        $rmt->decode_ping_response($xml);
    }

// +++++++++++++++++++++
    function test_decode_ping_responce_invalid_state()
    {
        $this->setExpectedException('invalid_state_exception');

        $xml = "<?xml version=\"1.0\"?>
            <response>
                <protocol_version>0.1</protocol_version>
                <state>invalid</state>
                <error_msg></error_msg>
            </response>";

        $rmt = new mdl_remotes();
        $rmt->decode_ping_response($xml);
    }
}
