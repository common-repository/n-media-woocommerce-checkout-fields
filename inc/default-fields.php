<?php
/**
 * Set Checkout Default Sectoins on Activation and on Demand
**/

/* 
**========== Direct access not allowed =========== 
*/ 
if( ! defined("ABSPATH") ) die("Not Allowed");

/* 
**========== CFOM defualt ALL sections =========== 
*/ 
function cfom_create_all_default_sections() {
     
    if( cfom_is_default_section_created() ) return '';

    // Deleting existing section if found
    global $wpdb;
    $cfom_table = $wpdb->prefix.CFOM_TABLE_META;
    $delete = $wpdb->query("TRUNCATE TABLE `{$cfom_table}`");
     
    $response = array();
    
    // Creating default sections
    foreach(cfom_get_sections() as $section_type => $section_title) {
         
        $is_active     = '';
        $show_title    = 'yes';
        $cfom_css      = '';
        $conditions    = ''; // may used later
        $cfom_meta     = cfom_get_section_default_meta( $section_type );
         
		if( $section_type == 'billing' || $section_type == 'shipping' ) {
			$is_active = 'yes';
		}
         
        $section_data = array (
    			'section_type'      => $section_type,
    			'section_title'		=> $section_title,
                'is_active'			=> $is_active,
                'show_title'		=> $show_title,
                'cfom_css'			=> $cfom_css,
    			'conditions'    	=> $conditions,
    			'cfom_meta'         => json_encode ( $cfom_meta ),
    			'date_created'       => current_time ( 'mysql' ) 
    	        );
    	        
    	  cfom_save_section( $section_type, $section_data );
    	  $response[] = array(    'class'=>'updated', 
    	                       'message'=> sprintf(__("%s Default Fields Loaded.", "cfom"),
    	                       $section_title));
    }

    if( !empty($response) ) {
    	update_option('cfom_default_sections_created', true);
    }

    return $response;
}


// Resetting section to default fields
function cfom_reset_section_to_default( $section_type ) {
     
   $response = array();
    
    $is_active     = '';
    $show_title    = 'yes';
    $cfom_css      = '';
    $conditions    = ''; // may used later
    $cfom_meta     = cfom_get_section_default_meta( $section_type );
    $section_title = cfom_get_section_default_title( $section_type );
     
    $section_default_data = array (
			'section_type'      => $section_type,
			'section_title'		=> $section_title,
            'is_active'			=> $is_active,
            'show_title'		=> $show_title,
            'cfom_css'			=> $cfom_css,
			'conditions'    	=> $conditions,
			'cfom_meta'         => json_encode ( $cfom_meta ),
			'date_created'       => current_time ( 'mysql' ) 
	        );
	        
	  $section_default_data = apply_filters('section_default_data', $section_default_data, $section_type);
	        
	  cfom_update_section( $section_type, $section_default_data );
	  $response = array(    'class'=>'updated', 
	                       'message'=> sprintf(__("%s Section Reset Successfully", "cfom"),
	                       $section_title));

    return $response;
}
 
/* 
**========== CFOM sections saved =========== 
*/
function cfom_save_section( $section_type, $section_data ) {
      
    global $wpdb;
    $cfom_table = $wpdb->prefix.CFOM_TABLE_META;
    
    $format = array (
    		'%s',
    		'%s',
    		'%s',
            '%s',
    		'%s',
    		'%s',
    		'%s', 
    		'%s' 
    );
    
    $wpdb->insert($cfom_table, $section_data, $format);
    $res_id = $wpdb->insert_id;
}

// Updating to DB when resetting
function cfom_update_section( $section_type, $section_meta ) {
      
    global $wpdb;
    $cfom_table = $wpdb->prefix.CFOM_TABLE_META;
    
    $format = array (
			'%s',
			'%s',
			'%s',
            '%s',
			'%s',
			'%s',
			'%s'
	);
	
	$section_meta = apply_filters('CFOM_Meta_data_update', $section_meta);
	// cfom_pa($section_meta); exit;
	
	$where = array (
			'section_type' => $section_type 
	);
	
	$where_format = array (
			'%s' 
	);
	
	global $wpdb;
	$cfom_table = $wpdb->prefix.CFOM_TABLE_META;
	
	$rows_effected = $wpdb->update($cfom_table, $section_meta, $where, $format, $where_format);
	// $wpdb->show_errors(); $wpdb->print_error(); exit;
}
 
