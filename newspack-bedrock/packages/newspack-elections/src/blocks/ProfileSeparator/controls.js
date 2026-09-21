import {  InspectorControls } from "@wordpress/block-editor"

import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import DimensionInput from "./../../components/Controls/DimensionInput"
import SeparatorColor from "./../../components/Controls/SeparatorColor"

export default function SpacerControls( {
	setAttributes,
	orientation,
	height,
	width,
	isResizing,
	separatorColor,
	setSeparatorColor
} ) {
	return (
		<>
		<InspectorControls group="color">
			<SeparatorColor
				colorValue = {separatorColor?.color}
				onColorChange = { ( value ) => {
					setSeparatorColor( value );
				}}
			/>
		</InspectorControls>
		<InspectorControls>
			<PanelBody title={ __( 'Settings' ) }>
				{ orientation === 'horizontal' && (
					<DimensionInput
						label={ __( 'Width' ) }
						value={ width }
						onChange={ ( nextWidth ) =>
							setAttributes( { width: nextWidth } )
						}
					/>
				) }
				{ orientation !== 'horizontal' && (
					<DimensionInput
						label={ __( 'Height' ) }
						value={ height }
						onChange={ ( nextHeight ) =>
							setAttributes( { height: nextHeight } )
						}
					/>
				) }
			</PanelBody>
		</InspectorControls>
		</>
	);
}