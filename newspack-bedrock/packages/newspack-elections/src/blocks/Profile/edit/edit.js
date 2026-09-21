/**
 * External dependencies
 */
import clsx from 'clsx';

/**
 * WordPress dependencies
 */

import { __, _x, isRTL } from '@wordpress/i18n';
import { 
	InspectorControls, useBlockProps, useInnerBlocksProps, InnerBlocks, BlockControls, store as blockEditorStore,
	__experimentalUseBorderProps as useBorderProps,
	__experimentalUseColorProps as useColorProps,
	__experimentalGetSpacingClassesAndStyles as getSpacingClassesAndStyles,
	__experimentalGetShadowClassesAndStyles as getShadowClassesAndStyles,
	BlockVerticalAlignmentToolbar,
} from '@wordpress/block-editor';
import { useInstanceId } from '@wordpress/compose';
import { useRef, useEffect} from '@wordpress/element';
import { ToolbarGroup, Toolbar, Icon, ResizableBox, SelectControl } from '@wordpress/components';
import { useDispatch, useSelect} from "@wordpress/data";
import { external, edit } from '@wordpress/icons';

import { BlockContextProvider } from '@wordpress/block-editor';

import { ProfileResetPanel } from '../../../components/Panels/ProfileResetPanel.jsx';
import { Spinner } from './../../../components/Spinner.jsx';

import { DEFAULT_TEMPLATE, FULL_TEMPLATE } from './default-template.js';

import { useProfileAttributes } from "@npe/editor";
import { usePostEditURL } from '@npe/editor';

import {useState} from "@wordpress/element"


const ProfileBlockControls = ({ attributes, setAttributes, ...props}) => {
	const postEditURL = usePostEditURL(attributes.postId)

	return (
		<BlockControls>
			<ToolbarGroup>
				
				<Toolbar
                	controls={ [
                    	{
                        	icon: <Icon icon={ edit } />,
                        	title: __( 'Choose Another Profile', 'govpack' ),
                        	onClick: () => {
                            	props.setProfile( null );
                        	},
                    	},
						{
                        	icon: <Icon icon={ external } />,
                        	title: __( 'View Profile (Opens in new page)', 'govpack' ),
                        	href : postEditURL,
							target: "_blank"
                    	},
                	] }
            	/>
			</ToolbarGroup>
		</BlockControls>
	)
}

const useResizeProps = (props ) => {
	const { toggleSelection } = useDispatch( blockEditorStore );

	const {
		attributes, 
		setAttributes, 
		isSelected: isSingleSelected
	} = props
	
	const {
		align,
		customWidth
	} = attributes

	const isResizeEnabled = () => {
		return (align === "left" ) || ( align === "right" ) || ( align === "center" )
	}

	const resizeEnabled = isResizeEnabled()
	const numericWidth = customWidth ? parseInt( customWidth, 10 ) : "250";
	const currentWidth = resizeEnabled ? numericWidth : "100%"
	const enableHandles = resizeEnabled && isSingleSelected

	let showRightHandle = false;
	let showLeftHandle = false;

	const onResizeStart = () => {
		toggleSelection( false );
	}

	const onResizeStop = () => {
		toggleSelection( true );
	}

	if ( align === 'center' ) {
		// When the image is centered, show both handles.
		showRightHandle = true;
		showLeftHandle = true;
	} else if ( isRTL() ) {
		if ( align === 'left' ) {
			showRightHandle = true;
		} else {
			showLeftHandle = true;
		}
	} else {
		if ( align === 'right' ) {
			showLeftHandle = true;
		} else {
			showRightHandle = true;
		}
	}

	//const maxWidthBuffer = maxWidth * 2.5;
	//const maxResizeWidth = maxContentWidth || maxWidthBuffer;

	return [resizeEnabled, {
		size : {
			width: currentWidth ?? 'auto'
		},
		//maxWidth : maxResizeWidth,
		onResizeStart : onResizeStart,
		onResizeStop : ( event, direction, elt ) => {
			onResizeStop()
			setAttributes({"customWidth":`${ elt.offsetWidth }px`})
		},
		showHandle: enableHandles,
		enable : {
			top: false,
			right: showRightHandle,
			bottom: false,
			left: showLeftHandle,
		},
		resizeRatio : (align === 'center' ? 2 : 1 )
	}]
}

