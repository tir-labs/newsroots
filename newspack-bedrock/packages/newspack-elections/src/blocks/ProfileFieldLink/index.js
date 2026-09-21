import { registerBlockType } from '@wordpress/blocks';
import { link as icon } from '@wordpress/icons';
import { __ } from "@wordpress/i18n"

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


