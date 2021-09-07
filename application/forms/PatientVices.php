<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 10, 2018
 *
 */
class Application_Form_PatientVices extends Pms_Form
{
    protected $_model = 'PatientVices';
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientVices::TRIGGER_FORMID;
    private $triggerformname = PatientVices::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientVices::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array(
        "MamboAssessment" => [
            'create_form_patient_smoking' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
            'create_form_patient_alcoholing' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );
    
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_patient_alcoholing' => [
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
            'create_form_patient_smoking' => [
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
    
    
    
	//Rauchen Sie?
	//Do you smoke?
	public function create_form_patient_smoking($values =  array() , $elementsBelongTo = null)
	{   

	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_patient_vices");
	    
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Do you smoke?'));
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
	   
	    
	    $subform->addElement('radio', 'smoke', array(
	         
	        'value'    => ! empty($values['smoke']) ? $values['smoke'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('smoke'),
	         
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
	         
	    ));
	    	    
	    return $this->filter_by_block_name($subform, $__fnName);	 
	}
	
	//Trinken Sie regelmäßig Alkohol?
	//Do you drink alcohol regularly?
	public function create_form_patient_alcoholing($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_patient_vices");
	    
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Do you drink alcohol regularly?'));
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
	    
	    
	    
	    $subform->addElement('radio', 'alcohol', array(
	         
	        'value'    => ! empty($values['alcohol']) ? $values['alcohol'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('alcohol'),
	         
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
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.alcohol_frequency_on').hide();} else {\$(this).parents('table').find('.alcohol_frequency_on').show();}",
	         
	    ));
	    
	    $display = $values['alcohol'] == 'yes' ? '' : "display:none";
	    
	    // 	    Häufigkeit
	    // 	    frequency
	    $subform->addElement('select', 'alcohol_frequency', array(
	         
	        'value'    => ! empty($values['alcohol_frequency']) ? $values['alcohol_frequency'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('alcohol_frequency'),
	        
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
	                "class"    => "alcohol_frequency_on", 
	                "style"    => $display,
	            )),
	        ),
	         
	    ));
	    
	     
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);	 
	}
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function save_form_patient_vices ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    
	    $entity = PatientVicesTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
	    return $entity;
	}
	     

	


	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        'smoke' => [//added like this cause translations
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        'alcohol' => [//added like this cause translations
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        'alcohol_frequency' => [
	            ''  => '---' //extra empty value for select
	        ]
	    
	    ];
	
	
	    $values = PatientVicesTable::getInstance()->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}