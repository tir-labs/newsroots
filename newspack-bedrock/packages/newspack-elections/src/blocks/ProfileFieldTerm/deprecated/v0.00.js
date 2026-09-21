import { resolveSelect, suspendSelect } from "@wordpress/data"
import { store as coreStore } from "@wordpress/core-data"

const dep0_0 = {
	attributes: {
		taxonomy: {
			type: "string"
		},
		separator: {
			"type": "string",
			"default": ", "
		},
		displayLinks : {
			"type": "boolean",
			"default" : false
		},
		termLimit : {
			"type": "number",
			"default" : 1
		}
	},
	supports : {},
	isEligible: (attributes, innerBlocks, data) => {


		
		if(
			(attributes.fieldType === "taxonomy") ||
			(typeof attributes.fieldKey !== "undefined") 
		){	
			return false
		}

		if(attributes.taxonomy){
			return true;
		}

		return false
	},
	save: (props) => {
		
	},
	migrate:  (attributes, innerBlocks) => {
		// Destructure attributes to remove the old taxonomy attribute
		const {taxonomy, ...restAttributes} = attributes

		let fieldKey;
		switch(taxonomy){
			case "govpack_party":
				fieldKey = "party";
				break;
			case "govpack_state":
				fieldKey = "state";
				break;
			case "govpack_legislative_body":
				fieldKey = "legislative_body";
				break;
			case "govpack_officeholder_title":
				fieldKey = "position";
				break;
			case "govpack_officeholder_status":
				fieldKey = "status";
				break;
		}

			
		return [
			{
				...restAttributes,
				fieldKey : fieldKey,
				fieldType: "taxonomy"
			},
			innerBlocks
		]
	}


}


const migrateUsingData = (attributes, innerBlocks) => {
		
		
	// Destructure attributes to remove the old taxonomy attribute
	const {taxonomy, ...restAttributes} = attributes

	// Get a Field from the data store by selecting the whole set then filtering down where 
	// type === taxonomy and then by matching the field and block taxonomy. 
	// The data store may not have received all the field entities and this doesn't rerun, 
	// use resolveSelect so we get a promise and eventually the fields as an array. 
	// Finally get the field object at index 0 from thefiltered array. 
	// Fallback to a null so we can exit quickly afterward
	const field = resolveSelect(coreStore)
		.getEntityRecords('govpack', 'fields', { per_page: '-1' })
		.then( (results) => {
			return results.filter( (f) => f.type === "taxonomy")
			.filter( (f) => f.taxonomy === taxonomy)
			.at(0)
		}
		) ?? null
	
		
	// If we dont have a field or a valid field slug then exit early
	if((!field) || (!field?.slug)){
		return false
	}
	
	// Generate the new attributes from the old attribute and the new fieldKey, and fieldType.
	// Then return to the editor
	return [
		{
			...restAttributes,
			fieldKey : field?.slug,
			fieldType: "taxonomy"
		},
		innerBlocks
	]
}
export default dep0_0