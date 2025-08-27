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

namespace App\Models\Traits;

trait MetaTagTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function getPageHtml()
	{
		$entries = self::getDefaultPages();
		
		// Get Page Name
		$out = $this->page;
		if (isset($entries[$this->page])) {
			$url = urlGen()->adminUrl('meta_tags/' . $this->id . '/edit');
			$out = '<a href="' . $url . '">' . $entries[$this->page] . '</a>';
		}
		
		return $out;
	}
	
	// ===| OTHER METHODS |===
	
	public static function getDefaultPages(): array
	{
		return [
			'home'           => 'Homepage',
			'search'         => 'Search (Default)',
			'searchCategory' => 'Search (Category)',
			'searchLocation' => 'Search (Location)',
			'searchProfile'  => 'Search (Profile)',
			'searchTag'      => 'Search (Tag)',
			'listingDetails' => 'Listing Details',
			'register'       => 'Register',
			'login'          => 'Login',
			'create'         => 'Listings Creation',
			'countries'      => 'Countries',
			'contact'        => 'Contact',
			'sitemap'        => 'Sitemap',
			'password'       => 'Password',
			'pricing'        => 'Pricing',
			'staticPage'     => 'Page (Static)',
		];
	}
}
