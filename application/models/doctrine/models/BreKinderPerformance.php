<?php
class BreKinderPerformance extends BaseBreKinderPerformance
{

    public function saved_performancerecord($ipids, $specific_period_days = false)
    {
        if (empty($ipids)) {
            return false;
        }
        
        if (! is_array($ipids)) {
            $ipids = array(
                $ipids
            );
        }
        
        $sql_str = "";
        if ($specific_period_days) {
            foreach ($specific_period_days as $sp_ipid => $sp_days) {
                
                $period[$sp_ipid]['start'] = $sp_days[0];
                $period[$sp_ipid]['end'] = end($sp_days);
            }
            
            foreach ($period as $ipid => $v_data) {
                $sql_data[] = "(`ipid` LIKE '" . $ipid . "' AND (`form_date` BETWEEN '" . date('Y-m-d', strtotime($v_data['start'])) . "' AND '" . date('Y-m-d', strtotime($v_data['end'])) . "') )";
            }
            
            $sql_str = implode(' OR ', $sql_data);
        } else {
            $period = false;
        }
        
        $query = Doctrine_Query::create()->select('*')
            ->from('BreKinderPerformance')
            ->whereIn('ipid', $ipids)
            ->andWhere('isdelete="0"');
        if (strlen($sql_str) > 0) {
            $query->andWhere($sql_str);
        }
        
        $q_res = $query->fetchArray();
        
        if ($q_res) {
            foreach ($q_res as $k_res => $v_res) {
                $formated_date = date("d.m.Y", strtotime($v_res['form_date']));
                
                if ($specific_period_days) {
                    
                    if (in_array($formated_date, $specific_period_days[$v_res['ipid']])) {
                        $master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date] = $v_res;
                    }
                } else {
                    $master_data[$v_res['ipid']][$v_res['shortcut']][$formated_date] = $v_res;
                }
            }
        } else {
            $master_data = false;
        }
        
