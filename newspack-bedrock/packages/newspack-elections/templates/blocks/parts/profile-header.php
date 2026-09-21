<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$profile_data  = $extra['profile_data'];
$profile_block = $extra['profile_block'];


if ( $profile_block->show( 'name' ) || $profile_block->show( 'status_tag' ) ) { ?>
	<div class="wp-block-govpack-profile__line wp-block-govpack-profile--flex-left">
		<?php if ( $profile_block->show( 'name' ) ) { ?>
			<h3 class="wp-block-govpack-profile__name"> <?php echo esc_html( $profile_data['name']['full'] ); ?></h3>
		<?php } ?>
		<?php if ( $profile_block->show( 'status_tag' ) ) { ?>
			<div class="wp-block-govpack-profile__status-tag">
				<div class="govpack-termlist">
					<?php echo gp_get_the_status_terms_list( $profile_data['id'] ); ?>
				</div>
			</div>
		<?php } ?>
	</div>
	<?php
} 