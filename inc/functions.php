<?php
/*
 * this file contains pluing meta information and then shared
 * between pluging and admin classes
 * * [1]
 */

if( ! defined('ABSPATH') ) die('Not Allowed.');

function cfom_check_validation($post_data, $passed=true) {
	
	$cfom		= new CFOM_Meta();
	if( ! $cfom->all_fields ) return $passed;
	
	// Extract checkout fields from POST
	$checkout_form_fields = cfom_extract_checkout_form_fields($post_data);
	// cfom_pa($checkout_form_fields); exit;
	
	if( empty($checkout_form_fields) ) return $passed;
	
	foreach($cfom->all_fields as $field) {
		
		// cfom_pa($field);
		
		if( empty($field['data_name']) || empty($field['required']) 
		&& (empty($field['min_checked']) && empty($field['max_checked']) )
		) continue;
		
		
		$data_name	= sanitize_key($field['data_name']);
		$title		= isset($field['title']) ? $field['title'] : '';
		$type		= isset($field['type']) ? $field['type'] : '';
		$field_section	= isset($field['section']) ? $field['section'] : '';
		
		if( ! cfom_is_field_visible( $field ) ) continue;
		
		// Ship to different address is disabled (Use billing address only)
		// For shipping fields only
		if( $field_section == 'shipping' && !isset($post_data['ship_to_different_address']) ) continue;
		
		// No need to show section title on error message
		/*if( !empty($field_section) ) {
			$section_title = cfom_get_section_default_title($field_section);
			$title = "{$section_title} {$title}";
		} */
		
		// Check if field is required by hidden by condition
		if( cfom_is_field_hidden_by_condition($data_name) ) continue;
		
		if( ! cfom_has_posted_field_value($checkout_form_fields, $field) ) {
			
			// Note: Checkbox is being validate by hook: cfom_has_posted_field_value
			
			$error_message = (isset($field['error_message']) && $field['error_message'] != '') ? $title.": ".$field['error_message'] : "<strong>{$title}</strong> is a required field";
			$error_message = sprintf ( __ ( '%s', 'cfom' ), $error_message );
			$error_message = stripslashes ($error_message);
			cfom_wc_add_notice( $error_message );
			$passed = false;
		}
	}
		
	/*var_dump($passed);
	cfom_pa($post_data); exit;*/
	
	return $passed;
}

/**
 * Check if selected meta as input type included
 * return input: data_name
 * 
 **/
function cfom_has_posted_field_value( $posted_fields, $field ) {
	
	$has_value = false;
	
	$data_name = $field['data_name'];
	
	if( !empty($posted_fields) ) {
		foreach( $posted_fields as $section => $fields) {
			foreach($fields as $field_key => $value){
				
				if( $field_key == $data_name) {
					
					if( $value != '' ) {
						$has_value = true;
					}
					
					if( $has_value ) break;
				}
			}
		}
	}
	
	return apply_filters('cfom_has_posted_field_value', $has_value, $posted_fields, $field);
}

function cfom_extract_checkout_form_fields( $posted_data ) {
	
	$cfom_data = array();
	foreach(cfom_get_sections() as $section_type => $section_title) {
	
		if( isset($posted_data['cfom'][$section_type]) ) {
			
			foreach($posted_data['cfom'][$section_type] as $key => $value) {
				$cfom_data[$section_type][$key] = $value;
			}
		}	
	}
	
	if ( !isset($posted_data['ship_to_different_address']) && ( WC()->cart->needs_shipping_address() || wc_ship_to_billing_address_only() ) ) {
		$section = 'shipping';
	    $cfom	= new CFOM_Meta();
		$shipping_fields	= $cfom->get_core_checkout_fields($section);
		
		if( $shipping_fields ) {
			foreach ( $shipping_fields as $key => $field ) {
				$cfom_data[$section][$key] = isset( $cfom_data['billing'][ 'billing_' . substr( $key, 9 ) ] ) ? $cfom_data['billing'][ 'billing_' . substr( $key, 9 ) ] : '';
			}
		} else // if shipping fields not defined by CFOM - Loading core
		{
			$shipping_fields = cfom_core_shipping_meta();
			foreach ( $shipping_fields as $field ) {
				$key = $field['data_name'];
				$cfom_data[$section][$key] = isset( $cfom_data['billing'][ 'billing_' . substr( $key, 9 ) ] ) ? $cfom_data['billing'][ 'billing_' . substr( $key, 9 ) ] : '';
			}
		}
	}
	// cfom_pa($cfom_data);
	
	// Order comments
	if( !empty($posted_data['order_comments']) ) {
		$cfom_data['order_comments']['order_comments'] = $posted_data['order_comments'];
	}
	// cfom_pa($cfom_data);
	return apply_filters('cfom_extract_checkout_form_data', $cfom_data, $posted_data);
}

