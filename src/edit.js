/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RadioControl, TextControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';
import './editor.css';

export default function Edit( { attributes, setAttributes } ) {
	const { feedUrl, hasBorder, layout } = attributes;
	const normalizeLayout = ( value ) => {
		if ( value === 'horizontal-left' ) {
			return 'horizontal';
		}
		if ( value === 'vertical-top' ) {
			return 'vertical';
		}
		return value;
	};
	const layoutValue = normalizeLayout( layout ) || 'horizontal';
	const blockProps = useBlockProps( { className: 'sbird-latest-feed-card-block-editor' } );
	const placeholderClassName = `sbird-latest-feed-card-block__placeholder${
		hasBorder ? '' : ' sbird-latest-feed-card-block__placeholder--borderless'
	}`;

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Feed Settings', 'sbird-latest-feed-card-block' ) } initialOpen>
					<TextControl
						label={ __( 'Feed URL', 'sbird-latest-feed-card-block' ) }
						value={ feedUrl }
						onChange={ ( value ) => setAttributes( { feedUrl: value } ) }
						placeholder="https://example.com/feed"
					/>
					<ToggleControl
						label={ __( 'Border', 'sbird-latest-feed-card-block' ) }
						checked={ hasBorder }
						onChange={ ( value ) => setAttributes( { hasBorder: value } ) }
					/>
					<RadioControl
						label={ __( 'Layout', 'sbird-latest-feed-card-block' ) }
						selected={ layoutValue }
						options={ [
							{
								label: __( 'Horizontal', 'sbird-latest-feed-card-block' ),
								value: 'horizontal',
							},
							{
								label: __( 'Vertical', 'sbird-latest-feed-card-block' ),
								value: 'vertical',
							},
						] }
						onChange={ ( value ) => {
							const normalized = normalizeLayout( value );
							setAttributes( {
								layout: normalized === 'horizontal' ? undefined : normalized,
							} );
						} }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ feedUrl ? (
					<ServerSideRender block={ metadata.name } attributes={ attributes } />
				) : (
					<p className={ placeholderClassName }>
						{ __( 'Enter an RSS URL.', 'sbird-latest-feed-card-block' ) }
					</p>
				) }
			</div>
		</>
	);
}
