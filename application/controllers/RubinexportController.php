<?php
/**
 * ISPC-2895
 * @author Ancuta 20.05.2021
 *
 */
class RubinexportController  extends Pms_Controller_Action {
    public function init() {
	    
	}
	
	public function exportAction() {
	    
	    //ERROR REPORTING
// 	    ini_set('display_errors', 1);
// 	    ini_set('display_startup_errors', 1);
// 	    error_reporting(E_ALL);
	    
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		
		$client_details = Client::getClientDataByid ( $clientid );
		$user_details = User::getUserDetails ( $logininfo->userid );
		$client_users = User::get_client_users ( $clientid, '1' );
		$this->view->user_details = $user_details;
		$this->view->client_users = $client_users;

		
		$current_q = Pms_CommonData::get_dates_of_quarter ( 'current', null, "d.m.Y" );
		$this->view->period_start = $current_q ['start'];
		$this->view->period_end = $current_q ['end'];
		
		$module_obj = new Modules();
		$PatientMaster_obj = new PatientMaster();
		$SapvVerordnung_obj = new SapvVerordnung();
		
		if ($this->getRequest ()->isPost ()) {
		    
			
			if(!empty($_REQUEST['period']['start']) && !empty($_REQUEST['period']['end'])) {
			    
			    $selected_period['start'] = date('Y-m-d',strtotime($_REQUEST['period']['start']));
			    $selected_period['end'] = date('Y-m-d',strtotime($_REQUEST['period']['end']));
			    
			    $overall_period['start'] =  "2008-01-01";
			    $overall_period['end'] = date('Y-m-d'); 
			    
			    $sql = 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
			    $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
			    $sql .= 'convert(AES_DECRYPT(p.sex,"' . Zend_Registry::get('salt') . '") using latin1) as sex,';
			    $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
			    $sql .= 'convert(AES_DECRYPT(p.mobile,"' . Zend_Registry::get('salt') . '") using latin1) as mobile,';
			    $sql .= 'convert(AES_DECRYPT(p.birth_city,"' . Zend_Registry::get('salt') . '") using latin1) as birth_city,';
			    $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
			    $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
			    $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
			    $sql .= 'convert(AES_DECRYPT(p.height,"' . Zend_Registry::get('salt') . '") using latin1) as height,';
			    
			    $conditions_overall['periods'][0]['start'] = $overall_period['start'];
			    $conditions_overall['periods'][0]['start'] = $overall_period['start'];
			    $conditions_overall['periods'][0]['end'] = $overall_period['end'];
			    $conditions_overall['client'] = $clientid;
			    $patient_days_overall = Pms_CommonData::patients_days($conditions_overall,$sql);

			    $ipids_overall = array_keys($patient_days_overall);
			
			    $export_patients = array();
			    foreach($patient_days_overall as $pipid => $p_details ){
			        foreach($p_details['admission_days'] as $adm_Date){
			            if(Pms_CommonData::isintersected(date('Y-m-d',strtotime($adm_Date)), date('Y-m-d',strtotime($adm_Date)), $selected_period['start'],  $selected_period['end'] ) ){
			                $export_patients[$pipid] = $p_details;
			            }
			        }
			    }
			    $ipids = array_keys($export_patients);
			    
// 			    dd($ipids);
			    if(empty($ipids)){
			        $error_msg_div ='<div class="err">No patients to be exported</div>';
			        echo $error_msg_div;
			        
			        return;
			    }

			    // health insurance
			    $phealthinsurance = new PatientHealthInsurance();
			    $healthinsu_multi_array = $phealthinsurance->get_multiple_patient_healthinsurance($ipids, true);
			    
			    $missing_healthinsurance = array();
			    foreach($export_patients as $ipid => $p_data){
			        if(empty($healthinsu_multi_array[$ipid]) || empty($healthinsu_multi_array[$ipid]['insurance_no'])){
			            $missing_healthinsurance[] = $ipid;
			        }
			        $export_patients[$ipid]['health_insurance'] = !empty($healthinsu_multi_array[$ipid])  ? $healthinsu_multi_array[$ipid] : array();
                    
			        foreach($p_data['locations'] as $ploc_id =>$loc_data){
			            
			            if($loc_data['type'] == '5'){
			                foreach($loc_data['days'] as $lday ){
			                    $export_patients[$ipid]['home_location_days'][] = date('Y-m-d',strtotime($lday));
			                }
			            } else{
			                foreach($loc_data['days'] as $lday ){
			                    $export_patients[$ipid]['other_location_days'][] = date('Y-m-d',strtotime($lday));
			                }
			            }
			            
		                foreach($loc_data['days'] as $lday ){
		                    $export_patients[$ipid]['day2location'][date('Y-m-d',strtotime($lday))] = $loc_data['type_description'];
		                }
			        }
			    }
			    
			    $patient_master_obj = new PatientMaster();
			    //Select  Pflegegrade
			    $pms = new PatientMaintainanceStage();
			    $pms_array = $pms->get_multiple_patatients_mt_period($ipids,$overall_period['start'],$overall_period['end']);
			    
			    $stage2patient = array();
			    $stage_dates_H = array();
			    foreach($pms_array as $k=>$pms_data){
			        $stage2patient[$pms_data['ipid']][] = $pms_data;
			    }
// 			    dd($stage2patient);
			    foreach($stage2patient as $patient_ipid =>$st){
			        foreach($st as $ks => $stage_arr){
		                if($stage_arr['tilldate'] == "0000-00-00"){
		                    $stage_arr['tilldate'] = date('Y-m-d');
		                }
		                
			            if(strlen(trim($stage_arr['stage']))>0){

			            } else{
			                $stage_arr['stage'] = "no_stage";
			            }
			            
			            if($stage_arr['horherstufung'] == '1'){
			                
    			            if(!is_array($stage_dates_H[$patient_ipid])){
    			                $stage_dates_H[$patient_ipid] = array();
    			            }
    			            
    			            if($stage_arr['h_fromdate'] == "0000-00-00"){
    			                $stage_arr['h_fromdate'] = $stage_arr['fromdate'];
    			            }
    			                
    			            $stage_dates_H[$patient_ipid] = array_merge($stage_dates_H[$patient_ipid],$patient_master_obj->getAllDaysInBetween($stage_arr['fromdate'],$stage_arr['tilldate']));
			            }
			            
			            if(!is_array($stage_dates[$patient_ipid][$stage_arr['stage']])){
			                $stage_dates[$patient_ipid][$stage_arr['stage']] = array();
			            }
			            $stage_dates[$patient_ipid][$stage_arr['stage']] = array_merge($stage_dates[$patient_ipid][$stage_arr['stage']],$patient_master_obj->getAllDaysInBetween($stage_arr['fromdate'],$stage_arr['tilldate']));
			        }
			    }
			    
			    foreach($stage_dates as $sipid => $status2dates ){
			        foreach ($status2dates as $status => $sdate){
			            foreach($sdate as $sk =>$date){
			                $patient_stage_date2status[$sipid][$date] = $status;
			            }
			        }
			    }
			    $pflegeValue2code = array(
                    '1'=>'rubin_cot',
                    '2'=>'rubin_cou',
                    '3'=>'rubin_cov',
                    '4'=>'rubin_cow',
                    '5'=>'rubin_cox',
			    );
			    
			    //Form types 
			    $cf_types = new FormTypes();
			    $cf_types_arr = $cf_types->get_form_types($clientid);
			    
			    $actions2type_ids = array();
			    foreach($cf_types_arr as $k=>$type_data){
    		        $actions2type_ids[$type_data['action']][] = $type_data['id'];
    		        
    		        if($type_data['action'] == '4'){
    		            $phone_cf_types[] = $type_data['id'];
    		        } else {
    		            $NON_phone_cf_types[] = $type_data['id'];
    		        }
			    }

			    // deleted cfs 
			    
			    // Contact forms 
			    $patients_cf_arr = array();
			    if(!empty($ipids)){
    			    $cf_obj =  new ContactForms();
    			    $deleted_cf_ids_arr = $cf_obj->get_patients_deleted_contactforms($ipids);
//     			    dd($deleted_cf_ids_arr);
    			    foreach($deleted_cf_ids_arr as $kd_ipid=>$ids){
    			        foreach($ids as $pc){
    			            $deleted_cf_ids[] = $pc;
    			        }
    			    }

    			    $patients_cf_q = Doctrine_Query::create()
    			    ->select('c.*,ca.*,de.*')
    			    ->from('ContactForms c')
    			    ->where('c.isdelete="0"')
    			    ->andWhere('c.isdelete="0"')
    			    ->andWhereIn('c.ipid',$ipids)
    			    ->leftJoin("c.FormBlockCoordinatorActions ca")
    			    ->leftJoin("c.FormBlockDelegation de")
    			    ->andWhere('c.isdelete = 0');
    			    if(!empty($deleted_cf_ids)){
    			        $patients_cf_q->andWhereNotIn('c.id',$deleted_cf_ids);
    			    }
    			    $patients_cf_arr = $patients_cf_q->fetchArray();
			    }
			    
			    if(empty($patients_cf_arr)){
			        $error_msg_div ='<div class="err">No patients to be exported</div>';
			        echo $error_msg_div;
			        
			        return;
			    }
			    
			    $patients_contact_forms = array();
			    $relevant_patients = array();
			    $contact_forms_ids = array();
			    foreach($patients_cf_arr as $k=>$cfs){
			        if(!empty($cfs['FormBlockCoordinatorActions']) || !empty($cfs['FormBlockDelegation'])){
			            //use cf 
			            $patients_contact_forms[$cfs['ipid']][$cfs['id']] = $cfs;
			            $relevant_patients[] = $cfs['ipid'];
// 			            $patients_contact_forms[$cfs['ipid']][1] = $cfs;
			            $contact_forms_ids[] = $cfs['id'];
			            
			        }  else {
			            // do not use  cf
			        }
			    }
			    
			    $fbtd = new FormBlockDrivetimedoc();
			    $time_document = $fbtd->get_multiple_block_drivetimedoc($relevant_patients,$contact_forms_ids);

			    
			    

			    $groupped_cfs = array();
			    foreach($patients_contact_forms as $fipid=>$forms){
			        usort($forms, array(new Pms_Sorter('start_date'), "_date_compare"));
			        $groupped_cfs[$fipid]['first'][$forms[0]['id']] = $forms[0];
			        if(count($forms) > 1){
    			        foreach($forms as $fk =>$form_data){
    			            if($fk!=0){
    			                $groupped_cfs[$fipid]['CRFCOPIES'][$form_data['id']] = $form_data;
    			            }
    			        }
			        }
			    }
			    
			    
			    $export_no_health_info = array();
			    foreach($export_patients as $patient_ipid => $patient_data){
			        if(in_array($patient_ipid,$relevant_patients) && in_array($patient_ipid,$missing_healthinsurance) ){
			            $export_no_health_info[] = $patient_data['details']['epid'];
			        }
			        
			        if(!in_array($patient_ipid,$relevant_patients)){
			            unset($export_patients[$patient_ipid]);
			        }
			    }
			    if(!empty($export_no_health_info)){
			        
			        $error_msg_div ='<div class="err">Patients missing health insurance info: '.implode(', ',$export_no_health_info).'</div>';
			        echo $error_msg_div;
			        
			        return;
			        
			    }
			    if(empty($export_patients)){
			        $error_msg_div ='<div class="err">No patients to be exported</div>';
			        echo $error_msg_div;
			        
			        return;
			        
			    }
			    

			    // get options for Leistung / Koordination
			    $blocks_settings = new FormBlocksSettings();
		        $blocks_settings_array = $blocks_settings->get_blocks_settings($clientid);
			    
			    foreach($blocks_settings_array as $key => $value)
			    {
			        $settings_array[$value['block']][$value['id']] = $value;
			        $settings_array[$value['block']][$value['id']]['source'] = $value['block'];
			        
			        if($value['block'] == "coordinator_actions")
			        {
			            $action_name2action_id[trim($value['option_name'])] = $value['id'];
			        }
			        
			    }

			    $coordinator_actions = $this->ca_actions();
			    
			    $groupped_ca_actions = array();
			    foreach($coordinator_actions as $action_name => $act_opts){
			        foreach($act_opts as  $opt => $xls_values)
			        {
			            $groupped_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['MP_Code']= $xls_values['MP_Code'];
			            $groupped_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['LaborValueCode']= $xls_values['LaborValueCode'];
			            $groupped_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['comment']= $xls_values['comment'];
			            $groupped_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['CrfTemplateSectionName']= $xls_values['CrfTemplateSectionName'];
			            $groupped_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['CrfTemplateFieldLowerRow']= $xls_values['CrfTemplateFieldLowerRow'];
			            $groupped_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['CrfTemplateFieldLowerColumn']= $xls_values['CrfTemplateFieldLowerColumn'];
			            $groupped_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['coordinator_actions'][$action_name][$opt] = $xls_values['LaborValueCode'];;
			        }
			    }

			    
			    $coordinator_actions_bottom = $this->ca_bottom_actions();
			    $groupped_bottom_ca_actions = array();
			    foreach($coordinator_actions_bottom as $action_name => $act_opts){
			        foreach($act_opts as  $opt => $xls_values)
			        {
			            $groupped_bottom_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['MP_Code']= $xls_values['MP_Code'];
			            $groupped_bottom_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['LaborValueCode']= $xls_values['LaborValueCode'];
			            $groupped_bottom_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['comment']= $xls_values['comment'];
			            $groupped_bottom_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['CrfTemplateSectionName']= $xls_values['CrfTemplateSectionName'];
			            $groupped_bottom_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['CrfTemplateFieldLowerRow']= $xls_values['CrfTemplateFieldLowerRow'];
			            $groupped_bottom_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['CrfTemplateFieldLowerColumn']= $xls_values['CrfTemplateFieldLowerColumn'];
			            $groupped_bottom_ca_actions[$xls_values['MP_Code'].$xls_values['CrfTemplateFieldLowerRow'].$xls_values['CrfTemplateFieldLowerColumn']]['coordinator_actions'][$action_name][$opt] = $xls_values['LaborValueCode'];;
			        }
			    }
			    
			    
			    
			    $delegation_actions = $this->de_actions();
			    $allowed_delegations_Act = array_keys($delegation_actions);

// 			    dd($delegation_actions,$allowed_delegations_Act);
			    // split array in 10 patients groups - generate xml for each group - then add to zip
			    $limit_patients_nr = 10;
			    $export_patient_groups = array_chunk($export_patients,$limit_patients_nr,true);

                
			    $overall_patients_ex = 0 ;
			    foreach($export_patient_groups as $patient_sets => $export_patients_arr)
                {
                    // create  patient exort array
                    $export_array = array();
                    $export_array['Source'] = "XMLIMPORT";
                    $export_array['ExportDate'] =  date('c');
                    $export_array['EffectData'] = array();
                    $p = 0;
                    $patients_ex = 0 ;
			        foreach($export_patients_arr as $pipid=>$padata)
			        {
    			        // skip if no contact forms - with relevant blocks () 
    			        if( empty($patients_contact_forms[$pipid]) || !isset($patients_contact_forms[$pipid])){
    			            continue;
    			        }
    			     
    			        $patient_set = array();
    			        $patient_set['Source'] = "CENTRAXX"; 
    
    			        //patient data array
    			        // Patient health insurance 
                        $patient_set['IDContainer']['FlexibleID']['key'] = "versichertennummer"; 
                        $patient_set['IDContainer']['FlexibleID']['value'] = !empty($padata['health_insurance']['insurance_no']) ? $padata['health_insurance']['insurance_no'] : " "; 
    			        
    			        //Patient master data
    			        $gender = "";
    			        if($padata['details']['sex'] == "1") {
        			        $gender = "MALE";
    			        } elseif($padata['details']['sex'] == "2"){
        			        $gender = "FEMALE";
    			        } else{
        			        $gender = "OTHER";
    			        }
    			        $patient_set['Masterdata'] = array();
    			        $patient_set['Masterdata']['DateOfBirth']['Date'] = date('c',strtotime($padata['details']['birthd']));
    			        $patient_set['Masterdata']['DateOfBirth']['Precision'] = "DAY";
    			        $patient_set['Masterdata']['FirstName'] = $padata['details']['first_name'];
    			        $patient_set['Masterdata']['Gender'] = $gender;
    			        $patient_set['Masterdata']['LastName'] =$padata['details']['last_name'];
    
    			        
    			        // ???
    			        $patient_set['OrganisationUnitRefs'] = "Ärztenetz Lippe";
    
    			        // StudyMember ??? 
    			        $patient_set['StudyMember'] = array();
    			        $patient_set['StudyMember']['StudyRef'] = "RubiN";
    			        $patient_set['StudyMember']['StudyProfileRef'] = "CTG-Profile_v01.00";
    			        $patient_set['StudyMember']['CenterRef'] = "Liebe";
    			        
    			        
                        // StudyMember ???  Contact forms 
    			        $patient_contact_forms = array();
    			        
    			        $cf = 0;
//     			        foreach($patients_contact_forms[$pipid] as $contact_form_id => $cf_data ){
    			        foreach($groupped_cfs[$pipid]['first'] as $contact_form_id => $cf_data ){
    			            $cf_data['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($cf_data['start_date'], $cf_data['end_date']);
    			            
    			            
    			            $patient_contact_forms['StudyCrfTemplateRef']['Name'] = "Leistungsnachweis";
    			            $patient_contact_forms['StudyCrfTemplateRef']['Version'] = "3";
    			            
    			            //ITEMS
    			            $i = 0;
    			            $cfr_items[$contact_form_id] = array();
    			            $items = array();
    			            //rubin_coe + rubin_cof
    			            //Interim	Leistungsnachweis	Beratener (Angaben zum Beratenen/ Betroffenen)	rubin_cof	rubin_coe	Angehöriger		rubin_coc	3	rubin_coh	OPTIONGROUP	1	1
    			            // Interim	Leistungsnachweis	Beratener (Angaben zum Beratenen/ Betroffenen)	rubin_cof	rubin_cod	Betroffener selbst	from ISPC this must be preselected.	rubin_coc	3	rubin_coh	OPTIONGROUP	1	1
    			            // Same item - multiple CustomCatalogEntryRef
    			            $item = array(
    			                'CrfTemplateSectionName' => "rubin_coh",
    			                'CrfTemplateFieldLowerRow' =>  "1",
    			                'CrfTemplateFieldLowerColumn' => "1",
    			                'LaborValueCode' => "rubin_cof",
    			                'CustomCatalogEntryRef_0' => "rubin_coe",
    			                'CustomCatalogEntryRef_1' => "rubin_cod",
    			                'ValueIndex' => "0",
    			                'PatientStatus' => array(
    			                    'Active' =>"true"
    			                )
    			            );
    			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			            $i++;
    			            
    			            $cf_date = date('Y-m-d',strtotime($cf_data['start_date']));
    			            
    			            
    			            //##### Pflegegrad SECTION START ##### 
    			            //Pflege Item - MANDATORY  
    			            $pflege_item = array();
    			            $pflege_item['CrfTemplateSectionName'] = "rubin_coh";
    			            $pflege_item['CrfTemplateFieldLowerRow'] = "3";
    			            $pflege_item['CrfTemplateFieldLowerColumn'] = "1";
    			            $pflege_item['LaborValueCode'] = "rubin_coq";
    			            if(isset($patient_stage_date2status[$pipid][$cf_date]) && $patient_stage_date2status[$pipid][$cf_date] != "no_stage"){
        			            $pflege_item['CustomCatalogEntryRef'] = "rubin_coo";
    			            } else{
        			            $pflege_item['CustomCatalogEntryRef'] = "rubin_cop";
    			            }
    			            $pflege_item['ValueIndex'] = "0";
    			            $pflege_item['PatientStatus'] = array('Active' =>"true");
    			            $patient_contact_forms['CrfItems_'.$i]  =  $pflege_item ;
    			            $i++;
    			            
    			            if(isset($patient_stage_date2status[$pipid][$cf_date]) && $patient_stage_date2status[$pipid][$cf_date] != "no_stage")
    			            {
    			                //if patient data has set X as value for "Pflegegrade" at the date on which the form was created
    			                if(in_array($patient_stage_date2status[$pipid][$cf_date], array('1','2','3','4','5')))
    			                {
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_cor",
    			                        'CrfTemplateFieldLowerRow' =>  "0",
    			                        'CrfTemplateFieldLowerColumn' => "0",
    			                        'LaborValueCode' => "rubin_coy",
    			                        'CustomCatalogEntryRef' => $pflegeValue2code[$patient_stage_date2status[$pipid][$cf_date]],
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                }
    			                
    			                //if patient data has set "Höherstufung beantragt" for "Pflegegrade" at the date on which the form was created
    			                if(in_array($cf_date,$stage_dates_H[$pipid])){
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_coh",
    			                        'CrfTemplateFieldLowerRow' =>  "5",
    			                        'CrfTemplateFieldLowerColumn' => "1",
    			                        'LaborValueCode' => "rubin_coz",
    			                        'BooleanValue' => "false",
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                }
    			            }
    			            //##### Pflegegrad SECTION END #####
    			            
    			            
    			            if(in_array($cf_data['form_type'],$phone_cf_types) || in_array($cf_data['form_type'],$NON_phone_cf_types)){
        			            //if contactfom is type "Kontaktformular-Telefon"
        			            if(in_array($cf_data['form_type'],$phone_cf_types)){
        			                
            			            $item = array(
            			                //'MP_CODE' => "rubin_cph",
            			                'CrfTemplateSectionName' => "rubin_cpn",
            			                'CrfTemplateFieldLowerRow' =>  "10",
            			                'CrfTemplateFieldLowerColumn' => "2",
            			                'LaborValueCode' =>"rubin_cph",
            			                'CustomCatalogEntryRef' => "rubin_cpf",
            			                'ValueIndex' => "0",
            			                'PatientStatus' => array(
            			                    'Active' =>"true"
            			                )
            			            );
            			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
            			            $i++;
        			            }
        			            else if(in_array($cf_data['form_type'],$NON_phone_cf_types))
        			            {
        			            //each contactform whch is not "Kontaktformular-Telefon"
        			                
            			            $item = array(
            			                //'MP_CODE' => "rubin_cph",
            			                'CrfTemplateSectionName' => "rubin_cpn",
            			                'CrfTemplateFieldLowerRow' =>  "10",
            			                'CrfTemplateFieldLowerColumn' => "2",
            			                'LaborValueCode' => "rubin_cph",
            			                'CustomCatalogEntryRef' => "rubin_cpd",
            			                'ValueIndex' => "0",
            			                'PatientStatus' => array(
            			                    'Active' =>"true"
            			                )
            			            );
            			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
            			            $i++;
        			            }
    			            } else{
    			                $item = array(
    			                    'CrfTemplateSectionName' => "rubin_cpn",
    			                    'CrfTemplateFieldLowerRow' =>  "10",
    			                    'CrfTemplateFieldLowerColumn' => "2",
    			                    'LaborValueCode' => "rubin_cph",
    			                    'ValueIndex' => "0",
    			                    'PatientStatus' => array(
    			                        'Active' =>"true"
    			                    ),
    			                    'ControlledMissingRef' => "9012",
    			                );
    			                $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			                $i++;
    			            }
    			            
    			            if(in_array($cf_date,$padata['other_location_days']) || in_array($cf_date,$padata['home_location_days']) ){
    			                
        			            //set this if the contact form was filled in a location type NOT "zu Hause" (any!)
        			            if(in_array($cf_date,$padata['other_location_days'])){
            			            $item = array(
            			                //'MP_CODE' => "rubin_cpm",
            			                'CrfTemplateSectionName' => "rubin_cpn",
            			                'CrfTemplateFieldLowerRow' =>  "12",
            			                'CrfTemplateFieldLowerColumn' => "2",
            			                'LaborValueCode' =>"rubin_cpm",
            			                'CustomCatalogEntryRef' => "rubin_cpl",
            			                'ValueIndex' => "0",
            			                'PatientStatus' => array(
            			                    'Active' =>"true"
            			                )
            			            );
            			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
            			            $i++;
        			            }
        			            else if(in_array($cf_date,$padata['home_location_days']))
        			            {
                                    //set this if the contact form was filled in a location type "zu Hause" (any!)
            			            $item = array(
            			                //'MP_CODE' => "rubin_cpm",
            			                'CrfTemplateSectionName' => "rubin_cpn",
            			                'CrfTemplateFieldLowerRow' =>  "12",
            			                'CrfTemplateFieldLowerColumn' => "2",
            			                'LaborValueCode' => "rubin_cpm",
            			                'CustomCatalogEntryRef' => "rubin_cpj",
            			                'ValueIndex' => "0",
            			                'PatientStatus' => array(
            			                    'Active' =>"true"
            			                )
            			            );
            			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
            			            $i++;
        			            }
    			            }
    			            else
    			            {
                                //set this if the contact form was filled in a location type "zu Hause" (any!)
        			            $item = array(
        			                //'MP_CODE' => "rubin_cpm",
        			                'CrfTemplateSectionName' => "rubin_cpn",
        			                'CrfTemplateFieldLowerRow' =>  "12",
        			                'CrfTemplateFieldLowerColumn' => "2",
        			                'LaborValueCode' => "rubin_cpm",
        			                'ValueIndex' => "0",
        			                'PatientStatus' => array(
        			                    'Active' =>"true"
        			                ),
        			                'ControlledMissingRef' => "9012",
        			            );
        			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
        			            $i++;
    			                
    			            }
    			            
    			            
    			            
    			            //Interim	Leistungsnachweis	Anlass der Beratung	rubin_cpq	rubin_cps	Geplanter Termin	ignore	rubin_cpr	3	rubin_cpn	OPTIONGROUP	15	2
    			            $item = array(
    			                'CrfTemplateSectionName' => "rubin_cpn",
    			                'CrfTemplateFieldLowerRow' =>  "15",
    			                'CrfTemplateFieldLowerColumn' => "2",
    			                'LaborValueCode' => "rubin_cpq",
    			                'CustomCatalogEntryRef' => "rubin_cps",
    			                'ValueIndex' => "0",
    			                'PatientStatus' => array(
    			                    'Active' =>"true"
    			                )
    			            );
    			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			            $i++;
    			            
    			            
    			            
    			            $group_customRef = array();
			                foreach($groupped_ca_actions as $mp_code=>$grp_data){
			                    foreach($grp_data['coordinator_actions'] as $action_name =>$opt2values){
			                        foreach($cf_data['FormBlockCoordinatorActions'] as $fbck=>$fbca){
    			                        if($fbca['action_id'] == $action_name2action_id[$action_name] && ($fbca['receives_services'] == '1' || $fbca['is_requested'] == '1'  )){
        
    			                            if($fbca['receives_services']  && isset($opt2values['receives_services'])){
    			                                $group_customRef[$mp_code][] =$opt2values['receives_services'];
    			                            }
    			                            if($fbca['is_requested'] == '1'  && isset($opt2values['is_requested'])){
    			                                $group_customRef[$mp_code][] =$opt2values['is_requested'];
    			                            }
    			                        }
			                        }
			                    }
			                  
			                    $rf_ident = 0;
			                    $i++;
			                    if(!empty($group_customRef[$mp_code])){
			                        $item = array();
			                        $item['CrfTemplateSectionName'] = $grp_data['CrfTemplateSectionName'];
			                        $item['CrfTemplateFieldLowerRow'] = $grp_data['CrfTemplateFieldLowerRow'];
			                        $item['CrfTemplateFieldLowerColumn'] = $grp_data['CrfTemplateFieldLowerColumn'];
			                        $item['LaborValueCode'] = $grp_data['MP_Code'];
			                        foreach($group_customRef[$mp_code] as $k=>$refs){
			                            $item['CustomCatalogEntryRef_'.$rf_ident] =  $refs;
			                            $rf_ident++;
			                        }
			                        $item['ValueIndex'] =  "0";
			                        $item['PatientStatus'] = array('Active' =>"true");
			                        
			                        $patient_contact_forms['CrfItems_'.$i]  =  $item ;
			                        $i++;
			                    }
			                }
			                
    			            // delegation
			                if(!empty($cf_data['FormBlockDelegation']))
			                {
			                    $delegation_refs = array();
        			            foreach($cf_data['FormBlockDelegation'] as $fbdk=>$fbd)
        			            {
        			                foreach($allowed_delegations_Act as $de_Act)
        			                {
        			                    if($fbd[$de_Act] == '1'){
            			                    $ch_data = array();
            			                    $ch_data =  $delegation_actions[$de_Act];
            			                    
            			                    $delegation_refs[] = $ch_data['LaborValueCode'];
            			                }
        			                }
        			            }
        			            
        			            if(!empty($delegation_refs)){
        			                
            			            $delegation_item = array();
            			            $delegation_item['CrfTemplateSectionName'] = "rubin_cre";
            			            $delegation_item['CrfTemplateFieldLowerRow'] = "32";
            			            $delegation_item['CrfTemplateFieldLowerColumn'] = "2";
            			            $delegation_item['LaborValueCode'] = "rubin_cvp";
            			            $dlg_ident = 0 ;
            			            foreach($delegation_refs as $k=>$deleg){
            			                $delegation_item['CustomCatalogEntryRef_'.$dlg_ident] = $deleg;
            			                $dlg_ident++;
            			            }
            			            $delegation_item['ValueIndex'] = "0";
            			            $delegation_item['PatientStatus'] = array('Active' =>"true");
            			            $patient_contact_forms['CrfItems_'.$i]  =  $delegation_item ;
            			            $i++;
        			            }
			                }
    			           
    			            
    			            
    			            
    			            //ca again 
    			            $group_customRef_bottom = array();
    			            foreach($groupped_bottom_ca_actions as $mp_code=>$grp_data){
    			                foreach($grp_data['coordinator_actions'] as $action_name =>$opt2values){
    			                    foreach($cf_data['FormBlockCoordinatorActions'] as $fbck=>$fbca){
    			                        if($fbca['action_id'] == $action_name2action_id[$action_name] && ($fbca['receives_services'] == '1' || $fbca['is_requested'] == '1'  )){
    			                            
    			                            if($fbca['receives_services']  && isset($opt2values['receives_services'])){
    			                                $group_customRef_bottom[$mp_code][] =$opt2values['receives_services'];
    			                            }
    			                            if($fbca['is_requested'] == '1'  && isset($opt2values['is_requested'])){
    			                                $group_customRef_bottom[$mp_code][] =$opt2values['is_requested'];
    			                            }
    			                        }
    			                    }
    			                }
    			                
    			                $rf_ident = 0;
    			                $i++;
    			                if(!empty($group_customRef_bottom[$mp_code])){
    			                    $item = array();
    			                    $item['CrfTemplateSectionName'] = $grp_data['CrfTemplateSectionName'];
    			                    $item['CrfTemplateFieldLowerRow'] = $grp_data['CrfTemplateFieldLowerRow'];
    			                    $item['CrfTemplateFieldLowerColumn'] = $grp_data['CrfTemplateFieldLowerColumn'];
    			                    $item['LaborValueCode'] = $grp_data['MP_Code'];
    			                    foreach($group_customRef_bottom[$mp_code] as $k=>$refs){
    			                        $item['CustomCatalogEntryRef_'.$rf_ident] =  $refs;
    			                        $rf_ident++;
    			                    }
    			                    $item['ValueIndex'] =  "0";
    			                    $item['PatientStatus'] = array('Active' =>"true");
    			                    
    			                    $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                }
    			            }
    			            
    			            
    			            //date and time of contact form
    			            $item = array(
    			                'CrfTemplateSectionName' => "rubin_cpn",
    			                'CrfTemplateFieldLowerRow' =>  '1',
    			                'CrfTemplateFieldLowerColumn' => '2',
    			                'LaborValueCode' => 'rubin_cvr',
    			                //'CustomCatalogEntryRef' => $ch_data['LaborValueCode'],//$ch_data['CustomCatalogEntryRef']
    			                'DateValue' => array(
    			                    'Date'=>date('c',strtotime($cf_data['start_date'])),
    			                    'Precision'=>"DAY"
    			                ),
    			                'ValueIndex' => "0",
    			                'PatientStatus' => array(
    			                    'Active' =>"true"
    			                )
    			            );
    			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			            $i++;
    			            
    			            //length of contact form (in minutes)
    			            $item = array(
    			                'CrfTemplateSectionName' => "rubin_cpn",
    			                'CrfTemplateFieldLowerRow' =>  '3',
    			                'CrfTemplateFieldLowerColumn' => '2',
    			                'LaborValueCode' => 'rubin_cvs',
    			                'NumericValue' => $cf_data['visit_duration'],// DURATION
    			                'ValueIndex' => "0",
    			                'PatientStatus' => array(
    			                    'Active' =>"true"
    			                )
    			            );
    			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			            $i++;
    			            
    			            
    			            //driving distance in contact form
    			            $fahrtstreke_km = "0.00";
    			            if(!empty($time_document[$cf_data['id']]['fahrtstreke_km1']) && strlen($time_document[$cf_data['id']]['fahrtstreke_km1']) > 0 ){
    			                $fahrtstreke_km =  $time_document[$cf_data['id']]['fahrtstreke_km1'];
    			            } else if(!empty($cf_data['fahrtstreke_km'])){
    			                $fahrtstreke_km = $cf_data['fahrtstreke_km'];
    			            } else{
    			                $fahrtstreke_km = '0.00';
    			            }
    			            $fahrtstreke_km = number_format($fahrtstreke_km, '2', '.', '');
    			            
    			            
    			            
    			            $item = array(
    			                'CrfTemplateSectionName' => "rubin_cpn",
    			                'CrfTemplateFieldLowerRow' =>  '5',
    			                'CrfTemplateFieldLowerColumn' => '2',
    			                'LaborValueCode' => 'rubin_cvt',
    			                'NumericValue' => $fahrtstreke_km,// distance
    			                'ValueIndex' => "0",
    			                'PatientStatus' => array(
    			                    'Active' =>"true"
    			                )
    			            );
    			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			            $i++;
    			            
    			            
    			            //Driving time for this contact form
    			            $fahrtzeit = "0.00";
    			            if(!empty($time_document[$cf_data['id']])){
    			                $fahrtzeit = $time_document[$cf_data['id']]['fahrtzeit1'];
    			            } else{
    			                $fahrtzeit = $cf_data['fahrtzeit1'];
    			            }
    			            $fahrtzeit = number_format($fahrtzeit, '2', '.', '');
    			            
    			            $item = array(
    			                'CrfTemplateSectionName' => "rubin_cpn",
    			                'CrfTemplateFieldLowerRow' =>  '7',
    			                'CrfTemplateFieldLowerColumn' => '2',
    			                'LaborValueCode' => 'rubin_cvu',
    			                'NumericValue' => $fahrtzeit,// Driving time
    			                'ValueIndex' => "0",
    			                'PatientStatus' => array(
    			                    'Active' =>"true"
    			                )
    			            );
    			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			            $i++;
    			            
    			            //Sonstiges (Beratungsort)
    			            $item = array(
    			                'CrfTemplateSectionName' => "rubin_sgl",
    			                'CrfTemplateFieldLowerRow' =>  "0",
    			                'CrfTemplateFieldLowerColumn' => "0",
    			                'LaborValueCode' =>"rubin_cvv",
    			                'StringValue'=> $padata['day2location'][$cf_date] ,
    			                'ValueIndex' => "0",
    			                'PatientStatus' => array(
    			                    'Active' =>"true"
    			                )
    			            );
    			            $patient_contact_forms['CrfItems_'.$i]  =  $item ;
    			            $i++;
    			            
    			            
    			            //cf data 
    			            // --- 
                
    			            
    			            
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf] = $patient_contact_forms;
    			            
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['CrfStatus'] = "FINAL";;
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['Empty'] = "false";;
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['obsolete'] = "false";;
    			            
    			            if(!empty($groupped_cfs[$pipid]['CRFCOPIES'])){
    			                
    			                $patient_contact_forms_cp = array();
    			                
    			                $cf_cp = 0;
    			                foreach($groupped_cfs[$pipid]['CRFCOPIES'] as $contact_form_id => $cf_data_cp ){
    			                    $cf_data_cp['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($cf_data_cp['start_date'], $cf_data_cp['end_date']);
    			                    
    			                    
    			                    $patient_contact_forms_cp['StudyCrfTemplateRef']['Name'] = "Leistungsnachweis";
    			                    $patient_contact_forms_cp['StudyCrfTemplateRef']['Version'] = "3";
    			                    
    			                    //ITEMS
    			                    $i = 0;
    			                    $cfr_items[$contact_form_id] = array();
    			                    $items = array();
    			                    //rubin_coe
    			                    //Interim	Leistungsnachweis	Beratener (Angaben zum Beratenen/ Betroffenen)	rubin_cof	rubin_coe	Angehöriger		rubin_coc	3	rubin_coh	OPTIONGROUP	1	1
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_coh",
    			                        'CrfTemplateFieldLowerRow' =>  "1",
    			                        'CrfTemplateFieldLowerColumn' => "1",
    			                        'LaborValueCode' => "rubin_cof",
    			                        'CustomCatalogEntryRef' => "rubin_coe",
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                    
    			                    
    			                    //rubin_cof
    			                    //Interim	Leistungsnachweis	Beratener (Angaben zum Beratenen/ Betroffenen)	rubin_cof	rubin_cod	Betroffener selbst	from ISPC this must be preselected.	rubin_coc	3	rubin_coh	OPTIONGROUP	1	1
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_coh",
    			                        'CrfTemplateFieldLowerRow' =>  "1",
    			                        'CrfTemplateFieldLowerColumn' => "1",
    			                        'LaborValueCode' => "rubin_cof",
    			                        'CustomCatalogEntryRef' => "rubin_coe",
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                    
    			                    
    			                    $cf_date = date('Y-m-d',strtotime($cf_data_cp['start_date']));
    			                    
    			                    
    			                    
    			                    //##### Pflegegrad SECTION START #####
    			                    //Pflege Item - MANDATORY
    			                    $pflege_item = array();
    			                    $pflege_item['CrfTemplateSectionName'] = "rubin_coh";
    			                    $pflege_item['CrfTemplateFieldLowerRow'] = "3";
    			                    $pflege_item['CrfTemplateFieldLowerColumn'] = "1";
    			                    $pflege_item['LaborValueCode'] = "rubin_coq";
    			                    if(isset($patient_stage_date2status[$pipid][$cf_date]) && $patient_stage_date2status[$pipid][$cf_date] != "no_stage"){
    			                        $pflege_item['CustomCatalogEntryRef'] = "rubin_coo";
    			                    } else{
    			                        $pflege_item['CustomCatalogEntryRef'] = "rubin_cop";
    			                    }
    			                    $pflege_item['ValueIndex'] = "0";
    			                    $pflege_item['PatientStatus'] = array('Active' =>"true");
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $pflege_item ;
    			                    $i++;
    			                    
    			                    if(isset($patient_stage_date2status[$pipid][$cf_date]) && $patient_stage_date2status[$pipid][$cf_date] != "no_stage")
    			                    {
    			                        //if patient data has set X as value for "Pflegegrade" at the date on which the form was created
    			                        if(in_array($patient_stage_date2status[$pipid][$cf_date], array('1','2','3','4','5')))
    			                        {
    			                            $item = array(
    			                                'CrfTemplateSectionName' => "rubin_cor",
    			                                'CrfTemplateFieldLowerRow' =>  "0",
    			                                'CrfTemplateFieldLowerColumn' => "0",
    			                                'LaborValueCode' => "rubin_coy",
    			                                'CustomCatalogEntryRef' => $pflegeValue2code[$patient_stage_date2status[$pipid][$cf_date]],
    			                                'ValueIndex' => "0",
    			                                'PatientStatus' => array(
    			                                    'Active' =>"true"
    			                                )
    			                            );
    			                            $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                            $i++;
    			                        }
    			                        
    			                        //if patient data has set "Höherstufung beantragt" for "Pflegegrade" at the date on which the form was created
    			                        if(in_array($cf_date,$stage_dates_H[$pipid])){
    			                            $item = array(
    			                                'CrfTemplateSectionName' => "rubin_coh",
    			                                'CrfTemplateFieldLowerRow' =>  "5",
    			                                'CrfTemplateFieldLowerColumn' => "1",
    			                                'LaborValueCode' => "rubin_coz",
    			                                'BooleanValue' => "false",
    			                                'ValueIndex' => "0",
    			                                'PatientStatus' => array(
    			                                    'Active' =>"true"
    			                                )
    			                            );
    			                            $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                            $i++;
    			                        }
    			                    }
    			                    //##### Pflegegrad SECTION END #####
    			                    
    			                    
    			                    if(in_array($cf_data_cp['form_type'],$phone_cf_types) || in_array($cf_data_cp['form_type'],$NON_phone_cf_types)){
        			                    //if contactfom is type "Kontaktformular-Telefon"
        			                    if(in_array($cf_data_cp['form_type'],$phone_cf_types)){
        			                        
        			                        $item = array(
        			                            //'MP_CODE' => "rubin_cph",
        			                            'CrfTemplateSectionName' => "rubin_cpn",
        			                            'CrfTemplateFieldLowerRow' =>  "10",
        			                            'CrfTemplateFieldLowerColumn' => "2",
        			                            'LaborValueCode' =>"rubin_cph",
        			                            'CustomCatalogEntryRef' => "rubin_cpf",
        			                            'ValueIndex' => "0",
        			                            'PatientStatus' => array(
        			                                'Active' =>"true"
        			                            )
        			                        );
        			                        $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
        			                        $i++;
        			                    }
        			                    else if(in_array($cf_data_cp['form_type'],$NON_phone_cf_types))
        			                    {
        			                    //each contactform whch is not "Kontaktformular-Telefon"
        			                        
        			                        $item = array(
        			                            //'MP_CODE' => "rubin_cph",
        			                            'CrfTemplateSectionName' => "rubin_cpn",
        			                            'CrfTemplateFieldLowerRow' =>  "10",
        			                            'CrfTemplateFieldLowerColumn' => "2",
        			                            'LaborValueCode' => "rubin_cph",
        			                            'CustomCatalogEntryRef' => "rubin_cpd",
        			                            'ValueIndex' => "0",
        			                            'PatientStatus' => array(
        			                                'Active' =>"true"
        			                            )
        			                        );
        			                        $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
        			                        $i++;
        			                    } 
    			                    } 
    			                    else
    			                    {
    			                        $item = array(
    			                            'CrfTemplateSectionName' => "rubin_cpn",
    			                            'CrfTemplateFieldLowerRow' =>  "10",
    			                            'CrfTemplateFieldLowerColumn' => "2",
    			                            'LaborValueCode' => "rubin_cph",
    			                            'ValueIndex' => "0",
    			                            'PatientStatus' => array(
    			                                'Active' =>"true"
    			                            ),
    			                            'ControlledMissingRef' => "9012",
    			                        );
    			                        $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                        $i++;
    			                        
    			                    }
    			                    
    			                    if(in_array($cf_date,$padata['other_location_days']) || in_array($cf_date,$padata['home_location_days'])){
        			                    //set this if the contact form was filled in a location type NOT "zu Hause" (any!)
        			                    if(in_array($cf_date,$padata['other_location_days'])){
        			                        $item = array(
        			                            //'MP_CODE' => "rubin_cpm",
        			                            'CrfTemplateSectionName' => "rubin_cpn",
        			                            'CrfTemplateFieldLowerRow' =>  "12",
        			                            'CrfTemplateFieldLowerColumn' => "2",
        			                            'LaborValueCode' =>"rubin_cpm",
        			                            'CustomCatalogEntryRef' => "rubin_cpl",
        			                            'ValueIndex' => "0",
        			                            'PatientStatus' => array(
        			                                'Active' =>"true"
        			                            )
        			                        );
        			                        $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
        			                        $i++;
        			                    } 
        			                    else if(in_array($cf_date,$padata['home_location_days']))
        			                    {
        			                         //set this if the contact form was filled in a location type "zu Hause" (any!)
        			                        $item = array(
        			                            //'MP_CODE' => "rubin_cpm",
        			                            'CrfTemplateSectionName' => "rubin_cpn",
        			                            'CrfTemplateFieldLowerRow' =>  "12",
        			                            'CrfTemplateFieldLowerColumn' => "2",
        			                            'LaborValueCode' => "rubin_cpm",
        			                            'CustomCatalogEntryRef' => "rubin_cpj",
        			                            'ValueIndex' => "0",
        			                            'PatientStatus' => array(
        			                                'Active' =>"true"
        			                            )
        			                        );
        			                        $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
        			                        $i++;
        			                    }
    			                    }
    			                    else
    			                    {
    			                        $item = array(
    			                            'CrfTemplateSectionName' => "rubin_cpn",
    			                            'CrfTemplateFieldLowerRow' =>  "12",
    			                            'CrfTemplateFieldLowerColumn' => "2",
    			                            'LaborValueCode' => "rubin_cpm",
    			                            'ValueIndex' => "0",
    			                            'PatientStatus' => array(
    			                                'Active' =>"true"
    			                            ),
    			                            'ControlledMissingRef' => "9012",
    			                        );
    			                        $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                        $i++;
    			                        
    			                    }
    			                   
    			                    
    			                    //Interim	Leistungsnachweis	Anlass der Beratung	rubin_cpq	rubin_cps	Geplanter Termin	ignore	rubin_cpr	3	rubin_cpn	OPTIONGROUP	15	2
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_cpn",
    			                        'CrfTemplateFieldLowerRow' =>  "15",
    			                        'CrfTemplateFieldLowerColumn' => "2",
    			                        'LaborValueCode' => "rubin_cpq",
    			                        'CustomCatalogEntryRef' => "rubin_cps",
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                    $i++;
 

    			                    $group_customRef = array();
    			                    foreach($groupped_ca_actions as $mp_code=>$grp_data){
    			                        foreach($grp_data['coordinator_actions'] as $action_name =>$opt2values){
    			                            foreach($cf_data_cp['FormBlockCoordinatorActions'] as $fbck=>$fbca){
    			                                if($fbca['action_id'] == $action_name2action_id[$action_name] && ($fbca['receives_services'] == '1' || $fbca['is_requested'] == '1'  )){
    			                                    
    			                                    if($fbca['receives_services']  && isset($opt2values['receives_services'])){
    			                                        $group_customRef[$mp_code][] =$opt2values['receives_services'];
    			                                    }
    			                                    if($fbca['is_requested'] == '1'  && isset($opt2values['is_requested'])){
    			                                        $group_customRef[$mp_code][] =$opt2values['is_requested'];
    			                                    }
    			                                }
    			                            }
    			                        }
    			                        
    			                        $rf_ident = 0;
    			                        $i++;
    			                        if(!empty($group_customRef[$mp_code])){
    			                            $item = array();
    			                            $item['CrfTemplateSectionName'] = $grp_data['CrfTemplateSectionName'];
    			                            $item['CrfTemplateFieldLowerRow'] = $grp_data['CrfTemplateFieldLowerRow'];
    			                            $item['CrfTemplateFieldLowerColumn'] = $grp_data['CrfTemplateFieldLowerColumn'];
    			                            $item['LaborValueCode'] = $grp_data['MP_Code'];
    			                            foreach($group_customRef[$mp_code] as $k=>$refs){
    			                                $item['CustomCatalogEntryRef_'.$rf_ident] =  $refs;
    			                                $rf_ident++;
    			                            }
    			                            $item['ValueIndex'] =  "0";
    			                            $item['PatientStatus'] = array('Active' =>"true");
    			                            
    			                            $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                            $i++;
    			                            
    			                        }
    			                        
    			                    }
    			                    
    			                    // delegation
    			                    if(!empty($cf_data_cp['FormBlockDelegation']))
    			                    {
    			                        $delegation_refs = array();
    			                        foreach($cf_data['FormBlockDelegation'] as $fbdk=>$fbd)
    			                        {
    			                            foreach($allowed_delegations_Act as $de_Act)
    			                            {
    			                                if($fbd[$de_Act] == '1'){
    			                                    $ch_data = array();
    			                                    $ch_data =  $delegation_actions[$de_Act];
    			                                    
    			                                    $delegation_refs[] = $ch_data['LaborValueCode'];
    			                                }
    			                            }
    			                        }
    			                        if(!empty($delegation_refs)){
    			                            
    			                            $delegation_item = array();
    			                            $delegation_item['CrfTemplateSectionName'] = "rubin_cre";
    			                            $delegation_item['CrfTemplateFieldLowerRow'] = "32";
    			                            $delegation_item['CrfTemplateFieldLowerColumn'] = "2";
    			                            $delegation_item['LaborValueCode'] = "rubin_cvp";
    			                            $dlg_ident = 0 ;
    			                            foreach($delegation_refs as $k=>$deleg){
    			                                $delegation_item['CustomCatalogEntryRef_'.$dlg_ident] = $deleg;
    			                                $dlg_ident++;
    			                            }
    			                            $delegation_item['ValueIndex'] = "0";
    			                            $delegation_item['PatientStatus'] = array('Active' =>"true");
    			                            $patient_contact_forms_cp['CrfItems_'.$i]  =  $delegation_item ;
    			                            $i++;
    			                        }
    			                    }
    			                    
    			                    //ca again
    			                    $group_customRef_bottom = array();
    			                    foreach($groupped_bottom_ca_actions as $mp_code=>$grp_data){
    			                        foreach($grp_data['coordinator_actions'] as $action_name =>$opt2values){
    			                            foreach($cf_data_cp['FormBlockCoordinatorActions'] as $fbck=>$fbca){
    			                                if($fbca['action_id'] == $action_name2action_id[$action_name] && ($fbca['receives_services'] == '1' || $fbca['is_requested'] == '1'  )){
    			                                    
    			                                    if($fbca['receives_services']  && isset($opt2values['receives_services'])){
    			                                        $group_customRef_bottom[$mp_code][] =$opt2values['receives_services'];
    			                                    }
    			                                    if($fbca['is_requested'] == '1'  && isset($opt2values['is_requested'])){
    			                                        $group_customRef_bottom[$mp_code][] =$opt2values['is_requested'];
    			                                    }
    			                                }
    			                            }
    			                        }
    			                        
    			                        $rf_ident = 0;
    			                        $i++;
    			                        if(!empty($group_customRef_bottom[$mp_code])){
    			                            $item = array();
    			                            $item['CrfTemplateSectionName'] = $grp_data['CrfTemplateSectionName'];
    			                            $item['CrfTemplateFieldLowerRow'] = $grp_data['CrfTemplateFieldLowerRow'];
    			                            $item['CrfTemplateFieldLowerColumn'] = $grp_data['CrfTemplateFieldLowerColumn'];
    			                            $item['LaborValueCode'] = $grp_data['MP_Code'];
    			                            foreach($group_customRef_bottom[$mp_code] as $k=>$refs){
    			                                $item['CustomCatalogEntryRef_'.$rf_ident] =  $refs;
    			                                $rf_ident++;
    			                            }
    			                            $item['ValueIndex'] =  "0";
    			                            $item['PatientStatus'] = array('Active' =>"true");
    			                            
    			                            $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                            $i++;
    			                        }
    			                    }
    			                    
    			                    
    			                    
    			                    //date and time of contact form
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_cpn",
    			                        'CrfTemplateFieldLowerRow' =>  '1',
    			                        'CrfTemplateFieldLowerColumn' => '2',
    			                        'LaborValueCode' => 'rubin_cvr',
    			                        //'CustomCatalogEntryRef' => $ch_data['LaborValueCode'],//$ch_data['CustomCatalogEntryRef']
    			                        'DateValue' => array(
    			                            'Date'=>date('c',strtotime($cf_data_cp['start_date'])),
    			                            'Precision'=>"DAY"
    			                        ),
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                    
    			                    //length of contact form (in minutes)
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_cpn",
    			                        'CrfTemplateFieldLowerRow' =>  '3',
    			                        'CrfTemplateFieldLowerColumn' => '2',
    			                        'LaborValueCode' => 'rubin_cvs',
    			                        'NumericValue' => $cf_data_cp['visit_duration'],// DURATION
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                    
    			                    
    			                    //driving distance in contact form
    			                    $fahrtstreke_km = "0.00";
    			                    if(!empty($time_document[$cf_data_cp['id']]['fahrtstreke_km1'])){
    			                        $fahrtstreke_km = $time_document[$cf_data_cp['id']]['fahrtstreke_km1'];
    			                    } else if(!empty($cf_data_cp['fahrtstreke_km'])){
    			                        $fahrtstreke_km = $cf_data_cp['fahrtstreke_km'];
    			                    } else{
    			                        $fahrtstreke_km = "0.00";
    			                    }
    			                    $fahrtstreke_km = number_format($fahrtstreke_km, '2', '.', '');
    			                    
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_cpn",
    			                        'CrfTemplateFieldLowerRow' =>  '5',
    			                        'CrfTemplateFieldLowerColumn' => '2',
    			                        'LaborValueCode' => 'rubin_cvt',
    			                        'NumericValue' => $fahrtstreke_km,// driving distance
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                    
    			                    
    			                    //Driving time for this contact form
    			                    $fahrtzeit = "0.00";
    			                    if(!empty($time_document[$cf_data_cp['id']])){
    			                        $fahrtzeit = $time_document[$cf_data_cp['id']]['fahrtzeit1'];
    			                    } else{
    			                        $fahrtzeit = $cf_data_cp['fahrtzeit1'];
    			                    }
    			                    $fahrtzeit = number_format($fahrtzeit, '2', '.', '');
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_cpn",
    			                        'CrfTemplateFieldLowerRow' =>  '7',
    			                        'CrfTemplateFieldLowerColumn' => '2',
    			                        'LaborValueCode' => 'rubin_cvu',
    			                        'NumericValue' => $fahrtzeit,// Driving time
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                    
    			                    //Sonstiges (Beratungsort)
    			                    $item = array(
    			                        'CrfTemplateSectionName' => "rubin_sgl",
    			                        'CrfTemplateFieldLowerRow' =>  "0",
    			                        'CrfTemplateFieldLowerColumn' => "0",
    			                        'LaborValueCode' =>"rubin_cvv",
    			                        'StringValue'=> $padata['day2location'][$cf_date] ,
    			                        'ValueIndex' => "0",
    			                        'PatientStatus' => array(
    			                            'Active' =>"true"
    			                        )
    			                    );
    			                    $patient_contact_forms_cp['CrfItems_'.$i]  =  $item ;
    			                    $i++;
    			                    
    			                    
    			                    //cf data
    			                    // ---
    			                    
    			                    $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['CrfCopies_'.$cf_cp] = $patient_contact_forms_cp;
    			                    
    			                    $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['CrfCopies_'.$cf_cp]['CrfStatus'] = "FINAL";;
    			                    $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['CrfCopies_'.$cf_cp]['Empty'] = "false";;
    			                    $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['CrfCopies_'.$cf_cp]['obsolete'] = "false";;
    			                    $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['CrfCopies_'.$cf_cp]['CxxId'] = "0";;
    			                    $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['CrfCopies_'.$cf_cp]['CrfId'] = "";;
    			                 
    			                    $cf_cp++;
    			                }
    			            }
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Crf_'.$cf]['CxxId'] = "0";;
    			            
    			            
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['StudyVisitItemStatus_'.$cf] = "INPROCESS"; 
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Locked_'.$cf] = "false"; 
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['StudyStatusRef_'.$cf] = "rubin_afh"; 
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['StudyPhaseName_'.$cf] = "Patientenakte"; 
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['StudyVisitTempName_'.$cf] = "Interim"; 
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['StudyVisitItemTempPosition_'.$cf] = "0"; 
    			            $patient_set['StudyMember']['StudyVisitItems_'.$cf]['Enabled_'.$cf] = "true";
    			             
    			            $cf++;
    			        }
    			        
    			      
    			        
    			        
    			        
    			        
    			        
    			        $patient_set['StudyMember']['Visible'] = "false";
    			        $patient_set['StudyMember']['Locked'] = "false";
    			        $patient_set['StudyMember']['StudyEnrollmentRef'] = "Allgemein";
    			        
    			        $patient_set['PatientStatus'] = array(
    			            'Active' =>"true"
    			        );
    			        $patients_ex++;
    			        $overall_patients_ex++;
    			        $export_array['EffectData']['PatientDataSet_'.$p] = $patient_set;
    			        $p++;
    			    }
			    
    			    if($patients_ex > 0 ){
    			        $xml_arrays[] = $this->generate_xml_string($export_array);
    			    }
		     }
		     
 
		     if($overall_patients_ex > 0 ){
    		     
    		     $errors = array();
    		     foreach($xml_arrays as $ks => $gen_info ){
    		         if(!empty($gen_info['errors']) && $gen_info['errors'] != 0 ){
    		             
    		             $errors[] = '#'.$ks.' '.implode("<br />",$gen_info['errors']);
    		         }
    		     }
    		     
    		     if(!empty($errors)){
    		         
    		        $error_msg = implode("<br/>",$errors);
                    $error_msg_div ='<div class="err">'.$error_msg.'</div>';
                    echo $error_msg_div;
    		         
    		     } else{
    		         
        		     $export_date__dmyHi = date('dmYHi');
        		      // create your zip file
        		      $zipname = 'RUBIN_'.$export_date__dmyHi.'.zip';
        		      $zip = new ZipArchive;
        		      $zip->open($zipname, ZipArchive::CREATE);
        		      
        		      foreach($xml_arrays as $kx => $xml_details){
        		          $xml_ident = $kx+1;
        		          $zip->addFromString('XML'.$xml_ident.'.xml', $xml_details['string'] );
        		      }
        		      //close the file
        		      
        		      // close the archive
        		      $zip->close();
        		      
        		      header('Content-Type: application/zip');
        		      header('Content-disposition: attachment; filename='.$zipname);
        		      header('Content-Length: ' . filesize($zipname));
        		      readfile($zipname);
        		      // remove the zip archive
        		      
        		      // you could also use the temp file method above for this.
        		      unlink($zipname);
        		      exit();
                    }
                    
    		     } else{
		         $error_msg_div ='<div class="err">No patients to be exported</div>';
                 echo $error_msg_div;
		     }
		     
		     
		     
		     
// 			    if($patients_ex > 0 ){

//     			    $gen = $this->generate_xml($export_array);
//         			    if($gen === true){
//         			        exit;
//         			    } else{
        			        
//               			    $error_msg = implode("<br/>",$gen);
//                             $error_msg_div ='<div class="err">'.$error_msg.'</div>';
//                             echo $error_msg_div;
//         			    }
// 			    } else{
//                         $error_msg_div ='<div class="err">No patients to be exported</div>';
//                         echo $error_msg_div;
// 			    }
		        
			}// end post
		}
	}
	
	
	
	private function generate_xml_string($data_array)
	{
	    $xml_sapv = $this->toXml($data_array, null, null, 'data');
	    $xml_string = $this->xmlpp($xml_sapv, false);
// 	    echo ($xml_string);
// 	    exit;
	    $validate = $this->isValid_RubinXML($xml_string);
	    $xml_data = array();
	    if (  $validate['success'] === true) {
 
            $xml_data['errors'] = 0 ;	        
            $xml_data['string'] = $xml_string;	 
            
            return $xml_data;
    	    
	    } elseif ($validate['success'] === false) {
	        
	        $xml_data['errors'] = $validate['errors'] ;
	        $xml_data['string'] = "";
	        
	        return $xml_data;
	    }
	    
	    
	}
	
	private function generate_xml($data_array)
	{
	    $xml_sapv = $this->toXml($data_array, null, null, 'data');
	    $xml_string = $this->xmlpp($xml_sapv, false);
 
	    
	    $validate = $this->isValid_RubinXML($xml_string);
	    
	    if (  $validate['success'] === true) {
    	    //download xml
    	    header("Pragma: public");
    	    header("Expires: 0");
    	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    	    header("Content-Type: application/force-download");
    	    header("Content-Type: application/octet-stream");
    	    header("Content-type: text/xml; charset=utf-8");
    	    header("Content-Disposition: attachment; filename=rubin.xml");
    	    ob_clean();
    	    echo $xml_string;
    	    return true;
    	    
	    } elseif ($validate['success'] === false) {
	        
	        return $validate['errors'];
	    }
	}
	
	private function toXml($data, $rootNodeName = 'data', $xml = null, $elem_root = 'element', $xsd_file = false)
	{
	    // turn off compatibility mode as simple xml throws a wobbly if you don't.
	    if(ini_get('zend.ze1_compatibility_mode') == 1)
	    {
	        ini_set('zend.ze1_compatibility_mode', 0);
	    }
	    
	    if($xml == null)
	    {
	        
	        $xml = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><CentraXXDataExchange xmlns="http://www.kairos-med.de" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.kairos-med.de ../CentraXXExchange.xsd"/>');
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
	        
	        // replace anything not alpha numeric
	        //find out if key is special
	        $special_key = explode('_', $key);
	        if(count($special_key) == '2' && is_numeric($special_key[1]))
	        {
	            $key = $special_key[0];
	        }
	        
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
	            $value = htmlspecialchars($value);
	            $xml->addChild($key, $value);
	        }
	    }
	    // pass back as string. or simple xml object if you want!
	    return $xml->asXML();
	}
	
