import { migrateLegacyAttrsToField, isEligibleForFieldAttrMigration } from "@npe/editor"

const dep0_01 = {
	"attributes": {
		"style" : {
			"type" : "object",
			"default" : {
				"layout" : {
					"selfStretch":"fill",
					"flexSize":null
				}
			}
		},
		"layout":{
			"type" : "object",
			"default" : {
				"type":"flex",
				"flexWrap":"no-wrap",
				"justifyContent":"space-between"
			}
		},
		"align": {
			"type": "string",
			"default": "full"
		},
		"showLabel" : {
			"type": "boolean"
		},
		"hideFieldIfEmpty" : {
			"type": "boolean",
			"default" : true
		},
		"label" : {
			"type": "string"
		},
		"fieldKey" : {
			"type": "string"
		},
		"fieldType" : {
			"type": "string"
		},
	},
	isEligible: isEligibleForFieldAttrMigration,
	save: () => {
	},
	migrate: migrateLegacyAttrsToField
}



export default dep0_01