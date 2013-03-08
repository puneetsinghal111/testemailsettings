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
class  plgSystemTestemail extends XiPlugin
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
		$view = JRequest::getVar('view');
		$from = JRequest::getVar('from_email');
		
		if($option != 'com_config' || $view != 'testemail'){
			return true;
		}
		
		$sender = 'Test User';
		$subject = 'Congratulations your emailing is working perfectly';
		$body = 'Hip-hip hurrrayyyyy';
		
		if (JFactory::getMailer()->sendMail($from, $sender, $from, $subject, $body) !== true)
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Add a image just before </body> tag
	 * which will href to cron trigger.
	 */
	function onAfterRender()
	{
		$option = JRequest::getVar('option');
		if($option != 'com_config'){
			return true;
		}
		?>
		<script>
			
		</script>
		<?php 
		// Only render for HTML output
		if (JFactory::getDocument()->getType() !== 'html' ) { return; }
		
		$root = JURI::root();
		$html = '<script type="text/javascript">';
			$html .= '(function($){';
				$html .= '$(document).ready(function(){';
					$html .= '$("#toolbar").append("<button class=btn type=submit id=jxi_test_email>Test Email</button>");';
					$html .= '$("#jxi_test_email").click(function(){';
						$html .= 'var from_email = $("#jform_mailfrom").val();';
						$html .= 'var url = "'.$root.'index.php?option=com_config&view=testemail&from_email=" + from_email;';
						$html .= '$(this).load(url); ';
						//$html .= 'window.location = "http://localhost/payplans2507/index.php?option";';
					$html .= '});';
				$html .= '});';
			$html .= '})(jQuery);';
		$html .= '</script>';
		//$html = '<button class="btn btn-success">Test Email</button>';	
		$body = JResponse::getBody();
		$body = str_replace('</body>', $html.'</body>', $body);
		JResponse::setBody($body);
		
		}
    
}


