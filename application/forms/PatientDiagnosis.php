<?php
require_once("Pms/Form.php");
class Application_Form_PatientDiagnosis extends Pms_Form
{

    protected $_model = 'PatientDiagnosis';
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientDiagnosis';
    
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_diagnosis' => [
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
    
    
    
	public function validate ( $post )
	{

	}

	public function InsertData ( $post )
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;


		$dg = new DiagnosisType();
		$darr = $dg->getDiagnosisTypes($clientid, $post['diagno_abb']);

		$triggerarr = array();

		$triggerarr = array();
		foreach ($post['diagnosis'] as $key => $val)
		{
			if (trim($post['hidd_tab'][$key]) == "dig")
			{
				$tabname = Pms_CommonData::aesEncrypt("diagnosis");
			}
			if (trim($post['hidd_tab'][$key]) == "text")
			{
				$tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
				if ($post['hidd_diagnosis'][$key] == "")
				{
					$post['hidd_diagnosis'][$key] = $post['newhidd_diagnosis'][$key];
				}
			}

			if ($post['hidd_diagnosis'][$key] > 0)
			{
				$cust = new PatientDiagnosis();
				//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner  
				$pc_listener = $cust->getListener()->get('IntenseDiagnosisConnectionListener');
				$pc_listener->setOption('disabled', true);
				//--
				$cust->ipid = $post['ipid'];
				$cust->tabname = $tabname;
				$cust->diagnosis_type_id = $post['dtype'][$key];
				$cust->diagnosis_id = $post['hidd_diagnosis'][$key];
				$cust->icd_id = $post['hidd_icdnumber'][$key];
				$cust->save();
				//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
				$pc_listener->setOption('disabled', false);
				//--
				if (count($post['meta_title'][$key]) > 0)
				{
					$triggerarr = array();
					for ($j = 0; $j < count($post['meta_title'][$key]); $j++)
					{
						if ($post['meta_title'][$key][$j] > 0)
						{

							$pd = new PatientDiagnosisMeta();
							$pd->ipid = $post['ipid'];
							$pd->metaid = $post['meta_title'][$key][$j];
							$pd->diagnoid = $cust->id;
							$pd->save();

							$triggerarr['meta_title'][$key][$j] = $post['meta_title'][$key][$j];
						}
					}

					$triggerarr['ipid'] = $post['ipid'];
					$triggerarr['isstandby'] = $post['isstandby'];
					Pms_Triggers::addMetaDiagnosistocourse($triggerarr);
				}
			}
		}
	}

	public function UpdateData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$reorder = array();
