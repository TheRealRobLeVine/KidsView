<?php
namespace KidsView;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class kv_entity {
	const DOMAIN_FORM_KEY = "domain";
	const REGION_FORM_KEY = "region";
	const LOCATION_FORM_KEY = "location";
	const GROUP_FORM_KEY = "group";

	const REGION_DOMAIN_ID_FIELD_KEY = self::REGION_FORM_KEY . "_domain_id";
	const LOCATION_REGION_ID_FIELD_KEY = self::LOCATION_FORM_KEY . "_region_id";
	const GROUP_LOCATION_ID_FIELD_KEY = self::GROUP_FORM_KEY . "_location_id";

	protected $id;
	protected $formidableItemId;
	protected $parentEntityId; // null in the case of a domain
	protected $name;
	protected $email;
	protected $phone;
	protected $address;
	protected $managers;
	protected $entityKidIDs; // list of kids' IDs that a person in this entity has access to
	protected $services;

	// parent_entity_id is the id of the level about the one you want to create
	//  if parent_entity_id is null, then it's the domain, and you can get that id from the current user
	public function __construct() {
	}
	
	public function getID() {
		return $this->id;
	}
	
	public function getParentEntityId() {
		return $this->parentEntityId;
	}
	
	public function getFormidableId() {
		return $this->formidableItemId;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function getPhone() {
		return $this->phone;
	}
	
	public function getManagers() {
		return $this->managers;
	}
	
	public function getEntityKidIDs() {
		return $this->entityKidIDs();
	}

	public function getServices() {
		return $this->services;
	}

	public function getAddress() {
		return $this->address;
	}

}

?>