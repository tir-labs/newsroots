import FieldType from "./field"

export default class Taxonomy extends FieldType {

	static slug = "taxonomy"

	constructor(...args) {
		super(...args);
	}

	valueToText(fieldValue){
		

		// something went wrong and a non-array got passed here
		if(!Array.isArray(fieldValue)){
			return ""
		}

		// there are no terms, return early
		if(fieldValue.length === 0){
			return ""
		}

		return fieldValue.map( (term) => term.name).join(", ")

	}
}

