<?php
/**
 * 
 * @author carmen
 * 
 * 29.05.2019 ISPC-2162
 *
 */
class Application_Form_ClientPrintSettings extends Pms_Form
{
	private $_client_receipt_profiles = null;
	
	public function __construct($options = null)
	{	
		if (isset($options['_client_receipt_profiles'])) {
			$this->_client_receipt_profiles = $options['_client_receipt_profiles'];
			unset($options['_client_receipt_profiles']);
		}
		//print_r($this->_patientMasterData); exit;
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
		if($data['settingstable']['plansmedi_settings']['plan_font_size'] && !is_numeric($data['settingstable']['plansmedi_settings']['plan_font_size']))
		{
			return false;
		}
		elseif($data['settingstable']['profile_settings']['margin_top'] && !is_numeric($data['settingstable']['profile_settings']['margin_top']))
		{
			return false;
		}
		elseif($data['settingstable']['profile_settings']['margin_bottom'] && !is_numeric($data['settingstable']['profile_settings']['margin_bottom']))
		{
			return false;
		}
		elseif($data['settingstable']['profile_settings']['margin_left'] && !is_numeric($data['settingstable']['profile_settings']['margin_left']))
		{
			return false;
		}
		elseif($data['settingstable']['profile_settings']['margin_right'] && !is_numeric($data['settingstable']['profile_settings']['margin_right']))
		{
			return false;
		}
		return parent::isValid($data);
	}
	
