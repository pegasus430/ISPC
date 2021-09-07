<?php
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Class AokprojectsController
 * ISPC-2625, AOK Kurzassessment, 04.07.2020, elena
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class AokprojectsController extends Pms_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */

        //Check patient permissions on controller and action
        $patient_privileges = PatientPermissions::checkPermissionOnRun();

        if ( ! $patient_privileges) {
            $this->_redirect(APP_BASE . 'error/previlege');
        }

//         //    ISPC-791 secrecy tracker
//         $user_access = PatientPermissions::document_user_acces();

        //phtml is the default for zf1 ... but on bootstrap you manualy set html :(
        $this->getHelper('viewRenderer')->setViewSuffix('phtml');


        $this
            ->setActionsWithPatientinfoAndTabmenus([
                /*
                 * actions that have the patient header
                */
                'kurzassessment',
                'assessmentbenefitplan',
            ])
            ->setActionsWithJsFile([
                /*
                 * actions that will include in the <head>:  /public {_ipad} /javascript/views / CONTROLLER / ACTION .js"
                */
                'kurzassessment',
                'assessmentbenefitplan',
            ])
            ->setActionsWithLayoutNew([
                /*
                 * actions that will use layout_new.phtml
                * Actions With Patientinfo And Tabmenus also use layout_new.phtml
                */
                'kurzassessment',
                'assessmentbenefitplan',
            ])
        ;
    }



    private function _populateCurrentMessages()
    {
        $this->view->SuccessMessages = array_merge(
            $this->_helper->flashMessenger->getMessages('SuccessMessages'),
            $this->_helper->flashMessenger->getCurrentMessages('SuccessMessages')
        );
        $this->view->ErrorMessages = array_merge(
            $this->_helper->flashMessenger->getMessages('ErrorMessages'),
            $this->_helper->flashMessenger->getCurrentMessages('ErrorMessages')
        );

        $this->_helper->flashMessenger->clearMessages('ErrorMessages');
        $this->_helper->flashMessenger->clearCurrentMessages('ErrorMessages');

        $this->_helper->flashMessenger->clearMessages('SuccessMessages');
        $this->_helper->flashMessenger->clearCurrentMessages('SuccessMessages');
    }


    /**
     * ISPC-2292
     * MAMBO Assessment
     */
    public function kurzassessmentAction()
    {
        $ipid = $this->ipid;
        $step = null;
        if ($this->getRequest()->isPost()) {
            $step = $this->getRequest()->getPost('step', null);
        }
        if (is_null($step)) {
            $step = $this->getRequest()->getParam('step');
        }

        $_block_name = 'AokprojectsKurzassessment';

        /*
         * created $form->setPatientMasterDataReference($this->_patientMasterData);
         */

        $this->getMasterData_extradata($this->ipid, 'PatientReligions');

        $form = new Application_Form_AokprojectsKurzassessment(array(
            '_patientMasterData'    =>& $this->_patientMasterData,
            '_block_name'           => $_block_name,
//             '_clientForms'          => $clientForms,
            '_clientModules'        => $this->_patientMasterData['ModulePrivileges'],
//             '_client'               => ,
//             '_block_feedback_values'    =>& $_block_feedback_values,
        ));



        if ( ! $this->getRequest()->isPost()) {

            $this->view->usersnewtodos = Pms_CommonData::get_nice_name_multiselect($this->clientid, $include_all_option = false);


//             $csrfSessionNS = "Zend_Form_Element_Hash_{$_block_name}_{$this->ipid}_token";
//             if (Zend_Session::namespaceIsset($csrfSessionNS)) {
//                 $this->_helper->flashMessenger->addMessage( $this->translate('Another MamboAssesment was in progress for this patient, you will be able to save only one of them'),  'ErrorMessages');
//             }
            $assessment_id = $this->getRequest()->getParam('assessment_id');

            $saved_values = $this->_assessment_GatherDetails($assessment_id);

            $savdiagno = $saved_values['_page_2']['PatientDiagnosis'];
            $a_diagno = array();

            $abb = "'HD','ND'";
            if($this->_patientMasterData['ModulePrivileges']['81'])
            {
                $abb .= ",'HS'";
            }
            $dg = new DiagnosisType();
            $darr = $dg->getDiagnosisTypes($this->logininfo->clientid, $abb);

            $comma = ",";
            $ipidval = "'0'";

            if(is_array($darr))
            {
                foreach($darr as $key => $val)
                {
                    $ipidval .= $comma . "'" . $val['id'] . "'";
                    $comma = ",";
                }
            }

            foreach($savdiagno as $kr=>$vr)
            {
                $a_diagno[] = $vr;
            }

            /*ISPC - 2364 - sorted by user */
            $pdiaord = PatientDiagnoOrderTable::getInstance()->findDiagnoOrder($ipid);
            //RWH - ISPC-950
            //sort by icd
            if(empty($pdiaord))
            {
                $a_diagno = $this->array_sort($a_diagno, 'icd_primary', SORT_ASC);
                $a_diagno = array_values($a_diagno);

                //sort by type
                foreach($darr as $k_diag_type => $v_diag_type)
                {
                    foreach($a_diagno as $k_diag => $v_diag)
                    {
                        if($v_diag_type['id'] == $v_diag['diagnosis_type_id'])
                        {
                            $a_diagno_sorted[] = $v_diag;
                        }
                    }
                }
                //RWH end
            }
            else
            {
                $a_diagno = array_column($a_diagno, null, 'id');
                $pdorder = $pdiaord[0]['diagno_order'];

                $diagnaddother = array_diff(array_keys($a_diagno), $pdorder);
                $diagnremoveother = array_diff($pdorder, array_keys($a_diagno));

                if($diagnaddother)
                {
                    $pdorder = array_merge($pdorder, $diagnaddother);
                }

                if($diagnremoveother)
                {
                    $pdorder = array_diff($pdorder, $diagnremoveother);
                }

                $a_diagno_sorted = [];
                foreach ($pdorder as $pdid) {
                    $a_diagno_sorted[] = $a_diagno[$pdid];
                }
            }
            $a_diagno = $a_diagno_sorted;
            $a_diagno = array_column($a_diagno, null, 'id');

            $saved_values['_page_2']['PatientDiagnosis'] = $a_diagno;

            if (isset($saved_values['_block_feedback_values'])) {
                $form->setBlockFeedbackValuesReference($saved_values['_block_feedback_values']);
            }

            $form->create_form_mambo_assessment($saved_values);


        } else {


            //there is no validate implemented.. how to proceed with validate and print-pdf at the same time? you abort the print if invalid?

            $post = $this->getRequest()->getPost();
            if(strlen(trim($post['_page_5']['PatientDisabilityDegree']['expiration'])) == 0){
                // 09.07.2020 elena
                // in form right now field name is : _page_5[PatientDisabilityDegree][expiration]
                // change if you change page in form
                // "dummy" future date if expiration field is empty, to prevent DB Exception
                // @todo better solution required
                // maybe alter table and set NULL for expiration field ?
                $post['_page_5']['PatientDisabilityDegree']['expiration'] = '01.01.2200';
            }

            $current_page = (int)$post['__formular']['__current_page'];

            $form->create_form_mambo_assessment($post);


            //set status = completed
            if ($post['__formular']['__action'] == 'save_completed') {
                $post['__formular']['status'] = 'completed';
            }

            $assessment = null; //MamboAssessment

            //save the form
            switch($post['__formular']['__action']) {
                case 'save' :
                case 'save_completed' :
                case 'print_pdf' :

                    $assessment = $this->_assessment_save($form, $post);

                    $post['__formular']['id'] = $assessment->id;

                    break;
            }


            switch($post['__formular']['__action']) {

                case 'save' :

                    if ($assessment instanceof AokprojectsKurzassessment) {
                        $this->_assessment_save_patientcourse($assessment->id, "AOK Kurzassessment has been saved / changed");
                    }

                    $this->_helper->flashMessenger->addMessage( 'Assessment was saved',  'SuccessMessages');

                    $this->redirect(APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName()
                        . "?id={$this->enc_id}&page={$current_page}" );
                    exit;//for read-ability

                    break;


                case 'save_completed' :


                    if ($assessment instanceof AokprojectsKurzassessment) {

                        $this->_assessment_save_patientcourse($assessment->id, "AOK Kurzassessment (PDF) was finalized");

                        //create TODOs from the yellow checkboxes

                        $form->save_feedback_TODOs();

                        //add a patient_course entry ?

                        $this->_helper->flashMessenger->addMessage( $this->translate('Assessment was completed, it can be downloaded from the history table at the bottom'),  'SuccessMessages');

                        //save a final pdf
                        $patient_file_id = $this->_assessment_print($form, $post, 'ftp');

                        //add the id of the file to it
                        if ($patient_file_id) {
                            $assessment->_touched_ids = array_merge($assessment->_touched_ids, ['PatientFileUpload' => ['completed' => $patient_file_id]]);
                            $assessment->save();
                        }
                    }


                    $this->redirect(APP_BASE . $this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName()
                        . "?id={$this->enc_id}&page={$current_page}" );
                    exit;//for read-ability

                    break;


                case 'print_pdf' :

                    if ($assessment instanceof AokprojectsKurzassessment) {
                        $this->_assessment_save_patientcourse($assessment->id, "AOK Kurzassessment has been saved / changed");
                    }
                    $this->_assessment_print($form, $post, 'browser');

                    exit;//for read-ability

                    break;
            }
        }

        $this->_populateCurrentMessages();

        $this->view->form = $form;

    }


    private function _assessment_save_patientcourse($assessment_id = '', $course_title = '')
    {
        $cust = [
            'ipid' => $this->ipid,
            'course_date' => date("Y-m-d H:i:00", time()),
            'course_type' => "K",
            'course_title' => $this->translate($course_title),
            'user_id' => $this->logininfo->userid,
            'tabname' => AokprojectsKurzassessment::PATIENT_COURSE_TABNAME,
            'recordid' => $assessment_id,
            'done_id' => $assessment_id,
            'done_name' => AokprojectsKurzassessment::PATIENT_COURSE_DONE_NAME
        ];

        $pc_obj = new PatientCourse();
        $pc_obj->triggerformid = 0;
        $pc_obj->triggerformname = 0;

        return $pc_obj->set_new_record($cust);
    }

    private function _assessment_save(Application_Form_AokprojectsKurzassessment $form, $post = [])
    {

        /*
         * !! attention !!
         * only if csrf-token does not match we don't save
         * ele we ignore the validation result, and we save anyways
         */
        if ( ! 1 &&  ! $validator = $form->isValid($post) ) {

            //missingToken - not ok
            //isEmpty - not ok
            //notSame - not ok, but we allow to have same form, opened in multiple pages
            $errors = $form->getErrors();
            if (! empty($errors['formular']['token'][0]) && $errors['formular']['token'][0] == 'notSame') {

                $this->getHelper('Log')->error('MamboAssessment csrf token problem');
                //multi-submit or what? .. this is a fail
                $this->redirect(APP_BASE . "patientcourse/patientcourse?id={$this->enc_id}" , array("exit" => true));
            }

            foreach ($form->getMessages() as $page => $subform) {
                $this->_helper->flashMessenger->addMessage( "<br/><span class='subform' style='float:left; font-weight:bold;'>". $this->translate($page) . ": </span><br/>",  'ErrorMessages');
                foreach ($subform as $subformName => $err) {
                    $this->_helper->flashMessenger->addMessage( "<b>". $this->translate("[{$subformName} Box Name]") . ": </b><br><span>" . implode("<br>", array_unique(Pms_CommonData::array_flatten_v2($err))) . "</span>",  'ErrorMessages');
                }
            }

//             $err_messages = Pms_CommonData::array_flatten_v2($form->getMessages());
//             array_walk(array_unique($err_messages), function($err, $i){
//                 $this->_helper->flashMessenger->addMessage( $i . $err,  'ErrorMessages');
//             });
        }

        //this is a POST request method
        $assessment  = $form->save_form_mambo_assessment($this->ipid, $post);

        return $assessment;
    }

    /**
     *
     * @param Zend_Form $form
     * @param unknown $post
     * @param string $output (browser|ftp)
     */
    private function _assessment_print(Zend_Form $form, $post = [], $outputTo = 'browser')
    {

        $form->populate($post); //TODO check and remove this line


        /*
         * removed per request from ISPC-2292 14.02.2019 h)
         *
        //save canvas as image for pdf
        //TODO check dompdf bugfixes4svg and print as real svg
        //post['canvas_container'] is added on tab-change
        $canvas_container = (reset($post['canvas_container']));
        //get from post,
        $canvas_container = ! empty($canvas_container) ? $canvas_container : $post['_page_4']['Wound_Localization']['previous_w_localisation'];

        $tmp_file = Pms_CommonData::temporary_image_create($canvas_container, 'base64', 'human-huge');
        */

        //create medication2 block for print
        $medicationSettings = Pms_CommonData::getMedicationSettings($this->ipid, $this->logininfo->clientid);
        $htmlform_medication = Pms_Template::createTemplate(
            array_merge($medicationSettings, array( 'medication_block'  => $post['medication_block'])),
            'templates/contact_form_pdf_medication.phtml'
        );



        $remov_option_from_print_selectbox_options = array(
            $this->translate('pleaseselect'),
            $this->translate('please select'),
            '---'
        );

        $s1  = $form->getSubForms(); //s1 = pages = tabs

        foreach ($s1 as $subform) {

            //main form is grouped into tabs = pages = $subform
//             $subform->removeDecorator('Fieldset');//remove div id=page-%i%
//             $subform->removeDecorator('HtmlTag');//remove div id=page-%i%

            $s2 = $subform->getSubForms();
            foreach ($s2 as $sub_subform) {
                //each pages = $subform, is grouped into fieldsets = 1 block = $sub_subform

                switch ($sub_subform->getName()) {

                    case "Medications":

                        //this block is made in the form via ajax
                        //Medications block add
                        $sub_subform->addElement('note', 'print_medis', array(
                            'value'  => $htmlform_medication,
                            'escape' => false,
                            'decorators' => array(
                                'ViewHelper'
                            ),
                        ));

                        $sub_subform->removeDecorator('HtmlTag');//remove div.Medications2_holder_div
                        $sub_subform->removeDecorator('Fieldset');//remove Medikamente:

                        break;


                    case "Contact_Persons":
                    case "Specialists":
                    case "PatientPflegediensts":
                        //this contain multiple blocks of same structure, remove the main decorator


                        $child_block = $sub_subform->getSubForms();

                        $cntids = 0;
                        foreach ($child_block as $child) {
                            //add the id of the parent to his childrens
                            if ($decorator = $child->getDecorator('Fieldset')) {

                                $old_class = $decorator->getOption('class');
                                $decorator->setOption('class', 'one_'.$sub_subform->getName() . ' '. $old_class);

                                $old_id = $decorator->getOption('id');
                                $old_id = empty($old_id) ? $cntids++ : $old_id;
                                $decorator->setOption('id', $sub_subform->getName() . '_'. $old_id);

                            }

                        }

                        $sub_subform->removeDecorator('Fieldset');
                        $sub_subform->removeDecorator('HtmlTag');
                        break;

                }

                $sub_subform_Elements = $sub_subform->getElements();
                foreach ( $sub_subform_Elements as $element) {

                    //remove 'Please Seelect',Auswahl,'bitte wählen'
                    if ($element->getType() == 'Zend_Form_Element_Select') {

                        $listOptions = $element->getMultiOptions();
                        $label = $listOptions[$element->getValue()];

                        if (in_array($label, $remov_option_from_print_selectbox_options)) {
                            //$element->setValue("NULL");
                            // you can remove this
//                             dd($element->getName(), $label, $remov_option_from_print_selectbox_options);
                            $sub_subform->removeElement($element->getName());
                        }


                    }

                    switch ($element->getName()) {

                        /*
                         * removed per request from ISPC-2292 14.02.2019 h)
                         *
                        case "human_canvas_holder":
                            //add human body as image
                            $element->setValue('<img src="'. $tmp_file . '" />');
                            break;
                        */

                        case "addnew_contactperson":
                        case "addnew_specialist":
                        case "addnew_diagnosis":
                        case "addnew_patientpflegedienst":
                            //remove buttons for ajax/add new
                            $sub_subform->removeElement($element->getName());
                            break;

                    }
                }

            }
        }



        $today_date = date('d.m.Y');
        $nice_name_epid = $this->_patientMasterData['nice_name_epid'];


        $html_form  = $form->__toString();


        $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);



        $this->view->app_path = APPLICATION_PATH; // this is used for the css => app_path/../public/css/page-css/wlassessment_pdf.css

        $this->view->mambo_assessment_form = $html_form; //this is the body of the pdf

        $html_print = $this->view->render("templates/mambo_assessment_pdf.phtml");


