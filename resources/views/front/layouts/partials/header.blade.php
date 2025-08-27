@php
	$countries ??= collect();
	
	// Search parameters
	$queryString = request()->getQueryString();
	$queryString = !empty($queryString) ? '?' . $queryString : '';
	
	$showCountryFlagNextLogo = (config('settings.localization.show_country_flag') == 'in_next_logo');
	
	// Check if the Multi-Countries selection is enabled
	$multiCountryIsEnabled = false;
	$multiCountryLabel = '';
	if ($showCountryFlagNextLogo) {
		if (!empty(config('country.code'))) {
			if ($countries->count() > 1) {
				$multiCountryIsEnabled = true;
				$multiCountryLabel = 'title="' . t('select_country') . '"';
			}
		}
	}
	
	// Country
	$countryName = config('country.name');
	$countryFlag24Url = config('country.flag24_url');
	$countryFlag32Url = config('country.flag32_url');
	
	// Logo
	$logoFactoryUrl = config('larapen.media.logo-factory');
	$logoDarkUrl = config('settings.app.logo_dark_url', $logoFactoryUrl);
	$logoLightUrl = config('settings.app.logo_light_url', $logoFactoryUrl);
	$logoAlt = strtolower(config('settings.app.name'));
	$logoWidth = (int)config('settings.upload.img_resize_logo_width', 454);
	$logoHeight = (int)config('settings.upload.img_resize_logo_height', 80);
	
	// Logo Label
	$logoLabel = '';
	if ($multiCountryIsEnabled) {
		$logoLabel = config('settings.app.name') . (!empty($countryName) ? ' ' . $countryName : '');
	}
	
	// User Menu
	$authUser = auth()->check() ? auth()->user() : null;
	$userMenu ??= collect();
	
	// Links CSS Class
	$linkClass = linkClass('body-emphasis');
	
	// Theme Preference (light/dark/system)
	$showIconOnly ??= false;
	
	// Header & Navbar design parameters
	$defaultBgColorClass = 'bg-body-tertiary';
	$borderBottomClass = 'border-bottom';
	$animationStartingClass = 'navbar-sticky';
	$shadowClass = 'shadow';
	
	$bgColorClass = config('settings.style.header_background_class') ?? $defaultBgColorClass;
	$bgColorClass = !empty($bgColorClass) ? $bgColorClass : $defaultBgColorClass;
	
	$isHeaderDarkThemeEnabled = (config('settings.style.dark_header') == '1');
	$headerThemeAttr = $isHeaderDarkThemeEnabled ? ' data-bs-theme="dark"' : '';
	
	$isHeaderAnimationEnabled = (config('settings.style.header_animation') == '1');
	$animationClass = $isHeaderAnimationEnabled ? " {$animationStartingClass}" : '';
	
	$isHeaderShadowEnabled = (config('settings.style.header_shadow') == '1');
	$shadowClassEnabled = $isHeaderShadowEnabled ? " {$shadowClass}" : '';
	
	$isFixedTopHeader = (config('settings.style.header_fixed_top') == '1');
	$fixedTopClass = $isFixedTopHeader ? ' fixed-top' : '';
	
	$navbarClass = "{$fixedTopClass}{$shadowClassEnabled}{$animationClass} {$bgColorClass} {$borderBottomClass}";
	
	$isFullWidthHeader = (config('settings.style.header_full_width') == '1');
	$containerClass = $isFullWidthHeader ? 'container-fluid' : 'container';
	
	// Header Highlighted Button
	$headerHighlightedBtnLink = config('settings.style.header_highlighted_btn_link');
	$isHeaderHighlightedBtnOutline = (config('settings.style.header_highlighted_btn_outline') == '1');
	$headerHighlightedBtnClass = config('settings.style.header_highlighted_btn_class');
	$headerHighlightedBtnClass = !empty($headerHighlightedBtnClass)
		? 'btn ' . (
			$isHeaderHighlightedBtnOutline
				? str_replace('btn-', 'btn-outline-', $headerHighlightedBtnClass)
				: $headerHighlightedBtnClass
			)
		: null;
	$listingCreationBtnClass = ($headerHighlightedBtnLink == 'listingCreationLink') ? $headerHighlightedBtnClass : '';
	$userMenuBtnClass = ($headerHighlightedBtnLink == 'userMenuLink') ? $headerHighlightedBtnClass : '';
	$listingCreationBtnClass = !empty($listingCreationBtnClass) ? $listingCreationBtnClass : "nav-link $linkClass";
	$userMenuBtnClass = !empty($userMenuBtnClass) ? $userMenuBtnClass : "nav-link $linkClass";
