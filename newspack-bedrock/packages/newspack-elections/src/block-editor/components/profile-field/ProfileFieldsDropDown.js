import styled from '@emotion/styled';

import { chevronDown } from '@wordpress/icons';
import { __ } from "@wordpress/i18n"


import { DropdownMenu } from '@wordpress/components';

import { useField } from "./../../fields"
import { ProfileFieldsMenu } from './ProfileFieldsMenu';



const StyledDropdownMenu = styled( DropdownMenu )`
	width: 100%;
`;

export  function ProfileFieldsDropDown( props ) {

	const {
		className,
		selectedValue,
		popoverProps = {}
	} = props

	const CurrentField = useField(selectedValue)


	return (
		<StyledDropdownMenu
			className={ className }
			label={ __( 'Profile Field Selector' ) }
			text={ CurrentField?.label ?? "Select a Profile Field" }
			popoverProps={ {
				...popoverProps,
				style : {
					"minWidth" : "250px"
				},
				className: `${ className }__popover`,
			} }
			icon={ chevronDown }
			toggleProps={ { 
				iconPosition: 'right',
				variant: "secondary"
			} }
		>
			{ () => (
				<div className={ `${ className }__container` }>
					<ProfileFieldsMenu {...props} />
				</div>
			) }
		</StyledDropdownMenu>
	);
}

export default ProfileFieldsDropDown