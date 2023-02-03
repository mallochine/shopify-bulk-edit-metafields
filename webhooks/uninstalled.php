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

$query = "DELETE FROM `be_stores` WHERE shop_url = '{$data['myshopify_domain']}'";
mysqli_query($link, $query);
