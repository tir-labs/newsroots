import { registerBlockType } from '@wordpress/blocks';
import { postCategories as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
 import Edit from './edit';
 import Save from './save';
 import metadata from './block.json';
 import deprecated from "./deprecated"
 
 /**
 * Style dependencies - will load in editor
 */
import './view.scss';
import "./editor.scss"


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
	deprecated,
	edit : Edit,
	save: Save,
} );