// Get Section Title by type
function cfom_get_section_default_title( $section_type ) {
	
	$cfom_sections = cfom_get_sections();
	
	$section_title = '';
	foreach($cfom_sections as $section => $title) {
		
		if( $section_type == $section ) {
			$section_title = $title;
		}
	}
	
	return apply_filters('cfom_section_title_by_type', $section_title, $section_type);
}

// enqueu required scripts/css for inputs
function cfom_hooks_load_input_scripts() {
    
    $cfom		= new CFOM_Meta();
	if( ! $cfom->all_fields ) return '';
	
    $cfom_meta_fields = $cfom->all_fields;
    
    $cfom_inputs        	= array();
    $cfom_conditional_fields= array();
    $croppie_options		= array();
    $cfom_core_scripts  	= array('jquery');
    $show_price_per_unit	= false;
    
    // Font-awesome
	wp_enqueue_style( 'prefix-font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css' );
    
    // Price display controller
	wp_enqueue_script( 'cfom-price', CFOM_URL.'/js/cfom-price.js', array('jquery','cfom-inputs'), CFOM_DB_VERSION, true);
		
	// cfom_pa($cfom_meta_fields);
	
    foreach($cfom_meta_fields as $field){
		
		
		$type			= $field['type'];
		$title			= ( isset($field['title']) ? $field ['title'] : '');
		$data_name		= ( isset($field['data_name']) ? $field ['data_name'] : $title);
		$data_name		= sanitize_key( $data_name );
		
		// var_dump($field['options']);
		if( isset($field['options']) && $type != 'bulkquantity') {
			$field['options'] = cfom_convert_options_to_key_val($field['options'], $field);
		}
		
		// Allow other types to be hooked
		$type = apply_filters('cfom_load_input_script_type', $type, $field);
		
		switch( $type ) {
		    
		    case 'text':
		    	if( !empty($field['input_mask']) ) {
                	//Enqueue masking script
			    	$cfom_mask_api = CFOM_URL . '/js/inputmask/jquery.inputmask.bundle.js';
    	        	wp_enqueue_script( 'cfom-inputmask', $cfom_mask_api, array('jquery'), CFOM_VERSION, true);
                }
		    	
            	break;
            	
		   	case 'color':
				
				// Check if value is in GET 
				if( !empty($_GET[$data_name]) ) {
					
					$field['default_color'] = sanitize_file_name($_GET[$data_name]);
				}
				
				
				$cfom_iris_api = CFOM_URL . '/js/color/Iris/dist/iris.js';
    	        wp_enqueue_script( 'cfom-iris', $cfom_iris_api, array('jquery','jquery-ui-core','jquery-ui-draggable', 'jquery-ui-slider'), CFOM_VERSION, true);
    	    	break;
    	    	
    	    case 'image':
				$cfom_tooltip = CFOM_URL . '/js/image-tooltip.js';
				wp_enqueue_script('cfom-zoom', $cfom_tooltip, array('jquery'), CFOM_VERSION, true);
    	    	break;
    	    	
    	    case 'pricematrix':
    	    	
    	    	if( isset($field['show_slider']) && $field['show_slider'] == 'on' ) {
	    	    	// Adding Bootstrap slider if slider is on
	    	    	$cfom_bs_slider_js = CFOM_URL . '/js/bs-slider/bootstrap-slider.min.js';
	    	    	$cfom_bs_slider_css = CFOM_URL . '/js/bs-slider/bootstrap-slider.min.css';
					wp_enqueue_script('cfom-bs-slider', $cfom_bs_slider_js, array('jquery'), CFOM_VERSION, true);
					wp_enqueue_style('cfom-bs-slider-css', $cfom_bs_slider_css);
    	    	}
    	    	
    	    	if( isset($field['show_price_per_unit']) && $field['show_price_per_unit'] == 'on' ) {
    	    		$show_price_per_unit = true;
    	    	}
    	    	break;
    	    	
    	    case 'palettes':
				
				
    	    	break;
    	    
    	    case 'cropper':
				$cfom_file_inputs[] = $field;
				
				
				$cfom_croppie_api	= CFOM_URL . '/js/croppie/node_modules/croppie/croppie.js';
		    	$cfom_cropper		= CFOM_URL . '/js/croppie/cfom-crop.js';
		    	$cfom_croppie_css	= CFOM_URL . '/js/croppie/node_modules/croppie/croppie.css';
		    
				$cfom_exif			= CFOM_URL . '/js/exif.js';
		        wp_enqueue_style( 'cfom-croppie-css', $cfom_croppie_css);
		        // Croppie options
				$croppie_options[$data_name]	= cfom_get_croppie_options($field);
		        
		        wp_enqueue_script( 'cfom-croppie', $cfom_croppie_api, '', CFOM_VERSION);
		        wp_enqueue_script( 'cfom-exif', $cfom_exif, '', CFOM_VERSION);
		        
		        
		        // wp_enqueue_script( 'cfom-croppie2', $cfom_cropper, array('jquery'), CFOM_VERSION);
    	        
    	        wp_enqueue_script( 'cfom-file-upload', CFOM_URL.'/js/file-upload.js', array('jquery', 'plupload','cfom-price'), CFOM_VERSION, true);
    	    	$plupload_lang = !empty($field['language']) ? $field['language'] : 'en';
    	    	// wp_enqueue_script( 'pluploader-language', CFOM_URL.'/js/plupload-2.1.2/js/i18n/'.$plupload_lang.'.js');
				$cfom_file_vars = array('ajaxurl' => admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http') ),
										'plugin_url' => CFOM_URL,
										'file_upload_path_thumb' => cfom_get_dir_url(true),
										'file_upload_path' => cfom_get_dir_url(),
										'mesage_max_files_limit'	=> __(' files allowed only', 'cfom'),
										'file_inputs'		=> $cfom_file_inputs,
										'delete_file_msg'	=> __("Are you sure?", "cfom"),
										'plupload_runtime'	=> (cfom_if_browser_is_ie()) ? 'html5,html4' : 'html5,silverlight,html4,browserplus,gear',
										'croppie_options'	=> $croppie_options,
										);
				wp_localize_script( 'cfom-file-upload', 'cfom_file_vars', $cfom_file_vars);
    	    	break;
    	    	
    	    case 'file':
    	    	$cfom_file_inputs[] = $field;
    	    	
    	    	$file_upload_pre_scripts = array('jquery', 'plupload','cfom-price');
    	    
				wp_enqueue_script( 'cfom-file-upload', CFOM_URL.'/js/file-upload.js', $file_upload_pre_scripts,  CFOM_VERSION, true);
    	    	$plupload_lang = !empty($field['language']) ? $field['language'] : 'en';
    	    	// wp_enqueue_script( 'pluploader-language', CFOM_URL.'/js/plupload-2.1.2/js/i18n/'.$plupload_lang.'.js');
				$cfom_file_vars = array('ajaxurl' => admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http') ),
										'plugin_url' => CFOM_URL,
										'file_upload_path_thumb' => cfom_get_dir_url(true),
										'file_upload_path' => cfom_get_dir_url(),
										'mesage_max_files_limit'	=> __(' files allowed only', 'cfom'),
										'file_inputs'		=> $cfom_file_inputs,
										'delete_file_msg'	=> __("Are you sure?", "cfom"),
										'plupload_runtime'	=> (cfom_if_browser_is_ie()) ? 'html5,html4' : 'html5,silverlight,html4,browserplus,gear');
				wp_localize_script( 'cfom-file-upload', 'cfom_file_vars', $cfom_file_vars);
				
				break;
				
				
				case 'bulkquantity':
					
					$field['options'] = stripslashes($field['options']);
				break;
		}
		
			// Conditional fields
			if( isset($field['logic']) && $field['logic'] == 'on' && !empty($field['conditions']) ){
				
				$field_conditions = $field['conditions'];
				
				//WPML Translation
				$condition_rules = $field_conditions['rules'];
				$rule_index = 0;
				foreach($condition_rules as $rule) {
					// cfom_pa($rule);
					$field_conditions['rules'][$rule_index]['element_values'] = cfom_wpml_translate($rule['element_values'], 'cfom');
					$rule_index++;
				}
				
				$cfom_conditional_fields[$data_name] = $field_conditions;
			}
			
		/**
		 * creating action space to render hooks for more addons
		 **/
		 do_action('cfom_hooks_inputs', $field, $data_name);
		
    	$cfom_inputs[] = $field;
    }
    		
    
    // cfom_pa($cfom_conditional_fields);
    
    
    wp_enqueue_script( 'cfom-inputs', CFOM_URL.'/js/cfom.inputs.js', $cfom_core_scripts, CFOM_DB_VERSION, true);
	$cfom_input_vars = array('ajaxurl' => admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http') ),
							'cfom_inputs'		=> $cfom_inputs,
							'field_meta'		=> $cfom_meta_fields);
	wp_localize_script( 'cfom-inputs', 'cfom_input_vars', $cfom_input_vars);
	
	
	$cfom_input_vars['wc_thousand_sep']	= wc_get_price_thousand_separator();
	$cfom_input_vars['wc_currency_pos']	= get_option( 'woocommerce_currency_pos' );
	$cfom_input_vars['wc_decimal_sep']	= get_option('woocommerce_price_decimal_sep');
	$cfom_input_vars['wc_no_decimal']	= get_option('woocommerce_price_num_decimals');
	$cfom_input_vars['product_base_label'] = __("Product Price", "cfom");
	$cfom_input_vars['option_total_label'] = __("Option Total", "cfom");
	$cfom_input_vars['product_quantity_label'] = __("Product Quantity", "cfom");
	$cfom_input_vars['total_without_fixed_label'] = __("Total", "cfom");
	$cfom_input_vars['total_discount_label'] = __("Total Discount", "cfom");
	$cfom_input_vars['fixed_fee_heading'] = __("Fixed Fee", "cfom");
	$cfom_input_vars['price_matrix_heading'] = __("Discount Price", "cfom");
	$cfom_input_vars['per_unit_label'] = __("unit", "cfom");
	$cfom_input_vars['show_price_per_unit'] = $show_price_per_unit;
	$cfom_input_vars['text_quantity'] = __("Quantity","cfom");
	$cfom_input_vars['plugin_url'] = CFOM_URL;
	
	$cfom_input_vars = apply_filters('cfom_input_vars', $cfom_input_vars);
	
	wp_localize_script('cfom-price', 'cfom_input_vars', $cfom_input_vars);
	
	// Conditional fields
	if( !empty($cfom_conditional_fields) || apply_filters('cfom_enqueue_conditions_js', false)) {
		$cfom_input_vars['conditions'] = $cfom_conditional_fields;
		
		wp_enqueue_script( 'cfom-conditions', CFOM_URL.'/js/cfom-conditions.js', array('jquery','cfom-inputs'), CFOM_DB_VERSION, true);
		wp_localize_script('cfom-conditions', 'cfom_input_vars', $cfom_input_vars);	
	}
	
	
	// Country select
	wp_enqueue_script( 'cfom-country-select', CFOM_URL.'/js/cfom-country-select.js', array('jquery'), CFOM_DB_VERSION, true);
			
}