//		for ($i = 1; $i <= sizeof($post['diagnosis']); $i++)
        //ISPC-2614 Ancuta 12.08.2020
        foreach($post['diagnosis'] as $line_id=>$v){
            if (strlen($v) == 0)
            {
                unset($post['diagnosis'][$line_id]);
            }
        }
        $last_key = array_keys($post['diagnosis'])[count($post['diagnosis'])-1];
        // --
        
		foreach ($post['diagnosis'] as $i => $value)
		{

//			if (strlen($post['diagnosis'][$i]) > 0)
			if (strlen($value) > 0)
			{
				if ($post['hidd_ids'][$i] > 0)
				{
					if (trim($post['hidd_tab'][$i]) == "dig")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis");
					}
					if (trim($post['hidd_tab'][$i]) == "text")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
						if ($post['hidd_diagnosis'][$i] == "")
						{
							$post['hidd_diagnosis'][$i] = $post['newhidd_diagnosis'][$i];
						}
					}
					if (trim($post['hidd_tab'][$i]) == "diagnosis_icd")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis_icd");
					}

					$settrigger = false;
					$cust = Doctrine::getTable('PatientDiagnosis')->find($post['hidd_ids'][$i]);

					if($cust)
					{
					    //ISPC-2614 Ancuta 12.08.2020 :: deactivate listner for all except last 
					    if($i != $last_key){
	       				    $pc_listener = $cust->getListener()->get('IntenseDiagnosisConnectionListener');
    					    $pc_listener->setOption('disabled', true);
					    }
					    //--
						if ($cust->diagnosis_type_id != $post['dtype'][$i] && $cust->diagnosis_id == $post['hidd_diagnosis'][$i])
						{
							$settrigger = true;
						}
						$cust->diagnosis_type_id = $post['dtype'][$i];
						$cust->diagnosis_id = $post['hidd_diagnosis'][$i];
						$cust->icd_id = $post['hidd_icdnumber'][$i];
						$cust->tabname = $tabname;
						$cust->diagnosis_from = $post['diagnosis_from'][$i]; //ISPC - 2364
						if($post['diagnosis_page']){// TODO-2604 Ancuta 17.10.2019  // Maria:: Migration ISPC to CISPC 08.08.2020
						    $cust->comments = $post['comments'][$i]; //ISPC - 2364
						}
						$cust->save();
						//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
						if($i != $last_key){
						  $pc_listener->setOption('disabled', false);
						}
						//--

						$reorder[] = $cust->id;
						if ($settrigger)
						{
							$a_post['ipid'] = $ipid;
							$a_post['diagnosis_id'] = $post['hidd_diagnosis'][$i];
							$a_post['diagnosis_type'] = $post['dtype'][$i];
	//						$a_post['diagnosis'] = $post['diagnosis'][$i];
							$a_post['diagnosis'] = $value;

							Pms_Triggers::DiagnosisTypeChange($a_post);
						}
					}
				}
				else
				{
					if (trim($post['hidd_tab'][$i]) == "dig")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis");
					}
					if (trim($post['hidd_tab'][$i]) == "text")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");

						if ($post['hidd_diagnosis'][$i] == "")
						{
							$post['hidd_diagnosis'][$i] = $post['newhidd_diagnosis'][$i];
						}
					}

					$cust = new PatientDiagnosis();
					//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner
					if($i != $last_key){
    					$pc_listener = $cust->getListener()->get('IntenseDiagnosisConnectionListener');
    					$pc_listener->setOption('disabled', true);
					}
					//--
					$cust->ipid = $ipid;
					$cust->diagnosis_type_id = $post['dtype'][$i];
					$cust->diagnosis_id = $post['hidd_diagnosis'][$i];
					$cust->icd_id = $post['hidd_icdnumber'][$i];
					$cust->tabname = $tabname;
					$cust->diagnosis_from = $post['diagnosis_from'][$i]; //ISPC - 2364
					if($post['diagnosis_page']){// TODO-2604 Ancuta 17.10.2019 // Maria:: Migration ISPC to CISPC 08.08.2020
					   $cust->comments = $post['comments'][$i]; //ISPC - 2364
					}
					$cust->save();

					//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
					if($i != $last_key){
					   $pc_listener->setOption('disabled', false);
					}
					//--
					$reorder[] = $cust->id;
					if (count($post['meta_title'][$i]) > 0)
					{
						$triggerarr = array();
						for ($j = 0; $j <= count($post['meta_title'][$i]); $j++)
						{
							if ($post['meta_title'][$i][$j] > 0)
							{
								$pd = new PatientDiagnosisMeta();
								$pd->ipid = $post['ipid'];
								$pd->metaid = $post['meta_title'][$i][$j];
								$pd->diagnoid = $cust->id;
								$pd->save();
								$triggerarr['meta_title'][$i][$j] = $post['meta_title'][$i][$j];
							}
						}

						$triggerarr['ipid'] = $ipid;
						Pms_Triggers::addMetaDiagnosistocourse($triggerarr);
					}
				}
			}
		}//for
		//ISPC - 2364
		//$data['diagno_order'] = $reorder;
		//$entity = PatientDiagnoOrderTable::getInstance()->findOrCreateOneBy('ipid', $ipid, $data);
		return $reorder;
	}

	public function UpdatedischargeData ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$aabb = "'DD'";
		$dg = new DiagnosisType();
		$ddarr = $dg->getDiagnosisTypes($clientid, $aabb);

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		for ($i = 1; $i <= sizeof($post['diagnosis']); $i++)
		{

			if (strlen($post['diagnosis'][$i]) > 0)
			{
				if ($post['hidd_ids'][$i] > 0)
				{
					if (trim($post['hidd_tab'][$i]) == "dig")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis");
					}
					if (trim($post['hidd_tab'][$i]) == "text")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
						if ($post['hidd_diagnosis'][$i] == "")
						{
							$post['hidd_diagnosis'][$i] = $post['newhidd_diagnosis'][$i];
						}
					}

					$cust = Doctrine::getTable('PatientDiagnosis')->find($post['hidd_ids'][$i]);
					//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner
					$pc_listener = $cust->getListener()->get('IntenseDiagnosisConnectionListener');
					$pc_listener->setOption('disabled', true);
					//--
					$cust->diagnosis_type_id = $ddarr[0]['id'];
					$cust->diagnosis_id = $post['hidd_diagnosis'][$i];
					$cust->icd_id = $post['hidd_icdnumber'][$i];
					$cust->tabname = $tabname;
					$cust->save();
					//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
					$pc_listener->setOption('disabled', false);
					//--
				}
				else
				{

					if (trim($post['hidd_tab'][$i]) == "dig")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis");
					}
					if (trim($post['hidd_tab'][$i]) == "text")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");

						if ($post['hidd_diagnosis'][$i] == "")
						{
							$post['hidd_diagnosis'][$i] = $post['newhidd_diagnosis'][$i];
						}
					}

					$cust = new PatientDiagnosis();
					//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner
					$pc_listener = $cust->getListener()->get('IntenseDiagnosisConnectionListener');
					$pc_listener->setOption('disabled', true);
					//--
					$cust->ipid = $ipid;
					$cust->diagnosis_type_id = $ddarr[0]['id'];
					$cust->diagnosis_id = $post['hidd_diagnosis'][$i];
					$cust->icd_id = $post['hidd_icdnumber'][$i];
					$cust->tabname = $tabname;
					$cust->save();
					//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
					$pc_listener->setOption('disabled', false);
					//--
				}
			}
		}//for
	}

	public function updatePatDiagnosis ( $post )
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		if ($post['newhidd_diagnosis'][0] > 0)
		{
			$tname = "diagnosis_freetext";
			$diagnosis_id = $post['newhidd_diagnosis'][0];
		}
		else
		{
			$tname = "diagnosis";
			$diagnosis_id = $post['hidd_diagnosis'][1];
		}

		$res = Doctrine::getTable('PatientDiagnosis')->find($_GET['did']);
		//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner
		$pc_listener = $res->getListener()->get('IntenseDiagnosisConnectionListener');
		$pc_listener->setOption('disabled', true);
		//--
		$res->diagnosis_id = $diagnosis_id;
		$res->tabname = Pms_CommonData::aesEncrypt($tname);
		$res->icd_id = $post['hidd_icdnumber'][1];
		$res->save();
		//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
		$pc_listener->setOption('disabled', false);
		//--
	}

	public function FetchDiagnosisType ( $post, $darr )
	{
		$diagnosistypes = array();
		for ($i = 0; $i < sizeof($darr); $i++)
		{
			$sz = sizeof($post['dtype_' . $darr[$i]['id']]);

			for ($j = 1; $j <= $sz; $j++)
			{

				if ($post['dtype_' . $darr[$i]['id']][$j] == 1)
				{
					$diagnosistypes[$j][] = $darr[$i]['id'];
				}
			}
		}
		return $diagnosistypes;
	}

	public function insertMetaData ( $post )
	{
		if ($post['meta_diagnosis'] > 0)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$abb = "'HD'";
			$dg = new DiagnosisType();
			$darr = $dg->getDiagnosisTypes($clientid, $abb);

			$cust = new PatientDiagnosis();
			$cust->ipid = $post['ipid'];
			$cust->tabname = Pms_CommonData::aesEncrypt("diagnosis_icd");
			$cust->diagnosis_type_id = $darr[0]['id'];
			$cust->diagnosis_id = $post['meta_diagnosis'];
			$cust->icd_id = $post['meta_icdnumber'];
			$cust->save();
		}
	}

	public function updateMetaData ( $post )
	{


		$pds = new PatientDiagnosis();
		$pdarr = $pds->getPatientMainDiagnosis($post['ipid'], "diagnosis_icd");

		if (count($pdarr) > 0)
		{
			$res = Doctrine::getTable('PatientDiagnosis')->find($post['hidd_meta_diagnosis']);
			//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner
			$pc_listener = $res->getListener()->get('IntenseDiagnosisConnectionListener');
			$pc_listener->setOption('disabled', true);
			//--
			
			if ($res)
			{
				$res->diagnosis_id = $post['meta_diagnosis'];
				$res->icd_id = $post['meta_diagnosis'];
				$res->save();
				//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
				$pc_listener->setOption('disabled', false);
				//--
			}
		}
		elseif (strlen($post['meta_diagnosis']) > 0)
		{
			$abb = "'HD'";
			$dg = new DiagnosisType();
			$darr = $dg->getDiagnosisTypes($post['clientid'], $abb);
			$cust = new PatientDiagnosis();
			$cust->ipid = $post['ipid'];
			$cust->tabname = Pms_CommonData::aesEncrypt("diagnosis_icd");
			$cust->diagnosis_type_id = $darr[0]['id'];
			$cust->diagnosis_id = $post['meta_diagnosis'];
			$cust->icd_id = $post['meta_diagnosis'];
			$cust->save();
		}
	}

	public function updateDiagnosisEdit ( $post )
	{
		for ($i = 1; $i <= sizeof($post['diagnosis_ed']); $i++)
		{
			if (strlen($post['diagnosis_ed'][$i]) > 0)
			{
				if ($post['hidd_ids_ed'][$i] > 0)
				{
					if (trim($post['hidd_tab_ed'][$i]) == "dig")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis");
					}
					if (trim($post['hidd_tab_ed'][$i]) == "text")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
						if ($post['hidd_diagnosis_ed'][$i] == "")
						{
							$post['hidd_diagnosis_ed'][$i] = $post['newhidd_diagnosis_ed'][$i];
						}
					}

					$cust = Doctrine::getTable('PatientDiagnosis')->find($post['hidd_ids_ed'][$i]);
					//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner
					$pc_listener = $res->getListener()->get('IntenseDiagnosisConnectionListener');
					$pc_listener->setOption('disabled', true);
					//--
					$cust->diagnosis_type_id = $post['diagnosis_type_id_ed'][$i];
					$cust->diagnosis_id = $post['hidd_diagnosis_ed'][$i];
					$cust->icd_id = $post['hidd_icdnumber_ed'][$i];
					$cust->tabname = $tabname;
					$cust->save();
					//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
					$pc_listener->setOption('disabled', false);
					//--
				}
				else
				{
					$abb = "'AD'";
					$dg = new DiagnosisType();
					$darr = $dg->getDiagnosisTypes($post['clientid'], $abb);

					if (trim($post['hidd_tab_ed'][$i]) == "dig")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis");
					}
					if (trim($post['hidd_tab_ed'][$i]) == "text")
					{
						$tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
						if ($post['hidd_diagnosis_ed'][$i] == "")
						{
							$post['hidd_diagnosis_ed'][$i] = $post['newhidd_diagnosis_ed'][$i];
						}
					}

					$cust = new PatientDiagnosis();
					//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner
					$pc_listener = $res->getListener()->get('IntenseDiagnosisConnectionListener');
					$pc_listener->setOption('disabled', true);
					//--
					$cust->ipid = $post['ipid'];
					$cust->diagnosis_type_id = $darr[0]['id'];
					$cust->diagnosis_id = $post['hidd_diagnosis_ed'][$i];
					$cust->icd_id = $post['hidd_icdnumber_ed'][$i];
					$cust->tabname = $tabname;
					$cust->save();
					//ISPC-2614 Ancuta 12.08.2020 :: reactivate listner
					$pc_listener->setOption('disabled', false);
					//--
				}
			}
		}
	}

	public function UpdateMetatoDiagnosis ( $post )
	{

		$res = new DiagnosisText();
		$res->clientid = $post['clientid'];
		$res->free_name = $post['diagnosis'];
		$res->save();

		$cust = new PatientDiagnosis();
		//ISPC-2614 Ancuta 12.08.2020 :: deactivate listner
		$pc_listener = $res->getListener()->get('IntenseDiagnosisConnectionListener');
		$pc_listener->setOption('disabled', true);
		//--
		$cust->ipid = $post['ipid'];
		$cust->diagnosis_type_id = $post['dtype'];
		$cust->diagnosis_id = $res->id;
		$cust->icd_id = $post['icd'];
		$cust->tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
		$cust->save();
	}
	
	
	
	public function create_form_diagnosis($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
// 	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis");
// 	    dd($values);
	    $abb = "'HD','ND'";
	    if ($this->_clientModules[81]) {
	        //Show Hauptsymptomlast(HS) column in Patient diagnosis. HS diagnosis type must be created in Administration>Diagnosen Arten
	        $abb .= ",'HS'";
	    }
		//Maria:: Migration CISPC to ISPC 22.07.2020
        if ($this->_clientModules[1005]) {
            //IM-103 Show Palliativfall-begruendende Diagnose (PBD) column in Patient diagnosis. PD diagnosis type must be created in Administration>Diagnosen Arten
            $abb .= ",'PBD'";
        }
		//--
	     
	    $dt = new DiagnosisType();
	    $DiagnosisTypes = $dt->getDiagnosisTypes($this->logininfo->clientid, $abb);

	    $columns = array(
            'ICD',
            'Beschreibung',
            'HD',
        );
	    if ($this->_clientModules[81]) {
	        $columns[] = 'HS';
	    }
	    $columns[] = 'ND';
		//Maria:: Migration CISPC to ISPC 22.07.2020
        if ($this->_clientModules[1005]) {
            $columns[] = 'PBD';
        }
		// --
	    $columns[] = 'Entfernen';
	    
	    
	    $subform = $this->subFormTable(array(
	        'columns' => $columns,
	        // 'class' => 'datatable',
	    ));
	    $subform->setLegend($this->translate('Diagnosis:'));
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	    
	     
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    
	    //return $subform;
	    $row_cnt = 0;
	    
	    foreach ($values as $row) {
	        
	        if (empty($row['icd_primary']) && empty($row['description'])) 
	            continue;
	        //Maria:: Migration CISPC to ISPC 22.07.2020
	        $row_elemnts = $this->create_form_diagnosis_row($row, $elementsBelongTo , $this->_clientModules[81], $this->_clientModules[1005], $DiagnosisTypes);
	        
	        $subform->addSubForm($row_elemnts, $row_cnt);
	         
	        $row_cnt++;   
	    }
	    
	    //add button to add new contacts
	    $subform->addElement('button', 'addnew_diagnosis', array(
	        'onClick'      => ( isset($_GET['clinic']) && intval($_GET['clinic'])==1) ? 'PatientDiagnosis_addnew_clinic(this, \'PatientDiagnosis\'); return false;' : 'PatientDiagnosis_addnew(this, \'PatientDiagnosis\'); return false;',//Maria:: Migration CISPC to ISPC 22.07.2020
	        'value'        => '1',
	        'label'        => $this->translate('Add new diagnosis'),
	        'decorators'   => array(
	            'ViewHelper',
	            'FormElements',
// 	            array('HtmlTag', array('tag' => 'tr')),
 
	            array(array('data'=>'HtmlTag'),array('tag'=>'td', 'colspan' => count($columns))),
	            array(array('row'=>'HtmlTag'),array('tag'=>'tr'))
 
 
	        ),
	        'class'        =>'button btnSubmit2018 plus_icon_bg dontPrint',
	    ));
	    
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	/**
	 *  $icd_secondary->getElement('icdnumber')->setLabel('Secondary diagnoses:'); so you change the label in first TD
	 *  $diagnosisType = HD or ND
	 *  
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 * Maria:: Migration CISPC to ISPC 22.07.2020
	 */
	public function create_form_diagnosis_row ($values =  array() , $elementsBelongTo = null, $hasModule_81_HS = null, $hasModule_1005_PD = null, $DiagnosisTypes = null)
	{   
	    $__fnName = __FUNCTION__;
	    
        //if null, we search to see if client use or not module 81=HS
        if (is_null($hasModule_81_HS) ) { 
           if (is_null($this->_clientModules)) {
               $modules =  new Modules();
               $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
           }
           $hasModule_81_HS = $this->_clientModules[81];
        }
	   
        if (is_null($hasModule_1005_PD) ) {
            if (is_null($this->_clientModules)) {
                $modules =  new Modules();
                $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
            }
            $hasModule_1005_PD = $this->_clientModules[1005];
        }
	   
        //if null we get the types
        if (is_null($DiagnosisTypes)) {
           $abb = "'HD','ND'";
           if ($hasModule_81_HS) {
               $abb .= ",'HS'";
           }
            if ($hasModule_1005_PD) {
                $abb .= ",'PBD'";
            }
           $dt = new DiagnosisType();
           $DiagnosisTypes = $dt->getDiagnosisTypes($this->logininfo->clientid, $abb);
        }
        
        $diagnosis_type_id_options = array(); // this is used for the radio
        $hasModule_81_HS_value = null; //used for onChange, HS needs to be only one selected
        $diagnosis_type_id_columns = array('HD', 'HS', 'ND', 'PBD');
        foreach ($diagnosis_type_id_columns as $col) {
           foreach ($DiagnosisTypes as $row) {
               
               
               if ( ! $hasModule_81_HS && $row['abbrevation'] == 'HS') 
                   continue;
               
               if ($hasModule_81_HS && $row['abbrevation'] == 'HS') {
                   $hasModule_81_HS_value = $row['id'];
               }
               
               if ($row['abbrevation'] == $col) {
                   $diagnosis_type_id_options[$row['id']] = '';
                   break;
               }
           }
        }

        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators()->setDecorators(array('FormElements'));
	   
        
        if ( ! is_null($elementsBelongTo)) {
           $subform->setOptions(array(
               'elementsBelongTo' => $elementsBelongTo
           ));
        }

        
        $hidden_deleted_row = '';
        if (isset($values['id_deleted']) && ! empty($values['id']) && $values['id_deleted'] == $values['id']) {
            $hidden_deleted_row = 'display:hidden';
        }
        
        $diagnoSelector = uniqid("icdrow_");// used as class to delete 2 rows
        
	    $subform->addElement('text', 'icd_primary', array(
	        'label'      => null,
	        'value'    => $values['icd_primary'],
	        'required'   => false,
	        'placeholder'=> 'ICD',
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element diagicd')),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true , 'class' => "icd_holder_row {$diagnoSelector}")),
	        ),
	        'class' => 'diagicd livesearchicdinp',
	        'data-livesearch' => 'icdnumber',
	        'style' => $hidden_deleted_row,
	    ));
	    
	    
	    $subform->addElement('text', 'description', array(
	        'label'        => null,
	        'value'        => $values['description'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element diagdesc')),
	            //array('Label', array('tag' => 'td')),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	        ),
	        'class' => 'livesearchinp',
	        'data-livesearch' => 'diagnosis',
	        'style' => 'width: 100%; ',
	    ));
	    
	    $subform->addElement('radio', 'diagnosis_type_id', array(
	        //'label'        => null,
	        'value'       => $values['diagnosis_type_id'],
	        'multiOptions' => $diagnosis_type_id_options,
	        'separator'    => '</td>'. PHP_EOL.'<td>',
	        'required'     => true,
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            
	        ),
	        'data-livesearch' => 'diagnosis_type_id',
	        'onChange' => ! $hasModule_81_HS ?: 'if (this.value == \''.$hasModule_81_HS_value.'\') { $(this).parents(\'table\').find("input:radio[value=\''.$hasModule_81_HS_value.'\']:not(\'#"+this.id+"\')").attr(\'checked\', false);}',
        ));
	    
	    $subform->addElement('note', 'delete_row', array(
	        'value'  => '<a onclick="$(\'input[name*=\\\'id_deleted\\\']\', $(this).parents(\'tr\')).attr(\'disabled\', false); $(this).parents(\'tr\').hide(); $(\'input:text\', $(this).parents(\'tr\')).remove(); $(this).parents(\'table\').find(\'tr.user_holder_row.'.$diagnoSelector.'\').remove();" class="delete_row" title="'.$this->translate('delete row').'" href="javascript:void(0)"></a>',
	        'escape' => false,
	        'alt' => 'delete row',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td', 
	                'class' => 'align_center',
	            )),
	        ),	         
	    ));

	    
	    //add hidden
	    $subform->addElement('hidden', 'id', array(
	        'value' => $values['id'],
	        'readonly' => true,
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'class' => 'hidden')),
	        ),
	        'data-livesearch' => 'id'
	    ));
	    
	    $subform->addElement('hidden', 'id_deleted', array(
	        'value' => $values['id'],
	        'readonly' => true,
	        'disabled' => true,
	        'decorators' => array('ViewHelper'),
	        'data-livesearch' => 'id_deleted'
	    ));
	    
	    
	    $subform->addElement('hidden', 'diagnosis_id', array(
	        'value' => $values['diagnosis_id'],
	        'decorators' => array('ViewHelper'),
	        'data-livesearch' => 'diagnosis_id'
	    ));
	    $subform->addElement('hidden', 'icd_id', array(
	        'value' => $values['icd_id'],
	        'decorators' => array('ViewHelper'),
	        'data-livesearch' => 'icd_id'
	    ));
