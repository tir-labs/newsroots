import { registerBlockType, createBlock } from '@wordpress/blocks';
import { postExcerpt as icon } from '@wordpress/icons';
import { __ } from "@wordpress/i18n"

/**
 * Internal dependencies
 */
import Edit from './edit';
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
	edit : Edit,
	save: Save,
	transforms: {
		"to" : [
			{
				type: 'block',
				blocks: [ 'npe/profile-row' ],
				transform: ( attributes  ) => {
					return createBlock( 'npe/profile-row', attributes );
				},
			}
		],
		"from" : [
			{
				type: 'block',
				blocks: [ 'npe/profile-row' ],
				transform: ( attributes  ) => {
					return createBlock( 'npe/profile-field-text', attributes );
				},
			}
		]
	},
	deprecated 
} );


