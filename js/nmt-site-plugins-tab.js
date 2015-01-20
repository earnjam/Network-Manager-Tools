jQuery(document).ready(function($) {
	$.urlParam = function(name){
		var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
		if (results===null){
			return null;
		}
		else{
			return results[1] || 0;
		}
	};
	var pluginsTab = '<a href="sites.php?page=nmt_site_plugins&id=' + $.urlParam('id') + '" class="nav-tab">Plugins</a>';
	$('.nav-tab-wrapper a:nth-child(3)').after(pluginsTab);
});