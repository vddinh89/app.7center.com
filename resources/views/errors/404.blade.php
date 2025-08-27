@extends('errors.master')

@php
	// Get page error title
	$titleKey = 'global.error_http_404_title';
	$title = trans($titleKey);
	if ($title === $titleKey) {
		$title = 'Page not found';
	}
	
	// Get page error message
	$messageKey = 'global.error_http_404_message';
	$message = trans($messageKey, ['url' => url('/')]);
	if ($message === $messageKey) {
		if (isset($exception) && $exception instanceof \Throwable) {
			$message = $exception->getMessage();
			$message = str_replace(base_path(), '', $message);
		}
	}
@endphp

@section('title', $title)
@section('status', 404)
@section('message')
	{!! $message !!}
@endsection
