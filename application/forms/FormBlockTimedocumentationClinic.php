<?php
/**
 * Maria:: Migration CISPC to ISPC 22.07.2020
 * Maria:: Migration CISPC to ISPC 20.08.2020
 * TODO-4163 whole file
 */
require_once("Pms/Form.php");

class Application_Form_FormBlockTimedocumentationClinic extends Pms_Form
{


    public function create_form_timedocumentationclinic($options = array(), $elementsBelongTo = null, $ipid = null)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $this->view->pid = Pms_Uuid::encrypt($decid);
        $ipid = Pms_CommonData::getIpid($decid);

        if (isset($options['formular_type'])) {// use the post ones, maybe this is just a print or a submit
            $timedocumentation = $_POST['timedocumentation'];
        }
        // update an existing contactform => loaded old values by ContactFormId in PatientController
        elseif (isset($options['id'])) {
            $timedocumentation = $options;
            $model = new FormBlockTimedocumentationClinicUser();
            $timedocumentation['timelog'] = $model->get_list_user($timedocumentation['id']);
        }

        //get the PatienCases
        $model = new PatientCaseStatus();
        $patientcases = $model->get_list_patient_status($ipid, $clientid);
        $case_types = $model->case_types;

        //create the pdf-Layout and return
        if ($options['formular_type'] == 'pdf') {
            $select_patientcase = $this->get_selected_patientcase($patientcases);
            return $this->create_pdf_careprocessclinic($timedocumentation, $select_patientcase, $case_types);
        }

        //get all Client-User
        $active_client_user = User::getUsersWithGroupnameFast($clientid, true);
        unset($active_client_user[1]);

        $user_select = array();
        $user_active = array();
        //build short arrays only with the necessary values
        foreach($active_client_user as $key=>$value){
            $user_select[$key] = $value['nice_name'];
            $user_active[$key] = array('groupid' => $value['groupid'], 'group' => $value['group']);
        }

        $user_keys=array_keys($user_select);
        $array_lowercase = array_map('strtolower', array_values($user_select));
        array_multisort(
            array_values($array_lowercase), SORT_ASC, SORT_STRING,
            $user_select,
            $user_active,
            $user_keys

        );
        $user_select=array_combine($user_keys, $user_select);
        $user_active=array_combine($user_keys, $user_active);

        $formular_cases = array();
        $selected='0';

        if (!empty($patientcases)) {
            foreach ($patientcases as $key=>$case) {
                $formular_cases[$case['id']] = $model->format_patientcase_for_select_option($case);
            }
            //preselection of the last element. the array is ordered by admdate by default
            $selected = end($patientcases)['id'];
        }

        //if this is an update, use the stored value
        if(isset($timedocumentation['patient_case_status_id'])) {
            $selected = $timedocumentation['patient_case_status_id'];
        }

        //add the option = 'please select' to our user array
        $formular_cases = array('0' => $this->translate('time_documentation_clinic_no_select')) + $formular_cases;


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn


        $this->mapValidateFunction($__fnName, "create_form_isValid");
        $this->mapSaveFunction($__fnName, "save_form_infusiontimes");

        $this->add_javascript(json_encode($user_select));//ISPC-2926,Elena,17.05.2021

        $timelogform = $this->subFormContactformBlock($this->getFnOptions($__fnName)); //timelog-logtable
        $timelogform->setLegend('time_documentation_clinic');
        $timelogform->setAttrib("class", "label_same_size_auto  contact_timedocumentationclinic timelog-table timelog-logtable");

        $timelogform_sum = new Zend_Form_SubForm();
        $timelogform_sum->setDecorators(array('FormElements'));
        $timelogform_sum->setAttrib("class", "timelog-sumrow");
        $this->__setElementsBelongTo($timelogform_sum, 'timedocumentation');

        $patientcase = new Zend_Form_SubForm(); //timelog-patientcasetable
        $patientcase->setDecorators(array('FormElements'));
        $patientcase->setAttrib("class", "label_same_size_auto contact_timedocumentationclinic timelog-table timelog-patientcasetable");

        $timedocumentationform = new Zend_Form_SubForm(); //timelog-addtable
        $timedocumentationform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
        $timedocumentationform->setDecorators(array('FormElements', array('SimpleTable')));
        $timedocumentationform->setAttrib("class", "label_same_size_auto contact_timedocumentationclinic timelog-table timelog-addtable");

        $this->add_patient_case_select($patientcase, $formular_cases, $selected);
        $timelogform->addSubForm($patientcase, 'patientcase');

        $this->add_timelog_headrow($timelogform);

        foreach ($timedocumentation['timelog'] as $key => $value) {
            $this->add_timelog_logrow($key, $value, $timelogform);
        }

        $this->add_timelog_sumrow($timedocumentation, $timelogform_sum);
        $this->add_timelog_addrow($logininfo->userid, $user_select, $timedocumentationform, $user_active);

        $timelogform->addSubForm($timelogform_sum, 'timedocumentation');
        $timelogform->addSubForm($timedocumentationform, 'timedocumentation2');


