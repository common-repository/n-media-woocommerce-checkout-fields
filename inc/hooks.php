<?php
/**
 * handling all hooks callbacks in future
 * @since 8.0
 **/

if( ! defined("ABSPATH") ) die("Not Allowed");


/** =============== CFOM ============== **/


// Adding some hidden fields on checkout page
function cfom_hook_hidden_fields() {

	// Manage conditional hidden fields to skip validation
	echo '<input type="hidden" name="cfom[conditionally_hidden]" id="conditionally_hidden">';
	// Option price hidden input: cfom-price.js
	echo '<input type="hidden" name="cfom_option_fees" id="cfom_option_fees">';
}

function cfom_hooks_render_fields() {
	
	$action_name = current_action();
	$section = cfom_arrays_get_section_by_action( $action_name );
	
	// Disable billing and shipping TEMP
	// if( $section == 'billing' || $section == 'shipping' ) return;
	
	$cfom	= new CFOM_Meta();
	$cfom_fields = $cfom->get_section_fields($section);
    
    if( !$cfom_fields ) return '';
    
    if( ! $cfom->has_unique_datanames() ) {
		
		printf(__("<div class='error'>Some of your fields has duplicated datanames, please fix it</div>"), 'cfom');
		return;
	}
	
	$section_title = '';
	// Disabling section title option
	/*$section_settings = cfom_get_settings_by_type( $section );
	if( isset($section_settings->show_title) && $section_settings->show_title ) {
		$section_title = $section_settings->section_title != '' ? $section_settings->section_title  : '';
	}*/
	
	cfom_render_checkout_fields( $cfom_fields, $section, $section_title );
}

/*** ============= RENDER FIELDS ============= ***/

function cfom_render_checkout_fields($fields, $section, $section_title) {
	
	// action - third party space
	do_action('cfom_before_section_fields', $section, $fields);
	
	$cfom_html = '<div id="cfom-'.esc_attr($section).'" class="cfom-wrapper">';
	
	if( $section_title ) {
		printf(__("<h3 class='cfom-section-header'>%s</h3>",'cfom'), $section_title);
	}

    
    // cfom_pa($fields);
    $template_vars = array(	'cfom_fields_meta'	=> $fields,
    						'section'			=> $section,);
    						
	ob_start();
    cfom_load_template ( 'render-fields.php', $template_vars );
    $cfom_html .= ob_get_clean();
	
    
	// Clear fix
	$cfom_html .= '<div style="clear:both"></div>';   // Clear fix
	$cfom_html .= '</div>';   // Ends cfom-wrappper
	
	echo apply_filters('cfom_render_fields_html', $cfom_html);
}

function cfom_hooks_create_order( $order, $data ) {
	
	if( !isset($_POST['cfom']) ) return;
	
	// Extract checkout fields from POST
	$checkout_form_fields = cfom_extract_checkout_form_fields($_POST);
	
	// cfom_pa($checkout_form_fields); exit;
	
	// Saving checkout fields
	foreach($checkout_form_fields as $section => $fields) {
		
		foreach ( $fields as $key => $value ) {
			
			if ( is_callable( array( $order, "set_{$key}" ) ) ) {
				$order->{"set_{$key}"}( $value );
			} else{
				// Non core fields will be saved with cfom_ prex
				$order_prefix = apply_filters('cfom_order_prefix', 'cfom_', $order, $data);
				$order->update_meta_data( $order_prefix.$key, $value );
			}
			
			
		}
	}

	// Finally saving cfom fields all in ONE
	/*$cfom_key = "cfom_all_fields";
	$order->update_meta_data($cfom_key, $checkout_form_fields);	*/
}

