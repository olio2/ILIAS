<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";
include_once "./Modules/Forum/classes/class.ilForumMailNotification.php";

/**
 * Forum notifications
 *
 * @author Michael Jansen <mjansen@databay.de>
 * @author Nadia Ahmnad <nahmad@databay.de>
 */
class ilForumCronNotification extends ilCronJob
{
	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var array  ilForumCronNotificationDataProvider
	 */
	public static $providerObject = array();
	
	/**
	 *
	 */
	public function __construct()
	{
		$this->settings = new ilSetting('frma');
	}

	public function getId()
	{
		return "frm_notification";
	}
	
	public function getTitle()
	{
		global $lng;
			
		return $lng->txt("cron_forum_notification");
	}
	
	public function getDescription()
	{
		global $lng;
			
		return $lng->txt("cron_forum_notification_crob_desc");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_IN_HOURS;
	}
	
	public function getDefaultScheduleValue()
	{
		return 1;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function hasCustomSettings() 
	{
		return true;
	}

	/**
	 * @return ilCronJobResult
	 */
	public function run()
	{
		global $ilDB, $ilLog, $ilSetting, $lng;

		$status = ilCronJobResult::STATUS_NO_ACTION;

		$lng->loadLanguageModule('forum');

		if(!($last_run_datetime = $ilSetting->get('cron_forum_notification_last_date')))
		{
			$last_run_datetime = null;
		}

		$numRows = 0;

		if($last_run_datetime != null &&
			checkDate(date('m', strtotime($last_run_datetime)), date('d', strtotime($last_run_datetime)), date('Y', strtotime($last_run_datetime))))
		{
			$threshold = max(strtotime($last_run_datetime), strtotime('-' . (int)$this->settings->get('max_notification_age', 30) . ' days', time()));
		}
		else
		{
			$threshold = strtotime('-' . (int)$this->settings->get('max_notification_age', 30) . ' days', time());
		}

		$date_condition = ' frm_posts.pos_date >= %s AND ';
		$types	= array('timestamp');
		$values = array(date('Y-m-d H:i:s', $threshold));

		$cj_start_date = date('Y-m-d H:i:s');
		
		/*** new posts ***/
		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id, 
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	'.$date_condition.' frm_posts.pos_thr_fk = frm_threads.thr_pk
			AND 	((frm_threads.thr_top_fk = frm_data.top_pk AND 	frm_data.top_frm_fk = frm_notification.frm_id)
					OR (frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk) )
			AND 	frm_posts.pos_display_user_id <> frm_notification.user_id
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);

		$frm_numRows = $ilDB->numRows($res);
		if($frm_numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_NEW);
			$this->resetProviderCache();
		}

