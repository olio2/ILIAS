<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* Awareness tool administration
*
* @author Alex Killing <killing@leifos.com>
* @version $Id$
*
* @ilCtrl_Calls ilObjAwarenessAdministrationGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjAwarenessAdministrationGUI: ilAdministrationGUI
*
* @ingroup ServicesAwareness
*/
class ilObjAwarenessAdministrationGUI extends ilObjectGUI
{
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "awra";
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule("awrn");
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editSettings";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 */
	public function getAdminTabs()
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("settings",
				$this->lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "editSettings"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("perm_settings",
				$this->lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"));
		}
	}

	
	/**
	 * Edit settings.
	 */
	public function editSettings($a_form = null)
	{
		$this->tabs_gui->setTabActive('settings');
		
		if(!$a_form)
		{
			$a_form = $this->initFormSettings();
		}		
		$this->tpl->setContent($a_form->getHTML());
		return true;
	}

	/**
	 * Save settings
	 */
	public function saveSettings()
	{
		global $ilCtrl;
		
		$this->checkPermission("write");
		
		$form = $this->initFormSettings();
		if($form->checkInput())
		{
			$awrn_set = new ilSetting("awrn");
			$awrn_set->set("awrn_enabled", (bool) $form->getInput("enable_awareness"));

			include_once("./Services/Awareness/classes/class.ilAwarenessUserProviderFactory.php");
			$prov = ilAwarenessUserProviderFactory::getAllProviders();
			foreach ($prov as $p)
			{
				$p->setActivationMode($form->getInput("up_act_mode_".$p->getProviderId()));
			}

			ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
			$ilCtrl->redirect($this, "editSettings");
		}
		
		$form->setValuesByPost();
		$this->editSettings($form);
	}

	/**
	 * Save settings
	 */
	public function cancel()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "view");
	}
		
	/**
	 * Init settings property form
	 *
	 * @access protected
	 */
	protected function initFormSettings()
	{
	    global $lng;
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('awareness_settings'));
		$form->addCommandButton('saveSettings',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));

		$en = new ilCheckboxInputGUI($lng->txt("awrn_enable"), "enable_awareness");
		$form->addItem($en);

		$awrn_set = new ilSetting("awrn");
		$en->setChecked($awrn_set->get("awrn_enabled", false));

		include_once("./Services/Awareness/classes/class.ilAwarenessUserProviderFactory.php");
		$prov = ilAwarenessUserProviderFactory::getAllProviders();
		foreach ($prov as $p)
		{
			// activation mode
			$options = array(
				ilAwarenessUserProvider::MODE_INACTIVE => $lng->txt("awrn_inactive"),
				ilAwarenessUserProvider::MODE_ONLINE_ONLY => $lng->txt("awrn_online_only"),
				ilAwarenessUserProvider::MODE_INCL_OFFLINE => $lng->txt("awrn_incl_offline")
				);
			$si = new ilSelectInputGUI($p->getTitle(), "up_act_mode_".$p->getProviderId());
			$si->setOptions($options);
			$si->setInfo($p->getInfo());
			$si->setValue($p->getActivationMode());
			$en->addSubItem($si);
		}

		return $form;
	}
}
?>