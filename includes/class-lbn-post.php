<?php
/**
 * LittleBot Netlifly
 *
 * A class for all plugin metaboxs.
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
 * Hooks saving and updating posts.
 */
class LBN_Post {

	/**
	 * Parent plugin class.
	 *
	 * @var object
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
		// add_action( 'save_post', array( $this, 'save_post' ), 10, 3 );
		// add_action( 'delete_post', array( $this, 'save_post' ), 10, 3 );
		add_action( 'wp_insert_post_data', array( $this, 'insert_post' ), 10, 3 );
		// add_action( 'manage_posts_columns', array( $this, 'show_publish_status' ), 10, 3 );
		// add_action( 'manage_posts_custom_column', array( $this, 'build_column' ), 30, 3 );
		// add_action( 'manage_pages_columns', array( $this, 'show_publish_status' ), 10, 3 );
		// add_action( 'manage_pages_custom_column', array( $this, 'build_column' ), 30, 3 );
		add_action( 'admin_head-post-new.php', array( $this, 'replace_edit_slug_url'));
		add_action( 'admin_head-post.php', array( $this, 'replace_edit_slug_url'));
		add_action( 'edit_form_after_title', array( $this, 'add_stage_url_permalink'));
		add_action( 'admin_head-post-new.php', array( $this, 'remove_view_post_link') );
		add_action( 'admin_head-post.php', array( $this, 'remove_view_post_link') );
	}

	/**
	 * Add publish column
	 *
	 * @param array $columns Post list columns.
	 *
	 * @return array
	 */
	public function show_publish_status( $columns ) {
		return array_merge( $columns,
			array(
				'Published' => __( 'Visible on', 'netlify' ),
			)
		);
	}

	/**
	 * Add columns to invoice and estimates
	 *
	 * @param  array $columns post screen columns.
	 * @param  int   $post_id   the post id.
	 * @return void
	 */
	public function build_column( $columns, $post_id ) {
		if ($columns === 'Published') {
			$stage_status = (bool) get_post_meta( $post_id, 'published_stage', true );
			$prod_status = (bool) get_post_meta( $post_id, 'published_production', true );

			if ( $prod_status ) {
				echo sprintf( '<div>%s</div>', esc_html( 'Production', 'netlify' ) );
			}

			if ( $stage_status ) {
				echo sprintf( '<div>%s</div>', esc_html( 'Stage', 'netlify' ) );
			}

			if ( ! $stage_status && ! $prod_status ) {
				echo '—';
			}
		}
	}

	/**
	 * Updates "deploy" status on post update
	 *
	 * @param object $data the $_POST request.
	 * @param object $post the post being updated.
	 *
	 * @return object
	 */
	public function insert_post( $data, $post ) {
		if (
			isset( $post['post_status'] ) && 'auto-draft' === $post['post_status'] ||
			defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
			defined( 'DOING_AJAX' ) && DOING_AJAX
			) {
			return $data;
		}

		// If it's a deploy, make sure it's set to publish.
		if ( isset( $post['deploy'] ) ) {
			$data['post_status'] = 'publish';
		}

		return $data;
	}

	/**
	 * Save post callback
	 *
	 * @param int     $post_id The post ID.
	 * @param object  $post    The post object.
	 * @param boolean $update  Is this an update.
	 * @return void
	 */
	public function save_post( $post_id, $post = null, $update = false ) {
		// Bail if it's a auto-draft, we're doing auto save or ajax.
		if (
			isset( $post->post_status ) && 'auto-draft' === $post->post_status ||
			defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ||
			defined( 'DOING_AJAX' ) && DOING_AJAX ||
			( ! $update )
			) {
			return;
		}

		// Get netlify buildhooks from plugin settings
		$production_buildhook = array_key_exists('production_buildhook', $this->options) ? $this->options['production_buildhook'] : '';
    $stage_buildhook = array_key_exists('stage_buildhook', $this->options) ? $this->options['stage_buildhook'] : '';

		// Deploy to staging if buildhook exists
		if ($stage_buildhook) {
			wp_remote_post( $stage_buildhook );
		}
		// Deploy to production if buildhook exists
		if ($production_buildhook) {
			wp_remote_post( $production_buildhook );
		}
	}

	public function testfun() {
	   echo "Your test function on button click is working";
	}


	/* ------------------------------------------------------------------------
		Hide 'View Post' link after save
	------------------------------------------------------------------------ */
	public function remove_view_post_link() {
	    echo '<style type="text/css">.notice-success a { display: none!important; }</style>';
	}

	/* ------------------------------------------------------------------------
		Replace edit post slug wp url with frontend (prod and staging) urls
	------------------------------------------------------------------------ */

	public function replace_edit_slug_url(){
		$prod_url = array_key_exists('production_url', $this->options) ? $this->options['production_url'] : '';
		$stage_url = array_key_exists('stage_url', $this->options) ? $this->options['stage_url'] : '';
		$wp_url_escaped = str_replace('/', '\/', get_site_url());
		if ( empty($prod_url) && empty($stage_url) ) return null;
		// If prod_url exists
		if ( !empty($prod_url) ) {
			echo '<script>jQuery(document).ready(function(){
				jQuery("#edit-slug-box").html(function(index,html){
					return html.replace(/' . $wp_url_escaped . '/g,"' . $prod_url . '");
				});
			});</script>';
		}
		// If stage_url exists
		if ( !empty($stage_url) ) {
			echo '<script>jQuery(document).ready(function(){
				jQuery("#stage-url").html(function(index,html){
					return html.replace(/' . $wp_url_escaped . '/g,"' . $stage_url . '");
				});
			});</script>';
		}
	}


	public function add_stage_url_permalink( $post_object ) {
		$stage_status = (bool) get_post_meta( $post_object->ID, 'published_stage', true );
		$stage_url = array_key_exists('stage_url', $this->options) ? $this->options['stage_url'] : '';
		if ($stage_status && $stage_url) {
			echo '
			<div id="edit-slug-box">
			<strong>Stage URL:</strong> <span class="sample-permalink" id="stage-url"><a href="' . get_permalink() . '">' . get_permalink() . '</a></span>
			</div>';
		}
	}


}
