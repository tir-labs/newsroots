

export const useContextOverAttribute = (props, contextKey = null, attributeKey = null, defaultValue = null) => {
	
	const {attributes, context} = props

	const attrValue = attributes?.[attributeKey] ?? null
	const contextValue = context?.[contextKey] ?? null

	const value = contextValue ?? attrValue ?? defaultValue ?? null
	const isControlledByContext = (contextValue !== null)
	const isControlledByAttribute = ((isControlledByContext === false) && (attrValue !== null))

	return [value, isControlledByContext, isControlledByAttribute]
}

export const useAttributeOverContext = (props, contextKey = null, attributeKey = null, defaultValue = null) => {
	
	const {attributes, context} = props

	const attrValue = attributes?.[attributeKey] ?? null
	const contextValue = context?.[contextKey] ?? null

	const value = attrValue ?? contextValue ?? defaultValue ?? null
	const isControlledByAttribute = ( attrValue !== null)
	const isControlledByContext = ((isControlledByAttribute === false) && (contextValue !== null))

	return [value, isControlledByContext, isControlledByAttribute]
}

