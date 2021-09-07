<?php
/**
 * 
 * @author ancuta
 * ISPC-2864 Ancuta 14.04.2021
 *
 */
class Application_Form_FormBlockProblemsBpss extends Pms_Form
{

   
    
    public function create_form_block_patient_problems_bpss ($values =  array() , $elementsBelongTo = null)
    {
    	$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
    
    	$this->mapValidateFunction($__fnName , "create_form_isValid");
    
    	$this->mapSaveFunction($__fnName , "save_form_block_patient_problems_bpss");
    
    	$subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
    	$subform->setLegend($this->translate('patient_problems'));
    	$subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
    	$subform->addDecorator('Form');
    	 
    	$this->__setElementsBelongTo($subform, $elementsBelongTo);
    	 
    	$subform->addElement('hidden', 'id', array(
    			'label'        => null,
    			'value'        => ! empty($values['id']) ? $values['id'] : '',
    			'required'     => false,
    			'readonly'     => true,
    			'filters'      => array('StringTrim'),
    			'decorators' => array(
    					'ViewHelper',
    					array(array('data' => 'HtmlTag'), array(
    							'tag' => 'td',
    							'colspan' => 2,
    					)),
    					array(array('row' => 'HtmlTag'), array(
    							'tag' => 'tr',
    							'class'    => 'dontPrint',
    					)),
    			),
    	));
 
    	$subform->addElement('hidden', 'clientid', array(
    			'label'        => null,
    			'value'        => ! empty($values['clientid']) ? $values['clientid'] : '',
    			'required'     => false,
    			'readonly'     => true,
    			'filters'      => array('StringTrim'),
    			'decorators' => array(
    					'ViewHelper'
    	 
    			),
    	));
    	
    	$subform->addElement('hidden', 'bpss_type', array(
    			'label'        => null,
    			'value'        => ! empty($values['bpss_type']) ? $values['bpss_type'] : '',
    			'required'     => false,
    			'readonly'     => true,
    			'filters'      => array('StringTrim'),
    	));
    	
    	$subform->addElement('textarea', 'bpss_description', array(
    	    'label'        => self::translate('bpss_description'.$values['bpss_type']).":",
    	    'value'        => ! empty($values['bpss_description']) ? $values['bpss_description'] : "",
    	    'required'     => true,
    	    'filters'      => array('StringTrim'),
    	    'validators'   => array('NotEmpty'),
    	    'class'        => '',
    	    'decorators' =>   array(
    	        'ViewHelper',
    	        array('Errors'),
    	        array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
    	        array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
    	        array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	    ),
    	    'cols' => 60,
    	    'rows' => 3,
    	));
    	
    	
    	return $this->filter_by_block_name($subform, $__fnName);
    }
    
 
    
    
    public function save_form_block_patient_problems_bpss ($ipid =  null , $data =  array())
    {
        
    	if (empty($ipid) || empty($data)) {
    		return;
    	}
 
    	$data['ipid'] = $ipid;
    	$entity = FormBlockProblemsBpssTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
    	 
    	return $entity;
    }
    
}

?>
