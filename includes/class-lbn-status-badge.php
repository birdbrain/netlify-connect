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
class LBN_Status_Badge {

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
    add_action( 'admin_bar_menu', array( $this, 'add_status_badge' ), 100 );
		add_action( 'admin_footer', array( $this, 'refresh_status_badge' ) );
		add_action( 'admin_footer', array( $this, 'register_rebuild_click_triggers' ) );
	}

	/**
	 * Remove default publish metabox. We'll add our own.
	 *
	 * @return void
	 */
	public function add_status_badge($admin_bar) {
		$production_build_status_badge_url = array_key_exists( 'production_build_status_badge_url', $this->options ) ? $this->options['production_build_status_badge_url'] : '';
    $staging_build_status_badge_url = array_key_exists('staging_build_status_badge_url',$this->options) ? $this->options['staging_build_status_badge_url'] : '';
    $production_buildhook = array_key_exists( 'production_buildhook', $this->options ) ? $this->options['production_buildhook'] : '';
    $stage_buildhook = array_key_exists( 'stage_buildhook', $this->options) ? $this->options['stage_buildhook'] : '';
    $production_url = array_key_exists( 'production_url',$this->options) ? $this->options['production_url'] : '';
    $stage_url = array_key_exists( 'stage_url', $this->options ) ? $this->options['stage_url'] : '';
		$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    if ($production_build_status_badge_url && $production_buildhook && $production_url) {
      $admin_bar->add_menu( array(
          'id'    => 'netlify--prod-build-status-badge',
          'title' => '<img id="netlify--production-build-status-badge" src=' . $production_build_status_badge_url . ' />',
          'href'  => '#',
          'meta'  => array(
              'title' => __('Production Build Status'),
          ),
      ));
			$admin_bar->add_menu( array(
        'parent'    => 'netlify--prod-build-status-badge',
        'id'    => 'netlify--preview-production',
        'title'     => 'Preview',
        'href'  => $production_url . '?prev=true',
        'meta'  => array(
          'target' => '_blank',
					'title' => __('Preview'),
        )
      ));
			$admin_bar->add_menu( array(
          'parent'    => 'netlify--prod-build-status-badge',
					'id' 		=> 'netlify--rebuild-production',
          'title' => 'Rebuild',
          'href'  => add_query_arg('rebuild_production', 0, $current_url),
          'meta'  => array(
              'title' => __('Rebuild'),
          ),
      ));
    }
    if ($staging_build_status_badge_url && $stage_buildhook && $stage_url) {
      $admin_bar->add_menu( array(
          'id'    => 'netlify--staging-build-status-badge',
          'title' => '<span style="color:#eee;margin-right:10px;">Staging: </span><img id="netlify--staging-build-status-badge" src=' . $staging_build_status_badge_url . ' />',
          'href'  => '#',
          'meta'  => array(
              'title' => __('Staging Build Status'),
          ),
      ));
			$admin_bar->add_menu( array(
        'parent'    => 'netlify--staging-build-status-badge',
        'id'    => 'netlify--visit-staging',
        'title'     => 'Visit Site',
        'href'  => $stage_url,
        'meta'  => array(
          'target' => '_blank',
					'title' => __('Visit staging site'),
        )
      ));
			$admin_bar->add_menu( array(
          'parent'    => 'netlify--staging-build-status-badge',
					'id' 		=> 'netlify--rebuild-staging',
          'title' => 'Trigger Rebuild',
          'href'  => add_query_arg('rebuild_staging', 0, $current_url),
          'meta'  => array(
              'title' => __('Rebuild staging site'),
          ),
      ));
    }
	}

	/**
	 * Refresh status badge every few seconds
	 *
	 * @return void
	 */
	public function refresh_status_badge() {
		echo '
			<style>
				#wp-admin-bar-netlify--prod-build-status-badge > a, #wp-admin-bar-netlify--staging-build-status-badge > a {
				  display: flex!important;
				  align-items: center;
				  justify-content: center;
				}
				#netlify--production-build-status-badge, #netlify--staging-build-status-badge {
				  height: 18px;
				}
			</style>
			<script>
				window.onload = function() {
					var productionBadge = document.getElementById("netlify--production-build-status-badge");
					var stagingBadge = document.getElementById("netlify--staging-build-status-badge");
					function updateBadges() {
						if (productionBadge) productionBadge.src = productionBadge.src.split("?")[0] + "?" + new Date().getTime();
						if (stagingBadge) stagingBadge.src = stagingBadge.src.split("?")[0] + "?" + new Date().getTime();
					}
					setInterval(updateBadges, 10000);
				}
			</script>
		';
	}

	/**
	 * Register rebuild click events
	 *
	 * @return void
	 */
	public function register_rebuild_click_triggers() {
		$production_buildhook = $this->options['production_buildhook'];
    $stage_buildhook = $this->options['stage_buildhook'];

		if (is_admin() && isset($_GET['rebuild_staging']) ) {
		  if ($stage_buildhook) {
		    wp_remote_post( $stage_buildhook );
		  }
		}

		if (is_admin() && isset($_GET['rebuild_production']) ) {
		  if ($production_buildhook) {
		    wp_remote_post( $production_buildhook );
		  }
		}
	}
}
