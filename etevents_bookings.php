<!---***********************************************************************************-->
<!-- ETEvents - Bookings Pasge                                                          -->
<!-- Summary - Bookings page for ETEvents Plugin                                        -->
<!-- Copywrite - Leonard H. Johnson                                                     -->
<!---***********************************************************************************-->
<!-- Date         Author               Summary                                          -->
<!---***********************************************************************************-->
<!-- 09/05/2013   Leonard H. Johnson   Initial Creation                                 -->
<!---***********************************************************************************-->
<?php
		global $wpdb,$myTable;
		$myTable=$wpdb->prefix . "lj_etevents";


/**************************************************************/
/* Used to detemine what is viewed on the display             */
/**************************************************************/
 	     if(isset($_GET['trackaction']))
		 {
			switch ($_GET['trackaction'])
			{
				case'view'://date descending
					$defaultview=1;
					break;
				case'send'://date descending
					$defaultview=2;
					break;
				case'cancel'://date descending
					echo "cancel";
					$defaultview=3;
					break;
 			}
		 }
		else
		{
			$defaultview=0;
		}

// hide show all buttin
	     if(isset($_GET['Submit']))
		 {
         		$defaultview=0;
		 }
/**************************************************************/
/* Need to build a table with here - Use already defined CSS  */
/**************************************************************/
?>
<div id="eteventsaboutpage">
    <div class="wrap">
      <div id="eteventsicon" class="icon32"></div>
		 <h2>ETEvents Bookings</h2>
		    <h3>Booking Summary</h3>

			<?php
			/**************************************************************/
			/* Shows the show all button if the view demands it           */
			/**************************************************************/
			 if ($defaultview!=0)
			   {
			   ?>
				  <form action="<?php
									 $pageurl=get_admin_url().'admin.php?page='.$_GET['page'].'&amp;trackaction=reset&id=';
									 echo $pageurl?>" method="post">
					 <p class="submit">
						<input name="Submit" class="button-primary" type="submit" value="View All Events" />
					 </p>
				</form>
			  <?php
			  }
			  ?>
			  <div class="eteventwidetable">
				<table class="hidealllabels widefat">
				<caption><h3><?php _e('Summary','etevents'); ?></h3></caption>
					<thead>
					<tr>
						<th id="event"><?php _e('Event','etevents'); ?></th>
						<th id="type"><?php _e('Type','etevents'); ?></th>
						<th id="date"><?php _e('Date','etevents'); ?></th>
						<th id="booked"><?php _e('Booked #','etevents'); ?></th>
						<th id="reserved"><?php _e('Reserved #','etevents'); ?></th>
						<th id="status"><?php _e('Status','etevents'); ?></th>

					</tr>
					</thead>
					<tbody>

					<?php
					/**************************************************************/
					/* Build defaultview =0           */
					/**************************************************************/
					  if ($defaultview==0)
					  {
					    $tempsql=""; //passing in a dummy var to satisfy complier
						$buildsql=etevents_buildsql($tempsql);
						echo etevents_getrows($buildsql);
					   }
					   else
					   {
   						$buildsql=etevents_buildsql_event($_GET['id']);
						$etevent_buildsql=$buildsql; // pass the event info into send reminders if needed
						echo etevents_getrows($buildsql);
					   }

					?>
					</tbody>
				</table>

				<?php
					/**************************************************************/
					/*Build user list                                      */
					/* View if event has been clicked on                    */
					/**************************************************************/
					  if ($defaultview==1)
					  {
				?>
					  <div class="eteventwidetable">
							<table class="hidealllabels widefat">
							<caption><h3><?php _e('Bookings','etevents'); ?></h3></caption>
								<thead>
								<tr>
									<th id="user"><?php _e('Username','etevents'); ?></th>
									<th id="fname"><?php _e('First tname','etevents'); ?></th>
									<th id="lname"><?php _e('Last name','etevents'); ?></th>
									<th id="email"><?php _e('Email','etevents'); ?></th>
									<th id="notes"><?php _e('Notes','etevents'); ?></th>
								</tr>
								</thead>
								<tbody>
								<?php
									$rowevents=etevents_getuserrows($_GET['id']);
									echo $rowevents;
								?>
					   		</tbody>
  						</table>
                       </div>
					<?php
					}
					?>

				<?php
					/**************************************************************/
					/* Send Reminders List                                        */
					/* View if send reminders was selected                        */
					/**************************************************************/
					  if ($defaultview==2)
					  {
                      ?>
                      <br />
						  	<table class="hidealllabels widefat" width="25%">
                            <tr>
                              <td align="center"><h3>Reminders have been sent</h3></td>
                            </tr>
                            </table>
                <?php

     						etevents_senduseremail($_GET['id']);

					  }
				?>



	</div>
</div>



<?php

