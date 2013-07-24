<?php
return array(
    /*
	|--------------------------------------------------------------------------
	| CDN base url.
    | For local development, get resources from the same server as is hosting
    | the site itself by specifying "" here. To use a caching proxy like
    | cloudfront on other environments, create an environment-specific config
    | file and specify the base URL to be prepended to asset URLs here, such
    | as "//abc0defgh1ij2.cloudfront.net" (omit the protocol so that http or
    | https will be selected automatically by the browser; and omit the
    | trailing slash because it's part of the asset URL that will be appended.)
	|--------------------------------------------------------------------------
	*/
    "cdn" => '',

    /*
    |--------------------------------------------------------------------------
    | Cached MD5 expiry
    |--------------------------------------------------------------------------
    |
    | Cache MD5 hashes of resources for this many minutes.
    |
    | Specify 0 not to cache at all.
    |
    */
    'expiry' => 0,

    /*
    |--------------------------------------------------------------------------
    | Path maps for location assets in alternative locations
    |--------------------------------------------------------------------------
    |
    | This is useful if you have a .htaccess file rewriting URLs.
    |
    | Provide a hashmap of user-facing URL base paths to their corresponding
    | filesystem paths, for example '/assets' => 'path/to/assets',
    |
    */
    'path_maps' => array(
    ),

);