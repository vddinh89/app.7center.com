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

namespace App\Models\Setting;

/*
 * settings.footer.option
 */

class FooterSetting
{
	public static function getValues($value, $disk)
	{
		$value = is_array($value) ? $value : [];
		
		$iosAppUrl = config('settings.other.ios_app_url');
		$androidAppUrl = config('settings.other.android_app_url');
		
		$defaultValue = [
			'hide_payment_plugins_logos' => '1',
			'ios_app_url'                => $iosAppUrl ?? null,
			'android_app_url'            => $androidAppUrl ?? null,
		];
		
		return array_merge($defaultValue, $value);
	}
	
	public static function setValues($value, $setting)
	{
		return $value;
	}
	
	public static function getFields($diskName): array
	{
		$fields = [
			[
				'name'    => 'hide_links',
				'label'   => trans('admin.Hide Links'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'hide_payment_plugins_logos',
				'label'   => trans('admin.Hide Payment Plugins Logos'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'mobile_apps_title',
				'type'  => 'custom_html',
				'value' => trans('admin.mobile_apps_title'),
			],
			[
				'name'    => 'ios_app_url',
				'label'   => trans('admin.app_store_label'),
				'type'    => 'text',
				'hint'    => trans('admin.available_on_app_store_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'android_app_url',
				'label'   => trans('admin.google_play_label'),
				'type'    => 'text',
				'hint'    => trans('admin.available_on_google_play_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'powered_by_title',
				'type'  => 'custom_html',
				'value' => trans('admin.powered_by_title'),
			],
			[
				'name'    => 'hide_powered_by',
				'label'   => trans('admin.hide_powered_by_label'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-6 mt-3',
				],
			],
			[
				'name'    => 'powered_by_text',
				'label'   => trans('admin.powered_by_text_label'),
				'type'    => 'text',
				'wrapper' => [
					'class' => 'col-md-6 powered-by-field',
				],
				'newline' => true,
			],
			
			[
				'name'  => 'tracking_title',
				'type'  => 'custom_html',
				'value' => trans('admin.tracking_title'),
			],
			[
				'name'       => 'tracking_code',
				'label'      => trans('admin.tracking_code_label'),
				'type'       => 'textarea',
				'attributes' => [
					'rows' => '15',
				],
				'hint'       => trans('admin.tracking_code_hint'),
				'wrapper'    => [
					'class' => 'col-md-12',
				],
			],
		];
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
}
