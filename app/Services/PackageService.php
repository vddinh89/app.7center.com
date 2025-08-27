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

namespace App\Services;

use App\Http\Resources\EntityCollection;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use Illuminate\Http\JsonResponse;

class PackageService extends BaseService
{
	/**
	 * List packages
	 *
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntries(array $params = []): JsonResponse
	{
		$locale = config('app.locale');
		$perPage = getNumberOfItemsPerPage('packages', $params['perPage'] ?? null, $this->perPage);
		$page = (int)($params['page'] ?? 1);
		$embed = getCommaSeparatedStrAsArray($params['embed'] ?? []);
		$sort = $params['sort'] ?? [];
		$packageType = $params['packageType'] ?? null;
		
		$isPromoting = ($packageType == 'promotion');
		$isSubscripting = ($packageType == 'subscription');
		
		abort_if((!$isPromoting && !$isSubscripting), 400, 'Package type not found.');
		
		// Cache control
		$this->updateCachingParameters($params);
		
		// Cache ID
		$cacheEmbedId = !empty($embed) ? '.embed.' . implode(',', $embed) : '';
		$cacheFiltersId = '.filters.' . $packageType;
		$cachePageId = '.page.' . $page . '.of.' . $perPage;
		$cacheId = 'packages.' . $cacheEmbedId . $cacheFiltersId . $cachePageId . $locale;
		
		// Cached Query
		$packages = cache()->remember($cacheId, $this->cacheExpiration, function () use ($embed, $isPromoting, $isSubscripting, $sort) {
			$packages = Package::query();
			
			$packages->when($isPromoting, fn ($query) => $query->promotion());
			$packages->when($isSubscripting, fn ($query) => $query->subscription());
			
			$packages->applyCurrency();
			
			if (in_array('currency', $embed)) {
				$packages->with('currency');
			}
			
			// Sorting
			$packages = $this->applySorting($packages, ['lft'], $sort);
			
			return $packages->get();
		});
		
		// Reset caching parameters
		$this->resetCachingParameters();
		
		$resourceCollection = new EntityCollection(PackageResource::class, $packages, $params);
		
		$message = ($packages->count() <= 0) ? t('no_packages_found') : null;
		
		return apiResponse()->withCollection($resourceCollection, $message);
	}
	
	/**
	 * Get package
	 *
	 * @param int $id
	 * @param array $params
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getEntry(int $id, array $params = []): JsonResponse
	{
		$embed = getCommaSeparatedStrAsArray($params['embed'] ?? []);
		
		// Cache control
		$this->updateCachingParameters($params);
		
		// Cache ID
		$cacheEmbedId = !empty($embed) ? '.embed.' . implode(',', $embed) : '';
		$cacheId = 'package.id.' . $id . '.' . $cacheEmbedId . config('app.locale');
		
		// Cached Query
		$package = cache()->remember($cacheId, $this->cacheExpiration, function () use ($id, $embed) {
			$package = Package::query()->where('id', $id);
			
			if (in_array('currency', $embed)) {
				$package->with('currency');
			}
			
			return $package->first();
		});
		
		// Reset caching parameters
		$this->resetCachingParameters();
		
		abort_if(empty($package), 404, t('package_not_found'));
		
		$package->setLocale(config('app.locale'));
		
		$resource = new PackageResource($package, $params);
		
		return apiResponse()->withResource($resource);
	}
}
