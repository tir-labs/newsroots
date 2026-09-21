import { PanelRow } from "@wordpress/components";
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { Fragment } from "@wordpress/element";



import {PanelTextControl, PanelDateControl, PanelTextareaControl, PanelFieldset, PanelUrlControl} from "./Controls"

const fieldTypes = {
	text : PanelTextControl,
	textarea : PanelTextareaControl,
	date : PanelDateControl,
	url : PanelUrlControl,
}

const SidebarPluginPanel = (props) => {
	return (
		<PluginDocumentSettingPanel 
			title = {props.title}
			name = {props.name}
			icon={ <Fragment /> }
			className = {props.className ?? null}
		>
			{props.children}
		</PluginDocumentSettingPanel>
	)
}


export const ProfileMetaSidebarPanel = (props) => {

	const { 
		label,
		fields = [],
		groupFields = false,
		name
	 } = props


	const fieldsWithComponents = fields.map( (field) => {
		return {
			...field,
			Component : fieldTypes[field.type ?? "text"]
		}
	} )

	let renderFields = {grouped : [], ungrouped : []}

	if(groupFields) {
		renderFields = fieldsWithComponents.reduce( (accumulator, field) => {

			if(!Object.hasOwn(accumulator, "grouped")){
				accumulator.grouped = []
			}

			if(!Object.hasOwn(accumulator, "ungrouped")){
				accumulator.ungrouped = []
			}

			// No group property on the field save to ungrouped and return out of this loop
			if(Object.hasOwn(field, "group") === false){
				accumulator.ungrouped.push(field)
				return accumulator
			}

			// create an array of fields for a specific group
			if(!Object.hasOwn(accumulator.grouped, field.group)){
				accumulator.grouped[field.group] = []
			}

			accumulator.grouped[field.group].push(field)

			return accumulator

		}, renderFields )
	} else {
		renderFields.ungrouped = fieldsWithComponents
	}

	return (
        <SidebarPluginPanel 
            title={label}
            name={name}
			className = "npe-profile-editor__panel"
			key={`gov-profile-meta-${label}`}
        >
			{(groupFields) && ( <>
		
				{ Object.keys(renderFields.grouped).map( (groupKey) => (
					<PanelFieldset legend={groupKey} key={`gp-panel-${label}-fieldset-${groupKey}`}>
						
						{ renderFields.grouped[groupKey].map( ({Component, ...field}, index) => (
							<Component 
								label = { field["label"] } 
								meta_key = { field["meta_key"] }
								onChange = { null } 
								__next40pxDefaultSize = {true}
								key = {`field-${groupKey}-${field["meta_key"]}`}
							/>
						))}
						
					</PanelFieldset>
				)) }
			</>)}
			{ !groupFields && fieldsWithComponents.map( ({Component, ...field}, index) => (
				<PanelRow key = {`gp-panel-field-row-about-${index}`}>
					<Component 
						label = { field["label"] } 
						meta_key = { field["meta_key"] }
						onChange = { null } 
					/>
				</PanelRow>
			))}
		</SidebarPluginPanel>
    )

}


export default ProfileMetaSidebarPanel