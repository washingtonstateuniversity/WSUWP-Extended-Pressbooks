<?php
/*
Plugin Name: WSUWP Extended Pressbooks
Plugin URI: https://github.com/washingtonstateuniversity/WSUWP-Extended-Pressbooks
Description: WSU specific extensions to the Pressbooks plugin.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.0.1
Author URI: https://web.wsu.edu
*/

namespace WSU\Pressbooks;

add_action( 'plugins_loaded', '\WSU\Pressbooks\remove_pressbooks_hooks' );

function remove_pressbooks_hooks() {
	// Remove forced color scheme in admin.
	remove_action( 'wp_login', '\Pressbooks\Activation::forcePbColors', 10 );
	remove_action( 'profile_update', '\Pressbooks\Activation::forcePbColors' );
	remove_action( 'user_register', '\Pressbooks\Activation::forcePbColors' );
}
