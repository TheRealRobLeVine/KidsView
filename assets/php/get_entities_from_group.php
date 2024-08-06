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
	
	$group_id = !empty($_POST["group_id"]) ? $_POST["group_id"] : $_GET["group_id"];
error_log("get_entities_by_group = groupid: $group_id");

	$entities = array();
	// iterate all domains
	$domains = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(kv_entity::DOMAIN_FORM_KEY)));
//error_log("get_entities_by_group domain ids" . print_r($domains, true));

	foreach($domains as $domain) {	
		$domain_id = $domain->id;
		$domain = new kv_domain($domain_id);
		if (!empty($domain->getRegions())) {
			foreach($domain->getRegions() as $region) {
				if (!empty($region->getLocations())) {
					foreach($region->getLocations() as $location) {
						if (!empty($location->getGroups())) {
							foreach($location->getGroups() as $group) {
error_log("get_entities_by_group group id: " . $group->getID());
								if ($group->getID() == $group_id) {
									$entities[] = array("domain_id" => $domain->getID(), "region_id" => $region->getID(), "location_id" => $location->getID());
									break 4;
								}
							}
						}
					}
				}
			}
		}
	}
	if (empty($entities)) {
		error_log("get_entities_by_group - no matching group ID found");
	}
	echo json_encode($entities);
	
	exit;
?>