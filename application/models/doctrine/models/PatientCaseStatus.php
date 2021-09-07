<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
Doctrine_Manager::getInstance()->bindComponent('PatientCaseStatus', 'IDAT');

class PatientCaseStatus extends BasePatientCaseStatus
{

    private $args = array();
    private $editableFields = array('case_number', 'case_type', 'admdate', 'disdate', 'case_finished', 'isdelete');

    public $case_types = array('keineauswahl' => '', 'station' => 'Station', 'konsil' => 'Palliativ-Dienst', 'ambulant' => 'Ambulant', 'sapv' => 'SAPV', 'standby' => 'Warteliste');
    public $case_status = array('0' => ' noch offen', '1' => 'abgeschlossen');


    /**
     * Find the list of status for the given patient.
     *
     * @param $ipid patient-ipid
     * @param $clientid
     * @return the current assigend beds
     */
    public function get_list_patient_status($ipid, $clientid=null, $onlyOpenCases = false)
    {

        $patstatus = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCaseStatus')
            ->where('ipid=?', $ipid)
            ->andWhere('isdelete=0');
        if(isset($clientid)) {
            $patstatus->andWhere('clientid=?', $clientid);
                }

            if($onlyOpenCases)
                $patstatus->andWhere('disdate=?', '0000-00-00 00:00:00');
            $patstatus->orderBy('admdate');

            return $patstatus->fetchArray();


    }


    /**
     * Find the list of status for the given patient (only closed cases).
     *
     * @param $ipid patient-ipid
     * @param $clientid
     * @return the current assigend beds
     */
    public function get_list_patient_status_discharge($ipid, $clientid, $onlyClosedCases = true)
    {

        $patstatus = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCaseStatus')
            ->where('ipid=?', $ipid)
            ->andWhere('clientid=?', $clientid)
            ->andWhere('isdelete=0');
            if($onlyClosedCases){
                $patstatus->andWhere('disdate>?', '0000-00-00 00:00:00');
                $patstatus->andWhere('admdate>?', '1970-01-01 00:00:00');

            }

            $patstatus->orderBy('admdate');

            return $patstatus->fetchArray();


    }

    /**
     * Find the list of status for the given patient (only open cases).
     *
     * @param $ipid patient-ipid
     * @param $clientid
     * @return the current assigend beds
     */
    public function get_list_patient_status_open($ipid, $clientid)
    {

        $patstatus = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCaseStatus')
            ->where('ipid=?', $ipid)
            ->andWhere('clientid=?', $clientid)
            ->andWhere('isdelete=0');

            $patstatus->andWhere('disdate=?', '0000-00-00 00:00:00');
            $patstatus->andWhere('admdate>?', '1970-01-01 00:00:00');



            $patstatus->orderBy('admdate');

            return $patstatus->fetchArray();


    }

    public function get_list_patients_by_status($clientid, $status, $onlyOpenCases = false)
    {
      $patstatus = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCaseStatus')
            ->where('case_type=?', $status)
            ->andWhere('clientid=?', $clientid)
            ->andWhere('isdelete=0');
        if($onlyOpenCases)
            $patstatus->andWhere('disdate=?', '0000-00-00 00:00:00');
        $patstatus->orderBy('admdate');

        return $patstatus->fetchArray();


    }

    /**
     * Find the status by id.
     *
     * @param $ipid patient-ipid
     * @param $clientid
     * @return the current assigend beds
     */
    public function get_patient_status($id)
    {

        $patstatus = Doctrine_Query::create()
            ->select('*')
            ->from('PatientCaseStatus')
            ->where('id=?', $id);
        return $patstatus->fetchOne();
    }


    /*
     * Method to handle the database-events for a single Status.
     * The method will create, update or delete the status, in dependence
     * of the current state.
     * Create: if the id is 0
     * Update: if the id is not null
     * Delete: is an update too. Just set the marker isdelete to true.
     *
     * @param $ipid
     * @param $clientid
     * @param array $args
     */
    public function update_patient_status($ipid, $clientid, $fallid, $args = array())
    {

        $this->args = func_get_arg(3);

        $this->update_timestamps();

        if (!$this->args) //nothing to do
            return false;

        if ($fallid == 0)
            $this->insert_db($ipid, $clientid);
        else
            $this->update_db($ipid, $clientid, $fallid);

        return true;

    }

    /**
     * removes discharge informations for case and marks case as not finished
     *
     * @param $ipid
     * @param $clientid
     * @param $discharge_to_delete_id
     * @throws Doctrine_Connection_Exception
     */
    public function rollback_discharge($ipid, $clientid, $discharge_to_delete_id){

        $update = $this->get_patient_status($discharge_to_delete_id);

        $update->disdate = '0000-00-00 00:00:00';
        $update->case_finished = 0;
        $update->discharge_method = 0;
        $update->discharge_location = 0;
        $update->discharge_comment = '';

        $update->replace();

    }

    /**
     * Method to handle the database-events for a set of Status for a single patient.
     * @param $ipid
     * @param $clientid
     * @param array $args
     * @return bool
     */
    public function update_list_patient_status($ipid, $clientid, $args = array())
    {

        if (!$args) //nothing to do
            return false;

        foreach ($args as $value) {
            $fallid = $value['id'];
            $this->update_patient_status($ipid, $clientid, $fallid, $value);
        }

        return true;

    }


    private function update_db($ipid, $clientid, $fallid)
    {
        $update = new PatientCaseStatus();

        $update->clientid = $clientid;
        $update->ipid = $ipid;
        $update->id = $fallid;

        foreach ($this->editableFields as $value) {
            if (array_key_exists($value, $this->args)) {
                $update->{$value} = $this->args[$value];
            }
        }

        $update->replace();
    }

