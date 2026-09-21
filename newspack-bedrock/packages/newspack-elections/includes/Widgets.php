<?php

namespace Govpack;

class Widgets {

	public static function hooks(): void {
		add_action( 'widgets_init', [ __CLASS__, 'register_widget_area' ], 10, 0 );
	}

	public static function register_widget_area(): void {
		register_sidebar(
			[
				'name'          => __( 'Newspack Elections Sidebar ', 'newspack-elections' ),
				'id'            => 'newspack-elections-sidebar',
				'description'   => esc_html__( 'Add widgets here to appear in the sidebar of Govpack profiles.', 'newspack-elections' ),
				'before_widget' => '<section id="%1$s" class="below-content widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<h2 class="widgettitle">',
				'after_title'   => '</h2>',
			]
		);
	}
}
