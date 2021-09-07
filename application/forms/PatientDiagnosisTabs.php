<?php
require_once("Pms/Form.php");
/**
 * ISPC-2654 Ancuta 07.10.2020
 * @author  Oct 7, 2020  ancuta
 *
 */

class Application_Form_PatientDiagnosisTabs extends Pms_Form
{

    public function create_icd($blockname, $options,$ipid,$clientid){
        
        
        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        $data = [];
        $data['value'] = $data;
        $data['icd'] = $options;
        
        // category info - saved by client or default 
        $ioms_arr = IcdOpsMreSettings::getIcdOpsMreSettings($clientid);
        $client_data = array();
        foreach($ioms_arr as $k=>$cl_data){
            $client_data[$cl_data['category']] = $cl_data;
        }
        
        $default_categories_array = Pms_CommonData::get_diagnosis_category_default(); 
        
        $categories = array();
        foreach($default_categories_array as $ck=>$dk){
            $categories[$dk['category']] = $dk;
            if(!empty($client_data[$dk['category']])){
                $categories[$dk['category']]['color'] = '#'.$client_data[$dk['category']]['color'];
            }
        }
        
        $data['diagnosis_categories'] = $categories;
//         $data['rehabilitation'] = $this->getColumnMapping('rehabilitation');
//         $data['rehabilitation_status'] = $this->getColumnMapping('rehabilitation_status');
        
        $blockconfig = array(
            'blockname' => "ICD",
            'template' => 'patient_diagnosis_icd.phtml',
            'formular_type' => $pdf,
        );
        
        return $this->create_subform_ui($blockconfig, $data );
    }
    
    
    
    
    
    
    
