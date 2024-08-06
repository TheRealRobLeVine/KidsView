<?php
namespace KidsView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/* The fc_formObject base class is not included by default, so we need to load it */
if ( ! class_exists( '\KidsView\kv_entity' ) ) {
	require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_entity.php';
}
if ( ! class_exists( '\KidsView\kv_region' ) ) {
	require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_region.php';
}
use KidsView\kv_entity;
use KidsView\kv_region;
use FrmField;
use FrmEntry;

class kv_domain extends kv_entity {

	private $regions;

	const ROLES = ["um_client", "um_management", "um_klantenservice", "um_coach"];
	const ROLES_NO_KIDS_DATA_ACCESS = ["um_financieel-management"];
	
	public function __construct($domain_id) {
		$this->id = $domain_id;
error_log("kv_domain __construct - domain_id: $domain_id");
		global $wpdb;
		$metasTable = $wpdb->prefix . 'frm_item_metas';
		$field_id = FrmField::get_id_by_key(kv_entity::DOMAIN_FORM_KEY . "_domain_id");
		$this->formidableItemId = $wpdb->get_var($wpdb->prepare("SELECT item_id FROM %1s WHERE field_id = %d and meta_value = %d", $metasTable, $field_id, $this->id));
error_log("kv_domain last query " . $wpdb->last_query);
error_log("kv_domain __construct formidableid " . $this->formidableItemId);
		$domain = FrmEntry::getOne($this->formidableItemId, true);
		if (null == $domain) {
error_log("kv_domain __construct null domain");
			return;
		}
		$this->name = $domain->metas[FrmField::get_id_by_key(kv_entity::DOMAIN_FORM_KEY . "_name")];
		$this->email = $domain->metas[FrmField::get_id_by_key(kv_entity::DOMAIN_FORM_KEY . "_email")];
		$this->phone = $domain->metas[FrmField::get_id_by_key(kv_entity::DOMAIN_FORM_KEY . "_phone")];
		$this->setRegions();
	}
	
	private function setRegions() {
		global $wpdb;
error_log("kv_domain __setRegions");
		// load all the regions in the domain
		$metasTable = $wpdb->prefix . "frm_item_metas";
		$field_id = FrmField::get_id_by_key(kv_entity::REGION_FORM_KEY . "_domain_id");
		$region_ids = $wpdb->get_col($wpdb->prepare("SELECT item_id FROM %1s WHERE field_id = %d and meta_value = %d", $metasTable, $field_id, $this->id));
		foreach($region_ids as $region_id) {
error_log("kv_domain __setRegions loop");
			$region = FrmEntry::getOne($region_id, true);
			$this->regions[] = new kv_region($region);
		}
	}
	
	public function getRegions() {
		return $this->regions;
	}
	
}

?>