{{--
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
--}}
@extends('front.layouts.master')

@php
	$promoPackages ??= [];
	$promoPackagesErrorMessage ??= '';
	
	$subsPackages ??= [];
	$subsPackagesErrorMessage ??= '';
	
	$isAllTypesOfPackageExist = (!empty($promoPackages) && !empty($subsPackages));
	$isAllTypesOfPackageNotExist = (empty($promoPackages) && empty($subsPackages));
	$errorMessage = t('no_packages_found');
	
	// Get the active tab
	$defaultPackageType = config('settings.listing_form.default_package_type');
	$packageType = request()->query('type', $defaultPackageType);
	
	// Get the active tab (by checking if its packages exist)
	$packageType = ($packageType == 'promotion' && !empty($promoPackages)) ? 'promotion' : 'subscription';
	$packageType = ($packageType == 'subscription' && !empty($subsPackages)) ? 'subscription' : 'promotion';
	
	// Set the active tab classes
	$promoLinkClass = ($packageType == 'promotion') ? 'active' : '';
	$subsLinkClass = ($packageType == 'subscription') ? 'active' : '';
	$promoContentClass = !empty($promoLinkClass) ? 'show ' . $promoLinkClass : '';
	$subsContentClass = !empty($subsLinkClass) ? 'show ' . $subsLinkClass : '';
@endphp
@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container" id="pricing">
			
			@include('helpers.titles.title-2', ['title' => t('Pricing')])
			
			@if (!$isAllTypesOfPackageNotExist)
				@if ($isAllTypesOfPackageExist)
					<ul class="nav nav-pills justify-content-center mb-3" id="pills-tab" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link {{ $promoLinkClass }}"
							        id="pills-promotion-tab"
							        data-bs-toggle="pill"
							        data-bs-target="#pills-promotion"
							        type="button"
							        role="tab"
							        aria-controls="pills-promotion"
							        aria-selected="false"
							>{{ t('promo_packages_tab') }}</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link {{ $subsLinkClass }}"
							        id="pills-subscription-tab"
							        data-bs-toggle="pill"
							        data-bs-target="#pills-subscription"
							        type="button"
							        role="tab"
							        aria-controls="pills-subscription"
							        aria-selected="true"
							>{{ t('subs_packages_tab') }}</button>
						</li>
					</ul>
				@endif
				
				<div class="tab-content" id="pills-tabContent">
					@if (!empty($promoPackages))
						<div class="tab-pane fade {{ $promoContentClass }}"
						     id="pills-promotion"
						     role="tabpanel"
						     aria-labelledby="pills-promotion-tab"
						>
							@include('front.pages.pricing.promo-packages', [
								'packages' => $promoPackages,
								'message'  => $promoPackagesErrorMessage
							])
						</div>
					@endif
					@if (!empty($subsPackages))
						<div class="tab-pane fade {{ $subsContentClass }}"
						     id="pills-subscription"
						     role="tabpanel"
						     aria-labelledby="pills-subscription-tab"
						>
							@include('front.pages.pricing.subs-packages', [
								'packages' => $subsPackages,
								'message'  => $subsPackagesErrorMessage
							])
						</div>
					@endif
				</div>
			@else
				<div class="row mt-5 mb-md-5 justify-content-center">
					<div class="col-md-6 col-sm-12 text-center">
						<div class="card bg-body-secondary">
							<div class="card-body">
								{{ $errorMessage ?? null }}
							</div>
						</div>
					</div>
				</div>
			@endif
			
		</div>
	</div>
	
	@includeWhen(!auth()->check(), 'auth.login.partials.modal')
@endsection
