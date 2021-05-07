# Underpin Custom Post Type Loader

Loader That assists with adding custom Post Types to a WordPress website.

## Installation

### Using Composer

`composer require underpin/custom-post-type-loader`

### Manually

This plugin uses a built-in autoloader, so as long as it is required _before_
Underpin, it should work as-expected.

`require_once(__DIR__ . '/underpin-custom-post-type/custom-post-types.php');`

## Setup

1. Install Underpin. See [Underpin Docs](https://www.github.com/underpin-wp/underpin)
1. Register new custom post types menus as-needed.

## Example

A very basic example could look something like this.

```php
// Register custom Post Type
underpin()->custom_post_types()->add( 'example_type', [
	'type' => 'example-type', // see register_post_type
	'args' => [ /*...*/ ] // see register_post_type
] );

```

Alternatively, you can extend `Custom_Post_Type` and reference the extended class directly, like so:

```php
underpin()->custom_post_types()->add('custom-post-type-key','Namespace\To\Class');
```

## Querying

A Custom Post Type instance includes a method, called `query`, which serves as a wrapper for `new WP_Query`.

This encapsulates queries for this post type in a method, and gives you a place to override exactly _how_ this post type
is queried, should you decide to extend the class.

```php
underpin()->custom_post_types()->get( 'post_type' )->query();
```