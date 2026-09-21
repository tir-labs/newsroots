/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import {
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	Panel,
	PanelBody,
	PanelRow
} from '@wordpress/components';
import { useViewportMatch } from '@wordpress/compose';
import { InspectorControls} from "@wordpress/block-editor"
import { __ } from "@wordpress/i18n"

import ProfileFieldsDropDown from "./ProfileFieldsDropDown"

const useToolsPanelDropdownMenuProps = () => {
	const isMobile = useViewportMatch( 'medium', '<' );
	return ! isMobile
		? {
				popoverProps: {
					placement: 'left-start',
					// For non-mobile, inner sidebar width (248px) - button width (24px) - border (1px) + padding (16px) + spacing (20px)
					//offset: 259,
				},
		  }
		: {};
};

const ToolsPanelItemWide = styled( ToolsPanelItem )`
	grid-column: span 2;
`;


export const ProfileFieldsInspectorControl = ({
	fieldKey,
	onSelectField,
	fieldType = "text",
	fields
}) => {


	//const dropdownMenuProps = useToolsPanelDropdownMenuProps()
	//const isMobile = useViewportMatch( 'medium', '<' );

	
	return(
		<InspectorControls group="settings">
		
				<PanelBody title={ __( 'Profile Field', 'govpack' ) }>
					<PanelRow>
						<ProfileFieldsDropDown 
							className={ 'govpack-profile-field-select' }
							onSelectField={ onSelectField }
							selectedValue={ fieldKey }
							choices={ fields }
							disableEmptyFields = {false}
						/>
					</PanelRow>
				</PanelBody>
			
		</InspectorControls>
	)
}
