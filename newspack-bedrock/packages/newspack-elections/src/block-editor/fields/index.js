import { registerGovpackStore, store } from "./store"
import { select, subscribe } from "@wordpress/data"
import { createFieldTypes } from "./types";

export { storeConfig, store } from './store';

export const initStore = () => {
	
	// Just create the store
	registerGovpackStore()

	
	// early request for fields and fieldTypes
	select(store).getFieldTypes()
	select(store).getFields()

	// create field type objects as they arrive from the API by 
	// subscribing to changes in the underlying redux store
	let cachedCountFieldTypes = null
	subscribe( () => {
		const types = select(store).getFieldTypes()

		// the number should only ever go up, so if we match then leave and csave cpu cycles
		if(cachedCountFieldTypes === types.length) {
			return 
		}

		// this is the condition on first run, populate it with the value from REST
		if(cachedCountFieldTypes === null){
			cachedCountFieldTypes = types.length
		} 

		createFieldTypes(types)

	}, store)
}




export { getFieldTypeObject, createFieldTypes } from './types'
export * from './hooks';