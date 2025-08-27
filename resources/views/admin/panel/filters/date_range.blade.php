{{-- Date Range CRUD filter --}}
@php
	$filter ??= new \stdClass();
	
	$activeClass = request()->query($filter->name) ? ' active' : '';
	$filterSlug = str($filter->name)->slug();
@endphp
<li class="nav-item dropdown{{ $activeClass }}"
    data-filter-name="{{ $filter->name }}"
    data-filter-type="{{ $filter->type }}"
>
	<a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
		{{ $filter->label }} <span class="caret"></span>
	</a>
	<div class="dropdown-menu p-0">
		<div class="backpack-filter mb-0">
			<div class="input-group date">
				<span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
				<input class="form-control float-end"
					id="daterangepicker-{{ $filterSlug }}"
					type="text"
					@if ($filter->currentValue)
						@php
						$dates = (array)json_decode($filter->currentValue);
						$start_date = $dates['from'];
						$end_date = $dates['to'];
						$date_range = implode(' ~ ', $dates);
						$date_range = str_replace('-', '/', $date_range);
						$date_range = str_replace('~', '-', $date_range);
						@endphp
						placeholder="{{ $date_range }}"
					@endif
				>
				<span class="input-group-text daterangepicker-{{ $filterSlug }}-clear-button">
					<a href=""><i class="fa-solid fa-xmark"></i></a>
				</span>
			</div>
		</div>
	</div>
</li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

@push('crud_list_styles')
	{{-- include select2 css --}}
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/daterangepicker/3.1/daterangepicker.css') }}"/>
	<style>
		.input-group.date {
			width: 320px;
			max-width: 100%;
		}
		
		.daterangepicker.dropdown-menu {
			z-index: 3001 !important;
		}
		.daterangepicker {
			padding: 0;
		}
	</style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
	<script type="text/javascript" src="{{ asset('assets/plugins/momentjs/2.18.1/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/plugins/daterangepicker/3.1/daterangepicker.js') }}"></script>
	<script>
		function applyDateRangeFilter(start, end) {
			let value;
			if (start && end) {
				const dates = {
					'from': start.format('YYYY-MM-DD'),
					'to': end.format('YYYY-MM-DD')
				};
				value = JSON.stringify(dates);
			} else {
				// this change to empty string,because addOrUpdateUriParameter method just judgment string
				value = '';
			}
			const parameter = '{{ $filter->name }}';
			
			// behaviour for ajax table
			const ajaxTable = $('#crudTable').DataTable();
			const currentUrl = ajaxTable.ajax.url();
			let newUrl = addOrUpdateUriParameter(currentUrl, parameter, value);
			
			// replace the datatables ajax url with newUrl and reload it
			newUrl = normalizeAmpersand(newUrl.toString());
			ajaxTable.ajax.url(newUrl).load();
			
			// mark this filter as active in the navbar-filters
			if (URI(newUrl).hasQuery('{{ $filter->name }}', true)) {
				$('li[data-filter-name={{ $filter->name }}]').removeClass('active').addClass('active');
			} else {
				$('li[data-filter-name={{ $filter->name }}]').trigger('filter:clear');
			}
		}
		
		onDocumentReady((event) => {
			const dateRangeInput = $('#daterangepicker-{{ $filterSlug }}').daterangepicker({
					timePicker: false,
					ranges: {
						'Today': [moment().startOf('day'), moment().endOf('day')],
						'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
						'Last 7 Days': [moment().subtract(6, 'days'), moment()],
						'Last 30 Days': [moment().subtract(29, 'days'), moment()],
						'This Month': [moment().startOf('month'), moment().endOf('month')],
						'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
					},
						@if ($filter->currentValue)
							startDate: moment("{{ $start_date }}"),
							endDate: moment("{{ $end_date }}"),
						@endif
						alwaysShowCalendars: true,
					autoUpdateInput: true
				},
				function (start, end) {
					applyDateRangeFilter(start, end);
				});
			
			$('li[data-filter-name={{ $filter->name }}]').on('hide.bs.dropdown', function () {
				if ($('.daterangepicker').is(':visible'))
					return false;
			});
			
			$('li[data-filter-name={{ $filter->name }}]').on('filter:clear', function (e) {
				// console.log('daterangepicker filter cleared');
				// if triggered by remove filters click just remove active class,no need to send ajax
				$('li[data-filter-name={{ $filter->name }}]').removeClass('active');
			});
			
			// datepicker clear button
			$(".daterangepicker-{{ $filterSlug }}-clear-button").click(function (e) {
				e.preventDefault();
				applyDateRangeFilter(null, null);
			})
		});
	</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
