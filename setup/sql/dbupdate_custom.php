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
<#37>
<?php
if(!$ilDB->tableColumnExists('frm_posts_deleted','is_thread_deleted'))
{
	$ilDB->addTableColumn('frm_posts_deleted', 'is_thread_deleted', array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0)
	);
}
?>	
<#38>
<?php
	if (!$ilDB->tableColumnExists("booking_schedule", "av_from"))
	{
		$ilDB->addTableColumn("booking_schedule", "av_from", array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
	if (!$ilDB->tableColumnExists("booking_schedule", "av_to"))
	{
		$ilDB->addTableColumn("booking_schedule", "av_to", array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
	}
?>
<#39>
<?php

if(!$ilDB->tableExists('exc_crit_cat'))
{
	$ilDB->createTable('exc_crit_cat', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'parent' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'pos' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));	
	$ilDB->addPrimaryKey('exc_crit_cat',array('id'));
	$ilDB->createSequence('exc_crit_cat');
}

?>
<#40>
<?php

if(!$ilDB->tableExists('exc_crit'))
{
	$ilDB->createTable('exc_crit', array(
		'id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'parent' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'type' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'title' => array(
			'type' => 'text',
			'length' => 255,
			'notnull' => false
		),
		'descr' => array(
			'type' => 'text',
			'length' => 1000,
			'notnull' => false
		),
		'pos' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));	
	$ilDB->addPrimaryKey('exc_crit',array('id'));
	$ilDB->createSequence('exc_crit');
}

?>
<#41>
<?php

if(!$ilDB->tableColumnExists('exc_crit','required'))
{
	$ilDB->addTableColumn('exc_crit', 'required', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 0
	));
}

?>
<#42>
<?php

if(!$ilDB->tableColumnExists('exc_crit','def'))
{
	$ilDB->addTableColumn('exc_crit', 'def', array(
		'type' => 'text',
		'length' => 4000,
		'notnull' => false
	));
}

?>
<#43>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#44>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_text'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_text', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}

?>
<#45>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_rating'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_rating', array(
		'type' => 'integer',
		'length' => 1,
		'notnull' => true,
		'default' => 1
	));
}

?>
<#46>
<?php

if(!$ilDB->tableColumnExists('exc_assignment','peer_crit_cat'))
{
	$ilDB->addTableColumn('exc_assignment', 'peer_crit_cat', array(
		'type' => 'integer',
		'length' => 4,
		'notnull' => false
	));
}

