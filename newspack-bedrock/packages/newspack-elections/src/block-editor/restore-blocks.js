import {  getBlockType} from '@wordpress/blocks';
import { __experimentalGetCoreBlocks as getCoreBlocks } from '@wordpress/block-library'

/**
 * Restores required Blocks that may have been de-registered
 */
export const restoreBlocks = (requiredBlocks = []) => {
	requiredBlocks.forEach( (block) => {
		restoreDeregisteredBlock(block)
	})
}

/**
 * Check if a block is registered.
 *
 * @param {string} name The block's name.
 *
 * @return {boolean} Whether the block is registered.
 */
const isBlockRegistered = ( name ) => {
	return getBlockType( name ) !== undefined;
}

const restoreDeregisteredBlock = (blockName) => {

	if(isBlockRegistered(blockName)){
		return;
	}

	const block = getCoreBlock(blockName)
	block.init()
}

/**
 * Get a specific core block type
 * 
 * @param {string} name The block's name.
 * 
 * @return {WPBlockType | undefined} The found Block.
 */
const getCoreBlock = (block) => {
	return  getCoreBlocks().find( ({name}) => {
		return name === block
	})
}