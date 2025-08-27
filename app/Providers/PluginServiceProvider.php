<?php
/*
 * LaraClassifier - Classified Ads Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Providers;

use App\Helpers\Common\Files\FileSys;
use App\Providers\PluginsService\PluginsTrait;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
	use PluginsTrait;
	
	/**
	 * Perform post-registration booting of services.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		// Set routes
		$this->setupRoutes($this->app->router);
		
		// Load the Plugins
		$this->loadPlugins();
	}
	
	/**
	 * Register any package services.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->registerPluginsServiceProviders();
	}
	
	/**
	 * Register the plugins services provider
	 *
	 * @return void
	 */
	private function registerPluginsServiceProviders(): void
	{
		// Load the plugins Services Provider & register them
		$pluginsDirs = glob(config('larapen.core.plugin.path') . '*', GLOB_ONLYDIR);
		if (!empty($pluginsDirs)) {
			foreach ($pluginsDirs as $pluginDir) {
				$plugin = load_plugin(basename($pluginDir));
				if (!empty($plugin)) {
					$this->app->register($plugin->provider);
				}
			}
		}
	}
	
	/**
	 * Autoload the plugins files dynamically
	 *
	 * @return void
	 */
	private function autoloadPlugins(): void
	{
		$pluginsPath = base_path('extras/plugins');
		
		if (!is_dir($pluginsPath)) {
			return;
		}
		
		// Recursively scan the directory for PHP files
		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pluginsPath));
		foreach ($files as $file) {
			if ($file->isFile() && $file->getExtension() === 'php') {
				require_once $file->getPathname();
			}
		}
	}
	
	/**
	 * Define the global routes for the plugins.
	 *
	 * NOTE:
	 * Prevent browser HTTP error like "net : Failed to load resource: net::ERR_SPDY_PROTOCOL_ERROR" on Chrome.
	 * The problem was that web hosting adds HTTP header Content-Encoding: gzip for all the PHP content
	 * even when the 'Content-Type: image/jpeg' is in the output from that script.
	 * For the hotfix I added HTTP header 'Content-Encoding: none' into that script. And it worked.
	 * But now I am asking web hosting provider to not add the wrong header if 'Content-Type: image/jpeg' is present. At HTTPS it makes sense.
	 *
	 * @param \Illuminate\Routing\Router $router
	 */
	private function setupRoutes(Router $router): void
	{
		// Public - Images
		Route::get('plugins/{pluginName}/images/{filename}', function ($pluginName, $filename) {
			$path = plugin_path($pluginName, 'public/images/' . $filename);
			if (File::exists($path)) {
				$type = File::mimeType($path);
				
				return response()->file($path, [
					'Content-Type'     => $type,
					'Content-Encoding' => 'none',
				]);
			}
			
			$message = 'Image not found in the "' . $pluginName . '" plugin.';
			abort(404, $message);
		})->where('pluginName', '[a-z0-9]+');
		
		// Public - Assets
		Route::get('plugins/{pluginName}/assets/{type}/{file}', function ($pluginName, $type, $file) {
			$path = plugin_path($pluginName, 'public/assets/' . $type . '/' . $file);
			if (File::exists($path)) {
				if ($type == 'js' && FileSys::getPathInfoExtension($file) != 'css') {
					return response()->file($path, [
						'Content-Type'     => 'application/javascript',
						'Content-Encoding' => 'none',
					]);
				} else {
					return response()->file($path, [
						'Content-Type'     => 'text/css',
						'Content-Encoding' => 'none',
					]);
				}
			}
			
			$message = 'Asset not found in the "' . $pluginName . '" plugin.';
			abort(404, $message);
		})->where('pluginName', '[a-z0-9]+')
			->where('type', '[^/]*');
	}
}
