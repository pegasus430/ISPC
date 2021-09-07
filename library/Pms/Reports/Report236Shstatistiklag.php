<?php

/**
 * 
 * @author Ancuta   
 * 17.05.2021
 *
 */
class Pms_Reports_Report236Shstatistiklag extends Pms_Reports_Common
{

    public function __construct()
    {
        parent::__construct();
    }

    public function Shstatistiklag($params)
    {
        // report_id = 236
        // report_name = SH Statistik LAG
        // Lore 22.01.2020
        // ISPC-2457
        /*
         * REPORT LINE: 1--> | Anzahl SAPV-Patienten | please count patients discharged in report period - if a person is discharged twice he is counted just one time
         * REPORT LINE: 2--> | Anzahl SAPV-Fälle | count Fall which were discharged in report period (Fall = admission -> discharge) where the first Verordnung in Fall was type "Erstverordnung"
         * REPORT LINE: 3--> | Anzahl Beratungen | Ammount all patients which are discharged in the selected period and had a Verordnung with Typ "Beratung", means that they had at least one day with SAPV type Beratung
         * REPORT LINE: 4--> | SAPV- Fälle im Hospiz | Ammount of all patients which are discharged in the selected period and had at least one active day in hospice location
         * REPORT LINE: 5--> | SAPV-Verweildauer (Fälle gesamt) > 28 Tage | ammount of "FÄLLE" (admission - discharge) which have more than than 28 active SAPV days and the patient is discharged in the selected
         * period. regard the complete Fall which is discharged in report period. the fall period can be outside the report period. example: Report period 2020: Fall 1: 01.12.2019 - 10.01.2020 ,
         * Fall 2: 01.02.2020 - 15.03.2020 = 2
         * REPORT LINE: 6--> | SAPV-Verweildauer (Fälle gesamt) < = 28 Tage | same as 5. But fall length = 8 - 28 SAPV days
         * REPORT LINE: 7--> | SAPV-Verweildauer (Fälle gesamt) > 4 Tage and < = 7 Tage | same as 5. But fall length = 4 - 7 SAPV days
         * REPORT LINE: 8--> | SAPV-Verweildauer (Fälle gesamt) < = 3 Tage | same as 5. But fall length = shorter or equal than 3 active SAPV days
         * REPORT LINE: 9--> | SAPV-Verweildauer (Durchschnitt) | average days of a SAPV Fall. of all Fall which were discharged is in the selected period
         * REPORT LINE: 10--> | SAPV-Verweildauer (Median) | same as 9 but median instead of average
         * REPORT LINE: 11--> | Abgerechnete SAPV-Tage | all days which were SAPV days (active, not in hospital, had Verordnung TV or VV) AND which were billed (had a visit or were in the range of
         * the first 7 days (flatrate)) from FALL which were discharged in the report period. (this can mean, that some of the visits are outside the report period. the FALL is the selector. )
         * REPORT LINE: 12--> | Gesamtzahl tatsächlich geleisteter Hausbesuche | ammount of all visits from a fall which was discharged in report period (this can mean, that some of the visits are outside the
         * report period. the FALL is the selector. )
         * REPORT LINE: 13--> | Durchschnitt tatsächlich geleisteter Hausbesuche | Average of VISITS done for patient discharged in report period. no matter if in Hospital, patient discharged,
         * 1000 visits per day, so the pure Ammount of contact forms which count as visit. Sum of visits / ammount of discharged patients
         * REPORT LINE: 14--> | Anzahl der abgerechneten telefonischen Beratungen | Select all patients discharged in report period and count the "TELEFONpauschale" (PHONE) which can be / were billed
         * (possible twice a day where no visit was done. )
         * REPORT LINE: 15--> | - | -
         * REPORT LINE: 16--> | Anzahl der Folge-VO | ammount of all Verordnungen with status „Folgeverordnungen“ of FALLs which were discharged in report period. means select patients which are
         * discharged in report period and count the FOLGEverordnung inside the fall which was discharged. example "A" below
         * REPORT LINE: 17--> | Anzahl der abgelehnten Fälle | ammount of Verordnungen with type "abgelehnt" of patients FALLs which are discharged in the selected period
         * REPORT LINE: 18--> | Beendigung SAPV durch Rückführung AAPV | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharge type„Rückführung in die AAPV“ - shortcut "AAPV"
         * REPORT LINE: 19--> | Beendigung SAPV durch Einweisung Krankenhaus | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharge type „Verlegung / Krankenhauseinweisung“ -
         * shortcut "VRL"
         * REPORT LINE: 20--> | Beendigung SAPV durch Versterben | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharge type „Tod“
         * REPORT LINE: 21--> | Sterbeort: zu Hause | all died patients in report period with "Sterbeort = zu Hause" in discharge form
         * REPORT LINE: 22--> | Sterbeort: Pflegeheim | all died patients in report period with "Sterbeort =Pflegeheim" in discharge form
         * REPORT LINE: 23--> | Sterbeort: Krankenhaus | all died patients in report period with "Sterbeort =Krankenhaus" in discharge form
         * REPORT LINE: 24--> | Sterbeort: Palliativstation | all died patients in report period with "Sterbeort =Palliativstation" in discharge form
         * REPORT LINE: 25--> | Sterbeort: Hospiz | all died patients in report period with "Sterbeort =Hospiz" in discharge form
         * REPORT LINE: 26--> | Sterbeort: Unbekannt | all died patients in report period with "Sterbeort =unbekannt" in discharge form
         */
        $quarterarr = $params['quarterarr'];
        $yeararr = $params['yeararr'];
        $montharr = $params['montharr'];
        $includearr = $params['includearr'];
        $onlyactive = $params['onlyactive'];

        $report_data = array();
        $report_labels = array(

            "1" => "discharged_patients",
            "2" => "fall_closed_in_report_period",
            "3" => "patients_discharged_with_verordnung_typ_Beratung",
            "4" => "patients_discharged_with_beginning_location_type_HOSPIZ",
            "5" => "sapv_longer_28days_of_patients_discharged",
            "6" => "sapv_shorter_28days_of_patients_discharged",
            "7" => "sapv_longer_4days_shorter_7days_of_patients_discharged",
            "8" => "sapv_shorter_3days_of_patients_discharged",
            "9" => "average_sapv_fall_of_all_discharged",
            "10" => "median_of_sapv_fall_of_all_discharged",
            "11" => "at_least_one_sapv_Day",
            "12" => "ammount_all_visit_of_all_discharged",
            "13" => "average_all_visit_of_all_discharged",
            "14" => "billable_days_xt",
            "15" => "verordnungen_status_Erstverordnungen_all_discharged",
            "16" => "all_follow_vv_STARTED_in_period",
            "17" => "all_denied_in_period",
            "18" => "all_falls_typ_discharge_rückführung_all_discharged_patients",
            "19" => "all_falls_typ_discharge_verlegung_krankenhaus_all_discharged_patients",
            "20" => "all_falls_typ_discharge_tod_of_period_discharged",
            "21" => "all_falls_discharge_location_zuhause_in_period_discharged",
            "22" => "all_falls_discharge_location_typ_Pflegeheim_in_period_discharged",
            "23" => "all_falls_discharge_location_typ_Krankenhaus_in_period_discharged",
            "24" => "all_falls_discharge_location_typ_Palliativstation_in_period_discharged",
            "25" => "all_falls_discharge_location_typ_Hospiz_in_period_discharged",
            "26" => "all_falls_discharge_location_typ_Unbekannt_in_period_discharged"
        );

 
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        // ISPC-2478 Ancuta 29.10.2020
        $modules = new Modules();
        $fisrt_Sapv_trigger_flatrate = $modules->checkModulePrivileges("246", $clientid);
        // --

        $patientmaster = new PatientMaster();
        // $active_cond = $this->getTimePeriod($quarterarr, $yeararr, $montharr);
        $active_cond = self::getTimePeriod($quarterarr, $yeararr, $montharr);
        $period = Pms_CommonData::getPeriodDates($quarterarr, $yeararr, $montharr);

        $x = 0;
        foreach ($period['start'] as $s => $start_date) {
            $report_dates[$x]['start'] = $start_date;
            $x ++;
        }

        $v = 0;
        foreach ($period['end'] as $e => $end_date) {
            $report_dates[$v]['end'] = $end_date;
            $v ++;
        }

        $ipids = array();
        $report_period_array = array();
        $selected_moths = array();
        $period_days = array();
        $conditions = array();

        /* ----------------- Report period days -------------------- */

        foreach ($period['start'] as $keyd => $startDate) {
            $selected_moths[] = date("m.Y", strtotime($startDate));
            $period_days[] = $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($startDate)), date("Y-m-d", strtotime($period['end'][$keyd])), false);
            $report_period_array[$keyd]['start'] = date("Y-m-d", strtotime($startDate));
            if (strtotime($period['end'][$keyd]) > strtotime(date("Y-m-d"))) {
                $report_period_array[$keyd]['end'] = date("Y-m-d");
            } else {
                $report_period_array[$keyd]['end'] = date("Y-m-d", strtotime($period['end'][$keyd]));
            }
        }

        $final_period_days = array();
        foreach ($period_days as $keyp => $daysp) {
            foreach ($daysp as $day) {

                if (strtotime($day) <= strtotime(date("Y-m-d"))) {
                    $final_period_days[] = $day;
                }
            }
        }
        $final_period_days = array_unique($final_period_days);
        $number_of_month_days = count($final_period_days);

        /* ################################################################################################### */

        
        // GET ALL PATIENTS WITH AT LEAST one ACTIVE DATE IN REPORT PERIOD
        $sql = 'e.epid, p.ipid, e.ipid,';
        $sql .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
        $sql .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
        $sql .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
        $sql .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
        $sql .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
        $sql .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
        $sql .= 'convert(AES_DECRYPT(p.sex,"' . Zend_Registry::get('salt') . '") using latin1) as sex,';

        $conditions['periods'] = $report_period_array;
        $conditions['client'] = $clientid;
        
        if (isset($_REQUEST['ptipid']) && ! empty($_REQUEST['ptipid'])) {
            $conditions['ipids'] = array(
                $_REQUEST['ptipid']
            );
        }
        $patient_days = Pms_CommonData::patients_days($conditions, $sql);
        //e006b63c34087dacc0dc38f0b8fadc8d996df06c
