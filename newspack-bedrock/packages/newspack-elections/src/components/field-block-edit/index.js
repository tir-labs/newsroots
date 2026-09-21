
import { useBlockProps } from "@wordpress/block-editor"

import { useProfileFieldAttributes } from './../../profile';

export const FieldBlockEdit = (props) => {

	const blockProps = useBlockProps();

	const { isControlledByContext } =  useProfileFieldAttributes( props ) 

	const { 
		children = [],
		defaultValue = "N/A",
		hasValue = false
	} = props

	const hasChildren = (children.length > 0)

	
	return (
		<div {...blockProps}>

			{!isControlledByContext && (
				<>
				{/*
					<ProfileFieldsInspectorControl
						fieldKey = {fieldKey}
						setFieldKey = {setFieldKey}
						fieldType = {fieldType}
						fields = { fieldsofType }
					/>
			
					<ProfileFieldsToolBar 
						fieldKey = {fieldKey}
						setFieldKey = {setFieldKey}
						fieldType = {fieldType}
						fields = { fieldsofType }
					/>
				*/}
				</>
			)}

			{hasChildren && hasValue && (<>{children}</>)}
			{(!hasValue || !hasChildren) && (<span>{defaultValue}</span>)}

		</div>
	)
}