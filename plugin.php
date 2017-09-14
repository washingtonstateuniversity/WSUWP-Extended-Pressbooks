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
add_filter( 'wpmu_validate_blog_signup', '\WSU\Pressbooks\allow_hyphens_in_site_url' );

function remove_pressbooks_hooks() {
	// Remove forced color scheme in admin.
	remove_action( 'wp_login', '\Pressbooks\Activation::forcePbColors', 10 );
	remove_action( 'profile_update', '\Pressbooks\Activation::forcePbColors' );
	remove_action( 'user_register', '\Pressbooks\Activation::forcePbColors' );
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
