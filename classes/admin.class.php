<?php
/*
 * CFOM Main Admin Class
*/


/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH') ) die('Not Allowed');


class CFOM_Admin extends CFOM_Manager {
	
	var $menu_pages, $plugin_scripts_admin, $plugin_settings;
	
	function __construct() {
		
		// setting plugin meta saved in config.php
		$this->plugin_meta = cfom_get_plugin_meta();
		
		// getting saved settings
		$this->plugin_settings = get_option ( $this -> plugin_meta['shortname'] . '_settings' );
		
		// Default Checkout Fields
		// add_action( 'admin_post_cfom_set_default_fields', array($this, 'set_default_fields') );
		add_action('wp_ajax_cfom_set_default_fields', array($this, 'set_default_fields') );
		// Set is_active state
		add_action( 'admin_post_cfom_set_active_state', array($this, 'set_active_state') );
		
		// Creating meta box for orders
        add_action( 'add_meta_boxes', array($this, 'add_meta_boxes') );
			
		/*
		 * [1] TODO: change this for plugin admin pages
		 */
		 $this->menu_pages = array ( array (
					'page_title' => __('Checkout Fields', "cfom"),
					'menu_title' => __('Checkout Fields', "cfom"),
					'cap' => 'manage_options',
					'slug' => "cfom",
					'callback' => 'product_meta',
					'parent_slug' => 'woocommerce' 
			),
		);
		
		add_action ( 'admin_menu', array (
				$this,
				'add_menu_pages' 
		) );
		
		
		// Get PRO version
		add_action('cfom_after_cfom_field_admin', 'cfom_admin_rate_and_get', 10);
		
		add_action( 'admin_notices', array($this, 'cfom_admin_show_notices') );


		// Save PDF settings hook
        add_action( 'wp_ajax_cfom-save-pdf-setting-meta', array($this, 'save_pdf_settings') );
        add_action( 'wp_ajax_nopriv_cfom-save-pdf-setting-meta', array($this, 'save_pdf_settings' ) );
	}
	
	/* 
	**========== Set defualt sections =========== 
	*/
	function set_default_fields() {

		$return_url = admin_url( 'admin.php?page=cfom' );
		if( ! current_user_can('administrator') ) wp_redirect($return_url);
		
		if( ! isset($_REQUEST['cfom_reset_section']) ) wp_redirect($return_url);
		
		$response = array();
		
		$section = $_REQUEST['cfom_reset_section'];
		
		$response = cfom_reset_section_to_default($section);
		
    	wp_send_json($response);
    	exit;
	}
	
	// Setting is_active states
	function set_active_state() {
		
		$return_url = admin_url( 'admin.php?page=cfom' );
		if( ! current_user_can('administrator') ) wp_redirect($return_url);
		
		if( ! isset($_GET['cfom_is_active']) ) wp_redirect($return_url);
		
		$is_active		= sanitize_text_field( $_GET['cfom_is_active'] );
		$section_id 	= sanitize_text_field( $_GET['cfom_section_id'] );
		$section_title	= sanitize_text_field( $_GET['cfom_section_title'] );
		$section_type	= sanitize_text_field( $_GET['cfom_section_type'] );
		
		$response = array();
		
		// Check if PRO version installed
		// Only Billing and Shipping are allowed in Basic version
		// Well let it be free ::)
		/*if( ! cfom_pro_is_installed() && $is_active == 'yes' ) {
			
			if( $section_type !== 'billing' || $section_type == 'shipping')
			$response[] = array('class'=>'updated', 
                       'message'=> sprintf(__("Only Shipping and Billing sections are allowed in Free. Update to <a href='%s'>Pro Version</a>.", "cfom"),
                       cfom_pro_url()));
		
			set_transient("cfom_default_meta_loaded", $response, 30);
	    	wp_redirect($return_url);
	    	exit;
		}*/
		
		global $wpdb;
		$cfom_table = $wpdb->prefix.CFOM_TABLE_META;
		
		$wpdb->query( $wpdb->prepare( 
			"
			UPDATE {$cfom_table} 
			SET is_active = %s
			WHERE section_id = %d
			",
		        $is_active, $section_id
		) );
		
		$active_state = $is_active == 'yes' ? 'Enabled' : 'Disabled';
		
		$response[] = array('class'=>'updated', 
                       'message'=> sprintf(__("%s is now %s.", "cfom"),
                       $section_title, $active_state));
		
		set_transient("cfom_default_meta_loaded", $response, 30);
    	wp_redirect($return_url);
    	exit;
	}


