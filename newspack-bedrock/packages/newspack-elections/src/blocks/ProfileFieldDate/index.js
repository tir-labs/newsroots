/**
 * WordPress dependencies
 */
import { registerBlockType, createBlock } from '@wordpress/blocks';
import { postCategories as icon } from '@wordpress/icons';
import { __ } from "@wordpress/i18n"

/**
 * Plugin dependencies
 */
import { FieldToRow, createRowToFieldTransform } from '../../block-transforms';

/**
 * Internal Block dependencies
 */
import Edit from './edit';
import Save from './save';
import metadata from './block.json';
import deprecated from "./deprecated"


/**
 * Style dependencies - will load in editor
 */
import './view.scss';



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
	transforms: {
		"to" : [
			FieldToRow
		],
		"from" : [
			createRowToFieldTransform(metadata.name)
		]
	},
	deprecated
} );


