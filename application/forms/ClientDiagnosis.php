<?php
/**
 * 
 * @author carmen
 * 
 * 21.11.2019 ISPC-2412 	    // Maria:: Migration ISPC to CISPC 08.08.2020
 *
 */
class Application_Form_ClientDiagnosis extends Pms_Form
{	
	public function __construct($options = null)
	{
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function create_form_clientdiagnosis( $options = array(), $elementsBelongTo = null)
	{
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => $options['id']['value'] != '' ? $this->translator->translate('client_diagno_edit') : $this->translator->translate('client_diagno_add')));
		$this->addDecorator('Form');
	
		if ( ! is_null($elementsBelongTo)) {
			$this->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		//add hidden
		$this->addElement('hidden', 'id', array(
				'value' => $options['id']['value'],
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
		//add hidden
		$this->addElement('hidden', 'clientid', array(
				'value' => $options['clientid']['value'] != '' ? $options['clientid']['value'] : $this->logininfo->clientid,
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
		$subtable = new Zend_Form_SubForm();
		//$subform->removeDecorator('Fieldset');
		$subtable->setDecorators( array(
				'FormElements',
				array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable', 'cellpadding'=>"0", 'cellspacing'=>"0", "style"=>'width: 600px;')),
				//'Fieldset',
		));
		
		unset($options['id']);
		unset($options['clientid']);
		$row = 0;
		
		foreach($options as $ko=>$vo)
		{			
			$subsubrow = $this->subFormTableRow();
	
			$elementDecorators = array();
			$i = 0;
			$elementDecorators[$i] = 'ViewHelper';
			$i++;
			$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
			$i++;
			$j = 0;
			$elementDecorators[$i][$j]['data'] = 'HtmlTag';
			$j++;
				
			$elementDecorators[$i][$j]['tag'] = 'td';
			
			
			$subsubrow->addElement('note', 'label_'.$ko, array(
					'value' => $this->translate($ko),
					'decorators' => $elementDecorators
			));
			
			
			$subsubrow->addElement('text', $ko, array(
			'label'      => null,
			'required'   => false,
			'value' => $vo['value'],
			'decorators' => $elementDecorators

			));
			
			$subtable->addSubForm($subsubrow, $row);
			$row++;
		}
			//$rowsubform->addSubForm($subtable, $kcat);
			$this->addSubForm($subtable, 'cdiag_table');
			//exit;
	
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
			 // 	        'content'      => $this->translate('submit'),
			 'label'        => $this->translator->translate('kh_save'),
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
	
	public function save_form_clientdiagnosis(array $data = array())
	{
		if($data['id'] == '')
		{
			$data['id'] = null;
		}
		$cdiag =  DiagnosisTable::getInstance()->findOrCreateOneBy('id', $data['id'], $data);
	
		return $orderm;
	}
	
}