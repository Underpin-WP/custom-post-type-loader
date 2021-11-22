<?php

use Underpin\Abstracts\Underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add this loader.
Underpin::attach( 'setup', new \Underpin\Factories\Observer( 'custom_post_types', [
	'update' => function ( Underpin $plugin ) {
		require_once( plugin_dir_path( __FILE__ ) . 'Custom_Post_Type.php' );
		require_once( plugin_dir_path( __FILE__ ) . 'Custom_Post_Type_Instance.php' );
		$plugin->loaders()->add( 'custom_post_types', [
			'instance' => 'Underpin_Custom_Post_Types\Abstracts\Custom_Post_Type',
			'default'  => 'Underpin_Custom_Post_Types\Factories\Custom_Post_Type_Instance'
		] );
	},
] ) );