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

namespace App\Services\User;

use App\Models\Permission;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use Illuminate\Http\JsonResponse;

trait Delete
{
	/**
	 * Close the User's Account
	 *
	 * @param $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function closeAccount($id): JsonResponse
	{
		// User account closure enabled?
		if (!isAccountClosureEnabled()) {
			return apiResponse()->forbidden(t('account_closure_disabled'));
		}
		
		// Get User
		$user = User::query()->withoutGlobalScopes([VerifiedScope::class])->where('id', $id)->first();
		
		if (empty($user)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		/** @var User $authUser */
		$authUser = request()->user() ?? auth(getAuthGuard())->user();
		
		// Check logged User
		// Get the User Personal Access Token Object
		$personalAccess = isFromApi()
			? $authUser->tokens()->where('id', getApiAuthToken())->first()
			: null;
		
		if (!empty($personalAccess)) {
			if ($personalAccess->tokenable_id != $user->id) {
				return apiResponse()->unauthorized();
			}
		} else {
			if ($authUser->getAuthIdentifier() != $user->id) {
				return apiResponse()->unauthorized();
			}
		}
		
		// This way can not delete admin users
		$isAdminUser = $user->can(Permission::getStaffPermissions());
		if ($isAdminUser) {
			return apiResponse()->forbidden(t('admin_users_cannot_be_deleted'));
		}
		
		// Close User's session (by revoking all the user's tokens)
		$user->tokens()->delete();
		
		// Delete User
		$user->delete();
		
		$message = t('your_account_has_been_deleted_1');
		
		return apiResponse()->noContentResource($message);
	}
}
