<?php namespace Themonkeys\Cachebuster;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;

class CachebusterServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('themonkeys/cachebuster');
        $rc = new \ReflectionClass($this->app);
        if ($rc->hasMethod('close')) {
            $this->app->close('cachebuster.StripSessionCookiesFilter');
        }
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app['cachebuster.url'] = $this->app->share(function($app) {
            return new AssetURLGenerator();
        });
        $this->app['cachebuster.StripSessionCookiesFilter'] = $this->app->share(function($app) {
            return new StripSessionCookiesFilter($app);
        });
        $rc = new \ReflectionClass($this->app);
        if ($rc->hasMethod('middleware')) {
            $this->app->middleware(function($app) {
                return new SessionCookiesStripper($app, App::make('cachebuster.StripSessionCookiesFilter'));
            });
        }
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}