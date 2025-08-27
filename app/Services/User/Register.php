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

use App\Http\Requests\Front\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

trait Register
{
	/**
	 * Register a new user account.
	 *
	 * @param UserRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	protected function register(UserRequest $request): JsonResponse
	{
		// Conditions to Verify User's Email or Phone
		$emailVerificationRequired = config('settings.mail.email_verification') == '1' && $request->filled('email');
		$phoneVerificationRequired = config('settings.sms.phone_verification') == '1' && $request->filled('phone');
		
		// New User
		$user = new User();
		$input = $request->only($user->getFillable());
		foreach ($input as $key => $value) {
			if ($request->has($key)) {
				$user->{$key} = $value;
			}
		}
		
		if ($request->filled('password')) {
			if (isset($input['password'])) {
				$user->password = Hash::make($input['password']);
			}
		}
		
		if ($request->anyFilled(['email', 'phone'])) {
			$user->email_verified_at = now();
			$user->phone_verified_at = now();
			
			// Email verification key generation
			if ($emailVerificationRequired) {
				$user->email_token = generateToken(hashed: true);
				$user->email_verified_at = null;
			}
			
			// Mobile activation key generation
			if ($phoneVerificationRequired) {
				$user->phone_token = generateOtp(defaultOtpLength());
				$user->phone_verified_at = null;
			}
		}
		
		// Save
		$user->save();
		
		$data = [
			'success' => true,
			'message' => t('your_account_has_been_created'),
			'result'  => (new UserResource($user))->toArray($request),
		];
		
		$extra = [];
		
		// Send Verification Link or Code
		if ($emailVerificationRequired || $phoneVerificationRequired) {
			
			// Email
			if ($emailVerificationRequired) {
				// Send Verification Link by Email
				$extra['sendEmailVerification'] = $this->sendEmailVerification('users', $user);
				if (
					array_key_exists('success', $extra['sendEmailVerification'])
					&& array_key_exists('message', $extra['sendEmailVerification'])
					&& !$extra['sendEmailVerification']['success']
				) {
					$extra['mail']['success'] = $extra['sendEmailVerification']['success'];
					$extra['mail']['message'] = $extra['sendEmailVerification']['message'];
				}
			}
			
			// Phone
			if ($phoneVerificationRequired) {
				// Send Verification Code by SMS
				$extra['sendPhoneVerification'] = $this->sendPhoneVerification('users', $user);
				if (
					array_key_exists('success', $extra['sendPhoneVerification'])
					&& array_key_exists('message', $extra['sendPhoneVerification'])
					&& !$extra['sendPhoneVerification']['success']
				) {
					$extra['mail']['success'] = $extra['sendPhoneVerification']['success'];
					$extra['mail']['message'] = $extra['sendPhoneVerification']['message'];
				}
			}
			
			// Once Verification Notification is sent (containing Link or Code),
			// Send Confirmation Notification, when user clicks on the Verification Link or enters the Verification Code.
			// Done in the "app/Observers/UserObserver.php" file.
			
		} else {
			
			// Redirect to the user area If Email or Phone verification is not required
			if (auth()->loginUsingId($user->id)) {
				if (isFromApi()) {
					// Create the API access token
					$defaultDeviceName = doesRequestIsFromWebClient() ? 'Website' : 'Other Client';
					$deviceName = $request->input('device_name', $defaultDeviceName);
					$token = $user->createToken($deviceName);
					
					$extra['authToken'] = $token->plainTextToken;
					$extra['tokenType'] = 'Bearer';
				}
			}
			
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
}
