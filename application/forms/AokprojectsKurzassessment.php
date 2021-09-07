<?php

/**
 * Class Application_Form_AokprojectsKurzassessment
 * ISPC-2625, AOK Kurzassessment, 07.07.2020, elena
 * cloned from MamboAssessment
 * Maria:: Migration CISPC to ISPC 22.07.2020
 */
class Application_Form_AokprojectsKurzassessment extends Pms_Form
{
    protected $_model = 'AokprojectsKurzassessment';

    private $triggerformid = AokprojectsKurzassessment::TRIGGER_FORMID;
    private $triggerformname = AokprojectsKurzassessment::TRIGGER_FORMNAME;
    protected $_translate_lang_array = AokprojectsKurzassessment::LANGUAGE_ARRAY;

    /**
     *
     * @var AokprojectsKurzassessment
     */
    private $_assessmentEntity = null;

    /**
     *
     * @var array
     */
    private $_feedbackOptions = [];




    protected $_block_feedback_options = [
        "AokprojectsKurzassessment" => [
            '_create_formular_commitment' => [
                "todo",
                "feedback",
                //"benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],

            '_create_formular_services_usage' => [
                "todo",
                "feedback",
                //"benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],

            '_create_formular_contact_location' => [
                "todo",
                "feedback",
                //"benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],

            '_create_formular_contact_type' => [
                "todo",
                "feedback",
                //"benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //     	        "further_assessment",
                //     	        "training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //     	        "inclusion_measures",
            ],
        ],
    ];





    /*
	$this->_categoriesForms['Stammdatenerweitert'] = [
	    "__form"   => $af_s, //zend_form
	    "__fn"     => [ // this is not really necesary since i can use ->getSaveFunction() to know them all..
	        'create_form_nationality' => [
	            "__page"   => $__fnName,
	            "__key"    => 'Nationality',
	            "__isMultiple" => false , // this is for Contactperson,
	        ],
	    ],
	];
	*/
    private $_categoriesForms = null;


    public function isValid($data)
    {
        return parent::isValid($data);
    }


    private function _create_formular_actions($options = array(), $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->clearDecorators()
            ->setDecorators( array(
                'FormElements',
                array('HtmlTag',array('tag'=>'div', 'class' => 'formular_actions dontPrint')),
            ));


        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }



        $el = $this->createElement('button', '__action', array(
            'type'         => 'submit',
            'value'        => 'save',
// 	        'content'      => $this->translate('submit'),
            'label'        => $this->translate('submit'),
// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
            'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
            'decorators'   => array('ViewHelper'),
            'class'        => 'btnSubmit2018 dontPrint',

        ));
// 	    dd($el->getAttrib('content'));
        $subform->addElement($el, 'save');


        $el = $this->createElement('button', '__action', array(
            'type'         => 'submit',
            'value'        => 'print_pdf',
// 	        'content'      => $this->translate('generatepdf'),
            'label'        => $this->translate('save AND Print'),
// 	        'onclick'      => '$(this).parents("form").attr("target", "_blank"); if(checkclientchanged(\'wlassessment_form\')){ setTimeout("window.location.reload()", 1000); return true;} else {return false;}',
            'onclick'      => '$(this).parents("form").attr("target", "_blank"); window.formular_button_action = this.value;',
            'decorators'   => array('ViewHelper'),
            'class'        => 'btnSubmit2018 print_icon_bg dontPrint',

        ));
        $subform->addElement($el, 'print_pdf');


        $el = $this->createElement('button', '__action', array(
            'type'         => 'submit',
            'value'        => 'save_completed',
            // 	        'content'      => $this->translate('generatepdf'),
            'label'        => $this->translate('save and mark-as-completed'),
            'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value; return confirm(translate("You sure you want to mark as completed this formular?)");',
            'decorators'   => array('ViewHelper'),
            'class'        => 'btnSubmit2018 dontPrint',

        ));
        $subform->addElement($el, 'save_completed');



        $el = $this->createElement('button', '__action', array(
            'type'         => 'button',
            'value'        => 'qtip_toggler',
            // 	        'content'      => $this->translate('generatepdf'),
            'label'        => $this->translate('Toggle Feedback Qtip'),
            'onclick'      => 'feedbackQtipToggle(); return false;',
            'decorators'   => array('ViewHelper'),
            'class'        => 'btnSubmit2018 dontPrint no_bg qtip_toggler',

        ));
// 	    $subform->addElement($el, 'toggle_feedback');


        //Add csrf here and not in [formular], for easier test
        $subform->addElement('hash', 'token', array(
            'ignore'       => true,
            //'session'     => new Zend_Session_Namespace("hashu"),
            // 		    'strict'       => false,
            'timeout'      => 3600, // it takes more than 1h to edit this?
            'salt'         => "{$this->_block_name}_{$this->_patientMasterData['ipid']}",
            'decorators'   => array('ViewHelper'),
        ));

        //current selected tab
        $subform->addElement('hidden', '__current_page', array(
            'label'        => null,
            'value'        => $options['__current_page'] ?: 0,
            'required'     => false,
            'readonly'     => true,
            'decorators'   => array('ViewHelper'),
            'class'        => 'assessment_current_page',
        ));

        /*
         // Add a captcha
         $subform->addElement('captcha', 'captcha', array(
         'label'      => 'Please enter the 5 letters displayed below:',
         'required'   => true,
         'captcha'    => array(
         'captcha' => 'Figlet',
         'captcha' => 'Dumb',
         // 	            'captcha' => 'Image', // config font
         // 	            'captcha' => 'ReCaptcha', //config key
         'wordLen' => 5,
         'timeout' => 300
         ),
         'decorators' => array(
         'ViewHelper',
         array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
         array(array('row' => 'HtmlTag'), array('tag' => 'tr', )),
         ),
         ));
        */


        return $subform;

    }


    public function create_form_mambo_assessment( $options = array(), $elementsBelongTo = null)
    {

        $max_pages = 10; // maximum number of tabs... you just need to create private function _page_67(){}, private function _page_68(){} ..

        $tabs_counter = 0;

        for($i=1; $i<= $max_pages; $i++) {

            $fn_name = "_page_{$i}";

            if (method_exists($this, $fn_name)) {
                $tabs_counter++;
            }
        }



        //@todo ! re-move this into the view ! if you intend to append this into your form
// 		$this->setMethod(self::METHOD_POST);
// 		$this->setAttrib("id", "wlassessment_form");
// 		$this->setAttrib("class", "wlassessment_form_class livesearchZipCities livesearchHealthInsurance livesearchDiagnosisIcd livesearchFamilyDoctor");
// 		$this->setAttrib("onsubmit", "return checkclientchanged('wlassessment_form');");

        $this->setDecorators(array(
            'FormElements',
// 		    array('HtmlTag',array('tag' => 'table')),
            'Form'
        ));



        if ( ! is_null($elementsBelongTo)) {
            $this->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }

        //add navigation tabs to this form
        $tabs_navi = $this->_tabs_navigation($tabs_counter);
        $this->addSubform($tabs_navi, 'tabs_navi');



        //add pages
        for($i=1; $i<= $max_pages; $i++)
        {
            $fn_name = "_page_{$i}";

            if (! method_exists($this, $fn_name)) {
                continue;
            }

            $options['__page_number'] = $i;

            $pageSubform = call_user_func(array($this, $fn_name), $options);

            foreach($pageSubform->getSubForms() as $subform) {
                $subform->setAttrib("class",  $subform->getAttrib("class") . " ". $subform->getName());
            }

            $this->addSubform($pageSubform , $fn_name);

        }

        $this->addElement('note', 'fakeTabsClick', array(
            'decorators' => array(
                array('ViewHelper'),
                array('HtmlTag', array(
                    'tag' => 'div',
                    'id' => "fakeTabsOnBottom",
                )),
            )
        ));

        //add action buttons
        $actions = $this->_create_formular_actions($options['formular'] , '__formular');
        $this->addSubform($actions, 'form_actions');



        $history = $this->create_form_mambo_history();
        if ($history) {
            $this->addSubform($history, 'form_history');
        }


        return $this;

    }



    private function _tabs_navigation($tabs_counter = 1)
    {
        $tabs = new Zend_Form_SubForm();
        $tabs->clearDecorators()->addDecorators(
            array('FormElements', array('HtmlTag', array('tag' => 'ul', 'class' => 'tabs_gray_class dontPrint'))));

        //add tab butons .. 1 to $tabs_counter
        for($i=1; $i<= $tabs_counter; $i++) {
            $tabs_li = new Zend_Form_SubForm();
            $tabs_li->clearDecorators()->addDecorators(array('FormElements', array('HtmlTag', array('tag' => 'li'))));
            $tabs_li->addElement(new Zend_Form_Element_Note(array(
                'name' => "p{$i}_nav",
                'value' => $this->translate('Page %s', $i),//sprintf($tabs_label, $i),
                'disableLoadDefaultDecorators' => true,
                'decorators' => array(
                    array('ViewHelper'),
                    array('HtmlTag', array(
                        'tag' => 'a',
                        'href' => "#page-{$i}",//$this->getView()->url(array()),
                        // 		            'class' => 'element',
                    )),
                )
            )), "nav-{$i}");
            $tabs->addSubform($tabs_li, "tabs_navi_{$i}");
        }
        return $tabs;

    }

    private function _create_formular_details($options = array(), $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Formular Details'));
        $subform->setAttrib("class", "label_same_size label_same_size_auto " . $__fnName);

        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }

        //hidden user_id, ipid, previous wlassessment_id
// 	    $subform->addElement('hidden', 'user_id', array(
// 	        'label'        => null,
// 	        'value'        => $userid,
// 	        'required'     => true,
// 	        'readonly'     => true,
// 	        'filters'      => array('StringTrim', 'Int'),
// 	        'validators'   => array('NotEmpty', 'Int'),
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>6 , 'openOnly'=>true)),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none', 'openOnly'=>true)),

// 	        ),
// 	    ));
        $subform->addElement('hidden', 'id', array(
            'label'        => null,
            'value'        => $options['id'],
            'required'     => false,
            'readonly'     => true,
            'filters'      => array('StringTrim', 'Int'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>6 , 'openOnly'=>true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none', 'openOnly'=>true)),

            ),
        ));

