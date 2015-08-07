<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailTemplateContext.php';

/**
 * Handles course mail placeholders
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @package ModulesCourse
 */
class ilCourseMailTemplateContext extends ilMailTemplateContext
{
	/**
	 * @return string
	 */
	public function getId()
	{
		return 'crs_context_manual';
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return 'Course XYZ';
	}

	/**
	 * Return an array of placeholders
	 * @return array
	 */
	public function getSpecificPlaceholders()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;

		$lng->loadLanguageModule('crs');

		return array(
			'crs_title' => array(
				'placeholder' => 'COURSE_TITLE',
				'label'       => $lng->txt('crs_title')
			),
			'crs_link'  => array(
				'placeholder' => 'CRS_LINK',
				'label'       => $lng->txt('crs_mail_permanent_link')
			)
		);
	}

	/**
	 * @param string    $placeholder_id
	 * @param array     $context_parameters
	 * @param ilObjUser $recipient
	 * @param bool      $html_markup
	 * @return string
	 */
	public function resolveSpecificPlaceholder($placeholder_id, array $context_parameters, ilObjUser $recipient, $html_markup = false)
	{
		/**
		 * @var $ilObjDataCache ilObjectDataCache
		 */
		global $ilObjDataCache;

		if('crs_title' == $placeholder_id)
		{
			return $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($context_parameters['ref_id']));
		}
		else if('crs_link' == $placeholder_id)
		{
			require_once './Services/Link/classes/class.ilLink.php';
			return ilLink::_getLink($context_parameters['ref_id'], 'crs');
		}

		return '';
	}
}