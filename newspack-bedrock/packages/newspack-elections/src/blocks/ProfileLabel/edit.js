/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from "@wordpress/block-editor"


import { useField } from "@npe/editor"
import { useUpdateBlockMetaName } from "../utils"

function Edit( {attributes, setAttributes, context, ...props} ) {
	const blockProps = useBlockProps();

	const { 
		'postId' : profileId, 
		'govpack/fieldKey' : profileFieldKey, 
		'govpack/field' : profileField, 
		'govpack/showLabel' : RowShowLabel = null,
		'govpack/showLabels' : GroupShowLabels = null,
		postType = false
	} = context

	

	const fieldKey = profileField?.key ?? profileFieldKey ?? null
	const { 
		label = null,
	} = attributes

	const showLabel = (RowShowLabel !== null) ? RowShowLabel : GroupShowLabels ?? true 


	const updateLabel = (label) => {
		setAttributes({
			label
		})
	}
	
	const field = useField(fieldKey)

	let calculatedLabel;
	if(!label && field){
		calculatedLabel = field?.label ?? "";
	} else {
		calculatedLabel = label;
	}

	useUpdateBlockMetaName(calculatedLabel)

	if( ! showLabel ) {
		return null;
	}

    return (

		<div {...blockProps}>
			
			<RichText
                { ...blockProps }
                tagName="span" // The tag here is the element output and editable in the admin
                value={ calculatedLabel } // Any existing content, either from the database or an attribute default
                allowedFormats={ [ 'core/bold', 'core/italic' ] } // Allow the content to be made bold or italic, but do not allow other formatting options
                onChange={ ( label ) => updateLabel( label ) } // Store updated content as a block attribute
                placeholder={ __( 'Label...' ) } // Display this text before any content has been added by the user
            />
		</div>
		
	)
}

export {Edit}
export default Edit
