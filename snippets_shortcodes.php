<?php
	/**
	 * Name: kv_generateUniqueKey
	 * Desc: Generates a random key for use with the user invitation feature
	 *
	 * Return: random key
	 **/
	add_shortcode("kv-unique-key", "kv_generateUniqueKey");
	function kv_generateUniqueKey() {
		global $wpdb;
		
		return FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_items', 'item_key', 0, 12 );
	}
	
	/**
	 * Name: kv_entityBanner
	 * Desc: Generates an "banner" for a view that shows the domain, region, location, group names, depending on the user's role
	 *
	 * Return: string
	 **/
	add_shortcode("kv-entity-banner", "kv_entityBanner");
	function kv_entityBanner() {
		$banner = "";
		$user_meta = get_user_meta( get_current_user_id() );
		$domain_ids = (isset($user_meta["domain_id"])) ? $user_meta["domain_id"] : null;
		$region_id = (isset($user_meta["region_id"])) ? $user_meta["region_id"][0] : null;
		$location_id = (isset($user_meta["location_id"])) ? $user_meta["location_id"][0] : null;
error_log("kv_entityBanner");
		// if $domains is not set, then use all of them
		if (null == $domain_ids || strlen($domain_ids[0]) < 1) {
			$banner = "<strong>Alle Domeinen</strong>";
		}
		else {
			$banner = "<strong>Domein:</strong> ";
			foreach($domain_ids as $domain_id) {
				$names[] = FrmEntryMeta::get_entry_meta_by_field($domain_id, FrmField::get_id_by_key(DOMAIN_FORM_KEY . "_name"));
			}
			$banner .= implode(", ", $names);

			if (!empty($region_id)) {
				$name = FrmEntryMeta::get_entry_meta_by_field($region_id, FrmField::get_id_by_key(REGION_FORM_KEY . "_name"));
				$banner .= " <strong>Regio:</strong> " . $name; 
				if (!empty($location_id)) {
					$name = FrmEntryMeta::get_entry_meta_by_field($location_id, FrmField::get_id_by_key(LOCATION_FORM_KEY . "_name"));
					$banner .= " <strong>Locatie:</strong> " . $name; 
					if (!empty($group_id)) {
						$name = FrmEntryMeta::get_entry_meta_by_field($group_id, FrmField::get_id_by_key(GROUP_FORM_KEY . "_name"));
						$banner .= " <strong>Groep:</strong> " . $name; 
					}
					else {
						$banner .= " <strong>Groep:</strong> All Groepen";
					}
				}
				else {
					$banner .= " <strong>Locatie:</strong> All Locaties";
				}
			}
			else {
				$banner .= " <strong>Regio:</strong> All Regio's";
			}
		}
		
error_log("kv_entityBanner banner: $banner");
		return $banner;
	}
