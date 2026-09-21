
import clsx from 'clsx';
import { isEmpty } from "lodash"

import { hasBlockSupport, getBlockDefaultClassName, getBlockSupport} from '@wordpress/blocks';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as blockEditorStore } from "@wordpress/block-editor"

import { createHigherOrderComponent, useInstanceId } from '@wordpress/compose';
import { addFilter } from '@wordpress/hooks';

import { ProfileFieldsInspectorControl, ProfileFieldsToolBar } from "./../components"


import { useProfileFields, useProfileFieldAttributes } from './../profile';
import { useFields } from "./../fields"

const featureName = "gp/field-aware"

const FIELD_TYPE_SCHEMA = {
	type: 'object',
	default: {
		"type" : "text" 
	}
};

const DEFAULT_FEATURE_SUPPORT = {
	type : "",
	allowSwitching: true
}




export const withFieldValue = createHigherOrderComponent(
	( BlockEditBlock ) => ( props ) => {
		const { clientId, name, attributes } = props;
		//const blockSupportsLayout = hasLayoutBlockSupport( name );

		if(!hasBlockSupport(name, "gp/field-aware", false)){
			return (
				<BlockEditBlock
					{ ...props }
				/>
			);
		}

		const extraProps = {
			"extraFieldProp" : "foobar"
		}
		console.log("filter listBlock", name, props)
		/*
		const layoutClasses = useLayoutClasses( attributes, name );
		const extraProps = useSelect(
			( select ) => {
				// The callback returns early to avoid block editor subscription.
				if ( ! blockSupportsLayout ) {
					return;
				}

				const { getSettings, getBlockSettings } = unlock(
					select( blockEditorStore )
				);
				const { disableLayoutStyles } = getSettings();

				if ( disableLayoutStyles ) {
					return;
				}

				const [ blockGapSupport ] = getBlockSettings(
					clientId,
					'spacing.blockGap'
				);

				return { blockGapSupport };
			},
			[ blockSupportsLayout, clientId ]
		);

		if ( ! extraProps ) {
			return (
				<BlockListBlock
					{ ...props }
					__unstableLayoutClassNames={
						blockSupportsLayout ? layoutClasses : undefined
					}
				/>
			);
		}
			*/

		return (

			<BlockEditBlock
				{ ...props }
				{ ...extraProps }
			/>
		);
	},
	'withFieldValues'
);

function hasAttribute( attributes = {}, attribute = "field", subAttribute = false ) {


	// if the parent "field" attribute is missing, always false
	if ( attribute in ( attributes ?? {} )  === false) {
		return false
	}	

	// if we're not looking for a sub attribute then we can quick exit and return true 
	if(subAttribute === false){
		return true
	}

	// look for the subAttribute within field
	if ( subAttribute in ( attributes?.field ?? {} ) ) {
		return true
	}

	return false

}

export function useBlockProps( props ) {

	if(!hasAttribute(props)){
		return {};
	}
	
	

	const blockClass = getBlockDefaultClassName(props.name)
	let {className, field } = props

	// add new classnames to blocks for field key and field-type
	className = clsx(className, {
		[`${blockClass}--type-${field.type}`] : hasAttribute(props, "field", 'type'),
		[`${blockClass}--key-${field.key}`] : hasAttribute(props, "field", 'key')
	});

	return {
		className
	};
}

export function addAttribute( blockSettings ) {

	if ( !hasBlockSupport( blockSettings, featureName ) ) {
		return blockSettings
	}

	const blockSuport = getBlockSupport(blockSettings.name, featureName) 
	
	// Allow blocks to specify their own attribute definition with default values if needed.
	if(!hasAttribute(blockSettings.attributes)){
		// Gracefully handle if blockSettings.attributes is undefined.
		blockSettings.attributes = {
			...blockSettings.attributes,
			field : {
				type: 'object',
				default: {
					"type" : "text" 
				}
			}
		};

		if(blockSuport.type){
			blockSettings.attributes.field.default.type = blockSuport.type
		}
	}

	return blockSettings;	
}

