<!---***********************************************************************************-->
<!-- ETEvents - Settings Pasge                                                             -->
<!-- Summary - Settings for the ETEvents Plugin                                           -->
<!-- Copywrite - Leonard H. Johnson                                                     -->
<!---***********************************************************************************-->
<!-- Date         Author               Summary                                          -->
<!---***********************************************************************************-->
<!-- 09/05/2013   Leonard H. Johnson   Initial Creation                                 -->
<!---***********************************************************************************-->

<?php

/**************************************************************/
/* Name: lj_etevents_option_page                              */
/* Summary: Draws Page                                        */   
/**************************************************************/
function lj_etevents_option_page() 
{
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>ETEvents Settings</h2>
		
	<div
		style="border: solid 1px #aaaaaa; width: 280px; background-color: #eeeeee; margin: 9px 15px 4px 0; padding: 5px; text-align: center; font-weight: bold;">
		<a href="http://www.bonesnap.com"><?php _e('Visit plugin site'); ?>
		</a> - <a href="http:///www.bonesnap.com"><?php _e('Donate'); ?>
		</a>
	</div>


  <form action="options.php" method="post">
			<?php settings_fields('lj_etevents_options'); ?>
			<?php do_settings_sections('lj_etevents'); ?>
			 <p class="submit">
				<input name="Submit" class="button-primary" type="submit" value="Save Changes" />
			 </p>
		</form>
	</div>
<?php 
}



add_action('admin_init', 'lj_etevents_admin_init');

/**************************************************************/
/* Name: lj_etevents_admin_init                              */
/* Summary:  Register and define the settings                */   
/**************************************************************/
function lj_etevents_admin_init(){
	register_setting('lj_etevents_options','lj_etevents_notification_email','lj_etevents_validate_email'); // Main Notifiaaction Email
	register_setting('lj_etevents_options','lj_etevents_notification_onbooking','lj_etevents_validate_booking_notify'); // Notify on booking
	register_setting('lj_etevents_options','lj_etevents_notification_oncancel','lj_etevents_validate_cancel_notify'); // Notify on cancel
	register_setting('lj_etevents_options','lj_etevents_allowed_bookings','lj_etevents_validate_allowed_bookings_default'); // Default Allowed bookings	
	register_setting('lj_etevents_options','lj_etevents_allowed_reservations','lj_etevents_validate_allowed_reservations_default'); // Default Allowed bookings		
		
// Main Notification Email Address
	add_settings_section(
		'lj_etevents_notifyemail_section',  // ID used to identify this section and with which to register options
		'Email',                            // Title to be displayed on the administration page  
		'lj_etevents_section_text',         // Callback used to render the description of the section 
		'lj_etevents'                       // Page on which to add this section of options  
	);

// Notification Email on Register 
	add_settings_section(
		'lj_etevents_notifications_section',  // ID used to identify this section and with which to register options
		'Notifications',                    // Title to be displayed on the administration page  
		'lj_etevents_section_text',         // Callback used to render the description of the section 
		'lj_etevents'                       // Page on which to add this section of options  
	);

// Event Defaults
	add_settings_section(
		'lj_etevents_bookings_default_section',  // ID used to identify this section and with which to register options
		'Event Defaults',                    // Title to be displayed on the administration page  
		'lj_etevents_section_text',         // Callback used to render the description of the section 
		'lj_etevents'                       // Page on which to add this section of options  
	);



//Main Notification Email Address
	add_settings_field(
		'lj_etevents_notification_email',   // ID used to identify the field throughout the theme  
		'Notification Email:',              // The label to the left of the option interface element
		'lj_etevents_setting_input',        // The name of the function responsible for rendering the option interface 
		'lj_etevents',                      // The page on which this option will be displayed  
		'lj_etevents_notifyemail_section'   // The name of the section to which this field belongs 
		                                    // The array of arguments to pass to the callback. In this case, just a description.
	);

// Notification Email on Register 
	add_settings_field(
		'lj_etevents_notification_onbooking',   // ID used to identify the field throughout the theme  
		'Notification on Booking:',              // The label to the left of the option interface element
		'lj_etevents_notification_onbooking_input',        // The name of the function responsible for rendering the option interface 
		'lj_etevents',                      // The page on which this option will be displayed  
		'lj_etevents_notifications_section'   // The name of the section to which this field belongs 
		                                    // The array of arguments to pass to the callback. In this case, just a description.
	);
// Notification Email on Cancel 
	add_settings_field(
		'lj_etevents_notification_oncancel',   // ID used to identify the field throughout the theme  
		'Notification on Cancel:',              // The label to the left of the option interface element
		'lj_etevents_notification_oncancel_input',        // The name of the function responsible for rendering the option interface 
		'lj_etevents',                      // The page on which this option will be displayed  
		'lj_etevents_notifications_section'   // The name of the section to which this field belongs 
		                                    // The array of arguments to pass to the callback. In this case, just a description.
	);

// Bookings Defaults 
	add_settings_field(
		'lj_etevents_allowed_bookings',   // ID used to identify the field throughout the theme  
		'Default Max Bookings per Event:',              // The label to the left of the option interface element
		'lj_etevents_allowed_bookings_input',        // The name of the function responsible for rendering the option interface 
		'lj_etevents',                      // The page on which this option will be displayed  
		'lj_etevents_bookings_default_section'   // The name of the section to which this field belongs 
		                                    // The array of arguments to pass to the callback. In this case, just a description.
	);

// Bookings Defaults 
	add_settings_field(
		'lj_etevents_allowed_reservations',   // ID used to identify the field throughout the theme  
		'Default Max Reservations per Event:',              // The label to the left of the option interface element
		'lj_etevents_allowed_reservations_input',        // The name of the function responsible for rendering the option interface 
		'lj_etevents',                      // The page on which this option will be displayed  
		'lj_etevents_bookings_default_section'   // The name of the section to which this field belongs 
		                                    // The array of arguments to pass to the callback. In this case, just a description.
	);

}

