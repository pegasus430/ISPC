<?php
//ISPC-2657, elena, 25.08.2020 (ELSA: Reaktionen)
//Maria:: Migration CISPC to ISPC 02.09.2020
class Application_Form_Reactions extends Pms_Form
{
    /**
     * @param array $options
     * @param $ipid
     * @param boolean $for_page //ISPC-2720, elena,30.11.2020
     * @return Zend_Form_SubForm
     */
    //ISPC-2831 Dragos 16.05.2021 added no_actions parameter
    public function create_form_reactions($options = array(), $ipid, $for_page = false, $no_actions = false){ //ISPC-2720, elena,30.11.2020
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        // update an existing contactform => loaded old values by ContactFormId
        if (isset($options['v'])) {
            $stored_data = json_decode($options['v'], true);
        } // use the post ones, maybe this is just a print
        else if (isset($options['formular_type'])) {
            $stored_data['reactions'] = $options['reactions'];
        }

        if (!$stored_data) {
            $stored_data['reactions'] = []; //ISPC-2657,elena,15.01.2021 //fix
        }

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $fn_options = $this->getFnOptions($__fnName);

        //print_r($fn_options);

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "reactions",
            'blocktitle' => "Reaktionen",
            'template' => 'form_block_reactions.html',
            'formular_type' => $pdf,
            'no_actions' => $no_actions, //ISPC-2831 Dragos 15.03.2021
        );

        $data = array();

