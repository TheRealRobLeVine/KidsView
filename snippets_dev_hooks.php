<?php
	/**
	 * Name: kv_alterViewFilterBasedOnEntityRepresentation
	 * Desc: Alters the view where for the Children view because some of the parameters may be null.
	 * 			In that case, remove that parameter from the query.
	 * 			I believe this to be "hacky" but frm_where_filter would not work
	 **/
	add_filter( 'frm_filter_view', 'kv_alterViewFilterBasedOnEntityRepresentation', 10, 1);
	function kv_alterViewFilterBasedOnEntityRepresentation( $view ) {
		if ($view->ID == FrmDb::get_var( 'posts', array( 'post_name' => sanitize_title( CHILDREN_LIST_VIEW_KEY ), 'post_type' => 'frm_display' ) )) {
			$count = 0;
			foreach ($view->frm_where_val as $where_value) {
	error_log("kv_alterViewFilterBasedOnEntityRepresentation where_value: " . $where_value);
				preg_match('~(?|"([^"]*)"|\'([^\']*)\')~', $where_value, $matches);
				$param = str_replace('"', '', $matches[0]);
	error_log("kv_alterViewFilterBasedOnEntityRepresentation param: " . $param);
				$value = get_user_meta(get_current_user_id(), $param, true);
	error_log("kv_alterViewFilterBasedOnEntityRepresentation value: " . $value);
				if (empty($value)) {
	error_log("kv_alterViewFilterBasedOnEntityRepresentation unsetting");				
					unset($view->frm_where[$count]);
				}
				++$count;
			}
		}
		return $view;
	}

	/**
	 * Name: kv_filterKindView
	 * Desc: Alters the view where for the Children view because some of the parameters may be null.
	 * 			In that case, remove that parameter from the query.
	 **/
	add_filter('frm_where_filter', 'kv_filterKindView', 10, 2);
	function kv_filterKindView($where, $args) {
		// 2024-07-24 get the view_id "manually" because FrmViewsDisplay::get_id_by_key has a bug that it's not just checking frm_display type posts
/*		$view_id = FrmDb::get_var( 'posts', array( 'post_name' => sanitize_title( CHILDREN_LIST_VIEW_KEY ), 'post_type' => 'frm_display' ) );

		if ($args['display']->ID == FrmViewsDisplay::get_id_by_key( CHILDREN_VIEW_KEY )) {
			error_log("kv_filterKindView where:" . print_r($where, true));
			$meta_value = null;
			// hacky code but I couldn't figure out how to get the meta_value from the structure
			foreach($where["0"] as $value) {
				error_log("kv_filterKindView meta_value in loop: $value");
				$meta_value = $value;
				break;
			}
			if (empty($meta_value)) {
				error_log("kv_filterKindView leaving with empty meta_value");
				$where = "fi.id='". $args['where_opt'] ."'";
//				error_log("kv_filterKindView where 2:" . print_r($where, true));
//				$where = "1 = 1";
			}
		}*/
		return $where;
	}

	/**
	 * Name: kv_afterChildCreateOrUpdate
	 * Desc: After a child entity is created or updated
	 * 			1. Set the domain, region and location ids based on the group chosen (assuming its imported)
	 **/
	add_action('frm_after_create_entry', 'kv_afterChildCreateOrUpdate', 30, 2);
	add_action('frm_after_update_entry', 'kv_afterChildCreateOrUpdate', 10, 2);
	function kv_afterChildCreateOrUpdate($entry_id, $form_id) {
		$form_key = FrmForm::get_key_by_id($form_id);
		if ($form_key != CHILD_FORM_KEY) {
			return;
		}
		$entry = FrmEntry::getOne($entry_id, true);
		$group_id = $entry->metas[FrmField::get_id_by_key(CHILD_FORM_KEY . "_group_id")];
		$output = file_get_contents(site_url() . ASSET_PATH . "get_entities_from_group.php?group_id=" . $group_id);
		$data = json_decode($output)[0];
error_log("kv_afterChildCreateOrUpdate data: " . print_r($data, true));
		$domain_id = $data->domain_id;
		$region_id = $data->region_id;
		$location_id = $data->location_id;
		$field_id = FrmField::get_id_by_key(CHILD_FORM_KEY . "_domain_id");
		kv_metaUpdateOrAdd( $entry_id, $field_id, $domain_id );
		$field_id = FrmField::get_id_by_key(CHILD_FORM_KEY . "_region_id");
		kv_metaUpdateOrAdd( $entry_id, $field_id, $region_id );
		$field_id = FrmField::get_id_by_key(CHILD_FORM_KEY . "_location_id");
		kv_metaUpdateOrAdd( $entry_id, $field_id, $location_id );
	}

	/**
	 * Name: kv_afterEntityCreateSendInvitation
	 * Desc: After an entity is created, send an invitation to the registered email, inviting the person as a manager
	 **/
	add_action('frm_after_create_entry', 'kv_afterEntityCreateSendInvitation', 30, 2);
	function kv_afterEntityCreateSendInvitation($entry_id, $form_id) {
		
		$form_key = FrmForm::get_key_by_id($form_id);
		if (!in_array($form_key, array(DOMAIN_FORM_KEY, REGION_FORM_KEY, LOCATION_FORM_KEY, GROUP_FORM_KEY, PARENT_FORM_KEY))) {
error_log("kv_afterEntityCreateSendInvitation - form key not matching $form_key");			
			return;
		}
		$domain_id = null;
		$region_id = null;
		$location_id = null;
		$group_id = null;
		$entity_id = null;
		$name = null;
		switch ($form_key) {
			case DOMAIN_FORM_KEY:
				// Bonus task - set its id into a hidden field
				FrmEntryMeta::add_entry_meta( $entry_id, FrmField::get_id_by_key(DOMAIN_FORM_KEY . "_domain_id"), null, $entry_id );
				$role = ROLE_CLIENT;
				$entity_id = $entry_id;
				break;
			case REGION_FORM_KEY:
				$role = ROLE_REGION;
				$region_id = $entry_id;
				$entity_id = $region_id;
				break;
			case LOCATION_FORM_KEY:
				$role = ROLE_LOCATION;
				$region_id = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(LOCATION_FORM_KEY . "_region_id"));
				$location_id = $entry_id;
				$entity_id = $location_id;
				break;
			case GROUP_FORM_KEY:
				$role = ROLE_GROUP;
				$region_id = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(GROUP_FORM_KEY . "_region_id"));
				$location_id = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(GROUP_FORM_KEY . "_location_id"));
				$group_id = $entry_id;
				$entity_id = $group_id;
				break;
			case PARENT_FORM_KEY:
				$role = ROLE_PARENT;
				$name = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(PARENT_FORM_KEY . "_name"));
				// there could be two parents, so save both names and emails
				$name_2 = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(PARENT_FORM_KEY . "_name_2"));
				$email_2 = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(PARENT_FORM_KEY . "_email_2"));
				$entity_id = $entry_id;
				break;
			default:
				return;
		}
		$domain_id = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key($form_key . "_domain_id"));
		$email = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key($form_key . "_email"));