    private function insert_db($ipid, $clientid)
    {

        $insert = new PatientCaseStatus();

        $insert->clientid = $clientid;
        $insert->ipid = $ipid;

        foreach ($this->editableFields as $value) {
            if (array_key_exists($value, $this->args)) {
                $insert->{$value} = $this->args[$value];
            }
        }

        $insert->save();

    }

    private function update_timestamps()
    {
        //join the date and the time
        $admdate = ($this->args['admdate'] != '') ? date('Y-m-d', strtotime($this->args['admdate'])) : "0000-00-00";
        $admtime = ($this->args['admtime'] != '') ? date('H:i:s', strtotime($this->args['admtime'])) : "00:00:00";
        $this->args['admdate'] = $admdate . ' ' . $admtime;

        $disdate = ($this->args['disdate'] != '') ? date('Y-m-d', strtotime($this->args['disdate'])) : "0000-00-00";
        $distime = ($this->args['distime'] != '') ? date('H:i:s', strtotime($this->args['distime'])) : "00:00:00";
        $this->args['disdate'] = $disdate . ' ' . $distime;
    }

    public function format_patientcase_for_select_option($case){
        $_input_format = "Fallnummer: %s, Fallart: %s,  %s - %s";
        $casenumber = $case['case_number'];
        $casetype = ($case['case_type']!= '') ? $this->case_types[$case['case_type']] : '';
        $admdate = strftime("%d.%m.%Y %H:%M", strtotime($case['admdate']));
        $disdate = ( $case['disdate'] != '0000-00-00 00:00:00') ? strftime("%d.%m.%Y %H:%M", strtotime($case['disdate'])) : '';
        return sprintf($_input_format, $casenumber, $casetype, $admdate, $disdate);
    }

    /**
     * returns the given cases with pretty formated string and case_id as array-keys
     * it reverses the given array. so the most recent case is on top
     */
    public function format_patientcases_for_select_option($cases, $add_default=null){
        $out=[];
        if(isset($add_default)){
            $out['']=$add_default;
        }
        foreach(array_reverse($cases) as $i=>$case){
            $out[$case['id']]=$this->format_patientcase_for_select_option($case);
        }
        return $out;
    }

   /**
     *  a clinic patient is only discharged, if all cases are closed.
     *
     * @param $ipid
     * @param $clientid
     */
    public function update_patient_master_for_clinic_case_status($ipid, $clientid ){

        $clinic_patientcases =$this->get_list_patient_status($ipid, $clientid, true);
        $opencases_count=count($clinic_patientcases);
        $isdischarged = ($opencases_count > 0) ? 0 : 1;

        $patientmaster=Doctrine::getTable('PatientMaster')->findOneByIpid($ipid);
        $patientmaster->isdischarged = $isdischarged;
        $patientmaster->save();
    }

    /**
     * render ops-overview-page
     *
     * @param $ipid patient-ipid
     * @param $caseid id in PatientCaseStatus Table
     * @param $quicklook triggers simpler output w/o js for mousover-popups
     * @param $quicklook triggers pdf-friendly html
     * @return string html
     */
    public static function get_opsoverview_k_html($ipid, $caseid=null, $quicklook=false, $pdf=false){
        $view = new Zend_View();
        $view->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $logininfo= new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $view->clientid = $clientid;
        $view->encid = Pms_Uuid::encrypt($decid);//$_GET['id'];
        $isadmin=0;
        if($logininfo->usertype=='SA' && $logininfo->showinfo!='show')
        {
            $isadmin =1;
        }
        $view->isadmin=$isadmin;
        if($pdf){
            $quicklook=true;
        }

        $view->quicklook=$quicklook;
        $view->pdf=$pdf;

        $cusers=User::getUsersWithGroupnameFast($clientid);
        $view->usermap=$cusers;

        $prm=new PatientCaseStatus();

        if(isset($caseid)){
            $caseid=intval($caseid);
        }else{
            $caseid="last";
        }
        $data = $prm->get_opsoverview($ipid,$caseid);
        $view->ops=$data;

        $cases=$prm->get_list_patient_status($ipid, $clientid);
        $view->cases=$cases;


        $view->config = ClientConfig::getConfig($clientid, 'opsconfig');

        $view->opsusermap=$prm->get_opsusermappings($view->config);

        $hllog= new PatientCaseStatusLog();
        $view->opslog=$hllog->getPatientCaseLogs($ipid, $view->ops['case']['id'], 'OPS');
        $view->timeslog=$hllog->getPatientCaseLogs($ipid, $view->ops['case']['id'], 'times');

        $view->usercansend=false;
        $navid = TabMenus::getMenubyLink("patient/patientops");
        $haspermission = PatientPermissions::verifyPermission($logininfo->userid, $ipid, $navid[0]['id'], 'canedit');
        if ($haspermission||$isadmin){
            $view->usercansend=1;
        }

        $html=$view->render('opsoverview_k_inner.html');
        return $html;
    }


