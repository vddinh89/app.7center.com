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
	$authUser ??= auth()->user();
	$lastLoginAt = $authUser->last_login_at ?? null;
	$lastLoginAtFormatted = \App\Helpers\Common\Date::format($lastLoginAt, 'datetime');
@endphp
@section('content')
	@include('front.common.spacer')
	<div class="main-container">
		<div class="container">
			<div class="row">
				<div class="col-md-3">
					@include('front.account.partials.sidebar')
				</div>
				
				<div class="col-md-9">
					
					@include('flash::message')
					
					@if (isset($errors) && $errors->any())
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ t('Close') }}"></button>
							<h5><strong>{{ t('validation_errors_title') }}</strong></h5>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{!! $error !!}</li>
								@endforeach
							</ul>
						</div>
					@endif
					
					@include('front.account.partials.header', [
						'headerTitle' => '<i class="bi bi-person-lines-fill"></i> ' . trans('auth.overview')
					])
					
					<div class="container border rounded bg-body-tertiary p-4 p-lg-3 p-md-2">
						<div class="row mb-3">
							<div class="col-12">
								<h4 class="p-0">
									{{ t('Hello') }} {{ $authUser->name }}!
								</h4>
								<span class="small text-secondary">
	                                {{ t('You last logged in at') }}: {!! $lastLoginAtFormatted !!}
	                            </span>
							</div>
						</div>
						
						@include('front.account.partials.overview-stats')
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
