<?php

	require_once("Pms/Form.php");

	class Application_Form_KvnoNurse extends Pms_Form {

		public function insertKvnoNurse($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$stmb = new KvnoNurse();
			$stmb->ipid = $ipid;

			// validate visit date
			if(empty($post['vizit_date']) || !Pms_Validation::isdate($post['vizit_date']) ){
			    $post['vizit_date'] = date('d.m.Y');
			}
			
			if(empty($post['kvno_begin_date_h']) || strlen($post['kvno_begin_date_h']) == 0){
			    $post['kvno_begin_date_h'] = date('H', strtotime('-5 minutes'));
			}
			
			if(empty($post['kvno_begin_date_m']) || strlen($post['kvno_begin_date_m']) == 0){
			    $post['kvno_begin_date_m'] = date('i', strtotime('-5 minutes'));
			}
			
			if(empty($post['kvno_end_date_h']) || strlen($post['kvno_end_date_h']) == 0){
			    $post['kvno_end_date_h'] = date('H', strtotime('+10 minutes'));
			}
			
			if(empty($post['kvno_end_date_m']) || strlen($post['kvno_end_date_m']) == 0){
			    $post['kvno_end_date_m'] = date('i', strtotime('+10 minutes'));
			}
			
			
			/* -----------------VISIT START DATE AND END DATE ------- */
			$stmb->start_date = date('Y-m-d H:i:s', strtotime($post['vizit_date'] . ' ' . $post['kvno_begin_date_h'] . ':' . $post['kvno_begin_date_m'] . ':00'));
			$stmb->end_date = date('Y-m-d H:i:s', strtotime($post['vizit_date'] . ' ' . $post['kvno_end_date_h'] . ':' . $post['kvno_end_date_m'] . ':00'));

			$vizit_date = explode(".", $post['vizit_date']);
			$stmb->kvno_begin_date_h = $post['kvno_begin_date_h'];
			$stmb->kvno_begin_date_m = $post['kvno_begin_date_m'];
			$stmb->kvno_end_date_h = $post['kvno_end_date_h'];
			$stmb->kvno_end_date_m = $post['kvno_end_date_m'];
			$stmb->vizit_date = $vizit_date[2] . "-" . $vizit_date[1] . "-" . $vizit_date[0];
			/* -------------------------------------------------------- */

			$stmb->quality = $post['quality'];
			$stmb->fahrtzeit = $post['fahrtzeit'];
			$stmb->fahrtstreke_km = $post['fahrtstreke_km'];

			$stmb->kvno_peg = $post['kvno_peg'];
			$stmb->kvno_peg_text = $post['kvno_peg_text'];
			$stmb->kvno_port = $post['kvno_port'];
			$stmb->kvno_port_text = $post['kvno_port_text'];
			$stmb->kvno_pumps = $post['kvno_pumps'];
			$stmb->kvno_pumps_text = $post['kvno_pumps_text'];
			$stmb->kvno_dk = $post['kvno_dk'];
			$stmb->kvno_dk_text = $post['kvno_dk_text'];
			$stmb->kvno_kunstliche = $post['kvno_kunstliche'];
			$stmb->kvno_kunstliche_text = $post['kvno_kunstliche_text'];
			//newly added 21.05.2012
			$stmb->kvno_darm = $post['kvno_darm'];
			$stmb->kvno_darm_text = $post['kvno_darm_text'];
			$stmb->kvno_blase = $post['kvno_blase'];
			$stmb->kvno_blase_text = $post['kvno_blase_text'];
			$stmb->kvno_luftrohre = $post['kvno_luftrohre'];
			$stmb->kvno_luftrohre_text = $post['kvno_luftrohre_text'];
			$stmb->kvno_ablaufsonde = $post['kvno_ablaufsonde'];
			$stmb->kvno_ablaufsonde_text = $post['kvno_ablaufsonde_text'];
			$stmb->kvno_fotodocumentation = htmlspecialchars($post['kvno_fotodocumentation']);
			$stmb->kvno_sonstiges = htmlspecialchars($post['kvno_sonstiges']);
			$stmb->comment_apotheke = htmlspecialchars($post['comment_apotheke']);
			$stmb->kvno_global = join(",", $post['kvno_global']);
			$stmb->kvno_medizini1 = $post['kvno_medizini1'];
			$stmb->kvno_medizini2 = join(",", $post['kvno_medizini2']);
			$stmb->sub_user = $post['sub_user'];
			$stmb->added_from = $post['added_from'];

			$stmb->save();

			$result = $stmb->id;

			$done_date = date('Y-m-d H:i:s', strtotime($post['vizit_date'] . ' ' . $post['kvno_begin_date_h'] . ':' . $post['kvno_begin_date_m'] . ':' . date('s', time())));
			if($post['leverkusen_visit'] == '1')
			{
				$tab_name = "lvn_nurse_form";
			}
			else
			{
				$tab_name = "kvno_nurse_form";
			}

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
					$patient_form = new Application_Form_PatientSymptomatology();
					$a_post['iskvno'] = 1;
					$a_post['kvnoid'] = 'n' . $result;
					$a_post['edit_entry_date'] = $done_date;
					$patient_form->InsertData($a_post);
				}

				$current_values = $post['current_value'];
				$comments = $post['comment'];
				foreach($post['input_value'] as $symp_id => $val)
				{
					if(strlen($val) > 0)
					{
						$sympvals = new KvnoNurseSymp();
						$sympvals->kdf_id = $result;
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

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("F");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
			$cust->recordid = $result;
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $result;
			$cust->save();

			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("K");
			$cust->course_title = Pms_CommonData::aesEncrypt($post['kvno_begin_date_h'] . ":" . $post['kvno_begin_date_m'] . ' - ' . $post['kvno_end_date_h'] . ':' . $post['kvno_end_date_m'] . ' ' . $post['vizit_date']);
			$cust->user_id = $userid;
			$cust->done_date = $done_date;
			$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
			$cust->done_id = $result;
			$cust->save();


			if(!empty($post['fahrtzeit']) & $post['fahrtzeit'] != "--")
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Fahrtzeit: " . $post['fahrtzeit']);
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
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
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}

			if($post['kvno_port'] == "2" && !empty($post['kvno_port_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Port - nicht ok : " . htmlspecialchars(addslashes($post['kvno_port_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kvno_kunstliche'] == "2" && !empty($post['kvno_kunstliche_text']))
			{
				if(!empty($post['kunstlichemore']))
				{
					$kunstlichemore = "(" . $post['kunstlichemore'] . ") ";
				}
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("besonderer Aus-/ Eingang " . $kunstlichemore . "- nicht ok : " . htmlspecialchars(addslashes($post['kvno_kunstliche_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kvno_pumps'] == "2" && !empty($post['kvno_pumps_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Pumpe - nicht ok : " . htmlspecialchars(addslashes($post['kvno_pumps_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kvno_dk'] == "2" && !empty($post['kvno_dk_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Dauerkatheter - nicht ok : " . htmlspecialchars(addslashes($post['kvno_dk_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kvno_peg'] == "2" && !empty($post['kvno_peg_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("PEG - nicht ok : " . htmlspecialchars(addslashes($post['kvno_peg_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kvno_darm'] == "2" && !empty($post['kvno_darm_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Darm - nicht ok : " . htmlspecialchars(addslashes($post['kvno_darm_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kvno_blase'] == "2" && !empty($post['kvno_blase_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Blase - nicht ok : " . htmlspecialchars(addslashes($post['kvno_blase_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kvno_luftrohre'] == "2" && !empty($post['kvno_luftrohre_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Luftröhre - nicht ok : " . htmlspecialchars(addslashes($post['kvno_luftrohre_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if($post['kvno_ablaufsonde'] == "2" && !empty($post['kvno_ablaufsonde_text']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Ablaufsonde  - nicht ok : " . htmlspecialchars(addslashes($post['kvno_ablaufsonde_text'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}


			if(!empty($post['kvno_sonstiges']))
			{
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Sonstiges / Kommentar:" . htmlspecialchars(addslashes($post['kvno_sonstiges'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
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
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}

			if(!empty($post['kvno_global']))
			{
				$val = array("1" => "schlechter", "2" => "besser", "3" => "gleich");
				$global_values = "";
				foreach($post['kvno_global'] as $key => $value)
				{
					$global_values .= $val[$value] . ',';
				}
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Globale Einschätzung: " . substr($global_values, 0, -1));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}
			if(!empty($post['kvno_medizini1']))
			{
				$val = array(1 => "wie geplant gelaufen", 2 => "werden fortgesetzt", 3 => "nicht wie geplant verlaufen");
				$kvno_medizini1 = $val[$post['kvno_medizini1']];
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Medizinische Maßnahmen: " . $kvno_medizini1);
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}



			if($post['sub_user'] != '0' && !empty($post['sub_user']))
			{
				$sub_users_array = Pms_CommonData::getSubUsers($clientid, $userid);
				$sub_user_name = $sub_users_array[$post['sub_user']]['name'];

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt("Benutzer: " . htmlspecialchars(addslashes($sub_user_name)));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();
			}

			if($stmb->id > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function UpdateKvnoNurse($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);


			$stmb = Doctrine::getTable('KvnoNurse')->find($post['kvno_nurse_id']);
			
			/* -----------------VISIT START DATE AND END DATE ------- */
			$stmb->start_date = date('Y-m-d H:i:s', strtotime($post['vizit_date'] . ' ' . $post['kvno_begin_date_h'] . ':' . $post['kvno_begin_date_m'] . ':00'));
			$stmb->end_date = date('Y-m-d H:i:s', strtotime($post['vizit_date'] . ' ' . $post['kvno_end_date_h'] . ':' . $post['kvno_end_date_m'] . ':00'));

			$vizit_date = explode(".", $post['vizit_date']);
			$stmb->kvno_begin_date_h = $post['kvno_begin_date_h'];
			$stmb->kvno_begin_date_m = $post['kvno_begin_date_m'];
			$stmb->kvno_end_date_h = $post['kvno_end_date_h'];
			$stmb->kvno_end_date_m = $post['kvno_end_date_m'];
			$stmb->vizit_date = $vizit_date[2] . "-" . $vizit_date[1] . "-" . $vizit_date[0];
			/* -------------------------------------------------------- */

			$stmb->quality = $post['quality'];
			$stmb->fahrtzeit = $post['fahrtzeit'];
			$stmb->fahrtstreke_km = $post['fahrtstreke_km'];

			$stmb->kvno_peg = $post['kvno_peg'];
			$stmb->kvno_peg_text = $post['kvno_peg_text'];
			$stmb->kvno_port = $post['kvno_port'];
			$stmb->kvno_port_text = $post['kvno_port_text'];
			$stmb->kvno_pumps = $post['kvno_pumps'];
			$stmb->kvno_pumps_text = $post['kvno_pumps_text'];
			$stmb->kvno_dk = $post['kvno_dk'];
			$stmb->kvno_dk_text = $post['kvno_dk_text'];
			$stmb->kvno_kunstliche = $post['kvno_kunstliche'];
			$stmb->kvno_kunstliche_text = $post['kvno_kunstliche_text'];
			//newly added 21.05.2012
			$stmb->kvno_darm = $post['kvno_darm'];
			$stmb->kvno_darm_text = $post['kvno_darm_text'];
			$stmb->kvno_blase = $post['kvno_blase'];
			$stmb->kvno_blase_text = $post['kvno_blase_text'];
			$stmb->kvno_luftrohre = $post['kvno_luftrohre'];
			$stmb->kvno_luftrohre_text = $post['kvno_luftrohre_text'];
			$stmb->kvno_ablaufsonde = $post['kvno_ablaufsonde'];
			$stmb->kvno_ablaufsonde_text = $post['kvno_ablaufsonde_text'];

			$stmb->kvno_fotodocumentation = htmlspecialchars($post['kvno_fotodocumentation']);
			$stmb->kvno_sonstiges = htmlspecialchars($post['kvno_sonstiges']);
			$stmb->comment_apotheke = htmlspecialchars($post['comment_apotheke']);
			$stmb->kvno_global = join(",", $post['kvno_global']);
			$stmb->kvno_medizini1 = $post['kvno_medizini1'];
			$stmb->kvno_medizini2 = join(",", $post['kvno_medizini2']);
			$stmb->sub_user = $post['sub_user'];


			$stmb->save();
			$done_date = date('Y-m-d H:i:s', strtotime($post['vizit_date'] . ' ' . $post['kvno_begin_date_h'] . ':' . $post['kvno_begin_date_m'] . ':00'));
			if($post['leverkusen_visit'] == '1')
			{
				$tab_name = "lvn_nurse_form";
			}
			else
			{
				$tab_name = "kvno_nurse_form";
			}

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
						->where('kvnoid = "d' . $kvnoid . '"');
					$upd_sym->execute();

					$upd_kvnosym = Doctrine_Query::create()
						->delete('KvnoNurseSymp')
						->where('kdf_id = "' . $kvnoid . '"');
					$upd_kvnosym->execute();

					//re-add all patient symptomatology for current form

					$patient_form = new Application_Form_PatientSymptomatology();
					$a_post['iskvno'] = 1;
					$a_post['kvnoid'] = 'n' . $stmb->id; // "n" is for nurse
					$a_post['edit_entry_date'] = $stmb->create_date;
					$patient_form->InsertData($a_post);


					$current_values = $post['current_value'];
					$comments = $post['comment'];
					foreach($post['input_value'] as $symp_id => $val)
					{
						if(strlen($val) > 0)
						{
							$sympvals = new KvnoNurseSymp();
							$sympvals->kdf_id = $stmb->id;
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
			$cust->recordid = $post['kvno_nurse_id'];
			$cust->user_id = $userid;
			$cust->save();

			$qa = Doctrine_Query::create()
				->update('PatientCourse')
				->set('done_date', "'" . $done_date . "'")
				->where('done_name = AES_ENCRYPT("' . $tab_name . '", "' . Zend_Registry::get('salt') . '")')
				->andWhere('done_id = "' . $post['kvno_nurse_id'] . '"')
				->andWhere('ipid LIKE "' . $ipid . '"');
			$qa->execute();
		}

		public function insertMultipleKvnoNurse($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$post_vizit_date = explode(".", $post['visit_date']);
			$vizit_date = $post_vizit_date [2] . "-" . $post_vizit_date [1] . "-" . $post_vizit_date [0];


			foreach($post['visit'] as $kh => $visit_values)
			{

				if(!empty($visit_values['start']) && !empty($visit_values['end']))
				{

					$start_time = explode(":", $visit_values['start']);
					$end_time = explode(":", $visit_values['end']);

					$stmb = new KvnoNurse();
					$stmb->ipid = $ipid;

					/* -----------------VISIT START DATE AND END DATE ------- */
					$stmb->start_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $start_time[0] . ':' . $start_time[1] . ':00'));
					$stmb->end_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $end_time[0] . ':' . $end_time[1] . ':00'));

					$stmb->kvno_begin_date_h = $start_time[0];
					$stmb->kvno_begin_date_m = $start_time[1];
					$stmb->kvno_end_date_h = $end_time[0];
					$stmb->kvno_end_date_m = $end_time[1];
					$stmb->vizit_date = $vizit_date;
					/* -------------------------------------------------- ------- */



					$stmb->added_from = $post['added_from'];
					$stmb->save();

					$result = $stmb->id;

					$done_date = date('Y-m-d H:i:s', strtotime($post['visit_date'] . ' ' . $start_time[0] . ':' . $start_time[1] . ':' . date('s', time())));

					if($post['leverkusen_visit'] == '1')
					{
						$tab_name = "lvn_nurse_form";
					}
					else
					{
						$tab_name = "kvno_nurse_form";
					}


					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("F");
					$cust->course_title = Pms_CommonData::aesEncrypt($comment);
					$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
					$cust->recordid = $result;
					$cust->user_id = $userid;
					$cust->done_date = $done_date;
					$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
					$cust->done_id = $result;
					$cust->save();

					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt($start_time[0] . ":" . $start_time[1] . ' - ' . $end_time[0] . ':' . $end_time[1] . ' ' . $post['visit_date']);
					$cust->user_id = $userid;
					$cust->done_date = $done_date;
					$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
					$cust->done_id = $result;
					$cust->save();


					$all_visits[] = $result;
				}
			}


			if(count($all_visits) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function insert_multiple_nurse_visits($post, $added_from)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			foreach($post as $key => $visit)
			{
				$start_time = explode(":", $visit['start_time']);
				$end_time = explode(":", $visit['end_time']);

				$stmb = new KvnoNurse();
				$stmb->ipid = $ipid;
				/* -----------------VISIT START DATE AND END DATE ------- */
				$stmb->start_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['start_time'] . ':00'));
				$stmb->end_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['end_time'] . ':00'));

				$stmb->kvno_begin_date_h = $start_time[0];
				$stmb->kvno_begin_date_m = $start_time[1];
				$stmb->kvno_end_date_h = $end_time[0];
				$stmb->kvno_end_date_m = $end_time[1];
				$stmb->vizit_date = $visit['start_date'];
				/* -------------------------------------------------- ------- */
				$stmb->added_from = $added_from;
				$stmb->save();
				$result = $stmb->id;

				$done_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['start_time'] . ':00'));
				$tab_name = "kvno_nurse_form";

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("F");
				$cust->course_title = Pms_CommonData::aesEncrypt($comment);
				$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
				$cust->recordid = $result;
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("K");
				$cust->course_title = Pms_CommonData::aesEncrypt($visit['start_time'] . ' - ' . $visit['end_time'] . ' ' . date('d.m.Y', strtotime($visit['start_date'])));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = Pms_CommonData::aesEncrypt($tab_name);
				$cust->done_id = $result;
				$cust->save();

				$all_visits[] = $result;
			}


			if(count($all_visits) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function update_multiple_nurse_visits($post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			foreach($post as $key => $visit)
			{
				$start_time = explode(":", $visit['start_time']);
				$end_time = explode(":", $visit['end_time']);

				if($visit['visit_added_from'] != 'contact_form')
				{
					$stmb = Doctrine::getTable('KvnoNurse')->find($visit['id']);
					/* -----------------VISIT START DATE AND END DATE ------- */
					$stmb->start_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['start_time'] . ':00'));
					$stmb->end_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['end_time'] . ':00'));

					$stmb->kvno_begin_date_h = $start_time[0];
					$stmb->kvno_begin_date_m = $start_time[1];
					$stmb->kvno_end_date_h = $end_time[0];
					$stmb->kvno_end_date_m = $end_time[1];
					$stmb->vizit_date = $visit['start_date'];
					/* -------------------------------------------------- ------- */
					$stmb->save();
					$result = $stmb->id;

					$done_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['start_time'] . ':00'));
					$tab_name = "kvno_nurse_form";


					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("K");
					$cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");
					$cust->recordid = $visit['id'];
					$cust->user_id = $userid;
					$cust->save();

					$qa = Doctrine_Query::create()
						->update('PatientCourse')
						->set('done_date', "'" . $done_date . "'")
						->where('done_name = AES_ENCRYPT("' . $tab_name . '", "' . Zend_Registry::get('salt') . '")')
						->andWhere('done_id = "' . $visit['id'] . '"')
						->andWhere('ipid LIKE "' . $ipid . '"');
					$qa->execute();
				}
				else
				{
					$cf_update = Doctrine::getTable('ContactForms')->find($visit['id']);

					$cf_update->start_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['start_time'] . ':00'));
					$cf_update->end_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['end_time'] . ':00'));

					$cf_update->begin_date_h = $start_time[0];
					$cf_update->begin_date_m = $start_time[1];
					$cf_update->end_date_h = $end_time[0];
					$cf_update->end_date_m = $end_time[1];
					$cf_update->date = $visit['start_date'];
					$cf_update->save();

					$done_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['old_start_time'] . ':00'));
					$tab_name = "contact_form";

					$cust = new PatientCourse();
					$cust->ipid = $ipid;
					$cust->course_date = date("Y-m-d H:i:s", time());
					$cust->course_type = Pms_CommonData::aesEncrypt("F");
					$cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");
					$cust->user_id = $userid;
					$cust->done_date = $done_date;
					$cust->done_id = $visit['id'];
					$cust->recordid = $visit['id'];
					$cust->tabname = Pms_CommonData::aesEncrypt($tab_name);
					$cust->save();
					
					if($visit['old_start_time'] != $visit['start_time'])
					{
						$cust = new PatientCourse();
						$cust->ipid = $ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt('Beginn: ' . $visit['old_start_time'] . ' --> ' . $visit['start_time']);
						$cust->user_id = $userid;
						$cust->done_date = $done_date;
						$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
						$cust->done_id = $visit['id'];
						$cust->save();
					}
					
					if($visit['old_end_time'] != $visit['end_time'])
					{
						$cust = new PatientCourse();
						$cust->ipid = $ipid;
						$cust->course_date = date("Y-m-d H:i:s", time());
						$cust->course_type = Pms_CommonData::aesEncrypt("K");
						$cust->course_title = Pms_CommonData::aesEncrypt('Ende: ' . $visit['old_end_time'] . ' --> ' . $visit['end_time']);
						$cust->user_id = $userid;
						$cust->done_date = $done_date;
						$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
						$cust->done_id = $visit['id'];
						$cust->save();
					}
				}
			}
		}

//		public function update_multiple_nurse_visits_old($post, $added_from)
//		{
//			$logininfo = new Zend_Session_Namespace('Login_Info');
//			$clientid = $logininfo->clientid;
//			$userid = $logininfo->userid;
//
//			$decid = Pms_Uuid::decrypt($_GET['id']);
//			$ipid = Pms_CommonData::getIpid($decid);
//
//			foreach($post as $key => $visit)
//			{
//				$start_time = explode(":", $visit['start_time']);
//				$end_time = explode(":", $visit['end_time']);
//
//				$stmb = Doctrine::getTable('KvnoNurse')->find($visit['id']);
//				/* -----------------VISIT START DATE AND END DATE ------- */
//				$stmb->start_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['start_time'] . ':00'));
//				$stmb->end_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['end_time'] . ':00'));
//
//				$stmb->kvno_begin_date_h = $start_time[0];
//				$stmb->kvno_begin_date_m = $start_time[1];
//				$stmb->kvno_end_date_h = $end_time[0];
//				$stmb->kvno_end_date_m = $end_time[1];
//				$stmb->vizit_date = $visit['start_date'];
//				/* -------------------------------------------------- ------- */
//				$stmb->save();
//				$result = $stmb->id;
//
//				$done_date = date('Y-m-d H:i:s', strtotime($visit['start_date'] . ' ' . $visit['start_time'] . ':00'));
//				$tab_name = "kvno_nurse_form";
//
//
//				$cust = new PatientCourse();
//				$cust->ipid = $ipid;
//				$cust->course_date = date("Y-m-d H:i:s", time());
//				$cust->course_type = Pms_CommonData::aesEncrypt("K");
//				$cust->course_title = Pms_CommonData::aesEncrypt("Besuch vom " . date('d.m.Y H:i', strtotime($done_date)) . " wurde editiert");
//				$cust->recordid = $visit['id'];
//				$cust->user_id = $userid;
//				$cust->save();
//
//				$qa = Doctrine_Query::create()
//					->update('PatientCourse')
//					->set('done_date', "'" . $done_date . "'")
//					->where('done_name = AES_ENCRYPT("' . $tab_name . '", "' . Zend_Registry::get('salt') . '")')
//					->andWhere('done_id = "' . $visit['id'] . '"')
//					->andWhere('ipid LIKE "' . $ipid . '"');
//				$qa->execute();
//			}
//		}
	}

?>