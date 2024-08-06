<?php

	$path = preg_replace('/wp-content.*$/','',__DIR__);
	require($path . '/wp-load.php');

	if ( ! defined( 'ABSPATH' ) ) {
		die( 'You are not allowed to call this page directly.' );
	}
	
	$domain_id = !empty($_POST["domain_id"]) ? $_POST["domain_id"] : $_GET["domain_id"];
	$parents = FrmEntry::getAll("it.form_id = " . FrmForm::get_id_by_key(PARENT_FORM_KEY));
	foreach($parents as $parent) {
		$name = null;
		$name_2 = null;
		if ($domain_id == FrmEntryMeta::get_entry_meta_by_field($parent->id, FrmField::get_id_by_key(PARENT_FORM_KEY . "_domain_id"))) {
			$name = FrmEntryMeta::get_entry_meta_by_field($parent->id, FrmField::get_id_by_key(PARENT_FORM_KEY . "_name"));
			$name_2 = FrmEntryMeta::get_entry_meta_by_field($parent->id, FrmField::get_id_by_key(PARENT_FORM_KEY . "_name_2"));
			if (!empty($name_2)) {
				$entities[] = array("id" => $parent->id, "lastname" => $name["last"], "firstname" => $name["first"], "lastname_2" => $name_2["last"], "firstname_2" => $name_2["first"]);
			}
			else {
				$entities[] = array("id" => $parent->id, "lastname" => $name["last"], "firstname" => $name["first"], "lastname_2" => null, "firstname_2" => null);
			}
		}
	}

	$key_values = array_column($entities, 'lastname'); 
	array_multisort($key_values, SORT_ASC, $entities);
	
	echo json_encode($entities);
	
	exit;
?>