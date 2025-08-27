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

use App\Http\Controllers\Web\Admin\Panel\PanelController;
use App\Http\Requests\Admin\CategoryFieldRequest as StoreRequest;
use App\Http\Requests\Admin\CategoryFieldRequest as UpdateRequest;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\Field;
use Illuminate\Http\RedirectResponse;

class CategoryFieldController extends PanelController
{
	public $parentEntity = null;
	
	private $categoryId = null;
	
	private $fieldId = null;
	
	public function setup()
	{
		// Parents Entities
		$parentEntities = ['categories', 'custom_fields'];
		
		// Get the parent Entity slug
		$this->parentEntity = request()->segment(2);
		if (!in_array($this->parentEntity, $parentEntities)) {
			abort(404);
		}
		
		// Category => CategoryField
		if ($this->parentEntity == 'categories') {
			$this->categoryId = request()->segment(3);
			
			// Get Parent Category's name
			$category = Category::findOrFail($this->categoryId);
		}
		
		// Field => CategoryField
		if ($this->parentEntity == 'custom_fields') {
			$this->fieldId = request()->segment(3);
			
			// Get Field's name
			$field = Field::findOrFail($this->fieldId);
		}
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(CategoryField::class);
		$this->xPanel->with(['category', 'field']);
		$this->xPanel->enableParentEntity();
		
		// Category => CategoryField
		if ($this->parentEntity == 'categories') {
			$this->xPanel->setRoute(urlGen()->adminUri('categories/' . $category->id . '/custom_fields'));
			$this->xPanel->setEntityNameStrings(
				trans('admin.custom field') . ' &rarr; ' . '<strong>' . $category->name . '</strong>',
				trans('admin.custom fields') . ' &rarr; ' . '<strong>' . $category->name . '</strong>'
			);
			$this->xPanel->enableReorder('field.name', 1);
			if (!request()->input('order')) {
				$this->xPanel->orderBy('lft');
			}
			$this->xPanel->setParentKeyField('category_id');
			$this->xPanel->addClause('where', 'category_id', '=', $category->id);
			$this->xPanel->setParentRoute(urlGen()->adminUri('categories'));
			$this->xPanel->setParentEntityNameStrings(trans('admin.category'), trans('admin.categories'));
			$this->xPanel->allowAccess(['reorder', 'parent']);
		}
		
		// Field => CategoryField
		if ($this->parentEntity == 'custom_fields') {
			$this->xPanel->setRoute(urlGen()->adminUri('custom_fields/' . $field->id . '/categories'));
			$this->xPanel->setEntityNameStrings(
				'<strong>' . $field->name . '</strong> ' . trans('admin.custom field') . ' &rarr; ' . trans('admin.category'),
				'<strong>' . $field->name . '</strong> ' . trans('admin.custom fields') . ' &rarr; ' . trans('admin.categories')
			);
			$this->xPanel->setParentKeyField('field_id');
			$this->xPanel->addClause('where', 'field_id', '=', $field->id);
			$this->xPanel->setParentRoute(urlGen()->adminUri('custom_fields'));
			$this->xPanel->setParentEntityNameStrings(trans('admin.custom field'), trans('admin.custom fields'));
			$this->xPanel->allowAccess(['parent']);
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_deletion_button', 'bulkDeletionButton', 'end');
		
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'      => 'id',
			'label'     => '',
			'type'      => 'checkbox',
			'orderable' => false,
		]);
		
		// Category => CategoryField
		if ($this->parentEntity == 'categories') {
			$this->xPanel->addColumn([
				'name'          => 'field_id',
				'label'         => mb_ucfirst(trans('admin.custom field')),
				'type'          => 'model_function',
				'function_name' => 'getFieldHtml',
			]);
		}
		
		// Field => CategoryField
		if ($this->parentEntity == 'custom_fields') {
			$this->xPanel->addColumn([
				'name'          => 'category_id',
				'label'         => trans('admin.Category'),
				'type'          => 'model_function',
				'function_name' => 'getCategoryHtml',
			]);
		}
		
		$this->xPanel->addColumn([
			'name'          => 'disabled_in_subcategories',
			'label'         => trans('admin.Disabled in subcategories'),
			'type'          => 'model_function',
			'function_name' => 'getDisabledInSubCategoriesHtml',
			'on_display'    => 'checkbox',
		]);
		
		
		// FIELDS
		// Category => CategoryField
		if ($this->parentEntity == 'categories') {
			$this->xPanel->addField([
				'name'  => 'category_id',
				'type'  => 'hidden',
				'value' => $this->categoryId,
			], 'create');
			$this->xPanel->addField([
				'name'        => 'field_id',
				'label'       => mb_ucfirst(trans('admin.Select a Custom field')),
				'type'        => 'select2_from_array',
				'options'     => $this->fields($this->fieldId),
				'allows_null' => false,
			]);
		}
		
		// Field => CategoryField
		if ($this->parentEntity == 'custom_fields') {
			$this->xPanel->addField([
				'name'  => 'field_id',
				'type'  => 'hidden',
				'value' => $this->fieldId,
			], 'create');
			$this->xPanel->addField([
				'name'        => 'category_id',
				'label'       => trans('admin.Select a Category'),
				'type'        => 'select2_from_array',
				'options'     => $this->categories($this->categoryId),
				'allows_null' => false,
			]);
		}
		
		$this->xPanel->addField([
			'name'  => 'disabled_in_subcategories',
			'label' => trans('admin.Disabled in subcategories'),
			'type'  => 'checkbox_switch',
		]);
	}
	
	public function store(StoreRequest $request): RedirectResponse
	{
		return parent::storeCrud($request);
	}
	
	public function update(UpdateRequest $request): RedirectResponse
	{
		return parent::updateCrud($request);
	}
	
	private function fields($selectedEntryId): array
	{
		$entries = Field::query()->orderBy('name')->get();
		
		return collect($entries)
			->mapWithKeys(function ($item) {
				return [$item['id'] => $item['name']];
			})->toArray();
	}
	
	private function categories($selectedEntryId): array
	{
		$entries = Category::root()->orderBy('lft')->get();
		
		if ($entries->count() <= 0) {
			return [];
		}
		
		$tab = [];
		foreach ($entries as $entry) {
			$tab[$entry->id] = $entry->name;
			
			$subEntries = Category::childrenOf($entry->id)->orderBy('lft')->get();
			if ($subEntries->count() > 0) {
				foreach ($subEntries as $subEntry) {
					$tab[$subEntry->id] = "---| " . $subEntry->name;
				}
			}
		}
		
		return $tab;
	}
}
