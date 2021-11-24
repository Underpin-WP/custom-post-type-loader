<?php

use Underpin\Abstracts\Underpin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add this loader.
Underpin::attach( 'setup', new \Underpin\Factories\Observers\Loader( 'custom_post_types', [
	'abstraction_class' => 'Underpin\Custom_Post_Types\Abstracts\Custom_Post_Type',
	'default_factory'  => 'Underpin\Custom_Post_Types\Factories\Custom_Post_Type_Instance',
] ) );