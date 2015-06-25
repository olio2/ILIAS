<#1>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("Sup", "sup", "sup",
	array());
ilDBUpdate3136::addStyleClass("Sub", "sub", "sub",
	array());
?>
<#2>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CarouselCntr", "ca_cntr", "div",
	array());
?>
<#3>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CarouselICntr", "ca_icntr", "div",
	array());
?>
<#4>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CarouselIHead", "ca_ihead", "div",
	array());
?>
<#5>
<?php
include_once("./Services/Migration/DBUpdate_3136/classes/class.ilDBUpdate3136.php");
ilDBUpdate3136::addStyleClass("CarouselICont", "ca_icont", "div",
	array());
?>
<#6>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_char'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_char', array(
		'type' => 'integer',
		'length' => 2,
		'notnull' => false
	));
}
?>
<#7>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_unlock'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_unlock', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#8>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','peer_valid'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_valid', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}
?>
<#9>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','team_tutor'))
{
	$ilDB->addTableColumn('exc_assignment', 'team_tutor', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#10>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','max_file'))
{
	$ilDB->addTableColumn('exc_assignment', 'max_file', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => false
	));
}
?>
<#11>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','deadline2'))
{
	$ilDB->addTableColumn('exc_assignment', 'deadline2', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	));
}
?>
<#12>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#13>
<?php
if(!$ilDB->tableColumnExists('exc_returned','late'))
{
	$ilDB->addTableColumn('exc_returned', 'late', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}
?>
<#14>
<?php
$ilDB->addTableColumn("il_wiki_data", "link_md_values",array (
	"type" => "integer",
	"length" => 1,
	"notnull" => false,
	"default" => 0,
));
?>
<#15>
<?php
if( !$ilDB->tableExists('member_noti') )
{
	$ilDB->createTable('member_noti', array(
		'ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'nmode' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));
		
	$ilDB->addPrimaryKey('member_noti', array('ref_id'));
}

?>
<#16>
<?php

if( !$ilDB->tableExists('member_noti_user') )
{
	$ilDB->createTable('member_noti_user', array(
		'ref_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'status' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		)
	));
		
	$ilDB->addPrimaryKey('member_noti_user', array('ref_id', 'user_id'));
}
?>
<#17>
<?php
if(!$ilDB->tableColumnExists('obj_members','contact'))
{
	$ilDB->addTableColumn(
		'obj_members',
		'contact',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}
?>
<#7>
<?php
	// register new object type 'awra' for awareness tool administration
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "typ", "awra", "Awareness Tool Administration", -1, ilUtil::now(), ilUtil::now()));
	$typ_id = $id;

	// create object data entry
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "awra", "__AwarenessToolAdministration", "Awareness Tool Administration", -1, ilUtil::now(), ilUtil::now()));

	// create object reference entry
	$ref_id = $ilDB->nextId('object_reference');
	$res = $ilDB->manipulateF("INSERT INTO object_reference (ref_id, obj_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($ref_id, $id));

	// put in tree
	$tree = new ilTree(ROOT_FOLDER_ID);
	$tree->insertNode($ref_id, SYSTEM_FOLDER_ID);

	// add rbac operations
	// 1: edit_permissions, 2: visible, 3: read, 4:write
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 1));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 2));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 3));
	$ilDB->manipulateF("INSERT INTO rbac_ta (typ_id, ops_id) VALUES (%s, %s)",
		array("integer", "integer"),
		array($typ_id, 4));
?>
<#8>
<?php
	$ilCtrlStructureReader->getStructure();
?>
