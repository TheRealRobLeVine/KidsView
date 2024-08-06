<?php
namespace KidsView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/* The fc_formObject base class is not included by default, so we need to load it */
if ( ! class_exists( '\KidsView\kv_entity' ) ) {
	require_once ABSPATH . 'wp-content/themes/Avada-Child-Theme/assets/php/kidsview/kv_entity.php';
}
use KidsView\kv_entity;
use FrmField;

class kv_group extends kv_entity {

	protected $services;

	const ROLES = ["um_groep", "um_groepmanager"];	

	public function __construct($group) {
		$this->id = $group->id;
		$this->parentEntityId = $group->metas[FrmField::get_id_by_key(kv_entity::GROUP_FORM_KEY . "_location_id")];
		$this->name = $group->metas[FrmField::get_id_by_key(kv_entity::GROUP_FORM_KEY . "_name")];
		$this->email = $group->metas[FrmField::get_id_by_key(kv_entity::GROUP_FORM_KEY . "_email")];
		$this->phone = $group->metas[FrmField::get_id_by_key(kv_entity::GROUP_FORM_KEY . "_phone")];
	}
	
}

?>