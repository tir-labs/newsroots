import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

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
		"field" : {
			"type" : "object",
			"default" : {
				"type" : "text"
			}
		}
	},
	isEligible: (attributes, innerBlocks, data) => {

		const isEligible = innerBlocks.some( (innerBlock) => {
			return (innerBlock.name === "govpack/profile-label")
		})

		return isEligible;
	},
	save: () => {
		const blockProps = useBlockProps.save();
		const innerBlocksProps = useInnerBlocksProps.save( blockProps );
		
		return (
				<>
					{ innerBlocksProps.children }
				</>
		);
	},
	migrate: (attributes, innerBlocks) => {

		const labelRow = innerBlocks.filter( (innerBlock) => (innerBlock.name === "govpack/profile-label")).at(0)
		const {label = null} = labelRow.attributes 
		
		if(label){
			attributes.label = label
		}

		const newInnerBlocks = innerBlocks.filter( (innerBlock) => (innerBlock.name !== "govpack/profile-label"))

		return [
			attributes, 
			newInnerBlocks
		]
	}
}



export default dep0_01