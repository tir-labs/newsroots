import { registerBlockType, registerBlockVariation } from '@wordpress/blocks';
import { row as icon, connection } from '@wordpress/icons';
import { __ } from "@wordpress/i18n"

/**
 * Internal dependencies
 */
import ProfileRowEdit from './edit';
import Save from './save';
import metadata from './block.json';
import deprecated from './deprecated';

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
	edit : ProfileRowEdit,
	save: Save,
	deprecated : deprecated
} );


registerBlockVariation(metadata.name, {
	name : "field-empty",
	title : "Profile Field",
	description: "Empty Profile Field",
	icon : connection,
	attributes : {},
	isDefault : true
})