import { isString } from "lodash"
import { useState } from "@wordpress/element"

import {
	MenuGroup,
	MenuItem,
	MenuItemsChoice,
	TextHighlight,
	SearchControl
} from '@wordpress/components';

export function FilterableMenu({
	onSelect,
	choices = [],
	selectedValue,
	filterProps = ["label", "info"],
	doHighlighting = true
} = props){

	const [searchText, setSearchText] = useState("")
	const hasSearchText = (searchText !== "")

	// separate lowercased searchValue to the user's entered text isn't adjusted
	const searchValue = searchText.toLowerCase()

	let filteredChoices = !hasSearchText ? 
		choices :
		choices.filter( (choice) => {
			// each choice is an object containing {label, disabled, value, info}

			// check that the choice contains at least 1 of the filterProps, filter out (false) if not included
			const choiceKeys = Object.keys(choice)
			if(!choiceKeys.some( key => filterProps.includes(key))){
				return false
			}

			return choiceKeys.some( (key) => {
				// check the current key is in the filterProps
				if(!filterProps.includes(key)){
					return false;
				}

				// we only want to check against strings 
				if(!isString(choice[key])){
					return false;
				}

				// does the choice's values include the search value somewhere?
				// convert to lowercase to normalise against different casing
				// searchValue is lowerCased above
				return choice[key].toLowerCase().includes(searchValue)
			})
		})

	
		
	if(doHighlighting){
		filteredChoices = filteredChoices.map( (f) => {
			
			return {
				...f,
				label : (<TextHighlight text={f.label} highlight={searchText}/>),
				info: (<TextHighlight text={f.info} highlight={searchText}/>),
			}
		})
	}

	return (
		<>
			<SearchControl
				__nextHasNoMarginBottom
				value = {searchText}
				onChange = {setSearchText}
				label = "Field Filter"
			/>
			<MenuGroup>
				{(filteredChoices.length >= 1) && (
					<MenuItemsChoice
						choices={ filteredChoices }
						value={ selectedValue }
						onSelect={ (v) => {
							onSelect(v) 
						}}
					/>
				)}

				{(filteredChoices.length === 0) && (
					<MenuItem
						disabled = {true}
					>
						No Fields Found
					</MenuItem>
				)}
			</MenuGroup>
		</>
	)
}