// 	    $subform->addElement('hidden', 'description', array(
// 	        'value' => '',
// 	        'decorators' => array('ViewHelper'),
// 	        'data-livesearch' => 'description'
// 	    ));
	    $subform->addElement('hidden', 'date', array(
	        'value' => $values['date'],
	        'decorators' => array('ViewHelper'),
	        'data-livesearch' => 'date'
	    ));
	    $subform->addElement('hidden', 'tabname', array(
	        'value' => $values['tabname'],
	        'data-livesearch' => 'tabname',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	            
	            
	        ),
	    ));
	    
	    
	   
        if (is_null($this->_clientModules)) {
            $modules =  new Modules();
            $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
        }
        
	    //if module 180 then add user that performed 
	    if ($this->_clientModules[180]) 
	    {
	        $subformParticipant = $this->subFormTableRow(['class' => "user_holder_row {$diagnoSelector}"]);
	        
    	    $subformParticipant->addElement('text', 'participant_name', array(
    	        'belongsTo' => 'PatientDiagnosisParticipants',
    	        'label'      => null,
    	        'value'    => $values['PatientDiagnosisParticipants']['participant_name'],
    	        'required'   => false,
    	        'placeholder'=> $this->translate('user'),
    	        'filters'    => array('StringTrim'),
    	        'validators' => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
    	            //array('Label', array('tag' => 'td')),
//     	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'class' => "user_holder_row {$diagnoSelector}")),
    	        ),
    	        'class' => '',
    	        'style' => $hidden_deleted_row,
    	        'data-livesearch'      => 'unifiedProvider',
    	        'data-livesearch_options'  => json_encode(['limitSearchGroups'=> ['user', 'voluntaryworker']]),
    	    ));
    	    
    	    
    	    //add hidden
    	    $subformParticipant->addElement('hidden', 'participant_id', array(
    	        'belongsTo' => 'PatientDiagnosisParticipants',
    	        'value' => $values['PatientDiagnosisParticipants']['participant_id'],
    	        'readonly' => true,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'class' => 'hidden')),
    	        ),
    	        'data-livesearch' => 'id'
    	    ));
    	    
    	    $subformParticipant->addElement('hidden', 'participant_type', array(
    	        'belongsTo' => 'PatientDiagnosisParticipants',
    	        'value' => $values['PatientDiagnosisParticipants']['participant_type'],
    	        'readonly' => true,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'class' => 'hidden')),