function cfom_load_bootstrap_css() {
	
	$return = true;
	
	return apply_filters('cfom_bootstrap_css', $return);
}

// Checking if give field name is core (billing, shipping, order_comments)
function cfom_is_checkout_core_field($field_dataname, $section_type=''){
	
	$return = false;
	$defult_meta = array();
	
	switch( $section_type ) {
            
        case 'billing':
            $defult_meta = cfom_core_billing_meta();
        break;
        
        case 'shipping':
            $defult_meta = cfom_core_shipping_meta();
        break;
        
        case 'after_order':
            $defult_meta = cfom_core_order_comments();
        break;
        
        // if not defined check it from all
        default:
        	$defult_meta = array_merge(cfom_core_billing_meta(), cfom_core_shipping_meta(), cfom_core_order_comments());
        break;
        
    }
    
    foreach ($defult_meta as $index => $meta) {
		$dataname = isset($meta['data_name']) ? $meta['data_name'] : '';
		if ($dataname == $field_dataname) {
			$return = true;
		}	
	}

	return $return;
}

// Check if field is Core WC address field
function cfom_get_core_field_wrapper_class( $field_name ) {
	
	$wrapper_class = '';
	
	if( ! $field_name ) return $wrapper_class;
	
	$core_address_fields = array(
							'billing_first_name'=> 'validate-required',
							'billing_last_name'	=> 'validate-required',
							'bill_country'		=> 'address-field update_totals_on_change validate-required',
							'billing_address_1'	=> 'address-field validate-required',
							'billing_address_2'	=> 'address-field',
							'billing_city'		=> 'address-field validate-required',
							'billing_state'		=> 'address-field validate-required validate-state',
							'billing_postcode'	=> 'address-field validate-required validate-postcode',
							'billing_phone'		=> 'validate-required validate-phone',
							'billing_email'		=> 'validate-required validate-email',
							'shipping_first_name'=> 'validate-required',
							'shipping_last_name'	=> 'validate-required',
							'bill_country'		=> 'address-field update_totals_on_change validate-required',
							'shipping_address_1'	=> 'address-field validate-required',
							'shipping_address_2'	=> 'address-field',
							'shipping_city'		=> 'address-field validate-required',
							'shipping_state'		=> 'address-field validate-required validate-state',
							'shipping_postcode'	=> 'address-field validate-required validate-postcode',
		);
		
	$wrapper_class = isset($core_address_fields[$field_name]) ? $core_address_fields[$field_name] : '';
	
	return apply_filters('cfom_core_fields_wrapper_classes', $wrapper_class);
}

