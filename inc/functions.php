<?php

/*
 * Custom defined functions
 */

function shopify_call($token, $shop, $api_endpoint, $query = array(), $method = 'GET', $request_headers = array(), $post_json = '') {

    // Build URL
    $url = "https://" . $shop . ".myshopify.com" . $api_endpoint;
    if (!is_null($query) && in_array($method, array('GET', 'DELETE')))
        $url = $url . "?" . http_build_query($query);

    // Configure cURL
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, TRUE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 3);
    // curl_setopt($curl, CURLOPT_SSLVERSION, 3);
//    curl_setopt($curl, CURLOPT_USERAGENT, 'My New Shopify Ap  p v.1');
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

    // Setup headers
    $request_headers[] = "";
    if (!is_null($token))
        $request_headers[] = "X-Shopify-Access-Token: " . $token;
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

    if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
        if (is_array($query)) {
            $query = http_build_query($query);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        }
    }
    if ($method != 'GET' && in_array($method, array('POST', 'PUT'))) {
        if (is_array($post_json)) {
            $post_json = json_encode($post_json);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_json);
        }
    }

    // Send request to Shopify and capture any errors
    $response = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_message = curl_error($curl);

    // Close cURL to be nice
    curl_close($curl);

    // Return an error is cURL has a problem
    if ($error_number) {
        return $error_message;
    } else {

        // No error, return Shopify's response by parsing out the body and the headers
        $response = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

        // Convert headers into an array
        $headers = array();
        $header_data = explode("\n", $response[0]);
        $headers['status'] = $header_data[0]; // Does not contain a key, have to explicitly set
        array_shift($header_data); // Remove status, we've already set it above
        foreach ($header_data as $part) {
            $h = explode(":", $part);
            $headers[trim($h[0])] = trim($h[1]);
        }

        // Return headers and Shopify's response
        return array('headers' => $headers, 'response' => $response[1]);
    }
}

function get_domain($url) {
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    }
    return false;
}

function get_host($url) {
    $pieces = parse_url($url);
    return isset($pieces['host']) ? $pieces['host'] : '';
}