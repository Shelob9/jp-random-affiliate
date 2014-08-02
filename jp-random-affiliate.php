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
if ( ! defined( 'ABSPATH' ) ) exit;

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

if ( ! defined( 'JP_RAND_AFF_FORCE_MOBILE' ) ) {
	define( 'JP_RAND_ADD_FORCE_MOBILE', false );
}

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


		//load front-end
		add_action( 'init', array( $this, 'front_end' ) );

		$pod_name = JP_RAND_AFF_MAIN_POD;
		add_action( "pods_api_pre_save_pod_item_{$pod_name}", array( $this, 'image_resize_on_save' ) );

		//check for image size change
		add_action( 'admin_init', array( $this, 'update_image_sizes' ) );
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

		if ( false !== ( $inline_css = $this->inline_css() )  ) {
			wp_add_inline_style( 'jp-rand-aff-styles', $inline_css );
		}

		/**
		 * All scripts goes here
		 */
		wp_enqueue_script( 'jp-rand-aff-scripts', trailingslashit( JP_RAND_AFF_URL ) . 'js/front-end.js', array( 'jquery' ), false, true );


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
		if ( false === $setup  || ( isset( $setup[ 'setup-complete' ] ) && false === $setup[ 'setup-complete' ] )  ) {
			return $this->setup_class();
		}

		//set the image size check options
		$sizes = array(
			'sq' => 'jp_rand_aff_sq_dim',
			'rct' => 'jp_rand_aff_rct_dim'
		);

		foreach( $sizes as $size => $option ) {
			update_option( $option, $this->image_dimensions( $size ) );
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

	/**
	 * Loads the front-end class.
	 *
	 * @return jp_rand_aff_front_end
	 */
	public function front_end() {
		//@TODO options fo where to show
		if ( ! is_admin() ) {
			include_once( 'jp_rand_aff_front_end.php' );

			$front_end = new jp_rand_aff_front_end();

			return $front_end;

		}

	}

	/**
	 * Resizes images when saved.
	 *
	 * @since 0.0.1
	 *
	 * @uses pods_api_pre_save_pod_item_{$pod_name} filter
	 *
	 * @param $pieces
	 */
	public function image_resize_on_save( $pieces ) {
		if ( isset( $pieces[ 'fields' ][ 'img_sq' ][ 'value' ] ) ) {
			$img = $pieces[ 'fields' ][ 'img_sq' ][ 'value' ];
			//get the array key, which is the ID
			$img = key( $img );

			$this->image_size( $img , 'sq'  );
		}

		if ( isset( $pieces[ 'fields' ][ 'img_rct' ][ 'value' ] ) ) {
			$img = $pieces[ 'fields' ][ 'img_rct' ][ 'value' ];

			$img = key( $img );
			$this->image_size( $img, 'rct' );
		}

	}

	/**
	 * Resizes images to our specific sizes or arbitrary size
	 *
	 * @since 0.1.0
	 *
	 * @param int $img Image ID to resize
	 * @param array|string $dimensions Either an array of dimensions to use or rct|sq for our standard sizes.
	 *
	 * @return bool True if image was resize, false if not.
	 */
	private function image_size( $img, $dimensions ) {
		if ( is_string( $dimensions ) ) {
			$dimensions = $this->image_dimensions( $dimensions );
		}


		if ( is_array( $dimensions ) ) {
			return pods_image_resize( $img, $dimensions );
		}

	}

	/**
	 * Bulk resize images used by this plugin
	 *
	 * @since 0.0.1
	 *
	 * @param $limit
	 */
	function image_bulk_resize( $limit = -1, $which = 'sq'  ) {
		if ( $which === 'sq' ) {
			//get square dimensions
			$dimensions = $this->image_dimensions( 'sq' );

			//set field name
			$field = 'img_sq';
		}
		elseif ( $which === 'rct' ) {
			//get rectangle dimensions
			$dimensions = $this->image_dimensions( 'rct' );

			//set field name
			$field = 'img_rct';
		}
		else {
			return;
		}


		$pods = pods( JP_RAND_AFF_MAIN_POD, array( 'limit' => $limit ) );
		if ( $pods->total() > 0 ) {

			while ( $pods->fetch() ) {

				//get image field
				$img = $pods->field( $field );

				//get ID from field
				$img = pods_image_id_from_field(  $img );

				//resize
				$this->image_size( $img, $dimensions );

			}

		}

	}

	/**
	 * Checks on admin_init if sizes have been changed for images and if so resizes
	 *
	 * @since 0.0.1
	 */
	function update_image_sizes() {
		$sizes = array(
			'sq' => 'jp_rand_aff_sq_dim',
			'rct' => 'jp_rand_aff_rct_dim'
		);

		foreach( $sizes as $size => $option ) {
			if ( $this->image_dimensions( $size ) !== maybe_unserialize( get_option( $option, 0 ) ) ) {
				$dimensions = $this->image_dimensions( $size );
				$this->image_bulk_resize( -1, $size );
				update_option( $option, $dimensions );
			}

		}

	}

	/**
	 * Get image size dimensions
	 *
	 * @param string $size rct|sq
	 *
	 * @return mixed|void
	 */
	public function image_dimensions( $size = 'sq' ) {
		if ( $size === 'sq' ) {
			/**
			 * Set the dimensions for square images
			 *
			 * @since 0.0.1
			 *
			 * @param array $dimensions an array of image dimensions
			 *
			 * @return The dimensions for square images
			 */
			return apply_filters( 'jp_random_affiliates_sq_size', $this->square_default() );

		}

		if ( $size === 'rct' ) {
			/**
			 * Set the dimensions for square images
			 *
			 * @since 0.0.1
			 *
			 * @param array $dimensions an array of image dimensions
			 *
			 * @return The dimensions for rectangular images
			 */
			return apply_filters( 'jp_random_affiliates_rct_size', $this->rectangle_default() );

		}

	}

	/**
	 * The default square image dimensions
	 *
	 * @return array
	 */
	private function square_default() {

		return array( 120, 120 );

	}

	/**
	 * The default rectangle image dimensions
	 *
	 * @return array
	 */
	private function rectangle_default() {

		return array( 120, 50 );

	}

	/**
	 * Outputs the inline CSS
	 *
	 * Will be used to switch to rectangle mode or to override main CSS if image sizes are changed via filters.
	 *
	 * @since 0.0.1
	 *
	 * @return bool|string
	 */
	function inline_css() {
		$inline_css = false;

		if ( jp_rand_aff_mobile_test() || JP_RAND_ADD_FORCE_MOBILE  ) {
		$dimensions = $this->image_dimensions( 'rct' );
			$inline_css =
					"li.jp-rand-aff-item img {
					width: {$dimensions[0]}px;
					height: {$dimensions[1]}px;
				}";

		}
		else {
			$dimensions = $this->image_dimensions( 'sq');
			if ( $dimensions !== $this->square_default()  ) {
				$inline_css =
					"li.jp-rand-aff-item img {
					width: {$dimensions[0]}px;
					height: {$dimensions[1]}px;
				}";

			}
		}

		return $inline_css;

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

function jp_rand_aff_test_setup() {
	update_option( 'jp_rand_aff_setup', false );
	include( 'jp_rand_aff_pods_setup.php' );

	$class = new jp_rand_aff_pods_setup( true, false );
}

/**
 * Checks if we want square or rectangle images
 *
 * @return bool
 */
function jp_rand_aff_mobile_test() {
	if ( function_exists( 'is_phone' ) ) {
		if ( is_phone() ) {
			return true;

		}

		if ( is_tablet() ) {
			return false;
		}
	}
	elseif ( wp_is_mobile() ) {
		return true;

	}
	else {
		return false;

	}

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