function cfom_product_meta_all() {
		
	global $wpdb;
	
	$qry = "SELECT * FROM " . $wpdb->prefix . CFOM_TABLE_META;
	// $qry = "SELECT * FROM " . $wpdb->prefix . 'nm_personalized';
	$res = $wpdb->get_results ( $qry );
	
	return $res;
}

/**
 * check if browser is IE
 **/
function cfom_if_browser_is_ie()
{
	//print_r($_SERVER['HTTP_USER_AGENT']);
	
	if(!(isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))){
		return false;
	}else{
		return true;
	}
}

function cfom_pa($arr){
	
	echo '<pre>';
	print_r($arr);
	echo '</pre>';
}

// Get field column
function cfom_get_field_colum( $meta ) {
	
	$field_column = '';
	if( empty($meta['width']) ) return 12;
	
	// Check width has old settings
	if( strpos( $meta['width'], '%' ) !== false ) {
		
		$field_column = 12;
	} elseif( intval($meta['width']) > 12 ) {
		$field_column = 12;
	} else {
		$field_column = $meta['width'];
	}
	
	return apply_filters('cfom_field_col', $meta['width'], $meta);
}

function cfom_translation_options( $option ) {
	
	if( !isset($option['option']) ) return $option;
	
	$option['option'] = cfom_wpml_translate($option['option'], 'cfom');
	return $option;
}

