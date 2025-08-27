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

namespace App\Http\Controllers\Web\Front\Locale\Traits;

use App\Http\Controllers\Web\Front\Traits\Sluggable\CategoryBySlug;
use App\Http\Controllers\Web\Front\Traits\Sluggable\PageBySlug;
use Throwable;

trait TranslateUrlTrait
{
	use CategoryBySlug, PageBySlug;
	
	/**
	 * @param string|null $url
	 * @param string|null $langCode
	 * @param string|null $baseUrl
	 * @return string|null
	 */
	private function translateUrl(?string $url, ?string $langCode, ?string $baseUrl = null): ?string
	{
		$defaultUrl = !empty($baseUrl) ? $baseUrl : url('/');
		$defaultUrl = getAsString($defaultUrl);
		
		try {
			$route = app('router')->getRoutes()->match(request()->create($url, request()->method()));
			if (empty($route)) {
				return $defaultUrl;
			}
			
			$prevUriPattern = $route->uri;
			$prevUriParameters = $route->parameters();
			
			if (str_contains($route->action['controller'], 'Search\CategoryController')) {
				$prevUriParameters = $this->translateRouteUriParametersForCat($prevUriParameters, $langCode);
			}
			if (str_contains($route->action['controller'], 'PageController')) {
				$prevUriParameters = $this->translateRouteUriParametersForPage($prevUriParameters, $langCode);
			}
			
			// Get possible translatable route key
			// $routeKey = array_search($prevUriPattern, trans('routes'));
			$routeKey = array_search($prevUriPattern, config('routes'));
			
			// Non-translatable route
			if (empty($routeKey)) {
				return $url;
			}
			
			// Translatable route
			$requestParams = urlQuery($url)->getParametersExcluding(['from']);
			
			$search = collect($prevUriParameters)
				->mapWithKeys(function ($value, $key) {
					return ['{' . $key . '}' => $key];
				})
				->keys()
				->toArray();
			
			$replace = collect($prevUriParameters)
				->mapWithKeys(function ($value, $key) {
					return [$value => $key];
				})
				->keys()
				->toArray();
			
			// $prevUriPattern = trans('routes.' . $routeKey, [], $langCode);
			$translatedUrl = str_replace($search, $replace, $prevUriPattern);
			
			return urlQuery($translatedUrl)->setParameters($requestParams)->toString();
		} catch (Throwable $e) {
		}
		
		return $defaultUrl;
	}
	
	/**
	 * @param array|null $prevUriParameters
	 * @param string|null $langCode
	 * @return array|null
	 */
	private function translateRouteUriParametersForCat(?array $prevUriParameters, ?string $langCode): ?array
	{
		$countryCode = $prevUriParameters['countryCode'] ?? null;
		$parentCatSlug = $prevUriParameters['catSlug'] ?? null;
		$catSlug = $prevUriParameters['subCatSlug'] ?? null;
		if (empty($catSlug)) {
			$catSlug = $parentCatSlug;
			$parentCatSlug = null;
		}
		
		$cat = $this->getCategoryBySlug($catSlug, $parentCatSlug, $langCode);
		if (!empty($cat)) {
			$cat = $this->getCategoryById(data_get($cat, 'id'), $langCode);
		}
		
		if (!empty($cat)) {
			$prevUriParameters = [
				'countryCode' => $countryCode,
				'catSlug'     => data_get($cat, 'slug'),
			];
			if (!empty($parentCatSlug)) {
				$prevUriParameters = [
					'countryCode' => $countryCode,
					'catSlug'     => data_get($cat, 'parent.slug'),
					'subCatSlug'  => data_get($cat, 'slug'),
				];
			}
		}
		
		return $prevUriParameters;
	}
	
	/**
	 * @param array|null $prevUriParameters
	 * @param string|null $langCode
	 * @return array|null
	 */
	private function translateRouteUriParametersForPage(?array $prevUriParameters, ?string $langCode): ?array
	{
		$slug = $prevUriParameters['slug'] ?? null;
		
		$page = $this->getPageBySlugOrId($slug, $langCode);
		
		if (!empty($page)) {
			$prevUriParameters = ['slug' => data_get($page, 'slug')];
		}
		
		return $prevUriParameters;
	}
}
