<?php

/*
 * This file will be executed when app is uninstalled
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__."/../config.php";
require_once __DIR__."/../inc/functions.php";

$data = file_get_contents('php://input');
$data = ($data != '')? json_decode($data, TRUE) : array();

if(empty($data)) {
    die;
}

$query = "SELECT * FROM `tr_stores` WHERE shop_url = '{$data['myshopify_domain']}'";
$result = mysqli_query($link, $query);
if(mysqli_num_rows($result)) {
    $row = mysqli_fetch_array($result);
    
    $query = "DELETE FROM `tr_webhooks` WHERE store_id = '{$row['store_id']}'";
    mysqli_query($link, $query);

    $query = "DELETE FROM `tr_stores` WHERE shop_url = '{$data['myshopify_domain']}'";
    mysqli_query($link, $query);
}
