<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$profile_block = $extra['profile_block'];

foreach ( $profile_block->rows() as $index => $row ) {
	
	if ( ! $row['shouldShow'] ) {
		continue;
	}	
	
	ob_start();
	switch ( $row['key'] ) {
		case 'social':
			gp_get_block_part( 'blocks/parts/row', 'social', $attributes, $content, $block, $extra );
			break;
		case 'contact':
			gp_get_block_part( 'blocks/parts/row', 'contact', $attributes, $content, $block,  $extra);
			break;
		case 'comms_other':
			break;
		case 'links':
			gp_get_block_part( 'blocks/parts/row', 'links', $attributes, $content, $block, $extra );
			break;
		case 'more_about':
			gp_get_block_part( 'blocks/parts/row', 'more-link', $attributes, $content, $block, $extra );
			break;
		default:
			if ( isset( $row['value'] ) && $row['value'] ) {
				echo $row['value']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			break;
	}
	$row_content = ob_get_clean();
	
	if(empty($row_content)){
		continue;
	}

	?>
		<div <?php echo gp_line_attributes( $row, $attributes ); ?>>

			<?php if ( isset( $row['label'] ) && ( $row['label'] ) && ( $row["format"] !== "group" ) ) { 
					$label_classes = gp_classnames(
						'npe-profile-row__label',
						[
							'npe-profile-row__label--show' => $profile_block->show( 'labels' ),
							'npe-profile-row__label--hide' => ! $profile_block->show( 'labels' ),
						] );
			?>
				<div class="<?php echo esc_attr($label_classes);?>">
					<?php echo esc_html( $row['label'] ); ?>
						</div>
			<?php } ?>
			<div class="<?php echo esc_attr(gp_classnames("npe-profile-row__content",[
				'npe-profile-row__content--' . $row['key'] => true
			]));?>">
				<?php
					echo $row_content;
				?>
			</div>
		</div>
	<?php
}