        return $this->filter_by_block_name($timelogform, __FUNCTION__);
    }

    public function save_form_timedocumentationclinic($ipid = null, $data = array())
    {
        if (empty($ipid) || empty($data) || !in_array('time_documentation_clinic', $data['__formular']['allowed_blocks'])) {
            return;
        }
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $this->view->pid = Pms_Uuid::encrypt($decid);
        $ipid = Pms_CommonData::getIpid($decid);

        $model = new FormBlockTimedocumentationClinic();
        $old_contact_formmid = $data['__formular']['old_contact_form_id'];

        //set the old entry to delete=1
        if ($old_contact_formmid > 0)
            $model->delete_timedocumentation($old_contact_formmid, $ipid);

        //create the new entry
        $contact_form_id = $data['contact_form_id'];
        $timedocumentation = $_POST['timedocumentation'];
        $timedocumentation['contact_form_date'] = date('Y-m-d H:i:s', strtotime($_POST['date'] . ' ' . $_POST['begin_date_h'] . ':' . $_POST['begin_date_m'] . ':00'));

        //add the patient-case
        $patient_case_id = $_POST['patientcase']['select_patient_case'];
        if($patient_case_id != '0'){
            $timedocumentation['patient_case_status_id'] = $patient_case_id;
            $modelPatientCase = new PatientCaseStatus();
            $patientcase = $modelPatientCase->get_patient_status($patient_case_id);
            if(isset($patientcase)){
                $timedocumentation['patient_case_type'] = $patientcase['case_type'];
            }

        }

        $model->create_timedocumentation($ipid, $contact_form_id, $timedocumentation);

        ######################################################


        //TODO-3843 Ancuta 11.02.2021
        // Check if bock is allowed to add to verlauf  - recoreddata - to F
        // Updated on 24.03.2021 Nico - The whole Table was to big. Only sum-row and users
        $block = 'time_documentation_clinic';
        if( ! empty($timedocumentation) && ! empty($data['__formular']['blocks2recorddata'])  && array_key_exists($block,$data['__formular']['blocks2recorddata']) && $data['__formular']['blocks2recorddata'][$block]['allow'] == '1'){

            $html_to_course = "<b>Zeitdokumentation</b> <br/>";

            if(isset($timedocumentation['patient_case_type'])){
                $casetype=$timedocumentation['patient_case_type'];
                $color=PatientCaseStatus::name_to_color($casetype);
                $html_to_course .= '<div>Fallart: <span style="background-color:'.$color.';">' . ucfirst($casetype) . '</span></div>';
            }

            /*

                        $users_lines = 0;
                        foreach($timedocumentation as $key_td=>$vals_td){
                            foreach($vals_td as $key=>$vals){
                                $users_lines++;
                                $html_to_course .= "<tr>";
                                $html_to_course .= "<td>". $vals['date'] ."</td>";
                                $html_to_course .= "<td>". $vals['username']."</td>";
                                $html_to_course .= "<td>". $vals['mins_patient']."</td>";
                                $html_to_course .= "<td>". $vals['mins_family']."</td>";
                                $html_to_course .= "<td>". $vals['mins_systemic']."</td>";
                                $html_to_course .= "<td>". $vals['mins_profi']."</td>";
                                $html_to_course .= "<td>". $vals['minutes'] ."</td>";
                                $html_to_course .= "</tr>";
                            }
                        }*/

            $users=[];
            foreach($timedocumentation as $key_td=>$vals_td){
                foreach($vals_td as $key=>$vals) {
                    $users[]=$vals['username'];
                }
            }
            $users=array_unique($users);
            if(count($users) > 0 ){

                $html_to_course .= '<div>Beteiligte: ' . implode(', ',$users) . '</div>';

                $html_to_course .= '<table  class="datatable_pc">';
                $html_to_course .= "<tr>";
                $html_to_course .= "<th>" .$this->translate('time_documentation_clinic_patient') ."</th>";
                $html_to_course .= "<th>" .$this->translate('time_documentation_clinic_family') ."</th>";
                $html_to_course .= "<th>" .$this->translate('time_documentation_clinic_systemic') ."</th>";
                $html_to_course .= "<th>" .$this->translate('time_documentation_clinic_professional') ."</th>";
                $html_to_course .= "<th>" .$this->translate('time_documentation_clinic_sum') ."</th>";
                $html_to_course .= "</tr>";

                $html_to_course .= "<tr>";
                $html_to_course .= "<td>". $timedocumentation['mins_patient'] ."</td>";
                $html_to_course .= "<td>". $timedocumentation['mins_family'] ."</td>";
                $html_to_course .= "<td>". $timedocumentation['mins_systemisch'] ."</td>";
                $html_to_course .= "<td>". $timedocumentation['mins_profi'] ."</td>";
                $html_to_course .= "<td>". $timedocumentation['minutes'] ."</td>";
                $html_to_course .= "</tr>";
                $html_to_course .= "</table>";

                $record_color = (!empty($data['__formular']['blocks2recorddata'][$block]['color'])) ? $data['__formular']['blocks2recorddata'][$block]['color'] : "#000000";

                $coursetext_rcd = '<br/><div class="rcd_FormBlockTimedocumentationClinic pc_record_data" style="color:'.$record_color.'!important">';
                $coursetext_rcd .= $html_to_course;
                $coursetext_rcd .= '</div>';


                $cust=Doctrine::getTable('PatientCourse')->findOneByRecordidAndTabname($data['__formular']['contact_form_id'], Pms_CommonData::aesEncrypt("contact_form"));
                $cust->recorddata=$cust->recorddata . $coursetext_rcd;
                $cust->save();
            }
        }
        // --


        //TODO-4069 Ancuta 26.04.2021
        $modules = new Modules();
        $companion_time_tracking = 0;
        if($modules->checkModulePrivileges("254", $clientid))
        {
            $companion_time_tracking = 1;
        }

        if($companion_time_tracking == 1) {
            foreach($timedocumentation['timelog'] as $k=>$log_row){
                $_POST['additional_users'][$log_row['userid']]['value'] = '1';
                if($log_row['userid'] == $logininfo->userid){
                    $_POST['additional_users'][$log_row['userid']]['creator'] = '1';
                }
            }

            $additional_users_block = new Application_Form_FormBlockAdditionalUsers();

            $allowed_blocks = array('additional_users');
            $result_additional_users = $additional_users_block->InsertData($_POST, $allowed_blocks);
        }

        //--


    }

    private function add_patient_case_select($subform, $patient_cases, $selected){

        $subform->addElement('note', 'note_patient_case', array(
            'value' => $this->translate('time_documentation_clinic_select_case'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'h4',
                    'style' => 'padding-top: 5px;padding-bottom: 5px;',
                )),
            )));

        $entries=[];
        if(count($patient_cases)) {
            foreach ($patient_cases as $caseid=>$case) {

                if(strpos(strtolower($case), 'station')){
                    $color=PatientCaseStatus::name_to_color('station');
                }
                if(strpos(strtolower($case), 'dienst')){
                    $color=PatientCaseStatus::name_to_color('konsil');
                }
                if(strpos(strtolower($case), 'ambulant')){
                    $color=PatientCaseStatus::name_to_color('ambulant');
                }
                if(strlen($color)<1){
                    $color="white;";
                }
                $sel="";
                if($caseid==$selected || (count($patient_cases) && $caseid>0)){
                    $sel=" selected";
                }
                $style=" data-casecolor='".$color."' ";
                $entries[]="<option value='".$caseid."' ".$style.$sel.">" . $case . "</option>";
            }
        }

        $html="<select name='patientcase[select_patient_case]' id='patientcase-select_patient_case'>" . implode("\n",$entries) . "</select>";

        $subform->addElement('note', 'doesntmatter', array(
            'value' => $html,
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'div',
                    'style' => 'padding-bottom: 20px;',
                )),
            ),
        ));
