<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlan', 'MDAT');

	class PatientDrugPlan extends BasePatientDrugPlan {
		
		public $triggerformid = 12;
		public $triggerformname = "frmpatientdrugplan";
		
		
		/**
		 * This is for new block - ISPC-2176 p6
		 * @var unknown
		 */
		const ISINTUBATED_VERLAUF_SHORTCUT = 'PM';
		
		/**
		 * this is the asssociation with the standard 
		 * $codeToSTitle = $DeKbv_Bmp2->keyFileToArray( 'KBV_BMP2_ZWISCHENUEBERSCHRIFT' );
		 * @var unknown
		 */
		public static $KBV_BMP2_ZWISCHENUEBERSCHRIFT_ASSOC = array(
				
				/*
				 * actual = Medikation
				 * isbedarfs = Bedarfs Medikation
				 * iscrisis = Krisen-/ Notfallmedikation
				 * isivmed = I.v. / s.c. Medikation
				 * isnutrition = Ernährung
				 * isschmerzpumpe = Pumpe
				 * treatment_care = Behandlungspflege
				 * scheduled = Intervall Medis 
				 */
		        "412" => "actual",//ISPC-2551 Ancuta 31.03.2020
				"411" => "isbedarfs", //Bedarfsmedikation
				"iscrisis" => "iscrisis", 
				"isivmed" => "isivmed", 
// 				"415" => "isivmed", // 415 & 416 Intravenöse Anwendung + Anwendung unter die Haut
// 				"416" => "isivmed", // 415 & 416 Intravenöse Anwendung + Anwendung unter die Haut
				"isnutrition" => "isnutrition", 
				"isschmerzpumpe" => "isschmerzpumpe", 
				"treatment_care" => "treatment_care", 
				"scheduled" => "scheduled", 

// 				@todo the above list is NOT done, it needs to be corelated by ZDaniel
// 				"411" => "xxx", //Bedarfsmedikation
// 				"412" => "xxx", //Dauermedikation
// 				"413" => "xxx", //Intramuskuläre Anwendung
// 				"414" => "xxx", //Besondere Anwendung
// 				"415" => "xxx", //Intravenöse Anwendung
// 				"416" => "xxx", //Anwendung unter die Haut
// 				"417" => "xxx", //Fertigspritze
// 				"418" => "xxx", //Selbstmedikation
// 				"419" => "xxx", //Allergiehinweise
// 				"421" => "xxx", //Wichtige Hinweise
// 				"422" => "xxx", //Wichtige Angaben
// 				"423" => "xxx", //zu besonderen Zeiten anzuwendende Medikamente
// 				"424" => "xxx", //zeitlich befristet anzuwendende Medikamente				
				
		);
				
		/**
		 * this are the shortcuts to be added into the patient course
		 */
		private static $_course_type_assoc = array(
				"isbedarfs" => "N",
				"isivmed" => "I",
				"isschmerzpumpe" => "Q",
				"treatment_care" => "BP",
				"scheduled" => "MI",
				"iscrisis" => "KM",
				"isintubated" => self::ISINTUBATED_VERLAUF_SHORTCUT,
				"default" => "M"
		);
		
		/**
		 * for usage with PatientCourse course_type
		 * first time used on Application_Form_Datamatrix
		 * 
		 * @param string $group
		 * @return string
		 */
		public static function getGroupCourseType ( $group = "default") {
			
			return isset(self::$_course_type_assoc [$group]) ? self::$_course_type_assoc [$group]: self::$_course_type_assoc ['default'];
			
		}

		

		public function getPatientDrugPlan($pid, $load_master = false)
		{//Changes for ISPC-1848 F
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$modules = new Modules();
			
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			
			    // Get declined data
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid);
// 			    if(empty($declined)){
// 			        $declined[] = "XXXXXXXXXXXXXXXXXXXXX";
// 			    }
			
			    $not_approved_ids	=  array();
			    $newly_not_approved	=  array();
			    
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid);
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			        
			        if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
			            $newly_not_approved[] = $not_approved['drugplan_id'];;
			        }
			        
			    }
// 			    if(empty($not_approved_ids)){
// 			        $not_approved_ids[] = "XXXXXXXX";
// 			    }
			    
// 			    if(empty($newly_not_approved)){
// 			        $newly_not_approved[] = "XXXXXXXX";
// 			    }
			    
			    
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = '0'");
    			if($acknowledge_func == "1")//Medication acknowledge
    			{
    				if( ! empty($declined) && is_array($declined)) {
    			    	$drugs->andWhereNotIn('id', $declined); // remove declined
    				}
    				if( ! empty($newly_not_approved) && is_array($newly_not_approved)) {
    			   		$drugs->andWhereNotIn('id', $newly_not_approved); // remove newly added - not approved
    				}
    			}
                $drugs->orderBy("id ASC");
			$drugsarray = $drugs->fetchArray();
			if($drugsarray)
			{
				if($load_master && ! empty($drugsarray))
				{
					//extract the master_medications_array
					$medi_master_ids = array();
					$medi_mastertreament_ids = array();
					$medi_masternutrition_ids = array();
// 					$medi_master_ids[] = '9999999999';
					foreach($drugsarray as $k_drug => $v_drug)
					{
						if($v_drug['treatment_care'] == "1")
						{
							$medi_mastertreament_ids[] = $v_drug['medication_master_id'];
						} 
						else if($v_drug['isnutrition'] == "1")
						{
							$medi_masternutrition_ids[] = $v_drug['medication_master_id'];
						} 
						else
						{
							$medi_master_ids[] = $v_drug['medication_master_id'];
						}
					}

// 					$medi_master_ids = array_column($drugsarray, 'medication_master_id');
					
					//sorting
// 					$medi_master_ids = array_values(array_unique($medi_master_ids));

					//get the data from master medications array
					$med = new Medication();
// 					$medarr = $med->master_medications_get($medi_master_ids, false); // this fn gets only the name
					$medarr = $med->getMedicationById($medi_master_ids, true); // so changed into this to fetch full row

					$med_treatment = new MedicationTreatmentCare();
					$medarr_treatment = $med_treatment->getMedicationTreatmentCareById($medi_mastertreament_ids); // so changed into this to fetch full row
					$treatment_med = array();
					if( ! empty($medarr_treatment)){
						foreach($medarr_treatment as $k=>$mt){
							$treatment_med[$mt['id']]  = $mt;
							$treatment_med[$mt['id']]['pzn']  = "00000000";
							$treatment_med[$mt['id']]['source']  = 'custom';
							$treatment_med[$mt['id']]['dbf_id']  = $mt['id'];
						}
					}
					
					$med_nutrition = new Nutrition();
					$medarr_nutrition = $med_nutrition->getMedicationNutritionById($medi_masternutrition_ids); // so changed into this to fetch full row
					
					$nutrition_med = array();
					if( ! empty($medarr_nutrition)){
						foreach($medarr_nutrition as $k=>$mn){
							$nutrition_med[$mn['id']]  = $mn;
						}
					}
					
					
// 					foreach($drugsarray as $k_drug_plan => $v_drug_plan)
// 					{

// 						$drugsarray[$k_drug_plan]['medication'] = $medarr[$v_drug_plan['medication_master_id']];
// 					}

					foreach($drugsarray as &$v_drug_plan)
					{
						if($v_drug_plan['treatment_care'] == "1")
						{
							$v_drug_plan['medication'] = $treatment_med[$v_drug_plan['medication_master_id']]['name'];
							$v_drug_plan['MedicationMaster'] = $treatment_med[$v_drug_plan['medication_master_id']]; // full row
						}
						elseif($v_drug_plan['isnutrition'] == "1")
						{
							$v_drug_plan['medication'] = $nutrition_med[$v_drug_plan['medication_master_id']]['name'];
							$v_drug_plan['MedicationMaster'] = $nutrition_med[$v_drug_plan['medication_master_id']]; // full row
						}
						else
						{
							$v_drug_plan['medication'] = $medarr[$v_drug_plan['medication_master_id']]['name'];
							$v_drug_plan['MedicationMaster'] = $medarr[$v_drug_plan['medication_master_id']]; // full row
						}
					}
				}

// 				dd($drugsarray);
				return $drugsarray;
			}
		}

		public function getMedicationPlan($pid, $allowivmedis = false, $allowschmerzpumpe = false, $remove_non_approved = false)
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $ipid = Pms_CommonData::getIpid($pid);

		    $type_condition = false;
		    if($allowivmedis)
		    {
		        $q = "isivmed = 1";
		        $type_condition = "isivmed = 1"; 
		    }
		    else
		    {
		        $q = "isivmed = 0";
		    }
		    if($allowschmerzpumpe)
		    {
		        $s = "isschmerzpumpe = 1";
		        $type_condition = "isschmerzpumpe = 1";
		    }
		    else
		    {
		        $s = "isschmerzpumpe = 0";
		    }
		    
		    $modules = new Modules();
		    if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
		    {
		        $acknowledge_func = '1';
		        
		        // Get declined data
		        $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,$type_condition);
		        if(empty($declined)){
		            $declined[] = "XXXXXXXX";
		        }
		        
		        //get non approved data 
		        $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,$type_condition);
		        foreach($non_approved['change'] as $drugplan_id =>$not_approved){
		            $not_approved_ids[] = $not_approved['drugplan_id'];
		            

		            if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
		                $newly_not_approved[] = $not_approved['drugplan_id'];;
		            }
		            
		        }
		        if(empty($not_approved_ids)){
		            $not_approved_ids[] = "XXXXXXXX";
		        }		
		        if(empty($newly_not_approved)){
		            $newly_not_approved[] = "XXXXXXXX";
		        }		
		    } 
		    else
		    {
		        $acknowledge_func = '0';
		    }

			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = 0")
				->andWhere("isbedarfs = 0")
				->andWhere("iscrisis = 0")
				->andWhere("treatment_care = 0")
				->andWhere("isnutrition = 0")
				->andWhere('scheduled = "0"')
				->andWhere('ispumpe = "0"')//ISPC-2833
				->andWhere($q)
				->andWhere($s);
    			if($acknowledge_func == "1")//Medication acknowledge
    			{
    			    $drugs->andWhereNotIn('id',$declined); // remove declined
    			    
    			    if($remove_non_approved){
        			    $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
    			    }
    			    
    			}
                $drugs->orderBy("id ASC");

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				
				if($acknowledge_func == "1")//Medication acknowledge
				{
    				foreach($drugsarray as $dkey => $d_data)
    				{
    				    if($non_approved['change'][$d_data['id']]) 
    				    {
        				    $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
        				    $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
        				    $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
    				    } 
    				}
				}
				return $drugsarray;
			}
		}

		public function getMedicationPlanAll($pid, $remove_non_approved = false, $allow_deleted = false,$db_drugplan_id = false)
		{
		    //ISPC-2833 Ancuta 26.02.2021 - added new type - ispumpe 
		    // //ISPC-2829 Ancuta 18.03.2021- added $drugplan_id - so it can retorn only for this id only 
		    
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $ipid = Pms_CommonData::getIpid($pid);

		    $type_condition = false;
		    $modules = new Modules();
		    if($modules->checkModulePrivileges("111", $logininfo->clientid) //Medication acknowledge
		        || $modules->checkModulePrivileges(155, $logininfo->clientid)//Offline App - medis are moderated
		        )
		    {
		        $acknowledge_func = '1';
		        
		        // Get declined data
		        $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt_all($ipid);
// 		        if(empty($declined)){
// 		            $declined[] = "XXXXXXXX";
// 		        }
		        
		        //get non approved data 
// 		        $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,$type_condition);
		        $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt_all($ipid);
		        $newly_not_approved = array();
		        foreach($non_approved['change'] as $drugplan_id =>$not_approved){
		            $not_approved_ids[] = $not_approved['drugplan_id'];
		            

		            if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
		                $newly_not_approved[] = $not_approved['drugplan_id'];;
		            }
		            
		        }
// 		        if(empty($not_approved_ids)){
// 		            $not_approved_ids[] = "XXXXXXXX";
// 		        }		
// 		        if(empty($newly_not_approved)){
// 		            $newly_not_approved[] = "XXXXXXXX";
// 		        }		
		    } 
		    else
		    {
		        $acknowledge_func = '0';
		    }

			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where("ipid = ?", $ipid);
			
				if($db_drugplan_id){
				    $drugs->andWhere("id =?",$db_drugplan_id);
				}
				
    			if($allow_deleted === false)//Medication acknowledge
    			{
					$drugs->andWhere("isdelete = 0");
    			}
    			if($acknowledge_func == "1")//Medication acknowledge
    			{
    			    if ( ! empty($declined)) {
    			        $drugs->andWhereNotIn('id', $declined); // remove declined
    			    }
    			    
    			    if($remove_non_approved && ! empty($newly_not_approved)){
        			    $drugs->andWhereNotIn('id', $newly_not_approved); // remove newly added - not approved
    			    }
    			    
    			}
                $drugs->orderBy("id ASC");

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				
				$medi_master_ids            = array();
				$treatmen_care_med_ids      = array();
				$medication_tr_array        = array();
				$nutrition_med_ids          = array();
				$medication_nutrition_array = array();
				
				//extract the master_medications_array
