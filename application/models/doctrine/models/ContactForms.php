<?php

Doctrine_Manager::getInstance()->bindComponent('ContactForms', 'MDAT');

class ContactForms extends BaseContactForms 
{
    
    const PatientCourse_DONE_NAME = 'contact_form';// this will be inserted on all var PatientCourse
    
    /**
     * INFO: if you change/update this list, check the offline /home/www/ispcw2017/src/Model/Table/PatientCourseTable.php
     * and replace there this fn
     * 
     * mapped the form_block to the tabnames this inserts in patientCourse
     * @return multitype:string
     */
    public static function block_2_patientCourse_tabname()
    {
        /*
         * null are the blocks that do not insert into patient course
         * exception the ones i don;t yet know like med and med_time_dosage .. this 2 i will hardcode on top
         */
        return [

            'med'                   => [// Medikation
                'patient_drugplan', 
                'patient_drugplan_deleted', //this is added when you delete a medication 
            ], 
            'med_time_dosage'       => [// Medikation New -ISPC1624 
                'patient_drugplan', 
                'patient_drugplan_deleted', //this is added when you delete a medication 
            ], 
            
            'drivetime'             => 'fahrtzeit_block', // Fahrtzeit / -strecke            
            'drivetime_doc'         => 'FormBlockDrivetimedoc', // Fahrtzeit / Dokumentationszeit
            /*
             * symp, symp_zapv, symp_zapv_complex will NOT work on the same form
             */
            'symp'                  => 'PatientSymptomatology', // Symptome
            'symp_zapv'             => 'PatientSymptomatology', // Symptome ZAPV I
            'symp_zapv_complex'     => 'PatientSymptomatology', // Symptome ZAPV II
            'com'                   => 'comment_block', // Kommentare
            'com_ph'                => 'comment_apotheke_block', // Kommentare Apotheke
            'anam'                  => 'case_history_block', // Anamnese
            
            'sgbv'                  => 'FormBlockSgbv', // Leistungseingabe
            'ecog'                  => 'karnofsky_block', // ECOG
            'befund'                => 'FormBlockBefund', // Korperlicher Befund
            'careinstructions'      => 'care_instructions_block', // Pflege Anweisung
            'visitplan'             => 'quality_block', // Besuch war
            'internalcomment'       => 'internal_comment_block', // Interner Kommentar
            'classification'        => 'FormBlockClassification', // Klassifizierung
            'bra_sapv'              => 'FormBlockBraSapv', // BRA - SAPV Team - Hausarzt Einsatz
            'additional_users'      => 'FormBlockAdditionalUsers', // Beteiligte Mitarbeiter
            'sgbxi'                 => 'FormBlockSgbxiActions', // Qualitätssicherungsbesuch (BW SGBV XI)
            'measures'              => 'FormBlockMeasures', // Maßnahmen
            'befund_txt'            => 'befund_txt_block', // Befund
            'free_visit'            => null, // Nicht berechnen in internen Rechnungen
            'therapy'               => 'therapy_txt_block', // Therapie
            'sgbxi_actions'         => 'FormBlockSgbxiActions', // SGB XI Leistungen
            //'ebm_ber', // EBM(BER_) for client BER
            //'service_entry', // Leistungserfassung BER
            'vital_signs'           => 'FormBlockVitalSigns', // Vitalwerte
            'bowel_movement'        => 'FormBlockBowelMovement', // Stuhlgang or bowel movement
            'hospiz_imex'           => 'FormBlockHospizimex',// Hospiz II  - Einfuhr & Ausfuhr
            'hospiz_medi'           => 'FormBlockHospizmedi',// Hospiz I
            'ipos'                  => null,//+ IPOS ISPC-1719 basisassessment from clinic system
            'lmu_visit'             => null, //+ Status  ISPC-1719 basisassessment from clinic system
            'lmu_pmba_body'         => null,//+ Körperliche Untersuchung ISPC-1719 basisassessment from clinic system
            'lmu_pmba_pain'         => null,// + Schmerzanamnese     ISPC-1719 basisassessment from clinic system
            'lmu_pmba_wishes'       => null,//+ Wünsche und Erwartungen des Patienten und seiner Angehörigen ISPC-1719 basisassessment from clinic system
            'lmu_pmba_aufklaerung'  => null,// + Aufklärungsstand/Krankheitsverarbeitung Patient/ ggf. Angehörige ISPC-1719 basisassessment from clinic system
            'todos'                 => 'block_todos', //+ To Do ISPC-1719 basisassessment from clinic system
            'lmu_pmba_psysoz'       => 'lmu_pmba_psysoz',// + Psychosoziale Anamnese ISPC-1719 basisassessment from clinic system  (15.06.2016)
            'kvno_visit_type'       => null, //+ ISPC-1740 block Nordrhein
            'bavaria_options'       => 'comments_l_block', //+ ISPC-1703 Kontaktformular for BAVARIA
            'time_division'         => null, //+ ISPC-1784 Zeitaufteilung Kontaktformular this block shows the "minutes" of the visit which the user documented
            'tracheostomy'          => 'FormBlockTracheostomy', //ISPC-1787
            'clientsymptoms'        => 'cf_client_symptoms', // ISPC-1798
            'ventilation'           => 'FormBlockVentilation', // ISPC-1798 - Beatmung
            'invoice_condition'     => null, // ISPC-1798 - Beatmung
            
            /*
             * time block inserts multiple tabnames...  
             */
            'time' => [
                'contact_form_first_date', // when you first create the formular
                'contact_form_change_date', // if you update the Time block, it has a different tabname
                'contact_form_moved_date' // this was removed with ispc 2071
            ],
            
            /*
             * next blocks are the one that overwrite the other ...
             */
            'ebm'                   => [
                'FormBlockEbmi', // FormBlockEbmi  = Arzt EBM , this has pc
                null, // FormBlockEbm = Arzt EBM , this has no patient course
            ],
            'goa'                   => [
                'FormBlockGoai',//Arzt GOÄ =  FormBlockGoai
                null,//Arzt GOÄ = FormBlockGoa , this has no patient course
            ],
            'ebmii'                 => [// EBM Hausbesuch
                'FormBlockEbmii',
                'patient_xbdt_actions' , //this was not done, FormBlockXbdtEbmii
            ],
            'goaii'                 => [// GOA Hausbesuch
                'FormBlockGoaii',
                'patient_xbdt_actions', //this was not done, FormBlockXbdtGoaii
            ],
            
            
            'puncture'              => FormBlockPuncture::PATIENT_COURSE_TABNAME,
            'infusion'              => FormBlockInfusion::PATIENT_COURSE_TABNAME,
            'infusiontimes'         => FormBlockInfusiontimes::PATIENT_COURSE_TABNAME,
            'adverseevents'         => FormBlockAdverseevents::PATIENT_COURSE_TABNAME,
            // ISPC-2387
            'visitclasification'    => FormBlockVisitClasification::PATIENT_COURSE_TABNAME,
            // ISPC-2488 Lore 22.11.2019
            'delegation'            => FormBlockDelegation::PATIENT_COURSE_TABNAME,
            // ISPC-2487 Ancuta 27.11.2019
            'coordinator_actions'   => FormBlockCoordinatorActions::PATIENT_COURSE_TABNAME,
            /*
             * this tabnames are exta, but were also inserted by a contacform
             * 'contact_form', //Besuch vom 08.08.2018 14:12 wurde editiert
             * 'contact_form_save', //PDF des Kontaktformular - Kontaktformular Arzt in Dateien und Dokumente wurde hinterlegt
             * 'contact_form_no_link', //Kontaktformular  hinzugefügt || Besuch vom 08.08.2018 14:12 wurde editier
             */
           
            
            //ISPC-2673 Lore 25.09.2020
            'resources'            => FormBlockResources::PATIENT_COURSE_TABNAME,
            
        ];
    }
    
    
        // #ISPC-2512PatientCharts
		public function get_contact_form($contact_form, $allow_deleted = false)
		{
			if(empty($contact_form)){
				return false;
			}
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->where('id= ?',$contact_form);
			if(!$allow_deleted)
			{
				$select->andWhere('isdelete="0"');
			}

			$select_res = $select->fetchArray();

			if($select_res)
			{
				return $select_res[0];
			}
			else
			{
				return false;
			}
		}

