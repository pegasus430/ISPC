<?php
require_once("Pms/Form.php");

/**
 * Class Application_Form_Interventions
 * ISPC-2630 Elsa: Interventionen
 * @elena, 05.08.2020
 * Maria:: Migration CISPC to ISPC 20.08.2020
 */
class Application_Form_Interventions extends Pms_Form
{

    //ISPC-2831 Dragos 15.03.2021 added no_actions
    public function create_form_interventions($options = array(), $ipid, $no_actions = false){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['interventions'] = $options['intervention'];
        }

        if (!$stored_data) {
            $stored_data['interventions'] = [];
        }

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $fn_options = $this->getFnOptions($__fnName);

        //print_r($fn_options);

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "interventions",
            'blocktitle' => "Interventionen",
            'template' => 'form_block_interventions.html',
            'formular_type' => $pdf,
            'no_actions' => $no_actions, //ISPC-2831 Dragos 15.03.2021
        );

        $data = array();

        $data['f_values'] = $stored_data;

        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];

        $medical_interventions = Interventions::getPatientInterventionsByType($ipid, 'medical');
        //print_r($medical_interventions);
        $nonmedical_interventions = Interventions::getPatientInterventionsByType($ipid, 'nonmedical');
        $data['medical'] = $medical_interventions;
        $data['nonmedical'] = $nonmedical_interventions;

        return $this->create_simple_auto_add_block($blockconfig, $data);

    }

    public function create_simple_auto_add_block($blockconfig, $data = array())
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $pdf = false;
        if ($blockconfig['formular_type'] == "pdf") {
            $pdf = true;
        }
        $newview = new Zend_View();
        $newview->pdf = $pdf;

        foreach ($data as $key=>$value){
            $newview->$key = $value;
        }
        // necessary for Baseassesment Pflege, does nothing with another form blocks
        $newview->blockconfig = $blockconfig;
        $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
        $html = $newview->render($blockconfig['template']);
        $options = array();
        $blockoptions = array();

        if(isset($data['class'])){
            $blockoptions['class'] = $data['class'];
        }
        if(isset($data['opened'])){
            $blockoptions['opened'] = $data['opened'];
        }
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators();
        $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
        $subform->setDecorators(array('FormElements', array('SimpleTemplate', $options),));
        $subform->setElementDecorators(array(
            'ViewHelper',
            array('Errors'),
        ));
        if($pdf){
            $subform->addDecorator('SimpleContactformBlockPdf');
        }else {
            $subform->addDecorator('SimpleContactformBlock', $blockoptions);
        }
        $subform->setLegend($blockconfig['blockname']);
        $subform->setAttrib('class', 'expanded');

        $subform->addElement('note', 'block_' . $blockconfig['blockname'], array(
            'value' => $html,
            'decorators' => array(
                'SimpleTemplate',
            ),
        ));
        //return $subform;
        // use cases : $subform for contactform, if nesessary, and $html for page
        return ['html' => $html, 'subform' => $subform];

    }

    public function create_frequenz_ui($blockname, $is_medical, $options){

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        $value = isset($options['value']) ? $options['value'] : '';
        $data = [];
        $data['value'] = $data;
        $data['options_array'] = Interventions::get_frequenz();
        $data['intervention'] = $options['intervention'];

        $blockconfig = array(
            'blockname' => "interventions",
            'blocksuffix' => ($is_medical) ? 'medical' : 'nonmedical',
            'template' => 'interventions_frequenz.html',
            'formular_type' => $pdf,
        );

        return $this->create_subform_ui($blockconfig, $data );


    }

    public function create_leitsymptom_ui($blockname, $is_medical, $options){

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        $value = isset($options['value']) ? $options['value'] : '';
        $data = [];
        $data['value'] = $data;
        $data['options_array'] = Interventions::get_leitsymptom();
        $data['intervention'] = $options['intervention'];

        $blockconfig = array(
            'blockname' => "interventions",
            'blocksuffix' => ($is_medical) ? 'medical' : 'nonmedical',
            'template' => 'interventions_leitsymptom.html',
            'formular_type' => $pdf,
        );

        return $this->create_subform_ui($blockconfig, $data );

    }

    public function create_proceed_ui($blockname,  $options){

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        $value = isset($options['value']) ? $options['value'] : '';
        $data = [];
        $data['value'] = $data;
        $data['options_array'] = Interventions::get_verfahrengruppen();
        $data['intervention'] = $options['intervention'];

        $blockconfig = array(
            'blockname' => "interventions",

            'template' => 'interventions_verfahrengruppe.html',
            'formular_type' => $pdf,
        );

        return $this->create_subform_ui($blockconfig, $data );

    }


    public function create_subform_ui($blockconfig,  $data){

        $newview = new Zend_View();
        //$newview->pdf = $pdf;

        foreach ($data as $key=>$value){
            $newview->$key = $value;
        }
        // necessary for Baseassesment Pflege, does nothing with another form blocks
        $newview->blockconfig = $blockconfig;
        $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
        $html = $newview->render($blockconfig['template']);

        return $html;

    }


    public function InsertData($post)
    {
        $cust = $this->mapFormFields($post);
        $cust->save();
        return $cust;

    }

    protected function mapFormFields($post){
        $cust = new Interventions();
        $cust->ipid = $post['ipid'];
        $cust->typ = $post['typ'];
        $cust->clientid = $post['clientid'];
        $cust->opscode = $post['opscode'];
        $cust->preparation = $post['preparation'];
        $cust->active_ingredient = $post['active_ingredient'];
        $cust->proceed_group = $post['proceed_group'];
        $cust->action_place = $post['action_place'];
        $cust->duration_hours = $post['duration_hours'];
        $cust->interval_hours = $post['interval_hours'];
        $cust->action_place = $post['action_place'];
        //ISPC-2630, elena, 29.09.2020
        if(isset($post['interventions_verfahren_freetext'])){
            $cust->proceed_group_freetext = $post['interventions_verfahren_freetext'];
        }

        $cust->first = $post['first'];
        $cust->last = $post['last'];
        if((isset($post['is_ongoing'])  && intval($post['is_ongoing']) == 1) ){
            $cust->is_ongoing  = 1;
        }
        //ISPC-2630, elena, 01.10.2020, fix
        $cust->intervention = $post['intervention'];

        $cust->intervention_position = $post['intervention_position'];
        $cust->main_symptom = $post['main_symptom'];
        $cust->main_symptom_freetext = $post['main_symptom_freetext'];
        $cust->aim_reason = $post['aim_reason'];

        $cust->frequency = $post['frequency'];
        $cust->frequency_text = $post['frequency_text'];

        $cust->dosageform = $post['dosageform'];
        $cust->medication_type = $post['medication_type'];

        $cust->dosis_absolute = $post['dosis_absolute'];
        $cust->dosis_absolute_unit = $post['dosis_absolute_unit'];

        $cust->count_day = $post['count_day'];
        $cust->is_ongoing = $post['is_ongoing'];
        return $cust;
    }

    public function UpdateData($post)
    {
        $cust = $this->mapFormFields($post);
        $cust->id = $post['id'];

        $cust->replace();
        return $cust;
    }

    public function validate($post){
        $aErrors = [];
        $Tr = new Zend_View_Helper_Translate();
        $validation = new Pms_Validation();
        if(!empty($post['dosis_absolute']) && !is_numeric($post['dosis_absolute'])){
            $aErrors['dosis_absolute'] = $Tr->translate('interventions_error_dosis_absolute');
        }
        //ISPC-2630, elena, 29.09.2020
        if(empty($post['action_place']) ){
            $aErrors['action_place'] = $Tr->translate('interventions_error_action_place');
        }

        if(!empty($post['duration_hours']) && !is_numeric($post['duration_hours'])){
            $aErrors['duration_hours'] = $Tr->translate('interventions_error_duration_hours');
        }

        if(!empty($post['interval_hours']) && !is_numeric($post['interval_hours'])){
            $aErrors['interval_hours'] = $Tr->translate('interventions_error_interval_hours');
        }

        if(!empty($post['first']) ){
            $first = $post['first'];

            $first = date_create_from_format('Y-m-d H:i:s', $first );
            if($first == false ){
                if($post['typ'] == 'medical'){
                    $aErrors['first'] = $Tr->translate('interventions_error_first');
                }else{
                    $aErrors['first'] = $Tr->translate('interventions_error_first_2');
                }

            }
        }else{ //ISPC-2630, elena, 29.09.2020
            if($post['typ'] == 'medical'){
                $aErrors['first'] = $Tr->translate('interventions_error_first');
            }else{
                $aErrors['first'] = $Tr->translate('interventions_error_first_2');
            }

        }

        if(!empty($post['last']) ){
            $last = $post['last'];


            $last = date_create_from_format('Y-m-d H:i:s', $last );
            if($last == false ){
                if($post['typ'] == 'medical') {
                    $aErrors['last'] = $Tr->translate('interventions_error_last');
                }else{
                    $aErrors['last'] = $Tr->translate('interventions_error_last_2');
                }
            }
        }else{ //ISPC-2630, elena, 29.09.2020
            if(empty($post['is_ongoing'])){ //ISPC-2630, elena, 29.09.2020 fixes
                if($post['typ'] == 'medical') {
                    $aErrors['last'] = $Tr->translate('interventions_error_last_empty');
                }else{
                    $aErrors['last'] = $Tr->translate('interventions_error_last_2_empty');
                }

            }

        }

        if($post['typ'] == 'medical' && empty($post['medication']['wirkstoff'])){
            $aErrors['wirkstoff'] = $Tr->translate('interventions_error_wirkstoff_empty');
        }



        return $aErrors;


    }



}
