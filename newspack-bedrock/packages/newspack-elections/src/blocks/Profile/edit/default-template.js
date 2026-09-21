const DEFAULT_ROW_PARTY = [ "npe/profile-row", {
	showLabel : false,
	field : {
		"key":"party",
		"type":"taxonomy"
	},
	fontFamily: "system-sans-serif",
	fontSize: "small",
	style : {
		typography: {
			fontWeight: "700"
		}
	}
}, [
	[ "npe/profile-field-term", {}, [] ]
]]

const DEFAULT_ROW_STATUS = [ "npe/profile-row", {
	showLabel : false,
	field : {
		"key":"status",
		"type":"taxonomy"
	},
	fontFamily: "system-sans-serif",
	fontSize: "small",
	style : {
		typography: {
			fontWeight: "500"
		}
	}
}, [
	[ "npe/profile-field-term", {}, [] ]
]]

const DEFAULT_IMAGE = [ "core/post-featured-image", {
		"isLink":true,
		"aspectRatio":"1"
	}, []]

const DEFAULT_SEPARATOR = ["npe/profile-separator", {
	"style":{"spacing":{"margin":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"}}}
}, [] ]

export const DEFAULT_TEMPLATE = [
	DEFAULT_IMAGE,
	[ "npe/profile-row-group", {
		"style":{
			"spacing":{
				"padding":{
					"top":"var:preset|spacing|40",
					"bottom":"var:preset|spacing|40",
					"left":"var:preset|spacing|40",
					"right":"var:preset|spacing|40",
				},
				"blockGap":"var:preset|spacing|0"
			}
		}
		},[
			[ "npe/profile-name", {}, []],
			DEFAULT_ROW_PARTY,
			DEFAULT_ROW_STATUS,
		]
	]
]


export const HORIZONTAL_TEMPLATE = [
	[ "core/post-featured-image", {
		isLink: true,
		aspectRatio: "1",
		width: "100px",
		height: "100%",
		style: {
			layout: {
				selfStretch: "fixed",
				flexSize:"150px"
			}
		}
	}, []],
	[ "npe/profile-row-group", {
		"style":{
			"spacing":{
				"padding":{
					"top":"var:preset|spacing|40",
					"bottom":"var:preset|spacing|40",
					"left":"var:preset|spacing|40",
					"right":"var:preset|spacing|40"
				},
				"blockGap":"var:preset|spacing|40"
			},
			"layout":{
				"selfStretch":"fill",
				"flexSize":null
			}
		}
		},[
			[ "npe/profile-name", {}, []],
			DEFAULT_ROW_PARTY,
			DEFAULT_ROW_STATUS,
		]
	]
]



export const FULL_TEMPLATE = [

	DEFAULT_IMAGE,
	["npe/profile-row-group", { "style": { "spacing": { "padding":{ "top":"var:preset|spacing|40", "bottom":"var:preset|spacing|40", "left":"var:preset|spacing|40", "right":"var:preset|spacing|40" }, "blockGap":"var:preset|spacing|0" } } }, [
		[ "npe/profile-name", {}, [] ],
		DEFAULT_ROW_PARTY,
		DEFAULT_ROW_STATUS,
		["npe/profile-row", {"showLabel":false,"field":{"type":"text","key":"endorsements"}} , [
				["npe/profile-field-text", {}, [] ]
		] ],
		DEFAULT_SEPARATOR,
		["core/group", {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}}, [
			["core/group", {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}}, [
				["core/heading", { "level":4, "fontSize":"small", "content" : "Official"}, []],
				["npe/profile-social-links", {"openInNewTab":true,"size":"has-small-icon-size","className":"is-style-logos-only","style":{"spacing":{"blockGap":{"left":"5px"}}}}, [
					["npe/profile-social-link", {"field":{"key":"x_official"}}, []],
					["npe/profile-social-link", {"field":{"key":"facebook_official"}}, []],
					["npe/profile-social-link", {"field":{"key":"instagram_official"}}, []],
					["npe/profile-social-link", {"field":{"key":"youtube_official"}}, [] ],
				]
			] ]
		] ] ],
		["core/group", {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}}, [
			["core/group", {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}}, [
				["core/heading", { "level":4, "fontSize":"small", "content" : "Campaign"}, []],
				["npe/profile-social-links", {"openInNewTab":true,"size":"has-small-icon-size","className":"is-style-logos-only","style":{"spacing":{"blockGap":{"left":"5px"}}}}, [
					["npe/profile-social-link", {"field":{"key":"x_campaign"}}, []],
					["npe/profile-social-link", {"field":{"key":"facebook_campaign"}}, []],
					["npe/profile-social-link", {"field":{"key":"instagram_campaign"}}, []],
					["npe/profile-social-link", {"field":{"key":"youtube_campaign"}}, [] ],
				]
			] ]
		] ] ],
		["core/group", {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}}, [
			["core/group", {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}}, [
				["core/heading", { "level":4, "fontSize":"small", "content" : "Personal"}, []],
				["npe/profile-social-links", {"openInNewTab":true,"size":"has-small-icon-size","className":"is-style-logos-only","style":{"spacing":{"blockGap":{"left":"5px"}}}}, [
					["npe/profile-social-link", {"field":{"key":"x_personal"}}, []],
					["npe/profile-social-link", {"field":{"key":"facebook_personal"}}, []],
					["npe/profile-social-link", {"field":{"key":"instagram_personal"}}, []],
					["npe/profile-social-link", {"field":{"key":"youtube_personal"}}, [] ],
				]
			] ]
		] ] ],
		DEFAULT_SEPARATOR,
		["core/group", {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}}, [
			["core/group", {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"top"}}, [
				["core/heading", { "level":4, "fontSize":"small", "content" : "Official"}, []],
				["core/group", {"style":{"spacing":{"blockGap":"0","margin":{"bottom":"0"}}},"layout":{"type":"constrained","justifyContent":"right"}}, [
					["core/group", {"style":{"spacing":{"blockGap":"var:preset|spacing|30","margin":{"bottom":"var:preset|spacing|20"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}}, [
						["npe/profile-field-link", {"linkFormat":"icon","iconSize":"has-small-icon-size","lock":{"move":false,"remove":false},"field":{"type":"email","key":"email_official"}}, [] ],
						["npe/profile-field-link", {"linkFormat":"icon","iconSize":"has-small-icon-size","lock":{"move":false,"remove":false},"field":{"type":"phone","key":"phone_official"}}, [] ],
						["npe/profile-field-link", {"linkFormat":"icon","iconSize":"has-small-icon-size","lock":{"move":false,"remove":false},"field":{"type":"phone","key":"fax_official"}}, [] ],
						["npe/profile-field-link", {"linkFormat":"icon","iconSize":"has-small-icon-size","lock":{"move":false,"remove":false},"field":{"type":"link","key":"website_official"}}, [] ]
					]],
					[ "npe/profile-field-text", {"style":{"typography":{"textAlign":"right"}},"layout":{"type":"flex","justifyContent":"right"},"metadata":{"name":"d"},"field":{"type":"text","key":"address_official"}}, [] ]
				]]
			] ],
			["core/group", {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"top"}}, [
				["core/heading", { "level":4, "fontSize":"small", "content" : "Campaign"}, []],
				["core/group", {"style":{"spacing":{"blockGap":"0","margin":{"bottom":"0"}}},"layout":{"type":"constrained","justifyContent":"right"}}, [
					["core/group", {"style":{"spacing":{"blockGap":"var:preset|spacing|30","margin":{"bottom":"var:preset|spacing|20"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}}, [
						["npe/profile-field-link", {"linkFormat":"icon","iconSize":"has-small-icon-size","lock":{"move":false,"remove":false},"field":{"type":"email","key":"email_campaign"}}, [] ],
						["npe/profile-field-link", {"linkFormat":"icon","iconSize":"has-small-icon-size","lock":{"move":false,"remove":false},"field":{"type":"phone","key":"phone_campaign"}}, [] ],
						["npe/profile-field-link", {"linkFormat":"icon","iconSize":"has-small-icon-size","lock":{"move":false,"remove":false},"field":{"type":"phone","key":"fax_campaign"}}, [] ],
						["npe/profile-field-link", {"linkFormat":"icon","iconSize":"has-small-icon-size","lock":{"move":false,"remove":false},"field":{"type":"link","key":"website_campaign"}}, [] ]
					]],
					[ "npe/profile-field-text", {"style":{"typography":{"textAlign":"right"}},"layout":{"type":"flex","justifyContent":"right"},"metadata":{"name":"d"},"field":{"type":"text","key":"address_campaign"}}, [] ]
				]]
			] ]
		]],
		DEFAULT_SEPARATOR,
		["npe/profile-social-links", {"openInNewTab":true,"size":"has-small-icon-size","className":"is-style-logos-only","style":{"spacing":{"blockGap":{"left":"5px"}}}}, [
			["npe/profile-social-link", {"field":{"key":"ballotpedia"}}, [] ],
			["npe/profile-social-link", {"field":{"key":"fec_id"}}, [] ],
			["npe/profile-social-link", {"field":{"key":"gab"}}, [] ],
			["npe/profile-social-link", {"field":{"key":"wikipedia"}}, [] ],
			["npe/profile-social-link", {"field":{"key":"openstates_id"}}, [] ],
			["npe/profile-social-link", {"field":{"key":"opensecrets_id"}}, [] ],
			["npe/profile-social-link", {"field":{"key":"linkedin"}}, [] ],
			["npe/profile-social-link", {"field":{"key":"rumble"}}, [] ]
		]] 
	] ]
]
