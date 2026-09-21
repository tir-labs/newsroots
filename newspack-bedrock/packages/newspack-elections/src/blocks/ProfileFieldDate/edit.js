import {isEmpty} from "lodash"

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

import {
		useBlockProps,
		InspectorControls, 
		__experimentalDateFormatPicker as DateFormatPicker
} from "@wordpress/block-editor"
import {PanelBody, PanelRow} from "@wordpress/components"
import { gmdate, getSettings as getDateSettings } from '@wordpress/date';
import { useEntityProp } from '@wordpress/core-data';

/**
 * Internal dependencies
 */
import { useProfileFieldAttributes } from "@npe/editor";



const getSiteDefaultDateFormat = () => {
	// Get the generic date settings and the site specific date settings. 
	// Use the root.site.date_format, Unless it doesn't exist. 
	// Fallback to the default in wpDateSettings.
	const wpDateSettings = getDateSettings();
	const [ siteFormat = wpDateSettings.formats.date ] = useEntityProp( 'root', 'site', 'date_format');

	return siteFormat
}

const DateFormattingControls = (props) => {

	const {attributes, setAttributes} = props
	const {dateFormat : format = null} = attributes
	
	const setFormat = (nextFormat) => {
		setAttributes({"dateFormat" : nextFormat})
	}
	

	const siteFormat = getSiteDefaultDateFormat()

	return (
		<InspectorControls>
			<PanelBody title="Date Format">
				<PanelRow>
					<DateFormatPicker 
						defaultFormat = { siteFormat }
						onChange = { ( nextFormat ) => setFormat( nextFormat ) }
						format = {format}
					/>
				</PanelRow>
			</PanelBody>
		</InspectorControls>
	)
}

function DateEdit( props ) {

	const { fieldKey, value, field } =  useProfileFieldAttributes(props) 
	const { attributes } = props
	const defaultFormat = getSiteDefaultDateFormat()
	const { dateFormat = defaultFormat } = attributes
	const blockProps = useBlockProps()	
	const date = field?.field_type?.value(value)

	console.log(field.label)
	let FieldValue

	if(date === null){
		 FieldValue = `No Date Set for ${field.label}`
	} else {
		FieldValue = gmdate(dateFormat, date, true)
	}

	
	
    return (
		<div {...blockProps}>
			<DateFormattingControls {...props}/>
			{FieldValue}
		</div>
	)
}



export {DateEdit as Edit}
export default DateEdit
