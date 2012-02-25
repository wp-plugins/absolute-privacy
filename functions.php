<?php

/* This file holds the functions for the Absolute Privacy plugin.
   
   By John Kolbert
   http://www.johnkolbert.com/
   
   email: j[at]johnkolbert[dot]com
      
*/


/**
 *	abpr_installOptionsMenu function
 *
 *	Hooks the settings page into add_options_page
 *
 *	@return void
*/
function abpr_installOptionsMenu() {  // install the options menu
	if ( current_user_can( 'manage_options' ) ) 
		add_options_page( 'Absolute Privacy', 'Absolute Privacy', 1, __FILE__, 'abpr_optionsPage' );
} 


/**
 * abpr_optionsPage function.
 *
 * Displays the settings page. Called by abpr_installOptionsMenu 
 *
 * @return void
*/
function abpr_optionsPage(){
    
    global $wpdb;
    
    if ( isset( $_GET[ 'db_update' ] ) && abpr_needsUpgrade() ){
		abpr_doUpgrade();	//upgrade DB
		echo '<div class="updated"> <p>Absolute Privacy datbase settings upgraded successfully. Carry on. </p> </div>';
	}
	
    if ( isset( $_POST[ 'update_options' ] ) ) {	//we're updating
   		$options[ 'member_lockdown' ] = trim( $_POST[ 'member_lockdown' ],'{}' );
   		$options[ 'redirect_page' ] = trim( $_POST[ 'redirect_page' ],'{}' );
   		$options[ 'allowed_pages' ] = trim( $_POST[ 'allowed_pages' ],'{}' );
   		$options[ 'admin_block' ] = isset($_POST['admin_block']) ? trim( $_POST[ 'admin_block' ], '{}' ) : null;
   		$options[ 'rss_control' ] = trim( $_POST[ 'rss_control' ], '{}' );
   		$options[ 'rss_characters' ] = trim( $_POST[ 'rss_characters' ], '{}' );
   		$options[ 'members_only_page' ] = trim( $_POST[ 'members_only_page' ], '{}' );
   		$options[ 'profile_page' ] = trim( $_POST[ 'profile_page' ], '{}' );
   		
   		$options[ 'pending_welcome_email_subject' ] = trim( stripslashes( $_POST[ 'pending_welcome_email_subject' ] ), '{}' );
   		$options[ 'pending_welcome_message' ] = trim( stripslashes( $_POST[ 'pending_welcome_message' ] ), '{}' );
   		$options[ 'account_approval_email_subject' ] = trim( stripslashes( $_POST[ 'account_approval_email_subject' ] ), '{}' );
   		$options[ 'account_approval_message' ] = trim( stripslashes( $_POST[ 'account_approval_message'] ), '{}' );
   		$options[ 'admin_approval_email_subject' ] = trim( stripslashes( $_POST[ 'admin_approval_email_subject' ] ), '{}' );
   		$options[ 'admin_approval_message' ] = trim( stripslashes( $_POST[ 'admin_approval_message' ] ), '{}' );

   		update_option( ABSPRIVACY_OPTIONS, $options );
    
    	// Show a message to say we've done something
	    echo '<div class="updated"> <p> Options saved </p> </div>';
    
    } else {
    	$options = get_option( ABSPRIVACY_OPTIONS );
    }
    
    if ( get_option( 'users_can_register' ) != 1 ) {	//notify user that registrations are not enabled. Hopefully this will save me some support emails.
    	echo '<div class="updated"> <p><strong>Notice:</strong> Your settings do not currently allow users to register themselves. If you want to allow the Absolute Privacy plugin to handle user moderation, please check <em>anyone can register</em> on the <a href="' . home_url('wp-admin/options-general.php') . '">general settings page</a>.</p></div>';
    }
    
?>    
    <div class="wrap">
    	<div id="icon-plugins" class="icon32"></div>
			<h2>Absolute Privacy: Options Page</h2>	
<?php if ( abpr_needsUpgrade() ) : ?>
    
    	<p>Absolute Privacy requires that your database settings be upgraded.</p>
		<a class="button-secondary" href="<?php echo get_admin_url() . 'options-general.php?page=absolute-privacy/functions.php&db_update'; ?>">Upgrade Settings</a>

<?php return; endif; ?>

<?php if ( is_multisite() ) : ?>

			<p>Sorry, Absolute Privacy does not currently support multi-site enabled installations. This is planned for a future release, but a timeline is not available.</p>

<?php else : ?>

				<form method="post" action="">
					<div class="submit" style="display: block; margin-bottom: -30px;" ><input type="submit" name="update_options" value="Update Settings"  style="font-weight:bold;" /> </div>				
					<br clear="all" />

					<div style="float: left; width: 65%; margin: 5px;">	

					<table class="widefat" cellspacing="0">
						<thead>
							<tr class="thead">
								<th scope="col" style="width: 100px;" colspan="2">Privacy Method</th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
							</tr>
						</thead>
						
						<tbody id="users" class="list:user user-list">
							<tr valign="top">
								<th style="width: 30px;">Off</th>
								<td style="padding-top: 7px;"><input type="radio" name="member_lockdown" value="off" class="radio_class" <?php if ($options['member_lockdown'] == "off") echo " checked "; ?> /></td>
							   	<th style="width: 140px;"> Complete Lockdown: </th>
								<td style="padding-top: 7px;">  <input type="radio" name="member_lockdown" value="lockdown" class="radio_class" <?php if ($options['member_lockdown'] == "lockdown") echo " checked "; ?> /> </td>
								<th style="width: 120px;"> Members Area: </th>
								<td style="padding-top: 7px;"><input type="radio" name="member_lockdown" value="members_area" class="radio_class" <?php if ($options['member_lockdown'] == "members_area") echo " checked "; ?> /></td>
							</tr>
							
							<tr>
								<td colspan="6" id="members_off" <?php if( $options['member_lockdown'] != 'off' ) echo "style='display: none;'"; ?> >
									<p>Privacy is currently <code>off</code></p><p>What does this mean? Users may view your website as normal. However, user moderation is still enabled, meaning users can register, choose a username and password, but their
									account will not be active until you approve it. To disable user moderation, please deactivate the Absolute Privacy plugin.</p>
								</td>
						</tbody>
					</table>
															
					<table id="lockdown_settings" class="widefat" style="margin-top: 15px; <?php if( $options['member_lockdown'] != "lockdown" ) echo 'display: none;'; ?>" cellspacing="0">
						<thead>
							<tr class="thead">
								<th scope="col" style="width: 100px;" colspan="2">Lockdown Settings</th>
								<th scope="col">Setting Description:</th>
							</tr>
						</thead>

						<tbody id="users" class="list:user user-list">
							<tr>
								<td colspan="3">
									<p>Enabling <code>Complete Lockdown</code> means that only logged in users will be able to access any part of your website (except the pages you specify below). This is ideal for
									   private family, personal, or business blogs. User moderation is also enabled, meaning you must approve user accounts before they will be activated.</p>
								</td>
							</tr>
							
							<tr>
								<th style="width: 120px;">Allowed Pages:</th>
								<td><input type="text" name="allowed_pages" id="allowed_pages" style="width: 58px;" value="<?php echo isset($options['allowed_pages']) ? $options['allowed_pages'] : ''; ?>" /></td>
								<td>These pages will be accessible to non-logged in users. List page IDs separated by a comma (eg: <code>0,19,12</code>). <em>Tip:</em> Enter <code>0</code> to allow access to the home page. </td>
							</tr>
							<tr>
								<th>RSS Control:</th>
								<td colspan="2">
									<input type="radio" name="rss_control" value="off" <?php if ($options['rss_control'] == "off") echo 'checked'; ?> /> RSS Disabled &nbsp; &nbsp;
									<input type="radio" name="rss_control" value="on" <?php if ($options['rss_control'] == "on") echo 'checked'; ?> /> RSS On &nbsp; &nbsp;<br />
									<input type="radio" name="rss_control" value="headline" <?php if ($options['rss_control'] == "headline") echo 'checked'; ?> /> Limited to headlines &nbsp; &nbsp;
									<input type="radio" name="rss_control" value="excerpt" <?php if ($options['rss_control'] == "excerpt") echo 'checked'; ?> /> Limited to <input type="text" name="rss_characters" id="rss_characters" value="<?php echo isset($options['rss_characters']) ? $options['rss_characters'] : ''; ?>" style="width: 32px;" />&nbsp;Characters
									<br />Viewing your website's RSS feed does not require the user to login. Thus your RSS feed is publicly accessible if it is enabled. You may disable or limit the RSS feed above.
								</td>
			
							</tr>
							
						</tbody>
					</table>
					
					
					<table id="members_settings" class="widefat" style="margin-top: 15px; <?php if( $options['member_lockdown'] != "members_area" ) echo 'display: none;'; ?>" cellspacing="0">
						<thead>
							<tr class="thead">
								<th scope="col" style="width: 100px;" colspan="2">Members Area Settings</th>
								<th scope="col">Setting Description:</th>
							</tr>
						</thead>

						<tbody id="users" class="list:user user-list">
							<tr>
								<td colspan="3">
									<p>Enabling <code>Members Area</code> means that your site will be accessible to visitors as normal, except the members page that you specify below. The members page (and <em>all</em> subpages) will be
									   accessible to logged in users only. User moderation is also enabled, meaning you must approve user accounts before they will be activated.</p>
								</td>
							</tr>
		
							<tr>
								<th style="width: 150px;">Members Only Page:</th>
								<td><input type="text" name="members_only_page" id="members_only_page" style="width: 58px;" value="<?php echo isset($options['members_only_page']) ? $options['members_only_page'] : ''; ?>" /></td>
								<td>Enter the ID of your main members only page <code>Eg: 42</code> This page and all child pages will be accessible only to logged in members.</td>
							</tr>
							
						</tbody>
					</table>
										
					<script type="text/javascript">
						jQuery(document).ready(function($){
				          $(".radio_class").click(function(){
				          	if($(this).val()==="lockdown"){
          						$("#members_settings").fadeOut(100);
          						$("#members_off").fadeOut(100);
				          		$("#lockdown_settings").fadeIn(1000);
				          		$("#general_settings").fadeIn(1000);
				          	}else if($(this).val()==="members_area"){
          						$("#lockdown_settings").fadeOut(100); 
          						$("#members_off").fadeOut(100);        		
          						$("#members_settings").fadeIn(1000);
				          		$("#general_settings").fadeIn(1000);
          					}else{
          						$("#lockdown_settings").fadeOut(100);         		
          						$("#members_settings").fadeOut(100);
				          		$("#general_settings").fadeOut(100);
          						$("#members_off").fadeIn(1000);
          					}
				          });
					     })
					</script>
				
					<br clear="all" />
					
					<table class="widefat" cellspacing="0" id="general_settings" <?php if( $options['member_lockdown'] == 'off' ) echo "style='display: none;'"; ?> >
						<thead>
							<tr class="thead">
								<th scope="col" style="width: 100px;" colspan="2" class="" style="">General Settings</th>
								<th scope="col">Setting Description:</th>
							</tr>
						</thead>

						<tbody id="users" class="list:user user-list">
							<tr>
								<th>Redirect Non-logged in Users To:</th>
								<td style="padding-top: 2.5%;"><input type="text" name="redirect_page" id="redirect_page" style="width: 28px;" value="<?php echo isset($options['redirect_page']) ? $options['redirect_page'] : ''; ?>" /></td>
								<td>By default, non-logged in users will be redirected to the login form. Alternatively, you can enter a page ID here that you want non-logged in users to be redirected to instead.</td>
							</tr>

							<tr>
								<th style="padding-top: 1%;">Block Admin Access:</th>
								<td style="padding-top: 3%;"><input type="checkbox" name="admin_block" value="yes" <?php if (isset($options['admin_block']) && $options['admin_block'] == "yes") echo " checked "; ?> /> Yes</td>
								<td>This blocks subscribers from viewing any administrative pages, such as their wp-admin profile page or the dashboard. If they try to access an administrative page they will be redirected to the homepage.</td>
							</tr>
							
						</tbody>
					</table>

					<br clear="all" />

					<table class="widefat" cellspacing="0">

						<thead>
							<tr class="thead">
								<th scope="col" style="width: 100px;" colspan="2" class="" style="">Message Settings</th>
								<th scope="col"></th>
							</tr>
						</thead>

						<tbody id="users" class="list:user user-list">
							<tr>
								<td colspan="3">
									<p>You may use the following variables in your emails: %username%, %name%, %blogname%, %blogurl%, %approval_url%, %login_url%. You are not able to send the users password in clear text.</p>
								</td>
							</tr>
							<tr>
								<th>Profile Edit Page</th>
								<td style="padding-top: 2%;"> <input type="text" size="10" name="profile_page" id="profile_page" value="<?php echo isset($options['profile_page']) ? $options['profile_page'] : ''; ?>" />
								<td> If you've created a page for the user to edit their profile, enter its ID here <code>(eg: 42)</code>. If a user uses the password recovery tool, they will be given a temporary password with a link to this page to change it. <em>Tip:</em> Use the <code>[profilepage]</code> shortcode to create a profile page.</td>
							</tr>
							
							<tr valign="top">
							   	<th style="width: 150px; padding-top: 2%;">Pending Welcome Message: <br /><br /><span style="font-weight: lighter; font-size: 10px;">This message is sent to the user immediately after they register & prior to approval.</span></th>
								<td colspan="2"> 
									Email Subject:<br />
									<input type="text" name="pending_welcome_email_subject" id="pending_welcome_email_subject" value="<?php echo stripslashes($options['pending_welcome_email_subject']); ?>" style="width: 100%;" /><br />
									Email Message:<br />
									<textarea name="pending_welcome_message" id="pending_welcome_message" style="width: 100%;" rows="5"><?php echo stripslashes($options['pending_welcome_message']); ?></textarea>
								</td>
							</tr>
		
							<tr valign="top">
							   	<th style="width: 150px; padding-top: 2%;">Account Approval Message: <br /><br /><span style="font-weight: lighter; font-size: 10px;">This message is sent to the user immediately after their account has been approved.</span></th>
								<td colspan="2"> 
									Email Subject:<br />
									<input type="text" name="account_approval_email_subject" id="account_approval_email_subject" value="<?php echo stripslashes($options['account_approval_email_subject']); ?>" style="width: 100%;" /><br />
									Email Message:<br />
									<textarea name="account_approval_message" id="account_approval_message" style="width: 100%;" rows="5"><?php echo stripslashes($options['account_approval_message']); ?></textarea>
								</td>
							</tr>
		
							<tr valign="top">
							   	<th style="width: 150px; padding-top: 2%;">Admin Notification Message: <br /><br /><span style="font-weight: lighter; font-size: 10px;">This message is sent to the administrator after a new registration is waiting approval.</span></th>
								<td colspan="2">
									Email Subject:<br />
									<input type="text" name="admin_approval_email_subject" id="admin_approval_email_subject" value="<?php echo stripslashes($options['admin_approval_email_subject']); ?>" style="width: 100%;" /><br />
									Email Message:<br /> 
									<textarea name="admin_approval_message" id="admin_approval_message" style="width: 100%;" rows="5"><?php echo stripslashes($options['admin_approval_message']); ?></textarea>
								</td>
							</tr>
						</tbody>	
					</table>

				<div class="clear"></div>	

				<div class="submit"><input type="submit" name="update_options" value="Update Settings"  style="font-weight:bold;" /> </div>
<?php endif; ?>
			</div>


			<div style="float: left; width: 30%; margin: 5px; ">
			
				<table name="pl_donate" class="widefat fixed" style="margin-bottom: 10px;" cellspacing="0">
					<thead>
						<tr class="thead">
							<th scope="col" style="width: 100px;"><img style="margin-top: -5px; margin-right: 3px; float: left;" src="<?php echo ABSPRIVACY_URL; ?>/img/LinkBack.png" alt="" />How To Support This Plugin</th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td>
								<ul style="font-size: 1.0em;">
									<li><a href="http://www.wordpress.org/extend/plugins/absolute-privacy/" title="Rate">Rate this plugin on WP.org</a></li>					
								</ul>
							</td>
						</tr>
					</tbody>
				</table>
			<!-- #pl_donate -->

				<table name="pl_help" class="widefat fixed" style="margin-bottom: 10px;" cellspacing="0">
					<thead>
						<tr class="thead">
							<th scope="col" class="" style=""><img style="margin-top: -5px; margin-right: 3px; float: left;" src="<?php echo ABSPRIVACY_URL; ?>/img/help.png" alt="" /> Plugin Help</th>
						</tr>
					</thead>

					<tbody>	
						<tr>
							<td>
								<ul style="font-size: 1.0em;">
									<li><a href="http://www.johnkolbert.com/portfolio/wp-plugins/absolute-privacy/" title="Go to Plugin Homepage">Plugin Homepage</a></li>
								</ul>
							</td>
						</tr>
					</tbody>
				</table>	
				<!-- #pl_help -->

				<table name="pl_author" class="widefat fixed" cellspacing="0">

					<thead>
						<tr class="thead">
							<th scope="col" style="width: 100px;" class="" style=""><img style="margin-top: -5px; margin-right: 3px; float: left;" src="<?php echo ABSPRIVACY_URL; ?>/img/info.png" alt="" />Plugin Author</th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td>
								<p style="text-align: center; font-size: 1.2em;">Plugin created by <a href="http://www.johnkolbert.com/" title="John Kolbert">John Kolbert</a><br />
									<span style="font-size: 0.8em;">Need Help? <a href="http://www.mammothapps.com/contact/" title="Hire Me">Hire me.</a><br />
									<a href="http://www.twitter.com/johnkolbert" title="Follow Me!">Follow me on Twitter!</a><br /></span>
								</p>
								<p style="text-align: center; font-size: 1.2em;">Plugin maintained by <a href="http://www.eamann.com/" title="Eric Mann">Eric Mann</a><br />
									<span style="font-size: 0.8em;"><a href="http://www.twitter.com/ericmann" title="Follow Me!">Follow me on Twitter!</a><br /></span>
								</p>
							</td>
						</tr>
					</tbody>
				</table>	<!-- #pl_author -->
				
			</div>
			</form>
		</div>
	</div>
<?php
    		
}

