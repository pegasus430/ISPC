<?php

    Doctrine_Manager::getInstance()->bindComponent('EmailLog', 'SYSDAT');

    class EmailLog extends BaseEmailLog {

	public function logemails($sender, $receiver, $subject, $ipids, $exception = false)
	{
		if(is_array($receiver)){
			$receiver =  implode(",",array_keys($receiver));
		}

		$mail_log = new EmailLog();
	    $mail_log->sender = $sender;
	    $mail_log->receiver = html_entity_decode($receiver);
	    $mail_log->subject = $subject;
	    $mail_log->ipid = $ipids;
	    if($exception)
	    {
		$mail_log->error = $exception;
	    }
	    $mail_log->save();
	}

	public function write_email_log($log_message)
	{
	    $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/mail.log');
	    $log = new Zend_Log($writer);
	    $log->crit($log_message);
	}

    }

?>