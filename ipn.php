<?php

define('WP_USE_THEMES', false);
require('../../../wp-blog-header.php');
header('HTTP/1.1 200 OK');
//echo "2";
$sandbox = get_option("eds_use_sandbox");

if (!isset($_POST['txn_id'])) {
    die();
}

if (!empty($sandbox)) {
    $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
} else {
    $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
}

$validate_ipn = array('cmd' => '_notify-validate');
$validate_ipn += stripslashes_deep($_POST);

// Send back post vars to paypal
$params = array(
    'body' => $validate_ipn,
    'sslverify' => false,
    'timeout' => 60,
    'httpversion' => '1.0.0',
    'compress' => false,
    'decompress' => false,
    'user-agent' => 'easy-digital-shop'
);


$response = wp_remote_post($paypal_url, $params);

// assign posted variables to local variables
$payment_status = $_POST['payment_status'];
$item_number = $_POST['item_number'];
$payment_amount = $_POST['mc_gross'];
$payment_currency = $_POST['mc_currency'];
$receiver_email = $_POST['receiver_email'];
$payer_email = $_POST['payer_email'];
$payer_first_name = $_POST['first_name'];
$payer_last_name = $_POST['last_name'];
$item_name = $_POST['item_name'];
$txn_id = $_POST['txn_id'];
$sender_address = strtolower(get_option('eds_paypal_email'));
$sitename = strtolower($_SERVER['SERVER_NAME']);

if (substr($sitename, 0, 4) == 'www.') {
    $sitename = substr($sitename, 4);
}

$eds_sender_email = get_option('eds_sender_email');
if (isset($eds_sender_email) && !empty($eds_sender_email)) {
    $sender_address = $eds_sender_email;
} elseif (substr($sender_address, strpos($sender_address, '@') + 1) != $sitename) {
    $sender_address = "shop@" . $sitename;
}

$eds_sender_name = get_option('eds_sender_name');

if (isset($eds_sender_name) && !empty($eds_sender_name)) {
    $mail_From = "From: " . $eds_sender_name . " <" . $sender_address . ">";
} else {
    $mail_From = "From: Shop <" . $sender_address . ">";
}


if (!is_wp_error($response) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 && strstr($response['body'], 'VERIFIED')) {

    $status = "";
    $post = get_post($item_number);
    $sql = "SELECT id, status, hash FROM {$wpdb->prefix}easydigitalshop WHERE txn_id = %s";
    $txn = $wpdb->get_row($wpdb->prepare($sql, array($txn_id)));

    if (is_null($post)) {
        $status = "invalid post id / item number: " . $item_number;
    } elseif ($payment_status != "Completed") {
        $status = "invalid payment status: " . $payment_status;
    } elseif (strtolower($receiver_email) != strtolower(get_option("eds_paypal_email"))) {
        $status = "invalid receiver email: " . $receiver_email;
    } elseif (floatval($payment_amount) != floatval(get_post_meta($post->ID, "eds_price", true))) {
        $status = "invalid price: " . $payment_amount;
    } elseif ($payment_currency != get_option('eds_currency_code')) {
        $status = "invalid currency: " . $payment_currency;
    } else {
        $status = "VERIFIED";
    }

    if (empty($txn)) {

        $hash = md5(uniqid(mt_rand(), true) . $item_number . $payer_email);
        $sql = "INSERT INTO {$wpdb->prefix}easydigitalshop (postid, hash, txn_id, price, status, ipn_date, first_name, last_name, payer_email) VALUES(%d, %s, %s, %f, %s, NOW(), %s, %s, %s)";
        $wpdb->query($wpdb->prepare($sql, array($item_number, $hash, $txn_id, $payment_amount, $status, $payer_first_name, $payer_last_name, $payer_email)));
    } else {

        $hash = $txn->hash;
        $sql = "UPDATE {$wpdb->prefix}easydigitalshop SET postid = %d, price = %f, status = %s, ipn_date = NOW(), first_name = %s, last_name = %s, payer_email = %s WHERE id = %d";
        $r = $wpdb->query($wpdb->prepare($sql, array($item_number, $payment_amount, $status, $payer_first_name, $payer_last_name, $payer_email, $txn->id)));
    }

    if ($status == "VERIFIED") {

        $blogname = get_option('blogname');
        $downloadlink = get_option('eds_shortlink') ? get_option('siteurl') . "/eds/" . $hash : WP_PLUGIN_URL . "/easy-digital-shop/download.php?h=" . $hash;
        $mail_Subject = get_option('eds_emailsubject');
        $search = array('$first_name',
            '$last_name',
            '$post_title',
            '$downloadlink',
            '$blogname');
        $replace = array($payer_first_name,
            $payer_last_name,
            $post->post_title,
            $downloadlink,
            $blogname);

        $mail_Body = str_replace($search, $replace, get_option('eds_emailtext'));
        wp_mail($payer_email, $mail_Subject, $mail_Body, $mail_From);
        wp_mail(get_option('efs_paypal_email'), $mail_Subject, $mail_Body, $mail_From);
    }
} else {
    $mail_Subject = "INVALID IPN";
    $mail_Body = "Something went wrong:\n";
    foreach ($_POST as $key => $value) {
        $mail_Body .= $key . " = " . $value . "\n";
    }
    wp_mail(get_option('eds_paypal_email'), $mail_Subject, $mail_Body, $mail_From);
}
?>