<?php
/*
 * This file is to define the credentials for APIs and DB
 */

error_reporting(E_ALL);
session_start();

$host = 'mysql1004.mochahost.com';
$user = 'mgtech_alex_bemf';
$password = '-Mg{tz~?xa0u';
$database = 'mgtech_alex_bemf';
$port = '3306';

$link = mysqli_connect($host, $user, $password, $database, $port);
if (!$link) {
  die("Connection failed: " . mysqli_connect_error());
}

define('BASE_URL', 'https://alexbulkeditmetafields.mgworkspace.com/');

define('API_KEY', '1035a7cca2746bacf7358311c68983b1');
define('API_SECRET_KEY', 'cf9614e6ce2a1135b75fe900ed0be356');
define('APP_SCOPES', 'read_products,write_products,read_product_listings');

define('SEND_EMAILS_USING_THIS_EMAIL', 'noreply@alexbulkeditmetafields.mgworkspace.com');
define('SEND_EMAILS_FROM_THIS_NAME', 'Bulk Edit Metafields');

define('APP_SLUG', 'bulk-edit-metafields');