    public function create_form_diagnosis($values =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        // 	    $this->mapValidateFunction($__fnName , "create_form_isValid");
        
        $this->mapSaveFunction($__fnName , "save_form_diagnosis");
        // 	    dd($values);
        $abb = "'HD','ND'";
        if ($this->_clientModules[81]) {
            //Show Hauptsymptomlast(HS) column in Patient diagnosis. HS diagnosis type must be created in Administration>Diagnosen Arten
            $abb .= ",'HS'";
        }
        //Maria:: Migration CISPC to ISPC 22.07.2020
        if ($this->_clientModules[1005]) {
            //IM-103 Show Palliativfall-begruendende Diagnose (PBD) column in Patient diagnosis. PD diagnosis type must be created in Administration>Diagnosen Arten
            $abb .= ",'PBD'";
        }
        //--
        
        $dt = new DiagnosisType();
        $DiagnosisTypes = $dt->getDiagnosisTypes($this->logininfo->clientid, $abb);
        
        $columns = array(
            'ICD',
            'Beschreibung',
            'HD',
        );
        if ($this->_clientModules[81]) {
            $columns[] = 'HS';
        }
        $columns[] = 'ND';
        //Maria:: Migration CISPC to ISPC 22.07.2020
        if ($this->_clientModules[1005]) {
            $columns[] = 'PBD';
        }
        // --
        $columns[] = 'Entfernen';
        
        
        $subform = $this->subFormTable(array(
            'columns' => $columns,
            // 'class' => 'datatable',
        ));
        $subform->setLegend($this->translate('Diagnosis:'));
        $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
        
        
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        
        //return $subform;
        $row_cnt = 0;
        
        foreach ($values as $row) {
            
            if (empty($row['icd_primary']) && empty($row['description']))
                continue;
                //Maria:: Migration CISPC to ISPC 22.07.2020
                $row_elemnts = $this->create_form_diagnosis_row($row, $elementsBelongTo , $this->_clientModules[81], $this->_clientModules[1005], $DiagnosisTypes);
                
                $subform->addSubForm($row_elemnts, $row_cnt);
                
                $row_cnt++;
        }
        
        //add button to add new contacts
        $subform->addElement('button', 'addnew_diagnosis', array(
            'onClick'      => ( isset($_GET['clinic']) && intval($_GET['clinic'])==1) ? 'PatientDiagnosis_addnew_clinic(this, \'PatientDiagnosis\'); return false;' : 'PatientDiagnosis_addnew(this, \'PatientDiagnosis\'); return false;',//Maria:: Migration CISPC to ISPC 22.07.2020
            'value'        => '1',
            'label'        => $this->translate('Add new diagnosis'),
            'decorators'   => array(
                'ViewHelper',
                'FormElements',
                // 	            array('HtmlTag', array('tag' => 'tr')),
                
                array(array('data'=>'HtmlTag'),array('tag'=>'td', 'colspan' => count($columns))),
                array(array('row'=>'HtmlTag'),array('tag'=>'tr'))
                
                
            ),
            'class'        =>'button btnSubmit2018 plus_icon_bg dontPrint',
        ));
        
        
        
        return $this->filter_by_block_name($subform, $__fnName);
    }
    
    
    /**
     *  $icd_secondary->getElement('icdnumber')->setLabel('Secondary diagnoses:'); so you change the label in first TD
     *  $diagnosisType = HD or ND
     *
     * @param unknown $values
     * @param string $elementsBelongTo
     * @return Zend_Form_SubForm
     * Maria:: Migration CISPC to ISPC 22.07.2020
     */
    public function create_form_diagnosis_row ($values =  array() , $elementsBelongTo = null, $hasModule_81_HS = null, $hasModule_1005_PD = null, $DiagnosisTypes = null)
    {
        $__fnName = __FUNCTION__;
        
        //if null, we search to see if client use or not module 81=HS
        if (is_null($hasModule_81_HS) ) {
            if (is_null($this->_clientModules)) {
                $modules =  new Modules();
                $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
            }
            $hasModule_81_HS = $this->_clientModules[81];
        }
        
        if (is_null($hasModule_1005_PD) ) {
            if (is_null($this->_clientModules)) {
                $modules =  new Modules();
                $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
            }
            $hasModule_1005_PD = $this->_clientModules[1005];
        }
        
        //if null we get the types
        if (is_null($DiagnosisTypes)) {
            $abb = "'HD','ND'";
            if ($hasModule_81_HS) {
                $abb .= ",'HS'";
            }
            if ($hasModule_1005_PD) {
                $abb .= ",'PBD'";
            }
            $dt = new DiagnosisType();
            $DiagnosisTypes = $dt->getDiagnosisTypes($this->logininfo->clientid, $abb);
        }
        
        $diagnosis_type_id_options = array(); // this is used for the radio
        $hasModule_81_HS_value = null; //used for onChange, HS needs to be only one selected
        $diagnosis_type_id_columns = array('HD', 'HS', 'ND', 'PBD');
        foreach ($diagnosis_type_id_columns as $col) {
            foreach ($DiagnosisTypes as $row) {
                
                
                if ( ! $hasModule_81_HS && $row['abbrevation'] == 'HS')
                    continue;
                    
                    if ($hasModule_81_HS && $row['abbrevation'] == 'HS') {
                        $hasModule_81_HS_value = $row['id'];
                    }
                    
                    if ($row['abbrevation'] == $col) {
                        $diagnosis_type_id_options[$row['id']] = '';
                        break;
                    }
            }
        }
        
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators()->setDecorators(array('FormElements'));
        
        
        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
        
        $hidden_deleted_row = '';
        if (isset($values['id_deleted']) && ! empty($values['id']) && $values['id_deleted'] == $values['id']) {
            $hidden_deleted_row = 'display:hidden';
        }
        
        $diagnoSelector = uniqid("icdrow_");// used as class to delete 2 rows
        
        $subform->addElement('text', 'icd_primary', array(
            'label'      => null,
            'value'    => $values['icd_primary'],
            'required'   => false,
            'placeholder'=> 'ICD',
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element diagicd')),
                //array('Label', array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true , 'class' => "icd_holder_row {$diagnoSelector}")),
                ),
                'class' => 'diagicd livesearchicdinp',
                'data-livesearch' => 'icdnumber',
                'style' => $hidden_deleted_row,
                ));
        
        
        $subform->addElement('text', 'description', array(
            'label'        => null,
            'value'        => $values['description'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element diagdesc')),
                //array('Label', array('tag' => 'td')),
            // 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
            ),
            'class' => 'livesearchinp',
            'data-livesearch' => 'diagnosis',
            'style' => 'width: 100%; ',
            ));
            
            $subform->addElement('radio', 'diagnosis_type_id', array(
                //'label'        => null,
                'value'       => $values['diagnosis_type_id'],
                'multiOptions' => $diagnosis_type_id_options,
                'separator'    => '</td>'. PHP_EOL.'<td>',
                'required'     => true,
                'decorators'   => array(
                    'ViewHelper',
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    
                ),
                'data-livesearch' => 'diagnosis_type_id',
                'onChange' => ! $hasModule_81_HS ?: 'if (this.value == \''.$hasModule_81_HS_value.'\') { $(this).parents(\'table\').find("input:radio[value=\''.$hasModule_81_HS_value.'\']:not(\'#"+this.id+"\')").attr(\'checked\', false);}',
            ));
            