@endphp
<header{!! $headerThemeAttr !!}>
	{{-- navbar fixed-top sticky-top --}}
	<nav class="navbar{{ $navbarClass }} navbar-expand-xl" role="navigation" id="mainNavbar">
		<div class="{{ $containerClass }}">
			
			{{-- Logo --}}
			<a href="{{ url('/') }}" class="navbar-brand logo logo-title">
				<img src="{{ $logoDarkUrl }}"
				     alt="{{ $logoAlt }}"
				     class="main-logo dark-logo"
				     data-bs-placement="bottom"
				     data-bs-toggle="tooltip"
				     title="{!! $logoLabel !!}"
				     style="max-width: {{ $logoWidth }}px; max-height: {{ $logoHeight }}px; width:auto;"
				/>
				<img src="{{ $logoLightUrl }}"
				     alt="{{ $logoAlt }}"
				     class="main-logo light-logo"
				     data-bs-placement="bottom"
				     data-bs-toggle="tooltip"
				     title="{!! $logoLabel !!}"
				     style="max-width: {{ $logoWidth }}px; max-height: {{ $logoHeight }}px; width:auto;"
				/>
			</a>
			
			{{-- Toggle Nav (Mobile) --}}
			<button class="navbar-toggler float-end"
			        type="button"
			        data-bs-toggle="collapse"
			        data-bs-target="#navbarNav"
			        aria-controls="navbarNav"
			        aria-expanded="false"
			        aria-label="Toggle navigation"
			>
				<span class="navbar-toggler-icon"></span>
			</button>
			
			<div class="collapse navbar-collapse" id="navbarNav">
				<ul class="navbar-nav me-md-auto">
					{{-- Country Flag --}}
					@if ($showCountryFlagNextLogo)
						@if (!empty($countryFlag32Url))
							<li class="nav-item flag-menu country-flag mb-xl-0 mb-2"
							    data-bs-toggle="tooltip"
							    data-bs-placement="{{ (config('lang.direction') == 'rtl') ? 'bottom' : 'right' }}" {!! $multiCountryLabel !!}
							>
								@if ($multiCountryIsEnabled)
									<a class="nav-link p-0 {{ $linkClass }}" data-bs-toggle="modal" data-bs-target="#selectCountry" style="cursor: pointer;">
										<img class="flag-icon mt-1" src="{{ $countryFlag32Url }}" alt="{{ $countryName }}">
										<i class="bi bi-chevron-down float-end mt-1 mx-2"></i>
									</a>
								@else
									<a class="nav-link p-0" style="cursor: default;">
										<img class="flag-icon" src="{{ $countryFlag32Url }}" alt="{{ $countryName }}">
									</a>
								@endif
							</li>
						@endif
					@endif
				</ul>
				
				<ul class="navbar-nav ms-auto">
					@if (config('plugins.currencyexchange.installed'))
						@include('currencyexchange::select-currency')
					@endif
					
					@if (config('settings.listings_list.display_browse_listings_link'))
						<li class="nav-item">
							@php
								$currDisplay = config('settings.listings_list.display_mode');
								$browseListingsIconClass = 'bi bi-grid-fill';
								if ($currDisplay == 'make-list') {
									$browseListingsIconClass = 'fa-solid fa-list';
								}
								if ($currDisplay == 'make-compact') {
									$browseListingsIconClass = 'fa-solid fa-bars';
								}
							@endphp
							<a href="{{ urlGen()->searchWithoutQuery() }}" class="nav-link {{ $linkClass }}">
								<i class="{{ $browseListingsIconClass }}"></i> {{ t('Browse Listings') }}
							</a>
						</li>
					@endif
					
					@if (config('settings.listing_form.pricing_page_enabled') == '2')
						<li class="nav-item pricing">
							<a href="{{ urlGen()->pricing() }}" class="nav-link {{ $linkClass }}">
								<i class="fa-solid fa-tags"></i> {{ t('pricing_label') }}
							</a>
						</li>
					@endif
					
					@php
						[$createListingLinkUrl, $createListingLinkAttr] = getCreateListingLinkInfo();
					@endphp
					<li class="nav-item">
						<a class="{{ $listingCreationBtnClass }}"
						   href="{{ $createListingLinkUrl }}"{!! $createListingLinkAttr !!}
						>
							<i class="fa-regular fa-pen-to-square"></i> {{ t('create_listing') }}
						</a>
					</li>
					
					@php
						$openOnHover = ''; // ' open-on-hover'
					@endphp
					@if (empty($authUser))
						<li class="nav-item dropdown{{ $openOnHover }}">
							<a href="#" class="{{ $userMenuBtnClass }} dropdown-toggle" data-bs-toggle="dropdown">
								<i class="fa-solid fa-user"></i>
								<span>{{ trans('auth.log_in') }}</span>
							</a>
							<ul id="authNavDropdown" class="dropdown-menu user-menu shadow-sm">
								<li>
									<a href="{!! urlGen()->signInModal() !!}" class="dropdown-item">
										<i class="fa-solid fa-user"></i> {{ trans('auth.log_in') }}
									</a>
								</li>
								<li>
									<a href="{{ urlGen()->signUp() }}" class="dropdown-item">
										<i class="fa-regular fa-user"></i> {{ trans('auth.sign_up') }}
									</a>
								</li>
							</ul>
						</li>
					@else
						<li class="nav-item dropdown{{ $openOnHover }}">
							<a href="#" class="{{ $userMenuBtnClass }} dropdown-toggle" data-bs-toggle="dropdown">
								<i class="bi bi-person-circle"></i>
								<span>{{ $authUser->name }}</span>
								<span class="badge rounded-pill text-bg-danger count-threads-with-new-messages">0</span>
							</a>
							<ul id="userNavDropdown" class="dropdown-menu shadow-sm">
								@if ($userMenu->count() > 0)
									@php
										$menuGroup = '';
										$dividerNeeded = false;
									@endphp
									@foreach($userMenu as $key => $value)
										@continue(!$value['inDropdown'])
										@php
											if ($menuGroup != $value['group']) {
												$menuGroup = $value['group'];
												if (!empty($menuGroup) && !$loop->first) {
													$dividerNeeded = true;
												}
											} else {
												$dividerNeeded = false;
											}
											$activeClass = (isset($value['isActive']) && $value['isActive']) ? ' active' : '';
										@endphp
										@if ($dividerNeeded)
											<li><hr class="dropdown-divider"></li>
										@endif
										<li>
											<a href="{{ $value['url'] }}" class="dropdown-item{{ $activeClass }} px-4">
												<i class="{{ $value['icon'] }}"></i> {{ $value['name'] }}
												@if (!empty($value['countCustomClass']) && !is_null($value['countVar']))
													<span class="badge rounded-pill text-bg-danger{{ $value['countCustomClass'] }}">0</span>
												@endif
											</a>
										</li>
									@endforeach
								@endif
							</ul>
						</li>
					@endif
					
					@if (isSettingsAppDarkModeEnabled())
						@include('front.layouts.partials.navs.themes', [
							'dropdownTag'   => 'li',
							'dropdownClass' => 'nav-item',
							'buttonClass'   => 'nav-link',
							'menuAlignment' => 'dropdown-menu-end',
							'showIconOnly'  => $showIconOnly,
							'linkClass'     => $linkClass,
						])
					@endif
					
					@include('front.layouts.partials.navs.languages')
					
				</ul>
			</div>
		
		</div>
	</nav>
