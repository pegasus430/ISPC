<?php

 require_once("Pms/Form.php");
 /**
  * ISPC-2654 Lore 06.10.2020
  */
 
 class Application_Form_IcdOpsMreSorting extends Pms_Form {

     protected $_old_icd_ops_mre_sorting = array();
     
     public function __construct($options = null)
     {
         if($options['_old_icd_ops_mre_sorting'])
         {
             $this->_old_icd_ops_mre_sorting = $options['_old_icd_ops_mre_sorting'];
             unset($options['_old_icd_ops_mre_sorting']);
         }
         
         parent::__construct($options);
         
     }
     
     public function isValid($data)
     {
         
         return parent::isValid($data);
     }
 	
 	
     public function create_form_IcdOpsMreSorting( $options = array(), $elementsBelongTo = null)
 	{
 	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
 	    
 	    $this->mapValidateFunction($__fnName , "create_form_isValid");
 	    
 	    $this->mapSaveFunction($__fnName , "save_form_icd_ops_mre_sorting");
 	    	    
 	    $this->addDecorator('FormElements');
 	    $this->addDecorator('Fieldset', array('legend' => $options['id']['value'] != '' ? $this->translator->translate('icd_ops_mre_sorting_edit') : $this->translator->translate('icd_ops_mre_sorting_add')));
 	    $this->addDecorator('HtmlTag', array('tag' => 'table')); 	    
 	    //$this->addDecorator('Form');

 	    //add hidden
 	    $this->addElement('hidden', 'id', array(
 	        'value' => $options['id'],
 	        'readonly' => true,
 	        'decorators' => array(
 	            'ViewHelper',
 	            array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
 	        ),
 	    ));
 	    
 	    $this->addElement('hidden', 'clientid', array(
 	        'value' => $options['clientid'] != '' ? $options['clientid'] : $this->logininfo->clientid,
 	        'readonly' => true,
 	        'decorators' => array(
 	            'ViewHelper',
 	            array(array('data' => 'HtmlTag'), array('tag' => 'td' )),
 	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style'=>'display:none')),
 	        ),
 	    ));
 	    
 	    $sorting  = IcdOpsMreSorting::get_sorting_columns();
 	    $sorting[0] = "";
 	    ksort($sorting);
 	    
        $this->addElement('select', 'main_sort_col', array(
            'label' 	   => 'main_sort_col',
            'multiOptions' => $sorting,
            'value'        => ! empty($options['main_sort_col']) ? $options['main_sort_col'] : null,
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', "class"=>"w200 " )),
                array('Label', array('tag' => 'td', 'tagClass'=>'w100')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => ' { $("select option").prop("disabled", false); $("select").not(this).find("option[value="+ $(this).val() + "]").attr("disabled", true); } ',
        ));
        
        
        $this->addElement('select', 'secondary_sort_col', array(
            'label' 	   => 'secondary_sort_col',
            'multiOptions' => $sorting,
            'value'        => ! empty($options['secondary_sort_col']) ? $options['secondary_sort_col'] : null,
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', "class"=>"w200 " )),
                array('Label', array('tag' => 'td', 'tagClass'=>'w100')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
            'onChange' => ' { $("select option").prop("disabled", false); $("select").not(this).find("option[value="+ $(this).val() + "]").attr("disabled", true); } ',
        ));
       
        $sort= array("1" => "Aufsteigend", "2"=>"Absteigend");
        $this->addElement('select', 'sort_order', array(
            'label' 	   => self::translate('sort_order'),
            'multiOptions' => $sort,
            'value'        => ! empty($options['sort_order']) ? $options['sort_order'] : null,
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', "class"=>"w200 " )),
                array('Label', array('tag' => 'td', 'tagClass'=>'w100')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));

 	    
 	    //add action buttons
 	    $actions = $this->_create_formular_actions($options['formular'] , 'formular');
 	    $this->addSubform($actions, 'form_actions');
 	    
 	    return $this;
 	    
 	    
 	}
 	
 	private function _create_formular_actions($options = array(), $elementsBelongTo = null)
 	{
 	    $subform = new Zend_Form_SubForm();
 	    $subform->clearDecorators();
/*  	    ->setDecorators( array(
 	        'FormElements',
 	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_actions')),
 	    )); */
 	    
 	    $subform->addDecorator('FormElements');
 	    $subform->addDecorator('HtmlTag', array('tag' => 'table'));
 	    
 	    
 	    if ( ! is_null($elementsBelongTo)) {
 	        $subform->setOptions(array(
 	            'elementsBelongTo' => $elementsBelongTo
 	        ));
 	    }
 	    
 	    $el = $this->createElement('button', 'button_action', array(
 	        'type'         => 'submit',
 	        'value'        => 'save',
 	        'label'        => $this->translator->translate('kh_save'),
 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
 	        'decorators'   => array('ViewHelper'),
 	        
 	    ));
 	    $subform->addElement($el, 'save');
 	    
 	    return $subform;
 	    
 	}
 	
 	
 	public function save_form_icd_ops_mre_sorting($data = array())
 	{
 	    if($data['id'] == '') {
 	        $data['id'] = null;
 	    }
 	    
 	    $logininfo = new Zend_Session_Namespace('Login_Info');
 	    $clientid = $logininfo->clientid;
 	    
 	    $entity = IcdOpsMreSortingTable::getInstance()->findOrCreateOneBy(['id', 'clientid'], [$data['id'], $clientid], $data);
 	    
 	    return $entity;
 	    
 	}
 	
 }