//         $this->_helper->CreateDompdf($html_print);

//         dd($html_print);
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);

        //                 $dompdf = new Dompdf(array('isRemoteEnabled'=> false));
        $dompdf->loadHtml($html_print);
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');




        $dompdf->set_option("enable_php",true);
        $dompdf->set_option('defaultFont', 'times');
        $dompdf->set_option("fontHeightRatio",0.90);

        // Render the HTML as PDF
        $dompdf->render();


        // add the footer
        //TODO move this footer in to a config class, along with default font ant others
        $canvas = $dompdf->get_canvas();

        $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
        $footer_font_size = 10;

        $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
        $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
        $footer_text = "Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text


        $canvas->page_text(
            ($canvas->get_width() - $text_width)/2,
            $canvas->get_height()-30,
            $footer_text,
            $footer_font_family,
            $footer_font_size,
            array(0,0,0));




        $output = $dompdf->output();

        $file_title = $post['__formular']['status'] != 'completed' ? $this->translate(AokprojectsKurzassessment::PATIENT_FILE_TITLE) : $this->translate(AokprojectsKurzassessment::PATIENT_FILE_TITLE . " completed");

        $result = $this->dompdf_ToFTP($output, $file_title);

        if ($result !== false) {

            $encrypted = Pms_CommonData::aesEncryptMultiple(array(
                'title' => $file_title,
                'file_name' => $result,
                'file_type' => 'PDF',
            ));


            $entity = new PatientFileUpload ();
            //bypass triggers, we will use our own
            $entity->triggerformid = null;
            $entity->triggerformname = null;

            $entity->title = $encrypted['title'];
            $entity->ipid = $this->ipid;
            $entity->file_name = $encrypted['file_name']; //$post['fileinfo']['filename']['name'];
            $entity->file_type = $encrypted['file_type'];
            $entity->recordid = $post['__formular']['id'];
            $entity->tabname = MamboAssessment::PATIENT_FILE_TABNAME;


            $entity->system_generated = "0"; //TODO this should be 0?

            $entity->save();

        }

        if ($outputTo == 'browser') {

            // Output the generated PDF to Browser
            $dompdf->stream($this->translate(AokprojectsKurzassessment::PATIENT_FILE_TITLE), array('Attachment' => true));

            exit;

        }

        return $entity->id;



    }


    //clean filename and extension
    private function clean_filename( $filename =  "ispc_download.pdf")
    {
        //sanitize filename
        $filename = Pms_CommonData::filter_filename($filename, true);

        if ( ($ext = strtolower( substr($filename, strlen($filename) - 4, 4))) != '.pdf') {
            $filename .= '.pdf';
        }

        return $filename;
    }

    public function dompdf_ToFTP($dompdf_output ='', $pdfname = 'ispc_document.pdf'  )
    {

        $legacy_path    = "uploads";
        $is_zipped      = NULL;
        $foster_file    = false;
        $clientid       = NULL;
        $filepass       = NULL;

        $pdfname = $this->clean_filename($pdfname);

        $temp_folder_pdf = Pms_CommonData::uniqfolder_v2( PDF_PATH , 'dompdf_');

        $file_path = $temp_folder_pdf ."/". $pdfname ;

        @file_put_contents($file_path, $dompdf_output);

        $result =  Pms_CommonData::ftp_put_queue( $file_path , $legacy_path , $is_zipped, $foster_file, $clientid, $filepass);


        $file_path_for_db = false;

        if ($result !== false) {

            $pathinfo = pathinfo($file_path);
            $fulldir = $pathinfo['dirname'];
            $dir = pathinfo($fulldir , PATHINFO_BASENAME);

            $file_path_for_db = $dir . "/" . $pdfname;

        }

        return $file_path_for_db ;

    }






    private function _assessment_GatherDetails($assessment_id = 0)
    {
        $result = [];
        //print_r(AokprojectsKurzassessmentTable::getInstance());

        //exit();


        $saved_formular = AokprojectsKurzassessmentTable::getInstance()->findOneByIpidAndStatus($this->ipid, 'open', Doctrine_Core::HYDRATE_ARRAY);

        $__feedback_values = [];

        if ($saved_formular && $saved_formular['id']) {

//             dd($saved_formular['_touched_ids']);
            /*
             * we have one opened .. we continue editing this one..
             * also this one has the yellow labels from table AssessmentProblems
             */
            if ($AssessmentProblemsTable = AokprojectsKurzassessmentProblemsTable::getInstance()->findByAssessmentIdAndAssessmentName ($saved_formular['id'], 'AokprojectsKurzassessment', Doctrine_Core::HYDRATE_ARRAY)) {
                foreach ($AssessmentProblemsTable as $feedback_row) {
                    $__feedback_values[$feedback_row['parent_table']] [$feedback_row['assessment_name']] [$feedback_row['fn_name']] = $feedback_row;
                }
            }

            $this->setPatientMasterData(  $__feedback_values['PatientMaster'] , '__feedback_values' );


        } else {
            /*
             * no open ones, this will be a brand new one
             */
            $saved_formular = [];

            if ($assessment_id) {
                //you requested a formular that does not exist or was allreay marked as completed
                $this->_helper->flashMessenger->addMessage( $this->translate("You requested a formular that does not exist or was allready marked as completed") ,  'ErrorMessages');

            }
        }



        $allowed_formular_date = []; // this are NOT validated on submit... cause we validate nothing...
        $findFallsOfIpid = PatientReadmission::findFallsOfIpid($this->ipid);
        foreach ($findFallsOfIpid as $fall) {
            $allowed_formular_date[] = [
                'from' => ! empty($fall['admission']['date']) ? date("Y-n-j", strtotime($fall['admission']['date'])) : '', // this is for html-js
                'from_ID' => ! empty($fall['admission']['id']) ? $fall['admission']['id'] : '', //this next2 are used below for fetch dgp
                'from_date' => ! empty($fall['admission']['date']) ? $fall['admission']['date'] : '',

                'till' => ! empty($fall['discharge']['date']) ? date("Y-n-j", strtotime($fall['discharge']['date'])) : '',
                'till_ID' => ! empty($fall['discharge']['id']) ? $fall['discharge']['id'] : '',
                'till_date' => ! empty($fall['discharge']['date']) ? $fall['discharge']['date'] : '',

            ];
        }
        $saved_formular["__allowed_formular_date"] = $allowed_formular_date;








        /*
         * '_page_1' => [
                //PatientMaster self fetching
                'Nationality'               => $saved_Nationality, // from Stammdatenerweitert
                'PatientHealthInsurance'    => $saved_PatientHealthInsurance,
                'Familydoctor'              => $saved_Familydoctor,
                'Contact_Persons'           => $saved_Contact_Persons, // from ContactPersonMaster
                'PatientACP'                => $saved_PatientACP,
                'PatientMaintainanceStage'  => $saved_PatientMaintainanceStage,
                'PatientPflegediensts'      => $saved_PatientPflegediensts, //from PatientPflegedienst
                'Marital_status'            => $saved_Marital_status, //from Stammdatenerweitert
                'PatientLives'              => $saved_PatientLives, TODO
            ],
         */
        $stam = new Stammdatenerweitert();
        $stamarr = $stam->getStammdatenerweitert($this->ipid);

        if ($stamarr) {
            $stamarr = $stamarr[0];

            $saved_Nationality = $stamarr;

            $saved_Marital_status = ['familienstand' => $stamarr['familienstand']];
        }
        $saved_Nationality = $saved_Nationality ?: [];
        $saved_Marital_status = $saved_Marital_status ?: [];




        $saved = $this->getPatientMasterData('PatientHealthInsurance');
        if (empty($saved)) {
            $entity = new PatientHealthInsurance();
            $saved = $entity->getPatientHealthInsurance($this->ipid);
        }
        $saved_PatientHealthInsurance = ! empty($saved) ? reset($saved) : array();


        $saved_Familydoctor = array();
        if ($this->_patientMasterData['familydoc_id'] > 0) {
            $entity = new FamilyDoctor();
            $saved_Familydoctor = $entity->getTable()->find($this->_patientMasterData['familydoc_id'], Doctrine_Core::HYDRATE_ARRAY);
            FamilyDoctor::beautifyName($saved_Familydoctor);
        }


        $entity =  new ContactPersonMaster();
        $saved = $entity->getPatientContact($this->ipid);
        $entity->beautifyName($saved);
        $saved_Contact_Persons = $saved ?: [] ;




        $saved_PatientACP = array();
        $entity = new PatientAcp();
        $saved = $entity->getByIpid( array($this->ipid) );
        foreach($saved[$this->ipid] as $row) {
            $saved_PatientACP[$row['division_tab']] =  $row;
        }

        $current_ms_arr = PatientMaintainanceStage::getLastpatientMaintainanceStage($this->_patientMasterData['ipid']);
        $saved_PatientMaintainanceStage = ! empty($current_ms_arr) ? $current_ms_arr[0] : array();



        $entity = new PatientPflegedienste();
        $saved = $entity->gatAllPatientPflegedienstes($this->ipid);
        $saved_PatientPflegediensts = ! empty($saved) ? $saved : array();
        PatientPflegedienste::beautifyName($saved_PatientPflegediensts);


        $saved_PatientLivesV2 = Doctrine_Core::getTable('PatientLivesV2')->findOneByIpid($this->ipid, Doctrine_Core::HYDRATE_ARRAY);


        /*
         * '_page_2' => [
            'PatientDiagnosis'          => $saved_PatientDiagnosis ?: null,
            'PatientDiagnosisObserved'  => $saved_PatientDiagnosisObserved ?: null, // +
        ],
         */
        $entity = new PatientDiagnosis();
        $saved = $entity->getAllDiagnosis($this->ipid);
        $saved_PatientDiagnosis =  ! empty($saved[$this->ipid]) ? $saved[$this->ipid] : array();


        $saved_PatientDiagnosisObserved = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['PatientDiagnosisObserved'])) {
            $saved_PatientDiagnosisObserved = Doctrine_Core::getTable('PatientDiagnosisObserved')->findOneByIdAndIpid($saved_formular['_touched_ids']['PatientDiagnosisObserved'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
        }
        $saved_PatientDiagnosisObserved = $saved_PatientDiagnosisObserved ?: [];


        /*
         * '_page_3' => [
                'Wound_Type'                => $saved_Wound_Type ?: null, // from WoundDocumentation
                'Wound_Localization'        => $saved_Wound_Localization ?: null, // from WoundDocumentation
                'PatientDgpKern'            => $saved_PatientDgpKern ?: null,
                'PatientFeedbackVitalSigns' => $saved_PatientFeedbackVitalSigns ?: null,// +
                'PatientVices'              => $saved_PatientVices ?: null, // +
                'PatientRegularChecks'      => $saved_PatientRegularChecks ?: null, // +
            ],
         */

        $saved_WoundDocumentation = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['WoundDocumentation'])) {
            $entity = new WoundDocumentation();
            $saved = $entity->getTable()->findOneByIdAndIpid($saved_formular['_touched_ids']['WoundDocumentation'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
            $saved_WoundDocumentation = ! empty($saved) ? $saved : array();
            $saved_WoundDocumentation['w_type'] = ! empty($saved_WoundDocumentation['w_type']) ? explode(',', $saved_WoundDocumentation['w_type']) : array();
        }
        $saved_WoundDocumentation = $saved_WoundDocumentation ?: [];

        $patient_readmission_ID = 0;
        $form_type = 'adm';
        $saved_formular_datetime = null;
        if (empty($saved_formular['formular_date_start'])) {
            //use the latest dgp .. from the current fall
            $last_fall = end($saved_formular['__allowed_formular_date']);
            $patient_readmission_ID = $last_fall['from_ID'];

        } else {

            $saved_formular_datetime = strtotime($saved_formular['formular_date_start'] . ' '. "23:59:59");

            foreach ($saved_formular['__allowed_formular_date'] as $fall) {

                $admissionDate = empty($fall['from_date']) ? 0 : strtotime($fall['from_date']);
                $dischargeDate = empty($fall['till_date']) ? 0 : strtotime($fall['till_date']);

                if ( empty($dischargeDate) && $admissionDate <= $saved_formular_datetime) {
                    //current fall, is active,
                    $patient_readmission_ID = $fall['from_ID'];
                    break 1;

                } else if ($admissionDate <= $saved_formular_datetime && $saved_formular_datetime <= $dischargeDate) {
                    $patient_readmission_ID = $fall['from_ID'];
                    break 1;
                } else {
                    //error_day_notinrange
                }
            }
        }
        $saved_PatientDgpKern = Doctrine_Core::getTable('DgpKern')->findOneByIpidAndFormTypeAndPatientReadmissionId($this->ipid, $form_type, $patient_readmission_ID, Doctrine_Core::HYDRATE_ARRAY);
        if ( ! empty($saved_PatientDgpKern)) {
            $saved_PatientDgpKern['begleitung'] = explode(',', $saved_PatientDgpKern['begleitung'] );
        }


        $saved_PatientFeedbackVitalSigns = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['PatientFeedbackVitalSigns'])) {
            $saved_PatientFeedbackVitalSigns = Doctrine_Core::getTable('PatientFeedbackVitalSigns')->findOneByIdAndIpid($saved_formular['_touched_ids']['PatientFeedbackVitalSigns'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
        }
        $saved_PatientFeedbackVitalSigns = $saved_PatientFeedbackVitalSigns ?: [];

        if ( ! empty($saved_formular['formular_date_start'])) {
            $VitalSigns_Period = [
                'start' => date('Y-m-d', strtotime($saved_formular['formular_date_start'])),
                'end' => date('Y-m-d', strtotime($saved_formular['formular_date_start']))
            ];
        } else {
            $VitalSigns_Period = [
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d')
            ];
        }
        $vitalsigns = FormBlockVitalSigns::get_patients_chart($this->ipid, $VitalSigns_Period);
        if (! empty($vitalsigns[$this->ipid])) {
            $vitalsigns = $vitalsigns[$this->ipid];
            $vitalsigns_mambo = array_filter($vitalsigns, function ($i) {return $i['source'] == 'mambo_assessment';});
            $vitalsigns_mambo = ! empty($vitalsigns_mambo) ? end($vitalsigns_mambo) : end($vitalsigns);

            $saved_PatientFeedbackVitalSigns = array_merge($saved_PatientFeedbackVitalSigns, $vitalsigns_mambo);
        }




        $saved_PatientVices = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['PatientVices'])) {
            $saved_PatientVices = Doctrine_Core::getTable('PatientVices')->findOneByIdAndIpid($saved_formular['_touched_ids']['PatientVices'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
        }

        $saved_PatientRegularChecks = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['PatientRegularChecks'])) {
            $saved_PatientRegularChecks = Doctrine_Core::getTable('PatientRegularChecks')->findOneByIdAndIpid($saved_formular['_touched_ids']['PatientRegularChecks'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
        }



        /*
         * '_page_4' => [
                'PatientFeedbackMedication' => $saved_PatientFeedbackMedication ?: null, // +
                //Medication ajax pageload
            ],
         */
        $saved_PatientFeedbackMedication = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['PatientFeedbackMedication'])) {
            $saved_PatientFeedbackMedication = Doctrine_Core::getTable('PatientFeedbackMedication')->findOneByIdAndIpid($saved_formular['_touched_ids']['PatientFeedbackMedication'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
        }

        /*
         *  '_page_5' => [
                'PatientFeedbackGeneral'    => $saved_PatientFeedbackGeneral ?: null, // +
                'PatientFeedbackCareAids'   => $saved_PatientFeedbackCareAids ?: null, // +
            ],
         */
        $saved_PatientFeedbackGeneral = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['PatientFeedbackGeneral'])) {
            $saved_PatientFeedbackGeneral = Doctrine_Core::getTable('PatientFeedbackGeneral')->findOneByIdAndIpid($saved_formular['_touched_ids']['PatientFeedbackGeneral'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
        }

        $saved_PatientFeedbackCareAids = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['PatientFeedbackCareAids'])) {
            $saved_PatientFeedbackCareAids = Doctrine_Core::getTable('PatientFeedbackCareAids')->findOneByIdAndIpid($saved_formular['_touched_ids']['PatientFeedbackCareAids'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
        }



        /*
         * '_page_6' => [
                'PatientDisabilityDegree'   => $saved_PatientDisabilityDegree ?: null, // +
            ],
         */
        $saved_PatientDisabilityDegree = null;
        if ( ! empty($saved_formular['_touched_ids']) && ! empty($saved_formular['_touched_ids']['PatientDisabilityDegree'])) {
            $saved_PatientDisabilityDegree = Doctrine_Core::getTable('PatientDisabilityDegree')->findOneByIdAndIpid($saved_formular['_touched_ids']['PatientDisabilityDegree'], $this->ipid, Doctrine_Core::HYDRATE_ARRAY);
        }

        $result = [

            'formular'                      => $saved_formular,
            '_block_feedback_values'        => $__feedback_values,

            '_page_1' => [
                //PatientMaster self fetching
                'Nationality'               => $saved_Nationality ?: [], // from Stammdatenerweitert
                'PatientHealthInsurance'    => $saved_PatientHealthInsurance ?: [],
                'Familydoctor'              => $saved_Familydoctor ?: [],
                'Contact_Persons'           => $saved_Contact_Persons ?: [], // from ContactPersonMaster
                'PatientACP'                => $saved_PatientACP ?: [],
                'PatientMaintainanceStage'  => $saved_PatientMaintainanceStage ?: [],
                'PatientPflegediensts'      => $saved_PatientPflegediensts ?: [], //from PatientPflegedienst
                'Marital_status'            => $saved_Marital_status ?: [], //from Stammdatenerweitert
                'PatientLivesV2'            => $saved_PatientLivesV2 ?: [],
            ],

            '_page_2' => [
                'PatientDiagnosis'          => $saved_PatientDiagnosis ?: [],
                'PatientDiagnosisObserved'  => $saved_PatientDiagnosisObserved ?: [], // +
            ],

            '_page_3' => [
                'Wound_Type'                => $saved_WoundDocumentation ?: [], // from WoundDocumentation
                'Wound_Localization'        => $saved_WoundDocumentation ?: [], // from WoundDocumentation
                'PatientDgpKern'            => $saved_PatientDgpKern ?: [],
                'PatientFeedbackVitalSigns' => $saved_PatientFeedbackVitalSigns ?: [],// +
                'PatientVices'              => $saved_PatientVices ?: [], // +
                //'PatientRegularChecks'      => $saved_PatientRegularChecks ?: [], // +
            ],
/*
            '_page_4' => [
                'PatientFeedbackMedication' => $saved_PatientFeedbackMedication ?: [], // +
                //Medication ajax pageload
            ],*/

            '_page_4' => [
                'PatientFeedbackGeneral'    => $saved_PatientFeedbackGeneral ?: [], // +
                'PatientFeedbackCareAids'   => $saved_PatientFeedbackCareAids ?: [], // +
            ],

            '_page_5' => [
                'PatientDisabilityDegree'   => $saved_PatientDisabilityDegree ?: [], // +
            ],
        ];


        return $result;

    }



    private function _assessmentbenefitplan_GatherDetails()
    {
        $results = Doctrine_Core::getTable('AokprojectsKurzassessment')->createQuery('ma')
            ->select('ma.*, ap.*, aps.*, apc.*')
            ->leftJoin('ma.AokprojectsKurzssessmentProblems ap')
            ->leftJoin('ap.AokprojectsKurzassessmentProblemStatus aps')
            ->leftJoin('ap.AokprojectsKurzassessmentProblemCourse apc')
            ->where('ma.ipid = ?', $this->ipid)
           // ->andWhere('ap.assessment_name = ?', 'MamboAssessment')
            ->andWhere("ap.benefit_plan = 'yes'")
            ->fetchArray();

        //dd($results);
        return $results;
    }

    /**
     * ISPC-2293
     * MAMBO - Versorgungsplan
     */
    public function assessmentbenefitplanAction()
    {


        $step = null;
        if ($this->getRequest()->isPost()) {
            $step = $this->getRequest()->getPost('step', null);
        }
        if (is_null($step)) {
            $step = $this->getRequest()->getParam('step');
        }

        $_block_name = 'AssessmentProblems';



        $form = new Application_Form_AssessmentProblems(array(
            '_patientMasterData'    =>& $this->_patientMasterData,
            '_block_name'           => $_block_name,
            '_clientModules'        => $this->_patientMasterData['ModulePrivileges'],
        ));



        if ( ! $this->getRequest()->isPost()) {

            $saved_values = $this->_assessmentbenefitplan_GatherDetails();

            $form->create_form_problems_benefitplan($saved_values);


        } else {


            //there is no validate implemented.. how to proceed with validate and print-pdf at the same time? you abort the print if invalid?

            $post = $this->getRequest()->getPost();

            $current_page = (int)$post['__current_page'];

            $form->create_form_problems_benefitplan($post);


            $assessment = null; //MamboAssessment
            //save the form
            switch($post['__formular']['__action']) {

                case 'addNewCourse' :

                    reset($post['AssessmentProblems']);
                    $id = key($post['AssessmentProblems']);

                    if (is_array($post['AssessmentProblems'][$id]['AssessmentProblemCourse']['__new_course'])) {
                        $post['AssessmentProblems'][$id]['AssessmentProblemCourse']['__new_course']['assessment_problems_id'] = $id;
                    }

                    $form->save_form_problem_course_newentry($this->ipid, $post['AssessmentProblems'][$id]['AssessmentProblemCourse']['__new_course']);

                    break;

                case 'save' :

                    if ( ! ($validator = $form->isValid($post)) ) {

                        //missingToken - not ok
                        //isEmpty - not ok
                        //notSame - not ok, but we allow to have same form, opened in multiple pages
                        $errors = $form->getErrors();
                        if ( ! empty($errors['__formular']['__token'][0]) && $errors['__formular']['__token'][0] == 'notSame') {

                            $this->getHelper('Log')->error('MamboAssessment csrf token problem');
                            //multi-submit or what? .. this is a fail
                            $this->redirect(APP_BASE . "patientcourse/patientcourse?id={$this->enc_id}" , array("exit" => true));
                        }

                        foreach ($form->getMessages() as $page => $subform) {
                            $this->_helper->flashMessenger->addMessage( "<br/><span class='subform' style='float:left; font-weight:bold;'>". $this->translate($page) . ": </span><br/>",  'ErrorMessages');
                            foreach ($subform as $subformName => $err) {
                                $this->_helper->flashMessenger->addMessage( "<b>". $this->translate("[{$subformName} Box Name]") . ": </b><br><span>" . implode("<br>", array_unique(Pms_CommonData::array_flatten_v2($err))) . "</span>",  'ErrorMessages');
                            }
                        }
                    }

                    $savedObj = $form->save_form_problems_benefitplan($this->ipid, $post);

                    $this->_helper->flashMessenger->addMessage( 'Assessment was saved',  'SuccessMessages');

                    $this->redirect(APP_BASE . "mambo/assessmentbenefitplan?id={$this->enc_id}" , array("exit" => true));


                    exit;// for readability


                    break;

                case 'print_pdf' :

                    $saved_values = $this->_assessmentbenefitplan_GatherDetails();

                    $form->create_form_problems_benefitplan($saved_values);

                    $this->_assessmentbenefitplan_print($form, $post, 'browser');

                    exit;//for read-ability

                    break;
            }
        }

        $this->_populateCurrentMessages();

        $this->view->form = $form;

    }



    /**
     *
     * @param Zend_Form $form
     * @param unknown $post
     * @param string $output (browser|ftp)
     */
    private function _assessmentbenefitplan_print(Zend_Form $form)
    {

        $today_date = date('d.m.Y');
        $nice_name_epid = $this->_patientMasterData['nice_name_epid'];


        $html_form  = $form->__toString();

        $html_form =  Pms_CommonData::html_prepare_dompdf($html_form);



        $this->view->app_path = APPLICATION_PATH; // this is used for the css => app_path/../public/css/page-css/wlassessment_pdf.css

        $this->view->mambo_assessment_form = $html_form; //this is the body of the pdf

        $html_print = $this->view->render("templates/mambo_assessment_pdf.phtml");


        //         $this->_helper->CreateDompdf($html_print);
//         dd($html_print);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);

        //                 $dompdf = new Dompdf(array('isRemoteEnabled'=> false));
        $dompdf->loadHtml($html_print);
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');




        $dompdf->set_option("enable_php",true);
        $dompdf->set_option('defaultFont', 'times');
        $dompdf->set_option("fontHeightRatio",0.90);

        // Render the HTML as PDF
        $dompdf->render();


        // add the footer
        //TODO move this footer in to a config class, along with default font ant others
        $canvas = $dompdf->get_canvas();

        $footer_font_family = $dompdf->getFontMetrics()->get_font("helvetica");
        $footer_font_size = 10;

        $footer_text = "Seite: 1 von 1 | Datum: {$today_date} | {$nice_name_epid}"; // for align purpose i've used this
        $text_width = $dompdf->getFontMetrics()->getTextWidth($footer_text, $footer_font_family, $footer_font_size);
        $footer_text = "Seite: {PAGE_NUM} von {PAGE_COUNT} | Datum: {$today_date} | {$nice_name_epid}"; //footer text


        $canvas->page_text(
            ($canvas->get_width() - $text_width)/2,
            $canvas->get_height()-30,
            $footer_text,
            $footer_font_family,
            $footer_font_size,
            array(0,0,0));

        // Output the generated PDF to Browser
        $dompdf->stream($this->translate(AssessmentProblems::PATIENT_FILE_TITLE), array('Attachment' => true));

        exit;








    }

    private function array_sort($array, $on = NULL, $order = SORT_ASC)
    {
        $new_array = array();
        $sortable_array = array();

        if(count($array) > 0)
        {
            foreach($array as $k => $v)
            {
                if(is_array($v))
                {
                    foreach($v as $k2 => $v2)
                    {
                        if($k2 == $on)
                        {
                            if($on == 'birthd' || $on == 'admissiondate' || $on == 'admission_date' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date' || $on == "start_date")
                            {

                                if($on == 'birthdyears')
                                {
                                    $v2 = substr($v2, 0, 10);
                                }
                                $sortable_array[$k] = strtotime($v2);
                            }
                            elseif($on == 'epid')
                            {
                                $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v2);
                            }
                            elseif($on == 'percentage')
                            {
                                $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                            }
                            else
                            {
                                $sortable_array[$k] = ucfirst(trim($v2));
                            }
                        }
                    }
                }
                else
                {
                    if($on == 'birthd' || $on == 'admission_date' || $on == 'admissiondate' || $on == 'discharge_date' || $on == 'diedon' || $on == 'birthdyears' || $on == 'dischargedate' || $on == 'beginvisit' || $on == 'endvisit' || $on == 'dateofbirth' || $on == 'date' || $on == 'letter_date' || $on = "start_date")
                    {
                        if($on == 'birthdyears')
                        {
                            $v = substr($v, 0, 10);
                        }
                        $sortable_array[$k] = strtotime($v);
                    }
                    elseif($on == 'epid' || $on == 'percentage')
                    {
                        $sortable_array[$k] = preg_replace('/[^\d\s]/', '', $v);
                    }
                    elseif($on == 'percentage')
                    {
                        $sortable_array[$k] = preg_replace('/[^\d\.]/', '', $v2);
                    }
                    else
                    {
                        $sortable_array[$k] = ucfirst($v);
                    }
                }
            }

            switch($order)
            {
                case SORT_ASC:
                    //						asort($sortable_array);
                    $sortable_array = Pms_CommonData::a_sort($sortable_array);
                    break;
                case SORT_DESC:
                    //						arsort($sortable_array);
                    $sortable_array = Pms_CommonData::ar_sort($sortable_array);
                    break;
            }
            foreach($sortable_array as $k => $v)
            {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

}