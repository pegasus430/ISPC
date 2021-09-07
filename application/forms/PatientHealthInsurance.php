<?php

require_once("Pms/Form.php");

class Application_Form_PatientHealthInsurance extends Pms_Form
{
    
    public static $patient_health_insurance_companyid = null;
    
    protected $_model = 'PatientHealthInsurance';
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_health_insurance' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],
        ],
    ];
    
    
    public function getVersorgerExtract() {
        return array(
            array( "label" => null, "cols" => array("HealthInsuranceSubdivisions" => "subdivision_name")),
            
            array( "label" => $this->translate('company_name'), "cols" => array("company_name")),
            array( "label" => $this->translate('name'), "cols" => array("ins2s_name")),

            array( "label" => $this->translate('insurance_provider'), "cols" => array("ins_insurance_provider")),
            array( "label" => $this->translate('insurance_provider'), "cols" => array("ins2s_insurance_provider")),
            
            
            array( "label" => $this->translate('phone'), "cols" => array("ins_phone")),
            array( "label" => $this->translate('phone'), "cols" => array("ins2s_phone")),
            
            array( "label" => $this->translate('insuranceno'), "cols" => array("insurance_no")),
            array( "label" => $this->translate('Institutskennzeichen'), "cols" => array("ins2s_iknumber")),
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("company_name")),
            array(array("ins_street")),
            array(array("ins_zip"), array("ins_city")),
        );
    }
    
    
    
	public function validate($post)
	{
		$Tr = new Zend_View_Helper_Translate();

		$error=0;
		$val = new Pms_Validation();

		$ppun = new PpunIpid();
		$modules = new Modules();
		$client_modules = $modules->get_client_modules();
		
		if(strlen($_GET['id'])>0)
		{
			if(!$val->isstring($post['company_name'])){
				$this->error_message['company_name']=$Tr->translate('companyname_error'); $error=5;
			}
			
			if(isset($client_modules['88']) && $client_modules['88'] =="1"){
			    
    			if( $val->isstring($post['ppun']) ){

    			    // ipid
    			    $decid = Pms_Uuid::decrypt($_GET['id']);
    			    $ipid = Pms_commonData::getIpid($decid);
    			    
    			    // client
    			    $logininfo = new Zend_Session_Namespace('Login_Info');
    			    $client = $logininfo->clientid;
    			     
    			    
    			    // check if ppun is unique
    			    $doc = Doctrine_Query::create()
    			    ->select('*')
    			    ->from('PpunIpid')
    			    ->where('ipid != ?', $ipid)
    			    ->andWhere('clientid = ?', $client )
    			    ->andWhere('ppun = ?',$post['ppun']);
    			    $doc_res = $doc->fetchArray();
    			    if(!empty($doc_res)){
        				$this->error_message['ppun']=$Tr->translate('ppun_error'); $error=6;
    			    }
    			}
			}
			
		}

		if($error==0)
		{
		 return true;
		}

		return false;
	}

	public function InsertData($post)
	{
		if(strlen($post['company_name'])>0)
		{
			$cardentry_date = explode(".",$post['cardentry_date']);
			$date_of_birth = explode(".",$post['date_of_birth']);
			$card_valid_till = explode(".",$post['card_valid_till']);
			$exemption_till_date = explode(".", $post['exemption_till_date']); //ISPC - 2079

			$cust = new PatientHealthInsurance();
			$cust->cardentry_date = $cardentry_date[2].".".$cardentry_date[1]."-".$cardentry_date[0];
			$cust->ipid = $post['ipid'];
			$cust->kvk_no = $post['kvk_no'];
			$cust->insurance_no = $post['insurance_no'];
			$cust->institutskennzeichen = $post['institutskennzeichen'];
			$cust->vk_no = $post['vk_no'];
			$cust->insurance_status = Pms_CommonData::aesEncrypt($post['insurance_status']);
			$cust->company_name = Pms_CommonData::aesEncrypt($post['company_name']);
			$cust->companyid = $post['hdn_companyid'];
			$cust->status_added = Pms_CommonData::aesEncrypt($post['status_added']);
			$cust->ins_insurance_provider = Pms_CommonData::aesEncrypt($post['ins_insurance_provider']);
			$cust->ins_first_name = Pms_CommonData::aesEncrypt($post['ins_first_name']);
			$cust->ins_middle_name = Pms_CommonData::aesEncrypt($post['ins_middle_name']);
			$cust->ins_last_name = Pms_CommonData::aesEncrypt($post['ins_last_name']);
			$cust->ins_contactperson = Pms_CommonData::aesEncrypt($post['ins_contactperson']);
			$cust->date_of_birth = $date_of_birth[2]."-".$date_of_birth[1]."-".$date_of_birth[0];
			$cust->ins_country = $post['ins_country'];
			$cust->ins_street = Pms_CommonData::aesEncrypt($post['ins_street']);
			$cust->ins_zip = Pms_CommonData::aesEncrypt($post['ins_zip']);
			$cust->ins_city = Pms_CommonData::aesEncrypt($post['ins_city']);
			$cust->ins_phone = Pms_CommonData::aesEncrypt($post['ins_phone']);
			$cust->ins_phone2 = Pms_CommonData::aesEncrypt($post['ins_phone2']);
			$cust->ins_phonefax = Pms_CommonData::aesEncrypt($post['ins_phonefax']);
			$cust->ins_post_office_box = Pms_CommonData::aesEncrypt($post['ins_post_office_box']);
			$cust->ins_post_office_box_location = Pms_CommonData::aesEncrypt($post['ins_post_office_box_location']);
			$cust->ins_email = Pms_CommonData::aesEncrypt($post['ins_email']);
			$cust->ins_zip_mailbox = Pms_CommonData::aesEncrypt($post['ins_zip_mailbox']);
			$cust->ins_debtor_number = Pms_CommonData::aesEncrypt($post['ins_debtor_number']);
			$cust->card_valid_till = $card_valid_till[2]."-".$card_valid_till[1]."-".$card_valid_till[0];
			$cust->checksum = $post['checksum'];
			$cust->help1 = Pms_CommonData::aesEncrypt($post['help1']);
			$cust->rezeptgebuhrenbefreiung = $post['rezeptgebuhrenbefreiung'];
			$cust->exemption_till_date = $exemption_till_date[2]."-".$exemption_till_date[1]."-".$exemption_till_date[0];

			if($post['insurance_options'] == 'privatepatient')
			{
				$cust->privatepatient = '1';
			}
			else
			{
				$cust->privatepatient = '0';
			}

			if($post['insurance_options'] == 'direct_billing')
			{
				$cust->direct_billing = '1';
			}
			else
			{
				$cust->direct_billing = '0';
			}

			if($post['insurance_options'] == 'bg_patient')
			{
				$cust->bg_patient = '1';
			}
			else
			{
				$cust->bg_patient = '0';
			}
			$cust->private_valid_contribution = $post['valid'];
			$cust->private_contribution = $post['privatecontribution'];
			

			if(strlen($post['ins_comment'])>0){
				$cust->comment = Pms_CommonData::aesEncrypt($post['ins_comment']);;
			} else{
				$cust->comment = Pms_CommonData::aesEncrypt($post['comment']);
			}
			
			
			$cust->help2 = Pms_CommonData::aesEncrypt($post['help2']);
			$cust->help3 = Pms_CommonData::aesEncrypt($post['help3']);
			$cust->help4 = Pms_CommonData::aesEncrypt($post['help4']);
			$cust->cost = $post['cost'];
			
			//ISPC-2666 Lore 16.09.2020
			$cust->ins_over_both_p = $post['ins_over_both_p'];
			$cust->ins_over_mother = $post['ins_over_mother'];
			$cust->ins_over_father = $post['ins_over_father'];
			$cust->self_insured = $post['self_insured'];
			$cust->ins_over_others = $post['ins_over_others'];
			$cust->ins_over_others_text = $post['ins_over_others_text'];
			//.
			
			$cust->save();


			if($post['subdivizions_permissions'] == '1' ){
				$q = Doctrine_Query::create()
				->delete("PatientHealthInsurance2Subdivisions")
				->where("ipid='".$post['ipid']."'");
				$q->execute();

				foreach($post['subdivizion'] as $subdiv_id=>$subdiv_details){
					$insert = 0;
					foreach($subdiv_details as $inputs){
						if(!empty($inputs)){
							$insert += 1;
						} else{
							$insert += 0;

						}
					}

					if($insert > 0){

						$hinsu = new PatientHealthInsurance2Subdivisions();
						$hinsu->ipid = $post['ipid'];
						$hinsu->company_id = $post['hdn_companyid'] ;
						$hinsu->subdiv_id = $subdiv_id;
						$hinsu->ins2s_name = Pms_CommonData::aesEncrypt($subdiv_details['name']);
						$hinsu->ins2s_insurance_provider = Pms_CommonData::aesEncrypt($subdiv_details['insurance_provider']);
						$hinsu->ins2s_contact_person = Pms_CommonData::aesEncrypt($subdiv_details['contact_person']);
						$hinsu->ins2s_street1 = Pms_CommonData::aesEncrypt($subdiv_details['street1']);
						$hinsu->ins2s_street2 = Pms_CommonData::aesEncrypt($subdiv_details['street2']);
						$hinsu->ins2s_zip = Pms_CommonData::aesEncrypt($subdiv_details['zip']);
						$hinsu->ins2s_city = Pms_CommonData::aesEncrypt($subdiv_details['city']);
						$hinsu->ins2s_phone = Pms_CommonData::aesEncrypt($subdiv_details['phone']);
						$hinsu->ins2s_phone2 = Pms_CommonData::aesEncrypt($subdiv_details['phone2']);
						$hinsu->ins2s_fax = Pms_CommonData::aesEncrypt($subdiv_details['fax']);
						$hinsu->ins2s_post_office_box = Pms_CommonData::aesEncrypt($subdiv_details['post_office_box']);
						$hinsu->ins2s_post_office_box_location = Pms_CommonData::aesEncrypt($subdiv_details['post_office_box_location']);
						$hinsu->ins2s_zip_mailbox = Pms_CommonData::aesEncrypt($subdiv_details['zip_mailbox']);
						$hinsu->ins2s_kvnumber = Pms_CommonData::aesEncrypt($subdiv_details['kvnumber']);
						$hinsu->ins2s_iknumber = Pms_CommonData::aesEncrypt($subdiv_details['iknumber']);
						$hinsu->ins2s_ikbilling = Pms_CommonData::aesEncrypt($subdiv_details['ikbilling']);
						$hinsu->ins2s_debtor_number= Pms_CommonData::aesEncrypt($subdiv_details['debtor_number']);
						$hinsu->comments = Pms_CommonData::aesEncrypt($subdiv_details['comments']);
						$hinsu->ins2s_email = Pms_CommonData::aesEncrypt($subdiv_details['email']);
						$hinsu->save();
					}
				}
			}
		}
	}

	public function UpdateData($post)
	{

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_commonData::getIpid($decid);

		$Mhi2s = Doctrine_Query::create()
		->select("*")
		->from("PatientHealthInsurance")
		->where("ipid like  '".$ipid."' " );
		$custarr = $Mhi2s->fetchArray();

		if(count($custarr)>0)
		{
			if($post['cardentry_date'])
			{
				$cardentry_date = explode(".",$post['cardentry_date']);
			}
			else
			{
				$cardentry_date = date("Y-m-d",time());
			}

			$date_of_birth = explode(".",$post['date_of_birth']);
			$card_valid_till = explode(".",$post['card_valid_till']);
			if(! empty($post['exemption_till_date'])){
				$exemption_till_date = explode(".", $post['exemption_till_date']); //ISPC - 2079
			}

			$cust = Doctrine::getTable('PatientHealthInsurance')->find($custarr[0]['id']);
			$existing_data = $cust->toArray();
			
			$cust->cardentry_date = $cardentry_date[2].".".$cardentry_date[1]."-".$cardentry_date[0];
			$cust->kvk_no = $post['kvk_no'];
			$cust->insurance_no = $post['insurance_no'];
			$cust->institutskennzeichen = $post['institutskennzeichen'];
			$cust->vk_no = $post['vk_no'];
			$cust->insurance_status = Pms_CommonData::aesEncrypt($post['insurance_status']);
			$cust->status_added = Pms_CommonData::aesEncrypt($post['status_added']);
			$cust->ins_first_name = Pms_CommonData::aesEncrypt($post['ins_first_name']);
			$cust->ins_insurance_provider = Pms_CommonData::aesEncrypt($post['ins_insurance_provider']);
			$cust->ins_middle_name = Pms_CommonData::aesEncrypt($post['ins_middle_name']);
			$cust->ins_contactperson = Pms_CommonData::aesEncrypt($post['ins_contactperson']);
			$cust->company_name = Pms_CommonData::aesEncrypt($post['company_name']);
			$cust->companyid = $post['hdn_companyid'];
			$cust->ins_last_name = Pms_CommonData::aesEncrypt($post['ins_last_name']);
			$cust->date_of_birth = $date_of_birth[2]."-".$date_of_birth[1]."-".$date_of_birth[0];
			$cust->ins_country = $post['ins_country'];
			$cust->rezeptgebuhrenbefreiung = $post['rezeptgebuhrenbefreiung'];

			if(!empty($exemption_till_date)){
				$cust->exemption_till_date = $exemption_till_date[2]."-".$exemption_till_date[1]."-".$exemption_till_date[0];
			} else{
				$cust->exemption_till_date = 0000-00-00;
			}
			
			if($post['rezeptgebuhrenbefreiung'] == '2')
			{
				
				if($cust->exemption_till_date != '0000-00-00')
				{
					$hinsu = new PatientHealthInsuranceHistory();
					$hinsu->ipid = $ipid;
					$hinsu->exemption_till_date = $existing_data['exemption_till_date'] ;
					$hinsu->patient_hi_data =	serialize($existing_data);
					$hinsu->save();
				
					$cust->exemption_till_date = '0000-00-00';
				}
				
			}			
			if($post['insurance_options'] == 'privatepatient')
			{
				$cust->privatepatient = '1';
			}
			else
			{
				$cust->privatepatient = '0';
			}

			if($post['insurance_options'] == 'direct_billing')
			{
				$cust->direct_billing = '1';
			}
			else
			{
				$cust->direct_billing = '0';
			}

			if($post['insurance_options'] == 'bg_patient')
			{
				$cust->bg_patient = '1';
			}
			else
			{
				$cust->bg_patient = '0';
			}
			$cust->private_valid_contribution = $post['valid'];
			$cust->private_contribution = $post['privatecontribution'];
			
			$cust->comment = Pms_CommonData::aesEncrypt($post['comment']);
			$cust->ins_zip = Pms_CommonData::aesEncrypt($post['ins_zip']);
			$cust->ins_street = Pms_CommonData::aesEncrypt($post['ins_street']);
			$cust->ins_city = Pms_CommonData::aesEncrypt($post['ins_city']);
			$cust->ins_phone = Pms_CommonData::aesEncrypt($post['ins_phone']);
			$cust->ins_phone2 = Pms_CommonData::aesEncrypt($post['ins_phone2']);
			$cust->ins_phonefax = Pms_CommonData::aesEncrypt($post['ins_phonefax']);
			$cust->ins_post_office_box = Pms_CommonData::aesEncrypt($post['ins_post_office_box']);
			$cust->ins_post_office_box_location = Pms_CommonData::aesEncrypt($post['ins_post_office_box_location']);
			$cust->ins_email = Pms_CommonData::aesEncrypt($post['ins_email']);
			$cust->ins_zip_mailbox = Pms_CommonData::aesEncrypt($post['ins_zip_mailbox']);
			$cust->ins_debtor_number = Pms_CommonData::aesEncrypt($post['ins_debtor_number']);


			$cust->card_valid_till = $card_valid_till[2]."-".$card_valid_till[1]."-".$card_valid_till[0];
			$cust->checksum = $post['checksum'];
			$cust->help1 = Pms_CommonData::aesEncrypt($post['help1']);
			$cust->help2 = Pms_CommonData::aesEncrypt($post['help2']);
			$cust->help3 = Pms_CommonData::aesEncrypt($post['help3']);
			$cust->help4 = Pms_CommonData::aesEncrypt($post['help4']);
			$cust->cost = $post['cost'];
			
			//ISPC-2666 Lore 16.09.2020
			$cust->ins_over_both_p = $post['ins_over_both_p'];
			$cust->ins_over_mother = $post['ins_over_mother'];
			$cust->ins_over_father = $post['ins_over_father'];
			$cust->self_insured = $post['self_insured'];
			$cust->ins_over_others = $post['ins_over_others'];
			$cust->ins_over_others_text = $post['ins_over_others_text'];
			//.
			
			$cust->save();


			if($post['subdivizions_permissions'] == '1' ){
				$q = Doctrine_Query::create()
				->delete("PatientHealthInsurance2Subdivisions")
				->where("ipid like  '".$ipid."' " );;
				$q->execute();

				foreach($post['subdivizion'] as $subdiv_id=>$subdiv_details){
					$insert = 0;
					foreach($subdiv_details as $inputs){
						if(!empty($inputs)){
							$insert += 1;
						} else{
							$insert += 0;
						}
					}

					if($insert > 0){
						$hinsu = new PatientHealthInsurance2Subdivisions();
						$hinsu->ipid = $ipid;
						$hinsu->company_id = $post['hdn_companyid'] ;
						$hinsu->subdiv_id = $subdiv_id;
						$hinsu->ins2s_name = Pms_CommonData::aesEncrypt($subdiv_details['name']);
						$hinsu->ins2s_insurance_provider = Pms_CommonData::aesEncrypt($subdiv_details['insurance_provider']);
						$hinsu->ins2s_contact_person = Pms_CommonData::aesEncrypt($subdiv_details['contact_person']);
						$hinsu->ins2s_street1 = Pms_CommonData::aesEncrypt($subdiv_details['street1']);
						$hinsu->ins2s_street2 = Pms_CommonData::aesEncrypt($subdiv_details['street2']);
						$hinsu->ins2s_zip = Pms_CommonData::aesEncrypt($subdiv_details['zip']);
						$hinsu->ins2s_city = Pms_CommonData::aesEncrypt($subdiv_details['city']);
						$hinsu->ins2s_phone = Pms_CommonData::aesEncrypt($subdiv_details['phone']);
						$hinsu->ins2s_phone2 = Pms_CommonData::aesEncrypt($subdiv_details['phone2']);
						$hinsu->ins2s_fax = Pms_CommonData::aesEncrypt($subdiv_details['fax']);
						$hinsu->ins2s_post_office_box = Pms_CommonData::aesEncrypt($subdiv_details['post_office_box']);
						$hinsu->ins2s_post_office_box_location = Pms_CommonData::aesEncrypt($subdiv_details['post_office_box_location']);
						$hinsu->ins2s_zip_mailbox = Pms_CommonData::aesEncrypt($subdiv_details['zip_mailbox']);
						$hinsu->ins2s_kvnumber = Pms_CommonData::aesEncrypt($subdiv_details['kvnumber']);
						$hinsu->ins2s_iknumber = Pms_CommonData::aesEncrypt($subdiv_details['iknumber']);
						$hinsu->ins2s_ikbilling = Pms_CommonData::aesEncrypt($subdiv_details['ikbilling']);
						$hinsu->ins2s_debtor_number= Pms_CommonData::aesEncrypt($subdiv_details['debtor_number']);
						$hinsu->comments = Pms_CommonData::aesEncrypt($subdiv_details['comments']);
						$hinsu->ins2s_email = Pms_CommonData::aesEncrypt($subdiv_details['email']);
						$hinsu->save();
					}
				}
			}
		}
		else
		{
			$post['ipid'] = $ipid;
			$this->InsertData($post);
		}
	}
	
	
	public function reset_contribution($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_commonData::getIpid($decid); 
		
		if(strlen($post['insurance_options']) == 0 ){
			$cust = Doctrine::getTable('PatientHealthInsurance')->find($ipid);
			$cust->private_valid_contribution = "";
			$cust->private_contribution = "";
			$cust->save();
			$str = "";
 
		}
	}
	
	
	/**
	 * Krankenkasse formular
	 * uses $this->_patientMasterData['ModulePrivileges'][90]
	 * uses $this->_patientMasterData['ModulePrivileges'][88]
	 * ppun is fetched inside this is missing
	 * 
	 * @claudiu 23.11.2017
	 * @param array $options, optional values to populate the form
	 * @return Zend_Form_SubForm
	 */
	public function create_form_health_insurance($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    	  
	    $this->mapValidateFunction($__fnName, null);//removed the create_form_isValid.... because i hane no time to fix
	    
	    $this->mapSaveFunction($__fnName, "save_form_health_insurance");
	    

	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('patient_health_insurance'));
	    $subform->setAttrib("class", "label_same_size {$__fnName}");
	    
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
// 	         if (! empty($options)) dd($options['company']['zip'], $options);
	    //hidden
	    
	    $subform->addElement('text', '_id', array(
	        'label'      => null,
	        'value'      => $options['id'],
	        'filters'    => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	    
	        ),
	    ));
	    $subform->addElement('hidden', 'id', array(
	        'label'      => null,
	        'value'      => $options['id'],
	        'filters'    => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	    
	        ),
	    ));
	    $subform->addElement('hidden', 'companyid', array(
	        'label'      => null,
	        'value'      => $options['companyid'],
	        'filters'    => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	    
	        ),
	    ));
	    
	    
	     
	    $subform->addElement('text', 'company_name', array(
	        'label'      => $this->translate('company_name'),
	        // 	        'placeholder' => 'Search my date',
	        'data-livesearch' => "HealthInsurance_company_name",
	        'required'   => true,
	        'value'    => $options['company_name'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	
	        ),
	    ));
	    $subform->addElement('text', 'ins_insurance_provider', array(
	        'label'      => $this->translate('insurance_provider'),
	        'required'   => false,
	        'value'    => $options['ins_insurance_provider'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_contactperson', array(
	        'label'      => $this->translate('contactperson'),
	        'value'    => $options['ins_contactperson'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_street', array(
	        'label'      => $this->translate('street'),
	        'value'    => ! empty($options['ins_street']) ? $options['ins_street'] : ( isset($options['company']['street1']) ? $options['company']['street1'] : "") ,
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_zip', array(
	        'label'      => $this->translate('zip'),
	        'value'    => ! empty($options['ins_zip']) ? $options['ins_zip'] : (isset($options['company']['zip']) ? $options['company']['zip'] : ""),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'data-livesearch'   => 'zip',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_city', array(
	        'label'      => $this->translate('city'),
	        'value'    => ! empty($options['ins_city']) ? $options['ins_city'] : (isset($options['company']['city']) ? $options['company']['city'] : ""),	         
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'data-livesearch'   => 'city',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_phone', array(
	        'label'      => $this->translate('Telefon 1'),
	        'value'    => $options['ins_phone'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_phone2', array(
	        'label'      => $this->translate('Telefon 2'),
	        'value'    => $options['ins_phone2'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_phonefax', array(
	        'label'      => $this->translate('phonefax'),
	        'value'    => $options['ins_phonefax'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_post_office_box', array(
	        'label'      => $this->translate('post_office_box'),
	        'value'    => $options['ins_post_office_box'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_zip_mailbox', array(
	        'label'      => $this->translate('zip_mailbox'),
	        'value'    => $options['ins_zip_mailbox'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_post_office_box_location', array(
	        'label'      => $this->translate('post_office_box_location'),
	        'value'    => $options['ins_post_office_box_location'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins_email', array(
	        'label'      => $this->translate('email'),
	        'value'    => $options['ins_email'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('EmailAddress'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'kvk_no', array(
	        'label'      => $this->translate('kassenno'),
	        'value'    => $options['kvk_no'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'institutskennzeichen', array(
	        'label'      => $this->translate('Institutskennzeichen (IK) '),
	        'value'    => $options['institutskennzeichen'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'insurance_no', array(
	        'label'      => $this->translate('insuranceno'),
	        'value'    => $options['insurance_no'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    
	    if ($this->_patientMasterData['ModulePrivileges'][90] == 1) {
	        //ISPC-2452 Ancuta
	        if ($this->_patientMasterData['ModulePrivileges'][90] == 1 && $this->_patientMasterData['ModulePrivileges'][204] == 1) {
	            //if($this->show_debtor_number == "1"):
	            $subform->addElement('text', 'ins_debtor_number', array(
	                'label'      => $this->translate('debtor_number') ,
	                'value'    => $options['ins_debtor_number'],
	                'required'   => false,
	                'filters'    => array('StringTrim'),
	                'validators' => array('NotEmpty'),
	                'decorators' => array(
	                    'ViewHelper',
	                    array('Errors'),
	                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3,"openOnly" => true)),
	                    array('Label', array('tag' => 'td')),
	                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', "openOnly" => true,)),
	                ),
	                'class' => "w100"
	            ));
	        
	            //add button to add new contacts
	            $subform->addElement('button', 'generatehidebitornrBtn', array(
	                'onClick'      => 'generatehidebitornr(this, \'ins_debtor_number\'); return false;',
	                'value'        => '1',
	                'label'        => 'generate_debtor_number',
	                'decorators'   => array(
	                    'ViewHelper',
	                    'FormElements',
	                    // 	            array('HtmlTag', array('tag' => 'tr')),
	                    array(array('data'=>'HtmlTag'),array('tag'=>'td', "closeOnly" => true)),
	                    array(array('row'=>'HtmlTag'),array('tag'=>'tr', "closeOnly" => true))
	                ),
	            ));
	            	
	        } else {
	            
        	    //if($this->show_debtor_number == "1"):
        	    $subform->addElement('text', 'ins_debtor_number', array(
        	        'label'      => $this->translate('debtor_number') ,
        	        'value'    => $options['ins_debtor_number'],
        	        'required'   => false,
        	        'filters'    => array('StringTrim'),
        	        'validators' => array('NotEmpty'),
        	        'decorators' => array(
        	            'ViewHelper',
        	            array('Errors'),
        	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
        	            array('Label', array('tag' => 'td')),
        	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
        	        ),
        	    ));
	        }
	    }

	    $insurance_status_values = $this->getInsuranceStatusArray();
	     
	    // 	    dd($insurance_status_values);
	   
	    $subform->addElement('select', 'insurance_status', array(
	        'label'      => $this->translate('insurancestatus'),
	        'value'    => isset($options['__insurance_status']) ? $options['__insurance_status'] : $options['insurance_status'],
	        'multiOptions'  => $insurance_status_values,
	        "disable"=>array('disabled_delimiter'), //TODO-3528 Ancuta 20.11.2020
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	
	    $subform->addElement('radio', 'rezeptgebuhrenbefreiung', array(
	        'label'      => $this->translate('Fees'),
	        'value'    => $options['rezeptgebuhrenbefreiung'],
	         
	        'multiOptions'  => array( 1 => $this->translate('gebuhrenbefreit'), 2 => $this->translate('gebuhrenpflichtig')),
	         
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly'=>true)),
	        ),
	        'onChange'  => "if(this.value=='1') {\$(this).parents('tr').find('.healt_insurance_exemption_till_date').show();} else {\$(this).parents('tr').find('.healt_insurance_exemption_till_date').hide();};",
	         
	    ));
	    
	    $display_exemption_till_date = $options['rezeptgebuhrenbefreiung'] == 1 ? "" : "display:none";
	    $subform->addElement('text', 'exemption_till_date', array(
	        'label'    => null,
	        'value'    => $options['exemption_till_date'],
	        'data-altfield' => 'exemption_till_date',
	        'data-altformat' => 'yy-mm-dd',
	        
	        'class'    => 'date allow_future',
	        
	        'required'   => false,
	        
	        //'validators' => array('Date'),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'openOnly'=>true, 'class'=>'healt_insurance_exemption_till_date', 'style'=> $display_exemption_till_date)),
	            //array('Label', array('tag' => 'td')),
	            //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),
	        ),
        ));
	    $hidden_date = $subform->createElement('hidden', 'exemption_till_date', array(
	        'label'    => null,
	        'value'    => $options['exemption_till_date'],
	        
	        'required'   => false,
	        
	        'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("date_format"=>'Y-m-d'))),
	        
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'closeOnly'=>true)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),
	        ),
	    ));
	    $subform->addElement($hidden_date, 'hidden_date'); // this is the alternative format.. so not to use date in php for db
	     
	     
	    //yafp
	    if ($options['privatepatient'] == 1 ) {$value_insurance_options =  'privatepatient';}
	    elseif ($options['direct_billing'] == 1 ) {$value_insurance_options =  'direct_billing';}
	    elseif ($options['bg_patient'] == 1 ) {$value_insurance_options =  'bg_patient';}
	    else {$value_insurance_options =  '';}
	
	    $insurance_options_values = HealthInsurance::getDefaultInsuranceOptions();
	    $subform->addElement('select', 'insurance_options', array(
	        'label'      => $this->translate('insurance_options_label'),
	        'value'    => $value_insurance_options,
	        'multiOptions'  => $insurance_options_values,
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'onChange'  => "if(this.value=='privatepatient') {\$('tr[data-name=\'insurance_options_validcontribution\'], tr[data-name=\'ppun_privatecontribution\']').show();} else {\$('tr[data-name=\'insurance_options_validcontribution\'], tr[data-name=\'ppun_privatecontribution\']').hide();};",
	    ));
	     
	    $display = $value_insurance_options != 'privatepatient' ? 'display:none;' : '';
	    $valid_values = HealthInsurance::getDefaultValid();
	    $subform->addElement('radio', 'private_valid_contribution', array(
	        'label'      => $this->translate('validcontribution'),
	        'value'    => $options['private_valid_contribution'],
	        'multiOptions'  => $valid_values,
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3, 'escape' => false)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'data-name' => 'insurance_options_validcontribution', 'style'=>$display)),
	        ),
	        'onChange'  => "if(this.value=='1') {\$('tr[data-name=\'validcontribution_privatecontribution\']').show().find('input').val('');} else {\$('tr[data-name=\'validcontribution_privatecontribution\']').hide().find('input').val('');};",
	
	    ));
	     
	    $display = $options['private_valid_contribution'] != 1 ? 'display:none;' : '';
	    $subform->addElement('text', 'private_contribution', array(
	        'label'      => $this->translate('contribution'),
	        'value'    => $options['private_contribution'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'data-name' =>'validcontribution_privatecontribution' , 'style'=>$display)),
	        ),
	    ));
	     
	    
	    if ($this->_patientMasterData['ModulePrivileges'][88] == 1) {
	        
	        //fetch ppun
	        if (empty($options['ppun']) && ! empty($options['id'])) {
	            $ppun = new PpunIpid();
	            $ppun_number = $ppun->check_patient_ppun_db($this->_ipid, $this->logininfo->clientid); // Just fill
	            
	            if ($ppun_number && is_array($ppun_number)) {
	                $options['ppun'] = $ppun_number['ppun'];
	            }
	        }
	        
	        
	        $display = $value_insurance_options != 'privatepatient' ? 'display:none;' : '';
	        $subform->addElement('text', 'ppun', array(
	            'label'      => $this->translate('ppun_label'),
	            'value'    => $options['ppun'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3 , "openOnly" => true)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'data-name' =>'ppun_privatecontribution', "openOnly" => true, 'style'=>$display)),
	            ),
	            'class' => "w100"
	        ));
	        
	        
	        //add button to add new contacts
	        $subform->addElement('button', 'generateppunBtn', array(
	            'onClick'      => 'generateppun(this, \'ppun\'); return false;',
	            'value'        => '1',
	            'label'        => 'generate_ppun',
	            'decorators'   => array(
	                'ViewHelper',
	                'FormElements',
	                // 	            array('HtmlTag', array('tag' => 'tr')),
	                array(array('data'=>'HtmlTag'),array('tag'=>'td', "closeOnly" => true)),
	                array(array('row'=>'HtmlTag'),array('tag'=>'tr', "closeOnly" => true))
	            ),
	        ));
	       
	    }
	    
	    
	    
	    $subform->addElement('textarea', 'comment', array(
	        'label'        => $this->translate('comment'),
	        'rows'         => 3,
	        'cols'         => 60,
	        'value'        => $options['comment'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	
	    //ISPC-2666 Lore 16.09.2020
	    if ($this->_patientMasterData['ModulePrivileges'][238] == 1) {
	        
	        $subform->addElement('note',  "insured_over", array(
	            'value' => $this->translate("insured_over"),
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' =>'show_hide','style' => 'font-weight: bold')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	            ),
	            'separator' => PHP_EOL
	            ));
	        
	        $subform->addElement('checkbox', 'ins_over_both_p', array(
	            'label'      => $this->translate('ins_over_both_p') ,
	            'value'      => $options['ins_over_both_p'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
	                array('Label', array('tag' => 'td', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	            ),
	        ));
	        
	        $subform->addElement('checkbox', 'ins_over_mother', array(
	            'label'      => $this->translate('ins_over_mother') ,
	            'value'      => $options['ins_over_mother'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	        $subform->addElement('checkbox', 'ins_over_father', array(
	            'label'      => $this->translate('ins_over_father') ,
	            'value'      => $options['ins_over_father'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	        $subform->addElement('checkbox', 'self_insured', array(
	            'label'      => $this->translate('self_insured') ,
	            'value'      => $options['self_insured'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	        
	        $subform->addElement('checkbox', 'ins_over_others', array(
	            'label'      => $this->translate('ins_over_others') ,
	            'value'      => $options['ins_over_others'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            'onChange' => "if($(this).is(':checked')) { $('.ins_over_others_valid', $(this).parents('table')).show();} else { $('.ins_over_others_valid', $(this).parents('table')).hide();} ",
	            ));
	        
	        $display_ins_over_others = $options['ins_over_others'] != 1 ? 'display:none' : '';
	        
	        $subform->addElement('text', 'ins_over_others_text', array(
	            'label'      => '' ,
	            'value'      => $options['ins_over_others_text'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'ins_over_others_valid', 'style' => $display_ins_over_others)),
	            ),
	        ));
	        
	        
	    }
	    //.
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	
	}

	
	
	/**
	 * ISPC-2666 Lore 16.09.2020
	 * @param array $options
	 * @param unknown $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_block_patient_hi($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName, null);//removed the create_form_isValid.... because i hane no time to fix
	    
	    $this->mapSaveFunction($__fnName, "save_form_health_insurance");
	    
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));	    
	    $subform->setLegend('block_patient_hi');
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    if($options['formular_type'] != 'pdf'){
	        $subform->addElement('text', '_id', array(
	            'label'      => null,
	            'value'      => $options['id'],
	            'filters'    => array('StringTrim', 'Int'),
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	                
	            ),
	        ));
	    }

	    $subform->addElement('hidden', 'id', array(
	        'label'      => null,
	        'value'      => $options['id'],
	        'filters'    => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	            
	        ),
	    ));
	    $subform->addElement('hidden', 'companyid', array(
	        'label'      => null,
	        'value'      => $options['companyid'],
	        'filters'    => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	            
	        ),
	    ));
	    
	    
	    
	    $subform->addElement('text', 'company_name', array(
	        'label'      => $this->translate('company_name'),
	        // 	        'placeholder' => 'Search my date',
	        'data-livesearch' => "HealthInsurance_company_name",
	        'required'   => true,
	        'value'    => $options['company_name'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_insurance_provider', array(
	        'label'      => $this->translate('insurance_provider'),
	        'required'   => false,
	        'value'    => $options['ins_insurance_provider'],
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_contactperson', array(
	        'label'      => $this->translate('contactperson'),
	        'value'    => $options['ins_contactperson'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_street', array(
	        'label'      => $this->translate('street'),
	        'value'    => ! empty($options['ins_street']) ? $options['ins_street'] : ( isset($options['company']['street1']) ? $options['company']['street1'] : "") ,
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_zip', array(
	        'label'      => $this->translate('zip'),
	        'value'    => ! empty($options['ins_zip']) ? $options['ins_zip'] : (isset($options['company']['zip']) ? $options['company']['zip'] : ""),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'data-livesearch'   => 'zip',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_city', array(
	        'label'      => $this->translate('city'),
	        'value'    => ! empty($options['ins_city']) ? $options['ins_city'] : (isset($options['company']['city']) ? $options['company']['city'] : ""),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'data-livesearch'   => 'city',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_phone', array(
	        'label'      => $this->translate('Telefon 1'),
	        'value'    => $options['ins_phone'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_phone2', array(
	        'label'      => $this->translate('Telefon 2'),
	        'value'    => $options['ins_phone2'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_phonefax', array(
	        'label'      => $this->translate('phonefax'),
	        'value'    => $options['ins_phonefax'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_post_office_box', array(
	        'label'      => $this->translate('post_office_box'),
	        'value'    => $options['ins_post_office_box'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_zip_mailbox', array(
	        'label'      => $this->translate('zip_mailbox'),
	        'value'    => $options['ins_zip_mailbox'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_post_office_box_location', array(
	        'label'      => $this->translate('post_office_box_location'),
	        'value'    => $options['ins_post_office_box_location'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'ins_email', array(
	        'label'      => $this->translate('email'),
	        'value'    => $options['ins_email'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('EmailAddress'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'kvk_no', array(
	        'label'      => $this->translate('kassenno'),
	        'value'    => $options['kvk_no'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'institutskennzeichen', array(
	        'label'      => $this->translate('Institutskennzeichen (IK) '),
	        'value'    => $options['institutskennzeichen'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    $subform->addElement('text', 'insurance_no', array(
	        'label'      => $this->translate('insuranceno'),
	        'value'    => $options['insurance_no'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    
	    
	    if ($this->_patientMasterData['ModulePrivileges'][90] == 1) {
	        //ISPC-2452 Ancuta
	        if ($this->_patientMasterData['ModulePrivileges'][90] == 1 && $this->_patientMasterData['ModulePrivileges'][204] == 1) {
	            //if($this->show_debtor_number == "1"):
	            if($options['formular_type'] == 'pdf'){
	                
	                $subform->addElement('text', 'ins_debtor_number', array(
	                    'label'      => $this->translate('debtor_number') ,
	                    'value'    => $options['ins_debtor_number'],
	                    'required'   => false,
	                    'filters'    => array('StringTrim'),
	                    'validators' => array('NotEmpty'),
	                    'decorators' => array(
	                        'ViewHelper',
	                        array('Errors'),
	                        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	                        array('Label', array('tag' => 'td')),
	                        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	                    )
	                ));
	                
	                
	            } else {
	                
	                $subform->addElement('text', 'ins_debtor_number', array(
	                    'label'      => $this->translate('debtor_number') ,
	                    'value'    => $options['ins_debtor_number'],
	                    'required'   => false,
	                    'filters'    => array('StringTrim'),
	                    'validators' => array('NotEmpty'),
	                    'decorators' => array(
	                        'ViewHelper',
	                        array('Errors'),
	                        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3,"openOnly" => true)),
	                        array('Label', array('tag' => 'td')),
	                        array(array('row' => 'HtmlTag'), array('tag' => 'tr', "openOnly" => true )),
	                    ),
	                    'class' => "w100"
	                ));
	                
	                //add button to add new contacts
	                if($options['formular_type'] != 'pdf'){
	                    $subform->addElement('button', 'generatehidebitornrBtn', array(
	                        'onClick'      => 'generatehidebitornr(this, \'ins_debtor_number\'); return false;',
	                        'value'        => '1',
	                        'label'        => 'generate_debtor_number',
	                        'decorators'   => array(
	                            'ViewHelper',
	                            'FormElements',
	                            // 	            array('HtmlTag', array('tag' => 'tr')),
	                            array(array('data'=>'HtmlTag'),array('tag'=>'td', "closeOnly" => true)),
	                            array(array('row'=>'HtmlTag'),array('tag'=>'tr', "closeOnly" => true))
	                        ),
	                    ));
	                }
	            }

	            
	        } else {
	            
	            //if($this->show_debtor_number == "1"):
	            $subform->addElement('text', 'ins_debtor_number', array(
	                'label'      => $this->translate('debtor_number') ,
	                'value'    => $options['ins_debtor_number'],
	                'required'   => false,
	                'filters'    => array('StringTrim'),
	                'validators' => array('NotEmpty'),
	                'decorators' => array(
	                    'ViewHelper',
	                    array('Errors'),
	                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	                    array('Label', array('tag' => 'td')),
	                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	                ),
	            ));
	        }
	    }
	    
	    $insurance_status_values = $this->getInsuranceStatusArray();
	    $current_hi =  $options;

	    if($options['formular_type'] == 'pdf'){
	        $subform->addElement('note', 'insurance_status', array(
	            'value'        => $insurance_status_values[$current_hi['insurance_status']],
	            'label'        => $this->translate('insurancestatus'),
	            'required'     => false,
	            // 				'filters'      => array('StringTrim'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	            ),
	        ));
	    }else {
	        $subform->addElement('select', 'insurance_status', array(
	            'label'      => $this->translate('insurancestatus'),
	            'value'    => isset($options['__insurance_status']) ? $options['__insurance_status'] : $options['insurance_status'],
	            'multiOptions'  => $insurance_status_values,
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            'class'=>'w100percent'
	        ));
	    }

	    if($options['formular_type'] == 'pdf'){
	        $subform->addElement('radio', 'rezeptgebuhrenbefreiung', array(
	            'label'      => $this->translate('Fees'),
	            'value'    => $options['rezeptgebuhrenbefreiung'],
	            
	            'multiOptions'  => array( 1 => $this->translate('gebuhrenbefreit'), 2 => $this->translate('gebuhrenpflichtig')),
	            
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            )	            
	        ));
	        
	    }else {
	        $subform->addElement('radio', 'rezeptgebuhrenbefreiung', array(
	            'label'      => $this->translate('Fees'),
	            'value'    => $options['rezeptgebuhrenbefreiung'],
	            
	            'multiOptions'  => array( 1 => $this->translate('gebuhrenbefreit'), 2 => $this->translate('gebuhrenpflichtig')),
	            
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly'=>true)),
	            ),
	            'onChange'  => "if(this.value=='1') {\$(this).parents('tr').find('.healt_insurance_exemption_till_date').show();} else {\$(this).parents('tr').find('.healt_insurance_exemption_till_date').hide();};",
	            
	        ));
	    }
	    

	    $display_exemption_till_date = $options['rezeptgebuhrenbefreiung'] == 1 ? "" : "display:none";
	    if($options['formular_type'] == 'pdf'){
	        
	        $subform->addElement('text', 'exemption_till_date', array(
	            'label'      => '',
	            'value'    => $options['exemption_till_date'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	        

	    
	    }else{
	        
	        $subform->addElement('text', 'exemption_till_date', array(
	            'label'    => null,
	            'value'    => $options['exemption_till_date'],
	            'data-altfield' => 'exemption_till_date',
	            'data-altformat' => 'yy-mm-dd',
	            
	            'class'    => 'date allow_future',
	            
	            'required'   => false,
	            
	            //'validators' => array('Date'),
	            
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'openOnly'=>true, 'class'=>'healt_insurance_exemption_till_date', 'style'=> $display_exemption_till_date)),
	                //array('Label', array('tag' => 'td')),
	                //array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),
	            ),
	            ));
	            $hidden_date = $subform->createElement('hidden', 'exemption_till_date', array(
	                'label'    => null,
	                'value'    => $options['exemption_till_date'],
	                
	                'required'   => false,
	                
	                'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("date_format"=>'Y-m-d'))),
	                
	                'decorators' => array(
	                    'ViewHelper',
	                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'closeOnly'=>true)),
	                    //array('Label', array('tag' => 'td')),
	                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),
	                ),
	            ));
	            $subform->addElement($hidden_date, 'hidden_date'); // this is the alternative format.. so not to use date in php for db
	    }

	    
	    //yafp
	    if ($options['privatepatient'] == 1 ) {$value_insurance_options =  'privatepatient';}
	    elseif ($options['direct_billing'] == 1 ) {$value_insurance_options =  'direct_billing';}
	    elseif ($options['bg_patient'] == 1 ) {$value_insurance_options =  'bg_patient';}
	    else {$value_insurance_options =  '';}
	    
	    $insurance_options_values = HealthInsurance::getDefaultInsuranceOptions();
	    $current_hi =  $options;
	    
	    if($options['formular_type'] == 'pdf'){
	        $subform->addElement('note', 'insurance_options', array(
	            'value'        => $insurance_options_values[$current_hi['insurance_options']],
	            'label'        => $this->translate('insurance_options_label'),
	            'required'     => false,
	            // 				'filters'      => array('StringTrim'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=> 3)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	            ),
	        ));
	    }else {
	        $subform->addElement('select', 'insurance_options', array(
	            'label'      => $this->translate('insurance_options_label'),
	            'value'    => $value_insurance_options,
	            'multiOptions'  => $insurance_options_values,
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            'class'=>'w100percent',
	            'onChange'  => "if(this.value=='privatepatient') {\$('tr[data-name=\'insurance_options_validcontribution\'], tr[data-name=\'ppun_privatecontribution\']').show();} else {\$('tr[data-name=\'insurance_options_validcontribution\'], tr[data-name=\'ppun_privatecontribution\']').hide();};",
	        ));
	    }

	    
	    $display = $value_insurance_options != 'privatepatient' ? 'display:none;' : '';
	    $valid_values = HealthInsurance::getDefaultValid();
	    
	    if($options['formular_type'] == 'pdf'){
	        $subform->addElement('radio', 'private_valid_contribution', array(
	            'label'      => $this->translate('validcontribution'),
	            'value'    => $options['private_valid_contribution'],
	            'multiOptions'  => $valid_values,
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'data-name' => 'insurance_options_validcontribution', 'style'=>$display)),
	            )	            
	        ));
	    }else {
	        $subform->addElement('radio', 'private_valid_contribution', array(
	            'label'      => $this->translate('validcontribution'),
	            'value'    => $options['private_valid_contribution'],
	            'multiOptions'  => $valid_values,
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3, 'escape' => false)),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'data-name' => 'insurance_options_validcontribution', 'style'=>$display)),
	            ),
	            'onChange'  => "if(this.value=='1') {\$('tr[data-name=\'validcontribution_privatecontribution\']').show().find('input').val('');} else {\$('tr[data-name=\'validcontribution_privatecontribution\']').hide().find('input').val('');};",
	            
	        ));
	    }

	    
	    $display = $options['private_valid_contribution'] != 1 ? 'display:none;' : '';
	    $subform->addElement('text', 'private_contribution', array(
	        'label'      => $this->translate('contribution'),
	        'value'    => $options['private_contribution'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'data-name' =>'validcontribution_privatecontribution' , 'style'=>$display)),
	        ),
	    ));
	    
	    
	    if ($this->_patientMasterData['ModulePrivileges'][88] == 1) {
	        
	        //fetch ppun
	        if (empty($options['ppun']) && ! empty($options['id'])) {
	            $ppun = new PpunIpid();
	            $ppun_number = $ppun->check_patient_ppun_db($this->_ipid, $this->logininfo->clientid); // Just fill
	            
	            if ($ppun_number && is_array($ppun_number)) {
	                $options['ppun'] = $ppun_number['ppun'];
	            }
	        }
	        
	        
	        $display = $value_insurance_options != 'privatepatient' ? 'display:none;' : '';
	        
	        if($options['formular_type'] == 'pdf'){
	            $subform->addElement('text', 'ppun', array(
	                'label'      => $this->translate('ppun_label'),
	                'value'    => $options['ppun'],
	                'required'   => false,
	                'filters'    => array('StringTrim'),
	                'validators' => array('NotEmpty'),
	                'decorators' => array(
	                    'ViewHelper',
	                    array('Errors'),
	                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3 )),
	                    array('Label', array('tag' => 'td')),
	                    array(array('row' => 'HtmlTag'), array('tag' => 'tr',  'style'=>$display)),
	                )
	             ));
	        } else {
	            $subform->addElement('text', 'ppun', array(
	                'label'      => $this->translate('ppun_label'),
	                'value'    => $options['ppun'],
	                'required'   => false,
	                'filters'    => array('StringTrim'),
	                'validators' => array('NotEmpty'),
	                'decorators' => array(
	                    'ViewHelper',
	                    array('Errors'),
	                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3 , "openOnly" => true)),
	                    array('Label', array('tag' => 'td')),
	                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'data-name' =>'ppun_privatecontribution', "openOnly" => true, 'style'=>$display)),
	                ),
	                'class' => "w100"
	            ));
	            
	            //add button to add new contacts
	            $subform->addElement('button', 'generateppunBtn', array(
	                'onClick'      => 'generateppun(this, \'ppun\'); return false;',
	                'value'        => '1',
	                'label'        => 'generate_ppun',
	                'decorators'   => array(
	                    'ViewHelper',
	                    'FormElements',
	                    // 	            array('HtmlTag', array('tag' => 'tr')),
	                    array(array('data'=>'HtmlTag'),array('tag'=>'td', "closeOnly" => true)),
	                    array(array('row'=>'HtmlTag'),array('tag'=>'tr', "closeOnly" => true))
	                ),
	            ));
	        }

  
	    }
	    
	    
	    
	    $subform->addElement('textarea', 'comment', array(
	        'label'        => $this->translate('comment'),
	        'rows'         => 3,
	        'cols'         => 60,
	        'value'        => $options['comment'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'class'=>'w100percent'
	    ));
	    
	    //ISPC-2666 Lore 16.09.2020
	    if ($this->_patientMasterData['ModulePrivileges'][238] == 1) {
	        
	        $subform->addElement('note',  "insured_over", array(
	            'value' => $this->translate("insured_over"),
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' =>'show_hide','style' => 'font-weight: bold')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            'separator' => PHP_EOL
	        ));
	        
	        $subform->addElement('checkbox', 'ins_over_both_p', array(
	            'label'      => $this->translate('ins_over_both_p') ,
	            'value'      => $options['ins_over_both_p'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	        
	        $subform->addElement('checkbox', 'ins_over_mother', array(
	            'label'      => $this->translate('ins_over_mother') ,
	            'value'      => $options['ins_over_mother'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	        $subform->addElement('checkbox', 'ins_over_father', array(
	            'label'      => $this->translate('ins_over_father') ,
	            'value'      => $options['ins_over_father'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	        $subform->addElement('checkbox', 'self_insured', array(
	            'label'      => $this->translate('self_insured') ,
	            'value'      => $options['self_insured'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));

	        $subform->addElement('checkbox', 'ins_over_others', array(
	            'label'      => $this->translate('ins_over_others') ,
	            'value'      => $options['ins_over_others'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first', 'style'=>"margin-left: 100px;width: 100px;")),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	            'onChange' => "if($(this).is(':checked')) { $('.ins_over_others_valid', $(this).parents('table')).show();} else { $('.ins_over_others_valid', $(this).parents('table')).hide();} ",
	        ));
	        
	        $display_ins_over_others = $options['ins_over_others'] != 1 ? 'display:none' : '';
	        
	        $subform->addElement('text', 'ins_over_others_text', array(
	            'label'      => '' ,
	            'value'      => $options['ins_over_others_text'],
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'ins_over_others_valid', 'style' => $display_ins_over_others)),
	            ),
	        ));
	        
	        
	    }
	    //.
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	    
	}
	
	
	/**
	 * uses $this->_patientMasterData['ModulePrivileges'][88]
	 * 
	 * @param string $ipid
	 * @param unknown $data
	 * @return void|Doctrine_Record
	 */
	public function save_form_health_insurance($ipid =  null , $data = array())
	{
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    
	    if ($this->_patientMasterData['ModulePrivileges'][88] == 1) {
	        
	        $ppun_data = array(
	            'ipid' => $ipid,
	            'clientid' => $this->logininfo->clientid,
	            'ppun' => $data['ppun']
	        );
	        
	        $ppunEntity = (new PpunIpid())->findOrCreateOneBy('ipid', $ipid, $ppun_data);
	         
	    }
	    
	    switch ($data['insurance_options']) {
	        
	        case "privatepatient":
	            $data['privatepatient'] = 1;
	            $data['direct_billing'] = 0;
	            $data['bg_patient'] = 0;
	            break;
	            
	        case "direct_billing":
	            $data['privatepatient'] = 0;
	            $data['direct_billing'] = 1;
	            $data['bg_patient'] = 0;
	            break;
	            
	        case "bg_patient":
	            $data['privatepatient'] = 0;
	            $data['direct_billing'] = 0;
	            $data['bg_patient'] = 1;
	            break;
	            
	        //TODO-3047 Lore 01.04.2020
	        default:
	            $data['privatepatient'] = 0;
	            $data['direct_billing'] = 0;
	            $data['bg_patient'] = 0;
	            break;
	        //    
	    }
	    
	    
	    $entity = new PatientHealthInsurance();
	    
// 	    return $entity->findOrCreateOneByIpidAndId( $ipid, $data['_id'], $data);
	    /*
	     * @since 27.08.2018
	     * changed to update/insert by ipid, cause this should be a single row, not multiple 
	     * (cause user could edit in multiple windows same patient)
	     */
	    $r = $entity->findOrCreateOneBy( 'ipid', $ipid, $data);
	    
	    self::$patient_health_insurance_companyid  =  $r['companyid'];
	    
	    return $r;
	    
	}
	
	
	
	
	
	/**
	 * Krankenkasse Subdivisions formular
	 * uses $this->_patientMasterData['ModulePrivileges'][90]
	 * division 3 = SAPV Rechnungsempfänger, has an extra field
	 * 
	 * @claudiu 23.11.2017
	 * @param array $options, optional values to populate the form
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_insurance_subdivision($options =  array() , $elementsBelongTo = null)
	{
	    $this->mapValidateFunction(__FUNCTION__, null); //create_form_isValid is not tested here ... so null
	
	    $this->mapSaveFunction(__FUNCTION__, "save_form_patient_insurance_subdivision");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    
	    
	    $subform->setLegend($options['HealthInsuranceSubdivisions']['subdivision_name']);
	    
	    
	    $subform->setAttrib("class", "label_same_size");
	
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	     
// 	    	    if (! empty($options)) ddecho($options);
	    //hidden
	     
	    $subform->addElement('hidden', 'id', array(
	        'label'      => null,
	        'value'      => $options['id'],
	        'filters'    => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	             
	        ),
	    ));
	     
	    $subform->addElement('hidden', 'company_id', array(
	        'label'      => null,
	        'value'      => $options['company_id'],
	        'filters'    => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	             
	        ),
	    ));
	
	    $subform->addElement('hidden', 'subdiv_id', array(
	        'label'      => null,
	        'value'      => $options['HealthInsuranceSubdivisions']['subdiv_id'],
	        'filters'    => array('StringTrim', 'Int'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	             
	        ),
	    ));
	
	    
	    
	    $subform->addElement('text', 'ins2s_name', array(
	        'label'      => $this->translate('name'),
	        'value'    => $options['ins2s_name'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	
	        ),
	    ));
	    
	    
	    
	    $subform->addElement('text', 'ins2s_insurance_provider', array(
	        'value'    => $options['ins2s_insurance_provider'],
	        'label'      => $this->translate('insurance_provider'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_contact_person', array(
	        'label'      => $this->translate('contactperson'),
	        'value'    => $options['ins2s_contact_person'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_street1', array(
	        'label'      => $this->translate('street'),
	        'value'    => $options['ins2s_street1'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_zip', array(
	        'label'      => $this->translate('zip'),
	        'value'    => $options['ins2s_zip'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'data-livesearch'   => 'zip',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_city', array(
	        'label'      => $this->translate('city'),
	        'value'    => $options['ins2s_city'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'data-livesearch'   => 'city',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_phone', array(
	        'label'      => $this->translate('phone'),
	        'value'    => $options['ins2s_phone'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_phone2', array(
	        'label'      => $this->translate('mobile'),
	        'value'    => $options['ins2s_phone2'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_fax', array(
	        'label'      => $this->translate('fax'),
	        'value'    => $options['ins2s_fax'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_post_office_box', array(
	        'label'      => $this->translate('post_office_box'),
	        'value'    => $options['ins2s_post_office_box'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_zip_mailbox', array(
	        'label'      => $this->translate('zip_mailbox'),
	        'value'    => $options['ins2s_zip_mailbox'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_post_office_box_location', array(
	        'label'      => $this->translate('post_office_box_location'),
	        'value'    => $options['ins2s_post_office_box_location'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_email', array(
	        'label'      => $this->translate('email'),
	        'value'    => $options['ins2s_email'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('EmailAddress'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_kvnumber', array(
	        'label'      => $this->translate('kassenno'),
	        'value'    => $options['ins2s_kvnumber'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ins2s_iknumber', array(
	        'label'      => $this->translate('Institutskennzeichen'),
	        'value'    => $options['ins2s_iknumber'],
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    //division 3 = SAPV Rechnungsempfänger, has an extra field
	    if (isset($options['HealthInsuranceSubdivisions']['subdiv_id']) && $options['HealthInsuranceSubdivisions']['subdiv_id'] == 3) {
    	    $subform->addElement('text', 'ins2s_ikbilling', array(
    	        'label'      => $this->translate('lbl_billing_ik'),
    	        'value'    => $options['ins2s_ikbilling'],
    	        'required'   => false,
    	        'filters'    => array('StringTrim'),
    	        'validators' => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
    	    ));
	    }
	    
	   
	    if ($this->_patientMasterData['ModulePrivileges'][90] == 1) {
    	    //if($this->show_debtor_number == "1"):
    	    $subform->addElement('text', 'ins2s_debtor_number', array(
    	        'label'      => $this->translate('debtor_number'),
    	        'value'    => $options['ins2s_debtor_number'],
    	        'required'   => false,
    	        'filters'    => array('StringTrim'),
    	        'validators' => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
    	            array('Label', array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
    	    ));
	    }
	

        $subform->addElement('textarea', 'comments', array(
            'label'        => $this->translate('comment'),
            'rows'         => 3,
            'cols'         => 60,
            'value'        => $options['comments'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

	
	        return $this->filter_by_block_name($subform, __FUNCTION__);
	
	}
	
	public function save_form_patient_insurance_subdivision($ipid =  null , $data = array())
	{
	    
	    if (empty($ipid) || empty($data)) {
	        return; //fail-safe
	    }
	    
	    if (is_array($data) && count($data) != count($data, COUNT_RECURSIVE))
	    {
	        $result = array();
	        
	        foreach ($data as $key=>$data_walk) {
	    
	            //i have a bug in PatientHealthInsurance2Subdivisions... an extra [0] .. hence this next if
	            if (empty($data_walk) || ! is_array($data_walk)) continue;	    
	            
	            
	            if ( ! empty(self::$patient_health_insurance_companyid)) {
	                $data_walk['company_id']  = self::$patient_health_insurance_companyid;
	            } elseif (empty($data_walk['company_id'])) {
	                if ($patient_insurance = $this->_patient_insurance_subdivision_findAparent($ipid)) {
    	                self::$patient_health_insurance_companyid = $patient_insurance[0]['companyid'];
    	                $data_walk['company_id']  = self::$patient_health_insurance_companyid;
	                }
	            }
	            
	            $entity = new PatientHealthInsurance2Subdivisions();
	            $result[] = $entity->findOrCreateOneByIpidAndId($ipid, $data_walk['id'], $data_walk);
	            
	        }
	        
	        return $result;
	        
	    } else {

	        if ( ! empty(self::$patient_health_insurance_companyid)) {
	            $data['company_id']  = self::$patient_health_insurance_companyid;
	        } elseif (empty($data['company_id'])) {
	            if ($patient_insurance = $this->_patient_insurance_subdivision_findAparent($ipid)) {
    	            $data['company_id'] = $patient_insurance[0]['companyid'];
	            }
	        }
	        
	        $entity = new PatientHealthInsurance2Subdivisions();
	        return $entity->findOrCreateOneByIpidAndId($ipid, $data['id'], $data);
	    }
	  
	}
	
	
	
	public function getInsuranceStatusArray() 
	{
	    $st = new KbvKeytabs();
	    return $st->getKbvKeytabs(1);
	}
	
	
	private function _patient_insurance_subdivision_findAparent($ipid) 
	{
	    if (empty($ipid)) {
	        return; //fail-safe
	    }
	    
	    return Doctrine_Core::getTable('PatientHealthInsurance')->findByDql('ipid = ? ORDER BY id DESC LIMIT 1', [$ipid], Doctrine_Core::HYDRATE_ARRAY);
	
	}
	
	
}

?>