<?php
/**
 * Checkout Manager Class
 * @since version 15.0
 * 
 * */

 
/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH') ) die('Not Allowed');

class CFOM_Manager {
     
     /**
	 * the static object instace
	 */
	private static $ins = null;
	
	public static function get_instance(){
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
	
	function __construct() {
		
		// hooking up scripts for front-end
		add_action('wp_enqueue_scripts', array($this, 'load_scripts'));
		
		// CFOM GLobal fields and some other stuff
		add_action('woocommerce_checkout_before_customer_details', 'cfom_hook_hidden_fields');
		
		// Setting All Actions Hooks against Sections
		foreach(cfom_arrays_get_action_hook_aginst_sections() as $section => $actions) {
			
			add_action($actions, "cfom_hooks_render_fields");
		}

		// Add print pdf of all cfom order meta sections
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'add_pdf_button' ), 10, 2 );
		add_action( 'admin_head', array( $this, 'create_pdf_icon') );
		
		// =========== Rendering Extra fields on different areas ================
			// Display extra fields after order
			add_action('woocommerce_order_details_after_customer_details', array( $this, 'show_all_order_meta'), 10, 1);
			// Display billing field value on the order edit page
			add_action( 'woocommerce_admin_order_data_after_billing_address', array($this, 'show_billing_extra_fields'), 10, 1 );
			// Display shipping field value on the order edit page
			add_action( 'woocommerce_admin_order_data_after_shipping_address', array($this, 'show_shipping_extra_fields'), 10, 1 );
			// inside email/invoice
			add_action( 'woocommerce_email_customer_details', array($this, 'show_order_in_email'), 30, 4 );
		// =========== Rendering Extra fields on different areas ================
		
		
		// Disable Checkout Core Fields
		add_filter( 'woocommerce_billing_fields' , array($this, 'checkout_billing_fields') );
		add_filter( 'woocommerce_shipping_fields' , array($this, 'checkout_shipping_fields') );
		// For order comments field
		add_filter( 'woocommerce_checkout_fields' , array($this, 'checkout_order_comment') );
		
		// Adding CFOM data into posted_array of wc checkout process
		add_filter('woocommerce_checkout_posted_data', 'cfom_hooks_add_checkout_data' );
		
		// Checkout validation
		add_action('woocommerce_checkout_process', 'cfom_hooks_validation_before_checkout' );
		
		// Post checkout value to server
		add_action('woocommerce_checkout_create_order', 'cfom_hooks_create_order', 999, 2);
	
		
		/** ============ LOCAL Hooks ==================== */
		add_filter('cfom_field_attributes', 'cfom_hooks_set_attributes', 10, 2);
		add_filter('cfom_field_setting', 'cfom_hooks_input_args', 10, 2);
		// Checkbox validation hook
		 add_filter('cfom_has_posted_field_value', 'cfom_hooks_checkbox_validation', 10, 3);
		// Change color type to text for rendering
		add_filter('nmform_attribute_value', 'cfom_hooks_color_to_text_type', 10, 3);
		// add a wrapper class in each input e.g: cfom-input-{data_name}
		add_filter('cfom_input_wrapper_class', 'cfom_hooks_input_wrapper_class', 10, 2);
		// Country Option
		add_filter('cfom_option_key', 'cfom_change_country_select_value', 99, 3);
		// Updating meta fields before saving, adding enable_field key if not set
		add_filter('cfom_meta_before_saving', 'cfom_hook_add_enable_field', 99, 2);

		/** ============ Ajax callbacks ==================== */
		 
		 
		 // Saving settings and fields
		add_action('wp_ajax_cfom_save_form_meta', 'cfom_admin_save_form_meta');
		add_action('wp_ajax_cfom_update_form_meta', 'cfom_admin_update_form_meta');
		add_action('wp_ajax_cfom_delete_meta', 'cfom_admin_delete_meta');
		add_action('wp_ajax_cfom_delete_selected_meta', 'cfom_admin_delete_selected_meta');
		
		

