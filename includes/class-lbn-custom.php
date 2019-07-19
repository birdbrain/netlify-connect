<?php
/**
 * LittleBot Netlifly
 *
 * A class for adding the Netlify Status Badge to the admin bar.
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
 * Show Netlify Status Badge
 */
class LBN_Custom {

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
    add_filter( 'preview_post_link', array( $this, 'set_preview_link'), 10, 2 );
    add_filter( 'rest_prepare_revision', array( $this, 'setup_revision_response'), 10, 2 );
	}

  /**
	 * Point preview URL at Koval frontend
	 *
	 * @return void
	 */
	 public function set_preview_link( $link, $post ) {
		 $queried_object = get_queried_object();
		 $post_obj = get_post($post);
		 $production_url = array_key_exists('production_url', $this->options) ? $this->options['production_url'] : '';
		 return $production_url . '/'
			 . 'preview/'
			 . $post_obj->post_type
			 . '?preview_id=' . get_the_ID()
			 . '&posttype=' . $post_obj->post_type;
	 }

  /**
	 * Point preview URL at Koval frontend
	 *
	 * @return void
	 */
	 public function setup_revision_response( $response, $post ) {
		  // Remove default non-embeddable parent link
			$response->remove_link( rest_url( "/wp/v2/posts/{$post->post_parent}" ) );
			// Add embeddable parent link
			$response->add_link( 'parent', rest_url( "/wp/v2/posts/{$post->post_parent}?_embed" ), array(
				'embeddable' => true,
			) );

			// Add embeddable author data so that we can access these on Koval
			// through revisions API endpoint with _embed query param
			$response->add_link( 'author', rest_url( "/wp/v2/users/{$post->post_author}" ), array(
				'embeddable' => true,
			) );

			// Loop through all taxonomies and add embeddable links to REST API response
			// so that we can access these on Koval through revisions API endpoint with _embed query param
			$args = array(
			  'public'   => true,
				'show_in_rest' => true
			);
			$output = 'objects'; // or names
			$operator = 'and'; // 'and' => all args must match tax object; 'or' => only one arg needs to match tax object
			$taxonomies = get_taxonomies( $args, $output, $operator );
			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxonomy ) {
					$tax_rest_base = $taxonomy->rest_base;
					$rest_base = (!isset($tax_rest_base) || is_null($tax_rest_base)) ? $taxonomy->name : $tax_rest_base;
					$response->add_link( $rest_base , rest_url( "/wp/v2/" . $rest_base . "?post={$post->post_parent}" ), array(
						'taxonomy' => $taxonomy->name,
						'embeddable' => true,
					) );
				}
			}

			// Set custom revision API response fields
			$data = $response->data;
			$data['featured_media'] = (object) array( "url" => get_the_post_thumbnail_url($post->post_parent) );
			$response->set_data($data);

			// Ensures a REST response is a response object
			// Returns WP_Error if response is not an object
		  return rest_ensure_response( $response );
	 }
}
