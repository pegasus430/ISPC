<?php

class Application_Form_PatientLives extends Pms_Form
{

    protected $_model = 'PatientLives';
    
    protected $_block_name_allowed_inputs =  array(
    
        "WlAssessment" => [
    
            'create_form_patient_lives' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => []
    
            ],
        ],
    
        "PatientDetails" => [
            'create_form_patient_lives' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    
    
        "MamboAssessment" => [
            'create_form_patient_lives' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
		//Maria:: Migration CISPC to ISPC 22.07.2020
        //ISPC-2625, AOK Kurzassessment, 09.07.2020, elena
        "AokprojectsKurzassessment" => [
            'create_form_patient_lives' => [
                //this are removed
                '__removed' => [],
                //only this are allowed
                '__allowed' => [],
            ],
        ],
    );
    
    protected $_block_feedback_options = [
        "MamboAssessment" => [
            'create_form_patient_lives_v2' => [
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
		// Maria:: Migration CISPC to ISPC 22.07.2020
        // ISPC-2625, AOK Kurzassessment, 09.07.2020, elena
        "AokprojectsKurzassessment" => [
            'create_form_patient_lives_v2' => [
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
    
    
    
	public function InsertData($post)
	{
		$frm = new PatientLives();
		$frm->ipid = $post['ipid'];
		$frm->alone = $post['alone'];
		$frm->house_of_relatives = $post['house_of_relatives'];
		$frm->apartment = $post['apartment'];
		$frm->home = $post['home'];
		$frm->hospiz = $post['hospiz'];
		$frm->sonstiges = $post['sonstiges'];
		$frm->save();
	}

	public function UpdateData($post)
	{
		$q = Doctrine_Query::create()
		->update('PatientLives')
		->set('alone', "'".$post['alone']."'")
		->set('house_of_relatives', "'".$post['house_of_relatives']."'")
		->set('apartment', "'".$post['apartment']."'")
		->set('home', "'".$post['home']."'")
		->where("ipid = '".$post['ipid']."'");
		$q->execute();
	}
	
	
	
	
	
	
	

	/**
	 * @cla on 11.07.2018
	 * Versorgung = PatientMobility
	 *
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_patient_lives($values =  array() , $elementsBelongTo = null)
	{
	
	    $this->mapValidateFunction(__FUNCTION__ , "create_form_isValid");
	
	    $this->mapSaveFunction(__FUNCTION__ , "save_form_patient_lives");
	
	
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend('lives');
	    $subform->setAttrib("class", "label_same_size_100 multipleCheckboxes inlineEdit " . __FUNCTION__);
	
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    } elseif ($elementsBelongTo = $this->getElementsBelongTo()) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	
	    
	    
	    $subform->addElement('checkbox', 'alone', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'alone',
	        'required'   => false,
	        'value' => $values['alone'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('checkbox', 'house_of_relatives', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'houseofrelatives',
	        'value' => $values['house_of_relatives'] ,
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),	
	    ));
	    
	    $subform->addElement('checkbox', 'apartment', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'apartment',
	        'value' => $values['apartment'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),	
	    ));
	     
	    $subform->addElement('checkbox', 'home', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'home',
	        'value' => $values['home'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),	
	    ));
	    
	    $subform->addElement('checkbox', 'hospiz', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'Hospiz',
	        'value' => $values['hospiz'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),	
	    ));
	    
	    $subform->addElement('checkbox', 'with_partner', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'with partner',
	        'value' => $values['with_partner'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),	
	    ));
	    
	    $subform->addElement('checkbox', 'with_child', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'with child',
	        'value' => $values['with_child'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),	
	    ));
	    
	    $subform->addElement('checkbox', 'sonstiges', array(
	        'checkedValue'    => '1',
	        'uncheckedValue'  => '0',
	        'label'      => 'sonstige',
	        'value' => $values['sonstiges'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),	
	    ));
	     
	    
	    
	    
	
	    return $this->filter_by_block_name($subform, __FUNCTION__);
	
	}
	public function save_form_patient_lives($ipid =  '' , $data = array())
	{
	    //this is cb
	    if(empty($ipid)) {
	        return;
	    }
	    
	    $entity = new PatientLives();
	    
	    $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
	    
	    foreach ($this->getCbValuesArray() as $kv => $tr) {
	       $this->_save_box_History($ipid, $newEntity, $kv, 'grow1', 'text');
	    }
	     
	    return $newEntity;
	}
	
	public function save_form_patient_lives_v2($ipid =  '' , $data = array())
	{
	    //this is cb
	    if(empty($ipid)) {
	        return;
	    }
	    
	    $entity = new PatientLivesV2();
	    
	    $newEntity =  $entity->findOrCreateOneBy('ipid', $ipid, $data);
	     
	    return $newEntity;
	}
	
	
	
	
	
	public function getCbValuesArray()
	{
	    return PatientLives::getCbValuesArray();
	}
	
	

	private function _save_box_History($ipid, $newEntity, $fieldname, $formid, $checkbox_or_radio_or_text)
	{
	
	    $newModifiedValues = $newEntity->getLastModified();
	
	    if (isset($newModifiedValues[$fieldname])) {
	        $oldValues = $newEntity->getLastModified(true);
	
	        $add_sufix = "";
	        $remove_sufix = "";
	        $added = [];
	        $removed = [];
	
	        switch ($checkbox_or_radio_or_text) {
	
	            case  "checkbox" :
	
	                $new_values = explode(',', $newModifiedValues[$fieldname]);
	                $old_values = explode(',', $oldValues[$fieldname]);
	
	                $added = array_diff($new_values, $old_values);
	                $removed = array_diff($old_values , $new_values);
	
	                $add_sufix = "-1";
	                $remove_sufix = "-0";
	
	                break;
	
	            case "radio" :
	            case "text" :
	            default:
	
	                $new_values = $newModifiedValues[$fieldname];
	                $old_values = $oldValues[$fieldname];
	
	                $added = [$new_values];
	
	                break;
	        }
	
	        $history = [];
	
	        if ( ! empty($added)) {
	            foreach ($added as $val) {
	                $history[] = [
	                    'ipid' => $ipid,
	                    'clientid' => $this->logininfo->clientid,
	                    'formid' => $formid,
	                    'fieldname' => $fieldname,
	                    'fieldvalue' => $val . $add_sufix,
	                ];
	            }
	        }
	
	
	        if ( ! empty($removed)) {
	            foreach ($removed as $val) {
	                $history[] = [
	                    'ipid' => $ipid,
	                    'clientid' => $this->logininfo->clientid,
	                    'formid' => $formid,
	                    'fieldname' => $fieldname,
	                    'fieldvalue' => $val . $remove_sufix,
	                ];
	            }
	        }
	
	        if ( ! empty($history)) {
	            $coll = new Doctrine_Collection("BoxHistory");
	            $coll->fromArray($history);
	            $coll->save();
	        }
	    }
	
	}
	
	
	/**
	 * @cla on 13.12.2018 for Mamabo Assessment ..  this uses a single vs a multiOptions in the previous .. the 2 cannot coexist in logic 
	 * @param unknown $values
	 * @param string $elementsBelongTo
	 * @return Zend_Form
	 */
	public function create_form_patient_lives_v2($values =  array() , $elementsBelongTo = null)
	{
	
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_patient_lives_v2");
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->addDecorator('HtmlTag', array('tag'=>'table'))->removeDecorator('DtDdWrapper');
	    $subform->setLegend('Housing situation');
	    $subform->setAttrib("class", "label_same_size_auto multipleCheckboxes inlineEdit {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    

	    $subform->addElement('select', 'type_1', array(
	        'multiOptions'   => $this->getColumnMapping_v2('type_1'),
	        'value' => $values['type_1'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    $subform->addElement('select', 'type_2', array(
	        'multiOptions'   => $this->getColumnMapping_v2('type_2'),
	        'value' => $values['type_2'],
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	        ),
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	
	}
	
	


	public function getColumnMapping_v2($fieldName, $revers = false)
	{
	    //             $fieldName => [ value => translation]
	    $overwriteMapping = [
	         
	        'type_1' => [
	            ''  => '---', //extra empty value for select
	        ], 
	        
	        'type_2' => [
	            ''  => '---', //extra empty value for select
	        ],
	    ];
	
	    $values = PatientLivesV2Table::getInstance()->getEnumValues($fieldName);
	
	    $values = array_combine($values, array_map("self::translate", $values));
	
	    if (isset($overwriteMapping[$fieldName])) {
	        $values = $overwriteMapping[$fieldName] + $values;
	    }
	
	    return $values;
	
	}
	
}
?>