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

namespace App\Http\Controllers\Web\Admin;

// Increase the server resources
$iniConfigFile = __DIR__ . '/../../../Helpers/Common/Functions/ini.php';
if (file_exists($iniConfigFile)) {
	include_once $iniConfigFile;
}

use App\Helpers\Services\Lang\LangManager;
use App\Http\Controllers\Web\Admin\Panel\Library\Helpers\LanguageFiles;
use App\Http\Controllers\Web\Admin\Panel\PanelController;
use App\Http\Requests\Admin\LanguageRequest as StoreRequest;
use App\Http\Requests\Admin\LanguageRequest as UpdateRequest;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class LanguageController extends PanelController
{
	public function setup()
	{
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(Language::class);
		$this->xPanel->setRoute(urlGen()->adminUri('languages'));
		$this->xPanel->setEntityNameStrings(trans('admin.language'), trans('admin.languages'));
		$this->xPanel->enableReorder('name', 1);
		$this->xPanel->allowAccess(['reorder']);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft');
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'sync_files', 'syncFilesLinesButton', 'end');
		$this->xPanel->addButtonFromModelFunction('top', 'files_edition', 'filesLinesEditionButton', 'end');
		
		// Filters
		// -----------------------
		$this->xPanel->disableSearchBar();
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'name',
				'type'  => 'text',
				'label' => mb_ucfirst(trans('admin.Name')),
			],
			false,
			function ($value) {
				$this->xPanel->addClause('where', 'name', 'LIKE', "%$value%");
				$this->xPanel->addClause('orWhere', 'code', '=', "$value");
				$this->xPanel->addClause('orWhere', 'locale', 'LIKE', "$value%");
			}
		);
		// -----------------------
		$this->xPanel->addFilter(
			[
				'name'  => 'status',
				'type'  => 'dropdown',
				'label' => trans('admin.Status'),
			],
			[
				1 => trans('admin.Activated'),
				2 => trans('admin.Unactivated'),
			],
			function ($value) {
				if ($value == 1) {
					$this->xPanel->addClause('where', 'active', '=', 1);
				}
				if ($value == 2) {
					$this->xPanel->addClause('where', fn ($query) => $query->columnIsEmpty('active'));
				}
			}
		);
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'  => 'code',
			'label' => trans('admin.code'),
		]);
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => trans('admin.name'),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'  => 'locale',
			'label' => trans('admin.locale'),
		]);
		$this->xPanel->addColumn([
			'name'  => 'direction',
			'label' => trans('admin.Direction'),
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans('admin.active'),
			'type'          => "model_function",
			'function_name' => 'getActiveHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'default',
			'label'         => trans('admin.default'),
			'type'          => "model_function",
			'function_name' => 'getDefaultHtml',
		]);
		
		// FIELDS
		$infoLine = [
			'name' => 'info_line_1',
			'type' => 'custom_html',
		];
		$this->xPanel->addField(array_merge($infoLine, [
			'value' => trans('admin.language_info_line_create'),
		]), 'create');
		$this->xPanel->addField(array_merge($infoLine, [
			'value' => trans('admin.language_info_line_update', ['code' => request()->segment(3)]),
		]), 'update');
		
		$and = t('_and_');
		$includedLanguages = collect($this->includedLanguages())
			->map(fn ($item) => ('<code>' . $item . '</code>'))
			->join(', ', $and);
		
		$codeHint = trans('admin.language_code_field_hint', ['languages' => '<br>' . $includedLanguages]);
		$codeHint .= '<br>' . trans('admin.language_locale_hint');
		$this->xPanel->addField([
			'label'       => mb_ucwords(trans('admin.language')),
			'name'        => 'code',
			'type'        => 'select2_from_array',
			'options'     => $this->getLanguageList(),
			'allows_null' => true,
			'hint'        => $codeHint,
			'wrapper'     => [
				'class' => 'col-md-6',
			],
		], 'create');
		
		$this->xPanel->addField([
			'name'       => 'native',
			'label'      => mb_ucwords(trans('admin.native_name')),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => mb_ucwords(trans('admin.native_name')),
			],
			'hint'       => trans('admin.language_native_name_hint'),
			'wrapper'    => [
				'class' => 'col-md-6',
			],
			'newline'    => true,
		]);
		
		/*
		 * WARNING: Bug found with certain servers when the Turkish language locale "tr_TR"
		 * is set via the PHP setlocale() function. And scopes or models functions  starting by "I"
		 * cannot be found since the Turkish language have a dotless "ı" and a dotted "i"
		 * that need the right locale (including locale with codeset).
		 * To fix that, a locale with codeset need to be set instead, like "tr_TR.UTF-8", "tr_TR.utf8", etc.
		 */
		$this->xPanel->addField([
			'label'       => trans('admin.locale'),
			'name'        => 'locale',
			'type'        => 'select2_from_array',
			'options'     => getLocalesWithName(),
			'allows_null' => true,
			'hint'        => trans('admin.locale_code_hint_bj'),
			'wrapper'     => [
				'class' => 'col-md-6',
			],
		], 'update');
		
		$this->xPanel->addField([
			'label'       => trans('admin.lang_script_label') . ' (' . trans('admin.Optional') . ')',
			'name'        => 'script',
			'type'        => 'select2_from_array',
			'options'     => getLanguageScriptRefList(),
			'allows_null' => true,
			'hint'        => trans('admin.lang_script_hint'),
			'wrapper'     => [
				'class' => 'col-md-6',
			],
		], 'update');
		
		$this->xPanel->addField([
			'name'        => 'flag',
			'label'       => trans('admin.flag'),
			'type'        => 'icon_picker',
			'iconSet'     => 'flagicon',
			'iconVersion' => '3.5.0',
			'wrapper'     => [
				'class' => 'col-md-3',
			],
		]);
		
		$this->xPanel->addField([
			'name'    => 'direction',
			'label'   => trans('admin.Direction'),
			'type'    => 'enum',
			'wrapper' => [
				'class' => 'col-md-3',
			],
		]);
		
		$this->xPanel->addField([
			'name'    => 'russian_pluralization',
			'label'   => trans('admin.Russian Pluralization'),
			'type'    => 'checkbox_switch',
			'wrapper' => [
				'class' => 'col-md-6 mt-4',
			],
			'newline' => 'create',
		]);
		
		$dateFormatHint = (config('settings.app.php_specific_date_format')) ? 'php_date_format_hint_bj' : 'iso_date_format_hint_bj';
		$this->xPanel->addField([
			'name'    => 'date_format',
			'label'   => trans('admin.date_format_label'),
			'type'    => 'text',
			'hint'    => trans('admin.' . $dateFormatHint, ['year' => date('Y')]),
			'wrapper' => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'    => 'datetime_format',
			'label'   => trans('admin.datetime_format_label'),
			'type'    => 'text',
			'hint'    => trans('admin.' . $dateFormatHint, ['year' => date('Y')]),
			'wrapper' => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'  => 'admin_date_format_info',
			'type'  => 'custom_html',
			'value' => trans('admin.lang_date_format_info', [
				'countriesUrl' => urlGen()->adminUrl('countries'),
			]),
		]);
		
		$this->xPanel->addField([
			'name'    => 'active',
			'type'    => 'hidden',
			'default' => 1,
		], 'create');
		$this->xPanel->addField([
			'name'  => 'active',
			'label' => trans('admin.active'),
			'type'  => 'checkbox_switch',
		], 'update');
		
		$this->xPanel->addField([
			'name'  => 'default',
			'label' => trans('admin.default_locale'),
			'type'  => 'checkbox_switch',
			'hint'  => trans('admin.language_default_info'),
		], 'update');
		
		$fallbackLocale = [
			'name'  => 'is_db_fallback_locale',
			'label' => trans('admin.db_fallback_locale'),
			'type'  => 'checkbox_switch',
			'value' => 0,
			'hint'  => trans('admin.db_fallback_locale_info'),
		];
		if (request()->segment(4) == 'edit') {
			$entry = Language::find(request()->segment(3));
			if (!empty($entry)) {
				if ($entry->code == config('translatable.fallback_locale')) {
					$fallbackLocale['value'] = 1;
				}
			}
		}
		$this->xPanel->addField($fallbackLocale, 'update');
		
		$this->xPanel->addField([
			'name'  => 'fill_missing_trans_texts',
			'label' => trans('admin.fill_missing_trans_texts_label'),
			'type'  => 'checkbox_switch',
			'hint'  => trans('admin.fill_missing_trans_texts_hint', [
				'fallbackLocale' => trans('admin.db_fallback_locale'),
			]),
		], 'update');
		
		$this->xPanel->addField([
			'name'    => 'created_at',
			'type'    => 'hidden',
			'default' => now()->format('Y-m-d H:i:s'),
		], 'create');
		
		$this->xPanel->addField([
			'name'    => 'updated_at',
			'type'    => 'hidden',
			'default' => now()->format('Y-m-d H:i:s'),
		]);
	}
	
	public function create()
	{
		if (empty(getLocales('installed'))) {
			$message = trans('admin.empty_locales_list', ['field' => trans('admin.locale')]);
			notification($message, 'warning');
		}
		
		return parent::create();
	}
	
	public function edit($id, $childId = null)
	{
		if (empty(getLocales('installed'))) {
			$message = trans('admin.empty_locales_list', ['field' => trans('admin.locale')]);
			notification($message, 'warning');
		}
		
		return parent::edit($id, $childId);
	}
	
	public function store(StoreRequest $request): RedirectResponse
	{
		return parent::storeCrud($request);
	}
	
	public function update(UpdateRequest $request): RedirectResponse
	{
		if ($request->filled('code')) {
			// Set or Remove Db Fallback Locale
			$fallbackLocaleEnabled = (
				$request->filled('is_db_fallback_locale')
				&& $request->input('is_db_fallback_locale') == '1'
			);
			if ($fallbackLocaleEnabled) {
				setDbFallbackLocale($request->input('code'));
			} else {
				if ($request->input('code') == config('translatable.fallback_locale')) {
					removeDbFallbackLocale();
				}
			}
			
			// Add missing translations
			$fillMissingTransEnabled = (
				$request->filled('fill_missing_trans_texts')
				&& $request->input('fill_missing_trans_texts') == '1'
			);
			if ($fillMissingTransEnabled) {
				if (!$fallbackLocaleEnabled) {
					$message = trans('admin.fill_missing_trans_texts_fallback', [
						'fallbackLocale' => trans('admin.db_fallback_locale'),
					]);
					notification($message, 'warning');
				}
				
				// Go to maintenance with DOWN status
				Artisan::call('down');
				
				addMissingTranslations($request->input('code'));
				
				// Restore system UP status
				Artisan::call('up');
			}
		}
		
		return parent::updateCrud($request);
	}
	
	/**
	 * (Try to) Fill the missing lines in all languages files
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function syncFilesLines(): RedirectResponse
	{
		$errorFound = false;
		
		try {
			// Get the current Default Language
			$defaultLang = Language::where('default', 1)->first();
			
			// Init. the language manager
			$manager = new LangManager();
			
			// Get all the others languages
			$locales = $manager->getLocales($defaultLang->code);
			if (!empty($locales)) {
				foreach ($locales as $locale) {
					$manager->syncLines($defaultLang->code, $locale);
				}
			}
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		// Check if error occurred
		if (!$errorFound) {
			$message = trans('admin.The languages files were been synchronized');
			notification($message, 'success');
		}
		
		return redirect()->back();
	}
	
	/**
	 * @param \App\Http\Controllers\Web\Admin\Panel\Library\Helpers\LanguageFiles $langFile
	 * @param \App\Models\Language $languages
	 * @param string $lang
	 * @param string $file
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
	 */
	public function showTexts(LanguageFiles $langFile, Language $languages, string $lang = '', string $file = 'site')
	{
		// SECURITY
		// Check if that file isn't forbidden in the config file
		if (in_array($file, (array)config('larapen.admin.language_ignore'))) {
			abort('403', trans('admin.cant_edit_online'));
		}
		
		if ($lang) {
			$langFile->setLanguage($lang);
		}
		
		// Set language file & Get its content
		$langFile->setFile($file);
		$fileArray = $langFile->getFileContent();
		
		// Check if the server can handle all input variables
		if (is_array($fileArray)) {
			$guaranteedMaxInputVars = count($fileArray) * 3;
			if (!$this->checkIfAllInputsCanBeHandled($guaranteedMaxInputVars)) {
				return redirect()->back();
			}
		}
		
		// Set the view's vars
		$this->data['xPanel'] = $this->xPanel;
		$this->data['currentFile'] = $file;
		$this->data['currentLang'] = !empty($lang) ? $lang : config('app.locale');
		$this->data['currentLangObj'] = Language::where('code', '=', $this->data['currentLang'])->first();
		$this->data['browsingLangObj'] = Language::where('code', '=', config('app.locale'))->first();
		$this->data['languages'] = $languages->orderBy('name')->get();
		$this->data['langFiles'] = $langFile->getLangFiles();
		$this->data['fileArray'] = $fileArray;
		$this->data['langFile'] = $langFile;
		$this->data['title'] = trans('admin.translations');
		
		return view('admin.translations', $this->data);
	}
	
	/**
	 * @param \App\Http\Controllers\Web\Admin\Panel\Library\Helpers\LanguageFiles $langFile
	 * @param \Illuminate\Http\Request $request
	 * @param string $lang
	 * @param string $file
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateTexts(LanguageFiles $langFile, Request $request, string $lang = '', string $file = 'site'): RedirectResponse
	{
		// SECURITY
		// Check if that file isn't forbidden in the config file
		if (in_array($file, config('larapen.admin.language_ignore'))) {
			abort('403', trans('admin.cant_edit_online'));
		}
		
		if ($lang) {
			$langFile->setLanguage($lang);
		}
		
		$langFile->setFile($file);
		
		// Check if the server can handle all input variables
		$guaranteedMaxInputVars = is_array($request->all()) ? count($request->all()) : 0;
		if (!$this->checkIfAllInputsCanBeHandled($guaranteedMaxInputVars)) {
			return redirect()->back();
		}
		
		$fields = $langFile->testFields($request->all());
		if (empty($fields)) {
			if ($langFile->setFileContent($request->all())) {
				notification(trans('admin.saved'), 'success');
			}
		} else {
			notification(trans('admin.please_fill_all_fields'), 'error');
		}
		
		return redirect()->back();
	}
	
	// PRIVATE METHODS
	
	/**
	 * Check if the server can handle all input variables
	 *
	 * @param int $guaranteedMaxInputVars
	 * @return bool
	 */
	private function checkIfAllInputsCanBeHandled(int $guaranteedMaxInputVars): bool
	{
		if (!is_numeric($guaranteedMaxInputVars) || $guaranteedMaxInputVars <= 0) {
			notification(trans('admin.no_entries_in_this_file'), 'error');
			
			return false;
		}
		
		$errorFound = false;
		try {
			if (ini_get('max_input_vars') < $guaranteedMaxInputVars) {
				if (ini_set('max_input_vars', $guaranteedMaxInputVars) === false) {
					$warning = trans('admin.Unable to set max_input_vars');
					notification($warning, 'warning');
					
					$error = trans('admin.files_max_input_vars_limit', ['number' => $guaranteedMaxInputVars]);
					notification($error, 'error');
					
					$errorFound = true;
				}
			}
		} catch (Throwable $e) {
			notification($e->getMessage(), 'error');
			$errorFound = true;
		}
		
		return !$errorFound;
	}
	
	/**
	 * @return array
	 */
	private function getLanguageList(): array
	{
		$entries = getRegionalLanguageRefList();
		
		return collect($entries)
			->map(function ($name, $code) {
				if (in_array($code, $this->includedLanguages())) {
					$name .= ' &#10004;';
				}
				
				return $name;
			})->toArray();
	}
	
	/**
	 * @return array
	 */
	private function includedLanguages(): array
	{
		$manager = new LangManager();
		
		return $manager->getTranslatedLanguages();
	}
}