	/* 
    **========== Save PDF Setting Callback =========== 
    */
    function save_pdf_settings(){

        // If this is a revision, don't send the email.
        if ( wp_is_post_revision( $post_id ) )
            return;

        if ( isset($_POST['cfom-pdf-header-html']) || isset($_POST['cfom-pdf-footer-html'])) {
            
            $header = stripslashes($_POST['cfom-pdf-header-html']);
            $footer = stripslashes($_POST['cfom-pdf-footer-html']);
            
            update_option('cfom_pdf_header_meta', $header);
            update_option('cfom_pdf_footer_meta', $footer);

            $response = array( 'status'=> 'success', 
                               'message' => sprintf(__('%s', 'cfom'), "Settings Saved Successfully")
                         );

        }else{
            $response = array(  'status'=> 'error', 
                                'message' => sprintf(__('%s', 'cfom'), "No Changing Founds")
                         );            
        }

         wp_send_json($response); 
    }

	
	/* 
	**========== Add metaboex for render checkout fields meta in order =========== 
	*/
	function add_meta_boxes() {
        add_meta_box( 'cfom_checkout_fields', 
                        __('Checkout Extra Fields','cfom'), 
                        array($this, "render_section"), 
                        'shop_order', 'normal', 'default' );
    }
    
    /* 
	**========== Metaboxe callback function=========== 
	*/
    function render_section( $order ) {
        
        // load style file
        wp_enqueue_style('cfom-order-css', CFOM_URL."/css/cfom-order.css");

        $cfom_order = new CFOM_Order( $order->ID );
        $order_meta = $cfom_order->display_order_meta('order');

        echo $order_meta;
    }
	
	/* 
	**========== Admin notices show =========== 
	*/
	function cfom_admin_show_notices() {
		
		if ( $resp_notices = get_transient( "cfom_default_meta_loaded" ) ) {
			foreach($resp_notices as $notice) {
			?>
			<div id="message" class="<?php echo $notice['class']; ?> updated notice is-dismissible">
				<p><?php echo $notice['message']; ?></p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text"><?php _e( 'Dismiss this notice', 'cfom' ); ?></span>
				</button>
			</div>
		<?php
		
		    delete_transient("cfom_default_meta_loaded");
			}
		}
	}
	
	/* 
	**========== Creating menu page =========== 
	*/
	function add_menu_pages() {
		
		if( !$this->menu_pages ) return '';
		
		foreach ( $this->menu_pages as $page ) {
			
			if ($page ['parent_slug'] == '') {
				
				$menu = add_options_page ( __ ( $page ['page_title'] , "cfom" ), __ ( $page ['menu_title'], "cfom" ), $page ['cap'], $page ['slug'], array (
						$this,
						$page ['callback'] 
				), $this->plugin_meta ['logo'], $this->plugin_meta ['menu_position'] );
			} else {
				
				$menu = add_submenu_page ( $page ['parent_slug'], __ ( $page ['page_title'], "cfom" ), __ ( $page ['menu_title'] , "cfom" ), $page ['cap'], $page ['slug'], array (
						$this,
						$page ['callback'] 
				) );
			}
		}
	}
	
	/* 
	**========== Render fields meta  =========== 
	*/
	function product_meta() {
		
		echo '<div class="cfom-admin-wrap woocommerce">';
		
		if ((!isset ( $_REQUEST ['cfom_id']))){
			echo '<h3 class="cfom-checkout-title">' . __ ( 'N-Media WooCommerce Checkout Fields Option Manager', "cfom" ) . '</h3>';
		}
		
		$do_meta	= (isset($_REQUEST ['do_meta']) ? sanitize_key($_REQUEST ['do_meta']) : '');
		$action 	= (isset($_REQUEST ['action']) ? sanitize_key($_REQUEST ['action']) : '');
		$cfom_id	= isset($_REQUEST ['action']) ? intval($_REQUEST ['cfom_id']) : null;
		
		if ($do_meta == 'edit' || $action == 'new') {
			cfom_load_template ( 'admin/cfom-fields.php' );
		} elseif ( $do_meta == 'clone') {
			$this -> clone_product_meta($cfom_id);
		}
		
		// existing meta group tables show only ppom main page
		if ( $action != 'new' && $do_meta != 'edit' ) {

			if (cfom_pro_is_installed()) {
				cfom_load_template ( 'admin/cfom-pdf-settings.php' );
			}
			cfom_load_template ( 'admin/existing-meta.php' );
		}
		
		echo '</div>';
	}
	
	/* 
	**========== Plugin validation =========== 
	*/
	function validate_plugin(){
		
		echo '<div class="wrap">';
		echo '<h2>' . __ ( 'Provide API key below:', "cfom" ) . '</h2>';
		echo '<p>' . __ ( 'If you don\'t know your API key, please login into your: <a target="_blank" href="http://wordpresspoets.com/member-area">Member area</a>', "cfom" ) . '</p>';
		
		echo '<form onsubmit="return validate_api_wooproduct(this)">';
			echo '<p><label id="plugin_api_key">'.__('Entery API key', "cfom").':</label><br /><input type="text" name="plugin_api_key" id="plugin_api_key" /></p>';
			wp_nonce_field();
			echo '<p><input type="submit" class="button-primary button" name="plugin_api_key" /></p>';
			echo '<p id="nm-sending-api"></p>';
		echo '</form>';
		
		echo '</div>';
	}
}