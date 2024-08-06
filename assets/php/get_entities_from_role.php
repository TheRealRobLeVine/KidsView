<?php

	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You are not allowed to call this page directly.' );
	}
	
	if ( ! class_exists( '\KidsView\kv_domain' ) ) {
		require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_domain.php';
	}
	use KidsView\kv_domain;
	if ( ! class_exists( '\KidsView\kv_region' ) ) {
		require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_region.php';
	}
	use KidsView\kv_region;
	if ( ! class_exists( '\KidsView\kv_location' ) ) {
		require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_location.php';
	}
	use KidsView\kv_location;
	if ( ! class_exists( '\KidsView\kv_group' ) ) {
		require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_group.php';
	}
	use KidsView\kv_group;
	
	$role = !empty($_POST["role"]) ? $_POST["role"] : $_GET["role"];
	$userID = get_current_user_id();

	$domainIDs = get_user_meta($userID, 'kv_domain_id'); 
error_log("get_entities_from_role domainIDs - " . print_r($domainIDs,true));
	if (empty($domainIDs) || strlen($domainIDs[0]) < 1) {
error_log("get_entities_from_role - no domains");
		// get all the domain ids
		$domain_list = FrmEntry::getAll(array("it.form_id" => FrmForm::get_id_by_key(DOMAIN_FORM_KEY)));
error_log("get_entities_from_role -domain_list " . print_r($domain_list, true));
		foreach($domain_list as $domain) {
			$domainIDs[] = $domain->id;
error_log("get_entities_from_role - adding domains ");
		}
	}
	if (!is_array($domainIDs)) {
		$domainIDs = array($domainIDs);
	}
	foreach($domainIDs as $domainID) {
		$domain = new kv_domain($domainID);
		if (empty($role)) {
			$user = wp_get_current_user();
			$roles = ( array ) $user->roles;
			$role = $roles[0]; // assumption: users only have one role
		}

		$entities = array();
		if (in_array($role, kv_region::ROLES)) {
			if (!empty($domain->getRegions())) {
				foreach($domain->getRegions() as $region) {
					$entities[] = array("id" => $region->getID(), "name" => $region->getName());
				}
			}
		}
		else {
			if (in_array($role, kv_location::ROLES)) {
				if (!empty($domain->getRegions())) {
					foreach($domain->getRegions() as $region) {
						if (!empty($region->getLocations())) {
							foreach($region->getLocations() as $location) {
								$entities[] = array("id" => $location->getID(), "name" => $location->getName());
							}
						}
					}
				}
			}
			else {
				if (in_array($role, kv_group::ROLES)) {
					if (!empty($domain->getRegions())) {
						foreach($domain->getRegions() as $region) {
							if (!empty($region->getLocations())) {
								foreach($region->getLocations() as $location) {
	error_log("get_entities_from_role - group loop " . print_r($region, true));
									if (!empty($location->getGroups())) {
										foreach($location->getGroups() as $group) {
											$entities[] = array("id" => $group->getID(), "name" => $group->getName());
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

	$key_values = array_column($entities, 'name'); 
	array_multisort($key_values, SORT_ASC, $entities);
	
	echo json_encode($entities);
	
	exit;
?>