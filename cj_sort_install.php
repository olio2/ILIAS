<?php
global $ilDB;

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SESSION_REMINDER);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

if(!$ilDB->tableColumnExists('cron_job', 'position'))
{
	$ilDB->addTableColumn(
		'cron_job', 'position',
		array(
			'type'    => 'integer',
			'length'  => 4,
			'notnull' => true,
			'default' => 0
		)
	);
}

echo "Done";
exit();