/**
 * abpr_moderateMenu function.
 *
 * installes the "Moderate Users" page, which displays all users currently not approved on the blog
 * 
 * @return void
 */
function abpr_moderateMenu() { 
	if ( current_user_can( 'manage_options' ) ) 
		add_submenu_page( 'users.php', 'Moderate Users', 'Moderate Users', 'edit_themes', basename(__FILE__), 'abpr_moderateUsers' );
}

/**
 * abpr_moderateUsers function.
 *
 * Allows for management of unapproved users. Called by
 * abpr_moderateMeni
 * 
 * @return void
 */
function abpr_moderateUsers(){
    global $wpdb, $current_user;
    $options = get_option( ABSPRIVACY_OPTIONS );
    
    if ( !current_user_can( 'manage_options' ) ) 
    	wp_die( 'You are not able to do that' );
    
    if ( isset( $_GET[ 'u_id' ] ) && !empty( $_GET[ 'u_id' ] ) ) {	//we're querying just one user
    	$unapproved[0] = $_GET[ 'u_id' ];	//make this an array to satisfy the foreach() further down
    	
    	$check_user = get_userdata( $unapproved[0] );
		$cap = $wpdb->prefix . "capabilities";

    	if ( !$check_user ) {
    		echo '<div class="error"><p>No such user exists. Check ID and try again.</p></div>';
			return;
    	} elseif ( !array_key_exists( ABSPRIVACY_ROLEREF, $check_user->$cap ) ) {
    		echo '<div class="updated"><p>This user has already been approved.</p></div>';
    		return;
    	}
    	    	
    } else {	// otherwise get all unapproved users

   		$query = "SELECT user_id FROM " . $wpdb->usermeta . " WHERE meta_key = '" . $wpdb->prefix . 'capabilities' . "' AND meta_value LIKE '%" . ABSPRIVACY_ROLEREF . "%';";
	    $unapproved = $wpdb->get_col( $query );
    }
    
    if ( isset( $_POST[ 'update_options' ] ) ) {
    
    	if ( $_POST[ 'update_options' ] == "Delete Selected Users" ) {
    		foreach( $_POST['users'] as $user ) {
    			if ( !current_user_can( 'delete_user', $user ) ) {
    				wp_die( 'You can&#8217;t delete that user.' );
    			}
    			if ( $user == $current_user->ID ) {
    				wp_die( 'You cannot delete yourself.' );
    			}
    	
    			wp_delete_user( $user );
    		}
    	// Show a message to say we've done something
    	echo '<div class="updated"><p>' . __('User(s) deleted') . '</p></div>';
    	return;
    	}
    	
    	if ( $_POST[ 'update_options' ] == "Approve Selected Users" ){
    		foreach( $_POST[ 'users' ] as $user ){
    			$user = get_userdata( $user );
    			$user_role = new WP_User( $user->ID );

    			$user_role->set_role( get_option('default_role') );
    			
    			abpr_handleEmail( $user->ID, $type= 'account_approved' );
    			
    		}
    			// Show a message to say we've done something
    			echo '<div class="updated"><p>' . __('User(s) Approved. Notifications sent via email.') . '</p></div>';
    			return;
    	}
    }

    $output = '<div class="wrap">
        		<h2>Absolute Privacy: Moderate Users</h2>
    			<form method="post" action="">
    			<table id="tablo" class="widefat fixed" cellspacing="0">
    		<p id="tablo_para">The following users have registered but not been approved to login.</p>

    		<thead>
    		<tr class="thead">
    		<th scope="col"  class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
    		<th scope="col" id="cb" class="manage-column column-cb check-column" style="">ID</th>
    		<th scope="col" id="username" class="manage-column column-username" style="">Username</th>
    		<th scope="col" id="name" class="manage-column column-name" style="">Name</th>
    		<th scope="col" id="email" class="manage-column column-email" style="">E-mail</th>
    		<th scope="col" id="role" class="manage-column column-role" style="">Status</th>
    		<th scope="col" id="role" class="manage-column column-role" style="">Registration Date</th>
    		</tr>
    		</thead>

    		<tfoot>
    		<tr class="thead">
    		<th scope="col"  class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
    		<th scope="col" id="cb" class="manage-column column-cb check-column" style="">ID</th>
    		<th scope="col" id="username" class="manage-column column-username" style="">Username</th>
    		<th scope="col" id="name" class="manage-column column-name" style="">Name</th>
    		<th scope="col" id="email" class="manage-column column-email" style="">E-mail</th>
    		<th scope="col" id="role" class="manage-column column-role" style="">Status</th>
    		<th scope="col" id="role" class="manage-column column-role" style="">Registration Date</th>
    	</tr>
    	</tfoot>
    	<tbody id="users" class="list:user user-list">';
    
    echo $output;

    $i=0;
    $state="class='alternate'";

    foreach( $unapproved as $user_id ) {
    	$user = get_userdata( $user_id );
    	$i++;

    	echo "<tr id='user-$i' $state>
    	  <th scope='row' class='check-column'><input type='checkbox' name='users[]' id='$user_id' class='administrator' value='$user_id' /></th>
    	  <th scope='row' class='check-column'>$user_id</th>
    	  <td class='username column-username'><strong><a href='user-edit.php?user_id=$user_id'>{$user->user_login}</a></strong></td>
    	  <td class='name column-name'>{$user->user_firstname} {$user->user_lastname} </td><td class='email column-email'><a href='mailto:{$user->user_email}' title='e-mail: {$user->user_email}'>{$user->user_email}</a></td>
    	  <td class='role column-role'>" . ABSPRIVACY_ROLENAME . "</td>
    	  <td class='column-name'>{$user->user_registered}</td>
    	  </tr>";
    	
    	if($state == "class='alternate'"){ $state = ''; continue;}
    	if($state == ''){ $state = "class='alternate'"; continue;}
     }

    if ( $i == 0 ) { echo "</table><script type='text/javascript'>document.getElementById('tablo').style.display = 'none'; document.getElementById('tablo_para').style.display = 'none';</script><p><strong>No users are waiting moderation</strong></p>"; 
    	echo "</table></form></div>"; 
    	return; 
    	}

    $output = '</table>
    			<p style="margin-bottom: 0px;">Approved users will receive an email notification of their approval.</p>
    			<div class="submit" style="float: left;"><input type="submit" name="update_options" value="Delete Selected Users"  onClick="return confirm(\'Really Delete? (This cannot be undone)\')" style="font-weight:bold; color: red; float: left;" /> </div>
    			<div class="submit" style="float: left;"><input type="submit" name="update_options" value="Approve Selected Users"  style="font-weight:bold; float: left;" /> </div>
    			</form>';
    echo $output;
}


