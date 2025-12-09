<?php
/**
 * Custom Post Type commands for the command palette
 *
 * @package         CPT_Commands
 */

namespace CPT_Commands;

/**
 * Class to hold all implementation functions of the plugin
 */
class CPT_Commands {

	/**
	 * Link to repo
	 *
	 * @var string
	 */
	public static $support_link = 'https://github.com/shanto/cpt-commands/';

	/**
	 * Plugin entry-point
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
	}

	/**
	 * Registers static assets
	 *
	 * @return void
	 */
	public static function register_assets() {
		$asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

		wp_enqueue_script(
			'cpt-commands',
			plugin_dir_url( __FILE__ ) . 'build/index.js',
			array( 'wp-element', 'wp-data', 'wp-core-data', 'wp-commands' ),
			$asset_file['version'],
			true
		);

		$options = get_option(
			'cpt_commands_options',
			array(
				'ignored_post_types' => array(),
			)
		);

		wp_add_inline_script(
			'cpt-commands',
			'const CPT_COMMANDS_OPTIONS = ' . wp_json_encode( $options ) . ';'
		);
	}

	/**
	 * Sets up admin features
	 *
	 * @return void
	 */
	public static function admin_init() {
		register_setting(
			'cpt_commands_options_group',
			'cpt_commands_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => function ( $value ) {
					$value                       = (array) $value;
					$value['ignored_post_types'] = array_map( 'sanitize_text_field', $value['ignored_post_types'] ?? array() );
					return $value;
				},
				'default'           => array(
					'ignored_post_types' => array(),
				),
			)
		);
	}

	/**
	 * Registers admin menu
	 *
	 * @return void
	 */
	public static function admin_menu() {
		add_options_page(
			'CPT Commands',
			'CPT Commands',
			'manage_options',
			'cpt-commands-settings',
			array( __CLASS__, 'settings_page' )
		);
		add_filter( 'plugin_action_links_' . CPT_COMMANDS_PLUGIN_BASE, array( __CLASS__, 'settings_link' ) );
	}

	/**
	 * Renders the link for Settings

	 * @param mixed $links Links array with Settings added.
	 * @return array
	 */
	public static function settings_link( $links ) {
		$settings_url  = add_query_arg( 'page', 'cpt-commands-settings', get_admin_url() . 'admin.php' );
		$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . __( 'Settings', 'cpt-commands' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Renders the settings page
	 */
	public static function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$options    = get_option( 'cpt_commands_options', array() );
		$ignored    = $options['ignored_post_types'] ?? array();
		$post_types = array_filter(
			get_post_types( array( 'show_ui' => true ), 'objects' ),
			function ( $post_type ) use ( $ignored ) {
				return ! in_array( $post_type->name, array( 'post', 'page', 'navigation', 'block', 'attachment' ), true ) &&
				! preg_match( '/^acf-|^wp_|^boldblocks_|^simple_/', $post_type->name );
			}
		);
		include plugin_dir_path( __FILE__ ) . 'cpt-commands.html';
	}
}
