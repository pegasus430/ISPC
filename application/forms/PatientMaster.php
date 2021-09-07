<?php

require_once("Pms/Form.php");

class Application_Form_PatientMaster extends Pms_Form 
{
    protected $_model = 'PatientMaster';
    
    protected $_block_name_allowed_inputs =  array(
        
        "WlAssessment" => [
            
            'create_form_patient_details' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [
                    'admission_date',
                    'first_name',
                    'last_name',
                    'birthd',
                    'street1',
                    'zip',
                    'city',
                    'phone',
                ]
                
            ],
        ],
        
        "PatientDetails" => [
            'create_form_patient_details' => [
                //this are removed
                '__removed' => [
                    'PatientReligions'
                ],
                //only this are allowed
                '__allowed' => [],
            ],
            'create_form_patient_hospiz_hospizverein_sapv_aapv' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
            'create_form_patient_hospiz_hospizverein_sapv_aapv_set_ishospiz_visitors' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
            'create_form_patient_hospiz_hospizverein_sapv_aapv_set_ishospizverein_visitors' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
        
        
        "MamboAssessment" => [
            
            'create_form_patient_details' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [
                    'admission_date',
                    'first_name',
                    'last_name',
                    'birthd',
                    'street1',
                    'zip',
                    'city',
                    'phone',
                    'mobile',
                    'PatientReligions',
                    
                ],
            ],
        ],
    );
    
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_patient_details' => [
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
    
            array( "label" => $this->translate('name'), "cols" => array("nice_name")),
            array( "label" => $this->translate('street'), "cols" => array("street1")),
            array( "label" => $this->translate('zip'), "cols" => array("zip")),
            array( "label" => $this->translate('city'), "cols" => array("city")),
            array( "label" => $this->translate('phone'), "cols" => array("phone")),
            array( "label" => $this->translate('mobile'), "cols" => array("mobile")),
        	array( "label" => $this->translate('email'), "cols" => array("email")),
            array( "label" => $this->translate('birthd'), "cols" => array("birthd")),
            array( "label" => $this->translate('sex'), "cols" => array("sex")),
            array( "label" => $this->translate('admission_date'), "cols" => array("admission_date")),
            
    
        );
    }
    
    public function getVersorgerAddress()
    {
        return array(
            array(array("nice_name")),
            array(array("street1")),
            array(array("zip"), array("city")),
        );
    }
    
    
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientMaster';
    

		public function validate($post)
		{


			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$month = "";
			$day = "";
			$year = "";
				
			
			
			$val = new Pms_Validation();

			$decid = Pms_Uuid::decrypt($_GET['id']);
			if($decid < 1)
			{
				
			}
			if(!$val->isstring($post['first_name']))
			{
				$this->error_message['first_name'] = $Tr->translate('firstname_error');
				$error = 1;
			}
			if(!$val->isstring($post['last_name']))
			{
				$this->error_message['last_name'] = $Tr->translate('lastname_error');
				$error = 3;
			}

			if(strtotime($post['admission_date']) > strtotime(date("d.m.Y", time())))
			{
				$this->error_message['admission_date'] = $Tr->translate('err_datefuture');
				$error = 5;
			}
			if(date('Y', strtotime($post['admission_date'])) < '2008')
			{
			    $this->error_message['admission_date'] = $Tr->translate('admission_date_error_before_2008');
			    $error = 15;
			}
			if(strlen($post['admission_date'])){
                $post_adm_array = explode(".",$post['admission_date']);
    			$day = $post_adm_array[0];
    			$month = $post_adm_array[1];
    			$year = $post_adm_array[2];
			}
			
			if(checkdate($month,$day,$year) === false)
			{
			    $this->error_message['admission_date'] = $Tr->translate('admission_date_error_invalid');
			    $error = 16;
			}
			

			//these checks are for readmission only
			if($post['last_discharge_date'])
			{
				if(strtotime(date("Y-m-d H:i", strtotime($post['admission_date'] . ' ' . $post['adm_timeh'] . ':' . $post['adm_timem']))) < strtotime($post['last_discharge_date']))
				{
					$this->error_message['re_admission_date'] = $Tr->translate('admission must be bigger than discharge') . " " . date('d.m.Y H:i', strtotime($post['last_discharge_date']));
					$error = 6;
				}

				//get last discharge date
				//check if patient discharge was edited when form was open
				$lastdate = new PatientReadmission();
				$last_dischargedate = $lastdate->getPatientLastDischargedate($post['ipid']);

				if(strtotime($post['last_discharge_date']) !== strtotime($last_dischargedate[0]['date']))
				{
					$this->error_message['re_admission_date'] = $Tr->translate('discharge_date_edited_while_readmitted') . " (" . date('d.m.Y H:i', strtotime($last_dischargedate[0]['date'])) . ")";
					$error = 7;
				}
			}

			
			
			//these checks are patient edit page
			if($post['previous_discharge_date'])
			{
				if(strtotime(date("Y-m-d H:i", strtotime($post['admission_date'] . ' ' . $post['adm_timeh'] . ':' . $post['adm_timem']))) < strtotime($post['previous_discharge_date']))
				{
					$this->error_message['re_admission_date'] = $Tr->translate('admission must be bigger than discharge') . " " . date('d.m.Y H:i', strtotime($post['previous_discharge_date']));
					$error = 6;
				}

				//get last discharge date
				//check if patient discharge was edited when form was open
				$lastdate = new PatientReadmission();
				$prev_dischargedate = $lastdate->get_patient_previous_dischargedate($post['ipid']);
 
				if(strtotime($post['previous_discharge_date']) !== strtotime($prev_dischargedate))
				{
					$this->error_message['re_admission_date'] = $Tr->translate('discharge_date_edited_while_editing') . " (" . date('d.m.Y H:i', strtotime($prev_dischargedate)) . ")";
					$error = 7;
				}
			}




			if(!$val->isstring($post['birthd']))
			{
				$this->error_message['birthd'] = $Tr->translate('birthdate_error');
				$error = 11;
			}

			if(date('Y', strtotime($post['birthd'])) < '1900')
			{
				$this->error_message['birthd'] = $Tr->translate('birthdate_error_before_1900');
				$error = 12;
			}

			if($val->isstring($post['birthd']))
			{
				list($BirthDay, $BirthMonth, $BirthYear) = explode(".", $post['birthd']);
				$bdt_time = mktime(0, 0, 0, $BirthMonth, $BirthDay, $BirthYear);
				$curr_time = mktime(0, 0, 0, date("m"), date("d"), date("y"));


				if($bdt_time > $curr_time)
				{
					$this->error_message['birthd'] = $Tr->translate('birthdateg_error');
					$error = 14;
				}
			}

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($clientid < 1)
			{
				$this->error_message['client_id'] = $Tr->translate('client_error');
				$error = 2;
			}


			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function InsertData($post)
		{
			$bd_date = explode(".", $post['birthd']);
			$rec_date = explode(".", $post['recording_date']);
			$admin_date = explode(".", $post['admission_date']);

			$ipid = Pms_Uuid::GenerateIpid();
			
			
			$last_ipid_session = new Zend_Session_Namespace('last_ipid');
			$last_ipid_session->isadminvisible =  1;
			$last_ipid_session->ipid =  $ipid;
			
			
			$cust = new PatientMaster();
			$cust->ipid = $ipid;
			$cust->referred_by = $post['referred_by'];
			$cust->orderadmission = $post['orderadmission'];
// 			$cust->recording_date = $rec_date[2] . "-" . $rec_date[1] . "-" . $rec_date[0] . " " . $post['rec_timeh'] . ":" . $post['rec_timem'];
			$cust->recording_date =date("Y-m-d H:i:s");
			$cust->first_name = Pms_CommonData::aesEncrypt($post['first_name']);
			$cust->middle_name = Pms_CommonData::aesEncrypt($post['middle_name']);
			$cust->last_name = Pms_CommonData::aesEncrypt($post['last_name']);
			$cust->street1 = Pms_CommonData::aesEncrypt($post['street1']);
			$cust->street2 = Pms_CommonData::aesEncrypt($post['street2']);
			$cust->zip = Pms_CommonData::aesEncrypt($post['zip']);
			$cust->city = Pms_CommonData::aesEncrypt($post['city']);
			$cust->familydoc_id = $post['hidd_docid'];
			$cust->title = Pms_CommonData::aesEncrypt($post['title']);
			$cust->salutation = Pms_CommonData::aesEncrypt($post['salutation']);
			$cust->phone = Pms_CommonData::aesEncrypt($post['phone']);
			$cust->mobile = Pms_CommonData::aesEncrypt($post['mobile']);
			//$cust->kontactnumber = $post['kontactnumber'];
			//$cust->kontactnumbertype = $post['kontactnumbertype'];
			// TODO-1303
			$cust->is_contact =   empty($post['phone'])  &&  empty($post['mobile']) ? 0 : 1;
			$cust->admission_date = $admin_date[2] . "-" . $admin_date[1] . "-" . $admin_date[0] . " " . $post['adm_timeh'] . ":" . $post['adm_timem'];
			$cust->birthd = $bd_date[2] . "-" . $bd_date[1] . "-" . $bd_date[0];
			$cust->birth_city = Pms_CommonData::aesEncrypt($post['birth_city']);
			$cust->sex = Pms_CommonData::aesEncrypt($post['sex']);
			$cust->height = Pms_CommonData::aesEncrypt(floatval(str_replace(',', '.', str_replace('.', '', $post['height']))));
			$cust->nation = $post['nation'];
			$cust->isstandby = $post['isstandby'];
			$cust->fdoc_caresalone = $post['fdoc_caresalone'];
			$cust->living_will = $post['living_will'];
			$cust->save();
			//die_ancuta($cust->first_name);
			
			if($post['isstandby'] == "1"){
				$patient_standby_details = new PatientStandbyDetails();
				$patient_standby_details->ipid = $ipid;
				$patient_standby_details->date = $admin_date[2] . "-" . $admin_date[1] . "-" . $admin_date[0] . " " . $post['adm_timeh'] . ":" . $post['adm_timem'];;
				$patient_standby_details->date_type = "1";
				$patient_standby_details->comment = "Patient admission";
				$patient_standby_details->save();
				
				$patient_standby_details = new PatientStandby();
				$patient_standby_details->ipid = $ipid;
				$patient_standby_details->start = $admin_date[2] . "-" . $admin_date[1] . "-" . $admin_date[0];
				$patient_standby_details->save();
				
				
					
			}
			
			return $cust;
		}

		public function UpdateData($post)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$bd_date = explode(".", $post['birthd']);
			$rec_date = explode(".", $post['recording_date']);
			$admin_date = explode(".", $post['admission_date']);

			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			$cust->referred_by = $post['referred_by'];
			$cust->orderadmission = $post['orderadmission'];
			$cust->recording_date = $rec_date[2] . "-" . $rec_date[1] . "-" . $rec_date[0] . " " . $post['rec_timeh'] . ":" . $post['rec_timem'];
			$cust->first_name = Pms_CommonData::aesEncrypt($post['first_name']);
			$cust->middle_name = Pms_CommonData::aesEncrypt($post['middle_name']);
			$cust->last_name = Pms_CommonData::aesEncrypt($post['last_name']);
			$cust->street1 = Pms_CommonData::aesEncrypt($post['street1']);
			$cust->street2 = Pms_CommonData::aesEncrypt($post['street2']);
			$cust->zip = Pms_CommonData::aesEncrypt($post['zip']);
			$cust->city = Pms_CommonData::aesEncrypt($post['city']);

			$cust->title = Pms_CommonData::aesEncrypt($post['title']);
			$cust->salutation = Pms_CommonData::aesEncrypt($post['salutation']);
			$cust->phone = Pms_CommonData::aesEncrypt($post['phone']);
			$cust->mobile = Pms_CommonData::aesEncrypt($post['mobile']);
			$cust->email = Pms_CommonData::aesEncrypt($post['email']);
			if(strlen($post['real_contact_number']) > 0)
			{
				$cust->kontactnumber = Pms_CommonData::aesEncrypt($post['phone']);
				$cust->kontactnumbertype = '2'; //contact number from patient
			}
			if(strlen($post['admission_date']) > 0)
			{
				$cust->admission_date = $admin_date[2] . "-" . $admin_date[1] . "-" . $admin_date[0] . " " . $post['adm_timeh'] . ":" . $post['adm_timem'];
			}
			$cust->birthd = $bd_date[2] . "-" . $bd_date[1] . "-" . $bd_date[0];
			$cust->birth_name = Pms_CommonData::aesEncrypt($post['birth_name']);
			$cust->birth_city = Pms_CommonData::aesEncrypt($post['birth_city']);
			$cust->sex = Pms_CommonData::aesEncrypt($post['sex']);
			
			
			$cust->height = Pms_CommonData::aesEncrypt(floatval(str_replace(',', '.', str_replace('.', '', $post['height']))));
			$cust->nation = $post['nation'];
			$cust->fdoc_caresalone = $post['fdoc_caresalone'];
			
			$cust->is_contact = (int)$post['real_contact_number'];
			
			$cust->save();
		}

		public function UpdateContactNumber($number, $type = '1', $patid = false , $caller = null)
		{
		    
		    return; //ispc-2045

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			if($patid)
			{
				$decid = Pms_Uuid::decrypt($patid);
			}
			else
			{
				$decid = Pms_Uuid::decrypt($_GET['id']);
			}

//updated query - stupid find(er)
			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			
			if ( $caller == 'location') {
				$cust->is_contact = true;//ISPC-2045
				$cust->is_contact_Location = true;//$_GET['formid'];//ISPC-2045
			}			
			
			$cust->kontactnumber = Pms_CommonData::aesEncrypt($number);
			$cust->kontactnumbertype = $type;
			$cust->save();

			// ISPC-2024 - removed locations as contact number
			// ISPC-2064 - add location for contacct number 
			if ( $caller == 'location') {
			
				$patloc = Doctrine_Query::create()
				->select('id')
				->from('PatientLocation')
				->where('ipid = ? ' , $cust['ipid'])
				->andWhere('isdelete = ? ' , 0)
				->orderBy('id DESC')
				->limit(1)
				->execute();
				
				if ($patloc && $patloc[0]) {
					$patloc[0]->is_contact = 1;
					$patloc->save();
				}
				
			}
			
			
//		$update = Doctrine_Query::create()
//			->update('PatientMaster')
//			->set('kontactnumber', '"'.Pms_CommonData::aesEncrypt($number).'"')
//			->set('kontactnumbertype', '"'.$type.'"')
//			->set('change_date', '"'.date('Y-m-d H:i:s', time()).'"')
//			->set('change_user', '"'.$userid.'"')
//			->where('id = "'.$decid.'"');
//		$update_res = $update->execute();

			if($patid)
			{
				exit;
			}
		}

		public function DisableContactNumber($patid = null , $caller = null)
		{
		    
		    return; //ispc-2045

			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($patid)
			{
				$decid = Pms_Uuid::decrypt($patid);
			}
			else
			{
				$decid = Pms_Uuid::decrypt($_GET['id']);
			}

			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			
			if ( $caller == 'location') {
				$cust->is_contact = false;//ISPC-2045
				$cust->is_contact_Location = true;//$_GET['formid'];
			}
			
			$cust->kontactnumber = "";
			$cust->kontactnumbertype = "0";
			$cust->save();
			
			// ISPC-2024 - removed locations as contact number
			// ISPC-2064 - add location for contacct number
			
			if ( $caller == 'location') {
				
				$patloc = Doctrine_Query::create()
				->select('id')
				->from('PatientLocation')
				->where('ipid = ? ' , $cust['ipid'])
				->andWhere('isdelete = ? ' , 0)
				->orderBy('id DESC')
				->limit(1)
				->execute();
				
				if ($patloc && $patloc[0]) {
					
					$patloc[0]->is_contact = 0;
					$patloc->save();
				}
			
			}
			
		}

		public function validateFamilyDoc($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();

			if(!$val->isstring($post['familydoc_id']))
			{
				
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function validatePflegedienste($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();

			if(!$val->isstring($post['pflegedienste']))
			{
				$this->error_message['pflegedienste'] = $Tr->translate('pflegedienste_error');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function validateHomecare($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();

			if(!$val->isstring($post['homecare']))
			{
				$this->error_message['homecare'] = $Tr->translate('homecare_error');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function validatePharmacy($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();

			if(!$val->isstring($post['pharmacy']))
			{
				$this->error_message['pharmacy'] = $Tr->translate('pharmacy_error');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function validateSupplier($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();

			if(!$val->isstring($post['supplier']))
			{
				$this->error_message['supplier'] = $Tr->translate('supplier_error');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function validateSpecialist($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();
//			if(!$val->isstring($post['practice'])){
//				$this->error_message['practice']=$Tr->translate('practice_error'); $error=1;
//			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function validateHospiceassociation($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();

			if(!$val->isstring($post['h_association']))
			{
				$this->error_message['h_association'] = $Tr->translate('h_association_error');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function InsertPflegedienste($post)
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);


			$cust = new PatientPflegedienste();
			$cust->ipid = Pms_CommonData::getIpid($decid);
			$cust->pflid = $post['hidd_pflegeid'];
			$cust->save();
		}

		public function UpdateFamilydoc($post)
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);
			if($post['pid'] > 0)
			{
				$decid = $post['pid'];
			}

			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			$cust->familydoc_id = $post['hidd_docid'];
			$cust->fdoc_caresalone = $post['fdoc_caresalone'];
			$cust->save();
		}

		public function UpdateFamilydocQPA($post)
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);
			if($post['pid'] > 0)
			{
				$decid = $post['pid'];
			}

			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			$cust->familydoc_id_qpa = $post['hidd_docid'];
			$cust->fdoc_caresalone = $post['fdoc_caresalone'];
			$cust->save();
		}

		public function UpdatePflegedienste($post)
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);
			if($post['pid'] > 0)
			{
				$decid = $post['pid'];
			}

			$cust = Doctrine::getTable('PatientMaster')->find($decid);
			$cust->pflegedienste = $post['hidd_pflegeid'];
			$cust->pflege_comment = $post['pflege_comment'];
			$cust->save();
		}
		
		public function UpdateLivingWillFrom($post)
		{
			//print_r($post);
			//exit;
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpId($decid);
			$post['living_will_from'] = date('Y-m-d', strtotime($post['living_will_from']));
			$ipid=$post['ipid'];
			
			
			if(strlen($post['living_will_from'])>0 && !empty ($post['living_will_from']))
			{
				
			$q = Doctrine_Query::create()
		     ->update('PatientMaster')
		     ->set('living_will', 1)
		     ->set('living_will_from', "'".$post['living_will_from']."'")
		     ->where("ipid = '".$post['ipid']."'");
		      $q->execute();
			}
			
		}
		
		public function Update_livingwillFrom($post)
		{
			//print_r($post);
			//exit;
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpId($decid);
			
				
			
		
				$q = Doctrine_Query::create()
				->update('PatientMaster')
				->set('living_will',"'".$post."'")
				->where("ipid = '".$ipid."'");
				$q->execute();
			
				
		}
		

		public function getFormatedData($arr)
		{
			for($i = 0; $i < sizeof($arr); $i++)
			{
				if(strlen($arr[$i]) > 0)
				{
					$finalarr[] = $arr[$i];
				}
			}
			return($finalarr);
		}

		public function validatePhysiotherapist($post)
		{
			$Tr = new Zend_View_Helper_Translate();
			$error = 0;
			$val = new Pms_Validation();

			if(!$val->isstring($post['physiotherapist']))
			{
				$this->error_message['physiotherapist'] = $Tr->translate('physiotherapist_error');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function readmit_standby_patient($ipid, $admission_date,$comment = "")
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			//transfered and adapted from old standby readmission from patientoveralllist
			if($ipid && $admission_date)
			{
				//time to check if client wants "L" default shorcut
				$trigger = Doctrine_Query::create()
					->select('*')
					->from('FieldTrigger')
					->where("formid = 1 and triggerid = 9 and event='2' and isdelete=0 and clientid=" . $clientid);
				$trigger = $trigger->fetchArray();

				if($trigger)
				{
					$inputs = unserialize($trigger[0]['inputs']);
					if($inputs['course_type'] == 'L')
					{

						$cust = new PatientCourse();
						$cust->ipid = $ipid;
						$cust->course_date = date("Y-m-d H:i:00", strtotime($admission_date));
						$cust->isstandby = 0;
						$cust->course_type = Pms_CommonData::aesEncrypt($inputs['course_type']);
						$cust->course_title = Pms_CommonData::aesEncrypt($inputs['course_title']);
						$cust->user_id = $userid;
						$cust->save();
					}
				}

//radu save firstadmision date start
				//search admision date in patient readmision
				$q = Doctrine_Query::create()
					->select('*')
					->from('PatientReadmission')
					->where("ipid='" . $ipid . "'")
					->andWhere('date_type = 1')
					->orderBy('date DESC')
					->limit('1');
				$standbydate = $q->fetchArray();
				if(!$standbydate)
				{
					//not found then add
					$patientreadmission = new PatientReadmission();
					$patientreadmission->user_id = $logininfo->userid;
					$patientreadmission->ipid = $ipid;
					$patientreadmission->date = date("Y-m-d H:i", strtotime($admission_date));
					$patientreadmission->date_type = 1; //1 =admission-readmission 2- discharge
					$patientreadmission->save();
				}
				else
				{
					//found then edit
					$q = Doctrine_Query::create()
						->update('PatientReadmission')
						->set('date', '"' . date("Y-m-d H:i:s", strtotime($admission_date)) . '"')
						->where("id='" . $standbydate[0]['id'] . "'");
					$q->execute();
				}
				// radu save firstadmision date end
				
				
				// check if standby data 
				$q_st = Doctrine_Query::create()
				->select('*')
				->from('PatientStandbyDetails')
				->where("ipid='" . $ipid . "'")
				->orderBy('date asc');
				$standbydate_arr = $q_st->fetchArray();
					
				
				
				if($standbydate_arr){
					$update = 1;
					$remove = 0;
					
					if(count($standbydate_arr) == "1" && strtotime($standbydate_arr[0]['date']) > strtotime($admission_date)){
						// if only one entry and the user "sets" the regular admission before the standby admission
						$update = 0;
						$remove = 1;
					}
						
					if($remove == "1"){
						// delete from PatientStandby and from PatientStandbyDetails
					
						$del_old_standby_data = Doctrine_Query::create()
						->delete('PatientStandby ps')
						->where('ps.ipid LIKE "' . $ipid . '"');
						$del_old_standby_data->execute();
					
						$del_old_standby_details= Doctrine_Query::create()
						->delete('PatientStandbyDetails psd')
						->where('psd.ipid LIKE "' . $ipid . '"');
						$del_old_standby_details->execute();
					}

					
					
					// TODO-1348
					//get last standby  admision date
					$last_standby_data = end($standbydate_arr);
					
					$remove_last = 0;
					if(count($standbydate_arr) > 1 && $last_standby_data['date_type'] == "1"  && strtotime($last_standby_data['date']) > strtotime($admission_date) ){
						// if only one entry and the user "sets" the regular admission before the standby admission
						$update = 0;
						$remove_last = 1;
					}
					
					if($remove_last == "1"){
						
						$del_last_standby_data = Doctrine_Query::create()
						->delete('PatientStandbyDetails')
						->where('ipid = ?', $ipid)
						->andWhere('id = ? ',$last_standby_data['id']);
						$del_last_standby_data->execute();
						
						
						$del_last_standby_details= Doctrine_Query::create()
						->delete('PatientStandby')
						->where('ipid = ?', $ipid)
						->andWhere('end = "0000-00-00" ');
						$del_last_standby_details->execute();
					}
					
					if($update == "1"){
						// allow the standby fall to be  closed
						// insert in PatientsStandbyDetails
						$patient_standby_details = new PatientStandbyDetails();
						$patient_standby_details->ipid = $ipid;
						$patient_standby_details->date = date("Y-m-d H:i", strtotime($admission_date));
						$patient_standby_details->date_type = "2";
						$patient_standby_details->comment = $comment;
						$patient_standby_details->save();
					
					
						$q = Doctrine_Query::create()
						->select('*')
						->from('PatientStandbyDetails')
						->where('ipid LIKE "' . $ipid . '"')
						->orderBy('date ASC');
						$q_res = $q->fetchArray();
					
						//"new" patient - data in readmission
						if($q_res)
						{
							$incr = '0';
							foreach($q_res as $k_patient_date => $v_patient_date)
							{
								//date_type switcher
								if($v_patient_date['date_type'] == '1')
								{
									$type = 'start';
								}
								else
								{
									$type = 'end';
								}
					
								$patient_admission[$incr][$type] = $v_patient_date['date'];
					
								//check next item (which is supposed to be discharge) exists
								if($v_patient_date['date_type'] == '1' && !array_key_exists(($k_patient_date + 1), $q_res))
								{
									$patient_admission[$incr]['end'] = '';
								}
					
								//increment when reaching end dates(date_type=2)
								if($v_patient_date['date_type'] == '2')
								{
									$incr++;
								}
							}
						}
					
					
						if(count($patient_admission) > '0')
						{
							$del_old_active_data = Doctrine_Query::create()
							->delete('PatientStandby pa')
							->where('pa.ipid LIKE "' . $ipid . '"');
							$del_old_active_data->execute();
								
							foreach($patient_admission as $k_adm_cycle => $v_cycle_data)
							{
								if(strlen($v_cycle_data['end']) == '0')
								{
									$end_date = '0000-00-00';
								}
								else
								{
									$end_date = date('Y-m-d', strtotime($v_cycle_data['end']));
								}
					
								$cycle_records[] = array(
										'ipid' => $ipid,
										'start' => date('Y-m-d', strtotime($v_cycle_data['start'])),
										'end' => $end_date
								);
							}
								
							$collection = new Doctrine_Collection('PatientStandby');
							$collection->fromArray($cycle_records);
							$collection->save();
						}
					}
					
					
				} else { //if no data in Standby details-  add dataaa

					
					
					
// 					$patientreadmission = new PatientStandbyDetails();
// 					$patientreadmission->ipid = $ipid;
// 					$patientreadmission->date = date("Y-m-d H:i", strtotime($admission_date));
// 					$patientreadmission->date_type = 1; //1 =admission-readmission 2- discharge
// 					$patientreadmission->save();
						
// 					$patientreadmission = new PatientStandby();
// 					$patientreadmission->ipid = $ipid;
// 					$patientreadmission->start = date("Y-m-d", strtotime($admission_date));
// 					$patientreadmission->save();
				}
				
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes("Patient wurde zum " . date("d.m.Y", strtotime($admission_date)) . " aktiviert"));
				//ISPC-2691 Carmen 04.11.2020
				$cust->tabname = Pms_CommonData::aesEncrypt("aufnahme");
				//--
				$cust->user_id = $logininfo->userid;
				$cust->save();

				// Patient activation  course - admission date START
				$activation_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y H:i', strtotime($admission_date)) . " Uhr";
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt($activation_date_course);
				//ISPC-2691 Carmen 04.11.2020
				$cust->tabname = Pms_CommonData::aesEncrypt("aufnahme_date");
				//--
				$cust->user_id = $logininfo->userid;
				$cust->save();
				// Patient activation course - admission date END

				$q = Doctrine_Query::create()
					->update('PatientCourse')
					->set('isstandby', 0)
					->where("ipid='" . $ipid . "'");
				$q->execute();

				// Added by Ancuta :: 06-11-2013
				$cucst = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
				$cucst->admission_date = date("Y-m-d H:i", strtotime($admission_date));
				$cucst->isstandby = "0";
				$cucst->save();

				if($cucst->id)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		public function readmit_discharged_patient($ipid, $admission_date)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($ipid && $admission_date)
			{
				// Patient readmission course - admission date START
				$readmission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y H:i', strtotime($admission_date)) . " Uhr";
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt($readmission_date_course);
				//ISPC-2691 Carmen 04.11.2020
				$cust->tabname = Pms_CommonData::aesEncrypt("aufnahme_date");
				//--
				$cust->user_id = $userid;
				$cust->save();
				// Patient readmission course - admission date END
				//@Radu: moved this from dischargelistaction (neaufnahme bug, Ancuta, 14.07.2011) [START]
				$ptm = Doctrine_Core::getTable('PatientMaster')->findOneByIpid($ipid);
				if($ptm)
				{
					$ptm->isdischarged = 0;
					$ptm->isarchived = 0;
					$ptm->save();
				}

				$dismiss = Doctrine::getTable('PatientDischarge')->findBy('ipid', $ipid);

				if($dismiss)
				{
					$dismarr = $dismiss->toArray();
				}

				if(count($dismarr) > 0)
				{
					$pr = Doctrine::getTable('PatientReadmission')->findByIpidAndDateAndDateType($ipid, $dismarr[0]['discharge_date'], '2');
					$prr = $pr->toArray();

					if(count($prr) == 0)
					{//not found then add
						$patientreadmission = new PatientReadmission();
						$patientreadmission->user_id = $userid;
						$patientreadmission->ipid = $ipid;
						$patientreadmission->date = $dismarr[0]['discharge_date'];
						$patientreadmission->date_type = 2; //1 =admission-readmission 2- discharge 
						$patientreadmission->save();
					}

					/* ------------------------------- DISCHARGE set isdelete = 1 DON'T DELETE --------------------------------------------------------- */
					$q = Doctrine_Query::create()
						->update('PatientDischarge')
						->set('isdelete', '1')
						->where("ipid='" . $ipid . "'");
					$q->execute();
				}

				$patreadm = Doctrine_Query::create()
					->select('*')
					->from('PatientReadmission')
					->where('ipid ="' . $ipid . '"')
					->orderBy('id ASC');
				$patientreadmissionarr = $patreadm->fetchArray();
				$patientlastrecord = end($patientreadmissionarr);

				if($patientlastrecord['date_type'] == "2" || count($patientreadmissionarr) == 0)
				{
					//@Radu: moved this from dischargelistaction (neaufnahme bug, Ancuta, 14.07.2011) [END]
					//radu save firstadmision date start
					//get first admision date from patient master
					$pm = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
					if($pm)
					{
						$patientmasterarray = $pm->toArray();
					}

					//search admision date in patient readmision
					$pr = Doctrine::getTable('PatientReadmission')->findOneByIpidAndDateAndDateType($ipid, $patientmasterarray['admission_date'], "1");
					if(!$pr)
					{
						//not found then add
						$patientreadmission = new PatientReadmission();
						$patientreadmission->user_id = $userid;
						$patientreadmission->ipid = $ipid;
						$patientreadmission->date = $patientmasterarray['admission_date'];
						$patientreadmission->date_type = 1; //1 =admission-readmission 2- discharge
						$patientreadmission->save();
					}

					//radu save firstadmision date end
					//radu formone active time patient (readmission new table) start

					if(strlen($admission_date) > 0)
					{
						$readmission_date = date('Y-m-d H:i:s', strtotime($admission_date));
					}

					$patientreadmission = new PatientReadmission();
					$patientreadmission->user_id = $userid;
					$patientreadmission->ipid = $ipid;
					$patientreadmission->date = $readmission_date;
					$patientreadmission->date_type = 1; //1 =admission-readmission 2- discharge
					$patientreadmission->save();

					if($pm)
					{
						$pm->admission_date = date('Y-m-d H:i:s', strtotime($admission_date));
						$pm->save();
					}
				}
				/* ------------------------------- Patients steps set isdelete = 1 DON'T DELETE --------------------------------------------------------- */
				$psteps = new PatientSteps();
				$patient_steps_array = $psteps->get_patient_steps($ipid);

				if($patient_steps_array || !empty($patient_steps_array))
				{
					$query = Doctrine_Query::create()
						->update('PatientSteps')
						->set("isdelete", "1")
						->where("ipid LIKE '" . $ipid . "'");
					$query->execute();
				}
				/* --------------------------------------------------------------------------------------------------------------------------------- */
				$pl = new PatientLocation();
				$larr = $pl->getLastLocationDataFromAdmissionUpdate($ipid);

				if(strlen($admission_date) > '0')
				{
					$current_time = date('Y-m-d H:i:s', strtotime($admission_date));
				}
				else
				{
					$current_time = date('Y-m-d H:i:s', time());
				}

				if($larr[0]['valid_till'] != "0000-00-00 00:00:00" && !empty($larr[0]['valid_till']))
				{
					$pl->clientid = $clientid;
					$pl->ipid = $ipid;
					$pl->location_id = 0;
					$pl->discharge_location = 1;
					$pl->valid_from = $larr[0]['valid_till'];
					$pl->valid_till = $current_time;
					$pl->save();
				}

				$comment = "Patient wurde wieder aufgenommen";

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt($comment);
				//ISPC-2691 Carmen 04.11.2020
				$cust->tabname = Pms_CommonData::aesEncrypt("aufnahme");
				//--
				$cust->user_id = $userid;
				$cust->save();

				if($pm->id)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		public function move_to_standby_discharged_patient($ipid, $admission_date,$comment = "")
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($ipid && $admission_date)
			{
				// Patient readmission course - admission date START
				$readmission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y H:i', strtotime($admission_date)) . " Uhr";
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt($readmission_date_course);
				//ISPC-2691 Carmen 04.11.2020
				$cust->tabname = Pms_CommonData::aesEncrypt("aufnahme_date");
				//--
				$cust->user_id = $userid;
				$cust->save();
				// Patient readmission course - admission date END
				//@Radu: moved this from dischargelistaction (neaufnahme bug, Ancuta, 14.07.2011) [START]
				$ptm = Doctrine_Core::getTable('PatientMaster')->findOneByIpid($ipid);
				if($ptm)
				{
					$ptm->isstandby = '1';
					$ptm->isdischarged = 0;
					$ptm->isarchived = 0;
					$ptm->traffic_status = '1';
					$ptm->save();
				}

				// add data in standby tables details
				$patient_standby_int = new PatientStandby();
				$patient_standby_int->ipid = $ipid;
				$patient_standby_int->start = date("Y-m-d", strtotime($admission_date));
				$patient_standby_int->save();
				
				// insert in PatientsStandbyDetails
				$comment = "Patient was moved from discharge to standby(ICON? )";
				$patient_standby_details = new PatientStandbyDetails();
				$patient_standby_details->ipid = $ipid;
				$patient_standby_details->date = date("Y-m-d H:i", strtotime($admission_date));
				$patient_standby_details->date_type = "1";
				$patient_standby_details->comment = $comment;
				$patient_standby_details->save();
					
					
				
				
				
				
				$dismiss = Doctrine::getTable('PatientDischarge')->findBy('ipid', $ipid);

				if($dismiss)
				{
					$dismarr = $dismiss->toArray();
				}

				if(count($dismarr) > 0)
				{
					$pr = Doctrine::getTable('PatientReadmission')->findByIpidAndDateAndDateType($ipid, $dismarr[0]['discharge_date'], '2');
					$prr = $pr->toArray();

					if(count($prr) == 0)
					{//not found then add
						$patientreadmission = new PatientReadmission();
						$patientreadmission->user_id = $userid;
						$patientreadmission->ipid = $ipid;
						$patientreadmission->date = $dismarr[0]['discharge_date'];
						$patientreadmission->date_type = 2; //1 =admission-readmission 2- discharge
						$patientreadmission->save();
					}

					/* ------------------------------- DISCHARGE set isdelete = 1 DON'T DELETE --------------------------------------------------------- */
					$q = Doctrine_Query::create()
						->update('PatientDischarge')
						->set('isdelete', '1')
						->where("ipid='" . $ipid . "'");
					$q->execute();
				}

				$patreadm = Doctrine_Query::create()
					->select('*')
					->from('PatientReadmission')
					->where('ipid ="' . $ipid . '"')
					->orderBy('id ASC');
				$patientreadmissionarr = $patreadm->fetchArray();
				$patientlastrecord = end($patientreadmissionarr);

				if($patientlastrecord['date_type'] == "2" || count($patientreadmissionarr) == 0)
				{
					//@Radu: moved this from dischargelistaction (neaufnahme bug, Ancuta, 14.07.2011) [END]
					//radu save firstadmision date start
					//get first admision date from patient master
					$pm = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
					if($pm)
					{
						$patientmasterarray = $pm->toArray();
					}

					//search admision date in patient readmision
					$pr = Doctrine::getTable('PatientReadmission')->findOneByIpidAndDateAndDateType($ipid, $patientmasterarray['admission_date'], "1");
					if(!$pr)
					{
						//not found then add
						$patientreadmission = new PatientReadmission();
						$patientreadmission->user_id = $userid;
						$patientreadmission->ipid = $ipid;
						$patientreadmission->date = $patientmasterarray['admission_date'];
						$patientreadmission->date_type = 1; //1 =admission-readmission 2- discharge
						$patientreadmission->save();
					}

					//radu save firstadmision date end
					//radu formone active time patient (readmission new table) start

					if(strlen($admission_date) > 0)
					{
						$readmission_date = date('Y-m-d H:i:s', strtotime($admission_date));
					}

					$patientreadmission = new PatientReadmission();
					$patientreadmission->user_id = $userid;
					$patientreadmission->ipid = $ipid;
					$patientreadmission->date = $readmission_date;
					$patientreadmission->date_type = 1; //1 =admission-readmission 2- discharge
					$patientreadmission->save();

					if($pm)
					{
						$pm->admission_date = date('Y-m-d H:i:s', strtotime($admission_date));
						$pm->save();
					}
				}
				/* ------------------------------- Patients steps set isdelete = 1 DON'T DELETE --------------------------------------------------------- */
				$psteps = new PatientSteps();
				$patient_steps_array = $psteps->get_patient_steps($ipid);

				if($patient_steps_array || !empty($patient_steps_array))
				{
					$query = Doctrine_Query::create()
						->update('PatientSteps')
						->set("isdelete", "1")
						->where("ipid LIKE '" . $ipid . "'");
					$query->execute();
				}
				/* --------------------------------------------------------------------------------------------------------------------------------- */
				$pl = new PatientLocation();
				$larr = $pl->getLastLocationDataFromAdmissionUpdate($ipid);

				if(strlen($admission_date) > '0')
				{
					$current_time = date('Y-m-d H:i:s', strtotime($admission_date));
				}
				else
				{
					$current_time = date('Y-m-d H:i:s', time());
				}

				if($larr[0]['valid_till'] != "0000-00-00 00:00:00" && !empty($larr[0]['valid_till']))
				{
					$pl->clientid = $clientid;
					$pl->ipid = $ipid;
					$pl->location_id = 0;
					$pl->discharge_location = 1;
					$pl->valid_from = $larr[0]['valid_till'];
					$pl->valid_till = $current_time;
					$pl->save();
				}

// 				$comment = "Patient wurde wieder aufgenommen";

				$Tr = new Zend_View_Helper_Translate();
				$comment = $Tr->translate('Patient MOVED TO STSANBY');
				
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt($comment);
				//ISPC-2691 Carmen 04.11.2020
				$cust->tabname = Pms_CommonData::aesEncrypt("aufnahme");
				//--
				$cust->user_id = $userid;
				$cust->save();

				if($pm->id)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		
		
		
		public function discharged2standby($ipid, $admission_date,$comment)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			if($ipid && $admission_date)
			{
				// add data in standby tables details
				$patient_standby_int = new PatientStandby();
				$patient_standby_int->ipid = $ipid;
				$patient_standby_int->start = date("Y-m-d", strtotime($admission_date));
				$patient_standby_int->save();
				
				// insert in PatientsStandbyDetails
				$patient_standby_details = new PatientStandbyDetails();
				$patient_standby_details->ipid = $ipid;
				$patient_standby_details->date = date("Y-m-d H:i", strtotime($admission_date));
				$patient_standby_details->date_type = "1";
				$patient_standby_details->comment = $comment;
				$patient_standby_details->save();
				 
				if($patient_standby_details->id)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}
		

		public function send2standbydeleted($ipid,$patient_details, $comment="")
		{
			return true;
			/* $logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
		
			//transfered and adapted from old standby readmission from patientoveralllist
			if($ipid)
			{
				$admission_date = date('Y-m-d H:i:s');
				// close standby 

				// check if standby data
				$q_st = Doctrine_Query::create()
				->select('*')
				->from('PatientStandbyDetails')
				->where("ipid='" . $ipid . "'")
				->orderBy('date asc');
				$standbydate_arr = $q_st->fetchArray();
				
				if($standbydate_arr)
				{
					$patient_standby_details = new PatientStandbyDetails();
					$patient_standby_details->ipid = $ipid;
					$patient_standby_details->date = date("Y-m-d H:i", strtotime($admission_date));
					$patient_standby_details->date_type = "2";
					$patient_standby_details->comment = $comment;
					$patient_standby_details->save();
						
					// REFRESH PATEINT STANDBY
					PatientMaster::get_patient_standby_admissions($ipid);
						
				} else { //if no data in Standby details-  add data
					
					// start standby  
					// get last admission details 
					$last_admission = $patient_details['admission_date'];
					$patientreadmission_st = new PatientStandbyDetails();
					$patientreadmission_st->ipid = $ipid;
					$patientreadmission_st->date = date("Y-m-d H:i", strtotime($last_admission));
					$patientreadmission_st->date_type = 1; //1 =admission-readmission 2- discharge
					$patientreadmission_st->comment = 'standby2standbydelete - patientoveralllist';
					$patientreadmission_st->save();
					
					$patientreadmission_std = new PatientStandbyDetails();
					$patientreadmission_std->ipid = $ipid;
					$patientreadmission_std->date = date("Y-m-d H:i", strtotime($admission_date));
					$patientreadmission_std->date_type = 2; //1 =admission-readmission 2- discharge
					$patientreadmission_std->comment = 'standby2standbydelete - patientoveralllist';
					$patientreadmission_std->save();
				
					
					$patientreadmission = new PatientStandby();
					$patientreadmission->ipid = $ipid;
					$patientreadmission->start = date("Y-m-d", strtotime($last_admission));
					$patientreadmission->end = date("Y-m-d", strtotime($admission_date));
					$patientreadmission->save();
				}
				
				// open standby delete 
				$patientreadmission = new PatientStandbyDelete();
				$patientreadmission->ipid = $ipid;
				$patientreadmission->start = date("Y-m-d", strtotime($admission_date));
				$patientreadmission->save();
				
				// insert in PatientsStandbyDetails
				$patient_standby_details = new PatientStandbyDeleteDetails();
				$patient_standby_details->ipid = $ipid;
				$patient_standby_details->date = date("Y-m-d H:i", strtotime($admission_date));
				$patient_standby_details->date_type = "1";
				$patient_standby_details->comment = $comment;
				$patient_standby_details->save();
				
				return true;
			} */
		}
		

		public function send2standby($ipid, $patient_details, $comment)
		{
			return true;
			/* $logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;

			// if no data -  add data as standby start   
			
			//transfered and adapted from old standby readmission from patientoveralllist
			if($ipid)
			{
				$admission_date  = date('d.m.Y H:i:s');
				
				
				$q_st = Doctrine_Query::create()
				->select('*')
				->from('PatientStandbyDetails')
				->where('ipid LIKE "' . $ipid . '"')
				->orderBy('date ASC');
				$standby_data = $q_st->fetchArray();
				
				if(!standby_data){
					//add stanby data
					
					// get last admission details
					$last_admission = $patient_details['admission_date'];
					$patientreadmission_st = new PatientStandbyDetails();
					$patientreadmission_st->ipid = $ipid;
					$patientreadmission_st->date = date("Y-m-d H:i", strtotime($last_admission));
					$patientreadmission_st->date_type = 1; //1 =admission-readmission 2- discharge
					$patientreadmission_st->comment = 'standby2standbydelete - patientoveralllist';
					$patientreadmission_st->save();
						
					$patientreadmission_std = new PatientStandbyDetails();
					$patientreadmission_std->ipid = $ipid;
					$patientreadmission_std->date = date("Y-m-d H:i", strtotime($admission_date));
					$patientreadmission_std->date_type = 2; //1 =admission-readmission 2- discharge
					$patientreadmission_std->comment = 'standby2standbydelete - patientoveralllist';
					$patientreadmission_std->save();
					
						
					$patientreadmission = new PatientStandby();
					$patientreadmission->ipid = $ipid;
					$patientreadmission->start = date("Y-m-d", strtotime($last_admission));
					$patientreadmission->end = date("Y-m-d", strtotime($admission_date));
					$patientreadmission->save();
					
					
				}
				
				// --------------------------
				// end standby delete period
				// ------------------------
				$q = Doctrine_Query::create()
				->select('*')
				->from('PatientStandbyDeleteDetails')
				->where('ipid LIKE "' . $ipid . '"')
				->orderBy('date ASC');
				$q_res = $q->fetchArray();
				
				//"new" patient - data in readmission
				if($q_res)
				{
					// allow the standby fall to be  closed
					// insert in PatientsStandbyDetails
					$patient_standby_details = new PatientStandbyDeleteDetails();
					$patient_standby_details->ipid = $ipid;
					$patient_standby_details->date = date("Y-m-d H:i:s", strtotime($admission_date));
					$patient_standby_details->date_type = "2";
					$patient_standby_details->comment = $comment;
					$patient_standby_details->save();
					
					
 					// REFRESH PATEINT STANDBY
					PatientMaster::get_patient_standbydelete_admissions($ipid);
				} else{
					// do nothig ath the moment 
				}	
				
				// -----------------
				// start standby period 
				// -----------------
				
 
				// add data in standby tables details
				$patient_standby_int = new PatientStandby();
				$patient_standby_int->ipid = $ipid;
				$patient_standby_int->start = date("Y-m-d", strtotime($admission_date));
				$patient_standby_int->save();
				
				// insert in PatientsStandbyDetails
				$patient_standby_details = new PatientStandbyDetails();
				$patient_standby_details->ipid = $ipid;
				$patient_standby_details->date = date("Y-m-d H:i:s", strtotime($admission_date));
				$patient_standby_details->date_type = "1";
				$patient_standby_details->comment = $comment;
				$patient_standby_details->save();
				
				
				return true;
			}
		  */
		}
		

			
    /**
     * @cla
     * + update on 10.07.2018, all the fields
     * + update belongsTo
     * + update on 19.12.2018 , do not save and disable if ! isadminvisible
     * 
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_patient_details ($values =  array() , $elementsBelongTo = null) 
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName , "create_form_isValid");
         
        $this->mapSaveFunction($__fnName , "save_form_patient_details");
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Patient Details'));
        $subform->setAttrib("class", "label_same_size {$__fnName}");
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        $subform->addElement('hidden', 'patient_id', array(
            'label'        => null,
            'value'        => ! empty($this->_patientMasterData['id']) ? Pms_Uuid::encrypt($this->_patientMasterData['id']) : null,
            'required'     => false,
            'readonly'     => true,
            'filters'      => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
            ),
        ));
        
        
        $subform->addElement('select', 'referred_by', array(
            'label'      => 'referredby',
            'multiOptions' => $this->getReferredByArray(),
            'required'   => false,
            'value'    => ! empty($this->_patientMasterData['admission_date']) ? $this->_patientMasterData['referred_by'] : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'), 
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        
        //adm_timeh
        //adm_timem
        
        $subform->addElement('note', 'admission_date', array(
            'label'      => $this->translate('Date of recording:'),
            // 	        'placeholder' => 'Search my date',
            'required'   => true,
            'value'    => ! empty($this->_patientMasterData['admission_date']) ? date('d.m.Y H:i', strtotime($this->_patientMasterData['admission_date'])) : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'class'    => 'date',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3, 'openOnly' => true)),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true)),
            ),
        ));
        /*
        $subform->addElement('text', 'admission_date', array(
            'label'      => $this->translate('Date of recording:'),
            // 	        'placeholder' => 'Search my date',
            'required'   => true,
            'value'    => ! empty($this->_patientMasterData['admission_date']) ? date('d.m.Y', strtotime($this->_patientMasterData['admission_date'])) : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'class'    => 'date',
            'decorators' => array(
                'ViewHelper',
                array('Errors'), 
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3, 'openOnly' => true)),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true)),
            ),
        ));
        $subform->addElement('text', '_admission_date_time', array(
            'label'      => null,
            // 	        'placeholder' => 'Search my date',
            'required'   => true,
            'value'    => ! empty($this->_patientMasterData['admission_date']) ? date('H:i', strtotime($this->_patientMasterData['admission_date'])) : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'class'    => 'timepicker',
            'decorators' => array(
                'ViewHelper',
                array('Errors'), 
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
            ),
        ));
        */
         
        $subform->addElement('text', 'first_name', array(
            'label'      => $this->translate('first_name'),
            'required'   => true,
            'value'    => ! empty($this->_patientMasterData['first_name']) ? $this->_patientMasterData['first_name'] : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'last_name', array(
            'label'      => $this->translate('last_name'),
            'value'    => ! empty($this->_patientMasterData['last_name']) ? $this->_patientMasterData['last_name'] : null,
            'required'   => true,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'birthd', array(
            'label'      => $this->translate('birthd'),
            'value'    => ! empty($this->_patientMasterData['birthd']) ? $this->_patientMasterData['birthd'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            //'filters'    => array(new Zend_Filter_LocalizedToNormalized(array("locale" => "de", "date_format"=>'d.m.Y'))),

            'validators' => array('NotEmpty', new Zend_Validate_Date(array("format"=>'dd.MM.Y'))),
            
            'class'    => 'date date_range_150',//TODO-2367 Added class by Ancuta 20.06.2019
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'street1', array(
            'label'      => $this->translate('street1'),
            'value'    => ! empty($this->_patientMasterData['street1']) ? $this->_patientMasterData['street1'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'zip', array(
            'label'      => $this->translate('zip'),
            'value'    => ! empty($this->_patientMasterData['zip']) ? $this->_patientMasterData['zip'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'data-livesearch'   => 'zip',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'city', array(
            'label'      => $this->translate('city'),
            'value'    => ! empty($this->_patientMasterData['city']) ? $this->_patientMasterData['city'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'data-livesearch'   => 'city',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'phone', array(
            'label'      => $this->translate('phone'),
            'value'    => ! empty($this->_patientMasterData['phone']) ? $this->_patientMasterData['phone'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('text', 'mobile', array(
            'label'      => 'mobile',
            'value'    => ! empty($this->_patientMasterData['mobile']) ? $this->_patientMasterData['mobile'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('text', 'email', array(
        		'label'        => 'email',
        		'value'        => ! empty($this->_patientMasterData['email']) ? $this->_patientMasterData['email'] : null,
        		'required'   => false,
        		'filters'    => array('StringTrim'),
        		'validators' => array('EmailAddress'),
        		'decorators' => array(
        				'ViewHelper',
        				array('Errors'),
        				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
        				array('Label', array('tag' => 'td')),
        				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
        		),
        ));
        
        $subform->addElement('text', 'birth_name', array(
            'label'      => 'birthname',
            'value'    => ! empty($this->_patientMasterData['birth_name']) ? $this->_patientMasterData['birth_name'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('text', 'birth_city', array(
            'label'      => 'birthcity',
            'value'    => ! empty($this->_patientMasterData['birth_city']) ? $this->_patientMasterData['birth_city'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('select', 'sex', array(
            'multiOptions' => $this->getSexArray(),
            'label'      => 'sex',
            'value'    => strlen ($this->_patientMasterData['sex']) > 0 ? $this->_patientMasterData['sex'] : null,
            'required'   => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>3)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        
        $af_pr = new Application_Form_PatientReligions([
	        '_patientMasterData'    => $this->_patientMasterData,
	        '_block_name'           => $this->_block_name,
	        '_clientForms'          => $this->_clientForms,
	        '_clientModules'        => $this->_clientModules,
	        '_client'               => $this->_client,
	        '_block_feedback_values'  => $this->_block_feedback_values,
            'elementsBelongTo'  => 'PatientReligions',
        ]);
        $religions = $af_pr->create_form_religion($this->_patientMasterData['PatientReligions'][0], 'PatientReligions');
        $religions->removeDecorator('HtmlTag');
        $religions->removeDecorator('Fieldset');
        
        $religions->getElement('religion')
        ->setAttrib('helper', 'formSelect')
        ->setLabel('religion')
        ->setDecorators(array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>1)),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
        ));
        
        $religionFreetext = $religions->getElement('religionfreetext')
        ->setDecorators(array(
            'ViewHelper',
            array('Errors'),
            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>2)),
            array(array('row' => 'HtmlTag'), array('tag' => 'tr',  'closeOnly' => true)),
        ));
        
        $subform->addSubForm($religions, 'PatientReligions');
        
        
        
        $subform->addElement('checkbox', 'fdoc_caresalone', array(
            'value'    => isset($this->_patientMasterData['fdoc_caresalone']) ? $this->_patientMasterData['fdoc_caresalone'] : null,            
            'label'        => 'familycare',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('Int'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        $subform->addElement('checkbox', 'is_contact', array(
            'value'    => isset($this->_patientMasterData['is_contact']) ? $this->_patientMasterData['is_contact'] : null,
            'label'        => 'is the contact phone number',
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('Int'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        
        /*
         * disable if not admin visible, if this is a XXXXXXXX
         */
        if($this->logininfo->usertype == 'SA' && $this->_patientMasterData['isadminvisible'] != 1) {
            $subform->setAttrib('disabled', true);
        }
        
        return $this->filter_by_block_name($subform, $__fnName);
    }		
		
    /**
     * @cla
     * + update on 19.12.2018 , do not save and disable if ! isadminvisible
     * 
     * @param string $ipid
     * @param unknown $data
     * @return Doctrine_Record , void|boolean only if you error
     */
    public function save_form_patient_details ($ipid =  null , $data = array())
    {
        if (empty($ipid) || empty($data)) {
            return; //fail-safe
        }
        
        /*
         * no dot save if not admin visible, if this is a XXXXXXXX
         */
        if($this->logininfo->usertype == 'SA' && $this->_patientMasterData['isadminvisible'] != 1) {
            return; //fail-safe
        }
        
        /*
        if ( ! empty($data['admission_date'])) {
            $date = new Zend_Date($data['admission_date']);
            $data['admission_date'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
            
            if ( ! empty($data['_admission_date_time'])) {
                $data['admission_date'] .= " " . $data['_admission_date_time'];
            }
            
            
        } else {
            $data['admission_date'] = null;
        }
        */
        if (isset($data['admission_date']))
            unset($data['admission_date']);
        
        
        if ( ! empty($data['birthd']) && Zend_Date::isDate($data['birthd'])) {
            $date = new Zend_Date($data['birthd']);
            $data['birthd'] = $date->toString(Zend_Date::YEAR.'-'.Zend_Date::MONTH.'-'.Zend_Date::DAY);
        } else {
            $data['birthd'] = null;
        }
        
        /**
         * if you add relation PatientReligions to Patientmaster you can then remove this is
         */
        if (isset($data['PatientReligions'])) {
            $af_pr = new Application_Form_PatientReligions();
            $af_pr->save_form_religion($ipid, $data['PatientReligions']);
        }
        
        //ISPC-2807 Lore 24.02.2021
        $patient_details_toVerlauf = $this->PatientStammdaten_toVerlauf($data);
        //.
        
        $entity = new PatientMaster();
        return $entity->findOrCreateOneBy('ipid', $ipid, $data);
        
    }

    public function getSexArray()
    {
        return array(
            "" => $this->translate('gender_select'), 
            0 => $this->translate('divers'),  //ISPC-2442 @Lore   30.09.2019
            1 => $this->translate('male'), 
            2 => $this->translate('female'),
        );
        
    }
    
    public function getReferredByArray()
    {
        //ISPC-2612 Ancuta 29.06.2020
        $client_is_follower_ref = ConnectionMasterTable::_check_client_connection_follower('PatientReferredBy',$this->logininfo->clientid);
        
        $qrq = Doctrine_Query::create()
        ->select('id, referred_name')
        ->from('PatientReferredBy')
        ->where("clientid = ?",  $this->logininfo->clientid)
        ->andWhere('isdelete = 0');
        if($client_is_follower_ref){//ISPC-2612 Ancuta 29.06.2020
            $qrq->andWhere('connection_id is NOT null');
            $qrq->andWhere('master_id is NOT null');
        }
        $qrq->orderBy('referred_name ASC');
        $qr = $qrq->fetchArray()
        ;
        $result = array("" => "");
        
        foreach ($qr as $row) {
            $result [ $row['id'] ] = $row['referred_name'];
        }
        
        return $result;
    }
    
    public function getNationalityArray ()
    {
        $result = array("" => "");
        return $result;
    }
    
    public function getReligionArray ()
    {

        return PatientReligions::getReligionsNames(true);        
    }
    /**
     * @cla
     *
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     */
    public function create_form_patient_hospiz_hospizverein_sapv_aapv ($values =  array() , $elementsBelongTo = null)
    {
        
//         $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
         
        $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_hospiz_hospizverein_sapv_aapv");
         
    
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend('Hospiz - Hospizverein - SAPV/AAPV');
        $subform->setAttrib("class", "label_same_size inlineEdit " . __FUNCTION__);
         
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        
        $assigned_userIDS = [];
        if ( ! empty($this->_patientMasterData['PatientQpaMapping'])) {
            $assigned_userIDS = array_column($this->_patientMasterData['PatientQpaMapping'], 'userid');
        }
        
        
        $objuser = new User();
        $hospizUsers = $objuser->fetchHospizUsers($this->logininfo->clientid);
        $hospizvereinUsers = $objuser->fetchHospizvereinUsers($this->logininfo->clientid);
        
        // HOSPIZ USERS 7
        $ishospiz_onChange = null;
        if (count($hospizUsers)) {
            $ishospiz_onChange = 'if (this.checked) {createSubFormDialog("set_ishospiz_visitors_addnewDialogHtml", this);}';
            
            $all_hospizUserIDS = array_column($hospizUsers, 'id');
            $hasAssignedIDS = array_intersect($all_hospizUserIDS, $assigned_userIDS);
            if ( ! empty($hasAssignedIDS)) {
                $ishospiz_onChange .= 'else {remove_ishospiz_ishospizverein_visitors("removehsusers", this);}';
            }
        }
        
        // hospizverein USERS 10
        $ishospizverein_onChange = null;
        if (count($hospizvereinUsers)) {
            $ishospizverein_onChange = 'if (this.checked) {createSubFormDialog("set_ishospizverein_visitors_addnewDialogHtml", this);}';
        
            $all_hospizvereinUserIDS = array_column($hospizvereinUsers, 'id');
            $hasAssignedIDS = array_intersect($all_hospizvereinUserIDS, $assigned_userIDS);
            if ( ! empty($hasAssignedIDS)) {
                $ishospizverein_onChange .= 'else {remove_ishospiz_ishospizverein_visitors("removehvusers", this);}';
            }
        }
        
    
//         $subform->addElement('hidden', 'patient_id', array(
//             'label'        => null,
//             'value'        => ! empty($this->_patientMasterData['id']) ? Pms_Uuid::encrypt($this->_patientMasterData['id']) : null,
//             'required'     => false,
//             'readonly'     => true,
//             'filters'      => array('StringTrim'),
//             'decorators' => array(
//                 'ViewHelper',
//                 array(array('data' => 'HtmlTag'), array('tag' => 'td')),
//                 array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
//             ),
//         ));
        
        $subform->addElement('checkbox', 'aapvsapv', array(
            'checkedValue'    => '1',
            'uncheckedValue'  => '0',
            'label'      => 'AAPV / SAPV',
            'required'   => false,
            'value' => 1,
            'decorators'   => array(
                'ViewHelper',
                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
        $subform->addElement('checkbox', 'ishospiz', array(
            'checkedValue'    => '1',
            'uncheckedValue'  => '0',
            'label'      => 'Hospiz',
            'required'   => false,
            'value' => isset($this->_patientMasterData['ishospiz']) ? $this->_patientMasterData['ishospiz'] : 0,
            'decorators'   => array(
                'ViewHelper',
                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            
            'onChange' => $ishospiz_onChange,
            
        ));
        
        $subform->addElement('checkbox', 'ishospizverein', array(
            'checkedValue'    => '1',
            'uncheckedValue'  => '0',
            'label'      => 'Hospizverein',
            'required'   => false,
            'value' =>  isset($this->_patientMasterData['ishospizverein']) ? $this->_patientMasterData['ishospizverein'] : 0,
            'decorators'   => array(
                'ViewHelper',
                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            
            'onChange' => $ishospizverein_onChange,
        ));
        
        
        return $this->filter_by_block_name($subform, __FUNCTION__);
        
    }
    
    
    /**
     * children of create_form_patient_hospiz_hospizverein_sapv_aapv
     * is displayed as a dialog when ishospiz is checked
     *  
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form
     */
    public function create_form_patient_hospiz_hospizverein_sapv_aapv_set_ishospiz_visitors ($values =  array() , $elementsBelongTo = null)
    {
    
//         $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
         
        $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_hospiz_hospizverein_sapv_aapv");
         
        
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table', 'class' => 'firstStep'));
        $subform->setLegend('');
        $subform->setAttrib("class", "label_same_size " . __FUNCTION__);
        
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
        
        $subform->addElement('note', 'note1', array(
            'value' => $this->translate('Sie haben dem Patienten eine neue Gruppe zugeordnet. Möchten Sie auch Benutzer dieser Gruppe Rechte für diesen Patienten geben ?'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        
        ));
        
        $subform->addElement('button', 'Nein', array(
            'value' => 'Nein',
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'style' => 'text-align: right;padding: 10px;')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true,)),
            ),
            'class' => "ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only",
            'style' => "width: 80px; height:30px",
            
            'onClick' => 'try{closeSubFormDialog(this);}catch(e){}; return false;',
            
        
        ));
        $subform->addElement('button', 'Ja', array(
            'value' => 'Ja',
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
            ),
            'class' => "ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only",
            'style' => "width: 80px; height:30px",
            
            'onClick' => '$(".firstStep", $(this).parents("fieldset")).hide();$(".secondStep", $(this).parents("fieldset")).show();',
            
        ));
        
        
        
        $subform2 = $this->subFormTable(array(
            'columns' => [
                $this->translate('#'),
                $this->translate('user'),
            ],
            'class' => 'secondStep display_none',
            //'id' => ''
        ));
        $subform2->removeDecorator('Fieldset');
        
        // HOSPIZ USERS
        //  7 == HOSPIZ
        $objuser = new User();
        $hospizUsers = $objuser->fetchHospizUsers($this->logininfo->clientid);
//         $hospizvereinUsers = $objuser->fetchHospizvereinUsers($this->logininfo->clientid);
        
        
        
        if ( ! empty($hospizUsers)) {
            
            $cb = $subform2->addElement('checkbox', 'all_users', array(
                'checkedValue'    => '1',
                'uncheckedValue'  => '0',
                'label'      => 'Alle Hospiz Benutzer',
                'required'   => false,
                'value' => 0,
                'decorators'   => array(
                    'ViewHelper',
                    array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.checked) { $("input:checkbox", $(this).parents("table")).prop("checked", true);} else{$("input:checkbox", $(this).parents("table")).prop("checked", false);}', 
            ));
            
            foreach ($hospizUsers as $user) {
                $cb = $subform2->createElement('checkbox', 'user_id', array(
                    'checkedValue'    => $user['id'],
                    'uncheckedValue'  => 0,
                    'label'      => $user['nice_name'],
                    'required'   => false,
                    'value' => 0,
                    'decorators'   => array(
                        'ViewHelper',
                        array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
                        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                    ),
                    'isArray' => true,
                    'onChange' => 'if (!this.checked) { $("input[name$=\"\[all_users\]\"]", $(this).parents("table")).prop("checked", false);} else{}',
                    
                    
                ));
                $subform2->addElement($cb, "user_id_{$user['id']}");
            }
            
        
            $subform2->addElement('hidden', 'patient_id', array(
                'label'        => null,
                'value'        => ! empty($this->_patientMasterData['id']) ? Pms_Uuid::encrypt($this->_patientMasterData['id']) : null,
                'required'     => false,
                'readonly'     => true,
                'filters'      => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
                ),
            ));
        }
    
        $subform->addSubForm($subform2, 'set_ishospiz_visitors');
    
        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    
    
    
    
    
    
    
    
    

    /**
     * children of create_form_patient_hospiz_hospizverein_sapv_aapv
     * is displayed as a dialog when ishospizverein is checked
     * 
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form
     */
    public function create_form_patient_hospiz_hospizverein_sapv_aapv_set_ishospizverein_visitors ($values =  array() , $elementsBelongTo = null)
    {
    
//         $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
         
        $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_hospiz_hospizverein_sapv_aapv");
         
    
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table', 'class' => 'firstStep'));
        $subform->setLegend('');
        $subform->setAttrib("class", "label_same_size " . __FUNCTION__);
    
    
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
    
    
        $subform->addElement('note', 'note1', array(
            'value' => $this->translate('Sie haben dem Patienten eine neue Gruppe zugeordnet. Möchten Sie auch Benutzer dieser Gruppe Rechte für diesen Patienten geben ?'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
    
        ));
    
        $subform->addElement('button', 'Nein', array(
            'value' => 'Nein',
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1, 'style' => 'text-align: right;padding: 10px;')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true,)),
            ),
            'class' => "ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only",
            'style' => "width: 80px; height:30px",
    
            'onClick' => 'try{closeSubFormDialog(this);}catch(e){}; return false;',
    
    
        ));
        $subform->addElement('button', 'Ja', array(
            'value' => 'Ja',
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>1)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
            ),
            'class' => "ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only",
            'style' => "width: 80px; height:30px",
    
            'onClick' => '$(".firstStep", $(this).parents("fieldset")).hide();$(".secondStep", $(this).parents("fieldset")).show();',
    
        ));
    
    
    
        $subform2 = $this->subFormTable(array(
            'columns' => [
                $this->translate('#'),
                $this->translate('user'),
            ],
            'class' => 'secondStep display_none',
            //'id' => ''
        ));
        $subform2->removeDecorator('Fieldset');
    
        // HOSPIZVEREIN USERS
        //  10 == HOSPIZ
//         $usergroup = new Usergroup();
//         $hospizgroups = $usergroup->getUserGroups(array("10"));
//         $hospizgroupsIDS =  array_column($hospizgroups, 'id');
    
//         $hospizUsers = array_filter($this->_patientMasterData['User'], function($user) use ($hospizgroupsIDS) {
//             return $user['isdelete'] == 0 && in_array($user['groupid'], $hospizgroupsIDS);
//         });
    
        $objuser = new User();
//         $hospizUsers = $objuser->fetchHospizUsers($this->logininfo->clientid);
        $hospizvereinUsers = $objuser->fetchHospizvereinUsers($this->logininfo->clientid);
        
    
        if ( ! empty($hospizvereinUsers)) {

            $cb = $subform2->addElement('checkbox', 'all_users', array(
                'checkedValue'    => '1',
                'uncheckedValue'  => '0',
                'label'      => 'Alle Hospizverein Benutzer',
                'required'   => false,
                'value' => 0,
                'decorators'   => array(
                    'ViewHelper',
                    array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                ),
                'onChange' => 'if (this.checked) { $("input:checkbox", $(this).parents("table")).prop("checked", true);} else{$("input:checkbox", $(this).parents("table")).prop("checked", false);}',
            ));

            foreach ($hospizvereinUsers as $user) {
                $cb = $subform2->createElement('checkbox', 'user_id', array(
                    'checkedValue'    => $user['id'],
                    'uncheckedValue'  => 0,
                    'label'      => $user['nice_name'],
                    'required'   => false,
                    'value' => 0,
                    'decorators'   => array(
                        'ViewHelper',
                        array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
                        array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
                    ),
                    'isArray' => true,
                    'onChange' => 'if (!this.checked) { $("input[name$=\"\[all_users\]\"]", $(this).parents("table")).prop("checked", false);} else{}',


                ));
                $subform2->addElement($cb, "user_id_{$user['id']}");
            }


            $subform2->addElement('hidden', 'patient_id', array(
                'label'        => null,
                'value'        => ! empty($this->_patientMasterData['id']) ? Pms_Uuid::encrypt($this->_patientMasterData['id']) : null,
                'required'     => false,
                'readonly'     => true,
                'filters'      => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
                ),
            ));
        }

        $subform->addSubForm($subform2, 'set_ishospizverein_visitors');

        return $this->filter_by_block_name($subform, __FUNCTION__);
    
    }
    
    
    
    public function save_form_patient_hospiz_hospizverein_sapv_aapv($ipid = '', $data = array())
    {
        if (empty($ipid) || empty($data)) {
            return;
        }
        
        $entity = new PatientMaster();
        
        $result =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
        
        
        if(isset($data['set_ishospiz_visitors'])) {
            $objuser = new User();
            $hospizUsers = $objuser->fetchHospizUsers($this->logininfo->clientid, false);
//             $hospizvereinUsers = $objuser->fetchHospizvereinUsers($this->logininfo->clientid, false);
            $allhospizusersIDS = array_column($hospizUsers, 'id');
        
            $this->_remove_assigned_users_from_patient($allhospizusersIDS);
        
            $assign = [];
            $vizibility = [];
            $assign_date = date("Y-m-d H:i:s", time());
        
            $epid = $this->_patientMasterData['epid'];
        
            foreach($data['set_ishospiz_visitors']['user_id'] as $assignvalue) {
        
                $assign[] = [
                    'epid'          => $epid,
                    'userid'        => $assignvalue,
                    'clientid'      => $this->logininfo->clientid,
                    'assign_date'   => $assign_date,
                ];
        
                $vizibility[] = [
                    'clientid'      => $this->logininfo->clientid,
                    'ipid'          => $ipid,
                    'userid'        => $assignvalue,
                    'create_date'   => $assign_date,
                ];
            }
        
            if ( ! empty($assign)) {
        
                $collection = new Doctrine_Collection('PatientQpaMapping');
                $collection->fromArray($assign);
                $collection->save();
        
                $collection = new Doctrine_Collection('PatientUsers');
                $collection->fromArray($vizibility);
                $collection->save();
            }
        
        } 
        if( isset($data['removeassignedusers']) &&  $data['removeassignedusers'] == 'removehsusers') {
                    
            $objuser = new User();
            $hospizUsers = $objuser->fetchHospizUsers($this->logininfo->clientid, false);
            //         $hospizvereinUsers = $objuser->fetchHospizvereinUsers($this->logininfo->clientid, false);
            $allhospizusersIDS = array_column($hospizUsers, 'id');
        
            $this->_remove_assigned_users_from_patient($allhospizusersIDS);
        
        }
        
        
        
        
        if(isset($data['set_ishospizverein_visitors'])) {    
            $objuser = new User();
//             $hospizUsers = $objuser->fetchHospizUsers($this->logininfo->clientid, false);
            $hospizvereinUsers = $objuser->fetchHospizvereinUsers($this->logininfo->clientid, false);
            $allhospizvereinusersIDS = array_column($hospizvereinUsers, 'id'); 
            
            $this->_remove_assigned_users_from_patient($allhospizvereinusersIDS);
            
            $assign = [];
            $vizibility = [];
            $assign_date = date("Y-m-d H:i:s", time());
            
            $epid = $this->_patientMasterData['epid'];
            
            foreach($data['set_ishospizverein_visitors']['user_id'] as $assignvalue) {
                
                $assign[] = [
                    'epid'          => $epid,
                    'userid'        => $assignvalue,
                    'clientid'      => $this->logininfo->clientid,
                    'assign_date'   => $assign_date,
                ];
                
                $vizibility[] = [
                    'clientid'      => $this->logininfo->clientid,
                    'ipid'          => $ipid,
                    'userid'        => $assignvalue,
                    'create_date'   => $assign_date,
                ];
            }
            
            if ( ! empty($assign)) {
            
                $collection = new Doctrine_Collection('PatientQpaMapping');
                $collection->fromArray($assign);
                $collection->save();
                
                $collection = new Doctrine_Collection('PatientUsers');
                $collection->fromArray($vizibility);
                $collection->save();   
            }
        
        } 
        if( isset($data['removeassignedusers']) &&  $data['removeassignedusers'] == 'removehvusers') {
                        
            $objuser = new User();
//             $hospizUsers = $objuser->fetchHospizUsers($this->logininfo->clientid, false);
            $hospizvereinUsers = $objuser->fetchHospizvereinUsers($this->logininfo->clientid, false);
            $allhospizvereinusersIDS = array_column($hospizvereinUsers, 'id');
            
            $this->_remove_assigned_users_from_patient($allhospizvereinusersIDS);
        
        }
        
        return $result;
        
    }
    
    
    private function _remove_assigned_users_from_patient($userids = array()) 
    {
        
        if (empty($userids) || ! is_array($userids)) {
            return ; // fail-safe
        }
        
        $ipid = $this->_patientMasterData['ipid'];;
        $epid = $this->_patientMasterData['epid'];;
        
//         dd($ipid, $epid, $userids );
        
        $q = Doctrine_Query::create()
        ->delete('PatientQpaMapping')
        ->where('epid = ?', $epid)
        ->andWhereIn('userid', $userids)
        ->execute()
        ;
        
        $u = Doctrine_Query::create()
        ->delete('PatientUsers')
        ->where('ipid = ?', $ipid)
        ->andWhereIn('userid', $userids)
        ->execute()
        ;
        
    }
    
    //ISPC-2807 Lore 24.02.2021
    public function PatientStammdaten_toVerlauf ($post)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_REQUEST['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        
               
        $model= new PatientMaster();
        $old_vals_db_data = $model->get_patients_details_By_Ipids(array($ipid));
        
        $course_title = '';
        foreach($post as $key => $vals){

            //if($key != 'patient_id' && $key != 'referred_by' && $key != 'fdoc_caresalone' && $key != 'is_contact' ) {
            //TODO-3930 Lore 08.03.2021 -- in mambo have PatientReligions in $post
            if($key != 'patient_id' && $key != 'referred_by' && $key != 'fdoc_caresalone' && $key != 'is_contact' && $key != 'PatientReligions') {
                
                if($key == 'birthd'){
                    
                    if($old_vals_db_data[$ipid]['birthd'] != date("Y-m-d", strtotime($vals))){
                        $last_value = $old_vals_db_data[$ipid][$key];
                        $course_title .= "Die ". $this->translate($key).' des Patienten wurde geändert: '.$last_value .' -> '.$vals . "\n\r";       //TODO-4004 Lore 25.03.2021
                    }
                }
                elseif($key == 'sex'){
                    $sex_array = array('0' => 'divers', '1' => 'männlich', '2' => 'weiblich');
                    if($old_vals_db_data[$ipid]['sex'] != $vals ){
                        $last_value = $sex_array[$old_vals_db_data[$ipid]['sex']];
                        $course_title .= "Das ". $this->translate($key).' des Patienten wurde geändert: '.$last_value .' -> '.$sex_array[$vals] . "\n\r";   //TODO-4004 Lore 25.03.2021
                    }
                }
                elseif($key == 'first_name' || $key == 'last_name' || $key == 'birth_name'){            //TODO-4009 Lore 29.03.2021
                    if(!(empty($vals)) || !(empty($old_vals_db_data[$ipid][$key]))){
                        if($old_vals_db_data[$ipid][$key] != $vals ){
                            $last_value = $old_vals_db_data[$ipid][$key];
                            $course_title .= "Der ". $this->translate($key).' des Patienten wurde geändert: '.$last_value .' -> '.$vals . "\n\r";   //TODO-4004 Lore 25.03.2021
                        }
                    }
                }
                elseif($key == 'phone'){            //TODO-4009 Lore 29.03.2021
                    if(!(empty($vals)) || !(empty($old_vals_db_data[$ipid][$key]))){
                        if($old_vals_db_data[$ipid][$key] != $vals ){
                            $last_value = $old_vals_db_data[$ipid][$key];
                            $course_title .= "Die Telefonnummer des Patienten wurde geändert: ".$last_value .' -> '.$vals . "\n\r";   //TODO-4004 Lore 25.03.2021
                        }
                    }
                }
                elseif($key == 'mobile'){            //TODO-4009 Lore 29.03.2021
                    if(!(empty($vals)) || !(empty($old_vals_db_data[$ipid][$key]))){
                        if($old_vals_db_data[$ipid][$key] != $vals ){
                            $last_value = $old_vals_db_data[$ipid][$key];
                            $course_title .= "Die Mobiltelefonnummer des Patienten wurde geändert: ".$last_value .' -> '.$vals . "\n\r";   //TODO-4004 Lore 25.03.2021
                        }
                    }
                }
                else {
                    if(!(empty($vals)) || !(empty($old_vals_db_data[$ipid][$key]))){
                        if($old_vals_db_data[$ipid][$key] != $vals ){
                            $last_value = $old_vals_db_data[$ipid][$key];
                            $course_title .= "Die ". $this->translate($key).' des Patienten wurde geändert: '.$last_value .' -> '.$vals . "\n\r";   //TODO-4004 Lore 25.03.2021
                        }
                    }
                }
            }
        }
        
        $recordid = $post['id'];
        if(!empty($course_title)){
            $insert_pc = new PatientCourse();
            $insert_pc->ipid =  $ipid;
            $insert_pc->course_date = date("Y-m-d H:i:s", time());
            $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
            $insert_pc->tabname = Pms_CommonData::aesEncrypt($post['__category']);
            $insert_pc->recordid = $recordid;
            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_title));
            $insert_pc->user_id = $userid;
            $insert_pc->save();
        }
        
        
    }
    
    
}

?>