        $data['f_values'] = $stored_data;

        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];

        $reactions_allergy = Reactions::getPatientReactionsByType($ipid, 'allergy');
        $reactions_intolerance = Reactions::getPatientReactionsByType($ipid, 'intolerance');
        $data['allergy'] = $reactions_allergy;
        $data['intolerance'] = $reactions_intolerance;
        $data['sae_reactions'] = SaeReactions::getPatientSaeReactions($ipid);

        return $this->create_simple_auto_add_block($blockconfig, $data, $for_page); //ISPC-2720, elena, 30.11.2020

    }

    /**
     * ISPC-2657,elena,14.01.2021
     *
     * @param array $options
     * @param $ipid
     * @param boolean $for_page
     * @return Zend_Form_SubForm
     */
    public function create_contact_form_reactions($options = array(), $ipid, $for_page = false){ //ISPC-2720, elena,30.11.2020
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_CommonData::getIdfromIpid($ipid);
        $encid = Pms_Uuid::encrypt($decid);

        if(isset($options['reactions']['allergy'])){
            $reactions_allergy = $options['reactions']['allergy'];
            $reactions_intolerance = $options['reactions']['intolerance'];
            $reactions_sae = $options['sae_reactions'];
        }else{
            $reactions_sae = $options['sae'];
            $reactions_allergy = [];
            $reactions_intolerance = [];
            foreach($options['reaction'] as $reaction){
                if($reaction['typ'] == 'allergy'){
                    $reactions_allergy[] = $reaction;
                }elseif($reaction['typ'] == 'intolerance'){
                    $reactions_intolerance[] = $reaction;
                }
            }

        }


        if (isset($options['formular_type'])) {
            $stored_data['reactions'] = $options['reactions'];
        }

        if (!$stored_data) {
            $stored_data['reactions'] = [];
        }

        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $fn_options = $this->getFnOptions($__fnName);

        //print_r($fn_options);

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';

        $blockconfig = array(
            'blockname' => "reactions",
            'blocktitle' => "Reaktionen",
            'template' => 'contact_form_block_reactions.html',
            'formular_type' => $pdf,
        );

        $data = array();

        $data['f_values'] = $stored_data;

        $data['expanded'] = $fn_options['expanded'];
        $data['opened'] = $fn_options['opened'];


        $data['allergy'] = $reactions_allergy;
        $data['intolerance'] = $reactions_intolerance;
        $data['sae_reactions'] = $reactions_sae;

        return $this->create_simple_auto_add_block($blockconfig, $data, $for_page); //ISPC-2720, elena, 30.11.2020

    }


    /**
     * ISPC-2657,elena,15.01.2020
     * @param null $ipid
     * @param array $data
     */
    public function save_contact_form_reactions($ipid = null, $data = array()){
        if (empty($ipid) || empty($data)) {
            return;
        }
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        foreach($data['reaction'] as $react){
            $reaction = new Reactions();
            if($react['id'] !== 'new'){
                $reaction->id = $react['id'];
            }
            $reaction->ipid = $ipid;
            $reaction->clientid = $clientid;
            $reaction->typ = $react['typ'];
            $reaction->reaction_against =  $react['reaction_against'];
            $reaction->reaction_text = $react['reaction_text'];
            $reaction->icdcode = $react['icdcode'];
            $reaction->first_diagnosis_date = $react['first_diagnosis_date'];
            $reaction->first_diagnosis_date_knowledge = $react['first_diagnosis_date_knowledge'];
            if($react['id'] !== 'new'){
                $reaction->replace();
            }else{
                $reaction->save();
            }
        }
        foreach($data['sae'] as $react){
            $reaction = new SaeReactions();
            if($react['id'] !== 'new'){
                $reaction->id = $react['id'];
            }
            $reaction->ipid = $ipid;
            $reaction->clientid = $clientid;


            $reaction->reaction_text = $react['reaction_text'];
            $reaction->consequence = $react['consequence'];
            $reaction->study = $react['study'];
            $reaction->cause = $react['cause'];
            $reaction->place = $react['place'];
            $reaction->first_sae_date = $react['first_sae_date'];
            $reaction->first_sae_date_knowledge = $react['first_sae_date_knowledge'];
            if($react['id'] !== 'new'){
                $reaction->replace();
            }else{
                $reaction->save();
            }
        }

        foreach($data['reactions_remove'] as $to_remove){
            $reaction = new Reactions();
            $reaction->id = $to_remove;
            $reaction->deleteReaction();
        }
        foreach($data['saereactions_remove'] as $to_remove){
            $reaction = new SaeReactions();
            $reaction->id = $to_remove;
            $reaction->deleteSaeReaction();
        }


    }


    /**
     * @param $blockconfig
     * @param array $data
     * @param boolean $for_page //ISPC-2720, elena,30.11.2020
     * @return Zend_Form_SubForm
     * @throws Zend_Form_Exception
     */
    public function create_simple_auto_add_block($blockconfig, $data = array(), $for_page = false) //ISPC-2720, elena,30.11.2020
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
        //ISPC-2720, elena,30.11.2020
        // if we need it for page only, return rendered html only
        if($for_page){
            return $html;
        }
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
        return $subform;

    }

    /**
     * creates ui for date / date knowledge input
     *
     * @param $blockname
     * @param $field
     * @param $options
     * @return string
     */
    public function create_date_ui($blockname, $field, $options){

        $pdf = ($options['formular_type'] == 'pdf') ? 'pdf' : '';
        $value = isset($options['value']) ? $options['value'] : '';
        $data = $options;
        //$data['value'] = $data;
        $data['options_array'] = [];
        if($field == 'first_sae_date'){
            $data['options_array']  = SaeReactions::getDateKnowledgeOption();
        }elseif($field == 'first_diagnosis_date'){
            $data['options_array'] = Reactions::getDateKnowledgeOption();
        }
        $data['reactions'] = $options['reactions'];

        $blockconfig = array(
            'blockname' => "reactions",
            'blockfield' => $field,
            'template' => 'reactions_date.html',
            'formular_type' => $pdf,
        );

        return $this->create_subform_ui($blockconfig, $data );


    }

    /**
     * @param $blockconfig
     * @param $data
     * @return string
     */
    public function create_subform_ui($blockconfig,  $data){

        $newview = new Zend_View();
        //$newview->pdf = $pdf;

        foreach ($data as $key=>$value){
            $newview->$key = $value;
        }

        $newview->blockconfig = $blockconfig;
        $newview->setScriptPath(APPLICATION_PATH . "/views/scripts/templates/");
        $html = $newview->render($blockconfig['template']);

        return $html;

    }

    /**
     * @param $post
     * @return Reactions|SaeReactions
     * @throws Exception
     */
    public function insertData($post){
        $cust = $this->mapFormFields($post);
        $cust->save();
        return $cust;

    }

    /**
     * @param $post
     * @return Reactions|SaeReactions
     * @throws Doctrine_Connection_Exception
     */
    public function UpdateData($post)
    {
        $cust = $this->mapFormFields($post);
        $cust->id = $post['id'];

        $cust->replace();
        return $cust;
    }

    /**
     * @param $post
     * @return array
     */
    public function validate($post){
        $Tr = new Zend_View_Helper_Translate();
        $validation = new Pms_Validation();

        $aErrors = [];
        if((isset($post['kind']) && $post['kind'] == 'reactions') || isset($post['typ'])){
            if(strlen(trim($post['icdcode'])) == 0){
                $aErrors['icdcode'] = $Tr->translate('reaction_please_choose_icd');
            }
            if(strlen(trim($post['reaction_text'])) == 0){
                $aErrors['reaction_text'] = $Tr->translate('reaction_please_fill_reaction');
            }

             if(strlen(trim($post['reaction_against'])) == 0){
                 if($post['typ'] == 'reaction'){
                     $aErrors['reaction_against'] = $Tr->translate('reaction_please_fill_reaction_against');
                 }else{
                     $aErrors['reaction_against'] = $Tr->translate('reaction_please_fill_intolerance_against');
                 }

            }


        }elseif((isset($post['kind']) && $post['kind'] == 'sae') ){
            if(strlen(trim($post['first_sae_date'])) == 0){
                $aErrors['first_sae_date'] = $Tr->translate('reaction_please_date');
            }
            if(strlen(trim($post['reaction_text'])) == 0){
                $aErrors['reaction_text'] = $Tr->translate('reaction_please_fill_reaction');
            }

        }
        return $aErrors;
    }

    /**
     * @param $post
     * @return Reactions|SaeReactions
     */
    protected function mapFormFields($post){
        if((isset($post['kind']) && $post['kind'] == 'reactions') || isset($post['typ'])){
            $cust = new Reactions();
            $cust->id = $post['id'];
            $cust->typ = $post['typ'];
            $cust->ipid = $post['ipid'];
            $cust->clientid = $post['clientid'];
            $cust->icdcode = $post['icdcode'];
            $cust->reaction_against = $post['reaction_against'];
            $cust->reaction_text = $post['reaction_text'];
            $cust->first_diagnosis_date = $post['first_diagnosis_date'];
            $cust->first_diagnosis_date_knowledge = $post['first_diagnosis_date_knowledge'];
            return $cust;

        }else{
            $cust = new SaeReactions();

            $cust->id = $post['id'];
            $cust->ipid = $post['ipid'];
            $cust->clientid = $post['clientid'];

            $cust->study = $post['study'];
            $cust->cause = $post['cause'];
            $cust->place = $post['place'];
            $cust->consequence = $post['consequence'];
            $cust->reaction_text = $post['reaction_text'];
            $cust->first_sae_date = $post['first_sae_date'];
            $cust->first_sae_date_knowledge = $post['first_sae_date_knowledge'];
            return $cust;
        }
    }

    /**
     * Generates PDF
     *
     * @param null $patientname
     * @param null $birthd
     * @param null $renderedHTML
     * @throws Zend_Session_Exception
     */
    public function generate_pdf($patientname= null, $birthd = null,$renderedHTML = null){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;

        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
        $epid = Pms_CommonData::getEpid($ipid);
        // the data can be already known (and we can spare DB access), if not, then get it
        if($patientname == null || $birthd == null){
            $patientmaster = new PatientMaster();
            $this->view->patientinfo = $patientmaster->getMasterData($decid, 1);
            $patientinfo = $patientmaster->getMasterData($decid, 0);
            $patientname = $patientinfo['last_name'].' '.$patientinfo['first_name'];
            $birthd = $patientinfo['birthd'];

        }

        if($renderedHTML == null){
            $options['formular_type'] = 'pdf';
            $form = $this->create_form_reactions($options, $ipid);
            $__formHTML = $form->render();
            $__formPDF = Pms_CommonData::html_prepare_fpdf(Pms_CommonData::html_prepare_dompdf($__formHTML, '12px', 'auto', false));


        }else{
            $__formPDF = $renderedHTML;
        }


        $options = [];
        $options['customheader'] = $patientname . ', ' . $birthd;
        $footer_text = "Seite %s von %s ";
        $options['footer_text'] = $footer_text;
        $options['footer_type'] = '1 of n date';
        $pdfid = Pms_PDFUtil::generate_pdf_to_patient_file($__formPDF, 'Reaktionen', 'Reaktionen', $ipid, $options );

        $cust = new PatientCourse();
        $cust->ipid = $ipid;
        $cust->course_date = date('Y-m-d H:i:s');
        $cust->course_type = Pms_CommonData::aesEncrypt('K');
        $cust->course_title = Pms_CommonData::aesEncrypt(addslashes('Reaktionen'));
        $cust->user_id = $logininfo->userid;
        $cust->tabname = Pms_CommonData::aesEncrypt("fileupload");
        $cust->recordid = $pdfid;
        $cust->save();

    }


}
