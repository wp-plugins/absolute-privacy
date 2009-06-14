<?php 
/*
Plugin Name: Absolute Privacy
Plugin URI: http://www.johnkolbert.com/portfolio/wp-plugins/absolute-privacy
Description: Give your blog the most privacy. Forces users to register with their name and to choose a password. Users cannot login until approved by an administrator. Also, gives the option to lock down your site from un-logged in viewers.
Author: John Kolbert
Version: 1.1
Author URI: http://www.johnkolbert.com

Copyright Notice

Copyright 2009 by John Kolbert

Permission is hereby granted, free of charge, to any person obtaining a 
copy of this software and associated documentation files (the "Software"), 
to deal in the Software without restriction, including without limitation 
the rights to use, copy, modify, merge, publish, distribute, sublicense, 
and/or sell copies of the Software, and to permit persons to whom the Software 
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all 
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED AS IS, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR 
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER 
DEALINGS IN THE SOFTWARE.
*/


if (!class_exists("absolutePrivacy")) {
class absolutePrivacy {
	
	//lets declare some variables
	var $capabilities;   //capabilities prefix
	var $role;           //role database name
	var $role_ref;		 //role database entry
	var $rolename;       //role name
	var $options;        //options
	var $default_role;   //default role on install
	
	
	function absolutePrivacy() {  //constructor
		global $wpdb;

		$this->capabilities = $wpdb->prefix . "capabilities";
		$this->role = "unapproved"; 							//do not change this or bad things will happen to good people
		$this->role_ref = "a:1:{s:10:\"unapproved\";b:1;}"; 	//leave it alone
		$this->rolename = "Unapproved User";					//Role name for unapproved users. Change this if you like (will require to deactivate and reactivate the plugin to register)
		$this->options = "absolute_privacy"; 					//name for options array();
		$this->default_role = "absolute_privacy_default"; 		//stores the default role on plugin installation (usually "Subscriber")
	}

	/**
	 * createRole function.
	 * Creates a new role on plugin activation and keeps track of the default role
	 * 
	 * @access public
	 * @return void
	 */
	function createRole(){
		global $wp_roles;
		$default = get_option('default_role');
		
		$role = get_role($this->role);
		if(!$role) { 
			$wp_roles->add_role($this->role, $this->rolename); //create the unapproved role
			$role = get_role($this->role);
			$role->add_cap('level_0');  //give the unaproved role the 0 capability
			update_option($this->default_role, $default); //saves the user's default role preference
			 $this->_changeDefaultRole($enabled="yes");
			return true;
		}
		else return false;
	}
	
	/**
	 * destroyRole function.
	 * Deletes role on plugin deactivation 
	 *
	 * @access public
	 * @return void
	 */
	function destroyRole(){
		global $wp_roles;
			
		$wp_roles->remove_role($this->role);
		$this->_changeDefaultRole($enabled="no");
	}
	

	/**
	 * _changeDefaultRole function.
	 * Changes the default blog role 
	 *
	 * @access private
	 * @param mixed $enabled
	 * @return void
	 */
	function _changeDefaultRole($enabled){
	
		$default = get_option($this->default_role);
		
		if($enabled == "yes"){
			update_option('default_role', $this->role);
		}
		else{
			update_option('default_role', $default); //change back to default
		}
		
	}
	

	/**
	 * registrationBox function.
	 * Echos input boxes for first name, last name, and password to 
	 * the registration box. 
	 *
	 * @access public
	 * @return void
	 */
	function registrationBox(){ 
		$options = get_option($this->options);
		$output = '<p><label>First Name:<br />
					<input type="text" name="first_name" id="first_name" class="input" value="' . attribute_escape(stripslashes($_POST['first_name'])) . '" size="25" tabindex="70" /></label></p>
					<p><label>Last Name:<br />
					<input type="text" name="last_name" id="last_name" class="input" value="' . attribute_escape(stripslashes($_POST['last_name'])) . '" size="25" tabindex="80" /></label></p>
				
					<p><label>Password:<br />
					<input type="password" name="pswd1" id="pswd1" class="input" size="25" tabindex="91"/></label></p>
					<p><label>Repeat Password:<br />
					<input type="password" name="pswd2" id="pswd2" class="input" size="25" tabindex="92" /></label></p>';
		
			$output .= "\n" . '<p class="message register" style="margin-bottom: 8px;">Your account must be approved before you will be able to login. You will be emailed once it is approved.</p>';
	
		echo $output;
	}

	/**
	 * checkRegErrors function.
	 * Adds error checks to registration form 
	 *
	 * @access public
	 * @param mixed $errors
	 * @return void
	 */
	function checkRegErrors($errors){

		if(empty($_POST['pswd1']) || empty($_POST['pswd2']) || $_POST['pswd1'] == '' || $_POST['pswd2'] == ''){
			$errors->add('password', __('<strong>ERROR</strong>: Please enter a password in both password boxes.'));
		}elseif ($_POST['pswd1'] != $_POST['pswd2']){
			$errors->add('password', __('<strong>ERROR</strong>: Passwords do not match.'));}
		if(empty($_POST['first_name']) || empty($_POST['last_name'])){
			$errors->add('name', __('<strong>ERROR</strong>: You must enter a first and last name'));}
					
		return $errors;
	}

	/**
	 * regCSS function.
	 * Adds CSS for registration form 
	 *
	 * @access public
	 * @return void
	 */
	function regCSS(){
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
	 * addNewUser function.
	 * Adds new registrants name and password
	 * to the database 
	 *
	 * @access public
	 * @param mixed $user_id
	 * @return void
	 */
	function addNewUser($user_id){ //adds user meta to the database on registration
		global $wpdb;
		$options = get_option($this->options);
	
		update_usermeta($user_id, 'first_name', attribute_escape(stripslashes($_POST['first_name'])));
		update_usermeta($user_id, 'last_name', attribute_escape(stripslashes($_POST['last_name'])));

								$user_role = new WP_User( $user->ID );
								$user_role->set_role($this->role);
			
		if(!empty($_POST['pswd1'])){
			$_POST['pswd1'] = wp_set_password(attribute_escape(stripslashes($_POST['pswd1'])), $user_id);
		}
		
		$_POST['pswd1'] = '';
		$_POST['pswd2'] = '';
		unset($_POST['pswd1']);
		unset($_POST['pswd2']);
	}

	/**
	 * installOptionsMenu function.
	 * 
	 * @access public
	 * @return void
	 */
	function installOptionsMenu() {  // install the options menu
		if (function_exists('current_user_can')) {
			if (!current_user_can('manage_options')) return;
		} else {
			global $user_level;
			get_currentuserinfo();
			if ($user_level < 10) return;
		}
		if (function_exists('add_options_page')) {
			add_options_page(__('Absolute Privacy'), __('Absolute Privacy'), 1, __FILE__, array(&$this,'optionsPage'));
		}
	} 

	/**
	 * optionsPage function.
	 * Displays the settings page 
	 *
	 * TODO: let user chose default email message on approval/regsitration
	 * @access public
	 * @return void
	 */
	function optionsPage(){
		global $wpdb;

		if (isset($_POST['update_options'])) {
	   		$options['members_enabled'] = trim($_POST['members_enabled'],'{}');
	   		
	   		update_option($this->options, $options);
		
		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('Options saved') . '</p></div>';
		} else {
		
		$options = get_option($this->options);
		}
	 
	
		$output = '<div class="wrap">
					<h2>Absolute Privacy: Options Page</h2>
    				<p>Created by <a href="http://www.johnkolbert.com/">John Kolbert</a><br />
					<form method="post" action="">
		
					<table class="form-table">
		   			<tr valign="top">
				   	<th>Members Only</th>
		   			<td>  <input type="checkbox" name="members_enabled" value="yes"';
		   			
		 if ($options['members_enabled'] == "yes") $output .= " checked ";
		 
		 $output .= '/> If checked users must be logged in to view ANY part of your blog. They will be redirected to 
		 			the page they were looking for after they login<br />This will also protects your site\'s RSS feeds</td>
		   			</tr>
      				</table>
		
					<div class="submit"><input type="submit" name="update_options" value="Update"  style="font-weight:bold;" /> </div>
					</form> 
    				</div>';
    		
		echo $output;
	}


	/**
	 * moderateMenu function.
	 * installes the "Moderate Users" page, which displays all users currently not approved on the blog
	 * @access public
	 * @return void
	 */
	function moderateMenu(){ 
			if (function_exists('current_user_can')) {
				if (!current_user_can('manage_options')) return;
			} else {
				global $user_level;
				get_currentuserinfo();
				if ($user_level < 10) return;
			}

			add_submenu_page('users.php', 'Moderate Users', 'Moderate Users', 'edit_themes', basename(__FILE__), array(&$this,'moderateUsers'));
	}

	/**
	 * moderateUsers function.
	 * handles the moderate users function
	 * 
	 * @access public
	 * @return void
	 */
	function moderateUsers(){
		global $wpdb;
		
		if (function_exists('current_user_can')) {
			if (!current_user_can('manage_options')) wp_die('You are not able to do that');
		} else {
			global $user_level;
			get_currentuserinfo();
			if ($user_level < 10) wp_die('You are not able to do that');
		}

		//get all users who are unapproved
		$query = "SELECT user_id FROM ".$wpdb->usermeta." WHERE meta_key = '" . $this->capabilities . "' AND meta_value = '" . $this->role_ref . "';";
		$unapproved = $wpdb->get_col($query);
		
		if (isset($_POST['update_options'])) {
		
			if ($_POST['update_options'] == "Delete Selected Users"){
				foreach($_POST['users'] as $user){
					if (!current_user_can('delete_user', $user)){
						wp_die(__('You can&#8217;t delete that user.'));
					}
					if($user == $current_user->ID) {
						wp_die('You cannot delete yourself.');
					}
			
					wp_delete_user($user);
				}
			// Show a message to say we've done something
			echo '<div class="updated"><p>' . __('User(s) deleted') . '</p></div>';
			return;
			}
			if ($_POST['update_options'] == "Approve Selected Users"){
				foreach($_POST['users'] as $user){
					$user = get_userdata($user);
					$user_role = new WP_User($user->ID);

					$user_role->set_role("subscriber");
					
					$headers = "MIME-Version: 1.0\n" .
		    				   "From: ". get_option('admin_email');   
					$message = "Dear " . $user->user_firstname .",\n";
					$message .= "Your account with ".get_bloginfo('name')." has been approved. You may login using the following info. \n";
					$message .= "Username: " . $user->user_login . "\n";
					$message .= "Password: (not shown)" . "\n";
					$message .= "URL: " . get_bloginfo('url');
	
					@wp_mail($user->user_email, 'Your Account Has Been Approved', $message, $headers);  //email the user telling them they've been approved
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
 	
 		foreach($unapproved as $user_id){
			$user = get_userdata($user_id);
			$capability = $this->capabilities;
			$a = $user->$capability;
			$i++;

			echo "<tr id='user-$i' $state>
			  <th scope='row' class='check-column'><input type='checkbox' name='users[]' id='$user_id' class='administrator' value='$user_id' /></th>
			  <th scope='row' class='check-column'>$user_id</th>
			  <td class='username column-username'><strong><a href='user-edit.php?user_id=$user_id'>{$user->user_login}</a></strong></td>
			  <td class='name column-name'>{$user->user_firstname} {$user->user_lastname} </td><td class='email column-email'><a href='mailto:{$user->user_email}' title='e-mail: {$user->user_email}'>{$user->user_email}</a></td>
			  <td class='role column-role'>$this->rolename</td>
			  <td class='column-name'>{$user->user_registered}</td>
			  </tr>";
			
			if($state == "class='alternate'"){ $state = ''; continue;}
			if($state == ''){ $state = "class='alternate'"; continue;}
		 }

		if($i == 0){ echo "</table><script type='text/javascript'>document.getElementById('tablo').style.display = 'none'; document.getElementById('tablo_para').style.display = 'none';</script><p><strong>No users are waiting moderation</strong></p>"; 
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
	 * lockDown function.
	 * redirects non-logged users if setting is enabled 
	 *
	 * @access public
	 * @return void
	 */
	function lockDown(){
		global $userdata, $wp_version;

		$options = get_option($this->options);
	
		if(($options['members_enabled'] == "yes") && (empty($userdata))){
		
	 		$requested_url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

			
			if($wp_version < 2.8){
				$requested_url = urlencode($requested_url); //WP 2.8+ encodes the URL
			}

	 		$url = wp_login_url($requested_url); 
				wp_redirect($url, 302);
				//header("Status: 302");
				exit();	
		}
		return;
	}


	
	
} // end class declaration
} // end !class_exists check


if (class_exists("absolutePrivacy")) {
	$absolutePrivacy = new absolutePrivacy();
}
//Actions and Filters	
if (isset($absolutePrivacy)) {
	register_activation_hook(__FILE__, array(&$absolutePrivacy, 'createRole')); //adds role on activation
	register_deactivation_hook(__FILE__, array(&$absolutePrivacy, 'destroyRole')); //removes role on deactivation
	
	if($_GET['action'] == 'register') add_action( 'login_head', array(&$absolutePrivacy, 'regCSS')); //adds registration form CSS
	add_action( 'register_form', array(&$absolutePrivacy, 'registrationBox'));	//adds password field to registration box
	add_filter( 'registration_errors', array(&$absolutePrivacy, 'checkRegErrors')); //adds registration form error checks
	add_action('user_register', array(&$absolutePrivacy, 'addNewUser')); //adds registration info to database

	add_action('admin_menu', array(&$absolutePrivacy, 'installOptionsMenu'));
	add_action('admin_menu', array(&$absolutePrivacy, 'moderateMenu'));
	add_action('template_redirect', array(&$absolutePrivacy, 'lockDown'));
	
	
	
//non class functions (pluggable)

	if(!function_exists('wp_authenticate')) {
	 function wp_authenticate($username, $password) {
		global $wpdb, $error, $absolutePrivacy;
		$username = sanitize_user($username);
		$password = trim($password);
		
		if(!isset($_POST['wp-submit'])) return new WP_Error('user_login', __('<strong>You must be logged in to view this site</strong>.'));;
		
		if ( '' == $username ) return new WP_Error('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));

		if ( '' == $password ) return new WP_Error('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));

		$user = get_userdatabylogin($username);

		if ( !$user || ($user->user_login != $username) ) {
			do_action( 'wp_login_failed', $username );
			return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Invalid login info.'));
		}
		
	
			$user_role = new WP_User($user->ID);

			$capabilities = $absolutePrivacy->capabilities;
			if (array_key_exists($absolutePrivacy->role, $user_role->$capabilities)) {  //if the user's role is listed as "unapproved"
				return new WP_Error('unapproved', __("<strong>ERROR</strong>: The administrator of this site must approve your account before you can login. You will be notified via email when it has been approved."));
			}
		


		$user = apply_filters('wp_authenticate_user', $user, $password);
		if (is_wp_error($user)) {
			do_action( 'wp_login_failed', $username );
			return $user;
		}

		if (!wp_check_password($password, $user->user_pass, $user->ID)) {
			do_action( 'wp_login_failed', $username );
			return new WP_Error('incorrect_password', __('<strong>ERROR</strong>: Invalid login info.'));
		}

		return new WP_User($user->ID);
	 }
	}

/* A lot of below was modified quite heavily from "New User Email Set Up"
URI: http://www.epicalex.com/new-user-email-set-up/
By: Alex Cragg
*/

	if ( !function_exists( 'newuser_mail' ) ) :
	 function newuser_mail($to, $subject, $message, $headers = '') {
		global $phpmailer;

		if ( !is_object( $phpmailer ) ) {
			require_once(ABSPATH . WPINC . '/class-phpmailer.php');
			require_once(ABSPATH . WPINC . '/class-smtp.php');
			$phpmailer = new PHPMailer();
		}

		$mail = compact('to', 'subject', 'message', 'headers');
		$mail = apply_filters('wp_mail', $mail);
		extract($mail, EXTR_SKIP);

		if ( $headers == '' ) {
			$headers = "MIME-Version: 1.0\n" .
				"From: " . apply_filters('wp_mail_from', "wordpress@" . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']))) . "\n" . 
				"Content-Type: text/HTML; charset=\"" . get_option('blog_charset') . "\"\n";
		}

		$phpmailer->ClearAddresses();
		$phpmailer->ClearCCs();
		$phpmailer->ClearBCCs();
		$phpmailer->ClearReplyTos();
		$phpmailer->ClearAllRecipients();
		$phpmailer->ClearCustomHeaders();

		$phpmailer->FromName = "WordPress";
		$phpmailer->AddAddress("$to", "");
		$phpmailer->Subject = $subject;
		$phpmailer->Body    = $message;
    	    if (get_option("newuseremailhtml") == 'text/HTML' ) {
		$phpmailer->IsHTML( true );
		    } else {
		$phpmailer->IsHTML( false );
		    }
		$phpmailer->IsMail(); // set mailer to use php mail()

		do_action_ref_array('phpmailer_init', array(&$phpmailer));

		$mailheaders = (array) explode( "\n", $headers );
		foreach ( $mailheaders as $line ) {
			$header = explode( ":", $line );
			switch ( trim( $header[0] ) ) {
				case "From":
					$from = trim( str_replace( '"', '', $header[1] ) );
					if ( strpos( $from, '<' ) ) {
						$phpmailer->FromName = str_replace( '"', '', substr( $header[1], 0, strpos( $header[1], '<' ) - 1 ) );
						$from = trim( substr( $from, strpos( $from, '<' ) + 1 ) );
						$from = str_replace( '>', '', $from );
					} else {
						$phpmailer->FromName = $from;
					}
					$phpmailer->From = trim( $from );
					break;
				default:
					if ( $line != '' && $header[0] != 'MIME-Version' && $header[0] != 'Content-Type' )
						$phpmailer->AddCustomHeader( $line );
					break;
			}
		}	

		$result = @$phpmailer->Send();

		return $result;
	 }
	endif;

	if ( !function_exists('wp_new_user_notification') ) {
	 function wp_new_user_notification($user_id, $plaintext_pass = '') {
		$user = new WP_User($user_id);

		$user_login = stripslashes($user->user_login);
		$user_email = stripslashes($user->user_email);
    
    
    		$headers .= "MIME-Version: 1.0\n" .
	    				"From: ". $user_email;         
	    	$subject = "New User Registration on " . get_bloginfo('name');

    	
    		$message = "A user has registered on " . get_bloginfo('name') . "\n";
    		$message .= "Username: " . $user_login . "\n";
	    	$message .= "Email: " . $user_email;
				$message .= "\n" . "This user cannot login until you approve their account." . "\n \n" .
							"Click the following link to approve this user: " .  get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=' . dirname(plugin_basename(__FILE__)) . '/ap_mod_email.php&id='.$user_id;

		@newuser_mail(get_option('admin_email'), $subject, $message, $headers);

		if ( empty($plaintext_pass) )
			return;

    		$headers .= "MIME-Version: 1.0\n" .
		    "From: ". get_option('admin_email');
        
    		$subject = "Registration info for " . get_bloginfo('name');

    		$message = "Thanks for registering for " . get_bloginfo('name') . "\n";
	    	$message .= "Your username is " . $user_login . "\n \n";
   			$message .= "You will not be able to login until you have been approved by an administrator. You will be emailed once your account has been approved.";
    	
		
		newuser_mail($user_email, $subject, $message, $headers);
	 }
	}

}
?>