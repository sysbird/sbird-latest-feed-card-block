/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { feedUrl, hasBorder } = attributes;
	const blockClassName = `rss-card ${ hasBorder ? 'rss-card--bordered' : 'rss-card--borderless' }`;
	const blockProps = useBlockProps( { className: blockClassName } );
	const placeholderClassName = `rss-card__placeholder${
		hasBorder ? '' : ' rss-card__placeholder--borderless'
	}`;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'RSS Settings', 'rss-card' ) } initialOpen>
					<TextControl
						label={ __( 'RSS URL', 'rss-card' ) }
						value={ feedUrl }
						onChange={ ( value ) => setAttributes( { feedUrl: value } ) }
						placeholder="https://example.com/feed"
					/>
					<ToggleControl
						label={ __( 'Border', 'rss-card' ) }
						checked={ hasBorder }
						onChange={ ( value ) => setAttributes( { hasBorder: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ feedUrl ? (
					<ServerSideRender block={ metadata.name } attributes={ attributes } />
				) : (
					<p className={ placeholderClassName }>
						{ __( 'Enter an RSS URL.', 'rss-card' ) }
					</p>
				) }
			</div>
		</>
	);
}
