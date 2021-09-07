<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 10, 2018
 *
 */
class Application_Form_PatientDisabilityDegree extends Pms_Form
{
    
    protected $_model = 'PatientDisabilityDegree';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientDisabilityDegree::TRIGGER_FORMID;
    private $triggerformname = PatientDisabilityDegree::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientDisabilityDegree::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array(
        "MamboAssessment" => [
            'create_form_disability_degree' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );
    
    
	public function create_form_disability_degree ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_disability_degree");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
	    $subform->setLegend($this->translate('Degree of disability'));
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}"); //has_feedback_options ??
	
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2)),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
	        ),
	    ));
	
	   
	    
	    
	    $subform->addElement('multiCheckbox', 'degree_disability', array(
	        
	        'value'        => ! empty($values['degree_disability']) ? $values['degree_disability'] : null,
	        'multiOptions' => $this->getColumnMapping('degree_disability'),
	        'label'        => $this->translate('Degree of disability'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tagClass'=>'print_column_first'
	                
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	    ));
	    
	    $subform->addElement('text', 'expiration', array(
	        'value'        => ! empty($values['expiration']) ? $values['expiration'] : null,
	        
	        'label'        => $this->translate('Expiration'),
	        "readonly"     => true,          //ISPC-2640 Lore 13.07.2020
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tagClass'=>'print_column_first'
	                
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        'class' => 'date',
	        'rows' => 3,
	    ));
	
	
	    
	    $subform->addElement('checkbox', 'permanent', array(
	        'checkedValue'     => 'yes',
	        'uncheckedValue'   => 'no',	        
	        'value'            => ! empty($values['permanent']) ? $values['permanent'] : null,
	        'label'            => $this->translate('permanent'),
	        'decorators'       => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first'
	        
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'initiate_application', array(
	        'checkedValue'     => 'yes',
	        'uncheckedValue'   => 'no',
	        'value'            => ! empty($values['initiate_application']) ? $values['initiate_application'] : null,
	        'label'            => $this->translate('Initiate initial application'),
	        'decorators'       => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first'
	    
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'trigger_amendment', array(
	        'checkedValue'     => 'yes',
	        'uncheckedValue'   => 'no',
	        'value'            => ! empty($values['trigger_amendment']) ? $values['trigger_amendment'] : null,
	        'label'            => $this->translate('Trigger an amendment'),
	        'decorators'       => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first'
	    
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	    ));
	    
	    
	    $subform->addElement('checkbox', 'benefits_workinglife', array(
	        'checkedValue'     => 'yes',
	        'uncheckedValue'   => 'no',
	        'value'            => ! empty($values['benefits_workinglife']) ? $values['benefits_workinglife'] : null,
	        'label'            => $this->translate('Services for participation in working life'),
	        'decorators'       => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first'
	    
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'benefits_community', array(
	        'checkedValue'     => 'yes',
	        'uncheckedValue'   => 'no',
	        'value'            => ! empty($values['benefits_community']) ? $values['benefits_community'] : null,
	        'label'            => $this->translate('Benefits for participation in life in the community'),
	        'decorators'       => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first'
	    
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	    ));
	    
	    
	    $subform->addElement('textarea', 'freetext', array(
	    
	        'value'    => ! empty($values['freetext']) ? $values['freetext'] : null,
	    
	        'label' => 'Degree of disability',
	        'required'   => false,
	        'filters'    => array('StringTrim'),
	        'validators' => array('NotEmpty'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'element', 'colspan'=>1)),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first'
	 
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	            
	        ),
	        'rows'     => 3,
	        'cols'     => 60,
	    ));
	    
	    
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	
	
	
	
	
	
	
	
	public function save_form_patient_disability_degree ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    
	    $entity = PatientDisabilityDegreeTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
	    return $entity;
	}
	


	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        
	        
	    ];
	
	
	    $values = PatientDisabilityDegreeTable::getInstance()->getEnumValues($fieldName);
	
	    
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}