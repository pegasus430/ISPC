<?php

	class TempController extends Zend_Controller_Action {

		public function importnopainAction()
		{
			exit;
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();

			$logininfo = new Zend_Session_Namespace('Login_Info');

			if(($handle = fopen('import/nr_patients/nopain-new.csv', "r")) !== FALSE)
			{
				while(($data = fgetcsv($handle, 4096, ";", '"')) !== FALSE)
				{
					if(!empty($data[0]))
					{

						$patient_data[$data[0]]['lastname'] = $data[0];
						$patient_data[$data[0]]['firstname'] = $data[1];
						$patient_data[$data[0]]['dob'] = $data[2];
						$patient_data[$data[0]]['krakenkasse'] = $data[3];
						$patient_data[$data[0]]['newdiagnosis'][1] = $data[4];
						$patient_data[$data[0]]['family_doc'] = ($data[5]);
						$patient_data[$data[0]]['admission'] = strtotime($data[6]);

						$patient_data[$data[0]]['street'] = ($data[7]);
						$patient_data[$data[0]]['zip'] = ($data[8]);
						$patient_data[$data[0]]['city'] = ($data[9]);
					}
				}
				fclose($handle);
			}

			unset($data);

			$clientid = 102;

			foreach($patient_data as $data)
			{

				$a_post = '';
				if($data['admission'] > 0)
				{

					$docform = new Application_Form_Familydoctor();
					$a_post['doclast_name'] = $data['family_doc'];
					$a_post['indrop'] = 1;
					$docinfo = $docform->InsertDataFromAdmission($a_post);
					$a_post['hidd_docid'] = $docinfo->id;

					$cust = new PatientMaster();
					$cust->ipid = Pms_Uuid::GenerateIpid();
					$cust->recording_date = date('Y-m-d H:i:s', $data['admission']);
					$cust->last_name = Pms_CommonData::aesEncrypt($data['lastname']);
					$cust->first_name = Pms_CommonData::aesEncrypt($data['firstname']);
					$cust->birthd = date('Y-m-d', strtotime($data['dob']));
					$cust->street1 = Pms_CommonData::aesEncrypt($data['street']);
					$cust->zip = Pms_CommonData::aesEncrypt($data['zip']);
					$cust->city = Pms_CommonData::aesEncrypt($data['city']);
					$cust->familydoc_id = $a_post['hidd_docid'];

					$cust->admission_date = date('Y-m-d H:i:s', $data['admission']);

					$cust->save();
					$ipid = $cust->ipid;

					/* Patient Case */

					$case = new PatientCase();
					$case->admission_date = date('Y-m-d H:i:s', $data['admission']);
					$case->clientid = $clientid;
					$case->save();

					$epid = Pms_Uuid::GenerateEpid($clientid, $case->id);
					$case = Doctrine::getTable('PatientCase')->find($case->id);
					$case->epid = $epid;
					$case->save();

					$patient_epidipid_form = new Application_Form_EpidIpidMapping();
					$a_post['epid'] = $epid;
					$a_post['ipid'] = $ipid;
					$a_post['no_assign'] = 1;
					$patient_epidipid_form->InsertData($a_post);

					$standby = false;

					$abb = "'HD','ND'";
					$dg = new DiagnosisType();
					$darr = $dg->getDiagnosisTypes($clientid, $abb);

					$patient_diagnosis = new Application_Form_PatientDiagnosis();
					$userid = $logininfo->userid;


					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", $data['admission']);
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt("Hausarzt " . $data['family_doc'] . " eingetragen.");
					$cust->user_id = $userid;
					$cust->save();

					if($standby != 'STANDBY')
					{
						$a_post['clientid'] = $clientid;
						$a_post['ipid'] = $ipid;
						$a_post['epid'] = $epid;
						$a_post['newdiagnosis'] = $data['newdiagnosis'];
						$a_post['diagno_abb'] = $abb;
						$a_post['dtype'][1] = '2';
						$a_post['hidd_tab'][1] = 'text';
						$a_post['hidd_diagnosis'][1] = '';
						$a_post['diagnosis'][1] = '';

						$patient_insurance_form = new Application_Form_PatientHealthInsurance();
						$pat_maintainance = new Application_Form_PatientMaintainanceStage();
						$patdiagnometa = new Application_Form_PatientDiagnosisMeta();

						$a_post['company_name'] = $data['krakenkasse'];


						$patient_insurance_form->InsertData($a_post);



						$patientreadmission = new PatientReadmission();
						$patientreadmission->user_id = 1;
						$patientreadmission->ipid = $ipid;
						$patientreadmission->date = date('Y-m-d H:i:s', $data['admission']);
						$patientreadmission->date_type = "1";
						$patientreadmission->special_medical_assistance = '';
						$patientreadmission->save();


						$diagno_text = new Application_Form_DiagnosisText();

						$dt = $diagno_text->InsertData($a_post);

						foreach($dt as $key => $val)
						{
							$a_post['newhidd_diagnosis'][$key] = $val->id;
						}


						$patient_diagnosis->insertMetaData($a_post);
						$diagnoarr = $patient_diagnosis->InsertData($a_post);

						$patdiagnometa->InsertData($a_post);

						$cust = new PatientCourse();
						$cust->ipid = $ipid;
						$cust->course_date = date("Y-m-d H:i:s", $data['admission']);
						$cust->course_type = Pms_CommonData::aesEncrypt('H');
						$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($a_post['newdiagnosis'][1]));
						$cust->user_id = $userid;
						$cust->isstandby = '0';
						$cust->save();
					}

					echo $epid . ' - ' . date('Y-m-d H:i:s', $data['admission']) . '<br />';
				}
			}
		}

		public function importdetailsAction()
		{
			exit;
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();

			$insurance_statuses = array(
				'3' => 'F',
				'1' => 'M',
				'5' => 'R'
			);

			if(($handle = fopen('heimport/patients.csv', "r")) !== FALSE)
			{
				while(($data = fgetcsv($handle, 4096, ",")) !== FALSE)
				{
					$patients[$data[0]] = $data[1];
				}
				fclose($handle);
			}

			unset($data);

			if(($handle = fopen('heimport/patient_data.csv', "r")) !== FALSE)
			{
				while(($data = fgetcsv($handle, 4096, ";", '*')) !== FALSE)
				{
					if(!empty($data[0]))
					{
						$patient_data[$data[0]]['id'] = $data[0];
						$patient_data[$data[0]]['gender'] = $data[1];
						$patient_data[$data[0]]['firstname'] = $data[2];
						$patient_data[$data[0]]['lastname'] = $data[3];
						$patient_data[$data[0]]['street'] = $data[4];
						$patient_data[$data[0]]['zip'] = $data[5];
						$patient_data[$data[0]]['city'] = $data[6];
						$patient_data[$data[0]]['dob'] = $data[7];
						$patient_data[$data[0]]['dob2'] = $data[8];
						$patient_data[$data[0]]['gender2'] = $data[9];
						$patient_data[$data[0]]['cave'] = $data[10];
						$patient_data[$data[0]]['hi_no'] = $data[11];
						$patient_data[$data[0]]['kasse'] = $data[12];
						$patient_data[$data[0]]['hi_status'] = $data[13];
						$patient_data[$data[0]]['krakenkasse'] = $data[14];
						$patient_data[$data[0]]['pflegestufe'] = $data[15];
					}
				}
				fclose($handle);
			}

			unset($data);

			if(($handle = fopen('heimport/verordnung_processed.csv', "r")) !== FALSE)
			{
				while(($data = fgetcsv($handle, 4096, ",")) !== FALSE)
				{
					$v_start = strtotime($data[1]);
					$v_end = strtotime($data[2]);
					if($v_start > 0 && $v_end > 0)
					{
						$verordnung[$data[0]][] = array(
							'start' => $v_start,
							'end' => $v_end
						);
						if(empty($patient_data[$data[0]]['admission']) || $patient_data[$data[0]]['admission'] > $v_start)
						{
							$patient_data[$data[0]]['admission'] = $v_start;
						}
					}
				}
				fclose($handle);
			}
			unset($data);

			if(($handle = fopen('heimport/results.csv', "r")) !== FALSE)
			{
				while(($data = fgetcsv($handle, 4096, ";")) !== FALSE)
				{

					$epidsipids[$data[1]]['epid'] = $data[0];
					$epidsipids[$data[1]]['ipid'] = $data[2];
				}
				fclose($handle);
			}

			$clientid = 101;

			foreach($patients as $patient_id => $standby)
			{



				$data = $patient_data[$patient_id];


				$ipid = $epidsipids[$patient_id]['ipid'];
				$epid = $epidsipids[$patient_id]['epid'];


				if($standby != 'STANDBY' && $ipid && $epid)
				{
					$a_post['clientid'] = $clientid;
					$a_post['ipid'] = $ipid;
					$a_post['epid'] = $epid;

					if(sizeof($verordnung[$patient_id]) > 0)
					{




						foreach($verordnung[$patient_id] as $vv)
						{

							unset($v_post);
							unset($docform);
							unset($sapvver);

							$v_post['verordnet_von'] = 'Hausarzt';

							$sapvver = new Application_Form_SapvVerordnung();
							$v_post['verordnet'] = '4,';
							$v_post['status'] = '3';
							$v_post['verordnungam'] = date('Y-m-d H:i:s', $vv['start']);
							$v_post['verordnungbis'] = date('Y-m-d H:i:s', $vv['end']);
							$v_post['ipid'] = $ipid;
							$sapvver->InsertData($v_post);
						}
					}

					if(!empty($data['cave']))
					{

						$cust = new PatientCourse();
						$cust->ipid = $ipid;
						$cust->course_date = date("Y-m-d H:i:s", $data['admission']);
						$cust->course_type = Pms_CommonData::aesEncrypt("C");
						$cust->course_title = Pms_CommonData::aesEncrypt($data['cave']);
						$cust->user_id = 1015;
						$cust->save();
					}
				}

				echo $epid . ' - ' . $patient_id . '<br />';
			}
		}

		public function importAction()
		{
			exit;
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();

			$insurance_statuses = array(
				'3' => 'F',
				'1' => 'M',
				'5' => 'R'
			);

			if(($handle = fopen('heimport/patients.csv', "r")) !== FALSE)
			{
				while(($data = fgetcsv($handle, 4096, ",")) !== FALSE)
				{
					$patients[$data[0]] = $data[1];
				}
				fclose($handle);
			}

			unset($data);

			if(($handle = fopen('heimport/patient_data.csv', "r")) !== FALSE)
			{
				while(($data = fgetcsv($handle, 4096, ";", '*')) !== FALSE)
				{
					if(!empty($data[0]))
					{
						$patient_data[$data[0]]['id'] = $data[0];
						$patient_data[$data[0]]['gender'] = $data[1];
						$patient_data[$data[0]]['firstname'] = $data[2];
						$patient_data[$data[0]]['lastname'] = $data[3];
						$patient_data[$data[0]]['street'] = $data[4];
						$patient_data[$data[0]]['zip'] = $data[5];
						$patient_data[$data[0]]['city'] = $data[6];
						$patient_data[$data[0]]['dob'] = $data[7];
						$patient_data[$data[0]]['dob2'] = $data[8];
						$patient_data[$data[0]]['gender2'] = $data[9];
						$patient_data[$data[0]]['cave'] = $data[10];
						$patient_data[$data[0]]['hi_no'] = $data[11];
						$patient_data[$data[0]]['kasse'] = $data[12];
						$patient_data[$data[0]]['hi_status'] = $data[13];
						$patient_data[$data[0]]['krakenkasse'] = $data[14];
						$patient_data[$data[0]]['pflegestufe'] = $data[15];
					}
				}
				fclose($handle);
			}

			unset($data);

			if(($handle = fopen('heimport/verordnung_processed.csv', "r")) !== FALSE)
			{
				while(($data = fgetcsv($handle, 4096, ",")) !== FALSE)
				{
					$v_start = strtotime($data[1]);
					$v_end = strtotime($data[2]);
					if($v_start > 0 && $v_end > 0)
					{
						$verordnung[$data[0]][] = array(
							'start' => $v_start,
							'end' => $v_end
						);
						if(empty($patient_data[$data[0]]['admission']) || $patient_data[$data[0]]['admission'] > $v_start)
						{
							$patient_data[$data[0]]['admission'] = $v_start;
						}
					}
				}
				fclose($handle);
			}

			$clientid = 101;
			foreach($patients as $patient_id => $standby)
			{
				$data = $patient_data[$patient_id];
				$cust = new PatientMaster();
				$cust->ipid = Pms_Uuid::GenerateIpid();
				$cust->recording_date = date('Y-m-d H:i:s', $data['admission']);
				$cust->last_name = Pms_CommonData::aesEncrypt($data['lastname']);
				$cust->first_name = Pms_CommonData::aesEncrypt($data['firstname']);
				$cust->birthd = date('Y-m-d', strtotime($data['dob']));
				$cust->street1 = Pms_CommonData::aesEncrypt($data['street']);
				$cust->zip = Pms_CommonData::aesEncrypt($data['zip']);
				$cust->city = Pms_CommonData::aesEncrypt($data['city']);

				switch($data['gender2'])
				{
					case 'männlich' : $sex = 1;
						break;
					case 'weiblich' : $sex = 2;
						break;
					default: $sex = 0;
						break;
				}
				$cust->sex = Pms_CommonData::aesEncrypt($sex);

				$cust->admission_date = date('Y-m-d H:i:s', $data['admission']);

				if($standby == 'STANDBY')
				{
					$cust->isstandby = 1;
				}
				else
				{
					$cust->isstandby = 0;
				}

				$cust->save();
				$ipid = $cust->ipid;

				/* Patient Case */

				$case = new PatientCase();
				$case->admission_date = date('Y-m-d H:i:s', $data['admission']);
				$case->clientid = $clientid;
				$case->save();

				$epid = Pms_Uuid::GenerateEpid($clientid, $case->id);
				$case = Doctrine::getTable('PatientCase')->find($case->id);
				$case->epid = $epid;
				$case->save();

				$patient_epidipid_form = new Application_Form_EpidIpidMapping();
				$a_post['epid'] = $epid;
				$a_post['ipid'] = $ipid;
				$patient_epidipid_form->InsertData($a_post);

				if($standby != 'STANDBY')
				{
					$a_post['clientid'] = $clientid;
					$a_post['ipid'] = $ipid;
					$a_post['epid'] = $epid;

					$patient_insurance_form = new Application_Form_PatientHealthInsurance();
					$pat_maintainance = new Application_Form_PatientMaintainanceStage();

					$a_post['company_name'] = $data['krakenkasse'];
					$a_post['insurance_no'] = $data['hi_no'];
					$a_post['kvk_no'] = $data['kasse'];
					$a_post['insurance_status'] = $insurance_statuses[$data['hi_status']];

					$patient_insurance_form->InsertData($a_post);

					if($data['pflegestufe'] == 'beantragt')
					{
						$a_post['stage'] = 'keine';
						$a_post['horherstufung'] = '1';
					}
					else
					{
						$a_post['horherstufung'] = '0';
						switch(trim($data['pflegestufe']))
						{
							case 'I':
								$a_post['stage'] = '1';
								break;
							case 'II':
								$a_post['stage'] = '2';
								break;
							case 'III':
								$a_post['stage'] = '3';
								break;
							default:
								$a_post['stage'] = 'keine';
								break;
						}
					}

					$pat_maintainance->InsertData($a_post);

					$patientreadmission = new PatientReadmission();
					$patientreadmission->user_id = 1;
					$patientreadmission->ipid = $ipid;
					$patientreadmission->date = date('Y-m-d H:i:s', $data['admission']);
					$patientreadmission->date_type = "1";
					$patientreadmission->special_medical_assistance = '';
					$patientreadmission->save();
				}

				echo $epid . ' - ' . $patient_id . '<br />';
			}
		}

		public function exportsecrecytrackerAction()
		{
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;
			$clientid = $logininfo->clientid;

			$patient = Doctrine_Query::create()
				->select('p.ipid,e.*')
				->from('PatientMaster p')
				->where('p.isdelete = 0');
			$patient->leftJoin("p.EpidIpidMapping e");
			$patient->andWhere('e.clientid = ' . $logininfo->clientid);
			$patienidtarray = $patient->fetchArray();

			foreach($patienidtarray as $single_patient)
			{
				$patients[$single_patient['ipid']] = $single_patient['EpidIpidMapping']['epid'];
			}


			$pc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,
						AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title,
						AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname,
						AES_DECRYPT(done_name,'" . Zend_Registry::get('salt') . "') as done_name,
						DATE_FORMAT(create_date, '%d.%m.%Y %H:%i') as ledate")
				->from('PatientCourse')
				->whereIn('ipid', array_keys($patients))
				->andWhere('AES_DECRYPT(course_type,"' . Zend_Registry::get('salt') . '") = "ST"')
				->orderBy('ipid ASC, DATE_FORMAT(create_date, "%Y-%m-%d %H:%i") ASC');
			$pcarray = $pc->fetchArray();
			foreach($pcarray as $pc_single)
			{
				echo $patients[$pc_single['ipid']] . ',' . $pc_single['ledate'] . ',' . $pc_single['course_title'] . "\n";
			}
			exit;

			//$cust->course_type = Pms_CommonData::aesEncrypt("ST");
		}

		
		function exportassesmentsAction() {
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->disableLayout();
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');
			$this->view->hidemagic = $hidemagic;
			$clientid = $logininfo->clientid;
			
			$patient = Doctrine_Query::create()
			->select('p.ipid,e.*')
			->from('PatientMaster p')
			->where('p.isdelete = 0');
			$patient->leftJoin("p.EpidIpidMapping e");
			$patient->andWhere('e.clientid = ' . $logininfo->clientid);
			$patienidtarray = $patient->fetchArray();
			
			foreach($patienidtarray as $single_patient)
			{
				$patients[$single_patient['ipid']] = $single_patient['EpidIpidMapping']['epid'];
				$ipids .= '"'.$single_patient['ipid'].'",';
			}
			
			$assesment = Doctrine_Query::create()
			->select("*")
			->from('KvnoAssessment')
			->where('ipid in(' . substr($ipids,0,-1) . ')')
			->andWhere('iscompleted = 1')
			->andWhere('completed_date BETWEEN "2014-01-01" AND "' . date("Y-m-d H:i:s") . '"');
			
			$assarr = $assesment->fetchArray();
			
			foreach($assarr as $ass_single) {
				echo $patients[$ass_single['ipid']].','.date('d/m/Y', strtotime($ass_single['completed_date']))."\n";
			}
			
			exit;
				
		}
		
		public function updateformssubusersAction()
		{
			set_time_limit(0);
			$this->_helper->viewRenderer->setNoRender();
			$this->_helper->layout->setLayout('layout_ajax');

			//get old subusers system
			$old_data = Pms_CommonData::getSubUsers($client, false);

			//get new subusers system
			$new_data = PseudoUsers::get_pseudo_user_data();

			//get all kvno contact forms!
			$sel_q = Doctrine_Query::create()
				->select('*')
				->from('KvnoNurse')
				->where('isdelete ="0"')
				->andWhere('sub_user != 0');
			$sel_res = $sel_q->fetchArray();

			$forms_ipids[] = "9999999999999999";
			foreach($sel_res as $k_visit => $v_visit)
			{
				$forms_ipids[] = $v_visit['ipid'];
			}

			$ipids2client = Pms_CommonData::get_patients_client($forms_ipids);

			foreach($sel_res as $k_visit => $v_visit)
			{
				$pseudo_requirements[$v_visit['id']]['client'] = $ipids2client[$v_visit['ipid']];
				$pseudo_requirements[$v_visit['id']]['create_user'] = $v_visit['create_user'];
				$pseudo_requirements[$v_visit['id']]['old_sub_user'] = $v_visit['sub_user'];

				$sub_user_name = trim(rtrim($old_data[$ipids2client[$v_visit['ipid']]][$v_visit['create_user']][$v_visit['sub_user']]['name']));

				$found_new_id = array_search($sub_user_name, $new_data[$ipids2client[$v_visit['ipid']]]);
				if($found_new_id)
				{
					$pseudo_requirements[$v_visit['id']]['new_sub_user'] = $found_new_id;
				}
				else
				{
					$pseudo_requirements[$v_visit['id']]['new_sub_user'] = "BANG";
				}
			}

			if($pseudo_requirements)
			{
				foreach($pseudo_requirements as $k_form_id => $v_form_requirements)
				{
					if($v_form_requirements['new_sub_user'] != 'BANG')
					{
						$upd_form = Doctrine::getTable('KvnoNurse')->findOneById($k_form_id);
						if($upd_form)
						{
							$upd_form->sub_user = $v_form_requirements['new_sub_user'];
							$upd_form->save();
							
							$updated_forms[] = $upd_form->id;
//							$updated_forms[] = $k_form_id;
						}
					}
					else
					{
						$banged_forms[] = $k_form_id;
					}
				}
			}
	
			print_r("To be updated data\n");
			print_r($pseudo_requirements);
			print_r("\n\n");
			print_r("Updated forms\n");
			print_r($updated_forms);
			print_r("\n\n");
			print_r("Banged forms\n");
			print_r($banged_forms);
			exit;
		}

	}

?>