		add_action( 'admin_post_cfom_print_pdf', 'cfom_admin_print_section_pdf' );
	}
	
	
	function load_scripts() {
	    
	    if( ! is_checkout() ) return;
	    
	    // Loading all required scripts/css for inputs like datepicker, fileupload etc
        cfom_hooks_load_input_scripts();
        
        // main css
        wp_enqueue_style( 'cfom-main', CFOM_URL.'/css/cfom-style.css');
        
        // If Bootstrap is enabled
        if( cfom_load_bootstrap_css() ) {
        	
        	// Boostrap 4.0
            $cfom_bs_css	= CFOM_URL.'/css/bootstrap/bootstrap.css';
            $cfom_bs_js 	= CFOM_URL.'/js/bootstrap.min.js';
            $cfom_bs_modal_css = CFOM_URL.'/css/bootstrap/bootstrap.modal.css';
            $cfom_tooltip	= CFOM_URL.'/js/admin/cfom-tooltip.js';
            
            wp_enqueue_style( 'cfom-bootstrap', $cfom_bs_css);
            wp_enqueue_style( 'cfom-bootstrap-modal', $cfom_bs_modal_css);
            
            
            wp_enqueue_script( 'cfom-tooltip', $cfom_tooltip, array('jquery'));
            wp_enqueue_script( 'bootstrap-js', $cfom_bs_js, array('cfom-tooltip'));
        }
        
        do_action('cfom_after_scripts_loaded');
	}

	// get all section order meta print
	function add_pdf_button( $actions, $order ) {
	    
	    if( ! cfom_pro_is_installed() ) return $actions;
	    
        $action_slug = 'cfom_print_pdf';

        // Set the action button
        $actions[$action_slug] = array(
            'url'       => wp_nonce_url( admin_url( 'admin-post.php?action=cfom_print_pdf&section=all&order_id=' . $order->get_id() ), 'cfom' ),
            'name'      => __( 'Create PDF', 'cfom' ),
            'action'    => $action_slug,
        );
	    
	    return $actions;
	}
	
	function create_pdf_icon() {
	    $action_slug = "cfom_print_pdf";

	    echo '<style>.wc-action-button-'.$action_slug.'::after { font-family: woocommerce !important; content: "\e029" !important; }</style>';
	}

	function show_all_order_meta($order_id) { 

		wp_enqueue_style('cfom-order-css', CFOM_URL."/css/cfom-order.css");

		$cfom_order = new CFOM_Order( $order_id );
        $order_meta = $cfom_order->display_order_meta('checkout');

	    echo $order_meta;
	}
	
	function show_billing_extra_fields( $order ) {
		
		$extra_fields_heading = apply_filters('cfom_extra_billing_fields_heading', 'Extra Fields');
		
		$cfom_order 	= new CFOM_Order( $order->get_id() );
        $billing_extra = $cfom_order->get_billing_extra_fields();
        
        if( ! $billing_extra ) return;
        
        echo "<p>";
		printf(__("<h4>%s</h4>",'cfom'), $extra_fields_heading);
    	foreach($billing_extra as $field_name => $detail) {
    		
    		printf(__("<strong>%s</strong>: %s<br>", 'cfom'), $detail['title'], $detail['display'] );
    	}
    	
    	echo "</p>";
        
	}
	
	function show_shipping_extra_fields( $order ) {
		
		$extra_fields_heading = apply_filters('cfom_extra_shipping_fields_heading', 'Extra Fields');
		
		$cfom_order 	= new CFOM_Order( $order->get_id() );
        $shipping_extra = $cfom_order->get_shipping_extra_fields();
        
        if( ! $shipping_extra ) return;
        
        echo "<p>";
		printf(__("<h4>%s</h4>",'cfom'), $extra_fields_heading);
    	foreach($shipping_extra as $field_name => $detail) {
    		
    		printf(__("<strong>%s</strong>: %s<br>", 'cfom'), $detail['title'], $detail['display'] );
    	}
    	
    	echo "</p>";
        
	}

	public function checkout_billing_fields( $billing_fields ) {
	    
    	$section		= 'billing';
	    $cfom			= new CFOM_Meta();
		$cfom_fields	= $cfom->get_core_checkout_fields($section);
		
		// If billing fields define/active then set core fields as NULL
		if( $cfom_fields ) {
			$billing_fields = array();
		}
		
	    //cfom_pa($billing_fields); exit;
						
	    return $billing_fields;
	    
	}
	
	public function checkout_shipping_fields( $shipping_fields ) {
	    
	    $section = 'shipping';
	    $cfom	= new CFOM_Meta();
		$cfom_fields	= $cfom->get_core_checkout_fields($section);
		
		// If billing fields define/active then set core fields as NULL
		if( $cfom_fields ) {
			$shipping_fields = array();
		}
		
	    return $shipping_fields;
	    
	}
	
	public function checkout_order_comment( $checkout_fields ) {
	    
	    $section	= 'after_order';
	    $cfom		= new CFOM_Meta();
		$cfom_fields= $cfom->get_core_checkout_fields($section);
		
		// If after_order fields define/active then set core fields as NULL
		if( $cfom_fields ) {
			unset($checkout_fields['order']['order_comments']);
		}
		
	    return $checkout_fields;
	    
	}
	
	
	
	public static function activate_plugin() {
		global $wpdb;
	
		/*
		 * meta_for: this is to make this table to contact more then one metas for NM plugins in future in this plugin it will be populated with: forms
		 */
		$forms_table_name = $wpdb->prefix . CFOM_TABLE_META;
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $forms_table_name (
		section_id INT(5) NOT NULL AUTO_INCREMENT,
		section_type VARCHAR(50) NOT NULL,
		section_title VARCHAR(50) NOT NULL,
		conditions MEDIUMTEXT,
		cfom_css MEDIUMTEXT,
        is_active VARCHAR(3) NOT NULL,
        show_title VARCHAR(3) NOT NULL,
        cfom_meta MEDIUMTEXT NOT NULL,
        cfom_options MEDIUMTEXT,
		date_created DATETIME NOT NULL,
		section_order INT(3),
		PRIMARY KEY  (section_id)
		) $charset_collate;";
		
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta ( $sql );
		
		update_option ( "cfom_db_version", CFOM_DB_VERSION );

		// Installing default sections with fields
		cfom_create_all_default_sections();
	}
	
	
	function show_order_in_email( $order, $sent_to_admin, $plain_text, $email ){
        
        $cfom_order = new CFOM_Order( $order->ID );
        $order_meta = $cfom_order->display_order_meta('email');
        
        echo $order_meta;
    }
	
}