</header>
@php
	$navbarHeightOffset = config('settings.style.header_height_offset');
	$navbarHeightOffset = (!empty($navbarHeightOffset) && is_numeric($navbarHeightOffset)) ? $navbarHeightOffset : 'null';
	
	$bgColorClass = config('settings.style.header_background_class') ?? $defaultBgColorClass;
	$bgColorClass = !empty($bgColorClass) ? $bgColorClass : $defaultBgColorClass;
	$bgColor = config('settings.style.header_background_color');
	$borderBottomWidth = config('settings.style.header_border_bottom_width');
	$borderBottomColor = config('settings.style.header_border_bottom_color');
	$linksColor = config('settings.style.header_link_color');
	$linksColorHover = config('settings.style.header_link_color_hover');
	
	$isFixedHeaderDarkThemeEnabled = (config('settings.style.fixed_dark_header') == '1');
	$isFixedHeaderShadowEnabled = (config('settings.style.fixed_header_shadow') == '1');
	$fixedBgColorClass = config('settings.style.fixed_header_background_class') ?? $defaultBgColorClass;
	$fixedBgColorClass = !empty($fixedBgColorClass) ? $fixedBgColorClass : $defaultBgColorClass;
	$fixedBgColor = config('settings.style.fixed_header_background_color');
	$fixedBorderBottomWidth = config('settings.style.fixed_header_border_bottom_width');
	$fixedBorderBottomColor = config('settings.style.fixed_header_border_bottom_color');
	$fixedLinksColor = config('settings.style.fixed_header_link_color');
	$fixedLinksColorHover = config('settings.style.fixed_header_link_color_hover');
