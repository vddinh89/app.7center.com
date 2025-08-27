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

namespace App\Observers;

use App\Helpers\Services\Lang\LangManager;
use App\Models\Language;
use App\Observers\Traits\LanguageTrait;
use Throwable;

class LanguageObserver extends BaseObserver
{
	use LanguageTrait;
	
	/**
	 * Listen to the Entry created event.
	 *
	 * @param Language $language
	 * @return void
	 */
	public function created(Language $language)
	{
		// Check Demo Website
		$this->isDemo();
		
		// Get the current Default Language
		$defaultLang = Language::where('default', 1)->first();
		
		if (!empty($defaultLang)) {
			$manager = new LangManager();
			
			// Copy the default language files
			$manager->copyFiles($defaultLang->code, $language->code);
		}
	}
	
	/**
	 * Listen to the Entry updating event.
	 *
	 * @param Language $language
	 * @return bool
	 */
	public function updating(Language $language): bool
	{
		// Check Demo Website
		$this->isDemo();
		
		// Get the original object values
		$original = $language->getOriginal();
		
		// Set default language
		if ($language->default != $original['default']) {
			if ($language->default == 1 || $language->default == 'on') {
				// The current language is updated as default language
				
				// Set default language
				self::setDefaultLanguage($language->code);
				
			} else {
				// The current language is updated as non-default language
				
				// Make sure a default language is set,
				// If not don't perform the update and go back.
				$existingDefaultLang = Language::where('default', 1)->where('code', '!=', $language->code);
				if ($existingDefaultLang->count() <= 0) {
					$msg = trans('admin.The app requires a default language');
					notification($msg, 'warning');
					
					return false;
				}
				
			}
		} else {
			if ($language->default == 1 && $language->active != 1) {
				$msg = trans('admin.You cannot disable the default language');
				notification($msg, 'warning');
				
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Language $language
	 * @return bool
	 */
	public function deleting(Language $language): bool
	{
		// Check Demo Website
		$this->isDemo();
		
		// Don't delete the default language
		if ($language->code == config('appLang.code')) {
			$msg = trans('admin.You cannot delete the default language');
			notification($msg, 'warning');
			
			return false;
		}
		
		// Forgetting all DB translations for a specific locale
		$this->forgetAllTranslations($language->code);
		
		// Remove all language files
		$manager = new LangManager();
		$manager->removeFiles($language->code);
		
		return true;
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Language $language
	 * @return void
	 */
	public function saved(Language $language)
	{
		// Removing Entries from the Cache
		$this->clearCache($language);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Language $language
	 * @return void
	 */
	public function deleted(Language $language)
	{
		// Removing Entries from the Cache
		$this->clearCache($language);
	}
	
	
	// PRIVATE METHODS
	
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $language
	 * @return void
	 */
	private function clearCache($language): void
	{
		try {
			cache()->flush();
		} catch (Throwable $e) {
		}
	}
	
	/**
	 * Check Demo Website
	 *
	 * @return bool|\Illuminate\Http\RedirectResponse
	 */
	private function isDemo()
	{
		if (isDemoDomain()) {
			notification(t('demo_mode_message'), 'error');
			
			return back();
		}
		
		return false;
	}
}
