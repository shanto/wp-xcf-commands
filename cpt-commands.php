<?php
/**
 * Plugin Name:     CPT Commands
 * Plugin URI:      https://github.com/shanto/cpt-commands
 * Description:     Custom Post Type commands for the command palette
 * License:         GPL v2 or later
 * Author:          Shaan
 * Author URI:      https://github.com/shanto/
 * Text Domain:     cpt-commands
 * Domain Path:     /languages
 * Version:         0.1.4
 *
 * @package         CPT_Commands
 */

namespace CPT_Commands;

! defined( 'WPINC' ) && die;

define( 'CPT_COMMANDS_PLUGIN_BASE', plugin_basename( __FILE__ ) );

require_once __DIR__ . '/class-cpt-commands.php';

add_action( 'plugins_loaded', array( \CPT_Commands\CPT_Commands::class, 'init' ) );
