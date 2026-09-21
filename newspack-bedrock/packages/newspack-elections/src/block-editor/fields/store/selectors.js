

export function getField( state, item) {
	return state.fields[item]
}

export function getFields( state ) {
	return Object.keys(state.fields).map( key => state.fields[key])
}

export function getFieldTypes( state ) {
	return Object.keys(state.types).map( key => state.types[key])
}

export function getFieldType( state, item) {
	return state.types[item]
}


export function getFieldsOfType( state, type ) {
	return Object.values(state.fields).filter( (field) => field.type === type)
}
