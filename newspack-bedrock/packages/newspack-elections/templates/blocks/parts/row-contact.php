<?php

$profile_data  = $extra['profile_data'];
$profile_block = $extra['profile_block'];

if ( ! $profile_block->show( 'contact' ) ) {
	return;
}

if ( empty( $profile_data['contact'] ) ) {
	return;
}

$groups = [
	"official" => "selectedCapitolCommunicationDetails",
	"district" => "selectedDistrictCommunicationDetails",
	"campaign" => "selectedCampaignCommunicationDetails",
	"other"    => "selectedOtherCommunicationDetails"
];



$services = [ 
	'email'   => 'showEmail',
	'phone'   => 'showPhone',
	'fax'     => 'showFax',
	'website' => 'showWebsite',
	'address' => 'showAddress',
];

$contact_info = [];

foreach($groups as $group_key => $group_view_attr){
	$contact_info[$group_key] = []; //$profile_block->attributes[$group_view_attr];
	$contact_info[$group_key]["label"]  = $profile_data['contact'][$group_key]["label"];
	$contact_info[$group_key]["services"] = [];

	if($group_key === "other"){
		foreach($profile_block->attributes[$group_view_attr] as $link_attribute => $show_other_link){
			if(!$show_other_link){
				continue;
			}
			$link = $profile_data['contact'][$group_key]["services"][$link_attribute];
			if($link["value"]){
				$contact_info[$group_key]["services"][$link_attribute] = $link;
			}
			
		}
		
		continue;
	}
	
	foreach($services as $service => $attr){
		if($profile_block->attributes[$group_view_attr][$attr] === true){
			$value = $profile_data['contact'][$group_key]["services"][$service];
			if($value){
				$contact_info[$group_key]["services"][$service] = $value;
			}
		}
	}
}


?>

<div class="wp-block-govpack-profile__group">
	<h4 class="wp-block-govpack-profile__heading wp-block-govpack-profile__heading--contact">
		Contact Information
	</h4>
	<div class="wp-block-govpack-profile__services wp-block-govpack-profile__group-items">
		<?php
		foreach ( $contact_info as $group_key => $group ) {
			
			if ( empty( $group['services'] ) ) {
				continue;
			}

			if( $group_key === "other"){
					?>
					<div class="wp-block-govpack-profile__comms-other">
						<div class="wp-block-govpack-profile__label npe-profile-sub-heading"><?php echo esc_html( $group['label'] ); ?>:</div>
						<dl class="wp-block-govpack-profile__comms-other key-pair-list">
							<?php foreach( $group['services'] as $service => $link) { ?>
								<dt class="key-pair-list__key" role="term"> <?php echo esc_html( $link['label'] ) ?></dt>
								<dd class="key-pair-list__value"><?php echo esc_html( $link['value'] ) ?> </dd>
							<?php } ?>
						</dl>
					</div>
					<?php
					continue;
				}

			?>
			<div class="gp-profile-contact">
				
					<div class="wp-block-govpack-profile__label npe-profile-sub-heading"><?php echo esc_html( $group['label'] ); ?>:</div>
					<div class="wp-block-govpack-profile__icon-set">
						<?php
						foreach ( $group['services'] as $service => $social_link ) {

							if ( ! $social_link ) {
								continue;
							}

							if($service === "address"){
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

							if ( is_email( $social_link ) ) {
								$url = 'mailto:' . antispambot( $social_link );
							} elseif ( ( 'phone' === $service ) || ( 'fax' === $service ) ) {
								$url = 'tel:' . $social_link;
							} else {
								$url = $social_link;
							}	

							if ( ! wp_parse_url( $url, PHP_URL_SCHEME ) && ! str_starts_with( $url, '//' ) && ! str_starts_with( $url, '#' ) ) {
								$url = 'https://' . $url;
							}
		
							?>
								
								<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $link_classes ); ?>">
									<span class="<?php echo esc_attr( $icon_classes ); ?>"><?php echo esc_svg( gp_get_icon( $service ) ); ?></span>
									<span class="gp-profile-contact__label"><?php echo esc_html( $service ); ?></span>
								</a>
							
							<?php
						}
						?>
					</div>
					<?php if(isset($group['services']['address'])){ ?>
						<address class="wp-block-govpack-profile__contact_address">
							<?php echo wp_kses_post($group['services']['address']); ?>
						</address>
					<?php } ?>
					</div>
		<?php } ?>
	</div>
</div>


