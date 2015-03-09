<?php

/**
 * @class       Easy_Digital_Shop_Activator
 * @version	1.0.0
 * @package	min-max-quantities-for-woocommerce
 * @category	Class
 * @author      johnny-manziel <jmkaila@gmail.com>
 */
class Easy_Digital_Shop_Activator {

    /**
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;
        $eds_db_version = 1.0;
        self::create_files();
        $table_name = $wpdb->prefix . "easydigitalshop";


        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            $sql = "CREATE TABLE " . $table_name . "  (
				`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`postid` BIGINT( 20 ) NOT NULL ,
				`hash` VARCHAR( 255 ) NOT NULL ,
				`txn_id` VARCHAR( 255 ) NOT NULL ,
				`price` FLOAT( 10, 2 ) NOT NULL ,
				`status` VARCHAR( 255 ) NOT NULL ,
				`ipn_date` DATETIME NOT NULL ,
				`first_name` VARCHAR( 255 ) NOT NULL ,
				`last_name` VARCHAR( 255 ) NOT NULL ,
				`payer_email` VARCHAR( 255 ) NOT NULL ,
				`downloads` INT( 11 ) NOT NULL DEFAULT '0' ,
				`last_download` DATETIME NULL 
				) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            add_option("eds_db_version", $eds_db_version);
        }

        $installed_ver = get_option("eds_db_version");

        if ($installed_ver != $eds_db_version) {

            $sql = "CREATE TABLE " . $table_name . "  (
				`id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`postid` BIGINT( 20 ) NOT NULL ,
				`hash` VARCHAR( 255 ) NOT NULL ,
				`txn_id` VARCHAR( 255 ) NOT NULL ,
				`status` VARCHAR( 255 ) NOT NULL ,
				`price` FLOAT( 10, 2 ) NOT NULL ,
				`status` VARCHAR( 255 ) NOT NULL ,
				`ipn_date` DATETIME NOT NULL ,
				`first_name` VARCHAR( 255 ) NOT NULL ,
				`last_name` VARCHAR( 255 ) NOT NULL ,
				`payer_email` VARCHAR( 255 ) NOT NULL ,
				`downloads` INT( 11 ) NOT NULL DEFAULT '0' ,
				`last_download` DATETIME NULL 
				) CHARACTER SET utf8 COLLATE utf8_unicode_ci;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            update_option("eds_db_version", $eds_db_version);
        }
    }

    /**
     * Create files/directories
     */
    private function create_files() {
        // Install files and folders for uploading files and prevent hotlinking
        $upload_dir = wp_upload_dir();

        $files = array(
            array(
                'base' => $upload_dir['basedir'] . '/easy_digital_shop_uploads',
                'file' => '.htaccess',
                'content' => 'deny from all'
            ),
            array(
                'base' => $upload_dir['basedir'] . '/easy_digital_shop_uploads',
                'file' => 'index.html',
                'content' => ''
            ),
            array(
                'base' => EASY_DIGITAL_SHOP_LOG_DIR,
                'file' => '.htaccess',
                'content' => 'deny from all'
            ),
            array(
                'base' => EASY_DIGITAL_SHOP_LOG_DIR,
                'file' => 'index.html',
                'content' => ''
            )
        );



        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                if ($file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'w')) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }

}
