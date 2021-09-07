<?php
/**
 * 
 * @author carmen
 * 
 * 15.09.2019 ISPC-2454 // Maria:: Migration ISPC to CISPC 08.08.2020	
 *
 */
class Application_Form_FormBlockCustomItems extends Pms_Form
{
	protected $_block_editable = null; 
	
	public function __construct($options = null)
	{
		if($options['_block_editable'])
		{
			
			$this->_block_editable = $options['_block_editable'];
			unset($options['_block_editable']);
		}
		
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function create_form_formblockcustomitems( $options = array(), $elementsBelongTo = null)
	{
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		
		$this->mapValidateFunction($__fnName , "create_form_isValid");
		
		$this->mapSaveFunction($__fnName , "save_form_formblockcustomsettings");
		 
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => $options['id']['value'] != '' ? $this->translator->translate('formblockcustom_edit') : $this->translator->translate('formblockcustom_add')));
		$this->addDecorator('Form');

		//print_r($options); exit;		
		//add hidden
		$this->addElement('hidden', 'id', array(
				'value' => $options['id']['value'] != '' ? $options['id']['value'] : '',
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
		$this->addElement('hidden', 'block_abbrev', array(
				//'label' 	   => $this->translator->translate('block_abbrev'),
				'value'        => $options['id']['value'] != '' ? $options['block_abbrev']['value'] : '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						//array('Label', array('placement' => 'PREPEND', 'style' => 'display: inline-block; width: 10%;')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;', 'class' => 'titlediv'))
				),
		
		));
		
		$this->addElement('text', 'block_name', array(
				'label' 	   => $this->translator->translate('Kontaktformular Block Headline'), 
				'value'        => $options['id']['value'] != '' ? $options['block_name']['value'] : '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'PREPEND',  'style' => 'display: inline-block, width: 10%;')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;', 'class' => 'titlediv'))
				),
				'style' => 'width: 50%',
				
		));
		
		$this->addElement('select',  'content_item_type', array(
				'label' 	   => $this->translator->translate('Kontaktformular Block Type'),
				'value'        => $options['block_type']['value'],
				'required'     => false,
				'multiOptions' => array('checkbox'=>'Checkbox', 'radio'=>'Radio', 'text'=>'Text', 'textarea' => 'Textarea'),
				//'multiOptions' => array('checkbox'=>'Checkbox', 'radio'=>'Radio'),
				'filters'      => array('StringTrim'),
				'validators'   => array('Int'),
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'PREPEND',  'style' => 'display: inline-block, width: 10%;')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;', 'class' => 'titlediv'))
				),
				'class' => 'ctype',
				'attribs' => $this->_block_editable == 'noteditable' ? array('disabled' => 'disabled') : array(),
		));
			
		$this->addElement('hidden', 'content_item_type_hidd', array(
				//'label' 	   => $this->translator->translate('block_abbrev'),
				'value'        => $options['block_type']['value'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'PREPEND',  'style' => 'display: inline-block, width: 10%;')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;', 'class' => 'titlediv'))
				),
				'class' => 'ctypehidd',
					
		));
		
		$subform = new Zend_Form_SubForm();
		$subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				array('HtmlTag',array('tag'=>'table', 'id' => 'formblockitems', 'style' => 'width: 100%;')),
		));
		
		$subform->addElement('note',  'content_item_label', array(
				'value'        => $this->translator->translate('Kontaktformular Block Options'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'th')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr',)),
				),
					
		));
		
		foreach($options['block_content']['value'] as $krow=>$vrow)
		{
			$subrowform = new Zend_Form_SubForm();
			$subrowform->clearDecorators()
					->setDecorators( array(
				'FormElements',
				//array('HtmlTag',array('tag'=>'table', 'class' => 'formular_actions', 'style' => 'border: 1px solid #000;')),
			));
		
			$this->__setElementsBelongTo($subrowform, $krow);
			$subrowform->addElement('text',  'content_item', array(
				'value'        => isset($vrow['content_item']) ? $vrow['content_item'] : "",
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td',)),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
				),
				'style' => $this->_block_editable == 'noteditable' ? 'color: #bbb; width: 90%;' : 'width: 90%;',
				'attribs' => $this->_block_editable == 'noteditable' ? array('readOnly' => true) : array(),
			));
			
			 $subform->addSubform($subrowform, $krow);
		}
		
		$this->addSubform($subform, 'block_content');
		
		$this->addElement('note', 'add_new_item', array(
				//'belongsTo' => 'set['.$krow.']',
				'label' => $this->translator->translate('add_new_item_row'),
				'value' => '<img src="'.RES_FILE_PATH.'/images/btttt_plus.png" class="ibutton addbutton" style="display: block; float: left; margin-right: 5px;"/>',
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'APPEND', 'class' => 'addbutton ipad_label', 'style' => 'display: block; height: 22px; line-height: 22px;')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left; padding-top: 10px; padding-bottom: 10px;', 'id' => 'divadd')),
				)));
		
		//add action buttons
		$actions = $this->_create_formular_actions($options['formular'] , 'formular');
		$this->addSubform($actions, 'form_actions');
		
		return $this->filter_by_block_name($subform, $__fnName);
	
	}
	
	public function create_form_formblockcustomitem_row ($values=array() , $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				//array('HtmlTag',array('tag'=>'table', 'class' => 'formular_actions', 'style' => 'border: 1px solid #000;')),
		));
		
		$this->__setElementsBelongTo($subform, $elementsBelongTo);
		
		$subform->addElement('text',  'content_item', array(
				'value'        => isset($options['content_item']) ? $options['content_item'] : "",
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array('tag' => 'td')),
						array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
				),
				'style' =>'width: 90%;'	
		));
		
		return $this->filter_by_block_name($subform, $__fnName);
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
			 //'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
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
	
	public function save_form_formblockcustomsettings(array $data = array())
	{
		if($data['id'] == '')
		{
			$data['id'] = null;
			$data['block_abbrev'] = 'custom_'.uniqid();
		}

		foreach($data['block_content'] as $kc => $vc)
		{
			if($vc['content_item'] == "")
			{
				unset($data['block_content'][$kc]);
			}
			unset($data['block_content'][$kc]['content_item_type']);
		}
		//var_dump($data); exit;
		$entity = FormBlockCustomSettingsTable::getInstance()->findOrCreateOneBy(['id'], $data['id'], $data);
		
	}
	
}