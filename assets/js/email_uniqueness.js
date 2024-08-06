	jQuery(document).ready(function($){
		"use strict";

		// the field to check is passed in via the script tag
		const script = document.querySelector("#email_unique_script");
		var emailFieldStartsWith = script.dataset.emailField;
		$("[id^=" + emailFieldStartsWith + "]").change(function() {
			var email = $(this).val();
			var emailField = $(this).attr("id");
			var origin = $(location).attr("origin");
			var url = origin + "/wp-content/themes/Avada-Child-Theme/assets/php/verify_email_unique.php";  
			$.ajax({
				  type: "POST",
  				  data: {"email": email},
				  dataType: 'TEXT',
				  url: url,
			}).done(function (responseText) {
				const validationErrorElementID = "frm_error_" + emailField;
				if ($('#'+validationErrorElementID).length > 0) {
					$('#'+validationErrorElementID).remove();
				}
				if (responseText.length > 0) {
					var insertElementHTML="&lt;div class='frm_error' id='"+validationErrorElementID+"' role='alert'&gt;"+responseText+"&lt;/div&gt;";
					$("#" + emailField).val("");
					insertElementHTML = insertElementHTML.replace(/&lt;/g, "<").replace(/&gt;/g, ">");
					$("#" + emailField).after(insertElementHTML);
				}
				if (responseText.length > 0) {
						$("#" + emailField).val();
				}
			}).fail(function (jqXHR, textStatus, errorThrown) { 
				console.error("Failed to verify email address");
			});
	  });

	});
