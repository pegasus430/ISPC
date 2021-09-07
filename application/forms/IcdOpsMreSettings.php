<?php

 require_once("Pms/Form.php");
 /**
  * ISPC-2654 Lore 06.10.2020
  */
 
 class Application_Form_IcdOpsMreSettings extends Pms_Form {

     protected $_old_icd_ops_mre = array();
     
     public function __construct($options = null)
     {
         if($options['_old_icd_ops_mre'])
         {
             $this->_old_icd_ops_mre = $options['_old_icd_ops_mre'];
             unset($options['_old_icd_ops_mre']);
         }
         
         parent::__construct($options);
         
     }
     
     public function isValid($data)
     {
         
         return parent::isValid($data);
     }
 	
 	
 	public function create_form_IcdOpsMreSettings( $options = array(), $elementsBelongTo = null)
 	{
 	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
 	    
 	    $this->mapValidateFunction($__fnName , "create_form_isValid");
 	    
 	    $this->mapSaveFunction($__fnName , "save_form_icd_ops_mre");
 	    	    
 	    $this->addDecorator('FormElements');
 	    $this->addDecorator('Fieldset', array('legend' => $this->translator->translate('icd_ops_mre_settings_edit') ));
 	    $this->addDecorator('Form');

 	    //add hidden
 	    $this->addElement('hidden', 'id', array(
 	        'value' => $options['id'],
 	        'readonly' => true,
 	        'decorators' => array(
 	            'ViewHelper',
 	            array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
 	        ),
 	    ));
 	    
 	    $this->addElement('hidden', 'clientid', array(
 	        'value' => $options['clientid'] != '' ? $options['clientid'] : $this->logininfo->clientid,
 	        'readonly' => true,
 	        'decorators' => array(
 	            'ViewHelper',
 	            array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
 	        ),
 	    ));
 	    
 	    
 	    $category = IcdOpsMreSettings::getCategory();
        $this->addElement('select', 'category', array(
            'label' 	   => 'category',
            'multiOptions' => $category,
            'value'        => !empty($options['category']) ? $options['category'] : null,
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array('Label', array('tag' => 'td', 'style'=>"padding-right: 57px;")),
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
            ),
            //'onchange' => '{$("#shortcut").val($(this).val());}', 
            'onchange' => 'if($(this).val()=="1"){$("#shortcut").val("H"); $(".shortcut_show", $(this).parents(\'div\')).hide();}
                    else { if($(this).val()=="2"){$("#shortcut").val("G"); $(".shortcut_show", $(this).parents(\'div\')).hide();} 
                    else { if($(this).val()=="3"){$("#shortcut").val("F"); $(".shortcut_show", $(this).parents(\'div\')).hide();} 
                    else { if($(this).val()=="4"){$("#shortcut").val("S"); $(".shortcut_show", $(this).parents(\'div\')).hide();}
                    else { if($(this).val()=="5"){$("#shortcut").val("X"); $(".shortcut_show", $(this).parents(\'div\')).hide();} 
                    else { if($(this).val()=="6"){$("#shortcut").val("D"); $(".shortcut_show", $(this).parents(\'div\')).hide();} 
                    else { if($(this).val()=="7"){$("#shortcut").val("A"); $(".shortcut_show", $(this).parents(\'div\')).hide();} }}}}}} ',
        ));
        
        
        if(empty($options['shortcut'])){
            $options['shortcut'] = 'H';
        }
        //$display_shortcut = $options['shortcut']=='H' || $options['shortcut']=='D' ? 'display:none;' : '';
        $display_shortcut = 'display:none;';
        
        $this->addElement('text', 'shortcut', array(
            'label' 	   => $this->translate('shortcut'),
            'value'        => $options['shortcut'],
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array('Label', array('tag' => 'td', 'class'=>'shortcut_show', 'style' => $display_shortcut,)),
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'shortcut_show', 'style' => 'width: 100%;'.$display_shortcut))
            ),
            
        ));

        $background_color_display = !empty($options['color']) ? "background-color: #".$options['color'] : '';
        $this->addElement('hidden', 'color', array(
            'label'        => $this->translate('select_category_color'),
            'value'        => $options['color'],
            'required'     => false,
            'filters'      => array('StringTrim'),
            'class'        => 'colorSelector',
            'style'        => 'background-color: ' . $options['color'],
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'id'=>'colorSelector', 'class'=>'icon_color_selector', 'style'=>"margin: 15px;".$background_color_display)),
                array('Label', array('tag' => 'td','class'=>'icon_color_selector')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        ));
        
/*         $sort= array("1" => "ASC", "2"=>"DESC");
        $this->addElement('select', 'sort_order', array(
            'label' 	   => $this->translate('sort_order'),
            'multiOptions' => $sort,
            'value'        => ! empty($options['sort_order']) ? $options['sort_order'] : null,
            'decorators'   => array(
                'ViewHelper',
                array('Errors'),
                array('Label', array('tag' => 'td')),
                array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
            ),
        )); */

 	    
 	    //add action buttons
 	    $actions = $this->_create_formular_actions($options['formular'] , 'formular');
 	    $this->addSubform($actions, 'form_actions');
 	    
 	    return $this;
 	    
 	    
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
 	        'value'        => 'save',
 	        'label'        => $this->translator->translate('kh_save'),
 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
 	        'decorators'   => array('ViewHelper'),
 	        
 	    ));
 	    $subform->addElement($el, 'save');
 	    
 	    return $subform;
 	    
 	}
 	
 	
 	public function save_form_icd_ops_mre($data = array())
 	{
 	    if($data['id'] == '') {
 	        $data['id'] = null;
 	    }
 	    
 	    $logininfo = new Zend_Session_Namespace('Login_Info');
 	    $clientid = $logininfo->clientid;
 	    
 	    $entity = IcdOpsMreSettingsTable::getInstance()->findOrCreateOneBy(['id', 'clientid'], [$data['id'], $clientid], $data);
 	    
 	    return $entity;
 	    
 	}
 	
 }