// Moving images to order directory
function cfom_hooks_move_images( $order_id, $data, $order ) {
	
	if( !isset($_POST['cfom']) ) return;
	
	// Extract checkout fields from POST
	$checkout_form_fields = cfom_extract_checkout_form_fields($_POST);
	// cfom_pa($checkout_form_fields); exit;
	
	$readable_meta = array();
	$cfom		= new CFOM_Meta();
	
	// Saving checkout fields
	foreach($checkout_form_fields as $section => $fields) {
		
		foreach ( $fields as $key => $value ) {
			
			// Move file/cropp input under order directory
			$files_moved = cfom_files_move_inside_order_directory($key, $value, $order_id);
			if( !empty($files_moved) ) {
				$file_meta_key = "_{$key}_meta";
				update_post_meta($order_id, $file_meta_key, $files_moved);
			}
			
			// Creating meta array with labels
			if( $cfom->all_fields ) {
				foreach($cfom->all_fields as $field) {
	                $title = isset($field['title']) ? stripcslashes($field['title']) : $key;
	                if( !empty($field['data_name']) && sanitize_key($field['data_name']) == $key) {
	                	
	                	$field_meta = array('title'	=> $title, 
	                						'value' => $value,
	                						'display'	=> '',
	                						);
	                						
	    		        $readable_meta[$section][$key] = apply_filters('cfom_meta_value', $field_meta, $field, $key, $value, $section, $order_id);
	                }
	    		}
			}
		}
	}
	
	// cfom_pa($readable_meta); exit;
	// Finally saving cfom fields all in ONE
	$cfom_key = "cfom_all_fields_readable";
	update_post_meta($order_id, $cfom_key, $readable_meta);
}

// Addint CFOM fields into WC Checkout post_data
function cfom_hooks_add_checkout_data( $post_data ) {
	
	$checkout_posted_data = cfom_extract_checkout_form_fields( $_POST );

	// Billing Data
	if( isset($checkout_posted_data['billing']) ) {
		foreach($checkout_posted_data['billing'] as $key => $value) {
			$post_data[ $key ] = $value;
		}
	}
	
	// Shipping Data
	if( isset($checkout_posted_data['shipping']) ) {
		foreach($checkout_posted_data['shipping'] as $key => $value) {
			$post_data[ $key ] = $value;
		}
	}
	
	// Order Comments
	if( isset($checkout_posted_data['after_order']) ) {
		foreach($checkout_posted_data['after_order'] as $key => $value) {
			$post_data[ $key ] = $value;
		}
	}
	
	// cfom_pa($post_data);
	return apply_filters('cfom_posted_data', $post_data);
}

function cfom_hooks_validation_before_checkout() {
	
	// ppom_pa($posted_data);
	cfom_check_validation($_POST);
	// $err_level = error_reporting();
	// error_reporting( 0 );
	// error_reporting( $err_level );
}


function cfom_hooks_update_cart_fee( $cart ) {
	
	if ( is_admin() && ! defined( 'DOING_AJAX' ) )
	return;
	
	if ( isset( $_POST['post_data'] ) ) {
        parse_str( $_POST['post_data'], $post_data );
    } else {
        $post_data = $_POST; // fallback for final checkout (non-ajax)
    }

	// cfom_pa($post_data);
	if( !isset($post_data['cfom_option_fees']) || $post_data['cfom_option_fees'] == '' ) return;
	
    // Getting option price
	$extra_fees = json_decode( stripslashes($post_data['cfom_option_fees']), true);
	if( !$extra_fees ) return;
	
    foreach($extra_fees as $fee) {
    	
    	$label = isset($fee['label']) ? $fee['label'] : '';
    	$price = isset($fee['price']) ? $fee['price'] : '';
    	
    	if( $price == 0 ) continue;
    	
    	WC()->cart->add_fee( $label, $price );
    	
    }

}

