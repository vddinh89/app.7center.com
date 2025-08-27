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

use App\Http\Controllers\Web\Admin\ActionController;
use App\Http\Controllers\Web\Admin\AdvertisingController;
use App\Http\Controllers\Web\Admin\BackupController;
use App\Http\Controllers\Web\Admin\BlacklistController;
use App\Http\Controllers\Web\Admin\CategoryController;
use App\Http\Controllers\Web\Admin\CategoryFieldController;
use App\Http\Controllers\Web\Admin\CityController;
use App\Http\Controllers\Web\Admin\CountryController;
use App\Http\Controllers\Web\Admin\CurrencyController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\FieldController;
use App\Http\Controllers\Web\Admin\FieldOptionController;
use App\Http\Controllers\Web\Admin\SectionController;
use App\Http\Controllers\Web\Admin\InlineRequestController;
use App\Http\Controllers\Web\Admin\LanguageController;
use App\Http\Controllers\Web\Admin\MetaTagController;
use App\Http\Controllers\Web\Admin\PackageController;
use App\Http\Controllers\Web\Admin\PageController;
use App\Http\Controllers\Web\Admin\Panel\Library\PanelRoutes;
use App\Http\Controllers\Web\Admin\PaymentController;
use App\Http\Controllers\Web\Admin\PaymentMethodController;
use App\Http\Controllers\Web\Admin\PermissionController;
use App\Http\Controllers\Web\Admin\PictureController;
use App\Http\Controllers\Web\Admin\PluginController;
use App\Http\Controllers\Web\Admin\PostController;
use App\Http\Controllers\Web\Admin\ReportTypeController;
use App\Http\Controllers\Web\Admin\RoleController;
use App\Http\Controllers\Web\Admin\SettingController;
use App\Http\Controllers\Web\Admin\SubAdmin1Controller;
use App\Http\Controllers\Web\Admin\SubAdmin2Controller;
use App\Http\Controllers\Web\Admin\SystemController;
use App\Http\Controllers\Web\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Admin Panel Area
Route::middleware(['admin', 'clearance', 'banned.user', 'no.http.cache'])
	->group(function ($router) {
		// Dashboard
		Route::controller(DashboardController::class)
			->group(function ($router) {
				Route::get('dashboard', 'dashboard');
				Route::get('/', 'redirect');
			});
		
		// Extra (must be called before CRUD)
		Route::controller(SettingController::class)
			->group(function ($router) {
				Route::get('settings/find/{key}', 'find')->where('key', '[^/]+');
				Route::get('settings/reset/{key}', 'reset')->where('key', '[^/]+');
			});
		Route::controller(SectionController::class)
			->group(function ($router) {
				Route::get('sections/find/{key}', 'find')->where('key', '[^/]+');
				Route::get('sections/reset/all/{action}', 'resetAll')->where('action', 'reorder|options');
			});
		Route::controller(LanguageController::class)
			->group(function ($router) {
				Route::get('languages/sync_files', 'syncFilesLines');
				Route::get('languages/texts/{lang?}/{file?}', 'showTexts')
					->where('lang', '[^/]*')
					->where('file', '[^/]*');
				Route::post('languages/texts/{lang}/{file}', 'updateTexts')
					->where('lang', '[^/]+')
					->where('file', '[^/]+');
			});
		Route::get('permissions/create_default_entries', [PermissionController::class, 'createDefaultEntries']);
		Route::get('blacklists/add', [BlacklistController::class, 'banUser']);
		Route::get('categories/rebuild-nested-set-nodes', [CategoryController::class, 'rebuildNestedSetNodes']);
		
		// Panel's Default Routes
		PanelRoutes::resource('advertisings', AdvertisingController::class);
		PanelRoutes::resource('blacklists', BlacklistController::class);
		PanelRoutes::resource('categories', CategoryController::class);
		PanelRoutes::resource('categories/{catId}/subcategories', CategoryController::class);
		PanelRoutes::resource('categories/{catId}/custom_fields', CategoryFieldController::class);
		PanelRoutes::resource('cities', CityController::class);
		PanelRoutes::resource('countries', CountryController::class);
		PanelRoutes::resource('countries/{countryCode}/cities', CityController::class);
		PanelRoutes::resource('countries/{countryCode}/admins1', SubAdmin1Controller::class);
		PanelRoutes::resource('currencies', CurrencyController::class);
		PanelRoutes::resource('custom_fields', FieldController::class);
		PanelRoutes::resource('custom_fields/{cfId}/options', FieldOptionController::class);
		PanelRoutes::resource('custom_fields/{cfId}/categories', CategoryFieldController::class);
		PanelRoutes::resource('sections', SectionController::class);
		PanelRoutes::resource('admins1/{admin1Code}/cities', CityController::class);
		PanelRoutes::resource('admins1/{admin1Code}/admins2', SubAdmin2Controller::class);
		PanelRoutes::resource('admins2/{admin2Code}/cities', CityController::class);
		PanelRoutes::resource('languages', LanguageController::class);
		PanelRoutes::resource('meta_tags', MetaTagController::class);
		PanelRoutes::resource('packages/promotion', PackageController::class);
		PanelRoutes::resource('packages/subscription', PackageController::class);
		PanelRoutes::resource('pages', PageController::class);
		PanelRoutes::resource('payments/promotion', PaymentController::class);
		PanelRoutes::resource('payments/subscription', PaymentController::class);
		PanelRoutes::resource('payment_methods', PaymentMethodController::class);
		PanelRoutes::resource('permissions', PermissionController::class);
		PanelRoutes::resource('pictures', PictureController::class);
		PanelRoutes::resource('posts', PostController::class);
		PanelRoutes::resource('report_types', ReportTypeController::class);
		PanelRoutes::resource('roles', RoleController::class);
		PanelRoutes::resource('settings', SettingController::class);
		PanelRoutes::resource('users', UserController::class);
		
		// Others
		Route::get('account', [UserController::class, 'account']);
		Route::post('ajax/{table}/{field}', [InlineRequestController::class, 'make'])
			->where('table', '[^/]+')
			->where('field', '[^/]+');
		
		// Backup
		Route::controller(BackupController::class)
			->group(function ($router) {
				Route::get('backups', 'index');
				Route::put('backups/create', 'create');
				Route::get('backups/download', 'download');
				Route::delete('backups/delete', 'delete');
			});
		
		// Actions
		Route::controller(ActionController::class)
			->group(function ($router) {
				Route::get('actions/clear_cache', 'clearCache');
				Route::get('actions/clear_images_thumbnails', 'clearImagesThumbnails');
				Route::get('actions/update_database_charset_collation', 'updateDBConnectionCharsetAndCollation');
				Route::post('actions/maintenance/{mode}', 'maintenance')->where('mode', 'down');
				Route::get('actions/maintenance/{mode}', 'maintenance')->where('mode', 'up');
			});
		
		// Plugins
		Route::controller(PluginController::class)
			->group(function ($router) {
				$router->pattern('plugin', '.+');
				Route::get('plugins', 'index');
				Route::post('plugins/{plugin}/install/code', 'installWithCode');
				Route::get('plugins/{plugin}/install', 'installWithoutCode');
				Route::get('plugins/{plugin}/uninstall', 'uninstall');
				Route::get('plugins/{plugin}/delete', 'delete');
			});
		
		// System Info
		Route::get('system', [SystemController::class, 'index']);
	});
