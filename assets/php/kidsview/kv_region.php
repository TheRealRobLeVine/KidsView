<?php
namespace KidsView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/* The fc_formObject base class is not included by default, so we need to load it */
if ( ! class_exists( '\KidsView\kv_entity' ) ) {
	require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_entity.php';
}
if ( ! class_exists( '\KidsView\kv_location' ) ) {
	require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_location.php';
}
use KidsView\kv_entity;
use KidsView\kv_location;
use FrmField;
use FrmEntry;

class kv_region extends kv_entity {

	const ROLES = ["um_regiomanager", "um_regio"];

	private $locations;

	public function __construct($region) {
		$this->id = $region->id;
		$this->parentEntityId = $region->metas[FrmField::get_id_by_key(kv_entity::REGION_FORM_KEY . "_domain_id")];
error_log("kv_region - __construct parentEntityID " . $this->parentEntityId);
$testID = $this->getParentEntityId();
error_log("kv_region - __construct testID " . $testID);

		$this->name = $region->metas[FrmField::get_id_by_key(kv_entity::REGION_FORM_KEY . "_name")];
error_log("kv_region - __construct getName " . $this->getName());
		$this->email = $region->metas[FrmField::get_id_by_key(kv_entity::REGION_FORM_KEY . "_email")];
		$this->phone = $region->metas[FrmField::get_id_by_key(kv_entity::REGION_FORM_KEY . "_phone")];
		$this->setLocations();
	}
	
	private function setLocations() {
		global $wpdb;

		// load all the regions in the domain
		$metasTable = $wpdb->prefix . "frm_item_metas";
		$field_id = FrmField::get_id_by_key(kv_entity::LOCATION_REGION_ID_FIELD_KEY);
		$location_ids = $wpdb->get_col($wpdb->prepare("SELECT item_id FROM %1s WHERE field_id = %d and meta_value = %d", $metasTable, $field_id, $this->id));
		foreach($location_ids as $location_id) {
			$location = FrmEntry::getOne($location_id, true);
			$this->locations[] = new kv_location($location);
		}
	}
	
	public function getLocations() {
		return $this->locations;
	}
	
}

?>