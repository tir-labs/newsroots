import {isEmpty} from "lodash"
import clsx from 'clsx';
/**
 * WordPress dependencies
 */
import { __, _x } from '@wordpress/i18n';
import { InspectorControls, useBlockProps, RichText } from "@wordpress/block-editor"
import { store as coreStore, useEntityProp } from '@wordpress/core-data'
import { useSelect } from '@wordpress/data';
import { useMemo } from "@wordpress/element"
import { 
	ToggleControl,
	RangeControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem
} from '@wordpress/components';

import { useViewportMatch } from '@wordpress/compose';
import { getBlockDefaultClassName } from "@wordpress/blocks"

/**
 * Internal dependencies
 */
import { useProfileFieldAttributes } from "@npe/editor";


const ELLIPSIS = '…';

function useToolsPanelDropdownMenuProps() {
	const isMobile = useViewportMatch( 'medium', '<' );
	return ! isMobile
		? {
				popoverProps: {
					placement: 'left-start',
					// For non-mobile, inner sidebar width (248px) - button width (24px) - border (1px) + padding (16px) + spacing (20px)
					offset: 259,
				},
		  }
		: {};
}


function Edit( props ) {

	const { fieldKey, value, field, profileId } =  useProfileFieldAttributes(props) 
	const blockProps = useBlockProps()
	const dropdownMenuProps = useToolsPanelDropdownMenuProps();

	const { context, attributes, setAttributes } = props
	const { postType } = context
	const { showMoreOnNewLine, bioLength, moreText } = attributes

	const [
		rawExcerpt,
		setExcerpt,
		{ rendered: renderedExcerpt, protected: isProtected } = {},
	] = useEntityProp( 'postType', postType, 'excerpt', profileId );

	const postTypeSupportsExcerpts = useSelect(
		( select ) => {
			return !! select( coreStore ).getPostType( postType )?.supports?.excerpt;
		},
		[ postType ]
	);

	

	/**
	 * When excerpt is editable, strip the html tags from
	 * rendered excerpt. This will be used if the entity's
	 * excerpt has been produced from the content.
	 */
	const strippedRenderedExcerpt = useMemo( () => {
		if ( ! renderedExcerpt ) {
			return '';
		}
		const document = new window.DOMParser().parseFromString(
			renderedExcerpt,
			'text/html'
		);
		return document.body.textContent || document.body.innerText || '';
	}, [ renderedExcerpt ] );

	/**
	 * translators: If your word count is based on single characters (e.g. East Asian characters),
	 * enter 'characters_excluding_spaces' or 'characters_including_spaces'. Otherwise, enter 'words'.
	 * Do not translate into your own language.
	 */
	const wordCountType = _x( 'words', 'Word count type. Do not translate!' );

	/**
	 * The excerpt length setting needs to be applied to both
	 * the raw and the rendered excerpt depending on which is being used.
	 */
	const rawOrRenderedExcerpt = (
		rawExcerpt || strippedRenderedExcerpt
	).trim();

	let trimmedExcerpt = '';
	if ( wordCountType === 'words' ) {
		trimmedExcerpt = rawOrRenderedExcerpt
			.split( ' ', bioLength )
			.join( ' ' );
	} else if ( wordCountType === 'characters_excluding_spaces' ) {
		/*
		 * 1. Split the excerpt at the character limit,
		 * then join the substrings back into one string.
		 * 2. Count the number of spaces in the excerpt
		 * by comparing the lengths of the string with and without spaces.
		 * 3. Add the number to the length of the visible excerpt,
		 * so that the spaces are excluded from the word count.
		 */
		const excerptWithSpaces = rawOrRenderedExcerpt
			.split( '', bioLength )
			.join( '' );

		const numberOfSpaces =
			excerptWithSpaces.length -
			excerptWithSpaces.replaceAll( ' ', '' ).length;

		trimmedExcerpt = rawOrRenderedExcerpt
			.split( '', bioLength + numberOfSpaces )
			.join( '' );
	} else if ( wordCountType === 'characters_including_spaces' ) {
		trimmedExcerpt = rawOrRenderedExcerpt
			.split( '', bioLength )
			.join( '' );
	}

	const isTrimmed = trimmedExcerpt !== rawOrRenderedExcerpt
	const hasValue = !isEmpty(rawOrRenderedExcerpt)

	if(!postTypeSupportsExcerpts){
		return null
	}

	const blockClassName = getBlockDefaultClassName(props.name)
	const excerptClassName = clsx(`${ blockClassName }__excerpt`, {
		'is-inline': ! showMoreOnNewLine,
	})
	const readMoreClassName = `${ blockClassName }__more-text`

	const readMoreLink = (
		<RichText
			identifier="moreText"
			className={`${ blockClassName }__more-link`}
			tagName="a"
			aria-label={ __( '“Read more” link text' ) }
			placeholder={ __( 'Add "read more" link text' ) }
			value={ moreText }
			onChange={ ( newMoreText ) =>
				setAttributes( { moreText: newMoreText } )
			}
			withoutInteractiveFormatting
		/>
	);

    return (
		<>
			<InspectorControls>
			<ToolsPanel
					label={ __( 'Settings' ) }
					resetAll={ () => {
						setAttributes( {
							showMoreOnNewLine: true,
							bioLength: 55,
						} );
					} }
					dropdownMenuProps={ dropdownMenuProps }
				>
					<ToolsPanelItem
						hasValue={ () => showMoreOnNewLine !== true }
						label={ __( 'Show link on new line' ) }
						onDeselect={ () =>
							setAttributes( { showMoreOnNewLine: true } )
						}
						isShownByDefault
					>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Show link on new line' ) }
							checked={ showMoreOnNewLine }
							onChange={ ( newShowMoreOnNewLine ) =>
								setAttributes( {
									showMoreOnNewLine: newShowMoreOnNewLine,
								} )
							}
						/>
					</ToolsPanelItem>
					<ToolsPanelItem
						hasValue={ () => bioLength !== 55 }
						label={ __( 'Max number of words' ) }
						onDeselect={ () =>
							setAttributes( { bioLength: 55 } )
						}
						isShownByDefault
					>
						<RangeControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __( 'Max number of words' ) }
							value={ bioLength }
							onChange={ ( value ) => {
								setAttributes( { bioLength: value } );
							} }
							min="10"
							max="100"
						/>
					</ToolsPanelItem>
				</ToolsPanel>
			</InspectorControls>

			<div {...blockProps}>
				<p className={ excerptClassName }>
					{ ! isTrimmed
						? rawOrRenderedExcerpt || __( 'No Bio found' )
						: trimmedExcerpt + ELLIPSIS }
				</p>
				{ ! showMoreOnNewLine && ' ' }
				{ showMoreOnNewLine ? (
					<p className={readMoreClassName}>
						{ readMoreLink }
					</p>
				) : (
					readMoreLink
				) }
			</div>
		</>
	)
}



export {Edit}
export default Edit
