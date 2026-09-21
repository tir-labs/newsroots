

/**
 * Internal dependencies
 */

import {createGPBlockListBlockFilter, createGPBlockRegisterFilter, createGPBlockEditFilter} from "./utils"
import fieldAware from "./field-aware.js"


const features = [
	fieldAware
]

export const registerBlockSupports = () => {
	createGPBlockListBlockFilter(features)
	createGPBlockRegisterFilter(features)
	createGPBlockEditFilter(features)
}

export {migrateLegacyAttrsToField, isEligibleForFieldAttrMigration} from "./field-aware"