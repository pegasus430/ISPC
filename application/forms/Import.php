<?php

	require_once("Pms/Form.php");

	class Application_Form_Import extends Pms_Form {

		public function import_handler($csv_data, $post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			//convert ddmmyyyy 2 yyyymmdd
			$splited_str = str_split($csv_data[0]['16'], '2');
			$splited_data[0] = $splited_str[2].$splited_str[3];
			$splited_data[1] = $splited_str[1];
			$splited_data[2] = $splited_str[0];
			$csv_data[0]['16'] = implode('', $splited_data);
			
			foreach($post['import_type'] as $k_csv_row => $import_type)
			{
				if($import_type == '1') //update patient (req: target patient, all csv_row selected data)
				{
					$this->import_update_patient($k_csv_row, $clientid, $userid, $post, $csv_data);
				}
				else if($import_type == '2') //new patient (req: all csv_row data)
				{
					$this->import_new_patient($k_csv_row, $clientid, $userid, $post, $csv_data);
				}
			}

			$import_session = new Zend_Session_Namespace('importSession');
			$import_session->userid = '';
			$import_session->form_data = '';
		}

		private function import_new_patient($csv_row, $clientid, $userid, $post, $csv_data)
		{
			$imported_csv_data = $csv_data[$csv_row];

			//revert and properly format d.m.Y in Y-m-d
//			$bd_date = implode('-', array_reverse(explode(".", $imported_csv_data['10'])));
			$bd_date = date('Y-m-d', strtotime($imported_csv_data['10']));
			$card_expiration_date = date('Y-m-d', strtotime($imported_csv_data['15']));
			$curent_dt = date('Y-m-d H:i:s', time());
			$Tr = new Zend_View_Helper_Translate();

			//generate ipid and epid
			$ipid = Pms_Uuid::GenerateIpid();

			$epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
			$epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];

			//insert patient
			$cust = new PatientMaster();
			$cust->ipid = $ipid;
			$cust->recording_date = $curent_dt;
			$cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['7']);
			$cust->middle_name = Pms_CommonData::aesEncrypt($imported_csv_data['8']);
			$cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
			$cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['11']);
			$cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['13']);
			$cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['14']);
			$cust->title = Pms_CommonData::aesEncrypt($imported_csv_data['6']);
			$cust->admission_date = $curent_dt;
			$cust->birthd = $bd_date;
			$cust->isstandby = '1';
			$cust->save();

			//insert epid-ipid
			$res = new EpidIpidMapping();
			$res->clientid = $clientid;
			$res->ipid = $ipid;
			$res->epid = $epid;
			$res->epid_chars = $epid_parts['epid_chars'];
			$res->epid_num = $epid_parts['epid_num'];
			$res->save();

			//assign patient to the current user
			$assign = new PatientQpaMapping();
			$assign->epid = $epid;
			$assign->userid = $userid;
			$assign->clientid = $clientid;
			$assign->assign_date = $curent_dt;
			$assign->save();

			//patient visibility for curent user
			$visibility = new PatientUsers();
			$visibility->clientid = $clientid;
			$visibility->ipid = $ipid;
			$visibility->userid = $userid;
			$visibility->create_date = $curent_dt;
			$visibility->save();

			//insert in patient case
			$case = new PatientCase();
			$case->admission_date = $curent_dt;
			$case->epid = $epid;
			$case->clientid = $clientid;
			$case->save();


			//insert health insurance company data
			$hi = new HealthInsurance();
			$hi->clientid = $clientid;
			$hi->name = $imported_csv_data['0'];
			$hi->extra = '1';
			$hi->save();

			$hi_id = $hi->id;

			//insert patient health insurance data
			$patient_hi = new PatientHealthInsurance();
			$patient_hi->ipid = $ipid;
			$patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data[0]);
			$patient_hi->companyid = $hi_id;
			$patient_hi->kvk_no = $imported_csv_data[1];
			$patient_hi->insurance_no = $imported_csv_data[2];
			$patient_hi->save();


			//insert data in terminal extra data
			$terminal = new TerminalExtra();
			$terminal->ipid = $ipid;
			$terminal->wop = $imported_csv_data['3'];
			$terminal->rsa = $imported_csv_data['4'];
			$terminal->legal_family = $imported_csv_data['5'];
			$terminal->country = $imported_csv_data['12'];
			$terminal->card_expiration_date =  date('Y-m-d H:i:s', strtotime($card_expiration_date));
			$terminal->card_read_date = date('Y-m-d', strtotime($imported_csv_data['16']));
			$terminal->approve_number = $imported_csv_data['17'];
			$terminal->save();
			//write first entry in patient course
			$course = new PatientCourse();
			$course->ipid = $ipid;
			$course->course_date = $curent_dt;
			$course->course_type = Pms_CommonData::aesEncrypt('K');
			$course->course_title = Pms_CommonData::aesEncrypt($Tr->translate('patient_import_new_verlauf'));
			$course->done_date = $curent_dt;
			$course->user_id = $userid;
			$course->tabname = Pms_CommonData::aesEncrypt('terminal_import_new');
			$course->save();
		}

		private function import_update_patient($csv_row, $clientid, $userid, $post, $csv_data)
		{
			//every field is updated only if csv[csv_row][field] == post[csv_row][field]
			$Tr = new Zend_View_Helper_Translate();
			$patientid = Pms_Uuid::decrypt($post['target_patient'][$csv_row] . '=');
			$ipid = Pms_CommonData::getIpid($patientid);
			$csv_labels = Pms_CommonData::terminal_import_csv_labels();
			$curent_dt = date('Y-m-d H:i:s', time());

			$updated_fields = array();

			$requested_data = $post['import_value'][$csv_row];

			//update PatientMaster
			//req value keys [6,7,8,9,10,11,13,14]
			$patient_master_keys = array('6', '7', '8', '9', '10', '11', '13', '14');

			$init_pm = false;

			foreach($patient_master_keys as $k_key => $v_key)
			{
				if(strlen(trim(rtrim($requested_data[$v_key]))) != '0' && strtolower(trim(rtrim($requested_data[$v_key]))) == strtolower(trim(rtrim($csv_data[$csv_row][$v_key]))))
				{
					$init_pm = true;
				}
			}

			if($init_pm)
			{
				$pm = Doctrine::getTable('PatientMaster')->find($patientid);

				if(strlen(trim(rtrim($requested_data['6']))) != '0' && strtolower(trim(rtrim($requested_data['6']))) == strtolower(trim(rtrim($csv_data[$csv_row]['6']))))
				{
					$pm->title = Pms_CommonData::aesEncrypt($requested_data['6']);
					$updated_fields[] = '6';
				}

				if(strlen(trim(rtrim($requested_data['7']))) != '0' && strtolower(trim(rtrim($requested_data['7']))) == strtolower(trim(rtrim($csv_data[$csv_row]['7']))))
				{
					$pm->first_name = Pms_CommonData::aesEncrypt($requested_data['7']);
					$updated_fields[] = '7';
				}

				if(strlen(trim(rtrim($requested_data['8']))) != '0' && strtolower(trim(rtrim($requested_data['8']))) == strtolower(trim(rtrim($csv_data[$csv_row]['8']))))
				{
					$pm->middle_name = Pms_CommonData::aesEncrypt($requested_data['8']);
					$updated_fields[] = '8';
				}

				if(strlen(trim(rtrim($requested_data['9']))) != '0' && strtolower(trim(rtrim($requested_data['9']))) == strtolower(trim(rtrim($csv_data[$csv_row]['9']))))
				{
					$pm->last_name = Pms_CommonData::aesEncrypt($requested_data['9']);
					$updated_fields[] = '9';
				}



				if(strlen(trim(rtrim($requested_data['10']))) != '0' && strtolower(trim(rtrim($requested_data['10']))) == strtolower(trim(rtrim($csv_data[$csv_row]['10']))))
				{
//				$pm->birthd = implode('-', array_reverse(explode('.', $requested_data['10'])));
					$pm->birthd = date('Y-m-d', strtotime($requested_data['10']));
					$updated_fields[] = '10';
				}

				if(strlen(trim(rtrim($requested_data['11']))) != '0' && strtolower(trim(rtrim($requested_data['11']))) == strtolower(trim(rtrim($csv_data[$csv_row]['11']))))
				{
					$pm->street1 = Pms_CommonData::aesEncrypt($requested_data['11']);
					$updated_fields[] = '11';
				}

				if(strlen(trim(rtrim($requested_data['13']))) != '0' && strtolower(trim(rtrim($requested_data['13']))) == strtolower(trim(rtrim($csv_data[$csv_row]['13']))))
				{
					$pm->zip = Pms_CommonData::aesEncrypt($requested_data['13']);
					$updated_fields[] = '13';
				}

				if(strlen(trim(rtrim($requested_data['14']))) != '0' && strtolower(trim(rtrim($requested_data['14']))) == strtolower(trim(rtrim($csv_data[$csv_row]['14']))))
				{
					$pm->city = Pms_CommonData::aesEncrypt($requested_data['14']);
					$updated_fields[] = '14';
				}

				$pm->save();
			}

			//insert health insurance
			//update/insert patient health insurance
			//req value keys [0,1,2]
			$hi_keys = array('0', '1', '2');

			$init_hi = false;
			foreach($hi_keys as $k_key => $v_key)
			{
				if(strlen(trim(rtrim($requested_data[$v_key]))) != '0' && strtolower(trim(rtrim($requested_data[$v_key]))) == strtolower(trim(rtrim($csv_data[$csv_row][$v_key]))))
				{
					$init_hi = true;
				}
			}

			if($init_hi)
			{
				if(strlen(trim(rtrim($requested_data['0']))) != '0' && strtolower(trim(rtrim($requested_data['0']))) == strtolower(trim(rtrim($csv_data[$csv_row]['0']))))
				{
					//insert health insurance company data
					$hi = new HealthInsurance();
					$hi->clientid = $clientid;
					$hi->name = $requested_data['0'];
					$hi->extra = '1';
					$hi->save();
				}

				$hi_id = $hi->id;

				//search for an existing patienthealthinsurance
				$phi = new PatientHealthInsurance();
				$search_phi = $phi->getPatientHealthInsurance($ipid);

				if($search_phi)
				{
					//update existing patient health insurance data
					$phi_update = Doctrine::getTable('PatientHealthInsurance')->findOneByIpid($ipid);
					if(strlen(trim(rtrim($requested_data['0']))) != '0' && strtolower(trim(rtrim($requested_data['0']))) == strtolower(trim(rtrim($csv_data[$csv_row]['0']))))
					{
						$phi_update->company_name = Pms_CommonData::aesEncrypt($requested_data['0']);
						$phi_update->companyid = $hi_id;
						$updated_fields[] = '0';
					}

					if(strlen(trim(rtrim($requested_data['1']))) != '0' && strtolower(trim(rtrim($requested_data['1']))) == strtolower(trim(rtrim($csv_data[$csv_row]['1']))))
					{
						$phi_update->kvk_no = $requested_data['1'];
						$updated_fields[] = '1';
					}

					if(strlen(trim(rtrim($requested_data['2']))) != '0' && strtolower(trim(rtrim($requested_data['2']))) == strtolower(trim(rtrim($csv_data[$csv_row]['2']))))
					{
						$phi_update->insurance_no = $requested_data['2'];
						$updated_fields[] = '2';
					}

					$phi_update->save();
				}
				else
				{
					//insert patient health insurance data
					$patient_hi = new PatientHealthInsurance();
					$patient_hi->ipid = $ipid;
					$patient_hi->company_name = Pms_CommonData::aesEncrypt($requested_data[0]);
					$patient_hi->companyid = $hi_id;
					$patient_hi->kvk_no = $requested_data[1];
					$patient_hi->insurance_no = $requested_data[2];
					$patient_hi->save();

					$updated_fields = array_merge($updated_fields, $hi_keys);
				}
			}


			//update/insert TerminalExtra
			//req value keys [3,4,5,15]
			$extra_data_keys = array('3', '4', '5', '12', '15', '16', '17');

			$init_extra = false;

			foreach($extra_data_keys as $k_key => $v_key)
			{
				if(strlen(trim(rtrim($requested_data[$v_key]))) != '0' && strtolower(trim(rtrim($requested_data[$v_key]))) == strtolower(trim(rtrim($csv_data[$csv_row][$v_key]))))
				{
					$init_extra = true;
				}
			}

			if($init_extra)
			{

				$extra_data = Doctrine::getTable('TerminalExtra')->findOneByIpid($ipid);

				if($extra_data)
				{
					$extra = Doctrine::getTable('TerminalExtra')->findOneByIpid($ipid);

					if(strlen(trim(rtrim($requested_data['3']))) != '0' && strtolower(trim(rtrim($requested_data['3']))) == strtolower(trim(rtrim($csv_data[$csv_row]['3']))))
					{
						$extra->wop = $requested_data['3'];
						$updated_fields[] = '3';
					}

					if(strlen(trim(rtrim($requested_data['4']))) != '0' && strtolower(trim(rtrim($requested_data['4']))) == strtolower(trim(rtrim($csv_data[$csv_row]['4']))))
					{
						$extra->rsa = $requested_data['4'];
						$updated_fields[] = '4';
					}

					if(strlen(trim(rtrim($requested_data['5']))) != '0' && strtolower(trim(rtrim($requested_data['5']))) == strtolower(trim(rtrim($csv_data[$csv_row]['5']))))
					{
						$extra->legal_family = $requested_data['5'];
						$updated_fields[] = '4';
					}

					if(strlen(trim(rtrim($requested_data['12']))) != '0' && strtolower(trim(rtrim($requested_data['12']))) == strtolower(trim(rtrim($csv_data[$csv_row]['12']))))
					{
						$extra->country = $requested_data['12'];
						$updated_fields[] = '12';
					}

					if(strlen(trim(rtrim($requested_data['15']))) != '0' && strtolower(trim(rtrim($requested_data['15']))) == strtolower(trim(rtrim($csv_data[$csv_row]['15']))))
					{
//						$extra->card_expiration_date = implode('-', array_reverse(explode('.', $requested_data['15'])));
						$extra->card_expiration_date = date('Y-m-d', strtotime($requested_data['15']));
						$updated_fields[] = '15';
					}

					if(strlen(trim(rtrim($requested_data['16']))) != '0' && strtolower(trim(rtrim($requested_data['16']))) == strtolower(trim(rtrim($csv_data[$csv_row]['16']))))
					{
//						$extra->card_read_date = date('Y-m-d H:i:s', strtotime(implode('-', array_reverse(explode('.', $requested_data['16'])))));
						$extra->card_read_date = date('Y-m-d H:i:s', strtotime($requested_data['16']));
						$updated_fields[] = '16';
					}

					if(strlen(trim(rtrim($requested_data['17']))) != '0' && strtolower(trim(rtrim($requested_data['17']))) == strtolower(trim(rtrim($csv_data[$csv_row]['17']))))
					{
						$extra->approve_number = $requested_data['17'];
						$updated_fields[] = '17';
					}

					$extra->save();
				}
				else
				{
					//insert data in terminal extra data
					$terminal = new TerminalExtra();
					$terminal->ipid = $ipid;
					$terminal->wop = $requested_data['3'];
					$terminal->rsa = $requested_data['4'];
					$terminal->legal_family = $requested_data['5'];
					$terminal->country = $requested_data['12'];
//					$terminal->card_expiration_date = implode('-', array_reverse(explode('.', $requested_data['15'])));
					$terminal->card_expiration_date = date('Y-m-d', strtotime($requested_data['15']));
//					$terminal->card_read_date = date('Y-m-d H:i:s', strtotime(implode('-', array_reverse(explode('.', $requested_data['16'])))));
					$terminal->card_read_date = date('Y-m-d H:i:s', strtotime($requested_data['16']));
					$terminal->approve_number = $requested_data['17'];
					$terminal->save();

					$updated_fields = array_merge($updated_fields, $extra_data_keys);
				}
			}

			//write first entry in patient course
			$course = new PatientCourse();
			$course->ipid = $ipid;
			$course->course_date = $curent_dt;
			$course->course_type = Pms_CommonData::aesEncrypt('K');
			$course->course_title = Pms_CommonData::aesEncrypt($Tr->translate('patient_import_update_verlauf'));
			$course->done_date = $curent_dt;
			$course->user_id = $userid;
			$course->tabname = Pms_CommonData::aesEncrypt('terminal_import_edit');
			$course->save();
		}

		
		

		public function patient_import_handler($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    

		    // get diagnosis types
		    $dg = new DiagnosisType();
		    $abb2 = "'HD'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    $comma = ",";
		    $typeid = "'0'";
		    foreach($ddarr2 as $key => $valdia)
		    {
		        $type_id_array[] = $valdia['id'];
		    }
		    $main_diagnosis_type = $type_id_array[0]; 
		    
		    // get discharge locations and methods
		    $discharge_locations[$clientid]= array();
		    $discharge_methods[$clientid]= array();
		    if($clientid == "177"){ //WL_Paderborn
		        
		        $discharge_locations[$clientid] = array(
		            "Pflegeheim"=>"2262",
		            "Hospiz"=>"2258",
		            "Palliativstation"=>"2261",
		            "zu Hause"=>"2260",
		            "Klinik, sonst."=>"2306",
		            "unbekannt"=>"2307"
		        );
		        
		        $discharge_methods[$clientid] = array(
		            "verstorben"=>"1504",
		            "kein Kontakt"=>"1580",
		            "kein weiterer Kontakt"=>"1585",
		            "nicht akut"=>"1581",
		            "nicht akut-palliativ"=>"1582",
		            "siehe Hinweise"=>"1583",
		            "unbekannt verzogen"=>"1584",
		            "verzogen"=>"1584",
		            
		        );
		    }
		    
		    if($clientid == "178"){ //WL_Hoexter
		        
		        $discharge_locations[$clientid] = array(
		            "Pflegeheim"=>"2268",
		            "Hospiz"=>"2264",
		            "Klinik, sonst."=>"2309",
		            "KZP"=>"2309",
		            "Palliativstation"=>"2267",
		            "Palliativstation Brakel"=>"2267",
		            "zu Hause"=>"2266",
		            "unbekannt"=>"2310"
		        );
		        
		        $discharge_methods[$clientid] = array(
		            "verstorben"=>"1516",
		            "kein Kontakt"=>"1587",
		            "kein weiterer Kontakt"=>"1588",
		            "kurativ"=>"1589",
		            "verzogen"=>"1590",
		        );
		        
		    }
		    
		    if($clientid == "179"){ //WL_Delbruek
		        
		        $discharge_locations[$clientid] = array(
		            "Pflegeheim"=>"2273",
		            "Hospiz"=>"2269",
		            "Klinik, sonst."=>"2313",
		            "Palliativstation"=>"2272",
		            "zu Hause"=>"2271",
		            "unbekannt"=>"2311"
		        );
		        
		        $discharge_methods[$clientid] = array(
		            "verstorben"=>"1524"
		        );
		        
		    }
		    
		    $import_client = array("177","178","179");
		    
		    // get health insurance
				$health = Doctrine_Query::create()
				->select('*')
				->from('HealthInsurance')
				->where('isdelete = ?', 0)
				->andWhere('extra = 0')
				->andWhereIn("clientid ",$import_client )
				->andWhere("onlyclients = '1'");
			$healtharray = $health->fetchArray();
			
		    foreach($healtharray as $kh=>$hi_data){
		        if($hi_data['import_hi']){
    		      $hi_data_arr[$hi_data['clientid']][$hi_data['import_hi']] = $hi_data;     
		        }
		    }
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
    	            $this->import_patient($k_csv_row, $clientid, $userid,  $csv_data,$main_diagnosis_type,$discharge_locations,$discharge_methods,$hi_data_arr);
		        }
		    }
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient($csv_row, $clientid, $userid,  $csv_data,$main_diagnosis_type,$discharge_locations,$discharge_methods,$health_insurance_data )
		{
		    $imported_csv_data = $csv_data[$csv_row];
		
		    $gender = array("m"=>"1","w"=>"2");
		    //revert and properly format d.m.Y in Y-m-d
		    //			$bd_date = implode('-', array_reverse(explode(".", $imported_csv_data['10'])));
		    $bd_date = date('Y-m-d', strtotime($imported_csv_data['10']));
		    $admission_date= date('Y-m-d H:i:00', strtotime($imported_csv_data['28']));
		    $discharge_date= date('Y-m-d H:i:00', strtotime($imported_csv_data['29']));
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $Tr = new Zend_View_Helper_Translate();

		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['13']);
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['12']);
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['17']);
		    $cust->street2 = Pms_CommonData::aesEncrypt($imported_csv_data['18']);
		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['19']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['16']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['15']);
		    $cust->admission_date = $admission_date;
		    $cust->birthd = $bd_date;
		    
		    if($gender[$imported_csv_data['11']]){
    		    $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['11']]);
		    } else {
    		    $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    
		    
		    if(strlen($imported_csv_data['29']) > 0){
    		    $cust->isdischarged = '1';
		    }
		    
		    
    		//$cust->isadminvisible= '1';
   		    
		    $cust->import_pat = $imported_csv_data['3'];
		    $cust->import_fd = $imported_csv_data['2'];
		    $cust->import_hi = $imported_csv_data['20'];
   		    
   		    
		    $cust->save();
		
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
		    
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		    
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		    
		    if(strlen($imported_csv_data['29']) > 0){
		        
		        $pd = new PatientDischarge();
		        $pd->ipid = $ipid;
		        $pd->discharge_date= $discharge_date;

		        if (strlen($imported_csv_data['30'])>0){
		          $pd->discharge_location = $discharge_locations[$clientid][trim($imported_csv_data['30'])];
		        }
		        if (strlen($imported_csv_data['31'])>0){
		          $pd->discharge_method = $discharge_methods[$clientid][trim($imported_csv_data['31'])];
		        }
		        $pd->save();
		        
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $discharge_date;
		        $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		        
		        
		    }
		    
		    //assign patient to the current user
// 		    $assign = new PatientQpaMapping();
// 		    $assign->epid = $epid;
// 		    $assign->userid = $userid;
// 		    $assign->clientid = $clientid;
// 		    $assign->assign_date = $curent_dt;
// 		    $assign->save();
		
		    //patient visibility for curent user
// 		    $visibility = new PatientUsers();
// 		    $visibility->clientid = $clientid;
// 		    $visibility->ipid = $ipid;
// 		    $visibility->userid = $userid;
// 		    $visibility->create_date = $curent_dt;
// 		    $visibility->save();
		
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $curent_dt;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
		    if(strlen($imported_csv_data['20']) > 0){
		
    		    if($health_insurance_data[$clientid][$imported_csv_data['20']])
    		    {
    		        //insert patient health insurance data
    		        $patient_hi = new PatientHealthInsurance();
    		        $patient_hi->ipid = $ipid;
    		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($health_insurance_data[$clientid][$imported_csv_data['20']]['name']);
    		        $patient_hi->companyid = $health_insurance_data[$clientid][$imported_csv_data['20']]['id'];
    		        $patient_hi->insurance_no = $imported_csv_data['23'];
    		        $patient_hi->save();
    		    }
		    }
		    
		    

		    if(strlen($imported_csv_data['24']) > 0){
		        
    		    $diagno_free = new DiagnosisText();
    		    $diagno_free->clientid = $clientid;
    		    $diagno_free->icd_primary = $imported_csv_data['24'];
    		    $diagno_free->free_name = " ";
    		    $diagno_free->save();
    		    $free_diagno_id = $diagno_free->id;
    		    
    		    $diagno = new PatientDiagnosis();
    		    $diagno->ipid = $ipid;
    		    $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
    		    $diagno->diagnosis_type_id = $main_diagnosis_type ;
    		    $diagno->diagnosis_id = $free_diagno_id;
    		    $diagno->icd_id = "0";
    		    $diagno->save();
		    }
		    
		    
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert 01.02.2016');
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_wl_clients_import');
		    $course->save();
		    
		    
		    
		}
		
		
/*--------------------------------------------------*/
/*--------------------------------------------------*/
/*--------------------------------------------------*/
/*--------------------------------------------------*/
		
		public function patient_import_handler_wlk($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;

		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
    	            $this->import_patient_wlk($k_csv_row, $clientid, $userid,  $csv_data);
		        }
		    }
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_wlk($csv_row, $clientid, $userid,  $csv_data)
		{
		    $Tr = new Zend_View_Helper_Translate();

		    $imported_csv_data = $csv_data[$csv_row];
		    
		    $gender = array("maennlich"=>"1","weiblich"=>"2");
		    
		    if(strlen($imported_csv_data['3']) > 0 ){
	       	    $bd_date = date('Y-m-d', strtotime($imported_csv_data['3']));
		    } 
		    
		    
   		    $curent_dt = date('Y-m-d H:i:s', time());
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['1']);
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['2']);
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['4']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['6']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['7']);
		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
// 		    $cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
		    $cust->email= Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->birthd = $bd_date;
		    
		    
// 		    $cust->admission_date = $curent_dt;
            if($imported_csv_data['30']){
    		    $post_admission_date = date('Y-m-d', strtotime($imported_csv_data['30'])); //
    		    $cust->admission_date =date('Y-m-d 00:00:00', strtotime($imported_csv_data['30'])); //
    		    $admission_date = date('Y-m-d 00:00:00', strtotime($imported_csv_data['30'])); //
    		    
            } else {
//     		    $cust->admission_date = $curent_dt;
    		    $cust->admission_date = date('1970-01-01 00:00:00'); //
            }
		    
		    
		    if($gender[$imported_csv_data['13']]){
    		    $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['13']]);
		    } else {
    		    $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    
		    
		    $cust->import_pat = $imported_csv_data['0'];
		    $cust->isadminvisible= '1';
		    
		    
		    if(strlen($imported_csv_data['31']) > 0){
    		    $cust->isdischarged = '1';
    		    $cust->traffic_status= '0';
		    }
		    
		    $cust->save();
		
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
 
		    
	
		    
		      
		    
		    // health insurance
		    if(strlen($imported_csv_data['21']) > 0){ // KK_Krankenversicherung_name
		        // insert in health insurance master
		        $hinsu = new HealthInsurance();
		        $hinsu->clientid = $clientid;
		        $hinsu->name = $imported_csv_data['21'];//KK_Krankenversicherung_name
// 		        $hinsu->name2 = $imported_csv_data['19'];//KK_Krankenversicherung_name2
// 		        $hinsu->zip = $imported_csv_data['21'];//KK_PLZ
// 		        $hinsu->city = $imported_csv_data['22'];//KK_Stadt
// 		        $hinsu->phone = $imported_csv_data['23'];//KK_Telefon 1
// 		        $hinsu->phone2 = $imported_csv_data['24'];//KK_Telefon 2
// 		        $hinsu->phonefax  = $imported_csv_data['25'];//KK_Fax
// 		        $hinsu->email = $imported_csv_data['26'];//KK_Email
// 		        $hinsu->iknumber = $imported_csv_data['15'];//KK_KrankenIK
		        $hinsu->valid_from = date("Y-m-d", time());
		        $hinsu->extra = 1;
		        $hinsu->onlyclients = '0';
		        $hinsu->save();
		        $company_id =$hinsu->id; 
		        
		        // insert in patient
		        $patient_hi = new PatientHealthInsurance();
		        $patient_hi->ipid = $ipid;
		        $patient_hi->companyid = $company_id;
// 		        $patient_hi->institutskennzeichen = $imported_csv_data['15'];//KK_KrankenIK
// 		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($imported_csv_data['16']);//KK_KV Status
// 		        $patient_hi->insurance_no = $imported_csv_data['17'];//KK_Versichertennr
		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['21']);//KK_Krankenversicherung_name
// 		        $patient_hi->ins_contactperson = Pms_CommonData::aesEncrypt($imported_csv_data['20']); //KK_Ansprechpartner
// 		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($imported_csv_data['21']);//KK_PLZ
// 		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($imported_csv_data['22']);//KK_Stadt
// 		        $patient_hi->ins_phone = Pms_CommonData::aesEncrypt($imported_csv_data['23']);//KK_Telefon 1
// 		        $patient_hi->ins_phone2 = Pms_CommonData::aesEncrypt($imported_csv_data['24']);//KK_Telefon 2
// 		        $patient_hi->ins_phonefax = Pms_CommonData::aesEncrypt($imported_csv_data['25']); // KK_Fax
// 		        $patient_hi->ins_email = Pms_CommonData::aesEncrypt($imported_csv_data['26']);//KK_Email
		        $patient_hi->insurance_no = $imported_csv_data['22'];//KK_Email
		        $patient_hi->save();
		    }

 
 
		    
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		    
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		    
		    if(strlen($imported_csv_data['31']) > 0){
		        
		        $discharge_date = date('Y-m-d 00:00:10', strtotime($imported_csv_data['31'])); //
		        
		        $pd = new PatientDischarge();
		        $pd->ipid = $ipid;
		        $pd->discharge_date = $discharge_date;

// 		        if (strlen($imported_csv_data['30'])>0){
// 		          $pd->discharge_location = $discharge_locations[$clientid][trim($imported_csv_data['30'])];
// 		        }
		        if (strlen($imported_csv_data['31'])>0){
		          $pd->discharge_method = "1715";
// 		          $pd->discharge_method = "1715";
// 		          $pd->discharge_method = "1670"; // for testing
// 		          $pd->discharge_method = "36"; // for testing
		        }
		        $pd->save();
		        
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $discharge_date;
		        $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		        
		        
		        // Patient discharge course - discharge date START
		        $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
		        $cust = new PatientCourse();
		        $cust->ipid = $ipid;
		        $cust->course_date = date("Y-m-d H:i:s", time());
		        $cust->course_type = Pms_CommonData::aesEncrypt("K");
		        $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
		        $cust->user_id = $userid;
		        $cust->save();
		        
		    }
		    
		
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
 
		    
 
		    
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert 16.12.2016');
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_wl_kinder_clients_import');
		    $course->save();
		    
		    
		    
		}
		
/*--------------------------------------------------*/
/*--------------------------------------------------*/
/*--------------------------------------------------*/
/*--------------------------------------------------*/
		
/*--------------------------------------------------*/
/*---------------WL II ----------------------*/
/*--------------------------------------------------*/
/*--------------------------------------------------*/
		
		public function patient_import_handler_wl_unna($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;

		    // get client locations
		    $locations = Locations::getLocations($clientid,0);
		    foreach($locations as $k=>$ldata){
		    	$location_master[$ldata['location']] = $ldata['id'];
		    }
		    
		    // get users
		    $usr = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where('clientid =?',$clientid);
		    $users_details_arr =$usr->fetchArray();
		    
		    foreach($users_details_arr as $k=>$udata){
		    	$client_users[$udata['username']] = $udata['id']; 
		    }
 
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
    	            $this->import_patient_wl_unna($k_csv_row, $clientid, $userid,  $csv_data,$client_users,$location_master);
		        }
		    }
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_wl_unna($csv_row, $clientid, $userid,  $csv_data,$client_users,$location_master)
		{
		    $Tr = new Zend_View_Helper_Translate();

		    $imported_csv_data = $csv_data[$csv_row];
		    
// 		    $gender = array("maennlich"=>"1","weiblich"=>"2");
		    $gender = array("männlich"=>"1","weiblich"=>"2");
		    
		    if(strlen($imported_csv_data['3']) > 0 ){
	       	    $bd_date = date('Y-m-d', strtotime($imported_csv_data['3']));
		    } 
		    
		    
   		    $curent_dt = date('Y-m-d H:i:s', time());
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['0']);
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['1']);
		    
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['4']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['5']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['6']);
		    
		    
// 		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
// 		    $cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
// 		    $cust->email= Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->birthd = $bd_date;
		    
		    
// 		    $cust->admission_date = $curent_dt;
            if($imported_csv_data['14']){
    		    $post_admission_date = date('Y-m-d', strtotime($imported_csv_data['14'])); //
    		    $cust->admission_date =date('Y-m-d 00:00:00', strtotime($imported_csv_data['14'])); //
    		    $admission_date = date('Y-m-d 00:00:00', strtotime($imported_csv_data['14'])); //
    		    
            } else {
//     		    $cust->admission_date = $curent_dt;
//     		    $cust->admission_date = date('1970-01-01 00:00:00'); //
    		    
    		    
    		    $post_admission_date = $curent_dt; //
    		    $cust->admission_date = $curent_dt; //
    		    $admission_date = $curent_dt; //
    		    
    		    $cust->isstandby = "1";
            }
		    
		    
		    if($gender[$imported_csv_data['2']]){
    		    $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['2']]);
		    } else {
    		    $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    
		    
// 		    $cust->import_pat = $imported_csv_data['0'];
		    $cust->isadminvisible= '1';
		    
		    
// 		    if(strlen($imported_csv_data['31']) > 0){
//     		    $cust->isdischarged = '1';
//     		    $cust->traffic_status= '0';
// 		    }
		    
		    $cust->save();
		
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
 
		    // insert in QPA mapping
		    
		    if(strlen($imported_csv_data['9'])){ 
		    	$client_user_id = "";
		    	$user_name = trim($imported_csv_data['9']);
		    	$client_user_id = $client_users[$user_name];

		    	if($client_user_id){
			    	$cust = new PatientQpaMapping();
			    	$cust->epid = $epid;
			    	$cust->clientid = $clientid;
			    	$cust->userid = $client_user_id;
			    	$cust->save();
		    	}
		    }
		    if(strlen($imported_csv_data['10'])){ 
		    	$client_user_id_second = "";
		    	$user_name_second = trim($imported_csv_data['10']);
		    	$client_user_id_second= $client_users[$user_name_second];

		    	if($client_user_id_second){
			    	$cust = new PatientQpaMapping();
			    	$cust->epid = $epid;
			    	$cust->clientid = $clientid;
			    	$cust->userid = $client_user_id_second;
			    	$cust->save();
		    	}
		    }
		    
		    
		    // INSERT in Patient locations
		    
		    if(strlen($imported_csv_data['7']) > 0 && strlen($imported_csv_data['8']) > 0){ 
		    
		    	$location_name = trim($imported_csv_data['8']);
		    	$location_id = $location_master[$location_name];
		    	if(strlen($location_id)>0){
			    	$cust = new PatientLocation();
			    	$cust->ipid = $ipid;
			    	$cust->clientid = $clientid;
			    	$cust->valid_from = date('Y-m-d 00:00:00', strtotime($imported_csv_data['7'])); //
			    	$cust->valid_till = "0000-00-00 00:00:00";
			    	$cust->location_id = $location_id;
			    	$cust->save();
		    	} 
		    }
	
		    
		      
		  /*  
		    // health insurance
		    if(strlen($imported_csv_data['21']) > 0){ // KK_Krankenversicherung_name
		        // insert in health insurance master
		        $hinsu = new HealthInsurance();
		        $hinsu->clientid = $clientid;
		        $hinsu->name = $imported_csv_data['21'];//KK_Krankenversicherung_name
// 		        $hinsu->name2 = $imported_csv_data['19'];//KK_Krankenversicherung_name2
// 		        $hinsu->zip = $imported_csv_data['21'];//KK_PLZ
// 		        $hinsu->city = $imported_csv_data['22'];//KK_Stadt
// 		        $hinsu->phone = $imported_csv_data['23'];//KK_Telefon 1
// 		        $hinsu->phone2 = $imported_csv_data['24'];//KK_Telefon 2
// 		        $hinsu->phonefax  = $imported_csv_data['25'];//KK_Fax
// 		        $hinsu->email = $imported_csv_data['26'];//KK_Email
// 		        $hinsu->iknumber = $imported_csv_data['15'];//KK_KrankenIK
		        $hinsu->valid_from = date("Y-m-d", time());
		        $hinsu->extra = 1;
		        $hinsu->onlyclients = '0';
		        $hinsu->save();
		        $company_id =$hinsu->id; 
		        
		        // insert in patient
		        $patient_hi = new PatientHealthInsurance();
		        $patient_hi->ipid = $ipid;
		        $patient_hi->companyid = $company_id;
// 		        $patient_hi->institutskennzeichen = $imported_csv_data['15'];//KK_KrankenIK
// 		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($imported_csv_data['16']);//KK_KV Status
// 		        $patient_hi->insurance_no = $imported_csv_data['17'];//KK_Versichertennr
		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['21']);//KK_Krankenversicherung_name
// 		        $patient_hi->ins_contactperson = Pms_CommonData::aesEncrypt($imported_csv_data['20']); //KK_Ansprechpartner
// 		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($imported_csv_data['21']);//KK_PLZ
// 		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($imported_csv_data['22']);//KK_Stadt
// 		        $patient_hi->ins_phone = Pms_CommonData::aesEncrypt($imported_csv_data['23']);//KK_Telefon 1
// 		        $patient_hi->ins_phone2 = Pms_CommonData::aesEncrypt($imported_csv_data['24']);//KK_Telefon 2
// 		        $patient_hi->ins_phonefax = Pms_CommonData::aesEncrypt($imported_csv_data['25']); // KK_Fax
// 		        $patient_hi->ins_email = Pms_CommonData::aesEncrypt($imported_csv_data['26']);//KK_Email
		        $patient_hi->insurance_no = $imported_csv_data['22'];//KK_Email
		        $patient_hi->save();
		    }

 
 */
		    
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		    
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		    
		    /* 
		     
		     if(strlen($imported_csv_data['31']) > 0){
		        
		        $discharge_date = date('Y-m-d 00:00:10', strtotime($imported_csv_data['31'])); //
		        
		        $pd = new PatientDischarge();
		        $pd->ipid = $ipid;
		        $pd->discharge_date = $discharge_date;

// 		        if (strlen($imported_csv_data['30'])>0){
// 		          $pd->discharge_location = $discharge_locations[$clientid][trim($imported_csv_data['30'])];
// 		        }
		        if (strlen($imported_csv_data['31'])>0){
		          $pd->discharge_method = "1715";
// 		          $pd->discharge_method = "1715";
// 		          $pd->discharge_method = "1670"; // for testing
// 		          $pd->discharge_method = "36"; // for testing
		        }
		        $pd->save();
		        
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $discharge_date;
		        $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		        
		        
		        // Patient discharge course - discharge date START
		        $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
		        $cust = new PatientCourse();
		        $cust->ipid = $ipid;
		        $cust->course_date = date("Y-m-d H:i:s", time());
		        $cust->course_type = Pms_CommonData::aesEncrypt("K");
		        $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
		        $cust->user_id = $userid;
		        $cust->save();
		        
		    }
		    
		*/
		    
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
 
		    
 
		    
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert 12.05.2017');
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_wl_kinder_clients_import');
		    $course->save();
		    
		    
// 		    XV,XZ, XR
		    // insert course 
		      if (strlen($imported_csv_data['12'])>0){ // XV
		      	$course_date_xv = "";
		      	$course_title_xv= "";
		      	
		      	
		      	$course_date_xv = date('Y-m-d 00:00:10', strtotime($imported_csv_data['12']));
		      	$course_title_xv = date('d.m.Y', strtotime($imported_csv_data['12']));
		      	
		      	$course_xv = new PatientCourse();
		      	$course_xv->ipid = $ipid;
		      	$course_xv->course_date = $course_date_xv;
		      	$course_xv->course_type = Pms_CommonData::aesEncrypt('XV');
		      	$course_xv->course_title = Pms_CommonData::aesEncrypt($course_title_xv); 
		      	$course_xv->done_date = $course_date_xv;
		      	$course_xv->user_id = $userid;
		      	$course_xv->tabname = Pms_CommonData::aesEncrypt('new_wl_unna_import');
		      	$course_xv->save();
		      }
		      
		      
		      
		      if (strlen($imported_csv_data['13'])>0){ // XZ
		      	$course_date_xz = "";
		      	$course_title_xz= "";
		      	 
		      	$course_date_xz = date('Y-m-d 00:00:10', strtotime($imported_csv_data['13']));
		      	$course_title_xz = date('d.m.Y', strtotime($imported_csv_data['13']));
		      	 
		      	$course = new PatientCourse();
		      	$course->ipid = $ipid;
		      	$course->course_date = $course_date_xz;
		      	$course->course_type = Pms_CommonData::aesEncrypt('XZ');
		      	$course->course_title = Pms_CommonData::aesEncrypt($course_title_xz); 
		      	$course->done_date = $course_date_xz;
		      	$course->user_id = $userid;
		      	$course->tabname = Pms_CommonData::aesEncrypt('new_wl_unna_import');
		      	$course->save();
		      }
		      
		      
		      if (strlen($imported_csv_data['15'])>0){ // XR

		      	$course_title_xr = "";
		      	$course_title_xr = $imported_csv_data['15'];
		      	 
		      	$course = new PatientCourse();
		      	$course->ipid = $ipid;
		      	$course->course_date = $curent_dt;
		      	$course->course_type = Pms_CommonData::aesEncrypt('XR');
		      	$course->course_title = Pms_CommonData::aesEncrypt($course_title_xr); ;
		      	$course->done_date = $curent_dt;
		      	$course->user_id = $userid;
		      	$course->tabname = Pms_CommonData::aesEncrypt('new_wl_unna_import');
		      	$course->save();
		      }
		    
		    
		}
		
/*--------------------------------------------------*/
/*--------------------------------------------------*/
/*--------------------------------------------------*/
/*--------------------------------------------------*/
		
		public function patient_import_handler_rp($csv_data,$csv_data_falls, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;

		    // get diagnosis types
		    // if no diagnisis types - insert
// 	 print_r($csv_data); 
	 foreach($csv_data as $k_csv_row => $import_type)
	 {
	 	if($k_csv_row != 0){
// 	 		print_r($import_type);
// 	 		print_r("\n");
			if( strlen(trim($import_type['1'])) > 0  ){
		 		$imported_ids[] = $import_type['1'];
				$hi_pat2street[trim($import_type['1'])] = $import_type['21'];
			}
	 //		$this->import_patient_rp($k_csv_row, $clientid, $userid,  $csv_data, $csv_falls,$side_diagnosis_type,$main_diagnosis_type);
	 		//     	            $this->import_patient_rp_second($k_csv_row, $clientid, $userid,  $csv_data, $csv_falls,$side_diagnosis_type,$main_diagnosis_type);
	 	}
	 }
	 if(!empty($imported_ids)){
	 	

	 	$patient = Doctrine_Query::create()
	 	->select("p.*,e.*")
	 	->from('PatientMaster p')
	 	->leftJoin("p.EpidIpidMapping e")
	 	->andWhereIn('e.clientid', array($clientid))
	 	->andWhere("p.import_pat != ''")
	 	->andWhere("p.isdelete = 0 ");
	 	$patient_details = $patient->fetchArray();
// 	 	print_r($patient_details); exit;
	 	
	 	foreach($patient_details as $k => $pat_val)
	 	{
	 		$patients_array[$pat_val['import_pat']] = $pat_val['ipid'];
// 	 		$ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
// 	 		$patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
// 	 		$patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
	 	}
// 	 	print_r($patients_array); exit;
	 	foreach($patients_array as $pid=>$ipid){
	 		$phi_update = Doctrine::getTable('PatientHealthInsurance')->findOneByIpid($ipid);
	 		if($phi_update){
	 			
		 		$phi_update->ins_street = Pms_CommonData::aesEncrypt($hi_pat2street[$pid]); //KK_Ansprechpartner
		 		$phi_update->save();
	 		}
	 	}
	 	
	 }
	 print_r($hi_pat2street); 
	 print_r($imported_ids); 
// 	 print_r($csv_data_falls); 
	 exit;
	 exit;
	 exit;
		    $dg = new DiagnosisType();
		    
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    if($ddarr1){
		    
		    	$comma = ",";
		    	$typeid = "'0'";
		    	foreach($ddarr1 as $key => $valdia)
		    	{
		    		$type_id_array[] = $valdia['id'];
		    	}
		    	$main_diagnosis_type = $type_id_array[0];
		    }
		    else
		    {
		    	$res = new DiagnosisType();
		    	$res->clientid = $clientid;
		    	$res->abbrevation = 'HD';
		    	$res->save();
		    	$main_diagnosis_type = $res->id;
		    }
		    
		    
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    if($ddarr2){
		    
		    	$comma = ",";
		    	$typeid = "'0'";
		    	foreach($ddarr2 as $key => $valdia)
		    	{
		    		$type_id_array[] = $valdia['id'];
		    	}
		    	$side_diagnosis_type = $type_id_array[0];
		    }
		    else
		    {
		    	$side_res = new DiagnosisType();
		    	$side_res->clientid = $clientid;
		    	$side_res->abbrevation = 'ND';
		    	$side_res->save();
		    	$side_diagnosis_type = $side_res->id;
		    }
		    
		    
 
		    foreach($csv_data_falls as $k_csv_row => $dimport){
		    	if($dimport[0] != "EPID"){

			    	$csv_falls[$dimport[0]]['isdischarged'] = '0';
		    		if(strlen($dimport[1]) > 0 ){
				    	$csv_falls[$dimport[0]]['admission_date'] = date("Y-m-d H:i:s",strtotime($dimport[1]));
				    	
				    	if(strlen($dimport[2]) > 0 ){ // patient has discharge
					    	$csv_falls[$dimport[0]]['discharge_date'] = date("Y-m-d H:i:s",strtotime($dimport[2]));
					    	$csv_falls[$dimport[0]]['discharge_method'] = $dimport[3];
					    	$csv_falls[$dimport[0]]['discharge_location'] = $dimport[4];
					    	$csv_falls[$dimport[0]]['isdischarged'] = '1';
				    	} 
		    		} else{
				    	$csv_falls[$dimport[0]]['admission_date'] = date("Y-m-d H:i:s");
		    		}
		    	}
		    }
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
    	            $this->import_patient_rp($k_csv_row, $clientid, $userid,  $csv_data, $csv_falls,$side_diagnosis_type,$main_diagnosis_type);
//     	            $this->import_patient_rp_second($k_csv_row, $clientid, $userid,  $csv_data, $csv_falls,$side_diagnosis_type,$main_diagnosis_type);
		        }
		    }

		    
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_rp($csv_row, $clientid, $userid,  $csv_data, $falls_array)
		{
		    $Tr = new Zend_View_Helper_Translate();

		    $imported_csv_data = $csv_data[$csv_row];
		    
		    $gender = array("m"=>"1","w"=>"2");
		    
		    if(strlen($imported_csv_data['2']) > 0 ){
	       	    $bd_date = date('Y-m-d', strtotime($imported_csv_data['2']));
		    } 
		    
		    
   		    $curent_dt = date('Y-m-d H:i:s', time());
   		    $curent_dt_dmY = date('d.m.Y', time());
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['5']);
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['4']);
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['8']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['7']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['6']);
		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
// 		    $cust->email= Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->living_will= $imported_csv_data['31'];
		    $cust->birthd = $bd_date;
		    
		    
// 		    $cust->admission_date = $curent_dt;
            if(!empty($falls_array[$imported_csv_data['1']])){
    		    $post_admission_date = $falls_array[$imported_csv_data['1']]['admission_date'];
    		    $admission_date = $falls_array[$imported_csv_data['1']]['admission_date'];

    		    $cust->admission_date = $falls_array[$imported_csv_data['1']]['admission_date'];
    		    $cust->admission_date = $falls_array[$imported_csv_data['1']]['admission_date'];
    		    $cust->isdischarged =  $falls_array[$imported_csv_data['1']]['isdischarged'];
    		    if($falls_array[$imported_csv_data['1']]['isdischarged'] == "1"){
    		    	$cust->traffic_status= '0';
    		    }
    		    
            } else {
//     		    $cust->admission_date = $curent_dt;
    		    $cust->admission_date = date('1970-01-01 00:00:00'); //
    		    $post_admission_date = date('1970-01-01 00:00:00'); //
    		    $admission_date = date('1970-01-01 00:00:00'); //
            }
		    
		    
		    if($gender[$imported_csv_data['3']]){
    		    $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['3']]);
		    } else {
    		    $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    
		    
		    $cust->import_pat = $imported_csv_data['1'];
		    $cust->isadminvisible= '1';
		    
		    
// 		    if(strlen($imported_csv_data['31']) > 0){
//     		    $cust->isdischarged = '1';
//     		    $cust->traffic_status= '0';
// 		    }
		    
		    $cust->save();
		
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
 
		    
		    // diagnosis
		    if(strlen($imported_csv_data['11']) > 0 && $imported_csv_data['11'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['11'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $main_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    
		    
		    if(strlen($imported_csv_data['12']) > 0  && $imported_csv_data['12'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['12'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    if(strlen($imported_csv_data['13']) > 0  && $imported_csv_data['13'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['13'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    if(strlen($imported_csv_data['14']) > 0  && $imported_csv_data['14'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['14'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    
		    
		    
		    // health insurance
		    if(strlen($imported_csv_data['18']) > 0){ // KK_Krankenversicherung_name
		        // insert in health insurance master
		        $hinsu = new HealthInsurance();
		        $hinsu->clientid = $clientid;
		        $hinsu->name = $imported_csv_data['18'];//KK_Krankenversicherung_name
		        $hinsu->name2 = $imported_csv_data['19'];//KK_Krankenversicherung_name2
		        $hinsu->zip = $imported_csv_data['22'];//KK_PLZ
		        $hinsu->city = $imported_csv_data['23'];//KK_Stadt
		        $hinsu->phone = $imported_csv_data['24'];//KK_Telefon 1
		        $hinsu->phone2 = $imported_csv_data['25'];//KK_Telefon 2
		        $hinsu->phonefax  = $imported_csv_data['26'];//KK_Fax
		        $hinsu->email = $imported_csv_data['27'];//KK_Email
		        $hinsu->iknumber = $imported_csv_data['15'];//KK_KrankenIK
		        $hinsu->insurance_provider = $imported_csv_data['20'];//KK_KrankenIK
		        $hinsu->valid_from = date("Y-m-d", time());
		        $hinsu->extra = 1;
		        $hinsu->onlyclients = '0';
		        $hinsu->save();
		        $company_id =$hinsu->id; 
		        
		        // insert in patient
		        $patient_hi = new PatientHealthInsurance();
		        $patient_hi->ipid = $ipid;
		        $patient_hi->companyid = $company_id;
// 		        $patient_hi->institutskennzeichen = $imported_csv_data['15'];//KK_KrankenIK
		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($imported_csv_data['16']);//KK_KV Status
// 		        $patient_hi->insurance_no = $imported_csv_data['17'];//KK_Versichertennr
		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['18']);//KK_Krankenversicherung_name
		        $patient_hi->ins_contactperson = Pms_CommonData::aesEncrypt($imported_csv_data['20']); //KK_Ansprechpartner
		        $patient_hi->ins_street = Pms_CommonData::aesEncrypt($imported_csv_data['20']); //KK_Ansprechpartner
		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($imported_csv_data['22']);//KK_PLZ
		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($imported_csv_data['23']);//KK_Stadt
		        $patient_hi->ins_phone = Pms_CommonData::aesEncrypt($imported_csv_data['24']);//KK_Telefon 1
		        $patient_hi->ins_phone2 = Pms_CommonData::aesEncrypt($imported_csv_data['25']);//KK_Telefon 2
		        $patient_hi->ins_phonefax = Pms_CommonData::aesEncrypt($imported_csv_data['26']); // KK_Fax
		        $patient_hi->ins_email = Pms_CommonData::aesEncrypt($imported_csv_data['27']);//KK_Email
		        $patient_hi->insurance_no = $imported_csv_data['17'];//KK_Email
		        $patient_hi->save();
		    }

 
 
		    
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		    
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		   	if($falls_array[$imported_csv_data['1']]['isdischarged'] == "1"){ 
		        $discharge_date = $falls_array[$imported_csv_data['1']]['discharge_date'];
		        
		        $pd = new PatientDischarge();
		        $pd->ipid = $ipid;
		        $pd->discharge_date = $discharge_date;
	            $pd->discharge_location = $falls_array[$imported_csv_data['1']]['discharge_location'];
	            $pd->discharge_method = $falls_array[$imported_csv_data['1']]['discharge_method'];
		        $pd->save();
		        
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $discharge_date;
		        $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		        
		        
		        // Patient discharge course - discharge date START
		        $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
		        $cust = new PatientCourse();
		        $cust->ipid = $ipid;
		        $cust->course_date = date("Y-m-d H:i:s", time());
		        $cust->course_type = Pms_CommonData::aesEncrypt("K");
		        $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
		        $cust->user_id = $userid;
		        $cust->save();
		    } else{
		    	
		    }
		    
		    
// 		    // INSERT IN PATIENT ACTIVE
// 		    $patient_active = new PatientActive();
// 		    $patient_active->ipid = $pipd;
// 		    $patient_active->start = date("Y-m-d", strtotime($admission_date));
// 		    if(isset($discharge_date) &&  strlen($discharge_date) > 0){
// 		    	$patient_active->end = date("Y-m-d", strtotime($discharge_date));
// 		    }
// 		    $patient_active->save();
		    
		    
		    
		    // religion
		    if(strlen($imported_csv_data['30']) > 0){
		    	if($imported_csv_data['30'] == ""){
		    
		    	}
		    	elseif($imported_csv_data['30'] == "nicht bekannt"){
		    		$religion = "999"; // dummy because - no corespondent
		    	}
		    	elseif($imported_csv_data['30'] == "Evangelisch"){
		    		$religion = "1";
		    	}
		    	elseif($imported_csv_data['30'] == "Islam"){
		    
		    		$religion = "5";
		    	}
		    	elseif($imported_csv_data['30'] == "Röm.-Kath."){
		    
		    		$religion = "3";
		    	}
		    	else
		    	{
		    		$religion = "";
		    	}
		    	if(strlen($religion)>0){
		    
		    		$frm = new PatientReligions();
		    		$frm->ipid = $ipid;
		    		$frm->religion = $religion;
		    		$frm->save();
		    	}
		    }
		    
		    if(strlen($imported_csv_data['28']) > 0){
		    	if($imported_csv_data['28'] == ""){
		    	
		    	} elseif($imported_csv_data['28'] == "deutsch"){
		    
		    		$stastszugehorigkeit = "1";
		    	} else{
		    		$stastszugehorigkeit = "1";
		    		$anderefree =$imported_csv_data['28'];
		    	}
		    	 
		    	$cust = new Stammdatenerweitert();
		    	$cust->ipid = $ipid;
		    	$cust->stastszugehorigkeit = $stastszugehorigkeit;
		    	$cust->anderefree = $anderefree;
		    	$cust->save();
		    	
		    }
		    
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
 
		    
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_rp_clients_import');
		    $course->save();
		    
		}
		
		
		###########################################################################################################
		############################## START WL 03 2019            ################################################
		###########################################################################################################
		###########################################################################################################
		
		public function patient_import_handler_wl_2019_2183($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;

		    $dg = new DiagnosisType();
		    
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    if($ddarr1){
		    
		        $type_id_array = array();
		    	$comma = ",";
		    	$typeid = "'0'";
		    	foreach($ddarr1 as $key => $valdia)
		    	{
		    		$type_id_array[] = $valdia['id'];
		    	}
		    	$main_diagnosis_type = $type_id_array[0];
		    }
		    else
		    {
		    	$res = new DiagnosisType();
		    	$res->clientid = $clientid;
		    	$res->abbrevation = 'HD';
		    	$res->save();
		    	$main_diagnosis_type = $res->id;
		    }
		    
		    
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    if($ddarr2){
		        $type_id_nd_array = array();
		    	$comma = ",";
		    	$typeid = "'0'";
		    	foreach($ddarr2 as $key => $valdia)
		    	{
		    		$type_id_nd_array[] = $valdia['id'];
		    	}
		    	$side_diagnosis_type = $type_id_nd_array[0];
		    }
		    else
		    {
		    	$side_res = new DiagnosisType();
		    	$side_res->clientid = $clientid;
		    	$side_res->abbrevation = 'ND';
		    	$side_res->save();
		    	$side_diagnosis_type = $side_res->id;
		    }
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
    	            $this->import_patient_wl_2019_2183($k_csv_row, $clientid, $userid,  $csv_data, $side_diagnosis_type,$main_diagnosis_type);
		        }
		    }

		    
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_wl_2019_2183($csv_row, $clientid, $userid,  $csv_data, $side_diagnosis_type,$main_diagnosis_type )
		{
		    $Tr = new Zend_View_Helper_Translate();

		    $imported_csv_data = $csv_data[$csv_row];
		    
		    
   		    $curent_dt = date('Y-m-d H:i:s', time());
   		    $curent_dt_dmY = date('d.m.Y', time());
		    
		    

   		    // insert in  familydoc
   		    if(strlen($imported_csv_data['0']) > 0 ){
   		        
   		        $master_fam = new FamilyDoctor();
   		        $master_fam->clientid = $clientid;
   		        $master_fam->indrop = '1';
   		        $master_fam->last_name = $imported_csv_data['0'];
   		        $master_fam->save();
   		        $master_fam_id = $master_fam->id;
   		    }
   		    
   		    
   		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
            //################################################################
		    //insert patient

		    $gender = array("m"=>"1","w"=>"2");
		    
		    if(strlen($imported_csv_data['2']) > 0 ){
		        $bd_date = date('Y-m-d', strtotime($imported_csv_data['2']));
		    }
		    
		    
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['5']);
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['4']);
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['8']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['7']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['6']);
		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
// 		    $cust->email= Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->living_will= $imported_csv_data['31'];
		    $cust->birthd = $bd_date;
		    if($master_fam_id){
    		    $cust->familydoc_id = $master_fam_id;
		    }
            if(!empty($imported_csv_data['15'])){
    		    $post_admission_date =  date("Y-m-d H:i:s", strtotime($imported_csv_data['15']));
    		    $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['15']));

    		    $cust->admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['15']));
    		    $cust->isdischarged =  0;
   		    	$cust->traffic_status= '1';
    		    
            } else {
    		    $cust->admission_date = date('1970-01-01 00:00:00'); //
    		    $post_admission_date = date('1970-01-01 00:00:00'); //
    		    $admission_date = date('1970-01-01 00:00:00'); //
            }
		    
		    
		    if($gender[$imported_csv_data['3']]){
    		    $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['3']]);
		    } else {
    		    $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    $cust->import_pat = $imported_csv_data['1'];
		    $cust->isadminvisible= '1';
		    $cust->save(); // ******************* SAVE ******************* 
		
		    
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		    
		    
		    //################################################################
		    // diagnosis
		    if(strlen($imported_csv_data['11']) > 0 && $imported_csv_data['11'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['11'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $main_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    
		    
		    if(strlen($imported_csv_data['12']) > 0  && $imported_csv_data['12'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['12'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    if(strlen($imported_csv_data['13']) > 0  && $imported_csv_data['13'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['13'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    if(strlen($imported_csv_data['14']) > 0  && $imported_csv_data['14'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['14'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    
		    
		    
		    // health insurance
		    if(strlen($imported_csv_data['19']) > 0){ // KK_Krankenversicherung_name
		        // insert in health insurance master
		        $hinsu = new HealthInsurance();
		        $hinsu->clientid = $clientid;
		        $hinsu->iknumber = $imported_csv_data['16'];//KK_KrankenIK
		        $hinsu->name = $imported_csv_data['19'];//KK_Krankenversicherung_name
		        $hinsu->name2 = $imported_csv_data['20'];//KK_Krankenversicherung_name2
		        $hinsu->insurance_provider = $imported_csv_data['21'];//KK_Ansprechpartner
		        $hinsu->street1= $imported_csv_data['22'];//KK_Straße 
		        $hinsu->zip = $imported_csv_data['23'];//KK_PLZ
		        $hinsu->city = $imported_csv_data['24'];//KK_Stadt
		        $hinsu->phone = $imported_csv_data['25'];//KK_Telefon 1
		        $hinsu->phone2 = $imported_csv_data['26'];//KK_Telefon 2
		        $hinsu->phonefax  = $imported_csv_data['27'];//KK_Fax
		        $hinsu->email = $imported_csv_data['28'];//KK_Email
		        $hinsu->valid_from = date("Y-m-d", time());
		        $hinsu->extra = 1;
		        $hinsu->onlyclients = '0';
		        $hinsu->save();
		        $company_id =$hinsu->id; 
		        
		        // insert in patient health insurance 
		        $patient_hi = new PatientHealthInsurance();
		        $patient_hi->ipid = $ipid;
		        $patient_hi->companyid = $company_id;
		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($imported_csv_data['17']);//KK_KV Status
		        $patient_hi->insurance_no = $imported_csv_data['18'];// KK_Versichertennr
		        $patient_hi->institutskennzeichen = $imported_csv_data['16'];// KK_KrankenIK
		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['19']);//KK_Krankenversicherung_name
		        $patient_hi->ins_contactperson = Pms_CommonData::aesEncrypt($imported_csv_data['21']); //KK_Ansprechpartner
		        $patient_hi->ins_street = Pms_CommonData::aesEncrypt($imported_csv_data['22']); //KK_Straße
		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($imported_csv_data['23']);//KK_PLZ
		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($imported_csv_data['24']);//KK_Stadt
		        $patient_hi->ins_phone = Pms_CommonData::aesEncrypt($imported_csv_data['25']);//KK_Telefon 1
		        $patient_hi->ins_phone2 = Pms_CommonData::aesEncrypt($imported_csv_data['26']);//KK_Telefon 2
		        $patient_hi->ins_phonefax = Pms_CommonData::aesEncrypt($imported_csv_data['27']); // KK_Fax
		        $patient_hi->ins_email = Pms_CommonData::aesEncrypt($imported_csv_data['28']);//KK_Email
		        $patient_hi->save();
		    }

 
 
		    
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		    
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		   /*  
		   	if($falls_array[$imported_csv_data['1']]['isdischarged'] == "1"){ 
		        $discharge_date = $falls_array[$imported_csv_data['1']]['discharge_date'];
		        
		        $pd = new PatientDischarge();
		        $pd->ipid = $ipid;
		        $pd->discharge_date = $discharge_date;
	            $pd->discharge_location = $falls_array[$imported_csv_data['1']]['discharge_location'];
	            $pd->discharge_method = $falls_array[$imported_csv_data['1']]['discharge_method'];
		        $pd->save();
		        
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $discharge_date;
		        $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		        
		        
		        // Patient discharge course - discharge date START
		        $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
		        $cust = new PatientCourse();
		        $cust->ipid = $ipid;
		        $cust->course_date = date("Y-m-d H:i:s", time());
		        $cust->course_type = Pms_CommonData::aesEncrypt("K");
		        $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
		        $cust->user_id = $userid;
		        $cust->save();
		    } else{
		    	
		    } */
		    
		    
// 		    // INSERT IN PATIENT ACTIVE
// 		    $patient_active = new PatientActive();
// 		    $patient_active->ipid = $pipd;
// 		    $patient_active->start = date("Y-m-d", strtotime($admission_date));
// 		    if(isset($discharge_date) &&  strlen($discharge_date) > 0){
// 		    	$patient_active->end = date("Y-m-d", strtotime($discharge_date));
// 		    }
// 		    $patient_active->save();
		    
		    
		    
		    // migration
		    if(strlen($imported_csv_data['29']) > 0)
		    { // Migration
    		    $mig = new PatientMigration();
    		    $mig->ipid = $ipid;
    		    if( strtolower($imported_csv_data['29']) == "1"){
    		        $mig->foreign_migration = 1;
    		    } else{
    		        $mig->foreign_migration = 0;
    		    }
    		    if(strlen($imported_csv_data['30']) > 0){ // Migration_welcher
    		        $mig->foreign_migration_from = $imported_csv_data['30'];
    		    }
    		    $mig->save();
		    }
		    
		    
		    
		    // religion
		    if(strlen($imported_csv_data['31']) > 0){
		    	if($imported_csv_data['31'] == ""){
		    
		    	}
		    	elseif($imported_csv_data['31'] == "nicht bekannt"){
		    		$religion = "999"; // dummy because - no corespondent
		    	}
		    	elseif($imported_csv_data['31'] == "Evangelisch"){
		    		$religion = "1";
		    	}
		    	elseif($imported_csv_data['31'] == "Islam"){
		    
		    		$religion = "5";
		    	}
		    	elseif($imported_csv_data['31'] == "Röm.-Kath."){
		    
		    		$religion = "3";
		    	}
		    	else
		    	{
		    		$religion = "";
		    	}
		    	if(strlen($religion)>0){
		    
		    		$frm = new PatientReligions();
		    		$frm->ipid = $ipid;
		    		$frm->religion = $religion;
		    		$frm->save();
		    	}
		    }
		    
		    
		    // Patientenverfügung
		    if(strlen($imported_csv_data['32']) > 0)
		    {
		        if($imported_csv_data['32'] == "1"){
		            $data['id'] =  0;
		            $data['active'] = "yes";
		            $data['division_tab'] = "living_will";
		            $data['contactperson_master_id'] = null;
		        
    		       $entity = new PatientAcp();
    		       $result = $entity->findOrCreateOneByIpidAndId($ipid, $data['id'], $data);
		        }
		    }
		    
		    //Wunschsterbeort :: Death wish
		    if(strlen($imported_csv_data['33']) > 0)
		    { 
    		    $pdw = new PatientDeathwish();
    		    $pdw->ipid = $ipid;
    		    if( strtolower($imported_csv_data['33']) == "1"){
    		        $pdw->death_wish = 1;
    		    } else{
    		        $pdw->death_wish = 0;
    		    }
    		    $pdw->save();
		    }
		    
		    
		     
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
 
		    
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_wl_2019_clients_import');
		    $course->save();
		    
		}

		
		public function patient_import_handler_wl_2019_2182($csv_data, $post)
		{
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
 

		    $dg = new DiagnosisType();
		    
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    if($ddarr1){
		    
		        $type_id_array = array();
		    	$comma = ",";
		    	$typeid = "'0'";
		    	foreach($ddarr1 as $key => $valdia)
		    	{
		    		$type_id_array[] = $valdia['id'];
		    	}
		    	$main_diagnosis_type = $type_id_array[0];
		    }
		    else
		    {
		    	$res = new DiagnosisType();
		    	$res->clientid = $clientid;
		    	$res->abbrevation = 'HD';
		    	$res->save();
		    	$main_diagnosis_type = $res->id;
		    }
		    
		    
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    if($ddarr2){
		        $type_id_nd_array = array();
		    	$comma = ",";
		    	$typeid = "'0'";
		    	foreach($ddarr2 as $key => $valdia)
		    	{
		    		$type_id_nd_array[] = $valdia['id'];
		    	}
		    	$side_diagnosis_type = $type_id_nd_array[0];
		    }
		    else
		    {
		    	$side_res = new DiagnosisType();
		    	$side_res->clientid = $clientid;
		    	$side_res->abbrevation = 'ND';
		    	$side_res->save();
		    	$side_diagnosis_type = $side_res->id;
		    }
		    
		    
		    // select all locations of client
		    // get client locations
		    $client_locations = array();
		    $client_loc_q = Doctrine_Query::create()
		    ->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
		    ->from('Locations')
		    ->where('client_id = ?',$clientid)
		    ->andWhere('isdelete=0');
		    $client_loc = $client_loc_q->fetchArray();
		    
		    // LOCATIONS
		    $locations = array();
		    $station_name = "";
		    $location_name = "";
		    foreach($csv_data as $k_csv_r=>$data){
		        $location_name = trim($data['6']);
		        
		        if(!empty($location_name )){
		            
		            $station_name = trim($data['7']);
		            
		            if(!empty($station_name) && !in_array($station_name,$locations[$location_name])){
		                
        		        $locations[$location_name][] = $station_name;
		            }
		        }
		    }

		    foreach($locations as $loc_name => $station_array){

		    	$master_loc = new Locations();
		    	$master_loc->client_id = $clientid;
		    	$master_loc->location = Pms_CommonData::aesEncrypt($loc_name);
	    		$master_loc->location_type = "0";
		    	$master_loc->save();
                $location_id = $master_loc->id;
                $location_name2id[$loc_name] = $location_id;
                
                if(!empty($station_array)){
                    foreach($station_array as $k=>$station_name){

                        $master_loc_station = new LocationsStations();
                        $master_loc_station->client_id = $clientid;
                        $master_loc_station->location_id = $location_id;
                        $master_loc_station->station = Pms_CommonData::aesEncrypt($station_name);  ;
                        $master_loc_station->save();
                        $station_id = $master_loc_station->id;
                        
                        $location_name2staionname2stationid[$loc_name][$station_name] = $station_id;
                    }
                }
		    }
		    // Locations - end
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
    	            $this->import_patient_wl_2019_2182($k_csv_row, $clientid, $userid,  $csv_data, $side_diagnosis_type,$main_diagnosis_type,$location_name2id,$location_name2staionname2stationid);
		        }
		    }

		    
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_wl_2019_2182($csv_row, $clientid, $userid,  $csv_data, $side_diagnosis_type,$main_diagnosis_type ,$location_name2id,$location_name2staionname2stationid)
		{
		    $Tr = new Zend_View_Helper_Translate();

		    $imported_csv_data = $csv_data[$csv_row];
		    
   		    $curent_dt = date('Y-m-d H:i:s', time());
   		    $curent_dt_dmY = date('d.m.Y', time());

   		    // insert in  familydoc
   		    if(strlen($imported_csv_data['0']) > 0 ){
   		        
   		        $regexp_ln = trim($imported_csv_data['0']);
   		        Pms_CommonData::value_patternation($regexp_ln);
   		        
   		        $client_f_doc = Doctrine_Query::create()
   		        ->select('id,title,salutation,first_name,last_name,street1,zip,city,phone_practice,phone_private,fax,email,doctornumber,doctor_bsnr,comments,practice,debitor_number')
   		        ->from('FamilyDoctor')
   		        ->where("last_name  REGEXP ?", $regexp_ln)
   		        ->andWhere('clientid = ?', $clientid)
   		        ->andWhere("valid_till='0000-00-00'")
   		        ->andWhere("indrop = 0")
   		        ->andWhere('isdelete=0')
   		        ->orderBy('last_name ASC')
   		        ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
   		        
   		        $master_fam = new FamilyDoctor();
   		        $master_fam->clientid = $clientid;
   		        $master_fam->indrop = '1';
   		        
   		        if(!empty($client_f_doc)) {
   		            foreach($client_f_doc as $column=>$value) {
   		                if($column != "id") {
   		                    $master_fam->{$column} = $value;
   		                } else{
   		                    $master_fam->self_id = $value;
   		                }
   		            }
   		        } else {
   		            $master_fam->last_name = $imported_csv_data['0'];
   		        }
   		        $master_fam->save();
   		        $master_fam_id = $master_fam->id;
   		    }
   		    
   		    
   		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
            //################################################################
		    //insert patient

		    $gender = array("m"=>"1","w"=>"2");
		    
		    if(strlen($imported_csv_data['2']) > 0 ){
		        $bd_date = date('Y-m-d', strtotime($imported_csv_data['2']));
		    }
		    
		    
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['4']);
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['5']);
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['11']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['13']);
		    $cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['12']);

		    $cust->living_will= $imported_csv_data['34'];
		    $cust->birthd = $bd_date;
		    if($master_fam_id){
    		    $cust->familydoc_id = $master_fam_id;
		    }
            if(!empty($imported_csv_data['8'])){
    		    $post_admission_date =  date("Y-m-d H:i:s", strtotime($imported_csv_data['8']));
    		    $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['8']));

    		    $cust->admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['8']));
    		    $cust->isdischarged =  0;
   		    	$cust->traffic_status= '1';
    		    
            } else {
    		    $cust->admission_date = date('1970-01-01 00:00:00'); //
    		    $post_admission_date = date('1970-01-01 00:00:00'); //
    		    $admission_date = date('1970-01-01 00:00:00'); //
            }
		    
		    
		    if($gender[$imported_csv_data['3']]){
    		    $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['3']]);
		    } else {
    		    $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    
		    $cust->import_pat = $imported_csv_data['1'];
		    $cust->isadminvisible= '1';
		    $cust->save(); // ******************* SAVE ******************* 
		
		    
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		    
		    // locations
		    
		    if(strlen($imported_csv_data['6']) > 0){
		        $cust = new PatientLocation();
		        $cust->ipid = $ipid;
		        $cust->clientid = $clientid;
		        $cust->valid_from = $admission_date;
		        $cust->valid_till = "0000-00-00 00:00:00";
		        $cust->location_id = $location_name2id[trim($imported_csv_data['6'])];
		        if(strlen($imported_csv_data['7']) > 0){
    		        $cust->station = $location_name2staionname2stationid[trim($imported_csv_data['6'])][trim($imported_csv_data['7'])];
		        }
		        $cust->save();
		    }
		    
		    
		    
		    //################################################################
		    // diagnosis
		    if(strlen($imported_csv_data['14']) > 0 && $imported_csv_data['14'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['14'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $main_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    
		    
		    if(strlen($imported_csv_data['15']) > 0  && $imported_csv_data['15'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['15'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    if(strlen($imported_csv_data['16']) > 0  && $imported_csv_data['16'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['16'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    if(strlen($imported_csv_data['17']) > 0  && $imported_csv_data['17'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['17'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    
		    
		    
		    // health insurance
		    if(strlen($imported_csv_data['21']) > 0){ // KK_Krankenversicherung_name
		        // insert in health insurance master
		        $hinsu = new HealthInsurance();
		        $hinsu->clientid = $clientid;
		        $hinsu->iknumber = $imported_csv_data['18'];//KK_KrankenIK
		        $hinsu->name = $imported_csv_data['21'];//KK_Krankenversicherung_name
		        $hinsu->name2 = $imported_csv_data['22'];//KK_Krankenversicherung_name2
		        $hinsu->insurance_provider = $imported_csv_data['23'];//KK_Ansprechpartner
		        $hinsu->street1= $imported_csv_data['24'];//KK_Straße 
		        $hinsu->zip = $imported_csv_data['25'];//KK_PLZ
		        $hinsu->city = $imported_csv_data['26'];//KK_Stadt
		        $hinsu->phone = $imported_csv_data['27'];//KK_Telefon 1
		        $hinsu->phone2 = $imported_csv_data['28'];//KK_Telefon 2
		        $hinsu->phonefax  = $imported_csv_data['29'];//KK_Fax
		        $hinsu->email = $imported_csv_data['30'];//KK_Email
		        $hinsu->valid_from = date("Y-m-d", time());
		        $hinsu->extra = 1;
		        $hinsu->onlyclients = '0';
		        $hinsu->save();
		        $company_id =$hinsu->id; 
		        
		        // insert in patient health insurance 
		        $patient_hi = new PatientHealthInsurance();
		        $patient_hi->ipid = $ipid;
		        $patient_hi->companyid = $company_id;
		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($imported_csv_data['19']);//KK_KV Status
		        $patient_hi->insurance_no = $imported_csv_data['20'];// KK_Versichertennr
		        $patient_hi->institutskennzeichen = $imported_csv_data['18'];// KK_KrankenIK
		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['21']);//KK_Krankenversicherung_name
		        $patient_hi->ins_contactperson = Pms_CommonData::aesEncrypt($imported_csv_data['23']); //KK_Ansprechpartner
		        $patient_hi->ins_street = Pms_CommonData::aesEncrypt($imported_csv_data['24']); //KK_Straße
		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($imported_csv_data['25']);//KK_PLZ
		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($imported_csv_data['26']);//KK_Stadt
		        $patient_hi->ins_phone = Pms_CommonData::aesEncrypt($imported_csv_data['27']);//KK_Telefon 1
		        $patient_hi->ins_phone2 = Pms_CommonData::aesEncrypt($imported_csv_data['28']);//KK_Telefon 2
		        $patient_hi->ins_phonefax = Pms_CommonData::aesEncrypt($imported_csv_data['29']); // KK_Fax
		        $patient_hi->ins_email = Pms_CommonData::aesEncrypt($imported_csv_data['30']);//KK_Email
		        $patient_hi->save();
		    }

 
 
		    
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		    
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		   /*  
		   	if($falls_array[$imported_csv_data['1']]['isdischarged'] == "1"){ 
		        $discharge_date = $falls_array[$imported_csv_data['1']]['discharge_date'];
		        
		        $pd = new PatientDischarge();
		        $pd->ipid = $ipid;
		        $pd->discharge_date = $discharge_date;
	            $pd->discharge_location = $falls_array[$imported_csv_data['1']]['discharge_location'];
	            $pd->discharge_method = $falls_array[$imported_csv_data['1']]['discharge_method'];
		        $pd->save();
		        
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $discharge_date;
		        $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		        
		        
		        // Patient discharge course - discharge date START
		        $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
		        $cust = new PatientCourse();
		        $cust->ipid = $ipid;
		        $cust->course_date = date("Y-m-d H:i:s", time());
		        $cust->course_type = Pms_CommonData::aesEncrypt("K");
		        $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
		        $cust->user_id = $userid;
		        $cust->save();
		    } else{
		    	
		    } */
		    
		    
// 		    // INSERT IN PATIENT ACTIVE
// 		    $patient_active = new PatientActive();
// 		    $patient_active->ipid = $pipd;
// 		    $patient_active->start = date("Y-m-d", strtotime($admission_date));
// 		    if(isset($discharge_date) &&  strlen($discharge_date) > 0){
// 		    	$patient_active->end = date("Y-m-d", strtotime($discharge_date));
// 		    }
// 		    $patient_active->save();
		    
		    
		    
		    // migration
		    if(strlen($imported_csv_data['31']) > 0)
		    { // Migration
    		    $mig = new PatientMigration();
    		    $mig->ipid = $ipid;
    		    if( strtolower($imported_csv_data['31']) == "1"){
    		        $mig->foreign_migration = 1;
    		    } else{
    		        $mig->foreign_migration = 0;
    		    }
    		    if(strlen($imported_csv_data['32']) > 0){ // Migration_welcher
    		        $mig->foreign_migration_from = $imported_csv_data['32'];
    		    }
    		    $mig->save();
		    }
		    
		    
		    
		    // religion
		    if(strlen($imported_csv_data['33']) > 0){
		    	if($imported_csv_data['33'] == ""){
		    
		    	}
		    	elseif($imported_csv_data['33'] == "nicht bekannt"){
		    		$religion = "999"; // dummy because - no corespondent
		    	}
		    	elseif($imported_csv_data['33'] == "Evangelisch"){
		    		$religion = "1";
		    	}
		    	elseif($imported_csv_data['33'] == "Islam"){
		    
		    		$religion = "5";
		    	}
		    	elseif($imported_csv_data['33'] == "Röm.-Kath."){
		    
		    		$religion = "3";
		    	}
		    	else
		    	{
		    		$religion = "";
		    	}
		    	if(strlen($religion)>0){
		    
		    		$frm = new PatientReligions();
		    		$frm->ipid = $ipid;
		    		$frm->religion = $religion;
		    		$frm->save();
		    	}
		    }
		    
		    
		    // Patientenverfügung
		    if(strlen($imported_csv_data['34']) > 0)
		    {
		        if($imported_csv_data['34'] == "1"){
		            $data['id'] =  0;
		            $data['active'] = "yes";
		            $data['division_tab'] = "living_will";
		            $data['contactperson_master_id'] = null;
		        }
		        
    		    $entity = new PatientAcp();
    		    $result = $entity->findOrCreateOneByIpidAndId($ipid, $data['id'], $data);
		    }
		    
		    //Wunschsterbeort :: Death wish
		    if(strlen($imported_csv_data['35']) > 0)
		    { 
    		    $pdw = new PatientDeathwish();
    		    $pdw->ipid = $ipid;
    		    if( strtolower($imported_csv_data['35']) == "1"){
    		        $pdw->death_wish = 1;
    		    } else{
    		        $pdw->death_wish = 0;
    		    }
    		    $pdw->save();
		    }
		    
		    
		     
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
 
		    
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_wl_2019_clients_import');
		    $course->save();
		    
		}
        ###########################################################################################################
        ############################## END WL 03 2019            ##################################################
        ###########################################################################################################
        ###########################################################################################################
		
		private function import_patient_rp_second($csv_row, $clientid, $userid,  $csv_data, $falls_array,$side_diagnosis_type,$main_diagnosis_type)
		{
		    $Tr = new Zend_View_Helper_Translate();

		    $imported_csv_data = $csv_data[$csv_row];
		    
// 		    print_r($imported_csv_data);
// 		    print_r($falls_array[$imported_csv_data['1']]);
// 		    exit;
		    
		    $gender = array("M"=>"1","F"=>"2");
		    
		    $bd_date = "";
		    if(strlen($imported_csv_data['2']) > 0 ){
	       	    $bd_date = date('Y-m-d', strtotime($imported_csv_data['2']));
		    } 
		    
		    
   		    $curent_dt = date('Y-m-d H:i:s', time());
   		    $curent_dt_dmY = date('d.m.Y', time());
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['5']);
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['4']);
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['8']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['7']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['6']);
		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
// 		    $cust->email= Pms_CommonData::aesEncrypt($imported_csv_data['10']);
// 		    $cust->living_will= $imported_csv_data['31'];
		    $cust->birthd = $bd_date;
		    
		    
// 		    $cust->admission_date = $curent_dt;
            if(!empty($falls_array[$imported_csv_data['1']])){
    		    $post_admission_date = $falls_array[$imported_csv_data['1']]['admission_date'];
    		    $admission_date = $falls_array[$imported_csv_data['1']]['admission_date'];

    		    $cust->admission_date = $falls_array[$imported_csv_data['1']]['admission_date'];
    		    $cust->admission_date = $falls_array[$imported_csv_data['1']]['admission_date'];
    		    $cust->isdischarged =  $falls_array[$imported_csv_data['1']]['isdischarged'];
    		    if($falls_array[$imported_csv_data['1']]['isdischarged'] == "1"){
    		    	$cust->traffic_status= '0';
    		    }
    		    
            } else {
//     		    $cust->admission_date = $curent_dt;
    		    $cust->admission_date = date('1970-01-01 00:00:00'); //
    		    $post_admission_date = date('1970-01-01 00:00:00'); //
    		    $admission_date = date('1970-01-01 00:00:00'); //
            }
		    
		    
		    if($gender[$imported_csv_data['3']]){
    		    $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['3']]);
		    } else {
    		    $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    
		    
		    $cust->import_pat = $imported_csv_data['1'];
		    $cust->isadminvisible= '1';
		    
		    
// 		    if(strlen($imported_csv_data['31']) > 0){
//     		    $cust->isdischarged = '1';
//     		    $cust->traffic_status= '0';
// 		    }
		    
		    $cust->save();
		
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
 
		    
		    // diagnosis
		    if(strlen($imported_csv_data['11']) > 0 && $imported_csv_data['11'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['11'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $main_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    
		    
		    if(strlen($imported_csv_data['12']) > 0  && $imported_csv_data['12'] != "-"){
		    
		    	$free_diagno_id ="";
		    	$diagno_free = new DiagnosisText();
		    	$diagno_free->clientid = $clientid;
		    	$diagno_free->icd_primary = $imported_csv_data['12'];
		    	$diagno_free->free_name = " ";
		    	$diagno_free->save();
		    	$free_diagno_id = $diagno_free->id;
		    
		    	$diagno = new PatientDiagnosis();
		    	$diagno->ipid = $ipid;
		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
		    	$diagno->diagnosis_id = $free_diagno_id;
		    	$diagno->icd_id = "0";
		    	$diagno->save();
		    }
		    
// 		    if(strlen($imported_csv_data['13']) > 0  && $imported_csv_data['13'] != "-"){
		    
// 		    	$free_diagno_id ="";
// 		    	$diagno_free = new DiagnosisText();
// 		    	$diagno_free->clientid = $clientid;
// 		    	$diagno_free->icd_primary = $imported_csv_data['13'];
// 		    	$diagno_free->free_name = " ";
// 		    	$diagno_free->save();
// 		    	$free_diagno_id = $diagno_free->id;
		    
// 		    	$diagno = new PatientDiagnosis();
// 		    	$diagno->ipid = $ipid;
// 		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
// 		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
// 		    	$diagno->diagnosis_id = $free_diagno_id;
// 		    	$diagno->icd_id = "0";
// 		    	$diagno->save();
// 		    }
// 		    if(strlen($imported_csv_data['14']) > 0  && $imported_csv_data['14'] != "-"){
		    
// 		    	$free_diagno_id ="";
// 		    	$diagno_free = new DiagnosisText();
// 		    	$diagno_free->clientid = $clientid;
// 		    	$diagno_free->icd_primary = $imported_csv_data['14'];
// 		    	$diagno_free->free_name = " ";
// 		    	$diagno_free->save();
// 		    	$free_diagno_id = $diagno_free->id;
		    
// 		    	$diagno = new PatientDiagnosis();
// 		    	$diagno->ipid = $ipid;
// 		    	$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
// 		    	$diagno->diagnosis_type_id = $side_diagnosis_type ;
// 		    	$diagno->diagnosis_id = $free_diagno_id;
// 		    	$diagno->icd_id = "0";
// 		    	$diagno->save();
// 		    }
		    
		    
		    // health insurance
		    if(strlen($imported_csv_data['16']) > 0){ // KK_Krankenversicherung_name
		        // insert in health insurance master
		        $hinsu = new HealthInsurance();
		        $hinsu->clientid = $clientid;
		        $hinsu->name = $imported_csv_data['16'];//KK_Krankenversicherung_name
// 		        $hinsu->name2 = $imported_csv_data['19'];//KK_Krankenversicherung_name2
		        $hinsu->street1 = $imported_csv_data['17'];
		        $hinsu->zip = $imported_csv_data['18'];//KK_PLZ
		        $hinsu->city = $imported_csv_data['19'];//KK_Stadt

// 		        $hinsu->phone = $imported_csv_data['24'];//KK_Telefon 1
// 		        $hinsu->phone2 = $imported_csv_data['25'];//KK_Telefon 2
// 		        $hinsu->phonefax  = $imported_csv_data['26'];//KK_Fax
// 		        $hinsu->email = $imported_csv_data['27'];//KK_Email
		        $hinsu->iknumber = $imported_csv_data['13'];//KK_KrankenIK
// 		        $hinsu->insurance_provider = $imported_csv_data['20'];//KK_KrankenIK
		        $hinsu->valid_from = date("Y-m-d", time());
		        $hinsu->extra = 1;
		        $hinsu->onlyclients = '0';
		        $hinsu->save();
		        $company_id =$hinsu->id; 

		        
		        // insert in patient
		        $patient_hi = new PatientHealthInsurance();
		        $patient_hi->ipid = $ipid;
		        $patient_hi->companyid = $company_id;
		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($imported_csv_data['14']);//KK_KV Status
		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['16']);//KK_Krankenversicherung_name
		        $patient_hi->ins_street = Pms_CommonData::aesEncrypt($imported_csv_data['17']);//KK_Stadt
		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($imported_csv_data['18']);//KK_PLZ
		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($imported_csv_data['19']);//KK_Stadt
		        $patient_hi->insurance_no = $imported_csv_data['15'];//KK_Email
		        $patient_hi->save();
		    }

 
 
		    
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		    
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		   	if($falls_array[$imported_csv_data['1']]['isdischarged'] == "1"){ 
		        $discharge_date = $falls_array[$imported_csv_data['1']]['discharge_date'];
		        
		        $pd = new PatientDischarge();
		        $pd->ipid = $ipid;
		        $pd->discharge_date = $discharge_date;
	            $pd->discharge_location = $falls_array[$imported_csv_data['1']]['discharge_location'];
	            $pd->discharge_method = $falls_array[$imported_csv_data['1']]['discharge_method'];
		        $pd->save();
		        
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $discharge_date;
		        $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		        
		        
		        // Patient discharge course - discharge date START
		        $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
		        $cust = new PatientCourse();
		        $cust->ipid = $ipid;
		        $cust->course_date = date("Y-m-d H:i:s", time());
		        $cust->course_type = Pms_CommonData::aesEncrypt("K");
		        $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
		        $cust->user_id = $userid;
		        $cust->save();
		    } else{
		    	
		    }
		    
		    
// 		    // INSERT IN PATIENT ACTIVE
// 		    $patient_active = new PatientActive();
// 		    $patient_active->ipid = $pipd;
// 		    $patient_active->start = date("Y-m-d", strtotime($admission_date));
// 		    if(isset($discharge_date) &&  strlen($discharge_date) > 0){
// 		    	$patient_active->end = date("Y-m-d", strtotime($discharge_date));
// 		    }
// 		    $patient_active->save();
		    
		    
		    
		    // religion
		    if(strlen($imported_csv_data['30']) > 0){
		    	if($imported_csv_data['30'] == ""){
		    
		    	}
		    	elseif($imported_csv_data['30'] == "nicht bekannt"){
		    		$religion = "999"; // dummy because - no corespondent
		    	}
		    	elseif($imported_csv_data['30'] == "ev"){
		    		$religion = "1";
		    	}
		    	elseif($imported_csv_data['30'] == "muslimisch"){
		    
		    		$religion = "5";
		    	}
		    	elseif($imported_csv_data['30'] == "christlich-orthodox"){
		    
		    		$religion = "3";
		    	}
		    	else
		    	{
		    		$religion = "";
		    	}
		    	if(strlen($religion)>0){
		    
		    		$frm = new PatientReligions();
		    		$frm->ipid = $ipid;
		    		$frm->religion = $religion;
		    		$frm->save();
		    	}
		    }
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
 
		    
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_rp_clients_import');
		    $course->save();
		    
		    
		    
		    
		}
		
		
/*--------------------------------------------------------------*/
/*--------------------------------------------------------------*/
/*--------------------------------------------------------------*/
/*--------------------------------------------------------------*/
		public function patient_import_handler_lmu($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    // get diagnosis types
		    // if no diagnisis types - insert 
		    
		    $dg = new DiagnosisType();
		    
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    if($ddarr1){
		        
    		    $comma = ",";
    		    $typeid = "'0'";
    		    foreach($ddarr1 as $key => $valdia)
    		    {
    		        $type_id_array[] = $valdia['id'];
    		    }
    		    $main_diagnosis_type = $type_id_array[0]; 
		    } 
		    else
		    {
		        $res = new DiagnosisType();
		        $res->clientid = $clientid;
		        $res->abbrevation = 'HD';
		        $res->save();
		        $main_diagnosis_type = $res->id;
		    }
		    
		    
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    if($ddarr2){
		        
    		    $comma = ",";
    		    $typeid = "'0'";
    		    foreach($ddarr2 as $key => $valdia)
    		    {
    		        $type_id_array[] = $valdia['id'];
    		    }
    		    $side_diagnosis_type = $type_id_array[0]; 
		    } 
		    else
		    {
		        $side_res = new DiagnosisType();
		        $side_res->clientid = $clientid;
		        $side_res->abbrevation = 'ND';
		        $side_res->save();
		        $side_diagnosis_type = $side_res->id;
		    }

		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
    	            $this->import_patient_lmu($k_csv_row, $clientid, $userid,  $csv_data,$main_diagnosis_type,$side_diagnosis_type);
		        }
		    }
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_lmu($csv_row, $clientid, $userid,  $csv_data,$main_diagnosis_type,$side_diagnosis_type)
		{
		    $Tr = new Zend_View_Helper_Translate();

		    $imported_csv_data = $csv_data[$csv_row];
		    
		    $gender = array("m"=>"1","w"=>"2");
		    
		    $bd_date = date('Y-m-d', strtotime($imported_csv_data['2']));
		    $curent_dt = date('Y-m-d H:i:s', time());
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['5']);
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['4']);
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['8']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['7']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['6']);
		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['10']);
		    $cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
		    $cust->birthd = $bd_date;
		    
		    $cust->admission_date = $curent_dt;
		    
		    
		    if($gender[$imported_csv_data['3']]){
    		    $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['3']]);
		    } else {
    		    $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    $cust->living_will = $imported_csv_data['30'];
		    
		    $cust->import_pat = $imported_csv_data['1'];
		    $cust->isadminvisible= '1';
		    $cust->save();
		
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
		    // diagnosis
		    if(strlen($imported_csv_data['11']) > 0){
		    
		        $free_diagno_id ="";
		        $diagno_free = new DiagnosisText();
		        $diagno_free->clientid = $clientid;
		        $diagno_free->icd_primary = $imported_csv_data['11'];
		        $diagno_free->free_name = " ";
		        $diagno_free->save();
		        $free_diagno_id = $diagno_free->id;
		    
		        $diagno = new PatientDiagnosis();
		        $diagno->ipid = $ipid;
		        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		        $diagno->diagnosis_type_id = $main_diagnosis_type ;
		        $diagno->diagnosis_id = $free_diagno_id;
		        $diagno->icd_id = "0";
		        $diagno->save();
		    }
		    
		    
		    if(strlen($imported_csv_data['12']) > 0){
		    
		        $free_diagno_id ="";
		        $diagno_free = new DiagnosisText();
		        $diagno_free->clientid = $clientid;
		        $diagno_free->icd_primary = $imported_csv_data['12'];
		        $diagno_free->free_name = " ";
		        $diagno_free->save();
		        $free_diagno_id = $diagno_free->id;
		    
		        $diagno = new PatientDiagnosis();
		        $diagno->ipid = $ipid;
		        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		        $diagno->diagnosis_type_id = $side_diagnosis_type ;
		        $diagno->diagnosis_id = $free_diagno_id;
		        $diagno->icd_id = "0";
		        $diagno->save();
		    }
		    if(strlen($imported_csv_data['13']) > 0){
		    
		        $free_diagno_id ="";
		        $diagno_free = new DiagnosisText();
		        $diagno_free->clientid = $clientid;
		        $diagno_free->icd_primary = $imported_csv_data['13'];
		        $diagno_free->free_name = " ";
		        $diagno_free->save();
		        $free_diagno_id = $diagno_free->id;
		    
		        $diagno = new PatientDiagnosis();
		        $diagno->ipid = $ipid;
		        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		        $diagno->diagnosis_type_id = $side_diagnosis_type ;
		        $diagno->diagnosis_id = $free_diagno_id;
		        $diagno->icd_id = "0";
		        $diagno->save();
		    }
		    if(strlen($imported_csv_data['14']) > 0){
		    
		        $free_diagno_id ="";
		        $diagno_free = new DiagnosisText();
		        $diagno_free->clientid = $clientid;
		        $diagno_free->icd_primary = $imported_csv_data['14'];
		        $diagno_free->free_name = " ";
		        $diagno_free->save();
		        $free_diagno_id = $diagno_free->id;
		    
		        $diagno = new PatientDiagnosis();
		        $diagno->ipid = $ipid;
		        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
		        $diagno->diagnosis_type_id = $side_diagnosis_type ;
		        $diagno->diagnosis_id = $free_diagno_id;
		        $diagno->icd_id = "0";
		        $diagno->save();
		    }

		    
		    // health insurance
		    if(strlen($imported_csv_data['18']) > 0){ // KK_Krankenversicherung_name
		        // insert in health insurance master
		        $hinsu = new HealthInsurance();
		        $hinsu->clientid = $clientid;
		        $hinsu->name = $imported_csv_data['18'];//KK_Krankenversicherung_name
		        $hinsu->name2 = $imported_csv_data['19'];//KK_Krankenversicherung_name2
		        $hinsu->zip = $imported_csv_data['21'];//KK_PLZ
		        $hinsu->city = $imported_csv_data['22'];//KK_Stadt
		        $hinsu->phone = $imported_csv_data['23'];//KK_Telefon 1
		        $hinsu->phone2 = $imported_csv_data['24'];//KK_Telefon 2
		        $hinsu->phonefax  = $imported_csv_data['25'];//KK_Fax
		        $hinsu->email = $imported_csv_data['26'];//KK_Email
		        $hinsu->iknumber = $imported_csv_data['15'];//KK_KrankenIK
		        $hinsu->valid_from = date("Y-m-d", time());
		        $hinsu->extra = 1;
		        $hinsu->onlyclients = '0';
		        $hinsu->save();
		        $company_id =$hinsu->id; 
		        
		        // insert in patient
		        $patient_hi = new PatientHealthInsurance();
		        $patient_hi->ipid = $ipid;
		        $patient_hi->companyid = $company_id;
		        $patient_hi->institutskennzeichen = $imported_csv_data['15'];//KK_KrankenIK
		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($imported_csv_data['16']);//KK_KV Status
		        $patient_hi->insurance_no = $imported_csv_data['17'];//KK_Versichertennr
		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['18']);//KK_Krankenversicherung_name
		        $patient_hi->ins_contactperson = Pms_CommonData::aesEncrypt($imported_csv_data['20']); //KK_Ansprechpartner
		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($imported_csv_data['21']);//KK_PLZ
		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($imported_csv_data['22']);//KK_Stadt
		        $patient_hi->ins_phone = Pms_CommonData::aesEncrypt($imported_csv_data['23']);//KK_Telefon 1
		        $patient_hi->ins_phone2 = Pms_CommonData::aesEncrypt($imported_csv_data['24']);//KK_Telefon 2
		        $patient_hi->ins_phonefax = Pms_CommonData::aesEncrypt($imported_csv_data['25']); // KK_Fax
		        $patient_hi->ins_email = Pms_CommonData::aesEncrypt($imported_csv_data['26']);//KK_Email
		        $patient_hi->save();
		    }

		    // migration 
		    if(strlen($imported_csv_data['27']) > 0)
		    { // Migration
		        $mig = new PatientMigration();
		        $mig->ipid = $ipid;
		        if( strtolower($imported_csv_data['27']) == "true"){
    		        $mig->foreign_migration = 1;
		        } else{
    		        $mig->foreign_migration = 0;
		        }
                if(strlen($imported_csv_data['28']) > 0){ // Migration_welcher
    		        $mig->foreign_migration_from = $imported_csv_data['28'];
                }
		        $mig->save();
		    }
		    
		    // religion
		    if(strlen($imported_csv_data['29']) > 0){
		        if($imported_csv_data['29'] == ""){
		            
		        }
		        elseif($imported_csv_data['29'] == "nicht bekannt"){
		            $religion = "999"; // dummy because - no corespondent 
		        } 
		        elseif($imported_csv_data['29'] == "evangelisch"){
		            $religion = "1";
		        } 
		        elseif($imported_csv_data['29'] == "muslimisch"){
		            
		            $religion = "5";
		        } 
		        elseif($imported_csv_data['29'] == "christlich-orthodox"){
		            
		            $religion = "3";
		        } 
		        else
		        {
		            $religion = "";
		        } 
    		    if(strlen($religion)>0){
    		        
    		        $frm = new PatientReligions();
        		    $frm->ipid = $ipid;
        		    $frm->religion = $religion;
        		    $frm->save();
    		    }
		    }
		    
		  /*   $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		    
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save(); */
		    
		    
/* 		    if(strlen($imported_csv_data['29']) > 0){
		        
		        $pd = new PatientDischarge();
		        $pd->ipid = $ipid;
		        $pd->discharge_date= $discharge_date;

		        if (strlen($imported_csv_data['30'])>0){
		          $pd->discharge_location = $discharge_locations[$clientid][trim($imported_csv_data['30'])];
		        }
		        if (strlen($imported_csv_data['31'])>0){
		          $pd->discharge_method = $discharge_methods[$clientid][trim($imported_csv_data['31'])];
		        }
		        $pd->save();
		        
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $discharge_date;
		        $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		    }
		     */
		
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $curent_dt;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
 
		    
 
		    
		    //write first entry in patient course
// 		    $course = new PatientCourse();
// 		    $course->ipid = $ipid;
// 		    $course->course_date = $curent_dt;
// 		    $course->course_type = Pms_CommonData::aesEncrypt('K');
// 		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert 01.02.2016');
// 		    $course->done_date = $curent_dt;
// 		    $course->user_id = $userid;
// 		    $course->tabname = Pms_CommonData::aesEncrypt('new_wl_clients_import');
// 		    $course->save();
		    
		    
		    
		}
		
		

		public function location_import_handler_lmu($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;

		    $import_clients = array($clientid);
		 
		
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		
            // create array for admisssions  
		    foreach($csv_data as $rk => $read_data){
  		        $falls2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		    }
		    foreach($falls2patients as $ipid => $fall_data){
		        
		        foreach($fall_data as $k => $fall){

		            $det_adm[$ipid][$k]['import_pat'] = $fall[0];
		            $det_adm[$ipid][$k]['ipid'] = $ipid;
		            $det_adm[$ipid][$k]['admission_date'] = date("Y-m-d H:i:s",strtotime($fall[1]));

		            $course_details[$ipid][$k]['admission']['patient_ipid'] = $ipid;
		            $course_details[$ipid][$k]['admission']['course_date'] = date("Y-m-d 00:00:00",strtotime($fall[1]));
		            $course_details[$ipid][$k]['admission']['done_date'] = date("Y-m-d 00:00:00",strtotime($fall[1]));
		            $course_details[$ipid][$k]['admission']['course_title'] = "Aufnahmezeitpunkt : ".date("d.m.Y",strtotime($fall[1]));
		            $course_details[$ipid][$k]['admission']['course_type'] = "K";

		            
		            $course_details[$ipid][$k]['location']['patient_ipid'] = $ipid;
		            $course_details[$ipid][$k]['location']['course_date'] = date("Y-m-d 00:01:00",strtotime($fall[1]));
		            $course_details[$ipid][$k]['location']['done_date'] = date("Y-m-d 00:01:00",strtotime($fall[1]));
		            $course_details[$ipid][$k]['location']['course_title'] = "Aufenthaltsort: ".$fall[7];
		            $course_details[$ipid][$k]['location']['course_type'] = "K";
		            
		            
		            if(strlen($fall[2])>0 &&  strlen($fall[4])>0)
		            {
    		            $det_adm[$ipid][$k]['discharge_date'] = date("Y-m-d H:i:s",strtotime($fall[2]));
    		            $det_adm[$ipid][$k]['discharge_method'] = $fall[4]; 
    		            $det_adm[$ipid][$k]['discharge_method_name'] = $fall[3]; 
    		            $det_adm[$ipid][$k]['discharge_location'] = $fall[6];
    		            $det_adm[$ipid][$k]['discharge_location_name'] = $fall[7];
		            }
		             
		            $det[$ipid][$k]['adm']['import_pat'] = $fall[0];
		            $det[$ipid][$k]['adm']['ipid'] = $ipid;
		            $det[$ipid][$k]['adm']['admission_date'] = date("Y-m-d H:i:s",strtotime($fall[1]));

		            if(strlen($fall[2])>0 &&  strlen($fall[4])>0)
		            {
    		            $det[$ipid][$k]['adm']['discharge_date'] = date("Y-m-d H:i:s",strtotime($fall[2]));
    		            $det[$ipid][$k]['adm']['discharge_method'] = $fall[4]; 
    		            $det[$ipid][$k]['adm']['discharge_method_name'] = $fall[3]; 
    		            $det[$ipid][$k]['adm']['discharge_location'] = $fall[6];
    		            $det[$ipid][$k]['adm']['discharge_location_name'] = $fall[7];
    		            

    		            $course_details[$ipid][$k]['discharge']['patient_ipid'] = $ipid;
    		            $course_details[$ipid][$k]['discharge']['course_date'] = date("Y-m-d H:i:s",strtotime($fall[2]));
    		            $course_details[$ipid][$k]['discharge']['done_date'] = date("Y-m-d H:i:s",strtotime($fall[2]));
    		            $course_details[$ipid][$k]['discharge']['course_title'] = "Entlassungszeitpunkt : ".date("d.m.Y",strtotime($fall[2]));
    		            $course_details[$ipid][$k]['discharge']['discharge_date'] = date("d.m.Y",strtotime($fall[2]));
    		            $course_details[$ipid][$k]['discharge']['discharge_method'] = $fall[3];
    		            $course_details[$ipid][$k]['discharge']['discharge_location'] = $fall[7];
    		            $course_details[$ipid][$k]['discharge']['course_type'] = "K";
    		            
    		            
		            } 
		            
                    // location
		            $det_loc[$ipid][$k]['ipid'] = $ipid;
		            $det_loc[$ipid][$k]['patient_ipid'] = $ipid;
		            $det_loc[$ipid][$k]['patient_client'] = $ipid2client[$ipid];
		            
		            $det_loc[$ipid][$k]['from'] = $fall[1];
   		            $det_loc[$ipid][$k]['till'] = $fall[2]; 
		            
		            $det_loc[$ipid][$k]['valid_from'] = date("Y-m-d H:i:s",strtotime($fall[1]));
		            if(strlen($fall[2])>0 ){
    		            $det_loc[$ipid][$k]['valid_till'] = date("Y-m-d H:i:s",strtotime($fall[2])); 
		            } 
		            else
		            {
    		            $det_loc[$ipid][$k]['valid_till'] = "0000-00-00 00:00:00"; 
		            }
		            
		            $det_loc[$ipid][$k]['location_id'] = $fall[8];
		            $det_loc[$ipid][$k]['location_name'] = $fall[7];
		            $det_loc[$ipid][$k]['discharge_location'] = "0";
		            

                    // location 
		            $det[$ipid][$k]['loc']['ipid'] = $ipid;
		            $det[$ipid][$k]['loc']['patient_ipid'] = $ipid;
		            $det[$ipid][$k]['loc']['valid_from'] = date("Y-m-d H:i:s",strtotime($fall[1]));
		            if(strlen($fall[2])>0 ){
    		            $det[$ipid][$k]['loc']['valid_till'] = date("Y-m-d H:i:s",strtotime($fall[2])); 
		            } else{
    		            $det[$ipid][$k]['loc']['valid_till'] = "0000-00-00 00:00:00";; 
		            }
		            $det[$ipid][$k]['loc']['location_id'] = $fall[8];
		            $det[$ipid][$k]['loc']['location_name'] = $fall[7];
		            $det[$ipid][$k]['loc']['discharge_location'] = "0";

		            
		  
		        }
		    }
		    
		    foreach($det_loc as $pat =>$data){
		        $sorted_data[$pat] = $this->array_sort($data, "valid_from", SORT_ASC);
		    }		    
		    foreach($sorted_data as $patient_imp_aid => $imdata){
		        $new_key_sorted_data[$patient_imp_aid] = array_values ($imdata);
		    }
		    
		    $pm = new PatientMaster();
		    foreach($new_key_sorted_data as $patient_imp_aid => $datas)
		    {
		        $diff[$patient_imp_aid] = "";
		    
		        if(count($datas) > 1){
		            $k_new ="9999";
		            foreach($datas as $loc_k=> $lock_data){
		                if($datas[$loc_k+1]['from'] && empty($datas[$loc_k]['till'])){
		                    $datas[$loc_k]['till'] = $datas[$loc_k+1]['from'];
		                }
		                if($datas[$loc_k+1]['from'] && $datas[$loc_k]['till'] != $datas[$loc_k+1]['from'] )
		                {
		                    $diff[$patient_imp_aid] = $pm->getDaysInBetween(date("Y-m-d", strtotime($datas[$loc_k]['till'])), date("Y-m-d", strtotime($datas[$loc_k+1]['from'])));
		    
		                    if(count($diff[$patient_imp_aid]) >=2)
		                    {
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['patient_ipid'] = $patient_imp_aid;
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['patient_client'] = $ipid2client[$patient_imp_aid];
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['from'] = date("Y-m-d",strtotime($diff[$patient_imp_aid][0]));
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['till'] = date("Y-m-d",strtotime(end($diff[$patient_imp_aid])));
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['valid_from'] = date("Y-m-d 00:00:00",strtotime($diff[$patient_imp_aid][0]));
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['valid_till'] = date("Y-m-d 00:00:00",strtotime(end($diff[$patient_imp_aid])));
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['location_name'] = "DISCHARGE";
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['location_id'] =  "0";
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['discharge_location'] =  "1";
		                        $new_key_sorted_data[$patient_imp_aid][$k_new]['EXTRA'] = "YES";
		                        $k_new++;
		                    }
		                }
		            }
		        }
		    }
		    
		    foreach($new_key_sorted_data as $pat =>$datan){
		        $sorted_data_extra[$pat] = $this->array_sort($datan, "from", SORT_ASC);
		    }
		    
		    foreach($sorted_data_extra as $patient_imp_aid => $imdatan){
		        $new_key_sorted_data_extra[$patient_imp_aid] = array_values ($imdatan);
		    }
		    
		    foreach($new_key_sorted_data_extra as $patient_imp_aid => $import_data)
		    {
		        $this->import_locations_lmu($import_data);
		    }


		    // ADMISSION 
		    foreach($det_adm as $pat_ipid =>$data_adm){
		        $sorted_data_adm[$pat_ipid] = $this->array_sort($data_adm, "admission_date", SORT_ASC);
		    }
		    foreach($sorted_data_adm as $patient_ipid => $adm_mdata){
		        $new_key_sorted_adm_data[$patient_ipid] = array_values ($adm_mdata);
		    }

		    
		    
            $discharged_patients = array();
            
            // get last period
            foreach($new_key_sorted_adm_data as $ipid_patient => $fall_array){
                $last_admission[$ipid_patient] = end($fall_array); 
            }
            
            foreach($last_admission as $patient_ipids => $last_dates){
                
                if(isset($patient_ipids) and strlen($patient_ipids)> 0 )
                {
                     $pr = Doctrine::getTable('PatientMaster')->findOneByIpid($patient_ipids);
                     $pr->admission_date = $last_dates['admission_date'];
                     if(isset($last_dates['discharge_date'])  && strlen($last_dates['discharge_date']) > 0 ){
                         $pr->isdischarged= "1";
                         $discharged_patients[] = $patient_ipids;
                         $last_discharge_date[$patient_ipids] = $last_dates['discharge_date']; 
                     } 
                     else 
                     {
                        $last_discharge_date[$patient_ipids] = "";
                        $current_active[] = $patient_ipids;
                     }
                     $pr->save();
                }
            }
            
            foreach($new_key_sorted_adm_data as $pipd =>$read_fall)
            {
                if($pipd && strlen($pipd) > 0 )
                { 
                    foreach($read_fall as $k =>$rfal){
                        // inset in patient readmission -  amission date
                        
                        $patientreadmission = new PatientReadmission();
                        $patientreadmission->user_id = $userid;
                        $patientreadmission->ipid = $pipd;
                        $patientreadmission->date = $rfal['admission_date'];
                        $patientreadmission->date_type = 1; //1 =admission-readmission 2- discharge
                        $patientreadmission->save();
                        
                        if(isset($rfal['discharge_date']) ){
                            
                            $patientreadmissiond = new PatientReadmission();
                            $patientreadmissiond->user_id = $userid;
                            $patientreadmissiond->ipid = $pipd;
                            $patientreadmissiond->date = $rfal['discharge_date'];
                            $patientreadmissiond->date_type = 2; //1 =admission-readmission 2- discharge
                            $patientreadmissiond->save();
                            
                            //insert in patient discharge
                            $patientdischarge = new PatientDischarge();
                            $patientdischarge->ipid = $pipd;
                            $patientdischarge->discharge_date = $rfal['discharge_date'];
                            $patientdischarge->discharge_method = $rfal['discharge_method'];
                            $patientdischarge->discharge_location = $rfal['discharge_location'];

                            if(in_array($pipd,$current_active)){// this means that all discharge entries should be marked as deleted
                                $patientdischarge->isdelete = "1"; 
                            } elseif(strlen($last_discharge_date[$pipd]) > 0  && $rfal['discharge_date'] != $last_discharge_date[$pipd] ){ // this means that all but current discharged must be marked as deleted
                                $patientdischarge->isdelete = "1";  
                            } else{
                                $patientdischarge->isdelete = "0"; 
                            }
 
                            $patientdischarge->save();
                        }
                        
                        // INSERT IN PATIENT ACTIVE 
                        $patient_active = new PatientActive();
                        $patient_active->ipid = $pipd;
                        $patient_active->start = date("Y-m-d", strtotime($rfal['admission_date']));
                        if(isset($read_fall['discharge_date']) &&  strlen($read_fall['discharge_method']) > 0){
                            $patient_active->end = date("Y-m-d", strtotime($rfal['discharge_date']));
                        }
                        $patient_active->save();
                    }
                }
            }
            
            // INSET IN PATIENT COURSE
            foreach($course_details as $kpipid =>$adm_period_interval)
            {
                if($kpipid){
                    foreach($adm_period_interval as $course_type => $course_value)
                    {
                        foreach($course_value as $t =>$cv){
                            $cust = new PatientCourse();
                            $cust->ipid = $kpipid;
                            $cust->course_date = $cv['course_date'];
                            $cust->course_type = Pms_CommonData::aesEncrypt("K");
                            $cust->course_title = Pms_CommonData::aesEncrypt($cv['course_title']);
                            $cust->done_date = $cv['done_date'];
                            $cust->user_id = $userid;
                            $cust->save();
                            	
                            if($t == "discharge"  ){
                                $comment="";
                                $comment = "Patient wurde am ".$cv['discharge_date']." entlassen \n Entlassungsart : ".$cv['discharge_method']."\n  Entlassungsort : ".$cv['discharge_location'];
                                $pc = new PatientCourse();
                                $pc->ipid = $kpipid;
                                $pc->course_date = $cv['course_date'];
                                $pc->course_type=Pms_CommonData::aesEncrypt("K");
                                $pc->course_title=Pms_CommonData::aesEncrypt($comment);
                                $pc->tabname=Pms_CommonData::aesEncrypt("discharge");
                                $pc->done_date = $cv['done_date'];
                                $pc->user_id = $userid;
                                $pc->save();
                            }
                        }
                    }
                }
            }
            
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_locations_lmu($import_data)
		{
		    foreach($import_data as $k=>$data)
		    {
		        if($data['patient_ipid'])
		        {
		            $cust = new PatientLocation();
		            $cust->ipid = $data['patient_ipid'];
		            $cust->clientid = $data['patient_client'];
		            $cust->valid_from = $data['valid_from'];
		            $cust->valid_till = $data['valid_till'];
		            $cust->location_id = $data['location_id'];
	                $cust->discharge_location = $data['discharge_location'];
		            $cust->save();
		        }
		    }
		}
		
		public function stamdaten_import_handler_lmu($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    $import_clients = array($clientid);
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
		        $data2patients[$patients_array[$read_data[0]]['ipid']][ strtolower(trim($read_data[1]))][] = $read_data;
		    }
		    
// 		    print_r($data2patients); exit;
            foreach($data2patients as $ipid => $type_data){
                if($ipid)
                {
                    foreach($type_data as $type => $type_details){
                        
                        $types_array[] = $type;
                        foreach($type_details as $k=>$values){

                            if($type == "pflegedienst")
                            {
                                //  insert in pflegedienst
                                $master_pfl = new Pflegedienstes();
                                $master_pfl->nursing = $values['2'];
                                $master_pfl->first_name = $values['3'];
                                $master_pfl->last_name = $values['4'];
                                $master_pfl->salutation = $values['5'];
                                $master_pfl->street1 = $values['6'];
                                $master_pfl->zip = $values['7'];
                                $master_pfl->fax = $values['12'];
                                $master_pfl->email = $values['11'];
                                $master_pfl->city = $values['8'];
                                $master_pfl->phone_practice = $values['9'];
                                $master_pfl->phone_emergency = $values['10'];
                                $master_pfl->comments = $values['13'];
                                $master_pfl->indrop = 1;
                                $master_pfl->clientid = $clientid;
                                $master_pfl->save();
                                $master_pfl_id = $master_pfl->id;

                                // insert in patient pflegedienst
                                $pfl_cl = new PatientPflegedienste();
                                $pfl_cl->ipid = $ipid;
                                $pfl_cl->pflid = $master_pfl_id ;
                                $pfl_cl->pflege_comment = $values['13'];
                                $pfl_cl->save();
    						  
                            } 
                            elseif($type == "apotheke")
                            {
                            // insert in pharmacy
                                $master_ph = new Pharmacy();
                                $master_ph->pharmacy = $values['2'];
                                $master_ph->first_name = $values['3'];
                                $master_ph->last_name = $values['4'];
                                $master_ph->salutation = $values['5'];
                                $master_ph->street1 = $values['6'];
                                $master_ph->zip = $values['7'];
                                $master_ph->fax = $values['12'];
                                $master_ph->email = $values['11'];
                                $master_ph->city = $values['8'];
                                $master_ph->phone = $values['9'];
                                $master_ph->comments = $values['13'];
                                $master_ph->indrop = 1;
                                $master_ph->clientid = $clientid;
                                $master_ph->save();
                                $master_ph_id = $master_ph->id;
    
                            // insert in patient pharmacy
                              $phar_cl = new PatientPharmacy();
    						  $phar_cl->ipid = $ipid;
    						  $phar_cl->pharmacy_id = $master_ph_id ;
    						  $phar_cl->pharmacy_comment = $values['13'];
    						  $phar_cl->save();
                            } 
                            elseif($type == "physiotherapie")
                            {
                                //  insert in physiotherapie
                                $master_phys = new Physiotherapists();
                                $master_phys->clientid = $clientid;
                                $master_phys->physiotherapist = $values['2'];
                                $master_phys->first_name = $values['3'];;
                                $master_phys->last_name = $values['4'];
                                $master_phys->salutation = $values['5'];
                                $master_phys->street1 = $values['6'];
                                $master_phys->zip = $values['7'];
                                $master_phys->fax = $values['12'];
                                $master_phys->email = $values['11'];
                                $master_phys->city = $values['8'];
                                $master_phys->indrop = 1;
                                $master_phys->phone_practice = $values['9'];
                                $master_phys->phone_emergency =  $values['10'];
                                $master_phys->comments =  $values['13'];
                                $master_phys->save();
                                $master_phys_id = $master_phys->id;
                                
    
                                // insert in Physiotherapist
                                $phy_cl = new PatientPhysiotherapist();
                                $phy_cl->ipid = $ipid;
                                $phy_cl->physioid = $master_phys_id;
                                $phy_cl->physio_comment = $values['13'];
                                $phy_cl->save();
                                
                                
                            } 
                            elseif($type == "sonstige")
                            {
                            // insert in Suppliers (sonstige Versorger")
                            
                                $master_sonst = new Suppliers();
                                $master_sonst->clientid = $clientid;
                                $master_sonst->supplier = $values['2'];
                                $master_sonst->first_name = $values['3'];
                                $master_sonst->last_name = $values['4'];
                                $master_sonst->salutation = $values['5'];
                                $master_sonst->street1 = $values['6'];
                                $master_sonst->zip = $values['7'];
                                $master_sonst->fax = $values['12'];
                                $master_sonst->email = $values['11'];
                                $master_sonst->city = $values['8'];
                                $master_sonst->indrop = 1;
                                $master_sonst->phone = $values['9'];
                                $master_sonst->comments =  $values['13'];
                                $master_sonst->save();
                                $master_sonst_id = $master_sonst->id;
                                
                                //  insert in Suppliers
                                $pfl_sonst = new PatientSuppliers();
                                $pfl_sonst->ipid = $ipid;
                                $pfl_sonst->supplier_id = $master_sonst_id;
                                $pfl_sonst->supplier_comment =  $values['13'];
                                $pfl_sonst->save();
                                
                            } 
                            elseif($type == "sani")
                            {
                                
                                // insert in Suppliers (sonstige Versorger")
                                $master_sani = new Supplies();
                                $master_sani->clientid = $clientid;
                                $master_sani->supplier = $values['2'];
                                $master_sani->first_name = $values['3'];
                                $master_sani->last_name = $values['4'];
                                $master_sani->salutation = $values['5'];
                                $master_sani->street1 = $values['6'];
                                $master_sani->zip = $values['7'];
                                $master_sani->fax = $values['12'];
                                $master_sani->email = $values['11'];
                                $master_sani->city = $values['8'];
                                $master_sani->indrop = 1;
                                $master_sani->phone = $values['9'];
                                $master_sani->comments =  $values['13'];
                                $master_sani->save();
                                $master_sani_id = $master_sani->id;
                                
                                // insert in Suppliers
                                $sani_cl = new PatientSuppliers();
                                $sani_cl->ipid = $ipid;
                                $sani_cl->supplier_id = $master_sani_id;
                                $sani_cl->supplier_comment =  $values['13'];
                                $sani_cl->save();
                                
                            } 
                            elseif($type == "hospizdienst")
                            {
                                
                                // insert in Suppliers (sonstige Versorger")
                                $master_hosp = new Hospiceassociation();
                                $master_hosp->clientid = $clientid;
                                $master_hosp->hospice_association = $values['2'];
                                $master_hosp->first_name = $values['3'];
                                $master_hosp->last_name = $values['4'];
                                $master_hosp->salutation = $values['5'];
                                $master_hosp->street1 = $values['6'];
                                $master_hosp->zip = $values['7'];
                                $master_hosp->fax = $values['12'];
                                $master_hosp->email = $values['11'];
                                $master_hosp->city = $values['8'];
                                $master_hosp->indrop = 1;
                                $master_hosp->phone_practice = $values['9'];
                                $master_hosp->phone_emergency = $values['10'];
                                $master_hosp->phone_private = $values['14'];
                                $master_hosp->comments =  $values['13'];
                                $master_hosp->save();
                                $master_hosp_id = $master_hosp->id;
                                
                                
                                
                                // insert in Suppliers
   				               $pat_hosp = new PatientHospiceassociation();
						       $pat_hosp->ipid = $ipid;
						       $pat_hosp->h_association_id = $master_hosp_id;
						       $pat_hosp->h_association_comment = $values['13'];
						       $pat_hosp->save();
                                
                            } 
                            elseif ($type == "facharzt" || $type == "kinderarzt")
                            {
                                // insert in specialists
                                $master_specialist = new Specialists();
                                $master_specialist->clientid = $clientid;
                                $master_specialist->practice = $values['2'];
                                $master_specialist->first_name = $values['3'];
                                $master_specialist->last_name = $values['4'];
                                $master_specialist->salutation = $values['5'];
                                $master_specialist->street1 = $values['6'];
                                $master_specialist->zip = $values['7'];
                                $master_specialist->city = $values['8'];
                                $master_specialist->phone_practice = $values['9'];
                                $master_specialist->fax = $values['12'];
                                $master_specialist->phone_private = $values['10'];
                                $master_specialist->phone_cell = $values['14'];;
                                
                                $master_specialist->email = $values['11'];
                                $master_specialist->comments = $values['13'];
                                $master_specialist->indrop = '1';
                                if($type == "facharzt"){
                                    $master_specialist->medical_speciality = "670";
                                } 
                                elseif($type == "kinderarzt")
                                {
                                  $master_specialist->medical_speciality = "671";
                                }
                                $master_specialist->save();
                                $master_specialist_id = $master_specialist->id;

                                // insert in patient
                                $sp_new = new PatientSpecialists();
                                $sp_new->ipid = $ipid;
                                $sp_new->sp_id = $master_specialist_id;
                                $sp_new->comment = $values['13'];
                                $sp_new->isdelete = "0";
                                $sp_new->save();
                            }
                            elseif ($type == "hausarzt")
                            {
                                // insert in  familydoc
                                $master_fam = new FamilyDoctor();
                                $master_fam->clientid = $clientid;
                                $master_fam->practice = $values['2'];
                                $master_fam->first_name = $values['3'];
                                $master_fam->last_name = $values['4'];
                                $master_fam->salutation = $values['5'];
                                $master_fam->street1 =  $values['6'];
                                $master_fam->zip = $values['7'];
                                $master_fam->city = $values['8'];
                                $master_fam->phone_practice = $values['9'];
                                $master_fam->phone_cell = $values['14'];
                                $master_fam->phone_private = $values['10'];
                                $master_fam->fax = $values['12'];
                                $master_fam->email= $values['11'];
                                $master_fam->comments = $values['13'];
                                $master_fam->indrop = 1;
                                $master_fam->save();
                                $master_fam_id = $master_fam->id;
                                
                                // update patient master
                                if($ipid){
                                    $pr = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                                    if($pr){
                                        $pr->familydoc_id= $master_fam_id;
                                        $pr->save();
                                    }
                                }
                            }
                            
                            else
                            {
                                // do nothing    
                            }
                        }
                    }
                }
            }
		    
		}
		
		
		public function stamdaten_import_handler_rp($csv_data, $post)
		{
			
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    $import_clients = array($clientid);
		    
		    
		    print_R($csv_data); exit;
		    
		    $stype= array(
		    		"Angehörige"=>"ContactPersonMaster", 
		    		"Apotheke"=>"Pharmacy",
		    		"Facharzt"=>"Specialists",
		    		"Hausarzt"=>"FamilyDoctor",
		    		"Hospizbegleiter"=>"Voluntaryworkers",
		    		"Krankenhaus"=>"Locations",
		    		"Pflege-/Altenheim"=>"Locations",
		    		"Pflegedienst"=>"Pflegedienstes",
		    		"Sanitätshaus"=>"Supplies",
		    		"Sonstige"=>"Suppliers",
		    		"Therapeut"=>"Physiotherapists"
		    );
		    $allowed  = array("Locations");
		    
		    $salutation = array(
		    		"" => "",
		    		"0" => "",
		    		"1" => "Herr",
		    		"2" => "Frau",
		    		"3" => "Fräulein");
		    
		    

		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		    	$patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		    	$ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		    	$patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		    	$patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    foreach($csv_data as $csv_row=>$csv_line){
		    	if($csv_row !=  0 ){
	    			if(isset($patients_array[$csv_line[0]]['ipid'])){
		    			$data2patients[$patients_array[$csv_line[0]]['ipid']] [$stype[$csv_line[1]]][] = $csv_line;
// 						if(in_array($stype[$csv_line[1]],$allowed  )){
// 			    			$data2patients[$patients_array[$csv_line[0]]['ipid']] [$stype[$csv_line[1]] ][] = $csv_line;
// 			    			if(empty($csv_line[3])){
// 			    				$locations[$csv_line[2]]= $csv_line;
// 			    			}
// 						}
	    			}
		    	}
		    	
		    }
		    
// 		    foreach($locations as $loc_name =>$values){

// 		    	$master_loc = new Locations();
// 		    	$master_loc->client_id = $clientid;
// 		    	$master_loc->location = Pms_CommonData::aesEncrypt($values['2']);  ;
// 				if($values['1'] == "Krankenhaus"){
// 		    		$master_loc->location_type = "1";
// 				} else{
// 		    		$master_loc->location_type = "3";
// 				}
// 		    	$master_loc->street = $values['6'];
// 		    	$master_loc->zip = $values['7'];
// 		    	$master_loc->fax = $values['12'];
// 		    	$master_loc->email = $values['11'];
// 		    	$master_loc->city = $values['8'];
// 		    	$master_loc->phone1 = $values['9'];
// 		    	$master_loc->phone2 = $values['10'];
// 		    	$master_loc->comment = $values['13'];
// 		    	$master_loc->save();
		    	
// 		    }
// // 		    print_r($locations); 
// 		    print_r($data2patients); 
// 		    exit;
            foreach($data2patients as $ipid => $type_data){
                if($ipid)
                {
                    foreach($type_data as $type => $type_details){
                        
                        $types_array[] = $type;
                        foreach($type_details as $k=>$values){

                            if($type == "Pflegedienstes")
                            {
                                //  insert in pflegedienst
                                $master_pfl = new Pflegedienstes();
                                $master_pfl->nursing = $values['2'];
                                $master_pfl->first_name = $values['3'];
                                $master_pfl->last_name = $values['4'];
                                $master_pfl->salutation = $salutation[$values['5']];
                                $master_pfl->street1 = $values['6'];
                                $master_pfl->zip = $values['7'];
                                $master_pfl->fax = $values['12'];
                                $master_pfl->email = $values['11'];
                                $master_pfl->city = $values['8'];
                                $master_pfl->phone_practice = $values['9'];
                                $master_pfl->phone_emergency = $values['10'];
                                $master_pfl->comments = $values['13'];
                                $master_pfl->indrop = 1;
                                $master_pfl->clientid = $clientid;
                                $master_pfl->save();
                                $master_pfl_id = $master_pfl->id;

                                // insert in patient pflegedienst
                                $pfl_cl = new PatientPflegedienste();
                                $pfl_cl->ipid = $ipid;
                                $pfl_cl->pflid = $master_pfl_id ;
                                $pfl_cl->pflege_comment = $values['13'];
                                $pfl_cl->save();
    						  
                            } 
                            elseif($type == "Pharmacy")
                            {
                            // insert in pharmacy
                                $master_ph = new Pharmacy();
                                $master_ph->pharmacy = $values['2'];
                                $master_ph->first_name = $values['3'];
                                $master_ph->last_name = $values['4'];
                                $master_ph->salutation = $salutation[$values['5']];
                                $master_ph->street1 = $values['6'];
                                $master_ph->zip = $values['7'];
                                $master_ph->fax = $values['12'];
                                $master_ph->email = $values['11'];
                                $master_ph->city = $values['8'];
                                $master_ph->phone = $values['9'];
                                $master_ph->comments = $values['13'];
                                $master_ph->indrop = 1;
                                $master_ph->clientid = $clientid;
                                $master_ph->save();
                                $master_ph_id = $master_ph->id;
    
                            // insert in patient pharmacy
                              $phar_cl = new PatientPharmacy();
    						  $phar_cl->ipid = $ipid;
    						  $phar_cl->pharmacy_id = $master_ph_id ;
    						  $phar_cl->pharmacy_comment = $values['13'];
    						  $phar_cl->save();
                            } 
                            elseif($type == "Physiotherapists")
                            {
                                //  insert in physiotherapie
                                $master_phys = new Physiotherapists();
                                $master_phys->clientid = $clientid;
                                $master_phys->physiotherapist = $values['2'];
                                $master_phys->first_name = $values['3'];;
                                $master_phys->last_name = $values['4'];
                                $master_phys->salutation = $salutation[$values['5']];
                                $master_phys->street1 = $values['6'];
                                $master_phys->zip = $values['7'];
                                $master_phys->fax = $values['12'];
                                $master_phys->email = $values['11'];
                                $master_phys->city = $values['8'];
                                $master_phys->indrop = 1;
                                $master_phys->phone_practice = $values['9'];
                                $master_phys->phone_emergency =  $values['10'];
                                $master_phys->comments =  $values['13'];
                                $master_phys->save();
                                $master_phys_id = $master_phys->id;
                                
    
                                // insert in Physiotherapist
                                $phy_cl = new PatientPhysiotherapist();
                                $phy_cl->ipid = $ipid;
                                $phy_cl->physioid = $master_phys_id;
                                $phy_cl->physio_comment = $values['13'];
                                $phy_cl->save();
                                
                                
                            } 
                            elseif($type == "Suppliers")
                            {
                            // insert in Suppliers (sonstige Versorger")
                            
                                $master_sonst = new Suppliers();
                                $master_sonst->clientid = $clientid;
                                $master_sonst->supplier = $values['2'];
                                $master_sonst->first_name = $values['3'];
                                $master_sonst->last_name = $values['4'];
                                $master_sonst->salutation = $salutation[$values['5']];
                                $master_sonst->street1 = $values['6'];
                                $master_sonst->zip = $values['7'];
                                $master_sonst->fax = $values['12'];
                                $master_sonst->email = $values['11'];
                                $master_sonst->city = $values['8'];
                                $master_sonst->indrop = 1;
                                $master_sonst->phone = $values['9'];
                                $master_sonst->comments =  $values['13'];
                                $master_sonst->save();
                                $master_sonst_id = $master_sonst->id;
                                
                                //  insert in Suppliers
                                $pfl_sonst = new PatientSuppliers();
                                $pfl_sonst->ipid = $ipid;
                                $pfl_sonst->supplier_id = $master_sonst_id;
                                $pfl_sonst->supplier_comment =  $values['13'];
                                $pfl_sonst->save();
                                
                            } 
                            elseif($type == "Supplies")
                            {
                                
                                // insert in Suppliers (sonstige Versorger")
                                $master_sani = new Supplies();
                                $master_sani->clientid = $clientid;
                                $master_sani->supplier = $values['2'];
                                $master_sani->first_name = $values['3'];
                                $master_sani->last_name = $values['4'];
                                $master_sani->salutation = $salutation[$values['5']];
                                $master_sani->street1 = $values['6'];
                                $master_sani->zip = $values['7'];
                                $master_sani->fax = $values['12'];
                                $master_sani->email = $values['11'];
                                $master_sani->city = $values['8'];
                                $master_sani->indrop = 1;
                                $master_sani->phone = $values['9'];
                                $master_sani->comments =  $values['13'];
                                $master_sani->save();
                                $master_sani_id = $master_sani->id;
                                
                                // insert in Suppliers
                                $sani_cl = new PatientSuppliers();
                                $sani_cl->ipid = $ipid;
                                $sani_cl->supplier_id = $master_sani_id;
                                $sani_cl->supplier_comment =  $values['13'];
                                $sani_cl->save();
                                
                            } 
                            elseif($type == "Hospiceassociation")
                            {
                                
                                // insert in Suppliers (sonstige Versorger")
                                $master_hosp = new Hospiceassociation();
                                $master_hosp->clientid = $clientid;
                                $master_hosp->hospice_association = $values['2'];
                                $master_hosp->first_name = $values['3'];
                                $master_hosp->last_name = $values['4'];
                                $master_hosp->salutation = $salutation[$values['5']];
                                $master_hosp->street1 = $values['6'];
                                $master_hosp->zip = $values['7'];
                                $master_hosp->fax = $values['12'];
                                $master_hosp->email = $values['11'];
                                $master_hosp->city = $values['8'];
                                $master_hosp->indrop = 1;
                                $master_hosp->phone_practice = $values['9'];
                                $master_hosp->phone_emergency = $values['10'];
                                $master_hosp->phone_private = $values['14'];
                                $master_hosp->comments =  $values['13'];
                                $master_hosp->save();
                                $master_hosp_id = $master_hosp->id;
                                
                                
                                
                                // insert in Suppliers
   				               $pat_hosp = new PatientHospiceassociation();
						       $pat_hosp->ipid = $ipid;
						       $pat_hosp->h_association_id = $master_hosp_id;
						       $pat_hosp->h_association_comment = $values['13'];
						       $pat_hosp->save();
                                
                            } 
                            elseif ($type == "Specialists")
                            {
                                // insert in specialists
                                $master_specialist = new Specialists();
                                $master_specialist->clientid = $clientid;
                                $master_specialist->practice = $values['2'];
                                $master_specialist->first_name = $values['3'];
                                $master_specialist->last_name = $values['4'];
                                $master_specialist->salutation = $salutation[$values['5']];
                                $master_specialist->street1 = $values['6'];
                                $master_specialist->zip = $values['7'];
                                $master_specialist->city = $values['8'];
                                $master_specialist->phone_practice = $values['9'];
                                $master_specialist->fax = $values['12'];
                                $master_specialist->phone_private = $values['10'];
                                $master_specialist->phone_cell = $values['14'];;
                                
                                $master_specialist->email = $values['11'];
                                $master_specialist->comments = $values['13'];
                                $master_specialist->indrop = '1';
                                if($type == "Specialists"){
                                    $master_specialist->medical_speciality = "1139";
                                } 
                                $master_specialist->save();
                                $master_specialist_id = $master_specialist->id;

                                // insert in patient
                                $sp_new = new PatientSpecialists();
                                $sp_new->ipid = $ipid;
                                $sp_new->sp_id = $master_specialist_id;
                                $sp_new->comment = $values['13'];
                                $sp_new->isdelete = "0";
                                $sp_new->save();
                            }
                            elseif ($type == "FamilyDoctor")
                            {
                                // insert in  familydoc
                                $master_fam = new FamilyDoctor();
                                $master_fam->clientid = $clientid;
                                $master_fam->practice = $values['2'];
                                $master_fam->first_name = $values['3'];
                                $master_fam->last_name = $values['4'];
                                $master_fam->salutation = $salutation[$values['5']];
                                $master_fam->street1 =  $values['6'];
                                $master_fam->zip = $values['7'];
                                $master_fam->city = $values['8'];
                                $master_fam->phone_practice = $values['9'];
                                $master_fam->phone_cell = $values['14'];
                                $master_fam->phone_private = $values['10'];
                                $master_fam->fax = $values['12'];
                                $master_fam->email= $values['11'];
                                $master_fam->comments = $values['13'];
                                $master_fam->indrop = 1;
                                $master_fam->save();
                                $master_fam_id = $master_fam->id;
                                
                                // update patient master
                                if($ipid){
                                    $pr = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                                    if($pr){
                                        $pr->familydoc_id= $master_fam_id;
                                        $pr->save();
                                    }
                                }
                            }
                            elseif ($type == "ContactPersonMaster")
                            {
                            	$master_cpm = new ContactPersonMaster();
                            	$master_cpm->ipid = $ipid;
                            	$master_cpm->cnt_first_name = Pms_CommonData::aesEncrypt($values['3']);
                            	$master_cpm->cnt_last_name = Pms_CommonData::aesEncrypt($values['4']);
                            	
                            	$master_cpm->cnt_street1 = Pms_CommonData::aesEncrypt($salutation[$values['6']]);
                            	$master_cpm->cnt_zip = Pms_CommonData::aesEncrypt($values['7']);
                            	$master_cpm->cnt_city = Pms_CommonData::aesEncrypt($values['8']);
                            	
                            	$master_cpm->cnt_phone = Pms_CommonData::aesEncrypt($values['9']);
                            	$master_cpm->cnt_mobile = Pms_CommonData::aesEncrypt($values['10']);
                            	$master_cpm->cnt_email = Pms_CommonData::aesEncrypt($values['11']);
                            	
                            	
                            	$master_cpm->cnt_comment = Pms_CommonData::aesEncrypt($values['13']);
                            	$master_cpm->save();
           
                            }
                            elseif ($type == "Voluntaryworkers")
                            {
                            	// check in voluntary exists in master voluntary.
                            	if ($clientid > 0)
                            	{
                            		$where = ' and clientid=' . $clientid;
                            	}
                            	else
                            	{
                            		$where = ' and clientid=0';
                            	}
                            	
                            	$fdoc1 = Doctrine_Query::create();
                            	$fdoc1->select('id');
                            	$fdoc1->from('Voluntaryworkers');
                            	$fdoc1->where("isdelete = 0  " . $where);
                            	$fdoc1->andWhere("indrop = 0  ");
                           		$fdoc1->andWhere("first_name like '%" . trim($values['3']) . "%'  AND last_name like '%" . trim($values['4']) . "%'   ");
                            	$fdoc1->limit("1");
                            	$exisitng = $fdoc1->fetchArray();
                            	
                            	$parent_id = 0;
                            	if(!empty($exisitng)){
	                            	foreach($exisitng as $f=>$v){
	                            		$parent_id  = $v['id'];
	                            	}
                            	}
                            	
                                // insert in  Voluntaryworkers
                                $master_vw = new Voluntaryworkers();
                                $master_vw->clientid = $clientid;
                                $master_vw->parent_id = $parent_id;
                                $master_vw->status = "e";//Hospizbegleiter
                                $master_vw->first_name = $values['3'];
                                $master_vw->last_name = $values['4'];
                                $master_vw->salutation = $salutation[$values['5']];
                                $master_vw->street =  $values['6'];
                                $master_vw->zip = $values['7'];
                                $master_vw->city = $values['8'];
                                $master_vw->phone = $values['9'];
                                $master_vw->mobile = $values['14'];
                                $master_vw->email= $values['11'];
                                $master_vw->comments = $values['13'];
                                $master_vw->indrop = 1;
                                $master_vw->save();
                                $master_vw_id = $master_vw->id;
                                
                                // insert in patient
                                $vw_new = new PatientVoluntaryworkers();
                                $vw_new->ipid = $ipid;
                                $vw_new->vwid = $master_vw_id;
                                $vw_new->vw_comment = $values['13'];
                                $vw_new->isdelete = "0";
                                $vw_new->save();
                            }
                            elseif ($type == "Locations")
                            {
                            	
//                             	$inserted_locations[] = $values['2'];
//                                 $master_loc = new Locations();
//                                 $master_loc->clientid = $clientid;
//                                 $master_loc->parent_id = $parent_id;
//                                 $master_loc->status = "e";//Hospizbegleiter
//                                 $master_loc->first_name = $values['3'];
//                                 $master_loc->last_name = $values['4'];
//                                 $master_loc->salutation = $salutation[$values['5']];
//                                 $master_loc->street =  $values['6'];
//                                 $master_loc->zip = $values['7'];
//                                 $master_loc->city = $values['8'];
//                                 $master_loc->phone = $values['9'];
//                                 $master_loc->mobile = $values['14'];
//                                 $master_loc->email= $values['11'];
//                                 $master_loc->comments = $values['13'];
//                                 $master_loc->indrop = 1;
//                                 $master_loc->save();
                                
                            }
                            
                            else
                            {
                                // do nothing    
                            }
                        }
                    }
                }
            }
		}
 
		
		public function stamdaten_import_handler_wlk($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;

		    $import_clients = array($clientid);
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    
		    
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
		        if(strlen($read_data[14])>0){
    		        $data2patients[$patients_array[$read_data[0]]['ipid']][ strtolower(trim($read_data[14]))][] = $read_data;
		        }
		    }
		    
// 		    print_r($patients_array); 
// 		    print_r($data2patients); 
// 		    exit;
		    
// 		    print_r($data2patients); exit;
            foreach($data2patients as $ipid => $type_data){
                if($ipid)
                {
                    foreach($type_data as $type => $type_details){
                        
                        $types_array[] = $type;
                        foreach($type_details as $k=>$values){

                            if($type == "pflegedienst_null")
                            {
                                //  insert in pflegedienst
                                $master_pfl = new Pflegedienstes();
                                $master_pfl->nursing = $values['2'];
                                $master_pfl->first_name = $values['3'];
                                $master_pfl->last_name = $values['4'];
                                $master_pfl->salutation = $values['5'];
                                $master_pfl->street1 = $values['6'];
                                $master_pfl->zip = $values['7'];
                                $master_pfl->fax = $values['12'];
                                $master_pfl->email = $values['11'];
                                $master_pfl->city = $values['8'];
                                $master_pfl->phone_practice = $values['9'];
                                $master_pfl->phone_emergency = $values['10'];
                                $master_pfl->comments = $values['13'];
                                $master_pfl->indrop = 1;
                                $master_pfl->clientid = $clientid;
                                $master_pfl->save();
                                $master_pfl_id = $master_pfl->id;

                                // insert in patient pflegedienst
                                $pfl_cl = new PatientPflegedienste();
                                $pfl_cl->ipid = $ipid;
                                $pfl_cl->pflid = $master_pfl_id ;
                                $pfl_cl->pflege_comment = $values['13'];
                                $pfl_cl->save();
    						  
                            } 
                            elseif($type == "apotheke")
                            {
                            // insert in pharmacy
                                $master_ph = new Pharmacy();
                                $master_ph->pharmacy = $values['2'];
//                                 $master_ph->first_name = $values['2'];
                                $master_ph->last_name = $values['15'];
//                                 $master_ph->salutation = $values['5'];
                                $master_ph->street1 = $values['4'];
                                $master_ph->zip = $values['5'];
                                $master_ph->city = $values['6'];
                                $master_ph->phone = $values['7'];
                                $master_ph->email = $values['9'];
                                $master_ph->fax = $values['16'];
                                $master_ph->comments = $values['17'];
                                
                                $master_ph->indrop = 1;
                                $master_ph->clientid = $clientid;
                                $master_ph->save();
                                $master_ph_id = $master_ph->id;
    
                            // insert in patient pharmacy
                              $phar_cl = new PatientPharmacy();
    						  $phar_cl->ipid = $ipid;
    						  $phar_cl->pharmacy_id = $master_ph_id ;
   						      $phar_cl->pharmacy_comment = $values['17'];
    						  
    						  $phar_cl->save();
                            } 
                            elseif($type == "physiotherapeut")
                            {
                                //  insert in physiotherapie
                                $master_phys = new Physiotherapists();
                                $master_phys->clientid = $clientid;
                                $master_phys->physiotherapist = $values['2'];
//                                 $master_phys->first_name = $values['2'];
                                $master_phys->last_name = $values['15'];
                                //$master_phys->salutation = $values['5'];
                                $master_phys->street1 = $values['4'];
                                $master_phys->zip = $values['5'];
                                $master_phys->city = $values['6'];
                                
                                $master_phys->phone_practice = $values['7'];
                                $master_phys->phone_emergency =  $values['8'];
                                $master_phys->email = $values['9'];
                                $master_phys->fax = $values['16'];
                                $master_phys->comments = $values['17'];
                                $master_phys->indrop = 1;
                                
                                $master_phys->save();
                                $master_phys_id = $master_phys->id;
                                
    
                                // insert in Physiotherapist
                                $phy_cl = new PatientPhysiotherapist();
                                $phy_cl->ipid = $ipid;
                                $phy_cl->physioid = $master_phys_id;
                                $phy_cl->physio_comment  = $values['17'];
                                
                                
                                $phy_cl->save();
                                
                                
                            } 
                            elseif($type == "sonstige_null")
                            {
                            // insert in Suppliers (sonstige Versorger")
                            
                                $master_sonst = new Suppliers();
                                $master_sonst->clientid = $clientid;
                                $master_sonst->supplier = $values['2'];
                                $master_sonst->first_name = $values['3'];
                                $master_sonst->last_name = $values['4'];
                                $master_sonst->salutation = $values['5'];
                                $master_sonst->street1 = $values['6'];
                                $master_sonst->zip = $values['7'];
                                $master_sonst->fax = $values['12'];
                                $master_sonst->email = $values['11'];
                                $master_sonst->city = $values['8'];
                                $master_sonst->indrop = 1;
                                $master_sonst->phone = $values['9'];
                                $master_sonst->comments =  $values['13'];
                                $master_sonst->save();
                                $master_sonst_id = $master_sonst->id;
                                
                                //  insert in Suppliers
                                $pfl_sonst = new PatientSuppliers();
                                $pfl_sonst->ipid = $ipid;
                                $pfl_sonst->supplier_id = $master_sonst_id;
                                $pfl_sonst->supplier_comment =  $values['13'];
                                $pfl_sonst->save();
                                
                            } 
                            elseif($type == "sanitätshaus")
                            {
                                
                                // insert in Suppliers (sonstige Versorger")
                                $master_sani = new Supplies();
                                $master_sani->clientid = $clientid;
                                
                                $master_sani->supplier = $values['2'];
//                                 $master_sani->first_name = $values['2'];
                                $master_sani->last_name = $values['15'];
                                //$master_sani->salutation = $values['5'];
                                $master_sani->street1 = $values['4'];
                                $master_sani->zip = $values['5'];
                                $master_sani->city = $values['6'];
                                $master_sani->phone = $values['7'];
                                $master_sani->email = $values['9'];
                                $master_sani->fax = $values['16'];
                                $master_sani->comments   = $values['17'];

                                $master_sani->indrop = 1;
                                $master_sani->save();
                                
                                
                                $master_sani_id = $master_sani->id;
                                
                                
                                
                                // insert in Suppliers
                                $sani_cl = new PatientSupplies();
                                $sani_cl->ipid = $ipid;
                                $sani_cl->supplier_id = $master_sani_id;
                                $sani_cl->supplier_comment  = $values['17'];
                                
                                $sani_cl->save();
                                
                            } 
                            elseif($type == "hospizdienst_null")
                            {
                                
                                // insert in Suppliers (sonstige Versorger")
                                $master_hosp = new Hospiceassociation();
                                $master_hosp->clientid = $clientid;
                                $master_hosp->hospice_association = $values['2'];
                                $master_hosp->first_name = $values['3'];
                                $master_hosp->last_name = $values['4'];
                                $master_hosp->salutation = $values['5'];
                                $master_hosp->street1 = $values['6'];
                                $master_hosp->zip = $values['7'];
                                $master_hosp->fax = $values['12'];
                                $master_hosp->email = $values['11'];
                                $master_hosp->city = $values['8'];
                                $master_hosp->indrop = 1;
                                $master_hosp->phone_practice = $values['9'];
                                $master_hosp->phone_emergency = $values['10'];
                                $master_hosp->phone_private = $values['14'];
                                $master_hosp->comments =  $values['13'];
                                $master_hosp->save();
                                $master_hosp_id = $master_hosp->id;
                                
                                
                                
                                // insert in Suppliers
   				               $pat_hosp = new PatientHospiceassociation();
						       $pat_hosp->ipid = $ipid;
						       $pat_hosp->h_association_id = $master_hosp_id;
						       $pat_hosp->h_association_comment = $values['13'];
						       $pat_hosp->save();
                                
                            } 
                            elseif ($type == "facharzt_null" || $type == "kinderarzt_null")
                            {
                                // insert in specialists
                                $master_specialist = new Specialists();
                                $master_specialist->clientid = $clientid;
                                $master_specialist->practice = $values['2'];
                                $master_specialist->first_name = $values['3'];
                                $master_specialist->last_name = $values['4'];
                                $master_specialist->salutation = $values['5'];
                                $master_specialist->street1 = $values['6'];
                                $master_specialist->zip = $values['7'];
                                $master_specialist->city = $values['8'];
                                $master_specialist->phone_practice = $values['9'];
                                $master_specialist->fax = $values['12'];
                                $master_specialist->phone_private = $values['10'];
                                $master_specialist->phone_cell = $values['14'];;
                                
                                $master_specialist->email = $values['11'];
                                $master_specialist->comments = $values['13'];
                                $master_specialist->indrop = '1';
                                if($type == "facharzt"){
                                    $master_specialist->medical_speciality = "670";
                                } 
                                elseif($type == "kinderarzt")
                                {
                                  $master_specialist->medical_speciality = "671";
                                }
                                $master_specialist->save();
                                $master_specialist_id = $master_specialist->id;

                                // insert in patient
                                $sp_new = new PatientSpecialists();
                                $sp_new->ipid = $ipid;
                                $sp_new->sp_id = $master_specialist_id;
                                $sp_new->comment = $values['13'];
                                $sp_new->isdelete = "0";
                                $sp_new->save();
                            }
                            elseif ($type == "hausarzt")
                            {
                                // insert in  familydoc
                                $master_fam = new FamilyDoctor();
                                $master_fam->clientid = $clientid;
                                $master_fam->practice = $values['2'];
//                                 $master_fam->first_name = $values['2'];
                                $master_fam->last_name = $values['15'];
                                //$master_fam->salutation = $values['5'];
                                $master_fam->street1 =  $values['4'];
                                $master_fam->zip = $values['5'];
                                $master_fam->city = $values['6'];
                                $master_fam->phone_practice = $values['7'];
                                $master_fam->phone_cell = $values['8'];
                                $master_fam->email= $values['9'];
//                                 $master_fam->phone_private = $values['10'];
                                $master_fam->fax = $values['16'];
                                $master_fam->comments  = $values['17'];
                                
                                $master_fam->indrop = 1;
                                $master_fam->save();
                                $master_fam_id = $master_fam->id;
                                
                                // update patient master
                                if($ipid){
                                    $pr = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                                    if($pr){
                                        $pr->familydoc_id= $master_fam_id;
                                        $pr->save();
                                    }
                                }
                            }
                            
                            else
                            {
                                // do nothing    
                            }
                        }
                    }
                }
            }
		    
		}
		
		
		
		public function contact_persons_import_handler_wlk($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
// 		    print_r($csv_data); exit;
		    $import_clients = array($clientid);
		    
		    
		    $fdoc = Doctrine_Query::create()
		    ->select('*')
		    ->from('FamilyDegree')
		    ->where('isdelete=0')
		    ->andwhere('clientid=' . $logininfo->clientid)
		    ->orderBy('family_degree ASC');
		    $loc = $fdoc->fetchArray();
		    
		    foreach($loc as $k=> $fd){
		        $degree[$fd['family_degree']] = $fd['id'];
		    }
		    
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    
		    
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
    		        $data2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		    }
		    
		    
            foreach($data2patients as $ipid => $cp_data){
                if($ipid)
                {
                    foreach($cp_data as $k=>$values){
                        //  insert in pflegedienst
                        $master_cpm = new ContactPersonMaster();
                        $master_cpm->ipid = $ipid;
                        $master_cpm->cnt_first_name = Pms_CommonData::aesEncrypt($values['2']);
                        $master_cpm->cnt_last_name = Pms_CommonData::aesEncrypt($values['3']);
                        
                        $master_cpm->cnt_street1 = Pms_CommonData::aesEncrypt($values['5']);
                        $master_cpm->cnt_zip = Pms_CommonData::aesEncrypt($values['6']);
                        $master_cpm->cnt_city = Pms_CommonData::aesEncrypt($values['7']);
                        
                        $master_cpm->cnt_phone = Pms_CommonData::aesEncrypt($values['8']);
                        $master_cpm->cnt_mobile = Pms_CommonData::aesEncrypt($values['9']);
                        $master_cpm->cnt_email = Pms_CommonData::aesEncrypt($values['10']);
                        if($degree[$values['11']]){
                            $master_cpm->cnt_familydegree_id = $degree[$values['11']];
                        }
                        
                        $master_cpm->cnt_comment = Pms_CommonData::aesEncrypt($values['18']);
                        $master_cpm->save();
                        $master_pfl_id = $master_cpm->id;
                   }
                }
            }
		    
		}
		
		
		public function verlauf_import_handler_wlk($csv_data, $post,$shortcut = "K",$weight = false,$extra_cols = false)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
// 		    print_r($csv_data); exit;
		    $import_clients = array($clientid);
		    
		    
		    
		    $modules = new Modules();
		    $b_vitalsigns_module = $modules->checkModulePrivileges("117", $clientid);
		    
		    
		    if($b_vitalsigns_module)
		    {
		        $vitalsigns_shortcut = "B";
		    }
		    else
		    {
		        $vitalsigns_shortcut = "K";
		    }
		    
		    $fdoc = Doctrine_Query::create()
		    ->select('id,import_id')
		    ->from('User')
		    ->where('isdelete = 0')
		    ->andwhere('clientid =' . $logininfo->clientid);
		    $loc = $fdoc->fetchArray();
		    
		    foreach($loc as $k=> $fd){
		        $user[trim($fd['import_id'])] = $fd['id'];
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }

		    
		    foreach($csv_data as $rk => $read_data){
    		      $data2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		    }

            foreach($data2patients as $ipid => $cp_data){
                if($ipid)
                {
                    foreach($cp_data as $k=>$values)
                    {
                        if($weight == "1" ){
                            $done_date = date('Y-m-d H:i:s', strtotime($values['4']));
                            
                            $vitals = new FormBlockVitalSigns();
                            $vitals->ipid = $ipid;
                            $vitals->contact_form_id = "0";
                            $vitals->source = "icon";
                            $vitals->signs_date = date('Y-m-d H:i:s', strtotime($values['4']));
                            $vitals->weight = Pms_CommonData::str2num($values['2']);
                            $vitals->save();
                            
                            $signs_date = date('d.m.Y H:i', strtotime($values['4']));
                            
                            if($values['2'] > '0')
                            {
                                $tocourse['weight'] =  " Gewicht: ". Pms_CommonData::str2num($values['2']) ." Kg" ;
                            }
                 
                            if(!empty($tocourse))
                            {
                                $coursecomment = " Vitalwerte: Datum: ". $signs_date ." ". implode(',',$tocourse);
                            }
                            //print_r($coursecomment);exit;
                            if(!empty($coursecomment) )
                            {
                                $cust = new PatientCourse();
                                $cust->ipid = $ipid;
                                $cust->course_date = date("Y-m-d H:i:s", strtotime($values['4']));
                                $cust->course_type = Pms_CommonData::aesEncrypt($vitalsigns_shortcut);
                                $cust->course_title = Pms_CommonData::aesEncrypt($coursecomment);
                                $cust->isserialized = 1;
                                if($user[$values['3']]) {
                                    $cust->user_id = $user[$values['3']];
                                } else {
                                    $cust->user_id = $userid;
                                }
                                $cust->done_date = $done_date;
                                $cust->done_name = Pms_CommonData::aesEncrypt("vital_signs_import");
                                $cust->tabname = Pms_CommonData::aesEncrypt("wlk_import");
                                $cust->done_id = "0";
                                $cust->save();
                            }
                        }
                        elseif($extra_cols=="1")
                        {
                            
                            $cust = new PatientCourse();
                            $cust->ipid = $ipid;
                            if(strlen($values['3']) > 0 ){
                                $cust->course_date = date("Y-m-d H:i:s",strtotime($values['3']));
                                $cust->done_date = date("Y-m-d H:i:s",strtotime($values['3']));
                            } else{
                                $cust->course_date = date("Y-m-d H:i:s",strtotime($values['5']));
                                $cust->done_date = date("Y-m-d H:i:s",strtotime($values['5']));
                            }
                            $cust->course_type = Pms_CommonData::aesEncrypt($shortcut);
                            $cust->course_title = Pms_CommonData::aesEncrypt($values['2']);
                            
                            
                            if($user[$values['6']]) {
                                $cust->user_id = $user[$values['6']];
                            } else {
                                $cust->user_id = $userid;
                            }
                            $cust->tabname = Pms_CommonData::aesEncrypt("wlk_import");
                            $cust->save();
                            
                        } 
                        else 
                        {
                            if(!empty($values['2']) && strlen($values['2']) > 0)
                            {
                                $cust = new PatientCourse();
                                $cust->ipid = $ipid;
                                $cust->course_date = date("Y-m-d H:i:s",strtotime($values['4']));
                                $cust->course_type = Pms_CommonData::aesEncrypt($shortcut);
                                $cust->course_title = Pms_CommonData::aesEncrypt('Procedere: '.$values['2']);
                                $cust->done_date = date("Y-m-d H:i:s",strtotime($values['4']));
                                if($user[$values['3']]) {
                                    $cust->user_id = $user[$values['3']];
                                } else {
                                    $cust->user_id = $userid;
                                }
                                $cust->tabname = Pms_CommonData::aesEncrypt("wlk_import");
                                $cust->save();
                            }
                        }
                   }
                }
            }
		}
		
		public function diagnosis_import_handler_wlk($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
// 		    print_r($csv_data); exit;
		    $import_clients = array($clientid);
		    

		    // get diagnosis types
		    $dg = new DiagnosisType();
		    $abb2 = "'HD','ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    $comma = ",";
		    $typeid = "'0'";
		    print_r($ddarr2);  
		    foreach($ddarr2 as $key => $valdia)
		    {
		        if($valdia['abbrevation'] == "HD"){
		            $type['H'] = $valdia['id'];
		        }
		        elseif($valdia['abbrevation'] == "ND"){
		            $type['N'] = $valdia['id'];
		        }
		    }
 
		    
		    $fdoc = Doctrine_Query::create()
		    ->select('id,import_id')
		    ->from('User')
		    ->where('isdelete = 0')
		    ->andwhere('clientid =' . $logininfo->clientid);
		    $loc = $fdoc->fetchArray();
		    
		    foreach($loc as $k=> $fd){
		        $user[trim($fd['import_id'])] = $fd['id'];
		    }
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    foreach($csv_data as $rk => $read_data){
    		      $data2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		    }
		    
            foreach($data2patients as $ipid => $dia_data)
            {
                if($ipid)
                {
                    foreach($dia_data as $k=>$values)
                    {
                        $diagno_free = new DiagnosisText();
                        $diagno_free->clientid = $clientid;
                        $diagno_free->icd_primary = $values['2'];
                        $diagno_free->free_name = $values['3'];
                        $diagno_free->save();
                        $free_diagno_id = $diagno_free->id;
                        
                        $diagno = new PatientDiagnosis();
                        $diagno->ipid = $ipid;
                        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
                        $diagno->diagnosis_type_id = $type[$values['1']];
                        $diagno->diagnosis_id = $free_diagno_id;
                        $diagno->icd_id = "0";
    
                        if($user[$values['5']]) {
                            $diagno->create_user = $user[$values['5']];
                        } else {
                            $diagno->create_user = $userid;
                        }
                        
                        $diagno->create_date = date('Y-m-d H:i:s', strtotime($values['6']));
                        $diagno->save();
                   }
                }
            }
		}
		
		public function normal_medication_import_handler_wlk($csv_data, $post)
		{ 
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
// 		    print_R($csv_data); exit;
		    $import_clients = array($clientid);
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        
		        $all_patients[] = $pat_val['ipid'];
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }

		    if(empty($all_patients)){
		        $all_patients[] = "999999999";
		    }
		    
		    // get all patients that have medication
		    $patient_drugs = Doctrine_Query::create()
		    ->select("ipid")
		    ->from('PatientDrugPlan')
		    ->whereIn('ipid', $all_patients);
		    $patient_drug_details = $patient_drugs->fetchArray();
		    
		    foreach($patient_drug_details as $k=>$mi){
		        $patients_with_medications[] = $mi['ipid'];
		    }
		    
		    $patient_drugs_dosage = Doctrine_Query::create()
		    ->select("ipid")
		    ->from('PatientDrugPlanDosageIntervals')
		    ->whereIn('ipid', $all_patients);
		    $patient_drug_dosage_details = $patient_drugs_dosage->fetchArray();
		    foreach($patient_drug_dosage_details as $lo=>$dm){
		          $patients_with_medications[] = $dm['ipid'];
		    }
	 
		    $patients_with_medications = array_unique($patients_with_medications);
		    
		    
		    
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
		        $data2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		        $users[] = trim($read_data[2]);
		    }
		    $cat_array = array_unique($cat); 
		    $users_array = array_unique($users); 
// 		    print_r($data2patients); exit;
 
		    $usr = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where("clientid ='" . $clientid. "'")
		    ->orderby("last_name ASC");
		    $dr = $usr->execute();
		   
		    foreach($dr as  $k=>$user_details){
		        $userln2id[$user_details['last_name']] = $user_details['id'];
		        $user[trim($fd['import_id'])] = $user_details['id'];
		    }

		    foreach($data2patients as $ipid => $med_data)
		    {
		        if($ipid)
		        {
		            foreach($med_data as $k_row => $med)
		            {
		                $dosage_input = "";
		                
                        if(strlen($med[1])){ // add medication only if we have medication name

                            
                            $medication_array[$ipid][$k_row]['name'] = $med[1];
                            
                            $dosage_input = $med[5];
                            
                            $dosage_input = str_replace( array('[',']') ,'', $dosage_input);
                            $dosage_input = str_replace( array('","','&quot;,&quot;','&quot;')  ,array('|','|',''), $dosage_input);
                            $dosage_input = explode('|',$dosage_input);
                            
                            foreach($dosage_input as $td){
                                $dosage_t = explode(';',$td);
                                $medication_array[$ipid][$k_row]['dosage'][date('H:i',strtotime($dosage_t[0]))]=$dosage_t[1];
                                
                            }
                            $medication_array[$ipid][$k_row]['dosage_input'] = $med[5];
                            $medication_array[$ipid][$k_row]['date'] = $med[7];
                            
                            if($user[$med[6]]) {
                                $medication_array[$ipid][$k_row]['create_user'] = $user[$med[6]];
                            } else {
                                $medication_array[$ipid][$k_row]['create_user'] = $userid;
                            }
                            
                            
                            
                            $medication_all[$ipid][$med[1]][$med[7]] = $medication_array[$ipid][$k_row];
                        } 
		            }
		        }
		    }
		    
		    foreach($medication_all as $ipid=>$medications){
		        foreach($medications as $med=>$mdates){
		            ksort($mdates);
		            $med_final[$ipid][] = end($mdates);
		        }
		    }
		    
		    
		    
		    foreach($med_final as $ipid=>$mdata){
		        foreach($mdata as $mk=>$md){
		            foreach($md['dosage'] as $time => $dosage){
    		            if(!in_array($time,$ipid_dosage[$ipid])){
    		                $ipid_dosage[$ipid][] = $time;
    		            }
		            }
		        }
		        asort($ipid_dosage[$ipid]);
		        $ipid_dosage[$ipid] = array_values($ipid_dosage[$ipid]);
		    }
		    
		    
		    // get client defaults 
		    $mtimes = new MedicationIntervals();
		    $client_actual = $mtimes->client_medication_intervals($clientid,"actual");
 
		    foreach($med_final as $ipid =>$med_data ){
		        
		        if(!in_array($ipid,$patients_with_medications))
		        {
    		        if(count($ipid_dosage[$ipid]) >= 4 && count($ipid_dosage[$ipid]) <= 12)
    		        {
    		            // insert in patient dosage intervals 
   		                if(!in_array($ipid,$inserted_dosage)){
        		            foreach($ipid_dosage[$ipid] as $kd => $dosage_time){
            		                $pdpdi = new PatientDrugPlanDosageIntervals();
            		                $pdpdi->ipid = $ipid;
            		                $pdpdi->medication_type = 'actual';
            		                $pdpdi->time_interval = $dosage_time;
            		                $pdpdi->save();
            		                
        		            }
    		            
   		                   $inserted_dosage[] = $ipid;
   		                }
    		            
    		            foreach($med_data as $k=>$medi){

    		                $dosage_values = array();
    		                foreach($ipid_dosage[$ipid] as $dosage_key=>$d_time){
    		                    $dosage_data_array[$d_time] = $medi['dosage'][$d_time]; 
    		                    $dosage_values[] = $medi['dosage'][$d_time];
    		                }
    		                
    		                // in sert in medication master and get id
    		                $medication_master = new Medication();
    		                $medication_master->clientid = $clientid;
    		                $medication_master->name = $medi['name'];
    		                $medication_master->extra = 1;
    		                $medication_master->save();
    		                $masterid = $medication_master->id;
    		                
        		            // insert in patient drugplan
    		                $pdp = new PatientDrugPlan();
    		                $pdp->ipid = $ipid;
    		                $pdp->medication_master_id = $masterid;
    		                $pdp->medication = $medi['name'];
    		                $pdp->medication_change = $medi['date'];
    		                $pdp->create_user = $medi['create_user'];
    		                $pdp->dosage = implode('-',$dosage_values );
    		                $pdp->save();
        		            $drugplan_id = $pdp->id;  
    		                
        		            // insert in patient drugplan dosage 
    		                foreach($dosage_data_array as $time=>$dos){
    		                    
        		                $pdpd = new PatientDrugPlanDosage();
        		                $pdpd->ipid = $ipid;
        		                $pdpd->drugplan_id = $drugplan_id;
        		                $pdpd->dosage  = $dos;
        		                $pdpd->dosage_time_interval = $time.':00';
        		                $pdpd->save();
    		                }
    		            }
    		        } 
    		        else 
    		        {

    		            foreach($med_data as $k=>$medi){
    		            
        		            $medication_master = new Medication();
        		            $medication_master->clientid = $clientid;
        		            $medication_master->name = $medi['name'];
        		            $medication_master->extra = 1;
        		            $medication_master->save();
        		            $masterid = $medication_master->id;
 
        		            $dosage_string ='Dosierung: ';
                            foreach($medi['dosage'] as $time=>$dosage){
                                $dosage_string .= $time.'->'.$dosage.' ';
                            }
        		            
        		            // insert in patient drugplan
        		            $pdp = new PatientDrugPlan();
        		            $pdp->ipid = $ipid;
        		            $pdp->medication_master_id = $masterid;
        		            $pdp->medication = $medi['name'];
        		            $pdp->medication_change = $medi['date'];
        		            $pdp->create_user = $medi['create_user'];
        		            $pdp->comments = $dosage_string;
        		            $pdp->save();
        		            $drugplan_id = $pdp->id;

        		            if(!in_array($ipid,$slots)){
            		            $slots[] = $ipid;
        		            }

        		            
    		            }
    		        }
		        }
		    }
		    print_r("more then 12 slots");
		    print_r($slots);
		    
		    print_r("correct");
		    print_r($inserted_dosage);

		    echo "SUCCESS";
		    exit;
		    
		    
		}
		
		public function bedarf_medication_import_handler_wlk($csv_data, $post)
		{ 
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    $import_clients = array($clientid);
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        
		        $all_patients[] = $pat_val['ipid'];
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }

		    if(empty($all_patients)){
		        $all_patients[] = "999999999";
		    }
		    
		    // get all patients that have medication
		    $patient_drugs = Doctrine_Query::create()
		    ->select("ipid")
		    ->from('PatientDrugPlan')
		    ->whereIn('ipid', $all_patients);
		    $patient_drug_details = $patient_drugs->fetchArray();
		    
		    foreach($patient_drug_details as $k=>$mi){
		        $patients_with_medications[] = $mi['ipid'];
		    }
		    
		    $patient_drugs_dosage = Doctrine_Query::create()
		    ->select("ipid")
		    ->from('PatientDrugPlanDosageIntervals')
		    ->whereIn('ipid', $all_patients);
		    $patient_drug_dosage_details = $patient_drugs_dosage->fetchArray();
		    foreach($patient_drug_dosage_details as $lo=>$dm){
		          $patients_with_medications[] = $dm['ipid'];
		    }
	 
		    $patients_with_medications = array_unique($patients_with_medications);
		    
		    
		    
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
		        $data2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		        $users[] = trim($read_data[2]);
		    }
		    $cat_array = array_unique($cat); 
		    $users_array = array_unique($users); 
// 		    print_r($data2patients); exit;
 
		    $usr = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where("clientid ='" . $clientid. "'")
		    ->orderby("last_name ASC");
		    $dr = $usr->execute();
		   
		    foreach($dr as  $k=>$user_details){
		        $userln2id[$user_details['last_name']] = $user_details['id'];
		        $user[trim($fd['import_id'])] = $user_details['id'];
		    }

		    foreach($data2patients as $ipid => $med_data)
		    {
		        if($ipid)
		        {
		            foreach($med_data as $k_row => $med)
		            {
		                $dosage_input = "";
		                
                        if(strlen($med[1])){ // add medication only if we have medication name

                            
                            $medication_array[$ipid][$k_row]['name'] = $med[1];
                            $medication_array[$ipid][$k_row]['dosage'] = $med[4];
                            $medication_array[$ipid][$k_row]['comments'] = $med[5];
                            $medication_array[$ipid][$k_row]['date'] = $med[7];

                            if($user[$med[6]]) {
                                $medication_array[$ipid][$k_row]['create_user'] = $user[$med[6]];
                            } else {
                                $medication_array[$ipid][$k_row]['create_user'] = $userid;
                            }
                            
                            
                            $medication_all[$ipid][$med[1]][$med[7]] = $medication_array[$ipid][$k_row];
                        } 
		            }
		        }
		    }
		    
		    foreach($medication_all as $ipid=>$medications){
		        foreach($medications as $med=>$mdates){
		            ksort($mdates);
		            $med_final[$ipid][] = end($mdates);
		        }
		    }
		    
// 		    print_r($med_final); exit;
		    
/* 		    foreach($med_final as $ipid=>$mdata){
		        foreach($mdata as $mk=>$md){
		            foreach($md['dosage'] as $time => $dosage){
    		            if(!in_array($time,$ipid_dosage[$ipid])){
    		                $ipid_dosage[$ipid][] = $time;
    		            }
		            }
		        }
		        asort($ipid_dosage[$ipid]);
		        $ipid_dosage[$ipid] = array_values($ipid_dosage[$ipid]);
		    } */
		    
		    
		    // get client defaults 
		 /*    $mtimes = new MedicationIntervals();
		    $client_actual = $mtimes->client_medication_intervals($clientid,"actual");
  */
		    foreach($med_final as $ipid =>$med_data )
		    {
                foreach($med_data as $k=>$medi)
                {
    	            $medication_master = new Medication();
    	            $medication_master->clientid = $clientid;
    	            $medication_master->name = $medi['name'];
    	            $medication_master->extra = 1;
    	            $medication_master->save();
    	            $masterid = $medication_master->id;
    	            
    	            // insert in patient drugplan
    	            $pdp = new PatientDrugPlan();
    	            $pdp->ipid = $ipid;
    	            $pdp->isbedarfs= "1";
    	            $pdp->medication_master_id = $masterid;
    	            $pdp->medication = $medi['name'];
    	            $pdp->medication_change = $medi['date'];
    	            $pdp->dosage = $medi['dosage'];
    	            $pdp->comments = $medi['comments'];
    	            $pdp->save();
    
    	            if(!in_array($ipid,$slots)){
    		            $slots[] = $ipid;
    	            }
                }
		    }
		    print_r("inserted bedarfs");
		    print_r($slots);

		    echo "SUCCESS";
		    exit;
		    
		    
		}
		
		
		
		
		public function sapv_import_handler_lmu($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    $import_clients = array($clientid);
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
		        $data2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		    }
		    
		    
            foreach($data2patients as $ipid => $sapv_periods)
            {
                if($ipid && strlen($ipid) > 0 )
                {
                    foreach($sapv_periods as $k=>$sapv_data){
                        if(strlen($sapv_data['1'])>0 && strlen($sapv_data['2']) > 0 ){
                            
                            $sapv_details[$ipid][$k]['verordnungam'] = date("Y-m-d H:i:s",strtotime($sapv_data['1'])); 
                            $sapv_details[$ipid][$k]['verordnungbis'] = date("Y-m-d H:i:s",strtotime($sapv_data['2'])); 
                            $sapv_details[$ipid][$k]['verordnet'] = $sapv_data['4']; 
                            $sapv_details[$ipid][$k]['verordnet_von'] = "importiert"; 
                            $sapv_details[$ipid][$k]['status'] = "2"; // genehmigt - approved
                        }
                    }
                }
            }
            
            foreach($sapv_details as $ipid =>$sdata){
                
                foreach($sdata as $s_int =>$sdetails){

                    $master_fam = new FamilyDoctor();
                    $master_fam->clientid = $clientid;
                    $master_fam->last_name = $sdetails['verordnet_von'];
                    $master_fam->indrop = 1;
                    $master_fam->save();
                    $master_fam_id = $master_fam->id;
                    
                    $cust = new SapvVerordnung();
                    $cust->ipid = $ipid;
                    $cust->verordnet_von = $master_fam_id;
                    $cust->verordnungam = $sdetails['verordnungam'];
                    $cust->verordnungbis = $sdetails['verordnungbis'];;
                    $cust->verordnet = $sdetails['verordnet'];;
                    $cust->status = $sdetails['status'];
                    $cust->save();
                }
            }
		}
		
		public function sapv_import_handler_rp($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    $import_clients = array($clientid);
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
		        $data2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		    }
		    
		
            foreach($data2patients as $ipid => $sapv_periods)
            {
                if($ipid && strlen($ipid) > 0 )
                {
                    foreach($sapv_periods as $k=>$sapv_data){
                        if(strlen($sapv_data['1'])>0 && strlen($sapv_data['2']) > 0 ){
                        	$verordnet = array();
                        	if($sapv_data['3'] == "TRUE"){
                        		$verordnet [] = "1";
                        	}
                        	if($sapv_data['4'] == "TRUE"){
                        		$verordnet [] = "2";
                        	}
                        	if($sapv_data['5'] == "TRUE"){
                        		$verordnet [] = "3";
                        	}
                        	if($sapv_data['6'] == "TRUE"){
                        		$verordnet [] = "4";
                        	}
                        	
                            $sapv_details[$ipid][$k]['verordnungam'] = date("Y-m-d H:i:s",strtotime($sapv_data['1'])); 
                            $sapv_details[$ipid][$k]['verordnungbis'] = date("Y-m-d H:i:s",strtotime($sapv_data['2'])); 
                            $sapv_details[$ipid][$k]['verordnet'] = implode(',',$verordnet); 
                            $sapv_details[$ipid][$k]['verordnet_von'] = "importiert"; 
                            $sapv_details[$ipid][$k]['status'] = "2"; // genehmigt - approved
                        }
                    }
                }
            }
            
            foreach($sapv_details as $ipid =>$sdata){
                
                foreach($sdata as $s_int =>$sdetails){

                    $master_fam = new FamilyDoctor();
                    $master_fam->clientid = $clientid;
                    $master_fam->last_name = $sdetails['verordnet_von'];
                    $master_fam->indrop = 1;
                    $master_fam->save();
                    $master_fam_id = $master_fam->id;
                    
                    $cust = new SapvVerordnung();
                    $cust->ipid = $ipid;
                    $cust->verordnet_von = $master_fam_id;
                    $cust->verordnungam = $sdetails['verordnungam'];
                    $cust->verordnungbis = $sdetails['verordnungbis'];;
                    $cust->verordnet = $sdetails['verordnet'];;
                    $cust->status = $sdetails['status'];
                    $cust->save();
                }
            }
		}
		
		
		public function course_import_handler_lmu($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    $import_clients = array($clientid);
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
// 		    print_r($patients_array); exit;
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
		        $data2patients[$patients_array[$read_data[0]]['ipid']][trim($read_data[4])][] = $read_data;
		        $cat[] = trim($read_data[4]);
		        $users[] = trim($read_data[6]);
		    }
		    $cat_array = array_unique($cat); 
		    $users_array = array_unique($users); 
 
// 		    [1] => Kollegiale Absprache // Shortcut U  ("mit Leistungserbringer")
// 		    [6] => Teambesprechung // Shortcut U  ("mit Leistungserbringer") and comment start with  "Teambesprechng: "

// 		    [2] => Besuch Patient // contact from
// 		    [74] => Besuch Arzt // contact from
// 		    [50] => Besuch Krankenhaus/stat. Einrichtung // contact from


// 		    [3] => Koordination // Shortcut V  
// 		    [5] => Telefonat // Shortcut XT  


// 		    [97] => E-Mail  // Shortcut K That  starts with  "E-Mail: "

		    
// 		    [38] => Sono // do not use
// 		    [80] => Dokumentation // do not use
		    
// 		    bockingdorothee
		    
		    $usr = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where("clientid ='" . $clientid. "'")
		    ->orderby("last_name ASC");
		    $dr = $usr->execute();
		   
		    foreach($dr as  $k=>$user_details){
		        $userln2id[$user_details['last_name']] = $user_details['id'];
		    }
		    
		    foreach($data2patients as $ipid => $type_data)
		    {
		        if($ipid)
		        {
		            foreach($type_data as $type => $type_details)
		            {
		                $types_array[] = $type;
		                foreach($type_details as $k=>$values)
		                {
        		                    
		                    if( $type == "Kollegiale Absprache" || $type == "Teambesprechung") // Shortcut U  ("mit Leistungserbringer") || Teambesprechung
		                    {
		                        if(strlen($values['5']) > 0 ) 
		                        {
		                            $u_duration = $values['5'];
		                        } 
		                        else
		                        {
		                            $u_duration = "10"; // default value for Beratrung
		                        }
		                        
                                // insert in patient course
		                        if($type == "Kollegiale Absprache")
		                        {
    		                        $verlauf_entry = "mit Leistungserbringer | ".$u_duration ." | ".$values['3']." | ".date("d.m.Y H:i",strtotime($values['1']))." ";
		                        }
		                        elseif($type == "Teambesprechung")
		                        {
		                            
    		                        $verlauf_entry = "mit Leistungserbringer | ".$u_duration ." | Teambesprechng: ".$values['3']." | ".date("d.m.Y H:i",strtotime($values['1']))." ";
		                        }
		                        
		                        $cust = new PatientCourse();
		                        $cust->ipid = $ipid;
		                        $cust->course_date = date("Y-m-d H:i:s",strtotime($values['1']));
		                        $cust->course_type = Pms_CommonData::aesEncrypt("U");
		                        $cust->course_title = Pms_CommonData::aesEncrypt($verlauf_entry);
		                        $cust->done_date = date("Y-m-d H:i:s",strtotime($values['1']));
		                        if($userln2id[$values['6']]){
    		                        $cust->user_id = $values['6'];
		                        } else{
    		                        $cust->user_id = $userid;
		                        }
		                        $cust->save();
		                    }
		                    elseif($type == "Besuch Patient" || $type == "Besuch Arzt"  || $type == "Besuch Krankenhaus/stat. Einrichtung" )
		                    { // contact form
		                        
		                        // create contact from id - then add to verlauf as record id
		                        $stmb = new ContactForms();
		                        $stmb->ipid = $ipid;
		                        $stmb->start_date = date("Y-m-d H:i:s", strtotime($values['1']));
	                            $stmb->end_date = date("Y-m-d H:i:s", strtotime($values['2']));
		                        $stmb->begin_date_h =  date("H", strtotime($values['1']));
		                        $stmb->begin_date_m = date("i", strtotime($values['1']));
		                        $stmb->end_date_h = date("H", strtotime($values['2']));
		                        $stmb->end_date_m = date("i", strtotime($values['2']));
		                        $stmb->date = date("Y-m-d H:i:s", strtotime($values['1']));
		                        $stmb->form_type = "121";
		                        $stmb->fahrtstreke_km = $values['7'];
		                        $stmb->comment = htmlspecialchars($values['3']);
		                        if(strlen($values['8'])> 0 && $values['8'] == "1"){
    		                        $stmb->quality = "4";
		                        }
		                        if($userln2id[$values['6']])
		                        {
		                            $stmb->create_user= $userln2id[$values['6']];
		                        } else{
		                            $stmb->create_user = $userid; // DEFAUL USER
		                        }
		                        $stmb->save();
		                        $conact_form_record_id = $stmb->id;
		                        
		                        $comment = 'Kontaktformular  hinzugefügt';
		                        $cust = new PatientCourse();
		                        $cust->ipid = $ipid;
		                        $cust->course_date = date("Y-m-d H:i:s", strtotime($values['1']));
		                        $cust->course_type = Pms_CommonData::aesEncrypt("F");
		                        $cust->course_title = Pms_CommonData::aesEncrypt($comment);
		                        $cust->tabname = Pms_CommonData::aesEncrypt("contact_form");
		                        $cust->recordid = $conact_form_record_id;
		                        
		                        if($userln2id[$values['6']])
		                        {
		                            $cust->user_id= $userln2id[$values['6']];
		                        } else {
		                            $cust->user_id = $userid; // DEFAUL USER
		                        }
		                        
		                        if($userln2id[$values['6']])
		                        {
		                            $cust->create_user= $userln2id[$values['6']];
		                        } else{
		                            $cust->create_user = $userid; // DEFAUL USER
		                        }
		                        $cust->done_date = date("Y-m-d H:i:s", strtotime($values['1']));
		                        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
		                        $cust->done_id = $conact_form_record_id;
		                        $cust->save();
		                        
		                        
		                        // KOMENT 
		                        $cust = new PatientCourse();
		                        $cust->ipid = $ipid;
		                        $cust->course_date = date("Y-m-d H:i:s", strtotime($values['1'])); //?? 
		                        $cust->course_type = Pms_CommonData::aesEncrypt("K");
		                        $cust->course_title = Pms_CommonData::aesEncrypt(date("H:i",strtotime($values['1'])) . ' - ' . date("H:i",strtotime($values['2'])) . '  ' . date("d.m.Y",strtotime($values['1'])));
		                        if($userln2id[$values['6']])
		                        {
		                            $cust->user_id= $userln2id[$values['6']];
		                        } else {
		                            $cust->user_id = $userid; // DEFAUL USER
		                        }
		                        $cust->done_date = date("Y-m-d H:i:s", strtotime($values['1']));
		                        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
		                        $cust->done_id = $conact_form_record_id;
		                        $cust->save();
		                        
		                        
		                        // Km :: Fahrtstrecke
		                        if(strlen($values['7']) > 0 )
		                        {
    		                        $cust = new PatientCourse();
    		                        $cust->ipid = $ipid;
    		                        $cust->course_date = date("Y-m-d H:i:s", strtotime($values['1'])); //??
    		                        $cust->course_type = Pms_CommonData::aesEncrypt("K");
    		                        $cust->course_title = Pms_CommonData::aesEncrypt("Fahrtstrecke: " . $values['7']);
    		                        
    		                        if($userln2id[$values['6']])
    		                        {
    		                            $cust->user_id= $userln2id[$values['6']];
    		                        } 
    		                        else 
    		                        {
    		                            $cust->user_id = $userid; // DEFAUL USER
    		                        }
    		                            		                        
    		                        $cust->done_date = date("Y-m-d H:i:s", strtotime($values['1'])); //??
    		                        $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
    		                        $cust->done_id = $conact_form_record_id;
    		                        $cust->save();
		                        }
		                    }
		                    elseif($type == "Koordination")
		                    {// Shortcut V

		                    	if(strlen($values['5']) > 0 ) 
		                        {
		                            $v_duration = $values['5'];
		                        } 
		                        else
		                        {
		                            $v_duration = "8"; // default value for koordination
		                        }
		                        $koord_verlauf_entry = $v_duration." | ".$values['3']." | ".date("d.m.Y H:i",strtotime($values['1']))." ";
		                        
		                        $cust = new PatientCourse();
		                        $cust->ipid = $ipid;
		                        $cust->course_date = date("Y-m-d H:i:s",strtotime($values['1']));
		                        $cust->course_type = Pms_CommonData::aesEncrypt("V");
		                        $cust->course_title = Pms_CommonData::aesEncrypt($koord_verlauf_entry);
		                        $cust->done_date = date("Y-m-d H:i:s",strtotime($values['1']));
		                        if($userln2id[$values['6']]){
		                            $cust->user_id = $userln2id[$values['6']];
		                        } else{
		                            $cust->user_id = $userid;
		                        }
		                        $cust->save();
 
		                    }
		                    elseif($type == "Telefonat")
		                    {// Shortcut XT

		                        if(strlen($values['5']) > 0 )
		                        {
		                            $xt_duration = $values['5'];
		                        }
		                        else
		                        {
		                            $xt_duration = "12"; // default value for Telefonat
		                        }
		                        
		                        $ktelefon_verlauf_entry = "".$xt_duration ." | ".$values['3']." | ".date("d.m.Y H:i",strtotime($values['1']))." ";
		                        
		                        $cust = new PatientCourse();
		                        $cust->ipid = $ipid;
		                        $cust->course_date = date("Y-m-d H:i:s",strtotime($values['1']));
		                        $cust->course_type = Pms_CommonData::aesEncrypt("XT");
		                        $cust->course_title = Pms_CommonData::aesEncrypt($ktelefon_verlauf_entry);
		                        $cust->done_date = date("Y-m-d H:i:s",strtotime($values['1']));
		                        if($userln2id[$values['6']]){
		                            $cust->user_id = $userln2id[$values['6']];
		                        } else{
		                            $cust->user_id = $userid;
		                        }
		                        $cust->save();
		                        
		                    }
		                    elseif($type == "E-Mail")
		                    {// // Shortcut K That  starts with  "E-Mail: "
		                        
		                        $comment_verlauf_entry = "E-Mail:  ".$values['3']." ";
		                        
		                        $cust = new PatientCourse();
		                        $cust->ipid = $ipid;
		                        $cust->course_date = date("Y-m-d H:i:s",strtotime($values['1']));
		                        $cust->course_type = Pms_CommonData::aesEncrypt("K");
		                        $cust->course_title = Pms_CommonData::aesEncrypt($comment_verlauf_entry);
		                        $cust->done_date = date("Y-m-d H:i:s",strtotime($values['1']));
		                        if($userln2id[$values['6']]){
		                            $cust->user_id = $userln2id[$values['6']];
		                        } else{
		                            $cust->user_id = $userid;
		                        }
		                        $cust->save();
		                        
		                    } 
		                    else
		                    {
		                    // do nothing    
		                    }
		                }
		                
		             }
		       }
		    }
		    
		}
		
		public function medication_course_import_handler_lmu($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    $import_clients = array($clientid);
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
		    
		    // create array for admisssions
		    foreach($csv_data as $rk => $read_data){
		        $data2patients[$patients_array[$read_data[0]]['ipid']][] = $read_data;
		        $users[] = trim($read_data[2]);
		    }
		    $cat_array = array_unique($cat); 
		    $users_array = array_unique($users); 
// 		    print_r($data2patients); exit;
 
		    $usr = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where("clientid ='" . $clientid. "'")
		    ->orderby("last_name ASC");
		    $dr = $usr->execute();
		   
		    foreach($dr as  $k=>$user_details){
		        $userln2id[$user_details['last_name']] = $user_details['id'];
		    }

		    foreach($data2patients as $ipid => $med_data)
		    {
		        if($ipid)
		        {
		            foreach($med_data as $k_row => $med)
		            {
		                $medication_name_drug[$k_row] = "";
		                $medication_dosage_string[$k_row] = "";
		                $med_dosage_str = "";
		                $dosage_value_arr = array();
		                $dosage_time_arr = array();
		                $medication_dosage_array = array();
		                
                        if(strlen($med[4])){ // add medication only if we have medication name
                            
                            if(strlen($med[3]))
                            {
                                $medication_name_drug[$k_row] = $med[4].'('.$med[3].')';
                            }
                            else
                            {
                                $medication_name_drug[$k_row] = $med[4];
                            }
                            
                            $med_dosage_str = preg_replace('/\([^)]*\)|[()]/', '', $med[6]);
                            $dosage_value_arr = explode(";",$med_dosage_str);
                            $dosage_time_arr = explode(";",$med[5]);
                            
                            foreach($dosage_value_arr as $dk =>$dv){
                                // Create_dosage
                                if(strlen($dosage_time_arr[$dk]) == 1){
                                    $dosage_time_arr[$dk] = "0".$dosage_time_arr[$dk].':00';
                                } else{
                                    $dosage_time_arr[$dk] = $dosage_time_arr[$dk].':00';
                                }
                                
                                $medication_dosage_array[] =  $dv.'('.$dosage_time_arr[$dk].')';
                            }
                            
                            
                            $medication_dosage_string[$k_row] = implode("-",$medication_dosage_array);
                            
                            // medication_date
                            if(strlen($med[1]) > 1){
                                $medication_date = ' | '.date("d.m.Y",strtotime($med[1]));
                                $course_date = date("Y-m-d 00:00:00",strtotime($med[1]));
                            }
                            else
                            {
                                $medication_date = "";
                                $course_date = date("Y-m-d 00:00:00",time());
                            }
                            $course_entry = "";
                            $course_entry = $medication_name_drug[$k_row] .'|'.$medication_dosage_string[$k_row]. $medication_date  ;

                            $cust = new PatientCourse();
                            $cust->ipid = $ipid;
                            $cust->course_date = $course_date; //??
                            $cust->course_type = Pms_CommonData::aesEncrypt("K");
                            $cust->course_title = Pms_CommonData::aesEncrypt($course_entry);
                            if($userln2id[$med['2']])
                            {
                                $cust->user_id= $userln2id[$med['2']];
                            }
                            else
                            {
                                $cust->user_id = $userid; // DEFAUL USER
                            }

                            if($userln2id[$med['2']])
                            {
                                $cust->create_user = $userln2id[$med['2']];
                            }
                            else
                            {
                                $cust->create_user = $userid; // DEFAUL USER
                            }
                            $cust->done_date = $course_date; //??
                            $cust->tabname = Pms_CommonData::aesEncrypt("medication_import");
                            $cust->save();
                            
                            // write in verlauf comment with link
                            
                            
                            
                            
                        } 
                        else
                        {
                            if(strlen($med[7])>0){
                                // write in verlauf comment with link
                                
                            }
                            
                        }
		                
		            }
		        }
		    }
		    
		}
		
	
		
		
		
		public function location_import_handler($csv_data, $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    $import_clients = array("177","178","179");

		    $locations['177'] = array(   //WL_Paderborn
		        "Hospiz" => "7671",
		        "Onkologie" => "7813",
		        "Palliativstation" => "7676",
		        "Klinik, sonst." => "7814",
		        "Pflegeheim" => "7815",
		        "Reha" => "7816",
		        "unbekannt" => "7817",
		        "zu Hause" => "7720",
		    );
		    
		    $locations['178'] = array( //WL_Hoexter
		        "Hospiz" => "7805",
		        "Notaufnahme" => "7806",
		        "Onkologie" => "7807",
		        "Palliativstation" => "7808",
		        "Intensivstation" => "7809",
		        "Klinik, sonst." => "7810",
		        "Pflegeheim" => "7811",
		        "Reha" => "7709",
		        "unbekannt" => "7812",
		        "zu Hause" => "7721",
		    );
		    
		    $locations['179'] = array( //WL_Delbruek
		        "Hospiz" => "7801",
		        "Palliativstation" => "7802",
		        "Klinik, sonst." => "7803",
		        "Pflegeheim" => "7294",
		        "unbekannt" => "7804",
		        "zu Hause" => "7292",
		    );
		    
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', $import_clients)
		    ->andWhere("import_pat != ''");
		    $patient_details = $patient->fetchArray();
		    
// 		    print_r($patient_details); exit;
		    foreach($patient_details as $k => $pat_val)
		    {
		        $patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
		        $patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
		        $patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
		    }
// 		    print_r($csv_data); exit;
		    
		    foreach($csv_data as $k => $loc_data){
		        
		        if($loc_data[1] || $loc_data[2])
		        {
		            $new_style[$k]['patient_id'] = $loc_data[0]; 
		            $new_style[$k]['patient_ipid'] = $patients_array[$loc_data[0]]['ipid']; 
		            $new_style[$k]['patient_client'] = $patients_array[$loc_data[0]]['client']; 
		            
   		            $new_style[$k]['from'] = $loc_data[1];
    		        $new_style[$k]['till'] = $loc_data[2]; 
		            if($loc_data[1])
		            {
    		            $new_style[$k]['valid_from'] = date("Y-m-d 00:00:00", strtotime($loc_data[1]));
		            }
		            
		            if($loc_data[2])
		            {
    		            $new_style[$k]['valid_till'] = date("Y-m-d 00:00:00",strtotime($loc_data[2])); 
		            } 
		            else
		            {
    		            $new_style[$k]['valid_till'] = "0000-00-00 00:00:00"; 
		            }
		            
		            
		            $new_style[$k]['location_name'] = $loc_data[3]; 
		            $new_style[$k]['location_id'] = $locations[$patient2client[$loc_data[0]]][trim($loc_data[3])];
		        }
		             
		    }
		    
		    
		    foreach($new_style as $ko=>$sd){
		        $extra[$sd['patient_id']][] = $sd;
		    }
		    foreach($extra as $pat =>$data){
		        $sorted_data[$pat] = $this->array_sort($data, "from", SORT_ASC);
		    }
		    
		    foreach($sorted_data as $patient_imp_aid => $imdata){
		        $new_key_sorted_data[$patient_imp_aid] = array_values ($imdata);
		    }
		    
		    
		    $pm = new PatientMaster();
		    
 
// 		    print_r($new_key_sorted_data); exit;
		    
		    foreach($new_key_sorted_data as $patient_imp_aid => $datas)
		    {
		        $diff[$patient_imp_aid] = "";
		        
		        if(count($datas) > 1){
		            $k_new ="9999";
		            foreach($datas as $loc_k=> $lock_data){
		                if($datas[$loc_k+1]['from'] && empty($datas[$loc_k]['till'])){
		                    $datas[$loc_k]['till'] = $datas[$loc_k+1]['from']; 
		                }
		                if($datas[$loc_k+1]['from'] && $datas[$loc_k]['till'] != $datas[$loc_k+1]['from'] )
		                {
                            $diff[$patient_imp_aid] = $pm->getDaysInBetween(date("Y-m-d", strtotime($datas[$loc_k]['till'])), date("Y-m-d", strtotime($datas[$loc_k+1]['from'])));
    		              
                            if(count($diff[$patient_imp_aid]) >=2)
                            {
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['patient_id'] = $lock_data['patient_id'];
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['patient_ipid'] = $lock_data['patient_ipid'];
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['patient_client'] = $lock_data['patient_client'];
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['from'] = date("d.m.Y",strtotime($diff[$patient_imp_aid][0]));
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['till'] = date("d.m.Y",strtotime(end($diff[$patient_imp_aid])));
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['valid_from'] = date("Y-m-d 00:00:00",strtotime($diff[$patient_imp_aid][0]));
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['valid_till'] = date("Y-m-d 00:00:00",strtotime(end($diff[$patient_imp_aid])));
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['location_name'] = "unbekannt";
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['location_id'] =  $locations[$lock_data['patient_client']]["unbekannt"];
                                $new_key_sorted_data[$patient_imp_aid][$k_new]['EXTRA'] = "YES";
                                $k_new++;
                            } 
		                }
		            }
		        }
		    }
		    foreach($new_key_sorted_data as $pat =>$datan){
		        $sorted_data_extra[$pat] = $this->array_sort($datan, "from", SORT_ASC);
		    }
		    
		    foreach($sorted_data_extra as $patient_imp_aid => $imdatan){
		        $new_key_sorted_data_extra[$patient_imp_aid] = array_values ($imdatan);
		    }
		    
// 		    print_r($new_key_sorted_data_extra); exit;
		    foreach($new_key_sorted_data_extra as $patient_imp_aid => $import_data)
		    {
   	            $this->import_locations($import_data);
		    }
 
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_locations($import_data)
		{
		    
		    foreach($import_data as $k=>$data)
		    {
		        
		        if($data['patient_ipid'])
		        {
    		        $cust = new PatientLocation();
    		        $cust->ipid = $data['patient_ipid'];
    		        $cust->clientid = $data['patient_client'];
    		        $cust->valid_from = $data['valid_from'];
    		        $cust->valid_till = $data['valid_till'];
    		        $cust->location_id = $data['location_id'];
    		        $cust->save();
		        }
		    }
		}


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
		                        if($on == 'from' || $on == 'admissiondate' || $on == 'admission_date' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'day' || $on == 'assessment_completed_date' || $on == 'visit_date' || $on == 'contact_form_date' || $on == 'first_sapv_active_day' || $on == 'patient_discharge_date' || $on == 'death_date' || $on == 'entry_date')
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
		                if($on == 'birthd' || $on == 'admission_date' || $on == 'admissiondate' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'day' || $on == 'assessment_completed_date' || $on == 'visit_date' || $on == 'contact_form_date' || $on == 'first_sapv_active_day' || $on == 'patient_discharge_date' || $on == 'death_date')
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

		
		
		public function voluntary_workers($csv_data, $post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$import_clients = array($clientid);
			//	Hospizbegleiter  = > Ehnrremanlichte
		
			foreach($csv_data as $rk => $read_data){
				if($rk != '0'){ // columns name
					$master_vw = new Voluntaryworkers();
					$master_vw->clientid = $clientid;
					$master_vw->status = "e";//Hospizbegleiter
					$master_vw->salutation = $read_data['1'];
					$master_vw->title = $read_data['2'];
					$master_vw->first_name = $read_data['3'];
					$master_vw->last_name = $read_data['4'];
					$master_vw->street = $read_data['5'];
					$master_vw->zip = $read_data['6'];
					$master_vw->city = $read_data['7'];
					$master_vw->phone = $read_data['8'];
					$master_vw->mobile = $read_data['9']; // We do not have fax 
					$master_vw->email = $read_data['10'];
					$master_vw->save();
				}
			}
		}
		
		public function specialists($csv_data, $post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$import_clients = array($clientid);
			//	Hospizbegleiter  = > Ehnrremanlichte
		
			foreach($csv_data as $rk => $read_data){
				if($rk != '0'){ // columns name
					//  insert in pflegedienst
					$master_sp = new Specialists();
					$master_sp->clientid = $clientid;
					$master_sp->medical_speciality = "1139"; // HARDCODDED FOR 
					$master_sp->salutation = $read_data['0'];
					$master_sp->title = $read_data['1'];
					$master_sp->first_name = $read_data['2'];
					$master_sp->last_name = $read_data['3'];
					$master_sp->street1 = $read_data['4'];
					$master_sp->zip = $read_data['5'];
					$master_sp->city = $read_data['6'];
					$master_sp->phone_practice = $read_data['7'];
					$master_sp->fax = $read_data['8'];
					$master_sp->phone_private = $read_data['9']; 
					$master_sp->email = $read_data['10'];
					$master_sp->comments= $read_data['11'];
					$master_sp->save();
				}
			}
		}
		
		

		public function medication_import_handler_rp($csv_data, $post)
		{
			setlocale(LC_ALL, 'de_DE.UTF-8');
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
		
// 			$this->hasColumn('cocktailid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			$types_array = array("Notfall"=>"isbedarfs","Regel"=>"actual","Bedarf"=>"isbedarfs","RegelPumpe"=>"isschmerzpumpe");
			
			$import_clients = array($clientid);
			foreach($csv_data as $csvr =>$csvd){
				if($csvd[0] != "PATIENT ID" && !empty($csvd['4'])){
// 					$medication[$csvd[0]][$csvd['4']][$csvd['3']] = $csvd;
					if($types_array[$csvd['3']] == "isschmerzpumpe" ){
						$medication[$csvd[0]][$types_array[$csvd['3']]][$csvd['4']] = $csvd;
						$medication[$csvd[0]][$types_array[$csvd['3']]]['cocktail_user'] = $csvd[2];
					} else {
						$medication[$csvd[0]][$types_array[$csvd['3']]][$csvd['4']] = $csvd;
					}
					$post_users[$csvd['1']] = (int)$csvd['2'];
				}
			}

// 			$usr = Doctrine_Query::create()
// 			->select('*')
// 			->from('User')
// 			->where("clientid ='" . $clientid. "'")
// 			->orderby("last_name ASC");
// 			$dr = $usr->execute();
			
// 			foreach($dr as $k=>$f){
// 				$existing_users[] = $f['last_name'].', '.$f['first_name']; 
// 				$existing_users_ids[$f['last_name'].', '.$f['first_name']] = $f['id']; 
// 			}

// 			foreach($post_users as $lf=>$id){
// 				if($id == "0" && !in_array($lf,$existing_users)){
// 					// insert users as inactive 
					
// 					$name = array();
// 					$last_name = "";
// 					$first_name =  "";
// 					$user_name =  "";
					
// 					$name = explode(', ',$lf);
// 					$last_name = $name[0];
// 					$first_name =  $name[1];
// 					$user_name =  strtolower(substr(str_replace(array(' ',',','.'),"",$name[0]).str_replace(array(' ',',','.') ,"",$name[1]),0,15));
					
// 					$master_user = new User();
// 					$master_user->clientid = $clientid;
// 					$master_user->username = $user_name;
// 					$master_user->password = md5($user_name);
// 					$master_user->first_name = $first_name;
// 					$master_user->last_name = $user_name;
// 					$master_user->isactive = "1";
// 					$master_user->save();
// 					$post_users[$lf] = $master_user->id;
// 				} else{
// 					$post_users[$lf] = $existing_users_ids[$lf];
// 				}
// 			}
			
			
			$patient = Doctrine_Query::create()
			->select("p.*,e.*")
			->from('PatientMaster p')
			->leftJoin("p.EpidIpidMapping e")
			->andWhereIn('e.clientid', $import_clients)
			->andWhere("p.import_pat != ''")
			->andWhere("p.isdelete = 0 ");
			$patient_details = $patient->fetchArray();
		
			foreach($patient_details as $k => $pat_val)
			{
		
				$all_patients[] = $pat_val['ipid'];
				$patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
				$ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
				$patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
				$patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
			}
		
			if(empty($all_patients)){
				$all_patients[] = "999999999";
			}
		
			foreach($medication as $p_id=>$med_array){
				if(isset($patients_array[$p_id]['ipid'])){
					$medication_array[$patients_array[$p_id]['ipid']] = $med_array; 
				}
			}
 
			 foreach($medication_array as $pipid => $med_data_type){
			 	
			 	foreach($med_data_type as $type=>$med_data){
			 		
			 		if($type == "isschmerzpumpe" && !isset($cocktail_id[$pipid])){
			 			//insert cocktail procedure
			 			$mc = new PatientDrugPlanCocktails();
			 			$mc->userid = $med_data['cocktail_user'];
			 			$mc->clientid = $clientid;
			 			$mc->ipid = $pipid;
			 			$mc->pumpe_type = "pca";
			 			$mc->save();
			 			
			 			//get cocktail id
			 			$cocktail_id[$pipid] = $mc->id;
			 		}
			 			
			 		
				 	foreach($med_data as $medication_name=>$medication_details){
				 		if($medication_name == "cocktail_user"){
				 			continue;
				 		}
				 		$medication_master = new Medication();
				 		$medication_master->clientid = $clientid;
				 		$medication_master->name = $medication_name;
				 		$medication_master->extra = 1;
				 		$medication_master->save();
				 		$masterid = $medication_master->id;
				 		
				 		$pdp = new PatientDrugPlan();
				 		$pdp->ipid = $pipid;
				 		$pdp->medication_master_id = $masterid;
				 		$pdp->medication = $medication_name;
				 		if(strlen($medication_details[8]) > 0 ){
					 		$pdp->medication_change = date("Y-m-d H:i:s",strtotime($medication_details[8]));
				 		}
				 		if($type == "isschmerzpumpe" && isset($cocktail_id[$pipid])) {
					 		$pdp->isschmerzpumpe = "1";
					 		$pdp->isbedarfs = "0";
					 		$pdp->cocktailid = $cocktail_id[$pipid];
				 		} elseif($type == "isbedarfs") {
					 		$pdp->isbedarfs = "1";
					 		$pdp->isschmerzpumpe = "0";
				 		} else {
					 		$pdp->isschmerzpumpe = "0";
					 		$pdp->isbedarfs = "0";
				 		}
				 		if(strlen($medication_details[9]) > 0){
					 		$pdp->dosage = $medication_details[9];
				 		}
				 		if(strlen($medication_details[7]) > 0){
					 		$pdp->comments = $medication_details[7];
				 		}
				 		
				 		$pdp->verordnetvon = $medication_details[2];
				 		$pdp->save();
				 		
				 		$drugplan_id = $pdp->id;
				 		
				 		if(strlen($medication_details[5])>0 || strlen($medication_details[6])>0)
				 		{
					 		$pdpe = new PatientDrugPlanExtra();
					 		$pdpe->ipid = $pipid;
					 		$pdpe->drugplan_id = $drugplan_id;
					 		$pdpe->drug = $medication_details[5];
					 		$pdpe->concentration = $medication_details[6];
					 		$pdpe->save();
				 		}
				
				 		
				 	}
			 	}
			 }
			 
			 
		}

		
		public function update_falls($csv_data_falls){
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
				
			$import_clients = array($clientid);
			
			
			$sql = 'e.epid, p.ipid, e.ipid,p.import_pat,e.clientid,';
			$sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
			$sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
			$sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip';
			
			$patient = Doctrine_Query::create()
// 			->select("p.*,e.*")
			->select($sql)
			->from('PatientMaster p')
			->leftJoin("p.EpidIpidMapping e")
			->andWhereIn('e.clientid', $import_clients)
			->andWhere("p.import_pat != ''")
			->andWhere("p.isdelete = 0 ");
			$patient_details = $patient->fetchArray();
			
			
			
			foreach($patient_details as $k => $pat_val)
			{
			
				$all_patients[] = $pat_val['ipid'];
				$patients_array[$pat_val['import_pat']]['ipid'] = $pat_val['ipid'];
				$ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
				$patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
				$patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];

 
				
				$all_patient_data[$pat_val['ipid']]['ipid'] = $pat_val['ipid'];
				$all_patient_data[$pat_val['ipid']]['import_pat'] = $pat_val['import_pat'];
				$all_patient_data[$pat_val['ipid']]['last_name'] = $pat_val['last_name'];
				$all_patient_data[$pat_val['ipid']]['first_name'] = $pat_val['first_name'];
			}
			
// 			print_R($all_patient_data);
			
			foreach($csv_data_falls as $k_csv_row => $dimport){
				
				if($dimport[0] != "_kf_Arzt_HausarztID"){
			
					$import_pat = $dimport[0];
					$pipid = $patients_array[$import_pat]['ipid'];
					$all_patient_data[$pipid ]['import_pat'] = $dimport[0];

					
					
					$all_patient_data[$pipid ]['isdischarged'] = '0';
					if(strlen($dimport[1]) > 0 ){
						$all_patient_data[$pipid]['admission_date'] = date("Y-m-d H:i:s",strtotime($dimport[1]));
						 
						if(strlen($dimport[2]) > 0 ){ // patient has discharge
							$all_patient_data[$pipid ]['discharge_date'] = date("Y-m-d H:i:s",strtotime($dimport[2]));
							
							
							
							if(strlen($dimport[5]) > 0 ){
								$all_patient_data[$pipid ]['discharge_method'] = $dimport[5];
							} else{
								$all_patient_data[$pipid ]['discharge_method'] = "1875";
							}
							$all_patient_data[$pipid ]['discharge_location'] = $dimport[6];
							$all_patient_data[$pipid ]['isdischarged'] = '1';
						}
					} else{
						$all_patient_data[$pipid ]['admission_date'] = date("Y-m-d H:i:s");
					}
				}
			}
 
			foreach($all_patient_data as $ipid =>$dataa){
				if(strlen($ipid) > 0 && $dataa['isdischarged'] == "1"){
					
					$quer = "UPDATE patient_discharge SET discharge_method = '".$dataa['discharge_method']."'  WHERE  ipid= '".$ipid."'  ";
					echo $quer.";";
					echo "<br/>";
					
				}
			}
			print_R($all_patient_data);
			
			exit;
			
		}
		
		/* - -------------------------------------------------------------------------------- */
		/* - -------------------------------------------------------------------------------- */
		/* - --------------------------- TODO-1368 ------------------------------------------ */
		/* - -------------------------------------------------------------------------------- */
		/* - -------------------------------------------------------------------------------- */
		
		public function patient_import_handler_rp_new($csv_data,$csv_data_extra, $post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			$start_dgp = microtime(true);
 
			
		 
			// gather necesary data! 
			$csv_patients = array();

			foreach($csv_data as $c=>$cont_data){
				if( $c != 0 && strlen($cont_data['15'])  > 0 ){
					$location_master[] = $cont_data['15'];
				}
				$csv_patients[] = $cont_data['0'];
				
				if(strlen($cont_data['13']) >0){
					$firs_contact[] = $cont_data['13'];
				}

				$hi_ver[$cont_data['0']] = $cont_data['9'];
			}
			
// 			dd($csv_data_extra);
			
			// HEALTH INSURANCE !!!!!!!!!
			/* $fix_health = 0;
			if($fix_health == "1"){
				$patient = Doctrine_Query::create()
				->select("p.*,e.*")
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->andWhereIn('e.clientid', array($clientid))
				->andWhere("p.import_pat != ''")
				->andWhere("p.isdelete = 0 ");
				$patient_details = $patient->fetchArray();
				$existing_patients = array();
	
				foreach($patient_details as $k => $pat_val)
				{
					$patients_array[$pat_val['import_pat']] = $pat_val['ipid'];
				}
				if(!empty($patients_array)){
					foreach($patients_array as $pimp=>$pidpi){
						
						
						$Mhi2s = Doctrine_Query::create()
						->select("*")
						->from("PatientHealthInsurance")
						->where("ipid like  '".$pidpi."' " );
						$custarr = $Mhi2s->fetchArray();
						
						if(count($custarr)>0)
						{
							$cust = Doctrine::getTable('PatientHealthInsurance')->find($custarr[0]['id']);
							$cust->insurance_no = $hi_ver[$pimp];
							$cust->save();
						}
					}
					
				}
				
				echo "HI_fixxed"; exit;
			} */
 
			$add_contacts = 1 ;
			if($add_contacts == "1"){
				
				$patients_array = array();
				$file = PUBLIC_PATH ."/import/rp_new_import/cnt_imported_second.txt";
				$current = file_get_contents($file);
				
				$imported_patients = array();
				if(strlen($current)>0){
					$imported_patients = explode(',',$current);
				}
				
				$patient = Doctrine_Query::create()
				->select("p.ipid,p.import_pat,e.ipid")
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->andWhereIn('e.clientid', array($clientid))
				->andWhere("p.import_pat != ''")
				->andWhere("p.isdelete = 0 ");
				if(!empty($imported_patients)){
					$patient->andWhereNotIn("p.ipid",$imported_patients);
				};
				$patient->limit("100");
				$patient_details = $patient->fetchArray();

				
				
				$pid2import = array();
				foreach($patient_details as $k => $pat_val)
				{
					$patients_array[$pat_val['import_pat']] = $pat_val['ipid'];
					$pid2import[] = $pat_val['import_pat'];
				}
				

 
				
				//CONTACTS
				// hausbesuch 427
				// STUPID HARDCODED SUBSTITUTE
				$user_substitute = array(
						"Ina Rohlandt"=>"inarohlandt",
						"Helga Schmidt"=>"helgaschmidt",
						"Monika Hildenbrand"=>"monikahildenbrand",
						"Heike Kautz"=>"heikekautz",
						"Jennifer Hofmann"=>"jenniferhofmann",
						"jenniferhoffmann"=>"jenniferhofmann",
						"Gero Dingendorf"=>"gerodingendorf",
						"Thomas Basten"=>"thomasbasten",
						"selmahendricksd"=>"selmahendricks",
						"jenniferhoffmann"=>"jenniferhofmann",
						"ulrickekerscher"=>"ulrikekerscher",
						"georgmockmanuelastebel"=>"georgmock",
						"elisabethjaeckelbrittagil"=>"elisabethjaeckel",
						"tomasmengenulrickekerscher"=>"thomasmengen",
						"janwalpuskimartinadaun"=>"janwalpuski",
						"juergenprusseitmanuelastebel"=>"juergenprusseit",
						"elisabethjaeckelwolfgangkemp"=>"elisabethjaeckel",
						"christinehoffmannwolfgangkemp"=>"christinehoffmann",
						"janwalpuskikarinmarx"=>"janwalpuski",
						"janwalpuskiRufdienstler"=>"janwalpuski",
						"selmahendricksd"=>"selmahendricks",
						"elisabethjaeckelm"=>"elisabethjaeckel",
						"elisabethjaeckelmartinadaun"=>"elisabethjaeckel",
						"elisabethjaeckelw"=>"elisabethjaeckel",
						"christinehoffmanndkieferfischer"=>"christinehoffmann",
						"christinehoffmannu"=>"christinehoffmann",
						"elisabethjaeckelRufdienstler"=>"elisabethjaeckel",
						"monikahillenbrand"=>"monikahildenbrand",
						"ingajezekgiselatextor"=>"ingajezek",
						"Rufdienstler"=>"rufgpflege",
						"GiselaTextor"=>"giselatextor",
						"jherbronwolf"=>"jherbornwolf",
						"tomasmengen"=>"thomasmengen",
						"tomasdyong"=>"thomasdyong",
						"rufpflege"=>"rufgpflege",
						"systemimport"=>"systemimport"
						
						
				);
				// get users
				$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('clientid ="'.$clientid.'" OR usertype="SA"' );
				$users_details_arr =$usr->fetchArray();
					
				foreach($users_details_arr as $k=>$udata){
					$client_users[$udata['username']] = $udata['id'];
				}
				
				$contacts = array();
				foreach($csv_data_extra['contacts'] as $c=>$cont_data){
					if($c != 0){
						$cnt_types[] = $cont_data['3'];
						if(strlen($cont_data['7']) > 0 ){
							if( array_key_exists(trim($cont_data['7']),$user_substitute )){
								$cont_data['7'] = $user_substitute[trim($cont_data['7'])];
							}
						} else{
							$cont_data['7'] = $user_substitute["systemimport"];
						}
							
						$cnt_users[] = $cont_data['7'];
						$cnt_user_id[$cont_data['7']] = $client_users[$cont_data['7']];
						
						$cnt_data[$cont_data['7']] = $cont_data['7'];
						if(!empty($cont_data['3'])){
							$contacts[$cont_data['3']][$cont_data['0']][] = $cont_data;
						}
					}
				}
				
				$cnt_types = array_values(array_unique($cnt_types));
				$cnt_users = array_values(array_unique($cnt_users));
					
				
				// get users
				$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('clientid ="'.$clientid.'" OR usertype="SA"' );
				$users_details_arr =$usr->fetchArray();
					
				foreach($users_details_arr as $k=>$udata){
					$client_users[$udata['username']] = $udata['id'];
				}
	 
				foreach($cnt_users as $k=>$uname){
					if( !isset($client_users[$uname])){
						$new[] = $uname;
					}
				}
				$tel = array(); 
				$cfar = array(); 
				
				$verlauf_data = array();
				$contact_form_array = array();
				foreach($contacts as $ct_type => $ct_arr){
					foreach($ct_arr as $patient=>$cont_item_arr){
						
						if( in_array($patient,$pid2import)){
						
							foreach($cont_item_arr as $k=>$cont_item){
					
								if($cont_item['3'] =="Hausbesuch"){
	
									$cfar[] = $cont_item;
									
									if(strlen($cont_item['4']) > 0 ){
										$duration = $cont_item['4'];
									} else{
										$duration = 20;
									}
										
									$start_date = date("Y-m-d",strtotime($cont_item['1']));
									$start_time = date("H:i:s",strtotime($cont_item['2']));
									$start_date_time = date("Y-m-d",strtotime($cont_item['1'])).' '.date("H:i:s",strtotime($cont_item['2']));
									$end_date = strtotime("+".$duration." minutes",strtotime($start_date_time));
									$end_date_time  =  date("Y-m-d H:i:s",$end_date);
										
										
									$contact_form_array[$patient][] =  array(
											"ipid" => 	$patients_array[$patient],
											"start_date" => 	$start_date_time,
											"end_date" => 	$end_date_time,
											"duration" => 	$duration  ,
											"begin_date_h" => 	date("H",strtotime($start_date_time))  ,
											"begin_date_m" => 	date("i",strtotime($start_date_time))  ,
											"end_date_h" => 	date("H",strtotime($end_date_time))  ,
											"end_date_m" => 	date("i",strtotime($end_date_time))  ,
											"date" => 	$start_date_time  ,
											"billable_date" => 	$start_date_time  ,
											"fahrtzeit" => 	$cont_item['6']  ,
											"fahrtstreke_km" => 	$cont_item['5'],
											"user" => 	$cnt_user_id[$cont_item['7']],
											"create_user" => 	$cnt_user_id[$cont_item['7']],
											"done_date" => 	$start_date_time,
											"done_name" => 	"contact_form",
									);
								}
								/* elseif($cont_item['3'] =="Telefonkontakt"){
									
									 
									$tell[] = $cont_item;
									$course_title = "";
									$course_type = "XT";
									$text = "importierter Eintrag aus PalliDoc";
	
									if(strlen($cont_item['4']) > 0 ){
										$duration = $cont_item['4'];
									} else{
										$duration = 20;
									}
									$start_date = date("Y-m-d",strtotime($cont_item['1']));
									$start_time = date("H:i:s",strtotime($cont_item['2']));
									$start_date_time = date("Y-m-d",strtotime($cont_item['1'])).' '.date("H:i:s",strtotime($cont_item['2']));
									$done_date_comment = date("d.m.Y H:i",strtotime($start_date_time)); 
									$done_date = date("Y-m-d H:i:s",strtotime($start_date_time)); 
									
									$course_date = date("Y-m-d H:i:s"); 
	
									
									$course_title = $duration;
									$course_title .= " | ".$text;
									$course_title .= " | ".$done_date_comment;
										
									$verlauf_data[$patient][] =  array(
											"ipid" => 	$patients_array[$patient],
											"course_date" => 	$course_date,
											"course_type" => 	Pms_CommonData::aesEncrypt($course_type),
											"course_title" => 	Pms_CommonData::aesEncrypt($course_title),
											"done_name" => 	"phone_verlauf",
											"done_date" => 	$done_date,
											"user_id" => 	$cnt_user_id[$cont_item['7']],
											"create_user" => 	$cnt_user_id[$cont_item['7']]
											
									);
									
								} */
							}
						
						}
					}
				}		
				
				foreach($patients_array as $p_import => $ipid){
					
					// INSERT CONTACTS
					foreach($contact_form_array[$p_import] as $k=>$cf_data)
					{
						$result ="";
						$insert_cf = new ContactForms();
						$insert_cf->ipid = $ipid;
						// -----------------VISIT START DATE AND END DATE ------- 
						$insert_cf->start_date = $cf_data['start_date'];
						$insert_cf->end_date =  $cf_data['start_date'];
						$insert_cf->begin_date_h = $cf_data['begin_date_h'];
						$insert_cf->begin_date_m = $cf_data['begin_date_m'];
						$insert_cf->end_date_h = $cf_data['end_date_h'];
						$insert_cf->end_date_m = $cf_data['end_date_m'];
						$insert_cf->billable_date =  $cf_data['start_date'];
						$insert_cf->date =  $cf_data['start_date'];
						// --------------------------------------------------------
						$insert_cf->form_type = 427;
						$insert_cf->fahrtzeit = $cf_data['fahrtzeit'];
						$insert_cf->fahrtstreke_km = $cf_data['fahrtstreke_km'];
						$insert_cf->create_user = $cf_data['create_user'];
						$insert_cf->save();
						$result = $insert_cf->id;
						
						$done_date="";
						
						if($result){
							
							$done_date = $cf_data['start_date'];
							$course_date = date("Y-m-d H:i:s", time());
							
							$cust = new PatientCourse();
							$cust->ipid = $ipid;
							$cust->course_date = $course_date;
							$cust->course_type = Pms_CommonData::aesEncrypt("F");
							$cust->course_title = Pms_CommonData::aesEncrypt("Kontaktformular  hinzugefügt");
							$cust->tabname = Pms_CommonData::aesEncrypt("contact_form");
							$cust->recordid = $result;
							$cust->user_id = $cf_data['user'];
							$cust->done_date = $done_date;
							$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
							$cust->done_id = $result;
							$cust->save();
							
							
							$course_date = date("Y-m-d H:i:s", time());
							$cust = new PatientCourse();
							$cust->ipid = $ipid;
							$cust->course_date = $course_date;
							$cust->course_type = Pms_CommonData::aesEncrypt("K");
							$cust->course_title = Pms_CommonData::aesEncrypt($cf_data['begin_date_h'] . ":" . $cf_data['begin_date_m'] . ' - ' . $cf_data['end_date_h'] . ':' . $cf_data['end_date_m'] . '  ' . date("d.m.Y",strtotime($cf_data['date'])));
							$cust->tabname = Pms_CommonData::aesEncrypt("contact_form_time");
							$cust->user_id = $cf_data['user'];
							$cust->done_date = $done_date;
							$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
							$cust->done_id = $result;
							$cust->save();
							
							if(strlen($cf_data['fahrtzeit'])){
								
								$cust = new PatientCourse();
								$cust->ipid = $ipid;
								$cust->course_date = $course_date;
								$cust->course_type = Pms_CommonData::aesEncrypt("K");
								$cust->course_title = Pms_CommonData::aesEncrypt("Fahrtzeit: " . $cf_data['fahrtzeit']);
								$cust->tabname = Pms_CommonData::aesEncrypt("contact_form_fahrtzeit");
								$cust->user_id = $cf_data['user'];
								$cust->done_date = $done_date;
								$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
								$cust->done_id = $result;
								$cust->save();
							}
							
							if(strlen($cf_data['fahrtstreke_km'])){
								$cust = new PatientCourse();
								$cust->ipid = $ipid;
								$cust->course_date = $course_date;
								$cust->course_type = Pms_CommonData::aesEncrypt("K");
								$cust->tabname = Pms_CommonData::aesEncrypt("contact_form_");
								$cust->course_title = Pms_CommonData::aesEncrypt("Fahrtstrecke: " . $cf_data['fahrtstreke_km']);
								$cust->user_id = $cf_data['user'];
								$cust->done_date = $done_date;
								$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
								$cust->done_id = $result;
								$cust->save();
							}
							$contact_patients[] = $ipid;
						}
					}
					
					// INSERT Verlauf
					/* if( ! empty($verlauf_data[$p_import])){
						
						$collection = new Doctrine_Collection('PatientCourse');
						$collection->fromArray($verlauf_data[$p_import]);
						$collection->save();
						$xt_patients[] = $ipid;
					} */
					
					
					$current .= $ipid.",";
					file_put_contents($file, $current);
				}
			}
			
			if(strlen($current)>0){
				$imported_patients_form = explode(',',$current);
			}
			
			echo "<pre>";
			print_R("contact patients");
			print_R($contact_patients);
// 			print_R("xt patients");
// 			print_R($xt_patients);
			print_R("final- all");
			print_R($imported_patients_form);
			echo "<pre>";
			exit;
			exit;
			exit;
			exit;
			exit;
			exit;
			exit;
			exit;
			exit;
			exit;
			exit;
			exit;
			exit;
			$location_master = array_values(array_unique($location_master));
			exit;
			
	
			
			$discharge_method = array();
			$discharge_method[] = "Behandlungsbeendigung / Entlassung"; // STUPID
			$falls_data_discharges = array();
			$falls_data_dec = array();
			$discharge_location = array();
			$sapv_types = array();
			$csv_patients_falls= array();
			
			foreach($csv_data_extra['falls'] as $c=>$cont_data){
				if($c != 0 ){
					
					$sapv_types[] = $cont_data['2'];
					$csv_patients_falls[] = $cont_data['0'];
					if( ! empty($cont_data['6'])){
						
						if(strlen($cont_data['7'])){
							$discharge_method[] = $cont_data['7'];
						} else{
							$discharge_method[] = "nicht bekannt";
						}
						
						if(strlen($cont_data['8'])){
							$discharge_location[] = $cont_data['8'];
						}
					}
					
					$falls_data_dec[$cont_data['0']][] = $cont_data;
					if(strlen($cont_data['6']) > 0 ){
						$falls_data_discharges[$cont_data['0']]["discharge_dates_array"][] = $cont_data['6'];
					}
					
				}
			}
// 			dd($csv_patients,$csv_patients_falls);
			foreach($csv_patients as $pid){
				if( ! in_array($pid,$csv_patients_falls)){
					$no_fall_data[] = $pid;
				}
			}
			
// 			dd($no_fall_data);
// 			dd($falls_data_discharges);
			// get client discharge methods
			$discharge_method = array_values(array_unique($discharge_method));
			
			$client_dm_q = Doctrine_Query::create()
			->select('*')
			->from('DischargeMethod')
			->where('clientid =?',$clientid)
			->andWhere('isdelete=0');
			$client_dm = $client_dm_q->fetchArray();
			
	 		if( ! empty($client_dm)){
	 			foreach($client_dm as $k=>$dm_data){
	 				$client_discharge_methods[$dm_data['description']] = $dm_data['id'];
	 			}
	 		}
 
		 	if( ! empty($discharge_method)){
		 		foreach($discharge_method as $k=>$dm_name){
		 			if ( ! array_key_exists($dm_name, $client_discharge_methods)) {
		 				$dmet = new DischargeMethod();
		 				if($dm_name == "Verstorben"){
			 				$dmet->abbr = "TOD";
		 				}else{
			 				$dmet->abbr = mb_substr($dm_name, 0, 4, "UTF-8");
		 				}
		 				$dmet->description = $dm_name;
		 				$dmet->clientid = $clientid;
		 				$dmet->save();
		 				$new_dmet= $dmet->id;
		 				$client_discharge_methods[$dm_name] =  $new_dmet;
		 			}
		 		}
		 	}
 
			// get client discharge locations
		 	$discharge_location = array_values(array_unique($discharge_location));
			
			$client_dloc_q = Doctrine_Query::create()
			->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
			->from('DischargeLocation')
			->where('clientid =?',$clientid)
			->andWhere('isdelete=0');
			$client_dloc = $client_dloc_q->fetchArray();

			if( ! empty($client_dloc)){
				foreach($client_dloc as $k=>$loc_data){
					$client_discharge_locations[$loc_data['location']] = $loc_data['id'];
				}
			}
			
			// NO insert is needed
			
			
			
			
			// get client locations
			$client_locations = array();
			$client_loc_q = Doctrine_Query::create()
			->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
			->from('Locations')
			->where('client_id = ?',$clientid)
			->andWhere('isdelete=0');
			$client_loc = $client_loc_q->fetchArray();
			
			if( ! empty($client_loc)){
				foreach($client_loc as $k=>$loc_data){
					$client_locations[$loc_data['location']] = $loc_data['id'];
				}
			}
				
			// insert if needed
			if( ! empty($location_master)){
				foreach($location_master as $k=>$loc_name){
					if ( ! array_key_exists($loc_name, $client_locations)) {
						$cloc = new Locations();
						$cloc->location = Pms_CommonData::aesEncrypt($loc_name);
						$cloc->client_id = $clientid;
						$cloc->save();
						$new_cloc= $cloc->id;
						$client_locations[$loc_name] =  $new_cloc;
					}
				}
			}
			
			
			
			// DIAGNOSIS MAIN
			$dg = new DiagnosisType();
			
			// get diagnosis types
			$abb1 = "'HD'";
			$ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
			if($ddarr1){
			
				$comma = ",";
				$typeid = "'0'";
				foreach($ddarr1 as $key => $valdia)
				{
					$type_id_array[] = $valdia['id'];
				}
				$main_diagnosis_type = $type_id_array[0];
			}
			else
			{
				// if no diagnisis types - insert
				$res = new DiagnosisType();
				$res->clientid = $clientid;
				$res->abbrevation = 'HD';
				$res->save();
				$main_diagnosis_type = $res->id;
			}
			
			 	
			
			
			
			//standby and discharge in the same line
			//8669401
			//7351251
			//9540735

			
// 			For example a) if we have a standby "line" and no discharge- we discharge the patient with the last Folgeverordnung end date.
// 			If the patient has a discharge (b) or c) example) - then we ignore the new standby period.
			
			$standby_shit = array();
			$sapv_periods = array();
			
			$sapv_substitute = array(
					"Beratung"=>1,
					"Koordination"=>2, 
					"Teilversorgung"=>3, 
					"Volleversorgung"=>4
			);
			
			$sapv_order = array(
				"SAPV Erstverordnung"=>1,
				"SAPV Folgeverordnung"=>2
 			);
			
			
			foreach($falls_data_dec as $patient =>$falls){
				if($patient != 0 ){
					$x= 0 ;
					foreach($falls as $k=>$fd){
						if(trim($fd['2']) == "Warteliste"){
							$standby_shit[$patient] = $falls;
						}
						
						if(trim($fd['2']) == "Warteliste" && ! empty($falls_data_discharges[$patient]["discharge_dates_array"])){
							unset($falls_data_dec[$patient][$k]);
						} 
						
						if(trim($fd['2'])!= "Warteliste" && strlen($fd['4']) > 0 ){
							$start_date = "";
							$end_date = "";
							$start_date = date("Y-m-d H:i:s",strtotime($fd['3']));
							$end_date = date("Y-m-d H:i:s",strtotime($fd['4']));
							
							$types_label_arr = array();
							$types_label_arr=explode(",",$fd['5']);
							$verordnet_arr = array();
							foreach($types_label_arr as $tp){
								$verordnet_arr[] = $sapv_substitute[trim($tp)];
							}
							asort($verordnet_arr);
							// sapv periods
							$sapv_periods [$patient][$x]['patient_id'] = $fd['0'];
							$sapv_periods [$patient][$x]['verordnet'] = implode(",",$verordnet_arr);
							$sapv_periods [$patient][$x]['verordnet_von'] = "PALLIDOC Import";
							$sapv_periods [$patient][$x]['verordnet_von_type'] = "verordnet_von_type";
							$sapv_periods [$patient][$x]['status'] = "2";
							$sapv_periods [$patient][$x]['approved_number'] = $fd['1'];
							$sapv_periods [$patient][$x]['sapv_order'] =  $sapv_order[trim($fd['2'])]; //Verordnung order
							$sapv_periods [$patient][$x]['verordnungam'] = $start_date; //Verordnung Start Date / admission date
							$sapv_periods [$patient][$x]['verordnungbis'] = $end_date; //Verordnung End Date
							$x++;
						}
						
					}
				}
			}
			
			
			foreach($sapv_periods  as $k=>$patient_sapv){
				usort($patient_sapv, array(new Pms_Sorter('verordnungam'), "_date_compare"));
				$sapv_periods [$k]= $patient_sapv;
			}
			
			
			
			
			
// 			dd($falls_data_dec["9906783"]);
			
			// PATIENTS PERIODS
			$active_periods = array();
			$inserted = array();
			$multiple_fall_issues = array();
			$discharge_details = array();
			foreach($falls_data_dec as $patient =>$falls){
				if($patient != 0 ){
					$i= 0 ;
					foreach($falls as $k=>$fd){
						
						$start_date = "";
						$end_date = "";
						$start_date = date("Y-m-d H:i:s",strtotime($fd['3']));
						$end_date = date("Y-m-d H:i:s",strtotime($fd['4']));
						$ignore_standby = 0;
					
			 
						if( ! array_key_exists($patient."-".$start_date.$end_date, $inserted)){
							
							$las_sapv_period = array();
							 if(trim($fd['2']) == "Warteliste" && empty($falls_data_discharges[$patient]["discharge_dates_array"]) && count($falls) > 1){
								$ignore_standby = 1;
								//$discharge_date=  lates sapv end
								$las_sapv_period = end($sapv_periods[$patient]);
								$fd['6'] = $las_sapv_period ['verordnungbis'];
								$fd['7'] = "Behandlungsbeendigung / Entlassung";
// 								$active_periods[$patient][$i]['CRAZY'] = "YES";
							}
							
							
							if(trim($fd['2']) == "Warteliste"){
								$active_periods[$patient][$i]['admission_date_standby'] = $start_date; // admission date
							}
							
							$active_periods[$patient][$i]['admission_date'] = $start_date; //Verordnung Start Date / admission date
							
							$discharge_data = array();
							if(!empty($fd['6']) && !empty($fd['7'])){
									
								$discharge_date = date('Y-m-d H:i:s',strtotime($fd['6']));
								$active_periods[$patient][$i]['discharge_date'] = $discharge_date;
								if(strlen($fd['7']) >0 ){
									$discharge_data['discharge_method'] = $client_discharge_methods[trim($fd['7'])];;
								} else{
									$discharge_data['discharge_method'] = $client_discharge_methods["nicht bekannt"];
								}
								$discharge_data['discharge_method_name'] = trim($fd['7']);
								
								if(strlen($fd['8']) >0 ){
									$discharge_data['discharge_location'] = $client_discharge_locations[trim($fd['8'])];
									$discharge_data['discharge_location_name'] = trim($fd['8']);
								}
								if(strlen($fd['9']) > 0  && strlen($fd['9']) != strlen($fd['6'])){
									$discharge_data['death_date'] = date("Y-m-d H:i:s",strtotime($fd['9']));
								}
		
								$discharge_details[$patient][$discharge_date] = $discharge_data;
							}
							
							$inserted[$patient."-".$start_date.$end_date][] = $start_date;
							
							
						}  else{
							
							$multiple_fall_issues[$patient."-".$start_date.$end_date][] = $start_date;
						}
						$i++;
					}
				}
			}
// 			dd($discharge_details);
			foreach($active_periods as $k=>$patient){
			
				usort($patient, array(new Pms_Sorter('admission_date'), "_date_compare"));
				$active_periods[$k]= $patient;
			}

// 			$active_periods = array($active_periods["9906783"]);
// 			dd($active_periods);
// 			dd($active_periods,$discharge_details['9906783']);
			$patient_periods = array();
			foreach($active_periods as $patient =>$periods){
				
					$first_admission = array();
					$discharge_dates = array();
					$standby_dates = array();
					
					foreach($periods as $k=>$row){
						
						/* if(isset($row['admission_date']) && isset($row['discharge_date']) && ! isset($row['admission_date_standby'])){
	
							if(strtotime($row['discharge_date']) < strtotime($row['admission_date'])){
								//dd("echo","Active",$patient,$k,$periods);
								$patients_with_issues[] = $patient;
								
							}
						}
						if(isset($row['admission_date_standby']) && isset($row['discharge_date'])){
	
							if(strtotime($row['discharge_date']) < strtotime($row['admission_date_standby'])){
								//dd("echo","Standby",$patient,$k,$periods);
								$patients_with_issues[] = $patient;
							}
						}
						continue; */
						
						if(empty($first_admission) && isset($row['admission_date'])){
							$first_admission = array("key"=>$k,"date"=>$row['admission_date']);
						}
						
						if(isset($row['discharge_date'])){
							$discharge_dates = array("key"=>$k,"date"=>$row['discharge_date']);
						}
						if(isset($row['admission_date_standby'])){
							$standby_dates = array("key"=>$k,"date"=>$row['admission_date_standby']);
						}
						
						if( ! empty($first_admission) && ! empty($discharge_dates) ){
	
							if(strtotime($first_admission['date']) > strtotime($discharge_dates['date'])){
								//dd("echo",$patient,$periods,$first_admission,$discharge_dates);
							}
							
							
							$patient_periods[$patient][] = array(
									"activ"=>$first_admission['date'].' - '.$discharge_dates['date'],
									"admission_date" => $first_admission['date'], 
									"discharge_date" => $discharge_dates['date'],
									"isdischarged" =>"1",
									"discharge_method" => $discharge_details[$patient][$discharge_dates['date']]['discharge_method'],
									"discharge_method_name" => $discharge_details[$patient][$discharge_dates['date']]['discharge_method_name'],
									"discharge_location" => $discharge_details[$patient][$discharge_dates['date']]['discharge_location'],
									"discharge_location_name" => $discharge_details[$patient][$discharge_dates['date']]['discharge_location_name'],
									"death_date" => $discharge_details[$patient][$discharge_dates['date']]['death_date']
							);
							
							$first_admission = array();
							$discharge_dates = array();
							
						}
					}
					
					if( ! empty($first_admission) && empty($discharge_dates)){
						
						if( ! empty($standby_dates)){
							$patient_periods[$patient][] =  array(
									"STANDBY"=>$first_admission['date'].' - XXXXXXXXXXXXXXXXX',
									"admission_date" => $first_admission['date'],
									"isstandby" => "1",
									"isdischarged" =>"0"
							);
						} else{
							$patient_periods[$patient][] = array(
									"activ opened"=>$first_admission['date'].' - XXXXXXXXXXXXXXXXX',
									"admission_date" => $first_admission['date'],
									"isdischarged" =>"0"
									
									
							);
						}
					}
// 					$patient_periods[$patient][] = $periods;
			}
			
// 				dd($patient_periods);

// 			dd("START");
			// DIAGNOSIS 
			$diagnosis_array =array();
			foreach($csv_data_extra['diagnosis'] as $d=>$dgn){
				if($d != 0 ){
					$diagnosis_array[$dgn[0]][] = $dgn;
				}
			}
			
// 			dd($diagnosis_array);
			
// 			dd($cnt_users,$cnt_data);
			// get diagnosis types
			// if no diagnisis types - insert
// 				 dd($csv_data);
// 				 8686352
//8949936

			//7301285

			$patient = Doctrine_Query::create()
			->select("p.*,e.*")
			->from('PatientMaster p')
			->leftJoin("p.EpidIpidMapping e")
			->andWhereIn('e.clientid', array($clientid))
			->andWhere("p.import_pat != ''")
			->andWhere("p.isdelete = 0 ");
			$patient_details = $patient->fetchArray();
			$existing_patients = array();
			
			foreach($patient_details as $k => $pat_val)
			{
				$existing_patients[] = $pat_val['import_pat'];
			}
			//10269167
// 			dd($existing_patients);
			$inserted_ipids = array();
			foreach($csv_data as $k_csv_row => $import_type)
			{
				
				if($k_csv_row != 0){
					if(!in_array($import_type['0'],$existing_patients)){
						if( strlen(trim($import_type['0'])) > 0  ){
							$imported_ids[] = $import_type['0'];
						}
						// add doubled data
						
						$inserted_ipids[]= $this->import_patient_rp_new($k_csv_row, $clientid, $userid,  $csv_data, $patient_periods,$side_diagnosis_type,$main_diagnosis_type,$diagnosis_array,$client_locations,$sapv_periods);
					}
				}
			}
			
			$end_dgp =  microtime(true) - $start_dgp;
			
			echo "TIME => ".round($end_dgp, 0).'SECONDS';
// dd($inserted_ipids);			
			/* if( ! empty($inserted_ipids) ){
				 
		
				$patient = Doctrine_Query::create()
				->select("p.*,e.*")
				->from('PatientMaster p')
				->leftJoin("p.EpidIpidMapping e")
				->andWhereIn('e.clientid', array($clientid))
				->andWhere("p.import_pat != ''")
				->andWhere("p.isdelete = 0 ");
				$patient_details = $patient->fetchArray();
				 
				foreach($patient_details as $k => $pat_val)
				{
					if(in_array($pat_val['ipid'],$inserted_ipids)){
						$patients_array[$pat_val['import_pat']] = $pat_val['ipid'];
					}
					// 	 		$ipid2client[$patients_array[$pat_val['import_pat']]['ipid']] = $pat_val['EpidIpidMapping']['clientid'];
					// 	 		$patients_array[$pat_val['import_pat']]['client'] = $pat_val['EpidIpidMapping']['clientid'];
					// 	 		$patient2client[$pat_val['import_pat']] = $pat_val['EpidIpidMapping']['clientid'];
				}
				// 	 	print_r($patients_array); exit;
 
				dd($patients_array); 
			} */
			exit;
			exit;
			exit;
			
			
			
			
			
			
			
		 
		
		
// 			foreach($csv_data_falls as $k_csv_row => $dimport){
// 				if($dimport[0] != "EPID"){
		
// 					$csv_falls[$dimport[0]]['isdischarged'] = '0';
// 					if(strlen($dimport[1]) > 0 ){
// 						$csv_falls[$dimport[0]]['admission_date'] = date("Y-m-d H:i:s",strtotime($dimport[1]));
						 
// 						if(strlen($dimport[2]) > 0 ){ // patient has discharge
// 							$csv_falls[$dimport[0]]['discharge_date'] = date("Y-m-d H:i:s",strtotime($dimport[2]));
// 							$csv_falls[$dimport[0]]['discharge_method'] = $dimport[3];
// 							$csv_falls[$dimport[0]]['discharge_location'] = $dimport[4];
// 							$csv_falls[$dimport[0]]['isdischarged'] = '1';
// 						}
// 					} else{
// 						$csv_falls[$dimport[0]]['admission_date'] = date("Y-m-d H:i:s");
// 					}
// 				}
// 			}
		
// 			foreach($csv_data as $k_csv_row => $import_type)
// 			{
// 				if($k_csv_row != 0){
// 					$this->import_patient_rp($k_csv_row, $clientid, $userid,  $csv_data, $csv_falls,$side_diagnosis_type,$main_diagnosis_type);
// 					//     	            $this->import_patient_rp_second($k_csv_row, $clientid, $userid,  $csv_data, $csv_falls,$side_diagnosis_type,$main_diagnosis_type);
// 				}
// 			}
		
		
// 			$import_session = new Zend_Session_Namespace('importSession');
// 			$import_session->userid = '';
// 			$import_session->form_data = '';
		}
		

		
		
		
		
		
		private function import_patient_rp_new($csv_row, $clientid, $userid,  $csv_data, $falls_array,$side_diagnosis_type,$main_diagnosis_type,$diagnosis_array,$client_locations,$sapv_periods)
		{
			$Tr = new Zend_View_Helper_Translate();

			$imported_csv_data = $csv_data[$csv_row];
			
			$gender = array("m"=>"1","w"=>"2");
			
			$patient_falls = array();
			$patient_falls = $falls_array[$imported_csv_data['0']];
			
			$patient_sapv_periods = array();
			$patient_sapv_periods = $sapv_periods[$imported_csv_data['0']];
			
			
			$current_fall ="";
			$current_fall = end($patient_falls);

			$first_period = "";
			$first_period = $patient_falls['0'];
			$first_admission_ever = $first_period['admission_date'];
			 
			$bd_date = "";
			if(strlen($imported_csv_data['4']) > 0 ){
				$bd_date = date('Y-m-d', strtotime($imported_csv_data['4']));
			}
		
		
			$curent_dt = date('Y-m-d H:i:s', time());
			$curent_dt_dmY = date('d.m.Y', time());
		
			//generate ipid and epid
			$ipid = Pms_Uuid::GenerateIpid();
		
			$epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
			$epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
			//insert patient
			$cust = new PatientMaster();
			$cust->ipid = $ipid;
			$cust->recording_date = $curent_dt;
		
			$cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['2']);
			$cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['1']);
// 			$cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['8']);
			$cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['6']);
			$cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['5']);
// 			$cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['10']);
// 			$cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
			// 		    $cust->email= Pms_CommonData::aesEncrypt($imported_csv_data['10']);
			// 		    $cust->living_will= $imported_csv_data['31'];
			$cust->birthd = $bd_date;
		
			if($gender[$imported_csv_data['3']]){
				$cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['3']]);
			} else {
				$cust->sex = Pms_CommonData::aesEncrypt("0");
			}
		
			
			if(!empty($current_fall)){
				$post_admission_date = $current_fall['admission_date'];
				$admission_date = $current_fall['admission_date'];
		
				$cust->admission_date = $current_fall['admission_date'];
				$cust->isdischarged =  $current_fall['isdischarged'];
				$cust->isstandby =  $current_fall['isstandby'];
				
				if($current_fall['isdischarged'] == "1"){
					$cust->traffic_status= '0';
				}
		
			} else {
// 				$cust->admission_date = date('1970-01-01 00:00:00'); //
// 				$post_admission_date = date('1970-01-01 00:00:00'); //
// 				$admission_date = date('1970-01-01 00:00:00'); //
				// JUST A GENERIC DATE 
				$cust->admission_date = date('2011-01-01 00:00:00'); //
				$post_admission_date = date('2011-01-01 00:00:00'); //
				$admission_date = date('2011-01-01 00:00:00'); //
				$first_admission_ever = date('2011-01-01 00:00:00'); //
				$cust->isstandby =  "1";
			}
		
			$cust->import_pat = $imported_csv_data['0'];
			$cust->isadminvisible= '1';
			$cust->save();
		
			//insert epid-ipid
			$res = new EpidIpidMapping();
			$res->clientid = $clientid;
			$res->ipid = $ipid;
			$res->epid = $epid;
			$res->epid_chars = $epid_parts['epid_chars'];
			$res->epid_num = $epid_parts['epid_num'];
			$res->save();
		
			

			//write first entry in patient course
			$course = new PatientCourse();
			$course->ipid = $ipid;
			$course->course_date = $curent_dt;
			$course->course_type = Pms_CommonData::aesEncrypt('K');
			$course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
			$course->done_date = $curent_dt;
			$course->user_id = $userid;
			$course->tabname = Pms_CommonData::aesEncrypt('new_rp_clients_import');
			$course->save();
			
			// Erstkontakt durch
			$existing = array(
					"Patient/Angehörige"=>"1",
					"Beratungsdienst"=>"2",
					"Hausarzt"=>"3",
					"Stationäres Hospiz"=>"4",
					"Krankenhaus"=>"5",
					"Palliativstation"=>"6",
					"Facharzt"=>"7",
					"ambulanter Hospizdienst"=>"8",
					"Ambulante Pflege"=>"9",
					"Stationäre Pflege"=>"10",
					"Sonstige"=>"11"
			);
			$first_patient_contact="";
			if(strlen($imported_csv_data['13']) > 0){
			
				if(array_key_exists(trim($imported_csv_data['13']), $existing)){
					$first_patient_contact = $existing[trim($imported_csv_data['13'])];
				}
			
			}
			
			// INSERT FALLS
			if( ! empty($patient_falls)){
			
				foreach($patient_falls as $k=>$period_data){
			
					$patientreadmission = new PatientReadmission();
					$patientreadmission->user_id = $userid;
					$patientreadmission->ipid = $ipid;
					$patientreadmission->date = $period_data['admission_date'];
					$patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
					if(!empty($first_patient_contact) && $period_data['admission_date'] == $first_admission_ever){
						$patientreadmission->first_contact = $first_patient_contact; //Erstkontakt durch
					}
					$patientreadmission->save();
					
					$admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($period_data['admission_date']));
					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
					$cust->user_id = $userid;
					$cust->save();
			
					if($period_data['isdischarged'] == "1" && isset($period_data['discharge_date'])){
							
						$pd = new PatientDischarge();
						$pd->ipid = $ipid;
						$pd->discharge_date = $period_data['discharge_date'];
						$pd->discharge_location = $period_data['discharge_location'];
						$pd->discharge_method = $period_data['discharge_method'];
						if($period_data['discharge_date'] != $current_fall['discharge_date']){
							$pd->isdelete = 1;
						}
						$pd->save();
						
						
						
						// Patient discharge course - discharge date START
						$discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($period_data['discharge_date']))."";
						$cust = new PatientCourse();
						$cust->ipid = $ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
						$cust->user_id = $userid;
						$cust->save();
						
						$comment="Patient wurde am ".date('d.m.Y H:i', strtotime($period_data['discharge_date']))."  entlassen \n Entlassungsart : ".$period_data['discharge_method_name']."\n ";
						$pc = new PatientCourse();
						$pc->ipid = $ipid;
						$pc->course_date = date("Y-m-d H:i:s",time());
						$pc->course_type=Pms_CommonData::aesEncrypt("K");
						$pc->course_title=Pms_CommonData::aesEncrypt($comment);
						$pc->tabname=Pms_CommonData::aesEncrypt("discharge");
						$pc->user_id = $userid;
						$pc->save();
						
							
						$patientreadmission = new PatientReadmission();
						$patientreadmission->user_id = $userid;
						$patientreadmission->ipid = $ipid;
						$patientreadmission->date = $period_data['discharge_date'];
						$patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
						$patientreadmission->save();

						if(!empty($period_data['death_date']) && $current_fall['discharge_method_name'] != "Verstorben"){
							$patientdeath_add = new PatientDeath();
							$patientdeath_add->ipid = $ipid;
							$patientdeath_add->death_date = date("Y-m-d H:i:s",strtotime($period_data['death_date']));
							$patientdeath_add->save();
						}
						
					} elseif($period_data['isstandby'] == "1"){
							
						$patientreadmission = new PatientStandby();
						$patientreadmission->ipid = $ipid;
						$patientreadmission->start = date("Y-m-d",strtotime($period_data['admission_date']));
						$patientreadmission->save();
			
						$patientreadmission = new PatientStandbyDetails();
						$patientreadmission->ipid = $ipid;
						$patientreadmission->date = $period_data['admission_date'];
						$patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
						$patientreadmission->save();
							
					}
				}
			} else{
				
				// INSERT DATA IF NO FALLS - make them OPENED and standby 
				$patientreadmission = new PatientReadmission();
				$patientreadmission->user_id = $userid;
				$patientreadmission->ipid = $ipid;
				$patientreadmission->date = $first_admission_ever;
				$patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
				$patientreadmission->save();
				
				
				$patientreadmission = new PatientStandby();
				$patientreadmission->ipid = $ipid;
				$patientreadmission->start = date("Y-m-d",strtotime($first_admission_ever));
				$patientreadmission->save();
					
				$patientreadmission = new PatientStandbyDetails();
				$patientreadmission->ipid = $ipid;
				$patientreadmission->date = $first_admission_ever;
				$patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
				$patientreadmission->save();
			}

			//insert in patient case
			$case = new PatientCase();
			$case->admission_date = $first_admission_ever;
			$case->epid = $epid;
			$case->clientid = $clientid;
			$case->save();

			
			
			
			// client_locations
			if(strlen($imported_csv_data['15']) > 0){
				$cust = new PatientLocation();
				$cust->ipid = $ipid;
				$cust->clientid = $clientid;
				$cust->valid_from = $first_admission_ever;
				$cust->valid_till = "0000-00-00 00:00:00";
				$cust->location_id = $client_locations[trim($imported_csv_data['15'])];
				$cust->save();
			}
				
			
		
			// diagnosis data
			if(!empty($diagnosis_array[$imported_csv_data['0']])){
				foreach($diagnosis_array[$imported_csv_data['0']] as $line=>$values){
					
					$free_diagno_id ="";
					$diagno_free = new DiagnosisText();
					$diagno_free->clientid = $clientid;
					$diagno_free->icd_primary = $values['1'];
					$diagno_free->free_name = $values['2'];
					$diagno_free->save();
					$free_diagno_id = $diagno_free->id;
			
					$diagno = new PatientDiagnosis();
					$diagno->ipid = $ipid;
					$diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
					$diagno->diagnosis_type_id = $main_diagnosis_type ;
					$diagno->diagnosis_id = $free_diagno_id;
					$diagno->description = $values['2'];
					$diagno->icd_id = "0";
					$diagno->save();
				}
			}
	 

			// health insurance
			if(strlen($imported_csv_data['7']) > 0){ // KK_name
				// insert in health insurance master
				$hinsu = new HealthInsurance();
				$hinsu->clientid = $clientid;
				$hinsu->name = $imported_csv_data['7'];//KK_name
				$hinsu->iknumber = $imported_csv_data['8'];//KK_IK
				$hinsu->valid_from = date("Y-m-d", time());
				$hinsu->extra = 1;
				$hinsu->onlyclients = '0';
				$hinsu->save();
				$company_id =$hinsu->id;
		
				// insert in patient
				$patient_hi = new PatientHealthInsurance();
				$patient_hi->ipid = $ipid;
				$patient_hi->companyid = $company_id;
				$patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['7']);//KK_name
				$patient_hi->insurance_no = $imported_csv_data['8'];//KK- Versichertennummer
				$patient_hi->save();
			}
		
		// 	   [10] => Patientenverfügung
		//     [11] => Vorsorgevollmacht
		//     [12] => Betreungsverfügung
 
			if(strlen($imported_csv_data['10']) > 0 && strtolower($imported_csv_data['10']) == "ja"){
				$patient_acp_l = new PatientAcp();
				$patient_acp_l->ipid = $ipid;
				$patient_acp_l->division_tab = "living_will";
				$patient_acp_l->active = "yes";
				$patient_acp_l->save();
			}
			if(strlen($imported_csv_data['11']) > 0 && strtolower($imported_csv_data['11']) == "ja"){
				$patient_acp_h = new PatientAcp();
				$patient_acp_h->ipid = $ipid;
				$patient_acp_h->division_tab = "healthcare_proxy";
				$patient_acp_h->active = "yes";
				$patient_acp_h->save();
			}
			if(strlen($imported_csv_data['12']) > 0 && strtolower($imported_csv_data['12']) == "ja"){
				$patient_acp_c = new PatientAcp();
				$patient_acp_c->ipid = $ipid;
				$patient_acp_c->division_tab = "care_orders";
				$patient_acp_c->active = "yes";
				$patient_acp_c->save();
			}
			
			

			// Pflegegrad
			$stage ="";
			if(strlen($imported_csv_data['14']) > 0){
				
				$stage_substitution = array(
						"Pflegegrad 3"=>"3",
						"Beantragt"=>"Beantragt",
						"Pflegegrad 2"=>"2",
						"Pflegegrad 4"=>"4",
						"Pflegegrad 5"=>"5",
						"Keine"=>"keine",
						"Pflegegrad 1"=>"1"
				);
				 $patient_stage_c = new PatientMaintainanceStage();
				 $patient_stage_c->ipid = $ipid;
				 $patient_stage_c->fromdate = date("Y-m-d",strtotime($first_admission_ever));
				 $patient_stage_c->stage = $stage_substitution[trim($imported_csv_data['14'])];
				 $patient_stage_c->save();
			}
			
			// SAPV FALLS
			if(!empty($patient_sapv_periods)){
				foreach($patient_sapv_periods as $s_int =>$sdetails){
			
					$master_fam = new FamilyDoctor();
					$master_fam->clientid = $clientid;
					$master_fam->last_name = $sdetails['verordnet_von'];
					$master_fam->indrop = 1;
					$master_fam->save();
					$master_fam_id = $master_fam->id;
			
					$cust = new SapvVerordnung();
					$cust->ipid = $ipid;
					$cust->sapv_order = $sdetails['sapv_order'];
					$cust->verordnet_von = $master_fam_id;
					$cust->verordnet_von_type = "verordnet_von_type";
					$cust->verordnungam = $sdetails['verordnungam'];
					$cust->verordnungbis = $sdetails['verordnungbis'];;
					$cust->regulation_start = $sdetails['verordnungam'];
					$cust->regulation_end= $sdetails['verordnungbis'];;
					$cust->verordnet = $sdetails['verordnet'];
					$cust->status = $sdetails['status'];
					$cust->approved_number = $sdetails['approved_number'];
					$cust->save();
				}
			}
			
			
			// karnofsky - ECOG
			$ecog_mapping = array(
					"100"=>"0",
					"90"=>"0",
					"80"=>"1",
					"70"=>"1",
					"60"=>"2",
					"50"=>"2",
					"40"=>"3",
					"30"=>"3",
					"20"=>"4",
					"10"=>"4",
					"0"=>"5"
			);
			$ecog_value = "";
			
			if(strlen($imported_csv_data['16']) > 0){
				$ecog_string = substr(trim($imported_csv_data['16']), 0, 2);
				$ecog_value = $ecog_mapping[$ecog_string]; 
			
				// DGP!!!!!
				if(strlen($ecog_value )>0){

					$stmb = new DgpKern();
					$stmb->ipid = $ipid;
					$stmb->form_type = "adm";
					$stmb->ecog = $ecog_value;
					$stmb->save();
					
					$result = $stmb->id;
					
					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s",time());
					$cust->course_type=Pms_CommonData::aesEncrypt("K");
					$cust->course_title=Pms_CommonData::aesEncrypt($comment);
					$cust->tabname = Pms_CommonData::aesEncrypt("dgpkernform");
					$cust->recordid = $result;
					$cust->user_id = $userid;
					$cust->save();
				}
			}
		
			
			return $ipid;
			
		}

		
		

		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		
		/*--------------------------------------------------*/
		/*---------------WL HAMM // TODO-1682 --------------*/
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		
		public function patient_import_handler_wl_hamm($import_data)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		
		    // get diagnosis types
		    $dg = new DiagnosisType();
		    $abb2 = "'HD'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    $comma = ",";
		    $typeid = "'0'";
		    foreach($ddarr2 as $key => $valdia)
		    {
		        $type_id_array[] = $valdia['id'];
		    }
		    $main_diagnosis_type = $type_id_array[0];
		    
		    
		    // healthinsurance
		    // get health insurance
		    $healtharray = Doctrine_Query::create()
		    ->select('*')
		    ->from('HealthInsurance')
		    ->where('isdelete = ?', 0)
		    ->andWhere('extra = 0')
		    ->andWhereIn("clientid ",array($clientid))
		    ->andWhere("onlyclients = '1'")
		    ->fetchArray();
		    	
		    $health_insurance_data = array();
		    if(!empty($healtharray)){
		        foreach($healtharray as $k=>$hi){
		            $health_insurance_data[$hi['iknumber']] = $hi; 		        
		        }
		        
		    }
		    
		    
		    // get imported patients
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', array($clientid))
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    // 	 	print_r($patient_details); exit;

		    $imported =  array();
		    foreach($patient_details as $k => $pat_val)
		    {
		        $imported[] = $pat_val['import_pat'];
		    }
		    
		    $current_import = array();
		    foreach($import_data as $patient_id => $import_details)
		    {
		        if(!empty($import_details)  && ! in_array($patient_id,$imported)){
		    
		            $imported_id = $this->import_patient_wl_hamm($patient_id,$import_details, $clientid, $userid,$main_diagnosis_type, $health_insurance_data);
		            if($imported_id){
    		            $imported[] = $imported_id;
    		            $current_import[] = $imported_id;
//     		            echo $imported_id."<br/>";
    		            
		            }
		        }
		    }
		
		    
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		    
		    return $current_import;
		}
		
		private function import_patient_wl_hamm($patient_id,$import_details, $clientid, $userid,$main_diagnosis_type,$health_insurance_data )
		{
		     if( empty($patient_id) || empty($import_details) ){
		         return false;
		     }
		
		    $curent_dt = date('Y-m-d H:i:s', time());
		
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		
		    $cust->last_name = Pms_CommonData::aesEncrypt($import_details['first_name']);
		    $cust->first_name = Pms_CommonData::aesEncrypt($import_details['last_name']);
		
		    $cust->street1 = Pms_CommonData::aesEncrypt($import_details['street1']);
		    $cust->zip = Pms_CommonData::aesEncrypt($import_details['zip']);
		    $cust->city = Pms_CommonData::aesEncrypt($import_details['city']);
		    $cust->birthd = $import_details['birthd'];
		    if($import_details['sex']){
    		    $cust->sex =  Pms_CommonData::aesEncrypt($import_details['sex']);
		    } else {
    		    $cust->sex =  Pms_CommonData::aesEncrypt("0");
		    }

		    if(!empty($import_details['falls'])){
		        
		        $cust->admission_date = $import_details['falls']['admission_date'];
		        $post_admission_date = $import_details['falls']['admission_date']; 
		        $admission_date = $import_details['falls']['admission_date']; 

		        $cust->isstandby = $import_details['falls']['isstandby'];
		        $cust->isdischarged = $import_details['falls']['isdischarged'];
		        
		
		    } else {
		
		        $post_admission_date = $curent_dt; //
		        $cust->admission_date = $curent_dt; //
		        $admission_date = $curent_dt; //
		
		        $cust->isstandby = "1";
		    }
		
		
		    $cust->import_pat = $import_details['import_pat'];
		    $cust->isadminvisible= '1';
		    $cust->save();
		    
		
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
		    // INSERT FALLS
		    if( ! empty($import_details['falls'])){
		        	
		            $patientreadmission = new PatientReadmission();
		            $patientreadmission->user_id = $userid;
		            $patientreadmission->ipid = $ipid;
		            $patientreadmission->date = $import_details['falls']['admission_date'];
		            $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		            $patientreadmission->save();
		            	
		            $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($import_details['falls']['admission_date']));
		            $cust = new PatientCourse();
		            $cust->ipid = $ipid;
		            $cust->course_date = date("Y-m-d H:i:s", time());
		            $cust->course_type = Pms_CommonData::aesEncrypt("K");
		            $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		            $cust->user_id = $userid;
		            $cust->save();
		            	
		            if($import_details['falls']['isdischarged'] == "1" && isset($import_details['falls']['discharge_date'])){
		                	
		                $pd = new PatientDischarge();
		                $pd->ipid = $ipid;
		                $pd->discharge_date = $import_details['falls']['discharge_date'];
		                $pd->discharge_method = $import_details['falls']['discharge_method'];
		                $pd->save();
		    
		                // Patient discharge course - discharge date START
		                $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($import_details['falls']['discharge_date']))."";
		                $cust = new PatientCourse();
		                $cust->ipid = $ipid;
		                $cust->course_date = date("Y-m-d H:i:s", time());
		                $cust->course_type = Pms_CommonData::aesEncrypt("K");
		                $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
		                $cust->user_id = $userid;
		                $cust->save();
		    
		                $comment="Patient wurde am ".date('d.m.Y H:i', strtotime($import_details['falls']['discharge_date']))."  entlassen \n Entlassungsart : ".$import_details['falls']['discharge_method_name']."\n ";
		                $pc = new PatientCourse();
		                $pc->ipid = $ipid;
		                $pc->course_date = date("Y-m-d H:i:s",time());
		                $pc->course_type=Pms_CommonData::aesEncrypt("K");
		                $pc->course_title=Pms_CommonData::aesEncrypt($comment);
		                $pc->tabname=Pms_CommonData::aesEncrypt("discharge");
		                $pc->user_id = $userid;
		                $pc->save();
		    
		                	
		                $patientreadmission = new PatientReadmission();
		                $patientreadmission->user_id = $userid;
		                $patientreadmission->ipid = $ipid;
		                $patientreadmission->date = $import_details['falls']['discharge_date'];
		                $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
		                $patientreadmission->save();
		    
		            } elseif($import_details['falls']['isstandby'] == "1"){
		                	
		                $patientreadmission = new PatientStandby();
		                $patientreadmission->ipid = $ipid;
		                $patientreadmission->start = date("Y-m-d",strtotime($import_details['falls']['admission_date']));
		                $patientreadmission->save();
		                	
		                $patientreadmission = new PatientStandbyDetails();
		                $patientreadmission->ipid = $ipid;
		                $patientreadmission->date = $import_details['falls']['admission_date'];
		                $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		                $patientreadmission->save();
		                	
		            }
		    } else {
		    
		        // INSERT DATA IF NO FALLS - make them OPENED and standby
		        $patientreadmission = new PatientReadmission();
		        $patientreadmission->user_id = $userid;
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $first_admission_ever;
		        $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		    
		    
		        $patientreadmission = new PatientStandby();
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->start = date("Y-m-d",strtotime($first_admission_ever));
		        $patientreadmission->save();
		        	
		        $patientreadmission = new PatientStandbyDetails();
		        $patientreadmission->ipid = $ipid;
		        $patientreadmission->date = $first_admission_ever;
		        $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		        $patientreadmission->save();
		    }
 
		
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
		
		
		    //write first entry in patient course
		    $import_course_comment = 'Patienten importiert '.date('d.m.Y');
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt($import_course_comment);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_wl_hamm_import');
		    $course->save();
		
		
		    
		    // MEMO
		    if( ! empty($import_details['memo']['memo'])){
		        $save_memo = new PatientMemo();
		        $save_memo->ipid = $ipid;
		        $save_memo->memo = $import_details['memo']['memo'];
		        $save_memo->save();
		    }
		    
		    // Diagnosis 
		    if( ! empty($import_details['diagnosis'])){
		        
		        foreach($import_details['diagnosis'] as $dk=>$diag){
		            
        		    $diagno_free = new DiagnosisText();
        		    $diagno_free->clientid = $clientid;
        		    $diagno_free->icd_primary = $diag['icd'];
        		    $diagno_free->free_name =  $diag['free_text'];
        		    $diagno_free->save();
        		    $free_diagno_id = $diagno_free->id;
        		    
        		    $diagno = new PatientDiagnosis();
        		    $diagno->ipid = $ipid;
        		    $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
        		    $diagno->diagnosis_type_id = $main_diagnosis_type ;
        		    $diagno->diagnosis_id = $free_diagno_id;
        		    $diagno->icd_id = "0";
        		    $diagno->save();
		        }
		    }
		    
		    
		    // HEALTH INSRUSANCE
		    if( ! empty($import_details['PatientHealthInsurance'])){
		        $company_data = array();
		        if(!empty($health_insurance_data[ $import_details['PatientHealthInsurance']['institutskennzeichen']])){
		            
		            $company_data= $health_insurance_data[ $import_details['PatientHealthInsurance']['institutskennzeichen']];
		        
    		        $patient_hi = new PatientHealthInsurance();
    		        $patient_hi->ipid = $ipid;
    		        $patient_hi->companyid = $company_data['id'];
    		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($company_data['name']);
    		        $patient_hi->ins_street = Pms_CommonData::aesEncrypt($company_data['street1']);
    		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($company_data['zip']);
    		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($company_data['city']);
    		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($import_details['PatientHealthInsurance']['insurance_status']);
    		        $patient_hi->insurance_no = $import_details['PatientHealthInsurance']['insurance_no'];
    		        $patient_hi->institutskennzeichen = $import_details['PatientHealthInsurance']['institutskennzeichen'];
    		        $patient_hi->save();
		        }
		    }
		    
		    
		    //PATIENT   COURSE
		    
		    if( ! empty($import_details['patient_course'])){
		        
		        $course_array = array();
		        foreach($import_details['patient_course'] as $pc_k=>$pc_v){
		            
		            $course_array[] = array(
		                'ipid'=> $ipid,
		                'user_id'=>  $userid,
		                'course_date'=> $pc_v['course_date'],
		                'course_type'=>  Pms_CommonData::aesEncrypt($pc_v['course_type']),
		                'course_title'=>  Pms_CommonData::aesEncrypt($pc_v['course_title']),
		                'done_date'=>  $pc_v['done_date'],
		                'tabname'=>  Pms_CommonData::aesEncrypt('wl_hamm_import_bdoc'),
		            );
		        }
		        
		        if( ! empty($course_array)){
		            $collection = new Doctrine_Collection('PatientCourse');
		            $collection->fromArray($course_array);
		            $collection->save();
		        }
		    }
		    
		    return $patient_id;
		}
		
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/		
		

		
		/**
		 * 
		 * TODO-1970
		 * plz PREPARE the import of these patients
		 * Ancuta 14.12.2018 
		 * 
		 * 
		 * 
		 * Ignore PAdr_Ansprechpartner - patient contact person
		 * 
		 * 
		 * @param unknown $csv_data
		 * @param unknown $post
		 */
		public function patient_import_handler_nr_mambo($csv_data = array(), $post)
		{
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		
		    // get client locations
		    $locations = Locations::getLocations($clientid,0);
		    foreach($locations as $k=>$ldata){
		        $location_master[$ldata['location']] = $ldata['id'];
		    }
		
		    
		    // get imported patients
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', array($clientid))
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    $imported_patients_array = array();
		    foreach($patient_details as $k => $pat_val)
		    {
		        $imported_patients_array[$pat_val['import_pat']] = $pat_val['ipid'];
		    }
		    
		    
		    /* 
		    [0] => ﻿ID_Pat
		    [1] => Pat_Namen
		    [2] => Pat_Vorname
		    [3] => Pat_Geb_Datum
		    [4] => Pat_Monika_erwünscht
		    [5] => Pat_Vers_Nr
		    [6] => Pat_EinschreibeArzt
		    [7] => Pat_EinschreibeDatum
		    [8] => Pat_VersStatus
		    [9] => Pat_gezahlt
		    [10] => Pat_gezahlt_anArzt
		    [11] => PAdr_Strasse
		    [12] => PAdr_HausNr
		    [13] => PAdr_PLZ
		    [14] => PAdr_Ort
		    [15] => PAdr_Telefon
		    [16] => PAdr_Ansprechpartner
		    [17] => Pat_Krankenkasse
		    [18] => Pat_AusschreibeDatum
		     */
		    
		    $map_columns = array(
		        "ID_Pat"=>"import_pat",
		        "Pat_Namen"=>"last_name",
		        "Pat_Vorname"=>"first_name",
		        "Pat_Geb_Datum"=>"birthd",
		        "Pat_Monika_erwünscht"=>"icon_monika",
		        "Pat_Vers_Nr"=>"insurance_number",
		        "Pat_EinschreibeArzt"=>"family_doctor",
		        "Pat_EinschreibeDatum"=>"admission_date",
		        "Pat_VersStatus"=>"insurance_status",
		        "Pat_gezahlt"=>"icon_patientNicht",
		        "Pat_gezahlt_anArzt"=>"icon_doctortNicht",
		        "PAdr_Strasse"=>"street1",
		        "PAdr_HausNr"=>"house_number", //  this will be deleted in the final file and added to street
		        "PAdr_PLZ"=>"zip",
		        "PAdr_Ort"=>"city",
		        "PAdr_Telefon"=>"phone",
		        "PAdr_Ansprechpartner"=>"contact_persons",// IGNORE
		        "Pat_AusschreibeDatum"=>"discharge_date",
		        "Pat_Krankenkasse"=>"insurance_company",
		        "Pat_Genus"=>"sex"
		        
		    );
		    
		    $export_value = array();
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        foreach($import_type as $indent => $value)
		        {
		            if(isset($map_columns[$csv_data[0][$indent]]))  {
                        $export_value[$k_csv_row][$map_columns[$csv_data[0][$indent]]] = $value;
		            } else {
                        $export_value[$k_csv_row][$csv_data[0][$indent]] = $value; 
		            }
		        }
		    }

		    
// 		    print_R($export_value); exit;
		    
		    $imps = 0 ;
		    $inserted = array();
		    echo date("d.m.Y H:i:s");
		    $start_time = microtime(true);
		    foreach($export_value as $row => $import_data)
		    {
		        if($row != 0){
		   
		           $inserted_ipid = $this->import_patient_nr_mambo($row, $clientid, $userid,  $export_value, $imported_patients_array);

		           if($inserted_ipid){
		               $inserted[] = $inserted_ipid; 
		           }
		            if(count($inserted) == 300 ){
                        
		                echo '<br/><br/>'.count($inserted)." inserted. Run again !<br/>";
		                
		                
		                $end_time =  microtime(true) - $start_time;
		                	
		                echo "<br/>TIME => ".round($end_time, 0).' SECONDS';
                        exit;
		            }
		        }
	            $imps++;
		    }
 
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_nr_mambo($csv_row, $clientid, $userid,  $csv_data, $imported_patients_array)
		{
// 		    dd($imported_patients_array);
		    $Tr = new Zend_View_Helper_Translate();
		    $imported_csv_data = $csv_data[$csv_row];

		    
		    // check if patient is importad  
		    if(!empty($imported_csv_data['import_pat'])){
		        $import_pat_id = $imported_csv_data['import_pat'];
		    }
		    
            // if patient is not importand then IMPORT
            if( ! array_key_exists($import_pat_id, $imported_patients_array))
            {
    		    $gender = array( '1' => '2', '2' =>'1','3' => '0');
    		
    		    $curent_dt = date('Y-m-d H:i:s', time());
    		
    		    //generate ipid and epid
    		    $ipid = Pms_Uuid::GenerateIpid();
    		
    		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
    		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
    		
    		    
    		    // Please check csv and see if date is correct
    		    if(strlen($imported_csv_data['birthd']) > 0 ){
                    // if date format ok
    		        $bd_date = date('Y-m-d', strtotime($imported_csv_data['birthd']));
    		    }
    		    
    		    //START:: Patient Insert
    		    $cust = new PatientMaster();
    		    $cust->import_pat = $import_pat_id; //[0] => ﻿ID_Pat
    		    $cust->ipid = $ipid;
    		    $cust->recording_date = $curent_dt;
    		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['last_name']);//[1] => Pat_Namen
    		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['first_name']);//[2] => Pat_Vorname
       		    $cust->birthd = $bd_date;//[3] => Pat_Geb_Datum
    		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['street1'].' '.$imported_csv_data['house_number']);//[11] => PAdr_Strasse + [12] => PAdr_HausNr( 12 will be removed) 
    		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['zip']);
    		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['city']);
                //[7] => Pat_EinschreibeDatum
    		    if($imported_csv_data['admission_date']){
    		        $post_admission_date = date('Y-m-d', strtotime($imported_csv_data['admission_date'])); 
    		        $cust->admission_date =date('Y-m-d 00:00:00', strtotime($imported_csv_data['admission_date'])); 
    		        $admission_date = date('Y-m-d 00:00:00', strtotime($imported_csv_data['admission_date'])); 
    		
    		    } else {
    		
    		        $post_admission_date = $curent_dt;
    		        $cust->admission_date = $curent_dt;
    		        $admission_date = $curent_dt;
    		
    		        $cust->isstandby = "1";
    		    }
                //[19] => Pat_genus
    		    if($gender[$imported_csv_data['sex']]){
    		        $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['sex']]);
    		    } else {
    		        $cust->sex = Pms_CommonData::aesEncrypt("0");
    		    }
    		    $cust->isadminvisible= '1';
    		
    		    //[18] => Pat_AusschreibeDatum
    		    if(strlen($imported_csv_data['discharge_date']) > 0){
        		    $cust->isdischarged = '1';
        		    $cust->traffic_status= '0';
    		    }
    		    $cust->save();
    		    // END :: Patient Insert 
    		    
    		    
    		
    		    //insert epid-ipid
    		    $res = new EpidIpidMapping();
    		    $res->clientid = $clientid;
    		    $res->ipid = $ipid;
    		    $res->epid = $epid;
    		    $res->epid_chars = $epid_parts['epid_chars'];
    		    $res->epid_num = $epid_parts['epid_num'];
    		    $res->save();
    		
    		    
    		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
    		    $cust = new PatientCourse();
    		    $cust->ipid = $ipid;
    		    $cust->course_date = date("Y-m-d H:i:s", time());
    		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
    		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
    		    $cust->user_id = $userid;
    		    $cust->save();
    		
    		    $patientreadmission = new PatientReadmission();
    		    $patientreadmission->user_id = $userid;
    		    $patientreadmission->ipid = $ipid;
    		    $patientreadmission->date = $admission_date;
    		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
    		    $patientreadmission->save();
    		
    
    
    		    if( ! empty($imported_csv_data['insurance_company'])){
    		    
    		        $company_name = "";
    		        $company_name = $imported_csv_data['insurance_company'];
    		    
    		        // HEALTH INSURANCE
    		        if($company_name == "pronova BKK"){
    		    
    		            $search_string = addslashes(urldecode(trim($company_name)));
    		    
    		            $drop = Doctrine_Query::create()
    		            ->select('*')
    		            ->from('HealthInsurance INDEXBY id')
    		            ->where("trim(lower(name)) like ?","%".trim(mb_strtolower($search_string, 'UTF-8'))."%")
    		            ->andWhere(' isdelete= 0 ')
    		            ->andWhere(' extra= 0 ')
    		            ->andWhere(' onlyclients="1" ')
    		            ->andWhere(' clientid= ?', $clientid)
    		            ->orderBy('name ASC');
    		            $droparray = $drop->fetchArray();
    		    
    		            $health_id = "131141"; // This healthInsurance exists
    		            if(!empty($droparray) && array_key_exists($health_id, $droparray)) {
    		                $hi_id = $health_id; // This healthInsurance exists
    		            }
    		        }
    		        else
    		        {
    		            //insert health insurance company data
    		            $hi = new HealthInsurance();
    		            $hi->clientid = $clientid;
    		            $hi->name = $company_name;
    		            $hi->extra = '1';
    		            $hi->save();
    		    
    		            $hi_id = $hi->id;
    		        }
    		    
    		        $status_int_array = array("1"=>"M", "3" => "F", "5" => "R");
    		        //insert patient health insurance data
    		        $patient_hi = new PatientHealthInsurance();
    		        $patient_hi->ipid = $ipid;
    		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($company_name);
    		        $patient_hi->companyid = $hi_id;
    		        $patient_hi->insurance_no = $imported_csv_data['insurance_number'];
    		        if(strlen($imported_csv_data['insurance_status']) > 0 ){
    		            $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($status_int_array[$imported_csv_data['insurance_status']]);
    		        }
    		        $patient_hi->save();
    		    }
    		    
    
    		    // icons
    		    // Pat_Monika_erwünscht - add the "MONIKA" "custom" icon in the patient      |||   1350
    		    if(strlen($imported_csv_data['icon_monika']) > 0){
    		        if( $imported_csv_data['icon_monika'] == "WAHR" || $imported_csv_data['icon_monika'] == "1"){
    
    		            $cust_icons = new IconsPatient();
    		            $cust_icons->ipid = $ipid;
    		            $cust_icons->icon_id = '1350';
    		            $cust_icons->save();
    		        }
    		    }
    		    
    		    // Pat_gezahlt - if "FALSCH" add custom icon "Patient nicht abgerechnet"     |||   1353
    		    if(strlen($imported_csv_data['icon_patientNicht']) > 0 || strlen($imported_csv_data['Pat_gezahlt']) > 0){
//     		        if($imported_csv_data['icon_patientNicht'] == "FALSCH"  || $imported_csv_data['icon_patientNicht'] == "0"){
    		        if($imported_csv_data['icon_patientNicht'] == "0"){
    
    		            $cust_icons = new IconsPatient();
    		            $cust_icons->ipid = $ipid;
    		            $cust_icons->icon_id = '1353';
    		            $cust_icons->save();
    		        }
    		    }
    		    
    		    // Pat_gezahlt_anArzt - if "FALSCH" add custom icon "Arzt nicht abgerechnet" |||   1352
    		    if(strlen($imported_csv_data['icon_doctortNicht']) > 0 || strlen($imported_csv_data['Pat_gezahlt_anArzt']) > 0){
    		        if( $imported_csv_data['icon_doctortNicht'] == "0"){
    
    		            $cust_icons = new IconsPatient();
    		            $cust_icons->ipid = $ipid;
    		            $cust_icons->icon_id = '1352';
    		            $cust_icons->save();
    		        }
    		    }
    		    
    		    
    		    // familydoctor
    		    if( ! empty($imported_csv_data['family_doctor'])  || ! empty($imported_csv_data['Pat_EinschreibeArzt']) ){
    		    
    		        $fdoc_name = array();
    		        $fdoc_name = explode(',',$imported_csv_data['family_doctor']);
    		    
    		        $regexp_ln = trim($fdoc_name['0']);
    		        Pms_CommonData::value_patternation($regexp_ln);
    		    
    		        $regexp_fn = trim($fdoc_name['1']);
    		        Pms_CommonData::value_patternation($regexp_fn);
    		    
    		        $client_f_doc = Doctrine_Query::create()
    		        ->select('id,title,salutation,first_name,last_name,street1,zip,city,phone_practice,phone_private,fax,email,doctornumber,doctor_bsnr,comments,practice,debitor_number')
    		        ->from('FamilyDoctor')
    		        ->where("last_name  REGEXP ?", $regexp_ln)
    		        ->andWhere("first_name  REGEXP ?", $regexp_fn)
    		        ->andWhere('clientid = ?', $clientid)
    		        ->andWhere("valid_till='0000-00-00'")
    		        ->andWhere("indrop = 0")
    		        ->andWhere('isdelete=0')
    		        ->orderBy('last_name ASC')
    		        ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
    		    
    		    
		            $master_fam = new FamilyDoctor();
		            $master_fam->clientid = $clientid;
		            $master_fam->indrop = '1';
		            
		            if(!empty($client_f_doc)) {
    		            foreach($client_f_doc as $column=>$value) {
    		                if($column != "id") {
    		                    $master_fam->{$column} = $value;
    		                }
    		            }
		            } else {
	                    $master_fam->last_name = $fdoc_name['0'];
	                    $master_fam->first_name = $fdoc_name['1'];
		            }
		            $master_fam->save();
		            $master_fam_id = $master_fam->id;
		    
		            
		            // update patient master
		            if($ipid && $master_fam_id){
		                $pr = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
		                if($pr){
		                    $pr->familydoc_id= $master_fam_id;
		                    $pr->save();
		                }
		                
		                $fd_course = "Hausarzt  ".$imported_csv_data['family_doctor']."  eingetragen.";
		                $cust = new PatientCourse();
		                $cust->ipid = $ipid;
		                $cust->course_date = date("Y-m-d H:i:s", time());
		                $cust->course_type = Pms_CommonData::aesEncrypt("K");
		                $cust->course_title = Pms_CommonData::aesEncrypt($fd_course);
		                $cust->user_id = $userid;
		                $cust->save();
		            }
    		    }
    		    
    		      
    		    if(strlen($imported_csv_data['discharge_date']) > 0){
        		    $discharge_date = date('Y-m-d 00:00:10', strtotime($imported_csv_data['discharge_date'])); //
        		
        		    $pd = new PatientDischarge();
        		    $pd->ipid = $ipid;
        		    $pd->discharge_date = $discharge_date;
        		    $pd->discharge_method = "2705"; //new type "Ausschreibung"
        		    $pd->save();
        		    
        		    
        		
        		    $patientreadmission = new PatientReadmission();
        		    $patientreadmission->user_id = $userid;
        		    $patientreadmission->ipid = $ipid;
        		    $patientreadmission->date = $discharge_date;
        		    $patientreadmission->date_type = "2"; //1 =admission-readmission 2- discharge
        		    $patientreadmission->save();
        		
        		
        		    // Patient discharge course - discharge date START
        		    $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
        		    $cust = new PatientCourse();
        		    $cust->ipid = $ipid;
        		    $cust->course_date = date("Y-m-d H:i:s", time());
        		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
        		    $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
        		    $cust->user_id = $userid;
        		    $cust->save();
    		    }
    		
    		
    		    //insert in patient case
    		    $case = new PatientCase();
    		    $case->admission_date = $admission_date;
    		    $case->epid = $epid;
    		    $case->clientid = $clientid;
    		    $case->save();
    		
    		
    		
    		    //write first entry in patient course
    		    $course = new PatientCourse();
    		    $course->ipid = $ipid;
    		    $course->course_date = $curent_dt;
    		    $course->course_type = Pms_CommonData::aesEncrypt('K');
    		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.date('d.m.Y'));
    		    $course->done_date = $curent_dt;
    		    $course->user_id = $userid;
    		    $course->tabname = Pms_CommonData::aesEncrypt('new_nr_client_import-todo-1970');
    		    $course->save();
    		
    		    
    		    return $ipid;
    		    
            } else {
    		    return false;
            }
		    
		}
		
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		/*--------------------------------------------------*/
		
		

		
    /**
     * TODO-2276
     * @author Ancuta - copy of old f
     * 25.04.2019
     * @param unknown $csv_data
     * @param unknown $post
     */

		public function patient_import_handler_caritas_2019_TODO2276($csv_data, $post)
		{
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
 		//print_r($csv_data);exit;
		    $dg = new DiagnosisType();
		
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    if($ddarr1){
		
		        $type_id_array = array();
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr1 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
		        $main_diagnosis_type = $type_id_array[0];
		    }
		    else
		    {
		        $res = new DiagnosisType();
		        $res->clientid = $clientid;
		        $res->abbrevation = 'HD';
		        $res->save();
		        $main_diagnosis_type = $res->id;
		    }
		
		
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    if($ddarr2){
		        $type_id_nd_array = array();
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr2 as $key => $valdia)
		        {
		            $type_id_nd_array[] = $valdia['id'];
		        }
		        $side_diagnosis_type = $type_id_nd_array[0];
		    }
		    else
		    {
		        $side_res = new DiagnosisType();
		        $side_res->clientid = $clientid;
		        $side_res->abbrevation = 'ND';
		        $side_res->save();
		        $side_diagnosis_type = $side_res->id;
		    }
		
		    // FOR RP ONLY
		    $map_location2live_id = array();
		    
    		if($clientid == "240"){
        		$location_name2id = array(
        		    "Zuhause"=>"12148",
        		    "Sonstige"=>"14901",
        		    "Krankenhaus"=>"12150"
        		);
    		}    
    		elseif($clientid == "239")
    		{
        		$location_name2id = array(
        		    "Zuhause"=>"12159",
        		    "in der eigenen Häuslichkeit"=>"14902"
        		);
    		    
    		}
 
    		
    		
    		
    		$ref_name2id = array();
    		
    		$drop = Doctrine_Query::create()
    		->select('*')
    		->from('PatientReferredBy')
    		->where("clientid =" . $clientid)
    		->andWhere('isdelete=0')
    		->orderBy('referred_name ASC');
    		
    		$ref_Arr = $drop->fetchArray();
    		
    		foreach($ref_Arr as $k=>$rf){
    		    $ref_name2id[$rf['referred_name']] = $rf['id'];
    		    
    		}
    		
    		foreach($csv_data as $k_csv_r=>$data){
    		    $ref_name = trim($data['12']);
    		
    		    if(!empty($ref_name) && !array_key_exists($ref_name, $ref_name2id)){
    		
    		        $nref = new PatientReferredBy();
    		        $nref->clientid = $clientid;
    		        $nref->referred_name = $ref_name;
    		        $nref->save();
    		        $nref_id = $nref->id;
    		        if($nref_id){
    		            
        		        $ref_name2id[$ref_name]  = $nref_id;
    		        }
    		    }
    		}
    		
    		
    		
    		
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            $this->import_patient_caritas_rp_sl($k_csv_row, $clientid, $userid,  $csv_data, $side_diagnosis_type,$main_diagnosis_type,$location_name2id,$ref_name2id);
		        }
		    }
		
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_caritas_rp_sl($csv_row, $clientid, $userid,  $csv_data, $side_diagnosis_type,$main_diagnosis_type ,$location_name2id,$ref_name2id)
		{
		    $Tr = new Zend_View_Helper_Translate();
		
		    $imported_csv_data = $csv_data[$csv_row];
		
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $curent_dt_dmY = date('d.m.Y', time());
		
		  
		 
		   		
		   		
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //################################################################
		    //insert patient
		
		    $gender = array("männlich"=>"1","weiblich"=>"2");
		
		    if(strlen($imported_csv_data['6']) > 0 ){
		        $bd_date = date('Y-m-d', strtotime($imported_csv_data['6']));
		    }
		
		
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		
		    $cust->last_name = Pms_CommonData::aesEncrypt($imported_csv_data['1']);
		    $cust->first_name = Pms_CommonData::aesEncrypt($imported_csv_data['2']);
		    $cust->street1 = Pms_CommonData::aesEncrypt($imported_csv_data['3']);
		    $cust->zip = Pms_CommonData::aesEncrypt($imported_csv_data['4']);
		    $cust->city = Pms_CommonData::aesEncrypt($imported_csv_data['5']);
		    $cust->phone = Pms_CommonData::aesEncrypt($imported_csv_data['8']);
		    
		    //$cust->mobile = Pms_CommonData::aesEncrypt($imported_csv_data['12']);
		
		    //$cust->living_will= $imported_csv_data['34'];
		    $cust->birthd = $bd_date;
	 
		    if(!empty($imported_csv_data['13'])){
		        $post_admission_date =  date("Y-m-d H:i:s", strtotime($imported_csv_data['13']));
		        $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['13']));
		
		        $cust->admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['13']));
		        $cust->isdischarged =  0;
		        $cust->traffic_status= '1';
		
		    } else {
		        $cust->admission_date = date('2010-01-01 00:00:00'); //
		        $post_admission_date = date('2010-01-01 00:00:00'); //
		        $admission_date = date('2010-01-01 00:00:00'); //
		    }
		
		
		    if($gender[$imported_csv_data['7']]){
		        $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['7']]);
		    } else {
		        $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		
		    if(!empty($ref_name2id[$imported_csv_data['12']])){
		        
		    $cust->referred_by = $ref_name2id[$imported_csv_data['12']];
		    }
		    
		    
		    $cust->import_pat = $imported_csv_data['0'];
		    $cust->isadminvisible= '1';
		    $cust->save(); // ******************* SAVE *******************
		
		
		    $pat_id = $cust->id; 
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
		    // locations
		
		    if(strlen($imported_csv_data['14']) > 0 && !empty($location_name2id[trim($imported_csv_data['14'])])){
		        $cust = new PatientLocation();
		        $cust->ipid = $ipid;
		        $cust->clientid = $clientid;
		        $cust->valid_from = $admission_date;
		        $cust->valid_till = "0000-00-00 00:00:00";
		        $cust->location_id = $location_name2id[trim($imported_csv_data['14'])];
		        $cust->save();
		    }
		
		
		
		    //################################################################
		    // diagnosis MAIN
		    if(strlen($imported_csv_data['15']) > 0 && $imported_csv_data['15'] != "-"){
		
		        $diagno_array = explode(",",$imported_csv_data['15']);
		        foreach($diagno_array as $k=>$diagno_name){
		            
    		        $free_diagno_id ="";
    		        $diagno_free = new DiagnosisText();
    		        $diagno_free->clientid = $clientid;
    		        $diagno_free->free_name = $diagno_name;
    		        $diagno_free->save();
    		        $free_diagno_id = $diagno_free->id;
    		
    		        $diagno = new PatientDiagnosis();
    		        $diagno->ipid = $ipid;
    		        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
    		        $diagno->diagnosis_type_id = $main_diagnosis_type ;
    		        $diagno->diagnosis_id = $free_diagno_id;
    		        $diagno->icd_id = "0";
    		        $diagno->save();
		        }
		    }
		    // diagnosis SIDE
		    if(strlen($imported_csv_data['17']) > 0 && $imported_csv_data['17'] != "-"){
		
		        $diagno_array = explode(",",$imported_csv_data['17']);
		        foreach($diagno_array as $k=>$diagno_name){
		            
    		        $free_diagno_id ="";
    		        $diagno_free = new DiagnosisText();
    		        $diagno_free->clientid = $clientid;
    		        $diagno_free->free_name =$diagno_name;
    		        $diagno_free->save();
    		        $free_diagno_id = $diagno_free->id;
    		
    		        $diagno = new PatientDiagnosis();
    		        $diagno->ipid = $ipid;
    		        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
    		        $diagno->diagnosis_type_id = $side_diagnosis_type ;
    		        $diagno->diagnosis_id = $free_diagno_id;
    		        $diagno->icd_id = "0";
    		        $diagno->save();
		        }
		    }
		   
		
		    // health insurance
		    if(strlen($imported_csv_data['11']) > 0){ // KK_Krankenversicherung_name
		        // insert in health insurance master
		        $hinsu = new HealthInsurance();
		        $hinsu->clientid = $clientid;
// 		        $hinsu->iknumber = $imported_csv_data['18'];//KK_KrankenIK
		        $hinsu->name = $imported_csv_data['11'];//KK_Krankenversicherung_name
// 		        $hinsu->name2 = $imported_csv_data['22'];//KK_Krankenversicherung_name2
// 		        $hinsu->insurance_provider = $imported_csv_data['23'];//KK_Ansprechpartner
// 		        $hinsu->street1= $imported_csv_data['24'];//KK_Straße
// 		        $hinsu->zip = $imported_csv_data['25'];//KK_PLZ
// 		        $hinsu->city = $imported_csv_data['26'];//KK_Stadt
// 		        $hinsu->phone = $imported_csv_data['27'];//KK_Telefon 1
// 		        $hinsu->phone2 = $imported_csv_data['28'];//KK_Telefon 2
// 		        $hinsu->phonefax  = $imported_csv_data['29'];//KK_Fax
// 		        $hinsu->email = $imported_csv_data['30'];//KK_Email
		        $hinsu->valid_from = date("Y-m-d", time());
		        $hinsu->extra = 1;
		        $hinsu->onlyclients = '0';
		        $hinsu->save();
		        $company_id =$hinsu->id;
		
		        // insert in patient health insurance
		        $patient_hi = new PatientHealthInsurance();
		        $patient_hi->ipid = $ipid;
		        $patient_hi->companyid = $company_id;
// 		        $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($imported_csv_data['19']);//KK_KV Status
		        $patient_hi->insurance_no = $imported_csv_data['16'];// KK_Versichertennr
// 		        $patient_hi->institutskennzeichen = $imported_csv_data['18'];// KK_KrankenIK
		        $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['11']);//KK_Krankenversicherung_name
// 		        $patient_hi->ins_contactperson = Pms_CommonData::aesEncrypt($imported_csv_data['23']); //KK_Ansprechpartner
// 		        $patient_hi->ins_street = Pms_CommonData::aesEncrypt($imported_csv_data['24']); //KK_Straße
// 		        $patient_hi->ins_zip = Pms_CommonData::aesEncrypt($imported_csv_data['25']);//KK_PLZ
// 		        $patient_hi->ins_city = Pms_CommonData::aesEncrypt($imported_csv_data['26']);//KK_Stadt
// 		        $patient_hi->ins_phone = Pms_CommonData::aesEncrypt($imported_csv_data['27']);//KK_Telefon 1
// 		        $patient_hi->ins_phone2 = Pms_CommonData::aesEncrypt($imported_csv_data['28']);//KK_Telefon 2
// 		        $patient_hi->ins_phonefax = Pms_CommonData::aesEncrypt($imported_csv_data['29']); // KK_Fax
// 		        $patient_hi->ins_email = Pms_CommonData::aesEncrypt($imported_csv_data['30']);//KK_Email
		        $patient_hi->save();
		    }
		
		
		
		
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		
		    // religion
		    $religion_imp = "";
		    $religion_imp = $imported_csv_data['10'];
		    if(strlen($religion_imp) > 0){
		        
		        if($religion_imp == ""){
		
		        }
		        elseif($religion_imp == "Nicht bekannt"){
		            $religion = "999"; // dummy because - no corespondent
		        }
		        elseif($religion_imp == "Evangelisch"){
		            $religion = "1";
		        }
		        elseif($religion_imp == "Muslimisch"){
		
		            $religion = "5";
		        }
		        elseif($religion_imp == "Katholisch"){
		
		            $religion = "2";
		        }
		        else
		        {
		            $religion = "";
		        }
		        if(strlen($religion)>0){
		
		            $frm = new PatientReligions();
		            $frm->ipid = $ipid;
		            $frm->religion = $religion;
		            $frm->save();
		        }
		    }
		
 
 
		     
		
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
		
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_caritas_2019_clients_import');
		    $course->save();
		
		}		
		
		
		
		
		
		
    /**
     * TODO-2271
     * @author Ancuta - copy of old f
     * 03.05.2019
     * @param unknown $csv_data
     * @param unknown $post
     */

		public function patient_import_handler_rp_2019_TODO2271($csv_data, $post)
		{
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
// 		    echo "<pre>";
 		
//     	    print_r($csv_data); exit;
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            $this->import_patient_rp_2271($k_csv_row, $clientid, $userid,  $csv_data);
		        }
		    }
		
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_rp_2271($csv_row, $clientid, $userid,  $csv_data)
		{
		    $Tr = new Zend_View_Helper_Translate();
		
		    $imported_csv_data = $csv_data[$csv_row];
		
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $curent_dt_dmY = date('d.m.Y', time());
		
		   		// cient id   =  301
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //################################################################
		    //insert patient
		
		    $gender = array("männlich"=>"1","weiblich"=>"2");
		
		    if(strlen($imported_csv_data['2']) > 0 ){
		        $bd_date = date('Y-m-d', strtotime($imported_csv_data['2']));
		    }
		
		
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		
		    $cust->last_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['0']));
		    $cust->first_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['1']));
		    $cust->street1 = Pms_CommonData::aesEncrypt(trim($imported_csv_data['5']));
		    $cust->zip = Pms_CommonData::aesEncrypt(trim($imported_csv_data['3']));
		    $cust->city = Pms_CommonData::aesEncrypt(trim($imported_csv_data['4']));
		    $cust->birthd = $bd_date;
		    if(!empty($imported_csv_data['6'])){
		        $post_admission_date =  date("Y-m-d H:i:s", strtotime($imported_csv_data['6']));
		        $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['6']));
		
		        $cust->admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['6']));
		        $cust->isdischarged =  0;
		        $cust->traffic_status= '1';
		
		    } else {
		        $cust->admission_date = date('2010-01-01 00:00:00'); //
		        $post_admission_date = date('2010-01-01 00:00:00'); //
		        $admission_date = date('2010-01-01 00:00:00'); //
		    }
	        $cust->sex = Pms_CommonData::aesEncrypt("0");
		    $cust->isadminvisible= '1';
		    $cust->save(); // ******************* SAVE *******************
		
		
		    $pat_id = $cust->id; 
		    
		   
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		
 
		
		
		    //################################################################
 
            // assigne user
		    $val_us = "";
		    if(!empty($imported_csv_data['7'])){
		        $val_us = trim($imported_csv_data['7']);
    		     $assign = new PatientQpaMapping();
    		     $assign->epid = $epid;
    		     $assign->userid = $val_us;
    		     $assign->clientid = $clientid;
    		     $assign->assign_date = date("Y-m-d H:i:s", time());
    		     $assign->save(); 
     
    		     $vizibility = new PatientUsers();
    		     $vizibility->clientid = $clientid;
    		     $vizibility->ipid = $epid;
    		     $vizibility->userid = $val_us;
    		     $vizibility->create_date = date("Y-m-d H:i:s", time());
    		     $vizibility->save(); 
		    }
		
            // voluntary
		    $voluntary = array();
		    if(!empty($imported_csv_data['8']) && !empty($imported_csv_data['9'])){
		        $voluntary = explode(",",$imported_csv_data['8']);
		      
                foreach($voluntary as $vw_id){
    		        $exisitng = array();
    		        $fdoc1 = Doctrine_Query::create();
    		        $fdoc1->select('*');
    		        $fdoc1->from('Voluntaryworkers');
    		        $fdoc1->where("isdelete = 0  ");
    		        $fdoc1->andWhere("clientid =? ",$clientid);
    		        $fdoc1->andWhere("indrop = 0");
    		        $fdoc1->andWhere("id = ?",$vw_id);
    		        $fdoc1->limit("1");
    		        $exisitng = $fdoc1->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
    		        
    		        if(!empty($exisitng)){
    		            
    		            // insert in  Voluntaryworkers
    		            $master_vw = new Voluntaryworkers();
    		            $master_vw->clientid = $clientid;
    		            $master_vw->parent_id = $exisitng['id'];
    		            $master_vw->status = "e";//Hospizbegleiter
    		            $master_vw->first_name = $exisitng['first_name'];
    		            $master_vw->last_name = $exisitng['last_name'];
    		            $master_vw->indrop = 1;
    		            $master_vw->save();
    		            $master_vw_id = $master_vw->id;
    		         
    		            // insert in patient
    		            $vw_new = new PatientVoluntaryworkers();
    		            $vw_new->ipid = $ipid;
    		            $vw_new->vwid = $master_vw_id;
    		            $vw_new->start_date = date("Y-m-d H:i:s",strtotime($imported_csv_data['9']));
    		            $vw_new->isdelete = "0";
    		            $vw_new->save();
    		        }
    		    }
            }
            
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
		
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_rp_2019_patients_import');
		    $course->save();
		
		}		
    /**
     * TODO-2363
     * @author Ancuta - copy of old fn
     * 26.06.2019
     * @param unknown $csv_data
     * @param unknown $post
     */
		public function patient_import_handler_sl_2019_TODO2363($csv_data, $post)
		{
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
// 		    echo "<pre>";
 		
//     	    dd($csv_data); exit;
		    $h= 1;
//     	    foreach($csv_data[1] as $column=>$val){
//     	        if(trim($val) != "Ignore"){
//     	            $allowed_columns_ids[] = $column;
// //     	            $allowed_columns[$column] = $csv_data[0][$column];
//     	            $allowed_columns[$h] = $csv_data[0][$column];
//     	            $h++;
//     	        }
//     	    }
    	    
//     	    dd($allowed_columns);
		    //[11] => Patient_Location
		    // Insert in locatin master

		    // Insert in discharge location [17] => Dyingplace
		    
		    
		    
		    /* 15544 	296 	Stationäres Hospiz
		    Edit Edit	Copy Copy	Delete Delete	15543 	296 	Alten-Pflegeheim
		    Edit Edit	Copy Copy	Delete Delete	15542 	296 	Palliativstation
		    Edit Edit	Copy Copy	Delete Delete	15541 	296 	Krankenhaus
		    Edit Edit	Copy Copy	Delete Delete	15540 	296 	in der eigenen Häuslichkeit
		    Edit Edit	Copy Copy	Delete Delete	15539 	296 	stat. Alten-/Pflege-Einrichtung
		     */
		    
		    $csv_locations = array();
		    foreach($csv_data as $k_csv_row => $row_info)
		    {
		        if($k_csv_row != 0 && !in_array(trim($row_info['26']),$csv_locations)){
		            $csv_locations[] = trim($row_info['26']);
		        }
		    }	

		    
		    //living_will
// 		    dd($csv_locations);
		    
		    $client_data = array();
		    // get client locations
		    $locations = Locations::getLocations($clientid,0);
		    foreach($locations as $k=>$ldata){
		        $client_data['locations'][$ldata['location']] = $ldata['id'];
		    }
		    
		    // get client discharge locations
		    $client_dloc_q = Doctrine_Query::create()
		    ->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
		    ->from('DischargeLocation')
		    ->where('clientid =?',$clientid)
		    ->andWhere('isdelete=0');
		    $client_dloc = $client_dloc_q->fetchArray();
		    
		    if( ! empty($client_dloc)){
		        foreach($client_dloc as $k=>$loc_data){
		            $client_data['discharge_locations'][$loc_data['location']] = $loc_data['id'];
		        }
		    }
		/*     echo "<pre/>";
		    print_r($client_data);
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            if( strlen(trim($import_type['34'])) > 0  ){
		                $dlocs[trim($import_type['34'])] = $client_data['discharge_locations'][$import_type['17']];
		            }
		        }
		    }
		    print_r($dlocs);
		    
		    $patient = Doctrine_Query::create()
		    ->select("p.*,e.*")
		    ->from('PatientMaster p')
		    ->leftJoin("p.EpidIpidMapping e")
		    ->andWhereIn('e.clientid', array($clientid))
		    ->andWhere("p.import_pat != ''")
		    ->andWhere("p.isdelete = 0 ");
		    $patient_details = $patient->fetchArray();
		    
		    
		    
		    foreach($patient_details as $k => $pat_val)
		    {
		        if($pat_val['isdischarged'] == "1"){
    		        $patients_array[$pat_val['import_pat']] = $pat_val['ipid'];
		        }
		    }

		    print_r($patients_array);  
		    
		    foreach($patients_array as $pid=>$ipid){
		        $pd_update = Doctrine::getTable('PatientDischarge')->findOneByIpid($ipid);
		        if($pd_update){
		            $pd_update->discharge_location = $dlocs[$pid];
		            $pd_update->save();
		        }
		    }
		    
		    
		    exit;
		    
		    
		    EXIT;
		    EXIT;
		    EXIT; */
		    EXIT;
		    
		    $dg = new DiagnosisType();
		    
		    
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    $type_id_array = array();
		    if($ddarr1){
		    
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr1 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
		        $client_data['main_diagno_type'] = $type_id_array[0];
		    } 
		    
		    
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    $type_id_array = array();
		    if($ddarr2){
		    
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr2 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
                $client_data['side_diagno_type'] = $type_id_array[0];
		    } 
		    
		    
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            $this->import_patient_sl_2363($k_csv_row, $clientid, $userid,  $csv_data, $client_data);
		        }
		    }
		
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';
		}
		
		private function import_patient_sl_2363($csv_row, $clientid, $userid,  $csv_data, $client_data)
		{
		    $Tr = new Zend_View_Helper_Translate();
		
// 		    if($csv_row < 10){
    		    $imported_csv_data = $csv_data[$csv_row];
// 		    } else{
// 		        return;
// 		    }
// 		dd($client_data,$csv_data);
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $curent_dt_dmY = date('d.m.Y', time());

		    $months = array(
		        'Jan'=>'01',
		        'Feb'=>'02',
		        'Mrz'=>'03',
		        'Apr'=>'04',
		        'Mai'=>'05',
		        'Jun'=>'06',
		        'Jul'=>'07',
		        'Aug'=>'08',
		        'Sep'=>'09',
		        'Okt'=>'10',
		        'Nov'=>'11',
		        'Dez'=>'12',
		    );
		    
		    // BIRTHDSAY
		    if(strlen($imported_csv_data['5']) > 0 ){
		        $bd_ar = array();
		        $bd_ar =  explode('-',$imported_csv_data['5']);
		        $imported_csv_data['5'] = '19'.$bd_ar[2].'-'.$months[$bd_ar[1]].'-'.$bd_ar[0];
		    }
		    
		    // ADM
		    if(!empty($imported_csv_data['10'])){
		        $adm_arr = explode('-',$imported_csv_data['10']);
		        $adm_date = '20'.$adm_arr[2].'-'.$months[$adm_arr[1]].'-'.$adm_arr[0];
		        $imported_csv_data['10'] = $adm_date;
		    }
		    
		    // DEATH DATE
		    if(!empty($imported_csv_data['15']) )   {
		        $dis_Dth_d_arr=array();
		        $dis_Dth_d_arr = explode('-',$imported_csv_data['15']);
		        $dis_dth_date = '20'.$dis_Dth_d_arr[2].'-'.$months[$dis_Dth_d_arr[1]].'-'.$dis_Dth_d_arr[0];
		        $imported_csv_data['15'] =$dis_dth_date;
		    }
		    
		    // DISCHARGE DATE
		    if(!empty($imported_csv_data['16']) ){
		        $dis_Dth_d_arr = array();
		        $dis_Dth_d_arr = explode('-',$imported_csv_data['16']);
		        $dis_dth_date = '20'.$dis_Dth_d_arr[2].'-'.$months[$dis_Dth_d_arr[1]].'-'.$dis_Dth_d_arr[0];
		        $imported_csv_data['16'] =$dis_dth_date;
		    
		    }
		    
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //################################################################
		    //insert patient
		
		    $gender = array("männlich"=>"1","weiblich"=>"2");
		
		

		
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		
		    $cust->last_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['0']));
		    $cust->first_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['1']));

		    if(strlen($imported_csv_data['5']) > 0 ){
    		    $cust->birthd = date('Y-m-d', strtotime($imported_csv_data['5']));
		    }
		    
	        $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['6']]);

	        $cust->street1 = Pms_CommonData::aesEncrypt(trim($imported_csv_data['2']));
		    $cust->zip = Pms_CommonData::aesEncrypt(trim($imported_csv_data['3']));
		    $cust->city = Pms_CommonData::aesEncrypt(trim($imported_csv_data['4']));
		    $cust->phone = Pms_CommonData::aesEncrypt(trim($imported_csv_data['7']));
		    
		    $discharge_date = "";
		    $isdischarged = 0 ;
		    if(!empty($imported_csv_data['10'])){
		        
		     
		        //[10] => Admission_Date
		        $post_admission_date =  date("Y-m-d H:i:s", strtotime($imported_csv_data['10']));
		        $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['10']));
		
		        $cust->admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['10']));
		        
		        // [15] => Date_of_death
		        // [16] => Discharge_Date
		        if(empty($imported_csv_data['15']) &&  empty($imported_csv_data['16'])){
		            // active
		            $isdischarged = 0 ;
	       	        $cust->isdischarged =  0;
    		        $cust->traffic_status= '1';
		        } 
		        elseif(!empty($imported_csv_data['15'])){
		            // dead
		            $discharge_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['15']));
		            $isdischarged = 1 ;
	       	        $cust->isdischarged =  1;
    		        $cust->traffic_status= '0';
		            
		        }
		        elseif(empty($imported_csv_data['15']) && !empty($imported_csv_data['16']) ){
		            // discharged
		            $discharge_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['16']));
		            $isdischarged = 1;
	       	        $cust->isdischarged =  1;
    		        $cust->traffic_status= '1';
		        }
		
		    } else {
		        $cust->admission_date = date('2010-01-01 00:00:00'); //
		        $post_admission_date = date('2010-01-01 00:00:00'); //
		        $admission_date = date('2010-01-01 00:00:00'); //
		        $isdischarged = 0 ;
		    }
		    $cust->isadminvisible= '1';
		    $cust->import_pat = $imported_csv_data['34'];
		    $cust->save(); // ******************* SAVE *******************
		
		
		    $pat_id = $cust->id; 

		    
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		    //################################################################
            // Readmission - Admission
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		    if(!empty($discharge_date) && $isdischarged == 1){
		        
    		    //################################################################
    		    // discharge info
    		    $discharge_location = 3599; // home Please use discharged at home if there is no other
    		    $discharge_method = 2824; // Behandlungsbeendigung / Entlassung Please use discharged at home if there is no other

    		    //[15] => Date_of_death
		        if(!empty($imported_csv_data['15']) ){
		            $discharge_method = 2822; // Verstorben :: discharged dead
		             
		        }
		        
		        if(!empty($imported_csv_data['17']) ){
		            $discharge_location = $client_data['discharge_location'][trim($imported_csv_data['17'])];// location from file
		        }
    		    
	            $pd = new PatientDischarge();
	            $pd->ipid = $ipid;
	            $pd->discharge_date= $discharge_date;
                $pd->discharge_location = $discharge_location;
                $pd->discharge_method = $discharge_method;
	            $pd->save();
    		    
    		    //################################################################
    		    // Readmission - Discharge
    		    $patientreadmission = new PatientReadmission();
    		    $patientreadmission->user_id = $userid;
    		    $patientreadmission->ipid = $ipid;
    		    $patientreadmission->date = $discharge_date;
    		    $patientreadmission->date_type = "2";
    		    $patientreadmission->save();
		    }
		    
		
		    //################################################################

		    // INSERT in Patient locations
		    if(strlen($imported_csv_data['11']) > 0 ){
		    
		        $location_name = trim($imported_csv_data['11']);
		        $location_id = $client_data['locations'][$location_name];
		        
		        if(strlen($location_id)>0){
		            
		            $cust = new PatientLocation();
		            $cust->ipid = $ipid;
		            $cust->clientid = $clientid;
		            $cust->valid_from = date('Y-m-d 00:00:00', strtotime($imported_csv_data['10']));
		            if(!empty($discharge_date) && $isdischarged == 1){
                        $cust->valid_till = date('Y-m-d 00:00:00', strtotime($discharge_date));
		            } else{
                        $cust->valid_till = "0000-00-00 00:00:00";
		            }
		            $cust->location_id = $location_id;
		            $cust->save();
		        }
		    }
		    
		    
		    
		    
		
		    //################################################################
            // voluntary
		    $voluntary = array();
		    if(!empty($imported_csv_data['27'])){
		        
	            // insert in  Voluntaryworkers
	            $master_vw = new Voluntaryworkers();
	            $master_vw->clientid = $clientid;
	            $master_vw->status = "e";//Hospizbegleiter
	            $master_vw->last_name = $imported_csv_data['27'];
	            $master_vw->first_name = $imported_csv_data['28'];
	            $master_vw->zip = $imported_csv_data['29'];
	            $master_vw->city = $imported_csv_data['30'];
	            $master_vw->street = $imported_csv_data['31'];
	            $master_vw->phone = $imported_csv_data['32'];
	            $master_vw->mobile = $imported_csv_data['33'];
	            $master_vw->indrop = 1;
	            $master_vw->save();
	            $master_vw_id = $master_vw->id;
	         
	            // insert in patient
	            $vw_new = new PatientVoluntaryworkers();
	            $vw_new->ipid = $ipid;
	            $vw_new->vwid = $master_vw_id;
	            $vw_new->start_date = date("Y-m-d H:i:s",strtotime($imported_csv_data['10']));
	            $vw_new->isdelete = "0";
	            $vw_new->save();
            }
            
            //################################################################
            //insert health insurance company data
            // [8] => Health_insurance
            if(!empty($imported_csv_data['8'])){
                $hi = new HealthInsurance();
                $hi->clientid = $clientid;
                $hi->name = $imported_csv_data['8'];
                $hi->extra = '1';
                $hi->save();
                $hi_id = $hi->id;
                
                //insert patient health insurance data
                $patient_hi = new PatientHealthInsurance();
                $patient_hi->ipid = $ipid;
                $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data[8]);
                $patient_hi->companyid = $hi_id;
                $patient_hi->save();
            }
            //################################################################
            //[9] => Patients_PFLEGEGRAD
            // insert pflegegrad
            
            if(strlen($imported_csv_data['9']) > 0){
                
                $patient_stage_c = new PatientMaintainanceStage();
                $patient_stage_c->ipid = $ipid;
                $patient_stage_c->fromdate = date("Y-m-d",strtotime($admission_date));
                $patient_stage_c->stage =  trim($imported_csv_data['9']);
                $patient_stage_c->save();
            }
            

            //################################################################
            //[12] => Main_Diagnosis
            // insert diagnosis
            
            if(strlen($imported_csv_data['12']) > 0){
                $dgn_arr = array();
                $dgn_arr =  explode(',',$imported_csv_data['12']);
                
                if(!empty($dgn_arr)){
                    
                    foreach($dgn_arr as $dgn_name){
                        
                        $diagno_free = new DiagnosisText();
                        $diagno_free->clientid = $clientid;
                        $diagno_free->free_name = $dgn_name;
                        $diagno_free->save();
                        $free_diagno_id = $diagno_free->id;
                    
                        $diagno = new PatientDiagnosis();
                        $diagno->ipid = $ipid;
                        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
                        $diagno->diagnosis_type_id = $client_data['main_diagno_type'] ;
                        $diagno->diagnosis_id = $free_diagno_id;
                        $diagno->icd_id = "0";
                        $diagno->save();
                    }
                }
            }
            

            //################################################################
            //[13] => secondary_diagnosis
            // insert diagnosis
            
            if(strlen($imported_csv_data['13']) > 0){
                $dgn_arr = array();
                $dgn_arr =  explode(',',$imported_csv_data['13']);
                
                if(!empty($dgn_arr)){
                    
                    foreach($dgn_arr as $dgn_name){
                        
                        $diagno_free = new DiagnosisText();
                        $diagno_free->clientid = $clientid;
                        $diagno_free->free_name = $dgn_name;
                        $diagno_free->save();
                        $free_diagno_id = $diagno_free->id;
                    
                        $diagno = new PatientDiagnosis();
                        $diagno->ipid = $ipid;
                        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
                        $diagno->diagnosis_type_id = $client_data['side_diagno_type'] ;
                        $diagno->diagnosis_id = $free_diagno_id;
                        $diagno->icd_id = "0";
                        $diagno->save();
                    }
                }
            }
            


            //################################################################
            // insert in  familydoc
            //[18] => Familydoctor_Name
            //[19] => Familydoctor_Street
            //[20] => Familydoctor_City
            //[21] => Familydoctor_phone
            
            
            if(strlen($imported_csv_data['18']) > 0 ){
                 
                $master_fam = new FamilyDoctor();
                $master_fam->clientid = $clientid;
                $master_fam->indrop = '1';
                $master_fam->last_name = $imported_csv_data['18'];
                $master_fam->street1 = $imported_csv_data['19'];
                $master_fam->city = $imported_csv_data['20'];
                $master_fam->phone_practice= $imported_csv_data['21'];
                $master_fam->save();
                $master_fam_id = $master_fam->id;
                
                
                
                // update patient master
                if($ipid){
                    $pr = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                    if($pr){
                        $pr->familydoc_id= $master_fam_id;
                        $pr->save();
                    }
                }
            }
            
            
            //################################################################
            //[22] => Facharzt_TYPE
            //[23] => Facharzt_Name
            //[24] => Facharzt_Street
            //[25] => Facharzt_phone
            
            if(strlen($imported_csv_data['23']) > 0 )
            {
                // insert in specialists
                $master_specialist = new Specialists();
                $master_specialist->clientid = $clientid;
                $master_specialist->last_name = $imported_csv_data['23'];
                $master_specialist->street1 = $values['24'];
                $master_specialist->phone_practice = $values['25'];
                $master_specialist->indrop = '1';
                $master_specialist->save();
                $master_specialist_id = $master_specialist->id;

                
                // insert in patient
                $sp_new = new PatientSpecialists();
                $sp_new->ipid = $ipid;
                $sp_new->sp_id = $master_specialist_id;
                $sp_new->isdelete = "0";
                $sp_new->save();
            }
            
            
            
            
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		

		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
 
		    
		    
		
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_rp_2019_patients_import');
		    $course->save();
		
		}		
		
		
    /**
     * TODO-2382
     * @author Ancuta - copy of old fn
     * 02.07.2019
     * @param unknown $csv_data
     * @param unknown $post
     */
		public function patient_import_handler_he_2019_TODO2382($csv_data, $post)
		{
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
// 		    echo "<pre>";
 		
//     	    dd($csv_data); exit;
//     	    foreach($csv_data[1] as $column=>$val){
//     	        if(trim($val) != "Ignore"){
//     	            $allowed_columns_ids[] = $column;
//     	            $allowed_columns[$column] = $csv_data[0][$column];
//     	            $h++;
//     	        }
//     	    }
    	    
//     	    $csv_locations = array();
//     	    foreach($csv_data as $k_csv_row => $row_info)
//     	    {
//     	        if($k_csv_row != 0 && !in_array(trim($row_info['11']),$csv_locations)){
//     	            $csv_locations[] = trim($row_info['11']);
//     	        }
//     	    }
//     	    dd($allowed_columns,$csv_locations);
 
		    
		    //living_will
		    $client_data = array();
		    // get client locations
		    $locations = Locations::getLocations($clientid,0);
		    foreach($locations as $k=>$ldata){
		        $client_data['locations'][$ldata['location']] = $ldata['id'];
		    }
		    
		    // get client discharge locations
		    $client_dloc_q = Doctrine_Query::create()
		    ->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
		    ->from('DischargeLocation')
		    ->where('clientid =?',$clientid)
		    ->andWhere('isdelete=0');
		    $client_dloc = $client_dloc_q->fetchArray();
		    
		    if( ! empty($client_dloc)){
		        foreach($client_dloc as $k=>$loc_data){
		            $client_data['discharge_locations'][$loc_data['location']] = $loc_data['id'];
		        }
		    }
		    
		    // get client discharge methods
		    $client_dm_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('DischargeMethod')
		    ->where('clientid =?',$clientid)
		    ->andWhere('isdelete=0');
		    $client_dm = $client_dm_q->fetchArray();
		    	
		    if( ! empty($client_dm)){
		        foreach($client_dm as $k=>$dm_data){
		            $client_data['discharge_methods'][$dm_data['id']] = $dm_data['description'];
		        }
		    }
		    
		    
		    $dg = new DiagnosisType();
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    $type_id_array = array();
		    if($ddarr1){
		    
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr1 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
		        $client_data['main_diagno_type'] = $type_id_array[0];
		    } 
		    
		    
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    $type_id_array = array();
		    if($ddarr2){
		    
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr2 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
                $client_data['side_diagno_type'] = $type_id_array[0];
		    } 
		    
		    
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            $this->import_patient_he_2382($k_csv_row, $clientid, $userid,  $csv_data, $client_data);
		        }
		    }
		
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';		}
		
		private function import_patient_he_2382($csv_row, $clientid, $userid,  $csv_data, $client_data)
		{
		    $Tr = new Zend_View_Helper_Translate();
		
// 		    if($csv_row < 10){
    		    $imported_csv_data = $csv_data[$csv_row];
// 		    } else{
// 		        return;
// 		    }
// 		dd($client_data,$csv_data);
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $curent_dt_dmY = date('d.m.Y', time());

		    $months = array(
		        'Jan'=>'01',
		        'Feb'=>'02',
		        'Mrz'=>'03',
		        'Apr'=>'04',
		        'Mai'=>'05',
		        'Jun'=>'06',
		        'Jul'=>'07',
		        'Aug'=>'08',
		        'Sep'=>'09',
		        'Okt'=>'10',
		        'Nov'=>'11',
		        'Dez'=>'12',
		    );
		    
		    // BIRTHDSAY
		    if(strlen($imported_csv_data['6']) > 0 ){
		        $bd_ar = array();
		        $bd_ar =  explode('-',$imported_csv_data['6']);
		        $imported_csv_data['6'] = '19'.$bd_ar[2].'-'.$months[$bd_ar[1]].'-'.$bd_ar[0];
		    }
		    
		    // ADM
		    if(!empty($imported_csv_data['12'])){
		        $adm_arr = explode('-',$imported_csv_data['12']);
		        $adm_date = '20'.$adm_arr[2].'-'.$months[$adm_arr[1]].'-'.$adm_arr[0];
		        $imported_csv_data['12'] = $adm_date;
		    }
		    
		    // DEATH DATE
		    if(!empty($imported_csv_data['15']) )   {
		        $dis_Dth_d_arr=array();
		        $dis_Dth_d_arr = explode('-',$imported_csv_data['15']);
		        $dis_dth_date = '20'.$dis_Dth_d_arr[2].'-'.$months[$dis_Dth_d_arr[1]].'-'.$dis_Dth_d_arr[0];
		        $imported_csv_data['15'] =$dis_dth_date;
		    }
		    
		    // DISCHARGE DATE
		    if(!empty($imported_csv_data['16']) ){
		        $dis_Dth_d_arr = array();
		        $dis_Dth_d_arr = explode('-',$imported_csv_data['16']);
		        $dis_dth_date = '20'.$dis_Dth_d_arr[2].'-'.$months[$dis_Dth_d_arr[1]].'-'.$dis_Dth_d_arr[0];
		        $imported_csv_data['16'] =$dis_dth_date;
		    
		    }
// 		    dd($imported_csv_data);
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //################################################################
		    //insert patient
		
		    $gender = array("männlich"=>"1","weiblich"=>"2");
		
		

		
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		
		    $cust->last_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['1']));
		    $cust->first_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['2']));

		    if(strlen($imported_csv_data['6']) > 0 ){
    		    $cust->birthd = date('Y-m-d', strtotime($imported_csv_data['6']));
		    }
		    
	        $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['7']]);

	        $cust->street1 = Pms_CommonData::aesEncrypt(trim($imported_csv_data['3']));
		    $cust->zip = Pms_CommonData::aesEncrypt(trim($imported_csv_data['4']));
		    $cust->city = Pms_CommonData::aesEncrypt(trim($imported_csv_data['5']));
		    $cust->phone = Pms_CommonData::aesEncrypt(trim($imported_csv_data['8']));
		    
		    $discharge_date = "";
		    $isdischarged = 0 ;
		    if(!empty($imported_csv_data['12'])){
		        
		     
		        //[12] => Admission_Date
		        $post_admission_date =  date("Y-m-d H:i:s", strtotime($imported_csv_data['12']));
		        $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['12']));
		
		        $cust->admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['12']));
		        
		        // [15] => Date_of_death
		        // [16] => Discharge_Date
		        if(empty($imported_csv_data['15']) &&  empty($imported_csv_data['16'])){
		            // active
		            $isdischarged = 0 ;
	       	        $cust->isdischarged =  0;
    		        $cust->traffic_status= '1';
		        } 
		        elseif(!empty($imported_csv_data['15'])){
		            // dead
		            $discharge_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['15']));
		            $isdischarged = 1 ;
	       	        $cust->isdischarged =  1;
    		        $cust->traffic_status= '0';
		            
		        }
		        elseif(empty($imported_csv_data['15']) && !empty($imported_csv_data['16']) ){
		            // discharged
		            $discharge_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['16']));
		            $isdischarged = 1;
	       	        $cust->isdischarged =  1;
    		        $cust->traffic_status= '1';
		        }
		
		    } else {
		        $cust->admission_date = date('2010-01-01 00:00:00'); //
		        $post_admission_date = date('2010-01-01 00:00:00'); //
		        $admission_date = date('2010-01-01 00:00:00'); //
		        $isdischarged = 0 ;
		    }
		    $cust->isadminvisible= '1';
		    $cust->import_pat = $imported_csv_data['0'];
		    $cust->save(); // ******************* SAVE *******************
		
		
		    $pat_id = $cust->id; 

		    
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		    //################################################################
		    
		    // [10] => First_contact
		    // Erstkontakt durch
		    $existing = array(
		        "Angehörige"=>"1",
		        "Pallitativ Care Team (SAPV / PKD )"=>"6",
		        "Stat. Alten-/Pflege-Einrichtung"=>"10",
		        "Pflegedienst / Sozialstation"=>"10",
		        "Alten-/Pflegeheim"=>"9",
		        "Pflegedienst"=>"9",
		        "Sonstige"=>"11",
		        "Hausarzt / Facharzt"=>"3",
		    );
		    
		    $first_patient_contact="";
		    if(strlen($imported_csv_data['10']) > 0){
		        	
		        if(array_key_exists(trim($imported_csv_data['10']), $existing)){
		            $first_patient_contact = $existing[trim($imported_csv_data['10'])];
		        }
		        	
		    }

		 
		    
		    
		    
            // Readmission - Admission
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    if(strlen($imported_csv_data['10']) > 0 ){
		        if(!empty($first_patient_contact)){
		            $patientreadmission->first_contact = $first_patient_contact; //Erstkontakt durch
		        }
		    }
		    $patientreadmission->save();
		    
		    if(!empty($discharge_date) && $isdischarged == 1){
		        
    		    //################################################################
    		    // discharge info
    		    $discharge_method = 2944; // Behandlungsbeendigung / Entlassung
    		    //[15] => Date_of_death
		        if(!empty($imported_csv_data['15']) ){
		            $discharge_method = 2942; // Verstorben :: discharged dead
		        }

		        $pd = new PatientDischarge();
	            $pd->ipid = $ipid;
	            $pd->discharge_date= $discharge_date;
                $pd->discharge_method = $discharge_method;
	            $pd->save();


	            // Patient discharge course - discharge date START
	            $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
	            $cust = new PatientCourse();
	            $cust->ipid = $ipid;
	            $cust->course_date = date("Y-m-d H:i:s", time());
	            $cust->course_type = Pms_CommonData::aesEncrypt("K");
	            $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
	            $cust->user_id = $userid;
	            $cust->save();
	            
	            
	            
	            $comment="Patient wurde am ".date('d.m.Y H:i', strtotime($discharge_date))."  entlassen \n Entlassungsart : ".$client_data['discharge_methods'][$discharge_method]."\n ";
	            $pc = new PatientCourse();
	            $pc->ipid = $ipid;
	            $pc->course_date = date("Y-m-d H:i:s",time());
	            $pc->course_type=Pms_CommonData::aesEncrypt("K");
	            $pc->course_title=Pms_CommonData::aesEncrypt($comment);
	            $pc->tabname=Pms_CommonData::aesEncrypt("discharge");
	            $pc->user_id = $userid;
	            $pc->save();
	            
	            
    		    //################################################################
    		    // Readmission - Discharge
    		    $patientreadmission = new PatientReadmission();
    		    $patientreadmission->user_id = $userid;
    		    $patientreadmission->ipid = $ipid;
    		    $patientreadmission->date = $discharge_date;
    		    $patientreadmission->date_type = "2";
    		    $patientreadmission->save();
		    }
		    
		
		    //################################################################

		    // INSERT in Patient locations
		    if(strlen($imported_csv_data['11']) > 0 ){
		    
		        $location_name = trim($imported_csv_data['11']);
		        $location_id = $client_data['locations'][$location_name];
		        
		        if(strlen($location_id)>0){
		            
		            $cust = new PatientLocation();
		            $cust->ipid = $ipid;
		            $cust->clientid = $clientid;
		            $cust->valid_from = date('Y-m-d 00:00:00', strtotime($imported_csv_data['12']));
		            if(!empty($discharge_date) && $isdischarged == 1){
                        $cust->valid_till = date('Y-m-d 00:00:00', strtotime($discharge_date));
		            } else{
                        $cust->valid_till = "0000-00-00 00:00:00";
		            }
		            $cust->location_id = $location_id;
		            $cust->save();
		        }
		    }
		    
		    
		    
		    
 
            
            //################################################################
            //insert health insurance company data
            // [9] => Health_insurance
            if(!empty($imported_csv_data['9'])){
                $hi = new HealthInsurance();
                $hi->clientid = $clientid;
                $hi->name = $imported_csv_data['9'];
                $hi->extra = '1';
                $hi->save();
                $hi_id = $hi->id;
                
                //insert patient health insurance data
                $patient_hi = new PatientHealthInsurance();
                $patient_hi->ipid = $ipid;
                $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
                $patient_hi->companyid = $hi_id;
                $patient_hi->save();
            }
 
            

            //################################################################
            //[13] => Main_Diagnosis
            // insert diagnosis
            
            if(strlen($imported_csv_data['13']) > 0){
                $dgn_arr = array();
                $dgn_arr =  explode(',',$imported_csv_data['13']);
                
                if(!empty($dgn_arr)){
                    
                    foreach($dgn_arr as $dgn_name){
                        
                        $diagno_free = new DiagnosisText();
                        $diagno_free->clientid = $clientid;
                        $diagno_free->free_name = $dgn_name;
                        $diagno_free->save();
                        $free_diagno_id = $diagno_free->id;
                    
                        $diagno = new PatientDiagnosis();
                        $diagno->ipid = $ipid;
                        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
                        $diagno->diagnosis_type_id = $client_data['main_diagno_type'] ;
                        $diagno->diagnosis_id = $free_diagno_id;
                        $diagno->icd_id = "0";
                        $diagno->save();
                    }
                }
            }
            

            //################################################################
            //[14] => secondary_diagnosis
            // insert diagnosis
            
            if(strlen($imported_csv_data['14']) > 0){
                $dgn_arr = array();
                $dgn_arr =  explode(',',$imported_csv_data['14']);
                
                if(!empty($dgn_arr)){
                    
                    foreach($dgn_arr as $dgn_name){
                        
                        $diagno_free = new DiagnosisText();
                        $diagno_free->clientid = $clientid;
                        $diagno_free->free_name = $dgn_name;
                        $diagno_free->save();
                        $free_diagno_id = $diagno_free->id;
                    
                        $diagno = new PatientDiagnosis();
                        $diagno->ipid = $ipid;
                        $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
                        $diagno->diagnosis_type_id = $client_data['side_diagno_type'] ;
                        $diagno->diagnosis_id = $free_diagno_id;
                        $diagno->icd_id = "0";
                        $diagno->save();
                    }
                }
            }
            
            //################################################################
            // [17] => ANSPRECHPARTNER
            if(strlen($imported_csv_data['17']) > 0 )
            {
                $master_cpm = new ContactPersonMaster();
                $master_cpm->ipid = $ipid;
                $master_cpm->cnt_last_name = Pms_CommonData::aesEncrypt($imported_csv_data['17']);
                $master_cpm->save();
            }
            

            //################################################################
            // insert in  familydoc
            //[18] => Familydoctor_Name
            if(strlen($imported_csv_data['18']) > 0 ){
                 
                $master_fam = new FamilyDoctor();
                $master_fam->clientid = $clientid;
                $master_fam->indrop = '1';
                $master_fam->last_name = $imported_csv_data['18'];
                $master_fam->save();
                $master_fam_id = $master_fam->id;
                
                // update patient master
                if($ipid){
                    $pr = Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
                    if($pr){
                        $pr->familydoc_id= $master_fam_id;
                        $pr->save();
                    }
                }
            }

            
            //################################################################
					
            
            
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = date("Y-m-d H:i:s", time());
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->save();
		

		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
 
		    
		    
		
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_he_2019_patients_import');
		    $course->save();
		
		}		
		
		
    /**
     * TODO-2509
     * @author Ancuta - copy of old fn
     * 22.08.2019
     * @param unknown $csv_data
     * @param unknown $post
     */
		public function patient_import_handler_nr_2019_TODO2509($csv_data, $post)
		{
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
// 		    dd($csv_data);
		    //living_will
		    $client_data = array();
		    // get client locations
		    $locations = Locations::getLocations($clientid,0);
		    foreach($locations as $k=>$ldata){
		        $client_data['locations'][$ldata['location']] = $ldata['id'];
		    }
		    
		    // get client discharge locations
		    $client_dloc_q = Doctrine_Query::create()
		    ->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
		    ->from('DischargeLocation')
		    ->where('clientid =?',$clientid)
		    ->andWhere('isdelete=0');
		    $client_dloc = $client_dloc_q->fetchArray();
		    
		    if( ! empty($client_dloc)){
		        foreach($client_dloc as $k=>$loc_data){
		            $client_data['discharge_locations'][$loc_data['location']] = $loc_data['id'];
		        }
		    }
		    
		    // get client discharge methods
		    $client_dm_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('DischargeMethod')
		    ->where('clientid =?',$clientid)
		    ->andWhere('isdelete=0');
		    $client_dm = $client_dm_q->fetchArray();
		    	
		    if( ! empty($client_dm)){
		        foreach($client_dm as $k=>$dm_data){
		            $client_data['discharge_methods'][$dm_data['id']] = $dm_data['description'];
		        }
		    }
		    
		    
		    $dg = new DiagnosisType();
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    $type_id_array = array();
		    if($ddarr1){
		    
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr1 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
		        $client_data['main_diagno_type'] = $type_id_array[0];
		    } 
		    
		    
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    $type_id_array = array();
		    if($ddarr2){
		    
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr2 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
                $client_data['side_diagno_type'] = $type_id_array[0];
		    } 
		    
		    
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            $inserted[] = $this->import_patient_nr_2509($k_csv_row, $clientid, $userid,  $csv_data, $client_data);
		        }
		    }
		
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';		
		}
		
		private function import_patient_nr_2509($csv_row, $clientid, $userid,  $csv_data, $client_data)
		{
		    $Tr = new Zend_View_Helper_Translate();
   		    $imported_csv_data = $csv_data[$csv_row];
    		    
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $curent_dt_dmY = date('d.m.Y', time());

		    $months = array(
		        'Jan'=>'01',
		        'Feb'=>'02',
		        'Mrz'=>'03',
		        'Apr'=>'04',
		        'Mai'=>'05',
		        'Jun'=>'06',
		        'Jul'=>'07',
		        'Aug'=>'08',
		        'Sep'=>'09',
		        'Okt'=>'10',
		        'Nov'=>'11',
		        'Dez'=>'12',
		    );
		    
		    // BIRTHDSAY
		    if(strlen($imported_csv_data['6']) > 0 ){
		        $imported_csv_data['6'] = date("Y-m-d",strtotime($imported_csv_data['6']));
		    }
		    
		    // ADM
		    if(strlen($imported_csv_data['17']) > 0 ){
		        $imported_csv_data['17'] = date("Y-m-d 00:00:00",strtotime($imported_csv_data['17']));
		    }
		    // DISCHARGE DATE
		    if(strlen($imported_csv_data['22']) > 0 ){
		        $imported_csv_data['22'] = date("Y-m-d 00:00:00",strtotime($imported_csv_data['22']));
		    }
		    // vw_start_date
		    if(strlen($imported_csv_data['14']) > 0 ){
		        $imported_csv_data['14'] = date("Y-m-d",strtotime($imported_csv_data['14']));
		    }
		    
		    // vw_end_date
		    if(strlen($imported_csv_data['23']) > 0 ){
		        $imported_csv_data['23'] = date("Y-m-d",strtotime($imported_csv_data['23']));
		    }
 
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //################################################################
		    //insert patient
		
		    $gender = array("männlich"=>"1","weiblich"=>"2");
		
		

		
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		
		    $cust->last_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['1']));
		    $cust->first_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['2']));

		    if(strlen($imported_csv_data['6']) > 0 ){
    		    $cust->birthd = date('Y-m-d', strtotime($imported_csv_data['6']));
		    }
		    
	        $cust->sex = Pms_CommonData::aesEncrypt($imported_csv_data['7']);

	        $cust->street1 = Pms_CommonData::aesEncrypt(trim($imported_csv_data['3']));
		    $cust->zip = Pms_CommonData::aesEncrypt(trim($imported_csv_data['4']));
		    $cust->city = Pms_CommonData::aesEncrypt(trim($imported_csv_data['5']));
		    $cust->phone = Pms_CommonData::aesEncrypt(trim($imported_csv_data['8']));
		    
		    $discharge_date = "";
		    $isdischarged = 0 ;
		    if(!empty($imported_csv_data['17'])){
		        
		     
		        //[17] => Admission_Date
		        $post_admission_date =  date("Y-m-d H:i:s", strtotime($imported_csv_data['17']));
		        $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['17']));
		
		        $cust->admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['17']));
		        
		        //[22] => discharged date 
		        if(empty($imported_csv_data['22']) &&  empty($imported_csv_data['22'])){
		            // active
		            $isdischarged = 0 ;
	       	        $cust->isdischarged =  0;
    		        $cust->traffic_status= '1';
		        } 
		        elseif(!empty($imported_csv_data['22'])){
		            // discharged
		            $discharge_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['22']));
		            $isdischarged = 1 ;
	       	        $cust->isdischarged =  1;
    		        $cust->traffic_status= '0';
		            
		        }
		
		    } else {
		        $cust->admission_date = date('2010-01-01 00:00:00'); //
		        $post_admission_date = date('2010-01-01 00:00:00'); //
		        $admission_date = date('2010-01-01 00:00:00'); //
		        $isdischarged = 0 ;
		    }
		    $cust->isadminvisible= '1';
		    $cust->import_pat = $imported_csv_data['0'];
		    $cust->save(); // ******************* SAVE *******************
		
		
		    $pat_id = $cust->id; 

		
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		    //################################################################
		    
		    // [12] => first_contact
		    
            // Readmission - Admission
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    if(strlen($imported_csv_data['12']) > 0 ){
	            $patientreadmission->first_contact = $imported_csv_data['12']; //Erstkontakt durch
		    }
		    $patientreadmission->save();
		    
		    if(!empty($discharge_date) && $isdischarged == 1){
		        
    		    //################################################################
    		    // discharge method
		        $discharge_method = "";
		        if(!empty($imported_csv_data['19']) ){
		            $discharge_method = $imported_csv_data['19']; 
		        }
    		    // discharge location
		        $discharge_location="";
		        if(!empty($imported_csv_data['24']) ){
		            $discharge_location = $imported_csv_data['24']; 
		        }

		        
		        $pd = new PatientDischarge();
	            $pd->ipid = $ipid;
	            $pd->discharge_date= $discharge_date;
                $pd->discharge_method = $discharge_method;
                $pd->discharge_location = $discharge_location;
	            $pd->save();


	            // Patient discharge course - discharge date START
	            $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
	            $cust = new PatientCourse();
	            $cust->ipid = $ipid;
	            $cust->course_date = date("Y-m-d H:i:s", time());
	            $cust->course_type = Pms_CommonData::aesEncrypt("K");
	            $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
	            $cust->user_id = $userid;
	            $cust->save();
	            
	            
	            
	            $comment="Patient wurde am ".date('d.m.Y H:i', strtotime($discharge_date))."  entlassen \n Entlassungsart : ".$client_data['discharge_methods'][$discharge_method]."\n ";
	            $pc = new PatientCourse();
	            $pc->ipid = $ipid;
	            $pc->course_date = date("Y-m-d H:i:s",time());
	            $pc->course_type=Pms_CommonData::aesEncrypt("K");
	            $pc->course_title=Pms_CommonData::aesEncrypt($comment);
	            $pc->tabname=Pms_CommonData::aesEncrypt("discharge");
	            $pc->user_id = $userid;
	            $pc->save();
	            
	            
    		    //################################################################
    		    // Readmission - Discharge
    		    $patientreadmission = new PatientReadmission();
    		    $patientreadmission->user_id = $userid;
    		    $patientreadmission->ipid = $ipid;
    		    $patientreadmission->date = $discharge_date;
    		    $patientreadmission->date_type = "2";
    		    $patientreadmission->save();
		    }
		    

		    //################################################################

		    // INSERT in Patient locations
		    if(strlen($imported_csv_data['15']) > 0 ){
		            
	            $cust = new PatientLocation();
	            $cust->ipid = $ipid;
	            $cust->clientid = $clientid;
	            $cust->valid_from = date('Y-m-d 00:00:00', strtotime($imported_csv_data['17']));
	            if(!empty($discharge_date) && $isdischarged == 1){
                    $cust->valid_till = date('Y-m-d 00:00:00', strtotime($discharge_date));
	            } else{
                    $cust->valid_till = "0000-00-00 00:00:00";
	            }
	            $cust->location_id = $imported_csv_data['15'];
	            $cust->save();
		    }

            //################################################################
            //insert health insurance company data
            // [9] => Health_insurance
            if(!empty($imported_csv_data['11'])){
                $hi = new HealthInsurance();
                $hi->clientid = $clientid;
                $hi->name = $imported_csv_data['11'];
                $hi->extra = '1';
                $hi->save();
                $hi_id = $hi->id;
                
                //insert patient health insurance data
                $patient_hi = new PatientHealthInsurance();
                $patient_hi->ipid = $ipid;
                $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['11']);
                $patient_hi->companyid = $hi_id;
                $patient_hi->save();
            }
 

  

            //################################################################
            //[18] => Main_Diagnosis
            // insert diagnosis
            
            if(strlen($imported_csv_data['18']) > 0){
                $diagno_free = new DiagnosisText();
                $diagno_free->clientid = $clientid;
                $diagno_free->free_name = $imported_csv_data['18'];
                $diagno_free->save();
                $free_diagno_id = $diagno_free->id;
            
                $diagno = new PatientDiagnosis();
                $diagno->ipid = $ipid;
                $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
                $diagno->diagnosis_type_id = $client_data['main_diagno_type'] ;
                $diagno->diagnosis_id = $free_diagno_id;
                $diagno->icd_id = "0";
                $diagno->save();
            }
            
            //################################################################
            // [34] => Kontakt Person
            if(strlen($imported_csv_data['34']) > 0 )
            {
                $master_cpm = new ContactPersonMaster();
                $master_cpm->ipid = $ipid;
                $master_cpm->cnt_last_name = Pms_CommonData::aesEncrypt($imported_csv_data['34']);
                $master_cpm->save();
            }
            


            $stastszugehorigkeit="";
            if(strlen($imported_csv_data['9']) > 0 )
            {
                if($imported_csv_data['9'] == "deutsch"){
                    $stastszugehorigkeit = "1";
                }
            }
            
            $familienstandarray = array(
                "ledig"=>"1",
                "verheiratet"=>"2",
                "verwitwet"=>"3",
                "geschieden"=>"4",
                "getrennt lebend"=>"5",
                "unbek"=>"6",
                "Partnerschaft"=>"7",
                'Lebenspartner /-in'=>'7',
            );
            
            $marital_text = "";
            $marital_text_value = "";
            if(strlen($imported_csv_data['10']) > 0 )
            {
            
                $marital_text = trim($imported_csv_data['10']);
                $marital_text_value = $familienstandarray[$marital_text];
            }
            
            
            if(!empty($stastszugehorigkeit) || !empty($marital_text_value))
            {
                $cust = new Stammdatenerweitert();
                $cust->ipid = $ipid;
                if(!empty($stastszugehorigkeit))
                {
                    $cust->stastszugehorigkeit = $stastszugehorigkeit;
                }
                if( !empty($marital_text_value))
                {
                    $cust->familienstand = $marital_text_value;
                }
                $cust->save();
            }
            

            
            //################################################################
            // [28] => Voluntary worker 1
            $vol_id = "";
            $vol_name = "";
            if(strlen($imported_csv_data['28']) > 0 )
            {
                $vol_id = $imported_csv_data['28'];
                $vol_name = $imported_csv_data['29'];
                
                $vwar = array();
                $v_volunteer = array();
                if(strlen($vol_name) > 0){
                    $vwar = explode(',',$vol_name);
                    $v_volunteer['last_name'] = $vwar[0]; 
                    $v_volunteer['first_name'] = $vwar[1]; 
                    $v_volunteer['phone'] = $vwar[2]; 
                }
                $existing_voluntary_arr = array();
                $existing_voluntary = Doctrine_Query::create()
                ->select('*')
                ->from('Voluntaryworkers')
                ->where("isdelete = 0  ")
                ->andWhere("clientid = ".$clientid);
                if(!empty($v_volunteer)){
                    $existing_voluntary->andWhere("id= '".$vol_id."' OR first_name like '%" . trim($v_volunteer['first_name']) . "%'  AND last_name like '%" . trim($v_volunteer['last_name']) . "%'      ");
                } else{
                    $existing_voluntary->andWhere("id= '".$vol_id."' ");
                }
                $existing_voluntary->andWhere("indrop = 0  ");
                $existing_voluntary_arr = $existing_voluntary->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
                
 
                $parent_id = 0;
                if(!empty($existing_voluntary_arr)){
                    
                    $parent_id  = $existing_voluntary_arr['id'];
                    // insert in  Voluntaryworkers
                    $master_vw = new Voluntaryworkers();
                    $master_vw->clientid = $clientid;
                    $master_vw->parent_id = $parent_id;
                    $master_vw->status = "e";//Hospizbegleiter
                    $master_vw->first_name = $existing_voluntary_arr['first_name'];
                    $master_vw->last_name = $existing_voluntary_arr['last_name'];
                    $master_vw->salutation = $existing_voluntary_arr['salutation'];
                    $master_vw->street =  $existing_voluntary_arr['street'];
                    $master_vw->zip = $existing_voluntary_arr['zip'];
                    $master_vw->city = $existing_voluntary_arr['city'];
                    $master_vw->phone = $existing_voluntary_arr['9'];
                    $master_vw->mobile = $existing_voluntary_arr['phone'];
                    $master_vw->email= $existing_voluntary_arr['mobile'];
                    $master_vw->comments = $existing_voluntary_arr['comments'];
                    $master_vw->indrop = 1;
                    $master_vw->save();
                    $master_vw_id = $master_vw->id;
                    
                } else {

                    $volunteer = new Voluntaryworkers();
                    $volunteer->clientid = $clientid;
                    $volunteer->last_name = $v_volunteer['last_name'];
                    $volunteer->first_name = $v_volunteer['first_name'];
                    $volunteer->phone = $v_volunteer['phone'];
                    $volunteer->indrop = '1';
                    $volunteer->save();
                    $master_vw_id =  $volunteer->id;
                }
                
                // insert in patient
                $vw_new = new PatientVoluntaryworkers();
                $vw_new->ipid = $ipid;
                $vw_new->vwid = $master_vw_id;
                $vw_new->start_date = $imported_csv_data['14'];
                if(!empty($imported_csv_data['23'])){
                    $vw_new->end_date = $imported_csv_data['23'];
                }
                $vw_new->isdelete = "0";
                $vw_new->save();
                
            }
            
            //################################################################
            // [30] => Voluntary worker 2
            $vol_id = "";
            $vol_name = "";
            if(strlen($imported_csv_data['30']) > 0 )
            {
                $vol_id = $imported_csv_data['30'];
                $vol_name = $imported_csv_data['31'];
                
                $vwar = array();
                $v_volunteer = array();
                if(strlen($vol_name) > 0){
                    $vwar = explode(',',$vol_name);
                    $v_volunteer['last_name'] = $vwar[0]; 
                    $v_volunteer['first_name'] = $vwar[1]; 
                    $v_volunteer['phone'] = $vwar[2]; 
                }
                
                $existing_voluntary_arr=array();
                $existing_voluntary = Doctrine_Query::create()
                ->select('*')
                ->from('Voluntaryworkers')
                ->where("isdelete = 0  ")
                ->andWhere("clientid = ".$clientid);
                if(!empty($v_volunteer)){
                    $existing_voluntary->andWhere("id= '".$vol_id."' OR first_name like '%" . trim($v_volunteer['first_name']) . "%'  AND last_name like '%" . trim($v_volunteer['last_name']) . "%'      ");
                } else{
                    $existing_voluntary->andWhere("id= '".$vol_id."' ");
                }
                $existing_voluntary->andWhere("indrop = 0  ");
                $existing_voluntary_arr = $existing_voluntary->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
                
 
                $parent_id = 0;
                if(!empty($existing_voluntary_arr)){
                    
                    $parent_id  = $existing_voluntary_arr['id'];
                    // insert in  Voluntaryworkers
                    $master_vw = new Voluntaryworkers();
                    $master_vw->clientid = $clientid;
                    $master_vw->parent_id = $parent_id;
                    $master_vw->status = "e";//Hospizbegleiter
                    $master_vw->first_name = $existing_voluntary_arr['first_name'];
                    $master_vw->last_name = $existing_voluntary_arr['last_name'];
                    $master_vw->salutation = $existing_voluntary_arr['salutation'];
                    $master_vw->street =  $existing_voluntary_arr['street'];
                    $master_vw->zip = $existing_voluntary_arr['zip'];
                    $master_vw->city = $existing_voluntary_arr['city'];
                    $master_vw->phone = $existing_voluntary_arr['9'];
                    $master_vw->mobile = $existing_voluntary_arr['phone'];
                    $master_vw->email= $existing_voluntary_arr['mobile'];
                    $master_vw->comments = $existing_voluntary_arr['comments'];
                    $master_vw->indrop = 1;
                    $master_vw->save();
                    $master_vw_id = $master_vw->id;
                    
                    
                    
                    
                    
                } else {

                    $volunteer = new Voluntaryworkers();
                    $volunteer->clientid = $clientid;
                    $volunteer->last_name = $v_volunteer['last_name'];
                    $volunteer->first_name = $v_volunteer['first_name'];
                    $volunteer->phone = $v_volunteer['phone'];
                    $volunteer->indrop = '1';
                    $volunteer->save();
                    $master_vw_id =  $volunteer->id;
                }
                
                // insert in patient
                $vw_new = new PatientVoluntaryworkers();
                $vw_new->ipid = $ipid;
                $vw_new->vwid = $master_vw_id;
                $vw_new->start_date = $imported_csv_data['14'];
                if(!empty($imported_csv_data['23'])){
                    $vw_new->end_date = $imported_csv_data['23'];
                }
                $vw_new->isdelete = "0";
                $vw_new->save();
                
            }
            //################################################################
            // [32] => Voluntary worker 2
            $vol_id = "";
            $vol_name = "";
            if(strlen($imported_csv_data['32']) > 0 )
            {
                $vol_id = $imported_csv_data['32'];
                $vol_name = $imported_csv_data['33'];
                
                $vwar = array();
                $v_volunteer = array();
                if(strlen($vol_name) > 0){
                    $vwar = explode(',',$vol_name);
                    $v_volunteer['last_name'] = $vwar[0]; 
                    $v_volunteer['first_name'] = $vwar[1]; 
                    $v_volunteer['phone'] = $vwar[2]; 
                }
                $existing_voluntary_arr=array();
                $existing_voluntary = Doctrine_Query::create()
                ->select('*')
                ->from('Voluntaryworkers')
                ->where("isdelete = 0  ")
                ->andWhere("clientid = ".$clientid);
                if(!empty($v_volunteer)){
                    $existing_voluntary->andWhere("id= '".$vol_id."' OR first_name like '%" . trim($v_volunteer['first_name']) . "%'  AND last_name like '%" . trim($v_volunteer['last_name']) . "%'      ");
                } else{
                    $existing_voluntary->andWhere("id= '".$vol_id."' ");
                }
                $existing_voluntary->andWhere("indrop = 0  ");
                $existing_voluntary_arr = $existing_voluntary->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
                
 
                $parent_id = 0;
                if(!empty($existing_voluntary_arr)){
                    
                    $parent_id  = $existing_voluntary_arr['id'];
                    // insert in  Voluntaryworkers
                    $master_vw = new Voluntaryworkers();
                    $master_vw->clientid = $clientid;
                    $master_vw->parent_id = $parent_id;
                    $master_vw->status = "e";//Hospizbegleiter
                    $master_vw->first_name = $existing_voluntary_arr['first_name'];
                    $master_vw->last_name = $existing_voluntary_arr['last_name'];
                    $master_vw->salutation = $existing_voluntary_arr['salutation'];
                    $master_vw->street =  $existing_voluntary_arr['street'];
                    $master_vw->zip = $existing_voluntary_arr['zip'];
                    $master_vw->city = $existing_voluntary_arr['city'];
                    $master_vw->phone = $existing_voluntary_arr['9'];
                    $master_vw->mobile = $existing_voluntary_arr['phone'];
                    $master_vw->email= $existing_voluntary_arr['mobile'];
                    $master_vw->comments = $existing_voluntary_arr['comments'];
                    $master_vw->indrop = 1;
                    $master_vw->save();
                    $master_vw_id = $master_vw->id;
                    
                } else {

                    $volunteer = new Voluntaryworkers();
                    $volunteer->clientid = $clientid;
                    $volunteer->last_name = $v_volunteer['last_name'];
                    $volunteer->first_name = $v_volunteer['first_name'];
                    $volunteer->phone = $v_volunteer['phone'];
                    $volunteer->indrop = '1';
                    $volunteer->save();
                    $master_vw_id =  $volunteer->id;
                }
                
                // insert in patient
                $vw_new = new PatientVoluntaryworkers();
                $vw_new->ipid = $ipid;
                $vw_new->vwid = $master_vw_id;
                $vw_new->start_date = $imported_csv_data['14'];
                if(!empty($imported_csv_data['23'])){
                    $vw_new->end_date = $imported_csv_data['23'];
                }
                $vw_new->isdelete = "0";
                $vw_new->save();
                
            }
            
            
            
            
            
            //################################################################
					
            
            
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = $curent_dt;
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->done_date = $curent_dt;
		    $cust->save();
		
		    
		    
		    //[26] => user_id
		    if(strlen($imported_csv_data['26']) > 0 ){
    		    //assign patient to the current user
    		    $assign = new PatientQpaMapping();
    		    $assign->epid = $epid;
    		    $assign->userid = $imported_csv_data['26'];
    		    $assign->clientid = $clientid;
    		    $assign->assign_date = $curent_dt;
    		    $assign->save();
    		    
    		    //patient visibility for curent user
    		    $visibility = new PatientUsers();
    		    $visibility->clientid = $clientid;
    		    $visibility->ipid = $ipid;
    		    $visibility->userid = $imported_csv_data['26'];
    		    $visibility->create_date = $curent_dt;
    		    $visibility->save();
    		    
		    }
		    
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
		
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_nr_2019_patients_import');
		    $course->save();
		    
		    return $ipid;
		
		}		
		
    /**
     * TODO-2699
     * @author Ancuta - copy of old fn
     * 07.01.2020
     * @param unknown $csv_data
     * @param unknown $post
     */
		public function patient_import_handler_rp_2020_2699($csv_data, $post)
		{
		
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
// 		    dd($csv_data);
		    //living_will
		    $client_data = array();
		    // get client locations
		    $locations = Locations::getLocations($clientid,0);
		    foreach($locations as $k=>$ldata){
		        $client_data['locations'][$ldata['location']] = $ldata['id'];
		    }
		    
// 		    dd($client_data);
		    
		    // get client discharge locations
		    $client_dloc_q = Doctrine_Query::create()
		    ->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
		    ->from('DischargeLocation')
		    ->where('clientid =?',$clientid)
		    ->andWhere('isdelete=0');
		    $client_dloc = $client_dloc_q->fetchArray();
		    
		    if( ! empty($client_dloc)){
		        foreach($client_dloc as $k=>$loc_data){
		            $client_data['discharge_locations'][$loc_data['location']] = $loc_data['id'];
		        }
		    }
		    
		    // get client discharge methods
		    $client_dm_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('DischargeMethod')
		    ->where('clientid =?',$clientid)
		    ->andWhere('isdelete=0');
		    $client_dm = $client_dm_q->fetchArray();
		    	
		    if( ! empty($client_dm)){
		        foreach($client_dm as $k=>$dm_data){
// 		            $client_data['discharge_methods'][$dm_data['id']] = $dm_data['description'];
		            $client_data['discharge_methods'][trim($dm_data['description'])] = $dm_data['id'];
		        }
		    }
		    
		    // get client reffered
		    $client_ref_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('PatientReferredBy')
		    ->where("clientid =?", $clientid)
		    ->andWhere('isdelete=0')
		    ->orderBy('referred_name ASC');
		    $client_ref = $client_ref_q->fetchArray();
		    
		    if( ! empty($client_ref )){
		        foreach($client_ref  as  $rf_data){
// 		            $client_data['ReferredBy'][$rf_data['id']] = $rf_data['referred_name'];
		            $client_data['ReferredBy'][trim($rf_data['referred_name'])] = $rf_data['id'];
		        }
		    }
		    
		    // get client contactperson relationship
		    $client_fd_q = Doctrine_Query::create()
		    ->select('*')
		    ->from('FamilyDegree')
		    ->where("clientid =?", $clientid)
		    ->andWhere('isdelete=0');
		    $client_fd = $client_fd_q->fetchArray();
		    
		    if( ! empty($client_fd )){
		        foreach($client_fd  as  $fd_data){
// 		            $client_data['FamilyDegree'][$fd_data['id']] = $fd_data['family_degree'];
		            $client_data['FamilyDegree'][trim($fd_data['family_degree'])] = $fd_data['id'];
		        }
		    }
		    
		    $dg = new DiagnosisType();
		    $abb1 = "'HD'";
		    $ddarr1 = $dg->getDiagnosisTypes($clientid, $abb1);
		    $type_id_array = array();
		    if($ddarr1){
		    
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr1 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
		        $client_data['main_diagno_type'] = $type_id_array[0];
		    } 
		    
		    
		    $abb2 = "'ND'";
		    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		    $type_id_array = array();
		    if($ddarr2){
		    
		        $comma = ",";
		        $typeid = "'0'";
		        foreach($ddarr2 as $key => $valdia)
		        {
		            $type_id_array[] = $valdia['id'];
		        }
                $client_data['side_diagno_type'] = $type_id_array[0];
		    } 

		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            $inserted[] = $this->import_patient_rp_2020_2699($k_csv_row, $clientid, $userid,  $csv_data, $client_data);
		        }
		    }
		
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';		
		}
		
		private function import_patient_rp_2020_2699($csv_row, $clientid, $userid,  $csv_data, $client_data)
		{
		    $Tr = new Zend_View_Helper_Translate();
   		    $imported_csv_data = $csv_data[$csv_row];
   		    if(strlen($imported_csv_data['26']) > 0 ){
   		        if(date("Y",strtotime($imported_csv_data['26'])) < "2008" ){
   		            // skip all patients before 2008 
   		            return;
   		        }
   		    } else{
   		        // If no admission 
   		        if(strlen($imported_csv_data['27']) > 0  && date("Y",strtotime($imported_csv_data['27'])) < "2008" ){
   		            // skip all patients before 2008 
   		            return;
   		        }
   		    }
   		    
   		    
   		    
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $curent_dt_dmY = date('d.m.Y', time());

		    //GENDER
		    $gender = array();
		    $gender = array("männlich"=>"1","weiblich"=>"2");
		    if(strlen($imported_csv_data['2']) > 0 ){
		        $imported_csv_data['2'] = $gender[$imported_csv_data['2']];
		    }
		    
		    // BIRTHDSAY
		    if(strlen($imported_csv_data['7']) > 0 ){
		        $imported_csv_data['7'] = date("Y-m-d",strtotime($imported_csv_data['7']));
		    }
		    
		    // ADM
		    if(strlen($imported_csv_data['26']) > 0 ){
		        $imported_csv_data['26'] = date("Y-m-d 00:00:00",strtotime($imported_csv_data['26']));
		    }
		    //DEATH DATE // 25
		    if(strlen($imported_csv_data['25']) > 0 ){
		        $imported_csv_data['25'] = date("Y-m-d 00:00:00",strtotime($imported_csv_data['25']));
		    }
		    
		    // DISCHARGE DATE
		    if(strlen($imported_csv_data['27']) > 0 ){
		        $imported_csv_data['27'] = date("Y-m-d 00:00:00",strtotime($imported_csv_data['27']));
		    }
		    // vw_start_date
		    if(strlen($imported_csv_data['22']) > 0 ){
		        $imported_csv_data['22'] = date("Y-m-d",strtotime($imported_csv_data['22']));
		    }
		    
		    // vw_end_date
		    if(strlen($imported_csv_data['24']) > 0 ){
		        $imported_csv_data['24'] = date("Y-m-d",strtotime($imported_csv_data['24']));
		    }
 
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    //################################################################
		    //insert patient
		
		    
		
		

		
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    $cust->last_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['0']));
		    $cust->first_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['1']));
		    if(strlen($imported_csv_data['7']) > 0 ){
    		    $cust->birthd =  $imported_csv_data['7'];
		    }
	        $cust->sex = Pms_CommonData::aesEncrypt($imported_csv_data['2']);
	        $cust->street1 = Pms_CommonData::aesEncrypt(trim($imported_csv_data['4']));
		    $cust->zip = Pms_CommonData::aesEncrypt(trim($imported_csv_data['5']));
		    $cust->city = Pms_CommonData::aesEncrypt(trim($imported_csv_data['6']));
		    $cust->phone = Pms_CommonData::aesEncrypt(trim($imported_csv_data['8']));
	
		    if(strlen($imported_csv_data['3']) > 0 && isset($client_data['ReferredBy'][$imported_csv_data['3']])){
		        $cust->referred_by = $client_data['ReferredBy'][$imported_csv_data['3']];//
		    }
		    
		    $discharge_date = "";
		    $isdischarged = 0 ;
		    
		    if(!empty($imported_csv_data['26'])){
		        //[26] => admission date
		        $admission_date = date("Y-m-d H:i:s", strtotime( $imported_csv_data['26']));
		        $cust->admission_date = date("Y-m-d H:i:s", strtotime( $imported_csv_data['26']));
		        
		        
		        //[27] => discharge date
		        //[25] => date of death
		        if(empty($imported_csv_data['27']) &&  empty($imported_csv_data['25'])){
		            // active
		            $isdischarged = 0 ;
	       	        $cust->isdischarged =  0;
    		        $cust->traffic_status= '1';
		        } 
		        elseif(!empty($imported_csv_data['27']) || !empty($imported_csv_data['25'])){
		            
		            $discharge_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['27']));
		            if(!empty($imported_csv_data['25']) && strtotime($imported_csv_data['25']) < strtotime($imported_csv_data['27'])){
		                //if death date is  smaller than discharge date- use death date as discharge date 
    		            $discharge_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['25']));
		            }
		            // discharged
		            $isdischarged = 1 ;
	       	        $cust->isdischarged =  1;
    		        $cust->traffic_status= '0';
		        }
		
		    } else {
		        if(!empty($imported_csv_data['27']) || !empty($imported_csv_data['25'])){
		            
		            $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['27']));
		            if(strtotime($imported_csv_data['25']) < strtotime($imported_csv_data['27'])){
		                //if death date is  smaller than discharge date- use death date as discharge date
		                $admission_date = date("Y-m-d H:i:s", strtotime($imported_csv_data['25']));
		            }
		            $cust->admission_date = $admission_date;
		            $cust->isdischarged =  1;
		            $cust->traffic_status= '0';
		            $isdischarged = 1 ;
		            
		        } else{
    		        $cust->admission_date = date('2010-01-01 00:00:00'); //
    		        $admission_date = date('2010-01-01 00:00:00'); //
    		        $isdischarged = 0 ;
		        }
		    }
		    $cust->isadminvisible= '1';
		    $cust->import_pat = $imported_csv_data['32'];
		    $cust->save(); // ******************* SAVE *******************
		
		    $pat_id = $cust->id; 

		
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		    //################################################################
		    
            // Readmission - Admission
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
		    if(!empty($discharge_date) && $isdischarged == 1){
		        
    		    //################################################################
    		    // discharge method
		        $discharge_method = "";
		        $discharge_method_name = "";
		        if(!empty($imported_csv_data['29']) ){
		            $discharge_method = $client_data['discharge_methods'][$imported_csv_data['29']]; 
		            $discharge_method_name = $imported_csv_data['29']; 
		        } else{
		            $discharge_method = $client_data['discharge_methods']["Behandlungsbeendigung / Entlassung"]; 
		            $discharge_method_name = "Behandlungsbeendigung / Entlassung"; 
		        }
		        
		        $pd = new PatientDischarge();
	            $pd->ipid = $ipid;
	            $pd->discharge_date= $discharge_date;
                $pd->discharge_method = $discharge_method;
	            $pd->save();

	            // Patient discharge course - discharge date START
	            $discharge_date_course = "Entlassungszeitpunkt : " . date('d.m.Y', strtotime($discharge_date))."";
	            $cust = new PatientCourse();
	            $cust->ipid = $ipid;
	            $cust->course_date = date("Y-m-d H:i:s", time());
	            $cust->course_type = Pms_CommonData::aesEncrypt("K");
	            $cust->course_title = Pms_CommonData::aesEncrypt($discharge_date_course);
	            $cust->user_id = $userid;
	            $cust->save();
	            
	            $comment="Patient wurde am ".date('d.m.Y H:i', strtotime($discharge_date))."  entlassen \n Entlassungsart : ".$discharge_method_name."\n ";
	            $pc = new PatientCourse();
	            $pc->ipid = $ipid;
	            $pc->course_date = date("Y-m-d H:i:s",time());
	            $pc->course_type=Pms_CommonData::aesEncrypt("K");
	            $pc->course_title=Pms_CommonData::aesEncrypt($comment);
	            $pc->tabname=Pms_CommonData::aesEncrypt("discharge");
	            $pc->user_id = $userid;
	            $pc->save();
	            
	            
    		    //################################################################
    		    // Readmission - Discharge
    		    $patientreadmission = new PatientReadmission();
    		    $patientreadmission->user_id = $userid;
    		    $patientreadmission->ipid = $ipid;
    		    $patientreadmission->date = $discharge_date;
    		    $patientreadmission->date_type = "2";
    		    $patientreadmission->save();
		    }
		    

		    //################################################################
		    // INSERT in Patient locations
            /*  
		    [23] => last residence
		    [28] => first residence
		     */
		    $first_loc = $imported_csv_data['28'];
		    $last_loc = $imported_csv_data['23'];
		    
		    if(strlen($first_loc) > 0 ){
		        
		        $cust_pl = new PatientLocation();
		        $cust_pl->ipid = $ipid;
		        $cust_pl->clientid = $clientid;
		        $cust_pl->valid_from = $admission_date;
		        if(!empty($discharge_date) && $isdischarged == 1){
		            $cust_pl->valid_till = date('Y-m-d 00:00:00', strtotime($discharge_date));
		        } else{
		            if(strlen($last_loc) > 0 ){
    		            
		            } else{
		                $cust_pl->valid_till = "0000-00-00 00:00:00";
		            }
		        }
		        $cust_pl->location_id = $client_data['locations'][trim($first_loc)];
		        $cust_pl->save();
		    }
		    
		    if(strlen($last_loc) > 0  && $last_loc != $first_loc ){
		        
		        $cust_pll = new PatientLocation();
		        $cust_pll->ipid = $ipid;
		        $cust_pll->clientid = $clientid;
		        $cust_pll->valid_from = $discharge_date;
		        if(!empty($discharge_date) && $isdischarged == 1){
		            $cust_pll->valid_till = date('Y-m-d 00:00:00', strtotime($discharge_date));
		        } 
		        $cust_pll->location_id = $client_data['locations'][trim($last_loc)];
		        $cust_pll->save();
		    }
            
            //################################################################
            /* 
            [9] => contactperson lastname
            [10] => contactperson firstname
            [11] => contactperson phone
            [12] => contactperson city
            [13] => contactperson zip
            [14] => contactperson street
            [15] => contactperson relationship
             */
            if(strlen($imported_csv_data['9']) > 0 || strlen($imported_csv_data['10']) > 0)
            {
                $master_cpm = new ContactPersonMaster();
                $master_cpm->ipid = $ipid;
                $master_cpm->cnt_last_name = Pms_CommonData::aesEncrypt($imported_csv_data['9']);
                $master_cpm->cnt_first_name = Pms_CommonData::aesEncrypt($imported_csv_data['10']);
                $master_cpm->cnt_phone = Pms_CommonData::aesEncrypt($imported_csv_data['11']);
                $master_cpm->cnt_city = Pms_CommonData::aesEncrypt($imported_csv_data['12']);
                $master_cpm->cnt_zip = Pms_CommonData::aesEncrypt($imported_csv_data['13']);
                $master_cpm->cnt_street1 = Pms_CommonData::aesEncrypt($imported_csv_data['14']);
                $master_cpm->cnt_familydegree_id = $client_data['FamilyDegree'][$imported_csv_data['15']];
                $master_cpm->save();
            }
            
  

            //################################################################
            //Main_Diagnosis
            /* 
            [16] => all-diagnosis-ICD
            [17] => all-diagnosis description
             */
            if(strlen($imported_csv_data['16']) > 0 || strlen($imported_csv_data['17']) > 0){
                $diagno_free = new DiagnosisText();
                $diagno_free->clientid = $clientid;
                $diagno_free->icd_primary = $imported_csv_data['16'];
                if(strlen($imported_csv_data['17']) > 0 ){
                    $diagno_free->free_name = $imported_csv_data['17'];
                }
                $diagno_free->save();
                $free_diagno_id = $diagno_free->id;
            
                $diagno = new PatientDiagnosis();
                $diagno->ipid = $ipid;
                $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
                $diagno->diagnosis_type_id = $client_data['main_diagno_type'] ;
                $diagno->diagnosis_id = $free_diagno_id;
                $diagno->icd_id = "0";
                $diagno->save();
            }
            
            //################################################################
            //Side_Diagnosis
            /* 
             [18] => next diagnosis description
             */
            
            if(strlen($imported_csv_data['18']) > 0){
                $diagno_free = new DiagnosisText();
                $diagno_free->clientid = $clientid;
                $diagno_free->free_name = $imported_csv_data['18'];
                $diagno_free->save();
                $free_diagno_id = $diagno_free->id;
            
                $diagno = new PatientDiagnosis();
                $diagno->ipid = $ipid;
                $diagno->tabname = Pms_CommonData::aesEncrypt('diagnosis_freetext');
                $diagno->diagnosis_type_id = $client_data['side_diagno_type'] ;
                $diagno->diagnosis_id = $free_diagno_id;
                $diagno->icd_id = "0";
                $diagno->save();
            }
            
            //################################################################
            /*
             [19] => volunteer lastname
             [20] => volunteer firstname
             [21] => volunteer phone
             [22] => begin supply by volunteer
             [24] => end supply by volunteer
             */
            if(strlen($imported_csv_data['19']) > 0 || strlen($imported_csv_data['20']) > 0 )
            {
                $v_volunteer = array();
                $v_volunteer['last_name'] = $imported_csv_data['19'];
                $v_volunteer['first_name'] = $imported_csv_data['20'];
                $v_volunteer['phone'] = $imported_csv_data['21'];
                
                if(strlen(trim($imported_csv_data['22'])) > 0 || strlen($imported_csv_data['24']) > 0  ){
                    $v_volunteer['start_date'] = strlen(trim($imported_csv_data['22'])) > 0 ? date("Y-m-d 00:00:00",strtotime($imported_csv_data['22'])) : date("Y-m-d 00:00:00",strtotime($imported_csv_data['24']));
                    if(strlen($imported_csv_data['24']) > 0 ){
                        $v_volunteer['end_date'] = strlen(trim($imported_csv_data['24'])) > 0 ? date("Y-m-d 00:00:00",strtotime($imported_csv_data['24'])) : "0000-00-00 00:00:00";
                    }
                }
                
                $existing_voluntary_arr = array();
                $existing_voluntary = Doctrine_Query::create()
                ->select('*')
                ->from('Voluntaryworkers')
                ->where("isdelete = 0  ")
                ->andWhere("clientid = ".$clientid);
                $existing_voluntary->andWhere("  first_name like '%" . trim($v_volunteer['first_name']) . "%'  AND last_name like '%" . trim($v_volunteer['last_name']) . "%'      ");
                $existing_voluntary->andWhere("indrop = 0  ");
                $existing_voluntary_arr = $existing_voluntary->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
                
                
                $parent_id = 0;
                if(!empty($existing_voluntary_arr)){
                    
                    $parent_id  = $existing_voluntary_arr['id'];
                    // insert in  Voluntaryworkers
                    $master_vw = new Voluntaryworkers();
                    $master_vw->clientid = $clientid;
                    $master_vw->parent_id = $parent_id;
                    $master_vw->status = "e";//Hospizbegleiter
                    $master_vw->first_name = $existing_voluntary_arr['first_name'];
                    $master_vw->last_name = $existing_voluntary_arr['last_name'];
                    $master_vw->salutation = $existing_voluntary_arr['salutation'];
                    $master_vw->street =  $existing_voluntary_arr['street'];
                    $master_vw->zip = $existing_voluntary_arr['zip'];
                    $master_vw->city = $existing_voluntary_arr['city'];
                    $master_vw->phone = $existing_voluntary_arr['9'];
                    $master_vw->mobile = $existing_voluntary_arr['phone'];
                    $master_vw->email= $existing_voluntary_arr['mobile'];
                    $master_vw->comments = $existing_voluntary_arr['comments'];
                    $master_vw->indrop = 1;
                    $master_vw->save();
                    $master_vw_id = $master_vw->id;
                } else {
                    $volunteer = new Voluntaryworkers();
                    $volunteer->clientid = $clientid;
                    $volunteer->last_name = $v_volunteer['last_name'];
                    $volunteer->first_name = $v_volunteer['first_name'];
                    $volunteer->phone = $v_volunteer['phone'];
                    $volunteer->indrop = '1';
                    $volunteer->save();
                    $master_vw_id =  $volunteer->id;
                }
                
                // insert in patient
                $vw_new = new PatientVoluntaryworkers();
                $vw_new->ipid = $ipid;
                $vw_new->vwid = $master_vw_id;
                
                if(strlen($imported_csv_data['22']) > 0 ){
                    $vw_new->start_date = $v_volunteer['start_date'];
                } else {
                    $vw_new->start_date = date('Y-m-d 00:00:00', strtotime($admission_date));
                }
                
                if(!empty($imported_csv_data['24'])){
                    $vw_new->end_date = $v_volunteer['end_date'];
                } else{
                    if(!empty($discharge_date) && $isdischarged == 1){
                        $vw_new->end_date = date('Y-m-d 00:00:00', strtotime($discharge_date));
                    } 
                }
                
                $vw_new->isdelete = "0";
                $vw_new->save();
            }
            
            
            //################################################################
            //insert health insurance company data
            //[30] => health insurance
            if(!empty($imported_csv_data['30'])){
                $hi = new HealthInsurance();
                $hi->clientid = $clientid;
                $hi->name = $imported_csv_data['30'];
                $hi->extra = '1';
                $hi->save();
                $hi_id = $hi->id;
                
                //insert patient health insurance data
                $patient_hi = new PatientHealthInsurance();
                $patient_hi->ipid = $ipid;
                $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data['30']);
                $patient_hi->companyid = $hi_id;
                $patient_hi->save();
            }
            
            //################################################################
            // [31] => marital status
            $familienstandarray = array(
                "ledig"=>"1",
                "verheiratet"=>"2",
                "verwitwet"=>"3",
                "geschieden"=>"4",
                "getrennt lebend"=>"5",
                "unbek"=>"6",
                "Partnerschaft"=>"7",
                'Lebenspartner /-in'=>'7',
            );
            
            $marital_text = "";
            $marital_text_value = "";
            if(strlen($imported_csv_data['31']) > 0 )
            {
            
                $marital_text = trim($imported_csv_data['31']);
                $marital_text_value = $familienstandarray[$marital_text];
            }
            
            if( !empty($marital_text_value))
            {
                $cust = new Stammdatenerweitert();
                $cust->ipid = $ipid;
                if( !empty($marital_text_value))
                {
                    $cust->familienstand = $marital_text_value;
                }
                $cust->save();
            }

            
        
            
            //################################################################
					
            
            
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = $curent_dt;
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->done_date = $curent_dt;
		    $cust->save();
		    
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
		
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_rp_2020_patients_import');
		    $course->save();
		    
		    return $ipid;
		
		}		
		/**
		 * TODO-3629
		 * Ancuta 26.11.2020
		 * @param unknown $csv_data
		 * @param unknown $post
		 */
		public function patient_import_handler_bay_2020_3629($csv_data, $post)
		{
		
		    //ERROR REPORTING
		    ini_set('display_errors', 1);
		    ini_set('display_startup_errors', 1);
		    error_reporting(E_ALL);
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		    
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            $inserted[] = $this->import_patient_bay_2020_3629($k_csv_row, $clientid, $userid,  $csv_data);
		        }
		    }
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';		
		}
		/**
		 * TODO-3629
		 * Ancuta 26.11.2020
		 * @param unknown $csv_row
		 * @param unknown $clientid
		 * @param unknown $userid
		 * @param unknown $csv_data
		 * @param unknown $client_data
		 * @return void|string
		 */
		private function import_patient_bay_2020_3629($csv_row, $clientid, $userid,  $csv_data)
		{
		    $Tr = new Zend_View_Helper_Translate();
   		    $imported_csv_data = $csv_data[$csv_row];
   		    if(strlen($imported_csv_data['0']) > 0 ){
   		        if(date("Y",strtotime($imported_csv_data['0'])) < "2008" ){
   		            // skip all patients before 2008
   		            return;
   		        }
   		    } else{
   		        // If no admission 
   		        if(strlen($imported_csv_data['0']) > 0  && date("Y",strtotime($imported_csv_data['0'])) < "2008" ){
   		            // skip all patients before 2008
   		            return;
   		        }
   		    }
   		    
   		    
   		    /*
            [0] => Aufnahmedatum
            [1] => Vorname
            [2] => Nachname
            [3] => Strasse
            [4] => PLZ
            [5] => Stadt
            [6] => Telefon
            [7] => Mobiltelefon
            [8] => Geb.-Datum   		     
   		    */
   		    
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $curent_dt_dmY = date('d.m.Y', time());

		    // BIRTHDSAY
		    if(strlen($imported_csv_data['8']) > 0 ){
		        $imported_csv_data['8'] = date("Y-m-d",strtotime($imported_csv_data['8']));
		    } 
		    else
		    {
		        $imported_csv_data['8'] = "1970-01-01";
		    }
		    
		    // ADM
		    if(strlen($imported_csv_data['0']) > 0 ){
		        $imported_csv_data['0'] = date("Y-m-d 00:00:00",strtotime($imported_csv_data['0']));
		    }
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    
		    //################################################################
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    $cust->last_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['2']));
		    $cust->first_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['1']));
		    if(strlen($imported_csv_data['8']) > 0 ){
    		    $cust->birthd =  $imported_csv_data['8'];
		    }
		    
	        $cust->street1 = Pms_CommonData::aesEncrypt(trim($imported_csv_data['3']));
		    $cust->zip = Pms_CommonData::aesEncrypt(trim($imported_csv_data['4']));
		    $cust->city = Pms_CommonData::aesEncrypt(trim($imported_csv_data['5']));
		    $cust->phone = Pms_CommonData::aesEncrypt(trim($imported_csv_data['6']));
		    $cust->mobile = Pms_CommonData::aesEncrypt(trim($imported_csv_data['7']));
 
		    if(!empty($imported_csv_data['0'])){
		        //[0] => admission date
		        $admission_date = date("Y-m-d H:i:s", strtotime( $imported_csv_data['0']));
		        $cust->admission_date = date("Y-m-d H:i:s", strtotime( $imported_csv_data['0']));
		        
	            // active
       	        $cust->isdischarged =  0;
		        $cust->traffic_status= '1';
 
		
		    } else {
		        
		        $cust->admission_date = date('2010-01-01 00:00:00'); //
		        $admission_date = date('2010-01-01 00:00:00'); //
		    }
		    $cust->isadminvisible= '1';
		    $cust->import_pat = random_int(1000, 2000);
		    $cust->save(); // ******************* SAVE *******************
		
		    $pat_id = $cust->id; 
		
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		    //################################################################
		    
            // Readmission - Admission
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
            
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = $curent_dt;
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->done_date = $curent_dt;
		    $cust->save();
		    
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
		
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_bay_2020_patients_import');
		    $course->save();
		    
		    return $ipid;
		
		}		
		
		
		
		/**
		 * TODO-3839 Ancuta 09.03.2021 
		 * @param unknown $csv_data
		 * @param unknown $post
		 */
		public function patient_import_handler_wl_2021_3839($csv_data, $post)
		{
		
		    //ERROR REPORTING
		    ini_set('display_errors', 1);
		    ini_set('display_startup_errors', 1);
		    error_reporting(E_ALL);
		    
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $clientid = $logininfo->clientid;
		    $userid = $logininfo->userid;
		  
		    foreach($csv_data as $k_csv_row => $import_type)
		    {
		        if($k_csv_row != 0){
		            $inserted[] = $this->import_patient_wl_2021_3839($k_csv_row, $clientid, $userid,  $csv_data);
		        }
		    }
		
		    $import_session = new Zend_Session_Namespace('importSession');
		    $import_session->userid = '';
		    $import_session->form_data = '';		
		}
		
		
		
        /**
         * TODO-3839 Ancuta 09.03.2021 
         * @param unknown $csv_row
         * @param unknown $clientid
         * @param unknown $userid
         * @param unknown $csv_data
         * @return void|unknown
         */
		private function import_patient_wl_2021_3839($csv_row, $clientid, $userid,  $csv_data)
		{
		    $Tr = new Zend_View_Helper_Translate();
   		    $imported_csv_data = $csv_data[$csv_row];
   		    if(strlen($imported_csv_data['6']) > 0 ){
   		        if(date("Y",strtotime($imported_csv_data['6'])) < "2008" ){
   		            // skip all patients before 2008
   		            return;
   		        }
   		    } else{
   		        // If no admission 
   		        if(strlen($imported_csv_data['6']) > 0  && date("Y",strtotime($imported_csv_data['6'])) < "2008" ){
   		            // skip all patients before 2008
   		            return;
   		        }
   		    }
   		    
   		    
   		    /*
            [0] => Aufnahmedatum
            [1] => Vorname
            [2] => Nachname
            [3] => Strasse
            [4] => PLZ
            [5] => Stadt
            [6] => Telefon
            [7] => Mobiltelefon
            [8] => Geb.-Datum   		     
   		    */
   		    
		    $curent_dt = date('Y-m-d H:i:s', time());
		    $curent_dt_dmY = date('d.m.Y', time());

		    // BIRTHDSAY
		    if(strlen($imported_csv_data['2']) > 0 ){
		        $imported_csv_data['2'] = date("Y-m-d",strtotime($imported_csv_data['2']));
		    } 
		    else
		    {
		        $imported_csv_data['2'] = "1970-01-01";
		    }
		    
		    // ADM
		    if(strlen($imported_csv_data['6']) > 0 ){
		        $imported_csv_data['6'] = date("Y-m-d 00:00:00",strtotime($imported_csv_data['6']));
		    }
		    
		    //generate ipid and epid
		    $ipid = Pms_Uuid::GenerateIpid();
		
		    $epid_parts = Pms_Uuid::GenerateSortEpid($clientid);
		    $epid = $epid_parts['epid_chars'] . $epid_parts['epid_num'];
		
		    
		    //################################################################
		    //insert patient
		    $cust = new PatientMaster();
		    $cust->ipid = $ipid;
		    $cust->recording_date = $curent_dt;
		    $cust->last_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['0']));
		    $cust->first_name = Pms_CommonData::aesEncrypt(trim($imported_csv_data['1']));
		    if(strlen($imported_csv_data['2']) > 0 ){
    		    $cust->birthd =  $imported_csv_data['2'];
		    }
		    
	        $cust->street1 = Pms_CommonData::aesEncrypt(trim($imported_csv_data['5']));
		    $cust->zip = Pms_CommonData::aesEncrypt(trim($imported_csv_data['3']));
		    $cust->city = Pms_CommonData::aesEncrypt(trim($imported_csv_data['4']));
		    
		    //$cust->phone = Pms_CommonData::aesEncrypt(trim($imported_csv_data['6']));
		    //$cust->mobile = Pms_CommonData::aesEncrypt(trim($imported_csv_data['7']));
 
		    $gender = array("männlich"=>"1","weiblich"=>"2");
		    if($gender[$imported_csv_data['10']]){
		        $cust->sex = Pms_CommonData::aesEncrypt($gender[$imported_csv_data['10']]);
		    } else {
		        $cust->sex = Pms_CommonData::aesEncrypt("0");
		    }
		    
		    if(!empty($imported_csv_data['6'])){
		        //[0] => admission date
		        $admission_date = date("Y-m-d H:i:s", strtotime( $imported_csv_data['6']));
		        $cust->admission_date = date("Y-m-d H:i:s", strtotime( $imported_csv_data['6']));
		        
	            // active
       	        $cust->isdischarged =  0;
		        $cust->traffic_status= '1';
 
		
		    } else {
		        
		        $cust->admission_date = date('2010-01-01 00:00:00'); //
		        $admission_date = date('2010-01-01 00:00:00'); //
		    }
		    $cust->isadminvisible= '1';
// 		    $cust->import_pat = random_int(11000, 21000);
		    $cust->import_pat = $imported_csv_data['12'];
		    $cust->save(); // ******************* SAVE *******************
		
		    $pat_id = $cust->id; 
		
		    //################################################################
		    //insert epid-ipid
		    $res = new EpidIpidMapping();
		    $res->clientid = $clientid;
		    $res->ipid = $ipid;
		    $res->epid = $epid;
		    $res->epid_chars = $epid_parts['epid_chars'];
		    $res->epid_num = $epid_parts['epid_num'];
		    $res->save();
		    //################################################################
		    
            // Readmission - Admission
		    $patientreadmission = new PatientReadmission();
		    $patientreadmission->user_id = $userid;
		    $patientreadmission->ipid = $ipid;
		    $patientreadmission->date = $admission_date;
		    $patientreadmission->date_type = "1"; //1 =admission-readmission 2- discharge
		    $patientreadmission->save();
		    
            
		    $admission_date_course = "Aufnahmezeitpunkt : " . date('d.m.Y', strtotime($admission_date));
		    $cust = new PatientCourse();
		    $cust->ipid = $ipid;
		    $cust->course_date = $curent_dt;
		    $cust->course_type = Pms_CommonData::aesEncrypt("K");
		    $cust->course_title = Pms_CommonData::aesEncrypt($admission_date_course);
		    $cust->user_id = $userid;
		    $cust->done_date = $curent_dt;
		    $cust->save();
		    
		    
		    //insert in patient case
		    $case = new PatientCase();
		    $case->admission_date = $admission_date;
		    $case->epid = $epid;
		    $case->clientid = $clientid;
		    $case->save();
		
		    
		    
		    
		    //insert health insurance company data
		    $hi = new HealthInsurance();
		    $hi->clientid = $clientid;
		    $hi->name = $imported_csv_data['7'];
		    $hi->extra = '1';
		    $hi->save();
		    
		    $hi_id = $hi->id;
		    
		    $status_array = array('Rentner'=>"R","Mitglied"=>"M","F"=>"F");
		    //insert patient health insurance data
		    $patient_hi = new PatientHealthInsurance();
		    $patient_hi->ipid = $ipid;
		    $patient_hi->company_name = Pms_CommonData::aesEncrypt($imported_csv_data[7]);
		    $patient_hi->companyid = $hi_id;
		    $patient_hi->kvk_no = $imported_csv_data[9];
		    $patient_hi->insurance_no = $imported_csv_data[11];
		    $patient_hi->insurance_status = Pms_CommonData::aesEncrypt($status_array[$imported_csv_data[8]]);
		    $patient_hi->save();
		    
		    
		
		    //write first entry in patient course
		    $course = new PatientCourse();
		    $course->ipid = $ipid;
		    $course->course_date = $curent_dt;
		    $course->course_type = Pms_CommonData::aesEncrypt('K');
		    $course->course_title = Pms_CommonData::aesEncrypt('Patienten importiert '.$curent_dt_dmY);
		    $course->done_date = $curent_dt;
		    $course->user_id = $userid;
		    $course->tabname = Pms_CommonData::aesEncrypt('new_wl_2021_patients_import');
		    $course->save();
		    
		    return $ipid;
		
		}		
		
	}

?>