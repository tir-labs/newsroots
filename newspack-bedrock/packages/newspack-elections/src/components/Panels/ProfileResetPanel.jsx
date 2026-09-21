
import { __ } from '@wordpress/i18n';
import {Panel, PanelBody, PanelRow, 
	Button,
	__experimentalHeading as Heading,
	__experimentalText as Text,
	__experimentalHStack as HStack, 
	__experimentalVStack as VStack,  
	__experimentalSpacer as Spacer,
	__experimentalTruncate as Truncate
} from '@wordpress/components';
import { useMemo } from "@wordpress/element"

import { useProfile } from '@npe/editor';

import { Spinner } from '../Spinner';
import { useMedia } from '../useMedia';

export const ProfileResetPanel = ({profileId, ...props}) => {

	const {profile, isLoading, ...query} = useProfile(profileId)
	const {media = {}} = useMedia(profile?.featured_media)
	const hasThumbnail = (media?.media_details?.sizes?.thumbnail?.source_url ?? false)
	

	const strippedRenderedExcerpt = useMemo( () => {
		if ( ! profile?.excerpt?.rendered ) {
			return '';
		}
		const document = new window.DOMParser().parseFromString(
			profile?.excerpt?.rendered,
			'text/html'
		);
		return document.body.textContent || document.body.innerText || '';
	}, [ profile?.excerpt?.rendered ] );

	const MiniProfile = () => {
	return (
		<VStack>
			<HStack className="mini-profile" justify="flex-start" align="top">
				{hasThumbnail && (
					<figure className='mini-profile__image' style={{
						minWidth : "60px",
						height : "60px",
						marginBottom: "0"
					}}>
						<img src={media.media_details.sizes.thumbnail.source_url} style={{
							width : "60px",
							height : "60px",
							display: "block"
						}}/>
					</figure>
				)}
				<div className='mini-profile__name'>
					<Heading level="3">{profile.meta.name}</Heading>
					<Text truncate numberOfLines="4">{strippedRenderedExcerpt}</Text>
				</div>
			</HStack>

			<div>
				<Button 
					variant="link"
					onClick = {props.setProfile}
				>
					Choose a different profile
				</Button>
			</div>
		</VStack>
	)}
	
	let Component
	if(isLoading){
		Component = () => (<Spinner />)
	} else {
		Component = () => (<MiniProfile />)
	}

	return (
		<Panel>
			<PanelBody title={ __( 'Currently Selected Profile', 'govpack' ) }>
				<Component />
			</PanelBody>
		</Panel>
	)
}