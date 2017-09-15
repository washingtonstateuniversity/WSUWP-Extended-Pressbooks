<?php
/*
Plugin Name: WSUWP Extended Pressbooks
Plugin URI: https://github.com/washingtonstateuniversity/WSUWP-Extended-Pressbooks
Description: WSU specific extensions to the Pressbooks plugin.
Author: washingtonstateuniversity, jeremyfelt
Version: 0.0.3
Author URI: https://web.wsu.edu
*/

namespace WSU\Pressbooks;

add_action( 'plugins_loaded', '\WSU\Pressbooks\remove_pressbooks_hooks' );
add_filter( 'wsu_analytics_app_analytics_id', '\WSU\Pressbooks\app_analytics_id' );
add_filter( 'pre_option_wsuwp_ga_id', 'WSU\Pressbooks\opentext_analytics_id' );
add_filter( 'wpmu_validate_blog_signup', '\WSU\Pressbooks\allow_hyphens_in_site_url' );
add_action( 'before_signup_form', 'WSU\Pressbooks\add_signup_text_filter' );

function remove_pressbooks_hooks() {
	// Remove forced color scheme in admin.
	remove_action( 'wp_login', '\Pressbooks\Activation::forcePbColors', 10 );
	remove_action( 'profile_update', '\Pressbooks\Activation::forcePbColors' );
	remove_action( 'user_register', '\Pressbooks\Activation::forcePbColors' );

	// Remove everything related to Google Analytics.
	remove_action( 'network_admin_menu', '\Pressbooks\Admin\Analytics\add_network_menu' );
	remove_action( 'admin_init', '\Pressbooks\Admin\Analytics\network_analytics_settings_init' );
	remove_action( 'admin_menu', '\Pressbooks\Admin\Analytics\add_menu' );
	remove_action( 'admin_init', '\Pressbooks\Admin\Analytics\analytics_settings_init' );
	remove_action( 'admin_head', '\Pressbooks\Admin\Analytics\print_admin_analytics' );
	remove_action( 'wp_head', '\Pressbooks\Analytics\print_analytics' );
}

/**
 * Filter the GA code used for tracking application views.
 *
 * The returned string is one we associate with the "WordPress" application.
 *
 * @since 0.0.4
 *
 * @return string The GA ID used for tracking application page views in Google Analytics.
 */
function app_analytics_id() {
	return 'UA-69906812-7';
}

/**
 * Filter the GA code used at each site level.
 *
 * @since 0.0.4
 *
 * @return string The GA code assigned to opentext.wsu.edu.
 */
function opentext_analytics_id() {
	return 'UA-17815664-40';
}

/**
 * Allow hyphens in site URLs when new books are created.
 *
 * @since 0.0.2
 *
 * @param array $result {
 *     Array of domain, path, blog name, blog title, user and error messages.
 *
 *     @type string          $domain     Domain for the site.
 *     @type string          $path       Path for the site. Used in subdirectory installs.
 *     @type string          $blogname   The unique site name (slug).
 *     @type string          $blog_title Blog title.
 *     @type string|\WP_User $user       By default, an empty string. A user object if provided.
 *     @type \WP_Error       $errors     WP_Error containing any errors found.
 * }
 *
 * @return mixed
 */
function allow_hyphens_in_site_url( $result ) {
	if ( ! is_wp_error( $result['errors'] ) ) {
		return $result;
	}

	$errors = new \WP_Error();

	foreach ( $result['errors']->errors as $code => $messages ) {

		foreach ( $messages as $key => $message ) {
			if ( 'blogname' !== $code ) {
				$errors->add( $code, $message );
				continue;
			}

			if ( 'Site names can only contain lowercase letters (a-z) and numbers.' === $message ) {
				$pattern = '/^[a-z0-9]+([-]?[a-z0-9]+)*$/';
				preg_match( $pattern, $result['blogname'], $match );

				if ( empty( $match ) || $result['blogname'] !== $match[0] ) {
					$errors->add( 'blogname', 'Site URLs can only contain lowercase letters (a-z), hyphens (-), and numbers.' );
				}
			} else {
				$errors->add( 'blogname', $message );
			}
		}
	}

	$result['errors'] = $errors;

	return $result;
}

/**
 * Add a gettext filter on the signup page to adjust signup language.
 *
 * @since 0.0.2
 */
function add_signup_text_filter() {
	add_filter( 'gettext', 'WSU\Pressbooks\custom_signup_text', 15, 2 );
}

/**
 * Re-"translate" a custom Pressbooks string to let users know that hyphens
 * can be used in book titles.
 *
 * @since 0.0.2
 *
 * @param string $translated_text
 * @param string $untranslated_text
 *
 * @return string
 */
function custom_signup_text( $translated_text, $untranslated_text ) {
	switch ( $untranslated_text ) {
		case 'Must be at least 4 characters, letters and numbers only. It cannot be changed, so choose carefully!' :
			$translated_text = __( 'Your webbook address is the web address where you will access and create your book. It must be at least 4 characters, letters, numbers, and hyphens only. It <strong>cannot be changed</strong>, so choose carefully! We suggest using the title of your book with no spaces.', 'pressbooks' );
			break;
		case 'If you&#8217;re not going to use a great site domain, leave it for a new user. Now have at it!' :
			$translated_text = __( 'Your webbook address is the web address where you will access and create your book. It must be at least 4 characters, letters, numbers, and hyphens only. It <strong>cannot be changed</strong>, so choose carefully! We suggest using the title of your book with no spaces.', 'pressbooks' );
			break;
	}

	return $translated_text;
}
