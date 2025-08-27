@php
	$hideOnMobile ??= '';
	$spaceClass = 'p-0 mt-lg-4 mt-md-3 mt-3';
@endphp
@if (isset($paddingTopExists))
	@if (isset($firstSection) && !$firstSection)
		<div class="{{ $spaceClass . $hideOnMobile }}"></div>
	@else
		@if (!$paddingTopExists)
			<div class="{{ $spaceClass . $hideOnMobile }}"></div>
		@endif
	@endif
@else
	<div class="{{ $spaceClass . $hideOnMobile }}"></div>
@endif
