<?php 

    /*
    Plugin Name: Facebook Pixel Pal
    Plugin URI: https://masterwoos.com
    Description: This plugin allows the user to easily add their Facebook Pixel Code to their WordPress website. Thnx CK :-)
    Author: Master Woo's
    Version: 1.01
    Author URI: https://masterwoos.com
	License: GPLv2 or later
	Requires at least: 3.4.1
	Tested up to: 4.7.2
	Text Domain: masterwoos-facebook-pixel-code-pal
	Domain Path: languages
    */   
	
	
	/*

    Copyright (C) 2017  Master Woo's  info@masterwoos.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

    class masterwoos_fbpp
    {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
    	add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
    	add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
    	add_options_page(
    		'Facebook Pixel Pal', 
    		'Facebook Pixel Pal', 
    		1, 
    		'Facebook Pixel Pal', 
    		array( $this, 'masterwoos_fbpp_admin' )
    		);
    }

    /**
     * Options page callback
     */
    public function masterwoos_fbpp_admin()
    {
        // Set class property
    	$this->options = get_option( 'masterwoos_fbpp_options' );
    	?>
    	<div class="wrap">

    		<div style="width: 100%">
    			<div style="width: 100%;float: left;">
    				<form method="post" action="options.php">
    					<?php
                // This prints out all hidden setting fields
    					settings_fields( 'masterwoos_fbpp_option_group' );
    					do_settings_sections( 'masterwoos_fbpp_settings_admin' );
    					submit_button();
    					?>
    				</form>
    			</div>

    			
    			</div>
    		</div>


    		<?php
    	}

    /**
     * Register and add settings
     */
    public function page_init()
    {        
    	register_setting(
            'masterwoos_fbpp_option_group', // Option group
            'masterwoos_fbpp_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
            );

    	add_settings_section(
            'masterwoos_fbpp_code', // ID
            '<h1>Facebook Pixel Pal</h1>', // Title
            array( $this, 'print_section_info' ), // Callback
            'masterwoos_fbpp_settings_admin' // Page
            );  

    	add_settings_field(
            'facebook_pixel_code', // ID
            'Facebook Pixel Code', // Title 
            array( $this, 'facebook_pixel_code_callback' ), // Callback
            'masterwoos_fbpp_settings_admin', // Page
            'masterwoos_fbpp_code' // Section           
            );      

   

    	foreach (array('post','page') as $type) 
    	{
    		add_meta_box('masterwoos_fbpp_all_post_meta', 'Insert Script to &lt;head&gt;', array( $this,'masterwoos_fbpp_meta_setup'), $type, 'normal', 'high');
    	}

    	add_action('save_post',array( $this,'masterwoos_fbpp_post_meta_save'));     
    }


    public function masterwoos_fbpp_meta_setup()
    {
    	global $post;

		// using an underscore, prevents the meta variable
		// from showing up in the custom fields section
    	$meta = get_post_meta($post->ID,'post_header_script',TRUE);
    	
    	?>

    	

    	<div>

    		<p>
    			<textarea name="post_header_script" rows="5" style="width:98%;"><?php if(!empty($meta)) echo htmlspecialchars_decode($meta); ?></textarea>
    		</p>

    		<p>Add some code to <code>&lt;head&gt;</code>.</p>
    	</div>

    	<?php

		// create a custom nonce for submit verification later
    	echo '<input type="hidden" name="masterwoos_fbpp_post_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />';
    }

    public function masterwoos_fbpp_post_meta_save($post_id) 
    {
		// authentication checks

		// make sure data came from our meta box
    	if ( ! isset( $_POST['masterwoos_fbpp_post_meta_noncename'] )
    		|| !wp_verify_nonce($_POST['masterwoos_fbpp_post_meta_noncename'],__FILE__)) return $post_id;

			// check user permissions
    		if ($_POST['post_type'] == 'page') 
    		{
    			if (!current_user_can('edit_page', $post_id)) return $post_id;
    		}
    		else 
    		{
    			if (!current_user_can('edit_post', $post_id)) return $post_id;
    		}


    		$current_data = get_post_meta($post_id, 'post_header_script', TRUE);	

    		$new_data = esc_textarea(($_POST['post_header_script']));

    		if ($current_data) 
    		{
    			if (is_null($new_data)){
    				
    				delete_post_meta($post_id,'post_header_script'); 
    				
    			}
    			else { 
    				update_post_meta($post_id,'post_header_script',$new_data);
    			}
    		}
    		elseif (!is_null($new_data))
    		{
    			
    			if ( ! add_post_meta($post_id,'post_header_script',$new_data,TRUE) ) { 
    				update_post_meta($post_id,'post_header_script',$new_data);
    			}
    			
    		}

    		return $post_id;
    	}

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
    	$new_input = array();
    	if( isset( $input['facebook_pixel_code'] ) )
    		$new_input['facebook_pixel_code'] = wp_json_encode( $input['facebook_pixel_code'] );


    	return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
    	print 'Enter Your Facebook Pixel Code:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function facebook_pixel_code_callback()
    {
    	printf(
    		'<textarea id="facebook_pixel_code" style="width:500px;height:500px" name="masterwoos_fbpp_options[facebook_pixel_code]" />%s</textarea>',
    		isset( $this->options['facebook_pixel_code'] ) ? esc_attr( json_decode($this->options['facebook_pixel_code'])) : ''
    		);
    }

}

if( is_admin() )
	$masterwoos_fbpp = new masterwoos_fbpp();



// Add the Facebook Pixel Code to the header
// 



function masterwoos_fbpp_add_facebook_pixel_code() {

	$masterwoos_fbpp_options = get_option("masterwoos_fbpp_options");
	if (!empty($masterwoos_fbpp_options)) {
		echo json_decode($masterwoos_fbpp_options["facebook_pixel_code"]);
	}
	$masterwoos_fbpp_post_meta = get_post_meta( get_the_ID(), 'post_header_script' , TRUE );

	if ( $masterwoos_fbpp_post_meta != '' ) {
		echo htmlspecialchars_decode($masterwoos_fbpp_post_meta);
	}

}
add_action('wp_head', 'masterwoos_fbpp_add_facebook_pixel_code');