?>
<#47>
<?php
$ilDB->manipulate('DELETE FROM addressbook_mlist_ass');
?>
<#48>
<?php
if($ilDB->tableColumnExists('addressbook_mlist_ass', 'addr_id'))
{
	$ilDB->renameTableColumn('addressbook_mlist_ass', 'addr_id', 'usr_id');
}
?>
<#49>
<?php
if($ilDB->tableExists('addressbook'))
{
	$query = "
		SELECT ud1.usr_id 'u1', ud2.usr_id 'u2'
		FROM addressbook a1
		INNER JOIN usr_data ud1 ON ud1.usr_id = a1.user_id
		INNER JOIN usr_data ud2 ON ud2.login = a1.login
		INNER JOIN addressbook a2 ON a2.user_id = ud2.usr_id AND a2.login = ud1.login
		WHERE ud1.usr_id != ud2.usr_id
	";
	$res = $ilDB->query($res);
	while($row = $ilDB->fetchAssoc($res))
	{
		$this->db->replace(
			'buddylist',
			array(
				'usr_id'       => array('integer', $row['u1']),
				'buddy_usr_id' => array('integer', $row['u2'])
			),
			array(
				'ts' => array('integer', time())
			)
		);

		$this->db->replace(
			'buddylist',
			array(
				'usr_id'       => array('integer', $row['u2']),
				'buddy_usr_id' => array('integer', $row['u1'])
			),
			array(
				'ts' => array('integer', time())
			)
		);
	}

	$query = "
		SELECT ud1.usr_id 'u1', ud2.usr_id 'u2'
		FROM addressbook a1
		INNER JOIN usr_data ud1 ON ud1.usr_id = a1.user_id
		INNER JOIN usr_data ud2 ON ud2.login = a1.login
		LEFT JOIN addressbook a2 ON a2.user_id = ud2.usr_id AND a2.login = ud1.login
		WHERE a2.addr_id IS NULL
	";
	$res = $ilDB->query($res);
	while($row = $ilDB->fetchAssoc($res))
	{
		$this->db->replace(
			'buddylist_requests',
			array(
				'usr_id'       => array('integer', $row['u1']),
				'buddy_usr_id' => array('integer', $row['u2'])
			),
			array(
				'ts'      => array('integer', time()),
				'ignored' => array('integer', 0)
			)
		);
	}

	$ilDB->dropTable('addressbook');
}
if($ilDB->sequenceExists('addressbook'))
{
	$ilDB->dropSequence('addressbook');
}
?>
<#50>
<?php
$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'integer', 'text'),
	array(-1,  'buddysystem_request', 'mail')
);
if(!$ilDB->numRows($res))
{
	$ilDB->insert(
		'notification_usercfg',
		array(
			'usr_id'  => array('integer', -1),
			'module'  => array('text', 'buddysystem_request'),
			'channel' => array('text', 'mail')
		)
	);
}
?>
<#51>
<?php
$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'integer', 'text'),
	array(-1,  'buddysystem_request', 'osd')
);
if(!$ilDB->numRows($res))
{
	$ilDB->insert(
		'notification_usercfg',
		array(
			'usr_id'  => array('integer', -1),
			'module'  => array('text', 'buddysystem_request'),
			'channel' => array('text', 'osd')
		)
	);
}
?>
<#52>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#53>
<?php
require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcacheServer.php');
ilMemcacheServer::installDB();
?>
<#54>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('edit_wiki_navigation', 'Edit Wiki Navigation', 'object', 3220);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
	}
}
?>
<#55>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('delete_wiki_pages', 'Delete Wiki Pages', 'object', 3300);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
	}
}

?>
<#56>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('activate_wiki_protection', 'Set Read-Only', 'object', 3240);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
	}
}

?>
<#57>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#58>
<?php
	if(!$ilDB->tableExists('wiki_user_html_export') )
	{
		$ilDB->createTable('wiki_user_html_export', array(
			'wiki_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'usr_id' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'progress' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => true
			),
			'start_ts' => array(
				'type' => 'timestamp',
				'notnull' => false
			),
			'status' => array(
				'type' => 'integer',
				'length' => 1,
				'notnull' => true,
				'default' => 0
			)
		));
		$ilDB->addPrimaryKey('wiki_user_html_export', array('wiki_id'));
	}
?>
<#59>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');

$wiki_type_id = ilDBUpdateNewObjectType::getObjectTypeId('wiki');
if($wiki_type_id)
{
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('wiki_html_export', 'Wiki HTML Export', 'object', 3242);
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($wiki_type_id, $new_ops_id);
	}
}

?>
<#60>
<?php
	// register new object type 'logs' for Logging administration
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "typ", "logs", "Logging Administration", -1, ilUtil::now(), ilUtil::now()));
	$typ_id = $id;

	// create object data entry
	$id = $ilDB->nextId("object_data");
	$ilDB->manipulateF("INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
		"VALUES (%s, %s, %s, %s, %s, %s, %s)",
		array("integer", "text", "text", "text", "integer", "timestamp", "timestamp"),
		array($id, "logs", "__LoggingSettings", "Logging Administration", -1, ilUtil::now(), ilUtil::now()));

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
<#61>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#62>
<?php

	if(!$ilDB->tableExists('log_components'))
	{
		$ilDB->createTable('log_components', array(
			'component_id' => array(
				'type' => 'text',
				'length' => 20,
				'notnull' => FALSE
			),
			'log_level' => array(
				'type' => 'integer',
				'length' => 4,
				'notnull' => FALSE,
				'default' => null
			)
		));
		
		$ilDB->addPrimaryKey('log_components',array('component_id'));
	}
?>
<#63>
<?php
$ilCtrlStructureReader->getStructure();
?>