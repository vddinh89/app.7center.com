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

use App\Enums\BootstrapColor;
use App\Helpers\Common\Files\Upload;
use Illuminate\Support\Facades\Storage;

/*
 * settings.style.option
 */

class StyleSetting
{
	public static function passedValidation($request)
	{
		$mediaOpPath = 'larapen.media.resize.namedOptions';
		$params = [
			[
				'attribute' => 'body_background_image_path',
				'destPath'  => 'app/logo',
				'width'     => (int)config($mediaOpPath . '.bg-body.width', 2500),
				'height'    => (int)config($mediaOpPath . '.bg-body.height', 2500),
				'ratio'     => config($mediaOpPath . '.bg-body.ratio', '1'),
				'upsize'    => config($mediaOpPath . '.bg-body.upsize', '0'),
				'filename'  => 'body-background-',
			],
		];
		
		foreach ($params as $param) {
			$file = $request->hasFile($param['attribute'])
				? $request->file($param['attribute'])
				: $request->input($param['attribute']);
			
			$request->request->set($param['attribute'], Upload::image($file, $param['destPath'], $param));
		}
		
		return $request;
	}
	
	public static function getValues($value, $disk)
	{
		$value = is_array($value) ? $value : [];
		
		$defaultValue = [
			'skin'              => 'default',
			'page_width'        => '1200',
			'header_full_width' => '0',
			
			'dark_header'                      => '0',
			'header_shadow'                    => '0',
			'header_background_class'          => 'bg-body-tertiary', // bg-body-tertiary
			'header_background_color'          => null, // '#f8f9fA'
			'header_border_bottom_width'       => null, // '1px'
			'header_border_bottom_color'       => null, // '#dee2e6'
			'header_animation'                 => '1',
			'header_fixed_top'                 => '1',
			'header_height_offset'             => 200,
			'fixed_dark_header'                => '0',
			'fixed_header_shadow'              => '1',
			'fixed_header_background_class'    => null, // bg-primary
			'fixed_header_background_color'    => null,
			'fixed_header_border_bottom_width' => null,
			'fixed_header_border_bottom_color' => null,
			'header_highlighted_btn_link'      => 'listingCreationLink',
			'header_highlighted_btn_class'     => 'btn-highlight',
			
			'logo_width'        => '216',
			'logo_height'       => '40',
			'logo_aspect_ratio' => '1',
			
			'dark_footer'             => '1',
			'high_spacing_footer'     => '1',
			'footer_full_width'       => '0',
			'footer_background_color' => null, // '#f8f9fA'
			'footer_border_top_width' => null, // '1px'
			'footer_border_top_color' => null, // '#dee2e6'
			
			'admin_logo_bg'          => 'skin3',
			'admin_navbar_bg'        => 'skin6',
			'admin_sidebar_type'     => 'full',
			'admin_sidebar_bg'       => 'skin5',
			'admin_sidebar_position' => '1',
			'admin_header_position'  => '1',
			'admin_boxed_layout'     => '0',
			'admin_dark_theme'       => '0',
		];
		
		$value = array_merge($defaultValue, $value);
		
		/** @var $disk Storage */
		$filePathList = ['body_background_image_path'];
		foreach ($value as $key => $item) {
			if (in_array($key, $filePathList)) {
				if (empty($item) || !$disk->exists($item)) {
					$value[$key] = $defaultValue[$key] ?? null;
				}
			}
		}
		
		// Append files URLs
		// body_background_image_url
		$bodyBackgroundImage = $value['body_background_image_path'] ?? $value['body_background_image'] ?? null;
		$value['body_background_image_url'] = thumbService($bodyBackgroundImage, false)->resize('bg-body')->url();
		
		return $value;
	}
	
	public static function setValues($value, $setting)
	{
		return $value;
	}
	
