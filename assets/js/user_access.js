	jQuery(document).ready(function($){
		"use strict";
		var origin = $(location).attr("origin");
		const searchParams = new URLSearchParams(window.location.search);
		var data_id = null;
		if (!searchParams.has('data_id')) {
			 $("#kv_data_view_container").remove();
			 $("#kv_access_message_container").show();
		}
		else {
			data_id = searchParams.get('data_id');
		}
		if (data_id == null) {
			return;
		}
		var url = origin + "/wp-content/themes/Avada-Child-Theme/assets/php/verify_user_access.php";  
		var page_type = $("#kv_page_identifier").val();
		$.ajax({
			  type: "POST",
			  data: {"page": page_type, "id" : data_id},
			  dataType: 'TEXT',
			  url: url,
		}).done(function (response) {
			if ("Y" == response) {
				 $("#kv_data_view_container").show();
				 $("#kv_access_message_container").hide();
			}
			else {
				 $("#kv_data_view_container").remove();
				 $("#kv_access_message_container").show();
			}
		}).fail(function (jqXHR, textStatus, errorThrown) { 
			console.error("Failed to get user access");
		});
	});