    /**
     * create handy mappings for ops-relevant groups and categories from opsconfig
     */
    public static function get_inops_outops_cats($case_type, $opsconfig){
        $inops_opsgroups=[];
        $inops_cats=[
            'mins_patient'=>true,
            'mins_family'=>true,
            'mins_systemic'=>true,
            'mins_profi'=>true,
        ];
        foreach($opsconfig['groups'] as $group=>$members){
            $inops_opsgroups[$group]=true;
        }
        $prefcode=$opsconfig['ops_prefcode_'.$case_type];
        if(isset($prefcode) && strlen($prefcode)){
            foreach ($opsconfig['codes'] as $code){
                if ($code['name']==$prefcode){
                    if(!$code['ops_includet_patient']){$inops_cats['mins_patient']=false;}
                    if(!$code['ops_includet_angeh']){$inops_cats['mins_family']=false;}
                    if(!$code['ops_includet_sys']){$inops_cats['mins_systemic']=false;}
                    if(!$code['ops_includet_prof']){$inops_cats['mins_profi']=false;}

                    foreach($code['ignored_groups'] as $iggroup){
                        if(strlen($iggroup)){
                            $inops_opsgroups[$iggroup]=false;
                        }
                    }
                    break;
                }
            }
        }
        $out=['in_ops_groups'=>$inops_opsgroups, 'in_ops_cats'=>$inops_cats];
        return $out;
    }
    /**
     * calculate estimated times in clinic case for this patient
     * used in Reportsclinic::casestatus()
     */
    public static function get_opstimes_preview($ipid, $case_id, $inops_outops_cats, $usermappings){
        $contactFormsQ = Doctrine_Query::create()
            ->select('td.contact_form_id as cfid, u.*, td.*')
            ->from('FormBlockTimedocumentationClinicUser u')
            ->leftJoin("u.TimedocumentationClinic as td")
            ->where('td.ipid=?', $ipid)
            ->andWhere('td.patient_case_status_id=?',$case_id)
            ->andWhere("td.isdelete=false");
        $contactforms_r = $contactFormsQ->fetchArray();

        $sum=array('mins_ops'=>0, 'mins_noops'=>0, 'mins_all'=>0);
        $cats=['mins_patient','mins_family','mins_systemic','mins_profi', 'mins_systemisch'];
        foreach($contactforms_r as $form){
            $opsgroup=$usermappings[$form['userid']];
            if(!isset($sum[$opsgroup])){
                $sum[$opsgroup]=['mins_ops'=>0,'mins_noops'=>0];
                foreach($cats as $cat){
                    $sum[$opsgroup][$cat]=0;
                }
            }
            foreach($cats as $cat){
                $mins=intval($form[$cat]);
                $sum['mins_all']=$sum['mins_all']+$mins;
                $sum[$opsgroup][$cat]=$sum[$opsgroup][$cat]+$mins;
                $sum[$opsgroup]['mins_all']=$sum[$opsgroup]['mins_all']+$mins;
                if($inops_outops_cats['in_ops_cats'][$cat] && $inops_outops_cats['in_ops_groups'][$opsgroup]){
                    $sum[$opsgroup]['mins_ops']=$sum[$opsgroup]['mins_ops']+$mins;
                    $sum['mins_ops']=$sum['mins_ops']+$mins;
                }else{
                    $sum[$opsgroup]['mins_noops']=$sum[$opsgroup]['mins_noops']+$mins;
                    $sum['mins_noops']=$sum['mins_noops']+$mins;
                }
            }
        }

        // times of extraopsleistung , IM-136
        $extra_entries_Q = Doctrine_Query::create()
            ->select("*")
            ->from('Extraopsleistung')
            ->where('ipid ="' . $ipid . '"')
            ->andWhere('isdelete=0');
        $extra_entries = $extra_entries_Q->fetchArray();

        foreach ($extra_entries as $entry){

            $opsgroup = $entry['done_group'];
            if(!isset($sum[$opsgroup])){
                $sum[$opsgroup]=['mins_ops'=>0,'mins_noops'=>0];
                foreach($cats as $cat){
                    $sum[$opsgroup][$cat]=0;
                }
            }
            foreach($cats as $cat){
                $mins=intval($entry[$cat]);
                $sum['mins_all']=$sum['mins_all']+$mins;
                $sum[$opsgroup][$cat]=$sum[$opsgroup][$cat]+$mins;
                $sum[$opsgroup]['mins_all']=$sum[$opsgroup]['mins_all']+$mins;
                if($inops_outops_cats['in_ops_cats'][$cat] && $inops_outops_cats['in_ops_groups'][$opsgroup]){
                    $sum[$opsgroup]['mins_ops']=$sum[$opsgroup]['mins_ops']+$mins;
                    $sum['mins_ops']=$sum['mins_ops']+$mins;
                }else{
                    $sum[$opsgroup]['mins_noops']=$sum[$opsgroup]['mins_noops']+$mins;
                    $sum['mins_noops']=$sum['mins_noops']+$mins;
                }
            }
        }


        return $sum;
    }

    /**
     * calculates ops-relevant times from logged times
     * used in Reportsclinic::casestatus()
     */
    public static function get_opstimes_from_log($log, $inops_outops_cats){
        $sum=array('mins_ops'=>0, 'mins_noops'=>0, 'mins_all'=>0);
        $cats=['mins_patient','mins_family','mins_systemic','mins_profi'];
        foreach($log as $opsgroup=>$form){
            if(!isset($sum[$opsgroup])){
                $sum[$opsgroup]=['mins_ops'=>0,'mins_noops'=>0];
                foreach($cats as $cat){
                    $sum[$opsgroup][$cat]=0;
                }
            }
            foreach($cats as $cat){
                $mins=intval($form[$cat]);
                $sum['mins_all']=$sum['mins_all']+$mins;
                $sum[$opsgroup][$cat]=$sum[$opsgroup][$cat]+$mins;
                $sum[$opsgroup]['mins_all']=$sum[$opsgroup]['mins_all']+$mins;
                if($inops_outops_cats['in_ops_cats'][$cat] && $inops_outops_cats['in_ops_groups'][$opsgroup]){
                    $sum[$opsgroup]['mins_ops']=$sum[$opsgroup]['mins_ops']+$mins;
                    $sum['mins_ops']=$sum['mins_ops']+$mins;
                }else{
                    $sum[$opsgroup]['mins_noops']=$sum[$opsgroup]['mins_noops']+$mins;
                    $sum['mins_noops']=$sum['mins_noops']+$mins;
                }
            }
        }
        return $sum;
    }

