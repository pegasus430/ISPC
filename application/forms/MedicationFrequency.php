<?php
/**
 * 
 * @author carmen
 * 
 * 23.10.2018 ISPC-2247
 *
 */
class Application_Form_MedicationFrequency extends Pms_Form
{
	
	public function __construct($options = null)
	{
		
		//print_r($this->_patientMasterData); exit;
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function create_form_medicationfrequency( $options = array(), $elementsBelongTo = null)
	{
		 
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => $options['id']['value'] != '' ? $this->translator->translate('medicationfrequency_edit') : $this->translator->translate('medicationfrequency_add')));
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
					
					
					$subsubrow->addElement('note', 'label_medication_frequency', array(
							'value' => $this->translate('frequency'),
							'decorators' => $elementDecorators
					));
					
					
					$subsubrow->addElement('text', 'frequency', array(
					'label'      => null,
					'required'   => false,
					'value' => $options['frequency']['value'],
					'decorators' => $elementDecorators
	
					));
				
				$subtable->addSubForm($subsubrow, 'frequency');
		
			//$rowsubform->addSubForm($subtable, $kcat);
			$this->addSubForm($subtable, 'freq_table');
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
	
	public function save_form_medicationfrequency(array $data = array())
	{
		$id = $data['id'];
		$entitybs  = new MedicationFrequency();
		$medfreq =  $entitybs->findOrCreateOneById($id, $data, true);
	
		return $medfreq;
	}
	
}