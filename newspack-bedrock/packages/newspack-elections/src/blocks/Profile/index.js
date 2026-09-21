import { registerBlockType, createBlock, getBlockType } from '@wordpress/blocks';


import { commentAuthorAvatar as icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
 import {ProfileEdit as Edit} from './edit/index';
 import Save from './save';
 import metadata from './block.json';

 /**
 * Style dependencies - will load in editor
 */
import './view.scss';
import { variations } from './variations';
import {withRestrictedAllowedBlocks} from "./edit/restrict-allowed-blocks"

import deprecations from './deprecated';
const { attributes, category, supports, styles } = metadata;


supports.color.__experimentalSkipSerialization = ["background"]
supports.layout.__experimentalSkipSerialization = true
supports.spacing.__experimentalSkipSerialization = ["padding"]
supports.shadow = {__experimentalSkipSerialization : true}
supports.__experimentalBorder.__experimentalSkipSerialization = true

// Add the filter
/*
addFilter(
    'editor.BlockEdit',
    'npe/restrict-allowed-blocks',
    withRestrictedAllowedBlocks
);

*/
registerBlockType( metadata.name, {
	apiVersion: 3,
	title: 'Elections Profile',
	styles,
    category,
    attributes,
	supports,
	icon,
	keywords: [ 'govpack' ],
	variations,
	edit : Edit,
	save: Save,
	deprecated: deprecations
} );


// Our filter function
function lockParagraphs( blockAttributes, blockType, innerHTML, attributes  ) {
    if('core/paragraph' === blockType.name) {
        blockAttributes['lock'] = {move: true}
    }
    return blockAttributes;
}



registerBlockType( "govpack/profile-v2", {
	apiVersion: 3,
	title: 'Elections Profile v2',
	styles,
    category,
    attributes,
	supports : {
		inserter : false
	},
	icon: 'groups',
	keywords: [ 'govpack' ],
	edit : Edit,
	save: Save,
	deprecated: deprecations,
	transforms: {
		"from" : [],
		"to" : [
			{
				type: 'block',
				blocks: [ metadata.name ],
				transform: ( attributes, innerBlocks ) => {
					innerBlocks = innerBlocks.map( (innerBlock) => {
						// if the innerblock is know, return as is
						if(innerBlock.name !== "core/missing"){
							return innerBlock
						}

						// if the innerblock's original name was not prefixed with govpack skip it
						if(innerBlock.attributes.originalName.indexOf("govpack/") !== 0){
							return innerBlock
						}

						// replace the old govpack with new npe prefix, try find that block and exit if it doesn't exist
						const newBlockName = innerBlock.attributes.originalName.replace("govpack/", "npe/");

						if(getBlockType(newBlockName) === undefined){
							return innerBlock
						}

						return createBlock(newBlockName, innerBlock.attributes, innerBlock.innerBlocks)
					})

					return createBlock(
						metadata.name,
						attributes,
						innerBlocks
					);
				},
			},
		]
	}
} );

const ProfileBlockName = metadata.name
export { ProfileBlockName }