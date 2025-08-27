<?php

namespace App\Exceptions\Handler\Traits;

use Illuminate\Support\Facades\View;

trait ExceptionTrait
{
	/**
	 * Get theme error view (dot-separated) path
	 *
	 * @param string|null $viewName
	 * @return string|null
	 */
	protected function getThemeErrorViewPath(?string $viewName = null): ?string
	{
		/*
		 * Set default theme errors views directory in the possible views base directories array
		 * NOTE: The custom "views/errors/" (i.e. errors) directory that auto-discovers errors views
		 *       by their status code should not be added as errors directory path below.
		 */
		$viewPathDirs = [
			'front.errors',
		];
		
		/*
		 * Create a custom view namespace to ensure Laravel uses the theme's error directory instead
		 * of the default "resources/views/errors" directory. This allows us to reference error views with
		 * "theme::errors." rather than "errors.", avoiding potential confusion with the default view hint for "resources/views/errors".
		 *
		 * Next, prepend the theme's error views directory to the $viewPathDirs array.
		 */
		$themePath = base_path('extras/themes/customized/views');
		if (is_dir($themePath)) {
			View::addNamespace('customized', $themePath);
			array_unshift($viewPathDirs, 'customized::errors');
		}
		
		// Use the first view found
		$viewPath = null;
		foreach ($viewPathDirs as $viewPathDir) {
			$tmpViewPath = "{$viewPathDir}.{$viewName}";
			if (view()->exists($tmpViewPath)) {
				$viewPath = $tmpViewPath;
				break;
			}
		}
		
		return $viewPath;
	}
}
