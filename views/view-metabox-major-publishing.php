<?php
/**
 * Renders publish major publishing elements
 *
 * @package littlebot_netlifly/views
 */

if ( $has_prod_hook || $has_stage_hook ) : ?>
	<h4 style="margin-bottom: 10px;"><?php esc_html_e( 'Visible on', 'lbn-netlifly' ); ?>:</h4>
	<?php if ($has_stage_hook): ?>
	<div><label><input data-env="stage" type="checkbox" name="published_stage" <?php if ( $published_stage ) : ?>checked<?php endif; ?>>Stage</label></div>
<?php endif; ?>
	<div><label><input data-env="production" type="checkbox" name="published_production" <?php if ( $published_production ) : ?>checked<?php endif; ?>>Production</label></div>
<?php else : ?>
	<div class="no-hooks">
		<?php
			$url = get_site_url() . '/wp-admin/options-general.php?page=netlify';
			echo sprintf( wp_kses( __( 'Opps, you need to <a href="%s">set a production or stage build hook</a> for this plugin to work.', 'netlify' ),
				array( 'a' => array( 'href' => array() ) ) ), esc_url( $url )
			);
		?>
	</div>
<?php endif; ?>