            $subform->addElement('note', 'delete_row', array(
                'value'  => '<a onclick="$(\'input[name*=\\\'id_deleted\\\']\', $(this).parents(\'tr\')).attr(\'disabled\', false); $(this).parents(\'tr\').hide(); $(\'input:text\', $(this).parents(\'tr\')).remove(); $(this).parents(\'table\').find(\'tr.user_holder_row.'.$diagnoSelector.'\').remove();" class="delete_row" title="'.$this->translate('delete row').'" href="javascript:void(0)"></a>',
                'escape' => false,
                'alt' => 'delete row',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'class' => 'align_center',
                    )),
                ),
            ));
            
            
            //add hidden
            $subform->addElement('hidden', 'id', array(
                'value' => $values['id'],
                'readonly' => true,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'class' => 'hidden')),
                ),
                'data-livesearch' => 'id'
            ));
            
            $subform->addElement('hidden', 'id_deleted', array(
                'value' => $values['id'],
                'readonly' => true,
                'disabled' => true,
                'decorators' => array('ViewHelper'),
                'data-livesearch' => 'id_deleted'
            ));
            
            
            $subform->addElement('hidden', 'diagnosis_id', array(
                'value' => $values['diagnosis_id'],
                'decorators' => array('ViewHelper'),
                'data-livesearch' => 'diagnosis_id'
            ));
            $subform->addElement('hidden', 'icd_id', array(
                'value' => $values['icd_id'],
                'decorators' => array('ViewHelper'),
                'data-livesearch' => 'icd_id'
            ));
            // 	    $subform->addElement('hidden', 'description', array(
            // 	        'value' => '',
            // 	        'decorators' => array('ViewHelper'),
            // 	        'data-livesearch' => 'description'
            // 	    ));
            $subform->addElement('hidden', 'date', array(
                'value' => $values['date'],
                'decorators' => array('ViewHelper'),
                'data-livesearch' => 'date'
            ));
            $subform->addElement('hidden', 'tabname', array(
                'value' => $values['tabname'],
                'data-livesearch' => 'tabname',
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
                    //array('Label', array('tag' => 'td')),
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
                ),
            ));
            
            
            
            if (is_null($this->_clientModules)) {
                $modules =  new Modules();
                $this->_clientModules = $modules->get_client_modules($this->logininfo->clientid);
            }
            
            //if module 180 then add user that performed
            if ($this->_clientModules[180])
            {
                $subformParticipant = $this->subFormTableRow(['class' => "user_holder_row {$diagnoSelector}"]);
                
                $subformParticipant->addElement('text', 'participant_name', array(
                    'belongsTo' => 'PatientDiagnosisParticipants',
                    'label'      => null,
                    'value'    => $values['PatientDiagnosisParticipants']['participant_name'],
                    'required'   => false,
                    'placeholder'=> $this->translate('user'),
                    'filters'    => array('StringTrim'),
                    'validators' => array('NotEmpty'),
                    'decorators' => array(
                        'ViewHelper',
                        array('Errors'),
                        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan' => 6)),
                        //array('Label', array('tag' => 'td')),
                    //     	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true, 'class' => "user_holder_row {$diagnoSelector}")),
                    ),
                    'class' => '',
                    'style' => $hidden_deleted_row,
                    'data-livesearch'      => 'unifiedProvider',
                    'data-livesearch_options'  => json_encode(['limitSearchGroups'=> ['user', 'voluntaryworker']]),
                    ));
                    
                    
                    //add hidden
                    $subformParticipant->addElement('hidden', 'participant_id', array(
                        'belongsTo' => 'PatientDiagnosisParticipants',
                        'value' => $values['PatientDiagnosisParticipants']['participant_id'],
                        'readonly' => true,
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'class' => 'hidden')),
                        ),
                        'data-livesearch' => 'id'
                    ));
                    
                    $subformParticipant->addElement('hidden', 'participant_type', array(
                        'belongsTo' => 'PatientDiagnosisParticipants',
                        'value' => $values['PatientDiagnosisParticipants']['participant_type'],
                        'readonly' => true,
                        'decorators' => array(
                            'ViewHelper',
                            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true, 'class' => 'hidden')),
                            //     	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
                        ),
                        'data-livesearch' => 'id'
                    ));
                    
                    $subform->addSubForm($subformParticipant, 'PatientDiagnosisParticipants');
                    
            }
            
            return $this->filter_by_block_name($subform, $__fnName);
            
    }
    
    
    
    
    
    
    
    
    
    public function create_form_icd($options = array(), $ipid){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

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
            'blockname' => "patientdiagnosis",
            'blocktitle' => "Diagnosen",
            'template' => 'form_block_patientdiagnosis.html',
            'formular_type' => $pdf,
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

    public function create_mre($blockname, $is_medical, $options){

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        $value = isset($options['value']) ? $options['value'] : '';
        $data = [];
        $data['value'] = $data;
        $data['options_array'] = Interventions::get_frequenz();
        $data['options_array'] = Interventions::get_frequenz();
        $data['options_array'] = PatientDiagnosis::get_frequenz();
        $data['intervention'] = $options['intervention'];

        $blockconfig = array(
            'blockname' => "MRE",
            'blocksuffix' => ($is_medical) ? 'medical' : 'nonmedical',
            'template' => 'patient_diagnosis_mre.phtml',
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