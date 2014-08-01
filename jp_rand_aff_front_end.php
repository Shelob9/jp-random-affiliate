<?php
/**
 * Front-end output
 *
 * @package   @TODO
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

class jp_rand_aff_front_end {
	function __construct() {
		add_action( 'wp_footer', array( $this, 'footer' ) );
	}

	public function output( ) {
		//get the item
		$pods = $this->get_affiliates();

		//start output false
		$out = false;
		if ( $pods && is_pod( $pods ) && $pods->total() > 0  ) {

			//get the headline and output, if it is set.
			if ( ( $headline = $this->headline() ) ) {
				$out .= '<h3 class="jp_rand_aff_headline">'.$headline.'</h3>';
			}

			//output each item
			while( $pods->fetch() ) {

				$item = $this->item( $pods );

				if ( $item ) {
					$out .= '<div class="jp-rand-aff-item" id="jp-rand-aff-item' . $pods->id() . '">'.$item.'</div>';
				}

			}

		}

		//output if we have output
		if ( $out ) {
			$out = '<div class="jp_rand_aff">'.$out;
			$out = $out.'</div><!--.jp_rand_aff-->';

			return $out;

		}

	}

	/**
	 * Output in the footer.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function footer() {
		if ( apply_filters( 'jp_rand_aff_output_in_footer', true ) ) {
			if ( false !== ( $output = $this->output() ) ) {
				echo $output;

			}

		}
	}

	/**
	 * Populate Pods object with random affiliates
	 *
	 * @since 1.0.0
	 *
	 * @return bool|Pods
	 */
	public function get_affiliates() {
		$pods = $this->pod( JP_RAND_AFF_MAIN_POD );

		$params = $this->cache_params();
		$params[ 'limit' ] = $this->limit();
		$params[ 'orderby' ] = "'RAND()'";
		$pods = $pods->find( $params );

		return $pods;
	}

	/**
	 * Get Pods object
	 *
	 * @param $name Which Pod to get.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|Pods
	 */
	private function pod( $name ) {

		return pods( $name, $this->cache_params(), true );

	}

	/**
	 * Cache params for building Pods objects.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function cache_params() {
		/**
		 * Override the cache mode.
		 *
		 * @param string $cache_mode Sets the cache mode. Defaults to 'cache' for object caching. Set to 'transient' or 'site-transient' to use transient caching.
		 * @param int $cache_expires Sets cache max length. Defaults to one week.
		 *
		 */
		$cache_mode = apply_filters( 'jp_rand_aff_cache_mode', 'cache' );
		$cache_expires = apply_filters( 'jp_rand_aff_cache_expires', WEEK_IN_SECONDS );

		return array(
			'cache_mode' 	=> $cache_mode,
			'expires'		=> $cache_expires,
		);

	}

	/**
	 * Get the limit based on device
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|null
	 */
	private function limit() {
		$pods = $this->pod( JP_RAND_AFF_SET_POD );
		if ( function_exists( 'is_phone' ) ) {
			if ( is_phone() ) {
				return $pods->field( 'per_page_m' );
			}

			if ( is_tablet() ) {
				return $pods->field( 'per_page_t' );
			}
		}
		else {
			if ( wp_is_mobile() ) {
				return $pods->field( 'per_page_m' );
			}
		}

		return $pods->field( 'per_page_d' );

	}

	/**
	 * Get the headline
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|null
	 */
	private function headline() {
		$pods = $this->pod( JP_RAND_AFF_SET_POD );
		if ( $this->use_headline() ) {
			return $pods->field( 'headline' );

		}

	}

	/**
	 * Check based on device if we are using headline or not
	 *
	 * @since 0.0.1
	 *
	 * @return mixed|null
	 */
	private function use_headline() {
		$pods = $this->pod( JP_RAND_AFF_SET_POD );

		if ( function_exists( 'is_phone' ) ) {
			if ( is_phone() ) {
				return $pods->field( 'use_headline_m' );
			}

			if ( is_tablet() ) {
				return $pods->field( 'use_headline_t' );
			}
		}
		else {
			if ( wp_is_mobile() ) {
				return $pods->field( 'use_headline_m' );
			}
		}

		return $pods->field( 'use_headline_d' );

	}

	/**
	 * Each individual item
	 *
	 * Gets the image + description
	 *
	 * @since 1.0.0
	 *
	 * @param Pods|obj $Pods Single Pods item
	 */
	private function item( $pods ) {
		//Get the image based on device.
		$img = false;
		if ( function_exists( 'is_phone' ) ) {
			if ( is_phone() ) {
				$img = $pods->field( 'img_rct' );
			}

			if ( is_tablet() ) {
				$img = $pods->field( 'img_sq' );
			}
		}
		elseif ( wp_is_mobile() ) {
			$img = $pods->field( 'img_rct' );
		}
		else {
			$img = $pods->field( 'img_sq' );
		}

		//get url of image if we can
		if ( $img  ) {
			$img = pods_image_url( $img );
		}

		//Put item description in a variable or set it false.
		if ( '' === ( $desc = $pods->display( 'desc' ) ) ) {
			$desc = false;
		}

		$out = '';

		//start output by creating image tag/link if we can
		if ( $img && is_string( $img ) ) {
			//create an image tag
			$img = '<img src="' . $img . '">';

			//make it clickable
			$img = $this->link( $pods, $img );

			//add it to output
			$out .= $img;
		}

		//add description to output
		if ( $desc ) {
			$out .= '<p class="jp-rand-aff-desc">' . $desc . '</p>';
		}

		//output if we have anything to output
		if ( $out ) {
			return $out;
		}

	}

	/**
	 * Wraps text in an HTML link, using the link field if it's valid.
	 *
	 * @param Pods|obj $pods The Pods object
	 * @param string $text The text to wrap in
	 */
	private function link( $pods, $text ) {
		//get the link
		$link = $pods->field( 'link' );

		//check that its not empty or invalid
		if ( $link && $link !== '' && filter_var( $link , FILTER_VALIDATE_URL ) ) {

			//wrap up as link
			$link = '<a href="'.$link.'" >'.$text.'</a>';
		}
		else {
			//if it failed, set return var to equal the input (ie do nothing)
			$link = $text;
		}

		return $link;

	}

} 
