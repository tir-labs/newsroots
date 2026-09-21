<?php
/**
 * Govpack
 *
 * @package Govpack
 */

namespace Govpack\Blocks;

use WP_Block;

defined( 'ABSPATH' ) || exit;

/**
 * Register and handle the block.
 */
class LegacyProfile extends \Govpack\Abstracts\Block {

	public string $block_name = 'govpack/profile';
	public $template          = 'profile';

	private $show         = null;
	private $profile      = null;
	public $attributes = [];
	protected $plugin;

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	public function disable_block( $allowed_blocks, $editor_context ): bool {
		return false;
	}

	public function block_build_path(): string {
		return $this->plugin->build_path( 'blocks/LegacyProfile' );
	}
	
	/**
	 * Block render handler for .
	 *
	 * @param array  $attributes    Array of shortcode attributes.
	 * @param string $content Post content.
	 * @param WP_Block $block Reference to the block being rendered .
	 *
	 * @return string HTML for the block.
	 */
	public function render( array $attributes, ?string $content = null, ?WP_Block $block = null ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable

		if ( ! $attributes['profileId'] ) {
			return;
		}

		if ( \is_admin() ) {
			return false;
		}


		return $this->handle_render( $attributes, $content, $block );
	}

	/**
	 * Loads a block from display on the frontend/via render.
	 *
	 * @param array  $attributes array of block attributes.
	 * @param string $content Any HTML or content redurned form the block.
	 * @param WP_Block $template The filename of the template-part to use.
	 */
	public function handle_render( array $attributes, string $content, WP_Block $block ) {

	
		$this->profile = \Govpack\Profile\CPT::get_data( $attributes['profileId'] );
		

		if ( ! $this->profile ) {
			return;
		}

		$this->enqueue_view_assets();

		$this->attributes = self::merge_attributes_with_block_defaults( $this->block_name, $attributes );
		return gp_template_loader()->render_block(
			$this->template(),
			$this->attributes, 
			$content, 
			$block, 
			[
				'profile_block' => $this,
				'profile_data'  => $this->profile,
			] 
		);
	}   

	public function populate_show() {
		return [
			'photo'             => ( has_post_thumbnail( $this->profile['id'] ) && 
										isset( $this->attributes['showAvatar'] ) && 
										$this->attributes['showAvatar']  
									),
			'name'              => ( isset( $this->profile['name'] ) && $this->attributes['showName'] ),
			'status_tag'        => ( isset( $this->profile['status'] ) && $this->attributes['showStatusTag'] ),
			'secondary_address' => ( isset( $this->profile['address']['secondary'] ) && ( $this->profile['address']['secondary'] !== $this->profile['address']['default'] ) ),
			'social'            => ( $this->attributes['showSocial'] && $this->profile['hasSocial'] ),
			'bio'               => ( $this->attributes['showBio'] && $this->profile['bio'] ),
			'labels'            => ( isset( $this->attributes['showLabels'] ) && ( $this->attributes['showLabels'] ) ),
			'links'             => $this->should_show_links(),
			'capitol_comms'     => $this->should_show_comms( 'capitol' ),
			'district_comms'    => $this->should_show_comms( 'district' ),
			'campaign_comms'    => $this->should_show_comms( 'campaign' ),
			'contact'           => ($this->should_show_comms( 'capitol' ) ||  $this->should_show_comms( 'district' ) || $this->should_show_comms( 'campaign' )),
			'profile_link'      => ( isset( $this->attributes['showProfileLink'] ) && $this->attributes['showProfileLink'] ),
		];
	}

	private function should_show_comms( $group ) {

		if ( $group === 'capitol' ) {
			$show_attr     = 'showCapitolCommunicationDetails';
			$selected_attr = 'selectedCapitolCommunicationDetails';
		} elseif ( $group === 'district' ) {
			$show_attr     = 'showDistrictCommunicationDetails';
			$selected_attr = 'selectedDistrictCommunicationDetails';
		} elseif ( $group === 'campaign' ) {
			$show_attr     = 'showCampaignCommunicationDetails';
			$selected_attr = 'selectedCampaignCommunicationDetails';
		} {
			$show_attr = 'showCampaignCommunicationDetails';
		}

		

		
		if ( ! isset( $this->attributes[ $show_attr ] ) || ! isset( $this->attributes[ $selected_attr ] ) || empty( $this->attributes[ $selected_attr ] ) || ( ! $this->attributes[ $show_attr ] ) ) {
			return false;
		}

		$selected_attrs = array_filter( $this->attributes[ $selected_attr ] );

		if ( empty( $selected_attrs ) ) {
			return false;
		}

		return true;
	}

	private function should_show_links() {
		if ( isset( $this->attributes['showOtherLinks'] ) ) {
			return $this->attributes['showOtherLinks'];
		}
	
		if ( ! isset( $this->profile['links'] ) || empty( $this->profile['links'] ) ) {
			return false;
		}
	
		if (
			( ! isset( $this->attributes['selectedLinks'] ) ) ||
			( empty( $this->profile['selectedLinks'] ) )
		) {
			return true;
		}
	
		return false;
	}

