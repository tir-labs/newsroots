/**
 * WordPress dependencies
 */
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

const EMPTY_ARRAY = [];

export default function useProfileTerms( profileId, taxonomy = {}) {

	const {
		slug = false
	} = taxonomy

	return useSelect(
		( select ) => {
			const visible = taxonomy?.visibility?.publicly_queryable;
			if ( ! visible ) {
				return {
					postTerms: EMPTY_ARRAY,
					isLoading: false,
					hasProfileTerms: false,
				};
			}

			

			const { getEntityRecords, isResolving } = select( coreStore );
			const taxonomyArgs = [
				'taxonomy',
				slug,
				{
					post: profileId,
					per_page: -1,
					context: 'view',
				},
			];
			const terms = getEntityRecords( ...taxonomyArgs );

			return {
				profileTerms: terms,
				isLoading: isResolving( 'getEntityRecords', taxonomyArgs ),
				hasProfileTerms: !! terms?.length,
			};
		},
		[ profileId, taxonomy?.visibility?.publicly_queryable, slug ]
	);
}