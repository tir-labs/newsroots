


export const createRowToFieldTransform = ( RowName ) => {
	return {
		type: 'block',
		blocks: [ 'npe/profile-row' ],
		transform: ( attributes  ) => {
			return createBlock( RowName, attributes );
		},
	}
}