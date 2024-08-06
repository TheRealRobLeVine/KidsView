<?php
	/**
	 * Name: kv_metaUpdateOrAdd
	 * Desc: Generates a random key for use with the user invitation feature
	 *
	 * Return: n/a
	 **/
	function kv_metaUpdateOrAdd($entry_id, $field_id, $value) {
		$updated   = FrmEntryMeta::update_entry_meta( $entry_id, $field_id, null, $value );
error_log("kv_metaUpdateOrAdd value: $value updated: $updated");
		if ( ! $updated ) {
			$added = FrmEntryMeta::add_entry_meta( $entry_id, $field_id, null, $value );
error_log("kv_metaUpdateOrAdd value: $value added: $added");
		}		
	}

	/**
	 * Name: kv_getEntitiesByChildId
	 * Desc: Gets the entity ids from the child entry
	 *
	 * Return: array
	 **/
	function kv_getEntitiesByChildId($id) {
		$entry = FrmEntry::getOne($id, true);
		$child_domain_id = null;
		$child_region_id = null;
		$child_location_id = null;
		$child_group_id = null;
		$child_parent_id = null;
		if (!empty($entry)) {
			$child_domain_id = (isset($entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_domain_id")])) ? $entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_domain_id")] : null;
			$child_region_id = (isset($entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_region_id")])) ? $entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_region_id")] : null;				
			$child_location_id = (isset($entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_location_id")])) ? $entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_location_id")] : null;			
			$child_group_id = (isset($entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_group_id")])) ? $entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_group_id")] : null;
			$child_parent_id = (isset($entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_parent")])) ? $entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_parent")] : null;
		}
		return array("domain_id" => $child_domain_id, "region_id" => $child_region_id, "location_id" => $child_location_id, "group_id" => $child_group_id, "parent_id" => $child_parent_id);
	}

