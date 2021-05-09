<?php
/**
 * Custom Post Type Abstraction.
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */


namespace Underpin_Custom_Post_Types\Abstracts;


use Underpin\Traits\Feature_Extension;
use function Underpin\underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Custom_Post_Type
 * Class Custom Post Type
 *
 * @since   1.0.0
 * @package Underpin\Abstracts
 */
abstract class Custom_Post_Type {

	use Feature_Extension;

	/**
	 * The post type.
	 *
	 * @since 1.0.0
	 *
	 * @var string The post type "$type" argument.
	 */
	protected $type = '';

	/**
	 * The post type args.
	 *
	 * @since 1.0.0
	 *
	 * @var array The list of post type args. See https://developer.wordpress.org/reference/functions/register_post_type/
	 */
	protected $args = [];

	/**
	 * @inheritDoc
	 */
	public function do_actions() {
		add_action( 'init', [ $this, 'register' ] );
		add_filter( 'rest_' . $this->type . '_query', [ $this, 'rest_query' ], 10, 2 );
	}

	/**
	 * Updates REST Requests to use prepared query arguments for REST Requests.
	 *
	 * @since 1.0.0
	 *
	 * @param array            $args
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 */
	public function rest_query( $args, \WP_REST_Request $request ) {
		return $this->prepare_query_args( $args );
	}

	/**
	 * Registers the post type.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$registered = register_post_type( $this->type, $this->args );

		if ( is_wp_error( $registered ) ) {
			underpin()->logger()->log_wp_error( 'error', $registered );
		} else {
			underpin()->logger()->log(
				'notice',
				'custom_post_type_registered',
				'The custom post type ' . $this->type . ' has been registered.',
				[ 'type' => $this->type, 'args' => $this->args ]
			);
		}
	}

	public function __get( $key ) {
		if ( isset( $this->$key ) ) {
			return $this->$key;
		} else {
			return new \WP_Error( 'custom_post_type_param_not_set', 'The custom post type key ' . $key . ' could not be found.' );
		}
	}

	/**
	 * Run a WP_Query against this post type.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Query arguments to provide.
	 *
	 * @return \WP_Query The WP Query object.
	 */
	public function query( $args = [] ) {
		return new \WP_Query( $this->prepare_query_args( $args ) );
	}

	/**
	 * Prepares query args specific to this post type.
	 *
	 * @since 1.0.o
	 *
	 * @param array $args Post args to process.
	 *
	 * @return array Processed query arguments.
	 */
	public function prepare_query_args( array $args ) {
		$args['post_type'] = $this->type;

		return $args;
	}

	/**
	 * Deletes a single post.
	 *
	 * @since 1.0.0
	 *
	 * @param int  $postid       The post ID.
	 * @param bool $force_delete Optional. Whether to bypass Trash and force deletion.
	 *                           Default false.
	 *
	 * @return \WP_Post|false|null|mixed The post on success, false or null on failure, or a mixed value.
	 */
	protected function _delete( $id, $force_delete = false ) {
		return wp_delete_post( $id, $force_delete );
	}

	/**
	 * Update a post with new post data.
	 *
	 * @since 1.0.0
	 *
	 * @param array|object $args Post insert args. See wp_update_post.
	 *
	 * @return int|\WP_Error The post ID on success. WP_Error on failure.
	 */
	protected function _update( $args ) {
		return wp_update_post( $args, true );
	}

	/**
	 * Insert a post.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Post insert args. see wp_insert_post.
	 *
	 * @return int|\WP_Error The post ID on success. The value 0 or WP_Error on failure.
	 */
	protected function _insert( $args ) {
		return wp_insert_post( $args, true );
	}

	/**
	 * Saves a post to the database.
	 *
	 * @param array $args
	 *
	 * @return int|\WP_Error Post ID on success, WP_Error on failure.
	 */
	public function save( array $args ) {

		// Annoying.
		if ( isset( $args['id'] ) ) {
			$args['ID'] = $args['id'];
			unset( $args['id'] );
		}

		$args['post_type'] = $this->type;

		$saved = isset( $args['ID'] ) ? $this->_update( $args ) : $this->_insert( $args );

		if ( is_wp_error( $saved ) ) {
			underpin()->logger()->log_wp_error( $saved );
		} else {
			underpin()->logger()->log(
				'notice',
				$this->type . '_saved',
				'A ' . $this->type . ' was saved',
				[ $args ]
			);
		}

		return $saved;
	}

	/**
	 * Attempts to delete the provided post.
	 * If the post does not match this post type, this will return a WP_Error object.
	 *
	 * @param int  $postid       The post ID.
	 * @param bool $force_delete Optional. Whether to bypass Trash and force deletion.
	 *                           Default false.
	 *
	 * @return \WP_Post|\WP_Error
	 */
	public function delete( $id, $force_delete ) {

		$post_type = get_post_type( $id );

		if ( false === $post_type ) {
			return underpin()->logger()->log_as_error(
				'warning',
				'post_delete_post_does_not_exist',
				'A post was not deleted because it does not exist.',
				[ 'id' => $id ]
			);
		}

		if ( $post_type !== $this->type ) {
			return underpin()->logger()->log_as_error(
				'error',
				'post_delete_wrong_post_type',
				'A post was not deleted because it is the wrong post type.',
				[ 'post_type' => $post_type, 'expects' => $this->type, 'id' => $id ]
			);
		}

		$deleted = $this->_delete( $id, $force_delete );

		// Delete can return just about anything, so we have to be explicit here.
		if ( ! ( $deleted instanceof \WP_Post && $id === $deleted->ID ) ) {

			// If the post returns something falsy, it's most-likely an error.
			if ( false === $deleted || null === $deleted ) {
				return underpin()->logger()->log_as_error(
					'error',
					'post_delete_failed',
					'A post was not deleted.',
					[ 'response' => $deleted, 'post' => get_post( $id ) ]
				);
				// If we got something else, it's probably not an error.
			} else {
				return underpin()->logger()->log_as_error(
					'warning',
					'post_delete_cancelled',
					'A post was not deleted, but this may have been intentional.',
					[ 'response' => $deleted, 'post' => get_post( $id ) ]
				);
			}
		}

		return $deleted;
	}

}