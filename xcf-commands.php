<?php
/**
 * Plugin Name:     XCF Commands
 * Plugin URI:      https://github.com/shanto/wp-xcf-commands
 * Description:     Site editor commands for custom post types
 * License:         GPL v2 or later
 * Author:          Shaan
 * Author URI:      https://github.com/shanto/
 * Text Domain:     xcf-commands
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         XCF_Commands
 */

namespace XCF_Commands;

!defined('WPINC') && die;

class XCF_Commands
{
    static function init() {
        add_action('enqueue_block_editor_assets', [__CLASS__, 'register_assets']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'register_assets']);
        add_action('admin_init', [__CLASS__, 'admin_init']);
        add_action('admin_menu', [__CLASS__, 'admin_menu']);
    }

    static function register_assets() {
        $current_screen = get_current_screen();
        if ( !method_exists( $current_screen, 'is_block_editor' ) || !$current_screen->is_block_editor() ) {
            return;
        }

        $asset_file = include plugin_dir_path( __FILE__ ) . 'build/index.asset.php';

        wp_enqueue_script(
            'xcf-commands',
            plugin_dir_url( __FILE__ ) . 'build/index.js',
            $asset_file['dependencies'],
            $asset_file['version'],
            true
        );
    }

    static function admin_init() {
        register_setting(
            'xcf_commands_options_group',     // settings group
            'xcf_commands_options',           // option name in wp_options
            [
                'type'              => 'array',
                'sanitize_callback' => function ( $value ) {
                    $value = (array) $value;
                    $value['ignored_post_types'] = array_map( 'sanitize_text_field', $value['ignored_post_types'] ?? [] );
                    return $value;
                },
                'default'           => [
                    'ignored_post_types' => [],
                ],
            ]
        );
    }

    static function admin_menu() {
        add_options_page(
            'XCF Commands',
            'XCF Commands',
            'manage_options',
            'xcf-commands-settings',
            [__CLASS__, 'xcf_commands_render_settings_page']
        );
    }

    static function xcf_commands_render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $options = get_option( 'xcf_commands_options', [] );
        $ignored = $options['ignored_post_types'] ?? [];
        $post_types = array_filter(get_post_types( [ 'show_ui' => true ], 'objects' ), function($post_type) use ($ignored) {
            return !in_array($post_type->name, ['post', 'page', 'navigation', 'block', 'attachment']) && !preg_match('/^acf-|^wp_|^boldblocks_/', $post_type->name);
        });
        return include(plugin_dir_path( __FILE__ ) . 'xcf-commands.html');
    }
}

add_action('plugins_loaded', [XCF_Commands::class, 'init']);