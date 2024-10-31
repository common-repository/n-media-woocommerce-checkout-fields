<?php
/**
 * cfom Meta Class
 * @since version 15.0
 * 
 * */

 
/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH') ) die('Not Allowed');

class CFOM_Meta {
    
    var $meta_id;
    
    // $product_id can be null if get instance to get data by meta_id
    function __construct() {
        
        $this->meta_id    = null;
        
        //Now we are creating properties agains each methods in our Alpha class.
        $methods = get_class_methods( $this );
        $excluded_methods = array('__construct', 
                                    'get_section_fields',
                                    'get_active_fields',
                                    'get_core_checkout_fields');
                                    
        foreach ( $methods as $method ) {
            if ( ! in_array($method, $excluded_methods) ) {
                $this->$method = $this->$method();
            }
        }
    }
    
    /* 
    **========== Check unique datanames =========== 
    */
    function has_unique_datanames() {

    	$has_unique = true;
        $datanames_array = array();
        
        foreach($this->all_fields() as $field) {
            $type = isset($field['type']) ? $field['type'] : '';
            
            if( !isset($field['data_name']) ) {
                $has_unique = false;
                break;
            }
            
            if( in_array($field['data_name'], $datanames_array) ) {
                
                $has_unique = false;
                break;
            }
            
            $datanames_array[] = $field['data_name'];
        }
        
        // cfom_pa($datanames_array);
        return apply_filters('cfom_has_unique_datanames', $has_unique);
    }
    
    /* 
    **========== Cfom all fields =========== 
    */
    function all_fields() {
        
        $all_fields = array();
        foreach( $this->get_active_fields() as $section => $fields ) { 
            
            foreach($fields as $field) {
                
                $field['section'] = $section;
                $all_fields[] = $field;
            }
        }
        
        return apply_filters('cfom_al_fields', $all_fields, $this);
    }
    
    /* 
    **========== Get active fields =========== 
    */
    function get_active_fields() {
        
        $active_fields = array();
        
        global $wpdb;
        $table_name = $wpdb->prefix . CFOM_TABLE_META;
        $qry = "SELECT section_type, cfom_meta FROM {$table_name} WHERE is_active='yes' ";
        // $qry .= "AND section_type = '{$section}' ";
        $qry .= "ORDER BY section_type, section_order ASC";
        
	    $section_fields = $wpdb->get_results ( $qry );
	    $checkout_fields = array();
	    if( $section_fields ) {
	        
	        foreach($section_fields as $section) {
	            
	            $section_meta = json_decode ( $section->cfom_meta, true );
	            $section_type = $section->section_type;
	            
	            foreach($section_meta as $meta) {
	                
	                $checkout_fields[$section_type][] = $meta;
	            }
	        }
	    }
        // cfom_pa($checkout_fields);
        return apply_filters('cfom_active_section_fields', $checkout_fields, $this);
    }
    
    /* 
    **========== Getting Core Checkout Fields for validation =========== 
    */
    function get_core_checkout_fields( $section ) {
        
        $checkout_fields = $this->get_section_fields($section);
        
        $core_fields = array();
        if( $checkout_fields ) {
            foreach( $checkout_fields as $field ) {
                
                $field_key  = $field['data_name'];
                $label      = isset($field['title']) ? $field['title'] : '';
                $required   = isset($field['required']) && $field['required'] == 'on' ? 1 : '';
                
                $billing_fields = array('label'    => $label,
                                        'required'  => $required);
                                        
                // Some core fields need type key
                switch($field_key) {
                    
                    case 'billing_country':
                    case 'shipping_country':
                        $billing_fields['type'] = 'country';
                    break;
                    case 'billing_state':
                    case 'shipping_state':
                        $billing_fields['type'] = 'state';
                    break;
                    case 'billing_phone':
                        $billing_fields['type'] = 'tel';
                    break;
                    case 'billing_email':
                        $billing_fields['type'] = 'email';
                    break;
                }
                
                $core_fields[$field_key] = $billing_fields;
            }
        }
        
        return apply_filters('cfom_core_checkout_fields', $core_fields, $section);
    }

    /*
    ** ============== Get sections  ================= 
    */
    function get_section_fields( $section ) {
        
        $active_fields = $this->get_active_fields();
        if( ! isset($active_fields[$section]) ) return null;
        
        $section_fields = $active_fields[$section];
        
        return apply_filters('cfom_section_fields', $section_fields, $section);
    }
    
}