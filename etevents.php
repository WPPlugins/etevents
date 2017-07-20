<?php
/*
Plugin Name: ETEvents
Plugin URI: http://wordpress.org/plugins/etevents/
Description: This plugin works was developed to work with the Event Theme created by Elegant Themes.
Version: 1.1
Author: Leonard Johnson
Author URI: http://www.bonesnap.com/blog/wordpress-plugins
License: GPLv2
*/

/*  Copyright 2013  Leonard Johnson  (email : webmaster@bonesnap.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*********************************************************************************************************/
/*                                           Hooks                                                      */
/*********************************************************************************************************/



/**************************************************************/
/* Name: lj_etevents_install                                  */
/* Summary: Called by activation hook. Used to comapre wp     */
/* version.                                                   */
/**************************************************************/
function lj_etevents_install()
{
    if ( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) )
	{
        deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
    }
	else
	{
		/**************************************************************/
		/* Create Database Table                                       */
		/**************************************************************/
	   lj_etevents_createDBTable();

	}
}



/**************************************************************/
/* Name: lj_etevents_createDBTable                            */
/* Summary: Called by install function. Creates db table      */
/**************************************************************/
function lj_etevents_createDBTable()
{
   global $wpdb;
   $table_name = $wpdb->prefix . "lj_etevents";
	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  user_id mediumint(9) NOT NULL,
	  event_id mediumint(9) NOT NULL,
      status int NOT NULL,
      dateaction datetime DEFAULT 0 NOT NULL,
	  UNIQUE KEY id (id)
	);";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
 }


/**************************************************************/
/* Name: lj_etevents_uninstall                                */
/* Summary: Called by deactivation hook.                      */
/**************************************************************/
function lj_etevents_uninstall()
{
  deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
}

/**************************************************************/
/* Name: lj_etevents_create_menu                              */
/* Sumamry: called by an Add_Actions - creates top level menu */
/**************************************************************/
function lj_etevents_create_menu()
 {
  add_menu_page( 'ETEvents', 'ETEvents', 'manage_options', __FILE__, '', plugins_url( '/images/etevents.png', __FILE__ ) );


	/**************************************************************/
	/* Creates sub menu                                           */
	/**************************************************************/
	add_submenu_page( __FILE__, 'Bookings', 'Bookings', 'manage_options',__FILE__, 'lj_etevents_bookings_page' );
	add_submenu_page( __FILE__, 'About', 'About & Help', 'manage_options',__FILE__.'about', 'lj_etevents_about_page' );

 }

/**************************************************************/
/* Name: lj_etevents_settings_menu                            */
/* Summary: lj_etevents_settings_menu                        */
/**************************************************************/

if (!function_exists('lj_etevents_settings_menu'))
{
		// Add a menu for our option page
		function lj_etevents_settings_menu()
		{
			$css_path = WP_PLUGIN_URL . '/etevents/css/etevents_admin.css';

			// registers your stylesheet
			wp_register_style( 'myStyleSheets', $css_path );

			// loads your stylesheet
			wp_enqueue_style( 'myStyleSheets' );

			include 'etevents_settings.php';
			add_options_page( 'ETEvents', 'ETEvents', 'manage_options', 'lj_etevents', 'lj_etevents_option_page' );
		}
}

/**************************************************************/
/* Name: lj_etevents_about_page                            */
/* Summary: Actions for About Page                         */
/**************************************************************/

if (!function_exists('lj_etevents_about_page'))
{
	 function lj_etevents_about_page()
	 {
		$css_path = WP_PLUGIN_URL . '/etevents/css/etevents_admin.css';

		// registers your stylesheet
		wp_register_style( 'myStyleSheets', $css_path );

		// loads your stylesheet
		wp_enqueue_style( 'myStyleSheets' );
		include 'etevents_about.php';
	 }
}

/**************************************************************/
/* Name: lj_etevents_bookings_page                            */
/* Summary: Actions for Bookings Page                         */
/**************************************************************/
if (!function_exists('lj_etevents_bookings_page'))
{
	 function lj_etevents_bookings_page()
	 {
		$css_path = WP_PLUGIN_URL . '/etevents/css/etevents_admin.css';
		// registers your stylesheet
		wp_register_style( 'myStyleSheets', $css_path );
		// loads your stylesheet
		wp_enqueue_style( 'myStyleSheets' );
		include 'etevents_bookings.php';
	 }
}

/**************************************************************/
/* Name: lj_etevents_create_metabox                           */
/* Summary: metabox create function                           */
/**************************************************************/
function lj_etevents_create_metabox()
{
	//create a custom meta box
	add_meta_box( 'lj_etevents_meta', 'ETEvents Settings', 'lj_etevents_metabox_function', 'post', 'normal', 'high' );
}

/**************************************************************/
/* Name: lj_etevents_metabox_function                           */
/* Summary: metabox  function                           */
/**************************************************************/
function lj_etevents_metabox_function( $post )
{
	//retrieve the meta data values if they exist
	$lj_etevents_max_booking = get_post_meta( $post->ID, '_lj_etevents_max_booking', true );
	$lj_etevents_reserve_booking = get_post_meta( $post->ID, '_lj_etevents_reserve_booking', true );

	?>
	<p>Max number of bookings: <input type="text" name="lj_etevents_max_booking" value="<?php echo esc_attr( $lj_etevents_max_booking ); ?>" /></p>
	<p>Allowed nnumber of reserve bookings: <input type="text" name="lj_etevents_reserve_booking" value="<?php echo esc_attr( $lj_etevents_reserve_booking ); ?>" /></p>
	<?php
}

/**************************************************************/
/* Name: lj_etevents_save_meta                                */
/* Summary: metabox  save                                     */
/**************************************************************/
function lj_etevents_save_meta( $post_id )
{
	//verify the meta data is set
	if ( isset( $_POST['lj_etevents_max_booking'] ) )
	 {
		//save the meta data
		update_post_meta( $post_id, '_lj_etevents_max_booking', strip_tags( $_POST['lj_etevents_max_booking'] ) );
		update_post_meta( $post_id, '_lj_etevents_reserve_booking', strip_tags( $_POST['lj_etevents_reserve_booking'] ) );

	}

}

/*********************************************************/
/*                                           Add Actions                                                 */
/*********************************************************/

/**************************************************************/
/* Name: Add-Action Admin Menu                                */
/**************************************************************/
add_action( 'admin_menu', 'lj_etevents_create_menu' );

/**************************************************************/
/* Actiavtions Hook call                                     */
/**************************************************************/
register_activation_hook( __FILE__, 'lj_etevents_install' );

/**************************************************************/
/* Deactivation Hook call                                     */
/**************************************************************/
register_deactivation_hook( __FILE__, 'lj_etevents_uninstall' );

/**************************************************************/
/* Set up Options                                     */
/**************************************************************/
add_action( 'admin_menu', 'lj_etevents_settings_menu' );

/**************************************************************/
/* Set up MetaBox                                             */
/**************************************************************/
add_action( 'add_meta_boxes', 'lj_etevents_create_metabox' );

/**************************************************************/
/* hook to save the meta box data                             */
/**************************************************************/
add_action( 'save_post', 'lj_etevents_save_meta' );





/**************************************************************/
/* End Plugin                                                 */
/**************************************************************/

?>