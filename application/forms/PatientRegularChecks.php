<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 10, 2018
 *
 */
class Application_Form_PatientRegularChecks extends Pms_Form
{

    protected $_model = 'PatientRegularChecks';
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientRegularChecks::TRIGGER_FORMID;
    private $triggerformname = PatientRegularChecks::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientRegularChecks::LANGUAGE_ARRAY;
    
    
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
            'create_form_family_treatment' => [
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
            'create_form_hospitalizations' => [
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
            'create_form_lastyear_checkup' => [
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
            'create_form_specialist_care' => [
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
    
    
	//Stehen Sie in regelmäßiger hausärztlicher Behandlung?
	//Are you undergoing regular family medical treatment?
	public function create_form_family_treatment ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	     
	    $this->mapSaveFunction($__fnName , "save_form_patient_regular_checks");
	    
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Are you undergoing regular family medical treatment?'));
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
	    
	    
	    
	    $family_treatment_radio_yes = $subform->createElement('radio', 'family_treatment', array(
	         
	        'value'    => ! empty($values['family_treatment']) ? $values['family_treatment'] : null,
	         
	        'multiOptions' => ['yes' => 'Ja'],
	         
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
// 	                "class"                    => "has_feedback_options",
// 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.family_treatment_yes').hide();\$(this).parents('table').find('.family_treatment_no').show();} else {\$(this).parents('table').find('.family_treatment_no').hide();\$(this).parents('table').find('.family_treatment_yes').show();}",
	        	        	         
	    ));
	    
	    $subform->addElement($family_treatment_radio_yes, 'family_treatment_radio_yes');
	    
	    $display = $values['family_treatment'] == 'yes' ? '' : "display:none";
	    
	    $subform->addElement('text', 'family_treatment_yes', array(
	    
	        'value'    => ! empty($values['family_treatment_yes']) ? $values['family_treatment_yes'] : null,
	         
	        'placeholder'      => $this->translate("Häufigkeit der Hausarztkontakte pro Monat oder Jahr"),
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data family_treatment_yes',
	                'colspan'  => 3,
	                "style"    => $display,	 
// 	                "closeOnly" => true,               
	            )),
// 	            array('Label', array(
// 	                'tag' => 'td',
// 	                "openOnly" => true,               
// 	                'tagClass'=>'print_column_first',
// // 	                'placement'=> 'IMPLICIT_APPEND'
	                
// 	            )),
	             
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
        ));
	    
	    
	    
	    $family_treatment_radio_no = $subform->createElement('radio', 'family_treatment', array(
	    
	        'value'    => ! empty($values['family_treatment']) ? $values['family_treatment'] : null,
	    
	        'multiOptions' => ['no' => 'Nein'],
	    
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
	                // 	                "class"                    => "has_feedback_options",
	            // 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.family_treatment_yes').hide();\$(this).parents('table').find('.family_treatment_no').show();} else {\$(this).parents('table').find('.family_treatment_no').hide();\$(this).parents('table').find('.family_treatment_yes').show();}",
	    
	    ));
	     
	    $subform->addElement($family_treatment_radio_no, 'family_treatment_radio_no');
	     
	    
	    $display = $values['family_treatment'] == 'no' ? '' : "display:none";
	    
	    $subform->addElement('multiCheckbox', 'family_treatment_no', array(
	         
	        'value'        => ! empty($values['family_treatment_no']) ? $values['family_treatment_no'] : null,
	         
	        'multiOptions' => $this->getColumnMapping('family_treatment_no'),
	        
	        'label'        => null,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data family_treatment_no',
	                'colspan'  =>3,
	                "style"    => $display,
	                'openOnly' => true,
	                
	            )),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
// 	            array(array('row' => 'HtmlTag'), array(
// 	                'tag'      => 'tr', 
// 	                'closeOnly' => true,
// 	            )),
	        ),
	        'onChange' => "if (this.value=='miscellaneous' && this.checked) {\$(this).parents('table').find('.family_treatment_freetext').show();} else if (this.value=='miscellaneous' && ! this.checked) {\$(this).parents('table').find('.family_treatment_freetext').hide();}",
	         
	         
	    ));
	    
	    $display = in_array('miscellaneous', $values['family_treatment_no']) ? '' : "display:none";
	    
	    $subform->addElement('text', 'family_treatment_freetext', array(
	         
	        'value'    => ! empty($values['family_treatment_freetext']) ? $values['family_treatment_freetext'] : null,
	    
// 	        'placeholder'  => '',
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data family_treatment_yes',
	                'closeOnly' => true,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
	        
	        'class' => 'family_treatment_freetext',
	        "style"    => $display,
	        
	    ));
	    
	     
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);	 
	}
	

	
	
	
	//Stehen Sie in regelmäßiger fachärztlicher Behandlung?
	//Be in regular specialist care?
	public function create_form_specialist_care($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_regular_checks");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Be in regular specialist care?'));
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
	     
	     
	     
	    $family_treatment_radio_yes = $subform->createElement('radio', 'specialist_care', array(
	
	        'value'    => ! empty($values['specialist_care']) ? $values['specialist_care'] : null,
	
	        'multiOptions' => ['yes' => 'Ja'],
	
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
	                // 	                "class"                    => "has_feedback_options",
	            // 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.specialist_care_yes').hide();\$(this).parents('table').find('.specialist_care_no').show();} else {\$(this).parents('table').find('.specialist_care_no').hide();\$(this).parents('table').find('.specialist_care_yes').show();}",
	
	    ));
	     
	    $subform->addElement($family_treatment_radio_yes, 'specialist_care_radio_yes');
	     
	    $display = $values['family_treatment'] == 'yes' ? '' : "display:none";
	     
	    

	    $subform->addElement('text', 'specialist_care_yes_text', array(
	         
	        'value'    => ! empty($values['specialist_care_yes_text']) ? $values['specialist_care_yes_text'] : null,
	        'placeholder'      => $this->translate("Häufigkeit der Facharztkontakte pro Monat oder Jahr"),
	         
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data specialist_care_yes',
	                'colspan'  =>3,
	                "style"    => $display,
	                'openOnly' => true,
	            )),
	        ),	 
	        'class' => "input_specialist_care_yes_text",   
	    ));
	    
	    $subform->addElement('multiCheckbox', 'specialist_care_yes', array(
	    
	        'value'        => ! empty($values['specialist_care_yes']) ? $values['specialist_care_yes'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('specialist_care_yes'),
	    
	        'label'        => null,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	            	                'tag'      => 'div',
	            )),
// 	            array(array('data' => 'HtmlTag'), array(
// 	                'tag'      => 'td',
// 	                'class'    => 'element print_column_data specialist_care_yes',
// 	                'colspan'  =>3,
// 	                "style"    => $display,
// 	                'openOnly' => true,
	    
// 	            )),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            // 	            array(array('row' => 'HtmlTag'), array(
	            // 	                'tag'      => 'tr',
	                // 	                'closeOnly' => true,
	                // 	            )),
	            ),
	        'onChange' => "if (this.value=='miscellaneous' && this.checked) {\$(this).parents('table').find('.specialist_care_freetext').show();} else if (this.value=='miscellaneous' && ! this.checked) {\$(this).parents('table').find('.specialist_care_freetext').hide();}",
	    
	    
	    ));
	      
	    $display = in_array('miscellaneous', $values['specialist_care_yes']) ? '' : "display:none";
	     
	    $subform->addElement('text', 'specialist_care_freetext', array(
	    
	        'value'    => ! empty($values['specialist_care_freetext']) ? $values['specialist_care_freetext'] : null,
	         
	        'required'     => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
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
	         
	        'class' => 'specialist_care_freetext',
	        "style"    => $display,
	         
	    ));
	     
	    
	    
	    
	    
	    
	    
	    
	    
	    
	    $family_treatment_radio_no = $subform->createElement('radio', 'specialist_care', array(
	         
	        'value'    => ! empty($values['specialist_care']) ? $values['specialist_care'] : null,
	         
	        'multiOptions' => ['no' => 'Nein'],
	         
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
	                // 	                "class"                    => "has_feedback_options",
	                // 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.specialist_care_yes').hide();\$(this).parents('table').find('.specialist_care_no').show();} else {\$(this).parents('table').find('.specialist_care_no').hide();\$(this).parents('table').find('.specialist_care_yes').show();}",	         
	    ));
	
	    $subform->addElement($family_treatment_radio_no, 'specialist_care_radio_no');
	
	     
	    $display = $values['specialist_care'] == 'no' ? '' : "display:none";
	     
	    $subform->addElement('select', 'specialist_care_no', array(
	
	        'value'        => ! empty($values['specialist_care_no']) ? $values['specialist_care_no'] : null,
	
	        'multiOptions' => $this->getColumnMapping('specialist_care_no'),
	         
	        'label'        => null,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data specialist_care_no',
	                'colspan'  =>3,
	                "style"    => $display,
	                'openOnly' => true,
	                 
	            )),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            // 	            array(array('row' => 'HtmlTag'), array(
	                // 	                'tag'      => 'tr',
	                // 	                'closeOnly' => true,
	                // 	            )),
	            ),
	
	
	    ));
	    
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	
	
	
	
	
	//Waren Sie im letzten Jahr bei einer Vorsorgeuntersuchung?
	//Were you in a check-up last year?
	public function create_form_lastyear_checkup ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	     
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_regular_checks");
	     
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Were you in a check-up last year?'));
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
	     
	     
	     
	    $lastyear_checkup_radio_yes = $subform->createElement('radio', 'lastyear_checkup', array(
	
	        'value'    => ! empty($values['lastyear_checkup']) ? $values['lastyear_checkup'] : null,
	
	        'multiOptions' => ['yes' => 'Ja'],
	
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
	                	        
	                // 	                "class"                    => "has_feedback_options",
	            // 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.lastyear_checkup_yes').hide();} else {\$(this).parents('table').find('.lastyear_checkup_yes').show();}",
	
	    ));	     
	    $subform->addElement($lastyear_checkup_radio_yes, 'lastyear_checkup_radio_yes');

	    $display = $values['lastyear_checkup'] == 'yes' ? '' : "display:none";
	    
	    $subform->addElement('multiCheckbox', 'lastyear_checkup_yes', array(
	    
	        'value'        => ! empty($values['lastyear_checkup_yes']) ? $values['lastyear_checkup_yes'] : null,
	    
	        'multiOptions' => $this->getColumnMapping('lastyear_checkup_yes'),
	    
	        'label'        => null,
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'class'    => 'element print_column_data lastyear_checkup_yes',
	                'colspan'  =>3,
	                "style"    => $display,
// 	                'openOnly' => true,
	    
	            )),
	            // 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )),
            ),
	    
	    
	    ));
	    
	    
	    
	    
	     
	    $lastyear_checkup_radio_no = $subform->createElement('radio', 'lastyear_checkup', array(
	         
	        'value'    => ! empty($values['lastyear_checkup']) ? $values['lastyear_checkup'] : null,
	         
	        'multiOptions' => ['no' => 'Nein'],
	         
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
	                // 	                "class"                    => "has_feedback_options",
	                // 	                "data-feedback_options"    => $__feedback_options,
	            )),
	        ),
	        'onChange' => "if (this.value=='no') {\$(this).parents('table').find('.lastyear_checkup_yes').hide();} else {\$(this).parents('table').find('.lastyear_checkup_yes').show();}",
	        	         
	    ));
	
	    $subform->addElement($lastyear_checkup_radio_no, 'lastyear_checkup_radio_no');
	
	  
	     
	
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	//Waren Sie im letzten Jahr bei einer Vorsorgeuntersuchung?
	//Hospitalizations?
	public function create_form_hospitalizations($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $columns = array(
	        'geplant',
	        'Grund',
	        'Länge in Tagen',
	        'Entfernen',
	    );	     
	     
	    $subform = $this->subFormTable(array(
	        'columns' => $columns,
	        // 'class' => 'datatable',
	    ));
	    $subform->setLegend($this->translate('Hospitalizations?'));
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}");
	    
	    $elementsBelongTo = $elementsBelongTo . "[hospitalizations]";
	
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	     
	    //return $subform;
	    $row_cnt = 0;
	    $subFormsRows = [];
	     
	    foreach ($values['hospitalizations'] as $row) {
	         
	        $subFormsRows[] = $this->create_form_hospitalizations_row($row);
	         
	        //$subform->addSubForm($row_elemnts, $row_cnt);
	
	        //$row_cnt++;
	    }
	     
	    $subform->addSubForms($subFormsRows);
	    
	    
	    //add button to add new contacts
	    $subform->addElement('button', 'addnew_hospitalizations', array(
	        'onClick'      => 'PatientRegularChecks_hospitalizations_addnew(this, \'PatientRegularChecks\'); return false;',
	        'value'        => '1',
	        'label'        => $this->translate('Add new hospitalizations line'),
	        'decorators'   => array(
	            'ViewHelper',
	            'FormElements',
	            // 	            array('HtmlTag', array('tag' => 'tr')),
	
	            array(array('data'=>'HtmlTag'),array('tag'=>'td', 'colspan' => count($columns))),
	            array(array('row'=>'HtmlTag'),array('tag'=>'tr'))
	
	
	        ),
	        'class'        =>'button btnSubmit2018 plus_icon_bg dontPrint',
	    ));
	     
	     
	     
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	public function create_form_hospitalizations_row ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()->setDecorators(array('FormElements'));
	
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
// 	    $this->__setElementsBelongTo($subform, 'hospitalizations');
	
