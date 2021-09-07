<?php
/**
 * 
 * @author claudiu  
 * Aug 7, 2017 
 *
 */
class Application_Form_Studypool extends Pms_Form
{
	
	private $triggerformid = 0; //use 0 if you want not to trigger
	
	private $triggerformname = "frmStudypool";  //define the name if you want to piggyback some triggers
		
	public function isValid($data)
	{
		if($data['status'] == "enabled"){
			return parent::isValid($data);
		} else {
			return true;
		}
	}
	
	public function create_settings_form( $options = array())
	{

		if(($default_docx_template = DocxTemplates::getDefaultTemplate($this->logininfo->clientid, StudypoolLetterSettings::$default_docx_template )) !== false) {
			$template_download_link = '<a href="ajax/docxtemplatedownload?id='. StudypoolLetterSettings::$default_docx_template  .'">' . $this->translate('Download .docx template') . " " . $default_docx_template['file_nicename'] . '</a>';				
		}

		$this->setMethod(self::METHOD_POST);
		
		$this->addElement('radio', 'status', array(
				'label'      => $this->translate('enable/disable module'),
				'separator'  => '&nbsp;',
				'required'   => true,
				'multiOptions'=>array(
			        'disabled'	=> $this->translate('module disabled'),
			        'enabled'	=> $this->translate('module enabled'),
			    ),
				'value' => 'disabled',
				'onchange' => 'status_changed(this)',
				
		));
		
		$this->addElement('text', 'survey_url', array(
				'label'      => $this->translate('Your studypool surveys url'),
				'required'   => true,
				'filters'    => array('StringTrim'),
				'validators' => array(
						'NotEmpty',
				),
				'placeholder' => 'Bitte wenden Sie sich an smart-Q für Ihren pers. Link',
				'value' => 'Bitte wenden Sie sich an smart-Q für Ihren pers. Link',
				'class' => 'wide_input',
		));
		
		$this->addElement('text', 'survey_when', array(
				'label'      => $this->translate('Ammount of days after the discharge'),
				'required'   => true,
				'filters'    => array('StringTrim'),
				'validators' => array(
						'NotEmpty',
						'Int'
				),
				'value' => '40',
				'class' => 'survey_when',
				'style' => 'width:60px',
				'data-attributes' => "required",
		));
		
		$this->addElement('text', 'email_subject', array(
				'label'      => $this->translate('email_subject'),
				'required'   => true,
				'filters'    => array('StringTrim'),
				'validators' => array(
						'NotEmpty',
				),
				'class' => 'wide_input',
				'value' => 'Konnten wir Ihnen helfen? - wir würden uns über Ihre Rückmeldung freuen',
		));
		
		$this->addElement('textarea', 'email_body', array(
				'label'      => $this->translate('email_content'),
				'required'   => true,
				'rows'       => '10',
				'filters'    => array('StringTrim'),
				'validators' => array(
						'NotEmpty',
				),
				'class' => 'wide_input',
				'value' => 'wir engagieren uns jeden Tag für unsere Patienten und bemühen uns alles richtig zu machen. Deshalb möchten wir auch wissen, wenn wir mal was nicht richtig machen oder wir freuen uns über Ihr Lob. Wir können nur besser werden, wenn Sie uns sagen was nicht so gut gelaufen ist. Mit diesem Fragebogen können Sie uns vollkommen anonym Rückmeldungen geben.

Wir würden uns aufrecht über Ihre Rückmeldung freuen.

Mit herzlichen Grüßen',
		));
		
		$this->addElement('hidden', 'template_id');
		
		$this->addElement('select', 'specified_user', array(
				'label'      => $this->translate('Selected user to receive the files'),
				'multiOptions'	=> $options['nice_name_multiselect'],
		));
		
		$this->addElement('note', 'template_download', array(
				'value'	=> $template_download_link,
		));

		
		$this->addElement('note', 'qqfileuploader', array(
				'label'	=> $this->translate('Upload new template'),
				'value'	=> '<div id="qq_file_uploader" class="qq_file_uploader"><noscript>' . $this->translate('Please enable JavaScript to use file uploader.') . '</noscript></div>',
		));
		
		$this->addDisplayGroup(
				array(
					'survey_url', 
					'survey_when',
					'email_subject',
					'email_body',	
					'template_id',
					'template_download',
					'specified_user',
					'qqfileuploader',	
				), 
				'form_settings',
				array(	
		));	

		$this->setDisplayGroupDecorators(array(
				'FormElements',
				'Fieldset',
				array('HtmlTag',array('tag'=>'div','style'=>'display:none', 'id'=>'form_settings'))
		));
		
		

		$this->addElement('submit', 'save', array(
				'label' => $this->translate('submit'),
// 				'onclick' => 'return checkclientchanged(\'studypool_form\');',	
		));
		
		foreach($this->getElements() as $element){
			$element->setAttrib('class', 'form-input' . ($element->getAttrib('class') == '' ? '' : ' ' . $element->getAttrib('class') )  );
		}

		
		/*
		$subform = new Zend_Form();
		$subform->addElement('textarea', 'email_body55', array(
				'label'      => 'email body77:',
				'required'   => true,
				'rows'       => '10',
				'filters'    => array('StringTrim'),
				'validators' => array(
						'NotEmpty',
				)
		));
		
		$this->addSubform($subform, 'base');
		*/
		
	}
	
