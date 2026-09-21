import {isEmpty} from "lodash"

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useBlockProps, AlignmentControl, BlockControls, InspectorControls, RichText} from "@wordpress/block-editor"
import { store as coreStore, useEntityProp } from '@wordpress/core-data'
import { useSelect } from '@wordpress/data';
import { ToggleControl, TextControl, PanelBody } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { PROFILE_POST_TYPE, useProfileFieldAttributes } from "@npe/editor";





function Edit( props ) {

	const { fieldKey, field, profile, profileId, ...restField } =  useProfileFieldAttributes(props) 
	
	const blockProps = useBlockProps()

	const { context, attributes, setAttributes } = props
	const { postType } = context
	const { textAlign, linkTarget, rel, linkText = "", prefixWithName, suffixWithName} = attributes

	const updateLinkText = ( newValue ) => {
		setAttributes({"linkText" : newValue})
	}

	
	const postTypeSupportsTitle = useSelect(
		( select ) => {
			return !! select( coreStore ).getPostType( postType )?.supports?.title;
		},
		[ postType ]
	);

	// get the title from 
	const [postTitle] = useEntityProp( 'postType', PROFILE_POST_TYPE, 'title', profileId );
	

	const profileName = (postTypeSupportsTitle ?
		postTitle :
		profile?.profile?.name
	) 

	const separator = " ";

    return (
		<>
			<BlockControls group="block">
				
				<AlignmentControl
					value={ textAlign }
					onChange={ ( nextAlign ) => {
						setAttributes( { textAlign: nextAlign } );
					} }
				/>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Settings' ) }>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Open in new tab' ) }
						onChange={ ( value ) =>
							setAttributes( {
								linkTarget: value
									? '_blank'
									: '_self',
							} )
						}
						checked={ linkTarget === '_blank' }
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Link rel' ) }
						value={ rel }
						onChange={ ( newRel ) =>
							setAttributes( { rel: newRel } )
						}
					/>

					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include Profile Name Before?' ) }
						onChange={ ( value ) =>
							setAttributes( { "prefixWithName": value } )
						}
						checked={ prefixWithName }
					/>

					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Include Profile Name After?' ) }
						onChange={ ( value ) =>
							setAttributes( { "suffixWithName": value } )
						}
						checked={ suffixWithName }
					/>

				</PanelBody>
			</InspectorControls>

			<a
				target={ linkTarget }
				rel={ rel }
				{...blockProps}
			>	
				{prefixWithName && profileName && (
					<>{profileName}{separator}</>
				)}
				<RichText
					identifier = "linkText"
					tagName="span" // The tag here is the element output and editable in the admin
					value={ linkText } // Any existing content, either from the database or an attribute default
					allowedFormats={ [ 'core/bold', 'core/italic' ] } // Allow the content to be made bold or italic, but do not allow other formatting options
					onChange={ ( newText ) => updateLinkText( newText ) } // Store updated content as a block attribute
					placeholder={ __( 'Read More' ) } // Display this text before any content has been added by the user
				/>
				{suffixWithName && profileName && (
					<>{separator}{profileName}</>
				)}
			</a>
			
		</>
	)
}



export {Edit}
export default Edit
