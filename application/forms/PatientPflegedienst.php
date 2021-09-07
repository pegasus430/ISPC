<?php
/**
 * 
 * this is the 'same' form as application/forms/Pflegedienstes.php
 * 
 * @author claudiu 
 * 11 2017
 *
 */
class Application_Form_PatientPflegedienst extends Pms_Form
{

    protected $_model = 'PatientPflegedienste';
    
    public function getVersorgerExtract()
    {
        return  array(
            array("label"=>$this->translate('nice_name'), "cols"=>array("nice_name")),
            array("label"=>$this->translate('phone1'), "cols"=>array("Pflegedienstes" => "phone_practice")),
            array("label"=>$this->translate('Emergency telephone'), "cols"=>array("Pflegedienstes" => "phone_emergency")),
            array("label"=>$this->translate('Emergency comments'), "cols"=>array("pflege_emergency_comment")),
            
        );
    }
    
    
    public function getVersorgerAddress() 
    { 
        return array(
            array(array("nice_name")),
            array(array("Pflegedienstes" => "street1")),
            array(array("Pflegedienstes" => "zip"), array("Pflegedienstes"=>"city")),
        );
        
    }
    
    protected $_block_name_allowed_inputs =  array(
    
        "WlAssessment" => [
            'create_form_patient_pflegedienst' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    
        "PatientDetails" => [
            'create_form_patient_pflegedienst' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    
        "MamboAssessment" => [
            'create_form_patient_pflegedienst' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );

    

    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_patient_pflegedienst_all' => [
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
    
    
	/**
	 * PatientPflegedienst formular
	 * @claudiu 27.11.2017
	 * 
	 * @param array $options, optional values to populate the form
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	public function create_form_patient_pflegedienst($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    	    
	    $this->mapValidateFunction($__fnName, "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName, "save_form_patient_pflegedienst");
	    
	    //@todo $subform or $this? this is the question
	    //A: SUBFORM!
	   
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('pflegedienste'));
	    $subform->setAttrib("class", "label_same_size {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    
	    if ( ! isset($options['Pflegedienstes'])) {
	        $options['Pflegedienstes'] = $options;
	    }
	    
	    
	    /* start with the hidden fields */
	    $subform->addElement('hidden', 'id', array(
	        'value'        => $options['id'] ? $options['id'] : 0 ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        //'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true, 'colspan' => 2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => 'display:none', 'openOnly' => true )),
	
	        ),
	    ));
	    
	    //TODO: add column self_id in Pflegedienstes table.. so you know what-id you selected from the dropdown
	    $subform->addElement('hidden', 'self_id', array(
	        'value'        => isset($options['self_id']) ? $options['self_id'] : $options['pflid'] ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'decorators'   => array('ViewHelper'),
	    ));
	    
	    $subform->addElement('hidden', 'pflid', array(
	        'value'        => $options['pflid'] ? $options['pflid'] : -1 ,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        //'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true )),
	        ),
	    ));
	
	    
	    /* visible inputs */
	    $subform->addElement('text', 'nursing', array(
	        'value'        => $options['Pflegedienstes']['nursing'] ,
	        'label'        => $this->translate('pflegedienste'),
	        'required'     => true,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	
	        ),
	        'data-livesearch'  => 'Careservice',
	    ));
		//Maria:: Migration CISPC to ISPC 20.08.2020
		//ISPC-2624 Elena ? 
        $subform->addElement('text', 'first_name', array(
            'value'        => $options['Pflegedienstes']['first_name'] ,
            'label'        => $this->translate('firstname'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'last_name', array(
	        'value'        => $options['Pflegedienstes']['last_name'] ,
	        'label'        => $this->translate('lastname'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'salutation', array(
	        'value'        => $options['Pflegedienstes']['salutation'] ,
	        'label'        => $this->translate('salutation'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'street1', array(
	        'value'        => $options['Pflegedienstes']['street1'] ,
	        'label'        => $this->translate('address'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'zip', array(
	        'value'        => $options['Pflegedienstes']['zip'] ,
	        'label'        => $this->translate('zip'),
	        'data-livesearch'  => 'zip',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),doctornumber
	    ));
	    $subform->addElement('text', 'city', array(
	        'value'        => $options['Pflegedienstes']['city'],
	        'label'        => $this->translate('city'),
	        'data-livesearch'   => 'city',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'phone_practice', array(
	        'value'        => $options['Pflegedienstes']['phone_practice'],
	        'label'        => $this->translate('phone1'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'phone_emergency', array(
	        'value'        => $options['Pflegedienstes']['phone_emergency'],
	        'label'        => $this->translate('Emergency telephone'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'fax', array(
	        'value'        => $options['Pflegedienstes']['fax'],
	        'label'        => $this->translate('fax'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'email', array(
	        'value'        => $options['Pflegedienstes']['email'],
	        'label'        => $this->translate('email'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty', 'EmailAddress'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('text', 'ik_number', array(
	        'value'        => $options['Pflegedienstes']['ik_number'],
	        'label'        => $this->translate('IK number'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('textarea', 'pflege_comment', array(
	        'value'        => ! empty($options['pflege_comment']) ?  $options['pflege_comment'] : $options['Pflegedienstes']['comments'],
	        'label'        => $this->translate('comments'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'rows'         => 3,
	        'cols'         => 60,
	    ));
	
	    
	    $subform->addElement('checkbox', 'is_contact', array(
	        'value'        => $options['Pflegedienstes']['is_contact'],
	        'label'        => $this->translate('real_contact_number'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('Int'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    $subform->addElement('checkbox', 'palliativpflegedienst', array(
	        'value'        => $options['Pflegedienstes']['palliativpflegedienst'],
	        'label'        => $this->translate('is palliative care service'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('Int'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    
	    $display = $options['pflege_emergency'] !=0 ? "" : "display:none";
	    
	    $subform->addElement('radio', 'pflege_emergency', array(
	        'value'        => ! empty($options['pflege_emergency']) ?  $options['pflege_emergency'] : 0,
	        'label'        => $this->translate('pflege_emergency'),
	        'multiOptions' => PatientPflegedienste::getDefaultsPflegeEmergency(),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('Int'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td' , 'class' =>'label_same_size_auto')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	        'separator'  => '&nbsp;',
	        'onChange'  => "if(this.value=='0') {\$('tr[data-name=\'pflege_emergency_comment\']', $(this).parents('table')).hide();} else {\$('tr[data-name=\'pflege_emergency_comment\']', $(this).parents('table')).show();};",
	         
	        
	    ));
	    $subform->addElement('textarea', 'pflege_emergency_comment', array(
	        'value'        => ! empty($options['pflege_emergency_comment']) ?  $options['pflege_emergency_comment'] : '',
	        'label'        => $this->translate('comments'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array('Label', array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'data-name' =>'pflege_emergency_comment', 'style' => $display)),
	        ),
	        'rows'         => 3,
	        'cols'         => 60,
	    ));
	    
	    


	    //ispc-2291
	    if ($this->_patientMasterData['ModulePrivileges'][182]) {
	    
	    
	        //             "Name Nursing Career" - TEXT FIELD
	        //             "Qualification" - DROPDOWN with (nurse, health and child nurse, nurse, geriatric nurse)
	        //             "Additional qualification" - TEXT FIELD
	        //             "Name nurse providing care in case of substitution" - TEXT FIELD
	        //             "Qualification" - DROPDOWN with (nurse, health and child nurse, nurse, geriatric nurse)
	        //             "Additional qualification" - TEXT FIELD
	    
	        $subform->addElement('text', 'nursing_career', array(
	    
	            'value'    => ! empty($options['Pflegedienstes']['nursing_career']) ? $options['Pflegedienstes']['nursing_career'] : null,
	            'label'      => "Name Nursing Career",
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr',
	                )),
	            ),
	            'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.alcohol_frequency_on').hide();} else {\$(this).parents('table').find('.alcohol_frequency_on').show();}",
	    
	        ));
	        $subform->addElement('select', 'qualification', array(
	    
	            'value'    => ! empty($options['Pflegedienstes']['qualification']) ? $options['Pflegedienstes']['qualification'] : null,
	    
	            'multiOptions' => $this->getColumnMapping('qualification'),
	            'label'      => "Qualification",
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr',
	                )),
	            ),
	            'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.alcohol_frequency_on').hide();} else {\$(this).parents('table').find('.alcohol_frequency_on').show();}",
	    
	        ));
	         
	        $subform->addElement('text', 'qualification_extra', array(
	            'value'        => $options['Pflegedienstes']['qualification_extra'],
	            'label'        => 'Additional qualification',
	            'required'     => false,
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	        
	        
	        $subform->addElement('text', 'substitution_nurse', array(
	            'value'    => ! empty($options['Pflegedienstes']['substitution_nurse']) ? $options['Pflegedienstes']['substitution_nurse'] : null,
	            'label'      => "Name nurse providing care in case of substitution",
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr',
	                )),
	            ),
	            'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.alcohol_frequency_on').hide();} else {\$(this).parents('table').find('.alcohol_frequency_on').show();}",
	    
	        ));
	        $subform->addElement('select', 'substitution_qualification', array(
	    
	            'value'    => ! empty($options['Pflegedienstes']['substitution_qualification']) ? $options['Pflegedienstes']['substitution_qualification'] : null,
	    
	            'multiOptions' => $this->getColumnMapping('substitution_qualification'),
	            'label'      => "Qualification",
	            'required'   => false,
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td', )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr',
	                )),
	            ),
	            'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.alcohol_frequency_on').hide();} else {\$(this).parents('table').find('.alcohol_frequency_on').show();}",
	    
	        ));
	         
	        $subform->addElement('text', 'substitution_qualification_extra', array(
	            'value'        => $options['Pflegedienstes']['substitution_qualification_extra'],
	            'label'        => 'Additional qualification',
	            'required'     => false,
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	                array('Label', array('tag' => 'td')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	            ),
	        ));
	    }
	
	    return $this->filter_by_block_name($subform , $__fnName);
	
	}

	

	public function create_form_patient_pflegedienst_all($options =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn

	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'patient_pflegedienst_accordion accordion_c'));
	    $subform->setLegend($this->translate('pflegedienste'));
	    $subform->setAttrib("class", "label_same_size {$__fnName}");
	     
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	     
	    
	    
	    if ( ! empty($options) && is_array($options))
	    {
	        $cp_counter = 0;
	        
	        foreach($options as $one_pflegedienst) {
	    
	            $cp_arr = array($one_pflegedienst);
	            PatientPflegedienste::beautifyName($cp_arr);
	            $one_pflegedienst = $cp_arr[0];
	    
	            $one_pflegedienst_form = $this->create_form_patient_pflegedienst($one_pflegedienst);
	            $one_pflegedienst_form->setLegend($one_pflegedienst_form->getLegend() . ' : ' .$one_pflegedienst['nice_name']);
	            $subform->addSubForm($one_pflegedienst_form, $cp_counter);
	            
	            $cp_counter++;
	            
	        }
	        
	    } else {
	        $this->create_form_patient_pflegedienst();//just so we have the mapping for save
	    }
	     
	     
	    //add button to add new contacts
	    $subform->addElement('button', 'addnew_patientpflegedienst', array(
	        'onClick'      => "PatientPflegedienst_addnew(this, 'PatientPflegediensts'); return false;",
	        'value'        => '1',
	        'label'        => $this->translate('Add new patient nursing'),
	        'decorators'   => array(
	            'ViewHelper',
	            'FormElements',
	            array('HtmlTag', array('tag' => 'div')),
	        ),
	        'class'        =>'button btnSubmit2018 plus_icon_bg dontPrint',
	    ));
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	/**
	 *
	 * @param string $ipid
	 * @param array $data
	 * @param number $indrop 0 = in the liveSearch, 1 = not
	 * @return void|Doctrine_Record
	 */
	public function save_form_patient_pflegedienst($ipid =  '', $data = array(), $indrop = 1)
	{
	    $patientModel   = 'PatientPflegedienste';
	    $relationModel  = 'Pflegedienstes';
	
	    $ipid = ! empty($ipid) ? $ipid : $this->_ipid ;
	
	    if (empty($ipid) || empty($data)) {
	        return;//fail-safe
	    }
	
	    $entity = new $patientModel();
	    //IPSC-2614
	    $pc_listener = $entity->getListener()->get('IntenseConnectionListener');
	    $pc_listener->setOption('disabled', true);
	    //--
	    $entity = $entity->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	
	    if ( ! $entity) {
	        return; //fail-safe
	    }
	
	    $localField = null;
	    $foreignField = null;
	
	    if ($relation = $entity->getTable()->getRelation($relationModel, false)) {
	        $relation = $relation->toArray();
	        $localField = $relation['local'];
	        $foreignField = $relation['foreign'];
	    }
	
	
	    if ( ! is_null($localField) && ! is_null($foreignField) && $data[$localField] != $entity->{$localField}) {
	        $data[$localField] = $entity->{$localField};
	    }
	
	    $data['indrop'] = $indrop;
	
	
	    $relationEntity = new $relationModel();
	    $relationEntity = $relationEntity->findOrCreateOneBy(['id', 'clientid'], [$data[$localField], $this->logininfo->clientid], $data);
	
	    if ($relationEntity && ! is_null($localField) && $entity->{$localField} != $relationEntity->{$foreignField}) {
	        //it was a new one
	        $entity->{$localField} = $relationEntity->{$foreignField};
	        $entity->save();
	
            if (empty($data['self_id']) ) {
                $this->_manual_nurse_message_send($relationEntity);
            }
	    }
	
	    
	    //IPSC-2614
	    $pc_listener->setOption('disabled', false);
	    //--
	    
	    //ISPC-2614 Ancuta :: Hack to re-trigger listner -
	    $entity->pflege_comment = $data['pflege_comment'].' ';
	    $entity->save();
	    //-- 
	    
	    return $entity;
	
	}
	
	

	/**
	 * if you have module 164 = Family Doc manually added -> send message
	 * send message when a new dowctor was added
	 *
	 * @param Pharmacy $fdoc
	 * @return void
	 */
	private function _manual_nurse_message_send(Pflegedienstes $fdoc)
	{
	     
	    $modules = new Modules();
	    if( ! $modules->checkModulePrivileges(164)) {
	        return;
	    }
	
	    $doctor_first_last_name = $fdoc->nursing . ',  ' . $fdoc->first_name . " " . $fdoc->last_name;
	
	    $patientMasterData =  $this->_patientMasterData;
	    $pat_encoded_id = $patientMasterData['id'] ? Pms_Uuid::encrypt($patientMasterData['id']) : 0;
	    $patientLink = "<a href='patientcourse/patientcourse?id={$pat_encoded_id}'>{$patientMasterData['epid']}</a>";
	
	    $users = User::get_AllByClientid($this->logininfo->clientid, array('us.manual_familydoc_message', 'username'));
	
	    //remove inactive and deleted, and the ones with clientid=0
	    $users = array_filter($users, function($user) {
	        return ( ! $user['isdelete']) && ( ! $user['isactive']) && ($user['clientid'] > 0) && ($user['UserSettings']['manual_familydoc_message'] == 'yes');
	    });
	

        if (empty($users)) {
            return; // no settings
        }
        
        //remove inactive and deleted, and the ones with clientid=0
        $users_with_emails = array_filter($users, function($user) {
            return strlen(trim($user['emailid']));
        });

    
        $message_title = $this->translate("New Nurse was manualy added");
        $message_title_enc = Pms_CommonData::aesEncrypt($message_title);
         
         
        $message_body = $this->translate('New Nurse (%s) was manualy added, please take action on %s', $doctor_first_last_name, $patientLink);
        $message_body = Pms_CommonData::br2nl($message_body);
        $message_body_enc = Pms_CommonData::aesEncrypt($message_body);
    
    
        $recipients = array_column($users, 'id');
         
        $records_template = array(
            "sender" => $this->logininfo->userid,
            "clientid" => $this->logininfo->clientid,
            "recipient" => null,
            "recipients" => implode(",", $recipients),
            "msg_date" => date("Y-m-d H:i:s", time()),
            "title" => $message_title_enc,
            "content" => $message_body_enc,
            "create_date" => date("Y-m-d", time()),
            "create_user" => $this->logininfo->userid,
        );
         
        $records_array = array();
        foreach($users as $user) {
            $record = $records_template;
            $record['recipient'] = $user['id'];
            $records_array[] = $record;
        }
        if ( ! empty($records_array)) {
            $collection = new Doctrine_Collection('Messages');
            $collection->fromArray($records_array);
            $collection->save();
        }
         
         
        //send email too ??
        $additional_text =
        $message_body = $this->translate('New Nurse (%s) was manualy added, please take action on %s', $doctor_first_last_name, $patientMasterData['epid']);
        //$message_body .= "<br/> <br/> Klicken Sie hier um sich einzuloggen --> <a href='https://www.ispc-login.de' target='_blank' >www.ispc-login.de</a>"; // link to ISPC
        // ISPC-2475 @Lore 31.10.2019
        $message_body .= $this->translate('system_wide_email_text_login');
        
        
        //TODO-3164 Ancuta 08.09.2020
        $email_data = array();
        $email_data['additional_text'] = $additional_text;
        $message_body = "";//overwrite
        $message_body = Pms_Template::createTemplate($email_data, 'templates/email_template_v1.html');
        //--
        
        
        $this->_mail_forceDefaultSMTP = false;
        foreach($users_with_emails  as $user) {
            $this->sendEmail( $user['emailid'] , "ISPC - {$message_title}", $message_body);
        }
         
        return;

	}
	
	
	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        'qualification' => [
	            ''  => '---' //extra empty value for select
	        ],
	        'substitution_qualification' => [
	            ''  => '---' //extra empty value for select
	        ],
        ];
	
	
	    $values = Doctrine_Core::getTable('Pflegedienstes')->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}
?>