        return $master_data;
    }

    public function get_performancerecord($ipids, $specific_period_days = false, $target = "form")
    {
        if (empty($ipids)) {
            return false;
        }
        
        if (! is_array($ipids)) {
            $ipids = array(
                $ipids
            );
        }
        
        $master_data = self::bre_actions($ipids, $specific_period_days, $target);
        
        return $master_data;
    }

    public function bre_actions($ipids, $specific_period_days = false, $target = "form")
    {
        if (empty($ipids)) {
            return false;
        }
        
        if (! is_array($ipids)) {
            $ipids = array(
                $ipids
            );
        }
        
        if ($specific_period_days) {
            foreach ($specific_period_days as $sp_ipid => $sp_days) {
                
                $period[$sp_ipid]['start'] = $sp_days[0];
                $period[$sp_ipid]['end'] = end($sp_days);
            }
        } else {
            $period = false;
        }
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        // Models
        $patientmaster_obj = new PatientMaster();
        $client_obj = new Client();
        $sapv_obj = new SapvVerordnung();
        $conatct_form_obj = new ContactForms();
        $price_list_obj = new PriceList();
        $invoice_obj = new InvoiceSystem();
        $PatientCourse_obj = new PatientCourse();
        
        // OVERALL
        $conditions_overall['periods'][0]['start'] = '2009-01-01';
        $conditions_overall['periods'][0]['end'] = date('Y-m-d');
        $conditions_overall['client'] = $clientid;
        $conditions_overall['ipids'] = $ipids;
        
        // beware of date d.m.Y format here
        $patient_overall_days = Pms_CommonData::patients_days($conditions_overall);
        
        if (! isset($patient_overall_days)) {
            $patient_overall_days = array();
        }
        $patient_days2locationtypes = array();
        $hospital_days_cs_dmY = array();
        $hospiz_days_cs_dmY = array();
        $patient_active_days = array();
        $patient_treatment_days = array();
        $first_admission_day = array();
        $admission_periods = array();
        
        $patient_active_months = array();
        
        foreach ($patient_overall_days as $k_patient => $patient_data) {
            
            $patient_active_days[$k_patient] = $patient_data['real_active_days'];
            $patient_treatment_days[$k_patient] = $patient_data['treatment_days'];
            
            // hospital days cs
            if (! empty($patient_data['hospital']['real_days_cs'])) {
                $hospital_days_cs_dmY[$k_patient] = $patient_data['hospital']['real_days_cs'];
                $patient_active_days[$k_patient] = array_diff($patient_data['real_active_days'], $patient_data['hospital']['real_days_cs']);
            }
            
            // hospiz days cs
            if (! empty($patient_data['hospiz']['real_days_cs'])) {
                $hospiz_days_cs_dmY[$k_patient] = $patient_data['hospiz']['real_days_cs'];
            }
            
            // first_admission_day
            if (! empty($patient_data['admission_days'])) {
                $first_admission_day[$k_patient] = $patient_data['admission_days'][0];
            }
            
            // get admsission periods days. // ISPC-2143 comment 06.06.2018 pct:2) @ancuta
            foreach ($patient_data['active_periods'] as $pk => $v_period) {
                $admission_periods[$k_patient][] = $patientmaster_obj->getDaysInBetween($v_period['start'], $v_period['end'], false, "d.m.Y");
                
                $last_active = end($patient_data['active_periods']);
                if ($v_period['end'] == $last_active['end']) {
                    $v_period['end'] = date('Y-m-d', strtotime($v_period['end']));
                }
                
                $months[$k_patient] = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], 'Y-m');
                if (empty($patient_active_months[$k_patient])) {
                    $patient_active_months[$k_patient] = array();
                }
                $patient_active_months[$k_patient] = array_merge($patient_active_months[$k_patient], $months[$k_patient]);
            }
            
            foreach ($patient_data['locations'] as $pat_location_row_id => $pat_location_data) {
                foreach ($pat_location_data['days'] as $kl => $lday) {
                    if (in_array($lday, $patient_data['real_active_days'])) {
                        
                        if (empty($pat_location_data['type'])) {
                            $pat_location_data['type'] = 0;
                        }
                        
                        if ($pat_location_data['discharge_location'] != 1) {
                            $patient_days2locationtypes[$k_patient][$lday][] = $pat_location_data['type'];
                        }
                    }
                }
            }
        }
        
        // locations
        $location_mapping = $invoice_obj->invoice_locations_mapping("bre_kinder_invoice");
//         dd($location_mapping);
        $loc_types2loc_ident = array();
        foreach ($location_mapping as $loc_ident => $loc_details) {
            
            foreach ($loc_details['location_type'] as $k => $loc_type) {
                
                $loc_types2loc_ident[$loc_type] = $loc_ident;
            }
        }
   
        
        foreach ($patient_days2locationtypes as $pipid => $locdata) {
            foreach ($locdata as $loc_day => $day_loc_types) {
                $del_val = "1";
                if (! in_array($loc_day, $hospital_days_cs_dmY[$pipid]) && ($key = array_search($del_val, $day_loc_types)) !== false) {
                    unset($patient_days2locationtypes[$pipid][$loc_day][$key]);
                }
            }
        }
        // dd($patient_active_days,$patient_days2locationtypes,$hospital_days_cs_dmY);
        foreach ($patient_days2locationtypes as $pipid => $locdata) {
            foreach ($locdata as $loc_day => $day_loc_types) {
                $patient_days2locationtypes[$pipid][$loc_day] = end($day_loc_types);
            }
        }
        
