<?php
/*
Plugin Name: JP Random Affiliates
Plugin URI: http://example.com/
Description: Description
Version: 0.0.1
Author: Your Name
Author URI: http://example.com/
Text Domain: jp-rand-aff
License: GPL v2 or later
*/

/**
 * Copyright (c) YEAR Your Name (email: Email). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Define constants
 *
 * @since 0.0.2
 */
define( 'JP_RAND_AFF_SLUG', plugin_basename( __FILE__ ) );
define( 'JP_RAND_AFF_URL', plugin_dir_url( __FILE__ ) );
define( 'JP_RAND_AFF_DIR', plugin_dir_path( __FILE__ ) );
define( 'JP_RAND_AFF_VERSION', '0.0.1' );

define( 'JP_RAND_AFF_MAIN_POD', 'jp_rand_aff' );
define( 'JP_RAND_AFF_SET_POD', 'jp_rand_aff_set' );

/**
 * JP_Rand_AFF class
 *
 * @class JP_Rand_AFF The class that holds the entire JP_Rand_AFF plugin
 *
 * @since 0.0.1
 */
class JP_Rand_AFF {

	/**
	 * Constructor for the JP_Rand_AFF class
	 *
	 * Sets up all the appropriate hooks and actions
	 * within the plugin.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		/**
		 * Plugin Setup
		 */
		add_action( 'init', array( $this, 'setup' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		// Localize our plugin
		add_action( 'init', array( $this, 'localization_setup' ) );

		/**
		 * Scripts/ Styles
		 */
		// Loads frontend scripts and styles
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Loads admin scripts and styles
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		//Require Pods ACT component
		add_action( 'plugins_loaded', array( $this, 'require_act' ) );



	}

	/**
	 * Initializes the JP_Rand_AFF() class
	 *
	 * Checks for an existing JP_Rand_AFF() instance
	 * and if it doesn't find one, creates it.
	 *
	 * @since 0.0.1
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new JP_Rand_AFF();
		}

		return $instance;

	}

	/**
	 * Deactivation function
	 *
	 * @todo Really delete all content on deactivation?
	 *
	 * @since 0.0.1
	 */
	public function deactivate() {
		return $this->setup_class( true, true );
	}

	/**
	 * Initialize plugin for localization
	 *
	 * @since 0.0.1
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'jp-rand-aff', false, trailingslashit( JP_RAND_AFF_URL ) . '/languages/' );

	}

	/**
	 * Enqueue front-end scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @since 0.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * All styles goes here
		 */
		wp_enqueue_style( 'jp-rand-aff-styles', trailingslashit( JP_RAND_AFF_URL ) . 'css/front-end.css' );

		/**
		 * All scripts goes here
		 */
		wp_enqueue_script( 'jp-rand-aff-scripts', trailingslashit( JP_RAND_AFF_URL ) . 'js/front-end.js', array( ), false, true );


		/**
		 * Example for setting up text strings from Javascript files for localization
		 *
		 * Uncomment line below and replace with proper localization variables.
		 */
		// $translation_array = array( 'some_string' => __( 'Some string to translate', 'jp-rand-aff' ), 'a_value' => '10' );
		// wp_localize_script( 'jp-rand-aff-scripts', 'podsExtend', $translation_array ) );

	}

	/**
	 * Enqueue admin scripts
	 *
	 * Allows plugin assets to be loaded.
	 *
	 * @since 0.0.1
	 */
	public function admin_enqueue_scripts() {

		/**
		 * All admin styles goes here
		 */
		wp_enqueue_style( 'jp-rand-aff-admin-styles', plugins_url( 'css/admin.css', __FILE__ ) );

		/**
		 * All admin scripts goes here
		 */
		wp_enqueue_script( 'jp-rand-aff-admin-scripts', plugins_url( 'js/admin.js', __FILE__ ), array( ), false, true );

	}

	/**
	 * Setup the Pods
	 *
	 * Check if we have done setup and if not, do it.
	 *
	 * @returns message about Pods being created
	 */
	public function setup() {
		$setup = get_option( 'jp_rand_aff_setup', false );
		if ( ! $setup  || ( isset( $setup[ 'setup-complete' ] ) && ! $setup[ 'setup-complete' ] )  ) {
			return $this->setup_class();
		}



	}

	/**
	 * Gets the setup class
	 *
	 * @param bool $delete_existing
	 * @param bool $delete_only
	 *
	 * @return jp_rand_aff_pods_setup
	 */
	private function setup_class( $delete_existing = false, $delete_only = false ) {

		include( 'jp_rand_aff_pods_setup.php' );

		$class = new jp_rand_aff_pods_setup( $delete_existing, $delete_only );

		return $class;

	}

	/**
	 * Ensures the Pods Advanced Content Type componet remains active.
	 */
	public function require_act() {
		pods_require_component( 'advanced-content-types' );
	}



} // JP_Rand_AFF

/**
 * Initialize class, if Pods is active.
 *
 * @since 0.0.1
 */
add_action( 'plugins_loaded', 'jp_rand_aff_safe_activate');
function jp_rand_aff_safe_activate() {
	if ( defined( 'PODS_VERSION' ) ) {
		$GLOBALS[ 'JP_Rand_AFF' ] = JP_Rand_AFF::init();
	}

}


/**
 * Throw admin nag if Pods isn't activated.
 *
 * Will only show on the plugins page.
 *
 * @since 0.0.1
 */
add_action( 'admin_notices', 'jp_rand_aff_admin_notice_pods_not_active' );
function jp_rand_aff_admin_notice_pods_not_active() {

	if ( ! defined( 'PODS_VERSION' ) ) {

		//use the global pagenow so we can tell if we are on plugins admin page
		global $pagenow;
		if ( $pagenow == 'plugins.php' ) {
			?>
			<div class="updated">
				<p><?php _e( 'You have activated Pods Extend, but not the core Pods plugin.', 'jp_rand_aff' ); ?></p>
			</div>
		<?php

		} //endif on the right page
	} //endif Pods is not active

}

/**
 * Throw admin nag if Pods minimum version is not met
 *
 * Will only show on the Pods admin page
 *
 * @since 0.0.1
 */
add_action( 'admin_notices', 'jp_rand_aff_admin_notice_pods_min_version_fail' );
function jp_rand_aff_admin_notice_pods_min_version_fail() {

	if ( defined( 'PODS_VERSION' ) ) {

		//set minimum supported version of Pods.
		$minimum_version = '2.3.18';

		//check if Pods version is greater than or equal to minimum supported version for this plugin
		if ( version_compare(  $minimum_version, PODS_VERSION ) >= 0) {

			//create $page variable to check if we are on pods admin page
			$page = pods_v('page','get', false, true );

			//check if we are on Pods Admin page
			if ( $page === 'pods' ) {
				?>
				<div class="updated">
					<p><?php _e( 'Pods Extend, requires Pods version '.$minimum_version.' or later. Current version of Pods is '.PODS_VERSION, 'jp_rand_aff' ); ?></p>
				</div>
			<?php

			} //endif on the right page
		} //endif version compare
	} //endif Pods is not active

}

function foo() {
	include( 'jp_rand_aff_pods_setup.php' );

	$class = new jp_rand_aff_pods_setup( true, true );
}
/**
 * Debug functions
 */
if ( !function_exists( 'print_r2' ) ) :
	function print_r2($val){
		echo '<pre>';
		print_r($val);
		echo  '</pre>';
	}
endif;


if ( !function_exists( 'print_x2' ) ) :
	function print_x2($val){
		echo '<pre>';
		var_export($val);
		echo  '</pre>';
	}
endif;