// While rendering fields return attributes for fields
function cfom_hooks_set_attributes($field_meta, $type) {
	
	$cfom_attribtues = array();
	
	$cfom_attribtues['data-errormsg']  = isset($field_meta['error_message']) ? cfom_wpml_translate($field_meta['error_message'], 'cfom') : null;
	
	switch( $type ) {
	    
	    case 'text':
	        
	        $cfom_attribtues['maxlength'] = isset($field_meta['maxlength']) ? $field_meta['maxlength'] : null;
	        $cfom_attribtues['minlength'] = isset($field_meta['minlength']) ? $field_meta['minlength'] : null;
	        break;
	        
	   case 'textarea':
	        
	        $cfom_attribtues['maxlength'] = isset($field_meta['max_length']) ? $field_meta['max_length'] : null;
	        break;
	        
	        
	   case 'number':
	        
	        $cfom_attribtues['min'] = isset($field_meta['min']) ? $field_meta['min'] : null;
	        $cfom_attribtues['max'] = isset($field_meta['max']) ? $field_meta['max'] : null;
	        $cfom_attribtues['step'] = isset($field_meta['step']) ? $field_meta['step'] : null;
	        break;
	        
	}
	
	return $cfom_attribtues;
}


function cfom_hooks_input_args($field_setting, $field_meta) {
    
    if($field_setting['type'] == 'date' && $field_meta['jquery_dp'] == 'on') {
        $field_setting['type'] = 'text';
        $field_setting['past_date'] = isset($field_meta['past_date']) ? $field_meta['past_date'] : '';
        $field_setting['no_weekends'] = isset($field_meta['no_weekends']) ? $field_meta['no_weekends'] : '';
    }
    
    // Adding conditional field
    if( isset($field_meta['logic']) && $field_meta['logic'] == 'on' ){
    	$field_setting['conditions'] = $field_meta['conditions'];
    }
    
    // Adding min/max for number input
    if( $field_setting['type'] == 'number' ) {
        $field_setting['min'] = !empty($field_meta['min']) ? $field_meta['min'] : '';
        $field_setting['max'] = !empty($field_meta['max']) ? $field_meta['max'] : '';
    }
    
    
    return $field_setting;
}

function cfom_hooks_color_to_text_type($attr_value, $attr, $args) {
	
	if( $attr == 'type' && $attr_value == 'color' ) {
		$attr_value = 'text';
	}
	
	return $attr_value;
}

function cfom_hooks_input_wrapper_class($input_wrapper_class, $field_meta) {
	
	if( isset($field_meta['logic']) && $field_meta['logic'] != 'on' ) 
		return $input_wrapper_class;
		
	$field_id = $field_meta['id'];
	
	$input_wrapper_class .= " cfom-input-{$field_id}";
	
	/**
	 * If conditional field then add class
	 * cfom-c-hide: if field need to be hidden with condition
	 * cfom-c-show: if field need to be visilbe with condition
	 * */
	// cfom_pa($field_meta);
	if( isset($field_meta['conditions']) ) {
		if( $field_meta['conditions']['visibility'] == 'Show') {
			$input_wrapper_class .= ' cfom-c-hide';
		} else {
			$input_wrapper_class .= ' cfom-c-show';
		}
	}
	
	return $input_wrapper_class;
}


// Changing country option key to Country code
function cfom_change_country_select_value($option_key, $option, $meta) {
	
	if( isset($meta['type']) && $meta['type'] == 'country')
	{
		$option_key = $option['id'];
	}
	
	return $option_key;
}

// Adding enable_field key in meta before saving
function cfom_hook_add_enable_field($fields) {
	
	if( ! $fields ) return $fields;
	
	$new_fields = array();
	foreach($fields as $field) {
		
		if( ! isset($field['enable_field']) ) {
			$field['enable_field'] = '';
		}
		
		$new_fields[] = $field;
	}
	
	return $new_fields;
		
}
/** =============== CFOM ============== **/


