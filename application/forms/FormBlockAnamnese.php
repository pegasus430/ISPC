<?php
//ISPC-2694, elena, 11.12.2020

class Application_Form_FormBlockAnamnese extends Pms_Form
{
    protected $_model = 'Anamnese';

    public $blockconfig = array(
        'blockname' => "FormBlockAnamnese",
        'blocktitle' => "Anamnese",
        'template' => 'form_block_anamnese.html',
        'formular_type' => null,
    );
    const BLOCK_ANAMNESE = 'FormBlockAnamnese';


    public function create_html_form_anamnese($options = array(), $ipid){

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        //print_r($options);
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        $stored_data['anamnese'] = $options['anamnese'];
        $stored_data['stammdaten'] = $options['stammdaten'];
        if(is_string($stored_data['anamnese']['childhood_diseases'])){
            $stored_data['anamnese'] = $this->map_saved_data($stored_data['anamnese']);
        }

        //print_r($stored_data['anamnese']);
        /*
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['anamnese'] = $options['anamnese'];
        }*/

        if (!$stored_data) {
            $stored_data['anamnese'] = [];
        }


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $fn_options = $this->getFnOptions($__fnName);

        //print_r($fn_options);

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $data = array();

        $data['f_values'] = $stored_data;

        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];
        if(isset($stored_data['stammdaten'])){
            $data['stammdaten'] = $stored_data['stammdaten'];
        }else{
            $data['stammdaten'] = false;
        }

        $this->blockconfig['formular_type'] = $pdf;


        $newview = new Zend_View();


        $newview->setScriptPath(APPLICATION_PATH."/views/scripts/templates/");

        $newview->pdf = $pdf;

        foreach ($data as $key=>$value){
            $newview->$key = $value;
        }

        $newview->setupoptions = Anamnese::getGroups();

        $newview->blockconfig = $this->blockconfig;

        $blockoptions = array();

        if(isset($data['class'])){
            $blockoptions['class'] = $data['class'];
        }
        if(isset($data['opened'])){
            $blockoptions['opened'] = $data['opened'];
        }

        $html = $newview->render($this->blockconfig['template']);

