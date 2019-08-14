<?php

namespace GutenbergCLI\Commands;

use WP_CLI;

class Migrate extends \WP_CLI_Command {

	/**
	 * Attempts to migrate posts from the Classic Editor post_content format into blocks
	 *
	 * ## OPTIONS
	 *
	 * <ids>
	 * : (optional) A list of post IDs 
	 *
	 * ## EXAMPLES
	 *
	 *   wp gutenberg migrate --ids=1,2,3
	 *
	 * @synopsis [--ids=<post_ids>]
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */

	public function __invoke( $args = array(), $assoc_args = array() ) {
		$post_ids = array();

		if ( isset( $assoc_args['ids'] ) ) {
			$post_ids = array_map('intval', explode(',', $assoc_args['ids'] ) );
		}

		WP_CLI::success( __( 'Command successfully registered.', 'gutenberg-cli' ) );
	}
}

WP_CLI::add_command( 'gutenberg migrate', __NAMESPACE__ . '\\Migrate' );