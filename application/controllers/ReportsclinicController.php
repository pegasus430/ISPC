<?php

/**
 * Class ReportsclinicController
 *
 * Provide reports for the clinic part of ISPC
 * //Maria:: Migration CISPC to ISPC 22.07.2020
 */
class ReportsclinicController extends Zend_Controller_Action
{
    const  BLOCK_CAREPROCESS_CLINIC = 'FormBlockCareProcessClinic';

    public function init()
    {
        $this->xls_mode = 'xls';
        $this->xls_last_row = 0;
        $this->xls_last_col = 0;

        $this->iposmap=array(
            'Schmerz'=>'ipos2a',
            'Atemnot'=>'ipos2b',
            'Schwäche'=>'ipos2c',
            'Übelkeit'=>'ipos2d',
            'Erbrechen'=>'ipos2e',
            'Appetitlosigk.'=>'ipos2f',
            'Verstopfung'=>'ipos2g',
            'Mundtrockenh.'=>'ipos2h',
            'Schläfrigkeit'=>'ipos2i',
            'eing.Mobilität'=>'ipos2j',
            '3.besorgt'=>'ipos3',
            '4.Fam.besorgt'=>'ipos4',
            '5.traurig'=>'ipos5',
            '6.Frieden'=>'ipos6',
            '7.Anteilnahme'=>'ipos7',
            '8.Information'=>'ipos8',
            '9.Organisation'=>'ipos9',
        );
    }




    public function reportcareprocessAction()
    {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $this->view->pid = Pms_Uuid::encrypt($decid);
        $ipid = Pms_CommonData::getIpid($decid);

        //get the client-users
        $users = User::getUsersWithGroupnameFast($clientid);

        //get the PatienCases
        $model = new PatientCaseStatus();
        $patientcases = $model->get_list_patient_status($ipid, $clientid);

        $patientmaster = new PatientMaster();
        $formular_cases = array();

        if (empty($patientcases)) {
            //no clinic_cases => nor further activities necessary => return
            $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
            $tm = new TabMenus();
            $this->view->tabmenus = $tm->getMenuTabs();
            return;
        }


        foreach ($patientcases as $key => $case) {
            $formular_cases[$case['id']] = $model->format_patientcase_for_select_option($case);
        }
        $selected = '0';
        //selection has changed
        if (isset($_GET['caseid'])) {
            $selected = $_GET['caseid'];
        } //preselection of the last element. the array is ordered by admdate by default
        else {
            $selected = end($patientcases)['id'];
        }

        $this->view->patientcases = $formular_cases;
        $this->view->selectedcase = $selected;


        //get the list of Timedocumentation. Needed for getting the contact_form_ids for the selected patient_case_clinic
        $contact_form_ids = array();
        $time_doc_user = array();
        $formBlockTimedocumentation = new FormBlockTimedocumentationClinic();
        $formBlockTimedocumentationUser = new FormBlockTimedocumentationClinicUser();
        $timedocumentation = $formBlockTimedocumentation->find_timedocumentation_by_case_id($selected, $ipid);
        foreach ($timedocumentation as $value){
            $contact_form_ids[] = $value['contact_form_id'];
            $time_doc_user[$value['contact_form_id']] = $formBlockTimedocumentationUser->get_pretty_list_user($value['id'], $users);
        }
        $contact_form_ids = array_unique($contact_form_ids);

        //get the careprocess-data
        $formBlockValue = new FormBlockKeyValue();
        $lastBlockValues = $formBlockValue->getAllBlockValues_with_contactform($ipid, self::BLOCK_CAREPROCESS_CLINIC,null, null, $contact_form_ids);

        $forms = array();

        foreach ($lastBlockValues as $blockKey => $blockValue) {
            $row_data = json_decode($blockValue['v'], true);
            if (is_array($row_data) && isset($row_data['input_values']) && is_array($row_data['input_values'])) {
                $new_entry = array();
                $time = 0;
                $new_entry['date'] = $blockValue['ContactForm']['start_date'];

                //take the user(s) of timedocumentation first
                if($time_doc_user[$blockValue['contact_form_id']]){
                    $new_entry['user'] = $time_doc_user[$blockValue['contact_form_id']];
                }
                //take the create_user of the contactformular second
                else {
                    $userid = $blockValue['ContactForm']['create_user'];
                    $user = $users[$userid];
                    if (is_array($user)) {
                        $new_entry['user'] = $user['nice_name'];
                    }
                }
                foreach ($row_data['input_values'] as $key => $value) {
                    $art_item = substr($key, -4); //"xxxx", "_lbl', '_txt'
                    if ($art_item != '_lbl' && $art_item != '_txt' && $value != '') {
                        $label = '';
                        $printitem = array();
                        if (isset($row_data['input_values'][$key . '_lbl']))
                            $label = $row_data['input_values'][$key . '_lbl'];
                        if (isset($row_data['input_values'][$key . '_txt']))
                            $label = $label . $row_data['input_values'][$key . '_txt'];
                        $time += $value;
                        $printitem['time'] = $value;
                        $printitem['mass'] = $label;
                        $new_entry['printitems'][] = $printitem;
                    }
                }
                $new_entry['totaltime'] = $time;
                if (count($new_entry['printitems']) > 0) {
                    $forms[] = $new_entry;
                }

            }
        }

        $this->view->forms = $forms;

        if ($_GET['pdf']) {

            //set the print-layout
            $this->_helper->layout->setLayout('layout_ajax');
            $this->_helper->viewRenderer->setNoRender();
            $this->view->layout = 'PDF';

            $patientinfo = array();
            $masterdata = $patientmaster->getMasterData($decid, 0);
            $this->view->patientinfo = $masterdata ['nice_name'] . ', ' . $masterdata['nice_address'];

            $this->view->patientcase = $formular_cases[$selected];

            $rend = $this->view->render('reportsclinic/reportcareprocess.html');


            $header = $this->view->translate('care_process_summary');
            $footer_text = $this->view->translate('[Page %s from %s]');
            $options = array(
                "orientation" => "P",
                "customheader" => $header,
                "footer_type" => "1 of n",
                "footer_text" => $footer_text,
                "margins" => array(25, 25, 20),
            );
            Pms_PDFUtil::generate_pdf_to_browser($rend, $header, $options);

        } else {

            $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
            $tm = new TabMenus();
            $this->view->tabmenus = $tm->getMenuTabs();

        }

    }




