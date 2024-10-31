<?php
/**
 * Inputs rendering class
 **/
 

// constants/configs
define( 'CFOM_ECHOABLE', false );

class CFOM_Form {
     
     
     /**
	 * the static object instace
	 */
	private static $ins = null;
	
	private $echoable;
	
	private $defaults;
	
	
     function __construct() {
         
        //  should control echo or return
        $this -> echoable = $this->get_property( 'echoable' );
        
        // control defaul settings
        $this -> defaults = $this->get_property( 'defaults' );
        
        // Local filters
        add_filter('nmform_attribute_value', array($this, 'adjust_attributes_values'), 10, 3);
     }
 
     public function Input( $args, $default_value = '' ) {
         
         $type       = $this -> get_attribute_value( 'type', $args);
         
         switch( $type ) {
             
            case 'text':
            case 'date':
            case 'daterange':
            case 'datetime-local':
            case 'email':
            case 'number':
            case 'color':
                
                $input_html = $this -> Regular( $args, $default_value );
                
            break;
            
            case 'textarea':
                $input_html = $this -> Textarea( $args, $default_value );
            break;
            
            case 'select':
                $input_html = $this -> Select( $args, $default_value );
            break;
            
            case 'country':
                $input_html = $this -> Country( $args, $default_value );
            break;
            
            case 'timezone':
                $input_html = $this -> Timezone( $args, $default_value );
            break;
            
            case 'checkbox':
                $input_html = $this -> Checkbox( $args, $default_value );
            break;
            
            case 'radio':
                $input_html = $this -> Radio( $args, $default_value );
            break;
            
            case 'palettes':
                $input_html = $this -> Palettes( $args, $default_value );
            break;
            
            case 'image':
                $input_html = $this -> Image( $args, $default_value );
            break;
            
            case 'section':
                $input_html = $this -> Section( $args, $default_value );
            break;
            
            case 'audio':
                $input_html = $this -> Audio_video( $args, $default_value );
            break;
            
            case 'file':
                $input_html = $this -> File( $args, $default_value );
            break;
            
            case 'cropper':
                $input_html = $this -> Cropper( $args, $default_value );
            break;
            
         }
         
         if( $this -> echoable )
            echo $input_html;
        else
            return $input_html;
     }
     
    /**
     * Regular Input Field
     * 1. Text
     * 2. Date
     * 3. Email
     * 4. Number
     * 5. color
     **/
     
