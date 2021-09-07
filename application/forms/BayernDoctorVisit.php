<?php

	require_once("Pms/Form.php");

	class Application_Form_BayernDoctorVisit extends Pms_Form {

		public function validate($post)
		{
			$Tr = new Zend_View_Helper_Translate();

			$error = 0;
			$validator = new Pms_Validation();
			if(!$validator->isstring($post['visit_date']) || !$validator->isdate($post['visit_date']))
			{
				$this->error_message['visit_date'] = $Tr->translate('bay_visit_date_required');
				$error = 1;
			}

			if($error == 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function insertBayernDoctorVisit($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$allow_e_entries = true;
			$verlauf_e_previleges = new Modules();

			if($verlauf_e_previleges->checkModulePrivileges("73", $clientid))// DEACTIVATE the E Verlauf entries from Besuchsformular Bayern
			{
				$allow_e_entries = false;
			}

			if(is_array($post['additional_users']) && sizeof($post['additional_users']) > 0)
			{
				$user = new User();
				$c_users = $user->getUserByClientid($clientid, 0, true);

				foreach($c_users as $k_c_users => $v_c_users)
				{
					$client_users[$v_c_users['id']] = $v_c_users;
				}

				foreach($post['additional_users'] as $id_aditional_user => $v_aditional_user)
				{
					if($v_aditional_user['value'] == '1')
					{
						$usr_details[] = $client_users[$id_aditional_user]['user_title'] . ' ' .$client_users[$id_aditional_user]['last_name'] . ', ' . $client_users[$id_aditional_user]['first_name'];
						$usr_ids[] = $client_users[$id_aditional_user]['id'];
					}
				}
			}
			$usr_ids = array_values(array_unique($usr_ids));
			$aditional_users = join(',', $usr_ids);


			$stmb = new BayernDoctorVisit();
			$stmb->ipid = $ipid;

			// validate visit date
			if(empty($post['visit_date']) || !Pms_Validation::isdate($post['visit_date']) ){
			    $post['visit_date'] = date('d.m.Y');
			}
			
			if(empty($post['begin_date_h']) || strlen($post['begin_date_h']) == 0){
			    $post['begin_date_h'] = date('H', strtotime('-5 minutes'));
			}
			
			
			if(empty($post['begin_date_m']) || strlen($post['begin_date_m']) == 0){
			    $post['begin_date_m'] = date('i', strtotime('-5 minutes'));
			}
			
			
			if(empty($post['end_date_h']) || strlen($post['end_date_h']) == 0){
			    $post['end_date_h'] = date('H', strtotime('+10 minutes'));
			}
			
			
			if(empty($post['end_date_m']) || strlen($post['end_date_m']) == 0){
			    $post['end_date_m'] = date('i', strtotime('+10 minutes'));
			}
			
			
			
			
			
			/* -----------------VISIT START DATE AND END DATE ------- */
			$visit_date = explode(".", $post['visit_date']);

			$stmb->start_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
			$stmb->end_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $post['end_date_h'] . ':' . $post['end_date_m'] . ':00'));

			$stmb->begin_date_h = $post['begin_date_h'];
			$stmb->begin_date_m = $post['begin_date_m'];
			$stmb->end_date_h = $post['end_date_h'];
			$stmb->end_date_m = $post['end_date_m'];
			$stmb->visit_date = $visit_date[2] . "-" . $visit_date[1] . "-" . $visit_date[0] . ' ' . date("H") . ':' . date("i") . ":00";
			/* ----------------------------------------------------- */
			$stmb->documantation_time = $post['documantation_time'];
			$stmb->fahrtzeit = $post['fahrtzeit'];
			$stmb->fahrtstreke_km = $post['fahrtstreke_km'];
			$stmb->peg = $post['peg'];
			$stmb->peg_text = $post['peg_text'];
			$stmb->port = $post['port'];
			$stmb->port_text = $post['port_text'];
			$stmb->pumps = $post['pumps'];
			$stmb->pumps_text = $post['pumps_text'];
			$stmb->dk = $post['dk'];
			$stmb->dk_text = $post['dk_text'];
			$stmb->kunstliche = $post['kunstliche'];
			$stmb->kunstliche_text = $post['kunstliche_text'];
			//newly added 21.05.2012
			$stmb->darm = $post['darm'];
			$stmb->darm_text = $post['darm_text'];
			$stmb->blase = $post['blase'];
			$stmb->blase_text = $post['blase_text'];
			$stmb->luftrohre = $post['luftrohre'];
			$stmb->luftrohre_text = $post['luftrohre_text'];
			$stmb->ablaufsonde = $post['ablaufsonde'];
			$stmb->ablaufsonde_text = $post['ablaufsonde_text'];

			$stmb->kopf = $post['kopf'];
			$stmb->kopf_text = htmlspecialchars($post['kopf_text']);
			$stmb->thorax = $post['thorax'];
			$stmb->thorax_text = htmlspecialchars($post['thorax_text']);
			$stmb->abdomen = $post['abdomen'];
			$stmb->abdomen_text = htmlspecialchars($post['abdomen_text']);
			$stmb->extremitaten = $post['extremitaten'];
			$stmb->extremitaten_text = htmlspecialchars($post['extremitaten_text']);
			$stmb->haut_wunden = $post['haut_wunden'];
			$stmb->haut_wunden_text = htmlspecialchars($post['haut_wunden_text']);
			$stmb->neurologisch_psychiatrisch = $post['neurologisch_psychiatrisch'];
			$stmb->neurologisch_psychiatrisch_text = htmlspecialchars($post['neurologisch_psychiatrisch_text']);
			$stmb->ecog = join(",", $post['ecog']);
			$stmb->sonstiges = htmlspecialchars($post['sonstiges']);
			$stmb->comment_apotheke = htmlspecialchars($post['comment_apotheke']);
			$stmb->global = join(",", $post['global']);
			$stmb->related_users = $aditional_users;
			$stmb->case_history = htmlspecialchars($post['case_history']);
			$stmb->conversation_phonecall = htmlspecialchars($post['conversation_phonecall']);
			$stmb->save();

			$result = $stmb->id;

			$done_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("F");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
			$cust->done_id = $result;
			$cust->save();

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($post['begin_date_h'] . ":" . $post['begin_date_m'] . ' - ' . $post['end_date_h'] . ':' . $post['end_date_m'] . '  ' . $post['visit_date']);
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
			$cust->done_id = $result;
			$cust->save();

			if(is_array($post['input_value']) && sizeof($post['input_value']) > 0)
			{
				$a_post = $post;
				$a_post['ipid'] = $ipid;
				$save_symp = 0;
				foreach($a_post['input_value'] as $val)
				{
					if(strlen($val) > '0')
					{
						$save_symp = 1;
					}
				}
				if($save_symp == 1)
				{
					$patient_form = new Application_Form_PatientSymptomatology();
					$a_post['iskvno'] = 1;
					$a_post['kvnoid'] = 'd' . $result; //"d" is for doctor
					$a_post['edit_entry_date'] = $done_date;
					$patient_form->InsertData($a_post);
				}

				$current_values = $post['current_value'];
				$comments = $post['comment'];
				foreach($post['input_value'] as $symp_id => $val)
				{
					if(strlen($val) > 0)
					{
						$sympvals = new BayernDoctorSymp();
						$sympvals->bdf_id = $result;
						$sympvals->ipid = $ipid;
						$sympvals->symp_id = $symp_id;
						$sympvals->last_value = ($current_values[$symp_id] == '' ? NULL : $current_values[$symp_id]);
						$sympvals->current_value = ($val == '' ? NULL : $val);
						$sympvals->comment = htmlspecialchars($comments[$symp_id]);
						$sympvals->save();
						$tocourse['input_value'] = $val;
						$tocourse['second_value'] = $post['comment'][$symp_id];
						$tocourse['symptid'] = $symp_id;
						$tocourse['setid'] = 1;
						$tocourse['iskvno'] = '0';
						$coursecomment[] = $tocourse;
					}
				}
			}

			if(!empty($usr_details))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Beteiligte Mitarbeiter: " . implode(', ', $usr_details));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}

			if(!empty($post['fahrtzeit']) & $post['fahrtzeit'] != "--")
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Fahrtzeit: " . $post['fahrtzeit']);
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if(!empty($coursecomment))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("S");
				$cust->course_title = Pms_CommonData::aesEncrypt(serialize($coursecomment));
				$cust->isserialized = 1;
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['port'] == "2" && !empty($post['port_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Port - nicht ok : " . htmlspecialchars(addslashes($post['port_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kunstliche'] == "2" && !empty($post['kunstliche_text']))
			{
				if(!empty($post['kunstlichemore']))
				{
					$kunstlichemore = "(" . $post['kunstlichemore'] . ") ";
				}
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("besonderer Aus-/ Eingang " . $kunstlichemore . "- nicht ok : " . htmlspecialchars(addslashes($post['kunstliche_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['pumps'] == "2" && !empty($post['pumps_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Pumpe - nicht ok : " . htmlspecialchars(addslashes($post['pumps_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['dk'] == "2" && !empty($post['dk_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Dauerkatheter - nicht ok : " . htmlspecialchars(addslashes($post['dk_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['peg'] == "2" && !empty($post['peg_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("PEG - nicht ok : " . htmlspecialchars(addslashes($post['peg_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['darm'] == "2" && !empty($post['darm_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Darm - nicht ok : " . htmlspecialchars(addslashes($post['darm_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['blase'] == "2" && !empty($post['blase_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Blase - nicht ok : " . htmlspecialchars(addslashes($post['blase_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['luftrohre'] == "2" && !empty($post['luftrohre_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Luftröhre - nicht ok : " . htmlspecialchars(addslashes($post['luftrohre_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['ablaufsonde'] == "2" && !empty($post['ablaufsonde_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Ablaufsonde  - nicht ok : " . htmlspecialchars(addslashes($post['ablaufsonde_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kopf'] == 2 && !empty($post['kopf_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("B");
				$cust->course_title = Pms_CommonData::aesEncrypt("Kopf: " . htmlspecialchars(addslashes($post['kopf_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['thorax'] == 2 && !empty($post['thorax_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("B");
				$cust->course_title = Pms_CommonData::aesEncrypt("Thorax: " . htmlspecialchars(addslashes($post['thorax_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['abdomen'] == 2 && !empty($post['abdomen_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("B");
				$cust->course_title = Pms_CommonData::aesEncrypt("Abdomen: " . htmlspecialchars(addslashes($post['abdomen_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['extremitaten'] == 2 && !empty($post['extremitaten_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("B");
				$cust->course_title = Pms_CommonData::aesEncrypt("Extremitaten: " . htmlspecialchars(addslashes($post['extremitaten_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['haut_wunden'] == 2 && !empty($post['haut_wunden_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("B");
				$cust->course_title = Pms_CommonData::aesEncrypt(" Haut/Wunden " . htmlspecialchars(addslashes($post['haut_wunden_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['neurologisch_psychiatrisch'] == 2 && !empty($post['neurologisch_psychiatrisch_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("B");
				$cust->course_title = Pms_CommonData::aesEncrypt("Neurologisch / Psychiatrisch: " . htmlspecialchars(addslashes($post['neurologisch_psychiatrisch_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}

			if(!empty($post['sonstiges']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Sonstiges / Kommentar:" . htmlspecialchars(addslashes($post['sonstiges'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}

			if(!empty($post['comment_apotheke']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("Q");
				$cust->course_title = Pms_CommonData::aesEncrypt("Kommentar Medikation / Pumpe / Apotheke:" . htmlspecialchars(addslashes($post['comment_apotheke'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if(!empty($post['case_history']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("A");
				$cust->course_title = Pms_CommonData::aesEncrypt("Anamnese: " . htmlspecialchars(addslashes($post['case_history'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}

			if(!empty($post['conversation_phonecall']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("G");
				$cust->course_title = Pms_CommonData::aesEncrypt("Gespräch / Telefonat :" . htmlspecialchars(addslashes($post['conversation_phonecall'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}


			if(!empty($post['global']))
			{
				$val = array("1" => "schlechter", "2" => "besser", "3" => "gleich");
				$global = "";
				foreach($post['global'] as $key => $value)
				{
					$global .= $val[$value] . ',';
				}
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Globale Einschätzung: " . substr($global, 0, -1));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}

			if(!empty($post['medizini_a']))
			{
				$val = array(1 => "wie geplant gelaufen", 2 => "werden fortgesetzt", 3 => "nicht wie geplant verlaufen");
				$medizini_a = $val[$post['medizini_a']];
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Medizinische Maßnahmen: " . $medizini_a);
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}

			/* ------------------------ SAPVFB3------------------------------------ */
			if(is_array($post['symptom']))
			{

				if(count($post['symptom']) > 0)
				{
					$sp = new Sapsymptom();
					$sp->ipid = Pms_CommonData::getIpid($decid);
					$sp->sapvalues = join(",", $post['symptom']);
					$sp->gesamt_zeit_in_minuten = trim($post['total_visit_duration']);

					if(!empty($post['fahrtstreke_km']))
					{
						$gesamt_fahrstrecke_in_km = trim(str_replace(" km", "", $post['fahrtstreke_km'])) * 2;
					}
					else
					{
						$gesamt_fahrstrecke_in_km = '';
					}
					$sp->gesamt_fahrstrecke_in_km = $gesamt_fahrstrecke_in_km;

					if($post['fahrtzeit'] != '--')
					{
						$davon_fahrtzeit = $post['fahrtzeit'] * 2;
					}
					else
					{
						$davon_fahrtzeit = '';
					}
					$sp->davon_fahrtzeit = $davon_fahrtzeit;

					// this is important
					$sp->visit_id = $result;
					$sp->visit_type = "bayern";
					$sp->save();

					$sapv_sym_id = $sp->id;


					$kuns = Doctrine_Core::getTable('Sapsymptom')->find($sapv_sym_id);
					$kunsarr = $kuns->toArray();
					if(count($kunsarr) > 0)
					{
						$the_visit_date = explode(".", $post['visit_date']);
						$begin_date_h = $post['begin_date_h'];
						$begin_date_m = $post['begin_date_m'];
						$kuns_up = Doctrine::getTable('Sapsymptom')->find($sapv_sym_id);
						$kuns_up->create_date = $the_visit_date[2] . "-" . $the_visit_date[1] . "-" . $the_visit_date[0] . ' ' . $begin_date_h . ':' . $begin_date_m . ":00";
						$kuns_up->save();
					}


					if($allow_e_entries)
					{
						for($i = 0; $i < count($post['comments_l']); $i++)
						{
							$cust = new PatientCourse();
							$cust->ipid = $ipid;
							$cust->course_date = date("Y-m-d H:i:s", time());
							$cust->course_type = Pms_CommonData::aesEncrypt("E");
							$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($post['comments_l'][$i]) . '');
							$cust->user_id = $userid;
							$cust->done_date = $done_date;
							$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
							$cust->done_id = $result;
							$cust->save();
						}
					}
				}
			}
			elseif(strlen($post['total_visit_time']) > 0 || strlen($post['fahrtstreke_km']) > 0 || strlen($post['fahrtzeit']) > 0)
			{
				$sp = new Sapsymptom();
				$sp->ipid = Pms_CommonData::getIpid($decid);
				$sp->gesamt_zeit_in_minuten = trim($post['total_visit_duration']);
				if(!empty($post['fahrtstreke_km']))
				{
					$gesamt_fahrstrecke_in_km = trim(str_replace(" km", "", $post['fahrtstreke_km'])) * 2;
				}
				else
				{
					$gesamt_fahrstrecke_in_km = '';
				}
				$sp->gesamt_fahrstrecke_in_km = $gesamt_fahrstrecke_in_km;

				if($post['fahrtzeit'] != '--')
				{
					$davon_fahrtzeit = $post['fahrtzeit'] * 2;
				}
				else
				{
					$davon_fahrtzeit = '';
				}
				$sp->davon_fahrtzeit = $davon_fahrtzeit;
				$sp->visit_id = $result;
				$sp->visit_type = "bayern";
				$sp->save();
				$sapv_sym_id = $sp->id;


				$kuns = Doctrine_Core::getTable('Sapsymptom')->find($sapv_sym_id);
				$kunsarr = $kuns->toArray();
				if(count($kunsarr) > 0)
				{
					$the_visit_date = explode(".", $post['visit_date']);
					$begin_date_h = $post['begin_date_h'];
					$begin_date_m = $post['begin_date_m'];
					$kuns_up = Doctrine::getTable('Sapsymptom')->find($sapv_sym_id);
					$kuns_up->create_date = $the_visit_date[2] . "-" . $the_visit_date[1] . "-" . $the_visit_date[0] . ' ' . $begin_date_h . ':' . $begin_date_m . ":00";
					$kuns_up->save();
				}
			}



			if($stmb->id > 0)
			{
				return $stmb->id;
			}
			else
			{
				return false;
			}
		}

		public function UpdateBayernDoctorVisit($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$bayernid = $_REQUEST['bayern_doc_id'];

			if(is_array($post['additional_users']) && sizeof($post['additional_users']) > 0)
			{
				$user = new User();
				$c_users = $user->getUserByClientid($clientid, 0, true);

				foreach($c_users as $k_c_users => $v_c_users)
				{
					$client_users[$v_c_users['id']] = $v_c_users;
				}

				foreach($post['additional_users'] as $id_aditional_user => $v_aditional_user)
				{
					if($v_aditional_user['value'] == '1')
					{
						$usr_details[] = $client_users[$id_aditional_user]['first_name'] . ' ' . $client_users[$id_aditional_user]['last_name'];
						$usr_ids[] = $client_users[$id_aditional_user]['id'];
					}
				}
			}
			$usr_ids = array_values(array_unique($usr_ids));
			$aditional_users = join(',', $usr_ids);


			$stmb = Doctrine::getTable('BayernDoctorVisit')->find($post['bayern_doc_id']);

			
			// validate visit date
			if(empty($post['visit_date']) || !Pms_Validation::isdate($post['visit_date']) ){
			    $post['visit_date'] = date('d.m.Y');
			}
			/* -----------------VISIT START DATE AND END DATE ------- */
			$visit_date = explode(".", $post['visit_date']);

			$stmb->start_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
			$stmb->end_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $post['end_date_h'] . ':' . $post['end_date_m'] . ':00'));

			$stmb->begin_date_h = $post['begin_date_h'];
			$stmb->begin_date_m = $post['begin_date_m'];
			$stmb->end_date_h = $post['end_date_h'];
			$stmb->end_date_m = $post['end_date_m'];
			$stmb->visit_date = $visit_date[2] . "-" . $visit_date[1] . "-" . $visit_date[0] . ' ' . date("H") . ':' . date("i") . ":00";
			/* ------------------------------------------------------ */
			$stmb->documantation_time = $post['documantation_time'];
			$stmb->fahrtzeit = $post['fahrtzeit'];
			$stmb->fahrtstreke_km = $post['fahrtstreke_km'];

			$stmb->peg = $post['peg'];
			$stmb->peg_text = $post['peg_text'];
			$stmb->port = $post['port'];
			$stmb->port_text = $post['port_text'];
			$stmb->pumps = $post['pumps'];
			$stmb->pumps_text = $post['pumps_text'];
			$stmb->dk = $post['dk'];
			$stmb->dk_text = $post['dk_text'];
			$stmb->kunstliche = $post['kunstliche'];
			$stmb->kunstliche_text = $post['kunstliche_text'];
			//newly added 21.05.2012
			$stmb->darm = $post['darm'];
			$stmb->darm_text = $post['darm_text'];
			$stmb->blase = $post['blase'];
			$stmb->blase_text = $post['blase_text'];
			$stmb->luftrohre = $post['luftrohre'];
			$stmb->luftrohre_text = $post['luftrohre_text'];
			$stmb->ablaufsonde = $post['ablaufsonde'];
			$stmb->ablaufsonde_text = $post['ablaufsonde_text'];



			$stmb->kopf = $post['kopf'];
			$stmb->kopf_text = htmlspecialchars($post['kopf_text']);
			$stmb->thorax = $post['thorax'];
			$stmb->thorax_text = htmlspecialchars($post['thorax_text']);
			$stmb->abdomen = $post['abdomen'];
			$stmb->abdomen_text = htmlspecialchars($post['abdomen_text']);
			$stmb->extremitaten = $post['extremitaten'];
			$stmb->extremitaten_text = htmlspecialchars($post['extremitaten_text']);
			$stmb->haut_wunden = $post['haut_wunden'];
			$stmb->haut_wunden_text = htmlspecialchars($post['haut_wunden_text']);
			$stmb->neurologisch_psychiatrisch = $post['neurologisch_psychiatrisch'];
			$stmb->neurologisch_psychiatrisch_text = htmlspecialchars($post['neurologisch_psychiatrisch_text']);
			$stmb->ecog = join(",", $post['ecog']);
			$stmb->sonstiges = htmlspecialchars($post['sonstiges']);
			$stmb->comment_apotheke = htmlspecialchars($post['comment_apotheke']);
			$stmb->global = join(",", $post['global']);
			$stmb->related_users = $aditional_users;
			$stmb->case_history = htmlspecialchars($post['case_history']);
			$stmb->conversation_phonecall = htmlspecialchars($post['conversation_phonecall']);

			$stmb->save();

			$done_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));


			if(is_array($post['input_value']) && sizeof($post['input_value']) > 0)
			{
				$a_post = $post;
				$a_post['ipid'] = $ipid;
				$save_symp = 0;
				foreach($a_post['input_value'] as $val)
				{
					if(strlen($val) > 0)
					{
						$save_symp = 1;
					}
				}
				if($save_symp == 1)
				{


					//delete all patient symptomatology for current form
					$upd_sym = Doctrine_Query::create()
						->delete('Symptomatology')
						->where('kvnoid = "d' . $bayernid . '"');
					$upd_sym->execute();

					$upd_bayernsym = Doctrine_Query::create()
						->delete('BayernDoctorSymp')
						->where('bdf_id = "' . $bayernid . '"');
					$upd_bayernsym->execute();

					//re-add all patient symptomatology for current form
					$patient_form = new Application_Form_PatientSymptomatology();
					$a_post['iskvno'] = 1;
					$a_post['kvnoid'] = 'd' . $stmb->id; //"d" is for doctor
					$a_post['edit_entry_date'] = $stmb->create_date;
					$patient_form->InsertData($a_post);


					$current_values = $post['current_value'];
					$comments = $post['comment'];
					foreach($post['input_value'] as $symp_id => $val)
					{
						if(strlen($val) > 0)
						{
							$sympvals = new BayernDoctorSymp();
							$sympvals->bdf_id = $stmb->id;
							$sympvals->ipid = $ipid;
							$sympvals->symp_id = $symp_id;
							$sympvals->last_value = ($current_values[$symp_id] == '' ? NULL : $current_values[$symp_id]);
							$sympvals->current_value = ($val == '' ? NULL : $val);
							$sympvals->comment = htmlspecialchars($comments[$symp_id]);
							$sympvals->save();
						}
					}
				}
			}

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");
			$cust->recordid = $post['bayern_doc_id'];
			$cust->user_id = $userid;
			$cust->save();

			$qa = Doctrine_Query::create()
				->update('PatientCourse')
				->set('done_date', "'" . $done_date . "'")
				->where('done_name = AES_ENCRYPT("bayern_doctorvisit", "' . Zend_Registry::get('salt') . '")')
				->andWhere('done_id = "' . $bayernid . '"')
				->andWhere('ipid LIKE "' . $ipid . '"');
			$qa->execute();

			if(!empty($usr_details))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Beteiligte Mitarbeiter: " . implode('; ', $usr_details));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt("bayern_doctorvisit");
				$cust->done_id = $result;
				$cust->save();
			}
		}

	}

?>