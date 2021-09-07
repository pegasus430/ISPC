<?php
/**
 * 
 * @author carmen
 * 
 * 17.01.2019
 *
 */
class Application_Form_PatientTreatmentPlan extends Pms_Form
{
	protected $_cf_block_values = null;
	
	protected $_cf_users = null;
	
	protected $_cl_users = null;
	
	protected $title = null;
	
	protected $_sp_index = null;
	
	protected $title_pdf_1 = null;
	
	protected $title_pdf_2 = null;
	
	public function __construct($options = null)
	{
		parent::__construct($options);
		
		$this->title = '<h2> + Behandlungsplan</h2>';
		$this->title_pdf_1 = 'Behandlungsplan';
		$this->title_pdf_2 = 'Palliativmedizinischer Dienst (PMD)';
	
		if (isset($options['_cf_block_values'])) {
			$this->_cf_block_values = $options['_cf_block_values'];
			unset($options['_cf_block_values']);
		}
		//print_r($this->_cf_block_values); exit;
		if (isset($options['_cf_users'])) {
			$this->_cf_users = $options['_cf_users'];
			unset($options['_cf_users']);
		}
		
		if (isset($options['_cl_users'])) {
			$this->_cl_users = $options['_cl_users'];
			unset($options['_cl_users']);
		}
		
		$this->_sp_index = 0;
	
	}
	
	public function isValid($data)
	{
			
		return parent::isValid($data);
	}
	
	public function create_view_form_treatmentplan( $options = array(), $elementsBelongTo = null)
	{
			
		$this->clearDecorators();
		$this->addDecorator('FormElements');
		//$this->addDecorator('Fieldset', array());
		$this->addDecorator('Form');
		$values = $this->_cf_block_values;
		$values['formular_type'] = 'view';
		
		$this->addElement('hidden', 'form_id', array(
				'value' => $values['form_id'],
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
		$this->addElement('note', 'label_form_title_1', array(
				'value' => $this->title_pdf_1,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'title_bold'),
						),
						array('HtmlTag', array('tag'=>'div', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'id'=>'treatment_plan'))
				),
		));
		