    /**
     * fetch all ops-relevant times
     */
    public function get_opstimes($ipid){

        $opstimes = array();

        $user_to_opsgroup = $this->get_opsusermappings();

        $contactFormsQ = Doctrine_Query::create()
            ->select('td.contact_form_id as cfid, u.*, td.*')
            ->from('FormBlockTimedocumentationClinicUser u')
            ->leftJoin("u.TimedocumentationClinic as td")
            ->where('td.ipid=?', $ipid)
            ->andWhere("td.isdelete=false");

        $contactforms_r = $contactFormsQ->fetchArray();

        $contactformids=array_column($contactforms_r, 'cfid');

        $contactformids=array_unique($contactformids);
        if (count($contactformids) > 0) {

            $cfpdfQ = Doctrine_Query::create()
                ->select("id, recordid,
                AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
                AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name")
                ->from('PatientFileUpload')
                ->Where('tabname="contact_form"')
                ->andWhereIn("recordid", $contactformids);
            $pdfs = $cfpdfQ->fetchArray();
            $cfid_to_pdfinfo = array();

            foreach ($pdfs as $pdf) {
                $cfid_to_pdfinfo[$pdf['recordid']] = $pdf;
            }
        }

        foreach($contactforms_r as $f){
            if(!isset($opstimes[$f['TimedocumentationClinic']['contact_form_id']])){
                $form=array();
                $form['contact_form_id']=$f['TimedocumentationClinic']['contact_form_id'];
                $form['contact_form_date']=$f['TimedocumentationClinic']['contact_form_date'];
                $form['patient_case_status_id']=$f['TimedocumentationClinic']['patient_case_status_id'];
                $form['patient_case_type']=$f['TimedocumentationClinic']['patient_case_type'];
                $form['ipid']=$f['TimedocumentationClinic']['ipid'];
                $form['minutes']=$f['TimedocumentationClinic']['minutes'];
                $form['mins_patient']=$f['TimedocumentationClinic']['mins_patient'];
                $form['mins_family']=$f['TimedocumentationClinic']['mins_family'];
                $form['mins_systemic']=$f['TimedocumentationClinic']['mins_systemic'];
                $form['mins_profi']=$f['TimedocumentationClinic']['mins_profi'];
                $form['day']=date('Y-m-d',strtotime($form['contact_form_date']));
                $form['pdfinfo']=$cfid_to_pdfinfo[$form['contact_form_id']];
                $form['done_date']=$form['contact_form_date'];
                $opstimes[$f['TimedocumentationClinic']['contact_form_id']]=$form;
            }

            if(isset($opstimes[$f['TimedocumentationClinic']['contact_form_id']]['users'][$f['userid']])){
                $inner=$opstimes[$f['TimedocumentationClinic']['contact_form_id']]['users'][$f['userid']];
            }else{
                $inner=array();
                $inner['minutes']=0;
                $inner['mins_patient']=0;
                $inner['mins_family']=0;
                $inner['mins_systemic']=0;
                $inner['mins_profi']=0;

                $inner['groupname']=$f['groupname'];
                $inner['groupid']=$f['groupid'];
                $inner['username']=$f['username'];
                $inner['userid']=$f['userid'];
                $inner['opsgroup']=$user_to_opsgroup[$f['userid']];
                $inner['contact_form_date']=$f['contact_form_date'];
            }

            $inner['minutes']=intval($f['minutes'])+$inner['minutes'];
            $inner['mins_patient']=intval($f['mins_patient'])+$inner['mins_patient'];
            $inner['mins_family']=intval($f['mins_family'])+$inner['mins_family'];
            $inner['mins_systemic']=intval($f['mins_systemic'])+$inner['mins_systemic'];
            $inner['mins_profi']=intval($f['mins_profi'])+$inner['mins_profi'];

            $opstimes[$f['TimedocumentationClinic']['contact_form_id']]['users'][$f['userid']]=$inner;
        }
        unset($contactforms_r);


       // extraopsleistung , IM-136
       $extra_entries_Q = Doctrine_Query::create()
            ->select("*")
            ->from('Extraopsleistung')
            ->where('ipid ="' . $ipid . '"')
            ->andWhere('isdelete=0');
        $extra_entries = $extra_entries_Q->fetchArray();

        foreach ($extra_entries as $entry){
            $ddate = strftime("%Y-%m-%d",strtotime($entry['done_date']));
            $user=$entry['create_user'];
            $opsgroup = $user_to_opsgroup[$user];
            $users =[];

            $opstimes[]=array(
                'minutes'=>$entry['mins'],
                'day'=>$ddate,
                'done_date'=> $entry['done_date'],
                'type'=> $entry['done_group'],
                'profs'=>$opsgroup,
                'ipid' => $ipid,
                'caseid' => $entry['caseid'],
                'mins_patient'=> $entry['mins_patient'],
                'mins_angehoerige'=> $entry['mins_angehoerige'],
                'mins_systemisch'=>$entry['mins_systemisch'],
                'mins_profi'=>$entry['mins_profi'],
                'mins_rest'=> 0,
                'is_extraopsleistung' => 'Externe Leistungen',
                'patient_case_type' => 'station',
                'patient_case_status_id' => $entry['caseid'],
                'course_type'=>$entry['done_group'],
                'create_date'=>$entry['create_date'],
                'users' => array(array(
                    'mins_patient'=> $entry['mins_patient'],
                    'mins_angehoerige'=> $entry['mins_angehoerige'],
                    'mins_systemisch'=>$entry['mins_systemisch'],
                    'mins_profi'=>$entry['mins_profi'],
                    'id' => $entry['create_user'],
                    'course_type'=>$entry['done_group'],
                    'name' => $entry['done_name'],
                    'group' => $entry['done_group']
                ))
            );

        }

        return $opstimes;
    }


    /**
     * generate case-info for ops overview
     */
    public function get_opsoverview($ipid, $k_case){
        $logininfo = new Zend_Session_Namespace('Login_Info');

        $opsconfig=ClientConfig::getConfig($logininfo->clientid, 'opsconfig');

        $userinfo=User::getUsersWithGroupnameFast($logininfo->clientid);

        //$cases=$this->get_cases($ipid);
        $cases=$this->get_list_patient_status($ipid, $logininfo->clientid);
        $opstimes = $this->get_opstimes($ipid);

        $opstimes_by_day=array();

        $startday="90901212";
        $endday=0;

        foreach ($opstimes as $opstime){
            $opstimes_by_day[$opstime['day']][]=$opstime;
        }
//echo 'opstimes by day';
        //print_r($opstimes_by_day);echo '<hr>';
        //fetch ba-entries
        $ba_form_id = $opsconfig['ba_formid'];

        $ba_query = Doctrine_Query::create()
            ->select('id, date')
            ->from('ContactForms t')
            ->where('t.ipid = ?',$ipid)
            ->andWhere('t.form_type = ?',$ba_form_id);
        $ba_days=$ba_query->fetchArray();
        $ba_ids=array();
        foreach ($ba_days as $ba){
            $ba_ids[]=$ba['id'];
        }


        foreach ($cases as $cno=>$case){
            $from_klau=false;
            if ($case['case_type'] == "station"){
                $pflegerisch    =$opsconfig['ops_oe_station_anf1'];
                $fachlich       =$opsconfig['ops_oe_station_anf2'];
                $e_oe           =$opsconfig['ops_oe_station_erb1'];
                $e_oe2          =$opsconfig['ops_oe_station_erb2'];
                if($opsconfig['ops_oe_station_klau']==1){
                    $from_klau=true;
                }
            }
            if ($case['case_type'] == "konsil"){
                $pflegerisch    =$opsconfig['ops_oe_konsil_anf1'];
                $fachlich       =$opsconfig['ops_oe_konsil_anf2'];
                $e_oe           =$opsconfig['ops_oe_konsil_erb1'];
                $e_oe2          =$opsconfig['ops_oe_konsil_erb2'];
                if($opsconfig['ops_oe_konsil_klau']==1){
                    $from_klau=true;
                }
            }
            if ($case['case_type'] == "ambulant"){
                $pflegerisch    =$opsconfig['ops_oe_ambulant_anf1'];
                $fachlich       =$opsconfig['ops_oe_ambulant_anf2'];
                $e_oe           =$opsconfig['ops_oe_ambulant_erb1'];
                $e_oe2          =$opsconfig['ops_oe_ambulant_erb2'];
                if($opsconfig['ops_oe_ambulant_klau']==1){
                    $from_klau=true;
                }
            }
            if ($case['case_type'] == "sapv"){
                $pflegerisch    =$opsconfig['ops_oe_sapv_anf1'];
                $fachlich       =$opsconfig['ops_oe_sapv_anf2'];
                $e_oe           =$opsconfig['ops_oe_sapv_erb1'];
                $e_oe2          =$opsconfig['ops_oe_sapv_erb2'];
                if($opsconfig['ops_oe_sapv_klau']==1){
                    $from_klau=true;
                }
            }

/*todo lmu_konsilauftrag
            if ($from_klau)
            {
                $oe_query = Doctrine_Query::create()
                    ->select('l.pv1_location1 as l1, l.pv1_location2 as l2')
                    ->from('LmuKonsilauftrag l')
                    ->where('l.ipid = ?',$ipid)
                    ->andWhere('l.pv1_visitnumber= ?',$case['case_no'])
                    ->limit('1')
                    ->orderBy('l.id DESC');
                $oe_arr=$oe_query->fetchArray();
                if($oe_arr) {
                    $pflegerisch = $oe_arr[0]['l1'];
                    $fachlich = $oe_arr[0]['l2'];
                }
            }
*/
            $cases[$cno]['disdate_virtual'] = date("Y-m-d H:i:s",strtotime($case['disdate']));
            $cases[$cno]['startday'] = date("Y-m-d",strtotime($case['admdate']));
            $cases[$cno]['endday'] = date("Y-m-d",strtotime($case['disdate']));
            if($cases[$cno]['endday'] < "2000-01-01"){
                $cases[$cno]['endday'] = date("Y-m-d");
                $cases[$cno]['disdate_virtual'] = date("Y-m-d H:i:s");
            }
            $cases[$cno]['e_oe'] = $e_oe;
            $cases[$cno]['e_oe2'] = $e_oe2;
            $cases[$cno]['pflegerisch'] = $pflegerisch;
            $cases[$cno]['fachlich'] = $fachlich;
            $cases[$cno]['case_finished'] = $case['case_finished'];

            if($cases[$cno]['startday']<$startday) $startday=$cases[$cno]['startday'];
            if($cases[$cno]['endday']>$endday) $endday=$cases[$cno]['endday'];

        }

        if($k_case==="last"){
            $lastcase=array();
            foreach ($cases as $case){
                $last=0;
                if (count($lastcase)>0){
                    $last=$lastcase['admdate'];
                }
                if($last<$case['admdate']){
                    $lastcase=$case;
                }
            }
            $k_case=$lastcase;
        }else{
            foreach ($cases as $case){
                if($case['id']==$k_case){
                    $k_case=$case;
                    break;
                }
            }
        }

        //generate days_list
        $d_end=date_create($k_case['endday']);
        $d_start=date_create($k_case['startday']);
        $interval = date_diff($d_start, $d_end);
        $kcase['number_of_days']=$interval->format("%a");

        $days=array();
        for($i=0; $i<=$kcase['number_of_days']; $i++){
            $days[]=['day'=>$d_start->format('Y-m-d')];
            $d_start->add(new DateInterval('P1D'));
        }

        //Some Infos about the Case. Week-Starts, Case-Start, etc.
        $info_days=array();
        $info_days[$k_case['startday']][]=['type'=>'caseinfo', 'title'=>'Aufnahme', 'done_date'=>$k_case['admdate']];
        if($k_case['disdate']>$k_case['admdate']){
            $info_days[$k_case['endday']][]=['type'=>'caseinfo', 'title'=>'Entlassung', 'done_date'=>$k_case['disdate']];
        }
        if($k_case['startday'] > $k_case['enddday']){
            $d_end=date_create($k_case['disdate_virtual']);
            $d_start=date_create($k_case['admdate']);
            $d_start->add(new DateInterval('P7D'));
            $weekcount=1;
            while($d_start<$d_end){
                $daytime=$d_start->format('Y-m-d');
                $info_days[$daytime][]=['type'=>'caseinfo', 'title'=>'Woche '.$weekcount.' abgeschlossen.', 'done_date'=>$d_start->format('Y-m-d 00:00:00')];
                $d_start->add(new DateInterval('P7D'));
                $weekcount++;
            }
        }

        $bpdays=$this->get_behandlungsplane($ipid);

        //START get WeeklyMeeting Days
        $oe_query = Doctrine_Query::create()
            ->select('id, date')
            ->from('WeeklyMeeting t')
            ->where('t.ipid = ?',$ipid)
            ->andWhere('t.week>0')
            ->andWhere('finished=1')
            ->andWhere('isdelete=0');
        $tm_days=$oe_query->fetchArray();

        $tm_ids=array();
        foreach ($tm_days as $tm){
            $tm_ids[]=$tm['id'];
        }

        $tmf_query = Doctrine_Query::create()
            ->select('recordid')
            ->from('PatientFileUpload t')
            ->where('t.ipid = ?',$ipid)
            ->andWhere('t.tabname="weekly_teammeeting"')
            ->andWhereIn('t.recordid', $tm_ids)
            ->andWhere('t.isdeleted=0');
        $tmfiles=$tmf_query->fetchArray();

        $tm_ids_to_files=array();
        foreach ($tmfiles as $tm){
            $tm_ids_to_files[$tm['recordid']]=$tm['id'];
        }

        $oe_query = Doctrine_Query::create()
            ->select('user, meeting')
            ->from('WeeklyMeetingusers t')
            ->whereIn('t.meeting',$tm_ids)
            ->andwhere('isdelete=0');
        $tm_users=$oe_query->fetchArray();

        $tmid_to_users=array();

        foreach($tm_users as $tmuser){
            $tmid_to_users[$tmuser['meeting']][]=$tmuser['user'];
        }

        foreach($tm_days as $tm){
            $pdfinfo=array('id'=>$tm_ids_to_files[$tm['id']], 'title'=>'Wöchentliche multiprofessionelle Teambesprechung');
            $day=date('Y-m-d',strtotime($tm['date']));
            $users=array();
            foreach ($tmid_to_users[$tm['id']] as $uid){
                $users[$uid]=$userinfo[$uid];
            }
            $tmdays[$day]=array('id'=>$tm['id'], 'day'=>$day, 'users'=>$users,'pdfinfo'=>$pdfinfo, 'done_date'=>$tm['date'], 'is_weekly_meeting'=>true);
        }

        $output=array('case'=>$k_case,'docs'=>array());
        $output['case']['number_of_days']=0;
        foreach($days as $day){
            if($day['day']>=$k_case['startday'] && $day['day']<=$k_case['endday'] ){
                $output['case']['number_of_days']++;
                if(isset($info_days[$day['day']])){
                    foreach ($info_days[$day['day']] as $item) {
                        if($item['title']!=="Entlassung") {
                            $output['docs'][] = $item;
                        }
                    }
                }
                if(isset($tmdays[$day['day']])){
                    $output['docs'][]=$tmdays[$day['day']];
                }
                //@todo bpdays
                if(isset($bpdays[$day['day']])){
                    $output['docs'][]=$bpdays[$day['day']];
                }
                foreach($opstimes_by_day[$day['day']] as $opsdetail){
                    if($opsdetail['patient_case_status_id'] == $k_case['id']) {

                        if(in_array($opsdetail['contact_form_id'], $ba_ids)){
                            $opsdetail['is_ba']=true;
                        }
                        $output['docs'][] = $opsdetail;
                    }
                }

                if(isset($info_days[$day['day']])){
                    foreach ($info_days[$day['day']] as $item) {
                        if($item['title']==="Entlassung") {
                            $output['docs'][] = $item;
                        }
                    }
                }
            }
        }
        return $output;
    }


    /**
     * @param $opsconfig optional ClientConfig::getConfig($logininfo->clientid, 'opsconfig')
     * @return array map of userid to ops_group
     */
    public static function get_opsusermappings($opsconfig=null){
        $logininfo= new Zend_Session_Namespace('Login_Info');
        if(!isset($opsconfig)) {
            $opsconfig = ClientConfig::getConfig($logininfo->clientid, 'opsconfig');
        }

        $usergroupid_to_opsgroup=array();
        foreach ($opsconfig['groups'] as $opsgroup=>$members){
            foreach ($members as $member){
                if($member>0) {
                    $usergroupid_to_opsgroup[$member] = $opsgroup;
                }
            }
        }
        foreach ($opsconfig['groupscustom'] as $opsgroup=>$members){
            foreach ($members as $member){
                if($member>0) {
                    $usergroupid_to_opsgroup[$member] = $opsgroup;
                }
            }
        }
        $UsersQ = Doctrine_Query::create()
            ->select('u.id, u.last_name, u.first_name, u.groupid')
            ->from('User u')
            ->where("clientid=?", $logininfo->clientid);
        $clientusers = $UsersQ->fetchArray();

        $user_to_opsgroup = array();
        foreach ($clientusers as $user) {
            $user_to_opsgroup[$user['id']] = $usergroupid_to_opsgroup[$user['groupid']];
        }

        return $user_to_opsgroup;
    }


    public function get_behandlungsplane($ipid){
        $bp_query = Doctrine_Query::create()
            ->select('id, recordid, create_user')
            ->from('PatientFileUpload t')
            ->where('t.ipid = ?',$ipid)
            ->andWhere('t.tabname="pmba_behandlungsplan"');
        $bpfiles=$bp_query->fetchArray();

        $cfids=array_column($bpfiles,'recordid');
        $file_ids = array_column($bpfiles, 'id');

        $course_entries_Q = Doctrine_Query::create()
            ->select("id, recordid, done_date")
            ->from('PatientCourse')
            ->where('ipid ="' . $ipid . '"')
            ->andWhere("tabname=?",Pms_CommonData::aesEncrypt('fileupload2'))
            ->andWhereIn('recordid', $file_ids)
            ->andWhere('wrong=0');
        $course_entries = $course_entries_Q->fetchArray();

        $file_id_to_course_done_date=array();
        foreach ($course_entries as $ce){
            $file_id_to_course_done_date[$ce['recordid']] = $ce['done_date'];
        }


        $cf_query = Doctrine_Query::create()
            ->select('id, date')
            ->from('ContactForms t')
            ->where('t.ipid = ?',$ipid)
            ->andWhereIn('t.id',$cfids);
        $cf_days=$cf_query->fetchArray();

        $cfid_to_date=array_combine(array_column($cf_days,'id'),array_column($cf_days,'date'));

        $output=array();

        foreach($bpfiles as $bp){
            if($bp['recordid']>0) {
                $a = array();
                $a['day'] = date('Y-m-d', strtotime($cfid_to_date[$bp['recordid']]));
                $a['done_date'] = $cfid_to_date[$bp['recordid']];
                $a['pdfinfo'] = array('title' => 'Behandlungsplan', 'id' => $bp['id']);
                $a['allusers'] = array($bp['create_user']);
                $output[$a['day']] = $a;
            }else{
                $a = array();
                $a['day'] = date('Y-m-d', strtotime($file_id_to_course_done_date[$bp['id']]));
                $a['done_date'] = $file_id_to_course_done_date[$bp['id']];
                $a['pdfinfo'] = array('title' => 'Behandlungsplan', 'id' => $bp['id']);
                $a['allusers'] = array($bp['create_user']);
                $output[$a['day']] = $a;
            }
        }

        return $output;
    }

    public function patientswitcherclinicheaderAction($ipid, $fallart)  {

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $status =  Client::getClientconfig($clientid, 'patientquicknavbar_lists');

        //there is no $fallart in parameter
        if($fallart == 'null'){
            $user=Doctrine::getTable('User')->findOneBy('id',$logininfo->userid);
            if(!$user->preferred_clinic_list == '') // get the preferred user list, if exist
                $fallart = $user->preferred_clinic_list;
            elseif (count($status) > 0) // get the first config entry, if exist
                $fallart = array_keys($status)[0];
            else                        //get the deffault vALUE
                $fallart = 'station';
        }
        /* ---------  get user's patients by permission   ---------------------------- */
        $user_patients = PatientUsers::getUserPatients($logininfo->userid);

        $hidemagic = Zend_Registry::get('hidemagic');

        $sql = "p.id,p.ipid,e.epid,";
        $sql .= "AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name,";
        $sql .= "AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";

        // if super admin check if patient is visible or not
        if($logininfo->usertype == 'SA')
        {
            $sql = "p.id,p.ipid,e.epid,";
            $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
            $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
        }

        $patient = Doctrine_Query::create()
            ->select($sql)
            ->from('PatientMaster p')
            ->where('p.isdelete = 0')
            ->andWhere('p.ipid IN (' . $user_patients['patients_str'] . ')')
            ->andWhere('p.isdelete = 0')
            ->andWhere('p.isstandbydelete=0')
            ->andWhere('p.isstandby = 0 ')
            ->andWhere('p.isdischarged = 0')
            ->andWhere('p.isarchived = 0');
        $patient->leftJoin("p.EpidIpidMapping e");
        $patient->andWhere('e.clientid = ' . $logininfo->clientid);
        $patient->orderBy('TRIM(CONVERT(CONVERT(AES_DECRYPT(p.last_name, "' . Zend_Registry::get('salt') . '") using utf8) using latin1)) COLLATE latin1_german2_ci ASC');
        $patienidt_array = $patient->fetchArray();

        PatientMaster::beautifyName($patienidt_array);


        /* ---------  get patients by case status   ---------------------------- */
        $list_status = $this->get_list_patients_by_status($clientid, $fallart, true);
        $ipids_by_status = array_column($list_status, 'ipid');

        $erg = array();
        $erg['act_pat_encid'] ='';
        $erg['act_nice_name'] ='';
        $erg['act_nice_name_epid'] ='';

        $pats = array();

         foreach ($patienidt_array as $pat){

            if(!in_array($pat['ipid'], $ipids_by_status))
                continue;

            $enc_id = Pms_Uuid::encrypt($pat['id']);

            if($ipid == $pat['ipid']){
                $erg['act_pat_encid'] = $enc_id;
                $erg['act_nice_name'] = $pat['nice_name'];
                $erg['act_nice_name_epid'] = $pat['nice_name_epid'];

            }

            $epid = $pat['EpidIpidMapping']['epid'];
            $pats[]=array(
                    'nice_name'=>$pat['nice_name'],
                    'nice_name_epid'=>$pat['nice_name_epid'],
                    'enc_id'=>$enc_id
                );
        }

        $erg['pats'] = $pats;

         $stats = array();

         foreach ($status as $key => $value){
             $stats[] = array(
                 'value' => $key,
                 'name' => $value,
             );
         }

        $erg['stats'] = $stats;
        $erg['act_status'] = $fallart;

        return $erg;
    }
    
    public static function name_to_color($name)
    {
        $name = strtolower($name);

        if ($name == "station") {
            return "#ffa07a";
        }
        if ($name == "konsil") {
            return "#90ee90";
        }
        if ($name == "ambulant") {
            return "#add8e6";
        }
    }


    //ISPC-2807 Lore 25.02.2021
    public function save_patient_case_status_toVerlauf($ipid, $data)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $userid = $logininfo->userid;
        $tr = new Zend_View_Helper_Translate();
        
        $model = new PatientCaseStatus();
        $olddatesarr = $model->get_list_patient_status($ipid);
        
        $box_name = $tr->translate("[PatientCaseStatus Box Name]");
        
        $case_types = array('keineauswahl' => '', 'station' => 'Station', 'konsil' => 'Palliativ-Dienst', 'ambulant' => 'Ambulant', 'sapv' => 'SAPV', 'standby' => 'Warteliste');
        
        $olddate_arr = array();
        foreach($olddatesarr as $k_o => $k_vals){
            $olddate_arr[$k_vals['id']] = $k_vals;
        }
        
        foreach($data as $key => $vals){
            //dd($data);
            $course_title = '';
            if($vals['id'] == '0'){
                $course_title .= "Der ".$tr->translate("case_type")." das ".$box_name." wurde hinzugefügt: ".$case_types[$vals['case_type']].', '.$tr->translate("case_number").' '.$vals['case_number'] ."\n\r" ;
            } else {
                if($vals['case_type'] != $olddate_arr[$vals['id']]['case_type']){
                    $course_title .= "Der ".$tr->translate("case_type")." das ".$box_name." wurde geändert: ".$case_types[$vals['case_type']].' '.$case_types[$olddate_arr[$vals['id']]['case_type']].' -> '.$case_types[$vals['case_type']]."\n\r" ;
                }
                if($vals['case_number'] != $olddate_arr[$vals['id']]['case_number']){
                    $course_title .= "Der ".$tr->translate("case_type").': '.$case_types[$vals['case_type']]." das ".$box_name." wurde geändert: ".$tr->translate("case_number").' '.$olddate_arr[$vals['id']]['case_number'].' -> '.$vals['case_number']."\n\r" ;
                }
                if(!empty($vals['admdate'])){
                    $new_admdate = date("Y-m-d", strtotime($vals['admdate'])).' '.$vals['admtime'].':00';
                    if($new_admdate != $olddate_arr[$vals['id']]['admdate']){
                        $course_title .= "Der ".$tr->translate("case_type").': '.$case_types[$vals['case_type']]." das ".$box_name." wurde geändert: ".$tr->translate("start").' '.date("d.m.Y H:i", strtotime($olddate_arr[$vals['id']]['admdate'])).' -> '.$vals['admdate'].' '.$vals['admtime']."\n\r" ;
                    }
                }
                
                if(!empty($vals['disdate'])){
                    $new_disdate = date("Y-m-d", strtotime($vals['disdate'])).' '.$vals['distime'].':00';
                    if($new_disdate != $olddate_arr[$vals['id']]['disdate'] && $olddate_arr[$vals['id']]['disdate'] != '0000-00-00 00:00:00'){
                        $course_title .= "Der ".$tr->translate("case_type").': '.$case_types[$vals['case_type']]." das ".$box_name." wurde geändert: ".$tr->translate("end").' '.date("d.m.Y H:i", strtotime($olddate_arr[$vals['id']]['disdate'])).' -> '.$vals['disdate'].' '.$vals['distime']."\n\r" ;
                    }
                    if($new_disdate != $olddate_arr[$vals['id']]['disdate'] && $olddate_arr[$vals['id']]['disdate'] == '0000-00-00 00:00:00'){
                        $course_title .= "Der ".$tr->translate("case_type").': '.$case_types[$vals['case_type']]." das ".$box_name." wurde geändert: ".$tr->translate("end").' -> '.$vals['disdate'].' '.$vals['distime']."\n\r" ;
                    }
                }
                
            }
            
            $recordid = $vals['id'];
            if(!empty($course_title)){
                $insert_pc = new PatientCourse();
                $insert_pc->ipid =  $ipid;
                $insert_pc->course_date = date("Y-m-d H:i:s", time());
                $insert_pc->course_type = Pms_CommonData::aesEncrypt("K");
                $insert_pc->recordid = $recordid;
                $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($course_title));
                $insert_pc->user_id = $userid;
                $insert_pc->save();
            }

            
        }
                  

        
    }
    
}

?>