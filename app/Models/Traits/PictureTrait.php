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


trait PictureTrait
{
	// ===| ADMIN PANEL METHODS |===
	
	public function getFilePathHtml(): string
	{
		$defaultPictureUrl = thumbParam(config('larapen.media.picture'))->url();
		$imgUrl = $this->file_url_small ?? $defaultPictureUrl;
		
		return '<img src="' . $imgUrl . '" class="img-rounded" style="width:auto; max-height:90px;">';
	}
	
	public function getPostTitleHtml(): string
	{
		if (!empty($this->post)) {
			$postUrl = dmUrl($this->post->country_code, urlGen()->postPath($this->post));
			
			return '<a href="' . $postUrl . '" target="_blank">' . $this->post->title . '</a>';
		} else {
			return 'no-link';
		}
	}
	
	public function getCountryHtml(): string
	{
		$countryCode = $this?->post?->country_code ?? '--';
		$countryName = $this?->post?->country?->name ?? null;
		$countryName = (!empty($countryName)) ? $countryName : $countryCode;
		$countryFlagUrl = $this?->post?->country_flag_url ?? null;
		
		if (!empty($countryFlagUrl)) {
			$out = '<a href="' . dmUrl($countryCode, '/', true, true) . '" target="_blank">';
			$out .= '<img src="' . $countryFlagUrl . '" data-bs-toggle="tooltip" title="' . $countryName . '">';
			$out .= '</a>';
			
			return $out;
		} else {
			return $countryCode;
		}
	}
	
	public function editPostButton($xPanel = false): string
	{
		$out = '';
		
		if (!empty($this->post)) {
			$url = urlGen()->adminUrl('posts/' . $this->post->id . '/edit');
			
			$msg = trans('admin.Edit the listing of this picture');
			$tooltip = ' data-bs-toggle="tooltip" title="' . $msg . '"';
			
			$out .= '<a class="btn btn-xs btn-light" href="' . $url . '"' . $tooltip . '>';
			$out .= '<i class="fa-regular fa-pen-to-square"></i> ';
			$out .= mb_ucfirst(trans('admin.Edit the listing'));
			$out .= '</a>';
		}
		
		return $out;
	}
	
	// ===| OTHER METHODS |===
}