        //not for model
        $subform->addElement('hidden', 'patient_id', array(
            'label'        => null,
            'value'        => Pms_Uuid::encrypt($this->_patientMasterData['id']),
            'required'     => false,
            'readonly'     => true,
            'filters'      => array('StringTrim'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly'=>true)),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly'=>true)),

            ),
        ));


// 	    $subform->addElement('note', 'allowed_formular_date', array(
// 	        'label'        => null,
// 	        'value'        => "<script type='text/javascript'> var __allowed_formular_date = " . json_encode($options['__allowed_formular_date']) . ";</script>",
// 	        'required'     => false,
// 	        'escape'       => false,
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>6 )),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),

// 	        ),
// 	    ));return $subform;


        $subform->addElement('text', 'formular_date_start', array(
            'label'        => $this->translate('Visit start date:'),
            'value'        => ! empty($options['formular_date_start']) ? date('d.m.Y', strtotime($options['formular_date_start'])) : date('d.m.Y'),
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'date formular_date',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
            ),

            'data-allowranges' => json_encode($options['__allowed_formular_date']),

            'onChange' => "reCreateTimebasedValues(this.value); return false;" ,

        ));
        $subform->addElement('text', 'formular_date_end', array(
            'label'        => $this->translate('Visit end date:'),
            'value'        => ! empty($options['formular_date_end']) ? date('d.m.Y', strtotime($options['formular_date_end'])) : date('d.m.Y'),
            'required'     => true,
            'filters'      => array('StringTrim'),
            'validators'   => array('NotEmpty'),
            'class'        => 'date formular_date',
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
            ),

            'data-allowranges' => json_encode($options['__allowed_formular_date']),

            'onChange' => "reCreateTimebasedValues(this.value); return false;" ,

        ));

        return $this->filter_by_block_name($subform, $__fnName);
    }



    //Kontaktart = Contact type
    private function _create_formular_contact_type($options = array(), $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Contact type'));
        $subform->setAttrib("class", "label_same_size_80 {$__fnName}");

        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
// 	    dd($options);

// 	    $subform2 = new Zend_Form_SubForm(['disableLoadDefaultDecorators' => true]);
// 	    $subform2->setDecorators(array(
// 	        'FormElements',
// 	    ));
        $subform->addElement('select', 'contact_type_1', array(
// 	        'label'      => $this->translate('contact_type_1'),
            'multiOptions' => $this->getColumnMapping("contact_type_1"),
            'required'   => false,
            'value'    => ! empty($options['contact_type_1']) ? $options['contact_type_1'] : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

        $subform->addElement('select', 'contact_type_2', array(
// 	        'label'      => $this->translate('contact_type_2'),
            'multiOptions' => $this->getColumnMapping('contact_type_2'),
            'required'   => false,
            'value'    => ! empty($options['contact_type_2']) ? $options['contact_type_2'] : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

// 	    $subform->addSubForm($subform2, 'contact_type');

        return $this->filter_by_block_name($subform, $__fnName);
    }




    //Kontaktort = contact location
    private function _create_formular_contact_location($options = array(), $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Contact location'));
        $subform->setAttrib("class", "label_same_size_80 {$__fnName}");

        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }

        $subform->addElement('select', 'contact_location', array(
// 	        'label'      => $this->translate('contact_location'),
            'multiOptions' => $this->getColumnMapping('contact_location'),
            'required'   => false,
            'value'    => ! empty($options['contact_location']) ? $options['contact_location'] : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

        return $this->filter_by_block_name($subform, $__fnName);
    }




    //Einsatz = commitment
    private function _create_formular_commitment($options = array(), $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn


        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Commitment'));
        $subform->setAttrib("class", "label_same_size_auto {$__fnName}");

        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }

        $subform->addElement('multiCheckbox', 'commitment', array(
            'label'      => false,
            'multiOptions' => $this->getColumnMapping('commitment'),
            'required'   => false,
            'value'    => ! empty($options['commitment']) ? $options['commitment'] : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

        return $this->filter_by_block_name($subform, $__fnName);
    }


    //Einsatz = commitment
    private function _create_formular_services_usage($options = array(), $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('The services are used as / for:'));
        $subform->setAttrib("class", "label_same_size_auto {$__fnName}");

        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }

        $subform->addElement('multiCheckbox', 'services_usage', array(
            'label'      => null,
            'multiOptions' => $this->getColumnMapping('services_usage'),
            'required'   => false,
            'value'    => ! empty($options['services_usage']) ? $options['services_usage'] : null,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

        return $this->filter_by_block_name($subform, $__fnName);
    }






    private function _page_1($options = array())
    {
        $__fnName = __FUNCTION__;
        $page_number = $options['__page_number'];

        $page = new Zend_Form_SubForm();
        $page->clearDecorators()
            ->setDecorators( array(
// 	        'FormErrors',//errors on top?
                'FormElements',
                array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}", 'class' => 'mamboTabsPageBody')),
            ))
            ->setLegend(sprintf($this->translate('Page %s'), $page_number));


        //formular
        $subform = $this->_create_formular_details($options['formular'] , '__formular');
        $page->addSubForm($subform, 'formular_details');


        //patient master - this will no be saved from here !?
        $af_pm =  new Application_Form_PatientMaster(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientMaster'] = [
            "__form"   => $af_pm, //zend_form
            "__fn"     => [],
        ];

        $patient_details_form = $af_pm->create_form_patient_details();
        $page->addSubForm($patient_details_form, 'patient_details');
        $this->_categoriesForms['PatientMaster']["__fn"]['create_form_patient_details'] = [
            "__page"       => $__fnName,
            "__key"        => 'patient_details',
            "__isMultiple" => false,
        ];




        $af_s = new Application_Form_Stammdatenerweitert(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['Stammdatenerweitert'] = [
            "__form"   => $af_s, //zend_form
            "__fn"     => [],
        ];



        $subform = $af_s->create_form_nationality($options[$__fnName]['Nationality']);
        $page->addSubform($subform, 'Nationality');
        $this->_categoriesForms['Stammdatenerweitert']["__fn"]['create_form_nationality'] = [
            "__page"       => $__fnName,
            "__key"        => 'Nationality',
            "__isMultiple" => false,
        ];



        //Kontaktart
        //$subform = $this->_create_formular_contact_type($options['formular'] , '__formular');
        //$page->addSubForm($subform, 'formular_contact_type');

        //Kontaktort
        //$subform = $this->_create_formular_contact_location($options['formular'] , '__formular');
        //$page->addSubForm($subform, 'formular_contact_location');

        //Einsatz
        $subform = $this->_create_formular_commitment($options['formular'] , '__formular');
        $page->addSubForm($subform, 'formular_commitment');



// 	    Kontaktort
// 	    Einsatz

        //Krankenkasse
        // 	    PatientHealthInsurance
        $af_hi = new Application_Form_PatientHealthInsurance(array(
            "_patientMasterData" => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientHealthInsurance'] = [
            "__form"   => $af_hi, //zend_form
            "__fn"     => [],
        ];

        $subform = $af_hi->create_form_health_insurance($options[$__fnName]['PatientHealthInsurance']);
        $page->addSubForm($subform, 'PatientHealthInsurance');
        $this->_categoriesForms['PatientHealthInsurance']["__fn"]['create_form_health_insurance'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientHealthInsurance',
            "__isMultiple" => false,
        ];





// 	    Hausarzt
        //patient Family_Doctor is only one
        $af_fd = new Application_Form_Familydoctor(array(
            '_patientMasterData' => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_block_feedback_values'  => $this->_block_feedback_values,

        ));
        $this->_categoriesForms['Familydoctor'] = [
            "__form"   => $af_fd, //zend_form
            "__fn"     => [],
        ];

        $subform = $af_fd->create_form_family_doctor($options[$__fnName]['Familydoctor']);
        $page->addSubform($subform, 'Familydoctor');
        $this->_categoriesForms['Familydoctor']["__fn"]['create_form_family_doctor'] = [
            "__page"       => $__fnName,
            "__key"        => 'Familydoctor',
            "__isMultiple" => false,
        ];



        //Ansprechpartner
        //patient Contact_Persons multiple
        $af_cpm = new Application_Form_ContactPersonMaster([
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,

        ]);

        $this->_categoriesForms['ContactPersonMaster'] = [
            "__form"   => $af_cpm, //zend_form
            "__fn"     => [],
        ];

        $subform = $af_cpm->create_form_contact_person_all($options[$__fnName]['Contact_Persons']);
        $page->addSubform($subform, 'Contact_Persons');

        $this->_categoriesForms['ContactPersonMaster']["__fn"]['create_form_contact_person_all'] = [
            "__page"       => $__fnName,
            "__key"        => 'Contact_Persons',
            "__isMultiple" => true,
        ];






        //ACP Patientenverfügung + +Vorsorgevollmacht
        // 	    //Living_Will
        $af_pacp = new Application_Form_PatientACP([
            '_block_name'           => $this->_block_name,
            '_block_feedback_values'  => $this->_block_feedback_values,
            '_patientMasterData'    => $this->_patientMasterData,
        ]);
        $this->_categoriesForms['PatientACP'] = [
            "__form"   => $af_pacp, //zend_form
            "__fn"     => [],
        ];

        $contact_persons_arr = array();

        ContactPersonMaster::beautifyName($options[$__fnName]['Contact_Persons']);
        $contact_persons_arr = array_column($options[$__fnName]['Contact_Persons'], 'nice_name', 'id');

        $options[$__fnName]['Patient_ACP'] = is_array($options[$__fnName]['PatientACP']) ? $options[$__fnName]['PatientACP'] : array();
        $acp_options = array_merge($options[$__fnName]['PatientACP'], array('contact_persons_arr' => $contact_persons_arr));

        $patient_acp_form = $af_pacp->create_form_acp($acp_options);
        $page->addSubform($patient_acp_form, 'PatientACP');

        $this->_categoriesForms['PatientACP']["__fn"]['create_form_acp'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientACP',
            "__isMultiple" => true,
        ];




// 	    Leistungen der Pflegeversicherung SGB XI
        $af_pms  = (new Application_Form_PatientMaintainanceStage(array(
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
            '_block_feedback_values'  => $this->_block_feedback_values,
        )));
        $this->_categoriesForms['PatientMaintainanceStage'] = [
            "__form"   => $af_pms, //zend_form
            "__fn"     => [],
        ];

        $patient_stage_form = $af_pms->create_form_maintenance_stage_benefits($options[$__fnName]['PatientMaintainanceStage']);
        $page->addSubForm($patient_stage_form, 'PatientMaintainanceStage');
        $this->_categoriesForms['PatientMaintainanceStage']["__fn"]['create_form_maintenance_stage_benefits'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientMaintainanceStage',
            "__isMultiple" => false,
        ];

// 	    Die Leistungen werden genutzt als/für:



        //Kontaktart
        $subform = $this->_create_formular_services_usage($options['formular'] , '__formular');
        $page->addSubForm($subform, 'formular_services_usage');





        //Pflegedienst
        //patient PatientPflegedienst multiple
        $af_pp = new Application_Form_PatientPflegedienst([
            '_block_name'           => $this->_block_name,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ]);
        $this->_categoriesForms['PatientPflegedienst'] = [
            "__form"   => $af_pp, //zend_form
            "__fn"     => [],
        ];

        $subform = $af_pp->create_form_patient_pflegedienst_all($options[$__fnName]['PatientPflegediensts']);
        $page->addSubform($subform, 'PatientPflegediensts');

        $this->_categoriesForms['PatientPflegedienst']["__fn"]['create_form_patient_pflegedienst'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientPflegediensts',
            "__isMultiple" => true,
        ];




        $subform = $af_s->create_form_marital_status($options[$__fnName]['Marital_status']);
        $page->addSubform($subform, 'Marital_status');
        $this->_categoriesForms['Stammdatenerweitert']['__fn']['create_form_marital_status'] = [
            "__page"   => $__fnName,
            "__key"    => 'Marital_status',
        ];






// 	    Wohnsituation
        $af_pl = (new Application_Form_PatientLives(array(
            "elementsBelongTo"      => "{$this->_categoriesForms_belongsTo}[{$model}]",
            "_patientMasterData"    => $this->_patientMasterData,
            "_block_name"           => $this->_block_name,
            "_clientForms"          => $this->_clientForms,
            '_block_feedback_values'  => $this->_block_feedback_values,
        )));
        $this->_categoriesForms['PatientLivesV2'] = [
            "__form"   => $af_pl, //zend_form
            "__fn"     => [],
        ];

        $subform = $af_pl->create_form_patient_lives_v2($options[$__fnName]['PatientLivesV2'], 'PatientLivesV2');
        $page->addSubform($subform, 'PatientLivesV2');
        $this->_categoriesForms['PatientLivesV2']["__fn"]['create_form_patient_lives_v2'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientLivesV2',
            "__isMultiple" => false,
        ];

        return $page;
    }


    private function _page_2($options = array())
    {
        $__fnName = __FUNCTION__;
        $page_number = $options['__page_number'];

        $page = new Zend_Form_SubForm();
        $page->clearDecorators()
            ->setDecorators( array(
                'FormElements',
                array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}", 'class' => 'mamboTabsPageBody')),
            ))
            ->setLegend(sprintf($this->translate('Page %s'), $page_number));



        $page->addElement('note', 'just_a_headline_1', array(
            'label'        => null,
            'value'        => self::translate('Modul 1: Medizinische Versorgungssituation'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'just_a_headline just_a_headline_modul_1')),
            ),
        ));
        //Diagnosen
        $af_pd = new Application_Form_PatientDiagnosis(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientDiagnosis'] = [
            "__form"   => $af_pd, //zend_form
            "__fn"     => [],
        ];

        $subform = $af_pd->create_form_diagnosis($options[$__fnName]['PatientDiagnosis']);
        $page->addSubForm($subform, 'PatientDiagnosis');
        $this->_categoriesForms['PatientDiagnosis']["__fn"]['create_form_diagnosis'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientDiagnosis',
            "__isMultiple" => false,
        ];



        $page->addElement('note', 'just_a_headline_2', array(
            'label'        => null,
            'value'        => self::translate('Medizinische Sensitivität/Selbsteinschätzung der Gesundheit'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'just_a_headline just_a_headline_modul_1_1')),
            ),
        ));


        //Diagnosen Observed
        $af_pdo = new Application_Form_PatientDiagnosisObserved(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientDiagnosisObserved'] = [
            "__form"   => $af_pdo, //zend_form
            "__fn"     => [
                //this works with one ring to rule them all.. so one function can persist them all.. or you can call for each.. but it takes longer
                'create_form_diseases_diagnosed' => [
                    "__page"       => $__fnName,
                    "__key"        => 'PatientDiagnosisObserved',
                    "__isMultiple" => false, //??
                ],
            ],
        ];

        // 	    Welche Erkrankungen hat der Haus- oder Facharzt bei Ihnen festgestellt? / Welche Erkrankungen sind Ihnen bekannt?
        // 	    Which diseases has the general practitioner diagnosed with you? / What diseases are known to you?
        $subform = $af_pdo->create_form_diseases_diagnosed($options[$__fnName]['PatientDiagnosisObserved'], 'PatientDiagnosisObserved');
        $page->addSubForm($subform, 'PatientDiagnosisObserved.diseases_diagnosed');

        // 	    Teilnahme am DMP
        // 	    Participation in the DMP
        $subform = $af_pdo->create_form_participation_DMP($options[$__fnName]['PatientDiagnosisObserved'], 'PatientDiagnosisObserved');
        $page->addSubForm($subform, 'PatientDiagnosisObserved.participation_DMP');

        // 	    Wie würden Sie Ihren Gesundheitszustand im Allgemeinen beschreiben?
        // 	    How would you describe your health in general?
        $subform = $af_pdo->create_form_general_health($options[$__fnName]['PatientDiagnosisObserved'], 'PatientDiagnosisObserved');
        $page->addSubForm($subform, 'PatientDiagnosisObserved.general_health');


        // 	    Im Vergleich zum vergangenen Jahr, wie würden Sie Ihren derzeitigen Gesundheitszustand beschreiben?
        // 	    Compared to last year, how would you describe your current state of health?
        // 	    Was ist der Grund dafür, dass es Ihnen nicht gut geht/schlechter geht?
        // 	    What is the reason that you are not feeling well?
        $subform = $af_pdo->create_form_current_state($options[$__fnName]['PatientDiagnosisObserved'], 'PatientDiagnosisObserved');
        $page->addSubForm($subform, 'PatientDiagnosisObserved.current_state');


        // 	    Was sind ihre eigenen Therapieziele/was ist Ihnen bei Ihren Krankheiten besonders wichtig?
        // 	    What are your own therapeutic goals / what is especially important for your illnesses?
        $subform = $af_pdo->create_form_therapeutic_goals($options[$__fnName]['PatientDiagnosisObserved'], 'PatientDiagnosisObserved');
        //$page->addSubForm($subform, 'PatientDiagnosisObserved.therapeutic_goals');


        // 	    Wie häufig sind Sie körperlich aktiv?
        // 	    How often are you physically active?
        $subform = $af_pdo->create_form_physically_active($options[$__fnName]['PatientDiagnosisObserved'], 'PatientDiagnosisObserved');
        $page->addSubForm($subform, 'PatientDiagnosisObserved.physically_active');


        // 	    Können Sie gut schlafen?
        // 	    Can you sleep well?
        $subform = $af_pdo->create_form_can_sleep($options[$__fnName]['PatientDiagnosisObserved'], 'PatientDiagnosisObserved');
        $page->addSubForm($subform, 'PatientDiagnosisObserved.can_sleep');



        // 	    Haben Sie häufig Schmerzen?
        // 	    Do you often have pain?
        $subform = $af_pdo->create_form_pain_recurrence($options[$__fnName]['PatientDiagnosisObserved'], 'PatientDiagnosisObserved');
        $page->addSubForm($subform, 'PatientDiagnosisObserved.pain_recurrence');




        return $page;
    }


    private function _page_3($options = array())
    {
        $__fnName = __FUNCTION__;
        $page_number = $options['__page_number'];


        $page = new Zend_Form_SubForm();
        $page->clearDecorators()
            ->setDecorators( array(
                // 	        'FormErrors',//errors on top?
                'FormElements',
                array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}", 'class' => 'mamboTabsPageBody')),
            ))
            ->setLegend(sprintf($this->translate('Page %s'), $page_number));



        /*
         * removed per request from ISPC-2292 14.02.2019 h)
         *
        $af_wd = new Application_Form_WoundDocumentation(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['WoundDocumentation'] = [
            "__form"   => $af_wd, //zend_form
            "__fn"     => [
                //this works with one ring to rule them all.. so one function can persist them all.. or you can call for each.. but it takes longer
                'create_form_wound_type' => [
                    "__page"       => $__fnName,
                    "__key"        => 'Wound_Type',
                    "__isMultiple" => false, //??
                ],
            ],
        ];


        $subform = $af_wd->create_form_wound_type($options[$__fnName]['Wound_Type']);
        $page->addSubform($subform, 'Wound_Type');


        $subform = $af_wd->create_form_wound_localization($options[$__fnName]['Wound_Localization']);
        $page->addSubform($subform, 'Wound_Localization');
        */





        $af_pdk = new Application_Form_PatientDgpKern(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientDgpKern'] = [
            "__form"   => $af_pdk, //zend_form
            "__fn"     => [],
        ];
        //print_r($options[$__fnName]);
        $subform = $af_pdk->create_form_ecog($options[$__fnName]['PatientDgpKern']);
        $page->addSubForm($subform, 'PatientDgpKern');
        $this->_categoriesForms['PatientDgpKern']["__fn"]['create_form_ecog'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientDgpKern',
            "__isMultiple" => false,
        ];



// 	    Vitalverte + fee
        $af_pfvs = new Application_Form_PatientFeedbackVitalSigns(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientFeedbackVitalSigns'] = [
            "__form"   => $af_pfvs, //zend_form
            "__fn"     => [],
        ];

        //hardcoded here
        $options[$__fnName]['PatientFeedbackVitalSigns']['source'] = 'mambo_assessment';

        $subform = $af_pfvs->create_form_nutrition($options[$__fnName]['PatientFeedbackVitalSigns']);
        $page->addSubForm($subform, 'PatientFeedbackVitalSigns');
        $this->_categoriesForms['PatientFeedbackVitalSigns']["__fn"]['create_form_nutrition'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientFeedbackVitalSigns',
            "__isMultiple" => false,
        ];




        //PatientVices
        $af_pv = new Application_Form_PatientVices(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientVices'] = [
            "__form"   => $af_pv, //zend_form
            "__fn"     => [
                //this works with one ring to rule them all.. so one function can persist them all.. or you can call for each.. but it takes longer
                'create_form_patient_smoking' => [
                    "__page"       => $__fnName,
                    "__key"        => 'PatientVices',
                    "__isMultiple" => false, //??
                ],
            ],
        ];

// 	    Rauchen Sie?
// 	    Do you smoke?
        $subform = $af_pv->create_form_patient_smoking($options[$__fnName]['PatientVices'], 'PatientVices');
        $page->addSubForm($subform, 'PatientVices.smoke');


// 	    Trinken Sie regelmäßig Alkohol?
// 	    Do you drink alcohol regularly?
        $subform = $af_pv->create_form_patient_alcoholing($options[$__fnName]['PatientVices'], 'PatientVices');
        $page->addSubForm($subform, 'PatientVices.alcohol');




        //PatientRegularChecks
        /*
        $af_prc = new Application_Form_PatientRegularChecks(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientRegularChecks'] = [
            "__form"   => $af_prc, //zend_form
            "__fn"     => [
                //this works with one ring to rule them all.. so one function can persist them all.. or you can call for each.. but it takes longer
                'create_form_family_treatment' => [
                    "__page"       => $__fnName,
                    "__key"        => 'PatientRegularChecks',
                    "__isMultiple" => false, //??
                ],
            ],
        ];*/


// 	    Stehen Sie in regelmäßiger hausärztlicher Behandlung?
// 	    Are you undergoing regular GP treatment?
        //$subform = $af_prc->create_form_family_treatment($options[$__fnName]['PatientRegularChecks'], 'PatientRegularChecks');
        //$page->addSubForm($subform, 'PatientRegularChecks.family_treatment');


//      Stehen Sie in regelmäßiger fachärztlicher Behandlung
//      Be in regular specialist
        //$subform = $af_prc->create_form_specialist_care($options[$__fnName]['PatientRegularChecks'], 'PatientRegularChecks');
        //$page->addSubForm($subform, 'PatientRegularChecks.specialist_care');


// 	    Waren Sie im letzten Jahr bei einer Vorsorgeuntersuchung
// 	    Were you in a check-up last year
        //$subform = $af_prc->create_form_lastyear_checkup($options[$__fnName]['PatientRegularChecks'], 'PatientRegularChecks');
        //$page->addSubForm($subform, 'PatientRegularChecks.lastyear_checkup');

// 	    Krankenhausaufenthalte
// 	    hospital stays
// 	    hospitalizations

        /*
         * removed per request from ISPC-2292 14.02.2019 e)
         *
        $subform = $af_prc->create_form_hospitalizations($options[$__fnName]['PatientRegularChecks'], 'PatientRegularChecks');
        $page->addSubForm($subform, 'PatientRegularChecks.hospitalizations');
        */



        return $page;
    }




    private function _page_4_bak($options = array())
    {
        $__fnName = __FUNCTION__;
        $page_number = $options['__page_number'];

        $page = new Zend_Form_SubForm();
        $page->clearDecorators()
            ->setDecorators( array(
                // 	        'FormErrors',//errors on top?
                'FormElements',
                array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}", 'class' => 'mamboTabsPageBody')),
            ))
            ->setLegend(sprintf($this->translate('Page %s'), $page_number));



        $page->addElement('note', 'just_a_headline_3', array(
            'label'        => null,
            'value'        => self::translate('Modul 2: Medikamentöse Versorgungssituation'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'just_a_headline just_a_headline_modul_2')),
            ),
        ));


        //PatientRegularChecks
        $af_pfm = new Application_Form_PatientFeedbackMedication(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientFeedbackMedication'] = [
            "__form"   => $af_pfm, //zend_form
            "__fn"     => [
                //this works with one ring to rule them all.. so one function can persist them all.. or you can call for each.. but it takes longer
                'create_form_nationwide_medicationplan' => [
                    "__page"       => $__fnName,
                    "__key"        => 'PatientFeedbackMedication',
                    "__isMultiple" => false, //??
                ],
            ],
        ];

        //Liegt der bundeseinheitliche Medikationsplan vor?
        //Is the nationwide medication plan available?
        $subform = $af_pfm->create_form_nationwide_medicationplan($options[$__fnName]['PatientFeedbackMedication'], 'PatientFeedbackMedication');
        $page->addSubForm($subform, 'PatientFeedbackMedication.nationwide_medicationplan');




        //MEDICATION BLOCK
        //this is just a holder, medication is just xhr.. try iframe