		$this->addElement('note', 'label_form_title_2', array(
				'value' => $this->title_pdf_2,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'title'),
						),
				),
		));
		
		$this->addElement('note', 'label_form_title', array(
				'value' => $this->title,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class'=>'contactform_dragvbox'),
						),
				),
		));
		//form_block_treatmentplan - values from the last cf
		$cfbt = new Application_Form_FormBlockTreatmentPlan();		
		$cfbt_form = $cfbt->create_form_formblocktreatmentplan($values);
		
		$cfbt_form->removeDecorator('Fieldset');
		$cfbt_form->addDecorator('Fieldset', array('legend' => '', 'style' => 'border: 0px; padding: 0px; font-size: 12px;'));		
		$cfbt_form->addDecorators(array(
				array('HtmlTag',array('tag'=>'div',   'class'=>'contactform_dragvbox_content')),
				array(array('fulldiv' => 'HtmlTag'), array(
						'tag' => 'div',
						'closeOnly' => true,
						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'contactform_dragvbox'),
				),
		));
		
		$this->addSubForm($cfbt_form, 'treatment_plan');
		
		//form_block_additionalusers
		$cfbu = new Application_Form_FormBlockAdditionalUsers(array(
				'_cl_users' => $this->_cl_users,
				));
		$cfbu_form = $cfbu->create_form_formblockadditionalusers($values, 'additional_users');
		/* $cfbu_form->removeDecorator('Fieldset');
		$cfbu_form->addDecorator('Fieldset', array('legend' => '', 'style' => 'border: 0px; padding: 0px; font-size: 12px;'));
		$cfbu_form->addDecorators(array(
				array('HtmlTag',array('tag'=>'div',   'class'=>'contactform_dragvbox_content')),
				array(array('fulldiv' => 'HtmlTag'), array(
						'tag' => 'div',
						'closeOnly' => true,
						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class'=>'contactform_dragvbox'),
				),
		)); */
		
		$this->addSubForm($cfbu_form, 'additional_users');
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'style'=>'width: 100%; padding-top: 10px;'))
		));
		$this->addSubform($form_details, 'sep'.$this->_sp_index);
		$this->_sp_index++;
		
		$this->addElement('note', 'label_agreed_with', array(
				'value' => $this->translate('label_agreed_with'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array('halfdiv' => 'HtmlTag', array('tag'=>'div','style'=>'width: 30%; float: left;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'style'=>'width: 100%; font-size: 14px;'),
						),
				),
		));
		
		$this->addElement('text', 'agreed_with', array(
				'belongsTo' => 'treatment_plan',
				'value'        => $values['agreed_with']['value'] ? $values['agreed_with']['value'] : '',
				'required'     => false,
				//'filters'      => array('StringTrim'),
				// 		    'validators'   => array('NotEmpty')
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('halfdiv' => 'HtmlTag'), array('tag' => 'div', 'style'=>'width: 25%; float: left;')),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', 'style'=>'clear: both', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, 'style'=>'width: 100%;'),
						),
				),
				'style' => 'width: 100%;'
				//'cols' => '10'
		));
		
		//extrafields form
		$form_details = $this->_create_formular_extrafields($values, 'treatment_plan');
		$this->addSubform($form_details, 'extra_form');
		
		/*$this->addElement('note', 'label_print_save', array(
				'value' => $this->translate('label_print_save'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array('tagdiv' => 'HtmlTag', array('tag'=>'div','style'=>'width: 30%; float: left; font-size: 14px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'style'=>'width: 100%;'),
						),
				),
		));*/
		//add action buttons
		$actions = $this->_create_formular_actions($options['formular'] , 'formular');
		/*$actions->addDecorators(array(
				array('tagdiv' => 'HtmlTag', array('tag'=>'div', 'style'=>'width: 70%; float: left;')),
				array(array('cleartag' => 'HtmlTag'), array(
						'tag' => 'div', 'style'=>'clear: both', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				array(array('fulldiv' => 'HtmlTag'), array(
						'tag' => 'div',
						'closeOnly' => true,
						'placement' => Zend_Form_Decorator_Abstract::APPEND, 'style' => 'width: 100%;'),
				),
		));*/
		
		$this->addSubform($actions, 'form_actions');
		
		return $this;
	}
	
	public function create_pdf_form_treatmentplan( $options = array(), $elementsBelongTo = null)
	{			
		$subform = new Zend_Form_SubForm();
		//$subform->setElementsBelongTo("treatment_plan");
		$subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				array('HtmlTag',array('tag'=>'div', 'id' => 'treatment_plan')),
		));
		//$options['formular_type'] = 'pdf';
		foreach($this->_cl_users as $kr => $vr)
		{
			$cl_users[$vr['id']] = $vr;
		}
		
		foreach($options as $kr=>$vr)
		{
			if($kr != 'additional_users')
			{
				foreach($vr as $kc=>$vc)
				{
					$values[$kr]['colprop']['values'][] = $kc;
				}
			$values[$kr]['value'] = $vr;
			}
			else 
			{
				foreach($vr as $kc=>$vc)
				{
					if(count($vc) > 1)
					{
						$vc['nice_name'] = $cl_users[$vc[0]]['nice_name'];
						$values[$kr]['value'][] = $vc;
					}
				}
			}
		}
		$values['formular_type'] = 'pdf';
		// print_r($values); exit;
		$subform->addElement('note', 'label_form_title_1', array(
				'value' => $this->title_pdf_1,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'title_bold'),
						),
				),
		));
		
		$subform->addElement('note', 'label_form_title_2', array(
				'value' => $this->title_pdf_2,
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'title'),
						),
				),
		));
		
		$form_details = $this->_create_form_details_sepform($options, $elementsBelongTo);
		$form_details->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'style'=>'width: 100%; padding-top: 10px; border-top: 2px solid #000;'))
		));
		$subform->addSubform($form_details, 'sep'.$this->_sp_index);
		$this->_sp_index++;
		
		//header form
		$form_details = $this->_create_formular_header($values, 'treatment_plan');
		$subform->addSubform($form_details, 'header');
		
		//extrafields form
		$form_details = $this->_create_formular_extrafields($values, 'treatment_plan');
		$subform->addSubform($form_details, 'extra_form');
		
		//form_block_treatmentplan - values from the last cf
		$cfbt = new Application_Form_FormBlockTreatmentPlan();
		
		$cfbt_form = $cfbt->create_form_formblocktreatmentplan($values);
		$cfbt_form->removeDecorator('Fieldset');
		$cfbt_form->addDecorator('Fieldset', array('legend' => '', 'style' => 'border: 0px; padding: 0px; font-size: 12px; font-weight: bold;'));
		$cfbt_form->addDecorators(array(
				array('HtmlTag', array('tag'=>'div'))));
		
		$subform->addSubForm($cfbt_form, 'treatment_plan');
	
		return $subform;
	}
	
	public function _create_formular_header($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		//$subform->setElementsBelongTo("treatment_plan");
		$subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				array('HtmlTag',array('tag'=>'div', 'class' => 'formular_header')),
		));
	
		$this->__setElementsBelongTo($subform, $elementsBelongTo);
		
		$subform->addElement('note', 'label_name', array(
				'value' => $this->_patientMasterData['last_name'] . " " . $this->_patientMasterData['first_name'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv')),
						array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'class' => 'innerdiv div30 fullbord', 'style' => 'padding: 15px 15px 15px 5px;', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND)),
				),
		));
		
		$subform->addElement('note', 'label_birthd', array(
				'value' => $this->_patientMasterData['birthd'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv'),
						),
				),
		));
		
		$subform->addElement('note', 'label_street', array(
				'value' => $this->_patientMasterData['street1'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv', 'style' => 'padding-top: 10px; font-weight: normal;'),
						),
				),
		));
		
		$subform->addElement('note', 'label_zip_city', array(
				'value' => $this->_patientMasterData['zip'] . " " . $this->_patientMasterData['city'],
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'style' => 'fulleldiv', 'style' => 'padding-top: 10px; font-weight: normal;'),
						),
						array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'closeOnly' => true,
							'placement' => Zend_Form_Decorator_Abstract::APPEND, 'class' => 'innerdiv div30 fullbord')),
				),
						
				
		));
		
		$subform->addElement('note', 'label_users', array(
				'value' => $this->translate('label_users'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv', 'style' => 'padding-bottom: 23px;')),
						array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class' => 'innerdiv div30', 'style' => 'padding: 0px 15px 0px 15px')),
				),
		));
		
		/*$subform->addElement('note', 'label_user_1', array(
				//'value' => $this->_cf_users['0'] ? $this->_cf_users['0'] : '&nbsp;',
				'value' => $options['additional_users']['value'][0]['nice_name'] ? $options['additional_users']['value'][0]['nice_name'] : '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
						),
				),
		));
		
		$subform->addElement('note', 'label_user_2', array(
				//'value' => $this->_cf_users['1'] ? $this->_cf_users['1'] : '&nbsp;',
				'value' => $options['additional_users']['value'][1]['nice_name'] ? $options['additional_users']['value'][1]['nice_name'] : '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
						),
				),
		));
		
		$subform->addElement('note', 'label_user_3', array(
				//'value' => $this->_cf_users['2'] ? $this->_cf_users['2'] : '&nbsp;',
				'value' => $options['additional_users']['value'][2]['nice_name'] ? $options['additional_users']['value'][2]['nice_name'] : '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
						),
						array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
		
		
		));*/
		
		$last_user = end($options['additional_users']['value']);
		if(!empty($options['additional_users']['value']))
		{
			$user_lines = count($options['additional_users']['value']);
			if($user_lines >=3)
			{
		foreach($options['additional_users']['value'] as $kr=>$vr)
		{
			if($last_user != $vr)
			{
				$subform->addElement('note', 'label_user_'.$kr, array(
				//'value' => $this->_cf_users['1'] ? $this->_cf_users['1'] : '&nbsp;',
				'value' => $vr['nice_name'] ? $vr['nice_name'] : '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
						),
				),
			));
			}
			else
			{
			$subform->addElement('note', 'label_user_'.$kr, array(
					//'value' => $this->_cf_users['2'] ? $this->_cf_users['2'] : '&nbsp;',
					'value' => $vr['nice_name'] ? $vr['nice_name'] : '&nbsp;',
					'decorators' => array(
							'ViewHelper',
							array('Errors'),
							array(array('fulleldiv' => 'HtmlTag'), array(
									'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
							),
							array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'closeOnly' => true,
									'placement' => Zend_Form_Decorator_Abstract::APPEND)),
					),
			
			
			));
			}
		}
		}
		else 
		{
			$user_lines_to_add = 3 - $user_lines;
		foreach($options['additional_users']['value'] as $kr=>$vr)
		{
			
				$subform->addElement('note', 'label_user_'.$kr, array(
				//'value' => $this->_cf_users['1'] ? $this->_cf_users['1'] : '&nbsp;',
				'value' => $vr['nice_name'] ? $vr['nice_name'] : '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
						),
				),
			));
		}
			
			for ($user_line = 1; $user_line <= $user_lines_to_add; $user_line++)
			{
				if($user_line != $user_lines_to_add)
				{
					$subform->addElement('note', 'label_user_extra_'.$user_line, array(
							//'value' => $this->_cf_users['1'] ? $this->_cf_users['1'] : '&nbsp;',
							'value' => '&nbsp;',
							'decorators' => array(
									'ViewHelper',
									array('Errors'),
									array(array('fulleldiv' => 'HtmlTag'), array(
											'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
									),
							),
					));
				}
				else
				{
					$subform->addElement('note', 'label_user_extra'.$user_line, array(
							//'value' => $this->_cf_users['2'] ? $this->_cf_users['2'] : '&nbsp;',
							'value' => '&nbsp;',
							'decorators' => array(
									'ViewHelper',
									array('Errors'),
									array(array('fulleldiv' => 'HtmlTag'), array(
											'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
									),
									array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'closeOnly' => true,
											'placement' => Zend_Form_Decorator_Abstract::APPEND)),
							),
								
								
					));
				}
					
			}
		}
		}
		else 
		{
			$users_lines = 3;
			for ($user_line = 1; $user_line <= $users_lines; $user_line++)
			{
			if($user_line < 3)
			{
				$subform->addElement('note', 'label_user_'.$user_line, array(
				//'value' => $this->_cf_users['1'] ? $this->_cf_users['1'] : '&nbsp;',
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
						),
				),
			));
			}
			else
			{
			$subform->addElement('note', 'label_user_'.$user_line, array(
					//'value' => $this->_cf_users['2'] ? $this->_cf_users['2'] : '&nbsp;',
					'value' => '&nbsp;',
					'decorators' => array(
							'ViewHelper',
							array('Errors'),
							array(array('fulleldiv' => 'HtmlTag'), array(
									'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' => 'padding: 10px 0px 5px 0px;'),
							),
							array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'closeOnly' => true,
									'placement' => Zend_Form_Decorator_Abstract::APPEND)),
					),
			
			
			));
			}
					
			}
		}
		
		$subform->addElement('note', 'label_agreed_with', array(
				'value' =>  $this->translate('label_agreed_with'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv', 'style' => 'padding-bottom: 23px;')),
						array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, 'class' => 'innerdiv div25', 'style' => 'padding: 0px 15px 0px 15px;')),
				),
		));
		
		$subform->addElement('note', 'agreed_with', array(
				'value' => $options['agreed_with']['value'] ? $options['agreed_with']['value'] : '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv bottbord', 'style' =>'padding: 10px 0px 5px 0px;'),
						),
				),
		));
		
		$subform->addElement('note', 'label_empty', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('fulleldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'fulleldiv', 'style' =>'padding: 10px 0px 5px 0px;'),
						),
				),
		));
		
		$subform->addElement('note', 'label_formular_date', array(
				'value' => $this->translate('formular_date'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('halfeldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'innerdiv div48', 'style' =>'padding: 10px 0px 0px 0px;')),
				),
		));
		
		$subform->addElement('note', 'formular_date', array(
				'value' => date('d.m.Y'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('halfeldiv' => 'HtmlTag'), array(
								'tag' => 'div', 'class' => 'innerdiv div48 bottbord', 'style' =>'padding: 15px 0px 0px 0px;'),
						),
						array(array('innerdiv' => 'HtmlTag'), array('tag'=>'div', 'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
		
				//$values = $options;
		));
		
		
		return $subform;
		
	}
	
	private function _create_formular_extrafields($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
    	//$subform->setElementsBelongTo("treatment_plan");
    	$subform->clearDecorators()
    		->setDecorators( array(
    				'FormElements',
    				array('HtmlTag',array('tag'=>'div', 'class' => 'extrafields')),
    		));
    	
    	$this->__setElementsBelongTo($subform, $elementsBelongTo);
    	
    	$subform->addElement('note', 'label_history_since_last_meeting', array(
    			'value' => $this->translate('label_history_since_last_meeting'),
    			'decorators' => array(
    					'ViewHelper',
    					array('Errors'),
    					array('fulleldiv' => 'HtmlTag', array('tag'=>'div', 'class'=> 'fulleldiv')),
    			),
    	));
    	
    	if($options['formular_type'] != 'pdf')
    	{
    	$subform->addElement('textarea', 'history_since_last_meeting', array(
    			//'belongsTo' => 'treatment_plan',
    			'value'        => $options['history_since_last_meeting']['value'] ? $options['history_since_last_meeting']['value'] : '',
    			'required'     => false,
    			//'filters'      => array('StringTrim'),
    			// 		    'validators'   => array('NotEmpty')
    			'decorators' => array(
    					'ViewHelper',
    					array('Errors'),
    					array(array('fulleldiv' => 'HtmlTag'), array('tag' => 'div', 'class'=> 'fulleldiv extra')),
    			),
    			'rows' => '3',
    			//'cols' => '10'
    	));
    	}
    	else 
    	{
    		$subform->addElement('note', 'history_since_last_meeting', array(
    				'value' => $options['history_since_last_meeting']['value'] ? nl2br($options['history_since_last_meeting']['value']) : '&nbsp;',
    				'decorators' => array(
    						'ViewHelper',
    						array('Errors'),
    						array(array('fulleldiv' => 'HtmlTag'), array(
    								'tag' => 'div', 'class' => 'fulleldiv extra'),
    						),
    				),
    		));
    	}
    	    	
    	$subform->addElement('note', 'label_main_problems', array(
    			'value' => $this->translate('label_main_problems'),
    			'decorators' => array(
    					'ViewHelper',
    					array('Errors'),
    					array('fulleldiv' => 'HtmlTag', array('tag'=>'div', 'class' => 'fulleldiv')),
    			),
    	));
    	
    	if($options['formular_type'] != 'pdf')
    	{
    		$subform->addElement('textarea', 'main_problems', array(
    			//'belongsTo' => 'treatment_plan',
    			'value'        => $options['main_problems']['value'] ? $options['main_problems']['value'] : '',
    			'required'     => false,
    			//'filters'      => array('StringTrim'),
    			// 		    'validators'   => array('NotEmpty')
    			'decorators' => array(
    					'ViewHelper',
    					array('Errors'),
    					array(array('fulleldiv' => 'HtmlTag'), array('tag' => 'div', 'class'=> 'fulleldiv extra')),
    			),
    			'rows' => '4',
    			//'cols' => '10'
    	));
    	}
    	else
    	{
    		$subform->addElement('note', 'main_problems', array(
    				'value' => $options['main_problems']['value'] ? nl2br($options['main_problems']['value']) : '&nbsp;',
    				'decorators' => array(
    						'ViewHelper',
    						array('Errors'),
    						array(array('fulleldiv' => 'HtmlTag'), array(
    								'tag' => 'div', 'class' => 'fulleldiv extra'),
    						),
    				),
    		));
    		
    	}
    	
    	return $subform;
		
		
	}
	
	private function _create_form_details_sepform($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
	
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
				'value'        => 'save',
				// 	        'content'      => $this->translate('submit'),
				'label'        => $this->translator->translate('kh_save'),
				// 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
				'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
				'decorators'   => array('ViewHelper',
						//array('tagdiv' => 'HtmlTag', array('tag'=>'div', 'style'=>'width: 100%;')),
				),
		
		));
		$subform->addElement($el, 'save');
			
		$el = $this->createElement('button', 'button_action', array(
		 'type'         => 'submit',
		 'value'        => 'printpdf',
		 // 	        'content'      => $this->translate('submit'),
		 'label'        => $this->translator->translate('kh_generate_pdf'),
		 // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
		 'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
		 'decorators'   => array('ViewHelper',
		 				  // array('tagdiv' => 'HtmlTag', array('tag'=>'div', 'style'=>'width: 100%;')),
		 ),
	
			));
		$subform->addElement($el, 'printpdf');		
			
			
		return $subform;
	
	}
	
	public function save_form_treatmentplan($ipid = null, array $data = array())
	{
		if (empty($ipid)) {
			throw new Exception('Contact Admin, formular cannot be saved.', 0);
		}
		//print_r($data); exit;
		if($data['form_id'] == '')
		{
			$data['form_id'] = null;
		}
		
		$entity = PatientTreatmentPlanTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['form_id'], $ipid], $data);
	
		return $entity;
	}
	
	
}