<?php
/**
 * 
 * @author carmen
 * August 18, 2020
 *
 */
class Application_Form_ImportCsvDateDialog extends Pms_Form
{	
	public function __construct($options = null)
	{
		parent::__construct($options);
	}
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	
	public function create_form_importcsvdialog($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->removeDecorator('DtDdWrapper');
	    //$subform->removeDecorator('FieldSet');
	    $subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => 'importcsvtable', ));
	    $subform->addDecorator('Form');
	    if ( ! is_null($elementsBelongTo)) {
	    	$subform->setOptions(array(
	    			'elementsBelongTo' => $elementsBelongTo
	    	));
	    }
	    
	    $subform->addElement('note',  'label_importdatev', array(
	    		'required'     => false,
	    		'value'        => $this->translate('DATEV-import for paid invoices'),
	    		'decorators' => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2', 'class' => 'importcsvtitle')),
	    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => '')),
	    		),
	    ));
	    
	    $subform->addElement('file', 'csvfile', array(
	    		'label'        => $this->translate('browsefile for import'),
	    		'value'        => '',
	    		'required'     => true,
	    		'validators'    => array(
	    				array('Extension', false, 'csv')
	    		),
	    		'decorators' => array(
	    				'ViewHelper',
	    				array('Errors'),
	    				array(array('data' => 'HtmlTag'), array('tag' => 'td', )),

	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'print_column_first',
	    				)),
	    				array(array('row' => 'HtmlTag'), array('tag' => 'tr',)),
	    		),
	    
	    ));
	    
	    $subform->addElement('select', 'delimiter', array(
	    		'multiOptions' => array(';' => ';', ',' => ','),
	    		'value'        => ';',
	    		'required'     => true,
	    		'validators'   => array('NotEmpty'),
	    		'decorators' => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array('tag' => 'td',)),

	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'print_column_first',
	    				)),
	    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => '')),
	    		),
	    ));
	    
	    //add action buttons
	    $actions = $this->_create_formular_actions($options['formular'] , 'formular');
	    $actions->clearDecorators()
	    ->setDecorators( array(
	    		'FormElements',
	    		array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => '2', 'class' => 'importcsvtitle')),
	    		array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => '')),
	    ));
	    
	    $subform->addSubform($actions, 'form_actions');
	    
	    return $subform;	    
	     
	}
    
    private function _create_formular_actions($options = array(), $elementsBelongTo = null)
    {
    	$subform = new Zend_Form_SubForm();
    	$subform->clearDecorators()
    	->setDecorators( array(
    			'FormElements',
    			array('HtmlTag',array('tag'=>'div', 'class' => 'formular_actions')),
    	));
    		
    
    	if ( ! is_null($elementsBelongTo)) {
    		$subform->setOptions(array(
    				'elementsBelongTo' => $elementsBelongTo
    		));
    	}
    
    	$el = $this->createElement('button', 'button_action', array(
    			'type'         => 'submit',
    			'value'        => 'importcsv',
    			// 	        'content'      => $this->translate('submit'),
    			'label'        => $this->translator->translate('process import'),
    			// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
    			'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
    			'decorators'   => array('ViewHelper'),
    
    	));
    	$subform->addElement($el, 'save');
    
    		
    	/*$el = $this->createElement('button', 'button_action', array(
    	 'type'         => 'submit',
    	 'value'        => 'printpdf',
    	 // 	        'content'      => $this->translate('submit'),
    	 'label'        => $this->translator->translate('kh_generate_pdf'),
    	 // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
    	 'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
    	 'decorators'   => array('ViewHelper'),
    
    		));
    	$subform->addElement($el, 'printpdf');*/
    		
    		
    	return $subform;
    
    }
    
    
}

