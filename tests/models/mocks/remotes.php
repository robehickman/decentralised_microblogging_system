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

require_once "app/models/remotes.php";

class mck_mdl_remotes
{
    var $instance;
    var $calls;
    var $user_name;
    var $user_bio;
    var $messages;

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Misc
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_priv_key()
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

// +++++++++++++++++++++
    function get_pub_key()
    {
        return "-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDdw0XNe1DVUXtbFW3UiIAt2Pwi
kz3s/PTSxzZi2icPFWJHUr8uDXyH/QTEkmsiyJukF3TViMWKZcJWReppVQCOjz+b
20Uf6s6eeFOFTaAkRddpB0p9/8AR+oxZfEbrtjRonY/U939d0fbA+oJ4HRS7nLVu
qkkq5Jua6fwIgCakwwIDAQAB
-----END PUBLIC KEY-----";
    }

// +++++++++++++++++++++
    protected function get_messages()
    {
        return array(
            array(
                'Time'    => '44444444444',
                'Message' => 'Message'),
            array(
                'Time'    => '44444444447',
                'Message' => 'Message 2'));
    } 

// +++++++++++++++++++++
    function __construct($user_name, $user_bio, $messages = array())
    {
        $this->instance = new mdl_remotes();
        $this->calls = array();
        $this->user_name = $user_name;
        $this->user_bio = $user_bio;

        if($messages == array())
            $messages = $this->get_messages();

        $this->messages = $messages;
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Main
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/

    function get_message_stream($url)
    {
        if(!isset($this->calls['get_message_stream']))
            $this->calls['get_message_stream'] = 0;

        $this->calls['get_message_stream'] ++;

        $xml = $this->instance->make_messages_xml($this->user_name,
            $this->get_pub_key(),
            $this->get_priv_key(),
            $this->user_bio,
            APP_ROOT . 'media/default_avatar.jpg',
            "http://localhost/users/profile/$this->user_name",
            $this->messages,
            'http://localhost/messages/ping',
            'http://localhost/relations/ping');

        return $this->instance->get_message_stream($url, $xml);
    }

// +++++++++++++++++++++
    function send_ping($a, $b, $c, $d, $e, $f)
    {
        if(!isset($this->calls['send_ping']))
            $this->calls['send_ping'] = 0;

        $this->calls['send_ping'] ++;

        return $this->instance->make_ping_response('success');
    }

// +++++++++++++++++++++
    function decode_ping($ping)
    {
        if(!isset($this->calls['decode_ping']))
            $this->calls['decode_ping'] = 0;

        $this->calls['decode_ping'] ++;

        return $this->instance->decode_ping($ping);
    }

// +++++++++++++++++++++
    function make_ping_response($responce)
    {
        if(!isset($this->calls['make_ping_response']))
            $this->calls['make_ping_response'] = 0;

        $this->calls['make_ping_response'] ++;

        return $this->instance->make_ping_response($responce);
    }

// +++++++++++++++++++++
    function decode_ping_response($responce)
    {
        if(!isset($this->calls['decode_ping_response']))
            $this->calls['decode_ping_response'] = 0;

        $this->calls['decode_ping_response'] ++;

        return $this->instance->decode_ping_response($responce);
    }
}
