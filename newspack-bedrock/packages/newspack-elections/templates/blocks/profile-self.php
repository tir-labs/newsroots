<?php
/**
 * Govpack profile template.
 *
 * @package Govpack
 */

// $profile_data is defined elsewhere.
// phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable

$profile_block = $extra['profile_block'];
$profile_data  = $extra['profile_data'];
$block_class   = $attributes['className'];

$available_widths = gp_get_available_widths();



$container_classes = gp_classnames(
	'wp-block-govpack-profile__container wp-block-govpack-profile-self__container',
	[
		'wp-block-govpack-profile-self__container--right'  => ( isset( $attributes['avatarAlignment'] ) && ( 'right' === $attributes['avatarAlignment'] ) ),
		'wp-block-govpack-profile-self__container--left'   => ( isset( $attributes['avatarAlignment'] ) && ( 'left' === $attributes['avatarAlignment'] ) ),
		'wp-block-govpack-profile-self__container--align-right' => ( isset( $attributes['align'] ) && ( 'right' === $attributes['align'] ) ),
		'wp-block-govpack-profile-self__container--align-left' => ( isset( $attributes['align'] ) && ( 'left' === $attributes['align'] ) ),
		'wp-block-govpack-profile-self__container--align-center' => ( isset( $attributes['align'] ) && ( 'center' === $attributes['align'] ) ),
		'wp-block-govpack-profile-self__container--center' => ( 'is-styled-center' === $attributes['className'] ),
	]
);


?>

<aside 
<?php
echo get_block_wrapper_attributes(
	[
		'class' => gp_classnames(
			'wp-block-govpack-profile',
			[
				'wp-block-govpack-profile--show-labels' => $profile_block->show( 'labels' ),
			] 
		),
	]
);
?>
>
	<!-- start block__container -->
	<div class="<?php echo esc_attr( $container_classes ); ?>">	
		<?php gp_get_block_part( 'blocks/parts/profile', 'photo', $attributes, $content, $block, $extra ); ?>
		<div class="wp-block-govpack-profile__info">
			<?php gp_get_block_part( 'blocks/parts/profile', 'header', $attributes, $content, $block, $extra ); ?>
			<?php gp_get_block_part( 'blocks/parts/profile', 'rows', $attributes, $content, $block, $extra ); ?>
		</div>
	</div>
	<!-- end block__container -->
</aside>
