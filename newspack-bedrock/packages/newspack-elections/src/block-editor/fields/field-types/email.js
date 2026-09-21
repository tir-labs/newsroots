import {isEmail, getProtocol} from "@wordpress/url"

import Link from "./link"

export default class Email extends Link {

	static slug = "email"

	constructor(...args) {
		super(...args);
	}

	getUrl(fieldValue = {}){

		if(!fieldValue){
			return false
		}

		let { url = "" } = fieldValue

		if(!isEmail(url)){
			return false
		}

		if(getProtocol(url) !== "mailto:"){
			url = "mailto:" + url;
		}
		
		return url

	}
}

