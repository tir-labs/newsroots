import { connection as connectionIcon, more,
    arrowLeft,
    arrowRight,
    arrowUp,
    arrowDown } from '@wordpress/icons';
import { Toolbar, ToolbarDropdownMenu, ToolbarGroup } from '@wordpress/components';
import { BlockControls } from '@wordpress/block-editor';

import './view.scss'

import { ProfileFieldsMenu } from "./ProfileFieldsMenu"

export const ProfileFieldsToolBar = (props) => {

	const {
		onSelectField,
		fieldKey,
		fields = [],
		showFieldsWithEmptyValues = false,
		disableEmptyFields = true
	} = props
	
	const baseClassName = "govpack-profile-field-select"

	return (
		<BlockControls group="parent">
		
           		<ToolbarDropdownMenu 
					icon = {connectionIcon}
					className = "gp-toolbar-item gp-toolbar-item--field-select"
					popoverProps = { {
						className: `${baseClassName}__popover`
					} }
				>
				   { () => (
				   <ProfileFieldsMenu 
				   		className={ 'govpack-profile-field-select' }
						onSelectField={ onSelectField }
						selectedValue={ fieldKey }
						choices={ fields }
						showFieldsWithEmptyValues = {showFieldsWithEmptyValues}
						disableEmptyFields = {disableEmptyFields}
				   />
				   )}
		  		</ToolbarDropdownMenu>
			
		</BlockControls>
    );
}