/**
 * abpr_handleEmail function.
 *
 * handles email notifications
 * 
 * $user_id:	the integer ID of the user being acted upon (newly registered, approved, etc)
 * $type:		pending_welcome, account_approve, or admin_notification
 * @return void
 */
function abpr_handleEmail( $user_id, $type ){

	$options = get_option( ABSPRIVACY_OPTIONS );
	$user = get_userdata( $user_id ); //object with user info

	switch( $type ){
	    case( 'pending_welcome' ):
	    	$to_email = $user->user_email;
	    	$subject = $options[ 'pending_welcome_email_subject' ];
	    	$message = $options[ 'pending_welcome_message' ];
	    	break;
	
	    case( 'account_approved' ):
	    	$to_email = $user->user_email;
	    	$subject = $options[ 'account_approval_email_subject' ];
	    	$message = $options[ 'account_approval_message' ];
	    	break;
	
	    case( 'admin_notification' ):
	    	$to_email = get_bloginfo( 'admin_email' );
	    	$subject = $options[ 'admin_approval_email_subject' ];
	    	$message = $options[ 'admin_approval_message' ];
	    	break;
	    	
	    default :	//an invalid response has been given
	    	return false;
	}
	
	$login_url = ( isset( $options[ 'redirect_page' ] ) && $options[ 'redirect_page' ] != '' ) ? get_permalink( $options[ 'redirect_page' ] ) : wp_login_url();
	
	$replace = array('%username%' 	  => $user->user_login,
	    			 '%name%' 		  => $user->display_name,
	    			 '%blogname%' 	  => get_bloginfo( 'name' ),
	    			 '%blogurl%'	  => get_bloginfo( 'url' ),
	    			 '%approval_url%' => get_bloginfo( 'url' ) . '/wp-admin/users.php?page=functions.php&u_id=' . $user_id,
	    			 '%login_url%'	  => $login_url
	    		);
	    		 
	$email_body = strtr( stripslashes( $message ), $replace ); //get email body and replace variables
	
	$headers = "MIME-Version: 1.0\n" .
	    	   "From: " . get_option( 'blogname' ) . " <" . get_option( 'admin_email' ) . ">";   
	
	wp_mail( $to_email, $subject, $email_body, $headers );
	
}


