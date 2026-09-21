/**
 * Returns an action object used in signalling that authors have been received.
 * Ignored from documentation as it's internal to the data store.
 *
 * @ignore
 *
 * @param {string}       queryID Query ID.
 * @param {Array|Object} users   Users received.
 *
 * @return {Object} Action object.
 */
export function receiveFieldsQuery( queryID, fields ) {
	return {
		type: 'RECEIVE_FIELDS_QUERY',
		fields: Array.isArray( fields ) ? fields : [ fields ],
		queryID,
	};
}



export function receiveFieldTypesQuery( queryID, types ) {
	return {
		type: 'RECEIVE_FIELD_TYPES_QUERY',
		types: Array.isArray( types ) ? types : [ types ],
		queryID,
	};
}