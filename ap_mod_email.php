<?php

if(!function_exists('add_action') ) exit(); //prevent direct loading

if (function_exists('current_user_can')) {
	if (!current_user_can('manage_options')) wp_die('You are not able to do that');
} else {
	global $user_level;
	get_currentuserinfo();
	if ($user_level < 10) wp_die('You are not able to do that');
}

$user_id = $_GET['id'];
if(!$user_id) wp_die('No user ID provided. Please check the URL'); //no ID found in URL

$user = get_userdata($user_id);
if(!$user) wp_die('Incorrect user ID provided. Please check the URL'); //ID not found in Database

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
		$user_role->set_role("subscriber"); //change their role to subscriber
		
		$email = new absolutePrivacy(); 
		$email->handleEmail($user_id, $type='account_approved'); //send approval email

		// Show a message to say we've done something
		echo '<div class="updated"><p>' . __('User Approved. Notification sent via email.') . '</p></div>';
	
		return;
	}
}	
?> 

	<div class="wrap">
		<h2>Absolute Privacy: User Waiting Approval</h2>
					
	<?php $user_role = new WP_User($user->ID);
			$capabilities = $this->capabilities;
		  if (!array_key_exists($this->role, $user_role->$capabilities)){
		  	echo "Looks like that user has already been approved. </div>";
		  return;
		  }
		$plugin_path = get_bloginfo('wpurl') . '/wp-content/plugins/' . dirname(plugin_basename(__FILE__));

?>

    	
<div class="wrap" style="float: left; width: 60%; margin: 5px;">	
		<form method="post" action="">


<table class="widefat" cellspacing="0" >



	<thead>
		<tr class="thead">
			<th scope="col" style="width: 100px;" colspan="2" class="" style="">The following user registered on <em><?php echo $user->user_registered; ?></em> GMT</th>
		</tr>
	</thead>

	<tbody id="users" class="list:user user-list">
		<tr><th style="width: 100px;">Name:</th> <td><?php echo $user->user_firstname . " " . $user->user_lastname; ?></td></tr>
		<tr><th>Username: </th> <td> <?php echo $user->user_login; ?> </td></tr>
		<tr><th>Email: </th> <td> <?php echo $user->user_email; ?></td></tr>

	</tbody>
</table>
	<div class="clear"></div>	
	<?php if($current_status != "approved"){
?>

<div class="submit" style="float: left;"><input type="submit" name="update_options" value="<?php _e('Delete User') ?>"  onClick="return confirm('Really Delete This User? (This cannot be undone)')" style="font-weight:bold; color: red; float: left;" /> </div>
<div class="submit" style="float: left;"><input type="submit" name="update_options" value="<?php _e('Approve User') ?>"  style="font-weight:bold; float: left;" /> </div>
<?php } ?>

</div>

<div style="float: left; width: 30%; margin: 5px; ">
<table name="pl_donate" class="widefat fixed" style="margin-bottom: 10px;" cellspacing="0">

	<thead>
		<tr class="thead">
			<th scope="col" style="width: 100px;"><img style="margin-top: -5px; margin-right: 3px; float: left;" src="<?php echo $plugin_path; ?>/img/LinkBack.png" alt="" />How To Support This Plugin</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>
				<ul style="font-size: 1.0em;">
					<li><a href="http://www.johnkolbert.com/donate/?plugin=absolute-privacy" title="Donate" target="_blank">Donate to support development</a></li>
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
			<th scope="col" class="" style=""><img style="margin-top: -5px; margin-right: 3px; float: left;" src="<?php echo $plugin_path; ?>/img/help.png" alt="" /> Plugin Help</th>
		</tr>
	</thead>

	<tbody>
		<tr >
			<td>
				<ul style="font-size: 1.0em;">
					<li><a href="http://www.johnkolbert.com/portfolio/wp-plugins/absolute-privacy/" title="Go to Plugin Homepage">Plugin Homepage</a></li>
					<li><a href="http://www.mammothapps.com/contact" title="Hire Me!">Hire me to customize this plugin</a></li>
					
				</ul>
			</td>
		</tr>
	</tbody>
</table>	
<!-- #pl_help -->

<table name="pl_author" class="widefat fixed" cellspacing="0">

	<thead>
		<tr class="thead">
			<th scope="col" style="width: 100px;" class="" style=""><img style="margin-top: -5px; margin-right: 3px; float: left;" src="<?php echo $plugin_path; ?>/img/info.png" alt="" />Plugin Author</th>
		</tr>
	</thead>

	<tbody>
		<tr>
			<td>
				<p style="text-align: center; font-size: 1.2em;">Plugin created by <a href="http://www.johnkolbert.com/" title="John Kolbert WordPress Consulting">John Kolbert</a><br />
				<span style="font-size: 0.8em;">Need Help? <a href="http://www.mammothapps.com/contact" title="Hire Me">Hire me.</a><br />
				<a href="http://www.twitter.com/johnkolbert" title="Follow Me!">Follow me on Twitter!</a><br /></span>
				</p>
			</td>
		</tr>
	</tbody>
</table>	<!-- #pl_author -->

</div>
</div>