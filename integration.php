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
		return $value;
	}

	/**
	 * Update post meta.
	 *
	 * @see update_post_meta().
	 */
	protected function update_data( $post_id, $meta_key, $meta_value, $data_prev_value = '' ) {
		return $value;
	}

	/**
	 * Delete post meta.
	 *
	 * @see delete_post_meta().
	 */
	protected function delete_data( $post_id, $meta_key, $meta_value = '' ) {
		return $value;
	}

	public function save_to_post_meta( $post_id, $data = null ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		$this->fm->data_id = $post_id;
		$this->fm->data_type = 'post';

		return $this->save( $data );
	}

	protected function save( $data = null ) {
		// Reset the save keys in the event this context instance is saved twice
		$this->save_keys = array();

		if ( $this->fm->serialize_data ) {
			$data = $this->save_field( $this->fm, $data, $this->fm->data_id );
		} else {
			if ( null === $data ) {
				$data = isset( $_POST[ $this->fm->name ] ) ? $_POST[ $this->fm->name ] : '';
			}
			$data = $this->save_walk_children( $this->fm, $data, $this->fm->data_id );
		}
		return $data;
	}

	protected function save_field( $field, $data ) {
		$field->data_id = $this->fm->data_id;
		$field->data_type = $this->fm->data_type;

		if ( isset( $this->save_keys[ $field->get_element_key() ] ) ) {
			throw new FM_Developer_Exception( sprintf( esc_html__( 'You have two fields in this group saving to the same key: %s', 'fieldmanager' ), $field->get_element_key() ) );
		} else {
			$this->save_keys[ $field->get_element_key() ] = true;
		}

		$current = $this->get_data( $this->fm->data_id, $field->get_element_key(), $field->serialize_data );
		$data = $this->prepare_data( $current, $data, $field );
		return $data;
	}

	protected function save_walk_children( $field, $data ) {
		if ( $field->serialize_data || ! $field->is_group() ) {
			return $this->save_field( $field, $data );
		} else {
			foreach ( $field->children as $child ) {
				if ( isset( $data[ $child->name ] ) ) {
					return $this->save_walk_children( $child, $data[ $child->name ] );
				}
			}
		}
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

class Babble_Fieldmanager_RichTextarea extends Fieldmanager_RichTextarea {

	public $babble_element_id = null;

	public function render_field( $name, $value, $title, $post ) {
		$context = new Babble_Fieldmanager_Context( $title, $this );
		return $context->render_fields( $post, $value, true );;
	}
	public function get_element_id() {
		//allows different id for Babble edit screen
		if ( true == isset( $this->babble_element_id ) && false === empty( $this->babble_element_id ) ) {
			return $this->babble_element_id;
		}
		$el = $this;
		$id_slugs = array();
		while ( $el ) {
			$slug = $el->is_proto ? 'proto' : $el->seq;
			array_unshift( $id_slugs, $el->name . '-' . $slug );
			$el = $el->parent;
		}
		//wp_editor can not take ID containintg '[]'
		return str_replace( array( '[', ']' ), '-', 'fm-' . implode( '-', $id_slugs ) );
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

	public $name;

	public $args;

	public $translation_meta_key = 'bbl_translation[meta]';

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
		$this->name = "{$this->translation_meta_key}[{$meta_key}]";
		$fm_args['name'] = $this->name;

		$this->fm = new $type( $fm_args );

	}

	public function get_input( $name, $value ) {
		return $this->fm->render_field( $name, $value, $this->meta_title, $this->post );
	}

	public function get_output() {
		$this->set_readonly_attribute();
		$this->maybe_update_ids();

		add_filter( 'tiny_mce_before_init', array( $this, 'readonly_for_tinymce' ) );
		add_filter( 'the_editor', array( $this, 'readonly_for_editor_textarea' ) );

		ob_start();
		echo $this->fm->render_field( $this->name, $this->get_value(), $this->meta_title, $this->post );
		$field = ob_get_clean();

		remove_filter( 'tiny_mce_before_init', array( $this, 'readonly_for_tinymce' ) );
		remove_filter( 'the_editor', array( $this, 'readonly_for_editor_textarea' ) );

		$original_meta = 'bbl_translation_original[meta]';
		$field = str_replace( sprintf( 'name="%s' , $this->translation_meta_key ), sprintf( 'name="%s', $original_meta ) , $field );

		//echoing the field instead of properly returing it will preserve HTML
		echo $field;
	}

	public function readonly_for_tinymce( $args ) {
		$args['readonly'] = 1;
		return $args;
	}

	public function readonly_for_editor_textarea( $the_editor ) {
		$the_editor = str_replace( '>%s</textarea></div>', ' readonly="readonly">%s</textarea></div>', $the_editor );
		return $the_editor;
	}

	public function maybe_update_ids( $el = null ) {
		if ( null === $el ) {
			$el = $this->fm;
		}
		if ( $el instanceof Babble_Fieldmanager_RichTextarea ) {
			$el->babble_element_id = $el->get_element_id( $el ) . "-original";
		}
		if ( false === empty( $el->children ) ) {
			foreach ( $el->children as $child ) {
				$this->maybe_update_ids( $child );
			}
		}
	}

	public function set_readonly_attribute( $el = null ) {
		if ( null === $el ) {
			$el = $this->fm;
		}
		$el->attributes = array_merge( $el->attributes, array( 'readonly' => 'readonly' ) );
		if ( false === empty( $el->children ) ) {
			foreach ( $el->children as $child ) {
				$this->set_readonly_attribute( $child );
			}
		}
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
		$context = new Babble_Fieldmanager_Context( $this->name, $this->fm );
		$value = $context->save_to_post_meta( $job->ID, $value );
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
		add_filter( 'bbl_meta_before_save', array( $this, 'before_meta_save' ), 10, 2 );
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

	public function before_meta_save( $value, $job ) {
		return $this->babble_meta->update( $value, $job );
	}

}