	//save the settings form
	public function save( $post ) 
	{

		//save templates... start idea was for multiple templates, altered to work for single template only
		if( ! empty($post['attachments'])) {
						
			foreach( $post['attachments'] as $single_qquuid) 
			{
				$template_id = DocxTemplates::saveTemplate($post['clientid'] , 'studypool' , $single_qquuid['filepath'], $single_qquuid['filename']);
			}
			
			$post['template_id'] = $template_id;
		}	
		
		//save data formular
		$slsObj = new StudypoolLetterSettings();
		$last_id = $slsObj->set_new_record($post);

	}
	


	/**
	 * ISPC-2034
	 * this fn is triggered by the cronjob controller
	 * Aug 11, 2017 @claudiu 
	 * 
	 * @param int $clientid
	 */
	public function create_studypool_emails_letters( $clientid = 0 )
	{	
		//email letter settings
		$slsObj = new StudypoolLetterSettings();
		$letter_settings = $slsObj->getByClientid($clientid);
		if( empty($letter_settings) || $letter_settings['status'] != 'enabled') 
		{
			return; // no settings no fun
		}
	
		if( ! ((int)$letter_settings['survey_when'] > 0)) {
			return; //wrong survey_when.. i know how... why did you doit?
		}		
		
		$ipids_arr_with_pdf = array(); //ipids that have no valid quality contact_person
		$contact_with_email_arr = array(); // this will receive emails
		$patients_details_arr = array(); //this will have only pdfs
	
		$pm_obj = new PatientMaster();
		$discharged = $pm_obj->get_discharged_ipids($clientid , array('x days ago' => $letter_settings['survey_when']));
// 		$discharged = $pm_obj->get_discharged_ipids($clientid , array('last x days'=>6666)); // testmode to have many data
		
		if( ! empty($discharged))
		{

			$ipid_arr = array_column($discharged, 'ipid');
				
			//get all patients details.. we will paste this later to emails and pdfs
			$pm_obj = new PatientMaster();
			$patients_details_arr = $pm_obj->get_multiple_patients_details($ipid_arr);
			$pm_obj->beautifyName($patients_details_arr);
				
			//get contact persons that are checked as quality_control
			$cpm_obj = new ContactPersonMaster();
			$cpm_arr = $cpm_obj->get_QualityControlByIpids($ipid_arr);
				
			if( ! empty($cpm_arr)) {
				//validate email here because we need the ipids for pdf
				$validator = new Zend_Validate_EmailAddress();
	
				foreach($cpm_arr as $row) {
	
					if ($validator->isValid($row['cnt_email'] )) {
						// email ok
						$contact_with_email_arr[] = $row;
					} else {
						//invalid email, add this patient to receive pdf
						$ipids_arr_with_pdf[] = $row["ipid"];
					}
				}
	
				//add the ipids that have no quality contact_person
				$cpm_ipid_arr = array_column($contact_with_email_arr, 'ipid');
				$diff =  array_diff($ipid_arr, $cpm_ipid_arr);
				$ipids_arr_with_pdf = array_unique(array_merge($ipids_arr_with_pdf, $diff));
	
			} else {
				//all ipids can only receive pdfs
				$ipids_arr_with_pdf = array_column($discharged, 'ipid');
			}
				
		}
	
		//send email for $contact_with_email_arr
		if( ! empty($contact_with_email_arr))
		{
				
			//get all patient details
			$cpm_ipid_arr = array_column($cpm_arr, 'ipid');
				
			$email_data = array(
					'clientid'		=> $clientid,
					'sender' 		=> 0,
					'email_subject'	=> $letter_settings['email_subject'],
					'email_body'	=> $letter_settings['email_body'],
					'attachment'	=> null,
					'recipients'	=> $contact_with_email_arr,
					'patients'		=> $patients_details_arr,
					'letter_settings'	=> $letter_settings,
			);
			$emails_2_contacts_cnt = $this->_send_emails_2_contacts($email_data);
		}
	
		//create letter pdfs for $ipids_arr_with_pdf
		if( ! empty($ipids_arr_with_pdf) &&  ! empty($letter_settings['DocxTemplates']['id']))
		{
			//will use only one last contact
			$contact_arr = array();
			foreach($cpm_arr as $row){
				$contact_arr[$row['ipid']] = $row;
			}
			$template = $letter_settings['DocxTemplates'];
			$fullPath =  ! empty($template['fullPath']) ? $template['fullPath'] : DOCX_TEMPLATE_PATH . "/" . $template['clientid'] . "/" .$template['action'] . "/" .$template['file_name'];
			$file_nicename = $template['file_nicename'];
	
			$pdf_data = array(
					'clientid'		=> $clientid,
					'docx_template'	=> $fullPath,
					'recipients'	=> $ipids_arr_with_pdf,
					'patients'		=> $patients_details_arr,
					'letter_settings'=> $letter_settings,
					'contact_arr'	=> $contact_arr,
			);
	
			if( file_exists($fullPath))
			{
				$pdf_2_contacts_cnt = $this->_send_pdf_2_contacts($pdf_data);
			}
		}
		
		return array(
				"emails_2_contacts_cnt" => $emails_2_contacts_cnt , 
				"pdf_2_contacts_cnt" => $pdf_2_contacts_cnt
				
		);
	
	}
	

