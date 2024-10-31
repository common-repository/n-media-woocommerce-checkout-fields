<?php
/**
 * CFOM Order Manager
 * 
**/


/* 
**========== Direct access not allowed =========== 
*/
if( ! defined('ABSPATH') ) die('Not Allowed');

/* 
**========== Include PDF library =========== 
*/

class CFOM_Order {
    
    // class properties
    protected static $wc_order;
    protected static $mpdf;
    
    function __construct( $order_id ) {
        
        
        // mpdf load
        if( cfom_pro_is_installed() ) {
            include_once CFOM_PRO_PATH . "/lib/php-pdf/vendor/autoload.php";
            $pdf_dir_path = cfom_get_dir_path('pdf_dir');
            self::$mpdf = new \Mpdf\Mpdf([
                'tempDir' => $pdf_dir_path,
                'margin_left' => 1,
                'margin_right' => 1,
                'setAutoTopMargin'=> 'stretch',
                'autoMarginPadding' => 0,
                'setAutoBottomMargin'=> 'stretch',
                'margin_bottom' => 0,
                'margin_header' => 0.4,
                'margin_footer' => 0.4,
            ]);
        }

        // add_action('user_register' , array($this, 'wpr_set_user_password'));
        self::$wc_order = wc_get_order( $order_id );
        
        if( !self::$wc_order ) return null;
        
        if( ! $this->get_all_meta() ) return null;
    }
    
    /* 
    **========== Get all fields meta =========== 
    */
    function get_all_meta() {
        
        $cfom_all_meta = get_post_meta($this->id(), 'cfom_all_fields_readable', true);
        return apply_filters('cfom_all_fields_readable', $cfom_all_meta, $this);
    }
    
    /* 
    **========== Order Id =========== 
    */
    function id() {
        return self::$wc_order->get_id();
    }
    
    
    /* 
    **========== Render fields order meta =========== 
    */
    function display_order_meta($context){
       
        $order_meta = $this->get_all_meta();
        
        /*if ($context == 'email') {
            unset($order_meta['billing']);
            unset($order_meta['shipping']);
        }*/
        
        if( apply_filters('cfom_show_only_core_extra_billing_shipping_fields_pdf', false, $context) ) {
            $order_meta['billing']  = $this->get_billing_extra_fields();
            $order_meta['shipping'] = $this->get_shipping_extra_fields();
        }
        
        // cfom_pa($order_meta);
        ob_start();
        $templates_name      = '/admin/fields-order.php';
        $template_vars       = array( 'order_meta' => $order_meta , 'order_id'=> $this->id(), 'context'=> $context );  
        $cfom_order_template = apply_filters('cfom_order_meta_list', $templates_name , $template_vars);
        cfom_load_template( $templates_name, $template_vars );
        $html = ob_get_clean();

        return $html;
    }
    
    // Only getting extra billing fields
    function get_billing_extra_fields() {
        
        $section    = 'billing';
        $order_meta = $this->get_all_meta();
        $billing_fields = isset($order_meta[$section]) ? $order_meta[$section] : null;
        
        if( ! $billing_fields ) return null;
        
        $extra_fields = array();
        foreach($billing_fields as $field_name => $detail) {
    		
    		// skip core fields display
    		if( cfom_is_checkout_core_field($field_name, $section) ) continue;
    		$extra_fields[] = $billing_fields[$field_name];
    	}
    	
    	return (! empty($extra_fields)) ? $extra_fields : null;
    }
    
    // Only getting extra shipping fields
    function get_shipping_extra_fields() {
        
        $section    = 'shipping';
        $order_meta = $this->get_all_meta();
        $shipping_fields = isset($order_meta[$section]) ? $order_meta[$section] : null;
        
        if( ! $shipping_fields ) return null;
        
        $extra_fields = array();
        foreach($shipping_fields as $field_name => $detail) {
    		
    		// skip core fields display
    		if( cfom_is_checkout_core_field($field_name, $section) ) continue;
    		$extra_fields[] = $shipping_fields[$field_name];
    	}
    	
    	return (! empty($extra_fields)) ? $extra_fields : null;
    }

    /* 
    **========== Print odf of order meta =========== 
    */
    function print_order_meta($section){

        $order_meta = $this->get_all_meta();
        if ($section != 'all') {
            $print_section = array($section => $order_meta[$section]);
        }else{
            $print_section = $order_meta;
        }

        ob_start();
        $templates_name      = '/admin/fields-order.php';
        $template_vars       = array( 'order_meta' => $print_section,'order_id'=> $this->id(), 'context'=> 'create_pdf' );  
        cfom_load_template( $templates_name, $template_vars );
        $html = ob_get_clean();
        
        $stylesheet = file_get_contents(CFOM_URL."/css/cfom-order.css");

        // self::$mpdf->SetDefaultBodyCSS('color', '#880000');

        self::$mpdf->SetHTMLHeader( $this->pdf_header() );
        self::$mpdf->SetHTMLFooter( $this->pdf_footer() );

        self::$mpdf->WriteHTML($stylesheet, 1);
        self::$mpdf->WriteHTML($html, 2);
        self::$mpdf->Output();
    }


    /* 
    **========== PDF Header function =========== 
    */
    function pdf_header(){

        $html = '';

        if ( get_option('cfom_pdf_header_meta') && get_option('cfom_pdf_header_meta') != '') {
            $html .= get_option('cfom_pdf_header_meta');
        }else{
            $html .= '<div style="text-align: center; background-color: #b4c1ff85;" class="cfom-pdf-header">';
            $html .= '<h1>'. get_bloginfo( 'name' ) .'</h1>';
            $html .= '</div>';
        }

        return $html;
    }


    /* 
    **========== PDF Footer function =========== 
    */
    function pdf_footer(){
        
        $html  = '';

        if ( get_option('cfom_pdf_footer_meta') && get_option('cfom_pdf_footer_meta') != '') {
            $html .= get_option('cfom_pdf_footer_meta');
        }else{
            $html  = '<div style="text-align: right; background-color: #b4c1ff85;">';
            $html .= '<h2>Email:'. get_bloginfo( 'admin_email' ) .'</h2>';
            $html .= '</div>';
        }

        return $html;
    }
    
}