//dd($this->_block_feedback_values ['PatientDrugPlan']);
        $__feedback_options = [
            "__block_name" => $this->_block_name,
            "__parent"     => 'PatientDrugPlan',
            "__fnName"     => 'patient_drug_plan_all',
            "__parentID"   => '',
            "__meta"       => [
                "todo" => (! empty($this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all'])) ? $this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all']['todo'] : 'no',
                "feedback" => (! empty($this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all'])) ? $this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all']['feedback'] : 'no',
                //"benefit_plan" => (! empty($this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all'])) ? $this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all']['benefit_plan'] : 'no',
                /*
                 "heart_monitoring",
        "referral_to",
        "further_assessment",
        "training_nutrition",
        "training_adherence",
        "training_device",
        "training_prevention",
        "training_incontinence",
        "organization_careaids",
        "inclusion_COPD",
        "inclusion_measures",
        */
            ]
        ];
        if (! empty($this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all'])) {
            $__feedback_options['__meta_val']['todo'] = $this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all']['todo_val'];
            $__feedback_options['__meta_val']['feedback'] = $this->_block_feedback_values ['PatientDrugPlan'][$this->_block_name]['patient_drug_plan_all']['feedback_val'];
        }


        $__feedback_options = Zend_Json::encode($__feedback_options);

        /*
         * ! hardcoded max-width
         */
        $subform = new Zend_Form_SubForm();
        $subform->addDecorator('HtmlTag', array('tag'=>'div', 'class' => 'Medications2_holder_div', 'style'=>'max-width:735px'))
            ->removeDecorator('DtDdWrapper')
        ;
        $subform->setLegend($this->translate('Medications:'));
        $subform->setAttrib("data-feedback_options", $__feedback_options);
        $subform->setAttrib("class", 'has_feedback_options');

        $page->addSubform($subform, 'Medications');





// 	    Wissen Sie warum Sie welche Medikamente nehmen?
// 	    Do you know why you take which medications?
        $subform = $af_pfm->create_form_knows_medication($options[$__fnName]['PatientFeedbackMedication'], 'PatientFeedbackMedication');
        $page->addSubForm($subform, 'PatientFeedbackMedication.knows_medication');



// 	    Nehmen Sie Ihre Medikamente regelmäßig ein?
// 	    Do you take your medication regularly?
        $subform = $af_pfm->create_form_takes_regularly($options[$__fnName]['PatientFeedbackMedication'], 'PatientFeedbackMedication');
        $page->addSubForm($subform, 'PatientMedicationFeedback.takes_regularly');



// 	    Wie schätzen Sie Ihre Medikation ein?
// 	    How do you rate your medication?
        $subform = $af_pfm->create_form_rate_medication($options[$__fnName]['PatientFeedbackMedication'], 'PatientFeedbackMedication');
        $page->addSubForm($subform, 'PatientFeedbackMedication.rate_medication');



// 	    Nehmen Sie regelmäßig Medikamente zum Schlafen ein?
// 	    Do you take medication regularly to sleep?
        $subform = $af_pfm->create_form_sleep_medication($options[$__fnName]['PatientFeedbackMedication'], 'PatientFeedbackMedication');
        $page->addSubForm($subform, 'PatientFeedbackMedication.sleep_medication');




// 	    Wie nehmen Sie Ihre Medikamente ein?
// 	    How do you take your medication?
        $subform = $af_pfm->create_form_medication_intake($options[$__fnName]['PatientFeedbackMedication'], 'PatientFeedbackMedication');
        $page->addSubForm($subform, 'PatientFeedbackMedication.medication_intake');





        return $page;
    }

    /**
     * it was 5, but page 4 deprecated
     * @param array $options
     * @return Zend_Form_SubForm
     * @throws Zend_Form_Exception
     */
    private function _page_4($options = array())
    {
        $__fnName = __FUNCTION__;
        $page_number = $options['__page_number'];

        $page = new Zend_Form_SubForm();
        $page->clearDecorators()
            ->setDecorators( array(
                // 	        'FormErrors',//errors on top?
                'FormElements',
                array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}", 'class' => 'mamboTabsPageBody')),
            ))
            ->setLegend(sprintf($this->translate('Page %s'), $page_number));


        $page->addElement('note', 'just_a_headline_4', array(
            'label'        => null,
            'value'        => self::translate('Modul 3: Pflegerische Versorgungssituation (SGB V, IX, XI, XII)'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'just_a_headline just_a_headline_modul_3')),
            ),
        ));



        //PatientFeedbackGeneral
        $af_pfg = new Application_Form_PatientFeedbackGeneral(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientFeedbackGeneral'] = [
            "__form"   => $af_pfg, //zend_form
            "__fn"     => [
                //this works with one ring to rule them all.. so one function can persist them all.. or you can call for each.. but it takes longer
                'create_form_assistance_healthinsurance' => [
                    "__page"       => $__fnName,
                    "__key"        => 'PatientFeedbackGeneral',
                    "__isMultiple" => false, //??
                ],
            ],
        ];

