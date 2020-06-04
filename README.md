# WP dependancy manager

**⚠️ Work in progress ⚠️**

A plugin for managing and caching frontend dependancies.

# Local caching
Currently this plugin downloads and stores a local cache of any frontend enqueued style or script on runtime. For example, instead of:
```html
<link href="https://cdn.jsdelivr.net/npm/tinymce@5.3.1/skins/ui/oxide-dark/skin.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
```

The plugin will download and store a local cache and change the output to:
```html
<link href="https://example.com/wp-content/cache/wpdepm/wp-styles/FA1A0EE51F53A9817856ABF569D1CBBD.css" rel="stylesheet">
<script src="https://example.com/wp-content/cache/wpdepm/wp-scripts/4A5ED3131C7263AF45B0E6B9DEE5F65F.js"></script>
```
## Inline
Also has the ability to inline the asset contents to reduce requests, for example instead of:
```html
<link href="https://cdn.jsdelivr.net/npm/tinymce@5.3.1/skins/ui/oxide-dark/skin.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
```

The plugin will download and store a local cache and change the output to:
```html
<style>.tox{box-sizing:content-box;color:#2a3746;cursor:auto;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;font-size:16px;font-style:normal;font-weight:400;}...</style>
<script>!function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=e.document?t(e,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return t(e)}:t(e)}...</script>
```

# Objects
## Asset
An asset is a single resource, taken from the core WordPress dependency enqueuing functions.

### Properties
All properties can be returned using their respective method e.g. `$asset->source()` or `$asset->version()`.
| Key | Type | Description |
|--|--|--|
| `type` | String | This is the name of the WordPress class the data was pulled from. Either WP_Scripts or WP_Styles. |
| `handle` | String | The handle give when the asset was registered. |
| `source` | String | The URI used when loading the asset. |
| `version` | ?String | The version string appended to the URI. |
| `args` | Array\|String | For things like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'. |
| `extras` | ?Array | Includes things like [localized script](https://developer.wordpress.org/reference/functions/wp_localize_script/) data. |


# Hooks and filters

## `wpdepm/asset/property`
Filter all property values whenever they are used.
```php
add_filter( 'wpdepm/asset/property', function( mixed $value, string $property, WPDEPM\Core\Asset $asset ) {
	// Force https on source url
	if ( $property === 'source' ) {
		$value = set_url_scheme( $value, 'https' );
	}
	return $value;
}, 10, 3 );
```

## `wpdepm/asset/property/{key}`
Filter a specific property value whenever it is used.
```php
add_filter( 'wpdepm/asset/property/source', function( mixed $value, WPDEPM\Core\Asset $asset ) {
	// Force https on source url
	if ( $property === 'source' ) {
		$value = set_url_scheme( $value, 'https' );
	}
	return $value;
}, 10, 2 );
```

## `asset/replace`
Filter each tag output when replacing on the frontend regardless of output type.
```php
add_filter( 'wpdepm/asset/replace', function( string $tag, WPDEPM\Core\Asset $asset ) {
	// Add the asset hash as a data attribute on the tag
	return preg_replace( '/^<([^\s]+)/', '<$1 data-wpdepm-hash="' . $asset->hash() . '"', $tag );
}, 10, 2 );
```

## `asset/replace/source`
Apply this filter to the output tag when cached but not inline.
```php
add_filter( 'wpdepm/asset/replace/inline', function( string $tag, WPDEPM\Core\Asset $asset ) {
	// Add the asset hash as a data attribute on the tag
	return preg_replace( '/^<([^\s]+)/', '<$1 data-wpdepm-hash="' . $asset->hash() . '"', $tag );
}, 10, 2 );
```

## `asset/replace/inline`
Apply this filter to the output tag when cached but and contents are printed inline.
```php
add_filter( 'wpdepm/asset/replace/inline', function( string $tag, WPDEPM\Core\Asset $asset ) {
	// Replace any google.com references in the inline output
	return str_replace( 'https://google.com/', 'http://example.com/', $tag );
}, 10, 2 );
```