	/** Prettifies an XML string into a human-readable string
	 *  @param string $xml The XML as a string
	 *  @param boolean $html_output True if the output should be escaped (for use in HTML)
	 */
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
	    return ($html_output) ? htmlspecialchars($xml) : $xml;
	}
	
 
	
	
	/**
	 * ISPC-2895 Ancuta 25.05.2021
	 * @return string[][][]
	 * used just to populate data
	 */
	public function getcolumnsAction(){
	    $filename_falls = PUBLIC_PATH ."/import/rubin_export/rubin_columns_csv.csv";
	    $handle_fall = fopen($filename_falls, "r");
	    $delimiter_falls = ";";
	    //parse csv into an array
	    while(($data_falls = fgetcsv($handle_fall, NULL, $delimiter_falls)) !== FALSE)
	    {
	        foreach($data_falls as $k_data => $v_data)
	        {
	            $data_falls[$k_data] = htmlspecialchars($v_data);
	        }
	        
	        
	        $csv_data_falls[] = $data_falls;
	    }
	    fclose($handle_fall);

// 	    dd($csv_data_falls);
	    foreach($csv_data_falls as $row => $data){
	        if($row != 0){
    	        $action_name = trim($data['4']);
    	        if(trim($data['4']) == $action_name){
        	        $arr[$data['3']][trim($data['4'])][] = $data;
    	        }
	        }
	    }
	    
	    foreach($arr as $block => $action_info){
	        foreach($action_info as $action_name => $infos){
	            foreach($infos as $k=> $info){
	                $new_arr[$block]['"'.$action_name.'"']['"'.$info['5'].'"']['"MP_Code"'] = '"'.$info[0].'",';
	                $new_arr[$block]['"'.$action_name.'"']['"'.$info['5'].'"']['"LaborValueCode"'] = '"'.$info[1].'",';
	                $new_arr[$block]['"'.$action_name.'"']['"'.$info['5'].'"']['"comment"'] = '"'.$info[6].'",';
	                $new_arr[$block]['"'.$action_name.'"']['"'.$info['5'].'"']['"CustomCatalogEntryRef"'] = '"'.$info[7].'",';
	                $new_arr[$block]['"'.$action_name.'"']['"'.$info['5'].'"']['"CrfTemplateSectionName"'] = '"'.$info[8].'",';
	                $new_arr[$block]['"'.$action_name.'"']['"'.$info['5'].'"']['"CrfTemplateFieldLowerRow"'] = '"'.$info[9].'",';
	                $new_arr[$block]['"'.$action_name.'"']['"'.$info['5'].'"']['"CrfTemplateFieldLowerColumn"'] = '"'.$info[10].'",';
// 	                $new_arr[$block][$action_name][$info['5']]['MP_Code'] = $info[0];
// 	                $new_arr[$block][$action_name][$info['5']]['LaborValueCode'] = $info[1];
// 	                $new_arr[$block][$action_name][$info['5']]['comment'] = $info[6];
// 	                $new_arr[$block][$action_name][$info['5']]['CustomCatalogEntryRef'] = $info[7];
// 	                $new_arr[$block][$action_name][$info['5']]['CrfTemplateSectionName'] = $info[8];
// 	                $new_arr[$block][$action_name][$info['5']]['CrfTemplateFieldLowerRow'] = $info[9];
// 	                $new_arr[$block][$action_name][$info['5']]['CrfTemplateFieldLowerColumn'] = $info[10];
	            }
	        }
	    }
	    dd($new_arr);
	    return $new_arr;
	}
	
	/**
	 * ISPC-2895 Ancuta 25.05.2021
	 * @return string[][][]
	 */
	private function ca_actions(){
	    
	    $actions = array(
	        "Grundsicherung (SGB II)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvi",
	                "LaborValueCode" => "rubin_cttb",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Grundsicherung (SGB II), ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctt",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "18",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvi",
	                "LaborValueCode" => "rubin_ctta",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Grundsicherung (SGB II), ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctt",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "18",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Zuzahlungsbefreiung (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnao",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Zuzahlungsbefreiung (SGB V), ' checkbox 1 is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnan",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Zuzahlungsbefreiung (SGB V), ' checkbox 2 is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Fahrkostenübernahme (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnam",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Fahrkostenübernahme (SGB V), ' checkbox 1 is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Haushalthilfe (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnal",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Haushalthilfe (SGB V),  ' checkbox 1 is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnak",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Haushalthilfe (SGB V),   ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Ambulanter Pflegedienst (SGB V) - Hauswirtschaftliche Tätigkeiten" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnaj",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Ambulanter Pflegedienst (SGB V), - Hauswirtschaftliche Tätigkeiten  ' checkbox 1 is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnai",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Ambulanter Pflegedienst (SGB V), - Hauswirtschaftliche Tätigkeiten  ' checkbox2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Ambulanter Pflegedienst (SGB V) - Medizinische Behandlungspflege" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnah",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Ambulanter Pflegedienst (SGB V), - Medizinische Behandlungspflege ' checkbox 1 is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnag",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Ambulanter Pflegedienst (SGB V), - Medizinische Behandlungspflege ), ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Geriatrische Tagesklinik (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnt",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Geriatrische Tagesklinik (SGB V),  ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctns",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Geriatrische Tagesklinik (SGB V),  ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Spezialisierte Geriatrische Rehabilitation (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnad",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Spezialisierte Geriatrische Rehabilitation (SGB V), ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnac",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Spezialisierte Geriatrische Rehabilitation (SGB V),  ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Akutgeriatrie (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnab",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Akutgeriatrie (SGB V), ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnaa",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Akutgeriatrie (SGB V), ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Psychiatrische Institutambulanz (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnz",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Psychiatrische Institutambulanz (SGB V), ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctny",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Psychiatrische Institutambulanz (SGB V), ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Psychiatrische Tagesklinik (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnx",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Psychiatrische Tagesklinik (SGB V),  ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnw",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Psychiatrische Tagesklinik (SGB V),  ' checkbox 2 is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Geriatrische Institutambulanz (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnv",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Geriatrische Institutambulanz (SGB V), ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnu",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Geriatrische Institutambulanz (SGB V), ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Krankenhaus (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnr",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Krankenhaus (SGB V), ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
 
	    
	    "Krankenhauseinweisung (SGB V)" => Array
	    (
	        "is_requested" => Array
	        (
	            "MP_Code" => "rubin_cvc",
	            "LaborValueCode" => "rubin_ctnq",
	            "comment" => "if in contactformblock Leistung/Koordination for 'Krankenhauseinweisung (SGB V), ' checkbox 2  is selected",
	            "CustomCatalogEntryRef" => "rubin_ctn",
	            "CrfTemplateSectionName" => "rubin_cre",
	            "CrfTemplateFieldLowerRow" => "6",
	            "CrfTemplateFieldLowerColumn" => "2",
	        ),
	        
	    ),
	    
	    "Palliativversorgung (SGB V)" => Array
	    (
	        "is_requested" => Array
	        (
	            "MP_Code" => "rubin_cvc",
	            "LaborValueCode" => "rubin_ctnp",
	            "comment" => "if in contactformblock Leistung/Koordination for 'Palliativversorgung (SGB V),  ' checkbox 2  is selected",
	            "CustomCatalogEntryRef" => "rubin_ctn",
	            "CrfTemplateSectionName" => "rubin_cre",
	            "CrfTemplateFieldLowerRow" => "6",
	            "CrfTemplateFieldLowerColumn" => "2",
	        ),
	        
	    ),
	    
	    "Ernährungsberatung (SGB V)" => Array
	    (
	        "receives_services" => Array
	        (
	            "MP_Code" => "rubin_cvc",
	            "LaborValueCode" => "rubin_ctnn",
	            "comment" => "if in contactformblock Leistung/Koordination for 'Ernährungsberatung (SGB V),  ' checkbox 1  is selected",
	            "CustomCatalogEntryRef" => "rubin_ctn",
	            "CrfTemplateSectionName" => "rubin_cre",
	            "CrfTemplateFieldLowerRow" => "6",
	            "CrfTemplateFieldLowerColumn" => "2",
	        ),
	        
	    ),
	    
	    "Rehasport (SGB V; SGB XI)" => Array
	    (
	        "is_requested" => Array
	        (
	            "MP_Code" => "rubin_cvc",
	            "LaborValueCode" => "rubin_ctnm",
	            "comment" => "if in contactformblock Leistung/Koordination for 'Rehasport (SGB V; SGB XI),  ' checkbox 2  is selected",
	            "CustomCatalogEntryRef" => "rubin_ctn",
	            "CrfTemplateSectionName" => "rubin_cre",
	            "CrfTemplateFieldLowerRow" => "6",
	            "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Hilfsmittel: (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnj",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Hilfsmittel: (SGB V),  ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnk",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Hilfsmittel: (SGB V),  ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Logopädie (SGB V)" => Array
	        (
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctni",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Logopädie (SGB V), ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Ergotherapie (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnh",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Ergotherapie (SGB V),  ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Krankengymnastik (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnf",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Krankengymnastik (SGB V),  ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctne",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Krankengymnastik (SGB V),  ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Physiotherapie (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnd",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Physiotherapie (SGB V),  ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnc",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Physiotherapie (SGB V),  ' checkbox 2  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Wundversorgung (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctnb",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Wundversorgung (SGB V),   ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Medikationscheck (SGB V)" => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvc",
	                "LaborValueCode" => "rubin_ctna",
	                "comment" => "if in contactformblock Leistung/Koordination for 'Medikationscheck (SGB V), ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctn",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "6",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	        ),
	        
	        "Schwer Behinderten Ausweis (SGB IX), - Grad der Behinderung," => Array
	        (
	            "receives_services" => Array
	            (
	                "MP_Code" => "rubin_cvf",
	                "LaborValueCode" => "rubin_ctqb",
	                "comment" => "if in contactformblock Leistung/Koordination for *Schwer Behinderten Ausweis (SGB IX), - Grad der Behinderung, ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctq",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "12",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                ),
	                
            "Feststellung der Schwerbehinderteneigenschaft und Gewährung von Leistungen nach dem Landesblindengeldgesetz (SGB IX)" => Array
                (
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvf",
                            "LaborValueCode" => "rubin_ctqa",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Feststellung der Schwerbehinderteneigenschaft und Gewährung von Leistungen nach dem Landesblindengeldgesetz (SGB IX),  ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctq",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "12",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                ),
	                
            "Wohnumfeld verbessernde Maßnahmen (SGB XI)" => Array
                (
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctru",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Wohnumfeld verbessernde Maßnahmen (SGB XI), ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvn",
                            "LaborValueCode" => "rubin_ctye",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Wohnumfeld verbessernde Maßnahmen (SGB XI),r' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_cty",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "30",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                ),
	                
            "Hausnotruf (SGB XI)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrt",
                            "comment" => "if in contactformblock Leistung/Koordination for *Hausnotruf (SGB XI),  ' checkbox 1  is selected",
	                "CustomCatalogEntryRef" => "rubin_ctr",
	                "CrfTemplateSectionName" => "rubin_cre",
	                "CrfTemplateFieldLowerRow" => "14",
	                "CrfTemplateFieldLowerColumn" => "2",
	            ),
	            
	            "is_requested" => Array
	            (
	                "MP_Code" => "rubin_cvg",
	                "LaborValueCode" => "rubin_ctrs",
	                "comment" => "if in contactformblock Leistung/Koordination for *Hausnotruf (SGB XI),  ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                ),
	                
            "Niedrigschwellige Betreuungs- und Entlastungsleistung (SGB XI)" => Array
                (
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrq",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Niedrigschwellige Betreuungs- und Entlastungsleistung (SGB XI), ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrr",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Niedrigschwellige Betreuungs- und Entlastungsleistung (SGB XI), ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                ),
	                
            "Ambulanter Pflegedienst (SGB XI) - Grundpflege" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrp",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Ambulanter Pflegedienst (SGB XI), - Grundpflege ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctro",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Ambulanter Pflegedienst (SGB XI), - Grundpflege ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                ),
	                
            "Pflegegrad (SGB XI)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrn",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Pflegegrad (SGB XI), ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrm",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Pflegegrad (SGB XI), ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
	                
                ),
	                
            "Häusliche Krankenpflege (SGB XI; SGB V)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrl",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Häusliche Krankenpflege (SGB XI; SGB V), ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrk",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Häusliche Krankenpflege (SGB XI; SGB V), ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Vollstationäre Pflege (SGB XI)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrj",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Vollstationäre Pflege (SGB XI), ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctre",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Vollstationäre Pflege (SGB XI), ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Stationäre Pflege (SGB XI)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrd",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Stationäre Pflege (SGB XI), ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrc",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Stationäre Pflege (SGB XI), ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Verhinderungspflege (SGB XI)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctry",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Verhinderungspflege (SGB XI),  ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrx",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Verhinderungspflege (SGB XI),  ' checkbox 2 is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Kurzzeitpflege(SGB XI)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrw",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Kurzzeitpflege(SGB XI),   ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrv",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Kurzzeitpflege(SGB XI),   ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Tagespflege (SGB XI)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctrb",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Tagespflege (SGB XI), ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvg",
                            "LaborValueCode" => "rubin_ctra",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Tagespflege (SGB XI), ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctr",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "14",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Widerspruchsverfahren (SGB X)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvh",
                            "LaborValueCode" => "rubin_ctsb",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Widerspruchsverfahren (SGB X),' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_cts",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "16",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvh",
                            "LaborValueCode" => "rubin_ctsa",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Widerspruchsverfahren (SGB X),' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_cts",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "16",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Betreuungsverfügung (BGB)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvj",
                            "LaborValueCode" => "rubin_ctuf",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Betreuungsverfügung (BGB),  ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctu",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "20",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvj",
                            "LaborValueCode" => "rubin_ctue",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Betreuungsverfügung (BGB),  ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctu",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "20",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Vorsorgevollmacht (BGB)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvj",
                            "LaborValueCode" => "rubin_ctud",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Vorsorgevollmacht (BGB), ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctu",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "20",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvj",
                            "LaborValueCode" => "rubin_ctuc",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Vorsorgevollmacht (BGB), ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctu",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "20",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Patientenverfügung (BGB)" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvj",
                            "LaborValueCode" => "rubin_ctub",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Patientenverfügung (BGB),  ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctu",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "20",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvj",
                            "LaborValueCode" => "rubin_ctua",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Patientenverfügung (BGB),  ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctu",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "20",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Ehrenamtsnetz" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvk",
                            "LaborValueCode" => "rubin_ctvb",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Ehrenamtsnetz  ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctv",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "22",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                    "is_requested" => Array
                        (
                            "MP_Code" => "rubin_cvk",
                            "LaborValueCode" => "rubin_ctva",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Ehrenamtsnetz  ' checkbox 2  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctv",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "22",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Bewegungsangebote" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvl",
                            "LaborValueCode" => "rubin_ctwb",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Bewegungsangebote  ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctw",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "24",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Sonstige Dienstleiste" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvn",
                            "LaborValueCode" => "rubin_ctyl",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Sonstige Dienstleister' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_cty",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "30",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Selbsthilfegruppen" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvn",
                            "LaborValueCode" => "rubin_ctyj",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Selbsthilfegruppen ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_cty",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "30",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Mittagstisch" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvn",
                            "LaborValueCode" => "rubin_ctym",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Mittagstisch ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_cty",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "30",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Präventive Maßnahmen" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvn",
                            "LaborValueCode" => "rubin_ctyi",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Präventive Maßnahmen' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_cty",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "30",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Soziale Aktivierung" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvn",
                            "LaborValueCode" => "rubin_ctyh",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Soziale Aktivierung ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_cty",
                            "CrfTemplateSectionName" => "rubin_cre",
                            "CrfTemplateFieldLowerRow" => "30",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Zahnarzt" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzo",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Zahnarzt' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Kardiologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzn",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Kardiologe ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Psychotherapeut" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzm",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Psychotherapeut ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Nephrologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzp",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Nephrologe ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Psychiater" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzl",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Psychiater ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Endokrinologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzk",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Endokrinologe ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Augenarzt" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzj",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Augenarzt' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Gynäkologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzi",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Gynäkologe ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Urologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzh",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Urologe' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Orthopäde" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzg",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Orthopäde' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Dermatologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzf",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Dermatologe' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Diabetologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctze",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Diabetologe' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung HNO Arzt" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzd",
                            "comment" => "if in contactformblock Leistung/Koordination for 'HNO Arzt' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Hypertensiologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzc",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Hypertensiologe ' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Neurologe" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctzb",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Neurologe' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                                
                ),
                                
            "Weiterleitung Geriater" => Array
                (
                    "receives_services" => Array
                        (
                            "MP_Code" => "rubin_cvo",
                            "LaborValueCode" => "rubin_ctza",
                            "comment" => "if in contactformblock Leistung/Koordination for 'Geriater' checkbox 1  is selected",
                            "CustomCatalogEntryRef" => "rubin_ctz",
                            "CrfTemplateSectionName" => "rubin_wfa",
                            "CrfTemplateFieldLowerRow" => "2",
                            "CrfTemplateFieldLowerColumn" => "2",
                        ),
                        
                ),
	        
	    );
	    
	    return $actions;
	    
	    
	}
	
	/**
	 * ISPC-2895 Ancuta 25.05.2021
	 * @return string[][][]
	 */
	private function ca_bottom_actions(){
        $actions = array(
            "Informationsmaterial / Vordrucke gewünscht über:" => array(

                "incontinence_protocol" => Array(
                    "MP_Code" => "rubin_cvq",
                    "LaborValueCode" => "rubin_cuad",
                    "comment" => "if in contactformblock Leistung/Koordination'Kontinenzprotokoll' is selected",
                    "CustomCatalogEntryRef" => "rubin_cua",
                    "CrfTemplateSectionName" => "rubin_wfa",
                    "CrfTemplateFieldLowerRow" => "0",
                    "CrfTemplateFieldLowerColumn" => "2"
                ),

                "sleep_diary" => Array(
                    "MP_Code" => "rubin_cvq",
                    "LaborValueCode" => "rubin_cuac",
                    "comment" => "if in contactformblock Leistung/Koordination'Schlaftagebuch' is selected",
                    "CustomCatalogEntryRef" => "rubin_cua",
                    "CrfTemplateSectionName" => "rubin_wfa",
                    "CrfTemplateFieldLowerRow" => "0",
                    "CrfTemplateFieldLowerColumn" => "2"
                ),

                "pain_diary" => Array(
                    "MP_Code" => "rubin_cvq",
                    "LaborValueCode" => "rubin_cuab",
                    "comment" => "if in contactformblock Leistung/Koordination'Schmerztagebuch' is selected",
                    "CustomCatalogEntryRef" => "rubin_cua",
                    "CrfTemplateSectionName" => "rubin_wfa",
                    "CrfTemplateFieldLowerRow" => "0",
                    "CrfTemplateFieldLowerColumn" => "2"
                ),
                "hand_strength_training" => Array(
                    "MP_Code" => "rubin_cvq",
                    "LaborValueCode" => "rubin_cuaa",
                    "comment" => "if in contactformblock Leistung/Koordination'Handkrafttraining' is selected",
                    "CustomCatalogEntryRef" => "rubin_cua",
                    "CrfTemplateSectionName" => "rubin_wfa",
                    "CrfTemplateFieldLowerRow" => "0",
                    "CrfTemplateFieldLowerColumn" => "2"
                )
            )
        );

        return $actions;
    }
	
    
    /**
     * ISPC-2895 Ancuta 25.05.2021
     * @return string[][][]
     */
	private function de_actions(){
        $actions = array( 
            
   
                "vaccination_sgbv" => Array
                (
                    "MP_Code" => "rubin_cvp",
                    "LaborValueCode" => "rubin_ctkh",
                    "comment" => "if in contactformblock Delegation 'Impfung (SGB V)'is selected",
                    "CustomCatalogEntryRef" => "rubin_ctk",
                    "CrfTemplateSectionName" => "rubin_cre",
                    "CrfTemplateFieldLowerRow" => "32",
                    "CrfTemplateFieldLowerColumn" => "2",
                ),
                "injection_sgbv" => Array
                (
                    "MP_Code" => "rubin_cvp",
                    "LaborValueCode" => "rubin_ctkg",
                    "comment" => "if in contactformblock Delegation 'Injektion (SGB V)'is selected",
                    "CustomCatalogEntryRef" => "rubin_ctk",
                    "CrfTemplateSectionName" => "rubin_cre",
                    "CrfTemplateFieldLowerRow" => "32",
                    "CrfTemplateFieldLowerColumn" => "2",
                ),
                "bz_measurement_sgbv" => Array
                (
                    "MP_Code" => "rubin_cvp",
                    "LaborValueCode" => "rubin_ctkf",
                    "comment" => "if in contactformblock Delegation 'BZ-Messung (SGB V)'is selected",
                    "CustomCatalogEntryRef" => "rubin_ctk",
                    "CrfTemplateSectionName" => "rubin_cre",
                    "CrfTemplateFieldLowerRow" => "32",
                    "CrfTemplateFieldLowerColumn" => "2",
                ),
                "inr_measurement_sgbv" => Array
                (
                    "MP_Code" => "rubin_cvp",
                    "LaborValueCode" => "rubin_ctke",
                    "comment" => "if in contactformblock Delegation 'INR-Messung (SGB V)'is selected",
                    "CustomCatalogEntryRef" => "rubin_ctk",
                    "CrfTemplateSectionName" => "rubin_cre",
                    "CrfTemplateFieldLowerRow" => "32",
                    "CrfTemplateFieldLowerColumn" => "2",
                ),
                "blood_collection_sgbv" => Array
                (
                    "MP_Code" => "rubin_cvp",
                    "LaborValueCode" => "rubin_ctkd",
                    "comment" => "if in contactformblock Delegation 'Blutentnahme(SGB V)'is selected",
                    "CustomCatalogEntryRef" => "rubin_ctk",
                    "CrfTemplateSectionName" => "rubin_cre",
                    "CrfTemplateFieldLowerRow" => "32",
                    "CrfTemplateFieldLowerColumn" => "2",
                ),
                "catheter_replacement_sgbv" => Array
                (
                    "MP_Code" => "rubin_cvp",
                    "LaborValueCode" => "rubin_ctkc",
                    "comment" => "if in contactformblock Delegation 'Katheterwechsel(SGB V)' is selected",
                    "CustomCatalogEntryRef" => "rubin_ctk",
                    "CrfTemplateSectionName" => "rubin_cre",
                    "CrfTemplateFieldLowerRow" => "32",
                    "CrfTemplateFieldLowerColumn" => "2",
                ),
                "wound_care_sgbv" => Array
                (
                    "MP_Code" => "rubin_cvp",
                    "LaborValueCode" => "rubin_ctkb",
                    "comment" => "if in contactformblock Delegation 'Wundversorgung (SGB V)' is selected",
                    "CustomCatalogEntryRef" => "rubin_ctk",
                    "CrfTemplateSectionName" => "rubin_cre",
                    "CrfTemplateFieldLowerRow" => "32",
                    "CrfTemplateFieldLowerColumn" => "2",
                ),
                
         
                "medication_check_sgbv" => Array
                (
                    "MP_Code" => "rubin_cvp",
                    "LaborValueCode" => "rubin_ctka",
                    "comment" => "if in contactformblock Delegation 'Medikationscheck (SGB V)' is selected",
                    "CustomCatalogEntryRef" => "rubin_ctk",
                    "CrfTemplateSectionName" => "rubin_cre",
                    "CrfTemplateFieldLowerRow" => "32",
                    "CrfTemplateFieldLowerColumn" => "2",
                )
                
            
        );

        return $actions;
    }
	
    
    public function isValid_RubinXML($xml_string)
    {
        $logininfo = new Zend_Session_Namespace ( 'Login_Info' );
        $clientid = $logininfo->clientid;
        
        
        libxml_use_internal_errors(true);
        
        $xmlerrors = array();
        
        
        $schemePath = PUBLIC_PATH . "/xsd/RUBIN.xsd";
 
        $dom = new DomDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        
        $result = $dom->loadXML($xml_string);
        
        if ($result === false) {
            
            $xmlerrors[] = "Document is not well formed\n";
            
            $validation = false;
            
        } elseif (@($dom->schemaValidate($schemePath))) {
            
            $validation = true;
            
        } else {
            
            $validation = false;
            
            $xmlerrors[] = "! Document is not valid:\n";
            $errors = libxml_get_errors();
            
            foreach ($errors as $error) {
                if (APPLICATION_ENV == 'development') {
                    $xmlerrors[] = "---\n" . sprintf("file: %s, line: %s, column: %s, level: %s, code: %s\nError: %s",
                        basename($error->file),
                        $error->line,
                        $error->column,
                        $error->level,
                        $error->code,
                        $error->message
                        );
                } else {
                    $xmlerrors[] = "---\n" . sprintf("Error: %s", $error->message);
                }
            }
        }
        
        $return = array();
        
        if ( ! empty($xmlerrors) ) {
            // invalid xml
            
            //we log in here.. this log should have been outside $this->_xml_xsd_errors = $xmlerrors ...
//             $this->_log_error('FAILED to ' . __METHOD__ . ' for client ' . $clientid . " " . print_r($xmlerrors, true));
            //$this->_log_error($xml_string);
            $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/rubin.log');
            $log = new Zend_Log($writer);
            $log->info('FAILED to ' . __METHOD__ . ' for client ' . $clientid . " " . print_r($xmlerrors, true));
            
            $return['success'] = false;
            $return['errors'] = $xmlerrors;
//             return false;
            return $return;
            
        } else {
            $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../public/log/rubin.log');
            $log = new Zend_Log($writer);
            $log->info('SUCCESS ');
            
//             return true;
            $return['success'] = true;
            return $return;
            
        }
        
    }
}
?>