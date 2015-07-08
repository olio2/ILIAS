<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Mail/classes/class.ilMailNotification.php';


/**
 * @author Nadia Ahmad <nahmad@databay.de>
 * @version $Id$
 *
 */
class ilForumMailNotification extends ilMailNotification 
{
	const TYPE_POST_NEW 		= 60;
	const TYPE_POST_ACTIVATION 	= 61;
	const TYPE_POST_UPDATED 	= 62;
	const TYPE_POST_CENSORED 	= 63; 
	const TYPE_POST_DELETED 	= 64;
	const TYPE_POST_ANSWERED	= 65;
	
	public static $events = array(
		self::TYPE_POST_NEW 		=> 'createdPost',
		self::TYPE_POST_ACTIVATION  => 'activatePost',
		self::TYPE_POST_UPDATED		=> 'updatedPost',
		self::TYPE_POST_CENSORED	=> 'censoredPost',
		self::TYPE_POST_DELETED		=> 'deletedPost',
		self::TYPE_POST_ANSWERED	=> 'answeredPost'
	);

	/**
	 * @var bool
	 */
	protected $is_cronjob = false;
	
	/**
	 * @var ilForumNotificationMailData|null
	 */
	protected $provider = NULL;

	/**
	 *
	 */
	public function __construct(ilForumNotificationMailData $provider)
	{
		parent::__construct();
		$this->provider = $provider;
	}

