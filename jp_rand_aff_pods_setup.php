<?php
/**
 * Creates Pods and Adds Fields To Them
 *
 * @package   @jp_rand_aff
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link      
 * @copyright 2014 Josh Pollock
 */

class jp_rand_aff_pods_setup {

	function __construct( $delete_existing = false, $only_delete = false ) {

		//delete this plugin's Pods if needed.
		if ( $delete_existing ) {
			$this->delete_existing();
		}

		//continue with setup if we are not only deleting
		if ( ! $only_delete ) {
			//Turn on ACTs
			$this->activate_act_component();

			$main_pod = $this->create_pod( JP_RAND_AFF_MAIN_POD );
			$settings_pod = $this->create_pod( JP_RAND_AFF_SET_POD );

			$this->add_template();
			
			if ( intval( $main_pod ) > 0 && intval( $settings_pod ) > 0 ) {
				$this->post_create_message( $main_pod, $settings_pod );
				$this->mark_setup();
			}


		}

	}

	/**
	 * Delete the Pods
	 *
	 * @TODO Success/fail messages and error/fail handling detection.
	 *
	 * @uses $this->delete_pod
	 *
	 * @return array
	 */
	private function delete_existing() {
		$delete_main = $delete_settings = null;
		$name = JP_RAND_AFF_MAIN_POD;
		$api = $this->pod_api( $name  );
		if ( is_object( $api ) ) {
			$id = $this->pod_id( $name );
			if ( $id ) {
				$delete_main = $this->delete_pod( $name, $id, $api );
			}

		}

		$name = JP_RAND_AFF_SET_POD;
		$api= $this->pod_api( $name );
		if ( is_object( $api ) ) {
			$id = $this->pod_id( $name );
			if ( $id ) {
				$delete_settings = $this->delete_pod( $name, $id, $api );
			}

		}

		return array( $delete_main, $delete_settings );

	}

	/**
	 * Does the actual deleting
	 *
	 * @param $name
	 * @param $id
	 * @param $api
	 *
	 * @return mixed
	 */
	private function delete_pod( $name, $id, $api ) {
		$params = array (
			'id'       => $id,
			'pod_name' => $name,
		);

		return $api->delete_pod( $params );

	}

	/**
	 * Get Pod ID
	 *
	 * @param $name
	 *
	 * @return int
	 */
	private function pod_id( $name ) {
		$lc_name = strtolower( $name );
		$id = get_option( "{$lc_name}_pod_id", false );

		if ( $id ) {
			return $id;
		}
		else{
			$pod =  pods( $name );

			if ( is_pod( $pod ) ) {
				return $pod->id;
			}
		}

	}

	/**
	 * Settings for creating the main Pod
	 *
	 * @return array
	 */
	private function main_pod() {
		$main_pod = array(
			'name' => JP_RAND_AFF_MAIN_POD,
			'label' => 'Affiliates',
			'description' => '',
			'options' =>
				array (
					'show_in_menu' => '1',
					'label_singular' => 'Affiliate',
					'public' => '1',
					'show_ui' => '1',
					'supports_title' => '1',
					'supports_editor' => '0',
					'old_name' => 'jp_rand_aff_affiliat',
					'publicly_queryable' => '0',
					'exclude_from_search' => '1',
					'capability_type' => 'post',
					'capability_type_custom' => 'jp_rand_aff_affiliat',
					'capability_type_extra' => '1',
					'has_archive' => '0',
					'hierarchical' => '0',
					'rewrite' => '1',
					'rewrite_with_front' => '1',
					'rewrite_feeds' => '0',
					'rewrite_pages' => '1',
					'query_var' => '1',
					'can_export' => '1',
					'default_status' => 'draft',
					'supports_author' => '0',
					'supports_thumbnail' => '0',
					'supports_excerpt' => '0',
					'supports_trackbacks' => '0',
					'supports_custom_fields' => '0',
					'supports_comments' => '0',
					'supports_revisions' => '0',
					'supports_page_attributes' => '0',
					'supports_post_formats' => '0',
					'built_in_taxonomies_category' => '0',
					'built_in_taxonomies_link_category' => '0',
					'built_in_taxonomies_post_tag' => '0',
					'menu_position' => '0',
					'show_in_nav_menus' => '1',
					'show_in_admin_bar' => '1',
				),
			'storage' => 'table',
		);

		return $main_pod;

	}

