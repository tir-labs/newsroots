/**
 * Newspack Elections Block Editor Bootstrapper
 * 
 * Loads and Creates Services used by Newspack Elections Blocks. 
 * 
 * File is embedded into the the editor-blocks.js asset within webpack
 * rather than being directly referenced.
 */

import domReady from "@wordpress/dom-ready"
import { registerBlockVariation } from "@wordpress/blocks"


import { registerBlockSupports } from "./block-supports" 
import { restoreBlocks } from "./restore-blocks";
import { initStore } from "./fields";


registerBlockVariation("core/query", {
	name : "npe/profile-query",
	title : "Election Profile Loop",
	attributes: {
		align : "wide",
        query: {
            postType: 'govpack_profiles',
			perPage: 12,
			order: "asc",
			orderBy:"title"
        },
    },
	innerBlocks: [
    [
        'core/post-template',
        	{
				layout: {
					type: "grid",
					columnCount: 3
				}
			},
        	[ 
				[ 'npe/profile' ]
			],
    	],
    	[ 'core/query-pagination' ],
    	[ 'core/query-no-results' ],
	]
})

// Create a Redux store for fields used by Profiles
initStore()

// Add Custom Block Support
registerBlockSupports()


// Newspack disabled a few blocks we need, this is where we can re-enable them
domReady( () => {
	const requiredCoreBlocks = [
		"core/post-featured-image",
		"core/query",
	]
	restoreBlocks(requiredCoreBlocks)
})


export * from "./block-supports" 
export * from "./profile"
export * from "./components" 
export * from "./fields"