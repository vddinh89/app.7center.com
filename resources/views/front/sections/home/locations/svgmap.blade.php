@php
	// Selected Skin Values (variables):
	$primaryBgColor = 'var(--bs-primary)';
	$primaryBgSubtleColor = 'var(--bs-primary-bg-subtle)';
	
	$sectionOptions = $locationsOptions ?? [];
	
	// Get Admin Map's values
	$mapPath = config('larapen.core.maps.path') . config('country.icode') . '.svg';
	$mapCanBeShown = (file_exists($mapPath) && data_get($sectionOptions, 'enable_map') == '1');
	$mapBackgroundColor = data_get($sectionOptions, 'map_background_color') ?? 'transparent';
	$mapBorder = data_get($sectionOptions, 'map_border') ?? ($primaryBgColor ?? '#c7c5c1');
	$mapHoverBorder = data_get($sectionOptions, 'map_hover_border') ?? ($primaryBgColor ?? '#c7c5c1');
	$mapBorderWidth = data_get($sectionOptions, 'map_border_width') ?? 4;
	$mapColor = data_get($sectionOptions, 'map_color') ?? ($primaryBgSubtleColor ?? '#f2f0eb');
	$mapColorHover = data_get($sectionOptions, 'map_hover') ?? ($primaryBgColor ?? '#4682B4');
	$mapWidth = data_get($sectionOptions, 'map_width') ?? 300;
	$mapHeight = data_get($sectionOptions, 'map_height') ?? 300;
	
	$mapWidth = forceToInt($mapWidth) . 'px';
	$mapHeight = forceToInt($mapHeight) . 'px';
	
	$svgMapSearchPage = urlQuery(urlGen()->search())
		->removeParameters(['country', 'r'])
		->toString();
@endphp

@if ($mapCanBeShown)
	@if (!$locCanBeShown)
		<div class="row">
			<div class="col-xl-12 col-md-12 col-sm-12">
				<h2 class="title-3 pt-1 pb-3 px-3" style="white-space: nowrap;">
					<i class="fa-solid fa-location-dot"></i>&nbsp;{{ t('Choose a state or region') }}
				</h2>
			</div>
		</div>
	@endif
	<div class="{{ $rightClassCol }} text-center">
		<div id="countryMap" class="page-sidebar col-thin-left p-0 m-auto">&nbsp;</div>
	</div>
@endif

@section('after_scripts')
	@parent
	<script src="{{ url('assets/plugins/twism/jquery.twism.js') }}"></script>
	<script>
		onDocumentReady((event) => {
			@if ($mapCanBeShown)
				$('#countryMap').css('cursor', 'pointer');
				$('#countryMap').twism("create",
				{
					map: "custom",
					customMap: '{{ config('larapen.core.maps.urlBase') . config('country.icode') . '.svg' }}',
					backgroundColor: '{{ $mapBackgroundColor }}',
					border: '{{ $mapBorder }}',
					hoverBorder: '{{ $mapHoverBorder }}',
					borderWidth: {{ $mapBorderWidth }},
					color: '{{ $mapColor }}',
					width: '{{ $mapWidth }}',
					height: '{{ $mapHeight }}',
					click: function(region) {
						if (!isDefined(region) || !isString(region) || isBlankString(region)) {
							return false;
						}
						region = rawurlencode(region);
						let searchPage = '{{ $svgMapSearchPage }}';
						let queryStringSeparator = searchPage.indexOf('?') !== -1 ? '&' : '?';
						@if (isMultiCountriesUrlsEnabled())
							searchPage = searchPage + queryStringSeparator + 'country={{ config('country.code') }}&r=' + region;
						@else
							searchPage = searchPage + queryStringSeparator + 'r=' + region;
						@endif
						redirect(searchPage);
					},
					hover: function(regionId) {
						if (isDefined(regionId)) {
							let selectedIdObj = document.getElementById(regionId);
							if (isDefined(selectedIdObj)) {
								selectedIdObj.style.fill = '{{ $mapColorHover }}';
							}
						}
					},
					unhover: function(regionId) {
						if (isDefined(regionId)) {
							let selectedIdObj = document.getElementById(regionId);
							if (isDefined(selectedIdObj)) {
								selectedIdObj.style.fill = '{{ $mapColor }}';
							}
						}
					}
				});
			@endif
		});
	</script>
@endsection
