<?php

/*
 * Shopify will redirect to this page after the app installation
 * Here we save the shop data in DB and activate the webhooks then redirect user to app home page
 */

require_once __DIR__."/config.php";
require_once __DIR__."/inc/functions.php";

$params = $_GET;
$hmac = $_GET['hmac'];

$params = array_diff_key($params, array('hmac' => ''));
ksort($params);
$computed_hmac = hash_hmac('sha256', http_build_query($params), API_SECRET_KEY);

if (hash_equals($hmac, $computed_hmac)) {

    $query = array(
        "client_id" => API_KEY,
        "client_secret" => API_SECRET_KEY,
        "code" => $params['code']
    );

    $access_token_url = "https://" . $params['shop'] . "/admin/oauth/access_token";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $access_token_url);
    curl_setopt($ch, CURLOPT_POST, count($query));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($query));
    $result = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($result, true);
    $access_token = $result['access_token'];
    
    if(!empty($result) && isset($result['access_token'])) {
        $query = "INSERT INTO `be_stores` SET `shop_url`='{$params['shop']}',`access_token`='{$result['access_token']}',`scope`='{$result['scope']}',`installed_at`='".date('Y-m-d H:i:s')."',`status`='Active'";
        if(mysqli_query($link, $query)) {
            $store_id = mysqli_insert_id($link);

            /*
             * Webhooks
             */

            $sc_result = shopify_call(
                $access_token,
                str_replace(".myshopify.com", "", $params['shop']),
                "/admin/api/2022-10/webhooks.json",
                NULL,
                "POST",
                array('Content-Type: application/json'),
                array(
                    "webhook" => array(
                        "topic" => "app/uninstalled",
                        "address" => BASE_URL."webhooks/uninstalled.php",
                        "format" => "json"
                    )
                )
            );
            // TODO: should add logs for fail/success of the webhook.

            header("Location: https://".$params['shop']."/admin/apps/".APP_SLUG);
            die;
        }
    }
    die('An error occured please try again.');
} else {
    die('This request is NOT from Shopify!');
}
