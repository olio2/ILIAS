<?php
global $ilUser;

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SESSION_REMINDER);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();
die("Remove this to send example notifications");

/***************************** Types ****************************/

$usr_id  = $ilUser->getId();
$rcp_lng = $lng;
$user    = $ilUser;
$sender  = $user;

$noti = 'aw'; // aw/bs


if($noti == 'bs')
{
	require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';
	$notification = new ilNotificationConfig('buddysystem_request');
	$notification->setTitleVar('buddy_notification_contact_request', array(), 'buddysystem');

	$bodyParams = array(
		'SALUTATION'      => ilMail::getSalutation($usr_id, $rcp_lng),
		'BR'              => nl2br("\n"),
		'APPROVE_REQUEST' => '<a href="' . ilLink::_getStaticLink($sender->getId(), 'usr', true, '_contact_approved') . '">' . $rcp_lng->txt('buddy_notification_contact_request_link_osd') . '</a>',
		'IGNORE_REQUEST'  => '<a href="' . ilLink::_getStaticLink($sender->getId(), 'usr', true, '_contact_ignored') . '">' . $rcp_lng->txt('buddy_notification_contact_request_ignore_osd') . '</a>',
		'REQUESTING_USER' => ilUserUtil::getNamePresentation($sender->getId())
	);
	$notification->setShortDescriptionVar('buddy_notification_contact_request_short', $bodyParams, 'buddysystem');

	$bodyParams = array(
		'SALUTATION'          => ilMail::getSalutation($usr_id, $rcp_lng),
		'BR'                  => "\n",
		'APPROVE_REQUEST'     => ilLink::_getStaticLink($sender->getId(), 'usr', true, '_contac
	 t_approve
	d'),
		'APPROVE_REQUEST_TXT' => $rcp_lng->txt('buddy_notification_contact_request_link'),
		'IGNORE_REQUEST'      => ilLink::_getStaticLink($sender->getId(), 'usr', true, '_contac
	 t_ignored
	'),
		'IGNORE_REQUEST_TXT'  => $rcp_lng->txt('buddy_notification_contact_request_ignore'),
		'REQUESTING_USER'     => ilUserUtil::getNamePresentation($sender->getId())
	);
	$notification->setLongDescriptionVar('buddy_notification_contact_request_long', $bodyParams, 'buddysystem');

	$notification->setAutoDisable(false);
	$notification->setValidForSeconds(30 * 60);
	$notification->setVisibleForSeconds(30);
	$notification->setIconPath('templates/default/images/icon_usr.svg');
	$notification->setHandlerParam('mail.sender', ANONYMOUS_USER_ID);
	$notification->notifyByUsers(array($user->getId()));
	echo "Delivered";
}
else if($noti == 'aw')
{
	$lng->loadLanguageModule('mail');

	include_once("./Services/Object/classes/class.ilObjectFactory.php");
	//$recipient = ilObjectFactory::getInstanceByObjId($this->user_id);
	$bodyParams = array(
		'online_user_names' => implode("<br />", array("root", "mjansen", "bheyser"))
	);

	require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';
	$notification = new ilNotificationConfig('aw_now_online');
	$notification->setTitleVar('awareness_now_online', $bodyParams, 'awrn');
	$notification->setShortDescriptionVar('awareness_now_online_users', $bodyParams, 'awrn');
	$notification->setLongDescriptionVar('', $bodyParams, '');
	$notification->setAutoDisable(false);

	$notification->setIconPath('templates/default/images/icon_usr.svg');
	$notification->setValidForSeconds(ilNotificationConfig::TTL_SHORT);
	$notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);

	$notification->notifyByUsers(array($usr_id));
	echo "Delivered";
}