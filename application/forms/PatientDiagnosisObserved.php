<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 5, 2018
 *
 */
class Application_Form_PatientDiagnosisObserved extends Pms_Form
{

    protected $_model = 'PatientDiagnosisObserved';
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientDiagnosisObserved::TRIGGER_FORMID;
    private $triggerformname = PatientDiagnosisObserved::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientDiagnosisObserved::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array(
        "MamboAssessment" => [
            'create_form_diseases_diagnosed' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );
    
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_can_sleep' => [
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
            'create_form_current_state' => [
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
            'create_form_diseases_diagnosed' => [
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
            'create_form_general_health' => [
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
            'create_form_pain_recurrence' => [
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
            'create_form_participation_DMP' => [
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
            'create_form_physically_active' => [
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
//                 "inclusion_measures",
            ],
            'create_form_therapeutic_goals' => [
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
	 * Welche Erkrankungen hat der Haus- oder Facharzt bei Ihnen festgestellt? / Welche Erkrankungen sind Ihnen bekannt?
	 * Which diseases has the general practitioner diagnosed with you? / What diseases are known to you?
     * 
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_diseases_diagnosed($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis_observed");
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Which diseases has the general practitioner diagnosed with you? / What diseases are known to you?'));
	    $subform->setAttrib("class", "label_same_size " . $__fnName);
	     
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

	    
	    $subform->addElement('text', 'diseases_diagnosed', array(
	        'label'      => false,
	        'required'   => false,
	        'value'    => ! empty($values['diseases_diagnosed']) ? $values['diseases_diagnosed'] : null,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr', 
	                //"class"                    => "has_feedback_options",
	                //"data-feedback_options"    => $__feedback_options,  
	            )),
	        ),
	        
	        'class'                    => 'autocomplete',
	        'data-autocomplete_source' => Zend_Json::encode($this->getColumnMapping('diseases_diagnosed')),
	        
	    ));
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	     
	}
	
	
	// 	    Teilnahme am DMP
	// 	    Participation in the DMP
	public function create_form_participation_DMP($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis_observed");
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Participation in the DMP'));
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
	    
	    
	    
	    $subform->addElement('multiCheckbox', 'participation_DMP', array(
	        'label'      => false,
	        'multiOptions' => $this->getColumnMapping('participation_DMP'),
	        'required'   => false,
	        'value'    => ! empty($values['participation_DMP']) ? $values['participation_DMP'] : null,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
// 	                "class"                    => "has_feedback_options",
// 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	    ));
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	
	}
	
	// 	    Wie würden Sie Ihren Gesundheitszustand im Allgemeinen beschreiben?
	// 	    How would you describe your health in general?
	public function create_form_general_health($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis_observed");
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('How would you describe your health in general?'));
	    $subform->setAttrib("class", "label_same_size_auto " . $__fnName);
	    
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
	    
	    $subform->addElement('select', 'general_health', array(
	        'label'      => null,
	        'multiOptions' => $this->getColumnMapping("general_health"),
	        'required'   => false,
	        'value'    => ! empty($values['general_health']) ? $values['general_health'] : null,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
// 	                "class"                    => "has_feedback_options",
// 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	    ));
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	
	}
	
	
	// 	    Im Vergleich zum vergangenen Jahr, wie würden Sie Ihren derzeitigen Gesundheitszustand beschreiben?
	// 	    Compared to last year, how would you describe your current state of health?
	// 	    Was ist der Grund dafür, dass es Ihnen nicht gut geht/schlechter geht?
	// 	    What is the reason that you are not feeling well?
	public function create_form_current_state($values =  array() , $elementsBelongTo = null)
	{     
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis_observed");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Compared to last year, how would you describe your current state of health?'));
	    $subform->setAttrib("class", "label_same_size_auto " . $__fnName);
	    
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
	     
	    $subform->addElement('select', 'current_state', array(
	        'label'      => null,
	        'multiOptions' => $this->getColumnMapping("current_state"),
	        'required'   => false,
	        'value'    => ! empty($values['current_state']) ? $values['current_state'] : null,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
// 	                "class"                    => "has_feedback_options",
// 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        'onChange' => "if (this.value=='much worse than a year ago' || this.value == 'a bit worse than a year ago') {\$(this).parents('table').find('.current_state_onoff').show();} else {\$(this).parents('table').find('.current_state_onoff').hide();};  feedbackQtipReposition($(this).parents('.mamboTabsPageBody'));",
	        
	    ));
	     
	    
	    $display = ! empty($values['current_state'])  && $values['current_state'] == 'much worse than a year ago' || $values['current_state'] == 'a bit worse than a year ago'? : "display:none";

	    $subform->addElement('note', 'just_a_headline_1', array(
	        'label'        => null,
	        'value'        => $this->translate('What is the reason that you are not feeling well?'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data h2', 'colspan'=>3)),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                "class" => "current_state_onoff",
	                "style" => $display,
	            )),
	        ),
	    ));

	    //TODO THIS EXTRA
	    $__parent = "PatientDiagnosisObserved";
	    $__parentID = $values['id'] ?: '';
	    $__fnName_child = $__fnName . ".child_1";
	    
	    $__feedback_options = [
	        "__block_name" => $this->_block_name,
	        "__parent"     => $__parent,
	        "__fnName"     => $__fnName_child,
	        "__parentID"   => $__parentID,
	        "__meta"       => [
    	        "todo"                     => $this->_block_feedback_values[$__parent][$this->_block_name][$__fnName_child]['todo'],
    	        "feedback"                 => $this->_block_feedback_values[$__parent][$this->_block_name][$__fnName_child]['feedback'],
//     	        "benefit_plan"             => $this->_block_feedback_values[$__parent][$this->_block_name][$__fnName_child]['benefit_plan'],
//     	        "heart_monitoring"         => $this->_block_feedback_values[$__parent][$this->_block_name][$__fnName_child]['heart_monitoring'],
//     	        "referral_to"              => $this->_block_feedback_values[$__parent][$this->_block_name][$__fnName_child]['referral_to'],
    	        // 	        "further_assessment"       => $values['__feedback_options'][$__feedback_block_name]['further_assessment'],
    	        // 	        "training_nutrition"       => $values['__feedback_options'][$__feedback_block_name]['training_nutrition'],
    	        // 	        "training_adherence"       => $values['__feedback_options'][$__feedback_block_name]['training_adherence'],
    	        // 	        "training_device"          => $values['__feedback_options'][$__feedback_block_name]['training_device'],
    	        // 	        "training_prevention"      => $values['__feedback_options'][$__feedback_block_name]['training_prevention'],
    	        // 	        "training_incontinence"    => $values['__feedback_options'][$__feedback_block_name]['training_incontinence'],
    	        // 	        "organization_careaids"    => $values['__feedback_options'][$__feedback_block_name]['organization_careaids'],
//     	        "inclusion_COPD"           => $this->_block_feedback_values[$__parent][$this->_block_name][$__fnName_child]['inclusion_COPD'],
    	        // 	        "inclusion_measures"       => $values['__feedback_options'][$__feedback_block_name]['inclusion_measures'],
	        ]
	    ];
	    if (! empty($this->_block_feedback_values [$__parent][$this->_block_name][$__fnName_child])) {
	        $__feedback_options['__meta_val']['todo'] = $this->_block_feedback_values [$__parent][$this->_block_name][$__fnName_child]['todo_val'];
	        $__feedback_options['__meta_val']['feedback'] = $this->_block_feedback_values [$__parent][$this->_block_name][$__fnName_child]['feedback_val'];
	    }
	    
	    $__feedback_options = Zend_Json::encode($__feedback_options);
	     
	    
	    $subform->addElement('multiCheckbox', 'current_state_reason', array(
	        'label'      => $this->translate("current_state_reason"),
	        'multiOptions' => $this->getColumnMapping('current_state_reason'),
	        'required'   => false,
	        'value'    => ! empty($values['current_state_reason']) ? $values['current_state_reason'] : null,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'                      => 'tr', 
	                "class"                    => "has_feedback_options current_state_onoff",
	                "style" => $display,
	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        
	        
	    ));
	    
	    
	    $subform->addElement('text', 'current_state_freetext', array(
	        'label'      => 'sonstiges',
	        'required'   => false,
	        'value'    => ! empty($values['current_state_freetext']) ? $values['current_state_freetext'] : null,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
	            	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr', 
	                "class" => "current_state_onoff",
	                "style" => $display,
	            )),
	        ),
	    ));
	     
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	
	}
	

	// 	    Was sind ihre eigenen Therapieziele/was ist Ihnen bei Ihren Krankheiten besonders wichtig?
	// 	    What are your own therapeutic goals / what is especially important for your illnesses?
	public function create_form_therapeutic_goals($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis_observed");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('What are your own therapeutic goals / what is especially important for your illnesses?'));
	    $subform->setAttrib("class", "label_same_size_auto has_feedback_options {$__fnName}");
	    
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
	    
	    $subform->addElement('multiCheckbox', 'therapeutic_goals', array(
	        'label'      => $this->translate("therapeutic_goals"),
	        'multiOptions' => $this->getColumnMapping('therapeutic_goals'),
	        'required'   => false,
	        'value'    => ! empty($values['therapeutic_goals']) ? $values['therapeutic_goals'] : null,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4)),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
// 	                "class"                    => "has_feedback_options",
// 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	    ));
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	    
	}
	
	// 	    Wie häufig sind Sie körperlich aktiv?
	// 	    How often are you physically active?
	public function create_form_physically_active($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis_observed");
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('How often are you physically active?'));
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
	    
	        
        $subform->addElement('radio', 'physically_active', array(
            
            'value'    => ! empty($values['physically_active']) ? $values['physically_active'] : null,
            
            'multiOptions' =>$this->getColumnMapping('physically_active'),
        
            'label'      => null,
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>4,)),
	            //array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    //'openOnly' => true,
// 	                "class"                    => "has_feedback_options",
// 	                "data-feedback_options"    => $__feedback_options,
                )),
            ),
	        'onChange' => "\$(this).parents('table').find('input:text.freetext').val('').show();",
            
        ));
	        
        $display = is_null($values['physically_active']) ? 'display:none' : '';
        
        $subform->addElement('text', 'physically_active_freetext', array(
             
            'belongsTo' => "physically_active",
             
            'value'    => ! empty($values['physically_active_freetext']) ? $values['physically_active_freetext'] : null,
             
            'label'      => null,
            'placeholder' => $this->translate('freetext'),
            'required'     => false,
            'filters'    => array('StringTrim'),
            'validators' => array('NotEmpty'),
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data freetext', 'colspan'=>4,)),
                // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    //'closeOnly' => true,
                )),
            ),
            'style' => $display,
            'class' => 'freetext',
        ));
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);	     
	}
	
	
	
	// 	    Können Sie gut schlafen?
	// 	    Can you sleep well?
	public function create_form_can_sleep($values =  array() , $elementsBelongTo = null)
	{   

	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis_observed");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Can you sleep well?'));
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
	   
	    
	    $subform->addElement('radio', 'can_sleep', array(
	         
	        'value'    => ! empty($values['can_sleep']) ? $values['can_sleep'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('can_sleep'),
	         
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
	                'tag' => 'tr',
// 	                "class"                    => "has_feedback_options",
// 	                "data-feedback_options"    => $__feedback_options,
	                
	            )),
	        ),
	        'onChange' => "if (this.value=='yes') {\$(this).parents('table').find('.can_sleep_reason').hide();} else {\$(this).parents('table').find('.can_sleep_reason').show();}",
	         
	    ));
	    
	    $display = $values['can_sleep'] =='no' ? '' : "display:none";
	    
	    $subform->addElement('multiCheckbox', 'can_sleep_reason', array(
	         
	        'value'    => ! empty($values['can_sleep_reason']) ? $values['can_sleep_reason'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('can_sleep_reason'),
	         
	        'label'      => null,
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data can_sleep_reason', 'colspan'=>4, 'style'=>$display)),
	            // 	                	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	         
	    ));
	    
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);	 
	}
	
	// 	    Haben Sie häufig Schmerzen?
	// 	    Do you often have pain?

	
	
	public function create_form_pain_recurrence($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_diagnosis_observed");
	    
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Do you often have pain?'));
	    $subform->setAttrib("class", "label_same_size_auto multipleCheckboxes {$__fnName}");
	    
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
	    
	    
	    
	    $subform->addElement('radio', 'pain_recurrence', array(
	         
	        'value'    => ! empty($values['pain_recurrence']) ? $values['pain_recurrence'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('pain_recurrence'),
	         
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
	                'tag' => 'tr',
// 	                "class"                    => "has_feedback_options",
// 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.pain_recurrence_on').hide();} else {\$(this).parents('table').find('.pain_recurrence_on').show();}",
	         
	    ));
	    
	    $display = $values['pain_recurrence'] == 'yes' ? '' : "display:none";
	    
	    // 	    Häufigkeit
	    // 	    frequency
	    
// 	    dd($values);
	    $subform->addElement('radio', 'pain_frequency', array(
	         
	        'value'    => ! empty($values['pain_frequency']) ? $values['pain_frequency'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('pain_frequency'),
	        
	        'label'      => $this->translate("Frequency"),
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
	                "class"    => "pain_recurrence_on", 
	                "style"    => $display,
	            )),
	        ),
	         
	    ));
	    
	    
	    
	    //Wo treten die Schmerzen auf?
	    //Where does the pain occur?
	    $subform->addElement('multiCheckbox', 'pain_position', array(
	    
	        'value'    => ! empty($values['pain_position']) ? $values['pain_position'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('pain_position'),
	    
	        'label'      => $this->translate("Where does the pain occur?"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr', 
	                "class" => "pain_recurrence_on",
	                "style"    => $display,
	            )),
	        ),
	    
	    ));
	    
	    

	    //NRS
	    $subform->addElement('radio', 'pain_NRS', array(
	         
	        'value'    => ! empty($values['pain_NRS']) ? $values['pain_NRS'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('pain_NRS'),
	        'separator'  => " ", //'&nbsp;',
	            
	        'label'      => $this->translate("NRS"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                "class" => "pain_recurrence_on",
	                "style"    => $display,
	            )),
	        ),
	         
	        //'labelStyle' => 'float:left',
	        
	    ));
	     
	    
	    //Bestehende Maßnahmen
	    //Existing measures
	    $subform->addElement('textarea', 'pain_measures', array(
	         
	        'value'    => ! empty($values['pain_measures']) ? $values['pain_measures'] : null,
	         
	        'label'      => $this->translate("Existing measures"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element print_column_data', 'colspan'=>3)),
	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                "class" => "pain_recurrence_on",
	                "style"    => $display,
	            )),
	        ),
	        'rows' => 3,
	         
	    ));
	    
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);	 
	}
	

	
	
	
	
	
	
	
	
	
	public function save_form_diagnosis_observed ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    
	    $entity = PatientDiagnosisObservedTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
	    return $entity;
	}
	     

	


	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        
	        'diseases_diagnosed' => [ //this are used for autocomlete
	            'Diabetes',
	            'Herzerkrankung',
	            'hoher Blutdruck',
	            'erhöhte Fettwerte',
	        ],
	        
	        'general_health'=> [
	            ''  => '---'
	        ],
	        
	        'current_state'=> [
	            ''  => '---'
	        ],
	        
	        'can_sleep' => [//added like this cause translations
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        'pain_recurrence' => [//added like this cause translations
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        'pain_NRS' => [
	            0,
	            1,
	            2,
	            3,
	            4,
	            5,
	            6,
	            7,
	            8,
	            9,
	            10,
	        ]
	    
	    ];
	
	
	    $values = PatientDiagnosisObservedTable::getInstance()->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}