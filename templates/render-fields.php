<?php
/**
 * Rendering all fields on product page
 * @since 10.0
 * 
 * */
if( ! defined("ABSPATH") ) die("Not Allowed");

// cfom_pa($cfom_fields_meta);


echo '<div class="form-row align-items-center cfom-section-collapse">';

$section_started = false;
$cfom_field_counter = 0;

foreach( $cfom_fields_meta as $meta ) {
    
    $type 			= ( isset($meta['type']) ? $meta ['type'] : '');
	$title			= ( isset($meta['title']) ? stripslashes($meta ['title']) : '');
	$data_name		= ( isset($meta['data_name']) ? $meta ['data_name'] : $title);
	$col			= cfom_get_field_colum($meta);
	$required		= ( isset($meta['required'] ) ? $meta['required'] : '' );
	$description	= ( isset($meta['description'] ) ? stripslashes($meta['description']) : '' );
	$condition		= ( isset($meta['conditions'] ) ? $meta['conditions'] : '' );
	$options		= ( isset($meta['options'] ) ? $meta['options'] : array());
	$default_value  = ( isset($meta['default_value'] ) ? $meta['default_value'] : '');
	$classes        = ( isset($meta['class'] ) ? $meta['class'] : '');
	
	$cfom_field_counter++;
	
	// @since: 12.4
	// checking field visibility
	if( ! cfom_is_field_visible($meta) ) continue;
	
	if( empty($data_name) ) {
	    printf(__("Please provide data name property for %s", 'cfom'), $title);
	    continue;
	}
	// Dataname senatize
	$data_name = sanitize_key( $data_name );
	
	if( !empty( $classes ) ) {
	    $classes = explode(",", $classes);
	    $classes[] = 'form-control';
	} else {
	    $classes = array('form-control');
	}
	
	$classes = apply_filters('cfom_input_classes', $classes, $meta);
	
	// Default values in settings
	switch ($type) {
		
		case 'checkbox':
			$default_value = isset($meta['checked']) ? explode("\n", $meta['checked']) : '';
			break;
			
		case 'select':
		case 'radio':
		case 'timezone':
		case 'palettes':
		case 'image':
			$default_value = isset($meta['selected']) ? $meta['selected'] : '';
			break;
			
	}
	// Stripslashes: default values
	$default_value = ! is_array($default_value) ? stripslashes($default_value) : $default_value;
	
	//WPML
	$title			= cfom_wpml_translate($title, 'cfom');
	$description	= cfom_wpml_translate($description, 'cfom');
	
	// Generating field label
	$show_asterisk		= ( !empty($required) ) ? '<span class="show_required"> *</span>' : '';
	$show_description	= ( !empty($description) ) ? '<span class="show_description">'.$description.'</span>' : '';
	$show_description	= apply_filters('cfom_field_description', $show_description, $meta);
	
	$field_label = $title . $show_asterisk . $show_description;
	
	
	if(is_array($options)){
		$options		= array_map("cfom_translation_options", $options);
	}
	
	$input_wrapper_class = $data_name;
	// Collapse Fields
	if( apply_filters('cfom_collapse_fields', false) ) {
		if( $type == 'section') {
			
			// if section started close it first
			if( $section_started ) {
				echo '<div style="clear:both"></div>';
				echo '</div>';
			}
			
			$field_html	= isset($meta['html']) ? $meta['html'] : '';
			
			// echo '<div class="cfom-section-collapse">';
    		echo '<h4 class="cfom-collapsed-title">'.$field_html.'</h4>';
    		echo '<div class="collapsed-child">';
    		// $input_wrapper_class .=' ';
			$section_started = true;
		}
	}
		
		$input_wrapper_class .= ' '.cfom_get_core_field_wrapper_class($data_name);
		
		
        echo '<div data-data_name='.esc_attr($data_name).' class="cfom-field-wrapper cfom-col col-md-'.esc_attr($col).' '.esc_attr($input_wrapper_class).'">';
            
        // Text|Email|Date|Number
        $cfom_field_attributes = apply_filters('cfom_field_attributes', $meta, $type);
        
            switch( $type ) {
                
                case 'text':
                case 'email':
                case 'date':
            	case 'daterange':
                case 'number':
                case 'color':
                	
                	$min	= isset( $meta['min'] ) ? $meta['min'] : '';
                	$max	= isset( $meta['max'] ) ? $meta['max'] : '';
                	$step	= isset( $meta['step'] ) ? $meta['step'] : '';
                	$ph 	= isset( $meta['placeholder'] ) ? $meta['placeholder'] : '';
                	$default_value = strip_tags($default_value);
                	
                    $cfom_field_setting = array(  
                    				'id'        => $data_name,
                                    'type'      => $type,
                                    'name'      => "cfom[{$section}][{$data_name}]",
                                    'classes'   => $classes,
                                    'label'     => $field_label,
                                    'title'		=> $title,
                                    'attributes'=> $cfom_field_attributes,
                                    'min'		=> $min,
                                    'max'		=> $max,
                                    'step'		=> $step,
                                    'placeholder'	=> $ph,
                                    'autocomplete' => "false",
                                    );
                                    
                    
                    
                    $cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
                    echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
                	break;
                	
                case 'measure':
                	
                	$min	= isset( $meta['min'] ) ? $meta['min'] : '';
                	$max	= isset( $meta['max'] ) ? $meta['max'] : '';
                	$step	= isset( $meta['step'] ) ? $meta['step'] : '';
                	$use_units = isset( $meta['use_units'] ) ? $meta['use_units'] : '';
                	$options = cfom_convert_options_to_key_val($options, $meta);
                	$default_value = strip_tags($default_value);
            
                    $cfom_field_setting = array(  
                    				'id'        => $data_name,
                                    'type'      => $type,
                                    'name'      => "cfom[{$section}][{$data_name}]",
                                    'classes'   => $classes,
                                    'label'     => $field_label,
                                    'title'		=> $title,
                                    'attributes'=> $cfom_field_attributes,
                                    'min'		=> $min,
                                    'max'		=> $max,
                                    'step'		=> $step,
                                    'options'	=> $options,
                                    'use_units'=> $use_units,
                                    );
                                    
                    
                    $cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
                    echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
                	break;
                	
                case 'textarea':
                	
                	if( !empty($default_value) ) {
                	
                		$default_value = str_replace(']]>', ']]&gt;', $default_value);
                	}
					
					// Cols & Rows
					$cols	= ( isset($meta['cols']) ? $meta ['cols'] : 10);
					$rows	= ( isset($meta['rows']) ? $meta ['rows'] : 3);
					$editor	= ( isset($meta['rich_editor']) ? $meta ['rich_editor'] : '');
					
					$cfom_field_setting = array(  
		                				'id'        => $data_name,
		                                'type'      => $type,
		                                'name'      => "cfom[{$section}][{$data_name}]",
		                                'classes'   => $classes,
		                                'label'     => $field_label,
		                                'title'		=> $title,
		                                'attributes'=> $cfom_field_attributes,
		                                'cols'		=> $cols,
		                                'rows'		=> $rows,
		                                'rich_editor' => $editor,
		                                );
		                
		            $cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
		            echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
		            break;
                
                case 'checkbox':
                	
                	$options = cfom_convert_options_to_key_val($options, $meta);
					$taxable		= (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
					
					$onetime = isset($meta['onetime']) ? $meta['onetime'] : '';
					$cfom_field_setting = array(  
								  'id'      	=> $data_name,
					              'type'    	=> 'checkbox',
					              'name'    	=> "cfom[{$section}][{$data_name}]",
					              //'classes'   => $classes, // apply default class: form-check-input
                                  'label'   	=> $field_label,
                                  'title'		=> $title,
                                  'attributes'	=> $cfom_field_attributes,
					              'options' 	=> $options,
					              'onetime'		=> $onetime,
					              'taxable'		=> $taxable,
					              );
					
					$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
					echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
					break;
					
				case 'select':
                	
                	$options = cfom_convert_options_to_key_val($options, $meta);
                	// cfom_pa($options);
                	$onetime = isset($meta['onetime']) ? $meta['onetime'] : '';
                	$taxable = (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
                	
                	$cfom_field_setting = array(  
								  'id'        => $data_name,
					              'type'      => 'select',
					              'name'      => "cfom[{$section}][{$data_name}]",
					              'classes'   => $classes,
                                  'label'     => $field_label,
                                  'title'		=> $title,
                                  'attributes'=> $cfom_field_attributes,
					              'options'   => $options,
					              'onetime'		=> $onetime,
					              'taxable'		=> $taxable,
					              );
					
					$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
					echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
					break;
					
				case 'country':
                	
                	$options = cfom_get_country_options();
                	$options = cfom_convert_options_to_key_val($options, $meta);
                	// cfom_pa($options);
                	$onetime = isset($meta['onetime']) ? $meta['onetime'] : '';
                	$taxable = (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
                	
                	$cfom_field_setting = array(  
								  'id'        => $data_name,
					              'type'      => 'country',
					              'name'      => "cfom[{$section}][{$data_name}]",
					              'classes'   => $classes,
                                  'label'     => $field_label,
                                  'title'		=> $title,
                                  'attributes'=> $cfom_field_attributes,
					              'options'   => $options,
					              );
					
					$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
					echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
					break;
					
				case 'radio':
                	
                	$options = cfom_convert_options_to_key_val($options, $meta);
                	$onetime = isset($meta['onetime']) ? $meta['onetime'] : '';
                	$taxable		= (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
                	
					$cfom_field_setting = array(  
								  'id'        => $data_name,
					              'type'      => 'radio',
					              'name'      => "cfom[{$section}][{$data_name}]",
					              //'classes'   => $classes, // apply default class: form-check-input
                                  'label'     => $field_label,
                                  'title'		=> $title,
                                  'attributes'=> $cfom_field_attributes,
					              'options'   => $options,
					              'onetime'		=> $onetime,
					              'taxable'		=> $taxable,
					              );
					
					$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
					echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
					break;
					
				case 'timezone':
                	
                	$regions		= isset($meta['regions']) ? $meta['regions'] : 'All';
                	$show_time		= isset($meta['show_time']) ? $meta['show_time'] : '';
                	$first_option	= isset($meta['first_option']) ? $meta['first_option'] : '';
					
                	$options = cfom_array_get_timezone_list($regions, $show_time);
                	if( !empty($first_option) ) {
                		$options[''] = sprintf(__("%s","cfom"), $first_option);
                	}
                	
                	// cfom_pa($options);
                	
					$cfom_field_setting = array(  
								  'id'        => $data_name,
					              'type'      => 'timezone',
					              'name'      => "cfom[{$section}][{$data_name}]",
					              'classes'   => $classes,
                                  'label'     => $field_label,
                                  'title'	  => $title,
                                  'attributes'=> $cfom_field_attributes,
					              'options'   => $options,
					              );
					
					$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
					echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
					break;
					
				case 'palettes':
					
					$options = cfom_convert_options_to_key_val($options, $meta);
					$color_width = !empty($meta['color_width']) ? intval($meta['color_width']) : 50;
    				$color_height = !empty($meta['color_height']) ? intval($meta['color_height']) : 50;
    				$onetime = isset($meta['onetime']) ? $meta['onetime'] : '';
                	$taxable		= (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
                	$display_circle	= (isset($meta['circle']) && $meta['circle'] == 'on') ? true : false;
                	$multiple_allowed	= isset($meta['multiple_allowed']) ? $meta['multiple_allowed'] : '';
                	
					$cfom_field_setting = array(  
                    				'id'        => $data_name,
                                    'type'      => $type,
                                    'name'      => "cfom[{$section}][{$data_name}]",
                                    'classes'   => $classes,
                                    'label'     => $field_label,
                                    'title'		=> $title,
                                    'color_height'=> $color_height,
                                    'color_width'=> $color_width,
                                    'options'   => $options,
                                    'onetime'		=> $onetime,
					            	'taxable'		=> $taxable,
					            	'display_circle'	=> $display_circle,
					            	'multiple_allowed' => $multiple_allowed,
                                    
                                    );
                    
                    $cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
                    echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
                	break;
                	
            	case 'image':
					
					$images	= isset($meta['images']) ? $meta['images'] : array();
					$show_popup	= isset($meta['show_popup']) ? $meta['show_popup'] : '';
					$multiple_allowed	= isset($meta['multiple_allowed']) ? $meta['multiple_allowed'] : '';
					
					$cfom_field_setting = array(  
                    				'id'        => $data_name,
                                    'type'      => $type,
                                    'name'      => "cfom[{$section}][{$data_name}]",
                                    'classes'   => $classes,
                                    'label'     => $field_label,
                                    'title'		=> $title,
                                    'legacy_view'	=> (isset($meta['legacy_view'])) ? $meta['legacy_view'] : '',
									'multiple_allowed' => $multiple_allowed,
									'images'	=> $meta['images'],
                                    'show_popup'=> $show_popup,
                                    );
                    
                    $cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
                    echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
                	break;
                	
                	case 'pricematrix':
                	
                	$options		= cfom_convert_options_to_key_val($options, $meta);
                	$discount		= isset($meta['discount']) ? $meta['discount'] : '';
                	$show_slider	= isset($meta['show_slider']) ? $meta['show_slider'] : '';
                	$qty_step		= isset($meta['qty_step']) ? $meta['qty_step'] : 1;
                	$show_price_per_unit		= isset($meta['show_price_per_unit']) ? $meta['show_price_per_unit'] : '';
                	
                	$cfom_field_setting = array(  
								  'id'        => $data_name,
					              'type'      => $type,
					              'name'      => "cfom[{$section}][{$data_name}]",
					              'label'	  => $field_label,
                                  'ranges'    => $options,
                                  'discount'  => $discount,
                                  'show_slider'	=> $show_slider,
                                  'qty_step'	=> $qty_step,
                                  'show_price_per_unit' => $show_price_per_unit,
					              );
					
					$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
					echo CFOM_Form() -> Input($cfom_field_setting);
					break;
                   
                   case 'quantities':
                	
                	$horizontal_layout = (isset( $meta['horizontal'] ) ? $meta['horizontal'] : '' );
                	$include_productprice = isset($meta['use_productprice']) ? $meta['use_productprice'] : '';
                	
                	if( !empty($_GET[$data_name]) ) {
                	
                		$default_value = sanitize_file_name( $_GET[$data_name] );
                	} 
					// cfom_pa($options);
					$cfom_field_setting = array(  
								  'id'        => $data_name,
					              'type'      => $type,
					              'name'      => "cfom[{$section}][{$data_name}]",
					              'label'	  => $field_label,
					              'required'		=> $required,
                                  'horizontal_layout' => $horizontal_layout,
                                  'options'		=> $options,
                                  'include_productprice' => $include_productprice
					              );
					
					$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
					echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
					break;
					
					// Section or HTML
					case 'section':
                	
                	// If step enable no need to show this:
                	if( $section_started ) break;
                	
                	$field_html	= isset($meta['html']) ? $meta['html'] : '';
                	
					$cfom_field_setting = array(  
								  'id'        => $data_name,
					              'type'      => $type,
					              'label'     => $field_label,
					              'name'      => "cfom[{$section}][{$data_name}]",
					              'html'		=> $field_html,
					              );
					
					$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
					echo CFOM_Form() -> Input($cfom_field_setting);
					break;
					
				// Audio/videos
				case 'audio':
					
					$audios	= isset($meta['audio']) ? $meta['audio'] : array();
					// $audios = cfom_convert_options_to_key_val($audios, $meta, $product);
				
					$cfom_field_setting = array(  
                    				'id'        => $data_name,
                                    'type'      => $type,
                                    'name'      => "cfom[{$section}][{$data_name}]",
                                    'classes'   => $classes,
                                    'label'     => $field_label,
                                    'title'		=> $title,
                                    /*'legacy_view'	=> (isset($meta['legacy_view'])) ? $meta['legacy_view'] : '',
									'popup_width'	=> $popup_width,
									'popup_height'	=> $popup_height,*/
									'multiple_allowed' => $meta['multiple_allowed'],
									'audios'		=> $audios,
                                    
                                    );
                    
                    $cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
                    echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
                	break;
                	
            	// File upload
				case 'file':
					
					$label_select = ($meta['button_label_select'] == '' ? __('Select files', "cfom") : $meta['button_label_select']);
					$files_allowed = ($meta['files_allowed'] == '' ? 1 : $meta['files_allowed']);
					$file_types = ($meta['file_types'] == '' ? 'jpg,png,gif' : $meta['file_types']);
					$file_size = ($meta['file_size'] == '' ? '10mb' : $meta['file_size']);
					$chunk_size = apply_filters('cfom_file_upload_chunk_size', '1mb');
					
					$drag_drop		= (isset( $meta ['dragdrop'] ) ? $meta ['dragdrop'] : '' );
					$button_class	= (isset( $meta ['button_class'] ) ? $meta ['button_class'] : '' );
					$photo_editing	= (isset( $meta ['photo_editing'] ) ? $meta ['photo_editing'] : '' );
					$editing_tools	= (isset( $meta ['editing_tools'] ) ? $meta ['editing_tools'] : '' );
					$popup_width	= (isset( $meta ['popup_width'] ) ? $meta ['popup_width'] : '500' );
					$popup_height	= (isset( $meta ['popup_height'] ) ? $meta ['popup_height'] : '400' );
					$file_cost		= (isset( $meta ['file_cost'] ) ? $meta ['file_cost'] : '' );
					$taxable		= (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
					$language		= (isset( $meta['language_opt'] ) ? $meta['language_opt'] : '' );
					
					$field_label = ($file_cost == '') ? $field_label : $field_label . ' - ' . wc_price($file_cost);
					
					$cfom_field_setting = array(
									'name'					=> "cfom[{$section}][{$data_name}]",
									'id'					=> $data_name,
									'type'					=> $type,
									'label'     			=> $field_label,
									'dragdrop'				=> $drag_drop,
									'button_label'			=> $label_select,
									'files_allowed'			=> $files_allowed,
									'file_types'			=> $file_types,
									'file_size'				=> $file_size,
									'chunk_size'			=> $chunk_size,
									'button_class'			=> $button_class,
									'photo_editing'			=> $photo_editing,
									'editing_tools'			=> $editing_tools,
									'file_cost'				=> $file_cost,
									'taxable'				=> $taxable,
									'language'				=> $language,
									);
									
					
                    
                    $cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
                    echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
                	break;
                	
            	// Cropper
				case 'cropper':
					
					$label_select	= ($meta['button_label_select'] == '' ? __('Select files', "cfom") : $meta['button_label_select']);
					$files_allowed	= ($meta['files_allowed'] == '' ? 1 : $meta['files_allowed']);
					$file_types 	= 'jpg,png,gif';
					$file_size		= ($meta['file_size'] == '' ? '10mb' : $meta['file_size']);
					$chunk_size 	= apply_filters('cfom_file_upload_chunk_size', '1mb');
					
					$drag_drop		= (isset( $meta ['dragdrop'] ) ? $meta ['dragdrop'] : '' );
					$button_class	= (isset( $meta ['button_class'] ) ? $meta ['button_class'] : '' );
					$taxable		= (isset( $meta['onetime_taxable'] ) ? $meta['onetime_taxable'] : '' );
					$language		= (isset( $meta['language_opt'] ) ? $meta['language_opt'] : '' );
					$file_cost		= (isset( $meta ['file_cost'] ) ? $meta ['file_cost'] : '' );
					$field_label	= ($file_cost == '') ? $field_label : $field_label . ' - ' . wc_price($file_cost);
					$options		= cfom_convert_options_to_key_val($options, $meta);
					
					// Croppie options
					$croppie_options	= cfom_get_croppie_options($meta);
					
					$cfom_field_setting = array(
									'name'					=> "cfom[{$section}][{$data_name}]",
									'id'					=> $data_name,
									'type'					=> $type,
									'label'     			=> $field_label,
									'dragdrop'				=> $drag_drop,
									'button_label'			=> $label_select,
									'files_allowed'			=> $files_allowed,
									'file_types'			=> $file_types,
									'file_size'				=> $file_size,
									'chunk_size'			=> $chunk_size,
									'button_class'			=> $button_class,
									'file_cost'				=> $file_cost,
									'taxable'				=> $taxable,
									'language'				=> $language,
									'croppie_options'		=> $croppie_options,
									'options'				=> $options,
									);
									
					
                    
                    $cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
                    echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
                	break;
					
				// Fixed Price Addon
            	case 'fixedprice':
						
						if( ! class_exists('NM_FixedPrice_wooproduct') ) 
							return;
							
						$first_option	= isset($meta['first_option']) ? $meta['first_option'] : '';
						$unit_plural	= isset($meta['unit_plural']) ? $meta['unit_plural'] : '';
						$unit_single	= isset($meta['unit_single']) ? $meta['unit_single'] : '';
						$options = cfom_convert_options_to_key_val($options, $meta);
						
						$cfom_field_setting = array(
								'name'			=> "",
								'id'			=> $data_name,
								'type'			=> $type,
								'label'     	=> $field_label,
								'description'	=> $description,
								'options'		=> $options,
								'classes'   	=> $classes,
								'attributes'	=> $cfom_field_attributes,
								'first_option'	=> $first_option,
								'unit_plural'	=> $unit_plural,
								'unit_single'	=> $unit_single,
								'title'			=> $title,
						);
						
						$cfom_field_setting = apply_filters('cfom_field_setting', $cfom_field_setting, $meta);
                    	echo CFOM_Form() -> Input($cfom_field_setting, $default_value);
							
					break;
					
					case 'hidden';
					
						$field_name = "cfom[{$section}][{$data_name}]";
						$hidden_val = isset( $meta['field_value'] ) ? $meta['field_value'] : '';
						
						echo '<input type="hidden" id="'.esc_attr($data_name).'" name="'.esc_attr($field_name).'" value="'.esc_attr($hidden_val).'">';
					break;
            }
            
            
        	/**
        	 * creating action space to render more addons
        	 **/
        	 do_action('cfom_rendering_inputs', $meta, $data_name, $classes, $field_label, $options);
        
        echo '</div>';  //col-lg-*
        
        if( count($cfom_fields_meta) == $cfom_field_counter && $section_started ) {
			echo '</div>';
		}
	
}

echo '</div>'; // Ends form-row