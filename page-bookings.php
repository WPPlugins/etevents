<?php session_start();
/*
Template Name: Bookings Page
*/
?>
<?php

	$et_ptemplate_settings = array();
	$et_ptemplate_settings = maybe_unserialize( get_post_meta(get_the_ID(),'et_ptemplate_settings',true) );

	$fullwidth = isset( $et_ptemplate_settings['et_fullwidthpage'] ) ? (bool) $et_ptemplate_settings['et_fullwidthpage'] : false;

	$et_regenerate_numbers = isset( $et_ptemplate_settings['et_regenerate_numbers'] ) ? (bool) $et_ptemplate_settings['et_regenerate_numbers'] : false;
?>

<?php get_header(); ?>
	<?php get_template_part('includes/breadcrumbs'); ?>

	<div id="left-area"<?php if ($fullwidth) echo ' class="fullwidth"'; ?>>
	<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
		<div class="big-box">
			<div class="big-box-top">
				<div class="big-box-content">
					<div class="post clearfix single">
						<h1 class="title"><?php the_title(); ?></h1>
						<?php the_content(); ?>
						<?php wp_link_pages(array('before' => '<p><strong>'.esc_html__('Pages','Event').':</strong> ', 'after' => '</p>', 'next_or_number' => 'number')); 

/**************************************************************/
/* end of default code                                        */
/**************************************************************/
						
?>

<?php
/**************************************************************/
/* Name: lj_etevents_send_email                             */
/* Summary:  Determines if email is sent and what type        */    
/**************************************************************/
function lj_etevents_send_email($lj_option_notemail,
                                $lj_option_noteonbookingorcancel,								
								$postEventId,
								$emailtype) 
{

//let's get the current user and email
	$currentUser=wp_get_current_user();
	
//current user email
	$userEmail=$currentUser->user_email;

//Post title
	$postTitle=get_the_title($postEventId); 

// Event Type
	 $categories = get_the_category($postEventId);
	 $catType=$categories[0]->cat_name;				 

//date
	$origpost = get_post($postEventId);
	$originpost=$origpost->post_date;				

//User Blog name as from in email
    $blogname=get_bloginfo('name');

	 $recipients=$userEmail;
	 
	 $subject=$postTitle;
	 $subject.="-";	 
	 $subject.=$catType;	 	 
     $headers = 'From: '.$blogname.' <'.$lj_option_notemail.'>' . "\r\n";
	 //buildign the header we need to determine if we are booking or cancleing.
	 // 1 means book 2 means cancel - so ajust message accordingly
	 
	 if ($emailtype==1)
	 {
    	 $message="You are now registered for the Event: ".$subject." on ".$originpost.".\r\n";
	 }

	 if ($emailtype==2)
	 {
    	 $message="You have now been deregistered for the Event: ".$subject." on ".$originpost.".\r\n";	 
	 }
 	 if ($emailtype==3)
	 {
    	 $message="The event is full. You have been placed on the reserved list for the Event: ".$subject." on ".$originpost.".\r\n";	 
	 }
	 
	 
	 $message.="Thank You.";
	 
     // user email
	 wp_mail( $recipients, $subject, $message,$headers);
	 
     //send a second one for the event coord.
	 if ($lj_option_noteonbookingorcancel==1)
	 {

		 $subject='EVENT BOOKING: '.$subject;
		 $recipients=$lj_option_notemail;
         $message='User: '.$currentUser->display_name."\r\n";
         $message.='Email: '.$currentUser->user_email."\r\n";		 
         $message.='Name: '.$currentUser->user_lastname.', '.$currentUser->user_firstname."\r\n";
		 		 		 
		 if ($emailtype==1)
		 {
	      	 $message.="has registered for the Event: ".$postTitle."(".$catType.") on ".$originpost.".\r\n";
		 }
		 else
		 {
	      	 $message.="has deregistered for the Event: ".$postTitle."(".$catType.") on ".$originpost.".\r\n";
		 }
	 
         $headers = 'From: '.$blogname.' <'.$lj_option_notemail.'>' . "\r\n";

	      wp_mail( $recipients, $subject, $message,$headers);
	 }
	 


}