/**
 * abpr_check_is_feed function.
 *
 * handles filtering the content based on the value the user selected on the options page.
 * Only runs if user has enabled "Lockdown Mode"
 * 
 * $content:	The post content passed from the action
 * @return $content
 */
function abpr_check_is_feed( $content ){
	$options = get_option( ABSPRIVACY_OPTIONS );
	if ( $options[ 'member_lockdown' ] == "lockdown" && is_feed() ) :
		switch( $options[ 'rss_control' ] ) {
			case "on":
				//allow full RSS
				break;
			case "headline":
				$content = '';
				break;
			case "excerpt":
				$content = substr( strip_tags( get_the_content() ), 0, $options[ 'rss_characters' ] ) . "...";
				break;		
		}
	endif;
		
	return $content;
}



/**
 * abpr_lockDown function.
 *
 * Checks if plugin is enabled, on lockdown mode, or in member area mode
 * and restricts non-logged in users accordingly.
 *
 * @return void
 */
function abpr_lockDown(){

    $options = get_option( ABSPRIVACY_OPTIONS );
        			
  	if ( $options[ 'member_lockdown' ] == 'off' || is_user_logged_in() ){
  		return; //plugin is activated but disabled or user is logged in
  	} elseif ( $options[ 'member_lockdown' ] == "lockdown" ){
    
        if ( is_feed() && $options[ 'rss_control' ] != "off" ) return; //allow RSS feed to be handled by check_is_feed() function unless the RSS feed is disabled.

    	if ( isset( $options[ 'allowed_pages' ] ) && $options[ 'allowed_pages' ] != '' ){
    		$allowed_pages = explode( ',', $options[ 'allowed_pages' ] );
    		
    		if ( in_array( 0, $allowed_pages ) && is_front_page() )
    			return;
    		
    		if ( is_page( $allowed_pages ) || is_single( $allowed_pages ) ) 
    			return;  //let them visit the allowed pages
       	}
       	
       	$http = ( !empty( $_SERVER[ 'HTTPS' ] ) && strtolower( $_SERVER[ 'HTTPS' ] != 'off' ) ) ? 'https://' : 'http://'; 		//Thanks to Brian L. for this fix
    	$original_request = $http . $_SERVER[ 'SERVER_NAME' ] . $_SERVER[ 'REQUEST_URI' ];	//this is where the user was trying to go

    
    	if ( isset( $options[ 'redirect_page' ] ) && $options[ 'redirect_page' ] != '' ){	//redirect page setting has been set
    	
    		if ( is_single( $options[ 'redirect_page' ] ) || is_page( $options[ 'redirect_page' ] ) ) 
    			return; //end the function if the visitor is already on the redirect_page page
    		
    		$redirect_url = get_permalink( $options[ 'redirect_page' ] );
    		$url = $redirect_url . '?req=' . urlencode( $original_request );
    		
    	} else {

			$url = wp_login_url( $original_request ); 
    	}

    	wp_redirect( $url, 302 );
    	exit();	
   
    } elseif ( $options[ 'member_lockdown' ] == 'members_area' ) {
    
    	if ( abpr_is_members_page() ) {
    	
           	$http = ( !empty( $_SERVER[ 'HTTPS' ] ) && strtolower( $_SERVER[ 'HTTPS' ] != 'off' ) ) ? 'https://' : 'http://'; //Thanks to Brian L. for this fix
	    	$original_request = $http . $_SERVER[ 'SERVER_NAME' ] . $_SERVER[ 'REQUEST_URI' ];	//this is where the user was trying to go

    		if ( isset( $options[ 'redirect_page' ] ) && $options[ 'redirect_page' ] != '' ) {
    	
    			if ( is_single( $options[ 'redirect_page' ] ) || is_page( $options[ 'redirect_page' ] ) ) 
    				return; //end the function if the visitor is already on the redirect_page page
    		
	    		$redirect_url = get_permalink( $options[ 'redirect_page' ] );
    			$url = $redirect_url . '?req=' . urlencode( $original_request );
    			
	    	} else {
       			$url = wp_login_url( $original_request ); 
	    	}

    		wp_redirect( $url, 302 );	//send them there
    		exit();	
    	} else {
    		return; //not a members page, so let it go
    	}
   }
    
    return;
}

