<?php
/**
 * admin related functions/hooks
 * 
 * @since 10.0
 **/
 
if( ! defined('ABSPATH') ) die('Not Allowed');
 
function cfom_admin_rate_and_get() {
		
	if( !cfom_pro_is_installed() ) return '';
	
	
	$cfom_pro = 'https://najeebmedia.com/get-quote/';
	echo '<p class="center"><a href="'.esc_url($cfom_pro).'" class="btn btn-primary">Get One Addon Free - Contact</a></p>';
}

/*
 * updating form meta in admin call
 */
function cfom_admin_update_form_meta() {
	
	
	global $wpdb;
	
	$cfom_id	= isset($_REQUEST['cfom_id']) ? intval($_REQUEST['cfom_id']) : '';
	$section_type = isset($_REQUEST['section_type']) ? sanitize_text_field($_REQUEST['section_type']) : '';
	$section_title = isset($_REQUEST['section_title']) ? sanitize_text_field($_REQUEST['section_title']) : '';
	$is_active = isset($_REQUEST['is_active']) ? sanitize_text_field($_REQUEST['is_active']) : '';
	$show_title = isset($_REQUEST['show_title']) ? sanitize_text_field($_REQUEST['show_title']) : '';
	$cfom_css = isset($_REQUEST['cfom_css']) ? sanitize_text_field($_REQUEST['cfom_css']) : '';
	$conditions = isset($_REQUEST['conditions']) ? sanitize_text_field($_REQUEST['conditions']) : '';
	
	$cfom_meta	= apply_filters('cfom_meta_before_saving', $_REQUEST['cfom']);
	$cfom_meta = isset($_REQUEST['cfom']) ? cfom_sanitize_meta($cfom_meta) : '';
	
	// cfom_pa($cfom_meta); exit;
	$section_meta = array (
			'section_type'      => $section_type,
			'section_title'		=> $section_title,
            'is_active'			=> $is_active,
            'show_title'		=> $show_title,
            'cfom_css'			=> $cfom_css,
			'conditions'    	=> $conditions,
			'cfom_meta'         => json_encode ( $cfom_meta ),
	);
	
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
			'section_id' => $cfom_id 
	);
	
	$where_format = array (
			'%d' 
	);
	
	global $wpdb;
	$cfom_table = $wpdb->prefix.CFOM_TABLE_META;
	
	$rows_effected = $wpdb->update($cfom_table, $section_meta, $where, $format, $where_format);
	
	// $wpdb->show_errors(); $wpdb->print_error();
	
	$resp = array ();
	if ($rows_effected) {
		
		$resp = array (
				'message' => __ ( 'Form updated successfully', 'cfom' ),
				'status' => 'success',
				'cfom_id' => $cfom_id 
		);
	} else {
		
		$resp = array (
				'message' => __ ( 'Form updated successfully.', 'cfom' ),
				'status' => 'success',
				'cfom_id' => $cfom_id 
		);
	}
	
	wp_send_json($resp);
}

/*
 * simplifying meta for admin view in existing-meta.php
 */
function cfom_admin_simplify_meta($meta) {
	//echo $meta;
	$metas = json_decode ( $meta );
	
	if ($metas) {
		echo '<a href="#" class="cfom-section-meta-title">'.__('Show/Hide', 'cfom').'</a>';
		echo '<ul class="cfom-section-meta">';
		foreach ( $metas as $meta => $data ) {
			
			//cfom_pa($data);
			$req = (isset( $data -> required ) && $data -> required == 'on') ? 'yes' : 'no';
			$title = (isset( $data -> title )  ? $data -> title : '');
			$type = (isset( $data -> type )  ? $data -> type : '');
			$options = (isset( $data -> options )  ? $data -> options : '');
			
			echo '<li>';
			echo '<strong>label:</strong> ' . $title;
			echo ' | <strong>type:</strong> ' . $type;
			
			if (! is_object ( $options) && is_array ( $options )){
				echo ' | <strong>options:</strong> ';
				foreach($options as $option){
					
					$display_info = '';
					if( isset($option->option) ) {
						$display_info = $option->option;
					} elseif(isset($option->width)) {
						$display_info = $option->width.'x'.$option->height;
					}
					
					if( empty($option->price) ) { 
						echo $display_info .', ';
					} else{
						echo $display_info . ' (' .$option -> price .'), ';
					}
				}
			}
			
				
			echo ' | <strong>required:</strong> ' . $req;
			echo '</li>';
		}
		
		echo '</ul>';
	}
}

function cfom_admin_print_section_pdf() {
        
    $order_id   = isset($_REQUEST['order_id']) ? $_REQUEST['order_id'] : '';
    $section    = isset($_REQUEST['section']) ? $_REQUEST['section'] : 'all';

    $cfom_order = new CFOM_Order( $order_id );
    $order_meta = $cfom_order->print_order_meta($section);
}