@endphp
@pushonce('before_scripts_stack')
	<script>
		if (typeof window.headerOptions === 'undefined') {
			window.headerOptions = {};
		}
		window.headerOptions = {
			animationEnabled: {{ $isHeaderAnimationEnabled ? 'true' : 'false' }},
			navbarHeightOffset: {{ $navbarHeightOffset }},
			default: {
				darkThemeEnabled: {{ $isHeaderDarkThemeEnabled ? 'true' : 'false' }},
				bgColorClass: '{{ $bgColorClass }}',
				borderBottomClass: '{{ $borderBottomClass }}',
				shadowClass: '{{ $isHeaderShadowEnabled ? $shadowClass : '' }}',
				bgColor: '{{ $bgColor }}',
				borderBottomWidth: '{{ $borderBottomWidth }}',
				borderBottomColor: '{{ $borderBottomColor }}',
				linksColor: '{{ $linksColor }}',
				linksColorHover: '{{ $linksColorHover }}',
			},
			fixed: {
				enabled: {{ $isFixedTopHeader ? 'true' : 'false' }},
				darkThemeEnabled: {{ $isFixedHeaderDarkThemeEnabled ? 'true' : 'false' }},
				bgColorClass: '{{ $fixedBgColorClass }}',
				borderBottomClass: null,
				shadowClass: '{{ $isFixedHeaderShadowEnabled ? $shadowClass : '' }}',
				bgColor: '{{ $fixedBgColor }}',
				borderBottomWidth: '{{ $fixedBorderBottomWidth }}',
				borderBottomColor: '{{ $fixedBorderBottomColor }}',
				linksColor: '{{ $fixedLinksColor }}',
				linksColorHover: '{{ $fixedLinksColorHover }}',
			},
		};
	</script>
@endpushonce