//       $subform->addElement('select', 'select_patient_case', array(
//            'multiOptions' => $patient_cases,
//            'value' => $selected,
//            'decorators' => array(
//                'ViewHelper',
//                array(array('data' => 'HtmlTag'), array(
//                    'tag' => 'div',
//                    'style' => 'padding-bottom: 20px;',
//                )),
//            )));
    }

    private function add_timelog_headrow($subform, $pdf=false)
    {

        $subform->addElement('note', 'header_date', array(
            'value' => $this->translate('time_documentation_clinic_date'),
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'th',
                    'style' => 'width:12%;',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'class' => 'timelog-headrow',
                    'openOnly' => true,

                )),
            )));

        $subform->addElement('note', 'header_name', array(
            'value' => $this->translate('time_documentation_clinic_name'),
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'th',
                    'style' => 'width:25%;',//ISPC-2926,Elena,17.05.2021
                )),
            )));

        $subform->addElement('note', 'header_patient', array(
            'value' => $this->translate('time_documentation_clinic_patient'),
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'th',
                    'style' => 'width:12%;',
                )),
            )));
        $subform->addElement('note', 'header_family', array(
            'value' => $this->translate('time_documentation_clinic_family'),
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'th',
                    'style' => 'width:12%;',
                )),
            )));

        $subform->addElement('note', 'header_systemic', array(
            'value' => $this->translate('time_documentation_clinic_systemic'),
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'th',
                    'style' => 'width:12%;',
                )),
            )));
        $subform->addElement('note', 'header_professional', array(
            'value' => $this->translate('time_documentation_clinic_professional'),
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'th',
                    'style' => 'width:11%;',//ISPC-2926,Elena,17.05.2021
                )),
            )));

        if(!$pdf) {

            $subform->addElement('note', 'header_sum', array(
                'value' => $this->translate('time_documentation_clinic_sum'),
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'th',
                        'style' => 'width:11%;',//ISPC-2926,Elena,17.05.2021
                    )),
                )));
            //ISPC-2899,Elena,23.04.2021
            $modules = new Modules();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            if($modules->checkModulePrivileges("1020", $logininfo->clientid))
            {
                $subform->addElement('note', 'header_call_on_duty', array(
                    'value' => $this->translate('time_documentation_call_on_duty'),
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'th',
                            'style' => 'width:12%;',
                        )),
                    )));
            }

            $subform->addElement('note', 'actions', array(
                'value' => $this->translate('actions'),
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'th',
                        'style' => 'width:8%;',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,

                    )),
                )));
        }
        else{
            //ISPC-2899,Elena,23.04.2021
            $modules = new Modules();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            if($modules->checkModulePrivileges("1020", $logininfo->clientid)) {
                $subform->addElement('note', 'header_sum', array(
                    'value' => $this->translate('time_documentation_clinic_sum'),
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'th',
                            'style' => 'width:12%;',
                        )),
//                        array(array('row' => 'HtmlTag'), array(
//                            'tag' => 'tr',
//                            'closeOnly' => true,
//
//                        )),
                    )));
