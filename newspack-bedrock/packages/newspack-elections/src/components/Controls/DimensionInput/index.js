import { useInstanceId } from '@wordpress/compose';
import { View } from '@wordpress/primitives';

import {
	__experimentalSpacingSizesControl as SpacingSizesControl,
	useSettings,
	isValueSpacingPreset
} from "@wordpress/block-editor"

import {
	__experimentalUseCustomUnits as useCustomUnits,
	__experimentalUnitControl as UnitControl,
	__experimentalParseQuantityAndUnitFromRawValue as parseQuantityAndUnitFromRawValue,
} from '@wordpress/components';

import useSpacingSizes from "./use-spacing-sizes"

export default function DimensionInput( { label, onChange, isResizing, value = '' } ) {
	const inputId = useInstanceId( UnitControl, 'block-profile-spacer-height-input' );
	const spacingSizes = useSpacingSizes();
	const [ spacingUnits ] = useSettings( 'spacing.units' );
	// In most contexts the spacer size cannot meaningfully be set to a
	// percentage, since this is relative to the parent container. This
	// unit is disabled from the UI.
	const availableUnits = spacingUnits
		? spacingUnits.filter( ( unit ) => unit !== 'px' )
		: [ 'px', 'em', 'rem', 'vw', 'vh' ];

	const units = useCustomUnits( {
		availableUnits,
		defaultValues: { px: 1, em: 1, rem: 1, vw: 1, vh: 1 },
	} );

	const handleOnChange = ( unprocessedValue ) => {
		onChange( unprocessedValue.all );
	};

	// Force the unit to update to `px` when the Spacer is being resized.
	const [ parsedQuantity, parsedUnit ] =
		parseQuantityAndUnitFromRawValue( value );
	const computedValue = isValueSpacingPreset( value )
		? value
		: [ parsedQuantity, isResizing ? 'px' : parsedUnit ].join( '' );

	return (
		<>
			{ ( ! spacingSizes || spacingSizes?.length === 0 ) && (
				<UnitControl
					id={ inputId }
					isResetValueOnUnitChange
					min={ 0 }
					max={ "100px" }
					onChange={ handleOnChange }
					value={ computedValue }
					units={ units }
					label={ label }
					__next40pxDefaultSize
				/>
			) }
			{ spacingSizes?.length > 0 && (
				<View className="tools-panel-item-spacing">
					<SpacingSizesControl
						
						values={ { all: computedValue } }
						onChange={ handleOnChange }
						label={ label }
						sides={ [ 'all' ] }
						units={ units }
						
						allowReset={ false }
						splitOnAxis={ false }
						showSideInLabel={ false }
					/>
				</View>
			) }
		</>
	);
}