// 	    Hilfen der Krankenversicherung (nach § 37 SGB V Anspruch auf häusliche Krankenpflege)
// 	    Assistance from health insurance (according to § 37 SGB V right to home care)
        $subform = $af_pfg->create_form_assistance_healthinsurance ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.assistance_healthinsurance');



// 	    Mobilität
// 	    Mobility
        $subform = $af_pfg->create_form_mobility($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.mobility');


// 	    Sturzgefahr
// 	    Risk of falling:
        $subform = $af_pfg->create_form_fall_hazards ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.fall_hazards');


// 	    Kognitive und kommunikative Fähigkeiten
// 	    Cognitive and communicative skills
        $subform = $af_pfg->create_form_cognitive_communicative ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.cognitive_communicative');


// 	    Verhaltensweisen und psychische Problemlagen
// 	    behaviors and mental problems
        $subform = $af_pfg->create_form_behaviors_mental ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.behaviors_mental');


// 	    Ernährung:
// 	    Nutrition:
        $subform = $af_pfg->create_form_nutrition ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.nutrition');


// 	    Kontinenz
// 	    continence
        $subform = $af_pfg->create_form_continence ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.continence');


// 	    Bewältigung der Alltagssituation
// 	    Coping with the everyday situation
        $subform = $af_pfg->create_form_coping_everyday ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.coping_everyday');


// 	    Haushaltsführung
// 	    housekeeping
        $subform = $af_pfg->create_form_housekeeping ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.housekeeping');



// 	    Soziale Einbindung / Gestaltung des Alltagslebens
// 	    Social involvement / design of everyday life
        $subform = $af_pfg->create_form_social_integration_everyday_life ($options[$__fnName]['PatientFeedbackGeneral'], 'PatientFeedbackGeneral');
        $page->addSubForm($subform, 'PatientFeedbackGeneral.social_integration_everyday_life');


        /*
         * removed per request from ISPC-2292 14.02.2019 f)
         *
        //PatientFeedbackCareAids
        $af_pfca = new Application_Form_PatientFeedbackCareAids(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientFeedbackCareAids'] = [
            "__form"   => $af_pfca, //zend_form
            "__fn"     => [],
        ];

// 	    Pflege- und Hilfsmittel
//         care and aids
        $subform = $af_pfca->create_form_care_aids ($options[$__fnName]['PatientFeedbackCareAids'], 'PatientFeedbackCareAids');
        $page->addSubForm($subform, 'PatientFeedbackCareAids.care_aids');
        $this->_categoriesForms['PatientFeedbackCareAids']["__fn"]['create_form_care_aids'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientFeedbackCareAids',
            "__isMultiple" => false,
        ];
         */

        return $page;

    }

    /**
     * it was 6, but 4 is deprecated
     *
     * @param array $options
     * @return Zend_Form_SubForm
     * @throws Zend_Form_Exception
     */
    private function _page_5($options = array())
    {
        $__fnName = __FUNCTION__;
        $page_number = $options['__page_number'];

        $page = new Zend_Form_SubForm();
        $page->clearDecorators()
            ->setDecorators( array(
                // 	        'FormErrors',//errors on top?
                'FormElements',
                array('HtmlTag',array('tag'=>'div', 'id'=> "page-{$page_number}", 'class' => 'mamboTabsPageBody')),
            ))
            ->setLegend(sprintf($this->translate('Page %s'), $page_number));

        $page->addElement('note', 'just_a_headline_5', array(
            'label'        => null,
            'value'        => self::translate('Modul 4: Hilfen nach SGB IX – Schwerbehinderung und Rehabilitation'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'just_a_headline just_a_headline_modul_4')),
            ),
        ));



        //PatientFeedbackGeneral
        $af_pdd = new Application_Form_PatientDisabilityDegree(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
            '_block_feedback_values'  => $this->_block_feedback_values,
        ));
        $this->_categoriesForms['PatientDisabilityDegree'] = [
            "__form"   => $af_pdd, //zend_form
            "__fn"     => [],
        ];

