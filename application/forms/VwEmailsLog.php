<?php

require_once("Pms/Form.php");

class Application_Form_VwEmailsLog extends Pms_Form
{
	public function validate($post)
	{
		$error=0;
		
		$val = new Pms_Validation();
		
		if( count($post['recipients'])<1)
		{
			$this->error_message['recipients'] = $this->translate("select_at_least_one_voluntary_worker");
			$error=1;
		}
		if( ! $val->isstring($post['subject']))
		{
			$this->error_message['subject'] = $this->translate('enter_email_subject'); 
			$error=1;
		}
		if( ! $val->isstring($post['content']))
		{
			$this->error_message['content'] = $this->translate('enter_email_content'); 
			$error=1;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	/**
	 * 
	 * Jul 21, 2017 @claudiu 
	 * 
	 * @param array $post
	 */
	public function save2email_log($post = array())
	{
				
		$date_time = date("Y-m-d H:i:s",time());
		$batch_id = 0;
		
		//$body_is_html = Pms_CommonData::assertIsHtml($post['content']);
		$enc_subject = Pms_CommonData::aesEncrypt($post['subject']);
		$enc_content = Pms_CommonData::aesEncrypt($post['content']);

		$enc_filetype = null;
		$enc_filetitle = null;
		$enc_filename = null;
		
		$att = false;
		$ftp_put_queue_id = false;
		$ftp_put_queue_filename =  false;
		
		//create attachment and put file on ftp
		if( is_array($post['attachment']))
		{
			$att = new Zend_Mime_Part(@file_get_contents($post['attachment']['filepath']));
			$att->type        = mime_content_type($post['attachment']['filepath']);
			$att->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
			$att->encoding    = Zend_Mime::ENCODING_BASE64;
			$att->filename    = $post['attachment']['filename'];
				
			$ftp_put_queue_id = Pms_CommonData :: ftp_put_queue($post['attachment']['filepath'] , 'clientuploads');
			
			if ($ftp_put_queue_filename = FtpPutQueue::get_file_name_by_id($ftp_put_queue_id)){
				$pathinfo = pathinfo($ftp_put_queue_filename);
				$ftp_put_path = "clientuploads/". $pathinfo['dirname'] . ".zip";
				$ftp_put_file_realname = $pathinfo['basename'];
				
				$enc_filetype = Pms_CommonData::aesEncrypt($pathinfo['extension']);
				$enc_filetitle = Pms_CommonData::aesEncrypt($this->translate('email_attachment') . ": " . $post['attachment']['filename']);
				$enc_filename = Pms_CommonData::aesEncrypt($ftp_put_queue_filename);
			}
		}
		
		//get client smtp settings
		$c_smpt_s = new ClientSMTPSettings();
		$smtp_settings = $c_smpt_s->get_mail_transport_cfg( $this->logininfo->clientid );
	
		if($client_smtp_settings !== false && ! empty($smtp_settings['host'])) {
			//use client defined settings
			$mail_transport 	= new Zend_Mail_Transport_Smtp( $smtp_settings['host'], $smtp_settings['config'] );
			$mail_FromEmail		= $smtp_settings['sender_email'];
			$mail_FromName		= $smtp_settings['sender_name'];
			
		} else {
			//use ispc default smtp
			$mail_transport		= new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
			$mail_FromEmail		= ISPC_SENDER;
			$mail_FromName		= ISPC_SENDERNAME;
		}
				
		//get client details
		$c_client_obj = new Client();
		$clientid_details = $c_client_obj->getClientDataByid($this->logininfo->clientid);
		$clientid_details = $clientid_details[0];
		
		//get curent user details
		$user_data_array = User::getUsersNiceName(array($this->logininfo->userid) , $this->logininfo->clientid);
		$userid_details =  $user_data_array[$this->logininfo->userid];
		
		//init the tokens filter
		$tokens_obj = new Pms_Tokens('VoluntaryworkersEmail');
		
		foreach($post['recipients'] as $key=>$val)
		{
			if( ! isset($post['client_vw_array'][$val]) || empty($post['client_vw_array'][$val]['email'])) {
				continue;//this is not your voluntaryworker, or vw email is empty
			}
			$vw_array = $post['client_vw_array'][$val];
			
 
			
			$email_log = new VwEmailsLog();
			$email_log->sender = $this->logininfo->userid;
			$email_log->clientid = $this->logininfo->clientid;
			$email_log->recipient = $val;
			$email_log->date = $date_time;
			$email_log->title = $enc_subject;
			$email_log->content = $enc_content;
			$email_log->recipients = implode(',', $post['recipients']);
			$email_log->batch_id = $batch_id;
			$email_log->save();
			
			//save this batch_id = self_id so we can group after
			if($batch_id == 0) {
				$batch_id = $email_log->id;
				$email_log->batch_id = $batch_id;
				$email_log->save();
			}
			
			//save the attachment on the member's page
			if($ftp_put_queue_id && $ftp_put_queue_filename) 
			{
				$cf_obj = new ClientFileUpload();
				$cf_obj->title = $enc_filetitle;
				$cf_obj->clientid = $this->logininfo->clientid;
				$cf_obj->file_name = $enc_filename;
				$cf_obj->file_type = $enc_filetype;
				$cf_obj->tabname= 'voluntary_worker';
				$cf_obj->recordid = $val;
				$cf_obj->parent_id = 0;
				
				if($cf_obj->parent_id == 0){
					//this is the original file
// 					$cf_obj->parent_id = $cf_obj->id;
					$cf_obj->revision = "1";
					$cf_obj->save();
				}

				if($cf_obj->id) {
					$email_log->attachment_id = $cf_obj->id;
					$email_log->save();
				}
				
			}


			$tokenfilter = array();
			$tokenfilter['client'] = $clientid_details;
			$tokenfilter['user'] = $userid_details;
			$tokenfilter['voluntaryworker'] = $vw_array;
	
			
			$email_subject = $tokens_obj->filterTokens($post['subject'], $tokenfilter);
			$email_body = $tokens_obj->filterTokens($post['content'], $tokenfilter);
			

			//TODO-3164 Ancuta 08.09.2020
			$email_data = array();
			$email_data['additional_text'] = '<pre>'.$email_body.'</pre>';
			$email_body = "";//overwrite
			$email_body = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
			//--
			
			$mail = new Zend_Mail('UTF-8');
			$mail->setFrom($mail_FromEmail, $mail_FromName)
			->setReplyTo($mail_FromEmail, $mail_FromName)
			->addTo($vw_array['email'], $vw_array['nice_name'])
			->setSubject($email_subject);
			
			if($att && $att instanceof Zend_Mime_Part) {
				$mail->addAttachment($att);
			}
			
			if(Pms_CommonData::assertIsHtml($email_body)) {
				$mail->setBodyHtml($email_body);
			} else {
				$mail->setBodyText($email_body);
			}
	
			
			$mail->send($mail_transport);
		}
	}
	

}

?>