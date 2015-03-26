<?php

/**
 * @class       Easy_Digital_Shop_Admin
 * @version	1.0.0
 * @package	min-max-quantities-for-woocommerce
 * @category	Class
 * @author      johnny-manziel <jmkaila@gmail.com>
 */
class Easy_Digital_Shop_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_shortcode('easydigitalshop', array($this, 'easydigitalshop_shortcode'));
        add_filter('woocommerce_paypal_args', array(__CLASS__, 'easy_digital_shop_standard_parameters'), 10, 1);
    }

    public function eds_meta_box() {
        add_meta_box('eds_meta', 'Easy Digital Shop', array($this, 'eds_metabox_content'), 'post');
        add_meta_box('eds_meta', 'Easy Digital Shop', array($this, 'eds_metabox_content'), 'page');
    }

    public function eds_check_dir() {
        if (!is_dir(EDS_DIR) OR @ file_put_contents(EDS_DIR . "/testfile.tmp", "test") === false) {
            return false;
        } else {
            @unlink(EDS_DIR . "/testfile.tmp");

            if (!file_exists(EDS_DIR . "/.htaccess")) {
                file_put_contents(EDS_DIR . "/.htaccess", "Order deny,allow\nDeny from all");
                @chmod(EDS_DIR . "/.htaccess", EDS_FILE_PERMISSIONS);
            }
            return true;
        }
    }

    public function eds_glob($pattern) {
        return glob($pattern) ? glob($pattern) : array();
    }

    public function eds_metabox_content() {
        global $post;

        $currency_code = get_option('eds_currency_code');

        if (empty($currency_code)) {
            echo "<p class='error'>Please select currency on settings page first.</p>";
            return;
        }

        if (!$this->eds_check_dir()) {
            echo "<p class='error'>";
            echo "Please create a directory " . EDS_DIR . " and make it writeable (chmod 777 or less).";
            echo "</p>";
            return;
        }
        ?>
        <script type="text/javascript">
            document.getElementById("post").setAttribute("enctype", "multipart/form-data");
            document.getElementById('post').setAttribute('encoding', 'multipart/form-data');

            jQuery(document).ready(function() {
                jQuery('#eds_deletelink').click(function(e) {
                    e.preventDefault();
                    jQuery('#eds_delete').val(1);
                    jQuery('#post').submit();
                });
            });
        </script>
        <?php
        echo "<table class='form-table'>";
        echo "<tbody>";

        $files = $this->eds_glob(EDS_DIR . "/post" . $post->ID . "_*");
        if (count($files)) {
            echo "<tr>";
            echo "<td>";
            echo "<label for='eds_file'><strong>File to sell</strong></label>";
            echo "</td>";
            echo "<td>";
            echo "<strong>" . array_pop(explode("/", $files[0])) . "</strong> (<a href='#' id='eds_deletelink'>delete</a>)";
            echo "</td>";
            echo "</tr>";
        }
        echo "<tr>";
        echo "<td>";
        echo '<label for="eds_file"><strong>' . "Upload new file" . '</strong></label>';
        echo "</td>";
        echo "<td>";
        echo "<input type='file' name='eds_file' id='eds_file' class='regular-text' />";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";
        echo "<label for='eds_price'><strong>Price in $currency_code</strong></label>";
        echo "</td>";
        echo "<td>";
        $price = get_post_meta($post->ID, 'eds_price', true);
        $price = empty($price) ? "" : sprintf("%01.2f", $price);
        echo "<input type='text' name='eds_price' id='eds_price' value='" . $price . "' class='regular-text' />";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>";
        echo "</td>";
        echo "<td>";
        echo "<input type='hidden' name='eds_delete' id='eds_delete' value='0' />";
        echo "<input name='publish' class='button-primary' type='submit' value='Update Post' />";
        echo "</td>";
        echo "</tr>";

        echo "</tbody>";
        echo "</table>";

        echo "<p>";
        echo "<strong>Don't forget:</strong> You need to add the shortcode [easydigitalshop] into the HTML of this post.<br />";
        echo "</p>";
    }

    function eds_save_meta($post_id, $post) {

        if ($post->post_type == 'revision') {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        $_POST['eds_price'] = floatval(strtr(@$_POST['eds_price'], ',', '.'));

        if (empty($_POST['eds_price'])) {
            $_POST['eds_price'] = "";
        } else {
            $_POST['eds_price'] = sprintf("%01.2f", $_POST['eds_price']);
        }

        if (get_post_meta($post_id, 'eds_price') == "" AND $_POST['eds_price'] != "") {
            add_post_meta($post_id, 'eds_price', $_POST['eds_price'], true);
        } elseif ($_POST['eds_price'] != get_post_meta($post_id, 'eds_price', true) AND $_POST['eds_price'] != "") {
            update_post_meta($post_id, 'eds_price', $_POST['eds_price']);
        } elseif ($_POST['eds_price'] == "") {
            delete_post_meta($post_id, 'eds_price');
        }

        if (!empty($_POST['eds_delete'])) {
            $files = $this->eds_glob(EDS_DIR . "/post" . $post_id . "_*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        if (isset($_FILES['eds_file']) AND $_FILES['eds_file']['error'] == 0) {
            $files = $this->eds_glob(EDS_DIR . "/post" . $post_id . "_*");
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $destination = EDS_DIR . "/post" . $post_id . "_" . sanitize_file_name($_FILES['eds_file']['name']);
            @move_uploaded_file($_FILES['eds_file']['tmp_name'], $destination);
            @chmod($destination, EDS_FILE_PERMISSIONS);
        }
    }

    function easydigitalshop_shortcode($atts) {
        global $post;

        extract(shortcode_atts(array('id' => null), $atts));

        if (is_null($id) AND !is_null($post)) {
            return $this->easydigitalshop_output($post->ID);
        } elseif (!is_null($id)) {
            return $this->easydigitalshop_output($id);
        } else {
            return "";
        }
    }

    public function easydigitalshop_output($postid) {
        $post = get_post($postid);

        $price = get_post_meta($post->ID, "eds_price", true);
        $currency = get_option('eds_currency_code');
        $use_sandbox = get_option('eds_use_sandbox');
        $email = get_option('eds_paypal_email');

        ob_start();

        if (!empty($price) AND !empty($currency) AND count($this->eds_glob(EDS_DIR . "/post" . $post->ID . "_*")) AND !empty($email)) {

            $price = sprintf("%01.2f", $price);

            echo "<form action='" . (empty($use_sandbox) ? "https://www.paypal.com/cgi-bin/webscr" : "https://www.sandbox.paypal.com/cgi-bin/webscr") . "' method='post' target='_blank'>";

            if (file_exists(EDS_DIR_CONTENT . "/button.php")) {
                include(EDS_DIR_CONTENT . "/button.php");
            } else {
                require_once plugin_dir_path(dirname(__FILE__)) . 'button.php';
            }

            echo '<input type="hidden" name="cmd" value="_xclick" />';
            echo '<input name="bn" value="mbjtechnolabs_SP" type="hidden" />';
            echo "<input type='hidden' name='business' value='$email' />";
            echo "<input type='hidden' name='item_name' value='" . esc_attr($post->post_title) . "' />";
            echo "<input type='hidden' name='item_number' value='$post->ID' />";
            echo "<input type='hidden' name='amount' value='$price' />";
            echo '<input type="hidden" name="no_shipping" value="1" />';
            echo '<input type="hidden" name="no_note" value="1" />';
            echo "<input type='hidden' name='currency_code' value='$currency' />";
            echo "<input type='hidden' name='notify_url' value='" . get_option('siteurl') . "/wp-content/plugins/easy-digital-shop/ipn.php' />";
            $eds_return_page = get_option('eds_return_page');
            if (!empty($eds_return_page)) {
                echo "<input type='hidden' name='return' value='" . get_page_link($eds_return_page) . "' />";
            }
            echo '</form>';
        }

        return ob_get_clean();
    }

    public function eds_register_mysettings() {
        register_setting('eds-settings-group', 'eds_currency_code');
        register_setting('eds-settings-group', 'eds_paypal_email', 'sanitize_email');
        register_setting('eds-settings-group', 'eds_use_sandbox');
        register_setting('eds-settings-group', 'eds_emailsubject');
        register_setting('eds-settings-group', 'eds_emailtext');
        register_setting('eds-settings-group', 'eds_shortlink');
        register_setting('eds-settings-group', 'eds_return_page');
    }

    public function eds_add_pages() {
        add_menu_page("Easy Digital Shop", "Easy Digital Shop", 'manage_options', 'eds-sales-list', array($this, 'eds_sales_page'));
        add_submenu_page('eds-sales-list', 'Settings', 'Settings', 'manage_options', 'eds-settings-page', array($this, 'eds_settings_page'));
    }

    public function eds_sales_page() {
        global $wpdb;

        echo "<div class='wrap'>";
        echo "<h2>Sales</h2>";

        if (!$this->eds_check_dir()) {
            echo "<div id='message' class='error' style='overflow:hidden;'>";
            echo "<p>";
            echo "Please create a directory " . EDS_DIR . " and make it writeable (chmod 777 or less).";
            echo "</p>";
            echo "</div>";
        }
        echo "<h3>Search Transaction</h3>";
        echo "<form method='get' action='{$_SERVER['SCRIPT_NAME']}'>";
        echo "<table class='form-table'>";
        echo "<tr valign='top'>";
        echo "<th scope='row'>Name of Payer</th>";
        echo "<td>";
        echo "<input type='text' name='name' value='" . @$_GET['name'] . "' />";
        echo "</td>";
        echo "</tr>";

        echo "<tr valign='top'>";
        echo "<th scope='row'>Email of Payer</th>";
        echo "<td>";
        echo "<input type='text' name='email' value='" . @$_GET['email'] . "' />";
        echo "</td>";
        echo "</tr>";

        echo "<tr valign='top'>";
        echo "<th scope='row'>Title of Post/File</th>";
        echo "<td>";
        echo "<input type='text' name='title' value='" . @$_GET['title'] . "' />";
        echo "</td>";
        echo "</tr>";

        echo "<tr valign='top'>";
        echo "<th scope='row'>Post / Product ID</th>";
        echo "<td>";
        echo "<input type='text' name='postid' value='" . @$_GET['postid'] . "' />";
        echo "</td>";
        echo "</tr>";

        echo "<tr valign='top'>";
        echo "<th scope='row'>Paypal Transaction ID</th>";
        echo "<td>";
        echo "<input type='text' name='txn_id' value='" . @$_GET['txn_id'] . "' />";
        echo "</td>";
        echo "</tr>";

        echo "<tr valign='top'>";
        echo "<th scope='row'>Status of payment</th>";
        echo "<td>";
        echo "<select name='status'>";
        $selected = (isset($_GET['status']) AND $_GET['status'] == 0) ? "selected='selected'" : "";
        echo "<option value='0' $selected>All</option>";
        $selected = (isset($_GET['status']) AND $_GET['status'] == 1) ? "selected='selected'" : "";
        echo "<option value='1' $selected>Only VERIFIED</option>";
        $selected = (isset($_GET['status']) AND $_GET['status'] == 2) ? "selected='selected'" : "";
        echo "<option value='2' $selected>Only Invalid</option>";
        echo "</select>";
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        echo "<p class='submit'>";
        echo "<input type='hidden' name='page' value='eds-sales-list' />";
        echo "<input type='submit' class='button-secondary' value='Search Transactions' />";
        echo "</p>";

        echo "</form>";

        foreach ($_GET as $key => $value) {
            $_GET[$key] = trim($value);
        }

        $nameBedingung = "";
        if (!empty($_GET['name'])) {
            $nameBedingung = " AND (first_name LIKE '%" . $wpdb->escape($_GET['name']) . "%' OR last_name LIKE '%" . $wpdb->escape($_GET['name']) . "%') ";
        }


        $emailBedingung = "";
        if (!empty($_GET['email'])) {
            $emailBedingung = " AND payer_email LIKE '%" . $wpdb->escape($_GET['email']) . "%' ";
        }

        $titleBedingung = "";
        if (!empty($_GET['title'])) {
            $titleBedingung = " AND post_title LIKE '%" . $wpdb->escape($_GET['title']) . "%' ";
        }

        $titleBedingung = "";
        if (!empty($_GET['title'])) {
            $titleBedingung = " AND post_title LIKE '%" . $wpdb->escape($_GET['title']) . "%' ";
        }

        $postidBedingung = "";
        if (!empty($_GET['postid'])) {
            $postidBedingung = " AND postid = " . intval($_GET['postid']) . " ";
        }

        $txn_idBedingung = "";
        if (!empty($_GET['txn_id'])) {
            $txn_idBedingung = " AND txn_id = '" . $wpdb->escape($_GET['txn_id']) . "' ";
        }

        $statusBedingung = "";
        if (!empty($_GET['status'])) {
            $operator = $_GET['status'] == 1 ? "=" : "!=";
            $statusBedingung = " AND status $operator 'VERIFIED' ";
        }

        $current = empty($_GET['paged']) ? 1 : intval($_GET['paged']);
        $items_per_page = 50;

        $easydigitalshop = $wpdb->prefix . "easydigitalshop";

        $sql = "SELECT SQL_CALC_FOUND_ROWS
				$easydigitalshop.*,
				{$wpdb->posts}.post_title
			FROM
				$easydigitalshop
			LEFT JOIN {$wpdb->posts} ON $easydigitalshop.postid = {$wpdb->posts}.ID
			WHERE TRUE
			$nameBedingung
			$emailBedingung
			$titleBedingung
			$postidBedingung
			$txn_idBedingung
			$statusBedingung
			ORDER BY id DESC
			LIMIT " . (($current - 1) * $items_per_page) . ", " . $items_per_page;

        //echo $sql;			

        $rows = $wpdb->get_results($sql);

        $found_rows = $wpdb->get_col("SELECT FOUND_ROWS();");

        $items = $found_rows[0];

        if ($items > 0) {

            $items_per_page = 50;
            $num_pages = ceil($items / $items_per_page);

            $page_links = paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $num_pages,
                'current' => $current
            ));

            echo "<h3>$items Transactions found</h3>";

            echo "<div class='tablenav'>";
            echo "<div class='tablenav-pages'>";
            echo $page_links;
            echo "</div>";
            echo "</div>";
            echo "<table class='widefat'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Transaction ID</th>";
            echo "<th>Post ID</th>";
            echo "<th>Post Title</th>";
            echo "<th>Price</th>";
            echo "<th>Status</th>";
            echo "<th>Date</th>";
            echo "<th>First name</th>";
            echo "<th>Last name</th>";
            echo "<th>Payer email</th>";
            echo "<th>Link</th>";
            echo "<th>Downloads</th>";
            echo "<th>Last Download</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tfoot>";
            echo "<tr>";
            echo "<th>Transaction ID</th>";
            echo "<th>Post ID</th>";
            echo "<th>Post Title</th>";
            echo "<th>Price</th>";
            echo "<th>Status</th>";
            echo "<th>Date</th>";
            echo "<th>First name</th>";
            echo "<th>Last name</th>";
            echo "<th>Payer email</th>";
            echo "<th>Link</th>";
            echo "<th>Downloads</th>";
            echo "<th>Last Download</th>";
            echo "</tr>";
            echo "</tfoot>";
            echo "<tbody>";
            foreach ($rows as $row) {
                echo "<tr>";
                echo "<td>{$row->txn_id}</td>";
                echo "<td><a href='" . get_page_link($row->postid) . "' target='_blank'>{$row->postid}</a></td>";
                echo "<td><a href='" . get_page_link($row->postid) . "' target='_blank'>{$row->post_title}</a></td>";
                echo "<td>" . $row->price . " " . get_option('eds_currency_code') . "</td>";
                echo "<td>{$row->status}</td>";
                echo "<td>{$row->ipn_date}</td>";
                echo "<td>{$row->first_name}</td>";
                echo "<td>{$row->last_name}</td>";
                echo "<td><a href='mailto:{$row->payer_email}'>{$row->payer_email}</a></td>";

                $link = get_option('eds_shortlink') ? get_option('siteurl') . "/eds/" . $row->hash : WP_PLUGIN_URL . "/easy-digital-shop/download.php?h=" . $row->hash;
                echo "<td><a href='$link' target='_blank'>{$row->hash}</a></td>";
                echo "<td>{$row->downloads}</td>";
                echo "<td>{$row->last_download}</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        }

        echo "</div>";
    }

    public function eds_settings_page() {
        global $eds_currency_codes;
        global $wpdb;

        if (get_option('eds_emailsubject') == "") {
            update_option('eds_emailsubject', "Your Download Link");
        }

        if (get_option('eds_emailtext') == "") {
            $text = 'Dear $first_name $last_name,

            thank you for buying the file $post_title.
            You may download it here:
            $downloadlink

            Kindest regards
            $blogname';

            update_option('eds_emailtext', $text);
        }
        ?>
        <div class="wrap">
            <h2>Easy Digital Shop Settings</h2>
            <?php
            if (!$this->eds_check_dir()) {
                echo "<div id='message' class='error' style='overflow:hidden;'>";
                echo "<p>";
                echo "Please create a directory " . EDS_DIR . " and make it writeable (chmod 777 or less).";
                echo "</p>";
                echo "</div>";
            }
            ?>
            <form method="post" action="options.php">
                <?php settings_fields('eds-settings-group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Currency</th>
                        <td>
                            <?php
                            echo "<select name='eds_currency_code'>";
                            $currency_code_options = self::get_eds_currencies();

                            foreach ($currency_code_options as $code => $name) {
                                $currency_code_options[$code] = $name . ' (' . self::get_eds_symbol($code) . ')';
                            }
                            foreach ($currency_code_options as $code => $currency) {
                                $selected = (get_option('eds_currency_code') == $code) ? "selected='selected'" : "";
                                echo "<option value='$code' $selected>$currency</option>";
                            }
                            echo "</select>";
                            ?>       
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Paypal Email</th>
                        <td>
                            <input type='text' name='eds_paypal_email' value='<?php echo get_option('eds_paypal_email'); ?>' />     
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row">Use Paypal Sandbox</th>
                        <td>
                            <?php
                            $checked = get_option('eds_use_sandbox') ? "checked='checked'" : "";
                            echo "<input type='checkbox' name='eds_use_sandbox' $checked />";
                            ?>
                        </td>
                    </tr>
                    <?php
                    echo "<tr valign='top'>";
                    echo "<th scope='row'>Thank you page / return page after the checkout process is completed</th>";
                    echo "<td>";
                    echo "<p class='description'>";
                    echo "</p>";

                    $sql = "SELECT
        			ID,
        			post_title
        		FROM {$wpdb->posts}
        		WHERE post_type = 'page' AND post_status = 'publish'";

                    $rows = $wpdb->get_results($sql);

                    echo "<select name='eds_return_page'>";
                    echo "<option value='0'>No return page</option>";
                    foreach ($rows as $row) {
                        $selected = get_option('eds_return_page') == $row->ID ? "selected='selected'" : "";
                        echo "<option value='{$row->ID}' $selected>{$row->post_title}</option>";
                    }
                    echo "</select>";

                    echo "<p class='description'>";
                    echo "Note: You can hide this page. It does not need to show up in any navigation (e.g. exclude it on the widget page).<br />";
                    echo "<strong>Important: You need to activate &quot;Auto Return&quot; on the Profile Page &quot;Website Payment Preferences&quot; of your paypal account.</strong><br />";
                    echo "</p>";
                    echo "</td>";
                    echo "</tr>";

                    echo "<tr valign='top'>";
                    echo "<th scope='row'>Subject of Download Link Mail</th>";
                    echo "<td>";
                    echo "<input type='text' name='eds_emailsubject' value='" . get_option('eds_emailsubject') . "' size='75' />";
                    echo "</td>";
                    echo "</tr>";

                    echo "<tr valign='top'>";
                    echo "<th scope='row'>Text of Download Link Mail<br />(variables beginning with '$' will be substituted)</th>";
                    echo "<td>";
                    echo "<textarea name='eds_emailtext' cols='75' rows='25'>";
                    echo get_option('eds_emailtext');
                    echo "</textarea>";
                    echo "</td>";
                    echo "</tr>";

                    echo "<tr valign='top'>";
                    echo "<th scope='row'>Use short Download Link</th>";
                    echo "<td>";

                    $checked = get_option('eds_shortlink') ? "checked='checked'" : "";
                    echo "<input type='checkbox' name='eds_shortlink' $checked onclick=\"var el = document.getElementById('htaccess'); if(this.checked) el.style.display = 'block'; else el.style.display = 'none'; \" />";
                    $hidden = get_option('eds_shortlink') ? "" : "style='display:none;'";

                    echo "<div id='htaccess' $hidden>";
                    echo "<p class='description'>";
                    echo "Please insert at the <strong>beginning</strong> of your .htaccess:";
                    echo "</p>";
                    echo "<p>";
                    echo "<textarea rows='10' cols='75'>";
                    echo "<IfModule mod_rewrite.c>\n";
                    echo "RewriteEngine On\n";
                    echo "RewriteBase " . COOKIEPATH . "\n";
                    echo "RewriteRule ^eds/([\d\w]+)/?$ " . COOKIEPATH . "wp-content/plugins/easy-digital-shop/download.php?h=$1 [L]\n";
                    echo "</IfModule>\n";
                    echo "</textarea>";
                    echo "</p>";
                    echo "</div>";
                    echo "</td>";
                    echo "</tr>";
                    ?>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>

            </form>
        </div>
        <?php
    }

    /**
     * Get full list of currency codes.
     * @return array
     */
    public static function get_eds_currencies() {
        return array_unique(
                apply_filters('paypal_donation_for_wordpress_currencies', array(
            'AED' => __('United Arab Emirates Dirham', 'paypal-donation-for-wordpress'),
            'AUD' => __('Australian Dollars', 'paypal-donation-for-wordpress'),
            'BDT' => __('Bangladeshi Taka', 'paypal-donation-for-wordpress'),
            'BRL' => __('Brazilian Real', 'paypal-donation-for-wordpress'),
            'BGN' => __('Bulgarian Lev', 'paypal-donation-for-wordpress'),
            'CAD' => __('Canadian Dollars', 'paypal-donation-for-wordpress'),
            'CLP' => __('Chilean Peso', 'paypal-donation-for-wordpress'),
            'CNY' => __('Chinese Yuan', 'paypal-donation-for-wordpress'),
            'COP' => __('Colombian Peso', 'paypal-donation-for-wordpress'),
            'CZK' => __('Czech Koruna', 'paypal-donation-for-wordpress'),
            'DKK' => __('Danish Krone', 'paypal-donation-for-wordpress'),
            'DOP' => __('Dominican Peso', 'paypal-donation-for-wordpress'),
            'EUR' => __('Euros', 'paypal-donation-for-wordpress'),
            'HKD' => __('Hong Kong Dollar', 'paypal-donation-for-wordpress'),
            'HRK' => __('Croatia kuna', 'paypal-donation-for-wordpress'),
            'HUF' => __('Hungarian Forint', 'paypal-donation-for-wordpress'),
            'ISK' => __('Icelandic krona', 'paypal-donation-for-wordpress'),
            'IDR' => __('Indonesia Rupiah', 'paypal-donation-for-wordpress'),
            'INR' => __('Indian Rupee', 'paypal-donation-for-wordpress'),
            'NPR' => __('Nepali Rupee', 'paypal-donation-for-wordpress'),
            'ILS' => __('Israeli Shekel', 'paypal-donation-for-wordpress'),
            'JPY' => __('Japanese Yen', 'paypal-donation-for-wordpress'),
            'KIP' => __('Lao Kip', 'paypal-donation-for-wordpress'),
            'KRW' => __('South Korean Won', 'paypal-donation-for-wordpress'),
            'MYR' => __('Malaysian Ringgits', 'paypal-donation-for-wordpress'),
            'MXN' => __('Mexican Peso', 'paypal-donation-for-wordpress'),
            'NGN' => __('Nigerian Naira', 'paypal-donation-for-wordpress'),
            'NOK' => __('Norwegian Krone', 'paypal-donation-for-wordpress'),
            'NZD' => __('New Zealand Dollar', 'paypal-donation-for-wordpress'),
            'PYG' => __('Paraguayan Guaraní', 'paypal-donation-for-wordpress'),
            'PHP' => __('Philippine Pesos', 'paypal-donation-for-wordpress'),
            'PLN' => __('Polish Zloty', 'paypal-donation-for-wordpress'),
            'GBP' => __('Pounds Sterling', 'paypal-donation-for-wordpress'),
            'RON' => __('Romanian Leu', 'paypal-donation-for-wordpress'),
            'RUB' => __('Russian Ruble', 'paypal-donation-for-wordpress'),
            'SGD' => __('Singapore Dollar', 'paypal-donation-for-wordpress'),
            'ZAR' => __('South African rand', 'paypal-donation-for-wordpress'),
            'SEK' => __('Swedish Krona', 'paypal-donation-for-wordpress'),
            'CHF' => __('Swiss Franc', 'paypal-donation-for-wordpress'),
            'TWD' => __('Taiwan New Dollars', 'paypal-donation-for-wordpress'),
            'THB' => __('Thai Baht', 'paypal-donation-for-wordpress'),
            'TRY' => __('Turkish Lira', 'paypal-donation-for-wordpress'),
            'USD' => __('US Dollars', 'paypal-donation-for-wordpress'),
            'VND' => __('Vietnamese Dong', 'paypal-donation-for-wordpress'),
            'EGP' => __('Egyptian Pound', 'paypal-donation-for-wordpress'),
                        )
                )
        );
    }

    /**
     * Get Currency symbol.
     * @param string $currency (default: '')
     * @return string
     */
    public static function get_eds_symbol($currency = '') {
        if (!$currency) {
            $currency = get_eds_currencies();
        }

        switch ($currency) {
            case 'AED' :
                $currency_symbol = 'د.إ';
                break;
            case 'BDT':
                $currency_symbol = '&#2547;&nbsp;';
                break;
            case 'BRL' :
                $currency_symbol = '&#82;&#36;';
                break;
            case 'BGN' :
                $currency_symbol = '&#1083;&#1074;.';
                break;
            case 'AUD' :
            case 'CAD' :
            case 'CLP' :
            case 'COP' :
            case 'MXN' :
            case 'NZD' :
            case 'HKD' :
            case 'SGD' :
            case 'USD' :
                $currency_symbol = '&#36;';
                break;
            case 'EUR' :
                $currency_symbol = '&euro;';
                break;
            case 'CNY' :
            case 'RMB' :
            case 'JPY' :
                $currency_symbol = '&yen;';
                break;
            case 'RUB' :
                $currency_symbol = '&#1088;&#1091;&#1073;.';
                break;
            case 'KRW' : $currency_symbol = '&#8361;';
                break;
            case 'PYG' : $currency_symbol = '&#8370;';
                break;
            case 'TRY' : $currency_symbol = '&#8378;';
                break;
            case 'NOK' : $currency_symbol = '&#107;&#114;';
                break;
            case 'ZAR' : $currency_symbol = '&#82;';
                break;
            case 'CZK' : $currency_symbol = '&#75;&#269;';
                break;
            case 'MYR' : $currency_symbol = '&#82;&#77;';
                break;
            case 'DKK' : $currency_symbol = 'kr.';
                break;
            case 'HUF' : $currency_symbol = '&#70;&#116;';
                break;
            case 'IDR' : $currency_symbol = 'Rp';
                break;
            case 'INR' : $currency_symbol = 'Rs.';
                break;
            case 'NPR' : $currency_symbol = 'Rs.';
                break;
            case 'ISK' : $currency_symbol = 'Kr.';
                break;
            case 'ILS' : $currency_symbol = '&#8362;';
                break;
            case 'PHP' : $currency_symbol = '&#8369;';
                break;
            case 'PLN' : $currency_symbol = '&#122;&#322;';
                break;
            case 'SEK' : $currency_symbol = '&#107;&#114;';
                break;
            case 'CHF' : $currency_symbol = '&#67;&#72;&#70;';
                break;
            case 'TWD' : $currency_symbol = '&#78;&#84;&#36;';
                break;
            case 'THB' : $currency_symbol = '&#3647;';
                break;
            case 'GBP' : $currency_symbol = '&pound;';
                break;
            case 'RON' : $currency_symbol = 'lei';
                break;
            case 'VND' : $currency_symbol = '&#8363;';
                break;
            case 'NGN' : $currency_symbol = '&#8358;';
                break;
            case 'HRK' : $currency_symbol = 'Kn';
                break;
            case 'EGP' : $currency_symbol = 'EGP';
                break;
            case 'DOP' : $currency_symbol = 'RD&#36;';
                break;
            case 'KIP' : $currency_symbol = '&#8365;';
                break;
            default : $currency_symbol = '';
                break;
        }

        return apply_filters('eds_currency_symbol', $currency_symbol, $currency);
    }

    public static function easy_digital_shop_standard_parameters($paypal_args) {
        $paypal_args['bn'] = 'mbjtechnolabs_SP';
        return $paypal_args;
    }

}
