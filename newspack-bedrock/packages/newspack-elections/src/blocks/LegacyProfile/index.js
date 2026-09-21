import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { commentAuthorAvatar as icon } from '@wordpress/icons';
/**
 * Internal dependencies
 */
 import Edit from './edit';
 import metadata from './block.json';

 /**
 * Style dependencies - will load in editor
 */
import './view.scss';



const { attributes, category } = metadata;

registerBlockType( 'govpack/profile-legacy', {
	apiVersion: 2,
	title: __('Election Profile (Legacy)', "newspack-elections"),
    category,
    attributes,
	icon,
	keywords: [ 'govpack', 'newspack-elections' ],
    styles: [
		{ name: 'default', label:  'Default', isDefault: true },
        { name: 'boxed', label:  'Boxed' }
	],
	edit : Edit,
	save() {
		return null;
	},

} );
