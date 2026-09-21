import FieldType from "./field"

export default class Link extends FieldType {

	static slug = "link"

	constructor(...args) {
		super(...args);
		this.formats = args[0].formats ?? []
	}

	valueToText(fieldValue){
		return fieldValue.url
	}

	getUrl(fieldValue){

		let { url = "" } = fieldValue
		
	}
}

