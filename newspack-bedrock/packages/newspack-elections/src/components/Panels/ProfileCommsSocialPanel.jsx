import { __ } from '@wordpress/i18n';
import {Panel, PanelBody, PanelRow, ToggleControl, BaseControl, ButtonGroup, Button} from '@wordpress/components';
import {__experimentalUnitControl as UnitControl} from '@wordpress/components';

import { ControlledPanel } from './ControlledPanel';

const ProfileCommsSocialPanel = (props) => {

    const {
		title,
        attributes,
        setAttributes,
		display : shouldDisplayPanel = true,
		parentAttributeKey
    } = props


	if(!shouldDisplayPanel){
		return null
	}

	const setSubAttributes = (attrs) => {

		const newAttrs = {
			...attributes[parentAttributeKey],
			...attrs
		}

		setAttributes({ [parentAttributeKey] : newAttrs })
	}
	

	let controls = [
		{
			label : __( "Display Official", 'newspack-elections' ),
			attr : "showOfficial", 
		},{
			label : __( "Display Campaign", 'newspack-elections' ),
			attr : "showCampaign", 
		},{
			label : __( "Display Personal", 'newspack-elections' ),
			attr : "showPersonal", 
		}
	]

	controls = controls.map( (control) => ({
		...control, 
		checked : attributes[parentAttributeKey][control.attr],
		onChange : () => {
			setSubAttributes( { [control.attr]: ! attributes[parentAttributeKey][control.attr] } ) 
		}
	}))

	
    return (

		<ControlledPanel 
			controls = {controls} 
			title = { title } 
		/>

		/*
		<Panel>
			<PanelBody 
				title={ title }
				initialOpen={ true }
				>
				<PanelRow>
					<ToggleControl
						label={ __( 'Display Official', 'newspack-elections' ) }
						checked={ showOfficial ?? true }
						onChange={ () => setSubAttributes( { showOfficial: ! showOfficial } ) }
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						label={ __( 'Display Campaign', 'newspack-elections' ) }
						checked={ showCampaign ?? true }
						onChange={ () => setSubAttributes( { showCampaign: ! showCampaign } ) }
					/>
				</PanelRow>
				<PanelRow>
					<ToggleControl
						label={ __( 'Display Personal', 'newspack-elections' ) }
						checked={ showPersonal ?? true }
						onChange={ () => setSubAttributes( { showPersonal: ! showPersonal } ) }
					/>
				</PanelRow>
			</PanelBody>
		</Panel>
		*/
	)
}

export default ProfileCommsSocialPanel