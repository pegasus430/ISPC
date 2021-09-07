<?php
class VstnController  extends Pms_Controller_Action {
    public function init() {
	    $this->random_export_number = 5750; // Aleator number for external id
	}
	
	public function exportAction() {
		set_time_limit ( 0 );
		
		
		// error_reporting(E_ALL);
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
		
		$page_lang = $this->view->translate('vstn_export');
		
		if ($this->getRequest ()->isPost ()) {
			
		    
		    //  EXPORT DATE 
		    $export_date = date('d.m.Y');
		    $export_date__dmy = date('d-m-Y');
		    $export_date__dmyHi = date('d-m-Y_H-i');
		    
			
			$lanr = $_POST['lanr'];
			$type = $_POST['type'];
			$s = array(" ","	","\n","\r");
			$ado_id = trim(str_replace($s,array(),$_POST['ado_id']));
			$ado_text_id = trim(str_replace($s,array(),$_POST['ado_text_id']));
			$ado_text = $_POST['ado_text'];
			$ik_option = $_POST['ik_option'];
			$status_option = $_POST['status_option'];
			$diagnosis_side = $_POST['diagnosis_side_option'];
			$diagnosis_main = $_POST['diagnosis_main_option'];
			$action_user = $_POST['user'];
			$action_group = $_POST['group_type'];
			
			$zip_density = ZipDensity::get_zip_density();
			
			if(!empty($_REQUEST['patients']) && !empty($_REQUEST['period']['start']) && !empty($_REQUEST['period']['end'])) {
				
				$ipids = $_REQUEST['patients'];
				$period_start = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['start']));
				$period_end = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['end']));
				
				$quarter_first_day = strtotime($_REQUEST['period']['start']);
				$quarter_last_day = strtotime($_REQUEST['period']['end']);

				$select = "e.epid_num, AES_DECRYPT(p.last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(p.first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(p.zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(p.street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(p.city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(p.phone,'".Zend_Registry::get('salt')."') using latin1) as phone, convert(AES_DECRYPT(p.sex,'".Zend_Registry::get('salt')."') using latin1) as sex,";
				$periods = array ( '0' => array('start' => date('Y-m-d H:i:s', strtotime($period_start)), 'end' => date('Y-m-d H:i:s', strtotime($period_end))));
				
				$period_days = PatientMaster::getDaysInBetween(date("Y-m-d", strtotime($period_start)), date("Y-m-d", strtotime($period_end)), false);

				$active_cond['interval_sql'] = '((date(%date_start%) <= "'.$periods[0]['start'].'") AND (date(%date_end%) >= "'.$periods[0]['start'].'")) OR ((date(%date_start%) >= "'.$periods[0]['start'].'") AND (date(%date_start%) < "'.$periods[0]['end'].'"))';
					
				if($_REQUEST['tp'] == 'ipids') {
					
				}  
				//$ipids = array("0b6cfbe94606f0c3de5f58d93438380a1063bb88","636fe8548311ab8cf55af0d6b1909a74ddded138");
				
				// Active in period
				$patients_arr = Pms_CommonData::patients_days(array('ipids' => $ipids, 'periods' => $periods, 'client' => $clientid), $select); // TODO -498 - remove standby 02.09.2016
				// recreate the ipid- to be sure is active in period
				$ipids = array_keys($patients_arr);
				
				
				// OVERALL perids of patients 
				$periods_overall = array(
                    '0' => array(
                        'start' => "2009-01-01 00:00:00",
                        'end' => date('Y-m-d H:i:s')
                    )
                );
				$patients_overall_arr = Pms_CommonData::patients_days(array('ipids' => $ipids, 'periods' => $periods_overall, 'client' => $clientid), $select); // TODO -498 - remove standby 02.09.2016
				
				
				
				
				
                //patient death detaills
				$patient_death_dates = PatientDischarge::getPatientsDeathDate($clientid,$ipids);

				
				
				
				// Discharge method details :: dead method
				$DischargeMethod_obj = new DischargeMethod();
				$client_death_methods = array();
				$client_death_methods = $DischargeMethod_obj->get_client_discharge_method($clientid,true);
				$client_discharge_methods = array();
				$client_discharge_methods = $DischargeMethod_obj->get_client_discharge_method($clientid);
				
				
				
				
				// Discharge location details + discharge location types 
				$DischargeLocation_obj = new DischargeLocation();
				$client_discharge_locations = array();
				$client_discharge_locations = $DischargeLocation_obj->getDischargeLocation($clientid,0);
				$system_discharge_locations_types =  Pms_CommonData::getDischargeLocationTypes();
				
				foreach ($client_discharge_locations as $k=>$dl ){
				    if($dl['type'] != 0 ){
    				    $discharge_location_types[$dl['id']] = $system_discharge_locations_types [$dl['type']];  
				    }
				}
				
				
				
				
				// Patient discharge details 
				$discharge_data_q = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				->whereIn('ipid', $ipids);
				$discharge_data_arr = $discharge_data_q->fetchArray();

				$patients_discharge_data  = array();
				$patients_discharge_data_details  = array();
				foreach ($discharge_data_arr as $k => $dis) {
                    
                    $patients_discharge_data[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))] = $dis;
                    $patients_discharge_data_details[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))] = $dis;
                    if (strlen($client_discharge_methods[$dis['discharge_method']])) {
                        $patients_discharge_data[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['discharge_method_str'] = $client_discharge_methods[$dis['discharge_method']];
                        $patients_discharge_data_details[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['discharge_method_str'] = $client_discharge_methods[$dis['discharge_method']];
                    }
                    
                    if (in_array($dis['discharge_method'], $client_death_methods)) {
                        $patients_discharge_data[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['died'] = 1;
                        $patients_discharge_data_details[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['died'] = 1;
                        if ($dis['discharge_location'] != 0) {
                            $patients_discharge_data[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['type_location_of_death'] = $discharge_location_types[$dis['discharge_location']];
                            $patients_discharge_data_details[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['type_location_of_death'] = $discharge_location_types[$dis['discharge_location']];
                        }
                    } else {
                        
                        $patients_discharge_data[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['died'] = 0;
                        $patients_discharge_data_details[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['died'] = 0;
                        $patients_discharge_data[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['type_location_of_death'] = "";
                        $patients_discharge_data_details[$dis['ipid']][date('d.m.Y', strtotime($dis['discharge_date']))]['type_location_of_death'] = "";
                    }
                }
				

                
                // DGP DATA
                $dgp_kern_model = new DgpKern();
                $text_arrays = $dgp_kern_model->get_form_texts();
                
                // Retrive all saved forms
                $dgp_forms = DgpKern::findFormsOfIpids($ipids);
                
                $dgp_discharge2readmission_id = array();
                foreach ($dgp_forms as $k => $dgp ){
                    if($dgp['form_type'] == "adm" && !empty($dgp['TwinDgpKern']['patient_readmission_ID'])){
                        $dgp_discharge2readmission_id [$dgp['ipid']] [ $dgp ['TwinDgpKern'] ['patient_readmission_ID'] ] = $dgp['TwinDgpKern'];
                    }
                }
                
				// Contact persons
				$patient_contact_personds = ContactPersonMaster::getContactPersonsByIpids($ipids);
				$cps_array = array();
				$cps_details_array = array();
				foreach($patient_contact_personds as $k=>$cps){
				    $cps_details_array[$cps['ipid']][] = $cps;
				    $cps_array[$cps['ipid']][] = $cps;
				}
				
				// Readmission details 
				$readmarr  = Doctrine_Query::create()
				->select("*")
				->from('PatientReadmission')
				->whereIn('ipid', $ipids)
				->fetchArray();
				
				$admission_dates = array();
				$admissiondate2readmissionid = array();
				$dischargedate2readmissionid = array();
				
				if(!empty($readmarr)){
				    
				    foreach($readmarr as $k=>$rdm_data){
				        
				        if($rdm_data['date_type'] == "1"){
				            
				            $admission_dates[$rdm_data['ipid']][] = $rdm_data['date']; // used for diagnosis sheet
				            $admissiondate2readmissionid[$rdm_data['ipid']][date('d.m.Y',strtotime($rdm_data['date']))] = $rdm_data['id'];
    				    }
    				    
				        if($rdm_data['date_type'] == "2"){
				            
				            $dischargedate2readmissionid[$rdm_data['ipid']][date('d.m.Y',strtotime($rdm_data['date']))] = $rdm_data['id'];;
    				    }
				    }
				}
				
				// Patient Details
				$external_source = "ISPC";
				$external_id = array();
				$external_id_client = array();
				$patient_familydoc_id = array();
				$admission_periods  =array();
				$patient_details  =array();
				$Last_discharge_in_period =array();
				$patient_days =array();
				$block_export_id = array();
				
				foreach($patients_arr as $pipid=>$pdata){
				    // EXPORT ID 
				    $external_id[$pipid] = ($pdata ['details'] ['clientid'] + $this->random_export_number).'-'.$pdata ['details'] ['epid_num'] ;
				    $block_export_id[$pipid] = ($pdata ['details'] ['clientid'] + $this->random_export_number).$pdata ['details'] ['epid_num'] ;
    				$patient_details[$pipid]['Export_id'] =  $external_id[$pipid] ;
    				
				    // EXPORT ID - CLIENT 
				    $external_id_client[$pipid] = $pdata ['details'] ['clientid'] + $this->random_export_number;
				    $patient_details[$pipid]['Export_client_id'] =  $external_id_client[$pipid] ;
    				
				    $patient_details[$pipid]['EPID'] = $pdata ['details'] ['epid'];
    				
    				
				    // GENDER
				    if ( $pdata['details']['sex'] == "1") {
    				    $patient_details[$pipid]['gender'] = $page_lang['male'];
				    }
				    elseif ( $pdata['details']['sex'] == "2") {
    				    $patient_details[$pipid]['gender'] = $page_lang['female'];
				    } 
				    elseif ( $pdata['details']['sex'] == "0") {
    				    $patient_details[$pipid]['gender'] = $page_lang['divers'];
				    } 
				    else {
    				    $patient_details[$pipid]['gender'] =  "---";//TODO-2179 08.10.2019
//     				    $patient_details[$pipid]['gender'] =  $page_lang['other'];
				    }
				    
				    
				    // AGE
				    $tod_date_patient = '';
				    if(array_key_exists($ipid, $patient_death_dates))
				    {
				        $tod_date_patient = $patient_death_dates[$pipid];
				    }
				    else
				    {
				        if(strtotime($period_end) >= strtotime('now'))
				        {
				    
				            $tod_date_patient = date("Y-m-d", time());
				        }
				        else
				        {
				            $tod_date_patient = date("Y-m-d", strtotime($period_end));
				        }
				    }
                    $patient_details[$pipid]['age']  = $PatientMaster_obj->GetAge($pdata['details']['birthd'], $tod_date_patient, true);
                    
                    
                    
                    // YEAR OF BIRTH
                    $patient_details[$pipid]['birth_year']  = date("Y",strtotime($pdata['details']['birthd']));
                    
                    
                    // ZIP DENSITY
                    if (!empty($pdata['details']['zip'])){
                        $patient_details[$pipid]['zip_density'] = $page_lang[$zip_density[trim($pdata['details']['zip'])]];
                    } else {
                        $patient_details[$pipid]['zip_density'] = "";
                    }                    
                    
                    // Familydoctor ids 
                    $patient_details[$pipid]['familydoc_id'] = $pdata['details']['familydoc_id'];
                    $patient_familydoc_id[] = $pdata['details']['familydoc_id'];
                    
                    $patient_details[$pipid]['caregiver_str'] = "";
                    
                    
                    // Contact persons Available
                    if(!empty($cps_array[$pipid])){
                        $patient_details[$pipid]['contact_perons'] = "Ja";
                    } else{
                        $patient_details[$pipid]['contact_perons'] = "Nein";
                    }
                    
                    
                    // ACP
                    $patient_details[$pipid]['living_will'] = 'Nein';
                    $patient_details[$pipid]['healthcare_proxy'] = 'Nein';
                    $patient_details[$pipid]['care_orders'] = 'Nein';
                    
                    // ACTIVE DAYS IN PERIOD
                    $patient_details[$pipid]['active_in_period'] = $pdata['real_active_days'];
                    $patient_days[$pipid]['active'] = $pdata['real_active_days'];
                    

                    // ADMISSION FALLS - IN RAPORTED  PERIOD
                    $adm_substitute = 1;
                    foreach($pdata['active_periods'] as $period_identification => $period_details)
                    {
                    
                        $admission_periods[$pipid][$adm_substitute ]['start'] = $period_details['start'];
                        $admission_periods[$pipid][$adm_substitute ]['end'] = $period_details['end'];
                        if(in_array($period_details['end'],array_values($pdata['discharge']))){
                            $admission_periods[$pipid][$adm_substitute ]['valid_discharge_date'] = $period_details['end'];
                        } else{
                            $admission_periods[$pipid][$adm_substitute ]['valid_discharge_date'] = '';
                        }
                        $admission_periods[$pipid][$adm_substitute ]['days'] = $PatientMaster_obj->getDaysInBetween($period_details['start'], $period_details['end'],false,"d.m.Y");
                        $admission_ids[$pipid][] = $adm_substitute ;
                        $adm_substitute++;
                    }
                    
                    // DISCHARGE DATES
                    $patient_details[$pipid]['discharge_dates_array'] = array_values($pdata['discharge']) ;
                    
                    
                    // Hospital days
                    $patient_details[$pipid]['hospital_days'] = $pdata['hospital']['days'];
                    $patient_days[$pipid]['hospital_days'] = $pdata['hospital']['days'];
                    // Hospiz days
                    
                    // Treatment days 
                    $patient_details[$pipid]['treatment_days'] = $pdata['treatment_days'];
                    
                    // death location
                    
                    usort($patients_discharge_data[$pipid], array(new Pms_Sorter('discharge_date'), "_date_compare"));
                    $Last_discharge_in_period[$pipid]  =  end($patients_discharge_data[$pipid]);
                    
                    if(!empty($Last_discharge_in_period[$pipid]) && $Last_discharge_in_period[$pipid]['died'] =="1"){
                        $patient_details[$pipid]['death_ocation'] = $Last_discharge_in_period[$pipid]['type_location_of_death'];
                    } else{
                        $patient_details[$pipid]['death_ocation'] = "";
                    }
				}

				// OVERALL PATIENTS DETAILS
				$admission_overall_ids = array();
				$admission_periods_overall = array();
				$patient_details_hospital_overall = array();
				$patient_days2locationtypes= array();
			
				foreach($patients_overall_arr as $pipid_ov=>$pdata_ov){
				    
                    // ACTIVE DAYS IN PERIOD
                    $patient_details[$pipid_ov]['overall_active_days'] = $pdata_ov['real_active_days'];
                    $patient_details[$pipid_ov]['overall_treatment_days'] = $pdata_ov['treatment_days'];//Added on 04.09.2018 By Ancuta - for comment from 03.09.2018
                    $patient_days[$pipid_ov]['overall_active'] = $pdata_ov['real_active_days'];
                    
                    $patient_days[$pipid_ov]['first_active_date'] = $pdata_ov['real_active_days'][0];
                    $patient_days[$pipid_ov]['last_active_date'] = end($pdata_ov['real_active_days']);
                    

                    // ADMISSION FALLS - IN RAPORTED  PERIOD
                    $adm_substitute = 1;
                    foreach($pdata_ov['active_periods'] as $period_identification => $period_details)
                    {
                    
                        $admission_periods_overall[$pipid_ov][$adm_substitute ]['start'] = $period_details['start'];
                        $admission_periods_overall[$pipid_ov][$adm_substitute ]['end'] = $period_details['end'];
                        if(in_array($period_details['end'],array_values($pdata_ov['discharge']))){
                            $admission_periods_overall[$pipid_ov][$adm_substitute ]['valid_discharge_date'] = $period_details['end'];
                        } else{
                            $admission_periods_overall[$pipid_ov][$adm_substitute ]['valid_discharge_date'] = '';
                        }
                        $admission_periods_overall[$pipid_ov][$adm_substitute ]['days'] = $PatientMaster_obj->getDaysInBetween($period_details['start'], $period_details['end'],false,"d.m.Y");
                        $admission_overall_ids[$pipid_ov][] = $adm_substitute ;
                        
                        if(array_key_exists($period_details['start'], $admissiondate2readmissionid[$pipid_ov])){
                            $admission_periods_overall[$pipid_ov][$adm_substitute ]['patient_readmission_adm_ID'] = $admissiondate2readmissionid[$pipid_ov][$period_details['start']];
                        }
                        if(array_key_exists($period_details['end'], $dischargedate2readmissionid[$pipid_ov])){
                            $admission_periods_overall[$pipid_ov][$adm_substitute ]['patient_readmission_dis_ID'] = $dischargedate2readmissionid[$pipid_ov][$period_details['end']];
                        }
                        
                        $adm_substitute++;
                        
                    }
                    // DISCHARGE DATES
                    $patient_details[$pipid_ov]['overall_discharge_dates_array'] = array_values($pdata_ov['discharge']) ;
                    
                    // Hospital days
                    if(!empty($pdata_ov['hospital']['days'])){
                            
                        $patient_details[$pipid_ov]['overall_hospital_days'] = $pdata_ov['hospital']['days'];
                        $patient_days[$pipid_ov]['overall_hospital_days'] = $pdata_ov['hospital']['days']; 
                        $patient_details_hospital_overall[$pipid_ov] = $pdata_ov['hospital'];
                        
                        $patient_days[$pipid_ov]['overall_FULL_hospital_days'] = $patient_days[$pipid_ov]['overall_hospital_days'];
                        foreach($pdata_ov['locations'] as $ploc_id=>$loc_datails ){
                            
                            if($loc_datails['type'] == 1) // hospital
                            {
                                $patient_days[$pipid_ov]['overall_hospital_admissions'][]  = $loc_datails['period']['start'];
                                $patient_days[$pipid_ov]['overall_hospital_discharge'][]  = $loc_datails['period']['end'];
                                if( $loc_datails['valid_till'] != "0000-00-00 00:00:00"){
                                    $patient_days[$pipid_ov]['overall_hospital_discharge_real'][]  = $loc_datails['valid_till'];
                                }
                                
                                if($loc_datails['period']['start'] == $loc_datails['period']['end']){
                                    $patient_days[$pipid_ov]['overall_hospital_admdis'][] = $loc_datails['period']['start'];
                                }
                                
                                // remove_hospital admissions and discharge from hospital days
                                $kadm = null;
                                $kadm = array_search($loc_datails['period']['start'], $patient_days[$pipid_ov]['overall_FULL_hospital_days']);
                                if($kadm !== NULL){
                                    unset($patient_days[$pipid_ov]['overall_FULL_hospital_days'][$kadm]); 
                                }
                                
                                $kdis = null;
                                $kdis = array_search($loc_datails['period']['end'], $patient_days[$pipid_ov]['overall_FULL_hospital_days']);
                                if($kdis !== NULL && $loc_datails['valid_till'] != "0000-00-00 00:00:00"){
                                    unset($patient_days[$pipid_ov]['overall_FULL_hospital_days'][$kdis]); 
                                }
                 
                            }
                        }
                    }
                    
                    // Treatment days 
                    $patient_details[$pipid_ov]['overall_treatment_days'] = $pdata_ov['treatment_days'];
                    
                    
                    foreach($pdata_ov['locations'] as $pat_location_row_id => $pat_location_data)
                    {
                        foreach($pat_location_data['days'] as $kl=>$lday)
                        {
                            if( empty($pat_location_data['type'])){
                                $pat_location_data['type'] = 0 ;
                            }
                
                            if($pat_location_data['discharge_location'] != 1 ){
                                $patient_days2locationtypes[$pipid_ov][$lday][] = $pat_location_data['type'];
                            }
                        }
                    }
				}
			
				// get sapv data
				// sapv  days !!! SAPV DAYS ARE MANDATORY !!!
				$patient_sapv_array = array();
				$patient_sapv_array = $SapvVerordnung_obj->get_patients_sapv_periods($ipids);
				// THIS ARRAY HOLDS ALL SAPV DATA

				//get only in admission 
				$sapv_in_export_period  = array();
				$sapv_in_admission_period  = array();
				$sapv_in_report_period  = array();
				// all sapv no matter admission or report period
				$sapv_data_array = array();

				foreach($patient_sapv_array as $sapv_ipid => $sapv_data)
				{
				    $kd = 0;
                    foreach($sapv_data as $sapv_id => $sv_period){
                        
                        foreach($sv_period['days'] as $sapv_day){
    				        if( in_array($sapv_day,$patient_details[$sapv_ipid]['overall_active_days'] )){ // Added on 04.09.2018 By Ancuta - for comment from 03.09.2018 - from "in report period" to "OVERALL"""// CHANGED AGAIN - 14.09.2018 - added by Ancuta - ast this fuckers (NR) clients do not use treatment days  
    				            $patient_details[$sapv_ipid]['valid_sapv_days'][] = $sapv_day; 
    				        }
    				        $patient_days[$sapv_ipid]['overall_sapv_days'][] = $sapv_day;
    				        //$patient_days[$sapv_ipid]['sapv_days'][$sapv_day] = $sv_period['highest'];
    				        $patient_days[$sapv_ipid]['sapv_days'][$sapv_day] = implode(',',$sv_period['types_arr']); // Ancuta 23.08.2018- Changed from HIGHEST to ALL types [comment from 22.08.2018]
                        }
                        
                        if (Pms_CommonData::isintersected ( strtotime ( $periods[0]['start'] ), strtotime ( $periods[0]['end'] ), strtotime ( $sv_period['start'] ), strtotime (  $sv_period['end'] ) )) {
                            $sapv_in_report_period[$sapv_ipid][] = $sapv_id; 
                        }
                        
                        $sapv_data_array[$sapv_ipid][] = $sapv_id;
                        
                        $patient_sapv_array[$sapv_ipid][$sapv_id]['KEY'] = $kd;
                        $kd++;
                        
                    }
                        
    				foreach($admission_periods[$sapv_ipid] as $admk=>$adm_data)
    				{
                        foreach($sapv_data as $sapv_id => $sv_period){
                            if (Pms_CommonData::isintersected ( strtotime ( $adm_data ['start'] ), strtotime ( $adm_data ['end'] ), strtotime ( $sv_period['start'] ), strtotime (  $sv_period['end'] ) )) {
                                $sapv_in_admission_period[$sapv_ipid][$adm_data ['start'].' - '.$adm_data ['end'] ][] = $sapv_id; 
                            }
                        }
    				} 
    
				}
	 
				
				// CREATE PATIENTS PERIODS 
				// period starts at admission
				// periods ends at discharge
				// period ends at admission in hospital if hospital day is at least one day 
				// period starts when patient is moved from hospital cu other location
				// period ends if sapv is changed
			
				foreach($ipids as $ipid){
				    // 1:: IF no SAPV remove patient
				    if(empty($patient_days[$ipid]['overall_sapv_days'])){
				        //unset($ipids[$ipid]);
				    }
				}
				
				$sapv_valid_array_dates = array();
				$overall_active_sapv_days= array();
				$sapv_details_per_day= array();

				$export_periods = array();
				$prs = array();
				foreach($ipids as $ipid){
				
				    // 2::  remove hospital days where the hospital stay is only one day, hospital_adm==hospital_disc  : days from locations not client settings! 
				    //$patient_days[$pipid]['overall_hospital_admdis']
				    if(!empty($patient_days[$ipid]['overall_hospital_days'])){
    				    if(!empty($patient_days[$ipid]['overall_hospital_admdis'])){
        				    $patient_days[$ipid]['overall_hospital_days'] = array_values(array_diff($patient_days[$ipid]['overall_hospital_days'],$patient_days[$ipid]['overall_hospital_admdis']));
    				    }
				    }

				    
				    // 3:: get all active days with SAPV    
				    $overall_active_sapv_days[$ipid] = array_values(array_intersect($patient_days[$ipid]['overall_active'],$patient_days[$ipid]['overall_sapv_days']));
                    
				    // 4:: REMOVE hospital days from active days with SAPV     
				    if(!empty( $patient_days[$ipid]['overall_FULL_hospital_days'])){
    				    $overall_active_sapv_days[$ipid] = array_values(array_diff($overall_active_sapv_days[$ipid], $patient_days[$ipid]['overall_FULL_hospital_days']));
				    }
    	
				}
// 				0b6cfbe94606f0c3de5f58d93438380a1063bb88
// 				dd($overall_active_sapv_days);
				
			    // 5:: Create valid days
				foreach($ipids as $ipid){
				    foreach($overall_active_sapv_days[$ipid] as $key=>$s_day){
				        $sapv_details_per_day[$ipid][$s_day]['day'] = $s_day;
				        $sapv_details_per_day[$ipid][$s_day]['type'] = $patient_days[$ipid]['sapv_days'][$s_day];
				    
				        if(in_array($s_day, $patients_overall_arr[$ipid]['admission_days'])){
				            $sapv_details_per_day[$ipid][$s_day]['app_adm'] = "1";
				        }else{
				            $sapv_details_per_day[$ipid][$s_day]['app_adm'] = "0";
				        }
				    
				        if(in_array($s_day, array_values($patients_overall_arr[$ipid]['discharge']))){
				            $sapv_details_per_day[$ipid][$s_day]['app_dis'] = "1";
				            $sapv_details_per_day[$ipid][$s_day]['discharge_reason'] = $patients_discharge_data_details[$ipid][$s_day]['discharge_method_str'];
				        }else{
				            $sapv_details_per_day[$ipid][$s_day]['app_dis'] = "0";
				        }
				    
				        if(in_array($s_day, $patient_days[$ipid]['overall_hospital_admissions'])){
				            $sapv_details_per_day[$ipid][$s_day]['hosp_adm'] = "1";
				        }else{
				            $sapv_details_per_day[$ipid][$s_day]['hosp_adm'] = "0";
				        }
				    
				        if(in_array($s_day, $patient_days[$ipid]['overall_hospital_discharge'])){
				            $sapv_details_per_day[$ipid][$s_day]['hosp_dis'] = "1";
				        }else{
				            $sapv_details_per_day[$ipid][$s_day]['hosp_dis'] = "0";
				        }
				    }
				    
				    $sapv_valid_array_dates[$ipid] = array_values($sapv_details_per_day[$ipid]);
				}
				
				
				foreach($ipids as $ipid){
				    $sident = 0;
				    foreach($sapv_valid_array_dates[$ipid]  as $sk=>$sdata){
				        $prs[$ipid][$sident]['days'][]=$sdata['day'];
				        $prs[$ipid][$sident]['type'] = $sdata['type'];

				        $gap = 0 ;
				        if($sapv_valid_array_dates[$ipid][$sk+1]['day']){
				            
				            $date1 = new DateTime($sdata['day']);
				            $date2 = new DateTime($sapv_valid_array_dates[$ipid][$sk+1]['day']);
				            $gap =  date_diff($date1, $date2)->days;
				        }
				        
				        $prs[$ipid][$sident]['gap'] = $gap;
				        if(   
				            $sdata['type'] != $sapv_valid_array_dates[$ipid][$sk+1]['type'] 
				            || ($sdata['hosp_adm'] == "1" && $sdata['hosp_dis']=="0")
				            || $sdata['app_dis'] == "1"
				            || $gap > 1
				            ){
				            
				            if($sdata['type'] != $sapv_valid_array_dates[$ipid][$sk+1]['type']){
				                $prs[$ipid][$sident]['discharge_reason'] = "Verordnungswechsel";
				            }
				    
				            if($sdata['hosp_adm'] == "1" && $sdata['hosp_dis']=="0"){
				                $prs[$ipid][$sident]['discharge_reason'] = "Krankenhausaufenthalt";
				            }
				    
				            if($sdata['app_dis'] == "1"){
				                $prs[$ipid][$sident]['discharge_reason'] = $sdata['discharge_reason'];
				            }
				    
				            $sident++;
				        }
				    
				    }
				}
 
			 
				foreach($ipids as $ipid){
				    if(!empty($prs[$ipid]) && !empty($patient_days[$ipid]['overall_sapv_days'])){
				        foreach($prs[$ipid] as $k=>$period_details){
				            $export_periods[$ipid][$k]['start'] = $period_details['days'][0];
				            $export_periods[$ipid][$k]['end'] = end($period_details['days']);
				            $export_periods[$ipid][$k]['discharge_reason'] = $period_details['discharge_reason'];
				            $export_periods[$ipid][$k]['days'] = $period_details['days'];
				            
				            $export_periods[$ipid][$k]['BLOCK_ID'] = $block_export_id[$ipid].$k; /// !!!!!!!!!!!!!!!!!
				        }
				    }
				}
			 
				// tie the export period to patient admission period / readmision id
				foreach($ipids as $ipid)
				{
				    foreach($admission_periods_overall[$ipid] as $ka=>$admp)
				    {
    				    foreach($export_periods[$ipid] as $ke=>$exp)
    				    {
    				        if (Pms_CommonData::isintersected ( strtotime ( $admp ['start'] ), strtotime ( $admp ['end'] ), strtotime ( $exp['start'] ), strtotime (  $exp['end'] ) )) {
    				            $export_periods[$ipid][$ke]['patient_readmission_adm_ID'] = $admp['patient_readmission_adm_ID'];
    				            $export_periods[$ipid][$ke]['patient_readmission_dis_ID'] = $admp['patient_readmission_dis_ID'];
    				        }
    				    }
				    }
				}
				
				
				$patientday2blockid= array();
				foreach($ipids as $ipid)
				{
				    foreach($export_periods[$ipid] as $k=>$exdata)
				    {
				        foreach($exdata['days'] as $k=>$day_date)
				        {
				            $patientday2blockid[$ipid][$day_date] = $exdata['BLOCK_ID']; 
				        }
				    }
				}
				
				
				foreach($patient_sapv_array as $sapv_ipid => $sapv_data)
				{
				    foreach($export_periods[$sapv_ipid] as $admk=>$adm_data)
				    {
				        foreach($sapv_data as $sapv_id => $sv_period){
				            if (Pms_CommonData::isintersected ( strtotime ( $adm_data ['start'] ), strtotime ( $adm_data ['end'] ), strtotime ( $sv_period['start'] ), strtotime (  $sv_period['end'] ) )) {
				                $sapv_in_export_period[$sapv_ipid][$adm_data['BLOCK_ID']][] = $sapv_id;
				            }
				        }
				    }
				}
				
				
			
				
				
				// ACP
				$acp = new PatientAcp();
				$acp_data_patients = $acp->getByIpid($ipids);
				
				if(!empty($acp_data_patients))
				{
				    foreach($acp_data_patients as $ipid=>$acp_data)
				    {
				        foreach($acp_data as $k=>$block)
				        {
				            if($block['division_tab'] == "living_will"){
				
				                if($block['active'] == "yes"){
				                    $patient_details[$ipid]['living_will'] = 'Ja';
				                } else{
				                    $patient_details[$ipid]['living_will'] = 'Nein';
				                }
				
				            }
				            elseif($block['division_tab'] == "healthcare_proxy")
				            {
				                if($block['active'] == "yes"){
				                    $patient_details[$ipid]['healthcare_proxy'] = 'Ja';
				                } else{
				                    $patient_details[$ipid]['healthcare_proxy'] = 'Nein';
				                }
				
				            }
				            elseif($block['division_tab'] == "care_orders")
				            {
				                if($block['active'] == "yes"){
				                    $patient_details[$ipid]['care_orders'] = 'Ja';
				                } else{
				                    $patient_details[$ipid]['care_orders'] = 'Nein';
				                }
				
				            }
				        }
				    }
				}
				
				
				// Hausarzt, Facharzt, Pflegedienst, Sanitätsshaus, Apotheke, Physiotherapeut, sonstige Versorger
                $patients['caregiver'] = array();
                    
                    // get familydoctor details
                $client_familydoctor_array = FamilyDoctor::get_family_doctors_multiple($patient_familydoc_id);
                foreach ($client_familydoctor_array as $fam_id => $fdetails) {
                    $fam_doc_details[$fdetails['id']]['nice_name'] = trim($fdetails['title']) != "" ? trim($fdetails['title']) . " " : "";
                    $fam_doc_details[$fdetails['id']]['nice_name'] .= trim($fdetails['last_name']);
                    $fam_doc_details[$fdetails['id']]['nice_name'] .= trim($fdetails['first_name']) != "" ? (", " . trim($fdetails['first_name'])) : "";
                }
                
                foreach ($ipids as $ipid) {
                    $patient_details[$ipid]['caregiver']['family_doctor'][] = $fam_doc_details[$patient_details[$ipid]['familydoc_id']]['nice_name'];
                    if (strlen($fam_doc_details[$patient_details[$ipid]['familydoc_id']]['nice_name']) > 0) {
                        $patient_details[$ipid]['caregiver_str'] .= $fam_doc_details[$patient_details[$ipid]['familydoc_id']]['nice_name'] . ', ';
                    }
                }
                
                // Facharzt
                $patients['caregiver']['specialists'] = PatientSpecialists::get_patient_specialists($ipids, true);
                // dd($patient_specialists_array);
                foreach ($patients['caregiver']['specialists'] as $caregiver_id => $cg_data) {
                    $patient_details[$cg_data['ipid']]['caregiver']['specialists'][] = $cg_data['master']['practice'];
                    if (strlen($cg_data['master']['practice']) > 0) {
                        $patient_details[$cg_data['ipid']]['caregiver_str'] .= $cg_data['master']['practice'] . ', ';
                    }
                }
                
                // Pflegedienst
                $patients['caregiver']['pflegedienst'] = PatientPflegedienste::get_multiple_patient_pflegedienste($ipids);
                // dd($patients['caregiver']['pflegedienst']);
                foreach ($patients['caregiver']['pflegedienst']['results'] as $cr_ipid => $cg_data_arr) {
                    foreach ($cg_data_arr as $k => $cg_data) {
                        $patient_details[$cg_data['ipid']]['caregiver']['pflegedienst'][] = $cg_data['nursing'];
                        if (strlen($cg_data['nursing']) > 0) {
                            $patient_details[$cg_data['ipid']]['caregiver_str'] .= $cg_data['nursing'] . ', ';
                        }
                    }
                }
                
                // Sanitatsshaus
                $patients['caregiver']['supplies'] = PatientSupplies::get_patient_supplies($ipids, true);
                // dd($patients['caregiver']['supplies']);
                foreach ($patients['caregiver']['supplies'] as $caregiver_id => $cg_data) {
                    $patient_details[$cg_data['ipid']]['caregiver']['supplies'][] = $cg_data['master']['supplier'];
                    if (strlen($cg_data['master']['supplier']) > 0) {
                        $patient_details[$cg_data['ipid']]['caregiver_str'] .= $cg_data['master']['supplier'] . ', ';
                    }
                }
                // get Apotheke,
                $patients['caregiver']['pharmacy'] = PatientPharmacy::get_patients_pharmacy($ipids);
                // dd($patients['caregiver']['pharmacy']);
                foreach ($patients['caregiver']['pharmacy'] as $ph_ipid => $ph_data_array) {
                    foreach ($ph_data_array as $k => $ph_data) {
                        $patient_details[$ph_ipid]['caregiver']['pharmacy'][] = $ph_data['pharmacy'];
                        
                        if (strlen($ph_data['pharmacy']) > 0) {
                            $patient_details[$ph_ipid]['caregiver_str'] .= $ph_data['pharmacy'] . ', ';
                        }
                    }
                }
                
                // Physiotherapeut
                $patients['caregiver']['physiotherapeut'] = PatientPhysiotherapist::get_patient_physiotherapists($ipids, true);
                // dd($patients['caregiver']['physiotherapeut']);
                foreach ($patients['caregiver']['physiotherapeut'] as $caregiver_id => $cg_data) {
                    $patient_details[$cg_data['ipid']]['caregiver']['physiotherapeut'][] = $cg_data['master']['physiotherapist'];
                    
                    if (strlen($cg_data['master']['physiotherapist']) > 0) {
                        $patient_details[$cg_data['ipid']]['caregiver_str'] .= $cg_data['master']['physiotherapist'] . ', ';
                    }
                }
                
                // sonstige Versorger
                $patients['caregiver']['suppliers'] = PatientSuppliers::get_patients_suppliers($ipids);
                foreach ($patients['caregiver']['suppliers'] as $sph_ipid => $sh_data_array) {
                    foreach ($sh_data_array as $k => $sph_data) {
                        $patient_details[$sph_ipid]['caregiver']['supplier'][] = $sph_data['supplier'];
                        
                        if (strlen($sph_data['supplier']) > 0) {
                            $patient_details[$sph_ipid]['caregiver_str'] .= $sph_data['supplier'] . ', ';
                        }
                    }
                }
                
                // Location
                $patient_location_obj = new PatientLocation();
                $patients['caregiver']['locations'] = $patient_location_obj->get_valid_patients_locations($ipids, true);
//                 dd($patients['caregiver']['locations']);
                foreach ($patients['caregiver']['locations'] as $locipid => $loc_data_array) {
                    $locid = 0;
                    foreach ($loc_data_array as $k => $loc_data) {
                        $locid = substr($loc_data['location_id'], 0, 4);
                        
                        $cnt_id = "";
                        if ($locid == "8888") {
                            $cnt_id = substr($loc_data['location_id'], 4);
                            $patients['caregiver']['locations'][$locipid][$k]['master_location_name'] = 'bei Kontaktperson ' . $cnt_id . ' ' . $cps_details_array[$loc_data['ipid']][$cnt_id - 1]['cnt_last_name'] . ' ' . $cps_details_array[$loc_data['ipid']][$cnt_id - 1]['cnt_first_name'];
                        }
                        
                        if (strlen($patients['caregiver']['locations'][$locipid][$k]['master_location_name'])) {
                            $patient_details[$loc_data['ipid']]['locations'][$k] = $patients['caregiver']['locations'][$locipid][$k]['master_location_name'];
                        }
                    }
                }
                
                //locations overall
                //$loc_type_name = array("0"=>"","1"=>"Krankenhaus","3-4"=>'Heim',"5"=>"Zu Hause");
                
                $loc_type_name = Locations:: getLocationTypes(); // NEW Array
                $loc_type_name["3"] = "Pflegeheim / Altenheim"; // overwrite master array
                $loc_type_name["4"] = "Pflegeheim / Altenheim"; // overwrite master array
                $loc_type_name["3-4"] = "Pflegeheim / Altenheim";
                
                $location_rank = array(
                    '5', //zu Hause
                    '3','4',//Pflegeheim / Altenheim
                    '0','2','6','7','8','9',//any other
                    '1'//Krankenhaus
                );

                if($_REQUEST['locatios'] == "1"){
                    echo "<pre/>";
                    print_r($patient_days2locationtypes);
                    exit;
                }
                
                
                $include_all_types = 1;
                
                if($_REQUEST['old_types'] == "1"){
                    // this is added for testing only
                    $include_all_types = 0;
                }
                $first_location_admission = array();
                $last_location_admission = array();
                
                $admission_period_locations  = array();
                $admp_discharge_locations  = array();
                
                foreach($ipids as $ipid){
                    foreach($admission_periods[$ipid] as $k=>$adm_data)
                    {
                        if( ! empty($patient_days2locationtypes[$ipid][$adm_data['start']]))
                        {
                            if( $include_all_types == "1" ) 
                            {
                                if( count($patient_days2locationtypes[$ipid][$adm_data['start']]) == 1 && in_array("1",$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                {
                                    $admission_period_locations[$ipid]['at_admission'][$k]  = $loc_type_name['1']; // this means hospital can only be exported if there are NO multiple locations.
                                } 
                                else 
                                {
                                    foreach($location_rank as $loc_key =>$loc_type)
                                    {
                                        if( empty($admission_period_locations[$ipid]['at_admission'][$k]) && in_array($loc_type,$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                        {
                                           $admission_period_locations[$ipid]['at_admission'][$k]  = $loc_type_name[$loc_type]; 
                                        } 
                                    }
                                }
                            }
                            else
                            { // use old method - not all types included
                                if( in_array("5",$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                {
                                    $first_location_admission[$ipid] = $loc_type_name['5'];
                                }
                                elseif( in_array("3",$patient_days2locationtypes[$ipid][$adm_data['start']])  ||  in_array("4",$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                {
                                    $first_location_admission[$ipid] = $loc_type_name['3-4'];
                                }
                                elseif( in_array("1",$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                {
                                    $first_location_admission[$ipid] = $loc_type_name['1'];
                                }
                                else
                                {
                                    $first_location_admission[$ipid] = $loc_type_name['0'];
                                }
                            }
                            
                        }
                        
                        if(!empty($patient_days2locationtypes[$ipid][$adm_data['end']]))
                        {
                            if( $include_all_types == "1" )
                            {
                                if( count($patient_days2locationtypes[$ipid][$adm_data['end']]) == 1 && in_array("1",$patient_days2locationtypes[$ipid][$adm_data['end']]))
                                {
                                    $admission_period_locations[$ipid]['at_discharge'][$k] = $loc_type_name['1']; // this means hospital can only be exported if there are NO multiple locations.
                                } 
                                else 
                                {
                                    foreach($location_rank as $loc_key =>$loc_type)
                                    {
                                        if( empty( $admission_period_locations[$ipid]['at_discharge'][$k] ) && in_array($loc_type,$patient_days2locationtypes[$ipid][$adm_data['end']]))
                                        {
                                            $admission_period_locations[$ipid]['at_discharge'][$k] = $loc_type_name[$loc_type];
                                        }
                                    }
                                }
                            } 
                            else
                            {   // use old method - not all types included
                                if(in_array("5",$patient_days2locationtypes[$ipid][$adm_data['end']]))
                                {
                                    $last_location_admission[$ipid] = $loc_type_name['5'];
                                }
                                elseif(in_array("3",$patient_days2locationtypes[$ipid][$adm_data['end']]) || in_array("4",$patient_days2locationtypes[$ipid][$adm_data['end']]))
                                {
                                    $last_location_admission[$ipid] = $loc_type_name['3-4'];
                                }
                                elseif(in_array("1",$patient_days2locationtypes[$ipid][$adm_data['end']]))
                                {
                                    $last_location_admission[$ipid] = $loc_type_name['1'];
                                }
                                else
                                {
                                    $last_location_admission[$ipid] = $loc_type_name['0'];
                                }                                
                            }                             
                        }
                        
                    }
                }
                
                
//                 dd($admission_period_locations);
                if( $include_all_types == "1" )
                {
                    $first_location_admission = array();
                    $last_location_admission = array();
                    
                    foreach($ipids as $ipid)
                    {
                        $first_location_admission[$ipid] = reset($admission_period_locations[$ipid]['at_admission']);
                        $last_location_admission[$ipid] = end($admission_period_locations[$ipid]['at_discharge']);
                    }
                }
                
// dd($admp_admission_locations,$admp_discharge_locations,$admission_periods,$last_location_admission);
                
                $location_admission = array();
                $location_discharge = array();
                foreach($ipids as $ipid)
                {
                    foreach($export_periods[$ipid] as $k=>$adm_data)
                    {
                        
                        if(!empty($patient_days2locationtypes[$ipid][$adm_data['start']]))
                        {
                            if( $include_all_types == "1" )
                            {
                                if( count($patient_days2locationtypes[$ipid][$adm_data['start']]) == 1 && in_array("1",$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                {
                                    $location_admission[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['1']; // this means hospital can only be exported if there are NO multiple locations.
                                } 
                                else 
                                {
                                    foreach($location_rank as $loc_key =>$loc_type)
                                    {
                                        if( empty($location_admission[$ipid][$adm_data['BLOCK_ID']]) && in_array($loc_type,$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                        {
                                            $location_admission[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name[$loc_type];
                                        }
                                    }
                                }
                            } 
                            else
                            { 
                                if(in_array("5",$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                {
                                    $location_admission[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['5']; 
                                } 
                                elseif(in_array("3",$patient_days2locationtypes[$ipid][$adm_data['start']]) || in_array("4",$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                {
                                    $location_admission[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['3-4']; 
                                } 
                                elseif(in_array("1",$patient_days2locationtypes[$ipid][$adm_data['start']]))
                                {
                                    $location_admission[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['1']; 
                                }
                                else
                                {
                                    $location_admission[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['0']; 
                                } 
                            }
                        }
                        
                        
                        if( ! empty($patient_days2locationtypes[$ipid][$adm_data['end']]))
                        {
                            if( $include_all_types == "1" )
                            {
                                if( count($patient_days2locationtypes[$ipid][$adm_data['end']]) == 1 && in_array("1",$patient_days2locationtypes[$ipid][$adm_data['end']]))
                                {
                                    $location_discharge[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['1']; // this means hospital can only be exported if there are NO multiple locations.
                                }
                                else
                                {
                                    foreach($location_rank as $loc_key =>$loc_type)
                                    {
                                
                                        if( empty($location_discharge[$ipid][$adm_data['BLOCK_ID']]) && in_array($loc_type,$patient_days2locationtypes[$ipid][$adm_data['end']]))
                                        {
                                            $location_discharge[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name[$loc_type];
                                        }
                                    }
                                }
                            } 
                            else
                            { 
                                if( in_array ("5", $patient_days2locationtypes[$ipid][$adm_data['end']]))
                                {
                                    $location_discharge[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['5']; 
                                } 
                                elseif ( in_array("3", $patient_days2locationtypes[$ipid][$adm_data['end']]) || in_array("4",$patient_days2locationtypes[$ipid][$adm_data['end']]))
                                {
                                    $location_discharge[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['3-4'];
                                } 
                                elseif ( in_array("1", $patient_days2locationtypes[$ipid][$adm_data['end']]))
                                {
                                    $location_discharge[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['1']; 
                                }
                                else
                                {
                                    $location_discharge[$ipid][$adm_data['BLOCK_ID']] = $loc_type_name['0']; 
                                }
                            }
                        }
                    }
                }
                
                
                
//                 dd($location_admission,$location_discharge);
//                 dd($location_admission);
//                 dd($first_location_admission,$last_location_admission);
                
                
                // PFLEGEGRAD
                $pms_res = Doctrine_Query::create()
                ->select("*")
                ->from('PatientMaintainanceStage')
                ->whereIn('ipid',$ipids)
                ->orderBy('fromdate, create_date asc')
                ->fetchArray();
                
//                 $patient_days[$pipid]['overall_active']
//                 dd($patient_days["4417515e91815cb88a4095cebe670009df261d5b"]['overall_active']);
//                 dd($patient_days);
//                 dd($pms_res);
                $pflegegrade_array = array();
                foreach($pms_res as $k => $pms_data){
                    
                    if($pms_data['tilldate'] == "0000-00-00"){
                       $pms_data['tilldate'] =  date('Y-m-d',strtotime($patient_days[$pms_data['ipid']]['last_active_date']));
                    }
                    
                    if(
                        strlen($pms_data['stage']) > 0
                        && Pms_CommonData::isintersected ( strtotime ( $patient_days[$pms_data['ipid']]['first_active_date']   ), strtotime ($patient_days[$pms_data['ipid']]['last_active_date'] ), strtotime ( $pms_data['fromdate'] ), strtotime (  $pms_data['tilldate'] ) )
                        ){
                        
                        if(strtotime(date($pms_data['create_date'])) < strtotime("2017-01-11")  &&  $pms_data['tilldate'] <= "2017-01-01" &&  $pms_data['fromdate'] <= "2017-01-01"){
                            $pms_data['stage'] = 'Stufe '.$pms_data['stage'] ;
                        } else{
                            $pms_data['stage'] = $pms_data['stage'] ;
                        }
                        $pflegegrade_array[$pms_data['ipid']][] = $pms_data;
                    }
                }
// dd($pflegegrade_array);
// dd($pflegegrade_array_extra);

                $pflegedrade_array2period = array();
                foreach($ipids as $ipid){
                    foreach($export_periods[$ipid] as $kdds=>$adm_data){
                        foreach($pflegegrade_array[$ipid] as $kpfl=>$pfl ){
                            
                            if($pfl['tilldate'] == "0000-00-00"){
                                $pfl['tilldate'] =  date('Y-m-d',strtotime($patient_days[$pfl['ipid']]['last_active_date']));
                            }
                            
                            if (Pms_CommonData::isintersected ( strtotime ( $adm_data ['start'] ), strtotime ( $adm_data ['end'] ), strtotime ( $pfl['fromdate'] ), strtotime (  $pfl['tilldate'] ) )) {
                                $pflegedrade_array2period[$ipid][$adm_data['BLOCK_ID']][] = $pfl['stage'];
                            }
                        }
                        
                    }
                }
                // ECOG
 
                /* ----------------- Patient Details - Deleted visits ----------------------------------------- */

                // Commented by Ancuta 20.03.2019
                // For this we use -  function  PatientCourse get_deleted_visits_multiple_patients 
                /*
                $deleted_visits = Doctrine_Query::create()
                ->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
                ->from('PatientCourse')
                ->whereIn("ipid",$ipids)
                ->andWhere('wrong=1')
                ->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
                ->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('visit_koordination_form')) . "'" . ' OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("kvno_doctor_form")) . '" OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("kvno_nurse_form")) . '"  OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("wl_doctor_form")) . '"  OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("wl_nurse_form")) . '"   OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("bayern_doctorvisit")) . '"   OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("contact_form")) . '"  ');
                $deleted_visits_array = $deleted_visits->fetchArray();
                 
                $del_visits =array();
                foreach($deleted_visits_array as $k_del_visit => $v_del_visit)
                {
                    $del_visits[$v_del_visit['tabname']][] = $v_del_visit['recordid'];
                }
                */
                
                // Get deleted visits for all patients 
                $pc = new PatientCourse();
                $del = $pc ->get_deleted_visits_multiple_patients($ipids);
                $del_visits = array();
                foreach($del as $pipid => $tabnames){
                    foreach($tabnames as $tabname => $varr){
                        foreach($varr as $k=> $vid){
                            $del_visits[$tabname][] = $vid;
                            
                            if(in_array($tabname,array('kvno_doctor_form','sakvno_doctor_form','wl_doctor_form'))){
                                $del_visits['old_doctor_visits'][] = $vid;
                            }
                            if(in_array($tabname,array('kvno_nurse_form','sakvno_nurse_form','wl_nurse_form','lvn_nurse_form'))){
                                $del_visits['old_nurse_visits'][] = $vid;
                            }
                        }
                    }
                }
                
                
                $kvnodoc = Doctrine_Query::create()
                ->select("*,
                    IF(kvno_ecog != '', kvno_ecog, NULL ) as ecog, 
                    start_date as date, 
                    NULL as karnofsky
                    ")
                ->from('KvnoDoctor')
                ->whereIn("ipid", $ipids)
                ->andWhere('isdelete = "0"');
                if(!empty($del_visits['kvno_doctor_form'])){
                    $kvnodoc->andWhereNotIn('id',$del_visits['kvno_doctor_form']);
                }
                $kvnodoc ->orderBy('start_date ASC');
                $kvnodocarray = $kvnodoc->fetchArray();
                
                foreach($kvnodocarray as $k=>$vdata){
                    $visits[$vdata['ipid']]['kd'.$vdata['id']] = $vdata;
                }
                
                
                $bay_q = Doctrine_Query::create()
                ->select("*,
                    IF(ecog != '', ecog, NULL) as ecog,
                    start_date as date, 
                    NULL as karnofsky
                    ")
                ->from('BayernDoctorVisit')
                ->whereIn("ipid",$ipids);
                if( !empty($del_visits['bayern_doctorvisit'])){
                    $bay_q ->andWhereNotIn('id',$del_visits['bayern_doctorvisit']);
                }
                $bay_q ->orderBy('start_date ASC');
                $bay_arr = $bay_q->fetchArray();
                
                foreach($bay_arr as $k=>$vdata){
                    $visits[$vdata['ipid']]['bay'.$vdata['id']] = $vdata;
                }
                
                $cf_q = Doctrine_Query::create()
                ->select("*,
                    IF(ecog != '', ecog, NULL) as ecog, 
                    IF(karnofsky != '', karnofsky, NULL) as karnofsky 
                    ")
                ->from('ContactForms')
                ->whereIn("ipid",$ipids)
                ->andWhere('isdelete = "0"');
                if( !empty($del_visits['contact_form'])){
                    $cf_q->andWhereNotIn('id',$del_visits['contact_form']);
                }
                $cf_q->orderBy('start_date ASC');
                $cf_arr = $cf_q->fetchArray();
                
                foreach($cf_arr as $k=>$vdata){
                    $visits[$vdata['ipid']]['cf'.$vdata['id']] = $vdata;
                }
                
                $ecog_karnofsky_mapp = array(                                     
                    "0"=> "100",
                    "1"=> "80",
                    "2"=> "60",
                    "3"=> "40",
                    "4"=> "20",
                    "5"=> "0"
                );
                // TODO-2179 
                // Ancuta on 11.03.2019
                // New mapping
                $old_ecog2new = array(
                    "1"=> "0",
                    "2"=> "1",
                    "3"=> "2",
                    "4"=> "3",
                    "5"=> "4",
                );
                
                $ecog_karnofsky_mapp = array();
                $ecog_karnofsky_mapp = array(                                     
                    "0"=> "100",
                    "1"=> "80",
                    "2"=> "60",
                    "3"=> "40",
                    "4"=> "20",
                );
                
//                 dd($visits);
                //2cbb2dcb31dbfe930395c25354f02e525902ed65
                $ecog_array = array();
                $new_k = array();
                $karnofsky_array = array();
                $all_karnofsky = array();
                $dbg_kar = array();
                foreach($ipids as $ipid){
                    foreach( $visits[$ipid] as $kv=>$values ){
                        if(!empty($values['karnofsky'])){
                            
                            $new_k[$ipid][$kv]["date"] = $values['date'];
                            $new_k[$ipid][$kv]["datedmy"] =date('d.m.Y',strtotime($values['date']));
                            $new_k[$ipid][$kv]["val"] = $values['karnofsky'];
                            
                        } elseif(!empty($values['ecog'])){
                            
                            $new_k[$ipid][$kv]["date"] =$values['date'];
                            $new_k[$ipid][$kv]["datedmy"] =date('d.m.Y',strtotime($values['date']));
							//$new_k[$ipid][$kv]["val"] = $ecog_karnofsky_mapp[$old_ecog2new[$values['ecog']]].' - '.$old_ecog2new[$values['ecog']].' - '.$values['ecog'];
                            $new_k[$ipid][$kv]["val"] = $ecog_karnofsky_mapp[$old_ecog2new[$values['ecog']]]; 
                            
                        }
                    }
                    
                    $dbg_kar[$ipid] = $new_k[$ipid];
                    usort($new_k[$ipid], array(new Pms_Sorter('date'), "_date_compare"));
                    $all_karnofsky[$ipid] = $new_k[$ipid];
                }
                
                /* if($logininfo->userid == "338"){
                    echo "<pre>";
                    print_r($visits);
                    print_r($all_karnofsky);
                    exit;
                } */
                // PER BLOCK 
                $karnofsky_array2period = array();
                foreach($ipids as $ipid){
                    foreach($export_periods[$ipid] as $kdds=>$adm_data){
                        foreach($all_karnofsky[$ipid] as $kk=>$kfdata ){
                            if (Pms_CommonData::isintersected ( strtotime ( $adm_data ['start'] ), strtotime ( $adm_data ['end'] ), strtotime ( $kfdata['datedmy'] ), strtotime (  $kfdata['datedmy'] ) )) {
                                $karnofsky_array2period[$ipid][$adm_data['BLOCK_ID']][] = $kfdata;
                            }
                        }
                    }
                }
                
                // PER POSTED PERIOD 
                $karnofsky_array2postperiod = array();
                foreach($ipids as $ipid){
                   foreach($all_karnofsky[$ipid] as $kk=>$kfdata ){
                       if (Pms_CommonData::isintersected ( strtotime ( $periods[0]['start'] ), strtotime ( $periods[0]['end'] ), strtotime ( $kfdata['datedmy'] ), strtotime (  $kfdata['datedmy'] ) )) {
                            $karnofsky_array2postperiod[$ipid][] = $kfdata;
                       }
                   }
                }
 
                
				// create all CSV files that need to be exported -  export a zip
				$allcsvs = array();

				//
				//FILE: 1.Basisdaten
				//
				$csv1 = array(); // pateitn details 
				$allcsvs[1][0] = $csv1[0] = array(
				'Source',//  (hardcode with "ISPC")
				'Team_ID',//  (like before an ISPC generated Team ID which does not allow to find out which team this is
				'Pat_ID',//  patient ID again with replacing the client part with a ID which does not let others find out which team this is
				'Export_Date',//  date of export
				'Geschlecht',//  Gender (Männlich, weiblich, unbestimmt)
				'Geburtsjahr',//  YEAR of birth (4 digit)
				'Bevölkerungsdichte',//  like before
				'Angehörige / Vertrauensperson',//  (ja, nein)
				'Patientenverfügung',//  (ja, nein)
				'Vollmacht',//  (ja, nein)
				'Betreuungsurkunde',//  (ja, nein)
				'Pflegegrad (Aufnahme)',//  Pflegegrad on first admission
				'Pflegegrad (Entlassung)',//  pflegegrad on last discharge
				'Aufenthaltsort (Aufnahme)',//  first location TYPE(ATTENTION SEE BELOW!!)
				'Aufenthaltsort (Entlassung)',//  last location TYPE
				'Karnofsky Index (erster Wert)',//- first Karnofsky
				'Karnofsky Index Datum (erster Wert)',//  first Karnofsky DATE
				'Karnofsky Index (letzter Wert)',//  last Karnofsky
				'Karnofsky Index Datum (letzter Wert)',//  last Karnofsky DATE
				'Versorgungstage mit aktiver SAPV',//  days of SAPV (active and SAPV) in report period
				'Sterbeort',//  LOCATION of dying
				);
				
				
				
				$karnofsky_arr =array();
				$overal_sapv_days =array();
				
                foreach ($ipids as $ipid) {
                    
                    foreach($export_periods[$ipid] as $bl_k=>$block_value){
                        foreach($block_value['days'] as $bk=>$b_day)
                        {
                            if(!in_array($b_day,$overal_sapv_days[$ipid])){
        				        $overal_sapv_days[$ipid][] = $b_day;
                            }
				        }
                    }
                    $patient_details[$ipid]['first_pflegegrade'] = "";
                    if(!empty($pflegegrade_array[$ipid])){
                        $patient_details[$ipid]['first_pflegegrade'] = $pflegegrade_array[$ipid][0]['stage'];

                        $last_pflegegrade[$ipid] =  end($pflegegrade_array[$ipid]);  
                        $patient_details[$ipid]['last_pflegegrade'] = $last_pflegegrade[$ipid]['stage'];
                        
                    }
                    
                    // They insist that in Basis the FIRST EVER and the LAST EVER Karnofsky shall be listed. no matter if in report period or not
                    // Ancuta on 23.08.2018, changed from $karnofsky_array2postperiod to $all_karnofsky (comment from 22.08.2018)
                    if(!empty($all_karnofsky[$ipid])){
                        $karnofsky_arr[$ipid]['first_val']= $all_karnofsky[$ipid][0]['val'];//'Karnofsky Index (erster Wert)',//- first Karnofsky
                        $karnofsky_arr[$ipid]['first_date']= $all_karnofsky[$ipid][0]['datedmy'];//'Karnofsky Index Datum (erster Wert)',//  first Karnofsky DATE
                        
                        $karnofsky_arr[$ipid]['last_val']="";//'Karnofsky Index (letzter Wert)',//  last Karnofsky
                        $karnofsky_arr[$ipid]['last_date']="";//'Karnofsky Index Datum (letzter Wert)',//  last Karnofsky DATE
                        
//                         if(count($all_karnofsky[$ipid]) > 1 ){
                            $last_karnofsky_array2postperiod[$ipid] = end($all_karnofsky[$ipid]);
                            
                            $karnofsky_arr[$ipid]['last_val'] = $last_karnofsky_array2postperiod[$ipid]['val'] ;//'Karnofsky Index (letzter Wert)',//  last Karnofsky
                            $karnofsky_arr[$ipid]['last_date']=$last_karnofsky_array2postperiod[$ipid]['datedmy'];//'Karnofsky Index Datum (letzter Wert)',//  last Karnofsky DATE
//                         }
                        
                    } else{
                        $karnofsky_arr[$ipid]['first_val']="";//'Karnofsky Index (erster Wert)',//- first Karnofsky
                        $karnofsky_arr[$ipid]['first_date']="";//'Karnofsky Index Datum (erster Wert)',//  first Karnofsky DATE
                        $karnofsky_arr[$ipid]['last_val']="";//'Karnofsky Index (letzter Wert)',//  last Karnofsky
                        $karnofsky_arr[$ipid]['first_date']="";//'Karnofsky Index Datum (letzter Wert)',//  last Karnofsky DATE
                        
                    }
                    
                    
                    $allcsvs[1][] = $csv1[] = array(
                        $external_source, // 'Source',//  (hardcode with "ISPC")
                        $external_id_client[$ipid], // 'Team_ID',//  (like before an ISPC generated Team ID which does not allow to find out which team this is
                        $external_id[$ipid], // 'PAT_ID',// => Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
                        $export_date, // 'Export_date',// => date of export
                        
                        $patient_details[$ipid]['gender'], // 'Geschlecht',// => gender with the values above
                        $patient_details[$ipid]['birth_year'],// YEAR of birth (4 digit)//$patient_details[$ipid]['age'], // 'Alter',// => AGE numeric
                        $patient_details[$ipid]['zip_density'], // 'Bevölkerungsdichte',// => you will get a excel sheet which maps all ZIP codes to the density of living. We just show how dense the area of the patient is with the 3 above values.
                        $patient_details[$ipid]['contact_perons'], //'Angehörige / Vertrauensperson - ', // => contact person available YES / NO
                        $patient_details[$ipid]['living_will'],//'Patientenverfügung', // => take from ACP box
                        $patient_details[$ipid]['healthcare_proxy'],//'Vollmacht', // => take from ACP box
                        $patient_details[$ipid]['care_orders'],//Betreuungsurkunde', // => take from ACP box
                        $patient_details[$ipid]['first_pflegegrade'],//'Pflegegrad (Aufnahme)', // => Pflegegrad on admission
                        $patient_details[$ipid]['last_pflegegrade'],//'Pflegegrad (Entlassung)', // => Pflegegrad on discharge
                        
                        $first_location_admission[$ipid],//  $patient_details[$ipid]['locations'][0],//'Aufenthaltsort (Aufnahme)', // => first location
                        $last_location_admission[$ipid],// end($patient_details[$ipid]['locations']),//'Aufenthaltsort (Entlassung)', // => last location
                        
                        $karnofsky_arr[$ipid]['first_val'],//'Karnofsky Index (erster Wert)',//- first Karnofsky
                        $karnofsky_arr[$ipid]['first_date'],//'Karnofsky Index Datum (erster Wert)',//  first Karnofsky DATE

                        $karnofsky_arr[$ipid]['last_val'],//'Karnofsky Index (letzter Wert)',//  last Karnofsky
                        $karnofsky_arr[$ipid]['last_date'],//'Karnofsky Index Datum (letzter Wert)',//  last Karnofsky DATE
                        
//                         count($patient_details[$ipid]['valid_sapv_days']),//'Versorgungstage mit aktiver SAPV !!!!!!!!!!!!!!!!!!!'// => numeric value of SAPV days in report period.
                        count($overal_sapv_days[$ipid]),//.'/'.count($patient_details[$ipid]['valid_sapv_days']),//'Versorgungstage mit aktiver SAPV !!!!!!!!!!!!!!!!!!!'// => numeric value of SAPV days in report period.// CHANGE (to count the period days from VSTN_Behandlungsabschnitte - 10.12.2018 ISPC2193 comment from 03.12.2018) 
                        $patient_details[$ipid]['death_ocation'] //'Sterbeort - LOCATION of dying'
                    );
				}
				
				//
				//FILE: 2.Symptome
				//
				$csv2 = array(); // Symptomatic details
				$allcsvs[2][0] = $csv2[0] = array(
    				'Pat_ID',//	=> Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
                    'Behandlungsabschnitt_ID',// - TREATMENT BLOCK ID (see below)
                    'Symptomausprägung',// - as all teams in ISPC use the 0-10 scale export hardcoded "0,1,2,3,4,5,6,7,8,9,10"
                    'Symptom',//- string of symptom
                    'Ausprägung Begin',// - FIRST symptom value in report period
                    'Ausprägung Ende',// - LAST symptom value in report period
                				    
				);
				
                // get symptomatic data 				
				$Symptomatology_obj = new Symptomatology();
				$SymptomatologyValues_obj = new SymptomatologyValues();
				$system_system_details = $SymptomatologyValues_obj->getSymptpomatologyValues('1');

				
				$symp_period = array();
				foreach($export_periods as $sipid => $adms_periods)// change to new admission blocks 
// 				foreach($admission_periods as $sipid => $adms_periods)// change to new admission blocks 
				{
				    foreach($adms_periods as $ak=>$aperiod)
				    {
				        //$symp_period[$sipid][$ak][$aperiod['start'].' - '.$ak][] = $Symptomatology_obj->getPatientSymptpomatologyFirstAdm($sipid,1,$aperiod['start']);
				        //$symp_period[$sipid][$ak][$aperiod['end'].' - '.$ak][] = $Symptomatology_obj->getPatientSymptpomatologyLastEnteredAdm($sipid,1,$aperiod['end']);
				        
				        $symp_period[$sipid][$ak]['adm'] = $Symptomatology_obj->getPatientSymptpomatologyFirstAdm($sipid,1,$aperiod['start']);
				        $symp_period[$sipid][$ak]['dis'] = $Symptomatology_obj->getPatientSymptpomatologyLastEnteredAdm($sipid,1,$aperiod['end']);
				    }
				}

				
				$dgp_symptom_mapping = array(
				    "1"=>	'kein',
				    "2"=>	'leicht',
				    "3"=>	'mittel',
				    "4"=>	'stark',
				);
				
				$symptom_mapping = array(
				    "0"=>	'kein',
				    
				    "1"=>	'leicht',
				    "2"=>	'leicht',
				    "3"=>	'leicht',
				    "4"=>	'leicht',
				    
				    "5"=>	'mittel',
				    "6"=>	'mittel',
				    "7"=>	'mittel',
				    
				    "8"=>	'stark',
				    "9"=>	'stark',
				    "10"=>	'stark'
				);
				$ident = array();
                foreach ($ipids as $ipid) {
                    if (! empty($symp_period[$ipid])) { 
                        
                        foreach ($symp_period[$ipid] as $adm_per_k => $values_per_adm) {
                            foreach ($values_per_adm['adm'] as $sym_key => $sym_val_array) {
                                if( strlen($sym_val_array['value'] ) > 0 || strlen($values_per_adm['dis'][$sym_key]['value']) > 0 ){
                                    $entrydmy = date('d.m.Y',strtotime($sym_val_array['entry_date']));
                                    if(!empty($patientday2blockid[$ipid][$entrydmy])){
                                        $symp_block_id = $patientday2blockid[$ipid][$entrydmy];
                                    } else{
                                        $symp_block_id ="";
                                    }
                                    $ident_str="";
                                    $ident_str = $external_id[$ipid].$sym_val_array['description'].$symp_block_id;
                  
                                    
                                    if( ! in_array($ident_str, $ident[$ipid])){
                                        $allcsvs[2][] = $csv2[] = array(
                                            $external_id[$ipid], // PAT_ID
                                            $symp_block_id, // TREATMENT BLOCK ID ????
                                            "0,1,2,3,4,5,6,7,8,9,10", // - as all teams in ISPC use the 0-10 scale export hardcoded "0,1,2,3,4,5,6,7,8,9,10"
                                            //utf8_encode($sym_val_array['description']), // 'Symptom',// => symptom type (one line per symptom)
                                            $sym_val_array['description'], // 'Symptom',// => symptom type (one line per symptom)
                                            $sym_val_array['value'], // 'Ausprägung',// (Aufnahme) => first value
                                            $values_per_adm['dis'][$sym_key]['value'] // 'Ausprägung'// (Entlassung) => last value
                                        );
                                        
                                        $ident[$ipid][] = $ident_str;
                                    
                                    }    
                                }
                            }
                        }
                        
                    }
                }
                if($logininfo->userid == "338")
                {
//                     echo "<pre>";
//                     print_r($ident);
//                     print_r($export_periods);
//                     print_r($symp_period);
//                     print_r( $csv2);
//                     exit;
                }
                
                
                // get all filles assessments
                $kvnoassessment = Doctrine_Query::create()
                ->select("*")
                ->from('KvnoAssessment')
                ->whereIn("ipid", $ipids)
                ->andWhere('iscompleted = "1"')
                ->orderBy('completed_date ASC')
                ->fetchArray();
                
                $assessment_in_block_period = array();
                
                foreach($kvnoassessment as $k=>$kvass){
                    
                $ka = 0 ;
                    foreach($export_periods[$kvass['ipid']] as $block_id=>$adm_data)
//                     foreach($admission_periods[$kvass['ipid']] as $block_id=>$adm_data)
                    {
                        if (Pms_CommonData::isintersected ( strtotime ( $adm_data ['start'] ), strtotime ( $adm_data ['end'] ), strtotime ( $kvass['completed_date'] ), strtotime (  $kvass['completed_date'] ) )) {
//                             $assessment_in_block_period[$kvass['ipid']][$block_id]['care_at_admission'][] = $kvass['care_at_admission'];
//                             $assessment_in_block_period[$kvass['ipid']][$block_id]['partners'][] = $kvass['partners'];
                            $assessment_in_block_period[$kvass['ipid']][$ka]['care_at_admission'] = $kvass['care_at_admission'];
                            $assessment_in_block_period[$kvass['ipid']][$ka]['partners'] = $kvass['partners'];
                            
                            if(!empty($patientday2blockid[$kvass['ipid']][date('d.m.Y',strtotime($kvass['completed_date']))])){
                                $asm_block_id = $patientday2blockid[$kvass['ipid']][date('d.m.Y',strtotime($kvass['completed_date']))];
                            } else{
                                $asm_block_id ="";
                            }
                            
                            $assessment_in_block_period[$kvass['ipid']][$ka]['BLOCK_ID'] = $asm_block_id;
                            $assessment_in_block_period[$kvass['ipid']][$ka]['completed_date'] = date('d.m.Y',strtotime($kvass['completed_date']));
                            $ka++;
                        }
                    }
                }
                
                //
                //FILE: 3.Versorgung_bei_Ubernahme
                //
                $csv3 = array();
                $allcsvs[3][0] = $csv3[0] = array(
                    'PAT_ID',       // =>	Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
                    'Versorgung' //	 => Versorgung - add a new field in the Nordrhein Assessment "Versorgung bei Übernahme"                     
                );
                foreach ($ipids as $ipid) {

                    if(!empty($assessment_in_block_period[$ipid]) && strlen($assessment_in_block_period[$ipid][0]['care_at_admission']) > 0 ){
                        $care_at_admission = "";
                        $care_at_admission = $assessment_in_block_period[$ipid][0]['care_at_admission']; // first ever < change per admission 
                        
                        $allcsvs[3][] =  $csv3[] = array(
                            $external_id[$ipid], //PAT_ID
                            $care_at_admission, //'Versorgung' //	 => Versorgung - add a new field in the Nordrhein Assessment "Versorgung bei Übernahme"
                        );
                    }
                }
 
              
                //
                //FILE: 4.Beteiligte_Dienste
                //
                $text_arrays['partners'][24]="SAPV-TEAM";
                $csv4 = array();
                $allcsvs[4][0] = $csv4[0] = array(
                    'PAT_ID',       // =>	Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
                    'Dienst',        //  =>	Dienst - we have already a block in Nordrhein Assessment which lists many items which are also listed in the register (see screenshot). Lets "upgrade this block" so it shows ALL items of the relevant register block (see screenshot). list all selected items in a new line in this export but also prefill the register data with it (admission block of the relevant Fall)
                );
                foreach ($ipids as $ipid) {
                
                    if(!empty($assessment_in_block_period[$ipid]) && !empty($assessment_in_block_period[$ipid][0]['partners'])){

                        $dgp_partners = "";
                        foreach($assessment_in_block_period[$ipid][0]['partners'] as $part_id){
                            if(strlen($text_arrays['partners'][$part_id]) > 1 ){
//                                 $dgp_partners .= $text_arrays['partners'][$part_id].', ' ;
                                $allcsvs[4][] =  $csv4[] = array(
                                    $external_id[$ipid], //PAT_ID
                                    $text_arrays['partners'][$part_id],//Dienst 
                                );
                            }
                        }
                        
                    }
                }
                //
                //FILE: 5.Diagnosen
                //                
				$csv5 = array();
				$allcsvs[5][0] = $csv5[0] = array(
                    'PAT_ID',       // =>	Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
                    'ICD10',        //  =>	ICD value of diagnosis
                    'Bezeichnung',  // => 	ICD text
                    'Aufn Diagn',   //  =>	 leave blank
                    'Hauptdiagnose',//	 => HD yes / no
                    'Nebendiagnose' //	 => ND yes / no
                );
				// get diagnosis data
				$PatientDiagnosis_obj = new PatientDiagnosis();
				$diagnosis_arr = $PatientDiagnosis_obj->get_multiple_finaldata($ipids);
				
				
				$diags = array();
				foreach($diagnosis_arr as $diag)
				{
				    $diags[$diag['ipid']][]=$diag;
				}
				
				$dtypes = new DiagnosisType();
				$digtypes_arr = $dtypes->get_client_diagnosistypes($clientid);
				
				foreach($digtypes_arr as $k=>$d_details)
				{
				    $digtypes[$d_details['abbrevation']] = $d_details['id'];
				}
				
				$period_start = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['start']));
				$period_end = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['end']));
				
				
				
				$diagno_ipids = array();
				if(!empty($diags)){
    				$diagno_ipids = array_keys($diags);
				}
				
				if(!empty($diagno_ipids)){
				}
				
				$diagnosis_details = array();
				foreach($ipids as $ipid){
				    $dl = 0;
				    foreach ($diags[$ipid] as $diag) {

				        $diag[$diag['create_date'].'_24h_admission'] = "Nein";
				        
				        if(!empty($admission_dates[$ipid])){
				            
				            $documented_interval = 0;
				            $diff_hours = 0;
				            
				            
    				        foreach($admission_dates[$ipid] as $adm_date){
    				            
                               if( strtotime($diag['create_date']) >= strtotime($adm_date) ){
                                   
        				           $documented_interval = strtotime($diag['create_date']) - strtotime($adm_date);
        				           $diff_hours = $documented_interval / (60*60);
        				           
        				           if($diff_hours >= 0  && $diff_hours <= 24){
        				               $diag[$diag['create_date'].'_24h_admission']  = "Ja";
        				           }
                               } 
    				        }
				        }
				        
				        
				        if ($diag['diagnosis_type_id'] == $digtypes['HD']) {
				            $diag['is_main_diagnosis'] = "Ja";
				        }  else{
				            $diag['is_main_diagnosis'] = "Nein";
				        }
				        if ($diag['diagnosis_type_id'] == $digtypes['ND']) {
				            $diag['is_side_diagnosis'] = "Ja";
				        } else{
				            $diag['is_side_diagnosis'] = "Nein";
				        }
				        
				        
				        $allcsvs[5][] =  $csv5[] = array(
				            $external_id[$ipid], //PAT_ID
				            $diag['icdnumber'], //ICD10
				            $diag['diagnosis'], //Bezeichnung
				            $diag[$diag['create_date'].'_24h_admission'], //Aufn Diagn = was this diagnosis documented till 24h after admission? 
				            $diag['is_main_diagnosis'],//Hauptdiagnose
				            $diag['is_side_diagnosis']//Nebendiagnose
				        );
				        
				    }
				}
				
				

				//
				//FILE: 6.Verordnung 
				//
				$csv6 = array(); // SAPV 
				$allcsvs[6][0] =$csv6[0] = array(
				    'PAT_ID',       //=> String	  => Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
				    'Verordnung_ID',//=> String	  => ID of verordnung
				    'Verordnung',   //=> SAPV Erstverordnung, SAPV Folgeverordnung	=>	print one of the 2 above values related to Verordnung
				    'Von',          //=> Datum    => from
				    'Bis',          //=> Datum    => till
				    'Genehmigt Von',//=> Datum    => "genehmigt" from
				    'Genehmigt bis',//=> Datum    => "genehmigt" till
				    'Voll',         //=> Ja, Nein => VV yes / no
				    'Teil',         //=> Ja, Nein => TV yes / no
				    'Beratung',     //=> Ja, Nein => BE yes / no
				    'Koordination', //=> Ja,Nein  => KO yes / no
				);
				
				foreach($ipids as $ipid){
				    if(!empty($sapv_data_array[$ipid])){
				        foreach($sapv_data_array[$ipid] as $sapv_id){
				            if(!empty($patient_sapv_array[$ipid][$sapv_id])){
				                
				                //Verordnung
				                if ($patient_sapv_array[$ipid][$sapv_id]['sapv_order'] == "1"){
				                    $patient_sapv_array[$ipid][$sapv_id]['sapv_order_text'] = "SAPV Erstverordnung";
				                } elseif ($patient_sapv_array[$ipid][$sapv_id]['sapv_order'] == "2"){
				                    $patient_sapv_array[$ipid][$sapv_id]['sapv_order_text'] = "SAPV Folgeverordnung";
				                } else {
				                    if($patient_sapv_array[$ipid][$sapv_id]['KEY'] == 0 ){
    				                    $patient_sapv_array[$ipid][$sapv_id]['sapv_order_text'] = "SAPV Erstverordnung";
				                    } else{
    				                    $patient_sapv_array[$ipid][$sapv_id]['sapv_order_text'] = "SAPV Folgeverordnung";
				                    }
				                    
				                }	                
				                
				                // VOLL
				                if (in_array("4",$patient_sapv_array[$ipid][$sapv_id]['types_arr'])){
				                    $patient_sapv_array[$ipid][$sapv_id]['vv'] = "Ja";
				                } else{
				                    $patient_sapv_array[$ipid][$sapv_id]['vv'] = "Nein";
				                }
				                
				                // Teil
				                if (in_array("3",$patient_sapv_array[$ipid][$sapv_id]['types_arr'])){
				                    $patient_sapv_array[$ipid][$sapv_id]['tv'] = "Ja";
				                } else{
				                    $patient_sapv_array[$ipid][$sapv_id]['tv'] = "Nein";
				                }
				                
				                // Koordination
				                if (in_array("2",$patient_sapv_array[$ipid][$sapv_id]['types_arr'])){
				                    $patient_sapv_array[$ipid][$sapv_id]['ko'] = "Ja";
				                } else{
				                    $patient_sapv_array[$ipid][$sapv_id]['ko'] = "Nein";
				                }
				                // Beratung
				                if (in_array("1",$patient_sapv_array[$ipid][$sapv_id]['types_arr'])){
				                    $patient_sapv_array[$ipid][$sapv_id]['be'] = "Ja";
				                } else{
				                    $patient_sapv_array[$ipid][$sapv_id]['be'] = "Nein";
				                }
				                
				               $allcsvs[6][] = $csv6[] = array(
				                    $external_id[$ipid], // PAT_ID
				                    $sapv_id,// Verordnung_ID
				                    $patient_sapv_array[$ipid][$sapv_id]['sapv_order_text'], // Verordnung 
				                    $patient_sapv_array[$ipid][$sapv_id]['regulation_start'],// Von   //Changed on 04.09.2018 By Ancuta - for comment from 03.09.2018
				                    $patient_sapv_array[$ipid][$sapv_id]['regulation_end'],// Bis 
				                    $patient_sapv_array[$ipid][$sapv_id]['start'],// Genehmigt Von
				                    $patient_sapv_array[$ipid][$sapv_id]['end'],// Genehmigt bis
				                    $patient_sapv_array[$ipid][$sapv_id]['vv'], // Voll
				                    $patient_sapv_array[$ipid][$sapv_id]['tv'], // Teil
				                    $patient_sapv_array[$ipid][$sapv_id]['be'], // Koordination
				                    $patient_sapv_array[$ipid][$sapv_id]['ko'], // Beratung
				                    
				                );
				            }
				        }
				    }
				}
				
				//
				//FILE: 7.VSTN_BEHANDLUNGSABSCHNITTE
				//
				$csv7 = array();// 
				$allcsvs[7][0] =$csv7[0] = array(
				    'Pat_ID',//	=> 	String			Pat_ID
				    'Verordnung_ID',//	=> 	String			Verordnung_ID - reference to the above Verordnung_ID
				    'Behandlungsabschnitt_ID',//	=> 	String			Behandlungsabschnitt_ID - generate an ID for this treatment block so it can be referenced in other blocks
				    'Von',//	=> 	Datum			Von - DATE from
				    'Bis',//	=> 	Datum			Bis - DATE till
// 				    'block_days',//	=> Dummy column
				    'Pflegegrad (Aufnahme)',//	=> 	Kein, Beantragt, 1,2,3,4,5, unbekannt			Pflegegrad (Aufnahme) - Pflegegrad on admission in this block
				    'Pflegegrad (Entlassung)',//	=> 	Kein, Beantragt, 1,2,3,4,5, unbekannt			Pflegegrad (Entlassung)- Pflegegrad on discharge in this block
				    'Aufenthaltsort (Aufnahme)',//	=> 	Zu Hause alleine, zu Hause mit anderen, Hospiz, Heim, Sonstiges 			Aufenthaltsort (Aufnahme) - location type on admission in this block (ATTENTION SEE BELOW!!)
				    'Aufenthaltsort (Entlassung)',//	=> 	Zu Hause alleine, zu Hause mit anderen, Hospiz, Heim, Sonstiges 			Aufenthaltsort (Entlassung) - location type on discharge in this block
				    'Karnofsky Index (erster Wert)',//	=> 	0,10,…,100			Karnofsky Index (erster Wert) - first karnofsky in this block
				    'Karnofsky Index Datum (erster Wert)',//	=> 				Karnofsky Index Datum (erster Wert) - first karnofsky in this block DATE
				    'Karnofsky Index (letzter Wert)',//	=> 				Karnofsky Index (letzter Wert) - last karnofsky in this block
				    'Karnofsky Index Datum (letzter Wert)',//	=> 				Karnofsky Index Datum (letzter Wert) - last karnofsky in this block DATE
				    'Entlassungsgrund',//	=> 	String			Entlassungsgrund - string of discharge reason (see above for the generated 2 reasons "Krankenhausaufenthalt" "Verordnungswechsel")
				    'Zufriendenheit',//	=> 	sehr gut, gut, mittel, schlecht, sehr schlecht			Zufriendenheit - Zufriedenheit from the register block
				);
				
				

				
// 				dd($export_periods,$dgp_discharge2readmission_id);
				foreach($ipids as $ipid){
//     				foreach($admission_periods[$ipid] as $admk=>$adm_data)
    				foreach($export_periods[$ipid] as $edmk=>$exp_per_data)
    				{
    				    // check if export periods are in requested period
    				    
// 			           $sapvperiod_ids = implode(", ", $sapv_in_admission_period[$ipid][$exp_per_data['start'].' - '.$exp_per_data['end'] ]);
// 			           $sapvperiod_ids = implode(", ", $sapv_in_export_period[$ipid][$exp_per_data['BLOCK_ID']]);
			           $sapvperiod_ids = $sapv_in_export_period[$ipid][$exp_per_data['BLOCK_ID']][0];
			           
                        if($patients_discharge_data_details[$ipid][$exp_per_data['end']]['died']  == "1"){
                            $death_location[$ipid][$exp_per_data['end']] = $patients_discharge_data_details[$ipid][$exp_per_data['end']]['type_location_of_death']; 
                        } else{
                            $death_location[$ipid][$exp_per_data['end']] = ""; 
                        }    

                        $zufriedenheit_mit = "";
                        if(isset($exp_per_data['patient_readmission_dis_ID'])){
                            
                            if(!empty($dgp_discharge2readmission_id[$ipid])  && !empty($dgp_discharge2readmission_id[$ipid][$exp_per_data['patient_readmission_dis_ID']]) ){
                                $zufriedenheit_mit_val = $dgp_discharge2readmission_id[$ipid][$exp_per_data['patient_readmission_dis_ID']]['zufriedenheit_mit'];
                                $zufriedenheit_mit  = $text_arrays['zufriedenheit_mit'][$zufriedenheit_mit_val];
                            }
                        } 
			             
			             $first_pflgrate[$exp_per_data['BLOCK_ID']] = "";
			             $last_pflgrate[$exp_per_data['BLOCK_ID']] = "";
			             if(!empty($pflegedrade_array2period[$ipid][$exp_per_data['BLOCK_ID']])){
			                 $first_pflgrate[$exp_per_data['BLOCK_ID']] = $pflegedrade_array2period[$ipid][$exp_per_data['BLOCK_ID']][0];
			                 $last_pflgrate[$exp_per_data['BLOCK_ID']] = end($pflegedrade_array2period[$ipid][$exp_per_data['BLOCK_ID']]);
			             }
			             
 
			             if(!empty($karnofsky_array2period[$ipid][$exp_per_data['BLOCK_ID']])){
			                 
			                 $first_karnofsky_val[$exp_per_data['BLOCK_ID']] = $karnofsky_array2period[$ipid][$exp_per_data['BLOCK_ID']][0]['val'];
			                 $first_karnofsky_date[$exp_per_data['BLOCK_ID']] = $karnofsky_array2period[$ipid][$exp_per_data['BLOCK_ID']][0]['datedmy'];

			                 
			                 $last_karnofsky_arr[$exp_per_data['BLOCK_ID']] = end($karnofsky_array2period[$ipid][$exp_per_data['BLOCK_ID']]);
			                 
			                 $last_karnofsky_val[$exp_per_data['BLOCK_ID']] = $last_karnofsky_arr[$exp_per_data['BLOCK_ID']]['val'];
			                 $last_karnofsky_date[$exp_per_data['BLOCK_ID']] = $last_karnofsky_arr[$exp_per_data['BLOCK_ID']]['datedmy'];
			             }
			             
			             
			             
			            $allcsvs[7][] = $csv7[] = array(
			                $external_id[$ipid], //'Pat_ID',//	=> 	String			Pat_ID
			                $sapvperiod_ids,//'Verordnung_ID',//	=> 	String			Verordnung_ID - reference to the above Verordnung_ID <- just add the FIRST Verordnung ID[requested on 04.07.2017]
			                $exp_per_data['BLOCK_ID'],//'Behandlungsabschnitt_ID',//	=> 	String			Behandlungsabschnitt_ID - generate an ID for this treatment block so it can be referenced in other blocks
			                $exp_per_data['start'],//'Von',//	=> 	Datum			Von - DATE from
			                $exp_per_data['end'],//'Bis',//	=> 	Datum			Bis - DATE till
// 			                count($exp_per_data['days']),
			                $first_pflgrate[$exp_per_data['BLOCK_ID']] ,//'Pflegegrad (Aufnahme)',//	=> 	Kein, Beantragt, 1,2,3,4,5, unbekannt			Pflegegrad (Aufnahme) - Pflegegrad on admission in this block
			                $last_pflgrate[$exp_per_data['BLOCK_ID']],//'Pflegegrad (Entlassung)',//	=> 	Kein, Beantragt, 1,2,3,4,5, unbekannt			Pflegegrad (Entlassung)- Pflegegrad on discharge in this block
			                
			                $location_admission[$ipid][$exp_per_data['BLOCK_ID']],//'Aufenthaltsort (Aufnahme)',//	=> 	Zu Hause alleine, zu Hause mit anderen, Hospiz, Heim, Sonstiges 			Aufenthaltsort (Aufnahme) - location type on admission in this block (ATTENTION SEE BELOW!!)
			                $location_discharge[$ipid][$exp_per_data['BLOCK_ID']],//'Aufenthaltsort (Entlassung)',//	=> 	Zu Hause alleine, zu Hause mit anderen, Hospiz, Heim, Sonstiges 			Aufenthaltsort (Entlassung) - location type on discharge in this block
			                
			                $first_karnofsky_val[$exp_per_data['BLOCK_ID']],//'Karnofsky Index (erster Wert)',//	=> 	0,10,…,100			Karnofsky Index (erster Wert) - first karnofsky in this block
			                $first_karnofsky_date[$exp_per_data['BLOCK_ID']] ,//'Karnofsky Index Datum (erster Wert)',//	=> 				Karnofsky Index Datum (erster Wert) - first karnofsky in this block DATE
			                
			                $last_karnofsky_val[$exp_per_data['BLOCK_ID']],//'Karnofsky Index (letzter Wert)',//	=> 				Karnofsky Index (letzter Wert) - last karnofsky in this block
			                $last_karnofsky_date[$exp_per_data['BLOCK_ID']],//'Karnofsky Index Datum (letzter Wert)',//	=> 				Karnofsky Index Datum (letzter Wert) - last karnofsky in this block DATE
			                
			                $exp_per_data['discharge_reason'],//'Entlassungsgrund',//	=> 	String			Entlassungsgrund - string of discharge reason (see above for the generated 2 reasons "Krankenhausaufenthalt" "Verordnungswechsel")
			                $zufriedenheit_mit,//'Zufriendenheit',//	=> 	sehr gut, gut, mittel, schlecht, sehr schlecht			Zufriendenheit - Zufriedenheit from the register block
			           );
    				}
				}

// 				dd($csv7);
// 				dd($export_periods);
// 				dd($export_periods);
				
				
				//
				//FILE: 8.Kontakte
				//
				//get doctor and nurse users
				//get all related users details
				$master_groups_first = array('4', '5');
				$usergroups = new Usergroup();
				$user = new User();
				$client_user_groups_first = $usergroups->getUserGroups($master_groups_first);
					
				foreach($client_user_groups_first as $k_group_f => $v_group_f)
				{
				    $master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
				    $group2mastergroup[$v_group_f['id']] = $v_group_f['groupmaster']; 
				}
					
				$client_users = $user->getClientsUsers($clientid);
					
				$nurse_users = array();
				$doctor_users = array();
				foreach($client_users as $k_cuser_det => $v_cuser_det)
				{
				    $master_user_details[$v_cuser_det['id']] = $v_cuser_det;
				    if(in_array($v_cuser_det['groupid'], $master2client['5']))
				    {
				        $nurse_users[] = $v_cuser_det['id'];
				    }
				    else if(in_array($v_cuser_det['groupid'], $master2client['4']))
				    {
				        $doctor_users[] = $v_cuser_det['id'];
				    }
				    $user2group[$v_cuser_det['id']] = $v_cuser_det['groupid'];
				}
					

				
				//Get deleted contact froms from patient course
/* 				$deleted_cf = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('wrong=1')
				->andWhereIn('ipid', $ipids)
				->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
				->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'");
				$deleted_cf_array = $deleted_cf->fetchArray();
				
				
				$excluded_cf_ids = array();
				foreach($deleted_cf_array as $k_dcf => $v_dcf)
				{
				    $excluded_cf_ids[] = $v_dcf['recordid'];
				}
 */				
				// Use array from visits details line 1138 - done before ecog 
				
				$excluded_cf_ids = array();
				if(!empty($del_visits['contact_form'])){
    				$excluded_cf_ids = array_unique($del_visits['contact_form']);
				}
				
				//get cf in period exclude deleted
				$cf = new ContactForms();
// 				$contactforms  = $cf->get_multiple_contact_form_by_periods($ipids, $periods, $excluded_cf_ids,true);
				$contactforms  = $cf->get_multiple_contact_form_by_periods($ipids, "no period", $excluded_cf_ids,true);
				
				$cf_ipids = array();
				$contact_forms_ids = array();
				
				foreach($contactforms as $ipids_key=>$cfss){
				    foreach($cfss as $k=>$cfdata){
				        $contact_forms_ids[] = $cfdata['id']; 
				        $cf_ipids[] = $cfdata['ipid']; 
				    }
				}
				$contact_forms_ids = array_unique($contact_forms_ids);
				$cf_ipids = array_unique($cf_ipids);
				
				$FormBlockAdditionalUsers_obj = new FormBlockAdditionalUsers();
				$block_au_data = $FormBlockAdditionalUsers_obj->getPatientFormBlockAdditionalUsers($cf_ipids, $contact_forms_ids, false, true);
				
				$involved_groups = array();
				foreach($block_au_data as $cfid=>$block_au_array ){
				    foreach($block_au_array as $k=>$block_au){
    				    $involved_groups[$block_au['contact_form_id']][] = $user2group[$block_au['additional_user']]; 
				    }
				}
				
				$FormBlockDrivetimedoc_obj = new FormBlockDrivetimedoc();
				$driving_array = $FormBlockDrivetimedoc_obj->get_multiple_block_drivetimedoc($cf_ipids, $contact_forms_ids, false, true);
				
				// get form types
				$FormTypes_obj = new FormTypes();
				$client_forms = $FormTypes_obj->get_form_types($clientid);
				
				foreach($client_forms as $ft_id=>$ftdata){
				    $contact_form_name[$ftdata['id']] = $ftdata['name'];
				}
			    $contact_form_name['V'] = "Koordination";
			    $contact_form_name['XT'] = "Telefonat";
			    $contact_form_name['U'] = "Beratung";
			    // Include old visits - required on 19.03.2019 in TODO-2179
			    $contact_form_name['kvno_doctor_form'] = "Besuchsformular Arzt";
			    $contact_form_name['kvno_nurse_form'] = "Besuchsformular Pflege";
			    $contact_form_name['bayern_doctorvisit'] = "Besuchsformular";
			    $contact_form_name['visit_koordination_form'] = "Besuchsformular Koordination";
			    
				// XT and V actions  - also export XT & U & V beside the contact forms [requested on 04.07.2017]
				$course_arr = array();
				$shortcuts = array("XT","V","U");
				$PatientCourse_obj = new PatientCourse();
				$course_arr = $PatientCourse_obj->get_patients_period_course_by_shortcuts($ipids,$shortcuts,false,false,true);
// 				dd($course_arr);
				
				foreach($course_arr as $ipid=>$course_data){
				    
				    foreach($course_data as $cdate => $cvals){

				        
				        $course_title_arr = array();
				        foreach($cvals as $k=>$cval){

			                $contactforms[$ipid]['pc'.$cval['id']] = $cval;
			                $contactforms[$ipid]['pc'.$cval['id']]['id'] = 'pc'.$cval['id'];
			                $contactforms[$ipid]['pc'.$cval['id']]['course_id'] = $cval['id'];
			                $contactforms[$ipid]['pc'.$cval['id']]['form_type'] = $cval['course_type'];
				            $course_title_arr = explode("|",$cval['course_title']);
				            
				            
				            $duration="";
				            
				            if(count($course_title_arr) == 3)
				            {
				                //method implemented with 3 inputs
				                $duration = $course_title_arr[0];
				                $contactforms[$ipid]['pc'.$cval['id']]['start_date'] = date("Y-m-d H:i:s",strtotime($course_title_arr[2].":00"));
				                
				                $minutes = "";
				                $minutes = "+".$duration." minutes";
				                $contactforms[$ipid]['pc'.$cval['id']]['end_date'] = date("Y-m-d H:i:s",strtotime($minutes, strtotime($course_title_arr[2].":00")));
				                
				            }
				            else if(count($course_title_arr) != 3 && count($course_title_arr) < 3)
				            {
				                //old method before anlage 10
				                $duration = $course_title_arr[0];
				                $contactforms[$ipid]['pc'.$cval['id']]['start_date'] = $cval['done_date'];
				                
				                $minutes = "";
				                $minutes = "+".$duration." minutes";
				                $contactforms[$ipid]['pc'.$cval['id']]['end_date'] = date("Y-m-d H:i:s",strtotime($minutes, strtotime($cval['done_date'])));
				                
				 
				            }
				            else if(count($course_title_arr) != 3 && count($course_title_arr) > 3)
				            {
				                //new method (U) 3 inputs and 1 select newly added in verlauf
				                $duration = $course_title_arr[1];
				                $contactforms[$ipid]['pc'.$cval['id']]['start_date'] = date("Y-m-d H:i:s",strtotime($course_title_arr[3].":00"));
				                
				                
				                $minutes = "";
				                $minutes = "+".$duration." minutes";
				                $contactforms[$ipid]['pc'.$cval['id']]['end_date'] = date("Y-m-d H:i:s",strtotime($minutes, strtotime($course_title_arr[3].":00")));
				            }
				            
				            $contactforms[$ipid]['pc'.$cval['id']]['visit_duration'] = $duration;
				        }
				        
				    }
				    
				}
				
				
				// add old visits
				// get all vistis types
				$old_visits  =array();
				$kv_doc = new KvnoDoctor();
				
				$old_visits['kvno_doctor_form'] = $kv_doc->get_visits_multiple_by_periods($ipids, false, $del_visits['old_doctor_visits'], true);
				
				$kv_nurse = new KvnoNurse();
				$old_visits['kvno_nurse_form'] = $kv_nurse->get_visits_multiple_by_periods($ipids, false, $del_visits['old_nurse_visits'], true);
				
				$bay_doc = new BayernDoctorVisit();
				$old_visits['bayern_doctorvisit'] = $bay_doc->get_visits_multiple_by_periods($ipids, false, $del_visits['bayern_doctorvisit'], true);
				
				$koord_visit = new VisitKoordination();
				$old_visits['visit_koordination_form'] = $koord_visit->get_visits_multiple_by_periods($ipids, false, $del_visits['visit_koordination_form'], true);
				
				
				foreach($old_visits as $visit_type=>$ipids_visits){
				    foreach($ipids_visits as $vipid => $visit_details){
				        foreach($visit_details  as $visit_id=>$cval)
				        {
				            $contactforms[$vipid][$visit_type.'_'.$cval['id']] = $cval;
				            $contactforms[$vipid][$visit_type.'_'.$cval['id']]['id'] = $visit_type.'_'.$cval['id'];
				            $contactforms[$vipid][$visit_type.'_'.$cval['id']]['form_type'] = $visit_type;
				        }
				    }
				}
				
				//pc12285164
				$csv8 = array();// Contact froms
				$allcsvs[8][0] =$csv8[0] = array(
        			'PAT_ID',//	=>	String		=>Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
        			'Behandlungsabschnitt_ID',//	=> 	String			Behandlungsabschnitt_ID - generate an ID for this treatment block so it can be referenced in other blocks
        			'Kontakt Datum',//	=>	Datum	=>	contact form date
        			'Uhrzeit',//	=>	Uhrzeit	=>	contact form time
        			'Art',//	=>	String	=>	name of contact form
        			'Dauer',// 	=>	Numerisch	=>	length in minutes
        			'Gefahrene Kilometer',//	=>	Numerisch	=>	driven km
        			'Fahrtdauer',//	=>	Numerisch	=>	driving time
        			'Arzt',//	=>	Ja, Nein	=>	User with grouptype doctor involved?
        			'Pflege',//	=>	Ja, Nein	=>	User with grouptype "PFLEGE" involved?
        			'Sonstige - Profession',//	=>	Ja, Nein	=>	User with any other grouptype involved
        			'Kontakt zwischen 22:00 - 06:00',//	=>	Ja, Nein	=>	did the contact from  touch the time between 22:00 and 06:00
				);
				
				if (! empty($contactforms)) {
                    foreach ($contactforms as $cf_ipid => $cfdata_array) {
                        $current = array();
                        foreach ($cfdata_array as $cf => $cfdata) {
                            
                            $current['distance_km'] =  0 ; 
                            if ( isset($driving_array[$cfdata['id']]) && ! empty($driving_array[$cfdata['id']]['fahrtstreke_km1'])) {
                                $current['distance_km'] = $driving_array[$cfdata['id']]['fahrtstreke_km1'];
                            } else {
                                if( isset($cfdata['fahrtstreke_km'])){
                                    $current['distance_km'] = $cfdata['fahrtstreke_km'];
                                } else{
                                    $current['distance_km'] = 0;
                                }
                            }
                            
                            
                            
                            $current['driving_time'] = 0 ;
                            if ( isset($driving_array[$cfdata['id']]) && ! empty($driving_array[$cfdata['id']]['fahrtzeit1'])) {
                                $current['driving_time'] = $driving_array[$cfdata['id']]['fahrtzeit1'];
                            } else {
                                if( isset($cfdata['fahrtzeit'])){
                                    $current['driving_time'] = $cfdata['fahrtzeit'];
                                } else{
                                    $current['driving_time'] = 0;
                                }
                            }
                            
                            
                            
                            $current['involved_doctor_group'] = "Nein";
                            $current['involved_nurse_group'] = "Nein";
                            $current['involved_other_group'] = "Nein";
                            $current['involved_multiple_groups'] = "Nein"; // If more then one group was involved in this visit
                            
                            if (empty($involved_groups[$cfdata['id']])) { // NO DATA IN Additional users block
                                $involved_groups[$cfdata['id']][] = $user2group[$cfdata['create_user']];
                            }
                            
                            if (! empty($involved_groups[$cfdata['id']])) {
                                // DOCTOR GROUPS
                                if (is_array(array_intersect($involved_groups[$cfdata['id']], $master2client['4'])) && ! empty(array_intersect($involved_groups[$cfdata['id']], $master2client['4']))) {
                                    $current['involved_doctor_group'] = "Ja";
                                }
                                
                                // NURSE GROUPS
                                if (is_array(array_intersect($involved_groups[$cfdata['id']], $master2client['5'])) && ! empty(array_intersect($involved_groups[$cfdata['id']], $master2client['5']))) {
                                    $current['involved_nurse_group'] = "Ja";
                                }
                                // OTHER GROUPS
                                if (is_array(array_diff($involved_groups[$cfdata['id']], $master2client['4'], $master2client['5'])) && ! empty(array_diff($involved_groups[$cfdata['id']], $master2client['4'], $master2client['5']))) {
                                    $current['involved_other_group'] = "Ja";
                                }
                                
                                // >1 Profession
                                if (count(array_unique($involved_groups[$cfdata['id']])) > 1) {
                                    $current['involved_multiple_groups'] = "Ja";
                                }
                            }
                            

                            
                            $current['between2200_0600'] ="Nein";
                            if(strtotime(date("d.m.Y",strtotime($cfdata['start_date']))) == strtotime(date("d.m.Y",strtotime($cfdata['end_date'])))){
                                if(   date("H",strtotime($cfdata['start_date'])) <= "22" && date("H",strtotime($cfdata['end_date'])) >= "22" 
                                   || date("H",strtotime($cfdata['start_date'])) >= "22" && date("H",strtotime($cfdata['end_date'])) >= "22"
                                   || date("H",strtotime($cfdata['start_date'])) < "6" && date("H",strtotime($cfdata['end_date'])) >= "6"
                                   || date("H",strtotime($cfdata['start_date'])) < "6" && date("H",strtotime($cfdata['end_date'])) <= "6" 
                                    
                                    )
                                {
                                    $current['between2200_0600'] ="Ja";
                                } else{
                                    $current['between2200_0600'] ="Nein";
                                }
                                
                            } elseif(strtotime(date("d.m.Y",strtotime($cfdata['start_date']))) < strtotime(date("d.m.Y",strtotime($cfdata['end_date'])))){
                                if(   date("H",strtotime($cfdata['start_date'])) <= "22" && (date("H",strtotime($cfdata['end_date'])) >= "22" || date("H",strtotime($cfdata['end_date'])) < "6" )
                                    || date("H",strtotime($cfdata['start_date'])) >= "22" && (date("H",strtotime($cfdata['end_date'])) >= "22" || date("H",strtotime($cfdata['end_date'])) < "6" )
                                
                                )
                                {
                                    $current['between2200_0600'] ="Ja";
                                }else{
                                    $current['between2200_0600'] ="Nein";
                                }
                                
                            }
                       
                            
                            $allcsvs[8][] = $csv8[] = array(
                                $external_id[$cfdata['ipid']], // 'PAT_ID'
                                $patientday2blockid[$cfdata['ipid']][date('d.m.Y', strtotime($cfdata['start_date']))],// "Behandlungsabschnitt_ID", // 'Behandlungsabschnitt_ID'
                                date('d.m.Y', strtotime($cfdata['start_date'])), // 'Kontakt Datum'
                                date('H:i', strtotime($cfdata['start_date'])),// 'Uhrzeit'<- just export the START time[requested on 04.07.2017]
                                $contact_form_name[$cfdata['form_type']], // 'Art'
                                $cfdata['visit_duration'], // 'Dauer'
                                intval($current['distance_km']), // 'Gefahrene Kilometer'
                                $current['driving_time'], // 'Fahrtdauer',// => Numerisch => driving time
                                $current['involved_doctor_group'], // 'Arzt',// => Ja, Nein => User with grouptype doctor involved?
                                $current['involved_nurse_group'], // 'Pflege',// => Ja, Nein => User with grouptype "PFLEGE" involved?
                                $current['involved_other_group'], // 'Sonstige - Profession',// => Ja, Nein => User with any other grouptype involved
                                $current['between2200_0600'] // 'Kontakt zwischen 22:00 - 06:00',// => Ja, Nein => did the contact from touch the time between 22:00 and 06:00
                            );
                        }
                    }
                }

                // create csv files and download zip 
                 
                $file_name_array_old =array(
                    'file-1' => "1.Basisdaten",
                    'file-2' => "2.Symptome",
                    'file-3' => "3.Diagnosen",
                    'file-4' => "4.Verordnung",
                    'file-5' => "5.Fälle",
                    'file-6' => "6.Kontakte",
                );
                 
                $file_name_array =array(
                    'file-1' => "1.Basisdaten",
                    'file-2' => "2.Symptome",
                    'file-3' => "3.Versorgung_bei_Ubernahme",
                    'file-4' => "4.Beteiligte_Dienste",
                    'file-5' => "5.Diagnosen",
                    'file-6' => "6.Verordnung",
                    'file-7' => "7.VSTN_Behandlungsabschnitte",
                    'file-8' => "8.Kontakte",
                );
                
                $old = true;
                
                if($old){
                    
                    // create your zip file
                    // $zipname = 'VSTN_'.$export_date__dmy.'.zip';
                    $zipname = '/tmp/VSTN_'.$export_date__dmyHi.'.zip';
                    $zip = new ZipArchive;
                    $zip->open($zipname, ZipArchive::CREATE);
                    
                    // loop to create 3 csv files
                    for ($i = 1; $i < 9; $i++) {
                    
                        // create a temporary file
                        $fd = fopen('php://temp/maxmemory:1048576', 'w');
                        if (false === $fd) {
                            die('Failed to create temporary file');
                        }
                    
                        // write the data to csv
                        //                     fputcsv($fd, $headers);
                        foreach($allcsvs[$i] as $k=>$record) {
                            fputcsv($fd, $record, ";");
                        }
                    
                        // return to the start of the stream
                        rewind($fd);
                    
                        // add the in-memory file to the archive, giving a name
                        $zip->addFromString($file_name_array['file-'.$i].'.csv', stream_get_contents($fd) );
                        //close the file
                        fclose($fd);
                    }
                    // close the archive
                    $zip->close();
                    
                }
                else
                {
                    // create your zip file
                    $zipname = 'VSTN_'.$export_date__dmyHi.'.zip';
                    $zip = new ZipArchive;
                    $zip->open($zipname, ZipArchive::CREATE);
                    
                    // loop to create all csv files
                    for ($i = 1; $i < 9; $i++) {
                    
                        $string="";
                        foreach($allcsvs[$i] as $k=>$record) {
                            $string .=  $this->array2string($record, ";","");
                        }
     
                        $zip->addFromString($file_name_array['file-'.$i].'.csv', $string );
                        //close the file
                    }
                    // close the archive
                    $zip->close();
                }
                
                header('Content-Type: application/zip');
                header('Content-disposition: attachment; filename='.$zipname);
                header('Content-Length: ' . filesize($zipname));
                readfile($zipname);
                // remove the zip archive
                
                // you could also use the temp file method above for this.
                unlink($zipname);
				exit();

			}
		}
	}
	
	public function array2string($dataArray,$delimiter,$enclosure){
	    // Write a line to a file
	    // $filePointer = the file resource to write to
	    // $dataArray = the data to write out
	    // $delimeter = the field separator
	    
	    // Build the string
	    $string = "";
	    
	    // No leading delimiter
	    $writeDelimiter = FALSE;
	    foreach($dataArray as $dataElement)
	    {
	        // Replaces a double quote with two double quotes
	        $dataElement=str_replace("\"", "\"\"", $dataElement);
	    
	        // Adds a delimiter before each field (except the first)
	        if($writeDelimiter) $string .= $delimiter;
	    
	        // Encloses each field with $enclosure and adds it to the string
	        $string .= $enclosure . $dataElement . $enclosure;
	    
	        // Delimiters are used every time except the first.
	        $writeDelimiter = TRUE;
	    } // end foreach($dataArray as $dataElement)
	    
	    // Append new line
	    $string .= "\n";
	    return $string;
	}
	// NOT USED FROM 25.06.2018
	/**
	 * @deprecated !
	 */
	public function exportv1Action() {
		set_time_limit ( 0 );
		
		
		// error_reporting(E_ALL);
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
		
		$page_lang = $this->view->translate('vstn_export');
		
		if ($this->getRequest ()->isPost ()) {
			
		    
		    //  EXPORT DATE 
		    $export_date = date('d.m.Y');
		    $export_date__dmy = date('d-m-Y');
		    
			
			$lanr = $_POST['lanr'];
			$type = $_POST['type'];
			$s = array(" ","	","\n","\r");
			$ado_id = trim(str_replace($s,array(),$_POST['ado_id']));
			$ado_text_id = trim(str_replace($s,array(),$_POST['ado_text_id']));
			$ado_text = $_POST['ado_text'];
			$ik_option = $_POST['ik_option'];
			$status_option = $_POST['status_option'];
			$diagnosis_side = $_POST['diagnosis_side_option'];
			$diagnosis_main = $_POST['diagnosis_main_option'];
			$action_user = $_POST['user'];
			$action_group = $_POST['group_type'];
			
			$zip_density = ZipDensity::get_zip_density();
			
			if(!empty($_REQUEST['patients']) && !empty($_REQUEST['period']['start']) && !empty($_REQUEST['period']['end'])) {
				
				$ipids = $_REQUEST['patients'];
				$period_start = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['start']));
				$period_end = date('Y-m-d H:i:s', strtotime($_REQUEST['period']['end']));
				
				$quarter_first_day = strtotime($_REQUEST['period']['start']);
				$quarter_last_day = strtotime($_REQUEST['period']['end']);

				$select = "e.epid_num, AES_DECRYPT(p.last_name,'".Zend_Registry::get('salt')."') as last_name,AES_DECRYPT(p.first_name,'".Zend_Registry::get('salt')."') as first_name,convert(AES_DECRYPT(p.zip,'".Zend_Registry::get('salt')."') using latin1) as zip,convert(AES_DECRYPT(p.street1,'".Zend_Registry::get('salt')."') using latin1) as street1,convert(AES_DECRYPT(p.city,'".Zend_Registry::get('salt')."') using latin1) as city,convert(AES_DECRYPT(p.phone,'".Zend_Registry::get('salt')."') using latin1) as phone, convert(AES_DECRYPT(p.sex,'".Zend_Registry::get('salt')."') using latin1) as sex,";
				$periods = array ( '0' => array('start' => date('Y-m-d H:i:s', strtotime($period_start)), 'end' => date('Y-m-d H:i:s', strtotime($period_end))));
				
				$period_days = PatientMaster::getDaysInBetween(date("Y-m-d", strtotime($period_start)), date("Y-m-d", strtotime($period_end)), false);

				$active_cond['interval_sql'] = '((date(%date_start%) <= "'.$periods[0]['start'].'") AND (date(%date_end%) >= "'.$periods[0]['start'].'")) OR ((date(%date_start%) >= "'.$periods[0]['start'].'") AND (date(%date_start%) < "'.$periods[0]['end'].'"))';
					
				if($_REQUEST['tp'] == 'ipids') {
					
				}  
				

				
				$patients_arr = Pms_CommonData::patients_days(array('ipids' => $ipids, 'periods' => $periods, 'client' => $clientid), $select); // TODO -498 - remove standby 02.09.2016
                  
                //patient death detaills
				$patient_death_dates = PatientDischarge::getPatientsDeathDate($clientid,$ipids);
				    	
				// Discharge method details :: dead method
				$DischargeMethod_obj = new DischargeMethod();
				$client_death_methods = array();
				$client_death_methods = $DischargeMethod_obj->get_client_discharge_method($clientid,true);
				$client_discharge_methods = array();
				$client_discharge_methods = $DischargeMethod_obj->get_client_discharge_method($clientid);
				
				// Discharge location details + discharge location types 
				$DischargeLocation_obj = new DischargeLocation();
				$client_discharge_locations = array();
				$client_discharge_locations = $DischargeLocation_obj->getDischargeLocation($clientid,0);
				$system_discharge_locations_types =  Pms_CommonData::getDischargeLocationTypes();
				
				foreach ($client_discharge_locations as $k=>$dl ){
				    if($dl['type'] != 0 ){
    				    $discharge_location_types[$dl['id']] = $system_discharge_locations_types [$dl['type']];  
				    }
				}
				
				// patient discharge details 
				$discharge_data_q = Doctrine_Query::create()
				->select("*,AES_DECRYPT(discharge_comment,'" . Zend_Registry::get('salt') . "') as discharge_comment")
				->from('PatientDischarge')
				->whereIn('ipid', $ipids);
				$discharge_data_arr = $discharge_data_q->fetchArray();
				
				// DGP DATA
				$ipid_str = "";
				foreach ($ipids as $ipid)
				{
				    $ipid_str .= '"' . $ipid. '",';
				}
				if ($ipid_str == "") {
				    $ipid_str='"99999",';
				}
				$ipid_str = substr($ipid_str, 0, -1);
				 
				
				$hospizregister_lang = $this->view->translate('hospizregister_lang');
				
				$dgp_kern_model = new DgpKern();
				$text_arrays = $dgp_kern_model->get_form_texts();
				
				
				$dgp_arr = array();
				// FIRST DGP DATA
				$patientKvnoarrayfirst = array();
				$start = microtime(true);
				$querystr = 'SELECT *,begleitung as partners
		        FROM patient_dgp_kern p
		        INNER JOIN (SELECT id  FROM `patient_dgp_kern` WHERE form_type = "adm" AND ipid IN ('.$ipid_str.') ORDER BY `create_date` ASC ) AS p2
		        ON p.id = p2.id
		        GROUP BY p.ipid
		        ORDER BY p.id asc';
				$manager = Doctrine_Manager::getInstance();
				$manager->setCurrentConnection('MDAT');
				$conn = $manager->getCurrentConnection();
				$query = $conn->prepare($querystr);
				$dropexec = $query->execute();
				$patientKvnoarrayfirst = $query->fetchAll();
				
				
				if(!empty($patientKvnoarrayfirst)){
				    $dgp_first = array();
				    $partners_arr = array();
				    foreach($patientKvnoarrayfirst as $k=>$dgpf){
				        
				        if(!empty($dgpf['partners'])){
				            $partners_arr = explode(',',$dgpf['partners']);
				            foreach($partners_arr as $part_id){
				                if(strlen($text_arrays['partners'][$part_id]) > 1 ){
            				        $dgp_first[$dgpf['ipid']]['partners'] .= $text_arrays['partners'][$part_id].', ' ; 
				                }
				            }
				        }
				        
    				    foreach($text_arrays['symptoms'] as $sym_id =>$sym_details){
                           $dgp_arr[$dgpf['ipid']]['symptoms']['first'][$sym_id]['code'] = $sym_details['code'];
                           $dgp_arr[$dgpf['ipid']]['symptoms']['first'][$sym_id]['value'] = $dgpf[$sym_details['code']];
                        }
				    }
				}
				
				// LAST DGP - for now - we take the last discharge dgp -> to be changed in  
				$patientKvnoarraylast = array();
	
				$querystr = 'SELECT *
				FROM patient_dgp_kern p
				INNER JOIN (SELECT id  FROM `patient_dgp_kern` WHERE form_type = "dis" AND ipid IN ('.$ipid_str.') ORDER BY `create_date` DESC ) AS p2
				ON p.id = p2.id
				GROUP BY p.ipid
				ORDER BY p.id asc;';
				$manager = Doctrine_Manager::getInstance();
				$manager->setCurrentConnection('MDAT');
				$conn = $manager->getCurrentConnection();
				$query = $conn->prepare($querystr);
				$dropexec = $query->execute();
				$patientKvnoarraylast = $query->fetchAll();
				
				if(!empty($patientKvnoarraylast)){
				    foreach($patientKvnoarraylast as $k=>$kvlast){
				        $kvlast_data[$kvlast['ipid']][] = $kvlast;
				        
				        foreach($text_arrays['symptoms'] as $sym_id=>$sym_details){
				            $dgp_arr[$kvlast['ipid']]['symptoms']['last'][$sym_id]['code'] = $sym_details['code'];
				            $dgp_arr[$kvlast['ipid']]['symptoms']['last'][$sym_id]['value'] = $kvlast[$sym_details['code']];
				        }
				        
				    }
				}

				$patients_discharge_data  = array();
				foreach($discharge_data_arr as $k=>$dis){
				    
				    $patients_discharge_data[$dis['ipid']][date('d.m.Y',strtotime($dis['discharge_date']))] = $dis;
				    if(strlen($client_discharge_methods[$dis['discharge_method']])){
    				    $patients_discharge_data[$dis['ipid']][date('d.m.Y',strtotime($dis['discharge_date']))]['discharge_method_str'] = $client_discharge_methods[$dis['discharge_method']];
				    }
				     
				    if (in_array($dis['discharge_method'],$client_death_methods)){
    				    $patients_discharge_data[$dis['ipid']][date('d.m.Y',strtotime($dis['discharge_date']))]['died'] = 1; 
    				    if($dis['discharge_location'] != 0 ){
        				    $patients_discharge_data[$dis['ipid']][date('d.m.Y',strtotime($dis['discharge_date']))]['type_location_of_death'] = $discharge_location_types[$dis['discharge_location']]; 
    				    }
				    }else{
    				    $patients_discharge_data[$dis['ipid']][date('d.m.Y',strtotime($dis['discharge_date']))]['died'] = 0; 
    				    $patients_discharge_data[$dis['ipid']][date('d.m.Y',strtotime($dis['discharge_date']))]['type_location_of_death'] = ""; 
				    }
				}
				
				// Contact persons
				$patient_contact_personds = ContactPersonMaster::getContactPersonsByIpids($ipids);
				$cps_array = array();
				$cps_details_array = array();
				foreach($patient_contact_personds as $k=>$cps){
				    $cps_details_array[$cps['ipid']][] = $cps;
				    $cps_array[$cps['ipid']][] = $cps;
				}
				
				
				$external_id = array();
				$patient_familydoc_id = array();
				$admission_periods  =array();
				$patient_details  =array();
				foreach($patients_arr as $pipid=>$pdata){
				    // EXPORT ID 
				    $external_id[$pipid] = ($pdata ['details'] ['clientid'] + $this->random_export_number).'-'.$pdata ['details'] ['epid_num'] ;
    				$patient_details[$pipid]['Export_id'] =  $external_id[$pipid] ;
    				$patient_details[$pipid]['EPID'] = $pdata ['details'] ['epid'];
    				
    				
				    // GENDER
				    if ( $pdata['details']['sex'] == "1") {
    				    $patient_details[$pipid]['gender'] = $page_lang['male'];
				    }
				    elseif ( $pdata['details']['sex'] == "2") {
    				    $patient_details[$pipid]['gender'] = $page_lang['female'];
				    } else {
    				    $patient_details[$pipid]['gender'] =  $page_lang['other'];
				    }
				    
				    
				    // AGE
				    $tod_date_patient = '';
				    if(array_key_exists($ipid, $patient_death_dates))
				    {
				        $tod_date_patient = $patient_death_dates[$pipid];
				    }
				    else
				    {
				        if(strtotime($period_end) >= strtotime('now'))
				        {
				    
				            $tod_date_patient = date("Y-m-d", time());
				        }
				        else
				        {
				            $tod_date_patient = date("Y-m-d", strtotime($period_end));
				        }
				    }
                    $patient_details[$pipid]['age']  = $PatientMaster_obj->GetAge($pdata['details']['birthd'], $tod_date_patient, true);
                    
                    // ZIP DENSITY
                    if (!empty($pdata['details']['zip'])){
                        $patient_details[$pipid]['zip_density'] = $page_lang[$zip_density[$pdata['details']['zip']]];
                    } else {
                        $patient_details[$pipid]['zip_density'] = "";
                    }                    
                    
                    // Familydoctor ids 
                    $patient_details[$pipid]['familydoc_id'] = $pdata['details']['familydoc_id'];
                    $patient_familydoc_id[] = $pdata['details']['familydoc_id'];
                    
                    $patient_details[$pipid]['caregiver_str'] = "";
                    
                    
                    // Contact persons Available
                    if(!empty($cps_array[$pipid])){
                        $patient_details[$pipid]['contact_perons'] = "Ja";
                    } else{
                        $patient_details[$pipid]['contact_perons'] = "Nein";
                    }
                    
                    // DGP PARTNERS
                    if( ! empty($dgp_first)){
                        $patient_details[$pipid]['first_dgp_partners'] = $dgp_first[$pipid]['partners'];
                    }
                    
                    // ACP
                    $patient_details[$pipid]['living_will'] = 'Nein';
                    $patient_details[$pipid]['healthcare_proxy'] = 'Nein';
                    $patient_details[$pipid]['care_orders'] = 'Nein';
                    
                    // ACTIVE DAYS IN PERIOD
//                     $patient_details[$pipid]['active_in_period'] = $pdata['real_active_days'];
                    

                    // ADMISSION FALLS - IN RAPORTED  PERIOD
                    $adm_substitute = 1;
                    foreach($pdata['active_periods'] as $period_identification => $period_details)
                    {
                    
                        $admission_periods[$pipid][$adm_substitute ]['start'] = $period_details['start'];
                        $admission_periods[$pipid][$adm_substitute ]['end'] = $period_details['end'];
                                                
                        if(in_array($period_details['end'],array_values($pdata['discharge']))){
                            $admission_periods[$pipid][$adm_substitute ]['valid_discharge_date'] = $period_details['end'];
                        } else{
                            $admission_periods[$pipid][$adm_substitute ]['valid_discharge_date'] = '';
                        }
//                         $admission_periods[$pipid][$adm_substitute ]['days'] = $PatientMaster_obj->getDaysInBetween($period_details['start'], $period_details['end'],false,"d.m.Y");
                        $admission_ids[$pipid][] = $adm_substitute ;
                        $adm_substitute++;
                        
                    }
                    // DISCHARGE DATES
                    $patient_details[$pipid]['discharge_dates_array'] = array_values($pdata['discharge']) ;
                    
                    
                    // hospital days
                    
                    // Hospiz days
                    
                    // Treatment days 
                    $patient_details[$pipid]['treatment_days'] = $pdata['treatment_days'];
				}
				
// 				dd($patient_details);
// 				dd($admission_periods, $patient_sapv_array);

				
				// get sapv data
				// sapv  days !!! SAPV DAYS ARE MANDATORY
				$patient_sapv_array = array();
				$patient_sapv_array = $SapvVerordnung_obj->get_patients_sapv_periods($ipids);
				// THIS ARRAY HOLDS ALL SAPV DATA
// 				dd($patient_sapv_array);
				//get only in admission 
				$sapv_in_admission_period  = array();
				$sapv_in_report_period  = array();
				
				foreach($patient_sapv_array as $sapv_ipid => $sapv_data)
				{
			 
				    
                    foreach($sapv_data as $sapv_id => $sv_period){
                        
                        foreach($sv_period['days'] as $sapv_day){
    				        if( in_array($sapv_day,$patient_details[$sapv_ipid]['treatment_days'] )){
    				            $patient_details[$sapv_ipid]['valid_sapv_days'][] = $sapv_day; 
    				        }
                        }
                        
                        if (Pms_CommonData::isintersected ( strtotime ( $periods[0]['start'] ), strtotime ( $periods[0]['end'] ), strtotime ( $sv_period['start'] ), strtotime (  $sv_period['end'] ) )) {
                            $sapv_in_report_period[$sapv_ipid][] = $sapv_id; 
                        }
                    }
                        
    				foreach($admission_periods[$sapv_ipid] as $admk=>$adm_data)
    				{
                        foreach($sapv_data as $sapv_id => $sv_period){
                            if (Pms_CommonData::isintersected ( strtotime ( $adm_data ['start'] ), strtotime ( $adm_data ['end'] ), strtotime ( $sv_period['start'] ), strtotime (  $sv_period['end'] ) )) {
                                $sapv_in_admission_period[$sapv_ipid][$adm_data ['start'].' - '.$adm_data ['end'] ][] = $sapv_id; 
                            }
                        }
    				} 
				}

				//dd($patient_details);
				// ACP
				$acp = new PatientAcp();
				$acp_data_patients = $acp->getByIpid($ipids);
				
				if(!empty($acp_data_patients))
				{
				    foreach($acp_data_patients as $ipid=>$acp_data)
				    {
				        foreach($acp_data as $k=>$block)
				        {
				            if($block['division_tab'] == "living_will"){
				
				                if($block['active'] == "yes"){
				                    $patient_details[$ipid]['living_will'] = 'Ja';
				                } else{
				                    $patient_details[$ipid]['living_will'] = 'Nein';
				                }
				
				            }
				            elseif($block['division_tab'] == "healthcare_proxy")
				            {
				                if($block['active'] == "yes"){
				                    $patient_details[$ipid]['healthcare_proxy'] = 'Ja';
				                } else{
				                    $patient_details[$ipid]['healthcare_proxy'] = 'Nein';
				                }
				
				            }
				            elseif($block['division_tab'] == "care_orders")
				            {
				                if($block['active'] == "yes"){
				                    $patient_details[$ipid]['care_orders'] = 'Ja';
				                } else{
				                    $patient_details[$ipid]['care_orders'] = 'Nein';
				                }
				
				            }
				        }
				    }
				}
				
				
				// Hausarzt, Facharzt, Pflegedienst, Sanitätsshaus, Apotheke, Physiotherapeut, sonstige Versorger
                $patients['caregiver'] = array();
                    
                    // get familydoctor details
                $client_familydoctor_array = FamilyDoctor::get_family_doctors_multiple($patient_familydoc_id);
                foreach ($client_familydoctor_array as $fam_id => $fdetails) {
                    $fam_doc_details[$fdetails['id']]['nice_name'] = trim($fdetails['title']) != "" ? trim($fdetails['title']) . " " : "";
                    $fam_doc_details[$fdetails['id']]['nice_name'] .= trim($fdetails['last_name']);
                    $fam_doc_details[$fdetails['id']]['nice_name'] .= trim($fdetails['first_name']) != "" ? (", " . trim($fdetails['first_name'])) : "";
                }
                
                foreach ($ipids as $ipid) {
                    $patient_details[$ipid]['caregiver']['family_doctor'][] = $fam_doc_details[$patient_details[$ipid]['familydoc_id']]['nice_name'];
                    if (strlen($fam_doc_details[$patient_details[$ipid]['familydoc_id']]['nice_name']) > 0) {
                        $patient_details[$ipid]['caregiver_str'] .= $fam_doc_details[$patient_details[$ipid]['familydoc_id']]['nice_name'] . ', ';
                    }
                }
                
                // Facharzt
                $patients['caregiver']['specialists'] = PatientSpecialists::get_patient_specialists($ipids, true);
                // dd($patient_specialists_array);
                foreach ($patients['caregiver']['specialists'] as $caregiver_id => $cg_data) {
                    $patient_details[$cg_data['ipid']]['caregiver']['specialists'][] = $cg_data['master']['practice'];
                    if (strlen($cg_data['master']['practice']) > 0) {
                        $patient_details[$cg_data['ipid']]['caregiver_str'] .= $cg_data['master']['practice'] . ', ';
                    }
                }
                
                // Pflegedienst
                $patients['caregiver']['pflegedienst'] = PatientPflegedienste::get_multiple_patient_pflegedienste($ipids);
                // dd($patients['caregiver']['pflegedienst']);
                foreach ($patients['caregiver']['pflegedienst']['results'] as $cr_ipid => $cg_data_arr) {
                    foreach ($cg_data_arr as $k => $cg_data) {
                        $patient_details[$cg_data['ipid']]['caregiver']['pflegedienst'][] = $cg_data['nursing'];
                        if (strlen($cg_data['nursing']) > 0) {
                            $patient_details[$cg_data['ipid']]['caregiver_str'] .= $cg_data['nursing'] . ', ';
                        }
                    }
                }
                
                // Sanitatsshaus
                $patients['caregiver']['supplies'] = PatientSupplies::get_patient_supplies($ipids, true);
                // dd($patients['caregiver']['supplies']);
                foreach ($patients['caregiver']['supplies'] as $caregiver_id => $cg_data) {
                    $patient_details[$cg_data['ipid']]['caregiver']['supplies'][] = $cg_data['master']['supplier'];
                    if (strlen($cg_data['master']['supplier']) > 0) {
                        $patient_details[$cg_data['ipid']]['caregiver_str'] .= $cg_data['master']['supplier'] . ', ';
                    }
                }
                // get Apotheke,
                $patients['caregiver']['pharmacy'] = PatientPharmacy::get_patients_pharmacy($ipids);
                // dd($patients['caregiver']['pharmacy']);
                foreach ($patients['caregiver']['pharmacy'] as $ph_ipid => $ph_data_array) {
                    foreach ($ph_data_array as $k => $ph_data) {
                        $patient_details[$ph_ipid]['caregiver']['pharmacy'][] = $ph_data['pharmacy'];
                        
                        if (strlen($ph_data['pharmacy']) > 0) {
                            $patient_details[$ph_ipid]['caregiver_str'] .= $ph_data['pharmacy'] . ', ';
                        }
                    }
                }
                
                // Physiotherapeut
                $patients['caregiver']['physiotherapeut'] = PatientPhysiotherapist::get_patient_physiotherapists($ipids, true);
                // dd($patients['caregiver']['physiotherapeut']);
                foreach ($patients['caregiver']['physiotherapeut'] as $caregiver_id => $cg_data) {
                    $patient_details[$cg_data['ipid']]['caregiver']['physiotherapeut'][] = $cg_data['master']['physiotherapist'];
                    
                    if (strlen($cg_data['master']['physiotherapist']) > 0) {
                        $patient_details[$cg_data['ipid']]['caregiver_str'] .= $cg_data['master']['physiotherapist'] . ', ';
                    }
                }
                
                // sonstige Versorger
                $patients['caregiver']['suppliers'] = PatientSuppliers::get_patients_suppliers($ipids);
                foreach ($patients['caregiver']['suppliers'] as $sph_ipid => $sh_data_array) {
                    foreach ($sh_data_array as $k => $sph_data) {
                        $patient_details[$sph_ipid]['caregiver']['supplier'][] = $sph_data['supplier'];
                        
                        if (strlen($sph_data['supplier']) > 0) {
                            $patient_details[$sph_ipid]['caregiver_str'] .= $sph_data['supplier'] . ', ';
                        }
                    }
                }
                
                // Location
                $patient_location_obj = new PatientLocation();
                $patients['caregiver']['locations'] = $patient_location_obj->get_valid_patients_locations($ipids, true);
                
                foreach ($patients['caregiver']['locations'] as $locipid => $loc_data_array) {
                    $locid = 0;
                    foreach ($loc_data_array as $k => $loc_data) {
                        $locid = substr($loc_data['location_id'], 0, 4);
                        
                        $cnt_id = "";
                        if ($locid == "8888") {
                            $cnt_id = substr($loc_data['location_id'], 4);
                            $patients['caregiver']['locations'][$locipid][$k]['master_location_name'] = 'bei Kontaktperson ' . $cnt_id . ' ' . $cps_details_array[$loc_data['ipid']][$cnt_id - 1]['cnt_last_name'] . ' ' . $cps_details_array[$loc_data['ipid']][$cnt_id - 1]['cnt_first_name'];
                        }
                        
                        if (strlen($patients['caregiver']['locations'][$locipid][$k]['master_location_name'])) {
                            $patient_details[$loc_data['ipid']]['locations'][$k] = $patients['caregiver']['locations'][$locipid][$k]['master_location_name'];
                        }
                    }
                }
                
                // PFLEGEGRAD
                $pms_q = Doctrine_Query::create()
                ->select("*")
                ->from('PatientMaintainanceStage')
                ->whereIn('ipid',$ipids)
                ->orderBy('fromdate, create_date asc');
                $pms_res= $pms_q->fetchArray();
                
                $pflegegrade_array = array();
                foreach($pms_res as $k => $pms_data){
                    if(strlen($pms_data['stage']) > 0){
                        $pflegegrade_array[$pms_data['ipid']][] = $pms_data;
                    }
                }

                // ECOG
 
                /* ----------------- Patient Details - Deleted visits ----------------------------------------- */
                $deleted_visits = Doctrine_Query::create()
                ->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
                ->from('PatientCourse')
                ->whereIn("ipid",$ipids)
                ->andWhere('wrong=1')
                ->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
                ->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('visit_koordination_form')) . "'" . ' OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("kvno_doctor_form")) . '" OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("kvno_nurse_form")) . '"  OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("wl_doctor_form")) . '"  OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("wl_nurse_form")) . '"   OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("bayern_doctorvisit")) . '"   OR tabname="' . addslashes(Pms_CommonData::aesEncrypt("contact_form")) . '"  ');
                $deleted_visits_array = $deleted_visits->fetchArray();
                 
                $del_visits =array();
                foreach($deleted_visits_array as $k_del_visit => $v_del_visit)
                {
                    $del_visits[$v_del_visit['tabname']][] = $v_del_visit['recordid'];
                }
                
                $kvnodoc = Doctrine_Query::create()
                ->select("*,kvno_ecog as ecog,start_date as date")
                ->from('KvnoDoctor')
                ->whereIn("ipid", $ipids)
                ->andWhere('isdelete = "0"');
                if(!empty($del_visits['kvno_doctor_form'])){
                    $kvnodoc->andWhereNotIn('id',$del_visits['kvno_doctor_form']);
                }
                $kvnodoc ->orderBy('start_date ASC');
                $kvnodocarray = $kvnodoc->fetchArray();
                
                foreach($kvnodocarray as $k=>$vdata){
                    $visits[$vdata['ipid']]['kd'.$vdata['id']] = $vdata;
                }
                
                
                $bay_q = Doctrine_Query::create()
                ->select("*,start_date as date")
                ->from('BayernDoctorVisit')
                ->whereIn("ipid",$ipids);
                if( !empty($del_visits['bayern_doctorvisit'])){
                    $bay_q ->andWhereNotIn('id',$del_visits['bayern_doctorvisit']);
                }
                $bay_q ->orderBy('start_date ASC');
                $bay_arr = $bay_q->fetchArray();
                
                foreach($bay_arr as $k=>$vdata){
                    $visits[$vdata['ipid']]['bay'.$vdata['id']] = $vdata;
                }
                
                $cf_q = Doctrine_Query::create()
                ->select("*")
                ->from('ContactForms')
                ->whereIn("ipid",$ipids)
                ->andWhere('isdelete = "0"');
                if( !empty($del_visits['contact_form'])){
                    $cf_q->andWhereNotIn('id',$del_visits['contact_form']);
                }
                $cf_q->orderBy('start_date ASC');
                $cf_arr = $cf_q->fetchArray();
                
                foreach($cf_arr as $k=>$vdata){
                    $visits[$vdata['ipid']]['cf'.$vdata['id']] = $vdata;
                }
                
                $ecog_array = array();
                foreach($ipids as $ipid){
                    foreach( $visits[$ipid] as $kv=>$values ){
                        $new[$ipid][$values['date']] = $values['ecog'];
                    }
                    $ecog_first[$ipid] = reset($new[$ipid]);
                    $ecog_array[$ipid]['first'] = reset($new[$ipid]);
                
                    $ecog_last[$ipid] = end($new[$ipid]);
                    $ecog_array[$ipid]['last'] = end($new[$ipid]);
                }
                
                
                // 
				// create all CSV files that need to be exported -  export a zip
				$allcsvs = array();

				
				$csv1 = array(); // pateitn details 
				$allcsvs[1][0] = $csv1[0] = array(
    				'PAT_ID',//	=> Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
    				'Export_date',//	=> 	date of export
    				'Geschlecht',//		=> gender with the values above
    				'Alter',//		=>  AGE numeric
    				'Bevölkerungsdichte',//		=> you will get a excel sheet which maps all ZIP codes to the density of living. We just show how dense the area of the patient is with the 3 above values.
    				'Versorgung bei Übernahme - Ambulant -',// 	=> 	Values from the register admission form
    				'Angehörige / Vertrauensperson',//		=> contact person available YES / NO
    				'Beteiligte Dienste',//		=>  name all caregiver boxes filled in Stammdaten
    				'Patientenverfügung',//	=> 	take from ACP box
    				'Vollmacht',//	=> 	take from ACP box
    				'Betreuungsurkunde',//	=> 	take from ACP box
    				'Pflegegrad (Aufnahme)',//		=> Pflegegrad on admission
    				'Pflegegrad (Entlassung)',//		=> Pflegegrad on discharge
    				'Aufenthaltsort (Aufnahme)',//		=> first location
    				'Aufenthaltsort (Entlassung)',//		=> last location
    				'ECOG (erster Wert)',//		=> First ECOG
    				'ECOG(letzter Wert)',//	=> 	last ECOG
    				'Versorgungstage mit aktiver SAPV',//		=>  numeric value of SAPV days in report period.
				);
				
				
                foreach ($ipids as $ipid) {
                    $patient_details[$ipid]['first_pflegegrade'] = "";
                    if(!empty($pflegegrade_array[$ipid])){
                        $patient_details[$ipid]['first_pflegegrade'] = $pflegegrade_array[$ipid][0]['stage'];

                        $last_pflegegrade[$ipid] =  end($pflegegrade_array[$ipid]);  
                        $patient_details[$ipid]['last_pflegegrade'] = $last_pflegegrade[$ipid]['stage'];
                        
                    }
                    
                    
                    $allcsvs[1][] = $csv1[] = array(
                        $external_id[$ipid], // 'PAT_ID',// => Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
                        $export_date, // 'Export_date',// => date of export
                        $patient_details[$ipid]['gender'], // 'Geschlecht',// => gender with the values above
                        $patient_details[$ipid]['age'], // 'Alter',// => AGE numeric
                        $patient_details[$ipid]['zip_density'], // 'Bevölkerungsdichte',// => you will get a excel sheet which maps all ZIP codes to the density of living. We just show how dense the area of the patient is with the 3 above values.
                        $patient_details[$ipid]['first_dgp_partners'],//Versorgung bei Übernahme - Ambulant - ', // => Values from the register admission form
                        $patient_details[$ipid]['contact_perons'], //'Angehörige / Vertrauensperson - ', // => contact person available YES / NO
                        $patient_details[$ipid]['caregiver_str'],//'Beteiligte Dienste', // => name all caregiver boxes filled in Stammdaten
                        $patient_details[$ipid]['living_will'],//'Patientenverfügung', // => take from ACP box
                        $patient_details[$ipid]['healthcare_proxy'],//'Vollmacht', // => take from ACP box
                        $patient_details[$ipid]['care_orders'],//Betreuungsurkunde', // => take from ACP box
                        $patient_details[$ipid]['first_pflegegrade'],//'Pflegegrad (Aufnahme)', // => Pflegegrad on admission
                        $patient_details[$ipid]['last_pflegegrade'],//'Pflegegrad (Entlassung)', // => Pflegegrad on discharge
                        $patient_details[$ipid]['locations'][0],//'Aufenthaltsort (Aufnahme)', // => first location
                        end($patient_details[$ipid]['locations']),//'Aufenthaltsort (Entlassung)', // => last location
                        $ecog_array[$ipid]['first'],//'ECOG (erster Wert)', // => First ECOG
                        $ecog_array[$ipid]['last'] ,//'ECOG(letzter Wert) ', // => last ECOG
                        count($patient_details[$ipid]['valid_sapv_days'])//'Versorgungstage mit aktiver SAPV !!!!!!!!!!!!!!!!!!!'// => numeric value of SAPV days in report period.
                    );
				}
				
// 				dd($allcsvs); 
				$csv2 = array(); // Symptomatic details
				
				$allcsvs[2][0] = $csv2[0] = array(
    				'Pat_ID',//	=> Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
    				'Symptom',//	=> 	symptom type (one line per symptom)
    				'Ausprägung',// (Aufnahme)	=> 	first value
    				'Ausprägung'// (Entlassung)	=> 	last value
				);
				
				
                // get symptomatic data 				
				$Symptomatology_obj = new Symptomatology();
				$SymptomatologyValues_obj = new SymptomatologyValues();
				$system_system_details = $SymptomatologyValues_obj->getSymptpomatologyValues('1');

				
				$symp_period = array();
				foreach($admission_periods as $sipid => $adms_periods)
				{
				    foreach($adms_periods as $ak=>$aperiod)
				    {
				        //$symp_period[$sipid][$ak][$aperiod['start'].' - '.$ak][] = $Symptomatology_obj->getPatientSymptpomatologyFirstAdm($sipid,1,$aperiod['start']);
				        //$symp_period[$sipid][$ak][$aperiod['end'].' - '.$ak][] = $Symptomatology_obj->getPatientSymptpomatologyLastEnteredAdm($sipid,1,$aperiod['end']);
				        
				        $symp_period[$sipid][$ak]['adm'] = $Symptomatology_obj->getPatientSymptpomatologyFirstAdm($sipid,1,$aperiod['start']);
				        $symp_period[$sipid][$ak]['dis'] = $Symptomatology_obj->getPatientSymptpomatologyLastEnteredAdm($sipid,1,$aperiod['end']);
				    }
				}
				
				
				$dgp_symptom_mapping = array(
				    "1"=>	'kein',
				    "2"=>	'leicht',
				    "3"=>	'mittel',
				    "4"=>	'stark',
				);
				
				$symptom_mapping = array(
				    "0"=>	'kein',
				    
				    "1"=>	'leicht',
				    "2"=>	'leicht',
				    "3"=>	'leicht',
				    "4"=>	'leicht',
				    
				    "5"=>	'mittel',
				    "6"=>	'mittel',
				    "7"=>	'mittel',
				    
				    "8"=>	'stark',
				    "9"=>	'stark',
				    "10"=>	'stark'
				);
				
                foreach ($ipids as $ipid) {
                    
                    if( ! empty($dgp_arr[$ipid]['symptoms']['first']) ||  !empty($dgp_arr[$ipid]['symptoms']['last'])  ){
                        
                        foreach($dgp_arr[$ipid]['symptoms']['first'] as $sym_id=>$sym_det){
                        
                            if( (strlen($sym_det['value']) > 0 || strlen($dgp_arr[$ipid]['symptoms']['last'][$sym_id]['value']) > 0)  
                                && (strlen($dgp_symptom_mapping[$sym_det['value']]) > 0  || strlen($dgp_symptom_mapping[$dgp_arr[$ipid]['symptoms']['last'][$sym_id]['value']]) > 0 )
                                
                                )
                                $allcsvs[2][] = $csv2[] = array(
                                    $external_id[$ipid], // PAT_ID
                                    //utf8_encode($sym_val_array['description']), // 'Symptom',// => symptom type (one line per symptom)
                                    //$sym_val_array['description'], // 'Symptom',// => symptom type (one line per symptom)
                                    $text_arrays['symptoms'][$sym_id]['label'], // 'Symptom',// => symptom type (one line per symptom)
                                    $dgp_symptom_mapping[$sym_det['value']], // 'Ausprägung',// (Aufnahme) => first value
                                    $dgp_symptom_mapping[$dgp_arr[$ipid]['symptoms']['last'][$sym_id]['value']] // 'Ausprägung'// (Entlassung) => last value
                                );
                        }
                    }
                    else if (! empty($symp_period[$ipid])) {
                        
                        foreach ($symp_period[$ipid] as $adm_per_k => $values_per_adm) {
                            
                            foreach ($values_per_adm['adm'] as $sym_key => $sym_val_array) {
                                if( strlen($sym_val_array['value'] ) > 0 || strlen($values_per_adm['dis'][$sym_key]['value']) > 0 ){
                                    
                                $allcsvs[2][] = $csv2[] = array(
                                    $external_id[$ipid], // PAT_ID
                                    //utf8_encode($sym_val_array['description']), // 'Symptom',// => symptom type (one line per symptom)
                                    $sym_val_array['description'], // 'Symptom',// => symptom type (one line per symptom)
                                    $symptom_mapping[$sym_val_array['value']], // 'Ausprägung',// (Aufnahme) => first value
                                    $symptom_mapping[$values_per_adm['dis'][$sym_key]['value']] // 'Ausprägung'// (Entlassung) => last value
                                );
                                }
                            }
                        }
                        
                    }
                }
				
                
				//dd($csv2);
				
                
                
				$csv3 = array(); // Diagnosen
				$allcsvs[3][0] =$csv3[0] = array(
                    'PAT_ID',       // =>	Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
                    'ICD10',        //  =>	ICD value of diagnosis
                    'Bezeichnung',  // => 	ICD text
                    'Aufn Diagn',   //  =>	 leave blank
                    'Hauptdiagnose',//	 => HD yes / no
                    'Nebendiagnose' //	 => ND yes / no
                );
				
				// get diagnosis data
				$PatientDiagnosis_obj = new PatientDiagnosis();
				$diagnosis_arr = $PatientDiagnosis_obj->get_multiple_finaldata($ipids);
				
				
				$diags = array();
				foreach($diagnosis_arr as $diag)
				{
				    $diags[$diag['ipid']][]=$diag;
				}
				
				$dtypes = new DiagnosisType();
				$digtypes_arr = $dtypes->get_client_diagnosistypes($clientid);
				
				foreach($digtypes_arr as $k=>$d_details)
				{
				    $digtypes[$d_details['abbrevation']] = $d_details['id'];
				}
				
				$diagnosis_details = array();
				foreach($ipids as $ipid){
				    $dl = 0;
				    foreach ($diags[$ipid] as $diag) {
				        if ($diag['diagnosis_type_id'] == $digtypes['HD']) {
				            $diag['is_main_diagnosis'] = "Ja";
				        }  else{
				            $diag['is_main_diagnosis'] = "Nein";
				        }
				        if ($diag['diagnosis_type_id'] == $digtypes['ND']) {
				            $diag['is_side_diagnosis'] = "Ja";
				        } else{
				            $diag['is_side_diagnosis'] = "Nein";
				        }
 
				        $allcsvs[3][] =  $csv3[] = array(
				            $external_id[$ipid], //PAT_ID
				            $diag['icdnumber'], //ICD10
				            $diag['diagnosis'], //Bezeichnung
				            "", //Aufn Diagn
				            $diag['is_main_diagnosis'],//Hauptdiagnose
				            $diag['is_side_diagnosis']//Nebendiagnose
				        );
				        
				    }
				}
				
// 				dd($csv3);
				$csv4 = array(); // SAPV 
				$allcsvs[4][0] =$csv4[0] = array(
				    'PAT_ID',       //=> String	  => Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
				    'Verordnung_ID',//=> String	  => ID of verordnung
				    'Verordnung',   //=> SAPV Erstverordnung, SAPV Folgeverordnung	=>	print one of the 2 above values related to Verordnung
				    'Von',          //=> Datum    => from
				    'Bis',          //=> Datum    => till
				    'Genehmigt Von',//=> Datum    => "genehmigt" from
				    'Genehmigt bis',//=> Datum    => "genehmigt" till
				    'Voll',         //=> Ja, Nein => VV yes / no
				    'Teil',         //=> Ja, Nein => TV yes / no
				    'Beratung',     //=> Ja, Nein => BE yes / no
				    'Koordination', //=> Ja,Nein  => KO yes / no
				);
				
				foreach($ipids as $ipid){
				    if(!empty($sapv_in_report_period[$ipid])){
				        foreach($sapv_in_report_period[$ipid] as $sapv_id){
				            if(!empty($patient_sapv_array[$ipid][$sapv_id])){
				                
				                //Verordnung
				                if ($patient_sapv_array[$ipid][$sapv_id]['sapv_order'] == "1"){
				                    $patient_sapv_array[$ipid][$sapv_id]['sapv_order_text'] = "SAPV Erstverordnung";
				                }		                
				                
				                if ($patient_sapv_array[$ipid][$sapv_id]['sapv_order'] == "2"){
				                    $patient_sapv_array[$ipid][$sapv_id]['sapv_order_text'] = "SAPV Folgeverordnung";
				                }		                
				                
				                // VOLL
				                if (in_array("4",$patient_sapv_array[$ipid][$sapv_id]['types_arr'])){
				                    $patient_sapv_array[$ipid][$sapv_id]['vv'] = "Ja";
				                } else{
				                    $patient_sapv_array[$ipid][$sapv_id]['vv'] = "Nein";
				                }
				                
				                // Teil
				                if (in_array("3",$patient_sapv_array[$ipid][$sapv_id]['types_arr'])){
				                    $patient_sapv_array[$ipid][$sapv_id]['tv'] = "Ja";
				                } else{
				                    $patient_sapv_array[$ipid][$sapv_id]['tv'] = "Nein";
				                }
				                
				                // Koordination
				                if (in_array("2",$patient_sapv_array[$ipid][$sapv_id]['types_arr'])){
				                    $patient_sapv_array[$ipid][$sapv_id]['ko'] = "Ja";
				                } else{
				                    $patient_sapv_array[$ipid][$sapv_id]['ko'] = "Nein";
				                }
				                // Beratung
				                if (in_array("1",$patient_sapv_array[$ipid][$sapv_id]['types_arr'])){
				                    $patient_sapv_array[$ipid][$sapv_id]['be'] = "Ja";
				                } else{
				                    $patient_sapv_array[$ipid][$sapv_id]['be'] = "Nein";
				                }
				                
				               $allcsvs[4][] = $csv4[] = array(
				                    $external_id[$ipid], // PAT_ID
				                    $sapv_id,// Verordnung_ID
				                    $patient_sapv_array[$ipid][$sapv_id]['sapv_order_text'], // Verordnung 
				                    $patient_sapv_array[$ipid][$sapv_id]['start'],// Von
				                    $patient_sapv_array[$ipid][$sapv_id]['end'],// Bis
				                    $patient_sapv_array[$ipid][$sapv_id]['regulation_start'],// Genehmigt Von 
				                    $patient_sapv_array[$ipid][$sapv_id]['regulation_end'],// Genehmigt bis
				                    $patient_sapv_array[$ipid][$sapv_id]['vv'], // Voll
				                    $patient_sapv_array[$ipid][$sapv_id]['tv'], // Teil
				                    $patient_sapv_array[$ipid][$sapv_id]['be'], // Koordination
				                    $patient_sapv_array[$ipid][$sapv_id]['ko'], // Beratung
				                    
				                );
				            }
				        }
				    }
				}
				
				$csv5 = array();// Fall
				
				$allcsvs[5][0] =$csv5[0] = array(
    				'PAT_ID',//	=> String =>Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
    				'Verordnung ID',//=>	String=>		all Ids of Verordnung used in this FALL
    				'Von',//	=>Datum	=>	Fall from
    				'Bis',//	=>Datum	=>	FALL till
    				'Entlassungsgrund',//	=>String	=>	discharge reason
    				'Sterbeort',//	=>String	=>	IF discharge = dead , show location type here
    				'Zufriendenheit',//	=>sehr gut, gut, mittel,schlecht,sehr schlecht=>		"Zufriedenheit" from register discharge
				);
				
				
				foreach($ipids as $ipid){
    				foreach($admission_periods[$ipid] as $admk=>$adm_data)
    				{
			           $sapvperiod_ids = implode(", ", $sapv_in_admission_period[$ipid][$adm_data ['start'].' - '.$adm_data ['end'] ]);
			           
                        if($patients_discharge_data[$ipid][$adm_data ['end']]['died']  == "1"){
                            $death_location[$ipid][$adm_data ['end']] = $patients_discharge_data[$ipid][$adm_data ['end']]['type_location_of_death']; 
                        } else{
                            $death_location[$ipid][$adm_data ['end']] = ""; 
                        }    

			             if(!empty($kvlast_data[$ipid])){
                            $kv_last[$ipid] = end($kvlast_data[$ipid]);
                            $zufriedenheit_mit  = $text_arrays['zufriedenheit_mit'][$kv_last[$ipid]['zufriedenheit_mit']];
			             } else{
                            $zufriedenheit_mit = $kv_last[$ipid]['zufriedenheit_mit'];
			             }
			             
			            $allcsvs[5][] = $csv5[] = array(
			               $external_id[$ipid], // PAT_ID
			               $sapvperiod_ids,// Verordnung_ID
			               $adm_data ['start'],// Von
			               $adm_data ['valid_discharge_date'],// Bis
			               $patients_discharge_data[$ipid][$adm_data ['end'] ]['discharge_method_str'],// 'Entlassungsgrund !!!!!!!!!!!!',//	=>String	=>	discharge reason
			               $death_location[$ipid][$adm_data ['end']],//'Sterbeort !!!!!!!!!!!!',//	=>String	=>	IF discharge = dead , show location type here
				           $zufriedenheit_mit//'Zufriendenheit !!!!!!!!!!!!',//	=>sehr gut, gut, mittel,schlecht,sehr schlecht=>		"Zufriedenheit" from register discharge
			           );
    				}
				}
				

				//get doctor and nurse users
				//get all related users details
				$master_groups_first = array('4', '5');
				$usergroups = new Usergroup();
				$user = new User();
				$client_user_groups_first = $usergroups->getUserGroups($master_groups_first);
					
				foreach($client_user_groups_first as $k_group_f => $v_group_f)
				{
				    $master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
				    $group2mastergroup[$v_group_f['id']] = $v_group_f['groupmaster']; 
				}
					
				$client_users = $user->getClientsUsers($clientid);
					
				$nurse_users = array();
				$doctor_users = array();
				foreach($client_users as $k_cuser_det => $v_cuser_det)
				{
				    $master_user_details[$v_cuser_det['id']] = $v_cuser_det;
				    if(in_array($v_cuser_det['groupid'], $master2client['5']))
				    {
				        $nurse_users[] = $v_cuser_det['id'];
				    }
				    else if(in_array($v_cuser_det['groupid'], $master2client['4']))
				    {
				        $doctor_users[] = $v_cuser_det['id'];
				    }
				    $user2group[$v_cuser_det['id']] = $v_cuser_det['groupid'];
				}
					
				
				
				//Get deleted contact froms from patient course
				$deleted_cf = Doctrine_Query::create()
				->select("*,AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type, AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientCourse')
				->where('wrong=1')
				->andWhereIn('ipid', $ipids)
				->andWhere('course_type="' . addslashes(Pms_CommonData::aesEncrypt("F")) . '"')
				->andWhere("tabname='" . addslashes(Pms_CommonData::aesEncrypt('contact_form')) . "'");
				$deleted_cf_array = $deleted_cf->fetchArray();
				
				
				$excluded_cf_ids = array();
				foreach($deleted_cf_array as $k_dcf => $v_dcf)
				{
				    $excluded_cf_ids[] = $v_dcf['recordid'];
				}
				
				//get cf in period exclude deleted
				$cf = new ContactForms();
				$contactforms  = $cf->get_multiple_contact_form_by_periods($ipids, $periods, $excluded_cf_ids,true);
				
				$cf_ipids = array();
				$contact_forms_ids = array();
				
				foreach($contactforms as $ipids=>$cfss){
				    foreach($cfss as $k=>$cfdata){
				        $contact_forms_ids[] = $cfdata['id']; 
				        $cf_ipids[] = $cfdata['ipid']; 
				    }
				}

				
				$FormBlockAdditionalUsers_obj = new FormBlockAdditionalUsers();
				$block_au_data = $FormBlockAdditionalUsers_obj->getPatientFormBlockAdditionalUsers($cf_ipids, $contact_forms_ids, false, true);
				
				$involved_groups = array();
				foreach($block_au_data as $cfid=>$block_au_array ){
				    foreach($block_au_array as $k=>$block_au){
    				    $involved_groups[$block_au['contact_form_id']][] = $user2group[$block_au['additional_user']]; 
				    }
				}
				
				$FormBlockDrivetimedoc_obj = new FormBlockDrivetimedoc();
				$driving_array = $FormBlockDrivetimedoc_obj->get_multiple_block_drivetimedoc($cf_ipids, $contact_forms_ids, false, true);
				
				// get form types
				$FormTypes_obj = new FormTypes();
				$client_forms = $FormTypes_obj->get_form_types($clientid);
				
// 				dd($client_forms);
				foreach($client_forms as $ft_id=>$ftdata){
				    $contact_form_name[$ftdata['id']] = $ftdata['name'];
				}
				
				$csv6 = array();// Contact froms
				$allcsvs[6][0] =$csv6[0] = array(
        			'PAT_ID',//	=>	String		=>Patient id from ISPC, DONT SHOW THE EPID PREFIX!!!! Add a unique value there for every client instead of EPID PROEFIX
        			'Kontakt_ID',//	=>	String	=>	name of contact form
        			'Kontakt Datum',//	=>	Datum	=>	contact form date
        			'Uhrzeit',//	=>	Uhrzeit	=>	contact form time
        			'Art',//	=>	String	=>	name of contact form
        			'Dauer',// 	=>	Numerisch	=>	length in minutes
        			'Gefahrene Kilometer',//	=>	Numerisch	=>	driven km
        			'Fahrtdauer',//	=>	Numerisch	=>	driving time
        			'Arzt',//	=>	Ja, Nein	=>	User with grouptype doctor involved?
        			'Pflege',//	=>	Ja, Nein	=>	User with grouptype "PFLEGE" involved?
        			'Sonstige - Profession',//	=>	Ja, Nein	=>	User with any other grouptype involved
        			'>1 Profession',// 	=>	Jan,Nein	=>	mehr than one usergroup involved
        			'Kontakt zwischen 22:00 - 06:00',//	=>	Ja, Nein	=>	did the contact from  touch the time between 22:00 and 06:00
				);
				

				if (! empty($contactforms)) {
                    foreach ($contactforms as $cf_ipid => $cfdata_array) {
                        $current = array();
                        foreach ($cfdata_array as $cf => $cfdata) {
                            
                            if (! empty($driving_array[$cfdata['id']]['fahrtstreke_km1'])) {
                                $current['distance_km'] = $driving_array[$cfdata['id']]['fahrtstreke_km1'];
                            } else {
                                $current['distance_km'] = $cfdata['fahrtstreke_km'];
                            }
                            
                            if (! empty($driving_array[$cfdata['id']]['fahrtzeit'])) {
                                $current['driving_time'] = $driving_array[$cfdata['id']]['fahrtzeit'];
                            } else {
                                $current['driving_time'] = $cfdata['fahrtzeit'];
                            }
                            
                            if (empty($involved_groups[$cfdata['id']])) { // NO DATA IN Additional users block
                                $involved_groups[$cfdata['id']][] = $user2group[$cfdata['create_user']];
                            }
                            
                            $current['involved_doctor_group'] = "Nein";
                            $current['involved_nurse_group'] = "Nein";
                            $current['involved_other_group'] = "Nein";
                            $current['involved_multiple_groups'] = "Nein"; // If more then one group was involved in this visit
                            
                            if (! empty($involved_groups[$cfdata['id']])) {
                                // DOCTOR GROUPS
                                if (is_array(array_intersect($involved_groups[$cfdata['id']], $master2client['4'])) && ! empty(array_intersect($involved_groups[$cfdata['id']], $master2client['4']))) {
                                    $current['involved_doctor_group'] = "Ja";
                                }
                                
                                // NURSE GROUPS
                                if (is_array(array_intersect($involved_groups[$cfdata['id']], $master2client['5'])) && ! empty(array_intersect($involved_groups[$cfdata['id']], $master2client['5']))) {
                                    $current['involved_nurse_group'] = "Ja";
                                }
                                // OTHER GROUPS
                                if (is_array(array_diff($involved_groups[$cfdata['id']], $master2client['4'], $master2client['5'])) && ! empty(array_diff($involved_groups[$cfdata['id']], $master2client['4'], $master2client['5']))) {
                                    $current['involved_other_group'] = "Ja";
                                }
                                
                                // >1 Profession
                                if (count(array_unique($involved_groups[$cfdata['id']])) > 1) {
                                    $current['involved_multiple_groups'] = "Ja";
                                }
                            }
                            
                            if(strtotime(date("d.m.Y",strtotime($cfdata['start_date']))) == strtotime(date("d.m.Y",strtotime($cfdata['end_date'])))){
                                if(   date("H",strtotime($cfdata['start_date'])) <= "22" && date("H",strtotime($cfdata['end_date'])) >= "22" 
                                   || date("H",strtotime($cfdata['start_date'])) >= "22" && date("H",strtotime($cfdata['end_date'])) >= "22"
                                   || date("H",strtotime($cfdata['start_date'])) < "6" && date("H",strtotime($cfdata['end_date'])) >= "6"
                                   || date("H",strtotime($cfdata['start_date'])) < "6" && date("H",strtotime($cfdata['end_date'])) <= "6" 
                                    
                                    )
                                {
                                    $current['between2200_0600'] ="Ja";
                                } else{
                                    $current['between2200_0600'] ="Nein";
                                }
                                
                            } elseif(strtotime(date("d.m.Y",strtotime($cfdata['start_date']))) < strtotime(date("d.m.Y",strtotime($cfdata['end_date'])))){
                                if(   date("H",strtotime($cfdata['start_date'])) <= "22" && (date("H",strtotime($cfdata['end_date'])) >= "22" || date("H",strtotime($cfdata['end_date'])) < "6" )
                                    || date("H",strtotime($cfdata['start_date'])) >= "22" && (date("H",strtotime($cfdata['end_date'])) >= "22" || date("H",strtotime($cfdata['end_date'])) < "6" )
                                
                                )
                                {
                                    $current['between2200_0600'] ="Ja";
                                }else{
                                    $current['between2200_0600'] ="Nein";
                                }
                                
                            }
                            $allcsvs[6][] = $csv6[] = array(
                                $external_id[$cfdata['ipid']], // 'PAT_ID'
                                $contact_form_name[$cfdata['form_type']], // 'Kontakt_ID',
                                date('d.m.Y', strtotime($cfdata['start_date'])), // 'Kontakt Datum'
                                date('H:i', strtotime($cfdata['start_date'])).' - '.date('H:i', strtotime($cfdata['end_date'])), // 'Uhrzeit'
                                $contact_form_name[$cfdata['form_type']], // 'Art'
                                $cfdata['visit_duration'], // 'Dauer'
                                $current['distance_km'], // 'Gefahrene Kilometer'
                                $current['driving_time'], // 'Fahrtdauer',// => Numerisch => driving time
                                $current['involved_doctor_group'], // 'Arzt',// => Ja, Nein => User with grouptype doctor involved?
                                $current['involved_nurse_group'], // 'Pflege',// => Ja, Nein => User with grouptype "PFLEGE" involved?
                                $current['involved_other_group'], // 'Sonstige - Profession',// => Ja, Nein => User with any other grouptype involved
                                $current['involved_multiple_groups'], // '>1 Profession',// => Jan,Nein => mehr than one usergroup involved
                                $current['between2200_0600'] // 'Kontakt zwischen 22:00 - 06:00',// => Ja, Nein => did the contact from touch the time between 22:00 and 06:00
                            );
                        }
                    }
                }

                // create csv files and download zip 
                 
                $file_name_array =array(
                    'file-1' => "1.Basisdaten",
                    'file-2' => "2.Symptome",
                    'file-3' => "3.Diagnosen",
                    'file-4' => "4.Verordnung",
                    'file-5' => "5.Fälle",
                    'file-6' => "6.Kontakte",
                );
                
                
                // create your zip file
                $zipname = 'VSTN_'.$export_date__dmy.'.zip';
                $zip = new ZipArchive;
                $zip->open($zipname, ZipArchive::CREATE);
                
                // loop to create 3 csv files
                for ($i = 1; $i < 7; $i++) {
                
                    // create a temporary file
                    $fd = fopen('php://temp/maxmemory:1048576', 'w');
                    if (false === $fd) {
                        die('Failed to create temporary file');
                    }
                
                    // write the data to csv
//                     fputcsv($fd, $headers);
                    foreach($allcsvs[$i] as $k=>$record) {
                        fputcsv($fd, $record, ";");
                    }
                
                    // return to the start of the stream
                    rewind($fd);
                
                    // add the in-memory file to the archive, giving a name
                    $zip->addFromString($file_name_array['file-'.$i].'.csv', stream_get_contents($fd) );
                    //close the file
                    fclose($fd);
                }
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
		}
	}
	
	
	public function getpatientsAction() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		$clientid = $logininfo->clientid;
		
		$quarter = explode ( '-', $_REQUEST ['quarter'] );
		$q_month = trim ( $quarter [1] );
		$q_year = trim ( $quarter [0] );

		$period_start = date ( "Y-m-d", strtotime ( $_REQUEST ['period_start'] ) ); // ?????? if not empty
		$period_end = date ( "Y-m-d", strtotime ( $_REQUEST ['period_end'] ) ); // ?????? if not empty
		
		$type = $_REQUEST ['type'];
		
		$select = "AES_DECRYPT(p.last_name,'" . Zend_Registry::get ( 'salt' ) . "') as last_name,AES_DECRYPT(p.first_name,'" . Zend_Registry::get ( 'salt' ) . "') as first_name,convert(AES_DECRYPT(p.zip,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as zip,convert(AES_DECRYPT(p.street1,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as street1,convert(AES_DECRYPT(p.city,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as city,convert(AES_DECRYPT(p.phone,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as phone, convert(AES_DECRYPT(p.sex,'" . Zend_Registry::get ( 'salt' ) . "') using latin1) as sex,";
		
		$periods = array (
				'0' => array (
						'start' => $period_start,
						'end' => $period_end 
				) 
		);
		if (strlen ( $_REQUEST ['length'] )) {
			$limit = $_REQUEST ['length'];
		} else {
			$limit = "100";
		}
		
		$offset = $_REQUEST ['start'];
		
		$limit_options = array (
				"limit" => $limit,
				'offset' => $offset 
		);
		
		$ipids = array();
		 
		if(empty($ipids)) {
		    $ipids = null;
		}

		$patients_arr = Pms_CommonData::patients_days ( array (
					'ipids' => $ipids,
					'periods' => $periods,
					'client' => $clientid 
			), $select );// TODO -498 - remove standby 02.09.2016

		$resulted_ipids = array_keys ( $patients_arr );
		
		$full_count = count ( $resulted_ipids );
		
		// get health insurance data for all ipids
		$healthins = new PatientHealthInsurance ();
		$pathealthins = $healthins->get_multiple_patient_healthinsurance ( $resulted_ipids, true );
		foreach($pathealthins as $ph_ipid=>$phdata){
			$insurance_option[$ph_ipid] = "";
			if($phdata ['privatepatient'] == "1" )
			{
				$insurance_option[$ph_ipid] = "Privatpatient";
			}
			elseif($phdata ['direct_billing'] == "1" )
			{
				$insurance_option[$ph_ipid]  = "Direktabrechnung";
			}
			elseif($phdata ['bg_patient'] == "1" )
			{
				$insurance_option[$ph_ipid]  = "BG Patient";
			} else {
				$insurance_option[$ph_ipid]  = "Keiner";
			}
		}
		
		$row_id = 0;
		$link = "";
		$resulted_data = array ();
		$discharge_date = "";
		$admission_date = "";
		$external_id = 0 ; 
		if (! empty ( $patients_arr )) {
			foreach ( $patients_arr as $ipid => $mdata ) {
				$link = '%s ';
				
				$external_id = ($mdata ['details'] ['clientid'] + $this->random_export_number).'-'.$mdata ['details'] ['epid_num'] ;
				$admission_date = end ( $mdata ['admission_days'] );
				if (! empty ( $mdata ['discharge'] )) {
					$discharge_date = end ( $mdata ['discharge'] );
				} else {
					$discharge_date = "";
				}
				
				$resulted_data [$row_id] ['epid_number'] = sprintf ( $link, $mdata ['details'] ['epid'] );
				$resulted_data [$row_id] ['export_id'] = $external_id;
				$resulted_data [$row_id] ['admission_date_full'] = sprintf ( $link, date ( 'Y-m-d', strtotime ( $admission_date ) ) );
				if (strlen ( $discharge_date ) > 0) {
					$resulted_data [$row_id] ['discharge_date_full'] = sprintf ( $link, date ( 'Y-m-d', strtotime ( $discharge_date ) ) );
				} else {
					$resulted_data [$row_id] ['discharge_date_full'] = sprintf ( $link, "" );
				}
				$resulted_data [$row_id] ['dob_full'] = sprintf ( $link, $mdata ['details'] ['birthd'] );
				$resulted_data [$row_id] ['select_patient'] = '<input type="checkbox" name="patients[]" class="patients_select" value="' . $mdata ['details'] ['ipid'] . '"/>'; // CHANGE !!!!!!!!!!!!! add what is needded
				$resulted_data [$row_id] ['epid'] = sprintf ( $link, $mdata ['details'] ['epid'] );
				$resulted_data [$row_id] ['first_name'] = sprintf ( $link, $mdata ['details'] ['first_name'] );
				$resulted_data [$row_id] ['last_name'] = sprintf ( $link, $mdata ['details'] ['last_name'] );
				if ($mdata ['details'] ['birthd'] != "0000-00-00") {
					$resulted_data [$row_id] ['birthd'] = sprintf ( $link, date ( "d.m.Y", strtotime ( $mdata ['details'] ['birthd'] ) ) );
				} else {
					$resulted_data [$row_id] ['birthd'] = sprintf ( $link, "-" );
				}
				
				$resulted_data [$row_id] ['admission_date'] = sprintf ( $link, $admission_date );
				$resulted_data [$row_id] ['discharge_date'] = sprintf ( $link, $discharge_date );
				if (! empty ( $pathealthins [$ipid] )) {
					
					$resulted_data [$row_id] ['health_insurance_company'] = sprintf ( $link, $pathealthins [$ipid] ['company_name'] );
					$resulted_data [$row_id] ['health_insurance_number'] = sprintf ( $link, $pathealthins [$ipid] ['insurance_no'] );
					$resulted_data [$row_id] ['health_insurance_kassennummer'] = sprintf ( $link, $pathealthins [$ipid] ['kvk_no'] );
					$resulted_data [$row_id] ['health_insurance_status'] = "";//sprintf ( $link, $pathealthins [$ipid] ['insurance_status'] );
					$resulted_data [$row_id] ['health_insurance_ik'] = sprintf ( $link, $pathealthins [$ipid] ['institutskennzeichen'] );
					$resulted_data [$row_id] ['insurance_options'] = sprintf ( $link, $insurance_option[$ipid] );
					
				} else {
					
					$resulted_data [$row_id] ['health_insurance_company'] = "";
					$resulted_data [$row_id] ['health_insurance_number'] = "";
					$resulted_data [$row_id] ['health_insurance_kassennummer'] = "";
					$resulted_data [$row_id] ['health_insurance_status'] = "";
					$resulted_data [$row_id] ['health_insurance_ik'] = "";
					$resulted_data [$row_id] ['insurance_options'] = "";
				}
				
				$row_id ++;
			}
		}
		
		$columns_array = $_REQUEST ['columns'];
		$sort_by = $columns_array[$_REQUEST ['order']['column']];
		$sort_dir = $_REQUEST ['order']['dir'];
		
		if($sort_dir == "asc"){
			$resulted_data = Pms_CommonData::array_sort($resulted_data,$sort_by,SORT_ASC);
		}else{
			$resulted_data = Pms_CommonData::array_sort($resulted_data,$sort_by,SORT_DESC);
		}
		$resulted_data = array_values($resulted_data);
		$response ['draw'] = $_REQUEST ['draw']; // ? get the sent draw from data table
		$response ['recordsTotal'] = $full_count;
		$response ['recordsFiltered'] = $full_count; // ??
		$response ['data'] = $resulted_data;
		$response ['order'] = $_REQUEST ['order'];
		$response ['columns'] = $_REQUEST ['columns'];
		
		header ( "Content-type: application/json; charset=UTF-8" );
		
		echo json_encode ( $response );
		exit ();
	}
	
}
?>