/**
 * abpr_adminLockDown function.
 *
 * Blocks subscribers from their admin profile page if enabled
 * in the plugin settings
 *
 * @return void
 */
function abpr_adminLockDown(){
    global $userdata, $wpdb;
    
    $options= get_option( ABSPRIVACY_OPTIONS );

    if ( !is_admin() || !( is_user_logged_in() ) || ( isset( $options[ 'member_lockdown' ] ) && $options[ 'member_lockdown' ] == 'off' ) ) return;
    	//if it's not an admin page or the user isn't logged in at all, we don't need this
    
    $user_role = new WP_User( $userdata->ID );
    $capabilities = $wpdb->prefix . 'capabilities';
     
    if ( isset( $options[ 'admin_block'] ) && $options[ 'admin_block' ] == "yes" && array_key_exists( 'subscriber', $user_role->$capabilities ) ) {
 		$url = get_bloginfo( 'url' ); 
    	wp_redirect( $url, 302 );
    	exit();	
    }		
}

/**
 * abpr_regCSS function.
 *
 * Adds CSS for registration form 
 *
 * @return void
 */
function abpr_regCSS(){
    echo '<style type="text/css">
    #invite_code, #first_name, #last_name, #pswd1, #pswd2{
    	font-size: 24px;
    	width: 97%;
    	padding: 3px;
    	margin-top: 2px;
    	margin-right: 6px;
    	margin-bottom: 16px;
    	border: 1px solid #e5e5e5;
    	background: #fbfbfb;
    }
    #reg_passmail{
    	display:none;
    }
    </style>';
}