const ProfileWrapper = ({children, attributes, __unstableLayoutClassNames = ""}) => {

	const borderProps = useBorderProps( attributes );

	const { style  = {}, verticalAlignment } = attributes
	const { spacing = {} } = style
	const { padding : paddingAttributes = {} } = spacing

	const spacingStyles = getSpacingClassesAndStyles({style:{spacing:{padding:paddingAttributes}}})

	const colorProps = useColorProps(attributes)

	

	const wrapperProps = {
		className : clsx("profile-inner", borderProps.className,  colorProps.className, __unstableLayoutClassNames, {
			[ `is-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
		}),
		style: {
			...spacingStyles.style,
			...borderProps.style,
			...colorProps.style,
		}
	}
	return (<div {...wrapperProps}>
		{ children }
	</div>
	)
}


const BlockHTMLElementControl = (props) => {	

	const {
		tagName,
		onSelectTagName
	} = props

	const htmlElementMessages = {
		
		main: __(
			'The <main> element should be used for the primary content of your document only.'
		),
		section: __(
			"The <section> element should represent a standalone portion of the document that can't be better represented by another element."
		),
		article: __(
			'The <article> element should represent a self-contained, syndicatable portion of the document.'
		),
		aside: __(
			"The <aside> element should represent a portion of a document whose content is only indirectly related to the document's main content."
		)
		
	};

	return (
		<InspectorControls group="advanced">
			<SelectControl
				__nextHasNoMarginBottom
				__next40pxDefaultSize
				label={ __( 'HTML element' ) }
				options={ [
					{ label: __( 'Default (<div>)' ), value: 'div' },
					{ label: '<main>', value: 'main' },
					{ label: '<section>', value: 'section' },
					{ label: '<article>', value: 'article' },
					{ label: '<aside>', value: 'aside' },
				] }
				value={ tagName }
				onChange={ onSelectTagName }
				help={ htmlElementMessages[ tagName ] }
			/>
		</InspectorControls>
	)
}

function ProfileBlockEdit( props ) {
	const {attributes, setAttributes, clientId, context, isProfilePage} = props

	
	const [resizeEnabled, resizeProps] = useResizeProps(props);
	const {setProfile, resetProfile, profileId = null, profile, profileQuery} = useProfileAttributes(props)


	
	
	const showAppender = false
	const blockProps = useBlockProps();
	const {children, ...innerBlockProps} = useInnerBlocksProps(blockProps, {
		template : (isProfilePage ? FULL_TEMPLATE : DEFAULT_TEMPLATE),
		renderAppender : (showAppender) ? InnerBlocks.ButtonBlockAppender : false
	})

	

	const { __unstableMarkNextChangeAsNotPersistent, updateBlockAttributes } = useDispatch( blockEditorStore );
	

	
	const {
		tagName : TagName = 'div',
		verticalAlignment
	} = attributes;

	const setTagName = ( nextTagValue ) => {
		setAttributes( { tagName: nextTagValue } )
	}

	const showSpinner = profileQuery.isLoading
	const showProfile = profileQuery.hasLoaded === true

	const classes = clsx( innerBlockProps.className , {
		[ `is-vertically-aligned-${ verticalAlignment }` ]: verticalAlignment,
	} );

	const { columnsClientIds, columnClientIds } = useSelect(
		( select ) => {

			return {
				columnsClientIds : select( blockEditorStore ).getBlockParentsByBlockName( clientId , ["core/column"], true),
				columnClientIds: select( blockEditorStore ).getBlockParentsByBlockName( clientId , ["core/columns"], true)
			};
		},
		[ clientId ]
	);

	const columnsClientId = columnsClientIds[0] ?? null
	const columnClientId = columnClientIds[0] ?? null

	const updateAlignment = ( value ) => {
		// Update own alignment.
		setAttributes( { verticalAlignment: value } );

		// match value on parent Column block.
		updateBlockAttributes( columnClientId, {
			verticalAlignment: value,
		} );

		// Reset parent Columns block.
		updateBlockAttributes( columnsClientId, {
			verticalAlignment: null,
		} );
	};


	let WrappedProfile = (
		<ProfileWrapper {...props}>
			{ children }
		</ProfileWrapper>
	)

	return (
		<>
		
			{ showSpinner && (
				<Spinner />
			) }
			
			{ showProfile && (
				<TagName {...innerBlockProps} className = {classes}>
					
					<ProfileBlockControls 
						attributes = {attributes} 
						setAttributes = {setAttributes} 
						setProfile = {setProfile}
					/>
					
					<BlockControls>
						<BlockVerticalAlignmentToolbar
							onChange={ updateAlignment }
							value={ verticalAlignment }
							controls={ [ 'top', 'center', 'bottom', 'stretch' ] }
						/>
					</BlockControls>
					
					<BlockHTMLElementControl
						tagName = {TagName}
						onSelectTagName = {setTagName}
					/>

					{ resizeEnabled && (
						<ResizableBox {...resizeProps} >
							{ WrappedProfile }
						</ResizableBox>
					)}

					{ !resizeEnabled && (
						<>{ WrappedProfile }</>
					)}
				</TagName>
			)}
		</>	
	);
}

export default ProfileBlockEdit
export {ProfileBlockEdit}