    /**
     * IM-10
     * Shows the status of all clinic cases, grouped by month or quartal and grouped by station/konsil/ambulant
     * also allows download as excel-file
     */
    public function casestatusAction(){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid=$logininfo->clientid;


        $opsconfig = ClientConfig::getConfig($logininfo->clientid, 'opsconfig');


        $this->view->patient_startpage_link="patientcourse/patientcourse";

        $user_c_details = User::getUserDetails($logininfo->userid);

        $preflist=$user_c_details[0]['preferred_clinic_list'];
        if(!$preflist){
            $preflist="station";
        }

        if(in_array($_REQUEST['active_list'],['station','konsil','ambulant'])){
            $preflist=$_REQUEST['active_list'];
            $u=Doctrine::getTable('User')->findOneBy('id',$logininfo->userid);
            $u->preferred_clinic_list=$this->view->active_list;
            $u->save();
        }

        $this->view->active_list=$preflist;


        $startdate=date("Y-m-01 00:00:00");

        if ($_GET['m'] ){
            $startdate=$_GET['m'];
            $startdate=substr($startdate, 0, 4) ."-".substr($startdate, 4, 2) ."-01 00:00:00";
        }
        $startmonth=date("Y-m-01 00:00:00", strtotime($startdate));
        $nextmonth = date("Y-m-01 00:00:00", strtotime($startmonth . " + 1 month"));

        $this->view->by_quartal=0;
        if($_GET['by_quartal']==1){
            $this->view->by_quartal=1;
            $m=date('m',strtotime($startdate));
            $m=intval(intval($m-1)/3);
            $m=$m*3;
            $m=$m+1;
            if($m<10){
                $m="0".$m;
            }
            $startmonth=date("Y-".$m."-01 00:00:00", strtotime($startdate));
            $nextmonth = date("Y-m-01 00:00:00", strtotime($startmonth . " + 3 months"));
        }
        $this->view->first_day  =date("Y-m-01",strtotime($startmonth));
        $this->view->last_day   =date("Y-m-t", strtotime($nextmonth . " - 1 day"));

        $this->view->list_modus="by_presence";
        if (isset($_GET['list_modus'])){
            $this->view->list_modus=$_GET['list_modus'];
        }


        if($this->view->list_modus=="by_admission") {
            $readm_sql = Doctrine_Query::create()
                ->select('*')
                ->from('PatientCaseStatus')
                ->where('admdate >=?', $startmonth)
                ->andwhere('admdate <?', $nextmonth)
                ->andWhere('case_type=?',$preflist)
                ->andWhere('clientid=?',$clientid)
                ->orderBy('admdate ASC');
            $cases_arr = $readm_sql->fetchArray();
        }else{
            $readm_sql = Doctrine_Query::create()
                ->select('*')
                ->from('PatientCaseStatus')
                ->where('admdate <?', $nextmonth)
                ->andwhere('admdate >?', "2015-02-01 00:00:01")
                ->andwhere('disdate >? OR disdate<"2000-01-01 00:00:00"', $startmonth)
                ->andWhere('case_type=?',$preflist)
                ->andWhere('clientid=?',$clientid)
                ->orderBy('admdate ASC');
            $cases_arr = $readm_sql->fetchArray();
        }

        $ipids=array_column($cases_arr, 'ipid');
        $case_ids=array_column($cases_arr, 'id');
        //$eim=new EpidIpidMapping();
        //$epids=$eim->getIpidsEpids($ipids);

        $sendlog_sql = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCaseStatusLog')
            ->whereIn('case_id',$case_ids)
            ->andwhere('isdelete=0')
            ->orderBy('log_date DESC');
        $sendlog_arr = $sendlog_sql->fetchArray();

        $caseid_to_log=array();
        foreach ($sendlog_arr as $log){
            $caseid_to_log[$log['case_id']][]=$log;
        }

        $this->view->show_dgp=false;
        //@todo DGP
//        $modules= new Modules();
//        $dpg_module_nr=126;
//        if($modules->checkModulePrivileges($dpg_module_nr, $logininfo->clientid)){
//            $dgp_status=DgpPatientsHistory::cases_submited_status($case_ids);
//            $this->view->show_dgp=true;
//        }
        $patdetails=PatientMaster::get_multiple_patients_details($ipids);

        $encids=array();
        foreach($ipids as $ipid){
            $decid=Pms_CommonData::getIdfromIpid($ipid);
            $epid=Pms_Uuid::encrypt($decid) ;
            $encids[$ipid]=$epid;
        }

        //DEATH
        $ipids_to_deathdates=PatientMaster::get_patients_death_dates($clientid, $ipids);
        //END DEATH


        //ICONS
        $ip=new IconsPatient();
        $paticons=$ip->get_patient_icons($ipids);
        $caticons=Client::getClientconfig($clientid, 'orgaicons');

        $ic=new IconsClient();
        $cicons=$ic->get_client_icons($clientid);
        $used_icons=array();

        $ipid_to_icons=array();
        foreach ($paticons as $icon){
            if((!in_array($icon['icon_id'], $caticons)) && (isset($cicons[$icon['icon_id']]))) {
                $path=$cicons[$icon['icon_id']]['image'];
                $ipid_to_icons[$icon['ipid']][] = array('id'=>$icon['icon_id'], 'path'=>'icons_system/'.$path, 'color'=>$cicons[$icon['icon_id']]['color']);
                $used_icons[$icon['icon_id']]=$cicons[$icon['icon_id']];
                $used_icons[$icon['icon_id']]['path']='icons_system/'.$path;
            }
        }
        $this->view->used_icons=$used_icons;
        //END ICONS


        $cases=array();


        $inops_outops_cats=PatientCaseStatus::get_inops_outops_cats($_GET['casetype'], $opsconfig);
        $ops_user_mappings=PatientCaseStatus::get_opsusermappings($opsconfig);
        unset($ipid);
        foreach($cases_arr as $case){
            if($patdetails[$case['ipid']] && $case['case_type'] == $this->view->active_list) {

                $row = array();
                $row['patient_name'] = $patdetails[$case['ipid']]['first_name'] ." " . $patdetails[$case['ipid']]['last_name'];
                $bdate=new DateTime($patdetails[$case['ipid']]['birthd']);
                $row['birthd'] = $bdate->format('d.m.Y');
                $row['sex']="";
                if($patdetails[$case['ipid']]['sex']=="1"){
                    $row['sex']="m";
                }
                if($patdetails[$case['ipid']]['sex']=="2"){
                    $row['sex']="w";
                }
                $row['death_date']="";
                if(isset($ipids_to_deathdates[$case['ipid']])){
                    $deathdate=new DateTime($ipids_to_deathdates[$case['ipid']]);
                    $row['death_date']=$deathdate->format('d.m.Y');
                }
                $row['encid'] = $encids[$case['ipid']];
                $row['icons'] = $ipid_to_icons[$case['ipid']];
                $row['case_id'] = $case['id'];
                $row['case_number'] = $case['case_number'];
                $row['startdate'] = date('d.m.Y', strtotime($case['admdate']));
                $row['enddate'] = "";
                if (strtotime($case['disdate']) > strtotime('2010-01-01 00:00:00')) {
                    $row['enddate'] = date('d.m.Y', strtotime($case['disdate']));
                }
                $mins = array();
                $code = "";
                $code_neu ="";
                $row['abrechnung_start_date']=null;
                $row['abrechnung_done_date']=null;
                $row['mino_neu']="";
                $row['mino_patfern_neu']="";
                foreach ($caseid_to_log[$case['id']] as $log) {
                    $logdata=json_decode($log['log_data'],1);

                    if ("times" == $log['log_type'] && count($mins)==0) {
                        $mins = $logdata['mins'];

                        $mins_details=json_decode($logdata['mins_details'],1);
                        $row['mins_details']=PatientCaseStatus::get_opstimes_from_log($mins_details, $inops_outops_cats);
                    }
                    if ("OPS" == $log['log_type'] && strlen($code)==0) {
                        $code=$logdata['opscode'];
                    }
                }
                $row['mins_preview']=array();
                if(count($mins)==0){
                    //no sent data, fetch actual data as preview
                    $preview=PatientCaseStatus::get_opstimes_preview($case['ipid'], $case['id'], $inops_outops_cats, $ops_user_mappings);
                    $row['mins_preview']=$preview;
                }

                $row['code'] = $code;
                $row['code_neu'] = $code_neu;
                $row['mins'] = $mins;
                $row['minsum'] = "";
                $row['profsum'] = 0;
                foreach ($mins as $min) {
                    $imin=intval($min);
                    $row['minsum'] += $imin;
                    if($imin>0){
                        $row['profsum']++;
                    }
                }
                if($row['minsum']==0){
                    $row['profsum']="";
                }
//todo DGP
//                if($modules->checkModulePrivileges($dpg_module_nr, $logininfo->clientid)){
//                    $row['dgp']=$dgp_status[$case['id']];
//                }


                $cases[$case['case_type']][] = $row;

            }
        }
        $this->view->cases=$cases;

        $monthmap=array('Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
        $monthname=date('n',strtotime($startmonth));
        $year=date('Y',strtotime($startmonth));
        $monthname=$monthmap[$monthname-1];

        $today      = strtotime(date("Y-m-01 00:00:00"));
        $actmonth   = $today;
        $limitmonth = strtotime("2015-02-01 00:00:00");

        $months=array();
        while ($actmonth>$limitmonth){
            $months[date("Ym",$actmonth)]=$monthmap[date('n',$actmonth)-1] . " " .  date("Y",$actmonth);
            $d=date('Y-m-d H:i:s',$actmonth);
            $t=strtotime( $d . " -1 month");
            $actmonth=strtotime(date("Y-m-01 00:00:00", $t));
        }

        $this->view->months=$months;
        $this->view->monthindex=date("Ym",strtotime($startmonth));

        $this->view->startdate=array('monthname'=>$monthname, 'year'=>$year);
        $professions_captions=array(
            "Arzt"=>"Arzt",
            "Pflege"=>"Pflege",
            "Sozialarbeit"=>"Sozial&shy;arbeit",
            "Atemtherapie"=>"Atem&shy;therapie",
            "Psychologie"=>"Psycho&shy;logie",
            "Krankengymnastik"=>"Kranken&shy;gymn.",
            "Apotheke"=>"Apotheke",
            "Seelsorge"=>"Seel&shy;sorge"
        );

        $ops_groups_custom=array_keys($opsconfig['groupscustom']);
        $ops_groups_custom=array_filter($ops_groups_custom);
        $ops_groups=array_keys($opsconfig['groups']);
        if(count($ops_groups_custom)){
            $ops_groups=array_merge($ops_groups, $ops_groups_custom);
        }
        $this->view->ops_groups=array();

        foreach($ops_groups as $opsgroup){
            $xname=$opsgroup;
            if(isset($professions_captions[$opsgroup])){
                $xname=$professions_captions[$opsgroup];
            }
            $this->view->ops_groups[$opsgroup]=$xname;
        }



        $this->view->k_phasen=array(
            1=>"stabil",
            2=>"fluktuierende Symptome",
            3=>"erwartet verschlechternd",
            4=>"sterbend",

        );



        if($_GET['xls']) {
            $file=new Pms_ExcelWriter();
            $file->beginFile('xls');

            $file->writeLabel(0, 0, 'Zeitraum:');
            $file->writeLabel(0, 1, date('d.m.Y', strtotime($this->view->first_day)));
            $file->writeLabel(0, 2, 'bis');
            $file->writeLabel(0, 3, date('d.m.Y', strtotime($this->view->last_day)));
            $row = 1;
            $col = 0;
            $file->writeLabel($row, $col++, 'Fallart');
            $file->writeLabel($row, $col++, 'Fallnummer');
            $file->writeLabel($row, $col++, 'Geburtsdatum');
            $file->writeLabel($row, $col++, 'Sterbedatum');
            $file->writeLabel($row, $col++, 'Geschlecht');
            $file->writeLabel($row, $col++, 'Aufnahme');
            $file->writeLabel($row, $col++, 'Entlassung');
            $file->writeLabel($row, $col++, 'Tage');
            if ($this->view->list_modus == "by_presence"){
                if ($this->view->by_quartal == 1){
                    $file->writeLabel($row, $col++, 'Tage im Quartal');
                }else{
                    $file->writeLabel($row, $col++, 'Tage im Monat');
                }
            }

            $file->writeLabel($row,$col++,'OPS-Code');
            $file->writeLabel($row,$col++,'Minuten Gesamt');
            $file->writeLabel($row,$col++,'Minuten OPS relevant');

            $file->writeLabel($row,$col++,'Anzahl Berufsgruppen');
            foreach ($this->view->ops_groups as $opg){
                $file->writeLabel($row,$col++,'Minuten '.$opg);
            }

            $caemap=array('konsil'=>'Konsil', 'ambulant'=>'Ambulant', 'station'=>'Station');
            $casetype = $this->view->active_list;
            $prettyname = $caemap[$casetype];

            foreach ($this->view->cases[$casetype] as $case) {
                $row++;
                $col = 0;
                $file->writeLabel($row, $col++, $prettyname);
                $file->writeLabel($row, $col++, $case['case_number']);
                $file->writeLabel($row, $col++, $case['birthd']);
                $file->writeLabel($row, $col++, $case['death_date']);
                $file->writeLabel($row, $col++, $case['sex']);
                $file->writeLabel($row, $col++, $case['startdate']);
                $file->writeLabel($row, $col++, $case['enddate']);

                $btage = 0;
                if ($case['startdate']) {
                    $enddate = $case['enddate'] ? $case['enddate'] : date("d.m.Y");
                    $startdate = $case['startdate'];
                    $btage = abs(Pms_CommonData::get_days_number_between($startdate, $enddate)) + 1;
                    $istartdate=substr($startdate,6,4) . "-" . substr($startdate,3,2). "-" . substr($startdate,0,2);
                    if($istartdate<$this->view->first_day){
                        $istartdate=$this->view->first_day;
                    }
                    $ienddate=substr($enddate,6,4) . "-" . substr($enddate,3,2). "-" . substr($enddate,0,2);
                    if($ienddate>$this->view->last_day){
                        $ienddate=$this->view->last_day;
                    }
                    $btage2 = abs(Pms_CommonData::get_days_number_between($istartdate, $ienddate)) + 1;
                }
                $file->writeNumber($row, $col++, $btage);

                if ($this->view->list_modus == "by_presence"){
                    $file->writeNumber($row, $col++, $btage2);
                }

                $file->writeLabel($row, $col++, $case['code']);

                $file->writeNumber($row, $col++, $case['minsum']);

                $file->writeNumber($row, $col++, $case['mins_details']['mins_ops']);

                $file->writeNumber($row, $col++, $case['profsum']);

                foreach ($this->view->ops_groups as $mk => $mv) {
                    $file->writeNumber($row, $col++, $case['mins'][$mk]);
                }

            }

            $file->toBrowser('Statistik_'.$this->view->monthindex.'_'.$_GET['casetype']);
            //this exits
        }



    }

    /**
     * IM-59, Qualitätsindikatoren
     */
    public function qistatusAction(){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $qi9fromacp=Client::getClientconfig($clientid, 'qi9_from_acp');
        $this->view->qi9fromacp=false;
        if($qi9fromacp){
            $this->view->qi9fromacp=true;
        }


        $this->view->dgp=false;
        if(Modules::checkModulePrivileges("125", $logininfo->clientid))
        {
            $this->view->dgp =true;
        }



        $rfilter=array(
            'filter-icd'=>0,
            'filter-casetype'=>'station',
            'filter-range'=>0,
            'filter-month'=>date('m')-1,
            'filter-quartal'=>ceil(date('m')/4),
            'filter-year'=>date('Y')

        );

        if(isset($_REQUEST['filter-icd']) && $_REQUEST['filter-icd']>0){
            $rfilter['filter-icd']=1;
            if($_REQUEST['filter-icd']>1){
                $rfilter['filter-icd']=2;
            }
        }
        if(isset($_REQUEST['filter-casetype'])){
            $rfilter['filter-casetype']=$_REQUEST['filter-casetype'];
        }
        if(isset($_REQUEST['filter-range']) && $_REQUEST['filter-range']>0){
            $rfilter['filter-range']=$_REQUEST['filter-range'];
        }
        if(isset($_REQUEST['filter-month'])){
            $rfilter['filter-month']=$_REQUEST['filter-month'];
        }
        if(isset($_REQUEST['filter-quartal']) && $_REQUEST['filter-quartal']>0) {
            $rfilter['filter-quartal'] = $_REQUEST['filter-quartal'];
        }
        if(isset($_REQUEST['filter-year']) && $_REQUEST['filter-year']>0){
            $rfilter['filter-year']=$_REQUEST['filter-year'];
        }

        $this->view->rfilter=$rfilter;


        switch(intval($rfilter['filter-range'])){
            case 0:
                //100days from now
                $nextmonth = date("Y-m-d 23:59:59");
                $startmonth = date("Y-m-d 00:00:00", strtotime($nextmonth." -100 days"));
                break;
            case 1:
                //month
                $m=intval($rfilter['filter-month'])+1;
                if($m<10){$m="0".$m;}
                $startmonth = $rfilter['filter-year'] . "-" . $m . "-01 00:00:00";
                $nextmonth = date("Y-m-01 00:00:00", strtotime($startmonth." +32 days"));
                break;
            case 2:
                //quartal
                $m=(intval($rfilter['filter-quartal'])-1)*3;
                if($m<10){$m="0".$m;}
                $startmonth = $rfilter['filter-year'] . "-" . $m . "-01 00:00:00";
                $nextmonth = date("Y-m-01 00:00:00", strtotime($startmonth." +95 days"));
                break;
            case 3:
                //year
                $startmonth = $rfilter['filter-year'] . "-01-01 00:00:00";
                $nextmonth = date("Y-01-01 00:00:00", strtotime($startmonth." +400 days"));
                break;
        }

        $case_type=$rfilter['filter-casetype'];

        $pats=$this->filterPatientsByDate($clientid, $startmonth, $nextmonth, 'by_discharge', $case_type);


        $maindiags=PatientDiagnosis::get_multiple_patients_main_diagnosis($pats['ipids'], $clientid);
        //C00 - D48
        $patients_with_cd_diag=array();
        foreach($maindiags as $ipid=>$ds){
            foreach ($ds as $diag){
                if (preg_match('/^([Cc]\d\d)|^([Dd][0-3]\d)|^([Dd]4[0-8])/', $diag)){
                    $patients_with_cd_diag[]=$ipid;
                }
            }
        }
        unset($maindiags);
        unset($ds);
        unset($diag);

        $ipids=$pats['ipids'];
#print_r($ipids);
        if($rfilter['filter-icd']===1){
            $ipids=$patients_with_cd_diag;
        }
        if($rfilter['filter-icd']===2){
            $ipids=array();
            foreach ($pats['ipids'] as $ipid){
                if(!in_array($ipid, $patients_with_cd_diag)){
                    $ipids[]=$ipid;
                }
            }
        }

        if(!$this->view->qi9fromacp) {
            //BP BEGIN
            $patient_bp_sql = Doctrine_Query::create()
                ->select('*')
                ->from('FormBlockKeyValue a')
                ->whereIn('a.ipid', $ipids)
                ->andwhere('a.block=?', "bp_qi")
                ->andwhere('a.isdelete=0');
            $bp_arr = $patient_bp_sql->fetchArray();
            $ipid_to_bp = array();
            foreach ($bp_arr as $bp) {
                $d = json_decode($bp['v'], 1);
                if ($d['enable'] == "on") {
                    $ipid_to_bp[$bp['ipid']][] = $d['date'];
                }
            }
            unset($d);
            unset($bp_arr);
            unset($patient_bp_sql);
            //BP END
        }

        //DEPRESION BEGIN
        $patient_bp_sql = Doctrine_Query::create()
            ->select('*, c.start_date')
            ->from('FormBlockKeyValue a')
            ->leftJoin('a.ContactForm c')
            ->whereIn('a.ipid', $ipids)
            ->andwhere('a.block=?', "depression_screening")
            ->andwhere('a.isdelete=0');
        $bp_arr=$patient_bp_sql->fetchArray();
        $ipid_to_depression=array();
        foreach($bp_arr as $bp){
            $d=json_decode($bp['v'],1);
            if(isset($d['itemayes']) || isset($d['itemano'])){
                $ipid_to_depression[$bp['ipid']][] =strtotime($bp['ContactForm']['start_date']);
            }
        }
        unset($d);
        unset($bp_arr);
        unset($patient_bp_sql);
        //DEPRESSION END

        //DGP BEGIN
        // DGP for clinic isn't implemented yet, that's why an empty array - elena
        $caseid_to_kern=array();
        // i don't remove the commented code, it is not suitable for ambu (field caseid doesn't exist), but it will remain that this part have to be replaced - elena
        /*
        $patient_saved_kern_q = Doctrine_Query::create()
            ->select('ka.caseid, ka.laxantine, ka.medicproc, ka.tumorspez, ka.who, ka.therapieende')
            ->from('DgpKern ka')
            ->whereIn('ka.ipid', $ipids)
            ->andwhere('isadmission=0')
            ->andWhere('therapieende=1');
        $patient_kerns = $patient_saved_kern_q->fetchArray();
        $caseid_to_kern=array();
        foreach($patient_kerns as $kern){
            $caseid_to_kern[$kern['caseid']]=$kern;
        }
        unset($patient_kerns);
        unset($patient_saved_kern_q);*/
        //DGP END

        //IPOS BEGIN
        $iposdata_sql = Doctrine_Query::create()
            ->select('*')
            ->from('FormBlockIpos')
            ->whereIn('ipid', $ipids)
            ->orderBy('create_date ASC');
        $iposdata_arr = $iposdata_sql->fetchArray();

        $ipid_to_ipos = array();
        foreach ($iposdata_arr as $log) {
            if ($log['score'] !== "" || $log['special']=="pflegeipos") {
                $ipid_to_ipos[$log['ipid']][] = $log;
            }
        }
        //IPOS END

        //DEATH START
        $discharge_sql = Doctrine_Query::create()
            ->select("ipid, discharge_method, discharge_location, disdate ")
            ->from('PatientCaseStatus')
            ->whereIn('ipid', $ipids)
            ->andWhere('isdelete=0');
        $discharge_arr = $discharge_sql->fetchArray();
        $ipid_to_discharge = array();

        foreach ($discharge_arr as $discharge) {
            $ipid_to_discharge[$discharge['ipid']][] = $discharge;
        }

        $dischargemethods_sql = Doctrine_Query::create()
            ->select("id,description")
            ->from('DischargeMethod')
            ->where('clientid=?', $clientid);
        $dischargemethods_arr = $dischargemethods_sql->fetchArray();
        $dischargemethods = array();

        foreach ($dischargemethods_arr as $dischargemethod) {
            if(strpos(strtolower($dischargemethod['description']),'storben')>0
                || strstr(strtolower($dischargemethod['description']), 'tod')
                || strstr(strtolower($dischargemethod['description']), 'dead')
            ){
                $dischargemethods[]=$dischargemethod['id'];
            }
        }

        $patientdeath_sql = Doctrine_Query::create()
            ->select("id,ipid,death_date")
            ->from('PatientDeath')
            ->whereIn('ipid', $ipids)
            ->andwhere('isdelete=0');
        $patientdeath_arr = $patientdeath_sql->fetchArray();
        $ipid_to_patientdeath=array();
        foreach ($patientdeath_arr as $pdeath) {
            $ipid_to_patientdeath[$pdeath['ipid']] = $pdeath['death_date'];
        }
        foreach($ipid_to_discharge as $ipid=>$discharges){
            foreach ($discharges as $dis) {
                if (in_array($dis['discharge_method'], $dischargemethods)) {
                    $ipid_to_patientdeath[$dis['ipid']] = $dis['disdate'];
                }
            }
        }
//print_r($ipid_to_patientdeath);
        unset($discharge_arr);
        unset($dischargemethods_arr);
        unset($patientdeath_arr);
        //DEATH END



        $patnames = PatientMaster::getPatientNames($ipids);

        $encids = array();
        foreach ($ipids as $ipid) {
            $decid = Pms_CommonData::getIdfromIpid($ipid);
            $epid = Pms_Uuid::encrypt($decid);
            $encids[$ipid] = $epid;
        }
        $rows=array();

        foreach($pats['readm_arr'] as $case){
            $ipid=$case['ipid'];
            if(!in_array($ipid,$ipids)){
                //ipids may have been icd-filtered
                continue;
            }

            $row = array();
            $row['patient_name'] = $patnames[$ipid];
            $row['patient_epid']=$pats['epids'][$ipid];
            $row['encid'] = $encids[$ipid];
            $row['case_type']=$case['case_type'];
            $row['case_no'] = $case['case_number'];
            $row['case_start'] = date('d.m.Y', strtotime($case['date']));
            $row['case_end'] = "";
            if (strtotime($case['disdate']) > strtotime('2010-01-01 00:00:00')) {
                $row['case_end'] = date('d.m.Y', strtotime($case['disdate']));
            }
            $row['death_date']=$ipid_to_patientdeath[$ipid];

            $row['died_here']=false;
            if(strtotime($row['death_date'] . " -3 days") <= strtotime($row['case_end'])){
                $row['died_here']=true;
            }



            $row['cd_diag']=false;

            if(in_array($ipid, $patients_with_cd_diag)){
                $row['cd_diag']=true;
            }
            $row['qi3_n']=0;
            $row['kern_death']=0;
            $row['qi7']=0;
            $row['qi6']=0;
            if(isset($caseid_to_kern[$case['id']])){
                $kern=$caseid_to_kern[$case['id']];
                $row['qi7']=$kern['medicproc'];
                $row['qi6']=$kern['tumorspez'];
                if($kern['therapieende']==1) {
                    $row['kern_death'] = 1;
                }

                $row['qi3']=$kern['laxantine'];
                $row['qi3_n']=$kern['who'];
            }


            if($this->view->dgp && $row['kern_death']){
                $row['died_here']=$row['kern_death'];
            }

            $row['qi9']=0;
            if($this->view->qi9fromacp){
                if($case['has_acp']>0){
                    $row['qi9']=1;
                }
            }else {
                if (isset($ipid_to_bp[$ipid])) {
                    foreach ($ipid_to_bp[$ipid] as $bp) {
                        if ($bp > strtotime($row['case_start'] . " -1 day") && $bp <= strtotime($row['case_end'])) {
                            $row['qi9'] = 1;
                        }
                    }
                }
            }
            $row['qi8']=0;
            if(isset($ipid_to_depression[$ipid])){
                foreach ($ipid_to_depression[$ipid] as $bp){
                    if($bp>strtotime($row['case_start']." -1 day") && $bp<=strtotime($row['case_end'])){
                        $row['qi8']=1;
                    }
                }
            }

            if ($ipid_to_ipos[$case['ipid']]) {
                $last_schmerz = 0;
                $last_atem = 0;
                $row['qua_ipos_schmerzlinderung'] = "";
                $row['qua_ipos_schmerzstark'] = "";
                $row['qua_ipos_schmerzstarklinderung'] = "";
                $row['qua_ipos_schmerzmax'] = "";
                $row['qua_ipos_atemlinderung'] = "";
                $row['qua_ipos_atemstark'] = "";
                $row['qua_ipos_atemstarklinderung'] = "";
                $row['qua_ipos_atemmax'] = "";
                $row['qi10']=false;
                $row['qi4']=0;
                $row['qi5']=0;
                $last_schmerz_date=0;
                $last_atem_date=0;
                $case_ipos=$ipid_to_ipos[$case['ipid']];
                //print_r($case_ipos);
                array_multisort(array_column($case_ipos,'date'), $case_ipos);
                foreach ($case_ipos as $ipos) {
                    if ($ipos['score'] > 0 || strlen($ipos['ipos0']) > 1 || $ipos['special']=="pflegeipos") {
                        if (strtotime($ipos['date']) > (strtotime($case['date'])-4000) && (strtotime($ipos['date']) < strtotime($case['date'] . " +2 days"))){
                            $row['qi10']=true;
                        }

                        if (strtotime($ipos['date']) > (strtotime($case['date'])-4000) && (strtotime($ipos['date']) < strtotime($row['case_end'] . " +1 day"))) {


                            if($row['died_here']){
                                if(strtotime($ipos['date']) >= strtotime($row['case_end'] . " -3 days")){
                                    $row['qi4']=1; //IPOS in last 72h

                                    $this_unruhe = FormBlockIpos::mapToScorevalues($ipos['iposunruhe']);
                                    if($this_unruhe>0){
                                        $row['qi5']=1;
                                    }
                                }
                            }

                            if($ipos['special']!="pflegeipos") {
                                if ($row['qua_ipos_start'] === "") {
                                    $row['qua_ipos_start'] = $ipos['score'];
                                    $row['ipos_start_ipos'] = $ipos;
                                }
                                $row['qua_ipos_end'] = $ipos['score'];
                                $row['ipos_end_ipos'] = $ipos;
                            }
                            $this_schmerz = FormBlockIpos::mapToScorevalues($ipos['ipos2a']);
                            if (strlen($this_schmerz) > 0) {
                                if($row['qua_ipos_schmerzlinderung']===""){
                                    $row['qua_ipos_schmerzlinderung']=0;
                                    $row['qua_ipos_schmerzstark']=0;
                                    $row['qua_ipos_schmerzstarklinderung']=0;
                                }

                                if ($this_schmerz < $last_schmerz) {
                                    $row['qua_ipos_schmerzlinderung'] = 1;
                                }
                                if($this_schmerz > $row['qua_ipos_schmerzmax']){
                                    $row['qua_ipos_schmerzmax']=$this_schmerz;
                                }
                                if($this_schmerz >2 && $last_schmerz<3){
                                    $row['qua_ipos_schmerzstark'] = 1;
                                    $last_schmerz_date = strtotime($ipos['date']);
                                }
                                if($this_schmerz <3) {
                                    if ($last_schmerz_date > 0 && $last_schmerz_date>strtotime($ipos['date']." - 48 hours")) {
                                        $row['qua_ipos_schmerzstarklinderung']=1;
                                    }
                                    $last_schmerz_date = 0;
                                }
                            }
                            $last_schmerz = $this_schmerz;

                            $this_atem = FormBlockIpos::mapToScorevalues($ipos['ipos2b']);


                            if (strlen($this_atem) > 0) {
                                if( $row['qua_ipos_atemlinderung']===""){
                                    $row['qua_ipos_atemlinderung']=0;
                                    $row['qua_ipos_atemstark']=0;
                                    $row['qua_ipos_atemstarklinderung']=0;
                                }

                                if ($this_atem < $last_atem) {
                                    $row['qua_ipos_atemlinderung'] = 1;
                                }
                                if($this_atem > $row['qua_ipos_atemmax']){
                                    $row['qua_ipos_atemmax']=$this_atem;
                                }
                                if($this_atem >2 && $last_atem<3){
                                    $row['qua_ipos_atemstark'] = 1;
                                    $last_atem_date = strtotime($ipos['date']);
                                }
                                if($this_atem <3) {
                                    if ($last_atem_date > 0 && $last_atem_date>strtotime($ipos['date']." - 48 hours")) {
                                        $row['qua_ipos_atemstarklinderung']=1;
                                    }
                                    $last_atem_date = 0;
                                }
                            }
                            $last_atem = $this_atem;
                        }
                    }
                }
            }
            $rows[]=$row;
        }
        //print_r($rows);

        $data=array();
        foreach(range(1,10) as $q){
            $data['Q'.$q]['yes']=array();
            $data['Q'.$q]['no']=array();
        }

        foreach($rows as $e){
            $prow=array(
                'name'=>$e['patient_name'],
                'datetext'=>$e['case_start'] ." - ".$e['case_end'],
                'encid'=>$e['encid'],
                'case_no'=>$e['case_no'],
            );

            if($e['qua_ipos_schmerzstark']>0 && $e['qua_ipos_schmerzstarklinderung']<1){
                $data['Q1']['no'][]=$prow;
            }
            if($e['qua_ipos_schmerzstarklinderung']>0){
                $data['Q1']['yes'][]=$prow;
            }

            if($e['qua_ipos_atemstark']>0 && $e['qua_ipos_atemstarklinderung']<1){
                $data['Q2']['no'][]=$prow;
            }
            if($e['qua_ipos_atemstarklinderung']>0){
                $data['Q2']['yes'][]=$prow;
            }

            if($e['qi3_n']>0 && $e['qi3']>0){
                $data['Q3']['yes'][]=$prow;
            }
            if($e['qi3_n']>0  && $e['qi3']<1){
                $data['Q3']['no'][]=$prow;
            }

            if($e['died_here'] && !$e['qi4']){
                $data['Q4']['no'][]=$prow;
            }
            if($e['died_here'] && $e['qi4']){
                $data['Q4']['yes'][]=$prow;

                if($e['qi5']){
                    $data['Q5']['yes'][]=$prow;
                }else{
                    $data['Q5']['no'][]=$prow;
                }
            }

            if($e['kern_death'] && !$e['qi6']){
                $data['Q6']['no'][]=$prow;
            }
            if($e['kern_death'] && $e['qi6']){
                $data['Q6']['yes'][]=$prow;
            }

            if($e['kern_death'] && !$e['qi7']){
                $data['Q7']['no'][]=$prow;
            }
            if($e['kern_death'] && $e['qi7']){
                $data['Q7']['yes'][]=$prow;
            }

            if($e['cd_diag'] && !$e['qi8']){
                $data['Q8']['no'][]=$prow;
            }
            if($e['cd_diag'] && $e['qi8']){
                $data['Q8']['yes'][]=$prow;
            }

            if($e['cd_diag'] && !$e['qi9']){
                $data['Q9']['no'][]=$prow;
            }
            if($e['cd_diag'] && $e['qi9']){
                $data['Q9']['yes'][]=$prow;
            }

            if($e['cd_diag'] && !$e['qi10']){
                $data['Q10']['no'][]=$prow;
            }
            if($e['cd_diag'] && $e['qi10']){
                $data['Q10']['yes'][]=$prow;
            }

        }

        $this->view->data=$data;

    }

    /**
     * filters patients by data
     * IM-59
     *
     * @param $clientid
     * @param $startmonth
     * @param $nextmonth
     * @param $list_modus
     * @param string $casetype
     * @return array
     */
    private function filterPatientsByDate($clientid, $startmonth, $nextmonth, $list_modus, $casetype="all"){


        if($list_modus=="by_admission") {
            $readm_sql = Doctrine_Query::create()
                ->select('ipid')
                ->from('PatientCaseStatus')
                ->where('admdate >=?', $startmonth)
                ->andwhere('admdate <?', $nextmonth)
                ->orderBy('admdate ASC');
            $readm_arr = $readm_sql->fetchArray();
        }else{
            $readm_sql = Doctrine_Query::create()
                ->select('ipid')
                ->from('PatientCaseStatus')
                ->where('admdate <?', $nextmonth)
                ->andwhere('admdate >?', "2015-02-01 00:00:01")
                ->andwhere('disdate >? OR disdate<"2000-01-01 00:00:00"', $startmonth)
                ->orderBy('admdate ASC');
            $readm_arr = $readm_sql->fetchArray();
        }

        $ipids[] = '999999999';
        foreach ($readm_arr as $v_act_ipid)
        {
            if(!in_array($v_act_ipid['ipid'], $ipids)){
                $ipids[] = $v_act_ipid['ipid'];
            }
        }

        //get client patients
        $actpatient = Doctrine_Query::create()
            ->select("p.ipid, e.epid")
            ->from('PatientMaster p');
        $actpatient->leftJoin("p.EpidIpidMapping e");
        $actpatient->where('e.clientid = ' . $clientid);
        $actpatient->andwhere('p.isdelete = 0');
        $actpatient->andwherein('p.ipid', $ipids);
        $actipidarray = $actpatient->fetchArray();

        $icon_id_testpatient=Client::getClientconfig($clientid, 'testpatienticon');
        $testpatients=array();
        if($icon_id_testpatient > 0) {
            $testpatients=IconsPatient::getPatientsWithIcon($icon_id_testpatient);
        }

        $act_ipids[] = '999999999';
        $ipid_to_epid=array();
        foreach ($actipidarray as $v_act_ipid)
        {
            if(!in_array($v_act_ipid['ipid'], $testpatients)){
                $act_ipids[] = $v_act_ipid['ipid'];
            }
            $ipid_to_epid[$v_act_ipid['ipid']]=$v_act_ipid['EpidIpidMapping']['epid'];
        }

        if($list_modus=="by_admission") {
            $readm_sql = Doctrine_Query::create()
                ->select('*')
                ->from('PatientCaseStatus')
                ->where('admdate >=?', $startmonth)
                ->andwhere('admdate <?', $nextmonth)
                ->andWhereIn('ipid', $act_ipids)
                ->orderBy('date ASC');
        }elseif($list_modus=="by_discharge"){
            $readm_sql = Doctrine_Query::create()
                ->select('*')
                ->from('PatientCaseStatus')
                ->where('disdate >=?', $startmonth)
                ->andwhere('disdate <?', $nextmonth)
                ->andWhereIn('ipid', $act_ipids)
                ->orderBy('admdate ASC');
        }else{
            $readm_sql = Doctrine_Query::create()
                ->select('*')
                ->from('PatientCaseStatus')
                ->where('admdate <?', $nextmonth)
                ->andwhere('admdate >?', "2015-02-01 00:00:01")
                ->andwhere('disdate >? OR disdate<"2000-01-01 00:00:00"', $startmonth)
                ->andWhereIn('ipid', $act_ipids)
                ->orderBy('admdate ASC');

        }

        if($casetype!="all"){
            $readm_sql->andWhere('case_type=?',$casetype);
        }
        $readm_arr = $readm_sql->fetchArray();


        $case_ids=array();
        $ipids=array();

        foreach($readm_arr as $readm){
            if (!in_array($readm['ipid'],$ipids)){
                $ipids[]=$readm['ipid'];
            }
            if (!in_array($readm['id'],$case_ids)){
                $case_ids[]=$readm['id'];
            }
        }

        $out=array(
            'ipids'=>$ipids,
            'case_ids'=>$case_ids,
            'readm_arr'=>$readm_arr,
            'epids'=>$ipid_to_epid
        );

        return $out;
    }

    //Start TODO-4163
    //ISPC-2815
    public function companionreportAction()
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $this->view->setupnotes=[];
        $prm=new PatientCaseStatus();
        $this->usermappings=$prm->get_opsusermappings();
        $um=implode('', array_unique($this->usermappings));
        if($um==""){
            $this->view->setupnotes[]='Die Benutzergruppen müssen in der OPS-Konfiguration eingerichtet werden. ';
        }

        $testicon = Doctrine::getTable('IconsClient')->findOneByNameAndClientIdAndIsdelete('Testdaten',$clientid,0);
        $testiconid=$testicon->id;
        $this->testpatients=IconsPatient::getPatientsWithIcon($testiconid);

        if(!$testicon){
            $this->view->setupnotes[]='Es ist kein Icon für Testpatienten verfügbar. Testpatienten würden also in der Statistik auftauchen.';
        }else {
            if (count($this->testpatients)) {
                $this->view->setupnotes[] = 'Es gibt ' . count($this->testpatients) .' Testpatienten. Diese werden igonriert.';
            }
        }

        $this->view->merror = "";
        if (!$_GET['y']) {

            $ipid_sql = Doctrine_Query::create()
                ->select("id")
                ->from('FormBlockTimedocumentationClinic')
                ->Where('isdelete=0')
                ->andwhere('clientid=?',0)
                ->andwhere('contact_form_date>=?','2020-01-01 00:00:00');
            $count=$ipid_sql->execute()->count();

            if($count>0){
                $this->view->setupnotes[]='Es gibt '.$count.' unzugeordnete Zeitdokus. Tabellen müssen aktualisiert werden.';
            }

            $this->view->year = date('Y');
            return;
        }
        $this->view->year = intval($_GET['y']);

        if ($this->view->year < 2000 || $this->view->year > 3000) {
            $this->view->merror = "Bitte korrektes Jahr angeben..";
            return;
        }
        $startdate=$this->view->year."-01-01 00:00:00";
        $enddate=$this->view->year."-12-31 23:59:59";




        $ipid_sql = Doctrine_Query::create()
            ->select("id")
            ->from('FormBlockTimedocumentationClinic')
            ->Where('isdelete=0')
            ->andwhere('clientid=?',$clientid)
            ->andwhere('contact_form_date>=?',$startdate)
            ->andwhere('contact_form_date<=?',$enddate)
            ->groupBy('ipid');
        $count=$ipid_sql->execute()->count();
        $limit=100;
        $offset=0;


        $this->companion_id_max=0;
        $this->caseid_to_companion=[];

        $sapv=0;
        if($_GET['sapv']){
            $sapv=1;
        }

        $this->columns=[
            'kontakte',
            'datum',
            'zeit',
            'setting',
            'COMPANION_ID',
            'alter',
            'geschlecht',
            'aufnahmedatum',
            'entlassungsdatum',
            'muttersprache_deutsch',
            'uebersetzer_noetig',
            'art_uebersetzer',
            'anzahl_tage',
            'entlassart',
            'onkologisch',
            'diagnose',
            'erstdiagnose',
            'anz_BG',
            'mins_arzt',
            'mins_pflege',
            'mins_sozialarbeit',
            'mins_atemtherapie',
            'mins_psychologie',
            'mins_krankengymnastik',
            'mins_apotheke',
            'mins_seelsorge',
            'mins_patient',
            'mins_angehoerige',
            'mins_systemisch',
            'mins_profi',
            'arzt_mins_patient',
            'arzt_mins_angehoerige',
            'arzt_mins_systemisch',
            'arzt_mins_profi',
            'pflege_mins_patient',
            'pflege_mins_angehoerige',
            'pflege_mins_systemisch',
            'pflege_mins_profi',
            'sozialarbeit_mins_patient',
            'sozialarbeit_mins_angehoerige',
            'sozialarbeit_mins_systemisch',
            'sozialarbeit_mins_profi',
            'atemtherapie_mins_patient',
            'atemtherapie_mins_angehoerige',
            'atemtherapie_mins_systemisch',
            'atemtherapie_mins_profi',
            'psychologie_mins_patient',
            'psychologie_mins_angehoerige',
            'psychologie_mins_systemisch',
            'psychologie_mins_profi',
            'krankengymnastik_mins_patient',
            'krankengymnastik_mins_angehoerige',
            'krankengymnastik_mins_systemisch',
            'krankengymnastik_mins_profi',
            'apotheke_mins_patient',
            'apotheke_mins_angehoerige',
            'apotheke_mins_systemisch',
            'apotheke_mins_profi',
            'seelsorge_mins_patient',
            'seelsorge_mins_angehoerige',
            'seelsorge_mins_systemisch',
            'seelsorge_mins_profi',
            'palliativphase',
            'phasenwechsel',
            'IPOS_schmerzen',
            'IPOS_atemnot',
            'IPOS_schwaeche',
            'IPOS_uebelkeit',
            'IPOS_erbrechen',
            'IPOS_appetitlosigkeit',
            'IPOS_verstopfung',
            'IPOS_mundtrockenheit',
            'IPOS_schlaefrigkeit',
            'IPOS_mobilitaet',
            'IPOS_patient_beunruhigt',
            'IPOS_familie_beunruhigt',
            'IPOS_traurig',
            'IPOS_frieden',
            'IPOS_gefuehle',
            'IPOS_informationen',
            'IPOS_probleme',
            'PCPSS_schmerzen',
            'PCPSS_symptome',
            'PCPSS_probleme',
            'PCPSS_angehoerige',
            'kognitiv_verwirrtheit',
            'kognitiv_unruhe',
            'AKPS',
            'Barthel_stuhlkontrolle',
            'Barthel_urinkontrolle',
            'Barthel_waschen',
            'Barthel_toilette',
            'Barthel_essen',
            'Barthel_transfer',
            'Barthel_bewegung',
            'Barthel_ankleiden',
            'Barthel_treppen',
            'Barthel_baden',
        ];


        if($sapv){
            $this->columns[]='hausbesuch';
            $this->columns[]='aufenthaltsort';
            $this->columns[]='art_verordnung';
            $this->columns[]='fahrtzeit';
        }



        echo implode(';',$this->columns);
        echo "\r\n";
        while($offset<$count) {
            $this->companionreport_limited($limit, $offset, $clientid, $startdate, $enddate, $sapv);
            $offset=$offset+$limit;
        }


        $fileName = 'companion_'.$this->view->year . ".csv";
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/text");
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=" . $fileName);
        exit;
    }
    //ISPC-2815
    public function companionreport_limited($limit, $offset, $clientid, $startdate, $enddate, $issapv){

        $pcoc_ipid_sql = Doctrine_Query::create()
            ->select("ipid")
            ->from('FormBlockTimedocumentationClinic')
            ->Where('isdelete=0')
            ->andwhere('clientid=?',$clientid)
            ->andwhere('contact_form_date>=?',$startdate)
            ->andwhere('contact_form_date<=?',$enddate)
            ->groupBy('ipid')
            ->limit($limit)
            ->offset($offset);
        $ipidsar = $pcoc_ipid_sql->fetchArray();

        $ipids=[];
        foreach ($ipidsar as $ipid){
            if (!in_array($ipid['ipid'], $this->testpatients)){
                $ipids[]=$ipid['ipid'];
            }
        }

        $pcoc_sql = Doctrine_Query::create()
            ->select("*")
            ->from('FormBlockTimedocumentationClinic')
            ->Where('isdelete=0')
            ->andwhere('contact_form_date>=?',$startdate)
            ->andwhere('contact_form_date<=?',$enddate)
            ->andWhereIn('ipid',$ipids)
            ->orderBy('ipid ASC, contact_form_date ASC');
        $td_blocks = $pcoc_sql->fetchArray();
        $cf_ids=array_column($td_blocks, 'contact_form_id');

        $pcoc_sql = Doctrine_Query::create()
            ->select("*")
            ->from('FormBlockPcoc p INDEXBY p.contact_form_id')
            ->whereIn('p.contact_form_id',$cf_ids)
            ->groupBy('p.contact_form_id');
        $pcoc_blocks = $pcoc_sql->fetchArray();


        if($issapv){
            $dt_sql = Doctrine_Query::create()
                ->select("p.id, p.fahrtzeit, p.form_type")
                ->from('ContactForms p INDEXBY p.id')
                ->whereIn('p.id',$cf_ids);
            $form_infos = $dt_sql->fetchArray();

            $ft_sql = Doctrine_Query::create()
                ->select("p.id, p.action")
                ->from('FormTypes p INDEXBY p.id')
                ->where('clientid=?',$clientid)
                ->andWhere('p.action=1');
            $hausbesuch_types = $ft_sql->fetchArray();

        }

        $patmaster_sql = Doctrine_Query::create()
            ->select("p.ipid, p.birthd, AES_DECRYPT(p.sex,'" . Zend_Registry::get('salt') . "') as sex ")
            ->from('PatientMaster p INDEXBY p.ipid')
            ->whereIn('p.ipid', $ipids);
        $patmaster_by_ipid = $patmaster_sql->fetchArray();
        $case=null;

        //cache td-blocks by ipid
        $ipid_to_tds=[];
        foreach ($td_blocks as $td_block){
            $ipid_to_tds[$td_block['ipid']][]=$td_block;
        }

        $this->last_phase=[];//reset for function pcoc_helper_addpcoc
        foreach ($td_blocks as $td_block){
            $row=[];

            //add times
            $has_times=$this->pcoc_helper_addtimes($td_block, $row);
            if($has_times<1){
                //skip form with no times.
                continue;
            }

            $ipid=$td_block['ipid'];
            $cfid=$td_block['contact_form_id'];
            $date=$td_block['contact_form_date'];
            $date_date=substr($date,0,10);

            if($issapv){
                $caseidarr=$this->companion_virtual_cases($ipid, $td_block['contact_form_date']);
                $caseid=$caseidarr[0];
            }else{
                $caseid=$td_block['patient_case_status_id'];
            }

            if(!isset($caseid) || $caseid===0 || $caseid==="0"){
                continue;
            }
            if($td_block['isdelete']){
                continue;
            }
            if(!isset($this->case_cache[$caseid])){
                $this->fill_case_cache($caseid, $ipid, $clientid, $issapv);
            }
            $mycasemap = $this->case_cache[$caseid];

            $companion_id=$this->get_companion_id($caseid);
            if(!isset($counts[$caseid][$date_date])){
                $counts[$caseid][$date_date]=0;
            }
            $counts[$caseid][$date_date]++;

            $row['kontakte']=$counts[$caseid][$date_date];
            $row['datum']=date('d.m.Y',strtotime($date));
            $row['zeit']=date('H:i',strtotime($date));
            $row['setting']=$mycasemap['setting'];
            $row['COMPANION_ID']=$companion_id;//$this->companion_id;
            $age=intval(substr($date,0,4))-intval(date('Y',strtotime($patmaster_by_ipid[$ipid]['birthd'])));
            $row['alter']=$age; //fuzzy date: years in blocks year
            $companion_sexmap=[0=>2,1=>0,2=>1,3=>0];
            $row['geschlecht']=$companion_sexmap[$patmaster_by_ipid[$ipid]['sex']];

            foreach ([   'aufnahmedatum',
                         'entlassungsdatum',
                         'muttersprache_deutsch',
                         'uebersetzer_noetig',
                         'art_uebersetzer',
                         'anzahl_tage',
                         'entlassart',
                         'onkologisch',
                         'diagnose',
                         'erstdiagnose'
                     ] as $ck_k){
                $row[$ck_k]=$mycasemap[$ck_k];
            }

            //add values of pcoc_block
            if(isset($pcoc_blocks[$cfid])){
                $block = $pcoc_blocks[$cfid];
                $this->last_pcoc=$block;
            }
            if(isset($this->last_pcoc) && $this->last_pcoc['ipid']==$ipid) {
                //add the last known pcoc for this patient.
                //most times it is the pcoc block of this form
                $this->pcoc_helper_addpcoc($this->last_pcoc, $row);
            }

            if($issapv){
                $row['fahrtzeit']=0;
                $row['aufenthaltsort']=$caseidarr[1];

                $dt=FormBlockDrivetimedoc::getPatientFormBlockDrivetimedoc($ipid,$cfid);
                if($dt){
                    $row['fahrtzeit']=$dt[0]['fahrtzeit1'];
                }
                $row['hausbesuch']=0;
                if(isset($hausbesuch_types[$form_info['form_type']])){
                    $row['hausbesuch']=1;
                }

                $row['art_verordnung']=$caseidarr[2];

            }

            foreach ($this->columns as $col){
                echo $row[$col].";";
            }
            echo "\r\n";
        }
    }
    //ISPC-2815
    public function fill_case_cache($caseid, $ipid, $clientid, $issapv){


        if(!isset($this->case_cache)){
            $this->case_cache=[];
        }

        if(!isset($this->discharge_methods )){
            $this->discharge_methods =[];
            $epid = Doctrine_Query::create()
                ->select('*')
                ->from('DischargeMethod')
                ->where('clientid=' . $clientid . '');
            $disarray = $epid->execute()->toArray();
            foreach ($disarray as $loc){
                $this->discharge_methods[$loc['id']]=$loc['description'];
            }
        }

        /*        //find "Diagnose bei Aufnahme".
                $icd_primary="";
                $patient_dg_sql = Doctrine_Query::create()
                    ->select('*')
                    ->from('FormBlockKeyValue a')
                    ->where('ipid=?', $ipid)
                    ->andwhere('a.block=?', "FormBlockDiagnosisClinic")
                    ->andwhere('a.isdelete=0');
                $dg_arr=$patient_dg_sql->fetchArray();
                if($dg_arr){
                    $case_tds=[];
                    //make sure its a block from matching case
                    foreach($td_blocks as $td){
                        if($td['patient_case_status_id'] == $caseid){
                            $case_tds[]=$td['contact_form_id'];
                        }
                    }
                    $hdid=0;
                    foreach ($dg_arr as $dg){
                        if(in_array($dg['contact_form_id'], $case_tds)){
                            $dgv=json_decode($dg['v'],1);
                            if(count($dgv['clinic_diagnosis'])){
                                foreach ($dgv['diagnosis_types'] as $dt){
                                    if($dt['abbrevation']=="HD"){
                                        $hdid=$dt['id'];
                                        break;
                                    }
                                }
                                if($hdid){
                                    foreach($dgv['clinic_diagnosis'] as $cd){
                                        if($cd['diagnosis_type_id'] == $hdid){
                                            $icd_primary=$cd['icd_primary'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                //END find Diagnose*/


        $hd=PatientDiagnosis::get_multiple_patients_hd([$ipid], 1);

        $icd_primary = "";
        $icd_date = "";
        if(isset($hd[$ipid]) && strlen($hd[$ipid][0]['icdnumber'])){
            $icd_primary = $hd[$ipid][0]['icdnumber'];
            $icd_date = date('d.m.Y', strtotime($hd[$ipid][0]['create_date']));
        }
        if(!$issapv) {
            $companion_casemap = ['station' => 0, 'konsil' => 1, 'ambulant' => 4, '' => 0, 'sapv' => 2];
            $case = Doctrine::getTable('PatientCaseStatus')->findOneBy('id', $caseid);
            $row['setting'] = $companion_casemap[$case['case_type']];
            $row['aufnahmedatum'] = date('d.m.Y', strtotime($case['admdate']));
            $row['entlassungsdatum'] = date('d.m.Y', strtotime($case['disdate']));
            $enddate = date('Y-m-d', strtotime($case['disdate']));
            if ($case['disdate'] < '2000-01-01 00:00:00') {
                $row['entlassungsdatum'] = "";
                $enddate = date('Y-m-d');
            }
            $distype = "";
            if ($row['entlassungsdatum'] !== "") {
                $distype = $this->discharge_methods[$case['discharge_method']];
            }
        }else{
            $distype = "";
            $row['setting']=2;//SAPV
            if(strpos($caseid,'fall')>-1){
                $case=$this->ipid_to_fall[$ipid][$caseid];
            }else{
                $case=$this->ipid_to_location[$ipid][$caseid];
            }

            $row['aufnahmedatum'] = date('d.m.Y', strtotime($case['start']));
            $row['entlassungsdatum'] = date('d.m.Y', strtotime($case['end']));
            $enddate=$case['end'];
            if($enddate == date('Y-m-d')){
                $row['entlassungsdatum'] = "";
            }else{

                $pd=new PatientDischarge();
                $pdi=$pd->getPatientDischarge($ipid);
                if($pdi){
                    foreach($pdi as $pd){
                        $dd=$pd['discharge_date'];
                        $dd=substr($dd,0,10);
                        if($dd == $enddate){
                            $distype = $this->discharge_methods[$pd['discharge_method']];
                        }
                    }
                }
            }
            $case['admdate'] = date('Y-m-d', strtotime($case['start']));
        }



        $dol=0;
        $deutsch="";
        $u=Doctrine::getTable('Stammdatenerweitert')->findOneBy('ipid',$ipid);

        if($u){
            $dolmetscher=$u->dolmetscher;
            if(strlen($dolmetscher) && $dolmetscher!="nein"){
                $dol=1;
                $deutsch="0";
            }
        }

        $row['muttersprache_deutsch']=$deutsch;//Mutersprache
        $row['uebersetzer_noetig']=$dol;//Übersetzer
        $row['art_uebersetzer']='';//Artübers 0= Ärztliches Personal mit gleicher Muttersprache (MS); 1= Pflegepersonal mit gleicher MS; 2= Sonstiges Personal mit gleicher MS; 3= Angehörige/Freunde/Bekannte des Patienten; 4= Professionelle Dolmetscher; 5= Videodolmetscher; 6= Zimmernachbarn/andere Patienten gleicher MS;
        $origin = new DateTime($case['admdate']);
        $target = new DateTime($enddate);
        $interval = $origin->diff($target);
        $interval= intval($interval->format('%a'))+1;
        $row['anzahl_tage']=$interval;

        $row['entlassart']=$distype;
        $row['onkologisch']=PatientDiagnosis::is_onko($icd_primary);
        $row['diagnose']=$icd_primary;//diagnose
        $row['erstdiagnose']=$icd_date;//erstdiagnosedatum

        $this->case_cache[$caseid]=$row;
    }


