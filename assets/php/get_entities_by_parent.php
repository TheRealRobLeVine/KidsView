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
	
	$domain_id = !empty($_POST["domain_id"]) ? $_POST["domain_id"] : $_GET["domain_id"];
	$parent_id = !empty($_POST["parent_id"]) ? $_POST["parent_id"] : $_GET["parent_id"];
	$parent_type = !empty($_POST["parent_type"]) ? $_POST["parent_type"] : $_GET["parent_type"];
error_log("get_entities_by_parent = domain: $domain_id parent: $parent_id parent_type: $parent_type");
	$domain = new kv_domain($domain_id);
error_log(print_r($domain, true));
	$entities = array();
	switch ($parent_type) {
		case "domain":
			if (!empty($domain->getRegions())) {
				foreach($domain->getRegions() as $region) {
error_log("get_entities_by_parent - region " . print_r($region, true));
error_log("get_entities_by_parent - parententityid " . $region->getParentEntityId());
					if ($region->getParentEntityId() == $parent_id) {
						$entities[] = array("id" => $region->getID(), "name" => $region->getName());
					}
				}
			}
			break;
		case "region":
			if (!empty($domain->getRegions())) {
				foreach($domain->getRegions() as $region) {
					if (!empty($region->getLocations())) {
						foreach($region->getLocations() as $location) {
							if ($location->getParentEntityId() == $parent_id) {
								$entities[] = array("id" => $location->getID(), "name" => $location->getName());
							}
						}
					}
				}
			}
			break;
		case "group":
			if (!empty($domain->getRegions())) {
				foreach($domain->getRegions() as $region) {
					if (!empty($region->getLocations())) {
						foreach($region->getLocations() as $location) {
							foreach($location->getGroups() as $group) {
								if ($group->getParentEntityId() == $parent_id) {
									$entities[] = array("id" => $group->getID(), "name" => $group->getName());
								}
							}
						}
					}
				}
			}
			break;

	}

	$key_values = array_column($entities, 'name'); 
	array_multisort($key_values, SORT_ASC, $entities);
	
	echo json_encode($entities);
	
	exit;
?>