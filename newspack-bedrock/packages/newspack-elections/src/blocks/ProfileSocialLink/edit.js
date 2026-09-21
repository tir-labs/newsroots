import {isEmpty} from "lodash"
import clsx from "clsx"


/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps} from "@wordpress/block-editor"
import { 
	PanelBody, PanelRow, TextControl, Icon,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	} from '@wordpress/components';

import { useSelect } from "@wordpress/data";
import { store as blocksStore } from '@wordpress/blocks';
import { background, link as LinkIcon } from '@wordpress/icons';

import { useProfileFieldAttributes } from "@npe/editor"
import { useUpdateBlockMetaName, useIsPreviewMode } from "./../utils"
import { NPEIcons } from "./../../components/Icons"

const sizeOptions = [
	{ name: __( 'Small' ), value: 12 },
	{ name: __( 'Normal' ), value: 18 },
	{ name: __( 'Large' ), value: 24 },
	{ name: __( 'Huge' ), value: 30 },
];



const DynamicIcon = ({icon, size = 24}) => {
	// Todo - make sure this exists
	// move the function call up to the icons component
	const SVG = NPEIcons[icon]()
	return (
		<Icon icon={ SVG } size={size} />
	)
}

function Edit( props ) {

	

	const {fieldKey, value, profile, field, profileId, fieldType } =  useProfileFieldAttributes(props) 
	const { attributes, setAttributes, context, clientId, isSelected } = props

	console.log("profileSocialLink", props )
	const {
		"npe/showLabels" : showLabels,
		"npe/iconColor" : iconColor,
		"npe/iconColorValue" : iconColorValue,
		"npe/iconBackgroundColor" : iconBackgroundColor,
		"npe/iconBackgroundColorValue" : iconBackgroundColorValue
	} = context

	

	const isPreviewMode = useIsPreviewMode(clientId)
	const hasValue = !isEmpty(value)
	const hideFieldIfEmpty = true

	const showValue = hasValue || isPreviewMode || hideFieldIfEmpty
	
	const shouldDimField = !isPreviewMode && hideFieldIfEmpty && (!hasValue) && (!isSelected)

	console.log("profile social shouldDimField", shouldDimField )
	
	const className = clsx("wp-block-social-link", "wp-social-link", {
		[ `has-${ iconColor }-color` ]: iconColor,
		[ `has-${ iconBackgroundColor }-background-color` ]: iconBackgroundColor,
		"is-dimmed" : shouldDimField
	})

	const serviceColor = field?.service_color ?? false
	const blockProps = useBlockProps({
		className,
		style : {
			color: serviceColor ?? iconColorValue,
			backgroundColor: iconBackgroundColorValue
		}
	})
	
	const IconComponent = ( {size = "1rem"} ) => {

		if(!field?.service){
			return (<Icon icon = { LinkIcon } size={size} />)
		}

		if(!NPEIcons[field.service]){
			return (<Icon icon = { LinkIcon } size={size} />)
		}

		

		const Component = NPEIcons[field.service];

		return (<Component size={size} />)
	}


    return (
		<>
			<li {...blockProps} >
				{ (showValue) && (
					<button
						className="wp-block-social-link-anchor"
						aria-haspopup="dialog"
					>
						<IconComponent />
						<span
							className={ clsx( 'wp-block-social-link-label', {
								'screen-reader-text': ! showLabels,
							} ) }
						>
							{ field.label }
						</span>
					</button>
				) }
			</li>
		</>
	)
}

export {Edit}
export default Edit
