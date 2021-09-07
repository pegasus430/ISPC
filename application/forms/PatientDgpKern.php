<?php
//TODO : move the saved values to DgpKern and then delete all this model PatientKarnofsky
//TODO after 31.01.2018 question is answered.. will decide if to move all this into DgpKern form or a new table will spawn

class Application_Form_PatientDgpKern extends Pms_Form
{
    protected $_model = 'DgpKern';
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_ecog' => [
                "todo",
                "feedback",
//                 "benefit_plan",
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
        //ISPC-2625, AOK Kurzassessment, 07.07.2020, elena
		// Maria:: Migration CISPC to ISPC 22.07.2020
         "AokprojectsKurzassessment" => [
            'create_form_ecog' => [
                "todo",
                "feedback",
//                 "benefit_plan",
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
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = null;
    private $triggerformname = null;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = 'Form_PatientDgpKern';
    
    
    //begleitung admission !
    public function create_form_partners ($values =  array() , $elementsBelongTo = null)
    {
        $subform = new Zend_Form_SubForm();
        $subform->removeDecorator('DtDdWrapper');
        $subform->addDecorator('HtmlTag', array('tag' => 'table'));
        $subform->setLegend($this->translate('Description of the current or immediately planned supply:'));
        $subform->setAttrib("class", "label_same_size " . __FUNCTION__);
        

        if ( ! is_null($elementsBelongTo)) {
            $subform->setOptions(array(
                'elementsBelongTo' => $elementsBelongTo
            ));
        }
        
        $lists = DgpKern::get_form_texts();
        
//         $subform->addElement('hidden', 'form_type', array(
//             'label'     => null,
//             'required'  => false,
//             'value'     => 'adm',
             
//             'decorators' =>   array(
//                 'ViewHelper',
//                 array('Errors'),
//                 array(array('data' => 'HtmlTag'), array('tag' => 'td')),
//                 array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none')),
//             )
        
//         ));
        
        //DgpKern
        /*
         * maybe you want to split into td's later.... 3/row?
        $cntTDs = 0;
        foreach ($lists['partners']  as $val=>$label) {
            
            $openOnly = $cntTDs % 3 == 0; 
            $closeOnly = $cntTDs % 3 == 1 ? true : false;
            
            $cntTDs++;

            
            
            $el= $this->createElement('checkbox', 'begleitung', array(
                'checkedValue'    => '1',
                'uncheckedValue'  => '',

                'checked'         => in_array('1', $options['w_type']) ? true : false,
            
                'label'        => $label,
                'required'     => false,
                'filters'      => array('StringTrim'),
                'validators'   => array('Int'),
                'decorators'   => array(
                    'ViewHelper',
                    array('Label', array(
                        'placement'=> 'APPEND'
                    )),
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                    //row must be conditioned if open || close
                    array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'openOnly' => $openOnly, 'closeOnly' =>$closeOnly)),
                ),
                'isArray' => true,
            ));
            
            $subform->addElement($el, "begleitung_{$cntTDs}");
    
        }
        */
         
        
        $subform->addElement('multiCheckbox', 'begleitung', array(
            'label'      => null,
            'separator' => PHP_EOL,
            'required'   => false,
            'multiOptions'=> $lists['partners'],
            'value' => $values['begleitung'],
             
            'decorators' =>   array(
                'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            )
        
        ));
    
    
        return $subform;
    }
    
    public function save_form_partners ($ipid = '', $data = array())
    {
        //__formular comes from WL-Assessment
        if (empty($data['__formular']['formular_date'])) {
            return; //fail-safe, dgp must be in a fall 
        }
        
        $findReadmissionFromDate = PatientReadmission::findReadmissionFromDate($ipid, $data['__formular']['formular_date']);
        
        if (empty($findReadmissionFromDate['admission']['date'])) {
            return; //fail-safe, dgp must be in a fall.. you have no fall for this date
        }
        $patient_readmission_ID =  $findReadmissionFromDate['admission']['id'];
        
        
        $data['begleitung'] =  implode(',' , $data['begleitung']);
        
        
        $entity = new DgpKern();
        return $entity->findOrCreateOneByIpidAndFormTypeAndPatientReadmissionID( $ipid, 'adm', $patient_readmission_ID, $data);
    
    }
    
    
    
    
    public function create_form_ecog ($values =  array() , $elementsBelongTo = null)
    {
    
        $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
        
        $this->mapValidateFunction($__fnName , "create_form_isValid");
        
        $this->mapSaveFunction($__fnName , "save_form_ecog");
        
        
        $subform = $this->subFormTable();
        $subform->setLegend($this->translate('ECOG:'));
        $subform->setAttrib("class", "label_same_size_auto " . $__fnName);
         
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        $lists = DgpKern::get_form_texts();
        $ecog_values = array(''=>'') + $lists['ecog'];   
         
        $subform->addElement('select', 'ecog', array(
            'value'        => $values['ecog'],
            'multiOptions' => $ecog_values,
            'required'     => false,
            'filters'      => array('StringTrim'),
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr' )),
            ),
        ));
         
    
        return $this->filter_by_block_name($subform, $__fnName);
    }
    
    public function save_form_ecog ($ipid = '', $data = array())
    {
        /*
         * __formular comes from WL-Assessment
         * @update 12.2018, it can also be from MamboAssessment
         */
        
        if (empty($data['__formular']['formular_date'])) {
            return; //fail-safe, dgp must be in a fall
        }
        
        
        //TODO-3359 Ancuta 20.08.2020:: add  time to date -  as the forms filled in the same date as admission - were not checked correctly 
        if(isset($data['__formular']['start_time'])){
            $data['__formular']['formular_date'] = $data['__formular']['formular_date'].' '.$data['__formular']['start_time'].':00';
        }
        //--
        
        $findReadmissionFromDate = PatientReadmission::findReadmissionFromDate($ipid, $data['__formular']['formular_date']);
        
        if (empty($findReadmissionFromDate['admission']['date'])) {
            return; //fail-safe, dgp must be in a fall.. you have no fall for this date
        }
        $patient_readmission_ID =  $findReadmissionFromDate['admission']['id'];
        
        
        $entity = new DgpKern();
        return $entity->findOrCreateOneByIpidAndFormTypeAndPatientReadmissionID( $ipid, 'adm', $patient_readmission_ID, $data);
        
    }
    
}
?>