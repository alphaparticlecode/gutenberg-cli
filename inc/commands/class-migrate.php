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
		$count = 0;

		$approved_tags = [
			'h1'  => '<!-- wp:heading {"level":1} -->**<!-- /wp:heading -->',
			'h2'  => '<!-- wp:heading -->**<!-- /wp:heading -->',
			'h3'  => '<!-- wp:heading {"level":3} -->**<!-- /wp:heading -->',
			'h4'  => '<!-- wp:heading {"level":4} -->**<!-- /wp:heading -->',
			'h5'  => '<!-- wp:heading {"level":5} -->**<!-- /wp:heading -->',
			'h6'  => '<!-- wp:heading {"level":6} -->**<!-- /wp:heading -->',
		];

		if ( isset( $assoc_args['ids'] ) ) {
			$post_ids = array_map('intval', explode(',', $assoc_args['ids'] ) );
		}
		else {
			$all_posts = new \WP_Query( 
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'fields'         => 'ids',
					'posts_per_page' => -1,
				)
			);

			$post_ids = $all_posts->posts;
		}

		$progress = \WP_CLI\Utils\make_progress_bar( 'Migrating posts', count($post_ids) );

		foreach( $post_ids as $post_id ) {
			$post = get_post( $post_id );

			$content = apply_filters( 'the_content', $post->post_content );

			$DOM = new \DOMDocument;
			$DOM->loadHTML( $content );

			foreach( $approved_tags as $tag => $wrapper ) {
				$elements = $DOM->getElementsByTagName( $tag );

				foreach( $elements as $element ) {
					$pattern = '/<.*' . $element->nodeValue . '<\/.*>/';
					preg_match( $pattern, $content, $matches );

					if( isset( $matches[0] ) ) {
						$text = str_replace( '**', $matches[0], $approved_tags[$tag] );
						$content = str_replace( $matches[0], $text, $content );
					}
				}
			}

			wp_update_post( array(
				'ID'           => $post_id,
				'post_content' => $content
			) );

			WP_CLI::log( __( 'Post ID: ' . $post_id . ' has been migrated.', 'gutenberg-cli' ) );
			$progress->tick();
		}

		$progress->finish();
		WP_CLI::success( __( $count . ' post(s) migrated. Migration complete.', 'gutenberg-cli' ) );
	}
}

WP_CLI::add_command( 'gutenberg migrate', __NAMESPACE__ . '\\Migrate' );