// 				$medi_master_ids[] = '9999999999';
				foreach($drugsarray as $k_drug => $v_drug)
				{
				    if ($v_drug['treatment_care'] == "1") {
				        $treatmen_care_med_ids[] = $v_drug['medication_master_id'];
				    } elseif ($v_drug['isnutrition'] == "1") {
				        $nutrition_med_ids[] = $v_drug['medication_master_id'];
				    } else{
				        $medi_master_ids[] = $v_drug['medication_master_id'];
				    }
				}
				
				
// 				if(empty($treatmen_care_med_ids))
// 				{
// 				    $treatmen_care_med_ids[] = "99999999999";
// 				}
                if ( ! empty($treatmen_care_med_ids)) {
    				$treatmen_care_med_ids = array_values(array_unique($treatmen_care_med_ids));
    				$medtr = new MedicationTreatmentCare();
    				$medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);
    				 
    				foreach($medarr_tr as $k_medarr_tr => $v_medarr_tr)
    				{
    				    $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
    				}
                }
				 
				// get nutrition  details
// 				if(empty($nutrition_med_ids))
// 				{
// 				    $nutrition_med_ids[] = "99999999999";
// 				}
				if ( ! empty($nutrition_med_ids)) {
    				$nutrition_med_ids = array_values(array_unique($nutrition_med_ids));
    				
    				$mednutrition = new Nutrition();
    				$medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
    				 
    				foreach($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition)
    				{
    				    $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
    				}
				}
				
				//sorting
				$medi_master_ids = array_values(array_unique($medi_master_ids));
				
				//get the data from master medications array
				$med = new Medication();
                //ISPC-2912,Elena,25.05.2021
				$medarr = $med->master_medications_get($medi_master_ids, false, true);
				$btmDetails = [];
				foreach($medarr['Medication'] as $medi_details){
				    $btmDetails[$medi_details['id']] = $medi_details['is_btm'];
                }

				
				if($acknowledge_func == "1")//Medication acknowledge || Offline App
				{
				   
    				foreach($drugsarray as $dkey => $d_data)
    				{
    				    if($non_approved['change'][$d_data['id']]) 
    				    { 
        				    $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
        				    $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
        				    $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
    				    } 
    				    
    				    //ispc-1999
    				    if ( ! empty($non_approved['change_offline'][$d_data['id']])) {
        				    $drugsarray[$dkey]['approved'] = $non_approved['change_offline'][$d_data['id']]['approved'];
        				    $drugsarray[$dkey]['values'] = $non_approved['change_offline'][$d_data['id']]['change_values'];
        				    $drugsarray[$dkey]['on_hold_changes_offline'][$d_data['id']] = $non_approved['change_offline'][$d_data['id']];
    				    } 
    				}
				}
				
				foreach($drugsarray as $k_drug_plan => $v_drug_plan)
				{
                    //ISPC-2912,Elena,25.05.2021
                    if(isset($btmDetails[$v_drug_plan['medication_master_id']])){
                        $drugsarray[$k_drug_plan]['is_btm'] = $btmDetails[$v_drug_plan['medication_master_id']];
                    }
                    else{
                        $drugsarray[$k_drug_plan]['is_btm'] = 0;
                    }

				    if(empty($v_drug_plan['medication'])){
    				    
				        if ($v_drug_plan['treatment_care'] == "1")
				        {
				            $drugsarray[$k_drug_plan]['medication'] = $medication_tr_array[$v_drug_plan['medication_master_id']]['name'];
				             
				        }
				        elseif ($v_drug_plan['isnutrition'] == "1")
				        {
				             
				            $drugsarray[$k_drug_plan]['medication'] = $medication_nutrition_array[$v_drug_plan['medication_master_id']]['name'];
				        }
				        else
				        {
				            $drugsarray[$k_drug_plan]['medication'] = $medarr[$v_drug_plan['medication_master_id']];
				        }
    				    
				    } else{
    				    $drugsarray[$k_drug_plan]['medication'] = $v_drug_plan['medication'];
				    }
				
				}
				
				return $drugsarray;
			}
		}

		public function getbedarfMedication($pid,$remove_non_approved = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			
			$modules = new Modules();
			
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			    
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,"isbedarfs = 1");
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			    
			    
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,"isbedarfs = 1");
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			        
			        if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
			            $newly_not_approved[] = $not_approved['drugplan_id'];;
			        }
			         
			    }
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			    if(empty($newly_not_approved)){
			        $newly_not_approved[] = "XXXXXXXX";
			    }
			    
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			$drugs = Doctrine_Query::create()
				->select("*,'another' as type")
				->from('PatientDrugPlan')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = 0")
				->andWhere("isbedarfs = 1");
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    $drugs->andWhereNotIn('id',$declined);
				    if($remove_non_approved)
				    {
    				    $drugs->andWhereNotIn('id',$newly_not_approved);
				    }
				}
				$drugs->orderBy("id ASC");
			$drugs->getSqlQuery();

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				
			    if($acknowledge_func == "1")//Medication acknowledge
				{
    				foreach($drugsarray as $dkey => $d_data)
    				{
    				    if($non_approved['change'][$d_data['id']])
    				    {
        				    $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
        				    $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
        				    $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
    				    } 
    				}
    	 
				}
				return $drugsarray;
			}
		}

		public function getCrisisMedication($pid,$remove_non_approved = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);
		
				
			$modules = new Modules();
				
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
				$acknowledge_func = '1';
				 
				$declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,"iscrisis = 1");
				if(empty($declined)){
					$declined[] = "XXXXXXXX";
				}
				 
				 
				//get non approved data
				$non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,"iscrisis = 1");
				foreach($non_approved['change'] as $drugplan_id =>$not_approved){
					$not_approved_ids[] = $not_approved['drugplan_id'];
					 
					if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
						$newly_not_approved[] = $not_approved['drugplan_id'];;
					}
		
				}
				if(empty($not_approved_ids)){
					$not_approved_ids[] = "XXXXXXXX";
				}
				if(empty($newly_not_approved)){
					$newly_not_approved[] = "XXXXXXXX";
				}
				 
			}
			else
			{
				$acknowledge_func = '0';
			}
				
			$drugs = Doctrine_Query::create()
			->select("*,'another' as type")
			->from('PatientDrugPlan')
			->where("ipid = '" . $ipid . "'")
			->andWhere("isdelete = 0")
			->andWhere("iscrisis = 1");
			if($acknowledge_func == "1")//Medication acknowledge
			{
				$drugs->andWhereNotIn('id',$declined);
				if($remove_non_approved)
				{
					$drugs->andWhereNotIn('id',$newly_not_approved);
				}
			}
			$drugs->orderBy("id ASC");
			$drugs->getSqlQuery();
		
			$dr = $drugs->execute();
		
			if($dr)
			{
				$drugsarray = $dr->toArray();
		
				if($acknowledge_func == "1")//Medication acknowledge
				{
					foreach($drugsarray as $dkey => $d_data)
					{
						if($non_approved['change'][$d_data['id']])
						{
							$drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
							$drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
							$drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
						}
					}
		
				}
				return $drugsarray;
			}
		}
		
		public function getDeletedMedication($pid,$include_treatment_care = false,$include_nutrition = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			
			$modules = new Modules();
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			    
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,false,false,true);
// 			    if(empty($declined)){
// 			        $declined[] = "XXXXXXXX";
// 			    }
			    

			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,false,false,true);
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			    }
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			$drugs = Doctrine_Query::create();
			$drugs->select('*');
			$drugs->from('PatientDrugPlan');
			$drugs->where("ipid = '" . $ipid . "'");
			$drugs->andWhere("isdelete = 1");
			if(!$include_treatment_care){
				$drugs->andWhere("treatment_care = 0");
			}
			if(!$include_nutrition){
				$drugs->andWhere("isnutrition = 0");
			}
			
			if($acknowledge_func == "1" && !empty($declined))//Medication acknowledge
			{
    			$drugs->andWhereNotIn('id',$declined);
			}
			
			$drugs->orderBy("id ASC");
			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				

				if($acknowledge_func == "1")//Medication acknowledge
				{
				    foreach($drugsarray as $dkey => $d_data)
				    {
				        if($non_approved['change'][$d_data['id']])
				        {
				            $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
				            $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
				            $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
				        }
				    }
				}
				return $drugsarray;
			}
		}

		public function getivMedication($pid,$remove_non_approve = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$modules = new Modules();
				
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,"isivmed = 1");
			    
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			    

			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,"isivmed = 1");
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			        
			        if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
			            $newly_not_approved[] = $not_approved['drugplan_id'];;
			        }
			        
			    }
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			    if(empty($newly_not_approved)){
			        $newly_not_approved[] = "XXXXXXXX";
			    }
			    
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			$drugs = Doctrine_Query::create()
				->select("*,'another' as type")
				->from('PatientDrugPlan')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = 0")
				->andWhere("isivmed = 1");
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    $drugs->andWhereNotIn('id',$declined);
				    
				    if($remove_non_approve)
				    {
					    $drugs->andWhereNotIn('id',$newly_not_approved);
				    }
				}
				$drugs->orderBy("id ASC");
			$drugs->getSqlQuery();

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    foreach($drugsarray as $dkey => $d_data)
    				{
    				    if($non_approved['change'][$d_data['id']])
    				    {
        				    $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
        				    $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
        				    $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
    				    } 
    				}
 
				}
				
				return $drugsarray;
			}
		}

		public function getSchmerzpumpeMedication($pid, $cid = 0,$ipid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if($ipid === false){
				$ipid = Pms_CommonData::getIpid($pid);
			}

			$modules = new Modules();
			
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';

			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,"isschmerzpumpe = 1");
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			    
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,"isschmerzpumpe = 1");
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			    }
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			    
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			
			$drugs = Doctrine_Query::create()
				->select("*,'another' as type")
				->from('PatientDrugPlan d')
				->where("d.ipid = '" . $ipid . "'")
				->andWhere("d.isdelete = 0")
				->andWhere("d.treatment_care = 0")
				->andWhere("d.isnutrition = 0")
				->andWhere("d.scheduled= 0")
				->andWhere("d.isivmed = 0")
				->andWhere("d.isschmerzpumpe = 1")
				->leftJoin('d.PatientDrugPlanCocktails c')
				->andwhere('d.cocktailid = c.id')
				->andwhere('c.isdelete = 0');
			
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    $drugs->andWhereNotIn('d.id',$declined);
				}
				
				$drugs->orderBy("c.id, d.id ASC");
			if($cid != 0)
			{
				$drugs->andWhere("cocktailid = '" . $cid . "'");
			}

			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();

				if($acknowledge_func == "1")//Medication acknowledge
				{
				    foreach($drugsarray as $dkey => $d_data)
				    {
				        if($non_approved['change'][$d_data['id']])
				        {
				            $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
				            $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
				            $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
				        }
				    }
				}
				
				return $drugsarray;
			}
		}

		public function getSchmerzpumpeMedicationall($pid,$remove_non_approved = false )
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);
			

			$modules = new Modules();
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,"isschmerzpumpe = 1");
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			     
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,"isschmerzpumpe = 1");
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			    	
			    	if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
			            $newly_not_approved[] = $not_approved['drugplan_id'];; 
			        }
			    }
			    
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			    if(empty($newly_not_approved)){
			        $newly_not_approved[] = "XXXXXXXX";
			    }
			     
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			$drugs = Doctrine_Query::create()
				->select('*,"another" as type, c.description  as comments, c.bolus as bolus, c.flussrate as flussrate , c.sperrzeit as sperrzeit  ')
				->from('PatientDrugPlan s')
				->leftJoin('s.PatientDrugPlanCocktails c')
				->where("s.ipid = '" . $ipid . "'")
				->andWhere("s.cocktailid = c.id")
				->andWhere("s.isdelete = 0")
				->andWhere("s.isivmed = 0")
				->andWhere("s.treatment_care = 0")
				->andWhere("s.isnutrition = 0")
				->andWhere("s.scheduled = 0")
				->andWhere("s.isschmerzpumpe = 1");
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    $drugs->andWhereNotIn('s.id',$declined);
				    
				    if($remove_non_approved)
				    {
    				    $drugs->andWhereNotIn('s.id',$newly_not_approved); // remove non approved newly added meds
				    }
				}
				$drugs->orderBy("s.cocktailid, s.id ASC");
			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $drugs->fetchArray();
				
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    foreach($drugsarray as $dkey => $d_data)
				    {
				        if($non_approved['change'][$d_data['id']])
				        {
				            $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
				            $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
				            $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
				        }
				    }
				}
				
				return $drugsarray;
			}
		}

		public function getPatientDrugPlanNoIV($pid,$remove_non_approved = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$modules = new Modules();
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			    	
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,false,false,true);
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,false,false,true);

			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			        
			        if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
			            $newly_not_approved[] = $not_approved['drugplan_id'];; 
			        }
			    }
			    
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			    if(empty($newly_not_approved)){
			        $newly_not_approved[] = "XXXXXXXX";
			    }
			
			}
			else
			{
			    $acknowledge_func = '0';
			}
				
			
			
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where("ipid = '" . $ipid . "'")
				->andWhere("isdelete = '0' and isivmed ='0' and isschmerzpumpe = 0 and treatment_care = 0 and isnutrition = 0 and scheduled = 0");
			
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    $drugs->andWhereNotIn('id',$declined);
				    if($remove_non_approved){
					    $drugs->andWhereNotIn('id',$newly_not_approved);
				    }
				}
				
			$drugs->orderBy("id ASC");
			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}

		public function getMedicationPlanCourse($pid, $pmid = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);

			$modules = new Modules();
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,false,false,true);
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			    	
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,false,false,true);
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			    }
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			    	
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			
			
			if($pmid)
			{
				$q = 'AND id="' . $pmid . '"';
			}
			else
			{
				$q = '';
			}

			//get patient medis
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where("ipid = '" . $ipid . "' " . $q . "")
				->andWhere("isdelete = 0")
				->andWhere('isschmerzpumpe ="0"')
				->andWhere('treatment_care ="0"')
				->andWhere('isnutrition ="0"')
				->andWhere('scheduled = "0" ');
				if($acknowledge_func == "1")//Medication acknowledge
				{
    				$drugs->andWhereNotIn('id',$declined);
				}
		      $drugs->orderBy("id ASC");
			$drug_plans = $drugs->fetchArray();

			//extract the master_medications_array
			$medi_master_ids[] = '99999999';
			foreach($drug_plans as $k_drug => $v_drug)
			{
				$medi_master_ids[] = $v_drug['medication_master_id'];
			}

			//sorting
			asort($medi_master_ids, SORT_NUMERIC);
			$medi_master_ids = array_values(array_unique($medi_master_ids));

			//get the data from master medications array
			$med = new Medication();
			$medarr = $med->master_medications_get($medi_master_ids, false);

			//map the master medications data to data from drugplan
			foreach($drug_plans as $k_drugplan => $v_drugplan)
			{
				$patient_drugs[$k_drugplan] = $v_drugplan;
				$patient_drugs[$k_drugplan]['medi_name'] = $medarr[$v_drugplan['medication_master_id']];

				if($v_drugplan['medication_change'] != "0000-00-00 00:00:00")
				{
					$patient_drugs[$k_drugplan]['medi_change'] = date('d.m.Y', strtotime($v_drugplan['medication_change']));
					$patient_drugs[$k_drugplan]['medi_replace'] = "none";
				}
				else if($v_drugplan['medication_change'] == "0000-00-00 00:00:00" && $v_drugplan['change_date'] != "0000-00-00 00:00:00")
				{
					$patient_drugs[$k_drugplan]['medi_change'] = date('d.m.Y', strtotime($v_drugplan['change_date']));
					$patient_drugs[$k_drugplan]['medi_replace'] = "change";
				}
				else
				{
					$patient_drugs[$k_drugplan]['medi_change'] = date('d.m.Y', strtotime($v_drugplan['create_date']));
					$patient_drugs[$k_drugplan]['medi_replace'] = "create";
				}
			}

			return $patient_drugs;
		}

		public function getPatientAllDrugs($ipid, $exclude_deleted = false)
		{			
		    $logininfo = new Zend_Session_Namespace('Login_Info');

			$modules = new Modules();
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,false,false,true);
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			    	
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,false,false,true);
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			    }
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			    	
			}
			else
			{
			    $acknowledge_func = '0';
			}
		    
		    
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where("ipid = '" . $ipid . "'");
			
				if($acknowledge_func == "1")//Medication acknowledge
				{
    				$drugs->andWhereNotIn('id',$declined);
				}
				
				if($exclude_deleted)
				{
    				$drugs->andWhere('isdelete = 0');
				}
				
		    $drugs->orderBy("id ASC");

			$drugsarray = $drugs->fetchArray();

			if($drugsarray)
			{
				return $drugsarray;
			}
		}

		// Maria:: Migration ISPC to CISPC 08.08.2020
		public function clone_records($ipid, $target_ipid, $target_client, $source_client = false)
		{
			$cocktails = new PatientDrugPlanCocktails();
			$save_cocktails = $cocktails->clone_record($ipid, $target_ipid, $target_client);

			$medis = $this->getPatientAllDrugs($ipid,true);

			$medication_extra = array();
			if($source_client){
    			$medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$source_client);

    			//ISPC-2614 Ancuta 20.07.2020
    			$medication_dosage  = PatientDrugPlanDosage::get_patient_drugplan_dosage($ipid );
    			//ISPC-2829 Ancuta 06.04.2021
    			$m_int_obj =new PatientDrugPlanDosageIntervals();
    			$medication_dosage_intervals  = $m_int_obj->get_patient_dosage_intervals($ipid,$source_client);
    			//--
    			$all_lists_connections = ConnectionMasterTable::_find_all_lists_connections();
    			$medication_connected_tables = array('MedicationUnit','MedicationType','MedicationDosageform','MedicationIndications');
    			$allowed_connections = array();
    			$allowed_connections_data = array();
    			$clone_value = array();
    			foreach($medication_connected_tables as $list_model){
    			    if( ( !empty($all_lists_connections[$list_model]['parent2child'][$source_client]) && in_array($target_client,$all_lists_connections[$list_model]['parent2child'][$source_client]) )
        			    ||  ( !empty($all_lists_connections[$list_model]['child2parent'][$target_client]) && $all_lists_connections[$list_model]['child2parent'][$target_client] == $source_client)
        			    ||  ( !empty($all_lists_connections[$list_model]['child2parent'][$source_client]) && $all_lists_connections[$list_model]['child2parent'][$source_client] == $target_client)
        			    || ( in_array($target_client,$all_lists_connections[$list_model]['children']) && in_array($source_client,$all_lists_connections[$list_model]['children'])  )
        			    ){
        			    
        			        if(in_array($target_client,$all_lists_connections[$list_model]['parent2child'][$source_client])){
        			            $allowed_connections_data[$list_model]['parent'] =  $source_client;
        			            $allowed_connections_data[$list_model]['child'] =  $target_client;
        			            $allowed_connections_data[$list_model]['connection_id'] =  $all_lists_connections[$list_model]['parent2connection'][$source_client];
        			        } else if(in_array($source_client,$all_lists_connections[$list_model]['parent2child'][$target_client])){
        			            $allowed_connections_data[$list_model]['parent'] =  $target_client;
        			            $allowed_connections_data[$list_model]['child'] =  $source_client;
        			            $allowed_connections_data[$list_model]['connection_id'] =  $all_lists_connections[$list_model]['parent2connection'][$target_client];
        			        }
        			        
        			        $allowed_connections[$list_model] = true;
        			        
        			} else{
        			    $allowed_connections[$list_model] = false;
        			}
        			
        			// get coresponting ids  
        			
        			if( $source_client == $allowed_connections_data[$list_model]['parent'] ){
        			    
        			    $query = Doctrine_Query::create()
        			    ->select('*')
        			    ->from($list_model)
        			    ->where('clientid = ? ', $target_client  )
        			    ->andWhere('connection_id = ?', $allowed_connections_data[$list_model]['connection_id'])
        			    ->andWhere('isdelete = 0')
        			    ->andWhere('extra = 0');
        			    $q_res = $query->fetchArray();
        			    
        			    foreach($q_res as $k=>$values){
        			        $clone_value[$list_model][$values['master_id']] = $values['id'];
        			    }
        			    
        			    // search in list - where master id =  curent id and get   id
        			} else if( $target_client == $allowed_connections_data[$list_model]['parent']  ){
        			    // search in list - where master id =  curent id and get   id
        			    $query = Doctrine_Query::create()
        			    ->select('*')
        			    ->from($list_model)
        			    ->where('clientid = ? ', $source_client  )
        			    ->andWhere('connection_id = ?', $allowed_connections_data[$list_model]['connection_id'])
        			    ->andWhere('isdelete = 0')
        			    ->andWhere('extra = 0');
        			    $q_res = $query->fetchArray();
        			    
        			    foreach($q_res as $k=>$values){
        			        $clone_value[$list_model][$values['id'] ] = $values['master_id'];
        			    }
        			}
    			}
    			//-- 
    			
			}
	 
			if($medis)
			{
				$master_medi = new Medication();
				$nutrition_master = new Nutrition();
				$treatm_master = new MedicationTreatmentCare();
				
				$cloned_patient_medis_smp = array();
				foreach($medis as $k_medi => $v_medi)
				{
				    if($v_medi['treatment_care'] == "1"){
    					$clone_master_medi = $treatm_master->clone_record($v_medi['medication_master_id'], $target_client);
				    }
				    elseif($v_medi['isnutrition'] == "1"){
    					$clone_master_medi = $nutrition_master->clone_record($v_medi['medication_master_id'], $target_client);
				    } else{
    					$clone_master_medi = $master_medi->clone_record($v_medi['medication_master_id'], $target_client);
				    }

					if($clone_master_medi)
					{
					    
					    $_POST['skip_trigger'] = 1;
						$ins_pmedi = new PatientDrugPlan();
						//ISPC-2614 Ancuta 20.07.2020 :: deactivate listner for clone
						$pc_listener = $ins_pmedi->getListener()->get('IntenseMedicationConnectionListener');
						$pc_listener->setOption('disabled', true);
// 						//--
						$ins_pmedi->ipid = $target_ipid;
						$ins_pmedi->medication_master_id = $clone_master_medi;
						$ins_pmedi->medication = $v_medi['medication'];
						$ins_pmedi->dosage = $v_medi['dosage'];
						$ins_pmedi->dosage_interval = $v_medi['dosage_interval'];
						$ins_pmedi->pattern1 = $v_medi['pattern1'];
						$ins_pmedi->pattern2 = $v_medi['pattern2'];
						$ins_pmedi->pattern3 = $v_medi['pattern3'];
						$ins_pmedi->amount = $v_medi['amount'];
						$ins_pmedi->dosage_unit = $v_medi['dosage_unit'];
						$ins_pmedi->dosage_method = $v_medi['dosage_method'];
						$ins_pmedi->active_date = $v_medi['active_date'];
						$ins_pmedi->inactive_date = $v_medi['inactive_date'];
						$ins_pmedi->comments = $v_medi['comments'];
						
						$ins_pmedi->isbedarfs = $v_medi['isbedarfs'];
						$ins_pmedi->iscrisis = $v_medi['iscrisis'];
						$ins_pmedi->isivmed = $v_medi['isivmed'];
						$ins_pmedi->isschmerzpumpe = $v_medi['isschmerzpumpe'];
						$ins_pmedi->treatment_care = $v_medi['treatment_care'];
						$ins_pmedi->isnutrition = $v_medi['isnutrition'];
						$ins_pmedi->isintubated = $v_medi['isintubated'];
						$ins_pmedi->scheduled = $v_medi['scheduled'];

						$ins_pmedi->administration_date = $v_medi['administration_date'];

						if($v_medi['cocktailid'])
						{
							$ins_pmedi->cocktailid = $save_cocktails[$v_medi['cocktailid']];
						}
						$ins_pmedi->source_drugplan_id = $v_medi['id'];
						$ins_pmedi->source_ipid = $ipid;
						$ins_pmedi->verordnetvon = $v_medi['verordnetvon'];
						$ins_pmedi->edit_type = $v_medi['edit_type'];
						$ins_pmedi->medication_change = $v_medi['medication_change'];
						$ins_pmedi->isdelete = $v_medi['isdelete'];
						//ispc-2291
						$ins_pmedi->dosage_product = $v_medi['dosage_product'];
						$ins_pmedi->days_interval_technical = $v_medi['days_interval_technical'];

						    // TODO-2407 Ancuta 11.07.2019						
    						$ins_pmedi->has_interval = $v_medi['has_interval'];
    						$ins_pmedi->days_interval = $v_medi['days_interval'];
    						$ins_pmedi->administration_date = $v_medi['administration_date'];
						
						$ins_pmedi->save();
						
						//ISPC-2614 Ancuta 20.07.2020	:: activate lister after clone
						$pc_listener->setOption('disabled', false);
						//--
						
						// TODO-2407 Ancuta 11.07.2019
						$drugplan_id =  $ins_pmedi->id;
						
						if($drugplan_id){
						    $ins_pmedi_extra = new PatientDrugPlanExtra();
						    $ins_pmedi_extra->ipid = $target_ipid;
						    $ins_pmedi_extra->drugplan_id = $drugplan_id;
						    $ins_pmedi_extra->drug = $medication_extra[$v_medi['id']]['drug'];
						    $ins_pmedi_extra->concentration = $medication_extra[$v_medi['id']]['concentration'];
						    $ins_pmedi_extra->kcal = $medication_extra[$v_medi['id']]['kcal'];
						    $ins_pmedi_extra->volume = $medication_extra[$v_medi['id']]['volume'];
						    //ISPC-2614 Ancuta 20.07.2020
						    // add unit
						    if($allowed_connections['MedicationUnit']){
						        $ins_pmedi_extra->unit = $clone_value['MedicationUnit'][ $medication_extra[$v_medi['id']]['unit_id']  ];
						        $cccc[$drugplan_id]['unit'] = $clone_value['MedicationUnit'][ $medication_extra[$v_medi['id']]['unit_id']  ];
						    }
						    // add type
						    if($allowed_connections['MedicationType']){
						        $ins_pmedi_extra->type = $clone_value['MedicationType'][$medication_extra[$v_medi['id']]['type_id']];
						        $cccc[$drugplan_id]['type'] = $clone_value['MedicationType'][$medication_extra[$v_medi['id']]['type_id']];
						    }
						    // add indication
						    if($allowed_connections['MedicationIndications']){
						        $ins_pmedi_extra->indication = $clone_value['MedicationIndications'][ $medication_extra[$v_medi['id']]['indication_id'] ];
						        $cccc[$drugplan_id]['indication'] = $clone_value['MedicationIndications'][ $medication_extra[$v_medi['id']]['indication_id'] ];
						    }
						    // add dosage_form
						    if($allowed_connections['MedicationDosageform']){
						        $ins_pmedi_extra->dosage_form = $clone_value['MedicationDosageform'][ $medication_extra[$v_medi['id']]['dosage_form_id'] ];
						        $cccc[$drugplan_id]['dosage_form'] = $clone_value['MedicationDosageform'][ $medication_extra[$v_medi['id']]['dosage_form_id'] ];
						    }
						    // add importance
						    $ins_pmedi_extra->importance = $medication_extra[$v_medi['id']]['importance'];
						    // add escalation
						    $ins_pmedi_extra->escalation = $medication_extra[$v_medi['id']]['escalation_id'];
						    // --
						    
						    
						    $ins_pmedi_extra->save();
						    
						    //ISPC-2614 Ancuta 20.07.2020
						    if(!empty($medication_dosage[$v_medi['id']])){
						        foreach($medication_dosage[$v_medi['id']] as $time=>$value){
        						    $ins_pmedi_dosage = new PatientDrugPlanDosage();
        						    $ins_pmedi_dosage->ipid = $target_ipid;
        						    $ins_pmedi_dosage->drugplan_id = $drugplan_id;
        						    $ins_pmedi_dosage->dosage_time_interval = $time.':00';;
        						    $ins_pmedi_dosage->dosage = $value;
        						    $ins_pmedi_dosage->save();
						        }
						    }
						    //--
						    
						}
						
						$inserted[] = $drugplan_id;
					}
				}
				
				
				//ISPC-2829 Ancuta 06.04.2021
				$query = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanDosageIntervals')
				->where('ipid =  ?', $ipid)
				->andWhere('isdelete = 0');
				$q_res = $query->fetchArray();
				
				
				
				if(!empty($q_res)){
				    
				    //Ancuta 11.05.2021 :: TODO-4086
                    //if $target_ipid has data - remove then add the new ?!?!?
				    $loc = Doctrine_Query::create()
				    ->update("PatientDrugPlanDosageIntervals")
				    ->set('isdelete', "1")
				    ->where("ipid = ?", $target_ipid);
				    $loc->execute();    
				    //
				    
				    foreach($q_res as $typ => $ints){
			            $ins_pmedi_dosage_ints = new PatientDrugPlanDosageIntervals();
			            $ins_pmedi_dosage_ints->ipid = $target_ipid;
			            $ins_pmedi_dosage_ints->medication_type = $ints['medication_type'];
			            $ins_pmedi_dosage_ints->time_interval = $ints['time_interval'];
			            $ins_pmedi_dosage_ints->save();
				    }
				}
				//
			}
			
			$_POST['skip_trigger'] = 0 ;
