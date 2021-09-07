<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 10, 2018
 *
 */
class Application_Form_PatientFeedbackGeneral extends Pms_Form
{

    protected $_model = 'PatientFeedbackGeneral';
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientFeedbackGeneral::TRIGGER_FORMID;
    private $triggerformname = PatientFeedbackGeneral::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientFeedbackGeneral::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array(
        "MamboAssessment" => [
            'create_form_assistance_healthinsurance' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_assistance_healthinsurance' => [
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
            'create_form_behaviors_mental' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
//                 "further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
//                 "inclusion_measures",
            ],
            'create_form_cognitive_communicative' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
//                 "further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
                //     	        "training_prevention",
                //     	        "training_incontinence",
                //     	        "organization_careaids",
                //     	        "inclusion_COPD",
//                 "inclusion_measures",
            ],
            'create_form_continence' => [
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
//  	            "training_incontinence",
//     	        "organization_careaids",
                //     	        "inclusion_COPD",
//                 "inclusion_measures",
            ],
            'create_form_coping_everyday' => [
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
 	            //"training_incontinence",
//     	        "organization_careaids",
                //     	        "inclusion_COPD",
                //"inclusion_measures",
            ],
            'create_form_fall_hazards' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
//      	        "training_prevention",
 	            //"training_incontinence",
//     	        "organization_careaids",
                //"inclusion_COPD",
//                 "inclusion_measures",
            ],
            'create_form_housekeeping' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
     	        //"training_prevention",
 	            //"training_incontinence",
    	        //"organization_careaids",
                //"inclusion_COPD",
//                 "inclusion_measures",
            ],
            'create_form_mobility' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
     	        //"training_prevention",
 	            //"training_incontinence",
    	        //"organization_careaids",
                //"inclusion_COPD",
                //"inclusion_measures",
            ],
            'create_form_nutrition' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
     	        //"training_prevention",
 	            //"training_incontinence",