/**
 * some WC functions wrapper
 * */
 

if( !function_exists('cfom_wc_add_notice')){
function cfom_wc_add_notice($string, $type="error"){
 	
 	global $woocommerce;
 	if( version_compare( $woocommerce->version, 2.1, ">=" ) ) {
 		wc_add_notice( $string, $type );
	    // Use new, updated functions
	} else {
	   $woocommerce->add_error ( $string );
	}
 }
}


/**
 * WPML
 * registering and translating strings input by users
 */
if( ! function_exists('cfom_wpml_register') ) {
	

	function cfom_wpml_register($field_value, $domain) {
		
		if ( ! function_exists ( 'icl_register_string' )) 
			return $field_value;
		
		$field_name = $domain . ' - ' . sanitize_key($field_value);
		//WMPL
	    /**
	     * register strings for translation
	     * source: https://wpml.org/wpml-hook/wpml_register_single_string/
	     */
	     
	     do_action( 'wpml_register_single_string', $domain, $field_name, $field_value );
	     
	    //WMPL
		}
}

if( ! function_exists('cfom_wpml_translate') ) {
	

	function cfom_wpml_translate($field_value, $domain) {
		
		$field_name = $domain . ' - ' . sanitize_key($field_value);
		//WMPL
	    /**
	     * register strings for translation
	     * source: https://wpml.org/wpml-hook/wpml_translate_single_string/
	     */
	    
	    $field_value = stripslashes($field_value);
		return apply_filters('wpml_translate_single_string', $field_value, $domain, $field_name );
		//WMPL
	}
}

/**
 * returning order id 
 * 
 * @since 7.9
 */
if ( ! function_exists('cfom_order_id') ) {
	function cfom_order_id( $order ) {
		
		$class_name = get_class ($order);
		if( $class_name != 'WC_Order' ) 
			return $order -> ID;
		
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {  
		
			// vesion less then 2.7
			return $order -> id;
		} else {
			
			return $order -> get_id();
		}
	}
}

// Return ammount after apply percent
function cfom_get_amount_after_percentage($base_amount, $percent) {
	
	$base_amount = floatval($base_amount);
	$percent_amount = 0;
	$percent		= substr( $percent, 0, -1 );
	$percent_amount	= wc_format_decimal( (floatval($percent) / 100) * $base_amount, wc_get_price_decimals());
	
	return $percent_amount;
}

function cfom_settings_link($links) {
	
	$quote_url = "https://najeebmedia.com/get-quote/";
	$cfom_setting_url = admin_url( 'admin.php?page=cfom');
	
	$cfom_links = array();
	$cfom_links[] = sprintf(__('<a href="%s">Add Fields</a>', 'cfom'), esc_url($cfom_setting_url) );
	
	if( ! cfom_pro_is_installed() ) {
		$cfom_links[] = sprintf(__('<a href="%s">Get PRO Version</a>', 'cfom'), esc_url(cfom_pro_url()) );
	}
	
	foreach($cfom_links as $link) {
		
  		array_push( $links, $link );
	}
	
  	return $links;
}

// Get field type by data_name
function cfom_get_field_meta_by_dataname( $data_name ) {
	
	$cfom		= new CFOM_Meta();
	if( ! $cfom->all_fields ) return '';
	
	$data_name = apply_filters('cfom_get_field_by_dataname_dataname', $data_name);
	
	$field_meta = '';
	foreach($cfom->all_fields as $field) {
	
		if( !cfom_is_field_visible($field) ) continue;
		
		if( !empty($field['data_name']) && sanitize_key($field['data_name']) == $data_name) {
			$field_meta = $field;
			break;
		}
	}
	
	return $field_meta;
}

