import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { decodeEntities } from '@wordpress/html-entities';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Icon, commentAuthorAvatar, } from '@wordpress/icons';
import { useEntityRecord, store as coreData } from "@wordpress/core-data"
import { Placeholder } from '@wordpress/components';

import AutocompleteWithSuggestions from './AutocompleteWithSuggestions.jsx'
import { Spinner } from './Spinner.jsx'

export const ProfileSelector = ( {
	setProfile,
	...props
} ) => {

	
	const [ isLoading, setIsLoading ] = useState( false );
	const [ maxItemsToSuggest, setMaxItemsToSuggest ] = useState( 10 )

	return (
		<Placeholder
            icon={ <Icon icon={ commentAuthorAvatar } /> }
            label={ __( 'Profile', 'govpack-blocks' ) }
        >
            { isLoading && (
				<Spinner />
			) }

            { ! isLoading && (
                <AutocompleteWithSuggestions
                    label={ __( 'Search for a profile to display', 'govpack' ) }
                    help={ __(
                        'Begin typing name, click autocomplete result to select.',
                        'govpack'
                    ) }

                    fetchSuggestions={ async ( search = null, offset = 0 ) => {

						const response = await apiFetch( {
							parse: false,
							path: addQueryArgs( '/wp/v2/govpack_profiles', {
								search,
								offset,
								fields: 'id,name',
							} ),
						} ).catch( (error) => {
							return error
						});

                        const total = parseInt( response.headers.get( 'x-wp-total' ) || 0 );
                        const profiles = await response.json();

						// Set max items for "load more" functionality in suggestions list.
						if ( ! maxItemsToSuggest && ! search ) {
							setMaxItemsToSuggest( total );
						}

        
						return profiles.map( _profile => {

							let label = decodeEntities( _profile.title.rendered ) ?? null
							if(label){
								label = label + " (ID: " + _profile.id + ")";
							}

							return {
								value: _profile.id,
								label: label || __( '(no name)', 'govpack' ),
							} 
						} );
                    } }
					maxItemsToSuggest={ maxItemsToSuggest }
					onChange={ (items) => {
						
						let profileId = parseInt( items[ 0 ].value );
						setProfile(profileId)
					}}
					postTypeLabel={ __( 'profile', 'govpack-blocks' ) }
					postTypeLabelPlural={ __( 'profiles', 'govpack-blocks' ) }
					selectedItems={ [] }
				/>
			)}
		</Placeholder>
	)
}