/**************************************************************/
/* Function: etevents_senduseremail                          */
/* Summary: grab emails for remindesr                        */
/**************************************************************/
	function etevents_senduseremail($Event_ID)
	{

	    global $wpdb,$myTable;
		$myTable=$wpdb->prefix . "lj_etevents";

        //lets get the users asccoited with the event
        $setMyQuery="SELECT  user_id FROM
			 ".$myTable."
		where event_id=".$Event_ID." and status=2";

        $myemails = $wpdb->get_results($setMyQuery);
 	    $rowreturn='';


		 //event title
		 $EventTitle=get_the_title($Event_ID);


		 //event sub cat
		 $etevent_categories = get_the_category($Event_ID);
		 $Etevent_subcat=$etevent_categories[0]->cat_name;


		 //date
		$origpost = get_post($Event_ID);
		$originpost=$origpost->post_date;


		 //blog name
		 //User Blog name as from in email
          $blogname=get_bloginfo('name');

     //notification email
		$lj_options = get_option( 'lj_etevents_notification_email' );
		$lj_option_notemail = $lj_options['text_string'];

	  	  foreach ($myemails as $user_id)
		  {
 		    $user=get_user_by('id',$user_id->user_id);
			$UserEmail=$user->user_email;


			   lj_etevents_send_email($UserEmail,$EventTitle,$Etevent_subcat,$originpost,$blogname,$lj_option_notemail);


		  }
 	}
/**************************************************************/
/*                                                              */
/**************************************************************/

/**************************************************************/
/* Name: lj_etevents_send_email                             */
/* Summary:  send reminders to all users of an event         */
/**************************************************************/
function lj_etevents_send_email($recipients,
                                $postTitle,
								$catType,
								$originpost,
								$blogname,
								$lj_option_notemail)
{

	 $subject=$postTitle;
	 $subject.="-";
	 $subject.=$catType;
     $headers = 'From: '.$blogname.' <'.$lj_option_notemail.'>' . "\r\n";

    	 $message="This is a reminder. You are registered for the Event: ".$subject." on ".$originpost.".\r\n";
         $message.="Thank You.";

     // user email
	 wp_mail( $recipients, $subject, $message,$headers);

}
/**************************************************************/
/*                                                              */
/**************************************************************/

/**************************************************************/
/* Function: etevents_getrows                                 */
/* Summary: Build Booking level Details                       */
/**************************************************************/
	function etevents_getrows($rowreturn)
	{
	    global $wpdb,$myTable;
		$myTable=$wpdb->prefix . "lj_etevents";
		$pageurl=get_admin_url().'admin.php?page='.$_GET['page'];


	   $myevents = $wpdb->get_results($rowreturn);
	    $rowreturn='';
	  	  foreach ($myevents as $post_id)
		  {
 		    $Event_ID=$post_id->event_id;
			//let's grab the reserve list number for each
            $etevent_reserved=etevents_buildsql_reserve($Event_ID);

			$EventTitle=get_the_title($Event_ID); //event title

            //lets grabe the booking number so we can turn on and off details as needed
				$nBooked=etevents_buildsql_booked($Event_ID);

				$rowreturn.='<td class="proid column-proid">';
				$rowreturn.='  <strong>';
				$rowreturn.='   	<a href="">'.$EventTitle.'</a>';
				$rowreturn.='	</strong><br>';
				$rowreturn.='	<div style="padding-bottom: 5px;">';
				$rowreturn.='   	<span class="edit" id="edit-2">';

				if ($nBooked>0)
				{
					$rowreturn.='   	   <a href="'.$pageurl.'&amp;trackaction=view&amp;id='.$Event_ID.'">View Bookings</a></span> | <span class="delete" id="delete-2">';

					$rowreturn.='   	   <a href="'.$pageurl.'&amp;trackaction=send&amp;id='.$Event_ID.'">Send Reminders</a></span><span class="default" id="default-2">';
				}
				//$rowreturn.='   	   <a href="'.$pageurl.'&amp;trackaction=cancel&amp;id='.$Event_ID.'">Cancel</a>';
				$rowreturn.=' 		</span></div>';
				$rowreturn.='</td>';

			    $categories = get_the_category($Event_ID); //event cat
				$rowreturn.='<td>';
					$rowreturn.=$categories[0]->cat_name;
				$rowreturn.='</td>';

			    $s_Date=$post_id->tdate;//date
				$rowreturn.='<td>';
					$rowreturn.=$s_Date;
				$rowreturn.='</td>';



				$rowreturn.='<td>';
					$rowreturn.=$nBooked;//bookings
				$rowreturn.='</td>';

				$etevent_reserved=etevents_buildsql_reserve($Event_ID);
				$rowreturn.='<td>';
					$rowreturn.=$etevent_reserved;//reserved
				$rowreturn.='</td>';

				$rowreturn.='<td>';
					$rowreturn.='Open';//status
				$rowreturn.='</td>';


            $rowreturn.='</tr>';
		  }
		  return $rowreturn;
 	}
