<?php

add_action( 'init', function() {

	//bail early as this should go to admin only
	if ( ! is_admin() ) {
		return;
	}

	$next_post = new Babble_Translatable_Fieldmanager(
		'Fieldmanager_Autocomplete',
		array(
			'name'           => 'next_page',
			'show_edit_link' => true,
			'datasource'     => new Fieldmanager_Datasource_Post(
				array(
					'query_args' => array(
						'post_type' => 'page',
					),
				)
			),
		),
		array(
			'add_meta_box' => array(
				'Next Page',
				'page'
			)
		)
	);

	$datasource_post = new Fieldmanager_Datasource_Post( array(
		'query_args' => array( 'post_type' => array( 'io_story', 'post', 'page' ), 'posts_per_page' => -1 ),
		'use_ajax' => false,
	) );

	$fm = new Babble_Translatable_Fieldmanager(
		'Fieldmanager_Group',
		array(
			'name'           => 'home-content-section',
			'label'          => __( 'Section', 'internetorg' ),
			'label_macro'    => __( 'Section: %s', 'internetorg' ),
			'add_more_label' => __( 'Add another Content Area', 'internetorg' ),
			'collapsed'      => false,
			'collapsible'    => true,
			'sortable'       => true,
			'limit'          => 0,
			'children'       => array(
				'title'          => new Fieldmanager_TextField( __( 'Section Title', 'internetorg' ) ),
				'name'           => new Fieldmanager_TextField( __( 'Section Name', 'internetorg' ) ),
				'content'        => new Babble_Fieldmanager_RichTextarea( __( 'Description', 'internetorg' ) ), //Note Babble_Fieldmanager_RichTextarea being used
				'src' => new Fieldmanager_Radios( __( 'Source', 'internetorg' ), array(
						'name'    => 'src',
						'default_value' => 'page',
						'options' => array(
							'page' => __( 'Page, Post, or Story' ),
							'custom' => __( 'Custom Link', 'internetorg' ),
						),
					)
				),
				'slug'  => new Fieldmanager_TextField( __( 'Section Slug', 'internetorg' ),
					array(
						'display_if' => array(
							'src' => 'src',
							'value' => 'custom',
						),
					)
				),
				'url-src' => new Fieldmanager_Select( __( 'URL Source', 'internetorg' ),
					array(
						'datasource' => $datasource_post,
						'display_if' => array(
							'src' => 'src',
							'value' => 'page',
						),
					)
				),
				'theme' => new Fieldmanager_Select( array(
					'label' => 'Select a Theme',
					'options' => array(
						'approach' => __( 'Approach', 'internetorg' ),
						'mission' => __( 'Mission', 'internetorg' ),
						'impact' => __( 'Impact', 'internetorg' ),
					),
				) ),
				'image'          => new Fieldmanager_Media( __( 'Background Image', 'internetorg' ) ),
				'call-to-action' => new Fieldmanager_Group(
					array(
						'label'          => __( 'Call to action', 'internetorg' ),
						'label_macro'    => __( 'Call to action: %s', 'internetorg' ),
						'add_more_label' => __( 'Add another CTA', 'internetorg' ),
						'limit'          => 5,
						'collapsible'    => true,
						'children'       => array(
							'title' => new Fieldmanager_TextField( __( 'CTA Title', 'internetorg' ) ),
							'text'  => new Babble_Fieldmanager_RichTextarea( __( 'Content', 'internetorg' ) ), //Note Babble_Fieldmanager_RichTextarea being used
							'cta_src' => new Fieldmanager_Radios( __( 'Link Source', 'internetorg' ), array(
									'name'    => 'cta_src',
									'default_value' => 'page',
									'options' => array(
										'page' => __( 'Page, Post, or Story' ),
										'custom' => __( 'Custom Link', 'internetorg' ),
									),
								)
							),
							'link'  => new Fieldmanager_TextField( __( 'Link', 'internetorg' ),
								array(
									'display_if' => array(
										'src' => 'cta_src',
										'value' => 'custom',
									),
								)
							),
							'link_src' => new Fieldmanager_Select( __( 'URL Source', 'internetorg' ),
								array(
									'datasource' => $datasource_post,
									'display_if' => array(
										'src' => 'cta_src',
										'value' => 'page',
									),
								)
							),
							'image' => new Fieldmanager_Media( __( 'Image', 'internetorg' ) ),
						),
					)
				),
			),
		),
		array(
			'add_meta_box' => array(
				__( 'Content Areas', 'internetorg' ),
				array( 'page' )
			)
		)
	);

	new Babble_Translatable_Fieldmanager(
		'Fieldmanager_TextArea',
		array(
			'name'       => 'page_subtitle',
			'label'      => __( 'Subtitle', 'internetorg' ),
			'attributes' => array(
				'rows' => 3,
				'cols' => 30,
			),
		),
		array(
			'add_meta_box' => array(
				__( 'Additional page configuration', 'internetorg' ),
				array( 'page' ),
				'internetorg_page_home_after_title',
				'high'
			)
		)
	);

	new Babble_Translatable_Fieldmanager(
		'Fieldmanager_Group',
		array(
			'name'     => 'page_intro_block',
			'children' => array(
				'intro_title'   => new Fieldmanager_TextField(
					array(
						'label' => __( 'Intro Title', 'internetorg' ),
					)
				),
				'intro_content' => new Fieldmanager_TextArea(
					array(
						'label'      => __( 'Intro Copy', 'internetorg' ),
						'attributes' => array(
							'rows' => 3,
							'cols' => 30,
						),
					)
				),
			),
		),
		array(
			'add_meta_box' => array(
				__( 'Page Intro', 'internetorg' ),
				array( 'page' ),
				'internetorg_page_home_after_title',
				'high'
			)
		)
	);
} );
