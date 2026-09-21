<?php

$profile_data  = $extra['profile_data'];
$profile_block = $extra['profile_block'];

if ( ! $profile_block->show( 'links' ) ) {
	return;
}

$links = $profile_block->get_profile_links();
if ( count( $links ) <= 0 ) {
	return;
}

foreach ( $links as $key => &$profile_link ) {
	$profile_link['show'] = gp_should_show_link( $key, $attributes );
}   

$links = array_filter(
	$links,
	function ( $profile_link ) {
		return $profile_link['show'];
	},
	ARRAY_FILTER_USE_BOTH 
);

if ( count( $links ) <= 0 ) {
	return;
}

?>
	<div class="wp-block-govpack-profile__comms">
		
			<ul class="wp-block-govpack-profile__comms-icons wp-block-govpack-profile__icon-set">
			<?php
			foreach ( $links as &$profile_link ) {
					
				if ( ! gp_icon_exists( $profile_link['slug'] ) ) {
					continue;
				}
					
				$row_classes = gp_classnames(
					'wp-block-govpack-profile__contact',
					[
						'wp-block-govpack-profile__contact--hide-label',
						"wp-block-govpack-profile__contact--{$profile_link['slug']}",
					]
				);
				

				?>
					<li class="<?php echo esc_attr( $row_classes ); ?>">
						<a href="<?php echo esc_url( $profile_link['href'] ); ?>" title="Link to <?php echo esc_attr( $profile_link['text'] ); ?>">
							<span class="wp-block-govpack-profile__icon wp-block-govpack-profile__icon--<?php echo esc_attr( $profile_link['slug'] ); ?>">
							<?php echo esc_svg( gp_get_icon( $profile_link['slug'] ) ); ?>
							</span>
							<span class="wp-block-govpack-profile__contact__label">
							<?php echo esc_html( $profile_link['text'] ); ?>
							</span>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
<?php

