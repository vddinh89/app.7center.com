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

use App\Helpers\Common\Files\FileSys;
use App\Http\Requests\Front\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

trait Update
{
	/**
	 * @param $id
	 * @param \App\Http\Requests\Front\UserRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function updateDetails($id, UserRequest $request): JsonResponse
	{
		/** @var User $user */
		$user = User::query()->withoutGlobalScopes([VerifiedScope::class])->where('id', $id)->first();
		
		if (empty($user)) {
			return apiResponse()->notFound(t('user_not_found'));
		}
		
		/** @var User $authUser */
		$authUser = request()->user() ?? auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		// Check logged User
		// Get the User Personal Access Token Object
		$personalAccess = isFromApi() ? $authUser->tokens()->where('id', getApiAuthToken())->first() : null;
		if (!empty($personalAccess)) {
			if ($personalAccess->tokenable_id != $user->id) {
				return apiResponse()->unauthorized();
			}
		} else {
			if ($authUser->getAuthIdentifier() != $user->id) {
				return apiResponse()->unauthorized();
			}
		}
		
		// Check if these fields have changed
		$emailChanged = $request->filled('email') && $request->input('email') != $user->email;
		$phoneChanged = $request->filled('phone') && $request->input('phone') != $user->phone;
		$usernameChanged = $request->filled('username') && $request->input('username') != $user->username;
		
		// Conditions to Verify User's Email or Phone
		$emailVerificationRequired = config('settings.mail.email_verification') == '1' && $emailChanged;
		$phoneVerificationRequired = config('settings.sms.phone_verification') == '1' && $phoneChanged;
		
		// Update User
		$input = $request->only($user->getFillable());
		
		$protectedColumns = ['username', 'password'];
		$protectedColumns = ($request->filled('auth_field'))
			? array_merge($protectedColumns, [$request->input('auth_field')])
			: array_merge($protectedColumns, ['email', 'phone']);
		
		foreach ($input as $key => $value) {
			if ($request->has($key)) {
				if (in_array($key, $protectedColumns) && empty($value)) {
					continue;
				}
				
				if ($key == 'photo_path' && FileSys::isUploadedFile($value)) {
					continue;
				}
				
				$user->{$key} = $value;
			}
		}
		
		$user->gender_id = (int)$user->gender_id;
		
		// Checkboxes
		$user->phone_hidden = (int)$request->input('phone_hidden');
		$user->disable_comments = (int)$request->input('disable_comments');
		$user->accept_marketing_offers = (int)$request->input('accept_marketing_offers');
		if ($request->filled('accept_terms')) {
			$user->accept_terms = (int)$request->input('accept_terms');
		}
		
		// Other fields
		if ($request->filled('password')) {
			if (isset($input['password'])) {
				$user->password = Hash::make($input['password']);
			}
		}
		
		// Email verification key generation
		if ($emailVerificationRequired) {
			$user->email_token = generateToken(hashed: true);
			$user->email_verified_at = null;
		}
		
		// Phone verification key generation
		if ($phoneVerificationRequired) {
			$user->phone_token = generateOtp(defaultOtpLength());
			$user->phone_verified_at = null;
		}
		
		$extra = [];
		
		// Don't log out the User (See the User model)
		$extra['emailOrPhoneChanged'] = ($emailVerificationRequired || $phoneVerificationRequired);
		
		// Save
		$user->save();
		
		// Unlock the user if his account was locked
		if ($extra['emailOrPhoneChanged']) {
			$user->resetVerificationLockout();
		}
		
		$data = [
			'success' => true,
			'message' => t('account_details_has_updated_successfully'),
			'result'  => (new UserResource($user))->toArray($request),
		];
		
		// Send an Email Verification message
		if ($emailVerificationRequired) {
			$extra['sendEmailVerification'] = $this->sendEmailVerification('users', $user);
			if (
				array_key_exists('success', $extra['sendEmailVerification'])
				&& array_key_exists('message', $extra['sendEmailVerification'])
			) {
				$vMessage = getModelVerificationMessage($user, 'email');
				$data['message'] = $data['message'] . ' ' . $vMessage;
				$extra['mail']['success'] = $extra['sendEmailVerification']['success'];
				$extra['mail']['message'] = $extra['sendEmailVerification']['message'];
			}
		}
		
		// Send a Phone Verification message
		if ($phoneVerificationRequired) {
			$extra['sendPhoneVerification'] = $this->sendPhoneVerification('users', $user);
			if (
				array_key_exists('success', $extra['sendPhoneVerification'])
				&& array_key_exists('message', $extra['sendPhoneVerification'])
			) {
				$vMessage = getModelVerificationMessage($user, 'phone');
				$data['message'] = $data['message'] . ' ' . $vMessage;
				$extra['mail']['success'] = $extra['sendPhoneVerification']['success'];
				$extra['mail']['message'] = $extra['sendPhoneVerification']['message'];
			}
		}
		
		// User's Photo
		$extra['photo'] = [];
		if ($request->hasFile('photo_path')) {
			// Update User's Photo
			$extra['photo'] = $this->updateUserPhoto($user->id, $request)->getData(true);
		} else {
			// Remove User's Photo
			$photoRemovalRequested = ($request->filled('remove_photo') && $request->input('remove_photo'));
			if ($photoRemovalRequested) {
				$extra['photo'] = $this->removeUserPhoto($user->id)->getData(true);
			}
		}
		if (array_key_exists('success', $extra['photo'])) {
			// Update the '$data' result value If a photo is uploaded successfully
			if ($extra['photo']['success']) {
				if (!empty($extra['photo']['result'])) {
					$data['result'] = $extra['photo']['result'];
					unset($extra['photo']['result']);
				}
			}
			
			// Update the '$data' infos If error found during the photo upload
			if (!$extra['photo']['success']) {
				if (array_key_exists('message', $extra['photo'])) {
					$data['success'] = $extra['photo']['success'];
					$data['message'] = $extra['photo']['message'];
					unset($extra['photo']['success']);
					unset($extra['photo']['message']);
				}
			}
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->updated($data);
	}
}
