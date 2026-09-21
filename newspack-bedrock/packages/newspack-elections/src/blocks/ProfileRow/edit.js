/**
 * External dependencies
 */
import clsx from 'clsx';
import {isEmpty} from "lodash"

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, useInnerBlocksProps, store as blockEditorStore, RichText} from "@wordpress/block-editor"
import { useSelect, select} from '@wordpress/data';
import { store as blocksStore } from "@wordpress/blocks"
import {
	PanelBody, PanelRow, ToggleControl, 
	__experimentalHStack as HStack,
	__experimentalText as Text,
	FlexBlock,
	FlexItem,
} from '@wordpress/components';

import {useEffect, useMemo} from "@wordpress/element"

/**
 * Internal dependencies
 */
import { useProfileFieldAttributes, useProfileFields } from "@npe/editor"
import { ProfileBlockName } from '../Profile';

import { useFields } from "@npe/editor"
import { useIsPreviewMode } from '../utils';

const defaultTemplate = [
	['core/paragraph', {
		"placeholder" : "Profile Value..."
	}]
]


const MetaInspectorControl = ({
	fieldKey,
	setAttributes,
	showLabel,
	hideFieldIfEmpty,
	fieldType = "text"
}) => {

	return(
		<InspectorControls>
			
				<PanelBody title={ __( 'Profile Label Controls', 'govpack' ) }>
					<PanelRow>
						<ToggleControl 
							label = "Show the field label?"
							help = "Enable or Disable showing the a descriptive label for the field."
							checked = {showLabel}
							onChange={ ( value ) => { setAttributes({"showLabel" : value}) } }
							__nextHasNoMarginBottom
						/>
					</PanelRow>
					<PanelRow>
						<ToggleControl 
							label = "Hide field if empty?"
							help = "Hide the field if the profile is missing this field, or leave it empty on screen."
							checked = {hideFieldIfEmpty}
							onChange={ ( value ) => { setAttributes({"hideFieldIfEmpty" : value}) } }
							__nextHasNoMarginBottom
						/>
					</PanelRow>
				</PanelBody>
			
		</InspectorControls>
	)
}


function useConditionalTemplate(clientId){

	
	const {name, attributes, ...block} = select(blockEditorStore).getBlock(clientId) ?? {}
		
	

	const template = useMemo( () => {
		const variation = select(blocksStore).getActiveBlockVariation(name, attributes)
		return variation?.innerBlocks ?? defaultTemplate 
	}, [attributes?.field] )

	return template
}

function ProfileRowEdit( props ) {

	const {attributes, setAttributes, context, clientId} = props  

	

	const blockProps = useBlockProps();
	const { setField, fieldKey, fieldType, value, field } =  useProfileFieldAttributes(props) 
	const fields = useProfileFields(props)
	const availableFields = useFields()

	const hasValue = !isEmpty(value)
	const hasField = !isEmpty(field)
	const isPreviewMode = useIsPreviewMode(clientId)
	
	/**
	 * Get Data From Parent Blocks
	 */
	const { 
		'npe/showLabels' : GroupShowLabels = null, 
	} = context

	/**
	 * Get Data From This Blocks
	 */
	const { 
		label = null,
		showLabel : RowShowLabel = null,
		hideFieldIfEmpty,
	} = attributes

	/**
	 * Get Data From The Editor
	 */

	const {children, ...innerBlocksProps } = useInnerBlocksProps(blockProps, {
		template : useConditionalTemplate(clientId),
		renderAppender : false,
		templateLock: "all",
	} );

	let {className} = innerBlocksProps

	// Select Block Store Data
	const {isBlockSelected, hasSelectedInnerBlock, isParentSelected, isRelativeSelected} = useSelect( (select) => {

		const parentProfileBlockClientIds = select(blockEditorStore).getBlockParentsByBlockName(clientId, ProfileBlockName)
		const closestParentProfileBlockClientId = parentProfileBlockClientIds.at(-1)
		const parentProfileBlock = select(blockEditorStore).getBlock(closestParentProfileBlockClientId)
		return {
			isBlockSelected : select(blockEditorStore).isBlockSelected(clientId),
			hasSelectedInnerBlock : select(blockEditorStore).hasSelectedInnerBlock(clientId),
			parentProfileBlockClientIds,
			parentProfileBlock,
			isParentSelected : select(blockEditorStore).isBlockSelected(parentProfileBlock?.clientId),
			isRelativeSelected : select(blockEditorStore).hasSelectedInnerBlock(parentProfileBlock?.clientId, true)
		}
	} )

	const {hasInnerBlocks} = useSelect( ( select ) => {
		const descendents = select( blockEditorStore ).getBlock( clientId )?.innerBlocks ?? []
		return {
			hasInnerBlocks : (descendents.length > 0)
		}
	}, [ clientId ]
);

	const updateLabel = (label) => {
		setAttributes({
			label
		})
	}

	/**
	 * When a fieldKey changes we reset the over ridden label
	 */
	useEffect( ()=> {
		//updateLabel(undefined)
	}, [fieldKey])
	
	let calculatedLabel;
	if(!label && field){
		calculatedLabel = field?.label ?? "";
	} else {
		calculatedLabel = label;
	}

	
	const showLabel = (RowShowLabel !== null) ? RowShowLabel : GroupShowLabels ?? true

	const showFieldOutput = isPreviewMode || hasInnerBlocks || (hasField && hasValue)

	// Should we output the UI to show that a field is hidden?
	// Add a class to the blockProps if so
	const shouldDimField = ((attributes?.field?.type !== "block") && !isPreviewMode && hideFieldIfEmpty && (!hasValue) && (!isBlockSelected) && (!hasSelectedInnerBlock) )
 	const shouldHideBlock = !hasInnerBlocks && !isPreviewMode && hideFieldIfEmpty && !hasValue && !isBlockSelected && !isRelativeSelected && !isParentSelected
	className = clsx(className, {"gp-dim-field" : shouldDimField })

	console.log("Row : should dim", shouldDimField, (attributes?.field?.type !== "block"), !isPreviewMode, hideFieldIfEmpty, (!hasValue), value)

	if(shouldHideBlock){
		return null;
	}

    return (
		<div {...innerBlocksProps} className={className}>
			<MetaInspectorControl
				fieldKey = {fieldKey}
				setAttributes = {setAttributes}
				showLabel = {showLabel ?? false}
				hideFieldIfEmpty = {hideFieldIfEmpty}
				fieldType = {fieldType}
			/>
			
			{ showFieldOutput && ( <>
				{ showLabel && (
					<div className="gp-block-row-label">
						<RichText
							identifier = "label"
							tagName="span" // The tag here is the element output and editable in the admin
							value={ calculatedLabel } // Any existing content, either from the database or an attribute default
							//value={ "label" } // Any existing content, either from the database or an attribute default
							allowedFormats={ [ 'core/bold', 'core/italic' ] } // Allow the content to be made bold or italic, but do not allow other formatting options
							onChange={ ( label ) => {
								updateLabel( label ) 
							} } // Store updated content as a block attribute
							placeholder={ __( 'Label...' ) } // Display this text before any content has been added by the user
						/>
					</div>
				)}

				{ children }
			</>)}
			
		</div>
	)
}

export {ProfileRowEdit}
export default ProfileRowEdit
