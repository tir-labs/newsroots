<?php // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals,WordPress.WP.GlobalVariablesOverride ?>

<?php defined( 'ABSPATH' ) || die ?>

<h3><?php esc_attr_e( 'Edit robots.txt', 'slim-seo' ); ?></h3>

<div id="ss-robots"></div>

<h3><?php esc_attr_e( 'AI Integration', 'slim-seo' ); ?></h3>
<p>
	<?php esc_html_e( 'Slim SEO uses the official WordPress AI plugin for meta titles and descriptions. Connect a provider under Settings → AI. Authors need the edit_posts capability; bulk generation requires edit_others_posts.', 'slim-seo' ); ?>
</p>
<p>
	<?php if ( \SlimSEO\MetaTags\AI::is_available() ) : ?>
		<strong><?php esc_html_e( 'WordPress AI is available.', 'slim-seo' ); ?></strong>
		<a href="<?php echo esc_url( admin_url( 'options-general.php?page=ai' ) ); ?>"><?php esc_html_e( 'Open AI settings', 'slim-seo' ); ?></a>
	<?php else : ?>
		<strong><?php esc_html_e( 'WordPress AI is not active.', 'slim-seo' ); ?></strong>
		<?php esc_html_e( 'Install the AI plugin (wordpress.org/plugins/ai) and connect a provider.', 'slim-seo' ); ?>
	<?php endif; ?>
</p>

<h3><?php esc_attr_e( 'Bulk Generate with AI', 'slim-seo' ); ?></h3>
<p><?php esc_html_e( 'Automatically generate meta titles and descriptions for all your post types and taxonomies using AI.', 'slim-seo' ); ?></p>

<div class="ss-bulk-ai-settings">
	<div class="ef-control">
		<div class="ef-control__label">
			<label><?php esc_html_e( 'Post Types', 'slim-seo' ); ?></label>
		</div>
		<div class="ef-control__input">
			<?php
			$post_types = eLightUp\SlimSEO\Common\Helpers\Data::get_post_types();
			unset( $post_types['attachment'] );
			foreach ( $post_types as $post_type ) :
				?>
				<label>
					<input type="checkbox" name="ss_bulk_post_types[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, [ 'post', 'page' ], true ) ); ?> />
					<?php echo esc_html( $post_type->labels->singular_name ); ?>
					(<?php echo esc_html( $post_type->name ); ?>)
				</label>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="ef-control">
		<div class="ef-control__label">
			<label><?php esc_html_e( 'Taxonomies', 'slim-seo' ); ?></label>
		</div>
		<div class="ef-control__input">
			<?php
			$public_taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
			foreach ( $public_taxonomies as $tax ) :
				?>
				<label>
					<input type="checkbox" name="ss_bulk_taxonomies[]" value="<?php echo esc_attr( $tax->name ); ?>" />
					<?php echo esc_html( $tax->labels->singular_name ); ?>
					(<?php echo esc_html( $tax->name ); ?>)
				</label>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="ef-control">
		<div class="ef-control__label">
			<label><?php esc_html_e( 'Overwrite', 'slim-seo' ); ?></label>
		</div>
		<div class="ef-control__input">
			<label>
				<input type="checkbox" name="ss_bulk_skip_title" value="1" checked />
				<?php esc_html_e( 'Keep existing meta titles (only generate for empty ones)', 'slim-seo' ); ?>
			</label>
			<label>
				<input type="checkbox" name="ss_bulk_skip_description" value="1" checked />
				<?php esc_html_e( 'Keep existing meta descriptions (only generate for empty ones)', 'slim-seo' ); ?>
			</label>
		</div>
	</div>

	<div class="ef-control">
		<div class="ef-control__label"></div>
		<div class="ef-control__input">
			<div class="ss-bulk-ai-actions">
				<button type="button" class="button" id="ss-bulk-ai-start"><?php esc_html_e( 'Generate', 'slim-seo' ); ?></button>
				<button type="button" class="button button-link" id="ss-bulk-ai-show-log" style="display:none"><?php esc_html_e( 'Show Logs', 'slim-seo' ); ?></button>
			</div>
			<div id="ss-bulk-ai-progress"></div>
		</div>
	</div>

	<div id="ss-bulk-ai-log-overlay" class="ss-modal-overlay" style="display:none"></div>
	<div id="ss-bulk-ai-log-modal" class="ss-modal-body ss-bulk-ai-modal" style="display:none">
		<div class="ss-modal-heading">
			<span><?php esc_html_e( 'Generation Logs', 'slim-seo' ); ?></span>
			<span class="ss-modal__close" id="ss-bulk-ai-close-log">&times;</span>
		</div>
		<div id="ss-bulk-ai-log" role="log">
			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Time', 'slim-seo' ); ?></th>
						<th><?php esc_html_e( 'Status', 'slim-seo' ); ?></th>
						<th><?php esc_html_e( 'Item', 'slim-seo' ); ?></th>
						<th><?php esc_html_e( 'Message', 'slim-seo' ); ?></th>
					</tr>
				</thead>
				<tbody id="ss-bulk-ai-log-body"></tbody>
			</table>
		</div>
	</div>
</div>

<h3><?php esc_attr_e( 'Migrate SEO Data', 'slim-seo' ); ?></h3>
<p><?php esc_html_e( 'Use the drop down below to choose which plugin you wish to import SEO data from.', 'slim-seo' ); ?></p>
<p><strong><?php esc_attr_e( 'Before performing an import, we strongly recommend that you make a backup of your site.', 'slim-seo' ); ?></strong></p>

<div class="ef-control">
	<div class="ef-control__label">
		<label for="source"><?php esc_html_e( 'Migrate SEO data from:', 'slim-seo' ); ?></label>
	</div>
	<div class="ef-control__input">
		<div class="ss-input-wrapper migration-handler">
			<select name="source" id="source">
				<optgroup value="meta" label="<?php esc_html_e( 'SEO plugins', 'slim-seo' ); ?>">
					<?php $sources = SlimSEO\Helpers\Data::get_migration_sources( 'meta' ) ?>
					<?php foreach ( $sources as $id => $source ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $source ); ?></option>
					<?php endforeach ?>
				</optgroup>

				<optgroup value="redirection" label="<?php esc_html_e( 'Redirection plugins', 'slim-seo' ); ?>">
					<?php $sources = SlimSEO\Helpers\Data::get_migration_sources( 'redirection' ) ?>
					<?php foreach ( $sources as $id => $source ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $source ); ?></option>
					<?php endforeach ?>
				</optgroup>
			</select>
			<button type="button" class="button" id="process"><?php esc_html_e( 'Migrate', 'slim-seo' ); ?></button>
		</div>
		<div class="migration-status">
			<div id="posts-status"></div>
			<div id="terms-status"></div>
			<div id="redirects-status"></div>
			<div id="robots-status"></div>
			<div id="done-status"></div>
		</div>
	</div>
</div>

<?php do_action( 'slim_seo_tools_tab_content' ); ?>

<?php submit_button( __( 'Save Changes', 'slim-seo' ) ); ?>
