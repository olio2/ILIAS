<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Object/classes/class.ilObjectGUI.php';
require_once './Services/Notifications/classes/class.ilObjNotificationAdmin.php';
require_once './Services/Notifications/classes/class.ilObjNotificationAdminAccess.php';

/**
 * GUI class for notification objects.
 *
 * @author            Jan Posselt <jposselt@databay.de>
 * @version           $Id$
 *
 * @ilCtrl_Calls      ilObjNotificationAdminGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjNotificationAdminGUI: ilAdministrationGUI
 *
 * @ingroup           ServicesNotifications
 */
class ilObjNotificationAdminGUI extends ilObjectGUI
{
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilLocatorGUI
	 */
	protected $locator;

	/**
	 * {@inheritdoc}
	 */
	public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $DIC;

		$this->type = 'nota';
		parent::__construct($a_data, $a_id, $a_call_by_reference, false);
		$this->lng->loadLanguageModule('notification');

		$this->tabs    = $DIC->tabs();
		$this->access  = $DIC->access();
		$this->locator = $DIC['ilLocator'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd        = $this->ctrl->getCmd();

		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				$this->initSubTabs();
				$this->tabs->activateTab('view');

				if(strlen($cmd) == 0 || $cmd == 'view')
				{
					$cmd = 'showGeneralSettings';
				}

				$cmd .= 'Object';
				$this->$cmd();
				break;
		}
	}

	public function initSubTabs()
	{
		$this->tabs->addSubTabTarget('notification_general', $this->ctrl->getLinkTargetByClass('ilObjNotificationAdminGUI', 'showGeneralSettings'));
		//$this->tabs->addSubTabTarget('notification_admin_channels', $this->ctrl->getLinkTargetByClass('ilObjNotificationAdminGUI', 'showChannels'));
		$this->tabs->addSubTabTarget('notification_admin_types', $this->ctrl->getLinkTargetByClass('ilObjNotificationAdminGUI', 'showTypes'));
		$this->tabs->addSubTabTarget('notification_admin_matrix', $this->ctrl->getLinkTargetByClass('ilObjNotificationAdminGUI', 'showConfigMatrix'));
	}

	/**
	 * {@inheritdoc}
	 */
	protected function setTabs()
	{
		$this->ctrl->setParameter($this, 'ref_id', $this->ref_id);

		if($this->access->checkAccess('visible', '', $this->ref_id))
		{
			$this->tabs->addTab('id_info',
				$this->lng->txt('info_short'),
				$this->ctrl->getLinkTargetByClass(array('ilobjfilegui', 'ilinfoscreengui'), 'showSummary'));
		}

		if($this->access->checkAccess('edit_permission', '', $this->ref_id))
		{
			$this->tabs->addTab('id_permissions',
				$this->lng->txt('perm_settings'),
				$this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), 'perm'));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function addLocatorItems()
	{
		if(is_object($this->object))
		{
			$this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ''), '', (int)$_GET['ref_id']);
		}
	}

	public function saveGeneralSettingsObject()
	{
		require_once 'Services/Notifications/classes/class.ilNotificationAdminSettingsForm.php';

		$settings = new ilSetting('notifications');

		$form = ilNotificationAdminSettingsForm::getGeneralSettingsForm();
		$form->setValuesByPost();
		if(!$form->checkInput())
		{
			$this->showGeneralSettingsObject($form);
		}
		else
		{
			/**
			 * @todo dirty...
			 *
			 * push all notifiation settings to the form to enable custom
			 * settings per channel
			 */
			$values = $form->store_values;//array('enable_osd', 'osd_polling_intervall', 'enable_mail');

			// handle custom channel settings
			foreach($values as $v)
			{
				$settings->set($v, $_POST[$v]);
			}

			foreach($_REQUEST['notifications'] as $type => $value)
			{
				ilNotificationDatabaseHandler::setConfigTypeForChannel($type, $value);
			}

			ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
			$this->showGeneralSettingsObject();
		}
	}

	/**
	 * @param ilPropertyFormGUI|null $form
	 */
	public function showGeneralSettingsObject(ilPropertyFormGUI $form = null)
	{
		require_once 'Services/Notifications/classes/class.ilNotificationAdminSettingsForm.php';

		$this->tabs->activateSubTab('notification_general');

		if($form == null)
		{
			$form     = ilNotificationAdminSettingsForm::getGeneralSettingsForm();
			$settings = new ilSetting('notifications');

			/**
			 * @todo dirty...
			 *
			 * push all notifiation settings to the form to enable custom
			 * settings per channel
			 */
			$form->setValuesByArray(array_merge($settings->getAll(), $form->restored_values));
		}

		$form->setFormAction($this->ctrl->getFormAction($this, 'saveGeneralSettings'));
		$form->addCommandButton('saveGeneralSettings', $this->lng->txt('save'));

		$this->tpl->setContent($form->getHTML());
	}

	public function saveTypesObject()
	{
		require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

		foreach($_REQUEST['notifications'] as $type => $value)
		{
			ilNotificationDatabaseHandler::setConfigTypeForType($type, $value);
		}

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->showTypesObject();
	}

	public function showTypesObject()
	{
		$this->tabs->activateSubTab('notification_admin_types');

		require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
		require_once 'Services/Notifications/classes/class.ilNotificationAdminSettingsForm.php';

		$form = ilNotificationAdminSettingsForm::getTypeForm(ilNotificationDatabaseHandler::getAvailableTypes());
		$form->setFormAction($this->ctrl->getFormAction($this, 'showTypes'));
		$form->addCommandButton('saveTypes', $this->lng->txt('save'));
		$this->tpl->setContent($form->getHTML());
	}

	public function saveChannelsObject()
	{
		require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

		foreach($_REQUEST['notifications'] as $type => $value)
		{
			ilNotificationDatabaseHandler::setConfigTypeForChannel($type, $value);
		}

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->showChannelsObject();
	}

	public function showChannelsObject()
	{
		$this->tabs->activateSubTab('notification_admin_channels');

		require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
		require_once 'Services/Notifications/classes/class.ilNotificationAdminSettingsForm.php';

		$form = ilNotificationAdminSettingsForm::getChannelForm(ilNotificationDatabaseHandler::getAvailableChannels());
		$form->setFormAction($this->ctrl->getFormAction($this, 'showChannels'));
		$form->addCommandButton('saveChannels', $this->lng->txt('save'));
		$form->addCommandButton('showChannels', $this->lng->txt('cancel'));
		$this->tpl->setContent($form->getHTML());
	}

	private function saveConfigMatrixObject()
	{
		require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

		ilNotificationDatabaseHandler::setUserConfig(-1, $_REQUEST['notification'] ? $_REQUEST['notification'] : array());

		ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
		$this->showConfigMatrixObject();
	}

	public function showConfigMatrixObject()
	{
		$this->tabs->activateSubTab('notification_admin_matrix');

		require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';
		require_once 'Services/Notifications/classes/class.ilNotificationSettingsTable.php';

		$userdata = ilNotificationDatabaseHandler::loadUserConfig(-1);

		$table = new ilNotificationSettingsTable($this, 'a title', ilNotificationDatabaseHandler::getAvailableChannels(), $userdata, true);
		$table->setFormAction($this->ctrl->getFormAction($this, 'saveConfigMatrix'));
		$table->setData(ilNotificationDatabaseHandler::getAvailableTypes());
		$table->setDescription($this->lng->txt('notification_admin_matrix_settings_table_desc'));
		$table->addCommandButton('saveConfigMatrix', $this->lng->txt('save'));

		$this->tpl->setContent($table->getHTML());
	}
}