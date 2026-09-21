/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, useInnerBlocksProps, InnerBlocks, store as blockEditorStore, InspectorControls, __experimentalSpacingSizesControl as SpacingSizesControl, withColors} from "@wordpress/block-editor"
import {useSelect} from "@wordpress/data";

import {
	Panel, PanelBody, PanelRow, ToggleControl,
	__experimentalToolsPanel as ToolsPanel, 
	__experimentalToolsPanelItem as ToolsPanelItem
} from '@wordpress/components';

import {useState, useEffect} from "@wordpress/element"

import "./edit.scss"
import DimensionInput from "./../../components/Controls/DimensionInput"
import SeparatorColor from "./../../components/Controls/SeparatorColor"


import { separator } from '@wordpress/icons';

const RowGroupEdit = ( {attributes, setAttributes, context, clientId, ...props} ) => {

	const {
		postType,
		"npe/postId" : pid
	} = context

	const {
		separatorColor, 
		setSeparatorColor
	} = props
	
	const {
		showSeparator,
		showLabels,
		separatorStyles = {}
	} = attributes

	const {
		spacing,
		height
	} = separatorStyles

	const { isBlockSelected, hasSelectedInnerBlock } = useSelect( (select) => {
		return {
			isBlockSelected : select(blockEditorStore).isBlockSelected(clientId),
			hasSelectedInnerBlock : select(blockEditorStore).hasSelectedInnerBlock(clientId, true),
		}
	});

	
	
	const blockProps = useBlockProps();
	const innerBlockProps = useInnerBlocksProps(blockProps, {
		renderAppender : (isBlockSelected || hasSelectedInnerBlock) ? InnerBlocks.ButtonBlockAppender : undefined,
		prioritizedInserterBlocks : [
			"npe/profile-row/field-empty"
		]
	});
	
	const units = [
		'%',
		'px',
		'em',
		'rem',
		'vw',
	];

	const setSeperatorStyles = ( newStyles ) => {
		
		setAttributes({separatorStyles : {
			...attributes.separatorStyles,
			...newStyles
		}})
	}

	const SeparatorControls = () => {
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
				<Panel>
					<PanelBody title={ __( 'Field Separators', 'govpack' ) }>
					<PanelRow>
						<ToggleControl 
							label = "Show Seperators Between Fields?"
							help = "Enable or Disable showing separator."
							checked = {showSeparator}
							onChange={ ( value ) => { setAttributes({"showSeparator" : value}) } }
							__nextHasNoMarginBottom
						/>
					</PanelRow>
					</PanelBody>
				</Panel>
			</InspectorControls>
			</>
		)
	}

    return (
		<>	

			<InspectorControls>
				<Panel>
					<PanelBody title={ __( 'Field Labels', 'govpack' ) }>
					<PanelRow>
						<ToggleControl 
							label = "Show Field Labels?"
							help = "Enable or Disable showing labels."
							checked = {showLabels}
							onChange={ ( value ) => { setAttributes({"showLabels" : value}) } }
							__nextHasNoMarginBottom
						/>
					</PanelRow>
					</PanelBody>
				</Panel>
			</InspectorControls>

			

			<div {...innerBlockProps} />
		</>
	)
}

const separatorColorAttributes = {
	separatorColor: 'separator-color',
}

//const Edit = withColors( separatorColorAttributes )( RowGroupEdit );

export {RowGroupEdit}
export default RowGroupEdit
