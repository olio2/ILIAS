<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Notifications/classes/class.ilNotificationHandler.php';

/**
 * Basic notification handler that dumps basic notification information
 * to stdout
 */
class ilNotificationEchoHandler extends ilNotificationHandler
{
	public function notify(ilNotificationObject $notification)
	{
		echo "Notification for Recipient {$notification->user->getId()}: {$notification->title} <br />";
	}
}