    //ISPC-2815
    public function get_companion_id($caseid){
        if(isset($this->caseid_to_companion[$caseid])){
            return $this->caseid_to_companion[$caseid];
        }else{
            $this->companion_id_max=$this->companion_id_max+1;
            $this->caseid_to_companion[$caseid] = $this->companion_id_max;
            return $this->companion_id_max;
        }
    }


    public function group_times_by_opsgroup($times){
        $out=[];
        $cats=['minutes', 'mins_patient', 'mins_family', 'mins_systemic', 'mins_profi'];
        foreach ($times as $time){
            if(!isset($out[$time['opsgrp']])){
                $out[$time['opsgrp']]=[];
                foreach($cats as $cat){
                    $out[$time['opsgrp']][$cat]=0;
                }
            }
            foreach($cats as $cat){
                $out[$time['opsgrp']][$cat]= $out[$time['opsgrp']][$cat] + intval($time[$cat]);
            }
        }
        return $out;
    }


    //for Companionreport
    //needs COMPANION_ID already be set in $row
    public function pcoc_helper_addpcoc($block, &$row){
        $pcocstats=[
            ['phase_phase', 'palliativphase',0],
            ['phase_change', 'Phasenwechsel',0],
            ['ipos2a','IPOS_schmerzen',-1],
            ['ipos2b','IPOS_atemnot',-1],
            ['ipos2c','IPOS_schwaeche',-1],
            ['ipos2d','IPOS_uebelkeit',-1],
            ['ipos2e','IPOS_erbrechen',-1],
            ['ipos2f','IPOS_appetitlosigkeit',-1],
            ['ipos2g','IPOS_verstopfung',-1],
            ['ipos2h','IPOS_mundtrockenheit',-1],
            ['ipos2i','IPOS_schlaefrigkeit',-1],
            ['ipos2j','IPOS_mobilitaet',-1],

            ['ipos3','IPOS_patient_beunruhigt',-1],
            ['ipos4','IPOS_familie_beunruhigt',-1],
            ['ipos5','IPOS_traurig',-1],
            ['ipos6','IPOS_frieden',-1],
            ['ipos7','IPOS_gefuehle',-1],
            ['ipos8','IPOS_informationen',-1],
            ['ipos9','IPOS_probleme',-1],

            ['pcpss_pain','PCPSS_schmerzen',-1],
            ['pcpss_other','PCPSS_symptome',-1],
            ['pcpss_psy','PCPSS_probleme',-1],
            ['pcpss_rel','PCPSS_angehoerige',-1],

            ['nps_verwirrtheit','kognitiv_verwirrtheit',-1],
            ['nps_unruhe','kognitiv_unruhe',-1],

            ['akps_akps','AKPS','akps'],

            ['barthel1','Barthel_stuhlkontrolle',-1],
            ['barthel2','Barthel_urinkontrolle',-1],
            ['barthel3','Barthel_waschen',-1],
            ['barthel4','Barthel_toilette',-1],
            ['barthel5','Barthel_essen',-1],
            ['barthel6','Barthel_transfer',-1],
            ['barthel7','Barthel_bewegung',-1],
            ['barthel8','Barthel_ankleiden',-1],
            ['barthel9','Barthel_treppen',-1],
            ['barthel10','Barthel_baden',-1]
        ];

        foreach ($pcocstats as $pcoci){
            if($pcoci[0]=='phase_change'){
                if(isset($last_phase[$row['COMPANION_ID']]) && $this->last_phase[$row['COMPANION_ID']]!==$block['phase_phase']){
                    $val= 1;
                }else{
                    $val = 0;
                }
                $this->last_phase[$row['COMPANION_ID']]=$block['phase_phase'];

            }else {
                $val = intval($block[$pcoci[0]]);
                if ($pcoci[2] === -1) {
                    $val = $val - 1;
                    if ($val == -2) {
                        $val = -99;
                    }
                    if ($val == -1) {
                        $val = "";
                    }
                } elseif ($pcoci[2] === 'akps') {
                    if ($val === 0) {
                        $val = "";
                    } else {
                        if ($val < 10) {
                            $val = 0;
                        }
                    }
                }
            }
            $row[$pcoci[1]] = $val;
        }
    }

