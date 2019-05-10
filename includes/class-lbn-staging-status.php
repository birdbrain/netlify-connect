<?php
/**
 * LittleBot Netlifly
 *
 * A class for adding a custom staging status to pages and posts
 *
 * @version   0.9
 * @category  Class
 * @package   LittleBotNetlifly
 * @author    Justin W Hall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Staging Status to pages and posts if staging envionment exists
 */
class LBN_Staging_Status {

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
		add_action( 'init', array( $this, 'register_status' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'my_custom_status_add_in_quick_edit' ) );
		add_action( 'admin_footer-post.php', array( $this, 'my_custom_status_add_in_post_page' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'my_custom_status_add_in_post_page' ) );
		add_action( 'pre_get_posts', array( $this, 'only_users_todos' ) );
	}

	/**
	 * Register Staging custom status
	 *
	 * @return void
	 */
	public function register_status() {
		if($this->options['stage_buildhook'] && $this->options['stage_url'] && $this->options['staging_build_status_badge_url']) {
			register_post_status( 'staging', array(
				/* WordPress built in arguments. */
				'label'                       => __( 'Staging', 'text_domain' ),
				'label_count'                 => _n_noop( 'Staging <span class="count">(%s)</span>', 'Staging <span class="count">(%s)</span>', 'text_domain' ),
				'public'                      => true,
				'show_in_admin_all_list'      => true,
				'show_in_admin_status_list'   => true,
				/* WP Statuses specific arguments. */
				'post_type'                   => array( 'post', 'page' ), // Only for posts!
				'show_in_metabox_dropdown'    => true,
				'show_in_inline_dropdown'     => true,
				'show_in_press_this_dropdown' => true,
				'labels'                      => array(
					'metabox_dropdown' => __( 'Staging',        'text_domain' ),
					'inline_dropdown'  => __( 'Staging',        'text_domain' ),
				),
				'dashicon'                    => 'dashicons-archive',
		  ));
		}
	}

	/**
	 * Add status to posts and pages quick edit status dropdowns
	 *
	 * @return void
	 */
	public function my_custom_status_add_in_quick_edit() {
		if($this->options['stage_buildhook'] && $this->options['stage_url'] && $this->options['staging_build_status_badge_url']) {
			echo "<script>
      jQuery(document).ready( function() {
        jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"staging\">Staging</option>' );
      });
      </script>";
		}
 	}

	/**
	 * Add status to posts and pages status dropdowns
	 *
	 * @return void
	 */
	public function my_custom_status_add_in_post_page() {
    global $post;
    $label    = " Staging";
		if($this->options['stage_buildhook'] && $this->options['stage_url'] && $this->options['staging_build_status_badge_url']) {
			if ( $post->post_status == 'staging' ) {
					echo "<script>
					jQuery(document).ready( function() {
						jQuery( 'select[name=\"post_status\"]' ).append( '<option selected value=\"staging\"> {$label}</option>' );
						jQuery( '.misc-pub-section #post-status-display' ).html( \"{$label}\");
					});
					</script>";
					return;
			}
			echo "<script>
			jQuery(document).ready( function() {
				jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"staging\"> {$label}</option>' );
			});
			</script>";
		}
  }

	/**
	 * Include staging status in rest api
	 *
	 * @return void
	 */
	public function only_users_todos( $query ) {
		if($this->options['stage_buildhook'] && $this->options['stage_url'] && $this->options['staging_build_status_badge_url']) {
			if ( !is_admin() && ($query->get( 'post_type' ) === 'page' || $query->get( 'post_type' ) === 'post' ) ) {
	      $query->set('post_status', array('publish','staging'));
	    }
		}
  }
}