/**************************************************************/
/* Meat of application                                        */
/* Pulls out all invalid posts                                */
/*  - Stuck with code that exists from functions.php          */
/**************************************************************/
	global $wpdb, $monthnum, $year, $wp_locale, $posts, $shortname;
    // $wpdb->show_errors();
	//$wpdb->print_error();
	//set up table id


/**************************************************************/
/* Need to go ahead and grap user switching vars              */
/**************************************************************/	
     //notification email
	$lj_options = get_option( 'lj_etevents_notification_email' );
	$lj_option_notemail = $lj_options['text_string'];
     //notification on booking email
	$lj_option = get_option( 'lj_etevents_notification_onbooking' );
	$lj_option_noteonbooking = $lj_option['lj_etevents_notification_onbooking_checkbox'];	
	
     //notification on cancel email
	$lj_option = get_option( 'lj_etevents_notification_oncancel');
	$lj_option_noteoncancel = $lj_option['lj_etevents_notification_oncancel_checkbox'];		    
	  
     //max booking default
	$lj_options = get_option( 'lj_etevents_allowed_bookings' );
	$lj_option_maxbookingdefault = $lj_options['text_string'];
     //max reservationst
	$lj_options = get_option( 'lj_etevents_allowed_reservations' );
	$lj_option_maxreserdefault = $lj_options['text_string'];
	
	//max users per event
	//$lj_maxeventusers=$lj_option_maxbookingdefault+$lj_option_maxreserdefault;

	
	$myTable=$wpdb->prefix . "lj_etevents";
		  
	$blogcat = (int) get_catId(get_option($shortname . '_blog_cat'));
	$blogcats_array = array_merge( array($blogcat), get_term_children($blogcat, 'category') );
	$blogcats = implode(",",$blogcats_array);
	$blog_category_posts_id = $wpdb->get_results("SELECT $wpdb->posts.ID "
									. "FROM $wpdb->posts "
									. "INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) " 
									. "INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) "
									. "AND $wpdb->term_taxonomy.taxonomy = 'category' "
									. "AND $wpdb->term_taxonomy.term_id IN ($blogcats) "
									. "AND($wpdb->posts.post_type = 'post') AND ($wpdb->posts.post_status = 'publish' OR $wpdb->posts.post_status = 'future') "
									. "ORDER BY post_date DESC ", ARRAY_A
									);



/**************************************************************/
/* creates an array of invalid posts                          */
/**************************************************************/
	$excluded_ids = array();
	foreach ( $blog_category_posts_id as $blog_cat_post ) 
	    {
			$excluded_ids[] = $blog_cat_post['ID'];
		}
	$excluded_posts_string = implode(",",$excluded_ids);

