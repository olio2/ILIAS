<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Course/group notifications
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilMembershipCronNotifications extends ilCronJob
{
	public function getId()
	{
		return "mem_notification";
	}
	
	public function getTitle()
	{
		global $lng;
		
		return $lng->txt("enable_course_group_notifications");
	}
	
	public function getDescription()
	{
		global $lng;
		
		return $lng->txt("enable_course_group_notifications_desc");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue()
	{
		return;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return false;
	}

	public function run()
	{				
		global $lng, $ilDB;
		
		$status = ilCronJobResult::STATUS_NO_ACTION;
		$status_details = null;
	
		$setting = new ilSetting("cron");		
		$last_run = $setting->get(get_class($this));
		
		// no last run?
		if(!$last_run)
		{
			$last_run = date("Y-m-d H:i:s", strtotime("yesterday"));
			
			$status_details = "No previous run found - starting from yesterday.";
		}
		// migration: used to be date-only value 
		else if(strlen($last_run) == 10)
		{
			$last_run .= " 00:00:00";
			
			$status_details = "Switched from daily runs to open schedule.";			
		}
		
		include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
		$objects = ilMembershipNotifications::getActiveUsersforAllObjects();
		if(sizeof($objects))
		{				
			// gather news for each user over all objects
			
			$user_news_aggr = array();
						
			include_once "Services/News/classes/class.ilNewsItem.php";
			foreach($objects as $ref_id => $user_ids)
			{
				// gather news per object
				$news_item = new ilNewsItem();
				if($news_item->checkNewsExistsForGroupCourse($ref_id, $last_run))
				{
					foreach($user_ids as $user_id)
					{
						// gather news for user
						$user_news = $news_item->getNewsForRefId($ref_id,
							false, false, $last_run, false, false, false, false,
							$user_id);
						if($user_news)
						{
							$user_news_aggr[$user_id][$ref_id] = $user_news;								
						}
					}
				}				
			}
			unset($objects);


			// send mails (1 max for each user)
			
			$old_lng = $lng;

			if(sizeof($user_news_aggr))
			{
				foreach($user_news_aggr as $user_id => $user_news)
				{
					$this->sendMail($user_id, $user_news);
				}
			
				// mails were sent - set cron job status accordingly
				$status = ilCronJobResult::STATUS_OK;							
			}

			$lng = $old_lng;
		}

		// save last run
		$setting->set(get_class($this), date("Y-m-d H:i:s")); 

		$result = new ilCronJobResult();
		$result->setStatus($status);	
		
		if($status_details)
		{
			$result->setMessage($status_details);
		}
		
		return $result;
	}

	/**
	 * Send news mail for 1 user and n objects
	 *
	 * @param int $a_user_id
	 * @param array $a_objects
	 */
	protected function sendMail($a_user_id, array $a_objects)
	{
		global $lng, $ilUser, $ilClientIniFile;
		
		include_once "./Services/Notification/classes/class.ilSystemNotification.php";
		$ntf = new ilSystemNotification();		
		$ntf->setLangModules(array("crs", "news"));
		// no single object anymore
		// $ntf->setRefId($a_ref_id);	
		// $ntf->setGotoLangId('url');
		// $ntf->setSubjectLangId('crs_subject_course_group_notification');
		
		// user specific language
		$lng = $ntf->getUserLanguage($a_user_id);
		
		$txt = "";		
		$object_counter = 0;
		foreach($a_objects as $parent_ref_id => $news)
		{			
			$object_counter++;
			
			if($object_counter > 1)
			{
				$txt .= "\n\n".$ntf->getBlockBorder();				
			}
			
			$parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
			$parent_obj_type = ilObject::_lookupType($parent_obj_id);

			$parent_obj_title = $lng->txt($parent_obj_type)." \"".ilObject::_lookupTitle($parent_obj_id)."\"";	
			// no single object anymore 
			// $ntf->setIntroductionDirect(sprintf($lng->txt("crs_intro_course_group_notification_for"), $parent_obj_title));
			$txt .= "#".$object_counter." ".sprintf($lng->txt("crs_intro_course_group_notification_for"), $parent_obj_title)."\n";
			
			// no single object anymore - see below
			// $subject = sprintf($lng->txt("crs_subject_course_group_notification"), $obj_title);

			// news summary					
			$news_counter = 1;			
			foreach($news as $item)
			{
				$title = ilNewsItem::determineNewsTitle($item["context_obj_type"],
					$item["title"], $item["content_is_lang_var"], $item["agg_ref_id"], 
					$item["aggregation"]);
				$content = ilNewsItem::determineNewsContent($item["context_obj_type"], 
					$item["content"], $item["content_text_is_lang_var"]);

				$item_obj_id = ilObject::_lookupObjId($item["ref_id"]);
				$item_obj_title = ilObject::_lookupTitle($item_obj_id);

				// path
				include_once './Services/Locator/classes/class.ilLocatorGUI.php';			
				$cont_loc = new ilLocatorGUI();
				$cont_loc->addContextItems($item["ref_id"], true);
				$cont_loc->setTextOnly(true);

				// #9954/#10044
				require_once "HTML/Template/ITX.php";
				require_once "./Services/UICore/classes/class.ilTemplateHTMLITX.php";
				require_once "./Services/UICore/classes/class.ilTemplate.php";
				$loc = "[".$cont_loc->getHTML()."]";
				
				$txt .= $ntf->getBlockBorder();				
				$txt .= '#'.$object_counter.".".$news_counter." - ".$loc." ".$item_obj_title."\n\n";
				$txt .= $title;
				if($content)
				{
					$txt .= "\n".$content;
				}			
				$txt .= "\n\n";

				$news_counter++;
			}			
		}
		
		$ntf->addAdditionalInfo("news", $txt, true);
		
		// :TODO: does it make sense to add client to subject?
		$client = $ilClientIniFile->readVariable('client', 'name');
		$subject = sprintf($lng->txt("crs_subject_course_group_notification"), $client);
			
		// #10044
		$mail = new ilMail($ilUser->getId());
		$mail->enableSOAP(false); // #10410
		$mail->sendMail(ilObjUser::_lookupLogin($a_user_id), 
			null, 
			null,
			$subject, 
			$ntf->composeAndGetMessage($a_user_id, null, "read", true), 
			null, 
			array("system"));
	}
	
	public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
	{				
		global $lng;
		
		switch($a_form_id)
		{			
			case ilAdministrationSettingsFormHandler::FORM_COURSE:				
			case ilAdministrationSettingsFormHandler::FORM_GROUP:								
				$a_fields["enable_course_group_notifications"] = $a_is_active ? 
					$lng->txt("enabled") :
					$lng->txt("disabled");
				break;
		}
	}
	
	public function activationWasToggled($a_currently_active)
	{
		global $ilSetting;
				
		// propagate cron-job setting to object setting
		$ilSetting->set("crsgrp_ntf", (bool)$a_currently_active);		
	}
}

?>