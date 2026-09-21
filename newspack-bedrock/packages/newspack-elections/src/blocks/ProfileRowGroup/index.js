import { registerBlockType } from '@wordpress/blocks';
import { group as icon } from '@wordpress/icons';
/**
 * Internal dependencies
 */
 import RowGroupEdit from './edit';
 import Save from './save';
 import metadata from './block.json';

 /**
 * Style dependencies - will load in editor
 */
import './view.scss';
import "./edit.scss"


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
	edit : RowGroupEdit,
	save: Save,
} );
