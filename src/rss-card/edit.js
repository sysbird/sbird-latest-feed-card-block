/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { feedUrl } = attributes;
	const blockProps = useBlockProps( { className: 'rss-card' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'RSSの設定', 'rss-card' ) } initialOpen>
					<TextControl
						label={ __( 'RSS URL', 'rss-card' ) }
						value={ feedUrl }
						onChange={ ( value ) => setAttributes( { feedUrl: value } ) }
						placeholder="https://example.com/feed"
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ feedUrl ? (
					<ServerSideRender block={ metadata.name } attributes={ attributes } />
				) : (
					<p className="rss-card__placeholder">
						{ __( 'RSSのURLを入力してください。', 'rss-card' ) }
					</p>
				) }
			</div>
		</>
	);
}
