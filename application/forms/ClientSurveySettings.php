<?php
/**
 * 
 * @author carmen 
 * Aug 21, 2019
 * ISPC-2411 	    // Maria:: Migration ISPC to CISPC 08.08.2020
 * Edited by Ancuta 
 *
 */
class Application_Form_ClientSurveySettings extends Pms_Form
{
	
	private $triggerformid = 0; //use 0 if you want not to trigger
	
	private $triggerformname = "frmSurvey";  //define the name if you want to piggyback some triggers
		
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
		
		$this->addElement('text', 'email_subject', array(
				'label'      => $this->translate('email_subject'),
				'required'   => true,
				'filters'    => array('StringTrim'),
				'validators' => array(
						'NotEmpty',
				),
				'class' => 'wide_input',
				'value' => ' ',
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
				'value' => ' ',
		));
		
// 		$this->addElement('select', 'specified_user', array(
// 				'label'      => $this->translate('Selected user to receive the files'),
// 				'multiOptions'	=> $options['nice_name_multiselect'],
// 		));
		
		$this->addDisplayGroup(
				array(
					'email_subject',
					'email_body',
					'specified_user',	
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
		
	}
	
	//save the settings form
	public function save( $post ) 
	{
		//save data formular
		$slsObj = new SurveyEmailSettings();
		
		$last_id = $slsObj->set_new_record($post);

	}
	


	/**
	 * ISPC-2034
	 * this fn is triggered by the cronjob controller
	 * @param int $clientid 
	 */
	public function create_pateint_survey_email( $clientid = 0,$survey_id = 0 ,$ipid = 0)
	{
 
	    if(empty($survey_id)){
	        return; // no survey to send
	        
	    }
	    if(empty($ipid)){
	        return; // no survey to send
	        
	    }
		//ipos email settings
		$res = SurveyEmailSettingsTable::findByClientid($clientid);
		$email_settings = $res[0];
		if( empty($email_settings) || $email_settings['status'] != 'enabled')
		{
			return; // no settings no fun
		}

		$ipids_arr_with_email = array();
	
		$pm_obj = new PatientMaster();
		// get all patients with survey activated - and that need to receive survey today.
		$survey_activated = $pm_obj->get_today_surveys2patient($clientid,$ipid);
 
// 		echo "<pre/>";
// 		print_R("---------------------------------------");
// 		print_R("Client:".$clientid);
// 		print_R($survey_activated);
// 		print_R("---------------------------------------");
		
		
		if( empty($survey_activated))
		{
		    return; // No active patients with ipost activated
		}

		$ipids_arr = array();
		$pateint_ids = array();
		
		$ipids_arr = array_column($survey_activated, 'ipid');
		$pateint_ids = array_column($survey_activated, 'patient_id');
		
		if(empty($ipids_arr) || empty($pateint_ids) ){
		    return; // no patientes // double check
		}

		
		//get all patients details.. we will paste this later to emails
		$pm_obj = new PatientMaster();
		$patients_details_arr = array();
		$patients_details_arr = $pm_obj->get_multiple_patients_details($ipids_arr);
		PatientMaster::beautifyName($patients_details_arr);
		
		
		// get all contact persons details
		$cntp = new ContactPersonMaster();
		$cnt_persons = array();
		$cnt_persons = $cntp->get_contact_persons_by_ipids($ipids_arr);
		foreach($cnt_persons as &$vcp)
		{
		    ContactPersonMaster::beautifyName( $vcp);
		}
		
		
		
		// Check if there is data in patient2chain
		// if there is no data - or all are closed ( have both start and END - Start Survey
		// if there is an entry with end = 0  
		$patient2chain_data = Doctrine_Query::create()
		->select('* ')
		->from('SurveyPatient2chain')
		->whereIn('patient',$pateint_ids)
		->orderBy('start ASC')
		->fetchArray();
		
		
	
		$pateint2chain_info = array(); 
		$surveys_not_completed	 = array(); 
		foreach($patient2chain_data as $k=>$s){
		    $pateint2chain_info[$s['patient']][] = $s; 
		    if($s['end'] == "0000-00-00 00:00:00"){
                $surveys_not_completed	[$s['patient']] = $s; 	          
		    }
		}

		$mypain = new Pms_MyPain();
		
		$patient_link = array();
 
		foreach($survey_activated as $k_ipid=>$survey_data){
		    
		    $already_sent = array();
		    $already_sent = SurveyScheduledHistoryTable::findByPatientAndSurvey($survey_data['patient_id'],$survey_id);
 
		    // Check if mail was sent today for pateint
		    if( empty($already_sent) || date('Y-m-d',strtotime($already_sent['date'])) < date("Y-m-d") ){
		        
                if (   empty($pateint2chain_info[$survey_data['patient_id']]) 
                    || empty($surveys_not_completed[$survey_data['patient_id']] )
                    ) {
                    
                    $pateint2chain_entry = new SurveyPatient2chain();
                    $pateint2chain_entry->patient = $survey_data['patient_id'];
                    $pateint2chain_entry->survey_id = $survey_id; // get from config
                    $pateint2chain_entry->start = date('Y-m-d H:i:s');
                    $pateint2chain_entry->save();
                    $patient_chain_id = $pateint2chain_entry->id;
                    
                    if ($patient_chain_id) {
                        // start survey
                        $start_survey_data = $mypain->start_survey($survey_data['patient_id'], $patient_chain_id);
                        if (! empty($start_survey_data)) {
                            if (! empty($start_survey_data['response_data']) && ! empty($start_survey_data['response_data']['link'])) {
                                $survey_activated[$k_ipid]['link'] = $start_survey_data['response_data']['link'];
                            }
                        } 
                    }
                } else {
                    // check if last entry has end = 0000-00-00
                    // if so get patient chain id - reset survey
                    
                    // sort pateint 2 chain by start date - and get last
                    usort($pateint2chain_info[$survey_data['patient_id']], array( new Pms_Sorter('start'), "_date_compare"));
                    $last_entry = array();
                    $last_entry = end($pateint2chain_info[$survey_data['patient_id']]);
                    $last_pateint2chainid = 0;
                    if ($last_entry['end'] == "0000-00-00 00:00:00") {
                        $last_pateint2chainid = $last_entry['id'];
                        
                        // First reset survey
                        $reset_survey_data = $mypain->reset_survey($survey_data['patient_id']);
                        // Delete entry from SurveyPatient2chain - make way for a new one
                        $started_survey = Doctrine::getTable('SurveyPatient2chain')->find($last_pateint2chainid);
                        if($started_survey){
                            $started_survey->delete();
                        }
                        
                        
                        // start survey
                        $pateint2chain_rentry = new SurveyPatient2chain();
                        $pateint2chain_rentry->patient = $survey_data['patient_id'];
                        $pateint2chain_rentry->survey_id = $survey_id; // get from config
                        $pateint2chain_rentry->start = date('Y-m-d H:i:s');
                        $pateint2chain_rentry->save();
                        $patient_chain_id = $pateint2chain_rentry->id;
                        
                        if ($patient_chain_id) {
                            $start_survey_data = $mypain->start_survey($survey_data['patient_id'], $patient_chain_id);
                            if (! empty($start_survey_data)) {
                                if (! empty($start_survey_data['response_data']) && ! empty($start_survey_data['response_data']['link'])) {
                                    $survey_activated[$k_ipid]['link'] = $start_survey_data['response_data']['link'];
                                }
                            }
                        }
    
                    } 
                }
            } else {
         
                //echo "sent today for ".$survey_data['patient_id'];
            }
        }
        
        foreach($survey_activated as $ky_ipid=>$patient_data){
            if (! empty($patient_data['link']) && $patient_data['PatientSurveySettings']['parent_table'] && $patient_data['PatientSurveySettings']['table_id'] != '0') {
                
                $patient_id = $patient_data['patient_id'];
                

                $receiver_id = $patient_data['PatientSurveySettings']['table_id'];
                $ipid = $patient_data['ipid'];
                
                $receiver_table = $patient_data['PatientSurveySettings']['parent_table'];
                if ($receiver_table == 'PatientMaster') {
                    $receiver = $patients_details_arr[$ipid];
                    $email = $receiver['email'];
                    $contacts = $cnt_persons[$ipid];
                    $patients = null;
                } else {
                    $contacts = $cnt_persons[$ipid];
                    $receiver = $contacts[array_search($receiver_id, array_column($contacts, 'id'))];
                    $email = $receiver['cnt_email'];
                    $patients = $patients_details_arr[$ipid];
                }

                // validate email
                $validator = new Zend_Validate_EmailAddress();
                if ($validator->isValid($email)) {
                    // email ok
                    
                    $ipids_arr_with_email[] = array(
                        'email' => $email,
                        'receiver_table' => $receiver_table,
                        'receiver_id' => $receiver_id,
                        'receiver' => $receiver,
                        'patients' => $patients,
                        'contacts' => $contacts,
                        'patient_id' => $patient_id,
                        'survey_link' => $patient_data['link'],
                        'survey_id' => $survey_id
                    );
                }
            }
        }
	 
		//send email 
		if( ! empty($ipids_arr_with_email))
		{
			$email_data = array(
					'clientid'		=> $clientid,
					'sender' 		=> 0,
					'email_subject'	=> $email_settings['email_subject'],
					'email_body'	=> $email_settings['email_body'],
					'attachment'	=> null,
					'recipients'	=> $ipids_arr_with_email,
					'email_settings'	=> $email_settings,
			);
			
			$emails_2_recipients_cnt = $this->_send_survey_link_emails_2_recipients($email_data);
		}
	
		return array(
				"emails_2_recipients_cnt" => $emails_2_recipients_cnt ,
	
		);
	}	

	/**
	 * ISPC-2034
	 * this fn is triggered by the cronjob controller
	 * Aug 11, 2017 @claudiu 
	 * Aug 21, 2019 modified by carmen
	 * @param int $clientid 
	 */
	public function create_survey_emails( $clientid = 0,$survey_id = 0 )
	{
	    if(empty($survey_id)){
	        return; // no survey to send
	        
	    }
		//ipos email settings
		$res = SurveyEmailSettingsTable::findByClientid($clientid);
		$email_settings = $res[0];
		if( empty($email_settings) || $email_settings['status'] != 'enabled')
		{
			return; // no settings no fun
		}

		$ipids_arr_with_email = array();
	
		$pm_obj = new PatientMaster();
		// get all patients with survey activated - and that need to receive survey today.
		$survey_activated = $pm_obj->get_today_surveys_patients($clientid);
 
// 		echo "<pre/>";
// 		print_R("---------------------------------------");
// 		print_R("Client:".$clientid);
// 		print_R($survey_activated);
// 		print_R("---------------------------------------");
		
		
		if( empty($survey_activated))
		{
		    return; // No active patients with ipost activated
		}

		$ipids_arr = array();
		$pateint_ids = array();
		
		$ipids_arr = array_column($survey_activated, 'ipid');
		$pateint_ids = array_column($survey_activated, 'patient_id');
		
		if(empty($ipids_arr) || empty($pateint_ids) ){
		    return; // no patientes // double check
		}

		
		//get all patients details.. we will paste this later to emails
		$pm_obj = new PatientMaster();
		$patients_details_arr = array();
		$patients_details_arr = $pm_obj->get_multiple_patients_details($ipids_arr);
		PatientMaster::beautifyName($patients_details_arr);
		
		
		// get all contact persons details
		$cntp = new ContactPersonMaster();
		$cnt_persons = array();
		$cnt_persons = $cntp->get_contact_persons_by_ipids($ipids_arr);
		foreach($cnt_persons as &$vcp)
		{
		    ContactPersonMaster::beautifyName( $vcp);
		}
		
		
		
		// Check if there is data in patient2chain
		// if there is no data - or all are closed ( have both start and END - Start Survey
		// if there is an entry with end = 0  
		$patient2chain_data = Doctrine_Query::create()
		->select('* ')
		->from('SurveyPatient2chain')
		->whereIn('patient',$pateint_ids)
		->orderBy('start ASC')
		->fetchArray();
		
		
	
		$pateint2chain_info = array(); 
		$surveys_not_completed	 = array(); 
		foreach($patient2chain_data as $k=>$s){
		    $pateint2chain_info[$s['patient']][] = $s; 
		    if($s['end'] == "0000-00-00 00:00:00"){
                $surveys_not_completed	[$s['patient']] = $s; 	          
		    }
		}

		$mypain = new Pms_MyPain();
		
		$patient_link = array();
 
		foreach($survey_activated as $k_ipid=>$survey_data){
		    
		    $already_sent = array();
		    $already_sent = SurveyScheduledHistoryTable::findByPatientAndSurvey($survey_data['patient_id'],$survey_id);
 
		    // Check if mail was sent today for pateint
		    if( empty($already_sent) || date('Y-m-d',strtotime($already_sent['date'])) < date("Y-m-d") ){
		        
                if (   empty($pateint2chain_info[$survey_data['patient_id']]) 
                    || empty($surveys_not_completed[$survey_data['patient_id']] )
                    ) {
                    
                    $pateint2chain_entry = new SurveyPatient2chain();
                    $pateint2chain_entry->patient = $survey_data['patient_id'];
                    $pateint2chain_entry->survey_id = $survey_id; // get from config
                    $pateint2chain_entry->start = date('Y-m-d H:i:s');
                    $pateint2chain_entry->save();
                    $patient_chain_id = $pateint2chain_entry->id;
                    
                    if ($patient_chain_id) {
                        // start survey
                        $start_survey_data = $mypain->start_survey($survey_data['patient_id'], $patient_chain_id);
                        if (! empty($start_survey_data)) {
                            if (! empty($start_survey_data['response_data']) && ! empty($start_survey_data['response_data']['link'])) {
                                $survey_activated[$k_ipid]['link'] = $start_survey_data['response_data']['link'];
                            }
                        } 
                    }
                } else {
                    // check if last entry has end = 0000-00-00
                    // if so get patient chain id - reset survey
                    
                    // sort pateint 2 chain by start date - and get last
                    usort($pateint2chain_info[$survey_data['patient_id']], array( new Pms_Sorter('start'), "_date_compare"));
                    $last_entry = array();
                    $last_entry = end($pateint2chain_info[$survey_data['patient_id']]);
                    $last_pateint2chainid = 0;
                    if ($last_entry['end'] == "0000-00-00 00:00:00") {
                        $last_pateint2chainid = $last_entry['id'];
                        
                        // First reset survey
                        $reset_survey_data = $mypain->reset_survey($survey_data['patient_id']);
                        // Delete entry from SurveyPatient2chain - make way for a new one
                        $started_survey = Doctrine::getTable('SurveyPatient2chain')->find($last_pateint2chainid);
                        if($started_survey){
                            $started_survey->delete();
                        }
                        
                        
                        // start survey
                        $pateint2chain_rentry = new SurveyPatient2chain();
                        $pateint2chain_rentry->patient = $survey_data['patient_id'];
                        $pateint2chain_rentry->survey_id = $survey_id; // get from config
                        $pateint2chain_rentry->start = date('Y-m-d H:i:s');
                        $pateint2chain_rentry->save();
                        $patient_chain_id = $pateint2chain_rentry->id;
                        
                        if ($patient_chain_id) {
                            $start_survey_data = $mypain->start_survey($survey_data['patient_id'], $patient_chain_id);
                            if (! empty($start_survey_data)) {
                                if (! empty($start_survey_data['response_data']) && ! empty($start_survey_data['response_data']['link'])) {
                                    $survey_activated[$k_ipid]['link'] = $start_survey_data['response_data']['link'];
                                }
                            }
                        }
    
                    } 
                }
            } else {
         
                //echo "sent today for ".$survey_data['patient_id'];
            }
        }
        
        foreach($survey_activated as $ky_ipid=>$patient_data){
            if (! empty($patient_data['link']) && $patient_data['PatientSurveySettings']['parent_table'] && $patient_data['PatientSurveySettings']['table_id'] != '0') {
                
                $patient_id = $patient_data['patient_id'];
                

                $receiver_id = $patient_data['PatientSurveySettings']['table_id'];
                $ipid = $patient_data['ipid'];
                
                $receiver_table = $patient_data['PatientSurveySettings']['parent_table'];
                if ($receiver_table == 'PatientMaster') {
                    $receiver = $patients_details_arr[$ipid];
                    $email = $receiver['email'];
                    $contacts = $cnt_persons[$ipid];
                    $patients = null;
                } else {
                    $contacts = $cnt_persons[$ipid];
                    $receiver = $contacts[array_search($receiver_id, array_column($contacts, 'id'))];
                    $email = $receiver['cnt_email'];
                    $patients = $patients_details_arr[$ipid];
                }

                // validate email
                $validator = new Zend_Validate_EmailAddress();
                if ($validator->isValid($email)) {
                    // email ok
                    
                    $ipids_arr_with_email[] = array(
                        'email' => $email,
                        'receiver_table' => $receiver_table,
                        'receiver_id' => $receiver_id,
                        'receiver' => $receiver,
                        'patients' => $patients,
                        'contacts' => $contacts,
                        'patient_id' => $patient_id,
                        'survey_link' => $patient_data['link'],
                        'survey_id' => $survey_id
                    );
                }
            }
        }
	 
		//send email 
		if( ! empty($ipids_arr_with_email))
		{
			$email_data = array(
					'clientid'		=> $clientid,
					'sender' 		=> 0,
					'email_subject'	=> $email_settings['email_subject'],
					'email_body'	=> $email_settings['email_body'],
					'attachment'	=> null,
					'recipients'	=> $ipids_arr_with_email,
					'email_settings'	=> $email_settings,
			);
			
			$emails_2_recipients_cnt = $this->_send_survey_link_emails_2_recipients($email_data);
		}
	
		return array(
				"emails_2_recipients_cnt" => $emails_2_recipients_cnt ,
	
		);
	}	

	
	private function _send_survey_link_emails_2_recipients( $post ) 
	{	
		$clientid =  ! empty($post['clientid']) ? $post['clientid'] : $this->logininfo->clientid;
		$userid =  ! empty($post['userid']) ? $post['userid'] : $this->logininfo->userid;
		
		$batch_id = 0;
		$total_emails_sent = 0;
	
		$date_time = date("Y-m-d H:i:s",time());
		//get client smtp settings
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
		$tokens_obj = new Pms_Tokens('Survey');

		$batch_id = 0;
 
		$total_emails_sent = 0;
		
		$date_time = date("Y-m-d H:i:s",time());
		foreach($post['recipients'] as $one_contact)
		{
			$receiver_email = $one_contact['email'];
			$receiver_nice_name = $one_contact["receiver"]["nice_name"];
			
			if( ! empty($receiver_email))
			{
			    //save a log
			    /*
			    $email_log_arr = array(
			        'sender' => $userid,
			        'clientid' => $clientid,
			        'recipient' => $one_contact['receiver']['id'],
			        'parent_table' => $one_contact['receiver_table'],
			        'date' => $date_time,
			        'email_subject' => $email_subject.'['.$receiver_email.']',
			        'email_body' => $email_body,
			        'batch_id' => $batch_id,
			    );
			    
			    $email_log = new SurveyEmailsLog();
			    $email_log_id = $email_log->set_new_record($email_log_arr);
			    if ($batch_id == 0) {
			        //save this batch_id = self_id so we can group after
			        $batch_id = $email_log_id;
			        $email_log->set_new_record(array('batch_id' => $email_log_id));
			    }
			    
			    //save a survey_log
			    $survey_log_arr = array(
			        'patient' => $one_contact['patient_id'],
			        'survey_id' => $one_contact['survey_id'],
			        'date' => $date_time,
			    );
			    $survey_schedule= new SurveyScheduledHistory();
			    $survey_schedule->set_new_record($survey_log_arr);
			    */
			    
			    
			    
			    
			    // Replace Tokens
			    // Send emails
				$tokenfilter = array();
				$tokenfilter['client']['client_address'] = '';
				$tokenfilter['client'] = $clientid_details;
				//TODO-3540 Ancuta 09.11.2020
				//$tokenfilter['default_tokens']['survey_link'] = $one_contact['survey_link'];
				$tokenfilter['default_tokens']['survey_link'] = '<a href="'.$one_contact['survey_link'].'">'.$one_contact['survey_link'].'</a>';
				//-- 
				$tokenfilter['default_tokens']['aktuelles_datum'] = date("d.m.Y");
				$tokenfilter['default_tokens']['default_current_date'] = date("d.m.Y");
				
				
				$tokenfilter['contact_vorname'] = '';
				$tokenfilter['contact_nachname']= '';
				
				$tokenfilter['contact_person']['cnt_first_name'] = '';
				$tokenfilter['contact_person']['cnt_last_name'] = '';
				$tokenfilter['contact_person']['cnt_street1'] = '';
				$tokenfilter['contact_person']['cnt_zip'] = '';
				$tokenfilter['contact_person']['cnt_city'] = '';
				$tokenfilter['contact_person']['cnt_phone'] = '';
				$tokenfilter['contact_person']['cnt_mobile'] = '';
				$tokenfilter['contact_person']['cnt_familydegree'] = '';
				
			    if($one_contact['receiver_table'] == 'ContactPersonMaster')
				{
					$tokenfilter['contact_person'] = $one_contact['receiver'];
					$tokenfilter['patient'] = $one_contact['patients'];
				}
				else 
				{
					$tokenfilter['contact_person'] = "";
					$tokenfilter['patient'] = $one_contact['receiver'];
				}
				
				if(!empty($one_contact['contacts'][0])){
    				$tokenfilter['contact_person'] = $one_contact['contacts'][0];
				}
				
				
				
				//IPSC-2411 13.12.2019 Ancuta
				/* 
				1) as you can select the patient OR a contact person as rcipient for an IPOS email we need tokens which adress the RECIPINET of the email
				2) please create a token $Ipos_recipient_firstname$ for the FIRSTNAME of the selected IPOS reicipient
				3) please create a token $Ipos_recipient_surname$ for the SURNAME of the selected IPOS reicipient
				 */
				if($one_contact['receiver_table'] == 'PatientMaster'){
				    $tokenfilter['recipient']['Ipos_recipient_firstname'] = $one_contact["receiver"]["first_name"];
				    $tokenfilter['recipient']['Ipos_recipient_surname'] =  $one_contact["receiver"]["last_name"];
				} elseif($one_contact['receiver_table'] == 'ContactPersonMaster'){
				    $tokenfilter['recipient']['Ipos_recipient_firstname'] = $one_contact["receiver"]["cnt_first_name"];
				    $tokenfilter['recipient']['Ipos_recipient_surname'] =  $one_contact["receiver"]["cnt_last_name"];
				}
				// --
				
				$email_subject = $tokens_obj->filterTokens($post['email_subject'], $tokenfilter);
				$email_body = $tokens_obj->filterTokens($post['email_body'], $tokenfilter);
				
				//TODO-3164 Ancuta 08.09.2020
				$email_data = array();
				$email_data['additional_text'] = '<pre>'.$email_body.'</pre>';
				$email_body = "";//overwrite
				$email_body = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
				//--
				
				// ------------
				// SAVE Email LOG
				// ------------
				
				$email_log_arr = array(
				    'sender' => $userid,
				    'clientid' => $clientid,
				    'recipient' => $one_contact['receiver']['id'],
				    'parent_table' => $one_contact['receiver_table'],
				    'date' => $date_time,
				    'email_subject' => $email_subject.'['.$receiver_email.']',
				    'email_body' => $email_body,
				    'batch_id' => $batch_id,
				);
				 
				$email_log = new SurveyEmailsLog();
				$email_log_id = $email_log->set_new_record($email_log_arr);
				if ($batch_id == 0) {
				    //save this batch_id = self_id so we can group after
				    $batch_id = $email_log_id;
				    $email_log->set_new_record(array('batch_id' => $email_log_id));
				}

				//-----------------
				// SAVE a survey_log
				//-----------------
				$survey_log_arr = array(
				    'patient' => $one_contact['patient_id'],
				    'survey_id' => $one_contact['survey_id'],
				    'date' => $date_time,
				);
				$survey_schedule= new SurveyScheduledHistory();
				$survey_schedule->set_new_record($survey_log_arr);
				 
				 
				
				
				
				
				//-----------
				// SEND EMAIL
				//-----------
				$mail = new Zend_Mail('UTF-8');
				$mail->setFrom($mail_FromEmail, $mail_FromName)
				->setReplyTo($mail_FromEmail, $mail_FromName)
				->addTo($receiver_email, $receiver_nice_name)
				->setSubject($email_subject);
				 
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
	
	
	/**
	 * Fn edited- get survey results (ANcuta 16.12.2019)
	 * @param number $clientid
	 * @param number $survey_id
	 * @return void|number
	 */
	
	
    public function _fetch_survey_data( $clientid = 0,$survey_id = 0 )
	{
	    if(empty($survey_id)){
	        return; // no survey to send
	    }
 
		// get all from patient2chain with end 0000-00-00
         $patient2chain_data = Doctrine_Query::create()
        ->select('*')
        ->from('SurveyPatient2chain')
        ->where('end ="0000-00-00 00:00:00" ')
        ->fetchArray();		
   
        $score_retrived_for = array();
        $mypain = new Pms_MyPain();
        $result = array();
        $survey_data = array();
        foreach($patient2chain_data as $k=>$ptc){
            $patient_id = $ptc['patient'];
            $patient_chain_id = $ptc['id'];
            $result[$patient_id] = $mypain->get_scores_data($patient_id, $patient_chain_id);
    
            // Get all survey data  [16.12.2019]
            $survey_data[$patient_id] = $mypain->get_survey_data($patient_id, $patient_chain_id);

            // INSERT SURVEY RESULTS DATA
            $existing_survey_result_Data = array();
            $existing_survey_result_Data = SurveyResultsTable::findByPatient2chain($patient_chain_id);
            if (! empty($existing_survey_result_Data)) {
                $results_rows_ids = array_column($existing_survey_result_Data, 'id');
                
                if (! empty($results_rows_ids)) {
                    $delete_existin_scores = Doctrine_Query::create()->update('SurveyResults')
                    ->set('isdelete', 1)
                    ->set('delete_date', '"' . date("Y-m-d H:i:s", time()) . '"')
                    ->andWhere("survey_took =?", $patient_chain_id)
                    ->andWhereIn("id", $results_rows_ids);
                    $delete_existin_scores->execute();
                }
            }
            
            //insert survey data

            if (! empty($survey_data[$patient_id]['response_data'])) {
                $SurveyResult = array();
                if (! empty($survey_data[$patient_id]['response_data']['results'])) {
                    
                    foreach ($survey_data[$patient_id]['response_data']['results'] as $numeric_code => $score_item) {
                        $SurveyResult[] = array(
                            'survey_took' => $patient_chain_id,
                            'survey' => $score_item['SurveyResult']['survey'],
                            'question' => $score_item['SurveyResult']['question'],
                            'answered' => $score_item['SurveyResult']['answered'],
                            'answer' => $score_item['SurveyResult']['answer'],
                            'freetext' => $score_item['SurveyResult']['freetext'],
                            'row' => $score_item['SurveyResult']['row'],
                            'column' => $score_item['SurveyResult']['column']
                        );
                    }
                    
                    /* foreach ($SurveyResult as $survey_result_line){
                        $insert_sr = new SurveyResults();
                        foreach($survey_result_line as $field =>$value){
                            if($field =="column"){
                                $insert_sr->{$field} =$value ;
                            } 
                            elseif($field == "row"){
                                $insert_sr->{$field} =$value ;
                            } else{
                                $insert_sr->{$field} =$value ;
                            }
                        }
                        $insert_sr->save();
                    }
                     */
                    
//                     if ($SurveyResult) {
//                         $collection = new Doctrine_Collection('SurveyResults');
//                         $collection->fromArray($SurveyResult);
//                         $collection->save();
//                     }

                    $conn = Doctrine_Manager::getInstance()->getConnection('IDAT');
                    foreach ($SurveyResult as $survey_row){
                        $values_don = "";
                        foreach($survey_row as $field => $value){
                            $values_don .= '"' . $value . '",';
                        }
                        $sqlInsert  = "INSERT INTO `survey_results` ( `survey_took`, `survey`, `question`, `answered`, `answer`, `freetext`, `row`, `column`) VALUES (".substr($values_don, 0, -1).")";
                        $queryInsert = $conn->prepare($sqlInsert);
                        $queryInsert = $conn->execute($sqlInsert);
                        $queryInsert->closeCursor();
                    }
                    
                }
            }
            
            // INSERT SCORE DATA 
            $existing_result_Data = array();
            $existing_result_Data = SurveyResultScoresTable::findByPatient2chain($patient_chain_id);
            if (! empty($existing_result_Data)) {
                $results_rows_ids = array_column($existing_result_Data, 'id');
                
                if (! empty($results_rows_ids)) {
                    $delete_existin_scores = Doctrine_Query::create()->update('SurveyResultScores')
                        ->set('isdelete', 1)
                        ->set('delete_date', '"' . date("Y-m-d H:i:s", time()) . '"')
                        ->andWhere("survey_took =?", $patient_chain_id)
                        ->andWhereIn("id", $results_rows_ids);
                    $delete_existin_scores->execute();
                }
            }
            if (! empty($result[$patient_id]['response_data'])) {
                $score_retrived_for[] = $patient_id;
                // insert result data
                $SurveyResultScores = array();
                if (! empty($result[$patient_id]['response_data']['scores'])) {
                    foreach ($result[$patient_id]['response_data']['scores'] as $numeric_code => $score_item) {
                        $SurveyResultScores[] = array(
//                             'id' => $score_item['SurveyResultScores']['id'],
                            'survey_took' => $patient_chain_id,
                            'survey' => $score_item['SurveyResultScores']['survey'],
                            'score' => $score_item['SurveyResultScores']['score'],
                            'value' => $score_item['SurveyResultScores']['value'],
                            'value_extra_text' => $score_item['SurveyResultScores']['value_extra_text'],
                            'value_extra' => $score_item['SurveyResultScores']['value_extra'],
                            'eq' => $score_item['SurveyResultScores']['eq'],
                            'chart_text' => $score_item['SurveyResultScores']['chart_text'],
                            'misc_details' => $score_item['SurveyResultScores']['misc_details']
                        );
                    }
                    
                    if ($SurveyResultScores) {
                        $collection = new Doctrine_Collection('SurveyResultScores');
                        $collection->fromArray($SurveyResultScores);
                        $collection->save();
                    }
                }
                
                if ( $ptc['end'] == "0000-00-00 00:00:00" && ! empty($result[$patient_id]['response_data']['info']['PatientSurvey']['end'])) {
                    //  update survey_patient2chain
                    $started_survey = Doctrine::getTable('SurveyPatient2chain')->find($patient_chain_id);
                    $started_survey->end = date('Y-m-d H:i:s',$result[$patient_id]['response_data']['info']['PatientSurvey']['end']);
                    $started_survey->save();
                }
            }
        }
        
        $score_retrived_for_arr['score_retrived_for'] = count($score_retrived_for);
        return $score_retrived_for_arr;
        
    }
}

