<?php

	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You are not allowed to call this page directly.' );
	}
	
	$key = !empty($_POST["key"]) ? $_POST["key"] : $_GET["key"];
	$formID = FrmForm::get_id_by_key(USER_INVITATION_FORM_KEY);
	global $wpdb;

	// load all the regions in the domain
	$metasTable = $wpdb->prefix . "frm_item_metas";
	$field_id = FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_key");
	$item_id = $wpdb->get_var($wpdb->prepare("SELECT item_id FROM %1s WHERE field_id = %d and meta_value = %s", $metasTable, $field_id, $key));

	$data = array();
	if (!empty($item_id)) {
		$entry = FrmEntry::getOne($item_id, true);
		$status = $entry->metas[FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_status")];
		switch($status) {
			case "ISSUED":
				$firstname = $entry->metas[FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_name")]["first"];
				$lastname = $entry->metas[FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_name")]["last"];
				$entity = $entry->metas[FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_entity_populate")];
				$role = $entry->metas[FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_role")];
				$email = $entry->metas[FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_email")];

				$domain_id = $entry->metas[FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_domain_id")];
				$field_id = USER_INVITATION_FORM_KEY . "_region_id";
				$region_id = isset($entry->metas[FrmField::get_id_by_key($field_id)]) ? $entry->metas[FrmField::get_id_by_key($field_id)] : null;
				$field_id = USER_INVITATION_FORM_KEY . "_location_id";
				$location_id = isset($entry->metas[FrmField::get_id_by_key($field_id)]) ? $entry->metas[FrmField::get_id_by_key($field_id)] : null;
				$field_id = USER_INVITATION_FORM_KEY . "_group_id";
				$group_id = isset($entry->metas[FrmField::get_id_by_key($field_id)]) ? $entry->metas[FrmField::get_id_by_key($field_id)] : null;

				$data = array("invitation_id" => $item_id, "role" => $role, "firstname" => $firstname, "lastname" => $lastname, "entity" => $entity, "email" => $email, "domain" => $domain_id, "region" => $region_id, "location" => $location_id, "group" => $group_id);
				break;
			case "USED":
				$data = array("error" => "The invitation has already been accepted");
				break;
			default:
				break;
		}
	}
	else {
		$data = array("error" => "The invitation is invalid");
	}

	echo json_encode($data);
	
	exit;
?>