//         dd($patient_days);
        $ipids = array_keys($patient_days); // return ipids

        /* DISCHARGE_LOCATIONS OF CLIENT */
        $discharge_location_arr = array();
        $dl_types = array();
        $client_discharge_location = DischargeLocation::getDischargeLocation($clientid);
        foreach ($client_discharge_location as $ky => $kv) {
            // $discharge_location_arr[$kv['id']] = $kv['location'];
            $discharge_location_arr[$kv['id']] = $kv['type']; 
            $dl_types[$kv['type']][] = $kv['id'];
        }

        /* DISCHARGE_ METHOD OF CLIENT */
        $discharge_method_arr = DischargeMethod::get_client_discharge_method_abbr($clientid);

        
        //GET PATIENT DETAILS
        // GET ALL PERIODS THAT ENDEDN IN 
        $patient_details = array();
        $falls_endded_inReport_period = array();
        $fall_closed_ipids = array();
        foreach ($patient_days as $ipid => $p_details) {

            $patient_details[$p_details['details']['ipid']]['epid'] = $p_details['details']['epid'];
            $patient_details[$p_details['details']['ipid']]['surname'] = $p_details['details']['last_name'];
            $patient_details[$p_details['details']['ipid']]['firstname'] = $p_details['details']['first_name'];
            $patient_details[$p_details['details']['ipid']]['dateofbirth'] = date("d.m.Y", strtotime($p_details['details']['birthd']));
            $patient_details[$p_details['details']['ipid']]['dateofbirth_ymd'] = $p_details['details']['birthd'];
            $patient_details[$p_details['details']['ipid']]['admissiondate'] = date("d.m.Y", strtotime($p_details['details']['admission_date']));

            if ($p_details['details']['isdischarged'] == '1') {
                $patient_details[$p_details['details']['ipid']]['dischargedate'] = end($p_details['discharge']);
                $patient_details[$p_details['details']['ipid']]['dischargedate_death'] = implode("<", $p_details['discharge_dead']);
            } else {
                $patient_details[$p_details['details']['ipid']]['dischargedate'] = "-";
            }

            // info for REPORT LINE: 2--> | Anzahl SAPV-Fälle | Fall which were closed in report period

            if (count($p_details['patient_active']) > 0) {
                foreach ($p_details['patient_active'] as $ky => $kv) {

                    if ($kv['end']!= "0000-00-00" &&in_array($kv['end'], $final_period_days)) {
                        if(!in_array($p_details['details']['ipid'],$fall_closed_ipids)){
                            $fall_closed_ipids[] = $p_details['details']['ipid'];
                        }
                        $falls_endded_inReport_period[$p_details['details']['ipid']][] = $kv;
                    }
                }
            }

            $patient_details[$p_details['details']['ipid']]['zip'] = $p_details['details']['zip'];
            $family_doctors[] = $p_details['details']['familydoc_id'];
            $patient_details[$p_details['details']['ipid']]['familydoc_id'] = $p_details['details']['familydoc_id'];

            $patient_details[$p_details['details']['ipid']]['real_active_days'] = $p_details['real_active_days'];
            $patient_details[$p_details['details']['ipid']]['real_active_days_no'] = $p_details['real_active_days_no'];

            $patient_details[$p_details['details']['ipid']]['hospital_days_cs'] = $p_details['hospital']['real_days_cs'];
            $patient_details[$p_details['details']['ipid']]['hospital_days_cs_no'] = $p_details['hospital']['real_days_cs_no'];
            $patient_details[$p_details['details']['ipid']]['active_days_no_hospital_days'] = $p_details['real_active_days_no'] - $p_details['hospital']['real_days_cs_no'];

            $patient_details[$p_details['details']['ipid']]['hospiz_days_cs'] = $p_details['hospiz']['real_days_cs'];
            $patient_details[$p_details['details']['ipid']]['hospiz_days_cs_no'] = $p_details['hospiz']['real_days_cs_no'];
            $patient_details[$p_details['details']['ipid']]['active_days_no_hospiz_days'] = $p_details['real_active_days_no'] - $p_details['hospiz']['real_days_cs_no'];
        }
        
        //FROM ALL ACTIVE PATIENTS IN REPORT PERIOD - GET  ALL PATIENTS WITH DISCHARGED DATE IN REPORT PERIOD
        $pat_dis = Doctrine_Query::create()->select('*,AES_DECRYPT(discharge_comment,"' . Zend_Registry::get('salt') . '") as discharge_comment')
            ->from('PatientDischarge')
            ->whereIn('ipid', $ipids)
            ->andWhere(" (" . str_replace('%date%', 'discharge_date', $active_cond['date_sql']) . ")");
        $ipids_disch_all = $pat_dis->fetchArray();
        
        
        $master_data = array();
        if (empty($ipids_disch_all)) {
            return $master_data;
        }

        $discharged_ipids = array();
        $ruckfung_ipids = array();
        $einweisung_ipids = array();
        $tod_ipids = array();
        $zuhause_ipids = array();
        $pflegeheim_ipids = array();
        $krankenhaus_ipids = array();
        $paliative_ipids = array();
        $hospiz_ipids = array();
        $unknown_ipids = array();
        $fall_closed_ipids = array();
        $beratung_ipids = array();
        $fall_hospiz = array();

        $patients_discharge_date = array();

        foreach ($ipids_disch_all as $key => $val) {

            $patients_discharge_date[$val['ipid']] = $val['discharge_date'];

            if (! in_array($val['ipid'], $discharged_ipids)) {
                $discharged_ipids[] = $val['ipid'];
            }

            // info for REPORT LINE: 18--> | Beendigung SAPV durch Rückführung AAPV | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharg Typ „Rückführung in die AAPV“
            if (! empty($val['discharge_method']) && $discharge_method_arr[$val['discharge_method']] == 'AAPV') {
                // shortcut to "AAPV"
                $ruckfung_ipids[] = $val['ipid'];
            }
            // info for REPORT LINE: 19--> | Beendigung SAPV durch Einweisung Krankenhaus | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharged Typ „Verlegung / Krankenhauseinweisung“
            //                if (! empty($val['discharge_method']) && $discharge_method_arr[$val['discharge_method']] == 'VRL') {
            if (! empty($val['discharge_method']) && $discharge_method_arr[$val['discharge_method']] == 'KRH') {
                // shortcut to KRH
                $einweisung_ipids[] = $val['ipid'];
            }
            
            if (! empty($val['discharge_method']) && $discharge_method_arr[$val['discharge_method']] == 'TOD') {
                $tod_ipids[] = $val['ipid']; // info for REPORT LINE: 20--> | Beendigung SAPV durch Versterben | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharged Typ „Tod“

                // info for REPORT LINE: 21--> | Sterbeort: zu Hause | Count all „Fälle“ of patients discharged DEAD in report period which have discharge location Zuhause
                if (in_array($val['discharge_location'], $dl_types['1'])) { // zu Hause //Lore 21.01.2021
                    $zuhause_ipids[] = $val['ipid'];
                }
                // info for REPORT LINE: 22--> | Sterbeort: Pflegeheim | number of „Fälle“ of patients discharged DEAD in report period which have the discharge location typ „Pflegeheim“
                if (in_array($val['discharge_location'], $dl_types['4'])) { // Pflegeheim //Lore 21.01.2021
                    $pflegeheim_ipids[] = $val['ipid'];
                }
                // info for REPORT LINE: 23--> | Sterbeort: Krankenhaus | = number of „Fälle“ of patients discharged DEAD in report period which have the discharge location typ „Krankenhaus“
                if (in_array($val['discharge_location'], $dl_types['2'])) { // Krankenhaus //Lore 21.01.2021
                    $krankenhaus_ipids[] = $val['ipid'];
                }
                // info for REPORT LINE: 24--> | Sterbeort: Palliativstation | = number of „Fälle“ of patients discharged DEAD in report period which have the discharge location typ „Palliativstation“
                if (in_array($val['discharge_location'], $dl_types['5'])) { // Paliative station //Lore 21.01.2021
                    $paliative_ipids[] = $val['ipid'];
                }
                // info for REPORT LINE: 25--> | Sterbeort: Hospiz | = number of „Fälle“ of patients discharged DEAD in report period which have the discharge location typ „Hospiz“
                if (in_array($val['discharge_location'], $dl_types['3'])) { // Hospiz //Lore 21.01.2021
                    $hospiz_ipids[] = $val['ipid'];
                }
                // info for REPORT LINE: 26--> | Sterbeort: Unbekannt | = number of „Fälle“ of patients discharged in report period which have the discharge location typ „unknown“
                if (! empty($val['discharge_location']) && (! in_array($val['discharge_location'], $dl_types['3']) && ! in_array($val['discharge_location'], $dl_types['5']) && ! in_array($val['discharge_location'], $dl_types['2']) && ! in_array($val['discharge_location'], $dl_types['4']) && ! in_array($val['discharge_location'], $dl_types['1']))) {
                    $unknown_ipids[] = $val['ipid'];
                }
            }

            
            // info for REPORT LINE: 4--> | SAPV- Fälle im Hospiz | Ammount of all patients which are discharged in the selected period had a fall beginning in location type HOSPIZ
            // Ammount of all patients which are discharged in the selected period and had at least one active day in hospice location 
            if(!empty($patient_details[$val['ipid']]['hospiz_days_cs'])){
                $fall_hospiz[] = $val['ipid'];
            }
            
        }

        
        
        $report_data['patients_discharged_with_beginning_location_type_HOSPIZ'] = count(array_unique($fall_hospiz));// info for REPORT LINE: 4--> | SAPV- Fälle im Hospiz | Ammount of all patients which are discharged in the selected period and had at least one active day in hospice location

        $report_data['all_falls_typ_discharge_rückführung_all_discharged_patients'] = count($ruckfung_ipids);// info for REPORT LINE: 18--> | Beendigung SAPV durch Rückführung AAPV | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharg Typ „Rückführung in die AAPV“
        $report_data['all_falls_typ_discharge_verlegung_krankenhaus_all_discharged_patients'] = count($einweisung_ipids);// info for REPORT LINE: 19--> | Beendigung SAPV durch Einweisung Krankenhaus | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharged Typ „Verlegung / Krankenhauseinweisung“
        $report_data['all_falls_typ_discharge_tod_of_period_discharged'] = count($tod_ipids);// info for REPORT LINE: 20--> | Beendigung SAPV durch Versterben | Ammount of all „Fälle“ of patients who were discharged in report period which have the discharged Typ „Tod“
        $report_data['all_falls_discharge_location_zuhause_in_period_discharged'] = count($zuhause_ipids);// info for REPORT LINE: 21--> | Sterbeort: zu Hause | Count all „Fälle“ of patients discharged DEAD in report period which have discharge location Zuhause
        $report_data['all_falls_discharge_location_typ_Pflegeheim_in_period_discharged'] = count($pflegeheim_ipids);// info for REPORT LINE: 22--> | Sterbeort: Pflegeheim | number of „Fälle“ of patients discharged DEAD in report period which have the discharge location typ „Pflegeheim“
        $report_data['all_falls_discharge_location_typ_Krankenhaus_in_period_discharged'] = count($krankenhaus_ipids);// info for REPORT LINE: 23--> | Sterbeort: Krankenhaus | = number of „Fälle“ of patients discharged DEAD in report period which have the discharge location typ „Krankenhaus“
        $report_data['all_falls_discharge_location_typ_Palliativstation_in_period_discharged'] = count($paliative_ipids);// info for REPORT LINE: 24--> | Sterbeort: Palliativstation | = number of „Fälle“ of patients discharged DEAD in report period which have the discharge location typ „Palliativstation“
        $report_data['all_falls_discharge_location_typ_Hospiz_in_period_discharged'] = count($hospiz_ipids);// info for REPORT LINE: 25--> | Sterbeort: Hospiz | = number of „Fälle“ of patients discharged DEAD in report period which have the discharge location typ „Hospiz“
        $report_data['all_falls_discharge_location_typ_Unbekannt_in_period_discharged'] = count($unknown_ipids);// info for REPORT LINE: 26--> | Sterbeort: Unbekannt | = number of „Fälle“ of patients discharged in report period which have the discharge location typ „unknown“
        
        
        $sapv_verd = SapvVerordnung::getSapvVerordnungData($discharged_ipids);
        $discharged_ipids_at_least_one_day_sapv = array();
        $report_data['fall_closed_in_report_period'] = 0;

        
        foreach($falls_endded_inReport_period as  $fc_ipid=>$fc){
            if(!in_array($fc_ipid,$discharged_ipids)){
                unset($falls_endded_inReport_period[$fc_ipid]);
            }
        }
        // get all sapvs in admission from period

        
        // For each patient - check the status of the first sapv from the fall that indedn inreport period, in report period
        foreach ($sapv_verd as $ky => $sdata) {
            $sapv2ipid[$sdata['ipid']][] = $sdata;
            $sapv_verd[$ky]['days'] = $patientmaster->getDaysInBetween(date('Y-m-d',strtotime($sdata['verordnungam'])), date('Y-m-d',strtotime($sdata['verordnungbis'])) );
            $sapv_verd[$ky]['days_in_period'] = array_intersect($final_period_days,$sapv_verd[$ky]['days']);
        }

        foreach ($sapv2ipid as $s_ipid => $pat_sapvs) {
            foreach ($falls_endded_inReport_period[$s_ipid] as $ks => $ipe) {

                usort($pat_sapvs, array( new Pms_Sorter('verordnungam'), "_date_compare" ));
                foreach ($pat_sapvs as $k => $spdataa) {
                    if (Pms_CommonData::isintersected(date('Y-m-d', strtotime($ipe['start'])), date('Y-m-d', strtotime($ipe['end'])), date('Y-m-d', strtotime($spdataa['verordnungam'])), date('Y-m-d', strtotime($spdataa['verordnungbis'])))) {
                        $falls_endded_inReport_period[$s_ipid][$ks]['sapvs'][] = $spdataa;
                    }
                }
            }
        }

        foreach ($falls_endded_inReport_period as $fe_ipid => $ended_periods) {
            foreach ($ended_periods as $per_k => $per_data) {
                if (! empty($per_data['sapvs']) && $per_data['sapvs'][0]['sapv_order'] == '1') {
                    // REPORT LINE: 2--> | Anzahl SAPV-Patienten | count Fall which were discharged in report period (Fall = admission -> discharge) where the first Verordnung in Fall was type "Erstverordnung"
                    $report_data['fall_closed_in_report_period'] ++;
                }
            }
        }

        foreach ($sapv_verd as $ky => $kv) {
 
            if(!empty($kv['days_in_period'])){
                $discharged_ipids_at_least_one_day_sapv[$kv['ipid']] = $kv['ipid'];
            }

            $verordnet_arr = explode(",", $kv['verordnet']);
            if (in_array("1", $verordnet_arr)) {
                $beratung_ipids[$kv['ipid']] = $kv['ipid'];
            }
        }

        // REPORT LINE: 1--> | Anzahl SAPV-Patienten | count the "Patients" of the client = please count DISCHARGED  patients (if a person is discharged twice he is counted just one time )
        $report_data['discharged_patients'] = count($discharged_ipids_at_least_one_day_sapv);

        
        // REPORT LINE: 3--> | Anzahl Beratungen | Ammount all patients which are discharged in the selected period and had a Verordnung with Typ "Beratung"
        $report_data['patients_discharged_with_verordnung_typ_Beratung'] = count($beratung_ipids);

        $report_data['sapv_longer_28days_of_patients_discharged'] = array();// REPORT LINE: 5--> | SAPV-Verweildauer (Fälle gesamt) > 28 Tage | ammount of "FÄLLE" (admission - discharge) which have more than than 28 active SAPV days and the patient is discharged in the selected period
        $report_data['sapv_shorter_28days_of_patients_discharged'] = array();// REPORT LINE: 6--> | SAPV-Verweildauer (Fälle gesamt) > 8 Tage and < = 28 Tage | ammount of "FÄLLE" (admission - discharge) which had 8 - 28 SAPV days and the patient is discharged in the selected period
        $report_data['sapv_longer_4days_shorter_7days_of_patients_discharged'] = array();// REPORT LINE: 7--> | SAPV-Verweildauer (Fälle gesamt) > 4 Tage and < = 7 Tage | ammount of "FÄLLE" (admission - discharge) which had 4 - 7 SAPV days days and the patient is discharged in the selected period
        $report_data['sapv_shorter_3days_of_patients_discharged'] = array();//REPORT LINE: 8--> | SAPV-Verweildauer (Fälle gesamt) < = 3 Tage | ammount of "FÄLLE" (admission - discharge) which were shorter or equal than 3 active SAPV days and the patient is discharged in the selected period

        foreach($falls_endded_inReport_period as $pipid => $active_falls){
            
            foreach($active_falls as $fal_k=>$fall_sapv_data){
                
                $active_falls_days[$pipid][$fal_k] = $patientmaster->getDaysInBetween($fall_sapv_data['start'], $fall_sapv_data['end']);
                
                if(!empty($fall_sapv_data['sapvs'])){
                    
                    foreach($fall_sapv_data['sapvs'] as $sk=>$sdays){
                        
                        if (Pms_CommonData::isintersected( $fall_sapv_data['start'], $fall_sapv_data['end'], date('Y-m-d', strtotime($sdays['verordnungam'])), date('Y-m-d', strtotime($sdays['verordnungbis'])))) {
                            
                            // REPORT LINE: 16--> | Anzahl der Folge-VO | ammount of all Verordnungen with status „Folgeverordnungen“ of patients which are dischraged in the selected period
                            if($sdays['sapv_order'] == 2){
                                $report_data['all_follow_vv_STARTED_in_period'][] = $sdays['id'];
                            }
                            
                            // REPORT LINE: 17--> | Anzahl der abgelehnten Fälle | ammount of Verordnungen with type "abgelehnt" of patients FALLs which are discharged in the selected period
                            if($sdays['status'] == 1){
                                $report_data['all_denied_in_period'][] = $sdays['id'];
                            }
                        }
                        
                        if(!is_array($active_sapv_falls_days[$pipid][$fal_k])){
                            $active_sapv_falls_days[$pipid][$fal_k] = array();
                        }
                        $sapv_temp = $patientmaster->getDaysInBetween(date('Y-m-d',strtotime($sdays['verordnungam'])),date('Y-m-d',strtotime($sdays['verordnungbis'])));
                        $sapvcount2sapv_fals[] = count($sapv_temp);
                        $sapv2sapv_Falls[] = $sdays['ipid'];
                        
                        $falls_endded_inReport_period[$pipid][$fal_k]['sapvs'][$sk]['all_sapv_days_per_sapv_fall'] =  $sapv_temp;
                        $falls_endded_inReport_period[$pipid][$fal_k]['sapvs'][$sk]['active_sapv_days_per_sapv_fall'] =  array_values(array_intersect($active_falls_days[$pipid][$fal_k], $sapv_temp));
                        $sapvcount2active_sapv_fals[] = count(array_intersect($active_falls_days[$pipid][$fal_k], $sapv_temp));
                        
                        $active_sapv_falls_days[$pipid][$fal_k] = array_merge($active_sapv_falls_days[$pipid][$fal_k],$sapv_temp);
                        
                        
                        
                        $verordnet_arr = explode(",", $sdays['verordnet']);
                        if(in_array('3',$verordnet_arr) || in_array('4',$verordnet_arr)){
                            if(!is_array($falls_endded_inReport_period[$pipid] ['active_tv_vv_sapv_days_per_ipid'])){
                                $falls_endded_inReport_period[$pipid] ['active_tv_vv_sapv_days_per_ipid'] = array();
                            }
                            
                            $tv_vv_sapv_temp = $patientmaster->getDaysInBetween(date('Y-m-d',strtotime($sdays['verordnungam'])),date('Y-m-d',strtotime($sdays['verordnungbis'])));
                            $falls_endded_inReport_period[$pipid][$fal_k]['sapvs'][$sk]['active_tv_vv_sapv_days_per_sapv_fall'] =  array_values(array_intersect($active_falls_days[$pipid][$fal_k], $tv_vv_sapv_temp));
                            $falls_endded_inReport_period[$pipid] ['active_tv_vv_sapv_days_per_ipid'] = array_merge($falls_endded_inReport_period[$pipid] ['active_tv_vv_sapv_days_per_ipid'], array_values(array_intersect($active_falls_days[$pipid][$fal_k], $tv_vv_sapv_temp)));
                        }
          
                    }
                    
                    
                    $active_with_sapv_per_fall[$pipid][$fal_k] =  array_values(array_intersect($active_falls_days[$pipid][$fal_k], $active_sapv_falls_days[$pipid][$fal_k]));
                    $falls_endded_inReport_period[$pipid][$fal_k]['active_with_sapv_per_fall'] =  array_values(array_intersect($active_falls_days[$pipid][$fal_k], $active_sapv_falls_days[$pipid][$fal_k]));
                    
                }
                
                if(!is_array($falls_endded_inReport_period[$pipid]['all_fall_days'])){
                    $falls_endded_inReport_period[$pipid]['all_fall_days'] = array();
                }
                $falls_endded_inReport_period[$pipid]['all_fall_days'] =  array_merge($falls_endded_inReport_period[$pipid]['all_fall_days'],$active_falls_days[$pipid][$fal_k] );
            }
        }
        
        // REPORT LINE: 9--> | SAPV-Verweildauer (Durchschnitt) | average SAPV Fall of all discharged  patient is in the selected period
        asort($sapvcount2sapv_fals);
        $sapvcount2sapv_fals = array_values($sapvcount2sapv_fals);
        $report_data['average_sapv_fall_of_all_discharged-SAPV_OV'] = round(array_sum($sapvcount2sapv_fals) / count($sapv2sapv_Falls), 2);
        // REPORT LINE: 10--> | SAPV-Verweildauer (Median) | please create the median of all SAPV Falls discharged  patient is in the selected period
        $median_sapvcount2sapv_fals = Pms_CommonData::calculate_median($sapvcount2sapv_fals);
        $report_data['median_of_sapv_fall_of_all_discharged-SAPV_OV'] = $median_sapvcount2sapv_fals;
        
        
        // REPORT LINE: 9--> | SAPV-Verweildauer (Durchschnitt) | average SAPV Fall of all discharged  patient is in the selected period
        asort($sapvcount2active_sapv_fals);
        $sapvcount2active_sapv_fals = array_values($sapvcount2active_sapv_fals);
        //$report_data['average_sapv_fall_of_all_discharged-SAPV_ACTIVE'] = round(array_sum($sapvcount2active_sapv_fals) / count($sapv2sapv_Falls), 2);
        $report_data['average_sapv_fall_of_all_discharged'] = round(array_sum($sapvcount2active_sapv_fals) / count($sapv2sapv_Falls), 2);
        
        // REPORT LINE: 10--> | SAPV-Verweildauer (Median) | please create the median of all SAPV Falls discharged  patient is in the selected period
        $median_sapvcount2sapv_fals = Pms_CommonData::calculate_median($sapvcount2active_sapv_fals);
        //$report_data['median_of_sapv_fall_of_all_discharged-SAPV_ACTIVE'] = $median_sapvcount2sapv_fals;
        $report_data['median_of_sapv_fall_of_all_discharged'] = $median_sapvcount2sapv_fals;
 
 
        foreach($falls_endded_inReport_period as $ff_ipid=>$fall_arr)
        {
            foreach($fall_arr as $fa_key => $fa_data )
            {
                if(!empty($fa_data['active_with_sapv_per_fall'])){
                    $sapvcount2admissions[] = count($fa_data['active_with_sapv_per_fall']);
                    $sapv2admissions_Falls[] = $fa_data['ipid'];
                    
                    $sapv_days_count = count($fa_data['active_with_sapv_per_fall']);
                    
                    if($sapv_days_count > 28){
                        $report_data['sapv_longer_28days_of_patients_discharged'][] = $ff_ipid;// REPORT LINE: 5--> | SAPV-Verweildauer (Fälle gesamt) > 28 Tage | ammount of "FÄLLE" (admission - discharge) which have more than than 28 active SAPV days and the patient is discharged in the selected period
                    }
                    else if($sapv_days_count > 7 && $sapv_days_count <= 28){
                        $report_data['sapv_shorter_28days_of_patients_discharged'][] = $ff_ipid;// REPORT LINE: 6--> | SAPV-Verweildauer (Fälle gesamt) > 8 Tage and < = 28 Tage | ammount of "FÄLLE" (admission - discharge) which had 8 - 28 SAPV days and the patient is discharged in the selected period
                    }
                    else if($sapv_days_count > 4 && $sapv_days_count <= 7){
                        $report_data['sapv_longer_4days_shorter_7days_of_patients_discharged'][] = $ff_ipid;// REPORT LINE: 7--> | SAPV-Verweildauer (Fälle gesamt) > 4 Tage and < = 7 Tage | ammount of "FÄLLE" (admission - discharge) which had 4 - 7 SAPV days days and the patient is discharged in the selected period
                    }
                    else if(  $sapv_days_count <= 3){
                        $report_data['sapv_shorter_3days_of_patients_discharged'][] = $ff_ipid;//REPORT LINE: 8--> | SAPV-Verweildauer (Fälle gesamt) < = 3 Tage | ammount of "FÄLLE" (admission - discharge) which were shorter or equal than 3 active SAPV days and the patient is discharged in the selected period
                    }
                }
            }
        }
 
        // REPORT LINE: 9--> | SAPV-Verweildauer (Durchschnitt) | average SAPV Fall of all discharged  patient is in the selected period
        asort($sapvcount2admissions);
        $sapvcount2admissions = array_values($sapvcount2admissions);
        $report_data['average_sapv_fall_of_all_discharged-ADM'] = round(array_sum($sapvcount2admissions) / count($sapv2admissions_Falls), 2);
        
        // REPORT LINE: 10--> | SAPV-Verweildauer (Median) | please create the median of all SAPV Falls discharged  patient is in the selected period
        $median_sapvcount2admissions = Pms_CommonData::calculate_median($sapvcount2admissions);
        $report_data['median_of_sapv_fall_of_all_discharged-ADM'] = $median_sapvcount2admissions;
        
        
        // REPORT LINE: 14--> | Anzahl der abgerechneten telefonischen Beratungen | Ammount of all billed and PAID product “Telefonpauschale“ of all dischareged patients (even outside the report period)
        // get sh invoices -  where  Phone items were billed
        $sh_invoices_q = Doctrine_Query::create()->select('*')
        ->from('ShInvoices')
        ->whereIn('ipid', $discharged_ipids)
        ->andWhere('isdelete=0');
        $sh_invoices = $sh_invoices_q->fetchArray();
        
        foreach($sh_invoices as $shk => $sh_inv){
            $invoices_ids[] = $sh_inv['id'];
        }
        
        if(!empty($invoices_ids)){
            $sh_invoices_itms_q = Doctrine_Query::create()->select('*')
            ->from('ShInvoiceItems')
            ->whereIn('invoice', $invoices_ids)
            ->andWhere('isdelete=0');
            $sh_invoices_items = $sh_invoices_itms_q->fetchArray();
            
            $billed_phones_qtys =array();
            foreach($sh_invoices_items as $ksi=>$sh_items){
                if($sh_items['shortcut'] == 'sh_overall_phones'){
                    //                     $billed_phones_qtys = $billed_phones_qtys + $sh_items['qty'];
                    $billed_phones_qtys[] = $sh_items['qty'];
                }
            }
        }
        if (! empty($billed_phones_qtys)) {
            $report_data['billable_days_xt'] = array_sum($billed_phones_qtys); // REPORT LINE: 14--> | Anzahl der abgerechneten telefonischen Beratungen | Ammount of all billed and PAID product “Telefonpauschale“ of all dischareged patients (even outside the report period)
        }
        
        
        // $all_sapv_data = $this->get_patients_all_valid_sapv($discharged_ipids, $final_period_days,$active_cond);
