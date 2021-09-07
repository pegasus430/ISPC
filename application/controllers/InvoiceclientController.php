<?php
/**
 * 
 * @author  Nov 19, 2020  ancuta
 * ISPC-2312
 * ISPC-2746
 */
class InvoiceclientController extends Pms_Controller_Action {
    
    
    public function init()
    {
        // ISPC-2609 Ancuta 03.09.2020
        $this->user_print_jobs = 1;
        //
        //ERROR REPORTING
//         ini_set('display_errors', 1);
//         ini_set('display_startup_errors', 1);
//         error_reporting(E_ALL);
        
    }
    public function __StartPrintJobs(){
        $appInfo = Zend_Registry::get('appInfo');
        $app_path  = 	isset($appInfo['appCronPath']) && !empty($appInfo['appCronPath']) ? $appInfo['appCronPath'] : false;
        
        $function_path = $app_path.'/cron/processprintjobs';
        popen('curl -s '.$function_path.' &', 'r');
    }

    
    
    public function patientlistoldAction()
    {
        
        if($_REQUEST['flg'] == "notemplate")
        {
            $this->view->error_no_template = $this->view->translate('client_has_no_template');
        }
        
        $clientid = $this->clientid;
        $userid = $this->userid;
        $patientmaster = new PatientMaster();
        
        //get allowed client invoices
        $client_allowed_invoice = ClientInvoicePermissions::get_client_allowed_invoice($clientid);
        $this->view->allowed_invoice = $client_allowed_invoice[0];
        
        if(!$client_allowed_invoice[0])
        {
            // 				echo '<div id="errorPrevilege_ErrorMsg" align="center" class="err"><b>There is no client invoice type set</b></div>';
            // TODO-1310
            echo '<div id="errorPrevilege_ErrorMsg" align="center" class="err"><b>'.$this->view->translate('There is no client invoice type set').'</b></div>';
            return;
        }
        
        //TODO-2788 Ancuta 06.01.2020
        if($client_allowed_invoice[0] == 'hospiz_invoice')
        {
            echo '<div id="errorPrevilege_ErrorMsg" align="center" class="err"><b>'.$this->view->translate('Client invoice type -Hospz register- does not use this page, invoices are generated from patient menu').'</b></div>';
            return;
        }
        //--
        
        $invoice_links = Pms_CommonData::invoices_links();
        $this->view->invoice_links = $invoice_links;
        
        //construct months selector array START
        $start_period = '2010-01-01';
        $end_period = date('Y-m-d', time());
        $period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
        
        foreach($period_months_array as $k_month => $v_month)
        {
            $month_select_array[$v_month] = $v_month;
        }
        //construct months selector array END
        //check if a month is selected START
        if(strlen($_REQUEST['list']) == '0')
        {
            $selected_month = end($month_select_array);
        }
        else
        {
            $selected_month = $month_select_array[$_REQUEST['list']];
        }
        
        $this->view->selected_month = $selected_month;
        $this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));
        
        if(!function_exists('cal_days_in_month'))
        {
            $month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
        }
        else
        {
            $month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
        }
        
        $months_details[$selected_month]['start'] = $selected_month . "-01";
        $months_details[$selected_month]['days_in_month'] = $month_days;
        $months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
        
        $month_days_arr = PatientMaster::getDaysInBetween($selected_month . "-01", $selected_month . "-" . $month_days);
        
        array_walk($month_days_arr, function(&$value) {
            $value = date("d.m.Y", strtotime($value));
        });
            $months_details[$selected_month]['days'] = $month_days_arr;
            //check if a month is selected END
            //construct month_selector START
            $attrs['onChange'] = 'changeMonth(this.value);';
            $attrs['class'] = 'select_month_rehnung_patients';
            $this->view->months_selector = $this->view->formSelect("select_month", $selected_month, $attrs, $month_select_array);
            //construct month_selector END
            
            // TODO-1310
            if($this->getRequest()->isPost() && !empty($client_allowed_invoice[0]))
            {
                if($_POST['selected_patient'])
                {
                    $epids_ipids = Pms_CommonData::get_multiple_ipids($_POST['selected_patient']);
                    
                    //loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)
                    if($_POST['selected_patient_period'])
                    {
                        $selected_sapv_falls_ipids[] = '99999999999';
                        $selected_fall_ipids[] = '99999999999';
                        
                        foreach($_POST['selected_patient'] as $k_sel_pat => $v_sel_pat)
                        {
                            //ISPC-2480 Lore 29.11.2019
                            // verify if have already invoice for that period ONLY for sh_invoice ???
                            $have_inv_arr = array();
                            
                            if ($client_allowed_invoice[0] == 'sh_invoice'){
                                
                                $adm_id = substr($_POST['selected_patient_period'][$v_sel_pat],10);
                                
                                $have_inv = Doctrine_Query::create()
                                ->select('*')
                                ->from('ShInvoices')
                                ->where('isdelete = 0')
                                ->andWhere('admissionid = ?', $adm_id);
                                $have_inv_arr = $have_inv->fetchArray();
                            }
                            
                            if (empty($have_inv_arr)){
                                $ipids[] = $epids_ipids[$v_sel_pat];
                                
                                $params['nosapvperiod'][$epids_ipids[$v_sel_pat]] = '0';
                                
                                $period_id_exploded = explode('_', $_POST['selected_patient_period'][$v_sel_pat]);
                                
                                //construct array with patients which have admission period selected
                                if(count($period_id_exploded) == "2" && $period_id_exploded[0] == "admission")
                                {
                                    $admission_fall[$epids_ipids[$v_sel_pat]] = $period_id_exploded[1];
                                }
                                
                                //ISPC-2461
                                //construct array with patients which have quarter period selected
                                if(count($period_id_exploded) == "2" && $period_id_exploded[0] == "quarter")
                                {
                                    $quarter_fall[$epids_ipids[$v_sel_pat]] = $period_id_exploded[1];
                                }
                                
                                if($_POST['selected_patient_period'][$v_sel_pat] != '0' && count($period_id_exploded) == "1")
                                {
                                    $selected_sapv_falls_ipids[] = $epids_ipids[$v_sel_pat];
                                    $selected_sapv_falls[$epids_ipids[$v_sel_pat]] = $_POST['selected_patient_period'][$v_sel_pat];
                                }
                                else if(count($period_id_exploded) == "1")
                                {
                                    $selected_fall_ipids[] = $epids_ipids[$v_sel_pat];
                                    $selected_fall[$epids_ipids[$v_sel_pat]] = $months_details[$selected_month];
                                }
                            }
                            
                        }
                        
                        //get patients sapvs last fall
                        if($selected_sapv_falls_ipids)
                        {
                            $patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($selected_sapv_falls_ipids, true);
                            foreach($selected_sapv_falls as $k_ipid => $fall_id)
                            {
                                $patients_sapv[$k_ipid] = $fall_id;
                                $patients_selected_periods[$k_ipid] = $patients_sapv_periods[$k_ipid][$fall_id];
                                $params['nosapvperiod'][$k_ipid] = '0';
                                $params['period'] = $patients_selected_periods;
                            }
                        }
                        
                        //patient days
                        $conditions['client'] = $this->clientid;
                        $conditions['ipids'] = $ipids;
                        $conditions['periods'][0]['start'] = '2009-01-01';
                        $conditions['periods'][0]['end'] = date('Y-m-d');
                        
                        $sql = 'e.epid, p.ipid, e.ipid,';
                        $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
                        $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
                        $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
                        $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
                        $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
                        $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
                        
                        //be aware of date d.m.Y format here
                        $patient_days = Pms_CommonData::patients_days($conditions, $sql);
                        
                        //rewrite the periods array if the period is entire month not sapv fall
                        $patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
                        
                        foreach($patients_sapv_periods as $k_sapv_ipid => $v_sapvs)
                        {
                            foreach($v_sapvs as $k_sapv_id => $v_sapv_data)
                            {
                                if(empty($sapv_days[$v_sapv_data['ipid']]))
                                {
                                    $sapv_days[$v_sapv_data['ipid']] = array();
                                }
                                
                                $sapv_days[$v_sapv_data['ipid']] = array_merge($sapv_days[$v_sapv_data['ipid']], $v_sapv_data['days']);
                                $sapv_days[$v_sapv_data['ipid']] = array_values(array_unique($sapv_days[$v_sapv_data['ipid']]));
                            }
                        }
                        
                        
                        foreach($ipids as $k_ipid => $v_ipid)
                        {
                            if(!in_array($v_ipid, $selected_sapv_falls_ipids))
                            {
                                //								var_dump(array_key_exists($v_ipid, $admission_fall));
                                if(array_key_exists($v_ipid, $admission_fall))
                                {
                                    $selected_period[$v_ipid] = $patient_days[$v_ipid]['active_periods'][$admission_fall[$v_ipid]];
                                    
                                    array_walk($selected_period[$v_ipid], function(&$value) {
                                        $value = date("Y-m-d", strtotime($value));
                                    });
                                        
                                        $selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($selected_period[$v_ipid]['start'], $selected_period[$v_ipid]['end']);
                                        
                                        array_walk($selected_period[$v_ipid]['days'], function(&$value) {
                                            $value = date("d.m.Y", strtotime($value));
                                        });
                                            
                                            $params['nosapvperiod'][$v_ipid] = '1';
                                            $params['selected_period'][$v_ipid] = $selected_period[$v_ipid];
                                            $params['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
                                            
                                            
                                            array_walk($params['selected_period'][$v_ipid]['days'], function(&$value) {
                                                $value = date("d.m.Y", strtotime($value));
                                            });
                                                
                                                //exclude outside admission falls days from sapv!
                                                if(empty($sapv_days[$v_ipid]))
                                                {
                                                    $sapv_days[$v_ipid] = array();
                                                }
                                                
                                                if(empty($params['selected_period'][$v_ipid]['days']))
                                                {
                                                    $params['selected_period'][$v_ipid]['days'] = array();
                                                }
                                                $patient_active_sapv_days[$v_ipid] = array_intersect($params['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
                                                $params['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
                                                
                                                $start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
                                                $end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
                                                
                                                $start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
                                                $end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
                                                
                                                //get all days of all sapvs in a period
                                                $params['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
                                                $params['period'][$v_ipid] = $selected_period[$v_ipid];
                                                
                                                //									$start_sapv_dmy = $patient_active_sapv_days[$v_ipid][0];
                                                //									$end_sapv_dmy = end($patient_active_sapv_days[$v_ipid]);
                                                
                                                $params['period'][$v_ipid]['start'] = $start_dmy;
                                                $params['period'][$v_ipid]['end'] = $end_dmy;
                                                
                                                $last_sapv_data['ipid'] = $v_ipid;
                                                $last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
                                                $last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
                                                $sapv_last_require_data[] = $last_sapv_data;
                                                
                                                $params['admissionid'][$v_ipid] = $admission_fall[$v_ipid];
                                }
                                //ISPC-2461
                                elseif(array_key_exists($v_ipid, $quarter_fall))
                                {
                                    // 								    dd($quarter_fall);
                                    
                                    
                                    $post_q = $quarter_fall[$v_ipid];
                                    $post_q_arr = explode("/",$post_q);
                                    $q_no = (int)$post_q_arr[0];
                                    $q_year = (int)$post_q_arr[1];
                                    
                                    // 								    $period_days_arr[$v_ipid] = array();
                                    $q_per = array();
                                    $quarter_start = "";
                                    $quarter_end = "";
                                    
                                    $q_per = Pms_CommonData::get_dates_of_quarter($q_no,$q_year,'Y-m-d');
                                    $quarter_start = $q_per['start'];
                                    $quarter_end = $q_per['end'];
                                    
                                    $selected_period[$v_ipid] = array();
                                    $selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($quarter_start, $quarter_end);
                                    $selected_period[$v_ipid]['start'] = $quarter_start;
                                    $selected_period[$v_ipid]['end'] = $quarter_end;
                                    
                                    array_walk($selected_period[$v_ipid]['days'], function(&$value) {
                                        $value = date("d.m.Y", strtotime($value));
                                    });
                                        
                                        $params['nosapvperiod'][$v_ipid] = '1';
                                        $params['selected_period'][$v_ipid] = $selected_period[$v_ipid];
                                        $params['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
                                        
                                        
                                        array_walk($params['selected_period'][$v_ipid]['days'], function(&$value) {
                                            $value = date("d.m.Y", strtotime($value));
                                        });
                                            
                                            //exclude outside admission falls days from sapv!
                                            if(empty($sapv_days[$v_ipid]))
                                            {
                                                $sapv_days[$v_ipid] = array();
                                            }
                                            
                                            if(empty($params['selected_period'][$v_ipid]['days']))
                                            {
                                                $params['selected_period'][$v_ipid]['days'] = array();
                                            }
                                            $patient_active_sapv_days[$v_ipid] = array_intersect($params['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
                                            $params['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
                                            
                                            $start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
                                            $end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
                                            
                                            $start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
                                            $end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
                                            
                                            //get all days of all sapvs in a period
                                            $params['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
                                            $params['period'][$v_ipid] = $selected_period[$v_ipid];
                                            
                                            //									$start_sapv_dmy = $patient_active_sapv_days[$v_ipid][0];
                                            //									$end_sapv_dmy = end($patient_active_sapv_days[$v_ipid]);
                                            
                                            $params['period'][$v_ipid]['start'] = $start_dmy;
                                            $params['period'][$v_ipid]['end'] = $end_dmy;
                                            
                                            $last_sapv_data['ipid'] = $v_ipid;
                                            $last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
                                            $last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
                                            $sapv_last_require_data[] = $last_sapv_data;
                                            
                                            $params['quarterid'][$v_ipid] = $quarter_fall[$v_ipid];
                                }
                                else
                                {
                                    
                                    $start_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
                                    $end_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
                                    
                                    $params['nosapvperiod'][$v_ipid] = '1';
                                    $params['selected_period'][$v_ipid] = $months_details[$selected_month];
                                    $params['sapv_in_period'][$v_ipid] = array_values($months_details[$selected_month]['days']);
                                    $params['existing_sapv_days'][$v_ipid] = array_values($months_details[$selected_month]['days']);
                                    $params['period'][$v_ipid] = $months_details[$selected_month];
                                    $params['period'][$v_ipid]['start'] = $start_dmy;
                                    $params['period'][$v_ipid]['end'] = $end_dmy;
                                    
                                    $last_sapv_data['ipid'] = $v_ipid;
                                    $last_sapv_data['start_period'] = date('Y-m-d', strtotime($start_dmy));
                                    $last_sapv_data['end_period'] = date('Y-m-d', strtotime($end_dmy));
                                    $sapv_last_require_data[] = $last_sapv_data;
                                }
                            }
                        }
                        
                        
                        //						}
                        $all_patients_sapvs = SapvVerordnung::get_all_sapvs($ipids);
                        if($sapv_last_require_data)
                        {
                            $last_sapvs_in_period = SapvVerordnung::get_multiple_last_sapvs_inperiod($sapv_last_require_data, true, true);
                        }
                        
                        foreach($all_patients_sapvs as $k_sapv => $v_sapv)
                        {
                            if(empty($sapv_days_overall[$v_sapv['ipid']]))
                            {
                                $sapv_days_overall[$v_sapv['ipid']] = array();
                            }
                            
                            
                            $start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
                            
                            if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00')
                            {
                                $end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
                            }
                            else
                            {
                                $end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
                            }
                            
                            //FIND A WAY TO GET MULTIPLE LAST SAPV IN EACH PERIOD FOR EACH PATIENT
                            if($last_sapvs_in_period[$v_sapv['ipid']])
                            {
                                $params['period'][$v_sapv['ipid']] = array_merge($params['period'][$v_sapv['ipid']], $last_sapvs_in_period[$v_sapv['ipid']]);
                            }
                            
                            $sapv_days_overall[$v_sapv['ipid']] = array_merge($sapv_days_overall[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end));
                            array_walk($sapv_days_overall[$v_sapv['ipid']], function(&$value) {
                                $value = date("d.m.Y", strtotime($value));
                            });
                                $sapv_days_overall[$v_sapv['ipid']] = array_values(array_unique($sapv_days_overall[$v_sapv['ipid']]));
                        }
                        
                        foreach($last_sapvs_in_period as $k_sapvs => $v_sapvs)
                        {
                            foreach($v_sapvs as $k_sapvp => $v_sapvp)
                            {
                                $startp = date('Y-m-d', strtotime($v_sapvp['verordnungam']));
                                
                                if($v_sapvp['status'] == '1' && $v_sapvp['verorddisabledate'] != '0000-00-00 00:00:00')
                                {
                                    $endp = date('Y-m-d', strtotime($v_sapvp['verorddisabledate']));
                                }
                                else
                                {
                                    $endp = date('Y-m-d', strtotime($v_sapvp['verordnungbis']));
                                }
                                if(empty($period_sapv_alldays[$v_sapvp['ipid']]))
                                {
                                    $period_sapv_alldays[$v_sapvp['ipid']] = array();
                                }
                                $period_sapv_alldays[$v_sapvp['ipid']] = array_merge($period_sapv_alldays[$v_sapvp['ipid']], PatientMaster::getDaysInBetween($startp, $endp));
                            }
                        }
                        
                        
                        $params['period_sapvs_alldays'] = $period_sapv_alldays;
                        $params['sapv_overall'] = $sapv_days_overall;
                    }
                    
                    
                    $params['ipids'] = $ipids;
                    //					$params['patient_sapvs'] = $patients_sapv;
                    $params['patient_days'] = $patient_days;
                    $params['get_pdf'] = '0';
                    $params['only_pdf'] = 0;
                    $params['invoice_type'] = $client_allowed_invoice[0];
                    
                    
                    //TODO-3112 - Lore 23.04.2020
                    //get invoices type allowed from menu permissions
                    $client_menu_perms = Doctrine_Query::create()
                    ->select('*')
                    ->from('MenuClient')
                    ->andWhere('clientid = "' . $clientid . '"');
                    $client_menu_permssions = $client_menu_perms->fetchArray();
                    
                    $allowed_menu_links = array();
                    if(!empty($client_menu_permssions)){
                        
                        foreach($client_menu_permssions as $val)
                        {
                            $menu_perms[] = $val['menu_id'];
                        }
                        
                        $menus_cl = Doctrine_Query::create()
                        ->select('id, menu_link, parent_id, isdelete')
                        ->from('Menus m')
                        ->where('m.isdelete = "0"')
                        ->andWhereIn("m.id", $menu_perms)
                        ->andWhere('m.isdelete = 0')
                        ->andWhere('m.forsuperadmin = 0')
                        ->andWhere('m.menu_link LIKE "%invoice/%" or m.menu_link LIKE "%invoicenew/%" or m.menu_link LIKE "%internalinvoice/%"')
                        ->orderBy('m.sortorder ASC');
                        $menus_cl_arr = $menus_cl->fetchArray();
                        
                        foreach($menus_cl_arr as $k_menu => $v_menu)
                        {
                            if(strlen($v_menu['menu_link']) > 0)
                            {
                                $allowed_menu_links[] = $v_menu['menu_link'];
                            }
                        }
                    }
                    //.
                    
                    //					print_r($client_allowed_invoice);
                    
                    switch($client_allowed_invoice[0])
                    {
                        case "bayern_sapv_invoice":
                            $this->bayern_sapv_invoice($params);
                            
                            //TODO-3112 Lore 23.04.2020
                            if(in_array('invoicenew/bayerninvoices', $allowed_menu_links)){
                                $this->redirect(APP_BASE . 'invoicenew/bayerninvoices');
                            }else {
                                $this->redirect(APP_BASE . 'invoicenew/invoicesnew');
                            }
                            //.
                            //$this->redirect(APP_BASE . 'invoicenew/invoicesnew');
                            exit;
                            break;
                            
                        case "sh_invoice":
                            $this->anlage14_invoice($params);
                            $this->redirect(APP_BASE . 'invoicenew/invoicesnew');
                            exit;
                            break;
                            
                        case "bw_medipumps_invoice":
                            $this->bwmedipumpsinvoice($params);
                            $this->redirect(APP_BASE . 'invoicenew/invoicesnew');
                            exit;
                            break;
                            
                        case "bw_sapv_invoice_new":
                            $this->bwsapvsinvoice($params);
                            $this->redirect(APP_BASE . 'invoicenew/invoicesnew');
                            exit;
                            break;
                            
                        case "rlp_invoice":
                            $this->generate_rlpinvoice($params);
                            $this->redirect(APP_BASE . 'invoicenew/invoicesnew');
                            exit;
                            break;
                            
                        case "bre_kinder_invoice":
                        case "nr_invoice":
                        case "demstepcare_invoice": // ISPC-2461
                            $this->generate_systeminvoice($params);
                            $this->redirect(APP_BASE . 'invoicenew/invoicesnew');
                            exit;
                            break;
                            
                        default:
                            exit;
                            break;
                    }
                }
            }
    }
    
    public function fetchpatientlistoldAction()
    {
        $clientid = $this->clientid;
        $userid = $this->userid;
        
        //get allowed client invoices
        $client_allowed_invoice = ClientInvoicePermissions::get_client_allowed_invoice($clientid);
        $invoice_type = $client_allowed_invoice[0];

        $this->view->allowed_invoice = $client_allowed_invoice[0];
        
        
        // TODO-1310
        if(  ! $client_allowed_invoice[0] || $client_allowed_invoice[0] == 'hospiz_invoice')
        {//TODO-2788 Ancuta 06.01.2020 - added hospiz_invoice contition- as this type of invoice does not use - this page to generate invoices
            
            $response['msg'] = "Success";
            $response['error'] = "";
            $response['callBack'] = "callBack";
            $response['callBackParameters'] = array();
            $response['callBackParameters']['newinvoicepatientlist'] = "";
            
            echo json_encode($response);
            exit;
        }
        
        $client_data = Client::getClientDataByid($this->clientid);
        $billing_method = $client_data[0]['billing_method'];
        // ISPC-2286 - 4) invoices will be generated on a monthly basis.
        if($invoice_type == "nr_invoice"){
            $billing_method  = "month";
        }
        // ISPC-2461 - allow only quart
        elseif($invoice_type == "demstepcare_invoice"){
            $billing_method  = "quarter";
        }
        //--
        $this->view->billing_method = $billing_method;
        
        
        $this->view->rowspan_rows = "1";
        if($billing_method == "both")
        {
            if($invoice_type == "demstepcare_invoice"){
                $this->view->rowspan_rows = "5";
            } else{
                $this->view->rowspan_rows = "4";
            }
        }
        else if($billing_method == "sapv" || $billing_method == "month" || $billing_method == "admission" )
        {
            $this->view->rowspan_rows = "2";
        }
        else if($billing_method == "quarter" )
        {
            $this->view->rowspan_rows = "2";
        }
        else
        {
            $this->view->rowspan_rows = "1";
        }
        
        //construct months selector array START
        $start_period = '2010-01-01';
        $end_period = date('Y-m-d', time());
        $period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
        
        foreach($period_months_array as $k_month => $v_month)
        {
            $month_select_array[$v_month] = $v_month;
        }
        //construct months selector array END
        //check if a month is selected START
        if(strlen($_REQUEST['list']) == '0')
        {
            $selected_month = end($month_select_array);
        }
        else
        {
            $selected_month = $month_select_array[$_REQUEST['list']];
        }
        $this->view->selected_month = $selected_month;
        
        if(!function_exists('cal_days_in_month'))
        {
            $month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
        }
        else
        {
            $month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
        }
        
        $months_details[$selected_month]['start'] = $selected_month . "-01";
        $months_details[$selected_month]['days_in_month'] = $month_days;
        $months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
        $this->view->selected_month_details = $months_details[$selected_month];
        //check if a month is selected END
        //sort and ordering START
        $columnarray = array(
            "epid" => "e.epid",
            "fn" => 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.first_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci',
            "ln" => 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci',
            "adm" => "a.start",
            "dis" => "a.end",
        );
        
        if(strlen($_REQUEST['clm']) == '0')
        {
            $sortby = 'ln';
        }
        else
        {
            $sortby = $_REQUEST['clm'];
        }
        
        if(strlen($_REQUEST['ord']) == '0')
        {
            $order = 'ASC';
        }
        else
        {
            $order = $_REQUEST['ord'];
        }
        
        
        $orderarray = array("ASC" => "DESC", "DESC" => "ASC");
        $this->view->order = $orderarray[$order];
        $this->view->{$sortby . "order"} = $orderarray[$order];
        $x = "TRIM(CONVERT(CONVERT(AES_DECRYPT(p.first_name, '" . Zend_Registry::get('salt') . "') using utf8) using latin1)) COLLATE latin1_german2_ci";
        $search_sql = "(TRIM(CONVERT(CONVERT(AES_DECRYPT(p.first_name, '" . Zend_Registry::get('salt') . "') using utf8) using latin1)) COLLATE latin1_german2_ci like '%" . trim($_REQUEST['val']) . "%' or TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, '" . Zend_Registry::get('salt') . "') using utf8) using latin1)) COLLATE latin1_german2_ci like '%" . trim($_REQUEST['val']) . "%' or e.epid like '%" . trim($_REQUEST['val']) . "%' )";
        
        //sort and ordering END
        
        $active_ipids_count = Pms_CommonData::patients_active('count(*)', $this->clientid, $months_details, false, $columnarray[$sortby], $order, $search_sql); // BW->SH patient list
        
        $limit = '9999';
        $page = $_REQUEST['pgno'];
        $sql = Pms_CommonData::sql_getters('patients_active');
        $active_ipids = Pms_CommonData::patients_active($sql, $this->clientid, $months_details, false, $columnarray[$sortby], $order, $search_sql, $limit, $page);
        
        $active_ipids_array[] = '99999999999999999';
        foreach($active_ipids as $k_active => $v_active)
        {
            $active_ipids_array[] = $v_active['ipid'];
        }
        
        //take all patients details
        $conditions['client'] = $clientid;
        $conditions['ipids'] = $active_ipids_array;
        $conditions['periods'][0]['start'] = '2009-01-01';
        $conditions['periods'][0]['end'] = date('Y-m-d');
        $conditions['include_standby'] = true;// TODO-2873 Ancuta 13.02.2020 [add standby condition, for patients thata are NOW standby but had active periods]
        
        $sql = 'e.epid, p.ipid, e.ipid,';
        $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
        $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
        $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
        $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
        $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
        $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
        
        //be aware of date d.m.Y format here
        $patient_days = Pms_CommonData::patients_days($conditions, $sql);
        
        // TODO-2873 Ancuta 13.02.2020
        $fullstandby= array();
        foreach($patient_days as $k_ipid => $v_pat_data)
        {
            foreach($v_pat_data['active_periods'] as $v_period_id => $v_period)
            {
                
                if($v_pat_data['details']['isstandby'] == '1'){
                    foreach($v_pat_data['standby_periods'] as $s_per_id => $s_per){
                        if($s_per['start'] == $v_period['start'] &&  $s_per['end'] == $v_period['end']){
                            $fullstandby[$k_ipid][] = $v_period['start'].$v_period['end'];
                        }
                    }
                }
            }
        }
        // --
        
        $patients_active_days = array();
        foreach($patient_days as $k_ipid => $v_pat_data)
        {
            $patients_active_days[$k_ipid] = $v_pat_data['real_active_days'];
            
            //sort invoice Lore 10.03.2020
            $ksort_v_pat_data = $v_pat_data['active_periods'];
            ksort($ksort_v_pat_data);
            
            foreach($ksort_v_pat_data as $v_period_id => $v_period)
            {
                // TODO-2873 Ancuta 13.02.2020
                $period_ident = 0;
                $period_ident = $v_period['start'].$v_period['end'];
                
                if($period_ident!=0 && !in_array($period_ident,$fullstandby[$k_ipid]) )
                {
                    $v_period['days'] = PatientMaster::getDaysInBetween($v_period['start'], $v_period['end']);
                    $patients_admissions_periods[$k_ipid][$v_period_id] = $v_period;
                    $patients_admissions_periods[$k_ipid][$v_period_id]['completed'] = 1;
                    
                    // TODO-2315 16.07.2019
                    if( $v_pat_data['patient_active'][$v_period_id]['end'] == "0000-00-00" ){
                        $patients_admissions_periods[$k_ipid][$v_period_id]['completed'] = 0;
                    }
                }
                
            }
        }
        //dd($patients_admissions_periods);
        foreach($active_ipids as $k_active_patient => $v_active_patient)
        {
            $active_patients[$v_active_patient['ipid']] = $v_active_patient;
            
            $active_ipids_arr[] = $v_active_patient['ipid'];
            
            $last_period[$v_active_patient['ipid']] = end($v_active_patient['PatientActive']);
            
            $active_patients[$v_active_patient['ipid']]['admission_date'] = date('d.m.Y', strtotime($last_period[$v_active_patient['ipid']]['start']));
            
            if($last_period[$v_active_patient['ipid']]['end'] != "0000-00-00")
            {
                $active_patients[$v_active_patient['ipid']]['discharge_date'] = date('d.m.Y', strtotime($last_period[$v_active_patient['ipid']]['end']));
            }
            else
            {
                $active_patients[$v_active_patient['ipid']]['discharge_date'] = "-";
            }
            $active_patients[$v_active_patient['ipid']]['id'] = $v_active_patient['PatientMaster']['id'];
        }
        
        // 			dd($active_patients);
        //check what was invoiced
        switch($client_allowed_invoice[0])
        {
            case "bayern_sapv_invoice":
                $invoiced_sapv_ids = BayernInvoicesNew::get_bay_invoiced_sapvs($active_ipids_arr);
                break;
                
            case "sh_invoice":
                $invoiced_sapv_ids = ShInvoices::get_sh_invoiced_sapvs($active_ipids_arr);
                break;
                
            case "bw_medipumps_invoice":
                $invoiced_sapv_ids = MedipumpsInvoicesNew::get_mp_invoiced_sapvs($active_ipids_arr);
                break;
            case "bw_sapv_invoice_new":
                $invoiced_sapv_ids = BwInvoicesNew::get_bw_invoiced_sapvs($active_ipids_arr);
                break;
            case "rlp_invoice":
                $invoiced_sapv_ids = RlpInvoices::get_rlp_invoiced_sapvs($active_ipids_arr,$clientid);//TODO-2997 Ancuta 11.03.2020
                break;
                
            case "bre_kinder_invoice":
                $invoiced_sapv_ids = InvoiceSystem::get_invoiced_sapvs("bre_kinder_invoice",$active_ipids_arr);
                break;
                
                // ISPC-2286
            case "nr_invoice":
                $invoiced_sapv_ids = InvoiceSystem::get_invoiced_sapvs("nr_invoice",$active_ipids_arr);
                break;
                
            case "demstepcare_invoice": // ISPC-2461
                $invoiced_sapv_ids = InvoiceSystem::get_invoiced_sapvs("demstepcare_invoice",$active_ipids_arr);
                break;
                
            default:
                exit;
                break;
        }
        // 			dd($invoiced_sapv_ids);
        // 			dd($client_allowed_invoice);
        //			print_r($invoiced_sapv_ids);
        //			exit;
        if($invoiced_sapv_ids)
        {
            $this->view->invoiced_sapv_ids = $invoiced_sapv_ids['sapv'];
            $this->view->invoiced_fall_ids = $invoiced_sapv_ids['fall'];
            
            //TODO-2820 Plz check: new invoices not marked as green if they are created Ancuta 17.01.2020
            if($client_allowed_invoice[0] == 'sh_invoice'){
                $this->view->invoiced_fall_ids = $invoiced_sapv_ids['fall_full'];
            }
            //--
            $this->view->invoiced_admissions_ids = $invoiced_sapv_ids['admission'];
            $this->view->invoiced_quarter_ids = $invoiced_sapv_ids['quarter'];
        }
        
        $patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($active_ipids_arr);
        
        //9641931c87a04669eaa4f352add7a6d6cd7de643
        
        //ISPC-2461 create qurters
        // 			dd($patients_active_days['9641931c87a04669eaa4f352add7a6d6cd7de643']);
        $patient_quart_arr = array();
        
        foreach($patients_active_days as $ipid=>$patient_active_days){
            
            //$patient_active_days_sort = ksort($patient_active_days);
            $month ="";
            $yearQuarter="";
            foreach($patient_active_days as $date){
                $month = date("n", strtotime($date));
                $yearQuarter = ceil($month / 3);
                $patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['q'] = '0'.$yearQuarter.'/'.date("Y", strtotime($date));
                $patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['q_ident'] = '0'.$yearQuarter.'_'.date("Y", strtotime($date));
                $patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['q_no'] = $yearQuarter;
                $patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['q_year'] = date("Y", strtotime($date));
                $patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['days'][] =  $date ;
                
                
                
                $q++;
            }
        }
        
        foreach($patient_quart_arr as $pat_ipid=>$q_id_data){
            /*                  if ($pat_ipid == '7975a3a95ccd355bdd3e7e2d62fbe637d948de73'){
             dd($q_id_data);
             } */
            foreach($q_id_data as $q_id=>$q_info){
                usort($q_info['days'], array(new Pms_Sorter(), "_date_compare"));
                $patient_quart_arr[$pat_ipid][$q_id]['start'] = $q_info['days'][0];
                $patient_quart_arr[$pat_ipid][$q_id]['end'] = end($q_info['days']);
                
                
                
                $patient_quart_arr[$pat_ipid][$q_id]['completed'] = 1; // ADD  CHECKES
            }
        }
        
        $this->view->{"style" . $_GET['pgno']} = "active";
        
        $grid = new Pms_Grid($active_patients, 1, $active_ipids_count[0]['count'], "listnewinvoicepatients.html");
        
        
        $grid->admission_periods = $patients_admissions_periods;
        $grid->sapv_periods = $patients_sapv_periods;
        $grid->quarter_periods = $patient_quart_arr;
        $this->view->invoice_type = $client_allowed_invoice[0];
        $this->view->newinvoicepatientsgrid = $grid->renderGrid();
        $this->view->navigation = $grid->dotnavigation("newinvoicepatientsnavigation.html", 5, $page, $limit);
        
        $response['msg'] = "Success";
        $response['error'] = "";
        $response['callBack'] = "callBack";
        $response['callBackParameters'] = array();
        $response['callBackParameters']['patientlist'] = $this->view->render('invoiceclient/fetchpatientlist.html');
        
        echo json_encode($response);
        exit;
    }
    
    //ISPC-2746 Carmen 25.11.2020
    public function patientlistAction()
    {
    
    	if($_REQUEST['flg'] == "notemplate")
    	{
    		$this->view->error_no_template = $this->view->translate('client_has_no_template');
    	}
    		
    	if(!$this->clientid)
    	{
    		$this->_redirect(APP_BASE . "error/noclient");
    		exit;
    	}
    	
    	$clientid = $this->clientid;
    	$userid = $this->userid;
    	$patientmaster = new PatientMaster();
    
    	//get allowed client invoices
    	// if multiple - chose first one - and allow user to select whic invoices to see
    	$client_allowed_invoices = ClientInvoiceMultiplePermissions::get_client_allowed_invoices($clientid);
    	if(empty($client_allowed_invoices)){
    	    $this->_redirect(APP_BASE . "error/previlege");
    	    exit;
    	}
    	//construct invoice type selector START
    	$attrs = array();
    	$attrs['onChange'] = 'change_invoice_type(this.value);';
    	$attrs['class'] = 'invoice_type';
    	
    	//construct month_selector END
    	if(count($client_allowed_invoices) == "1"){
    	    $invoice_type = end($client_allowed_invoices);
    	    $this->view->invoice_type_selector = "";
    	    
    	} else{
    	    if(strlen($_REQUEST['invoice_type']) == '0')
    	    {
    	        $invoice_type = end($client_allowed_invoices);
    	    }
    	    else
    	    {
    	        $invoice_type = $_REQUEST['invoice_type'];
    	    }
    	    
    	    
    	    $client_allowed_invoices_tr = array();
    	    foreach($client_allowed_invoices as $inv=>$inv_type_name){
    	        //if(!in_array($inv,array('hospiz_invoice','by_invoice')))
    	        $client_allowed_invoices_tr[$inv] = $this->translate($inv_type_name.'_label');
    	    }
    	    $this->view->invoice_type_selector = $this->view->formSelect("invoice_type", $invoice_type, $attrs, $client_allowed_invoices_tr);
    	}

    	$this->view->invoice_type = $invoice_type;
    	$this->view->allowed_invoice = $invoice_type;
    	
    	// NOT DONE YET 
    	/* if(in_array ($invoice_type, array('hospiz_invoice','by_invoice') ) ) 
    	{
    	    echo '<div id="errorPrevilege_ErrorMsg" align="center" class="err"><b>'.$this->view->translate('Client invoice type - '.$invoice_type.'- does not use this page, invoices are generated from patient menu').'</b></div>';
    	    return;
    	} */ 
    	
    	$invoice_links = Pms_CommonData::invoices_links2new();
    	$this->view->invoice_links = $invoice_links;
    
    	//construct months selector array START
    	$start_period = '2010-01-01';
    	$end_period = date('Y-m-d', time());
    	$period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
    
    	foreach($period_months_array as $k_month => $v_month)
    	{
    		$month_select_array[$v_month] = $v_month;
    	}
    	//construct months selector array END
    	//check if a month is selected START
    	if(strlen($_REQUEST['list']) == '0')
    	{
    		$selected_month = end($month_select_array);
    	}
    	else
    	{
    		$selected_month = $month_select_array[$_REQUEST['list']];
    	}
    
    	$this->view->selected_month = $selected_month;
    	$this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));
    
    	if(!function_exists('cal_days_in_month'))
    	{
    		$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
    	}
    	else
    	{
    		$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
    	}
    
    	$months_details[$selected_month]['start'] = $selected_month . "-01";
    	$months_details[$selected_month]['days_in_month'] = $month_days;
    	$months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
    
    	$month_days_arr = PatientMaster::getDaysInBetween($selected_month . "-01", $selected_month . "-" . $month_days);
    
    	array_walk($month_days_arr, function(&$value) {
    		$value = date("d.m.Y", strtotime($value));
    	});
    	$months_details[$selected_month]['days'] = $month_days_arr;
    	//check if a month is selected END
    	//construct month_selector START
    	$attrs['onChange'] = 'changeMonth(this.value);';
    	$attrs['class'] = 'select_month_rehnung_patients';
    	$this->view->months_selector = $this->view->formSelect("select_month", $selected_month, $attrs, $month_select_array);
    	//construct month_selector END
    			
    	//$client_allowed_invoice[0] = 'bw_sgbv_invoice';
//     	$client_allowed_invoice[0] = 'he_invoice';
//     	$this->view->allowed_invoice = $client_allowed_invoice[0];
    		
    	if(!empty($_REQUEST['patient']))
    	{
    	    $params = array();
    		$ipid = Pms_CommonData::get_ipid_from_epid($_REQUEST['patient'], $this->clientid);
    		$params['ipids'] = array($ipid);
    		if($_REQUEST['sapvid'])
    		{
    			$params[$ipid]['period_type'] = 'sapvid';
    			$params[$ipid]['selected_period'] = $_REQUEST['sapvid'];
    		}
    			
    		if($_REQUEST['list'])
    		{
    			$params[$ipid]['period_type'] = 'list';
    			$params[$ipid]['selected_period'] = $_REQUEST['list'];
    		}
    			
    		if($_REQUEST['admission'])
    		{
    			$params[$ipid]['period_type'] = 'admission';
    			$params[$ipid]['selected_period'] = $_REQUEST['admission'];
    		}
    			
    		if($_REQUEST['quarter'])
    		{
				$params[$ipid]['period_type'] = 'quarter';
    			$params[$ipid]['selected_period'] = $_REQUEST['quarter'];
    		}
    			
    		$params['get_pdf'] = '1';
    		$params['only_pdf'] = (int) $_REQUEST['only_invoice'];
    		$params['invoice_type'] = $invoice_type;
    		$params[$ipid]['stornopdf'] = (int) $_REQUEST['stornopdf'];
    		$params[$ipid]['stornoid'] = (int) $_REQUEST['stornoid'];
    				
    		switch($invoice_type)
    		{
    			case "bw_sgbv_invoice":
    				$this->generatebwsgbvinvoice($params);
    				//$this->redirect(APP_BASE . 'invoice/allinvoices');
    				exit;
    				break;
    						
    			case "bw_sgbxi_invoice":
    				$params['visit_id'] = $_REQUEST['visit_id'];
    				$this->generatebwsgbxiinvoice($params);
    				//$this->redirect(APP_BASE . 'invoice/allinvoices');
    				exit;
    				break;
     				
    			case "he_invoice":
    				$this->generateheinvoice($params);
    				//$this->redirect(APP_BASE . 'invoice/allinvoices');
    				exit;
    				break;
   						
    			case "rpinvoice":    				
    				$this->generaterpinvoice($params);
    				//$this->redirect(APP_BASE . 'invoice/allinvoices');
    				exit;
    				break;
    				
    			case "hospiz_invoice":    				
    				$this->generatehospizinvoice($params);
    				//$this->redirect(APP_BASE . 'invoice/allinvoices');
    				exit;
    				break;
    				
    			case "by_invoice":
    				$this->generatebyinvoice($params);
    				//$this->redirect(APP_BASE . 'invoice/allinvoices');
    				exit;
    				break;
    						
    						
     			default:
    				exit;
    				break;
    		}
    	}
    	//var_dump($client_allowed_invoice); exit;
    	// TODO-1310
    	if($this->getRequest()->isPost() && !empty($invoice_type))
    	{
    		if($_POST['selected_patient'])
    		{
    			$epids_ipids = Pms_CommonData::get_multiple_ipids($_POST['selected_patient']);
    				
    			$paramsnew = array();
    			$paramsnew['ipids'] = array_values($epids_ipids);
    			$paramsnew['get_pdf'] = '0';
    			$paramsnew['only_pdf'] = 0;
    			$paramsnew['invoice_type'] = $invoice_type;
    			
    			//loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)
    			if($_POST['selected_patient_period'])
    			{
    				foreach($_POST['selected_patient'] as $k_sel_pat => $v_sel_pat)
    				{	
    					$period_id_exploded = explode('_', $_POST['selected_patient_period'][$v_sel_pat]);
    							
    					//construct array with patients which have admission period selected
    					if(count($period_id_exploded) == "2" && $period_id_exploded[0] == "admission")
    					{
    					    $paramsnew[$epids_ipids[$v_sel_pat]]['period_type'] = 'admission';
    					    $paramsnew[$epids_ipids[$v_sel_pat]]['selected_period'] = $period_id_exploded[1];
    					}
    							
    					//ISPC-2461
    					//construct array with patients which have quarter period selected
    					if(count($period_id_exploded) == "2" && $period_id_exploded[0] == "quarter")
    					{
    					    $paramsnew[$epids_ipids[$v_sel_pat]]['period_type'] = 'quarter';
    					    $paramsnew[$epids_ipids[$v_sel_pat]]['selected_period'] = $period_id_exploded[1];
    					}
    							
    					if($_POST['selected_patient_period'][$v_sel_pat] != '0' && count($period_id_exploded) == "1")
    					{
    					    $paramsnew[$epids_ipids[$v_sel_pat]]['period_type'] = 'sapvid';		
    					    $paramsnew[$epids_ipids[$v_sel_pat]]['selected_period'] = $_POST['selected_patient_period'][$v_sel_pat];
    					}
    					else if(count($period_id_exploded) == "1")
    					{
    					    $paramsnew[$epids_ipids[$v_sel_pat]]['period_type'] = 'list';
    					    $paramsnew[$epids_ipids[$v_sel_pat]]['selected_period'] = $selected_month;
    					}
    				}
    			}
    			
    			
    			
    			//##############################
    			// OLD PARAMS
    			//##############################
    			$params = array();
    			//loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)
    			if($_POST['selected_patient_period'])
    			{
    			    $selected_sapv_falls_ipids[] = '99999999999';
    			    $selected_fall_ipids[] = '99999999999';
    			    
    			    foreach($_POST['selected_patient'] as $k_sel_pat => $v_sel_pat)
    			    {
    			        //ISPC-2480 Lore 29.11.2019
    			        // verify if have already invoice for that period ONLY for sh_invoice ???
    			        $have_inv_arr = array();
    			        
    			        if ($client_allowed_invoice[0] == 'sh_invoice'){
    			            
    			            $adm_id = substr($_POST['selected_patient_period'][$v_sel_pat],10);
    			            
    			            $have_inv = Doctrine_Query::create()
    			            ->select('*')
    			            ->from('ShInvoices')
    			            ->where('isdelete = 0')
    			            ->andWhere('admissionid = ?', $adm_id);
    			            $have_inv_arr = $have_inv->fetchArray();
    			        }
    			        
    			        if (empty($have_inv_arr)){
    			            $ipids[] = $epids_ipids[$v_sel_pat];
    			            
    			            $params['nosapvperiod'][$epids_ipids[$v_sel_pat]] = '0';
    			            
    			            $period_id_exploded = explode('_', $_POST['selected_patient_period'][$v_sel_pat]);
    			            
    			            //construct array with patients which have admission period selected
    			            if(count($period_id_exploded) == "2" && $period_id_exploded[0] == "admission")
    			            {
    			                $admission_fall[$epids_ipids[$v_sel_pat]] = $period_id_exploded[1];
    			            }
    			            
    			            //ISPC-2461
    			            //construct array with patients which have quarter period selected
    			            if(count($period_id_exploded) == "2" && $period_id_exploded[0] == "quarter")
    			            {
    			                $quarter_fall[$epids_ipids[$v_sel_pat]] = $period_id_exploded[1];
    			            }
    			            
    			            if($_POST['selected_patient_period'][$v_sel_pat] != '0' && count($period_id_exploded) == "1")
    			            {
    			                $selected_sapv_falls_ipids[] = $epids_ipids[$v_sel_pat];
    			                $selected_sapv_falls[$epids_ipids[$v_sel_pat]] = $_POST['selected_patient_period'][$v_sel_pat];
    			            }
    			            else if(count($period_id_exploded) == "1")
    			            {
    			                $selected_fall_ipids[] = $epids_ipids[$v_sel_pat];
    			                $selected_fall[$epids_ipids[$v_sel_pat]] = $months_details[$selected_month];
    			            }
    			        }
    			        
    			    }
    			    
    			    //get patients sapvs last fall
    			    if($selected_sapv_falls_ipids)
    			    {
    			        $patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($selected_sapv_falls_ipids, true);
    			        foreach($selected_sapv_falls as $k_ipid => $fall_id)
    			        {
    			            $patients_sapv[$k_ipid] = $fall_id;
    			            $patients_selected_periods[$k_ipid] = $patients_sapv_periods[$k_ipid][$fall_id];
    			            $params['nosapvperiod'][$k_ipid] = '0';
    			            $params['period'] = $patients_selected_periods;
    			        }
    			    }
    			    
    			    //patient days
    			    $conditions['client'] = $this->clientid;
    			    $conditions['ipids'] = $ipids;
    			    $conditions['periods'][0]['start'] = '2009-01-01';
    			    $conditions['periods'][0]['end'] = date('Y-m-d');
    			    
    			    $sql = 'e.epid, p.ipid, e.ipid,';
    			    $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    			    $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    			    $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    			    $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    			    $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    			    $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    			    
    			    //be aware of date d.m.Y format here
    			    $patient_days = Pms_CommonData::patients_days($conditions, $sql);
    			    
    			    //rewrite the periods array if the period is entire month not sapv fall
    			    $patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
    			    
    			    foreach($patients_sapv_periods as $k_sapv_ipid => $v_sapvs)
    			    {
    			        foreach($v_sapvs as $k_sapv_id => $v_sapv_data)
    			        {
    			            if(empty($sapv_days[$v_sapv_data['ipid']]))
    			            {
    			                $sapv_days[$v_sapv_data['ipid']] = array();
    			            }
    			            
    			            $sapv_days[$v_sapv_data['ipid']] = array_merge($sapv_days[$v_sapv_data['ipid']], $v_sapv_data['days']);
    			            $sapv_days[$v_sapv_data['ipid']] = array_values(array_unique($sapv_days[$v_sapv_data['ipid']]));
    			        }
    			    }
    			    
    			    
    			    foreach($ipids as $k_ipid => $v_ipid)
    			    {
    			        if(!in_array($v_ipid, $selected_sapv_falls_ipids))
    			        {
    			            //								var_dump(array_key_exists($v_ipid, $admission_fall));
    			            if(array_key_exists($v_ipid, $admission_fall))
    			            {
    			                $selected_period[$v_ipid] = $patient_days[$v_ipid]['active_periods'][$admission_fall[$v_ipid]];
    			                
    			                array_walk($selected_period[$v_ipid], function(&$value) {
    			                    $value = date("Y-m-d", strtotime($value));
    			                });
    			                    
    			                    $selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($selected_period[$v_ipid]['start'], $selected_period[$v_ipid]['end']);
    			                    
    			                    array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    			                        $value = date("d.m.Y", strtotime($value));
    			                    });
    			                        
    			                        $params['nosapvperiod'][$v_ipid] = '1';
    			                        $params['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    			                        $params['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    			                        
    			                        
    			                        array_walk($params['selected_period'][$v_ipid]['days'], function(&$value) {
    			                            $value = date("d.m.Y", strtotime($value));
    			                        });
    			                            
    			                            //exclude outside admission falls days from sapv!
    			                            if(empty($sapv_days[$v_ipid]))
    			                            {
    			                                $sapv_days[$v_ipid] = array();
    			                            }
    			                            
    			                            if(empty($params['selected_period'][$v_ipid]['days']))
    			                            {
    			                                $params['selected_period'][$v_ipid]['days'] = array();
    			                            }
    			                            $patient_active_sapv_days[$v_ipid] = array_intersect($params['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    			                            $params['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    			                            
    			                            $start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    			                            $end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    			                            
    			                            $start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    			                            $end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    			                            
    			                            //get all days of all sapvs in a period
    			                            $params['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    			                            $params['period'][$v_ipid] = $selected_period[$v_ipid];
    			                            
    			                            //									$start_sapv_dmy = $patient_active_sapv_days[$v_ipid][0];
    			                            //									$end_sapv_dmy = end($patient_active_sapv_days[$v_ipid]);
    			                            
    			                            $params['period'][$v_ipid]['start'] = $start_dmy;
    			                            $params['period'][$v_ipid]['end'] = $end_dmy;
    			                            
    			                            $last_sapv_data['ipid'] = $v_ipid;
    			                            $last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    			                            $last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    			                            $sapv_last_require_data[] = $last_sapv_data;
    			                            
    			                            $params['admissionid'][$v_ipid] = $admission_fall[$v_ipid];
    			            }
    			            //ISPC-2461
    			            elseif(array_key_exists($v_ipid, $quarter_fall))
    			            {
    			                // 								    dd($quarter_fall);
    			                
    			                
    			                $post_q = $quarter_fall[$v_ipid];
    			                $post_q_arr = explode("/",$post_q);
    			                $q_no = (int)$post_q_arr[0];
    			                $q_year = (int)$post_q_arr[1];
    			                
    			                // 								    $period_days_arr[$v_ipid] = array();
    			                $q_per = array();
    			                $quarter_start = "";
    			                $quarter_end = "";
    			                
    			                $q_per = Pms_CommonData::get_dates_of_quarter($q_no,$q_year,'Y-m-d');
    			                $quarter_start = $q_per['start'];
    			                $quarter_end = $q_per['end'];
    			                
    			                $selected_period[$v_ipid] = array();
    			                $selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($quarter_start, $quarter_end);
    			                $selected_period[$v_ipid]['start'] = $quarter_start;
    			                $selected_period[$v_ipid]['end'] = $quarter_end;
    			                
    			                array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    			                    $value = date("d.m.Y", strtotime($value));
    			                });
    			                    
    			                    $params['nosapvperiod'][$v_ipid] = '1';
    			                    $params['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    			                    $params['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    			                    
    			                    
    			                    array_walk($params['selected_period'][$v_ipid]['days'], function(&$value) {
    			                        $value = date("d.m.Y", strtotime($value));
    			                    });
    			                        
    			                        //exclude outside admission falls days from sapv!
    			                        if(empty($sapv_days[$v_ipid]))
    			                        {
    			                            $sapv_days[$v_ipid] = array();
    			                        }
    			                        
    			                        if(empty($params['selected_period'][$v_ipid]['days']))
    			                        {
    			                            $params['selected_period'][$v_ipid]['days'] = array();
    			                        }
    			                        $patient_active_sapv_days[$v_ipid] = array_intersect($params['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    			                        $params['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    			                        
    			                        $start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    			                        $end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    			                        
    			                        $start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    			                        $end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    			                        
    			                        //get all days of all sapvs in a period
    			                        $params['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    			                        $params['period'][$v_ipid] = $selected_period[$v_ipid];
    			                        
    			                        //									$start_sapv_dmy = $patient_active_sapv_days[$v_ipid][0];
    			                        //									$end_sapv_dmy = end($patient_active_sapv_days[$v_ipid]);
    			                        
    			                        $params['period'][$v_ipid]['start'] = $start_dmy;
    			                        $params['period'][$v_ipid]['end'] = $end_dmy;
    			                        
    			                        $last_sapv_data['ipid'] = $v_ipid;
    			                        $last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    			                        $last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    			                        $sapv_last_require_data[] = $last_sapv_data;
    			                        
    			                        $params['quarterid'][$v_ipid] = $quarter_fall[$v_ipid];
    			            }
    			            else
    			            {
    			                
    			                $start_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    			                $end_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    			                
    			                $params['nosapvperiod'][$v_ipid] = '1';
    			                $params['selected_period'][$v_ipid] = $months_details[$selected_month];
    			                $params['sapv_in_period'][$v_ipid] = array_values($months_details[$selected_month]['days']);
    			                $params['existing_sapv_days'][$v_ipid] = array_values($months_details[$selected_month]['days']);
    			                $params['period'][$v_ipid] = $months_details[$selected_month];
    			                $params['period'][$v_ipid]['start'] = $start_dmy;
    			                $params['period'][$v_ipid]['end'] = $end_dmy;
    			                
    			                $last_sapv_data['ipid'] = $v_ipid;
    			                $last_sapv_data['start_period'] = date('Y-m-d', strtotime($start_dmy));
    			                $last_sapv_data['end_period'] = date('Y-m-d', strtotime($end_dmy));
    			                $sapv_last_require_data[] = $last_sapv_data;
    			            }
    			        }
    			    }
    			    
    			    
    			    //						}
    			    $all_patients_sapvs = SapvVerordnung::get_all_sapvs($ipids);
    			    if($sapv_last_require_data)
    			    {
    			        $last_sapvs_in_period = SapvVerordnung::get_multiple_last_sapvs_inperiod($sapv_last_require_data, true, true);
    			    }
    			    
    			    foreach($all_patients_sapvs as $k_sapv => $v_sapv)
    			    {
    			        if(empty($sapv_days_overall[$v_sapv['ipid']]))
    			        {
    			            $sapv_days_overall[$v_sapv['ipid']] = array();
    			        }
    			        
    			        
    			        $start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
    			        
    			        if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00')
    			        {
    			            $end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
    			        }
    			        else
    			        {
    			            $end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
    			        }
    			        
    			        //FIND A WAY TO GET MULTIPLE LAST SAPV IN EACH PERIOD FOR EACH PATIENT
    			        if($last_sapvs_in_period[$v_sapv['ipid']])
    			        {
    			            $params['period'][$v_sapv['ipid']] = array_merge($params['period'][$v_sapv['ipid']], $last_sapvs_in_period[$v_sapv['ipid']]);
    			        }
    			        
    			        $sapv_days_overall[$v_sapv['ipid']] = array_merge($sapv_days_overall[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end));
    			        array_walk($sapv_days_overall[$v_sapv['ipid']], function(&$value) {
    			            $value = date("d.m.Y", strtotime($value));
    			        });
    			            $sapv_days_overall[$v_sapv['ipid']] = array_values(array_unique($sapv_days_overall[$v_sapv['ipid']]));
    			    }
    			    
    			    foreach($last_sapvs_in_period as $k_sapvs => $v_sapvs)
    			    {
    			        foreach($v_sapvs as $k_sapvp => $v_sapvp)
    			        {
    			            $startp = date('Y-m-d', strtotime($v_sapvp['verordnungam']));
    			            
    			            if($v_sapvp['status'] == '1' && $v_sapvp['verorddisabledate'] != '0000-00-00 00:00:00')
    			            {
    			                $endp = date('Y-m-d', strtotime($v_sapvp['verorddisabledate']));
    			            }
    			            else
    			            {
    			                $endp = date('Y-m-d', strtotime($v_sapvp['verordnungbis']));
    			            }
    			            if(empty($period_sapv_alldays[$v_sapvp['ipid']]))
    			            {
    			                $period_sapv_alldays[$v_sapvp['ipid']] = array();
    			            }
    			            $period_sapv_alldays[$v_sapvp['ipid']] = array_merge($period_sapv_alldays[$v_sapvp['ipid']], PatientMaster::getDaysInBetween($startp, $endp));
    			        }
    			    }
    			    
    			    
    			    $params['period_sapvs_alldays'] = $period_sapv_alldays;
    			    $params['sapv_overall'] = $sapv_days_overall;
    			}
    			
    			$params['ipids'] = $ipids;
    			//					$params['patient_sapvs'] = $patients_sapv;
    			$params['patient_days'] = $patient_days;
    			$params['get_pdf'] = '0';
    			$params['only_pdf'] = 0;
    			$params['invoice_type'] = $client_allowed_invoice[0];
    			
    			
    			
    			
    			
    			
    			
    			
    			
    			include 'InvoicenewController.php';
    			$invoicenewController = new InvoicenewController($this->_request, $this->_response);
    			
    			$params['redirect2new'] = 1;
    			
    			switch($invoice_type)
    			{
    				case "bw_sgbv_invoice":
    					$this->generatebwsgbvinvoice($paramsnew);
//     					$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=bw_sgbv_invoice');
//     					exit;
    					break;
    							
    				/* case "bw_sgbxi_invoice":
    					$this->generate_bwsgbxiinvoice($params);
//     					$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=bw_sgbxi_invoice');
    					exit;
    					break; */
    							
    				case "he_invoice":
    				    $this->generateheinvoice($paramsnew);
//     					$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=he_invoice');
//     					exit;
    					break;
    							
    				case "rpinvoice":
    					$this->generaterpinvoice($paramsnew);
//     					$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=rpinvoice');
//     					exit;
    					break;  
    					
    				case "hospiz_invoice":
    					$this->generatehospizinvoice($paramsnew);
    					$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=hospiz_invoice');
    					exit;
    					break;
    
    				case "bayern_sapv_invoice":
    				    $invoicenewController->bayern_sapv_invoice($params);
    				    
    				    break;
    				    
    				case "sh_invoice":
    				    $invoicenewController->anlage14_invoice($params);
    				    break;
    				    
    				case "bw_medipumps_invoice":
    				    $invoicenewController->bwmedipumpsinvoice($params);
    				    break;
    				    
    				case "bw_sapv_invoice_new":
    				    $invoicenewController->bwsapvsinvoice($params);
    				    break;
    				    
    				case "rlp_invoice":
    				    $invoicenewController->generate_rlpinvoice($params);
    				    break;
    				    
    				case "bre_kinder_invoice":
    				case "nr_invoice":
    				case "demstepcare_invoice": // ISPC-2461
    				    $invoicenewController->generate_systeminvoice($params);
    				    break;
    				    
    				case "by_invoice":
    				  	$this->generatebyinvoice($paramsnew);
    				   	//$this->redirect(APP_BASE . 'invoice/allinvoices');
    				   	exit;
    				   	break;
    					
    				default:
    					exit;
    					break;
    			}
    			
    			$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type);
    			exit;
    		}
    	}
    }
    
    public function fetchpatientlistAction()
    {
    	$clientid = $this->clientid;
    	$userid = $this->userid;
    
    	//get allowed client invoices
    	
    	// if multiple - chose first one - and allow user to select whic invoices to see
    	if(isset($_REQUEST['invoice_type']) && strlen($_REQUEST['invoice_type']) > '0')
    	{
    	    $invoice_type = $_REQUEST['invoice_type'];
    	    
    	} else{
    	    
        	$client_allowed_invoices = ClientInvoiceMultiplePermissions::get_client_allowed_invoices($clientid);
        	if(count($client_allowed_invoices) == "1"){
        	    $invoice_type = end($client_allowed_invoices);
        	} else{
        	        $invoice_type = end($client_allowed_invoices);
        	}
    	}
    	
    	$this->view->invoice_type = $invoice_type;
    	$this->view->allowed_invoice = $invoice_type;
    	$this->view->invoice_type = $invoice_type;
    		
    	// NOT DONE YET FOR hospiz_invoice and by_invoice
    	/* if(  ! $invoice_type || 	in_array ($invoice_type, array('hospiz_invoice','by_invoice')) )
    	{
    
    		$response['msg'] = "Success";
    		$response['error'] = "";
    		$response['callBack'] = "callBack";
    		$response['callBackParameters'] = array();
    		$response['callBackParameters']['invoiceclientpatientlist'] = "";
    
    		echo json_encode($response);
    		exit;
    	} */
    
    	$client_data = Client::getClientDataByid($this->clientid);
    	$billing_method = $client_data[0]['billing_method'];
    	// ISPC-2286 - 4) invoices will be generated on a monthly basis.
    	if($invoice_type == "nr_invoice"){
    	    $billing_method  = "month";
    	}
    	// ISPC-2461 - allow only quart
    	elseif($invoice_type == "demstepcare_invoice"){
    	    $billing_method  = "quarter";
    	}
    	elseif($invoice_type == "rpinvoice"){
    	    $billing_method  = "sapv";
    	}
    	//--

    	$this->view->billing_method = $billing_method;
    
    
    	$this->view->rowspan_rows = "1";
    	if($billing_method == "both")
    	{
    		/* if($invoice_type == "demstepcare_invoice"){
    		 $this->view->rowspan_rows = "5";
    		 } else{ */
    		$this->view->rowspan_rows = "4";
    		//}
    	}
    	else if($billing_method == "sapv" || $billing_method == "month" || $billing_method == "admission" )
    	{
    		$this->view->rowspan_rows = "2";
    	}
    	else if($billing_method == "quarter" )
    	{
    		$this->view->rowspan_rows = "2";
    	}
    	else
    	{
    		$this->view->rowspan_rows = "1";
    	}
    	
    	//construct months selector array START
    	$start_period = '2010-01-01';
    	$end_period = date('Y-m-d', time());
    	$period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
    
    	foreach($period_months_array as $k_month => $v_month)
    	{
    		$month_select_array[$v_month] = $v_month;
    	}
    	//construct months selector array END
    	//check if a month is selected START
    	if(strlen($_REQUEST['list']) == '0')
    	{
    		$selected_month = end($month_select_array);
    	}
    	else
    	{
    		$selected_month = $month_select_array[$_REQUEST['list']];
    	}
    	$this->view->selected_month = $selected_month;
    
    	if(!function_exists('cal_days_in_month'))
    	{
    		$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
    	}
    	else
    	{
    		$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
    	}
    
    	$months_details = array();
    	$months_details[$selected_month]['start'] = $selected_month . "-01";
    	$months_details[$selected_month]['days_in_month'] = $month_days;
    	$months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
    	$months_details[$selected_month]['days'] =  PatientMaster::getDaysInBetween($months_details[$selected_month]['start'], $months_details[$selected_month]['end'],false,"d.m.Y");
    	$this->view->selected_month_details = $months_details[$selected_month];
    	//check if a month is selected END
    	//sort and ordering START
    	$columnarray = array(
    			"epid" => "e.epid",
    			"fn" => 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.first_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci',
    			"ln" => 'TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci',
    			"adm" => "a.start",
    			"dis" => "a.end",
    	);
    
    	if(strlen($_REQUEST['clm']) == '0')
    	{
    		$sortby = 'ln';
    	}
    	else
    	{
    		$sortby = $_REQUEST['clm'];
    	}
    
    	if(strlen($_REQUEST['ord']) == '0')
    	{
    		$order = 'ASC';
    	}
    	else
    	{
    		$order = $_REQUEST['ord'];
    	}
    
    
    	$orderarray = array("ASC" => "DESC", "DESC" => "ASC");
    	$this->view->order = $orderarray[$order];
    	$this->view->{$sortby . "order"} = $orderarray[$order];
    	$x = "TRIM(CONVERT(CONVERT(AES_DECRYPT(p.first_name, '" . Zend_Registry::get('salt') . "') using utf8) using latin1)) COLLATE latin1_german2_ci";
    	$search_sql = "(TRIM(CONVERT(CONVERT(AES_DECRYPT(p.first_name, '" . Zend_Registry::get('salt') . "') using utf8) using latin1)) COLLATE latin1_german2_ci like '%" . trim($_REQUEST['val']) . "%' or TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, '" . Zend_Registry::get('salt') . "') using utf8) using latin1)) COLLATE latin1_german2_ci like '%" . trim($_REQUEST['val']) . "%' or e.epid like '%" . trim($_REQUEST['val']) . "%' )";
    
    	//sort and ordering END
    
    	$active_ipids_count = Pms_CommonData::patients_active('count(*)', $this->clientid, $months_details, false, $columnarray[$sortby], $order, $search_sql);
    
    	$limit = '9999';
    	$page = $_REQUEST['pgno'];
    	$sql = Pms_CommonData::sql_getters('patients_active');
    	$active_ipids = Pms_CommonData::patients_active($sql, $this->clientid, $months_details, false, $columnarray[$sortby], $order, $search_sql, $limit, $page);
    
    	$active_ipids_array[] = '99999999999999999';
    	foreach($active_ipids as $k_active => $v_active)
    	{
    		$active_ipids_array[] = $v_active['ipid'];
    	}
    
    	//take all patients details
    	$conditions['client'] = $clientid;
    	$conditions['ipids'] = $active_ipids_array;
    	$conditions['periods'][0]['start'] = '2009-01-01';
    	$conditions['periods'][0]['end'] = date('Y-m-d');
    	$conditions['include_standby'] = true;// TODO-2873 Ancuta 13.02.2020 [add standby condition, for patients thata are NOW standby but had active periods]
    
    	$sql = 'e.epid, p.ipid, e.ipid,';
    	$sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    	$sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    	$sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    	$sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    	$sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    	$sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    
    	//be aware of date d.m.Y format here
    	$patient_days = Pms_CommonData::patients_days($conditions, $sql);
    
    	// TODO-2873 Ancuta 13.02.2020
    	$fullstandby= array();
    	foreach($patient_days as $k_ipid => $v_pat_data)
    	{
    		foreach($v_pat_data['active_periods'] as $v_period_id => $v_period)
    		{
    
    			if($v_pat_data['details']['isstandby'] == '1'){
    				foreach($v_pat_data['standby_periods'] as $s_per_id => $s_per){
    					if($s_per['start'] == $v_period['start'] &&  $s_per['end'] == $v_period['end']){
    						$fullstandby[$k_ipid][] = $v_period['start'].$v_period['end'];
    					}
    				}
    			}
    		}
    	}
    	// --
    
    	$patients_active_days = array();
    	foreach($patient_days as $k_ipid => $v_pat_data)
    	{
    		$patients_active_days[$k_ipid] = $v_pat_data['real_active_days'];
    			
    		//sort invoice Lore 10.03.2020
    		$ksort_v_pat_data = $v_pat_data['active_periods'];
    		ksort($ksort_v_pat_data);
    			
    		foreach($ksort_v_pat_data as $v_period_id => $v_period)
    		{
    			// TODO-2873 Ancuta 13.02.2020
    			$period_ident = 0;
    			$period_ident = $v_period['start'].$v_period['end'];
    
    			if($period_ident!=0 && !in_array($period_ident,$fullstandby[$k_ipid]) )
    			{
    				$v_period['days'] = PatientMaster::getDaysInBetween($v_period['start'], $v_period['end']);
    				$patients_admissions_periods[$k_ipid][$v_period_id] = $v_period;
    				$patients_admissions_periods[$k_ipid][$v_period_id]['completed'] = 1;
    					
    				// TODO-2315 16.07.2019
    				if( $v_pat_data['patient_active'][$v_period_id]['end'] == "0000-00-00" ){
    					$patients_admissions_periods[$k_ipid][$v_period_id]['completed'] = 0;
    				}
    			}
    
    		}
    	}
    	//dd($patients_admissions_periods);
    	foreach($active_ipids as $k_active_patient => $v_active_patient)
    	{
    		$active_patients[$v_active_patient['ipid']] = $v_active_patient;
    
    		$active_ipids_arr[] = $v_active_patient['ipid'];
    
    		$last_period[$v_active_patient['ipid']] = end($v_active_patient['PatientActive']);
    
    		$active_patients[$v_active_patient['ipid']]['admission_date'] = date('d.m.Y', strtotime($last_period[$v_active_patient['ipid']]['start']));
    
    		if($last_period[$v_active_patient['ipid']]['end'] != "0000-00-00")
    		{
    			$active_patients[$v_active_patient['ipid']]['discharge_date'] = date('d.m.Y', strtotime($last_period[$v_active_patient['ipid']]['end']));
    		}
    		else
    		{
    			$active_patients[$v_active_patient['ipid']]['discharge_date'] = "-";
    		}
    		$active_patients[$v_active_patient['ipid']]['id'] = $v_active_patient['PatientMaster']['id'];
    	}
    
    	// 			dd($active_patients);
    	//check what was invoiced
    	switch($invoice_type)
    	{
    	    
    	    case "bayern_sapv_invoice":
    	        $invoiced_sapv_ids = BayernInvoicesNew::get_bay_invoiced_sapvs($active_ipids_arr);
    	        break;
    	        
    	    case "sh_invoice":
    	        $invoiced_sapv_ids = ShInvoices::get_sh_invoiced_sapvs($active_ipids_arr);
    	        break;
    	        
    	    case "bw_medipumps_invoice":
    	        $invoiced_sapv_ids = MedipumpsInvoicesNew::get_mp_invoiced_sapvs($active_ipids_arr);
    	        break;
    	    case "bw_sapv_invoice_new":
    	        $invoiced_sapv_ids = BwInvoicesNew::get_bw_invoiced_sapvs($active_ipids_arr);
    	        break;
    	    case "rlp_invoice":
    	        $invoiced_sapv_ids = RlpInvoices::get_rlp_invoiced_sapvs($active_ipids_arr,$clientid);//TODO-2997 Ancuta 11.03.2020
    	        break;
    	        
    	    case "bre_kinder_invoice":
    	        $invoiced_sapv_ids = InvoiceSystem::get_invoiced_sapvs("bre_kinder_invoice",$active_ipids_arr);
    	        break;
    	        
    	        // ISPC-2286
    	    case "nr_invoice":
    	        $invoiced_sapv_ids = InvoiceSystem::get_invoiced_sapvs("nr_invoice",$active_ipids_arr);
    	        break;
    	        
    	    case "demstepcare_invoice": // ISPC-2461
    	        $invoiced_sapv_ids = InvoiceSystem::get_invoiced_sapvs("demstepcare_invoice",$active_ipids_arr);
    	        break;
    	        
    	    default:
    	       
    	        break;
    	}
   
    	if($invoiced_sapv_ids)
    	{
    		$this->view->invoiced_sapv_ids = $invoiced_sapv_ids['sapv'];
    		$this->view->invoiced_fall_ids = $invoiced_sapv_ids['fall'];
    
    		$this->view->invoiced_admissions_ids = $invoiced_sapv_ids['admission'];
    		$this->view->invoiced_quarter_ids = $invoiced_sapv_ids['quarter'];
    	}
    
    	$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($active_ipids_arr);

    	
    	$statuscolorsarray = SapvVerordnung::getDefaultStatusColors();
    	$current_month_sapv = array();
    	foreach($patients_sapv_periods as $ipid=>$sapv_id){
    	    foreach($sapv_id as $sapv_Data){
    	        if(array_intersect($sapv_Data['days'], $months_details[$selected_month]['days'])){
    	            $current_month_sapv[$ipid][$sapv_Data['period']]['values'] = implode(',',$sapv_Data['sh_types_arr']);
    	            $current_month_sapv[$ipid][$sapv_Data['period']]['color_status'] = $statuscolorsarray[$sapv_Data['status']];
    	        }
    	    }
    	}
    	
    	$patient_quart_arr = array();
    
    	foreach($patients_active_days as $ipid=>$patient_active_days){
    
    		$month ="";
    		$yearQuarter="";
    		foreach($patient_active_days as $date){
    			$month = date("n", strtotime($date));
    			$yearQuarter = ceil($month / 3);
    			$patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['q'] = '0'.$yearQuarter.'/'.date("Y", strtotime($date));
    			$patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['q_ident'] = '0'.$yearQuarter.'_'.date("Y", strtotime($date));
    			$patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['q_no'] = $yearQuarter;
    			$patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['q_year'] = date("Y", strtotime($date));
    			$patient_quart_arr[$ipid] ['0'.$yearQuarter.'/'.date("Y", strtotime($date))]['days'][] =  $date ;
    
    			$q++;
    		}
    	}
    
    	foreach($patient_quart_arr as $pat_ipid=>$q_id_data){
 
    		foreach($q_id_data as $q_id=>$q_info){
    			usort($q_info['days'], array(new Pms_Sorter(), "_date_compare"));
    			$patient_quart_arr[$pat_ipid][$q_id]['start'] = $q_info['days'][0];
    			$patient_quart_arr[$pat_ipid][$q_id]['end'] = end($q_info['days']);
    
    			$patient_quart_arr[$pat_ipid][$q_id]['completed'] = 1; // ADD  CHECKES
    		}
    	}
    
    	$this->view->{"style" . $_GET['pgno']} = "active";
    
    	$grid = new Pms_Grid($active_patients, 1, $active_ipids_count[0]['count'], "listinvoiceclientpatients.html");
    
    
    	$grid->admission_periods = $patients_admissions_periods;
    	$grid->sapv_periods = $patients_sapv_periods;
    	$grid->current_sapv_periods = $current_month_sapv;
    	$grid->quarter_periods = $patient_quart_arr;
    	$this->view->invoice_type = $invoice_type;
    	$this->view->invoiceclientpatientsgrid = $grid->renderGrid();
    	$this->view->navigation = $grid->dotnavigation("invoiceclientpatientsnavigation.html", 5, $page, $limit);
    
    	$response['msg'] = "Success";
    	$response['error'] = "";
    	$response['callBack'] = "callBack";
    	$response['callBackParameters'] = array();
    	$response['callBackParameters']['invoiceclientpatientlist'] = $this->view->render('invoiceclient/fetchpatientlist.html');
    
    	echo json_encode($response);
    	exit;
    }
    
    private function generatebwsgbvinvoice($params)
    {
    	if(isset($params['print_job']) && $params['print_job'] == '1'){
    		$this->_helper->layout->setLayout('layout_ajax');
    		$this->_helper->viewRenderer->setNoRender();
    	}
    		
    	setlocale(LC_ALL, 'de_DE.UTF-8');
    	//$logininfo = new Zend_Session_Namespace('Login_Info');
    	//$tm = new TabMenus();
    	$p_list = new PriceList();
    	$form_types = new FormTypes();
    	$sapvs = new SapvVerordnung();
    	$patientmaster = new PatientMaster();
    	//$sapvverordnung = new SapvVerordnung();
    	$pflege = new PatientMaintainanceStage();
    	$hi_perms = new HealthInsurancePermissions();
    	$phelathinsurance = new PatientHealthInsurance();
    	$healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
    	$boxes = new LettersTextBoxes();
    	$client = new Client();
    	$sgbvinvoices = new SgbvInvoices();
    	$ppun = new PpunIpid();
    	
    	if(isset($params) && !empty($params)){
    		$_REQUEST = $params;
    		$this->_helper->viewRenderer->setNoRender();
    	}
    	
    //var_dump($_REQUEST); exit;	
    	//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
    	$clientid = isset($_REQUEST['clientid']) && ! empty($_REQUEST['clientid']) ? $_REQUEST['clientid'] :  $this->clientid;
    	$userid = isset($_REQUEST['userid']) && ! empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $this->userid;
    
    	$ipids = $_REQUEST['ipids'];
    	
    	//load template data
    	$template_data = InvoiceTemplates::get_template($clientid, false, '1', $_REQUEST['invoice_type']);
    	
    	/* if(!$template_data && $_REQUEST['get_pdf'] == '1'){
    		$this->redirect(APP_BASE . 'invoiceclient/patientlist?flg=notemplate');
    		exit;
    	} */
    	 
    	if(!empty($ipids))
    	{
    		$letter_boxes_details = $boxes->client_letter_boxes($clientid);
    		$client_details = $client->getClientDataByid($clientid);
    		$this->view->client_details = $client_details[0];
    		 
    		$modules =  new Modules();
    		$clientModules = $modules->get_client_modules($clientid);
    		
	    	//patient days
	    	$conditions['client'] = $clientid;
	    	$conditions['ipids'] = $ipids;
	    	$conditions['periods'][0]['start'] = '2009-01-01';
	    	$conditions['periods'][0]['end'] = date('Y-m-d');
	    	 	
	    	$sql = 'e.epid, p.ipid, e.ipid,';
	    	$sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
	    	$sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
	    	$sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
	    	$sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
	    	$sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
	    	$sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
	    	 	
	    	//be aware of date d.m.Y format here
	    	$patient_days = Pms_CommonData::patients_days($conditions, $sql);
	    	//var_dump($patient_days); exit;
	    	$all_patients_periods = array();
	    	foreach($patient_days as $k_ipid => $patient_data)
	    	{
	    		//all patients periods
	    		$all_patients_periods = array_merge_recursive($all_patients_periods, $patient_data['active_periods']);
	    	
	    		//used in flatrate
	    		if(empty($patient_periods[$k_ipid]))
	    		{
	    			$patient_periods[$k_ipid] = array();
	    		}
	    	
	    		array_walk_recursive($patient_data['active_periods'], function(&$value) {
	    			$value = date("Y-m-d", strtotime($value));
	    		});
	    			$patient_periods[$k_ipid] = array_merge($patient_periods[$k_ipid], $patient_data['active_periods']);
	    			 
	    			//hospital days cs
	    			if(!empty($patient_data['hospital']['real_days_cs']))
	    			{
	    				$hospital_days_cs[$k_ipid] = $patient_data['hospital']['real_days_cs'];
	    				array_walk($hospital_days_cs[$k_ipid], function(&$value) {
	    					$value = date("Y-m-d", strtotime($value));
	    				});
	    			}
	    			 
	    			//hospiz days cs
	    			if(!empty($patient_data['hospiz']['real_days_cs']))
	    			{
	    				$hospiz_days_cs[$k_ipid] = $patient_data['hospiz']['real_days_cs'];
	    				array_walk($hospiz_days_cs[$k_ipid], function(&$value) {
	    					$value = date("Y-m-d", strtotime($value));
	    				});
	    			}
	    			 
	    			//real active days
	    			if(!empty($patient_data['real_active_days']))
	    			{
	    				$active_days_in_period_cs[$k_ipid] = $patient_data['real_active_days'];
	    				array_walk($active_days_in_period_cs[$k_ipid], function(&$value) {
	    					$value = date("Y-m-d", strtotime($value));
	    				});
	    			}
	    			 
	    			//treatment days
	    			if(!empty($patient_data['treatment_days']))
	    			{
	    				$treatment_days_cs[$k_ipid] = $patient_data['treatment_days'];
	    				array_walk($treatment_days_cs[$k_ipid], function(&$value) {
	    					$value = date("Y-m-d", strtotime($value));
	    				});
	    			}
	    			 
	    			//active days
	    			if(!empty($patient_data['active_days']))
	    			{
	    				$active_days[$k_ipid] = $patient_data['active_days'];
	    				array_walk($active_days[$k_ipid], function(&$value) {
	    					$value = date("Y-m-d", strtotime($value));
	    				});
	    			}
	    			 
	    			if(empty($hospital_days_cs[$k_ipid]))
	    			{
	    				$hospital_days_cs[$k_ipid] = array();
	    			}
	    			 
	    			if(empty($hospiz_days_cs[$k_ipid]))
	    			{
	    				$hospiz_days_cs[$k_ipid] = array();
	    			}
	    			 
	    			$hospital_hospiz_days_cs[$k_ipid] = array_merge($hospital_days_cs[$k_ipid], $hospiz_days_cs[$k_ipid]);
	    	}
	    	
	    	$all_patients_periods = array_values($all_patients_periods);
	    	
	    	foreach($all_patients_periods as $k_period => $v_period)
	    	{
		    	if(empty($months))
		    	{
		    		$months = array();
		    	}
	    	 
	    		$period_months = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], 'Y-m');
	    		$months = array_merge($months, $period_months);
	    	}
	    	$months = array_values(array_unique($months));
	    	
	    	foreach($months as $k_m => $v_m)
	    	{
	    		$months_unsorted[strtotime($v_m)] = $v_m;
	    	}
	    	ksort($months_unsorted);
	    	$months = array_values(array_unique($months_unsorted));
	    	
	    	foreach($months as $k_month => $v_month)
	    	{
		    	if(!function_exists('cal_days_in_month'))
		    	{
		    	$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
		    	}
		    	else
		    	{
		    	$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
		    	}
	    	 
		    	$months_details[$v_month]['start'] = $v_month . "-01";
		    	$months_details[$v_month]['days_in_month'] = $month_days;
		    	$months_details[$v_month]['end'] = $v_month . '-' . $month_days;
		    	 
		    	//$month_select_array[$v_month] = $v_month;
		    	$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
	    	}
	 	
	    	//loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)    	
	    	foreach($ipids as $k_sel_pat => $v_sel_pat)
	    	{
			    //get patients sapvs last fall
			    if($_REQUEST[$v_sel_pat]['period_type'] == 'sapvid')
			    {
			   		$selected_sapv_falls_ipids[] = $v_sel_pat;
			   		$selected_sapv_falls[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
			    }
			    //get patient month fall
			    if($_REQUEST[$v_sel_pat]['period_type'] == 'list')
			    {
			   		$selected_fall_ipids[] = $v_sel_pat;			   		
			   		$selected_month[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];			   		
			    }
			    if($_REQUEST[$v_sel_pat]['period_type'] == 'admission')
			    {
			   		$admission_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
			    }
			    if($_REQUEST[$v_sel_pat]['period_type'] == 'quarter')
			    {
			   		$quarter_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
			   	}	    	 
	    	}
    	 
    	 	$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($selected_sapv_falls_ipids, true);
	    	foreach($selected_sapv_falls as $k_ipid => $fall_id)
	    	{
		    	$patients_sapv[$k_ipid] = $fall_id;
		    	$patients_selected_periods[$k_ipid] = $patients_sapv_periods[$k_ipid][$fall_id];
		    	$_REQUEST['nosapvperiod'][$k_ipid] = '0';
		    	$_REQUEST['period'] = $patients_selected_periods;
	    	}
    	
    	 	//rewrite the periods array if the period is entire month not sapv fall
    	 	$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
    	
    	 	foreach($patients_sapv_periods as $k_sapv_ipid => $v_sapvs)
    	 	{
		    	 foreach($v_sapvs as $k_sapv_id => $v_sapv_data)
		    	 {
			    	 if(empty($sapv_days[$v_sapv_data['ipid']]))
			    	 {
			    	 	$sapv_days[$v_sapv_data['ipid']] = array();
			    	 }
			    	
			    	 $sapv_days[$v_sapv_data['ipid']] = array_merge($sapv_days[$v_sapv_data['ipid']], $v_sapv_data['days']);
			    	 $sapv_days[$v_sapv_data['ipid']] = array_values(array_unique($sapv_days[$v_sapv_data['ipid']]));
		    	 }
    	 	}
    	 
    	
	    	foreach($ipids as $k_ipid => $v_ipid)
	    	{
			    if(!in_array($v_ipid, $selected_sapv_falls_ipids))
			    {
				   	 if(array_key_exists($v_ipid, $admission_fall))
				   	 {
			    	 	$selected_period[$v_ipid] = $patient_days[$v_ipid]['active_periods'][$admission_fall[$v_ipid]];
			    	 	
				    	array_walk($selected_period[$v_ipid], function(&$value) {
				    	 $value = date("Y-m-d", strtotime($value));
				    	 });
			    	
			    	 	$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($selected_period[$v_ipid]['start'], $selected_period[$v_ipid]['end']);
			    	
				    	array_walk($selected_period[$v_ipid]['days'], function(&$value) {
				    	 $value = date("d.m.Y", strtotime($value));
				    	 });
			    	
				    	$_REQUEST['nosapvperiod'][$v_ipid] = '1';
				    	$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
				    	$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
			    	
			    	
				    	array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
				    	 $value = date("d.m.Y", strtotime($value));
				    	 });
			    	
				    	//exclude outside admission falls days from sapv!
				    	if(empty($sapv_days[$v_ipid]))
				    	{
				    		$sapv_days[$v_ipid] = array();
				    	}
			    	
				    	if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
				    	{
				    		$_REQUEST['selected_period'][$v_ipid]['days'] = array();
				    	}
			    	 	$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
			    	 	$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
			    	
			    	 	$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
			    	 	$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
			    	
			    	 	$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
			    	 	$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
			    	
				    	//get all days of all sapvs in a period
				    	$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
				    	$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
			    	
			    	
				    	$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
				    	$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
			    	
				    	$last_sapv_data['ipid'] = $v_ipid;
				    	$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
				    	$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
				    	$sapv_last_require_data[] = $last_sapv_data;
				    	
				    	$_REQUEST['admissionid'][$v_ipid] = $admission_fall[$v_ipid];
			    	}
			    	//ISPC-2461
			    	elseif(array_key_exists($v_ipid, $quarter_fall))
			    	{
				    	$post_q = $_REQUEST[$v_ipid]['selected_period'];
				    	$post_q_arr = explode("/",$post_q);
				    	$q_no = (int)$post_q_arr[0];
				    	$q_year = (int)$post_q_arr[1];
				    	 
				    	$q_per = array();
				    	$quarter_start = "";
				    	$quarter_end = "";
				    	
				    	$q_per = Pms_CommonData::get_dates_of_quarter($q_no,$q_year,'Y-m-d');
				    	$quarter_start = $q_per['start'];
				    	$quarter_end = $q_per['end'];
				    	
				    	$selected_period[$v_ipid] = array();
				    	$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($quarter_start, $quarter_end);
				    	$selected_period[$v_ipid]['start'] = $quarter_start;
				    	$selected_period[$v_ipid]['end'] = $quarter_end;
			    	
				    	array_walk($selected_period[$v_ipid]['days'], function(&$value) {
				    	 $value = date("d.m.Y", strtotime($value));
				    	 });
			    	
				    	$_REQUEST['nosapvperiod'][$v_ipid] = '1';
				    	$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
				    	$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
			    	
			    	
				    	array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
				    	 $value = date("d.m.Y", strtotime($value));
				    	 });
			    	 	
				    	//exclude outside admission falls days from sapv!
				    	if(empty($sapv_days[$v_ipid]))
				    	{
				    	 	$sapv_days[$v_ipid] = array();
				    	}
			    	
				    	if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
				    	{
				    		$_REQUEST['selected_period'][$v_ipid]['days'] = array();
				    	}
				    	$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
				    	$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
			    	
				    	$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
				    	$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
			    	
				    	$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
				    	$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
			    	
				    	//get all days of all sapvs in a period
				    	$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
				    	$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
			   	
				    	$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
				    	$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
			    	
				    	$last_sapv_data['ipid'] = $v_ipid;
				    	$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
				    	$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
				    	$sapv_last_require_data[] = $last_sapv_data;
				    	
				    	$_REQUEST['quarterid'][$v_ipid] = $quarter_fall[$v_ipid];
		    		}
			    	else
			    	{
			    	
				    	$start_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['start']));
				    	$end_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['end']));
			    	
				    	$_REQUEST['nosapvperiod'][$v_ipid] = '1';
				    	$_REQUEST['selected_period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
				    	$_REQUEST['sapv_in_period'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
				    	$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
				    	$_REQUEST['period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
				    	$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
				    	$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
			    	
				    	$last_sapv_data['ipid'] = $v_ipid;
				    	$last_sapv_data['start_period'] = date('Y-m-d', strtotime($start_dmy));
				    	$last_sapv_data['end_period'] = date('Y-m-d', strtotime($end_dmy));
				    	$sapv_last_require_data[] = $last_sapv_data;
			    	}
		    	}
	    	}
	   //print_r($_REQUEST); exit; 	
	    	//get all sapv details
	    	$all_sapvs = array();
	    	$all_sapvs = $sapvs->get_all_sapvs($ipids);
	    	
	    	$sapv2ipid = array();
	    	/* foreach($all_sapvs as $k=>$sdata){
	    		$sapv2ipid[$sdata['ipid']][] = $sdata;
	    	} */

	    	foreach($all_sapvs as $k_sapv => $v_sapv)
	    	{
	    		$sapv2ipid[$v_sapv['ipid']][] = $v_sapv;
	    		if(empty($sapv_days_overall[$v_sapv['ipid']]))
			    {
			    	$sapv_days_overall[$v_sapv['ipid']] = array();
			    }	    	
	    	
	    	 	$start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
	    	
		    	if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00')
		    	{
		    		$end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
		    	}
		    	else
		    	{
		    		$end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
		    	}
	    	
		    	//FIND A WAY TO GET MULTIPLE LAST SAPV IN EACH PERIOD FOR EACH PATIENT
		    	if($last_sapvs_in_period[$v_sapv['ipid']])
		    	{
		    		$_REQUEST['period'][$v_sapv['ipid']] = array_merge($_REQUEST['period'][$v_sapv['ipid']], $last_sapvs_in_period[$v_sapv['ipid']]);
		    	}
	    	
		    	$sapv_days_overall[$v_sapv['ipid']] = array_merge($sapv_days_overall[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end));
		    	array_walk($sapv_days_overall[$v_sapv['ipid']], function(&$value) {
		    	 $value = date("d.m.Y", strtotime($value));
		    	 });
	    	 	$sapv_days_overall[$v_sapv['ipid']] = array_values(array_unique($sapv_days_overall[$v_sapv['ipid']]));
	    	}
    	
	    	foreach($last_sapvs_in_period as $k_sapvs => $v_sapvs)
	    	{
			    foreach($v_sapvs as $k_sapvp => $v_sapvp)
			    {
		    	 	$startp = date('Y-m-d', strtotime($v_sapvp['verordnungam']));
		    	
			    	if($v_sapvp['status'] == '1' && $v_sapvp['verorddisabledate'] != '0000-00-00 00:00:00')
			    	{
			    		$endp = date('Y-m-d', strtotime($v_sapvp['verorddisabledate']));
			    	}
			    	else
			    	{
			    		$endp = date('Y-m-d', strtotime($v_sapvp['verordnungbis']));
			    	}
			    	if(empty($period_sapv_alldays[$v_sapvp['ipid']]))
			    	{
			    		$period_sapv_alldays[$v_sapvp['ipid']] = array();
			    	}
			    	$period_sapv_alldays[$v_sapvp['ipid']] = array_merge($period_sapv_alldays[$v_sapvp['ipid']], PatientMaster::getDaysInBetween($startp, $endp));
		    	 }
	    	 }
    	
    	
	    	 $_REQUEST['period_sapvs_alldays'] = $period_sapv_alldays;
	    	 $_REQUEST['sapv_overall'] = $sapv_days_overall;
	    	 
	    	 $current_period = array();
	    	 foreach($_REQUEST['period'] as $ipidp => $vipidp)
	    	 {
	    	 	$current_period[$ipidp] = $vipidp;
	    	 	if($_REQUEST['sapv_in_period'][$ipidp])
	    	 	{
	    	 		$sapv_in_period[$ipidp] = $_REQUEST['sapv_in_period'][$ipidp];
	    	 	}
	    	 	else 
	    	 	{
	    	 		$sapv_in_period[$ipidp] = array();
	    	 	}
	    	 }
	    	 
	    	//Healthinsurance
	    	$healthinsu_multi_array = array();
	    	$healthinsu_multi_array = $phelathinsurance->get_multiple_patient_healthinsurance($ipids, true);
	    	
	    	foreach($healthinsu_multi_array as $k_hi => $v_hi)
	    	{
	    		$hi_companyids[] = $v_hi['companyid'];
	    	}
	    	
	    	//get socialcode group prices assigned to the health insurance of the patient
	    	$hi_array = array();
	    	if(!empty($hi_companyids))
	    	{
		    	$hi_query = Doctrine_Query::create()
		    	->select('price_sheet, price_sheet_group')
		    	->from('HealthInsurance')
		    	->whereIn("id", $hi_companyids);
		    	$hi_array = $hi_query->fetchArray();
	    	}
    
    
	    	if(!empty($hi_array))
	    	{
	    		foreach($hi_array as $khi => $vhi)
	    		{
	    			$price_sheet_group[$vhi['id']] = $vhi['price_sheet_group'];
	    		}
	    	}
	    	//print_r($price_sheet_group); exit;
    		
	    	//multiple hi subdivisions && hi subdivisions permissions
	    	$divisions = HealthInsurancePermissions::getClientHealthInsurancePermissions($clientid);
	    	
	    	$bonuses_arr = array('n', 'h');
	    	
	    	//patientheathinsurance
	    	if($divisions)
	    	{
	    		/* foreach($healthinsu_multi_array as $k_hi => $v_hi)
	    		 {
	    		 $hi_companyids[] = $v_hi['companyid'];
	    		 } */
	    			
	    		$healthinsu_subdiv_arr = PatientHealthInsurance2Subdivisions::get_hi_subdivisions_multiple($hi_companyids);
	    	}
	    	
	    	$pathelathinsu = array();
	    	$patient_address = array();
	    	$hi_address = array();
	    	foreach($ipids as $k_ipid => $v_ipid)
	    	{
	    		$pathelathinsu[$v_ipid]['insurance_no'] = $healthinsu_multi_array[$v_ipid]['insurance_no'];
	    		if($healthinsu_multi_array[$v_ipid]['company_name'] != "")
	    		{
	    			$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company_name'];
	    		}
	    		else
	    		{
	    			$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company']['name'];
	    		}
	    			
	    		if($healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
	    		{
	    			//			get patient name and adress
	    			$patient_address[$v_ipid] = '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['first_name']) . ' ' . htmlspecialchars($patient_days[$v_ipid]['details']['last_name']) . '<br />';
	    			$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['street1']) . '<br />';
	    			$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['zip']) . ' ' . '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['city']);
	    		}
	    			
	    		if(!empty($healthinsu_subdiv_arr[$v_ipid]['1']['name']))
	    		{
	    			//get new SAPV hi address
	    			$hi_address[$v_ipid] = '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['name'] . '<br />';
	    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['insurance_provider'] . '<br />';
	    			//$hi_address .= '&nbsp;' . $healthinsu_subdiv_arr[1]['contact_person'] . '<br />';
	    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['street1'] . '<br />';
	    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['zip'] . ' ' . $healthinsu_subdiv_arr[$v_ipid][1]['city'];
	    
	    			$pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_subdiv_arr[$v_ipid][1]['iknumber'];
	    			$pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_subdiv_arr[$v_ipid][1]['kvnumber'];
	    		}
	    		else
	    		{
	    			//get old hi_address
	    			$hi_address[$v_ipid] = '&nbsp;' . $pathelathinsu[$v_ipid]['name'] . '<br />';
	    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_insurance_provider'] . '<br />';
	    			//$hi_address .= '&nbsp;' . $healthinsu_array[0]['ins_contactperson'] . '<br />';
	    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_street'] . '<br />';
	    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_zip'] . ' ' . $healthinsu_multi_array[$v_ipid]['ins_city'];
	    
	    			$pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_multi_array[$v_ipid]['institutskennzeichen'];
	    			$pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_multi_array[$v_ipid]['kvk_no'];
	    		}
	    		
	    		//new columns
	    		if($clientModules['90'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "0")
	    		{
	    			//get debtor number from patient healthinsurance
	    			if(strlen($healthinsu_multi_array[$v_ipid]['ins_debtor_number']) > '0')
	    			{
	    				$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['ins_debtor_number'];
	    			}
	    			else
	    			{
	    				$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['company']['debtor_number'];
	    			}
	    		}
	    		if($clientModules['88'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
	    		{
	    			//get ppun (private patient unique number)
	    			$ppun_number = $ppun->check_patient_ppun($v_ipid, $clientid);
	    			if($ppun_number)
	    			{
	    				$pathelathinsu[$v_ipid]['ppun']= $ppun_number['ppun'];
	    				$pathelathinsu[$v_ipid]['debitor_number']= $ppun_number['ppun'];
	    			}
	    		}
	    		//--
	    	}    		
    	
	    	//var_dump($patient_address); exit;    		
	    	// get pflegestufe for all
	    		
	    	$all_pflegestufe = array();
	    	$all_pflegestufe = Doctrine_Query::create()
	    	->select("*")
	    	->from('PatientMaintainanceStage')
	    	->whereIn("ipid", $ipids)
	    	->orderBy('fromdate,create_date asc')
	    	->fetchArray();
	    		
	    	$pflegesufe2ipid = array();
	    	foreach($all_pflegestufe as $k=>$pflg) {
	    		$pflegesufe2ipid[$pflg['ipid']][] = $pflg;
	    	}
	    	//print_R($pflegesufe2ipid); exit;
	    		
	    	//start sgbv data gathering
	    	$master_data = array();
	    	//get day shortcuts group
	    	$sapv_day_gr = Pms_CommonData::get_sgbv_day_groups();
	    	/* foreach($sapv_day_gr as $k_dgr => $v_dgr)
	    	 {
	    	 $master_data[$k_dgr] = array();
	    	 } */
	    	/* foreach($days_in_period as $k_period_day => $v_period_day)
	    	{
	    		$period_days_arr[$v_period_day] = array();
	    	} */
	    	//get socialcode groups
	    	$socialcodegroups = new SocialCodeGroups();
	    	$grouplist = $socialcodegroups->getCientSocialCodeGroups($clientid);
	    		
	    	foreach($grouplist as $k_group => $v_group)
	    	{
	    		$group_list[$v_group['id']] = $v_group;
	    		$group_list_order[$v_group['id']] = $v_group['group_order'];
	    	}
	    	$cf = new ContactForms();
	    	//get multiple contact forms
	    	$p_contactforms = $cf->get_multiple_contact_form_period($ipids);
	    	$contact_form_ids = array();
	    	$period_contact_form_date = array();
	    	foreach($p_contactforms as $k_ipid => $v_ipid)
	    	{
	    		foreach($v_ipid as $k_p_cf => $v_p_cf)
	    		{
	    			$contact_form_ids[] = $v_p_cf['id'];
	    			if($v_p_cf['billable_date'] != "0000-00-00 00:00:00")
	    			{
	    				$period_contact_form_date[$k_ipid][$v_p_cf['id']] = date('Y-m-d', strtotime($v_p_cf['billable_date']));
	    			}
	    		}
	    		//$period_contactforms[$v_p_cf['id']] = $v_p_cf;
	    	}
    		
	    	//get saved actions
	    	$sgbv_form = new FormBlockSgbv();
	    	if(!empty($contact_form_ids))
	    	{
	    		$initial_form_actions = $sgbv_form->getPatientsFormsSavedActions($contact_form_ids);
	    	}
	    	//var_dump($period_contact_form_date); exit;
	    	$pat_initial_forms_actions = array();
	    	foreach($initial_form_actions as $kifa => $vifa)
	    	{
	    		$pat_initial_forms_actions[$vifa['ipid']][] = $vifa;
	    	}
	    	
	    	//get patient sgbv forms
	    	$disabled_statuses = array("10", "6");
    		
	    	//allowed sgbv actions per days
	    	$a_sgbv_ids = array();
	    	$sgbv_alowed_days = array();
	    	$sgbvforms = new SgbvForms();
	    	$alowed_sgbvs = $sgbvforms->getallPatientSgbvForm($ipids, false, false, $disabled_statuses);
	    	$sgbv_form_items = new SgbvFormsItems();
	    	
	    	$allowed_sgbv_form_items_arr = $sgbv_form_items->getPatientsSgbvFormItems($ipids);
	    		
	    	foreach($allowed_sgbv_form_items_arr as $k_allowed_sgbv_item => $v_allowed_sgbv_item)
	    	{
	    		//free of charge
	    		if($v_allowed_sgbv_item['free_of_charge'] == '1')
	    		{
	    			$foc_sgbv2items[$v_allowed_sgbv_item['ipid']][$v_allowed_sgbv_item['sgbv_form_id']][] = $v_allowed_sgbv_item['action_id'];
	    			$sgbv_foc_days[$v_allowed_sgbv_item['ipid']][$v_allowed_sgbv_item['action_id']] = $patientmaster->getDaysInBetween($v_allowed_sgbv_item['valid_from'], $v_allowed_sgbv_item['valid_till']);
	    		}
	    		$allowed_sgbv2items[$v_allowed_sgbv_item['ipid']][$v_allowed_sgbv_item['sgbv_form_id']][] = $v_allowed_sgbv_item['action_id'];
	    			
	    		$items_rules[$v_allowed_sgbv_item['ipid']][$v_allowed_sgbv_item['sgbv_form_id']][$v_allowed_sgbv_item['action_id']]['day'] += $v_allowed_sgbv_item['per_day'];
	    		$items_rules[$v_allowed_sgbv_item['ipid']][$v_allowed_sgbv_item['sgbv_form_id']][$v_allowed_sgbv_item['action_id']]['week'] += $v_allowed_sgbv_item['per_week'];
	    			
	    		$items_valid_period[$v_allowed_sgbv_item['ipid']][$v_allowed_sgbv_item['sgbv_form_id']][$v_allowed_sgbv_item['action_id']] = $patientmaster->getDaysInBetween($v_allowed_sgbv_item['valid_from'], $v_allowed_sgbv_item['valid_till']);
	    	}
	    		
	    		
	    	foreach($items_valid_period as $k_ipid => $v_ipid)
	    	{
	    		foreach($v_ipid as $k_sgbv_form => $v_form_action_arr)
	    		{
	    			foreach($v_form_action_arr as $k_action => $v_action_days)
	    			{
	    				foreach($v_action_days as $k_day => $v_day)
	    				{
	    					$v_week = date('W', strtotime($v_day));
	    					$items_period_rules[$k_ipid][$k_action]['day'][$v_day] = $items_rules[$k_ipid][$k_sgbv_form][$k_action]['day'];
	    					$items_period_rules[$k_ipid][$k_action]['week'][$v_week] = $items_rules[$k_ipid][$k_sgbv_form][$k_action]['week'];
	    					$items_period_rules[$k_ipid][$k_action]['week_day'][$v_week][$v_day] = $items_rules[$k_ipid][$k_sgbv_form][$k_action]['week'];
	    					// form an array - with valid days of action :: TODO-1053 10.07.2017
	    					$items_period_rules[$k_ipid][$k_action]['valid_days'][] = $v_day;
	    				}
	    			}
	    		}
	    	}
    		
	    	//denied and free of charge sgbv actions per day
	    	//Latter change 07.05.13: get all sgbv items free of charge
	    	$d_sgbv_ids = array();
	    	$sgbv_denied_days = array();
	    	$denied_sgbvs = $sgbvforms->getallPatientSgbvForm($ipids, false, $disabled_statuses);
	    	$d_sgbv_ids = array_values(array_unique($d_sgbv_ids));
	    		
	    	$sgbv_form_items_arr = $sgbv_form_items->getPatientsSgbvFormItems($ipids);
	    		
	    	foreach($sgbv_form_items_arr as $k_sgbv_item => $v_sgbv_item)
	    	{
	    		$sgbv2items[$v_sgbv_item['ipid']][$v_sgbv_item['sgbv_form_id']][] = $v_sgbv_item['action_id'];
	    	}
	    		
	    	$socialcode_actions = new SocialCodeActions();
	    	$sgbv_actions = $socialcode_actions->official_actions_details($clientid);
	    		
	    		
	    	$sgbv_actions_details = $sgbv_actions['action_name'];
	    	$sgbv_action2groups = $sgbv_actions['group'];
    	
	    	$allowed_sgbv_in_period = array();
	    	foreach($ipids as $k_ipid => $v_ipid)
	    	{
	    		$master_allowed_days[$v_ipid] = array();
	    		$master_denied_days[$v_ipid] = array();
	    		$sapv_approved[$v_ipid] = array();
	    			
	    		foreach($sapv2ipid[$v_ipid] as $s_ipid => $sdetails){
	    			foreach($sdetails as $sk=>$sapvData){
	    	
	    				$r1start = strtotime($sapvData['verordnungam']);
	    				$r1end = strtotime($sapvData['verordnungbis']);
	    					
	    				$r2start = strtotime($current_period[$v_ipid]['start']);
	    				$r2end = strtotime($current_period[$v_ipid]['end']);
	    				if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)){
	    	
	    					if(!empty($sapvData['approved_number'])){
	    						$sapv_approved['numbers'][$s_ipid] =  $sapvData['approved_number'];
	    					}
	    						
	    					$sapv_approved['dates'][$s_ipid] =  $sapvData['approved_date'];
	    					break;
	    				}
	    			}
	    		}
	    			
	    		//get client national hollidays
	    		$nhollyday = new NationalHolidays();
	    		$national_holidays_arr = $nhollyday->getNationalHoliday($clientid, $current_period[$v_ipid]['start'], true);
	    			
	    		foreach($national_holidays_arr as $k_natholliday => $v_natholliday)
	    		{
	    			$national_holidays[] = strtotime(date('Y-m-d', strtotime($v_natholliday['NationalHolidays']['date'])));
	    		}
	    		
	    		foreach($alowed_sgbvs as $k_sgbva => $v_sgbva)
	    		{
	    			if(strtotime($current_period[$v_ipid]['end']) >= strtotime(date('d.m.Y', strtotime($v_sgbva['valid_from']))) && strtotime($current_period[$v_ipid]['start']) <= strtotime(date('d.m.Y', strtotime($v_sgbva['valid_till']))))
	    			{
	    				$a_sgbv_ids[] = $v_sgbva['id'];
	    				$sgbv_alowed_days[$v_sgbva['ipid']][$v_sgbva['id']] = $patientmaster->getDaysInBetween(date('Y-m-d', strtotime($v_sgbva['valid_from'])), date('Y-m-d', strtotime($v_sgbva['valid_till'])));
	    				$allowed_sgbv_in_period[$v_sgbva['ipid']][] = $v_sgbva;
	    				
	    			}
	    		}
	    		
	    		$a_sgbv_ids = array_values(array_unique($a_sgbv_ids));
	    		
	    		if(count($allowed_sgbv_in_period[$v_ipid]) > '0')
	    		{
	    			$start_sgbv_activity[$v_ipid] = $allowed_sgbv_in_period[$v_ipid]['0']['valid_from'];
	    			 
	    			$last_alowed_sgbv[$v_ipid] = end($allowed_sgbv_in_period[$v_ipid]);
	    			$end_sgbv_activity[$v_ipid] = $last_alowed_sgbv[$v_ipid]['valid_till'];
	    		}
	    		else
	    		{
	    			$start_sgbv_activity[$v_ipid] = '';
	    			$end_sgbv_activity[$v_ipid] = '';
	    		}
	    			
	    		foreach($denied_sgbvs as $k_sgbvd => $v_sgbvd)
	    		{
	    			if(strtotime($current_period[$v_ipid]['end']) >= strtotime(date('d.m.Y', strtotime($v_sgbva['valid_from']))) && strtotime($current_period[$v_ipid]['start']) <= strtotime(date('d.m.Y', strtotime($v_sgbva['valid_till']))))
	    			{
	    				$d_sgbv_ids[] = $v_sgbvd['id'];
	    				$sgbv_denied_days[$v_sgbvd['ipid']][$v_sgbvd['id']] = $patientmaster->getDaysInBetween(date('Y-m-d', strtotime($v_sgbvd['valid_from'])),date('Y-m-d', strtotime($v_sgbvd['valid_till'])));
	    			}
	    		}
	    			
	    		$days_in_period[$v_ipid] = $patientmaster->getDaysInBetween($current_period[$v_ipid]['start'], $current_period[$v_ipid]['end']);
	    		foreach($days_in_period[$v_ipid] as $k_period_day => $v_period_day)
	    		{
	    			$period_days_arr[$v_ipid][$v_period_day] = array();
	    		}
	    		foreach($active_days[$v_ipid] as $k_active_day => $v_active_day)
	    		{
	    			if(in_array($v_active_day, $days_in_period[$v_ipid]))
	    			{
	    				$active_days_in_period[$v_ipid][] = $v_active_day;
	    			}
	    		}
	    			
	    			
	    			
	    		foreach($active_days_in_period[$v_ipid] as $k_active_day_period => $v_active_day_period)
	    		{
	    			//construct array with allowed sgbv actions each day
	    			foreach($alowed_sgbvs as $k_sgbv_a => $v_sgbv_a)
	    			{
	    					
	    				if(in_array($v_active_day_period, $sgbv_alowed_days[$v_ipid][$v_sgbv_a['id']]))
	    				{
	    					if(count($master_allowed_days[$v_ipid][$v_active_day_period][0]) == 0)
	    					{
	    						$days_allowed[0] = array();
	    					}
	    						
	    					$days_allowed[0] = array_unique(array_values(array_merge($days_allowed[0], $allowed_sgbv2items[$v_ipid][$v_sgbv_a['id']])));
	    					$master_allowed_days[$v_ipid][$v_active_day_period] = $days_allowed[0];
	    				}
	    			}
	    	
	    			//construct array with denied sgbv actions each day
	    			foreach($denied_sgbvs as $k_sgbv_d => $v_sgbv_d)
	    			{
	    				if(in_array($v_active_day_period, $sgbv_denied_days[$v_ipid][$v_sgbv_d['id']]))
	    				{
	    					if(count($master_denied_days[$v_ipid][$v_active_day_period][0]) == 0)
	    					{
	    						$days[0] = array();
	    					}
	    						
	    					$days[0] = array_unique(array_values(array_merge($days[0], $sgbv2items[$v_ipid][$v_sgbv_d['id']])));
	    					$days[] = "0";
	    						
	    					if(count($master_denied_days[$v_ipid][$v_active_day_period]) > 0)
	    					{
	    						$master_denied_days[$v_ipid][$v_active_day_period] = array_unique(array_values(array_merge($master_denied_days[$v_ipid][$v_active_day_period], $days[0])));
	    					}
	    					else
	    					{
	    						$master_denied_days[$v_ipid][$v_active_day_period] = $days[0];
	    					}
	    				}
	    			}
	    		}
	    			
	    		//array foc(free of charge) days with actions ids
	    		foreach($sgbv_foc_days[$v_ipid] as $k_foc_sgbv_id => $v_foc_sgbv_dates)
	    		{
	    			foreach($v_foc_sgbv_dates as $k_dates => $v_date)
	    			{
	    				if(strtotime($current_period[$v_ipid]['end']) >= strtotime(date('d.m.Y', strtotime($v_date))) && strtotime($current_period[$v_ipid]['start']) <= strtotime(date('d.m.Y', strtotime($v_date))))
	    				{
	    				/* if(strtotime(date('d.m.Y', strtotime($v_date))) <= strtotime($current_period[$v_ipid]['end']) && strtotime(date('d.m.Y', strtotime($v_date))) >= strtotime($current_period[$v_ipid]['start']))
	    				{ */
	    					$master_denied_days[$v_ipid][$v_date][] = $k_foc_sgbv_id;
	    				}
	    			}
	    		}
	    			
	    		//excluded actions which have disabled sgbv
	    		foreach($period_contact_form_date[$v_ipid] as $kpcid => $vbillpc)
	    		{
	    			if(strtotime($current_period[$v_ipid]['end']) >= strtotime(date('d.m.Y', strtotime($vbillpc))) && strtotime($current_period[$v_ipid]['start']) <= strtotime(date('d.m.Y', strtotime($vbillpc))))
	    			{
	    			/* if(strtotime(date('d.m.Y', strtotime($vbillpc))) <= strtotime($current_period[$v_ipid]['end']) && strtotime(date('d.m.Y', strtotime($vbillpc))) >= strtotime($current_period[$v_ipid]['start']))
	    			{ */
	    				foreach($initial_form_actions as $k_ff_action => $v_ff_action)
	    				{
	    					if($v_ff_action['contact_form_id'] == $kpcid)
	    					{
	    						$fform_actions_arr[$v_ipid][$v_ff_action['contact_form_id']][] = $v_ff_action['action_id'];
	    						$i_form_actions[$v_ipid][$v_ff_action['action_id']] = $v_ff_action;
	    					}
	    				}
	    			}
	    		}
	    			
	    		foreach($fform_actions_arr[$v_ipid] as $kk_contact_form_id => $v_actions)
	    		{
	    			$current_contact_form_date = $period_contact_form_date[$v_ipid][$kk_contact_form_id];
	    			foreach($v_actions as $k_v_action => $v_v_action)
	    			{
	    				if(!in_array($v_v_action, $master_denied_days[$v_ipid][$current_contact_form_date]) && in_array($v_v_action, $master_allowed_days[$v_ipid][$current_contact_form_date]))
	    				{
	    					$remaing_form_actions[$v_ipid][$kk_contact_form_id][] = $v_v_action;
	    				}
	    			}
	    		}
	    		
	    		foreach($period_contact_form_date[$v_ipid] as $kpcid => $vbillpc)
	    		{
	    			if(strtotime($current_period[$v_ipid]['end']) >= strtotime(date('d.m.Y', strtotime($vbillpc))) && strtotime($current_period[$v_ipid]['start']) <= strtotime(date('d.m.Y', strtotime($vbillpc))))
	    			{
	    			/* if(strtotime(date('d.m.Y', strtotime($vbillpc))) <= strtotime($current_period[$v_ipid]['end']) && strtotime(date('d.m.Y', strtotime($vbillpc))) >= strtotime($current_period[$v_ipid]['start']))
	    			{ */
	    				foreach($initial_form_actions as $k_ff_action => $v_ff_action)
	    				{
	    					if($v_ff_action['contact_form_id'] == $kpcid)
	    					{
	    						//excluded actions which have disabled sgbv
	    						if($sgbv_action2groups[$v_ff_action['action_id']] != '' && in_array($v_ff_action['action_id'], $remaing_form_actions[$v_ipid][$v_ff_action['contact_form_id']]))
	    						{
	    							$form_actions_arr[$v_ipid][$v_ff_action['contact_form_id']][] = $v_ff_action['action_id'];
	    							$actions2high_group[$v_ipid][$v_ff_action['contact_form_id']][$v_ff_action['action_id']] = $sgbv_action2groups[$v_f_action['action_id']];
	    							$actions2high_group_order[$v_ipid][$v_ff_action['contact_form_id']][$sgbv_action2groups[$v_ff_action['action_id']]] = $group_list_order[$sgbv_action2groups[$v_ff_action['action_id']]];
	    								
	    							$cf_with_actions_ids[$v_ipid][] = $v_ff_action['contact_form_id'];
	    							 
	    						}
	    					}
	    				}
	    			
	    			}
	    		}
	    		
	    		$cf_with_actions_ids[$v_ipid] = array_values(array_unique($cf_with_actions_ids[$v_ipid]));
	    	
	    		foreach($actions2high_group_order[$v_ipid] as $k_contact_form_ord => $groups_contact_form_ord)
	    		{
	    			asort($groups_contact_form_ord);
	    			$max_group = end($groups_contact_form_ord);
	    				
	    			//flip previous maxed array to get the groupid
	    			$fliped_groups_contact_form_ord = array_flip($groups_contact_form_ord);
	    				
	    			$contact_form_high_group[$v_ipid][$k_contact_form_ord] = $fliped_groups_contact_form_ord[$max_group];
	    		}
	    			
	    		foreach($actions2high_group[$v_ipid] as $k_contact_form => $groups_contact_form)
	    		{
	    			foreach($groups_contact_form as $k_action_id => $v_group_id)
	    			{
	    				$highest_actions[$v_ipid][$k_contact_form][] = $k_action_id;
	    				$highest_actions_details[$v_ipid][$k_contact_form][$k_action_id] = $sgbv_actions_details[$k_action_id];
	    			}
	    		}
	    		
	    		/* -------------------------- RWH - get healthinsurance pricelists - START --------------------------------- */
	    		//default period without sgbv form id(current month
	    			
	    		$period_pricelist['start'] = date('Y-m-d', strtotime($current_period[$v_ipid]['start']));
	    		$period_pricelist['end'] = date('Y-m-d', strtotime($current_period[$v_ipid]['start']));
	    			
	    			
	    		$socialcode_price = new SocialCodePriceList();
	    		$price_sheet[$v_ipid] = $socialcode_price->get_group_period_pricelist($price_sheet_group[$healthinsu_multi_array[$v_ipid]['company']['id']], $clientid, $period_pricelist);
	    			
	    		if($_REQUEST['dbgq'])
	    		{
	    			print_r($period_pricelist);
	    			print_r("Patient Health insurance\n");
	    			print_r($patient_healthinsurance);
	    			print_r("Health Insurance pricesheet group\n");
	    			print_r($price_sheet_group);
	    			print_r("Group Pricelists\n");
	    			print_r($price_sheet);
	    			exit;
	    		}
	    		/* -------------------------- RWH - get healthinsurance pricelists - END --------------------------------- */
	    		//get pricelist based on pricesheet from health insurance
	    		$p_groups = new SocialCodePriceGroups();
	    		$price_groups[$v_ipid] = $p_groups->get_prices($price_sheet[$v_ipid], $clientid);
	    			
	    		foreach($price_groups[$v_ipid] as $k_price_group => $v_price_group)
	    		{
	    			$group_price_details[$v_ipid][$v_price_group['groupshortcut']] = $v_price_group['groupname'];
	    		}
	    		$b_groups = new SocialCodePriceBonuses();
	    		$bonuses_price[$v_ipid] = $b_groups->get_prices($price_sheet[$v_ipid], $clientid);
	    		foreach($bonuses_price[$v_ipid] as $k_bprice_group => $v_bprice_group)
	    		{
	    			$bonuses_price_details[$v_ipid][$v_bprice_group['bonusshortcut']] = $v_bprice_group['bonusname'];
	    			$bonuses_prices[$v_ipid][$v_bprice_group['bonusshortcut']] = $v_bprice_group['price'];
	    		}
	    			
	    		foreach($sapv_day_gr as $k_dgr => $v_dgr)
	    		{
	    			$master_data[$v_ipid][$k_dgr] = array();
	    		}
	    	
	    		foreach($p_contactforms[$v_ipid] as $k_pcf => $v_pcf)
	    		{
	    			
	    			if(in_array($v_pcf['id'], $cf_with_actions_ids[$v_ipid]))
	    			{
	    				 
	    				foreach($sapv_day_gr as $k_d_gr => $v_d_gr)
	    				{
	    					if((($v_pcf['begin_date_h'] < '06' || $v_pcf['end_date_h'] >= '20') && $k_d_gr == 'Z') || ($v_pcf['end_date_h'] < $v_d_gr['end'] && $v_pcf['end_date_h'] >= $v_d_gr['start'] && $v_pcf['end_date_h'] < '20' && $v_pcf['begin_date_h'] >= '06')
	    							)
	    					{
	    						$current_week_day = date('w', strtotime($v_pcf['billable_date']));
	    						$current_date = date('Y-m-d', strtotime($v_pcf['billable_date']));
	    							
	    						$skip_night_bonus = false;
	    						if($v_pcf['end_date_h'] == "20" && $v_pcf['end_date_m'] == "00")
	    						{
	    							$skip_night_bonus = true;
	    						}
	    							
	    							
	    						foreach($highest_actions[$v_ipid][$v_pcf['id']] as $k_high_group_action => $v_high_group_action)
	    						{
	    							//limit per day and week
	    							$current_week = date('W', strtotime($current_date));
	    								
	    							if($_REQUEST['dbgz'] == 'yy')
	    							{
	    								print_r("Current date: " . $current_date . " -- Current week." . $current_week . "\n");
	    								print_r("Week(" . $current_week . ") used qty < Item week rule\n");
	    								print_r($current_week_qty['actions'][$v_high_group_action][$current_week]['qty'] . " < " . $items_period_rules[$v_high_group_action]['week_day'][$current_week][$current_date]);
	    								var_dump($current_week_qty['actions'][$v_high_group_action][$current_week]['qty'] < $items_period_rules[$v_high_group_action]['week_day'][$current_week][$current_date]);
	    								print_r("\n");
	    								print_r("Day used qty < Item day rule\n");
	    								print_r($current_day_qty['actions'][$v_high_group_action][$current_date]['qty'] . " < " . $items_period_rules[$v_high_group_action]['day'][$current_date]);
	    								var_dump($current_day_qty['actions'][$v_high_group_action][$current_date]['qty'] < $items_period_rules[$v_high_group_action]['day'][$current_date]);
	    								print_r("\n\n");
	    							}
	    								
	    							if($current_week_qty[$v_ipid]['actions'][$v_high_group_action][$current_week]['qty'] < $items_period_rules[$v_ipid][$v_high_group_action]['week_day'][$current_week][$current_date] && $current_day_qty[$v_ipid]['actions'][$v_high_group_action][$current_date]['qty'] < $items_period_rules[$v_ipid][$v_high_group_action]['day'][$current_date])
	    							{
	    								$current_week_qty[$v_ipid]['actions'][$v_high_group_action][$current_week]['qty'] += 1;
	    								$current_day_qty[$v_ipid]['actions'][$v_high_group_action][$current_date]['qty'] += 1;
	    									
	    								//initialize arrays with days if empty
	    								if(count($master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]]) == '0')
	    								{
	    									$master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]] = $period_days_arr[$v_ipid];
	    								}
	    									
	    								//initialize arrays with days if empty
	    								if(count($master_data[$v_ipid][$k_d_gr]['actions'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$v_high_group_action]) == '0')
	    								{
	    									$master_data[$v_ipid][$k_d_gr]['actions'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$v_high_group_action] = $period_days_arr[$v_ipid];
	    								}
	    									
	    								//initialize group days if array is empty
	    								if(count($master_data[$v_ipid][$k_d_gr]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]]) == '0')
	    								{
	    									$master_data[$v_ipid][$k_d_gr]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]] = $period_days_arr[$v_ipid];
	    								}
	    									
	    								//populate actions data
	    								$master_data[$v_ipid][$k_d_gr]['actions'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$v_high_group_action][$current_date]['qty'] += 1;
	    									
	    								//populate master group data
	    								$master_data[$v_ipid][$k_d_gr]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date][] = $v_pcf['id'];
	    								$master_data[$v_ipid][$k_d_gr]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date] = array_values(array_unique($master_data[$v_ipid][$k_d_gr]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]));
	    									
	    									
	    								if(count($master_data[$v_ipid][$k_d_gr]['actions'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$v_high_group_action][$current_date]) > 0)
	    								{
	    									//night shift bonus
	    									if($k_d_gr == 'Z' && !$skip_night_bonus)
	    									{
	    										$master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]['qty_n'] = 1;
	    										$master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]['qty_n_cf'][$v_pcf['id']] += 1;
	    									}
	    										
	    									//holidays and sunday === moved below (//populate group dataRichten)
	    									if($current_week_day == 0 || in_array(strtotime($current_date), $national_holidays))
	    									{
	    										$master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]['qty_h'] = 1;
	    									}
	    										
	    									if(count($master_data[$v_ipid][$k_d_gr]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]) > 0)
	    									{
	    										//old master data items with days in keys
	    										$mdi_old[$v_ipid]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date] = '1';
	    										$master_data_items[$v_ipid]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$v_pcf['id']] = '1';
	    									}
	    								}
	    									
	    									
	    									
	    								if(count($master_data[$v_ipid][$k_d_gr]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]) > '0' && ($current_week_day == 0 || in_array(strtotime($current_date), $national_holidays)))
	    								{
	    									$master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]['qty_h'] = count($master_data[$v_ipid][$k_d_gr]['group'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]);
	    								}
	    									
	    								if(count($master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]['qty_n_cf']) > '0')
	    								{
	    									$master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]['qty_n'] = count($master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]['qty_n_cf']);
	    								}
	    							}
	    						}
	    							
	    						//night shift bonus
	    						if($k_d_gr == 'Z')
	    						{
	    							$master_data_items[$v_ipid]['bonus']['n']['qty'][$current_date] = count($master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_ipid][$v_pcf['id']]][$current_date]['qty_n_cf']);
	    						}
	    							
	    						//holidays and sunday
	    						if($current_week_day == 0 || in_array(strtotime($current_date), $national_holidays))
	    						{
	    							$master_data_items[$v_ipid]['bonus']['h'] += $master_data[$v_ipid][$k_d_gr]['bonus'][$contact_form_high_group[$v_ipid][$v_pcf['id']]][$current_date]['qty_h'];
	    						}
	    					}
	    				}
	    			}
	    		}
	    			
	    	}

	    	$activity_days = array();
	    	foreach($ipids as $k_ipid => $v_ipid)
	    	{
	    		$item = '1';
	    		$grand_total = '0';
	    		$activity_days[$v_ipid] = array();
	    		
	    		foreach($master_data_items[$v_ipid] as $k_group_items => $v_group_items)
	    		{
	    			foreach($v_group_items as $k_group => $v_group_qty)
	    			{
	    				//used to get the activity dates
	    		
	    				if($k_group_items == 'group')
	    				{
	    					$invoice_items[$v_ipid]['group'][$item]['shortcut'] = $price_groups[$v_ipid][$k_group]['groupshortcut'];
	    					$invoice_items[$v_ipid]['group'][$item]['price'] = $price_groups[$v_ipid][$k_group]['price'];
	    					$invoice_items[$v_ipid]['group'][$item]['qty'] = count($v_group_qty);
	    					$invoice_items[$v_ipid]['group'][$item]['shortcut_total'] = (count($v_group_qty) * $price_groups[$v_ipid][$k_group]['price']);
	    					$grand_total += (count($v_group_qty) * $price_groups[$v_ipid][$k_group]['price']);
	    					$item++;
	    				}
	    		
	    				if($k_group_items == 'bonus')
	    				{
	    					if($k_group == 'n')
	    					{
	    						$v_group_qty_sum = array_sum($v_group_qty['qty']);
	    					}
	    					else
	    					{
	    						$v_group_qty_sum = $v_group_qty;
	    					}
	    					$invoice_items[$v_ipid]['bonus'][$item]['shortcut'] = $k_group;
	    					$invoice_items[$v_ipid]['bonus'][$item]['qty'] = $v_group_qty_sum;
	    		
	    					//todo add price group here
	    					$invoice_items[$v_ipid]['bonus'][$item]['price'] = $bonuses_prices[$v_ipid][strtoupper($k_group)];
	    					$invoice_items[$v_ipid]['bonus'][$item]['shortcut_total'] = ($v_group_qty_sum * $bonuses_prices[$v_ipid][strtoupper($k_group)]);
	    					$grand_total += ($v_group_qty_sum * $bonuses_prices[$v_ipid][strtoupper($k_group)]);
	    					$item++;
	    				}
	    		
	    				$invoice_items[$v_ipid]['grand_total'] = $grand_total;
	    			}
	    		}

		    	foreach($mdi_old[$v_ipid] as $k_group_mdi => $v_group_mdi)
		    	{
		    		foreach($v_group_mdi as $k_gr => $v_gr_qty)
		    		{
		    			//used to get the activity dates
		    			$extracted_keys[$v_ipid] = array_keys($v_gr_qty);
		    			
		    			if(!empty($extracted_keys[$v_ipid]))
		    			{
		    				
		    				$activity_days[$v_ipid] = array_merge_recursive($extracted_keys[$v_ipid], $activity_days[$v_ipid]);
		    			}
		    		}
		    	}
		    	
		    	asort($activity_days[$v_ipid]);
		    	$activity_days[$v_ipid] = array_values($activity_days[$v_ipid]);
		    
		    	if(count($activity_days[$v_ipid]) > 0)
		    	{
		    		$first_active_day[$v_ipid] = $activity_days[$v_ipid][0];
		    		 
		    		$last_date[$v_ipid] = end($activity_days[$v_ipid]);
		    		$last_active_day[$v_ipid] = $last_date[$v_ipid];
		    	}
		    	else
		    	{
		    		$first_active_day[$v_ipid] = '';
		    		$last_active_day[$v_ipid] = '';
		    	}
	    	}
	    	
    	
    		
    	
	    	$pseudo_post = array();    	
	    	foreach($ipids as $k_ipid => $v_ipid)
	    	{
	    		$pseudo_post['sapv_footer'] = $letter_boxes_details[0]['sapv_invoice_footer'];
	    		$pseudo_post['completed_date'] = date('d.m.Y', time());
	    		
	    		$pseudo_post['patid'] = $patient_days[$v_ipid]['details']['id'];
	    		$pseudo_post['ipid'] = $v_ipid;
	    		
	    		$pseudo_post['client_ik'] = $client_details[0]['institutskennzeichen'];
	    		$pseudo_post['client_details'] = $client_details[0];
	    		$pseudo_post['clientid'] = $clientid;
	    			
	    		$pseudo_post['patientdetails'] = $patient_days[$v_ipid]['details'];
	    		$pseudo_post['patientdetails']['birthd'] = date('d.m.Y', strtotime($patient_days[$v_ipid]['details']['birthd']));
	    			
	    		$pseudo_post['health_insurance'] = $pathelathinsu[$v_ipid]['name'];
	    		$pseudo_post['hi_subdiv_address'] = $hi_address[$v_ipid];
	    		$pseudo_post['health_insurance_ik'] = $pathelathinsu[$v_ipid]['health_insurance_ik'];
	    		$pseudo_post['health_insurance_kassenr'] = $pathelathinsu[$v_ipid]['health_insurance_kassenr'];
	    		$pseudo_post['insurance_no'] = $pathelathinsu[$v_ipid]['insurance_no'];
	    		
	    		$pseudo_post['first_sapv_day'] = $sapv_in_period[$v_ipid][0];
	    		$pseudo_post['last_sapv_day'] = end($sapv_in_period[$v_ipid]);
	  
	    		$pseudo_post['start_sgbv_activity'] = $start_sgbv_activity[$v_ipid];
	    		$pseudo_post['end_sgbv_activity'] = $end_sgbv_activity[$v_ipid];    		
	
	    		$pseudo_post['first_active_day'] = $first_active_day[$v_ipid];
	    		$pseudo_post['last_active_day'] = $last_active_day[$v_ipid];
	    			
	    		$invoice_pflegesute[$v_ipid] = array();
	    		foreach($pflegesufe2ipid[$v_ipid] as $k=>$pflitem){
	    				
	    			if($pflitem['tilldate'] == "0000-00-00"){
	    				$pflitem['tilldate'] == date('Y-m-d', strtotime($current_period[$v_ipid]['end']));
	    			}
	    				
	    			if(Pms_CommonData::isintersected(date('Y-m-d', strtotime($current_period[$v_ipid]['start'])), date('Y-m-d', strtotime($current_period[$v_ipid]['end'])), $pflitem['fromdate'], $pflitem['tilldate'])){
	    				$invoice_pflegesute[$v_ipid][] = $pflitem;
	    			}
	    				
	    		}
	    		
	    		$pflege_arr[$v_ipid] = array();
	    		$pflege_arr[$v_ipid] = end($invoice_pflegesute[$v_ipid] );
	    			
	    		if(!empty($pflege_arr[$v_ipid]))
	    		{
	    			$pseudo_post['patient_pflegestufe'] = $pflege_arr[$v_ipid]['stage'];
	    		}
	    		else
	    		{
	    			$pseudo_post['patient_pflegestufe'] = ' - ';
	    		}
	    			
	    		$pseudo_post['patient_address'] = $patient_address[$v_ipid];
				if(strlen($pseudo_post['address']) == '0')
				{
					$pseudo_post['address'] = $hi_address[$v_ipid];
				}
	    			
	    		$pseudo_post['master_data'] = $master_data[$v_ipid];
	    		$pseudo_post['period_days'] = $days_in_period[$v_ipid];
	    	
	    		$pseudo_post['group_list'] = $group_list;
	    		$pseudo_post['sgbv_actions_details'] = $sgbv_actions_details;
	    		$pseudo_post['national_holidays'] = $national_holidays;
	    			
	    		if($sapv_approved['dates'][$v_ipid] != '0000-00-00 00:00:00')
	    		{
	    			$pseudo_post['sapv_approve_date'] = $sapv_approved['dates'][$v_ipid];
	    		}
	    		else 
	    		{
	    			$pseudo_post['sapv_approve_date'] = '';
	    		}
	    		$pseudo_post['sapv_approve_nr'] = $sapv_approved['numbers'][$v_ipid];
	    		
	    		
	    		$pseudo_post['invoice_items'] = $invoice_items[$v_ipid];
	    			
	    		$pseudo_post['group_price_details'] = $group_price_details[$v_ipid];
	    		$pseudo_post['bonuses_price_details'] = $bonuses_price_details[$v_ipid];
	    		
	    		//new columns
	    		$pseudo_post['debitor_number'] = $pathelathinsu[$v_ipid]['debitor_number'];
	    		$pseudo_post['ppun'] = $pathelathinsu[$v_ipid]['ppun'];
	    		//--
	//print_r($pseudo_post); exit;
	    		if($_REQUEST['only_pdf'] == '0')
	    		{
	    			$sgbv_invoices_number = $sgbvinvoices->get_next_invoice_number($clientid, true);
					$prefix = $sgbv_invoices_number['prefix'];
					$invoicenumber = $sgbv_invoices_number['invoicenumber'];
					
					$pseudo_post['prefix'] = $prefix;
					
					//insert invoice START
					$ins_inv = new SgbvInvoices();
					$ins_inv->invoice_start = date('Y-m-d H:i:s', strtotime($current_period[$v_ipid]['start']));
					$ins_inv->invoice_end = date('Y-m-d H:i:s', strtotime($current_period[$v_ipid]['end']));
	
					if($pseudo_post['first_active_day'] != '')
					{
						$ins_inv->start_active = date('Y-m-d H:i:s', strtotime($pseudo_post['first_active_day']));
					}
					if($pseudo_post['last_active_day'] != '')
					{
						$ins_inv->end_active = date('Y-m-d H:i:s', strtotime($pseudo_post['last_active_day']));
					}
	
					if($pseudo_post['start_sgbv_activity'] != '')
					{
						$ins_inv->start_sgbv = date('Y-m-d H:i:s', strtotime($pseudo_post['start_sgbv_activity']));
					}
					
					if($pseudo_post['end_sgbv_activity'] != '')
					{
						$ins_inv->end_sgbv = date('Y-m-d H:i:s', strtotime($pseudo_post['end_sgbv_activity']));
					}
	
					$ins_inv->ipid = $v_ipid;
					$ins_inv->client = $clientid;
					$ins_inv->prefix = $prefix;
					$ins_inv->invoice_number = $invoicenumber;
					$ins_inv->invoice_total = $invoice_items[$v_ipid]['grand_total'];
					$ins_inv->address = (strlen($pseudo_post['patient_address']) > '0') ? $pseudo_post['patient_address'] : $pseudo_post['address'];
					$ins_inv->status = '1'; // DRAFT - ENTWURF
					
					//new columns
					$ins_inv->client_ik = $pseudo_post['client_ik'];
					$ins_inv->sapv_approve_nr = $pseudo_post['sapv_approve_nr'];
					$ins_inv->first_name = $pseudo_post['patientdetails']['first_name'];
					$ins_inv->last_name = $pseudo_post['patientdetails']['last_name'];
					$ins_inv->birthdate = date('Y-m-d', strtotime($pseudo_post['patientdetails']['birthd']));
					$ins_inv->insurance_no = $pseudo_post['insurance_no'];
					$ins_inv->street = $pseudo_post['patientdetails']['street'];
					//--
					$ins_inv->save();
	
					$ins_id = $ins_inv->id;
	
					foreach($invoice_items[$v_ipid]['group'] as $k_inv => $v_inv)
					{
						$invoice_items_arr[$v_ipid][] = array(
							'invoice' => $ins_id,
							'client' => $clientid,
							'shortcut' => $v_inv['shortcut'],
							'qty' => $v_inv['qty'],
							'price' => $v_inv['price']
						);
					}
					foreach($invoice_items[$v_ipid]['bonus'] as $k_b_inv => $v_b_inv)
					{
						$invoice_items_arr[$v_ipid][] = array(
							'invoice' => $ins_id,
							'client' => $clientid,
							'shortcut' => $v_b_inv['shortcut'],
							'qty' => $v_b_inv['qty'],
							'price' => $v_b_inv['price']
						);
					}
	
	
					if(count($invoice_items[$v_ipid]) > 0)
					{
						//insert many records with one query!!
						$collection = new Doctrine_Collection('SgbvInvoiceItems');
						$collection->fromArray($invoice_items_arr[$v_ipid]);
						$collection->save();
					}
	
					$pseudo_post['unique_id'] = $ins_id;
					//insert invoice END
					
					$pseudo_post['invoice_number'] = $invoicenumber;				
	    		}
	    		
	    		if($_REQUEST['get_pdf'] == '1')
	    		{
	    			if($_REQUEST['stornopdf'] == '1' && $_REQUEST['storno'])
	    			{
	    				$storno_data = $sgbvinvoices->getSgbvInvoice($_REQUEST['storno']);
	    			
	    				//ISPC-2532 Lore 09.11.2020
	    				$pseudo_post['storned_invoice_number'] = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
	    			
	    				$pseudo_post['address'] = $storno_data['address'];
	    				$pseudo_post['client'] = $storno_data['client'];
	    				$pseudo_post['prefix'] = $storno_data['prefix'];
	    				$pseudo_post['invoice_number'] = $storno_data['invoice_number'];
	    				$pseudo_post['completed_date'] = date('d.m.Y', strtotime($storno_data['completed_date']));
	    				$pseudo_post['first_active_day'] = date('d.m.Y', strtotime($storno_data['start_active']));
	    				$pseudo_post['last_active_day'] = date('d.m.Y', strtotime($storno_data['end_active']));
	    				if($storno_data['start_sgbv'] != '0000-00-00 00:00:00' && $storno_data['end_sgbv'] != '0000-00-00 00:00:00')
	    				{
	    					$pseudo_post['start_sgbv_activity'] = date('d.m.Y', strtotime($storno_data['start_sgbv']));
	    					$pseudo_post['end_sgbv_activity'] = date('d.m.Y', strtotime($storno_data['end_sgbv']));
	    				}
	    				$pseudo_post['client_ik'] = $client_details[0]['institutskennzeichen'];
	    				if($_REQUEST['bulk_print'] == '1'){
	    					$pseudo_post['unique_id'] = $storno_data['id'];
	    				} else {
	    					$pseudo_post['unique_id'] = $storno_data['record_id'];
	    				}
	    				$pseudo_post['grand_total'] = ($storno_data['invoice_total'] * (-1));
	    				$pseudo_post['sapv_footer'] = $storno_data['footer'];
	    			
	    				$template_files = array('storno_invoice_sgbv_pdf.html', 'socialcodepdf.html');
	    			}
	    			else
	    			{
	    				$template_files = array('invoice_sgbv_pdf.html', 'socialcodepdf.html');
	    			}
	    			
	    			$orientation = array('P', 'L');
	    			$background_pages = array('0'); //0 is first page;
	    			
	    			if($template_data)
	    			{
	    			
	    			 $template = INVOICE_TEMPLATE_PATH . '/' . $template_data[0]['file_path'];
	    			 //create public/joined_files/ dir
	    			 while(!is_dir(PDFJOIN_PATH))
	    			 {
	    			 mkdir(PDFJOIN_PATH);
	    			 if($i >= 50)
	    			 {
	    			 exit; //failsafe
	    			 }
	    			 $i++;
	    			 }
	    			  
	    			 //create public/joined_files/$clientid dir
	    			 $pdf_path = PDFJOIN_PATH . '/' . $clientid;
	    			  
	    			 while(!is_dir($pdf_path))
	    			 {
	    			 mkdir($pdf_path);
	    			 if($i >= 50)
	    			 {
	    			 exit; //failsafe
	    			 }
	    			 $i++;
	    			 }
	    	
		    		//create batch name
		    		$_Batchname = false;
		    		$_Batchname = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
		    			
		    		// generate invoice page
		    		$tokenfilter = array();
		    		$tokenfilter['patient'] = $pseudo_post['patientdetails'];
		    		$tokenfilter['invoice']['healthinsurancenumber'] = $pseudo_post['insurance_no'];
		    	
		    		$tokenfilter['invoice']['prefix'] = $pseudo_post['prefix'];
		    		$tokenfilter['invoice']['invoicenumber'] = $pseudo_post['invoice_number'];
		    		$tokenfilter['invoice']['full_invoice_number'] = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
		    		$tokenfilter['invoice']['completed_date'] = strftime('%A, %d. %B %Y', strtotime($pseudo_post['completed_date']));
		    		$tokenfilter['invoice']['patient_pflegestufe'] = $pseudo_post['patient_pflegestufe'];
		    		$tokenfilter['invoice']['unique_id'] = $pseudo_post['unique_id'];
	    			if($pseudo_post['start_sgbv_activity'] != "0000-00-00 00:00:00" && $pseudo_post['start_sgbv_activity'] != "1970-01-01 00:00:00")
					{
						$tokenfilter['invoice']['start_sgbv_activity'] = date('d.m.Y', strtotime($pseudo_post['start_sgbv_activity']));
					}
					else 
					{
						$tokenfilter['invoice']['start_sgbv_activity'] = "-";
					}
					if($pseudo_post['end_sgbv_activity'] != "0000-00-00 00:00:00" && $pseudo_post['end_sgbv_activity'] != "1970-01-01 00:00:00")
					{
						$tokenfilter['invoice']['end_sgbv_activity'] = date('d.m.Y', strtotime($pseudo_post['end_sgbv_activity']));
					}
					else 
					{
						$tokenfilter['invoice']['end_sgbv_activity'] = "-";
					}
					if($pseudo_post['first_active_day'] != "0000-00-00 00:00:00" && $pseudo_post['first_active_day'] != "1970-01-01 00:00:00")
					{
						$tokenfilter['invoice']['first_active_day'] = date('d.m.Y', strtotime($pseudo_post['first_active_day']));
					}
					else
					{
						$tokenfilter['invoice']['first_active_day'] = "-";
					}
					if($pseudo_post['last_active_day'] != "0000-00-00 00:00:00" && $pseudo_post['last_active_day'] != "1970-01-01 00:00:00")
					{
						$tokenfilter['invoice']['last_active_day'] = date('d.m.Y', strtotime($pseudo_post['last_active_day']));
					}
					else
					{
						$tokenfilter['invoice']['last_active_day'] = "-";
					}
		    	
		    		if($pseudo_post['patient_address'] != "")
		    		{
		    			$tokenfilter['invoice']['address'] = $pseudo_post['patient_address'];
		    		}
		    		else
		    		{
		    			$tokenfilter['invoice']['address'] = $pseudo_post['address'];
		    		}
		    	
		    		$tokenfilter['invoice']['invoicefooter'] = $pseudo_post['sapv_footer'];
		    		if($_REQUEST['stornopdf'] == '1' && $_REQUEST['storno'])
		    		{
		    			$tokenfilter['invoice']['invoiceamount'] = number_format($pseudo_post['grand_total'], '2', ',', '.');
		    		}
		    		else
		    		{
		    			$tokenfilter['invoice']['invoiceamount'] = number_format($pseudo_post['invoice_items']['grand_total'], '2', ',', '.');
		    		}
		    		$keyi = 0;
		    		foreach($pseudo_post['invoice_items'] as $kivi => $vivi)
		    		{
		    			if($kivi != 'grand_total')
		    			{
		    				$invoice_items['items'][$keyi][$kivi] = $vivi;
		    				$keyi++;
		    			}
		    	
		    		}
		    	
		    		if(count($pseudo_post['invoice_items']) > '0')
		    		{
		    			$rows = count($invoice_items['items']);
		    			$grid = new Pms_Grid($invoice_items['items'], 1, $rows, "bw_sgbv_invoice_items_list_pdf.html");
		    	
		    			$grid->group_price_details = $pseudo_post['group_price_details'];
		    			$grid->bonuses_price_details = $pseudo_post['bonuses_price_details'];
		    			$grid->invoice_total = $tokenfilter['invoice']['invoiceamount'];
		    			$grid->max_entries = $rows;
		    				
		    			$html_items = $grid->renderGrid();
		    		}
		    		else
		    		{
		    			$html_items = "";
		    			$html_items_short = "";
		    		}
		    	
		    		$tokenfilter['invoice']['invoice_items_html'] = $html_items;
		    		//print_r($tokenfilter); exit;
		    	
		    		$docx_helper = $this->getHelper('CreateDocxFromTemplate');
		    		$docx_helper->setTokenController('invoice');
		    			
		    		$tmpstmp = isset($this->view->folder_stamp) ? $this->view->folder_stamp : time();
		    			
		    		while(!is_dir($pdf_path . '/' . $tmpstmp))
		    		{
		    			mkdir($pdf_path . '/' . $tmpstmp);
		    			if($i >= 50)
		    			{
		    				exit; //failsafe
		    			}
		    			$i++;
		    		}
		    	
		    		$destination_path = $pdf_path . '/' . $tmpstmp . '/';
		    			
		    		$docx_helper->setOutputFile($destination_path.$_Batchname);
		    	
		    	
		    		//do not add extension !
		    		$docx_helper->setBrowserFilename($_Batchname);
		    	
		    		$docx_helper->create_pdf ($template, $tokenfilter) ;
		    	
		    		$temp_files[] = $destination_path.$_Batchname.'.pdf';
		    	
		    		$pseudo_post['first_active_day'] = $tokenfilter['invoice']['first_active_day'];
		    		$pseudo_post['last_active_day'] = $tokenfilter['invoice']['last_active_day'];
		    		//generate socialcoderecor page
		    		$temp_files[] = $this->generate_joined_files_pdf('4', $pseudo_post, 'SocialcodePdfs', 'socialcodepdf.html');
		    	
		    		$source = 'Bw_sgbv_invoice';
		    		$patient_data = array();
		    		$patient_data['return_file_name'] = '1';
		    		ob_end_clean();
		    			
		    		$this->join_pdfs_new($temp_files, $patient_data ,$source);
		    		
			    	/* $source = 'Bw_sgbv_invoice';
			    	$patient_data = array();
			    	ob_end_clean();
			    	$this->join_pdfs_new($pfiles, $patient_data ,$source); */
			    	exit;
	    			}
	    			else 
	    			{
	    				$pseudo_post['first_active_day'] = date('d.m.Y', strtotime($pseudo_post['first_active_day']));
	    				$pseudo_post['last_active_day'] = date('d.m.Y', strtotime($pseudo_post['last_active_day']));
	    				$this->generate_pdf($pseudo_post, "SocialcodePdfs", $template_files, $orientation, $background_pages);
	    			}
	    		}
	    	}
    
    		$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=bw_sgbv_invoice');
    	}
    		
    }
    	
    	private function generatebwsgbxiinvoice($params)
    	{
    		if(isset($params['print_job']) && $params['print_job'] == '1'){
    			$this->_helper->layout->setLayout('layout_ajax');
    			$this->_helper->viewRenderer->setNoRender();
    		}
    	
    		setlocale(LC_ALL, 'de_DE.UTF-8');
    		//$logininfo = new Zend_Session_Namespace('Login_Info');
    		//$tm = new TabMenus();
    		$p_list = new PriceList();
    		$form_types = new FormTypes();
    		$sapvs = new SapvVerordnung();
    		$patientmaster = new PatientMaster();
    		//$sapvverordnung = new SapvVerordnung();
    		$pflege = new PatientMaintainanceStage();
    		$hi_perms = new HealthInsurancePermissions();
    		$phelathinsurance = new PatientHealthInsurance();
    		$healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
    		$boxes = new LettersTextBoxes();
    		$client = new Client();    		
			$sgbxi_inv = new SgbxiInvoices();
    		$p_sgbxi = new PriceSgbxi();
    		$ppun = new PpunIpid();
    	
    		if(isset($params) && !empty($params)){
    			$_REQUEST = $params;
    			$this->_helper->viewRenderer->setNoRender();
    		}
    	
    		//var_dump($_REQUEST); exit;
    		//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
    		$clientid = isset($_REQUEST['clientid']) && ! empty($_REQUEST['clientid']) ? $_REQUEST['clientid'] :  $this->clientid;
    		$userid = isset($_REQUEST['userid']) && ! empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $this->userid;
    	
    		$letter_boxes_details = $boxes->client_letter_boxes($clientid);
    		$client_details = $client->getClientDataByid($clientid);
    		$this->view->client_details = $client_details[0];
    	
    		$modules =  new Modules();
    		$clientModules = $modules->get_client_modules($clientid);
    	
    		$shortcuts = Pms_CommonData::get_prices_shortcuts();
    		$default_price_list = Pms_CommonData::get_default_price_shortcuts();
    	
    		$ipids = $_REQUEST['ipids'];
    	
    		//load template data
    		$template_data = InvoiceTemplates::get_template($clientid, false, '1', $_REQUEST['invoice_type']);
    	
    		/* if(!$template_data && $_REQUEST['get_pdf'] == '1'){
    		 $this->redirect(APP_BASE . 'invoiceclient/patientlist?flg=notemplate');
    		 exit;
    		 } */
    	
    		if(!empty($ipids))
    		{
    			//patient days
    			$conditions['client'] = $clientid;
    			$conditions['ipids'] = $ipids;
    			$conditions['periods'][0]['start'] = '2009-01-01';
    			$conditions['periods'][0]['end'] = date('Y-m-d');
    	
    			$sql = 'e.epid, p.ipid, e.ipid,';
    			$sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    			$sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    			$sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    			$sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    			$sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    			$sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    	
    			//be aware of date d.m.Y format here
    			$patient_days = Pms_CommonData::patients_days($conditions, $sql);
    			//var_dump($patient_days); exit;
    			$all_patients_periods = array();
    			foreach($patient_days as $k_ipid => $patient_data)
    			{
    				//all patients periods
    				$all_patients_periods = array_merge_recursive($all_patients_periods, $patient_data['active_periods']);
    	
    				//used in flatrate
    				if(empty($patient_periods[$k_ipid]))
    				{
    					$patient_periods[$k_ipid] = array();
    				}
    	
    				array_walk_recursive($patient_data['active_periods'], function(&$value) {
    					$value = date("Y-m-d", strtotime($value));
    				});
    					$patient_periods[$k_ipid] = array_merge($patient_periods[$k_ipid], $patient_data['active_periods']);
    						
    					//hospital days cs
    					if(!empty($patient_data['hospital']['real_days_cs']))
    					{
    						$hospital_days_cs[$k_ipid] = $patient_data['hospital']['real_days_cs'];
    						array_walk($hospital_days_cs[$k_ipid], function(&$value) {
    							$value = date("Y-m-d", strtotime($value));
    						});
    					}
    						
    					//hospiz days cs
    					if(!empty($patient_data['hospiz']['real_days_cs']))
    					{
    						$hospiz_days_cs[$k_ipid] = $patient_data['hospiz']['real_days_cs'];
    						array_walk($hospiz_days_cs[$k_ipid], function(&$value) {
    							$value = date("Y-m-d", strtotime($value));
    						});
    					}
    						
    					//real active days
    					if(!empty($patient_data['real_active_days']))
    					{
    						$active_days_in_period_cs[$k_ipid] = $patient_data['real_active_days'];
    						array_walk($active_days_in_period_cs[$k_ipid], function(&$value) {
    							$value = date("Y-m-d", strtotime($value));
    						});
    					}
    						
    					//treatment days
    					if(!empty($patient_data['treatment_days']))
    					{
    						$treatment_days_cs[$k_ipid] = $patient_data['treatment_days'];
    						array_walk($treatment_days_cs[$k_ipid], function(&$value) {
    							$value = date("Y-m-d", strtotime($value));
    						});
    					}
    						
    					//active days
    					if(!empty($patient_data['active_days']))
    					{
    						$active_days[$k_ipid] = $patient_data['active_days'];
    						array_walk($active_days[$k_ipid], function(&$value) {
    							$value = date("Y-m-d", strtotime($value));
    						});
    					}
    						
    					if(empty($hospital_days_cs[$k_ipid]))
    					{
    						$hospital_days_cs[$k_ipid] = array();
    					}
    						
    					if(empty($hospiz_days_cs[$k_ipid]))
    					{
    						$hospiz_days_cs[$k_ipid] = array();
    					}
    						
    					$hospital_hospiz_days_cs[$k_ipid] = array_merge($hospital_days_cs[$k_ipid], $hospiz_days_cs[$k_ipid]);
    			}
    	
    			$all_patients_periods = array_values($all_patients_periods);
    	
    			foreach($all_patients_periods as $k_period => $v_period)
    			{
    				if(empty($months))
    				{
    					$months = array();
    				}
    	
    				$period_months = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], 'Y-m');
    				$months = array_merge($months, $period_months);
    			}
    			$months = array_values(array_unique($months));
    	
    			foreach($months as $k_m => $v_m)
    			{
    				$months_unsorted[strtotime($v_m)] = $v_m;
    			}
    			ksort($months_unsorted);
    			$months = array_values(array_unique($months_unsorted));
    	
    			foreach($months as $k_month => $v_month)
    			{
    				if(!function_exists('cal_days_in_month'))
    				{
    					$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
    				}
    				else
    				{
    					$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
    				}
    	
    				$months_details[$v_month]['start'] = $v_month . "-01";
    				$months_details[$v_month]['days_in_month'] = $month_days;
    				$months_details[$v_month]['end'] = $v_month . '-' . $month_days;
    	
    				//$month_select_array[$v_month] = $v_month;
    				$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
    			}
    			 
    			//loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)
    			foreach($ipids as $k_sel_pat => $v_sel_pat)
    			{
    				//get patients sapvs last fall
    				if($_REQUEST[$v_sel_pat]['period_type'] == 'sapvid')
    				{
    					$selected_sapv_falls_ipids[] = $v_sel_pat;
    					$selected_sapv_falls[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    				}
    				//get patient month fall
    				if($_REQUEST[$v_sel_pat]['period_type'] == 'list')
    				{
    					$selected_fall_ipids[] = $v_sel_pat;
    					$selected_month[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    				}
    				if($_REQUEST[$v_sel_pat]['period_type'] == 'admission')
    				{
    					$admission_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    				}
    				if($_REQUEST[$v_sel_pat]['period_type'] == 'quarter')
    				{
    					$quarter_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    				}
    			}
    	
    			$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($selected_sapv_falls_ipids, true);
    			foreach($selected_sapv_falls as $k_ipid => $fall_id)
    			{
    				$patients_sapv[$k_ipid] = $fall_id;
    				$patients_selected_periods[$k_ipid] = $patients_sapv_periods[$k_ipid][$fall_id];
    				$_REQUEST['nosapvperiod'][$k_ipid] = '0';
    				$_REQUEST['period'] = $patients_selected_periods;
    			}
    			 
    			//rewrite the periods array if the period is entire month not sapv fall
    			$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
    			 
    			foreach($patients_sapv_periods as $k_sapv_ipid => $v_sapvs)
    			{
    				foreach($v_sapvs as $k_sapv_id => $v_sapv_data)
    				{
    					if(empty($sapv_days[$v_sapv_data['ipid']]))
    					{
    						$sapv_days[$v_sapv_data['ipid']] = array();
    					}
    	
    					$sapv_days[$v_sapv_data['ipid']] = array_merge($sapv_days[$v_sapv_data['ipid']], $v_sapv_data['days']);
    					$sapv_days[$v_sapv_data['ipid']] = array_values(array_unique($sapv_days[$v_sapv_data['ipid']]));
    				}
    			}
    	
    			 
    			foreach($ipids as $k_ipid => $v_ipid)
    			{
    				if(!in_array($v_ipid, $selected_sapv_falls_ipids))
    				{
    					if(array_key_exists($v_ipid, $admission_fall))
    					{
    						$selected_period[$v_ipid] = $patient_days[$v_ipid]['active_periods'][$admission_fall[$v_ipid]];
    	
    						array_walk($selected_period[$v_ipid], function(&$value) {
    							$value = date("Y-m-d", strtotime($value));
    						});
    	
    							$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($selected_period[$v_ipid]['start'], $selected_period[$v_ipid]['end']);
    	
    							array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    								$value = date("d.m.Y", strtotime($value));
    							});
    	
    								$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    								$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    								$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    	
    	
    								array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    									$value = date("d.m.Y", strtotime($value));
    								});
    	
    									//exclude outside admission falls days from sapv!
    									if(empty($sapv_days[$v_ipid]))
    									{
    										$sapv_days[$v_ipid] = array();
    									}
    	
    									if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    									{
    										$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    									}
    									$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    									$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    	
    									$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    									$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    	
    									$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    									$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    	
    									//get all days of all sapvs in a period
    									$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    									$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    	
    	
    									$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    									$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    	
    									$last_sapv_data['ipid'] = $v_ipid;
    									$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    									$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    									$sapv_last_require_data[] = $last_sapv_data;
    										
    									$_REQUEST['admissionid'][$v_ipid] = $admission_fall[$v_ipid];
    					}
    					//ISPC-2461
    					elseif(array_key_exists($v_ipid, $quarter_fall))
    					{
    						$post_q = $_REQUEST[$v_ipid]['selected_period'];
    						$post_q_arr = explode("/",$post_q);
    						$q_no = (int)$post_q_arr[0];
    						$q_year = (int)$post_q_arr[1];
    	
    						$q_per = array();
    						$quarter_start = "";
    						$quarter_end = "";
    	
    						$q_per = Pms_CommonData::get_dates_of_quarter($q_no,$q_year,'Y-m-d');
    						$quarter_start = $q_per['start'];
    						$quarter_end = $q_per['end'];
    	
    						$selected_period[$v_ipid] = array();
    						$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($quarter_start, $quarter_end);
    						$selected_period[$v_ipid]['start'] = $quarter_start;
    						$selected_period[$v_ipid]['end'] = $quarter_end;
    	
    						array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    	
    							$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    							$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    							$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    	
    	
    							array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    								$value = date("d.m.Y", strtotime($value));
    							});
    	
    								//exclude outside admission falls days from sapv!
    								if(empty($sapv_days[$v_ipid]))
    								{
    									$sapv_days[$v_ipid] = array();
    								}
    	
    								if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    								{
    									$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    								}
    								$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    								$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    	
    								$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    								$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    	
    								$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    								$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    	
    								//get all days of all sapvs in a period
    								$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    								$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    	
    								$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    								$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    	
    								$last_sapv_data['ipid'] = $v_ipid;
    								$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    								$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    								$sapv_last_require_data[] = $last_sapv_data;
    	
    								$_REQUEST['quarterid'][$v_ipid] = $quarter_fall[$v_ipid];
    					}
    					else
    					{
    	
    						$start_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['start']));
    						$end_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['end']));
    	
    						$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    						$_REQUEST['selected_period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    						$_REQUEST['sapv_in_period'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    						$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    						$_REQUEST['period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    						$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    						$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    	
    						$last_sapv_data['ipid'] = $v_ipid;
    						$last_sapv_data['start_period'] = date('Y-m-d', strtotime($start_dmy));
    						$last_sapv_data['end_period'] = date('Y-m-d', strtotime($end_dmy));
    						$sapv_last_require_data[] = $last_sapv_data;
    					}
    				}
    			}
    			//print_r($_REQUEST); exit;
    			//get all sapv details
    			$all_sapvs = array();
    			$all_sapvs = $sapvs->get_all_sapvs($ipids);
    	
    			$sapv2ipid = array();
    			/* foreach($all_sapvs as $k=>$sdata){
    				$sapv2ipid[$sdata['ipid']][] = $sdata;
    			} */
    	
    			foreach($all_sapvs as $k_sapv => $v_sapv)
    			{
    				$sapv2ipid[$v_sapv['ipid']][] = $v_sapv;
    				if(empty($sapv_days_overall[$v_sapv['ipid']]))
    				{
    					$sapv_days_overall[$v_sapv['ipid']] = array();
    				}
    	
    				$start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
    	
    				if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00')
    				{
    					$end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
    				}
    				else
    				{
    					$end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
    				}
    	
    				//FIND A WAY TO GET MULTIPLE LAST SAPV IN EACH PERIOD FOR EACH PATIENT
    				if($last_sapvs_in_period[$v_sapv['ipid']])
    				{
    					$_REQUEST['period'][$v_sapv['ipid']] = array_merge($_REQUEST['period'][$v_sapv['ipid']], $last_sapvs_in_period[$v_sapv['ipid']]);
    				}
    	
    				$sapv_days_overall[$v_sapv['ipid']] = array_merge($sapv_days_overall[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end));
    				array_walk($sapv_days_overall[$v_sapv['ipid']], function(&$value) {
    					$value = date("d.m.Y", strtotime($value));
    				});
    					$sapv_days_overall[$v_sapv['ipid']] = array_values(array_unique($sapv_days_overall[$v_sapv['ipid']]));
    			}
    			 
    			foreach($last_sapvs_in_period as $k_sapvs => $v_sapvs)
    			{
    				foreach($v_sapvs as $k_sapvp => $v_sapvp)
    				{
    					$startp = date('Y-m-d', strtotime($v_sapvp['verordnungam']));
    						
    					if($v_sapvp['status'] == '1' && $v_sapvp['verorddisabledate'] != '0000-00-00 00:00:00')
    					{
    						$endp = date('Y-m-d', strtotime($v_sapvp['verorddisabledate']));
    					}
    					else
    					{
    						$endp = date('Y-m-d', strtotime($v_sapvp['verordnungbis']));
    					}
    					if(empty($period_sapv_alldays[$v_sapvp['ipid']]))
    					{
    						$period_sapv_alldays[$v_sapvp['ipid']] = array();
    					}
    					$period_sapv_alldays[$v_sapvp['ipid']] = array_merge($period_sapv_alldays[$v_sapvp['ipid']], PatientMaster::getDaysInBetween($startp, $endp));
    				}
    			}
    			 
    			 
    			$_REQUEST['period_sapvs_alldays'] = $period_sapv_alldays;
    			$_REQUEST['sapv_overall'] = $sapv_days_overall;
    			 
    			$current_period = array();
    			foreach($_REQUEST['period'] as $ipidp => $vipidp)
    			{
    				$current_period[$ipidp] = $vipidp;
    				if($_REQUEST['sapv_in_period'][$ipidp])
    				{
    					$sapv_in_period[$ipidp] = $_REQUEST['sapv_in_period'][$ipidp];
    				}
    				else 
    				{
    					$sapv_in_period[$ipidp] = array();
    				}
    			}
    			 
    			//Healthinsurance
    			$healthinsu_multi_array = array();
    			$healthinsu_multi_array = $phelathinsurance->get_multiple_patient_healthinsurance($ipids, true);
    	
    			foreach($healthinsu_multi_array as $k_hi => $v_hi)
    			{
    				$hi_companyids[] = $v_hi['companyid'];
    			}
    	
    			//multiple hi subdivisions && hi subdivisions permissions
    			$divisions = HealthInsurancePermissions::getClientHealthInsurancePermissions($clientid);
    	
    			//patientheathinsurance
    			if($divisions)
    			{
    				/* foreach($healthinsu_multi_array as $k_hi => $v_hi)
    				 {
    				 $hi_companyids[] = $v_hi['companyid'];
    				 } */
    	
    				$healthinsu_subdiv_arr = PatientHealthInsurance2Subdivisions::get_hi_subdivisions_multiple($hi_companyids);
    			}
    	
    			$pathelathinsu = array();
    			$patient_address = array();
    			$hi_address = array();
    			foreach($ipids as $k_ipid => $v_ipid)
    			{
    				$pathelathinsu[$v_ipid]['insurance_no'] = $healthinsu_multi_array[$v_ipid]['insurance_no'];
    				if($healthinsu_multi_array[$v_ipid]['company_name'] != "")
    				{
    					$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company_name'];
    				}
    				else
    				{
    					$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company']['name'];
    				}
    	
    				if($healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    				{
    					//			get patient name and adress
    					$patient_address[$v_ipid] = '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['first_name']) . ' ' . htmlspecialchars($patient_days[$v_ipid]['details']['last_name']) . '<br />';
    					$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['street1']) . '<br />';
    					$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['zip']) . ' ' . '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['city']);
    				}
    	
    				if(!empty($healthinsu_subdiv_arr[$v_ipid]['1']['name']))
    				{
    					//get new SAPV hi address
    					$hi_address[$v_ipid] = '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['name'] . '<br />';
    					$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['insurance_provider'] . '<br />';
    					//$hi_address .= '&nbsp;' . $healthinsu_subdiv_arr[1]['contact_person'] . '<br />';
    					$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['street1'] . '<br />';
    					$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['zip'] . ' ' . $healthinsu_subdiv_arr[$v_ipid][1]['city'];
    						
    					$pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_subdiv_arr[$v_ipid][1]['iknumber'];
    					$pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_subdiv_arr[$v_ipid][1]['kvnumber'];
    				}
    				else
    				{
    					//get old hi_address
    					$hi_address[$v_ipid] = '&nbsp;' . $pathelathinsu[$v_ipid]['name'] . '<br />';
    					$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_insurance_provider'] . '<br />';
    					//$hi_address .= '&nbsp;' . $healthinsu_array[0]['ins_contactperson'] . '<br />';
    					$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_street'] . '<br />';
    					$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_zip'] . ' ' . $healthinsu_multi_array[$v_ipid]['ins_city'];
    						
    					$pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_multi_array[$v_ipid]['institutskennzeichen'];
    					$pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_multi_array[$v_ipid]['kvk_no'];
    				}
    	
    				//new columns
    				if($clientModules['90'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "0")
    				{
    					//get debtor number from patient healthinsurance
    					if(strlen($healthinsu_multi_array[$v_ipid]['ins_debtor_number']) > '0')
    					{
    						$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['ins_debtor_number'];
    					}
    					else
    					{
    						$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['company']['debtor_number'];
    					}
    				}
    				if($clientModules['88'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    				{
    					//get ppun (private patient unique number)
    					$ppun_number = $ppun->check_patient_ppun($v_ipid, $clientid);
    					if($ppun_number)
    					{
    						$pathelathinsu[$v_ipid]['ppun']= $ppun_number['ppun'];
    						$pathelathinsu[$v_ipid]['debitor_number']= $ppun_number['ppun'];
    					}
    				}
    				//--
    				
    				//get contact forms in current period
    				$contact_forms_days[$v_ipid] = $this->get_period_contact_forms($v_ipid, $current_period[$v_ipid], true);
    				
    				
    				
    				if(!empty($_REQUEST['visit_id']) && is_numeric($_REQUEST['visit_id']) && count($contact_forms_days[$v_ipid][$_REQUEST['visit_id']]) > '0')
    				{
    					//set dates acording to the invoice selected
    					$visitid = $_REQUEST['visit_id'];
    				
    				
    					$first_active_day[$v_ipid] = $contact_forms_days[$v_ipid][$visitid]['start_date'];
    					$start_sgbv_activity[$v_ipid] = $contact_forms_days[$v_ipid][$visitid]['start_date'];
    				
    					$last_active_day[$v_ipid] = $contact_forms_days[$v_ipid][$visitid]['end_date'];
    					$end_sgbv_activity[$v_ipid] = $contact_forms_days[$v_ipid][$visitid]['end_date'];
    				}
    				
    				$price_list[$v_ipid] = $p_list->get_client_list_period($current_period[$v_ipid]['start'], $current_period[$v_ipid]['end'],$clientid);
    				$pricelist_sgbxi[$v_ipid] = $p_sgbxi->get_prices($price_list[$v_ipid][0]['id'], $clientid, $shortcuts['sgbxi'], $default_price_list['sgbxi']);
    			}
    			 
    			//var_dump($patient_address); exit;
    			// get pflegestufe for all
    			 
    			$all_pflegestufe = array();
    			$all_pflegestufe = Doctrine_Query::create()
    			->select("*")
    			->from('PatientMaintainanceStage')
    			->whereIn("ipid", $ipids)
    			->orderBy('fromdate,create_date asc')
    			->fetchArray();
    			 
    			$pflegesufe2ipid = array();
    			foreach($all_pflegestufe as $k=>$pflg) {
    				$pflegesufe2ipid[$pflg['ipid']][] = $pflg;
    			}
    			//print_R($pflegesufe2ipid); exit;
    	
    			/* $cf = new ContactForms();
    			//get multiple contact forms
    			$p_contactforms = $cf->get_multiple_contact_form_period($ipids);
    			$contact_form_ids = array();
    			$period_contact_form_date = array();
    			foreach($p_contactforms as $k_ipid => $v_ipid)
    			{
    				foreach($v_ipid as $k_p_cf => $v_p_cf)
    				{
    					$contact_form_ids[] = $v_p_cf['id'];
    					if($v_p_cf['billable_date'] != "0000-00-00 00:00:00")
    					{
    						$period_contact_form_date[$k_ipid][$v_p_cf['id']] = date('Y-m-d', strtotime($v_p_cf['billable_date']));
    					}
    				}
    				//$period_contactforms[$v_p_cf['id']] = $v_p_cf;
    			} */
    	
    			foreach($ipids as $k_ipid => $v_ipid)
    			{
    				$sapv_approved[$v_ipid] = array();
    	
    				foreach($sapv2ipid as $s_ipid => $sdetails){
    					foreach($sdetails as $sk=>$sapvData){
    	
    						$r1start = strtotime($sapvData['verordnungam']);
    						$r1end = strtotime($sapvData['verordnungbis']);
    	
    						$r2start = strtotime($current_period[$v_ipid]['start']);
    						$r2end = strtotime($current_period[$v_ipid]['end']);
    						if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)){
    	
    							if(!empty($sapvData['approved_number'])){
    								$sapv_approved['numbers'][$s_ipid] =  $sapvData['approved_number'];
    							}
    								
    							$sapv_approved['dates'][$s_ipid] =  $sapvData['approved_date'];
    							break;
    						}
    					}
    				}
    			}
    	
    	
    			/* foreach($p_contactforms[$v_ipid] as $k_pcf => $v_pcf)
    			{
    	
    			}

    	
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			 
    		} */
    	
    		$pseudo_post = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$pseudo_post['sapv_footer'] = $letter_boxes_details[0]['sapv_invoice_footer'];
    			$pseudo_post['completed_date'] = date('d.m.Y', time());
    	
    			$pseudo_post['patid'] = $patient_days[$v_ipid]['details']['id'];
    			$pseudo_post['ipid'] = $v_ipid;
    	
    			$pseudo_post['client_ik'] = $client_details[0]['institutskennzeichen'];
    			$pseudo_post['client_details'] = $client_details[0];
    			$pseudo_post['clientid'] = $clientid;
    	
    			$pseudo_post['patientdetails'] = $patient_days[$v_ipid]['details'];
    			$pseudo_post['patientdetails']['birthd'] = date('d.m.Y', strtotime($patient_days[$v_ipid]['details']['birthd']));
    	
    			$pseudo_post['health_insurance'] = $pathelathinsu[$v_ipid]['name'];
    			$pseudo_post['health_insurance_ik'] = $pathelathinsu[$v_ipid]['health_insurance_ik'];
    			$pseudo_post['health_insurance_kassenr'] = $pathelathinsu[$v_ipid]['health_insurance_kassenr'];
    			$pseudo_post['insurance_no'] = $pathelathinsu[$v_ipid]['insurance_no'];
    	
    			$pseudo_post['first_sapv_day'] = $sapv_in_period[$v_ipid][0];
    			$pseudo_post['last_sapv_day'] = end($sapv_in_period[$v_ipid]);
    	
    			$pseudo_post['start_sgbv_activity'] = $start_sgbv_activity[$v_ipid];
    			$pseudo_post['end_sgbv_activity'] = $end_sgbv_activity[$v_ipid];
    	
    			$pseudo_post['first_active_day'] = $first_active_day[$v_ipid];
    			$pseudo_post['last_active_day'] = $last_active_day[$v_ipid];
    	
    			$invoice_pflegesute[$v_ipid] = array();
    			foreach($pflegesufe2ipid[$v_ipid] as $k=>$pflitem){
    					
    				if($pflitem['tilldate'] == "0000-00-00"){
    					$pflitem['tilldate'] == date('Y-m-d', strtotime($current_period[$v_ipid]['end']));
    				}
    					
    				if(Pms_CommonData::isintersected(date('Y-m-d', strtotime($current_period[$v_ipid]['start'])), date('Y-m-d', strtotime($current_period[$v_ipid]['end'])), $pflitem['fromdate'], $pflitem['tilldate'])){
    					$invoice_pflegesute[$v_ipid][] = $pflitem;
    				}
    					
    			}
    	
    			$pflege_arr[$v_ipid] = array();
    			$pflege_arr[$v_ipid] = end($invoice_pflegesute[$v_ipid] );
    	
    			if(!empty($pflege_arr[$v_ipid]))
    			{
    				$pseudo_post['patient_pflegestufe'] = $pflege_arr[$v_ipid]['stage'];
    			}
    			else
    			{
    				$pseudo_post['patient_pflegestufe'] = ' - ';
    			}
    	
    			$pseudo_post['patient_address'] = $patient_address[$v_ipid];
    			if(strlen($pseudo_post['address']) == '0')
    			{
    				$pseudo_post['address'] = $hi_address[$v_ipid];
    			}
    			
    			$pseudo_post['period_days'] = $days_in_period[$v_ipid];
    	
    			if($sapv_approved['dates'][$v_ipid] != '0000-00-00 00:00:00')
    			{
    				$pseudo_post['sapv_approve_date'] = $sapv_approved['dates'][$v_ipid];
    			}
    			else
    			{
    				$pseudo_post['sapv_approve_date'] = '';
    			}
    			$pseudo_post['sapv_approve_nr'] = $sapv_approved['numbers'][$v_ipid];
    	
    			//set shortcut based on pflegestuffe
    			$shortcut[$v_ipid] = '';
    			if($pseudo_post['patient_pflegestufe'] != '-')
    			{
    				if($pseudo_post['patient_pflegestufe'] != '3+')
    				{
    					$shortcut[$v_ipid] = 'pf' . $pseudo_post['patient_pflegestufe'];
    				}
    				else
    				{
    					$shortcut[$v_ipid] = 'pf3';
    				}
    			
    				$invoice_items[$v_ipid]['shortcuts'][0]['shortcut'] = $shortcut[$v_ipid];
    				$invoice_items[$v_ipid]['shortcuts'][0]['price'] = $pricelist_sgbxi[$v_ipid][$shortcut[$v_ipid]]['price'];
    				$invoice_items[$v_ipid]['shortcuts'][0]['qty'] = '1';
    				$invoice_items[$v_ipid]['shortcuts'][0]['shortcut_total'] = $pricelist_sgbxi[$v_ipid][$shortcut[$v_ipid]]['price'];
    				$invoice_items[$v_ipid]['grand_total'] = $pricelist_sgbxi[$v_ipid][$shortcut[$v_ipid]]['price'];
    			}
    			
    			
    			
    			$pseudo_post['invoice_items'] = $invoice_items[$v_ipid];
    	
    			//new columns
    			$pseudo_post['debitor_number'] = $pathelathinsu[$v_ipid]['debitor_number'];
    			$pseudo_post['ppun'] = $pathelathinsu[$v_ipid]['ppun'];
    			//--
    			//print_r($pseudo_post); exit;
    			if($_REQUEST['only_pdf'] == '0')
    			{
					$sgbxi_inv_number = $sgbxi_inv->get_next_invoice_number($clientid, true);
					$prefix = $sgbxi_inv_number['prefix'];
					$invoicenumber = $sgbxi_inv_number['invoicenumber'];


					$pseudo_post['prefix'] = $prefix;
					$pseudo_post['invoice_number'] = $invoicenumber;

					if(strlen($_REQUEST['visit']) && is_numeric($_REQUEST['visit']))
					{
						$visitid = $_REQUEST['visit'];
					}

					//insert invoice START
					$ins_inv = new SgbxiInvoices();
					$ins_inv->invoice_start = date('Y-m-d H:i:s', strtotime($current_period[$v_ipid]['start']));
					$ins_inv->invoice_end = date('Y-m-d H:i:s', strtotime($current_period[$v_ipid]['end']));
					$ins_inv->start_active = date('Y-m-d H:i:s', strtotime($pseudo_post['first_active_day']));
					$ins_inv->end_active = date('Y-m-d H:i:s', strtotime($pseudo_post['last_active_day']));
					$ins_inv->start_sgbxi = date('Y-m-d H:i:s', strtotime($pseudo_post['start_sgbv_activity']));
					$ins_inv->end_sgbxi = date('Y-m-d H:i:s', strtotime($pseudo_post['end_sgbv_activity']));
					$ins_inv->ipid = $v_ipid;
					$ins_inv->client = $clientid;
					$ins_inv->contact_form_id = $visitid;
					$ins_inv->prefix = $prefix;
					$ins_inv->invoice_number = $invoicenumber;
					$ins_inv->invoice_total = $invoice_items[$v_ipid]['grand_total'];
					$ins_inv->address = (strlen($pseudo_post['patient_address']) > '0') ? $pseudo_post['patient_address'] : $pseudo_post['address'];
					$ins_inv->care_level = $pseudo_post['patient_pflegestufe'];
					$ins_inv->status = '1'; // DRAFT - ENTWURF
					$ins_inv->save();

					$ins_id = $ins_inv->id;
//@todo add single insert item in sgbxi_items with ins_id

					if(count($pseudo_post['invoice_items']) > '0')
					{
						$ins_inv_item = new SgbxiInvoiceItems();
						$ins_inv_item->invoice = $ins_id;
						$ins_inv_item->client = $clientid;
						$ins_inv_item->shortcut = $invoice_items[$v_ipid]['shortcuts'][0]['shortcut'];
						$ins_inv_item->qty = $invoice_items[$v_ipid]['shortcuts'][0]['qty'];
						$ins_inv_item->price = $invoice_items[$v_ipid]['shortcuts'][0]['price'];
						$ins_inv_item->save();
					}

					//$pseudo_post['unique_id'] = $ins_id;
				}
    			//}
    	
    			if($_REQUEST['get_pdf'] == '1')
    			{
    			if($_REQUEST['stornopdf'] == '1' && $_REQUEST['storno'] > '0')
					{
						$storno_data = $sgbxiinvoices->getSgbxiInvoice($_REQUEST['storno']);

						//ISPC-2532 Lore 09.11.2020
						$pseudo_post['storned_invoice_number'] = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
						
						$pseudo_post['address'] = $storno_data['address'];
						$pseudo_post['prefix'] = $storno_data['prefix'];
						$pseudo_post['invoice_number'] = $storno_data['invoice_number'];

						if($storno_data['completed_date'] != '0000-00-00 00:00:00')
						{
						    $pseudo_post['completed_date'] = date('d.m.Y', strtotime($storno_data['completed_date']));
						}

						$pseudo_post['first_active_day'] = date('d.m.Y', strtotime($storno_data['start_active']));
						$pseudo_post['last_active_day'] = date('d.m.Y', strtotime($storno_data['end_active']));
						if($storno_data['start_sgbxi'] != '0000-00-00 00:00:00' && $storno_data['end_sgbxi'] != '0000-00-00 00:00:00')
						{
							$pseudo_post['start_sgbv_activity'] = date('d.m.Y', strtotime($storno_data['start_sgbxi']));
							$pseudo_post['end_sgbv_activity'] = date('d.m.Y', strtotime($storno_data['end_sgbxi']));
						}


						$pseudo_post['client_ik'] = $client_details[0]['institutskennzeichen'];
						//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
						if($_REQUEST['bulk_print'] == '1'){
						    $pseudo_post['unique_id'] = $storno_data['id'];
						}else{
						    $pseudo_post['unique_id'] = $storno_data['record_id'];
						}
						$pseudo_post['grand_total'] = ($storno_data['invoice_total'] * (-1));

						$template_files = array('storno_invoice_sgbxi_pdf.html');
					}
					else
					{
						$template_files = array('invoice_sgbxi_pdf.html');
					}
    	
    				$orientation = array('P', 'L');
    				$background_pages = array('0'); //0 is first page;
    	
    				if($template_data)
    				{
    	
    					$template = INVOICE_TEMPLATE_PATH . '/' . $template_data[0]['file_path'];
    					//create public/joined_files/ dir
    					while(!is_dir(PDFJOIN_PATH))
    					{
    						mkdir(PDFJOIN_PATH);
    						if($i >= 50)
    						{
    							exit; //failsafe
    						}
    						$i++;
    					}
    	
    					//create public/joined_files/$clientid dir
    					$pdf_path = PDFJOIN_PATH . '/' . $clientid;
    	
    					while(!is_dir($pdf_path))
    					{
    						mkdir($pdf_path);
    						if($i >= 50)
    						{
    							exit; //failsafe
    						}
    						$i++;
    					}
    					
    					//create batch name
    					$_Batchname = false;
    					$_Batchname = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
    	
    					// generate invoice page
							$tokenfilter = array();
							$tokenfilter['patient'] = $pseudo_post['patientdetails'];
							$tokenfilter['invoice']['healthinsurancenumber'] = $pseudo_post['insurance_no'];
							$tokenfilter['invoice']['institutskennzeichen'] = $pseudo_post['client_ik'];
								
							$tokenfilter['invoice']['prefix'] = $pseudo_post['prefix'];
							$tokenfilter['invoice']['invoicenumber'] = $pseudo_post['invoice_number'];
							$tokenfilter['invoice']['full_invoice_number'] = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
							$tokenfilter['invoice']['invoicedate'] = strftime('%A, %d. %B %Y', strtotime($pseudo_post['completed_date']));
							$tokenfilter['invoice']['patient_pflegestufe'] = $pseudo_post['patient_pflegestufe'];
							$tokenfilter['invoice']['unique_id'] = $pseudo_post['unique_id'];
							$tokenfilter['invoice']['start_sgbxi_activity'] = $pseudo_post['start_sgbxi_activity'];
							$tokenfilter['invoice']['end_sgbxi_activity'] = $pseudo_post['end_sgbxi_activity'];
							if($pseudo_post['start_sgbxi_activity'] != "0000-00-00 00:00:00" && $pseudo_post['start_sgbxi_activity'] != "1970-01-01 00:00:00")
							{
								$tokenfilter['invoice']['start_sgbxi_activity'] = date('d.m.Y', strtotime($pseudo_post['start_sgbxi_activity']));
							}
							else
							{
								$tokenfilter['invoice']['start_sgbxi_activity'] = "-";
							}
							if($pseudo_post['end_sgbxi_activity'] != "0000-00-00 00:00:00" && $pseudo_post['end_sgbxi_activity'] != "1970-01-01 00:00:00")
							{
								$tokenfilter['invoice']['end_sgbxi_activity'] = date('d.m.Y', strtotime($pseudo_post['end_sgbxi_activity']));
							}
							else
							{
								$tokenfilter['invoice']['end_sgbv_activity'] = "-";
							}
							if($pseudo_post['first_active_day'] != "0000-00-00 00:00:00" && $pseudo_post['first_active_day'] != "1970-01-01 00:00:00")
							{
								$tokenfilter['invoice']['first_active_day'] = date('d.m.Y', strtotime($pseudo_post['first_active_day']));
							}
							else
							{
								$tokenfilter['invoice']['first_active_day'] = "-";
							}
							if($pseudo_post['last_active_day'] != "0000-00-00 00:00:00" && $pseudo_post['last_active_day'] != "1970-01-01 00:00:00")
							{
								$tokenfilter['invoice']['last_active_day'] = date('d.m.Y', strtotime($pseudo_post['last_active_day']));
							}
							else
							{
								$tokenfilter['invoice']['last_active_day'] = "-";
							}
							
							if($pseudo_post['patient_address'] != "")
							{
								$tokenfilter['invoice']['address'] = $pseudo_post['patient_address'];
							}
							else
							{
								$tokenfilter['invoice']['address'] = $pseudo_post['address'];
							}
								
							$tokenfilter['invoice']['invoicefooter'] = $pseudo_post['sapv_footer'];
							if($_REQUEST['stornopdf'] == '1' && $_REQUEST['storno'])
							{
								$tokenfilter['invoice']['invoiceamount'] = number_format($pseudo_post['grand_total'], '2', ',', '.');
							}
							else
							{
								$tokenfilter['invoice']['invoiceamount'] = number_format($pseudo_post['invoice_items']['grand_total'], '2', ',', '.');
							}
							
							$keyi = 0;
							foreach($pseudo_post['invoice_items'] as $kivi => $vivi)
							{
								if($kivi != 'grand_total')
								{
									$sgbxi_invoice_items['items'][$keyi][$kivi] = $vivi;
									$keyi++;
								}
						
							}
							
							if(count($pseudo_post['invoice_items']) > '0')
							{
								$rows = count($sgbxi_invoice_items['items']);
								$grid = new Pms_Grid($sgbxi_invoice_items['items'], 1, $rows, "bw_sgbxi_invoice_items_list_pdf.html");
								$grid_short = new Pms_Grid($sgbxi_invoice_items['items'], 1, $rows, "bw_sgbxi_invoice_items_list_pdf_short.html");
								
								$grid->invoice_total = $tokenfilter['invoice']['invoiceamount'];
								$grid->max_entries = $rows;
								
								$grid_short->invoice_total = $tokenfilter['invoice']['invoiceamount'];
								$grid_short->max_entries = $rows;
									
								$html_items = $grid->renderGrid();
								$html_items_short = $grid_short->renderGrid();
							}
							else
							{
								$html_items = "";
								$html_items_short = "";
							}
						
							$tokenfilter['invoice']['invoice_items_html'] = $html_items;
							$tokenfilter['invoice']['invoice_items_html_short'] = $html_items_short;
							//print_r($tokenfilter); exit;
								
							$docx_helper = $this->getHelper('CreateDocxFromTemplate');
							$docx_helper->setTokenController('invoice');
							
								
							$tmpstmp = isset($this->view->folder_stamp) ? $this->view->folder_stamp : time();
								
							while(!is_dir($pdf_path . '/' . $tmpstmp))
							{
								mkdir($pdf_path . '/' . $tmpstmp);
								if($i >= 50)
								{
									exit; //failsafe
								}
								$i++;
							}
								
							$template = INVOICE_TEMPLATE_PATH . '/' . $template_data[0]['file_path'];
								
							$destination_path = $pdf_path . '/' . $tmpstmp . '/';
								
							$docx_helper->setOutputFile($destination_path.$_Batchname);
								
								
							//do not add extension !
							$docx_helper->setBrowserFilename($_Batchname);
								
							$docx_helper->create_pdf ($template, $tokenfilter) ;
							
							$docx_helper->download_file();
							exit;
    				}
    				else
    				{
    					$this->generate_pdf($pseudo_post, "SGB_XI", $template_files, $orientation, $background_pages);
    				}
    			}
    		}
    	
    		$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=bw_sgbxi_invoice');
    	}
    }
    
    private function generateheinvoice($params)
    {
    	if(isset($params['print_job']) && $params['print_job'] == '1'){
    		$this->_helper->layout->setLayout('layout_ajax');
    		$this->_helper->viewRenderer->setNoRender();
    	}
    	 
    	setlocale(LC_ALL, 'de_DE.UTF-8');
    	//$logininfo = new Zend_Session_Namespace('Login_Info');
    	//$tm = new TabMenus();
    	$p_list = new PriceList();
    	$form_types = new FormTypes();
    	$sapvs = new SapvVerordnung();
    	$patientmaster = new PatientMaster();
    	//$sapvverordnung = new SapvVerordnung();
    	$pflege = new PatientMaintainanceStage();
    	$locations = new Locations();
    	$pat_locations = new PatientLocation();
    	$hi_perms = new HealthInsurancePermissions();
    	$phelathinsurance = new PatientHealthInsurance();
    	$healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
    	$boxes = new LettersTextBoxes();
    	$client = new Client();
    	$he_invoices = new HeInvoices();
		$he_invoices_items_p = new HeInvoiceItemsPeriod();
		$he_invoice_form = new Application_Form_HeInvoices();
		$p_hessen = new PriceHessen();
		$p_xbdt_goaii = new PriceXbdtActions();
		$ppun = new PpunIpid();
		
		
    	if(isset($params) && !empty($params)){
    		$_REQUEST = $params;
    		$this->_helper->viewRenderer->setNoRender();
    	}
    	 
    	//var_dump($_REQUEST); exit;
    	//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
    	$clientid = isset($_REQUEST['clientid']) && ! empty($_REQUEST['clientid']) ? $_REQUEST['clientid'] :  $this->clientid;
    	$userid = isset($_REQUEST['userid']) && ! empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $this->userid;
    	
    	$ipids = $_REQUEST['ipids'];
    	 
    	//load template data
    	$template_data = InvoiceTemplates::get_template($clientid, false, '1', $_REQUEST['invoice_type']);
    	 
    	/* if(!$template_data && $_REQUEST['get_pdf'] == '1'){
    	 $this->redirect(APP_BASE . 'invoiceclient/patientlist?flg=notemplate');
    	 exit;
    	 } */
    	 
    	if(!empty($ipids))
    	{

    		$letter_boxes_details = $boxes->client_letter_boxes($clientid);
    		$client_details = $client->getClientDataByid($clientid);
    		$this->view->client_details = $client_details[0];
    		
    		$modules =  new Modules();
    		$clientModules = $modules->get_client_modules($clientid);
    		
    		//Get client locations
    		$locationsarray = $locations->getLocations($clientid, 3); //get location_id => location_type
    		
    		foreach($locationsarray as $k_loc_arr => $v_loc_arr)
    		{
    			if($v_loc_arr == '2')
    			{
    				$hospiz_location_ids[] = $k_loc_arr;
    			}
    		
    			if($v_loc_arr == '1')
    			{
    				$hospital_location_ids[] = $k_loc_arr;
    			}
    		}
    		
    		$pat_locations_array = $pat_locations->get_valid_patients_locations($ipids);
    		//var_dump($pat_locations_array); exit;
    		
    		//patient days
    		$conditions['client'] = $clientid;
    		$conditions['ipids'] = $ipids;
    		$conditions['periods'][0]['start'] = '2009-01-01';
    		$conditions['periods'][0]['end'] = date('Y-m-d');
    		 
    		$sql = 'e.epid, p.ipid, e.ipid,';
    		$sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    		$sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    		$sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    		$sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    		$sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    		$sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    		 
    		//be aware of date d.m.Y format here
    		$patient_days = Pms_CommonData::patients_days($conditions, $sql);
    		
    		$patient_admissions = $patientmaster->getTreatedDaysRealMultiple($ipids);
    		//print_R($patient_days); exit;
    		$all_patients_periods = array();
    		foreach($patient_days as $k_ipid => $patient_data)
    		{
    			$fall_key = '0';
    			foreach($patient_data['active_periods'] as $k_period => $v_period)
    			{
    				$admission_date_fl[$k_ipid][$fall_key] = $v_period['start'];
    				$fall_key++;
    			}   			
    			
    			//all patients periods
    			$all_patients_periods = array_merge_recursive($all_patients_periods, $patient_data['active_periods']);
    			 
    			//used in flatrate
    			if(empty($patient_periods[$k_ipid]))
    			{
    				$patient_periods[$k_ipid] = array();
    			}
    			 
    			array_walk_recursive($patient_data['active_periods'], function(&$value) {
    				$value = date("Y-m-d", strtotime($value));
    			});
    				$patient_periods[$k_ipid] = array_merge($patient_periods[$k_ipid], $patient_data['active_periods']);
    
    				//hospital days cs
    				if(!empty($patient_data['hospital']['real_days_cs']))
    				{
    					$hospital_days_cs[$k_ipid] = $patient_data['hospital']['real_days_cs'];
    					array_walk($hospital_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//hospiz days cs
    				if(!empty($patient_data['hospiz']['real_days_cs']))
    				{
    					$hospiz_days_cs[$k_ipid] = $patient_data['hospiz']['real_days_cs'];
    					array_walk($hospiz_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//real active days
    				if(!empty($patient_data['real_active_days']))
    				{
    					$active_days_in_period_cs[$k_ipid] = $patient_data['real_active_days'];
    					array_walk($active_days_in_period_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//treatment days
    				if(!empty($patient_data['treatment_days']))
    				{
    					$treatment_days_cs[$k_ipid] = $patient_data['treatment_days'];
    					array_walk($treatment_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//active days
    				if(!empty($patient_data['active_days']))
    				{
    					$active_days[$k_ipid] = $patient_data['active_days'];
    					array_walk($active_days[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				if(empty($hospital_days_cs[$k_ipid]))
    				{
    					$hospital_days_cs[$k_ipid] = array();
    				}
    
    				if(empty($hospiz_days_cs[$k_ipid]))
    				{
    					$hospiz_days_cs[$k_ipid] = array();
    				}
    
    				$hospital_hospiz_days_cs[$k_ipid] = array_merge($hospital_days_cs[$k_ipid], $hospiz_days_cs[$k_ipid]);
    				
    				
    				
    				
    		}
    		 
    		$all_patients_periods = array_values($all_patients_periods);
    		 
    		foreach($all_patients_periods as $k_period => $v_period)
    		{
    			if(empty($months))
    			{
    				$months = array();
    			}
    			 
    			$period_months = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], 'Y-m');
    			$months = array_merge($months, $period_months);
    		}
    		$months = array_values(array_unique($months));
    		 
    		foreach($months as $k_m => $v_m)
    		{
    			$months_unsorted[strtotime($v_m)] = $v_m;
    		}
    		ksort($months_unsorted);
    		$months = array_values(array_unique($months_unsorted));
    		 
    		foreach($months as $k_month => $v_month)
    		{
    			if(!function_exists('cal_days_in_month'))
    			{
    				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
    			}
    			else
    			{
    				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
    			}
    			 
    			$months_details[$v_month]['start'] = $v_month . "-01";
    			$months_details[$v_month]['days_in_month'] = $month_days;
    			$months_details[$v_month]['end'] = $v_month . '-' . $month_days;
    			 
    			//$month_select_array[$v_month] = $v_month;
    			$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
    		}
    		
    		$patient_location_changes = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			foreach($pat_locations_array[$v_ipid] as $k_p_location => $v_p_location)
    			{
    				if($v_p_location['valid_till'] == '0000-00-00 00:00:00')
    				{
    					$pat_locations_array[$v_ipid][$k_p_location]['valid_till'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d', time())));
    				}
    			}
    			 
    			//get hospiz and hospital locations
    			foreach($pat_locations_array[$v_ipid] as $k_p_location => $v_p_location)
    			{
    				if(in_array($v_p_location['location_id'], $hospiz_location_ids))
    				{
    					$hospiz_patient_locations[$v_ipid][] = $v_p_location;
    				}
    				 
    				if(in_array($v_p_location['location_id'], $hospital_location_ids))
    				{
    					$hospital_patient_locations[$v_ipid][] = $v_p_location;
    				}
    			}
    			 
    			foreach($hospiz_patient_locations[$v_ipid] as $k_pat_hospiz => $v_pat_hospiz)
    			{
    				$start_hospiz = date('Y-m-d', strtotime($v_pat_hospiz['valid_from']));
    				$end_hospiz = date('Y-m-d', strtotime($v_pat_hospiz['valid_till']));
    				 
    				$hospiz_days = $patientmaster->getDaysInBetween($start_hospiz, $end_hospiz);
    				if(!empty($hospiz_days))
    				{
    					//					$hospiz_days_arr = array_merge($hospiz_days_arr, $hospiz_days);
    			   
    					if(empty($hospiz_days_arr_type[$v_ipid][$v_pat_hospiz['id']]))
    					{
    						$hospiz_days_arr_type[$v_ipid][$v_pat_hospiz['id']] = array();
    					}
    					$hospiz_days_arr_type[$v_ipid][$v_pat_hospiz['id']] = array_merge($hospiz_days_arr_type[$v_pat_hospiz['id']], $hospiz_days);
    				}
    			}
    			 
    			foreach($hospiz_days_arr_type[[$v_ipid]] as $k_h_ty => $v_h_ty)
    			{
    				foreach($v_h_ty as $k_hospiz_day => $v_hospiz_day)
    				{
    					if(!in_array($v_hospiz_day, $hospiz_days_cs[[$v_ipid]]))
    					{
    						unset($hospiz_days_arr_type[[$v_ipid]][$k_h_ty][$k_hospiz_day]);
    						array_values($hospiz_days_arr_type[[$v_ipid]][$k_h_ty]);
    					}
    				}
    			}
    			
    			$patient_location_changes[$v_ipid] = true;
    			if(count($pat_locations_array[$v_ipid]) == '0')//no location so no change
    			{
    				$patient_location_changes[$v_ipid] = false;
    			}
    			else if(count($pat_locations_array[$v_ipid]) > '0' && count($hospiz_patient_locations[$v_ipid]) == '0')//no hospiz so no change
    			{
    				$patient_location_changes[$v_ipid] = false;
    			}
    			else if(count($pat_locations_array[$v_ipid]) > '0' && count($hospiz_patient_locations[$v_ipid]) > '0')//all locations hospiz
    			{
    				//double check if all period is hospiz
    				$patient_cycle_days_cs[$v_ipid] = $patient_days[$v_ipid]['active_days'];
    				//TODO-3739 Carmen 22.01.2021
    				array_walk($patient_cycle_days_cs[$v_ipid], function(&$value) {
    					$value = date('Y-m-d', strtotime($value));
    				});
    				//--
    				foreach($patient_cycle_days_cs[$v_ipid] as $k_cday_pat => $v_cday_pat)
    				{
    					if(in_array($v_cday_pat, $hospiz_days_cs[$v_ipid]))
    					{
    						$days_in_hospiz[$v_ipid][] = $v_cday_pat;
    					}
    					else
    					{
    						$days_non_hospiz[$v_ipid][] = $v_cday_pat;
    					}
    				}
    			
    				if(count($days_in_hospiz[$v_ipid]) != count($patient_cycle_days_cs[$v_ipid]) && count($days_non_hospiz[$v_ipid]) != count($patient_cycle_days_cs[$v_ipid]))
    				{
    					//change
    					$patient_location_changes[$v_ipid] = true;
    				}
    				else
    				{
    					$patient_location_changes[$v_ipid] = false;
    				}
    			}    			 
    		}
    
    		//loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)
    		foreach($ipids as $k_sel_pat => $v_sel_pat)
    		{
    			//get patients sapvs last fall
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'sapvid')
    			{
    				$selected_sapv_falls_ipids[] = $v_sel_pat;
    				$selected_sapv_falls[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			//get patient month fall
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'list')
    			{
    				$selected_fall_ipids[] = $v_sel_pat;
    				$selected_month[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'admission')
    			{
    				$admission_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'quarter')
    			{
    				$quarter_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    		}
    		 
    		$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($selected_sapv_falls_ipids, true);
    		foreach($selected_sapv_falls as $k_ipid => $fall_id)
    		{
    			$patients_sapv[$k_ipid] = $fall_id;
    			$patients_selected_periods[$k_ipid] = $patients_sapv_periods[$k_ipid][$fall_id];
    			$_REQUEST['nosapvperiod'][$k_ipid] = '0';
    			$_REQUEST['period'] = $patients_selected_periods;
    		}
    
    		//rewrite the periods array if the period is entire month not sapv fall
    		$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
    
    		foreach($patients_sapv_periods as $k_sapv_ipid => $v_sapvs)
    		{
    			foreach($v_sapvs as $k_sapv_id => $v_sapv_data)
    			{
    				if(empty($sapv_days[$v_sapv_data['ipid']]))
    				{
    					$sapv_days[$v_sapv_data['ipid']] = array();
    				}
    				 
    				$sapv_days[$v_sapv_data['ipid']] = array_merge($sapv_days[$v_sapv_data['ipid']], $v_sapv_data['days']);
    				$sapv_days[$v_sapv_data['ipid']] = array_values(array_unique($sapv_days[$v_sapv_data['ipid']]));
    			}
    		}
    		 
    
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			if(!in_array($v_ipid, $selected_sapv_falls_ipids))
    			{
    				if(array_key_exists($v_ipid, $admission_fall))
    				{
    					$selected_period[$v_ipid] = $patient_days[$v_ipid]['active_periods'][$admission_fall[$v_ipid]];
    					 
    					array_walk($selected_period[$v_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    						 
    						$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($selected_period[$v_ipid]['start'], $selected_period[$v_ipid]['end']);
    						 
    						array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    							 
    							$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    							$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    							$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    							 
    							 
    							array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    								$value = date("d.m.Y", strtotime($value));
    							});
    								 
    								//exclude outside admission falls days from sapv!
    								if(empty($sapv_days[$v_ipid]))
    								{
    									$sapv_days[$v_ipid] = array();
    								}
    								 
    								if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    								{
    									$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    								}
    								$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    								$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    								 
    								$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    								$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    								 
    								$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    								$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    								 
    								//get all days of all sapvs in a period
    								$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    								$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    								 
    								 
    								$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    								$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    								 
    								$last_sapv_data['ipid'] = $v_ipid;
    								$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    								$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    								$sapv_last_require_data[] = $last_sapv_data;
    
    								$_REQUEST['admissionid'][$v_ipid] = $admission_fall[$v_ipid];
    				}
    				//ISPC-2461
    				elseif(array_key_exists($v_ipid, $quarter_fall))
    				{
    					$post_q = $_REQUEST[$v_ipid]['selected_period'];
    					$post_q_arr = explode("/",$post_q);
    					$q_no = (int)$post_q_arr[0];
    					$q_year = (int)$post_q_arr[1];
    					 
    					$q_per = array();
    					$quarter_start = "";
    					$quarter_end = "";
    					 
    					$q_per = Pms_CommonData::get_dates_of_quarter($q_no,$q_year,'Y-m-d');
    					$quarter_start = $q_per['start'];
    					$quarter_end = $q_per['end'];
    					 
    					$selected_period[$v_ipid] = array();
    					$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($quarter_start, $quarter_end);
    					$selected_period[$v_ipid]['start'] = $quarter_start;
    					$selected_period[$v_ipid]['end'] = $quarter_end;
    					 
    					array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    						$value = date("d.m.Y", strtotime($value));
    					});
    						 
    						$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    						$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    						$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    						 
    						 
    						array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    							 
    							//exclude outside admission falls days from sapv!
    							if(empty($sapv_days[$v_ipid]))
    							{
    								$sapv_days[$v_ipid] = array();
    							}
    							 
    							if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    							{
    								$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    							}
    							$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    							$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    							 
    							$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    							$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    							 
    							$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    							$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    							 
    							//get all days of all sapvs in a period
    							$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    							$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    							 
    							$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    							$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    							 
    							$last_sapv_data['ipid'] = $v_ipid;
    							$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    							$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    							$sapv_last_require_data[] = $last_sapv_data;
    							 
    							$_REQUEST['quarterid'][$v_ipid] = $quarter_fall[$v_ipid];
    				}
    				else
    				{
    					 
    					$start_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['start']));
    					$end_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['end']));
    					 
    					$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    					$_REQUEST['selected_period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    					$_REQUEST['sapv_in_period'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    					$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    					$_REQUEST['period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    					$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    					$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    					 
    					$last_sapv_data['ipid'] = $v_ipid;
    					$last_sapv_data['start_period'] = date('Y-m-d', strtotime($start_dmy));
    					$last_sapv_data['end_period'] = date('Y-m-d', strtotime($end_dmy));
    					$sapv_last_require_data[] = $last_sapv_data;
    				}
    			}
    		}
    		//print_r($_REQUEST); exit;
    		//get all sapv details
    		$all_sapvs = array();
    		$all_sapvs = $sapvs->get_all_sapvs($ipids);
    		 
    		$sapv2ipid = array();
    		/* foreach($all_sapvs as $k=>$sdata){
    			$sapv2ipid[$sdata['ipid']][] = $sdata;
    		} */
    		 
    		foreach($all_sapvs as $k_sapv => $v_sapv)
    		{
    			$sapv2ipid[$v_sapv['ipid']][] = $v_sapv;
    			if(empty($sapv_days_overall[$v_sapv['ipid']]))
    			{
    				$sapv_days_overall[$v_sapv['ipid']] = array();
    			}
    			 
    			$start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
    			 
    			if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00')
    			{
    				$end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
    			}
    			else
    			{
    				$end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
    			}
    			 
    			//FIND A WAY TO GET MULTIPLE LAST SAPV IN EACH PERIOD FOR EACH PATIENT
    			if($last_sapvs_in_period[$v_sapv['ipid']])
    			{
    				$_REQUEST['period'][$v_sapv['ipid']] = array_merge($_REQUEST['period'][$v_sapv['ipid']], $last_sapvs_in_period[$v_sapv['ipid']]);
    			}
    			 
    			$sapv_days_overall[$v_sapv['ipid']] = array_merge($sapv_days_overall[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end));
    			array_walk($sapv_days_overall[$v_sapv['ipid']], function(&$value) {
    				$value = date("d.m.Y", strtotime($value));
    			});
    				$sapv_days_overall[$v_sapv['ipid']] = array_values(array_unique($sapv_days_overall[$v_sapv['ipid']]));
    		}
    
    		foreach($last_sapvs_in_period as $k_sapvs => $v_sapvs)
    		{
    			foreach($v_sapvs as $k_sapvp => $v_sapvp)
    			{
    				$startp = date('Y-m-d', strtotime($v_sapvp['verordnungam']));
    
    				if($v_sapvp['status'] == '1' && $v_sapvp['verorddisabledate'] != '0000-00-00 00:00:00')
    				{
    					$endp = date('Y-m-d', strtotime($v_sapvp['verorddisabledate']));
    				}
    				else
    				{
    					$endp = date('Y-m-d', strtotime($v_sapvp['verordnungbis']));
    				}
    				if(empty($period_sapv_alldays[$v_sapvp['ipid']]))
    				{
    					$period_sapv_alldays[$v_sapvp['ipid']] = array();
    				}
    				$period_sapv_alldays[$v_sapvp['ipid']] = array_merge($period_sapv_alldays[$v_sapvp['ipid']], PatientMaster::getDaysInBetween($startp, $endp));
    			}
    		}
    
    
    		$_REQUEST['period_sapvs_alldays'] = $period_sapv_alldays;
    		$_REQUEST['sapv_overall'] = $sapv_days_overall;
    
    		$current_period = array();
    		foreach($_REQUEST['period'] as $ipidp => $vipidp)
    		{
    			$current_period[$ipidp] = $vipidp;
    			if($_REQUEST['sapv_in_period'][$ipidp])
    			{
    				$sapv_in_period[$ipidp] = $_REQUEST['sapv_in_period'][$ipidp];
    			}
    			else
    			{
    				$sapv_in_period[$ipidp] = array();
    			}
    		}
    
    		//Healthinsurance
    		$healthinsu_multi_array = array();
    		$healthinsu_multi_array = $phelathinsurance->get_multiple_patient_healthinsurance($ipids, true);
    		 
    		$hi_companyids  =array();
    		foreach($healthinsu_multi_array as $k_hi => $v_hi)
    		{
    			$hi_companyids[] = $v_hi['companyid'];
    		}
    		
    		//multiple hi subdivisions && hi subdivisions permissions
    		$divisions = HealthInsurancePermissions::getClientHealthInsurancePermissions($clientid);
    		
    		//patientheathinsurance
    		if($divisions)
    		{
    			/* foreach($healthinsu_multi_array as $k_hi => $v_hi)
    			 {
    			 $hi_companyids[] = $v_hi['companyid'];
    			 } */
    			 
    			$healthinsu_subdiv_arr = PatientHealthInsurance2Subdivisions::get_hi_subdivisions_multiple($hi_companyids);
    		}
    		
    		
    		
    		$shortcuts = Pms_CommonData::get_prices_shortcuts();
    		foreach($shortcuts['hessen'] as $type => $list_items_array){
    			 
    			if($type == $healthinsu_array[0]['he_price_list_type']){
    				foreach($list_items_array as $k=>$item_sh){
    					$hessen_related_dta[$item_sh] = strtoupper ($item_sh) .'  -  '.$this->view->translate('shortcut_description_'.$item_sh);
    				}
    			}
    		}
    		$hessen_related_dta["custom_dta"] = $this->view->translate('enter custom DTA ID');
    		 
    		//$this->view->hessen_related_dta = $hessen_related_dta;
    		
    		$saved_xbdt_goa = PatientXbdtActions::get_actions($ipid);
    		$patients_saved_xbdt_goa = array();
    		foreach($saved_xbdt_goa as $kact => $vact)
    		{
    			$patients_saved_xbdt_goa[$vact['ipid']][] = $vact;
    		}
    		//print_r($saved_xbdt_goa); exit;
    		//get previous patient invoices
    		$previous_invoices = $he_invoices->get_previous_patient_invoices($ipids, $clientid);
    		$patients_previous_invoices = array();
    		foreach($previous_invoices as $kpi => $vpi)
    		{
    			$patients_previous_invoices[$vpi['ipid']][] = $vpi;
    		}
    		//print_R($patients_previous_invoices); exit;
    		 

    		$default_price_list = Pms_CommonData::get_default_price_shortcuts();
    		$cl_xa_goa = XbdtActions ::client_xbdt_actions ($clientid);
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			//call each calculation function based on healthinsurance price list type
    			$admission_date[$v_ipid] = $patient_admissions[$v_ipid]['admission_date'];
    			$dis_date[$v_ipid] = $patient_admissions[$v_ipid]['discharge_date'];
    			$price_list[$v_ipid] = $p_list->get_client_list_period($admission_date[$v_ipid], $dis_date[$v_ipid]);
    			
    			 
    			$pricelist_hessen = $p_hessen->get_prices($price_list[$v_ipid][0]['id'], $clientid, $shortcuts['hessen'], $default_price_list['hessen']);
    			
    			
    			foreach( $price_list[$v_ipid] as $k=>$pl){
    				$pricelist_xbdt_goa[$v_ipid][$pl['id']] = $p_xbdt_goaii->get_prices($pl['id'], $clientid);
    				$price_list_days[$v_ipid][$pl['id']] = $patientmaster->getDaysInBetween(date('Y-m-d',strtotime($pl['start'])), date('Y-m-d',strtotime($pl['end'])));
    			}
    			
    			
    			foreach($price_list_days[$v_ipid] as $list_id=>$dates){
    				foreach($dates as $k=>$pl_date){
    					$dates2pl[$v_ipid][$pl_date] = $list_id;
    				}
    			}
    			
    			
    			foreach($cl_xa_goa as $k=>$goa_act){
    				$xbdt_goa_list[$v_ipid][$goa_act['id']] = $goa_act;
    				$xbdt_goa_list[$v_ipid][$goa_act['id']]['shortcut'] = $goa_act['action_id'];
    				if(strlen($pricelist_xbdt_goa[$v_ipid][$goa_act['id']]['price'])>0){
    					$xbdt_goa_list[$v_ipid][$goa_act['id']]['price'] = $pricelist_xbdt_goa[$goa_act['id']]['price'];
    				} else{
    					$xbdt_goa_list[$v_ipid][$goa_act['id']]['price'] = '0.00';
    				}
    			}
    			
    			$cxg_cnt=0;
    			foreach($patients_saved_xbdt_goa[$v_ipid] as $kxg=>$cxg){
    				$cxg_cnt++;
    				if($cxg['file_id'] == "0"){
    					$xbdt_goa_actions[$v_ipid][$cxg_cnt] = $cxg;
    					$xbdt_goa_actions[$v_ipid][$cxg_cnt]['xbdt_action'] =  $cxg['id'];
    						
    					//$xbdt_goa_actions[$cxg['id']]['price'] =  $xbdt_goa_list[$cxg['action']]['price'];
    					// get price list for action date
    					if($pricelist_xbdt_goa[ $dates2pl[date("Y-m-d", strtotime($cxg['action_date']))]  ][$cxg['action']]['price']){
    						$xbdt_goa_actions[$v_ipid][$cxg_cnt]['price'] =  $pricelist_xbdt_goa[ $dates2pl[date("Y-m-d", strtotime($cxg['action_date']))]  ][$cxg['action']]['price'];
    					} else{
    						$xbdt_goa_actions[$v_ipid][$cxg_cnt]['price'] =  '0.00';
    					}
    					$xbdt_goa_actions[$v_ipid][$cxg_cnt]['name'] =  $xbdt_goa_list[$cxg['action']]['name'];
    					$xbdt_goa_actions[$v_ipid][$cxg_cnt]['shortcut'] =  $xbdt_goa_list[$cxg['action']]['action_id'];
    					$xbdt_goa_actions[$v_ipid][$cxg_cnt]['from_date'][] =  date("Y-m-d H:i", strtotime($cxg['action_date']));
    					$xbdt_goa_actions[$v_ipid][$cxg_cnt]['from_date_view'][] =  date("d.m.Y", strtotime($cxg['action_date']));
    					$xbdt_goa_actions[$v_ipid][$cxg_cnt]['till_date'][] =  date("Y-m-d H:i", strtotime($cxg['action_date']));
    				}
    			}
    			//$this->view->xbdt_goa_actions = $xbdt_goa_actions;
    		
    		
    			foreach($patients_previous_invoices[$v_ipid] as $k_prev_inv => $v_prev_inv)
    			{
    				$previous_inv[$v_ipid][] = array(
    						'from' => date('d.m.Y', strtotime($v_prev_inv['invoice_start'])),
    						'till' => date('d.m.Y', strtotime($v_prev_inv['invoice_end'])),
    						'shortcut' => 'INV' . $v_prev_inv['id'],
    						'description' => $this->view->translate('invoice') . ' ' . $v_prev_inv['prefix'] . $v_prev_inv['invoice_number'],
    						'qty' => '1',
    						'price' => $v_prev_inv['invoice_total'],
    						'total' => Pms_CommonData::str2num(($v_prev_inv['invoice_total'] * (-1))),
    						'custom' => '2',
    				);
    				$previous_invoices_ids[$v_ipid][] = $v_prev_inv['id']; //TODO-3739 Carmen 22.01.2021
    			}
    		
    		}
    		//var_dump($previous_inv); exit;
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$previous_inv[$v_ipid] = $this->array_sort($previous_inv[$v_ipid], 'from', SORT_ . strtoupper(ASC));
    		}
    		
    		$prev_inv_items_periods[$v_ipid] = $he_invoices_items_p->get_flatrate_items_period($previous_invoices_ids[$v_ipid]); //TODO=3739 Carmen 22.01.2021   		
    		
    		//var_dump($patient_address); exit;
    		// get pflegestufe for all
    		
    		$all_pflegestufe = array();
    		$all_pflegestufe = Doctrine_Query::create()
    		->select("*")
    		->from('PatientMaintainanceStage')
    		->whereIn("ipid", $ipids)
    		->orderBy('fromdate,create_date asc')
    		->fetchArray();
    		
    		$pflegesufe2ipid = array();
    		foreach($all_pflegestufe as $k=>$pflg) {
    			$pflegesufe2ipid[$pflg['ipid']][] = $pflg;
    		}
    		 
    		$pathelathinsu = array();
    		$patient_address = array();
    		$hi_address = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$pathelathinsu[$v_ipid]['insurance_no'] = $healthinsu_multi_array[$v_ipid]['insurance_no'];
    			if($healthinsu_multi_array[$v_ipid]['company_name'] != "")
    			{
    				$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company_name'];
    			}
    			else
    			{
    				$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company']['name'];
    			}
    			 
    			if($healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				//			get patient name and adress
    				$patient_address[$v_ipid] = '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['first_name']) . ' ' . htmlspecialchars($patient_days[$v_ipid]['details']['last_name']) . '<br />';
    				$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['street1']) . '<br />';
    				$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['zip']) . ' ' . '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['city']);
    			}
    			 
    			if(!empty($healthinsu_subdiv_arr[$v_ipid]['1']['name']))
    			{
    				//get new SAPV hi address
    				$hi_address[$v_ipid] = '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['name'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['insurance_provider'] . '<br />';
    				//$hi_address .= '&nbsp;' . $healthinsu_subdiv_arr[1]['contact_person'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['street1'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['zip'] . ' ' . $healthinsu_subdiv_arr[$v_ipid][1]['city'];
    
    				$pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_subdiv_arr[$v_ipid][1]['iknumber'];
    				$pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_subdiv_arr[$v_ipid][1]['kvnumber'];
    			}
    			else
    			{
    				//get old hi_address
    				$hi_address[$v_ipid] = '&nbsp;' . $pathelathinsu[$v_ipid]['name'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_insurance_provider'] . '<br />';
    				//$hi_address .= '&nbsp;' . $healthinsu_array[0]['ins_contactperson'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_street'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_zip'] . ' ' . $healthinsu_multi_array[$v_ipid]['ins_city'];
    
    				$pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_multi_array[$v_ipid]['institutskennzeichen'];
    				$pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_multi_array[$v_ipid]['kvk_no'];
    			}
    			
    			//new columns
    			if($clientModules['90'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "0")
    			{
    				//get debtor number from patient healthinsurance
    				if(strlen($healthinsu_multi_array[$v_ipid]['ins_debtor_number']) > '0')
    				{
    					$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['ins_debtor_number'];
    				}
    				else
    				{
    					$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['company']['debtor_number'];
    				}
    			}
    			if($clientModules['88'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				//get ppun (private patient unique number)
    				$ppun_number = $ppun->check_patient_ppun($v_ipid, $clientid);
    				if($ppun_number)
    				{
    					$pathelathinsu[$v_ipid]['ppun']= $ppun_number['ppun'];
    					$pathelathinsu[$v_ipid]['debitor_number']= $ppun_number['ppun'];
    				}
    			}
    			//--
    			//exclude sapv fall calculated days from other calculation methods
    			
    			if(!empty($healthinsu_multi_array[$v_ipid]['company']['he_price_list_type'])){
    				$h_type[$v_ipid] = $healthinsu_multi_array[$v_ipid]['company']['he_price_list_type'];
    			}else{
    				$h_type[$v_ipid] = false;
    			}
    			// 			$sapv_fall_items = $this->calculate_sapv_be_fall($ipid, $patient_admissions[$ipid], $pricelist_hessen['sapvbe']);
    			$sapv_fall_items[$v_ipid] = $this->calculate_sapv_be_fall($v_ipid, $patient_admissions[$v_ipid], $pricelist_hessen,$h_type[$v_ipid]);
    			
    			if(empty($excluded_fall_days[$v_ipid]))
    			{
    				$excluded_fall_days[$v_ipid] = array();
    			}
    			 
    			$excluded_be_falls[$v_ipid] = array();
    			foreach($sapv_fall_items[$v_ipid] as $k_sapv_f => $v_sapv_f)
    			{
    				foreach($v_sapv_f['from_date'] as $k_sapv_fall => $v_sapv_fall_start)
    				{
    					$start = $v_sapv_fall_start;
    					$end = $v_sapv_f['till_date'][$k_sapv_fall];
    					 
    					$excluded_fall_days[$v_ipid] = array_merge($excluded_fall_days[$v_ipid], $patientmaster->getDaysInBetween($start, $end));
    				}
    				$excluded_be_falls[$v_ipid][] = $v_sapv_f['fall'];
    			}
    			 
    			//remove hospital from cycle days array
    			$patient_cycle_days_cs[$v_ipid] = $patient_days[$v_ipid]['active_days'];
    			//TODO-3739 Carmen 22.01.2021
    			array_walk($patient_cycle_days_cs[$v_ipid], function(&$value) {
    				$value = date('Y-m-d', strtotime($value));
    			});
    			//--
    			$no_hospital_cycle_days[$v_ipid] = array_values(array_diff($patient_cycle_days_cs[$v_ipid], $hospital_days_cs[$v_ipid]));
    			
    			array_walk($no_hospital_cycle_days[$v_ipid], function(&$value) {
    				$value = date('Y-m-d', strtotime($value));
    			});
    			
    			//append hospiz days to a new treatment days array (hospiz does not pause fl)
    			if(!empty($patient_days[$v_ipid]['hospiz']['real_days_cs']))
    			{
    				$patient_days[$v_ipid]['treatment_days_with_hospiz'] = array_merge($patient_days[$v_ipid]['treatment_days'], $patient_days[$v_ipid]['hospiz']['real_days_cs']);
    			}
    			else
    			{
    				$patient_days[$v_ipid]['treatment_days_with_hospiz'] = $patient_days[$v_ipid]['treatment_days'];
    			}
    			
    			array_walk($patient_days[$v_ipid]['treatment_days_with_hospiz'], function(&$value) {
    				$value = strtotime($value);
    			});
    			
    			asort($patient_days[$v_ipid]['treatment_days_with_hospiz']);
    			$patient_days[$v_ipid]['treatment_days_with_hospiz'] = array_values($patient_days[$v_ipid]['treatment_days_with_hospiz']);
    			array_walk($patient_days[$v_ipid]['treatment_days_with_hospiz'], function(&$value) {
    				$value = date('Y-m-d', $value);
    			});
    			
    			
    			$patient_days[$v_ipid]['treatment_days_with_hospiz'] = array_diff($patient_days[$v_ipid]['treatment_days_with_hospiz'], $excluded_fall_days[$v_ipid]);
    			
    			$flatrate_days_calculated[$v_ipid] = false;
    			foreach($admission_date_fl[$v_ipid] as $k_fall => $admission_fall_date)
    			{
    				if(!in_array($k_fall, $excluded_be_falls[$v_ipid]) && $flatrate_days_calculated[$v_ipid] === false)
    				{
    					$pat_first_location_array[$v_ipid] = $pat_locations->get_first_location($v_ipid, $admission_fall_date);
    					
    					//determine first admision location normal/hospiz and calculate admission 10days flatrate days
    					$admission_details[$v_ipid] = array();
    			
    					//					OLD SYSTEM
    					//					$flatrate_start = date('Y-m-d', strtotime($admission_fall_date));
    					//					$flatrate_end = date('Y-m-d', strtotime('+9 days', strtotime($admission_fall_date)));
    					//
    					//					$admission_details['flatrate_days'] = $patientmaster->getDaysInBetween($flatrate_start, $flatrate_end);
    					//NEW SYSTEM
    					$i = 1;
    					foreach($patient_days[$v_ipid]['treatment_days_with_hospiz'] as $k_pat_day => $v_pat_day)
    					{
    						if($i <= '10')
    						{
    							$admission_details[$v_ipid]['flatrate_days'][] = strtotime($v_pat_day);
    						}
    							$i++;
    						}
    			
    						asort($admission_details[$v_ipid]['flatrate_days']);
    						$admission_details[$v_ipid]['flatrate_days'] = array_values($admission_details[$v_ipid]['flatrate_days']);
    						array_walk($admission_details[$v_ipid]['flatrate_days'], function(&$value, $index) {
    							$value = date("Y-m-d", $value);
    						});
    			
    						if(count($pat_first_location_array) > '0')
    						{
    							
    							//check if is hospiz
    							if(in_array($pat_first_location_array[$v_ipid][0]['location_id'], $hospiz_location_ids))
    									{
    										$admission_details[$v_ipid]['type'] = 'h';
    										
    									}
    									else //normal if no hospiz
    									{
    										$admission_details[$v_ipid]['type'] = 'n';
    									}
    								}
    								else //no first location in that day is considered normal admission
    								{
    									$admission_details[$v_ipid]['type'] = 'n';
    								}
    			
    								$flatrate_days_calculated[$v_ipid] = true;
    						}
    					}
    					
    					foreach($admission_details[$v_ipid]['flatrate_days'] as $k_date => $v_date)
    					{
    						if(!in_array($v_date, $no_hospital_cycle_days[$v_ipid]))
    						{
    							unset($admission_details[$v_ipid]['flatrate_days'][$k_date]);
    						}
    					}
    					
    					$admission_details[$v_ipid]['flatrate_days'] = array_values($admission_details[$v_ipid]['flatrate_days']);
    					
    					foreach($hospiz_days_arr_type[$v_ipid] as $k_hospiz_type_arr => $v_hospiz_type_arr)
    					{
    						$hospiz_days_arr_type[$v_ipid][$k_hospiz_type_arr] = array_values(array_intersect($hospiz_days_arr_type[$v_ipid][$k_hospiz_type_arr], $no_hospital_cycle_days[$v_ipid]));
    					}
    			
    					$no_hospital_cycle_days[$v_ipid] = array_diff($no_hospital_cycle_days[$v_ipid], $excluded_fall_days[$v_ipid]);
    					$hospiz_days_cs[$v_ipid] = array_diff($hospiz_days_cs[$v_ipid], $excluded_fall_days[$v_ipid]);

    					switch($healthinsu_multi_array[$v_ipid]['company']['he_price_list_type'])
    					{
    						case "vdek":
    							if(count($no_hospital_cycle_days[$v_ipid]) > '0' || count($hospiz_days_cs[$v_ipid]) > '0')
    							{
    								$invoice_items[$v_ipid] = $this->vdek_method($v_ipid, $admission_details[$v_ipid], $no_hospital_cycle_days[$v_ipid], $hospiz_days_cs[$v_ipid], $pricelist_hessen[$healthinsu_multi_array[$v_ipid]['company']['he_price_list_type']], $prev_inv_items_periods[$v_ipid]); //TODO-3739 Carmen 22.01.2021
    								
    							}
    					
    							break;
    					
    						case "privat":
    							if(count($no_hospital_cycle_days[$v_ipid]) > '0' || count($hospiz_days_cs[$v_ipid]) > '0')
    							{
    								$invoice_items[$v_ipid] = $this->privat_method($v_ipid, $admission_details[$v_ipid], $no_hospital_cycle_days[$v_ipid], $hospiz_days_cs[$v_ipid], $pricelist_hessen[$healthinsu_multi_array[$v_ipid]['company']['he_price_list_type']], $prev_inv_items_periods[$v_ipid]); //TODO-3739 Carmen 22.01.2021
    							}
    							break;
    					
    						case "primar":
    							if($patient_location_changes[$v_ipid])
    							{
    								if(count($no_hospital_cycle_days[$v_ipid]) > '0' || count($hospiz_days_cs[$v_ipid]) > '0')
    								{
    									//							$invoice_items = $this->primary_method_changes($ipid, $admission_details, $no_hospital_cycle_days, $hospiz_days_cs, $pricelist_hessen[$healthinsu_array[0]['he_price_list_type']], $hospiz_days_arr_type);
    									$invoice_items[$v_ipid] = $this->primary_method_changes_new($v_ipid, $admission_details[$v_ipid], $no_hospital_cycle_days[$v_ipid], $hospiz_days_cs[$v_ipid], $pricelist_hessen[$healthinsu_multi_array[$v_ipid]['company']['he_price_list_type']], $hospiz_days_arr_type[$v_ipid]);
    									if($_REQUEST['dqd'])
    									{
    										print_r("invoice_items\n");
    										print_r($invoice_items);
    										exit;
    									}
    								}
    							}
    							else
    							{
    								if(count($no_hospital_cycle_days[$v_ipid]) > '0' || count($hospiz_days_cs[$v_ipid]) > '0')
    								{
    									$invoice_items[$v_ipid] = $this->primary_method($v_ipid, $admission_details[$v_ipid], $no_hospital_cycle_days[$v_ipid], $hospiz_days_cs[$v_ipid], $pricelist_hessen[$healthinsu_multi_array[$v_ipid]['company']['he_price_list_type']], $excluded_fall_days[$v_ipid]);
    								}
    							}
    							ksort($invoice_items[$v_ipid], SORT_REGULAR);
    							break;
    					
    						default:
    							break;
    					}
    					
    					if(count($sapv_fall_items[$v_ipid]) > '0' && count($invoice_items[$v_ipid]) > '0')
    					{
    						//merge invoice items with fall items
    						$invoice_items[$v_ipid] = array_merge($invoice_items[$v_ipid], $sapv_fall_items[$v_ipid]);
    					}
    					else if(count($sapv_fall_items[$v_ipid]) > '0')
    					//			if(count($sapv_fall_items) > '0')
    					{
    						$invoice_items[$v_ipid] = $sapv_fall_items[$v_ipid];
    					}
    					else if(count($invoice_items[$v_ipid]) > '0')
    					{
    						$invoice_items[$v_ipid] = $invoice_items[$v_ipid];
    					}

    					foreach($invoice_items[$v_ipid] as $k_inv_item => $v_inv_item)
    					{
    						if(!in_array($k_inv_item, $excluded_normal_items[$v_ipid]))
    						{
    							//TODO-3739 Carmen 25.01.2021
    							$invoice_total[$v_ipid]['invoice_total'] += $v_inv_item['total'];
    							//--
    					
    							if(strlen($v_inv_item['from_date'][0]) == '10')
    							{
    								$invoice_items[$v_ipid][$k_inv_item]['start_date_filter'] = $v_inv_item['from_date'][0];
    							}
    							else
    							{
    								$invoice_items[$v_ipid][$k_inv_item]['start_date_filter'] = $v_inv_item['from_date'];
    							}
    						}
    					}
    					
    					$invoice_items[$v_ipid] = $this->array_sort($invoice_items[$v_ipid], 'start_date_filter', SORT_ . strtoupper(ASC));
    					
    					if($_REQUEST['dbg'] == 'items1')
    					{
    						print_r($invoice_items);
    						exit;
    					}
    					
    					//TODO-3739 Carmen 25.01.2021 - no need for automatically generated invoice
    					/* $invoice_data[$v_ipid]['items'] = $invoice_items[$v_ipid];
    					$invoice_data[$v_ipid]['list_type'] = $healthinsu_multi_array[$v_ipid]['he_price_list_type'];
    					
    					if($_REQUEST['dbg'] == 'items')
    					{
    						print_r($invoice_data);
    						exit;
    					}
    					if(strlen($invoice_data[$v_ipid]['footer']) == 0)
    					{
    						$invoice_data[$v_ipid]['footer'] = $letter_boxes_details[0]['sapv_invoice_footer'];
    					} */
    					//--
    					//$this->view->invoice_data = $invoice_data;
	
    		}
    		
    		//$pseudo_post = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			//TODO-3739  Carmen 22.01.2021
    			$pseudo_post = array();
    			//--
    					$pseudo_post['ipid'] = $v_ipid;
    					$pseudo_post['pricelist_type'] = $healthinsu_multi_array[$v_ipid]['company']['he_price_list_type'];
    					
    					$pseudo_post['items'] = $invoice_items[$v_ipid];
    					$pseudo_post['start_invoice'] = date('Y-m-d', strtotime($patient_admissions[$v_ipid]['admission_date']));
    					$pseudo_post['end_invoice'] = date('Y-m-d', strtotime($patient_admissions[$v_ipid]['discharge_date']));
    					
    					$pseudo_post['invoice_total'] = $invoice_total[$v_ipid]['invoice_total']; //TODO-3739 Carmen 25.01.2021
    					$pseudo_post['footer'] = $letter_boxes_details[0]['sapv_invoice_footer'];
    					
    					$pseudo_post['patientdetails'] = $patient_days[$v_ipid]['details'];
    					$pseudo_post['patientdetails']['birthd'] = date('d.m.Y', strtotime($patient_days[$v_ipid]['details']['birthd']));
    					
    					$invoice_pflegesute[$v_ipid] = array();
    					foreach($pflegesufe2ipid[$v_ipid] as $k=>$pflitem){
    							
    						if($pflitem['tilldate'] == "0000-00-00"){
    							$pflitem['tilldate'] == date('Y-m-d', strtotime($current_period[$v_ipid]['end']));
    						}
    							
    						if(Pms_CommonData::isintersected(date('Y-m-d', strtotime($current_period[$v_ipid]['start'])), date('Y-m-d', strtotime($current_period[$v_ipid]['end'])), $pflitem['fromdate'], $pflitem['tilldate'])){
    							$invoice_pflegesute[$v_ipid][] = $pflitem;
    						}
    							
    					}
    					 
    					$pflege_arr[$v_ipid] = array();
    					$pflege_arr[$v_ipid] = end($invoice_pflegesute[$v_ipid] );
    					 
    					if(!empty($pflege_arr[$v_ipid]))
    					{
    						$pseudo_post['patient_pflegestufe'] = $pflege_arr[$v_ipid]['stage'];
    					}
    					else
    					{
    						$pseudo_post['patient_pflegestufe'] = ' - ';
    					}
    					
    					$pseudo_post['client_ik'] = $client_details[0]['institutskennzeichen'];
    					$pseudo_post['client_details'] = $client_details[0];
    					$pseudo_post['clientid'] = $clientid;
    					
    					/* $pseudo_post['health_insurance'] = $pathelathinsu[$v_ipid]['name'];
    					$pseudo_post['health_insurance_ik'] = $pathelathinsu[$v_ipid]['health_insurance_ik'];
    					$pseudo_post['health_insurance_kassenr'] = $pathelathinsu[$v_ipid]['health_insurance_kassenr']; */
    					$pseudo_post['insurance_no'] = $pathelathinsu[$v_ipid]['insurance_no'];
    					 
    					$pseudo_post['patient_address'] = $patient_address[$v_ipid];
    					if(strlen($pseudo_post['address']) == '0')
    					{
    						$pseudo_post['address'] = $hi_address[$v_ipid];
    					}
    					
    					
    					$pseudo_post['previous_invoices'] = $previous_inv[$v_ipid];
    					
    					foreach($pseudo_post['previous_invoices'] as $kprevi => $vprevi)
    					{
    						$prev_val += (float)$vprevi['total'];
    					}
    					//$pseudo_post['invoice_total']  = number_format(($pseudo_post['invoice_total'] + $prev_val), '2');	//TODO-3739 Carmen 25.01.2021
    			//new columns
    					if($sapv_approved['dates'][$v_ipid] != '0000-00-00 00:00:00')
    					{
    						$pseudo_post['sapv_approve_date'] = $sapv_approved['dates'][$v_ipid];
    					}
    					else
    					{
    						$pseudo_post['sapv_approve_date'] = '';
    					}
    					$pseudo_post['sapv_approve_nr'] = $sapv_approved['numbers'][$v_ipid];
    			$pseudo_post['debitor_number'] = $pathelathinsu[$v_ipid]['debitor_number'];
    			$pseudo_post['ppun'] = $pathelathinsu[$v_ipid]['ppun'];
    			//--
    			//print_r($pseudo_post); exit;
    			if($_REQUEST['only_pdf'] == '0')
    			{
    				//second parameter is temp true||false
    				$high_invoice_nr = $he_invoices->get_next_invoice_number($clientid, true);
    					
    				$prefix = $high_invoice_nr['prefix'];
    				$invoicenumber = $high_invoice_nr['invoicenumber'];
    				
    				$pseudo_post['prefix'] = $prefix;
    				$pseudo_post['invoice_number'] = $invoicenumber;
    				
    				//insert invoice START
    				//$he_invoice_form = new Application_Form_HeInvoices();    				
    				$insert_invoice = $he_invoice_form->create_invoice($clientid, $pseudo_post);
    				//TODO-3739 Carmen 25.01.2021
    				/*if($insert_invoice)
    				{
    					$invoice_data = $he_invoices->getHeInvoice($insert_invoice);
    				}*/
    				//--
    				//var_dump($invoice_data); exit; */
    				
    			}
    			 
    			if($_REQUEST['get_pdf'] == '1')
    			{
    				//					print_r($post);
    				//					exit;
    				
    			    //TODO-3706 Ancuta 05.01.2021+TODO-3739 Carmen 25.01.2021
    			    /* if(!empty($invoice_data)){
    			        $pseudo_post['completed_date']  = $invoice_data['completed_date'];
    			        $pseudo_post['create_date']  = $invoice_data['create_date'];
    			        $pseudo_post['address']= $invoice_data['address'];
    			        $pseudo_post['items']= $invoice_data['items'];
    			    } */
    				//--
    				
    				$template_files = array('invoice_he_pdf.html');
    				$orientation = array('P');
    				$background_pages = array('0'); //0 is first page;
    				$pseudo_post['clientid'] = $clientid;
    				$pseudo_post['create_date'] = date('Y-m-d', time());
    				
    				//ISPC-2747 Lore 27.11.2020
    				$pseudo_post['show_box_active'] = '1';
    				$pseudo_post['show_box_patient'] = '1';
    				$pseudo_post['show_box_sapv'] = '1';
    				
    				if($template_data)
    				{
    					//TODO-3739 Carmen 25.01.2021 - we use data from generated invoice only for tokens
    					if($insert_invoice)
    					{
    						$invoice_data = $he_invoices->getHeInvoice($insert_invoice);
    						
    						$invoice_total = 0;
    						foreach($invoice_data['items'] as $k_inv_item => $v_inv_item)
    						{
    							$invoice_total += $v_inv_item['total'];
    							$invoice_data['items'][$k_inv_item]['start_date_filter'] = $v_inv_item['from_date'][0];
    								
    							if($v_inv_item['custom'] == '0')
    							{
    									
    								if(bccomp(number_format(($v_inv_item['qty'] * $v_inv_item['price']), 2, '.', ''), $v_inv_item['total'], '2') != '0' && bccomp($v_inv_item['percent'], '0.00', '2') == '0' && (str_replace('pa', '', $v_inv_item['shortcut']) || str_replace('pc', '', $v_inv_item['shortcut'])))
    								 {
    								 //get percent if item is (qty*price != total and custom=0)
    								 $percent = ($v_inv_item['total'] / $v_inv_item['price']);
    								 $invoice_data['items'][$k_inv_item]['percent'] = number_format(($percent * 100), '2', '.', '');
    								 }
    								 	
    								 if(bccomp($invoice_data['items'][$k_inv_item]['percent'], '0', '2') == '0')
    								 {
    								 unset($invoice_data['items'][$k_inv_item]['percent']);
    								 }
    							}
    							/* //make sure they are always last
    							 else if($v_inv_item['custom'] == '1')
    							 {
    							 $invoice_data['items'][$k_inv_item]['start_date_filter'] = date('Y-m-d H:i:s', strtotime('+ ' . $v_inv_item['id'] . ' seconds', strtotime($v_inv_item['create_date'])));
    							 unset($invoice_data['items'][$k_inv_item]['percent']);
    							 //					$invoice_data['items'][$k_inv_item]['start_date_filter'] = date('Y-m-d H:i:s', strtotime($v_inv_item['create_date']));
    							 } */
    							else if($v_inv_item['custom'] == '2') //always stay on bottom
    							{
    								$invoice_data['items'][$k_inv_item]['start_date_filter'] = date('Y-m-d H:i:s', strtotime('+1 year', time()));
    								unset($invoice_data['items'][$k_inv_item]['percent']);
    							}
    							/* //ispc-1871 -
    							 else if($v_inv_item['custom'] == '3')
    							 {
    							 $invoice_data['items'][$k_inv_item]['start_date_filter'] = $v_inv_item['from_date'];
    							 } */
    						}
    							
    						$invoice_data['items'] = $this->array_sort($invoice_data['items'], 'start_date_filter', SORT_ . strtoupper(ASC));
    					}
    					//--
    					$Batch_name = false;
    					$Batch_name = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
    					$Batch_name = Pms_CommonData::filter_filename($Batch_name, true);
    					
    					// generate invoice page
						$tokenfilter = array();
						$tokenfilter['invoice']['address'] = $pseudo_post['address'];
						//$tokenfilter['client']['city'] = $pdf_data['client_city'];
						//$tokenfilter['client']['institutskennzeichen'] = $pdf_data['ik_nummer'];
						
						//TODO-3739 Carmen 25.01.2021 - generate a draft invoice
						/* if($pseudo_post['completed_date'] != "0000-00-00 00:00:00" && $pseudo_post['completed_date'] != "1970-01-01 00:00:00")
						{
							$tokenfilter['invoice']['invoicedate'] = strftime('%A, %d. %B %Y', strtotime($pseudo_post['completed_date']));
						}
						else
						{ */
							$tokenfilter['invoice']['invoicedate'] = strftime('%A, %d. %B %Y', strtotime($pseudo_post['create_date']));
						//}
					
						$tokenfilter['invoice']['prefix'] = $pseudo_post['prefix'];
						$tokenfilter['invoice']['invoicenumber'] = $pseudo_post['invoice_number'];
						$tokenfilter['invoice']['full_invoice_number'] = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
						/* if($pdf_data['first_active_day'] != "0000-00-00 00:00:00" && $pdf_data['first_active_day'] != "1970-01-01 00:00:00")
						{
							$tokenfilter['invoice']['first_active_day'] = date('d.m.Y', strtotime($pdf_data['first_active_day']));
						}
						else
						{
							$tokenfilter['invoice']['first_active_day'] = "-";
						}
						if($pdf_data['last_active_day'] != "0000-00-00 00:00:00" && $pdf_data['last_active_day'] != "1970-01-01 00:00:00")
						{
							$tokenfilter['invoice']['last_active_day'] = date('d.m.Y', strtotime($pdf_data['last_active_day']));
						}
						else
						{
							$tokenfilter['invoice']['last_active_day'] = "-";
						} */
					
						$tokenfilter['invoice']['healthinsurancenumber'] = $pseudo_post['insurance_no'];
						//$tokenfilter['invoice']['health_insurance_ik'] = $pdf_data['health_insurance_ik'];
						//$tokenfilter['invoice']['healthinsurance_versnr'] = $pdf_data['healthinsurance_versnr'];
					
					
						$tokenfilter['patient'] = $pseudo_post['patientdetails'];
						$tokenfilter['invoice']['patient_pflegestufe'] = $pseudo_post['patient_pflegestufe'];
						$tokenfilter['footer'] = $pseudo_post['footer'];
						
						/* if($pdf_data['healthinsurance_name'] != "")
						{
							$tokenfilter['healthinsurance']['healthinsurance_name'] = $pdf_data['healthinsurance_name'];
						}
						else
						{
							$tokenfilter['healthinsurance']['healthinsurance_name'] = "--";
						}
					
						$tokenfilter['invoice']['unique_id'] = $pdf_data['unique_id'];
					
						if($pdf_data['current_period']['start'] != "0000-00-00 00:00:00" && $pdf_data['current_period']['start'] != "1970-01-01 00:00:00")
						{
							$tokenfilter['invoice']['invoice_period_start'] = date('d.m.Y', strtotime($pdf_data['current_period']['start']));
						}
						else
						{
							$tokenfilter['invoice']['invoice_period_start'] = "-";
						}
						if($pdf_data['current_period']['end'] != "0000-00-00 00:00:00" && $pdf_data['current_period']['end'] != "1970-01-01 00:00:00")
						{
							$tokenfilter['invoice']['invoice_period_end'] = date('d.m.Y', strtotime($pdf_data['current_period']['end']));
						}
						else
						{
							$tokenfilter['invoice']['invoice_period_end'] = "-";
						} */
					
						//TODO-3739 Carmen 25.01.2021 - we generate a draft invoice
						/* if($_REQUEST['stornopdf'] == '1' && $_REQUEST['storno'])
						{
							$tokenfilter['invoice']['invoiceamount'] = number_format($pseudo_post['invoice_total'], '2', ',', '.');
						}
						else
						{ */
							$tokenfilter['invoice']['invoiceamount'] = number_format($invoice_total, '2', ',', '.'); //TODO-3739 Carmen 25.01.2021
						//}
							
						$keyi = 0;
						$he_invoice_items = array();
						foreach($invoice_data['items'] as $kivi => $vivi)
						{
							$he_invoice_items['items'][$keyi]['shortcuts'][$kivi] = $vivi;
							$keyi++;
								
						}
					
						//TODO-3739 Carmen 22.01.2021
						/*if(count($pseudo_post['items']) > '0')
						{*/
						if(count($invoice_data['items']) > '0')
						{
						//--
							$rows = count($he_invoice_items['items']);
							$grid = new Pms_Grid($he_invoice_items['items'], 1, $rows, "he_invoice_items_list_pdf.html");
							//$grid_short = new Pms_Grid($sgbxi_invoice_items['items'], 1, $rows, "bw_sgbxi_invoice_items_list_pdf_short.html");
							
							$grid->invoice_total = $tokenfilter['invoice']['invoiceamount'];
							$grid->max_entries = $rows;
								
							/* $grid_short->invoice_total = $tokenfilter['invoice']['invoiceamount'];
							 $grid_short->max_entries = $rows; */
					
							$html_items = $grid->renderGrid();
							//$html_items_short = $grid_short->renderGrid();
						}
						else
						{
							$html_items = "";
							$html_items_short = "";
						}
							
						$tokenfilter['invoice']['invoice_items_html'] = $html_items;
						//$tokenfilter['invoice']['invoice_items_html_short'] = $html_items_short;
						//print_r($tokenfilter); exit;
							
						$docx_helper = $this->getHelper('CreateDocxFromTemplate');
						$docx_helper->setTokenController('invoice');
							
						//create public/joined_files/ dir
						while(!is_dir(PDFJOIN_PATH))
						{
							mkdir(PDFJOIN_PATH);
							if($ifile >= 50)
							{
								exit; //failsafe
							}
							$ifile++;
						}
							
						//create public/joined_files/$clientid dir
						$pdf_path = PDFJOIN_PATH . '/' . $clientid;
							
						while(!is_dir($pdf_path))
						{
							mkdir($pdf_path);
							if($ifile >= 50)
							{
								exit; //failsafe
							}
							$ifile++;
						}
							
						$tmpstmp = isset($this->view->folder_stamp) ? $this->view->folder_stamp : time();
							
						while(!is_dir($pdf_path . '/' . $tmpstmp))
						{
							mkdir($pdf_path . '/' . $tmpstmp);
							if($ifile >= 50)
							{
								exit; //failsafe
							}
							$ifile++;
						}
							
						$template = INVOICE_TEMPLATE_PATH . '/' . $template_data[0]['file_path'];
							
						$destination_path = $pdf_path . '/' . $tmpstmp . '/';
							
						$docx_helper->setOutputFile($destination_path.$Batch_name);
					
						//do not add extension !
						$docx_helper->setBrowserFilename($Batch_name);
							
						$docx_helper->create_pdf ($template, $tokenfilter) ;
							
						$docx_helper->download_file();
						exit;
    				}
    				else
    				{
    					//TODO-3739 Carmen 25.01.2021 - generate automatically a draft invoice
    					/* $item_counter = 0;
    					foreach($pseudo_post['custom']['shortcut'] as $k_cust_item => $v_cust_item)
    					{
    						$item_counter++;
    						if($pseudo_post['custom']['xbdt_action'][$k_cust_item]){
    							$postcustom = "3";
    						} else{
    							$postcustom = "1";
    						}
    					
    						//ispc-1871
    						//$post['items'][strtoupper($v_cust_item)] = array(
    						$pseudo_post['items'][$item_counter] = array(
    								'shortcut' => htmlspecialchars(strtoupper($v_cust_item)),
    								'from_date' => $post['custom']['from_date'][$k_cust_item],
    								'description' => $post['custom']['description'][$k_cust_item],
    								'qty' => $post['custom']['qty'][$k_cust_item],
    								'price' => $post['custom']['price'][$k_cust_item],
    								'total' => $post['custom']['total'][$k_cust_item],
    								'custom' => $postcustom
    						);
    					
    					
    						//$pseudo_post['invoice_total'] += $post['custom']['total'][$k_cust_item];
    					    	
    					} */
    					//--
    					
    					$this->generate_pdf($pseudo_post, "HeInvoice", $template_files, $orientation, $background_pages);
    				}
    			}
    		}
    		 
    		$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=he_invoice');
    	}
    }
    
     private function generate_joined_files_pdf($chk, $post, $pdfname, $filename, $invoice_type_special = false)
     {
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	$clientid = isset($post['clientid']) && !empty($post['clientid']) ? $post['clientid'] :  $logininfo->clientid;
    	$clientinfo = Pms_CommonData::getClientData($clientid);
    
    	if(isset($_GET['id'])){
    		$decid = Pms_Uuid::decrypt($_GET['id']);
    		$ipid = Pms_CommonData::getIpid($decid);
    	}
    	 
    	if($pdfname == "SocialcodePdfs")
    	{
    		if(strlen($post['hi_subdiv_address']) > 0)
    		{
    			if(strpos($post['hi_subdiv_address'],"style"))
    			{
    				$post['hi_subdiv_address'] = preg_replace('/style=\"(.*)\"/i', '', $post['hi_subdiv_address']);
    			}
    			 
    			$post['hi_subdiv_address'] = str_replace(array("<p>","<p >"), "", $post['hi_subdiv_address']);
    			$post['hi_subdiv_address'] = str_replace("</p>", "", $post['hi_subdiv_address']);
    			$post['hi_subdiv_address'] = str_replace("\n", "<br/>", $post['hi_subdiv_address']);
    		}
    
    		if(strlen($post['patient_address']) > 0)
    		{
    			if(strpos($post['patient_address'],"style"))
    			{
    				$post['patient_address'] = preg_replace('/style=\"(.*)\"/i', '', $post['patient_address']);
    			}
    			 
    			$post['patient_address'] = str_replace(array("<p>","<p >"), "", $post['patient_address']);
    			$post['patient_address'] = str_replace("</p>", "", $post['patient_address']);
    			$post['patient_address'] = str_replace("\n", "<br/>", $post['patient_address']);
    		}
    	}
    
    	$excluded_keys = array(
    			'RP_invoice_items',
    			"rpinvoice",
    	);
    	//print_r($post);exit;
    	if($invoice_type_special === false && $pdfname != "SocialcodePdfs") //ISPC-2745 Carmen 17.11.2020
    	{
    		$post = Pms_CommonData::clear_pdf_data($post, $excluded_keys);
    	}
    
    	if(is_array($filename))
    	{
    		foreach($filename as $k_file => $v_file)
    		{
    			$htmlform[$k_file] = Pms_Template::createTemplate($post, 'templates/' . $v_file);
    			$html[$k_file] = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform[$k_file]);
    		}
    	}
    	else
    	{
    		$htmlform = Pms_Template::createTemplate($post, 'templates/' . $filename);
    		if($invoice_type_special === false)
    		{
    			$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
    		}
    		else
    		{
    			$html = $htmlform;
    		}
    	}
    
    
    	//dont return the pdf file to user
    	if($chk == 4)
    	{
    		$navnames = array(
    				"rpinvoice" => "SAPV-Abrechnungsbogen",
    				"RP_invoice_items" => "RP-Leistungsnachweis",
    				"SocialcodePdfs" => "SocialcodePdf" //ISPC-2745 Carmen 16.11.2020
    		);
    
    		if($pdfname == 'rpinvoice')
    		{
    			$orientation = 'P';
    			$format = "A4";
    			$bottom_margin = "5";
    		}
    		else if($pdfname == 'RP_invoice_items')
    		{
    			$orientation = 'P';
    			$format = "A4";
    		}
    		//ISPC-2745 Carmen 16.11.2020
    		if($pdfname == 'SocialcodePdfs')
    		{
    			$orientation = 'L';
    			$format = "A4";
    			$bottom_margin = "5";
    		}
    		//--
    		else
    		{
    			$orientation = 'P';
    			$format = "A4";
    		}
    
    		$pdf = new Pms_PDF($orientation, 'mm', $format, true, 'UTF-8', false);
    		$pdf->setDefaults(true, $orientation, $bottom_margin); //defaults with header
    
    		if($invoice_type_special !== false)
    		{
    			$pdf->SetFont('dejavusans', '', 10);
    			$pdf->setPrintFooter(false); //remove black line at bottom
    			$pdf->SetMargins(20, 49, 50);
    			if($invoice_type_special == "ND_patient")
    			{
    				$background_type = "24"; //ND patient
    			}
    			elseif($invoice_type_special == "ND_user")
    			{
    				$background_type = "25"; // ND user
    			}
    		}
    		else
    		{
    			$pdf->setImageScale(1.6);
    			$pdf->format = $format;
    			$pdf->SetMargins(10, 5, 10); //reset margins
    		}
    
    
    
    		switch($pdfname)
    		{
    			case 'rpinvoice':
    				$pdf->SetMargins(28, 18, 28);
    				$pdf->setPrintFooter(false); // remove black line at bottom
    				$pdf->SetFont('dejavusans', '', 10);
    				$background_type="48";
    
    				break;
    
    			case 'RP_invoice_items':
    				$pdf->SetMargins(10, 5, 10);
    
    				// ISPC-1603
    				$pdf->SetAutoPageBreak(TRUE, 35);
    				$pdf->setFooterFont(Array('helvetica', '', 7));
    				//$pdf->no_first_page_invoice_footer = true; //remove footer from the first page
    				$pdf->invoice_footer = true; // set special footer
    
    				$footer_text = '<table width="100%">
                                	<tr>
                                		<td width="45%">Ich bestätige die vertragsgemäße Ausführung<br/>der oben angegebenen Leistungen</td>
                                		<td width="10%"></td>
                                		<td width="45%">Ich bestätige die Durchführung der oben<br/>angegebenen Leistungen</td>
                                	</tr>
                                	<tr>
                                        <td colsapn="3">&nbsp;</td>
                                	</tr>
                                	<tr>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td></td>
                                		<td style="border-bottom:1px solid #000000;"></td>
                                	</tr>
                                	<tr>
                                		<td>Verantwortlicher Leistungserbinger PCT</td>
                                		<td></td>
                                		<td>Versicherter / Bezugsperson</td>
                                	</tr>
                                    <tr>
                                		<td colsapn="3">&nbsp;</td>
                                	</tr>
                                	<tr>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td style="border-bottom:1px solid #000000;"></td>
                                	</tr>
                                	<tr>
                                		<td>Dieses Dokument wurde erstellt mit ISPC &reg;.</td>
                                		<td></td>
                                		<td>&copy; smart-Q Softwaresysteme GmbH 2015</td>
                                	</tr>
                                </table>';
    
    
    				$pdf->footer_text = $footer_text; // set pdf background only for the first page
    				$pdf->setPrintFooter(true); // remove black line at bottomC
    				break;
    		}
    
    		if($background_type != false)
    		{
    			$bg_image = Pms_CommonData::getPdfBackground($clientinfo[0]['id'], $background_type);
    			if($bg_image !== false)
    			{
    				$bg_image_path = PDFBG_PATH . '/' . $clientinfo[0]['id'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
    				if(is_file($bg_image_path))
    				{
    					$pdf->setBackgroundImage($bg_image_path);
    				}
    			}
    		}
    
    		if($invoice_type_special === false)
    		{
    			$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
    		}
    
    		$excluded_css_cleanup_pdfs = array(
    				"rpinvoice",
    				"RP_invoice_items",
    				"SocialcodePdfs"
    		);
    
    		if($invoice_type_special === false)
    		{
    			if(!in_array($pdfname, $excluded_css_cleanup_pdfs))
    			{
    				$html = preg_replace('/style=\"(.*)\"/i', '', $html);
    			}
    		}
    			
    		// 				print_r($html); exit;
    		$pdf->setHTML($html);
    
    		//create public/joined_files/ dir
    		while(!is_dir(PDFJOIN_PATH))
    		{
    			mkdir(PDFJOIN_PATH);
    			if($i >= 50)
    			{
    				exit; //failsafe
    			}
    			$i++;
    		}
    
    		//create public/joined_files/$clientid dir
    		$pdf_path = PDFJOIN_PATH . '/' . $clientid;
    
    		while(!is_dir($pdf_path))
    		{
    			mkdir($pdf_path);
    			if($i >= 50)
    			{
    				exit; //failsafe
    			}
    			$i++;
    		}
    
    		$tmpstmp = isset($this->view->folder_stamp) ? $this->view->folder_stamp : time();
    
    		while(!is_dir($pdf_path . '/' . $tmpstmp))
    		{
    			mkdir($pdf_path . '/' . $tmpstmp);
    			if($i >= 50)
    			{
    				exit; //failsafe
    			}
    			$i++;
    		}
    
    		if($pdfname == 'shimplementationproof' || $pdfname == 'shanlage14' )
    		{
    			$pdfname = $pdfname.'_'.$post['epid'];
    		}
    
    		if($pdfname == 'rpinvoice' || $pdfname == 'RP_invoice_items' )
    		{
    			$pdfname = $pdfname.'_'.$post['unique_id'];
    		}
    
    		$pdf->toFile($pdf_path . '/' . $tmpstmp . '/' . $pdfname. '.pdf');
    
    		return $pdf_path . '/' . $tmpstmp . '/' . $pdfname. '.pdf';
    	}
    }
    
    
    public function join_pdfs_new($files = false, $patient_data = false, $source , $extra = false)
    {
    		
    	$logininfo = new Zend_Session_Namespace('Login_Info');
    	if($extra && !empty($extra)){
    		$clientid = $extra['clientid'];
    	} else{
    		$clientid = $logininfo->clientid;
    	}
    	 
    	$file_data = array();
    	$file_data['ipid'] = $patient_data['ipid'];
    	$file_data['file_title'] = $source;
    	
    	if($source == 'Rp_invoice_and_items')
    	{
    		// 		        $file_data['pdfname'] = $patient_data['epid'] . '_Rechnung';
    		// 		        $output_name = $patient_data['epid'].'_Rechnung.pdf';
    
    		$file_data['pdfname'] = 'RP_Rechnung';
    
    		//ISPC-2472Ancuta 07.11.2019
    		if($patient_data['invoice_full_number']){
    			$output_name = $patient_data['invoice_full_number'].'.pdf';
    		} else{
    			$output_name = 'RP_Rechnung.pdf';
    		}
    	}
    	elseif($source == 'HiInvoice' || $source == 'HiUserInvoice')
    	{
    		if($patient_data){
    			$Batch_name = "";
    			$Batch_name = $patient_data[0];
    			if(count($patient_data) > 1){
    				$Batch_name .='_'.end($patient_data);
    			}
    			$output_name = $Batch_name;
    		} else{
    			$output_name = 'Benutzer_Rechnung.pdf';
    		}
    	}
    	//ISPC-2745 Carmen 17.11.2020
    	if($source == 'Bw_sgbv_invoice')
    	{
    		// 		        $file_data['pdfname'] = $patient_data['epid'] . '_Rechnung';
    		// 		        $output_name = $patient_data['epid'].'_Rechnung.pdf';
    
    		$file_data['pdfname'] = 'BW SGBV';
    		 
    		$output_name = 'BW_SGBV_Rechnung.pdf';
    		 
    	}
    	if($source == 'Bw_sgbxi_invoice')
    	{
    		// 		        $file_data['pdfname'] = $patient_data['epid'] . '_Rechnung';
    		// 		        $output_name = $patient_data['epid'].'_Rechnung.pdf';
    
    		$file_data['pdfname'] = 'BW SGBXI';
    
    		$output_name = 'BW_SGBXI_Rechnung.pdf';
    
    	}
    	if($source == 'RP_invoice_token')
    	{
    		// 		        $file_data['pdfname'] = $patient_data['epid'] . '_Rechnung';
    		// 		        $output_name = $patient_data['epid'].'_Rechnung.pdf';
    
    		$file_data['pdfname'] = 'RP_Rechnung';
    
    		//ISPC-2472Ancuta 07.11.2019
    		if($patient_data['invoice_full_number']){
    			$output_name = $patient_data['invoice_full_number'].'.pdf';
    		} else{
    			$output_name = 'RP_Rechnung.pdf';
    		}
    	}
    	//--
    
    	 
    	if($extra['bulk_print'] == '1' && !empty($extra['batch_temp_folder']) )
    	{
    		$file_data['return_file_name'] = 1;
    		$batch_temp_folder = $extra['batch_temp_folder'];
    
    		if(!is_dir(PDFDOCX_PATH))
    		{
    			while(!is_dir(PDFDOCX_PATH))
    			{
    				mkdir(PDFDOCX_PATH);
    				if($i >= 50)
    				{
    					//exit; //failsafe
    					break;
    				}
    				$i++;
    			}
    		}
    
    		if(!is_dir(PDFDOCX_PATH . '/' . $clientid))
    		{
    			while(!is_dir(PDFDOCX_PATH . '/' . $clientid))
    			{
    				mkdir(PDFDOCX_PATH . '/' . $clientid);
    				if($i >= 50)
    				{
    					//exit; //failsafe
    					break;
    				}
    				$i++;
    			}
    		}
    
    
    
    		if(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
    		{
    			while(!is_dir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder))
    			{
    				mkdir(PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder);
    				if($i >= 50)
    				{
    					exit; //failsafe
    				}
    				$i++;
    			}
    		}
    
    		$destination_path = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/pdf_invoice_' . $extra['unique_id'].'.pdf';
    		// 		        $merged_other_filename = PDFDOCX_PATH . '/' . $clientid . '/' . $batch_temp_folder . '/pdf_invoice_' . $extra['unique_id'].'.pdf';
    		// 		        //merge all files existing in $batch_temp_files!
    		// 		        $merge = new MultiMerge();
    		// 		        $merge_process = $merge->mergePdf($files, $merged_other_filename);
    
    		// 		        dd($files);
    		// 		        $merge = new MultiMerge();
    		// 		        $merge_process = $merge->mergePdf($files,$destination_path);
    
    		// 		        return $destination_path;
    
    		if($files)
    		{
    			$pdf = new Pms_PDFMerger();
    
    			foreach($files AS $file) {
    				$pdf->addPDF($file, 'all');
    			}
    			$merged_files = $pdf->merge('browser', $destination_path, $file_data);
    
    			return $merged_files;
    		}
    
    
    	}
    	else
    	{
    
    		//create public/joined_files/ dir
    		while(!is_dir(PDFJOIN_PATH))
    		{
    			mkdir(PDFJOIN_PATH);
    			if($i >= 50)
    			{
    				exit; //failsafe
    			}
    			$i++;
    		}
    			
    		//create public/joined_files/$clientid dir
    		$pdf_path = PDFJOIN_PATH . '/' . $clientid;
    			
    		while(!is_dir($pdf_path))
    		{
    			mkdir($pdf_path);
    			if($i >= 50)
    			{
    				exit; //failsafe
    			}
    			$i++;
    		}
    			
    		$outputh_path = $pdf_path . '/' . $output_name;
    
    		if($files)
    		{
    			$pdf = new Pms_PDFMerger();
    			 
    			foreach($files AS $file) {
    				$pdf->addPDF($file, 'all');
    			}
    			$pdf->merge('download', $output_name, $file_data);
    		}
    
    	}
    
    }
    
    private function generate_pdf($post_data, $pdfname, $filename, $orientation = false, $background_pages = false)
    {
    	// 			print_r(func_get_args());
    	// 			die();
    	$pdf_names = array(
    			// "forms" - from patient menu
    			'PerformancePdf' => 'Formular SAPV Leistungsnachweis',
    			'form_performance_items_pdf' => 'Formular SAPV Leistungsnachweis',
    			'SocialcodePdf' => 'Formular SGB V Abrechnung',
    			'MedipumpsControl' => 'Formular Medikamenten Pumpen',
    			// invoices
    			'PerformancePdfs' => 'SAPV Leistungsnachweis Rechnung',
    			'PerformancePdf_invoice_items' => 'SAPV Leistungsnachweis Rechnung',
    			'SocialcodePdfs' => 'SGB V Abrechnung Rechnung',
    			'MedipumpsControlPdfs' => 'Medikamenten Pumpen Rechnung ',
    			'InvoiceJournal' => 'Rechnungsausgangsjournal',
    			//ISPC-2424
    			'InvoiceJournal_sh' => 'Rechnungsausgangsjournal SH',
    			'HeInvoice' => 'Hessen Rechnung',
    			'BayernPdf' => 'Bayern Rechnung',
    			'SGB_XI' => 'SGB XI',
    			'rpinvoice' => 'RP - Abrechnung',
    			'rpperformancerecord' => 'RP - Leistungsübersicht',
    			'BreHospizSapvPerformanceInvoice' => 'BRE Hospiz Rechnung'
    	);
    
    	if($pdfname == 'HeInvoice' || $pdfname == 'PerformancePdfs'  || $pdfname == 'PerformancePdf_invoice_items' || $pdfname == 'BayernPdf' ||    $pdfname == 'SocialcodePdfs' || $pdfname == 'SGB_XI' || $pdfname == 'MedipumpsControlPdfs' )
    	{
    
    		if(strlen($post_data['address']) > 0)
    		{
    			if(strpos($post_data['address'],"style"))
    			{
    				$post_data['address'] = preg_replace('/style=\"(.*)\"/i', '', $post_data['address']);
    			}
    
    			$post_data['address'] = str_replace(array("<p>","<p >"), "", $post_data['address']);
    			$post_data['address'] = str_replace("</p>", "", $post_data['address']);
    			$post_data['address'] = str_replace("\n", "<br/>", $post_data['address']);
    		}
    	}
    	if( $pdfname == 'PerformancePdfs' || $pdfname == 'PerformancePdf_invoice_items' || $pdfname == 'SocialcodePdfs'  || $pdfname == 'SGB_XI'  || $pdfname == 'MedipumpsControlPdfs' )
    	{
    
    		if(strlen($post_data['hi_subdiv_address']) > 0)
    		{
    			if(strpos($post_data['hi_subdiv_address'],"style"))
    			{
    				$post_data['hi_subdiv_address'] = preg_replace('/style=\"(.*)\"/i', '', $post_data['hi_subdiv_address']);
    			}
    
    			$post_data['hi_subdiv_address'] = str_replace(array("<p>","<p >"), "", $post_data['hi_subdiv_address']);
    			$post_data['hi_subdiv_address'] = str_replace("</p>", "", $post_data['hi_subdiv_address']);
    			$post_data['hi_subdiv_address'] = str_replace("\n", "<br/>", $post_data['hi_subdiv_address']);
    		}
    
    		if(strlen($post_data['patient_address']) > 0)
    		{
    			if(strpos($post_data['patient_address'],"style"))
    			{
    				$post_data['patient_address'] = preg_replace('/style=\"(.*)\"/i', '', $post_data['patient_address']);
    			}
    
    			$post_data['patient_address'] = str_replace(array("<p>","<p >"), "", $post_data['patient_address']);
    			$post_data['patient_address'] = str_replace("</p>", "", $post_data['patient_address']);
    			$post_data['patient_address'] = str_replace("\n", "<br/>", $post_data['patient_address']);
    		}
    	}
    		
    
    	if(is_array($filename))
    	{
    		foreach($filename as $k_file => $v_file)
    		{
    			$htmlform[$k_file] = Pms_Template::createTemplate($post_data, 'templates/' . $v_file);
    			$html[$k_file] = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform[$k_file]);
    		}
    	}
    	else
    	{
    		$htmlform = Pms_Template::createTemplate($post_data, 'templates/' . $filename);
    		$html = str_replace('../../images', OLD_RES_FILE_PATH . '/images', $htmlform);
    	}
    
    	if($pdfname == 'PerformancePdfs')
    	{
    		$pdf_type = '19';
    	}
    		
    	elseif($pdfname == 'PerformancePdf_invoice_items')
    	{
    		$pdf_type = '19';
    	}
    	else if($pdfname == 'SocialcodePdfs')
    	{
    		$pdf_type = '22';
    	}
    	else if($pdfname == 'MedipumpsControlPdfs')
    	{
    		$pdf_type = '35';
    	}
    	else if($pdfname == 'HeInvoice')
    	{
    		$pdf_type = '36';
    	}
    	else if($pdfname == 'BayernPdf')
    	{
    		$pdf_type = '37';
    	}
    	else if($pdfname == 'SGB_XI')
    	{
    		$pdf_type = '38';
    	}
    	else if($pdfname == 'rpinvoice')
    	{
    		$pdf_type = '48';
    	}
    	else if($pdfname == 'BreHospizSapvPerformanceInvoice')
    	{
    		$pdf_type = '52';
    	}
    		
    	else if($pdfname == 'InvoiceJournal_sh')
    	{
    		$pdf_type = '73';
    	}
    		
    
    
    	$pdf = new Pms_PDF('L', 'mm', 'A4', true, 'UTF-8', false);
    	$pdf->setDefaults(true); //defaults with header
    	// 			$pdf->setDefaults(true,$orientation,$bottom_margin); //defaults with header
    	$pdf->setImageScale(1.6);
    	if($pdfname != 'PerformancePdf_invoice_items' && $pdfname != 'form_performance_items_pdf' && $pdfname != 'RP_invoice_items'){
    		$pdf->SetFont('dejavusans', '', 10);
    	}
    	if($pdfname == 'PerformancePdf_invoice_items'){
    		$pdf->SetFont('', '', 10);
    	}
    	if($pdfname == 'form_performance_items_pdf'){
    		$pdf->SetFont('', '', 10);
    	}
    
    	$pdf->SetMargins(6, 5, 10); //reset margins
    	$pdf->setPrintFooter(false); // remove black line at bottom
    	$pdf->SetAutoPageBreak(TRUE, 10);
    
    	if($pdfname == 'HeInvoice')
    	{
    		$pdf->firstpagebackground = true; // set pdf background only for the first page
    		$pdf->SetMargins(6, 20, 10); //reset margins
    		$pdf->SetAutoPageBreak(TRUE, 30);
    	}
    	else if($pdfname == 'HeInvoice')
    	{
    		$pdf->firstpagebackground = true; // set pdf background only for the first page
    	}
    	else if($pdfname == 'rpinvoice')
    	{
    		//				$pdf->firstpagebackground = true;
    		$pdf->SetMargins(28, 18, 28);
    		//				$pdf->SetAutoPageBreak(TRUE, 30);
    	}
    	else if($pdfname == 'RP_invoice_items')
    	{
    		//				$pdf->firstpagebackground = true;
    		// 				$pdf->SetMargins(28, 18, 28);
    		$pdf->SetMargins(10, 5, 10);
    		//				$pdf->SetAutoPageBreak(TRUE, 30);
    	}
    	else if($pdfname == 'PerformancePdf_invoice_items')
    	{
    		$pdf->SetMargins(10, 5, 10);
    	}
    	else if($pdfname == 'form_performance_items_pdf')
    	{
    		$pdf->SetMargins(10, 5, 10);
    	}
    	elseif( $pdfname == 'rpperformancerecord')
    	{
    		$pdf->SetAutoPageBreak(TRUE, 1);
    	}
    
    	if($pdfname == 'InvoiceJournal')
    	{
    		if(strlen($post_data['start_date']) == 0)
    		{
    			//get current month default
    			$post_data['start_date'] = date('Y-m-', time()) . '01';
    			$post_data['end_date'] = date('Y-m-', time()) . date('t', time());
    		}
    		$pdf->SetMargins(10, 40, 10);
    		$pdf->SetHeaderMargin(5);
    
    		$header_text = '<table width="890" border="0">
                        		<tr>
                        			<td style="font-size: 14;" colspan="6"><strong>Rechnungsausgangsjournal</strong></td>
                        			<td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2">' . $post_data['clientinfo']['client_name'] . '</td>
                        		</tr>
                        		<tr>
                        			<td style="font-size: 9;" colspan="2">Auswertung nach:</td>
                        			<td style="font-size: 9;font-weight: bold;text-align: left;" colspan="2">Rechnungsdatum</td>
                        			<td></td>
                        			<td></td>
                        			<td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2"></td>
                        		</tr>
                        		<tr>
                        			<td style="font-size: 9;" colspan="2">Sortiert nach:</td>
                        			<td style="font-size: 9;font-weight: bold;" colspan="2">' . $this->view->translate($post_data['j_sortby']) . '</td>
                        			<td></td>
                        			<td></td>
                        			<td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2">' . $post_data['clientinfo']['street1'] . $post_data['clientinfo']['street2'] . '</td>
                        		</tr>
                        		<tr>
                        			<td style="font-size: 9;" colspan="2">Zeitraum:</td>
                        			<td style="font-size: 9;font-weight: bold;" colspan="2">' . date('d.m.Y', strtotime($post_data['start_date'])) . ' - ' . date('d.m.Y', strtotime($post_data['end_date'])) . '</td>
                        			<td></td>
                        			<td></td>
                        			<td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2">' . $post_data['clientinfo']['city'] . '</td>
                        		</tr>
                        		<tr>
                        			<td style="font-size: 9;" colspan="2">Druckdatum:</td>
                        			<td style="font-size: 9;font-weight: bold;" colspan="2">' . date('d.m.Y H:i', time()) . '</td>
                        			<td></td>
                        			<td></td>
                        			<td style="font-size: 9;" colspan="2"></td>
                        		</tr>
                        		<tr>
                        			<td style="font-size: 9;" colspan="2">Seite:</td>
                        			<td style="font-size: 9;font-weight: bold;text-align: left;" colspan="2">' . $pdf->getAliasNumPage() . '</td>
                        			<td></td>
                        			<td></td>
                        			<td style="font-size: 9;font-weight: bold;text-align: right;" colspan="2">IK: ' . $post_data['clientinfo']['institutskennzeichen'] . '</td>
                        		</tr>
    
                        </table>';
    
    		// 				$pdf->setHeaderFont(Array('arial', '', 11));
    		$pdf->setHeaderFont(Array('dejavusans', '', 11));
    		$pdf->HeaderText = $header_text;
    	}
    
    	//ISPC-2424
    	if($pdfname == 'InvoiceJournal_sh')
    	{
    		if(strlen($post_data['start_date']) == 0)
    		{
    			//get current month default
    			$post_data['start_date'] = date('Y-m-', time()) . '01';
    			$post_data['end_date'] = date('Y-m-', time()) . date('t', time());
    		}
    		$pdf->SetMargins(10, 45, 10);
    		$pdf->SetHeaderMargin(17);
    		 
    		$header_text = '<table width="960" >
	                            <tr>
                        			<td style="font-size: 18;font-weight: bold; color: #6d6d6d; " colspan="3">FIBU Protokoll</td>
                        			<td style="font-size: 12;" colspan="4">' . date('d.m.Y', strtotime($post_data['start_date'])) . ' - ' . date('d.m.Y', strtotime($post_data['end_date'])) . '</td>
                        			<td style="width:180;color: #f2f2f2;font-weight: bold;text-align: right;"  >
                                        <font size="12">' . $pdf->getAliasNumPage() . '</font><br/>
                                        <font size="9">von ' . $pdf->getAliasNbPages() . '</font>
                                    </td>
                                    <td></td>
                        		</tr>
    
             
                        		<tr>
                                    <td   colspan="9" > </td>
                                </tr>
                        		<tr>
                        			<td style="font-size: 9;font-weight: bold;" colspan="4">Team ' . $post_data['clientinfo']['team_name'] . '</td>
                        			<td></td>
                        			<td></td>
                                    <td   colspan="3" > </td>
                                </tr>
    
                        		<tr>
                        			<td style="font-size: 9;font-weight: bold;  color: #6c6c6c;" colspan="4">Fibu-Datei vom ' . date('d.m.Y H:i', time()) . '</td>
                        		</tr>
               
                        </table>';
    		// 			    echo $header_text; exit;
    		// 				$pdf->setHeaderFont(Array('arial', '', 11));
    		$pdf->setHeaderFont(Array('dejavusans', '', 11));
    		$pdf->HeaderText = $header_text;
    	}
    		
    	if($pdfname == 'PerformancePdf_invoice_items')
    	{
    		$pdf->SetAutoPageBreak(TRUE, 35);
    		 
    		$pdf->setFooterFont(Array('helvetica', '', 7));
    
    		$pdf->no_first_page_invoice_footer = true; //remove footer from the first page
    
    		$pdf->invoice_footer = true; // set special footer
    		$footer_text = '<table width="100%">
                                	<tr>
                                		<td width="45%">Ich bestätige die vertragsgemäße Ausführung<br/>der oben angegebenen Leistungen</td>
                                		<td width="10%"></td>
                                		<td width="45%">Ich bestätige die Durchführung der oben<br/>angegebenen Leistungen</td>
                                	</tr>
                                	<tr>
                                        <td colsapn="3">&nbsp;</td>
                                	</tr>
                                	<tr>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td></td>
                                		<td style="border-bottom:1px solid #000000;"></td>
                                	</tr>
                                	<tr>
                                		<td>Verantwortlicher Leistungserbinger PCT</td>
                                		<td></td>
                                		<td>Versicherter / Bezugsperson</td>
                                	</tr>
                                    <tr>
                                		<td colsapn="3">&nbsp;</td>
                                	</tr>
                                	<tr>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td style="border-bottom:1px solid #000000;"></td>
                                	</tr>
                                	<tr>
                                		<td>Dieses Dokument wurde erstellt mit ISPC &reg;.</td>
                                		<td></td>
                                		<td>&copy; smart-Q Softwaresysteme GmbH 2015</td>
                                	</tr>
                                </table>';
    
    
    		$pdf->footer_text = $footer_text; // set pdf background only for the first page
    		$pdf->setPrintFooter(true); // remove black line at bottomC
    	}
    	if($pdfname == 'form_performance_items_pdf')
    	{
    		 
    		$pdf->setFooterFont(Array('helvetica', '', 7));
    		// 			   $pdf->firstpagebackground = false; // set pdf background only for the first page
    		$pdf->invoice_footer = true; // set special footer
    		$footer_text = '<table width="100%">
                                	<tr>
                                		<td width="45%">Ich bestätige die vertragsgemäße Ausführung<br/>der oben angegebenen Leistungen</td>
                                		<td width="10%"></td>
                                		<td width="45%">Ich bestätige die Durchführung der oben<br/>angegebenen Leistungen</td>
                                	</tr>
                                	<tr>
                                        <td colsapn="3">&nbsp;</td>
                                	</tr>
                                	<tr>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td></td>
                                		<td style="border-bottom:1px solid #000000;"></td>
                                	</tr>
                                	<tr>
                                		<td>Verantwortlicher Leistungserbinger PCT</td>
                                		<td></td>
                                		<td>Versicherter / Bezugsperson</td>
                                	</tr>
                                    <tr>
                                		<td colsapn="3">&nbsp;</td>
                                	</tr>
                                	<tr>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td style="border-bottom:1px solid #000000;"></td>
                                	</tr>
                                	<tr>
                                		<td>Dieses Dokument wurde erstellt mit ISPC &reg;.</td>
                                		<td></td>
                                		<td>&copy; smart-Q Softwaresysteme GmbH 2015</td>
                                	</tr>
                                </table>';
    
    
    		$pdf->footer_text = $footer_text; // set pdf background only for the first page
    		$pdf->setPrintFooter(true); // remove black line at bottomC
    	}
    	if($pdfname == 'PerformancePdf') //ISPC-2187
    	{
    		$pdf->setPrintFooter(true);
    		//$pdf->SetMargins(10, 5, 10); //reset margins > ace3stea sunt necesare? le-am lasat pt ca dadea exemplu rooster si am zis sa fie poz la fel
    		$pdf->footer_text = $this->view->translate("This certificate of achievement was printed on"). " " . date("d.m.Y");
    		$pdf->setFooterType('1 of n date');
    	}
    		
    	if($pdfname == 'PerformancePdfs')//ISPC-2187
    	{
    		$pdf->setPrintFooter(true);
    		//$pdf->SetMargins(10, 5, 10); //reset margins
    		$pdf->footer_text = $this->view->translate("This certificate of achievement was printed on"). " " . date("d.m.Y");
    		$pdf->setFooterType('1 of n date');
    	}
    		
    	// ISPC-1603
    	if($pdfname == 'RP_invoice_items')
    	{
    		$pdf->SetAutoPageBreak(TRUE, 35);
    		 
    		$pdf->setFooterFont(Array('helvetica', '', 7));
    
    		$pdf->no_first_page_invoice_footer = true; //remove footer from the first page
    
    		$pdf->invoice_footer = true; // set special footer
    		$footer_text = '<table width="100%">
                                	<tr>
                                		<td width="45%">Ich bestätige die vertragsgemäße Ausführung<br/>der oben angegebenen Leistungen</td>
                                		<td width="10%"></td>
                                		<td width="45%">Ich bestätige die Durchführung der oben<br/>angegebenen Leistungen</td>
                                	</tr>
                                	<tr>
                                        <td colsapn="3">&nbsp;</td>
                                	</tr>
                                	<tr>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td></td>
                                		<td style="border-bottom:1px solid #000000;"></td>
                                	</tr>
                                	<tr>
                                		<td>Verantwortlicher Leistungserbinger PCT</td>
                                		<td></td>
                                		<td>Versicherter / Bezugsperson</td>
                                	</tr>
                                    <tr>
                                		<td colsapn="3">&nbsp;</td>
                                	</tr>
                                	<tr>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td style="border-bottom:1px solid #000000;">&nbsp;</td>
                                		<td style="border-bottom:1px solid #000000;"></td>
                                	</tr>
                                	<tr>
                                		<td>Dieses Dokument wurde erstellt mit ISPC &reg;.</td>
                                		<td></td>
                                		<td>&copy; smart-Q Softwaresysteme GmbH 2015</td>
                                	</tr>
                                </table>';
    
    
    		$pdf->footer_text = $footer_text; // set pdf background only for the first page
    		$pdf->setPrintFooter(true); // remove black line at bottomC
    	}
    		
    
    	//set page background for a defined page key in $background_pages array
    	$bg_image = Pms_CommonData::getPdfBackground($post_data['clientid'], $pdf_type);
    	if($bg_image !== false)
    	{
    		$bg_image_path = PDFBG_PATH . '/' . $post_data['clientid'] . '/' . $bg_image['id'] . '_' . $bg_image['filename'];
    		if(is_file($bg_image_path))
    		{
    			$pdf->setBackgroundImage($bg_image_path);
    		}
    	}
    
    	//			print_r($html);exit;
    
    	if(is_array($html))
    	{
    		foreach($html as $k_html => $v_html)
    		{
    			if(is_array($orientation))
    			{
    				if(is_array($background_pages))
    				{
    					if(!in_array($k_html, $background_pages))
    					{
    						//unset page background for a nondefined page key in $background_pages array
    						$pdf->setBackgroundImage();
    					}
    				}
    				//each page has it`s own orientation
    				$pdf->setHTML($v_html, $orientation[$k_html]);
    			}
    			else
    			{
    				//all pages one custom orientation
    				$pdf->setHTML($v_html, $orientation);
    			}
    		}
    	}
    	else
    	{
    		if(empty($background_pages) && is_file($bg_image_path))
    		{
    			$pdf->setBackgroundImage($bg_image_path);
    		}
    
    		$pdf->setHTML($html, $orientation);
    	}
    
    		
    
    	//$tmpstmp = substr(md5(time() . rand(0, 999)), 0, 12);
    	//mkdir('uploads/' . $tmpstmp);
    	$tmpstmp = $pdf->uniqfolder(PDF_PATH);
    
    	if($post_data['bulk_print'] == 1){
    
    		$invoice_number_full="";
    		$invoice_number_full .=  (strlen($post_data['prefix']) > 0) ? $post_data['prefix'] : '';
    		$invoice_number_full .= $post_data['invoice_number'];
    		 
    		$pdfname_inv = $pdfname;
    		if(strlen($invoice_number_full) > 0 ){
    			$pdfname_inv = $pdfname.'_'.$invoice_number_full;
    		}
    		// 			    $source_path = PDF_PATH . '/' . $tmpstmp . '/' . $pdfname_inv . '.pdf';
    
    		// 			    $pdf->toFile($source_path);
    		 
    		 
    		$batch_temp_folder = $post_data['batch_temp_folder'];
    
    		if(!is_dir(PDFDOCX_PATH))
    		{
    			while(!is_dir(PDFDOCX_PATH))
    			{
    				mkdir(PDFDOCX_PATH);
    				if($i >= 50)
    				{
    					//exit; //failsafe
    					break;
    				}
    				$i++;
    			}
    		}
    		 
    		if(!is_dir(PDFDOCX_PATH . '/' . $post_data['clientid']))
    		{
    			while(!is_dir(PDFDOCX_PATH . '/' . $post_data['clientid']))
    			{
    				mkdir(PDFDOCX_PATH . '/' . $post_data['clientid']);
    				if($i >= 50)
    				{
    					//exit; //failsafe
    					break;
    				}
    				$i++;
    			}
    		}
    		 
    		 
    		 
    		if(!is_dir(PDFDOCX_PATH . '/' . $post_data['clientid'] . '/' . $batch_temp_folder))
    		{
    			while(!is_dir(PDFDOCX_PATH . '/' . $post_data['clientid'] . '/' . $batch_temp_folder))
    			{
    				mkdir(PDFDOCX_PATH . '/' . $post_data['clientid'] . '/' . $batch_temp_folder);
    				if($i >= 50)
    				{
    					exit; //failsafe
    				}
    				$i++;
    			}
    		}
    		 
    		 
    		$destination_path = PDFDOCX_PATH . '/' . $post_data['clientid'] . '/' . $batch_temp_folder . '/pdf_invoice_' . $post_data['unique_id'].'.pdf';
    		 
    		$pdf->toFile($destination_path);
    		 
    		// 			    $destination_path = '/home/www/ispc20172/public/pdfdocx_files/70/338_bw_sapv_invoice_3/pdf_invoice_6295.pdf';
    		// 			    copy($source_path, $destination_path);
    		return $destination_path;
    		 
    	} else {
    		 
    		$pdf->toFile(PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf');
    	}
    		
    		
    		
    		
    		
    		
    	$_SESSION['filename'] = $tmpstmp . '/' . $pdfname . '.pdf';
    	// 			$cmd = "zip -9 -r -P " . $logininfo->filepass . " uploads/" . $tmpstmp . ".zip uploads/" . $tmpstmp . "; rm -r uploads/" . $tmpstmp;
    	// 			exec($cmd);
    	$zipname = $tmpstmp . ".zip";
    	$filename = "uploads/" . $tmpstmp . ".zip";
    	/*
    	 $con_id = Pms_FtpFileupload::ftpconnect();
    	 if($con_id)
    	 {
    	 $upload = Pms_FtpFileupload::fileupload($con_id, $filename, 'uploads/' . $zipname);
    	 Pms_FtpFileupload::ftpconclose($con_id);
    	 }
    	 */
    
    		
    	if($pdfname == 'PerformancePdf' || $pdfname == 'PerformancePdfs'  || $pdfname == 'PerformancePdf_invoice_items'   || $pdfname == 'form_performance_items_pdf' )
    	{
    		$tabname = 'sapvinvoice';
    	}
    	else if($pdfname == 'SocialcodePdf' || $pdfname == 'SocialcodePdfs')
    	{
    		$tabname = 'sgbvinvoice';
    	}
    	else if($pdfname == 'InvoiceJournal')
    	{
    		$tabname = 'invoicejournal';
    	}
    	else if($pdfname == 'HeInvoice')
    	{
    		$tabname = 'heinvoice';
    	}
    	else if($pdfname == 'BayernPdf')
    	{
    		$tabname = 'bayerninvoice';
    	}
    
    	if(
    			$pdfname == 'PerformancePdf' ||
    			// 				$pdfname == 'form_performance_items_pdf' ||
    			$pdfname == 'SocialcodePdf' ||
    			$pdfname == 'MedipumpsControl')
    	{
    		$patient_file = '1';
    	}
    	else
    	{
    		$patient_file = '0';
    	}
    
    	if($patient_file == "1")
    	{// No invoice should go to patient files
    		$cust = new PatientFileUpload();
    		$cust->title = Pms_CommonData::aesEncrypt(addslashes($pdf_names[$pdfname]));
    		$cust->ipid = $post_data['ipid'];
    		$cust->file_name = Pms_CommonData::aesEncrypt($_SESSION ['filename']); //$post['fileinfo']['filename']['name'];
    		$cust->file_type = Pms_CommonData::aesEncrypt('PDF');
    		$cust->system_generated = "1";
    		$cust->tabname = $tabname;
    		$cust->save();
    		$file_id = $cust->id;
    
    		if(
    				$pdfname == "PerformancePdf" ||
    				$pdfname == "SocialcodePdf"
    				// 				    ||  $pdfname == "form_performance_items_pdf"
    				)
    		{
    			//insert system file tags
    			$insert_tag = Application_Form_PatientFile2tags::insert_file_tags($file_id, array('6'));
    		}
    
    		//upload to ftp
    		$ftp_put_queue_result = Pms_CommonData :: ftp_put_queue (PDF_PATH . '/' . $tmpstmp . '/' . $pdfname . '.pdf' , "uploads" );
    
    	}
    
    	ob_end_clean();
    	ob_start();
    
    	// ISPC-2472 @Ancuta 07.11.2019
    	// THIS AFFECTS ALL INVOICES THAT USE THIS FUNCTION
    	$invoice_number_full="";
    	$invoice_number_full .=  (strlen($post_data['prefix']) > 0) ? $post_data['prefix'] : '';
    	$invoice_number_full .= $post_data['invoice_number'];
    		
    	if(strlen($invoice_number_full) > 0 ){
    		$pdfname = $invoice_number_full;
    	}
    	// --
    	 
    	$pdf->toBrowser($pdfname . '.pdf', 'D');
    	exit;
    }
    
    /**
     * Function changed to also consider the health insurance type
     * Changed by Ancuta on 07.03.2019
     */
    private function calculate_sapv_be_fall($ipid, $patient_admissions, $pricelist, $health_insurance_type = false)
    {
    
    	 
    	//@todo on office for test TP10064
    	$pm = new PatientMaster();
    	$sapv = new SapvVerordnung();
    
    	$sapv_verordnet_days = $sapv->get_patients_valid_sapv(array($ipid));
  
    	//get patient all sapv
    	foreach($patient_admissions['dischargeDates'] as $k_full_fall => $v_full_fall)
    	{
    		$fall_start = date('Y-m-d', strtotime($patient_admissions['admissionDates'][$k_full_fall]['date']));
    		$fall_end = date('Y-m-d', strtotime($v_full_fall['date']));
    
    		$period_days[$k_full_fall] = $pm->getDaysInBetween($fall_start, $fall_end);
    	}
    
    
    	foreach($period_days as $k_fall => $v_days_arr)
    	{
    		$invoiced_falls[$k_fall] = '0'; //default not invoiced
    		$skip_fall = false;
    		foreach($v_days_arr as $k_day => $v_day)
    		{
    			if(in_array($v_day, $sapv_verordnet_days[1]) && $skip_fall === false)
    			{
    				$invoiced_falls[$k_fall] = '1';
    			}
    
    			//reset fall to 0 if day exists in vv(2, 3, 4)
    			//added skip fall var
    			if(in_array($v_day, $sapv_verordnet_days[2]) || in_array($v_day, $sapv_verordnet_days[3]) || in_array($v_day, $sapv_verordnet_days[4]))
    			{
    				$skip_fall = true;
    				$invoiced_falls[$k_fall] = '0';
    			}
    		}
    	}
    

    	$first_fall = true;
    	foreach($invoiced_falls as $k_fall => $v_allow_invoice)
    	{
    		if($v_allow_invoice == '1' && !empty($health_insurance_type))
    		{
    
    			if($first_fall)
    			{
    				$shortcut = 'pb1';
    				$first_fall = false;
    			}
    			else
    			{
    				$shortcut = 'pb2';
    			}
    
    			$master_data[$shortcut]['from_date'][] = $period_days[$k_fall][0];
    			$master_data[$shortcut]['till_date'][] = end($period_days[$k_fall]);
    			$master_data[$shortcut]['shortcut'] = $shortcut;
    			$master_data[$shortcut]['qty'] += 1;
    			$master_data[$shortcut]['price'] = $pricelist[$health_insurance_type][$shortcut]['price'];
    			$master_data[$shortcut]['total'] += $pricelist[$health_insurance_type][$shortcut]['price'];
    			$master_data[$shortcut]['price_details'] = $pricelist[$health_insurance_type][$shortcut];
    			$master_data[$shortcut]['fall'] = $k_fall;
    			$master_data[$shortcut]['custom'] = '0';
    		}
    	}
    	return $master_data;
    }
    
    private function array_sort($array, $on = NULL, $order = SORT_ASC)
    {
    	$new_array = array();
    	$sortable_array = array();
    
    	if(count($array) > 0)
    	{
    		foreach($array as $k => $v)
    		{
    			if(is_array($v))
    			{
    				foreach($v as $k2 => $v2)
    				{
    					if($k2 == $on)
    					{
    						if($on == 'date' || $on == 'discharge_date' || $on == 'from_date' || $on == 'from' || $on == 'start_date_filter')
    						{
    							$sortable_array[$k] = strtotime($v2);
    						}
    						else
    						{
    							$sortable_array[$k] = ucfirst($v2);
    						}
    					}
    				}
    			}
    			else
    			{
    				if($on == 'date' || $on == 'from_date' || $on == 'from' || $on == 'start_date_filter')
    				{
    					$sortable_array[$k] = strtotime($v);
    				}
    				else
    				{
    					$sortable_array[$k] = ucfirst($v);
    				}
    			}
    		}
    
    		switch($order)
    		{
    			case 'SORT_ASC':
    				//						asort($sortable_array);
    				$sortable_array = Pms_CommonData::a_sort($sortable_array);
    				break;
    			case 'SORT_DESC':
    				//						arsort($sortable_array);
    				$sortable_array = Pms_CommonData::ar_sort($sortable_array);
    				break;
    		}
    
    		foreach($sortable_array as $k => $v)
    		{
    			$new_array[$k] = $array[$k];
    		}
    	}
    
    	return $new_array;
    }
    
    private function vdek_method($ipid, $admission_details, $no_hospital_cycle_days, $hospiz_days, $pricelist, $prev_inv_items_periods)
    {
    	if($_REQUEST['dbgqz'])
    	{
    		print_r("A\n");
    		print_r($admission_details);
    
    		print_r("B\n");
    		print_r($no_hospital_cycle_days);
    
    		print_r("C\n");
    		print_r($hospiz_days);
    		exit;
    	}
    
    	//		L.E. changed to calculate by following rule Flatrate + (each day price)
    	//determine admission type hospiz/normal
    	$shortcut_flatrate = '';
    	if($admission_details['type'] == 'h')
    	{
    		$shortcut_flatrate = 'ph1';
    	}
    	else if($admission_details['type'] == 'n')
    	{
    		$shortcut_flatrate = 'pv1';
    	}
    
    	//extract flatrate days from total number of days
    	$patient_period_days = array_diff($no_hospital_cycle_days, $admission_details['flatrate_days']); //105 - 10 = 95 days
    	$patient_period_days = array_values(array_unique($patient_period_days));
    
    	if($_REQUEST['dbgqz'])
    	{
    		print_r("D\n");
    		print_r($patient_period_days);
    		exit;
    	}
    		
    	//exclude flatrate_days from total number of days
    	$hospiz_days = array_diff($hospiz_days, $admission_details['flatrate_days']);
    	$hospiz_days = array_values(array_unique($hospiz_days));
    
    	if(count($patient_period_days) > '0')
    	{
    		$incr = 0;
    		//loop through patient days no flatrate included
    		foreach($patient_period_days as $k_period_day => $v_period_day)
    		{
    			//				determine day number (from 10+)
    			$curent_day_no = (11 + $k_period_day);
    
    
    			//determine curent day condition
    			if(in_array($v_period_day, $hospiz_days))
    			{
    				$curent_day_type = 'h';
    			}
    			else
    			{
    				$curent_day_type = 'n';
    			}
    
    			//determine curent condition shortcut
    			if($curent_day_no >= '11' && $curent_day_no <= '56')
    			{
    				$condition_shortcut['n'] = 'pv2';
    			}
    			else if($curent_day_no >= '57')
    			{
    				$condition_shortcut['n'] = 'pv3';
    			}
    
    			//hospiz
    			if($curent_day_no >= '11')
    			{
    				$condition_shortcut['h'] = 'ph2';
    			}
    
    
    
    			//determine curent day condition
    			if(in_array($patient_period_days[$k_period_day + 1], $hospiz_days))
    			{
    				$next_day_type = 'h';
    			}
    			else
    			{
    				$next_day_type = 'n';
    			}
    
    
    			if($condition_shortcut[$curent_day_type])
    			{
    				//array each day with coresponding price
    				$invoice_details[$condition_shortcut[$curent_day_type]][$v_period_day] = $pricelist[$condition_shortcut[$curent_day_type]]['price'];
    
    				// get date range
    				$invoice_date_range[$condition_shortcut[$curent_day_type]][$incr][] = $v_period_day;
    				if($next_day_type != $curent_day_type)
    				{
    					$incr++;
    				}
    
    				if(strtotime($patient_period_days[$k_period_day + 1]) != strtotime("+1 day", strtotime($v_period_day)))
    				{
    					$incr++;
    				}
    			}
    		}
    	}
    	if($_REQUEST['gbs'] == 1)
    	{
    		print_R($invoice_date_range);
    	}
    
    	//construct items array
    	if(strlen($shortcut_flatrate) > '0')
    	{
    		//				OLD
    		//				$invoice_items[$shortcut_flatrate]['from_date'][] = $admission_details['flatrate_days'][0];
    		//				$invoice_items[$shortcut_flatrate]['till_date'][] = end($admission_details['flatrate_days']);
    		//NEW
    		$k_period = '0';
    		foreach($admission_details['flatrate_days'] as $k_adm_fl_day => $v_adm_fl_day)
    		{
    			$tmp_period[$k_period][] = $v_adm_fl_day;
    
    			$next_expected_day = date('Y-m-d', strtotime('+1 day', strtotime($v_adm_fl_day)));
    			if($admission_details['flatrate_days'][($k_adm_fl_day + 1)] != $next_expected_day)
    			{
    				$invoice_items[$shortcut_flatrate]['from_date'][$k_period] = $tmp_period[$k_period]['0'];
    				$invoice_items[$shortcut_flatrate]['till_date'][$k_period] = end($tmp_period[$k_period]);
    
    				if(!in_array($tmp_period[$k_period]['0'] . ' 00:00:00', $prev_inv_items_periods[$shortcut_flatrate]['from_date']) && !empty($prev_inv_items_periods[$shortcut_flatrate]['from_date']))
    				{
    					$invoice_items[$shortcut_flatrate]['paid_periods'][$k_period] = '1';
    				}
    				else
    				{
    					$invoice_items[$shortcut_flatrate]['paid_periods'][$k_period] = '0';
    				}
    				$k_period++;
    			}
    		}
    
    
    		$invoice_items[$shortcut_flatrate]['qty'] = '1';
    		$invoice_items[$shortcut_flatrate]['total'] = $pricelist[$shortcut_flatrate]['price'];
    		$invoice_items[$shortcut_flatrate]['shortcut'] = $shortcut_flatrate;
    		$invoice_items[$shortcut_flatrate]['price_details'] = $pricelist[$shortcut_flatrate];
    		$invoice_items[$shortcut_flatrate]['price'] = $pricelist[$shortcut_flatrate]['price'];
    		$invoice_items[$shortcut_flatrate]['custom'] = '0';
    	}
    
    	
    	foreach($invoice_details as $k_inv_det => $v_inv_det)
    	{
    		$invoice_items[$k_inv_det]['qty'] = count($v_inv_det);
    		$invoice_items[$k_inv_det]['shortcut'] = $k_inv_det;
    		$invoice_items[$k_inv_det]['price_details'] = $pricelist[$k_inv_det];
    		$invoice_items[$k_inv_det]['price'] = $pricelist[$k_inv_det]['price'];
    		
    		//TODO-3706 Ancuta - 06.01.2021 - changed the way the total is calculated
//     		$invoice_items[$k_inv_det]['total'] = array_sum($v_inv_det);
    		foreach($v_inv_det as $date=>$value){
    		    $invoice_items[$k_inv_det]['total_other'] += $value;
    		}
    		$invoice_items[$k_inv_det]['total'] = str_replace(",",".",round(str_replace(",",".",$invoice_items[$k_inv_det]['total_other']),4));
    		// -- 
    		$invoice_items[$k_inv_det]['custom'] = '0';
    	}
    
    
    	foreach($invoice_date_range as $k_shortcut => $sh_interval_details)
    	{
    		foreach($sh_interval_details as $knr => $sh_interval_values)
    		{
    			$invoice_items[$k_shortcut]['from_date'][] = $sh_interval_values[0];
    			$invoice_items[$k_shortcut]['till_date'][] = end($sh_interval_values);
    		}
    	}
    	if($_REQUEST['gbs'] == 1)
    	{
    		print_R($invoice_items);
    		exit;
    		// 			print_R($invoice_details); exit;
    	}
    	return $invoice_items;
    }
    
    private function privat_method($ipid, $admission_details, $no_hospital_cycle_days, $hospiz_days, $pricelist, $prev_inv_items_periods)
    {
    
    	//		L.E. changed to calculate by following rule Flatrate + (each day price)
    	//determine admission type hospiz/normal
    	$shortcut_flatrate = '';
    	if($admission_details['type'] == 'h')
    	{
    		$shortcut_flatrate = 'ph1pp';
    	}
    	else if($admission_details['type'] == 'n')
    	{
    		$shortcut_flatrate = 'pv1pp';
    	}
    
    	//extract flatrate days from total number of days
    	$patient_period_days = array_diff($no_hospital_cycle_days, $admission_details['flatrate_days']); //105 - 10 = 95 days
    	$patient_period_days = array_values(array_unique($patient_period_days));
    
    	//exclude flatrate_days from total number of days
    	$hospiz_days = array_diff($hospiz_days, $admission_details['flatrate_days']);
    	$hospiz_days = array_values(array_unique($hospiz_days));
    
    	if(count($patient_period_days) > '0')
    	{
    
    		$incr = 0;
    		//loop through patient days no flatrate included
    		foreach($patient_period_days as $k_period_day => $v_period_day)
    		{
    			//				determine day number (from 10+)
    			$curent_day_no = (11 + $k_period_day);
    
    
    			//determine curent day condition
    			if(in_array($v_period_day, $hospiz_days))
    			{
    				$curent_day_type = 'h';
    			}
    			else
    			{
    				$curent_day_type = 'n';
    			}
    
    			//determine curent condition shortcut
    			if($curent_day_no >= '11' && $curent_day_no <= '56')
    			{
    				$condition_shortcut['n'] = 'pv2pp';
    			}
    			else if($curent_day_no >= '57')
    			{
    				$condition_shortcut['n'] = 'pv3pp';
    			}
    
    			//hospiz
    			if($curent_day_no >= '11')
    			{
    				$condition_shortcut['h'] = 'ph2pp';
    			}
    
    			//determine next day condition
    			if(in_array($patient_period_days[$k_period_day + 1], $hospiz_days))
    			{
    				$next_day_type = 'h';
    			}
    			else
    			{
    				$next_day_type = 'n';
    			}
    
    
    			if($condition_shortcut[$curent_day_type])
    			{
    				//array each day with coresponding price
    				$invoice_details[$condition_shortcut[$curent_day_type]][$v_period_day] = $pricelist[$condition_shortcut[$curent_day_type]]['price'];
    
    				// get date range
    				$invoice_date_range[$condition_shortcut[$curent_day_type]][$incr][] = $v_period_day;
    				if($next_day_type != $curent_day_type)
    				{
    					$incr++;
    				}
    				if(strtotime($patient_period_days[$k_period_day + 1]) != strtotime("+1 day", strtotime($v_period_day)))
    				{
    					$incr++;
    				}
    			}
    		}
    	}
    
    
    	$heinvoice_items = new HeInvoiceItems();
    	//construct items array
    	if(strlen($shortcut_flatrate) > '0')
    	{
    		//				OLD
    		//				$invoice_items[$shortcut_flatrate]['from_date'][] = $admission_details['flatrate_days'][0];
    		//				$invoice_items[$shortcut_flatrate]['till_date'][] = end($admission_details['flatrate_days']);
    		//				NEW
    		$k_period = '0';
    		foreach($admission_details['flatrate_days'] as $k_adm_fl_day => $v_adm_fl_day)
    		{
    			$tmp_period[$k_period][] = $v_adm_fl_day;
    
    			$next_expected_day = date('Y-m-d', strtotime('+1 day', strtotime($v_adm_fl_day)));
    			if($admission_details['flatrate_days'][($k_adm_fl_day + 1)] != $next_expected_day)
    			{
    				$invoice_items[$shortcut_flatrate]['from_date'][$k_period] = $tmp_period[$k_period]['0'];
    				$invoice_items[$shortcut_flatrate]['till_date'][$k_period] = end($tmp_period[$k_period]);
    
    				if(!in_array($tmp_period[$k_period]['0'] . ' 00:00:00', $prev_inv_items_periods[$shortcut_flatrate]['from_date']) && !empty($prev_inv_items_periods[$shortcut_flatrate]['from_date']))
    				{
    					$invoice_items[$shortcut_flatrate]['paid_periods'][$k_period] = '1';
    				}
    				else
    				{
    					$invoice_items[$shortcut_flatrate]['paid_periods'][$k_period] = '0';
    				}
    				$k_period++;
    			}
    		}
    
    
    		$invoice_items[$shortcut_flatrate]['qty'] = '1';
    		$invoice_items[$shortcut_flatrate]['total'] = $pricelist[$shortcut_flatrate]['price'];
    		$invoice_items[$shortcut_flatrate]['shortcut'] = $shortcut_flatrate;
    		$invoice_items[$shortcut_flatrate]['price_details'] = $pricelist[$shortcut_flatrate];
    		$invoice_items[$shortcut_flatrate]['price'] = $pricelist[$shortcut_flatrate]['price'];
    		$invoice_items[$shortcut_flatrate]['custom'] = '0';
    	}
    
    	foreach($invoice_details as $k_inv_det => $v_inv_det)
    	{
    		$invoice_items[$k_inv_det]['qty'] = count($v_inv_det);
    		$invoice_items[$k_inv_det]['shortcut'] = $k_inv_det;
    		$invoice_items[$k_inv_det]['price_details'] = $pricelist[$k_inv_det];
    		$invoice_items[$k_inv_det]['price'] = $pricelist[$k_inv_det]['price'];
    		//TODO-3739 Carmen 26.01.2021 
    		//$invoice_items[$k_inv_det]['total'] = array_sum($v_inv_det);
    		foreach($v_inv_det as $date => $value)
    		{
    			$invoice_items[$k_inv_det]['total_other'] += $value;
    		}
    		$invoice_items[$k_inv_det]['total'] = str_replace(",",".",round(str_replace(",",".",$invoice_items[$k_inv_det]['total_other']),4));
    		//--
    		$invoice_items[$k_inv_det]['custom'] = '0';
    	}
    
    
    	foreach($invoice_date_range as $k_shortcut => $sh_interval_details)
    	{
    		foreach($sh_interval_details as $knr => $sh_interval_values)
    		{
    			$invoice_items[$k_shortcut]['from_date'][] = $sh_interval_values[0];
    			$invoice_items[$k_shortcut]['till_date'][] = end($sh_interval_values);
    		}
    	}
    
    	//			print_r($prev_inv_items_periods);
    	//			print_r($invoice_items);
    	//			exit;
    	return $invoice_items;
    }
    
    private function primary_method($ipid, $admission_details, $no_hospital_cycle_days, $hospiz_days, $pricelist)
    {
    
    	//count patient days and determine the shortcut to be used
    	$total_patient_days = count($no_hospital_cycle_days); //111 => PA10 || PC10
    	//		print_r($total_patient_days);
    	//assing shortcut based on total patients
    	//non hospiz
    	if($total_patient_days <= '10')
    	{
    		$condition_shortcut['n'] = 'pa1';
    	}
    	else if($total_patient_days <= '20')
    	{
    		$condition_shortcut['n'] = 'pa2';
    	}
    	else if($total_patient_days <= '30')
    	{
    		$condition_shortcut['n'] = 'pa3';
    	}
    	else if($total_patient_days <= '40')
    	{
    		$condition_shortcut['n'] = 'pa4';
    	}
    	else if($total_patient_days <= '50')
    	{
    		$condition_shortcut['n'] = 'pa5';
    	}
    	else if($total_patient_days <= '60')
    	{
    		$condition_shortcut['n'] = 'pa6';
    	}
    	else if($total_patient_days <= '75')
    	{
    		$condition_shortcut['n'] = 'pa7';
    	}
    	else if($total_patient_days <= '90')
    	{
    		$condition_shortcut['n'] = 'pa8';
    	}
    	else if($total_patient_days <= '105')
    	{
    		$condition_shortcut['n'] = 'pa9';
    	}
    	else if($total_patient_days <= '120')
    	{
    		$condition_shortcut['n'] = 'pa10';
    	}
    	else if($total_patient_days <= '150')
    	{
    		$condition_shortcut['n'] = 'pa11';
    	}
    	else if($total_patient_days <= '180')
    	{
    		$condition_shortcut['n'] = 'pa12';
    	}
    	else if($total_patient_days > '180')
    	{
    		$condition_shortcut['n'] = 'pa13';
    		$previous_short['n'] = 'pa12';
    	}
    
    	//hospiz conditions
    	if($total_patient_days <= '10')
    	{
    		$condition_shortcut['h'] = 'pc1';
    	}
    	else if($total_patient_days <= '20')
    	{
    		$condition_shortcut['h'] = 'pc2';
    	}
    	else if($total_patient_days <= '30')
    	{
    		$condition_shortcut['h'] = 'pc3';
    	}
    	else if($total_patient_days <= '40')
    	{
    		$condition_shortcut['h'] = 'pc4';
    	}
    	else if($total_patient_days <= '50')
    	{
    		$condition_shortcut['h'] = 'pc5';
    	}
    	else if($total_patient_days <= '60')
    	{
    		$condition_shortcut['h'] = 'pc6';
    	}
    	else if($total_patient_days <= '75')
    	{
    		$condition_shortcut['h'] = 'pc7';
    	}
    	else if($total_patient_days <= '90')
    	{
    		$condition_shortcut['h'] = 'pc8';
    	}
    	else if($total_patient_days <= '105')
    	{
    		$condition_shortcut['h'] = 'pc9';
    	}
    	else if($total_patient_days <= '120')
    	{
    		$condition_shortcut['h'] = 'pc10';
    	}
    	else if($total_patient_days <= '150')
    	{
    		$condition_shortcut['h'] = 'pc11';
    	}
    	else if($total_patient_days <= '180')
    	{
    		$condition_shortcut['h'] = 'pc12';
    	}
    	else if($total_patient_days > '180')
    	{
    		$condition_shortcut['h'] = 'pc13';
    		$previous_short['h'] = 'pc12';
    	}
    
    
    	$shortcut = $condition_shortcut[$admission_details['type']];
    
    	if(!empty($hospiz_days))
    	{
    		$nonhospiz_days_arr = array_diff($no_hospital_cycle_days, $hospiz_days);
    	}
    	else
    	{
    		$nonhospiz_days_arr = $no_hospital_cycle_days;
    	}
    
    	asort($nonhospiz_days_arr);
    	asort($hospiz_days);
    
    	$hospiz_days = array_values($hospiz_days);
    
    	$incr = '0';
    
    	$calculated_period_days = array();
    	if($admission_details['type'] == 'h')
    	{
    		$calculated_period_days = $hospiz_days;
    	}
    	else
    	{
    		$calculated_period_days = $nonhospiz_days_arr;
    	}
    
    	$calculated_period_days_arr = array();
    	$days_counter = '1';
    	foreach($calculated_period_days as $k_day => $v_day)
    	{
    		//PA12 || PC12 period
    		if($days_counter <= '180' && $previous_short[$admission_details['type']])
    		{
    			$max_days_periods[$incr][] = $v_day;
    		}
    
    		//PA13 || PC13
    		if($days_counter > '180' && $previous_short[$admission_details['type']])
    		{
    			$calculated_period_days_arr[$incr][] = $v_day;
    		}
    		else if($days_counter < '180' && empty($previous_short[$admission_details['type']]))
    		{
    			$calculated_period_days_arr[$incr][] = $v_day;
    		}
    
    		if(strtotime('+1 day', strtotime($v_day)) != strtotime($calculated_period_days[$k_day + 1]))
    		{
    			$incr++;
    		}
    
    		$days_counter++;
    	}
    
    	if($total_patient_days > '180')
    	{
    		$patient_days_overdue = ($total_patient_days - 180);
    		$multiplier = ceil($patient_days_overdue / 30);
    
    		//PA12 || PC12
    		$invoice_items[$previous_short[$admission_details['type']]]['shortcut'] = $previous_short[$admission_details['type']];
    		$invoice_items[$previous_short[$admission_details['type']]]['qty'] = '1';
    		$invoice_items[$previous_short[$admission_details['type']]]['price_details'] = $pricelist[$previous_short[$admission_details['type']]];
    		$invoice_items[$previous_short[$admission_details['type']]]['price'] = $pricelist[$previous_short[$admission_details['type']]]['price'];
    		$invoice_items[$previous_short[$admission_details['type']]]['total'] = $pricelist[$previous_short[$admission_details['type']]]['price'];
    		$invoice_items[$previous_short[$admission_details['type']]]['custom'] = '0';
    
    
    		foreach($max_days_periods as $k_max_period => $v_max_period)
    		{
    			$invoice_items[$previous_short[$admission_details['type']]]['from_date'][] = $v_max_period[0];
    			$invoice_items[$previous_short[$admission_details['type']]]['till_date'][] = end($v_max_period);
    		}
    
    
    		//PA13 || PC13
    		if($multiplier > '0')
    		{
    			$invoice_items[$shortcut]['shortcut'] = $shortcut;
    			$invoice_items[$shortcut]['price_details'] = $pricelist[$shortcut];
    			$invoice_items[$shortcut]['price'] = $pricelist[$shortcut]['price'];
    			$invoice_items[$shortcut]['qty'] = $multiplier;
    			$invoice_items[$shortcut]['total'] = ($multiplier * $pricelist[$shortcut]['price']);
    			$invoice_items[$shortcut]['custom'] = '0';
    		}
    	}
    	else
    	{
    		$invoice_items[$shortcut]['shortcut'] = $shortcut;
    		$invoice_items[$shortcut]['price_details'] = $pricelist[$shortcut];
    		$invoice_items[$shortcut]['price'] = $pricelist[$shortcut]['price'];
    		$invoice_items[$shortcut]['qty'] = '1';
    		$invoice_items[$shortcut]['total'] = $pricelist[$shortcut]['price'];
    		$invoice_items[$shortcut]['custom'] = '0';
    	}
    
    	foreach($calculated_period_days_arr as $k_period => $v_period)
    	{
    		$invoice_items[$shortcut]['from_date'][] = $v_period[0];
    		$invoice_items[$shortcut]['till_date'][] = end($v_period);
    	}
    	if($_REQUEST['dbg_nc'] == '1')
    	{
    		print_r("Shortcut: " . $shortcut . " -- TOTal patient days: " . $total_patient_days . ", Multiplier = " . $multiplier . "\n");
    		print_r("Condition shortcut :\n");
    		print_r($condition_shortcut);
    		print_r("Total patient days :\n");
    		print_r($total_patient_days);
    		print_r("Invoice Items :\n");
    		print_r($invoice_items);
    		exit;
    	}
    
    	return $invoice_items;
    }
    
    private function primary_method_changes($ipid, $admission_details, $no_hospital_cycle_days, $hospiz_days, $pricelist, $hospiz_days_arr_type)
    {
    
    	if($_REQUEST['dbgq'])
    	{
    		print_r("hospiz_days_cs\n");
    		print_r($hospiz_days);
    		print_r("hospiz_days_arr_type\n");
    		print_r($hospiz_days_arr_type);
    		exit;
    	}
    	asort($hospiz_days);
    	$hospiz_days = array_values($hospiz_days);
    	//count patient days and determine the shortcut to be used
    	$total_patient_days = count($no_hospital_cycle_days); //111 => PA10 || PC10
    	//assing shortcut based on total patients
    	//non hospiz
    	if($total_patient_days <= '10')
    	{
    		$condition_shortcut['n'] = 'pa1';
    	}
    	else if($total_patient_days <= '20')
    	{
    		$condition_shortcut['n'] = 'pa2';
    	}
    	else if($total_patient_days <= '30')
    	{
    		$condition_shortcut['n'] = 'pa3';
    	}
    	else if($total_patient_days <= '40')
    	{
    		$condition_shortcut['n'] = 'pa4';
    	}
    	else if($total_patient_days <= '50')
    	{
    		$condition_shortcut['n'] = 'pa5';
    	}
    	else if($total_patient_days <= '60')
    	{
    		$condition_shortcut['n'] = 'pa6';
    	}
    	else if($total_patient_days <= '75')
    	{
    		$condition_shortcut['n'] = 'pa7';
    	}
    	else if($total_patient_days <= '90')
    	{
    		$condition_shortcut['n'] = 'pa8';
    	}
    	else if($total_patient_days <= '105')
    	{
    		$condition_shortcut['n'] = 'pa9';
    	}
    	else if($total_patient_days <= '120')
    	{
    		$condition_shortcut['n'] = 'pa10';
    	}
    	else if($total_patient_days <= '150')
    	{
    		$condition_shortcut['n'] = 'pa11';
    	}
    	else if($total_patient_days <= '180')
    	{
    		$condition_shortcut['n'] = 'pa12';
    	}
    	else if($total_patient_days > '180')
    	{
    		$condition_shortcut['n'] = 'pa13';
    	}
    
    	//hospiz conditions
    	if($total_patient_days <= '10')
    	{
    		$condition_shortcut['h'] = 'pc1';
    	}
    	else if($total_patient_days <= '20')
    	{
    		$condition_shortcut['h'] = 'pc2';
    	}
    	else if($total_patient_days <= '30')
    	{
    		$condition_shortcut['h'] = 'pc3';
    	}
    	else if($total_patient_days <= '40')
    	{
    		$condition_shortcut['h'] = 'pc4';
    	}
    	else if($total_patient_days <= '50')
    	{
    		$condition_shortcut['h'] = 'pc5';
    	}
    	else if($total_patient_days <= '60')
    	{
    		$condition_shortcut['h'] = 'pc6';
    	}
    	else if($total_patient_days <= '75')
    	{
    		$condition_shortcut['h'] = 'pc7';
    	}
    	else if($total_patient_days <= '90')
    	{
    		$condition_shortcut['h'] = 'pc8';
    	}
    	else if($total_patient_days <= '105')
    	{
    		$condition_shortcut['h'] = 'pc9';
    	}
    	else if($total_patient_days <= '120')
    	{
    		$condition_shortcut['h'] = 'pc10';
    	}
    	else if($total_patient_days <= '150')
    	{
    		$condition_shortcut['h'] = 'pc11';
    	}
    	else if($total_patient_days <= '180')
    	{
    		$condition_shortcut['h'] = 'pc12';
    	}
    	else if($total_patient_days > '180')
    	{
    		$condition_shortcut['h'] = 'pc13';
    	}
    
    	//calculate each amount
    	$all_hospiz_days = count($hospiz_days);
    	$all_non_hospiz_days = ($total_patient_days - $all_hospiz_days);
    
    	//calculate hospiz/non-hospiz percent from overall time
    	// and extract the percent from overall time shortcut price
    
    	if($all_hospiz_days > '0')
    	{
    		$hospiz_percent = ($all_hospiz_days / $total_patient_days) * 100;
    		$hospiz_amount = round((($hospiz_percent / 100) * $pricelist[$condition_shortcut['h']]['price']), 2);
    
    		if($non_hospiz_percent != '0')
    		{
    			$invoice_items[$condition_shortcut['h']]['qty'] = '1';
    			$invoice_items[$condition_shortcut['h']]['percent'] = number_format($hospiz_percent, '2', '.', '');
    			$invoice_items[$condition_shortcut['h']]['shortcut'] = $condition_shortcut['h'];
    			$invoice_items[$condition_shortcut['h']]['price_details'] = $pricelist[$condition_shortcut['h']];
    			$invoice_items[$condition_shortcut['h']]['price'] = $pricelist[$condition_shortcut['h']]['price'];
    			$invoice_items[$condition_shortcut['h']]['total'] = $hospiz_amount;
    			$invoice_items[$condition_shortcut['h']]['custom'] = '0';
    
    			foreach($hospiz_days_arr_type as $k_period_id => $v_period_days)
    			{
    				if(strlen(trim(rtrim($v_period_days[0]))) > '0')
    				{
    					$invoice_items[$condition_shortcut['h']]['from_date'][$k_period_id] = $v_period_days[0];
    					$invoice_items[$condition_shortcut['h']]['till_date'][$k_period_id] = end($v_period_days);
    				}
    			}
    
    			if(count($invoice_items[$condition_shortcut['h']]['from_date']) > '0')
    			{
    				$invoice_items[$condition_shortcut['h']]['from_date'] = array_values($invoice_items[$condition_shortcut['h']]['from_date']);
    			}
    
    			if(count($invoice_items[$condition_shortcut['h']]['till_date']) > '0')
    			{
    				$invoice_items[$condition_shortcut['h']]['till_date'] = array_values($invoice_items[$condition_shortcut['h']]['till_date']);
    			}
    		}
    	}
    
    	$nonhospiz_days_arr = array_diff($no_hospital_cycle_days, $hospiz_days);
    
    	asort($nonhospiz_days_arr);
    	$incr = '0';
    	foreach($nonhospiz_days_arr as $k_day => $v_day)
    	{
    		$period_non_hospiz_days[$incr][] = $v_day;
    		if(strtotime('+1 day', strtotime($v_day)) != strtotime($nonhospiz_days_arr[$k_day + 1]))
    		{
    			$incr++;
    		}
    	}
    
    	$non_hospiz_percent = ($all_non_hospiz_days / $total_patient_days) * 100;
    	$non_hospiz_amount = round((($non_hospiz_percent / 100) * $pricelist[$condition_shortcut['n']]['price']), 2);
    
    	if($non_hospiz_percent != '0')
    	{
    		$invoice_items[$condition_shortcut['n']]['qty'] = '1';
    		$invoice_items[$condition_shortcut['n']]['percent'] = number_format($non_hospiz_percent, '2', '.', '');
    		$invoice_items[$condition_shortcut['n']]['shortcut'] = $condition_shortcut['n'];
    		$invoice_items[$condition_shortcut['n']]['price'] = $pricelist[$condition_shortcut['n']]['price'];
    		$invoice_items[$condition_shortcut['n']]['price_details'] = $pricelist[$condition_shortcut['n']];
    		$invoice_items[$condition_shortcut['n']]['total'] = $non_hospiz_amount;
    		$invoice_items[$condition_shortcut['n']]['custom'] = '0';
    
    		foreach($period_non_hospiz_days as $k_period => $v_period)
    		{
    			if(strlen(trim(rtrim($v_period[0]))) > '0')
    			{
    				$invoice_items[$condition_shortcut['n']]['from_date'][] = $v_period[0];
    				$invoice_items[$condition_shortcut['n']]['till_date'][] = end($v_period);
    			}
    		}
    	}
    
    
    	if($_REQUEST['dbg'] == 'z')
    	{
    		print_r("hospiz_days_arr_type\n");
    		print_r($hospiz_days_arr_type);
    
    		print_r("only hospiz_days\n");
    		print_r($hospiz_days);
    
    		print_r('invoice_items\n');
    		print_r($invoice_items);
    
    		print_r("no_hospital_cycle_days\n");
    		print_r($no_hospital_cycle_days);
    		//			print_r("no_hospital_cycle_days\n");
    		//			print_r($no_hospital_cycle_days);
    		print_r("all_hospiz_days: ");
    		print_r($all_hospiz_days);
    		print_r("\n all_non_hospiz_days: ");
    		print_r($all_non_hospiz_days);
    		print_r("\n hopsiz percentage: ");
    		print_r($hospiz_percent);
    		print_r("\n hospiz amount ");
    		print_r($hospiz_amount);
    		print_r("\n nonhospiz percentage ");
    		print_r($non_hospiz_percent);
    		print_r("\n nonhospiz amount ");
    		print_r($non_hospiz_amount);
    		print_r("\n hospiz_percent_no_round: ");
    		print_r($hospiz_percent_no_round);
    		exit;
    	}
    
    
    
    
    	//		print_r($invoice_items);
    	//		print_r("total_patient_days :".$total_patient_days."\n");
    	//		print_r("all_hospiz_days :".$all_hospiz_days."\n");
    	//		print_r("all_non_hospiz_days :".$all_non_hospiz_days."\n");
    	//		print_r($condition_shortcut);
    	//		print_r("\nNon Hospiz percent: ".$non_hospiz_percent."% from ". $pricelist[$condition_shortcut['n']]['price'] ." = ".$non_hospiz_amount."\n");
    	//		print_r("\nHospiz percent: ".$hospiz_percent."% from ". $pricelist[$condition_shortcut['h']]['price'] ." = ".$hospiz_amount."\n");
    	//		exit;
    
    	return $invoice_items;
    }
    
    private function primary_method_changes_new($ipid, $admission_details, $no_hospital_cycle_days, $hospiz_days, $pricelist, $hospiz_days_arr_type)
    {
    	asort($hospiz_days);
    	$hospiz_days = array_values($hospiz_days);
    	//count patient days and determine the shortcut to be used
    	$total_patient_days = count($no_hospital_cycle_days); //111 => PA10 || PC10
    	//assing shortcut based on total patients
    	//non hospiz
    	if($total_patient_days <= '10')
    	{
    		$condition_shortcut['n'] = 'pa1';
    	}
    	else if($total_patient_days <= '20')
    	{
    		$condition_shortcut['n'] = 'pa2';
    	}
    	else if($total_patient_days <= '30')
    	{
    		$condition_shortcut['n'] = 'pa3';
    	}
    	else if($total_patient_days <= '40')
    	{
    		$condition_shortcut['n'] = 'pa4';
    	}
    	else if($total_patient_days <= '50')
    	{
    		$condition_shortcut['n'] = 'pa5';
    	}
    	else if($total_patient_days <= '60')
    	{
    		$condition_shortcut['n'] = 'pa6';
    	}
    	else if($total_patient_days <= '75')
    	{
    		$condition_shortcut['n'] = 'pa7';
    	}
    	else if($total_patient_days <= '90')
    	{
    		$condition_shortcut['n'] = 'pa8';
    	}
    	else if($total_patient_days <= '105')
    	{
    		$condition_shortcut['n'] = 'pa9';
    	}
    	else if($total_patient_days <= '120')
    	{
    		$condition_shortcut['n'] = 'pa10';
    	}
    	else if($total_patient_days <= '150')
    	{
    		$condition_shortcut['n'] = 'pa11';
    	}
    	else if($total_patient_days <= '180')
    	{
    		$condition_shortcut['n'] = 'pa12';
    	}
    	else if($total_patient_days > '180')
    	{
    		$condition_shortcut['n'] = 'pa13';
    		$previous_shortcut['n'] = 'pa12';
    	}
    
    	//hospiz conditions
    	if($total_patient_days <= '10')
    	{
    		$condition_shortcut['h'] = 'pc1';
    	}
    	else if($total_patient_days <= '20')
    	{
    		$condition_shortcut['h'] = 'pc2';
    	}
    	else if($total_patient_days <= '30')
    	{
    		$condition_shortcut['h'] = 'pc3';
    	}
    	else if($total_patient_days <= '40')
    	{
    		$condition_shortcut['h'] = 'pc4';
    	}
    	else if($total_patient_days <= '50')
    	{
    		$condition_shortcut['h'] = 'pc5';
    	}
    	else if($total_patient_days <= '60')
    	{
    		$condition_shortcut['h'] = 'pc6';
    	}
    	else if($total_patient_days <= '75')
    	{
    		$condition_shortcut['h'] = 'pc7';
    	}
    	else if($total_patient_days <= '90')
    	{
    		$condition_shortcut['h'] = 'pc8';
    	}
    	else if($total_patient_days <= '105')
    	{
    		$condition_shortcut['h'] = 'pc9';
    	}
    	else if($total_patient_days <= '120')
    	{
    		$condition_shortcut['h'] = 'pc10';
    	}
    	else if($total_patient_days <= '150')
    	{
    		$condition_shortcut['h'] = 'pc11';
    	}
    	else if($total_patient_days <= '180')
    	{
    		$condition_shortcut['h'] = 'pc12';
    	}
    	else if($total_patient_days > '180')
    	{
    		$condition_shortcut['h'] = 'pc13';
    		$previous_shortcut['h'] = 'pc12';
    	}
    
    	//calculate each amount
    	$all_hospiz_days = count($hospiz_days);
    	$all_non_hospiz_days = ($total_patient_days - $all_hospiz_days);
    
    	//			L.E: new method calculates the sum of pa12&&pc12 which will be
    	//			used as price for percent calculation below
    	//calculate multiplier for curent condition shortcut and previous shortcut
    	$grouped_pre_item['n'] = array();
    	$grouped_pre_item['h'] = array();
    	if(in_array('pa13', $condition_shortcut) || in_array('pc13', $condition_shortcut))
    	{
    		$qty = '1';
    		//pa12
    		//get previous shortcut price non-hospiz
    		$pre_item[$previous_shortcut['n']]['shortcut'] = $previous_shortcut['n'];
    		$pre_item[$previous_shortcut['n']]['qty'] = $qty;
    		$pre_item[$previous_shortcut['n']]['name'] = '( ' . $qty . ' x ' . number_format($pricelist[$previous_shortcut['n']]['price'], 2, ',', '.') . ')';
    		$pre_item[$previous_shortcut['n']]['price'] = $pricelist[$previous_shortcut['n']]['price'];
    		$pre_item[$previous_shortcut['n']]['total'] = ($qty * $pricelist[$previous_shortcut['n']]['price']);
    
    		//pa13
    		$patient_days_overdue = ($total_patient_days - 180);
    
    		if($patient_days_overdue > 0)
    		{
    			$multiplier = ceil($patient_days_overdue / 30);
    		}
    
    		$pre_item[$condition_shortcut['n']]['shortcut'] = $condition_shortcut['n'];
    		$pre_item[$condition_shortcut['n']]['qty'] = $multiplier;
    		$pre_item[$condition_shortcut['n']]['name'] = '( ' . $multiplier . ' x ' . number_format($pricelist[$condition_shortcut['n']]['price'], 2, ',', '.') . ')';
    		$pre_item[$condition_shortcut['n']]['price'] = $pricelist[$condition_shortcut['n']]['price'];
    		$pre_item[$condition_shortcut['n']]['total'] = ($multiplier * $pricelist[$condition_shortcut['n']]['price']);
    		array_push($grouped_pre_item['n'], $pre_item[$previous_shortcut['n']]);
    		array_push($grouped_pre_item['n'], $pre_item[$condition_shortcut['n']]);
    
    		//pc12 && pc13
    		if($all_hospiz_days > '0')
    		{
    			$pre_item[$previous_shortcut['h']]['shortcut'] = $previous_shortcut['h'];
    			$pre_item[$previous_shortcut['h']]['qty'] = $qty;
    			$pre_item[$previous_shortcut['h']]['name'] = '( ' . $qty . ' x ' . number_format($pricelist[$previous_shortcut['h']]['price'], 2, ',', '.') . ')';
    			$pre_item[$previous_shortcut['h']]['price'] = $pricelist[$previous_shortcut['h']]['price'];
    			$pre_item[$previous_shortcut['h']]['total'] = ($qty * $pricelist[$previous_shortcut['h']]['price']);
    
    			$pre_item[$condition_shortcut['h']]['shortcut'] = $condition_shortcut['h'];
    			$pre_item[$condition_shortcut['h']]['qty'] = $multiplier;
    			$pre_item[$condition_shortcut['h']]['name'] = '( ' . $multiplier . ' x ' . number_format($pricelist[$condition_shortcut['h']]['price'], 2, ',', '.') . ')';
    			$pre_item[$condition_shortcut['h']]['price'] = $pricelist[$condition_shortcut['h']]['price'];
    			$pre_item[$condition_shortcut['h']]['total'] = ($multiplier * $pricelist[$condition_shortcut['h']]['price']);
    
    			array_push($grouped_pre_item['h'], $pre_item[$previous_shortcut['h']]);
    			array_push($grouped_pre_item['h'], $pre_item[$condition_shortcut['h']]);
    		}
    
    		//sum pa12 + pa13 => new price
    		$sub_price['n'] = ($pre_item[$previous_shortcut['n']]['total'] + $pre_item[$condition_shortcut['n']]['total']);
    		$sub_price['h'] = ($pre_item[$previous_shortcut['h']]['total'] + $pre_item[$condition_shortcut['h']]['total']);
    		if($_REQUEST['dqd'])
    		{
    			print_r($pre_item);
    			print_r($grouped_pre_item);
    			print_r($sub_price);
    		}
    	}
    
    
    
    	if($previous_shortcut['h'])
    	{
    		$short_prefix = '_';
    	}
    	//calculate hospiz/non-hospiz percent from overall time
    	// and extract the percent from overall time shortcut price
    	if($all_hospiz_days > '0')
    	{
    		if($sub_price['h'])
    		{
    			$hospiz_item_price = number_format($sub_price['h'], 2, '.', '');
    		}
    		else
    		{
    			$hospiz_item_price = number_format($pricelist[$condition_shortcut['h']]['price'], 2, '.', '');
    		}
    		$hospiz_percent = number_format((($all_hospiz_days / $total_patient_days) * 100), 2, '.', '');
    		$hospiz_amount = number_format((($hospiz_percent / 100) * $hospiz_item_price), 2, '.', '');
    
    		if($hospiz_percent != '0')
    		{
    			$invoice_items[$condition_shortcut['h']]['qty'] = '1';
    			$invoice_items[$condition_shortcut['h']]['percent'] = number_format($hospiz_percent, '2', '.', '');
    
    			foreach($grouped_pre_item['h'] as $k_item_h => $v_item_h)
    			{
    				$invoice_items[$condition_shortcut['h']]['description_items'][] = '&nbsp;' . $this->view->translate('shortcut_name_' . $v_item_h['shortcut']) . '&nbsp;&nbsp;&nbsp;&nbsp;' . $this->view->translate('shortcut_description_' . $v_item_h['shortcut']) . ' ' . $v_item_h['name'];
    			}
    
    			if(!empty($grouped_pre_item['h']))
    			{
    				$invoice_items[$condition_shortcut['h']]['description'] = implode('<br /> ', $invoice_items[$condition_shortcut['h']]['description_items']);
    			}
    
    			$invoice_items[$condition_shortcut['h']]['shortcut'] = $condition_shortcut['h'] . $short_prefix;
    			$invoice_items[$condition_shortcut['h']]['price_details'] = $pricelist[$condition_shortcut['h']];
    
    			if($sub_price['h'])
    			{
    				$invoice_items[$condition_shortcut['h']]['price_details']['price'] = $hospiz_item_price;
    			}
    
    
    			$invoice_items[$condition_shortcut['h']]['price'] = $hospiz_item_price;
    			$invoice_items[$condition_shortcut['h']]['total'] = $hospiz_amount;
    			$invoice_items[$condition_shortcut['h']]['custom'] = '0';
    
    			foreach($hospiz_days_arr_type as $k_period_id => $v_period_days)
    			{
    				if(strlen(trim(rtrim($v_period_days[0]))) > '0')
    				{
    					$invoice_items[$condition_shortcut['h']]['from_date'][$k_period_id] = $v_period_days[0];
    					$invoice_items[$condition_shortcut['h']]['till_date'][$k_period_id] = end($v_period_days);
    				}
    			}
    
    			if(count($invoice_items[$condition_shortcut['h']]['from_date']) > '0')
    			{
    				$invoice_items[$condition_shortcut['h']]['from_date'] = array_values($invoice_items[$condition_shortcut['h']]['from_date']);
    			}
    
    			if(count($invoice_items[$condition_shortcut['h']]['till_date']) > '0')
    			{
    				$invoice_items[$condition_shortcut['h']]['till_date'] = array_values($invoice_items[$condition_shortcut['h']]['till_date']);
    			}
    		}
    	}
    
    	$nonhospiz_days_arr = array_diff($no_hospital_cycle_days, $hospiz_days);
    
    	asort($nonhospiz_days_arr);
    	$incr = '0';
    	foreach($nonhospiz_days_arr as $k_day => $v_day)
    	{
    		$period_non_hospiz_days[$incr][] = $v_day;
    		if(strtotime('+1 day', strtotime($v_day)) != strtotime($nonhospiz_days_arr[$k_day + 1]))
    		{
    			$incr++;
    		}
    	}
    
    	if($sub_price['h'])
    	{
    		$non_hospiz_item_price = number_format($sub_price['n'], 2, '.', '');
    	}
    	else
    	{
    		$non_hospiz_item_price = number_format($pricelist[$condition_shortcut['n']]['price'], 2, '.', '');
    	}
    	//			$non_hospiz_item_price = number_format($sub_price['n'], 2, '.','');
    	$non_hospiz_percent = number_format((($all_non_hospiz_days / $total_patient_days) * 100), 2, '.', '');
    	$non_hospiz_amount = number_format((($non_hospiz_percent / 100) * $non_hospiz_item_price), 2, '.', '');
    
    	if($non_hospiz_percent != '0')
    	{
    		$invoice_items[$condition_shortcut['n']]['qty'] = '1';
    		$invoice_items[$condition_shortcut['n']]['percent'] = $non_hospiz_percent;
    
    		foreach($grouped_pre_item['n'] as $k_item_n => $v_item_n)
    		{
    			$invoice_items[$condition_shortcut['n']]['description_items'][] = '&nbsp;' . $this->view->translate('shortcut_name_' . $v_item_n['shortcut']) . '&nbsp;&nbsp;&nbsp;&nbsp;' . $this->view->translate('shortcut_description_' . $v_item_n['shortcut']) . ' ' . $v_item_n['name'];
    		}
    
    		if(!empty($grouped_pre_item['n']))
    		{
    			$invoice_items[$condition_shortcut['n']]['description'] = implode('<br /> ', $invoice_items[$condition_shortcut['n']]['description_items']);
    		}
    
    		$invoice_items[$condition_shortcut['n']]['shortcut'] = $condition_shortcut['n'] . $short_prefix;
    		$invoice_items[$condition_shortcut['n']]['price'] = $non_hospiz_item_price;
    
    		$invoice_items[$condition_shortcut['n']]['price_details'] = $pricelist[$condition_shortcut['n']];
    
    		if($sub_price['n'])
    		{
    			$invoice_items[$condition_shortcut['n']]['price_details']['price'] = $non_hospiz_item_price;
    		}
    
    		$invoice_items[$condition_shortcut['n']]['total'] = $non_hospiz_amount;
    		$invoice_items[$condition_shortcut['n']]['custom'] = '0';
    
    		foreach($period_non_hospiz_days as $k_period => $v_period)
    		{
    			if(strlen(trim(rtrim($v_period[0]))) > '0')
    			{
    				$invoice_items[$condition_shortcut['n']]['from_date'][] = $v_period[0];
    				$invoice_items[$condition_shortcut['n']]['till_date'][] = end($v_period);
    			}
    		}
    	}
    
    
    	if($_REQUEST['dbgtst'])
    	{
    		print_r("Total patient days:" . $total_patient_days . "\n");
    		print_r("Hospiz days:" . $all_hospiz_days . "\n");
    		print_r("Non-Hospiz days:" . $all_non_hospiz_days . "\n");
    
    		print_r("Hospiz Days Arr\n");
    		print_r($hospiz_days);
    		print_r("Non Hospiz Days Arr\n");
    		print_r($nonhospiz_days_arr);
    
    		exit;
    	}
    
    	return $invoice_items;
    }
    
    public function invoicesAction(){
        
        //you reach this if from a inline ajax
        if ($this->getRequest()->isXmlHttpRequest()) {
            
            switch ($this->getRequest()->getPost('__action')) {
                case "transmit_hl7_ft1":
                    $this->_helper->layout->setLayout('layout_ajax');
                    $this->_helper->viewRenderer->setNoRender();
                    
                    $this->__invoicesnew_hl7_ft1();
                    
                    exit; //for read-ability
                    
                    break;
                    // Ancuta 12.05.2020
                case "transmit_hl7_activation":
                    $this->_helper->layout->setLayout('layout_ajax');
                    $this->_helper->viewRenderer->setNoRender();
                    
                    $this->__invoicesnew_hl7_activation();
                    
                    exit; //for read-ability
                    
                    break;
            }
        }
        
        //general data
        $patientmaster = new PatientMaster();
        $client_details = new Client();
        $clientid = $this->clientid;
        $userid = $this->userid;
        
        
        //get allowed client invoices
        // if multiple - chose first one - and allow user to select whic invoices to see
//         $client_allowed_invoices = ClientInvoicePermissions::get_client_allowed_invoices($clientid);
        $client_allowed_invoices = ClientInvoiceMultiplePermissions::get_client_allowed_invoices($clientid);
        
        //construct invoice type selector START
        $attrs = array();
        $attrs['onChange'] = 'change_invoice_type(this.value);';
        $attrs['class'] = 'invoice_type';
        
        //construct month_selector END
        if(count($client_allowed_invoices) == "1"){
            $invoice_type = end($client_allowed_invoices);
            $this->view->invoice_type_selector = "";
            
        } else{
            if(strlen($_REQUEST['invoice_type']) == '0')
            {
                $invoice_type = end($client_allowed_invoices);
            }
            else
            {
                $invoice_type = $_REQUEST['invoice_type'];
            }
            

            $client_allowed_invoices_tr = array();
            foreach($client_allowed_invoices as $inv=>$inv_type_name){
                $client_allowed_invoices_tr[$inv] = $this->translate($inv_type_name.'_label');
             }
             $this->view->invoice_type_selector = $this->view->formSelect("invoice_type", $invoice_type, $attrs, $client_allowed_invoices_tr);
        }
        if(empty($invoice_type)){
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }

        
        $this->view->invoice_type = $invoice_type;
        $this->view->allowed_invoice = $invoice_type;
  

        
        $system_invoice_details = Pms_CommonData::clients_invoices_details();
        if(empty($system_invoice_details[$invoice_type])){
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }
        
        // ExTRA CHECK - if no model was added
        if(empty($system_invoice_details[$invoice_type]['models']['invoice'])){
            echo "check data";
            return;
        }
        
        $invoice_model = $system_invoice_details[$invoice_type]['models']['invoice'];
        $invoice_form = $system_invoice_details[$invoice_type]['forms']['invoice'];
        if($invoice_form){
            $invoice_form_obj = new $invoice_form();
        }
        $invoice_payment_model = $system_invoice_details[$invoice_type]['models']['payment'];
        $invoice_payment_obj = new $invoice_payment_model();
        

        $invoice_edit_link = $system_invoice_details[$invoice_type]['links']['edit'];
        $this->view->invoice_edit_link = $invoice_edit_link;
        $invoice_print_link = $system_invoice_details[$invoice_type]['links']['print'];
        $this->view->invoice_print_link = $invoice_print_link;
        $invoice_print_storno_link = $system_invoice_details[$invoice_type]['links']['print_storno'];
        $this->view->invoice_print_storno_link = $invoice_print_storno_link;
        
        
        $invoice_model_obj = null;
        $invoice_model_obj = new $invoice_model();
        
        $invoice_columns  = array();
        $invoice_columns = array_keys($invoice_model_obj->getTable()->getColumns());
        
        
        
        
        
        //ISPC-2609 Ancuta 28.08.2020 + Changes on  07.09.2020
        //get printjobs - active or completed - for client, user and invoice type
        $allowed_invoice_name =  $invoice_type;
        $this->view->allowed_invoice = $allowed_invoice_name;
        $invoice_user_printjobs = PrintJobsBulkTable::_find_invoices_print_jobs($clientid,$this->userid,$allowed_invoice_name );
        
        $print_html = '<div class="print_jobs_div">';
        $print_html = '<fieldset>';
        $print_html .= "<legend> ".$this->translate('print_job_table_headline')."</legend>";
        $print_html .= '<span id="clear_user_jobs" class="clear_user_jobs" style="width:95%;" data-user="'.$this->userid.'"  data-invoice_type="'.$allowed_invoice_name .'" data-client="'.$clientid.'"> '.$this->translate('Clear_all_prints')."</span>";
        $table_html = $this->view->tabulate($invoice_user_printjobs,array("class"=>"datatable",'id'=>'print_jobs_table','escaped'=>false));
        $print_html .= $table_html;
        $print_html .= '</fieldset>';
        $print_html .= '</div>';
        if(count($invoice_user_printjobs) > 1 ){
            echo $print_html;
        }
        
        $this->view->show_print_jobs = $this->user_print_jobs;
        //---
        
        //construct months array - for search
        $start_period = '2010-01-01';
        $end_period = date('Y-m-d', time());
        $period_months_array = Pms_CommonData::get_period_months($start_period, $end_period, 'Y-m');
        $month_select_array['99999999'] = '';
        foreach($period_months_array as $k_month => $v_month)
        {
            $month_select_array[$v_month] = $v_month;
        }
        
        //see how many days in selected month
        $this->view->month_selected = date('m.Y', strtotime($selected_month . '-01'));
        
        if(!function_exists('cal_days_in_month'))
        {
            $month_days = date('t', mktime(0, 0, 0, date("n", strtotime($selected_month . "-01")), 1, date("Y", strtotime($selected_month . "-01"))));
        }
        else
        {
            $month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($selected_month . "-01")), date("Y", strtotime($selected_month . "-01")));
        }
        
        //construct selected month array (start, days, end)
        $months_details[$selected_month]['start'] = $selected_month . "-01";
        $months_details[$selected_month]['days_in_month'] = $month_days;
        $months_details[$selected_month]['end'] = $selected_month . '-' . $month_days;
        
        krsort($month_select_array);
        
        $this->view->months_selector = $this->view->formSelect("selected_month", '', null, $month_select_array);
        
 
        
        include 'InvoicenewController.php';
        $invoicenewController = new InvoicenewController($this->_request, $this->_response);
        
        include 'InvoiceController.php';
        $invoiceController = new InvoiceController($this->_request, $this->_response);
        
        
        
        
        if($invoice_type == "by_invoice"){
       
            if($_REQUEST['mode'] == "delete")
            {
                $client_invoice = new ClientInvoices();
                $invoice_details = $client_invoice->getInvoice($_REQUEST['invoiceid'], $clientid);
                $invoice_status = $invoice_details[0]['status'];
                
                if($invoice_status != '2')
                {
                    $delInvoice = $invoice_model_obj->DeleteInvoice($_REQUEST['invoiceid']);
                    
                    if($delInvoice > 0)
                    {
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=by_invoice?flg=suc');
                    }
                    else
                    {
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=by_invoice?flg=err');
                    }
                }
                else
                {
                    $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=by_invoice?flg=err');
                }
            }
            
            if($_REQUEST['mode'] == 'setstorno')
            {
                if(is_numeric($_REQUEST['inv_id']) && strlen($_REQUEST['inv_id']) > '0')
                {
                    $invoiceid = $_REQUEST['inv_id'];
                }
                else
                {
                    $invoiceid = '0';
                }
                
                if($invoiceid > '0')
                {
                    $client_invoices = new ClientInvoices();
                    $clone_record = $client_invoices->create_storno_invoice($invoiceid);
                    
                    $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=by_invoice?flg=suc');
                    exit;
                }
                $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=by_invoice?flg=suc');
                exit;
            }
            
            
            if($this->getRequest()->isPost())
            {
 
                if($_POST['deletemore'] == "1")
                {
                    $delInvoice = $invoice_form_obj->DeleteInvoices($_POST['document']);
                }
                else if($_POST['cancelmore'] == "1")
                {
                    $delInvoice = $invoice_form_obj->ToggleStatusInvoices($_POST['document'], "3");
                }
                else if($_POST['draftmore'] == "1")
                {
                    //$delInvoice = $invoice_form_obj->ToggleStatusInvoices($_POST['document'], "4");
                    $delInvoice = $invoice_form_obj->ToggleStatusInvoices($_POST['document'], "0", false, $clientid);
                }
                else if($_POST['activate'] == "1")
                {
                    $activate_invoice = $invoice_form_obj->ToggleStatusInvoices($_POST['document'], "0", false, $clientid); // This was set wrong! 0 = unpaid :: Unbezahlt
                }
                else if($_POST['paidmore'] == "1")
                {
                    $delInvoice = $invoice_form_obj->ToggleStatusInvoices($_POST['document'], "2"); //fully paid with selected amount
                }
                else if($_POST['payment'] == "1")
                {
                    $payInvoice = $invoice_form_obj->SubmitPayment($_POST);
                }
                elseif($_POST['warningmore'] == "1")
                {
                    //var_dump($_POST['document']); exit;
                    $invoiceids_to_warn = implode(',', $_POST['document']);
                    
                    $this->forward('generatereminderinvoice', 'Invoicenew', null, array());
                    
                    return;
                    //$this->_redirect(APP_BASE . 'invoicenew/generatereminderinvoice?invoiceids='.$invoiceids_to_warn.'&invoicetable='.$_POST['warningmore_table'].'&invoicewarning='.$_POST['warningmore_type']);
                }
                //ISPC-2609 + ISPC-2000 Ancuta
                elseif($_POST['batch_print_more'] == "1")
                {
                    $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                    $params['batch_print'] = '1'; //enables batch print procedure
                    $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                    $params['get_pdf'] = '0'; //stops downloading single pdf
                    
                    //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                    if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                        
                        
                        
                    } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                        
                        $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                        
                        $print_job_data = array();
                        $print_job_data['clientid'] = $clientid;
                        $print_job_data['user'] = $userid;
                        $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                        $print_job_data['output_type'] = 'pdf';
                        $print_job_data['status'] = 'active';
                        $print_job_data['invoice_type'] = 'by_invoice';
                        $print_job_data['print_params'] = serialize($params);
                        $print_job_data['print_function'] = 'editinvoiceAction';
                        $print_job_data['print_controller'] = 'invoice';
                        
                        foreach($_POST['document'] as $k=>$inv_id){
                            $print_job_data['PrintJobsItems'][] = array(
                                'clientid'=>$print_job_data['clientid'],
                                'user'=>$print_job_data['user'],
                                'invoice_id'=>$inv_id,
                                'invoice_type'=>$print_job_data['invoice_type'],
                                'status'=>"new"
                            );
                        }
                        
                        $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                        $print_id = $PrintJobsBulk_obj->id;
                        
                        if($print_id){
                            $this->__StartPrintJobs();
                        }
                    }
                    
                    //ISPC-2609 Ancuta 07.09.2020
                    $msg="";
                    if($print_id){
                        $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                    }
                    $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=by_invoice'.$msg); //to avoid resubmission
                    // --
                    exit;
                    
                }
                
                
             $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=by_invoice'); //to avoid resubmission
             exit;
            }
        }
        
        
        
        
        //mark invoice as paid from invoices list link
        if(!empty($_REQUEST['mode']) && !empty($_REQUEST['iid']) && $_REQUEST['iid'] > '0')
        {
            if($_REQUEST['mode'] == "paid")
            {
                //mark as paid
                $invoice_pay_data = array();
                $invoice_pay_data['invoiceId'] = $_REQUEST['iid'];
                $invoice_pay_data['paymentAmount'] = '0.00';
                $invoice_pay_data['paymentComment'] = "";
                $invoice_pay_data['paymentDate'] = date('Y-m-d H:i:s', time());
                $invoice_pay_data['mark_as_paid'] = "1";

                if(in_array('invoice_type',$invoice_columns)){
                    $invoice_form_obj->submit_payment($invoice_type,$invoice_pay_data);
                } else{
                    $invoice_form_obj->submit_payment($invoice_pay_data);
                }
                $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type);
                exit;
            }
        }
        
        
        
        
        
        if($this->getRequest()->isPost())
        {
            if($_POST['draftmore'] == "1")
            {
                if(in_array('invoice_type',$invoice_columns)){
                    $invoice_form_obj->ToggleStatusInvoices($invoice_type,$_POST['document'], "2", $clientid);
                } else{
                    $invoice_form_obj->ToggleStatusInvoices($_POST['document'], "2", $clientid);
                }
            }
            elseif($_POST['delmore'] == "1" || $_POST['deletemore'] == "1")
            {
                if(in_array('invoice_type',$invoice_columns)){
                    $invoice_form_obj->delete_multiple_invoices($invoice_type,$_POST['document']);
                } else{
                    $invoice_form_obj->delete_multiple_invoices($_POST['document']);
                }
            }
            elseif($_POST['archive_invoices_more'] == "1")
            {
                if(in_array('invoice_type',$invoice_columns)){
                    $invoice_form_obj->archive_multiple_invoices($invoice_type,$_POST['document'], $clientid);
                } else{
                    $invoice_form_obj->archive_multiple_invoices($_POST['document'], $clientid);
                }
            }
            elseif($_POST['warningmore'] == "1")
            {
                $invoiceids_to_warn = implode(',', $_POST['document']);
                // include  something  
                $this->forward('generatereminderinvoice', null, null, array('oldaction' => 'invoiceclient'));
                
                return;
                
            }
            elseif(!empty($_POST['batch_print_more']))
            {
                
                
                switch($invoice_type)
                {
                    case "bayern_sapv_invoice":{
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            $invoicenewController->bayern_sapv_invoice($params);
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $this->clientid;
                            $print_job_data['user'] = $this->userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'bayern_sapv_invoice';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'bayern_sapv_invoice';
//                             $print_job_data['print_controller'] = $this->getRequest()->getControllerName();
                            $print_job_data['print_controller'] = 'invoicenew';
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                    
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                    }
                        break;
                        
                    case "sh_invoice":{
                            $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                            $params['batch_print'] = '1'; //enables batch print procedure
                            $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                            $params['get_pdf'] = '0'; //stops downloading single pdf
                            
                            //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                            if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                                $invoicenewController->anlage14_invoice($params);
                            }
                            elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 )
                            {
                                $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                                
                                $print_job_data = array();
                                $print_job_data['clientid'] = $this->clientid;
                                $print_job_data['user'] = $this->userid;
                                $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                                $print_job_data['output_type'] = 'pdf';
                                $print_job_data['status'] = 'active';
                                $print_job_data['invoice_type'] = 'sh_invoice';
                                $print_job_data['print_params'] = serialize($params);
                                $print_job_data['print_function'] = 'anlage14_invoice';
                                $print_job_data['print_controller'] = 'invoicenew';
                                
                                foreach($_POST['document'] as $k=>$inv_id){
                                    $print_job_data['PrintJobsItems'][] = array(
                                        'clientid'=>$print_job_data['clientid'],
                                        'user'=>$print_job_data['user'],
                                        'invoice_id'=>$inv_id,
                                        'invoice_type'=>$print_job_data['invoice_type'],
                                        'status'=>"new"
                                    );
                                }
                                
                                $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                                $print_id = $PrintJobsBulk_obj->id;
                                
                                if($print_id){
                                    $this->__StartPrintJobs();
                                }
                                
                            }
                     
                            $msg="";
                            if($print_id){
                                $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                            }
                            $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                    }
                    break;
                    
                    case "bre_kinder_invoice": // ISPC-2214
                    case "nr_invoice": // ISPC-2286
                    case "demstepcare_invoice": // ISPC-2461
                
                        $params['invoice_type'] = $invoice_type; //invoice_type
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            $invoicenewController->generate_systeminvoice($params);
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $this->clientid;
                            $print_job_data['user'] = $this->userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = $invoice_type;
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'generate_systeminvoice';
                            $print_job_data['print_controller'] = 'invoicenew';
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                            
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                        
                        break;

                        
                    case "rlp_invoice":{ // ISPC-2143
                           
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            $invoicenewController->generate_rlpinvoice($params);
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $this->clientid;
                            $print_job_data['user'] = $this->userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'rlp_invoice';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'generate_rlpinvoice';
                            $print_job_data['print_controller'] = 'invoicenew';
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                        
                        //ISPC-2609 Ancuta 07.09.2020
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                 
                       
                    }
                        break;
                        
                        
                    case "hospiz_invoice":{
                            $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                            $params['batch_print'] = '1'; //enables batch print procedure
                            $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                            $params['get_pdf'] = '0'; //stops downloading single pdf
                            
                            //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                            if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                                
                                $invoicenewController->hospizinvoice($params);
                                
                            } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                                
                                $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                                
                                $print_job_data = array();
                                $print_job_data['clientid'] = $this->clientid;
                                $print_job_data['user'] = $this->userid;
                                $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                                $print_job_data['output_type'] = 'pdf';
                                $print_job_data['status'] = 'active';
                                $print_job_data['invoice_type'] = 'hospiz_invoice';
                                $print_job_data['print_params'] = serialize($params);
                                $print_job_data['print_function'] = 'hospizinvoice';
                                $print_job_data['print_controller'] = 'invoicenew';
                                
                                foreach($_POST['document'] as $k=>$inv_id){
                                    $print_job_data['PrintJobsItems'][] = array(
                                        'clientid'=>$print_job_data['clientid'],
                                        'user'=>$print_job_data['user'],
                                        'invoice_id'=>$inv_id,
                                        'invoice_type'=>$print_job_data['invoice_type'],
                                        'status'=>"new"
                                    );
                                }
                                
                                $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                                $print_id = $PrintJobsBulk_obj->id;
                                
                                if($print_id){
                                    $this->__StartPrintJobs();
                                }
                            }
                       
                            //ISPC-2609 Ancuta 07.09.2020
                            $msg="";
                            if($print_id){
                                $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                            }
                            $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                            exit;
                        }
                        break;
                    
                        
                    case "nie_patient_invoice":{
                        
                        $phealthinsurance = new PatientHealthInsurance();
                        $client_details = new Client();
                        $boxes = new LettersTextBoxes();
                        $letter_boxes_details = $boxes->client_letter_boxes($clientid);
                        
                        $client_det = $client_details->getClientDataByid($clientid);
                        
                        
                        
                        $template_data = InvoiceTemplates::get_template($clientid, false, '1', 'nie_patient_invoice');
                        if($template_data)
                        {
                            $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                            $params['batch_print'] = '1'; //enables batch print procedure
                            $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                            $params['get_pdf'] = '0'; //stops downloading single pdf
                            
                            $invoiceController->healthins_print_invoice($params);
                        }
                        else
                        {
                            $invoices_ids = $_POST['document'];
                            //letter footer text
                            $letter_boxes_details = $boxes->client_letter_boxes($clientid);
                            
                            $invoices_data = $invoice_model_obj->get_invoices($invoices_ids);
                            
                            $ipids = array_unique($invoices_data['invoices_ipdis']);
                            
                            //patient HEALTH INSURANCE START
                            $healthinsu_multi_array = $phealthinsurance->get_multiple_patient_healthinsurance($ipids, true);
                            
                            $sql = 'e.epid,  e.ipid, p.ipid, p.birthd, p.admission_date, ';
                            $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
                            $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
                            $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
                            $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
                            $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
                            $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
                            
                            $patient = Doctrine_Query::create()
                            ->select($sql)
                            ->from('PatientMaster p')
                            ->whereIn("p.ipid", $ipids)
                            ->leftJoin("p.EpidIpidMapping e")
                            ->andWhere('e.clientid = ?', $clientid);
                            $patients_res = $patient->fetchArray();
                            
                            $patient_details = array();
                            foreach($patients_res as $kpat=>$vpat)
                            {
                                $patient_details[$vpat['ipid']] = $vpat;
                                $patient_details[$vpat['ipid']]['epid'] = $vpat['EpidIpidMapping']['epid'];
                            }
                            foreach($invoices_data['invoices_data'] as $invoice_data)
                            {
                                $invoice_data['health_insurance']['insurance_no'] = $healthinsu_multi_array[$invoice_data['ipid']]['insurance_no'];
                                
                                $invoice_data['client_details'] = $client_det[0];
                                
                                $invoice_data['patient_details'] = $patient_details[$invoice_data['ipid']];
                                $replacement_arr['%first_name'] = $invoice_data['patient_details']['first_name'];
                                $replacement_arr['%last_name'] = $invoice_data['patient_details']['last_name'];
                                $replacement_arr['%admission_date'] = date('d.m.Y', strtotime($invoice_data['patient_details']['admission_date']));
                                $replacement_arr['%client_name'] = $invoice_data['client_name'];
                                $replacement_arr['%invoice_start'] = date('d.m.Y', strtotime($invoice_data['invoice_start']));
                                $replacement_arr['%invoice_end'] = date('d.m.Y', strtotime($invoice_data['invoice_end']));
                                
                                //no money no honey => default gofer
                                if(strlen($invoice_data['header']) == 0)
                                {
                                    $header_default = '
							<b><u>Liquidation</u></b>
							<br />
							<br />
							O.g. <b>%first_name %last_name</b> erhält seit dem <b>%admission_date</b> SAPV-Leistungen.
							<br />
							<p>
								Wir erlauben uns, gemäß § 132 d Abs. 1 SGB V über die spezialisierte ambulante Palliativversorgung
								(SAPV) nach § 37 b SGB V und dem entsprechenden Vertrag zwischen dem
								Landeskrankenkassenverband und der %client_name für die Zeit vom %invoice_start
								bis zum  %invoice_end folgende Kosten in Rechnung zu stellen:
							</p>';
                                    
                                    
                                    $invoice_data['header'] = Pms_CommonData::str_replace_assoc($replacement_arr, $header_default);
                                }
                                
                                if(strlen($invoice_data['footer']) == 0)
                                {
                                    
                                    //ISPC:2035:: Please change the invoce Text for NIE_Diepholz
                                    if(!empty($letter_boxes_details)){
                                        $footer_default = $letter_boxes_details[0]['nd_invoice_footer'];
                                    } else {
                                        $footer_default = 'Wir bitten um Erstattung der angegebenen Summe innerhalb von <b>3 Wochen</b> auf unten stehende Kontoverbindung. Für Rückfragen stehen wir gerne zur Verfügung.
								<br />
								<br />
								Mit freundlichen Grüßen';
                                    }
                                    $invoice_data['footer'] = Pms_CommonData::str_replace_assoc($replacement_arr, $footer_default);
                                    
                                }
                                
                                $title = 'Rechnung';
                                $template = 'health_insurance_invoice.html';
                                
                                $invoice_type_special = "ND_patient";
                                
                                
                                // ISPC-2472 @Ancuta 07.11.2019
                                //$this->generateformPdf(3, $invoice_data, $title . "-" . $invoice_data['prefix'] . $invoice_data['invoice_number'] . "", $template, $invoice_type_special);
                                
                                $pdf_file_name =  $title . "-" . $invoice_data['prefix'] . $invoice_data['invoice_number'] . "";
                                
                                $invoice_number_full="";
                                $invoice_number_full .=  (strlen($invoice_data['prefix']) > 0) ? $invoice_data['prefix'] : '';
                                $invoice_number_full .= $invoice_data['invoice_number'];
                                
                                if(strlen($invoice_number_full) > 0 ){
                                    //$pdf_file_name = $invoice_number_full;
                                    $inv_names[] = $invoice_number_full;
                                }
                                //print_r($invoice_data); exit;
                                //$this->generateformPdf(3, $invoice_data, $pdf_file_name, $template, $invoice_type_special);
                                //save pdf as file
                                $files[] = $invoiceController->generate_joined_files_pdf('4', $invoice_data, $pdf_file_name, $template, $invoice_type_special);
                                
                                // --
                                
                            }
                            
                            $source = 'HiInvoice';
                            $invoiceController->join_pdfs_new($files, $inv_names ,$source);
                            //$this->_redirect(APP_BASE . 'invoice/healthinsuranceinvoices');
                            exit;
                        }
                    }
                    break;
                 
                    
                    
                    case "bw_sapv_invoice_new":{
                        
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            $invoicenewController->bwsapvsinvoice($params);
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $this->clientid;
                            $print_job_data['user'] = $this->userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'bw_sapv_invoice_new';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'bwsapvsinvoice';
                            $print_job_data['print_controller'] = 'invoicenew';
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                        
                     
                    
                        //ISPC-2609 Ancuta 07.09.2020
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                        
                    }
                    break;
                    
                    
                    case "he_invoice":{
                        
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $clientid;
                            $print_job_data['user'] = $userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'he_invoice';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'editheinvoiceAction';
                            $print_job_data['print_controller'] = 'invoice';
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                        
                        //ISPC-2609 Ancuta 07.09.2020
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                    }
                    break;
                    
                    
                    case "rpinvoice":{
                        
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $clientid;
                            $print_job_data['user'] = $userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'rpinvoice';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'editrpinvoiceAction';
                            $print_job_data['print_controller'] = "invoice";
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                        //ISPC-2609 Ancuta 07.09.2020
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                    }
                    break;
                    
                    case "bre_sapv_invoice":{
                        
                        
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $clientid;
                            $print_job_data['user'] = $userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'bre_sapv_invoice';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'bresapvperformanceAction';
                            $print_job_data['print_controller'] = "patientform";
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                        //ISPC-2609 Ancuta 07.09.2020
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                            
                    }
                    break;
                    
                    case "bre_hospiz_sapv_invoice":{
                        
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $clientid;
                            $print_job_data['user'] = $userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'bre_hospiz_sapv_invoice';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'editbrehospizsapvinvoiceAction';
                            $print_job_data['print_controller'] = "invoice";
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                        
                        //ISPC-2609 Ancuta 07.09.2020
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                            
                    }
                    break;
                    
                    case "bw_medipumps_invoice":{
                        
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        $invoicenewController->bwmedipumpsinvoice($params);
                            
                    }
                    break;
                    
                    
                    case "bw_sgbv_invoice":{
                   
                        
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            
                            
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $clientid;
                            $print_job_data['user'] = $userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'bw_sgbv_invoice';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'socialcoderecordAction';
                            $print_job_data['print_controller'] = 'invoice';
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                            
                        
                        
                        //ISPC-2609 Ancuta 07.09.2020
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                    }
                    break;
                    
                    case "bw_sgbxi_invoice":{
                        
                        $params['invoices'] = $_POST['document']; //contains invoices ids to be printed
                        $params['batch_print'] = '1'; //enables batch print procedure
                        $params['only_pdf'] = '1'; //stops invoice calculation(from system data)
                        $params['get_pdf'] = '0'; //stops downloading single pdf
                        
                        //ISPC-2609 Ancuta 27.08-30.09.2020 :: Do not print, add to print jobs
                        if( !isset($this->user_print_jobs) || $this->user_print_jobs == 0 ){
                            
                            
                        } elseif( isset($this->user_print_jobs) && $this->user_print_jobs == 1 ){
                            
                            $params['print_job'] = '1'; //stop downloading files, just save them //ISPC-2609 Ancuta 01.09.2020
                            
                            $print_job_data = array();
                            $print_job_data['clientid'] = $clientid;
                            $print_job_data['user'] = $userid;
                            $print_job_data['page'] =  APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName();;
                            $print_job_data['output_type'] = 'pdf';
                            $print_job_data['status'] = 'active';
                            $print_job_data['invoice_type'] = 'bw_sgbxi_invoice';
                            $print_job_data['print_params'] = serialize($params);
                            $print_job_data['print_function'] = 'sgbxiinvoiceAction';
                            $print_job_data['print_controller'] = 'invoice';
                            
                            foreach($_POST['document'] as $k=>$inv_id){
                                $print_job_data['PrintJobsItems'][] = array(
                                    'clientid'=>$print_job_data['clientid'],
                                    'user'=>$print_job_data['user'],
                                    'invoice_id'=>$inv_id,
                                    'invoice_type'=>$print_job_data['invoice_type'],
                                    'status'=>"new"
                                );
                            }
                            
                            $PrintJobsBulk_obj = PrintJobsBulkTable::getInstance()->findOrCreateOneBy('id', null, $print_job_data);
                            $print_id = $PrintJobsBulk_obj->id;
                            
                            if($print_id){
                                $this->__StartPrintJobs();
                            }
                        }
                        
                        //ISPC-2609 Ancuta 07.09.2020
                        $msg="";
                        if($print_id){
                            $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
                        }
                        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg); //to avoid resubmission
                        exit;
                        
                    }
                    break;
                    
                }
            }
            else if(!empty($_POST['invoiceId']))
            {
                $post = $_POST;
                $post["mark_as_paid"] = "0";
                if(in_array('invoice_type',$invoice_columns)){
                    $invoice_form_obj->submit_payment($invoice_type,$post);
                } else{
                    $invoice_form_obj->submit_payment($post);
                }
            }
            
            $msg="";
            if($print_id){
                $msg = '&flg=suc&msg=inform_print_job_created&jobid='.$print_id;
            }
            
            
            
            $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.$msg);
            exit;
        }
        
        
        if($_REQUEST['mode'] == 'setstorno')
        {
            if(is_numeric($_REQUEST['inv_id']) && strlen($_REQUEST['inv_id']) > '0')
            {
                $invoiceid = $_REQUEST['inv_id'];
            }
            else
            {
                $invoiceid = '0';
            }
            
            if($invoiceid > '0')
            {
                if(in_array('invoice_type',$invoice_columns)){
                    $invoice_model_obj->create_storno_invoice($invoice_type,$invoiceid);
                } else{
                    $invoice_model_obj->create_storno_invoice($invoiceid);
                }
                
                $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.'&flg=suc&msg=storno_created');
                exit;
            }
        }
        
        
        if($_REQUEST['mode'] == 'delete' && $_REQUEST['invoiceid'])
        {
            if(in_array('invoice_type',$invoice_columns)){
                $delete_invoice = $invoice_form_obj->delete_invoice($invoice_type,$_REQUEST['invoiceid']);
            } else{
                $delete_invoice = $invoice_form_obj->delete_invoice($_REQUEST['invoiceid']);
            }
            
            if($delete_invoice)
            {
                $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.'&flg=suc&msg=invoice_deleted');
                exit;
            }
            else
            {
                $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoice_type.'&flg=delerr');
                exit;
            }
        }
        
    }
    
    public function fetchinvoicesAction()
    {
        $this->_helper->layout->setLayout('layout_ajax');
        $hidemagic = Zend_Registry::get('hidemagic');
        
        //general data
        $patientmaster = new PatientMaster();
        $users = new User();
        $client_details = new Client();
        $clientid = $this->clientid;
        
        $warnings = new RemindersInvoice();
        $modules = new Modules();
        
        
        //get allowed client invoices
        if(!empty($_REQUEST['invoice_type'])){// ISPC-2312 Ancuta 06.12.2020
            $client_allowed_invoice = array();
            $invoice_type = $client_allowed_invoice[0]= $_REQUEST['invoice_type'];
            $this->view->invoice_type = $invoice_type;
        } else{
            $client_allowed_invoice = ClientInvoicePermissions::get_client_allowed_invoice($clientid);
            $this->view->invoice_type = $invoice_type = $client_allowed_invoice[0];
        }

        // get all info based on requested invoice- 
        // form, models , items. linlks 
        $system_invoice_details = Pms_CommonData::clients_invoices_details();
        if(empty($system_invoice_details[$invoice_type])){
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }
        
        // ExTRA CHECK - if no model was added 
        if(empty($system_invoice_details[$invoice_type]['models']['invoice'])){
            return; 
        }
        
        
        $invoice_model = $system_invoice_details[$invoice_type]['models']['invoice'];
        $invoice_form = $system_invoice_details[$invoice_type]['forms']['invoice'];
        $invoice_payment_model = $system_invoice_details[$invoice_type]['models']['payment'];
        $invoice_payment_obj = new $invoice_payment_model();
        
        
        $invoice_edit_link = $system_invoice_details[$invoice_type]['links']['edit'];
        $this->view->invoice_edit_link = $invoice_edit_link;
        $invoice_print_link = $system_invoice_details[$invoice_type]['links']['print'];
        $this->view->invoice_print_link = $invoice_print_link;
        $invoice_print_storno_link = $system_invoice_details[$invoice_type]['links']['print_storno'];
        $this->view->invoice_print_storno_link = $invoice_print_storno_link;
        
        
        $invoice_model_obj = null;
        $invoice_model_obj = new $invoice_model();
        
        
        $invoice_columns  = array();
        $invoice_columns = array_keys($invoice_model_obj->getTable()->getColumns());
        
        
        $client_column = 'client';
        if(in_array('client',$invoice_columns)){
            $client_column = 'client';
        }
        if(in_array('clientid',$invoice_columns)){
            $client_column = 'clientid';
        }
        $sql_inv = '*';
        $sql_inv .=", IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort, concat(prefix,invoice_number) as full_invoice_number_sort";
        if($invoice_type == 'by_invoice'){
        $sql_inv = '*,';
        $sql_inv .= ' id,"0" as sapv,"0" as user, clientid as client,  ipid, prefix, 
                  rnummer AS invoice_number, 
                  CONCAT(prefix, rnummer) AS full_invoice_number_sort, 
                  rnummer as invoice_nr, 
                  invoiceTotal as invoice_total, isdelete, record_id, storno, create_date, create_date as invoice_date, create_date as invoice_start,  create_date as invoice_end,
                  IF(completedDate = "0000-00-00 00:00:00", create_date, IF(completedDate = "1970-01-01 01:00:00", create_date, completedDate)) as completed_date,
                  IF(completedDate = "0000-00-00 00:00:00", create_date, IF(completedDate = "1970-01-01 01:00:00", create_date, completedDate)) as completed_date_sort
						    ,create_date as inv_start_date
						    ,create_date as inv_end_date
						    ';
        }
        
//         $invoicesd = Doctrine_Query::create()
//         ->select("*, IF(completedDate = '0000-00-00 00:00:00', create_date, IF(completedDate = '1970-01-01 01:00:00', create_date, completedDate)) as completed_date_sort")
//         ->from('ClientInvoices ci')
//         ->Where("clientid='" . $clientid . "'")
//         ->andWhere('isDelete = 0')
//         ->fetchArray();
//         dd($invoicesd);
        
        if($modules->checkModulePrivileges("170", $clientid))
        {
            $this->view->create_bulk_warnings = "1";
        }
        else
        {
            $this->view->create_bulk_warnings = "0";
        }
        
        
        $limit = 50;
        $this->view->limit = $limit;
        $filters = array();
        
        
        //################### 
        //Get storno invoices 
        $storno_invoices_q = Doctrine_Query::create()
        ->select($sql_inv)
        ->from($invoice_model)
        ->where('`'.$client_column.'` = ?', $clientid)
        ->andWhere('storno = 1');
        if(in_array('invoice_type',$invoice_columns)){
            $storno_invoices_q->andWhere('invoice_type = ?',$invoice_type);
        }
        $storno_invoices_q->andWhereIn("isdelete",array("0"));
        $storno_invoices_array = $storno_invoices_q->fetchArray();
        
        $storno_ids = array();
        $storno_ids_str="";
        foreach($storno_invoices_array as $k => $st)
        {
            $storno_ids[] = $st['record_id'];
            $storno_ids_str .= '"' . $st['record_id'] . '",';
        }
        
        if( strlen($storno_ids_str) > 0 )
        {
            $storno_ids_str = substr($storno_ids_str, 0, -1);
            $storno_ids_str_sql = " AND id NOT IN (" . $storno_ids_str . ")";
        } else{
            $storno_ids_str_sql = "";
        }
        
        
        //###################
        // get client data - Check due date
        $client_details = $client_details->getClientDataByid($clientid);
        
        $invoice_due_days = $client_details[0]['invoice_due_days'];
        $plus_due_days = '+' . $invoice_due_days . ' days';
        $this->view->plus_due_days = $plus_due_days;
        
        //###################
        //process tabs - create tabs  and filters 
        $filters['invoice_system_search'] = '';
        if($invoice_type != "by_invoice"){
            switch($_REQUEST['f_status'])
            {
                case 'draft':
                    $filters['invoice'] = ' AND status ="1" AND isdelete=0 AND isarchived ="0"';
                    break;
                    
                case 'unpaid':
                    $filters['invoice'] = ' AND (status = "2" OR status = "5")  AND storno = 0 '.$storno_ids_str_sql.' AND isdelete = 0 AND isarchived ="0"';
                    
                    break;
                    
                case 'paid':
                    $filters['invoice'] = ' AND status="3"  AND storno = 0  '.$storno_ids_str_sql.'  AND isdelete=0 AND isarchived ="0"';
                    break;
                    
                case 'deleted':
                    $filters['invoice'] = ' AND (status="4" OR isdelete="1") AND isarchived ="0"';
                    break;
                    
                case 'overdue':
                    $filters['invoice'] = ' AND (status = "2" OR status = "5")  AND storno = 0 '.$storno_ids_str_sql.'   AND   DATE(NOW()) >  DATE_ADD(DATE(completed_date), INTERVAL ' . $invoice_due_days . ' DAY)   AND isdelete=0 AND isarchived ="0"';
                    break;
                    
                case 'all':
                    $filters['invoice'] = ' AND isarchived ="0"';
                    break;
                case 'archived':
                    $filters['invoice'] = ' AND isarchived ="1" AND isdelete=0';
                    break;
                    
                default: // unpaid- open
                    $filters['invoice'] = ' AND (status = "2" OR status = "5")   AND storno = 0 '.$storno_ids_str_sql.'  AND isdelete = 0 AND isarchived ="0"';
                    break;
            }
        } else{
            switch($_REQUEST['f_status'])
            {
                case 'draft':
                    // if($vInvoice['status'] == "4")
                    $filters['invoice'] = ' AND status ="4" AND isdelete=0 AND isarchived ="0"';
                    break;
                    
                case 'unpaid':
                    //($vInvoice['storno'] == 0 && !in_array($vInvoice['status'], array("2", "3", "4")) && ($vInvoice['status'] == 0 || $vInvoice['status'] == 1 || ($vInvoice['invoiceTotal'] != $vInvoice['paidAmount'] && $vInvoice['invoiceTotal'] > $vInvoice['paidAmount'])))
                    //$filters['invoice'] = ' AND (status = "0" OR status = "1" )  AND status != "2" AND status != "3" AND status != "4"  AND storno = 0 '.$storno_ids_str_sql.' AND isdelete = 0 AND isarchived ="0"';
                    $filters['invoice'] = ' AND status in ("0","1")  AND status not in ("2","3","4")  AND storno = 0 '.$storno_ids_str_sql.' AND isdelete = 0 AND isarchived ="0"';
                    break;
                    
                case 'paid':
                    //($vInvoice['storno'] == 0 && $vInvoice['paidDate'] != "0000-00-00 00:00:00" && $vInvoice['status'] == 2)
                    $filters['invoice'] = ' AND status="2"  AND storno = 0  '.$storno_ids_str_sql.'  AND isdelete=0 AND isarchived ="0"';
                    break;
                    
                case 'deleted':
                    //if($vInvoice['status'] == "3")
                    $filters['invoice'] = ' AND status="3" AND isarchived ="0"';
                    break;
                    
                case 'overdue':
                    //if($vInvoice['storno'] == 0 && !in_array($vInvoice['status'], array("3", "4")) && ( (strtotime($vInvoice['dueDate']) < strtotime("now") && $vInvoice['paidDate'] == "0000-00-00 00:00:00") || (strtotime($vInvoice['paidDate']) > strtotime($vInvoice['dueDate']) && $vInvoice['paidDate'] != "0000-00-00 00:00:00")))
                    //$filters['invoice'] = ' AND status != "3" AND status != "4"  AND storno = 0 '.$storno_ids_str_sql.'   AND   DATE(NOW()) >  DATE_ADD(DATE(completed_date), INTERVAL ' . $invoice_due_days . ' DAY)   AND isdelete=0 AND isarchived ="0"';
                    $filters['invoice'] = ' AND status not in ("3","4")  AND storno = 0 '.$storno_ids_str_sql.'   AND   DATE(NOW()) >  DATE_ADD(DATE(paidDate), INTERVAL ' . $invoice_due_days . ' DAY) AND paidDate !="0000-00-00 00:00:00"  AND isdelete=0 AND isarchived ="0"';
                    break;
                    
                case 'all':
                    $filters['invoice'] = ' AND isarchived ="0"';
                    break;
                case 'archived':
                    $filters['invoice'] = ' AND isarchived ="1" AND isdelete=0';
                    break;
                    
                default: // unpaid- open
                    $filters['invoice'] = ' AND status in ("0","1")  AND status not in ("2","3","4")  AND storno = 0 '.$storno_ids_str_sql.' AND isdelete = 0 AND isarchived ="0"';
                    break;
            }
            
        }
  
 
        if(!empty($_REQUEST['last_name']))
        {
            $filters['patient_master'] = ' AND (CONCAT(AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '"), " ", e.epid) LIKE "%' . addslashes($_REQUEST['last_name']) . '%")';
        }
        
        if(!empty($_REQUEST['first_name']))
        {
            $filters['patient_master'] .= ' AND (CONCAT(AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '"), " ", e.epid) LIKE "%' . addslashes($_REQUEST['first_name']) . '%")';
        }
        
        if(!empty($_REQUEST['epid']))
        {
            $filters['patient_master'] .= ' AND ( e.epid LIKE "%' . addslashes($_REQUEST['epid']) . '%")';
        }
        
        if(!empty($_REQUEST['rnummer']))
        {
            $filters['invoice'] .= ' AND ( LOWER(CONCAT(`prefix`,CAST(  `invoice_number` AS CHAR ))) LIKE "%' . addslashes(strtolower($_REQUEST['rnummer'])) . '%")';
            $filters['invoice_system_search'] .= ' AND ( LOWER(CONCAT(`prefix`,CAST(  `invoice_number` AS CHAR ))) LIKE "%' . addslashes(strtolower($_REQUEST['rnummer'])) . '%")';
        }
        if(!empty($_REQUEST['selected_month']) && $_REQUEST['selected_month'] != '99999999')
        {
            $filters['invoice'] .= ' AND MONTH(DATE(invoice_start)) = MONTH("' . $_REQUEST['selected_month'] . '-01") AND YEAR(DATE(invoice_start)) = YEAR("' . $_REQUEST['selected_month'] . '-01")';
            $filters['invoice_system_search'] .= ' AND MONTH(DATE(invoice_start)) = MONTH("' . $_REQUEST['selected_month'] . '-01") AND YEAR(DATE(invoice_start)) = YEAR("' . $_REQUEST['selected_month'] . '-01")';
        }
        
        //###################
        //get invoice patients
        $sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
        $sql .= "CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1)  as firstname,";
        $sql .= "CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1)  as middlename,";
        $sql .= "CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1)  as lastname,";
        $sql .= "CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1)  as title,";
        $sql .= "CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1)  as salutation,";
        
        // if super admin check if patient is visible or not
        if($this->usertype == 'SA')
        {
            $sql = "e.ipid,e.epid,p.birthd,p.admission_date,p.change_date,p.last_update,p.isadminvisible,p.traffic_status,p.isdischarged,p.isarchived,p.isstandby,p.isstandbydelete,";
            $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as firstname, ";
            $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middlename, ";
            $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as lastname, ";
            $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
            $sql .= "IF(p.isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
        }
        
        
        //###################
        //filter patients name/surname/epid
        $f_patients_ipids = array();
        $f_patient = Doctrine_Query::create()
        ->select($sql)
        ->from('PatientMaster p')
        ->leftJoin("p.EpidIpidMapping e")
        ->where('p.isdelete=0')
        ->andWhere('e.clientid = ' . $clientid . $filters['patient_master']);
        $f_patients_res = $f_patient->fetchArray();
        
        $patient_details = array();
        foreach($f_patients_res as $k_f_pat_res => $v_f_pat_res)
        {
            $f_patients_ipids[] = $v_f_pat_res['EpidIpidMapping']['ipid'];
            $patient_details[$v_f_pat_res['EpidIpidMapping']['ipid']] = $v_f_pat_res;
        }
        

        //################### 
        //all invoices for counting
        $invoices_counting = Doctrine_Query::create();
        $invoices_counting->select($sql_inv);
        $invoices_counting->from($invoice_model);
        $invoices_counting->where($client_column."=? ",$clientid);
        if(in_array('ipid',$invoice_columns)){
            $invoices_counting->andWhereIn('ipid', $f_patients_ipids);
        }
        if(in_array('invoice_type',$invoice_columns)){
            $invoices_counting->andWhere("invoice_type= ? ",$invoice_type);
        }
        $invoices_counting->andWhere($client_column." ='" . $clientid . "'" . $filters['invoice_system_search']);
        $inv2count = $invoices_counting->fetchArray();

        $count_invoices = array();
        $status_count_invoices = array();
 
        if($invoice_type == "by_invoice"){
            foreach($inv2count as $kInvoice => $vInvoice)
            {
                // OPEN - unpaid; partiay paid
                if($vInvoice['storno'] == 0 && !in_array($vInvoice['status'], array("2", "3", "4")) && ($vInvoice['status'] == 0 || $vInvoice['status'] == 1 || ($vInvoice['invoiceTotal'] != $vInvoice['paidAmount'] && $vInvoice['invoiceTotal'] > $vInvoice['paidAmount'])))
                {
                    $status_count_invoices['unpaid'][] = $vInvoice;
                }
                
                //PAID
                if($vInvoice['storno'] == 0 && $vInvoice['paidDate'] != "0000-00-00 00:00:00" && $vInvoice['status'] == 2 && $vInvoice['isdelete'] == 0  && $vInvoice['isarchived'] == 0 && !in_array($vInvoice['id'],$storno_ids))
                {
                    $status_count_invoices['paid'][] = $vInvoice;
                }
                
                //OVERDUE
                if(!in_array($vInvoice['status'], array("3", "4")) && $vInvoice['storno'] == 0 && $vInvoice['isdelete'] == 0  && $vInvoice['isarchived'] == 0
                    && strtotime(date('Y-m-d', time())) > strtotime(date('Y-m-d', strtotime($plus_due_days, strtotime($vInvoice['dueDate']))))
                    && strtotime($vInvoice['dueDate']) < strtotime("now")
                    && $vInvoice['paidDate'] == "0000-00-00 00:00:00"
                    )
                {
                    $status_count_invoices['overdue'][] = $vInvoice;
                }
                
                // DRAFT
                if($vInvoice['status'] == "4")
                {
                    $status_count_invoices['draft'][] = $vInvoice;
                }
                //CANCELED
                if($vInvoice['status'] == "3")
                {
                    $status_count_invoices['deleted'][] = $vInvoice;
                }
                
                //ALL
                if($vInvoice['isarchived'] == "0")
                {
                    $status_count_invoices['all'][] = $vInvoice;
                }
                //ALL
                if($vInvoice['isarchived'] == "1")
                {
                    $status_count_invoices['archived'][] = $vInvoice;
                }
                
            }
            
        }
        else
        {
            foreach($inv2count as $k_inv2count => $v_inv2count)
            {
                $count_invoices[$v_inv2count['status']][] = '1';
                
                if($v_inv2count['status'] == "1" && $v_inv2count['isdelete'] == "0" && $v_inv2count['isarchived'] == "0")
                {
                    $status_count_invoices["draft"][] = '1';
                }
                
                if(($v_inv2count['status'] == "2" || $v_inv2count['status'] == "5") && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'], $storno_ids) && $v_inv2count['isdelete'] == "0" && $v_inv2count['isarchived'] == "0")
                {
                    $status_count_invoices["unpaid"][] = '1';
                }
                
                if($v_inv2count['status'] == "3" && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'], $storno_ids) && $v_inv2count['isdelete'] == "0" && $v_inv2count['isarchived'] == "0")
                {
                    $status_count_invoices["paid"][] = '1';
                }
                
                if($v_inv2count['status'] == "4" || $v_inv2count['isdelete'] == "1" && $v_inv2count['isarchived'] == "0")
                {
                    $status_count_invoices["deleted"][] = '1';
                }
                
                if(($v_inv2count['status'] == "2" || $v_inv2count['status'] == "5") && $v_inv2count['storno'] == "0" && !in_array($v_inv2count['id'], $storno_ids) && $v_inv2count['isdelete'] == "0" && strtotime(date('Y-m-d', time())) > strtotime(date('Y-m-d', strtotime($plus_due_days, strtotime($v_inv2count['completed_date'])))) && $v_inv2count['isarchived'] == "0")
                {
                    $status_count_invoices["overdue"][] = '1';
                }
                
                if($v_inv2count['isarchived'] == "0")
                {
                    $status_count_invoices["all"][] = '1';
                }
                
                if($v_inv2count['isarchived'] == "1")
                {
                    $status_count_invoices["archived"][] = '1';
                }
            }
        }
        

        //###################
        //deleted_invoices
        $del_inv2count_Q = Doctrine_Query::create()
        ->select($sql_inv)
        ->from($invoice_model)
        ->where($client_column." = ? ", $clientid );
        $del_inv2count_Q->andWhereIn('ipid', $f_patients_ipids);
        if(in_array('invoice_type',$invoice_columns)){
            $del_inv2count_Q->andWhere("invoice_type= ? ",$invoice_type);
        }
        $del_inv2count_Q->andWhere(" 1 " . $filters['invoice'])
        ->andWhere("isdelete=1 or status=4");
        $del_inv2count =$del_inv2count_Q->fetchArray();

        
        $counted_del_inv = array();
        foreach($del_inv2count as $k_del_inv => $v_del_inv)
        {
            $counted_del_inv[$v_del_inv['status']][] = '1';
        }
        
        
        //###################
        //filter invoices status/invoice_number/amount
        $invoices_nl = Doctrine_Query::create()
        ->select($sql_inv)
        ->from($invoice_model);
        $invoices_nl->where($client_column." = ? ", $clientid );
        $invoices_nl->andWhereIn('ipid', $f_patients_ipids);
        if(in_array('invoice_type',$invoice_columns)){
            $invoices_nl->andWhere("invoice_type= ? ",$invoice_type);
        }
        $invoices_nl->andWhere(" 1 " . $filters['invoice']);
        $invoices_nl->andWhereIn("isdelete",array("0","1"));
        $invoices_no_limit = $invoices_nl->fetchArray();
        
        $invoice_ipids  = array();
        foreach($invoices_no_limit as $k_nl_inv => $v_nl_inv)
        {
            $invoice_ipids[] = $v_nl_inv['ipid'];
        }
        
        
        //###################
        if(!empty($_REQUEST['page']) && is_numeric($_REQUEST['page']))
        {
            $current_page = $_REQUEST['page'];
        }
        else
        {
            $current_page = 1;
        }
        
        if($_REQUEST['sort'] == 'asc')
        {
            $sort = 'asc';
        }
        else
        {
            $sort = 'desc';
        }
        
        switch($_REQUEST['ord'])
        {
            
            case 'id':
                $orderby = 'id ' . $sort;
                break;
                
            case 'ln':
                $orderby = 'epid ' . $sort;
                break;
                
            case 'nr':
                //$orderby = 'invoice_number ' . $sort;
                $orderby = 'full_invoice_number_sort ' . $sort; //TODO-2073 ISPC: Invoices sorting not correct :: @Ancuta 22.01.2019
                break;
                
            case 'date':
                $orderby = 'change_date, create_date ' . $sort;
                break;
                
            case 'amnt':
                $orderby = 'invoice_total ' . $sort;
                break;
            case 'invoice_date':
                $orderby = 'completed_date_sort ' . $sort;
                break;
                
            default:
                //$orderby = 'id DESC'; // ISPC-2220: change_order_invoice :: @Ancuta 30.07.2018 [NEW]
                $orderby = 'full_invoice_number_sort DESC'; //TODO-2073 ISPC: Invoices sorting not correct :: @Ancuta 22.01.2019
                break;
        }
        
        
        if(!empty($invoice_ipids)){
            $invoices = Doctrine_Query::create()
//             ->select($sql_inv. ", IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort, concat(prefix,invoice_number) as full_invoice_number_sort")
            ->select($sql_inv)
            ->from($invoice_model)
            ->where($client_column." ='" . $clientid . "'" . $filters['invoice']);
            
            if(in_array('invoice_type',$invoice_columns)){
                $invoices->andWhere("invoice_type= ? ",$invoice_type);
            }
            if(in_array('ipid',$invoice_columns)){
                $invoices->andWhereIn("ipid",$invoice_ipids);
            }
            $invoices->orderby($orderby);
            $invoices->offset(($current_page - 1) * $limit);
            $invoices->limit($limit);
            $invoicelimit = $invoices->fetchArray();
        }
        
        
        $invoice_ids  = array();
        $invoice_uids  = array();
        foreach($invoicelimit as $k_il => $v_il)
        {
            $invoice_ids[] = $v_il['id'];
            $invoice_uids[] = $v_il['create_user'];
            $invoice_uids[] = $v_il['change_user'];
        }
        
        
        //count tabs contents
        $invoice_tabs = array('unpaid', 'paid', 'draft', 'deleted', 'overdue', 'all', 'archived');
        
        $counted = array();
        foreach($invoice_tabs as $tab)
        {
            $counted[$tab] += count($status_count_invoices[$tab]);
        }
        
        $invoice_uids = array_values(array_unique($invoice_uids));
        if(!empty($invoice_uids)){
            $users_details = $users->getMultipleUserDetails($invoice_uids);
        }
        

        
        // Some donot have multiple params
        if(in_array('invoice_type',$invoice_columns)){
            $invoice_payments = $invoice_payment_obj->getInvoicesPaymentsSum($invoice_type, $invoice_ids);
        } else{
            $invoice_payments = $invoice_payment_obj->getInvoicesPaymentsSum($invoice_ids);
        }
        
        
        $no_invoices = sizeof($invoices_no_limit);
        $no_pages = ceil($no_invoices / $limit);
        
        $all_warnings = $warnings->get_reminders($invoice_ids, $invoice_type, $clientid);
        
        foreach ($invoicelimit as &$row) {
            if (isset($all_warnings[$row['id']])) {
                $row['InvoiceWarnings'] = $all_warnings[$row['id']];
            }
        }
        
        
        if($invoice_type == "nr_invoice"){
            
            include 'InvoicenewController.php';
            $invoicenewController = new InvoicenewController($this->_request, $this->_response);
            
                                        /*
                                         * TODO:
                                         * - group ipids by start_active->end_active
                                         * - and fetch visit_number in batches of ipids, in a fn __get_patientSSSS_visit_number
                                         */
                                            
                                        foreach ($invoicelimit as &$one_invoice) {
                                            if($one_invoice['status'] == '2') {
                                                $pv = $invoicenewController->__get_patient_visit_number($one_invoice['ipid'], $one_invoice['start_active'], $one_invoice['end_active']);
                                                if ( ! empty($pv['visit_number']))
                                                    $one_invoice['__patient_PV1_VisitNumber'] = $pv['visit_number'];
                                            }
                                        }
                                        
                                        //find if hl7-ft1 was sent for any of them ... we could filter by invoice.status=2 cause we only display on those invoices`
                                        $Hl7_TransmitedInvoices = [];
                                        $Hl7_transmited = Hl7MessagesSentTable::getInstance()->findTransmitedInvoices('InvoiceSystem', array_column($invoicelimit, 'id'));
                                        //leave like this and not indexBy cause we may change to have multiple
                                        array_walk($Hl7_transmited, function($item) use(&$Hl7_TransmitedInvoices){$Hl7_TransmitedInvoices[$item['parent_table_id']] = $item;});
                                        
                                        $this->view->Hl7_TransmitedInvoices = $Hl7_TransmitedInvoices;
                                        
                                        
                                        //ISPC-2459 Ancuta 26.11.2019
                                        $this->view->allow_hl7_activation = 0 ; //Ancuta 12.05.2020
                                        $activation_approved = 0;
                                        
                                        if($this->userid == "338" || $this->userid == "293" ){
                                            $activation_approved = 1;
                                        }
                                        // get data foreach invoice - check movement numbers
                                        if ($activation_approved == 1 && !empty($invoicelimit)){
                                            
                                            $this->view->allow_hl7_activation = 1 ; //Ancuta 12.05.2020
                                            
                                            $hl7_movementNrs_check= $invoicenewController->__invoicesnew_hl7_check_movementNumbers($invoicelimit);
                                            
                                            foreach ($invoicelimit as &$one_invoice) {
                                                $no_movement_days = $hl7_movementNrs_check['data'][$one_invoice['ipid']][$one_invoice['id']]['no_movementNr_days'];
                                                if ( ! empty($no_movement_days)){
                                                    $one_invoice['__invoice_NO_movement_numbers'] = implode(", ",$no_movement_days);
                                                }
                                            }
                                        }
                                        //--
        }
        
        $this->view->storned_invoces = $invoice_model::get_storned_invoices($invoice_type,$clientid);
        
        $this->view->invoicelist = $invoicelimit;
        $this->view->user_details = $users_details;
        $this->view->patient_details = $patient_details;
        $this->view->invoice_payments = $invoice_payments;
        $this->view->current_page = $current_page;
        $this->view->no_pages = $no_pages;
        $this->view->no_invoices = $no_invoices;
        $this->view->orderby = $_REQUEST['ord'];
        $this->view->sort = $_REQUEST['sort'];
        $this->view->counted = $counted;
    }
 
    
    public function listpaymentsAction()
    {
        
        $this->_helper->viewRenderer->setNoRender();
        $clientid = $this->clientid;
        
        $user = new User();
        
        //get allowed client invoices
        // if multiple - chose first one - and allow user to select whic invoices to see
        $client_allowed_invoices = ClientInvoiceMultiplePermissions::get_client_allowed_invoices($clientid);
        
        if(count($client_allowed_invoices) == "1"){
            $invoice_type = end($client_allowed_invoices);
            
        } else{
            if(strlen($_REQUEST['invoice_type']) == '0')
            {
                $invoice_type = end($client_allowed_invoices);
            }
            else
            {
                $invoice_type = $_REQUEST['invoice_type'];
            }
        }
        if(empty($invoice_type)){
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }
        
        
        $system_invoice_details = Pms_CommonData::clients_invoices_details();
        if(empty($system_invoice_details[$invoice_type])){
            $this->_redirect(APP_BASE . "error/previlege");
            exit;
        }
        
        // ExTRA CHECK - if no model was added
        if(empty($system_invoice_details[$invoice_type]['models']['invoice'])){
            echo "check data";
            return;
        }
        
        $invoice_model = $system_invoice_details[$invoice_type]['models']['invoice'];
        $invoice_form = $system_invoice_details[$invoice_type]['forms']['invoice'];
        if($invoice_form){
            $invoice_form_obj = new $invoice_form();
        }
        $invoice_payment_model = $system_invoice_details[$invoice_type]['models']['payment'];
        $invoice_payment_obj = new $invoice_payment_model();
        
        $invoice_model_obj = null;
        $invoice_model_obj = new $invoice_model();
        
        $invoice_columns  = array();
        $invoice_columns = array_keys($invoice_model_obj->getTable()->getColumns());
        
        
        
        
        
        
        
        
        
        
        
        if($_REQUEST['invoiceid'])
        {
            if(in_array('invoice_type',$invoice_columns)){
                $payments = $invoice_payment_obj->getInvoicePayments($invoice_type, $_REQUEST['invoiceid']);
            } else{
                $payments = $invoice_payment_obj->getInvoicePayments($_REQUEST['invoiceid']);
            }
            
            $users[] = '999999999999';
            foreach($payments as $k_payment => $v_payment)
            {
                $users[] = $v_payment['create_user'];
            }
            
            $users_list = $user->getMultipleUserDetails($users);
            
            foreach($users_list as $k_user => $v_user)
            {
                $users_list_details[$v_user['id']] = $v_user;
            }
            
            if($_REQUEST['op'] == 'del')
            {
                if(count($payments) == 1)
                {
                    $next = '0';
                }
                else
                {
                    $next = '1';
                }
                
                if(in_array('invoice_type',$invoice_columns)){
                    $del_payment = $invoice_payment_obj->delete_invoice_payment($invoice_type,$_REQUEST['paymentid']);
                } else{
                    $del_payment = $invoice_payment_obj->delete_invoice_payment($_REQUEST['paymentid']);
                }
                
                //update invoice status when deleting an payment
                if($del_payment)
                {
                    
                    if(in_array('invoice_type',$invoice_columns)){
                        $invoice_payments_sum = $invoice_payment_obj->getInvoicesPaymentsSum($invoice_type,array($_REQUEST['invoiceid']));
                        $invoice_details = $invoice_model_obj->get_invoice($invoice_type,$_REQUEST['invoiceid']);
                    } else{
                        $invoice_payments_sum = $invoice_payment_obj->getInvoicesPaymentsSum(array($_REQUEST['invoiceid']));
                        $invoice_details = $invoice_model_obj->get_invoice($_REQUEST['invoiceid']);
                    }
                    
                    if($invoice_payments_sum)
                    {
                        if($invoice_payments_sum[$_REQUEST['invoiceid']]['paid_sum'] >= $invoice_details[0]['invoice_total'])
                        {
                            $status = '3'; //paid
                        }
                        else
                        {
                            $status = '5'; //not paid/partial paid
                        }
                    }
                    else
                    {
                        //no payments => draft
                        $status = '2';
                    }
                    if(in_array('invoice_type',$invoice_columns)){
                        $update_status = $invoice_form_obj->ToggleStatusInvoices($invoice_type,array($_REQUEST['invoiceid']), $status);
                    } else {
                        $update_status = $invoice_form_obj->ToggleStatusInvoices(array($_REQUEST['invoiceid']), $status);
                    }
                }
                
                //reload the payments
                unset($payments);
                if(in_array('invoice_type',$invoice_columns)){
                    $payments = $invoice_payment_obj->getInvoicePayments($invoice_type,$_REQUEST['invoiceid']);
                } else{
                    $payments = $invoice_payment_obj->getInvoicePayments($_REQUEST['invoiceid']);
                    
                }
            }
            
            $this->view->payments = $payments;
            $this->view->users_list = $users_list_details;
            $payments_list = $this->view->render('invoiceclient/listpayments.html');
            echo $payments_list;
            
            exit;
        }
        else
        {
            exit;
        }
    }
 
    
    /**
     * Ancuta  temp fn - as it is not finished
     */
    public function generatereminderinvoiceAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        
        $clientid = $this->clientid;
        $userid = $this->userid;
        
        if($this->getRequest()->isPost())
        {
            $invoiceids = array_values($_REQUEST['document']);
            $invoicetable = $_REQUEST['warningmore_table'];
            $invoicewarning = $_REQUEST['warningmore_type'];
        }
        else
        {
            $params = $this->getRequest()->getParams();
            $invoiceids = $params['invoiceids'];
            $invoicetable = $params['invoicetable'];
            $invoicewarning = $params['invoicewarning'];
            
        }
        
        
        if(!$invoiceids || $invoiceids == "")
        {
            $this->_redirect(APP_BASE . 'invoiceclient/invoices');
            exit();
            
        }
        
        $client = new Client();
        $modules = new Modules();
        $usergroups = new Usergroup();
        $users = new User();
        $rtmpl = new RemindersInvoiceTemplates;
        $patientmaster = new PatientMaster();
        $hi_perms = new HealthInsurancePermissions();
        $phelathinsurance = new PatientHealthInsurance();
        $healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
        $ppun = new PpunIpid();
        
        $template_data = $rtmpl->get_reminders_template($clientid, false, '1', false, $invoicewarning);
        //var_dump($template_data); exit;
        
        $this->view->folder_stamp = time();
        
        //used modules checks
        if($modules->checkModulePrivileges("88", $this->clientid))
        {
            $ppun_module = "1";
        }
        else
        {
            $ppun_module = "0";
        }
        
        if($modules->checkModulePrivileges("90", $this->clientid))
        {
            $debtor_number_module = "1";
        }
        else
        {
            $debtor_number_module = "0";
        }
        
        if($modules->checkModulePrivileges("91", $this->clientid))
        {
            $paycenter_module = "1";
        }
        else
        {
            $paycenter_module = "0";
        }
        
        if($invoicetable == "sh_invoice_new" ){
            $allowed_inv = new ShInvoices();
            $invoicetype = 'sh_invoice';
            $invoice_type = 'sh_invoice';
            $oldaction=$this->getRequest()->getParam('oldaction');
            $url_redirect = APP_BASE . 'invoicenew/'.$oldaction;
            $no_temp_redirect = APP_BASE . 'invoicenew/shinvoices';
            
        } else if($invoicetable == "bayern_sapv_invoice_new")  {
            $allowed_inv = new BayernInvoicesNew();
            $invoicetype = 'bayern_sapv_invoice';
            $invoice_type = 'bayern_sapv_invoice';
            $oldaction=$this->getRequest()->getParam('oldaction');
            $url_redirect = APP_BASE . 'invoicenew/'.$oldaction;
            $no_temp_redirect = APP_BASE . 'invoicenew/invoicesnew';
            
        } else if($invoicetable == "bw_sapv_invoice_new")  {
            $allowed_inv = new BwInvoicesNew();
            $invoicetype = 'bw_sapv_invoice_new';
            $invoice_type = 'bw_sapv_invoice_new';
            $oldaction=$this->getRequest()->getParam('oldaction');
            $url_redirect = APP_BASE . 'invoicenew/'.$oldaction;
            $no_temp_redirect = APP_BASE . 'invoicenew/invoicesnew';
            
        } else if($invoicetable == "bw_sapv_invoice_old")  {
            $allowed_inv = new BwInvoices();
            $invoicetype = 'bw_sapv_invoice';
            $invoice_type = 'bw_sapv_invoice';
            $url_redirect = APP_BASE . 'invoice/bwinvoices';
            $no_temp_redirect = APP_BASE . 'invoice/bwinvoices';
            
            
            
        }else if($invoicetable == "hospiz_invoice_new")  {
            $allowed_inv = new HospizInvoices();
            $invoicetype = 'hospiz_invoice';
            $invoice_type = 'hospiz_invoice';
            $oldaction=$this->getRequest()->getParam('oldaction');
            $url_redirect = APP_BASE . 'invoicenew/'.$oldaction;
            $no_temp_redirect = APP_BASE . 'invoicenew/invoicesnew';
            
        }else if($invoicetable == "rlp_invoice_new")  {
            $allowed_inv = new RlpInvoices();
            $invoicetype = 'rlp_invoice';
            $invoice_type = 'rlp_invoice';
            $oldaction=$this->getRequest()->getParam('oldaction');
            $url_redirect = APP_BASE . 'invoicenew/'.$oldaction;
            $no_temp_redirect = APP_BASE . 'invoicenew/invoicesnew';
            
            
        }else if($invoicetable == "bra_invoice_new")  {
            $allowed_inv = new BraInvoices();
            $invoicetype = 'bra_invoice';
            $invoice_type = 'bra_invoice';
            $url_redirect = APP_BASE . 'invoicenew/brainvoices';
            $no_temp_redirect = APP_BASE . 'invoicenew/brainvoices';
            
        }else if($invoicetable == "bre_sapv_invoice_old")  {
            $allowed_inv = new BreInvoices();
            $invoicetype = 'bre_sapv_invoice';
            $invoice_type = 'bre_sapv_invoice';
            $url_redirect = APP_BASE . 'invoice/breinvoices';
            $no_temp_redirect = APP_BASE . 'invoice/breinvoices';
            
            
        }else if($invoicetable == "bre_hospiz_sapv_invoice_old")  {
            $allowed_inv = new BreHospizInvoices();
            $invoicetype = 'bre_hospiz_sapv_invoice';
            $invoice_type = 'bre_hospiz_sapv_invoice';
            $url_redirect = APP_BASE . 'invoice/brehospizinvoices';
            $no_temp_redirect = APP_BASE . 'invoice/brehospizinvoices';
            
            
        }else if($invoicetable == "he_invoice_old")  {
            $allowed_inv = new HeInvoices();
            $invoicetype = 'he_invoice';
            $invoice_type = 'he_invoice';
            $url_redirect = APP_BASE . 'invoice/heinvoiceslist';
            $no_temp_redirect = APP_BASE . 'invoice/heinvoiceslist';
            
            
        }else if($invoicetable == "nie_patient_invoice_old")  {
            $allowed_inv = new HiInvoices();
            $invoicetype = 'nie_patient_invoice';
            $invoice_type = 'nie_patient_invoice';
            $url_redirect = APP_BASE . 'invoice/healthinsuranceinvoices';
            $no_temp_redirect = APP_BASE . 'invoice/healthinsuranceinvoices';
            //ISPC-2312 Ancuta 07.12.2020
        }else if($invoicetable == "nie_patient_invoice")  {
            $allowed_inv = new HiInvoices();
            $invoicetype = 'nie_patient_invoice';
            $invoice_type = 'nie_patient_invoice';
            $url_redirect = APP_BASE . 'invoice/healthinsuranceinvoices';
            $no_temp_redirect = APP_BASE . 'invoice/healthinsuranceinvoices';
            //--
            
        }else if($invoicetable == "rp_invoice_old")  {
            $allowed_inv = new RpInvoices();
            $invoicetype = 'rp_invoice';
            $invoice_type = 'rp_invoice';
            $url_redirect = APP_BASE . 'invoice/rpinvoiceslist';
            $no_temp_redirect= APP_BASE . 'invoice/rpinvoiceslist';
            
            
        }else if($invoicetable == "by_invoice_old")  {
            $allowed_inv = new ClientInvoices();
            $invoicetype = 'by_invoice';
            $invoice_type = 'by_invoice';
            $url_redirect = APP_BASE . 'invoice/invoice';
            $no_temp_redirect = APP_BASE . 'invoice/invoice';
            
            
        } else{
            
            $allowed_inv = new InvoiceSystem();
            //$invoicetype = $invoicetable; // At the momment this is used for bre_kinder_invoice and nr invoice(ISPC-2278)
            if(in_array($invoicetable,array("bre_kinder_invoice","nr_invoice","demstepcare_invoice"))){
                $invoicetype = $invoicetable; // At the momment this is used for bre_kinder_invoice
                $invoice_type = $invoicetable; // At the momment this is used for bre_kinder_invoice
                $oldaction = $this->getRequest()->getParam('oldaction');
                $url_redirect = APP_BASE . 'invoicenew/'.$oldaction;
                $no_temp_redirect = APP_BASE . 'invoicenew/invoicesnew';
            }
            
        }
        
        
        $template_data = $rtmpl->get_reminders_template($clientid, false, '1', false, $invoicewarning);
        if(!$template_data)
        {
            
            $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoicetype.'&flg=notemplate');
            exit();
        }
        
        $client_usergroups_array = $usergroups->getClientGroups($clientid);
        
        $ug_details = array();
        foreach($client_usergroups_array as $k=>$group_data){
            $ug_details[$group_data['id']] = $group_data;
        }
        
        //user details
        
        $client_user_array = $users->getUserByClientid($clientid,0,true,false);
        
        foreach($client_user_array as $k=>$cu)
        {
            $user_array[$cu['id']] = $cu;
            $user_array[$cu['id']]['groupname'] = $ug_details[$cu['groupid']]['groupname'];
            if(strlen($cu['shortname']) >  0){
                $user_array[$cu['id']]['initials'] = $cu['shortname'];
            }
            else
            {
                $user_array[$cu['id']]['initials'] = mb_substr($cu['first_name'], 0, 1, "UTF-8") . "" . mb_substr($cu['last_name'], 0, 1, "UTF-8");
                
            }
        }
        
        //client_details
        $client_details = $client->getClientDataByid($clientid);
        
        //invoices details
        if(!is_array($invoiceids))
        {
            $invoiceids_arr = explode(',', $invoiceids);
        }
        else
        {
            $invoiceids_arr = $invoiceids;
        }
        
        
        switch ($invoicetype){
            case 'by_invoice':
                $invoices_data = $allowed_inv->get_invoices($invoiceids_arr, $clientid);
                break;
            case 'bre_kinder_invoice':
            case 'nr_invoice': // ISPC-2286
                $invoices_data = $allowed_inv->get_invoices($invoice_type, $invoiceids_arr);
                break;
            case "demstepcare_invoice": // ISPC-2461
                $invoices_data = $allowed_inv->get_invoices($invoice_type, $invoiceids_arr);
                break;
            default:
                $invoices_data = $allowed_inv->get_invoices($invoiceids_arr);
                break;
        }
        
        $patients_invoice_days = array();
        foreach($invoices_data['invoices_data'] as $v_invoice)
        {
            //var_dump($v_invoice['ipid']); exit;
            $ipids[] = $v_invoice['ipid'];
            
            $current_period[$v_invoice['ipid']]['start'] = date('Y-m-d', strtotime($v_invoice['invoice_start']));
            $current_period[$v_invoice['ipid']]['end'] = date('Y-m-d', strtotime($v_invoice['invoice_end']));
            
            if(empty($current_period[$v_invoice['ipid']]['days']))
            {
                $current_period[$v_invoice['ipid']]['days'] = array();
            }
            
            if(empty($patients_invoice_days[$v_invoice['ipid']]))
            {
                $patients_invoice_days[$v_invoice['ipid']] = array();
            }
            
            $days_arr = $patientmaster->getDaysInBetween($v_invoice['invoice_start'], $v_invoice['invoice_end']);
            $current_period[$v_invoice['ipid']]['days'] = array_merge($current_period[$v_invoice['ipid']]['days'], $days_arr);
            $patients_invoice_days[$v_invoice['ipid']] = array_merge($patients_invoice_days[$v_invoice['ipid']], $days_arr);
            
            array_walk_recursive($current_period[$v_invoice['ipid']]['days'], function(&$value) {
                $value = date("d.m.Y", strtotime($value));
            });
                array_walk_recursive($patients_invoice_days[$v_invoice['ipid']], function(&$value) {
                    $value = date("d.m.Y", strtotime($value));
                });
                    
                    $days_arr = array();
        }
        
        $sql = 'e.epid,  e.ipid, p.ipid, p.birthd,';
        $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
        $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
        $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
        $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
        $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
        $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
        
        $ipids = array_values(array_unique($ipids));
        
        $patient = Doctrine_Query::create()
        ->select($sql)
        ->from('PatientMaster p')
        ->whereIn("p.ipid", $ipids)
        ->leftJoin("p.EpidIpidMapping e")
        ->andWhere('e.clientid = ?', $clientid);
        $patients_res = $patient->fetchArray();
        
        $params['patient_days'] = array();
        foreach($patients_res as $kpat=>$vpat)
        {
            $params['patient_days'][$vpat['ipid']]['details'] = $vpat;
            $params['patient_days'][$vpat['ipid']]['details']['epid'] = $vpat['EpidIpidMapping']['epid'];
        }
        //set current period to work with
        $params['period'] = $current_period;
        $sapv_details = $invoicenewController->get_sapvs_approved_details($ipids, $current_period);
        
        //print_r($params['patient_days']); exit;
        //patient HEALTH INSURANCE START
        $healthinsu_multi_array = $phelathinsurance->get_multiple_patient_healthinsurance($ipids, true);
        
        //multiple hi subdivisions && hi subdivisions permissions
        $divisions = $hi_perms->getClientHealthInsurancePermissions($clientid);
        
        if($divisions)
        {
            foreach($healthinsu_multi_array as $k_hi => $v_hi)
            {
                $hi_companyids[] = $v_hi['companyid'];
            }
            
            $healthinsu_subdiv_arr = $healthinsu_subdiv->get_hi_subdivisions_multiple($hi_companyids);
        }
        
        foreach($ipids as $k_ipid => $v_ipid)
        {
            
            if($divisions && strlen($healthinsu_subdiv_arr[$v_ipid]['3']['name']) > '0')
            {
                $sapv_address[$v_ipid][] = htmlentities($healthinsu_subdiv_arr[$v_ipid][3]['street1']);
                $sapv_address[$v_ipid][] = htmlentities($healthinsu_subdiv_arr[$v_ipid][3]['zip'] . ' ' . $healthinsu_subdiv_arr[$v_ipid][3]['city']);
                $params['patient_days'][$v_ipid]['sapv_recipient'] = implode('<br />', array_values(array_unique($sapv_address[$v_ipid])));
            }
            else
            {
                $params['patient_days'][$v_ipid]['sapv_recipient'] = '';
            }
        }
        
        //patient HEALTH INSURANCE END
        
        
        $batch_temp_folder = Pms_CommonData::uniqfolder(PDFDOCX_PATH . '/' . $this->clientid);
        $temp_files = array();
        $reminder_db_data = array();
        
        
        foreach($invoices_data['invoices_data'] as $invoice_data)
        {
            $sapv_details_data[$invoice_data['ipid']] = end($sapv_details[$invoice_data['ipid']]);
            if(strlen($invoice_data['address']) > 0 ){
                if(strpos($invoice_data['address'],"style"))
                {
                    $invoice_data['address'] = preg_replace('/style=\"(.*)\"/i', '', $invoice_data['address']);
                }
                
                $invoice_data['address'] = str_replace(array(" <p >"," <p>"," <p> ","<p >","<p>"),"", $invoice_data['address']);
                $invoice_data['address'] = str_replace(array("</p>"," </p>","</p> "),"", $invoice_data['address']);
                if($invoicetable != "nie_patient_invoice_old"){
                    $invoice_data['address'] = str_replace(array("\n"),"<br />", $invoice_data['address']);
                }
                
                if( empty($invoice_data['beneficiary_address'])){
                    $invoice_data['beneficiary_address'] = $invoice_data['address'];
                }
            }
            else{
                if($invoicetable == "by_invoice_old"){
                    $addr = array();
                    foreach($invoice_data['items'] as $k=>$invit){
                        if(in_array($invit['itemLabel'],array('healthinsurancename','healthinsurancecontact','healthinsurancestreet','healthinsuranceaddress')) ){
                            $addr[]= $invit['itemString'].' ';
                        }
                    }
                    $invoice_data['address'] = implode("<br />",$addr);
                }
            }
            
            $pflege_arr = PatientMaintainanceStage::getpatientMaintainanceStageInPeriod($invoice_data['ipid'], $invoice_data['invoice_start'], $invoice_data['invoice_end']);
            
            if($pflege_arr)
            {
                $last_pflege = end($pflege_arr);
                $invoice_data['patient_pflegestufe'] = $last_pflege['stage'];
            }
            else
            {
                $invoice_data['patient_pflegestufe'] = ' - ';
            }
            
            //client tokens
            if(strlen($invoice_data['client_ik']) > '0')
            {
                $tokens['client_ik'] = $invoice_data['client_ik'];
            }
            else
            {
                $tokens['client_ik'] = $client_details[0]['institutskennzeichen'];
            }
            
            //patient details tokens
            $tokens['patienten_id'] = strtoupper(html_entity_decode($params['patient_days'][$invoice_data['ipid']]['details']['epid'], ENT_QUOTES, 'UTF-8'));
            $tokens['first_name'] = html_entity_decode($params['patient_days'][$invoice_data['ipid']]['details']['first_name'], ENT_QUOTES, 'UTF-8');
            $tokens['last_name'] = html_entity_decode($params['patient_days'][$invoice_data['ipid']]['details']['last_name'], ENT_QUOTES, 'UTF-8');
            $tokens['birthd'] = date('d.m.Y', strtotime($params['patient_days'][$invoice_data['ipid']]['details']['birthd']));
            $tokens['street'] = html_entity_decode($params['patient_days'][$invoice_data['ipid']]['details']['street1'], ENT_QUOTES, 'UTF-8');
            $tokens['zip'] = html_entity_decode($params['patient_days'][$invoice_data['ipid']]['details']['zip'], ENT_QUOTES, 'UTF-8');
            $tokens['city'] = html_entity_decode($params['patient_days'][$invoice_data['ipid']]['details']['city'], ENT_QUOTES, 'UTF-8');
            $tokens['patient_pflegestufe'] = html_entity_decode($invoice_data['patient_pflegestufe'], ENT_QUOTES, 'UTF-8');
            
            //health insurance tokens
            $tokens['insurance_no'] = html_entity_decode($healthinsu_multi_array[$invoice_data['ipid']]['insurance_no'], ENT_QUOTES, 'UTF-8');
            // 						$tokens['address'] = html_entity_decode($invoice_data['address'], ENT_QUOTES, 'UTF-8');
            switch ($invoicetable)
            {
                case  "by_invoice_old" : // OK
                case  "rlp_invoice_new" : // OK
                    
                case  "bre_sapv_invoice_old" :
                case  "bre_hospiz_sapv_invoice_old" :
                case  "he_invoice_old" :
                case  "nie_patient_invoice_old" :
                case  "nie_patient_invoice" : //ISPC-2312 Ancuta 07.12.2020
                case  "rp_invoice_old" :
                case  "bw_sapv_invoice_old" :
                    
                case  "bw_sapv_invoice_new" : //14.08.2018
                    $tokens['address'] = html_entity_decode($invoice_data['address'], ENT_QUOTES, 'UTF-8');
                    break;
                    
                    
                case  "sh_invoice_new" :
                case  "sh_invoice" :
                case  "bayern_sapv_invoice_new" :
                case  "hospiz_invoice_new" :
                case  "bra_invoice_new" :
                    $tokens['address'] = htmlentities($invoice_data['address']);
                    
                    break;
                    
                default:
                    $tokens['address'] = htmlentities($invoice_data['address']);
                    break;
                    
            }
            
            $tokens['beneficiary_address'] = "";
            $tokens['SAPV_Rechnungsempfaenger'] = html_entity_decode($params['patient_days'][[$invoice_data['ipid']]]['sapv_recipient'], ENT_QUOTES, 'UTF-8');//ISPC-1236
            
            //invoice specific tokens
            $tokens['invoiced_month'] = "";
            if(!empty($invoice_data['invoiced_month']) && $invoice_data['invoiced_month'] != "0000-00-00 00:00:00")
            {
                $tokens['invoiced_month'] = date('m/Y', strtotime($invoice_data['invoiced_month']));
            }
            
            $tokens['invoiced_period'] = "";
            
            if(!empty($invoice_data['invoice_start']) && $invoice_data['invoice_start'] != "0000-00-00 00:00:00" && !empty($invoice_data['invoice_end']) && $invoice_data['invoice_end'] != "0000-00-00 00:00:00")
            {
                $tokens['invoiced_period'] = date('d.m.Y', strtotime($invoice_data['invoice_start'])).'-'.date('d.m.Y', strtotime($invoice_data['invoice_end']));
            }
            
            $tokens['prefix'] = html_entity_decode($invoice_data['prefix'], ENT_QUOTES, 'UTF-8');
            if($invoicetype != 'by_invoice')
            {
                $tokens['invoice_number'] = html_entity_decode($invoice_data['invoice_number'], ENT_QUOTES, 'UTF-8');
            }
            else
            {
                $tokens['invoice_number'] = html_entity_decode($invoice_data['rnummer'], ENT_QUOTES, 'UTF-8');
            }
            if($invoicetype != 'by_invoice')
            {
                $tokens['full_invoice_number'] = html_entity_decode($invoice_data['prefix'] . $invoice_data['invoice_number'], ENT_QUOTES, 'UTF-8');
            }
            else
            {
                $tokens['full_invoice_number'] = html_entity_decode($invoice_data['prefix'] . $invoice_data['rnummer'], ENT_QUOTES, 'UTF-8');
            }
            
            //invoice date
            if($invoicetype != 'by_invoice')
            {
                if($invoice_data['completed_date'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['completed_date'])) != "1970")
                {
                    $tokens['invoice_date'] = date('d.m.Y', strtotime($invoice_data['completed_date']));
                }
                else
                {
                    $tokens['invoice_date'] = '';
                }
            }
            else
            {
                if($invoice_data['completedDate'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['completedDate'])) != "1970")
                {
                    $tokens['invoice_date'] = date('d.m.Y', strtotime($invoice_data['completedDate']));
                }
                else
                {
                    $tokens['invoice_date'] = '';
                }
            }
            //start billed action day
            if($invoice_data['start_active'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['start_active'])) != "1970")
            {
                $tokens['first_active_day'] = date('d.m.Y', strtotime($invoice_data['start_active']));
            }
            else
            {
                $tokens['first_active_day'] = '';
            }
            
            //end billed action day
            if($invoice_data['end_active'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['end_active'])) != "1970")
            {
                $tokens['last_active_day'] = date('d.m.Y', strtotime($invoice_data['end_active']));
            }
            else
            {
                $tokens['last_active_day'] = '';
            }
            
            //first sapv day
            if($invoice_data['start_sapv'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['start_sapv'])) != "1970")
            {
                $tokens['first_sapv_day'] = date('d.m.Y', strtotime($invoice_data['start_sapv']));
            }
            else
            {
                $tokens['first_sapv_day'] = '';
            }
            
            //last sapv day
            if($invoice_data['end_sapv'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['end_sapv'])) != "1970")
            {
                $tokens['last_sapv_day'] = date('d.m.Y', strtotime($invoice_data['end_sapv']));
            }
            else
            {
                $tokens['last_sapv_day'] = '';
            }
            
            //sapv approve date
            if($invoice_data['sapv_approve_date'] != '0000-00-00 00:00:00' && date('Y', strtotime($invoice_data['sapv_approve_date'])) != "1970")
            {
                $tokens['sapv_approve_date'] = date('d.m.Y', strtotime($invoice_data['sapv_approve_date']));
            }
            elseif(date('Y', strtotime($sapv_details_data[$invoice_data['ipid']]['approved_date'])) != "1970" && strlen($invoice_data['sapv_approve_date']) > '0')
            {
                $tokens['sapv_approve_date'] = $sapv_details_data[$invoice_data['ipid']]['approved_date'];
            }
            else
            {
                $tokens['sapv_approve_date'] = '';
            }
            
            if($invoice_data['sapv_approve_nr'] == "-" || strlen(trim(rtrim($invoice_data['sapv_approve_nr']))) == "0")
            {
                $tokens['sapv_approve_nr'] = html_entity_decode($invoice_data['sapv_approve_nr'], ENT_QUOTES, 'UTF-8');
            }
            else
            {
                $tokens['sapv_approve_nr'] = html_entity_decode($sapv_details_data[$invoice_data['ipid']]['approved_number'], ENT_QUOTES, 'UTF-8');
            }
            
            //$tokens['footer'] = html_entity_decode($invoice_data['footer'], ENT_QUOTES, 'UTF-8');
            //$tokens['invoice_items'] = $invoice_data['items'];
            
            $tokens['unique_id'] = $invoice_data['id'];
            if($invoicetype != 'by_invoice')
            {
                $tokens['invoice_total'] = number_format(($invoice_data['invoice_total']), '2', ',', '.');
            }
            else
            {
                $tokens['invoice_total'] = number_format(($invoice_data['invoiceTotal']), '2', ',', '.');
            }
            
            $tokens['debitoren_nummer_oder_pv'] = '';
            $tokens['debitor_number'] = "";
            $tokens['debtor_number'] = "";
            if($ppun_module == "1")
            {
                
                if (array_key_exists("ppun",$invoice_data))
                {
                    if(strlen($invoice_data['ppun']) > '0')
                    {
                        $tokens['ppun'] = $invoice_data['ppun'];
                        $tokens['debitoren_nummer_oder_pv'] = $invoice_data['ppun'];
                    }
                    else
                    {
                        $tokens['ppun'] = '';
                    }
                }
                else
                {
                    if($healthinsu_multi_array[$invoice_data['ipid']]['privatepatient']  == "1")
                    {
                        $ppun_number = $ppun->check_patient_ppun($invoice_data['ipid'], $clientid);
                        if($ppun_number)
                        {
                            $tokens['debitor_number'] = $ppun_number['ppun'];
                            $tokens['debtor_number'] = $ppun_number['ppun'];
                        }
                        else
                        {
                            $tokens['debitor_number'] = '';
                            $tokens['debtor_number'] = '';
                        }
                    }
                }
            }
            
            if($debtor_number_module == "1")
            {
                if (array_key_exists("debtor_number",$invoice_data))
                {
                    if(strlen($invoice_data['debtor_number']) > '0')
                    {
                        $tokens['debtor_number'] = $invoice_data['debtor_number'];
                        $tokens['debitor_number'] = $invoice_data['debtor_number'];
                        $tokens['debitoren_nummer_oder_pv'] = $invoice_data['debtor_number'];
                    }
                    else
                    {
                        $tokens['debtor_number'] = '';
                        $tokens['debitor_number'] = '';
                    }
                }
                else
                {
                    if($healthinsu_multi_array[$invoice_data['ipid']]['privatepatient'] == "0")
                    {
                        if(strlen($healthinsu_multi_array[$invoice_data['ipid']]['ins_debtor_number']) > '0')
                        {
                            $tokens['debtor_number'] = $healthinsu_multi_array[$invoice_data['ipid']]['ins_debtor_number'];
                            $tokens['debitor_number'] = $healthinsu_multi_array[$invoice_data['ipid']]['ins_debtor_number'];
                        }
                        else
                        {
                            $tokens['debtor_number'] = $healthinsu_multi_array[$invoice_data['ipid']]['company']['debtor_number'];
                            $tokens['debitor_number'] = $healthinsu_multi_array[$invoice_data['ipid']]['company']['debtor_number'];
                        }
                    }
                }
            }
            
            
            if($paycenter_module == "1" && strlen($invoice_data['paycenter']) > '0')
            {
                $tokens['paycenter'] = $invoice_data['paycenter'];
            }
            else
            {
                $tokens['paycenter'] = '';
            }
            
            /* if($userid == "338"){
             echo "<pre>";
             print_R($tokens); exit;
             } */
            
            $reminder_data[] = array(
                "clientid"=> $clientid,
                "invoiceid"=> $invoice_data['id'],
                "invoice_type"=> $invoicetype,
                "reminder_type"=> $invoicewarning,
            );
            
            if($template_data){
                // generate file
                
                if(count($invoiceids_arr) == 1 && isset($params['invoiceids'])){// from individual link
                    // save warning individual warning
                    $res = new RemindersInvoice();
                    $res->clientid = $clientid;
                    $res->invoiceid = $invoice_data['id'];
                    $res->invoice_type = $invoicetype;
                    $res->reminder_type = $invoicewarning;
                    if($_REQUEST['type'])
                    {
                        $res->reminder_doc_type = $_REQUEST['type'];
                    }
                    $res->save();
                    // generate individual pdf warning
                    $invoicenewController->generate_reminder_file($template_data[0], $tokens);
                    
                } else{
                    
                    // generate and save docx files form multiple invoices
                    $temp_files[] = $invoicenewController->generate_reminder_file($template_data[0], $tokens, 'docx', $batch_temp_folder, 'generate');
                    // create array to save warnings for multiple invoices
                    $reminder_db_data[] = array(
                        "clientid"=> $clientid,
                        "invoiceid"=> $invoice_data['id'],
                        "invoice_type"=> $invoicetype,
                        "reminder_type"=> $invoicewarning,
                    );
                }
                
            }
        }
        
        if(count($temp_files) > '0')
        {
            // save warning for multiple invoices
            if(!empty($reminder_db_data)){
                
                if($_REQUEST['warningmore_doc_type'])
                {
                    foreach($reminder_db_data as &$data)
                    {
                        $data['reminder_doc_type'] = $_REQUEST['warningmore_doc_type'];
                    }
                }
                
                $collection = new Doctrine_Collection('RemindersInvoice');
                $collection->fromArray($reminder_db_data);
                $collection->save();
            }
            
            //final cleanup (check if files are on disk)
            foreach($temp_files as $k_temp => $v_file)
            {
                if(!is_file($v_file))
                {
                    //remove unexisting files
                    //							$unsetted_files[] = $v_file; //for debugs
                    unset($temp_files[$v_file]);
                }
            }
            
            $remaining_temp_files = array_values(array_unique($temp_files));
            
            if(count($remaining_temp_files) > '0')
            {
                if($_REQUEST['warningmore_doc_type'])
                {
                    if($_REQUEST['warningmore_doc_type'] == 'pdf')
                    {
                        $final_file = $invoicenewController->generate_reminder_file($template_data[0], false, 'pdf', $batch_temp_folder, 'merge', $temp_files);
                    }
                    else if($_REQUEST['warningmore_doc_type'] == 'docx')
                    {
                        $final_file = $invoicenewController->generate_reminder_file($template_data[0], false, 'docx', $batch_temp_folder, 'merge', $temp_files);
                    }
                }
                else
                {
                    $final_file = $invoicenewController->generate_reminder_file($template_data[0], false, 'pdf', $batch_temp_folder, 'merge', $temp_files);
                }
            }
        }
        
        //ISPC-2312 Ancuta 06.12.2020
        $this->_redirect(APP_BASE . 'invoiceclient/invoices?invoice_type='.$invoicetype);
        exit();
   
        //--
    }
    
    private function generaterpinvoice($params)
    {
    	if(isset($params['print_job']) && $params['print_job'] == '1'){
    		$this->_helper->layout->setLayout('layout_ajax');
    		$this->_helper->viewRenderer->setNoRender();
    	}
    
    	setlocale(LC_ALL, 'de_DE.UTF-8');
    	//$logininfo = new Zend_Session_Namespace('Login_Info');
    	//$tm = new TabMenus();
    	$p_list = new PriceList();
    	$form_types = new FormTypes();
    	$sapvs = new SapvVerordnung();
    	$patientmaster = new PatientMaster();
    	//$sapvverordnung = new SapvVerordnung();
    	//$pflege = new PatientMaintainanceStage();
    	$hi_perms = new HealthInsurancePermissions();
    	$phelathinsurance = new PatientHealthInsurance();
    	$healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
    	//$boxes = new LettersTextBoxes();
    	$client = new Client();
    	$user = new User();
    	$usergroups = new Usergroup();
    	$patientdischarge = new PatientDischarge();
    	$discharge_method = new DischargeMethod();
    	$pat_diagnosis = new PatientDiagnosis();
    	//Check patient permissions on controller and action
    	//$patient_privileges = PatientPermissions::checkPermissionOnRun();
    	$rp_invoices_form = new Application_Form_RpInvoices();
    	
    	$ppun = new PpunIpid();
    
    	if(isset($params) && !empty($params)){
    		$_REQUEST = $params;
    		$this->_helper->viewRenderer->setNoRender();
    	}
    
    	//var_dump($_REQUEST); exit;
    	//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
    	$clientid = isset($_REQUEST['clientid']) && ! empty($_REQUEST['clientid']) ? $_REQUEST['clientid'] :  $this->clientid;
    	$userid = isset($_REQUEST['userid']) && ! empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $this->userid;
    
    	//$letter_boxes_details = $boxes->client_letter_boxes($clientid);
    	$client_details = $client->getClientDataByid($clientid);
    	$this->view->client_details = $client_details[0];
    
    	if(strlen($client_details[0]['institutskennzeichen']) > 0)
    	{
    		$client_address['ik'] = $client_details[0]['institutskennzeichen'];
    	}
    
    	if(strlen($client_details[0]['team_name']) > 0)
    	{
    		$client_address['team_name'] = $client_details[0]['team_name'];
    	}
    
    	if(strlen($client_details[0]['street1']) > 0)
    	{
    		$client_address['street1'] = $client_details[0]['street1'];
    	}
    
    	if(strlen($client_details[0]['postcode']) > 0)
    	{
    		$client_address['postcode_city'] = $client_details[0]['postcode'];
    	}
    
    	if(strlen($client_details[0]['city']) > 0)
    	{
    		$client_address['postcode_city'] = $client_address['postcode_city'] . ' ' . $client_details[0]['city'];
    	}
    
    	if(strlen($client_details[0]['phone']) > 0)
    	{
    		$client_address['phone'] = "Tel: " . $client_details[0]['phone'];
    	}
    
    	if(strlen($client_details[0]['fax']) > 0)
    	{
    		$client_address['fax'] = "Fax: " . $client_details[0]['fax'];
    	}
    
    
    	$client_nice_address = implode("\n", $client_address);
    
    	$modules =  new Modules();
    	$clientModules = $modules->get_client_modules($clientid);
    
    	// Groups  details
    	//ISPC-2134 - 19.12.2017
    	$client_usergroups_array = $usergroups->getClientGroups($clientid);
    
    	$ug_details = array();
    	foreach($client_usergroups_array as $k=>$group_data){
    		$ug_details[$group_data['id']] = $group_data;
    	}
    
    	//user details
    	$users = new User();
    	$client_user_array = $users->getUserByClientid($clientid,0,true,false);
    	foreach($client_user_array as $k=>$cu)
    	{
    		$user_array[$cu['id']] = $cu;
    		$user_array[$cu['id']]['groupname'] = $ug_details[$cu['groupid']]['groupname'];
    		if(strlen($cu['shortname']) >  0){
    			$user_array[$cu['id']]['initials'] = $cu['shortname'];
    		}
    		else
    		{
    			$user_array[$cu['id']]['initials'] = mb_substr($cu['first_name'], 0, 1, "UTF-8") . "" . mb_substr($cu['last_name'], 0, 1, "UTF-8");
    
    		}
    	}
    
    	//user Betriebsstätten-Nr.
    	$user = Doctrine::getTable('User')->find($userid);
    	if($user)
    	{
    		$uarray = $user->toArray();
    		$user_btsnr = $uarray['betriebsstattennummer'];
    		$user_arztnr = $uarray['LANR'];
    	}
    
    	//get default products pricelist
    	$shortcuts = Pms_CommonData::get_prices_shortcuts();
    	$default_price_list = Pms_CommonData::get_default_price_shortcuts();
    
    	//location type to price_type mapping
    	$location_type_match = Pms_CommonData::get_rp_price_mapping();
    
    	//get used form types
    	$form_types = new FormTypes();
    	$all_forms = $form_types->get_form_types($clientid);
    	$set_one = $form_types->get_form_types($clientid, '1');
    	foreach($set_one as $k_set_one => $v_set_one)
    	{
    		$set_one_ids[] = $v_set_one['id'];
    	}
    
    	$client_form_type =  FormTypeActions::get_form_type_actions();
    
    	foreach($all_forms as $k=>$ft){
    		$form2action[$ft['id']] = $client_form_type[$ft['action']]['name'];
    	}
    
    	//get doctor and nurse users
    	//get all related users details
    	$master_groups_first = array('4', '5');
    
    	$client_user_groups_first = $usergroups->getUserGroups($master_groups_first);
    
    	foreach($client_user_groups_first as $k_group_f => $v_group_f)
    	{
    		$master2client[$v_group_f['groupmaster']][] = $v_group_f['id'];
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
    	}
    
    	$ipids = $_REQUEST['ipids'];
    
    	//load template data
    	$template_data = InvoiceTemplates::get_template($clientid, false, '1', $_REQUEST['invoice_type']);
    
    	/* if(!$template_data && $_REQUEST['get_pdf'] == '1'){
    	 $this->redirect(APP_BASE . 'invoiceclient/patientlist?flg=notemplate');
    	 exit;
    	 } */
    
    	//Check patient permissions on controller and action
    	$pt = Doctrine_Query::create()
    	->select('id, ipid, epid')
    	->from('EpidIpidMapping IndexBy ipid')
    	->whereIn('ipid', $ipids)
    	->fetchArray();
    
    	$ipids_no_privileges = array();
    	foreach($ipids as $k_ipid => $v_ipid)
    	{
    		$_REQUEST['epid'] = $pt[$v_ipid]['epid'];
    		$patient_privileges[$v_ipid] = PatientPermissions::checkPermissionOnRun();
    		if(!$patient_privileges[$v_ipid])
    		{
    			$ipids_no_privileges[] = $v_ipid;
    		}
    	}
    
    	$ipids = array_diff($ipids, $ipids_no_privileges);
    	//var_dump($ipids); exit;
    
    	if(!empty($ipids))
    	{
    		$patients_discharge = $patientdischarge->getPatientDischarge($ipids);
    
    		if($patients_discharge)
    		{
    			//get discharge methods
    			$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);
    
    			foreach($discharge_methods as $k_dis_method => $v_dis_method)
    			{
    				if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA")
    				{
    					$death_methods[] = $v_dis_method['id'];
    				}
    			}
    			$death_methods = array_values(array_unique($death_methods));
    		}
    
    		//get patient main diagnosis
    		$patient_main_diag = $pat_diagnosis->get_ipids_main_diagnosis($ipids, $clientid);
    		//this->vie$w->main_diagnosis = implode(', ', $patient_main_diag['icd']);
    
    		//patient days
    		$conditions['client'] = $clientid;
    		$conditions['ipids'] = $ipids;
    		$conditions['periods'][0]['start'] = '2009-01-01';
    		$conditions['periods'][0]['end'] = date('Y-m-d');
    		 
    		$sql = 'e.epid, p.ipid, e.ipid,';
    		$sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    		$sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    		$sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    		$sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    		$sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    		$sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    		 
    		//be aware of date d.m.Y format here
    		$patient_days = Pms_CommonData::patients_days($conditions, $sql);
    		//var_dump($patient_days); exit;
    		$all_patients_periods = array();
    		foreach($patient_days as $k_ipid => $patient_data)
    		{
    			//all patients periods
    			$all_patients_periods = array_merge_recursive($all_patients_periods, $patient_data['active_periods']);
    
    			//used in flatrate
    			if(empty($patient_periods[$k_ipid]))
    			{
    				$patient_periods[$k_ipid] = array();
    			}
    
    			array_walk_recursive($patient_data['active_periods'], function(&$value) {
    				$value = date("Y-m-d", strtotime($value));
    			});
    				$patient_periods[$k_ipid] = array_merge($patient_periods[$k_ipid], $patient_data['active_periods']);
    
    				//hospital days cs
    				if(!empty($patient_data['hospital']['real_days_cs']))
    				{
    					$hospital_days_cs[$k_ipid] = $patient_data['hospital']['real_days_cs'];
    					array_walk($hospital_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//hospiz days cs
    				if(!empty($patient_data['hospiz']['real_days_cs']))
    				{
    					$hospiz_days_cs[$k_ipid] = $patient_data['hospiz']['real_days_cs'];
    					array_walk($hospiz_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//real active days
    				if(!empty($patient_data['real_active_days']))
    				{
    					$active_days_in_period_cs[$k_ipid] = $patient_data['real_active_days'];
    					array_walk($active_days_in_period_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//treatment days
    				if(!empty($patient_data['treatment_days']))
    				{
    					$treatment_days_cs[$k_ipid] = $patient_data['treatment_days'];
    					array_walk($treatment_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//active days
    				if(!empty($patient_data['active_days']))
    				{
    					$active_days[$k_ipid] = $patient_data['active_days'];
    					array_walk($active_days[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				if(empty($hospital_days_cs[$k_ipid]))
    				{
    					$hospital_days_cs[$k_ipid] = array();
    				}
    
    				if(empty($hospiz_days_cs[$k_ipid]))
    				{
    					$hospiz_days_cs[$k_ipid] = array();
    				}
    
    				$hospital_hospiz_days_cs[$k_ipid] = array_merge($hospital_days_cs[$k_ipid], $hospiz_days_cs[$k_ipid]);
    		}
    		 
    		$all_patients_periods = array_values($all_patients_periods);
    		 
    		foreach($all_patients_periods as $k_period => $v_period)
    		{
    			if(empty($months))
    			{
    				$months = array();
    			}
    
    			$period_months = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], 'Y-m');
    			$months = array_merge($months, $period_months);
    		}
    		$months = array_values(array_unique($months));
    		 
    		foreach($months as $k_m => $v_m)
    		{
    			$months_unsorted[strtotime($v_m)] = $v_m;
    		}
    		ksort($months_unsorted);
    		$months = array_values(array_unique($months_unsorted));
    		 
    		foreach($months as $k_month => $v_month)
    		{
    			if(!function_exists('cal_days_in_month'))
    			{
    				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
    			}
    			else
    			{
    				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
    			}
    
    			$months_details[$v_month]['start'] = $v_month . "-01";
    			$months_details[$v_month]['days_in_month'] = $month_days;
    			$months_details[$v_month]['end'] = $v_month . '-' . $month_days;
    
    			//$month_select_array[$v_month] = $v_month;
    			$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
    		}
    
    		//loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)
    		foreach($ipids as $k_sel_pat => $v_sel_pat)
    		{
    			//get patients sapvs last fall
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'sapvid')
    			{
    				$selected_sapv_falls_ipids[] = $v_sel_pat;
    				$selected_sapv_falls[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			//get patient month fall
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'list')
    			{
    				$selected_fall_ipids[] = $v_sel_pat;
    				$selected_month[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'admission')
    			{
    				$admission_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'quarter')
    			{
    				$quarter_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    		}
    		 
    		$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($selected_sapv_falls_ipids, true);
    		foreach($selected_sapv_falls as $k_ipid => $fall_id)
    		{
    			$patients_sapv[$k_ipid] = $fall_id;
    			$patients_selected_periods[$k_ipid] = $patients_sapv_periods[$k_ipid][$fall_id];
    			$_REQUEST['nosapvperiod'][$k_ipid] = '0';
    			$_REQUEST['period'] = $patients_selected_periods;
    		}
    
    		//rewrite the periods array if the period is entire month not sapv fall
    		$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
    
    		foreach($patients_sapv_periods as $k_sapv_ipid => $v_sapvs)
    		{
    			foreach($v_sapvs as $k_sapv_id => $v_sapv_data)
    			{
    				if(empty($sapv_days[$v_sapv_data['ipid']]))
    				{
    					$sapv_days[$v_sapv_data['ipid']] = array();
    				}
    					
    				$sapv_days[$v_sapv_data['ipid']] = array_merge($sapv_days[$v_sapv_data['ipid']], $v_sapv_data['days']);
    				$sapv_days[$v_sapv_data['ipid']] = array_values(array_unique($sapv_days[$v_sapv_data['ipid']]));
    			}
    		}
    		 
    
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			if(!in_array($v_ipid, $selected_sapv_falls_ipids))
    			{
    				if(array_key_exists($v_ipid, $admission_fall))
    				{
    					$selected_period[$v_ipid] = $patient_days[$v_ipid]['active_periods'][$admission_fall[$v_ipid]];
    
    					array_walk($selected_period[$v_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    							
    						$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($selected_period[$v_ipid]['start'], $selected_period[$v_ipid]['end']);
    							
    						array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    
    							$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    							$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    							$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    
    
    							array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    								$value = date("d.m.Y", strtotime($value));
    							});
    									
    								//exclude outside admission falls days from sapv!
    								if(empty($sapv_days[$v_ipid]))
    								{
    									$sapv_days[$v_ipid] = array();
    								}
    									
    								if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    								{
    									$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    								}
    								$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    								$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    									
    								$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    								$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    									
    								$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    								$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    									
    								//get all days of all sapvs in a period
    								$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    								$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    									
    									
    								$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    								$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    									
    								$last_sapv_data['ipid'] = $v_ipid;
    								$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    								$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    								$sapv_last_require_data[] = $last_sapv_data;
    
    								$_REQUEST['admissionid'][$v_ipid] = $admission_fall[$v_ipid];
    				}
    				//ISPC-2461
    				elseif(array_key_exists($v_ipid, $quarter_fall))
    				{
    					$post_q = $_REQUEST[$v_ipid]['selected_period'];
    					$post_q_arr = explode("/",$post_q);
    					$q_no = (int)$post_q_arr[0];
    					$q_year = (int)$post_q_arr[1];
    
    					$q_per = array();
    					$quarter_start = "";
    					$quarter_end = "";
    
    					$q_per = Pms_CommonData::get_dates_of_quarter($q_no,$q_year,'Y-m-d');
    					$quarter_start = $q_per['start'];
    					$quarter_end = $q_per['end'];
    
    					$selected_period[$v_ipid] = array();
    					$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($quarter_start, $quarter_end);
    					$selected_period[$v_ipid]['start'] = $quarter_start;
    					$selected_period[$v_ipid]['end'] = $quarter_end;
    
    					array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    						$value = date("d.m.Y", strtotime($value));
    					});
    							
    						$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    						$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    						$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    							
    							
    						array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    
    							//exclude outside admission falls days from sapv!
    							if(empty($sapv_days[$v_ipid]))
    							{
    								$sapv_days[$v_ipid] = array();
    							}
    
    							if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    							{
    								$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    							}
    							$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    							$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    
    							$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    							$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    
    							$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    							$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    
    							//get all days of all sapvs in a period
    							$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    							$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    
    							$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    							$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    
    							$last_sapv_data['ipid'] = $v_ipid;
    							$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    							$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    							$sapv_last_require_data[] = $last_sapv_data;
    
    							$_REQUEST['quarterid'][$v_ipid] = $quarter_fall[$v_ipid];
    				}
    				else
    				{
    
    					$start_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['start']));
    					$end_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['end']));
    
    					$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    					$_REQUEST['selected_period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    					$_REQUEST['sapv_in_period'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    					$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    					$_REQUEST['period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    					$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    					$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    
    					$last_sapv_data['ipid'] = $v_ipid;
    					$last_sapv_data['start_period'] = date('Y-m-d', strtotime($start_dmy));
    					$last_sapv_data['end_period'] = date('Y-m-d', strtotime($end_dmy));
    					$sapv_last_require_data[] = $last_sapv_data;
    				}
    			}
    		}
    		//print_r($_REQUEST); exit;
    		//get all sapv details
    		$all_sapvs = array();
    		$all_sapvs = $sapvs->get_all_sapvs($ipids);
    		 
    		$sapv2ipid = array();
    		/* foreach($all_sapvs as $k=>$sdata){
    			$sapv2ipid[$sdata['ipid']][] = $sdata;
    		} */
    
    		$has_no_sapv = array();
    		foreach($all_sapvs as $k=>$sv_data){
    			$has_no_sapv[$sv_data['ipid']] = true;
    			if($sv_data['verordnungam'] != "0000-00-00 00:00:00" && $sv_data['verordnungam'] != "1970-01-01 00:00:00" )
    			{
    				$st_date = date('Y-m-d',strtotime($sv_data['verordnungam']));
    				$sapv_period2type[$sv_data['ipid']][$st_date] = $sv_data['verordnet'];
    
    				$sapv_dates[$sv_data['ipid']]['id'] = $sv_data['id'];
    				$sapv_dates[$sv_data['ipid']]['from'] = date('Y-m-d', strtotime($sv_data['verordnungam']));
    				$sapv_dates[$sv_data['ipid']]['till'] = date('Y-m-d', strtotime($sv_data['verordnungbis']));
    				$sapv_dates[$sv_data['ipid']]['type'] = trim($sv_data['verordnet']);
    
    				$sapv_selector_source[$sv_data['ipid']][$sv_data['id']] = $sapv_dates[$sv_data['ipid']];
    				$has_no_sapv[$sv_data['ipid']] = false;
    			}
    		}
    
    
    		foreach($all_sapvs as $k_sapv => $v_sapv)
    		{
    			$sapv2ipid[$v_sapv['ipid']][] = $v_sapv;
    			if(empty($sapv_days_overall[$v_sapv['ipid']]))
    			{
    				$sapv_days_overall[$v_sapv['ipid']] = array();
    			}
    
    			$start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
    
    			if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00')
    			{
    				$end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
    			}
    			else
    			{
    				$end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
    			}
    
    			//FIND A WAY TO GET MULTIPLE LAST SAPV IN EACH PERIOD FOR EACH PATIENT
    			if($last_sapvs_in_period[$v_sapv['ipid']])
    			{
    				$_REQUEST['period'][$v_sapv['ipid']] = array_merge($_REQUEST['period'][$v_sapv['ipid']], $last_sapvs_in_period[$v_sapv['ipid']]);
    			}
    
    			$sapv_days_overall[$v_sapv['ipid']] = array_merge($sapv_days_overall[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end));
    			array_walk($sapv_days_overall[$v_sapv['ipid']], function(&$value) {
    				$value = date("d.m.Y", strtotime($value));
    			});
    				$sapv_days_overall[$v_sapv['ipid']] = array_values(array_unique($sapv_days_overall[$v_sapv['ipid']]));
    		}
    
    		foreach($last_sapvs_in_period as $k_sapvs => $v_sapvs)
    		{
    			foreach($v_sapvs as $k_sapvp => $v_sapvp)
    			{
    				$startp = date('Y-m-d', strtotime($v_sapvp['verordnungam']));
    
    				if($v_sapvp['status'] == '1' && $v_sapvp['verorddisabledate'] != '0000-00-00 00:00:00')
    				{
    					$endp = date('Y-m-d', strtotime($v_sapvp['verorddisabledate']));
    				}
    				else
    				{
    					$endp = date('Y-m-d', strtotime($v_sapvp['verordnungbis']));
    				}
    				if(empty($period_sapv_alldays[$v_sapvp['ipid']]))
    				{
    					$period_sapv_alldays[$v_sapvp['ipid']] = array();
    				}
    				$period_sapv_alldays[$v_sapvp['ipid']] = array_merge($period_sapv_alldays[$v_sapvp['ipid']], PatientMaster::getDaysInBetween($startp, $endp));
    			}
    		}
    
    
    		$_REQUEST['period_sapvs_alldays'] = $period_sapv_alldays;
    		$_REQUEST['sapv_overall'] = $sapv_days_overall;
    
    		$current_period = array();
    		foreach($_REQUEST['period'] as $ipidp => $vipidp)
    		{
    			$current_period[$ipidp] = $vipidp;
    			if($_REQUEST['sapv_in_period'][$ipidp])
    			{
    				$sapv_in_period[$ipidp] = $_REQUEST['sapv_in_period'][$ipidp];
    			}
    			else
    			{
    				$sapv_in_period[$ipidp] = array();
    			}
    		}
    
    		//Healthinsurance
    		$healthinsu_multi_array = array();
    		$healthinsu_multi_array = $phelathinsurance->get_multiple_patient_healthinsurance($ipids, true);
    
    		foreach($healthinsu_multi_array as $k_hi => $v_hi)
    		{
    			$hi_companyids[] = $v_hi['companyid'];
    		}

    		//multiple hi subdivisions && hi subdivisions permissions
    		$divisions = HealthInsurancePermissions::getClientHealthInsurancePermissions($clientid);
    		 
    		//patientheathinsurance
    		if($divisions)
    		{
    			/* foreach($healthinsu_multi_array as $k_hi => $v_hi)
    			 {
    			 $hi_companyids[] = $v_hi['companyid'];
    			 } */
    
    			$healthinsu_subdiv_arr = PatientHealthInsurance2Subdivisions::get_hi_subdivisions_multiple($hi_companyids);
    		}
    		 
    		$pathelathinsu = array();
    		$patient_address = array();
    		$hi_address = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$pathelathinsu[$v_ipid]['insurance_no'] = $healthinsu_multi_array[$v_ipid]['insurance_no'];
    			if($healthinsu_multi_array[$v_ipid]['company_name'] != "")
    			{
    				$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company_name'];
    			}
    			else
    			{
    				$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company']['name'];
    			}
    
    			if($healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				//			get patient name and adress
    				$patient_address[$v_ipid] = '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['first_name']) . ' ' . htmlspecialchars($patient_days[$v_ipid]['details']['last_name']) . '<br />';
    				$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['street1']) . '<br />';
    				$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['zip']) . ' ' . '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['city']);
    			}
    
    			/* if(!empty($healthinsu_subdiv_arr[$v_ipid]['1']['name']))
    			 {
    			 //get new SAPV hi address
    			 $hi_address[$v_ipid] = '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['name'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['insurance_provider'] . '<br />';
    			 //$hi_address .= '&nbsp;' . $healthinsu_subdiv_arr[1]['contact_person'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['street1'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['zip'] . ' ' . $healthinsu_subdiv_arr[$v_ipid][1]['city'];
    
    			 $pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_subdiv_arr[$v_ipid][1]['iknumber'];
    			 $pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_subdiv_arr[$v_ipid][1]['kvnumber'];
    			 }
    			 else
    			 { */
    			//get old hi_address
    			$hi_address[$v_ipid] = '&nbsp;' . $pathelathinsu[$v_ipid]['name'] . '<br />';
    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_insurance_provider'] . '<br />';
    			//$hi_address .= '&nbsp;' . $healthinsu_array[0]['ins_contactperson'] . '<br />';
    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_street'] . '<br />';
    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_zip'] . ' ' . $healthinsu_multi_array[$v_ipid]['ins_city'];
    
    			$pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_multi_array[$v_ipid]['institutskennzeichen'];
    			$pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_multi_array[$v_ipid]['company']['kvk_no'];
    			$pathelathinsu[$v_ipid]['health_insurance_status'] = $healthinsu_multi_array[$v_ipid]['insurance_status'];
    			//}
    
    			//new columns
    			if($clientModules['90'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "0")
    			{
    				//get debtor number from patient healthinsurance
    				if(strlen($healthinsu_multi_array[$v_ipid]['ins_debtor_number']) > '0')
    				{
    					$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['ins_debtor_number'];
    				}
    				else
    				{
    					$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['company']['debtor_number'];
    				}
    			}
    			if($clientModules['88'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				//get ppun (private patient unique number)
    				$ppun_number = $ppun->check_patient_ppun($v_ipid, $clientid);
    				if($ppun_number)
    				{
    					$pathelathinsu[$v_ipid]['ppun']= $ppun_number['ppun'];
    					$pathelathinsu[$v_ipid]['debitor_number']= $ppun_number['ppun'];
    				}
    			}
    			//--
    
    			foreach($patients_discharge as $patient_discharge)
    			{
    				if(in_array($patient_discharge['discharge_method'], $death_methods))
    				{
    					$discharge_dead_date[$v_ipid] = date('Y-m-d', strtotime($patient_discharge['discharge_date']));
    					$discharge_dead_date_time[$v_ipid] = date('Y-m-d H:i:00', strtotime($patient_discharge['discharge_date']));
    				}
    			}
    
    			$first_sapv_id[$v_ipid] = $sapv2ipid[$v_ipid][0]['id'];
    
    			//erstverordnung
    			$sapv_erst[$v_ipid] = '0';
    
    			if(count($sapv2ipid[$v_ipid]) >= '1' && $_REQUEST[$v_ipid]['selected_period'] == $first_sapv_id[$v_ipid] && $has_no_sapv[$v_ipid] === false)
    			{
    				$sapv_erst[$v_ipid] = '1';
    			}
    
    			//folgeverordnung
    			$sapv_folge[$v_ipid] = '0';
    			if(count($sapv2ipid[$v_ipid]) > '1' && $has_no_sapv[$v_ipid] === false)
    			{
    				$sapv_folge[$v_ipid] = '1';
    			}
    
    			if($has_no_sapv[$v_ipid] === false)
    			{
    				//get curent verordnung date from-till
    				$curent_sapv_from[$v_ipid] = $sapv_selector_source[$v_ipid][$_REQUEST[$v_ipid]['selected_period']]['from'];
    				$curent_sapv_till[$v_ipid] = $sapv_selector_source[$v_ipid][$_REQUEST[$v_ipid]['selected_period']]['till'];
    				$curent_sapv_type[$v_ipid] = $sapv_selector_source[$v_ipid][$_REQUEST[$v_ipid]['selected_period']]['type'];
    
    				//overide the end of selected sapv period with discharge date
    				if(strlen($discharge_dead_date[$v_ipid]) > 0)
    				{
    					if(strtotime($curent_sapv_till[$v_ipid]) > strtotime($discharge_dead_date[$v_ipid]))
    					{
    						$curent_sapv_till[$v_ipid] = date('Y-m-d', strtotime($discharge_dead_date[$v_ipid]));
    					}
    
    					if(strtotime($curent_sapv_from[$v_ipid]) > strtotime($discharge_dead_date[$v_ipid]))
    					{
    						$curent_sapv_from[$v_ipid] = date('Y-m-d', strtotime($discharge_dead_date[$v_ipid]));
    					}
    				}
    					
    					
    					
    				// check if there were sapv periods with only BE
    				foreach($sapv_period2type[$v_ipid] as $per_start => $per_type )
    				{
    					if(strtotime($per_start)  < strtotime($curent_sapv_from[$v_ipid])  && $per_type == "1")
    					{
    						$only_be_before[$v_ipid][] =  $per_start ;
    					} else {
    						$execpt_be[$v_ipid][] =  $per_start ;
    					}
    				}
    					
    				/* the patient gets ANOTHER Verordnung AFTER that Beratung verordnung then there is one rule to be applied
    				 if the next admission is within 28 days after the Beratung admission, NO assessment is again billed
    				 if the next admission is AFTER 28 days after the Beratung admission, a "reduziertes Assessment" is only billed */
    					
    				// if before the current sapv patient had only - check the admision
    					
    				$curent_sapv_from_f[$v_ipid] = date('d.m.Y', strtotime($curent_sapv_from[$v_ipid]));
    				$curent_sapv_till_f[$v_ipid] = date('d.m.Y', strtotime($curent_sapv_till[$v_ipid]));
    					
    				/* $this->view->curent_sapv_from = $curent_sapv_from_f;
    				 $this->view->curent_sapv_till = $curent_sapv_till_f;
    
    				 $this->view->invoice_date_from = $curent_sapv_from_f;
    				 $this->view->invoice_date_till = $curent_sapv_till_f;*/
    
    				 $curent_period[$v_ipid]['start'] = $curent_sapv_from[$v_ipid];
    				 $curent_period[$v_ipid]['end'] = $curent_sapv_till[$v_ipid];
    				 $pd_curent_period[$v_ipid]['start'] = $curent_sapv_from[$v_ipid];
    				 $pd_curent_period[$v_ipid]['end'] = $curent_sapv_till[$v_ipid];
    					
    			}
    
    			// if patient had an only be before
    			$bill_assessment[$v_ipid] = 1;
    			$bill_secondary_assessment[$v_ipid] = 0;
    			if(isset($only_be_before[$v_ipid]) && !empty($only_be_before[$v_ipid])){
    				$admission_days[$v_ipid] = $patient_days[$v_ipid]['admission_days'];
    					
    				$last_only_be[$v_ipid] = end($only_be_before[$v_ipid]);
    				$last_admission_date[$v_ipid]  = end($admission_days[$v_ipid]);
    					
    				if(strtotime($last_only_be[$v_ipid]) < strtotime($last_admission_date[$v_ipid])){
    					$from_sapv_be2patient_admision[$v_ipid] = $patientmaster->getDaysInBetween($last_only_be[$v_ipid], $last_admission_date[$v_ipid]);
    					if(count($from_sapv_be2patient_admision[$v_ipid]) < 28 ){
    						// if the next admission is within 28 days after the Beratung admission, NO assessment is again billed
    						$bill_assessment[$v_ipid] = 0;
    						$bill_secondary_assessment[$v_ipid] = 0;
    							
    					} else {
    						//if the next admission is AFTER 28 days after the Beratung admission, a "reduziertes Assessment" is only billed
    						$bill_assessment[$v_ipid] = 0;
    						$bill_secondary_assessment[$v_ipid] = 1;
    					}
    				}
    			}
    
    			//get patient locations and construct day2location_type arr
    			$pat_locations[$v_ipid] = PatientLocation::get_period_locations($v_ipid, $curent_period[$v_ipid]);
   
    			$pat_days2loctype[$v_ipid] = array();
    			foreach($pat_locations[$v_ipid] as $k_pat => $v_pat)
    			{
    				if($v_pat['discharge_location'] == "0")
    				{
    					foreach($v_pat['all_days'] as $k_day => $v_day)
    					{
    						if(in_array(date("d.m.Y",strtotime($v_day)),$patient_days[$v_ipid]['real_active_days']) )
    						{ // allow only location days that are included in patient active days
    							$pat_days2loctype[$v_ipid][$v_day][] = $v_pat['master_details']['location_type'];
    						}
    					}
    				}
    			}
    
    			// TODO-2722 Ancuta 10.12.2019 - move patient location so locations according to client settings
    			foreach($pat_days2loctype[$v_ipid]  as $loc_day => $day_loc_types ){
    					
    				$del_val = "1";
    				if (  ! in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$v_ipid]['hospital']['real_days_cs']) && ($key = array_search($del_val, $day_loc_types)) !== false) {
    					unset($pat_days2loctype[$v_ipid][$loc_day][$key]);
    				}
    					
    				$del_val = "2";
    				if (  ! in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$v_ipid]['hospiz']['real_days_cs']) && ($key = array_search($del_val, $day_loc_types)) !== false) {
    					unset($pat_days2loctype[$v_ipid][$loc_day][$key]);
    				}
    			}
    
    			foreach($pat_days2loctype[$v_ipid] as $loc_day => $day_loc_types){
    				if (in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$v_ipid]['hospital']['real_days_cs']) ) {
    					$pat_days2loctype[$v_ipid][$loc_day] = '1';
    				}
    				elseif (in_array(date("d.m.Y",strtotime($loc_day)),$patient_days[$v_ipid]['hospiz']['real_days_cs']) ) {
    					$pat_days2loctype[$v_ipid][$loc_day] = '2';
    				} else{
    					$pat_days2loctype[$v_ipid][$loc_day] = end($day_loc_types);
    				}
    					
    			}
    			//--
    
    			if(!$curent_sapv_from[$v_ipid])
    			{
    				$pr_date_from[$v_ipid] = $pr_date_till[$v_ipid] = date('Y-m-d', time());
    				$ppl[$v_ipid] = PriceList::get_period_price_list($pr_date_from[$v_ipid], $pr_date_till[$v_ipid]);
    			}
    			else
    			{
    				$ppl[$v_ipid] = PriceList::get_period_price_list($curent_sapv_from[$v_ipid], $curent_sapv_till[$v_ipid]);
    			}
    
    			foreach($shortcuts['rp'] as $k_short => $v_short)
    			{
    				if(!$curent_sapv_from[$v_ipid])
    				{
    					$price_date[$v_ipid] = date('Y-m-d', time());
    				}
    				else
    				{
    					$price_date[$v_ipid] = $curent_sapv_from[$v_ipid];
    				}
    				$products[$v_ipid][$v_short]['shortcut'] = $v_short;
    				$products[$v_ipid][$v_short]['price'] = '';
    				$products[$v_ipid][$v_short]['qty_gr']['p_home'] = '0';
    				$products[$v_ipid][$v_short]['price_gr']['p_home'] = $ppl[$v_ipid][$price_date[$v_ipid]][0][$v_short]['p_home'];
    				$products[$v_ipid][$v_short]['total']['p_home'] = '0.00';
    					
    				$products[$v_ipid][$v_short]['qty_gr']['p_nurse'] = '0';
    				$products[$v_ipid][$v_short]['price_gr']['p_nurse'] = $ppl[$v_ipid][$price_date[$v_ipid]][0][$v_short]['p_nurse'];
    				$products[$v_ipid][$v_short]['total']['p_nurse'] = '0.00';
    					
    				$products[$v_ipid][$v_short]['qty_gr']['p_hospiz'] = '0';
    				$products[$v_ipid][$v_short]['price_gr']['p_hospiz'] = $ppl[$v_ipid][$price_date[$v_ipid]][0][$v_short]['p_hospiz'];
    				$products[$v_ipid][$v_short]['total']['p_hospiz'] = '0.00';
    			}
    
    			//get curent contact forms
    			$contact_forms[$v_ipid] = $this->get_period_contact_forms($v_ipid, $curent_period[$v_ipid], false, false, true);
    
    			$doctor_contact_forms[$v_ipid] = array();
    			$nurse_contact_forms[$v_ipid] = array();
    			ksort($contact_forms[$v_ipid]);
    			foreach($contact_forms[$v_ipid] as $kcf => $day_cfs)
    			{
    				foreach($day_cfs as $k_dcf => $v_dcf)
    				{
    					if(!empty($discharge_dead_date_time[$v_ipid])){
    						if(strtotime($v_dcf['start_date']) <= strtotime($discharge_dead_date_time[$v_ipid])){ // excude if the visit started after the discharge dead hour
    							$all_contact_forms[$v_ipid][] = $v_dcf;
    						}
    					}
    					else
    					{
    						$all_contact_forms[$v_ipid][] = $v_dcf;
    					}
    
    				}
    			}
    
    			$contact_forms2date[$v_ipid] = array();
    			foreach($all_contact_forms[$v_ipid] as $k_cf => $v_cf)
    			{
    				//visit date formated
    				$visit_date[$v_ipid] = date('Y-m-d', strtotime($v_cf['billable_date']));
    					
    				//switch shortcut_type based on patient location for *visit* date
    				$vday_matched_loc_price_type[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$visit_date]];
    					
    				//switch shortcut doctor/nurse
    				$shortcut_switch[$v_ipid] = false;
    				if(in_array($v_cf['create_user'], $doctor_users))
    				{
    					$shortcut_switch[$v_ipid] = 'doc';
    				}
    				else if(in_array($v_cf['create_user'], $nurse_users))
    				{
    					$shortcut_switch[$v_ipid] = 'nur';
    				}
    					
    				//create products (doc||nurse)
    				if(strlen($vday_matched_loc_price_type[$v_ipid]) > 0 && $shortcut_switch[$v_ipid] && in_array($v_cf['form_type'], $set_one_ids))
    				{
    					if($ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    					{
    						$contact_forms2date[$v_ipid][date('Y-m-d', strtotime($v_cf['billable_date']))][] = $v_cf;
    					}
    				}
    			}
    
    			//check if patient has saved data in db
    			$saved_data[$v_ipid] = RpControl::get_rp_controlsheet($v_ipid, $curent_period[$v_ipid]['start'], $curent_period[$v_ipid]['end']);
    
    			if($saved_data[$v_ipid])
    			{
    				//reconstruct array
    				foreach($saved_data[$v_ipid] as $k_shortcut => $v_sv_data)
    				{
    					$saved_data_arr[$v_ipid][$k_shortcut]['shortcut'] = $k_shortcut;
    
    					foreach($v_sv_data as $k_date => $v_qty)
    					{
    						if($ppl[$v_ipid][$k_date][0][$k_shortcut]['p_home'] != '0.00')
    						{
    							$saved_data_arr[$v_ipid][$k_shortcut]['qty_gr']['p_home'] += $v_qty['p_home'];
    							$saved_data_arr[$v_ipid][$k_shortcut]['price_gr']['p_home'] = $ppl[$v_ipid][$k_date][0][$k_shortcut]['p_home'];
    							$saved_data_arr[$v_ipid][$k_shortcut]['total']['p_home'] += ($v_qty['p_home'] * $ppl[$v_ipid][$k_date][0][$k_shortcut]['p_home']);
    							if($v_qty['p_home'] != 0  && !empty($contact_forms2date[$v_ipid][$k_date])){
    								$dates[$v_ipid][$k_shortcut][$k_date] += $v_qty['p_home'];
    							}
    						}
    							
    						if($ppl[$v_ipid][$k_date][0][$k_shortcut]['p_nurse'] != '0.00')
    						{
    							$saved_data_arr[$v_ipid][$k_shortcut]['qty_gr']['p_nurse'] += $v_qty['p_nurse'];
    							$saved_data_arr[$v_ipid][$k_shortcut]['price_gr']['p_nurse'] = $ppl[$v_ipid][$k_date][0][$k_shortcut]['p_nurse'];
    							$saved_data_arr[$v_ipid][$k_shortcut]['total']['p_nurse'] += ($v_qty['p_nurse'] * $ppl[$v_ipid][$k_date][0][$k_shortcut]['p_nurse']);
    							if($v_qty['p_nurse'] != 0   && !empty($contact_forms2date[$v_ipid][$k_date]) ){
    								$dates[$v_ipid][$k_shortcut][$k_date] += $v_qty['p_nurse'];
    							}
    						}
    							
    						if($ppl[$v_ipid][$k_date][0][$k_shortcut]['p_hospiz'] != '0.00')
    						{
    							$saved_data_arr[$v_ipid][$k_shortcut]['qty_gr']['p_hospiz'] += $v_qty['p_hospiz'];
    							$saved_data_arr[$v_ipid][$k_shortcut]['price_gr']['p_hospiz'] = $ppl[$v_ipid][$k_date][0][$k_shortcut]['p_hospiz'];
    							$saved_data_arr[$v_ipid][$k_shortcut]['total']['p_hospiz'] += ($v_qty['p_hospiz'] * $ppl[$v_ipid][$k_date][0][$k_shortcut]['p_hospiz']);
    							if($v_qty['p_hospiz'] != 0   && !empty($contact_forms2date[$v_ipid][$k_date]) ){
    								$dates[$v_ipid][$k_shortcut][$k_date] += $v_qty['p_hospiz'];
    							}
    						}
    					}
    				}
    					
    				$products[$v_ipid] = $saved_data_arr[$v_ipid];
    			}
    
    			foreach($dates[$v_ipid] as $dn_sh=>$visits_values)
    			{
    				if($dn_sh == "rp_doc_2" || $dn_sh == "rp_nur_2")
    				{
    					foreach($visits_values as $date=>$saved_qty)
    					{
    						if($saved_qty != 0 && (count($contact_forms2date[$v_ipid][$date]) <= $saved_qty) )
    						{
    							foreach($contact_forms2date[$v_ipid][$date] as $cfk=>$cf_data)
    							{
    								$extra_data[$v_ipid]['home_visit'][$cf_data['id']] = $cf_data;
    							}
    						}
    						else if($saved_qty != 0 && (count($contact_forms2date[$v_ipid][$date]) > $saved_qty) )
    						{
    							$cfs[$date] = 0;
    
    							foreach($contact_forms2date[$v_ipid][$date] as $cfk=>$cf_data)
    							{
    								if( $cfs[$date] <= $saved_qty )
    								{
    									$extra_data[$v_ipid]['home_visit'][$cf_data['id']] = $cf_data;
    									$cfs[$date]++;
    								}
    							}
    						}
    					}
    				}
    			}
    
    			$products[$v_ipid]['grand_total'] = (float)0;
    
    			if(!$saved_data[$v_ipid])
    			{
    				if($has_no_sapv[$v_ipid] === false)
    				{
    					if($curent_sapv_type[$v_ipid] == "1") // only BE
    					{
    						$rp_asses[$v_ipid] = Rpassessment::get_patient_completed_rpassessment($v_ipid, $curent_period[$v_ipid]);
    							
    						if(!empty($rp_asses[$v_ipid])){
    							$rp_assessment_final[$v_ipid][0] = $rp_asses[$v_ipid][0];
    
    							$v_assessment[$v_ipid] = $rp_assessment_final[$v_ipid][0];
    							// 							foreach($rp_assessment_final as $k_assessment => $v_assessment)
    							// 							{
    							$location_matched_price[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$v_assessment[$v_ipid]['completed_date']]];
    
    							if(strlen($location_matched_price[$v_ipid]) > 0)
    							{
    								//found saved data for day of assessment completion
    								if(strlen($saved_data[$v_ipid]['rp_eb_1'][$v_assessment[$v_ipid]['completed_date']][$location_matched_price[$v_ipid]]))
    								{
    									$products[$v_ipid]['rp_eb_1']['qty_gr'][$location_matched_price[$v_ipid]] += $saved_data[$v_ipid]['rp_eb_1'][$v_assessment[$v_ipid]['completed_date']][$location_matched_price[$v_ipid]];
    									$products[$v_ipid]['rp_eb_1']['price'] = $ppl[$v_ipid][$v_assessment[$v_ipid]['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]];
    									$products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] = ($products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] + ($saved_data_arr[$v_ipid]['rp_eb_1'][$v_assessment[$v_ipid]['completed_date']][$location_matched_price[$v_ipid]] * $ppl[$v_ipid][$v_assessment[$v_ipid]['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]]));
    									$products[$v_ipid]['rp_eb_1']['source']['saved_data'][$sapvday_loc_matched_price[$v_ipid]][$v_assessment[$v_ipid]['completed_date']] += $saved_data[$v_ipid]['rp_eb_1'][$v_assessment[$v_ipid]['completed_date']][$location_matched_price[$v_ipid]];
    									$excluded_saved_data_days[$v_ipid]['rp_eb_1'][] = $v_assessment[$v_ipid]['completed_date'];
    
    									$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]];
    								}
    								//no saved data, load system data instead
    								else
    								{
    									$products[$v_ipid]['rp_eb_1']['qty_gr'][$location_matched_price[$v_ipid]] += '1';
    									$products[$v_ipid]['rp_eb_1']['price'] = $ppl[$v_ipid][$v_assessment[$v_ipid]['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]];
    									$products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] = ($products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] + $ppl[$v_ipid][$v_assessment[$v_ipid]['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]]);
    									$products[$v_ipid]['rp_eb_1']['source']['system_data'][$location_matched_price[$v_ipid]][$v_assessment[$v_ipid]['completed_date']] += '1';
    
    									$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]];
    								}
    							}
    
    							// 							}
    							}
    
    
    
    							$visit_cnt[$v_ipid]['rp_doc_1'] = 0;
    							$visit_cnt[$v_ipid]['rp_doc_2'] = 0;
    							$visit_cnt[$v_ipid]['rp_doc_3'] = 0;
    							$visit_cnt[$v_ipid]['rp_doc_4'] = 0;
    
    							$visit_cnt[$v_ipid]['rp_nur_1'] = 0;
    							$visit_cnt[$v_ipid]['rp_nur_2'] = 0;
    							$visit_cnt[$v_ipid]['rp_nur_3'] = 0;
    							$visit_cnt[$v_ipid]['rp_nur_4'] = 0;
    
    							//DOCTOR and NURSE VISITS - all
    							foreach($all_contact_forms[[$v_ipid]] as $k_cf => $v_cf)
    							{
    								//visit date formated
    								$visit_date[$v_ipid] = date('Y-m-d', strtotime($v_cf['billable_date']));
    									
    								//switch shortcut_type based on patient location for *visit* date
    								$vday_matched_loc_price_type[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$visit_date[[$v_ipid]]]];
    									
    								//switch shortcut doctor/nurse
    								$shortcut_switch[$v_ipid] = false;
    								if(in_array($v_cf['create_user'], $doctor_users))
    								{
    									$shortcut_switch[$v_ipid] = 'doc';
    								}
    								else if(in_array($v_cf['create_user'], $nurse_users))
    								{
    									$shortcut_switch[$v_ipid] = 'nur';
    								}
    									
    								//create products (doc||nurse)
    								if(strlen($vday_matched_loc_price_type[$v_ipid]) > 0 && $shortcut_switch[$v_ipid] && in_array($v_cf['form_type'], $set_one_ids)
    										)
    								{
    									//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
    									if($ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    									{
    										//overide with saved data
    										if(strlen($saved_data[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]) > '0')
    										{
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $saved_data[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]];
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['extra_data'] = $v_cf;
    											$extra_data['home_visit'][$v_cf['id']] = $v_cf;
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]]));
    											$excluded_saved_data_days[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'][] = $visit_date[$v_ipid];
    
    											$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]];
    										}
    										else
    										{
    
    											if($visit_cnt[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'] == 0 ){
    												$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += 1;
    												$extra_data[$v_ipid]['home_visit'][$v_cf['id']] = $v_cf;
    												$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]] + $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]]);
    												$visit_cnt[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']++;
    
    												$prod_vis_ident[$v_ipid][$shortcut_switch[$v_ipid]][] = $v_cf['id'];
    													
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    										}
    											
    											
    										$shortcut[$v_ipid] = '';
    										$qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] = '';
    									}
    
    									//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 45 (rp_doc_1||rp_nur_1)
    									if($v_cf['visit_duration'] >= '0')
    									{
    										$shortcut[$v_ipid] = 'rp_' . $shortcut_switch[$v_ipid] . '_1';
    										$qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] = '1';
    											
    										if($shortcut[$v_ipid] && $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] && $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    										{
    											//overide with saved data
    											if(strlen($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]) > '0')
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]));
    												$excluded_saved_data_days[$v_ipid][$shortcut[$v_ipid]][] = $visit_date[$v_ipid];
    													
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    											else
    											{
    												if($visit_cnt[$v_ipid][$shortcut[$v_ipid]] == 0  && in_array($v_cf['id'],$prod_vis_ident[$v_ipid][$shortcut_switch[$v_ipid]])){
    													$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $qty[$vday_matched_loc_price_type[$v_ipid]];
    													$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    													$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]]));
    													$visit_cnt[$v_ipid][$shortcut[$v_ipid]]++;
    
    													$products[$v_ipid]['grand_total'] += $products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]];
    
    
    												}
    											}
    										}
    										$shortcut[$v_ipid] = '';
    									}
    
    									//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
    									if($v_cf['visit_duration'] > '45')
    									{
    										// calculate multiplier of 15 minutes after 60 min (round up)
    										// ISPC-2006 29.06.2017 :: From 60 was changed to 45
    										// calculate multiplier of 15 minutes after 45 min (round up)
    
    										$shortcut[$v_ipid] = 'rp_' . $shortcut_switch[$v_ipid] . '_3';
    										$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
    										$qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] = $multiplier; //multiplier value
    											
    										if($shortcut[$v_ipid] && $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] && $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    										{
    
    											//overide with saved data
    											if(strlen($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]) > '0')
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $saved_data[$shortcut][$visit_date][$vday_matched_loc_price_type];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]));
    												$excluded_saved_data_days[$v_ipid][$shortcut[$v_ipid]][] = $visit_date[$v_ipid];
    													
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    											else
    											{
    												if($visit_cnt[$v_ipid][$shortcut[$v_ipid]] == 0  && in_array($v_cf['id'],$prod_vis_ident[$v_ipid][$shortcut_switch[$v_ipid]])){
    													$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $qty[$v_ipid][$vday_matched_loc_price_type][$v_ipid];
    													$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    													$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]]));
    													$visit_cnt[$v_ipid][$shortcut[$v_ipid]]++;
    
    													$products[$v_ipid]['grand_total'] += $products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]];
    												}
    											}
    										}
    										$shortcut[$v_ipid] = '';
    									}
    
    									//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
    									if($v_cf['visit_duration'] < '20')
    									{
    										$shortcut[$v_ipid] = 'rp_' . $shortcut_switch . '_4';
    										$qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] = '1';
    											
    										if($shortcut[$v_ipid] && $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] && $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    										{
    											//overide with saved data
    											if(strlen($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]) > '0')
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]));
    												$excluded_saved_data_days[$v_ipid][$shortcut[$v_ipid]][] = $visit_date[$v_ipid];
    													
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    											else
    											{
    												if($visit_cnt[$v_ipid][$shortcut[$v_ipid]] == 0  && in_array($v_cf['id'],$prod_vis_ident[$v_ipid][$shortcut_switch[$v_ipid]])){
    													$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]];
    													$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    													$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]]));
    													$visit_cnt[$v_ipid][$shortcut[$v_ipid]]++;
    
    													$products[$v_ipid]['grand_total'] += $products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]];
    												}
    											}
    										}
    										$shortcut[$v_ipid] = '';
    									}
    								}
    							}
    
    						}
    						else
    						{
    
    							//--if the next admission is within 28 days after the Beratung admission, NO assessment is again billed
    							//--if the next admission is AFTER 28 days after the Beratung admission, a "reduziertes Assessment" is only billed
    
    
    							if($bill_assessment[$v_ipid] == "1"){
    								//GATHER INVOICE ITEMS START
    								//Ebene 1 (Assessment / Beratung) - RP-Assessment marked as completed
    								$rp_asses[$v_ipid] = Rpassessment::get_patient_completed_rpassessment($v_ipid, $curent_period[$v_ipid]);
    									
    								foreach($rp_asses[$v_ipid] as $k_assessment => $v_assessment)
    								{
    									$location_matched_price[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$v_assessment['completed_date']]];
    
    									if(strlen($location_matched_price[$v_ipid]) > 0)
    									{
    										//found saved data for day of assessment completion
    										if(strlen($saved_data[$v_ipid]['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price[$v_ipid]]))
    										{
    											$products[$v_ipid]['rp_eb_1']['qty_gr'][$location_matched_price[$v_ipid]] += $saved_data[$v_ipid]['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price[$v_ipid]];
    											$products[$v_ipid]['rp_eb_1']['price'] = $ppl[$v_ipid][$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]];
    											$products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] = ($products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] + ($saved_data_arr[$v_ipid]['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price[$v_ipid]] * $ppl[$v_ipid][$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]]));
    											$products[$v_ipid]['rp_eb_1']['source']['saved_data'][$sapvday_loc_matched_price[$v_ipid]][$v_assessment['completed_date']] += $saved_data[$v_ipid]['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price[$v_ipid]];
    											$excluded_saved_data_days[$v_ipid]['rp_eb_1'][] = $v_assessment['completed_date'];
    
    
    										}
    										//no saved data, load system data instead
    										else
    										{
    											$products[$v_ipid]['rp_eb_1']['qty_gr'][$location_matched_price[$v_ipid]] += '1';
    											$products[$v_ipid]['rp_eb_1']['price'] = $ppl[$v_ipid][$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]];
    											$products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] = ($products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] + $ppl[$v_ipid][$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]]);
    											$products[$v_ipid]['rp_eb_1']['source']['system_data'][$location_matched_price[$v_ipid]][$v_assessment['completed_date']] += '1';
    
    
    										}
    									}
    								}
    								$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]];
    
    							}
    							else
    							{
    								//Ebene 1 (reduziertes Assessment) - Not used yet (saved data for this shortcut as is not calculated by system)
    								if($bill_secondary_assessment[$v_ipid] == "1"){
    									$rp_asses[$v_ipid] = Rpassessment::get_patient_completed_rpassessment($v_ipid, $curent_period[$v_ipid]);
    
    									foreach($rp_asses[$v_ipid] as $k_assessment => $v_assessment)
    									{
    										$location_matched_price[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$v_assessment['completed_date']]];
    											
    										if(strlen($location_matched_price[$v_ipid]) > 0)
    										{
    											//found saved data for day of assessment completion
    											if(strlen($saved_data[$v_ipid]['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price[$v_ipid]]))
    											{
    												$products[$v_ipid]['rp_eb_2']['qty_gr'][$location_matched_price[$v_ipid]] += $saved_data[$v_ipid]['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price[$v_ipid]];
    												$products[$v_ipid]['rp_eb_2']['price'] = $ppl[$v_ipid][$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]];
    												$products[$v_ipid]['rp_eb_2']['total'][$location_matched_price[$v_ipid]] = ($products['rp_eb_1']['total'][$location_matched_price[$v_ipid]] + ($saved_data_arr[$v_ipid]['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price] * $ppl[$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]]));
    												$products[$v_ipid]['rp_eb_2']['source']['saved_data'][$sapvday_loc_matched_price[$v_ipid]][$v_assessment['completed_date']] += $saved_data[$v_ipid]['rp_eb_1'][$v_assessment['completed_date']][$location_matched_price[$v_ipid]];
    												$excluded_saved_data_days[$v_ipid]['rp_eb_1'][] = $v_assessment['completed_date'];
    
    
    											}
    											//no saved data, load system data instead
    											else
    											{
    												$products[$v_ipid]['rp_eb_2']['qty_gr'][$location_matched_price[$v_ipid]] += '1';
    												$products[$v_ipid]['rp_eb_2']['price'] = $ppl[$v_ipid][$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]];
    												$products[$v_ipid]['rp_eb_2']['total'][$location_matched_price[$v_ipid]] = ($products[$v_ipid]['rp_eb_1']['total'][$location_matched_price[$v_ipid]] + $ppl[$v_ipid][$v_assessment['completed_date']][0]['rp_eb_1'][$location_matched_price[$v_ipid]]);
    												$products[$v_ipid]['rp_eb_2']['source']['system_data'][$location_matched_price[$v_ipid]][$v_assessment['completed_date']] += '1';
    
    
    											}
    										}
    									}
    									$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_eb_2']['total'][$sapvday_loc_matched_price[$v_ipid]];
    									$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_eb_2']['total'][$location_matched_price[$v_ipid]];
    								}
    
    							}
    
    
    							//Ebene 2 - the daily added price when patient is active and has Verordnung
    							$sapv_days[$v_ipid] = $patientmaster->getDaysInBetween($curent_sapv_from[$v_ipid], $curent_sapv_till[$v_ipid]);
    							$sapv_days[$v_ipid] = array_values(array_unique($sapv_days[$v_ipid]));
    
    							foreach($sapv_days[$v_ipid] as $k_sapv_day => $v_sapv_day)
    							{
    								$sapvday_loc_matched_price[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$v_sapv_day]];
    									
    								if(strlen($sapvday_loc_matched_price[$v_ipid]) > 0 && $ppl[$v_ipid][$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price[$v_ipid]] != '0.00')
    								{
    									if(strlen($saved_data[$v_ipid]['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price[$v_ipid]]) > '0')
    									{
    										$products[$v_ipid]['rp_eb_3']['qty_gr'][$sapvday_loc_matched_price[$v_ipid]] += $saved_data[$v_ipid]['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price[$v_ipid]];
    										$products[$v_ipid]['rp_eb_3']['price'] = $ppl[$v_ipid][$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price[$v_ipid]];
    										$products[$v_ipid]['rp_eb_3']['source']['saved_data'][$sapvday_loc_matched_price[$v_ipid]][$v_sapv_day] += $saved_data[$v_ipid]['rp_eb_3'][$v_sapv_day][$sapvday_loc_matched_price[$v_ipid]];
    										$excluded_saved_data_days[$v_ipid]['rp_eb_3'][] = $v_sapv_day;
    									}
    									else
    									{
    										$products[$v_ipid]['rp_eb_3']['qty_gr'][$sapvday_loc_matched_price[$v_ipid]] += 1;
    										$products[$v_ipid]['rp_eb_3']['price'] = $ppl[$v_ipid][$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price[$v_ipid]];
    										$products[$v_ipid]['rp_eb_3']['source']['system_data'][$sapvday_loc_matched_price[$v_ipid]][$v_sapv_day] += '1';
    									}
    
    									$products[$v_ipid]['rp_eb_3']['total'][$sapvday_loc_matched_price[$v_ipid]] = ($products[$v_ipid]['rp_eb_3']['qty_gr'][$sapvday_loc_matched_price[$v_ipid]] * $ppl[$v_ipid][$v_sapv_day][0]['rp_eb_3'][$sapvday_loc_matched_price[$v_ipid]]);
    
    
    								}
    							}
    							$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_eb_3']['total'][$sapvday_loc_matched_price[$v_ipid]];
    
    							//DOCTOR and NURSE VISITS - all
    							foreach($all_contact_forms[$v_ipid] as $k_cf => $v_cf)
    							{
    								//visit date formated
    								$visit_date[$v_ipid] = date('Y-m-d', strtotime($v_cf['billable_date']));
    									
    								//switch shortcut_type based on patient location for *visit* date
    								$vday_matched_loc_price_type[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$visit_date[$v_ipid]]];
    									
    								//switch shortcut doctor/nurse
    								$shortcut_switch[$v_ipid] = false;
    								if(in_array($v_cf['create_user'], $doctor_users))
    								{
    									$shortcut_switch[$v_ipid] = 'doc';
    								}
    								else if(in_array($v_cf['create_user'], $nurse_users))
    								{
    									$shortcut_switch[$v_ipid] = 'nur';
    								}
    									
    								//create products (doc||nurse)
    								if(strlen($vday_matched_loc_price_type[$v_ipid]) > 0 && $shortcut_switch[$v_ipid] && in_array($v_cf['form_type'], $set_one_ids)
    										)
    								{
    									//DOCTOR VISITS - Vor- und Nachbereitung Arzt - price for the preparation of the doctor visit (no matter how long). ONCE per visit (rp_doc_2||rp_nur_2)
    									if($ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    									{
    										//overide with saved data
    										if(strlen($saved_data[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]) > '0')
    										{
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $saved_data[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['price'] = $ppl[$v_ipid][$visit_date][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]];
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['extra_data'] = $v_cf;
    											$extra_data[$v_ipid]['home_visit'][$v_cf['id']] = $v_cf;
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]]));
    											$excluded_saved_data_days[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2'][] = $visit_date[$v_ipid];
    
    											$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]];
    										}
    										else
    										{
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += 1;
    											$extra_data[$v_ipid]['home_visit'][$v_cf['id']] = $v_cf;
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]];
    											$products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]] + $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_' . $shortcut_switch[$v_ipid] . '_2'][$vday_matched_loc_price_type[$v_ipid]]);
    
    											$products[$v_ipid]['grand_total'] += $products[$v_ipid]['rp_' . $shortcut_switch[$v_ipid] . '_2']['total'][$vday_matched_loc_price_type[$v_ipid]];
    										}
    											
    											
    										$shortcut[$v_ipid] = '';
    										$qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] = '';
    									}
    
    									//DOCTOR VISITS - Ärztlicher Hausbesuch - price for the doctor visit from minute 20 - 45 (rp_doc_1||rp_nur_1)
    									if($v_cf['visit_duration'] >= '0')
    									{
    										$shortcut[$v_ipid] = 'rp_' . $shortcut_switch[$v_ipid] . '_1';
    										$qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] = '1';
    											
    										if($shortcut[$v_ipid] && $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] && $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    										{
    											//overide with saved data
    											if(strlen($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]) > '0')
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut][$vday_matched_loc_price_type[$v_ipid]]));
    												$excluded_saved_data_days[$v_ipid][$shortcut[$v_ipid]][] = $visit_date[$v_ipid];
    
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    											else
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]]));
    
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$vday_matched_loc_price_type[$v_ipid]];
    													
    													
    											}
    										}
    									}
    
    									//DOCTOR VISITS - Zusatzentgeld Arzt - for EVERY 15 minutes more from minute 61 this product is added. (doctor) (rp_doc_3||rp_nur_3)
    									if($v_cf['visit_duration'] > '45')
    									{
    										// calculate multiplier of 15 minutes after 60 min (round up)
    										// ISPC-2006 29.06.2017 :: From 60 was changed to 45
    										// calculate multiplier of 15 minutes after 45 min (round up)
    											
    										$shortcut[$v_ipid] = 'rp_' . $shortcut_switch[$v_ipid] . '_3';
    										$multiplier = ceil(($v_cf['visit_duration'] - 45) / 15);
    										$qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] = $multiplier; //multiplier value
    											
    										if($shortcut[$v_ipid] && $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] && $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    										{
    
    											//overide with saved data
    											if(strlen($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]) > '0')
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]));
    												$excluded_saved_data_days[$v_ipid][$shortcut[$v_ipid]][] = $visit_date[$v_ipid];
    
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    											else
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]]));
    
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    										}
    									}
    
    									//DOCTOR VISITS - Abschlag Arzt - reduction of the doctor visit price when visit is shorter than 20 minutes.(rp_doc_4||||rp_nur_4)
    									if($v_cf['visit_duration'] < '20')
    									{
    										$shortcut[$v_ipid] = 'rp_' . $shortcut_switch[$v_ipid] . '_4';
    										$qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] = '1';
    											
    										if($shortcut[$v_ipid] && $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]] && $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] != '0.00')
    										{
    											//overide with saved data
    											if(strlen($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]) > '0')
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid][$shortcut[$v_ipid]][$visit_date][$vday_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]]));
    												$excluded_saved_data_days[$v_ipid][$shortcut[$v_ipid]][] = $visit_date[$v_ipid];
    
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    											else
    											{
    												$products[$v_ipid][$shortcut[$v_ipid]]['qty_gr'][$vday_matched_loc_price_type[$v_ipid]] += $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]];
    												$products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid][$shortcut[$v_ipid]]['total'][$vday_matched_loc_price_type[$v_ipid]] + ($ppl[$v_ipid][$visit_date[$v_ipid]][0][$shortcut[$v_ipid]][$vday_matched_loc_price_type[$v_ipid]] * $qty[$v_ipid][$vday_matched_loc_price_type[$v_ipid]]));
    
    												$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$vday_matched_loc_price_type[$v_ipid]];
    											}
    										}
    									}
    								}
    							}
    
    							//Fallabschluss - patient death coordination. added once (rp_pat_dead)
    							if(strlen($discharge_dead_date[$v_ipid]) > 0)
    							{
    								//visit date formated
    								$visit_date[$v_ipid] = date('Y-m-d', strtotime($discharge_dead_date[$v_ipid]));
    									
    								//switch shortcut_type based on patient location for *visit* date
    								$dead_matched_loc_price_type[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$discharge_dead_date[$v_ipid]]];
    								$qty[$v_ipid][$dead_matched_loc_price_type[$v_ipid]] = '1';
    									
    								if($dead_matched_loc_price_type[$v_ipid] && $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_pat_dead'][$dead_matched_loc_price_type[$v_ipid]] != '0.00')
    								{
    									//overide with saved data
    									if(strlen($saved_data[$v_ipid]['rp_pat_dead'][$visit_date[$v_ipid]][$dead_matched_loc_price_type[$v_ipid]]) > '0')
    									{
    										$products[$v_ipid]['rp_pat_dead']['qty_gr'][$dead_matched_loc_price_type[$v_ipid]] += $saved_data[$v_ipid]['rp_pat_dead'][$visit_date[$v_ipid]][$dead_matched_loc_price_type[$v_ipid]];
    										$products[$v_ipid]['rp_pat_dead']['price'] = $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_pat_dead'][$dead_matched_loc_price_type[$v_ipid]];
    										$products[$v_ipid]['rp_pat_dead']['total'][$dead_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid]['rp_pat_dead']['total'][$dead_matched_loc_price_type[$v_ipid]] + ($saved_data[$v_ipid]['rp_pat_dead'][$visit_date[$v_ipid]][$dead_matched_loc_price_type[$v_ipid]] * $ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_pat_dead'][$dead_matched_loc_price_type[$v_ipid]]));
    										$excluded_saved_data_days[$v_ipid]['rp_pat_dead'][] = $visit_date[$v_ipid];
    
    										$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$dead_matched_loc_price_type[$v_ipid]];
    
    									}
    									else
    									{
    										$products[$v_ipid]['rp_pat_dead']['qty_gr'][$dead_matched_loc_price_type[$v_ipid]] += $qty[$v_ipid][$dead_matched_loc_price_type[$v_ipid]];
    										$products[$v_ipid]['rp_pat_dead']['price'] = $ppl[$v_ipid][$discharge_dead_date[$v_ipid]][0]['rp_pat_dead'][$dead_matched_loc_price_type[$v_ipid]];
    										$products[$v_ipid]['rp_pat_dead']['total'][$dead_matched_loc_price_type[$v_ipid]] = ($products[$v_ipid]['rp_pat_dead']['total'][$dead_matched_loc_price_type[$v_ipid]] + ($ppl[$v_ipid][$visit_date[$v_ipid]][0]['rp_pat_dead'][$dead_matched_loc_price_type[$v_ipid]] * $qty[$v_ipid][$dead_matched_loc_price_type[$v_ipid]]));
    
    										$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$dead_matched_loc_price_type[$v_ipid]];
    									}
    								}
    								//GATHER INVOICE ITEMS END
    							}
    						}
    					}
    					else
    					{
    						//reset date values
    						/* $this->view->curent_sapv_from = '';
    						$this->view->curent_sapv_till = '';
    						$this->view->invoice_date_from = '';
    						$this->view->invoice_date_till = ''; */
    						$curent_sapv_from[$v_ipid] = '';
    						$curent_sapv_till[$v_ipid] = '';
    						$invoice_date_from[$v_ipid] = '';
    						$invoice_date_till[$v_ipid] = '';
    					}
    			}
    
    			//append the rest of saved data for existing invoiced sapv days
    			//			print_r("products I\n");
    			// 			print_r($products); exit;
    			//removed rp_eb_3 from shortcuts arr because is allready calculated
    			$shortcuts_array = array('rp_eb_1', 'rp_eb_2', 'rp_doc_1', 'rp_doc_2', 'rp_doc_3', 'rp_doc_4', 'rp_nur_1', 'rp_nur_2', 'rp_nur_3', 'rp_nur_4', 'rp_pat_dead');
    			foreach($shortcuts_array as $k_short => $v_short)
    			{
    				foreach($sapv_days[$v_ipid] as $k_sapv_day => $vsapv_day)
    				{
    					$sapv_day_loc_matched_price[$v_ipid] = $location_type_match[$pat_days2loctype[$v_ipid][$vsapv_day]];
    
    					if(!in_array($vsapv_day, $excluded_saved_data_days[$v_ipid][$v_short]) && !in_array($vsapv_day, $second_exclude[$v_ipid][$v_short][$sapv_day_loc_matched_price[$v_ipid]]) && $ppl[$v_ipid][$vsapv_day][0][$v_short][$sapv_day_loc_matched_price[$v_ipid]] != '0.00' && strlen($saved_data[$v_ipid][$v_short][$vsapv_day][$sapv_day_loc_matched_price[$v_ipid]]) > 0
    							)
    					{
    						$products[$v_ipid][$v_short]['qty_gr'][$sapv_day_loc_matched_price[$v_ipid]] += $saved_data[$v_ipid][$v_short][$vsapv_day][$sapv_day_loc_matched_price[$v_ipid]];
    						$products[$v_ipid][$v_short]['price'] = $ppl[$v_ipid][$vsapv_day][0][$v_short][$sapv_day_loc_matched_price[$v_ipid]];
    						$products[$v_ipid][$v_short]['total'][$sapv_day_loc_matched_price[$v_ipid]] = ($products[$v_ipid][$v_short]['qty_gr'][$sapv_day_loc_matched_price[$v_ipid]] * $ppl[$v_ipid][$vsapv_day][0][$v_short][$sapv_day_loc_matched_price[$v_ipid]]);
    						$second_exclude[$v_ipid][$v_short][$sapv_day_loc_matched_price[$v_ipid]][] = $vsapv_day;
    							
    						$products[$v_ipid]['grand_total'] += $products[$v_ipid][$v_short]['total'][$sapv_day_loc_matched_price[$v_ipid]];
    					}
    				}
    			}
    
    
    			usort($extra_data[$v_ipid]['home_visit'], array(new Pms_Sorter('billable_date'), "_date_compare"));
    			//$this->view->items = $products;
    			// 			print_r($products); exit;
    			//			print_r("products II\n");
    			//			if($_REQUEST['dbgqz'])
    			//			{
    				//
    				//				print_r("saved_data\n");
    				//				print_r($saved_data);
    				//
    				//				print_r("excluded dates\n");
    				//				print_r($excluded_saved_data_days);
    				//
    				//				print_r("saved_data_arr\n");
    				//				print_r($saved_data_arr);
    				//				exit;
    				//			}
    		}
    
    		//print_r($products); exit;
    
    		 
    		$pseudo_post = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$pseudo_post['ipid'] = $v_ipid;
    			$pseudo_post['clientid'] = $clientid;
    			$pseudo_post['shortcuts'] = $shortcuts['rp'];
    			$pseudo_post['items'] = $products[$v_ipid];
    
    			$pseudo_post['grand_total'] = $products[$v_ipid]['grand_total'];
    
    			$pseudo_post['extra_data'] = $extra_data[$v_ipid];
    			$pseudo_post['users_array'] = $user_array;
    
    			$pseudo_post['client_form_type'] = $client_form_type;
    			$pseudo_post['form2action'] = $form2action;
    
    			$pseudo_post['alias']['home_visit'] = "Hausbesuch";
    			$pseudo_post['alias']['beratung'] = "Beratung";
    			$pseudo_post['alias']['koordination'] = "Koordination";
    
    			$pseudo_post['epid'] = $patient_days[$v_ipid]['details']['epid'];
    
    			$pseudo_post['client_ik'] = $client_details[0]['institutskennzeichen'];
    			$pseudo_post['client_details'] = $client_details[0];
    			$pseudo_post['clientid'] = $clientid;
    
    			$pseudo_post['patientdetails'] = $patient_days[$v_ipid]['details'];
    			$pseudo_post['geb'] = date('d.m.Y', strtotime($patient_days[$v_ipid]['details']['birthd']));
    
    			$pseudo_post['topdatum'] = date('d.m.Y', time());
    			$pseudo_post['sig_date'] = date('d.m.Y', time());
    			$pseudo_post['stample'] = $client_nice_address;
    
    			$pseudo_post['patient_name'] = $patient_days[$v_ipid]['details']['last_name'] . ", " . $patient_days[$v_ipid]['details']['first_name'] . "\n" . $patient_days[$v_ipid]['details']['street1'] . "\n" . $patient_days[$v_ipid]['details']['zip'] . "&nbsp;" . $patient_days[$v_ipid]['details']['city'];
    
    			$pseudo_post['krankenkasse'] = $pathelathinsu[$v_ipid]['name'];
    			//$pseudo_post['health_insurance_ik'] = $pathelathinsu[$v_ipid]['health_insurance_ik'];
    			$pseudo_post['kassen_nr'] = $pathelathinsu[$v_ipid]['health_insurance_kassenr'];
    			$pseudo_post['versicherten_nr'] = $pathelathinsu[$v_ipid]['insurance_no'];
    			$pseudo_post['status'] = $pathelathinsu[$v_ipid]['health_insurance_status'];
    
    			$pseudo_post['betriebsstatten_nr'] = $user_btsnr;
    			$pseudo_post['arzt_nr'] = $user_arztnr;
    
    			$pseudo_post['completed_date'] = date('d.m.Y', time());
    
    			$pseudo_post['sapv_erst'] = $sapv_erst[$v_ipid];
    			$pseudo_post['sapv_folge'] = $sapv_folge[$v_ipid];
    
    			$pseudo_post['curent_sapv_from'] = $curent_sapv_from_f[$v_ipid];
    			$pseudo_post['curent_sapv_till'] = $curent_sapv_till_f[$v_ipid];
    			$pseudo_post['invoice_date_from'] = $curent_sapv_from_f[$v_ipid];
    			$pseudo_post['invoice_date_till'] = $curent_sapv_till_f[$v_ipid];
    
    
    			$pseudo_post['main_diagnosis'] = implode(', ', $patient_main_diag[$v_ipid]['icd']);
    
    
    			//new columns
    			$pseudo_post['debitor_number'] = $pathelathinsu[$v_ipid]['debitor_number'];
    			$pseudo_post['ppun'] = $pathelathinsu[$v_ipid]['ppun'];
    			//--
    			//print_r($pseudo_post); exit;
    			if($_REQUEST['only_pdf'] == '0')
    			{
    				//get invoice temp number
    				$rp_invoice_nr = RpInvoices::get_next_invoice_number($clientid, true);
    
    				$prefix = $rp_invoice_nr['prefix'];
    				$invoice_number = $rp_invoice_nr['invoicenumber'];
    					
    				$pseudo_post['prefix'] = $prefix;
    				$pseudo_post['invoice_number'] = $invoice_number;
    				$create_rp_invoice = $rp_invoices_form->create_invoice($clientid, $pseudo_post);
    
    					
    			}
    
    			if($_REQUEST['get_pdf'] == '1')
    			{
    				/* if($_REQUEST['stornopdf'] == '1' && $_REQUEST['storno'])
    				 {
    				 $storno_data = $sgbvinvoices->getSgbvInvoice($_REQUEST['storno']);
    
    				 //ISPC-2532 Lore 09.11.2020
    				 $pseudo_post['storned_invoice_number'] = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
    
    				 $pseudo_post['address'] = $storno_data['address'];
    				 $pseudo_post['client'] = $storno_data['client'];
    				 $pseudo_post['prefix'] = $storno_data['prefix'];
    				 $pseudo_post['invoice_number'] = $storno_data['invoice_number'];
    				 $pseudo_post['completed_date'] = date('d.m.Y', strtotime($storno_data['completed_date']));
    				 $pseudo_post['first_active_day'] = date('d.m.Y', strtotime($storno_data['start_active']));
    				 $pseudo_post['last_active_day'] = date('d.m.Y', strtotime($storno_data['end_active']));
    				 if($storno_data['start_sgbv'] != '0000-00-00 00:00:00' && $storno_data['end_sgbv'] != '0000-00-00 00:00:00')
    				 {
    				 $pseudo_post['start_sgbv_activity'] = date('d.m.Y', strtotime($storno_data['start_sgbv']));
    				 $pseudo_post['end_sgbv_activity'] = date('d.m.Y', strtotime($storno_data['end_sgbv']));
    				 }
    				 $pseudo_post['client_ik'] = $client_details[0]['institutskennzeichen'];
    				 if($_REQUEST['bulk_print'] == '1'){
    				 $pseudo_post['unique_id'] = $storno_data['id'];
    				 } else {
    				 $pseudo_post['unique_id'] = $storno_data['record_id'];
    				 }
    				 $pseudo_post['grand_total'] = ($storno_data['invoice_total'] * (-1));
    				 $pseudo_post['sapv_footer'] = $storno_data['footer'];
    
    				 $template_files = array('storno_invoice_sgbv_pdf.html', 'socialcodepdf.html');
    				 }
    				 else
    				 {
    				 $template_files = array('invoice_sgbv_pdf.html', 'socialcodepdf.html');
    				 }
    
    				 $orientation = array('P', 'L');
    				 $background_pages = array('0'); //0 is first page; */
    					
    				if($template_data)
    				{
    
    					$template = INVOICE_TEMPLATE_PATH . '/' . $template_data[0]['file_path'];
    					//create public/joined_files/ dir
    					while(!is_dir(PDFJOIN_PATH))
    					{
    						mkdir(PDFJOIN_PATH);
    						if($i >= 50)
    						{
    							exit; //failsafe
    						}
    						$i++;
    					}
    
    					//create public/joined_files/$clientid dir
    					$pdf_path = PDFJOIN_PATH . '/' . $clientid;
    
    					while(!is_dir($pdf_path))
    					{
    						mkdir($pdf_path);
    						if($i >= 50)
    						{
    							exit; //failsafe
    						}
    						$i++;
    					}
    
    					//create batch name
    					$_Batchname = false;
    					$_Batchname = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
    
    					// generate invoice page
    					$tokenfilter = array();
    					$tokenfilter['invoice']['address'] = $pseudo_post['patient_name'];
    					//$tokenfilter['client']['city'] = $pdf_data['client_city'];
    					$tokenfilter['client']['institutskennzeichen'] = $pseudo_post['client_ik'];
    					if($pseudo_post['completed_date'] != "0000-00-00 00:00:00" && $pseudo_post['completed_date'] != "1970-01-01 00:00:00")
    					{
    						$tokenfilter['invoice']['invoicedate'] = strftime('%A, %d. %B %Y', strtotime($pseudo_post['completed_date']));
    					}
    					else
    					{
    						$tokenfilter['invoice']['invoicedate'] = '';
    					}
    
    					$tokenfilter['invoice']['prefix'] = $pseudo_post['prefix'];
    					$tokenfilter['invoice']['invoicenumber'] = $pseudo_post['invoice_number'];
    					$tokenfilter['invoice']['full_invoice_number'] = $pseudo_post['prefix'].$pseudo_post['invoice_number'];
    					/* if($pdf_data['first_active_day'] != "0000-00-00 00:00:00" && $pdf_data['first_active_day'] != "1970-01-01 00:00:00")
    					 {
    					 $tokenfilter['invoice']['first_active_day'] = date('d.m.Y', strtotime($pdf_data['first_active_day']));
    					 }
    					 else
    					 {
    					 $tokenfilter['invoice']['first_active_day'] = "-";
    					 }
    					 if($pdf_data['last_active_day'] != "0000-00-00 00:00:00" && $pdf_data['last_active_day'] != "1970-01-01 00:00:00")
    					 {
    					 $tokenfilter['invoice']['last_active_day'] = date('d.m.Y', strtotime($pdf_data['last_active_day']));
    					 }
    					 else
    					 {
    					 $tokenfilter['invoice']['last_active_day'] = "-";
    					 } */
    
    					//$tokenfilter['invoice']['healthinsurancenumber'] = $pdf_data['insurance_no'];
    					//$tokenfilter['invoice']['health_insurance_ik'] = $pdf_data['health_insurance_ik'];
    					$tokenfilter['invoice']['healthinsurance_versnr'] = $pseudo_post['versicherten_nr'];
    					$tokenfilter['invoice']['healthinsurance_kassennr'] = $pseudo_post['kassen_nr'];
    					$tokenfilter['invoice']['healthinsurance_status'] = $pseudo_post['status'];
    
    					$tokenfilter['familydoctor']['doctor_bsnr'] = $pseudo_post['betriebsstatten_nr'];
    					$tokenfilter['familydoctor']['doctor_lbnr'] = $pseudo_post['arzt_nr'];
    					$tokenfilter['invoice']['topdatum'] = $pseudo_post['topdatum'];
    
    					$tokenfilter['invoice']['stample'] = $pseudo_post['stample'];
    
    					if($pseudo_post['sapv_erst'] == 1)
    					{
    						$tokenfilter['sapv']['sapv_erst'] = 'X';
    					}
    					else
    					{
    						$tokenfilter['sapv']['sapv_erst'] = '';
    					}
    
    					if($pseudo_post['sapv_folge'] == 1)
    					{
    						$tokenfilter['sapv']['sapv_folge'] = 'X';
    					}
    					else
    					{
    						$tokenfilter['sapv']['sapv_folge'] = '';
    					}
    
    					$tokenfilter['sapv']['sapv_from'] = $pseudo_post['curent_sapv_from'];
    					$tokenfilter['sapv']['sapv_till'] = $pseudo_post['curent_sapv_till'];
    					$tokenfilter['invoice']['invoiced_period_start'] = $pseudo_post['invoice_date_from'];
    					$tokenfilter['invoice']['invoiced_period_end'] = $pseudo_post['invoice_date_till'];
    
    					$tokenfilter['ionvoice']['main_diagnosis'] = $pseudo_post['main_diagnosis'];
    
    					$tokenfilter['patient']['birthd'] = $pseudo_post['geb'];
    					//$tokenfilter['invoice']['patient_pflegestufe'] = $pdf_data['patient_pflegestufe'];
    					//$tokenfilter['footer'] = $pdf_data['footer'];
    
    					/* if($pdf_data['healthinsurance_name'] != "")
    					 {
    					 $tokenfilter['healthinsurance']['healthinsurance_name'] = $pdf_data['krankenkasse'];
    					 }
    					 else
    					 {
    					 $tokenfilter['healthinsurance']['healthinsurance_name'] = "--";
    					 } */
    
    					/* $tokenfilter['invoice']['unique_id'] = $pdf_data['unique_id'];
    					 	
    					if($pdf_data['current_period']['start'] != "0000-00-00 00:00:00" && $pdf_data['current_period']['start'] != "1970-01-01 00:00:00")
    					{
    					$tokenfilter['invoice']['invoice_period_start'] = date('d.m.Y', strtotime($pdf_data['current_period']['start']));
    					}
    					else
    					{
    					$tokenfilter['invoice']['invoice_period_start'] = "-";
    					}
    					if($pdf_data['current_period']['end'] != "0000-00-00 00:00:00" && $pdf_data['current_period']['end'] != "1970-01-01 00:00:00")
    					{
    					$tokenfilter['invoice']['invoice_period_end'] = date('d.m.Y', strtotime($pdf_data['current_period']['end']));
    					}
    					else
    					{
    					$tokenfilter['invoice']['invoice_period_end'] = "-";
    					} */
    
    					/* if($_REQUEST['stornopdf'] == '1' && $_REQUEST['storno'])
    					 {
    					 $tokenfilter['invoice']['invoiceamount'] = number_format($pdf_data['invoice_total'], '2', ',', '.');
    					 }
    					 else
    					 {
    					 $tokenfilter['invoice']['invoiceamount'] = number_format($pdf_data['invoice_total'], '2', ',', '.');
    					 } */
    
    					$keyi = 0;
    					foreach($pseudo_post['items'] as $kivi => $vivi)
    					{
    						$rp_invoice_items['items'][$keyi]['shortcuts'][$kivi] = $vivi;
    						$keyi++;
    
    					}
    
    					if(count($pseudo_post['items']) > '0')
    					{
    						$rows = count($rp_invoice_items['items']);
    						$grid = new Pms_Grid($rp_invoice_items['items'], 1, $rows, "rp_invoice_items_list_pdf.html");
    						//$grid_short = new Pms_Grid($sgbxi_invoice_items['items'], 1, $rows, "bw_sgbxi_invoice_items_list_pdf_short.html");
    
    						//$grid->invoice_total = $tokenfilter['invoice']['invoiceamount'];
    						$grid->max_entries = $rows;
    
    						/* $grid_short->invoice_total = $tokenfilter['invoice']['invoiceamount'];
    						 $grid_short->max_entries = $rows; */
    							
    						$html_items = $grid->renderGrid();
    						//$html_items_short = $grid_short->renderGrid();
    					}
    					else
    					{
    						$html_items = "";
    						$html_items_short = "";
    					}
    
    					$tokenfilter['invoice']['invoice_items_html'] = $html_items;
    					//$tokenfilter['invoice']['invoice_items_html_short'] = $html_items_short;
    					//print_r($tokenfilter); exit;
    					//print_r($tokenfilter); exit;
    
    					$docx_helper = $this->getHelper('CreateDocxFromTemplate');
    					$docx_helper->setTokenController('invoice');
    
    					$tmpstmp = isset($this->view->folder_stamp) ? $this->view->folder_stamp : time();
    
    					while(!is_dir($pdf_path . '/' . $tmpstmp))
    					{
    						mkdir($pdf_path . '/' . $tmpstmp);
    						if($i >= 50)
    						{
    							exit; //failsafe
    						}
    						$i++;
    					}
    
    					$destination_path = $pdf_path . '/' . $tmpstmp . '/';
    
    					$docx_helper->setOutputFile($destination_path.$_Batchname);
    
    
    					//do not add extension !
    					$docx_helper->setBrowserFilename($_Batchname);
    
    					$docx_helper->create_pdf ($template, $tokenfilter) ;
    
    					if(!empty($extra_data))
    					{
    						$temp_files[] = $destination_path.$_Batchname.'.pdf';
    							
    						//generate leistungs page
    						$temp_files[] = $this->generate_joined_files_pdf(4,$pseudo_post,'RP_invoice_items',"rp_invoice_items.html");
    
    						$source = 'RP_invoice_token';
    						$patient_data = array();
    						$patient_data['invoice_full_number'] = $tokenfilter['invoice']['full_invoice_number'];
    						ob_end_clean();
    						$this->join_pdfs_new($temp_files, $patient_data ,$source);
    					}
    					else
    					{
    						$docx_helper->download_file();
    						exit;
    					}
    
    				}
    				else
    				{
    					if($_REQUEST['version'] == "old"){
    						$this->generate_pdf($pseudo_post, "rpinvoice", "rpinvoice_pdf.html", 'P');
    					}
    					else
    					{
    
    						$this->_helper->layout->setLayout('layout_ajax');
    						$this->_helper->viewRenderer->setNoRender();
    
    
    						// 					$template_files = array('rpinvoice_pdf.html', 'rp_invoice_items.html');// This is per patient - invoice
    						// 					$background_pages = array('0'); //0 is first page;
    
    						// 					$orientation = array('P', 'P');
    						// 					$this->generate_pdf($post, "rpinvoice", "rpinvoice_pdf.html", 'P');
    						// 					$this->generate_pdf($post, "RP_invoice_items", $template_files, $orientation, $background_pages);// This is per patient - invoice
    
    						$files[] = $this->generate_joined_files_pdf(4,$pseudo_post,'rpinvoice',"rpinvoice_pdf.html");
    						if(!empty($extra_data))
    						{
    							$files[] = $this->generate_joined_files_pdf(4,$pseudo_post,'RP_invoice_items',"rp_invoice_items.html");
    						}
    
    						//Final step merge generated files!
    						$patient_data['ipid'] = $pseudo_post['ipid'];
    						$patient_data['epid'] = $pseudo_post['epid'];
    						$patient_data['invoice_full_number']= 'TEMP_'.$pseudo_post['epid'];//TODO-3490
    						$source = 'Rp_invoice_and_items';
    						ob_end_clean();//TODO-3490
    						$this->join_pdfs_new($files, $patient_data, $source);
    					}
    				}
    			}
    		}
    		 
    		$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=rpinvoice');
    }
    }
    
    private function generatehospizinvoice($params)
    {
    	if(isset($params['print_job']) && $params['print_job'] == '1'){
    		$this->_helper->layout->setLayout('layout_ajax');
    		$this->_helper->viewRenderer->setNoRender();
    	}
    
    	setlocale(LC_ALL, 'de_DE.UTF-8');
    	//$logininfo = new Zend_Session_Namespace('Login_Info');
    	//$tm = new TabMenus();
    	$p_list = new PriceList();
    	$form_types = new FormTypes();
    	$sapvs = new SapvVerordnung();
    	$patientmaster = new PatientMaster();
    	//$sapvverordnung = new SapvVerordnung();
    	$pflege = new PatientMaintainanceStage();
    	$hi_perms = new HealthInsurancePermissions();
    	$phelathinsurance = new PatientHealthInsurance();
    	$healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
    	$boxes = new LettersTextBoxes();
    	$client = new Client();
    	$hospiz_invoices = new HospizInvoices();
    	$hospiz_invoices_items = new HospizInvoiceItems();
    	$hospiz_invoice_form = new Application_Form_HospizInvoices();
    	$ppun = new PpunIpid();
    	
    	//TODO-3727 Ancuta 12.01.2021 
//     	include 'InvoicenewController.php';
//     	$invoicenewController = new InvoicenewController($this->_request, $this->_response);
    
    	if(isset($params) && !empty($params)){
    		$_REQUEST = $params;
    		$this->_helper->viewRenderer->setNoRender();
    	}
    
    	//var_dump($_REQUEST); exit;
    	//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
    	$clientid = isset($_REQUEST['clientid']) && ! empty($_REQUEST['clientid']) ? $_REQUEST['clientid'] :  $this->clientid;
    	$userid = isset($_REQUEST['userid']) && ! empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $this->userid;
    
    	$letter_boxes_details = $boxes->client_letter_boxes($clientid);
    	$client_details = $client->getClientDataByid($clientid);
    	$this->view->client_details = $client_details[0];
    
    	$modules =  new Modules();
    	$clientModules = $modules->get_client_modules($clientid);
    
    	$ipids = $_REQUEST['ipids'];
    
    	//load template data
    	$template_data = InvoiceTemplates::get_template($clientid, false, '1', $_REQUEST['invoice_type']);
    
    	/* if(!$template_data && $_REQUEST['get_pdf'] == '1'){
    	 $this->redirect(APP_BASE . 'invoiceclient/patientlist?flg=notemplate');
    	 exit;
    	 } */
    
    	if(!empty($ipids))
    	{
    		//patient days
    		$conditions['client'] = $clientid;
    		$conditions['ipids'] = $ipids;
    		$conditions['periods'][0]['start'] = '2009-01-01';
    		$conditions['periods'][0]['end'] = date('Y-m-d');
    		 
    		$sql = 'e.epid, p.ipid, e.ipid,';
    		$sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    		$sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    		$sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    		$sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    		$sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    		$sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    		 
    		//be aware of date d.m.Y format here
    		$patient_days = Pms_CommonData::patients_days($conditions, $sql);
    		//var_dump($patient_days); exit;
    		$all_patients_periods = array();
    		foreach($patient_days as $k_ipid => $patient_data)
    		{
    			$overall_periods[$k_ipid] = array_values($patient_data['active_periods']);
    			$overall[$k_ipid]['start'] = $overall_periods[$k_ipid][0]['start'];
    			$last_period[$k_ipid] = end($overall_periods[$k_ipid]);
    			$overall[$k_ipid]['end'] = $last_period[$k_ipid]['end'];
    			
    			//all patients periods
    			$all_patients_periods = array_merge_recursive($all_patients_periods, $patient_data['active_periods']);
    
    			//used in flatrate
    			if(empty($patient_periods[$k_ipid]))
    			{
    				$patient_periods[$k_ipid] = array();
    			}
    
    			array_walk_recursive($patient_data['active_periods'], function(&$value) {
    				$value = date("Y-m-d", strtotime($value));
    			});
    				$patient_periods[$k_ipid] = array_merge($patient_periods[$k_ipid], $patient_data['active_periods']);
    
    				//hospital days cs
    				if(!empty($patient_data['hospital']['real_days_cs']))
    				{
    					$hospital_days_cs[$k_ipid] = $patient_data['hospital']['real_days_cs'];
    					array_walk($hospital_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//hospiz days cs
    				if(!empty($patient_data['hospiz']['real_days_cs']))
    				{
    					$hospiz_days_cs[$k_ipid] = $patient_data['hospiz']['real_days_cs'];
    					array_walk($hospiz_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//real active days
    				if(!empty($patient_data['real_active_days']))
    				{
    					$active_days_in_period_cs[$k_ipid] = $patient_data['real_active_days'];
    					array_walk($active_days_in_period_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//treatment days
    				if(!empty($patient_data['treatment_days']))
    				{
    					$treatment_days_cs[$k_ipid] = $patient_data['treatment_days'];
    					array_walk($treatment_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//active days
    				if(!empty($patient_data['active_days']))
    				{
    					$active_days[$k_ipid] = $patient_data['active_days'];
    					array_walk($active_days[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				if(empty($hospital_days_cs[$k_ipid]))
    				{
    					$hospital_days_cs[$k_ipid] = array();
    				}
    
    				if(empty($hospiz_days_cs[$k_ipid]))
    				{
    					$hospiz_days_cs[$k_ipid] = array();
    				}
    
    				$hospital_hospiz_days_cs[$k_ipid] = array_merge($hospital_days_cs[$k_ipid], $hospiz_days_cs[$k_ipid]);
    
    		}
    		 
    		$all_patients_periods = array_values($all_patients_periods);
    		 
    		foreach($all_patients_periods as $k_period => $v_period)
    		{
    			if(empty($months))
    			{
    				$months = array();
    			}
    
    			$period_months = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], 'Y-m');
    			$months = array_merge($months, $period_months);
    		}
    		$months = array_values(array_unique($months));
    		 
    		foreach($months as $k_m => $v_m)
    		{
    			$months_unsorted[strtotime($v_m)] = $v_m;
    		}
    		ksort($months_unsorted);
    		$months = array_values(array_unique($months_unsorted));
    		 
    		foreach($months as $k_month => $v_month)
    		{
    			if(!function_exists('cal_days_in_month'))
    			{
    				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
    			}
    			else
    			{
    				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
    			}
    
    			$months_details[$v_month]['start'] = $v_month . "-01";
    			$months_details[$v_month]['days_in_month'] = $month_days;
    			$months_details[$v_month]['end'] = $v_month . '-' . $month_days;
    
    			//$month_select_array[$v_month] = $v_month;
    			$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
    		}
    
    		//loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)
    		foreach($ipids as $k_sel_pat => $v_sel_pat)
    		{
    			//get patients sapvs last fall
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'sapvid')
    			{
    				$selected_sapv_falls_ipids[] = $v_sel_pat;
    				$selected_sapv_falls[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			//get patient month fall
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'list')
    			{
    				$selected_fall_ipids[] = $v_sel_pat;
    				$selected_month[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'admission')
    			{
    				$admission_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'quarter')
    			{
    				$quarter_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    		}
    		 
    		$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($selected_sapv_falls_ipids, true);
    		foreach($selected_sapv_falls as $k_ipid => $fall_id)
    		{
    			$patients_sapv[$k_ipid] = $fall_id;
    			$patients_selected_periods[$k_ipid] = $patients_sapv_periods[$k_ipid][$fall_id];
    			$_REQUEST['nosapvperiod'][$k_ipid] = '0';
    			$_REQUEST['period'] = $patients_selected_periods;
    		}
    
    		//rewrite the periods array if the period is entire month not sapv fall
    		$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
    
    		foreach($patients_sapv_periods as $k_sapv_ipid => $v_sapvs)
    		{
    			foreach($v_sapvs as $k_sapv_id => $v_sapv_data)
    			{
    				if(empty($sapv_days[$v_sapv_data['ipid']]))
    				{
    					$sapv_days[$v_sapv_data['ipid']] = array();
    				}
    					
    				$sapv_days[$v_sapv_data['ipid']] = array_merge($sapv_days[$v_sapv_data['ipid']], $v_sapv_data['days']);
    				$sapv_days[$v_sapv_data['ipid']] = array_values(array_unique($sapv_days[$v_sapv_data['ipid']]));
    			}
    		}
    		 
    
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			if(!in_array($v_ipid, $selected_sapv_falls_ipids))
    			{
    				if(array_key_exists($v_ipid, $admission_fall))
    				{
    					$selected_period[$v_ipid] = $patient_days[$v_ipid]['active_periods'][$admission_fall[$v_ipid]];
    
    					array_walk($selected_period[$v_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    							
    						$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($selected_period[$v_ipid]['start'], $selected_period[$v_ipid]['end']);
    							
    						array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    
    							$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    							$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    							$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    
    
    							array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    								$value = date("d.m.Y", strtotime($value));
    							});
    									
    								//exclude outside admission falls days from sapv!
    								if(empty($sapv_days[$v_ipid]))
    								{
    									$sapv_days[$v_ipid] = array();
    								}
    									
    								if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    								{
    									$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    								}
    								$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    								$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    									
    								$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    								$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    									
    								$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    								$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    									
    								//get all days of all sapvs in a period
    								$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    								$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    									
    									
    								$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    								$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    									
    								$last_sapv_data['ipid'] = $v_ipid;
    								$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    								$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    								$sapv_last_require_data[] = $last_sapv_data;
    
    								$_REQUEST['admissionid'][$v_ipid] = $admission_fall[$v_ipid];
    				}
    				//ISPC-2461
    				elseif(array_key_exists($v_ipid, $quarter_fall))
    				{
    					$post_q = $_REQUEST[$v_ipid]['selected_period'];
    					$post_q_arr = explode("/",$post_q);
    					$q_no = (int)$post_q_arr[0];
    					$q_year = (int)$post_q_arr[1];
    
    					$q_per = array();
    					$quarter_start = "";
    					$quarter_end = "";
    
    					$q_per = Pms_CommonData::get_dates_of_quarter($q_no,$q_year,'Y-m-d');
    					$quarter_start = $q_per['start'];
    					$quarter_end = $q_per['end'];
    
    					$selected_period[$v_ipid] = array();
    					$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($quarter_start, $quarter_end);
    					$selected_period[$v_ipid]['start'] = $quarter_start;
    					$selected_period[$v_ipid]['end'] = $quarter_end;
    
    					array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    						$value = date("d.m.Y", strtotime($value));
    					});
    							
    						$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    						$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    						$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    							
    							
    						array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    
    							//exclude outside admission falls days from sapv!
    							if(empty($sapv_days[$v_ipid]))
    							{
    								$sapv_days[$v_ipid] = array();
    							}
    
    							if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    							{
    								$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    							}
    							$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    							$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    
    							$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    							$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    
    							$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    							$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    
    							//get all days of all sapvs in a period
    							$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    							$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    
    							$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    							$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    
    							$last_sapv_data['ipid'] = $v_ipid;
    							$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    							$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    							$sapv_last_require_data[] = $last_sapv_data;
    
    							$_REQUEST['quarterid'][$v_ipid] = $quarter_fall[$v_ipid];
    				}
    				else
    				{
    
    					$start_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['start']));
    					$end_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['end']));
    
    					$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    					$_REQUEST['selected_period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    					$_REQUEST['sapv_in_period'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    					$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    					$_REQUEST['period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    					$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    					$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    
    					$last_sapv_data['ipid'] = $v_ipid;
    					$last_sapv_data['start_period'] = date('Y-m-d', strtotime($start_dmy));
    					$last_sapv_data['end_period'] = date('Y-m-d', strtotime($end_dmy));
    					$sapv_last_require_data[] = $last_sapv_data;
    				}
    			}
    		}
    		//print_r($_REQUEST); exit;
    		//get all sapv details
    		$all_sapvs = array();
    		$all_sapvs = $sapvs->get_all_sapvs($ipids);
    		 
    		$sapv2ipid = array();
    		/* foreach($all_sapvs as $k=>$sdata){
    			$sapv2ipid[$sdata['ipid']][] = $sdata;
    		} */
    
    		$has_no_sapv = array();
    		foreach($all_sapvs as $k=>$sv_data){
    			$has_no_sapv[$sv_data['ipid']] = true;
    			if($sv_data['verordnungam'] != "0000-00-00 00:00:00" && $sv_data['verordnungam'] != "1970-01-01 00:00:00" )
    			{
    				$st_date = date('Y-m-d',strtotime($sv_data['verordnungam']));
    				$sapv_period2type[$sv_data['ipid']][$st_date] = $sv_data['verordnet'];
    
    				$sapv_dates[$sv_data['ipid']]['id'] = $sv_data['id'];
    				$sapv_dates[$sv_data['ipid']]['from'] = date('Y-m-d', strtotime($sv_data['verordnungam']));
    				$sapv_dates[$sv_data['ipid']]['till'] = date('Y-m-d', strtotime($sv_data['verordnungbis']));
    				$sapv_dates[$sv_data['ipid']]['type'] = trim($sv_data['verordnet']);
    
    				$sapv_selector_source[$sv_data['ipid']][$sv_data['id']] = $sapv_dates[$sv_data['ipid']];
    				$has_no_sapv[$sv_data['ipid']] = false;
    			}
    		}
    
    
    		foreach($all_sapvs as $k_sapv => $v_sapv)
    		{
    			$sapv2ipid[$v_sapv['ipid']][] = $v_sapv;
    			if(empty($sapv_days_overall[$v_sapv['ipid']]))
    			{
    				$sapv_days_overall[$v_sapv['ipid']] = array();
    			}
    
    			$start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
    
    			if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00')
    			{
    				$end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
    			}
    			else
    			{
    				$end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
    			}
    
    			//FIND A WAY TO GET MULTIPLE LAST SAPV IN EACH PERIOD FOR EACH PATIENT
    			if($last_sapvs_in_period[$v_sapv['ipid']])
    			{
    				$_REQUEST['period'][$v_sapv['ipid']] = array_merge($_REQUEST['period'][$v_sapv['ipid']], $last_sapvs_in_period[$v_sapv['ipid']]);
    			}
    
    			$sapv_days_overall[$v_sapv['ipid']] = array_merge($sapv_days_overall[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end));
    			array_walk($sapv_days_overall[$v_sapv['ipid']], function(&$value) {
    				$value = date("d.m.Y", strtotime($value));
    			});
    				$sapv_days_overall[$v_sapv['ipid']] = array_values(array_unique($sapv_days_overall[$v_sapv['ipid']]));
    		}
    
    		foreach($last_sapvs_in_period as $k_sapvs => $v_sapvs)
    		{
    			foreach($v_sapvs as $k_sapvp => $v_sapvp)
    			{
    				$startp = date('Y-m-d', strtotime($v_sapvp['verordnungam']));
    
    				if($v_sapvp['status'] == '1' && $v_sapvp['verorddisabledate'] != '0000-00-00 00:00:00')
    				{
    					$endp = date('Y-m-d', strtotime($v_sapvp['verorddisabledate']));
    				}
    				else
    				{
    					$endp = date('Y-m-d', strtotime($v_sapvp['verordnungbis']));
    				}
    				if(empty($period_sapv_alldays[$v_sapvp['ipid']]))
    				{
    					$period_sapv_alldays[$v_sapvp['ipid']] = array();
    				}
    				$period_sapv_alldays[$v_sapvp['ipid']] = array_merge($period_sapv_alldays[$v_sapvp['ipid']], PatientMaster::getDaysInBetween($startp, $endp));
    			}
    		}
    
    
    		$_REQUEST['period_sapvs_alldays'] = $period_sapv_alldays;
    		$_REQUEST['sapv_overall'] = $sapv_days_overall;
    
    		$current_period = array();
    		foreach($_REQUEST['period'] as $ipidp => $vipidp)
    		{
    			$current_period[$ipidp] = $vipidp;
    			if($_REQUEST['sapv_in_period'][$ipidp])
    			{
    				$sapv_in_period[$ipidp] = $_REQUEST['sapv_in_period'][$ipidp];
    			}
    			else 
    			{
    				$sapv_in_period[$ipidp] = array();
    			}
    		}
    
    		$all_pflegestufe = array();
    		$all_pflegestufe = Doctrine_Query::create()
    		->select("*")
    		->from('PatientMaintainanceStage')
    		->whereIn("ipid", $ipids)
    		->orderBy('fromdate,create_date asc')
    		->fetchArray();
    
    		$pflegesufe2ipid = array();
    		foreach($all_pflegestufe as $k=>$pflg) {
    			$pflegesufe2ipid[$pflg['ipid']][] = $pflg;
    		}
    		//print_R($pflegesufe2ipid); exit;
    
    		//Healthinsurance
    		$healthinsu_multi_array = array();
    		$healthinsu_multi_array = $phelathinsurance->get_multiple_patient_healthinsurance($ipids, true);
    
    		foreach($healthinsu_multi_array as $k_hi => $v_hi)
    		{
    			$hi_companyids[] = $v_hi['companyid'];
    		}
    		
    		//multiple hi subdivisions && hi subdivisions permissions
    		$divisions = HealthInsurancePermissions::getClientHealthInsurancePermissions($clientid);
    
    		//patientheathinsurance
    		if($divisions)
    		{
    			/* foreach($healthinsu_multi_array as $k_hi => $v_hi)
    			 {
    			 $hi_companyids[] = $v_hi['companyid'];
    			 } */
    
    			$healthinsu_subdiv_arr = PatientHealthInsurance2Subdivisions::get_hi_subdivisions_multiple($hi_companyids);
    		}
    		 
    		$pathelathinsu = array();
    		$patient_address = array();
    		$hi_address = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$pathelathinsu[$v_ipid]['insurance_no'] = $healthinsu_multi_array[$v_ipid]['insurance_no'];
    			if($healthinsu_multi_array[$v_ipid]['company_name'] != "")
    			{
    				$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company_name'];
    			}
    			else
    			{
    				$pathelathinsu[$v_ipid]['name'] = $healthinsu_multi_array[$v_ipid]['company']['name'];
    			}
    
    			if($healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				//			get patient name and adress
    				$patient_address[$v_ipid] = '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['first_name']) . ' ' . htmlspecialchars($patient_days[$v_ipid]['details']['last_name']) . '<br />';
    				$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['street1']) . '<br />';
    				$patient_address[$v_ipid] .= '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['zip']) . ' ' . '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['city']);
    			}
    
    			/* if(!empty($healthinsu_subdiv_arr[$v_ipid]['1']['name']))
    			 {
    			 //get new SAPV hi address
    			 $hi_address[$v_ipid] = '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['name'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['insurance_provider'] . '<br />';
    			 //$hi_address .= '&nbsp;' . $healthinsu_subdiv_arr[1]['contact_person'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['street1'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['zip'] . ' ' . $healthinsu_subdiv_arr[$v_ipid][1]['city'];
    
    			 $pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_subdiv_arr[$v_ipid][1]['iknumber'];
    			 $pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_subdiv_arr[$v_ipid][1]['kvnumber'];
    			 }
    			 else
    			 { */
    			//get old hi_address
    			$hi_address[$v_ipid] = '&nbsp;' . $pathelathinsu[$v_ipid]['name'] . '<br />';
    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_insurance_provider'] . '<br />';
    			//$hi_address .= '&nbsp;' . $healthinsu_array[0]['ins_contactperson'] . '<br />';
    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_street'] . '<br />';
    			$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_zip'] . ' ' . $healthinsu_multi_array[$v_ipid]['ins_city'];
    
    			$pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_multi_array[$v_ipid]['institutskennzeichen'];
    			$pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_multi_array[$v_ipid]['company']['kvk_no'];
    			$pathelathinsu[$v_ipid]['health_insurance_status'] = $healthinsu_multi_array[$v_ipid]['insurance_status'];
    			//}
    
    			if($healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				$privat[$v_ipid] = 1;
    			}
    			else
    			{
    				$privat[$v_ipid] = 0;
    			}
    			//ispc-1876
    			$privat[$v_ipid] = 1;
    
    			if($healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				$invoice_data[$v_ipid]['address'] = $patient_address[$v_ipid];
    			}
    			else
    			{
    				$invoice_data[$v_ipid]['address'] = $hi_address[$v_ipid];
    			}
    
    			//new columns
    			if($clientModules['90'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "0")
    			{
    				//get debtor number from patient healthinsurance
    				if(strlen($healthinsu_multi_array[$v_ipid]['ins_debtor_number']) > '0')
    				{
    					$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['ins_debtor_number'];
    				}
    				else
    				{
    					$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['company']['debtor_number'];
    				}
    			}
    			if($clientModules['88'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				//get ppun (private patient unique number)
    				$ppun_number = $ppun->check_patient_ppun($v_ipid, $clientid);
    				if($ppun_number)
    				{
    					$pathelathinsu[$v_ipid]['ppun']= $ppun_number['ppun'];
    					$pathelathinsu[$v_ipid]['debitor_number']= $ppun_number['ppun'];
    				}
    			}
    			//--
    			//depending on MONTH
    			if(strlen($_REQUEST['monthdata']) == '0')
    			{
    				$selected_period[$v_ipid] = $overall[$v_ipid];
    				$pactive_days[$v_ipid] = $patient_days[$v_ipid]['real_active_days'];
    			
    				$invoice_data[$v_ipid]['start_active'] = $selected_period[$v_ipid]['start'];
    				$invoice_data[$v_ipid]['end_active'] = $selected_period[$v_ipid]['end'];
    			}
    			else
    			{
    			$selected_period[$v_ipid]['start'] = $months_details[$selected_month[$v_ipid]]['start'];
    			$selected_period[$v_ipid]['end'] =  $months_details[$selected_month[$v_ipid]]['end'];
    		
    			foreach($patient_days[$v_ipid]['real_active_days'] as $kd => $aval)
    			{
    				$r1start = strtotime($aval);
    				$r1end = strtotime($aval);
    				$r2start = strtotime($selected_period[$v_ipid]['start']);
    				$r2end = strtotime($selected_period[$v_ipid]['end']);    				
    				if(Pms_CommonData::isintersected($r1start, $r1end, $r2start, $r2end)){
    					$pactive_days[$v_ipid][] =  $aval;
    				}
    			}
   
    			$invoice_data[$v_ipid]['start_active'] = $pactive_days[$v_ipid][0] ;
    			$invoice_data[$v_ipid]['end_active'] = end($pactive_days[$v_ipid]);
    
    		
    			$invoice_data[$v_ipid]['days_in_period'] = $pactive_days[$ipid];
    			}
    
    			foreach($pflegesufe2ipid[$v_ipid] as $kpfl => $vpfl)
    			{
    				$highest[$v_ipid] = $vpfl[0]['stage'];
    			}
    
    			// get pricelist details
    			$master_price_list[$v_ipid] = $p_list->get_period_price_list(date('Y-m-d', strtotime($selected_period[$v_ipid]['start'])), date('Y-m-d', strtotime($selected_period[$v_ipid]['end'])));
    			$pricelist[$v_ipid] = end($master_price_list[$v_ipid]);
    
    			// Calculate days
    			$amount_of_days[$v_ipid] =  count($pactive_days[$v_ipid]);
    
    
    			$inv_pv_pat_total[$v_ipid] = 0;
    			$hospiz_pv_pat_total[$v_ipid] = 0;
    			$hospiz_pv_pat_5_percent_total[$v_ipid] = 0;
    
    
    			$inv_normal_pat_total[$v_ipid] = 0;
    			$hospiz_normal_pat_total[$v_ipid] = 0;
    			$hospiz_normal_pat_5_percent_total[$v_ipid] = 0;
    			if($privat[$v_ipid] == "1")
    			{
    					
    				$hospiz_pv_pat_price[$v_ipid] = $pricelist[$v_ipid][0]['hospiz_pv_pat']['price'];
    					
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat"]['shortcut'] = "hospiz_pv_pat";
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat"]['description'] = $this->view->translate("shortcut_name_hospiz_pv_pat");
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat"]['qty'] = $amount_of_days[$v_ipid];
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat"]['price'] = $hospiz_pv_pat_price[$v_ipid];
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat"]['total'] =  number_format( round(($amount_of_days[$v_ipid] * $hospiz_pv_pat_price[$v_ipid]),2) , '2', '.', '');
    					
    				$hospiz_pv_pat_total[$v_ipid] = round(($amount_of_days[$v_ipid] * $hospiz_pv_pat_price[$v_ipid]),2);
    					
    					
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat_5_percent"]['shortcut'] = "hospiz_pv_pat_5_percent";
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat_5_percent"]['description'] = $this->view->translate("shortcut_name_hospiz_pv_pat_5_percent");
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat_5_percent"]['qty'] = $amount_of_days[$v_ipid];
    					
    				$hospiz_pv_pat_5_percent_price[$v_ipid] = round((5 / 100) * $hospiz_pv_pat_price[$v_ipid],2);
    				$hospiz_pv_pat_5_percent_total[$v_ipid] =  round($amount_of_days[$v_ipid] * round((5 / 100) * $hospiz_pv_pat_price[$v_ipid],2),2);
    					
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat_5_percent"]['price'] = number_format($hospiz_pv_pat_5_percent_price[$v_ipid]  * (-1) , '2', '.', ''); // negative amount
    				$invoice_data[$v_ipid]['items']["hospiz_pv_pat_5_percent"]['total'] =  number_format($hospiz_pv_pat_5_percent_total[$v_ipid] * (-1) , '2', '.', ''); // negative amount
    					
    				$inv_pv_pat_total[$v_ipid] = $hospiz_pv_pat_total[$v_ipid] - $hospiz_pv_pat_5_percent_total[$v_ipid];
    					
    				$invoice_data[$v_ipid]['invoice_total'] = number_format($inv_pv_pat_total[$v_ipid] , '2', '.', ''); ;
    				$invoice_data[$v_ipid]['sub_invoice_total'] =  number_format($inv_pv_pat_total[$v_ipid] , '2', '.', '');
    			}
    			else
    			{
    				$hospiz_normal_pat_price[$v_ipid] = $pricelist[$v_ipid][0]['hospiz_normal_pat']['price'];
    					
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat"]['shortcut'] = "hospiz_normal_pat";
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat"]['description'] = $this->view->translate("shortcut_name_hospiz_normal_pat");
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat"]['qty'] = count($pactive_days[$v_ipid]);
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat"]['price'] = $hospiz_normal_pat_price[$v_ipid];
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat"]['total'] =  number_format( round(($amount_of_days[$v_ipid] * $hospiz_normal_pat_price[$v_ipid]),2) , '2', '.', '');
    				$hospiz_normal_pat_total[$v_ipid] = round(($amount_of_days[$v_ipid] * $hospiz_normal_pat_price[$v_ipid]),2);
    					
    					
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat_5_percent"]['shortcut'] = "hospiz_normal_pat_5_percent";
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat"]['description'] = $this->view->translate("shortcut_name_hospiz_normal_pat_5_percent");
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat_5_percent"]['qty'] = $amount_of_days[$v_ipid];
    				$hospiz_normal_pat_5_percent_price[$v_ipid] =  round( ((5 / 100) * $hospiz_normal_pat_price[$v_ipid]) ,2);
    				$hospiz_normal_pat_5_percent_total[$v_ipid] =  round(($amount_of_days[$v_ipid] * $hospiz_normal_pat_5_percent_price[$v_ipid]),2);
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat_5_percent"]['price'] = number_format($hospiz_normal_pat_5_percent_price[$v_ipid]  * (-1) , '2', '.', ''); // negative amount
    				$invoice_data[$v_ipid]['items']["hospiz_normal_pat_5_percent"]['total'] =   number_format($hospiz_normal_pat_5_percent_total[$v_ipid] * (-1) , '2', '.', ''); // negative amount
    				$inv_normal_pat_total[$v_ipid] =  $hospiz_normal_pat_total - $hospiz_normal_pat_5_percent_total[$v_ipid];
    					
    					
    				$invoice_data[$v_ipid]['invoice_total'] = number_format($inv_normal_pat_total[$v_ipid] , '2', '.', '');
    				$invoice_data[$v_ipid]['sub_invoice_total'] = number_format($inv_normal_pat_total[$v_ipid] , '2', '.', '');
    				$invoice_data[$v_ipid]['invoice_total_normal'] = number_format($inv_normal_pat_total[$v_ipid] , '2', '.', '');
    				// pflegestufe lines
    					
    				if($highest[$v_ipid]){
    					if($highest[$v_ipid] == "3+")
    					{
    						$pflegestufe[$v_ipid] = "3_5";
    					}
    					else
    					{
    						$pflegestufe[$v_ipid] = $highest[$v_ipid];
    					}
    
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_".$pflegestufe[$v_ipid]]['shortcut'] = "care_level_".$pflegestufe[$v_ipid];
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_".$pflegestufe[$v_ipid]]['qty'] = $amount_of_days[$v_ipid];
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_".$pflegestufe[$v_ipid]]['price'] = $pricelist[$v_ipid][0]["care_level_".$pflegestufe[$v_ipid]]['price'];
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_".$pflegestufe[$v_ipid]]['description'] = $pricelist[$v_ipid][0]["care_level_".$pflegestufe[$v_ipid]]['description'];
    
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_".$pflegestufe[$v_ipid]]['total_round'] = round(($amount_of_days[$v_ipid] * $pricelist[$v_ipid][0]["care_level_".$pflegestufe[$v_ipid]]['price']),2);
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_".$pflegestufe[$v_ipid]]['total'] = number_format($invoice_data[$v_ipid]['pflegestufe_items']["care_level_".$pflegestufe[$v_ipid]]['total_round'], '2', '.', '') ;
    					//     		        $invoice_data['pflegestufe_items']["care_level_".$pflegestufe]['total'] = round(($amount_of_days[$ipid] * $pricelist[$ipid][0]["care_level_".$pflegestufe]['price']),2);
    
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_remaining"]['shortcut'] = "care_level_remaining";
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_remaining"]['qty'] = "";
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_remaining"]['price'] = "";
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_remaining"]['description'] = "Verbleibender Restbetrag";
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_remaining"]['total_round'] = $invoice_data[$v_ipid]['invoice_total_normal'] - round(($amount_of_days[$v_ipid] * $pricelist[$v_ipid][0]["care_level_".$pflegestufe[$v_ipid]]['price']),2) ;
    					$invoice_data[$v_ipid]['pflegestufe_items']["care_level_remaining"]['total'] = number_format($invoice_data[$v_ipid]['pflegestufe_items']["care_level_remaining"]['total_round'], '2', '.', '');
    					//     		        $invoice_data['pflegestufe_items']["care_level_remaining"]['total'] = $invoice_data['invoice_total_normal'] - round(($amount_of_days[$ipid] * $pricelist[$ipid][0]["care_level_".$pflegestufe]['price']),2) ;
    				}
    				else
    				{
    					$invoice_data[$v_ipid]['invoice_total'] = number_format($invoice_data[$v_ipid]['items']["hospiz_normal_pat"]['total'] + $invoice_data[$v_ipid]['items']["hospiz_normal_pat_5_percent"]['total'] , '2', '.', '');//$invoice_data['items']["hospiz_normal_pat"]['total'] + $invoice_data['items']["hospiz_normal_pat_5_percent"]['total'] ;
    					$invoice_data[$v_ipid]['sub_invoice_total'] = number_format($invoice_data[$v_ipid]['items']["hospiz_normal_pat"]['total'] + $invoice_data[$v_ipid]['items']["hospiz_normal_pat_5_percent"]['total'] , '2', '.', '');//$invoice_data['items']["hospiz_normal_pat"]['total'] + $invoice_data['items']["hospiz_normal_pat_5_percent"]['total'] ;
    				}
    			}
    		}
    		//print_R($invoice_data); exit;
    		 
    		$pseudo_post = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$pseudo_post['ipids'] = array($v_ipid);
    			$pseudo_post['ipid'] = $v_ipid;
    			$pseudo_post['items'] = $invoice_data[$v_ipid]['items'];
    
    			$pseudo_post['start_invoice'] = date('Y-m-d', strtotime($invoice_data[$v_ipid]['start_active']));
    			$pseudo_post['end_invoice'] = date('Y-m-d', strtotime($invoice_data[$v_ipid]['end_active']));
    
    			$pseudo_post['invoice_total'] = $invoice_data[$v_ipid]['invoice_total'];
    
    			$pseudo_post['patientdetails'] = $patient_days[$v_ipid]['details'];
    			$pseudo_post['patient_pflegestufe'] = $highest[$v_ipid];
    			$pseudo_post['insurance_no'] = $pathelathinsu[$v_ipid]['insurance_no'];
    
    			$pseudo_post['invoice'] = $invoice_data[$v_ipid];
    			if($privat[$v_ipid] == "1")
    			{
    				$pseudo_post['invoice']['type'] = "private";
    			}
    			else
    			{
    				$pseudo_post['invoice']['type'] = "normal";
    			}
    
    			$pseudo_post['invoice']['period']['start'] = date('Y-m-d', strtotime($invoice_data[$v_ipid]['start_active']));
    			$pseudo_post['invoice']['period']['end'] = date('Y-m-d', strtotime($invoice_data[$v_ipid]['end_active']));
    			$pseudo_post['client']['id'] = $clientid;
    
    			//new columns
    			$pseudo_post['debitor_number'] = $pathelathinsu[$v_ipid]['debitor_number'];
    			$pseudo_post['ppun'] = $pathelathinsu[$v_ipid]['ppun'];
    			//--
    			//print_r($pseudo_post); exit;
    			if($_REQUEST['only_pdf'] == '0')
    			{
    				// invoice number - TEMP
    				$high_invoice_nr = $hospiz_invoices->get_next_invoice_number($clientid, true);
    				$prefix = $high_invoice_nr['prefix'];
    				$invoicenumber = $high_invoice_nr['invoicenumber'];
    					
    				$pseudo_post['prefix'] = $prefix;
    				$pseudo_post['invoice_number'] = $invoicenumber;
    				$insert_invoice = $hospiz_invoice_form->insert_invoice($pseudo_post);
    
    				$_REQUEST['iid'] = $insert_invoice[0];
    
    			}
    
    			if($_REQUEST['get_pdf'] == '1')
    			{
    				$params['sapv_overall'][$v_ipid] = array_values($sapv_days_overall[$v_ipid]);
    				$params['ipids'] = array($v_ipid);
    				$params['patient_sapvs'] = $patients_sapv[$v_ipid];
    				$params['patient_days'] = $patient_days[[$v_ipid]];
    				$params['get_pdf'] = '1';
    				$params['only_pdf'] = (int) $_REQUEST['only_invoice'];
    				$params['stornopdf'] = (int) $_REQUEST['stornopdf'];
    				$params['stornoid'] = (int) $_REQUEST['stornoid'];
    				if(!empty($_REQUEST['iid']))
    				{
    					$params['invoices'] = array((int) $_REQUEST['iid']);
    				}
    
    				$params['redirect2new'] = 1;
			    	//TODO-3727 Ancuta 12.01.2021
    				//$invoicenewController->hospizinvoice($params);
    				$this->forward('hospizinvoice', 'Invoicenew', null, $params);
    				//$this->hospizinvoice($params);
    			}
    		}
    		 
    		$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=hospiz_invoice');
    	}
    }
    
    //ISPC-2609 + ISPC-2000 Ancuta 24.09.2020 - add client param
    private function get_period_contact_forms($ipid, $current_period, $sgbxi = false, $duration = false, $duration_after_death = false, $clientid = false)
    {
    	if($duration_after_death){
    
    
    		if(!$clientid){//ISPC-2609 + ISPC-2000 Ancuta 24.09.2020 - add client param
    			$logininfo = new Zend_Session_Namespace('Login_Info');
    			$clientid = $logininfo->clientid;
    		}
    
    		$patientdischarge = new PatientDischarge();
    		$discharge_method = new DischargeMethod();
    		$patient_discharge = $patientdischarge->getPatientDischarge($ipid);
    		$discharge_dead_date = '';
    		if($patient_discharge)
    		{
    			//get discharge methods
    			$discharge_methods = $discharge_method->getDischargeMethod($clientid, 0);
    
    			foreach($discharge_methods as $k_dis_method => $v_dis_method)
    			{
    				if($v_dis_method['abbr'] == "TOD" || $v_dis_method['abbr'] == "TODNA")
    				{
    					$death_methods[] = $v_dis_method['id'];
    				}
    			}
    			$death_methods = array_values(array_unique($death_methods));
    
    			if(in_array($patient_discharge[0]['discharge_method'], $death_methods))
    			{
    				$discharge_dead_date = date('Y-m-d', strtotime($patient_discharge[0]['discharge_date']));
    				$discharge_dead_date_time = date('Y-m-d H:i:00', strtotime($patient_discharge[0]['discharge_date']));
    			}
    		}
    
    	}
    
    	$contact_from_course = Doctrine_Query::create()
    	->select("id,ipid,recordid, AES_DECRYPT(course_type,'" . Zend_Registry::get('salt') . "') as course_type,AES_DECRYPT(course_title,'" . Zend_Registry::get('salt') . "') as course_title, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
    	->from('PatientCourse')
    	->where('ipid ="' . $ipid . '"')
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
    
    	$contact_form_visits = Doctrine_Query::create()
    	->select("*")
    	->from("ContactForms")
    	->where('ipid = ?', $ipid);
    	if(!empty($deleted_contact_forms)){
    		$contact_form_visits->andWhereNotIn('id', $deleted_contact_forms);
    	}
    	$contact_form_visits->andWhere('DATE(billable_date) BETWEEN ? AND ?', array(date("Y-m-d",strtotime($current_period['start'])),date("Y-m-d",strtotime($current_period['end'])) ))
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
    				$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$v_contact_visit['end_date']);
    			}
    			elseif($duration_after_death)
    			{
    				if(!empty($discharge_dead_date_time)){
    
    					// RE calculate visit duration  // ISPC 2051
    					$visit_start_date = strtotime(date('Y-m-d H:i:00', strtotime($v_contact_visit['start_date'])));
    					$visit_end_date = strtotime(date('Y-m-d H:i:00', strtotime($v_contact_visit['end_date'])));
    					$a1start = strtotime($discharge_dead_date_time);
    					$a1end = strtotime($discharge_dead_date_time);
    
    					if(Pms_CommonData::isintersected($visit_start_date, $visit_end_date, $a1start, $a1end))
    					{
    						$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$discharge_dead_date_time);
    					}
    					else
    					{
    						$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$v_contact_visit['end_date']);
    					}
    				}
    				else
    				{
    					$v_contact_visit['visit_duration'] = Pms_CommonData::calculate_visit_durationbydates($v_contact_visit['start_date'],$v_contact_visit['end_date']);
    				}
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
    
    private function generatebyinvoice($params)
    {
    	if(isset($params['print_job']) && $params['print_job'] == '1'){
    		$this->_helper->layout->setLayout('layout_ajax');
    		$this->_helper->viewRenderer->setNoRender();
    	}
    
    	setlocale(LC_ALL, 'de_DE.UTF-8');
    	//$logininfo = new Zend_Session_Namespace('Login_Info');
    	//$tm = new TabMenus();
    	$p_list = new PriceList();
    	//$form_types = new FormTypes();
    	$sapvs = new SapvVerordnung();
    	$patientmaster = new PatientMaster();
    	//$sapvverordnung = new SapvVerordnung();
    	$pflege = new PatientMaintainanceStage();
    	$hi_perms = new HealthInsurancePermissions();
    	$phelathinsurance = new PatientHealthInsurance();
    	$healthinsu_subdiv = new PatientHealthInsurance2Subdivisions();
    	//$boxes = new LettersTextBoxes();
    	$client = new Client();
    	$invoices = new ClientInvoices();
    	$invoicesForm = new Application_Form_Invoices();
    	//$lc = new Locations();
    	$loca = new PatientLocation();
    	$ppun = new PpunIpid();
    	/*$famdoc = new FamilyDoctor();
    	 $pc = new ContactPersonMaster();
    	 $familydegree = new FamilyDegree();
    	 $pharmacy = new PatientPharmacy();
    	 $m_specialists_types = new SpecialistsTypes();
    	 $specialists = new PatientSpecialists();
    	 $m_supplies =  new PatientSupplies();
    	 $suppliers = new PatientSuppliers();
    	 $physiotherapists = new PatientPhysiotherapist();
    	 $m_homecare = new PatientHomecare(); */
    	 
    	if(isset($params) && !empty($params)){
    		$_REQUEST = $params;
    		$this->_helper->viewRenderer->setNoRender();
    	}
    
    	//var_dump($_REQUEST); exit;
    	//ISPC-2609 + ISPC-2000 Ancuta 22-24.09.2020
    	$clientid = isset($_REQUEST['clientid']) && ! empty($_REQUEST['clientid']) ? $_REQUEST['clientid'] :  $this->clientid;
    	$userid = isset($_REQUEST['userid']) && ! empty($_REQUEST['userid']) ? $_REQUEST['userid'] : $this->userid;
    
    	//$letter_boxes_details = $boxes->client_letter_boxes($clientid);
    	$client_details = $client->getClientDataByid($clientid);
    	$this->view->client_details = $client_details[0];
    
    	$modules =  new Modules();
    	$clientModules = $modules->get_client_modules($clientid);
    	 
    	$verordnets = Pms_CommonData::getSapvCheckBox(true,true);
    	 
    	foreach($verordnets as $type => $vv_sh)
    	{
    		$bayern_sapv_types[$vv_sh] = $type;
    	}
    	 
    	$shortcuts = Pms_CommonData::get_prices_shortcuts();
    
    	$ipids = $_REQUEST['ipids'];
    
    	//load template data
    	$template_data = InvoiceTemplates::get_template($clientid, false, '1', $_REQUEST['invoice_type']);
    
    	/* if(!$template_data && $_REQUEST['get_pdf'] == '1'){
    	 $this->redirect(APP_BASE . 'invoiceclient/patientlist?flg=notemplate');
    	 exit;
    	 } */
    
    	if(!empty($ipids))
    	{
    		//patients locations
    		$patloc = Doctrine_Query::create()
    		->select('*')
    		->from('PatientLocation')
    		->whereIn('ipid', $ipids)
    		->andWhere('isdelete="0"')
    		->limit(1)
    		->orderBy('ipid ASC, id ASC')
    		->fetchArray();
    
    		$patlocbyipid = array();
    		foreach($patloc as $kpat => $vpat)
    		{
    			$patlocbyipid[$vpat['ipid']][] = $vpat;
    		}
    		//patient days
    		$conditions['client'] = $clientid;
    		$conditions['ipids'] = $ipids;
    		$conditions['periods'][0]['start'] = '2009-01-01';
    		$conditions['periods'][0]['end'] = date('Y-m-d');
    		 
    		$sql = 'e.epid, p.ipid, e.ipid,';
    		$sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
    		$sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
    		$sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
    		$sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
    		$sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
    		$sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
    		 
    		//be aware of date d.m.Y format here
    		$patient_days = Pms_CommonData::patients_days($conditions, $sql);
    
    		$patient_admissions = $patientmaster->getTreatedDaysRealMultiple($ipids);
    		//var_dump($patient_days); exit;
    		$all_patients_periods = array();
    		foreach($patient_days as $k_ipid => $patient_data)
    		{
    			//all patients periods
    			$all_patients_periods = array_merge_recursive($all_patients_periods, $patient_data['active_periods']);
    
    			//used in flatrate
    			if(empty($patient_periods[$k_ipid]))
    			{
    				$patient_periods[$k_ipid] = array();
    			}
    
    			array_walk_recursive($patient_data['active_periods'], function(&$value) {
    				$value = date("Y-m-d", strtotime($value));
    			});
    				$patient_periods[$k_ipid] = array_merge($patient_periods[$k_ipid], $patient_data['active_periods']);
    
    				//hospital days cs
    				if(!empty($patient_data['hospital']['real_days_cs']))
    				{
    					$hospital_days_cs[$k_ipid] = $patient_data['hospital']['real_days_cs'];
    					array_walk($hospital_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//hospiz days cs
    				if(!empty($patient_data['hospiz']['real_days_cs']))
    				{
    					$hospiz_days_cs[$k_ipid] = $patient_data['hospiz']['real_days_cs'];
    					array_walk($hospiz_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//real active days
    				if(!empty($patient_data['real_active_days']))
    				{
    					$active_days_in_period_cs[$k_ipid] = $patient_data['real_active_days'];
    					array_walk($active_days_in_period_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//treatment days
    				if(!empty($patient_data['treatment_days']))
    				{
    					$treatment_days_cs[$k_ipid] = $patient_data['treatment_days'];
    					array_walk($treatment_days_cs[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				//active days
    				if(!empty($patient_data['active_days']))
    				{
    					$active_days[$k_ipid] = $patient_data['active_days'];
    					array_walk($active_days[$k_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    				}
    
    				if(empty($hospital_days_cs[$k_ipid]))
    				{
    					$hospital_days_cs[$k_ipid] = array();
    				}
    
    				if(empty($hospiz_days_cs[$k_ipid]))
    				{
    					$hospiz_days_cs[$k_ipid] = array();
    				}
    
    				$hospital_hospiz_days_cs[$k_ipid] = array_merge($hospital_days_cs[$k_ipid], $hospiz_days_cs[$k_ipid]);
    
    		}
    		 
    		$all_patients_periods = array_values($all_patients_periods);
    		 
    		foreach($all_patients_periods as $k_period => $v_period)
    		{
    			if(empty($months))
    			{
    				$months = array();
    			}
    
    			$period_months = Pms_CommonData::get_period_months($v_period['start'], $v_period['end'], 'Y-m');
    			$months = array_merge($months, $period_months);
    		}
    		$months = array_values(array_unique($months));
    		 
    		foreach($months as $k_m => $v_m)
    		{
    			$months_unsorted[strtotime($v_m)] = $v_m;
    		}
    		ksort($months_unsorted);
    		$months = array_values(array_unique($months_unsorted));
    		 
    		foreach($months as $k_month => $v_month)
    		{
    			if(!function_exists('cal_days_in_month'))
    			{
    				$month_days = date('t', mktime(0, 0, 0, date("n", strtotime($v_month . "-01")), 1, date("Y", strtotime($v_month . "-01"))));
    			}
    			else
    			{
    				$month_days = cal_days_in_month(CAL_GREGORIAN, date("n", strtotime($v_month . "-01")), date("Y", strtotime($v_month . "-01")));
    			}
    
    			$months_details[$v_month]['start'] = $v_month . "-01";
    			$months_details[$v_month]['days_in_month'] = $month_days;
    			$months_details[$v_month]['end'] = $v_month . '-' . $month_days;
    
    			//$month_select_array[$v_month] = $v_month;
    			$month_days_arr[date('Ym', strtotime($months_details[$v_month]['start']))] = $patientmaster->getDaysInBetween($months_details[$v_month]['start'], $months_details[$v_month]['end']);
    		}
    
    		//loop throuhg posted patients (0 = no sapv period, >0 = sapv period id)
    		foreach($ipids as $k_sel_pat => $v_sel_pat)
    		{
    			//get patients sapvs last fall
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'sapvid')
    			{
    				$selected_sapv_falls_ipids[] = $v_sel_pat;
    				$selected_sapv_falls[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			//get patient month fall
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'list')
    			{
    				$selected_fall_ipids[] = $v_sel_pat;
    				$selected_month[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'admission')
    			{
    				$admission_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    			if($_REQUEST[$v_sel_pat]['period_type'] == 'quarter')
    			{
    				$quarter_fall[$v_sel_pat] = $_REQUEST[$v_sel_pat]['selected_period'];
    			}
    		}
    		 
    		$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($selected_sapv_falls_ipids, true);
    		foreach($selected_sapv_falls as $k_ipid => $fall_id)
    		{
    			$patients_sapv[$k_ipid] = $fall_id;
    			$patients_selected_periods[$k_ipid] = $patients_sapv_periods[$k_ipid][$fall_id];
    			$_REQUEST['nosapvperiod'][$k_ipid] = '0';
    			$_REQUEST['period'] = $patients_selected_periods;
    		}
    
    		//rewrite the periods array if the period is entire month not sapv fall
    		$patients_sapv_periods = SapvVerordnung::get_patients_sapv_periods($ipids, true);
    
    		foreach($patients_sapv_periods as $k_sapv_ipid => $v_sapvs)
    		{
    			foreach($v_sapvs as $k_sapv_id => $v_sapv_data)
    			{
    				if(empty($sapv_days[$v_sapv_data['ipid']]))
    				{
    					$sapv_days[$v_sapv_data['ipid']] = array();
    				}
    					
    				$sapv_days[$v_sapv_data['ipid']] = array_merge($sapv_days[$v_sapv_data['ipid']], $v_sapv_data['days']);
    				$sapv_days[$v_sapv_data['ipid']] = array_values(array_unique($sapv_days[$v_sapv_data['ipid']]));
    			}
    		}
    		 
    
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			if(!in_array($v_ipid, $selected_sapv_falls_ipids))
    			{
    				if(array_key_exists($v_ipid, $admission_fall))
    				{
    					$selected_period[$v_ipid] = $patient_days[$v_ipid]['active_periods'][$admission_fall[$v_ipid]];
    
    					array_walk($selected_period[$v_ipid], function(&$value) {
    						$value = date("Y-m-d", strtotime($value));
    					});
    							
    						$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($selected_period[$v_ipid]['start'], $selected_period[$v_ipid]['end']);
    							
    						array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    
    							$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    							$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    							$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    
    
    							array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    								$value = date("d.m.Y", strtotime($value));
    							});
    									
    								//exclude outside admission falls days from sapv!
    								if(empty($sapv_days[$v_ipid]))
    								{
    									$sapv_days[$v_ipid] = array();
    								}
    									
    								if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    								{
    									$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    								}
    								$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    								$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    									
    								$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    								$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    									
    								$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    								$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    									
    								//get all days of all sapvs in a period
    								$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    								$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    									
    									
    								$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    								$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    									
    								$last_sapv_data['ipid'] = $v_ipid;
    								$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    								$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    								$sapv_last_require_data[] = $last_sapv_data;
    
    								$_REQUEST['admissionid'][$v_ipid] = $admission_fall[$v_ipid];
    				}
    				//ISPC-2461
    				elseif(array_key_exists($v_ipid, $quarter_fall))
    				{
    					$post_q = $_REQUEST[$v_ipid]['selected_period'];
    					$post_q_arr = explode("/",$post_q);
    					$q_no = (int)$post_q_arr[0];
    					$q_year = (int)$post_q_arr[1];
    
    					$q_per = array();
    					$quarter_start = "";
    					$quarter_end = "";
    
    					$q_per = Pms_CommonData::get_dates_of_quarter($q_no,$q_year,'Y-m-d');
    					$quarter_start = $q_per['start'];
    					$quarter_end = $q_per['end'];
    
    					$selected_period[$v_ipid] = array();
    					$selected_period[$v_ipid]['days'] = PatientMaster::getDaysInBetween($quarter_start, $quarter_end);
    					$selected_period[$v_ipid]['start'] = $quarter_start;
    					$selected_period[$v_ipid]['end'] = $quarter_end;
    
    					array_walk($selected_period[$v_ipid]['days'], function(&$value) {
    						$value = date("d.m.Y", strtotime($value));
    					});
    							
    						$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    						$_REQUEST['selected_period'][$v_ipid] = $selected_period[$v_ipid];
    						$_REQUEST['selected_period'][$v_ipid]['days'] = $selected_period[$v_ipid]['days'];
    							
    							
    						array_walk($_REQUEST['selected_period'][$v_ipid]['days'], function(&$value) {
    							$value = date("d.m.Y", strtotime($value));
    						});
    
    							//exclude outside admission falls days from sapv!
    							if(empty($sapv_days[$v_ipid]))
    							{
    								$sapv_days[$v_ipid] = array();
    							}
    
    							if(empty($_REQUEST['selected_period'][$v_ipid]['days']))
    							{
    								$_REQUEST['selected_period'][$v_ipid]['days'] = array();
    							}
    							$patient_active_sapv_days[$v_ipid] = array_intersect($_REQUEST['selected_period'][$v_ipid]['days'], $sapv_days[$v_ipid]);
    							$_REQUEST['sapv_in_period'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    
    							$start_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['start']));
    							$end_dmy = date('d.m.Y', strtotime($selected_period[$v_ipid]['end']));
    
    							$start_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['start']));
    							$end_sapv_dmy = date('d.m.Y', strtotime($months_details[$selected_month]['end']));
    
    							//get all days of all sapvs in a period
    							$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($patient_active_sapv_days[$v_ipid]);
    							$_REQUEST['period'][$v_ipid] = $selected_period[$v_ipid];
    
    							$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    							$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    
    							$last_sapv_data['ipid'] = $v_ipid;
    							$last_sapv_data['start_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['start']));
    							$last_sapv_data['end_period'] = date('Y-m-d', strtotime($selected_period[$v_ipid]['end']));
    							$sapv_last_require_data[] = $last_sapv_data;
    
    							$_REQUEST['quarterid'][$v_ipid] = $quarter_fall[$v_ipid];
    				}
    				else
    				{
    
    					$start_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['start']));
    					$end_dmy = date('d.m.Y', strtotime($months_details[$selected_month[$v_ipid]]['end']));
    
    					$_REQUEST['nosapvperiod'][$v_ipid] = '1';
    					$_REQUEST['selected_period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    					$_REQUEST['sapv_in_period'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    					$_REQUEST['existing_sapv_days'][$v_ipid] = array_values($months_details[$selected_month[$v_ipid]]['days']);
    					$_REQUEST['period'][$v_ipid] = $months_details[$selected_month[$v_ipid]];
    					$_REQUEST['period'][$v_ipid]['start'] = $start_dmy;
    					$_REQUEST['period'][$v_ipid]['end'] = $end_dmy;
    
    					$last_sapv_data['ipid'] = $v_ipid;
    					$last_sapv_data['start_period'] = date('Y-m-d', strtotime($start_dmy));
    					$last_sapv_data['end_period'] = date('Y-m-d', strtotime($end_dmy));
    					$sapv_last_require_data[] = $last_sapv_data;
    				}
    			}
    		}
    		//print_r($_REQUEST); exit;
    		//get all sapv details
    		$all_sapvs = array();
    		$all_sapvs = $sapvs->get_all_sapvs($ipids);
    		 
    		$sapv2ipid = array();
    		/* foreach($all_sapvs as $k=>$sdata){
    			$sapv2ipid[$sdata['ipid']][] = $sdata;
    		} */
    
    		foreach($all_sapvs as $k_sapv => $v_sapv)
    		{
    			$sapv2ipid[$v_sapv['ipid']][] = $v_sapv;
    			if(empty($sapv_days_overall[$v_sapv['ipid']]))
    			{
    				$sapv_days_overall[$v_sapv['ipid']] = array();
    			}
    
    			$start = date('Y-m-d', strtotime($v_sapv['verordnungam']));
    
    			if($v_sapv['status'] == '1' && $v_sapv['verorddisabledate'] != '0000-00-00 00:00:00')
    			{
    				$end = date('Y-m-d', strtotime($v_sapv['verorddisabledate']));
    			}
    			else
    			{
    				$end = date('Y-m-d', strtotime($v_sapv['verordnungbis']));
    			}
    
    			//FIND A WAY TO GET MULTIPLE LAST SAPV IN EACH PERIOD FOR EACH PATIENT
    			if($last_sapvs_in_period[$v_sapv['ipid']])
    			{
    				$_REQUEST['period'][$v_sapv['ipid']] = array_merge($_REQUEST['period'][$v_sapv['ipid']], $last_sapvs_in_period[$v_sapv['ipid']]);
    			}
    
    			$sapv_days_overall[$v_sapv['ipid']] = array_merge($sapv_days_overall[$v_sapv['ipid']], PatientMaster::getDaysInBetween($start, $end));
    			array_walk($sapv_days_overall[$v_sapv['ipid']], function(&$value) {
    				$value = date("d.m.Y", strtotime($value));
    			});
    				$sapv_days_overall[$v_sapv['ipid']] = array_values(array_unique($sapv_days_overall[$v_sapv['ipid']]));
    		}
    
    		foreach($last_sapvs_in_period as $k_sapvs => $v_sapvs)
    		{
    			foreach($v_sapvs as $k_sapvp => $v_sapvp)
    			{
    				$startp = date('Y-m-d', strtotime($v_sapvp['verordnungam']));
    
    				if($v_sapvp['status'] == '1' && $v_sapvp['verorddisabledate'] != '0000-00-00 00:00:00')
    				{
    					$endp = date('Y-m-d', strtotime($v_sapvp['verorddisabledate']));
    				}
    				else
    				{
    					$endp = date('Y-m-d', strtotime($v_sapvp['verordnungbis']));
    				}
    				if(empty($period_sapv_alldays[$v_sapvp['ipid']]))
    				{
    					$period_sapv_alldays[$v_sapvp['ipid']] = array();
    				}
    				$period_sapv_alldays[$v_sapvp['ipid']] = array_merge($period_sapv_alldays[$v_sapvp['ipid']], PatientMaster::getDaysInBetween($startp, $endp));
    			}
    		}
    
    
    		$_REQUEST['period_sapvs_alldays'] = $period_sapv_alldays;
    		$_REQUEST['sapv_overall'] = $sapv_days_overall;
    
    		$current_period = array();
    		foreach($_REQUEST['period'] as $ipidp => $vipidp)
    		{
    			$current_period[$ipidp] = $vipidp;
    			if($_REQUEST['sapv_in_period'][$ipidp])
    			{
    				$sapv_in_period[$ipidp] = $_REQUEST['sapv_in_period'][$ipidp];
    			}
    			else 
    			{
    				$sapv_in_period[$ipidp] = array();
    			}
    		}
    
    		$all_pflegestufe = array();
    		$all_pflegestufe = Doctrine_Query::create()
    		->select("*")
    		->from('PatientMaintainanceStage')
    		->whereIn("ipid", $ipids)
    		->orderBy('fromdate,create_date asc')
    		->fetchArray();
    
    		$pflegesufe2ipid = array();
    		foreach($all_pflegestufe as $k=>$pflg) {
    			$pflegesufe2ipid[$pflg['ipid']][] = $pflg;
    		}
    		//print_R($pflegesufe2ipid); exit;
    
    		//Healthinsurance
    		$healthinsu_multi_array = array();
    		$healthinsu_multi_array = $phelathinsurance->get_multiple_patient_healthinsurance($ipids, true);
    
    		foreach($healthinsu_multi_array as $k_hi => $v_hi)
    		{
    			$hi_companyids[] = $v_hi['companyid'];
    		}
    
    		//multiple hi subdivisions && hi subdivisions permissions
    		$divisions = HealthInsurancePermissions::getClientHealthInsurancePermissions($clientid);
    
    		//patientheathinsurance
    		if($divisions)
    		{
    			/* foreach($healthinsu_multi_array as $k_hi => $v_hi)
    			 {
    			 $hi_companyids[] = $v_hi['companyid'];
    			 } */
    
    			$healthinsu_subdiv_arr = PatientHealthInsurance2Subdivisions::get_hi_subdivisions_multiple($hi_companyids);
    		}
    		 
    		$pathelathinsu = array();
    		$patient_address = array();
    		$hi_address = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$pathelathinsu[$v_ipid]['insurance_no'] = $healthinsu_multi_array[$v_ipid]['insurance_no'];
    			if($healthinsu_multi_array[$v_ipid]['companyid'] == "")
    			{
    				$pathelathinsu[$v_ipid]['healthinsurancename'] = $healthinsu_multi_array[$v_ipid]['company_name'];
    				$pathelathinsu[$v_ipid]['healthinsurancecontact'] = $healthinsu_multi_array[$v_ipid]['insurance_provider'];
    				$pathelathinsu[$v_ipid]['healthinsurancestreet'] = $healthinsu_multi_array[$v_ipid]['street1'];
    				$pathelathinsu[$v_ipid]['healthinsuranceaddress'] = $healthinsu_multi_array[$v_ipid]['zip'] . " " . $healthinsu_multi_array[$v_ipid]['city'];
    			}
    			else
    			{
    				$pathelathinsu[$v_ipid]['healthinsurancename'] = $healthinsu_multi_array[$v_ipid]['company']['name'];
    				$pathelathinsu[$v_ipid]['healthinsurancecontact'] = $healthinsu_multi_array[$v_ipid]['company']['insurance_provider'];
    				$pathelathinsu[$v_ipid]['healthinsurancestreet'] = $healthinsu_multi_array[$v_ipid]['company']['street1'];
    				$pathelathinsu[$v_ipid]['healthinsuranceaddress'] = $healthinsu_multi_array[$v_ipid]['company']['zip'] . " " . $healthinsu_multi_array[$v_ipid]['company']['city'];
    			}
    
    			if($healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				//			get patient name and adress
    				$pathelathinsu[$v_ipid]['healthinsurancecontact'] = htmlspecialchars($patient_days[$v_ipid]['details']['first_name']) . ' ' . htmlspecialchars($patient_days[$v_ipid]['details']['last_name']);
    				$pathelathinsu[$v_ipid]['healthinsurancestreet'] = htmlspecialchars($patient_days[$v_ipid]['details']['street1']);
    				$pathelathinsu[$v_ipid]['healthinsuranceaddress'] = htmlspecialchars($patient_days[$v_ipid]['details']['zip']) . ' ' . '&nbsp;' . htmlspecialchars($patient_days[$v_ipid]['details']['city']);
    
    			}
    
    			if(!empty($healthinsu_subdiv_arr[$v_ipid]['1']['name']))
    			{
    				//get new SAPV hi address
    				$hi_address[$v_ipid] = '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['name'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['insurance_provider'] . '<br />';
    				//$hi_address .= '&nbsp;' . $healthinsu_subdiv_arr[1]['contact_person'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['street1'] . '<br />';
    				$hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_subdiv_arr[$v_ipid][1]['zip'] . ' ' . $healthinsu_subdiv_arr[$v_ipid][1]['city'];
    
    				$pathelathinsu[$v_ipid]['healthinsurancename'] = $healthinsu_subdiv_arr[3]['name'];
    				$pathelathinsu[$v_ipid]['healthinsurancecontact'] = $healthinsu_subdiv_arr[3]['insurance_provider'];
    				$pathelathinsu[$v_ipid]['healthinsurancestreet'] = $healthinsu_subdiv_arr[3]['street1'];
    				$pathelathinsu[$v_ipid]['healthinsuranceaddress'] = $healthinsu_subdiv_arr[3]['zip'] . " " . $healthinsu_subdiv_arr[3]['city'];
    				 
    			}
    			/* else
    			 {
    			 //get old hi_address
    			 $hi_address[$v_ipid] = '&nbsp;' . $pathelathinsu[$v_ipid]['name'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_insurance_provider'] . '<br />';
    			 //$hi_address .= '&nbsp;' . $healthinsu_array[0]['ins_contactperson'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_street'] . '<br />';
    			 $hi_address[$v_ipid] .= '&nbsp;' . $healthinsu_multi_array[$v_ipid]['ins_zip'] . ' ' . $healthinsu_multi_array[$v_ipid]['ins_city'];
    
    			 $pathelathinsu[$v_ipid]['health_insurance_ik'] = $healthinsu_multi_array[$v_ipid]['institutskennzeichen'];
    			 $pathelathinsu[$v_ipid]['health_insurance_kassenr'] = $healthinsu_multi_array[$v_ipid]['company']['kvk_no'];
    			 $pathelathinsu[$v_ipid]['health_insurance_status'] = $healthinsu_multi_array[$v_ipid]['insurance_status'];
    			 } */
    
    			if(count($patient_admissions[$v_ipid]['admissionDates']) != "0")
    			{
    				//gesamt limitation
    				$admissionsCycles[$v_ipid][-1]['start'] = date("d.m.Y", strtotime($patient_admissions[$v_ipid]['admissionDates'][0]['date']));
    				 
    				foreach($patient_admissions[$v_ipid]['admissionDates'] as $keyAdm => $admitedDate)
    				{
    					if(!empty($patient_admissions[$v_ipid]['dischargeDates'][$keyAdm]['date']))
    					{
    						$dischargeDate[$v_ipid] = $patient_admissions[$v_ipid]['dischargeDates'][$keyAdm]['date'];
    					}
    					else
    					{
    						$dischargeDate[$v_ipid] = date("d.m.Y");
    					}
    					 
    					$admCycle[$keyAdm] = date("d.m.Y", strtotime($admitedDate['date'])) . " - " . date("d.m.Y", strtotime($dischargeDate[$v_ipid]));
    					$admissionsCycles[$v_ipid][$keyAdm]['start'] = date("d.m.Y", strtotime($admitedDate['date']));
    					$admissionsCycles[$v_ipid][$keyAdm]['end'] = date("d.m.Y", strtotime($dischargeDate[$v_ipid]));
    					 
    					if($keyAdm == (count($patient_admissions[$v_ipid]['admissionDates']) - 1))
    					{
    						$admissionsCycles[$v_ipid][-1]['end'] = date("d.m.Y", strtotime($dischargeDate[$v_ipid]));
    					}
    				}
    			}
    			else
    			{
    				$admCycle[$v_ipid][0] = date("d.m.Y", strtotime($patient_admissions[$v_ipid]['admission_date'])) . " - " . date("d.m.Y", strtotime($admissions[$ipid_sel]['discharge_date']));
    				//gesamt if no admision-readmission cycle
    				$admissionsCycles[$v_ipid][-1]['start'] = date("d.m.Y", strtotime($patient_admissions[$v_ipid]['admission_date']));
    				$admissionsCycles[$v_ipid][-1]['end'] = date("d.m.Y", strtotime($patient_admissions[$v_ipid]['discharge_date']));
    				$admissionsCycles[$v_ipid][0]['start'] = date("d.m.Y", strtotime($patient_admissions[$v_ipid]['admission_date']));
    				$admissionsCycles[$v_ipid][0]['end'] = date("d.m.Y", strtotime($patient_admissions[$v_ipid]['discharge_date']));
    			}
    
    			foreach($admissionsCycles[$v_ipid] as $cycle_key => $cycle_date)
    			{
    				 
    				$current_period[$v_ipid]['start'] = date("Y-m-d", strtotime($cycle_date['start']));
    				$current_period[$v_ipid]['end'] = date("Y-m-d", strtotime($cycle_date['end']));
    				 
    				if(strtotime(date('Y-m-d', strtotime($_REQUEST['period'][$v_ipid]['start']))) == strtotime($current_period[$v_ipid]['start']) && strtotime(date('Y-m-d', strtotime($_REQUEST['period'][$v_ipid]['end']))) == strtotime($current_period[$v_ipid]['end']))
    				{
    					$sel_val[$v_ipid] = $cycle_key;
    				}
    				// 					$master_price_list[$cycle_key] = $p_list->get_period_price_list($current_period['start'], $current_period['end']); // CHanged the function  on 18.03.2019 TODO-2194
    				$master_price_list[$v_ipid][$cycle_key] = $p_list->get_period_price_list_day2type($current_period[$v_ipid]['start'], $current_period[$v_ipid]['end']);
    			}
    			 
    			foreach($admissionsCycles[$v_ipid] as $ckey => $cdate)
    			{
    				$cycle_period[$v_ipid]['start'] = date("Y-m-d", strtotime($cdate['start']));
    				$cycle_period[$v_ipid]['end'] = date("Y-m-d", strtotime($cdate['end']));
    				 
    				foreach($shortcuts['bayern_sapv'] as $shs)
    				{
    					$price_list[$v_ipid][$ckey][$bayern_sapv_types[$shs]] = $master_price_list[$v_ipid][$ckey][$cycle_period[$v_ipid]['start']][0]['bayern_sapv'][$shs]['price'];
    				}
    			}
    			 
    			$verdarray[$v_ipid] = $sapvs->getFormoneAllSapvInPeriods($v_ipid, $admissionsCycles[$v_ipid]);
    			 
    			if(count($verdarray[$v_ipid]) > 0)
    			{
    				foreach($verdarray[$v_ipid] as $kAdmCycle => $verord)
    				{
    					foreach($verord as $vKey => $verordValue)
    					{
    						 
    						if(strlen($verordValue['verordnet']) > 1)
    						{
    							//explode value
    							$verords = explode(",", $verordValue['verordnet']);
    							foreach($verords as $verordnet)
    							{
    								$finalVerordnet[$v_ipid][$kAdmCycle][] = $verordnet;
    							}
    						}
    						else
    						{
    							$finalVerordnet[$v_ipid][$kAdmCycle][] = $verordValue['verordnet'];
    						}
    					}
    					 
    					if(count($finalVerordnet[$v_ipid][$kAdmCycle]) > 0)
    					{
    						$finalVerordnet[$v_ipid][$kAdmCycle] = max(array_unique($finalVerordnet[$v_ipid][$kAdmCycle]));
    					}
    					else
    					{
    						$finalVerordnet[$v_ipid][$kAdmCycle] = 0;
    					}
    				}
    			}
    			
    			$highsapv[$v_ipid] = $finalVerordnet[$v_ipid][$sel_val[$v_ipid]];
    			
    			$invoiceamount[$v_ipid] = $price_list[$v_ipid][$sel_val[$v_ipid]][max($finalVerordnet[$v_ipid])];
    			
    			//new columns
    			if($clientModules['90'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "0")
    			{
    				//get debtor number from patient healthinsurance
    				if(strlen($healthinsu_multi_array[$v_ipid]['ins_debtor_number']) > '0')
    				{
    					$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['ins_debtor_number'];
    				}
    				else
    				{
    					$pathelathinsu[$v_ipid]['debitor_number'] = $healthinsu_multi_array[$v_ipid]['company']['debtor_number'];
    				}
    			}
    			if($clientModules['88'] && $healthinsu_multi_array[$v_ipid]['privatepatient'] == "1")
    			{
    				//get ppun (private patient unique number)
    				$ppun_number = $ppun->check_patient_ppun($v_ipid, $clientid);
    				if($ppun_number)
    				{
    					$pathelathinsu[$v_ipid]['ppun']= $ppun_number['ppun'];
    					$pathelathinsu[$v_ipid]['debitor_number']= $ppun_number['ppun'];
    				}
    			}
    			//--
    			 
    		}
    		//print_R($invoice_data); exit;
    		 
    		$pseudo_post = array();
    		foreach($ipids as $k_ipid => $v_ipid)
    		{
    			$pseudo_post['patientipid'] = $v_ipid;
    			$pseudo_post['patientepid'] = $patient_days[$v_ipid]['details']['epid'];
    			$pseudo_post['nvg'] = $patient_days[$v_ipid]['details']['last_name'] . ', ' . $patient_days[$v_ipid]['details']['first_name'] . ', ' . date('d.m.Y', strtotime($patient_days[$v_ipid]['details']['birthd']));
    			$pseudo_post['admissionlocation'] = $patlocbyipid[$v_ipid][0]['location_id'];
    
    			$pseudo_post['status'] = '4';
    			$pseudo_post['cycle'] = $sel_val[$v_ipid];
    
    			$pseudo_post['invoiceamount'] = $invoiceamount[$v_ipid];
    			$pseudo_post['letterdate'] = date('d.m.Y', time());
    
    			$pseudo_post['healthinsurancenumber'] = $pathelathinsu[$v_ipid]['insurance_no'];
    
    			$pseudo_post['healthinsurancename'] = $pathelathinsu[$v_ipid]['healthinsurancename'];
    			$pseudo_post['healthinsurancecontact'] = $pathelathinsu[$v_ipid]['healthinsurancecontact'];
    			$pseudo_post['healthinsurancestreet'] = $pathelathinsu[$v_ipid]['healthinsurancestreet'];
    			$pseudo_post['healthinsuranceaddress'] = $pathelathinsu[$v_ipid]['healthinsuranceaddress'];
    
    			$pseudo_post['highsapv'] = $highsapv[$v_ipid];
    			 
    			//new columns
    			$pseudo_post['debitor_number'] = $pathelathinsu[$v_ipid]['debitor_number'];
    			$pseudo_post['ppun'] = $pathelathinsu[$v_ipid]['ppun'];
    			//--
    			//print_r($pseudo_post); exit;
    			if($_REQUEST['only_pdf'] == '0')
    			{
    				// invoice number - TEMP
    				$invoice_number_arr = $invoices->get_next_invoice_number($clientid, true);
    				$prefix = $invoice_number_arr['prefix'];
    				$invoicenumber = $invoice_number_arr['invoicenumber'];
    
    				$pseudo_post['prefix'] = $prefix;
    				$pseudo_post['invoicenumber'] = $invoicenumber;
    				$pseudo_post['draft'] = 'Entwurf';
    					
    				$invoice = $invoicesForm->InsertData($pseudo_post);
    
    			}
    		}
    		
    		$this->redirect(APP_BASE . 'invoiceclient/invoices?invoice_type=by_invoice');
    	}
    }
    
}