		/*** updated posts ***/
		$updated_condition = ' frm_posts.pos_update > frm_posts.pos_date AND frm_posts.pos_update >= %s AND ';
		$types	= array('timestamp');
		$values	= array(date('Y-m-d H:i:s', $threshold));

		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id, 
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	'.$updated_condition.' frm_posts.pos_thr_fk = frm_threads.thr_pk
			AND 	((frm_threads.thr_top_fk = frm_data.top_pk AND 	frm_data.top_frm_fk = frm_notification.frm_id)
					OR (frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk) )
			AND 	frm_posts.pos_display_user_id <> frm_notification.user_id
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$frm_numRows = $ilDB->numRows($res);
		if($frm_numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_UPDATED);
			$this->resetProviderCache();
		}

		/*** censored posts ***/
		$censored_condition = ' frm_posts.pos_cens = %s AND frm_posts.pos_cens_date >= %s AND ';
		$types	= array('integer','timestamp');
		$values	= array(1, date('Y-m-d H:i:s', $threshold));

		$res = $ilDB->queryf('
			SELECT 	frm_threads.thr_subject thr_subject, 
					frm_data.top_name top_name, 
					frm_data.top_frm_fk obj_id, 
					frm_notification.user_id user_id, 
					frm_posts.* 
			FROM 	frm_notification, frm_posts, frm_threads, frm_data 
			WHERE	'.$censored_condition.' frm_posts.pos_thr_fk = frm_threads.thr_pk
			AND 	((frm_threads.thr_top_fk = frm_data.top_pk AND 	frm_data.top_frm_fk = frm_notification.frm_id)
					OR (frm_threads.thr_pk = frm_notification.thread_id 
			AND 	frm_data.top_pk = frm_threads.thr_top_fk) )
			AND 	frm_posts.pos_display_user_id <> frm_notification.user_id
			ORDER BY frm_posts.pos_date ASC',
			$types,
			$values
		);
		
		$frm_numRows = $ilDB->numRows($res);
		if($frm_numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_CENSORED);
			$this->resetProviderCache();
		}

		/*** deleted posts ***/
		$res = $ilDB->query('
			SELECT 	frm_posts_deleted.thread_title thr_subject, 
					frm_posts_deleted.forum_title  top_name, 
					frm_posts_deleted.obj_id obj_id, 
					frm_notification.user_id user_id, 
					frm_posts_deleted.pos_display_user_id,
					frm_posts_deleted.pos_usr_alias
					
			FROM 	frm_notification, frm_posts_deleted
			
			WHERE 	( frm_posts_deleted.obj_id = frm_notification.frm_id
					OR frm_posts_deleted.thread_id = frm_notification.thread_id) 
			AND 	frm_posts_deleted.pos_display_user_id <> frm_notification.user_id
			ORDER BY frm_posts_deleted.post_date ASC');
	
		$frm_numRows = $ilDB->numRows($res);
		if($frm_numRows > 0)
		{
			$this->sendCronForumNotification($res, ilForumMailNotification::TYPE_POST_DELETED);
			$ilDB->manipulateF('DELETE FROM frm_posts_deleted WHERE deleted_id > %s', array('integer'), array(0));
			$ilLog->write(__METHOD__.':DELETED ENTRIES: frm_posts_deleted');
			$this->resetProviderCache();
		}

		$ilSetting->set('cron_forum_notification_last_date', $cj_start_date);

		$mess = 'Send '.$numRows.' messages.';
		$ilLog->write(__METHOD__.': '.$mess);

		$result = new ilCronJobResult();
		if($numRows)
		{
			$status = ilCronJobResult::STATUS_OK;
			$result->setMessage($mess);
		};
		$result->setStatus($status);
		return $result;
	}

	/**
	 * @param $res
	 * @param $notification_type
	 */
	public function sendCronForumNotification($res, $notification_type)
	{
		global $ilDB, $ilLog;
		
		include_once './Modules/Forum/classes/class.ilForumCronNotificationDataProvider.php';
		include_once './Modules/Forum/classes/class.ilForumMailNotification.php';

		while($row = $ilDB->fetchAssoc($res))
		{
			if($this->existsProviderObject($row['pos_pk']))
			{
				self::$providerObject[$row['pos_pk']]->addRecipient($row['user_id']);
			}	
			else
			{
				$this->addProviderObject($row);
			}	
		}

		foreach(self::$providerObject as $provider)
		{
			$mailNotification = new ilForumMailNotification($provider);
			$mailNotification->setIsCronjob(true);
			$mailNotification->setType($notification_type);
			$mailNotification->setRecipients($provider->getCronRecipients());

			$mailNotification->send();
			
			$ilLog->write(__METHOD__.':SUCCESSFULLY SEND: NotificationType: '.$notification_type.' -> Recipients: '. implode(', ',$provider->getCronRecipients()));
		}
	}

	/**
	 * @param $post_id
	 * @return bool
	 */
	public function existsProviderObject($post_id)
	{
		if(isset(self::$providerObject[$post_id]))
		{
			return true;
		}	
		return false;
	}

	/**
	 * @param $row
	 */
	private function addProviderObject($row)
	{
		$tmp_provider = new ilForumCronNotificationDataProvider($row);

		self::$providerObject[$row['pos_pk']] = $tmp_provider;
		self::$providerObject[$row['pos_pk']]->addRecipient($row['user_id']);
	}

	/**
	 * 
	 */
	private function resetProviderCache()
	{
		self::$providerObject = array();
	}
	
	/**
	 * @param int   $a_form_id
	 * @param array $a_fields
	 * @param bool  $a_is_active
	 */
	public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		switch($a_form_id)
		{
			case ilAdministrationSettingsFormHandler::FORM_FORUM:
				$a_fields['cron_forum_notification'] = $a_is_active ?
					$lng->txt('enabled') :
					$lng->txt('disabled');
				break;
		}
	}

	/**
	 * @param bool $a_currently_active
	 */
	public function activationWasToggled($a_currently_active)
	{		
		global $ilSetting;
		
		// propagate cron-job setting to object setting
		if((bool)$a_currently_active)
		{
			$ilSetting->set('forum_notification', 2);
		}
		else
		{
			$ilSetting->set('forum_notification', 1);
		}
	}

	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	public function addCustomSettingsToForm(ilPropertyFormGUI $a_form)
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$lng->loadLanguageModule('forum');

		$max_notification_age = new ilNumberInputGUI($lng->txt('frm_max_notification_age'), 'max_notification_age');
		$max_notification_age->setSize(5);
		$max_notification_age->setSuffix($lng->txt('frm_max_notification_age_unit'));
		$max_notification_age->setRequired(true);
		$max_notification_age->allowDecimals(false);
		$max_notification_age->setMinValue(1);
		$max_notification_age->setInfo($lng->txt('frm_max_notification_age_info'));
		$max_notification_age->setValue($this->settings->get('max_notification_age', 30));

		$a_form->addItem($max_notification_age);
	}

	/**
	 * @param ilPropertyFormGUI $a_form
	 */
	public function saveCustomSettings(ilPropertyFormGUI $a_form)
	{
		$this->settings->set('max_notification_age', $a_form->getInput('max_notification_age'));
		return true;
	}
}