<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../inc/functions.php";

$params = $_POST;
$hmac = $_POST['HMAC'];

$params = array_diff_key($params, array('HMAC' => ''));
ksort($params);
$computed_hmac = hash_hmac('sha256', http_build_query($params), API_SECRET_KEY);

if (!hash_equals($hmac, $computed_hmac)) {
  header("HTTP/1.1 401 Unauthorized");
  die('This request is NOT from Shopify!');
}

echo json_encode(array('msg' => 'Data Erased'));