// 	    Grad der Behinderung
// 	    Degree of disability
        $subform = $af_pdd->create_form_disability_degree ($options[$__fnName]['PatientDisabilityDegree'], 'PatientDisabilityDegree');
        $page->addSubForm($subform, 'PatientDisabilityDegree.disability_degree');
        $this->_categoriesForms['PatientDisabilityDegree']["__fn"]['create_form_disability_degree'] = [
            "__page"       => $__fnName,
            "__key"        => 'PatientDisabilityDegree',
            "__isMultiple" => false,
        ];



        return $page;
    }













    /**
     *
     * @param unknown $ipid
     * @param unknown $data
     * @throws Exception
     * @return NULL|Doctrine_Record
     */
    public function save_form_mambo_assessment($ipid, $data = array())
    {
        if (empty($ipid)) {
            throw new Exception('Contact Admin, formular cannot be saved.', 0);
        }


        $dataFormular = array_merge(array_column($data, '__formular'));
        $dataFormular = $dataFormular[0];
        $dataFormular = array_merge($dataFormular, $data['__formular']);
        $dataFormular['ipid'] = $ipid;

        /*
         * TODO
         * if this is not completed...
         * we must make sure only one opened version of the form exists, so no multiple instances of this form cane e opened at the same time
         */

        $assessmentEntity = AokprojectsKurzassessmentTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$dataFormular['id'], $ipid], $dataFormular);

        if ( ! $assessmentEntity->id) {
            throw new Exception('Contact Admin, formular cannot be saved.', 1);
            return null;//we cannot save... contact admin
        }

        /*
         * ISPC-2095 -
         */
        FormsEditmodeTable::finishedEditing([
            'pathname' => 'aokprojects/kurzassessment',
            'client_id' => $this->logininfo->clientid,
            'patient_master_id' => $this->_patientMasterData['id'],
            'user_id' => $this->logininfo->userid,
            'search' => 'assessment_id=' . (int)$dataFormular['id'],
            'is_edited' => 'yes',
        ]);


        $related_IDS = [];

        /*
         * removed per request from ISPC-2292 14.02.2019 h)
         *
	    $canvas_container = reset($data['canvas_container']);
	    if ( ! empty($canvas_container)) { //save only if not empty, so we don't overwrite
	        $data [$this->_categoriesForms['WoundDocumentation']["__fn"]['create_form_wound_type']['__page']] ['Wound_Type'] ['w_localisation'] = $canvas_container;
	    }
	    */


        //need [__formular][formular_date] to save Ecog
        $data[$this->_categoriesForms['PatientDgpKern']["__fn"]['create_form_ecog']['__page_3']] ['PatientDgpKern'] ['__formular']['formular_date'] = $assessmentEntity->formular_date_start;
