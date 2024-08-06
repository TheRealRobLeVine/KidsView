<?php

	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You are not allowed to call this page directly.' );
	}
	$email = $_POST["email"];
	
	$error_message = null;
	$emailFound = email_exists( $email );
	// if not found also check if the email has been invited already
	if (!$emailFound) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$metasTable = $wpdb->prefix . "frm_item_metas";
		$field_id = FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_email");
		$count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM %1s WHERE field_id = %d and meta_value = %s", $metasTable, $field_id, $email));
		if (!empty($count) && $count > 0) {
			$errorMessage = "$email is al uitgenodigd"; // has already been invited
		}
	}
	else {
		$errorMessage = "$email is al geregistreerd"; // is already registered
	}
	
    	echo (!empty($errorMessage)) ? $errorMessage : "";
    
	exit;
?>