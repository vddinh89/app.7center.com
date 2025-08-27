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
use App\Http\Controllers\Web\Admin\Traits\SubAdminTrait;
use App\Http\Requests\Admin\SubAdmin2Request as StoreRequest;
use App\Http\Requests\Admin\SubAdmin2Request as UpdateRequest;
use App\Models\Country;
use App\Models\SubAdmin1;
use App\Models\SubAdmin2;
use Illuminate\Http\RedirectResponse;

class SubAdmin2Controller extends PanelController
{
	use SubAdminTrait;
	
	public $parentEntity = null;
	
	public $countryCode = null;
	
	public $admin1Code = null;
	
	public function setup()
	{
		// Parents Entities
		$parentEntities = ['admins1'];
		
		// Get the parent Entity slug
		$this->parentEntity = request()->segment(2);
		if (!in_array($this->parentEntity, $parentEntities)) {
			abort(404);
		}
		
		// Admin1 => Admin2
		if ($this->parentEntity == 'admins1') {
			// Get the Admin1 Codes
			$this->admin1Code = request()->segment(3);
			
			// Get the Admin1's name
			$admin1 = SubAdmin1::findOrFail($this->admin1Code);
			
			// Get the Country Code
			$this->countryCode = $admin1->country_code;
			
			// Get the Country's name
			$country = Country::findOrFail($this->countryCode);
		}
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel(SubAdmin2::class);
		$this->xPanel->enableParentEntity();
		$this->xPanel->allowAccess(['parent']);
		
		// Admin1 => Admin2
		if ($this->parentEntity == 'admins1') {
			$this->xPanel->setRoute(urlGen()->adminUri('admins1/' . $this->admin1Code . '/admins2'));
			$this->xPanel->setEntityNameStrings(
				trans('admin.admin division 2') . ' &rarr; ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>',
				trans('admin.admin divisions 2') . ' &rarr; ' . '<strong>' . $admin1->name . '</strong>' . ', ' . '<strong>' . $country->name . '</strong>'
			);
			$this->xPanel->setParentKeyField('subadmin1_code');
			$this->xPanel->addClause('where', 'subadmin1_code', '=', $this->admin1Code);
			$this->xPanel->setParentRoute(urlGen()->adminUri('countries/' . $this->countryCode . '/admins1'));
			$this->xPanel->setParentEntityNameStrings(
				trans('admin.admin division 1') . ' &rarr; ' . '<strong>' . $country->name . '</strong>',
				trans('admin.admin divisions 1') . ' &rarr; ' . '<strong>' . $country->name . '</strong>'
			);
		}
		
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_activation_button', 'bulkActivationButton', 'end');
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_deactivation_button', 'bulkDeactivationButton', 'end');
		$this->xPanel->addButtonFromModelFunction('top', 'bulk_deletion_button', 'bulkDeletionButton', 'end');
		$this->xPanel->addButtonFromModelFunction('line', 'cities', 'citiesButton', 'beginning');
		
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
			'name'  => 'code',
			'label' => trans('admin.code'),
		]);
		$this->xPanel->addColumn([
			'name'          => 'name',
			'label'         => trans('admin.Name'),
			'type'          => 'model_function',
			'function_name' => 'getNameHtml',
		]);
		$this->xPanel->addColumn([
			'name'          => 'active',
			'label'         => trans('admin.Active'),
			'type'          => 'model_function',
			'function_name' => 'getActiveHtml',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'  => 'country_code',
			'type'  => 'hidden',
			'value' => $this->countryCode,
		], 'create');
		$this->xPanel->addField([
			'name'  => 'subadmin1_code',
			'type'  => 'hidden',
			'value' => $this->admin1Code,
		], 'create');
		$this->xPanel->addField([
			'name'    => 'code',
			'type'    => 'hidden',
			'default' => $this->autoIncrementCode($this->admin1Code . '.'),
		], 'create');
		$this->xPanel->addField([
			'name'       => 'name',
			'label'      => trans('admin.Name'),
			'type'       => 'text',
			'attributes' => [
				'placeholder' => trans('admin.Enter the name'),
			],
			'wrapper'    => [
				'class' => 'col-md-6',
			],
		]);
		$this->xPanel->addField([
			'name'  => 'active',
			'label' => trans('admin.Active'),
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
}
