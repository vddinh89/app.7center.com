@if (auth()->check())
	@php
		$authUser = auth()->user();
		
		// Get plugins admin menu
		$pluginsMenu = '';
		$plugins = plugin_installed_list();
		if (!empty($plugins)) {
			foreach($plugins as $plugin) {
				if (method_exists($plugin->class, 'getAdminMenu')) {
					$pluginsMenu .= call_user_func($plugin->class . '::getAdminMenu');
				}
			}
		}
	@endphp
	<style>
		#adminSidebar ul li span {
			text-transform: capitalize;
		}
	</style>
	<aside class="left-sidebar" id="adminSidebar">
		{{-- Sidebar scroll --}}
		<div class="scroll-sidebar">
			{{-- Sidebar navigation --}}
			<nav class="sidebar-nav">
				<ul id="sidebarnav">
					<li class="sidebar-item user-profile">
						<a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false">
							<img src="{{ $authUser->photo_url ?? '/images/user.png' }}" alt="Administrator">
							<span class="hide-menu">{{ $authUser->name ?? 'Administrator' }}</span>
						</a>
						<ul aria-expanded="false" class="collapse first-level">
							<li class="sidebar-item">
								<a href="{{ urlGen()->adminUrl('account') }}" class="sidebar-link p-0">
									<i class="mdi mdi-adjust"></i>
									<span class="hide-menu">{{ trans('admin.my_account') }}</span>
								</a>
							</li>
							<li class="sidebar-item">
								<a href="{{ urlGen()->signOut() }}" class="sidebar-link p-0">
									<i class="mdi mdi-adjust"></i>
									<span class="hide-menu">{{ trans('admin.logout') }}</span>
								</a>
							</li>
						</ul>
					</li>
					
					<li class="sidebar-item">
						<a href="{{ urlGen()->adminUrl('dashboard') }}" class="sidebar-link waves-effect waves-dark">
							<i data-feather="home" class="feather-icon"></i> <span class="hide-menu">{{ trans('admin.dashboard') }}</span>
						</a>
					</li>
					@if (
						doesUserHavePermission($authUser, 'post-list')
						|| doesUserHavePermission($authUser, 'category-list')
						|| doesUserHavePermission($authUser, 'picture-list')
						|| doesUserHavePermission($authUser, 'field-list')
						|| userHasSuperAdminPermissions()
					)
						<li class="sidebar-item">
							<a href="#collapseListings"
							   class="sidebar-link has-arrow waves-effect waves-dark"
							   data-bs-toggle="collapse"
							   aria-expanded="false"
							   aria-controls="collapseListings"
							>
								<i data-feather="list"></i> <span class="hide-menu">{{ trans('admin.listings') }}</span>
							</a>
							<ul class="collapse first-level" id="collapseListings">
								@if (doesUserHavePermission($authUser, 'post-list') || userHasSuperAdminPermissions())
									<li class="sidebar-item">
										<a href="{{ urlGen()->adminUrl('posts') }}" class="sidebar-link">
											<i class="mdi mdi-adjust"></i>
											<span class="hide-menu">{{ trans('admin.list') }}</span>
										</a>
									</li>
								@endif
								@if (doesUserHavePermission($authUser, 'category-list') || userHasSuperAdminPermissions())
									<li class="sidebar-item">
										<a href="{{ urlGen()->adminUrl('categories') }}" class="sidebar-link">
											<i class="mdi mdi-adjust"></i>
											<span class="hide-menu">{{ trans('admin.categories') }}</span>
										</a>
									</li>
								@endif
								@if (doesUserHavePermission($authUser, 'picture-list') || userHasSuperAdminPermissions())
									<li class="sidebar-item">
										<a href="{{ urlGen()->adminUrl('pictures') }}" class="sidebar-link">
											<i class="mdi mdi-adjust"></i>
											<span class="hide-menu">{{ trans('admin.pictures') }}</span>
										</a>
									</li>
								@endif
								@if (doesUserHavePermission($authUser, 'field-list') || userHasSuperAdminPermissions())
									<li class="sidebar-item">
										<a href="{{ urlGen()->adminUrl('custom_fields') }}" class="sidebar-link">
											<i class="mdi mdi-adjust"></i>
											<span class="hide-menu">{{ trans('admin.custom fields') }}</span>
										</a>
									</li>
								@endif
							</ul>
						</li>
					@endif
					
					@if (
						doesUserHavePermission($authUser, 'user-list')
						|| doesUserHavePermission($authUser, 'role-list')
						|| doesUserHavePermission($authUser, 'permission-list')
						|| userHasSuperAdminPermissions()
					)
						<li  class="sidebar-item">
							<a href="#collapseUsers"
							   class="sidebar-link has-arrow waves-effect waves-dark"
							   data-bs-toggle="collapse"
							   aria-expanded="false"
							   aria-controls="collapseUsers"
							>
								<i data-feather="users" class="feather-icon"></i> <span class="hide-menu">{{ trans('admin.users') }}</span>
							</a>
							<ul class="collapse first-level" id="collapseUsers">
								@if (doesUserHavePermission($authUser, 'user-list') || userHasSuperAdminPermissions())
									<li class="sidebar-item">
										<a href="{{ urlGen()->adminUrl('users') }}" class="sidebar-link">
											<i class="mdi mdi-adjust"></i>
											<span class="hide-menu">{{ trans('admin.list') }}</span>
										</a>
									</li>
								@endif
								@if (doesUserHavePermission($authUser, 'role-list') || userHasSuperAdminPermissions())
									<li class="sidebar-item">
										<a href="{{ urlGen()->adminUrl('roles') }}" class="sidebar-link">
											<i class="mdi mdi-adjust"></i>
											<span class="hide-menu">{{ trans('admin.roles') }}</span>
										</a>
									</li>
								@endif
								@if (doesUserHavePermission($authUser, 'permission-list') || userHasSuperAdminPermissions())
									<li class="sidebar-item">
										<a href="{{ urlGen()->adminUrl('permissions') }}" class="sidebar-link">
											<i class="mdi mdi-adjust"></i>
											<span class="hide-menu">{{ trans('admin.permissions') }}</span>
										</a>
									</li>
								@endif
							</ul>
						</li>
					@endif
					
					@if (
						doesUserHavePermission($authUser, 'payment-list')
						|| userHasSuperAdminPermissions()
					)
						<li class="sidebar-item">
							<a href="#collapsePayments"
							   class="sidebar-link has-arrow waves-effect waves-dark"
							   data-bs-toggle="collapse"
							   aria-expanded="false"
							   aria-controls="collapsePayments"
							>
								<i data-feather="dollar-sign" class="feather-icon"></i> <span class="hide-menu">{{ trans('admin.payments') }}</span>
							</a>
							<ul class="collapse first-level" id="collapsePayments">
								<li class="sidebar-item">
									<a href="{{ urlGen()->adminUrl('payments/promotion') }}" class="sidebar-link">
										<i class="mdi mdi-adjust"></i>
										<span class="hide-menu">{{ trans('admin.promotions') }}</span>
									</a>
								</li>
								<li class="sidebar-item">
									<a href="{{ urlGen()->adminUrl('payments/subscription') }}" class="sidebar-link">
										<i class="mdi mdi-adjust"></i>
										<span class="hide-menu">{{ trans('admin.subscriptions') }}</span>
									</a>
								</li>
							</ul>
						</li>
					@endif
					@if (doesUserHavePermission($authUser, 'page-list') || userHasSuperAdminPermissions())
						<li class="sidebar-item">
							<a href="{{ urlGen()->adminUrl('pages') }}" class="sidebar-link">
								<i data-feather="book-open" class="feather-icon"></i> <span class="hide-menu">{{ trans('admin.pages') }}</span>
							</a>
						</li>
					@endif
					{!! $pluginsMenu !!}
					
					{{-- ======================================= --}}
					@if (
						doesUserHavePermission($authUser, 'setting-list')
						|| doesUserHavePermission($authUser, 'language-list')
						|| doesUserHavePermission($authUser, 'section-list')
						|| doesUserHavePermission($authUser, 'meta-tag-list')
						|| doesUserHavePermission($authUser, 'package-list')
						|| doesUserHavePermission($authUser, 'payment-method-list')
						|| doesUserHavePermission($authUser, 'advertising-list')
						|| doesUserHavePermission($authUser, 'country-list')
						|| doesUserHavePermission($authUser, 'currency-list')
						|| doesUserHavePermission($authUser, 'blacklist-list')
						|| doesUserHavePermission($authUser, 'report-type-list')
						|| userHasSuperAdminPermissions()
					)
						<li class="nav-small-cap">
							<i class="mdi mdi-dots-horizontal"></i>
							<span class="hide-menu">{{ trans('admin.configuration') }}</span>
						</li>
						
						@if (
							doesUserHavePermission($authUser, 'setting-list')
							|| doesUserHavePermission($authUser, 'language-list')
							|| doesUserHavePermission($authUser, 'section-list')
							|| doesUserHavePermission($authUser, 'meta-tag-list')
							|| doesUserHavePermission($authUser, 'package-list')
							|| doesUserHavePermission($authUser, 'payment-method-list')
							|| doesUserHavePermission($authUser, 'advertising-list')
							|| doesUserHavePermission($authUser, 'country-list')
							|| doesUserHavePermission($authUser, 'currency-list')
							|| doesUserHavePermission($authUser, 'blacklist-list')
							|| doesUserHavePermission($authUser, 'report-type-list')
							|| userHasSuperAdminPermissions()
						)
							<li class="sidebar-item">
								<a href="#collapseSettings"
								   class="has-arrow sidebar-link"
								   data-bs-toggle="collapse"
								   aria-expanded="false"
								   aria-controls="collapseSettings"
								>
									<i data-feather="settings" class="feather-icon"></i>
									<span class="hide-menu">{{ trans('admin.settings') }}</span>
								</a>
								<ul class="collapse first-level" id="collapseSettings">
									@include('admin.layouts.partials.sidebar.general-settings')
									@include('admin.layouts.partials.sidebar.tableData-settings')
								</ul>
							</li>
						@endif
					@endif
					
					@if (doesUserHavePermission($authUser, 'plugin-list') || userHasSuperAdminPermissions())
						<li class="sidebar-item">
							<a href="{{ urlGen()->adminUrl('plugins') }}" class="sidebar-link">
								<i data-feather="package" class="feather-icon"></i> <span class="hide-menu">{{ trans('admin.plugins') }}</span>
							</a>
						</li>
					@endif
					@if (doesUserHavePermission($authUser, 'clear-cache') || userHasSuperAdminPermissions())
						<li class="sidebar-item">
							<a href="{{ urlGen()->adminUrl('actions/clear_cache') }}" class="sidebar-link">
								<i data-feather="refresh-cw" class="feather-icon"></i> <span class="hide-menu">{{ trans('admin.clear cache') }}</span>
							</a>
						</li>
					@endif
					@if (doesUserHavePermission($authUser, 'backup-list') || userHasSuperAdminPermissions())
						<li class="sidebar-item">
							<a href="{{ urlGen()->adminUrl('backups') }}" class="sidebar-link">
								<i data-feather="hard-drive" class="feather-icon"></i> <span class="hide-menu">{{ trans('admin.backups') }}</span>
							</a>
						</li>
					@endif
					
					@if (
						doesUserHavePermission($authUser, 'maintenance') ||
						userHasSuperAdminPermissions()
					)
						@if (app()->isDownForMaintenance())
							@if (doesUserHavePermission($authUser, 'maintenance') || userHasSuperAdminPermissions())
								<li class="sidebar-item">
									<a href="{{ urlGen()->adminUrl('actions/maintenance/up') }}"
									   data-bs-toggle="tooltip"
									   title="{{ trans('admin.Leave Maintenance Mode') }}"
									   class="sidebar-link confirm-simple-action"
									>
										<i data-feather="toggle-right"></i> <span class="hide-menu">{{ trans('admin.Live Mode') }}</span>
									</a>
								</li>
							@endif
						@else
							@if (doesUserHavePermission($authUser, 'maintenance') || userHasSuperAdminPermissions())
								<li class="sidebar-item">
									<a href="#maintenanceMode"
									   data-bs-toggle="modal"
									   title="{{ trans('admin.Put in Maintenance Mode') }}"
									   class="sidebar-link"
									>
										<i data-feather="toggle-left"></i> <span class="hide-menu">{{ trans('admin.Maintenance') }}</span>
									</a>
								</li>
							@endif
						@endif
					@endif
					@if (doesUserHavePermission($authUser, 'system-info') || userHasSuperAdminPermissions())
						<li class="sidebar-item">
							<a href="{{ urlGen()->adminUrl('system') }}" class="sidebar-link">
								<i data-feather="alert-circle"></i> <span class="hide-menu">{{ trans('admin.system_info') }}</span>
							</a>
						</li>
					@endif
					
					@if (userHasSuperAdminPermissions())
						<li class="sidebar-item">
							<a href="{{ url('docs/api') }}" target="_blank" class="sidebar-link">
								<i data-feather="book" class="feather-icon"></i> <span class="hide-menu">{{ trans('admin.api_docs') }}</span>
							</a>
						</li>
					@endif
					
				</ul>
			</nav>
			
		</div>
		
	</aside>
@endif
