jQuery(document).ready(function($) {
	$('.disable').parents('.inactive').removeClass('inactive').addClass('enabled');
	var selectbox = document.getElementById('bulk-action-selector-top');
	text = '<option value="-1" selected="selected">Bulk Actions</option>' +
	'<option value="activate-selected">Network Activate</option>' +
	'<option value="deactivate-selected">Network Deactivate</option>' +
	'<option value="enable-selected">Network Enable</option>' +
	'<option value="disable-selected">Network Disable</option>' +
	'<option value="update-selected">Update</option>' +
	'<option value="delete-selected">Delete</option>';
	$(selectbox).html(text);
});