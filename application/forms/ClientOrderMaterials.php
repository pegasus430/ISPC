<?php
/**
 * 
 * @author carmen
 * 
 * 17.12.2018 ISPC-2281
 *
 */
class Application_Form_ClientOrderMaterials extends Pms_Form
{
	protected $_category = null;
	
	public function __construct($options = null)
	{
		if (isset($options['_category'])) {
			$this->_category = $options['_category'];
			unset($options['_category']);
		}
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function create_form_ordermaterials( $options = array(), $elementsBelongTo = null)
	{
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => $options['id']['value'] != '' ? $this->translator->translate('order_edit') : $this->translator->translate('order_add')));
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
		
		//add hidden
		$this->addElement('hidden', 'category', array(
				'value' => $this->_category,
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
		unset($options['category']);
		//Carmen 30.06.2020
		unset($options['connection_id']);
		unset($options['master_id']);
		//--
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
			$this->addSubForm($subtable, 'order_table');
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
	
	public function save_form_ordermaterials(array $data = array())
	{
		$entity  = new ClientOrderMaterials();
		$entconn = $entity->getTable()->getConnection();
		$enttable = new ClientOrderMaterialsTable('', $entconn);
		if($data['id'] == '')
		{
			$data['id'] = null;
		}
		$orderm =  $enttable->findOrCreateOneBy('id', $data['id'], $data);
	
		return $orderm;
	}
	
}