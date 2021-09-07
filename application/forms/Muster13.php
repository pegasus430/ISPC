<?php

	require_once("Pms/Form.php");

	class Application_Form_Muster13 extends Pms_Form {

		public function insert_data($ipid, $post)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$diagnosis_name1 = '';
			$diagnosis_name2 = '';
			if(strlen(trim(rtrim($post['icd_diagnosis_1']))) > '0')
			{
				$diagnosis_name1 = trim(rtrim($post['icd_diagnosis_1']));
			}

			if(strlen(trim(rtrim($post['icd_diagnosis_2']))) > '0')
			{
				$diagnosis_name2 = trim(rtrim($post['icd_diagnosis_2']));
			}
			
			$verordnung_radio = '';
			if($post['verordnung_radio'][0] != null)
			{
				$verordnung_radio .= $post['verordnung_radio'][0].',';
			}
			else 
			{
				$verordnung_radio .= ',';
			}
			if($post['verordnung_radio'][1] != null)
			{
				$verordnung_radio .= $post['verordnung_radio'][1].',';
			}
			else 
			{
				$verordnung_radio .= ',';
			}
			if($post['verordnung_radio'][2] != null)
			{
				$verordnung_radio .= $post['verordnung_radio'][2].',';
			}
			else 
			{
				$verordnung_radio .= ',';
			}
			if($post['verordnung_radio'][3] != null)
			{
				$verordnung_radio .= $post['verordnung_radio'][3];
			}
			else 
			{
				$verordnung_radio .= ',';
			}
			
			$gebuhr_radio = '';
			if($post['gebuhr_radio'][0] != null)
			{
				$gebuhr_radio .= $post['gebuhr_radio'][0].',';
			}
			else 
			{
				$gebuhr_radio .= ',';
			}
			if($post['gebuhr_radio'][1] != null)
			{
				$gebuhr_radio .= $post['gebuhr_radio'][1];
			}
			else 
			{
				$gebuhr_radio .= ',';
			}
			
			if($post['birthd'] != '')
			{
				$birthd = date('Y-m-d', strtotime($post['birthd']));
			}
			else 
			{
				$birthd = '0000-00-00';
			}
			
			if($post['insurance_datum'] != '')
			{
				$datum = date('Y-m-d', strtotime($post['insurance_datum']));
			}
			else
			{
				$datum = '0000-00-00';
			}
			
			$insert = new Muster13();
			
			$insert->ipid = $ipid;
			$insert->client = $clientid;			
			$insert->client_ik_number = $post['client_ik_number'];
			
			$insert->insurance_name = $post['insurance_com_name'];
			$insert->patient_name = $post['patient_name'];
			$insert->street = $post['street'];
			$insert->zipcode = $post['zip'];
			$insert->city = $post['city'];
			$insert->birthdate = $birthd;
			$insert->ins_kassenno = $post['kassen_no'];
			$insert->ins_insuranceno = $post['insurance_number'];
			$insert->ins_status = $post['insurance_stat'];
			
			$insert->bsnr = $post['betriebsstatten_nr'];
			$insert->lanr = $post['lanr'];
			$insert->datum = $datum;
			
			$insert->gesamt_zuzahlung = $post['gesamt_zuzahlung'];
			$insert->gesamt_brutto = $post['gesamt_brutto'];
			$insert->heilmittel_pos_1 = $post['heilmittel_pos_1'];
			$insert->faktor_1 = $post['faktor_1'];
			$insert->heilmittel_pos_2 = $post['heilmittel_pos_2'];
			$insert->faktor_2 = $post['faktor_2'];
			$insert->wegegeld = $post['wegegeld'];
			$insert->faktor_3 = $post['faktor_3'];
			$insert->km = $post['km'];
			$insert->hausbesuch_1 = $post['hausbesuch_1'];
			$insert->faktor_4 = $post['faktor_4'];
			$insert->hausbesuch_2 = $post['hausbesuch_2'];
			$insert->faktor_5 = $post['faktor_5'];
			$insert->rechnungsnummer = $post['rechnungsnummer'];
			$insert->belegnummer = $post['belegnummer'];
			$insert->verordnungs_menge_1 = $post['verordnungs_menge_1'];
			$insert->heilmittel_1 = $post['heilmittel_1'];
			$insert->anzahl_woche_1 = $post['anzahl_woche_1'];
			$insert->verordnungs_menge_2 = $post['verordnungs_menge_2'];
			$insert->heilmittel_2 = $post['heilmittel_2'];
			$insert->anzahl_woche_2 = $post['anzahl_woche_2'];
			$insert->indikation_key = $post['indikation_key'];
			$insert->indikation_name = $post['indikation_name'];
			
			$insert->icd_code1 = $post['icd_code1'];
			$insert->icd_diagnosis1 = $diagnosis_name1;
			
			$insert->icd_code2 = $post['icd_code2'];			
			$insert->icd_diagnosis2 = $diagnosis_name2;
			
//			$insert->icd_diagnosis = $post['icd_diagnosis_1'];

			$insert->gegebenenfalls_spezifizierung = $post['gegebenenfalls_spezifizierung_1'] . "\n" . $post['gegebenenfalls_spezifizierung_2'] . "\n" . $post['gegebenenfalls_spezifizierung_3'];
			$insert->medizinische_begrundung_verordnungen = $post['medizinische_begrundung_verordnungen_1'] . "\n" . $post['medizinische_begrundung_verordnungen_2'] . "\n" . $post['medizinische_begrundung_verordnungen_3'] . "\n" . $post['medizinische_begrundung_verordnungen_4'] . "\n" . $post['medizinische_begrundung_verordnungen_5'];
			$insert->verordnung_radio = $verordnung_radio;
			/* if(empty($post['behandlungsbeginn_date']))
			{
				$insert->behandlungsbeginn_date = date('Y-m-d', time());
			}
			else */
			
			if(!empty($post['behandlungsbeginn_date']))
			{
				//$behandlungsbeginn_date = $post['behandlungsbeginn_date'];
				//$date_ar = explode('.', $behandlungsbeginn_date);
				
				$date_ar = array();
				$date_ar[0] = substr($post['behandlungsbeginn_date'], 0, 2);
				$date_ar[1] = substr($post['behandlungsbeginn_date'], 2, 2);
				$date_ar[2] = substr($post['behandlungsbeginn_date'], 4, 2);
				
				if($date_ar[2] < 61)
				{
				if($date_ar[2] != '00')
					{
						$year = $date_ar[2] + 2000;
					}
					else 
					{
						$year = '0000';
					}
				}
				else
				{
					$year = $date_ar[2] + 1900;
				}
				$date_final = date('Y-m-d', strtotime($year . '-' . $date_ar[1] . '-' . $date_ar[0]));
				
				$insert->behandlungsbeginn_date = $date_final;
			}
			else 
			{
				$insert->behandlungsbeginn_date = '0000-00-00';
			}
			$insert->hausbesuch_radio = $post['hausbesuch_radio'];
			$insert->therapiebericht_radio = $post['therapiebericht_radio'];
			$insert->gebuhr_radio = $gebuhr_radio;
			$insert->unfall_radio = $post['unfall_radio'];
			$insert->stampuser = $post['stampuser'];
			$insert->stampid = $post['stampid'];
            //ISPC-2530, elena, 14.10.2020
			if($post['formvalid'] == '01102020'){
                $insert->verordnung_gruppe = $post['verordnung_gruppe'];
                $insert->formvalid = $post['formvalid'];
                $insert->heilmittel_3 = $post['heilmittel_3'];
                $insert->anzahl_woche_3 = $post['anzahl_woche_3'];
                $insert->heilmittel_4 = $post['heilmittel_4'];
                $insert->anzahl_woche_4 = $post['anzahl_woche_4'];
                $insert->diagnosis_freetext = $post['diagnosis_freetext'];
                $insert->diaggroup = $post['diaggroup'];
                //TODO-3735 Cristi.C $
                $insert->mainsymptomatic_letter = implode(',', $post['mainsymptomatic_letter']);             
                $insert->therapie_frequenz = $post['therapie_frequenz'];
                //
                $insert->mainsymptomatic_freetext = $post['mainsymptomatic_freetext'];
                $insert->dringlicher_behandlungsbedarf = $post['dringlicher_behandlungsbedarf'];
                $insert->therapieziele = $post['therapieziele'];
            }

			
			$insert->save();

			$id = $insert->id;

			if($id)
			{
				/*$comment = "Formular Muster 13 hinzugefügt.";
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("F");
				$cust->course_title = Pms_CommonData::aesEncrypt($comment);
				$cust->user_id = $logininfo->userid;
				$cust->done_name = Pms_CommonData::aesEncrypt('muster_13_form');
				$cust->tabname = Pms_CommonData::aesEncrypt('muster_13_form');
				$cust->done_date = date("Y-m-d H:i:s", time());
				$cust->done_id = $id;
				$cust->recordid = $id;
				$cust->save();*/
				
				//prepare log data
				$data['user'] = $userid;
				$data['muster13id'] = $id;
				$data['date'] = $insert->create_date;
				$data['operation'] = "created";
				//var_dump($data); exit;
				//save log
				$muster13_log = new Application_Form_Muster13Log();
				$write_muster13_log = $muster13_log->insert_muster13_log($ipid, $clientid, $data);
				
				return $id;
			}
			else
			{
				return false;
			}
		}

		public function update_data($post)
		{
//			print_r("Update post data\n");
//			print_r($post);
//			exit;
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);

			$diagnosis_name1 = '';
			$diagnosis_name2 = '';
			if(strlen(trim(rtrim($post['icd_diagnosis_1']))) > '0')
			{
				$diagnosis_name1 = trim(rtrim($post['icd_diagnosis_1']));
			}

			if(strlen(trim(rtrim($post['icd_diagnosis_2']))) > '0')
			{
				$diagnosis_name2 = trim(rtrim($post['icd_diagnosis_2']));
			}
			
			$verordnung_radio = '';
			if($post['verordnung_radio'][0] != null)
			{
				$verordnung_radio .= $post['verordnung_radio'][0].',';
			}
			else 
			{
				$verordnung_radio .= ',';
			}
			if($post['verordnung_radio'][1] != null)
			{
				$verordnung_radio .= $post['verordnung_radio'][1].',';
			}
			else 
			{
				$verordnung_radio .= ',';
			}
			if($post['verordnung_radio'][2] != null)
			{
				$verordnung_radio .= $post['verordnung_radio'][2].',';
			}
			else 
			{
				$verordnung_radio .= ',';
			}
			if($post['verordnung_radio'][3] != null)
			{
				$verordnung_radio .= $post['verordnung_radio'][3];
			}
			else 
			{
				$verordnung_radio .= ',';
			}
	
			$gebuhr_radio = '';
			
			if($post['gebuhr_radio'][0] != null)
			{
				$gebuhr_radio .= $post['gebuhr_radio'][0].',';
			}
			else 
			{
				$gebuhr_radio .= ',';
			}
			if($post['gebuhr_radio'][1] != null)
			{
				$gebuhr_radio .= $post['gebuhr_radio'][1];
			}
			else 
			{
				$gebuhr_radio .= ',';
			}
			
			if($post['birthd'] != '')
			{
				$birthd = date('Y-m-d', strtotime($post['birthd']));
			}
			else
			{
				$birthd = '0000-00-00';
			}
			
			if($post['insurance_datum'] != '')
			{
				$datum = date('Y-m-d', strtotime($post['insurance_datum']));
			}
			else
			{
				$datum = '0000-00-00';
			}

			$update = Doctrine::getTable('Muster13')->findOneById($post['saved_id']);
			
			$update->client = $clientid;
			$update->client_ik_number = $post['client_ik_number'];
			
			$update->insurance_name = $post['insurance_com_name'];
			$update->patient_name = $post['patient_name'];
			$update->street = $post['street'];
			$update->zipcode = $post['zip'];
			$update->city = $post['city'];
			$update->birthdate = $birthd;
			$update->ins_kassenno = $post['kassen_no'];
			$update->ins_insuranceno = $post['insurance_number'];
			$update->ins_status = $post['insurance_stat'];
			
			$update->bsnr = $post['betriebsstatten_nr'];
			$update->lanr = $post['lanr'];
			$update->datum = $datum;
			
			$update->gesamt_zuzahlung = $post['gesamt_zuzahlung'];
			$update->gesamt_brutto = $post['gesamt_brutto'];
			$update->heilmittel_pos_1 = $post['heilmittel_pos_1'];
			$update->faktor_1 = $post['faktor_1'];
			$update->heilmittel_pos_2 = $post['heilmittel_pos_2'];
			$update->faktor_2 = $post['faktor_2'];
			$update->wegegeld = $post['wegegeld'];
			$update->faktor_3 = $post['faktor_3'];
			$update->km = $post['km'];
			$update->hausbesuch_1 = $post['hausbesuch_1'];
			$update->faktor_4 = $post['faktor_4'];
			$update->hausbesuch_2 = $post['hausbesuch_2'];
			$update->faktor_5 = $post['faktor_5'];
			$update->rechnungsnummer = $post['rechnungsnummer'];
			$update->belegnummer = $post['belegnummer'];
			$update->verordnungs_menge_1 = $post['verordnungs_menge_1'];
			$update->heilmittel_1 = $post['heilmittel_1'];
			$update->anzahl_woche_1 = $post['anzahl_woche_1'];
			$update->verordnungs_menge_2 = $post['verordnungs_menge_2'];
			$update->heilmittel_2 = $post['heilmittel_2'];
			$update->anzahl_woche_2 = $post['anzahl_woche_2'];
			$update->indikation_key = $post['indikation_key'];
			$update->indikation_name = $post['indikation_name'];
			
			$update->icd_code1 = $post['icd_code1'];
			$update->icd_diagnosis1 = $diagnosis_name1;
			
			$update->icd_code2 = $post['icd_code2'];
			$update->icd_diagnosis2 = $diagnosis_name2;