const FieldAwareEdit = (props) => {

	const {clientId, name : blockName, attributes} = props
	const {updateBlockAttributes} = useDispatch(blockEditorStore)


	const { setField : setFieldAttribute, isControlledByContext, fieldKey, fieldType, value } =  useProfileFieldAttributes(props) 

	
	const fields = useProfileFields(props)
	const availableFields = useFields()
	
	const contextProvidingBlock = useSelect( (select) => {
		const profileRows = select(blockEditorStore).getBlockParentsByBlockName(clientId, "npe/profile-row")
		const parentRowClientId = profileRows.at(profileRows.length - 1)
		return select(blockEditorStore).getBlock(parentRowClientId)
	} )

	const blockSupports = getBlockSupport(blockName, featureName, DEFAULT_FEATURE_SUPPORT)
	
	
	/*
	useEffect( () => {

		if(!blockSupports?.type){
			return
		}

	}, [blockSupports?.type, fieldType])
	*/
	
	const updateContextProviderAttribute = (attrs) => {
		updateBlockAttributes(contextProvidingBlock.clientId, attrs )
	}


	const setFieldContext = (newValue) => {
		updateContextProviderAttribute({"field" : newValue})
	}
	
	const setFieldKey = isControlledByContext ? setFieldContext : setFieldAttribute
	
	const onSelectField = (fieldKey) => {
		const field = availableFields.find( (f) => f.slug === fieldKey)
		const newFieldAttr = {
			type : field.type,
			key: fieldKey
		}
		setFieldKey(newFieldAttr)
	}
	

	if(!hasBlockSupport(blockName, "gp/field-aware.allowSwitching", DEFAULT_FEATURE_SUPPORT.allowSwitching)){
		return null
	}

	let switchToTypes = getBlockSupport(blockName, "gp/field-aware.allowSwitching", DEFAULT_FEATURE_SUPPORT.allowSwitching)
	switchToTypes = (switchToTypes === true) ? [] : switchToTypes // if true was passed, return an empty array and bypass
	switchToTypes = Array.isArray(switchToTypes) ? switchToTypes : [switchToTypes] // convert a single string passed as a type to an array
	
	const fieldsFilteredByType = (switchToTypes.length < 1) ? fields : fields.filter( (f) => switchToTypes.includes(f.type) )

	
	return (
		<>
			<ProfileFieldsInspectorControl
				fieldKey = {fieldKey}
				onSelectField = {onSelectField}
				fieldType = {fieldType}
				fields = { fieldsFilteredByType }
				showFieldsWithEmptyValues = {true}
				disableEmptyFields = {false}
			/> 

			<ProfileFieldsToolBar 
				fieldKey = {fieldKey}
				onSelectField = { onSelectField }
				fieldType = {fieldType}
				fields = { fieldsFilteredByType }
				showFieldsWithEmptyValues = {true}
				disableEmptyFields = {false}
			/>
		</>
	)

}

const migrateLegacyAttrsToField =  (attributes, innerBlocks) => {

	let field = {}
	const {
		fieldType = null, 
		fieldKey = null, 
		...restAttrs
	} = attributes

	if(fieldType){
		field.type = fieldType
	}

	if(fieldKey){
		field.key = fieldKey
	}

	return [
		{...restAttrs, field},
		innerBlocks
	]
}

const isEligibleForFieldAttrMigration = (attributes, innerBlocks, data) => {

	const hasFieldAttr = hasAttribute(attributes)
	const hasDeprecatedFieldTypeAttr = hasAttribute(attributes, "fieldType")
	const hasDeprecatedFieldKeyAttr = hasAttribute(attributes, "fieldKey")

	return hasDeprecatedFieldTypeAttr || hasDeprecatedFieldKeyAttr
}


addFilter(
	'editor.BlockEdit',
	'npe/block-supports/field-aware/with-field-value',
	withFieldValue
);

export default {
	edit: FieldAwareEdit,
	attributeKeys: ["field"],
	addAttribute: addAttribute,
	useBlockProps,
	hasSupport( name ) {
		return hasBlockSupport( name, featureName, false );
	},
};

export { migrateLegacyAttrsToField, hasAttribute, isEligibleForFieldAttrMigration }