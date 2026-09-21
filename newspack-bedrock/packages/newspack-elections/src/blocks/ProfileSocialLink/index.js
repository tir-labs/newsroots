import { registerBlockType, store as blocksStore, registerBlockVariation } from '@wordpress/blocks';

import { link as icon } from '@wordpress/icons';
import { __ , sprintf} from "@wordpress/i18n"

import {subscribe, select} from "@wordpress/data"
import {store as FieldStore} from "@npe/editor"
import { NPEIcons } from "./../../components/Icons"
/**
 * Internal dependencies
 */
import Edit from './edit';
import Save from './save';
import metadata from './block.json';



/**
 * Style dependencies - will load in editor
 */
import './editor.scss';



const { attributes, category, title } = metadata;


registerBlockType( metadata.name, {
	apiVersion: 3,
	title,
    category,
    attributes,
	icon,
	keywords: [ 'govpack' ],
    styles: [
	],
	edit : Edit,
	save: Save,
} );


let cachedCountServiceFields = null
subscribe( () => {
	const fields = select(FieldStore).getFieldsOfType("service")

	// the number should only ever go up, so if we match then leave and csave cpu cycles
	if(cachedCountServiceFields === fields.length) {
		return 
	}

	// this is the condition on first run, populate it with the value from REST
	if(cachedCountServiceFields === null){
		cachedCountServiceFields = fields.length
	} 

	createServiceFieldVariations(fields)

}, FieldStore)

/*
const variation = {
	'category' : 'newspack-elections-profile-row-fields',
	//'name'     => sprintf( 'profile-field-service-%s', $field->slug ),
	//'title'       => $field->label,
	//'description' => sprintf(
	//	__( 'Display Profile Social media Field: %s' ),
	//	$field->label
	),
	'attributes'  => [
		'field' => [ 
			'type' => $field->type->slug,
			'key'  => $field->slug,
		],
	],
	'isActive'    => [ 'field.type', 'field.key' ],
	'scope'       => [ 'inserter' ],
}
*/

const createServiceFieldVariations = (fields) => {
	const existingVariations = select( blocksStore ).getBlockVariations( metadata.name ) ?? []


	fields.forEach((field) => {
		const variationToCreateName = sprintf( 'profile-field-service-%s', field.slug )

		const variationAlreadyExists = existingVariations.some( (extistingVariation) => { 
			return extistingVariation.name === variationToCreateName
		})

		if(variationAlreadyExists){
			return;
		}

	
		registerBlockVariation(metadata.name, {
			'category' : 'newspack-elections-profile-row-fields',
			'name' : variationToCreateName,
			'title' : field.label,
			'description' : sprintf(
				__( 'Display Profile Social media Field: %s' ),
				field.label
			),
			'attributes' : {
				'field' : { 
					'type' : field.type.slug,
					'key'  : field.slug,
				},
			},
			'isActive' : [ 'field.type', 'field.key' ],
			'scope'    : [ 'inserter' ],
			'icon' :  NPEIcons[field.service]
		})
	} )
}