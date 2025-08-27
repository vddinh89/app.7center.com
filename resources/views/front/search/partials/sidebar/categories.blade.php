@php
	$countPostsPerCat ??= [];
	
	// Clear Filter Button
	$clearFilterBtn = urlGen()->getCategoryFilterClearLink($cat ?? null, $city ?? null);
	
	// Links CSS Class
	$linkClass = linkClass('body-emphasis');
@endphp
@if (!empty($cat))
	@php
		$catParentUrl = urlGen()->parentCategory(data_get($cat, 'parent') ?? null, $city ?? null);
	@endphp
	
	{{-- SubCategory --}}
	<div id="subCatsList">
		@if (!empty(data_get($cat, 'children')))
			
			<div class="container p-0 vstack gap-2">
				<h5 class="border-bottom pb-2 d-flex justify-content-between mb-0">
					<span class="fw-bold">
						@if (!empty(data_get($cat, 'parent')))
							<a href="{{ urlGen()->category(data_get($cat, 'parent'), null, $city ?? null) }}"
							   class="{{ $linkClass }}"
							>
								<i class="fa-solid fa-reply"></i> {{ data_get($cat, 'parent.name') }}
							</a>
						@else
							<a href="{{ $catParentUrl }}" class="{{ $linkClass }}">
								<i class="fa-solid fa-reply"></i> {{ t('all_categories') }}
							</a>
						@endif
					</span> {!! $clearFilterBtn !!}
				</h5>
				<ul class="mb-0 list-unstyled">
					<li class="py-1">
						<div class="border-bottom pb-2 mb-3">
							<span class="fs-5">
								@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
									<i class="{{ data_get($cat, 'icon_class') ?? 'bi bi-folder-fill' }}"></i>
								@endif
								{{ data_get($cat, 'name') }}
							</span>
							@if (config('settings.listings_list.count_categories_listings'))
								&nbsp;<span class="fw-normal">({{ $countPostsPerCat[data_get($cat, 'id')]['total'] ?? 0 }})</span>
							@endif
						</div>
						<ul class="mb-0 ps-2 list-unstyled long-list">
							@foreach (data_get($cat, 'children') as $iSubCat)
								<li class="py-1">
									<a href="{{ urlGen()->category($iSubCat, null, $city ?? null) }}"
									   class="{{ $linkClass }}"
									   title="{{ data_get($iSubCat, 'name') }}"
									>
										@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
											<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }}"></i>
										@endif
										{{ str(data_get($iSubCat, 'name'))->limit(100) }}
										@if (config('settings.listings_list.count_categories_listings'))
											&nbsp;<span class="fw-normal">({{ $countPostsPerCat[data_get($iSubCat, 'id')]['total'] ?? 0 }})</span>
										@endif
									</a>
								</li>
							@endforeach
						</ul>
					</li>
				</ul>
			</div>
			
		@else
			
			@if (!empty(data_get($cat, 'parent.children')))
				<div class="container p-0 vstack gap-2">
					<h5 class="border-bottom pb-2 d-flex justify-content-between">
						<span class="fw-bold">
							@if (!empty(data_get($cat, 'parent.parent')))
								<a href="{{ urlGen()->category(data_get($cat, 'parent.parent'), null, $city ?? null) }}"
								   class="{{ $linkClass }}"
								>
									<i class="fa-solid fa-reply"></i> {{ data_get($cat, 'parent.parent.name') }}
								</a>
							@elseif (!empty(data_get($cat, 'parent')))
								<a href="{{ urlGen()->category(data_get($cat, 'parent'), null, $city ?? null) }}"
								   class="{{ $linkClass }}"
								>
									<i class="fa-solid fa-reply"></i> {{ data_get($cat, 'name') }}
								</a>
							@else
								<a href="{{ $catParentUrl }}" class="{{ $linkClass }}">
									<i class="fa-solid fa-reply"></i> {{ t('all_categories') }}
								</a>
							@endif
						</span> {!! $clearFilterBtn !!}
					</h5>
					<ul class="mb-0 list-unstyled">
						@foreach (data_get($cat, 'parent.children') as $iSubCat)
							<li class="py-1">
								@if (data_get($iSubCat, 'id') == data_get($cat, 'id'))
									<span class="fw-bold">
										@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
											<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }}"></i>
										@endif
										{{ str(data_get($iSubCat, 'name'))->limit(100) }}
										@if (config('settings.listings_list.count_categories_listings'))
											&nbsp;<span class="fw-normal">({{ $countPostsPerCat[data_get($iSubCat, 'id')]['total'] ?? 0 }})</span>
										@endif
									</span>
								@else
									<a href="{{ urlGen()->category($iSubCat, null, $city ?? null) }}"
									   class="{{ $linkClass }}"
									   title="{{ data_get($iSubCat, 'name') }}"
									>
										@if (in_array(config('settings.listings_list.show_category_icon'), [4, 5, 6, 8]))
											<i class="{{ data_get($iSubCat, 'icon_class') ?? 'bi bi-folder-fill' }}"></i>
										@endif
										{{ str(data_get($iSubCat, 'name'))->limit(100) }}
										@if (config('settings.listings_list.count_categories_listings'))
											&nbsp;<span class="fw-normal">({{ $countPostsPerCat[data_get($iSubCat, 'id')]['total'] ?? 0 }})</span>
										@endif
									</a>
								@endif
							</li>
						@endforeach
					</ul>
				</div>
			@else
				
				@include('front.search.partials.sidebar.categories.root', ['countPostsPerCat' => $countPostsPerCat])
			
			@endif
			
		@endif
	</div>
	
@else
	
	@include('front.search.partials.sidebar.categories.root', ['countPostsPerCat' => $countPostsPerCat])
	
@endif
