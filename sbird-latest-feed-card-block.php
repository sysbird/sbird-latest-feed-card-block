<?php
/**
 * Plugin Name:       sBird Latest Feed Card Block
 * Description:       Display the latest entry from an external RSS feed.
 * Version:           1.0.1
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            sysbird
 * Author URI:        https://profiles.wordpress.org/sysbird/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:      sbird-latest-feed-card-block
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function sbird_latest_feed_card_block_init() {
	$build_dir = __DIR__ . '/build';
	if ( file_exists( __DIR__ . '/block.json' ) ) {
		$build_dir = __DIR__;
	}
	$manifest = $build_dir . '/blocks-manifest.php';

	if ( file_exists( $build_dir . '/block.json' ) ) {
		register_block_type( $build_dir );
		return;
	}
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) && file_exists( $manifest ) ) {
		wp_register_block_types_from_metadata_collection( $build_dir, $manifest );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) && file_exists( $manifest ) ) {
		wp_register_block_metadata_collection( $build_dir, $manifest );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = file_exists( $manifest ) ? require $manifest : array();
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( "{$build_dir}/{$block_type}" );
	}
}
add_action( 'init', 'sbird_latest_feed_card_block_init' );

/**
 * Ensure dynamic rendering is wired for WordPress versions that ignore `render` in block.json.
 *
 * @param array $settings Block settings.
 * @param array $metadata Block metadata.
 *
 * @return array
 */
function sbird_latest_feed_card_block_filter_metadata_settings( $settings, $metadata ) {
	if ( isset( $metadata['name'] ) && 'sysbird/sbird-latest-feed-card-block' === $metadata['name'] ) {
		$settings['render_callback'] = 'sbird_latest_feed_card_block_render';
	}

	return $settings;
}
add_filter( 'block_type_metadata_settings', 'sbird_latest_feed_card_block_filter_metadata_settings', 10, 2 );

/**
 * Enqueue front-end styles from a real file for both front-end and editor.
 */
function sbird_latest_feed_card_block_enqueue_block_style() {
	$candidates = array(
		array(
			'path' => __DIR__ . '/style.css',
			'url'  => plugins_url( 'style.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/build/style.css',
			'url'  => plugins_url( 'build/style.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/build/sbird-latest-feed-card-block/style.css',
			'url'  => plugins_url( 'build/sbird-latest-feed-card-block/style.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/build/editorStyle.css',
			'url'  => plugins_url( 'build/editorStyle.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/build/sbird-latest-feed-card-block/editorStyle.css',
			'url'  => plugins_url( 'build/sbird-latest-feed-card-block/editorStyle.css', __FILE__ ),
		),
	);

	foreach ( $candidates as $candidate ) {
		if ( file_exists( $candidate['path'] ) ) {
			wp_enqueue_style(
				'sbird-latest-feed-card-block-style',
				$candidate['url'],
				array(),
				filemtime( $candidate['path'] )
			);
			break;
		}
	}
}
add_action( 'enqueue_block_assets', 'sbird_latest_feed_card_block_enqueue_block_style' );

/**
 * Enqueue front-end styles inside the editor as well.
 */
function sbird_latest_feed_card_block_enqueue_editor_style() {
	$editor_candidates = array(
		array(
			'path' => __DIR__ . '/editor.css',
			'url'  => plugins_url( 'editor.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/index.css',
			'url'  => plugins_url( 'index.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/build/editor.css',
			'url'  => plugins_url( 'build/editor.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/build/sbird-latest-feed-card-block/editor.css',
			'url'  => plugins_url( 'build/sbird-latest-feed-card-block/editor.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/build/index.css',
			'url'  => plugins_url( 'build/index.css', __FILE__ ),
		),
		array(
			'path' => __DIR__ . '/build/sbird-latest-feed-card-block/index.css',
			'url'  => plugins_url( 'build/sbird-latest-feed-card-block/index.css', __FILE__ ),
		),
	);

	foreach ( $editor_candidates as $candidate ) {
		if ( file_exists( $candidate['path'] ) ) {
			wp_enqueue_style(
				'sbird-latest-feed-card-block-editor-style',
				$candidate['url'],
				array(),
				filemtime( $candidate['path'] )
			);
			break;
		}
	}
}
add_action( 'enqueue_block_editor_assets', 'sbird_latest_feed_card_block_enqueue_editor_style' );

/**
 * Render callback for the sBird Latest Feed Card Block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function sbird_latest_feed_card_block_render( $attributes ) {
	$render_file_candidates = array(
		__DIR__ . '/render.php',
		__DIR__ . '/build/sbird-latest-feed-card-block/render.php',
		__DIR__ . '/build/render.php',
	);
	$render_file = '';
	foreach ( $render_file_candidates as $candidate ) {
		if ( file_exists( $candidate ) ) {
			$render_file = $candidate;
			break;
		}
	}
	if ( '' === $render_file ) {
		return '';
	}

	$renderer = require $render_file;
	if ( is_callable( $renderer ) ) {
		return $renderer( $attributes );
	}

	return '';
}
