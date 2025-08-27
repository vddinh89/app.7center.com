@php
	$sectionOptions = $textAreaOptions ?? [];
	$sectionData ??= [];
	
	// Fallback Language
	$textTitle = data_get($sectionOptions, 'title_' . config('appLang.code'));
	$textTitle = replaceGlobalPatterns($textTitle);
	
	$textBody = data_get($sectionOptions, 'body_' . config('appLang.code'));
	$textBody = replaceGlobalPatterns($textBody);
	
	// Current Language
	if (!empty(data_get($sectionOptions, 'title_' . config('app.locale')))) {
		$textTitle = data_get($sectionOptions, 'title_' . config('app.locale'));
		$textTitle = replaceGlobalPatterns($textTitle);
	}
	
	if (!empty(data_get($sectionOptions, 'body_' . config('app.locale')))) {
		$textBody = data_get($sectionOptions, 'body_' . config('app.locale'));
		$textBody = replaceGlobalPatterns($textBody);
	}
	
	$hideOnMobile = (data_get($sectionOptions, 'hide_on_mobile') == '1') ? ' d-none d-md-block' : '';
@endphp
@if (!empty($textTitle) || !empty($textBody))
	@include('front.sections.spacer', ['hideOnMobile' => $hideOnMobile])
	
	<div class="container{{ $hideOnMobile }}">
		<div class="card">
			<div class="card-body">
				@if (!empty($textTitle))
					<h4 class="card-title fw-bold border-bottom pb-3 mb-3">{{ $textTitle }}</h4>
				@endif
				@if (!empty($textBody))
					<div>{!! $textBody !!}</div>
				@endif
			</div>
		</div>
	</div>
@endif
