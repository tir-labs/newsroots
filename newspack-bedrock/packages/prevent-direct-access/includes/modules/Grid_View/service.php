<?php

namespace PDAFree\modules\Grid_View;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
class Service {
	private $repository;
	private $handler;
	private $dir_url;
	private $pda_admin;

	public function __construct( $pda_admin ) {
		$this->repository = new \PDA_Repository();
		$this->handler    = new \Pda_Free_Handle();
		$this->dir_url    = plugin_dir_url( __FILE__ );
		$this->pda_admin  = $pda_admin;
	}

	public function maybe_add_protection_border_class( $response, $attachment ) {
		if ( $this->repository->is_protected_file( $attachment->ID ) ) {
			$response['customClass'] = 'pda-protected-grid-view';
		} else {
			$response['customClass'] = '';
		}

		return $response;
	}

	private function load_media_js( $post_id, $exists ) {
		ob_start();
		$meta_value = $this->repository->get_post_meta_by_post_id( $post_id )->meta_value;
		$upload_dir = wp_upload_dir();
		$fileUrl    = path_join( $upload_dir['basedir'], $meta_value );
		?>
		<script>
		  (function ($) {
			$(document).ready(function ($) {
			  var exists = <?php echo esc_attr( $exists ); ?>;
			  pda_media.handleAfterUpdatedMeta();
			  var post_id = <?php echo esc_attr( $post_id ); ?>;
			  var $checkBoxProtection = $('#pda_' + post_id + '_protection');
			  var $label = $('#pda_' + post_id + '_label');
			  var url = "<?php echo esc_attr( $fileUrl ); ?>"
			  $checkBoxProtection.change(function () {
				if (!exists) {
				  pda_media.handleFileExistError(url);
				} else {
				  if ($checkBoxProtection.prop('checked')) {
				  	window.pdaLiteProtectProcessing = true;
					$label.text('Protecting...');
				  } else {
				  	window.pdaLiteProtectProcessing = true;
					$label.text('Unprotecting...');
				  }
				}
			  });
			});
		  })(jQuery);
		</script>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render checkbox UI and FAP select.
	 *
	 * @param integer $post_id
	 * @param boolean $is_protected
	 * @param false   $limited
	 *
	 * @return false|string
	 */
	private function render_ui( $post_id, $is_protected, $limited = false ) {
		ob_start();
		$id       = "pda_{$post_id}_protection";
		$fap_id   = "pda_{$post_id}_fap";
		$label_id = "pda_{$post_id}_label";

		?>
		<div class="pda_wrap_protection_setting">
			<input type="hidden"
			       value="pda_protection_setting_hidden"
			       name="attachments[<?php echo esc_attr( $post_id ); ?>][pda_protection_setting_hidden]"
			/>
			<input class="pda_protection_setting"
			       type="checkbox"
			       name="attachments[<?php echo esc_attr( $post_id ); ?>][pda_protection_setting]"
			       id="<?php echo esc_attr( $id ); ?>"
				 <?php checked( $is_protected ); ?>
			/>
			<label id="<?php echo esc_attr( $label_id ); ?>"
				   for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html__( 'Protect this file', 'prevent-direct-access' ); ?>
			</label> 
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * @param array  $form_fields
	 * @param Object $post
	 *
	 * @return mixed
	 */
	public function maybe_add_checkbox_protection( $form_fields, $post ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $form_fields;
		}
		$screen    = get_current_screen();
		$screen_id = isset( $screen->id ) ? $screen->id : false;

		if ( $screen_id && 'attachment' === $screen->id ) {
			return $form_fields;
		}
		$post_id = $post->ID;

		$is_protected_file                     = $this->repository->is_protected_file( $post_id );
		$limited                               = $this->pda_admin->is_file_limitation_over();
		$exists                                = \Pda_Helper::does_url_exists( $post_id );
		$form_fields['pda_protection_setting'] = array(
			'value' => $is_protected_file,
			'label' => '<h2>Prevent Direct Access</h2>',
			'input' => 'html',
			'html'  => $this->render_ui( $post_id, $is_protected_file, $limited ) . $this->load_media_js( $post_id, $exists ? 1 : 0 ),
		);

		return $form_fields;
	}

	/**
	 * Handle when user updating attachment data.
	 *
	 * @param object $post       Post.
	 * @param array  $attachment Attachment data.
	 *
	 * @return mixed
	 */
	public function update_protection_status( $post, $attachment ) {
		if ( ! isset( $attachment['pda_protection_setting_hidden'] ) || 'pda_protection_setting_hidden' !== $attachment['pda_protection_setting_hidden'] ) {
			return $post;
		}
		$post_id = $post['ID'];

		if ( isset( $attachment['pda_protection_setting'] ) ) {
			// Update metadata to protect file.
			$file_result = $this->pda_admin->insert_prevent_direct_access( $post_id, 1 );
		} else {
			// Update metadata to unprotect file.
			$file_result = $this->pda_admin->insert_prevent_direct_access( $post_id, 0 );
		}

		// Allow to move file when number of protected file is not maximum.
		if ( ! is_array( $file_result ) || ! isset( $file_result['error'] ) ) {
			$this->pda_admin->handle_move_file( $post_id );
		}

		return $post;
	}

	/**
	 * Check condition to load asset
	 */
	public function enqueue_media() {
		global $pagenow, $mode;
		$pda_should_add = wp_script_is( 'media-views' ) || ( 'upload.php' === $pagenow && 'grid' === $mode );
		if ( ! $pda_should_add ) {
			return;
		}

		wp_enqueue_style( 'pda-free-add-media-css', $this->dir_url . 'assets/style.css', array(), PDAF_VERSION, 'all' );
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
		wp_enqueue_script( 'pda-free-add-media-js', $this->dir_url . 'assets/script.js', array( 'jquery' ), PDAF_VERSION );
	}
}