// 	    dd($subform->getElementsBelongTo());
	
	    $hidden_deleted_row = '';
	    if (isset($values['id_deleted']) && ! empty($values['id']) && $values['id_deleted'] == $values['id']) {
	        $hidden_deleted_row = 'display:hidden';
	    }
	
	
	    $subform->addElement('select', 'visit_planned', array(
	        'label'      => null,
	        'value'    => $values['visit_planned'],
	        'multiOptions' => $this->getColumnMapping('visit_planned'),
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element visit_planned')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true , 'class' => "icd_holder_row")),
	        ),
	        'class' => '',
	        'style' => $hidden_deleted_row,
	    ));
	     
	     
	    
	    $subform->addElement('text', 'description', array(
	        'label'        => null,
	        'value'        => $values['description'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element description')),
	        ),
	        'class' => '',
        ));
	         
	    $subform->addElement('text', 'days', array(
	        'label'        => null,
	        'value'        => $values['days'],
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element days')),
	        ),
        ));
	         
	         
        $subform->addElement('note', 'delete_row', array(
            'value'  => '<a onclick="$(this).parents(\'tr\').remove();" class="delete_row" title="'.$this->translate('delete row').'" href="javascript:void(0)"></a>',
            'escape' => false,
            'alt' => 'delete row',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_center',
                )),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
            ),
        ));
	         
	         
        return $this->filter_by_block_name($subform, $__fnName);
	         
	}
	
	
	
	
	
	

	
	public function save_form_patient_regular_checks ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    $data['hospitalizations'] = array_values($data['hospitalizations']); // reset keys, cause they come like "new_" . unique()
	    
	    
	    $entity = PatientRegularChecksTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
	    return $entity;
	}  
	
	     

	


	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        'family_treatment' => [//added like this cause translations
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	        'specialist_care_no' => [
	            ''  => '---' //extra empty value for select
	        ],
	    
	        'visit_planned' => [
	            'yes' => "geplant",
	            'no' => "nicht geplant",
	        ],
	        
	        'nationwide_medicationplan' => [
	            ''  => '---', //extra empty value for select
	            'yes' => "Ja",
	            'no' => "Nein",
	        ],
	        
	    ];
	
	
	    $values = PatientRegularChecksTable::getInstance()->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}