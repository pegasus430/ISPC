<?php
/*
 * @auth Ancuta 16.03.2020
 * ISPC-2428
 */
class Pms_Reports_Report222ExtarnalInternalInvoices extends Pms_Reports_Common
{

    public function __construct()
    {
        parent::__construct();
    }

    public function ExtarnalInternalInvoices($params)
    {
        $clientid = static::$logininfo->clientid;
        $userid = static::$logininfo->userid;
        
        $quarterarr = $params['quarterarr'];
        $yeararr = $params['yeararr'];
        $montharr = $params['montharr'];
        $includearr = $params['includearr'];
        $onlyactive = $params['onlyactive'];
        
        $active_cond = self::getTimePeriod($quarterarr, $yeararr, $montharr);
        
        $period = Pms_CommonData::getPeriodDates($quarterarr, $yeararr, $montharr);
        $report_period_array = array();
        foreach ($period['start'] as $keyd => $startDate) {
            $report_period_array[$keyd]['start'] = date("Y-m-d", strtotime($startDate));
            if (strtotime($period['end'][$keyd]) > strtotime(date("Y-m-d"))) {
                $report_period_array[$keyd]['end'] = date("Y-m-d");
            } else {
                $report_period_array[$keyd]['end'] = date("Y-m-d", strtotime($period['end'][$keyd]));
            }
        }
        
        
       // THIS NEED To BE CHANGED 
        $external_type = "ShInvoices"; 
        $external_items = "ShInvoiceItems"; 
        $external_invoice_arr = array();
        
        $internal_type = "ShShiftsInternalInvoices";
        $internal_items = "ShShiftsInternalInvoiceItems"; 
        $internal_invoice_arr = array();
        // --- 
        
        
        switch ($external_type){
            
            case 'ShInvoices':
                // get storno invoices
                $external_storno = Doctrine_Query::create()->select('*')
                    ->from('ShInvoices')
                    ->andWhere('isdelete=0')
                    ->andWhere('storno = 1')
                    ->andWhere('client = ?', $clientid)
                    ->fetchArray();
//                     dd($external_storno);
                $external_strono_invoices_ids = array();
                $ex_storno_ids_str_sql ="";
                if (! empty($external_storno)) {
                    $external_strono_invoices_ids = array_column($external_storno, 'record_id');
                    if(!empty($external_strono_invoices_ids)){
                        $ex_storno_ids_str_sql = " AND inv.id NOT IN (" . implode(',',$external_strono_invoices_ids) . ")";
                    }
                }
                
                
                $s = array('%date_start%', '%date_end%');
                $r = array('inv.invoice_start', 'inv.invoice_end');
                
                $external_inv_arr = Doctrine_Query::create()
                ->select('inv.*, 
                        inv_items.id, 
                        inv_items.invoice, 
                        inv_items.client,
                        inv_items.shortcut, 
                        inv_items.description,
                        inv_items.qty,
                        inv_items.price,
                        inv_items.total,
                        inv_items.custom,
                        inv_items.isdelete')
                ->from('ShInvoices as inv')
                ->leftJoin('inv.ShInvoiceItems as inv_items')
                ->andWhere('inv.isdelete=0 and ((' . str_replace($s, $r, $active_cond ['interval_sql']) . '))')
                ->andWhere('inv.client = ?',$clientid)
                ->andWhere('inv.storno = 0 '.$ex_storno_ids_str_sql )
                ->andWhere('inv_items.isdelete = 0') 
                ->fetchArray();
                $external_invoice_arr['ShInvoices'] = $external_inv_arr;
                
                break;
        }
        
        switch ($internal_type){
            
            case 'ShShiftsInternalInvoices':
                // get storno invoices
                $internal_storno = Doctrine_Query::create()->select('*')
                ->from('ShShiftsInternalInvoices')
                ->andWhere('isdelete=0')
                ->andWhere('client = ?', $clientid)
                ->andWhere('storno = 1')
                ->fetchArray();
                
                $internal_strono_invoices_ids = array();
                $in_storno_ids_str_sql="";
                if (! empty($internal_storno)) {
                    $internal_strono_invoices_ids = array_column($internal_storno, 'record_id');
                    if(!empty($internal_strono_invoices_ids)){
                        $in_storno_ids_str_sql = " AND inv.id NOT IN (" . implode(',',$internal_strono_invoices_ids) . ")";
                    }
                }
                
                $s = array('%date_start%', '%date_end%');
                $r = array('inv.invoice_start', 'inv.invoice_end');
                
                $internal_inv_arr = Doctrine_Query::create()
                ->select('inv.*, 
                        inv_items.id, 
                        inv_items.invoice, 
                        inv_items.client,
                        inv_items.shortcut, 
                        inv_items.description,
                        inv_items.qty,
                        inv_items.price,
                        inv_items.total,
                        inv_items.custom,
                        inv_items.isdelete')
                ->from('ShShiftsInternalInvoices as inv')
                ->leftJoin('inv.ShShiftsInternalInvoiceItems as inv_items')
                ->andWhere('inv.isdelete=0 and ((' . str_replace($s, $r, $active_cond ['interval_sql']) . '))')
                ->andWhere('inv.client = ?',$clientid)
                ->andWhere('inv.storno = 0 '.$in_storno_ids_str_sql)
                ->andWhere('inv_items.isdelete = 0') 
                ->fetchArray();
                
                $internal_invoice_arr['ShShiftsInternalInvoices'] =  $internal_inv_arr;
                break;
        }

        if ( empty($external_invoice_arr[$external_type]) && empty($internal_invoice_arr[$internal_type]) ){
            return;
        }
        
        
        // match external invoices with internal Invoices
        
        
        
        $invoices_ipids = array();
        $external_ipids = array();
        $internal_ipids = array();
        
        $external2ipid = array();
        $external2internal = array();
        $patient_invoices = array();
        if(!empty($external_invoice_arr[$external_type])){
            $external_ipids = array_column($external_invoice_arr[$external_type], 'ipid');
            foreach($external_invoice_arr[$external_type] as $k=>$ex_inv){
                $external2ipid[$ex_inv['ipid']][] = $ex_inv;
                $patient_invoices[$ex_inv['ipid']]['external'][]= $ex_inv;
            }
        }
        
        
        $internal2ipid = array();
        $internal2external = array();
        if(!empty($internal_invoice_arr[$internal_type])){
            $internal_ipids = array_column($internal_invoice_arr[$internal_type], 'ipid');
            foreach($internal_invoice_arr[$internal_type] as $ki=>$in_inv){
                $internal2ipid[$in_inv['ipid']][] = $in_inv;
                $patient_invoices[$in_inv['ipid']]['internal'][]= $in_inv;
            }
        }
        $invoices_ipids = array_merge($external_ipids,$internal_ipids);
        $invoices_ipids = array_unique($invoices_ipids);
//         dd($patient_invoices);
//         $invoices_ipids = array('09b4894a93bda7fedf793bf208b8a40da2de99fb');
        //7467
        $external_total = array();
        $patient_totals = array();
        foreach($invoices_ipids as $ipid){
            foreach ($patient_invoices[$ipid]['external'] as $k=>$exinv ){
                $patient_totals[$ipid][$exinv['id']]['external'] = $exinv['invoice_total'];
                $external_total[$exinv['id']] = $exinv['invoice_total'];
                foreach($patient_invoices[$ipid]['internal'] as $key=>$ininv){
                    if(date('d.m.Y',strtotime($exinv['invoice_start'])) == date('d.m.Y',strtotime($ininv['invoice_start'])) 
                        && date('d.m.Y',strtotime($exinv['invoice_end'])) == date('d.m.Y',strtotime($ininv['invoice_end']))
                         ){
                        $external2internal[$exinv['id']][] = $ininv['id'];
                        $external_total[$exinv['id']] -= $ininv['invoice_total'];
                        $patient_totals[$ipid][$exinv['id']]['internal'][] = $ininv['invoice_total'];
//                         $internal_total[$exinv['id']] += $ininv['invoice_total'];
                        $internal2external[ $ininv['id']]  = $exinv['id'];
                    }
                }
            }
        }
        
//         dd($patient_totals,$total);
//         dd ($total,$external2internal,$internal2external);
        
//         $sql = Pms_Reports_Common::getSqlString();
        
        $sql = 'e.epid, p.ipid, e.ipid,';
        $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
        $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
        $sql .= "AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as gender,";
        $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
        $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
        $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
        $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
        $sql .= "IF(p.admission_date != '0000-00-00',DATE_FORMAT(p.admission_date,'%d\.%m\.%Y'),'') as day_of_admission,";
        $sql .= "IF(p.birthd != '0000-00-00',DATE_FORMAT(p.birthd,'%d\.%m\.%Y'),'') as birthd,";
        $sql .= "p.familydoc_id,";
        
        
        $patients_array = array();
        $patients_q = Doctrine_Query::create()
        ->select($sql)
        ->from('PatientMaster p indexby ipid')
        ->where('isdelete = 0')
        ->andWhereIn('ipid',$invoices_ipids);
        $patients_q->leftJoin("p.EpidIpidMapping e");
        $patients_q->andWhere('e.ipid = p.ipid  and e.clientid = ' .$clientid);
        $patients_q->orderBy("convert(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1) ASC");
        $patients_array = $patients_q->fetchArray();
        
        $external_rows = array();
        if(!empty($external_invoice_arr[$external_type])){
            $el = 0 ;
            foreach($external_invoice_arr[$external_type] as $ek=>$e_inv ){
                if(!empty($e_inv[$external_items])){
                    foreach($e_inv[$external_items] as $eik=>$e_items){
                        $external_rows[$el]['patient_epid'] = $patients_array[$e_inv['ipid']]['EpidIpidMapping']['epid'];
                        $external_rows[$el]['patient_last_name'] = $patients_array[$e_inv['ipid']]['last_name'];
                        $external_rows[$el]['patient_first_name'] = $patients_array[$e_inv['ipid']]['first_name'];
                        $external_rows[$el]['invoice_number'] = "EXTERNAL: ".$e_inv['prefix'].$e_inv['invoice_number'].' '.$e_inv['id'];
                        $external_rows[$el]['product_name'] = $e_items['shortcut'].' '.$e_items['description'];
                        $external_rows[$el]['product_total'] = $e_items['total'];
                        $external_rows[$el]['invoice_total'] = $e_inv['invoice_total'];
                        $external_rows[$el]['delta'] = external_total[$e_inv['id']];;
                        $el++;
                    }
                }
            }
        }
        
        $internals_rows = array();
        if(!empty($internal_invoice_arr[$internal_type])){
            $il = 0 ;
            foreach($internal_invoice_arr[$internal_type] as $ik=>$i_inv ){
                if(!empty($i_inv[$internal_items])){
                    foreach($i_inv[$internal_items] as $iik=>$i_items){
                        $internals_rows[$il]['patient_epid'] = $patients_array[$i_inv['ipid']]['EpidIpidMapping']['epid'];
                        $internals_rows[$il]['patient_last_name'] = $patients_array[$i_inv['ipid']]['last_name'];
                        $internals_rows[$il]['patient_first_name'] = $patients_array[$i_inv['ipid']]['first_name'];
                        $internals_rows[$il]['invoice_number'] = "INTERNAL: ".$i_inv['prefix'].$i_inv['invoice_number'].' '.$i_inv['id'].' '.$internal2external[$i_inv['id']];
                        $internals_rows[$il]['product_name'] = $i_items['shortcut'].' '.$i_items['description'];
                        $internals_rows[$il]['product_total'] = $i_items['total'];
                        $internals_rows[$il]['invoice_total'] = $i_inv['invoice_total'];
                        $internals_rows[$il]['delta'] =$external_total[$internal2external[$i_inv['id']]];
                        $il++;
                    }
                }
            }
        }
        $invoices_rows = array();
        $invoices_rows['data'] = array_merge($external_rows,$internals_rows);
        
        
        
           
        return $invoices_rows;
         
    }
    
    public static function getTimePeriod($quarterarr, $yeararr, $montharr)
    {
        if($quarterarr == 'only_now' && $yeararr == 'only_now' && $montharr == 'only_now')
        {
            $active_sql = '(date(%date%) >= "' . date('Y') . '-' . date('m') . '-' . date('d') . '") OR ';
            $admission_sql = '(date(%date%) < "' . date('Y') . '-' . (date('m') + 1) . '-01") OR ';
            $date_sql = ' year(%date%) > "1900" AND ';
            $interval_location_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
            $interval_location_sql_qtz = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
            $interval_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
            $negated_interval_sql = '(year(%date_start%) < "1900" AND year(%date_end%) > "2100") OR ';
            $readmission_delete_sql = '(year(%date_start%) >= "1900" AND year(%date_end%) <= "2100") OR ';
            $onlynowactive = 1;
            $periods = null;
        }
        else
        {
            $onlynowactive = 0;
            if(!empty($quarterarr))
            {
                $montharr = array();
                foreach($quarterarr as $quart)
                {
                    switch($quart)
                    {
                        case '2':
                            $montharr[] = 4;
                            $montharr[] = 5;
                            $montharr[] = 6;
                            break;
                            
                        case '3':
                            $montharr[] = 7;
                            $montharr[] = 8;
                            $montharr[] = 9;
                            break;
                            
                        case '4':
                            $montharr[] = 10;
                            $montharr[] = 11;
                            $montharr[] = 12;
                            break;
                            
                        default:
                            $montharr[] = 1;
                            $montharr[] = 2;
                            $montharr[] = 3;
                            break;
                    }
                }
            }
            
            foreach($yeararr as $year)
            {
                if(is_numeric($year))
                {
                    $year_sql .= '"' . $year . '",';
                    
                    if(is_array($montharr) && sizeof($montharr))
                    {
                        foreach($montharr as $month)
                        {
                            if(is_numeric($month))
                            {
                                $this_month = $year . '-' . $month . '-01';
                                $this_month_end = date('Y-m-d', strtotime('-1 day', strtotime('+1 month', strtotime($this_month))));
                                $next_month = date('Y-m-', strtotime('+1 month', strtotime($this_month))) . '01';
                                
                                $active_sql .= '(date(%date%) >= "' . $year . '-' . $month . '-01") OR ';
                                $admission_sql .= '(date(%date%) < "' . $next_month . '") OR ';
                                $interval_location_sql .= '(((date(%date_start%) >= "' . $year . '-' . $month . '-01") AND date(%date_start%) < "' . $next_month . '" AND (date(%date_end%) >= "' . $year . '-' . $month . '-01"  OR date(%date_end%) = "0000-00-00") ) OR ((date(%date_start%) >= "' . $year . '-' . $month . '-01" AND date(%date_start%) < "' . $next_month . '") AND (date(%date_end%) < "' . $next_month . '" AND (date(%date_end%) >= "' . $year . '-' . $month . '-01" OR date(%date_end%) = "0000-00-00")))) OR ';
                                $interval_location_sql_qtz .= '(((date(%date_start%) >= "' . $year . '-' . $month . '-01") AND date(%date_start%) < "' . $next_month . '" AND (date(%date_end%) >= "' . $year . '-' . $month . '-01"  OR date(%date_end%) = "0000-00-00 0000:00:00")) or ((date(%date_start%) <= "' . $year . '-' . $month . '-01") AND (date(%date_end%) >= "' . $year . '-' . $month . '-01")) or ((date(%date_start%) <= "' . $year . '-' . $month . '-01") AND (date(%date_end%) = "0000-00-00 0000:00:00"))     OR ((date(%date_start%) >= "' . $year . '-' . $month . '-01" AND date(%date_start%) < "' . $next_month . '") AND (date(%date_end%) < "' . $next_month . '" AND (date(%date_end%) >= "' . $year . '-' . $month . '-01" OR date(%date_end%) = "0000-00-00 0000:00:00")))  OR (date(%date_start%) <="' . $year . '-' . $month . '-01" AND date(%date_end%) =  "0000-00-00 0000:00:00") OR (date(%date_start%) > "' . $year . '-' . $month . '-01" AND date(%date_start%) < "' . $next_month . '" AND date(%date_end%) =  "0000-00-00 0000:00:00" )) OR ';
                                $interval_sql .= '(((date(%date_start%) <= "' . $year . '-' . $month . '-01") AND (date(%date_end%) >= "' . $year . '-' . $month . '-01")) OR ((date(%date_start%) >= "' . $year . '-' . $month . '-01") AND (date(%date_start%) < "' . $next_month . '"))) OR ';
                                $negated_interval_sql .= '((date(%date_start%) < "' . $year . '-' . $month . '-01") AND date(%date_end%) < "' . $year . '-' . $month . '-01" AND %date_end% IS NOT NULL) OR  date(%date_start%) >= "' . $next_month . '") OR ';
                                $readmission_delete_sql .= '(%date_start% IS NOT NULL AND (date(%date_start%) < "' . $year . '-' . $month . '-01") AND date(%date_end%) > "' . $this_month_end . '") AND ';
                                
                                $periods[] = array(
                                    'start' =>  $year . '-' . $month . '-01',
                                    'end' => $this_month_end
                                );
                                
                            }
                            else
                            {
                                $active_sql .= '(year(%date%) >= "' . $year . '") OR ';
                                $admission_sql .= '(year(%date%) <= "' . $year . '") OR ';
                                $interval_location_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
                                $interval_location_sql_qtz .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
                                $interval_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
                                $negated_interval_sql .= '((year(%date_start%) > "' . $year . '") OR (year(%date_end%) < "' . $year . '")) OR ';
                                $readmission_delete_sql .= '(%date_start% IS NOT NULL AND (year(%date_start%) < "' . $year . '") AND (year(%date_end%) > "' . $year . '")) AND ';
                                
                                $periods[] = array(
                                    'start' => $year.'-01-01',
                                    'end' => ($year+1).'-01-01'
                                );
                            }
                        }
                    }
                    else
                    {
                        $active_sql .= '(year(%date%) >= "' . $year . '") OR ';
                        $admission_sql .= '(year(%date%) <= "' . $year . '") OR ';
                        //$interval_sql .= '((year(%date_start%) >= "'.$year.'") AND (year(%date_end%) <= "'.$year.'")) OR ';
                        $interval_location_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
                        $interval_location_sql_qtz .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) or ((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) = "0000")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
                        $interval_sql .= '(((year(%date_start%) <= "' . $year . '") AND (year(%date_end%) >= "' . $year . '")) OR ((year(%date_start%) >= "' . $year . '") AND (year(%date_start%) < "' . ($year + 1) . '"))) OR ';
                        $negated_interval_sql .= '((year(%date_start%) > "' . $year . '") OR (year(%date_end%) < "' . $year . '" AND %date_end% IS NOT NULL)) OR ';
                        $readmission_delete_sql .= '(%date_start% IS NOT NULL AND (year(%date_start%) < "' . $year . '") AND (year(%date_end%) > "' . $year . '")) AND ';
                        
                        $periods[] = array(
                            'start' => $year.'-01-01',
                            'end' => ($year+1).'-01-01'
                        );
                    }
                }
            }
            
            foreach($montharr as $month)
            {
                if(is_numeric($month))
                {
                    $month_sql .= '"' . $month . '",';
                }
            }
            
            if(!empty($month_sql))
            {
                $date_sql .= ' month(%date%) IN (' . substr($month_sql, 0, -1) . ') AND ';
            }
            
            if(!empty($year_sql))
            {
                $date_sql .= ' year(%date%) IN (' . substr($year_sql, 0, -1) . ') AND ';
            }
        }
        
        if(!empty($date_sql))
        {
            $return['date_sql'] = substr($date_sql, 0, -5);
            $return['active_sql'] = substr($active_sql, 0, -4);
            $return['admission_sql'] = substr($admission_sql, 0, -4);
            $return['interval_location_sql'] = substr($interval_location_sql, 0, -4);
            $return['interval_location_sql_qtz'] = substr($interval_location_sql_qtz, 0, -4);
            $return['interval_sql'] = substr($interval_sql, 0, -4);
            $return['negated_interval_sql'] = substr($negated_interval_sql, 0, -4);
            $return['readmission_delete_sql'] = substr($readmission_delete_sql, 0, -4);
            $return['onlynowactive'] = $onlynowactive;
            $return['periods'] = $periods;
            
            return $return;
        }
        else
        {
            return false;
        }
    }
}