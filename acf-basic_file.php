<?php
/*
Plugin Name: Advanced Custom Fields: Basic File
Plugin URI: {{git_url}}
Description: This plugin will add a basic file input field to the Advanced Custom Fields plugins
Version: 1.0.0
Author: Elliot Condon
Author URI: http://elliotcondon.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


class acf_field_basic_file_plugin
{
	/*
	*  Construct
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 1/04/13
	*/
	
	function __construct()
	{
		// set text domain
		/*
		$domain = 'acf-basic_file';
		$mofile = trailingslashit(dirname(__File__)) . 'lang/' . $domain . '-' . get_locale() . '.mo';
		load_textdomain( $domain, $mofile );
		*/
		
		
		// version 4+
		add_action('acf/register_fields', array($this, 'register_fields'));	

		
		// version 3-
		/* add_action( 'init', array( $this, 'init' ), 5); */
	}
	
	
	/*
	*  Init
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 1/04/13
	*/
	
	function init()
	{
		if(function_exists('register_field'))
		{ 
			register_field('acf_field_basic_file', dirname(__File__) . '/basic_file-v3.php');
		}
	}
	
	/*
	*  register_fields
	*
	*  @description: 
	*  @since: 3.6
	*  @created: 1/04/13
	*/
	
	function register_fields()
	{
		include_once('basic_file-v4.php');
	}
	
}

new acf_field_basic_file_plugin();
		
?>