function cfom_load_template($file_name, $variables=array('')){

	if( is_array($variables))
    extract( $variables );
    
   $file_path =  CFOM_PATH . '/templates/'.$file_name;
   if( file_exists($file_path))
   	include ($file_path);
   else
   	die('File not found'.$file_path);
}

function cfom_convert_options_to_key_val($options, $meta) {
	
	if( empty($options) ) return $options;
	
	// Do not change options for cropper
	// if( $meta['type'] == 'cropper' ) return $options;
	
	// cfom_pa($options);
	
	$cfom_new_option = array();
	foreach($options as $option) {
		
		if( isset($option['option']) ) {
			
			$option_price_without_tax	= '';
			$option_label	= $option['option'];
			$option_percent = '';
			
			$show_price		= isset($meta['show_price']) ? $meta['show_price'] : '';
			$data_name		= isset($meta['data_name']) ? $meta['data_name'] : '';
			
			$option_price	= isset($option['price']) ? $option['price'] : '';
			// This filter change prices for Currency switcher
			$option_price	= apply_filters('cfom_option_price', $option_price);
			
			// Price matrix discount
			$discount	= isset($meta['discount']) && $meta['discount'] == 'on' ? true : false;
			$discount_type	= isset($meta['discount_type']) ? $meta['discount_type'] : 'base';
			
			$cart_total_amount = WC()->cart->get_cart_total();
			// $show_option_price = apply_filters('cfom_show_option_price', $show_price, $meta);
			if( !empty($option_price) ) {
				
				// $option_price = $option['price'];
				
				// check if price in percent
				if(strpos($option_price,'%') !== false){
					$option_price = cfom_get_amount_after_percentage($cart_total_amount, $option_price);
					// check if price is fixed and taxable
					if(isset($meta['onetime']) && $meta['onetime'] == 'on' && $meta['onetime_taxable'] == 'on') {
						$option_price_without_tax = $option_price;
						// $option_price = cfom_get_price_including_tax($option_price, $product);
					}
					
					$option_label = $option['option'] . ' ('.cfom_price($option_price).')';
					$option_percent = $option['price'];
				} else {
					
					// check if price is fixed and taxable
					if(isset($meta['onetime']) && $meta['onetime'] == 'on' && $meta['onetime_taxable'] == 'on') {
						$option_price_without_tax = $option_price;
						// $option_price = cfom_get_price_including_tax($option_price, $product);
					}
					$option_label = $option['option'] . ' ('.cfom_price($option_price).')';
				}
				
			}
			
			$option_key = apply_filters('cfom_option_key', stripslashes($option['option']), $option, $meta);
			
			$option_id = cfom_get_option_id($option, $data_name);
			
			$cfom_new_option[$option_key] = array('label'	=> apply_filters('cfom_option_label', stripcslashes($option_label), $option, $meta), 
													'price'	=> $option_price,
													'raw'	=> $option_key,
													'without_tax'=>$option_price_without_tax,
													'percent'=> $option_percent,
													'option_id' => $option_id);
														
			if( $discount ) {
				$cfom_new_option[$option_key]['discount'] = $discount_type;
			}
			
			if( $meta['type'] == 'cropper' ) {
				
				$cfom_new_option[$option_key]['width'] = isset($option['width']) ? $option['width'] : '';
				$cfom_new_option[$option_key]['height'] = isset($option['height']) ? $option['height'] : '';
			}
			
		}
	}
	
	if( !empty($meta['first_option']) ) {
		$cfom_new_option[''] = array('label'=>sprintf(__("%s","cfom"),$meta['first_option']), 
										'price'	=> '',
										'raw'	=> '',
										'without_tax' => '');
	}
	
	// cfom_pa($cfom_new_option);
	return apply_filters('cfom_options_after_changes', $cfom_new_option, $options, $meta);
}

// Converting WC Countries in CFOM Countries with Key: option, price and id
function cfom_get_country_options() {
	
	$countries_obj	= new WC_Countries();
	
	if( apply_filters('cfom_use_shipping_countries', true) ) {
		$wc_countries   = $countries_obj->get_shipping_countries();
	}else{
		$wc_countries   = $countries_obj->get_countries();
	}
    
    $cfom_countries = array();
	foreach($wc_countries as $code => $country) {
		
		$cfom_countries[$code] = array('option'	=> $country,
								'price'		=> 0,
								'id'		=> $code);
	}
	
	return apply_filters('cfom_wc_countries_option', $cfom_countries);
}


// Retrun unique option ID
function cfom_get_option_id($option, $data_name=null) {
	
	$default_option = is_null($data_name) ? $option['option'] : $data_name.'_'.$option['option'];
	
	$option_id = empty($option['id']) ? $default_option : $option['id'];

	return apply_filters('cfom_option_id', sanitize_key( $option_id ), $option, $data_name );
}