    //for Companionreport
    //needs $this->usermappings to be set
    public function pcoc_helper_addtimes($td_block, &$row){

        $pcoc_sql = Doctrine_Query::create()
            ->select("*")
            ->from('FormBlockTimedocumentationClinicUser p')
            ->where('p.form_id=?',$td_block['id']);
        $userminutes = $pcoc_sql->fetchArray();

        foreach ($userminutes as $k=>$userentry){
            $opsgrp=$this->usermappings[$userentry['userid']];
            if(isset($opsgrp)){
                $opsgrp=strtolower($opsgrp);
            }
            $userminutes[$k]['opsgrp']=$opsgrp;
        }
        if(isset($userminutes)) {
            $tgroup = $this->group_times_by_opsgroup($userminutes);
        }else {
            $tgroup = [];
        }
        $profs = [
            "arzt",
            "pflege",
            "sozialarbeit",
            "atemtherapie",
            "psychologie",
            "krankengymnastik",
            "apotheke",
            "seelsorge"];
        $cats = [
            'patient',
            'family',
            'systemisch',
            'profi'
        ];
        foreach ($cats as $cat) {
            $row["mins_" . $cat] = 0;
        }
        $groups = [];
        foreach ($profs as $prof) {
            $row["mins_" . $prof] = 0;
            foreach ($cats as $cat) {
                $mins=0;
                if(isset($tgroup[$prof])) {
                    $mins = intval($tgroup[$prof]['mins_'.$cat]);
                    $groups[$prof] = 1;
                }
                $cat2=$cat;
                if($cat=="family"){
                    $cat2='angehoerige';
                }
                $row[$prof . "_mins_" . $cat2]   = $mins;
                $row["mins_" . $prof]           = $row["mins_" . $prof] + $mins;
                $row["mins_" . $cat2]            = $row["mins_" . $cat2] + $mins;
            }
        }
        $row['anz_BG']=count($groups);

        return count($groups);
    }


