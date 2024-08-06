<?php

	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You are not allowed to call this page directly.' );
	}
	$entity_id = $_POST["entity_id"];  // entity id to check
	$entity_type = $_POST["entity_type"]; // entity type to check
	$domain_id = $_POST["domain"]; // domain id to compare against

	$user_id = get_current_user_id();

	$domain = new kv_domain($domain_id);

	$in_domain = "N";

	switch ($entity_type) {
		case "region":
			if (!empty($domain->getRegions())) {
				foreach($domain->getRegions() as $region) {
					if ($region->getID() == $entity_id) {
						$in_domain = "Y";
						break;
					}
				}
			}
			break;		
		case "region":
			if (!empty($domain->getRegions())) {
				foreach($domain->getRegions() as $region) {
					if (!empty($region->getLocations())) {
						foreach($region->getLocations() as $location) {
							if ($location->getID() == $entity_id) {
								$in_domain = "Y";
								break 2;
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
							if (!empty($location->getGroups())) {
								foreach($region->getGroups() as $group) {
									if ($group->getID() == $entity_id) {
										$in_domain = "Y";
										break 3;
									}
								}
							}
						}
					}
				}
			}
			break;		
		default:
			break;
	}

	return $in_domain;
?>