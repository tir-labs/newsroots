<?php

$profile_data  = $extra['profile_data'];
$profile_block = $extra['profile_block'];

if ( ! $profile_block->show( 'social' ) ) {
	return;
}

if ( empty( $profile_data['social'] ) ) {
	return;
}


?>

<div class="wp-block-govpack-profile__social wp-block-govpack-profile__group">
	<h4 class="wp-block-govpack-profile__heading wp-block-govpack-profile__heading--social">
		Social Media
	</h4>
	<div class="wp-block-govpack-profile__services wp-block-govpack-profile__group-items">
		<?php
		foreach ( $profile_data['social'] as $group_key => $group ) {

			if ( empty( $group['services'] ) ) {
				continue;
			}

			?>
			<div class="gp-profile-contact">
				<div class="wp-block-govpack-profile__label npe-profile-sub-heading	"><?php echo esc_html( $group['label'] ); ?>:</div>
				<div class="wp-block-govpack-profile__icon-set">
					<?php
					foreach ( $group['services'] as $service => $social_link ) {

						if ( ! $social_link ) {
							continue;
						}

						$link_classes = gp_classnames(
							'gp-profile-contact__link', 
							[ 
								'gp-profile-contact__link--hide-label',
								"gp-profile-contact__link--{$service}",
							]
						);
						

						$icon_classes = gp_classnames(
							'wp-block-govpack-profile__icon',
							[
								"wp-block-govpack-profile__icon--{$service}",
							]
						);
	
						?>
							
							<a href="<?php echo esc_url( $social_link ); ?>" class="<?php echo esc_attr( $link_classes ); ?>">
								<span class="<?php echo esc_attr( $icon_classes ); ?>"><?php echo esc_svg( gp_get_icon( $service ) ); ?></span>
								<span class="gp-profile-contact__label"><?php echo esc_html( $service ); ?></span>
							</a>
							
						<?php
					}
					?>
				</div>
			</div>
		<?php } ?>
	</div>
</div>


