<?php
	/**
	 * Name: kv_afterUserInvitationCreate
	 * Desc: After an invitiation is created, update the domain, region, location and group ids
	 *           based on the entity selected
	 **/
	function getRoleSlugFromName($role_name_to_find) {
		$role_keys = get_option( 'um_roles', array() );
		foreach($role_keys as $role_key) {
			$role_name = UM()->roles()->get_role_name( $role_key );
			if ($role_name == $role_name_to_find) {
				return $role_key;
			}
			return null;
		}
	}
