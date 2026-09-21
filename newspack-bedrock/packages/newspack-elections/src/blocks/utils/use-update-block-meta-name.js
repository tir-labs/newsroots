import { useEffect } from "@wordpress/element"
import { useBlockProps, useBlockEditContext, store as blockEditorStore } from "@wordpress/block-editor"
import { select, useSelect, dispatch } from "@wordpress/data"





export const useUpdateBlockMetaName = (name) => {

	const { clientId } = useBlockEditContext()
	const blockProps = useBlockProps()
	const { updateBlockAttributes, __unstableMarkNextChangeAsNotPersistent } = dispatch( blockEditorStore );

	const setAttributes = ( newAttributes ) => {

		const multiSelectedBlockClientIds = select( blockEditorStore ).getMultiSelectedBlockClientIds() ?? [];
		const clientIds = multiSelectedBlockClientIds.length ? 
			multiSelectedBlockClientIds : 
			[ clientId ] ;
		
		__unstableMarkNextChangeAsNotPersistent()
		updateBlockAttributes( clientIds, newAttributes );
	}

	const { metadata, ...attributes } = useSelect( (select) => {
		return select(blockEditorStore).getBlockAttributes(clientId)
	}, [clientId])


	useEffect( () => {
		
		setAttributes({metadata : {
			...metadata,
			name
		}})

	}, [name] )

}

export default useUpdateBlockMetaName