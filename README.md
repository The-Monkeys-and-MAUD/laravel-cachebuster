![The Monkeys](http://www.themonkeys.com.au/img/monkey_logo.png)

Laravel Cachebuster
===================

Adds MD5 hashes to the URLs of your application's assets, so when they change, their URL changes. URLs contained in your
css files are transformed automatically; other URLs (such as those referenced via `<script>`, `<link>` and `<img>` tags)
are easy to transform too via a helper function in your blade templates.

Also supports adding a CDN proxy prefix to your asset URLs, to quickly and easily add the performance and scalability
of a transparent CDN such as [Cloudfront](http://aws.amazon.com/cloudfront/) to your app.

Installation
------------
To get the latest version of cachebuster simply require it in your composer.json file.

```JSON
"themonkeys/cachebuster": "dev-master"
```

You'll then need to run `composer install` to download it and have the autoloader updated.

Once cachebuster is installed you need to register the service provider with the application.
Open up `app/config/app.php` and find the `providers` key.

```php
'providers' => array(
    'Themonkeys\Cachebuster\CachebusterServiceProvider',
)
```

The package ships with a facade which provides a concise static syntax for encoding your URLs. You can register the
facade via the `aliases` key of your `app/config/app.php` file.

```php
'aliases' => array(

    'Bust' => 'Themonkeys\Cachebuster\Cachebuster'

)
```

Add the following to your .htaccess file **before** the Laravel rewrite rule:

```ApacheConf
# ------------------------------------------------------------------------------
# | Remove cachebuster hash from request URLs if present                       |
# ------------------------------------------------------------------------------
<IfModule mod_rewrite.c>
    RewriteRule ^(.*)-[0-9a-f]{32}(\.(.*))$ $1$2 [DPI]
</IfModule>
```

And add the following to your .htaccess file **after** the Laravel rewrite rule:

```ApacheConf
# ------------------------------------------------------------------------------
# | Allow Laravel to pre-process the css to add cachebusters to image urls     |
# ------------------------------------------------------------------------------
<IfModule mod_rewrite.c>
    RewriteCond %{REQUEST_URI} !^/index.php
    RewriteRule ^(.*\.css)$ index.php [L]
</IfModule>
```

Finally, add this to your `app/routes.php` file:

```php
Route::get('/css/{filename}.css', function($filename) {
    return Bust::css("/css/$filename.css");
});
```

> Note: you might want to adjust the URL pattern if your css files are located elsewhere than `/css/`.

Usage
-----

Wherever you specify an asset path in your blade templates, use `Bust::url()` to transform the path. For example, a
script tag like this...

```HTML
<script src="{{ Bust::url('/js/main.js') }}"></script>
```

...will look like this to your users:

```HTML
<script src="/js/main-a09b64644df96f807a0db134d27912bf.js"></script>
```

Or if you've configured a CDN it might look like:

```HTML
<script src="//a1bc23de4fgh5i.cloudfront.net/js/main-a09b64644df96f807a0db134d27912bf.js"></script>
```

The same goes for `<img>` tags:

```HTML
<img src="{{ Bust::url('/img/spacer.gif') }}" alt="">
```

will look like this to your users:

```HTML
<img src="/img/spacer-5e416a75e3af86e42b1a3bc8efc33ebc.gif" alt="">
```

The final piece of the puzzle is your css:

```HTML
<link rel="stylesheet" href="{{ Bust::url('/css/main.css') }}">
```

comes out looking like this:

```HTML
<link rel="stylesheet" href="/css/main-f75168d5f53c7a09d9a08840d7b5a5ec.css">
```

Some real magic happens here - all the URLs inside your CSS file (images, fonts etc.) are automatically passed through
the cachebuster, so they now have hashes in their filenames too. Open the CSS file in your browser and have a look!

### Absolute URLs

Sometimes you might want to specify an absolute URL, for example in an OpenGraph meta tag. That's easy:

```HTML
<meta property="og:image" content="{{ Bust::url('/img/share-thumbnail.jpg') }}" />
```

might come out as:

```HTML
<meta property="og:image" content="http://yourhost/img/share-thumbnail-2a7d7b5a4401ef3176565dffcd59b282.png" />
```

This uses Laravel's built-in URL generators so the URLs will be generated depending on your environment.


Configuration
-------------

To configure the package, you can use the following command to copy the configuration files to
`app/config/packages/themonkeys/cachebuster`.

```sh
php artisan config:publish themonkeys/cachebuster
```

Or you can just create a new file in that folder and only override the settings you need.

The settings themselves are documented inside `config.php`.


