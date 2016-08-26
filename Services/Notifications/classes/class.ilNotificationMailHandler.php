<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Notifications/classes/class.ilNotificationHandler.php';

/**
 * Notification handler for sending notifications the to recipients email address
 */
class ilNotificationMailHandler extends ilNotificationHandler
{
	/**
	 * {@inheritdoc}
	 */
	public function notify(ilNotificationObject $notification)
	{
		require_once 'Services/Mail/classes/class.ilMail.php';

		$sender_id = (isset($notification->handlerParams['mail']['sender']) ? $notification->handlerParams['mail']['sender'] : ANONYMOUS_USER_ID);

		$mail = new ilMail($sender_id);
		$mail->appendInstallationSignature(true);
		$mail->sendMail(
			$notification->user->getLogin(),
			'',
			'',
			$notification->title,
			$notification->longDescription,
			array(),
			array('normal')
		);
	}
}