    public function companion_virtual_cases($ipid, $date){
        if(!isset($this->ipid_to_location)){
            $this->ipid_to_location=[];
        }

        if(!isset($this->ipid_to_location[$ipid])){
            $period['start']="2021-01-01 00:00:00";
            $period['end']=date('Y-m-d 23:59:59');
            $loco=PatientLocation::getPatientLocationsPeriods($ipid, $period);

            $ipidsloc=[];
            foreach ($loco as $loc){
                $locentry=[];
                $locentry['start']    =$loc['days']['start'];
                $locentry['end']      =$loc['days']['end'];
                if($loc['valid_till'] == "0000-00-00 00:00:00"){
                    $locentry['end'] = date('Y-m-d');
                }
                $locentry['location'] =$loc['master_details']['location'];
                $locentry['locid']    =$loc['id'];
                $ipidsloc['loc-' . $loc['id']]=$locentry;
            }
            $this->ipid_to_location[$ipid]=$ipidsloc;

            $falls=PatientReadmission::findFallsOfIpid($ipid);
            $ipidsfalls=[];
            if($falls){
                foreach ($falls as $fall){
                    $locentry=[];
                    $locentry['start']    =substr($fall['admission']['date'],0,10);
                    $locentry['end']      =substr($fall['discharge']['date'],0,10);
                    $ipidsfalls['fall-' . $fall['admission']['id']]=$locentry;
                }
            }
            $this->ipid_to_fall[$ipid]=$ipidsfalls;

            $pd=new PatientDischarge();
            $dischs=$pd->getPatientDischarge($ipid);
            $this->ipid_to_discharge[$ipid]=[];
            if($dischs){
                foreach($dischs as $dis){
                    $dm=$dis['discharge_method'];
                    $dd=substr($dis['discharge_date'],0,10);
                    $this->ipid_to_discharge[$ipid][$dd]=$dm;
                }
            }



            $sapv = Doctrine_Query::create()
                ->select("*")
                ->from('SapvVerordnung')
                ->where('ipid=?', $ipid)
                ->andWhere('isdelete = 0')
                ->andWhere('verordnungam != "0000-00-00 00:00:00" AND DATE(verordnungam) != "1970-01-01"')
                ->andWhere('verordnungbis != "0000-00-00 00:00:00" AND DATE(verordnungbis) != "1970-01-01"')
                ->andWhere('verordnet != ""')
                ->andWhere('status>1')
                ->orderBy('verordnungam, verordnungbis ASC');
            $sapv_res = $sapv->fetchArray();
            $this->ipid_to_sapv[$ipid]=$sapv_res;
        }

        $caseid="0";
        $shortdate=substr($date,0,10);

        $falls=$this->ipid_to_fall[$ipid];
        if(count($falls)){
            foreach($falls as $cid=>$fall){
                if($fall['start']<=$shortdate && $fall['end']>=$shortdate){
                    $caseid=$cid;
                }
            }
        }

        $location="";
        $locs=$this->ipid_to_location[$ipid];
        foreach ($locs as $cid=>$loc){
            if($loc['start']<=$shortdate && $loc['end']>=$shortdate){
                $caseid=$cid;
                $location=$loc['location'];
            }
        }

        $sapvmap=[
            0=>"",
            1=>"Beratung",
            2=>"Koordination",
            3=>"Teilversorgung",
            4=>"Vollversorgung"
        ];

        $highest_sapv=0;
        if(isset($this->ipid_to_sapv[$ipid])){
            foreach ($this->ipid_to_sapv[$ipid] as $sapv){
                $von=substr($sapv['verordnungam'],0,10);
                $bis=substr($sapv['verordnungbis'],0,10);
                if($bis<"2000-01-01"){
                    $bis=date("Y-m-d");
                }
                if($date>$von && $date<$bis){
                    $v=$sapv['verordnet'];
                    $v=explode(",",$v);
                    foreach ($v as $vi){
                        $vii=intval($vi);
                        if($vii>$highest_sapv){
                            $highest_sapv=$vi;
                        }
                    }
                }
            }
        }

        $highest_sapv=$sapvmap[$highest_sapv];
        return [$caseid, $location,$highest_sapv];
    }
    //END TODO-4163
}

?>