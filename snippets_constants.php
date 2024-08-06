<?php
	const ASSET_PATH = "/wp-content/themes/Avada-Child-Theme/assets/php/";

	define("USER_REGISTRATION_FORM_KEY", "user-registration");
	define("USER_INVITATION_FORM_KEY", "user-invitation");
	
	// entity forms (note: these are duplicated in the kv_entity class in /themes/Avada-Child-Theme/assets/php/)
	define("DOMAIN_FORM_KEY", "domain");
	define("REGION_FORM_KEY", "region");
	define("LOCATION_FORM_KEY", "location");
	define("GROUP_FORM_KEY", "group");
	define("CHILD_FORM_KEY", "child");
	define("PARENT_FORM_KEY", "parent");
	define("ROLE_FORM_KEY", "role");
	
	// views
	define("CHILDREN_LIST_VIEW_KEY", "children-list");

	define("USER_ROLE_GROUP_MANAGER", "");
	define("USER_ROLE_LOCATION_MANAGER", "");
	define("USER_ROLE_REGION_MANAGER", "");
	define("USER_ROLE_ORGANIZATION_MANAGER", "");
	
	const USER_META_DOMAIN_ID = "domain_id";
	const USER_META_REGION_ID = "region_id";
	const USER_META_LOCATION_ID = "location_id";
	const USER_META_GROUP_ID = "group_id";

	// field keys
	const USER_INVITATION_ENTITY_ID_FIELD_KEY = "user-invitation_entity_populate";
	const USER_INVITATION_DOMAIN_ID_FIELD_KEY = "user-invitation_domain";
	const USER_INVITATION_REGION_ID_FIELD_KEY = "user-invitation_region";
	const USER_INVITATION_LOCATION_ID_FIELD_KEY = "user-invitation_location";
	const USER_INVITATION_GROUP_ID_FIELD_KEY = "user-invitation_group";
	const USER_INVITATION_ROLE_FIELD_KEY = "user-invitation_role";

	const ROLE_NAME_FIELD_KEY = "role_name";
	const ROLE_SLUG_FIELD_KEY = "role_slug";

	const CHILD_PARENT_FIELD_KEY = "child_parent";

	// roles (note: these are duplicated in the kv_ classes in /themes/Avada-Child-Theme/assets/php/)
	const ROLES_DOMAIN = ["um_client", "um_management", "um_klantenservice", "um_coach"];
	const ROLES_REGION = ["um_regiomanager", "um_regio"];
	const ROLES_LOCATION = ["um_locatiemanager", "um_assistent-locatiemanager", "um_locatie", "um_ggd-inspecteur"];	
	const ROLES_GROUP = ["um_groepmanager", "um_groep"];
	const ROLES_PARENT = ["um_ouder"];
	const ROLES_NO_DATA_ACCESS = ["um_onboarding-intern", "um_onboarding-extern", "wdm_instructor"];
	const ROLES_LIMITED_DATA_ACCESS = ["um_financieel-management"]; // can see child names and parent data but no specific kid data

	const ROLE_CLIENT = "um_client";
	const ROLE_MAMANGEMENT = "um_management";
	const ROLE_CLIENT_SERVICE = "um_klantenservice";
	const ROLE_FINANCIAL_MANAGEMENT = "um_financieel-management";
	const ROLE_COACH = "um_coach";
	const ROLE_REGION = "um_regio";
	const ROLE_REGION_MANGER = "um_regiomanager";
	const ROLE_LOCATION = "um_locatie";
	const ROLE_LOCATION_MANAGER = "um_locatiemanager";
	const ROLE_LOCATION_ASSISTANT_MANAGER = "um_assistent-locatiemanager";
	const ROLE_LOCATION_GGD_INSPECTOR = "um_ggd-inspecteur";
	const ROLE_GROUP = "um_groep";
	const ROLE_GROUP_MANAGER = "um_groepmanager";
	const ROLE_PARENT = "um_ouder";
	const ROLE_STAFF_INTERNAL = "um_medewerker-intern";
	const ROLE_STAFF_EXTERNAL = "um_medewerker-extern";
	const ROLE_ONBOARDING_INTERNAL = "um_onboarding-intern";
	const ROLE_ONBOARDING_EXTERNAL = "um_onboarding-extern";
	const ROLE_INSTRUCTOR = "wdm_instructor";

	// invitation status codes
	const INVITATION_STATUS_ISSUED = "ISSUED";
	const INVITATION_STATUS_USED = "USED";

	// Form actions
	const INVITATION_ACTION_SEND_EMAIL = 3398;