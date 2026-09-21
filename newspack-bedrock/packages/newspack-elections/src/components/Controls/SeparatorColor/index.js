import { 
	__experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
	__experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
} from "@wordpress/block-editor"

export const separatorColorAttributes = {
	separatorColor: 'separator-color',
}



export  default function SeparatorColor (props)  {

	const {
		colorValue, 
		onColorChange
	} = props

	return (
		<ColorGradientSettingsDropdown
			settings={ [ {
				label : "Separator Color",
				colorValue: colorValue,
				onColorChange: onColorChange,
				clearable: true,
				...useMultipleOriginColorsAndGradients()
			} ] }
			hasColorsOrGradients={ true }
			disableCustomColors={ false }
			__experimentalIsRenderedInSidebar
		/>
	)
}