$data[$this->_categoriesForms['PatientDgpKern']["__fn"]['create_form_ecog']['__page']] ['PatientDgpKern'] ['__formular']['formular_date'] = $assessmentEntity->formular_date_start;
$data[$this->_categoriesForms['PatientDgpKern']["__fn"]['create_form_ecog']['__page']] ['PatientDgpKern'] ['__formular']['formular_type'] = "aok_kurz";//$assessmentEntity->formular_date_start;

        //need [__formular][formular_date] to save FormBlockVitalSigns
        $data [$this->_categoriesForms['PatientFeedbackVitalSigns']["__fn"]['create_form_nutrition']['__page']] ['PatientFeedbackVitalSigns'] ['__formular']['formular_date'] = $assessmentEntity->formular_date_start;

/*

        $med_form =  new Application_Form_PatientDrugPlan(array(
            '_patientMasterData'    => $this->_patientMasterData,
            '_block_name'           => $this->_block_name,
            '_clientForms'          => $this->_clientForms,
            '_clientModules'        => $this->_clientModules,
            '_client'               => $this->_client,
        ));
        $med_form->save_medication($ipid, $data);

*/


        /*
         * no data is saved for PatientMaster.. you must edit from stammdaten?
         */
        $af = new Application_Form_PatientMaster(array(
            '_block_name'   => $this->_block_name,
        ));
