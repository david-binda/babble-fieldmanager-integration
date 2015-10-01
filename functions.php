dd_action( 'init', function() {

	//bail early as this should go to admin only
	if ( ! is_admin() ) {
		return;
	}

	new Babble_Translatable_Fieldmanager(
		'Fieldmanager_TextField',
		array(
			'name' => 'demo-field',
		),
		array(
			'add_meta_box' => array(
				'TextField Demo',
				array( 'post' )
	        )
		)
	);
	
	new Babble_Translatable_Fieldmanager(
		'Fieldmanager_Group',
		array(
			'name' => 'demo-group',
			'children' => array(
				'field-one' => new Babble_Fieldmanager_TextField( 'First Field' ),
				'field-two' => new Babble_Fieldmanager_TextField( 'Second Field' ),
			),
		),
		array(
			'add_meta_box' => array(
				'Group demo',
				array( 'post' )
	        )
		)
	);

	new Babble_Translatable_Fieldmanager(
		'Fieldmanager_Group',
		array(
			'name'           => 'tabbed_meta_fields',
			'tabbed'         => 'vertical',
			'children'       => array(
			'tab-1' => new Babble_Fieldmanager_Group( array(
				'label'          => 'First Tab',
				'children'       => array(
					'text' => new Babble_Fieldmanager_Textfield( 'Text Field' ),
				)
			) ),
			'tab-2' => new Fieldmanager_Group( array(
				'label'          => 'Second Tab',
				'children'       => array(
					'textarea' => new Babble_Fieldmanager_TextArea( 'TextArea' ),
					'media'    => new Babble_Fieldmanager_Media( 'Media File' ),
				)
			) ),
		) ),
		array(
			'add_meta_box' => array(
				'Tabbed Demo',
				array( 'post' )
	        )
		)
	);

	// using Fieldmanager for a slideshow - any number of slides,
	// with any number of related links
	new Babble_Translatable_Fieldmanager(
		'Fieldmanager_Group',
		array(
			'name' => 'slideshow',
			'limit' => 0,
			'label' => __( 'New Slide', 'your-domain' ),
			'label_macro' => array( __( 'Slide: %s', 'your-domain' ), 'title' ),
			'add_more_label' => __( 'Add another slide', 'your-domain' ),
			'collapsed' => true,
			'sortable' => true,
			'children' => array(
				'title' => new Babble_Fieldmanager_Textfield( __( 'Slide Title', 'your-domain' ) ),
				'slide' => new Babble_Fieldmanager_Media( __( 'Slide', 'your-domain' ) ),
				'description' => new Babble_Fieldmanager_RichTextarea( __( 'Description', 'your-domain' ) ),
				'posts' => new Babble_Fieldmanager_Autocomplete( array(
					'label' => __( 'Related Posts', 'your-domain' ),
					'limit' => 0,
					'sortable' => true,
					'one_label_per_item' => false,
					'add_more_label' => __( 'Add another related link', 'your-domain' ),
					'datasource' => new Fieldmanager_Datasource_Post( array(
						'query_args' => array(
							'post_status' => 'any',
						),
					) ),
				) ),
			),
		),
		array(
			'add_meta_box' => array(
				__( 'Slides', 'your-domain' ),
				'post'
	        )
		)
	);

	new Babble_Translatable_Fieldmanager(
		'Fieldmanager_RichTextarea',
		array(
			'name' => 'demo-editor',
		),
		array(
			'add_meta_box' => array(
				'Editor Demo',
				array( 'post' )
			)
		)
	);

} );
