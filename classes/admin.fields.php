<?php
/**
 * CFOM Fields Manager Class
**/

/* 
**========== Direct access not allowed =========== 
*/ 
if( ! defined('ABSPATH') ) die('Not Allowed');
 

 class CFOM_Fields_Meta {
 
    private static $ins;
    
    function __construct() {
              
        add_action('admin_enqueue_scripts', array($this, 'load_script'));
    }
    
    public static function get_instance() {
        // create a new object if it doesn't exist.
        is_null(self::$ins) && self::$ins = new self;
        return self::$ins;
    }
    
    /* 
    **============ Load all scripts =========== 
    */ 
    function load_script($hook) {

        if( $hook != 'woocommerce_page_cfom' ) { return ''; }
        
        // Bootstrap Files
        wp_enqueue_style('cfom-bs', CFOM_URL."/css/admin/bootstrap.min.css");
        wp_enqueue_script('cfom-bs', CFOM_URL."/js/admin/bootstrap.min.js", array('jquery'), CFOM_VERSION, true);
        
        // Font-awesome File 
        wp_enqueue_style('cfom-fontawsome', CFOM_URL."/css/font-awesome/css/font-awesome.css");

        // Swal Files
        wp_enqueue_style('cfom-swal', CFOM_URL."/css/admin/sweetalert.css");
        wp_enqueue_script('cfom-swal', CFOM_URL."/js/admin/sweetalert.js", array('jquery'), CFOM_VERSION, true); 
        
        // Tabletojson JS File 
        wp_enqueue_script('cfom-tabletojson', CFOM_URL."/js/admin/jquery.tabletojson.min.js", array('jquery'), CFOM_VERSION, true);

        // Datatable Files
        wp_enqueue_style('cfom-datatables', CFOM_URL."/js/datatable/datatables.min.css");
        wp_enqueue_script('cfom-datatables', CFOM_URL."/js/datatable/jquery.dataTables.min.js", array('jquery'), CFOM_VERSION, true);

        // Description Tooltips JS File
        wp_enqueue_script('cfom-tooltip', CFOM_URL."/js/admin/cfom-tooltip.js", array('jquery'), CFOM_VERSION, true);

        // CFOM Admin Files
        wp_enqueue_style('cfom-field', CFOM_URL."/css/admin/cfom-admin.css");
        wp_enqueue_script('cfom-field', CFOM_URL."/js/admin/cfom-admin.js", array('cfom-swal','cfom-tabletojson','cfom-datatables','cfom-tooltip','jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-dialog'), CFOM_VERSION, true);

		// WP Media enqueue
		wp_enqueue_media ();
		
        $cfom_admin_meta = array(
	      'plugin_admin_page' => admin_url( 'admin.php?page=cfom'),
	      'loader'            => CFOM_URL.'/images/loading.gif',
	    );

        // localize cfom_vars
	    wp_localize_script( 'cfom-field', 'cfom_vars', $cfom_admin_meta);
	    wp_localize_script( 'cfom-meta-table', 'cfom_vars', $cfom_admin_meta);
    }

    /* 
    **============ Render all fields =========== 
    */
    function render_field_settings() {
        
        $all_input = $this->fields_meta_array();
        
        $html  = '';        
        $html .= '<div id="cfom-fields-wrapper">';
        foreach( $all_input as $fields_type => $meta ) {
        
           	$field_title = isset($meta['title']) ? $meta['title'] : '';
           	$field_desc  = isset($meta['desc']) ? $meta['desc'] : '';
           	$settings    = isset($meta['field_meta']) ? $meta['field_meta'] : array();
		

            $html .= '<div class="cfom-modal-box cfom-slider cfom-field-'.esc_attr($fields_type).'">';
			    $html .= '<header>';
			        $html .= '<h3>'.sprintf(__("%s","cfom"), $field_title).'</h3>';
			    $html .= '</header>';
			    $html .= '<div class="cfom-modal-body">';

			        $html .= $this->render_field_meta($settings, $fields_type);

			    $html .= '</div>';
			    $html .= '<footer>';
			    	$html .= '<span class="cfom-req-field-id"></span>';
                   	$html .= '<button type="button" class="btn btn-default cfom-close-checker cfom-close-fields cfom-js-modal-close" style="margin-right: 5px;">'.esc_html__( 'close', 'cfom' ).'</button>';
                    $html .= '<button type="button" class="btn btn-primary cfom-field-checker cfom-add-field" data-field-type="'.esc_attr($field_title).'">'.esc_html__( 'Add Field', 'cfom' ).'</button>';
			    $html .= '</footer>';
			$html .= '</div>';
        }

        $html .= '</div>';
        echo $html;
    }

    /* 
    **============ Render all fields meta =========== 
    */
    function render_field_meta($field_meta, $fields_type, $field_index='', $save_meta='', $section_title= '', $core_fields_key = '' ) {
    	
    	$html  = '';
       	$html .= '<div data-table-id="'.esc_attr($fields_type).'" class="row cfom-tabs cfom-fields-actions" data-field-no="'.esc_attr($field_index).'" data-section-id="'.esc_attr($core_fields_key).'">';
       		$html .= '<input type="hidden" name="cfom['.esc_attr($field_index).'][type]" value="'.esc_attr($fields_type).'" class="cfom-meta-field" data-metatype="type">';
       		
			$html .= '<div class="col-md-12 cfom-tabs-header">';
				
				$html .= '<label for="tab1" id="tab1" class="cfom-tabs-label cfom-active-tab">'.esc_html__( 'Fields', 'cfom' ).'</label>';
				
				if ($fields_type != 'hidden') {
					$html .= '<label for="tab2" id="tab2" class="cfom-tabs-label cfom-condition-tab-js">'.esc_html__( 'Conditions', 'cfom' ).'</label>';
				}
				
				if ($fields_type == 'select' || $fields_type == 'radio' || $fields_type == 'checkbox' || $fields_type == 'cropper' || $fields_type == 'quantities' || $fields_type == 'pricematrix' || $fields_type == 'palettes' || $fields_type == 'fixedprice' || $fields_type == 'bulkquantity'){
				$html .= '<label for="tab3" id="tab3" class="cfom-tabs-label">'.esc_html__( 'Add Options', 'cfom' ).'</label>';
				}else if($fields_type == 'image' || $fields_type == 'imageselect'){
				$html .= '<label for="tab3" id="tab3" class="cfom-tabs-label">'.esc_html__( 'Add Images', 'cfom' ).'</label>';
				}else if($fields_type == 'audio'){
				$html .= '<label for="tab3" id="tab3" class="cfom-tabs-label">'.esc_html__( 'Add Audio/Video ', 'cfom' ).'</label>';
				}
			
			$html .= '</div>';
    	
        if ($field_meta) {
        	
            foreach ($field_meta as $fields_meta_key => $meta) {
                
                $title      = isset($meta['title']) ? $meta['title'] : '';
                $desc       = isset($meta['desc']) ? $meta['desc'] : '';   
                $field_id   = isset($meta['data_name']) ? $meta['data_name'] : '';
                $type       = isset($meta['type']) ? $meta['type'] : '';
                $link       = isset($meta['link']) ? $meta['link'] : '';
                $values     = isset($save_meta[$fields_meta_key]) ? $save_meta[$fields_meta_key] : '';

                $default_value		= isset($meta ['default']) ? $meta ['default'] : '';
			
				if ( empty( $values) ){
					$values = $default_value;
				}

                if ($type == 'checkbox') {
                    $col = 'col-md-6 col-sm-6 cfom-handle-all-fields cfom-checkboxe-style';
                }else if($type == 'html-conditions'){
                    $col = 'col-md-12 cfom-handle-condition';
                }else if($type == 'paired' || $type == 'paired-cropper' || $type == 'paired-quantity' || $type == 'pre-images' || $type == 'pre-audios' || $type == 'bulk-quantity' || $type == 'imageselect'){
                    $col = 'col-md-12 cfom-handle-paired';
                }else{ 
                    $col = 'col-md-6 col-sm-6 cfom-handle-all-fields';
                }
                if ($fields_meta_key== 'logic') {
                	$col = 'col-md-6 cfom-handle-condition cfom-checkboxe-style';	
                }
                
                

                $html .= '<div data-meta-id="'.esc_attr($fields_meta_key).'" class="'.esc_attr($col).'">';
	                $html .= '<div class="form-group">';

	                    $html .= '<label>'.sprintf(__("%s","cfom"), $title).'';
	                        $html .= '<span class="cfom-helper-icon" data-cfom-tooltip="cfom_tooltip" title="'.sprintf(__("%s","cfom"),$desc).'">';
	                            $html .= '<i class="dashicons dashicons-editor-help"></i>';
	                        $html .= '</span>'.$link.'';
	                    $html .= '</label>';
	                    $html .= $this-> render_all_input_types( $fields_meta_key, $meta, $fields_type, $field_index, $values );

	                $html .= '</div>';
	            $html .= '</div>';
                  
            }
        }

        $html .= '</div>';

        return $html;        
    }

	/* 
    **============ Render all input field for settings =========== 
    */
	function render_all_input_types($name, $data, $fields_type, $field_index, $values ) {

		
		$type		   = (isset( $data ['type'] ) ? $data ['type'] : '');
		$options	   = (isset( $data ['options'] ) ? $data ['options'] : '');
		$placeholders  = isset($data['placeholders']) ? $data['placeholders'] : '';
		$existing_name = 'name="cfom['.esc_attr($field_index).']['.esc_attr($name).']"';

		$plugin_meta   = cfom_get_plugin_meta();
		$html_input    = '';
		
		if(!is_array($values))
			$values = stripslashes($values);
			
		
		
		switch ($type) {
			
			case 'number':
			case 'text' :
				// cfom_pa($values);
				$html_input .= '<input data-metatype="'.esc_attr($name).'" type="'.esc_attr($type).'"  value="' . esc_html( $values ). '" class="form-control cfom-meta-field"';

				if( $field_index != '') {

                  $html_input .= $existing_name;
                }

				$html_input .= '>';
				
				break;
			
			case 'textarea' :

				$html_input .= '<textarea data-metatype="'.esc_attr($name).'" class="form-control cfom-meta-field cfom-adjust-box-height"';
				
				if( $field_index != '') {

                  $html_input .= $existing_name;
                }
				
				$html_input .= '>' . esc_html( $values ) . '</textarea>';

				break;
			
			case 'select' :

				$html_input .= '<select id="'.$name.'" data-metatype="'.esc_attr($name).'" class="form-control cfom-meta-field"';
				
				if( $field_index != '') {

                  $html_input .= $existing_name;
                }

				$html_input .= '>';

				foreach ( $options as $key => $val ) {
					$selected = ($key == $values) ? 'selected="selected"' : '';
					$html_input .= '<option value="' . $key . '" ' . $selected . '>' . esc_html( $val ) . '</option>';
				}
				$html_input .= '</select>';

				break;
				
			case 'country' :
				
				$html_input .= '<p>'.__('All countries all loaded from WC Countries (WC_Countries class)', 'cfom').'</p>';
				
				break;
			
			case 'paired':
				
				$plc_option = (!empty($placeholders)) ? $placeholders[0] : __('Option','cfom');
				$plc_price = (!empty($placeholders) ? $placeholders[1] : cfom_pro_is_installed()) ? __('Price', 'cfom') : __('Price PRO Feature', 'cfom');
			
				$plc_id = (isset($placeholders[2]) && !empty($placeholders)) ? $placeholders[2] : __('Unique Option ID)', 'cfom');

				$opt_index0  = 1;
				$html_input .= '<ul class="cfom-options-container cfom-options-sortable">';
				
				if($values){
					// cfom_pa($values);
					$last_array_id = max(array_keys($values));

					foreach ($values as $opt_index => $option){
				
						$option_id = cfom_get_option_id($option);
						$html_input .= '<li class="data-options cfom-sortable-handle" style="display: flex;">';
							$html_input .= '<span class="dashicons dashicons-move"></span>';
							$html_input .= '<input type="text" class="option-title form-control cfom-option-keys" name="cfom['.esc_attr($field_index).'][options]['.esc_attr($opt_index).'][option]" value="'.esc_attr(stripslashes($option['option'])).'" placeholder="'.$plc_option.'" data-metatype="option">';
							
							
							$html_input .= '<input type="text" class="option-price form-control cfom-option-keys" name="cfom['.esc_attr($field_index).'][options]['.esc_attr($opt_index).'][price]" value="'.esc_attr($option['price']).'" placeholder="'.$plc_price.'" data-metatype="price">';
							
							$html_input .= '<input type="text" class="option-id form-control cfom-option-keys" name="cfom['.esc_attr($field_index).'][options]['.esc_attr($opt_index).'][id]" value="'.esc_attr($option_id).'" placeholder="'.$plc_id.'" data-metatype="id">';
							$html_input .= '<button class="btn btn-success cfom-add-option" data-option-type="paired"><i class="fa fa-plus" aria-hidden="true"></i></button>';
						$html_input .= '</li>';

						$opt_index0 =  $last_array_id;
                    	$opt_index0++;

					}
				}else{
					$html_input .= '<li class="data-options" style="display: flex;">';
						$html_input .= '<span class="dashicons dashicons-move"></span>';
						$html_input .= '<input type="text" class="option-title form-control cfom-option-keys" placeholder="'.$plc_option.'" data-metatype="option">';
						$html_input .= '<input type="text" class="option-price form-control cfom-option-keys" placeholder="'.$plc_price.'" data-metatype="price">';
				
						$html_input .= '<input type="text" class="option-id form-control cfom-option-keys" placeholder="'.$plc_id.'" data-metatype="id">';
						$html_input .= '<button class="btn btn-success cfom-add-option" data-option-type="paired"><i class="fa fa-plus" aria-hidden="true"></i></button>';
					$html_input .= '</li>';
				}
				$html_input .= '<input type="hidden" id="cfom-meta-opt-index" value="'.esc_attr($opt_index0).'">';
				$html_input	.= '<ul/>';
				
				break;
				
			case 'paired-cropper' :
				
				$opt_index0  = 1;
				$html_input .= '<ul class="cfom-options-container cfom-cropper-boundary">';
				
				if($values){
					
					$last_array_id = max(array_keys($values));
					foreach ($values as $opt_index => $option){
												
						$html_input .= '<li class="data-options" style=display:flex;>';
							$html_input .= '<span class="dashicons dashicons-move"></span>';
							$html_input .= '<input type="text" name="cfom['.esc_attr($field_index).'][options]['.esc_attr($opt_index).'][option]" value="'.esc_attr(stripslashes($option['option'])).'" placeholder="'.__('Label',"cfom").'" class="form-control cfom-option-keys" data-metatype="option">';
							$html_input .= '<input type="text" name="cfom['.esc_attr($field_index).'][options]['.esc_attr($opt_index).'][width]" value="'.esc_attr(stripslashes($option['width'])).'" placeholder="'.__('Width',"cfom").'" class="form-control cfom-option-keys" data-metatype="width">';
							$html_input .= '<input type="text" name="cfom['.esc_attr($field_index).'][options]['.esc_attr($opt_index).'][height]" value="'.esc_attr($option['height']).'" placeholder="'.__('Height',"cfom").'" class="form-control cfom-option-keys" data-metatype="height">';
							$html_input .= '<input type="text" name="cfom['.esc_attr($field_index).'][options]['.esc_attr($opt_index).'][price]" value="'.esc_attr($option['price']).'" placeholder="'.__('Price (optional)',"cfom").'" class="form-control cfom-option-keys" data-metatype="price">';
							$html_input .= '<button class="btn btn-success cfom-add-option" data-option-type="paired-cropper"><i class="fa fa-plus" aria-hidden="true"></i></button>';
						$html_input .= '</li>';

						$opt_index0 =  $last_array_id;
                    	$opt_index0++;
					}
				}else{
					$html_input .= '<li class="data-options" style=display:flex;>';
						$html_input .= '<span class="dashicons dashicons-move"></span>';
						$html_input .= '<input type="text" placeholder="'.__('option',"cfom").'" class="form-control cfom-option-keys" data-metatype="option">';
						$html_input .= '<input type="text" placeholder="'.__('Width',"cfom").'" class="form-control cfom-option-keys" data-metatype="width">';
						$html_input .= '<input type="text" placeholder="'.__('Height',"cfom").'" class="form-control cfom-option-keys" data-metatype="height">';
						$html_input .= '<input type="text" placeholder="'.__('Price (optional)',"cfom").'" class="form-control cfom-option-keys" data-metatype="price">';
						$html_input .= '<button class="btn btn-success cfom-add-option" data-option-type="paired-cropper"><i class="fa fa-plus" aria-hidden="true"></i></button>';
					$html_input .= '</li>';
				}
					$html_input .= '<input type="hidden" id="cfom-meta-opt-index" value="'.esc_attr($opt_index0).'">';
				$html_input	.= '<ul/>';
				
				break;
				
			case 'checkbox' :
				
				if ($options) {
					foreach ( $options as $key => $val ) {
						
						parse_str ( $values, $saved_data );
						$checked = '';
						if ( isset( $saved_data ['editing_tools'] ) && $saved_data ['editing_tools']) {
							if (in_array($key, $saved_data['editing_tools'])) {
								$checked = 'checked="checked"';
							}else{
								$checked = '';
							}
						}
						
						// For event Calendar Addon
						if ( isset( $saved_data ['cal_addon_disable_days'] ) && $saved_data ['cal_addon_disable_days']) {
							if (in_array($key, $saved_data['cal_addon_disable_days'])) {
								$checked = 'checked="checked"';
							}else{
								$checked = '';
							}
						}
						// $html_input .= '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>';
						$html_input .= '<label style="float:left;">';
							$html_input .= '<input type="checkbox" value="' . $key . '" name="cfom['.esc_attr($field_index).']['.esc_attr($name).'][]" ' . $checked . '> ' . $val . '<br>';
							$html_input .= '<span></span>';
						$html_input .= '</label>';
					}
				} else {
					$checked = ( (isset($values) && $values != '' ) ? 'checked = "checked"' : '' );
						
						$html_input .= '<label style="float:left;">';
							$html_input .= '<input type="checkbox" class="cfom-meta-field" data-metatype="'.esc_attr($name).'" ' . $checked . '';
					
							if( $field_index != '') {

		                  		$html_input .= $existing_name;
		                	}
					
							$html_input .= '>';
							
							$html_input .= '<span></span>';
						$html_input .= '</label>';

				}
				break;
				
			case 'html-conditions' :
				
				$condition_index = 1;
				$rule_i = 1;
				if($values){
					
					$condition_rules = isset($values['rules']) ? $values['rules'] : array();
					$last_array_id   = max(array_keys($condition_rules));

					$visibility_show = ($values['visibility'] == 'Show') ? 'selected="selected"' : '';
					$visibility_hide = ($values['visibility'] == 'Hide') ? 'selected="selected"' : '';
					$bound_all       = ($values['bound'] == 'All') ? 'selected="selected"' : '';
					$bound_any       = ($values['bound'] == 'Any') ? 'selected="selected"' : '';
					
					$html_input	= '<div class="row cfom-condition-style-wrap">';
						$html_input	.= '<div class="col-md-3 col-sm-3">';
							$html_input	.= '<select name="cfom['.esc_attr($field_index).'][conditions][visibility]" class="form-control cfom-condition-visible-bound" data-metatype="visibility">';
								$html_input .= '<option '.$visibility_show.'>'.__( 'Show', 'cfom' ).'</option>';
								$html_input .= '<option '.$visibility_hide.'>'.__( 'Hide', 'cfom' ).'</option>';
							$html_input	.= '</select>';
						$html_input .= '</div>';

						$html_input	.= '<div class="col-md-2 col-sm-2">';
							$html_input .= '<p>'.__( 'only if', 'cfom' ).'</p>';
						$html_input .= '</div>';

						$html_input	.= '<div class="col-md-3 col-sm-3">';
							$html_input	.= '<select name="cfom['.esc_attr($field_index).'][conditions][bound]" class="form-control cfom-condition-visible-bound" data-metatype="bound">';
								$html_input .= '<option '.$bound_all.'>'.__( 'All', 'cfom' ).'</option>';
								$html_input .= '<option '.$bound_any.'>'.__( 'Any', 'cfom' ).'</option>';
							$html_input	.= '</select>';
						$html_input .= '</div>';

						$html_input	.= '<div class="col-md-4 col-sm-4">';
							$html_input .='<p>'.__( 'of the following matches', 'cfom' ).'</p>';
						$html_input .= '</div>';
					$html_input .= '</div>';

					$html_input .= '<div class="row cfom-condition-clone-js">';
					foreach ($condition_rules as $rule_index => $condition){

						$element_values   = isset($condition['element_values']) ? stripslashes($condition['element_values']) : '';
						$element          = isset($condition['elements']) ? stripslashes($condition['elements']) : '';
						$operator_is 	  = ($condition['operators'] == 'is') ? 'selected="selected"' : '';
						$operator_not 	  = ($condition['operators'] == 'not') ? 'selected="selected"' : '';
						$operator_greater = ($condition['operators'] == 'greater than') ? 'selected="selected"' : '';
						$operator_less 	  = ($condition['operators'] == 'less than') ? 'selected="selected"' : '';
						
							$html_input .= '<div class="webcontact-rules" id="rule-box-'.esc_attr($rule_i).'">';
								$html_input .= '<div class="col-md-12 col-sm-12"><label>'.__('Rule ', "cfom") . $rule_i++ .'</label></div>';
								
								// conditional elements
								$html_input .= '<div class="col-md-4 col-sm-4">';
									$html_input .= '<select name="cfom['.esc_attr($field_index).'][conditions][rules]['.esc_attr($rule_index).'][elements]" class="form-control cfom-conditional-keys" data-metatype="elements"
										data-existingvalue="'.esc_attr($element).'" >';
										$html_input .= '<option>'.$element.'</option>';
									$html_input .= '</select>';
								$html_input .= '</div>';

								// is value meta
								$html_input .= '<div class="col-md-2 col-sm-2">';
									$html_input .= '<select name="cfom['.esc_attr($field_index).'][conditions][rules]['.esc_attr($rule_index).'][operators]" class="form-control cfom-conditional-keys" data-metatype="operators">';
										$html_input	.= '<option '.$operator_is.'>'. __('is', "cfom").'</option>';
										$html_input .= '<option '.$operator_not.'>'. __('not', "cfom").'</option>';
										$html_input .= '<option '.$operator_greater.'>'. __('greater than', "cfom").'</option>';
										$html_input .= '<option '.$operator_less.'>'. __('less than', "cfom").'</option>';
									$html_input	.= '</select> ';
								$html_input .= '</div>';

								// conditional elements values
								$html_input .= '<div class="col-md-4 col-sm-4">';
									$html_input .= '<input type="text" name="cfom['.esc_attr($field_index).'][conditions][rules]['.esc_attr($rule_index).'][element_values]" class="form-control cfom-conditional-keys" value="'.esc_attr($element_values).'" placeholder="Enter Option" data-metatype="element_values">';
								$html_input .= '</div>';

								// Add and remove btn
								$html_input .= '<div class="col-md-2 col-sm-2">';
									$html_input .= '<button class="btn btn-success cfom-add-rule" data-index="5"><i class="fa fa-plus" aria-hidden="true"></i></button>';
								$html_input .= '</div>';
							$html_input .= '</div>';

						$condition_index = $last_array_id;
                    	$condition_index++;
					}
					$html_input .= '</div>';
				}else{

					$html_input .= '<div class="row cfom-condition-style-wrap">';
						$html_input	.= '<div class="col-md-4 col-sm-4">';
							$html_input	.= '<select class="form-control cfom-condition-visible-bound" data-metatype="visibility">';
								$html_input .= '<option>'.__('Show', "cfom").'</option>';
								$html_input .= '<option>'. __('Hide', "cfom").'</option>';
							$html_input	.= '</select> ';
						$html_input .= '</div>';
						$html_input	.= '<div class="col-md-4 col-sm-4">';
							$html_input	.= '<select class="form-control cfom-condition-visible-bound" data-metatype="bound">';
								$html_input .= '<option>'. __('All', "cfom").'</option>';
								$html_input .= '<option>'. __('Any', "cfom").'</option>';
							$html_input	.= '</select> ';
						$html_input .= '</div>';
						$html_input	.= '<div class="col-md-4 col-sm-4">';
							$html_input .='<p>'. __(' of the following matches', "cfom").'</p>';
						$html_input .= '</div>';
					$html_input .= '</div>';

					$html_input .= '<div class="row cfom-condition-clone-js">';
						$html_input .= '<div class="webcontact-rules" id="rule-box-'.esc_attr($rule_i).'">';
							$html_input .= '<div class="col-md-12 col-sm-12"><label>'.__('Rule ', "cfom") . $rule_i++ .'</label></div>';
							
							// conditional elements
							$html_input .= '<div class="col-md-4 col-sm-4">';
								$html_input .= '<select data-metatype="elements" class="cfom-conditional-keys form-control"></select>';
							$html_input .= '</div>';
							
							// is
							$html_input .= '<div class="col-md-2 col-sm-2">';
								$html_input .= '<select data-metatype="operators" class="cfom-conditional-keys form-control">';
									$html_input	.= '<option>'. __('is', "cfom").'</option>';
									$html_input .= '<option>'. __('not', "cfom").'</option>';
									$html_input .= '<option>'. __('greater than', "cfom").'</option>';
									$html_input .= '<option>'. __('less than', "cfom").'</option>';
								$html_input	.= '</select> ';
							$html_input .= '</div>';

							// conditional elements values
							$html_input .= '<div class="col-md-4 col-sm-4">';
								$html_input .= '<input type="text" class="form-control cfom-conditional-keys" placeholder="Enter Option" data-metatype="element_values">';
							$html_input .= '</div>';

							// Add and remove btn
							$html_input .= '<div class="col-md-2 col-sm-2">';
								$html_input .= '<button class="btn btn-success cfom-add-rule" data-index="5"><i class="fa fa-plus" aria-hidden="true"></i></button>';
							$html_input .= '</div>';

						$html_input .= '</div>';
					$html_input .= '</div>';
				}
				$html_input .= '<input type="hidden" class="cfom-condition-last-id" value="'.esc_attr($condition_index).'">';

				break;
				
				case 'pre-images' :
				
					$html_input	.= '<div class="pre-upload-box table-responsive">';
					
						$html_input	.= '<button class="btn btn-info cfom-pre-upload-image-btn" data-metatype="images">'.__('Select/Upload Image', "cfom").'</button>';

						$opt_index0  = 0;
						$html_input .= '<ul class="cfom-options-container">';
						if ($values) {
							
							$last_array_id = max(array_keys($values));

							foreach ($values as $opt_index => $pre_uploaded_image){
						
								$image_link = (isset($pre_uploaded_image['link']) ? $pre_uploaded_image['link'] : '');
								$image_id   = (isset($pre_uploaded_image['id']) ? $pre_uploaded_image['id'] : '');
								$image_url  = (isset($pre_uploaded_image['url']) ? $pre_uploaded_image['url'] : '');
								
								$image_name = isset($pre_uploaded_image['link']) ? basename($pre_uploaded_image['link']) : '';

								$html_input .= '<li class="data-options">';
									$html_input .= '<span class="dashicons dashicons-move" style="margin-bottom: 7px;margin-top: 2px;"></span>';	
									$html_input .= '<span class="cfom-uploader-img-title">'.$image_name.'</span>';
									$html_input .= '<div style="display: flex;">';
										$html_input .= '<div class="cfom-uploader-img-center">';
											$html_input .= '<img width="60" src="'.esc_url($image_link).'" style="width: 34px;">';
										$html_input .= '</div>';
										$html_input .= '<input type="hidden" name="cfom['.esc_attr($field_index).'][images]['.esc_attr($opt_index).'][link]" value="'.esc_url($image_link).'">';
										$html_input .= '<input type="hidden" name="cfom['.esc_attr($field_index).'][images]['.esc_attr($opt_index).'][id]" value="'.esc_attr($image_id).'">';
										$html_input .= '<input class="form-control" type="text" placeholder="Title" value="'.esc_attr(stripslashes($pre_uploaded_image['title'])).'" name="cfom['.esc_attr($field_index).'][images]['.esc_attr($opt_index).'][title]">';
										$html_input .= '<input class="form-control" type="text" placeholder="Price - Fix or % of cart total" value="'.esc_attr(stripslashes($pre_uploaded_image['price'])).'" name="cfom['.esc_attr($field_index).'][images]['.esc_attr($opt_index).'][price]">';
										$html_input .= '<input class="form-control" type="text" placeholder="URL" value="'.esc_url(stripslashes($pre_uploaded_image['url'])).'" name="cfom['.esc_attr($field_index).'][images]['.esc_attr($opt_index).'][url]">';
										$html_input .= '<button class="btn btn-danger cfom-pre-upload-delete" style="height: 35px;"><i class="fa fa-times" aria-hidden="true"></i></button>';
									$html_input .= '</div>';
								$html_input .= '</li>';

								$opt_index0 =  $last_array_id;
	                    		$opt_index0++;
							}
						}
						$html_input .= '</ul>';
						$html_input .= '<input type="hidden" id="cfom-meta-opt-index" value="'.esc_attr($opt_index0).'">';
					
					$html_input .= '</div>';
				
				break;
				
				case 'pre-audios' :
				
					$html_input	.= '<div class="pre-upload-box">';
					$html_input	.= '<button class="btn btn-info cfom-pre-upload-image-btn" data-metatype="audio">'.__('Select Audio/Video', "cfom").'</button>';
					
					$html_input .= '<ul class="cfom-options-container">';
					$opt_index0  = 0;
					
					if ($values) {
						$last_array_id = max(array_keys($values));
						foreach ($values as $opt_index => $pre_uploaded_image){
					
							$image_link  = (isset($pre_uploaded_image['link']) ? $pre_uploaded_image['link'] : '');
							$image_id    = (isset($pre_uploaded_image['id']) ? $pre_uploaded_image['id'] : '');
							$image_url   = (isset($pre_uploaded_image['url']) ? $pre_uploaded_image['url'] : '');
							$media_title = (isset($pre_uploaded_image['title']) ? stripslashes($pre_uploaded_image['title']) : '');
							$media_price = (isset($pre_uploaded_image['price']) ? stripslashes($pre_uploaded_image['price']) : '');
							
							$html_input .= '<li class="data-options">';
								$html_input .= '<span class="dashicons dashicons-move" style="margin-bottom: 7px;margin-top: 2px;"></span>';
								$html_input .= '<div style="display: flex;">';
									$html_input .= '<div class="cfom-uploader-img-center">';
										$html_input .= '<span class="dashicons dashicons-admin-media" style="margin-top: 5px;"></span>';
									$html_input .= '</div>';
									$html_input .= '<input type="hidden" name="cfom['.esc_attr($field_index).'][audio]['.esc_attr($opt_index).'][link]" value="'.esc_url($image_link).'">';
									$html_input .= '<input type="hidden" name="cfom['.esc_attr($field_index).'][audio]['.esc_attr($opt_index).'][id]" value="'.esc_attr($image_id).'">';
									$html_input .= '<input class="form-control" type="text" placeholder="Title" value="'.esc_attr($media_title).'" name="cfom['.esc_attr($field_index).'][audio]['.esc_attr($opt_index).'][title]">';
									$html_input .= '<input class="form-control" type="text" placeholder="Price - Fix or % of cart total" value="'.esc_attr($media_price).'" name="cfom['.esc_attr($field_index).'][audio]['.esc_attr($opt_index).'][price]">';
									$html_input .= '<button class="btn btn-danger cfom-pre-upload-delete" style="height: 35px;"><i class="fa fa-times" aria-hidden="true"></i></button>';
								$html_input .= '</div>';
							$html_input .= '</li>';

							$opt_index0 =  $last_array_id;
                    		$opt_index0++;
					
						}
					}
						$html_input .= '</ul>';
						$html_input .= '<input type="hidden" id="cfom-meta-opt-index" value="'.esc_attr($opt_index0).'">';
					$html_input .= '</div>';
				
				break;
		}
		
		
		return apply_filters('cfom_render_input_types', $html_input, $type, $name, $values, $options);
	}
	
	/* 
    **============ Get fields title =========== 
    */
	function meta_title(){
        return array (
                'type' => 'text',
                'title'=> __ ( 'Title', 'cfom' ),
                'desc' => __ ( 'It will be shown as field label', 'cfom' ),
        	);
    }
    
    /* 
    **============ Get fields dataname =========== 
    */
    function meta_data_name(){
        return array (
                'type'  => 'text',
                'title' => __ ( 'Field ID', 'cfom' ),
                'desc'  => __ ( 'REQUIRED: The identification name of this field, that you can insert into body email configuration. Note:Use only lowercase characters and underscores.', 'cfom' )
            );
    }
    
    /* 
    **============ Get fields placeholder =========== 
    */
    function meta_placeholder() {
        return array (
                'type'  => 'text',
                'title' => __ ( 'Placeholder', 'cfom' ),
                'desc'  => __ ( 'Optionally placeholder.', 'cfom' ) 
            );
    }
    
    /* 
    **============ Get fields description =========== 
    */
    function meta_desc(){
      return  array (
                'type'  => 'textarea',
                'title' => __ ( 'Description', 'cfom' ),
                'desc'  => __ ( 'Small description, it will be diplay near name title.', 'cfom' ) 
            );
    }
    
    /* 
    **============ Get fields error message =========== 
    */
    function meta_error_message(){
       return array (
                    'type'  => 'text',
                    'title' => __ ( 'Error message', 'cfom' ),
                    'desc'  => __ ( 'Insert the error message for validation.', 'cfom' ) 
                );
    }
    
    /* 
    **============ Get fields classes =========== 
    */
    function meta_class(){
       return  array (
                'type'  => 'text',
                'title' => __ ( 'Class', 'cfom' ),
                'desc'  => __ ( 'Insert an additional class(es) (separateb by comma) for more personalization.', 'cfom' ) 
            );
    }
    
    /* 
    **============ Get fields width =========== 
    */
    function meta_width(){
    	
    	$cfom_cols = array(
    					2=>'2 Col',
    					3=>'3 Col', 
    					4=>'4 Col',
    					5=>'5 Col',
    					6=>'6 Col',
						7=>'7 Col',
						8=>'8 Col',
						9=>'9 Col',
						10=>'10 Col',
						11=>'11 Col',
						12=>'12 Col'
					);
				
        return array (
                'type'    => 'select',
                'title'   => __ ( 'Width', 'cfom' ),
                'desc'    => __ ( 'Select column for this field from 12 columns Grid', 'cfom' ), 
                'options' => $cfom_cols,
                'default' => 12,
            );
    }
    
    /* 
    **============ Get fields visibility =========== 
    */
    function meta_visibility() {

        $visibility_options = array(
        						'everyone'	=> __('Everyone'),
								'guests'	=> __('Only Guests'),
								'members'	=> __('Only Members'),
								'roles'		=> __('By Role')
							);

        return array (
                'type'    => 'select',
                'title'   => __ ( 'Visibility', 'cfom' ),
                'desc'    => __ ( 'Set field visibility based on user.', 'cfom' ),
                'options' => $visibility_options,
                'default' => 'everyone', 
            );
    }
    
    /* 
    **============ Get fields visibility role =========== 
    */
    function meta_visibility_role() {
        return array (
                'type'  => 'text',
                'title' => __ ( 'User Roles', 'cfom' ),
                'desc'  => __ ( 'Role separated by comma.', 'cfom' ),
                'hidden' => true,
            );
    }
    
    /* 
    **============ Get fields tooltips =========== 
    */
    function meta_desc_tooltip() {
        return array (
				'type' => 'checkbox',
				'title' => __ ( 'Show tooltip (PRO)', 'cfom' ),
				'desc' => __ ( 'Show Description in Tooltip with Help Icon', 'cfom' )
			);
    }
    
    /* 
    **============ Get fields required =========== 
    */
    function meta_required(){
    
        return array (
                'type'  => 'checkbox',
                'title' => __ ( 'Required', 'cfom' ),
                'desc'  => __ ( 'Select this if it must be required.', 'cfom' ) 
            );
    }
    
    /* 
    **============ Get fields condition logic option =========== 
    */
    function meta_logic(){
    
        return array (
				'type' => 'checkbox',
				'title' => __ ( 'Enable Conditions', 'cfom' ),
				'desc' => __ ( 'Tick it to turn conditional logic to work below', 'cfom' )
			);
    }
    
    /* 
    **============ Get fields condition meta =========== 
    */
    function meta_conditions(){
    
        return array (
				'type' => 'html-conditions',
				'title' => __ ( 'Conditions', 'cfom' ),
				'desc' => __ ( 'Tick it to turn conditional logic to work below', 'cfom' )
			);
    }
    
    /* 
    **============ Get fields options =========== 
    */
    function meta_options(){
    
        $meta_options = array (
					'type' => 'paired',
					'title' => __ ( 'Add options', 'cfom' ),
					'desc' => __ ( 'Type option with price (optionally), Option ID should be unique and without spaces.', 'cfom' )
			);
			
		return apply_filters('cfom_meta_options', $meta_options);
    }
    
    /* 
    **============ Get fields options =========== 
    */
    function meta_countries(){
    
        return array (
					'type' => 'country',
					'title' => __ ( 'Countries', 'cfom' ),
					'desc' => __ ( 'All countries are loaded from WC', 'cfom' )
			);
    }
    
	
	/* 
    **============ Feilds meta array =========== 
    */
    function fields_meta_array() {

		$cfom_fields = array(

            'text'      => array(
    
                'title' => __('Text Input','cfom'),
                'icon'  => __('<i class="fa fa-pencil-square-o" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular text input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'placeholder'   => $this->meta_placeholder(),
                    'description'   => $this->meta_desc(),
                    'error_message' => $this->meta_error_message(),
                    'maxlength' => array (
							'type'  => 'text',
							'title' => __ ( 'Max. Length', 'cfom' ),
							'desc'  => __ ( 'Max. characters allowed, leave blank for default.', 'cfom' )
					),
					'minlength' => array (
							'type'  => 'text',
							'title' => __ ( 'Min. Length', 'cfom' ),
							'desc'  => __ ( 'Min. characters allowed, leave blank for default.', 'cfom' )
					),
					'default_value' => array (
							'type'  => 'text',
							'title' => __ ( 'Set default value', 'cfom' ),
							'desc'  => __ ( 'Pre-defined value for text input.', 'cfom' )
					),
                    'class'         => $this->meta_class(),
                    'input_mask' => array (
							'type'  => 'text',
							'title' => __ ( 'Input Masking', 'ppom' ),
							'desc'  => __ ( 'Click options to see all Masking Options.', 'ppom' ),
							'link'  => __ ( '<a href="https://github.com/RobinHerbots/Inputmask" target="_blank">Options</a>', 'ppom' ) 
					),
                    'width'           => $this->meta_width(),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                    'desc_tooltip'    => $this->meta_desc_tooltip(),
                    'required'        => $this->meta_required(),
                    'logic'           => $this->meta_logic(),
                    'conditions'      => $this->meta_conditions(),
                   
                )
            ),
            
            
            'textarea'  => array(
    
                'title' => __('Textarea Input','cfom'),
                'icon'  => __('<i class="fa fa-file-text-o" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular textarea input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'description'   => $this->meta_desc(),
                    'error_message' => $this->meta_error_message(),
		            'default_value' => array (
							'type'  => 'text',
							'title' => __ ( 'Post ID', "cfom" ),
							'desc'  => __ ( 'It will pull content from post. e.g: 22.', "cfom" )
					),
					'rows' => array (
							'type'  => 'text',
							'title' => __ ( 'Rows', "cfom" ),
							'desc'  => __ ( 'e.g: 3.', "cfom" )
					),
					'max_length' => array (
							'type'  => 'text',
							'title' => __ ( 'Max. Length', "cfom" ),
							'desc'  => __ ( 'Max. characters allowed, leave blank for default.', "cfom" )
					),
                    'class'         => $this->meta_class(),
                    'width'           => $this->meta_width(),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                    'desc_tooltip'    => $this->meta_desc_tooltip(),
                    'required'        => $this->meta_required(),
                    'rich_editor' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Rich Editor', "cfom" ),
							'desc' => __ ( 'Enable WordPress rich editor.', "cfom" ),
							'link' => __ ( '<a target="_blank" href="https://codex.wordpress.org/Function_Reference/wp_editor">Editor</a>', 'cfom' ) 
					),
                    'logic'           => $this->meta_logic(),
                    'conditions'      => $this->meta_conditions(),
                   
                )
            ),
            
            
            'select'    => array(
    
                'title' => __('Select Input','cfom'),
                'icon'  => __('<i class="fa fa-check" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular select input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'description'   => $this->meta_desc(),
                    'error_message' => $this->meta_error_message(),
		            'options'       => $this->meta_options(),
					'selected' => array (
							'type' => 'text',
							'title' => __ ( 'Selected option', 'cfom' ),
							'desc' => __ ( 'Type option name given in (Add Options) tab if you want already selected.', 'cfom' ) 
					),
					'first_option' => array (
							'type' => 'text',
							'title' => __ ( 'First option', 'cfom' ),
							'desc' => __ ( 'Just for info e.g: Select your option.', 'cfom' ) 
					),
                    'class'         => $this->meta_class(),
                    'width'           => $this->meta_width(),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                    'desc_tooltip'    => $this->meta_desc_tooltip(),
                    'required'        => $this->meta_required(),
                    'onetime' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Fixed Fee', 'cfom' ),
							'desc' => __ ( 'Add one time fee to cart total.', 'cfom' ) 
					),
					'onetime_taxable' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Fixed Fee Taxable?', 'cfom' ),
							'desc' => __ ( 'Calculate Tax for Fixed Fee', 'cfom' ) 
					),
                    'logic'           => $this->meta_logic(),
                    'conditions'      => $this->meta_conditions(),
                   
                )
            ),
            
            
            'radio'     => array(
    
                'title' => __('Radio Input','cfom'),
                'icon'  => __('<i class="fa fa-dot-circle-o" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular select input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'description'   => $this->meta_desc(),
                    'error_message' => $this->meta_error_message(),
		            'options'       => $this->meta_options(),
					'selected' => array (
							'type'  => 'text',
							'title' => __ ( 'Selected option', "cfom" ),
							'desc'  => __ ( 'Type option name given in (Add Options) tab if you want already selected.', "cfom" ) 
					),
                    'class'         => $this->meta_class(),
                    'width'           => $this->meta_width(),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                    'desc_tooltip'    => $this->meta_desc_tooltip(),
                    'required'        => $this->meta_required(),
                    'onetime' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Fixed Fee', 'cfom' ),
							'desc' => __ ( 'Add one time fee to cart total.', 'cfom' ) 
					),
					'onetime_taxable' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Fixed Fee Taxable?', 'cfom' ),
							'desc' => __ ( 'Calculate Tax for Fixed Fee', 'cfom' ) 
					),
                    'logic'           => $this->meta_logic(),
                    'conditions'      => $this->meta_conditions(),
                   
                )
            ),
            
            
            'checkbox'  => array(
    
                'title' => __('Checkbox Input','cfom'),
                'icon'  => __('<i class="fa fa-check-square-o" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular checkbox input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'description'   => $this->meta_desc(),
                    'error_message' => $this->meta_error_message(),
		            'options'       => $this->meta_options(),
		            'checked' => array (
							'type' => 'textarea',
							'title' => __ ( 'Checked option(s)', "cfom" ),
							'desc' => __ ( 'Type option(s) name given in (Add Options) tab if you want already checked.', "cfom" ) 
					),
					'min_checked' => array (
							'type' => 'text',
							'title' => __ ( 'Min. Checked option(s)', "cfom" ),
							'desc' => __ ( 'How many options can be checked by user e.g: 2. Leave blank for default.', "cfom" ) 
					),
					'max_checked' => array (
							'type' => 'text',
							'title' => __ ( 'Max. Checked option(s)', "cfom" ),
							'desc' => __ ( 'How many options can be checked by user e.g: 3. Leave blank for default.', "cfom" ) 
					),
                    'class'         => $this->meta_class(),
                    'width'           => $this->meta_width(),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                    'desc_tooltip'    => $this->meta_desc_tooltip(),
                    'required'        => $this->meta_required(),
                    'onetime' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Fixed Fee', 'cfom' ),
							'desc' => __ ( 'Add one time fee to cart total.', 'cfom' ) 
					),
					'onetime_taxable' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Fixed Fee Taxable?', 'cfom' ),
							'desc' => __ ( 'Calculate Tax for Fixed Fee', 'cfom' ) 
					),
                    'logic'           => $this->meta_logic(),
                    'conditions'      => $this->meta_conditions(),
                   
                )
            ),
            
            
            'number'    => array(
    
                'title' => __('Number Input','cfom'),
                'icon'  => __('<i class="fa fa-check-square-o" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular number input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'placeholder'   => $this->meta_placeholder(),
                    'description'   => $this->meta_desc(),
                    'error_message' => $this->meta_error_message(),
		            'max' => array (
							'type' => 'text',
							'title' => __ ( 'Max. values', "cfom" ),
							'desc' => __ ( 'Max. values allowed, leave blank for default', "cfom" )
					),
					'min' => array (
							'type' => 'text',
							'title' => __ ( 'Min. values', "cfom" ),
							'desc' => __ ( 'Min. values allowed, leave blank for default', "cfom" )
					),
					'step' => array (
							'type' => 'text',
							'title' => __ ( 'Steps', "cfom" ),
							'desc' => __ ( 'specified legal number intervals', "cfom" )
					),
					'default_value' => array (
							'type' => 'text',
							'title' => __ ( 'Set default value', "cfom" ),
							'desc' => __ ( 'Pre-defined value for text input', "cfom" )
					),
                    'class'         => $this->meta_class(),
                    'width'           => $this->meta_width(),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                    'desc_tooltip'    => $this->meta_desc_tooltip(),
                    'required'        => $this->meta_required(),
                    'logic'           => $this->meta_logic(),
                    'conditions'      => $this->meta_conditions(),
                   
                )
            ),
            
            
            'date'      => array(
    
                'title' => __('Date Input','cfom'),
                'icon'  => __('<i class="fa fa-calendar" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular date input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'description'   => $this->meta_desc(),
                    'error_message' => $this->meta_error_message(),
		            'default_value' => array (
							'type' => 'text',
							'title' => __ ( 'Default Date', 'cfom' ),
							'desc' => __ ( 'User format YYYY-MM-DD e.g: 2017-05-25.', 'cfom' ),
					),
					'date_formats' => array (
							'type' => 'select',
							'title' => __ ( 'Date formats', 'cfom' ),
							'desc' => __ ( 'Select date format. (if jQuery enabled below)', 'cfom' ),
							'options' => cfom_get_date_formats(),
					),
					'year_range' => array (
							'type' => 'text',
							'title' => __ ( 'Year Range', 'cfom' ),
							'desc' => __ ( 'e.g: 1950:2016. (if jQuery enabled below) Set start/end year like used example.', 'cfom' ),
							'link' => __ ( '<a target="_blank" href="http://api.jqueryui.com/datepicker/#option-yearRange">Example</a>', 'cfom' )
					),
                    'class'         => $this->meta_class(),
                    'width'           => $this->meta_width(),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                    'desc_tooltip'    => $this->meta_desc_tooltip(),
                    'required'        => $this->meta_required(),
                    'jquery_dp' => array (
							'type' => 'checkbox',
							'title' => __ ( 'jQuery Datepicker (PRO)', 'cfom' ),
							'desc' => __ ( 'It will load jQuery fancy datepicker.', 'cfom' ) 
					),
					'past_dates' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Disable Past Dates', 'cfom' ),
							'desc' => __ ( 'It will disable past dates.', 'cfom' ) 
					),
					'no_weekends' => array (
							'type' => 'checkbox',
							'title' => __ ( 'Disable Weekends', 'cfom' ),
							'desc' => __ ( 'It will disable Weekends.', 'cfom' ) 
					),
                    'logic'           => $this->meta_logic(),
                    'conditions'      => $this->meta_conditions(),
                   
                )
            ),
            
            
            'email'     => array(
    
                'title' => __('Email Input','cfom'),
                'icon'  => __('<i class="fa fa-user-plus" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular email input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'description'   => $this->meta_desc(),
                    'error_message' => $this->meta_error_message(),
                    'class'         => $this->meta_class(),
                    'width'           => $this->meta_width(),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                    'desc_tooltip'    => $this->meta_desc_tooltip(),
                    'required'        => $this->meta_required(),
                    'logic'           => $this->meta_logic(),
                    'conditions'      => $this->meta_conditions(),
                   
                )
            ),
            
            
            'hidden'    => array(
    
                'title' => __('Hidden Input','cfom'),
                'icon'  => __('<i class="fa fa-hashtag" aria-hidden="true"></i>','cfom'),
                'desc'  => __('Regular hidden input.','cfom'),
                'field_meta'  => array (
                    
                    'title'         => $this->meta_title(),
                    'data_name'     => $this->meta_data_name(),
                    'field_value' => array (
							'type' => 'text',
							'title' => __ ( 'Field value', "cfom" ),
							'desc' => __ ( 'you can pre-set the value of this hidden input.', "cfom" )
					),
                    'visibility'      => $this->meta_visibility(),
                    'visibility_role' => $this->meta_visibility_role(),
                   
                )
            ),
            
            'country'	=> array(
    
                            'title' => __('Country Input','cfom'),
                            'icon'  => __('<i class="fa fa-check" aria-hidden="true"></i>','cfom'),
                            'desc'  => __('Country input.','cfom'),
                            'field_meta'  => array (
                                
                                'title'         => $this->meta_title(),
                                'data_name'     => $this->meta_data_name(),
                                'description'   => $this->meta_desc(),
                                'error_message' => $this->meta_error_message(),
                                'options'		=> $this->meta_countries(),
                                'class'         => $this->meta_class(),
                                'width'           => $this->meta_width(),
                                'visibility'      => $this->meta_visibility(),
                                'visibility_role' => $this->meta_visibility_role(),
                                'desc_tooltip'    => $this->meta_desc_tooltip(),
                                'required'        => $this->meta_required(),
                                'logic'           => $this->meta_logic(),
                                'conditions'      => $this->meta_conditions(),
                               
                            )
                        ),
    
            
            
        );
    
        return apply_filters('cfom_field_meta_array', $cfom_fields, $this);
    }

}

CFOM_FIELDS_META();
function CFOM_FIELDS_META(){
    return CFOM_Fields_Meta::get_instance();
}