<?php
/**
 * Server-side rendering for the sBird Latest Feed Card Block.
 *
 * @package sbird-latest-feed-card-block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'sbird_latest_feed_card_block_get_feed_data' ) ) {
	/**
	 * Retrieve the latest item from the provided feed URL.
	 *
	 * @param string $feed_url RSS feed URL.
	 *
	 * @return array|WP_Error
	 */
	function sbird_latest_feed_card_block_get_feed_data( $feed_url ) {
		if ( empty( $feed_url ) ) {
			return new WP_Error( 'sbird_latest_feed_card_block_empty_url', __( 'No Feed URL has been set.', 'sbird-latest-feed-card-block' ) );
		}

		$cache_key = 'sbird_latest_feed_card_block_' . md5( $feed_url );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		require_once ABSPATH . WPINC . '/feed.php';
		$feed = fetch_feed( $feed_url );
		if ( is_wp_error( $feed ) ) {
			return new WP_Error( 'sbird_latest_feed_card_block_invalid_feed', __( 'Failed to fetch the feed.', 'sbird-latest-feed-card-block' ) );
		}

		$items = $feed->get_items( 0, 1 );
		if ( empty( $items ) ) {
			return new WP_Error( 'sbird_latest_feed_card_block_no_items', __( 'No items were found in the feed.', 'sbird-latest-feed-card-block' ) );
		}

		$item        = $items[0];
		$item_title  = wp_strip_all_tags( $item->get_title() );
		$item_link   = $item->get_permalink();
		$item_date   = $item->get_date( 'U' );
		$description = $item->get_description();
		$content     = $item->get_content();
		$excerpt     = wp_trim_words( wp_strip_all_tags( $description ? $description : $content ), 100 );
		$image_url   = sbird_latest_feed_card_block_extract_image( $item, $content );

		$data = array(
			'feedTitle'   => wp_strip_all_tags( $feed->get_title() ),
			'feedLink'    => esc_url_raw( $feed->get_permalink() ),
			'itemTitle'   => $item_title,
			'itemLink'    => esc_url_raw( $item_link ),
			'itemDate'    => $item_date,
			'itemExcerpt' => $excerpt,
			'imageUrl'    => $image_url,
		);

		set_transient( $cache_key, $data, 30 * MINUTE_IN_SECONDS );

		return $data;
	}
}

if ( ! function_exists( 'sbird_latest_feed_card_block_extract_image' ) ) {
	/**
	 * Attempt to grab an image URL from the feed item.
	 *
	 * @param SimplePie_Item $item    Feed item.
	 * @param string         $content Item content.
	 *
	 * @return string
	 */
	function sbird_latest_feed_card_block_extract_image( $item, $content ) {
		$enclosure = $item->get_enclosure();
		if ( $enclosure && $enclosure->get_link() ) {
			return esc_url_raw( $enclosure->get_link() );
		}

		$media_ns = class_exists( 'SimplePie' ) ? SimplePie::NAMESPACE_MEDIARSS : 'http://search.yahoo.com/mrss/';
		$media    = $item->get_item_tags( $media_ns, 'content' );
		if ( ! empty( $media[0]['attribs']['']['url'] ) ) {
			return esc_url_raw( $media[0]['attribs']['']['url'] );
		}

		$thumbnails = $item->get_item_tags( $media_ns, 'thumbnail' );
		if ( ! empty( $thumbnails[0]['attribs']['']['url'] ) ) {
			return esc_url_raw( $thumbnails[0]['attribs']['']['url'] );
		}

		if ( method_exists( $item, 'get_thumbnail' ) ) {
			$thumbnail = $item->get_thumbnail();
			if ( $thumbnail ) {
				return esc_url_raw( $thumbnail );
			}
		}

		if ( $content && preg_match( '/<img[^>]+src="([^">]+)"/i', $content, $matches ) ) {
			return esc_url_raw( $matches[1] );
		}

		$og_image = sbird_latest_feed_card_block_fetch_og_image( $item->get_permalink() );
		if ( $og_image ) {
			return esc_url_raw( $og_image );
		}

		return '';
	}
}

