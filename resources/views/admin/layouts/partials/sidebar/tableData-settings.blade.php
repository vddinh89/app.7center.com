@php
	$authUser ??= null;
@endphp
@if (doesUserHavePermission($authUser, 'language-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ urlGen()->adminUrl('languages') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.languages') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'section-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ urlGen()->adminUrl('sections') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.homepage') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'meta-tag-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ urlGen()->adminUrl('meta_tags') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.meta tags') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'package-list')|| userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="#collapsePackages"
		   class="sidebar-link has-arrow"
		   data-bs-toggle="collapse"
		   aria-expanded="false"
		   aria-controls="collapsePackages"
		>
			<i class="mdi mdi-adjust"></i> <span class="hide-menu">{{ trans('admin.packages') }}</span>
		</a>
		<ul class="collapse second-level" id="collapsePackages">
			<li class="sidebar-item">
				<a href="{{ urlGen()->adminUrl('packages/promotion') }}" class="sidebar-link">
					<i class="mdi mdi-adjust"></i>
					<span class="hide-menu">{{ trans('admin.promotion') }}</span>
				</a>
			</li>
			<li class="sidebar-item">
				<a href="{{ urlGen()->adminUrl('packages/subscription') }}" class="sidebar-link">
					<i class="mdi mdi-adjust"></i>
					<span class="hide-menu">{{ trans('admin.subscription') }}</span>
				</a>
			</li>
			<li class="sidebar-item">&nbsp;</li>
		</ul>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'payment-method-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ urlGen()->adminUrl('payment_methods') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.payment methods') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'advertising-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ urlGen()->adminUrl('advertisings') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.advertising') }}</span>
		</a>
	</li>
@endif
@if (
	doesUserHavePermission($authUser, 'country-list')
	|| doesUserHavePermission($authUser, 'currency-list')
	|| userHasSuperAdminPermissions()
)
	<li class="sidebar-item">
		<a href="#collapseInternational"
		   class="sidebar-link has-arrow"
		   data-bs-toggle="collapse"
		   aria-expanded="false"
		   aria-controls="collapseInternational"
		>
			<i class="fa-solid fa-globe"></i> <span class="hide-menu">{{ trans('admin.international') }}</span>
		</a>
		<ul class="collapse second-level" id="collapseInternational">
			@if (doesUserHavePermission($authUser, 'country-list') || userHasSuperAdminPermissions())
				<li class="sidebar-item">
					<a href="{{ urlGen()->adminUrl('countries') }}" class="sidebar-link">
						<i class="mdi mdi-adjust"></i>
						<span class="hide-menu">{{ trans('admin.countries') }}</span>
					</a>
				</li>
			@endif
			@if (doesUserHavePermission($authUser, 'currency-list') || userHasSuperAdminPermissions())
				<li class="sidebar-item">
					<a href="{{ urlGen()->adminUrl('currencies') }}" class="sidebar-link">
						<i class="mdi mdi-adjust"></i>
						<span class="hide-menu">{{ trans('admin.currencies') }}</span>
					</a>
				</li>
			@endif
			<li class="sidebar-item">&nbsp;</li>
		</ul>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'blacklist-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ urlGen()->adminUrl('blacklists') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.blacklist') }}</span>
		</a>
	</li>
@endif
@if (doesUserHavePermission($authUser, 'report-type-list') || userHasSuperAdminPermissions())
	<li class="sidebar-item">
		<a href="{{ urlGen()->adminUrl('report_types') }}" class="sidebar-link">
			<i class="mdi mdi-adjust"></i>
			<span class="hide-menu">{{ trans('admin.report types') }}</span>
		</a>
	</li>
@endif