// 			dd($cccc,$inserted);
			$medis_ins = $this->getPatientAllDrugs($target_ipid);
		}

		public function get_patient_drugplan($ipid, $current_period)
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    
		    $modules = new Modules();
		    if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
		    {
		        $acknowledge_func = '1';
		        	
		        $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,FALSE,false,true);
		        if(empty($declined)){
		            $declined[] = "XXXXXXXX";
		        }
		    
		        //get non approved data
		        $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,FALSE,false,true);
		        foreach($non_approved['change'] as $drugplan_id =>$not_approved){
		            $not_approved_ids[] = $not_approved['drugplan_id'];
		        }
		        if(empty($not_approved_ids)){
		            $not_approved_ids[] = "XXXXXXXX";
		        }
		    
		    }
		    else
		    {
		        $acknowledge_func = '0';
		    }
		    
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlan')
				->where("ipid = '" . $ipid . "'")
				->andWhere('DATE(medication_change) BETWEEN DATE("' . $current_period['start'] . '") AND DATE("' . $current_period['end'] . '") OR DATE(change_date) BETWEEN DATE("' . $current_period['start'] . '") AND DATE("' . $current_period['end'] . '") OR DATE(create_date) BETWEEN DATE("' . $current_period['start'] . '") AND DATE("' . $current_period['end'] . '")');
				if($acknowledge_func == "1")//Medication acknowledge
				{
    				$drugs->andWhereNotIn('id',$declined);
				}
		    $drugs->orderBy("id ASC");
			$drugs_array = $drugs->fetchArray();
			if($drugs_array)
			{
				return $drugs_array;
			}
			else
			{
				return false;
			}
		}
		
		

		public function get_treatment_care($pid,$remove_non_approved = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);
		
			$modules = new Modules();
			
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';
			    
			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,"treatment_care = 1", $special_case = "treatment_care");
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			    	
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,"treatment_care = 1", $special_case = "treatment_care");
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			        
			    	if($not_approved['status'] == "new" && $not_approved['approved'] == "0")
			    	{
			            $newly_not_approved[] = $not_approved['drugplan_id'];; 
			        }
			    }
			    
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			    if(empty($newly_not_approved)){
			        $newly_not_approved[] = "XXXXXXXX";
			    }
			    
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			
			$drugs = Doctrine_Query::create()
			->select("*,'another' as type")
			->from('PatientDrugPlan')
			->where("ipid = '" . $ipid . "'")
			->andWhere("isdelete = 0")
			->andWhere("treatment_care = 1");
			if($acknowledge_func == "1")//Medication acknowledge
			{
                $drugs->andWhereNotIn('id',$declined); // remove declined
                if($remove_non_approved)
                {
                    $drugs->andWhereNotIn('id',$newly_not_approved); // remove declined
                }
			}
			
			$drugs->orderBy("id ASC");
			$drugs->getSqlQuery();
			$dr = $drugs->execute();
		
			if($dr)
			{
				$drugsarray = $dr->toArray();
							
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    foreach($drugsarray as $dkey => $d_data)
				    {
				        if($non_approved['change'][$d_data['id']])
				        {
				            $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
				            $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
				            $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
				        }
				    }
				}
				return $drugsarray;
			}
		}
		
		public function get_isnutrition($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$ipid = Pms_CommonData::getIpid($pid);
		
			$modules = new Modules();
			
			if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
			{
			    $acknowledge_func = '1';

			    $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid,"isnutrition = 1", $special_case = "isnutrition");
			    if(empty($declined)){
			        $declined[] = "XXXXXXXX";
			    }
			    
			    //get non approved data
			    $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid,"isnutrition = 1", $special_case = "isnutrition");
			    foreach($non_approved['change'] as $drugplan_id =>$not_approved){
			        $not_approved_ids[] = $not_approved['drugplan_id'];
			    }
			    if(empty($not_approved_ids)){
			        $not_approved_ids[] = "XXXXXXXX";
			    }
			}
			else
			{
			    $acknowledge_func = '0';
			}
			
			
			$drugs = Doctrine_Query::create()
			->select("*,'another' as type")
			->from('PatientDrugPlan')
			->where("ipid = '" . $ipid . "'")
			->andWhere("isdelete = 0")
			->andWhere("isnutrition = 1");
			if($acknowledge_func == "1")//Medication acknowledge
			{
                $drugs->andWhereNotIn('id',$declined); // remove declined
			}
			
			$drugs->orderBy("id ASC");
			$drugs->getSqlQuery();
		
			$dr = $drugs->execute();
		
			if($dr)
			{
				$drugsarray = $dr->toArray();
				
				if($acknowledge_func == "1")//Medication acknowledge
				{
				    foreach($drugsarray as $dkey => $d_data)
				    {
				        if($non_approved['change'][$d_data['id']])
				        {
				            $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
				            $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
				            $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
				        }
				    }
				}
				return $drugsarray;
			}
		}

		

		public function get_drugplan_id_details($ipid, $drugplan_id)
		{
		    $drugs = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientDrugPlan')
		    ->where("ipid = '" . $ipid . "'")
		    ->andWhere("id = '" . $drugplan_id . "'");		    
		    $drugs->orderBy("id ASC");
		    $drugs_array = $drugs->fetchArray();
		    
		    if($drugs_array)
		    {
		        return $drugs_array[0];
		    }
		    else
		    {
		        return false;
		    }
		    
		}

		public function get_details($drugplan_ids)
		{
		    $drugs = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientDrugPlan')
		    ->andWhereIn("id" ,$drugplan_ids)
		    ->andWhere("isdelete = '0'");
		    $drugs->orderBy("id ASC");
		    $drugsarray = $drugs->fetchArray();
		    
		  
	        //extract the master_medications_array
	        $medi_master_ids[] = '99999999';
	        
	        foreach($drugsarray as $k_drug => $v_drug)
	        {
	            if ($v_drug['treatment_care'] == "1") {
	                $treatmen_care_med_ids[] = $v_drug['medication_master_id'];
	            } elseif ($v_drug['isnutrition'] == "1") {
	                $nutrition_med_ids[] = $v_drug['medication_master_id'];
	            } else{
    	            $medi_master_ids[] = $v_drug['medication_master_id'];
	            }
	            
	        }
	        //sorting
	        $medi_master_ids = array_values(array_unique($medi_master_ids));
	    
	        
	        
	        if(empty($treatmen_care_med_ids))
	        {
	            $treatmen_care_med_ids[] = "99999999";
	        }
	        $medtr = new MedicationTreatmentCare();
	        $medarr_tr = $medtr->getMedicationTreatmentCareById($treatmen_care_med_ids);
	        
	        foreach($medarr_tr as $k_medarr_tr => $v_medarr_tr)
	        {
	            $medication_tr_array[$v_medarr_tr['id']] = $v_medarr_tr;
	        }
	        
	        // get nutrition  details
	        if(empty($nutrition_med_ids))
	        {
	            $nutrition_med_ids[] = "99999999";
	        }
	        $mednutrition = new Nutrition();
	        $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
	        
	        foreach($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition)
	        {
	            $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
	        }
	        
	        //get the data from master medications array
	        if(empty($medi_master_ids))
	        {
	            $medi_master_ids[] = "99999999";
	        }
	        $med = new Medication();
	        $medarr = $med->master_medications_get($medi_master_ids, false);
	    
	        foreach($drugsarray as $k_drug_plan => $v_drug_plan)
	        {
	            if ($v_drug_plan['treatment_care'] == "1") 
	            {
	               $drugsarray[$k_drug_plan]['medication'] = $medication_tr_array[$v_drug_plan['medication_master_id']]['name'];
	                
	            }
	            elseif ($v_drug_plan['isnutrition'] == "1") 
	            {
	                
	               $drugsarray[$k_drug_plan]['medication'] = $medication_nutrition_array[$v_drug_plan['medication_master_id']]['name'];
	            } 
	            else
	            {
	               $drugsarray[$k_drug_plan]['medication'] = $medarr[$v_drug_plan['medication_master_id']];
	            }
	        }

		    if($drugsarray)
		    {
		        return $drugsarray;
		    }
		    else
		    {
		        return false;
		    }
		    
		}
		
		

		public function get_scheduled_medication($ipids,$interval = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $usertype = $logininfo->usertype;
		
		    if(!is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		
		
		    $modules = new Modules();
		    if($modules->checkModulePrivileges("111", $logininfo->clientid))//Medication acknowledge
		    {
		        $acknowledge_func = '1';
		
		        // Get declined data
		        $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids);
		         
		        foreach($ipids as $kd=>$ipidd)
		        {
		            foreach($declined_ids2ipids[$ipidd] as $kdd=>$declined_ids)
		            {
		                $declined[] = $declined_ids;
		            }
		             
		        }
		         
		        if(empty($declined)){
		            $declined[] = "XXXXXXXX";
		        }
		
		        //get non approved data
		        $non_approved = PatientDrugPlanAlt::get_patients_drugplan_alt($ipids,false,false,true);
		
		        foreach($ipids as $k=>$ipid)
		        {
		            foreach($non_approved[$ipid]['change'] as $drugplan_id =>$not_approved)
		            {
		                $not_approved_ids[] = $not_approved['drugplan_id'];
		                 
		                if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
		                    $newly_not_approved[] = $not_approved['drugplan_id'];;
		                }
		            }
		        }
		
		        if(empty($not_approved_ids)){
		            $not_approved_ids[] = "XXXXXXXX";
		        }
		        if(empty($newly_not_approved)){
		            $newly_not_approved[] = "XXXXXXXX";
		        }
		    }
		    else
		    {
		        $acknowledge_func = '0';
		    }
		     
		    $scheduled_date = "ADDDATE(`administration_date`, `days_interval` ) AS scheduled_date";
            if($interval){
    		    $sql_scheduled_date= "DATE(scheduled_date) BETWEEN  DATE_SUB(NOW(),INTERVAL ".$interval['before']." DAY) AND DATE_ADD(NOW(), INTERVAL ".$interval['after']." DAY)";
                
            } else{
    		    $sql_scheduled_date= "DATE(scheduled_date) BETWEEN  NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)";
            }
		
		
		    $drugs = Doctrine_Query::create()
		    ->select("*,"  . $scheduled_date . "")
		    ->from('PatientDrugPlan')
		    ->whereIn('ipid', $ipids)
		    ->andWhere("isdelete = '0'")
		    ->andWhere("scheduled = '1' OR has_interval = '1' ")
		    ->andWhere("administration_date != '0000-00-00 00:00:00'");
		    if($acknowledge_func == "1")
		    {
		        $drugs->andWhereNotIn('id',$declined); // remove declined
		        $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
		    }
		    $drugs->having($sql_scheduled_date);
		    $drugs->orderBy("id ASC");
		    $drugsarray = $drugs->fetchArray();
		
		    foreach($drugsarray as $key_drg => $v_drg)
		    {
		        $master_medications_ids[] = $v_drg['medication_master_id'];
		        $drug_ids[] = $v_drg['id'];
		    }
		
		    if(empty($master_medications_ids)){
		        $master_medications_ids[] = "99999999";
		    }
		    //get the data from master medications array
		    $med = new Medication();
		    $master_tr_array = $med->master_medications_get($master_medications_ids, false);
		
		
		    if(!empty($drug_ids)){
		        $drugs_extra = Doctrine_Query::create()
		        ->select('*')
		        ->from('PatientDrugPlanExtra')
		        ->whereIn('ipid', $ipids)
		        ->andWhere("isdelete = '0'")
		        ->andWhereIn("drugplan_id",$drug_ids);
		        $drugsarray_extra = $drugs_extra->fetchArray();
		
		        if(!empty($drugsarray_extra)){
		
		            // get details for indication
		            $indications_array = MedicationIndications::client_medication_indications($clientid);
		
		            foreach ($indications_array as $ki => $ind_value) {
		                $indication[$ind_value['id']]['name'] = $ind_value['indication'];
		                $indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
		            }
		
		            foreach($drugsarray_extra as $k=>$extra_data){
		                $drug_indication[$extra_data['drugplan_id']] = $indication[$extra_data['indication']];
		            }
		
		        }
		    }
		
		
		    foreach($drugsarray as $key => $drugp)
		    {
		         
		        if($drugp['days_interval'])
		        {
		            $drugp['days_interval'] =  $drugp['days_interval'];
		        }
		        $patient_medication[$drugp['ipid']][$drugp['id']]['medication_name'] = $master_tr_array[$drugp['medication_master_id']]  ;
		        $patient_medication[$drugp['ipid']][$drugp['id']]['dosage'] = $drugp['dosage'];
		        $patient_medication[$drugp['ipid']][$drugp['id']]['indications'] = $drug_indication[$drugp['id']] ;
		        $patient_medication[$drugp['ipid']][$drugp['id']]['comments'] = $drugp['comments'];
		        $patient_medication[$drugp['ipid']][$drugp['id']]['days_interval'] =   $drugp['days_interval'];
		        $patient_medication[$drugp['ipid']][$drugp['id']]['due_date'] = $drugp['scheduled_date'];
		
		    }
		
		    foreach($ipids as $k_ipid => $v_ipid)
		    {
		        if(!empty($patient_medication[$v_ipid]))
		        {
		            $patient_medication_data['scheduled_medication_data'][$v_ipid] = $patient_medication[$v_ipid];
		            $patient_medication_data['ipids'][] = $v_ipid;
		        }
		    }
		
		    return $patient_medication_data;
		}

		
		public function get_medication_exportdata($ipid, $addcolumn=array(), $addcolumnpumpemeta=array(), $to_db=false){
			$decid=Pms_CommonData::getIdfromIpid($ipid);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid=$logininfo->clientid;
		
			$cocktails_model = new PatientDrugPlanCocktails();
			$cocktails_arr = $cocktails_model->getCocktails($ipid);
			$cocktails=array();
			foreach ($cocktails_arr as $cocktail){
				$cocktails[$cocktail['id']]=$cocktail;
			}
		
			$medic = new PatientDrugPlan();
			$allmedicarr = $medic->getMedicationPlanAll($decid);
			$medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);
		
			$med_ids=array();
			foreach($allmedicarr as $medic){
				$med_ids[]=$medic['medication_master_id'];
			}
		
			$master_medication=array();
			if(count($med_ids)>0) {
				$medmast = new Medication();
				$med_ids=array_unique($med_ids);
				$master_medication = $medmast->master_medications_get($med_ids, false);
			}
		
			$outarr=array();
			$outarr['medic']=array();
			$outarr['bedarfs']=array();
			$outarr['iv']=array();
			$outarr['pumpe']=array();
		
			foreach ($allmedicarr as $medic){
		
				$newmed=array();
				$newmed['medication']       =  $master_medication[$medic['medication_master_id']];
				$newmed['dosage']           =  $medic['dosage'];
				$newmed['comments']         =  $medic['comments'];
				$newmed['drug']             =   "";
				$newmed['unit']             =   "";
				$newmed['dosage_form']      =   "";
				$newmed['concentration']    =   "";
				if(count($addcolumn)>0){
					foreach ($addcolumn as $addval){
						$newmed[$addval[0]]=$addval[1];
					}
				}
		
				if(isset($medication_extra[$medic['id']])){
					$extra=$medication_extra[$medic['id']];
					$newmed['drug']             =   $extra['drug'];
					$newmed['unit']             =   $extra['unit'];
					$newmed['dosage_form']      =   $extra['dosage_form'];
					$newmed['concentration']    =   $extra['concentration'];
					$newmed['indication'] ="";
					if (is_array($extra['indication'])) {
						$newmed['indication'] = $extra['indication']['name'];
					}
					$newmed['type'] = $extra['type'];
				}
		
				if($medic['isbedarfs']==1){
					$outarr['bedarfs'][]=$newmed;
				}
				elseif($medic['isivmed']==1){
					$outarr['iv'][]=$newmed;
				}
				elseif($medic['isschmerzpumpe']==1){
					$cocktailid=$medic['cocktailid'];
		
					if(isset($pumpeid_to_pumpe[$cocktailid])) {
						$outarr['pumpe'][$pumpeid_to_pumpe[$cocktailid]][] = $newmed;
					}else{
						$newindex=count($pumpeid_to_pumpe);
						$outarr['pumpe'][$newindex]=array();
		
						if(count($addcolumnpumpemeta)>0){
							foreach ($addcolumnpumpemeta as $addval){
								$outarr['pumpe'][$newindex]['meta'][$addval[0]]=$addval[1];
							}
						}
		
						$outarr['pumpe'][$newindex]['meta']['description']              =$cocktails[$cocktailid]['description'];
						$outarr['pumpe'][$newindex]['meta']['bolus']                    =$cocktails[$cocktailid]['bolus'];
						$outarr['pumpe'][$newindex]['meta']['flussrate']                =$cocktails[$cocktailid]['flussrate'];
						$outarr['pumpe'][$newindex]['meta']['pumpe_medication_type']    =$cocktails[$cocktailid]['pumpe_medication_type'];
						$outarr['pumpe'][$newindex]['meta']['sperrzeit']                =$cocktails[$cocktailid]['sperrzeit'];
						$outarr['pumpe'][$newindex]['meta']['carrier_solution']         =$cocktails[$cocktailid]['carrier_solution'];
						$outarr['pumpe'][$newindex]['meta']['max_bolus']                =$cocktails[$cocktailid]['max_bolus'];
		
						$outarr['pumpe'][$newindex][] = $newmed;
						$pumpeid_to_pumpe[$cocktailid]=$newindex;
					}
				}
				elseif($medic['isnutrition']==1){
		
				}
				elseif($medic['iscrisis']==1){
		
				}
				else{
					$outarr['medic'][]=$newmed;
				}
			}
		
			if(count($allmedicarr)>0 && $to_db){
				SystemsSyncPackets::createPacket($ipid, array('drugs'=>$outarr, 'date'=>date('d.m.Y')), "med", 1);
			}
		
			return $outarr;
		}

        /**
         * //Maria:: Migration CISPC to ISPC 22.07.2020
         * this is a copy and modification of get_medication_exportdata
         * i can't make decision, if the original method can be changed safely
         */
        public function get_medic_exportdata($ipid, $addcolumn=array(), $addcolumnpumpemeta=array()){
            $decid=Pms_CommonData::getIdfromIpid($ipid);
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid=$logininfo->clientid;

            $cocktails_model = new PatientDrugPlanCocktails();
            $cocktails_arr = $cocktails_model->getCocktails($ipid);
            $cocktails=array();
            foreach ($cocktails_arr as $cocktail){
                $cocktails[$cocktail['id']]=$cocktail;
            }

            $medic = new PatientDrugPlan();
            $allmedicarr = $medic->getMedicationPlanAll($decid, true);
            $medication_extra  = PatientDrugPlanExtra::get_patient_drugplan_extra($ipid,$clientid);

            $med_ids=array();
            foreach($allmedicarr as $medic){
                $med_ids[]=$medic['medication_master_id'];
            }

            $master_medication=array();
            if(count($med_ids)>0) {
                $medmast = new Medication();
                $med_ids=array_unique($med_ids);
                $master_medication = $medmast->master_medications_get($med_ids, false);
            }

            $outarr = Client::getClientconfig($clientid,'block_medication_clinic');
            $pumpeid_to_pumpe = array();

            foreach ($allmedicarr as $medic){

                $newmed=array();
                $newmed['medication']       =  $master_medication[$medic['medication_master_id']];
                $newmed['dosage']           =  $medic['dosage'];
                if($medic['isschmerzpumpe'] == 1){
                    $dosage_value = str_replace(",",".",$medic['dosage']);
                    $newmed['dosage24h'] =  ($dosage_value != '') ? $dosage_value * 24 : '';
                }
                $newmed['comments']         =  $medic['comments'];
                $newmed['drug']             =   "";
                $newmed['unit']             =   "";
                $newmed['dosage_form']      =   "";
                $newmed['concentration']    =   "";
                if(count($addcolumn)>0){
                    foreach ($addcolumn as $addval){
                        $newmed[$addval[0]]=$addval[1];
                    }
                }

                if(isset($medication_extra[$medic['id']])){
                    $extra=$medication_extra[$medic['id']];
                    $newmed['drug']             =   $extra['drug'];
                    $newmed['unit']             =   $extra['unit'];
                    $newmed['dosage_form']      =   $extra['dosage_form'];
                    $newmed['concentration']    =   $extra['concentration'];
                    $newmed['indication'] ="";
                    if (is_array($extra['indication'])) {
                        $newmed['indication'] = $extra['indication']['name'];
                    }
                    $newmed['type'] = $extra['type'];
                }

                if($medic['isbedarfs']==1){
                    $outarr['isbedarfs']['medic'][]=$newmed;
                }
                elseif($medic['isivmed']==1){
                    $outarr['isivmed']['medic'][]=$newmed;
                }
                elseif ($medic['isschmerzpumpe'] == 1) {
                    $cocktailid = $medic['cocktailid'];

                    if (isset($pumpeid_to_pumpe[$cocktailid])) {
                        $pumpe[$pumpeid_to_pumpe[$cocktailid]][] = $newmed;
                    } else {
                        $newindex=count($pumpeid_to_pumpe);
                        $pumpe[$newindex]=array();

                        if(count($addcolumnpumpemeta)>0){
                            foreach ($addcolumnpumpemeta as $addval){
                                $pumpe[$newindex]['meta'][$addval[0]]=$addval[1];
                            }
                        }

                        $pumpe[$newindex]['meta']['description']              =$cocktails[$cocktailid]['description'];
                        $pumpe[$newindex]['meta']['bolus']                    =$cocktails[$cocktailid]['bolus'];
                        $pumpe[$newindex]['meta']['flussrate']                =$cocktails[$cocktailid]['flussrate'];
                        $pumpe[$newindex]['meta']['pumpe_medication_type']    =$cocktails[$cocktailid]['pumpe_medication_type'];
                        $pumpe[$newindex]['meta']['pumpe_type']               =$cocktails[$cocktailid]['pumpe_type'];
                        $pumpe[$newindex]['meta']['sperrzeit']                =$cocktails[$cocktailid]['sperrzeit'];
                        $pumpe[$newindex]['meta']['carrier_solution']         =$cocktails[$cocktailid]['carrier_solution'];
                        $pumpe[$newindex]['meta']['max_bolus']                =$cocktails[$cocktailid]['max_bolus'];

                        $pumpe[$newindex][] = $newmed;
                        $pumpeid_to_pumpe[$cocktailid]=$newindex;
                    }
                }
                elseif($medic['isnutrition']==1){
                    $outarr['isnutrition']['medic'][]=$newmed;
                }
                elseif($medic['scheduled']==1){
                }
                elseif($medic['iscrisis']==1){
                }
                else{
                    $outarr['actual']['medic'][]=$newmed;
                }
            }

            if(count($pumpe)>0) {
                $pumpe = $this->complete_pumpen_meta($pumpe);
                $outarr['isschmerzpumpe']['pumpe'] = $pumpe;
            }


           // $tmp = json_encode($outarr);
            return $outarr;
        }

        public static function getPatientsDosageIntervals($ipid){
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid=$logininfo->clientid;
            $modules = new Modules();
            $individual_medication_time_m = $modules->checkModulePrivileges("141", $clientid);
            if($individual_medication_time_m){
                $individual_medication_time = 1;
            }else {
                $individual_medication_time = 0;
            }


            if($individual_medication_time == "1"){
                //get time scchedule options
                $client_med_options = MedicationOptions::client_saved_medication_options($clientid);

                $time_blocks = array('all');
                $NOT_timed_scheduled_medications = array();
                foreach($client_med_options as $mtype=>$mtime_opt){
                    if($mtime_opt['time_schedule'] == "1"){
                        $time_blocks[]  = $mtype;
                        $timed_scheduled_medications[]  = $mtype;
                    } else {
                        $NOT_timed_scheduled_medications[]  = $mtype;
                    }
                }
                if(empty($timed_scheduled_medications)){
                    $timed_scheduled_medications = array("actual","isivmed");
                }

                foreach($timed_scheduled_medications  as $tk=>$tmed){
                    if(in_array($tmed,$NOT_timed_scheduled_medications)){
                        unset($timed_scheduled_medications[$tk]);
                    }
                }
            } else {
                $timed_scheduled_medications = array("actual","isivmed");
                $time_blocks = array("actual","isivmed");
            }


            $patient_time_scheme  = PatientDrugPlanDosageIntervals::get_patient_dosage_intervals($ipid,$clientid,$time_blocks);

            if($patient_time_scheme['patient']){
                foreach($patient_time_scheme['patient']  as $med_type => $dos_data)
                {
                    if($med_type != "new"){
                        $set = 0;
                        foreach($dos_data  as $int_id=>$int_data)
                        {
                            if(in_array($med_type,$patient_time_scheme['patient']['new'])){

                                $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                                $interval_array['interval'][$med_type][$int_id]['custom'] = '1';

                                $dosage_settings[$med_type][$set] = $int_data;
                                $set++;

                                $dosage_intervals[$med_type][$int_data] = $int_data;
                            }
                            else
                            {
                                $interval_array['interval'][$med_type][$int_id]['time'] = $int_data;
                                $interval_array['interval'][$med_type][$int_id]['custom'] = '0';
                                $interval_array['interval'][$med_type][$int_id]['interval_id'] = $int_id;

                                $dosage_settings[$med_type][$set] = $int_data;
                                $set++;

                                $dosage_intervals[$med_type][$int_data] = $int_data;
                            }
                        }
                    }
                }
            }
            else
            {
                foreach($patient_time_scheme['client']  as $med_type=>$mtimes)
                {

                    $inf=1;
                    $setc= 0;
                    foreach($mtimes as $int_id=>$int_data){

                        $interval_array['interval'][$med_type][$inf]['time'] = $int_data;
                        $interval_array['interval'][$med_type][$inf]['custom'] = '1';
                        $dosage_settings[$med_type][$setc] = $int_data;
                        $setc++;
                        $inf++;

                        $dosage_intervals[$med_type][$int_data] = $int_data;
                    }
                }
            }

            return $dosage_intervals;
        }

    public function find_patient_isintubated($pid, $remove_non_approved = false, $allow_deleted = false, $order_by = false)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $ipid = Pms_CommonData::getIpid($pid);
        
        $modules = new Modules();
        
        // Medication acknowledge
        if ($modules->checkModulePrivileges("111", $logininfo->clientid)) {
            $acknowledge_func = '1';
            
            $declined = PatientDrugPlanAlt::get_declined_patient_drugplan_alt($ipid, "isnutrition = 1", $special_case = "isnutrition");
            
            // get non approved data
            $non_approved = PatientDrugPlanAlt::get_patient_drugplan_alt($ipid, "isnutrition = 1", $special_case = "isnutrition");
            $newly_not_approved = array();
            foreach ($non_approved['change'] as $drugplan_id => $not_approved) {
                $not_approved_ids[] = $not_approved['drugplan_id'];
                
                if ($not_approved['status'] == "new" && $not_approved['approved'] == "0") {
                    $newly_not_approved[] = $not_approved['drugplan_id'];
                    ;
                }
            }
       
        } else {
            $acknowledge_func = '0';
        }
        
        $drugs = Doctrine_Query::create()->select("*,'another' as type")
            ->from('PatientDrugPlan')
            ->where("ipid = ?", $ipid)
            ->andWhere("isintubated = 1");
        if($allow_deleted === false){
            $drugs->andWhere("isdelete = 0");
        }
        // Medication acknowledge
        if ($acknowledge_func == "1") {
            if (! empty($declined)) {
                $drugs->andWhereNotIn('id', $declined); // remove declined
            }
            if ($remove_non_approved && ! empty($newly_not_approved)) {
                $drugs->andWhereNotIn('id', $newly_not_approved); // remove newly added - not approved
            }
        }
        if( ! empty($order_by)){
            $drugs->orderBy($order_by);
        } else{
           $drugs->orderBy("id ASC");
        }
