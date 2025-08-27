{{-- checkbox --}}
@php
	use App\Http\Controllers\Web\Admin\UserController;
	use App\Models\Permission;
	use App\Models\User;
	
	$isDeleteAccessAllowed = (isset($xPanel) && !$xPanel->hasAccess('delete'));
	// Security for Admin Users
	$isOnUserControllerPage = str_contains(currentRouteAction(), UserController::class);
	$userHasPermission = (isset($entry) && $entry instanceof User && $entry->can(Permission::getStaffPermissions()));
	
	$disabled = '';
	if (
		$isDeleteAccessAllowed
		|| ($isOnUserControllerPage && $userHasPermission)
	) {
		$disabled = 'disabled="disabled"';
	}
@endphp
<td class="dt-checkboxes-cell">
	<input name="entryId[]" type="checkbox" value="{{ $entry->{$column['name']} }}" class="dt-checkboxes" {!! $disabled !!}>
</td>