/**
 * abpr_registrationBox function.
 *
 * Echos input boxes for first name, last name, and password to 
 * the registration box. 
 *
 * Todo: allow users to add custom boxes via filter/action
 * @return void
 */
function abpr_registrationBox(){ 

    $output = '<p><label>First Name:<br />
    			<input type="text" name="first_name" id="first_name" class="input" value="' . ( isset( $_POST[ 'first_name' ] ) ? esc_attr( stripslashes( $_POST[ 'first_name' ] ) ) : '' ) . '" size="25" tabindex="70" /></label></p>
    			<p><label>Last Name:<br />
    			<input type="text" name="last_name" id="last_name" class="input" value="' . ( isset( $_POST[ 'last_name' ] ) ? esc_attr( stripslashes( $_POST[ 'last_name' ] ) ) : '' ) . '" size="25" tabindex="80" /></label></p>
    		
    			<p><label>Password:<br />
    			<input type="password" name="pswd1" id="pswd1" class="input" size="25" tabindex="91"/></label></p>
    			<p><label>Repeat Password:<br />
    			<input type="password" name="pswd2" id="pswd2" class="input" size="25" tabindex="92" /></label></p>';
    
    $output .= "\n" . '<p class="message register" style="margin-bottom: 8px;">Your account must be approved before you will be able to log in. You will be emailed once it is approved.</p>';

   // echo apply_filter( 'abpr_regbox', $output );
    
   // do_action( 'abpor_add_regbox' );
   
   echo $output;
}

/**
 * abpr_checkRegErrors function.
 *
 * Adds error checks to registration form 
 *
 * $errors:	contains other errors passed to the function
 * @return $errors
 */
function abpr_checkRegErrors( $errors ){

    if ( empty( $_POST[ 'first_name' ] ) || empty( $_POST[ 'last_name' ] ) ) {
    	$errors->add( 'name', __('<strong>ERROR</strong>: You must enter a first and last name') );
    }
    if ( empty( $_POST[ 'pswd1' ] ) || empty( $_POST[ 'pswd2' ] ) || $_POST[ 'pswd1' ] == '' || $_POST[ 'pswd2' ] == '' ) {
    	$errors->add( 'password', __('<strong>ERROR</strong>: Please enter a password in both password boxes.') );
    } elseif ( $_POST[ 'pswd1' ] != $_POST[ 'pswd2' ] ) {
    	$errors->add( 'password', __('<strong>ERROR</strong>: Passwords do not match.') );
    }
    			
    return $errors;
}

/**
 * abpr_addNewUser function.
 *
 * Adds new registrants name and password to the database 
 *
 * $user_id:	the integer ID of the newly added user
 * @return void
 */
function abpr_addNewUser( $user_id ){

    update_user_meta( $user_id, 'first_name', esc_attr( stripslashes( $_POST[ 'first_name' ] ) ) );
    update_user_meta( $user_id, 'last_name', esc_attr( stripslashes( $_POST[ 'last_name' ] ) ) );

    $user_role = new WP_User( $user_id );
    $user_role->set_role( ABSPRIVACY_ROLEREF );  //for some reason this role isn't being set. Need to look into it
    	
    if ( !empty( $_POST[ 'pswd1' ] ) ) {
    	$_POST[ 'pswd1' ] = wp_set_password( esc_attr( stripslashes( $_POST[ 'pswd1' ] ) ), $user_id );
    }
    
    unset( $_POST[ 'pswd1' ] );
    unset( $_POST[ 'pswd2' ] );
}

