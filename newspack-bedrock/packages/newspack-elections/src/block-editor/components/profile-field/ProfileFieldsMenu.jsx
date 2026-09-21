import { FilterableMenu } from "./../filterable-menu";

export function ProfileFieldsMenu({
	onSelectField,
	choices,
	selectedValue,
	showFieldsWithEmptyValues = true,
	disableEmptyFields = true,
	emptyProfileValueText = "-"
} = props){

	
	let filteredChoices = choices.map( (f) => {
		const fieldTextValue = f.field_type.valueToText(f.value)
		
		const info = (f.value && f.value !== "" && fieldTextValue) ? fieldTextValue : emptyProfileValueText

		return {
			label : f.label,
			value : f.slug,
			info : info,
			disabled : ( (disableEmptyFields && (!f.value || f.value === "")) ? true : false)
		}
	})

	if(showFieldsWithEmptyValues === false && disableEmptyFields === true){
		filteredChoices = filteredChoices.filter( (f) => !f.disabled)
	}

	return (
		<FilterableMenu 
			choices={ filteredChoices }
			value={ selectedValue }
			onSelect={ onSelectField }
		/>
	)
}