	public function create_form_addclientprintsettings( $options = array(), $elementsBelongTo = null)
	{
		if($options['id']['value'] != '')
		{
			if($this->_block_name == 'PLANSMEDIPRINTSETTINGS')
			{
				$fieldsetname = $this->translator->translate('plansmediprintsettings_edit');
			}
			else 
			{
				$fieldsetname = $this->translator->translate('receiptprintsettings_edit');
			}
		}
		else 
		{
			if($this->_block_name == 'PLANSMEDIPRINTSETTINGS')
			{
				$fieldsetname = $this->translator->translate('plansmediprintsettings_add');
			}
			else
			{
				$fieldsetname = $this->translator->translate('receiptprintsettings_add');
			}
		}
		
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => $fieldsetname));
		$this->addDecorator('Form');		

		/* $this->setOptions(array(
				'elementsBelongTo' => $this->_block_name
		));	 */			
			
		//add hidden
		$this->addElement('hidden', 'block_name', array(
				'value' => $this->_block_name,
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
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
		
		
		if($this->_block_name == 'PLANSMEDIPRINTSETTINGS')
		{
			$saved_plans = PlansMediPrintSettingsTable::findAllClientPlansMediPrintSettings($this->logininfo->clientid);
			$saved_plans_ids = array_map(function($plan) {
				return $plan['plansmedi_id'];
			}, $saved_plans);
			$all_plans = [
					'medication' => $this->translate("pdf_medicationplan"),
					'medication_plan_patient' => $this->translate("pdf_patient_medicationplan"),
					'medication_plan_patient_active_substance' => $this->translate("pdf_patient_medicationplan") ." ". $this->translate("medication_drug"),
					'medication_plan_bedarfsmedication' => $this->translate("pdf_patient_bedarfsmedication"),
					'medication_plan_applikation' => $this->translate("pdf_patient_applikation")
			];
			$unset_plans = array();
			
			foreach($all_plans as $key=>$val)
			{
				if(!in_array($key, $saved_plans_ids))
				{
					$unset_plans[$key] = $val;
				}
			}
			

			//add hidden
			$this->addElement('hidden', 'unset_plans_nr', array(
					'value' => count($unset_plans),
					'readonly' => true,
					'decorators' => array(
							'ViewHelper',
							array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
					),
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
				
				
			$subsubrow->addElement('note', 'label_plan_medi', array(
					'value' => $this->translate('label_plan_medi'),
					'decorators' => $elementDecorators
			));
				
			if($options['id']['value'])
			{
				$subsubrow->addElement('note', 'label_plan_medi', array(
					'value' => "<b>".$all_plans[$options['plansmedi_id']['value']]."</b>",
					'decorators' => $elementDecorators
				));
				//add hidden
				$subsubrow->addElement('hidden', 'plansmedi_id', array(
						'label'      => null,
						'required'   => false,
						'value' => $options['plansmedi_id']['value'],
						'decorators' => $elementDecorators
				
				));
			}
			else 
			{			
					$subsubrow->addElement('select', 'plansmedi_id', array(
							'multiOptions' => $unset_plans,
							'value'            => $options['plansmedi_id']['value'],
							'decorators' => $elementDecorators
					
					));
			}
			
			$subtable->addSubForm($subsubrow, '1');
				
			$subsubrow = $this->subFormTableRow();
	
			$elementDecorators = array();
			$i = 0;
			$elementDecorators[$i] = 'ViewHelper';
			$i++;
			$elementDecorators[$i] = 'Errors';
			$i++;
			$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
			$i++;
			$j = 0;
			$elementDecorators[$i][$j]['data'] = 'HtmlTag';
			$j++;
				
			$elementDecorators[$i][$j]['tag'] = 'td';
			
			
			$subsubrow->addElement('note', 'label_plan_font_size', array(
					'value' => $this->translate('label_plan_font_size'),
					'decorators' => $elementDecorators
			));
			
			$subsubrow->setOptions(array(
					'elementsBelongTo' => 'plansmedi_settings'
			));
			
			$subsubrow->addElement('text', 'plan_font_size', array(
			'label'      => null,
			'required'   => false,
			'value' => $options['plansmedi_settings']['value']['plan_font_size'],
			'decorators' => $elementDecorators,
			));
			
			$subtable->addSubForm($subsubrow, '2');
		
			//$rowsubform->addSubForm($subtable, $kcat);
			$this->addSubForm($subtable, 'settingstable');
			//exit;
		}
		else 
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
			
			
				$subsubrow->addElement('note', 'label_profile_name', array(
						'value' => $this->translate('label_profile_name'),
						'decorators' => $elementDecorators
				));
			
				$subsubrow->addElement('text', 'profile_name', array(
						'label'      => null,
						'required'   => false,
						'value' => $options['profile_name']['value'],
						'decorators' => $elementDecorators,
				));
					
				$subtable->addSubForm($subsubrow, '1');
			
				$subsubrow = $this->subFormTableRow();
			
				$elementDecorators = array();
				$i = 0;
				$elementDecorators[$i] = 'ViewHelper';
				$i++;
				$elementDecorators[$i] = 'Errors';
				$i++;
				$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
				$i++;
				$j = 0;
				$elementDecorators[$i][$j]['data'] = 'HtmlTag';
				$j++;
			
				$elementDecorators[$i][$j]['tag'] = 'td';
					
					
				$subsubrow->addElement('note', 'label_margin_top', array(
						'value' => $this->translate('label_margin_top'),
						'decorators' => $elementDecorators
				));
					
				$subsubrow->setOptions(array(
						'elementsBelongTo' => 'profile_settings'
				));
					
				$subsubrow->addElement('text', 'margin_top', array(
						'label'      => null,
						'required'   => false,
						'value' => $options['profile_settings']['value']['margin_top'],
						'decorators' => $elementDecorators,
				));
					
				$subtable->addSubForm($subsubrow, '2');
				
				/* $subsubrow = $this->subFormTableRow();
					
				$elementDecorators = array();
				$i = 0;
				$elementDecorators[$i] = 'ViewHelper';
				$i++;
				$elementDecorators[$i] = 'Errors';
				$i++;
				$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
				$i++;
				$j = 0;
				$elementDecorators[$i][$j]['data'] = 'HtmlTag';
				$j++;
					
				$elementDecorators[$i][$j]['tag'] = 'td';
					
					
				$subsubrow->addElement('note', 'label_margin_bottom', array(
						'value' => $this->translate('label_margin_top'),
						'decorators' => $elementDecorators
				));
					
				$subsubrow->setOptions(array(
						'elementsBelongTo' => 'profile_settings'
				));
					
				$subsubrow->addElement('text', 'margin_bottom', array(
						'label'      => null,
						'required'   => false,
						'value' => $options['profile_settings']['value']['margin_bottom'],
						'decorators' => $elementDecorators,
				));
					
				$subtable->addSubForm($subsubrow, '3'); */
				
				$subsubrow = $this->subFormTableRow();
					
				$elementDecorators = array();
				$i = 0;
				$elementDecorators[$i] = 'ViewHelper';
				$i++;
				$elementDecorators[$i] = 'Errors';
				$i++;
				$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
				$i++;
				$j = 0;
				$elementDecorators[$i][$j]['data'] = 'HtmlTag';
				$j++;
					
				$elementDecorators[$i][$j]['tag'] = 'td';
					
					
				$subsubrow->addElement('note', 'label_margin_left', array(
						'value' => $this->translate('label_margin_left'),
						'decorators' => $elementDecorators
				));
					
				$subsubrow->setOptions(array(
						'elementsBelongTo' => 'profile_settings'
				));
					
				$subsubrow->addElement('text', 'margin_left', array(
						'label'      => null,
						'required'   => false,
						'value' => $options['profile_settings']['value']['margin_left'],
						'decorators' => $elementDecorators,
				));
					
				$subtable->addSubForm($subsubrow, '4');
				
				/* $subsubrow = $this->subFormTableRow();
					
				$elementDecorators = array();
				$i = 0;
				$elementDecorators[$i] = 'ViewHelper';
				$i++;
				$elementDecorators[$i] = 'Errors';
				$i++;
				$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
				$i++;
				$j = 0;
				$elementDecorators[$i][$j]['data'] = 'HtmlTag';
				$j++;
					
				$elementDecorators[$i][$j]['tag'] = 'td';
					
					
				$subsubrow->addElement('note', 'label_margin_right', array(
						'value' => $this->translate('label_margin_right'),
						'decorators' => $elementDecorators
				));
					
				$subsubrow->setOptions(array(
						'elementsBelongTo' => 'profile_settings'
				));
					
				$subsubrow->addElement('text', 'margin_right', array(
						'label'      => null,
						'required'   => false,
						'value' => $options['profile_settings']['value']['margin_right'],
						'decorators' => $elementDecorators,
				));
					
				$subtable->addSubForm($subsubrow, '5'); */
			
				//$rowsubform->addSubForm($subtable, $kcat);
				$this->addSubForm($subtable, 'settingstable');
				//exit;
			}
	
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
	
	public function save_form_clientprintsettings(array $data = array())
	{
		//print_r($data); exit;
		if($data['id'] == '')
		{
			$data['id'] = null;
		}
		
		switch($data['block_name'])
		{
			case 'PLANSMEDIPRINTSETTINGS':
				$table = 'PlansMediPrintSettingsTable';
				break;
			case 'RECEIPTPRINTSETTINGS':
				$table = 'ReceiptPrintSettingsTable';
				break;
			default:
				break;
				
		}
		
		foreach($data['settingstable'] as $kr=>$vr)
		{
			if($kr == 'plansmedi_settings' || $kr == 'profile_settings')
			{
				$data[$kr] = $vr;
				continue;
			}
			foreach($vr as $key=>$val)
			{
				$data[$key] = $val;
			}
		}
		
		unset($data['settingstable']);
		unset($data['block_name']);
		unset($data['formular']);
		//var_dump($data);exit;
		$entity = $table::getInstance()->findOrCreateOneBy(['id'], [$data['id']], $data);
	
		return $entity;
	}
	
	public function create_form_userselectreceiptprintsettings( $options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('DtDdWrapper');
		$subform->addDecorator('HtmlTag', array('tag' => 'table' , 'class' => ''));
		$subform->setLegend($this->translate('Receipt print profiles'));
		$subform->setAttrib("class", "receipt_print_profile");
		
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		if(!empty($this->_client_receipt_profiles)){
		    
    		$subform->addElement('multiCheckbox', 'receipt_print_settings', array(
    				'label'      => null,
    				'required'   => false,
    				'multiOptions' => $this->_client_receipt_profiles,
    				'value' => $options,
    				'separator'  => '&nbsp;',
    				'decorators' => array(
    						'ViewHelper',
    						array('Errors'),
    						array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style' => "width:100%; display:block")),
    						array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    				),
    				'class' => 'multicheckbox_box',//ISPC-2754, Elena, 27.01.2021
    				'label_style' => 'display: block; line-height: 18px; float: left; margin-right: 5px;width: 300px;'
    		));
		}
		
		
		return $this->filter_by_block_name($subform , __FUNCTION__);
	}
	
}