/**************************************************************/
/* Pulls back a list of future valid events                   */
/**************************************************************/
    $blog_category_posts_id = $wpdb->get_results("SELECT DISTINCT ID, DATE_FORMAT(post_date,'%m/%d/%y') AS tdate
				FROM $wpdb->posts
				WHERE post_type = 'post' AND (post_status = 'publish' OR post_status = 'future') AND ID NOT IN ($excluded_posts_string)
				AND post_date>NOW()
				ORDER BY post_date ASC");		
				


/**************************************************************/
/* Need to build a table with here - Use already defined CSS  */
/**************************************************************/
?>
<br />

<!--**************************************************************-->
<!-- Need to build a table with here - Use already defined CSS  *-->
<!--**************************************************************-->

<?php
/**************************************************************/
/* Check to see if someone posted a booking or cancellation  */
/* Reference - 0 - no recond                                */
/* Reference - 1 - Canceled                                 */
/* Reference - 2 - Booked                                  */
/**************************************************************/
if (isset($_POST['lj_etevent_submit']))
{

  //someone booked/cancelled -> need to update tables
		$postUserId=$_POST['lj_etevent_user_id'];
		$postEventId=$_POST['lj_etevent_event_id'];
        $postStatus=$_POST['lj_etevent_status'];
        $postOrgPostDate=$_POST['lj_etevent_postdate'];		
/**************************************************************/
/* If  lj_etevent_status                                       */
/**************************************************************/
	switch ($postStatus) {
		case 0:
         //insert row as booked
		 $values=array(
		               'user_id'=>$postUserId,
		               'event_id'=>$postEventId,
		               'status'=>2,
 		               'dateaction'=>date('Y-m-d', strtotime($postOrgPostDate))					   
					   );					   

         $formats_values=array(
		                       '%d', 
		                       '%d', 							   
							   '%d', 
							   '%d');
		 
		/**************************************************************/
		/* Inserts rows in table if they don't exist                  */
		/**************************************************************/												
		  $wpdb->insert($myTable, $values, $format_values);

		/*******************************/
		/* Send enmail if needed       */
		/*******************************/
        //need to send user id, notificatin email,  
		  lj_etevents_send_email($lj_option_notemail,
								 $lj_option_noteonbooking,
								 $postEventId,
								 '1');
		                         

			break;
		case 1:
		/**************************************************************/
		/* Update row to booking                                      */
		/**************************************************************/												
			$wpdb->update( 
				$myTable, 
				array( 
					'status' => 2	// string
				), 
				array( 
				     'user_id' => $postUserId,
					 'event_id'=> $postEventId ), 
				array( 
					'%d',	// value1
					'%d'	// value2
				), 
				array( '%d' ) 
			);
			
		/*******************************/
		/* Send enmail if needed       */
		/*******************************/
        //need to send user id, notificatin email,  
		  lj_etevents_send_email($lj_option_notemail,
								 $lj_option_noteonbooking,
								 $postEventId,
								 '1');
		                         			
			
			break;
		case 2:
		/**************************************************************/
		/* Update a Row to canceled                                   */
		/**************************************************************/												
			$wpdb->update( 
				$myTable, 
				array( 
					'status' => 1	// string
				), 
				array( 
				     'user_id' => $postUserId,
					 'event_id'=> $postEventId ), 
				array( 
					'%d',	// value1
					'%d'	// value2
				), 
				array( '%d' ) 
			);
			
		/*******************************/
		/* Send enmail if needed       */
		/*******************************/
        //need to send user id, notificatin email,  
		  lj_etevents_send_email($lj_option_notemail,
								 $lj_option_noteonbooking,
								 $postEventId,
								 '2');			
			break;
		case 3:
		/**************************************************************/
		/* Update row to Reservered                                   */
		/**************************************************************/												
			$wpdb->update( 
				$myTable, 
				array( 
					'status' => 3	// string
				), 
				array( 
				     'user_id' => $postUserId,
					 'event_id'=> $postEventId ), 
				array( 
					'%d',	// value1
					'%d'	// value2
				), 
				array( '%d' ) 
			);
			
		/*******************************/
		/* Send enmail if needed       */
		/*******************************/
        //need to send user id, notificatin email,  
		  lj_etevents_send_email($lj_option_notemail,
								 $lj_option_noteonbooking,
								 $postEventId,
								 '3');
		                         			
			
			break;	
		case 4:
         //insert row as Reservered
		 $values=array(
		               'user_id'=>$postUserId,
		               'event_id'=>$postEventId,
		               'status'=>3,
 		               'dateaction'=>date('Y-m-d', strtotime($postOrgPostDate))					   
					   );					   

         $formats_values=array(
		                       '%d', 
		                       '%d', 							   
							   '%d', 
							   '%d');
		 
		/**************************************************************/
		/* Inserts rows in table if they don't exist                  */
		/**************************************************************/												
		  $wpdb->insert($myTable, $values, $format_values);

		/*******************************/
		/* Send enmail if needed       */
		/*******************************/
        //need to send user id, notificatin email,  
		  lj_etevents_send_email($lj_option_notemail,
								 $lj_option_noteonbooking,
								 $postEventId,
								 '3');
		                         

			break;				
				
	}
}
?>


    <table width="100%" border="1" align="center">
      <caption>
       <h2 class="title">Current Events</h2>
       <br />
	   
      </caption>


      
      <tr>
        <th col align="center"><h4 class="title"><a>Event</a></h4></th>
 		<th col align="center"><h4 class="title"><a>Type</a></h4></th>
 		<th col align="center"><h4 class="title"><a>Date</a></h4></th>
		<th col align="center"><h4 class="title"><a>Status</a></h4></th>
		<th col align="center"><h4 class="title"><a>Action</a></h4></th>
      </tr>

<?php

	/**************************************************************/
	/* Let's grab Current User Id, first and last name            */
	/**************************************************************/
	global $current_user;
	$current_user = wp_get_current_user();
	$current_user_lastname=$current_user->user_lastname;
	$current_user_firstname=$current_user->user_firstname;
	
	// lets do a check to see if we should continue
	$allowbook="";
	if ((strlen($current_user_firstname)<1)||(strlen($current_user_lastname)<1))
	{
		$allowbook="disabled";
	}
	
	
	$current_user_id=$current_user->ID;
	       			
 	  foreach ($blog_category_posts_id as $post_id)
	    {
		  $Event_ID=$post_id->ID;
		 // let's build our rows
		  echo ("<tr col align=\"center\">");
		       // Column Event
			   echo("<td><p class=\"meta-info\"><a>");
		      	
				$postTitle=get_the_title($Event_ID); 
				$permalink = get_permalink($Event_ID); 
				echo ("<A HREF=\"");
				echo ($permalink);
				echo ("\">");
				echo ($postTitle);
				echo ("</A>");
				
 		       echo("</a></p></td>");		   
		       // Event Type			   
			   echo("<td><p class=\"meta-info\"><a>");
				 $categories = get_the_category($Event_ID);
				 echo $categories[0]->cat_name;				 
 		       echo("</a></p></td>");		   
		       // Date			   
			   echo("<td><p class=\"meta-info\"><a>");

				$origpostdate = $post_id->tdate;				
				 echo ($origpostdate);			 
 		       echo("</a></p></td>");
			   

				/**************************************************************/
				/*Need to get how many are already redigsterd for the event  */
				/**************************************************************/
			   
			   //first need to get a count of how many users are currently registered.
			     $setTotalEventUsers="SELECT  count(*) as bookings  FROM 
					 ".$myTable." a
					where a.event_id=".$Event_ID."
					and a.status IN('2','3')
					group by a.event_id";  
				 $resultsRec = $wpdb->get_row($setTotalEventUsers);
				 
				 //number of folks already registered
				 $maxam=$resultsRec->bookings;
			   
			  	   
			   
				/**************************************************************/
				/* Need to query the event and user                           */
				/**************************************************************/
                $setMyQuery="SELECT * FROM ".$myTable." WHERE user_id = ".$current_user_id." AND event_id=".$Event_ID."";
				
                $eventRec = $wpdb->get_row($setMyQuery);

				/**************************************************************/
				/* See if we have rows                                        */
				/**************************************************************/
				$bRowCount=$wpdb->num_rows;			   
				
				
				

								
				/**************************************************************/
				/* set default to not booked                                  */
				/**************************************************************/		
				$bStatus="Not Booked";  
                if ($bRowCount<1)
				{
     			    $bStatus="Not Booked";  
     			    $bAction="Book"; 								
					$bStatusCD=0; //just used to insert					
				}
				else 
				{
					 // we have a record but not sure if it's been cancelled or not.
					 switch ($eventRec->status)
					  {
							case 1:
							// Canceled - 
								$bStatus="Booking Withdrawn";  
  			     			    $bAction="Book"; 								
								$bStatusCD=1;
							break;
							case 2:
							// Booked
								$bStatus="Booked";  
  			     			    $bAction="Cancel"; 								
								$bStatusCD=2;
							break;							
							case 3:
							// Reserved
								$bStatus="Reserved";  
  			     			    $bAction="Cancel"; 								
								$bStatusCD=2;
							break;							
							
					}

				}
					/**************************************************************/
					// Truth table - you can always cancel but now always book    */
					/**************************************************************/				
			
					/**************************************************************/
                    // we will set it to not book - however if the even is full   */
					// or has reserverd - we will need to check and set as needed.*/
					// will defualt it as "book" for now                          */
					// $maxam  is the currently number of folks booked            */
					// $lj_option_maxbookingdefault = max of allowed to book      */
                    // $lj_option_maxreserdefault =  max number allowed to book   */
                    // $lj_maxeventusers= 	max users per event                   */
  					/**************************************************************/					
					// A need option I forgot about. I give the events folks thae */
					// ability to over ride the default settings.                 */
					// I need to retrive the defaults from the post and if they   */
					// are populated then use those values instead                */
					/**************************************************************/					
					
					$post_lj_etevent_maxbooking=get_post_meta($Event_ID,'_lj_etevents_max_booking',true);
					$post_lj_etevent_maxreserve=get_post_meta($Event_ID,'_lj_etevents_reserve_booking',true);
					$lj_maxeventusers=0;
					$lj_etevents_MBD=0;
					$lj_etevents_MRD=0;
					
					//let's determine whcih default we use. Post always over ride defauls settings
				    if ($post_lj_etevent_maxbooking>0)
					{
					    $lj_etevents_MBD=$post_lj_etevent_maxbooking;
					}
					else
					{
						$lj_etevents_MBD=$lj_option_maxbookingdefault;					
					}
				
					//let's determine whcih default we use. Post always over ride defauls settings					
				    if ($post_lj_etevent_maxreserve>0)
					{
					    $lj_etevents_MRD=$post_lj_etevent_maxreserve;
					}
					else
					{
						$lj_etevents_MRD=$lj_option_maxbookingdefault;
					}					
                    //max user default
					$lj_maxeventusers=$lj_etevents_MBD+$lj_etevents_MRD;

   				     $etevent_disablebutton="";
					 $MaxEventDisabled="";
					  if ($bAction=="Book")
					  {
						/**************************************************************/
						// Lets see if our event if full                              */
						/**************************************************************/				
						if ($maxam>=$lj_maxeventusers)
						{
							$bStatus="Event Full.";  
  			     		    $bAction="No Action"; 								
							$bStatusCD=0;
							$MaxEventDisabled="disabled";
						}
						else
						{
							/**************************************************************/
							// Need to see if we are in reserver status or not            */
							/**************************************************************/				
							if ($maxam>=$lj_etevents_MBD)
							{
								$bStatus="Event Full - Reserve only.";  
								$bAction="Reserve"; 								
								$bStatusCD=3;
									
								/**************************************************************/
								// OOpss... bug - make sure we check & insert new rec if need */
								/**************************************************************/							
								   if ($bRowCount<1)																			
								   {
									   $bStatusCD=4;
																		   
								   }
								   
							}	
						}
						
					  }
                 
				   //need to disable button if needed.
					if ((strlen($MaxEventDisabled)>0)||(strlen($allowbook)>0))
					{
						$etevent_disablebutton="disabled";
					}
				
							   
		       // Status			   
			   echo("<td><p class=\"meta-info\"><a>");
				 echo ($bStatus);

 		       echo("</a></p></td>");		   
		       // Action			   
			   echo("<td>");
			
				?>
					<form action="" method="POST" name="lj_etevent_form">
						<input type="hidden" name="lj_etevent_user_id" value="<?php echo($current_user_id);?>"/>
						<input type="hidden" name="lj_etevent_event_id" value="<?php echo($Event_ID);?>"/>
						<input type="hidden" name="lj_etevent_status" value="<?php echo($bStatusCD);?>"/>												
						<input type="hidden" name="lj_etevent_postdate" value="<?php echo($origpostdate);?>"/>	
						<input type="submit" <?php echo($etevent_disablebutton); ?> name="lj_etevent_submit" value="<?php echo($bAction); ?>"/>                     
					</form>	
				
				
<?php				
 		       echo("</td>");		   
		       echo ("</tr>");
	   

            }
           
?>			


<!--**************************************************************-->
<!-- Rest of templated code stuff                                 -->
<!--**************************************************************-->


    </table>
	<br>

<?php
//here we will display a warning message is a user ahsn't entered a first & last name
   if ((strlen($allowbook))>1)
   {
   ?>
<table style="border:1px solid red;">
  <tr>

              <td bgcolor="#EED77D" ><font color="#000000"><strong>*Booking for 
                Events have been disabled. Please complete your user profile by 
                filling out your First and Last name.</strong></font></td>
</tr>
</table>



<?php   
   }  
?>


					</div> 	<!-- end .post-->
				</div> 	<!-- end .big-box-content-->
			</div> 	<!-- end .big-box-top-->
		</div> 	<!-- end .big-box-->
	<?php endwhile; endif; ?>
	</div> 	<!-- end #left-area -->
	<?php if (!$fullwidth) get_sidebar(); ?>

<?php get_footer(); ?>