<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$profile_data  = $extra['profile_data'];
$profile_block = $extra['profile_block'];


if ( $profile_block->show( 'bio' ) ) {
	echo wp_kses_post( $profile_data['bio'] );
}
