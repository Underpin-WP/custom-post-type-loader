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
		$args['post_type'] = $this->type;

		return new \WP_Query( $args );
	}

}