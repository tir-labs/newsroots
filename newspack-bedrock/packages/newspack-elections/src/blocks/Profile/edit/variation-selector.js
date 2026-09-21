
import { useSelect, useDispatch } from '@wordpress/data';
import {
	__experimentalBlockVariationPicker as BlockVariationPicker,
	store as blockEditorStore
} from '@wordpress/block-editor';
import { store as blocksStore, synchronizeBlocksWithTemplate } from '@wordpress/blocks';


import { DEFAULT_TEMPLATE } from './default-template';

export const ProfileVariationSelector = ( props ) => {

	const { name, clientId, setAttributes } = props
	
	const {variations, getSelectedBlocksInitialCaretPosition, isBlockSelected} = useSelect(
		( select ) => {
			return {
				variations : select( blocksStore ).getBlockVariations( name, 'block' ) ?? [],
				getSelectedBlocksInitialCaretPosition: select( blockEditorStore ).getSelectedBlocksInitialCaretPosition,
				isBlockSelected: select(blockEditorStore).isBlockSelected
			}
		},
		[ name ]
	);

	

	const { replaceInnerBlocks, __unstableMarkNextChangeAsNotPersistent } =
		useDispatch( blockEditorStore );

	const selectVariation = ( nextVariation ) => {
		setAttributes( nextVariation.attributes );
		const nextBlocks = synchronizeBlocksWithTemplate([], nextVariation.innerBlocks ?? DEFAULT_TEMPLATE)

		__unstableMarkNextChangeAsNotPersistent();
		replaceInnerBlocks(
			clientId,
			nextBlocks,
			(nextBlocks.length !== 0 && isBlockSelected( clientId )),
			// This ensures the "initialPosition" doesn't change when applying the template
			// If we're supposed to focus the block, we'll focus the first inner block
			// otherwise, we won't apply any auto-focus.
			// This ensures for instance that the focus stays in the inserter when inserting the "buttons" block.
			getSelectedBlocksInitialCaretPosition()
		);
	};
	
	return <BlockVariationPicker 
		{...props}
		variations={ variations } 
		onSelect = { selectVariation }
	/>;
}