/**
 * abpr_add_error_code function.
 *
 * Adds 'unapproved' $wp_error to the list of shake codes for the login box
 *
 * $shake_codes:	other shake error codes passed to the function
 * @return $shake_codes
 */
function abpr_add_error_code( $shake_codes ){
	$shake_codes[] = 'unapproved';
	
	return $shake_codes;

}

/**
 * abpr_authenticateUser function.
 *
 * Adds additional authentication when logging in. Checks that the
 * user trying to log in isn't an 'Unapproved User'
 *
 * $user:		NULL
 * $username:	username of attempted login
 * $password:	password of attempted login
 * @return $user
 */
function abpr_authenticateUser( $user, $username, $password ){
	global $wpdb;

	$tempUser = get_user_by( 'login', $username );

	$cap = $wpdb->prefix . "capabilities";
	if ( $tempUser && array_key_exists( ABSPRIVACY_ROLEREF, $tempUser->$cap ) ) {  //if the user's role is listed as "unapproved"
		$user = new WP_Error( 'unapproved', __("<strong>ERROR</strong>: The administrator of this site must approve your account before you can login. You will be notified via email when it has been approved.") );
		add_filter( 'shake_error_codes', 'abpr_add_error_code' );	//make the login box shake
		remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );	//prevent authentication of user
	}
	
	return $user;
}

/**
 * abpr_profileRecoveryLink function.
 *
 * If the profile page has been set in the options, this
 * adds a link in the password recovery email to allow the
 * user to change their password.
 *
 * $message:	The original password recovery message
 * $key:		The users unique key. Not used in this function
 * @return bool
 */

function abpr_profileRecoveryLink( $message, $key ){

	$options = get_option( ABSPRIVACY_OPTIONS );
	
	$message = "Here is your temporary password for " . get_option('blogname') . "\n \n" . $message;
	
	if ( isset($options[ 'profile_page' ] ) && $options[ 'profile_page' ] != '' ){
	
		$message .= "\n \n After logging in, you may change this temporary password here: " . get_permalink( $options['profile_page'] );
	}
		
	return $message;

}

/**
 * abpr_is_ancestor function.
 *
 * Checks if the given $post_id is an ancestor of the currently
 * queried post
 *
 * Thanks to http://www.kevinleary.net/wordpress-is_child-for-advanced-navigation/ for this
 * $post_id:	ID of post/page to check
 * @return bool
 */
function abpr_is_ancestor( $post_id ) {
	global $wp_query;
	
	$ancestors = $wp_query->post->ancestors;

	if ( in_array( $post_id, $ancestors ) ) {
		$return = true;
	} else {
		$return = false;
	}
	
	return $return;
}

/**
 * abpr_is_members_page function.
 *
 * Checks if the current page is the members page or a subpage of it. Calls abpr_is_ancestor()
 *
 * @return bool
 */
function abpr_is_members_page(){
	global $wpdb;
	
	$options = get_option( ABSPRIVACY_OPTIONS );
	$members_page =  $options[ 'members_only_page' ];
	
	if ( is_single( $members_page ) || is_page( $members_page ) ) {
		$return = true;
	} elseif ( is_page() && abpr_is_ancestor( $members_page ) ) {
		$return = true;
	} else {
		$return = false;
	}
	
	return $return; //true = is member page; false = not member page
}

/**
 * wp_new_user_notification function
 *
 * Overwrites wp_new_user_notification() function found in pluggable.php
 * Handles emails when a new user registers.
 *
 * @return void
 */
if ( !function_exists( 'wp_new_user_notification' ) ) {
 function wp_new_user_notification( $user_id, $plaintext_pass = '' ) {

	$user = get_userdata( $user_id ); //object with user info
			
	abpr_handleEmail( $user_id, $type='admin_notification' );  //send admin email
    
	if ( empty( $plaintext_pass ) )
		return;
		
	abpr_handleEmail( $user_id, $type = 'pending_welcome' ); //send new user pending message email

 }
}

/**
 * abpr_loginShortcode function.
 *
 * Handles the [loginform] shortcode. This displays a login form
 * via wp_login_form() if the user is not logged in. Otherwise it 
 * displays the useraname and a logout link.
 *
 * The shortcode takes the standard inputs of wp_login_form()
 */
function abpr_loginShortcode( $atts ){
	global $userdata;
	
	extract( shortcode_atts(array(
		'redirect' => NULL,
		'form_id' => 'loginform',
        'label_username' => 'Username',
        'label_password' => 'Password',
        'label_remember' => 'Remember Me',
        'label_log_in' => 'Log In',
        'id_username' => 'user_login',
        'id_password' => 'user_pass',
        'id_remember' => 'rememberme',
        'id_submit' => 'wp-submit',
        'remember' => true,
        'value_username' => '' ,
        'value_remember' => false,
        'loggedin_id' => 'logged-in',
        'logout_url' => home_url(),
        'lostpassword' => NULL
	), $atts ) );
	
	switch( $redirect ){
	
		case NULL :
			if( isset( $options[ 'redirect_page' ] ) && $options[ 'redirect_page' ] != '' ) {
				$redirect_to = get_permalink( $options[ 'redirect_page' ] );
				break;
			}else{
				$redirect = 'same';			
			}
	
		case 'same' :
      	 	$http = ( !empty( $_SERVER[ 'HTTPS' ] ) && strtolower( $_SERVER[ 'HTTPS' ] != 'off' ) ) ? 'https://' : 'http://';
    		$redirect_to = $http . $_SERVER[ 'SERVER_NAME' ] . $_SERVER[ 'REQUEST_URI' ];	//this is where the user was trying to go
			break;
		
		case 'home' :
			$redirect_to = home_url();
			break;
		
		default :
			$redirect_to = $redirect;
	}
			
	$redirect_to = ( empty($_GET['req']) ? $redirect_to : $_GET['req'] );	//a get request trumps the user input for now

	if ( is_user_logged_in() ) {
		echo '<p id="' . $loggedin_id . '"> You are currently logged in as ' . $userdata->user_login . '. <a href="' . wp_logout_url( $logout_url ) .'" title="Logout">Log out?</a></p>';
	} else {	
	
		$return = wp_login_form( array( 
									'echo' => false, 
									'redirect' => $redirect_to,
       								'label_username' => $label_username,
        							'label_password' => $label_password,
        							'label_remember' => $label_remember,
        							'label_log_in' => $label_log_in,
       								'id_username' => $id_username,
        							'id_password' => $id_password,
        							'id_remember' => $id_remember,
	        						'id_submit' => $id_submit,
    	    						'remember' => $remember,
       								'value_username' => $value_username,
        							'value_remember' => $value_remember
									) 
								);	
	}
	
	return $return;
}

