<?php
/**
 * LittleBot Netlifly
 *
 * A class for all plugin metaboxes.
 *
 * @version   0.9
 * @category  Class
 * @package   LittleBotNetlifly/Metaboxes
 * @author    Justin W Hall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Metabox.
 */
class LBN_Metaboxes {

	/**
	 * Parent plugin class.
	 *
	 * @var object
	 * @since 0.9.0
	 */
	protected $plugin = null;

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
		// Remove default publish metabox.
		add_action( 'do_meta_boxes', array( $this, 'remove_publish_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_deploy_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_publish_box' ) );
	}

	/**
	 * Remove default publish metabox. We'll add our own.
	 *
	 * @return void
	 */
	public function remove_publish_box() {
		remove_meta_box( 'submitdiv', array( array_keys($this->options['deploy_to_post_types']) ), 'side' );
	}
	
	/**
	 * Add a deploy metabox.
	 *
	 * @return void
	 */
	public function add_deploy_box() {
		add_meta_box(
			'lb_netlifly-deploy',
			__( 'Deploy', 'littlebot-netlify-deploy' ),
			array( $this, 'render_meta_box_content_deploy' ),
			array(),
			'side',
			'high'
		);
	}

	/**
	 * Add a custom publishing metabox.
	 *
	 * @return void
	 */
	public function add_publish_box() {
		add_meta_box(
			'lb_netlifly',
			__( 'Visibility', 'littlebot-netlify' ),
			array( $this, 'render_meta_box_content' ),
			array_keys($this->options['deploy_to_post_types']),
			'side',
			'high'
		);
	}

	
	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content_deploy( $post ) {
		include_once $this->plugin->path . 'views/view-metabox-publish-deploy.php';
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
		include_once $this->plugin->path . 'views/view-metabox-publish.php';
	}
}