	/**
	 * @return bool|void
	 */
	public function send()
	{
		global $ilSetting, $lng, $ilUser;
		
		$lng->loadLanguageModule('forum');
		
		// check if forum notifications enabled 
		if(!$ilSetting->get('forum_notification'))
		{
			return;
		}
		
		switch($this->getType())
		{
			case self::TYPE_POST_NEW:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
			
					$this->setSubject($this->getLanguageText('forums_notification_subject')." ".$this->provider->getForumTitle());
				
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forums_notification_subject')." ".$this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum').": ".$this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread').": ".$this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('new_post').": \n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author').": ". $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date').": ". $this->provider->getPostDate());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject').": ". $this->provider->getPostTitle());
					$this->appendBody("\n\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody($pos_message . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPostingLinks());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));

				}
				break;

			case self::TYPE_POST_ACTIVATION:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject($this->getLanguageText('forums_notification_subject')." ".$this->provider->getForumTitle());

					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");

					$this->appendBody($this->getLanguageText('forums_post_activation_mail'));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum').": ".$this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread').": ".$this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('new_post').": \n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author').": ". $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date').": ". $this->provider->getPostDate());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject').": ". $this->provider->getPostTitle());
					$this->appendBody("\n\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody($pos_message . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPostingLinks());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
				}
				break;

			case self::TYPE_POST_ANSWERED;
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject($this->getLanguageText('forums_notification_subject')." ".$this->provider->getForumTitle());
						
					$this->setBody(ilMail::getSalutation($rcp,$this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum_post_replied'));
					$this->appendBody($this->getLanguageText('forum').": ".$this->provider->getForumTitle()." -> ".$this->provider->getThreadTitle()."\n\n");

					$this->appendBody("\n------------------------------------------------------------\n");
					$this->appendBody($this->getSecurePostMessage());
					$this->appendBody("\n------------------------------------------------------------\n");

					$this->appendBody($this->getPostingLinks());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
					
				}
				break;
	
			case self::TYPE_POST_UPDATED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(sprintf($this->getLanguageText("post_updated_subject"), $this->provider->getForumTitle()));

					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('post_updated_by'), $this->provider->getPostUpdateUserName(), $this->provider->getForumTitle()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('content_post_updated') . "\n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date') . ": " . $this->provider->getPostDate());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
					$this->appendBody("\n\n");
					
					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");
					
					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPostingLinks());
					$this->appendBody(ilMail::_getInstallationSignature());
					
					$this->sendMail(array($rcp), array('system'));
				}	
				break;
			
			case self::TYPE_POST_CENSORED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(sprintf($this->getLanguageText('post_censored_subject'), $this->provider->getForumTitle()));

					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('post_censored_by'), $this->provider->getPostUpdateUserName() ,$this->provider->getForumTitle()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('content_censored_post') . "\n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date') . ": " . $this->provider->getPostDate());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
					$this->appendBody("\n\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody($pos_message . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPostingLinks());
					$this->appendBody(ilMail::_getInstallationSignature());

					$this->sendMail(array($rcp), array('system'));
				}
				break;
			
			case self::TYPE_POST_DELETED:
				foreach($this->getRecipients() as $rcp)
				{
					$this->initLanguage($rcp);
					$this->initMail();
					$this->setSubject(sprintf($this->getLanguageText('post_deleted_subject'), $this->provider->getForumTitle()));

					$this->setBody(ilMail::getSalutation($rcp, $this->getLanguage()));
					$this->appendBody("\n\n");
					$this->appendBody(sprintf($this->getLanguageText('post_deleted_by'), $ilUser->getLogin(),  $this->provider->getForumTitle()));
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('forum') . ": " . $this->provider->getForumTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('thread') . ": " . $this->provider->getThreadTitle());
					$this->appendBody("\n\n");
					$this->appendBody($this->getLanguageText('content_deleted_post') ."\n------------------------------------------------------------\n");

					$this->appendBody($this->getLanguageText('author') . ": " . $this->provider->getPostUserName($this->getLanguage()));
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('date') . ": " . $this->provider->getPostDate());
					$this->appendBody("\n");
					$this->appendBody($this->getLanguageText('subject') . ": " . $this->provider->getPostTitle());
					$this->appendBody("\n\n");

					if($this->provider->getPostCensored() == 1)
					{
						$this->appendBody($this->provider->getCensorshipComment() . "\n");
					}
					else
					{
						$pos_message = $this->getSecurePostMessage();
						$this->appendBody(strip_tags($pos_message) . "\n");
					}
					$this->appendBody("------------------------------------------------------------\n");

					if(count($this->provider->getAttachments()) > 0)
					{
						foreach($this->provider->getAttachments() as $attachment)
						{
							$this->appendBody($this->getLanguageText('attachment') . ": " . $attachment . "\n");
						}
						$this->appendBody("\n------------------------------------------------------------\n");
					}

					$this->appendBody($this->getPostingLinks());
					$this->appendBody(ilMail::_getInstallationSignature());
					
					$this->sendMail(array($rcp), array('system'));
				}
				break;
		}
		return true;
	}

	/**
	 * @param int $a_usr_id
	 */
	protected function initLanguage($a_usr_id)
	{
		parent::initLanguage($a_usr_id);
		$this->language->loadLanguageModule('forum');
	}

	/**
	 * @return boolean
	 */
	public function isCronjob()
	{
		return (bool)$this->is_cronjob;
	}

	/**
	 * @param boolean $is_cronjob
	 */
	public function setIsCronjob($is_cronjob)
	{
		$this->is_cronjob = (bool)$is_cronjob;
	}

	/**
	 * @return string
	 */
	private function getPostingLinks()
	{
		global $ilIliasIniFile, $ilClientIniFile;
	
		$posting_link = '';
		
		if($this->isCronjob())
		{
			$posting_link = sprintf($this->getLanguageText("forums_notification_show_post"),
					$ilIliasIniFile->readVariable("server", "http_path") . "/goto.php?target=frm_" .
					$this->provider->getRefId() . "_" . $this->provider->getThreadId() . "_" . $this->provider->getPostId() . '&client_id=' . CLIENT_ID) . "\n\n";

			$posting_link .= sprintf($this->getLanguageText("forums_notification_intro"),
					$ilClientIniFile->readVariable("client", "name"),
					$ilIliasIniFile->readVariable("server", "http_path") . '/?client_id=' . CLIENT_ID) . "\n\n";
		}
		else
		{
			$posting_link = sprintf($this->getLanguageText("forums_notification_show_post"),
					ILIAS_HTTP_PATH . "/goto.php?target=frm_" .
					$this->provider->getRefId() . "_" . $this->provider->getThreadId() . "_" . $this->provider->getPostId() . '&client_id=' . CLIENT_ID) . "\n\n";

			$posting_link .= sprintf($this->getLanguageText("forums_notification_intro"),
					$ilClientIniFile->readVariable("client", "name"),
					ILIAS_HTTP_PATH . '/?client_id=' . CLIENT_ID) . "\n\n";
		}
		
		return $posting_link;
	}

	/**
	 * @return string
	 */
	private function getSecurePostMessage()
	{
		$pos_message = $this->provider->getPostMessage();
		if(strip_tags($pos_message) != $pos_message)
		{
			$pos_message = preg_replace("/\n/i", "", $pos_message);
			$pos_message = preg_replace("/<br(\s*)(\/?)>/i", "\n", $pos_message);
			$pos_message = preg_replace("/<p([^>]*)>/i", "\n\n", $pos_message);
			$pos_message = preg_replace("/<\/p([^>]*)>/i", '', $pos_message);
			return $pos_message;
		}
		return strip_tags($pos_message);
	}
}