<?php
/**
 * 
 * @author carmen
 * 
 * 16.01.2020 ISPC-2508
 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
 */
class Application_Form_ClientArtificialEntryExit extends Pms_Form
{
	protected $_old_entries_exits = array();
	
	public function __construct($options = null)
	{
		if($options['_old_entries_exits'])
		{
			 
			$this->_old_entries_exits = $options['_old_entries_exits'];
			unset($options['_old_entries_exits']);
		}
		
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function create_form_clientartificialentryexit( $options = array(), $elementsBelongTo = null)
	{
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		
		$this->mapValidateFunction($__fnName , "create_form_isValid");
		
		$this->mapSaveFunction($__fnName , "save_form_clientartificialentryexit");
		 
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => $options['id']['value'] != '' ? $this->translator->translate('clientartificialentryexit_edit') : $this->translator->translate('clientartificialentryexit_add')));
		$this->addDecorator('Form');
		
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
			if ($ko == 'old_name') continue;
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
					'value' => $this->translate('artificial_option_'.$ko),
					'decorators' => $elementDecorators
			));
			 
			switch ($vo['colprop']['type'])
			{
				case 'string':
				case 'integer':
					
					$subsubrow->addElement('text', $ko, array(
					'label'      => null,
					'required'   => false,
					'value' => $vo['value'],
					'decorators' => $elementDecorators
		
					));
					break;
				case 'enum':
					$subsubrow->addElement('radio', $ko, array(
							'label' => null,
							'required'   => false,
							'multiOptions'=>$vo['colprop']['values'],
							'value' => $vo['value'],
							'decorators' => $elementDecorators,
							'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
					
					));
					break;
				default:
					break;
			}
			
			$subtable->addSubForm($subsubrow, $row);
			$row++;
			
			
		}
		
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
		$elementDecorators[$i][$j]['colspan'] = '2';
		
		$subsubrow->addElement('note', 'label_map_old_entries_exits_empty', array(
				'value' => '',
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, $row);
		$row++;
		
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
		$elementDecorators[$i][$j]['colspan'] = '2';
		
		$subsubrow->addElement('note', 'label_map_old_entries_exits', array(
				'value' => $this->translate('map_old_entries_exits'),
				'decorators' => $elementDecorators
		));
		$subtable->addSubForm($subsubrow, $row);
		$row++;
		
		foreach($this->_old_entries_exits as $kb => $vb)
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
		
		
		$subsubrow->addElement('note', 'label_old_entries_exits', array(
				'value' => $kb,
				'decorators' => $elementDecorators
		));
			
		$subsubrow->addElement('radio', $kb, array(
				'label' => null,
				'required'   => false,
				'multiOptions'=>$this->_old_entries_exits[$kb],
				'value' => $options['old_name']['value'][$kb],
				'decorators' => $elementDecorators,
				'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
		
		));
		
		$subtable->addSubForm($subsubrow, $row);
		$row++;
		}
		
			//$rowsubform->addSubForm($subtable, $kcat);
			$this->addSubForm($subtable, 'artificial_entry_exit');
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
	
	public function save_form_clientartificialentryexit(array $data = array())
	{
		if($data['id'] == '')
		{
			$data['id'] = null;
		}
		$entity = ArtificialEntriesExitsListTable::getInstance()->findOrCreateOneBy(['id'], $data['id'], $data);
	
		return $orderm;
	}
	
}