import { useBlockProps } from "@wordpress/block-editor"
import { ProfileSelector as ProfileSelectorPlaceholder } from "./../../../components/ProfileSelector.jsx"

export const ProfileSelector = ( props ) => {

	const {attributes, setAttributes} = props
	const blockProps = useBlockProps();



	return (
		<div {...blockProps }>
			<ProfileSelectorPlaceholder setProfile = {props.setProfile} />
		</div>
	)
}