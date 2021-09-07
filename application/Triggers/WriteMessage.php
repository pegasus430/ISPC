<?php

// require_once 'Pms/Triggers.php';

class application_Triggers_WriteMessage extends Pms_Triggers
{

	public function triggerWriteMessage($event,$inputs,$fieldname,$fieldid,$eventid,$gpost)
	{
		$message=$inputs['message'];
		$from=$inputs['fromaddress'];
		$subject=$inputs['subject'];
		$invoker = $event->getInvoker();
		$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
		$mail = new Zend_Mail();
		$receiver_id= $invoker->recipient;
		$usr = Doctrine::getTable('User')->find($receiver_id);
		$usrarray = $usr->toArray();
		$useremail_id=$usrarray['emailid'];
		$firstname=$usrarray['firstname'];

		$mail->setBodyText($message)
		->setFrom($from, "ISPC")
		->addTo($useremail_id, $firstname)
		->setSubject($subject.'- System Notification, '.date('d.m.Y H:i'))
		->send($mail_transport);
	}

}

?>