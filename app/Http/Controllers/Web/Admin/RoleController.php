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
use App\Http\Requests\Admin\Request;
use App\Http\Requests\Admin\RoleRequest as StoreRequest;
use App\Http\Requests\Admin\RoleRequest as UpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;

class RoleController extends PanelController
{
	public function setup()
	{
		$roleModel = config('permission.models.role');
		$permissionModel = config('permission.models.permission');
		
		/*
		|--------------------------------------------------------------------------
		| BASIC CRUD INFORMATION
		|--------------------------------------------------------------------------
		*/
		$this->xPanel->setModel($roleModel);
		$this->xPanel->setRoute(urlGen()->adminUri('roles'));
		$this->xPanel->setEntityNameStrings(trans('admin.role'), trans('admin.roles'));
		
		$this->xPanel->removeButton('delete');
		$this->xPanel->addButtonFromModelFunction('line', 'delete', 'deleteButton', 'end');
		
		
		/*
		|--------------------------------------------------------------------------
		| COLUMNS AND FIELDS
		|--------------------------------------------------------------------------
		*/
		// COLUMNS
		$this->xPanel->addColumn([
			'name'  => 'name',
			'label' => trans('admin.name'),
			'type'  => 'text',
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'  => 'name',
			'label' => trans('admin.name'),
			'type'  => 'text',
		], 'create');
		
		$entity = $this->xPanel->getModel()->find(request()->segment(3));
		if (!empty($entity)) {
			$this->xPanel->addField([
				'name'  => 'name',
				'type'  => 'custom_html',
				'value' => '<h3><strong>' . trans('admin.name') . ':</strong> ' . $entity->name . '</h3>',
			], 'update');
		}
		
		$this->xPanel->addField([
			'label'     => mb_ucfirst(trans('admin.permission_plural')),
			'type'      => 'checklist',
			'name'      => 'permissions',
			'entity'    => 'permissions',
			'attribute' => 'name',
			'model'     => $permissionModel,
			'pivot'     => true,
		]);
		
		if (!config('larapen.admin.allow_role_create')) {
			$this->xPanel->denyAccess('create');
		}
		if (!config('larapen.admin.allow_role_update')) {
			$this->xPanel->denyAccess('update');
		}
		if (!config('larapen.admin.allow_role_delete')) {
			$this->xPanel->denyAccess('delete');
		}
	}
	
	public function store(StoreRequest $request): RedirectResponse
	{
		$request = $this->setRoleDefaultPermissions($request);
		
		// Otherwise, changes won't have an effect
		cache()->forget('spatie.permission.cache');
		
		return parent::storeCrud($request);
	}
	
	public function update(UpdateRequest $request): RedirectResponse
	{
		$request = $this->setRoleDefaultPermissions($request);
		
		// Otherwise, changes won't have an effect
		cache()->forget('spatie.permission.cache');
		
		return parent::updateCrud($request);
	}
	
	/**
	 * Set role's default (or required) permissions
	 *
	 * @param \App\Http\Requests\Admin\Request $request
	 * @return \App\Http\Requests\Admin\Request
	 */
	public function setRoleDefaultPermissions(Request $request): Request
	{
		// Get request permissions
		$permissionIds = $request->input('permissions');
		$permissionIds = collect($permissionIds)->map(fn ($item) => (int)$item)->toArray();
		
		// Set staff default permissions
		// Give the minimum admin panel permissions to the role.
		$staffPermissionsArr = Permission::getStaffPermissions();
		$staffPermissions = Permission::whereIn('name', $staffPermissionsArr);
		if ($staffPermissions->count() > 0) {
			$staffPermissionsIds = collect($staffPermissions->get())->keyBy('id')->keys()->toArray();
			$permissionIds = array_merge($permissionIds, $staffPermissionsIds);
		}
		
		// Set the Super Admin default permissions (If needed)
		$role = Role::find($request->segment(3));
		if (!empty($role)) {
			// Get the Super Admin role
			$superAdminRole = Role::getSuperAdminRole();
			
			// If the role is the Super Admin role,
			// Then give the minimum permissions to the 'super-admin' role.
			if (strtolower($role->name) == strtolower($superAdminRole)) {
				$superAdminPermissionsArr = Permission::getSuperAdminPermissions();
				$superAdminPermissions = Permission::whereIn('name', $superAdminPermissionsArr);
				if ($superAdminPermissions->count() > 0) {
					$superAdminPermissionsIds = collect($superAdminPermissions->get())->keyBy('id')->keys()->toArray();
					$permissionIds = array_merge($permissionIds, $superAdminPermissionsIds);
				}
			}
		}
		
		// Update the request value
		// $request->request->set('permissions', $permissionIds);
		$request->merge(['permissions' => $permissionIds]);
		
		return $request;
	}
}
