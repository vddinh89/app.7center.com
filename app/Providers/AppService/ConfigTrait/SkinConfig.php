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

namespace App\Providers\AppService\ConfigTrait;

trait SkinConfig
{
	private function updateSkinConfig(?array $settings = []): void
	{
		$data = [];
		
		// Get Pre-Defined Skins By Color
		$skinsByColor = collect(getCachedReferrerList('skins'))
			->mapWithKeys(fn ($item, $key) => [$key => $item['color']])
			->toArray();
		
		// Update The Custom Skin Color
		if (is_array($skinsByColor)) {
			$skinsByColor['custom'] = data_get($settings, 'custom_skin_color');
		}
		
		// Get Selected Skin
		$selectedSkin = getFrontSkin(request()->input('skin'));
		
		// Generate CSS Colors From The Selected Skin Color
		$primaryBgColor = null;
		if (!empty($skinsByColor[$selectedSkin])) {
			// Primary Color
			$primaryBgColor = $skinsByColor[$selectedSkin];
			$primaryColor = getContrastColor($primaryBgColor);
			// ---
			$primaryBgColor10 = colourBrightness($primaryBgColor, 0.1); // button:hover
			$primaryBgColor50 = colourBrightness($primaryBgColor, 0.5); // button:focus
			$primaryBgColor80 = colourBrightness($primaryBgColor, 0.8); // svg-map:bg
			
			// Primary Link Color
			$primaryBgColor10d = colourBrightness($primaryBgColor, -0.1); // a
			$primaryBgColor20d = colourBrightness($primaryBgColor, -0.2); // a:hover | .btn-gradient
			
			// Primary Dark Color
			$primaryDarkBgColor = colourBrightness($primaryBgColor, -0.5);
			$primaryDarkColor = getContrastColor($primaryDarkBgColor);
			// ---
			$primaryDarkBgColor10 = colourBrightness($primaryDarkBgColor, 0.1); // button:hover
			$primaryDarkBgColor50 = colourBrightness($primaryDarkBgColor, 0.5); // button:focus
			
			// Data To Share!
			$data['selectedSkin'] = $selectedSkin;
			
			$data['primaryBgColor'] = $primaryBgColor;
			$data['primaryColor'] = $primaryColor;
			$data['primaryBgColor10'] = $primaryBgColor10;
			$data['primaryBgColor50'] = $primaryBgColor50;
			$data['primaryBgColor80'] = $primaryBgColor80;
			
			$data['primaryBgColor10d'] = $primaryBgColor10d;
			$data['primaryBgColor20d'] = $primaryBgColor20d;
			
			$data['primaryDarkBgColor'] = $primaryDarkBgColor;
			$data['primaryDarkColor'] = $primaryDarkColor;
			$data['primaryDarkBgColor10'] = $primaryDarkBgColor10;
			$data['primaryDarkBgColor50'] = $primaryDarkBgColor50;
		}
		
		view()->share($data);
	}
}