/**************************************************************/
/* Function: etevents_getuserrows                             */
/* Summary: Process Filter Info                             */
/**************************************************************/
	function etevents_getuserrows($rowreturn)
	{
	    global $wpdb,$myTable;
		$myTable=$wpdb->prefix . "lj_etevents";
             $setMyQuery="SELECT  user_id, status FROM
					 ".$myTable."
					where event_id=".$rowreturn." and status IN(2,3)";


	   $myevents = $wpdb->get_results($setMyQuery);
	    $rowreturn='';
	  	  foreach ($myevents as $user_id)
		  {
 		    $user=get_user_by('id',$user_id->user_id);
			$UserEmail=$user->user_email;
			$UserDisplayName=$user->display_name;
			$UserFirstName=$user->user_firstname;
			$UserLastName=$user->user_lastname;
			//set up reserve notes
			$UserStatus=$user_id->status;
			if ($UserStatus==3)
			{
				$UserStatus='Reserve';
			}
			else
			{
				$UserStatus='';
			}

                $rowreturn.='<tr>';
				//username
					$rowreturn.='<td class="proid column-proid">';
					$rowreturn.='  <strong>';
					$rowreturn.=' '.$UserDisplayName.' ';
					$rowreturn.='	</strong>';
					$rowreturn.='</td>';
			  //firstname
					$rowreturn.='<td>';
					$rowreturn.=' '.$UserFirstName.' ';
					$rowreturn.='</td>';
				//last name
					$rowreturn.='<td>';
					$rowreturn.=' '.$UserLastName.' ';
					$rowreturn.='</td>';
				//Email
					$rowreturn.='<td>';
					$rowreturn.=' '.$UserEmail.' ';
					$rowreturn.='</td>';
				//Status
					$rowreturn.='<td>';
					$rowreturn.=' '.$UserStatus.' ';
					$rowreturn.='</td>';


                $rowreturn.='</tr>';
		  }
		  return $rowreturn;
 	}
/**************************************************************/
/* Function: etevents_buildsql                                */
/* Summary: Build Top Level Event Detail                      */
/**************************************************************/
	function etevents_buildsql($sqlreturn)
	{
	    global $wpdb,$myTable;
		$myTable=$wpdb->prefix . "lj_etevents";


    $setMyQuery="SELECT  id AS event_id,DATE_FORMAT(post_date,'%m/%d/%y') AS tdate  FROM
					 wp_posts
					where post_date > Now()
					and post_status != 'trash'
					group by id
					order by post_date asc";


    /*    $setMyQuery="SELECT  count(*) as bookings,a.event_id,DATE_FORMAT(b.post_date,'%m/%d/%y') AS tdate  FROM
					 ".$myTable." a left join wp_posts b on a.event_id=b.ID
					where a.status=2 and  a.DateAction > Now()
					group by a.event_id
					order by b.post_date asc";
	*/
        return $setMyQuery;
	  }
/**************************************************************/
/* Function: etevents_buildsql_event                             */
/* Summary: Process Filter Info                             */
/**************************************************************/
	function etevents_buildsql_event($sqlreturn)
	{
	    global $wpdb,$myTable;
		$myTable=$wpdb->prefix . "lj_etevents";

        $setMyQuery="SELECT  count(*) as bookings,a.event_id,DATE_FORMAT(b.post_date,'%m/%d/%y') AS tdate  FROM
					 ".$myTable." a left join wp_posts b on a.event_id=b.ID
					where a.status=2
					and a.event_id=".$sqlreturn."
					group by a.event_id
					order by b.post_date asc";

        return $setMyQuery;
	  }
/**************************************************************/
/* Function: etevents_buildsql_reserve                        */
/* Summary: Returns the Reserve count for a event             */
/**************************************************************/
	function etevents_buildsql_reserve($sqlreturn)
	{
	    global $wpdb,$myTable;
		$myTable=$wpdb->prefix . "lj_etevents";

        $setMyQuery="SELECT  count(*) as reserved_num  FROM
					 ".$myTable."
					where status=3
					and event_id=".$sqlreturn."";


	    $resultsRec = $wpdb->get_row($setMyQuery);

		 //number of folks in reserve
			 $maxam=$resultsRec->reserved_num;
	  return $maxam;
	 }
/**************************************************************/
/* Function: etevents_buildsql_booked                        */
/* Summary: Returns the Booked count for a event             */
/**************************************************************/
	function etevents_buildsql_booked($sqlreturn)
	{
	    global $wpdb,$myTable;
		$myTable=$wpdb->prefix . "lj_etevents";

        $setMyQuery="SELECT  count(*) as booked_num  FROM
					 ".$myTable."
					where status=2
					and event_id=".$sqlreturn."";


	    $resultsRec = $wpdb->get_row($setMyQuery);

		 //number of folks booked
			 $maxam=$resultsRec->booked_num;
	  return $maxam;
	 }

?>