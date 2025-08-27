{{-- Select2 Ajax CRUD filter --}}
@php
	$filter ??= new \stdClass();
	$activeClass = request()->query($filter->name) ? ' active' : '';
@endphp
<li class="nav-item dropdown{{ $activeClass }}"
    data-filter-name="{{ $filter->name }}"
    data-filter-type="{{ $filter->type }}"
>
	<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
		{{ $filter->label }} <span class="caret"></span>
	</a>
	<div class="dropdown-menu ajax-select">
		<div class="mb-0">
			<input type="text"
				   value="{{ request()->query($filter->name) ? request()->query($filter->name) . '|' . request()->query($filter->name . '_text') : '' }}"
				   id="filter_{{ $filter->name }}"
			>
		</div>
	</div>
</li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

@push('crud_list_styles')
	{{-- include select2 css--}}
	<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css"/>
	<link href="{{ asset('assets/plugins/select2-bootstrap3-theme/0.1.0-beta.10/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
	<style>
		.form-inline .select2-container {
			display: inline-block;
		}
		
		li[filter-type="{{ $filter->type }}"] .select2-container {
			display: block;
		}
		
		.select2-drop-active {
			border: none;
		}
		
		.select2-container .select2-choices .select2-search-field input, .select2-container .select2-choice, .select2-container .select2-choices {
			border: none;
		}
		
		.select2-container-active .select2-choice {
			border: none;
			box-shadow: none;
		}
	</style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
	{{-- include select2 js--}}
	<script src="{{ asset('assets/plugins/select2/js/select2.js') }}"></script>
	<script>
		onDocumentReady((event) => {
			// trigger select2 for each untriggered select2 box
			$('#filter_{{ $filter->name }}').select2({
				minimumInputLength: 2,
				allowClear: true,
				placeholder: "{{ $filter->placeholder ? $filter->placeholder : ' ' }}",
				closeOnSelect: false,
				theme: "bootstrap",
				// tags: [],
				ajax: {
					url: "{{ $filter->values }}",
					dataType: 'json',
					type: "GET",
					quietMillis: 50,
					data: function (term) {
						return {
							term: term
						};
					},
					results: function (data) {
						return {
							results: $.map(data, function (item, i) {
								return {
									text: item,
									id: i
								}
							})
						};
					}
				},
				initSelection: function (element, callback) {
					const value = element.val().split('|');
					const results = [];
					
					if (value !== '' && value !== null) {
						results.push({
							id: value[0],
							text: value[1]
						});
					}
					callback(results[0]);
				}
			}).on('change', function (evt) {
				const val = $(this).val();
				const val_text = $(this).select2('data') ? $(this).select2('data').text : null;
				const parameter = '{{ $filter->name }}';
				
				let currentUrl, newUrl;
				@if (!$xPanel->ajaxTable())
					// behaviour for normal table
					currentUrl = normalizeAmpersand('{{ request()->fullUrl() }}');
					newUrl = addOrUpdateUriParameter(currentUrl, parameter, val);
					if (val_text) {
						newUrl = addOrUpdateUriParameter(newUrl, parameter + "_text", val_text);
					}
					
					// refresh the page to the newUrl
					newUrl = normalizeAmpersand(newUrl.toString());
					window.location.href = newUrl;
				@else
					// behaviour for ajax table
					const ajaxTable = $("#crudTable").DataTable();
					currentUrl = ajaxTable.ajax.url();
					newUrl = addOrUpdateUriParameter(currentUrl, parameter, val);
					if (val_text) {
						newUrl = addOrUpdateUriParameter(newUrl, parameter + "_text", val_text);
					}
					newUrl = normalizeAmpersand(newUrl.toString());
					
					
					// replace the datatables ajax url with newUrl and reload it
					ajaxTable.ajax.url(newUrl).load();
					
					// mark this filter as active in the navbar-filters
					if (URI(newUrl).hasQuery('{{ $filter->name }}', true)) {
						$("li[data-filter-name={{ $filter->name }}]").removeClass('active').addClass('active');
					} else {
						$("li[data-filter-name={{ $filter->name }}]").trigger("filter:clear");
					}
				@endif
			});
			
			// when the dropdown is opened, autofocus on the select2
			$("li[data-filter-name={{ $filter->name }}]").on('shown.bs.dropdown', function () {
				$('#filter_{{ $filter->name }}').select2('open');
			});
			
			// clear filter event (used here and by the Remove all filters button)
			$("li[data-filter-name={{ $filter->name }}]").on('filter:clear', function (e) {
				// console.log('select2 filter cleared');
				$("li[data-filter-name={{ $filter->name }}]").removeClass('active');
				/* $("li[data-filter-name={{ $filter->name }}] .select2").select2("val", ""); */
				$('#filter_{{ $filter->name }}').val('');
			});
		});
	</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}

