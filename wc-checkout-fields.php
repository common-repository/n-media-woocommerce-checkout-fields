<?php
/*
Plugin Name: N-Media WooCommerce Checkout Fields
Plugin URI: http://najeebmedia.com/wordpress-plugin/woocommerce-personalized-product-option/
Description: Checkout Fields Manager plugin allow to manage fields on Checkout page. Billing, Shipping, Extra fields etc.
Version: 18.0
Author: Najeeb Ahmad
Text Domain: cfom
Domain Path: /languages
WC requires at least: 3.0.0
WC tested up to: 3.5.0
Author URI: http://www.najeebmedia.com/
*/

// @since 6.1
if( ! defined('ABSPATH' ) ){
	exit;
}

define('CFOM_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define('CFOM_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define('CFOM_WP_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __DIR__ ) ));
define('CFOM_VERSION', 18.0);
define('CFOM_DB_VERSION', 18.0);
define('CFOM_TABLE_META', 'cfom');
define('cfom_UPLOAD_DIR_NAME', 'cfom_files');


include_once CFOM_PATH . "/inc/functions.php";
include_once CFOM_PATH . "/inc/arrays.php";
include_once CFOM_PATH . "/inc/hooks.php";
include_once CFOM_PATH . "/inc/admin.php";
include_once CFOM_PATH . "/inc/nmInput.class.php";
include_once CFOM_PATH . "/inc/default-fields.php";


/* ======= For now we are including class file, we will replace  =========== */
include_once CFOM_PATH . "/classes/admin.fields.php";
include_once CFOM_PATH . "/classes/input.class.php";
include_once CFOM_PATH . "/classes/cfom.meta.php";
include_once CFOM_PATH . "/classes/cfom.order.php";
include_once CFOM_PATH . "/classes/cfom.class.php";

// deactivation form
include_once CFOM_PATH . "/classes/deactivate.class.php";

if( is_admin() ){

	include_once CFOM_PATH . "/classes/admin.class.php";

	$cfom_admin = new CFOM_Admin();
	
	$cfom_basename = plugin_basename( __FILE__ );
	add_filter( "plugin_action_links_{$cfom_basename}", 'cfom_settings_link');
}


// ==================== INITIALIZE PLUGIN CLASS =======================
//
add_action('woocommerce_init', 'CFOM');
//
// ==================== INITIALIZE PLUGIN CLASS =======================

function CFOM(){
	return CFOM_Manager::get_instance();
}

//activation/install the plugin data
register_activation_hook( __FILE__, array('CFOM_Manager', 'activate_plugin'));
// register_deactivation_hook( __FILE__, array('CFOM_Manager', 'deactivate_plugin'));