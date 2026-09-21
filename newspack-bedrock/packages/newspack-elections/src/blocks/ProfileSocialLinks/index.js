import { registerBlockType } from '@wordpress/blocks';
import { share as icon } from '@wordpress/icons';
/**
 * Internal dependencies
 */
 import SocialLinksEdit from './edit';
 import Save from './save';
 import metadata from './block.json';

 /**
 * Style dependencies - will load in editor
 */
import './view.scss';
import "./edit.scss"


const { attributes, category, title, name } = metadata;

registerBlockType( name, {
	apiVersion: 3,
	title,
    category,
    attributes,
	icon,
	keywords: [ 'govpack' ],
	edit : SocialLinksEdit,
	save: Save,
} );