//        echo  $drugs->getSqlQuery();
//         exit;
        $dr = $drugs->execute();
        
        if ($dr) {
            $drugsarray = $dr->toArray();
            
            $medi_master_ids = array();
            $nutrition_med_ids = array();
            $medication_nutrition_array = array();
            
            
            $drug_ids = array();
            
            foreach ($drugsarray as $k_drug => $v_drug) {
                $drug_ids[] = $v_drug['id'];
                $medi_master_ids[] = $v_drug['medication_master_id'];
            }
            
            //sorting
            $medi_master_ids = array_values(array_unique($medi_master_ids));
            
            //get the data from master medications array
            $med = new Medication();
            $medarr = $med->master_medications_get($medi_master_ids, false, true);
//             dd($medarr);
            
//             if (! empty($nutrition_med_ids)) {
//                 $nutrition_med_ids = array_values(array_unique($nutrition_med_ids));
                
//                 $mednutrition = new Nutrition();
//                 $medarr_nutrition = $mednutrition->getMedicationNutritionById($nutrition_med_ids);
                
//                 foreach ($medarr_nutrition as $k_medarr_nutritionr => $v_medarr_nutrition) {
//                     $medication_nutrition_array[$v_medarr_nutrition['id']] = $v_medarr_nutrition;
//                 }
//             }
            $drugs_extra = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlanExtra')
            ->whereIn('ipid', array($ipid))
            ->andWhere("isdelete = '0'")
            ->andWhereIn("drugplan_id",$drug_ids);
            $drugsarray_extra = $drugs_extra->fetchArray();
            //1152200
            
            $drug_extra_array = array();
            if(!empty($drugsarray_extra)){
                foreach($drugsarray_extra as $k=>$extra_data){
                    $drug_extra_array[$extra_data['drugplan_id']] = $extra_data;
                }
            }
            
            // Medication acknowledge
            if ($acknowledge_func == "1") {
                foreach ($drugsarray as $dkey => $d_data) {
                    if ($non_approved['change'][$d_data['id']]) {
                        $drugsarray[$dkey]['approved'] = $non_approved['change'][$d_data['id']]['approved'];
                        $drugsarray[$dkey]['values'] = $non_approved['change'][$d_data['id']]['change_values'];
                        $drugsarray[$dkey]['on_hold_changes'][$d_data['id']] = $non_approved['change'][$d_data['id']];
                    }
                }
            }
            foreach ($drugsarray as $k_drug_plan => $v_drug_plan) {
                if (empty($v_drug_plan['medication'])) {
                    $drugsarray[$k_drug_plan]['medication'] = $medarr['Medication'][$v_drug_plan['medication_master_id']]['name'];
                } else {
                    $drugsarray[$k_drug_plan]['medication'] = $v_drug_plan['medication'];
                }
                
                $drugsarray[$k_drug_plan]['medication_master'] = $medarr['Medication'][$v_drug_plan['medication_master_id']];
                
                
                if( ! empty($drug_extra_array[$v_drug_plan['id']])){
                    $drugsarray[$k_drug_plan]['extra'] = $drug_extra_array[$v_drug_plan['id']];
                }
                
            }
            
            return $drugsarray;
        }
    }
		//Maria:: Migration CISPC to ISPC 22.07.2020
        public function complete_pumpen_meta($pumpen_data){

            foreach( $pumpen_data as $key => $pumpe) {
                $f_bolus = "";
                $f_maxbolus = "";
                $bolustext = "";
                if (strlen($pumpe['meta']['bolus']) > 0) {
                    $bolus = str_replace(',', '.', $pumpe['meta']['bolus']);
                    $bolus = floatval($bolus);
                    if ($bolus > 0) {
                        foreach ($pumpe as $pindex => $pumpa) {
                            if ($pindex === "meta") {
                                continue;
                            }
                            $pa_medname = $pumpa['medication'];
                            $pa_unit = $pumpa['unit'];
                            $pa_concentration = $pumpa['concentration'];
                            $pa_concentration = str_replace(',', '.', $pa_concentration);
                            $pa_concentration = floatval($pa_concentration);
                            if ($pa_concentration > 0) {
                                $myval = $pa_concentration * $bolus;
                                $myval = round($myval, 3);
                                $myval = str_replace(".", ",", $myval);
                                if (strlen($bolustext) > 0) {
                                    $bolustext = $bolustext . ", ";
                                }
                                $bolustext = $bolustext . $myval . " " . $pa_unit . " " . $pa_medname;
                            }
                        }

                        if (strlen($bolustext) > 0) {
                            $bolustext = "Jeder Bolus entspricht " . $bolustext;
                            $f_bolus = str_replace(".", ",", $bolus) . " (" . $bolustext . ")";
                        }
                        else {
                            $f_bolus = $bolus;
                        }
                    }
                }
	
                $pumpen_data[$key]['meta']['bolus_text'] = $f_bolus;
            }

            return $pumpen_data;

        }


    /**
     * Ancuta ISPC-2512
     * #ISPC-2512PatientCharts
     * @param unknown $ipids
     * @param number $clientid
     * @param boolean $period
     * @param string $type
     * @return unknown
     */
    public function get_chart_medication($ipids,$clientid=0,$period=false,$type = 'actual', $with_deleted_in_period = false) //ISPC-2871,Elena,30.03.2021
    {
        
        if(!is_array($ipids))
        {
            $ipids = array($ipids);
        }
        else
        {
            $ipids = $ipids;
        }
        
        
        $modules = new Modules();
        if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
        {
            $acknowledge_func = '1';
            
            // Get declined data
            $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids);
            
            foreach($ipids as $kd=>$ipidd)
            {
                foreach($declined_ids2ipids[$ipidd] as $kdd=>$declined_ids)
                {
                    $declined[] = $declined_ids;
                }
                
            }
            
            if(empty($declined)){
                $declined[] = "XXXXXXXX";
            }
            
            //get non approved data
            $non_approved = PatientDrugPlanAlt::get_patients_drugplan_alt($ipids,false,false,true);
            
            foreach($ipids as $k=>$ipid)
            {
                foreach($non_approved[$ipid]['change'] as $drugplan_id =>$not_approved)
                {
                    $not_approved_ids[] = $not_approved['drugplan_id'];
                    
                    if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
                        $newly_not_approved[] = $not_approved['drugplan_id'];;
                    }
                }
            }
            
            if(empty($not_approved_ids)){
                $not_approved_ids[] = "XXXXXXXX";
            }
            if(empty($newly_not_approved)){
                $newly_not_approved[] = "XXXXXXXX";
            }
        }
        else
        {
            $acknowledge_func = '0';
        }
        
        $drugs = Doctrine_Query::create()
        ->select("*")
        ->from('PatientDrugPlan')
            //ISPC-2871,Elena,30.03.2021
        ->whereIn('ipid', $ipids);
        //ISPC-2871,Elena,30.03.2021
        if(!$with_deleted_in_period){
            $drugs->andWhere("isdelete = '0'");
        }else{
            $drugs->andWhere("(isdelete = '0' OR (isdelete = '1' and delete_date >= '" . $with_deleted_in_period['start'] . "'))");
        }

        if($period){
            $drugs->andWhere('DATE(medication_change) BETWEEN DATE("' . $period['start'] . '") AND DATE("' . $period['end'] . '") OR DATE(change_date) BETWEEN DATE("' . $period['start'] . '") AND DATE("' . $period['end'] . '") OR DATE(create_date) BETWEEN DATE("' . $current_period['start'] . '") AND DATE("' . $current_period['end'] . '")');
        }

        if($type == "isbedarfs"){
            $drugs->andWhere("isbedarfs = '1'");
        }
        elseif($type == "iscrisis"){
            $drugs->andWhere("iscrisis = '1'");
        }
        elseif($type == "isnutrition"){
            $drugs->andWhere("isnutrition = '1'");
        }
        elseif($type == "isschmerzpumpe"){
            $drugs->andWhere("isschmerzpumpe = '1'");
        }
        //ISPC-2871,Elena,30.03.2021
        elseif($type == "isivmed"){
            $drugs->andWhere("isivmed = '1'");
        }elseif($type == 'ispumpe'){
            $drugs->andWhere("ispumpe = '1'");
        }
        elseif($type == "actual"){
            $drugs->andWhere("isbedarfs = '0'")
            ->andWhere("isivmed = 0")
            ->andWhere("iscrisis = 0")
            ->andWhere("isschmerzpumpe = 0")
            ->andWhere("ispumpe = 0")//ISPC-2833 Ancuta 04.03.2021
            ->andWhere("treatment_care = 0")
            ->andWhere("isnutrition = 0")
            ->andWhere("isintubated = 0")
            ->andWhere('scheduled = "0"');
        }
        if($acknowledge_func == "1")
        {
            $drugs->andWhereNotIn('id',$declined); // remove declined
           // $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved//ISPC-2871,Elena,11.05.2021
        }
        $drugs->orderBy("id ASC");
        $drugsarray = $drugs->fetchArray();
        
        foreach($drugsarray as $key_drg => $v_drg)
        {
            $master_medications_ids[] = $v_drg['medication_master_id'];
            $drug_ids[] = $v_drg['id'];
        }
        
        if(empty($master_medications_ids)){
            $master_medications_ids[] = "99999999";
        }
        //get the data from master medications array
        $med = new Medication();
        $master_tr_array = $med->master_medications_get($master_medications_ids, false);
        
        
        if(!empty($drug_ids)){
            $drugs_extra = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlanExtra')
            ->whereIn('ipid', $ipids)
            ->andWhere("isdelete = '0'")
            ->andWhereIn("drugplan_id",$drug_ids);
            $drugsarray_extra = $drugs_extra->fetchArray();
            
            if(!empty($drugsarray_extra)){
                
                // get details for indication
                $indications_array = MedicationIndications::client_medication_indications($clientid);
                
                foreach ($indications_array as $ki => $ind_value) {
                    $indication[$ind_value['id']]['name'] = $ind_value['indication'];
                    $indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
                }
                
                $extra_info = array();
                foreach($drugsarray_extra as $k=>$extra_data){
                    $drug_indication[$extra_data['drugplan_id']] = $indication[$extra_data['indication']];
                    $extra_info[$extra_data['drugplan_id']] = $extra_data;
                }
                
                
                
                
                
                // get details for unit
                $units_array = MedicationUnit::client_medication_unit($clientid);
                foreach ($units_array as $ku => $unit_value) {
                    $unit[$unit_value['id']] = $unit_value['unit'];
                }
                
                // get details for dosage_form
                $dosage_form_array = MedicationDosageform::client_medication_dosage_form($clientid, true);// added second param true to includ extra for custom options from sets
                foreach ($dosage_form_array as $ku => $ds_value) {
                    $dosage_form[$ds_value['id']] = $ds_value['dosage_form'];
                }
                
            }
            
            
            $drugplan_dosages = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlanDosage')
            ->whereIn('ipid', $ipids)
            ->andWhere("isdelete = '0'")
            ->andWhereIn("drugplan_id",$drug_ids)
            ->fetchArray();
            
            $dosage_info = array();
            foreach($drugplan_dosages as $k=>$dosage_data){
                $dosage_info[$dosage_data['drugplan_id']][] = $dosage_data;
            }
        }
        
        
        foreach($drugsarray as $key => $drugp)
        {
            
            if($drugp['days_interval'])
            {
                $drugp['days_interval'] =  $drugp['days_interval'];
            }
            $patient_medication[$drugp['ipid']][$drugp['id']]['id'] = $drugp['id'] ;
            //ISPC-2871,Elena,30.03.2021
            $patient_medication[$drugp['ipid']][$drugp['id']]['isdelete'] = $drugp['isdelete'] ;
            $patient_medication[$drugp['ipid']][$drugp['id']]['delete_date'] = $drugp['delete_date'] ;
            $patient_medication[$drugp['ipid']][$drugp['id']]['cocktailid'] = $drugp['cocktailid'] ;
            $patient_medication[$drugp['ipid']][$drugp['id']]['pumpe_id'] = $drugp['pumpe_id'] ; //ISPC-2871,Elena,30.03.2021
            $patient_medication[$drugp['ipid']][$drugp['id']]['create_date'] = $drugp['create_date'] ;
            $patient_medication[$drugp['ipid']][$drugp['id']]['medication_name'] = $master_tr_array[$drugp['medication_master_id']]  ;
            $patient_medication[$drugp['ipid']][$drugp['id']]['name'] = $master_tr_array[$drugp['medication_master_id']]  ;
            $patient_medication[$drugp['ipid']][$drugp['id']]['category'] = $type;
            $patient_medication[$drugp['ipid']][$drugp['id']]['indications'] = $drug_indication[$drugp['id']] ;
            $patient_medication[$drugp['ipid']][$drugp['id']]['comments'] = $drugp['comments'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['date'] = $drugp['medication_change'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['days_interval'] =   $drugp['days_interval'];
            //ISPC-2903,Elena,26.04.2021
            $patient_medication[$drugp['ipid']][$drugp['id']]['has_interval'] =   $drugp['has_interval'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['administration_date'] =   $drugp['administration_date'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['due_date'] = $drugp['scheduled_date'];
//             $patient_medication[$drugp['ipid']][$drugp['id']]['full_data'] = $drugp;
            $patient_medication[$drugp['ipid']][$drugp['id']]['extra'] = $extra_info[$drugp['id']];
            $patient_medication[$drugp['ipid']][$drugp['id']]['drug'] = $extra_info[$drugp['id']]['drug'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['unit_name'] = $unit[$extra_info[$drugp['id']]['unit']];
            $patient_medication[$drugp['ipid']][$drugp['id']]['dosage_form_name'] = $dosage_form[$extra_info[$drugp['id']]['dosage_form']];
            $patient_medication[$drugp['ipid']][$drugp['id']]['main_old_dosage'] = $drugp['dosage'];
            if($dosage_info[$drugp['id']]){
                $patient_medication[$drugp['ipid']][$drugp['id']]['dosage'] = $dosage_info[$drugp['id']];
                $patient_medication[$drugp['ipid']][$drugp['id']]['drugplan_dosage'] = $dosage_info[$drugp['id']];
            } else{
                $patient_medication[$drugp['ipid']][$drugp['id']]['dosage'] = $drugp['dosage'];
            }
            
            //ISPC-2636 Lore 29.07.2020
                $patient_medication[$drugp['ipid']][$drugp['id']]['medication'] = $master_tr_array[$drugp['medication_master_id']]  ;
                $patient_medication[$drugp['ipid']][$drugp['id']]['indication'] = $extra_info[$drugp['id']]['indication'];
                $patient_medication[$drugp['ipid']][$drugp['id']]['importance'] = $extra_info[$drugp['id']]['importance'];
                $patient_medication[$drugp['ipid']][$drugp['id']]['change_date'] = $drugp['change_date'] ;
            //.
           
            //ISPC-2826 Lore 18.02.2021
           $medication_escalation = PatientDrugPlanExtra::getMedicationEscalation();
           $patient_medication[$drugp['ipid']][$drugp['id']]['escalation'] = !empty($extra_info[$drugp['id']]['escalation']) ? $medication_escalation[$extra_info[$drugp['id']]['escalation']]: '' ; 
                
           //ISPC-2797 Ancuta     19.02.2021
           $patient_medication[$drugp['ipid']][$drugp['id']]['active_date'] = $drugp['active_date'] ;
           //ISPC-2871,Elena,11.05.2021
           if(in_array($drugp['id'], $not_approved_ids)){
               $patient_medication[$drugp['ipid']][$drugp['id']]['approved'] =  0;

           } else{
               $patient_medication[$drugp['ipid']][$drugp['id']]['approved'] =  1;
           }
            
        }
        
        //ISPC-2636 Lore 30.07.2020
        $cust = Doctrine_Query::create()
        ->select("client_medi_sort, user_overwrite_medi_sort_option")
        ->from('Client')
        ->where('id = ?',  $clientid);
        $cust->getSqlQuery();
        $disarray = $cust->fetchArray();
        
        $client_medi_sort = $disarray[0]['client_medi_sort'];
        $user_overwrite_medi_sort_option = $disarray[0]['user_overwrite_medi_sort_option'];
        
        $uss = Doctrine_Query::create()
        ->select('*')
        ->from('UserTableSorting')
        ->Where('client = ?', $clientid)
        ->orderBy('change_date DESC')
        ->limit(1);
        $uss_arr = $uss->fetchArray();
        $last_sort_order = unserialize($uss_arr[0]['value']);
        //dd($last_sort_order[0][1]);
        
        /* ================ MEDICATION :: USER SORTING ======================= */
        $usort = new UserTableSorting();
        $saved_data = $usort->user_saved_sorting($userid, false, $ipid);
                
        foreach($saved_data as $k=>$sord){
            if($sord['name'] == "order"){
                
                $med_type_sarr = explode("-",$sord['page']);
                $page = $med_type_sarr[0];
                $med_type = $med_type_sarr[1];
                if($page == "patientmedication" && $med_type){
                    $order_value = unserialize($sord['value']);
                    $saved_order[$med_type]['col'] = $order_value[0][0] ;
                    $saved_order[$med_type]['ord'] = $order_value[0][1];
                    
                }
            }
        }
        
        //TODO-3450 Ancuta 22.09.2020 - added sorting in request - so we can use BOTH clent sorting - and the sorting in page, as  the page is refreshed when sorting is applied
        if(!empty($client_medi_sort)){
            
            $request_sort = array();
            if(!empty($_REQUEST['sort_b']) && !empty($_REQUEST['sort_c']) && !empty($_REQUEST['sort_d'])){
                $request_sort[$_REQUEST['sort_b']]['col'] = $_REQUEST['sort_c'];
                $request_sort[$_REQUEST['sort_b']]['ord'] = $_REQUEST['sort_d'];
            }
            
            foreach($medication_blocks as $k=>$mt){
                if(!empty($request_sort[$mt])){
                    $saved_order[$mt]['col'] = $request_sort[$mt]['col'];
                    $saved_order[$mt]['ord'] = $request_sort[$mt]['ord'];
                }
                elseif(!empty($client_medi_sort)){
                    $saved_order[$mt]['col'] = !empty($client_medi_sort) ? $client_medi_sort : "medication";              //ISPC-2636 Lore 29.07.2020
                    $saved_order[$mt]['ord'] = "asc";
                }
                elseif(empty($saved_order[$mt])){
                    $saved_order[$mt]['col'] = "medication";
                    $saved_order[$mt]['ord'] = "asc";
                }
            }
            
        } else{
            foreach($medication_blocks as $k=>$mt){
                if(empty($saved_order[$mt])){
                    $saved_order[$mt]['col'] = "medication";
                    $saved_order[$mt]['ord'] = "asc";
                }
            }
        }
        //---
        
        $saved_order = !empty($client_medi_sort) ? $client_medi_sort : "medication";
        
        if($user_overwrite_medi_sort_option != '0'){
            $uomso = Doctrine_Query::create()
            ->select('*')
            ->from('UserSettingsMediSort')
            ->Where('clientid = ?', $clientid)
            ->orderBy('create_date DESC')
            ->limit(1);
            $uomso_arr = $uomso->fetchArray();
            
            if(!empty($uomso_arr)){
                $saved_order = !empty($uomso_arr[0]['sort_column'] ) ? $uomso_arr[0]['sort_column'] : 'medication';//Ancuta 17.09.2020-- Issue if empty
            }
        }
        
        $sort_oorder = 'SORT_ASC';
        if(!empty($last_sort_order[0][1]) && $last_sort_order[0][1] == 'desc'){
            $sort_oorder = 'SORT_DESC';
        }
/*         foreach($patient_medication as $keyipid => $vals){            
            $keys = array_column($vals, $saved_order);
            array_multisort($keys, $sort_oorder, $vals);
            $patient_medication[$keyipid] = $vals;
        } */
        //.
        //ISPC-2826 Lore 19.02.2021 
        foreach($patient_medication as $keyipid => $vals){
            if(!empty($last_sort_order[0][1]) && $last_sort_order[0][1] == 'desc'){
                $medications_array_sorted[$keyipid] = $this->array_sort($vals, $saved_order, SORT_DESC);
            } else {
                $medications_array_sorted[$keyipid] = $this->array_sort($vals, $saved_order, SORT_ASC);
            }
        }
        if(!empty($medications_array_sorted)){
            $patient_medication = array();
            $patient_medication = $medications_array_sorted;
        }
        //.        
        return $patient_medication;
    }

    /*
     *  ISPC-2826 Lore 19.02.2021 
     */
    private function array_sort($array, $on = NULL, $order = SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();
        if(count($array) > 0)
        {
            foreach($array as $k => $v)
            {
                if(is_array($v))
                {
                    foreach($v as $k2 => $v2)
                    {
                        if($k2 == $on)
                        {
                            if($on == 'birthd' || $on == 'admissiondate'  ||  $on == 'medication_change')
                            {
                                
                                if($on == 'birthdyears')
                                {
                                    $v2 = substr($v2, 0, 10);
                                }
                                $sortable_array[$k] = strtotime($v2);
                            }
                            elseif($on == 'epid')
                            {
                                $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
                            }
                            elseif($on == 'percentage')
                            {
                                $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                            }
                            else
                            {
                                $sortable_array[$k] = ucfirst($v2);
                            }
                        }
                    }
                }
                else
                {
                    if($on == 'birthd' || $on == 'admissiondate'  ||  $on == 'medication_change')
                    {
                        if($on == 'birthdyears')
                        {
                            $v = substr($v, 0, 10);
                        }
                        $sortable_array[$k] = strtotime($v);
                    }
                    elseif($on == 'epid' || $on == 'percentage')
                    {
                        $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
                    }
                    elseif($on == 'percentage')
                    {
                        $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                    }
                    else
                    {
                        $sortable_array[$k] = ucfirst($v);
                    }
                }
            }
            switch($order)
            {
                case SORT_ASC:
                    $sortable_array = Pms_CommonData::a_sort($sortable_array);
                    break;
                    
                case SORT_DESC:
                    $sortable_array = Pms_CommonData::ar_sort($sortable_array);
                    
                    break;
            }
            
            foreach($sortable_array as $k => $v)
            {
                $new_array[$k] = $array[$k];
            }
        }
        
        return $new_array;
    }
	
    //ISPC-2517 - add button- allow bulk - gell all data
    public function get_dosage_interaction_medication($ipids,$clientid=0,$period=false, $type = 'actual')
    {
        if(!is_array($ipids))
        {
            $ipids = array($ipids);
        }
        else
        {
            $ipids = $ipids;
        }
        
        
        $modules = new Modules();
        if($modules->checkModulePrivileges("111", $clientid))//Medication acknowledge
        {
            $acknowledge_func = '1';
            
            // Get declined data
            $declined_ids2ipids = PatientDrugPlanAlt::get_declined_patients_drugplan_alt($ipids);
            
            foreach($ipids as $kd=>$ipidd)
            {
                foreach($declined_ids2ipids[$ipidd] as $kdd=>$declined_ids)
                {
                    $declined[] = $declined_ids;
                }
                
            }
            
            if(empty($declined)){
                $declined[] = "XXXXXXXX";
            }
            
            //get non approved data
            $non_approved = PatientDrugPlanAlt::get_patients_drugplan_alt($ipids,false,false,true);
            
            foreach($ipids as $k=>$ipid)
            {
                foreach($non_approved[$ipid]['change'] as $drugplan_id =>$not_approved)
                {
                    $not_approved_ids[] = $not_approved['drugplan_id'];
                    
                    if($not_approved['status'] == "new" && $not_approved['approved'] == "0"){
                        $newly_not_approved[] = $not_approved['drugplan_id'];;
                    }
                }
            }
            
            if(empty($not_approved_ids)){
                $not_approved_ids[] = "XXXXXXXX";
            }
            if(empty($newly_not_approved)){
                $newly_not_approved[] = "XXXXXXXX";
            }
        }
        else
        {
            $acknowledge_func = '0';
        }
        
        $drugs = Doctrine_Query::create()
        ->select("*")
        ->from('PatientDrugPlan')
        ->whereIn('ipid', $ipids)
        ->andWhere("isdelete = '0'");
        if($period){
            $drugs->andWhere('DATE(medication_change) BETWEEN DATE("' . $period['start'] . '") AND DATE("' . $period['end'] . '") OR DATE(change_date) BETWEEN DATE("' . $period['start'] . '") AND DATE("' . $period['end'] . '") OR DATE(create_date) BETWEEN DATE("' . $current_period['start'] . '") AND DATE("' . $current_period['end'] . '")');
        }

        if($type == "isbedarfs"){
            $drugs->andWhere("isbedarfs = '1'");
        }
        elseif($type == "iscrisis"){
            $drugs->andWhere("iscrisis = '1'");
        }
        elseif($type == "isivmed"){
            $drugs->andWhere("isivmed = '1'");
        }
        elseif($type == "isnutrition"){
            $drugs->andWhere("isnutrition = '1'");
        }
        elseif($type == "actual"){
            $drugs->andWhere("isbedarfs = '0'")
            ->andWhere("isivmed = 0")
            ->andWhere("iscrisis = 0")
            ->andWhere("isschmerzpumpe = 0")
            ->andWhere("treatment_care = 0")
            ->andWhere("isnutrition = 0")
            ->andWhere("ispumpe = 0") //ISPC-2914,Elena,10.05.2021
            ->andWhere("isintubated = 0")
            ->andWhere('scheduled = "0"');
        }
        if($acknowledge_func == "1")
        {
            $drugs->andWhereNotIn('id',$declined); // remove declined
            $drugs->andWhereNotIn('id',$newly_not_approved); // remove newly added - not approved
        }
        $drugs->orderBy("id ASC");
        $drugsarray = $drugs->fetchArray();
        
        $medi_masternutrition_ids = array();
        foreach($drugsarray as $key_drg => $v_drg)
        {
            if($v_drg['isnutrition'] == '1'){
                $medi_masternutrition_ids[] = $v_drg['medication_master_id'];
            } else{
                $master_medications_ids[] = $v_drg['medication_master_id'];
            }
            $drug_ids[] = $v_drg['id'];
        }
        
        if(empty($master_medications_ids)){
            $master_medications_ids[] = "99999999";
        }
        //get the data from master medications array
        $med = new Medication();
        $master_tr_array = $med->master_medications_get($master_medications_ids, false);
        
        
        $nutrition_med = array();
        if(!empty($medi_masternutrition_ids)){
            $med_nutrition = new Nutrition();
            $medarr_nutrition = $med_nutrition->getMedicationNutritionById($medi_masternutrition_ids); // so changed into this to fetch full row
            
            if( ! empty($medarr_nutrition)){
                foreach($medarr_nutrition as $k=>$mn){
                    $nutrition_med[$mn['id']]  = $mn;
                }
            }
        }
        
        if(!empty($drug_ids)){
            $drugs_extra = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlanExtra')
            ->whereIn('ipid', $ipids)
            ->andWhere("isdelete = '0'")
            ->andWhereIn("drugplan_id",$drug_ids);
            $drugsarray_extra = $drugs_extra->fetchArray();
            
            if(!empty($drugsarray_extra)){
                
                // get details for indication
                $indications_array = MedicationIndications::client_medication_indications($clientid);
                
                foreach ($indications_array as $ki => $ind_value) {
                    $indication[$ind_value['id']]['name'] = $ind_value['indication'];
                    $indication[$ind_value['id']]['color'] = $ind_value['indication_color'];
                }
                
                $extra_info = array();
                foreach($drugsarray_extra as $k=>$extra_data){
                    $drug_indication[$extra_data['drugplan_id']] = $indication[$extra_data['indication']];
                    $extra_info[$extra_data['drugplan_id']] = $extra_data;
                }
                
                
                
                
                
                // get details for unit
                $units_array = MedicationUnit::client_medication_unit($clientid);
                foreach ($units_array as $ku => $unit_value) {
                    $unit[$unit_value['id']] = $unit_value['unit'];
                }
                
                // get details for dosage_form
                $dosage_form_array = MedicationDosageform::client_medication_dosage_form($clientid, true);// added second param true to includ extra for custom options from sets
                foreach ($dosage_form_array as $ku => $ds_value) {
                    $dosage_form[$ds_value['id']] = $ds_value['dosage_form'];
                }
                
            }
            
            
            $drugplan_dosages = Doctrine_Query::create()
            ->select('*')
            ->from('PatientDrugPlanDosage')
            ->whereIn('ipid', $ipids)
            ->andWhere("isdelete = '0'")
            ->andWhereIn("drugplan_id",$drug_ids)
            ->fetchArray();
            
            $dosage_info = array();
            foreach($drugplan_dosages as $k=>$dosage_data){
                $dosage_info[$dosage_data['drugplan_id']][] = $dosage_data;
            }
        }
        
        // get given information 
        
        $saved_dosage_interacion_array = array();
        $saved_dosage_interacion_array = PatientDrugPlanDosageGivenTable::findAllByIpids($ipids);
        
        $saved_dosage_interacion = array();
        $saved_dosage_interacion_NoTime = array();
        foreach($saved_dosage_interacion_array as $k=>$dsg_interaction){
            $saved_dosage_interacion[$dsg_interaction['ipid']][$dsg_interaction['drugplan_id']][date('Y-m-d',strtotime($dsg_interaction['dosage_date']))][$dsg_interaction['dosage_time_interval']] = $dsg_interaction;
            
            $saved_dosage_interacion_NoTime[$dsg_interaction['ipid']][$dsg_interaction['drugplan_id']][date('Y-m-d',strtotime($dsg_interaction['dosage_date']))] = $dsg_interaction;
        }
        
        
        
        foreach($drugsarray as $key => $drugp)
        {
            
            if($drugp['days_interval'])
            {
                $drugp['days_interval'] =  $drugp['days_interval'];
            }
            $patient_medication[$drugp['ipid']][$drugp['id']]['id'] = $drugp['id'] ;
            if($drugp['isnutrition'] == '1'){
                $patient_medication[$drugp['ipid']][$drugp['id']]['medication_name'] = $nutrition_med[$drugp['medication_master_id']]['name']  ;
                $patient_medication[$drugp['ipid']][$drugp['id']]['name'] = $nutrition_med[$drugp['medication_master_id']]['name']  ;
            } else{
                $patient_medication[$drugp['ipid']][$drugp['id']]['medication_name'] = $master_tr_array[$drugp['medication_master_id']]  ;
                $patient_medication[$drugp['ipid']][$drugp['id']]['name'] = $master_tr_array[$drugp['medication_master_id']]  ;
            }
            $patient_medication[$drugp['ipid']][$drugp['id']]['category'] = $type;
            $patient_medication[$drugp['ipid']][$drugp['id']]['indications'] = $drug_indication[$drugp['id']] ;
            $patient_medication[$drugp['ipid']][$drugp['id']]['comments'] = $drugp['comments'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['date'] = $drugp['medication_change'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['days_interval'] =   $drugp['days_interval'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['due_date'] = $drugp['scheduled_date'];
//             $patient_medication[$drugp['ipid']][$drugp['id']]['full_data'] = $drugp;
            $patient_medication[$drugp['ipid']][$drugp['id']]['extra'] = $extra_info[$drugp['id']];
            $patient_medication[$drugp['ipid']][$drugp['id']]['drug'] = $extra_info[$drugp['id']]['drug'];
            $patient_medication[$drugp['ipid']][$drugp['id']]['unit_name'] = $unit[$extra_info[$drugp['id']]['unit']];
            $patient_medication[$drugp['ipid']][$drugp['id']]['dosage_form_name'] = $dosage_form[$extra_info[$drugp['id']]['dosage_form']];
            if($dosage_info[$drugp['id']]){
                $patient_medication[$drugp['ipid']][$drugp['id']]['dosage'] = $dosage_info[$drugp['id']];
                foreach($dosage_info[$drugp['id']] as $k=>$ddo) {
                    $patient_medication[$drugp['ipid']][$ddo['drugplan_id']]['drugplan_dosage'][$ddo['dosage_time_interval']] = $ddo;
                }
            } else{
                $patient_medication[$drugp['ipid']][$drugp['id']]['dosage'] = $drugp['dosage'];
            }
            
            //dosage_interaction
            $patient_medication[$drugp['ipid']][$drugp['id']]['givenInfo_time'] = $saved_dosage_interacion[$drugp['ipid']][$drugp['id']];
            $patient_medication[$drugp['ipid']][$drugp['id']]['givenInfo_Notime'] = $saved_dosage_interacion_NoTime[$drugp['ipid']][$drugp['id']];
        }
        
        return $patient_medication;
    }
    
    
	}

?>