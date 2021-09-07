<?php

require_once("Pms/Form.php");

class Application_Form_PatientLocation extends Pms_Form 
{
    
    public function getVersorgerExtract() {
        return [
            [
                "label" => $this->translate('location'),
                "cols" => ["nice_name"],
//                 "vsprintf_named" => '<span class="s1">{sapv_order_name} {dotsLegend}</span> <span class="s2"><font color="red">{verordnet_longtext}</font></span>'
                
            ],
            
            [
                "label" => $this->translate('start_time'),
                "cols" => ["__PatientLocation" => "valid_from"],            
            ],
            
            
            [
                "label" => $this->translate('end_time'),
                "cols" => ["__PatientLocation" => "valid_till"],            
            ],
            
        ];
    }
    
    
    //define the name and if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientLocation';
        

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();
			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function newvalidate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$val = new Pms_Validation();

			for($i = 0; $i < count($post['location_id']); $i++)
			{
				if(!$val->isstring($post['valid_from'][$i]))
				{
					$this->error_message[$i + 1]['valid_from'] = $Tr->translate('validfrom_error');
					$error = 1;
				}

				if(!$val->isstring($post['valid_till'][$i]))
				{
					if($val->isstring($post['valid_till'][$i + 1]))
					{
						$this->error_message[$i + 1]['valid_till'] = $Tr->translate('validtill_error');
						$error = 2;
					}
				}

				if($post['location_id'][$i] < 1 && $post['is_discharged'][$i] == 0)
				{
					$this->error_message[$i + 1]['location'] = $Tr->translate('location_error');
					$error = 3;
				}

				if(strtotime($post['valid_from'][$i]) < strtotime($post['valid_from'][$i - 1]))
				{
					$this->error_message[$i + 1]['fromerror'] = $Tr->translate('err_fromdate');
					$error = 4;
				}

				if($post['valid_till'][$i + 1] != '0000-00-00 00:00:00' && $post['valid_till'][$i + 1] != "")
				{
					if(strtotime($post['valid_till'][$i]) > strtotime($post['valid_till'][$i + 1]))
					{
						$this->error_message[$i + 1]['fromerror'] = $Tr->translate('err_tilldate');
						$error = 5;
					}
				}

				if($post['valid_till'][$i] != '0000-00-00 00:00:00' && $post['valid_till'][$i] != "")
				{
					if(strtotime($post['valid_from'][$i]) > strtotime($post['valid_till'][$i]))
					{
						$this->error_message[$i + 1]['fromerror'] = $Tr->translate('tillerror');
						$error = 6;
					}
				}

				if(strtotime($post['valid_from'][$i]) > strtotime(date("d.m.Y", time())))
				{
					$this->error_message[$i + 1]['fromerror'] = $Tr->translate('err_datefuture');
					$error = 7;
				}

				if(strtotime($post['valid_till'][$i]) > strtotime(date("d.m.Y", time())))
				{
					$this->error_message[$i + 1]['fromerror'] = $Tr->translate('err_datefuture');
					$error = 8;
				}
			}
			if($error == 0)
			{
				return true;
			}