error_log("kv_afterEntityCreateSendInvitation - domain: $domain_id");
error_log("kv_afterEntityCreateSendInvitation - region: $region_id");
error_log("kv_afterEntityCreateSendInvitation - location: $location_id");
error_log("kv_afterEntityCreateSendInvitation - group: $group_id");
error_log("kv_afterEntityCreateSendInvitation - email: $email");
error_log("kv_afterEntityCreateSendInvitation - name: " . print_r($name, true));
		$new_entry_id = kv_createUserInvitation($name, $email, $role, $entity_id, $domain_id, $region_id, $location_id, $group_id);
error_log("kv_afterEntityCreateSendInvitation - new entry id: $new_entry_id");
		if (!empty($email_2)) {
error_log("kv_afterEntityCreateSendInvitation - email_2: $email_2");
			$new_entry_id = kv_createUserInvitation($name_2, $email_2, $role, $entity_id, $domain_id, $region_id, $location_id, $group_id);
error_log("kv_afterEntityCreateSendInvitation - new entry id 2: $new_entry_id");
		}
	}

	/**
	 * Name: kv_createUserInvitation
	 * Desc: Creates a user invitation entry
	 * Return: integer (new entry ID)
	 **/
	function kv_createUserInvitation($name, $email, $role, $entity_id, $domain_id, $region_id, $location_id, $group_id) {
		global $wpdb;
		$new_entry_id = FrmEntry::create(array(
		  'form_id' => FrmForm::get_id_by_key(USER_INVITATION_FORM_KEY),
		  'item_key' => FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_items', 'item_key'),
		  'frm_user_id' => get_current_user_id(),
		  'item_meta' => array(
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_name") => $name,
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_email") => $email,
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_role") => $role,
			FrmField::get_id_by_key(USER_INVITATION_ENTITY_ID_FIELD_KEY) => $entity_id,
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_domain_id") => $domain_id,
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_region_id") => $region_id,
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_location_id") => $location_id,
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_group_id") => $group_id,
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_status") => INVITATION_STATUS_ISSUED,
			FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_key") =>  kv_generateUniqueKey()
		  ),
		));
		return $new_entry_id;		
	}
																 
	/**
	 * Name: kv_afterUserInvitationCreate
	 * Desc: After an invitiation is created, update the domain, region, location and group ids
	 *           based on the entity selected
	 **/
	add_action('frm_after_create_entry', 'kv_afterUserInvitationCreate', 30, 2);
	function kv_afterUserInvitationCreate($entry_id, $form_id) {
		if ($form_id == FrmForm::get_id_by_key(USER_INVITATION_FORM_KEY)) {
			// Step 1: Set the entity ids for the user from domain -> group
			$field_id = FrmField::get_id_by_key(USER_INVITATION_ENTITY_ID_FIELD_KEY);
error_log("kv_afterUserInvitationCreate field_id: $field_id");
			$entity_id = FrmEntryMeta::get_entry_meta_by_field($entry_id, $field_id);
			$role_slug = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_role"));
error_log("kv_afterUserInvitationCreate role: $role_slug");
			$entityEntry = FrmEntry::getOne($entity_id, true);
			$form_id = $entityEntry->form_id;
error_log("kv_afterUserInvitationCreate form_id: $form_id");			
			$field_prefix = FrmForm::get_key_by_id($form_id);
error_log("kv_afterUserInvitationCreate field_prefix: $field_prefix");
			$field_id = FrmField::get_id_by_key($field_prefix . "_domain_id");
error_log("kv_afterUserInvitationCreate field_id: $field_id");
			$domain_id = ($field_id  > 0) ? (isset($entityEntry->metas[$field_id]) ? $entityEntry->metas[$field_id] : null) : null;
			// if the role is management, klantenservice or coach, get the domain id(s) from the multi-choice field
			if (in_array($role_slug, array('um_management', 'um_klantenservice', 'um_coach'))) {
error_log("kv_afterUserInvitationCreate management, klantenservice, coach");
				$field_id = FrmField::get_id_by_key($field_prefix . "_domain_list");
				$domain_id = ($field_id  > 0) ? (isset($entityEntry->metas[$field_id]) ? $entityEntry->metas[$field_id] : null) : null;
error_log("kv_afterUserInvitationCreate domain list: $domain_id");
			}
			if (in_array($role_slug, ROLES_DOMAIN)) {
				$domain_id = $entity_id;
			}
error_log("kv_afterUserInvitationCreate domain_id: $domain_id");
			$id = FrmEntryMeta::add_entry_meta( $entry_id, FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_domain_id"), "", $domain_id );
error_log("kv_afterUserInvitationCreate new id: $id");
			$field_id = FrmField::get_id_by_key($field_prefix . "_region_id");
			$region_id = ($field_id  > 0) ? (isset($entityEntry->metas[$field_id]) ? $entityEntry->metas[$field_id] : null) : null;
			if (in_array($role_slug, ROLES_REGION)) {
				$region_id = $entity_id;
			}
			FrmEntryMeta::add_entry_meta( $entry_id, FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_region_id"), "", $region_id );
			$field_id = FrmField::get_id_by_key($field_prefix . "_location_id");
			$location_id = ($field_id  > 0)  ? (isset($entityEntry->metas[$field_id]) ? $entityEntry->metas[$field_id] : null) : null;
			if (in_array($role_slug, ROLES_LOCATION)) {
				$location_id = $entity_id;
			}
			FrmEntryMeta::add_entry_meta( $entry_id, FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_location_id"), "", $location_id );
			$field_id = FrmField::get_id_by_key($field_prefix . "_group_id");
			$group_id = ($field_id  > 0) ? (isset($entityEntry->metas[$field_id]) ? $entityEntry->metas[$field_id] : null) : null;
			if (in_array($role_slug, ROLES_GROUP)) {
				$group_id = $entity_id;
			}
			FrmEntryMeta::add_entry_meta( $entry_id, FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_group_id"), "", $group_id );
		}
	}
	
	/**
	  * Name: kv_afterFormidableUserRegistrationCreate
	  * Desc: Do things after a user registers via Formidable
	  *            1. set the invitation entry to "used"
	  *
	 **/
	add_action('frm_after_create_entry', 'kv_afterFormidableUserRegistrationCreate', 30, 2);
	function kv_afterFormidableUserRegistrationCreate($entry_id, $form_id) {
error_log("kv_afterFormidableUserRegistrationCreate");
		if ($form_id == FrmForm::get_id_by_key(USER_REGISTRATION_FORM_KEY)) {
			$invitation_id = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(USER_REGISTRATION_FORM_KEY . "_invitation_id"));
			$updated = FrmEntryMeta::update_entry_meta($invitation_id, FrmField::get_id_by_key(USER_INVITATION_FORM_KEY . "_status"), null, INVITATION_STATUS_USED);
error_log("kv_afterFormidableUserRegistrationCreate - updated: $updated");
			$role = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(USER_REGISTRATION_FORM_KEY . "_role"));
error_log("kv_afterFormidableUserRegistrationCreate - role: $role");
			$user = wp_get_current_user();
error_log("kv_afterFormidableUserRegistrationCreate - user: " . print_r($user, true));
			$updated_user = (array)$user;
error_log("kv_afterFormidableUserRegistrationCreate - updated_user before: " . print_r($updated_user, true));
			$updated_user['role'] = $role;
			$updated = wp_update_user($updated_user);
error_log("kv_afterFormidableUserRegistrationCreate - updated_user after: " . print_r($updated_user, true));
error_log("kv_afterFormidableUserRegistrationCreate - updated role: $updated");
			// in the case of a parent, update the parent entry's userid to the new one
			if ($role == ROLES_PARENT[0]) {
				// get the email address from the registration and find the matching entry in the parent form
				$user_id = get_current_user_id();
				$email = FrmEntryMeta::get_entry_meta_by_field($entry_id, FrmField::get_id_by_key(USER_REGISTRATION_FORM_KEY . "_email"));
error_log("kv_afterFormidableUserRegistrationCreate primary parent email");
				// search the parent form for the primary parent
				global $wpdb;
				$metasTable = $wpdb->prefix . "frm_item_metas";
				$field_id = FrmField::get_id_by_key(PARENT_FORM_KEY . "_email");
				$item_id = $wpdb->get_var($wpdb->prepare("SELECT item_id FROM %1s WHERE field_id = %d and meta_value = %s", $metasTable, $field_id, $email));
				if (!empty($item_id)) {
error_log("kv_afterFormidableUserRegistrationCreate found primary parent");
					$field_id = FrmField::get_id_by_key(PARENT_FORM_KEY . "_userid");
error_log("kv_afterFormidableUserRegistrationCreate setting primary parent user id to $user_id");
					kv_metaUpdateOrAdd($item_id, $field_id, $user_id);
error_log("kv_afterFormidableUserRegistrationCreate update_user_meta for $user_id with parent_entry_id: $item_id");
					update_user_meta($user_id, 'kv_parent_entry_id', $item_id);
				}
				else {
					// search the parent form for the secondary parent
					$field_id = FrmField::get_id_by_key(PARENT_FORM_KEY . "_email_2");
					$item_id = $wpdb->get_var($wpdb->prepare("SELECT item_id FROM %1s WHERE field_id = %d and meta_value = %s", $metasTable, $field_id, $email));
					if (!empty($item_id)) {
error_log("kv_afterFormidableUserRegistrationCreate found secondary parent");
						$field_id = FrmField::get_id_by_key(PARENT_FORM_KEY . "_userid_2");
error_log("kv_afterFormidableUserRegistrationCreate setting secondary parent user id to $user_id");
						kv_metaUpdateOrAdd($item_id, $field_id, $user_id);
error_log("kv_afterFormidableUserRegistrationCreate update_user_meta for $user_id with parent_entry_id: $item_id");
						update_user_meta($user_id, 'kv_parent_entry_id', $item_id);
					}
				}
			}
		}
	}

	/**
	  * Name: kv_populateRoleList
	  * Desc: populate the list of roles in the user invitation droplist
	  *
	 **/
	add_filter('frm_setup_new_fields_vars', 'kv_populateRoleList', 20, 2);
	add_filter('frm_setup_new_fields_vars', 'kv_populateRoleList', 20, 2);
	function kv_populateRoleList($values, $field) {
		if ($field->field_key != USER_INVITATION_ROLE_FIELD_KEY) {
			return $values;
		}
		$roleEntries = FrmEntry::getAll(array('it.form_id' => FrmForm::get_id_by_key(ROLE_FORM_KEY)));
		unset($values['options']);
		$values['options'] = array('');
/*		$values['options'][''] = '';*/
		foreach($roleEntries as $roleEntry) {
			$role = FrmEntry::getOne($roleEntry->id, true);
			$values['options'][$role->metas[FrmField::get_id_by_key(ROLE_SLUG_FIELD_KEY)]] = [$role->metas[FrmField::get_id_by_key(ROLE_NAME_FIELD_KEY)]];
		}
/*error_log("kv_populateRoleList: roles" . print_r($values, true));*/
		asort($values['options']);
		return $values;
	}

	/**
	  * Name: kv_populateRegistrationFromInvitation
	  * Desc: populate the registration form with stuff from the invitation entry
	  *
	 **/
	add_filter('frm_setup_new_fields_vars', 'kv_populateRegistrationFromInvitation', 20, 2);
	function kv_populateRegistrationFromInvitation($values, $field) {
		if (!str_starts_with($field->field_key, USER_INVITATION_FORM_KEY)) {
			return $values;
		}
		switch ($field->field_key) {
			case USER_INVITATION_FORM_KEY . "_domain_id":
				break;
			case USER_INVITATION_FORM_KEY . "_region_id":
				break;
			case USER_INVITATION_FORM_KEY . "_location_id":
				break;
			case USER_INVITATION_FORM_KEY . "_group_id":
				break;
		}
//		$values['value'] = 
		return $values;
	}

	/**
		Name: kv_populateRoleDropDown
		Desc: populates the drop down with a list of Ultimate Member user roles
	 */
	add_filter('frm_setup_new_fields_vars', 'kv_populateRoleDropDown', 20, 2);
	add_filter('frm_setup_edit_fields_vars', 'kv_populateRoleDropDown', 20, 2);
	function kv_populateRoleDropDown( $values, $field ) {
		$field_key = $field->field_key;
		 if ( $field_key == 'user-invitation_role_populate' ) {
			$values['options'] = array( );
			$um_roles = get_option( 'um_roles', array() );  // if you need to include WP roles, use UM()->roles()->get_roles()
			$rolesForDropdown = array();
			foreach ($um_roles as $roleIndex => $roleSlug) {
				 $values['options'][] = array(
					 'label' =>  UM()->roles()->get_role_name( $roleSlug ),
					 'value' =>  $roleSlug
				 );
			}
		 }
		 return $values;
	}


	add_filter('frm_setup_new_fields_vars', 'kv_populateStaffDropDown', 20, 2);
	add_filter('frm_setup_edit_fields_vars', 'kv_populateStaffDropDown', 20, 2);
	
	/**
		Name: kv_populateStaffDropDown
		Desc: populates the drop down with a list of staff members that the current user can choose from
	 */
	function kv_populateStaffDropDown( $values, $field ) {
		 return $values;
	}

	add_filter('frm_setup_new_fields_vars', 'kv_populateEntityDropDown', 20, 2);
	add_filter('frm_setup_edit_fields_vars', 'kv_populateEntityDropDown', 20, 2);

	/**
		Name: kv_populateEntityDropDown
		Desc: populates the drop down with a list of entities (e.g., groups) the user can choose from
	 */
	function kv_populateEntityDropDown( $values, $field ) {
		 return $values;
	}

