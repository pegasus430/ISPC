<?php
/**
 * 
 * @author carmen
 * June 3, 2019
 *
 */
class Application_Form_SelectForm extends Pms_Form
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
	
	
	
	public function create_form_select($options = array(), $elementsBelongTo = null)
	{	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_select");
	     
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    $subform->removeDecorator('FieldSet');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => ''));
	    //$subform->setLegend($this->translate('Receipt print profiles preffered'));
	    $subform->setAttrib("class", "receipt_print_profile");
	    
	    if ( ! is_null($elementsBelongTo)) {
	    	$subform->setOptions(array(
	    			'elementsBelongTo' => $elementsBelongTo
	    	));
	    }
	    
	    $subform->addElement('note', 'label_select_form', array(
					'value' => $this->translate('2162 - label_select_form'),
					'decorators' => array(
	    				'ViewHelper',
	    				array('Errors'),
	    				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'openOnly' => true)),
	    		),
			));
	    
	    $subform->addElement('select', 'select_option', array(
	    		'multiOptions' => $this->_set_options,
	    		'value'            => '',
	    		'decorators' => array(
	    				'ViewHelper',
	    				array('Errors'),
	    				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'closeOnly' => true)),
	    		),
	    			
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

