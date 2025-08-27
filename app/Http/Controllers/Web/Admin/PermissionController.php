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

use App\Helpers\Common\DBUtils;
use App\Http\Controllers\Web\Admin\Panel\PanelController;
use App\Http\Requests\Admin\PermissionRequest as StoreRequest;
use App\Http\Requests\Admin\PermissionRequest as UpdateRequest;
use App\Http\Requests\Admin\Request;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PermissionController extends PanelController
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
		$this->xPanel->setModel($permissionModel);
		$this->xPanel->setRoute(urlGen()->adminUri('permissions'));
		$this->xPanel->setEntityNameStrings(trans('admin.permission_singular'), trans('admin.permission_plural'));
		
		$this->xPanel->addButtonFromModelFunction('top', 'create_default_entries', 'createDefaultEntriesButton', 'end');
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
			'name'  => 'name',
			'label' => trans('admin.name'),
			'type'  => 'text',
		]);
		$this->xPanel->addColumn([
			// n-n relationship (with pivot table)
			'label'     => trans('admin.roles_have_permission'),
			'type'      => 'select_multiple',
			'name'      => 'roles',
			'entity'    => 'roles',
			'attribute' => 'name',
			'model'     => $roleModel,
			'pivot'     => true,
		]);
		
		// FIELDS
		$this->xPanel->addField([
			'name'    => 'name',
			'label'   => trans('admin.name'),
			'type'    => 'select2_from_array',
			'options' => Permission::defaultPermissions(),
		], 'create');
		$permission = Permission::find(request()->segment(3));
		if (!empty($permission)) {
			$this->xPanel->addField([
				'name'  => 'name_html',
				'type'  => 'custom_html',
				'value' => '<h3><strong>' . trans('admin.permission') . '</strong>: ' . $permission->name . '</h3>',
			], 'update');
		}
		$this->xPanel->addField([
			'label'     => trans('admin.roles'),
			'type'      => 'checklist',
			'name'      => 'roles',
			'entity'    => 'roles',
			'attribute' => 'name',
			'model'     => $roleModel,
			'pivot'     => true,
		]);
		
		if (!config('larapen.admin.allow_permission_create')) {
			$this->xPanel->denyAccess('create');
		}
		if (!config('larapen.admin.allow_permission_update')) {
			$this->xPanel->denyAccess('update');
		}
		if (!config('larapen.admin.allow_permission_delete')) {
			$this->xPanel->denyAccess('delete');
		}
	}
	
	public function store(StoreRequest $request): RedirectResponse
	{
		$request = $this->setPermissionDefaultRoles($request);
		
		return parent::storeCrud($request);
	}
	
	public function update(UpdateRequest $request): RedirectResponse
	{
		$request = $this->setPermissionDefaultRoles($request);
		
		return parent::updateCrud($request);
	}
	
	/**
	 * Auto-creation of default permissions
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function createDefaultEntries(): RedirectResponse
	{
		$success = false;
		
		$aclTableNames = config('permission.table_names');
		
		// Get all permissions
		if (isset($aclTableNames['permissions'])) {
			$permissions = Permission::defaultPermissions();
			if (!empty($permissions)) {
				DB::statement('ALTER TABLE ' . DBUtils::table($aclTableNames['permissions']) . ' AUTO_INCREMENT = 1;');
				foreach ($permissions as $permission) {
					$doesPermissionExist = Permission::query()->where('name', '=', $permission)->exists();
					if (!$doesPermissionExist) {
						$entry = new Permission();
						$entry->name = $permission;
						$entry->save();
						
						$success = true;
					}
				}
			}
		}
		
		if ($success) {
			$message = trans('admin.The default permissions were been created');
			notification($message, 'success');
		} else {
			$message = trans('admin.Default permissions have already been created');
			notification($message, 'warning');
		}
		
		return redirect()->back();
	}
	
	/**
	 * Set permission's default (or required) roles
	 *
	 * @param \App\Http\Requests\Admin\Request $request
	 * @return \App\Http\Requests\Admin\Request
	 */
	private function setPermissionDefaultRoles(Request $request): Request
	{
		// Get request roles
		$roleIds = $request->input('roles');
		$roleIds = collect($roleIds)->map(fn ($item, $key) => (int)$item)->toArray();
		
		// Set the 'super-admin' role for the permission (if needed),
		$permission = Permission::find($request->segment(3));
		if (!empty($permission)) {
			// Get all the default Super Admin permissions
			$superAdminPermissionsArr = Permission::getSuperAdminPermissions();
			$superAdminPermissionsArrLower = collect($superAdminPermissionsArr)
				->map(fn ($item, $key) => strtolower($item))
				->toArray();
			
			// If the permission is a Super Admin permission,
			// Then assign it to the 'super-admin' role.
			if (in_array(strtolower($permission->name), $superAdminPermissionsArrLower)) {
				$superAdminRoles = Role::query()->where('name', '=', Role::getSuperAdminRole());
				if ($superAdminRoles->count() > 0) {
					$superAdminRolesIds = collect($superAdminRoles->get())->keyBy('id')->keys()->toArray();
					$roleIds = array_merge($roleIds, $superAdminRolesIds);
				}
			}
		}
		
		// Update the request value
		// $request->request->set('roles', $roleIds);
		$request->merge(['roles' => $roleIds]);
		
		return $request;
	}
}
