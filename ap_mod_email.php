<?php
global $wpdb, $absolutePrivacy;

if(!$absolutePrivacy) die('Hmm, not sure you should be doing that.');

	if (function_exists('current_user_can')) {
		if (!current_user_can('manage_options')) wp_die('You are not able to do that');
	} else {
		global $user_level;
		get_currentuserinfo();
		if ($user_level < 10) wp_die('You are not able to do that');
	}

$user_id = $_GET['id'];
$user = get_userdata($user_id);

	if (isset($_POST['update_options'])) {
		if ($_POST['update_options'] == "Delete User"){
			if (!current_user_can('delete_user', $user_id) )
				wp_die(__('You can&#8217;t delete that user.'));

			if($user_id == $current_user->ID) {
				wp_die('You cannot delete yourself');
			}
			
			wp_delete_user($user_id);
		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('User deleted') . '</p></div>';
		return;
		}
		if ($_POST['update_options'] == "Approve User"){
					$user_role = new WP_User($user_id);

					$user_role->set_role("subscriber");

			$headers = "MIME-Version: 1.0\n" .
	    				"From: ". get_option('admin_email');   
			$message = "Dear " . $user->user_firstname .",\n";
			$message .= "Your account with ".get_bloginfo('name')." has been approved. You may login using the following info. \n";
			$message .= "Username: " . $user->user_login . "\n";
			$message .= "URL: " . get_bloginfo('url') . "/wp-login.php";
	
			@wp_mail($user->user_email, 'Your Account Has Been Approved', $message, $headers);  //email the user telling them they've been approved

			// Show a message to say we've done something
			echo '<div class="updated"><p>' . __('User Approved. Notification sent via email.') . '</p></div>';
			return;
		}
	}	
?>
<div class="wrap">
	<h2>User Waiting Approval</h2>
	<form method="post" action="">
	<?php $user_role = new WP_User($user->ID);
			$capabilities = $absolutePrivacy->capabilities;
		  if (!array_key_exists($absolutePrivacy->role, $user_role->$capabilities)){
		  	echo "Looks like that user has already been approved.";
		  return;
		  }
?>
	<table style="text-align: left;">
		<p>The following user registered on <em><?php echo $user->user_registered; ?></em> GMT</p>
		<tr><th>Name:</th> <td><?php echo $user->user_firstname . " " . $user->user_lastname; ?></td></tr>
		<tr><th>Username: </th> <td> <?php echo $user->user_login; ?> </td></tr>
		<tr><th>Email: </th> <td> <?php echo $user->user_email; ?></td></tr>
	</table>
	<?php if($current_status != "approved"){
?>

<div class="submit" style="float: left;"><input type="submit" name="update_options" value="<?php _e('Delete User') ?>"  onClick="return confirm('Really Delete This User? (This cannot be undone)')" style="font-weight:bold; color: red; float: left;" /> </div>
<div class="submit" style="float: left;"><input type="submit" name="update_options" value="<?php _e('Approve User') ?>"  style="font-weight:bold; float: left;" /> </div>
<?php } ?>
</form>
</div>
<?php

?>