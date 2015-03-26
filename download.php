<?php

require('../../../wp-blog-header.php');

$upload_dir = wp_upload_dir();
if (!defined('EDS_DIR')) {
    define('EDS_DIR', $upload_dir['basedir'] . '/easy_digital_shop_uploads');
}

if (empty($_GET['h'])) {
    die("Error 01");
}

function eds_glob($pattern) {
    return glob($pattern) ? glob($pattern) : array();
}

$easydigitalshop = $wpdb->prefix . "easydigitalshop";

$sql = "SELECT id, postid FROM $easydigitalshop WHERE hash = %s AND downloads < 3 AND ipn_date > DATE_SUB(CURDATE(), INTERVAL 30 DAY)";

$rows = $wpdb->get_results($wpdb->prepare($sql, array($_GET['h'])));

if (count($rows) == 0) {
    die("your file down limit has been exceeded");
}

$id = $rows[0]->id;

$postid = $rows[0]->postid;

$files = glob(EDS_DIR . "/post" . $postid . "_*");

if (empty($files)) {
    die("Error 03");
}

$file = "";

foreach ($files as $file) {
    if (is_file($file)) {
        break;
    }
}

if (empty($file)) {
    die("Error 04");
}

status_header(200);
header("Content-Type: application/octet-stream");
$saveas = substr(end(explode('/', $file)), 5 + (strlen($postid . "")));
header("Content-Disposition: attachment; filename=\"$saveas\"");
readfile($file);

$sql = "UPDATE $easydigitalshop SET last_download = NOW(), downloads = downloads + 1 WHERE id = " . $id;
$wpdb->query($sql);