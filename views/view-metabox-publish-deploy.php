<?php
/**
 * Renders publish metabox
 *
 * @package littlebot_netlifly/views
 */

// Do we have build hooks?
$lb_netlifly = get_option( 'lb_netlifly' );
$has_prod_hook = (bool) $lb_netlifly['production_buildhook'];
$has_stage_hook = (bool) $lb_netlifly['stage_buildhook'];

$post_type = $post->post_type;
$post_type_object = get_post_type_object( $post_type );
$can_publish = current_user_can( $post_type_object->cap->publish_posts );
?>

<?php if ($can_publish): ?>

<div id="deploypost" class="post-box submitbox">
	<script>
		jQuery(document).ready(function(){
			jQuery('#stage-deploy-button').click(function(event) {
				jQuery('#trigger-stage-deploy').val('true');
				var confirm = window.confirm("You are about to deploy all selected content to staging, are you sure you want to continue?");
				if (!confirm) {
					jQuery('#trigger-stage-deploy').val('');
					return event.preventDefault();
				}
				jQuery('#stage-deploy-button').unbind('click');
			});
			jQuery('#prod-deploy-button').click(function(event) {
				jQuery('#trigger-prod-deploy').val('true');
				var confirm = window.confirm("You are about to deploy all selected content to production, are you sure you want to continue?");
				if (!confirm) {
					jQuery('#trigger-prod-deploy').val('');
					return event.preventDefault();
				}
				jQuery('#prod-deploy-button').unbind('click');
			});
		});
	</script>
	<div id="lb-publishing-action">

		<p>Select an environment to deploy to</p>

		<div id="major-publishing-actions" style="display:flex; justify-content: space-between">
			<?php if ($has_stage_hook): ?>
				<div class="stage-deploy">
					<input name="trigger-stage-deploy" type="hidden" id="trigger-stage-deploy" value="" />
					<input name="save" type="submit" class="button button-primary button-large" id="stage-deploy-button" value="<?php esc_attr_e( 'Stage' ) ?>" />
				</div>
			<?php endif; ?>
			<?php if ($has_prod_hook): ?>
				<div class="prod-deploy">
					<input name="trigger-prod-deploy" type="hidden" id="trigger-prod-deploy" value="" />
					<input name="save" type="submit" class="button button-primary button-large" id="prod-deploy-button" value="<?php esc_attr_e( 'Production' ) ?>" />
				</div>
			<?php endif; ?>
		</div>

	</div>
	<div class="clear"></div>
</div>

<?php endif; ?>
