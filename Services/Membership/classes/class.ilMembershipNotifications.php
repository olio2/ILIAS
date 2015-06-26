<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
 * Membership notification settings
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesMembership
 */
class ilMembershipNotifications
{
	protected $ref_id; // [int]	
	protected $mode; // [int]
	protected $custom; // [array]
	protected $participants; // [ilParticipants]
	
	const VALUE_OFF = 0;
	const VALUE_ON = 1;
	const VALUE_BLOCKED = 2;
	
	const MODE_SELF = 1;
	const MODE_ALL = 2;
	const MODE_ALL_BLOCKED = 3;
	const MODE_CUSTOM = 4;
	
	/**
	 * Constructor
	 * 
	 * @param int $a_ref_id
	 * @return self
	 */
	public function __construct($a_ref_id)
	{
		$this->ref_id = (int)$a_ref_id;						
		$this->custom = array();
		$this->setMode(self::MODE_SELF);		
		
		if($this->ref_id)
		{
			$this->read();
		}	
	}
		
	/**
	 * Is feature active?
	 * 
	 * @return bool
	 */
	public static function isActive()
	{
		global $ilSetting;
						
		// :TODO: what about active news service?
		return (bool)$ilSetting->get("crsgrp_ntf", false);		
	}
	
	/**
	 * Read from DB
	 */
	protected function read()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT nmode mode".
			" FROM member_noti".
			" WHERE ref_id = ".$ilDB->quote($this->ref_id, "integer"));
		if($ilDB->numRows($set))
		{
			$row = $ilDB->fetchAssoc($set);
			$this->setMode($row["mode"]);
				
			if($row["mode"] == self::MODE_CUSTOM)
			{
				$set = $ilDB->query("SELECT *".
					" FROM member_noti_user".
					" WHERE ref_id = ".$ilDB->quote($this->ref_id, "integer"));
				while($row = $ilDB->fetchAssoc($set))
				{
					$this->custom[$row["user_id"]] = $row["status"];
				}
			}
		}
	}	
	
	
	//
	// MODE
	//
	
	/**
	 * Get mode
	 * 
	 * @return int
	 */
	public function getMode()
	{
		return $this->mode;
	}
	
	/**
	 * Set mode	 
	 * 
	 * @param int $a_value
	 */
	protected function setMode($a_value)
	{		
		if($this->isValidMode($a_value))
		{
			$this->mode = $a_value;
		}
	}
	
	/**
	 * Is given mode valid?
	 * 
	 * @param int $a_value
	 * @return bool
	 */
	protected function isValidMode($a_value)
	{
		$valid = array(
			self::MODE_SELF
			,self::MODE_ALL
			,self::MODE_ALL_BLOCKED
			// ,self::MODE_CUSTOM currently used in forum
		);
		return in_array($a_value, $valid);
	}
	
	/**
	 * Switch mode for object
	 * 
	 * @param int $a_new_mode
	 * @return bool
	 */
	public function switchMode($a_new_mode)
	{
		global $ilDB;
		
		if(!$this->ref_id)
		{
			return;
		}
				
		if($this->mode &&
			$this->mode != $a_new_mode &&
			$this->isValidMode($a_new_mode))
		{		
			$ilDB->manipulate("DELETE FROM member_noti".
				" WHERE ref_id = ".$ilDB->quote($this->ref_id, "integer"));				

			// no custom data
			if($a_new_mode != self::MODE_CUSTOM)
			{
				$ilDB->manipulate("DELETE FROM member_noti_user".
					" WHERE ref_id = ".$ilDB->quote($this->ref_id, "integer"));
			}
			
			// mode self is default
			if($a_new_mode != self::MODE_SELF)
			{
				$ilDB->insert("member_noti", array(
					"ref_id" => array("integer", $this->ref_id),
					"nmode" => array("integer", $a_new_mode)
				));
			}	
			
			// remove all user settings (all active is preset, optional opt out)
			if($a_new_mode == self::MODE_ALL)
			{
				$ilDB->manipulate("DELETE FROM usr_pref".
					" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_".$this->ref_id));
			}
		}
		
		$this->setMode($a_new_mode);
	}	
	
	
	//
	// ACTIVE USERS
	//
	
	/**
	 * Init participants for current object
	 *
	 * @return ilParticipants
	 */
	protected function getParticipants()
	{
		global $tree;
		
		if($this->participants === null)
		{		
			$this->participants = false;
			
			$grp_ref_id = $tree->checkForParentType($this->ref_id, "grp");
			if($grp_ref_id)
			{			
				include_once "Modules/Group/classes/class.ilGroupParticipants.php";
				$this->participants = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjId($grp_ref_id));			
			}

			if(!$this->participants)
			{
				$crs_ref_id = $tree->checkForParentType($this->ref_id, "crs");
				if($crs_ref_id)
				{			
					include_once "Modules/Course/classes/class.ilCourseParticipants.php";
					$this->participants =  ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjId($crs_ref_id));
				}
			}			
		}
		
		return $this->participants;
	}	
	
	/**
	 * Get active notifications for current object
	 *
	 * @return array
	 */
	public function getActiveUsers()
	{		
		global $ilDB;
		
		$users = array();
		
		$all = $this->getParticipants()->getParticipants();
		
		switch($this->getMode())
		{
			// users decide themselves
			case self::MODE_SELF:						
				$set = $ilDB->query("SELECT usr_id".
					" FROM usr_pref".
					" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_".$this->ref_id).
					" AND value = ".$ilDB->quote(self::VALUE_ON, "text"));
				while($row = $ilDB->fetchAssoc($set))
				{					
					$users[] = $row["usr_id"];			
				}
				break;
			
			// all members, mind opt-out
			case self::MODE_ALL:
				// users who did opt-out
				$inactive = array();
				$set = $ilDB->query("SELECT usr_id".
					" FROM usr_pref".
					" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_".$this->ref_id).
					" AND value = ".$ilDB->quote(self::VALUE_OFF, "text"));
				while($row = $ilDB->fetchAssoc($set))
				{					
					$inactive[] = $row["usr_id"];			
				}								
				$users = array_diff($all, $inactive);
				break;
			
			// all members, no opt-out
			case self::MODE_ALL_BLOCKED:				
				$users = $all;			
				break;
			
			// custom settings
			case self::MODE_CUSTOM:	
				foreach($this->custom as $user_id => $status)
				{
					if($status != self::VALUE_OFF)
					{
						$users[] = $user_id;
					}
				}				
				break;
		}
		
		// only valid participants
		return  array_intersect($all, $users);
	}
	
	
	//
	// USER STATUS
	//
	
	/**
	 * Activate notification for user
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function activateUser($a_user_id = null)
	{
		return $this->toggleUser(true, $a_user_id);	
	}
	
	/**
	 * Deactivate notification for user
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function deactivateUser($a_user_id = null)
	{
		return $this->toggleUser(false, $a_user_id);		
	}
	
	/**
	 * Init user instance
	 *
	 * @param int $a_user_id
	 * @return ilUser
	 */
	protected function getUser($a_user_id = null)
	{
		global $ilUser;
		
		if($a_user_id === null ||
			$a_user_id == $ilUser->getId())
		{
			$user = $ilUser;
		}
		else
		{			
			$user = new ilUser($a_user_id);		
		}
		
		if($user->getId() &&
			$user->getId() != ANONYMOUS_USER_ID)
		{		
			return $user;
		}
	}
	
	/**
	 * Toggle user notification status
	 * 
	 * @param bool $a_status
	 * @param int $a_user_id
	 * @return boolean
	 */
	protected function toggleUser($a_status, $a_user_id = null)
	{
		global $ilDB;
		
		if(!self::isActive())
		{
			return;
		}
	
		switch($this->getMode())
		{
			case self::MODE_ALL:				
			case self::MODE_SELF:				
				// current user!					
				$user = $this->getUser();
				if($user)
				{
					// blocked value not supported in user pref!
					$user->setPref("grpcrs_ntf_".$this->ref_id, (int)(bool)$a_status);	
					$user->writePrefs();
					return true;
				}
				break;
			
			case self::MODE_CUSTOM:
				$user = $this->getUser($a_user_id);
				if($user)
				{
					$user_id = $user->getId();
					
					// did status change at all?
					if(!array_key_exists($user_id, $this->custom) ||
						$this->custom[$user_id != $a_status])
					{
						$this->custom[$user_id] = $a_status;

						$ilDB->replace("member_noti_user",
							array(
								"ref_id" => array("integer", $this->ref_id),
								"user_id" => array("integer", $user_id),
							),
							array(
								"status" => array("integer", $a_status)
							)
						);		
					}
					return true;		
				}
				break;
				
			case self::MODE_ALL_BLOCKED:
				// no individual settings
				break;			
		}
				
		return false;
	}
	
	
	//
	// CURRENT USER
	//
	
	/**
	 * Get user notification status
	 * 
	 * @return boolean
	 */
	public function isCurrentUserActive()
	{		
		global $ilUser;
		
		return in_array($ilUser->getId(), $this->getActiveUsers());
	}
	
	/**
	 * Can user change notification status?
	 * 
	 * @return boolean
	 */
	public function canCurrentUserEdit()
	{
		global $ilUser;
		
		$user_id = $ilUser->getId();
		if($user_id == ANONYMOUS_USER_ID)
		{
			return false;
		}
		
		switch($this->getMode())
		{
			case self::MODE_SELF:
			case self::MODE_ALL:
				return true;
				
			case self::MODE_ALL_BLOCKED:
				return false;
				
			case self::MODE_CUSTOM:
				return !(array_key_exists($user_id, $this->custom) &&
					$this->custom[$user_id] == self::VALUE_BLOCKED);			
		}
	}
	
	
	//
	// CRON
	//
	
	/**
	 * Get active notifications for all objects
	 * 
	 * @return array
	 */
	public static function getActiveUsersforAllObjects()
	{
		global $ilDB, $tree;
		
		$objects = array();
				
		if(self::isActive())
		{						
			// user-preference data (MODE_SELF) 																		
			$set = $ilDB->query("SELECT DISTINCT(keyword) keyword".
				" FROM usr_pref".
				" WHERE ".$ilDB->like("keyword", "text", "grpcrs_ntf_%").
				" AND value = ".$ilDB->quote("1", "text"));
			while($row = $ilDB->fetchAssoc($set))
			{
				$ref_id = substr($row["keyword"], 11);					
				$objects[] = (int)$ref_id;																
			}			
			
			// all other modes
			$set = $ilDB->query("SELECT ref_id".
				" FROM member_noti");
			while($row = $ilDB->fetchAssoc($set))
			{					
				$objects[] = (int)$row["ref_id"];
			}
			
			// this might be slow but it is to be used in CRON JOB ONLY!
			foreach(array_unique($objects) as $ref_id)
			{
				// :TODO: enough checking?
				if(!$tree->isDeleted($ref_id))
				{
					$noti = new self($ref_id);
					$active = $noti->getActiveUsers();
					if(sizeof($active))
					{
						$objects[$ref_id] = $active;
					}
				}
			}
		}
		
		return $objects;		
	}
	
	
	//
	// (OBJECT SETTINGS) FORM
	//
	
	/**
	 * Add notification settings to form 
	 *
	 * @param ilObject $a_object
	 * @param ilPropertyFormGUI $a_form
	 */
	public static function addToSettingsForm(ilObject $a_object, ilPropertyFormGUI $a_form)
	{
		global $lng;
		
		$ref_id = $a_object->getRefId();
		
		if(self::isActive() &&
			$ref_id)
		{				
			$lng->loadLanguageModule("membership");			
			$noti = new self($ref_id);
			
			$force_noti = new ilRadioGroupInputGUI($lng->txt("mem_force_notification"), "force_noti");
			$a_form->addItem($force_noti);
			
			if($noti->isValidMode(self::MODE_SELF))
			{
				$option = new ilRadioOption($lng->txt("mem_force_notification_mode_self"), self::MODE_SELF);
				$option->setInfo($lng->txt("mem_force_notification_mode_self_info"));
				$force_noti->addOption($option);
			}
			if($noti->isValidMode(self::MODE_ALL))
			{
				$option = new ilRadioOption($lng->txt("mem_force_notification_mode_all"), self::MODE_ALL);
				$option->setInfo($lng->txt("mem_force_notification_mode_all_info"));
				$force_noti->addOption($option);
			}
			if($noti->isValidMode(self::MODE_ALL_BLOCKED))
			{
				$option = new ilRadioOption($lng->txt("mem_force_notification_mode_blocked"), self::MODE_ALL_BLOCKED);
				$option->setInfo($lng->txt("mem_force_notification_mode_blocked_info"));
				$force_noti->addOption($option);	
			}
			if($noti->isValidMode(self::MODE_CUSTOM))
			{
				$option = new ilRadioOption($lng->txt("mem_force_notification_mode_custom"), self::MODE_CUSTOM);
				$option->setInfo($lng->txt("mem_force_notification_mode_custom_info"));
				$force_noti->addOption($option);	
			}
						
			$force_noti->setValue($noti->getMode());
		}
	}
	
	/**
	 * Import notification settings from form 
	 *
	 * @param ilObject $a_object
	 * @param ilPropertyFormGUI $a_form
	 */
	public static function importFromForm(ilObject $a_object, ilPropertyFormGUI $a_form = null)
	{
		$ref_id = $a_object->getRefId();
		
		if(self::isActive() &&
			$ref_id)
		{			
			if(!$a_form)
			{
				$value = (int)$_POST["force_noti"];
			}
			else
			{
				$value = $a_form->getInput("force_noti");
			}						
			$noti = new self($ref_id);
			$noti->switchMode($value);			
		}
	}
}