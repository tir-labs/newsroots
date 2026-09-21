import { InnerBlocks, useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import {switchToBlockType, createBlock}  from '@wordpress/blocks'; 
const targetBlockSwaps = {
	"core/post-title" : "npe/profile-name",
	"core/post-excerpt": "npe/profile-bio",
}

const targetBlocks = Object.keys(targetBlockSwaps)

const hasTargetBlock = (blocks) => {


	return blocks.some( (block) => {
		return  ( targetBlocks.includes(block.name) ||  // check if this block's name is in the list of targets
			targetBlocks.includes(block?.attributes?.originalName) || // if the block is of a deactivated type, look for the original name
			hasTargetBlock(block.innerBlocks) || // try this again on innerblocks
			false ) // fallback
	})
}

const dep0_00 = {
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
		return hasTargetBlock(innerBlocks)
	},
	save: () => {
	},
	migrate: (attributes, innerBlocks) => {

		// We want to look through each innerblock and transform _some_ of them to a different block type.
		// the number of blocks returned may not match the innerBlocks being transformed so use reduce instead of map. 
		// Prefer reduce over foreach to avoid state outside of the loop
		const newInnerBlocks = innerBlocks?.reduce( (migratedBlocks, block) => {
			
			const blockTypeIsMissing = (block.name === "core/missing")
			const blockName = blockTypeIsMissing ? block.attributes.originalName : block.name

			
			// Check the block passed to process is in the block targets list, if not return early
			if( !targetBlocks.includes(blockName)){
				migratedBlocks.push(block)
				return migratedBlocks
			}

			//if((blockTypeIsMissing)){
			//	const newBlocks = createBlock(targetBlockSwaps[blockName], attributes, innerBlocks)
			//} else {
				// use block transforms to change the block type.
				// This returns an array for loop that later on
			//	const newBlocks = switchToBlockType(block, targetBlockSwaps[blockName])
			//}

			const newBlocks = blockTypeIsMissing ? 
				[createBlock(targetBlockSwaps[blockName], attributes, innerBlocks)] : // wrap in array to match switchToBlockType
				switchToBlockType(block, targetBlockSwaps[blockName])
			
			// if the type switch failed, (e.g) no registered transforms, it will return null
			// add the original block to accumulator and return from the function to avoid looping null
			if(newBlocks === null){
			
				migratedBlocks.push(block)
				return migratedBlocks
			}


			// the switchToBlockType returns an array so loop it and add to the accumulator
			// then exit the migration
			newBlocks.forEach((block) => {
				migratedBlocks.push(block)
			})

			return migratedBlocks

		}, []) ?? [] // if innerBlocks isn't set then return an empty array

		
		return [
			attributes,
			newInnerBlocks
		]
	}
}

export default dep0_00
export { dep0_00 }