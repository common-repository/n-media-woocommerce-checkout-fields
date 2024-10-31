<?php
/*
 * Followig class handling all inputs control and their 
 * dependencies. Do not make changes in code
 * Create on: 9 November, 2013 
 */

class CFOM_Inputs{

	/*
	 * this var is pouplated with current plugin meta 
	 */
	var $plugin_meta;
	
	
	/*
	 * this var contains the scripts info 
	 * requested by input
	 */
	var $input_scripts = array();
	
	/**
	 * the static object instace
	 */
	private static $ins = null;
	
	
	/**
	 * __construct function.
	 *
	 * @access public
	 * @param 
	 */
	public function __construct() {
		
		$this -> plugin_meta = cfom_get_plugin_meta();
	}
	
	public static function get_instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
	
	/*
	 * returning relevant input object
	 */
	function get_input($type){
		
		$class_name 	= 'NM_' . ucfirst($type) . '_cfom';
		$file_name		= 'input.' . $type . '.php';
				
		if (! class_exists ( $class_name )) {
			
			/**
			 * adding filter: nm_input_class-filename
			 * @since 6.7
			 * @since 7.6: changing path for eventcalendar addon
			 **/
			 
			 $_inputs = '';
			 switch( $type ) {
			 	
			 	case 'eventcalendar':
			 		$_inputs = $this->plugin_meta['cfom_eventcalendar'];
			 	break;
			 	
			 	default:
			 		$_inputs = apply_filters('nm_input_class-'.$type, CFOM_PATH . "/classes/inputs/{$file_name}", $type); 	
			 	break;
			 }
			 
			 
			 
			if (file_exists ( $_inputs )){
				
				include_once ($_inputs);
				if (class_exists ( $class_name ))
					return new $class_name();
				else
					return null;
				
			}else{
				die ( 'Reen, Reen, BUMPs! not found ' . $_inputs );
			}
		} else {
			return new $class_name();
		}
	}
	
	/*
	 * check if browser is ie
	 */
	function if_browser_is_ie()
	{
		//print_r($_SERVER['HTTP_USER_AGENT']);
		
		if(!(isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))){
			return false;
		}else{
			return true;
		}
	}
	
	/*
	 * get current page url with query string
	 */
	function current_page_url() {
		$page_url = 'http';
		if( isset($_SERVER["HTTPS"]) ) {
			if ($_SERVER["HTTPS"] == "on") {$page_url .= "s";}
		}
		$page_url .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $page_url;
	}
}

function cfom_Inputs(){
	return cfom_Inputs::get_instance();
}