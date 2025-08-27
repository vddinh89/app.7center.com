<div class="modal fade" id="securityTips" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			
			<div class="modal-header px-3">
				<h5 class="modal-title fs-5 fw-bold" id="securityTipsLabel">
					{{ t('phone_number') }}
				</h5>
				
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ t('Close') }}"></button>
			</div>
			
			@php
				$phoneModal = '';
				$phoneModalLink = '';
				// If the 'hide_phone_number' option is disabled, append phone number in modal
				if (config('settings.listing_page.hide_phone_number') == '') {
					if (isset($post, $post->phone)) {
						$phoneModal = $post->phone;
						$phoneModalLink = 'tel:' . $post->phone;
					}
				}
			@endphp
			
			<div class="modal-body">
				<div class="row">
					<div class="col-12 text-center">
						<div id="phoneModal" class="p-4 border-2 border-danger bg-body-tertiary rounded h2 fw-bold text-primary">
							{{ $phoneModal }}
						</div>
					</div>
					<div class="col-12 mt-4">
						<h3 class="text-danger fw-bold">
							<i class="fa-solid fa-triangle-exclamation"></i> {!! t('security_tips_title') !!}
						</h3>
					</div>
					<div class="col-12">
						{!! t('security_tips_text', ['appName' => config('app.name')]) !!}
					</div>
				</div>
			</div>
			
			<div class="modal-footer">
				<a id="phoneModalLink" href="{{ $phoneModalLink }}" class="btn btn-primary">
					<i class="fa-solid fa-mobile-screen-button"></i> {{ t('call_now') }}
				</a>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ t('Close') }}</button>
			</div>
			
		</div>
	</div>
</div>
