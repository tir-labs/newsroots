import { __ } from '@wordpress/i18n';
import {Panel, PanelBody, PanelRow, ToggleControl} from '@wordpress/components';


const LabelPositionToggle = ({
	labelsAbove,
	setAttributes
}) => {
	return (

		<ToggleControl
			label={ __( 'Position Labels Above', 'newspack-elections' ) }
			checked={ labelsAbove }
			onChange={ () => setAttributes( { labelsAbove: ! labelsAbove } ) }
			help={
				labelsAbove
					? 'Labels will be shown above.'
					: 'Labels will be shown beside.'
			}
		/>
	)
}

const ProfileLabelPanel = (props) => {

    const {
        attributes : {showLabels = true} = {},
        setAttributes,
    } = props

   

	return (
		<Panel>
			<PanelBody 
				title={ __( 'Label', 'newspack-elections' ) }
				initialOpen = { false }
			>
				<PanelRow>
					<ToggleControl
						label={ __( 'Display Row Labels', 'newspack-elections' ) }
						checked={ showLabels }
						onChange={ () => setAttributes( { showLabels: ! showLabels } ) }
					/>
				</PanelRow>
			</PanelBody>
		</Panel>
	)
}

export default ProfileLabelPanel