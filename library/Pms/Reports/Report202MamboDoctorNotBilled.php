<?php
/*
 * @auth Ancuta 04.04.2019
 * ISPC-2354
 */
class Pms_Reports_Report202MamboDoctorNotBilled extends Pms_Reports_Common
{

    public function __construct()
    {
        parent::__construct();
    }

    public function MamboDoctorNotBilled($params)
    {
        
        // report_id = 202
        // report_name = "Mambo - Arzt nicht abgerechnet" - ISPC-2354
        // Mambo - Arzt nicht abgerechnet
        // filters for all patients active in report period which have the Icon "Arzt nicht abgerechnet" (this is available only in NR_Leverkusen MAMBO"
        
        // Pat_Namen = surname
        // Pat_Vorname = firstname
        // Pat_Geb_Datum = DoB
        // Doc_Name = Surname family doctor
        // Doc_Vorname = firstname family doctor
        // Pat_EinschreibeDatum = admission date patient
        // Pat_gezahlt_anArzt = prefill this with "FALSCH"
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
        
        $patientDetails = array();
        $patientDetails = array_column($patient_days, 'details');
        
        $doctorids = array();
        $doctorids = array_column($patientDetails, 'familydoc_id');
        
        /* ----------------------- Get family doctor details-------------------------------- */
        $familydoctors = array();
        
        if (! empty($doctorids)) {
            $famdoc = new FamilyDoctor();
            $familidoc = $famdoc->getFamilyDoctors(false, false, false, $doctorids);
            
            foreach ($familidoc as $fd_key => $fd_value) {
                $familydoctors[$fd_value['id']] = $fd_value;
            }
        }
        
        /* ----------------------- Get icons -------------------------------- */
        $client_icons = new IconsClient();
        $client_icons_details = $client_icons->get_client_icons($clientid);
        
        $needed_icon_arr = array_filter($client_icons_details, function ($i)
        {
            return $i['name'] == 'Arzt nicht abgerechnet';
        });
        $needed_icon_ids = array_column($needed_icon_arr, 'id');
        
        $patient_icons = new IconsPatient();
        $patient_icons_details = $patient_icons->get_patient_icons($ipids);
        
        $patientWithIcons = array_filter($patient_icons_details, function ($pi) use($needed_icon_ids)
        {
            return in_array($pi['icon_id'], $needed_icon_ids);
        });
        
        $valid_patients = array_column($patientWithIcons, 'ipid');
        
        $patientDetailsFinal = array_filter($patientDetails, function ($pi) use($valid_patients)
        {
            return in_array($pi['ipid'], $valid_patients);
        });
        
        
        
        /* ----------------------- Display data -------------------------------- */
        foreach ($patientDetailsFinal as $k => $pdata) {
            $last_period[$pdata['ipid']] = end($patient_days[$pdata['ipid']]['patient_active']);
            
            $masterdata['data'][$pdata['ipid']]['pat_name'] = $pdata['last_name'];
            $masterdata['data'][$pdata['ipid']]['pat_first_name'] = $pdata['first_name'];
            $masterdata['data'][$pdata['ipid']]['pat_birth'] = $pdata['birthd'];
            $masterdata['data'][$pdata['ipid']]['famdoc_last_name'] = $familydoctors[$pdata['familydoc_id']]['last_name'];
            $masterdata['data'][$pdata['ipid']]['famdoc_first_name'] = $familydoctors[$pdata['familydoc_id']]['first_name'];
            $masterdata['data'][$pdata['ipid']]['famdoc_lanr'] = $familydoctors[$pdata['familydoc_id']]['doctornumber'];
            $masterdata['data'][$pdata['ipid']]['pat_adm_date'] = date('d.m.Y', strtotime($last_period[$pdata['ipid']]['start']));
            $masterdata['data'][$pdata['ipid']]['pat_c_icon_pgaa'] = "FALSCH";
        }
        
        return $masterdata;
    }
}