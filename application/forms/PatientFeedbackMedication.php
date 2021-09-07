<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 10, 2018
 *
 */
class Application_Form_PatientFeedbackMedication extends Pms_Form
{

    protected $_model = 'PatientFeedbackMedication';
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientFeedbackMedication::TRIGGER_FORMID;
    private $triggerformname = PatientFeedbackMedication::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientFeedbackMedication::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array(
        "MamboAssessment" => [
            'create_form_family_treatment' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );
    
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_knows_medication' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //"inclusion_measures",
            ],
            'create_form_medication_intake' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
//     	        "training_adherence",
//     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
//                 "inclusion_measures",
            ],
            'create_form_nationwide_medicationplan' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //"inclusion_measures",
            ],
            'create_form_rate_medication' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //"inclusion_measures",
            ],
            'create_form_sleep_medication' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //"inclusion_measures",
            ],
            'create_form_takes_regularly' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //"inclusion_measures",
            ],
            
            
            
            
        ],
    ];
    
    
	//Do you know why you take which medications?
	public function create_form_knows_medication ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_medication");
	    
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Do you know why you take which medications?'));
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	     
	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	        ),
	    ));
	    
	    
	    
	    $subform->addElement('select', 'knows_medication', array(
	         
	        'value'    => ! empty($values['knows_medication']) ? $values['knows_medication'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('knows_medication'),
	         
	        'label'      => null,
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>1)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        	        	         
	    ));
	    
	    
	     
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);	 
	}
	

	
	
	
	//Do you take your medication regularly?
	public function create_form_takes_regularly($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_medication");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Do you take your medication regularly?'));
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	     
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	
	
	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	        ),
	    ));
	     
	     
	     
	    $subform->addElement('select', 'takes_regularly', array(
	
	        'value'    => ! empty($values['takes_regularly']) ? $values['takes_regularly'] : null,
	
	        'multiOptions' => $this->getColumnMapping('takes_regularly'),
	
	        'label'      => null,
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_first', 'colspan'=>1)),
	            // 	                	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'openOnly' => true,
	            )),
	        ),
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.takes_regularly_no').show();} else {\$(this).parents('table').find('.takes_regularly_no').hide();}",
	
	    ));
	     
	    $display = $values['takes_regularly'] == 'no' ? '' : "display:none";
	     
	    $subform->addElement('select', 'takes_regularly_no', array(
	
	        'value'        => ! empty($values['takes_regularly_no']) ? $values['takes_regularly_no'] : null,
	
	        'multiOptions' => $this->getColumnMapping('takes_regularly_no'),
	         
	        'label'        => null,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data takes_regularly_no',
	                'colspan'  => 3,
	                "style"    => $display,	                 
	            )),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
            ),
	    ));
	    
	    

	    
	    
	    
	    
	    
	    
	    
	    
	    $subform->addElement('note', 'Dosage_intake_independently', array(
	        'label'        => null,
	        'value'        => "<div class='legend'>".self::translate('Dosage and intake take place independently?') . "</div>",
	        
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data',
	                'colspan'  => 4,
	        
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
            ),
	    ));
	     
	    $subform->addElement('select', 'dosage_intake', array(
	    
	        'value'    => ! empty($values['dosage_intake']) ? $values['dosage_intake'] : null,
	    
	        'multiOptions' =>  $this->getColumnMapping('dosage_intake'),
	    
	        'label'      => null,
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td', 
	                'class'    => 'element print_column_data', 
	                'colspan'  =>4,
// 	                'rowspan'  => 2,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
// 	                'openOnly' => true,
	            )),
	        ),
	        
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.dosage_intake_no').show();} else {\$(this).parents('table').find('.dosage_intake_no').hide();}",
	    
	    ));

	    
	    $display = $values['dosage_intake'] == 'no' ? '' : "display:none";
	    
	    $subform->addElement('select', 'dosage_intake_helper', array(
	    
	        'value'        => ! empty($values['dosage_intake_helper']) ? $values['dosage_intake_helper'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('dosage_intake_helper'),
	    
	        'label'        => $this->translate('Help should be done by'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data dosage_intake_helper',
	                'colspan'  => 3,
	            )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                "style"    => $display,
	                "class"    => "dosage_intake_no",
// 	                'closeOnly' => true,
	            )),
	        ),
	    
	    
	    ));
	     
	    
	    $subform->addElement('multiCheckbox', 'dosage_intake_help', array(
	         
	        'value'        => ! empty($values['dosage_intake_help']) ? $values['dosage_intake_help'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('dosage_intake_help'),
	         
	        'label'        => $this->translate('Help is required'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data dosage_intake_help dosage_intake_no',
	                'colspan'  => 3,
	            )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                "class"    => "dosage_intake_no",
	                "style"    => $display,
	            )),
	        ),
	         
	         
	    ));
	    
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	
	
	
	
	
	//How do you rate your medication?
	public function create_form_rate_medication ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_medication");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('How do you rate your medication?'));
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	     
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	
	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	        ),
	    ));
	     
	     
	     
	    $subform->addElement('select', 'rate_medication', array(
	
	        'value'    => ! empty($values['rate_medication']) ? $values['rate_medication'] : null,
	
	        'multiOptions' => $this->getColumnMapping('rate_medication'),
	
	        'label'      => null,
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	
	    ));	     
	    
	     
	
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	//Do you take medication regularly to sleep?
	public function create_form_sleep_medication($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_medication");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Do you take medication regularly to sleep?'));
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	
	
	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	        ),
	    ));
	
	
	
	    $subform->addElement('select', 'takes_sleep_medication', array(
	
	        'value'    => ! empty($values['takes_sleep_medication']) ? $values['takes_sleep_medication'] : null,
	
	        'multiOptions' => $this->getColumnMapping('takes_sleep_medication'),
	
	        'label'      => null,
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
	            // 	                	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
// 	                'openOnly' => true,
	            )),
	        ),
	        'onChange' => "if (this.value=='yes') {\$(this).parents('table').find('.takes_sleep_medication_yes').show();} else {\$(this).parents('table').find('.takes_sleep_medication_yes').hide();}",
	
	    ));
	
	    $display = $values['takes_sleep_medication'] == 'yes' ? '' : "display:none";
	
	    $subform->addElement('checkbox', 'sleep_medication_in_module', array(
	
	        'value'        => ! empty($values['sleep_medication_in_module']) ? $values['sleep_medication_in_module'] : null,
	
	        'checkedValue'    => 'yes',
	        'uncheckedValue'  => 'no',
	        
	
	        'label'        => $this->translate("Is the drug registered in the medication module?"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data',
	                'colspan'  => 3,
	            )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first takes_sleep_medication_yes')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'class' => 'takes_sleep_medication_yes',
	                "style"    => $display,
	            )),
	        ),
	    ));
	    
	    
	
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	
	
	
	
	
	
	
	//Liegt der bundeseinheitliche Medikationsplan vor?
	//Is the nationwide medication plan available?
	public function create_form_nationwide_medicationplan ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_medication");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Is the nationwide medication plan available?'));
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	
	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	        ),
	    ));
	
	

	    $subform->addElement('select', 'nationwide_medicationplan', array(
	    
	        'value'        => ! empty($values['nationwide_medicationplan']) ? $values['nationwide_medicationplan'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('nationwide_medicationplan'),
	    
	        'label'        => null,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data',
	                'colspan'  =>4,
	    
	            )),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    
	    
	    ));
	
	
	
	
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	//Wie nehmen Sie Ihre Medikamente ein?
	//How do you take your medication?
	public function create_form_medication_intake ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_medication");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('How do you take your medication?'));
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	
	
	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	        ),
	    ));
	
	
	
	    $subform->addElement('select', 'intake_sametime', array(
	         
	        'value'        => ! empty($values['intake_sametime']) ? $values['intake_sametime'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('intake_sametime'),
	         
	        'label'        => $this->translate("Always at the same time"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data',
	                'colspan'  => 3,
	                 
	            )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	         
	         
	    ));
	
	
	    

	    $subform->addElement('radio', 'intake_with_water', array(
	    
	        'value'        => ! empty($values['intake_with_water']) ? $values['intake_with_water'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('intake_with_water'),
	    
	        'label'        => $this->translate("With tap water"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data_autowidth',
	                'colspan'  => 1,
	    
	            )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                "openOnly" => true,
	            )),
	        ),
	        
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.intake_with_water_freetext').show();} else {\$(this).parents('table').find('.intake_with_water_freetext').hide();}",
	         
	    ));
	    
	    $display = $values['intake_with_water'] == 'no' ? '' : "display:none";
	    
	    $subform->addElement('text', 'intake_with_water_freetext', array(
	        'value'        => ! empty($values['intake_with_water_freetext']) ? $values['intake_with_water_freetext'] : null,
	        'placeholder'  => $this->translate("Nein, sondern mit"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'colspan'  => 2,
	                'tag'      => 'td',
	                'class'    => 'intake_with_water_freetext',
        	        'style'    => $display,
// 	                "closeOnly" => true,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
	    ));
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    $subform->addElement('select', 'intake_with_meals', array(
	         
	        'value'        => ! empty($values['intake_with_meals']) ? $values['intake_with_meals'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('intake_with_meals'),
	         
	        'label'        => $this->translate("With meals"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data_autowidth',
	                'colspan'  => 3,
	                 
	            )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    ));
	    
	    
	    
	    $subform->addElement('select', 'intake_device', array(
	    
	        'value'        => ! empty($values['intake_device']) ? $values['intake_device'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('intake_device'),
	    
	        'label'        => $this->translate("Secured use of devices (compliance with therapy or illness-related behavioral regulations)"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data_autowidth',
	                'colspan'  => 1,
	    
	            )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'openOnly' => true,
	            )),
	        ),
	        
	        'onChange' => "if (this.value=='yes') {\$(this).parents('table').find('.intake_device_yes').show();} else {\$(this).parents('table').find('.intake_device_yes').hide();}",
	         
	    ));
	
	    
	    $display = $values['intake_device'] == 'yes' ? '' : "display:none";

	     
	    $subform->addElement('text', 'intake_device_frequency', array(
	        'value'        => ! empty($values['intake_device_frequency']) ? $values['intake_device_frequency'] : null,
	        'placeholder'  => $this->translate("intake frequency"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
// 	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'colspan'  => 2,
	                'tag'      => 'td',
	                'class'    => 'intake_device_yes',
	                "openOnly" => true,
	                'style'    => $display,
	            )),
	        ),
	    ));
	     
	     

	    $subform->addElement('multiCheckbox', 'intake_device_yes', array(
	         
	        'value'        => ! empty($values['intake_device_yes']) ? $values['intake_device_yes'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('intake_device_yes'),
	         
	        'label'        => null,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'div',
	            )),
	        ),
	        
	        'onChange' => "if (this.value=='miscellaneous' && this.checked) {\$(this).parents('table').find('.intake_device_freetext').show();} else if (this.value=='miscellaneous' && ! this.checked) {\$(this).parents('table').find('.intake_device_freetext').hide();}",
	    
	         
	    ));
	    
	    $display = in_array('miscellaneous', $values['intake_device_yes']) ? '' : "display:none";
	    
	    $subform->addElement('text', 'intake_device_freetext', array(
	        'value'        => ! empty($values['intake_device_freetext']) ? $values['intake_device_freetext'] : null,
	        'placeholder'  => $this->translate("freetext"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'closeOnly' => true,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
	        'class' => "intake_device_freetext",
	        'style'    => $display,
        ));
	    
	    
	    
	    
	    
	    
	    
	    $subform->addElement('multiCheckbox', 'diary', array(
	    
	        'value'        => ! empty($values['diary']) ? $values['diary'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('diary'),
	    
	        'label'        => $this->translate('Diary'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data_autowidth',
	                'colspan'  => 3,
	                'openOnly' => true,
	            )),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'openOnly' => true,
	            )),
	        ),
	        'onChange' => "if (this.value=='miscellaneous' && this.checked) {\$(this).parents('table').find('.diary_freetext').show();} else if (this.value=='miscellaneous' && ! this.checked) {\$(this).parents('table').find('.diary_freetext').hide();}",
	         
	    
	    ));
	    
	    
	    $display = in_array('miscellaneous', $values['diary']) ? '' : "display:none";
	     
	    $subform->addElement('text', 'diary_freetext', array(
	         
	        'value'        => ! empty($values['diary_freetext']) ? $values['diary_freetext'] : null,
	        'placeholder'  => $this->translate("freetext"),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
                    "closeOnly" => true,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
	        'class'    => 'diary_freetext',
	        'style'    => $display,
	        
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	     
	public function save_form_patient_feedback_medication ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	     
	     
	    $entity = PatientFeedbackMedicationTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	     
	     
	    return $entity;
	}
	


	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        
	        'takes_regularly' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        'takes_regularly_no' => [
	            ''  => '---' //extra empty value for select
	        ],
	        
	        'dosage_intake' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        'dosage_intake_helper' => [
	            ''  => '---' //extra empty value for select
	        ],
	    
	        'rate_medication' => [
	            ''  => '---' //extra empty value for select
	        ],
	        
	        'takes_sleep_medication' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        'knows_medication' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        'nationwide_medicationplan' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        
	        'intake_sametime' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        'intake_with_water' => [
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        'intake_with_meals' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        'intake_device' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        
	        
	    ];
	
	
	    $values = PatientFeedbackMedicationTable::getInstance()->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}