// Check if field conditionally hidden
function cfom_is_field_hidden_by_condition( $field_name ) {
	
	if( !isset($_POST['cfom']['conditionally_hidden']) ) return false;
	
	$cfom_is_hidden = false;
	
	$cfom_hidden_fields = explode(",", $_POST['cfom']['conditionally_hidden']);
	// Remove duplicates
	$cfom_hidden_fields = array_unique( $cfom_hidden_fields );
	
	if( in_array($field_name, $cfom_hidden_fields) ) {
		
		$cfom_is_hidden = true;
	}
	
	return apply_filters('cfom_is_field_hidden_by_condition', $cfom_is_hidden);
}

// Return thumbs size
function cfom_get_thumbs_size() {
	
	return apply_filters('cfom_thumbs_size', '75px');
}

// Return file size in kb
function cfom_get_filesize_in_kb( $file_name ) {
		
	$base_dir = cfom_get_dir_path();
	$file_path = $base_dir . 'confirmed/' . $file_name;
	
	if (file_exists($file_path)) {
		$size = filesize ( $file_path );
		return round ( $size / 1024, 2 ) . ' KB';
	}elseif(file_exists( $base_dir . '/' . $file_name ) ){
		$size = filesize ( $base_dir . '/' . $file_name );
		return round ( $size / 1024, 2 ) . ' KB';
	}
	
}


// Meta display
function cfom_get_meta_display($key, $value, $field, $order_id) {
	
	$field_type = isset($field['type']) ? $field['type'] : '';
	
	$display = $value;
	switch( $field_type ) {
		
		case 'cropper':
		case 'file':
			$all_files = $value;
			if( $all_files ) {
				foreach($all_files as $id => $files) {
					
					$display = cfom_generate_html_for_files($files, $field_type, $order_id);
				}
			}
		break;
		
		case 'image':
			$display = cfom_generate_html_for_images( $value );
			
		break;
	}
	
	return apply_filters('cfom_meta_display', $display, $key, $value, $field, $order_id);
}


// Generating html for file input and cropper in order meta from filename
function cfom_generate_html_for_files( $files_array, $input_type, $order_id ) {
	
	$order_html = '<table>';
	foreach($files_array as $key => $file_name) {
		
			// Making file thumb download with new path
			$cfom_file_url = cfom_get_file_download_url( $file_name, $order_id );
			$cfom_file_thumb_url = cfom_is_file_image($file_name) ? cfom_get_dir_url(true) . $file_name : CFOM_URL.'/images/file.png';
			$order_html .= '<tr><td><a href="'.esc_url($cfom_file_url).'">';
			$order_html .= '<img class="img-thumbnail" style="width:'.esc_attr(cfom_get_thumbs_size()).'" src="'.esc_url($cfom_file_thumb_url).'">';
			$order_html .= '</a></td>';
			$order_html .= '<td><a class="button" href="'.esc_url($cfom_file_url).'">';
			$order_html .= __('Download File', 'cfom');
			$order_html .= '</a></td></tr>';
			
	}
	$order_html .= '</table>';
	
	return apply_filters('cfom_order_files_html', $order_html, $files_array, $input_type, $order_id);
}

// return html for images selected
function cfom_generate_html_for_images( $images ) {
	
	
	$ppom_html	=  '<table class="table table-bordered">';
	foreach($images as $id => $images_meta) {
		
		$images_meta	= json_decode(stripslashes($images_meta), true);
		$image_url		= stripslashes($images_meta['link']);
		$image_label	= $images_meta['title'];
		$image_html 	= '<img class="img-thumbnail" style="width:'.esc_attr(ppom_get_thumbs_size()).'" src="'.esc_url($image_url).'" title="'.esc_attr($image_label).'">';
		
		$ppom_html	.= '<tr><td><a href="'.esc_url($image_url).'" class="lightbox" itemprop="image" title="'.esc_attr($image_label).'">' . $image_html . '</a></td>';
		$ppom_html	.= '<td>' .esc_attr(ppom_files_trim_name( $image_label )) . '</td>';
		$ppom_html	.= '</tr>';
		
	}
	
	$ppom_html .= '</table>';
	
	return apply_filters('ppom_images_html', $ppom_html);
}

// Getting field option price
function cfom_get_field_option_price( $field_meta, $option_label ) {
	
	// var_dump($field_meta['options']);
	if( ! isset( $field_meta['options']) || $field_meta['type'] == 'bulkquantity' || $field_meta['type'] == 'cropper' ) return 0;
	
	$option_price = 0;
	foreach( $field_meta['options'] as $option ) {
		
		if( $option['option'] == $option_label && isset($option['price']) && $option['price'] != '' ) {
			
			$option_price = $option['price'];
		}
	}
	
	// For currency switcher
	$option_price = apply_filters('cfom_option_price', $option_price);
	
	return apply_filters("cfom_field_option_price", wc_format_decimal($option_price), $field_meta, $option_label);
}


