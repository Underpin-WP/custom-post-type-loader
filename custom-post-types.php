<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add this loader.
add_action( 'underpin/before_setup', function ( $file, $class ) {
		require_once( plugin_dir_path( __FILE__ ) . 'Custom_Post_Type.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'Custom_Post_Type_Instance.php' );
		Underpin\underpin()->get( $file, $class )->loaders()->add( 'custom_post_types', [
			'instance' => 'Underpin_Custom_Post_Types\Abstracts\Custom_Post_Type',
			'default'  => 'Underpin_Custom_Post_Types\Factories\Custom_Post_Type_Instance'
		] );
}, 10, 2 );