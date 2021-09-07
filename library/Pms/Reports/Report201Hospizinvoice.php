<?php
/*
 * @auth Ancuta 03.04.2019
 * ISPC-2354
 */

class Pms_Reports_Report201Hospizinvoice extends Pms_Reports_Common
{

    public function __construct()
    {
        parent::__construct();
    }

    public static function Hospizinvoice($params)
    {
        
        // report_id = 201
        // report_name = hospiz invoices - ISPC-2356
        $clientid = static::$logininfo->clientid;
        $userid = static::$logininfo->userid;
        $quarterarr = $params['quarterarr'];
        $yeararr = $params['yeararr'];
        $montharr = $params['montharr'];
        $includearr = $params['includearr'];
        $onlyactive = $params['onlyactive'];
        
        $period = Pms_CommonData::getPeriodDates($quarterarr, $yeararr, $montharr);
        
        foreach ($period['start'] as $keyd => $startDate) {
            $report_period_array[$keyd]['start'] = date("Y-m-d", strtotime($startDate));
            if (strtotime($period['end'][$keyd]) > strtotime(date("Y-m-d"))) {
                $report_period_array[$keyd]['end'] = date("Y-m-d");
            } else {
                $report_period_array[$keyd]['end'] = date("Y-m-d", strtotime($period['end'][$keyd]));
            }
        }
      
        $sql = Pms_Reports_Common::getSqlString();
        $conditions['periods'] = $report_period_array;
        $conditions['include_standby'] = false;
        $conditions['client'] = $clientid;
        
        $patient_days = Pms_CommonData::patients_days($conditions, $sql);
        
        $ipids = array_keys($patient_days);
        
        $invoicesdd = Doctrine_Query::create()->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
            ->from('HospizInvoices INDEXBY id')
            ->where("client= ?", $clientid)
            ->andWhere('storno = 1');
        $storno_invoices_res = $invoicesdd->fetchArray();
        $stornos = array();
        foreach ($storno_invoices_res as $k => $invdet) {
            $stornos[] = $invdet['record_id'];
        }
        
        $invoices = Doctrine_Query::create()->select("*, IF(completed_date = '0000-00-00 00:00:00', create_date, IF(completed_date = '1970-01-01 01:00:00', create_date, completed_date)) as completed_date_sort")
            ->from('HospizInvoices INDEXBY id')
            ->where("client= ?", $clientid)
            ->andWhereIn('ipid', $ipids)
            ->andWhere('isdelete = 0')
            ->andWhere('storno != 1')
            ->andWhereNotIn('status', array(
            '1',
            '4'
        ));
        if (! empty($stornos)) {
            $invoices->andWhereNotIn('id', $stornos);
        }
        
        $invoices_res = $invoices->fetchArray();
        $invoices_ids = array();
        foreach ($invoices_res as $inv_id => $invoice_data) {
            $invoices_ids[] = $invoice_data['id'];
        }
        
        if (! empty($invoices_ids)) {
            $hospiz_invoice_items = new HospizInvoiceItems();
            $invoice_items = $hospiz_invoice_items->getInvoicesItems($invoices_ids);
            foreach ($invoice_items as $inv_ids => $items) {
                foreach ($items as $k => $itm_Vals) {
                    if ($itm_Vals['shortcut'] == "hospiz_pv_pat") {
                        $invoice2days[$itm_Vals['invoice']] = $itm_Vals['qty'];
                    }
                }
                
                $invoices_res[$inv_ids]['items'] = $items;
            }
        }
        $overall_billed_days = 0;
        foreach ($invoices_res as $inv_id => $invoice_data) {
            if (! in_array($invoice_data['id'], $stornos)) {
                
                $inv2patient[$invoice_data['ipid']][] = date('d.m.Y', strtotime($invoice_data['start_active'])) . ' - ' . date('d.m.Y', strtotime($invoice_data['end_active'])) . ' (Items: ' . $invoice2days[$invoice_data['id']] . ')';
                $billed_days[$invoice_data['ipid']] += $invoice2days[$invoice_data['id']];
                $overall_billed_days += $invoice2days[$invoice_data['id']];
            }
        }
        
        $total = array();
        
        foreach ($patient_days as $ipid => $pdata) {
            
            $masterdata['data'][$ipid]['epid'] = $pdata['details']['epid'];
            $masterdata['data'][$ipid]['active_days_number'] = $pdata['real_active_days_no'];
            $masterdata['data'][$ipid]['hospital_days_number'] = $pdata['hospital']['real_days_cs_no'];
            $masterdata['data'][$ipid]['hospiz_days_number'] = $pdata['hospiz']['real_days_cs_no'];
            $masterdata['data'][$ipid]['treatment_days_number'] = $pdata['treatment_days_no'];
            $masterdata['data'][$ipid]['invoiced_pers'] = ! empty($inv2patient[$ipid]) ? implode("<br/>", $inv2patient[$ipid]) : "No invoice";
            $masterdata['data'][$ipid]['billed_items'] = ! empty($billed_days[$ipid]) ? "  " . $billed_days[$ipid] : "";
            
            $total['real_active_days_no'] += $pdata['real_active_days_no'];
            $total['hospital_real_days_cs_no'] += $pdata['hospital']['real_days_cs_no'];
            $total['hospiz_real_days_cs_no'] += $pdata['hospiz']['real_days_cs_no'];
            $total['treatment_days_no'] += $pdata['treatment_days_no'];
        }
        
        $masterdata['extra']['sum_active_days'] = $total['real_active_days_no'];
        $masterdata['extra']['sum_hospital_days'] = $total['hospital_real_days_cs_no'];
        $masterdata['extra']['sum_hospiz_days'] = $total['hospiz_real_days_cs_no'];
        $masterdata['extra']['sum_treatment_days'] = $total['treatment_days_no'];
        
        $masterdata['extra']['sum_billed_items'] = $overall_billed_days;
        
        return $masterdata;
    }
}