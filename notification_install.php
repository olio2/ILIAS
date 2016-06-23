<?php
global $ilDB;

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_CRON);

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_COOKIE["ilClientId"] = 'default';
$_POST['username']     = 'root';
$_POST['password']     = 'homer';

include_once './include/inc.header.php';

/***************************** Types ****************************/
exit();

require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
ilNotificationSetupHelper::registerType('buddysystem_request', 'buddysystem_request', 'buddysystem_request_desc', 'buddysystem');
ilNotificationSetupHelper::registerType('aw_now_online', 'aw_now_online', 'aw_now_online', 'aw');

// Buddy System
$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'text', 'text'),
	array(-1,  'buddysystem_request', 'mail')
);
$num = $ilDB->numRows($res);
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

$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'text', 'text'),
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

// Awareness tool
$res = $ilDB->queryF(
	'SELECT * FROM notification_usercfg WHERE usr_id = %s AND module = %s AND channel = %s',
	array('integer', 'text', 'text'),
	array(-1,  'aw_now_online', 'osd')
);
if(!$ilDB->numRows($res))
{
	$ilDB->insert(
		'notification_usercfg',
		array(
			'usr_id'  => array('integer', -1),
			'module'  => array('text', 'aw_now_online'),
			'channel' => array('text', 'osd')
		)
	);
}


echo "Done";
exit();

/***************************** Admin Node ****************************/
$typ_id = $ilDB->nextId('object_data');

$query = "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
	"VALUES (".$typ_id.", 'typ', 'nota', 'Notification Service Administration Object', -1, now(), now())";

$ilDB->query($query);

// REGISTER RBAC OPERATIONS FOR OBJECT TYPE
// 1: edit_permissions, 2: visible, 3: read, 4:write
$query = "INSERT INTO rbac_ta (typ_id, ops_id) VALUES"
	."  (".$ilDB->quote($typ_id).",'1')"
	.", (".$ilDB->quote($typ_id).",'2')"
	.", (".$ilDB->quote($typ_id).",'3')"
	.", (".$ilDB->quote($typ_id).",'4')"
;

$ilDB->query($query);

// ADD NODE IN SYSTEM SETTINGS FOLDER
// create object data entry

$obj_id = $ilDB->nextId('object_data');

$query = "INSERT INTO object_data (obj_id, type, title, description, owner, create_date, last_update) ".
	"VALUES (".$obj_id.", 'nota', '__Notification Admin', 'Notification Service settings', -1, now(), now())";
$ilDB->query($query);

$ref_id = $ilDB->nextId('object_reference');

// create object reference entry
$query = "INSERT INTO object_reference (ref_id, obj_id) VALUES(".$ref_id.", ".$ilDB->quote($obj_id).")";
$res = $ilDB->query($query);

// put in tree
$tree = new ilTree(ROOT_FOLDER_ID);
$tree->insertNode($ref_id,SYSTEM_FOLDER_ID);

require_once 'Services/Notifications/classes/class.ilNotificationSetupHelper.php';
ilNotificationSetupHelper::setupTables();

echo "Done";
exit();