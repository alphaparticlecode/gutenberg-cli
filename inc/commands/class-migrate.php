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
			'h4' => '<!-- wp:heading {"level":4} -->**<!-- /wp:heading -->'
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

			$count += 1;
		}

		WP_CLI::success( __( $count . ' post(s) migrated. Migration complete.', 'gutenberg-cli' ) );
	}
}

WP_CLI::add_command( 'gutenberg migrate', __NAMESPACE__ . '\\Migrate' );