//         $all_sapv_data = self::get_patients_all_valid_sapv($discharged_ipids, $final_period_days, $active_cond);
        $all_sapv_data = self::get_patients_all_valid_sapv($discharged_ipids, false, false);
        //$full_sapv_data = $this->get_patients_all_valid_sapv($ipids,$final_period_days,false);
        $full_sapv_data = self::get_patients_all_valid_sapv($discharged_ipids,$final_period_days,false);
        
        $patient_Erstsapv_days = array();
        if (! empty($all_sapv_data)) {
            $sapv_fall_days = array();
            foreach ($all_sapv_data as $ipid => $sapv_data) {

                if (! empty($sapv_data['first_sapv_in_period']) && isset($sapv_data['sapv_falls'][$sapv_data['first_sapv_in_period']]) && $sapv_data['sapv_falls'][$sapv_data['first_sapv_in_period']]['sapv_order'] == '1') {
                    // first Verordnung in Fall was type "Erstverordnung"
                }

                // ISPC-2478
                foreach ($sapv_data['sapv_falls'] as $sapv_id => $sdata) {
                    if ($sdata['sapv_order'] == "1") {
                        $patient_Erstsapv_days[$ipid][$sapv_id] = $sdata['all_days'];
                    }
                }
                // --
                
                if (! empty($sapv_data['sapv_days_in_period'])) {
                    foreach ($sapv_data['sapv_days_in_period'] as $k => $sday) {

                        if (in_array(date('d.m.Y', strtotime($sday)), $patient_details[$ipid]['real_active_days']) && ! in_array(date('d.m.Y', strtotime($sday)), $patient_details[$ipid]['hospital_days_cs']) && ! in_array(date('d.m.Y', strtotime($sday)), $patient_details[$ipid]['hospiz_days_cs'])) {
                            $valid_Sapv_Days_in_report[$ipid][] = $sday;
                        }
                    }
                }

                if (! empty($sapv_data['sapv_falls'])) {
                    foreach ($sapv_data['sapv_falls'] as $sapv_id => $fall_data) {

                        if (isset($fall_data['days_no']) && $fall_data['days_no'] > 0) {
                            $sapv_fall_days[] = $fall_data['days_no'];
                        }
                    }
                }

//                 if (! empty($sapv_data['sapv_days_in_period'])) {
                if (! empty($sapv_data['sapv_days_overall'])) {
                    $curent_period_days_sapv[$ipid] = $sapv_data['sapv_days_overall'];

                    array_walk($curent_period_days_sapv[$ipid], function (&$value) {
                        $value = date('d.m.Y', strtotime($value));
                    });
                }
            }
        }

        // prepare for LINE 11
        $s = array(
            '%date_start%',
            '%date_end%'
        );
        $r = array(
            'verordnungam',
            'verordnungbis'
        );

        $dropSapv = Doctrine_Query::create()->select('*')
            ->from('SapvVerordnung')
            ->whereIn('ipid', $discharged_ipids)
            ->andWhere('isdelete=0')
            ->andWhere("status != 1")
            ->andWhere('verordnet LIKE "%4%" OR verordnet LIKE "%3%"')
            ->andWhere('verordnungam != "0000-00-00 00:00:00"')
            ->andWhere('verordnungbis != "0000-00-00 00:00:00"')
            ->andWhere(str_replace($s, $r, $active_cond['interval_sql']))
            ->orderBy("verordnungam ASC");
        $sapv_array_TV_VV = $dropSapv->fetchArray();

        $patient_active_sapv = array();
        if (! empty($sapv_array_TV_VV)) {
            $sapv_TV_VV = array();
            $s = 1;
            foreach ($sapv_array_TV_VV as $sapvkey => $sapvvalue) {
                $sapv_TV_VV[$sapvvalue['ipid']]['sapv_start_days'][] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));

                $sapv_TV_VV[$sapvvalue['ipid']]['sapv_intervals'][$s]['start'] = date('Y-m-d', strtotime($sapvvalue['verordnungam']));
                $sapv_TV_VV[$sapvvalue['ipid']]['sapv_intervals'][$s]['end'] = date('Y-m-d', strtotime($sapvvalue['verordnungbis']));

                $patient_active_sapv[$sapvvalue['ipid']][] = $patientmaster->getDaysInBetween($sapv_TV_VV[$sapvvalue['ipid']]['sapv_intervals'][$s]['start'], $sapv_TV_VV[$sapvvalue['ipid']]['sapv_intervals'][$s]['end']);

                $s ++;
            }

            foreach ($patient_active_sapv as $pat_ipid => $overall_sdays) {
                foreach ($overall_sdays as $sinter => $sinterval_days) {
                    foreach ($sinterval_days as $sdays) {
                        $sapv_TV_VV[$pat_ipid]['sapv_days_overall'][] = $sdays;

                        if ($final_period_days && ! empty($final_period_days)) {

                            if (in_array(date('Y-m-d', strtotime($sdays)), $final_period_days)) {
                                $sapv_TV_VV[$pat_ipid]['sapv_days_in_period'][] = $sdays;
                            }
                        }
                    }
                }
            }

            $valid_Sapv_Days_in_report_TV_VV = array();
            foreach ($sapv_TV_VV as $keys => $vals) {

                if (! empty($vals['sapv_days_in_period'])) {
                    foreach ($vals['sapv_days_in_period'] as $k => $sday) {

                        if (in_array(date('d.m.Y', strtotime($sday)), $patient_details[$ipid]['real_active_days']) && ! in_array(date('d.m.Y', strtotime($sday)), $patient_details[$ipid]['hospital_days_cs']) && ! in_array(date('d.m.Y', strtotime($sday)), $patient_details[$ipid]['hospiz_days_cs'])) {
                            $valid_Sapv_Days_in_report_TV_VV[$ipid][] = $sday;
                        }
                    }
                }
            }
        }

        // /Billable
        $shortcuts_arr = array(
            'sh_beko',
            'sh_folgeko',
            'sh_doc_non_hospiz_visits',
            'sh_nur_non_hospiz_visits',
            'sh_other_visits',
            'sh_doc_hospiz_visits',
            'sh_telefonat',
            'sh_flatrate',
            // used only in custom totals
            'sh_nur_visits',
            'sh_nur_hospiz_visits'
        );

        $visits_shortcuts = array(
            'sh_doc_non_hospiz_visits',
            'sh_nur_non_hospiz_visits',
            'sh_other_visits',
            'sh_doc_hospiz_visits',
            // used only in custom totals
            'sh_nur_hospiz_visits',
            'sh_nur_non_hospiz_visits'
        );

        $patient_specific_period = array();
        foreach ($curent_period_days_sapv as $pat_ipid => $sapv_days) {
            $patient_specific_period[$pat_ipid]['ipid'] = $pat_ipid;
            $patient_specific_period[$pat_ipid]['start'] = $sapv_days[0];
            $patient_specific_period[$pat_ipid]['end'] = end($sapv_days);
        }
        // patient HEALTH INSURANCE END

        // REPORT LINE: 12--> | Gesamtzahl tatsächlich geleisteter Hausbesuche | Sum the ammount of all visits independently if it is billed or not of all discharged patients (even outside the report period)
        // get patient TELEFONAT (XT) START
        $patient_phones = array();
        $count_phones = 0;
        $tel_array = PatientCourse::get_shstatistik_patient_shortcuts_course($discharged_ipids, array( 'XT' ), $patient_specific_period);
        foreach ($tel_array as $k_tel => $v_tel) {
            
            $v_tel_date = date('d.m.Y', strtotime($v_tel['done_date']));

            if (in_array($v_tel_date, $curent_period_days_sapv[$v_tel['ipid']])) {
                // REMOVE CONTACTS AFTER Death time
                if ($v_tel_date == date('Y-m-d', strtotime($patients_discharge_date[$v_tel['ipid']])) && strtotime(date('Y-m-d H:i:s', strtotime($v_tel['done_date']))) > strtotime($patients_discharge_date[$v_tel['ipid']])) {
                    // do not add
                } else {
                    $patient_phones[$v_tel['ipid']][$v_tel_date]['sh_telefonat'][] = $v_tel;
                    $count_phones = $count_phones + 1;
                }
            }
            $v_tel_date = '';
        }
        // get patient TELEFONAT (XT) END

        
        
        
        // get contact forms (ALL) START
        $count_visits = 0;
        // TODO-4135 Ancuta 18.05.2021
        // $contact_forms_all = ContactForms::get_sh_period_contact_forms($discharged_ipids, false, false, $curent_period_days_sapv);
        $contact_forms_all = ContactForms::get_sh_period_contact_forms($discharged_ipids, false, false, false);
        $count_all_visits = 0;
        foreach ($contact_forms_all as $kcf => $day_cfs) {
            foreach ($day_cfs as $k_dcf => $v_dcf) {
                $count_all_visits = $count_all_visits + 1;
            }
        }
        $report_data['ammount_all_visit_of_all_discharged'] = $count_all_visits;// REPORT LINE: 12--> | Gesamtzahl tatsächlich geleisteter Hausbesuche | Sum the ammount of all visits independently if it is billed or not of all discharged patients (even outside the report period)
        $report_data['average_all_visit_of_all_discharged'] = round(($count_all_visits) / count($discharged_ipids), 2);// REPORT LINE: 13--> | Durchschnitt tatsächlich geleisteter Hausbesuche | average the ammount of all visits, of all discharged patients (even outside the report period)
        // --
        
        
        

        foreach ($contact_forms_all as $kcf => $day_cfs) {
            foreach ($day_cfs as $k_dcf => $v_dcf) {
                if (is_numeric($k_dcf)) {
                    if (strtotime(date('Y-m-d H:i:s', strtotime($v_dcf['start_date']))) > strtotime($patients_discharge_date[$v_dcf['ipid']]) && $patient_days[$v_dcf['ipid']]['details']['isdischarged'] == '1') {
                        unset($contact_forms_all[$kcf][$k_dcf]);
                    }
                }
            }
        }

        
        
        
        /*
         // REPORT LINE: 11 :
         all days which were SAPV days (active, not in hospital, had Verordnung TV or VV)
         AND which were billed (had a visit or were in the range of the first 7 days (flatrate))
         from FALL which were discharged in the report period. (this can mean, that some of the visits are outside the report period. the FALL is the selector. )
         */
        $anlage_ipids = array_keys($falls_endded_inReport_period);
        
        // patient days
        $conditions_oov['client'] = $clientid;
        $conditions_oov['ipids'] = $anlage_ipids;
        $conditions_oov['periods'][0]['start'] = '2009-01-01';
        $conditions_oov['periods'][0]['end'] = date('Y-m-d');
        
        $sql_ol = 'e.epid, p.ipid, e.ipid,';
        $sql_ol .= 'AES_DECRYPT(p.last_name,"' . Zend_Registry::get('salt') . '") as last_name,';
        $sql_ol .= 'AES_DECRYPT(p.first_name,"' . Zend_Registry::get('salt') . '") as first_name,';
        $sql_ol .= 'convert(AES_DECRYPT(p.zip,"' . Zend_Registry::get('salt') . '") using latin1) as zip,';
        $sql_ol .= 'convert(AES_DECRYPT(p.street1,"' . Zend_Registry::get('salt') . '") using latin1) as street1,';
        $sql_ol .= 'convert(AES_DECRYPT(p.city,"' . Zend_Registry::get('salt') . '") using latin1) as city,';
        $sql_ol .= 'convert(AES_DECRYPT(p.phone,"' . Zend_Registry::get('salt') . '") using latin1) as phone,';
        
        // beware of date d.m.Y format here
        $overall_patient_days = Pms_CommonData::patients_days($conditions_oov, $sql_ol);
        
        
        
        
        $current_form = array(
            'shanlage14'
        );
        $form_items = FormsItems::get_all_form_items($clientid, $current_form, 'v');

        foreach ($form_items[$current_form[0]] as $k_item => $v_item) {
            $items_arr[] = $v_item['id'];
        }
        $items_contact_forms = Forms2Items::get_items_forms($clientid, $items_arr);

        foreach ($contact_forms_all as $kcf => $day_cfs) {
            foreach ($day_cfs as $k_dcf => $v_dcf) {
                // format contact form date to fit the format used in patients_days()
                $contact_form_date = date('d.m.Y', strtotime($kcf));

                if (in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['real_active_days'])) {

                    // all contact forms mapped with id as key
                    $contact_forms_details[$v_dcf['id']] = $v_dcf;

                    if (in_array($v_dcf['form_type'], $items_contact_forms['sh_other_visits']) && ! in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) && ! in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) {
                        // catch the contact forms added by users which belong to the client setting selected groups
                        $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_other_visits'][] = $v_dcf['id'];
                        $count_visits = $count_visits + 1;
                    }

                    if (in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_hospiz_visits']) || in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_non_hospiz_visits'])) {
                        // all doctor contactforms
                        $contact_forms[$v_dcf['ipid']][$contact_form_date]['doctor_all'][] = $v_dcf['id'];
                        $count_visits = $count_visits + 1;

                        // split doctors contact forms into 2 entities (hospiz and non-hospiz)
                        if ((in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) || in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_hospiz_visits'])) {
                            $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_doc_hospiz_visits'][] = $v_dcf['id'];
                            $count_visits = $count_visits + 1;
                        } else if ((! in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) && ! in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_doc_non_hospiz_visits'])) {
                            $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_doc_non_hospiz_visits'][] = $v_dcf['id'];
                            $count_visits = $count_visits + 1;
                        }
                    }

                    if (in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_hospiz_visits']) || in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_non_hospiz_visits'])) {
                        // all nurse contactforms
                        $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_nur_visits'][] = $v_dcf['id'];
                        $count_visits = $count_visits + 1;

                        // nurse contact forms in hospiz and non hospiz(non hospiz is used in "Anzahl Tagespauschale")
                        if ((in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) || in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_hospiz_visits'])) {
                            $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_nur_hospiz_visits'][] = $v_dcf['id'];
                            $count_visits = $count_visits + 1;
                        } // nurse contact forms in hospiz and non hospiz(non hospiz is used in "Anzahl Tagespauschale")
                        else if ((! in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospiz']['real_days_cs']) && ! in_array($contact_form_date, $overall_patient_days[$v_dcf['ipid']]['hospital']['real_days_cs'])) && in_array($v_dcf['form_type'], $items_contact_forms['sh_nur_non_hospiz_visits'])) {
                            $contact_forms[$v_dcf['ipid']][$contact_form_date]['sh_nur_non_hospiz_visits'][] = $v_dcf['id'];
                            $count_visits = $count_visits + 1;
                        }
                    }
                }

                $count_visits = $count_visits + 1;
            }
        }
        // .get contact forms (ALL) END

 
 

        
