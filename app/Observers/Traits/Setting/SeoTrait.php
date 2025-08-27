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

namespace App\Observers\Traits\Setting;

use App\Helpers\Common\PhpArrayFile;
use Illuminate\Support\Facades\File;
use Throwable;

trait SeoTrait
{
	/**
	 * Updating
	 *
	 * @param $setting
	 * @param $original
	 */
	public function seoUpdating($setting, $original)
	{
		// Remove the "public/robots.txt" file (It will be re-generated automatically)
		if ($this->checkIfRobotsTxtFileCanBeRemoved($setting, $original)) {
			$this->removeRobotsTxtFile($setting, $original);
		}
		
		// Regenerate the "config/routes.php" file
		if ($this->checkIfDynamicRoutesFileCanBeRegenerated($setting, $original)) {
			$this->regenerateDynamicRoutes($setting);
		}
	}
	
	/**
	 * @param $setting
	 * @param $original
	 * @return bool
	 */
	private function checkIfRobotsTxtFileCanBeRemoved($setting, $original): bool
	{
		if (
			array_key_exists('robots_txt', $setting->value)
			|| array_key_exists('robots_txt_sm_indexes', $setting->value)
		) {
			$robotsTxt = $setting->value['robots_txt'] ?? '';
			$robotsTxtOld = $original['value']['robots_txt'] ?? '';
			
			$robotsTxtSmIndexes = $setting->value['robots_txt_sm_indexes'] ?? null;
			$robotsTxtSmIndexesOld = $original['value']['robots_txt_sm_indexes'] ?? null;
			
			return (
				(md5($robotsTxt) != md5($robotsTxtOld))
				|| ($robotsTxtSmIndexes != $robotsTxtSmIndexesOld)
			);
		}
		
		return false;
	}
	
	/**
	 * Remove the robots.txt file (It will be re-generated automatically)
	 *
	 * @param $setting
	 * @param $original
	 */
	private function removeRobotsTxtFile($setting, $original): void
	{
		$robotsFile = public_path('robots.txt');
		if (File::exists($robotsFile)) {
			File::delete($robotsFile);
		}
	}
	
	/**
	 * @param $setting
	 * @param $original
	 * @return bool
	 */
	private function checkIfDynamicRoutesFileCanBeRegenerated($setting, $original): bool
	{
		if (
			array_key_exists('listing_permalink', $setting->value)
			|| array_key_exists('listing_permalink_ext', $setting->value)
			|| array_key_exists('multi_country_urls', $setting->value)
		) {
			$listingPermalink = $setting->value['listing_permalink'] ?? null;
			$listingPermalinkOld = $original['value']['listing_permalink'] ?? null;
			
			$listingPermalinkExt = $setting->value['listing_permalink_ext'] ?? null;
			$listingPermalinkExtOld = $original['value']['listing_permalink_ext'] ?? null;
			
			$multiCountryUrls = $setting->value['multi_country_urls'] ?? null;
			$multiCountryUrlsOld = $original['value']['multi_country_urls'] ?? null;
			
			return (
				($listingPermalink != $listingPermalinkOld)
				|| ($listingPermalinkExt != $listingPermalinkExtOld)
				|| ($multiCountryUrls != $multiCountryUrlsOld)
			);
		}
		
		return false;
	}
	
	/**
	 * Regenerate the "config/routes.php" file
	 *
	 * @param null $setting
	 * @return bool
	 */
	private function regenerateDynamicRoutes($setting = null): bool
	{
		$doneSuccessfully = true;
		
		try {
			// Update in live the config vars related the Settings below before saving them.
			if (isset($setting->value)) {
				if (array_key_exists('listing_permalink', $setting->value)) {
					config()->set('settings.seo.listing_permalink', $setting->value['listing_permalink']);
				}
				if (array_key_exists('listing_permalink_ext', $setting->value)) {
					config()->set('settings.seo.listing_permalink_ext', $setting->value['listing_permalink_ext']);
				}
				if (array_key_exists('multi_country_urls', $setting->value)) {
					// Check the Domain Mapping plugin
					if (config('plugins.domainmapping.installed')) {
						config()->set('settings.seo.multi_country_urls', false);
					} else {
						config()->set('settings.seo.multi_country_urls', $setting->value['multi_country_urls']);
					}
				}
			}
			
			// Get current values of "config/larapen/routes.php" (Original version)
			$origRoutes = PhpArrayFile::getFileContent(config_path('larapen/routes.php'));
			
			// Create or Update the "config/routes.php" file
			$filePath = config_path('routes.php');
			PhpArrayFile::writeFile($filePath, $origRoutes);
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$doneSuccessfully = false;
		}
		
		return $doneSuccessfully;
	}
}