//			$update->icd_diagnosis = $post['icd_diagnosis_1'];

			$update->gegebenenfalls_spezifizierung = $post['gegebenenfalls_spezifizierung_1'] . "\n" . $post['gegebenenfalls_spezifizierung_2'] . "\n" . $post['gegebenenfalls_spezifizierung_3'];
			$update->medizinische_begrundung_verordnungen = $post['medizinische_begrundung_verordnungen_1'] . "\n" . $post['medizinische_begrundung_verordnungen_2'] . "\n" . $post['medizinische_begrundung_verordnungen_3'] . "\n" . $post['medizinische_begrundung_verordnungen_4'] . "\n" . $post['medizinische_begrundung_verordnungen_5'];
			$update->verordnung_radio = $verordnung_radio;
			if(!empty($post['behandlungsbeginn_date']))
			{
				//$behandlungsbeginn_date = $post['behandlungsbeginn_date'];
				
				//$date_ar = explode('.', $behandlungsbeginn_date);
				$date_ar = array();
				$date_ar[0] = substr($post['behandlungsbeginn_date'], 0, 2);
				$date_ar[1] = substr($post['behandlungsbeginn_date'], 2, 2);
				$date_ar[2] = substr($post['behandlungsbeginn_date'], 4, 2);
	
			
				if($date_ar[2] < 61)
				{
					if($date_ar[2] != '00')
					{
						$year = $date_ar[2] + 2000;
					}
					else 
					{
						$year = '0000';
					}
				}
				else
				{
					$year = $date_ar[2] + 1900;
				}
				
				$date_final = date('Y-m-d', strtotime($year . '-' . $date_ar[1] . '-' . $date_ar[0]));
				
				$update->behandlungsbeginn_date = $date_final;
			}
			else 
			{
				$update->behandlungsbeginn_date = '0000-00-00';
			}
			$update->hausbesuch_radio = $post['hausbesuch_radio'];
			$update->therapiebericht_radio = $post['therapiebericht_radio'];
			$update->gebuhr_radio = $gebuhr_radio;
			$update->unfall_radio = $post['unfall_radio'];
			$update->stampuser = $post['stampuser'];
			$update->stampid = $post['stampid'];
            //ISPC-2530, elena, 14.10.2020
            if($post['formvalid'] == '01102020'){
                $update->verordnung_gruppe = $post['verordnung_gruppe'];
                $update->formvalid = $post['formvalid'];
                $update->heilmittel_3 = $post['heilmittel_3'];
                $update->anzahl_woche_3 = $post['anzahl_woche_3'];
                $update->heilmittel_4 = $post['heilmittel_4'];
                $update->anzahl_woche_4 = $post['anzahl_woche_4'];
                $update->diagnosis_freetext = $post['diagnosis_freetext'];
                $update->diaggroup = $post['diaggroup'];
                //TODO-3735 
                $update->mainsymptomatic_letter = implode(',', $post['mainsymptomatic_letter']);                                
                $update->therapie_frequenz = $post['therapie_frequenz'];                
                //
                $update->mainsymptomatic_freetext = $post['mainsymptomatic_freetext'];
                $update->dringlicher_behandlungsbedarf = $post['dringlicher_behandlungsbedarf'];
                $update->therapieziele = $post['therapieziele'];
            }
			
			$update->save();

			/*$comment = "Formular Muster 13 wurde editiert.";
			$cust = new PatientCourse();
			$cust->ipid = $ipid;
			$cust->course_date = date("Y-m-d H:i:s", time());
			$cust->course_type = Pms_CommonData::aesEncrypt("F");
			$cust->course_title = Pms_CommonData::aesEncrypt($comment);
			$cust->user_id = $logininfo->userid;
			$cust->done_name = Pms_CommonData::aesEncrypt('muster_13_form');
			$cust->tabname = Pms_CommonData::aesEncrypt('muster_13_form');
			$cust->done_date = date("Y-m-d H:i:s", time());
			$cust->done_id = $post['saved_id'];
			$cust->recordid = $post['saved_id'];
			$cust->save();*/
			
			$ms13_id = $update->id;
			
			if($ms13_id)
			{
				//prepare log data
				$data['user'] = $userid;
				$data['muster13id'] = $ms13_id;
				$data['date'] = date('Y-m-d H:i:s', time());
				$data['operation'] = "edited";
			
			
				//save log
				$muster13_log = new Application_Form_Muster13Log();
				$write_muster13_log = $muster13_log->insert_muster13_log($ipid, $clientid, $data);
			}
			
			
			return true;
		}
		
		public function delete_data($ms13_id, $ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			$delete = Doctrine::getTable('Muster13')->findOneById($ms13_id);
			$delete->isdelete = 1;
			$delete->save();
			
			//prepare log data
			$data['user'] = $userid;
			$data['muster13id'] = $ms13_id;
			$data['date'] = date('Y-m-d H:i:s', time());
			$data['operation'] = "deleted";
			
			//save log
			$muster13_log = new Application_Form_Muster13Log();
			$write_muster13_log = $muster13_log->insert_muster13_log($ipid, $clientid, $data);
			
		}
		
		public function duplicate_data($ipid, $ms13_id)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			
			$excluded_duplicate_keys = array('id', 'create_user', 'create_date', 'change_user', 'change_date');
			
			$todupl = new Muster13();
			$todupl_data = $todupl->get_muster13_patient_data($ipid, $ms13_id);
			//var_dump($todupl_data); exit;
			$duplicate = new Muster13();
			
			$current_date = date('Y-m-d', time());
			
			foreach($todupl_data as $k_elem => $v_elem_value)
			{
				if(!in_array($k_elem, $excluded_duplicate_keys))
				{
					if($k_elem == "datum")
					{
						$v_elem_value = $current_date;
					}
					else if($k_elem == "isduplicated")
					{
						$v_elem_value = "1";
					}
					else if($k_elem == "source")
					{
						$v_elem_value = $ms13_id;
					}
			
					$duplicate->{$k_elem} = $v_elem_value;
				}
			}
			
			$duplicate->save();
			
			$id = $duplicate->id;
			
			if($id)
			{
				/*$comment = "Formular Muster 13 Vervielfältigt.";
				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = Pms_CommonData::aesEncrypt("F");
				$cust->course_title = Pms_CommonData::aesEncrypt($comment);
				$cust->user_id = $logininfo->userid;
				$cust->done_name = Pms_CommonData::aesEncrypt('muster_13_form');
				$cust->tabname = Pms_CommonData::aesEncrypt('muster_13_form');
				$cust->done_date = date("Y-m-d H:i:s", time());
				$cust->done_id = $id;
				$cust->recordid = $id;
				$cust->save();*/
				
				//prepare log data
				$data['user'] = $userid;
				$data['muster13id'] = $id;
				$data['date'] = $duplicate->create_date;
				$data['operation'] = "duplicated";
				$data['source'] = $ms13_id;
				
				//save log
				$muster13_log = new Application_Form_Muster13Log();
				$write_muster13_log = $muster13_log->insert_muster13_log($ipid, $clientid, $data);
			
				return $id;
			}
			else
			{
				return false;
			}
			
		}

	}
	