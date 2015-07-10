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
<#18>
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
<#19>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#20>
<?php
	$s = new ilSetting("awrn");
	$s->set("max_nr_entries", 50);
?>
<#21>
<?php
if(!$ilDB->tableExists('buddylist'))
{
	$ilDB->createTable('buddylist', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'buddy_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('buddylist', array('usr_id', 'buddy_usr_id'));
}

if(!$ilDB->tableExists('buddylist_requests'))
{
	$ilDB->createTable('buddylist_requests', array(
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'buddy_usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'ignored' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),
		'ts' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	$ilDB->addPrimaryKey('buddylist_requests', array('usr_id', 'buddy_usr_id'));
	$ilDB->addIndex('buddylist_requests', array('buddy_usr_id', 'ignored'), 'i1');
}
?>
<#22>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#23>
<?php
if(!$ilDB->tableExists('mail_man_tpl'))
{
	$ilDB->createTable('mail_man_tpl', array(
		'tpl_id'    => array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		),
		'title'     => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => true
		),
		'context'   => array(
			'type'    => 'text',
			'length'  => 100,
			'notnull' => true
		),
		'lang'      => array(
			'type'    => 'text',
			'length'  => 2,
			'notnull' => true
		),
		'm_subject' => array(
			'type'    => 'text',
			'length'  => 255,
			'notnull' => false,
			'default' => null
		),
		'm_message' => array(
			'type'    => 'clob',
			'notnull' => false,
			'default' => null
		)
	));

	$ilDB->addPrimaryKey('mail_man_tpl', array('tpl_id'));
	$ilDB->createSequence('mail_man_tpl');
}
?>
<#24>
<?php
if(!$ilDB->tableExists('mail_tpl_ctx'))
{
	$ilDB->createTable('mail_tpl_ctx', array(
		'id'             => array(
			'type'    => 'text',
			'length'  => 100,
			'notnull' => true
		),
		'component'      => array(
			'type'    => 'text',
			'length'  => 100,
			'notnull' => true
		),
		'class' => array(
			'type'    => 'text',
			'length'  => 100,
			'notnull' => true
		),
		'path'           => array(
			'type'    => 'text',
			'length'  => 4000,
			'notnull' => false,
			'default' => null
		)
	));
	$ilDB->addPrimaryKey('mail_tpl_ctx', array('id'));
}
?>
<#25>
<?php
$ilDB->addIndex('mail_man_tpl', array('context'), 'i1');
?>
<#26>
<?php
if(!$ilDB->tableColumnExists('mail_saved', 'tpl_ctx_id'))
{
	$ilDB->addTableColumn(
		'mail_saved',
		'tpl_ctx_id',
		array(
			'type'    => 'text',
			'length'  => '100',
			'notnull' => false,
			'default' => null
		)
	);
}

if(!$ilDB->tableColumnExists('mail_saved', 'tpl_ctx_params'))
{
	$ilDB->addTableColumn(
		'mail_saved',
		'tpl_ctx_params',
		array(
			'type'    => 'blob',
			'notnull' => false,
			'default' => null
		)
	);
}
?>
<#27>
<?php
if(!$ilDB->tableColumnExists('mail', 'tpl_ctx_id'))
{
	$ilDB->addTableColumn(
		'mail',
		'tpl_ctx_id',
		array(
			'type'    => 'text',
			'length'  => '100',
			'notnull' => false,
			'default' => null
		)
	);
}

if(!$ilDB->tableColumnExists('mail', 'tpl_ctx_params'))
{
	$ilDB->addTableColumn(
		'mail',
		'tpl_ctx_params',
		array(
			'type'    => 'blob',
			'notnull' => false,
			'default' => null
		)
	);
}
?>
<#28>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#29>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');				
$blog_type_id = ilDBUpdateNewObjectType::getObjectTypeId('blog');
if($blog_type_id)
{					
	// not sure if we want to clone "write" or "contribute"?
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('redact', 'Redact', 'object', 3204);	
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($blog_type_id, $new_ops_id);						
	}
}	

?>
<#30>
<?php

$redact_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('redact');
if($redact_ops_id)
{
	include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
	ilDBUpdateNewObjectType::addRBACTemplate('blog', 'il_blog_editor', 'Editor template for blogs', 
		array(
			ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
			ilDBUpdateNewObjectType::RBAC_OP_READ,
			ilDBUpdateNewObjectType::RBAC_OP_WRITE,
			$redact_ops_id)
	);
}

?>
<#31>
<?php
if(!$ilDB->tableColumnExists('frm_posts','pos_cens_date'))
{
	$ilDB->addTableColumn('frm_posts', 'pos_cens_date', array(
			'type' => 'timestamp',
			'notnull' => false)
	);
}
?>
<#32>
<?php
if(!$ilDB->tableExists('frm_posts_deleted'))
{
	$ilDB->createTable('frm_posts_deleted',
		array(
			'deleted_id'   => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'deleted_date' => array(
				'type'    => 'timestamp',
				'notnull' => true
			),
			'deleted_by'  => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'forum_title' => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'thread_title'=> array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'post_title'  => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => true
			),
			'post_message'=> array(
				'type'    => 'clob',
				'notnull' => true
			),
			'post_date'   => array(
				'type'    => 'timestamp',
				'notnull' => true
			),
			'obj_id'      => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'ref_id'       => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'thread_id'   => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'forum_id'   => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true
			),
			'pos_display_user_id' => array(
				'type'    => 'integer',
				'length'  => 4,
				'notnull' => true,
				'default' => 0
			),
			'pos_usr_alias' => array(
				'type'    => 'text',
				'length'  => 255,
				'notnull' => false
			)
		));

	$ilDB->addPrimaryKey('frm_posts_deleted', array('deleted_id'));
	$ilDB->createSequence('frm_posts_deleted');
}
?>
<#33>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#34>
<?php

if (!$ilDB->tableColumnExists('adv_md_record_objs', 'optional'))
{
	$ilDB->addTableColumn('adv_md_record_objs', 'optional', array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0
	));
}
	
?>
<#35>
<?php

if (!$ilDB->tableColumnExists('adv_md_record', 'parent_obj'))
{
	$ilDB->addTableColumn('adv_md_record', 'parent_obj', array(
		"type" => "integer",
		"notnull" => false,
		"length" => 4
	));
}
	
?>
<#36>
<?php
$ilCtrlStructureReader->getStructure();
?>