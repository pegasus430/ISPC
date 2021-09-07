<?php
/**
 * 
 * @author carmen
 * Oct 14, 2019
 * for ISPC-2465
 */
class Application_Form_TextForm extends Pms_Form
{	
	private $_set_options = null;
	
	public function __construct($options = null)
	{
	
		
		parent::__construct($options);
	
		if (isset($options['_set_options'])) {
			$this->_set_options = $options['_set_options'];
			unset($options['_set_options']);
		}
		
		if (isset($options['elementsBelongTo'])) {
			$this->_elementsBelongTo = $options['elementsBelongTo'];
			unset($options['elementsBelongTo']);
		}
	
	}
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	
	public function create_form_text($options = array(), $elementsBelongTo = null)
	{	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_text");
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
		));
	    
		if($this->_elementsBelongTo)
			{
				$this->__setElementsBelongTo($subform, $this->_elementsBelongTo );
			}
		else if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
	    
	    $subform->addElement('text', $this->_set_options, array(
		    	//'isArray'     => true,
		    	'value'        => '',
		    	//'label'        => $this->translate('label item'),
		    	'required'     => false,
		    	'filters'      => array('StringTrim'),
		    	'validators'   => array('NotEmpty'),
		    	'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
		    				/* array('Label', array(
    									'tag' => 'td',
    									'tagClass'=>'print_column_first'
        
		    				)), */
	    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    					),
	    	'class' => $this->_set_options,
			));
	    
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

