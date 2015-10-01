<?php

class Babble_Fieldmanager_Context extends Fieldmanager_Context_Storable {

	/**
	 * @var string
	 * Title of meta box
	 */
	public $title = '';

	/**
	 * @var Fieldmanager_Group
	 * Base field
	 */
	public $fm = null;

	/**
	 * Add a context to a fieldmanager
	 * @param string $title
	 * @param string|string[] $post_types
	 * @param string $context (normal, advanced, or side)
	 * @param string $priority (high, core, default, or low)
	 * @param Fieldmanager_Field $fm
	 */
	public function __construct( $title, $fm = Null ) {

		$this->title = $title;
		$this->fm = $fm;

	}

	public function render_fields( $post, $values = array(), $echo = true ) {
		$this->fm->data_type = 'post';
		$this->fm->data_id = $post->ID;

		$this->render_field( array( 'data' => $values, 'echo' => $echo ) );

		// Check if any validation is required
		$fm_validation = Fieldmanager_Util_Validation( 'post', 'post' );
		$fm_validation->add_field( $this->fm );
	}


	protected function render_field( $args = array() ) {
		$data = array_key_exists( 'data', $args ) ? $args['data'] : null;
		$echo = isset( $args['echo'] ) ? $args['echo'] : true;

		$field = $this->fm->element_markup( $data );
		if ( $echo ) {
			echo $field;
		} else {
			return $field;
		}
	}

	/**
	 * Get post meta.
	 *
	 * @see get_post_meta().
	 */
	protected function get_data( $post_id, $meta_key, $single = false ) {
		return get_post_meta( $post_id, $meta_key, $single );
	}

	/**
	 * Add post meta.
	 *
	 * @see add_post_meta().
	 */
	protected function add_data( $post_id, $meta_key, $meta_value, $unique = false ) {
		return;
	}

	/**
	 * Update post meta.
	 *
	 * @see update_post_meta().
	 */
	protected function update_data( $post_id, $meta_key, $meta_value, $data_prev_value = '' ) {
		return;
	}

	/**
	 * Delete post meta.
	 *
	 * @see delete_post_meta().
	 */
	protected function delete_data( $post_id, $meta_key, $meta_value = '' ) {
		return;
	}

}

class Babble_Fieldmanager_TextField extends Fieldmanager_TextField {
	public function render_field( $name, $value, $title, $post ) {
		$context = new Babble_Fieldmanager_Context( $title, $this );
		return $context->render_fields( $post, $value );
	}
}

class Babble_Fieldmanager_TextArea extends Fieldmanager_TextArea {
	public function render_field( $name, $value, $title, $post ) {
		$context = new Babble_Fieldmanager_Context( $title, $this );
		return $context->render_fields( $post, $value );
	}
}

class Babble_Fieldmanager_Media extends Fieldmanager_Media {
	public function render_field( $name, $value, $title, $post ) {
		$context = new Babble_Fieldmanager_Context( $title, $this );
		return $context->render_fields( $post, $value );
	}
}

class Babble_Fieldmanager_Group extends Fieldmanager_Group {
	public function render_field( $name, $value, $title, $post ) {
		$context = new Babble_Fieldmanager_Context( $title, $this );
		return $context->render_fields( $post, $value );
	}
}

//No Visial Editor for Babble since it's not working properly
class Babble_Fieldmanager_RichTextarea extends Fieldmanager_TextArea {
	public function render_field( $name, $value, $title, $post ) {
		$context = new Babble_Fieldmanager_Context( $title, $this );
		return $context->render_fields( $post, $value, true );;
	}
}

class Babble_Fieldmanager_Autocomplete extends Fieldmanager_Autocomplete {
	public function render_field( $name, $value, $title, $post ) {
		$context = new Babble_Fieldmanager_Context( $title, $this );
		return $context->render_fields( $post, $value );
	}
}

class Babble_Fieldmanager_Meta_Field extends Babble_Meta_Field {

	public $fm;

	public function __construct( WP_Post $post, $meta_key, $meta_title, array $args = array() ) {

		$this->post       = $post;
		$this->meta_key   = $meta_key;
		$this->meta_title = $meta_title;
		$this->meta_value = maybe_unserialize( get_post_meta( $this->post->ID, $this->meta_key, true ) );
		$this->args       = $args;

		$type = $this->args['fm']['type'];
		$fm_args = $this->args['fm']['args'];

		$type = "Babble_{$type}";

		//rename
		$fm_args['name'] = "bbl_translation[meta][{$meta_key}]";

		$this->fm = new $type( $fm_args );

	}

	public function get_input( $name, $value ) {
		$field = $this->fm->render_field( $name, $value, $this->meta_title, $this->post );
		return $field;
	}

	public function get_output() {
		return serialize( $this->get_value() );
	}

	public function get_title() {
		return $this->meta_title;
	}

	public function get_value() {
		return $this->meta_value;
	}

	public function get_key() {
		return $this->meta_key;
	}

	public function update( $value, WP_Post $job ) {
		return $value;
	}
}


class Babble_Translatable_Fieldmanager {

	public $type = null;

	public $args = array();

	public $action_args = array();

	public $babble_meta = null;

	public function __construct( $type, $args, $actions ) {

		$this->type = $type;
		$this->args = $args;
		$this->actions = $actions;

		foreach( $this->actions as $action => $action_args ) {
			switch( $action ) {
				case 'add_meta_box' :
					list( , $cpts ) = $action_args;
					if ( ! $cpts ) {
						$cpts = array( 'post' );
					} else if ( ! is_array( $cpts ) ) {
						$cpts = array( $cpts );
					}
					foreach ( $cpts as $cpt ) {
						add_action( "fm_post_{$cpt}", array( $this, 'register_fieldmanager_meta' ), 10, 0 );
					}
					break;
			}

		}
		add_filter( 'bbl_translated_meta_fields', array( $this, 'translated_meta_fields' ), 10, 2 );
		add_filter( 'bbl_sync_meta_key', array( $this, 'do_not_sync' ), 10, 2 );
		add_filter( 'bbl_meta_before_save', array( $this, 'before_meta_save' ), 10, 5 );
	}

	public function register_fieldmanager_meta() {

		$fm = new $this->type( $this->args );

		foreach( $this->actions as $action => $action_args ) {
			switch( $action ) {
				case 'add_meta_box' :
					list( $title, $posts ) = $action_args;
					if ( true === isset( $action_args[2] ) ) {
						$context = $action_args[2];
					} else {
						$context = 'advanced';
					}
					if ( true === isset( $action_args[3] ) ) {
						$priority = $action_args[3];
					} else {
						$priority = 'default';
					}
					$fm->add_meta_box( $title, $posts, $context, $priority );
					break;
			}

		}
	}

	public function translated_meta_fields( array $fields, WP_Post $post ) {

		$name = $this->args['name'];
		$title = $this->actions['add_meta_box'][0];

		$this->babble_meta = new Babble_Fieldmanager_Meta_Field( $post, $name, $title, array(
			'fm' => array(
				'type' => $this->type,
				'args' => $this->args,
			)
		) );

		$fields[$name] = $this->babble_meta;

		//This is a filter! Don't forget to return $fields
		return $fields;
	}

	public function do_not_sync( $synchronize, $meta_key ) {
		if ( $meta_key === $this->args['name'] ) {
			$synchronize = false;
		}
		return $synchronize;
	}

	public function before_meta_save( $value, $job, $meta_key, $meta_field, $meta_data ) {
		return $this->babble_meta->update( $value, $job );
	}

}