/**************************************************************/
/* Name: lj_etevents_section_text                             */
/* Summary:  Draws Section Header                             */   
/**************************************************************/
function lj_etevents_section_text() 
{

}

/**************************************************************/
/* Name: lj_etevents_setting_input                            */
/* Summary:  Display and fill the form field                  */   
/**************************************************************/
function lj_etevents_setting_input() {
	// get option 'text_string' value from the database
	$options = get_option( 'lj_etevents_notification_email' );
	$text_string = $options['text_string'];
	// echo the field
	echo "<input id='text_string' size =\"25\" name='lj_etevents_notification_email[text_string]' type='text' value='$text_string' />";
	echo "<p class=\"description\">The email address that will recieve the event notifications.</p>";

}

/**************************************************************/
/* Name: lj_etevents_notification_onbooking_input             */
/* Summary:  Display and fill the form field                  */   
/**************************************************************/
function lj_etevents_notification_onbooking_input() {
	// get option 'text_string' value from the database
	$options = get_option( 'lj_etevents_notification_onbooking' );
	// echo the field
	
	$html = '<input type="checkbox" id="lj_etevents_notification_onbooking_checkbox" name="lj_etevents_notification_onbooking[lj_etevents_notification_onbooking_checkbox]" value="1"' . checked( 1, $options['lj_etevents_notification_onbooking_checkbox'], false ) . '/>';
	$html.=' <p class="description">Receive emails on event booking by users.</p>';
	echo $html;
	
}