//         $patient_details = $af->save_form_patient_details($ipid, $data['_page_1']['patient_details']);


        foreach ($this->_categoriesForms as $formName => $formMapping)
        {

            if (isset($formMapping['__form'])
                && $formMapping['__form'] instanceof Pms_Form
                && ! empty($formMapping['__fn'])//this stores the create_fn => [__page, __key]
            )
            {
                foreach($formMapping['__fn'] as $createFn => $fndataMap) {

                    $entity = null;

                    try {

                        if ($fndataMap['__isMultiple']) {

                            foreach ( $data [$fndataMap['__page']] [$fndataMap['__key']] as $one) {
                                $entity = $formMapping['__form']->triggerSaveFunction($createFn, array($ipid, $one ));

                                if ($entity && $entity instanceof Doctrine_Record) {
                                    $related_IDS[$entity->getTable()->getComponentName()][] = $entity->{$entity->getTable()->getIdentifier()};
                                }
                            }

                        } else {

                            $entity = $formMapping['__form']->triggerSaveFunction($createFn, array($ipid, $data [$fndataMap['__page']] [$fndataMap['__key']] ));

                            if ($entity && $entity instanceof Doctrine_Record) {
                                $related_IDS[$entity->getTable()->getComponentName()] = $entity->{$entity->getTable()->getIdentifier()};
                            }
                        }


                        if (APPLICATION_ENV == "development" && $entity === false) {
                            //you die!
                            $formMapping['__form']->getSaveFunction();
                            ddecho("FAILED !", $formName , $createFn, $fndataMap, $formMapping['__form']->getCreateFunction());
                        }

                    }
                    catch (Exception $e) {

                        if (APPLICATION_ENV == "development") {
                            //echo $createFn;
                            //echo $e->getMessage();
                            //echo $e->getTraceAsString();
                            ddecho("HORROR FAILED !", $createFn, $e->getMessage(), $e->getTraceAsString());
                        }

                        //remove formular and all new saved entries?
                        //do we also fallback on the ones we just edited?

                    }
                }
            }
        }

        $assessmentEntity->_touched_ids = $related_IDS;
        $assessmentEntity->save();

        $this->_assessmentEntity = $assessmentEntity;

        $this->save_feedback_options($data);

        return $assessmentEntity;
    }



    /**
     * save the yellow things on right to AssessmentProblemsTable
     */
    public function save_feedback_options($data = [])
    {

        $assessmentEntity = $this->_assessmentEntity;

        $allValuesSoGood = [];

        foreach ($data['feedback_options'][$this->_block_name] as $parent_table => $_fnNames)
        {
            foreach ($_fnNames as $__fnName => $values) {

                if ( ! empty($values['__parentID'])) {
                    $values['parent_table_id'] = $values['__parentID'] ?: null;
                }


                $feedbackEntity = AokprojectsKurzassessmentProblemsTable::getInstance()->findOrCreateOneBy(

                    [
                        'assessment_id',
                        'assessment_name',
                        'parent_table',
                        'fn_name',
                    ],

                    [
                        $assessmentEntity->id,
                        $this->_block_name,
                        $parent_table,
                        $__fnName,
                    ],

                    $values
                );

                array_push($this->_feedbackOptions, $feedbackEntity->toArray());



                $valuesSoGood = array_filter($values, function($i){return $i['sogood'];});
                $valuesSoGood = array_column($valuesSoGood, 'freetext', 'type');
                $allValuesSoGood = array_merge_recursive($allValuesSoGood, $valuesSoGood);
            }
        }

        foreach ($allValuesSoGood as $field_name => $field_values) {
            $field_values =  is_array($field_values) ? $field_values : [$field_values];

            foreach($field_values as $field_value) {

                FormsTextsListTable::getInstance()->findOrCreateOneBy(

                //fields to search
                    ['clientid', 'field_name', 'field_value'],

                    //search values
                    [$this->logininfo->clientid, $field_name, $field_value],

                    //data to update/insert
                    [
                        //Todo is universal, so anyform for it
                        'form_name' => $field_name == 'todo' ? 'anyform' : 'mambo/assessment'
                    ]
                );
            }
        }

//                 dd($feedbackEntity->toArray(), $allValuesSoGood, $valuesSoGood);

    }


    /**
     * when a formular is marked as save-and-completed, we insert all the yellow TODOs into verlauf as real TODOs
     */
    public function save_feedback_TODOs()
    {

        $assessmentEntity = $this->_assessmentEntity;


        $feedbackOptionsTODOs = array_filter($this->_feedbackOptions, function($i) { return $i['todo'] == 'yes' && ! empty($i['todo_val']['freetext']) && ! empty($i['todo_val']['user']) && ! empty($i['todo_val']['date']);});

        $newTODOs = array_column($feedbackOptionsTODOs, 'todo_val');

//         dd( $newTODOs, $feedbackOptionsTODOs, $this->_feedbackOptions);



        if (empty ($newTODOs) || empty($this->_ipid)) {
            return; //break the execution cycle because we have nothing to insert
        }

        $course_date = date("Y-m-d H:i:00", time());
        $course_type = "W";

        $fbt_array = array();
        $todoid_array = array();

        foreach ($newTODOs as $entry)
        {
            $entry['verlauftext'] = $entry['freetext'] . " |---------| " . $entry['user'] . " |---------| " . $entry['date'] ." |---------| 0";

            $cust = [
                'ipid' => $this->_ipid,
                'course_date' => $course_date,
                'course_type' => $course_type,
                'course_title' => $entry['verlauftext'],
                'user_id' => $this->logininfo->userid,

                'tabname' => "block_todos",

                'done_id' => $this->_assessmentEntity->id,
                'done_name' => AokprojectsKurzassessment::PATIENT_COURSE_DONE_NAME
            ];

            $pc_obj = new PatientCourse();
            $pc_obj->triggerformid = 0;
            $pc_obj->triggerformname = 0;
            $course_id = $pc_obj->set_new_record($cust);

            //piggyback
            $gpost = array(
                "todo_text"		=> $entry['freetext'],
                "todo_date"		=> $entry['date'],
                "todo_users"	=> explode(',', $entry['user']),
            );

            $event = new Doctrine_Event($pc_obj, Doctrine_Event::RECORD_SAVE);
            $trigger_ToDos_obj = new application_Triggers_addValuetoToDos();
            $trigger_ToDos_obj->triggerAddValuetoTodos($event, null, $this->triggerformname, $this->triggerformid, 2, $gpost);
            $todos = $trigger_ToDos_obj->get_last_insert_ids();

            if ( ! empty($todos)) {
                $pc_obj->recorddata = serialize(array(
                    "todo_id" => $todos,
                    "assessment_id" => $this->_assessmentEntity->id,
                ));
                $pc_obj->recordid = 0;
                $pc_obj->save();


                $pc_obj->set_new_record(array(
                    'recorddata' => serialize($todoid_array),
                    'isserialized' => 1
                ));

            }

        }

        return;
    }



    public function create_form_mambo_history( $options = array(), $elementsBelongTo = null)
    {
        if (empty($this->_ipid)) {
            return; //fail-safe ... __construct -> _ipid
        }

        $oldersForms = AokprojectsKurzassessmentTable::getInstance()->findByIpidAndStatus($this->_ipid, 'completed', Doctrine_Core::HYDRATE_ARRAY);

        if (empty($oldersForms)) {
            return; //nothing here to see
        }


        $columns = array(
            '#',
            $this->translate('Formular start - end date'),
            $this->translate('Download'),
        );
        $subform = $this->subFormTable(array(
            'columns' => $columns,
            'class' => "align_center {$__fnName}",

        ));
//         $subform->removeDecorator('Fieldset');
        $subform->setLegend('Completed formulars');
        $subform->addDecorator('HtmlTag', array('tag' => 'div', 'class'=> 'formular_history dontPrint'));


        $row_cnt = 0;

        $rows = [];

        foreach ($oldersForms as $completedForm) {

            $row_cnt++;

            $row = $this->subFormTableRow();

            $row->addElement('note', 'counter', array(
                'value'  => $row_cnt,
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'class' => 'align_left',
                    )),
                ),
            ));

            $row->addElement('note', 'formular_date', array(
                'value'  => $completedForm['formular_date_start'] . " - " . $completedForm['formular_date_end'],
                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'class' => 'align_left',
                    )),
                ),
            ));

            $row->addElement('note', 'download', array(
                'value'  => ($completedForm['_touched_ids']['PatientFileUpload']['completed']) ? "<a class='verlauf_contactform_download_pdf' href='".APP_BASE."stats/patientfileupload?doc_id={$completedForm['_touched_ids']['PatientFileUpload']['completed']}&id={$this->_patientMasterData['id_encrypted']}' title=''>". $this->translate("Click to Download Pdf") . "</a>" : 'keine PDF',

                'decorators' => array(
                    'ViewHelper',
                    array(array('data' => 'HtmlTag'), array(
                        'tag' => 'td',
                        'class' => 'align_left',
                    )),
                ),
            ));

            $rows[] = $row;
        }


        $subform->addSubForms($rows);

        return $subform;

    }




    public function getColumnMapping($fieldName, $revers = false)
    {


        $overwriteMapping = [
//             $fieldName => [ value => translation]
            "status" => [
                "1" => self::translate('liegt vor'),
                "2" => self::translate('wurde beantragt'),
                "3" => self::translate('Höherstufung beantragt'),
                "4" => self::translate('Widerspruch / Klage eingereicht'),
                "5" => self::translate('Erstantrag vornehmen'),
            ],
            "contact_type_1" => [
                ''  => '---' ,//add extra empty option
            ],
            "contact_type_2" =>  [
                ''  => '---' ,//add extra empty option
            ],
            "contact_location" =>  [
                ''  => '---' ,//add extra empty option
            ],
            'commitment' => [
            ],
        ];


        $values = AokprojectsKurzassessmentTable::getInstance()->getEnumValues($fieldName);

        $values = array_combine($values, array_map("self::translate", $values));

        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }

        return $values;

    }

}