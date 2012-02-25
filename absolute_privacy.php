<?php 

/*

Plugin Name: Absolute Privacy
Plugin URI: http://www.johnkolbert.com/portfolio/wp-plugins/absolute-privacy
Description: Give your blog absolute privacy. Forces users to register with their name and to choose a password (do not forget to enable registrations). Users cannot login until approved by an administrator. Also, gives the option to lock down your site from non-logged in viewers. 
Author: John Kolbert, Eric Mann
Version: 2.0.7
Author URI: http://www.johnkolbert.com/

Copyright Notice

Copyright 2009-2011 by John Kolbert

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



=== This file holds the definitions, actions, and filters for the Absolute Privacy plugin ===

*/



define( 'ABSPRIVACY_OPTIONS', 'absolute-privacy-options' );			// options name
define( 'ABSPRIVACY_DBOPTION', 'absolute-privacy-dbversion' );		// database version options name
define( 'ABSPRIVACY_DBVERSION', 1 );								// database version
define( 'ABSPRIVACY_PATH', WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) );
define( 'ABSPRIVACY_URL', WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)) );
define( 'ABSPRIVACY_ROLEREF', 'unapproved' );
define( 'ABSPRIVACY_ROLENAME', 'Unapproved User' );
	
require_once( ABSPRIVACY_PATH . '/functions.php' );				// holds all the functions for the plugin

register_activation_hook( __FILE__, 'abpr_doUpgrade' ); 		//adds role and options on activation


/* Install the menus */	
add_action( 'admin_menu', 'abpr_installOptionsMenu' ); 					//install the options menu
add_action( 'admin_menu', 'abpr_moderateMenu' ); 						//install the moderate user menu


if ( !is_multisite() ) {	// No multisite support yet

	/* Setup Lockdown */
	add_action( 'template_redirect', 'abpr_lockDown' ); 				//lock down website
	add_action( 'init', 'abpr_adminLockDown', 0 );						//lock down admin area
	add_filter( 'the_content', 'abpr_check_is_feed' );					//filter content for feeds

	/* Setup Registration Form */
	if ( isset( $_GET[ 'action' ] ) && ( $_GET[ 'action' ] == 'register' ) ) 
		add_action( 'login_head', 'abpr_regCSS' ); 						//adds registration form CSS
	add_action( 'register_form', 'abpr_registrationBox' );				//adds password field & first/last name to registration box
	add_filter( 'registration_errors', 'abpr_checkRegErrors' ); 		//adds registration form error checks
	add_filter( 'authenticate', 'abpr_authenticateUser', 10, 3 );		//authenticate new user
	add_action( 'user_register', 'abpr_addNewUser' ); 					//adds registration info to database 
	add_filter( 'password_reset_message', 'abpr_profileRecoveryLink', 10, 2 );

	add_shortcode( 'loginform', 'abpr_loginShortcode' );
	add_shortcode( 'profilepage', 'abpr_profileShortcode' );
	
	if ( abpr_needsUpgrade() )
		add_action( 'admin_notices', 'abpr_adminnotice' );
}