/**************************************************************/
/* Name: lj_etevents_notification_oncancel_input             */
/* Summary:  Display and fill the form field                  */   
/**************************************************************/
function lj_etevents_notification_oncancel_input() {
	// get option 'text_string' value from the database
	$options = get_option( 'lj_etevents_notification_oncancel' );
	// echo the field
	
	$html = '<input type="checkbox" id="lj_etevents_notification_oncancel_checkbox" name="lj_etevents_notification_oncancel[lj_etevents_notification_oncancel_checkbox]" value="1"' . checked( 1, $options['lj_etevents_notification_oncancel_checkbox'], false ) . '/>';
	$html.=' <p class="description">Receive emails on event cancelation by users.</p>';
	echo $html;
	
}
/**************************************************************/
/* Name: lj_etevents_allowed_bookings_input                   */
/* Summary:  Display and fill the form field                   */   
/**************************************************************/
function lj_etevents_allowed_bookings_input() {
	// get option 'text_string' value from the database
	$options = get_option( 'lj_etevents_allowed_bookings' );
	$text_string = $options['text_string'];
	// echo the field
	echo "<input id='text_string' size =\"25\" name='lj_etevents_allowed_bookings[text_string]' type='text' value='$text_string' />";
	echo "<p class=\"description\">Default maximum guests allowed to book for an event. This field is ignored if Event Max field is populated on post.</p>";	
}
/**************************************************************/
/* Name: lj_etevents_allowed_reservations_input                   */
/* Summary:  Display and fill the form field                   */   
/**************************************************************/
function lj_etevents_allowed_reservations_input() {
	// get option 'text_string' value from the database
	$options = get_option( 'lj_etevents_allowed_reservations' );
	$text_string = $options['text_string'];
	// echo the field
	echo "<input id='text_string' size =\"25\" name='lj_etevents_allowed_reservations[text_string]' type='text' value='$text_string' />";
   echo "<p class=\"description\">Default maximum guests allowed to make reservations. This field is ignored if Event reservation field is populated on post.</p>";	
}
			
				
/**************************************************************/
/* Name: lj_etevents_validate_email                         */
/* Summary:  Validate user input (we want text only) (email   */   
/**************************************************************/
function lj_etevents_validate_email( $input ) 
  {
 	$pattern = '~[a-zA-Z0-9-_.]+@[a-zA-Z]+\.[a-z]{2,4}~i';
    $valid['text_string']=$input['text_string']; 
		if(preg_match($pattern,$input['text_string']))
		{ //if TRUE, a match has been found
	      
		}
		else
		{
           $valid['text_string']='';
			add_settings_error
			(
					'lj_etevents_text_string',
					'lj_etevents_texterror',
					'Please use the format: xxxxxxx@xxxxxx.xxx!',
					'error'
			);
		}		

	return $valid;
}

/**************************************************************/
/* Name: lj_etevents_validate_options                         */
/* Summary:  Validate user input (we want text only) (email   */   
/**************************************************************/
function lj_etevents_validate_booking_notify( $input ) 
  {
       $options = get_option( 'lj_etevents_notification_onbooking' );
			if ( ! isset( $input['lj_etevents_notification_onbooking_checkbox'] ) || $input['lj_etevents_notification_onbooking_checkbox'] != '1' )
				$options['lj_etevents_notification_onbooking_checkbox'] = 0;
			  else
				$options['lj_etevents_notification_onbooking_checkbox'] = 1;
			  return $options;
		
}


/**************************************************************/
/* Name: lj_etevents_validate_options                         */
/* Summary:  Validate user input (we want text only) (email   */   
/**************************************************************/
function lj_etevents_validate_cancel_notify( $input ) 
  {
       $options = get_option( 'lj_etevents_validate_cancel_notify' );
			if ( ! isset( $input['lj_etevents_notification_oncancel_checkbox'] ) || $input['lj_etevents_notification_oncancel_checkbox'] != '1' )
				$options['lj_etevents_notification_oncancel_checkbox'] = 0;
			  else
				$options['lj_etevents_notification_oncancel_checkbox'] = 1;
			  return $options;
		
}
/**************************************************************/
/* Name: lj_etevents_validate_allowed_bookings_default    */
/* Summary:  Validate user input (we want numbers             */   
/**************************************************************/
function lj_etevents_validate_allowed_bookings_default( $input ) 
  {
 	$pattern = '/^[1-9][0-9]{0,5}$/';
    $valid['text_string']=$input['text_string']; 
		if(preg_match($pattern,$input['text_string']))
		{ //if TRUE, a match has been found
	      
		}
		else
		{
           $valid['text_string']='';
			add_settings_error
			(
					'lj_etevents_text_string',
					'lj_etevents_texterror',
					'Please enter a numeric value betweeen 0 and 99999.',
					'error'
			);
		}		

	return $valid;
}
/**************************************************************/
/* Name: lj_etevents_validate_allowed_reservations_default    */
/* Summary:  Validate user input (we want numbers             */   
/**************************************************************/
function lj_etevents_validate_allowed_reservations_default( $input ) 
  {
 	$pattern = '/^[0-9][0-9]{0,5}$/';
    $valid['text_string']=$input['text_string']; 
		if(preg_match($pattern,$input['text_string']))
		{ //if TRUE, a match has been found
	      
		}
		else
		{
           $valid['text_string']='';
			add_settings_error
			(
					'lj_etevents_text_string',
					'lj_etevents_texterror',
					'Please enter a numeric value betweeen 0 and 99999.',
					'error'
			);
		}		

	return $valid;
}




?>