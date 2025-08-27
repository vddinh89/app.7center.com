@php
    $htmlLang = getLangTag(config('app.locale'));
	$langDirection = config('lang.direction');
	$userThemePreference = currentUserThemePreference();
	
	$htmlDir = ($langDirection == 'rtl') ? ' dir="rtl"' : '';
	$htmlTheme = ($userThemePreference == 'dark') ? ' data-bs-theme="dark"' : '';
	
	$showIconOnly = true;
@endphp
<!DOCTYPE html>
<html lang="{{ $htmlLang }}"{!! $htmlDir . $htmlTheme !!}>
<head>
    <meta charset="{{ config('larapen.core.charset', 'utf-8') }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{-- Tell the browser to be responsive to screen width --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="{{ config('app.name') }}">
    {{-- Favicon icon --}}
    <link rel="icon" type="image/png" sizes="16x16" href="{{ config('settings.app.favicon_url') }}">
    
    <title>{!! isset($title) ? strip_tags($title) . ' :: ' . config('app.name') . ' Admin' : config('app.name') . ' Admin' !!}</title>
    
    {{-- Encrypted CSRF token for Laravel, in order for Ajax requests to work --}}
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    {{-- Specify a default target for all hyperlinks and forms on the page --}}
    <base target="_top"/>
    
    <link rel="canonical" href="{{ url()->current() }}" />
    
    @yield('before_styles')
    
    {{-- Admin Panel's CSS files (Handled by Mix) --}}
    @if (config('lang.direction') == 'rtl')
        <link href="https://fonts.googleapis.com/css?family=Cairo|Changa" rel="stylesheet">
        <link href="{{ url(mix('dist/admin/styles.rtl.css')) }}" rel="stylesheet">
    @else
        <link href="{{ url(mix('dist/admin/styles.css')) }}" rel="stylesheet">
    @endif
    
    @yield('after_styles')
    
    <style>
        /* Fix for "datatables/css/jquery.dataTables.css" */
        table.dataTable thead .sorting,
        table.dataTable thead .sorting_asc,
        table.dataTable thead .sorting_desc,
        table.dataTable thead .sorting_asc_disabled,
        table.dataTable thead .sorting_desc_disabled {
            background-image: inherit;
        }
    </style>
    
    @include('front.common.js.document')
    
    {{-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries --}}
    {{-- WARNING: Respond.js doesn't work if you view the page via file:// --}}
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="bg-body-tertiary text-body-emphasis">
{{-- Main wrapper - style you can find in pages.scss --}}
<div id="main-wrapper">
    
    {{-- Topbar header - style you can find in pages.scss --}}
    @include('admin.layouts.partials.header')
    
    {{-- Left Sidebar - style you can find in sidebar.scss  --}}
    @include('admin.layouts.partials.sidebar')
    
    {{-- Page wrapper  --}}
    <div class="page-wrapper">
        {{-- Container fluid  --}}
        <div class="container-fluid">
            {{-- Bread crumb and right sidebar toggle --}}
            @yield('header')
            
            {{-- Start Page Content --}}
            @yield('content')
        </div>
        
        {{-- Footer --}}
        <footer class="footer bg-body border-top border-light-subtle border-opacity-50">
            <div class="row">
                <div class="col-md-6 text-start">
                    {{ trans('admin.Version') }} {{ env('APP_VERSION', config('version.app')) }}
                </div>
                @if (config('settings.footer.hide_powered_by') != '1')
                    <div class="col-md-6 text-end">
                        @if (config('settings.footer.powered_by_text'))
                            {{ trans('admin.powered_by') }} {!! config('settings.footer.powered_by_text') !!}
                        @else
                            {{ trans('admin.powered_by') }} <a target="_blank" href="https://bedigit.com">BeDigit</a>
                        @endif
                    </div>
                @endif
            </div>
        </footer>
    </div>
</div>

@include('front.common.js.init')

@yield('before_scripts')

<script>
    {{-- Theme Preference (light/dark/system) --}}
    var isSettingsAppDarkModeEnabled = {{ isSettingsAppDarkModeEnabled() ? 'true' : 'false' }};
    var isSettingsAppSystemThemeEnabled = {{ isSettingsAppSystemThemeEnabled() ? 'true' : 'false' }};
    var userThemePreference = {!! !empty($userThemePreference) ? "'$userThemePreference'" : 'null' !!};
    var showIconOnly = {{ $showIconOnly ? 'true' : 'false' }};
    
    {{-- The app's default auth field --}}
    var defaultAuthField = '{{ old('auth_field', getAuthField()) }}';
    var phoneCountry = '';
    
    {{-- Admin Panel Theme Settings --}}
    const adminPanelSettings = {
        Theme: {{ ($userThemePreference == 'dark') ? 'true' : 'false' }},
        Layout: 'vertical',
        LogoBg: '{{ config('settings.style.admin_logo_bg') }}',
        NavbarBg: '{{ config('settings.style.admin_navbar_bg') }}',
        SidebarType: '{{ config('settings.style.admin_sidebar_type') }}',
        SidebarColor: '{{ config('settings.style.admin_sidebar_bg') }}',
        SidebarPosition: {{ config('settings.style.admin_sidebar_position') == '1' ? 'true' : 'false' }},
        HeaderPosition: {{ config('settings.style.admin_header_position') == '1' ? 'true' : 'false' }},
        BoxedLayout: {{ config('settings.style.admin_boxed_layout') == '1' ? 'true' : 'false' }},
    };
</script>

<script src="{{ url(mix('dist/admin/scripts.js')) }}"></script>

<script>
    onDocumentReady((event) => {
        "use strict";
        $('#main-wrapper').AdminSettings(adminPanelSettings);
    });
</script>

{{-- Page Script --}}
<script type="text/javascript">
    onDocumentReady((event) => {
        /* To make Pace works on Ajax calls */
        $(document).ajaxStart(function () {
            Pace.restart();
        });
    
        /* Set active state on menu element */
        const currentUrl = "{{ url(Route::current()->uri()) }}";
        $("#sidebarnav li a").each(function () {
            if ($(this).attr('href').startsWith(currentUrl) || currentUrl.startsWith($(this).attr('href'))) {
                $(this).parents('li').addClass('selected');
            }
        });
    });
</script>
<script>
    onDocumentReady((event) => {
        {{-- Confirm Action For AJAX (Update) Request --}}
        $(document).on('click', '.ajax-request', function(e)
        {
            e.preventDefault(); {{-- Prevents submission or reloading --}}
    
            const thisEl = this;
            
            /* Get element's icon */
            let iconEl = null;
            if ($(thisEl).is('a') && $(thisEl).hasClass('btn')) {
                iconEl = $(thisEl).find('i');
            } else {
                if ($(thisEl).next('a').hasClass('btn')) {
                    iconEl = $(thisEl).next('a').find('i');
                }
            }
            
            /* Don't make multiple simultaneous calls */
            if (iconEl) {
                if (iconEl.hasClass('spinner-border')) {
                    return false;
                }
            }
            
            Swal.fire({
                position: 'top',
                text: langLayout.confirm.message.question,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: langLayout.confirm.button.yes,
                cancelButtonText: langLayout.confirm.button.no
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    saveAjaxRequest(siteUrl, thisEl);
                    
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    
                    pnAlert(langLayout.confirm.message.cancel, 'info');
                    
                }
            });
        });
    });
    
    function saveAjaxRequest(siteUrl, thisEl)
    {
        if (isDemoDomain()) {
            return false;
        }
        
        /* Get element's icon */
        let iconEl = null;
        if ($(thisEl).is('a') && $(thisEl).hasClass('btn')) {
            iconEl = $(thisEl).find('i');
        } else {
            if ($(thisEl).next('a').hasClass('btn')) {
                iconEl = $(thisEl).next('a').find('i');
            }
        }
        
        /* Get database info */
        let _token = $('input[name=_token]').val();
        let dataTable = $(thisEl).data('table');
        let dataField = $(thisEl).data('field');
        let dataId = $(thisEl).data('id');
        let dataLineId = $(thisEl).data('line-id');
        let dataValue = $(thisEl).data('value');
        
        /* Remove dot (.) from var (referring to the PHP var) */
        dataLineId = dataLineId.split('.').join("");
        
        let adminUri = '{{ urlGen()->adminUri() }}';
        let requestUrl = siteUrl + '/' + adminUri + '/ajax/' + dataTable + '/' + dataField + '';
        
        const ajax = $.ajax({
            method: 'POST',
            url: requestUrl,
            context: this,
            data: {
                'dataId': dataId,
                '_token': _token
            },
            beforeSend: function() {
                if (dataTable === 'countries' && dataField === 'active') {
                    /* Change the button indicator */
                    if (iconEl) {
                        iconEl.removeClass('fa-solid fa-download');
                        iconEl.addClass('spinner-border spinner-border-sm').css({'vertical-align': 'middle'});
                        iconEl.attr({'role': 'status', 'aria-hidden': 'true'});
                    }
                }
            }
        });
        ajax.done(function(xhr) {
            /* Check the required xhr attributes */
            if (
                    typeof xhr.success === 'undefined'
                    || typeof xhr.message === 'undefined'
                    || typeof xhr.isToggleOn === 'undefined'
                    || typeof xhr.table === 'undefined'
            ) {
                pnAlert('Error: Impossible to retrieve xhr attributes.', 'error');
                return false;
            }
            
            let message = xhr.message;
            
            if (xhr.success !== true) {
                pnAlert(message, 'notice');
                return false;
            }
            
            /* Decoration */
            if (xhr.table === 'countries' && dataField === 'active') {
                /* Country case */
                if (xhr.isToggleOn === true) {
                    $('#' + dataLineId).removeClass('fa-solid fa-toggle-off').addClass('fa-solid fa-toggle-on');
                    $('#install' + dataId).removeClass('btn-light')
                            .addClass('btn-success')
                            .addClass('text-white')
                            .empty()
                            .html('<i class="fa-solid fa-download"></i> <?php echo trans('admin.Installed'); ?>');
                } else {
                    $('#' + dataLineId).removeClass('fa-solid fa-toggle-on').addClass('fa-solid fa-toggle-off');
                    $('#install' + dataId).removeClass('btn-success')
                            .removeClass('text-white')
                            .addClass('btn-light')
                            .empty()
                            .html('<i class="fa-solid fa-download"></i> <?php echo trans('admin.Install'); ?>');
                }
                
                /* Reset the button indicator */
                if (iconEl) {
                    iconEl.removeClass('spinner-border spinner-border-sm').css({'vertical-align': ''});
                    iconEl.addClass('fa-solid fa-download').removeAttr('role aria-hidden');
                }
            } else {
                /* All other cases */
                if (xhr.isToggleOn === true) {
                    $('#' + dataLineId).removeClass('fa-solid fa-toggle-off').addClass('fa-solid fa-toggle-on').blur();
                } else {
                    $('#' + dataLineId).removeClass('fa-solid fa-toggle-on').addClass('fa-solid fa-toggle-off').blur();
                }
            }
            
            pnAlert(message, 'success');
            
            return false;
        });
        ajax.fail(function(xhr, textStatus, errorThrown) {
            let message = getErrorMessageFromXhr(xhr);
            if (message !== null) {
                pnAlert(message, 'error');
            }
            
            /* Reset the button indicator */
            if (iconEl) {
                iconEl.removeClass('spinner-border spinner-border-sm').css({'vertical-align': ''});
                iconEl.addClass('fa-solid fa-download').removeAttr('role aria-hidden');
            }
            
            return false;
        });
        
        return false;
    }
</script>

@include('admin.layouts.partials.alerts')
@include('admin.layouts.partials.maintenance')

<script>
    onDocumentReady((event) => {
        {{-- Maintenance Modal --}}
        @if (isset($errors) && $errors->any())
            @if ($errors->any() && old('maintenanceForm')=='1')
                {{-- Re-open the modal if error occured --}}
                openMaintenanceModal();
            @endif
        @endif
    });
</script>

@yield('after_scripts')
</body>
</html>
