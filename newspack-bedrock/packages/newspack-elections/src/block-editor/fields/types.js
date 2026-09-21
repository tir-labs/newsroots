
import { FieldType, availableFieldTypes } from "./field-types"


const fieldTypes = {}

export const getFieldTypeObject = (type) => {
	if(hasFieldType(type)){
		return fieldTypes[type]
	}

	return null
}

export const getAllFieldTypeObjects = () => {
	return fieldTypes;
}

export const createFieldTypes = (types) => {
	types.forEach( (type) => {
		createFieldType(type)
	})
}

export const createFieldType = (type) => {
	if(hasFieldType(type.slug)){
		return
	}

	let fieldClass = availableFieldTypes.find( (ft) => ft.slug === type.slug) ?? FieldType
	fieldTypes[type.slug] =  new fieldClass.prototype.constructor(type)
}

export const hasFieldType = (type) => {
	return Object.hasOwn(fieldTypes, type)
}

