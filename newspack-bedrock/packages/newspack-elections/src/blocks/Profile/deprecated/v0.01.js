import { InnerBlocks, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import {switchToBlockType, createBlock}  from '@wordpress/blocks'; 

const dep0_01 = {
	"attributes": {
		"preview" : {
			"type": "boolean",
			"default": false
		},
		"queryId": {
			"type": "number"
		},
		"profileId": {
			"type": "number"
		},
		"postId": {
			"type": "number"
		},
		"postType": {
			"type": "string",
			"default": "govpack_profiles"
		},
		"width": {
			"type": "string"
		},
		"customWidth": {
			"type": "string",
			"default": "250px"
		},
		"style": {
			"type": "object"
		},
		"tagName": {
			"type": "string",
			"default" : "div"
		}
	},
	supports : {},
	isEligible: (attributes, innerBlocks, data) => {
		const {profileId = null} = attributes
		return profileId !== null
	},
	save: () => {
	},
	migrate: (attributes, innerBlocks) => {

		const {profileId, postId, queryId, ...restAttrs} = attributes
		
		return [
			{
				...restAttrs,
				postId : postId ?? profileId ?? undefined 
			},
			innerBlocks
		]
	}
}

export default dep0_01