/* 
**========== CFOM defualt sections created=========== 
*/
 function cfom_is_default_section_created() {
     
	$default_created = false;
	
	if( get_option('cfom_default_sections_created') ) {
		$default_created = true;
	}
	
	return $default_created;
 }

/* 
**========== CFOM get defualt sections meta =========== 
*/
function cfom_get_section_default_meta($section_type) {
     
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
        
        default:
            $defult_meta = cfom_get_default_meta_for_other_sections($section_type);
    }
    
    return apply_filters('cfom_section_default_meta', $defult_meta, $section_type);
 }
 
/* 
**========== CFOM core billing fields meta =========== 
*/
function cfom_core_billing_meta() {
		
	$field_set = array(
				array(	'type'			=> 'country',
						'title'			=> __('Country', 'cfom'),
						'data_name'		=> 'billing_country',
						'enable_field'	=> 'on',
						'description'	=> __('Country', 'cfom'),
						'placeholder'	=> __('Country', 'cfom'),
						'class'			=> 'cfom_country_to_state country_select',
						'width'			=> 12,
						'options'		=> array(), // these will be populated on rendering
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_first_name',
						'enable_field'	=> 'on',
						'title'			=> __('First Name', 'cfom'),
						'description'	=> __('First Name', 'cfom'),
						'placeholder'	=> __('First Name', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 6,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_last_name',
						'enable_field'	=> 'on',
						'title'			=> __('Last Name', 'cfom'),
						'description'	=> __('Last Name', 'cfom'),
						'placeholder'	=> __('Last Name', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 6,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_company',
						'enable_field'	=> 'on',
						'title'			=> __('Company Name', 'cfom'),
						'description'	=> __('Company Name', 'cfom'),
						'placeholder'	=> __('Company Name', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> ''),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_address_1',
						'enable_field'	=> 'on',
						'title'			=> __('Address 1', 'cfom'),
						'description'	=> __('Address 1', 'cfom'),
						'placeholder'	=> __('Address 1', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_address_2',
						'enable_field'	=> 'on',
						'title'			=> __('Address 2', 'cfom'),
						'description'	=> __('Address 2', 'cfom'),
						'placeholder'	=> __('Address 2', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> ''),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_city',
						'enable_field'	=> 'on',
						'title'			=> __('City', 'cfom'),
						'description'	=> __('City', 'cfom'),
						'placeholder'	=> __('City', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_state',
						'enable_field'	=> 'on',
						'title'			=> __('State / Country', 'cfom'),
						'description'	=> __('State / Country', 'cfom'),
						'placeholder'	=> __('State / Country', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> ''),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_postcode',
						'enable_field'	=> 'on',
						'title'			=> __('Postcode / ZIP', 'cfom'),
						'description'	=> __('Postcode / ZIP', 'cfom'),
						'placeholder'	=> __('Postcode / ZIP', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_email',
						'enable_field'	=> 'on',
						'title'			=> __('Email', 'cfom'),
						'description'	=> __('Email', 'cfom'),
						'placeholder'	=> __('Email', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 6,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'billing_phone',
						'enable_field'	=> 'on',
						'title'			=> __('Phone', 'cfom'),
						'description'	=> __('Phone', 'cfom'),
						'placeholder'	=> __('Phone', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 6,
						'required'		=> 'on'),
		);
			
	return apply_filters('cfom_core_billing_meta', $field_set);
}

/* 
**========== CFOM core shipping fields meta =========== 
*/
function cfom_core_shipping_meta() {
	
	$field_set = array(
				array(	'type'			=> 'text',
						'data_name'		=> 'shipping_first_name',
						'enable_field'	=> 'on',
						'title'			=> __('First Name', 'cfom'),
						'description'	=> __('First Name', 'cfom'),
						'placeholder'	=> __('First Name', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 6,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'shipping_last_name',
						'enable_field'	=> 'on',
						'title'			=> __('Last Name', 'cfom'),
						'description'	=> __('Last Name', 'cfom'),
						'placeholder'	=> __('Last Name', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 6,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'shipping_company',
						'enable_field'	=> 'on',
						'title'			=> __('Company Name', 'cfom'),
						'description'	=> __('Company Name', 'cfom'),
						'placeholder'	=> __('Company Name', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> ''),
				array(	'type'			=> 'country',
						'title'			=> __('Country', 'cfom'),
						'data_name'		=> 'shipping_country',
						'enable_field'	=> 'on',
						'description'	=> __('Country', 'cfom'),
						'placeholder'	=> __('Country', 'cfom'),
						'class'			=> 'cfom_country_to_state country_select',
						'width'			=> 12,
						'options'		=> array(), // these will be populated on rendering
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'shipping_address_1',
						'enable_field'	=> 'on',
						'title'			=> __('Address 1', 'cfom'),
						'description'	=> __('Address 1', 'cfom'),
						'placeholder'	=> __('Address 1', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'shipping_address_2',
						'enable_field'	=> 'on',
						'title'			=> __('Address 2', 'cfom'),
						'description'	=> __('Address 2', 'cfom'),
						'placeholder'	=> __('Address 2', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> ''),
				array(	'type'			=> 'text',
						'data_name'		=> 'shipping_city',
						'enable_field'	=> 'on',
						'title'			=> __('City', 'cfom'),
						'description'	=> __('City', 'cfom'),
						'placeholder'	=> __('City', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> 'on'),
				array(	'type'			=> 'text',
						'data_name'		=> 'shipping_state',
						'enable_field'	=> 'on',
						'title'			=> __('State / Country', 'cfom'),
						'description'	=> __('State / Country', 'cfom'),
						'placeholder'	=> __('State / Country', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> ''),
				array(	'type'			=> 'text',
						'data_name'		=> 'shipping_postcode',
						'enable_field'	=> 'on',
						'title'			=> __('Postcode / ZIP', 'cfom'),
						'description'	=> __('Postcode / ZIP', 'cfom'),
						'placeholder'	=> __('Postcode / ZIP', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> 'on'),
			);
			
	return apply_filters('cfom_core_shipping_meta', $field_set);
}

/* 
**========== CFOM Order Comments =========== 
*/
function cfom_core_order_comments() {
    
    $data_name	= "order_comments";
    $cols		= 5;
    $rows		= 3;
    $defult_meta = array(
                array(	'type'	        => 'textarea',
						'data_name'		=> $data_name,
						'enable_field'	=> 'on',
						'title'			=> __('Order notes (optional)', 'cfom'),
						'description'	=> __('A Sample Text', 'cfom'),
						'placeholder'	=> __('Notes about your order, e.g. special notes for delivery.', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'cols'			=> $cols,
                        'rows'			=> $rows,
						'required'		=> '')
        );
        
    return apply_filters('cfom_core_order_comments', $defult_meta);
}

/* 
**========== CFOM other section meta =========== 
*/
function cfom_get_default_meta_for_other_sections( $section_type ) {
    
    $data_name = "test_{$section_type}";
    
    $defult_meta = array(
                array(	'type'	        => 'text',
						'data_name'		=> $data_name,
						'title'			=> __('A Sample Text', 'cfom'),
						'description'	=> __('A Sample Text', 'cfom'),
						'placeholder'	=> __('A Sample Text', 'cfom'),
						'class'			=> 'input-text',
						'width'			=> 12,
						'required'		=> 'on')
        );
        
    return apply_filters('cfom_default_meta_for_other_sections', $defult_meta, $section_type);
}

/* 
**========== Check if given data_name is default checkout field =========== 
*/
function cfom_is_default_checkout_field( $data_name ) {
	
	$is_default = false;
	
	foreach(cfom_core_billing_meta() as $field) {
		
		if( $data_name == $field['data_name'] ) {
			$is_default = true;
			break;
		}
	}
	
	if( ! $is_default ) {
		
		foreach(cfom_core_shipping_meta() as $field) {
		
			if( $data_name == $field['data_name'] ) {
				$is_default = true;
				break;
			}
		}
	}
	
	echo "{$data_name} is {$is_default}"; 
	return apply_filters('cfom_is_default_checkout_field', $is_default, $data_name);
}