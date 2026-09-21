import { isObject } from "lodash"
import { useSelect } from "@wordpress/data"

import { store } from "./store"
import { getFieldTypeObject, getAllFieldTypeObjects } from "./types"

export const useField = ( slug ) => {

	const field = useSelect( (select) => {
		return select(store).getField(slug) ?? {}
	}, [slug] )
	
	if(field.type){
		field.field_type = getFieldTypeObject(field.type)
	}

	return field
}

export const useFields = () => {
	const rawFields =  useSelect( (select) => {
		return select(store).getFields() ?? []
	} )

	const enrichedFields = rawFields.map( (field)=> {
		return {
			...field,
			field_type : getFieldTypeObject(field.type)
		}
	})

	return enrichedFields
}

export const useFieldType = ( slug ) => {
	return useSelect( (select) => {
		return select(store).getFieldType(slug) ?? {}
	}, [slug] )
}

export const useFieldTypeObject = ( slug ) => {
	return getFieldTypeObject(slug)
}

export const useAllFieldTypeObjects = ( slug ) => {
	return getAllFieldTypeObjects()
}


export const useFieldTypes = () => {
	return useSelect( (select) => {
		return select(store).getFieldTypes() ?? []
	} )
}

export const useFieldsOfType = ( type ) => {
	return useSelect( (select) => {
		return select(store).getFieldsOfType(type) ?? []
	} )
}


export const useFieldAttributes = (props) => {

	const { attributes, setAttributes, context } = props
	const { field = {} } = attributes
	const { 'npe/field' :inheritedField = {} } = context

	

	let { 
		key : localFieldKey = null,
		type : localFieldType = null 
	} = field

	if(!localFieldKey){
		localFieldKey = attributes.fieldKey
	}

	if(!localFieldType){
		localFieldType = attributes.fieldType 
	}

	let { 
		'key' : inheritedFieldKey = null,
		'type' : inheritedFieldType = null 
	} = inheritedField

	
	if(!inheritedFieldKey){
		inheritedFieldKey = context["npe/fieldKey"]
	}

	if(!inheritedFieldType){
		inheritedFieldType = context["npe/fieldType"]
	}
		

	// if the field exists from context, use it. Unless we have a field type of block from context, then revert to the attribute value
	const isControlledByContext = (inheritedFieldKey || inheritedFieldType) && (inheritedField.type !== "block") ? true : false;
	const fieldKey = isControlledByContext ? inheritedFieldKey : localFieldKey
	const gottenField = useField(fieldKey)
	const fieldType = (isControlledByContext ? inheritedFieldType : localFieldType) ?? gottenField.type

	

	const setField = (newFieldOrType, newKey = null) => {

		if(isObject(newFieldOrType)){
			setFieldFromObject(newFieldOrType)
			return
		}

		if(newFieldOrType === null || newKey === null){
			console.error("Neither field Type or key can be null")
		}

		// ToDo
		// Block attributes do not check the sub attributes, 
		// so we should check that these match how we 
		// specify them

		const newField = {
			type : newFieldOrType,
			key : newKey,
		}
		
		setFieldFromObject(newField)
	}
	
	const setFieldFromObject = (newField) => {
		setAttributes({"field" : newField})
	}

	const setFieldType = (newType) => {
		
		let { field } = attributes
		field = {
			...field,
			"type" : newType
		}
		setField(field)
	}

	const setFieldKey = (newKey) => {
		
		let { field } = attributes
		field = {
			...field,
			"key" : newKey
		}
		setField(field)
	}

	

	return {
		field : gottenField,
		fieldType,
		fieldKey,
		setFieldKey,
		setField,
		isControlledByContext
	}
}
