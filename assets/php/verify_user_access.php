<?php

	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You are not allowed to call this page directly.' );
	}
	
	$access = "N";

	$page = (isset($_POST["page"])) ? $_POST["page"] : null;
	$id = (isset($_POST["id"])) ? $_POST["id"] : null;
error_log("verify_user_access page: $page id: $id");
	if (null != $page || null != $id) {
		$user = wp_get_current_user();

		$roles = ( array ) $user->roles;

    		$role = $roles[0];
error_log("verify_user_access role: $role");
		if (in_array($role, ROLES_NO_DATA_ACCESS)) {
			return "N";
		}

		$user_id = get_current_user_id();
error_log("verify_user_access - user_id: $user_id");
		$user_domain_id = get_user_meta($user_id, "_kv_domain_id");	// could be an array
		$user_region_id = get_user_meta($user_id, "kv_region_id", true);				
		$user_location_id = get_user_meta($user_id, "kv_location_id", true);	
error_log("verify_user_access - user_location: $user_location_id");
		$user_group_id = get_user_meta($user_id, "kv_group_id", true);	

		switch ($page) {
			case "parent":
				$entry = FrmEntry::getOne($id, true);
				$parent_domain_id = $entry->metas[FrmField::get_id_by_key(PARENT_FORM_KEY . "_domain_id")];	
				switch ($role) {
					case ROLE_CLIENT:
						$access = "Y";
						break;
					case ROLE_MAMANGEMENT:
					case ROLE_CLIENT_SERVICE:
					case ROLE_COACH:
						if (is_array($domain_id)) {
							$access == (in_array($parent_domain_id, $user_domain_id)) ? "Y" : "N";
						}
						else {
							$access = ($parent_domain_id == $user_domain_id) ? "Y" : "N";
						}
						break;
					case ROLE_FINANCIAL_MANAGEMENT:
						$access = "N";
						break;
					case ROLE_REGION:
					case ROLE_REGION_MANGER:
						if (!empty($user_region_id) && empty($parent_domain_id)) {
							$access = file_get_contents(site_url() . ASSET_PATH . "verify_entity_in_domain.php?entity_id=$user_region_id&entity_type=region&domain_id=$parent_domain_id");
						}
						break;
					case ROLE_LOCATION:
					case ROLE_LOCATION_MANAGER:
					case ROLE_LOCATION_ASSISTANT_MANAGER:
					case ROLE_LOCATION_GGD_INSPECTOR:
						if (!empty($user_location_id) && empty($parent_domain_id)) {
							$access = file_get_contents(site_url() . ASSET_PATH . "verify_entity_in_domain.php?entity_id=$user_location_id&entity_type=location&domain_id=$parent_domain_id");
						}
						break;
					case ROLE_GROUP:
					case ROLE_GROUP_MANAGER:
					case ROLE_STAFF_INTERNAL:
					case ROLE_STAFF_EXTERNAL:
						break;
					case ROLE_PARENT:
						$access = ($id == get_user_meta( get_current_user_id(), "kv_parent_entry_id", true )) ? "Y" : "N";
						break;
				}
				break;
			case "child":
				// get the entity ids from the child
				$child_entities = kv_getEntitiesByChildId($id);
				$child_domain_id = $child_entities["domain_id"];				
				$child_region_id = $child_entities["region_id"];				
				$child_location_id = $child_entities["location_id"];				
				$child_group_id = $child_entities["group_id"];	
				$child_parent_id = $child_entities["parent_id"];	

				switch($role) {
					case ROLE_CLIENT:
						$access = "Y";
						break;
					case ROLE_MAMANGEMENT:
					case ROLE_CLIENT_SERVICE:
					case ROLE_COACH:
						if (is_array($domain_id)) {
							$access == (in_array($child_domain_id, $user_domain_id)) ? "Y" : "N";
						}
						else {
							$access = ($child_domain_id == $user_domain_id) ? "Y" : "N";
						}
						break;
					case ROLE_FINANCIAL_MANAGEMENT:
						$access = "N";
						break;
					case ROLE_REGION:
					case ROLE_REGION_MANGER:
						if (!empty($child_region_id) && !empty($user_region_id)) {
							$access = ($child_region_id == $user_region_id) ? "Y" : "N";
						}
						break;
					case ROLE_LOCATION:
					case ROLE_LOCATION_MANAGER:
					case ROLE_LOCATION_ASSISTANT_MANAGER:
					case ROLE_LOCATION_GGD_INSPECTOR:
error_log("child view location role child_location: $child_location_id and user_location: $user_location_id");
						if (!empty($child_location_id) && !empty($user_location_id)) {
							$access = ($child_location_id == $user_location_id) ? "Y" : "N";
						}
						break;
					case ROLE_GROUP:
					case ROLE_GROUP_MANAGER:
					case ROLE_STAFF_INTERNAL:
					case ROLE_STAFF_EXTERNAL:
						if (!empty($child_group_id) && !empty($user_group_id)) {
							$access = ($child_group_id == $user_group_id) ? "Y" : "N";
						}
						break;
					case ROLE_PARENT:
						$access = ($child_parent_id == get_user_meta( get_current_user_id(), "kv_parent_entry_id", true )) ? "Y" : "N";
						break;
					default: 
						$access = "N";
						break;
				}
				break;
			default:
				$access = "N";
				break;

		}
	}

error_log("verify_user_access returning $access");
	echo $access;
	
	exit;
?>