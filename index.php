<?php

/*
 * If the app is already registered with us redirect to the app home page otherwise ask to install the app
 */

require_once __DIR__."/config.php";
require_once __DIR__."/inc/functions.php";

$shop = $_GET['shop'];

$query = "SELECT * FROM `be_stores` WHERE shop_url = '$shop' AND status = 'Active'";
$result = mysqli_query($link, $query);
if(mysqli_num_rows($result)) {
    $row = mysqli_fetch_array($result);
    
    $params = $_GET;
    $hmac = $_GET['hmac'];
    $params = array_diff_key($params, array('hmac' => ''));
    ksort($params);
    $computed_hmac = hash_hmac('sha256', http_build_query($params), API_SECRET_KEY);

    if (hash_equals($hmac, $computed_hmac)) {
        $_SESSION['bemf_info'] = $params;
        $_SESSION['bemf_shop_data'] = $row;
        header("Location: " . BASE_URL."home.php?shop=$shop&session_id=".session_id());
    } else {
        die('This request is NOT from Shopify!');
    }
} else {
    $redirect_url = BASE_URL."generate_token.php";
    $install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . API_KEY . "&scope=" . APP_SCOPES . "&redirect_uri=" . urlencode($redirect_url);
    header("Location: " . $install_url);
    die();
}