	private function _send_emails_2_contacts( $post ) 
	{
// 		$post['email_subject'] .= " claudiu was here";
// 		$post['email_body'] .= " claudiu was here";
		
		$clientid =  ! empty($post['clientid']) ? $post['clientid'] : $this->logininfo->clientid;
		$userid =  ! empty($post['userid']) ? $post['userid'] : $this->logininfo->userid;
	
		$batch_id = 0;
		
		$total_emails_sent = 0;
	
		$date_time = date("Y-m-d H:i:s",time());
	
		$att = false; // attachment file
	
		if( is_array($post['attachment']))
		{
			$att = new Zend_Mime_Part(file_get_contents($post['attachment']['filepath']));
			$att->type        = mime_content_type($post['attachment']['filepath']);
			$att->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
			$att->encoding    = Zend_Mime::ENCODING_BASE64;
			$att->filename    = $post['attachment']['filename'];
		}
	
		//get clisnt smtp settings
		$c_smpt_s = new ClientSMTPSettings();
		$smtp_settings = $c_smpt_s->get_mail_transport_cfg( $clientid);
		if( empty($smtp_settings['host'])){
			
			return false; // we have no smtp? why?
		}
		
		//init the smtp
		$mail_transport 	= new Zend_Mail_Transport_Smtp( $smtp_settings['host'], $smtp_settings['config'] );
		$mail_FromEmail		= $smtp_settings['sender_email'];
		$mail_FromName		= $smtp_settings['sender_name'];
		
		//get client details
		$c_client_obj = new Client();
		$clientid_details = $c_client_obj->getClientDataByid($clientid);
		$clientid_details = $clientid_details[0];
			
		//init the tokens filter
		$tokens_obj = new Pms_Tokens('Studypool');
		
		$batch_id = 0;
		foreach($post['recipients'] as $one_contact)
		{

			$receiver_email = $one_contact['cnt_email'];
			$receiver_nice_name = $one_contact["cnt_last_name"] . " " .$one_contact["cnt_first_name"];
			
			if( ! empty($receiver_email))
			{
				//save a log
				$email_log_arr = array(
						'sender' => $userid,
						'clientid' => $clientid,
						'recipient' => $one_contact['id'],
						'date' => $date_time,
						'email_subject' => $post['email_subject'],
						'email_body' => $post['email_body'],
						'batch_id' => $batch_id,
				);
				$email_log = new StudypoolEmailsLog();
				$email_log_id = $email_log->set_new_record($email_log_arr);
				
				if ($batch_id == 0) {
					//save this batch_id = self_id so we can group after
					$batch_id = $email_log_id;
					$email_log->set_new_record(array('batch_id' => $email_log_id));
				}
				
				//save the attachment on the patients's page ........

 

				$tokenfilter = array();
				$tokenfilter['client'] = $clientid_details;
				$tokenfilter['patient'] = $post['patients'][$one_contact['ipid']];
				$tokenfilter['contact_person'] = $one_contact;
				$tokenfilter['default_tokens']['survey_url'] = $post['letter_settings']['survey_url'];
				
				$email_subject = $tokens_obj->filterTokens($post['email_subject'], $tokenfilter);
				$email_body = $tokens_obj->filterTokens($post['email_body'], $tokenfilter);
					
				//TODO-3164 Ancuta 08.09.2020
				$email_data = array();
				$email_data['additional_text'] = '<pre>'.$email_body.'</pre>';
				$email_body = "";//overwrite
				$email_body = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
				//--
				$mail = new Zend_Mail('UTF-8');
				$mail->setFrom($mail_FromEmail, $mail_FromName)
				->setReplyTo($mail_FromEmail, $mail_FromName)
				->addTo($receiver_email, $receiver_nice_name)
				->setSubject($email_subject);
				 
				if ($att && $att instanceof Zend_Mime_Part) {
					$mail->addAttachment($att);
				}
				 
				if(Pms_CommonData::assertIsHtml($email_body)) {
					$mail->setBodyHtml($email_body);
				} else {
					$mail->setBodyText($email_body);
				}
				 
				$mail->send($mail_transport);
				
				$total_emails_sent++;
			}
		
		} //endforeach;
		
		return $total_emails_sent;
	}
	
	
	private function _send_pdf_2_contacts( $post )
	{
		
		$total_pdf_sent = 0;
		
		$clientid =  ! empty($post['clientid']) ? $post['clientid'] : $this->logininfo->clientid;
		$userid =  ! empty($post['userid']) ? $post['userid'] : $this->logininfo->userid;
		
		//get client details
		$c_client_obj = new Client();
		$clientid_details = $c_client_obj->getClientDataByid($clientid);
		$clientid_details = $clientid_details[0];
		
		$tokens_obj = new Pms_Tokens('Studypool');
		
		foreach($post['recipients'] as $one_ipid)
		{
			$tokenfilter = array();
			$tokenfilter['client'] = $clientid_details;
			$tokenfilter['patient'] = $post['patients'][$one_ipid];
			$tokenfilter['default_tokens']['survey_url'] = $post['letter_settings']['survey_url'];
			$tokenfilter['contact_person'] = $post['contact_arr'][$one_ipid];
			
			$tokens_array = $tokens_obj->filterTokensDocx($tokenfilter);
			//generate file and putit to ftp		
			$ftp_id = $this->_generate_file($post['docx_template'] , $tokens_array , $clientid_details);
			
			if($ftp_id) 
			{
				//file ok in ftp queue
				$ftp_file_name =  FtpPutQueue::get_file_name_by_id($ftp_id);
				
				//save in patient files
				$pfu_obj = new PatientFileUpload();
				$pfu_id = $pfu_obj->set_new_record(array(
						'title' => $this->translate('Studypool letter PDF file'),
						'file_name' => $ftp_file_name,
						'file_type' => 'pdf',
						'ipid' => $one_ipid,
						'tabname' => $this->triggerformname,
						'system_generated' => 1,
						
				));
				
				//piggyback	addInternalMessage
				$event = new Doctrine_Event($pfu_obj, Doctrine_Event::RECORD_SAVE);
				
				$users_and_groups_2_send =  explode("," , $post['letter_settings']['specified_user']);
				
				$file_download_link = '<a href="' . APP_BASE . 'stats/patientfileupload?doc_id='.$pfu_id.'">'.$this->translate('Studypool letter PDF file').'</a>';
				
				$gpost = array(
						"title" => $this->translate('Studypool user internal message title') ,
						
						"verlauf_entry"		=> $this->translate('Studypool user internal message text') 
						."\n\n"
						.$this->translate('Download PDF Letter:'). " ". $file_download_link
						."\n"
						.$this->translate('Your studypool surveys url') . " ". $post['letter_settings']['survey_url']
						."\n",
						
						"users_and_groups_2_send"	=> $users_and_groups_2_send,
				);
				$inputs = array();
				$trigger_addInternalMessage_obj = new application_Triggers_addInternalMessage();
				$trigger_addInternalMessage_obj->triggeraddInternalMessage($event, $inputs, $this->triggerformname, $this->triggerformid, 2, $gpost);
				
				$total_pdf_sent ++;
			}
			
		}
		
		return $total_pdf_sent;
	}
	
	
	private function _generate_file($template_path = false, $tokens = array(), $clientid_details)
	{
		if( ! file_exists($template_path)) {
			return;
		}

		$clientid =  $clientid_details['id'];
		$clientid_filepas =  $clientid_details['fileupoadpass'];
				
		$docx = new CreateDocxFromTemplate($template_path);
	
		if( ! empty($tokens))
		{

			//parse header
			$docx->replaceVariableByText($tokens, array('parseLineBreaks' => true, 'target' => 'header'));
			
			//parse footer
			$docx->replaceVariableByText($tokens, array('parseLineBreaks' => true, 'target' => 'footer'));
			
			//parse document
			$docx->replaceVariableByText($tokens, array('parseLineBreaks' => true));
			
			
		}

		//create temp folders
		$temp_folder_docx = Pms_CommonData::uniqfolder_v2( PDFDOCX_PATH , 'studypool_');
		$temp_folder_pdf = Pms_CommonData::uniqfolder_v2( PDF_PATH , 'studypool_');
		
		$filename = $temp_folder_docx . "/studypool.docx";
		
		$other_filename = $temp_folder_pdf ."/studypool.pdf";											
	
		$docx->createDocx($filename);
		
		$docx->enableCompatibilityMode();
		
		$docx->transformDocument($filename, $other_filename);
		
		
		$ftp_put_queue = Pms_CommonData :: ftp_put_queue($other_filename , 'uploads', null, false, $clientid, $clientid_filepas);
		
		unlink($filename);
		
		return $ftp_put_queue;
		
//		unlink($filename);
	}
	
	
	
	
}