//     	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    	        ),
    	        'data-livesearch' => 'id'
    	    ));
    	    
    	    $subform->addSubForm($subformParticipant, 'PatientDiagnosisParticipants');
	    
	    }
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	    
	}
	
	
	public function save_form_diagnosis ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    $wlassessment_id = $data['wlassessment_id'];
	    unset($data['wlassessment_id']);
	    
	    $entity = new PatientDiagnosis();
	    $old_PatientDiagnosis = $entity->getAllDiagnosis($ipid);
	    
	    $old_PatientDiagnosis = $old_PatientDiagnosis[$ipid];
	    
	    //delete missing ids from your post
	    //alternative you can add on delete a javascript marker for that row, maybe post-max-size or other fails, or 2 users are editing at the same time 
// 	    if ( ! empty($old_PatientDiagnosis)) {
	        
//     	    $old_Diagnosis_ids = array_column($old_PatientDiagnosis, 'id');
    	    
//     	    $post_ids = array_filter(array_column($data, 'id'));
    	    
//     	    $deleted_ids = array_diff($old_Diagnosis_ids, $post_ids);
    	    
//     	    if ( ! empty($deleted_ids)) {
//     	       //this must be deleted
//     	        $query = $entity->getTable()->createQuery()
//     	        ->delete()
//     	        ->whereIn('id', $deleted_ids)
//     	        ->andWhere('ipid = ? ', $ipid)
//     	        ->execute();
//     	    }
// 	    }
	     
	    $deleted_ids = array();//this will hold the ids that must be deleted
	    foreach ($data as &$row) {
	        if (isset($row['id_deleted']) && $row['id_deleted'] == $row['id']) {	             
	            $deleted_ids[] = $row['id_deleted'];
// 	            unset($data[$k]);
	            $row['id'] = null;
	        }
	        
	    }
	    if ( ! empty($deleted_ids)) {
	         
	       //this must be deleted
	        $query = $entity->getTable()->createQuery()
	        ->delete()
	        ->whereIn('id', $deleted_ids)
	        ->andWhere('ipid = ? ', $ipid)
	        ->execute();
	    }
	    $new_diagnosis = array();//this will hold the new ones
	    unset($row);
	    
	    
	    //update if modified diagnosis_type_id
	    foreach ($data as $row) {
	        
	        if (empty($row['id'])) {
	            //this may be new, if not wmpty texts
	            $new_diagnosis[] = $row;
	            continue;
	            
	        } elseif ( (int)$old_PatientDiagnosis [$row['id']] ['diagnosis_type_id'] != (int)$row['diagnosis_type_id']) { //cast used to force null = 0
	        
	            //modified diagnosis_type_id 
	            $query = $entity->getTable()->createQuery()
	            ->update()
	            ->set('diagnosis_type_id', '?', $row['diagnosis_type_id'] )
	            ->where('id = ?', $row['id'])
	            ->andWhere('ipid = ? ', $ipid)
	            ->execute();
	            
	            $a_post = array(
	                'ipid' => $ipid,
	                'diagnosis_id' => $row['diagnosis_id'],
	                'diagnosis_type' => $row['diagnosis_type_id'],
	                'diagnosis' => $row['description'],
	            );
                Pms_Triggers::DiagnosisTypeChange($a_post);
 
	        }
	        
	        /*
	         * update the participant who did this action
	         */
	        if ($row['id'] && ! empty($row['PatientDiagnosisParticipants'])) {
	            PatientDiagnosisParticipantsTable::getInstance()->findOrCreateOneBy('patient_diagnosis_id', $row['id'], $row['PatientDiagnosisParticipants']);
	        }
	    }
	    unset($row);
	    
	    //insert new
	    if ( ! empty($new_diagnosis)) {
	        
	        foreach ($new_diagnosis as $k=>&$row) {
	            
	            if ( ! isset($row['icd_primary']) || (strlen(trim($row['icd_primary'])) == 0 && strlen(trim($row['description'])) == 0)) {
	                //we don't save empty rows or deleted ones
	                unset($new_diagnosis[$k]);
	                continue;
	            }
	            
	            if ($row['tabname'] == 'diagnosis_freetext') {
	                //this saves the 2 text-inputs in another table, just for fun
	                $dt = new DiagnosisText();
	                $DiagnosisText = $dt->findOrCreateOneBy('id', null, array(
	                    'clientid' => $this->logininfo->clientid,
	                    'free_name' => $row['description'],
	                    'icd_primary' => $row['icd_primary'],
	                ));
	                $row['diagnosis_id'] = $DiagnosisText->id;
	            }
	            
	            $row['ipid'] = $ipid;
	            $row['tabname'] = Pms_CommonData::aesEncrypt($row['tabname']);
	            
	        }
	        if ( ! empty($new_diagnosis)) {
	            $records =  new Doctrine_Collection('PatientDiagnosis');
	            $records->fromArray($new_diagnosis);
	            $records->save();
	        }
	        
	        
        }
	    
	    return true;
	    
	     
	}
	     
	
	
	
	public function create_form_diagnosis_clinical($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
// 	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis");
// 	    dd($values);
	    $abb = "'HD','ND'";
	    if ($this->_clientModules[81]) {
	        //Show Hauptsymptomlast(HS) column in Patient diagnosis. HS diagnosis type must be created in Administration>Diagnosen Arten
	        $abb .= ",'HS'";
	    }
		//Maria:: Migration CISPC to ISPC 22.07.2020
        if ($this->_clientModules[1005]) {
            //IM-103 Show Palliativfall-begruendende Diagnose (PBD) column in Patient diagnosis. PD diagnosis type must be created in Administration>Diagnosen Arten
            $abb .= ",'PBD'";
        }
		//--
	     
	    $dt = new DiagnosisType();
	    $DiagnosisTypes = $dt->getDiagnosisTypes($this->logininfo->clientid, $abb);

// 	    dd($values);
	    
/* 	    $columns = array(
            'ICD',
            'Beschreibung',
            'HD',
        );
	    if ($this->_clientModules[81]) {
	        $columns[] = 'HS';
	    }
	    $columns[] = 'ND';
		//Maria:: Migration CISPC to ISPC 22.07.2020
        if ($this->_clientModules[1005]) {
            $columns[] = 'PBD';
        }
		// --
	    $columns[] = 'Entfernen';
	    
	    
	    */
	    $subform = $this->subFormTable(array(
	        'columns' => $columns,
	        // 'class' => 'datatable',
	    )); 
	    $subform->setLegend($this->translate('Diagnosis:'));
	    $subform->setAttrib("class", "label_same_size_auto livesearchFormEvents {$__fnName}");
	    $subform->addDecorator('Form',array('class'=>'icd_from_add', 'id'=>'icd_form','method'=>'post'));
	    $subform->removeDecorator('Fieldset');
	     
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    
	    //return $subform;
	    $row_cnt = 0;
        $row_elemnts = $this->create_form_diagnosis_clinical_row($values, $elementsBelongTo , $this->_clientModules[81], $this->_clientModules[1005], $DiagnosisTypes);
        $subform->addSubForm($row_elemnts, $row_cnt);
 
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	/**
	 *  $icd_secondary->getElement('icdnumber')->setLabel('Secondary diagnoses:'); so you change the label in first TD
	 *  $diagnosisType = HD or ND
	 *  
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 * Maria:: Migration CISPC to ISPC 22.07.2020
	 */
	public function create_form_diagnosis_clinical_row ($values =  array() , $elementsBelongTo = null, $hasModule_81_HS = null, $hasModule_1005_PD = null, $DiagnosisTypes = null)
	{   
	    $__fnName = __FUNCTION__;
	    
        //if null, we search to see if client use or not module 81=HS
        if (is_null($hasModule_81_HS) ) { 
           if (is_null($this->_clientModules)) {
               $modules =  new Modules();
               $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
           }
           $hasModule_81_HS = $this->_clientModules[81];
        }
	   
        if (is_null($hasModule_1005_PD) ) {
            if (is_null($this->_clientModules)) {
                $modules =  new Modules();
                $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
            }
            $hasModule_1005_PD = $this->_clientModules[1005];
        }
	   
        //if null we get the types
        if (is_null($DiagnosisTypes)) {
           $abb = "'HD','ND'";
           if ($hasModule_81_HS) {
               $abb .= ",'HS'";
           }
            if ($hasModule_1005_PD) {
                $abb .= ",'PBD'";
            }
           $dt = new DiagnosisType();
           $DiagnosisTypes = $dt->getDiagnosisTypes($this->logininfo->clientid, $abb);
        }
        
        $diagnosis_type_id_options = array(); // this is used for the radio
        $hasModule_81_HS_value = null; //used for onChange, HS needs to be only one selected
        $diagnosis_type_id_columns = array('HD', 'HS', 'ND', 'PBD');
        foreach ($diagnosis_type_id_columns as $col) {
           foreach ($DiagnosisTypes as $row) {
               
               
               if ( ! $hasModule_81_HS && $row['abbrevation'] == 'HS') 
                   continue;
               
               if ($hasModule_81_HS && $row['abbrevation'] == 'HS') {
                   $hasModule_81_HS_value = $row['id'];
               }
               
               if ($row['abbrevation'] == $col) {
                   $diagnosis_type_id_options[$row['id']] = '';
                   break;
               }
           }
        }

        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators()->setDecorators(array('FormElements'));
	   
        
//         if ( ! is_null($elementsBelongTo)) {
//            $subform->setOptions(array(
//                'elementsBelongTo' => $elementsBelongTo
//            ));
//         }

        
        $hidden_deleted_row = '';
        if (isset($values['id_deleted']) && ! empty($values['id']) && $values['id_deleted'] == $values['id']) {
            $hidden_deleted_row = 'display:hidden';
        }
        
        $diagnoSelector = uniqid("icdrow_");// used as class to delete 2 rows
        
        
        //TODO-4120 Ancuta 07.05.2021
        // main category
//         $main_category_items = array('main_diagnosis'=>'icd_main_diagnosis','primary_disease'=>'icd_primary_disease','secondary_disease'=>'icd_secondary_disease');
        $main_category_items = array('main_diagnosis'=>'icd_main_diagnosis','secondary_disease'=>'icd_secondary_disease');
        // --
        $subform->addElement('radio', 'main_category', array(
            'label'        => "Kategorie",
            'value'       => $values['PatientDiagnosisClinical']['main_category'],
            'multiOptions' => $main_category_items,
//             'separator'    => '</td>'. PHP_EOL.'<td>',
            'required'     => true,
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element diagicd','style'=>'vertical-align: top')),
                array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , "openOnly" => true )),
                
            ),
        ));
   
        $values['PatientDiagnosisClinical']['side_category'] = array();
        if($values['PatientDiagnosisClinical']['symptoms'] == 'yes'){
            $values['PatientDiagnosisClinical']['side_category'][] = 'symptoms';
        }
        if($values['PatientDiagnosisClinical']['archived'] == 'yes'){
            $values['PatientDiagnosisClinical']['side_category'][] = 'archived';
        }
        if($values['PatientDiagnosisClinical']['side_diagnosis'] == 'yes'){
            $values['PatientDiagnosisClinical']['side_category'][] = 'side_diagnosis';
        }
        if($values['PatientDiagnosisClinical']['relevant2hospitalstay'] == 'yes'){
            $values['PatientDiagnosisClinical']['side_category'][] = 'relevant2hospitalstay';
        }
            
        $secondary_category_items = array('symptoms'=>'icd_symptoms','archived'=>'icd_archived','side_diagnosis'=>'icd_side_diagnosis','relevant2hospitalstay'=>'icd_relevant2hospitalstay');
        $subform->addElement('multiCheckbox', 'side_category', array(
//             'label'        => "",
            'value'       => $values['PatientDiagnosisClinical']['side_category'],
            'multiOptions' => $secondary_category_items,
//             'separator'    => '</td>'. PHP_EOL.'<td>',
            'required'     => true,
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element side_labels','style'=>'vertical-align: top')),
//                 array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', "closeOnly" => true  )),
                
            ),
        ));
        
        
	    $subform->addElement('text', 'icd_primary', array(
	        'label'      => 'icd_primary',
	        'value'    => $values['icd_primary'],
	        'required'   => false,
	        'placeholder'=> 'ICD',
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element diagicd', "openOnly" => true , 'colspan'=>'2')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', "openOnly" => true ,  'class' => "icd_holder_row {$diagnoSelector}")),
	        ),
	        'class' => 'diagicd livesearchicdinp',
	        'data-livesearch' => 'icdnumber',
	        'style' => $hidden_deleted_row,
	    ));
	    
	    
	    $subform->addElement('text', 'description', array(
	        'label'        => 'Diagnose',
	        'value'        => $values['description'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'placeholder'=> 'Diagnose',
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element diagdesc', "closeOnly" => true)),
// 	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', "closeOnly" => true )),
	        ),
	        'class' => 'livesearchinp',
	        'data-livesearch' => 'diagnosis',
	        'style' => 'width: 70%; ',
	    ));
	    
	    $subform->addElement('radio', 'relevant2admission', array(
	        'label'        => "Aufnahmegrund",
	        'value'       => $values['PatientDiagnosisClinical']['relevant2admission'],
	        'multiOptions' => array('yes'=>'Ja','no'=>'Nein'),
	        'separator'    => '</td>'. PHP_EOL.'<td>',
	        'required'     => false,
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr'  )),
	            
	        ),
	    ));
	    
	    $subform->addElement('text', 'start_date', array(
	        'label'        => self::translate('start_date'),
	        'value'        => ! empty($values['PatientDiagnosisClinical']['start_date']) ? date('d.m.Y', strtotime($values['PatientDiagnosisClinical']['start_date'])) : "",
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'option_date StartDate',
	        'readonly'     => true,
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        
	    ));
	    $subform->addElement('text', 'end_date', array(
	        'label'        => self::translate('end_date'),
	        'value'        => ! empty($values['PatientDiagnosisClinical']['end_date']) && $values['PatientDiagnosisClinical']['end_date']!= "0000-00-00 00:00:00" ? date('d.m.Y', strtotime($values['PatientDiagnosisClinical']['end_date'])) : "",
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => 'option_date EndDate',
	        'readonly'     => true,
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2')),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        
	    ));
	    $subform->addElement('textarea', 'comments', array(
	        'label'        => "Kommentar",
	        'value'        =>  $values['comments'] ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'class'        => ' ',
	        'decorators' =>   array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2','style'=>'vertical-align: top' )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first','style'=>'vertical-align: top')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
	        ),
	        'rows'=>'5',
	        'cols'=>'50'
	        
	    ));
	    
	    /* 
	    $subform->addElement('radio', 'diagnosis_type_id', array(
	        //'label'        => null,
	        'value'       => $values['diagnosis_type_id'],
	        'multiOptions' => $diagnosis_type_id_options,
	        'separator'    => '</td>'. PHP_EOL.'<td>',
	        'required'     => true,
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            
	        ),
	        'data-livesearch' => 'diagnosis_type_id',
	        'onChange' => ! $hasModule_81_HS ?: 'if (this.value == \''.$hasModule_81_HS_value.'\') { $(this).parents(\'table\').find("input:radio[value=\''.$hasModule_81_HS_value.'\']:not(\'#"+this.id+"\')").attr(\'checked\', false);}',
        ));
	    
	    $subform->addElement('note', 'delete_row', array(
	        'value'  => '<a onclick="$(\'input[name*=\\\'id_deleted\\\']\', $(this).parents(\'tr\')).attr(\'disabled\', false); $(this).parents(\'tr\').hide(); $(\'input:text\', $(this).parents(\'tr\')).remove(); $(this).parents(\'table\').find(\'tr.user_holder_row.'.$diagnoSelector.'\').remove();" class="delete_row" title="'.$this->translate('delete row').'" href="javascript:void(0)"></a>',
	        'escape' => false,
	        'alt' => 'delete row',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td', 
	                'class' => 'align_center',
	            )),
	        ),	         
	    )); */

	    
	    //add hidden
	    $subform->addElement('hidden', 'id', array(
	        'value' => $values['id'],
	        'readonly' => true,
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'class' => 'hidden')),
	        ),
	        'data-livesearch' => 'id'
	    ));
	    
	    $subform->addElement('hidden', 'id_deleted', array(
	        'value' => $values['id'],
	        'readonly' => true,
	        'disabled' => true,
	        'decorators' => array('ViewHelper'),
	        'data-livesearch' => 'id_deleted'
	    ));
	    
	    
	    $subform->addElement('hidden', 'diagnosis_id', array(
	        'value' => $values['diagnosis_id'],
	        'decorators' => array('ViewHelper'),
	        'data-livesearch' => 'diagnosis_id'
	    ));
	    $subform->addElement('hidden', 'icd_id', array(
	        'value' => $values['icd_id'],
	        'decorators' => array('ViewHelper'),
	        'data-livesearch' => 'icd_id'
	    ));
