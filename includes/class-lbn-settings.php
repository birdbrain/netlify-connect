<?php
/**
 * LittleBot settings pages
 *
 * @author      Justin W Hall
 * @category    Settings
 * @package     LittleBot Invoices/Settings
 * @version     0.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings & Settings page
 */
class LBN_Settings {
	/**
	 * Plugin options.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Kick it off.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	/**
	 * Create the options page
	 *
	 * @return void
	 */
	public function add_plugin_page() {
		add_options_page(
			'Settings Admin',
			'Netlify Connect',
			'manage_options',
			'netlify',
			array( $this, 'create_admin_page' )
		);
	}

	/**
	 * Renders the plugin options page.
	 *
	 * @return void
	 */
	public function create_admin_page() {
		// Set class property.
		$this->options = get_option( 'lb_netlifly' );

		?>
		<div class="wrap">
			<h1>Netlify Connect Settings</h1>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields.
				settings_fields( 'build_group' );
				do_settings_sections( 'netlify' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	/**
	 * Registers plugins options.
	 *
	 * @return void
	 */
	public function page_init() {
		register_setting(
			'build_group',
			'lb_netlifly',
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'production_section_id',
			'Production Environment',
			false,
			'netlify'
		);

		add_settings_field(
			'production_buildhook',
			'Production Build Hook',
			array( $this, 'prod_callback' ),
			'netlify',
			'production_section_id'
		);

		add_settings_field(
			'production_url',
			'Production URL',
			array( $this, 'prod_url_callback' ),
			'netlify',
			'production_section_id'
		);

		add_settings_field(
			'production_build_status_badge_url',
			'Production Status Badge URL',
			array( $this, 'production_build_status_badge_url_callback' ),
			'netlify',
			'production_section_id'
		);

		add_settings_section(
			'staging_section_id',
			'Staging Environment',
			false,
			'netlify'
		);

		add_settings_field(
			'stage_buildhook',
			'Staging Build Hook',
			array( $this, 'stage_callback' ),
			'netlify',
			'staging_section_id'
		);

		add_settings_field(
			'stage_url',
			'Staging URL',
			array( $this, 'stage_url_callback' ),
			'netlify',
			'staging_section_id'
		);

		add_settings_field(
			'staging_build_status_badge_url',
			'Staging Status Badge URL',
			array( $this, 'staging_build_status_badge_url_callback' ),
			'netlify',
			'staging_section_id'
		);
	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys.
	 */
	public function sanitize( $input ) {
		$new_input = array();
		if ( isset( $input['production_buildhook'] ) ) {
			$new_input['production_buildhook'] = sanitize_text_field( $input['production_buildhook'] );
		}

		if ( isset( $input['production_url'] ) ) {
			$new_input['production_url'] = sanitize_text_field( $input['production_url'] );
		}

		if ( isset( $input['stage_buildhook'] ) ) {
			$new_input['stage_buildhook'] = sanitize_text_field( $input['stage_buildhook'] );
		}

		if ( isset( $input['stage_url'] ) ) {
			$new_input['stage_url'] = sanitize_text_field( $input['stage_url'] );
		}

		if ( isset( $input['production_build_status_badge_url'] ) ) {
			$new_input['production_build_status_badge_url'] = sanitize_text_field( $input['production_build_status_badge_url'] );
		}

		if ( isset( $input['staging_build_status_badge_url'] ) ) {
			$new_input['staging_build_status_badge_url'] = sanitize_text_field( $input['staging_build_status_badge_url'] );
		}

		return $new_input;
	}

	/**
	 * Renders productions input option.
	 *
	 * @return void
	 */
	public function prod_callback() {
		printf(
			'<input type="text" id="prod_buildhook" name="lb_netlifly[production_buildhook]" value="%s" style="min-width:450px;"/>',
			isset( $this->options['production_buildhook'] ) ? esc_attr( $this->options['production_buildhook'] ) : ''
		);
	}

	/**
	 * Renders productions input option.
	 *
	 * @return void
	 */
	public function prod_url_callback() {
		printf(
			'<input type="text" id="production_url" name="lb_netlifly[production_url]" value="%s" style="min-width:450px;"/>',
			isset( $this->options['production_url'] ) ? esc_attr( $this->options['production_url'] ) : ''
		);
	}

	/**
	 * Renders stage input option.
	 *
	 * @return void
	 */
	public function stage_callback() {
		printf(
			'<input type="text" id="stage_buildhook" name="lb_netlifly[stage_buildhook]" value="%s" style="min-width:450px;" />',
			isset( $this->options['stage_buildhook'] ) ? esc_attr( $this->options['stage_buildhook'] ) : ''
		);
	}

	/**
	 * Renders stage input option.
	 *
	 * @return void
	 */
	public function stage_url_callback() {
		printf(
			'<input type="text" id="stage_url" name="lb_netlifly[stage_url]" value="%s" style="min-width:450px;" />',
			isset( $this->options['stage_url'] ) ? esc_attr( $this->options['stage_url'] ) : ''
		);
	}

	/**
	 * Renders stage input option.
	 *
	 * @return void
	 */
	public function staging_build_status_badge_url_callback() {
		printf(
			'<input type="text" id="staging_build_status_badge_url" name="lb_netlifly[staging_build_status_badge_url]" value="%s" style="min-width:450px;" />',
			isset( $this->options['staging_build_status_badge_url'] ) ? esc_attr( $this->options['staging_build_status_badge_url'] ) : ''
		);
	}

	/**
	 * Renders stage input option.
	 *
	 * @return void
	 */
	public function production_build_status_badge_url_callback() {
		printf(
			'<input type="text" id="production_build_status_badge_url" name="lb_netlifly[production_build_status_badge_url]" value="%s" style="min-width:450px;" />',
			isset( $this->options['production_build_status_badge_url'] ) ? esc_attr( $this->options['production_build_status_badge_url'] ) : ''
		);
	}

}