		public function get_patient_contact_form($form_id = false, $visit_credentials = false)
		{
			if(empty($form_id)){
				return false;
			}
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->where('id= ?', $form_id);
			$select_res = $select->fetchArray();

			if(empty($select_res)){
				return false;
			}
			
			if($visit_credentials['patient'])
			{
				//normal symptomatics
				$sympval = new SymptomatologyValues();
				$set_details = $sympval->getSymptpomatologyValues(1); //HOPE set

				$form_symp = new ContactFormsSymp();
				$form_symps = $form_symp->getContactFormsSymp($form_id, $visit_credentials['patient']);

				if($visit_credentials['symptomatology_scale'] == 'a')
				{
					$sym_attrib_scale = Pms_CommonData::symptoms_attribute_values();
				}

				foreach($set_details as $key => $sym)
				{
					$sym_entry = '';
					if(strlen($form_symps[$sym['id']]['comment']) > '0')
					{
						$sym_comment = ' (' . $form_symps[$sym['id']]['comment'] . ')';
					}
					else
					{
						$sym_comment = '';
					}

					if($visit_credentials['symptomatology_scale'] == 'a')
					{

						$sym_value = $sym_attrib_scale[$form_symps[$sym['id']]['current_value']];
					}
					else
					{
						$sym_value = $form_symps[$sym['id']]['current_value'];
					}

					$sym_entry = $sym['value'] . ' ' . $sym_value . $sym_comment;

					$newsymptomarr['symptomatik'][] = $sym_entry;
				}

				//zapv symptomatics
				$replaced_str = array('_', '/', '\\', '.', ',', '-', ']', '[', '`', '\'', '"', "_", "|", "  ");
				$alias = false;
				$other_alias = true;
				$multi_array = true;
				$set_details_zapv = SymptomatologyZapvDetails::getSymptpomatologyZapvDetails(4, $alias, $other_alias, $multi_array); //HESSEN set

				$symp_zapv_details = new SymptomatologyZapvDetails();
				$zapv_details_items = $symp_zapv_details->getSymptpomatologyZapvItems();

				$symps = Doctrine_Query::create()
					->select('*')
					->from('ContactFormsSymp')
					->where('ipid = ?', $visit_credentials['patient'])
					->andWhere('contact_form_id = ?', $form_id)
					->orderBy('current_value DESC'); 
				$symarr = $symps->fetchArray();

				//show subcategories instead of values
				foreach($symarr as $k_symarr => $v_symarr)
				{
					$cf_symp_ids[$v_symarr['id']] = $v_symarr['symp_id'];
				}


				if($symarr)
				{

					$symptoms_details = Doctrine_Query::create()
						->select('*')
						->from('ContactFormsSympDetails')
						->where('contact_form_id = ?',$form_id)
						->orderBy('id ASC');
					$symptoms_details_res = $symptoms_details->fetchArray();

					foreach($symptoms_details_res as $k_sym_det => $v_sym_det)
					{
						$subcat_symptoms[$cf_symp_ids[$v_sym_det['entry_id']]][] = $zapv_details_items[$v_sym_det['detail_id']];
					}
					foreach($symarr as $ksy => $vsy)
					{
						if($subcat_symptoms[$vsy['symp_id']])
						{
							$sympt_descr = utf8_encode(str_replace(" ", "_", str_replace($replaced_str, " ", strtolower(trim(rtrim($set_details_zapv[$vsy['symp_id']]['alias']))))));
							$zapv_form_data[$sympt_descr] = html_entity_decode(implode(', ', $subcat_symptoms[$vsy['symp_id']]), ENT_QUOTES, 'utf-8');
						}
					}
				}
				else
				{
					foreach($zapv_details_items as $k_zapv_s => $v_zapv_s)
					{
						$sympt_descr = utf8_encode(str_replace(" ", "_", str_replace($replaced_str, " ", strtolower(trim(rtrim($set_details_zapv[$k_zapv_s]['alias']))))));
						if($sympt_descr != '')
						{
							$zapv_form_data[$sympt_descr] = '';
						}
					}
				}
				
				
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
				
				// symptome II block
				$ipid = $visit_credentials['patient'];
				//$sym_blocks = FormBlockClientSymptoms::get_patients_form_block_ClientSymptoms($ipid,array($form_id) );
				$sym_blocks_all_data = FormBlockClientSymptoms::get_patients_form_block_ClientSymptoms($ipid,array($form_id),false,false,true );
				
				$sym_blocks = array();
				foreach($sym_blocks_all_data[$ipid] as $cf_id=>$symp_data){
				    foreach($symp_data as $sline=>$sy_data){
				        $sym_blocks[$ipid][$cf_id][] = $sy_data['symptom_id'];
				    }
				}
				
				if(!empty($sym_blocks)) {
						
					
					$client_symp_groups = ClientSymptomsGroups::get_client_symptoms_groups($clientid);
					$client_symps = ClientSymptoms::get_client_symptoms($clientid);
					
					$symptomatik_II = "";
					foreach($sym_blocks[$ipid] as $cf_id=>$symp_data ){
				
						foreach($symp_data as $ko=>$sm_id){
				
							if(!is_array($return_data[$client_symps[$sm_id]['group_id']])){
				
								$return_data_extra[$client_symps[$sm_id]['group_id']] = array();
				
							}
				
							$symptomatik_II .= trim($client_symps[$sm_id]['description']).', ';
							$return_data_arr[$client_symps[$sm_id]['group_id']][]= trim($client_symps[$sm_id]['description']);
				
							$return_data_extra[$client_symps[$sm_id]['group_id']][] = $sm_id;
						}
					}
				}
				
				
				//ISPC-1236 Lore 20.01.2020
				if(!empty($sym_blocks_all_data)) {
				    $severity = array(""=>"","0"=>"kein","4"=>"leicht","7"=>"mittel","10"=>"schwer");
					$symptomatik_II_table = array();
					foreach($sym_blocks_all_data[$ipid] as $cf_ids=>$symp_datas ){
					    
					    $symptomatik_II_table['date'] = $select_res[0]['start_date'];
					    
					    foreach($symp_datas as $kye=>$vla){
					        
					        $symptomatik_II_table[$vla['symptom_id']]['name'] = $client_symps[$vla['symptom_id']]['description'];
					        $symptomatik_II_table[$vla['symptom_id']]['value'] = $severity[$vla['severity']];
					    }
					    
					}
				}
				//.
						
				
				
				
				$blocks_settings = new FormBlocksSettings();
				$block_measures_values = $blocks_settings->get_block($clientid, 'measures');
				
				$system_measures_items = array();
				foreach($block_measures_values as $k=>$v){
					$system_measures_items[$v['id']] = $v['option_name'];
				}
				$patient_measures_blocks = FormBlockMeasures::getPatientFormBlockMeasures($ipid,$form_id );
				
				
				$cf_measures_values = array();
				foreach($patient_measures_blocks as	$option_id=>$action_value){
					if($action_value == "1"){
						$cf_measures_values[] = $system_measures_items[$option_id];
					}
				}
		
				
				//ISPC-1236 Lore 28.02.2020 ($Vitalwerte_aktuell$)
				$form_vital = new FormBlockVitalSigns();
				$form_vital_all_data = $form_vital->getPatientFormBlockVitalSigns1( $visit_credentials['patient'], $form_id);
				
				if(!empty($form_vital_all_data)) {
				    
				    $vitalwerte_str = '';
				    
				    foreach($form_vital_all_data as	$keys=>$values){
				        $tovars = array();

				        if ($values['blood_pressure_a'] != 0 || $values['blood_pressure_b'] != 0) {
				            
				            $tovars_blood = array();
				            $tovars_blood['pre_blood_pressure_a'] = "RR: ";
				            
				            if ($values['blood_pressure_a'] != 0) {
				                $tovars_blood['blood_pressure_a'] = Pms_CommonData::str2num($values['blood_pressure_a']);
				            }
				            
				            if ($values['blood_pressure_a'] != 0 && $values['blood_pressure_b'] != 0) {
				                $tovars_blood['blood_pressure_val_separator'] = " / ";
				            }
				            
				            if ($values['blood_pressure_b'] != 0) {
				                $tovars_blood['blood_pressure_b'] = Pms_CommonData::str2num($values['blood_pressure_b']);
				            }
				            
				            $tovars_blood['post_blood_pressure_b'] = " mmHg"."\n";
				            
				            $tovars['blood_pressure'] = implode('', $tovars_blood);
				        }
				            
			            if ($values['puls'] != 0) {
			                $tovars['puls'] = "Puls: " . Pms_CommonData::str2num($values['puls']) . " /min."."\n";
			            }
			            
			            if ($values['respiratory_frequency'] != 0) {
			                $tovars['respiratory_frequency'] = "Atemfrequenz: " . Pms_CommonData::str2num($values['respiratory_frequency']) . " /min"."\n";
			            }
			            
			            if ($values['temperature_dd'] == '1') {
			                $temperature_dd = "im Ohr";
			            } elseif ($values['temperature_dd'] == '2') {
			                $temperature_dd = "oral";
			            } elseif ($values['temperature_dd'] == '3') {
			                $temperature_dd = "rektal";
			            }
			            //TODO-3513 Lore 12.10.2020
			            elseif ($values['temperature_dd'] == '4') {
			                $temperature_dd = "in der Blase";
			            } elseif ($values['temperature_dd'] == '5') {
			                $temperature_dd = "axillar";
			            } elseif ($values['temperature_dd'] == '6') {
			                $temperature_dd = "an der Stirn";
			            }
			            //.
			            
			            if ($values['temperature'] != 0) {
			                $tovars['temperature'] = "Temperatur: " . Pms_CommonData::str2num($values['temperature']) . " °C " . $temperature_dd."\n";
			            }
			            
			            if ($values['oxygen_saturation'] != 0) {
			                $tovars['oxygen_saturation'] = "Sauerstoffsättigung: " . Pms_CommonData::str2num($values['oxygen_saturation']) . " %"."\n";
			            }
			            
			            if ($values['blood_sugar'] != 0) {
			                $tovars['blood_sugar'] = "BZ: " . Pms_CommonData::str2num($values['blood_sugar']) . " mg/dl"."\n";
			            }
			            
			            if ($values['weight'] != 0) {
			                $tovars['weight'] = "Gewicht: " . Pms_CommonData::str2num($values['weight']) . " Kg"."\n";
			            }
			            
			            if ($values['height'] != 0) {
			                $tovars['height'] = "Größe : " . Pms_CommonData::str2num($values['height']) . " cm"."\n";
			            }
			            
			            if ($values['head_circumference'] != 0) {
			                $tovars['head_circumference'] = "Kopfumfang: " . Pms_CommonData::str2num($values['head_circumference']) . " cm"."\n";
			            }
			            
			            if ($values['waist_circumference'] != 0) {
			                $tovars['waist_circumference'] = "Bauchumfang: " . Pms_CommonData::str2num($values['waist_circumference']) . " cm"."\n";
			            }
			            
			            if (! empty($tovars)) {
			                $vitalwerte_str  .= "Vitalwerte: Datum: " . date("d.m.Y H:i",strtotime($values['signs_date'])) . "\n " . implode(' ', $tovars) ."\n";
			            }				        
				    }

				}
				//.
				
			}

			foreach($select_res as $k_cf => $v_cf)
			{
				if(count($newsymptomarr) > '0')
				{
					$v_cf['symptomatik'] = implode('; ', $newsymptomarr['symptomatik']);
					if($zapv_form_data){
						$v_cf['zapv_symptomatik'] = $zapv_form_data;
					} else{
						$v_cf['zapv_symptomatik'] = "";
					}
					if(strlen($symptomatik_II) > 0 ){
						$v_cf['symptomatik_II'] = $symptomatik_II;
					} else{
						$v_cf['symptomatik_II'] = "";
					}

					//ISPC-1236 Lore 20.01.2020
					if(count($symptomatik_II_table) > 0 ){
					    $v_cf['symptomatik_II_table'][] = $symptomatik_II_table;
					}
					else{
					    $v_cf['symptomatik_II_table'] = "";
					}
					//.
					
				}
				else
				{
					$v_cf['symptomatik'] = '';
					$v_cf['zapv_symptomatik'] = '';
					$v_cf['symptomatik_II'] = "";
					$v_cf['symptomatik_II_table'] = "";
					
				}
				
				if(count($cf_measures_values) > 0)
				{
					$v_cf['Maßnahmen'] = implode(', ', $cf_measures_values);
				}
				else
				{
					$v_cf['Maßnahmen'] = "";
				}
				
				//ISPC-1236 Lore 28.02.2020 ($Vitalwerte_aktuell$)
				
				if(count($vitalwerte_str) > 0)
				{
				    $v_cf['Vitalwerte_aktuell'] = $vitalwerte_str;
				}
				else
				{
				    $v_cf['Vitalwerte_aktuell'] = "";
				}

				$contact_forms[$k_cf] = $v_cf;
			}

			return $contact_forms;
		}