//ISPC-2899,Elena,23.04.2021
                $subform->addElement('note', 'header_call_on_duty', array(
                    'value' => $this->translate('time_documentation_call_on_duty'),
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'th',
                            //'style' => 'width:12%;',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,

                        )),
                    )));

            }else{
                //ISPC-2899,Elena,23.04.2021
                $subform->addElement('note', 'header_sum', array(
                    'value' => $this->translate('time_documentation_clinic_sum'),
                    'filters' => array('StringTrim'),
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'th',
                            'style' => 'width:12%;',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,

                        )),//ISPC-2899,Elena,23.04.2021
                    )));


            }//ISPC-2899,Elena,23.04.2021

        }
    }

    private function add_timelog_logrow($key, $timelog, $subform, $pdf=false)
    {

        $subform->addElement('text', 'date_' . $key, array(
            'value' => date('d.m.Y',strtotime($timelog['date'])),
            'filters' => array('StringTrim'),
            'readonly' => true,
            'belongsTo' => '[timelog]',
            'elementBelongsTo' => 'timedocumentation',
            'array_index' => $key,
            'class' => 'timelog',
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'class' => 'timelog-logrow',
                    'openOnly' => true,

                )),
            )));

        $subform->addElement('text', 'username_' . $key, array(
            'value' => $timelog['username'],
            'readonly' => true,
            'belongsTo' => '[timelog]',
            'elementBelongsTo' => 'timedocumentation',
            'array_index' => $key,
            'class' => 'timelog',
            'filters' => array('StringTrim'),
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style'=> 'width:25%;' //ISPC-2926,Elena,17.05.2021
                )),
            )));
        $subform->addElement('text', 'mins_patient_' . $key, array(
            'value' => $timelog['mins_patient'],
            'readonly' => true,
            'belongsTo' => '[timelog]',
            'elementBelongsTo' => 'timedocumentation',
            'array_index' => $key,
            'class' => 'timelog-mins',
            'filters' => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            )));
        $subform->addElement('text', 'mins_family_' . $key, array(
            'value' => $timelog['mins_family'],
            'readonly' => true,
            'belongsTo' => '[timelog]',
            'elementBelongsTo' => 'timedocumentation',
            'array_index' => $key,
            'class' => 'timelog-mins',
            'filters' => array('StringTrim'),
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            )));

        $subform->addElement('text', 'mins_systemic_' . $key, array(
            'value' => $timelog['mins_systemic'],
            'readonly' => true,
            'belongsTo' => '[timelog]',
            'elementBelongsTo' => 'timedocumentation',
            'array_index' => $key,
            'class' => 'timelog-mins',
            'filters' => array('StringTrim'),
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            )));
        $subform->addElement('text', 'mins_profi_' . $key, array(
            'value' => $timelog['mins_profi'],
            'readonly' => true,
            'belongsTo' => '[timelog]',
            'elementBelongsTo' => 'timedocumentation',
            'array_index' => $key,
            'class' => 'timelog-mins',
            'filters' => array('StringTrim'),
            'readonly' => true,
            'decorators' => array(
                'ViewHelper',
                'SimpleInput',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            )));
        if(!$pdf) {
            $subform->addElement('text', 'minutes_' . $key, array(
                'value' => $timelog['minutes'],
                'readonly' => true,
                'belongsTo' => '[timelog]',
                'elementBelongsTo' => 'timedocumentation',
                'array_index' => $key,
                'class' => 'timelog-mins timelog-mins_row_sum',//TODO-4069 Ancuta 14.05.2021
                'filters' => array('StringTrim'),
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                )));
            //ISPC-2899,Elena,23.04.2021
            $modules = new Modules();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            if($modules->checkModulePrivileges("1020", $logininfo->clientid))
            {
                $on_duty = ($timelog['call_on_duty'] == 1) ? true : false;
                $subform->addElement('checkbox', 'call_on_duty_' . $key,
                    array(
                        'value' => 1,
                        'class' => 'call_on_duty',
                        'belongsTo' => '[timelog]',
                        'checked' => $on_duty,
                        'elementBelongsTo' => 'timedocumentation',
                        'array_index' => $key,
                        'decorators' => array(
                            'ViewHelper',
                            'SimpleCheckbox',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                                'style' => 'width:12%',
                            )),
                        )
                    )
                );
            }


            $subform->addElement('image', 'remove_' . $key, array(
                'type' => 'button',
                'label' => '',
                'src' => RES_FILE_PATH . '/images/action_delete.png',
                'style' => ' width: 16px',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                ),
                'class' => 'remove-timelog-button'
            ));

            $subform->addElement('hidden', 'userid_' . $key, array(
                'value' => $timelog['userid'],
                'belongsTo' => '[timelog]',
                'elementBelongsTo' => 'timedocumentation',
                'array_index' => $key,
                'class' => 'timelog',
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                )));

            $subform->addElement('hidden', 'groupid_' . $key, array(
                'value' => $timelog['groupid'],
                'belongsTo' => '[timelog]',
                'elementBelongsTo' => 'timedocumentation',
                'array_index' => $key,
                'class' => 'timelog',
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                )));

            $subform->addElement('hidden', 'groupname_' . $key, array(
                'value' => $timelog['groupname'],
                'belongsTo' => '[timelog]',
                'elementBelongsTo' => 'timedocumentation',
                'array_index' => $key,
                'class' => 'timelog',
                'filters' => array('StringTrim'),
                'decorators' => array(
                    'ViewHelper',
                    'SimpleInput',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                )));
        }
        else{
            //ISPC-2899,Elena,23.04.2021
            $modules = new Modules();
            $logininfo = new Zend_Session_Namespace('Login_Info');

            if($modules->checkModulePrivileges("1020", $logininfo->clientid)){

                $subform->addElement('text', 'minutes_' . $key, array(
                    'value' => $timelog['minutes'],
                    'readonly' => true,
                    'belongsTo' => '[timelog]',
                    'elementBelongsTo' => 'timedocumentation',
                    'array_index' => $key,
                    'class' => 'timelog-mins timelog-mins_row_sum',//TODO-4069 Ancuta
                    'filters' => array('StringTrim'),
                    'readonly' => true,
                    'decorators' => array(
                        'ViewHelper',
                        'SimpleInput',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                        ))
                    )));
                $on_duty = ($timelog['call_on_duty'] == 1) ? ' x' : '';
                $subform->addElement('note', 'call_on_duty_' . $key,
                    array(
                        'value' => $on_duty ,
                        'class' => 'call_on_duty',
                        'belongsTo' => '[timelog]',

                        'elementBelongsTo' => 'timedocumentation',
                        'array_index' => $key,
                        'decorators' => array(
                            'ViewHelper',
                            //'SimpleCheckbox',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                                // 'style' => 'width:12%',
                            )),
                            array(array('roe' => 'HtmlTag'), array(
                                'tag' => 'tr',
                                'closeOnly' => true,
                            )),
                        )
                    )
                );

            }else{

                $subform->addElement('text', 'minutes_' . $key, array(
                    'value' => $timelog['minutes'],
                    'readonly' => true,
                    'belongsTo' => '[timelog]',
                    'elementBelongsTo' => 'timedocumentation',
                    'array_index' => $key,
                    'class' => 'timelog-mins timelog-mins_row_sum',//TODO-4069 Ancuta 14.05.2021
                    'filters' => array('StringTrim'),
                    'readonly' => true,
                    'decorators' => array(
                        'ViewHelper',
                        'SimpleInput',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,
                        )),
                    )));

            }//ISPC-2899,Elena,23.04.2021


        }

    }

    private function add_timelog_sumrow($timedocumentation, $subform, $pdf=false)
    {


        $subform->addElement('note', 'summe', array(
            'value' => 'Summe',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'colspan' => 2,
                    'style' => 'padding-bottom: 20px;',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'class' => 'timelog-sumrow',
                    'openOnly' => true,

                )),
            )));

        $subform->addElement('text', 'mins_patient', array(
            'value' => $timedocumentation['mins_patient'],
            'readonly' => true,
            'class' => 'timelog-sum',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            )));
        $subform->addElement('text', 'mins_family', array(
            'value' => $timedocumentation['mins_family'],
            'readonly' => true,
            'class' => 'timelog-sum',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            )));

        $subform->addElement('text', 'mins_systemisch', array(
            'value' => $timedocumentation['mins_systemisch'],
            'readonly' => true,
            'class' => 'timelog-sum',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            )));
        $subform->addElement('text', 'mins_profi', array(
            'value' => $timedocumentation['mins_profi'],
            'readonly' => true,
            'class' => 'timelog-sum',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            )));
        if(!$pdf) {
            $subform->addElement('text', 'minutes', array(
                'value' => $timedocumentation['minutes'],
                'readonly' => true,
                'class' => 'timelog-sum',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                )));
            //ISPC-2899,Elena,23.04.2021
            $modules = new Modules();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            if($modules->checkModulePrivileges("1020", $logininfo->clientid))
            {
                $subform->addElement('note', 'call_on_duty' ,
                    array(
                        'value' => '',
                        'class' => 'call_on_duty',
                        'belongsTo' => '[timelog]',
                        //'checked' => false,
                        //'elementBelongsTo' => 'timedocumentation',
                        //'array_index' => $key,
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array(
                                'tag' => 'td',
                                'style' => 'width:12%',
                            )),
                        )
                    )
                );
            }
            $subform->addElement('hidden', 'empty', array(
                'id' => 'src-delete-image',
                'data-src' => RES_FILE_PATH . '/images/action_delete.png',
                'data-delete-message' => $this->translate('confirmdeleteonerecord'),
                'data-delete-title' => $this->translate('confirmdeletetitle'),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                        'closeOnly' => true,
                    )),
                )));
        }
        else{
            //ISPC-2899,Elena,23.04.2021
            $modules = new Modules();
            $logininfo = new Zend_Session_Namespace('Login_Info');
            if($modules->checkModulePrivileges("1020", $logininfo->clientid))
            {
                $subform->addElement('text', 'minutes', array(
                    'value' => $timedocumentation['minutes'],
                    'readonly' => true,
                    'class' => 'timelog-sum',
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'colspan' => 2
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,
                        )),
                    )));

            }else{
                $subform->addElement('text', 'minutes', array(
                    'value' => $timedocumentation['minutes'],
                    'readonly' => true,
                    'class' => 'timelog-sum',
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                        )),
                        array(array('row' => 'HtmlTag'), array(
                            'tag' => 'tr',
                            'closeOnly' => true,
                        )),
                    )));

            }

        }

    }

    private function add_timelog_addrow($userid, $client_user, $subform, $active_client_user){

        $subform->addElement('text', 'date', array(
            'value' => date('d.m.Y'),
            'filters' => array('StringTrim'),
            'class' => 'timelog-date datepicker',
            'options' => array('ignore' => TRUE),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'width:12%;',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'id' => 'timelog-addrow',
                    'openOnly' => true,

                )),
            )));

        // Maria:: Migration CISPC to ISPC 08.08.2020
        // for selectboxes below we need empty row
        $client_user_for_other = [];
        $client_user_for_other[0] = '';
        foreach($client_user as $key => $value){
            $client_user_for_other[$key] = $value;
        }

