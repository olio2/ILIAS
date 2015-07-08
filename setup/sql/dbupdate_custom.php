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
	if(!$ilDB->tableColumnExists('frm_posts','pos_cens_date'))
	{
		$ilDB->addTableColumn('frm_posts', 'pos_cens_date', array(
				'type' => 'timestamp',
				'notnull' => false)
		);
	}
?>
<#16>
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
<#17>
<?php
	$ilCtrlStructureReader->getStructure();
?>
