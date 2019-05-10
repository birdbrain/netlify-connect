<?php
/**
 * LittleBot Netlifly
 *
 * A class custome fields.
 *
 * @version   0.9.0
 * @category  Class
 * @package   LittleBotNetlifly
 * @author    Justin W Hall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register our meta fields.
 */
class LBN_Meta_Fields {

	/**
	 * Kick it off.
	 *
	 * @param object $plugin the parent class.
	 */
	function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->options = get_option( 'lb_netlifly' );
		$this->hooks();
	}

	/**
	 * Attach hooks.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'register_meta_fields' ) );
	}

	/**
	 * Register customer meta fields and show in REST.
	 *
	 * @return void
	 */
	public function register_meta_fields() {

		$args = array(
			'type'         => 'boolean',
			'description'  => 'Has this post been published to stage',
			'single'       => true,
			'show_in_rest' => true,
		);
		$stage_rest_args = array(
			'get_callback' => function ($post) {
				return get_post_meta($post['id'], 'published_production', true) === '1' ? true : false;
			},
		);
		$production_rest_args = array(
			'get_callback' => function ($post) {
				return get_post_meta($post['id'], 'published_production', true) === '1' ? true : false;
			},
		);

		foreach(array_keys($this->options['deploy_to_post_types']) as $post_type) {
			register_meta($post_type, 'published_stage', $args );
			register_rest_field($post_type, 'published_stage', $stage_rest_args);
			$args['description'] = 'Has this post been published to production';
			register_meta( $post_type, 'published_production', $args );
			register_rest_field($post_type, 'published_production', $production_rest_args);
		}

	}

}
