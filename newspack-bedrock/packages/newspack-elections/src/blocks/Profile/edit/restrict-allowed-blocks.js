import {createHigherOrderComponent} from "@wordpress/compose"
import {useSelect} from "@wordpress/data";
import {store as blockEditorStore} from "@wordpress/block-editor";
import {store as blockStore} from "@wordpress/blocks";

import { ProfileBlockName } from "..";
export const withRestrictedAllowedBlocks = createHigherOrderComponent( ( BlockEdit ) => {

	
    return ( props ) => {

		const {clientId} = props

		const {parents, parent, parentType} = useSelect( (select) => {
			const parents = select(blockEditorStore).getBlockParentsByBlockName(clientId, ProfileBlockName)
			const parentId = parents?.[parents.length - 1] ?? null
			const parent = select(blockEditorStore).getBlock(parentId) ?? []
			const parentType = select(blockStore).getBlockType(parent?.name) ?? null
			return {
				parents,
				parentId,
				parent,
				parentType
			}
		}, [clientId])

		if((props.name === "core/group") && (parents.length > 0) && (parentType?.allowedBlocks?.length > 0)){
			props.attributes.allowedBlocks = parentType?.allowedBlocks 
		}

        return ( <BlockEdit key="edit" { ...props } /> );
    };
}, 'withRestrictedAllowedBlocks' );
