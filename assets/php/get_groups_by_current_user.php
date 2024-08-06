<?php

	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You are not allowed to call this page directly.' );
	}
	
	if ( ! class_exists( '\KidsView\kv_entity' ) ) {
		require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_entity.php';
	}
	use KidsView\kv_entity;
	if ( ! class_exists( '\KidsView\kv_domain' ) ) {
		require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_domain.php';
	}
	use KidsView\kv_domain;

	$user_meta = get_user_meta( get_current_user_id() );
	$domain_ids = (isset($user_meta["domain_id"])) ? $user_meta["domain_id"] : null;
	$region_id = (isset($user_meta["region_id"])) ? $user_meta["region_id"][0] : null;
	$location_id = (isset($user_meta["location_id"])) ? $user_meta["location_id"][0] : null;

error_log("get_groups_by_current_user domain_ids: " . print_r($domain_ids, true));
error_log("get_groups_by_current_user region_id: " . $region_id);
error_log("get_groups_by_current_user location_id: " . $location_id);
	// if $domains is not set, then use all of them
	if (null == $domain_ids || (is_array($domain_ids) && strlen($domain_ids[0]) < 1)) {
error_log("get_groups_by_current_user empty domain list");
		$domains_list = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(kv_entity::DOMAIN_FORM_KEY)));
		foreach($domains_list as $domain_entry) {
error_log("get_groups_by_current_user adding to domain list");
			$domain_ids[] = $domain_entry->id;
		}
	}
	$entities = array();
	foreach($domain_ids as $domain_id) {
		$domain = new kv_domain($domain_id);

		if (!empty($domain->getRegions())) {
			foreach($domain->getRegions() as $region) {
				if (!empty($region->getLocations())) {
					foreach($region->getLocations() as $location) {
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

	$key_values = array_column($entities, 'name'); 
	array_multisort($key_values, SORT_ASC, $entities);
	
	echo json_encode($entities);
	
	exit;
?>