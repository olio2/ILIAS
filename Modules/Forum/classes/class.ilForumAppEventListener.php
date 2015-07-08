<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Forum listener. Listens to events of other components.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup ModulesForum
*/
class ilForumAppEventListener
{
	protected static $ref_ids = array();
	
	/**
	* Handle an event in a listener.
	*
	* @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	* @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	* @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	*/
	static function handleEvent($a_component, $a_event, $a_parameter)
	{
		switch($a_component)
		{
			case "Modules/Forum": 
				switch ($a_event)
				{
					case "createdPost":
						include_once './Modules/Forum/classes/class.ilForumMailNotification.php';
						$provider = $a_parameter['provider'];
						$notification_type = ilForumMailNotification::TYPE_POST_NEW;
						self::performNotification($provider, $notification_type);
						break;
					
					case "updatedPost":
						include_once './Modules/Forum/classes/class.ilForumMailNotification.php';
						$provider = $a_parameter['provider'];
						$notification_type = ilForumMailNotification::TYPE_POST_UPDATED;
						self::performNotification($provider, $notification_type);
						break;

					case "censoredPost":
						include_once './Modules/Forum/classes/class.ilForumMailNotification.php';
						$provider = $a_parameter['provider'];
						$notification_type = ilForumMailNotification::TYPE_POST_CENSORED;
						self::performNotification($provider, $notification_type);
						break;

					case "deletedPost":
						include_once './Modules/Forum/classes/class.ilForumMailNotification.php';
						$provider = $a_parameter['provider'];
						$notification_type = ilForumMailNotification::TYPE_POST_DELETED;
						self::performNotification($provider, $notification_type);
						break;
					
					case "activatePost":
						include_once './Modules/Forum/classes/class.ilForumMailNotification.php';
						$provider = $a_parameter['provider'];
						
						// get moderators to notify about needed activation
						$rcps = $provider->getPostActivationRecipients();

						if(count($rcps) > 0)
						{
							$mailNotification = new ilForumMailNotification($provider);
							$mailNotification->setType(ilForumMailNotification::TYPE_POST_ACTIVATION);
							$mailNotification->setRecipients($rcps);
							$mailNotification->send();
						}
						break;
					
					case "answeredPost":
						include_once './Modules/Forum/classes/class.ilForumMailNotification.php';
						$provider = $a_parameter['provider'];
						
						// get recipient who wants to get deriect notifications   
						$rcps = $provider->getPostAnsweredRecipients();
						if(count($rcps) > 0)
						{
							$mailNotification = new ilForumMailNotification($provider);
							$mailNotification->setType(ilForumMailNotification::TYPE_POST_ANSWERED);
							$mailNotification->setRecipients($rcps);
							$mailNotification->send();
						}
						break;
				}
				break;
			case "Services/News":
				switch ($a_event)
				{
					case "readNews":
						// here we could set postings to read, if news is
						// read (has to be implemented)
						break;
				}
				break;

			case "Services/Tree":
				switch ($a_event)
				{
					case "moveTree":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';
						ilForumNotification::_clearForcedForumNotifications($a_parameter);
						break;
				}
				break;
			
			case "Modules/Course":
				switch($a_event)
				{
					case "addParticipant":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';
						
						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsInsert($ref_id, $a_parameter['usr_id']);
							break;
						}
						
						break;
					case 'deleteParticipant':
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsDelete($ref_id, $a_parameter['usr_id']);
							break;
						}
						break;
				}
				break;
			case "Modules/Group":
				switch($a_event)
				{
					case "addParticipant":
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsInsert($ref_id, $a_parameter['usr_id']);
							break;
						}

						break;
					case 'deleteParticipant':
						include_once './Modules/Forum/classes/class.ilForumNotification.php';

						$ref_ids = self::getCachedReferences($a_parameter['obj_id']);

						foreach($ref_ids as $ref_id)
						{
							ilForumNotification::checkForumsExistsDelete($ref_id, $a_parameter['usr_id']);
							break;
						}
						break;
				}
				break;
		}
	}

	/**
	 * @param int $obj_id
	 */
	private function getCachedReferences($obj_id)
	{
		if(!array_key_exists($obj_id, self::$ref_ids))
		{
			self::$ref_ids[$obj_id] = ilObject::_getAllReferences($obj_id);	
		}
		return self::$ref_ids[$obj_id];
	}

	/**
	 * @param ilObjForumNotificationDataProvider $provider
	 * @param 									 $notification_type
	 */
	private static function performNotification(ilObjForumNotificationDataProvider $provider, $notification_type)
	{
		include_once './Modules/Forum/classes/class.ilForumMailNotification.php';
		
		// get recipients who wants to get forum notifications   
		$rcps = $provider->getForumNotificationRecipients();
		if(count($rcps) > 0)
		{
			$mailNotification = new ilForumMailNotification($provider);
			$mailNotification->setType($notification_type);
			$mailNotification->setRecipients($rcps);
			$mailNotification->send();
		}

		// get recipients who wants to get thread notifications
		$rcps = $provider->getThreadNotificationRecipients();
		if(count($rcps) > 0)
		{
			$mailNotification = new ilForumMailNotification($provider);
			$mailNotification->setType($notification_type);
			$mailNotification->setRecipients($rcps);
			$mailNotification->send();
		}
	}
}
?>