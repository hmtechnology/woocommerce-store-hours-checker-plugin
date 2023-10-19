<?php
/*
Plugin Name: WooCommerce Store Hours Checker
Description: Allows managing store hours and disabling checkout outside of store hours.
Version: 1.0
Author: hmtechnology
Author URI: https://github.com/hmtechnology
License: GNU General Public License v3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.txt
Plugin URI: https://github.com/hmtechnology/woocommerce-store-hours-checker-plugin
*/

// Include all necessary files
require_once plugin_dir_path(__FILE__) . 'functions.php';
require_once plugin_dir_path(__FILE__) . 'admin-settings.php';
require_once plugin_dir_path(__FILE__) . 'checkout-checker.php';

// Set the timezone based on the plugin option
$store_timezone = get_option('store_timezone', 'Europe/Rome');
date_default_timezone_set($store_timezone);

?>