/**
 * abpr_profileShortcode function.
 *
 * Handles the [profilepage] shortcode. This displays a login form
 * via wp_login_form() if the user is not logged in. Otherwise it 
 * displays the useraname and a logout link, and a form where the user
 * can chagne their name, email, and password. The code is found in
 * profile_page.php
 *
 */
function abpr_profileShortcode(){

	include( ABSPRIVACY_PATH . '/profile_page.php' );

}

function abpr_needsUpgrade(){
	
	$db_version = get_option( ABSPRIVACY_DBOPTION );
	$options = get_option( ABSPRIVACY_OPTIONS );
	
	if ( !$db_version || $db_version < ABSPRIVACY_DBVERSION || !$options ) {
		return true;
	}
	
	return false;
}

function abpr_adminnotice(){
	echo '<div class="error"><p>Absolute Privacy database update needed. Your site may not be protected until you update. <a href="'.admin_url() . 'wp-admin/options-general.php?page=absolute-privacy/functions.php'.'">More information</a></p></div>';
}


/**
 *	abpr_doUpgrade function
 *
 *	Runs when plugin is first activated or if a database/settings update
 *	is needed. Handles
 *
 *	@return void
*/
function abpr_doUpgrade(){

	global $wp_roles;
	
	/* First lets make sure the absolute privacy role is set */
	$role = get_role( ABSPRIVACY_ROLEREF );
	if ( !$role ) add_role( ABSPRIVACY_ROLEREF, ABSPRIVACY_ROLENAME );  //create the unapproved role

	$options = get_option( ABSPRIVACY_OPTIONS );
	
	if ( !$options ) {		// no options set so set default
	
		$legacy_options = get_option( 'absolute_privacy' );	// options term used prior to 2.0
		
		if ( $legacy_options ) {	// user is upgrading from legacy version
			$options[ 'member_lockdown' ] = ( $legacy_options[ 'members_enabled' ] == 'yes' ) ? 'lockdown' : 'off';
			$options[ 'allowed_pages' ] = $legacy_options[ 'allowed_pages' ];
			$options[ 'pending_welcome_email_subject' ] = $legacy_options[ 'pending_welcome_email_subject' ];
			$options[ 'pending_welcome_message' ] = $legacy_options[ 'pending_welcome_message' ];
			$options[ 'account_approval_email_subject' ] = $legacy_options[ 'account_approval_email_subject' ];
			$options[ 'account_approval_message' ] = $legacy_options[ 'account_approval_message' ];
			$options[ 'admin_approval_email_subject' ] = $legacy_options[ 'admin_approval_email_subject'];
			$options[ 'admin_approval_message' ] = $legacy_options[ 'admin_approval_message' ];
			$options[ 'redirect_page' ] = $legacy_options[ 'redirect_page' ];
			$options[ 'admin_block' ] = $legacy_options[ 'admin_block' ];
			$options['rss_control'] = $legacy_options[ 'rss_control' ];
	   		$options['rss_characters'] = $legacy_options[ 'rss_characters' ];

			
			delete_option( 'absolute_privacy' );	// delete legacy options from database
			delete_option( 'absolute_privacy_default' );
			
			/* prior to 2.0 Absolute Privacy changed the default role. 2.0+ no longer does this
			 * so we need to change the default role back. For now we'll just change this to subscriber
			 */
			$default_role = get_option( 'default_role' );
			if ( $default_role == 'unapproved' ){
				update_option( 'default_role', 'subscriber' );
			}
			
		} else {	// user must be installing fresh since no options were found	
	
			$options[ 'member_lockdown' ] = 'off';
			$options[ 'rss_control' ] = 'off';
			$options[ 'pending_welcome_email_subject' ] = 'Your account with ' . stripslashes( get_option( 'blogname' ) ) . ' is under review';
			$options[ 'pending_welcome_message' ] = "Hi %name%, \n \n Thanks for registering for %blogname%! Your registration is currently being reviewed. You will not be able to login until it has been approved. You will receive an email at that time. Thanks for your patience. \n \n Sincerely, \n \n %blogname%";
			$options[ 'account_approval_email_subject' ] = "Your account has been approved!";
			$options[ 'account_approval_message' ] = "Your registration with %blogname% has been approved! \n \n You may login using the following information: \n Username: %username% \n Password: (hidden) \n URL: %login_url%";
			$options[ 'admin_approval_email_subject' ] = "A new user is waiting approval";
			$options[ 'admin_approval_message' ] = "A new user has registered for %blogname% and is waiting your approval. You may approve or delete them here: %approval_url% \n \n This user cannot log in until you approve them.";
		
		}
		
		update_option( ABSPRIVACY_OPTIONS, $options );		// set option values
		update_option( ABSPRIVACY_DBOPTION, ABSPRIVACY_DBVERSION );
	} else {	// there are $options already in the database
	
		if ( abpr_needsUpgrade() ){
			/* Run options upgrade script here */
			
			// for now lets just enter the DB version
			update_option( ABSPRIVACY_DBOPTION, ABSPRIVACY_DBVERSION );	
		}
	}
}
?>