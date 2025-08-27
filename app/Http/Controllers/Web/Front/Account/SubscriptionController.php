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

namespace App\Http\Controllers\Web\Front\Account;

use App\Http\Controllers\Web\Front\Payment\HasPaymentRedirection;
use App\Http\Requests\Front\PackageRequest;
use App\Models\PaymentMethod;
use App\Models\Scopes\VerifiedScope;
use App\Models\User;
use App\Services\Payment\HasPaymentReferrers;
use App\Services\Payment\Subscription\SubscriptionPayment;
use App\Services\UserService;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class SubscriptionController extends AccountBaseController
{
	use HasPaymentReferrers;
	use SubscriptionPayment, HasPaymentRedirection;
	
	/**
	 * @param \App\Services\UserService $userService
	 */
	public function __construct(UserService $userService)
	{
		parent::__construct($userService);
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	public function commonQueries(): void
	{
		$this->getPaymentReferrersData();
		$this->setPaymentSettingsForSubscription();
	}
	
	/**
	 * @return \Illuminate\Contracts\View\View
	 */
	public function showForm()
	{
		// Get auth user
		$authUser = auth()->user();
		
		// Get User
		$user = null;
		if (!empty($authUser)) {
			$user = User::query()
				->with([
					'possiblePayment' => fn ($q) => $q->with('package'),
					'paymentEndingLater',
				])
				->withoutGlobalScopes([VerifiedScope::class])
				->where('id', $authUser->getAuthIdentifier())
				->first();
		}
		
		if (empty($user)) {
			abort(404, t('user_not_found'));
		}
		
		view()->share('user', $user);
		
		// Share the post's current active payment info (If exists)
		$this->getCurrentActivePaymentInfo($user);
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('update_my_subscription') . ' - ' . $appName;
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', t('update_my_subscription'));
		
		return view('front.account.subscription');
	}
	
	/**
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(PackageRequest $request)
	{
		// Get auth user
		$authUser = auth()->user();
		abort_if(empty($authUser), 404, t('user_not_found'));
		
		$userId = $authUser->getAuthIdentifier();
		
		// Add required data in the request for API
		$inputArray = [
			'payable_type' => 'User',
			'payable_id'   => $userId,
		];
		$request->merge($inputArray);
		
		// Check if the payment process has been triggered
		// ===| Make|send payment (if needed) |==============
		
		$user = $this->retrievePayableModel($request, $userId);
		abort_if(empty($user), 404, t('user_not_found'));
		
		$payResult = $this->isPaymentRequested($request, $user);
		if (data_get($payResult, 'success')) {
			$this->sendPayment($request, $user);
		}
		if (data_get($payResult, 'failure')) {
			flash(data_get($payResult, 'message'))->error();
		}
		
		// ===| If no payment is made (continue) |===========
		
		$isOfflinePayment = PaymentMethod::query()
			->where('name', 'offlinepayment')
			->where('id', $request->input('payment_method_id'))
			->exists();
		
		// Notification Message
		if (!$isOfflinePayment) {
			flash(t('no_payment_is_made'))->info();
		}
		
		// Get the next URL & Notification
		$nextUrl = urlGen()->accountOverview();
		
		return redirect()->to($nextUrl);
	}
}
