@php
	// Get a WYSIWYG editor
	$defaultEditor = 'textarea';
	$defaultEditor = config('settings.listing_form.wysiwyg_editor', $defaultEditor);
	$editor = $params['editor'] ?? $defaultEditor;
	$editorView = 'helpers.forms.fields.' . $editor;
	$editorView = view()->exists($editorView) ? $editorView : $defaultEditor;
	
	// Get all variables available in the current view, then
	// Filter out Laravel's internal variables if needed
	$allVars = get_defined_vars();
	$bladeInternalVars = ['__data', '__path', '__env', 'app', 'errors'];
	$passedParams = array_diff_key($allVars, array_flip($bladeInternalVars));
	
	// Retrieve the right format for value
	$value = $passedParams['value'] ?? '';
	$value = !isWysiwygEnabled() ? strip_tags($value) : $value;
	$passedParams['value'] = $value;
@endphp
@include($editorView, $passedParams)