//         $curent_period_days = array();
//         foreach ($discharged_ipids as $kk_ipid => $vv_ipid) {
//             $curent_period_days[$vv_ipid] = $final_period_days;
//         }

        foreach($falls_endded_inReport_period as $fipid =>$fall_data_info){
            array_walk($fall_data_info['all_fall_days'], function (&$value) {
                $value = date('d.m.Y', strtotime($value));
            });
                
            $curent_period_days[$fipid] = $fall_data_info['all_fall_days'];
        }
        
        // get saved data if any START
        $anlage14ctrl = new Anlage14Control();
        $anlage14_res = $anlage14ctrl->get_period_shstatistik_anlage14_controlsheet($clientid, $anlage_ipids, $report_dates, true);
        
        $master_overall_data = array();
        $invoice_data = array();
 
        // / overall data for flatrate
        // load saved data and create master data array START
        $real_active_days_all = array();
        foreach ($anlage_ipids as $kk_ipid => $vv_ipid) {

            $treated_days_all[$vv_ipid] = array_values($overall_patient_days[$vv_ipid]['treatment_days']);
            $real_active_days_all[$vv_ipid] = array_values($overall_patient_days[$vv_ipid]['real_active_days']);
            $pat_sapv_days_dmy[$vv_ipid] = $full_sapv_data[$vv_ipid]['sapv_days_overall'];

            array_walk($pat_sapv_days_dmy[$vv_ipid], function (&$value) {
                $value = date('d.m.Y', strtotime($value));
            });

            $treated_days_all[$vv_ipid] = array_intersect($treated_days_all[$vv_ipid], $pat_sapv_days_dmy[$vv_ipid]);

            $treated_days_all_ts[$vv_ipid] = $treated_days_all[$vv_ipid];
            array_walk($treated_days_all_ts[$vv_ipid], function (&$value) {
                $value = strtotime($value);
            });

            asort($treated_days_all_ts[$vv_ipid], SORT_NUMERIC);

            $treated_days_all_ts[$vv_ipid] = array_values(array_unique($treated_days_all_ts[$vv_ipid]));

            $pat_treatment_days[$vv_ipid] = $treated_days_all_ts[$vv_ipid];
            array_walk($pat_treatment_days[$vv_ipid], function (&$value) {
                $value = date('Y-m-d', $value);
            });
 
            $flatrate_treatment_days[$vv_ipid] = $treated_days_all_ts[$vv_ipid];

            if (count($flatrate_treatment_days[$vv_ipid]) > 0) {
                $flatrate_start[$vv_ipid] = $flatrate_treatment_days[$vv_ipid][0];
                $fl_days[$vv_ipid] = array();
                while (count($fl_days[$vv_ipid]) < '7') {
                    if (in_array($flatrate_start[$vv_ipid], $flatrate_treatment_days[$vv_ipid])) {
                        $fl_days[$vv_ipid][] = $flatrate_start[$vv_ipid];
                    } else {
                        $fl_days[$vv_ipid][] = $flatrate_treatment_days[$vv_ipid][0];
                    }

                    $flatrate_start[$vv_ipid] = strtotime('+1 day', $flatrate_start[$vv_ipid]);
                }
            }
 
            // ISPC-2478 Ancuta 27.10.2020 Start
            $days29ths[$vv_ipid] = array();
            if ($fisrt_Sapv_trigger_flatrate) { // check module

                foreach ($patient_Erstsapv_days[$vv_ipid] as $sid => $s_days) {
                    array_walk($s_days, function (&$value) {
                        $value = date('d.m.Y', strtotime($value));
                    });

                    $patient_Erstsapv_days[$vv_ipid][$sid] = array_values(array_intersect($treated_days_all[$vv_ipid], $s_days));
                    array_walk($patient_Erstsapv_days[$vv_ipid][$sid], function (&$value) {
                        $value = date('Y-m-d', strtotime($value));
                    });
                }

                foreach ($patient_Erstsapv_days[$vv_ipid] as $sid => $s_days) {

                    $s_days_ts = $s_days;
                    array_walk($s_days_ts, function (&$value) {
                        $value = strtotime($value);
                    });

                    // if existing flatrates - ar in the curent $s sapv days then skip
                    if (array_intersect($fl_days[$vv_ipid], $s_days_ts)) {} else {

                        $flatrate_treatment_days_sapv[$sid] = $s_days_ts;

                        if (count($flatrate_treatment_days_sapv[$sid]) > 0) {
                            $flatrate_start_sapv[$sid] = $flatrate_treatment_days_sapv[$sid][0];
                            $flatrate_start_days_sapv[$sid] = $flatrate_treatment_days_sapv[$sid][0];
                            $fl_days_Sapv[$sid] = array();
                            while (count($fl_days_Sapv[$sid]) < '7') {
                                if (in_array($flatrate_start_sapv[$sid], $flatrate_treatment_days_sapv[$sid])) {
                                    $fl_days_Sapv[$sid][] = $flatrate_start_sapv[$sid];
                                } else {
                                    $fl_days_Sapv[$sid][] = $flatrate_treatment_days_sapv[$sid][0];
                                }

                                $flatrate_start_sapv[$sid] = strtotime('+1 day', $flatrate_start_sapv[$sid]);
                            }

                            $fl_days[$vv_ipid] = array_merge($fl_days[$vv_ipid], $fl_days_Sapv[$sid]);
                        }
                    }
                }

                $fl_rts[$vv_ipid] = array();
                foreach ($flatrate_start_days_sapv as $sapv_id => $start_flartare_Date) {
                    $fl_rts[$vv_ipid][$sapv_id][] = $start_flartare_Date;

                    foreach ($treated_days_all_ts[$vv_ipid] as $kdt => $day_treatment) {
                        if ($day_treatment > $start_flartare_Date && count($fl_rts[$vv_ipid][$sapv_id]) < 30) {
                            $fl_rts[$vv_ipid][$sapv_id][] = $day_treatment;
                        }
                    }
                }

                foreach ($fl_rts[$vv_ipid] as $sids => $trsdays) {
                    if (count($trsdays) >= 29) {
                        $days29ths[$vv_ipid][] = end($trsdays);
                    }
                }
            }
            // ISPC-2478 Ancuta 27.10.2020 END
            
            //e006b63c34087dacc0dc38f0b8fadc8d996df06c

            // get FLATRATE DAYS - END
            foreach ($curent_period_days[$vv_ipid] as $k_day => $v_day) {
                $day_is_sapv = false;
                if (in_array($v_day, $curent_period_days_sapv[$vv_ipid])) {
                    $day_is_sapv = true;
                }
                
                foreach ($shortcuts_arr as $k_short => $v_short) {
                    if (in_array($v_short, $visits_shortcuts) && in_array($v_day, $real_active_days_all[$vv_ipid])) {
                        // handle visitable shortcuts here
                        // stop if day is in flatrate days
                        // reverted ISPC-1131 - show visits in flatrate days
                        if ($anlage14_res[$vv_ipid][$v_day][$v_short] > 0) {
                            $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                            $master_data[$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];

                            if (strlen($first_active_day[$vv_ipid]) == '0') {
                                $first_active_day[$vv_ipid] = $v_day;
                            }
                            $last_active_day[$vv_ipid] = $v_day;
                        } else if (count($contact_forms[$vv_ipid][$v_day][$v_short]) > '0' && ! array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv) {
                    
                            $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                            $master_data[$vv_ipid][$v_day][$v_short]['qty'] = count($contact_forms[$vv_ipid][$v_day][$v_short]);

                            if (strlen($first_active_day[$vv_ipid]) == '0') {
                                $first_active_day[$vv_ipid] = $v_day;
                            }
                            $last_active_day[$vv_ipid] = $v_day;
                        } else {
                            $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '0';
                            $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '0';
                        }

                        // add to totals
                        $master_data[$vv_ipid]['totals'][$v_short] += $master_data[$vv_ipid][$v_day][$v_short]['checked'];

                        // add to custom overall totals
                        if ($v_short == 'sh_nur_non_hospiz_visits' || $v_short == 'sh_doc_non_hospiz_visits' || $v_short == 'sh_other_visits') {
                            // Anzahl Tagespauschale - total days doc/nurse non hospiz (:: ISPI-2195 include also sh_other_visits )
                            if ($master_data[$vv_ipid][$v_day][$v_short]['checked'] == '1') {
                                $master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz'][] = $v_day;
                                $master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz'] = array_unique(array_values($master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz']));
                            }
                        } else if ($v_short == 'sh_nur_hospiz_visits' || $v_short == 'sh_doc_hospiz_visits') {
                            // Tagespauschalen Hospiz - total visits doc/nurse in hospiz
                            if ($master_data[$vv_ipid][$v_day][$v_short]['checked'] == '1') {
                                $master_overall_data[$vv_ipid]['overall_doc_nur_hospiz'][] = $v_day;
                                $master_overall_data[$vv_ipid]['overall_doc_nur_hospiz'] = array_unique(array_values($master_overall_data[$vv_ipid]['overall_doc_nur_hospiz']));
                            }
                        }

                        $master_data[$vv_ipid]['custom_totals']['sh_overall_doc_nur_non_hospiz'] = count($master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz']);
                        $master_data[$vv_ipid]['custom_totals']['sh_overall_doc_nur_hospiz'] = count($master_overall_data[$vv_ipid]['overall_doc_nur_hospiz']);
                    } else {

                        // handle the rest of shortcuts here
                        if ($v_short == 'sh_beko') {
                            if ($anlage14_res[$vv_ipid][$v_day][$v_short] > '0') {
                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];

                                if (strlen($first_active_day[$vv_ipid]) == '0') {
                                    $first_active_day[$vv_ipid] = $v_day;
                                }
                                $last_active_day[$vv_ipid] = $v_day;
                            } else if (strtotime($v_day) == strtotime($pat_treatment_days[$vv_ipid][0]) && ! array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv) {

                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '1';

                                if (strlen($first_active_day[$vv_ipid]) == '0') {
                                    $first_active_day[$vv_ipid] = $v_day;
                                }
                                $last_active_day[$vv_ipid] = $v_day;
                            } else {
                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '0';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '0';
                            }

                            $master_data[$vv_ipid]['custom_totals']['sh_overall_beko'] += $master_data[$vv_ipid][$v_day][$v_short]['qty'];
                        }

                        if ($v_short == 'sh_folgeko') {
                            if ($anlage14_res[$vv_ipid][$v_day][$v_short] > '0') {
                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];
                                if (strlen($first_active_day[$vv_ipid]) == '0') {
                                    $first_active_day[$vv_ipid] = $v_day;
                                }
                                $last_active_day[$vv_ipid] = $v_day;
                            } else if ((count($treated_days_all_ts[$vv_ipid]) >= '26' && strtotime($v_day) == $treated_days_all_ts[$vv_ipid][28] && ! array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv) || in_array(strtotime($v_day), $days29ths[$vv_ipid])) 
                            // ISPC-2478 Ancuta 27.10.2020 - additional condition $days29ths
                            {
                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '1';

                                if (strlen($first_active_day[$vv_ipid]) == '0') {
                                    $first_active_day[$vv_ipid] = $v_day;
                                }
                                $last_active_day[$vv_ipid] = $v_day;
                            } else {
                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '0';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '0';
                            }

                            $master_data[$vv_ipid]['custom_totals']['sh_overall_folgeko'] += $master_data[$vv_ipid][$v_day][$v_short]['qty'];
                        }

                        if ($v_short == 'sh_flatrate') {
                            if ($anlage14_res[$vv_ipid][$v_day][$v_short] > '0') {
                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = $anlage14_res[$vv_ipid][$v_day][$v_short];

                                if (strlen($first_active_day[$vv_ipid]) == '0') {
                                    $first_active_day[$vv_ipid] = $v_day;
                                }
                                $last_active_day[$vv_ipid] = $v_day;

                                // append flatrate into the Anzahl Tagespauschale
                                $master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz'][] = $v_day;
                                $master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz'] = array_unique(array_values($master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz']));
                            } else if (! empty($fl_days) && ! array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv) {
                                if (in_array(strtotime($v_day), $fl_days[$vv_ipid])) {
                                    $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                                    $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '1';

                                    if (strlen($first_active_day[$vv_ipid]) == '0') {
                                        $first_active_day[$vv_ipid] = $v_day;
                                    }
                                    $last_active_day[$vv_ipid] = $v_day;

                                    // append flatrate into the Anzahl Tagespauschale
                                    $master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz'][] = $v_day;
                                    $master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz'] = array_unique(array_values($master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz']));
                                } else {
                                    $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '0';
                                    $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '0';
                                }
                            } else {
                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '0';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '0';
                            }
                        }

                        // added limit to shown/calculate phones only in days with no Anzahl Tagepauschale triggered(has visit and/or flatrate)
                        if ($v_short == 'sh_telefonat') {
                            $qty_limit[$vv_ipid][$v_day] = '0';
                            if ($anlage14_res[$vv_ipid][$v_day][$v_short] > 0) {
                                // changed to show maximum 2 phones (same way as it was calculated)
                                if ($anlage14_res[$vv_ipid][$v_day][$v_short] >= '2') {
                                    $qty_limit[$vv_ipid][$v_day] = "2";
                                } else {
                                    $qty_limit[$vv_ipid][$v_day] = $anlage14_res[$vv_ipid][$v_day][$v_short];
                                }

                                if ($anlage14_res[$vv_ipid][$v_day][$v_short] > '0') {
                                    $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                                    $master_data[$vv_ipid][$v_day][$v_short]['qty'] = $qty_limit[$vv_ipid][$v_day];

                                    if (strlen($first_active_day[$vv_ipid]) == '0') {
                                        $first_active_day[$vv_ipid] = $v_day;
                                    }
                                    $last_active_day[$vv_ipid] = $v_day;
                                } else {
                                    $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '0';
                                    $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '0';
                                }
                            } else if (! in_array($v_day, $master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz']) && ! in_array(strtotime($v_day), $fl_days[$vv_ipid]) && ! array_key_exists($v_day, $anlage14_res[$vv_ipid]) && $day_is_sapv && ! in_array(date("d.m.Y", strtotime($v_day)), $invoice_data[$v_ipid]['invoice_data']['hospital_real_days_cs'])) {
                                // changed to show maximum 2 phones (same way as it was calculated)
                                if (count($patient_phones[$vv_ipid][$v_day][$v_short]) >= '2') {
                                    $qty_limit[$vv_ipid][$v_day] = "2";
                                } else {
                                    $qty_limit[$vv_ipid][$v_day] = count($patient_phones[$vv_ipid][$v_day][$v_short]);
                                }

                                if (count($patient_phones[$vv_ipid][$v_day][$v_short]) > '0') {
                                    $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '1';
                                    $master_data[$vv_ipid][$v_day][$v_short]['qty'] = $qty_limit[$vv_ipid][$v_day];

                                    if (strlen($first_active_day[$vv_ipid]) == '0') {
                                        $first_active_day[$vv_ipid] = $v_day;
                                    }
                                    $last_active_day[$vv_ipid] = $v_day;
                                } else {
                                    $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '0';
                                    $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '0';
                                }
                            } else {
                                $master_data[$vv_ipid][$v_day][$v_short]['checked'] = '0';
                                $master_data[$vv_ipid][$v_day][$v_short]['qty'] = '0';
                            }

                            // Anzahl Telefonpauschale - total phones with limit per day of 2 qty
                            $master_data[$vv_ipid]['custom_totals']['sh_overall_phones'] += $qty_limit[$vv_ipid][$v_day];
                        }

                        // add to totals
                        $master_data[$vv_ipid]['totals'][$v_short] += $master_data[$vv_ipid][$v_day][$v_short]['qty'];
                    }

                    $invoice_data[$vv_ipid]['invoice_data']['first_active_day'] = $first_active_day[$vv_ipid];
                    $invoice_data[$vv_ipid]['invoice_data']['last_active_day'] = $last_active_day[$vv_ipid];

                    $master_data[$vv_ipid]['custom_totals']['sh_overall_doc_nur_non_hospiz'] = count($master_overall_data[$vv_ipid]['overall_doc_nur_non_hospiz']);
                    $master_data[$vv_ipid]['custom_totals']['sh_overall_doc_nur_hospiz'] = count($master_overall_data[$vv_ipid]['overall_doc_nur_hospiz']);
                }
            }
        }