			return false;
		}

		public function InsertData($post)
		{
			$epid = Pms_CommonData::getEpid($post['ipid'][0]);
			$admin_date = explode(".", $post['admission_date'][0]);

			if($post['location_id'][0] > 0)
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$cust = new PatientLocation();
				$cust->clientid = $logininfo->clientid;
				$cust->station = $post['station_select'][0];
				$cust->ipid = $post['ipid'];
				$cust->location_id = $post['location_id'][0];
				$cust->reason = $post['reason'][0];
				$cust->reason_txt = $post['reason_txt'][0];
				$cust->hospdoc = $post['hospdoc'][0];
				$cust->transport = $post['transport'][0];
				$valid_frm = explode(".", $post['valid_from'][0]);
				$valid_til = explode(".", $post['valid_till'][0]);
				$cust->valid_from = $valid_frm[2] . "-" . $valid_frm[1] . "-" . $valid_frm[0];
				$cust->valid_till = $valid_til[2] . "-" . $valid_til[1] . "-" . $valid_til[0];
				$cust->admission_comments = $post['admission_comments'][0];
				$cust->comment = $post['comment'][0];
				$cust->save();
			}
		}

		public function InsertDataFromAdmission($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$cust = new PatientLocation();
			$cust->clientid = $logininfo->clientid;
			$cust->ipid = $post['ipid'];
			$cust->location_id = $post['location_id'];
			$valid_frm = explode(".", $post['valid_from']);
			$cust->valid_from = $valid_frm[2] . "-" . $valid_frm[1] . "-" . $valid_frm[0];
			$cust->save();
		}

		public function UpdateData($post)
		{
			if($post['location_id'] > 0)
			{
				$decid = Pms_Uuid::decrypt($_GET['id']);
				$ipid = Pms_CommonData::getIpid($decid);
				$pl = new PatientLocation();
				$larr = $pl->getLastLocationDataFromAdmissionUpdate($ipid);

				if(!empty($post['admission_date']) && !empty($post['adm_timeh']) && !empty($post['adm_timem']))
				{
					$current_time = strtotime($post['admission_date'] . ' ' . $post['adm_timeh'] . ':' . $post['adm_timem'] . ':00');
				}
				else
				{
					$current_time = time();
				}

				if($larr[0]['location_id'] != $post['location_id'])
				{

					$epid = Pms_CommonData::getEpidFromId($decid);

					$drop = Doctrine_Query::create()
						->select('id')
						->from('PatientLocation')
						->where('ipid="' . $ipid . '"  and isdelete="0"')
						->limit(1)
						->orderBy('valid_from DESC');
					$dropexec = $drop->execute();
					$droparray = $dropexec->toArray();

					if(count($droparray) > 0)
					{
						$cust = Doctrine::getTable('PatientLocation')->find($droparray[0]['id']);
						$cust->valid_till = date("Y-m-d H:i:s", $current_time);
						$cust->save();
					}

					$logininfo = new Zend_Session_Namespace('Login_Info');
					$cust = new PatientLocation();
					$cust->clientid = $logininfo->clientid;
					$cust->ipid = $ipid;
					$cust->location_id = $post['location_id'];
					$cust->reason = $post['reason'];
					$cust->reason_txt = $post['reason_txt'];
					$cust->hospdoc = $post['hospdoc'];
					$cust->transport = $post['transport'];
					$cust->comment = $post['comment'];
					$cust->valid_from = date("Y-m-d H:i:s", $current_time);
					$cust->admission_comments = $post['admission_comments'];
					$cust->save();
				}
			}
		}

		public function EditLocation($post)
		{
			$valid_frm = explode(".", $post['valid_from']);
			$valid_til = explode(".", $post['valid_till']);

			$cust = Doctrine::getTable('PatientLocation')->find($post['lid']);
			$cust->location_id = $post['location_id'];
			$cust->valid_from = $valid_frm[2] . "-" . $valid_frm[1] . "-" . $valid_frm[0];
			$cust->valid_till = $valid_til[2] . "-" . $valid_til[1] . "-" . $valid_til[0];
			$cust->save();
		}

		public function InserLocationBetween($post)
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);

			for($i = 0; $i < count($post['location_id']); $i++)
			{
				$valid_frm = explode(".", $post['valid_from'][$i]);
				$valid_til = explode(".", $post['valid_till'][$i]);
				$hr = date("H", time());
				$mt = date("i", time());

				$valid_frm = $valid_frm[2] . "-" . $valid_frm[1] . "-" . $valid_frm[0] . " " . $hr . ":" . $mt;
				$valid_til = $valid_til[2] . "-" . $valid_til[1] . "-" . $valid_til[0] . " " . $hr . ":" . $mt;

				if($post['location_id'][$i] > 0 || $post['is_discharged'][$i] == 1)
				{
					if($post['lid'][$i - 1] > 0)
					{
						$cust = Doctrine::getTable('PatientLocation')->find($post['lid'][$i - 1]);
						$cust->valid_till = $valid_frm;
						$cust->save();
					}

					if($post['lid'][$i + 1] > 0)
					{
						$cust = Doctrine::getTable('PatientLocation')->find($post['lid'][$i + 1]);
						$cust->valid_from = $valid_til;
						$cust->save();
					}

					if($post['lid'][$i] > 0)
					{
						$pm_form = new Application_Form_PatientMaster();

						if($post['kontactnumbertype'] == "1")
						{
							$pm_form->UpdateContactNumber("", true);
						}

						$logininfo = new Zend_Session_Namespace('Login_Info');
						$cust = Doctrine::getTable('PatientLocation')->find($post['lid'][$i]);
						$cust->clientid = $logininfo->clientid;
						$cust->location_id = $post['location_id'][$i];
						$cust->valid_from = $valid_frm;
						$cust->valid_till = $valid_til;
						$cust->station = $post['station_select'][$i];
						$cust->reason = $post['reason'][$i];
						$cust->reason_txt = $post['reason_txt'][$i];
						$cust->hospdoc = $post['hospdoc'][$i];
						$cust->transport = $post['transport'][$i];
						$cust->comment = $post['comment'][$i];
						$cust->discharge_location = $post['is_discharged'][$i];
						$cust->save();
					}
					else
					{
						$pm_form = new Application_Form_PatientMaster();

						if($post['kontactnumbertype'] == "1")
						{
							$pm_form->UpdateContactNumber("", true);
						}

						$logininfo = new Zend_Session_Namespace('Login_Info');
						$cust = new PatientLocation();
						$cust->clientid = $logininfo->clientid;
						$cust->location_id = $post['location_id'][$i];
						$cust->valid_from = $valid_frm;
						$cust->valid_till = $valid_til;
						$cust->station = $post['station_select'][$i];
						$cust->ipid = $post['ipid'];
						$cust->reason = $post['reason'][$i];
						$cust->reason_txt = $post['reason_txt'][$i];
						$cust->hospdoc = $post['hospdoc'][$i];
						$cust->transport = $post['transport'][$i];
						$cust->comment = $post['comment'][$i];
						$cust->discharge_location = $post['is_discharged'][$i];
						$cust->save();
					}
				}
			}
		}

		//InserLocationBetween optimized
		public function insert_location_between($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;
			$clientid = $logininfo->clientid;
			$pm_form = new Application_Form_PatientMaster();
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$Tr = new Zend_View_Helper_Translate();
			
			//TODO-3595 Ancuta 21.12.2020
			$patloc = Doctrine_Query::create()
			->select('*')
			->from('PatientLocation INDEXBY id')
			->where('ipid=?', $post['ipid'])
			->andWhere('isdelete="0"');
			$pat_loc_details = $patloc->fetchArray();
 
		
			if(!empty($pat_loc_details)){
    			$locs_ids = array_column($pat_loc_details, 'location_id');
    			$location_model = new Locations();
    			$larr = $location_model->getLocationbyIds($locs_ids);
    			foreach($pat_loc_details as $pl_id => $loc_data ){
    			    $pat_loc_details[$pl_id]['master_location'] = $larr[$loc_data['location_id']];
    			}
    			
    			$_POST['existing_data'] = $pat_loc_details;
			}
			//--
			
			foreach($post['location_id'] as $i => $v_location)
			{
				$valid_frm = date('Y-m-d', strtotime($post['valid_from'][$i])) . " " . date('H:i', time());
				if(strlen($post['valid_till'][$i]) > '0')
				{
					$valid_til = date('Y-m-d', strtotime($post['valid_till'][$i])) . " " . date('H:i', time());
				}
				else
				{
					$valid_til = '';
				}

				if($post['location_id'][$i] > 0 || $post['is_discharged'][$i] == 1)
				{
					if($post['lid'][($i - 1)] > 0)
					{
// 					    $update_prev = Doctrine::getTable('PatientLocation')->find($post['lid'][$i - 1]);
					    
// 					    if($update_prev){
// 					        $update_prev->valid_till = $valid_frm;
// 					        $update_prev->save();
// 					    }
					    
						$update_prev_till = Doctrine_Query::create()
							->update('PatientLocation')
							->set('valid_till', '"' . $valid_frm . '"')
							->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
							->set('change_user', '"' . $userid . '"')
							->where('id ="' . $post['lid'][$i - 1] . '"');
						$update_res = $update_prev_till->execute();
					}

					if($post['lid'][($i + 1)] > 0)
					{
// 					    $update_next = Doctrine::getTable('PatientLocation')->find($post['lid'][$i + 1]);
					    
// 					    if($update_next){
// 					        $update_next->valid_from = $valid_til;
// 					        $update_next->save();
// 					    }
					    
						$update_next_from = Doctrine_Query::create()
							->update('PatientLocation')
							->set('valid_from', '"' . $valid_til . '"')
							->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
							->set('change_user', '"' . $userid . '"')
							->where('id ="' . $post['lid'][$i + 1] . '"');
						$update_res = $update_next_from->execute();
						
					}

					if($post['lid'][$i] > 0)
					{
						if($post['kontactnumbertype'] == "1")
						{
							$pm_form->UpdateContactNumber("", true);
						}
						
						//update curent loop item
						/* $update = Doctrine_Query::create()
							->update('PatientLocation')
							->set('clientid', '"' . $clientid . '"')
							->set('location_id', '"' . $post['location_id'][$i] . '"')
							->set('valid_from', '"' . $valid_frm . '"')
							->set('valid_till', '"' . $valid_til . '"')
							->set('station', '"' . $post['station_select'][$i] . '"')
							->set('reason', '"' . $post['reason'][$i] . '"')
							->set('reason_txt', '"' . $post['reason_txt'][$i] . '"')
							->set('hospdoc', '"' . $post['hospdoc'][$i] . '"')
							->set('transport', '"' . $post['transport'][$i] . '"')
							->set('comment', '"' . $post['comment'][$i] . '"')
							->set('discharge_location', '"' . $post['is_discharged'][$i] . '"')
							->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
							->set('change_user', '"' . $userid . '"')
							->where('id ="' . $post['lid'][$i] . '"');
						$update_res = $update->execute(); */
						
						
						
						
						$update_ibt = Doctrine::getTable('PatientLocation')->find($post['lid'][$i]);
						
						if($update_ibt){
    						$update_ibt->clientid= $clientid ;
    						$update_ibt->location_id= $post['location_id'][$i] ;
    						$update_ibt->valid_from= $valid_frm ;
    						$update_ibt->valid_till= $valid_til ;
    						$update_ibt->station= $post['station_select'][$i] ;
    						$update_ibt->reason= $post['reason'][$i] ;
    						$update_ibt->reason_txt = htmlspecialchars($post['reason_txt'][$i]);
    						$update_ibt->hospdoc= $post['hospdoc'][$i] ;
    						$update_ibt->transport= $post['transport'][$i] ;
    						$update_ibt->comment = htmlspecialchars($post['comment'][$i]);
    						$update_ibt->discharge_location= $post['is_discharged'][$i] ;
    						$update_ibt->change_date= date('Y-m-d H:i:s', time()) ;
    						$update_ibt->change_user= $userid ;
    						$update_ibt->save();
						}
						
						
						
						
						
					}
					else
					{
						if($post['kontactnumbertype'] == "1")
						{
							$pm_form->UpdateContactNumber("", true);
						}

						$cust = new PatientLocation();
						$cust->clientid = $clientid;
						$cust->location_id = $post['location_id'][$i];
						$cust->valid_from = $valid_frm;
						$cust->valid_till = $valid_til;
						$cust->station = $post['station_select'][$i];
						$cust->ipid = $post['ipid'];
						$cust->reason = $post['reason'][$i];
						$cust->reason_txt = $post['reason_txt'][$i];
						$cust->hospdoc = $post['hospdoc'][$i];
						$cust->transport = $post['transport'][$i];
						$cust->comment = $post['comment'][$i];
						$cust->discharge_location = $post['is_discharged'][$i];
						$cust->save();
					}
				}
			}
		}

		
        public function remove_locations($location_ids = false)
		{
            $location_ids = array_filter($location_ids);
            
			if ( ! empty($location_ids)) {
			    
    			$patient_loc = new PatientLocation();
    			
				foreach($location_ids as $loc_id)
				{
					$pat_loc_res = $patient_loc->getLocationById($loc_id, Doctrine_Core::HYDRATE_RECORD);
					
					if ( ! $pat_loc_res->count()) {
					    continue; //something is wrong
					}
					//delete this 
					$pat_loc_res =  $pat_loc_res->getFirst();
					$pat_loc_res->isdelete=1;
					$pat_loc_res->save();
					
					//TODO-3595 Ancuta 21.12.2020
    					$logininfo = new Zend_Session_Namespace('Login_Info');
    					$userid = $logininfo->userid;
    					$ls = new Locations();
    					$master_loc= $ls->getLocationbyId($pat_loc_res->location_id);
    					
    					$Tr = new Zend_View_Helper_Translate();
    					$loccomment = $Tr->translate('patientlocation')." : ".$master_loc[0]['location'] .$Tr->translate('pl_location_was_deleted');
    					
    					$cust = new PatientCourse();
    					$cust->ipid = $pat_loc_res->ipid;
    					$cust->course_date = date("Y-m-d H:i:s",time());
    					$cust->course_type=Pms_CommonData::aesEncrypt("K");
    					$cust->course_title=Pms_CommonData::aesEncrypt($loccomment);
    					$cust->user_id = $userid;
    					$cust->save();
					//-- 
            					
					//check for next location
					$patarray = $patient_loc->getNextLocation($pat_loc_res->valid_from, $pat_loc_res->ipid, Doctrine_Core::HYDRATE_RECORD);
					if ($patarray->count()) {
						//update the next location valid_from with deleted location valid from to keep the timeline continuuous
					    $next = $patarray->getFirst();
					    $next->valid_from = $pat_loc_res->valid_from;
					}
	
				}
			}
		}
		
		public function remove_locations_OLD($location_ids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$userid = $logininfo->userid;

			$patient_loc = new PatientLocation();
			$ls = new Locations();
			$patientmaster = new PatientMaster();

			$locations_form = new Application_Form_PatientLocation();

			if($location_ids)
			{
				foreach($location_ids as $k_loc_id => $v_loc_id)
				{
					if($v_loc_id != '0')
					{
						$pat_loc_res = $patient_loc->getLocationById($v_loc_id);
						$carr = $pat_loc_res[0];

						$locarr = $ls->getLocationbyId($carr['location_id']);

						$parr = $patientmaster->getMasterData(null, 0, 1, $carr['ipid']);

						if($parr['kontactnumber'] == $locarr[0]['phone1'])
						{
							$pmaster = Doctrine_Query::create()
								->update('PatientMaster')
								->set('kontactnumber', '" "')
								->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
								->set('change_user', '"' . $userid . '"')
								->where('ipid LIKE "' . $carr['ipid'] . '"');
							$update_res = $pmaster->execute();
						}

						$patloc = Doctrine_Query::create()
							->update('PatientLocation')
							->set('isdelete', '"1"')
							->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
							->set('change_user', '"' . $userid . '"')
							->where('id = "' . $v_loc_id . '"');
						$patloc_res = $patloc->execute();

						//check for next location
						$patarray = $patient_loc->getNextLocation($carr['valid_from'], $carr['ipid']);

						if(count($patarray) > 0)
						{
							//update the next location valid_from with deleted location valid from to keep the timeline continuuous
							$patloc_next = Doctrine_Query::create()
								->update('PatientLocation')
								->set('valid_from', '"' . $carr['valid_from'] . '"')
								->set('change_date', '"' . date('Y-m-d H:i:s', time()) . '"')
								->set('change_user', '"' . $userid . '"')
								->where('id = "' . $patarray[0]['id'] . '"');
							$patloc_next_res = $patloc_next->execute();
						}
					}
				}
			}
		}

		
	
		
		

	public function create_form_patient_location ($values =  array() , $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table'));
		$subform->setLegend($this->translate('Patient Location'));
		$subform->setAttrib("class", "label_same_size");
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}


		
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
	
		$current_location_arr = PatientLocation::getPatientActiveLocation($this->_patientMasterData['ipid']);
		
		if( ! empty($current_location_arr)){
			$current_location = $current_location_arr[0];
		}

		$loc = new Locations();
		$locationsall = $loc->getLocations($clientid, 1);
	
	
		$subform->addElement('hidden', 'id', array(
				'value'        => $current_location['id'] ? $current_location['id'] : 0 ,
				'required'     => false,
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none' )),
	
				),
		));
	
		$subform->addElement('select', 'location_id', array(
				'value'        => $current_location['location_id'],
				'multiOptions' => $locationsall,
				'label'        => $this->translate('location'),
				// 				'required'     => true,
				// 				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array('Label', array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
				),
		));
	
		return $subform;
	}
		
		
		
	public function save_patientlocation($ipid= '', $data = array()){
	
		if(empty($ipid) || empty($data)) {
			return;
		}
		
	
		$data['ipid'] = $ipid;
	
		$entity = new PatientLocation();
		// !!!!!!!!!!! special !!!!!!!!!!!!!!!!!!!
		
		return $entity->findOrCreateOneById($data['id'], $data);
	}
	
	
	

	// 		$lbprevileges = new Modules();
	// 		if($lbprevileges->checkModulePrivileges("65", $clientid))
	    // 		{
	    // 		    $this->view->lb_visits_deactivate = 1;
	    // 		}
	    // 		else
	        // 		{
	        // 		    $this->view->lb_visits_deactivate = 0;
	        // 		}
			
	
	
	public function create_form_all_patient_location_display ($values =  array() , $elementsBelongTo = null)
	{
        $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
         
        $this->mapSaveFunction(__FUNCTION__ , "save_form_all_patient_location_display");
        
        $columns = array(
            $this->translate('patientlocation'),
            $this->translate('start_time'),
            $this->translate('end_time'),
        );
        
        $subform = $this->subFormTable(array(
            'columns' => $columns,
            // 'class' => 'datatable',
            'id'=> 'patient_locations_table'
        ));
        $subform->setLegend('Patient Location');
        $subform->setAttrib("class", "label_same_size_auto inlineEdit " . __FUNCTION__);
        
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
        
	    
	   
	
	    $cnt_rows = 0;
	    foreach ($values as $key => $row) {
	        
    	    $subform->addElement('note', "location_nice_name_{$key}"  , array(
    	        'value'        => $row['nice_name'],
    	        'label'        => null,
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true , 'class' => $cnt_rows%2 == 0 ? 'even' : 'odd')),
    	        ),
    	    ));
	
    	    $subform->addElement('note', "valid_from_{$key}"  , array(
    	        'value'        =>  !empty($row['__PatientLocation']['valid_from']) && $row['__PatientLocation']['valid_from'] != "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($row['__PatientLocation']['valid_from'])) : ' - ',    	         
    	        'label'        => null,
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	        ),
    	    ));
    	    
    	    $subform->addElement('note', "valid_till_{$key}"  , array(
    	        'value'        =>  !empty($row['__PatientLocation']['valid_till']) && $row['__PatientLocation']['valid_till'] != "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($row['__PatientLocation']['valid_till'])) : ' - ',
    	        'label'        => null,
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true )),
    	        ),
    	    ));
    	    
    	    $cnt_rows ++;
	    }
	    
	    if ( ! empty($values)) {
	        $last_location = end($values);
	        
    	    $subform->addElement('checkbox', 'is_contact', array(
    	        'checkedValue'    => '1',
    	        'uncheckedValue'  => '0',
    	        
    	        'value'    => (int)$last_location['is_contact'],
    	        'label'        => 'is the contact phone number',
    	        'required'     => false,
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' =>  3)),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    	        ),
    	    ));
	    }
	    
        $redirect_2_edit_link = ! empty($values) ? "patient/patientlocationlistedit" : "patient/patientlocationadd";
	    
	    $subform->addElement('note', "redirect_2_edit"  , array(
	        'escape'       => false,
	        'value'        => '<a href="'.APP_BASE.$redirect_2_edit_link.'?id=' . Pms_Uuid::encrypt($this->_patientMasterData['id']) . '" class="ibutton addbutton dontPrint"><img src="' . RES_FILE_PATH .'/images/btttt_plus.png"> Aufenthaltsort hinzufügen</a>',
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' =>  3)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	    ));
	    
	
// 	    if (!empty($values)) dd($values, $subform->__toString());
	    
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	}
	
	
	
	public function save_form_all_patient_location_display ($ipid, $data) 
	{
	   if (empty($ipid) || empty($data)) {
	       return; //fail-safe
	   }    
	   
	   if (isset($data['is_contact'])) {
	       $this->_change_is_contact($ipid, $data['is_contact']);
	   }
	   
	}
	
	
	
	
	
	
	
	private function _change_is_contact ($ipid = '', $is_contact = 0)
	{

	    if (empty($ipid)) {
	        return; //fail-safe
	    }

	    $last_location = PatientLocation::getIpidLastLocationDetails($ipid, true);
	     
	    if ($last_location && $last_location->count() // hmm.. count on null will be fun
	        && ($last_location->valid_till == '0000-00-00 00:00:00'
	            || Pms_CommonData::isToday($last_location->valid_till)
	            || Pms_CommonData::isFuture($last_location->valid_till)))
	    {
	        //current_location is valid
	        $last_location->is_contact = (int)$is_contact;
	        $last_location->save();
	    } else {
	        /**
	         * TODO : a cronjob that should remove the contact-phone when location auto-expires
	         * we have a valid contact_phone for a location if the location's valid_till > CURRENT_TIMESTAMP
	         * other old-invalid locations can only be disabled
	         *
	         * TODO: this logic was NOT implemented in the listener
	         */
	        //we can only disable the contact_phone and set this as is_contact for future location updates/insert to work
	        if ((int)$is_contact == 1) { //bypass listener
	            $pl_obj = new PatientLocation();
	            $listenerChain = $pl_obj->getListener();
	            $i = 0;
	            //i should have given the listeners a name...
	            while ($listener = $listenerChain->get($i))
	            {
	                if ($listener instanceof PatientContactPhoneListener)
	                {
	                    $listener->setOption('disabled', true);
	                    break;
	                }
	                $i++;
	            }
	        }
	        $last_location->is_contact = (int)$_GET['chkval'];
	        $last_location->save();
	    }
	}
	
	public function create_form_all_patient_location_display_sapv ($values =  array() , $elementsBelongTo = null)
	{
		$this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
		 
		$this->mapSaveFunction(__FUNCTION__ , "save_form_all_patient_location_display_sapv");
	
		$columns = array(
				$this->translate('patientlocation'),
				$this->translate('start_time'),
				$this->translate('end_time'),
				$this->translate('reason'),
		);
	
		$subform = $this->subFormTable(array(
				'columns' => $columns,
				// 'class' => 'datatable',
				'id'=> 'patient_locations_table'
		));
		$subform->setLegend('Patient Location');
		$subform->setAttrib("class", "label_same_size_auto inlineEdit " . __FUNCTION__);
	
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		} elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
	
		 
	
	
		$cnt_rows = 0;
		foreach ($values as $key => $row) {
			 
			$subform->addElement('note', "location_nice_name_{$key}"  , array(
					'value'        => $row['nice_name'],
					'label'        => null,
					'decorators'   => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => true , 'class' => $cnt_rows%2 == 0 ? 'even' : 'odd')),
					),
			));
	
			$subform->addElement('note', "valid_from_{$key}"  , array(
					'value'        =>  !empty($row['__PatientLocation']['valid_from']) && $row['__PatientLocation']['valid_from'] != "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($row['__PatientLocation']['valid_from'])) : ' - ',
					'label'        => null,
					'decorators'   => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td')),
					),
			));
				
			$subform->addElement('note', "valid_till_{$key}"  , array(
					'value'        =>  !empty($row['__PatientLocation']['valid_till']) && $row['__PatientLocation']['valid_till'] != "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($row['__PatientLocation']['valid_till'])) : ' - ',
					'label'        => null,
					'decorators'   => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td')),
					),
			));
			
			$subform->addElement('note', "reason_nice_name_{$key}"  , array(
					'value'        => $row['reason_nice_name'],
					'label'        => null,
					'decorators'   => array(
							'ViewHelper',
							array('Errors'),
							array(array('data' => 'HtmlTag'), array('tag' => 'td')),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'closeOnly' => true )),
					),
			));
				
			$cnt_rows ++;
		}
		 
		if ( ! empty($values)) {
			$last_location = end($values);
			 
			$subform->addElement('checkbox', 'is_contact', array(
					'checkedValue'    => '1',
					'uncheckedValue'  => '0',
					 
					'value'    => (int)$last_location['is_contact'],
					'label'        => 'is the contact phone number',
					'required'     => false,
					'decorators'   => array(
							'ViewHelper',
							array('Label', array('placement'=> 'IMPLICIT_APPEND')),
							array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' =>  3)),
							array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
					),
			));
		}
		 
		$redirect_2_edit_link = ! empty($values) ? "patient/patientlocationlistedit" : "patient/patientlocationadd";
		 
		$subform->addElement('note', "redirect_2_edit"  , array(
				'escape'       => false,
				'value'        => '<a href="'.APP_BASE.$redirect_2_edit_link.'?id=' . Pms_Uuid::encrypt($this->_patientMasterData['id']) . '" class="ibutton addbutton dontPrint"><img src="' . RES_FILE_PATH .'/images/btttt_plus.png"> Aufenthaltsort hinzufügen</a>',
				'decorators'   => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' =>  3)),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
				),
		));
		 
	
		// 	    if (!empty($values)) dd($values, $subform->__toString());
		 
		return $this->filter_by_block_name($subform, __FUNCTION__);
	}
	
	
}
?>