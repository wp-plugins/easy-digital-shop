<?php

define('WP_USE_THEMES', false);
require('../../../wp-blog-header.php');
status_header(200);

$sandbox = get_option("eds_use_sandbox");

// read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';

foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
    //error_log("$key=$value");
}

// post back to PayPal system to validate

$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";

// If testing on Sandbox use: 
if (!empty($sandbox)) {
    $header .= "Host: www.sandbox.paypal.com:443\r\n";
} else {
    $header .= "Host: www.paypal.com:443\r\n";
}

$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";

if (!empty($sandbox)) {
    $fp = fsockopen('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
} else {
    $fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);
}

if (!isset($_POST['txn_id'])) {
    die();
}

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

if (substr($sender_address, strpos($sender_address, '@') + 1) != $sitename) {
    $sender_address = "shop@" . $sitename;
}

if ($fp) {

    fputs($fp, $header . $req);

    while (!feof($fp)) {

        $res = fgets($fp, 1024);

        if (strcmp($res, "VERIFIED") == 0) {

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
                $mail_From = "From: Shop <" . $sender_address . ">";
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
        } elseif (strcmp($res, "INVALID") == 0) {

            $mail_From = "From: Shop <" . $sender_address . ">";
            $mail_Subject = "INVALID IPN";
            $mail_Body = "Something went wrong:\n";
            foreach ($_POST as $key => $value) {
                $mail_Body .= $key . " = " . $value . "\n";
            }

            wp_mail(get_option('eds_paypal_email'), $mail_Subject, $mail_Body, $mail_From);
        }
    }
    fclose($fp);
}
?>