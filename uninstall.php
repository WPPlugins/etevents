<?php 
/**************************************************************/
/* Uninstall Procedures                                       */
/**************************************************************/


/**************************************************************/
/* Check to make sure uninstalled was from WP                 */
/* If not - exit                                              */
/**************************************************************/
if( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
exit (); 


/**************************************************************/
/* Delete Options from database                               */
/**************************************************************/
delete_option( 'etevents_options' ); 

// remove any additional options and custom tables 
 global $wpdb;
  $thetable = $wpdb->prefix."lj_etevents";
  //Delete any options that's stored also?
  //delete_option('wp_yourplugin_version'); -> investigate
  $wpdb->query("DROP TABLE IF EXISTS $thetable");

?>