        return $html;

    }


    public function create_form_anamnese($form_options = array(), $ipid){

        $html = $this->create_html_form_anamnese($form_options, $ipid);
        $__fnName = __FUNCTION__;
        $options = array();
        $blockoptions = array();
        $fn_options = $this->getFnOptions($__fnName);

        //print_r($fn_options);

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';


        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];

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

        if( $this->blockconfig['formular_type'] == 'pdf'){
            $subform->addDecorator('SimpleContactformBlockPdf');
        }else {
            $subform->addDecorator('SimpleContactformBlock', $blockoptions);
        }
        $subform->setLegend($this->blockconfig['blocktitle']);
        $subform->setAttrib('class', 'expanded');

        $subform->addElement('note', 'block_' . $this->blockconfig['blockname'], array(
            'value' => $html,
            'decorators' => array(
                'SimpleTemplate',
            ),
        ));

        return $subform;

    }


    public function map_saved_data($data){
        $data['childhood_diseases'] = json_decode($data['childhood_diseases'], true);
        $data['birth_anamnese'] = json_decode($data['birth_anamnese'], true);
        $data['development_anamnese'] = json_decode($data['development_anamnese'], true);
        return $data;
    }

    public function save_form_anamnese($ipid, $data_post){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $anamnese = new Anamnese();
        if(isset($data_post['contact_form_id'])){
            $anamnese->contact_form_id = $data_post['contact_form_id'];
        }else{
            $anamnese->contact_form_id = 0;
        }
        $anamnese_data = $data_post['anamnese'];
        if(!isset($data_post['anamnese'])){
            $anamnese_data = $data_post['Anamnese'];
        }

        $anamnese->ipid = $ipid;
        $anamnese->isdelete = 0;
        if(strlen(trim($anamnese_data['datum'])) >0 ){
            $datum = date_create_from_format('d.m.Y', $anamnese_data['datum']);
            $anamnese->datum = $datum->format('Y-m-d');
        }

        $anamnese->clientid = $clientid;
        $anamnese->childhood_diseases = json_encode($anamnese_data['childhood_diseases']);
        $anamnese->birth_anamnese = json_encode($anamnese_data['birth_anamnese']);
        $anamnese->development_anamnese = json_encode($anamnese_data['development_anamnese']);
        $anamnese->extra_anamnese = '';
        $anamnese->datum = $anamnese_data['datum'];
        //i save each version as new entry, because these are the same data and you want probably to see always a current (last) version
        $anamnese->save();
        return true;
        /*
        if($anamnese->contact_form_id > 0){
            $anamnese->replace();
        }else{
            $anamnese->save();
        }*/


    }

    public function create_stammdaten_box_anamnese($values =  array() , $elementsBelongTo = null){

        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpId($decid);
        $options = [];
        $options['anamnese'] = Anamnese::getLastBlockValues( $ipid)[0];
        $options['stammdaten'] = true;
        $this->blockconfig['blockname'] = 'patientDetails';

        //$this->blockconfig['formular_type'] = 'pdf';
        $html = $this->create_html_form_anamnese($options, $ipid);
        $__fnName = __FUNCTION__;

        $blockoptions = array();
        $fn_options = $this->getFnOptions($__fnName);
        $this->mapSaveFunction($__fnName, "save_form_anamnese");


        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
        //$subform->clearDecorators();
        $subform->addPrefixPath('Zend_Form_Decorator_', APPLICATION_PATH . '/forms/decorator/', 'DECORATOR');
        $subform->setDecorators(array('FormElements', array('SimpleTemplate', $options),));
        $subform->setLegend('Anamnese');
        $subform->setAttrib("class", "label_same_size_180 multipleCheckboxes inlineEdit " . __FUNCTION__);


        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }


        $subform->addElement('note', 'block_' . 'conf', array(
            'value' => '<div style="position:relative">' . $html . '</div>',
            'decorators' => array(
                'SimpleTemplate',
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

        return $this->filter_by_block_name($subform, __FUNCTION__);



    }

    /**
     * ISPC-2694, elena, 07.01.2021
     *
     * @param array $values
     * @param $elementsBelongTo
     * @return string
     */
    public function createViewAnamnese($values =  array() , $elementsBelongTo = nul)
    {
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpId($decid);
        $options = [];
        $options['anamnese'] = Anamnese::getLastBlockValues($ipid)[0];
        //echo 'anamnsese';
        //print_r($options['anamnese']);
        if(!is_array($options['anamnese'])){
            return '';
        }

        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        //print_r($options);
        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);
        // update an existing contactform => loaded old values by ContactFormId
        $stored_data['anamnese'] = $options['anamnese'];
        $stored_data['stammdaten'] = $options['stammdaten'];
        if (is_string($stored_data['anamnese']['birth_anamnese'])) {
            $stored_data['anamnese'] = $this->map_saved_data($stored_data['anamnese']);
        }

        //print_r($stored_data['anamnese']);
        /*
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['anamnese'] = $options['anamnese'];
        }*/

        if (!$stored_data) {
            $stored_data['anamnese'] = [];
        }


        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $fn_options = $this->getFnOptions($__fnName);

        //print_r($fn_options);

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $data = array();

        $data['f_values'] = $stored_data;

        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];
        if (isset($stored_data['stammdaten'])) {
            $data['stammdaten'] = $stored_data['stammdaten'];
        } else {
            $data['stammdaten'] = false;
        }

        $this->blockconfig['formular_type'] = $pdf;

        $this->blockconfig['formular_type'] = $pdf;
        $this->blockconfig['template'] = 'view_data_anamnese.html';


        $newview = new Zend_View();


        $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");

        $newview->pdf = $pdf;

        foreach ($data as $key => $value) {
            $newview->$key = $value;
        }

        $newview->setupoptions = Anamnese::getGroups();

        $newview->blockconfig = $this->blockconfig;

        $blockoptions = array();

        if (isset($data['class'])) {
            $blockoptions['class'] = $data['class'];
        }
        if (isset($data['opened'])) {
            $blockoptions['opened'] = $data['opened'];
        }

        $html = $newview->render($this->blockconfig['template']);

        return $html;
    }





    }