<?php

$profile_data  = $extra['profile_data'];
$profile_block = $extra['profile_block'];

if ( ! $profile_block->show( 'profile_link' ) ) {
	return;
}

$more_link = sprintf( '<a href="%s">%s %s</a>', $profile_data['link'], 'More About', $profile_data['name']['name'] );

echo wp_kses_post( $more_link );