	public static function getFields($diskName): array
	{
		// Get Pre-Defined Skins By Name
		$skins = getCachedReferrerList('skins');
		$skinsByName = collect($skins)
			->mapWithKeys(fn ($item, $key) => [$key => $item['name']])
			->toArray();
		
		// Get Bootstrap's Background Colors
		$bgColorsByName = BootstrapColor::Background->getColorsByName();
		$formattedBgColors = BootstrapColor::Background->getFormattedColors();
		
		// Get Bootstrap's Button Colors
		$btnColorsByName = BootstrapColor::Button->getColorsByName();
		$formattedBtnColors = BootstrapColor::Button->getFormattedColors();
		
		$fields = [
			[
				'name'  => 'separator_1',
				'type'  => 'custom_html',
				'value' => trans('admin.style_html_frontend'),
			],
			[
				'name'    => 'skin',
				'label'   => trans('admin.Front Skin'),
				'type'    => 'select2_from_skins',
				'options' => $skinsByName,
				'skins'   => $skins,
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'custom_skin_color',
				'label'      => trans('admin.custom_skin_color_label'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#FFFFFF',
				],
				'hint'       => trans('admin.custom_skin_color_hint'),
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'  => 'separator_2',
				'type'  => 'custom_html',
				'value' => trans('admin.style_html_customize_front'),
			],
			[
				'name'  => 'separator_2_1',
				'type'  => 'custom_html',
				'value' => trans('admin.style_html_customize_front_global'),
			],
			[
				'name'       => 'body_background_color',
				'label'      => trans('admin.Body Background Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#FFFFFF',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'body_text_color',
				'label'      => trans('admin.Body Text Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#292B2C',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'body_background_image_path',
				'label'   => trans('admin.Body Background Image'),
				'type'    => 'image',
				'upload'  => true,
				'disk'    => $diskName,
				'default' => null,
				'wrapper' => [
					'class' => 'col-md-12',
				],
				'newline' => true,
			],
			[
				'name'        => 'body_background_image_position',
				'label'       => trans('admin.bg_image_position_label'),
				'type'        => 'select2_from_array',
				'options'     => collect(getCachedReferrerList('css/background-position'))
					->mapWithKeys(fn ($item) => [$item => $item])
					->toArray(),
				'allows_null' => true,
				'wrapper'     => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'        => 'body_background_image_size',
				'label'       => trans('admin.bg_image_size_label'),
				'type'        => 'select2_from_array',
				'options'     => collect(getCachedReferrerList('css/background-size'))
					->mapWithKeys(fn ($item) => [$item => $item])
					->toArray(),
				'allows_null' => true,
				'wrapper'     => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'        => 'body_background_image_repeat',
				'label'       => trans('admin.bg_image_repeat_label'),
				'type'        => 'select2_from_array',
				'options'     => collect(getCachedReferrerList('css/background-repeat'))
					->mapWithKeys(fn ($item) => [$item => $item])
					->toArray(),
				'allows_null' => true,
				'wrapper'     => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'        => 'body_background_image_attachment',
				'label'       => trans('admin.bg_image_attachment_label'),
				'type'        => 'select2_from_array',
				'options'     => collect(getCachedReferrerList('css/background-attachment'))
					->mapWithKeys(fn ($item) => [$item => $item])
					->toArray(),
				'allows_null' => true,
				'wrapper'     => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'body_background_image_animation',
				'label'   => trans('admin.bg_image_animation_label'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'    => 'page_width',
				'label'   => trans('admin.Page Width'),
				'type'    => 'number',
				'wrapper' => [
					'class' => 'col-md-6',
				],
				'newline' => true,
			],
			
			[
				'name'       => 'title_color',
				'label'      => trans('admin.Titles Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#292B2C',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'progress_background_color',
				'label'      => trans('admin.Progress Background Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'link_color',
				'label'      => trans('admin.Links Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#4682B4',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'link_color_hover',
				'label'      => trans('admin.Links Color Hover'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#FF8C00',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'style_header_title',
				'type'  => 'custom_html',
				'value' => trans('admin.style_header_title'),
			],
			[
				'name'    => 'header_full_width',
				'label'   => trans('admin.Header Full Width'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-6 mt-3',
				],
			],
			[
				'name'       => 'header_height',
				'label'      => trans('admin.Header Height'),
				'type'       => 'number',
				'attributes' => [
					'placeholder' => 65,
					'min'         => 0,
					'step'        => 1,
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'dark_header',
				'label'   => trans('admin.dark_header_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.dark_header_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'header_shadow',
				'label'   => trans('admin.header_shadow_label'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'        => 'header_background_class',
				'label'       => trans('admin.header_background_class_label'),
				'type'        => 'select2_from_skins',
				'options'     => $bgColorsByName,
				'skins'       => $formattedBgColors,
				'allows_null' => true,
				'hint'        => trans('admin.header_background_class_hint'),
				'wrapper'     => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'       => 'header_background_color',
				'label'      => trans('admin.Header Background Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#F8F8F8',
				],
				'wrapper'    => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'       => 'header_border_bottom_width',
				'label'      => trans('admin.Header Border Bottom Width'),
				'type'       => 'number',
				'attributes' => [
					'placeholder' => 0,
					'min'         => 0,
					'step'        => 1,
				],
				'wrapper'    => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'       => 'header_border_bottom_color',
				'label'      => trans('admin.Header Border Bottom Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#E8E8E8',
				],
				'wrapper'    => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'       => 'header_link_color',
				'label'      => trans('admin.Header Links Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#333',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'header_link_color_hover',
				'label'      => trans('admin.Header Links Color Hover'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#000',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'header_animation',
				'label'   => trans('admin.header_animation_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.header_animation_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
				'newline' => true,
			],
			[
				'name'    => 'header_fixed_top',
				'label'   => trans('admin.header_fixed_top_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.header_fixed_top_hint', ['navbarHeightOffset' => trans('admin.header_height_offset_label')]),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'header_height_offset',
				'label'      => trans('admin.header_height_offset_label'),
				'type'       => 'number',
				'attributes' => [
					'placeholder' => 200,
					'min'         => 0,
					'step'        => 1,
				],
				'hint'       => trans('admin.header_height_offset_hint'),
				'wrapper'    => [
					'class' => 'col-md-6 fixed-header',
				],
			],
			[
				'name'    => 'fixed_dark_header',
				'label'   => trans('admin.fixed_dark_header_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.fixed_dark_header_hint'),
				'wrapper' => [
					'class' => 'col-md-6 fixed-header',
				],
			],
			[
				'name'    => 'fixed_header_shadow',
				'label'   => trans('admin.fixed_header_shadow_label'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-6 fixed-header',
				],
			],
			[
				'name'        => 'fixed_header_background_class',
				'label'       => trans('admin.fixed_header_background_class_label'),
				'type'        => 'select2_from_skins',
				'options'     => $bgColorsByName,
				'skins'       => $formattedBgColors,
				'allows_null' => true,
				'hint'        => trans('admin.fixed_header_background_class_hint'),
				'wrapper'     => [
					'class' => 'col-md-3 fixed-header',
				],
			],
			[
				'name'       => 'fixed_header_background_color',
				'label'      => trans('admin.fixed_header_background_color_label'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#F8F8F8',
				],
				'wrapper'    => [
					'class' => 'col-md-3 fixed-header',
				],
			],
			[
				'name'       => 'fixed_header_border_bottom_width',
				'label'      => trans('admin.fixed_header_border_bottom_width_label'),
				'type'       => 'number',
				'attributes' => [
					'placeholder' => 0,
					'min'         => 0,
					'step'        => 1,
				],
				'wrapper'    => [
					'class' => 'col-md-3 fixed-header',
				],
			],
			[
				'name'       => 'fixed_header_border_bottom_color',
				'label'      => trans('admin.fixed_header_border_bottom_color_label'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#E8E8E8',
				],
				'wrapper'    => [
					'class' => 'col-md-3 fixed-header',
				],
			],
			[
				'name'       => 'fixed_header_link_color',
				'label'      => trans('admin.fixed_header_link_color_label'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#333',
				],
				'wrapper'    => [
					'class' => 'col-md-6 fixed-header',
				],
			],
			[
				'name'       => 'fixed_header_link_color_hover',
				'label'      => trans('admin.fixed_header_link_color_hover_label'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#000',
				],
				'wrapper'    => [
					'class' => 'col-md-6 fixed-header',
				],
				'newline' => true,
			],
			
			[
				'name'    => 'header_highlighted_btn_link',
				'label'   => trans('admin.header_highlighted_btn_link_label'),
				'type'    => 'select2_from_array',
				'options' => [
					'none'                => trans('admin.no_link_as_header_btn'),
					'listingCreationLink' => trans('admin.listing_creation_link_as_header_btn'),
					'userMenuLink'        => trans('admin.user_menu_link_as_header_btn'),
				],
				'hint'    => trans('admin.header_highlighted_btn_link_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'        => 'header_highlighted_btn_class',
				'label'       => trans('admin.header_highlighted_btn_class_label'),
				'type'        => 'select2_from_skins',
				'options'     => $btnColorsByName,
				'skins'       => $formattedBtnColors,
				'allows_null' => true,
				'hint'        => trans('admin.header_highlighted_btn_class_hint'),
				'wrapper'     => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'    => 'header_highlighted_btn_outline',
				'label'   => trans('admin.header_highlighted_btn_outline_label'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-3 mt-3',
				],
			],
			
			[
				'name'  => 'separator_logo',
				'type'  => 'custom_html',
				'value' => trans('admin.style_logo_title'),
			],
			[
				'name'       => 'logo_width',
				'label'      => trans('admin.logo_width_label'),
				'type'       => 'number',
				'attributes' => [
					'placeholder' => 216,
					'min'         => 0,
					'step'        => 1,
				],
				'hint'       => trans('admin.logo_width_hint'),
				'wrapper'    => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'       => 'logo_height',
				'label'      => trans('admin.logo_height_label'),
				'type'       => 'number',
				'attributes' => [
					'placeholder' => 40,
					'min'         => 0,
					'step'        => 1,
				],
				'hint'       => trans('admin.logo_height_hint'),
				'wrapper'    => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'    => 'logo_aspect_ratio',
				'label'   => trans('admin.logo_aspect_ratio_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.logo_aspect_ratio_hint'),
				'wrapper' => [
					'class' => 'col-md-6 mt-3',
				],
			],
			
			[
				'name'  => 'style_footer_title',
				'type'  => 'custom_html',
				'value' => trans('admin.style_footer_title'),
			],
			[
				'name'    => 'dark_footer',
				'label'   => trans('admin.dark_footer_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.dark_footer_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'high_spacing_footer',
				'label'   => trans('admin.high_spacing_footer_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.high_spacing_footer_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'footer_full_width',
				'label'   => trans('admin.Footer Full Width'),
				'type'    => 'checkbox_switch',
				'wrapper' => [
					'class' => 'col-md-12',
				],
			],
			[
				'name'       => 'footer_background_color',
				'label'      => trans('admin.Footer Background Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#F5F5F5',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'footer_border_top_width',
				'label'   => trans('admin.Footer Border Top Width'),
				'type'    => 'number',
				'wrapper' => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'       => 'footer_border_top_color',
				'label'      => trans('admin.Footer Border Top Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#E8E8E8',
				],
				'wrapper'    => [
					'class' => 'col-md-3',
				],
			],
			[
				'name'       => 'footer_text_color',
				'label'      => trans('admin.Footer Text Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#333',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'footer_title_color',
				'label'      => trans('admin.Footer Titles Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#000',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
				'newline'    => true,
			],
			
			[
				'name'       => 'footer_link_color',
				'label'      => trans('admin.Footer Links Color'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#333',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'footer_link_color_hover',
				'label'      => trans('admin.Footer Links Color Hover'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#333',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'       => 'footer_inside_line_border_color',
				'label'      => trans('admin.footer_inside_line_border_color_label'),
				'type'       => 'color_picker',
				'attributes' => [
					'placeholder' => '#ddd',
				],
				'wrapper'    => [
					'class' => 'col-md-6',
				],
			],
			
			[
				'name'  => 'separator_3',
				'type'  => 'custom_html',
				'value' => trans('admin.style_html_raw_css'),
			],
			[
				'name'  => 'separator_3_1',
				'type'  => 'custom_html',
				'value' => trans('admin.style_html_raw_css_hint'),
			],
			[
				'name'       => 'custom_css',
				'label'      => trans('admin.Custom CSS'),
				'type'       => 'textarea',
				'attributes' => [
					'rows' => '5',
				],
				'hint'       => trans('admin.do_not_include_style_tags'),
			],
			
			[
				'name'  => 'backend_title_separator',
				'type'  => 'custom_html',
				'value' => trans('admin.backend_title_separator'),
			],
			[
				'name'    => 'admin_logo_bg',
				'label'   => trans('admin.admin_logo_bg_label'),
				'type'    => 'select2_from_array',
				'options' => [
					'skin1' => 'Green',
					'skin2' => 'Red',
					'skin3' => 'Blue',
					'skin4' => 'Purple',
					'skin5' => 'Black',
					'skin6' => 'White',
				],
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'admin_navbar_bg',
				'label'   => trans('admin.admin_navbar_bg_label'),
				'type'    => 'select2_from_array',
				'options' => [
					'skin1' => 'Green',
					'skin2' => 'Red',
					'skin3' => 'Blue',
					'skin4' => 'Purple',
					'skin5' => 'Black',
					'skin6' => 'White',
				],
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'admin_sidebar_type',
				'label'   => trans('admin.admin_sidebar_type_label'),
				'type'    => 'select2_from_array',
				'options' => [
					'full'         => 'Full',
					'mini-sidebar' => 'Mini Sidebar',
					'iconbar'      => 'Icon Bbar',
					'overlay'      => 'Overlay',
				],
				'hint'    => trans('admin.admin_sidebar_type_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'admin_sidebar_bg',
				'label'   => trans('admin.admin_sidebar_bg_label'),
				'type'    => 'select2_from_array',
				'options' => [
					'skin1' => 'Green',
					'skin2' => 'Red',
					'skin3' => 'Blue',
					'skin4' => 'Purple',
					'skin5' => 'Black',
					'skin6' => 'White',
				],
				'wrapper' => [
					'class' => 'col-md-6',
				],
				'newline' => true,
			],
			
			[
				'name'    => 'admin_sidebar_position',
				'label'   => trans('admin.admin_sidebar_position_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.admin_sidebar_position_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'admin_header_position',
				'label'   => trans('admin.admin_header_position_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.admin_header_position_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
			[
				'name'    => 'admin_boxed_layout',
				'label'   => trans('admin.admin_boxed_layout_label'),
				'type'    => 'checkbox_switch',
				'hint'    => trans('admin.admin_boxed_layout_hint'),
				'wrapper' => [
					'class' => 'col-md-6',
				],
			],
		];
		
		return addOptionsGroupJavaScript(__NAMESPACE__, __CLASS__, $fields);
	}
}
