<?php
/*
 * @auth Ancuta 04.04.2019
 * ISPC-2354
 */

class Pms_Reports_Report203MamboPatientNotBilled extends Pms_Reports_Common
{

    public function __construct()
    {
        parent::__construct();
    }

    public function MamboPatientNotBilled($params)
    {
        
        // report_id = 203
        // report_name = "Mambo - Arzt nicht abgerechnet" - ISPC-2354
        // "Mambo - Patient nicht abgerechnet"
        // filters for all patients active in report period which have the Icon "Patient nicht abgerechnet
        // " (this is available only in NR_Leverkusen MAMBO"
        
        // LE_Datum = date of latest entry in verlauf
        // Pat_Vers_Nr = health insurance patient number
        // Pat_EinschreibeDatum = patient admission date
        // Doc_Name = famialy doc surname
        // Doc_Vorname = famialy doc firstname
        // Doc_LANR = family doctor LANR
        // Pat_abgerechnet = "FALSCH"
        // Pat_Namen = Patient Surname
        // Pat_Vorname = Patient firstname
        // Pat_Geb_Datum = DoB
        // Pat_AusschreibeDatum = discharge date
        $clientid = static::$logininfo->clientid;
        $userid = static::$logininfo->userid;
        $quarterarr = $params['quarterarr'];
        $yeararr = $params['yeararr'];
        $montharr = $params['montharr'];
        $includearr = $params['includearr'];
        $onlyactive = $params['onlyactive'];
        
        $period = Pms_CommonData::getPeriodDates($quarterarr, $yeararr, $montharr);
        $patientmaster =  new PatientMaster();
        $period_days  = array(); 
        foreach ($period['start'] as $keyd => $startDate) {
            $report_period_array[$keyd]['start'] = date("Y-m-d", strtotime($startDate));
            if (strtotime($period['end'][$keyd]) > strtotime(date("Y-m-d"))) {
                $report_period_array[$keyd]['end'] = date("Y-m-d");
            } else {
                $report_period_array[$keyd]['end'] = date("Y-m-d", strtotime($period['end'][$keyd]));
            }
            $period_days = $patientmaster->getDaysInBetween($report_period_array[$keyd]['start'] ,$report_period_array[$keyd]['end']);
        }
        
        $sql = Pms_Reports_Common::getSqlString();
        
        $conditions['periods'] = $report_period_array;
        $conditions['include_standby'] = false;
        $conditions['client'] = $clientid;
        
        $patient_days = Pms_CommonData::patients_days($conditions, $sql);
        
        $ipids = array_keys($patient_days);
        
        if(empty($ipids)){
            return;
        }
        $patientDetails = array();
        $patientDetails = array_column($patient_days, 'details');
        

        /* ----------------------- Get icons -------------------------------- */
        $client_icons = new IconsClient();
        $client_icons_details = $client_icons->get_client_icons($clientid);
        
        $needed_icon_arr = array_filter($client_icons_details, function ($i)
        {
            return trim($i['name']) == 'Patient nicht abgerechnet';
        });
        $needed_icon_ids = array_column($needed_icon_arr, 'id');
        
     
        
        $patient_icons = new IconsPatient();
        $patient_icons_details = $patient_icons->get_patient_icons($ipids);
        
        $patientWithIcons = array_filter($patient_icons_details, function ($pi) use($needed_icon_ids)
        {
            return in_array($pi['icon_id'], $needed_icon_ids);
        });
        
        /* ----------------------- Get valid patients with icons  -------------------------------- */
        $valid_patients = array_column($patientWithIcons, 'ipid');
        
        $patientDetailsFinal = array();
        $patientDetailsFinal = array_filter($patientDetails, function ($pi) use($valid_patients)
        {
            return in_array($pi['ipid'], $valid_patients);
        });
        
        
        if(empty($patientDetailsFinal)){
            return;
        }
        
        $final_ipids =  array_column($patientDetailsFinal, 'ipid');
        
        /* ----------------------- Get family doctor details-------------------------------- */
        $doctorids = array();
        $doctorids = array_column($patientDetailsFinal, 'familydoc_id'); // only for valid patients 
        
        $familydoctors = array();
        if (! empty($doctorids)) {
            
            $famdoc = new FamilyDoctor();
            $familidoc = $famdoc->getFamilyDoctors(false, false, false, $doctorids);
        
            foreach ($familidoc as $fd_key => $fd_value) {
                $familydoctors[$fd_value['id']] = $fd_value;
            }
        }
        
        /* ----------------------- Get LE entries  -------------------------------- */
        $patient_course = new PatientCourse();
        $le_courses = $patient_course->get_multi_pat_shortcuts_course($final_ipids, array('LE'), false, false);

        if(!empty($le_courses)){
            $leInPeriod = array_filter($le_courses, function ($pi) use($period_days)
            {
                return in_array(date("Y-m-d",strtotime($pi['done_date'])), $period_days);
            });
            
            foreach($leInPeriod as $k=>$le_entry){
                $leInPeriod2patient[$le_entry['ipid']][$le_entry['done_date']] = $le_entry;
            }
            foreach($final_ipids as $fipid){
                ksort($leInPeriod2patient[$fipid]);
                
                $last_entries[$fipid] = end($leInPeriod2patient[$fipid]) ;
            }
        }
        /* ----------------------- Get Healthinsurance-------------------------------- */
        
        $aptient_health = Doctrine_Query::create()
        ->select("ipid,insurance_no")
        ->from('PatientHealthInsurance')
        ->whereIn('ipid', $final_ipids)
        ->fetchArray();
        
        $patient_healthinsurance  =array();
        foreach($aptient_health as $patient_health)
        {
            $patient_healthinsurance[$patient_health['ipid']]['number'] = $patient_health["insurance_no"];
        }

        /* ----------------------- Display data -------------------------------- */
        foreach ($patientDetailsFinal as $k => $pdata) {
            $last_period[$pdata['ipid']] = end($patient_days[$pdata['ipid']]['patient_active']);
            
            
            $masterdata['data'][$pdata['ipid']]['last_LE_verlauf'] = !empty($last_entries[$pdata['ipid']]) ? date("d.m.Y",strtotime($last_entries[$pdata['ipid']]['done_date'])): " - "; 
            $masterdata['data'][$pdata['ipid']]['pat_insurance_no'] = $patient_healthinsurance[$pdata['ipid']]['number'];
            $masterdata['data'][$pdata['ipid']]['pat_adm_date'] = date('d.m.Y', strtotime($last_period[$pdata['ipid']]['start']));
            $masterdata['data'][$pdata['ipid']]['famdoc_last_name'] = $familydoctors[$pdata['familydoc_id']]['last_name'];
            $masterdata['data'][$pdata['ipid']]['famdoc_first_name'] = $familydoctors[$pdata['familydoc_id']]['first_name'];
            $masterdata['data'][$pdata['ipid']]['famdoc_lanr'] = $familydoctors[$pdata['familydoc_id']]['doctornumber'];
            $masterdata['data'][$pdata['ipid']]['pat_c_icon_pna'] = "FALSCH";

            $masterdata['data'][$pdata['ipid']]['pat_name'] = $pdata['last_name'];
            $masterdata['data'][$pdata['ipid']]['pat_first_name'] = $pdata['first_name'];
            $masterdata['data'][$pdata['ipid']]['pat_birth'] = $pdata['birthd'];
            
            $masterdata['data'][$pdata['ipid']]['pat_dis_date'] = !empty($last_period[$pdata['ipid']]['end']) && $last_period[$pdata['ipid']]['end'] !="0000-00-00" ?  date('d.m.Y', strtotime($last_period[$pdata['ipid']]['end'])) : "--";
            
            
        }
        
        return $masterdata;
    }
}