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
		<div class="form-group backpack-filter mb-0">
			<div class="input-group date">
				<span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
				<input class="form-control float-end"
					   id="datepicker-{{ $filterSlug }}"
					   type="text"
					   @if ($filter->currentValue)
					   value="{{ $filter->currentValue }}"
						@endif
				>
				<span class="input-group-text datepicker{{ $filterSlug }}-clear-button">
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
	<link href="{{ asset('assets/plugins/datepicker/1.9.0/datepicker3.css') }}" rel="stylesheet" type="text/css" />
	<style>
		.input-group.date {
			width: 320px;
			max-width: 100%;
		}
	</style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
	{{-- include select2 js--}}
	<script type="text/javascript" src="{{ asset('assets/plugins/datepicker/1.9.0/bootstrap-datepicker.js') }}"></script>
	<script>
		onDocumentReady((event) => {
			const dateInput = $('#datepicker-{{ $filterSlug }}').datepicker({
					autoclose: true,
					format: 'yyyy-mm-dd',
					todayHighlight: true
				})
				.on('changeDate', function(e) {
					var d = new Date(e.date);
					// console.log(e);
					// console.log(d);
					let value;
					if (isNaN(d.getFullYear())) {
						value = '';
					} else {
						value = d.getFullYear() + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2);
					}
					// console.log(value);
					
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
					}
					else
					{
						$('li[data-filter-name={{ $filter->name }}]').trigger('filter:clear');
					}
				});
			$('li[data-filter-name={{ $filterSlug }}]').on('filter:clear', function(e) {
				// console.log('date filter cleared');
				$('li[data-filter-name={{ $filter->name }}]').removeClass('active');
				$('#datepicker-{{ $filterSlug }}').datepicker('clearDates');
			});
			
			// datepicker clear button
			$(".datepicker-{{ $filterSlug }}-clear-button").click(function(e) {
				e.preventDefault();
				
				$('li[data-filter-name={{ $filterSlug }}]').trigger('filter:clear');
				$('#datepicker-{{ $filterSlug }}').trigger('changeDate');
			})
		});
	</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
