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

namespace App\Http\Controllers\Web\Front\Search;

use Larapen\LaravelMetaTags\Facades\MetaTag;
use Throwable;

class CategoryController extends BaseController
{
	/**
	 * Category URL
	 * Pattern: (countryCode/)category/category-slug
	 * Pattern: (countryCode/)category/parent-category-slug/category-slug
	 *
	 * @param $countryCode
	 * @param $catSlug
	 * @param $subCatSlug
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index($countryCode, $catSlug, $subCatSlug = null)
	{
		// Check if the multi-country site option is enabled
		if (!isMultiCountriesUrlsEnabled()) {
			$subCatSlug = $catSlug;
			$catSlug = $countryCode;
		}
		
		// Get Posts
		$queryParams = [
			'op' => 'search',
			'c'  => $catSlug,
			'sc' => $subCatSlug,
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = getServiceData($this->postService->getEntries($queryParams));
		
		$apiMessage = data_get($data, 'message');
		$apiResult = data_get($data, 'result');
		$apiExtra = data_get($data, 'extra');
		$preSearch = data_get($apiExtra, 'preSearch');
		
		// Sidebar
		$this->bindSidebarVariables((array)data_get($apiExtra, 'sidebar'));
		
		// Get Titles
		$this->getBreadcrumb($preSearch);
		$this->getHtmlTitle($preSearch);
		
		// Meta Tags
		[$title, $description, $keywords] = $this->getMetaTag($preSearch);
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		MetaTag::set('keywords', $keywords);
		
		// Open Graph
		try {
			$this->og->title($title)->description($description)->type('website');
		} catch (Throwable $e) {
		}
		view()->share('og', $this->og);
		
		// SEO: noindex
		$noIndexCategoriesPermalinkPages = (
			config('settings.seo.no_index_categories')
			&& currentRouteActionContains('Search\CategoryController')
		);
		// Filters (and Orders) on Listings Pages (Except Pagination)
		$noIndexFiltersOnEntriesPages = (
			config('settings.seo.no_index_filters_orders')
			&& currentRouteActionContains('Search\\')
			&& !empty(request()->except(['page']))
		);
		// "No result" Pages (Empty Searches Results Pages)
		$noIndexNoResultPages = (
			config('settings.seo.no_index_no_result')
			&& currentRouteActionContains('Search\\')
			&& empty(data_get($apiResult, 'data'))
		);
		
		return view(
			'front.search.results',
			compact(
				'apiMessage',
				'apiResult',
				'apiExtra',
				'noIndexCategoriesPermalinkPages',
				'noIndexFiltersOnEntriesPages',
				'noIndexNoResultPages'
			)
		);
	}
}
