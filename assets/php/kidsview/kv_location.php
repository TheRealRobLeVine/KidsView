<?php
namespace KidsView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/* The fc_formObject base class is not included by default, so we need to load it */
if ( ! class_exists( '\KidsView\kv_entity' ) ) {
	require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_entity.php';
}
if ( ! class_exists( '\KidsView\kv_group' ) ) {
	require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_group.php';
}
use KidsView\kv_entity;
use KidsView\kv_group;
use FrmField;
use FrmEntry;

class kv_location extends kv_entity {

	private $groups;

	const ROLES = ["um_locatiemanager", "um_assistent-locatiemanager", "um_locatie", "um_ggd-inspecteur"];	

	public function __construct($location) {
		$this->id = $location->id;
		$this->parentEntityId = $location->metas[FrmField::get_id_by_key(kv_entity::LOCATION_FORM_KEY . "_region_id")];
		$this->name = $location->metas[FrmField::get_id_by_key(kv_entity::LOCATION_FORM_KEY . "_name")];
		$this->email = $location->metas[FrmField::get_id_by_key(kv_entity::LOCATION_FORM_KEY . "_email")];
		$this->phone = $location->metas[FrmField::get_id_by_key(kv_entity::LOCATION_FORM_KEY . "_phone")];
		$this->setGroups();
	}
	
	private function setGroups() {
		global $wpdb;

		// load all the regions in the domain
		$metasTable = $wpdb->prefix . "frm_item_metas";
		$field_id = FrmField::get_id_by_key(kv_entity::GROUP_LOCATION_ID_FIELD_KEY);
		$group_ids = $wpdb->get_col($wpdb->prepare("SELECT item_id FROM %1s WHERE field_id = %d and meta_value = %d", $metasTable, $field_id, $this->id));
		foreach($group_ids as $group_id) {
			$group = FrmEntry::getOne($group_id, true);
			$this->groups[] = new kv_group($group);
		}
	}
	
	public function getGroups() {
		return $this->groups;
	}
	
}

?>