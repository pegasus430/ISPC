<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 10, 2018
 *
 */
class Application_Form_PatientFeedbackCareAids extends Pms_Form
{

    protected $_model = 'PatientFeedbackCareAids';
    
    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = PatientFeedbackCareAids::TRIGGER_FORMID;
    private $triggerformname = PatientFeedbackCareAids::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = PatientFeedbackCareAids::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array(
        "MamboAssessment" => [
            'create_form_care_aids' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_care_aids' => [
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
// 	           "inclusion_measures",
            ],
        ],
    ];
    
		
	public function create_form_care_aids ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_feedback_care_aids");
	
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->addDecorator('HtmlTag', array('tag' => 'div'));
	    $subform->setLegend($this->translate('Care and Aids'));
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
	
	   
        
	    
	    
	    $cbUpTable = $this->create_form_care_aids_cb1($values, 'checkboxes');
	    $subform->addSubForm($cbUpTable, 'care_aids_cb1');
	    
	    $radioTable = $this->create_form_care_aids_radios($values);
	    $subform->addSubForm($radioTable, 'care_aids_radio');
	    
	    $cbDownTable = $this->create_form_care_aids_cb2($values, 'checkboxes');
	    $subform->addSubForm($cbDownTable, 'care_aids_cb2');
	    
	    
	    
	    
	    
	    
	    
	    $subform->addElement('textarea', 'freetext', array(
	        'value'        => ! empty($values['freetext']) ? $values['freetext'] : null,
	        
	        'placeholder'  => $this->translate("Remarks"),	        
// 	        'label'        => $this->translate('Remarks'),
	        
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Errors'),
// 	            array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'    => 'care_aids_freetext')),
	             
	        ),
	        'class' => 'elastic',
	        'rows' => 3,
	    ));
	
	
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	public function create_form_care_aids_radios($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__;
	    
	    $columns = array(
	        '',
	        'Vorhanden',
	        'ungenutzt',
	        'Benötigt',
	    );
	    $subform = $this->subFormTable(array(
	        'columns' => $columns,
	        'class' => "align_center {$__fnName}",
	        
	    ));	    
	    $subform->removeDecorator('Fieldset');
	
	    //$this->__setElementsBelongTo($subform, $elementsBelongTo);
	     
	    //return $subform;
	    $row_cnt = 0;
	    $rows = [];
	     
	    $__defaultRadios = $this->getColumnMapping('care_aids_radio');

	    foreach ($__defaultRadios as $key => $translation) {
	        
	        $subform->addElement('note', "labelRadio{$row_cnt}", array(
	            'value'  => $translation,
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                    'class' => 'align_left',
	                )),
    	            array(array('row' => 'HtmlTag'), array(
    	                'tag' => 'tr',
    	                'openOnly' => true ,
                    )),
	           ),
	        ));
	        
	        $subform->addElement('radio', $key, array(
	            
	            'value' => $values['care_aids_radio'][$this->filterName($key)],
	            'required'   => false,
	            
	            'multiOptions' => ['available'=> '', 'unused'=> '', 'requires' => ''],
	            
	            'separator' => '</td><td class="element align_center">',
	            
	            'filters'    => array('StringTrim'),
	            'validators' => array('NotEmpty'),
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                    'class' => 'element align_center'
	                )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr',
	                    'closeOnly' => true ,
	                )),
	            ),
	        ));
	        
	        $row_cnt++;
	    }	    
	     
	    return $subform;
	}
	
	
	
    
    public function create_form_care_aids_cb1($values =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__;
         
        $columns = array(
            '1',
            '2',
            '3',
        );
        $subform = $this->subFormTable(array(
            'columns' => $columns,
            'class' => "align_center {$__fnName}",
             
        ));
        $subform->removeDecorator('Fieldset');
    
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
    
        
        
        //row1
        $subform->addElement('note', 'cb1.1', array(
            'value'  => '',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_left',
//                     'rowspan'=>2,
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true ,
                )),
            ),
        ));
        $subform->addElement('note', 'cb1.2', array(
            'value'  => $this->translate('None'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_left',
                )),
            ),
        ));
        $cb1 = $subform->createElement('multiCheckbox', 'care_aids_cb1', array(
            'multiOptions' => ['None' => ''],
            'value'  => $values['care_aids_cb1'],
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_center',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true ,
                )),
            ),
        ));
        $subform->addElement($cb1, 'cb1');
        
        
        //row2
        $subform->addElement('note', 'cb2.1', array(
            'value'  => '',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_left',
                    //                     'rowspan'=>2,
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true ,
                )),
            ),
        ));
        $subform->addElement('note', 'cb2.2', array(
            'value'  => $this->translate('No need'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_left',
                )),
            ),
        ));
        $cb2 = $subform->createElement('multiCheckbox', 'care_aids_cb1', array(
            'multiOptions' => ['No need' => ''],
            'value'  => $values['care_aids_cb1'],
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_center',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true ,
                )),
            ),
        ));
        $subform->addElement($cb2, 'cb2');
        
        
        
        return $subform;
    }

    public function create_form_care_aids_cb2($values =  array() , $elementsBelongTo = null)
    {
        $__fnName = __FUNCTION__;
         
        $columns = array(
            '1',
            '2',
            '3',
        );
        $subform = $this->subFormTable(array(
            'columns' => $columns,
            'class' => "align_center {$__fnName}",
             
        ));
        $subform->removeDecorator('Fieldset');
    
        $this->__setElementsBelongTo($subform, $elementsBelongTo);
        
        //row1
        $subform->addElement('note', 'cb3.1', array(
            'value'  => '',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_left',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true ,
                )),
            ),
        ));
        $cb3_1 = $subform->createElement('multiCheckbox', 'care_aids_cb2', array(
            'multiOptions' => ['arrange pickup' => ''],
            'value'  => $values['care_aids_cb2'],
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_center',
                )),
            ),
        ));
        $subform->addElement($cb3_1, 'cb3.3');
        $cb3_2 = $subform->createElement('multiCheckbox', 'care_aids_cb2', array(
            'value'  => $values['care_aids_cb2'],
            'multiOptions' => ['Prescription by doctor' => ''],
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_center',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true ,
                )),
            ),
        ));
        $subform->addElement($cb3_2, 'cb3.4');
         
         
        

        //row2
        
        $subform->addElement('note', 'cb4.1', array(
            'value'  => '',
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_left',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'openOnly' => true ,
                )),
            ),
        ));
        
        $subform->addElement('note', 'cb4.3', array(
            'value'  => $this->translate('arrange pickup'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_center',
                )),
            ),
        ));
        

        $subform->addElement('note', 'cb4.4', array(
            'value'  => $this->translate('Prescription by doctor'),
            'decorators' => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                    'class' => 'align_center',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag' => 'tr',
                    'closeOnly' => true ,
                )),
            ),
        ));
    
        return $subform;
    }
    
    
    
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function save_form_patient_feedback_care_aids ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    /*
	     * this is my bad...@cla.. the form was not created correectly 
	     */
	    if (isset($data['checkboxes'])) {
	        $data = array_merge($data, $data['checkboxes']);
	    }
	    
	    $entity = PatientFeedbackCareAidsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
// 	    dd($data, $entity->toArray());
	    
	    return $entity;
	}


	public function getColumnMapping($fieldName, $revers = false)
	{
	
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	        
	        '_radioCols' => [
	            'Available'    => '', 
	            'Unused'       => '',
	            'Requires'     => '',
	        ], 
	        
	        '__defaultRadios' => [
	            '' => ''
	        ],
	    ];
	
	
	    $values = PatientFeedbackCareAidsTable::getInstance()->getEnumValues($fieldName);
	
	    
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
}