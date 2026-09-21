
/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { memo, useEffect, useState } from "@wordpress/element"
import {useBlockEditContext} from "@wordpress/block-editor"

/**
 * External dependencies
 */
import clsx from 'clsx';

function BlockProps( {
	index,
	useBlockProps: hook,
	setAllWrapperProps,
	...props
} ) {

	const wrapperProps = hook( props );
	
	const setWrapperProps = ( next ) =>
		setAllWrapperProps( ( prev ) => {
			const nextAll = [ ...prev ];
			nextAll[ index ] = next;
			return nextAll;
		} );
	// Setting state after every render is fine because this component is
	// pure and will only re-render when needed props change.
	useEffect( () => {
		// We could shallow compare the props, but since this component only
		// changes when needed attributes change, the benefit is probably small.
		setWrapperProps( wrapperProps );
		return () => {
			setWrapperProps( undefined );
		};
	} );
	return null;
}

const BlockPropsPure = memo( BlockProps );	

export const createGPBlockListBlockFilter = (features = []) => {

	const withGPBlockListBlockSupportsHooks = createHigherOrderComponent(
		(OriginalBlockListBlock) => ( props ) => {
			
			const [ allWrapperProps, setAllWrapperProps ] = useState(
				Array( features.length ).fill( undefined )
			);

			

			return [
				...features.map( ( feature, i ) => {

					const {
						hasSupport,
						attributeKeys = [],
						useBlockProps,
						isMatch,
					} = feature;

					const neededProps = {};

					for ( const key of attributeKeys ) {
						if ( props.attributes[ key ] ) {
							neededProps[ key ] = props.attributes[ key ];
						}
					}

					if (
						// Skip rendering if none of the needed attributes are
						// set.
						! Object.keys( neededProps ).length ||
						! hasSupport( props.name ) ||
						( isMatch && ! isMatch( neededProps ) )
					) {
						return null;
					}

				
					return (
						<BlockPropsPure
							// We can use the index because the array length
							// is fixed per page load right now.
							key={ i }
							index={ i }
							useBlockProps={ useBlockProps }
							// This component is pure, so we must pass a stable
							// function reference.
							setAllWrapperProps={ setAllWrapperProps }
							name={ props.name }
							//clientId={ props.clientId }
							// This component is pure, so only pass needed
							// props!!!
							{ ...neededProps }
						/>
					);

				}),
				<OriginalBlockListBlock
					key="edit"
					{ ...props }
					wrapperProps={ 
						allWrapperProps.filter( Boolean )
							.reduce( ( acc, wrapperProps ) => {
								return {
									...acc,
									...wrapperProps,
									className: clsx(
										acc.className,
										wrapperProps.className
									),
									style: {
										...acc.style,
										...wrapperProps.style,
									},
								};
							}, 
							props.wrapperProps || {} 
						) 
					}
				/>
			]
		},
		'withGPBlockListBlockSupportsHooks'
	) 

	
	addFilter(
		'editor.BlockListBlock',
		'gp/block-supports/blockListBlocks',
		withGPBlockListBlockSupportsHooks
	);

}


export const createGPBlockRegisterFilter = (features = []) => {

	const withGPBlockListBlockRegisterHooks = (settings, name) => {

		/**
		 * For a list of given block support features, filter down to features that can add attributes to to this block 
		 * (is support for the current block provided and does the feature have an addAttribute method). Use the passed 
		 * in "setting" run each addAttribute passing and recieving the settings object
		 */
		features
			.filter(feature => Object.hasOwn(feature, "addAttribute") && feature.hasSupport(name))
			.reduce( (settings, feature) => feature.addAttribute(settings), settings)

		return settings
	}

	addFilter(
		'blocks.registerBlockType',
		'gp/block-supports/addAttributes',
		withGPBlockListBlockRegisterHooks
	);

}


export function createGPBlockEditFilter( features ) {
	// We don't want block controls to re-render when typing inside a block.
	// `memo` will prevent re-renders unless props change, so only pass the
	// needed props and not the whole attributes object.
	// Make sure the feature has an Edit method as well
	features = features.filter( feature => Object.hasOwn(feature, "edit") && feature.edit ).map( ( settings ) => {
		return { ...settings, Edit: memo( settings.edit ) };
	} );

	const withGPBlockEditHooks = createHigherOrderComponent(
		( OriginalBlockEdit ) => ( props ) => {

			
			const context = useBlockEditContext();
			
			const injectProps = {}
			// CAUTION: code added before this line will be executed for all
			// blocks, not just those that support the feature! Code added
			// above this line should be carefully evaluated for its impact on
			// performance.
			return [
				...features.map( ( feature, i ) => {
					const {
						Edit,
						hasSupport,
						attributeKeys = [],
						shareWithChildBlocks,
					} = feature;

					
					//const shouldDisplayControls = shareWithChildBlocks
					//	context[ mayDisplayControlsKey ] ||
					//	( context[ mayDisplayParentControlsKey ] &&
					//		shareWithChildBlocks );
					

					if (
						! hasSupport( props.name )
					) {
						return null;
					}

					const neededProps = {};
					for ( const key of attributeKeys ) {
						if ( props.attributes[ key ] ) {
							neededProps[ key ] = props.attributes[ key ];
						}
					}

					injectProps["injectedProp"] = "foobar"

					return (
						<Edit
							// We can use the index because the array length
							// is fixed per page load right now.
							key={ i }
							name={ props.name }
							isSelected={ props.isSelected }
							clientId={ props.clientId }
							setAttributes={ props.setAttributes }
							__unstableParentLayout={
								props.__unstableParentLayout
							}
							context = { props.context }
							// This component is pure, so only pass needed
							// props!!!
							attributes = { neededProps }
							
						/>
					);
				} ),
				<OriginalBlockEdit key="edit" { ...props } {...injectProps } />,
			];
		},
		'withGPBlockEditHooks'
	);
	addFilter( 
		'editor.BlockEdit', 
		'gp/block-supports/blockEdit',
		withGPBlockEditHooks 
	);
}