//         dd($visits_shortcuts,$contact_forms,$curent_period_days);
//         dd($master_data);
        
        
        
//         dd($master_data);
        $report_billing_visits_shs = array(
            "sh_doc_non_hospiz_visits",
            "sh_nur_non_hospiz_visits",
            "sh_other_visits",
            "sh_flatrate"
        );
        $report_billing_telefonat_shs = array(
            "sh_telefonat"
        );

        foreach ($master_data as $k_ipid => $v_invoice_data) {

            $billable_days['full'][$k_ipid] = array();
            $billable_days['visits'][$k_ipid] = array();
            $billable_days['phones'][$k_ipid] = array();

            foreach ($v_invoice_data as $bill_date => $date_values) {
                foreach ($date_values as $sh => $sh_data) {
                    if (! in_array($bill_date, $billable_days['full'][$k_ipid]) && (in_array($sh, $report_billing_visits_shs) || in_array($sh, $report_billing_telefonat_shs)) && $sh_data['qty'] > 0) {
                        $billable_days['full'][$k_ipid][] = $bill_date;
                    }
                    if (! in_array($bill_date, $billable_days['visits'][$k_ipid]) && in_array($sh, $report_billing_visits_shs) && $sh_data['qty'] > 0
                        && in_array(date('Y-m-d',strtotime($bill_date)), $falls_endded_inReport_period[$k_ipid] ['active_tv_vv_sapv_days_per_ipid'])
                        ) {
                        $billable_days['visits'][$k_ipid][] = $bill_date;
                    }

                    if (! in_array($bill_date, $billable_days['phones'][$k_ipid]) && in_array($sh, $report_billing_telefonat_shs) && $sh_data['qty'] > 0) {
                        $billable_days['phones'][$k_ipid][] = $bill_date;
                    }
                }
            }
        }

        foreach ($discharged_ipids as $ik => $ipid_value) {
            $billable_days_array[] = count($billable_days['visits'][$ipid_value]);
            $billable_phone_days_array[] = count($billable_days['phones'][$ipid_value]);
        }

        if (! empty($billable_days_array)) {
            $report_data['billable_days_cf'] = array_sum($billable_days_array);
            $report_data['at_least_one_sapv_Day'] = array_sum($billable_days_array); // REPORT LINE 11 ->
            $report_data['average_billable_days_cf'] = round(array_sum($billable_days_array) / count($discharged_ipids), 2);
        }
 
        
        $row = 0;
//         $translate_sh = $this->view->translate('sh_statistic_lag');

        foreach ($report_labels as $order => $label) {
            // $MasterData['data'][$row]["column"] = $translate_sh [$label];
            $MasterData['data'][$row]["column"] = $label;

            if (! empty($report_data[$label])) {

                if (is_array($report_data[$label])) {
                    $MasterData['data'][$row]["value"] = count($report_data[$label]);
                } else {
                    $MasterData['data'][$row]["value"] = $report_data[$label];
                }
            } else {
                $MasterData['data'][$row]["value"] = '-';
            }

            $row ++;
        }

        return $MasterData;
    }
}