// 	    $subform->addElement('hidden', 'description', array(
// 	        'value' => '',
// 	        'decorators' => array('ViewHelper'),
// 	        'data-livesearch' => 'description'
// 	    ));
	    $subform->addElement('hidden', 'date', array(
	        'value' => $values['date'],
	        'decorators' => array('ViewHelper'),
	        'data-livesearch' => 'date'
	    ));
	    $subform->addElement('hidden', 'tabname', array(
	        'value' => $values['tabname'],
	        'data-livesearch' => 'tabname',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
	            //array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
	            
	            
	        ),
	    ));
	    
	    
	   
        if (is_null($this->_clientModules)) {
            $modules =  new Modules();
            $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
        }
        
	    //if module 180 then add user that performed 
	   /*  if ($this->_clientModules[180]) 
	    {
	        $subformParticipant = $this->subFormTableRow(['class' => "user_holder_row {$diagnoSelector}"]);
	        
    	    $subformParticipant->addElement('text', 'participant_name', array(
    	        'belongsTo' => 'PatientDiagnosisParticipants',
    	        'label'      => null,
    	        'value'    => $values['PatientDiagnosisParticipants']['participant_name'],
    	        'required'   => false,
    	        'placeholder'=> $this->translate('user'),
    	        'filters'    => array('StringTrim'),
    	        'validators' => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
    	            //array('Label', array('tag' => 'td')),
//     	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'class' => "user_holder_row {$diagnoSelector}")),
    	        ),
    	        'class' => '',
    	        'style' => $hidden_deleted_row,
    	        'data-livesearch'      => 'unifiedProvider',
    	        'data-livesearch_options'  => json_encode(['limitSearchGroups'=> ['user', 'voluntaryworker']]),
    	    ));
    	    
    	    
    	    //add hidden
    	    $subformParticipant->addElement('hidden', 'participant_id', array(
    	        'belongsTo' => 'PatientDiagnosisParticipants',
    	        'value' => $values['PatientDiagnosisParticipants']['participant_id'],
    	        'readonly' => true,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'class' => 'hidden')),
    	        ),
    	        'data-livesearch' => 'id'
    	    ));
    	    
    	    $subformParticipant->addElement('hidden', 'participant_type', array(
    	        'belongsTo' => 'PatientDiagnosisParticipants',
    	        'value' => $values['PatientDiagnosisParticipants']['participant_type'],
    	        'readonly' => true,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'class' => 'hidden')),
//     	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    	        ),
    	        'data-livesearch' => 'id'
    	    ));
    	    
    	    $subform->addSubForm($subformParticipant, 'PatientDiagnosisParticipants');
	    
	    }
	     */
	    return $this->filter_by_block_name($subform, $__fnName);
	    
	}
	
	
	public function save_form_diagnosis_clinical ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	
	    $wlassessment_id = $data['wlassessment_id'];
	    unset($data['wlassessment_id']);
	 
	    $entity = new PatientDiagnosis();
	    $old_PatientDiagnosis = $entity->getAllDiagnosis($ipid);
	    
	    $old_PatientDiagnosis = $old_PatientDiagnosis[$ipid];
	    
	    //if null we get the types
	    if (is_null($DiagnosisTypes)) {
	        $abb = "'HD','ND'";
	        if ($hasModule_81_HS) {
	            $abb .= ",'HS'";
	        }
	        if ($hasModule_1005_PD) {
	            $abb .= ",'PBD'";
	        }
	        $dt = new DiagnosisType();
	        $DiagnosisTypes = $dt->getDiagnosisTypes($this->logininfo->clientid, $abb);
	    }
	    $mapp_type2id = array();
	    foreach($DiagnosisTypes as $k=>$oldt){
	        $mapp_type2id[$oldt['abbrevation']] = $oldt['id'];
	    }
	    
	    $deleted_ids = array();//this will hold the ids that must be deleted
	    foreach ($data as &$row) {
	        if (isset($row['id_deleted']) && $row['id_deleted'] == $row['id']) {	             
	            $deleted_ids[] = $row['id_deleted'];
	            $row['id'] = null;
	        }
	        
	    }
	    if ( ! empty($deleted_ids)) {
	         
	       //this must be deleted
	        $query = $entity->getTable()->createQuery()
	        ->delete()
	        ->whereIn('id', $deleted_ids)
	        ->andWhere('ipid = ? ', $ipid)
	        ->execute();
	    }
	    $new_diagnosis = array();//this will hold the new ones
	    $existing_diagnosis =array(); //this will hold the existing
	    unset($row);
	    
	    
	    //update if modified diagnosis_type_id
	    foreach ($data as $row) {
	        
	        if($row['main_category'] == 'main_diagnosis'){
	            $row['diagnosis_type_id'] = $mapp_type2id['HD'];
	        }
	        if($row['main_category'] == 'secondary_disease'){
	            $row['diagnosis_type_id'] = $mapp_type2id['ND'];
	        }
	        if(!empty($row['side_category'])){
	            foreach($row['side_category'] as $sk=>$side_c){
	                $row[$side_c] = 'yes';
	            }
	            unset($row['side_category']);
	        }
	        
	        if (empty($row['id'])) {
	            //this may be new, if not wmpty texts
	            $new_diagnosis[] = $row;
	            continue;
	            
	        } else {
	            
	            /* if((int)$old_PatientDiagnosis [$row['id']] ['diagnosis_type_id'] != (int)$row['diagnosis_type_id']) { //cast used to force null = 0
    	            //modified diagnosis_type_id 
    	            $query = $entity->getTable()->createQuery()
    	            ->update()
    	            ->set('diagnosis_type_id', '?', $row['diagnosis_type_id'] )
    	            ->where('id = ?', $row['id'])
    	            ->andWhere('ipid = ? ', $ipid)
    	            ->execute();
    	            
    	            $a_post = array(
    	                'ipid' => $ipid,
    	                'diagnosis_id' => $row['diagnosis_id'],
    	                'diagnosis_type' => $row['diagnosis_type_id'],
    	                'diagnosis' => $row['description'],
    	            );
                    Pms_Triggers::DiagnosisTypeChange($a_post);
 
    	        } else {
    	             // UPDATE existing
    	            
    	        } */
    	        $existing_diagnosis[] = $row;
    	        continue;
	        }
 
	    }
	    //unset($row);
	    
	    
	    
	    
	    //insert new
