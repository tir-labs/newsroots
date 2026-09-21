export default class FieldType {
	
	constructor(args) {
		
		this.slug = args.slug
		this.label = args.label
		this.block = args.block
		this.display_icon = args.display_icon ?? null
	}

	valueToText(fieldValue){
		return fieldValue
	}

	icon(){
		return this.display_icon
	}
}

