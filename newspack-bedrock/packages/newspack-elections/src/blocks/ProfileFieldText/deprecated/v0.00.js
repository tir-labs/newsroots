import { migrateLegacyAttrsToField, isEligibleForFieldAttrMigration } from "@npe/editor"

const dep0_00 = {
	"attributes": {
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



export default dep0_00