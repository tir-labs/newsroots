import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from "@wordpress/url"

export const getField = ( item ) => async ( { dispatch } ) => {
	
	const path = addQueryArgs(
		'/govpack/v1/fields',
		{
			"slug" : item
		}
	);

	const fields = await apiFetch( { path } );
	dispatch.receiveFieldsQuery( path, fields );
}

/**
 * Requests Fields from the REST API.
 *
 * @param {Object|undefined} query Optional object of query parameters to
 *                                 include with request.
 */
export const getFields = ( query = {} ) => async ( { dispatch } ) => {
	
	const path = '/govpack/v1/fields'
	const fields = await apiFetch( { path } );
	dispatch.receiveFieldsQuery( path, fields );
};


export const getFieldType = ( item ) => async ( { dispatch } ) => {
	
	const path = addQueryArgs(
		'/govpack/v1/types',
		{
			"slug" : item
		}
	);

	const types = await apiFetch( { path } );
	dispatch.receiveFieldTypesQuery( path, types );
}

export const getFieldTypes = ( query = {} ) => async ( { dispatch } ) => {
	const path = '/govpack/v1/types'
	const types = await apiFetch( { path } );
	dispatch.receiveFieldTypesQuery( path, types );
};