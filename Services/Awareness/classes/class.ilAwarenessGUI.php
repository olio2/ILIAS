<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Awareness GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessGUI
{
	protected $ctrl;

	/**
	 * Constructor
	 */
	function __construct()
	{
		global $ilCtrl;

		$this->ref_id = (int) $_GET["ref_id"];
		$this->ctrl = $ilCtrl;
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$cmd = $this->ctrl->getCmd();

		if (in_array($cmd, array("getAwarenessList")))
		{
			$this->$cmd();
		}
	}


	/**
	 * Get instance
	 *
	 * @return ilAwarenessGUI awareness gui object
	 */
	static function getInstance()
	{
		return new ilAwarenessGUI();
	}

	/**
	 * Get main menu html
	 */
	function getMainMenuHTML()
	{
		global $ilUser;

		$awrn_set = new ilSetting("awrn");
		if (!$awrn_set->get("awrn_enabled", false))
		{
			return "";
		}

		$cache_period = (int) $awrn_set->get("caching_period");
		$last_update = ilSession::get("awrn_last_update");
		$now = time();

		// init js
		$GLOBALS["tpl"]->addJavascript("./Services/Awareness/js/Awareness.js");
		$GLOBALS["tpl"]->addOnloadCode("il.Awareness.setBaseUrl('".$this->ctrl->getLinkTarget($this,
				"", "", true, false)."');");
		$GLOBALS["tpl"]->addOnloadCode("il.Awareness.setLoaderSrc('".ilUtil::getImagePath("loader.svg")."');");
		$GLOBALS["tpl"]->addOnloadCode("il.Awareness.init();");

		$tpl = new ilTemplate("tpl.awareness.html", true, true, "Services/Awareness");

		include_once("./Services/Awareness/classes/class.ilAwarenessAct.php");
		$act = ilAwarenessAct::getInstance($ilUser->getId());
		$act->setRefId($this->ref_id);

		if ($last_update == "" || ($now - $last_update) >= $cache_period)
		{
			$cnt = $act->getAwarenessUserCounter();
			$act->notifyOnNewOnlineContacts();
			ilSession::set("awrn_last_update", $now);
			ilSession::set("awrn_nr_users", $cnt);
		}
		else
		{
			$cnt = (int) ilSession::get("awrn_nr_users");
		}

		if ($cnt > 0)
		{
			$tpl->setCurrentBlock("status_text");
			$tpl->setVariable("STATUS_TXT", $cnt);
			$tpl->parseCurrentBlock();

			$tpl->setVariable("LOADER", ilUtil::getImagePath("loader.svg"));

			return $tpl->get();
		}

		return "";
	}
	
	/**
	 * Get awareness list (ajax)
	 */
	function getAwarenessList()
	{
		global $ilUser;

		$filter = $_GET["filter"];

		$tpl = new ilTemplate("tpl.awareness_list.html", true, true, "Services/Awareness");

		include_once("./Services/Awareness/classes/class.ilAwarenessAct.php");
		$act = ilAwarenessAct::getInstance($ilUser->getId());
		$act->setRefId($this->ref_id);

		$users = $act->getAwarenessData($filter);

		$ucnt = 0;
		$last_uc_title = "";
		foreach ($users as $u)
		{
			if ($u->collector != $last_uc_title)
			{
				$tpl->setCurrentBlock("uc_title");
				$tpl->setVariable("UC_TITLE", $u->collector);
				$tpl->parseCurrentBlock();
				$tpl->setCurrentBlock("item");
				$tpl->parseCurrentBlock();
			}
			$last_uc_title = $u->collector;

			$ucnt++;

			$fcnt = 0;
			foreach ($u->features as $f)
			{
				$fcnt++;
				if ($fcnt == 1)
				{
					$tpl->touchBlock("arrow");
					//$tpl->setCurrentBlock("arrow");
					//$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("feature");
				$tpl->setVariable("FEATURE_HREF", $f->href);
				$tpl->setVariable("FEATURE_TEXT", $f->text);
				$tpl->parseCurrentBlock();
			}

			if ($u->online)
			{
				$tpl->touchBlock("uonline");
			}

			$tpl->setCurrentBlock("user");
			if ($u->public_profile)
			{
				$tpl->setVariable("UNAME", $u->lastname.", ".$u->firstname);
			}
			else
			{
				$tpl->setVariable("UNAME", "&nbsp;");
			}
			$tpl->setVariable("UACCOUNT", $u->login);

			$tpl->setVariable("USERIMAGE", $u->img);
			$tpl->setVariable("CNT", $ucnt);
			$tpl->parseCurrentBlock();
			$tpl->setCurrentBlock("item");
			$tpl->parseCurrentBlock();
		}

		include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
		$tpl->setCurrentBlock("filter");
		$tpl->setVariable("GL_FILTER", ilGlyphGUI::get(ilGlyphGUI::FILTER));
		$tpl->setVariable("VAL_FILTER", ilUtil::prepareFormOutput($filter));
		$tpl->parseCurrentBlock();

		echo $tpl->get();
		exit;
	}
	
}
?>