	/**
	 * Settings for creating the settings Pod
	 *
	 * @return array
	 */
	private function settings_pod() {
		$settings_pod = array(
			'name' => JP_RAND_AFF_SET_POD,
			'label' => 'JP Random Affiliate Settings',
			'type' => 'settings',
			'description' => '',
			'options' =>
				array (
					'show_in_menu' => 1,
					'menu_name' => 'JP Random Affiliate Settings',
					'menu_location' => 'settings',
					'old_name' => 'jp_rand_aff_set',
					'ui_style' => 'settings',
					'menu_position' => '0',
				),
		);

		return $settings_pod;

	}

	/**
	 * Creates the two Pods
	 *
	 * @param $name
	 *
	 * @return int Pod ID
	 */
	private function create_pod( $name ) {

		$api = $this->pod_api( $name  );
		if ( $name === JP_RAND_AFF_SET_POD ) {
			$pod_id = $api->save_pod( $this->settings_pod() );
		}
		else {
			$pod_id = $api->save_pod( $this->main_pod() );
		}

		$lc_name = strtolower( $name );
		update_option( "{$lc_name}_pod_id", $pod_id );

		$fields = $this->fields( $name, $pod_id );

		foreach( $fields as $field ) {
			$api->save_field( $field );
		}

		return $pod_id;

	}

	/**
	 * Get the fields for the two Pods.
	 *
	 * @param $name
	 * @param $pod_id
	 *
	 * @return mixed
	 */
	private function fields( $name, $pod_id ) {
		if ( $name === JP_RAND_AFF_MAIN_POD ) {
			return $this->main_pod_fields( $pod_id );
		}

		return $this->settings_pod_fields( $pod_id );

	}

	/**
	 * Main Pod fields
	 *
	 * @param $pod_id
	 *
	 * @return mixed
	 */
	private function main_pod_fields( $pod_id ) {
		//start $weight value at 30 to make sure we are after default fields.
		$weight = 30;

		$img_options = array(
			'required' => '1',
			'file_format_type' => 'single',
			'file_uploader' => 'attachment',
			'file_attachment_tab' => 'upload',
			'file_edit_title' => '1',
			'file_linked' => '1',
			'file_limit' => '0',
			'file_restrict_filesize' => '10MB',
			'file_type' => 'images',
			'file_add_button' => 'Add File',
			'file_modal_title' => 'Attach an Image',
			'file_modal_add_button' => 'Add Image',
			'pick_format_type' => 'single',
			'file_allowed_extensions' => '',
		);
		$img[ 'img_sqr' ] = array(
					'name' => 'img_sq',
					'label' => 'Image Square',
					'description' => 'A square image for the affiliate link.',
		);
		$img[ 'img_rct' ] = array(
			'name' => 'img_rct',
			'label' => 'Image Rectangle',
			'description' => 'A rectangular image for the affiliate link.',
		);

		foreach( $img as $field => $settings ) {
			//add unique settings for the field
			$fields[ $field ] = $settings;

			//set type
			$fields[ $field ][ 'type' ] = 'file';

			//set weight
			$fields[ $field ][ 'weight' ] = $weight;

			//add the non-unique options
			$fields[ $field ][ 'options' ] = $img_options;

			//increment weight variable
			$weight++;
		}

		//the link field
		$fields[ 'link' ] = array (
				'id' => 11,
				'name' => 'link',
				'label' => 'Link',
				'description' => 'Link for the affiliate',
				'type' => 'website',
				'options' =>
					array (
						'required' => '1',
					),
				'weight' => $weight,
		);

		//increment weight variable
		$weight++;

		//the description field
		$fields[ 'desc' ] = array(
			'name' => 'desc',
			'label' => 'Description',
			'description' => 'A short description of the affiliate.',
			'weight' => $weight,
			'type'	=> 'text',
			'options' => array(
				'text_max_length' => '140',
			),
		);

		//add pod id and name to field arrays
		foreach( $fields as $field => $value ) {
			$fields[ $field ][ 'pod_id' ] = $pod_id;
			$fields[ $field ][ 'pod_name' ] = JP_RAND_AFF_SET_POD;
		}

		return $fields;

	}

