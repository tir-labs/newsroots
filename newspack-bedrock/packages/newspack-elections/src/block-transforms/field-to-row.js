import { createBlock } from '@wordpress/blocks';

export const FieldToRow = {
	type: 'block',
	blocks: [ 'npe/profile-row' ],
	transform: ( attributes  ) => {
		return createBlock( 'npe/profile-row', attributes );
	},
}