if ( ! function_exists( 'sbird_latest_feed_card_block_fetch_og_image' ) ) {
	/**
	 * Fetch og:image from the entry URL.
	 *
	 * @param string $url Entry URL.
	 *
	 * @return string
	 */
	function sbird_latest_feed_card_block_fetch_og_image( $url ) {
		if ( empty( $url ) ) {
			return '';
		}

		$url       = esc_url_raw( $url );
		$cache_key = 'sbird_latest_feed_card_block_og_v2_' . md5( $url );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 6,
				'user-agent' => 'WordPress sBird Latest Feed Card Block',
			)
		);

		if ( is_wp_error( $response ) ) {
			set_transient( $cache_key, '', 5 * MINUTE_IN_SECONDS );
			return '';
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			set_transient( $cache_key, '', 5 * MINUTE_IN_SECONDS );
			return '';
		}

		$image_url = '';
		if ( preg_match( '/<meta[^>]+property=[\"\']og:image[\"\'][^>]+content=[\"\']([^\"\']+)[\"\']/i', $html, $matches ) ) {
			$image_url = html_entity_decode( $matches[1], ENT_QUOTES );
		} elseif ( preg_match( '/<meta[^>]+content=[\"\']([^\"\']+)[\"\'][^>]+property=[\"\']og:image[\"\']/i', $html, $matches ) ) {
			$image_url = html_entity_decode( $matches[1], ENT_QUOTES );
		}

		if ( $image_url && ! preg_match( '#^https?://#i', $image_url ) ) {
			$parsed = wp_parse_url( $url );
			if ( $parsed && isset( $parsed['scheme'], $parsed['host'] ) ) {
				$base = $parsed['scheme'] . '://' . $parsed['host'];
				if ( isset( $parsed['port'] ) ) {
					$base .= ':' . $parsed['port'];
				}
				$image_url = $base . '/' . ltrim( $image_url, '/' );
			}
		}

		$image_url = $image_url ? esc_url_raw( $image_url ) : '';
		set_transient( $cache_key, $image_url, 30 * MINUTE_IN_SECONDS );

		return $image_url;
	}
}

return function( $attributes ) {
	$feed_url = isset( $attributes['feedUrl'] ) ? esc_url_raw( $attributes['feedUrl'] ) : '';
	$has_border = ! isset( $attributes['hasBorder'] ) || $attributes['hasBorder'];
	$layout = isset( $attributes['layout'] ) ? $attributes['layout'] : 'horizontal';
	if ( 'horizontal-left' === $layout ) {
		$layout = 'horizontal';
	} elseif ( 'vertical-top' === $layout ) {
		$layout = 'vertical';
	}
	$layout_class = 'horizontal' === $layout ? 'sbird-latest-feed-card-block--layout-horizontal' : 'sbird-latest-feed-card-block--layout-vertical';
	$card_class = 'sbird-latest-feed-card-block ' . $layout_class . ' ' . ( $has_border ? 'sbird-latest-feed-card-block--bordered' : 'sbird-latest-feed-card-block--borderless' );
	$placeholder_class = 'sbird-latest-feed-card-block__placeholder' . ( $has_border ? '' : ' sbird-latest-feed-card-block__placeholder--borderless' );
	$error_class = 'sbird-latest-feed-card-block__error' . ( $has_border ? '' : ' sbird-latest-feed-card-block__error--borderless' );
	if ( empty( $feed_url ) ) {
		return '<p class="' . esc_attr( $placeholder_class ) . '">' . esc_html__( 'Enter an Feed URL.', 'sbird-latest-feed-card-block' ) . '</p>';
	}

	$data = sbird_latest_feed_card_block_get_feed_data( $feed_url );
	if ( is_wp_error( $data ) ) {
		return '<p class="' . esc_attr( $error_class ) . '">' . esc_html( $data->get_error_message() ) . '</p>';
	}

	$datetime_attr = $data['itemDate'] ? gmdate( 'c', $data['itemDate'] ) : '';
	$date_display = $data['itemDate'] ? date_i18n( get_option( 'date_format' ), $data['itemDate'] ) : '';

	ob_start();
	?>
	<article class="<?php echo esc_attr( $card_class ); ?>">
		<a class="sbird-latest-feed-card-block__link" href="<?php echo esc_url( $data['itemLink'] ); ?>" target="_blank" rel="noopener noreferrer">
			<?php if ( ! empty( $data['imageUrl'] ) ) : ?>
				<div class="sbird-latest-feed-card-block__thumb">
					<img src="<?php echo esc_url( $data['imageUrl'] ); ?>" alt="">
				</div>
			<?php endif; ?>
			<div class="sbird-latest-feed-card-block__content">
				<?php if ( ! empty( $data['feedTitle'] ) ) : ?>
					<span class="sbird-latest-feed-card-block__source"><?php echo esc_html( $data['feedTitle'] ); ?></span>
				<?php endif; ?>
				<h3 class="sbird-latest-feed-card-block__title"><?php echo esc_html( $data['itemTitle'] ); ?></h3>
				<?php if ( ! empty( $data['itemExcerpt'] ) ) : ?>
					<p class="sbird-latest-feed-card-block__excerpt"><?php echo esc_html( $data['itemExcerpt'] ); ?></p>
				<?php endif; ?>
				<?php if ( $date_display ) : ?>
					<time class="sbird-latest-feed-card-block__date" datetime="<?php echo esc_attr( $datetime_attr ); ?>"><?php echo esc_html( $date_display ); ?></time>
				<?php endif; ?>
			</div>
		</a>
	</article>
	<?php
	return ob_get_clean();
};
