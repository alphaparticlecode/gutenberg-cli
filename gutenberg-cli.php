<?php
/**
 * Plugin Name: Gutenberg CLI
 * Plugin URI: https://alphaparticle.com
 * Description: A set of WP-CLI commands to support Gutenberg development
 * Version: 0.1
 * Author: Alpha Particle
 * Author URI: https://alphaparticle.com
 * Text Domain: gutenberg-cli
 *
*/

// Only load this plugin once and bail if WP CLI is not present
if (  ! defined( 'WP_CLI' ) ) {
	return;
}

define( 'GUTENBERG_CLI_COMMANDS_PATH', 'inc/commands/' );

require_once( GUTENBERG_CLI_COMMANDS_PATH . 'class-migrate.php' );