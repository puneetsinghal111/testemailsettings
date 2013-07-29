<?php

/**
* @copyright	Copyright (C) 2009 - 2013 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @contact		team@readybytes.in
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
 * @author 	puneetsinghal.11@gmail.com
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
	
	function onAfterRender()
	{
		$option = JRequest::getVar('option');
		if($option != 'com_config'){
			return true;
		}
		// Only render for HTML output
		if (JFactory::getDocument()->getType() !== 'html' ) { return; }
		
		if(JVERSION < 3.0){
			$html = $this->_getJ25Html();
		}
		elseif (JVERSION >= 3.0){
			$html = $this->_getJ30Html();
		}
		$body = JResponse::getBody();
		$body = str_replace('</body>', $html.'</body>', $body);
		JResponse::setBody($body);
		
		return true;
	}
	
	function onAfterRoute()
	{
		$input = JFactory::getApplication()->input;
		$option = $input->get('option', false);
		$rbsl 	= $input->get('rbsl', false);
		
		if($option != 'com_config' || !$rbsl){
			return true;
		}
		
		$from 	= $input->get('from_email', false, 'string');
		$date = JDate::getInstance()->toSql();
		
		$sender  = 'Test User';
		$subject = 'Congratulations your emailing is working perfectly';
		$body 	 = 'Hip-hip hurrrayyyyy. Current Time is: '.$date;
		$result	 = array();
		$message = 'Email send successfully. <strong>IMPORTANT:</strong> Save your current email settings.';
		$state	 = 'message';
		
		$mailer = self::createMailer();
		
		if ($mailer->sendMail($from, $sender, $from, $subject, $body) !== true)
		{
			if(JVERSION < 3.0){
				$message = 'Email sending failed. Check your email settings and try again.';
				$state	 = 'error';
			}else {
				$this->_getJ30ErrorMsz();
			}
		}
		
		if(JVERSION < 3.0){
			$url = JURI::root().DS.'administrator'.DS.'index.php?option=com_config';
			$app = JFactory::getApplication();
			$app->enqueueMessage($message, $state);
			$app->redirect($url);
			return true;
		}
		
		$this->_getJ30SuccessMsz();
	}
	
	protected static function createMailer()
	{
		$input = JFactory::getApplication()->input;

		$smtpauth 	= $input->get('smtp_auth', 0);
		$smtpuser 	= $input->get('smtp_user', false, 'string');
		$smtppass 	= $input->get('smtp_pass', false, 'string');
		$smtphost 	= $input->get('smtp_host', false);
		$smtpsecure = $input->get('smtp_secure', false);
		$smtpport 	= $input->get('smtp_port', false);
		$mailfrom 	= $input->get('from_email', false, 'string');
		$fromname 	= $input->get('from_name', false);
		$mailer 	= $input->get('mailer', false);

		// Create a JMail object
		$mail = JMail::getInstance();

		// Set default sender without Reply-to
		$mail->SetFrom(JMailHelper::cleanLine($mailfrom), JMailHelper::cleanLine($fromname), 0);

		// Default mailer is to use PHP's mail function
		switch ($mailer)
		{
			case 'smtp':
				$mail->useSMTP($smtpauth, $smtphost, $smtpuser, $smtppass, $smtpsecure, $smtpport);
				break;

			case 'sendmail':
				$mail->IsSendmail();
				break;

			default:
				$mail->IsMail();
				break;
		}

		return $mail;
	}
	
	protected function _getJ30ErrorMsz()
	{
		$result['status'] = 'error';
		$result['message']= 'Email sending failed. Check your email settings and try again.';
		$result = json_encode($result);
		
		echo $result;
		exit();
	}
	
	protected function _getJ30SuccessMsz()
	{
		$result['status'] = 'success';
		$result['message']= 'Email send successfully. <strong>IMPORTANT:</strong> Save your current email settings.';
		$result = json_encode($result);

		echo $result;
		exit();
	}
	
	private function _getJ25Html()
	{
		$root = JURI::root();
		ob_start();
		?>
		
		<script type="text/javascript">
			function send_email(){
				var from_email = document.getElementById("jform_mailfrom").value;
				var url = "<?php echo $root; ?>";

				var e = document.getElementById("jform_mailer");
				var mailer = e.options[e.selectedIndex].value;

				var e = document.getElementById("jform_smtpsecure");
				var smtp_secure = e.options[e.selectedIndex].value;

				var smtp_auth	= 1;
				if(document.getElementById('jform_smtpauth1').checked){
					smtp_auth	= 0;
				}
				
				url = url + 'administrator/index.php?option=com_config&';
				url = url + 'from_email=' 	+ document.getElementById("jform_mailfrom").value + '&';
				url = url + 'mailer=' 		+ mailer + '&';
				url = url + 'from_name=' 	+ document.getElementById("jform_mailfrom").value + '&';
				url = url + 'send_path=' 	+ document.getElementById("jform_sendmail").value + '&';
				url = url + 'smtp_auth=' 	+ smtp_auth + '&';
				url = url + 'smtp_secure=' 	+ smtp_secure + '&';
				url = url + 'smtp_port=' 	+ document.getElementById("jform_smtpport").value + '&';
				url = url + 'smtp_user=' 	+ document.getElementById("jform_smtpuser").value + '&';
				url = url + 'smtp_pass=' 	+ document.getElementById("jform_smtppass").value + '&';
				url = url + 'smtp_host=' 	+ document.getElementById("jform_smtphost").value + '&';
				url = url + 'rbsl=1';

				window.location = url;
			}

			var toolbar = document.getElementById("toolbar");
			toolbar.innerHTML = toolbar.innerHTML + "<a id=\"jxi_test_email\" href=\"#\" onclick=\"javascript:send_email();\" style=\"margin:14% 14% 0 0; font-weight:bold; float:right;\">Test Email</a>";
		</script>
		
		<?php 
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	private function _getJ30Html()
	{
		$root = JURI::root();
		ob_start();
		?>
		<script type="text/javascript">
			(function($){
				$(document).ready(function(){
					$("#toolbar").append("<button class=\"btn\" type=submit id=jxi_test_email><i class=\"icon-wrench\"></i>&nbsp;Test Email</button>");
					$("#content").prepend("<div id=\"jxi_email_msg\">&nbsp;</div>");

					$("#jxi_test_email").click(function(){
						$('body').css('opacity','0.4');
						var smtp_auth	= 1;
						if($('#jform_smtpauth1').prop("checked")){
							smtp_auth	= 0;
						}

						var url = "<?php echo $root;?>";
						url = url + 'administrator/index.php?option=com_config&';
						url = url + 'from_email=' 	+ $("#jform_mailfrom").val() + '&';
						url = url + 'mailer=' 		+ $('#jform_mailer :selected').val() + '&';
						url = url + 'from_name=' 	+ $('#jform_fromname').val() + '&';
						url = url + 'send_path=' 	+ $('#jform_sendmail').val() + '&';
						url = url + 'smtp_auth=' 	+ smtp_auth + '&';
						url = url + 'smtp_secure=' 	+ $('#jform_smtpsecure :selected').val() + '&';
						url = url + 'smtp_port=' 	+ $('#jform_smtpport').val() + '&';
						url = url + 'smtp_user=' 	+ $('#jform_smtpuser').val() + '&';
						url = url + 'smtp_pass=' 	+ $('#jform_smtppass').val() + '&';
						url = url + 'smtp_host=' 	+ $('#jform_smtphost').val() + '&';
						url = url + 'rbsl=1';
						
						//$(this).load(url);
						$.get(url, function(data, status){
							var record = JSON.parse(data);
							$('body').css('opacity','1');

							if(record.status == 'success'){
								$('#jxi_email_msg').removeClass("alert alert-error");
								$('#jxi_email_msg').addClass("alert alert-success");
								var message = record.message; 
								$('#jxi_email_msg').html(message);
							}

							if(record.status == 'error'){
								$('#jxi_email_msg').removeClass("alert alert-success");
								$('#jxi_email_msg').addClass("alert alert-error");
								var message = record.message; 
								$('#jxi_email_msg').html(message);
							}
							
						});
					});
				});
			})(jQuery);
		</script>
		
		<?php 
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	
}