	public function get_profile_links() {
		
		if ( ! isset( $this->profile['links'] ) ) {
			return [];
		}

		if ( empty( $this->profile['links'] ) ) {
			return [];
		}

		
		$links = apply_filters( 'govpack_profile_links', $this->profile['links'] ?? [], $this->profile['id'], $this->profile );
		foreach ( $links as &$link ) {

			$link = apply_filters( 'govpack_profile_link', $link, $this->profile['id'], $this->profile );

			$link_attrs = array_filter(
				$link,
				function ( $value, $key ) {
					if ( ( $value === null ) || ( $value === '' ) ) {
						return false;
					}

					if ( ( $key === 'text' ) || ( $key === 'meta' ) ) {
						return false;
					}
				
					if ( is_array( $value ) && ( empty( $value ) ) ) {
						return false;
					}

					return true;
				},
				ARRAY_FILTER_USE_BOTH
			);

			$link['src'] = sprintf( '<a %s>%s</a>', gp_normalise_html_element_args( $link_attrs ), $link['text'] );
			
		}

		return $links;
	}

	public function rows() {

		$rows = [ 
			[
				'key'        => 'party',
				'value'      => esc_html( $this->profile['party'] ),
				'label'      => 'Party',
				'shouldShow' => $this->attributes['showParty']  && !empty($this->profile['party']),
			],

			[
				'key'        => 'position',
				'value'      => esc_html( $this->profile['position'] ),
				'label'      => 'Position',
				'shouldShow' => $this->attributes['showPosition'] && !empty($this->profile['position']),
			],

			[
				'key'        => 'leg_body',
				'value'      => esc_html( $this->profile['legislative_body'] ),
				'label'      => 'Office',
				'shouldShow' => $this->attributes['showLegislativeBody']  && !empty($this->profile['legislative_body']),
			],
			
			[
				'key'        => 'status',
				'value'      => esc_html( $this->profile['status'] ),
				'label'      => 'Status',
				'shouldShow' => $this->attributes['showStatus'] && !empty($this->profile['status']),
			],

			[
				'key'        => 'age',
				'value'      => esc_html( $this->profile['age'] ),
				'label'      => 'Age',
				'shouldShow' => $this->attributes['showAge']  && !empty($this->profile['age']),
			],
			
			[
				'key'        => 'state',
				'value'      => esc_html( $this->profile['state'] ),
				'label'      => 'State',
				'shouldShow' => $this->attributes['showState'] && !empty($this->profile['state']),
			],
			[
				'key'        => 'district',
				'value'      => esc_html( $this->profile['district'] ),
				'label'      => 'District',
				'shouldShow' => $this->attributes['showDistrict'] && !empty($this->profile['district']),
			],
			
			[
				'key'        => 'endorsements',
				'value'      => esc_html( $this->profile['endorsements'] ),
				'label'      => 'Endorsements',
				'shouldShow' => $this->attributes['showEndorsements'] && !empty($this->profile['endorsements']),
			],
			[
				'key'			=> 'social',
				'value'			=> $this->profile['social'],
				'label'			=> 'Social Media',
				'shouldShow'	=> $this->show( 'social' ),
				'format' 		=> 'group'
			],
			[
				'key'        => 'contact',
				'value'      => $this->profile['contact'],
				'label'      => 'Contact Info',
				'shouldShow' => $this->show( 'contact' ),
				'format' 	=> 'group'
			],
			/*
			[
				'key'        => 'comms_capitol',
				'value'      => $this->profile['comms']['capitol'],
				'label'      => 'Contact Info (Official)',
				'subLabel'   => 'Official',
				'shouldShow' => $this->show( 'capitol_comms' ),
				'show'       => $this->attributes['selectedCapitolCommunicationDetails'],
			],
			[
				'key'        => 'comms_district',
				'label'      => 'Contact Info (District)',
				'subLabel'   => 'District',
				'value'      => $this->profile['comms']['district'],
				'shouldShow' => $this->show( 'district_comms' ),
				'show'       => $this->attributes['selectedDistrictCommunicationDetails'],
			],
			[
				'key'        => 'comms_campaign',
				'value'      => $this->profile['comms']['campaign'],
				'label'      => 'Contact Info (Campaign)',
				'subLabel'   => 'Campaign',
				'shouldShow' => $this->show( 'campaign_comms' ),
				'show'       => $this->attributes['selectedCampaignCommunicationDetails'],
			],
			[
				'key'        => 'comms_other',
				'value'      => $this->profile['comms']['other'],
				'label'      => 'Contact Info (Other)',
				'shouldShow' => $this->show( 'other_comms' ),
				'show'       => $this->attributes['selectedOtherCommunicationDetails'],
			],
			*/
			[
				'key'			=> 'links',
				'value'			=> $this->get_profile_links(),
				'shouldShow'	=> $this->show( 'links' ),
				'label'			=> 'Links',
				'format' 		=> 'collection'
			],
			[
				'key'        => 'more_about',
				'shouldShow' => $this->show( 'profile_link' ),
			],
			
		];

		// the format key was added late, array map each row so a format is definatley set
		return array_map(function ($r){

			if(!isset($r['format'])){
				$r['format'] = "row";
			}

			return $r;
		}, $rows);
	}

	public function show( $key ) {

		if ( $this->show === null ) {
			$this->show = $this->populate_show();
		}

		if ( ! isset( $this->show[ $key ] ) ) {
			return false;
		}

		return $this->show[ $key ];
	}

	public function template(): string {
		return sprintf( 'blocks/%s', $this->template );
	}
}
