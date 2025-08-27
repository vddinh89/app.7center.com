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
use App\Http\Requests\Admin\FieldOptionRequest as StoreRequest;
use App\Http\Requests\Admin\FieldOptionRequest as UpdateRequest;
use App\Models\Field;
use App\Models\FieldOption;
use Illuminate\Http\RedirectResponse;

class FieldOptionController extends PanelController
{
	private $fieldId = null;
	
	public function setup()
	{
		// Get the Custom Field's ID
		$this->fieldId = request()->segment(3);
		
		// Get the Custom Field's name
		$field = Field::findOrFail($this->fieldId);
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(FieldOption::class);
		$this->xPanel->setRoute(urlGen()->adminUri('custom_fields/' . $field->id . '/options'));
		$this->xPanel->setEntityNameStrings(
			trans('admin.option') . ' &rarr; ' . '<strong>' . $field->name . '</strong>',
			trans('admin.options') . ' &rarr; ' . '<strong>' . $field->name . '</strong>'
		);
		$this->xPanel->enableReorder('value', 1);
		if (!request()->input('order')) {
			$this->xPanel->orderBy('lft');
		}
		
		$this->xPanel->enableParentEntity();
		$this->xPanel->setParentKeyField('field_id');
		$this->xPanel->addClause('where', 'field_id', '=', $field->id);
		$this->xPanel->setParentRoute(urlGen()->adminUri('custom_fields'));
		$this->xPanel->setParentEntityNameStrings(trans('admin.custom field'), trans('admin.custom fields'));
		$this->xPanel->allowAccess(['reorder', 'parent']);
		
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
		$this->xPanel->addColumn([
			'name'  => 'value',
			'label' => trans('admin.Value'),
		]);
		
		
		// FIELDS
		$this->xPanel->addField([
			'name'  => 'field_id',
			'type'  => 'hidden',
			'value' => $this->fieldId,
		], 'create');
		$this->xPanel->addField([
			'name'       => 'value',
			'label'      => trans('admin.Value'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Value'),
			],
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
}
