<?php
/*
 * @auth Ancuta 04.04.2019
 * ISPC-2354
 */

class Pms_Reports_Report204MamboPatients2doctors extends Pms_Reports_Common
{

    public function __construct()
    {
        parent::__construct();
    }

    public function MamboPatients2doctors($params)
    {
        // report_id = 204
        // report_name = "Mambo - Arzt nicht abgerechnet" - ISPC-2354
        // "Mambo - Anzahl Patienten pro Arzt"
        // this reports lists all doctors with the ammount of active patients they have.
        // Pat_EinschreibeArzt = every Facharzt or family doctor with a patient assigned
        // Summe_Patienten = number assigned
        
        
//         $clientid = static::$logininfo->clientid;
//         $userid = static::$logininfo->userid;
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
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
        
        /* ----------------------- Get family doctor details-------------------------------- */
        $doctorids = array();
        $doctorids = array_column($patientDetails, 'familydoc_id'); // only for valid patients
        
        $familydoctors = array();
        if (! empty($doctorids)) {
        
            $famdoc = new FamilyDoctor();
            $familidoc = $famdoc->getFamilyDoctors(false, false, false, $doctorids);
        
            foreach ($familidoc as $fd_key => $fd_value) {
                $familydoctors[$fd_value['id']] = $fd_value;
            }
        }
        
        foreach($patientDetails as $k=>$pat_details){
            if(!empty($pat_details['familydoc_id']) && $pat_details['familydoc_id']!=0)
            {
                if(strlen(trim($familydoctors[$pat_details['familydoc_id']]['last_name'])) > 0 || strlen(trim($familydoctors[$pat_details['familydoc_id']]['first_name'])) > 0 ) 
                {
                    $docs2patients['H'][trim($familydoctors[$pat_details['familydoc_id']]['last_name']).', '.trim($familydoctors[$pat_details['familydoc_id']]['first_name'])] [] = $pat_details['ipid']; 
                }
            }
        }
        
        //get patient specialists
        $specialists = new PatientSpecialists();
        $patients_specialists = $specialists->get_patient_specialists($ipids, true);
        
        foreach($patients_specialists as $k_specialist => $v_specialist)
        {
            if(count($v_specialist['master']) != '0')
            {
                $docs2patients['F'][trim($v_specialist['master']['last_name']).','.trim($v_specialist['master']['first_name'])][] = $v_specialist['ipid'];
            }
        }
        
        

        /* ----------------------- Display data -------------------------------- */
        $k=0;
        foreach ($docs2patients as $doc_type => $doc_vals) {
        
            foreach($doc_vals as $doc_name=>$patients ){
                
                
                $masterdata['data'][$k]['fam_doctorsname'] = $doc_name.' ('.$doc_type.')';
                $masterdata['data'][$k]['amount_of_patients_2_doctors'] = count($patients);
                $k++;
            }
        
        
        }
        
        return $masterdata;
        
        
        
        
        
    }
}