	/**
	 * Settings Pod fields
	 *
	 * @param $pod_id
	 *
	 * @return mixed
	 */
	private function settings_pod_fields( $pod_id ) {
		$weight = 0;

		$mobble_notice = __( 'Note: If Mobble mobile detection plugin is not installed, tablets will use phone settings.', 'jp-rand-aff' );

		//non-unique options for the per_post fields
		$per_page_options =  array (
				'required' => '0',
				'number_format_type' => 'slider',
				'number_format' => 'i18n',
				'number_decimals' => '0',
				'number_step' => '1',
				'number_min' => '1',
				'number_max' => '3',
		);

		//unique options for each per_page field
		$per_page[ 'per_page_d' ] = array(
			'name' => 'per_page_d',
			'default' => '3',
			'label' => 'Links Per Page- Desktop',
			'description' => 'Number of affiliate links to show per page on desktop.',
		);
		$per_page[ 'per_page_t' ] = array(
			'name' => 'per_page_t',
			'default' => '3',
			'label' => 'Links Per Page- Tablets',
			'help' => $mobble_notice,
			'description' => 'Number of affiliate links to show per page on tablets.',
		);
		$per_page[ 'per_page_p' ] = array(
			'name' => 'per_page_p',
			'default' => '1',
			'label' => 'Links Per Page- Phone',
			'description' => 'Number of affiliate links to show per page on phone.',
		);

		//add per page options to fields
		foreach ( $per_page as $field => $settings ) {
			//add unique settings for the field
			$fields[ $field ] = $settings;

			//set type
			$fields[ $field ][ 'type' ] = 'number';

			//set weight
			$fields[ $field ][ 'weight' ] = $weight;

			//add the non-unique options
			$fields[ $field ][ 'options' ] = $per_page_options;

			//increment weight variable
			$weight++;

		}

		//Settings for the use headlines fields
		$headline_use[ 'use_headline_d' ] = array (
			  'name' => 'use_headline_d',
			  'label' => 'Use Headline On Desktop?',
			  'default' => '1',
		);
		$headline_use[ 'use_headline_t' ] = array (
			'name' => 'use_headline_t',
			'label' => 'Use Headline On Tablets?',
			'help' => $mobble_notice,
			'default' => '1',
		);
		$headline_use[ 'use_headline_d' ] = array (
			'name' => 'use_headline_d',
			'label' => 'Use Headline On Phones?',
			'default' => '1',
		);

		foreach( $headline_use as $field => $settings ) {
			//add unique settings for the field
			$fields[ $field ] = $settings;

			//set type
			$fields[ $field ][ 'type' ] = 'boolean';

			//set weight
			$fields[ $field ][ 'weight' ] = $weight;


			//increment weight variable
			$weight++;

		}

		//headline
		$fields[ 'headline' ] = array(
			'name' => 'headline',
			'label' => 'Headline For The Section',
			'default' => 'Affiliate Links',
			'weight' => $weight,
			'type'	=> 'text',
			'options' => array(
				'text_max_length' => '140',
			),
		);

		//add pod id and name to field arrays
		foreach( $fields as $field => $value ) {
			$fields[ $field ][ 'pod_id' ] = $pod_id;
			$fields[ $field ][ 'pod_name' ] = JP_RAND_AFF_SET_POD;
		}

		return $fields;

	}

	/**
	 * Get PodsAPI instance.
	 *
	 * @param string $pod Defaults to 'main' for main Pod. Else settings Pod.
	 *
	 * @return PodsAPI
	 */
	private function pod_api( $pod_name ) {

		return pods_api( $pod_name );

	}

	/**
	 * Post create Pod message
	 *
	 * @todo Fail message.
	 * @param $main_pod
	 * @param $settings_pod
	 */
	private function post_create_message( $main_pod, $settings_pod ) {

		$format = '<div id="message" class="error"><p>Succefully created the main Pod with the ID %1$d and the settings Pod with the ID %2$d</p></div>';
		echo sprintf( __( $format, 'jp-rand-aff' ), $main_pod, $settings_pod );

	}

	/**
	 * Activates the Advanced Content Type Component
	 */
	private function activate_act_component(){
		$component_settings = PodsInit::$components->settings;
		$component_settings['components']['advanced-content-types'] = array();

		update_option( 'pods_component_settings', json_encode($component_settings));
	}

	/**
	 * Set an option to mark that the plugin was setup
	 */
	private function mark_setup() {
		$value = array(
			'setup-complete' 	=> true,
			'plugin-version'	=> JP_RAND_AFF_VERSION,
			'pods-version'		=> PODS_VERSION,
		);

		update_option( 'jp_rand_aff_setup', $value );

	}

	/**
	 * Add our template
	 */
	private function add_template() {
		$template_name = apply_filters( 'jp_rand_aff_output_template', 'jp_rand_aff_output_template' );

		//try and load the template. If it doesn't exist, this will return null
		if ( is_null( get_page_by_title( $template_name, 'OBJECT', '_pods_template' ) ) ) {
			$post = array(
				'post_type' 	=> '_pods_template',
				'post_content'	=> '@TODO AFF CONTENT',
			);

			wp_insert_post( $post );
		}
	}


} 