		public function get_child_forms($contact_form, $return_ids = false)
		{
			if(empty($contact_form )){
				return false;
			}
			
			$select = Doctrine_Query::create()
				->select('*')
				->from('ContactForms')
				->where('parent = ?', $contact_form)
				->andWhere('isdelete = ?', 1);
			$select_res = $select->fetchArray();

			if($select_res)
			{
				if($return_ids)
				{
					foreach($select_res as $k_sel => $v_sel)
					{
						$sel_ids[] = $v_sel['id'];
					}

					return $sel_ids;
				}
				else
				{
					return $select_res;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_contact_form_period($ipid, $period = null, $excluded_cf = false)
		{
			if(empty($ipid)){
				return false;
			}
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->where('ipid=?', $ipid)
				->andwhere('isdelete = ?', 0);

			if($period)
			{
				$start_date = date('Y-m-d', strtotime($period['start']));
				$end_date = date('Y-m-d',  strtotime($period['end']));
				$select->andWhere('DATE(billable_date) BETWEEN ? AND ?', array($start_date,$end_date));
			}
			
			if($excluded_cf &&  !empty($excluded_cf))
			{
				$select->andWhereNotIn('id', $excluded_cf);
			}
			$select->orderBy('start_date ASC');
			$select_res = $select->fetchArray();

			
			if($select_res)
			{
				return $select_res;
			}
			else
			{
				return false;
			}
		}

		public function get_multiple_contact_form_period($ipids, $period = false, $excluded_cf = false, $orderby = 'id', $orderdirection = 'ASC')
		{
			if(empty($ipids)){
				return false;
			}
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->whereIn('ipid', $ipids)
				->andwhere('isdelete="0"');

			if($period)
			{
				$start_date = date('Y-m-d', strtotime($period['start']));
				$end_date = date('Y-m-d',   strtotime($period['end']));
				$select->andWhere('DATE(billable_date) BETWEEN ? AND ?', array($start_date,$end_date));
			}

			if($excluded_cf)
			{
				$select->andWhereNotIn('id', $excluded_cf);
			}
			$select->orderBy($orderby." ".$orderdirection);
			$select_res = $select->fetchArray();

			if($select_res)
			{
				foreach($select_res as $k_cf => $v_cf)
				{
// 					$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_duration(str_pad($v_cf['begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_cf['end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_cf['begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_cf['end_date_m'], 2, "0", STR_PAD_LEFT), $v_cf['date']);
					$v_cf['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$v_cf['end_date']);
					$contact_forms[$v_cf['ipid']][] = $v_cf;
				}

				return $contact_forms;
			}
			else
			{
				return false;
			}
		}

		public function get_calendar_contact_form($userid, $start, $end, $del_recordids = false, $client_ipids)
		{
			if(empty($userid )){
				return false;
			}
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$cf_types = new FormTypes();
			$cf_types_arr = $cf_types->get_form_types($clientid);

			foreach($cf_types_arr as $k_cft => $v_cft)
			{
				$contact_form_types[$v_cft['id']] = $v_cft;
			}

			$start_date = date('Y-m-d', $start);
			$end_date = date('Y-m-d', $end);

			$selector = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->where('create_user="' . $userid . '" or change_user = "' . $userid . '"')
 				->andWhere('DATE(billable_date) BETWEEN ? AND ?', array($start_date,$end_date))
				->andwhere('isdelete="0"')
				->andWhereIn('ipid', $client_ipids);
			if($del_recordids)
			{
				$selector->andWhereNotIn('id', $del_recordids);
			}

			$results = $selector->fetchArray();

			foreach($results as $k_res => $v_res)
			{
				$results_arr[$k_res] = $v_res;
				$results_arr[$k_res]['form_type_name'] = $contact_form_types[$v_res['form_type']]['name'];
			}

			return $results_arr;
		}

		public function get_pat_calendar_contact_form($ipid, $userid = false, $start, $end, $del_recordids = false)
		{
			if(empty($ipid)){
				return false;
			}
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$cf_types = new FormTypes();
			$cf_types_arr = $cf_types->get_form_types($clientid);

			foreach($cf_types_arr as $k_cft => $v_cft)
			{
				$contact_form_types[$v_cft['id']] = $v_cft;
			}

			$start_date = date('Y-m-d',  strtotime($start));
			$end_date = date('Y-m-d',  strtotime($end));

			$selector = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->where('ipid = ?', $ipid )
// 				->andWhere('DATE(billable_date) BETWEEN ? AND ?', array($start_date,$end_date))
				->andwhere('isdelete= ?',0);
			if($userid)
			{
				$selector->andWhere('create_user="' . $userid . '" or change_user = "' . $userid . '"');
			}

			if($del_recordids)
			{
				$selector->andWhereNotIn('id', $del_recordids);
			}

			$results = $selector->fetchArray();

			foreach($results as $k_res => $v_res)
			{
				$results_arr[$k_res] = $v_res;
				$results_arr[$k_res]['form_type_name'] = $contact_form_types[$v_res['form_type']]['name'];
				$results_arr[$k_res]['form_type_color'] = $contact_form_types[$v_res['form_type']]['calendar_color'];
				$results_arr[$k_res]['form_type_text_color'] = $contact_form_types[$v_res['form_type']]['calendar_text_color'];
			}

			return $results_arr;
		}

		public function checkContactFormsByUser($user_id, $start_date, $end_date, $edit_id)
		{
			$contact_form = Doctrine_Query::create()
				->select("ipid,start_date,end_date")
				->from('ContactForms')
				->where("create_user = ?", $user_id )
				->andWhere("parent = ?",0);
			$contact_form->andWhere('( start_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" ) OR ( end_date BETWEEN "' . $start_date . '" AND "' . $end_date . '" )');

			if($edit_id)
			{
				$contact_form->andWhere(' id <> "' . $edit_id . '"');
			}
			$contact_form_array = $contact_form->fetchArray();

			return $contact_form_array;
		}

		public function deleteContactForm($cf_id, $ipid)
		{
			$del_cform = Doctrine::getTable('ContactForms')->findOneByIdAndIpid($cf_id, $ipid);
			$del_cform->isdelete = '1';
			$del_cform->save();


			if($del_cform)
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		public function get_contact_form_period_sgbvxi($ipid, $period =  false, $excluded_cf = false)
		{
			if(empty($ipid)){
				return false;
			}
			
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->where('ipid= ?', $ipid)
				->andwhere('isdelete= ?', 0)
				->andWhere('sgbxi_quality = ?', 1);
			if($period)
			{
				$start_date = date('Y-m-d',  strtotime($period['start']));
				$end_date = date('Y-m-d',   strtotime($period['end']));
				$select->andWhere('DATE(billable_date) BETWEEN ? AND ?', array($start_date,$end_date));
			}

			if($excluded_cf)
			{
				$select->andWhereNotIn('id', $excluded_cf);
			}
			$select_res = $select->fetchArray();

			if($select_res)
			{
				return $select_res;
			}
			else
			{
				return false;
			}
		}

		public function get_multiple_contact_form_report_period($ipids, $report_period, $excluded_cf = false)
		{
			if(empty($ipids)){
				return false;
			}
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->whereIn('ipid', $ipids)
				->andwhere('isdelete= ?',0);

			if(is_array($report_period))
			{
				$date_sql = " ";
				foreach($report_period as $k => $date)
				{
					$start_date_time = date('Y-m-d H:i:s', strtotime($date['start']));
					$end_date_time = date('Y-m-d H:i:s', strtotime($date['end']));
					$date_sql .= ' ( DATE(billable_date) BETWEEN DATE("' . $start_date_time . '") AND  DATE("' . $end_date_time . '") )  OR ';
				}

				$select->andWhere('' . substr($date_sql, 0, -4) . '');
			}
			else
			{
				$start_date = date('Y-m-d H:i:s', strtotime($start_date));
				$select->andWhere('YEAR("' . $start_date . '") = YEAR(`date`)');
				$select->andWhere('MONTH("' . $start_date . '") = MONTH(`date`)');
			}

			if($excluded_cf)
			{
				$select->andWhereNotIn('id', $excluded_cf);
			}

			$select_res = $select->fetchArray();

			if($select_res)
			{
				foreach($select_res as $k_cf => $v_cf)
				{
					$contact_forms[$v_cf['ipid']][] = $v_cf;
				}

				return $contact_forms;
			}
			else
			{
				return false;
			}
		}

		public function get_internal_invoice_contactforms($ipid, $excluded = false, $period = false)
		{

			if(empty($ipid)){
				return false;
			}
			
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->where('ipid = ?', $ipid)
				->andWhere('isdelete = ?', 0)
				->andWhere('free_visit = ?', 0);
			if($excluded)
			{
				$select->andWhereNotIn('id', $excluded);
			}

			if($period)
			{
				$select->andWhere('DATE(date) BETWEEN ? and ? ',array($period['start'],$period['end']));
			}

			$select->orderBy('start_date ASC');
			$select_res = $select->fetchArray();
			
			if($select_res)
			{
				$cf_ids[] = '99999999999';
				foreach($select_res as $k_cf => $v_cf)
				{
					$result[$v_cf['id']] = $v_cf;
					$result[$v_cf['id']]['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates( $v_cf['start_date'], $v_cf['end_date']);
					$cf_ids[] = $v_cf['id'];
				}

				$block_aditional_users = new FormBlockAdditionalUsers();
				$aditional_users = $block_aditional_users->getPatientFormBlockAdditionalUsers($ipid, $cf_ids);

				if($aditional_users)
				{
					foreach($aditional_users as $k_auser => $v_auser)
					{
						$result[$v_auser['contact_form_id']]['aditional_users'][] = $v_auser['additional_user'];
					}
				}

				return $result;
			}
			else
			{
				return false;
			}
		}

		public function get_action_contact_forms($ipid = false, $client = false, $action = false, $deleted_contact_forms)
		{
			if($ipid && $client && $action)
			{
				//get maste form types by action
				$form_types = new FormTypes();
				$form_types_actions = $form_types->get_form_types($client, $action);

				$form_type_ids[] = '99999999999';
				foreach($form_types_actions as $k_ft => $v_ft)
				{
					$form_type_ids[] = $v_ft['id'];
				}

				//get all contactform for all form_types
				$last_contact_form_filled = $this->get_last_contact_form_type($ipid, $form_type_ids, $deleted_contact_forms);

				if(!empty($last_contact_form_filled))
				{
					return $last_contact_form_filled;
				}
				else
				{
					return false;
				}
			}
		}

		private function get_last_contact_form_type($ipid, $form_types = false, $deleted_contact_forms = false)
		{
			if(empty($ipid)){
				return false;
			}
			
			if(is_array($form_types))
			{
				$form_types_ids = $form_types;
			}
			else
			{
				$form_types_ids[] = array($form_types);
			}
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->whereIn('form_type', $form_types_ids)
				->andWhere('ipid = ? ', $ipid)
				->andWhere('parent = ?', 0)
				->orderBy('start_date ASC');
			if($deleted_contact_forms)
			{
				$select->andWhereNotIn('id', $deleted_contact_forms);
			}
			$select_res = $select->fetchArray();

			if($select_res)
			{
				return end($select_res);
			}
			else
			{
				return false;
			}
		}

		/**
		 * @deprecated, use ContactForms::get_deleted_contactforms_by_ipid
		 */
		public function get_deleted_contactforms($ipid)
		{
			$deleted_cf = Doctrine_Query::create()
				->select("id,ipid,recordid,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('wrong=1')
				->andWhere("ipid= ? ", $ipid)
				->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
				->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'");
			$deleted_cf_array = $deleted_cf->fetchArray();

			$excluded_cf_ids[] = '99999999999';
			foreach($deleted_cf_array as $k_dcf => $v_dcf)
			{
				$excluded_cf_ids[] = $v_dcf['recordid'];
			}

			return $excluded_cf_ids;
		}

		public function get_internal_invoice_contactforms_multiple($ipids, $excluded = false, $period = false)
		{
			if(empty($ipids)){
				return false;
			}

			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->whereIn('ipid', $ipids_arr)
				->andWhere('isdelete="0"')
				->andWhere('free_visit="0"');
			if($excluded)
			{
				$select->andWhereNotIn('id', $excluded);
			}

			if($period)
			{
				foreach($period['start'] as $k_period => $v_period)
				{
					$sql_period[] = ' DATE(billable_date) BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") ';
				}
				$select->andWhere(implode(' OR ', $sql_period));
			}

//			print_r($select->getSqlQuery());exit;
			$select->orderBy('start_date ASC');
			$select_res = $select->fetchArray();
//			print_r($select_res);

			if($select_res)
			{
				$cf_ids[] = '99999999999';
				foreach($select_res as $k_cf => $v_cf)
				{
					$result[$v_cf['id']] = $v_cf;
					$cf_ids[] = $v_cf['id'];
				}

				$block_aditional_users = new FormBlockAdditionalUsers();
				$aditional_users = $block_aditional_users->getPatientFormBlockAdditionalUsers($ipid, $cf_ids);

				if($aditional_users)
				{
					foreach($aditional_users as $k_auser => $v_auser)
					{
						$result[$v_auser['contact_form_id']]['aditional_users'][] = $v_auser['additional_user'];
					}
				}

				return $result;
			}
			else
			{
				return false;
			}
		}

		//$search = array("db_field_name"="searched_value");
		/**
		 * 
		 * @param string $ipid
		 * @param string $deleted_form_ids
		 * @param string $search
		 * @return boolean|string
		 */
		public function get_patient_contactforms($ipid, $deleted_form_ids = false, $search = false)
		{
			if(empty($ipid)){
				return false;
			}
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$form_types = new FormTypes();

			$contact_form_types = $form_types->get_form_types($clientid);


			foreach($contact_form_types as $k_form_type => $v_form_type)
			{
				$contact_form_types_final[$v_form_type['id']] = $v_form_type;
			}

			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->where('ipid = ? ', $ipid)
				->andwhere('isdelete="0"');

			if($deleted_form_ids && !empty($deleted_form_ids))
			{
				$select->andWhereNotIn('id', $deleted_form_ids);
			}

			if($search)
			{
				$search_fields = array_keys($search);
				$search_arr = false;
				$search_sql = '';
				foreach($search_fields as $k_search => $v_search)
				{
					$search_arr[] = 'DATE(`' . $v_search . '`) = DATE("' . date('Y-m-d H:i:s', strtotime($search[$v_search])) . '")';
				}

				if($search_arr)
				{
					$search_sql = implode(" OR ", $search_arr);
				}
				$select->andWhere($search_sql);
			}
			$select->orderBy('start_date ASC');
			$select_res = $select->fetchArray();

			foreach($select_res as $k_res => $v_res)
			{
				$v_res['form_type'] = $contact_form_types_final[$v_res['form_type']]['name'];
				//contact form
				$v_res['visit_form_type'] = 'cf';

				$cforms_res[] = $v_res;
			}

			if($cforms_res)
			{
				return $cforms_res;
			}
			else
			{
				return false;
			}
		}

		public function get_period_contact_forms($ipid, $current_period, $duration = false)
		{
			if(empty($ipid)){
				return false;
			}
			$contact_from_course = Doctrine_Query::create()
				->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('ipid = ?', $ipid)
				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("wrong = 1")
				->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
				->orderBy('course_date ASC');

			$contact_v = $contact_from_course->fetchArray();

			$deleted_contact_forms[] = '9999999999999999';
			foreach($contact_v as $k_contact_v => $v_contact_v)
			{
				$deleted_contact_forms[] = $v_contact_v['recordid'];
			}

			$contact_form_visits = Doctrine_Query::create()
				->select("*")
				->from("ContactForms")
				->where('ipid = ?', $ipid);
				if(!empty($deleted_contact_forms)){
					$contact_form_visits ->andWhereNotIn('id', $deleted_contact_forms);
				}
				$contact_form_visits ->andWhere('DATE(billable_date) BETWEEN ? AND ?', array(date("Y-m-d",strtotime($current_period['start'])),date("Y-m-d",strtotime($current_period['end'])) ))
				->andWhere('isdelete = ?', 0)
				->andWhere('parent = ?', 0);
			$contact_form_visits->orderBy('begin_date_h, begin_date_m ASC');
			$contact_form_visits_res = $contact_form_visits->fetchArray();

			foreach($contact_form_visits_res as $k_contact_visit => $v_contact_visit)
			{
				$contact_form_visit_date = date('Y-m-d', strtotime($v_contact_visit['billable_date']));

				if($duration)
				{
// 					$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_duration(str_pad($v_contact_visit['begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_m'], 2, "0", STR_PAD_LEFT), $v_contact_visit['date']);
					$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates( $v_contact_visit['start_date'], $v_contact_visit['end_date']);
				}

				$cf_visit_days[$contact_form_visit_date][] = $v_contact_visit;
			}

			return $cf_visit_days;
		}

		public function get_sh_period_contact_forms($ipid, $current_period = false, $duration = false, $individual_period_days = false)
		{
			if(is_array($ipid))
			{
				$ipids_arr = $ipid;
			}
			else
			{
				$ipids_arr = array($ipid);
			}

			$contact_from_course = Doctrine_Query::create()
				->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->whereIn('ipid', $ipids_arr)
// 				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("course_type = AES_ENCRYPT('F','" . Zend_Registry::get('salt') . "')" )
				->andWhere("wrong = 1")
// 				->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
				->andWhere("tabname = AES_ENCRYPT('contact_form','" . Zend_Registry::get('salt') . "')")
				->orderBy('course_date ASC');
			$contact_v = $contact_from_course->fetchArray();

			$deleted_contact_forms[] = '9999999999999999';
			foreach($contact_v as $k_contact_v => $v_contact_v)
			{
				$deleted_contact_forms[] = $v_contact_v['recordid'];
			}

			$contact_form_visits = Doctrine_Query::create()
				->select("*")
				->from("ContactForms")
//				->where('ipid = "' . $ipid . '"')
				->whereIn('ipid', $ipids_arr)
				->andWhereNotIn('id', $deleted_contact_forms)
				->andWhere('isdelete ="0"')
				->andWhere('parent ="0"');
			$contact_form_visits->orderBy('begin_date_h, begin_date_m ASC');

			if($current_period)
			{
				$contact_form_visits->andWhere('DATE(billable_date) BETWEEN ? AND ?', array(date("Y-m-d",strtotime($current_period['start'])),date("Y-m-d",strtotime($current_period['end'])) ));
			}

			$contact_form_visits_res = $contact_form_visits->fetchArray();

			foreach($contact_form_visits_res as $k_contact_visit => $v_contact_visit)
			{
				$contact_form_visit_date = date('Y-m-d', strtotime($v_contact_visit['billable_date']));

				if($duration)
				{
// 					$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_duration(str_pad($v_contact_visit['begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_m'], 2, "0", STR_PAD_LEFT), $v_contact_visit['date']);
					$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$v_contact_visit['end_date']);
				}

				if($individual_period_days)
				{
					if(in_array(date('d.m.Y', strtotime($v_contact_visit['billable_date'])), $individual_period_days[$v_contact_visit['ipid']]))
					{
//						$cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date][] = $v_contact_visit;
						$cf_visit_days[$contact_form_visit_date][] = $v_contact_visit;
					}
				}
				else
				{
					$cf_visit_days[$contact_form_visit_date][] = $v_contact_visit;
				}
			}

			return $cf_visit_days;
		}

		
		public function get_multiple_contact_form_by_periods($ipids, $report_period, $excluded_cf = false, $duration=false)
		{
			$select = Doctrine_Query::create()
				->select('*,comment as comment_block')
				->from('ContactForms')
				->whereIn('ipid', $ipids)
				->andwhere('isdelete="0"');

			if(is_array($report_period) )
			{
			    if(!empty($report_period)){
			        
    				$date_sql = " ";
    				$start_date ="";
    				$end_date ="";
    				$date_sql = "";
    				foreach($report_period as $k => $date)
    				{
    					$start_date = date('Y-m-d', strtotime($date['start']));
    					$end_date = date('Y-m-d', strtotime($date['end']));
    					$date_sql .= ' ( DATE(billable_date) BETWEEN DATE("' . $start_date . '") AND  DATE("' . $end_date . '") )  OR ';
    				}
    
    				$select->andWhere('(' . substr($date_sql, 0, -4) . ')');
			    }
			}
			else
			{
// 				$start_date = date('Y-m-d H:i:s', strtotime($report_period));
// 				$select->andWhere('YEAR("' . $start_date . '") = YEAR(`date`)');
// 				$select->andWhere('MONTH("' . $start_date . '") = MONTH(`date`)');
			}

			if($excluded_cf)
			{
				$select->andWhereNotIn('id', $excluded_cf);
			}

			$select->orderBy('start_date ASC');
			$select_res = $select->fetchArray();

			if($select_res)
			{
				foreach($select_res as $k_cf => $v_cf)
				{
					$contact_forms[$v_cf['ipid']][$v_cf['id']] = $v_cf;
					if($duration)
					{
// 					    $contact_forms[$v_cf['ipid']][$v_cf['id']]['visit_duration'] = Pms_CommonData::calculate_visit_duration(str_pad($v_cf['begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_cf['end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_cf['begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_cf['end_date_m'], 2, "0", STR_PAD_LEFT), $v_cf['date']);
					    $contact_forms[$v_cf['ipid']][$v_cf['id']]['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_cf['start_date'],$v_cf['end_date']);
					}
				}

				return $contact_forms;
			}
			else
			{
				return false;
			}
		}

		public function get_all_client_form_types($clientid)
		{
			if($clientid)
			{
				$form_types = new FormTypes();

				$set_one = $form_types->get_form_types($clientid, '1');
				foreach($set_one as $k_set_one => $v_set_one)
				{
					$set_one_ids[] = $v_set_one['id'];
				}

				$set_two = $form_types->get_form_types($clientid, '2');
				foreach($set_two as $k_set_two => $v_set_two)
				{
					$set_two_ids[] = $v_set_two['id'];
				}

				$set_three = $form_types->get_form_types($clientid, '3');
				foreach($set_three as $k_set_three => $v_set_three)
				{
					$set_three_ids[] = $v_set_three['id'];
				}

				$set_fourth = $form_types->get_form_types($clientid, '4');
				foreach($set_fourth as $k_set_fourth => $v_set_fourth)
				{
					$set_fourth_ids[] = $v_set_fourth['id'];
				}

				$set_ids['one'] = $set_one_ids;
				$set_ids['two'] = $set_two_ids;
				$set_ids['three'] = $set_three_ids;
				$set_ids['fourth'] = $set_fourth_ids;

				return $set_ids;
			}
			else
			{
				return false;
			}
		}
		
		
		
		public function get_patients_deleted_contactforms($ipids)
		{
		    if(!is_array($ipids))
		    {
		        $ipids = array($ipids);
		    }
		    else
		    {
		        $ipids = $ipids;
		    }
		
		    $deleted_cf = Doctrine_Query::create()
		    ->select("id,ipid,recordid")
		    ->from('PatientCourse')
		    ->where('wrong=1')
		    ->andWhereIn("ipid",$ipids)
		    ->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
		    ->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'");
		    $deleted_cf_array = $deleted_cf->fetchArray();
		
		    $excluded_cf_ids[] = '99999999999';
		    foreach($deleted_cf_array as $k_dcf => $v_dcf)
		    {
		        if( ! empty($v_dcf['recordid']) ){
    		        $excluded_cf_ids[$v_dcf['ipid']][] = $v_dcf['recordid'];
		        }
		    }
		
		    return $excluded_cf_ids;
		}
		
		/**
		 * this return the cf's that are marked as wrong in PatientCourse 
		 * and also the ones that have isdelete = 1
		 * 
		 * @param unknown $ipids
		 * @param string $indexByIpid
		 * @return void|Ambigous <multitype:, Doctrine_Collection>
		 */
		public static function get_deleted_contactforms_by_ipid($ipids = array(), $indexByIpid = false)
		{
			if (empty($ipids)) {
				return;
			}
			
			if ( ! is_array($ipids)) {
				$ipids = array($ipids);
			}

			$excluded_cf_ids = array();
			
			$wrong_cf_array = Doctrine_Query::create()
			->select("id,ipid,recordid")
			->from('PatientCourse')
			->whereIn("ipid", $ipids)
			->andWhere('course_type = ? ',  Pms_CommonData::aesEncrypt("F") ) // TODO replace with the reverse logic if problems
			->andWhere("tabname = ? " , Pms_CommonData::aesEncrypt('contact_form') )// TODO replace with the reverse logic if problems
			->andWhere('source_ipid = \'\'') // do not include from shared patients
			->andWhere('wrong = 1')
			->fetchArray()
            ;
			if ( ! empty($wrong_cf_array)) {
    			if ($indexByIpid) {
    				foreach($wrong_cf_array as $k_dcf => $v_dcf) {
    					$excluded_cf_ids[$v_dcf['ipid']][] = $v_dcf['recordid'];
    				}
    			} else {
    				$excluded_cf_ids = array_column($wrong_cf_array, 'recordid');
    			}
			}
			
			$deleted_cf_array = Doctrine_Query::create()
			->select("id, ipid")
			->from('ContactForms')
			->whereIn("ipid", $ipids)
			->andWhere('isdelete = 1')
			->fetchArray();
			if ( ! empty($wrong_cf_array)) {
    			if ($indexByIpid) {
    			    foreach($deleted_cf_array as $k_dcf => $v_dcf) {
    			        $excluded_cf_ids[$v_dcf['ipid']][] = $v_dcf['id'];
    			    }
    			} else {
    			    $excluded_cf_ids = array_merge($excluded_cf_ids, array_column($deleted_cf_array, 'id'));
    			}
			}
		
			return $excluded_cf_ids;
		}
		

		
		public function get_contactforms_multiple($ipids = false, $period = false, $free_visit = false)
		{
		
			if( empty($ipids)){
				return false;
			}
		    if(is_array($ipids))
		    {
		        $ipids_arr = $ipids;
		    }
		    else
		    {
		        $ipids_arr = array($ipids);
		    }
		    
			$contact_from_course = Doctrine_Query::create()
				->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->whereIn('ipid', $ipids_arr)
				->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
				->andWhere("wrong = 1")
				->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
				->orderBy('course_date ASC');
			$contact_v = $contact_from_course->fetchArray();

			foreach($contact_v as $k_contact_v => $v_contact_v)
			{
				$deleted_contact_forms[] = $v_contact_v['recordid'];
			}
		    
		    $select = Doctrine_Query::create()
		    ->select('*,comment as comment_block')
		    ->from('ContactForms')
		    ->whereIn('ipid', $ipids_arr)
		    ->andWhere('isdelete="0"');
		    if($free_visit){
			    $select->andWhere('free_visit="0"');
		    }
		    if( ! empty($deleted_contact_forms)){
    	        $select->andWhereNotIn('id', $deleted_contact_forms);
		    }
		
		    if($period)
		    {
		        foreach($period['start'] as $k_period => $v_period)
		        {
		            $sql_period[] = ' DATE(billable_date) BETWEEN DATE("' . date('Y-m-d H:i:s', strtotime($v_period)) . '") AND DATE("' . date('Y-m-d H:i:s', strtotime($period['end'][$k_period])) . '") ';
		        }
		        $select->andWhere(implode(' OR ', $sql_period));
		    }
		
		    $select->orderBy('start_date ASC');
		    $select_res = $select->fetchArray();
		
		    if($select_res)
		    {
		        foreach($select_res as $k_cf => $v_cf)
		        {
		            $result[$v_cf['id']] = $v_cf;
		        }
 
		        return $result;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		
		
		
		
		
		function get_period_contact_forms_special($ipid, $current_period, $sgbxi = false, $duration = false)
		{
			$contact_from_course = Doctrine_Query::create()
			->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
			->from('PatientCourse')
			->where('ipid = ?', $ipid)
			->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
			->andWhere("wrong = 1")
			->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
			->orderBy('course_date ASC');
		
			$contact_v = $contact_from_course->fetchArray();
		
			$deleted_contact_forms[] = '9999999999999999';
			foreach($contact_v as $k_contact_v => $v_contact_v)
			{
				$deleted_contact_forms[] = $v_contact_v['recordid'];
			}
		
			$contact_form_visits = Doctrine_Query::create()
			->select("*")
			->from("ContactForms")
			->where('ipid = ?',$ipid)
			->andWhereNotIn('id', $deleted_contact_forms)
			->andWhere('DATE(billable_date) BETWEEN ? AND ?',array($current_period['start'],$current_period['end']))
			->andWhere('isdelete ="0"')
			->andWhere('parent ="0"');
		
			if($sgbxi)
			{
				$contact_form_visits->andWhere('sgbxi_quality = "1"');
			}
		
			$contact_form_visits->orderBy('begin_date_h, begin_date_m ASC');
			$contact_form_visits_res = $contact_form_visits->fetchArray();
		
			foreach($contact_form_visits_res as $k_contact_visit => $v_contact_visit)
			{
		
				if(!$sgbxi)
				{
					$contact_form_visit_date = date('Y-m-d', strtotime($v_contact_visit['billable_date']));
		
					if($duration)
					{
// 						$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_duration(str_pad($v_contact_visit['begin_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_h'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['begin_date_m'], 2, "0", STR_PAD_LEFT), str_pad($v_contact_visit['end_date_m'], 2, "0", STR_PAD_LEFT), $v_contact_visit['date']);
						$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$v_contact_visit['end_date']);
					}
					$cf_visit_days[$contact_form_visit_date][] = $v_contact_visit;
		
					$cf_visit_days[$contact_form_visit_date]['form_types'][] = $v_contact_visit['form_type'];
					$cf_visit_days[$contact_form_visit_date]['form_types'] = array_unique($cf_visit_days[$contact_form_visit_date]['form_types']);
				}
				else
				{
					$cf_visit_days[$v_contact_visit['id']] = $v_contact_visit;
				}
			}
		
			return $cf_visit_days;
		}

		
	/**
	 * backtrace form history.. traceHistory uses this function
	 * parents= older forms, are marked with isdelete=1
	 * @param unknown $ids
	 */
    private static function _fetchIdFormAndParents (array $ids =  array()) 
    {
        if (empty($ids)) {
            return array(); //empty array to be sure
        }
        
        $qr = Doctrine_Query::create()->from('ContactForms INDEXBY id')        
//         $qr = $this->getTable()->createQuery('cfh INDEXBY id') 
        ->select('id , parent')
        ->whereIn('id',  $ids) 
        ->orWhereIn('parent', $ids)
        ->fetchArray();
        
        
        $new_parents = array();
        $new_ids = array();
        
        foreach ($qr as $row) {
            if ($row['parent'] > 0 && ! in_array($row['parent'], $ids)) {
                //must fetch this also
                $new_parents[] = $row['parent'];
            } elseif ( ! in_array($row['id'], $ids)) {
                //must fetch this also
                $new_ids[] = $row['id'];// last update of the cf, or ch has no children
            }
        }
        
        if ( ! empty($new_parents) || ! empty($new_ids)) {

            $all_ids = array_merge($ids, $new_parents, $new_ids);
            //this should happen only once
            $ids = array_merge($ids, self::_fetchIdFormAndParents($all_ids));
            
        }
        
        return array_unique($ids);
        
    }
    
    /**
     * @author claudiu
     * 
     * @param array $ids
     * @return void|multitype:
     */
    public static function traceHistory($ids =  array()) 
    {
        if (empty($ids) || ! is_array($ids)) {
            return;
        }
        
        $all_ids = self::_fetchIdFormAndParents($ids);
        
        $qr = Doctrine_Query::create()->from('ContactForms INDEXBY id')
//         $qr = $this->getTable()->createQuery('cfh INDEXBY id')
        ->select('id , parent, form_type')
        ->whereIn('id',  $all_ids)
        ->fetchArray();
        
        $cf_trace = array();
        $cf_form_type = array();
        
        foreach ($qr as $row) {
            
            $cf_form_type[$row['id']] = $row['form_type'];
            
            if($row['parent'] == 0) {
                $cf_trace[$row['id']][] =  $row['id'];
            } else {
                $cf_trace[$row['parent']][] =  $row['id'];
            }
        }
        foreach ($cf_trace as &$cf) {
            sort($cf);
        }
        
        return ['_trace' => $cf_trace, '_form_type' => $cf_form_type];
        
    }
    
   
    /**
     * get the id's of the older forms that are now isdelete=1 and have been edited/replaced with a newer parent!=0
     * 
     * @param string  $ipids
     * @return void|multitype:number |Ambigous <NULL, multitype:>
     */
    public static function fetchReEditedForms($ipid = '')
    {
        if ( empty($ipid) || ! is_string($ipid)) {
            return;
        }
        
        $q_r = Doctrine_Query::create()->from('ContactForms INDEXBY id')
//         $q_r = $this->getTable()->createQuery()
        ->select('id')
        ->where('ipid = ?', $ipid)
        ->andWhere('parent != 0')
        ->andWhere('isdelete = 1')
        ->fetchArray()
        ;
        
        return ( ! empty($q_r)) ? array_column($q_r, 'id') : null;
        
       
    }
		
    
    /**
     * @author claudiu on 01.03.2018 for ISPC-2161
     * returns the one with MAX(start_date)
     * 
     * @param unknown $ipids
     * @return void|multitype:unknown
     */
    public static function get_last_contactform( $ipids = array())
    {
        if (empty($ipids)){
            return;
        }
         
        if ( ! is_array($ipids)) {
            $ipids = array($ipids);
        }
         
        $result = array();

       
        $deleted_cfs =  self::get_deleted_contactforms_by_ipid($ipids);
        if ( ! empty($deleted_cfs)) { //exclude the wrong and deleted ones
            
            $placeholder_deleted_cfs = str_repeat ('?, ',  count ($deleted_cfs) - 1) . '?';
            
            $q_deleted_cf = "AND cf.id NOT IN ({$placeholder_deleted_cfs})";
            $q_deleted_cfcf = "AND id NOT IN ({$placeholder_deleted_cfs})";

        } else {
            
            $deleted_cfs = array();
            $placeholder_deleted_cfs = '';
            $q_deleted_cf = '';
            $q_deleted_cfcf = '';
            
        }
        
        $placeholder_ipids = str_repeat ('?, ',  count ($ipids) - 1) . '?';
         
        $querystr = "SELECT cf.* FROM contact_forms cf
        INNER JOIN 
        (SELECT MAX(start_date) AS max_start_date, ipid FROM contact_forms WHERE ipid IN ({$placeholder_ipids}) {$q_deleted_cfcf} AND isdelete = 0 GROUP BY ipid) cfcf
        ON cf.ipid = cfcf.ipid
        AND cf.start_date = cfcf.max_start_date
        AND cf.isdelete = 0
        {$q_deleted_cf}
        ";
                     
        $conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
        $query = $conn->prepare($querystr);
        $query->execute(array_merge($ipids, $deleted_cfs, $deleted_cfs));
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
         
        foreach ($res as $row) {
            $result[$row['ipid']] = $row;
        }
                     
        return $result;
    }
    

    /**
     * @author Ancuta on 29.03.2018 for TODO-1466
     * @param unknown $ipids
     * @param unknown $current_period
     * @param string $sgbxi
     * @return boolean|Ambigous <multitype:, Doctrine_Collection>
     */
    public function get_patients_period_cf($ipids, $current_period, $sgbxi = false)
    {
    	if(empty($ipids)){
    		return false;
    	}
    	if(is_array($ipids))
    	{
    		$ipids_arr = $ipids;
    	}
    	else
    	{
    		$ipids_arr[] = $ipids;
    	}
    
    	$contact_from_course = Doctrine_Query::create()
    	->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
    	->from('PatientCourse')
    	->whereIn('ipid', $ipids_arr)
    	->andWhere("AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') = 'F'")
    	->andWhere("wrong = 1")
    	->andWhere("AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') = 'contact_form'")
    	->andWhere('source_ipid = ""')
    	->orderBy('course_date ASC');
    	$contact_v = $contact_from_course->fetchArray();
    
    	foreach($contact_v as $k_contact_v => $v_contact_v)
    	{
    		$deleted_contact_forms[] = $v_contact_v['recordid'];
    	}
    
    	foreach($ipids_arr as $k_ipid => $v_ipid)
    	{
    		$sql_w[] = ' (`ipid` LIKE "%' . $v_ipid . '%" AND DATE(`billable_date`) BETWEEN DATE("' . date("Y-m-d", strtotime($current_period[$v_ipid]['start'])) . '") AND DATE("' . date("Y-m-d", strtotime($current_period[$v_ipid]['end'])) . '")) ';
    	}
    
    
    	$contact_form_visits = Doctrine_Query::create()
    	->select("*")
    	->from("ContactForms")
    	->where('isdelete ="0"');
    	if(!empty($deleted_contact_forms)){
    		$contact_form_visits->andWhereNotIn('id', $deleted_contact_forms);
    	}
    	$contact_form_visits->andWhere('parent ="0"');
    	if(count($sql_w))
    	{
    		$contact_form_visits->andWhere(implode("OR", $sql_w));
    	}
    
    	if($sgbxi)
    	{
    		$contact_form_visits->andWhere('sgbxi_quality = "1"');
    	}
    
    	$contact_form_visits->orderBy('begin_date_h, begin_date_m ASC');
    	$contact_form_visits_res = $contact_form_visits->fetchArray();
    
    
    	foreach($contact_form_visits_res as $k_contact_visit => $v_contact_visit)
    	{
    		if(!$sgbxi)
    		{
    			$contact_form_visit_date = date('Y-m-d', strtotime($v_contact_visit['billable_date']));
    
    			$cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date][] = $v_contact_visit;
    
    			$cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date]['form_types'][$v_contact_visit['id']] = $v_contact_visit['form_type'];
    			$cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date]['form_types'] = array_unique($cf_visit_days[$v_contact_visit['ipid']][$contact_form_visit_date]['form_types']);
    		}
    		else
    		{
    			$cf_visit_days[$v_contact_visit['ipid']][$v_contact_visit['id']] = $v_contact_visit;
    		}
    	}
    
    	return $cf_visit_days;
    }
    
    /**
     * created by carmen on 17.01.2019 for ISPC - 2296
     * returns the one with MAX(start_date) or the one with MAX(start_date) that contain a specified block
     *
     * @param unknown $ipids
     * @return void|multitype:unknown
     */
    public static function get_last_contactform_by_block( $ipids = array(), $clientid=false, $has_block=false)
    {
    	if (empty($ipids)){
    		return;
    	}
    	 
    	if ( ! is_array($ipids)) {
    		$ipids = array($ipids);
    	}
    	
    	if($clientid && $has_block)
    	{
    		$fbb = new FormBlocks2Type();
    		$fblocks = $fbb->get_form_types_blocks_special($clientid, 0, array($has_block));
    		
    		$cftypes = array();
    		foreach($fblocks as $fb)
    		{
    			$cftypes[] = $fb['form_type'];
    		}
    		
    		if ( ! empty($cftypes)) {
    			 
    			$placeholder_cftypes = str_repeat ('?, ',  count ($cftypes) - 1) . '?';
    		}
    	}
    	 
    	$result = array();
    
    	 
    	$deleted_cfs =  self::get_deleted_contactforms_by_ipid($ipids);
    	if ( ! empty($deleted_cfs)) { //exclude the wrong and deleted ones
    
    		$placeholder_deleted_cfs = str_repeat ('?, ',  count ($deleted_cfs) - 1) . '?';
    
    		$q_deleted_cf = "AND cf.id NOT IN ({$placeholder_deleted_cfs})";
    		$q_deleted_cfcf = "AND id NOT IN ({$placeholder_deleted_cfs})";
    
    	} else {
    
    		$deleted_cfs = array();
    		$placeholder_deleted_cfs = '';
    		$q_deleted_cf = '';
    		$q_deleted_cfcf = '';
    
    	}
    
    	$placeholder_ipids = str_repeat ('?, ',  count ($ipids) - 1) . '?';
    
    	if ( ! empty($cftypes)) {
        	$querystr = "SELECT cf.* FROM contact_forms cf
            INNER JOIN";
        	if($has_block)
        	{
        		$querystr .= " (SELECT MAX(start_date) AS max_start_date, ipid FROM contact_forms WHERE ipid IN ({$placeholder_ipids}) {$q_deleted_cfcf} AND isdelete = 0 AND form_type IN ({$placeholder_cftypes}) GROUP BY ipid) cfcf";
        	}
        	else
        	{
        		$querystr .= " (SELECT MAX(start_date) AS max_start_date, ipid FROM contact_forms WHERE ipid IN ({$placeholder_ipids}) {$q_deleted_cfcf} AND isdelete = 0 GROUP BY ipid) cfcf";
        	}
        	$querystr .= " ON cf.ipid = cfcf.ipid
        	AND cf.start_date = cfcf.max_start_date
        	AND cf.isdelete = 0
        	{$q_deleted_cf}
        	";
        
        	$conn = Doctrine_Manager::getInstance()->getConnection('MDAT');
        	$query = $conn->prepare($querystr);
        	//var_dump(array_merge($ipids, $deleted_cfs, $cftypes, $deleted_cfs)); exit;
        	$query->execute(array_merge($ipids, $deleted_cfs, $cftypes, $deleted_cfs));
        	
        	$res = $query->fetchAll(PDO::FETCH_ASSOC);
        	
        	foreach ($res as $row) {
        		$result[$row['ipid']] = $row;
        	}
    	} else {
    		$result =  array();
    	    
    	}
    	 
    	return $result;
    }
    


	 /**
     * Nico 18.03.2021
     * handy function to add recorddata for display in course
     * TODO-4035 Nico 12.04.2021
     */
    public static function add_recorddata($html, $block, $__formular){
        //$__formular from $data_post['__formular']

        if(!strlen($html)){
            //html is empty, no entry written
            return;
        }

        $record_color = (!empty($__formular['blocks2recorddata'][$block]['color'])) ? $__formular['blocks2recorddata'][$block]['color'] : "#000000";

        $coursetext = '<div class="rcd_'.$block.' pc_record_data" style="color:'.$record_color.'!important">';
        if($record_color=="000000"){
            $coursetext = '<div class="rcd_'.$block.' pc_record_data">';
        }
        $coursetext .= $html;
        $coursetext .= '</div>';


        $cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($__formular['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));

        $shortcut = $__formular['blocks2recorddata'][$block]['shortcut'];
        if(strlen($shortcut)){
            $rd=[
                'html'=>$coursetext,
            ];
            $json=json_encode($rd);
            //separate entry into its own user defined shortcut
            $custnew = new PatientCourse();
            $custnew->ipid = $cust->ipid;
            $custnew->course_date   = date("Y-m-d H:i:s", time());
            $custnew->course_type   = Pms_CommonData::aesEncrypt($shortcut);
            $custnew->course_title  = Pms_CommonData::aesEncrypt('');
            $custnew->recorddata    = $json;
            $custnew->tabname       = Pms_CommonData::aesEncrypt('contactform_usershortcut');
            $custnew->recordid      = $cust->recordid;
            $custnew->user_id       = $cust->user_id;
            $custnew->save();

        }else{
            //append to 'F'-entry's recorddata
            $cust->recorddata=$cust->recorddata . "<br/>".$coursetext;
            $cust->save();
        }


    }

    
}

?>