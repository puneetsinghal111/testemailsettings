<?php

/**
* @license		GNU/GPL, see LICENSE.php
* @contact		puneetsinghal.11@gmail.com
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


// check if Payplans installed or not
jimport('joomla.filesystem.file');

if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}

/**
 * System Plugin
 *
 * @package	Test Email
 * @subpackage	Plugin
 * @author 	Puneet Singhal
 * 
 */
class  plgSystemTestemail extends JPlugin
{
	public $_app = null;
	protected $_tplVars = array();

	function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->_app = JFactory::getApplication();
	}
	
	function onAfterRoute()
	{
		$option = JRequest::getVar('option');
		$from = JRequest::getVar('from_email', false);
		
		if($option != 'com_config' || !$from){
			return true;
		}
		
		$date = JDate::getInstance()->toSql();
		
		$sender  = 'Test User';
		$subject = 'Congratulations your emailing is working perfectly';
		$body 	 = 'Hip-hip hurrrayyyyy. Current Time is: '.$date;
		
		if (JFactory::getMailer()->sendMail($from, $sender, $from, $subject, $body) !== true)
		{
			return false;
		}
		
		if(JVERSION < 3.0){
			$url = JURI::root().DS.'administrator'.DS.'index.php?option=com_config';
			JFactory::getApplication()->redirect($url);
		}
		
		return true;
	}

	function onAfterRender()
	{
		$option = JRequest::getVar('option');
		if($option != 'com_config'){
			return true;
		}
		// Only render for HTML output
		if (JFactory::getDocument()->getType() !== 'html' ) { return; }
		
		$root = JURI::root();
		
		if(JVERSION < 3.0){
			$html = '<script type="text/javascript">';
				$html .= 'function send_email(){';
					$html .= 'var from_email = document.getElementById("jform_mailfrom").value;';
					$html .= 'window.location = "'.$root.'administrator/index.php?option=com_config&from_email="+from_email;';
				$html .= '}';
				$html .= 'var toolbar = document.getElementById("toolbar");';
				$html .= 'toolbar.innerHTML = toolbar.innerHTML + "<a id=\"jxi_test_email\" href=\"#\" onclick=\"javascript:send_email();\" style=\"margin:14% 14% 0 0; font-weight:bold; float:right;\">Test Email</a>";';
			$html .= '</script>';
		}
		elseif (JVERSION == 3.0){
			$html = '<script type="text/javascript">';
				$html .= '(function($){';
					$html .= '$(document).ready(function(){';
						$html .= '$("#toolbar").append("<button class=btn type=submit id=jxi_test_email>Test Email</button>");';
						$html .= '$("#jxi_test_email").click(function(){';
							$html .= 'var from_email = $("#jform_mailfrom").val();';
							$html .= 'var url = "'.$root.'administrator/index.php?option=com_config&from_email=" + from_email;';
							$html .= '$(this).load(url); ';
						$html .= '});';
					$html .= '});';
				$html .= '})(jQuery);';
			$html .= '</script>';	
		}
		$body = JResponse::getBody();
		$body = str_replace('</body>', $html.'</body>', $body);
		JResponse::setBody($body);
		
		return true;
	}
}