// 	    dd($new_diagnosis);
	    if ( ! empty($new_diagnosis)) {
 
	        foreach ($new_diagnosis as $k=>&$row) {
	            
	            if ( ! isset($row['icd_primary']) || (strlen(trim($row['icd_primary'])) == 0 && strlen(trim($row['description'])) == 0)) {
	                //we don't save empty rows or deleted ones
	                unset($new_diagnosis[$k]);
	                continue;
	            }
	            
	            if($row['main_category'] == 'main_diagnosis'){
	                $row['diagnosis_type_id'] = $mapp_type2id['HD'];
	            }
	            if($row['main_category'] == 'secondary_disease'){
	                $row['diagnosis_type_id'] = $mapp_type2id['ND'];
	            }
	            if(!empty($row['side_category'])){
	                foreach($row['side_category'] as $sk=>$side_c){
	                    $row[$side_c] = 'yes';
	                }
	               unset($row['side_category']);
	            }
	            $row['tabname'] ='diagnosis_freetext';
	            if ($row['tabname'] == 'diagnosis_freetext') {
	                //this saves the 2 text-inputs in another table, just for fun
	                $dt = new DiagnosisText();
	                $DiagnosisText = $dt->findOrCreateOneBy('id', null, array(
	                    'clientid' => $this->logininfo->clientid,
	                    'free_name' => $row['description'],
	                    'icd_primary' => $row['icd_primary'],
	                ));
	                $row['diagnosis_id'] = $DiagnosisText->id;
	            }
	            
	            $row['ipid'] = $ipid;
	            $row['tabname'] = Pms_CommonData::aesEncrypt($row['tabname']);
	            
	        }
	        $c = 0 ;
	        $clinical = array();
	        foreach($new_diagnosis as $k=>$neow){
	            $clinical[$c]['ipid'] = $neow['ipid'];
	            $clinical[$c]['main_category'] = $neow['main_category'];
	            $clinical[$c]['side_category']= $neow['side_category'];
	            $clinical[$c]['start_date']= (!empty($neow['start_date'])) ? date('Y-m-d H:i:s',strtotime($neow['start_date'])) : date('Y-m-d H:i:s');
	            $clinical[$c]['end_date']= (!empty($neow['end_date'])) ? date('Y-m-d H:i:s',strtotime($neow['end_date'])) : ""; //$neow['end_date'];
	            $clinical[$c]['relevant2admission']= $neow['relevant2admission'];
	            $clinical[$c]['symptoms']= $neow['symptoms'];
	            $clinical[$c]['archived']= $neow['archived'];
	            $clinical[$c]['side_diagnosis']= $neow['side_diagnosis'];
	            $clinical[$c]['relevant2hospitalstay']= $neow['relevant2hospitalstay'];
	        }
	        
	        $normal = $new_diagnosis ;
	        foreach($normal as $k=>&$drow){
	            unset($drow['main_category']);
	            unset($drow['side_category']);
	            unset($drow['start_date']);
	            unset($drow['end_date']);
	            unset($drow['relevant2admission']);
	            unset($drow['symptoms']);
	            unset($drow['archived']);
	            unset($drow['side_diagnosis']);
	            unset($drow['relevant2hospitalstay']);
	        }
	        
	        $records =  new Doctrine_Collection('PatientDiagnosis');
	        $records->fromArray($normal);
            $records->save();
            $flag = $records->getPrimaryKeys();
       
            if(!empty($flag)){
                $clinical[0]['patient_diagnosis_id']= $flag[0];
    	        $records =  new Doctrine_Collection('PatientDiagnosisClinical');
    	        $records->fromArray($clinical);
    	        $records->save();
            }
	        
        }
       
        if ( ! empty($existing_diagnosis)) {
        
        	foreach ($existing_diagnosis as $k=>&$row) {
        		 
        		if ( ! isset($row['icd_primary']) || (strlen(trim($row['icd_primary'])) == 0 && strlen(trim($row['description'])) == 0)) {
        			//we don't save empty rows or deleted ones
        			unset($existing_diagnosis[$k]);
        			continue;
        		}
        		 
        		if($row['main_category'] == 'main_diagnosis'){
        			$row['diagnosis_type_id'] = $mapp_type2id['HD'];
        		}
        		elseif($row['main_category'] == 'primary_disease'){
        			$row['diagnosis_type_id'] = 0;
        		}
        		
        		if($row['main_category'] == 'secondary_disease'){
        			$row['diagnosis_type_id'] = $mapp_type2id['ND'];
        		}
        		if(!empty($row['side_category'])){
        			foreach($row['side_category'] as $sk=>$side_c){
        				$row[$side_c] = 'yes';
        			}
        			unset($row['side_category']);
        		}
        		$row['tabname'] ='diagnosis_freetext';
        		if ($row['tabname'] == 'diagnosis_freetext') {
        			//this saves the 2 text-inputs in another table, just for fun
        			$dt = new DiagnosisText();
        			$DiagnosisText = $dt->findOrCreateOneBy('id', null, array(
        					'clientid' => $this->logininfo->clientid,
        					'free_name' => $row['description'],
        					'icd_primary' => $row['icd_primary'],
        			));
        			$row['diagnosis_id'] = $DiagnosisText->id;
        		}
        		 
        		$row['ipid'] = $ipid;
        		$row['tabname'] = Pms_CommonData::aesEncrypt($row['tabname']);
        		 
        	}
        	
        	foreach($existing_diagnosis as $k=>$neow){
        		$clinical = array();
        		$clinical['patient_diagnosis_id'] = $neow['id'];
        		$clinical['ipid'] = $neow['ipid'];
        		$clinical['main_category'] = $neow['main_category'];
        		$clinical['side_category']= $neow['side_category'];
        		$clinical['start_date']= (!empty($neow['start_date'])) ? date('Y-m-d H:i:s',strtotime($neow['start_date'])) : date('Y-m-d H:i:s');
        		$clinical['end_date']= (!empty($neow['end_date'])) ? date('Y-m-d H:i:s',strtotime($neow['end_date'])) : ""; //$neow['end_date'];
        		$clinical['relevant2admission']= $neow['relevant2admission'];
        		$clinical['symptoms']= $neow['symptoms'];
        		$clinical['archived']= $neow['archived'];
        		$clinical['side_diagnosis']= $neow['side_diagnosis'];
        		$clinical['relevant2hospitalstay']= $neow['relevant2hospitalstay'];
        		
        		$patdclentity = PatientDiagnosisClinicalTable::getInstance()->findOrCreateOneBy(array('ipid', 'patient_diagnosis_id'), array($neow['ipid'], $neow['id']), $clinical);
        	}
        	 
        	$normal = $existing_diagnosis ;
        	foreach($normal as $k=>&$drow){
        		unset($drow['main_category']);
        		unset($drow['side_category']);
        		unset($drow['start_date']);
        		unset($drow['end_date']);
        		unset($drow['relevant2admission']);
        		unset($drow['symptoms']);
        		unset($drow['archived']);
        		unset($drow['side_diagnosis']);
        		unset($drow['relevant2hospitalstay']);
        		
        		$patdentity = PatientDiagnosisTable::getInstance()->findOrCreateOneBy(array('ipid', 'id'), array($drow['ipid'], $drow['id']), $drow);
        		if((int)$old_PatientDiagnosis [$drow['id']] ['diagnosis_type_id'] != (int)$drow['diagnosis_type_id']) { //cast used to force null = 0
        			//modified diagnosis_type_id
        			/* $query = $entity->getTable()->createQuery()
        			->update()
        			->set('diagnosis_type_id', '?', $row['diagnosis_type_id'] )
        			->where('id = ?', $row['id'])
        			->andWhere('ipid = ? ', $ipid)
        			->execute(); */
        			 
        			$a_post = array(
        					'ipid' => $drow['ipid'],
        					'diagnosis_id' => $drow['diagnosis_id'],
        					'diagnosis_type' => (int)$drow['diagnosis_type_id'],
        					'diagnosis' => $drow['icd_primary'].'|'.$drow['description'],
        			);
        			Pms_Triggers::DiagnosisTypeChange($a_post);
        		
        		}
        		
        	}
        	//var_dump($normal); exit;
        	
        	/* $records =  new Doctrine_Collection('PatientDiagnosis');
        	$records->fromArray($normal);
        	$records->save();
        	$flag = $records->getPrimaryKeys();
        	 
        	if(!empty($flag)){
        		$clinical[0]['patient_diagnosis_id']= $flag[0];
        		$records =  new Doctrine_Collection('PatientDiagnosisClinical');
        		$records->fromArray($clinical);
        		$records->save();
        	} */
        	 
        }
	    
	    return true;
	    
	     
	}


	/* ISPC-2831 Dragos 15.03.2021 add $noactions parameter */
	public function create_diagnosis_clinical($blockname, $options,$ipid,$clientid, $noactions = false){
	    
	    
	    $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
	    
// 	    dd($options);
	    // category info - saved by client or default
	    $ioms_arr = IcdOpsMreSettings::getIcdOpsMreSettings($clientid);
	    $client_data = array();
	    foreach($ioms_arr as $k=>$cl_data){
	        $client_data[$cl_data['category']] = $cl_data;
	    }
	    
	    $default_categories_array = Pms_CommonData::get_diagnosis_category_default();
	    
	    $categories = array();
	    foreach($default_categories_array as $ck=>$dk){
	        $categories[$dk['db_name']] = $dk;
	        if(!empty($client_data[$dk['category']])){
	            $categories[$dk['db_name']]['color'] = '#'.$client_data[$dk['category']]['color'];
	        } 
	    }
// 	    dd($client_data,$categories);
	    
	    $data['diagnosis_categories'] = $categories;
	    
// 	    dd($options);
	    $kops= 0; 
	    $arc = 0;
	    $end_inc = 0;
	    $icd_inc = 0;
	    $has_custom_order = array();
	    foreach($options as $kd => $vops)
	    {
            if ($vops["id"]) {
                // add maind category and side categoruy
                $category_sh = "";
                $cts = array();
                if (! empty($vops['PatientDiagnosisClinical']['main_category'])) {
                	//ISPC-2775 Carmen 06.01.2021
                	if($pdf == 'pdf')
                	{
                    	$category_sh .= '<td class="cat_sh" bgcolor="' . $categories[$vops['PatientDiagnosisClinical']['main_category']]['color'] . '" > ' . $categories[$vops['PatientDiagnosisClinical']['main_category']]['shortcut'] . '</td>';
                	}
                	else 
                	{
                		$category_sh .= '<span class="cat_sh" style="background:' . $categories[$vops['PatientDiagnosisClinical']['main_category']]['color'] . '" > ' . $categories[$vops['PatientDiagnosisClinical']['main_category']]['shortcut'] . '</span>';
                	}
                    $data['used_categories'][] = $vops['PatientDiagnosisClinical']['main_category'];
                    $cts[] = $categories[$vops['PatientDiagnosisClinical']['main_category']]['shortcut'];
                }

                if (! empty($vops['PatientDiagnosisClinical']['symptoms']) && $vops['PatientDiagnosisClinical']['symptoms'] == 'yes') {
                	//ISPC-2775 Carmen 06.01.2021
                	if($pdf == 'pdf')
                	{
                    	$category_sh .= '<td class="cat_sh" bgcolor="' . $categories['symptoms']['color'] . '" > ' . $categories['symptoms']['shortcut'] . '</td>';
                	}
                	else 
                	{
                		$category_sh .= '<span class="cat_sh" style="background:' . $categories['symptoms']['color'] . '" > ' . $categories['symptoms']['shortcut'] . '</span>';
                	}
                    $data['used_categories'][] = 'symptoms';
                    $cts[] = $categories['symptoms']['shortcut'];
                }

                if (! empty($vops['PatientDiagnosisClinical']['archived']) && $vops['PatientDiagnosisClinical']['archived'] == 'yes') {
                	//ISPC-2775 Carmen 06.01.2021
                	if($pdf == 'pdf')
                	{
                		$category_sh .= '<td class="cat_sh" bgcolor="' . $categories['archived']['color'] . '" > ' . $categories['archived']['shortcut'] . '</td>';
                	}
                	else
                	{
                    	$category_sh .= '<span class="cat_sh" style="background:' . $categories['archived']['color'] . '" > ' . $categories['archived']['shortcut'] . '</span>';
                	}
                    $data['used_categories'][] = 'archived';
                    $cts[] = $categories['archived']['shortcut'];
                }

                if (! empty($vops['PatientDiagnosisClinical']['side_diagnosis']) && $vops['PatientDiagnosisClinical']['side_diagnosis'] == 'yes') {
                	//ISPC-2775 Carmen 06.01.2021
                	if($pdf == 'pdf')
                	{
                		$category_sh .= '<td class="cat_sh" bgcolor="' . $categories['side_diagnosis']['color'] . '" > ' . $categories['side_diagnosis']['shortcut'] . '</td>';
                	}
                	else 
                	{
                    	$category_sh .= '<span class="cat_sh" style="background:' . $categories['side_diagnosis']['color'] . '" > ' . $categories['side_diagnosis']['shortcut'] . '</span>';
                	}
                    $data['used_categories'][] = 'side_diagnosis';
                    $cts[] = $categories['side_diagnosis']['shortcut'];
                }

                if (! empty($vops['PatientDiagnosisClinical']['relevant2hospitalstay']) && $vops['PatientDiagnosisClinical']['relevant2hospitalstay'] == 'yes') {
                	//ISPC-2775 Carmen 06.01.2021
                	if($pdf == 'pdf')
                	{
                		$category_sh .= '<td class="cat_sh" bgcolor="' . $categories['relevant2hospitalstay']['color'] . '" > ' . $categories['relevant2hospitalstay']['shortcut'] . '</td>';
                	}
                	else 
                	{
                    	$category_sh .= '<span class="cat_sh" style="background:' . $categories['relevant2hospitalstay']['color'] . '" > ' . $categories['relevant2hospitalstay']['shortcut'] . '</span>';
                	}
                    $data['used_categories'][] = 'relevant2hospitalstay';
                    $cts[] = $categories['relevant2hospitalstay']['shortcut'];
                }
  
                
                
                if($vops['custom_order']!="9999"){
                    $has_custom_order[] = $vops['id'];
                }
                $data['icd_data'][$kops]['order_value'] = $vops['id'];
                $data['icd_data'][$kops]['order'] ='<span class="item-sort-'.$vops['id'].'">  </span><span class="drag-sort"><img title="' . $this->translate("edit") . '" width="16" height="16" border="0" src="' . RES_FILE_PATH . '/images/arrow_updown_icon.png" /></span>';
                $data['icd_data'][$kops]['icd_category'] = $category_sh;
//                 $data['icd_data'][$kops]['icd_category'] = $category_sh.''.implode(',',$cts);;
//                 $data['icd_data'][$kops]['icd_category_hidden'] = implode(',',$cts);
                $data['icd_data'][$kops]['icd_code'] = $vops['PatientDiagnosisClinical']['icd_code'];
                $data['icd_data'][$kops]['icd_description'] = $vops['PatientDiagnosisClinical']['icd_description'];
                //ISPC-2775 carmen 06.01.2021
                if($pdf == 'pdf')
                {
                	$data['icd_data'][$kops]['relevant2admission'] = $vops['PatientDiagnosisClinical']['relevant2admission'] == "yes" ? 'Ja' : '';
                }
                else 
                {
                	$data['icd_data'][$kops]['relevant2admission'] = $vops['PatientDiagnosisClinical']['relevant2admission'] == "yes" ? '<span style="display: block; width:100%; text-align: center;"><img title="' . $this->translate("Ja") . '" width="26"  border="0" src="' . RES_FILE_PATH . '/images/ambulance.png" /></span>' : '';
                }
                $data['icd_data'][$kops]['icd_start_date_hidden'] = $vops['PatientDiagnosisClinical']['start_date'] != '0000-00-00 00:00:00' ?  $vops['PatientDiagnosisClinical']['start_date'] : '';
                $data['icd_data'][$kops]['icd_start_date'] = $vops['PatientDiagnosisClinical']['start_date'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($vops['PatientDiagnosisClinical']['start_date'])) : '';
                $data['icd_data'][$kops]['icd_end_date_hidden'] = isset($vops['PatientDiagnosisClinical']['end_date']) && $vops['PatientDiagnosisClinical']['end_date'] != '0000-00-00 00:00:00' ?  $vops['PatientDiagnosisClinical']['end_date'] : '';
                $data['icd_data'][$kops]['icd_end_date'] = isset($vops['PatientDiagnosisClinical']['end_date']) && $vops['PatientDiagnosisClinical']['end_date'] != '0000-00-00 00:00:00' ? date('d.m.Y', strtotime($vops['PatientDiagnosisClinical']['end_date'])) : '';
                $data['icd_data'][$kops]['icd_comment'] = $vops['PatientDiagnosisClinical']['icd_comment'];
                
//                 $input = '<input type="text" name="corder['.$vops["id"].']"  data-row_id="'.$vops["id"].'" value="'.$vops["custom_order"].'"  />';
                $input = '';
                /* ISPC-2831 Dragos 15.03.2021 */
                if (!$noactions) {
					$data['icd_data'][$kops]['actions'] = '<span class="edit_icd" data-entry_id = "' . $vops["id"] . '"><img title="' . $this->translate("edit") . '" width="16" height="16" border="0" src="' . RES_FILE_PATH . '/images/edit.png" /></span><span class="delete_icd" data-entry_id = "' . $vops["id"] . '"><img title="' . $this->translate("delete") . '" width="16" height="16" border="0" src="' . RES_FILE_PATH . '/images/action_delete.png" data-entry_id = "' . $vops["id"] . '" /></span>'.$input;
				}
                // -- //

//                 $data['icd_data'][$kops]['nr'] = '<img title="' . $this->translate("edit") . '" width="16" height="16" border="0" src="' . RES_FILE_PATH . '/images/arrow_updown_icon.png" />';

                
                if( (empty($vops['PatientDiagnosisClinical']['archived'])  ||  $vops['PatientDiagnosisClinical']['archived'] == 'no')
                    && isset($vops['PatientDiagnosisClinical']['end_date']) && $vops['PatientDiagnosisClinical']['end_date'] != '0000-00-00 00:00:00' 
                    ){
                    $data['icd_data_ended'][$end_inc] = $data['icd_data'][$kops];
                    $end_inc++;
                }
                elseif (! empty($vops['PatientDiagnosisClinical']['archived']) && $vops['PatientDiagnosisClinical']['archived'] == 'yes'
                    && isset($vops['PatientDiagnosisClinical']['end_date']) && $vops['PatientDiagnosisClinical']['end_date'] != '0000-00-00 00:00:00'
                    ) {
                    $data['icd_data_archived'][$arc] = $data['icd_data'][$kops];
                    $arc++;
                } else{
                    
                    $data['icd_data_result'][$icd_inc] = $data['icd_data'][$kops];
                    $icd_inc++;
                    
                }
                
                $kops ++;
            }
        }
        
        $data['icd_data'] = array();
        $data['icd_data'] = $data['icd_data_result'];
        
        $sorting  = IcdOpsMreSorting::get_sorting_columns();
        $ioms_sort_arr = IcdOpsMreSorting::getIcdOpsMreSorting($clientid);
        if(empty($ioms_sort_arr)){
            $ioms_sort_arr = Pms_CommonData::get_sort_column_diagnosis_default();
        }
  
        $display_mapping = array(
            'icd_category' =>'3',
            'icd_code' =>'4',
            'icd_description' =>'5',
            'relevant2admission' =>'6',
            'icd_start_date' =>'7',
            'icd_end_date' =>'8',
            'icd_comment' =>'9',
        );
        
        //SET DEFAULTLT
        $order_mapp = array('1'=>'asc','2'=>'desc');
        $data['order_multiple']['main_sort_column'] = '0';
        $data['order_multiple']['main_sort_order'] = 'asc';
        $data['order_multiple']['main_sec_column'] = '0';
        $data['order_multiple']['main_sec_order'] = 'asc';
        
        $data['order_str'] = '[[ '.$data['order_simple']['main_sort_column'].', "'.$data['order_simple']['main_sort_order'].'" ]]';
 
        if(!empty($ioms_sort_arr) && empty($has_custom_order)){
            if(!isset($options['ro']) || $options['ro'] != '1'){
                $sort_arr= $ioms_sort_arr['0'];
                
                $data['order_multiple']['main_sort_column'] = $display_mapping[ $sorting[$sort_arr['main_sort_col']] ];
                $data['order_multiple']['main_sort_order'] = $order_mapp[$sort_arr['sort_order']];
                
                $data['order_multiple']['main_sec_column'] = $display_mapping[ $sorting[$sort_arr['secondary_sort_col']] ];
                $data['order_multiple']['main_sec_order'] = $order_mapp[$sort_arr['sort_order']];;
            }
        }
        
	    if($options['filter']){
// 	        $return =  $data['icd_data'];
	        $return =  $data;
	        return $return;
	    }
// 	    dd($data['icd_data_archived']);

		/* ISPC-2831 Dragos 15.03.2021 */
		if ($noactions) {
			$blockconfig = array(
				'blockname' => "OPS",
				'template' => 'patient_diagnosis_icd_no_actions.phtml',
				'formular_type' => $pdf,
			);
		} else {
			$blockconfig = array(
				'blockname' => "OPS",
				'template' => 'patient_diagnosis_icd.phtml',
				'formular_type' => $pdf,
			);
		}

		// -- //
	    
	    //ISPC-2775 Carmen 06.01.2021
	    if($pdf == 'pdf')
	    {
	    	$blockconfig = array(
	    			'blockname' => "OPS",
	    			'template' => 'patient_diagnosis_icd_pdf.phtml',
	    			'formular_type' => $pdf,
	    	);
	    }
	    //print_r($data); exit;
	    return $this->create_subform_ui($blockconfig, $data );
	    
	}
	
	public function create_subform_ui($blockconfig,  $data){
	    
	    $newview = new Zend_View();
	    
	    foreach ($data as $key=>$value){
	        $newview->$key = $value;
	    }
	    // necessary for Baseassesment Pflege, does nothing with another form blocks
	    $newview->blockconfig = $blockconfig;
	    $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
	    $html = $newview->render($blockconfig['template']);
	    
	    return $html;
	}
	
	/**
	 * ISPC-2654 Ancuta 13.10.2020
	 * @param unknown $ipid
	 * @return void|Doctrine_Collection
	 */
	public function archiv_ended_diagnosis ( $ipid )
	{
	    if(empty($ipid)){
	        return;
	    }
        $update = Doctrine_Query::create()
        ->update('PatientDiagnosisClinical')
        ->set('archived',"?", "yes")
        ->where('ipid ="'.$ipid.'"')
        ->andWhere('end_date != "0000-00-00 00:00:00"');
        $update_res = $update->execute();
        
        return $update_res;
	}
	

}
?>
