<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';

/**
 * Methods for building the administration forms
 */
class ilNotificationAdminSettingsForm
{
	/**
	 * @param array $types
	 * @return ilPropertyFormGUI
	 */
	public static function getTypeForm(array $types)
	{
		global $lng;

		$lng->loadLanguageModule('notification');

		$form = new ilPropertyFormGUI();
		$form->setTitle($lng->txt('notification_admin_types'));

		$options = array(
			'disabled'     => $lng->txt('disabled'),
			'set_by_user'  => $lng->txt('set_by_user'),
			'set_by_admin' => $lng->txt('set_by_admin')
		);

		foreach($types as $type)
		{
			$mode = new ilRadioGroupInputGUI($lng->txt('nott_' . $type['name']), 'notifications[' . $type['name'] . ']');
			foreach($options as $key => $translation)
			{
				$option = new ilRadioOption($translation, $key);
				$mode->addOption($option);
			}
			$mode->setValue($type['config_type']);
			$form->addItem($mode);
		}

		return $form;
	}

	/**
	 * @param array $types
	 * @return ilPropertyFormGUI
	 */
	public static function getChannelForm(array $types)
	{
		global $lng;

		$form = new ilPropertyFormGUI();

		$options = array(
			'disabled'     => $lng->txt('disabled'),
			'set_by_user'  => $lng->txt('set_by_user'),
			'set_by_admin' => $lng->txt('set_by_admin')
		);

		foreach($types as $type)
		{
			$mode = new ilRadioGroupInputGUI($lng->txt('notc_' . $type['name']), 'notifications[' . $type['name'] . ']');
			foreach($options as $key => $translation)
			{
				$option = new ilRadioOption($translation, $key);
				$mode->addOption($option);
			}
			$mode->setValue($type['config_type']);
			$form->addItem($mode);
		}

		return $form;
	}

	/**
	 * @return ilPropertyFormGUI
	 */
	public static function getGeneralSettingsForm()
	{
		global $lng;

		$form = new ilPropertyFormGUI();

		require_once 'Services/Notifications/classes/class.ilNotificationDatabaseHelper.php';

		$channels = ilNotificationDatabaseHandler::getAvailableChannels(array(), true);

		$options = array(
			'set_by_user'  => $lng->txt('set_by_user'),
			'set_by_admin' => $lng->txt('set_by_admin')
		);
		/**
		 * @todo dirty...
		 */
		$form->restored_values = array();
		$store_values          = array();
		foreach($channels as $channel)
		{
			$chb = new ilCheckboxInputGUI($lng->txt('enable_' . $channel['name']), 'enable_' . $channel['name']);
			if($lng->txt('enable_' . $channel['name'] . '_info') != '-enable_' . $channel['name'] . '_info-')
			{
				$chb->setInfo($lng->txt('enable_' . $channel['name'] . '_info'));
			}

			$store_values[] = 'enable_' . $channel['name'];

			$mode = new ilRadioGroupInputGUI($lng->txt('config_type'), 'notifications[' . $channel['name'] . ']');
			foreach($options as $key => $translation)
			{
				$option = new ilRadioOption($translation, $key);
				$mode->addOption($option);
			}
			$mode->setValue($channel['config_type']);
			$chb->addSubItem($mode);

			/**
			 * @todo dirty...
			 */
			$form->restored_values['notifications[' . $channel['name'] . ']'] = $channel['config_type'];
			require_once $channel['include'];

			// let the channel display their own settings below the "enable channel"
			// checkbox
			$inst   = new $channel['handler']();
			$result = $inst->{'showSettings'}($chb);
			if($result)
			{
				$store_values = array_merge($result, $store_values);
			}

			$form->addItem($chb);
		}

		/**
		 * @todo dirty...
		 */
		$form->store_values = $store_values;

		return $form;
	}
}