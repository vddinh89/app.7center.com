@php
	$packageType ??= null;
	$packages ??= collect();
	$paymentMethods ??= collect();
	
	$payment ??= [];
	$upcomingPayment ??= [];
	$package ??= []; // Selected package
	$selectedPackageId = data_get($package, 'id', data_get($payment, 'package.id', 0));
	
	$isPayabilityActivated = (
		!empty($packageType)
		&& isset($packages, $paymentMethods)
		&& $packages->count() > 0
		&& $paymentMethods->count() > 0
	);
	
	$doesPaymentExist = (
		!empty($payment)
		&& !empty(data_get($payment, 'package'))
		&& !empty(data_get($payment, 'paymentMethod'))
	);
	
	$tableHover = isSingleStepFormEnabled() ? '' : ' table-hover';
	$tdPadding = isSingleStepFormEnabled() ? ' py-3 px-0' : ' p-3';
@endphp
@if ($isPayabilityActivated)
	<div class="col-12 pb-0">
		<h3>
			@if ($packageType == 'promotion')
				<i class="fa-solid fa-certificate icon-color-1"></i> {{ t('promote_your_listing') }}
			@else
				<i class="fa-solid fa-certificate icon-color-1"></i> {{ t('upgrade_your_subscription') }}
			@endif
		</h3>
		<p>
			{{ ($packageType == 'promotion') ? t('promo_packages_hint') : t('subs_packages_hint') }}
		</p>
		@php
			$packageIdError = (isset($errors) && $errors->has('package_id')) ? ' is-invalid' : '';
		@endphp
		<div class="w-100 mb-0">
			<table id="packagesTable" class="table{{ $tableHover }} checkboxtable mb-0">
				@foreach ($packages as $package)
					@php
						$packageDisabledAttr = '';
						$badge = '';
						if ($doesPaymentExist) {
							if ($package->price > 0) {
								if ($package->currency_code == data_get($payment, 'package.currency_code')) {
									if ($package->price < data_get($payment, 'package.price')) {
										$badge = ' <span class="badge bg-warning">' . t('downgrade') . '</span>';
									}
									if ($package->price > data_get($payment, 'package.price')) {
										$badge = ' <span class="badge bg-success">' . t('upgrade') . '</span>';
									}
									if ($package->price === data_get($payment, 'package.price')) {
										$badge = '';
									}
								} else {
									$badge = '';
								}
							} else {
								$packageDisabledAttr = ' disabled';
								$badge = ' <span class="badge bg-danger">' . t('not_available') . '</span>';
							}
							
							if ($package->id == data_get($payment, 'package.id')) {
								$badge = ' <span class="badge bg-secondary">' . t('current') . '</span>';
								if (data_get($payment, 'active') == 0) {
									$badge .= ' <span class="badge bg-info">' . t('payment_pending') . '</span>';
								} else {
									$badge .= ' <span class="badge bg-info">' . data_get($payment, 'expiry_info') . '</span>';
								}
							}
						} else {
							if ($package->price > 0) {
								$badge = ' <span class="badge bg-success">' . t('upgrade') . '</span>';
							}
						}
					@endphp
					<tr>
						<td class="text-start align-middle{{ $tdPadding }}">
							@php
								$packageCheckedAttr = (old('package_id', $selectedPackageId) == $package->id)
														? ' checked'
														: (($package->price == 0) ? ' checked' : '');
							@endphp
							<div class="form-check">
								<input class="form-check-input package-selection{{ $packageIdError }}"
									   type="radio"
									   name="package_id"
									   id="packageId-{{ $package->id }}"
									   value="{{ $package->id }}"
									   data-name="{{ $package->name }}"
									   data-currency-symbol="{{ $package->currency->symbol }}"
									   data-currency-in-left="{{ $package->currency->in_left }}"
										{{ $packageCheckedAttr }} {{ $packageDisabledAttr }}
								>
								<label class="form-check-label mb-0{{ $packageIdError }}">
									<strong class=""
											data-bs-placement="right"
											data-bs-toggle="tooltip"
											title="{!! $package->description_string !!}"
									>{!! $package->name . $badge !!} </strong>
								</label>
							</div>
						</td>
						<td class="text-end align-middle{{ $tdPadding }}">
							<p id="price-{{ $package->id }}" class="mb-0">
								@if ($package->currency->in_left == 1)
									<span class="price-currency">{!! $package->currency->symbol !!}</span>
								@endif
								<span class="price-int">{{ $package->price }}</span>
								@if ($package->currency->in_left == 0)
									<span class="price-currency">{!! $package->currency->symbol !!}</span>
								@endif
							</p>
						</td>
					</tr>
				@endforeach
				
				<tr>
					<td class="text-start align-middle{{ $tdPadding }}">
						@include('front.payment.payment-methods')
					</td>
					<td class="text-end align-middle{{ $tdPadding }}">
						<p class="mb-0">
							<strong>
								{{ t('Payable Amount') }}:
								<span class="price-currency amount-currency currency-in-left" style="display: none;"></span>
								<span class="payable-amount">0</span>
								<span class="price-currency amount-currency currency-in-right" style="display: none;"></span>
							</strong>
						</p>
					</td>
				</tr>
				
				@if ($doesPaymentExist)
					<tr>
						<td class="text-start align-middle{{ $tdPadding }}" colspan="2">
							{{-- accept_package_renewal --}}
							@include('helpers.forms.fields.checkbox', [
								'label'    => t('accept_package_renewal_label'),
								'id'       => 'acceptPackageRenewal',
								'name'     => 'accept_package_renewal',
								'required' => false,
								'value'    => null,
								'hint'     => t('accept_package_renewal_hint', ['date' => data_get($upcomingPayment, 'period_start_formatted')]),
							])
						</td>
					</tr>
				@endif
				
			</table>
		</div>
	</div>
	
	@include('front.payment.payment-methods.plugins')

@endif