// check if cfom PRO is installed
function cfom_pro_is_installed() {
	
	$return = false;
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'wc-checkout-fields-pro/wc-checkout-fields-pro.php' ) ) {
	  $return = true;
	} 
	return $return;
}


// CFOM pro version URL
function cfom_pro_url() {
	return 'https://najeebmedia.com/wordpress-plugin/woocommerce-checkout-field-editor-plugin/';
}

// Check if field is visible
function cfom_is_field_visible( $field ) {
	
	// if( ! cfom_pro_is_installed() ) return true;
	
	// cfom_pa($field);
	// If field is turned off (core fields)
	if( cfom_is_checkout_core_field($field['data_name']) ) {
		if( isset($field['enable_field']) && $field['enable_field'] != 'on' ) return false;
	}
	
	$visibility = isset($field['visibility']) ? $field['visibility'] : 'everyone';
	$visibility_role = isset($field['visibility_role']) ? $field['visibility_role'] : '';
	
	$is_visible = false;
	switch( $visibility ) {
		
		case 'everyone':
			$is_visible = true;
			break;
			
		case 'members':
			if( is_user_logged_in() ) {
				$is_visible = true;
			}
			break;
			
		case 'guests':
			if( ! is_user_logged_in() ) {
				$is_visible = true;
			}
			break;
			
		case 'roles':
			$role = cfom_get_current_user_role();
			$allowed_roles = explode(',', $visibility_role);
			
			if( in_array($role, $allowed_roles) ) {
				$is_visible = true;
			}
			break;
	}
	
	return apply_filters('cfom_is_field_visible', $is_visible, $field);
	
}

// Get logged in user role
function cfom_get_current_user_role() {
  
	if( is_user_logged_in() ) {
		$user = wp_get_current_user();
		$role = ( array ) $user->roles;
		return $role[0];
	} else {
		return false;
	}
}

// Retrun price with currency symbol but without html
function cfom_price( $price ) {
	
	$price					= floatval($price);
	
	$decimal_separator		= wc_get_price_decimal_separator();
	$thousand_separator		= wc_get_price_thousand_separator();
	$decimals				= wc_get_price_decimals();
	$price_format			= get_woocommerce_price_format();
	$negative       		= $price < 0;
	
	$wc_price = number_format( $price,$decimals, $decimal_separator, $thousand_separator );
	$formatted_price = ( $negative ? '-' : '' ) . sprintf( $price_format, get_woocommerce_currency_symbol(), $wc_price );
	return apply_filters('cfom_woocommerce_price', $formatted_price);
}



function cfom_get_date_formats() {
	
	$formats = array (
						'mm/dd/yy' => 'Default - mm/dd/yyyy',
						'dd/mm/yy' => 'dd/mm/yyyy',
						'yy-mm-dd' => 'ISO 8601 - yy-mm-dd',
						'd M, y' => 'Short - d M, y',
						'd MM, y' => 'Medium - d MM, y',
						'DD, d MM, yy' => 'Full - DD, d MM, yy',
						'\'day\' d \'of\' MM \'in the year\' yy' => 'With text - \'day\' d \'of\' MM \'in the year\' yy',
						'\'Month\' MM \'day\' d \'in the year\' yy' => 'With text - \'Month\' January \'day\' 7 \'in the year\' yy'
				);
				
	return apply_filters('cfom_date_formats', $formats);
}

/*
** ============== Get settings by meta id  ================= 
*/
function cfom_get_settings_by_id( $meta_id ) {
    
    global $wpdb;
    
	$qry = "SELECT * FROM " . $wpdb->prefix . CFOM_TABLE_META . " WHERE section_id = {$meta_id}";
	$meta_settings = $wpdb->get_row ( $qry );
	
	$meta_settings = empty($meta_settings) ? null : $meta_settings;
	
	return apply_filters('cfom_get_settings_by_id', $meta_settings, $meta_id);
}

function cfom_get_settings_by_type( $section_type ) {
    
    global $wpdb;
    
	$qry = "SELECT * FROM " . $wpdb->prefix . CFOM_TABLE_META . " WHERE section_type = '{$section_type}'";
	$meta_settings = $wpdb->get_row ( $qry );
	
	$meta_settings = empty($meta_settings) ? null : $meta_settings;
	
	return apply_filters('cfom_get_settings_by_type', $meta_settings, $section_type);
}

// Sanitizing meta array recursively
function cfom_sanitize_meta( &$array ) {

    foreach ($array as &$value) {   

        if( !is_array($value) ) 

            // sanitize if value is not an array
            $value = sanitize_text_field( $value );

        else

            // go inside this function again
            cfom_sanitize_meta($value);

    }

    return $array;

}