//         dd($patient_days2locationtypes);
        
        // XT and V actions
        $course_arr = array();
        $course_shortcuts = array(
            "XT",
            "V"
        );
        $course_arr = $PatientCourse_obj->get_patients_period_course_by_shortcuts($ipids, $course_shortcuts);
        
        $pateint_cs_days = array();
        if (! empty($course_arr)) {
            foreach ($course_arr as $cs_ipid => $cs_details) {
                foreach ($cs_details as $cs_date => $couse_sh_arr) {
                    $pateint_cs_days[$cs_ipid][] = date('d.m.Y', strtotime($cs_date));
                }
            }
        }
        
        // contact forms
        $conatct_form_arr = array();
        $conatct_form_arr = $conatct_form_obj->get_contactforms_multiple($ipids);
        
        $pateint_cf_days = array();
        if (! empty($conatct_form_arr)) {
            foreach ($conatct_form_arr as $cfid => $cfdata) {
                $pateint_cf_days[$cfdata['ipid']][] = date('d.m.Y', strtotime($cfdata['billable_date']));
            }
        }
        
        
        
        // sapv days !!! SAPV DAYS ARE MANDATORY
        $patient_sapv_array = array();
        $patient_sapv_array = $sapv_obj->get_patients_sapv_periods($ipids); // All sapv including denied
                                                                            
        $active_sapv_contact_days = array();
        $denied_sapv_contact_days = array();
        
        foreach ($ipids as $ipid) {
            
            foreach ($patient_sapv_array[$ipid] as $sapv_id => $sapv_data) {
                
                // ACTIVE SAPV
                if ($sapv_data['status'] != "1") {
                    
                    foreach ($sapv_data['days'] as $k => $sday) {
                        // acitve, in current period and contact day visit or verlauf actions
                        if (in_array($sday, $patient_active_days[$ipid]) && in_array($sday, $specific_period_days[$ipid]) && (in_array($sday, $pateint_cs_days[$ipid]) || in_array($sday, $pateint_cf_days[$ipid]))) {
                            $active_sapv_contact_days[$ipid][date("Y-m", strtotime($sday))][] = $sday;
                        }
                    }
                }                 

                // DENIED SAPV
                else {
                    foreach ($sapv_data['days'] as $k => $sday) {
                        // active and in current period
                        if (in_array($sday, $patient_active_days[$ipid]) && in_array($sday, $specific_period_days[$ipid])) {
                            
                            $denied_sapv_contact_days[$ipid][date("Y-m", strtotime($sday))][] = $sday;
                        }
                    }
                }
            }
        }
        
        
        // price list
        $master_price_list = array();
        foreach ($ipids as $kmp_ipid => $vmp_ipid) {
            $master_price_list[$vmp_ipid] = $price_list_obj->period_price_list_specific_brekinder($period[$vmp_ipid]['start'], $period[$vmp_ipid]['end']);
        }
        
//         dd($master_price_list);
        // ###############
        // get saved data
        // ###############
        $saved_data = array();
        $saved_data = self::saved_performancerecord($ipids, $specific_period_days);
        
        $calendar_months2period = array();
        $month_days= array();
        
        foreach($specific_period_days as $ipid=>$ipid_period){
            foreach($ipid_period as $k=>$iday){
                $month_days[$ipid][date("Y-m",strtotime($iday))][] = $iday;
            }
            $calendar_months2period[$ipid] = Pms_CommonData::get_period_months( date('Y-m-d',strtotime($ipid_period[0])), date('Y-m-d',strtotime(end($ipid_period))), 'Y-m');
        }

        
        
        // split saved data by calendar months
        foreach($saved_data as $ipid => $shortcut_array){
            
            foreach($shortcut_array as $shortcut=>$days_arr){
                
                foreach($days_arr as $sday => $dvalue){
//                     $saved_per_month[$ipid][$shortcut][date('Y-m',strtotime($sday))] = $dvalue;
                    $saved_per_month[$ipid][date('Y-m',strtotime($sday))][$shortcut] = $dvalue;
                }
            }
        }
