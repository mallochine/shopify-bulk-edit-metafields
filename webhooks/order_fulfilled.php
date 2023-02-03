<?php

/*
 * This file will be executed when order is fulfilled by shop admin
 */

require_once __DIR__."/../config.php";
require_once __DIR__."/../inc/functions.php";

$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= 'From: '.SEND_EMAILS_FROM_THIS_NAME.'<'.SEND_EMAILS_USING_THIS_EMAIL.'>' . "\r\n";

$data = file_get_contents('php://input');
$data = ($data != '')? json_decode($data, TRUE) : array();

if(isset($data['fulfillment_status']) && $data['fulfillment_status'] == 'fulfilled') {
    if(!empty($data['customer'])) {

        $domain = get_host($data['order_status_url']);
        $subject = 'Give us review on Trustpilot!';

        $message = 'Dear '.$data['customer']['last_name'].',<br><br>';
        $message .= 'Give us review on Trustpilot: <a href="https://www.trustpilot.com/review/'.$domain.'">https://www.trustpilot.com/review/'.$domain.'</a><br><br>';
        $message .= 'Thank You!';

        mail($data['customer']['email'], $subject, $message, $headers);
    }
}