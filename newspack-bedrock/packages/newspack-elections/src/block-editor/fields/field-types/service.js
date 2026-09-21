import {isEmail, getProtocol} from "@wordpress/url"

import Link from "./link"

export default class Service extends Link {

	static slug = "service"

	constructor(...args) {
		super(...args);
	}

}