// Saving Cropped image when posted from product page.
function cfom_hooks_save_cropped_image( $cfom_fields, $posted_data ) {
	
	$product_id = $posted_data['add-to-cart'];
	// var_dump($product_id);
	$cropped_fields = cfom_has_field_by_type($product_id, 'cropper');
	if( empty($cropped_fields) ) return $cfom_fields;
	
	$cropper_found = array();
	foreach($cropped_fields as $cropper) {
		
		if( isset($cfom_fields['fields'][$cropper['data_name']]) ) {
			
			$cropper_found = $cfom_fields['fields'][$cropper['data_name']];
			foreach($cropper_found as $file_id => $values) {
				
				if( empty($values['cropped']) ) continue;
				
				$image_data = $values['cropped'];
				$file_name	= isset($values['org']) ? $values['org'] : '';
				$file_name	= cfom_file_get_name($file_name, $product_id);
				cfom_save_data_url_to_image($image_data, $file_name);
			}
			// Saving cropped data to image
		}
	}
	
	// cfom_pa($cropper_found); exit;
	
	return $cfom_fields;
}

// Convert option price if currency swithcer found
function cfom_hooks_convert_price( $option_price ) {
	
	if( has_filter('woocs_exchange_value') && !empty($option_price) ) {
		global $WOOCS;
		if($WOOCS->current_currency != $WOOCS->default_currency) {
			$option_price = apply_filters('woocs_exchange_value', $option_price);
		}
	}
	
	return $option_price;
}

// Converting currency back to default currency rates due to WC itself converting these
// Like for cart line total, fixed fee etc.
function cfom_hooks_convert_price_back( $price ) {
	
	if( has_filter('woocs_exchange_value') ) {
		
		global $WOOCS;
		// var_dump($WOOCS->is_multiple_allowed);
		if($WOOCS->current_currency != $WOOCS->default_currency && ! $WOOCS->is_multiple_allowed) {
			
			// cfom conver all prices into current currency, but woocommerce also
			// converts cart prices to current, so have to get our currencies back to default rates
			$set_currencies 	= $WOOCS->get_currencies();
			$current_currency_rate = $set_currencies[$WOOCS->current_currency]['rate'];
			$price	= $WOOCS->back_convert($price, $current_currency_rate);
		}
	}
	
	
	return $price;
}

function cfom_hooks_checkbox_validation($has_value, $posted_fields, $field) {
	
	if ( $field['type'] != 'checkbox' ) return $has_value;
	
	
	if( (!empty($field['max_checked']) || !empty($field['min_checked'])) && empty($field['required']) ) {
		$has_value = true;
	}
	
	if ( ! $has_value && empty($field['required'])) return $has_value;
	
	$data_name = $field['data_name'];
	$max_checked = isset($posted_fields[$data_name]) ? count($posted_fields[$data_name]) : 0;
	
	
	if ( !empty($field['max_checked']) && $max_checked > intval($field['max_checked']) ) {
		$has_value = false;
	}
	
	if ( !empty($field['min_checked']) && $max_checked < intval($field['min_checked']) ) {
		$has_value = false;
	}
	
		
	return $has_value;
}

/**
 * registration meta in wmp for translation
 * @TODO: need to connect with CFOM Meta Data Saving
 **/
function cfom_hooks_register_wpml( $meta_data ) {
	

	foreach($meta_data as $index => $data) {
		
		// If Dataname is not provided then generate it.
		$data['data_name'] = empty($data['data_name']) ? sanitize_key($data['title']) : $data['data_name'];
		
		// title 
		if( isset($data['title']) ) {
			
			nm_wpml_register($data['title'], 'cfom');
		}
		
		// description
		if( isset($data['description']) ) {
		
			nm_wpml_register($data['description'], 'cfom');
		}
		
		// error_message
		if( isset($data['error_message']) ) {
			
			nm_wpml_register($data['error_message'], 'cfom');
		}
		
		// options (select, radio, checkbox)
		if( isset($data['options']) && is_array($data['options']) ) {
			
			$new_option = array();
			
			// If Option ID is not provided then generate it.
			foreach($data['options'] as $option){
				
				nm_wpml_register($option['option'], 'cfom');
				
				$option['id']	= cfom_get_option_id($option);
				$new_option[]	= $option;
				
			}
			
			$data['options'] = $new_option;
			
		}
		
		$meta_data[$index] = $data;
		
	}
	
	// cfom_pa($meta_data); exit;
	return $meta_data;
}