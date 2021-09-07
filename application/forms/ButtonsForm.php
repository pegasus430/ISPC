<?php
/**
 * ISPC-2508 new design
 * @author carmen
 * May, 2020
 *#ISPC-2512PatientCharts
 */
class Application_Form_ButtonsForm extends Pms_Form
{	
	private $_set_options = null;
	
	public function __construct($options = null)
	{
		
		parent::__construct($options);
	
		if (isset($options['_set_options'])) {
			$this->_set_options = $options['_set_options'];
			unset($options['_set_options']);
		}
		
	}
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	
	public function create_form_buttons($options = array(), $elementsBelongTo = null)
	{	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_select");
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				array('HtmlTag',array('tag'=>'div', 'class' => 'formular_buttons')),
		));
	    
	    if ( ! is_null($elementsBelongTo)) {
	    	$subform->setOptions(array(
	    			'elementsBelongTo' => $elementsBelongTo
	    	));
	    }
	    
	    foreach($this->_set_options as $kopt => $vopt)
	    {
	    	$el = $this->createElement('button', 'button_action', array(
	    			'type'         => 'button',
	    			'value'        => $kopt,
	    			'label'        => $vopt,
	    			'decorators'   => array('ViewHelper'),
	    			'class' => 'artificial_action'
	    	
	    	));
	    	$subform->addElement($el, $kopt);
	    }
	    
	    
	    return $this->filter_by_block_name($subform , __FUNCTION__);	    
	     
	}
	
	public function save_form_select()
	{
	    
	}
	
	
	
	
	
    public function getColumnMapping($fieldName, $revers = false) 
    {
        

        $overwriteMapping = [
//             $fieldName => [ value => translation]

        ];
        
        
        $values = UserTable::getInstance()->getEnumValues($fieldName);
        
        $values = array_combine($values, array_map("self::translate", $values));
        
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
        
        return $values;
        
    }
        
   
    
    
}

