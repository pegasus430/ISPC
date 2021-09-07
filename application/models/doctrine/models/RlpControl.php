<?php

	Doctrine_Manager::getInstance()->bindComponent('RlpControl', 'MDAT');

	class RlpControl extends BaseRlpControl {

		public function get_rp_controlsheet($ipid, $date_start = false, $date_end = false)
		{
			$query = Doctrine_Query::create()
				->select('*')
				->from('RlpControl')
				->where('ipid = ?', $ipid )
				->andWhere('isdelete="0"');

			if($date_start && !$date_end)
			{
				$query->andWhere('MONTH(date) = MONTH("' . $date_start . '")');
				$query->andWhere('YEAR(date) = YEAR("' . $date_start . '")');
			}
			else if($date_start && $date_end)
			{
				$query->andWhere('DATE(date) BETWEEN DATE("' . $date_start . '") AND DATE("' . $date_end . '")');
			}

			$q_res = $query->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$formated_date = date('Y-m-d', strtotime($v_res['date']));
					$master_data[$v_res['shortcut']][$formated_date]['p_home'] = $v_res['qty_home'];
					$master_data[$v_res['shortcut']][$formated_date]['p_nurse'] = $v_res['qty_nurse'];
					$master_data[$v_res['shortcut']][$formated_date]['p_hospiz'] = $v_res['qty_hospiz'];
				}

				return $master_data;
			}
			else
			{
				return false;
			}
		}
		
		
		public function saved_rlp_controlsheets($ipids, $specific_period_days = false)
		{
			if(empty($ipids)){
				return false;
			}
			
			if( ! is_array($ipids))
			{
				$ipids = array($ipids);
			}
			
			$sql_str ="";
			if($specific_period_days)
			{
				foreach( $specific_period_days as $sp_ipid => $sp_days) {
						
					$period[$sp_ipid]['start'] = $sp_days[0];
					$period[$sp_ipid]['end'] = end($sp_days);
				}
				 
				foreach($period as $ipid => $v_data)
				{
					$sql_data[] = "(`ipid` LIKE '" . $ipid . "' AND (`form_date` BETWEEN '" . date('Y-m-d',strtotime($v_data['start'])) . "' AND '" . date('Y-m-d',strtotime($v_data['end'])) . "') )";
				}
				
				$sql_str = implode(' OR ', $sql_data);
				
			} else{
				$period = false;
			}
  
			
			$query = Doctrine_Query::create()
				->select('*')
				->from('RlpControl')
				->whereIn('ipid', $ipids)
				->andWhere('isdelete="0"');
			if(strlen($sql_str) > 0)
			{
				$query->andWhere($sql_str);
			}
			
			$q_res = $query->fetchArray();
			
			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$formated_date = date("d.m.Y",strtotime($v_res['form_date']));

					if($specific_period_days) {
						
						if(in_array($formated_date,$specific_period_days[$v_res['ipid']])) {
							$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date] = $v_res;
						}
					}
					else 
					{
						$master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date] = $v_res;
					}
				}

			}
			else
			{
				$master_data = false;
			}
			
			
			return $master_data;
			
		}
		
		
		public function get_rlp_controlsheets($ipids,$specific_period_days= false,$target = "form")
		{
			if(empty($ipids)){
				return false;
			}
			
			if( ! is_array($ipids))
			{
				$ipids = array($ipids);
			}

			$master_data = $this->rlp_actions($ipids,$specific_period_days,$target);
			
			return $master_data;
 
		}
		
		
		public function rlp_actions($ipids,$specific_period_days = false, $target = "form"){
			
			if(empty($ipids)){
				return false;
			}
			
			if( ! is_array($ipids))
			{
				$ipids = array($ipids);
			}
			
			if($specific_period_days)
			{
				foreach( $specific_period_days as $sp_ipid => $sp_days) {
						
					$period[$sp_ipid]['start'] = $sp_days[0];
					$period[$sp_ipid]['end'] = end($sp_days);
				}
			
			}
			else
			{
				$period = false;
			}
			
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;

			// Models
			$patientmaster_obj = new PatientMaster();
			$client_obj = new Client();
			$sapv_obj = new SapvVerordnung();
			$conatct_form_obj  = new ContactForms();
			$price_list_obj = new PriceList();
			$rlp_invoice_obj = new RlpInvoices();
			$PatientCourse_obj= new PatientCourse();
			
			
			//OVERALL
			$conditions_overall['periods'][0]['start'] = '2009-01-01';
			$conditions_overall['periods'][0]['end'] = date('Y-m-d');
			$conditions_overall['client'] = $clientid;
			$conditions_overall['ipids'] = $ipids;
			
			//beware of date d.m.Y format here
			$patient_overall_days = Pms_CommonData::patients_days($conditions_overall);
			
			if( ! isset($patient_overall_days) ){
				$patient_overall_days = array();
			}
			$patient_days2locationtypes = array();
			$hospital_days_cs_dmY = array();
			$hospiz_days_cs_dmY = array();
			$patient_active_days = array();
			$patient_treatment_days = array();
			$first_admission_day = array();
			$admission_periods = array();
			
 
			
			foreach($patient_overall_days as $k_patient=>$patient_data){
				
				$patient_active_days[$k_patient] = $patient_data['real_active_days'];
				$patient_treatment_days[$k_patient] = $patient_data['treatment_days'];
				
				//hospital days cs
				if(!empty($patient_data['hospital']['real_days_cs']))
				{
					$hospital_days_cs_dmY[$k_patient] = $patient_data['hospital']['real_days_cs'];
    				$patient_active_days[$k_patient] = array_diff($patient_data['real_active_days'], $patient_data['hospital']['real_days_cs']);
				}
				
				
				//hospiz days cs
				if(!empty($patient_data['hospiz']['real_days_cs']))
				{
					$hospiz_days_cs_dmY[$k_patient] = $patient_data['hospiz']['real_days_cs'];
				}
				
				// first_admission_day
				if(!empty($patient_data['admission_days'])){
					$first_admission_day[$k_patient]  = $patient_data['admission_days'][0];
				}
				
				// get admsission periods  days. // ISPC-2143 comment 06.06.2018 pct:2)  @ancuta 
				foreach($patient_data['active_periods'] as $pk=>$v_period){
				    $admission_periods[$k_patient][] = $patientmaster_obj->getDaysInBetween($v_period['start'], $v_period['end'],false,"d.m.Y");
				}

				foreach($patient_data['locations'] as $pat_location_row_id => $pat_location_data)
				{
					foreach($pat_location_data['days'] as $kl=>$lday)
					{
						if(in_array($lday,$patient_data['real_active_days']))
						{
				
							if( empty($pat_location_data['type'])){
								$pat_location_data['type'] = 0 ;
							}
					 
							if($pat_location_data['discharge_location'] != 1 ){
    							$patient_days2locationtypes[$k_patient][$lday][] = $pat_location_data['type'];
							}
						}
					}
				}
			}
			/* if($userid == "338"){
				print_R($first_admission_day);
				exit;
			} */
			// locations
			$location_mapping = $rlp_invoice_obj->rlp_locations();
			
			$loc_types2loc_ident = array();
			foreach($location_mapping as $loc_ident=>$loc_details){
				
				foreach($loc_details['location_type'] as $k=>$loc_type){
					
					$loc_types2loc_ident[$loc_type] = $loc_ident;
					$loc_types2dta_digits_1_2[$loc_type] = $loc_details['dta_digits_1_2'];
					
				}
			}
			
			
			  foreach( $patient_days2locationtypes as $pipid=>$locdata){
				foreach($locdata as $loc_day => $day_loc_types){
					$del_val = "1";
					if ( ! in_array($loc_day,$hospital_days_cs_dmY[$pipid]) && ($key = array_search($del_val, $day_loc_types)) !== false) {
						unset($patient_days2locationtypes[$pipid][$loc_day][$key]);
					}

					
        			//TODO-1589 @ancuta 06.06.2018
			        /*
					$del_val = "2";
					if ( ! in_array($loc_day,$hospiz_days_cs_dmY[$pipid]) && ($key = array_search($del_val, $day_loc_types)) !== false) {
						unset($patient_days2locationtypes[$pipid][$loc_day][$key]);
					}
			        */
                    //--			
				}
			} 
			/* if($userid =="338"){
			    echo "<pre/>";
			    print_r("patient_active_days: ");
			    print_r($patient_active_days);
			    
			    print_r("hospital_days_cs_dmY");
			    print_r($hospital_days_cs_dmY);
			    
			    print_r("patient_days2locationtypes");
			    print_r($patient_days2locationtypes);
			    
			    print_r($patient_overall_days);
			} */
			
            //dd($patient_active_days,$patient_days2locationtypes,$hospital_days_cs_dmY);
			foreach($patient_days2locationtypes as $pipid=>$locdata){
				foreach($locdata as $loc_day => $day_loc_types){
					$patient_days2locationtypes[$pipid][$loc_day] = end($day_loc_types);
				}
			}
			
			// XT and V actions
			$course_arr = array();
			$shortcuts = array("XT","V");
			$course_arr = $PatientCourse_obj->get_patients_period_course_by_shortcuts($ipids,$shortcuts);


			$pateint_cs_days = array();
			if( ! empty($course_arr)){
			    foreach($course_arr as $cs_ipid=>$cs_details){
			        foreach($cs_details as $cs_date=>$couse_sh_arr){
			            $pateint_cs_days[$cs_ipid][] =  date('d.m.Y',strtotime($cs_date));
			        }
			    }
			}
			
			// contact forms
			$conatct_form_arr = array();
			$conatct_form_arr = $conatct_form_obj->get_contactforms_multiple($ipids);
			
			$pateint_cf_days = array();
			if( ! empty($conatct_form_arr)){
				foreach( $conatct_form_arr as $cfid=>$cfdata){
					$pateint_cf_days[$cfdata['ipid']][] = date('d.m.Y',strtotime($cfdata['billable_date']));
				}
			}
			
			// sapv  days !!! SAPV DAYS ARE MANDATORY
			$patient_sapv_array = array();
			$patient_sapv_array = $sapv_obj->get_patients_sapv_periods($ipids);

			$sapv_dta_mapping = $rlp_invoice_obj->rlp_sapvtypes();
			
			$sapv_mapping = array('1'=>'be','2'=>'ko','1.2'=>'beko','3'=>'tv','4'=>'vv');
			$patient_sapvday2type_arr = array();
			$patient_sapvdays = array();
			$patient_sapvday2type = array();
			
			foreach($patient_sapv_array as $pipid => $sdata){
				foreach($sdata as $sid=>$s_details){
				    if($s_details['status'] != 1 || ($s_details['status'] == "1" && $s_details['status_denied'] == "partially" )){
				        
    					foreach($s_details['days'] as $k=>$sday){
    						//     TODO-1493 Leistungsnachweis Rheinland Pfalz BUG :: Comented on 12.04.2018 Ancuta
    						
    					    if($s_details['highest'] == "2" && in_array('1',$s_details['types_arr'] )){
    							$s_details['highest'] = "1.2";
    						}
    						//TODO-2012  ISPC: DTA RLP bug :: 15.01.2019 Ancuta
    						// changed again - to alloe  1.2
    						  
    						$patient_sapvday2type_arr[$pipid][$sday][] =$s_details['highest'];
    						$patient_sapvdays[$pipid][] = $sday;
    					}
				    }
					
				}
			}
			
			foreach($patient_sapvday2type_arr as $sipid => $sdate){
				foreach ($sdate as $sday=>$svals){
					// add only if sapv in active days of patient
					if(in_array($sday,$patient_active_days[$sipid])){
						$patient_sapvday2type[$sipid][$sday] = $sapv_mapping[max($svals)];
					}
				}
			}
			
			// price list 
			$master_price_list = array();
			foreach($ipids as $kmp_ipid => $vmp_ipid)
			{
				$master_price_list[$vmp_ipid] = $price_list_obj->period_price_list_specific_rlp($period[$vmp_ipid]['start'], $period[$vmp_ipid]['end']);
			}
			
			
			// ###############
			// get saved data
			// ###############
			$saved_data  =array();
			$saved_data = self::saved_rlp_controlsheets($ipids,$specific_period_days);
			
			$saved_data_overall = array();
			$saved_data_overall = self::saved_rlp_controlsheets($ipids);
			
			
			$day_location_type = "";
			$day_location_dts_digits_1_2 = "";
			$be_aasse_days = array();
			$first_dot_days = array();
			$second_dot_days = array();
			$overall_first_dot_days = array();
			$overall_shortcut_details = array();
			$billable_regional_flatrate = array();
			
 			foreach($ipids as $k=>$ipid){

 				if( ! isset($billable_first_dot_days[$ipid])){
 					$billable_first_dot_days[$ipid] = array();
 				}
 				
 				if(!isset($pateint_cf_days[$ipid])){
 					$pateint_cf_days[$ipid] = array();
 				}
 				
 				if(!isset($pateint_cs_days[$ipid])){
 					$pateint_cs_days[$ipid] = array();
 				}
 				
 				if(!empty($saved_data_overall[$ipid])){
 					
 					foreach( $saved_data_overall[$ipid] as $sh_key=>$sh_dates){
 						foreach($sh_dates as $fdate=>$fvalues){
 							if($fvalues['value'] == "1"){
		 						$overall_shortcut_details[$ipid][$sh_key][] = date("d.m.Y",strtotime($fvalues['form_date']));
		 						if($sh_key == "first_dot"){
// 		 							$billable_first_dot_days[$ipid][] = date("d.m.Y",strtotime($fvalues['form_date']));
		 						}
 							}
 						}
 					}
 				}
 				
 				if(!empty($overall_shortcut_details[$ipid]["first_dot"])){
 					$first_dot_ever =  $overall_shortcut_details[$ipid]["first_dot"][0];
 				}
 				
 				foreach($patient_sapvday2type[$ipid] as $sapv_day => $sapv_type){
 					
 					if( ! isset($billable_days[$ipid])){
 						$billable_days[$ipid] = array();
 					}
 					$date_billable_actions[$ipid][$sapv_day] = 0 ;

 					
 					$day_location_type = $loc_types2loc_ident[$patient_days2locationtypes[$ipid][date('d.m.Y',strtotime($sapv_day))]];
 					
 					$day_location_dta_digits_1_2 = $loc_types2dta_digits_1_2[$patient_days2locationtypes[$ipid][date('d.m.Y',strtotime($sapv_day))]];
 					$day_sapv_dta_digits_5_6 = $sapv_dta_mapping[$sapv_type]['dta_digits_5_6'];
 					
 					
 					$not_billable_days[$ipid] = array(); 
 					if(!empty($billable_days[$ipid])){
 						$last_billable_date[$ipid] = end($billable_days[$ipid]);
 						 
 						$not_billable_days[$ipid] = $patientmaster_obj->getDaysInBetween($last_billable_date[$ipid], $sapv_day);
 					}
 					
 					// TODO-2217
 					// Ancuta 29.03.2019
 					// removed all billable days from array
 					foreach($not_billable_days[$ipid] as $k_date=>$nbdate ){
 					    if(in_array(date("d.m.Y",strtotime($nbdate)),$billable_days[$ipid])){
 					        unset($not_billable_days[$ipid][$k_date]);
 					    }
 					}
 					//--

 					
 					// be_aasse ::  billed 1 time, can be billed again after 28 days of pause
 					$shortcut = "be_aasse";

 					if( isset($saved_data[$ipid][$shortcut][$sapv_day]))
 					{
 						if($saved_data[$ipid][$shortcut][$sapv_day]['value'] == "1")
 						{
 							$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
 							$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
 							
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
 							$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
 							$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
 							$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
 							
 							$items[$ipid][$sapv_day][$shortcut]['system'] = "0";
 							$be_aasse_days[$ipid][] = $sapv_day;
 							
 							$date_billable_actions[$ipid][$sapv_day] +=1;
 						} else {
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 0;
 						}
 					}
 					else
 					{
 					    //TODO-2217  on 29.03.2019 - Change to higher then 28
	 					if(empty($be_aasse_days[$ipid]) || empty($not_billable_days[$ipid]) || count($not_billable_days[$ipid]) > 28 ){
	 					
	 					    // TODO-2058 on 29.01.2019 - this is triggered - ONLY for BE 
		 					if(($sapv_type == "be") && in_array($sapv_day,$pateint_cf_days[$ipid]) ){
		 						
		 						$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
		 						$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
		 					
								$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
								$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
								$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
								$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
								$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
								$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];

								$items[$ipid][$sapv_day][$shortcut]['system'] = "1";
								$be_aasse_days[$ipid][] = $sapv_day;
								
								$date_billable_actions[$ipid][$sapv_day] +=1;
		 					}
	 					} 
 					}
 					
 					//sapv_flatrate :: is added manually - One time in a lifetime per patient // CHANGE to radio
 					$shortcut = "sapv_flatrate"; // radio
 					if( isset($saved_data[$ipid][$shortcut][$sapv_day]))
 					{
 						if($saved_data[$ipid][$shortcut][$sapv_day]['value'] == "1")
 						{
 							$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
 							$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
 					
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
 							$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
 							$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
 							$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
 							
 							$items[$ipid][$sapv_day][$shortcut]['system'] = "0";
 					
 							$date_billable_actions[$ipid][$sapv_day] +=1;
 					
 						}else {
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 0;
 						}
 					}
 					
 					//first_dot :: billed 1 time, can be billed again after 28 days of pause 
 					$shortcut = "first_dot";
 					if( isset($saved_data[$ipid][$shortcut][$sapv_day]))
 					{
 						if($saved_data[$ipid][$shortcut][$sapv_day]['value'] == "1")
 						{
 							$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
 							$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
 					
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
 							$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
 							$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
 							$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
 							
 							$items[$ipid][$sapv_day][$shortcut]['system'] = "0";
 							$first_dot_days[$ipid][] = $sapv_day;
 							$overall_first_dot_days[$ipid][] = $sapv_day;
 							
 							$date_billable_actions[$ipid][$sapv_day] +=1;
 							
 							$billable_first_dot_days[$ipid][] =$sapv_day;
 					
 						}else {
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 0;
 						}
 					}	
 					else
 					{
 						// ISPC-2149 on  21.03.2018  the KO option was added
 						// TODO-2012 beko option was added 21.01.2019
 					    // TODO-2058 on 29.01.2019 - this must be triggered by  all sapv types, EXCEPT only BE [ no changes done on 29.01.2019 - just comment added]
	 					if(($sapv_type == "tv" || $sapv_type == "vv" || $sapv_type == "ko" || $sapv_type == "beko") ){	 						
	
	 						if( ! isset($first_dot_days[$ipid])){
	 							$first_dot_days[$ipid] = array();
	 						}
	 						
	 						if( ! isset($overall_shortcut_details[$ipid][$shortcut])){
	 							$overall_shortcut_details[$ipid][$shortcut] = array();
	 						}
	 						
	 						
	 						if(!empty($overall_shortcut_details[$ipid][$shortcut]))
	 						{
	 							usort($overall_shortcut_details[$ipid][$shortcut], array("_date_compare"));
	 							$overall_shortcut_details[$ipid][$shortcut] = array_values($overall_shortcut_details[$ipid][$shortcut]);
	 						}
	 						// Added also course date condittion: ISPC-2143( comment: 25.05.2018  ) @Ancuta 30.05.2018
	 						//TODO-2217  on 29.03.2019 - Change to ONLY higher then 28
	 						if(!in_array($sapv_day,$first_dot_days[$ipid]) 
 								&& (in_array($sapv_day,$pateint_cf_days[$ipid]) || in_array($sapv_day,$pateint_cs_days[$ipid]))   
	 							&& (empty($billable_first_dot_days[$ipid]) || (empty($not_billable_days[$ipid]) || count($not_billable_days[$ipid]) > 28)) 
	 								){
	 							
		 						$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
		 						$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
		 					
		 						$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
		 						$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
		 						$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
		 						$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
		 						$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
		 						$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
		 						
		 						$items[$ipid][$sapv_day][$shortcut]['system'] = "1";
		 						$first_dot_days[$ipid][] = $sapv_day;
		 						$overall_shortcut_details[$ipid][$shortcut][] = $sapv_day;
		 						
		 						$overall_first_dot_days[$ipid][] = $sapv_day;
		 						$date_billable_actions[$ipid][$sapv_day] +=1;
		 						
		 						$billable_first_dot_days[$ipid][] =$sapv_day;
	 						}
	 					}
 					}
 					
 					
 					//second_dot :: every SAPV day after day one 
 					$shortcut = "second_dot";
 					
 					if( isset($saved_data[$ipid][$shortcut][$sapv_day]))
 					{
 						if($saved_data[$ipid][$shortcut][$sapv_day]['value'] == "1")
 						{
 							$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
 							$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
 					
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
 							$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
 							$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
 							$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
 							
 							$items[$ipid][$sapv_day][$shortcut]['system'] = "0";
 							$second_dot_days[$ipid][] = $sapv_day;
 							
 							$date_billable_actions[$ipid][$sapv_day] +=1;
 					
 						}else {
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 0;
 						}
 					} 
 					else
 					{
	 					// ISPC-2149 on  21.03.2018  the KO option was added
 					    // TODO-2012 beko option was added 21.01.2019
 					    // TODO-2058 on 29.01.2019 - this must be triggered by  all sapv types, EXCEPT only BE [ no changes done on 29.01.2019 - just comment added]
 						if(($sapv_type == "tv" || $sapv_type == "vv" || $sapv_type == "ko" || $sapv_type == "beko") 
 						    && ! empty($first_dot_days[$ipid]) 
 						    && ! in_array($sapv_day,$first_dot_days[$ipid]) 
 						    && ! in_array($sapv_day,$second_dot_days[$ipid])){

 						
	 						$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
	 						$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
	 							
	 						$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
	 						$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
	 						$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
	 						$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
	 						$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
	 						$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
	 						
	 						$items[$ipid][$sapv_day][$shortcut]['system'] = "1";
	 						$second_dot_days[$ipid][] = $sapv_day;
	 						
	 						$date_billable_actions[$ipid][$sapv_day] +=1;
	 					}
 					}
 					
 					
 					//doctor_visit :: is added manually, can be billed once per SAPV day 
 					$shortcut = "doctor_visit";
 					if( isset($saved_data[$ipid][$shortcut][$sapv_day]))
 					{
 						if($saved_data[$ipid][$shortcut][$sapv_day]['value'] == "1")
 						{
 							$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
 							$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
 					
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
 							$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
 							//$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
 							$items[$ipid][$sapv_day][$shortcut]['dta_id'] = "60".$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;//TODO-3454 Ancuta 23.09.2020
 							$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
 							$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
 							
 							$items[$ipid][$sapv_day][$shortcut]['system'] = "0";
 							
 							$date_billable_actions[$ipid][$sapv_day] +=1;
 					
 						}else {
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 0;
 						}
 					}
 					
 					//nurse_visit :: is added manually, can be billed once per SAPV day 
 					$shortcut = "nurse_visit";
 					if( isset($saved_data[$ipid][$shortcut][$sapv_day]))
 					{
 						if($saved_data[$ipid][$shortcut][$sapv_day]['value'] == "1")
 						{
 							$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
 							$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
 					
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
 							$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
 							//$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
 							$items[$ipid][$sapv_day][$shortcut]['dta_id'] = "60".$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;//TODO-3454 Ancuta 23.09.2020
 							$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
 							$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
 							
 							
 							$items[$ipid][$sapv_day][$shortcut]['system'] = "0";
 							
 							$date_billable_actions[$ipid][$sapv_day] +=1;
 					
 						}else {
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 0;
 						}
 					}
 					
 					//regional_flatrate :: is added at admission once in a lifetime per patient 
 					$shortcut = "regional_flatrate";

 					if( isset($saved_data[$ipid][$shortcut][$sapv_day]))
 					{
 						if($saved_data[$ipid][$shortcut][$sapv_day]['value'] == "1")
 						{
 							$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
 							$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
 					
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;$items[$ipid][$sapv_day][$shortcut]['value'] = 1;
 							$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
 							$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
//  							$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
 							$items[$ipid][$sapv_day][$shortcut]['dta_id'] = "60".$day_location_dta_digits_3_4."60".$day_location_dta_digits_7_10; // TODO-1621 Hardcoded by Ancuta - 14.06.2018
 							$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
 							$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
 							
 							
 							$items[$ipid][$sapv_day][$shortcut]['system'] = "0";
 					
 							$date_billable_actions[$ipid][$sapv_day] +=1;
 					
 						}else {
 							$items[$ipid][$sapv_day][$shortcut]['qty'] = 0;
 						}
 					}
 					else
 					{
 					    // ISPC2143 + TODO-1592 change from billing the first admission date, to first sapv date in first admission
	 					if( empty($billable_regional_flatrate[$ipid]) &&  in_array($sapv_day,$admission_periods[$ipid][0])){
	
	 						$day_location_dta_digits_3_4 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_3_4'];
	 						$day_location_dta_digits_7_10 = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_digits_7_10'];
	 						
	 						$items[$ipid][$sapv_day][$shortcut]['qty'] = 1;; $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
	 						$items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price'];
	 						$items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['dta_price'];
// 	 						$items[$ipid][$sapv_day][$shortcut]['dta_id'] = $day_location_dta_digits_1_2.$day_location_dta_digits_3_4.$day_sapv_dta_digits_5_6.$day_location_dta_digits_7_10;
	 						$items[$ipid][$sapv_day][$shortcut]['dta_id'] = "60".$day_location_dta_digits_3_4."60".$day_location_dta_digits_7_10;// TODO-1621 Hardcoded by Ancuta - 14.06.2018
	 						$items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type;
	 						$items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d",strtotime($sapv_day))][$shortcut][$day_location_type]['price_list'];
	 						
	 						
	 						$items[$ipid][$sapv_day][$shortcut]['system'] = "1";
	 						$date_billable_actions[$ipid][$sapv_day] +=1;
	 						
	 						$billable_regional_flatrate[$ipid][] = $sapv_day;
	 					}
 					}
 					
 					if($date_billable_actions[$ipid][$sapv_day] > 0 ){
 						$billable_days[$ipid][] = $sapv_day;
 					}
 					
 				}	
 			}
 			
			if(!empty($specific_period_days)){
			
				if($target == "form"){
					foreach($items as $patient=>$data_arr){
						foreach($data_arr as $day =>$shortcut_arr){
							if(in_array($day,$specific_period_days[$patient])){
								
								foreach($shortcut_arr as $shortcut=>$sh_data){
									if($sh_data['qty'] == "1"){
										$master_data[$patient][$shortcut][date('Y-m-d',strtotime($day))] = $sh_data;
									}
								}
							}
						}
					}
				}elseif($target == "invoice"){
					foreach($items as $patient=>$data_arr){
						foreach($data_arr as $day => $shortcut_arr){
							if(in_array($day,$specific_period_days[$patient])){
								foreach($shortcut_arr as $shortcut=>$sh_data){
									$master_data[$patient][$shortcut][date('Y-m-d',strtotime($day))] = $sh_data;
								}
							}
						}
					}
				}
			}
			
			
			//dd($patient_days2locationtypes,$master_data); 
			
			return $master_data;
		}
	}

?>