<?php
/**
 * @TODO What this does.
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
		$pods = $this->get_affiliates();
		$out = false;
		if ( $pods && is_pod( $pods ) && $pods->total() > 0  ) {
			$template_name = apply_filters( 'jp_rand_aff_output_template', 'jp_rand_aff_output_template' );

			if ( false !== ( $headline = $this->headline() ) ) {
				$out .= '<h3 class="jp_rand_aff_headline">'.$headline.'</h3>';
			}

			while( $pods->fetch() ) {
				//reset id
				$pods->id = $pods->id();

				//load template into variable so we can test it before outputting
				$template = $pods->template( $template_name );

				if ( $template ) {
					$out .= '<div class="jp-rand-aff-template">'.$template.'</div>';
				}

			}

		}

		pods_error( var_dump( $out ) );
		if ( $out ) {
			$out = '<div class="jp_rand_aff">'.$out;
			$out = $out.'</div><!--.jp_rand_aff-->';

			return $out;

		}

	}

	public function footer() {
		if ( apply_filters( 'jp_rand_aff_output_in_footer', true ) ) {
			if ( false !== ( $output = $this->output() ) ) {
				echo $output;

			}

		}
	}

	private function get_affiliates() {
		$pods = $this->pod( JP_RAND_AFF_MAIN_POD );

		$params = $this->cache_params();
		$params[ 'limit' ] = $this->limit();
		$params[ 'orderby' ] = "'RAND()'";
		$pods = $pods->find( $params );

		return $pods;
	}

	private function pod( $name ) {

		return pods( $name, $this->cache_params(), true );

	}

	private function cache_params() {
		$cache_mode = apply_filters( 'jp_rand_aff_cache_mode', 'object' );
		$cache_expires = apply_filters( 'jp_rand_aff_cache_expires', WEEK_IN_SECONDS );

		return array(
			'cache_mode' 	=> $cache_mode,
			'expires'		=> $cache_expires,
		);

	}

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

	private function headline() {
		$pods = $this->pod( JP_RAND_AFF_SET_POD );
		if ( $this->use_headline() ) {
			return $pods->field( 'headline' );

		}

	}

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
} 