//     	        "organization_careaids",
                //"inclusion_COPD",
                //"inclusion_measures",
            ],
            'create_form_social_integration_everyday_life' => [
                "todo",
                "feedback",
//                 "benefit_plan",
                //     	        "heart_monitoring",
                //     	        "referral_to",
                //"further_assessment",
                //"training_nutrition",
                //     	        "training_adherence",
                //     	        "training_device",
     	        //"training_prevention",
 	            //"training_incontinence",
    	        //"organization_careaids",
                //"inclusion_COPD",
//                 "inclusion_measures",
            ],
        ],
    ];
    
    
	public function create_form_assistance_healthinsurance ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	    
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Assistance from health insurance (according to § 37 SGB V right to home care)'));
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
	    
	    
	    
	    $subform->addElement('multiCheckbox', 'assistance_healthinsurance', array(
	         
	        'value'    => ! empty($values['assistance_healthinsurance']) ? $values['assistance_healthinsurance'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('assistance_healthinsurance'),
	         
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
	    
	    $subform->addElement('textarea', 'assistance_healthinsurance_freetext', array(
	    
	        'value'    => ! empty($values['assistance_healthinsurance_freetext']) ? $values['assistance_healthinsurance_freetext'] : null,
	        
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	     
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);	 
	}
	

	public function create_form_mobility ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Mobility'));
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
	     
	     
	    $subform->addElement('note', 'infotext', array(
	        'label'        => null,
	        'value'        => $this->translate('(z.B. Alltagsbewältigung, mobil außer Haus, mobil im Wohnbereich, Treppensteigen)'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'class' => 'element infotext',
	                'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    ));
	    
	     
	    $subform->addElement('select', 'mobility', array(
	
	        'value'    => ! empty($values['mobility']) ? $values['mobility'] : null,
	
	        'multiOptions' => $this->getColumnMapping('mobility'),
	
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
	     
	    $subform->addElement('textarea', 'mobility_freetext', array(
	         
	        'value'    => ! empty($values['mobility_freetext']) ? $values['mobility_freetext'] : null,
	         
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	public function create_form_fall_hazards ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Fall Hazards'));
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
	     
	     
	     
	    $subform->addElement('select', 'fall_hazards', array(
	
	        'value'    => ! empty($values['fall_hazards']) ? $values['fall_hazards'] : null,
	
	        'multiOptions' => $this->getColumnMapping('fall_hazards'),
	
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
	                'openOnly'      => true,
	            )),
	        ),
	
	        'onChange' => "if (this.value=='yes') {\$(this).parents('table').find('.fall_hazards_yes').show();} else {\$(this).parents('table').find('.fall_hazards_yes').hide();}",
	         
	    ));
	     
	     
	    $display = $values['fall_hazards'] == 'yes' ? '' : "display:none";
	     
	    $subform->addElement('multiCheckbox', 'fall_hazards_yes', array(
	        
	        'value'        => ! empty($values['fall_hazards_yes']) ? $values['fall_hazards_yes'] : null,

	        'multiOptions' => $this->getColumnMapping('fall_hazards_yes'),
	         
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'colspan'  => 3,
	                'tag'      => 'td',
	                'class'    => 'fall_hazards_yes',
	                'style'    => $display,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
	    ));
	
	     
	    $subform->addElement('textarea', 'fall_hazards_freetext', array(
	    
	        'value'    => ! empty($values['fall_hazards_freetext']) ? $values['fall_hazards_freetext'] : null,
	    
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	public function create_form_cognitive_communicative ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Cognitive and communicative skills'));
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
	     
	    
	    $subform->addElement('note', 'infotext', array(
	        'label'        => null,
	        'value'        => $this->translate('(z.B. Erkennen von Personen, örtliche und zeitliche Orientierung, Erinnern an wesentliche Ereignisse oder Beobachtungen, Steuern mehrschrittigen Alltagshandlungen, Treffen von Entscheidungen im Alltagsleben, Erkennen von Risiken und Gefahren)'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'class' => 'element infotext',
	                'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    ));
	     
	    $subform->addElement('select', 'cognitive_communicative', array(
	
	        'value'    => ! empty($values['cognitive_communicative']) ? $values['cognitive_communicative'] : null,
	
	        'multiOptions' => $this->getColumnMapping('cognitive_communicative'),
	
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
	     
	    $subform->addElement('textarea', 'cognitive_communicative_freetext', array(
	         
	        'value'    => ! empty($values['cognitive_communicative_freetext']) ? $values['cognitive_communicative_freetext'] : null,
	         
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	public function create_form_behaviors_mental ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Behaviors and mental problems'));
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
	     
	     
	    $subform->addElement('note', 'infotext', array(
	        'label'        => null,
	        'value'        => $this->translate('(z.B selbstschädigendes und autoaggressives Verhalten, Beschädigen von Gegenständen, physisch aggressives Verhalten, Abwehr pflegerischer oder anderer unterstützender Maßnahmen, Ängste, depressive Stimmungslage)'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td', 
	                'class' => 'element infotext', 
	                'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    ));
	     
	     
	    
	     
	    $subform->addElement('select', 'behaviors_mental', array(
	
	        'value'    => ! empty($values['behaviors_mental']) ? $values['behaviors_mental'] : null,
	
	        'multiOptions' => $this->getColumnMapping('behaviors_mental'),
	
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
	                'openOnly' => true,
	            )),
	        ),
	        
	        
	        'onChange' => "if (this.value=='more often a week' || this.value == 'Every day') {\$(this).parents('table').find('.behaviors_mental_yes').show();} else {\$(this).parents('table').find('.behaviors_mental_yes').hide();}",
	         
	
	    ));
	    

	    $display = ($values['behaviors_mental'] == 'more often a week' || $values['behaviors_mental'] == 'Every day') ? '' : "display:none";
	    
	    $subform->addElement('select', 'behaviors_mental_yes', array(
	         
	        'value'        => ! empty($values['behaviors_mental_yes']) ? $values['behaviors_mental_yes'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('behaviors_mental_yes'),
	    
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'colspan'  => 3,
	                'tag'      => 'td',
	                'class'    => 'behaviors_mental_yes',
	                'style'    => $display,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
	    ));
	    
	    
	     
	    $subform->addElement('textarea', 'behaviors_mental_freetext', array(
	    
	        'value'    => ! empty($values['behaviors_mental_freetext']) ? $values['behaviors_mental_freetext'] : null,
	    
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	public function create_form_nutrition ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Nutrition'));
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
	     
	     
	     
	    $subform->addElement('select', 'nutrition', array(
	
	        'value'    => ! empty($values['nutrition']) ? $values['nutrition'] : null,
	
	        'multiOptions' => $this->getColumnMapping('nutrition'),
	
	        'label'      => $this->translate("Nutrition"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	
	    ));
	     
	    $subform->addElement('select', 'difficulty_drinking', array(
	    
	        'value'    => ! empty($values['difficulty_drinking']) ? $values['difficulty_drinking'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('difficulty_drinking'),
	    
	        'label'      => $this->translate("Difficulty drinking"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    
	    ));
	    
	    $subform->addElement('select', 'difficulty_eating', array(
	         
	        'value'    => ! empty($values['difficulty_eating']) ? $values['difficulty_eating'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('difficulty_eating'),
	         
	        'label'      => $this->translate("Difficulty in eating"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	         
	    ));
	     
	    $subform->addElement('textarea', 'nutrition_freetext', array(
	         
	        'value'    => ! empty($values['nutrition_freetext']) ? $values['nutrition_freetext'] : null,
	         
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	public function create_form_continence ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Continence'));
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
	     
	     
	     
	    $subform->addElement('multiCheckbox', 'continence', array(
	
	        'value'    => ! empty($values['continence']) ? $values['continence'] : null,
	
	        'multiOptions' => $this->getColumnMapping('continence'),
	
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
	     
	     
	    $subform->addElement('textarea', 'continence_freetext', array(
	    
	        'value'    => ! empty($values['continence_freetext']) ? $values['continence_freetext'] : null,
	    
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	public function create_form_coping_everyday ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Coping with the everyday situation'));
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
	     
	     
	    $subform->addElement('note', 'infotext', array(
	        'label'        => null,
	        'value'        => $this->translate('(Körperhygiene, An- und Auskleiden, Toilettenbenutzung)'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'class' => 'element infotext',
	                'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    ));
	    
	    $subform->addElement('select', 'coping_everyday', array(
	
	        'value'    => ! empty($values['coping_everyday']) ? $values['coping_everyday'] : null,
	
	        'multiOptions' => $this->getColumnMapping('coping_everyday'),
	
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
	     
	     
	    $subform->addElement('textarea', 'coping_everyday_freetext', array(
	         
	        'value'    => ! empty($values['coping_everyday_freetext']) ? $values['coping_everyday_freetext'] : null,
	         
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	public function create_form_housekeeping ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Housekeeping'));
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
	     
	     
	    $subform->addElement('note', 'infotext', array(
	        'label'        => null,
	        'value'        => $this->translate('(Einkaufen, Zubereitung der Mahlzeiten, Aufräum- und Reinigungsarbeiten, Wäsche, Erledigen von Behördengängen)'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'class' => 'element infotext',
	                'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    ));
	     
	    $subform->addElement('select', 'housekeeping', array(
	
	        'value'    => ! empty($values['housekeeping']) ? $values['housekeeping'] : null,
	
	        'multiOptions' => $this->getColumnMapping('housekeeping'),
	
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
	     
	     
	    $subform->addElement('textarea', 'housekeeping_freetext', array(
	    
	        'value'    => ! empty($values['housekeeping_freetext']) ? $values['housekeeping_freetext'] : null,
	    
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	public function create_form_social_integration_everyday_life ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_general");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Social integration / design of everyday life'));
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
	
	
	    $subform->addElement('note', 'infotext', array(
	        'label'        => null,
	        'value'        => $this->translate('(Tagesstrukturierende Maßnahmen, Aktivierung, Einbindung in das soziale Umfeld, Gestaltung des Tagesablaufes, sich beschäftigen, zukunftsgerichtet Planungen)'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'class' => 'element infotext',
	                'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    ));
	
	    $subform->addElement('select', 'social_integration', array(
	
	        'value'    => ! empty($values['social_integration']) ? $values['social_integration'] : null,
	
	        'multiOptions' => $this->getColumnMapping('social_integration'),
	
	        'label'      => $this->translate("Social integration"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>1)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),      	      
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	
	    ));
	    $subform->addElement('select', 'everyday_life', array(
	
	        'value'    => ! empty($values['everyday_life']) ? $values['everyday_life'] : null,
	
	        'multiOptions' => $this->getColumnMapping('everyday_life'),
	
	         'label'      => $this->translate("Eeveryday life"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>1)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),      	      
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	
	    ));
	
	
	    $subform->addElement('textarea', 'social_integration_freetext', array(
	         
	        'value'    => ! empty($values['social_integration_freetext']) ? $values['social_integration_freetext'] : null,
	         
	        'placeholder'=> $this->translate('comments'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>4)),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        'rows'     => 3,
	    ));
	
	
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	

	public function save_form_patient_feedback_general ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	     
	     
	    $entity = PatientFeedbackGeneralTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
	    return $entity;
	}

	


	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        
	        'mobility' => [
	            ''  => '---', //extra empty value for select
	            //'yes' => "Ja",
	            //'no' => "Nein",
	        ],
	        'fall_hazards' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        'difficulty_drinking' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        'difficulty_eating' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        
	        
	        
	        'cognitive_communicative' => [
	            ''  => '---', //extra empty value for select
	        ],
	        'behaviors_mental' => [
	            ''  => '---', //extra empty value for select
	        ],
	        'behaviors_mental_yes' => [
	            ''  => '---', //extra empty value for select
	        ],
	        'coping_everyday' => [
	            ''  => '---', //extra empty value for select
	        ],
	        
	        'nutrition' => [
	            ''  => '---', //extra empty value for select
	        ],
	        
	        'housekeeping' => [
	            ''  => '---', //extra empty value for select
	        ],
	        
	        'social_integration' => [
	            ''  => '---', //extra empty value for select
	        ],
	        
	        'everyday_life' => [
	            ''  => '---', //extra empty value for select
	        ],
	        
	        
	        
	        
	        
	        
	        
	        
	        
	        
	    ];
	
	
	    $values = PatientFeedbackGeneralTable::getInstance()->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}