    public function Regular( $args, $default_value = '' ) {
         
        global $product;
        
        $type       = $this -> get_attribute_value( 'type', $args);
        
        $label      = $this -> get_attribute_value('label', $args);
        $classes    = $this -> get_attribute_value('classes', $args);
        $id         = $this -> get_attribute_value('id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        $placeholder= $this -> get_attribute_value('placeholder', $args);
        $attributes = $this -> get_attribute_value('attributes', $args);
        
        $num_min    = $this -> get_attribute_value('min', $args);
        $num_max    = $this -> get_attribute_value('max', $args);
        $step       = $this -> get_attribute_value('step', $args);
        
        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $input_wrapper_id    = apply_filters('cfom_input_wrapper_id', $id.'_field');
        $html       = '<p class="'.esc_attr($input_wrapper_class).'" id="'.esc_attr($input_wrapper_id).'">';
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        // cfom_pa($args);
        
        $html       .= '<span class="woocommerce-input-wrapper">';
        $html       .= '<input type="'.esc_attr($type).'" ';
        $html       .= 'id="'.esc_attr($id).'" ';
        $html       .= 'name="'.esc_attr($name).'" ';
        $html       .= 'class="'.esc_attr($classes).'" ';
        $html       .= 'placeholder="'.esc_attr($placeholder).'" ';
        $html       .= 'autocomplete="off" ';
        $html       .= 'data-type="'.esc_attr($type).'" ';
        
        // Adding min/max for number input
        if( $type == 'number' ) {
            $html       .= 'min="'.esc_attr($num_min).'" ';
            $html       .= 'max="'.esc_attr($num_max).'" ';
            $html       .= 'step="'.esc_attr($step).'" ';
        }
        
        //Values
        if( $default_value != '')
        $html      .= 'value="'.esc_attr($default_value).'" ';
        
        // Attributes
        foreach($attributes as $attr => $value) {
            
            $html      .= esc_attr($attr) . '="'.esc_attr($value).'" ';
        }
        
        $html   .= '>';
        $html   .= '</span>';
        $html   .= '</p>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $default_value);
    }
    
    
    /**
     * Textarea field only
     * 
     * filter: nmforms_input_htmls
     * filter: 
     * */
    function Textarea($args, $default_value = '') {
        
        $label      = $this -> get_attribute_value('label', $args);
        $classes    = $this -> get_attribute_value('classes', $args);
        $id         = $this -> get_attribute_value('id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        $placeholder= $this -> get_attribute_value('placeholder', $args);
        $attributes = $this -> get_attribute_value('attributes', $args);
        $rich_editor= $this -> get_attribute_value('rich_editor', $args);
        
        // cols & rows
        $cols       = $this -> get_attribute_value( 'cols', $args );
        $rows       = $this -> get_attribute_value( 'rows', $args );
        
        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        
        if( $rich_editor == 'on' ) {
						
			$wp_editor_setting = array('media_buttons'=> false,
									'textarea_rows'=> $rows,
									'editor_class' => $classes,
									'teeny'			=> true,
									'textarea_name'	=> $name	);
									
			ob_start();
            wp_editor($default_value, $id, $wp_editor_setting);
            $html .= ob_get_clean();
			
        } else {
        
            $html       .= '<textarea ';
            $html       .= 'id="'.esc_attr($id).'" ';
            $html       .= 'name="'.esc_attr($name).'" ';
            $html       .= 'class="'.esc_attr($classes).'" ';
            $html       .= 'placeholder="'.esc_attr($placeholder).'" ';
            // $html       .= 'cols="'.esc_attr($cols).'" ';
            $html       .= 'rows="'.esc_attr($rows).'" ';
            
            // Attributes
            foreach($attributes as $attr => $value) {
                
                $html      .= esc_attr($attr) . '="'.esc_attr($value).'" ';
            }
            
            $html      .= '>';  // Closing textarea
            
            //Values
            if( $default_value != '')
                $html      .= esc_html($default_value);
            
            $html      .= '</textarea>';
        }
        
        $html      .= '</div>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $default_value);
        
    }
    
    /**
     * Select options
     * 
     * $options: array($key => $value)
     **/
    public function Select( $args, $selected_value = '' ) {
        
        $label      = $this -> get_attribute_value('label', $args);
        $classes    = $this -> get_attribute_value('classes', $args);
        $id         = $this -> get_attribute_value('id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        $multiple   = $this -> get_attribute_value('multiple', $args);
        $attributes = $this -> get_attribute_value('attributes', $args);
        
        // Only title without description for price calculation etc.
        $title      = $args['title'];
        // One time fee
        $onetime    = $args['onetime'];
        $taxable	= $args['taxable'];
        
        
        // Options
        $options    = $this -> get_attribute_value('options', $args);
        
        if ( ! $options ) return;

        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        $html       .= '<span class="woocommerce-input-wrapper">';
        $html       .= '<select ';
        $html       .= 'id="'.esc_attr($id).'" ';
        $html       .= 'name="'.esc_attr($name).'" ';
        $html       .= 'class="'.esc_attr($classes).'" ';
        $html       .= ($multiple) ? 'multiple' : '';
        
        // Attributes
        foreach($attributes as $attr => $value) {
            
            $html      .= esc_attr($attr) . '="'.esc_attr($value).'" ';
        }
        
        $html   .= '>';  // Closing select
        $html   .= '</span>';
        
        foreach($options as $key => $value) {
            
            // for multiple selected
            
            $option_label   = $value['label'];
            $option_price   = $value['price'];
            $option_id      = isset($value['option_id']) ? $value['option_id'] : '';
            $raw_label      = $value['raw'];
            $without_tax    = $value['without_tax'];
            
            if( is_array($selected_value) ){
            
                foreach($selected_value as $s){
                    $html   .= '<option '.selected( $s, $key, false ).' value="'.esc_attr($key).'" ';
                    $html   .= 'data-price="'.esc_attr($option_price).'" ';
                    $html   .= 'data-label="'.esc_attr($option_label).'"';
                    $html   .= 'data-onetime="'.esc_attr($onetime).'"';
                    $html   .= '>'.$option_label.'</option>';
                }
            } else {
                $html   .= '<option '.selected( $selected_value, $key, false ).' ';
                $html   .= 'value="'.esc_attr($key).'" ';
                $html   .= 'data-price="'.esc_attr($option_price).'" ';
                $html   .= 'data-optionid="'.esc_attr($option_id).'" ';
                $html   .= 'data-label="'.esc_attr($raw_label).'"';
                $html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
                $html   .= 'data-onetime="'.esc_attr($onetime).'"';
                $html   .= 'data-taxable="'.esc_attr($taxable).'"';
                $html   .= 'data-without_tax="'.esc_attr($without_tax).'"';
                $html   .= 'data-data_name="'.esc_attr($id).'"';
                $html   .= '>'.$option_label.'</option>';
            }
        }
        
        $html .= '</select>';
        $html      .= '</div>';    //form-group
        
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $selected_value);
    }
    
    
    /**
     * Country options
     * 
     * $options: array($key => $value)
     **/
    public function Country( $args, $selected_value = '' ) {
        
        $label      = $this -> get_attribute_value('label', $args);
        $classes    = $this -> get_attribute_value('classes', $args);
        $id         = $this -> get_attribute_value('id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        $multiple   = $this -> get_attribute_value('multiple', $args);
        $attributes = $this -> get_attribute_value('attributes', $args);
        
        // Only title without description for price calculation etc.
        $title      = $args['title'];
      
        // Options
        $options    = $this -> get_attribute_value('options', $args);
        
        if ( ! $options ) return;

        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        $html       .= '<span class="woocommerce-input-wrapper">';
        $html       .= '<select ';
        $html       .= 'id="'.esc_attr($id).'" ';
        $html       .= 'name="'.esc_attr($name).'" ';
        $html       .= 'class="'.esc_attr($classes).'" ';
        $html       .= ($multiple) ? 'multiple' : '';
        
        // Attributes
        foreach($attributes as $attr => $value) {
            
            $html      .= esc_attr($attr) . '="'.esc_attr($value).'" ';
        }
        
        $html   .= '>';  // Closing select
        $html   .= '</span>';
        
        foreach($options as $key => $value) {
            
            // for multiple selected
            
            $option_label   = $value['label'];
            $option_price   = $value['price'];
            $option_id      = isset($value['option_id']) ? $value['option_id'] : '';
            $raw_label      = $value['raw'];
            
            if( is_array($selected_value) ){
            
                foreach($selected_value as $s){
                    $html   .= '<option '.selected( $s, $key, false ).' value="'.esc_attr($key).'" ';
                    $html   .= 'data-price="'.esc_attr($option_price).'" ';
                    $html   .= 'data-label="'.esc_attr($option_label).'"';
                    $html   .= 'data-onetime="'.esc_attr($onetime).'"';
                    $html   .= '>'.$option_label.'</option>';
                }
            } else {
                $html   .= '<option '.selected( $selected_value, $key, false ).' ';
                $html   .= 'value="'.esc_attr($key).'" ';
                $html   .= 'data-price="'.esc_attr($option_price).'" ';
                $html   .= 'data-optionid="'.esc_attr($option_id).'" ';
                $html   .= 'data-label="'.esc_attr($raw_label).'"';
                $html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
                $html   .= 'data-data_name="'.esc_attr($id).'"';
                $html   .= '>'.$option_label.'</option>';
            }
        }
        
        $html .= '</select>';
        $html      .= '</div>';    //form-group
        
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $selected_value);
    }
    
    /**
     * Timezone
     * 
     * $options: array($key => $value)
     **/
    public function Timezone( $args, $selected_value = '' ) {
        
        $label      = $this -> get_attribute_value('label', $args);
        $classes    = $this -> get_attribute_value('classes', $args);
        $id         = $this -> get_attribute_value('id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        $multiple   = $this -> get_attribute_value('multiple', $args);
        $attributes = $this -> get_attribute_value('attributes', $args);
        
        // Only title withou description for price calculation etc.
        $title      = $args['title'];
        
        
        // Options
        $options    = $this -> get_attribute_value('options', $args);


        if ( ! $options ) return;

        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        $html       .= '<select ';
        $html       .= 'id="'.esc_attr($id).'" ';
        $html       .= 'name="'.esc_attr($name).'" ';
        $html       .= 'class="'.esc_attr($classes).'" ';
        $html       .= ($multiple) ? 'multiple' : '';
        
        // Attributes
        foreach($attributes as $attr => $value) {
            
            $html      .= esc_attr($attr) . '="'.esc_attr($value).'" ';
        }
        
        $html       .= '>';  // Closing select
        
        // cfom_pa($options);
        foreach($options as $key => $option_label) {
            
            
            if( is_array($selected_value) ){
            
                foreach($selected_value as $s){
                    $html   .= '<option '.selected( $s, $key, false ).' value="'.esc_attr($key).'" ';
                    $html   .= 'data-price="'.esc_attr($option_price).'" ';
                    $html   .= 'data-label="'.esc_attr($option_label).'"';
                    $html   .= 'data-onetime="'.esc_attr($onetime).'"';
                    $html   .= '>'.$option_label.'</option>';
                }
            } else {
                $html   .= '<option '.selected( $selected_value, $key, false ).' ';
                $html   .= 'value="'.esc_attr($key).'" ';
                $html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
                $html   .= '>'.$option_label.'</option>';
            }
        }
        
        $html .= '</select>';
        $html .= '</div>';    //form-group
        
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $selected_value);
    }
    
    
    // Checkbox
    public function Checkbox( $args, $checked_value = array() ) {
        
        $type       = $this -> get_attribute_value( 'type', $args);
        
        $label      = $this -> get_attribute_value('label', $args);
        $classes    = $this -> get_attribute_value('classes', $args);
        $id         = $this -> get_attribute_value('id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        
        // Only title withou description for price calculation etc.
        $title      = $args['title'];
        // One time fee
        $onetime    = $args['onetime'];
        $taxable	= $args['taxable'];
        
        // Options
        $options    = $this -> get_attribute_value('options', $args);
        
        // Checkbox label class
        $check_wrapper_class = apply_filters('cfom_checkbox_wrapper_class','form-check-inline');
        $check_label_class = $this -> get_attribute_value('check_label_class', $args);

        if ( ! $options ) return;
        
        
        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        // $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
        
        foreach($options as $key => $value) {
            
            $option_label = $value['label'];
            $option_price = $value['price'];
            $raw_label      = $value['raw'];
            $without_tax    = $value['without_tax'];
            $option_id      = $value['option_id'];
            
            $checked_option = '';
            if( count($checked_value) > 0 && in_array($key, $checked_value) && !empty($key)){
            
                $checked_option = checked( $key, $key, false );
            }
            
            // $option_id = sanitize_key( $id."-".$key );
            
            $html       .= '<div class="'.esc_attr($check_wrapper_class).'">';
                $html       .= '<label class="'.esc_attr($check_label_class).'" for="'.esc_attr($option_id).'">';
                    $html       .= '<input type="'.esc_attr($type).'" ';
                    $html       .= 'id="'.esc_attr($option_id).'" ';
                    $html       .= 'name="'.esc_attr($name).'[]" ';
                    $html       .= 'class="'.esc_attr($classes).'" ';
                    $html       .= 'value="'.esc_attr($key).'"';
                    $html       .= 'data-optionid="'.esc_attr($option_id).'" ';
                    $html       .= 'data-price="'.esc_attr($option_price).'"';
                    $html       .= 'data-label="'.esc_attr($raw_label).'"';
                    $html       .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
                    $html       .= 'data-onetime="'.esc_attr($onetime).'"';
                    $html       .= 'data-taxable="'.esc_attr($taxable).'"';
                    $html       .= 'data-without_tax="'.esc_attr($without_tax).'"';
                    $html       .= 'data-data_name="'.esc_attr($id).'"';
                    $html       .= $checked_option;
                    $html       .= '> ';  // Closing checkbox
                    $html       .= '<span class="cfom-label-checkbox">'.$option_label.'</span>';
                $html       .= '</label>';    // closing form-check
            $html       .= '</div>';    // closing form-check
        }
        
        $html      .= '</div>';    //form-group
        
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $checked_value);
    }
    
    
    // Radio
    public function Radio( $args, $checked_value = '' ) {
        
        $type       = $this -> get_attribute_value( 'type', $args);
        
        $label      = $this -> get_attribute_value('label', $args);
        $classes    = $this -> get_attribute_value('classes', $args);
        $id         = $this -> get_attribute_value('id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        
        // Only title withou description for price calculation etc.
        $title      = $args['title'];
        // One time fee
        $onetime    = $args['onetime'];
        $taxable	= $args['taxable'];
        
        // Options
        $options    = $this -> get_attribute_value('options', $args);
        if ( ! $options ) return;
        
        // Radio label class
        $radio_wrapper_class = apply_filters('cfom_radio_wrapper_class','form-check');
        $radio_label_class = $this -> get_attribute_value('radio_label_class', $args);

        
        
        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        foreach($options as $key => $value) {
            
            $option_label   = $value['label'];
            $option_price   = $value['price'];
            $raw_label      = $value['raw'];
            $without_tax    = $value['without_tax'];
            $option_id      = $value['option_id'];
            
            $checked_option = '';
            if( ! empty($checked_value) ){
            
                $checked_value = stripcslashes($checked_value);
                $checked_option = checked( $checked_value, $key, false );
            }
            
            
            $html       .= '<div class="'.esc_attr($radio_wrapper_class).'">';
                $html       .= '<label class="'.esc_attr($radio_label_class).'" for="'.esc_attr($option_id).'">';
                    $html       .= '<input type="'.esc_attr($type).'" ';
                    $html       .= 'id="'.esc_attr($option_id).'" ';
                    $html       .= 'name="'.esc_attr($name).'" ';
                    $html       .= 'class="'.esc_attr($classes).'" ';
                    $html       .= 'value="'.esc_attr($key).'"';
                    $html       .= 'data-price="'.esc_attr($option_price).'"';
                    $html       .= 'data-optionid="'.esc_attr($option_id).'" ';
                    $html       .= 'data-label="'.esc_attr($raw_label).'"';
                    $html       .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
                    $html       .= 'data-onetime="'.esc_attr($onetime).'"';
                    $html       .= 'data-taxable="'.esc_attr($taxable).'"';
                    $html       .= 'data-without_tax="'.esc_attr($without_tax).'"';
                    $html       .= 'data-data_name="'.esc_attr($id).'"';
                    $html       .= $checked_option;
                    $html       .= '> ';  // Closing radio
                    $html       .= '<span class="cfom-label-radio">'.$option_label.'</span>';
                $html       .= '</label>';    // closing form-check
            $html       .= '</div>';    // closing form-check
        }
        
        $html      .= '</div>';    //form-group
        
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $checked_value);
    }
    
    // A custom input will be just some option html
    public function Palettes( $args, $default_value = '' ) {
         
        $type       = $this -> get_attribute_value( 'type', $args);
        $id         = $this -> get_attribute_value( 'id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        $label      = $this -> get_attribute_value( 'label', $args);
        $classes    = isset($args['classes']) ? $args['classes'] : '';
	    
	    // Only title withou description for price calculation etc.
        $title      = $args['title'];
        
        // One time fee
        $onetime    = $args['onetime'];
        $taxable	= $args['taxable'];
        
	    // Options
		$options    = isset($args['options']) ? $args['options'] : '';
		if ( ! $options ) return '';
		
// 		cfom_pa($options);

        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        $html .= '<div class="cfom-palettes cfom-palettes-'.esc_attr($id).'">';
     	foreach($options as $key => $value)
		{
			// First Separate color code and label
			$color_label_arr = explode('-', $key);
			$color_code = trim($color_label_arr[0]);
			$color_label = '';
			if(isset($color_label_arr[1])){
				$color_label = trim($color_label_arr[1]);
			}
			
			$option_label   = $value['label'];
        	$option_price   = $value['price'];
        	$raw_label      = $value['raw'];
        	$without_tax    = $value['without_tax'];

			$option_id      = $value['option_id'];
			
			$checked_option = '';

			if( ! empty($default_value) ){
        
                $checked_option = checked( $default_value, $key, false );
            }
            
			
			$html .= '<label for="'.esc_attr($option_id).'"> ';
				$html .= '<input id="'.esc_attr($option_id).'" ';
				$html .= 'data-price="'.esc_attr($option_price).'" ';
				$html .= 'data-label="'.esc_attr($color_label).'"';
				$html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
				$html .= 'type="radio" ';
				$html .= 'name="'.esc_attr($name).'" ';
				$html .= 'value="'.esc_attr($raw_label).'" ';
				$html .= 'data-onetime="'.esc_attr($onetime).'"';
                $html .= 'data-taxable="'.esc_attr($taxable).'"';
                $html .= 'data-without_tax="'.esc_attr($without_tax).'"';
				$html .= $checked_option;
				$html .= '>';
			
				
			$html .= '<span class="cfom-single-palette" ';
			$html	.= 'title="'.esc_attr($option_label).'" data-cfom-tooltip="cfom_tooltip"';
			$html	.= 'style="background-color:'.esc_attr($color_code).';';
			$html	.= 'width:'.esc_attr($args['color_width']).'px;';
			$html	.= 'height:'.esc_attr($args['color_height']).'px;';
			if( $args['display_circle'] ) {
			    $html	.= 'border-radius: 50%;';
			}
			$html	.= '">';    // Note '"' is to close style inline attribute
			$html	.= '';
			$html	.= '</span>';
		
			$html .= '</label>';
		}
		$html .= '</div>'; //.cfom-palettes
        
        $html   .= '</div>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $default_value);
    }
    
    // Image type
    public function Image( $args, $default_value = '' ) {
        
        global $product;
         
        $type       = $this -> get_attribute_value( 'type', $args);
        $id         = $this -> get_attribute_value( 'id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        $label      = $this -> get_attribute_value( 'label', $args);
        $classes    = $this -> get_attribute_value('classes', $args);
        
        // Only title withou description for price calculation etc.
        $title      = $args['title'];
        
	    // Options
	   // cfom_pa($args['images']);
		$images    = isset($args['images']) ? $args['images'] : '';
		if ( ! $images ) return __("Images not selected", 'cfom');

        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        // cfom_pa($args);
        if (isset($args['legacy_view']) && $args['legacy_view'] == 'on') {
	        $html .= '<div class="cfom_upload_image_box">';
			foreach ($images as $image){
						
				$image_full   = isset($image['link']) ? $image['link'] : 0;
				$image_id   = isset($image['id']) ? $image['id'] : 0;
				$image_title= isset($image['title']) ? stripslashes($image['title']) : 0;
				$image_price= isset($image['price']) ? $image['price'] : 0;
				
				// If price set in %
				if(strpos($image['price'],'%') !== false){
					$image_price = cfom_get_amount_after_percentage($product->get_price(), $image['price']);
				}

	            // Actually image URL is link
				$image_link = isset($image['url']) ? $image['url'] : '';
				$image_url  = wp_get_attachment_thumb_url( $image_id );
				$image_title_price = $image_title . ' ' . ($image_price > 0 ? '(+'.wc_price($image_price).')' : '');
				
				$checked_option = '';
				if( ! empty($default_value) ){
	        
	                $checked = ($image['title'] == $default_value ? 'checked = "checked"' : '' );
	                $checked_option = checked( $default_value, $key, false );
	            }
				
				$html .= '<div class="pre_upload_image '.esc_attr($classes).'">';
				
				if( !empty($image_link) ) {
				    $html .= '<a href="'.esc_url($image_link).'"><img class="img-thumbnail" src="'.esc_url($image_url).'" /></a>';
				} else {
				    $html .= '<img data-toggle="modal" class="img-thumbnail"  data-target="#modalImage'.esc_attr($image_id).'" src="'.esc_url($image_url).'" />';
				}
				
				// Loading Modals
				$modal_vars = array('image_id' => $image_id, 'image_full'=>$image_full, 'image_title'=>$image_title_price);
				cfom_load_template('v10/image-modals.php', $modal_vars);
				?>
				
				<?php
					
				$html	.= '<div class="input_image">';
				if ($args['multiple_allowed'] == 'on') {
					$html	.= '<input type="checkbox" ';
					$html   .= 'data-price="'.esc_attr($image_price).'" ';
					$html   .= 'data-label="'.esc_attr($image_title).'" ';
					$html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
					$html   .= 'name="'.$args['name'].'[]" ';
					$html   .= 'value="'.esc_attr(json_encode($image)).'" />';
				}else{
					
					//default selected
					$checked = ($image['title'] == $default_value ? 'checked = "checked"' : '' );
					$html	.= '<input type="radio" ';
					$html   .= 'data-price="'.esc_attr($image_price).'"';
					$html   .= 'data-label="'.esc_attr($image_title).'" ';
					$html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
					$html   .= 'data-type="'.esc_attr($type).'" name="'.$args['name'].'[]" ';
					$html   .= 'value="'.esc_attr(json_encode($image)).'" '.$checked_option.' />';
				}
					
			    $html	.= '<div class="p_u_i_name">'.$image_title_price.'</div>';
				$html	.= '</div>';	//input_image
					
					
				$html .= '</div>';  // pre_upload_image
			}
			
			$html .= '</div>'; //.cfom_upload_image_box
        	
        } else {
			$html .= '<div class="cfom_upload_image_box">';
				
			$img_index = 0;
			
			if ($images) {
			    
				
				foreach ($images as $image){

                    // cfom_pa($image);
					$image_full   = isset($image['link']) ? $image['link'] : 0;
					$image_id   = isset($image['id']) ? $image['id'] : 0;
					$image_title= isset($image['title']) ? stripslashes($image['title']) : 0;
					$image_price= isset($image['price']) ? $image['price'] : 0;
					$option_id  = $id.'-'.$image_id;
					
					$cart_total = WC()->cart->total;
					
                    // If price set in %
    				if(strpos($image['price'],'%') !== false){
    					$image_price = cfom_get_amount_after_percentage($cart_total, $image['price']);
    				}
		            // Actually image URL is link
					$image_link = isset($image['url']) ? $image['url'] : '';
					$image_url  = wp_get_attachment_thumb_url( $image_id );
					$image_title_price = $image_title . ' ' . ($image_price > 0 ? '(+'.wc_price($image_price).')' : '');
					
					$checked_option = '';
					
					
					if( ! empty($default_value) ){
					    
					    if( is_array($default_value) ) {
					        
					        foreach($default_value as $img_data) {
					            
					            if( $image['id'] == $img_data['id'] ) {
					                $checked_option = 'checked="checked"';
					            }
					        }
					    } else {
					        
					        $checked_option = ($image['title'] == $default_value ? 'checked=checked' : '' );
		                  
					    }
					    
		            }
					
					$html .= '<label>';
					$html .= '<div class="pre_upload_image '.esc_attr($classes).'" ';
					$html .= 'title="'.esc_attr($image_title_price).'" data-cfom-tooltip="cfom_tooltip">';
						if ($args['multiple_allowed'] == 'on') {
							$html	.= '<input type="checkbox" ';
							$html   .= 'id="'.esc_attr($option_id).'"';
							$html   .= 'data-price="'.esc_attr($image_price).'" ';
							$html   .= 'data-label="'.esc_attr($image_title).'" ';
							$html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
							$html   .= 'name="'.$args['name'].'[]" ';
							$html   .= 'value="'.esc_attr(json_encode($image)).'" '.esc_attr($checked_option).' />';
						}else{
							
							//default selected
				// 			$checked = ($image['title'] == $default_value ? 'checked = "checked"' : '' );
							$html	.= '<input type="radio" ';
							$html   .= 'id="'.esc_attr($option_id).'"';
							$html   .= 'data-price="'.esc_attr($image_price).'"';
							$html   .= 'data-label="'.esc_attr($image_title).'" ';
							$html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
							$html   .= 'data-type="'.esc_attr($type).'" name="'.$args['name'].'[]" ';
							$html   .= 'value="'.esc_attr(json_encode($image)).'" '.esc_attr($checked_option).' />';
						}
					if($image['id'] != ''){
						if( isset($image['url']) && $image['url'] != '' ) {
							$html .= '<a href="'.$image['url'].'"><img src="'.wp_get_attachment_thumb_url( $image['id'] ).'" /></a>';
						} else {
						    
						    $image_url = wp_get_attachment_thumb_url( $image['id'] );
							$html .= '<img data-image-tooltip="'.wp_get_attachment_url($image['id']).'" class="img-thumbnail cfom-zoom" src="'.esc_url($image_url).'" />';
						}
						
					}else{
						if( isset($image['url']) && $image['url'] != '' )
							$html .= '<a href="'.$image['url'].'"><img width="150" height="150" src="'.esc_url($image['link']).'" /></a>';
						else {
							$html .= '<img class="img-thumbnail cfom-zoom" data-image-tooltip="'.esc_url($image['link']).'" src="'.esc_url($image['link']).'" />';
						}
					}
					
					$html .= '</div></label>';
						
					$img_index++;
				}
			}
			
			$html .= '<div style="clear:both"></div>';	
				
			$html .= '</div>';		//cfom_upload_image_box
        }
        $html   .= '</div>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $default_value);
    }
    
    
    // HTML or Text (section)
    public function Section( $args, $default_value = '' ) {
         
        $type       = $this -> get_attribute_value( 'type', $args);
        $id         = $this -> get_attribute_value( 'id', $args);
        $label      = $this -> get_attribute_value('label', $args);
        $field_html = $this -> get_attribute_value( 'html', $args);
        
        // var_dump($field_html);
        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
       
        if( $label ) {
            
            $field_html = $field_html . $label;
        }
        
        $html   .= stripslashes( $field_html );
        
        $html .= '<div style="clear: both"></div>';
        
        $html   .= '</div>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $default_value);
    }
    
    // Audio/video
    public function Audio_video( $args, $default_value = '' ) {
         
        $type       = $this -> get_attribute_value( 'type', $args);
        $id         = $this -> get_attribute_value( 'id', $args);
        $name       = $this -> get_attribute_value('name', $args);
        $label      = $this -> get_attribute_value( 'label', $args);
        $classes    = isset($args['classes']) ? $args['classes'] : '';
	    
	    // Only title withou description for price calculation etc.
        $title      = $args['title'];
        
	    // Options
		$audios    = isset($args['audios']) ? $args['audios'] : '';
		if ( ! $audios ) return __("audios not selected", 'cfom');

        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        // cfom_pa($audios);
        $html .= '<div class="cfom_audio_box">';
		foreach ($audios as $audio){
					
			
			$audio_link = isset($audio['link']) ? $audio['link'] : 0;
			$audio_id   = isset($audio['id']) ? $audio['id'] : 0;
			$audio_title= isset($audio['title']) ? stripslashes($audio['title']) : 0;
			$audio_price= isset($audio['price']) ? $audio['price'] : 0;

            // Actually image URL is link
			$audio_url  = wp_get_attachment_url( $audio_id );
			$audio_title_price = $audio_title . ' ' . ($audio_price > 0 ? wc_price($audio_price) : '');
			
			$checked_option = '';
			if( ! empty($default_value) ){
        
                $checked = ($audio['title'] == $default_value ? 'checked = "checked"' : '' );
                $checked_option = checked( $default_value, $key, false );
            }
			
			$html .= '<div class="cfom_audio">';
			
			if( !empty($audio_url) ) {
			    $html .= apply_filters( 'the_content', $audio_url );
			}
			
			?>
			
			<?php
				
			$html	.= '<div class="input_image">';
			if ($args['multiple_allowed'] == 'on') {
				$html	.= '<input type="checkbox" ';
				$html   .= 'data-price="'.esc_attr($audio_price).'" ';
				$html   .= 'data-label="'.esc_attr($audio_title).'" ';
				$html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
				$html   .= 'name="'.$args['name'].'[]" ';
				$html   .= 'value="'.esc_attr(json_encode($audio)).'" />';
			}else{
				
				$html	.= '<input type="radio" ';
				$html   .= 'data-price="'.esc_attr($audio_price).'"';
				$html   .= 'data-label="'.esc_attr($audio_title).'" ';
				$html   .= 'data-title="'.esc_attr($title).'"'; // Input main label/title
				$html   .= 'data-type="'.esc_attr($type).'" name="'.$args['name'].'[]" ';
				$html   .= 'value="'.esc_attr(json_encode($audio)).'" '.$checked_option.' />';
			}
				
		    $html	.= '<div class="p_u_i_name">'.$audio_title_price.'</div>';
			$html	.= '</div>';	//input_image
				
				
			$html .= '</div>';  // pre_upload_image
		}
		
		$html .= '</div>'; //.cfom_upload_image_box
        $html   .= '</div>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $default_value);
    }
    
    // File Upload
    public function File( $args, $default_files = '' ) {
         
        $type       = $this -> get_attribute_value( 'type', $args);
        $id         = $this -> get_attribute_value( 'id', $args);
        $label      = $this -> get_attribute_value( 'label', $args);
        
       
        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        
        $html       = '<div id="cfom-file-container-'.esc_attr($args['id']).'" class="'.$input_wrapper_class.'">';
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        

        $container_height = isset($args['dragdrop']) ? 'auto' : '30px' ;
        $html .= '<div class="cfom-file-container text-center" ';
        $html .= 'style="height: '.esc_attr($container_height).' ;">';
			$html .= '<a id="selectfiles-'.esc_attr($args['id']).'" ';
			$html .= 'href="javascript:;" ';
			$html .= 'class="btn btn-primary '.esc_attr($args['button_class']).'">';
			$html .= $args['button_label'] . '</a>';
			$html .= '<span class="cfom-dragdrop-text">'.__('Drag file(s) here', 'cfom').'</span>';
		$html .= '</div>';		//cfom-file-container

		if($args['dragdrop']){
			
			$html .= '<div class="cfom-droptext">';
				$html .= __('Drag file/directory here', 'cfom');
			$html .= '</div>';
		}
    	
    	$html .= '<div id="filelist-'.esc_attr($args['id']).'" class="filelist">';
    	
    	
    	// Editing existing file
    	if( !empty( $default_files ) ) {
    	    
    	   // var_dump($default_files);
    	    
    	    foreach($default_files as $key => $file ) {
    	        
    	        $file_preview = cfom_uploaded_file_preview($file['org'], $args);
    	        if( !isset($file['org']) || $file_preview == '') continue;
    	        
    	        $html .= '<div class="u_i_c_box" id="u_i_c_'.esc_attr($key).'" data-fileid="'.esc_attr($key).'">';
    	        
    	        $html .= $file_preview;
    	        
    	        if( $html != '' ) 
    	        
    	        $file_name = $file['org'];
    	        $data_name = 'cfom[fields]['.$args['id'].']['.$key.'][org]';
    	        $file_class = 'cfom-file-cb cfom-file-cb-'.$args['id'];
    	        
    	        // Adding CB for data handling
    	        $html .= '<input checked="checked" name="'.esc_attr($data_name).'" ';
    	        $html .= 'data-price="'.esc_attr($args['file_cost']).'" ';
    	        $html .= 'data-label="'.esc_attr($file_name).'" ';
    	        $html .= 'data-title="'.esc_attr($label).'" ';
    	        $html .= 'value="'.esc_attr($file_name).'" ';
    	        $html .= 'class="'.esc_attr($file_class).'" ';
    	        $html .= 'type="checkbox"/>';
    	        
    	        $html .= '</div>'; //u_i_c_box
    	        
    	    }
    	}
    	
    	$html .= '</div>';  // filelist
        
        $html   .= '</div>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $default_files);
    }
    
    // Cropper
    public function Cropper( $args, $selected_value = '' ) {
         
        $type       = $this -> get_attribute_value( 'type', $args);
        $id         = $this -> get_attribute_value( 'id', $args);
        $label      = $this -> get_attribute_value( 'label', $args);
        $title      = $this -> get_attribute_value( 'title', $args);
        
        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        
        $html       = '<div id="cfom-file-container-'.esc_attr($args['id']).'" class="'.$input_wrapper_class.'">';
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        $container_height = isset($args['dragdrop']) ? 'auto' : '30px' ;
        $html .= '<div class="cfom-file-container text-center" ';
        $html .= 'style="height: '.esc_attr($container_height).' ;">';
			$html .= '<a id="selectfiles-'.esc_attr($args['id']).'" ';
			$html .= 'href="javascript:;" ';
			$html .= 'class="btn btn-primary '.esc_attr($args['button_class']).'">';
			$html .= $args['button_label'] . '</a>';
			$html .= '<span class="cfom-dragdrop-text">'.__('Drag file/directory here', 'cfom').'</span>';
		$html .= '</div>';		//cfom-file-container

		if($args['dragdrop']){
			
			$html .= '<div class="cfom-droptext">';
				$html .= __('Drag file/directory here', 'cfom');
			$html .= '</div>';
		}
    	
    	$html .= '<div id="filelist-'.esc_attr($args['id']).'" class="filelist"></div>';
    	
    	$html   .= '<div class="cfom-croppie-wrapper-'.esc_attr($args['id']).' text-center">';
    	$html   .= '<div class="cfom-croppie-preview">';
    	
        // 	cfom_pa($args['options']);
    	// @since: 12.8
    	// Showing size option if more than one found.
    	if (isset($args['options']) && count($args['options']) > 1){
    	    
    	   $cropping_sizes = $args['options'];
    	    
    	   $select_css = 'width:'.$args['croppie_options']['boundary']['width'].'px;';
    	   $select_css .= 'margin:5px auto;display:none;';
    	    
    	    $html   .= '<select style="'.esc_attr($select_css).'" class="cfom-cropping-size form-control" data-field_name="'.esc_attr($args['id']).'" id="crop-size-'.esc_attr($args['id']).'">';
    	        foreach($cropping_sizes as $key => $size) {
    	            
    	            $option_label   = $size['label'];
                    $option_price   = $size['price'];
                    $raw_label      = $size['raw'];
                    $without_tax    = $size['without_tax'];
    	            
    	            $html   .= '<option '.selected( $selected_value, $key, false ).' ';
                    $html   .= 'value="'.esc_attr($key).'" ';
                    $html   .= 'data-price="'.esc_attr($option_price).'" ';
                    $html   .= 'data-label="'.esc_attr($raw_label).'" ';
                    $html   .= 'data-title="'.esc_attr($title).'" '; // Input main label/title
                    // $html   .= 'data-onetime="'.esc_attr($onetime).'" ';
                    // $html   .= 'data-taxable="'.esc_attr($taxable).'" ';
                    $html   .= 'data-without_tax="'.esc_attr($without_tax).'" ';
                    $html   .= 'data-width="'.esc_attr($size['width']).'" data-height="'.esc_attr($size['height']).'" ';
                    $html   .= '>'.$option_label.'</option>';
    	        }
    	        
    	        $html .= '<option selected="selected">'.__('Select Size', 'cfom').'</option>';
    	   $html    .= '</select>';
    	   
    	}
    	
    	$html   .= '</div>';    // cfom-croppie-preview
    	
    	$html   .= '<a href="#" style="display:none" data-fileid="'.esc_attr($args['id']).'" class="btn btn-info cfom-croppie-btn">';
    	$html   .= __('Confirm and Preview', 'cfom').'</a>';
    	$html   .= '</div>'; //cfom-croppie-wrapper
    	// Loading Modals
		$modal_vars = array('file_id' => $args['id'], 'image_full'=>'', 'image_title'=>$args['label']);
		ob_start();
        cfom_load_template('v10/cropper-modals.php', $modal_vars);
        $html .= ob_get_clean();
        
        $html   .= '</div>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $selected_value);
    }
    
    // A custom input will be just some option html
    public function Custom( $args, $default_value = '' ) {
         
        $type       = $this -> get_attribute_value( 'type', $args);
        $id         = $this -> get_attribute_value( 'id', $args);
        $label      = $this -> get_attribute_value( 'label', $args);
        
        $input_wrapper_class = $this->get_default_setting_value('global', 'input_wrapper_class', $id);
        $input_wrapper_class = apply_filters('cfom_input_wrapper_class', $input_wrapper_class, $args);
        $html       = '<div class="'.$input_wrapper_class.'">';
        if( $label ){
            $html   .= '<label class="'.$this->get_default_setting_value('global', 'label_class', $id).'" for="'.$id.'">';
            $html   .= sprintf(__("%s", "cfom"), $label) .'</label>';
        }
        
        $html   .= apply_filters('nmform_custom_input', $html, $args, $default_value);
        
        $html   .= '</div>';    //form-group
        
        // filter: nmforms_input_htmls
        return apply_filters("nmforms_input_html", $html, $args, $default_value);
    }
    
    
    /**
     * this function return current or/else default attribute values
     * 
     * filter: nmform_attribute_value
     * 
     * */
    private function get_attribute_value( $attr, $args ) {
        
        $attr_value = '';
        $type       = isset($args['type']) ? $args['type'] : $this->get_default_setting_value('global', 'type');
        
        // if( $attr == 'type' ) return $type;
        
        if( isset($args[$attr]) ){
            
            $attr_value = $args[$attr];
        } else {
            
            $attr_value = $this->get_default_setting_value( $type, $attr );
        }
        
        return apply_filters('nmform_attribute_value', $attr_value, $attr, $args);
    }
    
    
    /**
     * this function return default value
     * defined in class/config
     * 
     * @params: $setting_type
     * @params: $key
     * filter: default_setting_value
     * */
    function get_default_setting_value( $setting_type, $key, $field_id = '' ){
        
        $defaults = $this -> get_property( 'defaults' );
        
        $default_value = isset( $defaults[$setting_type][$key] ) ? $defaults[$setting_type][$key] : '';
        
        return apply_filters('default_setting_value', $default_value, $setting_type, $key, $field_id);
    }
    
    
    /**
     * function return class property values/settings
     * 
     * filter: nmform_property-{$property}
     * */
    private function get_property( $property ) {
        
        $value = '';
        switch( $property ) {
            
            case 'echoable':
                    $value = CFOM_ECHOABLE;
            break;
            
            case 'defaults':
                
                    $value =  array(
                                    'global'   => array('type' => 'text',
                                                        'input_wrapper_class'=>'form-group',
                                                        'label_class'   => 'form-control-label',),
                                    'text'      => array('placeholder' => "", 'attributes' => array()),
                                    'date'      => array(),
                                    'email'     => array(),
                                    'number'    => array(),
                                    'textarea'  => array('cols' => 6, 'rows' => 3),
                                    'select'    => array('multiple' => false),
                                    'checkbox'  => array('label_class' => 'form-control-label',
                                                        'check_wrapper_class' => 'form-check',
                                                        'check_label_class' => 'form-check-label',
                                                        'classes' => array('cfom-check-input')),
                                    'radio'     => array('label_class' => 'form-control-label',
                                                        'radio_wrapper_class' => 'form-check',
                                                        'radio_label_class' => 'form-check-label',
                                                        'classes' => array('cfom-check-input')),
                    );
            break;
        }
        
        return apply_filters("nmform_property-{$property}", $value);
        
    }
    
    
    /**
     * ====================== FILTERS =====================================
     * 
     * */
     
    public function adjust_attributes_values( $attr_value, $attr, $args ) {
        
        switch( $attr ) {
            
            // converting classes to string
            case 'classes':
                $attr_value = implode(" ", $attr_value);
            break;
            
            /**
             * converting name to array for multiple:select
             * */
            case 'name':
                
                $type       = $this -> get_attribute_value( 'type', $args);
                $multiple   = $this -> get_attribute_value('multiple', $args);
                if( $type == 'select' && $multiple ){
                    
                    $attr_value .= '[]';
                }
            break;
        }
        
        return $attr_value;
    }
    
    /**
     * ====================== ENDs FILTERS =====================================
     * 
     * */
    
    public static function get_instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
}

function CFOM_Form(){
	return CFOM_Form::get_instance();
}