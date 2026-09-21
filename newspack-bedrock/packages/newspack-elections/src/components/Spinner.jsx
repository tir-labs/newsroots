import { __ } from '@wordpress/i18n';
import { Spinner as Rotator } from '@wordpress/components';

export const Spinner = () => (
	<div className="is-loading">
		{ __( 'Fetching profile info…', 'govpack' ) }
		<Rotator />
	</div>
)