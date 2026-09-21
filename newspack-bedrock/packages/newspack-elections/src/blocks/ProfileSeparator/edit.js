/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { HorizontalRule } from '@wordpress/components';

import { 
	useBlockProps,
	getColorClassName,
	withColors,
	useSettings,
	__experimentalUseColorProps as useColorProps,
	__experimentalGetSpacingClassesAndStyles as getSpacingClassesAndStyles,
	getColorObjectByAttributeValues,
	__experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
} from "@wordpress/block-editor"


import {useMemo} from "@wordpress/element"

import SpacerControls from './controls'

const useAllColors = () => {

	const [
		userPalette,
		themePalette,
		defaultPalette,
		userGradients,
		themeGradients,
		defaultGradients,
	] = useSettings(
		'color.palette.custom',
		'color.palette.theme',
		'color.palette.default',
		'color.gradients.custom',
		'color.gradients.theme',
		'color.gradients.default'
	);

	const colors = useMemo(
		() => [
			...( userPalette || [] ),
			...( themePalette || [] ),
			...( defaultPalette || [] ),
		],
		[ userPalette, themePalette, defaultPalette ]
	);

	return colors
}

function SeperatorEdit( {attributes, setAttributes, context, ...props} ) {

	const {
		styles : originalStyles = {}, 
		...blockProps
	} = useBlockProps();

	const { 
		separatorColor, 
		setSeparatorColor,
	} = props


	const { 
		backgroundColor, 
		opacity, 
		style = {},
		height : ownHeight = false,
		
	} = attributes;

	let { 
		'npe/separatorStyle' : inheritedStyle = {},
		'npe/separatorColor' : inheritedSeperatorColor = {}
	} = context;

	
	const { 
		height : parentHeight = false
	} = inheritedStyle;
	

	/**
	 * Copy the Original Styles into a new variable so we can modify it safely
	 */
	let styles = {...originalStyles}

	/**
	 * Use the blocks height from its Attribute, fallback to parent if not set
	 * This lets the block override the passed context
	 */
	const height = ownHeight || parentHeight

	styles = {
		...styles,
		borderTopWidth : height
	}

	if(inheritedStyle && ! style?.spacing?.margin){

		let fakeBlockProps = getSpacingClassesAndStyles({
			style : inheritedStyle
		})
	
		styles = {
			...styles,
			...fakeBlockProps?.style ?? {}
		}
	}

	/**
	 * Get a color from the Context and create a color object. Used to fallback if no own color is set
	 */
	
	inheritedSeperatorColor = getColorObjectByAttributeValues(useAllColors(), inheritedSeperatorColor)


	/**
	 * We need to look at the value of color on the color object to see which of these is valid
	 * Prefer the seperatorColor, ie the one which was chosen for this block. Fallback to the 
	 * parent context shared color.
	 */
	let currentColor = {}
	if( separatorColor?.color ){
		currentColor = separatorColor
	} else if( inheritedSeperatorColor?.color ) {
		currentColor = inheritedSeperatorColor
	}

	const hasCustomColor = currentColor.slug === undefined ;
	const colorClass = getColorClassName( 'color', currentColor.slug );


	const className = clsx(
		{
			'has-text-color': backgroundColor || currentColor.slug ,
			[ colorClass ]: colorClass,
			'has-css-opacity': opacity === 'css',
			'has-alpha-channel-opacity': opacity === 'alpha-channel',
		},
		currentColor.class ?? false
	);

	
	
	if(hasCustomColor){
		styles = {
			...styles,
			color: currentColor.color,
			backgroundColor: currentColor.color
		}
	};

	
	return (
		<>	
			<SpacerControls 
				orientation = "vertical"
				height = {height}
				setAttributes={setAttributes}
				isResizing={false}
				separatorColor={separatorColor}
				setSeparatorColor = {setSeparatorColor}
			/>
			<HorizontalRule { ...useBlockProps({
				className,
				style : styles
				}) } 
			/>
		</>
	)
}

const separatorColorAttributes = {
	separatorColor: 'separator-color',
}

const Edit = withColors( separatorColorAttributes )( SeperatorEdit );

export {Edit}
export default Edit
