<#1>
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
<#2>
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