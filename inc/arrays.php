<?php
/**
 * Arrays contining settings/meta detail
 **/
 
if( ! defined('ABSPATH') ) die('Not Allowed.');

// get checkout sections
function cfom_get_sections() {
	
	$cfom_sections = array(
			'before_customer_details'	=> __('Before Billing & Shipping Form', 'cfom'),
			'billing'	=> __('Billing', 'cfom'),
			'shipping'	=> __('Shipping', 'cfom'),
			'after_order'	=> __('Order Notes', 'cfom'),
			'before_billing'	=> __('Before Billing', 'cfom'),
			'after_billing'	=> __('After Billing', 'cfom'),
			'before_shipping'	=> __('Before Shipping', 'cfom'),
			'after_shipping'	=> __('After Shipping', 'cfom'),
			'before_order_review'	=> __('Before Order Review', 'cfom'),
			'before_order_payment'	=> __('Before Order Payment Methods', 'cfom'),
			'before_terms_conditions'	=> __('Before Terms & Conditions', 'cfom'),
			'after_terms_conditions'	=> __('After Terms & Conditions', 'cfom'),
			'after_order_payment'	=> __('After Order Payment Methods', 'cfom'),
			
	);
	
	return apply_filters('cfom_get_sections', $cfom_sections);
}

// Returns Actions Hooks against section name
function cfom_arrays_get_action_hook_aginst_sections() {
	
	$actions = array(
		
					'before_customer_details'	=> 'woocommerce_checkout_before_customer_details',
					'billing'					=> 'woocommerce_before_checkout_billing_form',
					'shipping'					=> 'woocommerce_before_checkout_shipping_form',
					'after_order'				=> 'woocommerce_after_order_notes',
					'before_billing'			=> 'woocommerce_checkout_billing',
					'after_billing'				=> 'woocommerce_checkout_billing',
					'before_shipping'			=> 'woocommerce_checkout_shipping',
					'after_shipping'			=> 'woocommerce_checkout_shipping',
					'before_order_review'		=> 'woocommerce_checkout_before_order_review',
					'before_order_payment'		=> 'woocommerce_review_order_before_payment',
					'before_terms_conditions'	=> 'woocommerce_checkout_before_terms_and_conditions',
					'after_terms_conditions'	=> 'woocommerce_checkout_after_terms_and_conditions',
					'after_order_payment'		=> 'woocommerce_review_order_after_payment',
					
		);
		
	$actions = apply_filters('cfom_actions_by_sections', $actions);
	return $actions;
}

// Returns Actions Hooks against section name
function cfom_arrays_get_section_by_action( $action_name ) {
	
	$actions = cfom_arrays_get_action_hook_aginst_sections();
	
	$section_found = '';
	foreach($actions as $section => $action) {
		
		if( $action_name == $action ) {
			$section_found = $section;
		}
	}
	
	return apply_filters('cfom_section_by_action', $section_found, $action_name);
}



function cfom_get_plugin_meta(){

	return array('name'	=> 'CFOM',
				'dir_name'		=> '',
				'shortname'		=> 'cfom',
				'path'			=> CFOM_PATH,
				'url'			=> CFOM_URL,
				'db_version'	=> 3.12,
				'logo'			=> CFOM_URL . '/images/logo.png',
				'menu_position'	=> 90,
	);
}


// Get timezone list
function cfom_array_get_timezone_list($selected_regions, $show_time) 
{
	if( $selected_regions == 'All' ) {
	    $regions = array(
	        DateTimeZone::AFRICA,
	        DateTimeZone::AMERICA,
	        DateTimeZone::ANTARCTICA,
	        DateTimeZone::ASIA,
	        DateTimeZone::ATLANTIC,
	        DateTimeZone::AUSTRALIA,
	        DateTimeZone::EUROPE,
	        DateTimeZone::INDIAN,
	        DateTimeZone::PACIFIC,
	    );
	} else {
		$selected_regions = explode(",", $selected_regions);
		$tz_regions = array();
		
		foreach($selected_regions as $region) {
			// var_dump($region);
			switch($region) {
				case 'AFRICA':
					$tz_regions[] = DateTimeZone::AFRICA;
				break;
				case 'AMERICA':
					$tz_regions[] = DateTimeZone::AMERICA;
				break;
				case 'ANTARCTICA':
					$tz_regions[] = DateTimeZone::ANTARCTICA;
				break;
				case 'ASIA':
					$tz_regions[] = DateTimeZone::ASIA;
				break;
				case 'ATLANTIC':
					$tz_regions[] = DateTimeZone::ATLANTIC;
				break;
				case 'AUSTRALIA':
					$tz_regions[] = DateTimeZone::AUSTRALIA;
				break;
				case 'EUROPE':
					$tz_regions[] = DateTimeZone::EUROPE;
				break;
				case 'INDIAN':
					$tz_regions[] = DateTimeZone::INDIAN;
				break;
				case 'PACIFIC':
					$tz_regions[] = DateTimeZone::PACIFIC;
				break;
			}
			
		}
		
		$regions = $tz_regions;
	}
	
	// cfom_pa($regions);

    $timezones = array();
    foreach( $regions as $region )
    {
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers( $region ) );
    }

    $timezone_offsets = array();
    foreach( $timezones as $timezone )
    {
        $tz = new DateTimeZone($timezone);
        $timezone_offsets[$timezone] = $tz->getOffset(new DateTime);
    }

    // sort timezone by timezone name
    ksort($timezone_offsets);

    $timezone_list = array();
    foreach( $timezone_offsets as $timezone => $offset )
    {
        $offset_prefix = $offset < 0 ? '-' : '+';
        $offset_formatted = gmdate( 'H:i', abs($offset) );

        $pretty_offset = "UTC${offset_prefix}${offset_formatted}";
        
        $t = new DateTimeZone($timezone);
        $c = new DateTime(null, $t);
        $current_time = $c->format('g:i A');

		if( $show_time == 'on' ) {
        	$timezone_list[$timezone] = "(${pretty_offset}) $timezone - $current_time";
		} else {
			$timezone_list[$timezone] = "(${pretty_offset}) $timezone";
		}
    }

    return $timezone_list;
}