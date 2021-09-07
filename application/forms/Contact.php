<?php

require_once("Pms/Form.php");

class Application_Form_Contact extends Pms_Form
{
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();
		$error=0;
		$val = new Pms_Validation();
		if(!$val->isstring($post['your_name'])){
			$this->error_message['your_name']=$Tr->translate('enteryourname'); $error=1;
		}
		if(!$val->email($post['your_emailid'])){
			$this->error_message['your_emailid'] = $Tr->translate("pleaseprovideyourvalidemail");$error=7;
		}
		if(!$val->isstring($post['subject'])){
			$this->error_message['subject']=$Tr->translate('entersubject'); $error=2;
		}
		if(!$val->isstring($post['message'])){
			$this->error_message['message']=$Tr->translate('entermessage'); $error=3;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function SendMail($post)
	{
		$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
		$mail = new Zend_Mail('UTF-8');

		$mail->setBodyText($post['message'])
		->setFrom($post['your_emailid'], $post['your_name'])
		->addTo('info@smart-q.de', 'ISPC')
		->setSubject($post['subject'])
		->send($mail_transport);
	}

	 
}

?>