<?php

require_once("Pms/Form.php");

class Application_Form_Messages extends Pms_Form
{
	public function validate($post)
	{

		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(count($post['userid'])<1){
			$this->error_message['userid']="<br>".$Tr->translate("selectatleastoneuser"); $error=1;
		}
		if(!$val->isstring($post['title'])){
			$this->error_message['subject']="<br>".$Tr->translate('entersubject'); $error=2;
		}
		if(!$val->isstring($post['content'])){
			$this->error_message['message']="<br>".$Tr->translate('entermessage'); $error=2;
		}


		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function validatefolder($post)
	{

		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(!$val->isstring($post['folder_name'])){
			$this->error_message['folder_name']=$Tr->translate("enterfoldername"); $error=2;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function validatecheckbox($post)
	{

		$error=0;
		$Tr = new Zend_View_Helper_Translate();
		$val = new Pms_Validation();
		if(count($post['msg_id'])<1){
			$this->error_message['msg_id']=$this->view->translate('selectatleastone'); $error=2;
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();

		$sender = Doctrine::getTable('User')->find($logininfo->userid);
		$senderarray = $sender->toArray();

		if(count($senderarray)>0 && !empty($senderarray['last_name']))
		{
			$the_sender = $senderarray['first_name'].' '.$senderarray['last_name'];
		}
		

		// ###################################
		// ISPC-1600
		// ###################################
		$email_subject = $Tr->translate('mail_subject_action_send_message').' - '.$the_sender.', '.date('d.m.Y H:i');
	    //ISPC-2155
	    if ($post['priority'] != "none") {
	        $email_subject = $this->translate('priority_subject_label').': '.$this->translate('priority_'.$post['priority']).' | ' . $email_subject ;
	    }
	    
		$email_text = "";
		$email_text .= $Tr->translate('youhavenewmailinyourispcinbox');
		// link to ISPC
		//$email_text .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>";
		// ISPC-2475 @Lore 31.10.2019
		$email_text .= $Tr->translate('system_wide_email_text_login');
		// client details
		$client_details_array = Client::getClientDataByid($logininfo->clientid);
		if(!empty($client_details_array)){
		    $client_details = $client_details_array[0];
		}
		$client_details_string = "<br/>";
		$client_details_string  .= "<br/> ".$client_details['team_name'];
		$client_details_string  .= "<br/> ".$client_details['street1'];
		$client_details_string  .= "<br/> ".$client_details['postcode']." ".$client_details['city'];
		$client_details_string  .= "<br/> ".$client_details['emailid'];
		$email_text .= $client_details_string;
		
		//TODO-3164 Ancuta 08.09.2020
		$email_data = array();
		$email_data['client_info'] = $client_details_string;
		$email_text = "";//overwrite
		$email_text = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
		//--
		
		foreach($post['userid'] as $key=>$val)
		{

			$mail = new Messages();
			$mail->sender = $logininfo->userid;
			$mail->clientid = $logininfo->clientid;
			$mail->recipient = $val;
			$mail->msg_date = date("Y-m-d H:i:s",time());
			$mail->title = Pms_CommonData::aesEncrypt(addslashes($post['title']));
			$mail->content = Pms_CommonData::aesEncrypt(addslashes($post['content']));
			$mail->recipients = implode(',', $post['userid']);
			$mail->priority = $post['priority'];
			$mail->create_date = date("Y-m-d",time());
			$mail->create_user = $logininfo->userid;
			$mail->save();

			if($mail->id>0)
			{
				$user = Doctrine::getTable('User')->find($val);
				$userarray = $user->toArray();

				//TODO-2857 Lore 28.01.2020
				//if(count($userarray)>0 && !empty($userarray['emailid']))
				if(count($userarray)>0 && !empty($userarray['emailid']) && $userarray['notification'] == '0' )
				{
				    //ISPC-2410 - Ancuta 06.1.2019
			    	$this->_mail_forceDefaultSMTP = false;
				    $this->sendEmail($userarray['emailid'], $email_subject, $email_text);
                    
				    /*
				 	$mail_transport = new Zend_Mail_Transport_Smtp(ISPC_SMTP_SERVER, Zend_Registry::get('mail_transport_cfg'));
					$mail = new Zend_Mail('UTF-8');
// 					$mail->setBodyText($Tr->translate('youhavenewmailinyourispcinbox'))
					$mail->setBodyHtml($email_text)
					->setFrom(ISPC_SENDER, ISPC_SENDERNAME)
					->addTo($userarray['emailid'], $userarray['last_name'].' '.$userarray['first_name'])
					//->addTo($userarray['emailid'])
					//						  ->setSubject($Tr->translate('youhavenewmailinispc'))
// 					->setSubject($Tr->translate('youhavenewmailinispc').'- '.$the_sender.', '.date('d.m.Y H:i'))
					->setSubject($email_subject)
					->send($mail_transport);
                    */

				}
			}
		}

	}

	public function InsertReplyData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		// client details
		$client_details_array = Client::getClientDataByid($this->logininfo->clientid);
		if(!empty($client_details_array)){
		    $client_details = $client_details_array[0];
		}
		$client_details_string = "<br/>";
		$client_details_string  .= "<br/> ".$client_details['team_name'];
		$client_details_string  .= "<br/> ".$client_details['street1'];
		$client_details_string  .= "<br/> ".$client_details['postcode']." ".$client_details['city'];
		$client_details_string  .= "<br/> ".$client_details['emailid'];
		
		$email_subject = $this->translate('You have a reply to a ISPC internal message') . ' - ' . $this->logininfo->loguname . ', '.date('d.m.Y H:i');
		//ISPC-2155
		if ($post['priority'] != "none") {
		    $email_subject = $this->translate('priority_subject_label').': '.$this->translate('priority_'.$post['priority']).' | ' . $email_subject ;
		}
		
		$email_TextOrHtml = "";
		$email_TextOrHtml .= $this->translate('youhavenewmailinyourispcinbox');
		//$email_TextOrHtml .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>"; // link to ISPC
		// ISPC-2475 @Lore 31.10.2019
		$email_TextOrHtml .= $this->translate('system_wide_email_text_login');
		$email_TextOrHtml .= $client_details_string;
		
		
		//TODO-3164 Ancuta 08.09.2020
		$email_data = array();
		$email_data['client_info'] = $client_details_string;
		$email_TextOrHtml = "";//overwrite
		$email_TextOrHtml = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
		//--
		
		foreach($post['userid'] as $key=>$val)
		{
			$mail = new Messages();
			$mail->sender = $logininfo->userid;
			$mail->clientid = $logininfo->clientid;
			$mail->recipient = $val;
			$mail->msg_date = date("Y-m-d H:i:s",time());
			$mail->replied_msg = $_GET['id'];
			$mail->title = Pms_CommonData::aesEncrypt(addslashes($post['title']));
			$mail->content = Pms_CommonData::aesEncrypt(addslashes($post['content']));
			$mail->recipients = implode(',', $post['userid']);
			$mail->priority = $post['priority'];
			$mail->create_date = date("Y-m-d",time());
			$mail->create_user = $logininfo->userid;
			$mail->save();
			
			if ($mail->id) {
			    //TODO-1266
			    
			    $userarray = Doctrine::getTable('User')->findOneBy('id', $val, Doctrine_Core::HYDRATE_ARRAY);
			    if(count($userarray) > 0 && ! empty($userarray['emailid']))
			    {
			        $this->sendEmail($userarray['emailid'], $email_subject, $email_TextOrHtml);
			    }
			}
		}

	}

	public function InsertFolderData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$folder = new MessageFolder();
		$folder->userid = $logininfo->userid;
		if($logininfo->usertype!='SA')
		{
			$folder->clientid = $logininfo->clientid;
		}
		$folder->folder_name = Pms_CommonData::aesEncrypt($post['folder_name']);
		$folder->parentid = $post['parentid'];
		$folder->save();

	}

	public function EditFolderData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');

		$folder = Doctrine::getTable('MessageFolder')->find($_GET['id']);
		$folder->userid = $logininfo->userid;
		$folder->folder_name = Pms_CommonData::aesEncrypt($post['folder_name']);
		$folder->parentid = $post['parentid'];
		$folder->save();

	}
	
}

?>