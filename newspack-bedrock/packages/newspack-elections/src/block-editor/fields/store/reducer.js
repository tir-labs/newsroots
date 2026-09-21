import {DEFAULT_STATE} from "./constants"

export const reducer = ( state = DEFAULT_STATE, action) => {
	
	switch ( action.type ) {
		case "RECEIVE_FIELDS_QUERY" : 
			return {
				...state,
				fields : action.fields.reduce( (accum, field) => {
					accum[field.slug] = field
					return accum
				}, state.fields )
			};
			break;
		case "RECEIVE_FIELD_TYPES_QUERY" : 
			return {
				...state,
				types : action.types.reduce( (accum, type) => {
					accum[type.slug] = type
					return accum
				}, state.types )
			};
			break;
	}
	return state;
}

export default reducer