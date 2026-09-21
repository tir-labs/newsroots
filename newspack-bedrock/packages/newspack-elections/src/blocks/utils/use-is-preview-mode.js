import {useSelect, select} from "@wordpress/data"
import { store as blockEditorStore } from "@wordpress/block-editor"
import {useMemo} from "@wordpress/element"

/*
export const useIsPreviewMode = () => {
	return useSelect( ( select ) => {
		console.count("Is Preview Mode")
		const settings = select( blockEditorStore ).getSettings()
		return settings?.isPreviewMode ?? settings?.__unstableIsPreviewMode ?? false
	}, [] );
}
*/
export const useIsPreviewMode = (clientId ) => {
	
	const {__unstableIsPreviewMode, isPreviewMode } = select( blockEditorStore ).getSettings()

	const isPreview = useMemo( () => {
		return isPreviewMode ?? __unstableIsPreviewMode ?? false
	}, [ isPreviewMode, __unstableIsPreviewMode ])
	
	return isPreview
}

export default useIsPreviewMode