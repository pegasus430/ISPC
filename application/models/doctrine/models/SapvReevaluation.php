<?php

	Doctrine_Manager::getInstance()->bindComponent('SapvReevaluation', 'MDAT');

	class SapvReevaluation extends BaseSapvReevaluation {

		public function getSapvReevaluationData($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvReevaluation')
				->where("ipid='" . $ipid . "'")
				->andWhere("isdeleted=0")
				->andWhere('clientid="' . $clientid . '"')
				->orderBy("id")
				->limit("1");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function get_multiple_sapv_reeval($ipids)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvReevaluation')
				->whereIn("ipid", $ipids)
				->andWhere("isdeleted=0")
				->andWhere('clientid="' . $clientid . '"')
				->orderBy("id");
			$droparray = $drop->fetchArray();

			foreach($droparray as $k_drop => $v_drop)
			{
				$drop_array[$v_drop['ipid']] = $v_drop;
			}

			return $drop_array;
		}

		public function deleteReevaluation($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$Q = Doctrine_Query::create()
				->delete('SapvReevaluation')
				->where("ipid='" . $ipid . "'");
			$Q->execute();

			return $Q;
		}

		public function export_sapv_xml($ipid =  null)
		{
			//get patient master data
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$patientmaster = new PatientMaster();
			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			$clientid = $logininfo->clientid;
			$patientinfo = $patientmaster->getMasterData($decid, 0);

			//get saved form data for export!
			$formdataarray = $this->getSapvReevaluationData($ipid);

			//mapped arrays
			$gender_arr = array('1' => 'männlich', '2' => 'weiblich');
			$verordnet_map = array('1' => 'Beratung', '2' => 'Koordination', '3' => 'Teilversorgung', '4' => 'Vollversorgung');
			$wohn_map = array('1' => 'zu hause, allein', '2' => 'zu Hause mit Angehörigen', '3' => 'im stat.Hospiz', '4' => 'in stat.Pflegeeinrichtung');
			$deathwish_map = array('1' => 'ja', '2' => 'nein', '3' => 'unbekannt/unbestimmt');
			$sapv_expect_map = array('1' => 'ja', '2' => 'nein', '3' => 'teilweise', '4' => 'unbekannt');
			$sapv_dead_map = array('1' => 'im häuslichen Umfeld verstorben', '2' => 'auf Palliativstation verstorben', '3' => 'im Krankenhaus verstorben', '4' => 'im Heim verstorben', '5' => 'im Hospiz verstorben');
			$wunsch_patient = array('ja', 'nein', 'unbekannt');

			//get client diagnosis type id
			$dg = new DiagnosisType();
			$abb2 = "'HD'";
			$ddarr2 = $dg->getDiagnosisTypes($logininfo->clientid, $abb2);
			$comma = ",";
			$typeid = "'0'";
			foreach($ddarr2 as $key => $valdia)
			{
				$typeid .=$comma . "'" . $valdia['id'] . "'";
				$comma = ",";
			}

			$patdia = new PatientDiagnosis();
			$dianoarray = $patdia->getFinalData($ipid, $typeid);

			if(count($dianoarray) > 0)
			{
				$comma = "";
				$diagnosis = "";
				$diagnosis_label = "";
				foreach($dianoarray as $key => $valdia)
				{
					if(strlen($valdia['diagnosis']) > 0 && in_array($valdia['icdnumber'], explode(',', $formdataarray[0]['icddiagnosis'])))
					{
						$diagnosis .= $comma . $valdia['icdnumber'];
						$diagnosis_label .= $comma . $valdia['diagnosis'];
						$comma = ",";
					}
				}
			}

			$dgND = new DiagnosisType();
			$abb2ND = "'ND'";
			$ddarr2ND = $dgND->getDiagnosisTypes($clientid, $abb2ND);
			$comma = ",";
			$typeidND = "'0'";
			foreach($ddarr2ND as $key => $valdia)
			{
				$typeidND .=$comma . "'" . $valdia['id'] . "'";
				$comma = ",";
			}

			$patdiaND = new PatientDiagnosis();
			$dianoNDarray = $patdiaND->getFinalData($ipid, $typeidND);

			if(count($dianoNDarray) > 0)
			{
				$n_diagnosis = explode(',', $formdataarray[0]['icdNDdiagnosis']);

				$diagnosisND = array();
				foreach($dianoNDarray as $key => $valdia)
				{
					if(strlen($valdia['diagnosis']) > 0 && in_array($valdia['icdnumber'], $n_diagnosis))
					{
						$diagnosisND[] = $valdia['icdnumber'];
						$diagnosisND_label[] = $valdia['diagnosis'];
					}
				}
			}
			//data gathering
			$xml_export_data['e_pk'] = ''; //blank
			$xml_export_data['e_status'] = ''; //blank
			$xml_export_data['e_patienten_id'] = $formdataarray[0]['epid']; //blank
			$xml_export_data['e_ersteller_titel'] = ''; //blank
			//
			//first and last name of user who is exporting
			$user = new User();
			$user_details = $user->getUserDetails($logininfo->userid);
			$xml_export_data['e_ersteller_vorname'] = $user_details[0]['last_name'];
			$xml_export_data['e_ersteller_nachname'] = $user_details[0]['first_name'];
			$xml_export_data['e_erstellt_am'] = date('Y-m-d');
			$xml_export_data['e_bearbeiter_titel'] = ''; //blank
			$xml_export_data['e_bearbeiter_vorname'] = ''; //blank
			$xml_export_data['e_bearbeiter_nachname'] = ''; //blank
			$xml_export_data['e_bearbeitet_am'] = date('Y-m-d'); //blank
			//version
			$xml_export_data['e_formular'] = 'Vers 2012';
			$xml_export_data['e_geburtsdatum'] = '';

			//health insurance matching
			$patient_health_insu = trim(mb_strtolower($formdataarray[0]['hi_company_name'], 'UTF-8'));

			$handler = fopen(APPLICATION_PATH . "/../public/sapv2012/s_kostentraeger.csv", "r");

			if($handler) //avoid huge loading if file not exists
			{
				while(($data = fgetcsv($handler, '500', ';', '"')) !== FALSE)
				{
					$csv_data_arr[$data[0]] = trim(mb_strtolower($data[1], 'UTF-8'));
				}
				fclose($handler);
			}

			$health_insu_match = array_search($patient_health_insu, $csv_data_arr);

			if($health_insu_match)
			{
				$xml_export_data['e_kostentraeger_fk'] = $health_insu_match; //http://smart-q.taskshere.com/project/718/ new: https://smartq.atlassian.net/browse/ISPC-40
			}
			else
			{
				$xml_export_data['e_kostentraeger_fk'] = '--';
			}

			//health insurance matching end
			$xml_export_data['ev_pk'] = '';
			$xml_export_data['eb_geburtsdatum'] = $patientinfo['birthd'];
			$xml_export_data['eb_alter'] = $formdataarray[0]['age'];
			$xml_export_data['eb_geschlecht'] = $gender_arr[$formdataarray[0]['gender']];
			$xml_export_data['eb_beginn'] = date('d.m.Y', strtotime($formdataarray[0]['beginSapvFall']));
			$xml_export_data['eb_kostentraeger'] = $formdataarray[0]['hi_company_name'];
			$xml_export_data['eb_grundkrankheit'] = $diagnosis;
			$xml_export_data['eb_grundkrankheit_name'] = $diagnosis_label;

			if(!empty($formdataarray[0]['firstSapvMaxbe']))
			{
				$verordnet[] = $verordnet_map[$formdataarray[0]['firstSapvMaxbe']];
			}

			if(!empty($formdataarray[0]['firstSapvMaxko']))
			{
				$verordnet[] = $verordnet_map[$formdataarray[0]['firstSapvMaxko']];
			}

			if(!empty($formdataarray[0]['firstSapvMaxtv']))
			{
				$verordnet[] = $verordnet_map[$formdataarray[0]['firstSapvMaxtv']];
			}

			if(!empty($formdataarray[0]['firstSapvMaxvv']))
			{
				$verordnet[] = $verordnet_map[$formdataarray[0]['firstSapvMaxvv']];
			}

			$xml_export_data['eb_verordnet'] = implode(', ', $verordnet);

			if(!empty($formdataarray[0]['alone']))
			{
				$wohn[] = $wohn_map[1];
			}

			if(!empty($formdataarray[0]['house_of_relatives']))
			{
				$wohn[] = $wohn_map[2];
			}

			if(!empty($formdataarray[0]['hospiz']))
			{
				$wohn[] = $wohn_map[3];
			}

			if(!empty($formdataarray[0]['nursingfacility']))
			{
				$wohn[] = $wohn_map[4];
			}

			$xml_export_data['eb_wohnsituation'] = implode(', ', $wohn);


			if(!empty($formdataarray[0]['curentlivingmore']))
			{
				$xml_export_data['eb_wohnsituation_text'] = $formdataarray[0]['curentlivingmore'];
			}
			else
			{
				$xml_export_data['eb_wohnsituation_text'] = '';
			}

			if(!empty($formdataarray[0]['stagekeine']))
			{
				$pflegestufe[] = 'keine';
			}

			if(!empty($formdataarray[0]['stageone']))
			{
				$pflegestufe[] = $formdataarray[0]['stageone'];
			}

			if(!empty($formdataarray[0]['stagetwo']))
			{
				$pflegestufe[] = $formdataarray[0]['stagetwo'];
			}

			if(!empty($formdataarray[0]['stagethree']))
			{
				$pflegestufe[] = $formdataarray[0]['stagethree'];
			}

			$xml_export_data['eb_pflegestufe'] = implode(', ', $pflegestufe);


			if(!empty($formdataarray[0]['beantragt']))
			{
				$pflegestufe_antrag[] = 'beantragt';
			}

			if(!empty($formdataarray[0]['nbeantragt']))
			{
				$pflegestufe_antrag[] = 'nicht beantragt';
			}

			$xml_export_data['eb_pflegestufe_antrag'] = implode(', ', $pflegestufe_antrag);


			$xml_export_data['eb_wunsch_patienten_sapv'] = $wunsch_patient[$formdataarray[0]['deathwishja']]; // "ja" / "nein" / "unbekannt" taken from "Sterbeort n. Wunsch:"

			if(count($diagnosisND) > 0)
			{
				foreach($diagnosisND as $key_arr => $ndiag)
				{
					$key = ($key_arr + 1);
					if($incr < '5')
					{
						$keys_s = 'ed_nebendiagnose_' . $key;
						$xml_export_data[$keys_s] = $ndiag . ', ' . $diagnosisND_label[$key_arr];
						$incr++;
					}
				}
			}

			if(!empty($formdataarray[0]['stabilization']))
			{
				$xml_export_data['ee_ende_stabilisierung'] = 'Stabilisierung';
			}
			else
			{
				$xml_export_data['ee_ende_stabilisierung'] = '';
			}

			if(!empty($formdataarray[0]['causaltherapy']))
			{
				$xml_export_data['ee_ende_therapieansatz'] = 'Kausaler Therapieansatz';
			}
			else
			{
				$xml_export_data['ee_ende_therapieansatz'] = '';
			}

			if(!empty($formdataarray[0]['regulationexpiration']))
			{
				$xml_export_data['ee_ende_ablauf_verordnung'] = 'Ablauf der Verordnung';
			}
			else
			{
				$xml_export_data['ee_ende_ablauf_verordnung'] = '';
			}


			if(!empty($formdataarray[0]['laying']))
			{
				$ende[] = $ende_map[4];
			}

			if(!empty($formdataarray[0]['deceased']))
			{
				$xml_export_data['ee_ende_verstorben'] = 'verstorben';
			}
			else
			{
				$xml_export_data['ee_ende_verstorben'] = '';
			}

			if(!empty($formdataarray[0]['noneedsapv']))
			{
				$ende[] = $ende_map[6];
			}

			if(!empty($formdataarray[0]['sapvterminationother']))
			{
				$xml_export_data['ee_ende_sonstiges'] = 'Sonstiges';
			}
			else
			{
				$xml_export_data['ee_ende_sonstiges'] = '';
			}

			$xml_export_data['ee_ende_sonstiges_text'] = ''; //blank

			if(!empty($formdataarray[0]['stagelastkeine']))
			{
				$pflegestufe_last[] = 'keine';
			}

			if(!empty($formdataarray[0]['stagelastone']))
			{
				$pflegestufe_last[] = $formdataarray[0]['stagelastone'];
			}

			if(!empty($formdataarray[0]['stagelasttwo']))
			{
				$pflegestufe_last[] = $formdataarray[0]['stagelasttwo'];
			}

			if(!empty($formdataarray[0]['stagelastthree']))
			{
				$pflegestufe_last[] = $formdataarray[0]['stagelastthree'];
			}

			$xml_export_data['ee_pflegestufe_abschluss'] = implode(', ', $pflegestufe_last);


			if(!empty($formdataarray[0]['lastbeantragt']))
			{
				$pflegestufe_last_antrag[] = 'beantragt';
			}

			if(!empty($formdataarray[0]['nlastbeantragt']))
			{
				$pflegestufe_last_antrag[] = 'nicht beantragt';
			}

			$xml_export_data['ee_pflegestufe_antrag'] = implode(', ', $pflegestufe_last_antrag);

			//sapv death start
			if(!empty($formdataarray[0]['homedead']))
			{
				$sapv_dead[] = $sapv_dead_map[$formdataarray[0]['homedead']];
			}
			if(!empty($formdataarray[0]['heimdead']))
			{
				$sapv_dead[] = $sapv_dead_map[$formdataarray[0]['heimdead']];
			}
			if(!empty($formdataarray[0]['hospizdead']))
			{
				$sapv_dead[] = $sapv_dead_map[$formdataarray[0]['hospizdead']];
			}
			if(!empty($formdataarray[0]['palliativdead']))
			{
				$sapv_dead[] = $sapv_dead_map[$formdataarray[0]['palliativdead']];
			}
			if(!empty($formdataarray[0]['krankendead']))
			{
				$sapv_dead[] = $sapv_dead_map[$formdataarray[0]['krankendead']];
			}

			$xml_export_data['ee_zusatz_verstorben_wo'] = implode(', ', $sapv_dead);
			//sapv death end

			if(!empty($formdataarray[0]['deathwishja']))
			{
				$xml_export_data['ee_zusatz_sterbeort_wunsch'] = $deathwish_map[$formdataarray[0]['deathwishja']];
			}
			else
			{
				$xml_export_data['ee_zusatz_sterbeort_wunsch'] = '';
			}

			$xml_export_data['ee_besuche'] = $formdataarray[0]['besuche'];

			if($formdataarray[0]['hospitalwithNotarz'] >= '0')
			{
				$xml_export_data['ee_notarzteinsaetze'] = $formdataarray[0]['hospitalwithNotarz'];
			}
			else
			{
				$xml_export_data['ee_notarzteinsaetze'] = '';
			}

			if($formdataarray[0]['hospitalwithoutNotarz'] >= '0')
			{
				$xml_export_data['ee_kh_einweisungen'] = $formdataarray[0]['hospitalwithoutNotarz'];
			}
			else
			{
				$xml_export_data['ee_kh_einweisungen'] = ' ';
			}

			if($formdataarray[0]['stathospiz'] == '1')
			{
				$durch[] = 'Stationäres Hospiz';
			}

			if($formdataarray[0]['kranken'] == '1')
			{
				$durch[] = 'Krankenhaus';
			}
			if($formdataarray[0]['palliativ'] == '1')
			{
				$durch[] = 'Palliativstation';
			}
			if($formdataarray[0]['statpflege'] == '1')
			{
				$durch[] = 'Stationäre Pflege';
			}
			if($formdataarray[0]['ambhospizdienst'] == '1')
			{
				$durch[] = 'ambulanter Hospizdienst';
			}
			if($formdataarray[0]['ambpflege'] == '1')
			{
				$durch[] = 'Amb. Pflege';
			}

			if($formdataarray[0]['harzt'] == '1')
			{
				$durch[] = 'Hausarzt';
			}

			if($formdataarray[0]['farzt'] == '1')
			{
				$durch[] = 'Facharzt';
			}

			if($formdataarray[0]['patange'] == '1')
			{
				$durch[] = 'Patient/Angehörige';
			}

			if($formdataarray[0]['beratung'] == '1')
			{
				$durch[] = 'Beratungsdienst';
			}

			if($formdataarray[0]['erstsapv'] == '1')
			{
				$durch_sapv[] = 'Erst-SAPV';
			}

			if($formdataarray[0]['weideraufnahme'] == '1')
			{
				$durch_sapv[] = 'Wiederaufnahme SAPV';
			}

			if(count($durch_sapv) > 0)
			{
				$xml_export_data['ev_erstkontakt_durch_sapv'] = implode(', ', $durch_sapv); //"Erst-SAPV" oder "Wiederaufnahme-SAPV" take from form
			}
			else
			{
				$xml_export_data['ev_erstkontakt_durch_sapv'] = '';
			}

			if(count($durch) > 0)
			{
				$xml_export_data['ev_erstkontakt_durch'] = implode(', ', $durch);
			}
			else
			{
				$xml_export_data['ev_erstkontakt_durch'] = '';
			}


			if(!empty($formdataarray[0]['expectationkeine']))
			{
				$xml_export_data['ev_erwart_beginn_keine_angabe'] = '1';
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_keine_angabe'] = '0';
			}

			if(!empty($formdataarray[0]['expectationsonstiges']))
			{
				$xml_export_data['ev_erwart_beginn_sonstiges_text'] = $formdataarray[0]['expectationsonstiges'];
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_sonstiges_text'] = '';
			}

			if(!empty($formdataarray[0]['expectation']))
			{
				$xml_export_data['ev_erwart_beginn_sonstiges'] = $formdataarray[0]['expectation'];
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_sonstiges'] = '';
			}

			if(!empty($formdataarray[0]['preabilitation']))
			{
				$xml_export_data['ev_erwart_beginn_pall_reha'] = $formdataarray[0]['preabilitation'];
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_pall_reha'] = '';
			}

			if(!empty($formdataarray[0]['symptomrelief']))
			{
				$xml_export_data['ev_erwart_beginn_symptomlind'] = $formdataarray[0]['symptomrelief'];
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_symptomlind'] = '';
			}

			if(!empty($formdataarray[0]['nohospital']))
			{
				$xml_export_data['ev_erwart_beginn_kein_krankenhaus'] = $formdataarray[0]['nohospital'];
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_kein_krankenhaus'] = '';
			}

			if(!empty($formdataarray[0]['nolifeenxendingmeasures']))
			{
				$xml_export_data['ev_erwart_beginn_kein_lebensverl'] = $formdataarray[0]['nolifeenxendingmeasures'];
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_kein_lebensverl'] = '';
			}

			if(!empty($formdataarray[0]['leftalone']))
			{
				$xml_export_data['ev_erwart_beginn_in_ruhe_lassen'] = $formdataarray[0]['leftalone'];
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_in_ruhe_lassen'] = '';
			}

			if(!empty($formdataarray[0]['activeparticipation']))
			{
				$xml_export_data['ev_erwart_beginn_selbstbestimmung'] = $formdataarray[0]['activeparticipation'];
			}
			else
			{
				$xml_export_data['ev_erwart_beginn_selbstbestimmung'] = '';
			}

			$patient_erwartung = array('ja', 'nein', 'teilweise', 'unbekannt');

			if(!empty($formdataarray['patientexpectationsapv']))
			{
				$xml_export_data['ev_pat_erwart_sapv_real'] = $patient_erwartung[$formdataarray['patientexpectationsapv']];
			}
			else
			{
				$xml_export_data['ev_pat_erwart_sapv_real'] = '';
			}

			if($formdataarray[0]['painsymptoms'] >= '0')
			{
				$xml_export_data['ev_kompl_sympt_schmerz'] = $formdataarray[0]['painsymptoms'];
			}
			else
			{
				$xml_export_data['ev_kompl_sympt_schmerz'] = '';
			}

			if($formdataarray[0]['gastrointestinalsymptoms'] >= '0')
			{
				$xml_export_data['ev_kompl_sympt_gastro'] = $formdataarray[0]['gastrointestinalsymptoms'];
			}
			else
			{
				$xml_export_data['ev_kompl_sympt_gastro'] = '';
			}

			if($formdataarray[0]['psychsymptoms'] >= '0')
			{
				$xml_export_data['ev_kompl_sympt_neuro'] = $formdataarray[0]['psychsymptoms'];
			}
			else
			{
				$xml_export_data['ev_kompl_sympt_neuro'] = '';
			}

			if($formdataarray[0]['urogenitalsymptoms'] >= '0')
			{
				$xml_export_data['ev_kompl_sympt_uro'] = $formdataarray[0]['urogenitalsymptoms'];
			}
			else
			{
				$xml_export_data['ev_kompl_sympt_uro'] = '';
			}

			if($formdataarray[0]['ulztumor'] >= '0')
			{
				$xml_export_data['ev_kompl_sympt_ulz'] = $formdataarray[0]['ulztumor'];
			}
			else
			{
				$xml_export_data['ev_kompl_sympt_ulz'] = '';
			}

			if($formdataarray[0]['cardiacsymptoms'] >= '0')
			{
				$xml_export_data['ev_kompl_sympt_respir'] = $formdataarray[0]['cardiacsymptoms'];
			}
			else
			{
				$xml_export_data['ev_kompl_sympt_respir'] = '';
			}



			if($formdataarray[0]['ethicalconflicts'] >= '0')
			{
				$xml_export_data['ev_weit_gesch_ethisch'] = $formdataarray[0]['ethicalconflicts'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_ethisch'] = '';
			}

			if($formdataarray[0]['acutecrisispat'] >= '0')
			{
				$xml_export_data['ev_weit_gesch_akut'] = $formdataarray[0]['acutecrisispat'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_akut'] = '';
			}

			if($formdataarray[0]['paliatifpflege'] >= '0')
			{
				$xml_export_data['ev_weit_gesch_palliativ'] = $formdataarray[0]['paliatifpflege'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_palliativ'] = '';
			}

			if($formdataarray[0]['privatereferencesupport'] >= '0')
			{
				$xml_export_data['ev_weit_gesch_unterst'] = $formdataarray[0]['privatereferencesupport'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_unterst'] = '';
			}

			if($formdataarray[0]['sociolegalproblems'] >= '0')
			{
				$xml_export_data['ev_weit_gesch_betreuung'] = $formdataarray[0]['sociolegalproblems'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_betreuung'] = '';
			}

			if($formdataarray[0]['securelivingenvironment'] >= '0')
			{
				$xml_export_data['ev_weit_gesch_sicherung'] = $formdataarray[0]['securelivingenvironment'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_sicherung'] = '';
			}

			if($formdataarray[0]['coordinationcare'] >= '0')
			{
				$xml_export_data['ev_weit_gesch_koordination'] = $formdataarray[0]['coordinationcare'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_koordination'] = '';
			}

			if($formdataarray[0]['otherrequirements'] >= '0')
			{
				$xml_export_data['ev_weit_gesch_sonstiges'] = $formdataarray[0]['otherrequirements'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_sonstiges'] = '';
			}

			if(!empty($formdataarray[0]['complexeventsmore']))
			{
				$xml_export_data['ev_weit_gesch_sonstiges_text'] = $formdataarray[0]['complexeventsmore'];
			}
			else
			{
				$xml_export_data['ev_weit_gesch_sonstiges_text'] = '';
			}

			if($formdataarray[0]['actualconductedko'])
			{
				$sapvlast[] = $verordnet_map[1];
			}

			if($formdataarray[0]['actualconductedbe'])
			{
				$sapvlast[] = $verordnet_map[2];
			}

			if($formdataarray[0]['actualconductedtv'])
			{
				$sapvlast[] = $verordnet_map[3];
			}

			if($formdataarray[0]['actualconductedvv'])
			{
				$sapvlast[] = $verordnet_map[4];
			}

			$xml_export_data['ev_tats_durchgef_sapv'] = implode(', ', $sapvlast);
			$xml_export_data['ev_ende_sapv_am'] = date('d.m.Y', strtotime($formdataarray[0]['endSapvFall']));
			$xml_export_data['ev_ende_sapv_kein_bedarf'] = $formdataarray[0]['noneedsapv'];
			$xml_export_data['ev_sapv_real_erfolgreich'] = $sapv_expect_map[$formdataarray[0]['sapvstatusja']];
			$xml_export_data['ev_tage_24h_bereitschaft'] = $formdataarray[0]['bereitschft'];
			$xml_export_data['ev_tage_intermittierung'] = $formdataarray[0]['allhospitaldays'];

			$export_data['evaluation'] = $xml_export_data;

			$xml_data = $this->toXml($export_data);

//			print_r($xml_data);exit;
			return $this->xmlpp($xml_data);
		}

		private function toXml($data, $rootNodeName = '<evaluations></evaluations>', $xml = null, $elem_root = '', $xsd_file = false)
		{
			// turn off compatibility mode as simple xml throws a wobbly if you don't.
			if(ini_get('zend.ze1_compatibility_mode') == 1)
			{
				ini_set('zend.ze1_compatibility_mode', 0);
			}


			if($xml == null)
			{
				$xml = simplexml_load_string("<?xml version='1.0' ?>$rootNodeName");
			}

			// loop through the data passed in.
			foreach($data as $key => $value)
			{
				// no numeric keys in our xml please!
				if(is_numeric($key))
				{
					// make string key...
					$key = "unknownNode" . (string) $key;
				}

//				replace anything not alpha numeric
//				$key = preg_replace('/[^a-z]/i', '', $key);
//
//				find out if key is ipid
//				$ipid_key = explode('_', $key);
//				if (count($ipid_key) == '1')
//				{
//					$key = $elem_root;
//				}
//
				// if there is another array found recrusively call this function
				if(is_array($value))
				{
					$node = $xml->addChild($key);
					// recrusive call.
					$this->toXml($value, $rootNodeName, $node);
				}
				else
				{
					// add single node.
					$value = html_entity_decode($value, ENT_QUOTES, "utf-8");
					$xml->addChild($key, $value);
				}
			}
			// pass back as string. or simple xml object if you want!

			return $xml->asXML();
		}

		function xmlpp($xml, $html_output = false)
		{
			$xml_obj = new SimpleXMLElement($xml);

			$level = 4;
			$indent = 0; // current indentation level
			$pretty = array();

			// get an array containing each XML element
			$xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));

			// shift off opening XML tag if present
			if(count($xml) && preg_match('/^<\?\s*xml/', $xml[0]))
			{
				$pretty[] = array_shift($xml);
			}

			foreach($xml as $el)
			{
				if(preg_match('/^<([\w])+[^>\/]*>$/U', $el))
				{
					// opening tag, increase indent
					$pretty[] = str_repeat(' ', $indent) . $el;
					$indent += $level;
				}
				else
				{
					if(preg_match('/^<\/.+>$/', $el))
					{
						$indent -= $level;  // closing tag, decrease indent
					}
					if($indent < 0)
					{
						$indent += $level;
					}
					$pretty[] = str_repeat(' ', $indent) . $el;
				}
			}
			$xml = implode("\n", $pretty);
			$xml = html_entity_decode($xml, ENT_QUOTES, "utf-8");

			return ($html_output) ? $xml : $xml;
		}

		public function get_export_ready_sapv($clientid, $ipids = false, $period_filter = false)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('SapvReevaluation')
				->where("isdeleted=0")
				->andWhere('clientid="' . $clientid . '"')
				->andWhere('export_ready="1"');
			if($period_filter)
			{
				$drop->andWhere('beginSapvFall BETWEEN "' . date('Y-m-d H:i:s', strtotime($period_filter['start_date'])) . '" AND "' . date('Y-m-d H:i:s', strtotime($period_filter['end_date'])) . '"');
			}

			if($ipids)
			{
				$drop->andWhereIn('ipid', $ipids);
			}

			$drop->orderBy("create_date, change_date DESC");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

	}

?>