//print_r($client_user);


        $subform->addElement('select', 'name', array(
            'multiOptions' => $client_user,
            'placeholder' => 'Benutzer auswählen',//ISPC-2926,Elena,17.05.2021
            'value' => $userid,
            'style' => 'max-width:120px;',
            'class' => 'timelog-name',
            'array_user' => json_encode($active_client_user),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'width:25%;vertical-align:bottom;',//ISPC-2926,Elena,17.05.2021
                )),
            )));

        $this->add_time_measure($subform, 'mins_patient');
        $this->add_time_measure($subform, 'mins_family');
        $this->add_time_measure($subform, 'mins_systemic');
        $this->add_time_measure($subform, 'mins_profi');

        $subform->addElement('text', 'minutes', array(
            'value' => '',
            'filters' => array('StringTrim'),
            'class' => 'timelog-mins-sum',
            'readonly'=>1, //TODO-4136 Nico 17.05.2021
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'width:10%',//ISPC-2926,Elena,17.05.2021
                )),
            )));

        //ISPC-2899,Elena,23.04.2021
        $modules = new Modules();
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($modules->checkModulePrivileges("1020", $logininfo->clientid))
        {
            $subform->addElement('checkbox', 'call_on_duty',
                array(
                    'value' => 1,
                    'class' => 'call_on_duty',
                    'checked' => false,
                    'decorators' => array(
                        'ViewHelper',
                        array(array('data' => 'HtmlTag'), array(
                            'tag' => 'td',
                            'style' => 'width:11%',//ISPC-2926,Elena,17.05.2021
                        )),
                    )
                )
            );
        }

        $subform->addElement('image', 'add_image', array(
            'type' => 'button',
            'label' => '',
            'src' => RES_FILE_PATH . '/images/action_add.png',
            'style' => ' width: 16px',
            'class' => 'add-timelog-button',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'width: 8%;vertical-align:top;margin:2px;',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,
                )),
            ),
        ));
        // Maria:: Migration CISPC to ISPC 08.08.2020
        /*
        $subform->addElement('note',  'comments_note', array(
            'value'        =>  $this->translate('time_documentation_clinic_add_other_users'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2, 'class'=>'note', 'style' => 'vertical-align:top;margin:2px;')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
            ),
        ));*/
        /*
           $subform->addElement('image', 'add_user_image', array(
            'type' => 'button',
            'label' => '',
            'src' => RES_FILE_PATH . '/images/action_add.png',
            'style' => ' width: 16px',
            'class' => 'add-other_users-button',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',

                    'style' => 'width: 8%;vertical-align:top;margin:2px;',
                )),

            ),
        ));*/

        $subform->addElement('note',  'comments_note', array(
            'value'        =>  '',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td',  'class'=>'note', 'style' => 'vertical-align:top;margin:2px;')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'class' => 'row_other_users')),
            ),
        ));
        $client_user_for_other[0] = 'Benutzer auswählen';
        $subform->addElement('select', 'other_names_0', array(
            'multiOptions' => $client_user_for_other , //json_encode($active_client_user),
            //ISPC-2926,Elena,17.05.2021
            'value' => 0,
            'placeholder' => 'Benutzer auswählen',//ISPC-2926,Elena,17.05.2021
            'style' => 'max-width:120px;',
            //'multiple' => 'multiple',
            'class' => 'timelog-other_names',
            'array_user' => json_encode($active_client_user),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'colspan' => 2,
                    'style' => 'width:25%;vertical-align:bottom;',//ISPC-2926,Elena,17.05.2021
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true,
                ))
            )));





    }

    private function add_time_measure($subform, $name)
    {

        $subform->addElement('button', $name . '_decr', array(
            'label' => '-',
            'class' => 'timelog-decr',
            'type' => 'button',
            'style' => 'width:16px;height:20px; border:none; background-color:#ddd;',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'style' => 'width:12%;font-size:0px;',
                    'openOnly' => true,
                )),
            )));
        $subform->addElement('text', $name, array(
            'value' => '',
            'class' => 'timelog-mins',
            'filters' => array('StringTrim'),
            'style' => 'width:20px;text-align: center; border:none;',
            'decorators' => array(
                'ViewHelper'
            )));
        $subform->addElement('button', $name . '_incr', array(
            'label' => '+',
            'type' => 'button',
            'filters' => array('StringTrim'),
            'class' => 'timelog-incr',
            'style' => 'width:16px;height:20px; border:none; background-color:#ddd;',
            // 'onclick' => "var newval=0; var oldval=parseInt($(this).closest('td').find('.timelog-mins').val()); if(isNaN(oldval)){oldval=0;}; newval=oldval+5; $(this).closest('td').find('.timelog-mins').val(newval).change();timelog_addrow_sum_mins();",
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'closeOnly' => true,
                )),
            )));

    }

    private function add_javascript($user_select_as_json)//ISPC-2926,Elena,17.05.2021
    {
        //ISPC-2899,Elena,23.04.2021
        $has_call_on_duty = '0';
        $modules = new Modules();
        $logininfo = new Zend_Session_Namespace('Login_Info');//ISPC-2926,Elena,17.05.2021
        if($modules->checkModulePrivileges("1020", $logininfo->clientid))
        {
            $has_call_on_duty = '1';
        }



        //TODO-4069 Ancuta 26.04.2021
        //$logininfo = new Zend_Session_Namespace('Login_Info');//ISPC-2926,Elena,17.05.2021
        $clientid = $logininfo->clientid;
        $modules = new Modules();
        $companion_time_tracking  = 0;
        if($modules->checkModulePrivileges("254", $clientid))//Medication acknowledge
        {
            $companion_time_tracking = 1;
        }
        //--


        echo "<script>   
        var other_users = 0;
        //ISPC-2926,Elena,17.05.2021
        var client_users = JSON.parse('"  . $user_select_as_json . "');  //ISPC-2926,Elena,17.05.2021
        //console.log(client_users);
        var companion_time_tracking = '$companion_time_tracking';  //TODO-4069 Ancuta 24.04.2021
        var cid = '".$_REQUEST['cid']."';  //TODO-4069 Ancuta 14.05.2021 - check if not edit 


        //ISPC-2899,Elena,23.04.2021
        var has_call_on_duty = $has_call_on_duty;

         $(document).ready(function () {   
     //ISPC-2926,Elena,17.05.2021
          $('.timelog-name, .timelog-other_names').selectize({
          sortField: 'text'
          
            });           
      
         var a_prev_vals = [];
         a_prev_vals['timedocumentation2-other_names_0'] = '';
         
         
         $('.timelog-other_names').live('change', function(){
            //ISPC-2926,Elena,17.05.2021
            aExisting = [];
            var userExists = false;
            $.each($('.timelog-other_names'), function(ind, elm){
                var vl = ($(elm).val());
                if(aExisting.includes( parseInt(vl))){
                    userExists = true;
                }
                if(vl !== NaN && vl !== '' &&  vl !== 0 && !aExisting.includes( vl)){
                    aExisting.push(parseInt(vl))
                }
                
            })
            if(!aExisting.includes( parseInt($('.timelog-name').val()))){
               aExisting.push(parseInt($('.timelog-name').val())); 
            }else{
                userExists = true;
            }
            
            //console.log('all existing', aExisting);
            
            
            //console.log('current_user', $(this).val());
            //ISPC-2926,Elena,17.05.2021
            if($(this).val() == null || $(this).val() == '' || $(this).val() == '0'){
                return;
            }
            var current_user = $(this).val();
            
            var id = $(this).attr('id');
            console.log(id);
            console.log('prev', a_prev_vals[id]);
            //ISPC-2926,Elena,17.05.2021
            if(!userExists ){
            var parentRow = $(this).parent().parent();
            var nextRow = parentRow.clone();
            other_users++;
            console.log(nextRow.find('td').find('select'));
            var selectbox = nextRow.find('td').find('select');
            selectbox.attr('id',  'timedocumentation2-other_names_' + other_users.toString());
            a_prev_vals['timedocumentation2-other_names_' + other_users.toString()] = '';
            selectbox.attr('name',  'timedocumentation2[other_names][' + other_users.toString() + ']');
            //ISPC-2926,Elena,17.05.2021
            selectbox.html('');
            //ISPC-2926,Elena,17.05.2021
            //we need to add all values to modified with selectize dropdown
            selectbox.append($('<option>',{
                  		value: 0,
                  		text: 'Benutzer auswählen'
            		}));
            $.each(client_users, function(index, value){
            		selectbox.append($('<option>',{
                  		value: index,
                  		text: value
            		}));
      		}); 
      		//selectbox.val( " . $logininfo->userid ." );
      		//ISPC-2926,Elena,17.05.2021
      		selectbox.val( 0 );
            //[0].attr('id', 'timedocumentation2-other_names_' + other_users.toString());
            //ISPC-2926,Elena,17.05.2021
            //we need to clear table cell from all selectize elements, then add dropdown and then apply selectize
            selectbox.parent('td').html('').append(selectbox);
            nextRow.addClass('add_other_row_2');
            parentRow.parent().append(nextRow);
            //ISPC-2926,Elena,17.05.2021
            var select = selectbox.selectize({
            sortField: 'text',
            allowEmptyOption : true,
            showEmptyOptionInDropdown : true
            
            
            });
            
            }
            
            
            
            
         
         });
             
            $('#block-time_documentation_clinic input.datepicker').datepicker({
             			dateFormat: 'dd.mm.yy',
             			changeMonth: true,
		                changeYear: true,
		                nextText: '',
		                prevText: ''
	            });
           
             timelog_sum_mins();
         })
         
         $(document).on('click', '.remove-timelog-button', function (e) {

                var whichtr = $(this).closest('tr');
                //I've found no other way...
                var message = $('#src-delete-image').data('delete-message');
                var title = $('#src-delete-image').data('delete-titlee');
                jConfirm(message, title, function (r) {
                    if (r) {
                        whichtr.remove();
                        timelog_sum_mins();
                        recalculate_log_row();

                        if(companion_time_tracking == 1 ) { //TODO-4069 Ancuta 14.05.2021 - check if not edit  // TODO-4069 Ancuta  04.06.2021 - removed   cid condition  
                            get_highest_sum(); //TODO-4069 Ancuta 23.04.2021 
                    	}

                    }
                });                
                e.preventDefault();               
            
            });
        
            $(document).on('click', '.add-timelog-button', function (e) {
                    timelog_sum_mins();
                    add_other_log_rows();
                    add_log_row();
                    $('.add_other_row_2').remove();
                    $('#timedocumentation2-other_names_0').val(0);
                    
                    if(companion_time_tracking == 1   ) { // TODO-4069 Ancuta  04.06.2021 - removed   cid condition 
                        get_highest_sum(); //TODO-4069 Ancuta 23.04.2021
                    }

                    e.preventDefault(); 
                                    timedoc_naginfo();
            });
            
            $(document).on('click', '.timelog-decr', function (e) {
                var newval=0; 
                var oldval=parseInt($(this).closest('td').find('.timelog-mins').val()); 
                
                if(isNaN(oldval)){
                    newval=0;
                }
                else if(oldval<=0){
                    newval=30;
                }else{
                    newval=oldval-5
                }; 
                $(this).closest('td').find('.timelog-mins').val(newval).change();
                timelog_addrow_sum_mins();
        });
            
             $(document).on('click', '.timelog-incr', function (e) {
                   var newval=0; 
                   var oldval=parseInt($(this).closest('td').find('.timelog-mins').val()); 
                   if(isNaN(oldval)){
                       oldval=0;
                   }; 
                   newval=oldval+5; 
                   $(this).closest('td').find('.timelog-mins').val(newval).change();
                   timelog_addrow_sum_mins();   
                 });
             //ISPC-2646 - elena, 24.07.2020 // Maria:: Migration CISPC to ISPC 08.08.2020
             var oldvals = [];
             $('.timelog-mins').on('focus', function(){
                 //console.log('focus');
                 oldvals[$(this).attr('id')] = $(this).val();
             })
             $('.timelog-mins').on('change', function(){
                 //console.log('blur');
                 var newval = $(this).val();
                 var oldval = oldvals[$(this).attr('id')];
                 if(oldval === undefined || oldval.trim() === '' || isNaN(parseInt(oldval) )){
                     oldval = 0;
                 }
         
                 if( !isNaN(parseInt(newval)) && !isNaN(parseInt(newval))){
                     timelog_addrow_sum_mins();  
                 }
             })
         
        function  timelog_addrow_sum_mins(){
                var sum=0;
                $('#timelog-addrow').find('.timelog-mins').each(function(){
                    var val=parseInt($(this).val());
                    if(isNaN(val)){
                        val=0;
                    }
                    sum=sum+val;
                   
                });
              
              $('#timelog-addrow').find('.timelog-mins-sum').val(sum).change();
                              timedoc_naginfo();
        }   
       

        function timelog_sum_mins(){
                var sum=[0,0,0,0,0];
                var row=0;
                $('.timelog-logrow').each(function(){
                    var inputs=$(this).find('.timelog-mins');
                    for (var i=0; i<5; i++){
                        var v=parseInt($(inputs[i]).val());
                        if(isNaN(v)){
                         v=0;
                         }
                       sum[i]=sum[i]+v;
                        
                    }
                });
                var cols=$('.timelog-sumrow td');
                for (var i=0; i<5; i++){
                    $(cols[i+1]).find('.timelog-sum').val(sum[i]);
                }
                timedoc_naginfo();
        }

        //TODO-4069 Ancuta 23.04.2021        
        function get_highest_sum(){
            var sums =[];
            $('.timelog-mins_row_sum').each(function(){
                sums.push($(this).val()); 
            });

            if (sums.length === 0) {
                alert('Die Zeit muss dokumentiert werden.');
            }
            else{
                var max_sum = Math.max.apply(Math,sums);

                //TODO-4069 Ancuta 15.06.2021 :: added  so changes are only done when needed info is filled 
			    var current_time = $('#begin_date_h').val()+':'+$('#begin_date_m').val()+':00';
			    set_end_time(addMinutes(current_time, max_sum));
                //-- 
            }
  

            //  TODO-4069 Ancuta 15.06.2021 :: commented - and moved above        
			/*
            var current_time = $('#begin_date_h').val()+':'+$('#begin_date_m').val()+':00';
			set_end_time(addMinutes(current_time, max_sum));
            */
            //--
        }
        //--        
        
        function add_log_row(){
                var timeinpts=$('#timelog-addrow').find('.timelog-mins');
                var timeinptssum=$('#timelog-addrow').find('.timelog-mins-sum');
                
                //I've found no other way...
                var key = $('.timelog-logrow').length;
                var path = $('#src-delete-image').data('src');
                var obj = JSON.parse($('#timelog-addrow').find('.timelog-name').attr('array_user'));
                var userid = $('#timelog-addrow').find('.timelog-name option:selected').val();
                           
                var newrow=$('<tr>');
                $(newrow).addClass('timelog-logrow');         
              
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog\" name=\"timedocumentation[timelog][' + key + '][date]\" value=\"'+$('#timelog-addrow').find('.timelog-date').val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog\" name=\"timedocumentation[timelog]['+ key +'][username]\" value=\"'+$('#timelog-addrow').find('.timelog-name option:selected').text()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][mins_patient]\" value=\"'+$(timeinpts[0]).val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][mins_family]\" value=\"'+$(timeinpts[1]).val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][mins_systemic]\" value=\"'+$(timeinpts[2]).val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][mins_profi]\" value=\"'+$(timeinpts[3]).val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins  timelog-mins_row_sum\" name=\"timedocumentation[timelog]['+ key +'][minutes]\" value=\"'+$(timeinptssum).val()+'\"></td>'); //TODO-4069 Ancuta 14.05.2021
                //ISPC-2899,Elena,23.04.2021
                if(parseInt(has_call_on_duty) == 1){
                var onduty = '';
                if($('#timedocumentation2-call_on_duty').prop('checked')){
                    onduty = ' checked ';
                }
                $(newrow).append('<td><input readonly=\"readonly\" type=\"checkbox\" class=\"timelog timelog-call-on-duty\" name=\"timedocumentation[timelog]['+ key +'][call_on_duty]\" value=\"1\" '+ onduty + '></td>'); 
                }
                $(newrow).append('<td><input type=\"image\" style=\"width: 16px;\" class=\"timelog remove-timelog-button\" name=\"remove_'+ key +'\" src=\"'+ path + '\" ></td>'); 
                $(newrow).append('<td><input type=\"hidden\" name=\"timedocumentation[timelog]['+ key +'][userid]\" value=\"'+userid+'\"></td>'); 
                $(newrow).append('<td><input type=\"hidden\" name=\"timedocumentation[timelog]['+ key +'][groupid]\" value=\"'+obj[userid]['groupid']+'\"></td>'); 
                $(newrow).append('<td><input type=\"hidden\" name=\"timedocumentation[timelog]['+ key +'][groupname]\" value=\"'+obj[userid]['group']+'\"></td>'); 

                $('.timelog-sumrow').before(newrow); 
                timelog_sum_mins();
                $('#timelog-addrow').find('.timelog-mins').val('');
                $('#timelog-addrow').find('.timelog-mins-sum').val('');
            }
            
            function add_other_log_rows(){
            //ISPC-2646, Elena, 31.07.2020 // Maria:: Migration CISPC to ISPC 08.08.2020
                var otherusers = $('select.timelog-other_names option:selected').map(function() {return $(this).val();}).get();//ISPC-2926,Elena,17.05.2021
                
                var userid_first = $('#timelog-addrow').find('select.timelog-name option:selected').val();//ISPC-2926,Elena,17.05.2021
                console.log('other users', otherusers);
                for(var i=0; i<otherusers.length; i++){
                //ISPC-2926,Elena,17.05.2021
                if(otherusers[i] == undefined || otherusers[i] == null || otherusers[i] == ''){
                    //console.log('continue');
                    continue;
                }
                console.log('other user', otherusers[i]);
                    if(parseInt(otherusers[i] )!== 0 && otherusers[i] !== userid_first  ){ //exclude user chosen in add_row
                    var timeinpts=$('#timelog-addrow').find('.timelog-mins');
                var timeinptssum=$('#timelog-addrow').find('.timelog-mins-sum');
                
                //I've found no other way...
                var key = $('.timelog-logrow').length;
                var path = $('#src-delete-image').data('src');
                var obj = JSON.parse($('#timelog-addrow').find('select.timelog-name').attr('array_user'));//ISPC-2926,Elena,17.05.2021
                //console.log('obj array user', obj);//ISPC-2926,Elena,17.05.2021
                //console.log('client users', client_users, otherusers[i]);//ISPC-2926,Elena,17.05.2021
                var username = client_users[otherusers[i]];//ISPC-2926,Elena,17.05.2021
                //console.log('username', username);//ISPC-2926,Elena,17.05.2021
                           
                var newrow=$('<tr>');
                $(newrow).addClass('timelog-logrow');         
              
                              
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog\" name=\"timedocumentation[timelog][' + key + '][date]\" value=\"'+$('#timelog-addrow').find('.timelog-date').val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog\" name=\"timedocumentation[timelog]['+ key +'][username]\" value=\"'+username+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][mins_patient]\" value=\"'+$(timeinpts[0]).val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][mins_family]\" value=\"'+$(timeinpts[1]).val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][mins_systemic]\" value=\"'+$(timeinpts[2]).val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][mins_profi]\" value=\"'+$(timeinpts[3]).val()+'\"></td>');
                $(newrow).append('<td><input readonly=\"readonly\" type=\"text\" class=\"timelog timelog-mins\" name=\"timedocumentation[timelog]['+ key +'][minutes]\" value=\"'+$(timeinptssum).val()+'\"></td>'); 
                
                //ISPC-2899,Elena,23.04.2021
                if(parseInt(has_call_on_duty) == 1){
                 var onduty = '';
                if($('#timedocumentation2-call_on_duty').prop('checked')){
                    onduty = ' checked ';
                }
                $(newrow).append('<td><input readonly=\"readonly\" type=\"checkbox\" class=\"timelog timelog-call-on-duty\" name=\"timedocumentation[timelog]['+ key +'][call_on_duty]\" value=\"1\"' + onduty + '></td>'); 
                }
                $(newrow).append('<td><input type=\"image\" style=\"width: 16px;\" class=\"timelog remove-timelog-button\" name=\"remove_'+ key +'\" src=\"'+ path + '\" ></td>'); 
                //ISPC-2926,Elena,17.05.2021
                $(newrow).append('<td><input type=\"hidden\" name=\"timedocumentation[timelog]['+ key +'][userid]\" value=\"'+otherusers[i]+'\"></td>'); 
                $(newrow).append('<td><input type=\"hidden\" name=\"timedocumentation[timelog]['+ key +'][groupid]\" value=\"'+obj[otherusers[i]]['groupid']+'\"></td>'); 
                $(newrow).append('<td><input type=\"hidden\" name=\"timedocumentation[timelog]['+ key +'][groupname]\" value=\"'+obj[otherusers[i]]['group']+'\"></td>'); 

                $('.timelog-sumrow').before(newrow); 
                timelog_sum_mins();
                }

               
                
                }
                
                
            }
            
        function recalculate_log_row(){
            
             var rowcount = 0;
             
             $('.timelog-logrow').each(function () {              
                  $(this).find('input, textarea, select, hidden').each(function () {
                   var name = $(this).attr('name');
                   name = name.replace(/\[[\d+]\]/, \"[\" + rowcount + \"]\");
                   $(this).attr('name', name);
               });
                rowcount++;
             });            
        }
        
        $(document).ready(function(){
            function select_patient_case_color_update(){
                var casecolor = $('#patientcase-select_patient_case').find('option:selected').data('casecolor');
                //console.log('casecolor');
                $('#patientcase-select_patient_case').css('background-color',casecolor);        
            }
            $(document).on('change', '#patientcase-select_patient_case', select_patient_case_color_update);
            $('#patientcase-select_patient_case').change();//update color
        });
        
        function timedoc_naginfo(){
            var t=$('#block-time_documentation_clinic_content .timelog-mins-sum');
            var btt=$('#block-time_documentation_clinic_content .add-timelog-button');
            
            var el=$('#block-time_documentation_clinic_content .timedoc-naginfo');
            if(el.length<1){
                var newel=$('<img class=\'timedoc-naginfo\' src=\'".RES_FILE_PATH."/images/nag_timedoku.png\' >');
                $('#block-time_documentation_clinic_content').append(newel);
                var el=$('#block-time_documentation_clinic_content .timedoc-naginfo');
                $(el).css('position', 'absolute');
            }
            var pos=$('.add-timelog-button').position();                
                $(el).css('left', pos.left +20);
                $(el).css('top', pos.top -80);
                //console.log($(t).val());
            if($(t).val()>0){
                $(el).show();
            }else{
                $(el).hide();
            }
        }        


         
        </script>";
    }

    private function create_pdf_careprocessclinic($timedocumentation, $case, $casetypes)
    {
        $subform = $this->subFormContactformBlock(); //timelog-logtable
        $subform->setLegend('time_documentation_clinic');
        $this->__setElementsBelongTo($subform, 'timedocumentation');
        $model = new PatientCaseStatus();
        //ISPC-2899,Elena,23.04.2021
        $colspan = 7;
        $modules = new Modules();
        $logininfo = new Zend_Session_Namespace('Login_Info');
        if($modules->checkModulePrivileges("1020", $logininfo->clientid))
        {
            $colspan = 8;
        }

        if($case){
            $subform->addElement('note', 'patient_case', array(
                'value' => $model->format_patientcase_for_select_option($case),
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'th',
                        'colspan' => $colspan,//ISPC-2899,Elena,23.04.2021
                    )),
                    array(array('row' => 'HtmlTag'), array(
                        'tag' => 'tr',
                    )),
                )));
        }
        if(!empty($timedocumentation['timelog'])){
            $this->add_timelog_headrow($subform, true);

            usort($timedocumentation['timelog'], function($a, $b) {
                return $a['date'] > $b['date'];
            });

            foreach ($timedocumentation['timelog'] as $key=>$value){
                $this->add_timelog_logrow($key, $value, $subform, true);
            }

            $this->add_timelog_sumrow($timedocumentation, $subform, true);

        }


        return $this->filter_by_block_name($subform);


    }

    private function get_selected_patientcase($patientcases)
    {
        $patient_case_id = $_POST['patientcase']['select_patient_case'];
        $patient_case = null;
        if($patient_case_id == '0')
            return false;

        $key = array_search($patient_case_id, array_column($patientcases, 'id'));
        if($key!==false)
            return $patientcases[$key];
        return false;
    }

}

?>