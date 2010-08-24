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

class mdl_remotes 
{
/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Convert messages into XML
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function make_messages_xml($username, $pub_key, $priv_key,
        $bio, $avatar, $profile, $messages, $message_pingback,
        $relation_pingback)
    {
    // Validate paramiters
        validate_username($username);
        validate_bio($bio);
        validate_avatar($avatar);
        validate_url($profile);
        validate_url($message_pingback);
        validate_url($relation_pingback);

        validate_pub_key($pub_key);
        validate_priv_key($priv_key);

    // Generate digital signature
        $signature_str = PROTOCOL_VERSION . $username . $bio . $avatar . $profile .
            $message_pingback . $relation_pingback;

        foreach($messages as $message)
            $signature_str .= ($message['Time'] . $message['Message']);

        $pkeyid = @openssl_get_privatekey($priv_key);
        @openssl_sign($signature_str, $signature, $pkeyid); 
        @openssl_free_key($pkeyid);

    // Make XML
        $messages_XML = new SimpleXMLElement("<messages></messages>");
        $messages_XML->addChild('protocol_version', PROTOCOL_VERSION);

        $head = $messages_XML->addChild('head');

        $head->addChild('by_user',      base64_encode($username));
        $head->addChild('user_pub_key', base64_encode($pub_key));
        $head->addChild('data_sig',     base64_encode($signature));
        $head->addChild('user_bio',     base64_encode($bio));
        $head->addChild('user_avatar',  base64_encode($avatar));
        $head->addChild('user_profile', base64_encode($profile));

        $head->addChild('message_pingback',  base64_encode($message_pingback));
        $head->addChild('relation_pingback', base64_encode($relation_pingback));

        foreach($messages as $message)
        {
            $element = $messages_XML->addChild('message');
            $element -> addChild('time',    base64_encode($message['Time'])); 
            $element -> addChild('message', base64_encode($message['Message'])); 
        }

        return $messages_XML->asXML();
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Fetch the messeges of a remote user, test paramiter alows
 * the method to be tested without hitting the network.
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function get_message_stream($remote_url, $test = "",
        $check_signiture = true)
    {
        if($test == "")
            $xml = $this->http_request($remote_url);
        else
            $xml = $test;

        if($test == 'User does not exist on this node')
            throw new no_such_user_exception();

        $parsed_xml = @simplexml_load_string($xml);

        if(!$parsed_xml)
            throw new malformed_xml_exception();

    // Protocol version number must be numeric and have a decimal point
        if(!preg_match("/[0-9]+\.[0-9]+/", $parsed_xml->protocol_version))
            throw new invalid_protocol_version_exception();

    // check protocol version tag exitsts
        if($parsed_xml->protocol_version > PROTOCOL_VERSION)
            throw new messages_from_the_future_exception();

        $parsed_xml->head->by_user           = base64_decode($parsed_xml->head->by_user);
        $parsed_xml->head->user_pub_key      = base64_decode($parsed_xml->head->user_pub_key);
        $parsed_xml->head->user_bio          = base64_decode($parsed_xml->head->user_bio);
        $parsed_xml->head->user_avatar       = base64_decode($parsed_xml->head->user_avatar);
        $parsed_xml->head->user_profile      = base64_decode($parsed_xml->head->user_profile);
        $parsed_xml->head->message_pingback  = base64_decode($parsed_xml->head->message_pingback);
        $parsed_xml->head->relation_pingback = base64_decode($parsed_xml->head->relation_pingback);

        for($i = 0; $i < count($parsed_xml->message); $i ++)
        {
            $parsed_xml->message[$i]->time    = base64_decode($parsed_xml->message[$i]->time);
            $parsed_xml->message[$i]->message = base64_decode($parsed_xml->message[$i]->message);
        }

    // Varify stream signature
        if($check_signiture == true)
        {
            $signature_str = $parsed_xml->protocol_version . $parsed_xml->head->by_user .
                $parsed_xml->head->user_bio . $parsed_xml->head->user_avatar .
                $parsed_xml->head->user_profile . $parsed_xml->head->message_pingback .
                $parsed_xml->head->relation_pingback;

            foreach($parsed_xml->message as $message)
                $signature_str .= ($message->time . $message->message);

            validate_pub_key($parsed_xml->head->user_pub_key);

            $pubkeyid = openssl_get_publickey($parsed_xml->head->user_pub_key);
            $result = openssl_verify($signature_str, base64_decode($parsed_xml->head->data_sig), $pubkeyid); 
            openssl_free_key($pubkeyid);

            if($result != 1)
                throw new stream_signature_error_exception();
        }
       
    // Varify user info
        validate_username($parsed_xml->head->by_user);
        validate_bio($parsed_xml->head->user_bio);
        validate_avatar($parsed_xml->head->user_avatar);

    // Validate URL's
        validate_url($parsed_xml->head->user_profile);
        validate_url($parsed_xml->head->message_pingback);
        validate_url($parsed_xml->head->relation_pingback);

    // Check that all URL's point to the same host name
        $remote   = parse_url($remote_url);

        $profile  = parse_url($parsed_xml->head->user_profile);
        $message  = parse_url($parsed_xml->head->message_pingback);
        $relation = parse_url($parsed_xml->head->relation_pingback);

        if( $profile['host']  != $remote['host'] ||
            $message['host']  != $remote['host'] ||
            $relation['host'] != $remote['host'])
            throw new exception('Invalid message stream');

        return $parsed_xml;
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    * Send a ping
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function send_ping($url, $type, $user, $pub_key, $priv_key,
        $data, $return_xml = false)
    {
        validate_username($user);
        validate_pub_key($pub_key);
        validate_priv_key($priv_key);

    // Genereate signature
        $signature_str = PROTOCOL_VERSION . $type . $user . $data;

        $pkeyid = @openssl_get_privatekey($priv_key);
        @openssl_sign($signature_str, $signature, $pkeyid); 
        @openssl_free_key($pkeyid);

    // Generate XML
        $ping_XML = new SimpleXMLElement("<ping></ping>");

        $ping_XML->addChild('protocol_version', PROTOCOL_VERSION);
        $ping_XML->addChild('user_pub_key',     base64_encode($pub_key));
        $ping_XML->addChild('data_sig',         base64_encode($signature));
        $ping_XML->addChild('type',             base64_encode($type));
        $ping_XML->addChild('user',             base64_encode($user));
        $ping_XML->addChild('data',             base64_encode($data));

        $ping_XML = $ping_XML->asXML();

        if($return_xml == true)
            return $ping_XML;

    // send ping XML using POST
        return $this->http_request($url, array('data' => $ping_XML));
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Decode ping message
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function decode_ping($ping, $varify_signature = true)
    {
        $parsed_xml = @simplexml_load_string($ping);

        if(!$parsed_xml)
            throw new malformed_xml_exception();

        if(!preg_match("/[0-9]+\.[0-9]+/", $parsed_xml->protocol_version))
            throw new invalid_protocol_version_exception();

        if($parsed_xml->protocol_version > PROTOCOL_VERSION)
            throw new messages_from_the_future_exception();

    // Decode
        $parsed_xml->user_pub_key = base64_decode($parsed_xml->user_pub_key);
        $parsed_xml->type = base64_decode($parsed_xml->type);
        $parsed_xml->user = base64_decode($parsed_xml->user);
        $parsed_xml->data = base64_decode($parsed_xml->data);

        validate_pub_key($parsed_xml->user_pub_key);

    // Varify signature
        if($varify_signature == true)
        {
            $this->varify_ping_signature($parsed_xml, $parsed_xml->user_pub_key);
        }


    // Validate data
        if(!preg_match("/[a-zA-Z]+/", $parsed_xml->type))
            throw new exception("Type is invalid");

        validate_username($parsed_xml->user);

        return $parsed_xml;
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Varify a ping signature
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function varify_ping_signature($parsed_xml, $pub_key)
    {
        $signature_str = $parsed_xml->protocol_version . $parsed_xml->type .
            $parsed_xml->user . $parsed_xml->data;
        
        $pubkeyid = openssl_get_publickey($pub_key);
        $result = openssl_verify($signature_str, base64_decode($parsed_xml->data_sig), $pubkeyid); 
        openssl_free_key($pubkeyid);

        if($result != 1)
            throw new stream_signature_error_exception();
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Generate responce XML for a ping sequance
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function make_ping_response($state, $error_msg = "")
    {
        if(!($state == 'success' || $state == 'fail'))
            throw new invalid_state_exception();

        $ping_responce_XML = new SimpleXMLElement("<response></response>");

        $ping_responce_XML->addChild('protocol_version', PROTOCOL_VERSION);
        $ping_responce_XML->addChild('state', $state);
        $ping_responce_XML->addChild('error_msg', $error_msg);

        return $ping_responce_XML->asXML();
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Decode a ping responce
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function decode_ping_response($response)
    {
        $parsed_xml = @simplexml_load_string($response);

        if(!$parsed_xml)
            throw new malformed_xml_exception();

    // Protocol version number must be numeric and have a decimal point
        if(!preg_match("/[0-9]+\.[0-9]+/", $parsed_xml->protocol_version))
            throw new invalid_protocol_version_exception();

        if($parsed_xml->protocol_version > PROTOCOL_VERSION)
            throw new messages_from_the_future_exception();

        if(!($parsed_xml->state == "success" || $parsed_xml->state == "fail"))
            throw new invalid_state_exception();

        return $parsed_xml;
    }

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Proform a GET or POST HTTP request
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
    function http_request($url, $post_data = array())
    {
        validate_url($url);

    // Convert the assosiative data array into key value string
        $first    = true;
        $data_str = "";

        foreach($post_data as $key => $value)
        {
            if($first == false)
                $data_str .= "&";

            $data_str .= "$key=" . urlencode($value);
            $first     = false;
        }

    // If no data was provided, do GET request
        if($post_data == array())
        {
            $resource = @fopen($url, 'rb');
        }
    // Else do POST
        else
        {
            $params = array(
                'http' => array(
                    'method'  => 'POST',
                    'content' => $data_str));

            $ctx      = stream_context_create($params);
            $resource = @fopen($url, 'rb', false, $ctx);
        }

        if (!$resource)
            throw new dead_url_exception();

        $response = @stream_get_contents($resource);
        if ($response === false)
            throw new dead_url_exception();

        return $response;
    }
}

// Exceptions
class malformed_xml_exception extends exception { }
class invalid_protocol_version_exception extends exception { }
class messages_from_the_future_exception extends exception { };
class invalid_state_exception extends exception { };
class dead_url_exception extends exception { };
class stream_signature_error_exception extends exception { };
