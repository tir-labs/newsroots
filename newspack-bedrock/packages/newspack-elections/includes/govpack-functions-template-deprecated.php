<?php

/**
 * Utility Function that Outputs a row
 * 
 * @param string  $id The html ID to output.
 * @param string  $value The value to output.
 * @param boolean $display Override to control if this row will output.
 */
if ( ! function_exists( 'gp_row' ) ) {
	function gp_row( $id, $value, $display ) {

		gp_deprecated( 'gp_row', '1.1' );

		if ( ! $display ) {
			return null;
		}

		if ( ! $value ) {
			return null;
		}

		// No escaping here. $value here needs to handle HTML beyond what wp_kses can realistically handle. Escaping should be done before passing to this function.
		echo '<div id="govpack-profile-block-' . $id . '" class="wp-block-govpack-profile__line wp-block-govpack-profile__line--' . $id . '">' . $value . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}


if ( ! function_exists( 'gp_get_profile_lines' ) ) {
	function gp_get_profile_lines( $attributes, $profile_data ) {

		gp_deprecated( 'gp_row', '1.1' );

		$show  = gp_get_show_data( $profile_data, $attributes );
		$lines = [ 
		
			[
				'key'        => 'age',
				'value'      => esc_html( $profile_data['age'] ),
				'label'      => 'Age',
				'shouldShow' => $attributes['showAge'],
			],
			[
				'key'        => 'leg_body',
				'value'      => esc_html( $profile_data['legislative_body'] ),
				'label'      => 'Legislative Body',
				'shouldShow' => $attributes['showLegislativeBody'],
			],
			[
				'key'        => 'position',
				'value'      => esc_html( $profile_data['position'] ),
				'label'      => 'Position',
				'shouldShow' => $attributes['showPosition'],
			],
			[
				'key'        => 'party',
				'value'      => esc_html( $profile_data['party'] ),
				'label'      => 'Party',
				'shouldShow' => $attributes['showParty'],
			],
			[
				'key'        => 'district',
				'value'      => esc_html( $profile_data['district'] ),
				'label'      => 'District',
				'shouldShow' => $attributes['showDistrict'],
			],
			[
				'key'        => 'state',
				'value'      => esc_html( $profile_data['state'] ),
				'label'      => 'State',
				'shouldShow' => $attributes['showState'],
			],
			[
				'key'        => 'status',
				'value'      => esc_html( $profile_data['status'] ),
				'label'      => 'Status',
				'shouldShow' => $attributes['showDistrict'],
			],
			[
				'key'        => 'social',
				'value'      => gp_social_media( $profile_data, $attributes ),
				'label'      => 'Social Media',
				'shouldShow' => $show['social'],
			],
			[
				'key'        => 'comms_capitol',
				'value'      => gp_contact_info( 'Official', $profile_data['comms']['capitol'], $attributes['selectedCapitolCommunicationDetails'] ),
				'label'      => 'Contact Info (Official)',
				'shouldShow' => $attributes['showCapitolCommunicationDetails'],
			],
			[
				'key'        => 'comms_district',
				'value'      => gp_contact_info( 'District', $profile_data['comms']['district'], $attributes['selectedDistrictCommunicationDetails'] ),
				'label'      => 'Contact Info (District)',
				'shouldShow' => $attributes['showDistrictCommunicationDetails'],
			],
			[
				'key'        => 'comms_campaign',
				'value'      => gp_contact_info( 'Campaign', $profile_data['comms']['campaign'], $attributes['selectedCampaignCommunicationDetails'] ),
				'label'      => 'Contact Info (Campaign)',
				'shouldShow' => $attributes['showCampaignCommunicationDetails'],
			],
			[
				'key'        => 'comms_other',
				'value'      => gp_contact_other( 'Other', $profile_data['comms']['other'], $attributes['selectedOtherCommunicationDetails'] ),
				'label'      => 'Contact Info (Campaign)',
				'shouldShow' => $attributes['showOtherCommunicationDetails'],
			],
			[
				'key'        => 'more_about',
				'value'      => gp_maybe_link( sprintf( 'More About %s', $profile_data['name']['name'] ), $profile_data['link'], isset( $attributes['showProfileLink'] ) && $attributes['showProfileLink'] ),
				'shouldShow' => ( isset( $attributes['showProfileLink'] ) && $attributes['showProfileLink'] ),
			],
			[
				'key'        => 'links',
				'value'      => gp_the_profile_links( $profile_data, $attributes ),
				'shouldShow' => shouldShowLinks( $profile_data, $attributes ),
			],
		];

		return $lines;
	}
}


if ( ! function_exists( 'shouldShowLinks' ) ) {
	function shouldShowLinks( $profile_data, $attributes ) {

		gp_deprecated( 'shouldShowLinks', '1.1' );

		if ( isset( $attributes['showOtherLinks'] ) ) {
			return $attributes['showOtherLinks'];
		}

		if ( ! isset( $profile_data['links'] ) || empty( $profile_data['links'] ) ) {
			return false;
		}

		if (
			( ! isset( $attributes['selectedLinks'] ) ) ||
			( empty( $profile_data['selectedLinks'] ) )
		) {
			return true;
		}

		return false;
	}
}

/**
 * Utility Function that Outputs a link to a profile
 * 
 * @param string  $url The url to link to.
 * @param boolean $title Name of the profile to link to eg More About {$title}.
 */
if ( ! function_exists( 'gp_link' ) ) {
	function gp_link( $url, $title ) {
		gp_deprecated( 'gp_link', 1.1 );
		return '<a href=' . esc_url( $url ) . '>More About ' . esc_html( $title ) . '</a>';
	}
}


/**
 * Utility Function that Outputs a Profiles Social and Email.
 * 
 * @param array $profile_data Data about the profile.
 * @param array $attributes Attributes from the Block.
 */
if ( ! function_exists( 'gp_contacts' ) ) {
	function gp_contacts( $profile_data, $attributes ) {
		gp_deprecated( 'gp_contacts', 1.1 );
		$icons = gp_get_icons();
	
		$icon = '<span class="wp-block-govpack-profile__contact__icon wp-block-govpack-profile__contact__icon--{%s}">%s</span>';

		if ( $attributes['showEmail'] && $profile_data['email'] ) {
			$email_icon = sprintf( $icon, 'email', gp_get_icon( 'email' ) );
			$classes    = [
				'wp-block-govpack-profile__contact--hide-label',
			];

			$classes = join( ' ', $classes );

			$email = "<li class=\"wp-block-govpack-profile__contact {$classes}\">
				<a href=\"mailto:{$profile_data['email']}\" class=\"wp-block-govpack-profile__contact__link\">
					{$email_icon}
					<span class=\"wp-block-govpack-profile__contact__label\">Email</span>
				</a>
			</li>";

		}
	

		$social = '';
		if ( $attributes['showSocial'] ) {

			$services = [ 'facebook', 'x', 'linkedin', 'instagram' ];


			foreach ( $services as $service ) {
				if ( ! isset( $profile_data[ $service ] ) || ! $profile_data[ $service ] ) {
					continue;
				}

				$classes = [
					'wp-block-govpack-profile__contact',
					'wp-block-govpack-profile__contact--hide-label',
					"wp-block-govpack-profile__contact--{$service}",
				];
		
				$classes = join( ' ', $classes );


				$contact_icon = sprintf( $icon, $service, gp_get_icons( $service ) );
				$social      .=  
				"<li class=\"{$classes} \">
					<a href=\"{$profile_data[$service]}\" class=\"wp-block-govpack-profile__contact__link\">
						{$contact_icon}
						<span class=\"wp-block-govpack-profile__contact__label\">{$service}</span>
					</a>
				</li>";

			}
		}


		return sprintf(
			'<div class="wp-block-govpack-profile__contacts">
					<ul>
						%s
						%s
					</ul>
				</div>',
			$email ?? '',
			$social ?? '',
		);
	}
}

/**
 * Utility Function that Outputs a Profiles's Social Media Sections
 * 
 * @param array $profile_data Data about the profile.
 * @param array $attributes Attributes from the Block.
 */
if ( ! function_exists( 'gp_social_media' ) ) {
	function gp_social_media( $profile_data, $attributes ) {
		gp_deprecated( 'gp_social_media', 1.1 );
		$template = '<div class="wp-block-govpack-profile__social">
			<ul class="wp-block-govpack-profile__services govpack-vertical-list">
			%s
			</ul>
			</div>';

		$content = '';

		if ( $attributes['selectedSocial']['showOfficial'] && isset( $profile_data['social']['official'] ) ) {
			$content .= gp_social_media_row( 'Official', $profile_data['social']['official'] );
		}

		if ( $attributes['selectedSocial']['showCampaign'] && isset( $profile_data['social']['campaign'] ) ) {
			$content .= gp_social_media_row( 'Campaign', $profile_data['social']['campaign'] );
		}

		if ( $attributes['selectedSocial']['showPersonal'] && isset( $profile_data['social']['personal'] ) ) {
			$content .= gp_social_media_row( 'Personal', $profile_data['social']['personal'] );
		}

		return sprintf( $template, $content ); 
	}
}

/**
 * Utility Function that Outputs a Profiles's Social Media Row
 * 
 * @param string $label Row label to shoe.
 * @param array  $links Links for social media profiles.
 */
if ( ! function_exists( 'gp_social_media_row' ) ) {
	function gp_social_media_row( $label, $links = [] ) {
		gp_deprecated( 'gp_social_media_row', 1.1 );

		$outer_template = 
			'<li class="wp-block-govpack-profile__social_group">
				<div class="wp-block-govpack-profile__label">%s: </div>
				<ul class="govpack-inline-list">
					%s
				</ul>
			</li>';

		$content = '';

		$services = [ 'facebook', 'x', 'linkedin', 'instagram', 'youtube' ];


		foreach ( $services as $service ) {
			if ( ! isset( $links[ $service ] ) || ! $links[ $service ] ) {
				continue;
			}

			$classes = [
				'wp-block-govpack-profile__contact',
				'wp-block-govpack-profile__contact--hide-label',
				"wp-block-govpack-profile__contact--{$service}",
			];

			$classes = join( ' ', $classes );

			$icon         = '<span class="wp-block-govpack-profile__contact__icon wp-block-govpack-profile__contact__icon--{%s}">%s</span>';
			$contact_icon = sprintf( $icon, $service, gp_get_icon( $service ) );

			$content .=  
			"<li class=\"{$classes} \">
				<a href=\"{$links[$service]}\" class=\"wp-block-govpack-profile__contact__link\">
					{$contact_icon}
					<span class=\"wp-block-govpack-profile__contact__label\">{$service}</span>
				</a>
			</li>";

		}

		return sprintf( $outer_template, $label, $content ); 
	}
}


if ( ! function_exists( 'gp_the_profile_links' ) ) {
	function gp_the_profile_links( $profile_data, $attributes ) {
		gp_deprecated( 'gp_the_profile_links', '1.1' );
		$links = gp_get_profile_links( $profile_data, $attributes );
		foreach ( $links as $key => &$link ) {
			$link['show'] = gp_should_show_link( $key, $attributes );
		}   

		$links = array_filter(
			$links,
			function ( $link, $key ) {
				return $link['show'];
			},
			ARRAY_FILTER_USE_BOTH 
		);

		if ( count( $links ) <= 0 ) {
			return '';
		}


		ob_start();
		?>
		<ul class="govpack-vertical-list">
			<?php foreach ( $links as &$link ) { ?>
				<li><?php echo $link['src']; ?></li>
			<?php } ?>
		</ul>
		<?php
		return ob_get_clean();
	}
}

if ( ! function_exists( 'gp_get_profile_links' ) ) {
	function gp_get_profile_links( $profile_data, $attributes ) {
		
		gp_deprecated( 'gp_get_profile_links', 1.1 );

		if ( ! isset( $profile_data['links'] ) ) {
			return [];
		}

		if ( empty( $profile_data['links'] ) ) {
			return [];
		}

		
		$links = apply_filters( 'govpack_profile_links', $profile_data['links'] ?? [], $profile_data['id'], $profile_data );
		foreach ( $links as &$link ) {

			$link = apply_filters( 'govpack_profile_link', $link, $profile_data['id'], $profile_data );

			$link_attrs = array_filter(
				$link,
				function ( $value, $key ) {
					if ( ( $value === null ) || ( $value === '' ) ) {
						return false;
					}

					if ( ( $key === 'text' ) || ( $key === 'meta' ) ) {
						return false;
					}
				
					if ( is_array( $value ) && ( empty( $value ) ) ) {
						return false;
					}

					return true;
				},
				ARRAY_FILTER_USE_BOTH
			);

			$link['src'] = sprintf( '<a %s>%s</a>', gp_normalise_html_element_args( $link_attrs ), $link['text'] );
			
		}

		return $links;
	}
}


/**
 * Utility Function that Outputs a links to the profile's websites.
 * 
 * Currently only supports the campaign & legislative websites
 * 
 * @param array $websites Data about websites from the profile.
 */
if ( ! function_exists( 'gp_websites' ) ) {
	function gp_websites( $websites ) {

		$campaign    = '';
		$legislative = '';
		$li          = '<li><a href="%s">%s</a></li>';

		if ( $websites['campaign'] ) {
			$campaign = sprintf( $li, esc_url( $websites['campaign'] ), 'Campaign Website' );
		}

		if ( $websites['legislative'] ) {
			$legislative = sprintf( $li, esc_url( $websites['legislative'] ), 'Legislative Website' );
		}

		return sprintf(
			'<div class="wp-block-govpack-profile__contacts">
					<ul>
						%s
						%s
					</ul>
				</div>',
			$campaign ?? '',
			$legislative ?? '',
		);
	}
}