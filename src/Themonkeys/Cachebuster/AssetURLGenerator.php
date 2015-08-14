<?php namespace Themonkeys\Cachebuster;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;

/**
 * Knows how to generate the URL of a static asset (including a cachebuster hash) and generates absolute CDN URLs if
 * configured to do so.
 */
class AssetURLGenerator
{
    /**
     * Returns a full URL to the given asset. For example, on production passing '/css/main.css' may return
     * http://a1bc23de4fgh5i.cloudfront.net/css/main-8b50d865ef2e3469be477e2745c888c5.css
     *
     * @param $asset
     */
    public function url($asset, $absolute = false) {
        $url = $this->cachebusted($asset);

        $base = Config::get("cachebuster.cdn");
        if ($base === '' && $absolute) {
            $base = URL::to('/');
        }
        return $base . $url;
    }

    public function cachebusted($asset) {
        $url = $asset;

        if (Config::get("cachebuster.enabled")) {
            $md5 = $this->md5($url);

        
            if ($md5) {
                $parts = pathinfo($url);
                $dirname = ends_with($parts['dirname'], '/') ? $parts['dirname'] : $parts['dirname'] . '/';
                $url = "{$dirname}{$parts['filename']}-$md5.{$parts['extension']}";
            }
        }

        return $url;
    }

    public function md5($asset) {


        $expiry = Config::get('cachebuster.expiry');
        $self = $this;
        $calculate = function() use($asset, $self) {
            $path = public_path() . DIRECTORY_SEPARATOR . $self->map_path($asset);
            if (File::exists($path) && File::isFile($path)) {
                return md5_file($path);
            } else {
                throw new \Exception("Asset '$path' not found");
            }
        };
        if ($expiry) {
            return Cache::remember('url.md5.' . $asset, $expiry, $calculate);
        } else {
            return $calculate();
        }
    }

    /**
     * Loads the css file at the given URL, replaces all urls within it to cachebusted CDN urls,
     * and returns the resulting css source code as a Response object suitable for the Laravel router.
     * @param $url
     */
    public function css($url) {
        if (Session::isStarted() && Session::has('flash.old')) {
            Session::reflash(); // in case any flash data would have been lost here
        }

        // strip out cachebuster from the url, if necessary
        $url = $this->map_path($url);
        $public = public_path();
        $path = $public . DIRECTORY_SEPARATOR . $url;
        if (File::exists($path)) {
            $source = File::get($path);
            $base = realpath(dirname($path));

            // search for url('*') and replace with processed url
            $self = $this;
            if (Config::get("cachebuster.enabled")) {
                $source = preg_replace_callback('/url\\((["\']?)([^\\)\'"\\?]+)((\\?[^\\)\'"]+)?[\'"]?)\\)/', function ($matches) use ($base, $public, $self) {
                    $url = $matches[2];
                    $qs = $matches[3];

                    // determine the absolute path of the given URL (resolve ../ etc against the path to the css file)
                    if (substr($url, 0, 1) != '/') {
                        $abs = realpath($base . '/' . $url);
                        if (File::exists($abs) && starts_with($abs, $public)) {
                            $url = substr($abs, strlen($public));
                        }
                    }
                    // if the url is absolute, we can process; otherwise, have to leave it alone
                    $replacement = $matches[0];
                    if (substr($url, 0, 1) == '/') {
                        $replacement = 'url(' . $matches[1] . $self->url($url) . $matches[3] . ')';
                    }
                    return $replacement;

                }, $source);
            }

            return Response::make(
                $source,
                200,
                array(
                    'Content-Type' => 'text/css',
                )
            );

        } else {
            App::abort(404, 'Page not found');
        }
    }

    protected function strip_cachebuster($url) {
        return preg_replace('/-[0-9a-f]{32}\./', '.', $url);
    }

    public function map_path($url) {
        $url = '/' . preg_replace(';(^/+|#.*$);', '', $this->strip_cachebuster($url));
        foreach (Config::get('cachebuster.path_maps') as $from => $to) {
            if (starts_with($url, $from)) {
                $part = substr($url, strlen($from));
                if (starts_with($part, '/')) {
                    $part = substr($part, 1);
                }
                if (ends_with($to, '/')) {
                    $to = substr($to, 0, strlen($to) - 1);
                }
                $url = $to . '/' . $part;
            }
        }
        if (starts_with($url, '/')) {
            $url = substr($url, 1);
        }
        return $url;
    }
}
