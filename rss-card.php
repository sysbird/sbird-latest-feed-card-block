<?php
/**
 * Plugin Name:       RSS Card
 * Description:       Display the latest entry from an external RSS feed.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            sysbird
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rss-card
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
function rss_card_block_init() {
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'rss_card_block_init' );

/**
 * Ensure dynamic rendering is wired for WordPress versions that ignore `render` in block.json.
 *
 * @param array $settings Block settings.
 * @param array $metadata Block metadata.
 *
 * @return array
 */
function rss_card_filter_metadata_settings( $settings, $metadata ) {
	if ( isset( $metadata['name'] ) && 'sysbird/rss-card' === $metadata['name'] ) {
		$settings['render_callback'] = 'rss_card_render';
	}

	return $settings;
}
add_filter( 'block_type_metadata_settings', 'rss_card_filter_metadata_settings', 10, 2 );

/**
 * Render callback for the RSS Card block.
 *
 * @param array $attributes Block attributes.
 *
 * @return string
 */
function rss_card_render( $attributes ) {
	$render_file = __DIR__ . '/build/rss-card/render.php';
	if ( ! file_exists( $render_file ) ) {
		return '';
	}

	$renderer = require $render_file;
	if ( is_callable( $renderer ) ) {
		return $renderer( $attributes );
	}

	return '';
}
