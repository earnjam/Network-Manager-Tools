jQuery(document).ready(function( $ ) {
	$( ".progress" ).progressbar({
		value: 0
	});

	var action = 'build_site_info';
	if ( typeof data == "undefined" ) {
		var data = { n: 0 };
	}

	$('#site-info-form').submit(function() {
		$(this).hide();
		$(".progress").show();
		ajax_call(data);
		event.preventDefault();
	});

	function ajax_call(data) {
		data.action = action;
		$.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			dataType: 'json',
			success: function (data) {
				completion_check(data);
			},
			error: function (jqXHR, textStatus, thrownError) {
				console.log(jqXHR);
				console.log(textStatus);
				console.log(thrownError);
			}
		});
	}

	function completion_check(data) {
		percent = Math.round((data.n / data.total) * 100);
		$( ".progress" ).progressbar( "option", "value", percent );
		$( ".percent" ).text(percent + '%');
		if ( percent == 100 ) {
			location.reload();
		} else {
			ajax_call(data);
		}
	}

	var datatable = document.getElementById("network-data");
	if ( datatable ) {
		$.extend( $.fn.dataTableExt.oStdClasses, {
			"sSortAsc": "sorted asc",
			"sSortDesc": "sorted desc",
			"sSortable": "sortable desc",
			"sPagePrevious": "prev-page",
			"sPageNext": "next-page",
			"sPageLast": "last-page",
			"sPageFirst": "first-page",
			"sPageButtonStaticDisabled": "disabled",
			"sStripeEven": "alternate"
		} );
		var oTable = $(datatable).dataTable( {
			"aLengthMenu": [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, "All"]],
			"iDisplayLength": 10,
			"aaSorting": [[ 0, "asc" ]],
			"bAutoWidth": false,
			"sPaginationType": "full_numbers",
			"oSearch": {"sSearch": "", "bSmart": false},
			"sDom": "<'tablenav top'<'left one-page displaying-num' i><'right searchbox'f><'clear'>r>t<'btm-controls tablenav'<'left' l><'tablenav-pages' p><'clear'>>",
			"aoColumns": [
				// { "bSortable": false },
				null,
				null,
				null,
				null,
				null,
				//null,
				{ "bSortable": false }
			],
			"oLanguage": {
				"oPaginate": {
					"sFirst": "&laquo;",
					"sLast": "&raquo;",
					"sNext": "&rsaquo;",
					"sPrevious": "&lsaquo;"
				}
			}
		});

	}

	Array.prototype.slice.call(wp_addons);
	$('#network-data_filter input').autocomplete({
		source: wp_addons,
		select: function(event,ui) { this.value=ui.item.value; oTable.fnFilter( ui.item.value ); }
	});

});