//         dd($saved_per_month );
        $saved_data_overall = array();
        $saved_data_overall = self::saved_performancerecord($ipids);
        
        $day_location_type = [];
        $overall_shortcut_details = array();
        
        $monthly_fee_days = array();
        $transfer2standard_days = array();
        $rejected_regulation_days = array();
        
        // get shortcuts and saved pricelist or default pricelist
        $shortcuts = $invoice_obj->invoice_products('bre_kinder_invoice');
         
        
        
        
        $items = array();
        foreach ($ipids as $k => $ipid) {
            $day_location_type[$ipid] = array();
            foreach ($calendar_months2period[$ipid] as $k => $calendar_month) {
                
                
                // SAVED DATA
                if (! empty($saved_per_month[$ipid][$calendar_month])) {
                    
                    foreach ($shortcuts as $shortcut) {
                        if (! empty($saved_per_month[$ipid][$calendar_month][$shortcut])) {
                            
                            $item_day = $saved_per_month[$ipid][$calendar_month][$shortcut]['form_date'];
                            $item_day_dmy = date('d.m.Y', strtotime($saved_per_month[$ipid][$calendar_month][$shortcut]['form_date']));
                            
                            $day_location_type[$ipid][$item_day_dmy] = "";
                            if (! empty($patient_days2locationtypes[$ipid][$item_day_dmy])) {
                                $day_location_type[$ipid][$item_day_dmy] = $loc_types2loc_ident[$patient_days2locationtypes[$ipid][$item_day_dmy]];
                            } else {
                                $day_location_type[$ipid][$item_day_dmy] = 'no_location';
                            }
                            
                            if ($shortcut == "monthly_fee" && empty($saved_per_month[$ipid][$calendar_month]['rejected_regulation'])) {
                                
                                $items[$ipid][$item_day_dmy][$shortcut]['qty'] = 1;
                                $items[$ipid][$item_day_dmy][$shortcut]['value'] = 1;
                                $items[$ipid][$item_day_dmy][$shortcut]['price'] = $master_price_list[$ipid][$item_day][$shortcut][$day_location_type[$ipid][$item_day_dmy]]['price'];
                                $items[$ipid][$item_day_dmy][$shortcut]['dta_price'] = $master_price_list[$ipid][$item_day][$shortcut][$day_location_type[$ipid][$item_day_dmy]]['dta_price'];
                                $items[$ipid][$item_day_dmy][$shortcut]['dta_id'] = $master_price_list[$ipid][$item_day][$shortcut][$day_location_type[$ipid][$item_day_dmy]]['dta_id'];
                                $items[$ipid][$item_day_dmy][$shortcut]['location_type'] = $day_location_type[$ipid][$item_day_dmy];
                                $items[$ipid][$item_day_dmy][$shortcut]['price_list'] = $master_price_list[$ipid][$item_day][$shortcut][$day_location_type[$ipid][$item_day_dmy]]['price_list'];
                                $items[$ipid][$item_day_dmy][$shortcut]['system'] = "0";
                                
                            } elseif ($shortcut == "transfer2standard" && empty($saved_per_month[$ipid][$calendar_month]['rejected_regulation'])) {
                                
                                $items[$ipid][$item_day_dmy][$shortcut]['qty'] = 1;
                                $items[$ipid][$item_day_dmy][$shortcut]['value'] = 1;
                                $items[$ipid][$item_day_dmy][$shortcut]['price'] = $master_price_list[$ipid][$item_day][$shortcut]['all']['price'];
                                $items[$ipid][$item_day_dmy][$shortcut]['dta_price'] = $master_price_list[$ipid][$item_day][$shortcut]['all']['dta_price'];
                                $items[$ipid][$item_day_dmy][$shortcut]['dta_id'] = $master_price_list[$ipid][$item_day][$shortcut]['all']['dta_id'];
                                $items[$ipid][$item_day_dmy][$shortcut]['location_type'] = "all";
                                $items[$ipid][$item_day_dmy][$shortcut]['price_list'] = $master_price_list[$ipid][$item_day][$shortcut]['all']['price_list'];
                                $items[$ipid][$item_day_dmy][$shortcut]['system'] = "0";
                                
                            } elseif ($shortcut == "rejected_regulation") {
                                
                                $items[$ipid][$item_day_dmy][$shortcut]['qty'] = 1;
                                $items[$ipid][$item_day_dmy][$shortcut]['value'] = 1;
                                $items[$ipid][$item_day_dmy][$shortcut]['price'] = $master_price_list[$ipid][$item_day][$shortcut]['all']['price'];
                                $items[$ipid][$item_day_dmy][$shortcut]['dta_price'] = $master_price_list[$ipid][$item_day][$shortcut]['all']['dta_price'];
                                $items[$ipid][$item_day_dmy][$shortcut]['dta_id'] = $master_price_list[$ipid][$item_day][$shortcut]['all']['dta_id'];
                                $items[$ipid][$item_day_dmy][$shortcut]['location_type'] = "all";
                                $items[$ipid][$item_day_dmy][$shortcut]['price_list'] = $master_price_list[$ipid][$item_day][$shortcut]['all']['price_list'];
                                $items[$ipid][$item_day_dmy][$shortcut]['system'] = "0";
                            }
                        }
                    }
                } else {
                    
                    
                    // SYSTEM DATA
                    foreach ($month_days[$ipid][$calendar_month] as $k => $sapv_day) {
                        
                        $day_location_type[$ipid][$sapv_day] = "";
                        if (! empty($patient_days2locationtypes[$ipid][date('d.m.Y', strtotime($sapv_day))])) {
                            $day_location_type[$ipid][$sapv_day] = $loc_types2loc_ident[$patient_days2locationtypes[$ipid][date('d.m.Y', strtotime($sapv_day))]];
                        } else {
                            $day_location_type[$ipid][$sapv_day] = 'no_location';
                        }
                        
                        // product is added ONCE a calendar month if pateint has at least ONE active approved sapv day with visit or phone call.
                        $shortcut = "monthly_fee";
                        if (empty($rejected_regulation_days[$ipid][date("Y-m", strtotime($sapv_day))]) && empty($monthly_fee_days[$ipid][date("Y-m", strtotime($sapv_day))])) {
                            
                            if (! empty($active_sapv_contact_days[$ipid][date("Y-m", strtotime($sapv_day))]) && $active_sapv_contact_days[$ipid][date("Y-m", strtotime($sapv_day))][0] == $sapv_day) {
                                
                                
                                if (isset($master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut][$day_location_type[$ipid][$sapv_day]])) {
                                    
                                    $items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut][$day_location_type[$ipid][$sapv_day]]['price'];
                                    $items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut][$day_location_type[$ipid][$sapv_day]]['dta_price'];
                                    $items[$ipid][$sapv_day][$shortcut]['dta_id'] = $master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut][$day_location_type[$ipid][$sapv_day]]['dta_id'];
                                    $items[$ipid][$sapv_day][$shortcut]['location_type'] = $day_location_type[$ipid][$sapv_day];
                                    $items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut][$day_location_type[$ipid][$sapv_day]]['price_list'];
                                    $items[$ipid][$sapv_day][$shortcut]['qty'] = 1;
                                    $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
                                    $items[$ipid][$sapv_day][$shortcut]['system'] = "1";
                                    
                                } 
                                else 
                                {
                                    $items[$ipid][$sapv_day][$shortcut]['qty'] = 1;
                                    $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
                                    $items[$ipid][$sapv_day][$shortcut]['price'] = '0.00';
                                    $items[$ipid][$sapv_day][$shortcut]['dta_price'] = '0.00';
                                    $items[$ipid][$sapv_day][$shortcut]['dta_id'] = "";
                                    $items[$ipid][$sapv_day][$shortcut]['location_type'] = '';
                                    $items[$ipid][$sapv_day][$shortcut]['price_list'] = '';
                                    $items[$ipid][$sapv_day][$shortcut]['system'] = "1";
                                }
                                
                                $monthly_fee_days[$ipid][date("Y-m", strtotime($sapv_day))][] = $sapv_day;
                            }  
                        }
                        
                        $shortcut = "rejected_regulation";
                        if (empty($rejected_regulation_days[$ipid][date("Y-m", strtotime($sapv_day))])) {
                            
                            
                            if (empty($active_sapv_contact_days[$ipid][date("Y-m", strtotime($sapv_day))]) && ! empty($denied_sapv_contact_days[$ipid][date("Y-m", strtotime($sapv_day))])) {
                                
                                if (isset($master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut]['all'])) {
                                    
                                    $items[$ipid][$sapv_day][$shortcut]['qty'] = 1;
                                    $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
                                    $items[$ipid][$sapv_day][$shortcut]['price'] = $master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut]['all']['price'];
                                    $items[$ipid][$sapv_day][$shortcut]['dta_price'] = $master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut]['all']['dta_price'];
                                    $items[$ipid][$sapv_day][$shortcut]['dta_id'] = $master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut]['all']['dta_id'];
                                    $items[$ipid][$sapv_day][$shortcut]['location_type'] = 'all';
                                    $items[$ipid][$sapv_day][$shortcut]['price_list'] = $master_price_list[$ipid][date("Y-m-d", strtotime($sapv_day))][$shortcut][$day_location_type[$ipid][$sapv_day]]['price_list'];
                                    $items[$ipid][$sapv_day][$shortcut]['system'] = "1";
                                } 
                                else 
                                {
                                    $items[$ipid][$sapv_day][$shortcut]['qty'] = 1;
                                    $items[$ipid][$sapv_day][$shortcut]['value'] = 1;
                                    $items[$ipid][$sapv_day][$shortcut]['price'] = '0.00';
                                    $items[$ipid][$sapv_day][$shortcut]['dta_price'] = '0.00';
                                    $items[$ipid][$sapv_day][$shortcut]['dta_id'] = "";
                                    $items[$ipid][$sapv_day][$shortcut]['location_type'] = '';
                                    $items[$ipid][$sapv_day][$shortcut]['price_list'] = '';
                                    $items[$ipid][$sapv_day][$shortcut]['system'] = "1";
                                }
                                
                                $rejected_regulation_days[$ipid][date("Y-m", strtotime($sapv_day))][] = $sapv_day;
                            }
                        }
                        
                        // Manually added once a calendar month :: so no system data
                        // $shortcut = "transfer2standard";
                    }
                }
            }
        }
        
        if (! empty($specific_period_days)) {
            
            if ($target == "form") {
                foreach ($items as $patient => $data_arr) {
                    foreach ($data_arr as $day => $shortcut_arr) {
                        if (in_array($day, $specific_period_days[$patient])) {
                            
                            foreach ($shortcut_arr as $shortcut => $sh_data) {
                                if ($sh_data['qty'] == "1") {
                                    $master_data[$patient][$shortcut][date('Y-m-d', strtotime($day))] = $sh_data;
                                }
                            }
                        }
                    }
                }
            } elseif ($target == "invoice") {
                foreach ($items as $patient => $data_arr) {
                    foreach ($data_arr as $day => $shortcut_arr) {
                        if (in_array($day, $specific_period_days[$patient])) {
                            foreach ($shortcut_arr as $shortcut => $sh_data) {
                                $master_data[$patient][$shortcut][date('Y-m-d', strtotime($day))] = $sh_data;
                            }
                        }
                    }
                }
            }
        }
        
        // dd($patient_days2locationtypes,$master_data);
//         dd($master_data,$monthly_fee_days);
        return $master_data;
    }
}

?>