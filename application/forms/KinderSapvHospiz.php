<?php
/**
 * 
 * @author carmen
 * 
 * 17.08.2018
 *
 */
class Application_Form_KinderSapvHospiz extends Pms_Form
{
	protected $_phealthinsurance = null;
	
	//protected $_page_lang = null;
	
	protected $_cl_index = null;
	
	protected $title_center = null;
	
	protected $title_left = null;
	
	protected $top_left = null;
	
	protected $top_right = null;
	
	protected $title_pag2 = null;
	
	protected $top_right_pag2 = null;
	
	protected $_phospiceassoc = null;
	
	protected $final_left = null;
	
	public function __construct($options = null)
	{
		if (isset($options['_phealthinsurance'])) {
			$this->_phealthinsurance = $options['_phealthinsurance'];
			unset($options['_phealthinsurance']);
		}
		
		if (isset($options['_phospiceassoc'])) {
			$this->_phospiceassoc = $options['_phospiceassoc'];
			unset($options['_phospiceassoc']);
		}
	
		parent::__construct($options);
	
		//$this->_page_lang = $this->translate ( 'careregulationnew_lang' );

		$this->_cl_index = 0;
		//print_r($this->_patientMasterData); exit;
		
		$this->title_center = '<h2>Ärztliche Bescheinigung zur' .
							'<font style=" text-decoration: underline;">&nbsp;Hospizversorgung gemäß § 39 a SGB V</font>' . 
							'&nbsp;im Kinderhospiz</h2>';
		$this->title_left = '<b>Diese Bescheinigung ist nicht erforderlich für Aufenthalte, die ausschließlich Leistungen der
							Pflegeversicherung erfordern.</b>';
		$this->top_left = '<h2>Anlage 1</h2>';
		$this->top_right = '(Vorderseite)';
		
		$this->title_pag2 = '<h2>Antrag auf vollstationäre Hospiz- und Pflegeleistungen nach</h2>'.
				'<h2>§ 39 a Abs. 1 SGB V und §§ 42 und 43 SGB XI</h2>';
		$this->top_right_pag2 = '(Rückseite)';
		$this->final_left = 'Stand: 12.12.2006';
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
		 
	}
	
	public function create_form_kindersapvhospiz( $options = array(), $elementsBelongTo = null)
	{
		 
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		//$this->addDecorator('Fieldset', array());
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
		
		//print_r($this->_phealthinsurance); exit;
		$options['header'] = array(
				'col' => '3',
				'class' => 'headertable',
				"editable" => false,
				"tableheader" => array(
						array(
								"name" => $this->translator->translate('kh_healthinsurance_address'),
								"value" => $this->translator->translate('kh_healthinsurance_address')
						),
						array(
								"name" => $this->translator->translate('kh_patient_surname'),
								"value" => $this->translator->translate('kh_patient_surname')
						),
						array(
								"name" => "patient_surname",
								"value" => $this->_patientMasterData['last_name']
						)
				),
				"data" => array(
						array(
								array(
										"type" => "note",
										"name" => "phealthinsurance_name",
										"value" => $this->_phealthinsurance['company_name'].'<br />'.$this->_phealthinsurance['zip'] . " " . $this->_phealthinsurance['city']
								),
								array(
										"type" => "note",
										"name" => $this->translator->translate('kh_patient_firstname'),
										"value" => $this->translator->translate('kh_patient_firstname')
								),
								array(
										"type" => "note",
										"name" => "patient_firstname",
										"value" => $this->_patientMasterData['first_name']
								)
						),
						array(
								array(
										"type" => "note",
										"name" => $this->translator->translate('kh_patient_dob'),
										"value" => $this->translator->translate('kh_patient_dob')
								),
								array(
										"type" => "note",
										"name" => "patient_birthd",
										"value" =>$this->_patientMasterData['birthd']
								)
						),
						array(
								array(
										"type" => "note",
										"name" => $this->translator->translate('kh_patient_street'),
										"value" => $this->translator->translate('kh_patient_street')
								),
								array(
										"type" => "note",
										"name" => "patient_street",
										"value" =>$this->_patientMasterData['street1']
								)
						),
						array(
								array(
										"type" => "note",
										"name" => $this->translator->translate('kh_patient_zipcity'),
										"value" => $this->translator->translate('kh_patient_zipcity')
								),
								array(
										"type" => "note",
										"name" => "patient_zipcity",
										"value" =>($this->_patientMasterData['zip'] ? $this->_patientMasterData['zip'] . " " . $this->_patientMasterData['city'] : ($this->_patientMasterData['city'] ? $this->_patientMasterData['city'] : ""))
								)
						),
						array(
								array(
										"type" => "note",
										"name" => $this->translator->translate('kh_patient_kvnr'),
										"value" => $this->translator->translate('kh_patient_kvnr')
											
								),
								array(
										"type" => "note",
										"name" => "patient_kvnr",
										"value" =>$this->_phealthinsurance['kvnumber']
								)
						)
				)
		);
		
		//the page1
		$form_details = $this->_create_form_details_page1($options, $elementsBelongTo);
		$this->addSubform($form_details, 'page1');
	
		//the page2
		$form_details = $this->_create_form_details_page2($options, $elementsBelongTo);
		$this->addSubform($form_details, 'page2');
		
		//add action buttons
		$actions = $this->_create_formular_actions($options['formular'] , 'formular');
		$this->addSubform($actions, 'form_actions');
	
		return $this;
	
	
	}
	
	private function _create_form_details_page1($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'page1')),
		));
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		//print_r($options); exit;
		$subform->addElement('note', 'label_form_left', array(
				'value' => $this->top_left,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'span', //'class'=>'halfdiv',
						'style' => 'width: 49%; display: inline-block; line-height: 20px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, //'class'=>'fulldiv',
						'style' => 'width: 100%; float: left;'),
						),
				),
		));
		
		$subform->addElement('note', 'label_form_right', array(
				'value' => $this->top_right,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'span', //'class'=>'halfdiv right',
						'style' => 'width: 49%; text-align: right; display: inline-block; line-height: 20px;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, //'class'=>'fulldiv',
								'style' => 'width: 100%; float: left;'),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', //'class'=>'clear',
								'style' => 'clear: both;',
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
		));
		
		$subform->addElement('note', 'label_form_title_center', array(
				'value' => $this->title_center,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
						'style' => 'width: 100%; text-align: center; line-height: 20px;'))
				),
		));
		
		$subform->addElement('note', 'label_form_title_left', array(
				'value' => $this->title_left,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv',
						'style' => 'width: 100%; line-height: 20px;'))
				),
		));		
		
		$header = $this->_create_form_table($options['header'], $elementsBelongTo = null);
		$subform->addSubForm($header, 'header_details');
		
		//first table on page1
		$col_page1 = array(array('appl_order'),
							array('other'),
							array('required_hospiz_treatment_justification'),
							array('required_hospiz_regist_current_trend_change'),
							array('home_palliative_treatment_plan'),
							array('home_current_therapies'));
		$col_page1_attr = array(array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_afer' => false)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => false)));
		
		$options_first = array();
		$options_first['class'] = 'first';
		$options_first['col'] = 1;
		$options_first['editable'] = true;
		//$options_first['id'] = $options['id']['value'];
		//print_r($options); exit;
		$firstrow = true;
		foreach($col_page1 as $krow=>$vrow)
		{
			foreach($vrow as $kcol=>$vcol)
			{
				if($vcol == 'other')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol.'1';
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol.'1');
				}
			
				if(array_key_exists($vcol, $options))
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol;
					$options_first['data'][$krow][$kcol]['type'] = $options[$vcol]['colprop']['type'];
					if($options[$vcol]['colprop']['type'] == 'enum' || $col_page1_attr[$krow][$kcol]['view_type'] == 'object')
					{
						$options_first['data'][$krow][$kcol]['values'] = $options[$vcol]['colprop']['values'];
						$options_first['data'][$krow][$kcol]['escape'] = false;
						switch ($vcol)
						{
							case 'appl_order':
								$options_first['data'][$krow][$kcol]['separator'] = 80;
								$options_first['data'][$krow][$kcol]['names'] = array($options[$vcol]['colprop']['values'][0] => '<span class="checkmark"></span><b style="font-size: 14px;">Erstantrag</b>', $options[$vcol]['colprop']['values'][1] => '<span class="checkmark"></span><b style="font-size: 14px;">Folgeantrag</b>');
								break;
							default:
								$options_first['data'][$krow][$kcol]['separator'] = 30;
								$options_first['data'][$krow][$kcol]['names'] = array($options[$vcol]['colprop']['values'][1] => '<span class="checkmark"></span>ja', $options[$vcol]['colprop']['values'][0] => '<span class="checkmark"></span>nein');
								break;
						}
						$options_first['data'][$krow][$kcol]['value'] = $options[$vcol]['value'];
					}
					else if($options[$vcol]['colprop']['type'] == 'timestamp')
					{
						switch ($vcol)
						{
							case 'form_date':
								$options_first['data'][$krow][$kcol]['value'] = (($options[$vcol]['value'] != '0000-00-00 00:00:00' && $options[$vcol]['value'] !== null) ? date('d.m.Y', strtotime($options[$vcol]['value'])) : date('d.m.Y'));
							break;
							default:
								$options_first['data'][$krow][$kcol]['value'] = (($options[$vcol]['value'] != '0000-00-00 00:00:00' && $options[$vcol]['value'] !== null) ? date('d.m.Y', strtotime($options[$vcol]['value'])) : '');
							break;
						}
					}
					else 
					{
                    	$options_first['data'][$krow][$kcol]['value'] = $options[$vcol]['value'];
					}
				}
				
				if($col_page1_attr[$krow][$kcol] != "")
				{
					foreach($col_page1_attr[$krow][$kcol] as $kcolat=>$vcolat)
					{
                    	$options_first['data'][$krow][$kcol][$kcolat] = $vcolat;
					}
				}
			}				
		
		}
		
		
		//print_r($options_first); exit;
		
		$subform1 = new Zend_Form_SubForm();
		//$subform->removeDecorator('Fieldset');
		$subform1->setDecorators( array(
				'FormElements',
				array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable' ." ". $options_first['class'])),
				//'Fieldset',
		));
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		foreach($options_first['data'] as $krow=>$vrow)
		{
			$rowsubform_label = $this->subFormTableRow();
			$rowsubform_data = $this->subFormTableRow();
			foreach($vrow as $kdata=>$vdata)
			{
				//var_dump($vdata);
				$elementDecorators = array();
				$i = 0;
				$elementDecorators[$i] = 'ViewHelper';
				$i++;
				$j = 0;
				$elementDecorators[$i][$j]['data'] = 'HtmlTag';
				$j++;
					
				$elementDecorators[$i][$j]['tag'] = 'td';
					
				$elementDecorators[$i][$j]['class'] = $vdata['class'];
				if(!$vdata['label_after'])
				{
					if(!$vdata['no_label'])
					{
						if (!$vdata['label_on_same_row'])
						{
							$elementDecorators[$i][$j]['colspan'] = '2';
								
							$rowsubform_label->addElement('note', 'label_'.$vdata['name'], array(
									'value' => $this->translate('label_'.$vdata['name']),
									'decorators' => $elementDecorators
							));
						}
						else
						{
				
							//$elementDecorators[$i][$j]['width'] = '65%';
							$elementDecorators[$i][$j]['colspan'] = '2';
							$rowsubform_label->addElement('note', 'label_20'.$vdata['name'], array(
									'value' => "",
									'decorators' => $elementDecorators
							));
							$elementDecorators[$i][$j]['colspan'] = null;
							$rowsubform_data->addElement('note', 'label_'.$vdata['name'], array(
									'value' => $this->translate('label_'.$vdata['name']),
									'decorators' => $elementDecorators
							));
								
								
						}
					}
					else
					{
						$elementDecorators[$i][$j]['colspan'] = '2';
						$rowsubform_label->addElement('note', 'label_10'.$vdata['name'], array(
								'value' => '',
								'decorators' => $elementDecorators
						));
					}
				
						
					switch ($vdata['type'])
					{
						case 'string':
						case 'integer':
							if($vdata['name'] == 'stufe_text')
							{
								$elementDecorators[$i][$j]['style'] = 'border-bottom: 1px solid #000';
								$label = 'Stufe';
							}
							 
							$rowsubform_data->addElement('text', $vdata['name'], array(
									'value'        => $vdata['value'],
									'required'     => false,
									'filters'      => array('StringTrim'),
									'label' => $label,
									'class' => 'kh_stufe',
									// 		    'validators'   => array('NotEmpty'),
									'decorators' => $elementDecorators
							));
							$elementDecorators[$i][$j]['style'] = 'none';
							break;
						case 'text':
				
							$rowsubform_data->addElement('textarea', $vdata['name'], array(
							'value'        => $vdata['value'],
							'required'     => false,
							'rows' => "2",
							'filters'      => array('StringTrim'),
							// 		    'validators'   => array('NotEmpty'),
							'decorators' => $elementDecorators
							));
							break;
						case 'enum':
				
							//var_dump($elementDecorators); exit;
							//$elementDecorators[$i][$j]['colspan'] = '2';
							$rowsubform_data->addElement('radio', $vdata['name'], array(
									'label' => null,
									'required'   => false,
									'multiOptions'=>$vdata['names'],
									'value' => $vdata['value'],
									'decorators' => $elementDecorators,
									'escape'     => $vdata['escape'],
									'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
				
							));
				
							break;
						case 'object':
				
							$rowsubform_data->addElement('multiCheckbox', $vdata['name'], array(
							'label'      => null,
							'required'   => false,
							'multiOptions' => $vdata['names'],
							'value' => $vdata['value'],
							'class' => ($vdata['onevalue'] ? 'onevalue' : ''),
							'decorators' => $elementDecorators,
							'escape'     => $vdata['escape'],
							'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
				
							));
							//var_dump($elementDecorators); exit;
							break;
						case 'timestamp':
				
							$rowsubform_data->addElement('text', $vdata['name'], array(
							'value'        => $vdata['value'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							// 		    'validators'   => array('NotEmpty'),
							'decorators' => $elementDecorators,
							'class' => "kh_date"
									));
							break;
				
						default:
							$elementDecorators[$i][$j]['colspan'] = '2';
							$rowsubform_data->addElement('note', $vdata['name'], array(
									'value' => $vdata['value'],
									'decorators' => $elementDecorators
							));
							break;
					}
				}
				
				
			}
			$subform1->addSubForm($rowsubform_label, $krow.'label');
			$subform1->addSubForm($rowsubform_data, $krow.'data');
			if($firstrow)
			{
				$rowsubform_label->addDecorator(array('head' => 'HtmlTag'), array('tag' => 'tbody', 'openOnly' => true));
				$firstrow = false;
			}
		}
		
		$subform->addSubform($subform1, 'first_details');
		
		/*$first = $this->_create_form_table($options_first, $elementsBelongTo = null);
		$subform->addSubForm($first, 'first_details');*/
		
		//second table on page1
		$col_page1 = array(array('other'),
							array('postother'),
							array('pain_therapy_already_started'),
							array('pain_therapy_expected'),
							array('presymptom'),
							array('symptom_control_crisis_intervention'),
							array('postsymptom'),
							array('psychosocial_or_pastoral_support'),
							array('special_wound_care'),
							array('signs_of_infectious_diseases'),
							array('other_palliative_needs'),
							array('form_date'));
		$col_page1_attr = array(array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => true)));
		
		$options_first = array();
		$options_first['class'] = 'second';
		$options_first['col'] = 1;
		$options_first['editable'] = true;
		//$options_first['id'] = $options['id']['value'];
		//print_r($options); exit;
		foreach($col_page1 as $krow=>$vrow)
		{
			foreach($vrow as $kcol=>$vcol)
			{
				if($vcol == 'other')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol.'2';
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol.'2');
				}
				
				if($vcol == 'postother')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol;
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol);
				}
				
				if($vcol == 'presymptom')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol;
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol);
				}
				
				if($vcol == 'postsymptom')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol.'2';
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol);
				}
			
				if(array_key_exists($vcol, $options))
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol;
					$options_first['data'][$krow][$kcol]['type'] = $options[$vcol]['colprop']['type'];
					if($options[$vcol]['colprop']['type'] == 'enum' || $col_page1_attr[$krow][$kcol]['view_type'] == 'object')
					{
						$options_first['data'][$krow][$kcol]['values'] = $options[$vcol]['colprop']['values'];
						$options_first['data'][$krow][$kcol]['escape'] = false;
						switch ($vcol)
						{
							default:
								$options_first['data'][$krow][$kcol]['separator'] = 30;
								$options_first['data'][$krow][$kcol]['names'] = array($options[$vcol]['colprop']['values'][1] => '<span class="checkmark"></span>ja', $options[$vcol]['colprop']['values'][0] => '<span class="checkmark"></span>nein');
								break;
						}
						$options_first['data'][$krow][$kcol]['value'] = $options[$vcol]['value'];
					}
					else if($options[$vcol]['colprop']['type'] == 'timestamp')
					{
						switch ($vcol)
						{
							case 'form_date':
								$options_first['data'][$krow][$kcol]['value'] = (($options[$vcol]['value'] != '0000-00-00 00:00:00' && $options[$vcol]['value'] !== null) ? date('d.m.Y', strtotime($options[$vcol]['value'])) : date('d.m.Y'));
							break;
							default:
								$options_first['data'][$krow][$kcol]['value'] = (($options[$vcol]['value'] != '0000-00-00 00:00:00' && $options[$vcol]['value'] !== null) ? date('d.m.Y', strtotime($options[$vcol]['value'])) : '');
							break;
						}
					}
					else 
					{
                                            $options_first['data'][$krow][$kcol]['value'] = $options[$vcol]['value'];
					}
				}
					
				if($col_page1_attr[$krow][$kcol] != "")
				{
					foreach($col_page1_attr[$krow][$kcol] as $kcolat=>$vcolat)
					{
                                            $options_first['data'][$krow][$kcol][$kcolat] = $vcolat;
					}
				}
			}
		
		
		}
		
		$subform2 = new Zend_Form_SubForm();
		//$subform->removeDecorator('Fieldset');
		$subform2->setDecorators( array(
				'FormElements',
				array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable' ." ". $options_first['class'])),
				//'Fieldset',
		));
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		foreach($options_first['data'] as $krow=>$vrow)
		{
			$rowsubform_label = $this->subFormTableRow();
			$rowsubform_data = $this->subFormTableRow();
			foreach($vrow as $kdata=>$vdata)
			{
				//var_dump($vdata);
				$elementDecorators = array();
				$i = 0;
				$elementDecorators[$i] = 'ViewHelper';
				$i++;
				$j = 0;
				$elementDecorators[$i][$j]['data'] = 'HtmlTag';
				$j++;
					
				$elementDecorators[$i][$j]['tag'] = 'td';
					
				$elementDecorators[$i][$j]['class'] = $vdata['class'];
				if(!$vdata['label_after'])
				{
					if(!$vdata['no_label'])
					{
						if (!$vdata['label_on_same_row'])
						{
							$elementDecorators[$i][$j]['colspan'] = '2';
		
							$rowsubform_label->addElement('note', 'label_'.$vdata['name'], array(
									'value' => $this->translate('label_'.$vdata['name']),
									'decorators' => $elementDecorators
							));
						}
						else
						{
		
							//$elementDecorators[$i][$j]['width'] = '65%';
							$elementDecorators[$i][$j]['colspan'] = '2';
							$rowsubform_label->addElement('note', 'label_20'.$vdata['name'], array(
									'value' => "",
									'decorators' => $elementDecorators
							));
							$elementDecorators[$i][$j]['colspan'] = null;
							$rowsubform_data->addElement('note', 'label_'.$vdata['name'], array(
									'value' => $this->translate('label_'.$vdata['name']),
									'decorators' => $elementDecorators
							));
		
		
						}
					}
					else
					{
						$elementDecorators[$i][$j]['colspan'] = '2';
						$rowsubform_label->addElement('note', 'label_10'.$vdata['name'], array(
								'value' => '',
								'decorators' => $elementDecorators
						));
					}
		
		
					switch ($vdata['type'])
					{
						case 'string':
						case 'integer':
							if($vdata['name'] == 'stufe_text')
							{
								$elementDecorators[$i][$j]['style'] = 'border-bottom: 1px solid #000';
								$label = 'Stufe';
							}
		
							$rowsubform_data->addElement('text', $vdata['name'], array(
									'value'        => $vdata['value'],
									'required'     => false,
									'filters'      => array('StringTrim'),
									'label' => $label,
									'class' => 'kh_stufe',
									// 		    'validators'   => array('NotEmpty'),
									'decorators' => $elementDecorators
							));
							$elementDecorators[$i][$j]['style'] = 'none';
							break;
						case 'text':
		
							$rowsubform_data->addElement('textarea', $vdata['name'], array(
							'value'        => $vdata['value'],
							'required'     => false,
							'rows' => "2",
							'filters'      => array('StringTrim'),
							// 		    'validators'   => array('NotEmpty'),
							'decorators' => $elementDecorators
							));
							break;
						case 'enum':
		
							//var_dump($elementDecorators); exit;
							//$elementDecorators[$i][$j]['colspan'] = '2';
							$rowsubform_data->addElement('radio', $vdata['name'], array(
									'label' => null,
									'required'   => false,
									'multiOptions'=>$vdata['names'],
									'value' => $vdata['value'],
									'decorators' => $elementDecorators,
									'escape'     => $vdata['escape'],
									'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
		
							));
		
							break;
						case 'object':
		
							$rowsubform_data->addElement('multiCheckbox', $vdata['name'], array(
							'label'      => null,
							'required'   => false,
							'multiOptions' => $vdata['names'],
							'value' => $vdata['value'],
							'class' => ($vdata['onevalue'] ? 'onevalue' : ''),
							'decorators' => $elementDecorators,
							'escape'     => $vdata['escape'],
							'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
		
							));
							//var_dump($elementDecorators); exit;
							break;
						case 'timestamp':
		
							$rowsubform_data->addElement('text', $vdata['name'], array(
							'value'        => $vdata['value'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							// 		    'validators'   => array('NotEmpty'),
							'decorators' => $elementDecorators,
							'class' => "kh_date"
									));
							break;
		
						default:
							$elementDecorators[$i][$j]['colspan'] = '2';
							$rowsubform_data->addElement('note', $vdata['name'], array(
									'value' => $vdata['value'],
									'decorators' => $elementDecorators
							));
							break;
					}
				}
				elseif($vdata['label_after'])
				{
					if(!$vdata['no_label'])
					{
						if (!$vdata['label_on_same_row'])
						{
							
								$elementDecorators[$i][$j]['colspan'] = '2';
								$rowsubform_data->addElement('note', 'label_'.$vdata['name'], array(
										'value' => $this->translate('label_'.$vdata['name']),
										'decorators' => $elementDecorators
								));
								
							}
				
				
						
					}
					else
					{
						
							
							$rowsubform_label->addElement('note', 'label_'.$vdata['name'], array(
									'value' => '',
									'decorators' => $elementDecorators
							));
							
						
					}
				
					switch ($vdata['type'])
					{
						case 'string':
						case 'integer':
				
							$rowsubform_label->addElement('text', $vdata['name'], array(
							'value'        => $vdata['value'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							// 		    'validators'   => array('NotEmpty'),
							'decorators' => $elementDecorators
							));
				
							break;
						case 'text':
				
							$rowsubform_label->addElement('textarea', $vdata['name'], array(
							'value'        => $vdata['value'],
							'required'     => false,
							'rows' => "2",
							'filters'      => array('StringTrim'),
							// 		    'validators'   => array('NotEmpty'),
							'decorators' => $elementDecorators
							));
							break;
						case 'enum':
				
							$rowsubform_data->addElement('radio', $vdata['name'], array(
							'label' => null,
							'required'   => false,
							'multiOptions'=>$vdata['names'],
							'value' => $vdata['value'],
							'decorators' => $elementDecorators,
							'escape'     => $vdata['escape'],
							'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
				
							));
							break;
				
						case 'object':
				
							$rowsubform_data->addElement('multiCheckbox', $vdata['name'], array(
							'label'      => null,
							'required'   => false,
							'multiOptions' => $vdata['names'],
							'value' => $vdata['value'],
							'class' => ($vdata['onevalue'] ? 'onevalue' : ''),
							'decorators' => $elementDecorators,
							'escape'     => $vdata['escape'],
							'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
				
							));
							//var_dump($elementDecorators); exit;
							break;
				
						case 'timestamp':
				
							$rowsubform_label->addElement('text', $vdata['name'], array(
							'value'        => $vdata['value'],
							'required'     => false,
							'filters'      => array('StringTrim'),
							// 		    'validators'   => array('NotEmpty'),
							'decorators' => $elementDecorators,
							'class' => "kh_date"
									));
							break;
				
						default:
							$elementDecorators[$i][$j]['colspan'] = '2';
							$rowsubform_label->addElement('note', $vdata['name'], array(
									'value' => $vdata['value'],
									'decorators' => $elementDecorators
							));
							break;
				
				
							//print_r($elementDecorators); exit;
					}
					
				
				}
		
			}
			$subform2->addSubForm($rowsubform_label, $krow.'label');
			$subform2->addSubForm($rowsubform_data, $krow.'data');
			if($firstrow)
			{
				$rowsubform_label->addDecorator(array('head' => 'HtmlTag'), array('tag' => 'tbody', 'openOnly' => true));
				$firstrow = false;
			}
		}
		
		$subform->addSubform($subform2, 'second_details');
		//print_r($options_first); exit;
		/*$second = $this->_create_form_table($options_first, $elementsBelongTo = null);
		$subform->addSubForm($second, 'second_details');*/
		
		return $subform;
	
	}
	
	private function _create_form_details_page2($options = array(), $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('HtmlTag', array('tag'=>'div', 'class'=>'page2')),
		));
	
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$subform->addElement('note', 'seppage', array(
				'value' => '',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
								'style' => 'width: 99%; page-break-after: always;'))
				),
		));
		
		$subform->addElement('note', 'label_form_left', array(
				'value' => '&nbsp;',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'halfdiv',
						'style' => 'width: 49%; float: left;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND, //'class'=>'fulldiv',
								'style' => 'width: 99%;'),
						),
				),
		));
		
		$subform->addElement('note', 'label_form_right', array(
				'value' => $this->top_right_pag2,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'halfdiv right',
						'style' => 'width: 49%; float: left; text-align: right;')),
						array(array('fulldiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'closeOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::APPEND, //'class'=>'fulldiv',
								'style' => 'width: 99%;'),
						),
						array(array('cleartag' => 'HtmlTag'), array(
								'tag' => 'div', //'class'=>'clear',
								'style' => 'clear: both',
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
		));
	
		$subform->addElement('note', 'label_form_title_center', array(
				'value' => $this->title_pag2,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', //'class'=>'fulldiv center',
						'style' => 'width: 99%; text-align: center;'))
				),
		));
		
		$header = $this->_create_form_table($options['header'], $elementsBelongTo = null);
		$subform->addSubForm($header, 'header_details');
	
		//the body form
		//print_r(empty($options[receiving_or_entitled_careservices_from]['value'])); exit;
		$col_page1 = array(array('hospice_name', 'expected_recording_date'),
				array('hospice_address'),
				array('precontact'),
				array('contact_for_inquiries_name', 'contact_for_inquiries_tel'),
				array('medical_prescription_attached'),
				array('outpatient_or_semistationary_care_alternatif'),
				array('prereqcare'),
				array('required_longterm_care_insurance_based'),
				array('stufe_text'),
				array('receiving_or_entitled_careservices'),
				array('receiving_or_entitled_careservices_from'),
				array('postentcare'),
				array('careservice_name_address'),
				array('other'),
				array('insured_consent_for_signature')
				);
		$col_page1_attr = array(array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => true), array('label_on_same_row' => false, 'no_label' => false, 'label_after' => true)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => true)),
								array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => true), array('label_on_same_row' => false, 'no_label' => false, 'label_after' => true)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => false, 'no_label' => false, 'label_after' => true)),
								array(array('label_on_same_row' => false, 'no_label' => true, 'label_after' => false)),
								array(array('label_on_same_row' => true, 'no_label' => false, 'label_after' => false)));
		
		
		$options_first = array();
		$options_first['class'] = 'page2_body';
		$options_first['col'] = 1;
		$options_first['editable'] = true;
		$options_first['id'] = $options['id']['value'];
		//$options_first['label_on_same_row'] = false;
		
		foreach($col_page1 as $krow=>$vrow)
		{
			
			foreach($vrow as $kcol=>$vcol)
			{
				if($vcol == 'other')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol.'3';
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol.'3');
				}
				
				if($vcol == 'precontact')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol;
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol);
				}
				
				if($vcol == 'prereqcare')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol;
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol);
				}
				
				if($vcol == 'postentcare')
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol;
					$options_first['data'][$krow][$kcol]['value'] = $this->translator->translate($vcol);
				}
			
				if(array_key_exists($vcol, $options))
				{
					$options_first['data'][$krow][$kcol]['name'] = $vcol;
					if($vcol == 'hospice_name')
					{
						if(!$options[$vcol]['value'])
						{
							$options_first['data'][$krow][$kcol]['value'] = $this->_phospiceassoc['hospice_association'];
						}
						else
						{
                            $options_first['data'][$krow][$kcol]['value'] = $options[$vcol]['value'];
						}
					}
					else if($vcol == 'hospice_address')
					{
						if(!$options[$vcol]['value'])
						{
							$options_first['data'][$krow][$kcol]['value'] = $this->_phospiceassoc['zip'] . " " . $this->_phospiceassoc['city'];
						}
						else
						{
                             $options_first['data'][$krow][$kcol]['value'] = $options[$vcol]['value'];
						}
					}			
					else if($options[$vcol]['colprop']['type'] == 'timestamp')
					{
						switch ($vcol)
						{
							case 'form_date':
								$options_first['data'][$krow][$kcol]['value'] = (($options[$vcol]['value'] != '0000-00-00 00:00:00' && $options[$vcol]['value'] !== null) ? date('d.m.Y', strtotime($options[$vcol]['value'])) : date('d.m.Y'));
							break;
							default:
								$options_first['data'][$krow][$kcol]['value'] = (($options[$vcol]['value'] != '0000-00-00 00:00:00' && $options[$vcol]['value'] !== null) ? date('d.m.Y', strtotime($options[$vcol]['value'])) : '');
							break;
						}
					}
					else 
					{
						$options_first['data'][$krow][$kcol]['value'] = $options[$vcol]['value'];
					}
					
					if($options[$vcol]['colprop']['type'] == 'enum' || $options[$vcol]['colprop']['type'] == 'object')
					{
						$options_first['data'][$krow][$kcol]['values'] = $options[$vcol]['colprop']['values'];
						$options_first['data'][$krow][$kcol]['escape'] = false;
						
						switch ($vcol)
						{
							case 'receiving_or_entitled_careservices_from':
								$options_first['data'][$krow][$kcol]['separator'] = 10;
								$options_first['data'][$krow][$kcol]['type'] = 'object';
								
								$options_first['data'][$krow][$kcol]['names'][1] = '<span class="checkmark"></span>der Pflege-';
								$options_first['data'][$krow][$kcol]['names'][2] = '<span class="checkmark"></span>der Beihilfe-';
								$options_first['data'][$krow][$kcol]['names'][3] = '<span class="checkmark"></span>dem-';
								$options_first['data'][$krow][$kcol]['names'][4] = '<span class="checkmark"></span>der Unfall-';
								$options_first['data'][$krow][$kcol]['names'][5] = '<span class="checkmark"></span>dem Ver-';
								$options_first['data'][$krow][$kcol]['names'][6] = '<span class="checkmark"></span>sonstige';
								
								
								$options_first['data'][$krow][$kcol]['class'] = 'labelflex';
								break;
									
							default:
								$options_first['data'][$krow][$kcol]['separator'] = 30;
								$options_first['data'][$krow][$kcol]['names'] = array($options[$vcol]['colprop']['values'][1] => '<span class="checkmark"></span>ja', $options[$vcol]['colprop']['values'][0] => '<span class="checkmark"></span>nein');
								
								$options_first['data'][$krow][$kcol]['type'] = $options[$vcol]['colprop']['type'];
								break;
						}
					}
					else 
					{
						
                         $options_first['data'][$krow][$kcol]['type'] = $options[$vcol]['colprop']['type'];
					}
				}
				
				if($col_page1_attr[$krow][$kcol] != "")
				{
					foreach($col_page1_attr[$krow][$kcol] as $kcolat=>$vcolat)
					{
                         if($kcolat == 'view_type') continue;
                         $options_first['data'][$krow][$kcol][$kcolat] = $vcolat;
					}
				}
					
			
			}
			
		}
		
		//print_r($options_first); exit;
		$page2_body = $this->_create_form_table($options_first, $elementsBelongTo = null);
		$subform->addSubForm($page2_body, 'page2_details');
		
		$subform->addElement('note', 'label_form_final_left', array(
				'value' => $this->final_left,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv'))
				),
		));
	
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
		 'decorators'   => array('ViewHelper'),
		  
		 ));
		$subform->addElement($el, 'save');
                
		 
		$el = $this->createElement('button', 'button_action', array(
		 'type'         => 'submit',
		 'value'        => 'printpdf',
		 // 	        'content'      => $this->translate('submit'),
		 'label'        => $this->translator->translate('kh_generate_pdf'),
		 // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
		 'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
		 'decorators'   => array('ViewHelper'),
		  
		 ));
		$subform->addElement($el, 'printpdf');
		 
		 
		return $subform;
	
	}
	
	private function _create_form_table($options = array(), $elementsBelongTo = null) // for header table and [age 2 table
	{
		$subform = new Zend_Form_SubForm();
		//$subform->removeDecorator('Fieldset');
		$subform->setDecorators( array(
				'FormElements',
				array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable' ." ". $options['class'])),
				//'Fieldset',
		));
		
		
		if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		//print_r($options);exit;
		if(!$options['editable'])
		{
			if(!empty($options['tableheader']))
			{
				$rowsubform = $this->subFormTableRow();
				for($kcol = 0; $kcol<$options['col']; $kcol++)
				{
					if($kcol == 0)
					{
					$rowsubform->addElement('note', $options['tableheader'][$kcol]['name'], array(
							'value' => $options['tableheader'][$kcol]['value'],
							'decorators' => array(
									'ViewHelper',
									array(array('data' => 'HtmlTag'), array('tag' => 'td',
									'style' => 'border-top: 1px solid #000; border-bottom: 1px solid #000; border-left: 1px solid #000; border-right: 1px solid #000; width: 305px;'))
							),
					));
					}
					else if($kcol == 2)
					{
						$rowsubform->addElement('note', $options['tableheader'][$kcol]['name'], array(
								'value' => $options['tableheader'][$kcol]['value'],
								'decorators' => array(
										'ViewHelper',
										array(array('data' => 'HtmlTag'), array('tag' => 'td',
												'style' => 'border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; width: 305px;'))
								),
						));
					}
					else 
					{
						$rowsubform->addElement('note', $options['tableheader'][$kcol]['name'], array(
								'value' => $options['tableheader'][$kcol]['value'],
								'decorators' => array(
										'ViewHelper',
										array(array('data' => 'HtmlTag'), array('tag' => 'td',
												'style' => 'border-top: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000; width: 100px;'))
								),
						));
					}
				}
				
				$rowsubform->addDecorator(array('head' => 'HtmlTag'), array('tag' => 'thead'));
				$subform->addSubForm($rowsubform, 'head');
			}
			
			$firstrow = true;
			foreach($options['data'] as $krow=>$vrow)
			{			
				$rowsubform = $this->subFormTableRow();
				foreach($vrow as $kdata=>$vdata)
				{
					if($vdata['name'] == 'phealthinsurance_name')
					{
						
						$rowsubform->addElement($vdata['type'], $vdata['name'], array(
								'value' => $vdata['value'],
								'decorators' => array(
										'ViewHelper',
										array(array('data' => 'HtmlTag'), array('tag' => 'td', 'rowspan' => '5',
												"style" => 'border-left: 1px solid #000; border-bottom: 1px solid #000; border-right: 1px solid #000;'
										))
								),
						));
					}
					else 
					{
						if($kdata == 0)
						{
							$styleborder = 'border-bottom: 1px solid #000; border-right: 1px solid #000;';
						}
						else
						{
							$styleborder = 'border-bottom: 1px solid #000; border-right: 1px solid #000;';
						}
						$rowsubform->addElement($vdata['type'], $vdata['name'], array(
								'value' => $vdata['value'],
								'decorators' => array(
										'ViewHelper',
										array(array('data' => 'HtmlTag'), array('tag' => 'td',
												"style" => $styleborder
										))
								),
						));
					}
					
			//print_r($elementDecorators); exit;
				}
				if($firstrow)
				{
					$rowsubform->addDecorator(array('head' => 'HtmlTag'), array('tag' => 'tbody', 'openOnly' => true));
					$firstrow = false;
				}
				$subform->addSubForm($rowsubform, $krow);
			}
		}
		else 
		{
			if(!empty($options['tableheader']))
			{
				$rowsubform = $this->subFormTableRow();
				for($kcol = 0; $kcol<$options['col']; $kcol++)
				{
					$class_td = array("wide1", "", "wide2");
						
					$rowsubform->addElement('note', $options['tableheader'][$kcol]['name'], array(
							'value' => $options['tableheader'][$kcol]['value'],
							'decorators' => array(
									'ViewHelper',
									array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>$class_td[$kcol]))
							),
					));
				}
				
				$rowsubform->addDecorator(array('head' => 'HtmlTag'), array('tag' => 'thead'));
				$subform->addSubForm($rowsubform, 'head');
			}
			
			$firstrow = true;
			//print_r($options['data']); exit;
			foreach($options['data'] as $krow=>$vrow)
			{
				$columns = count($vrow);
				$rowsubform_label = $this->subFormTableRow();
				$rowsubform_data = $this->subFormTableRow();
				foreach($vrow as $kdata=>$vdata)
				{
					//var_dump($vdata); exit;
					$elementDecorators = array();
					$i = 0;
					$elementDecorators[$i] = 'ViewHelper';
					$i++;
					$elementDecorators[$i] = 'Label';
					$i++;
					
					$j = 0;
					$elementDecorators[$i][$j]['data'] = 'HtmlTag';

					$j++;
					$elementDecorators[$i][$j]['tag'] = 'td';
						
					$elementDecorators[$i][$j]['class'] = $vdata['class'];
						
					if($vdata['extra_td'])
					{
						foreach($vdata['extra_td'] as $kextra => $vextra)
						{
								
							$elementDecorators[$i][$j][$kextra] = $vextra;
						}
					}
					//var_dump($elementDecorators); exit;
					
					if(!$vdata['label_after'])
					{
						if(!$vdata['no_label'])
						{
							if (!$vdata['label_on_same_row'])
							{
									if(count($vrow) > 1)
									{
										//$elementDecorators[$i][$j]['width'] = '65%';
									}
									else 
									{
										$elementDecorators[$i][$j]['colspan'] = '4';
									}
									
									$rowsubform_label->addElement('note', 'label_'.$vdata['name'], array(
											'value' => $this->translate('label_'.$vdata['name']),
											'decorators' => $elementDecorators
									));								
								}
								else 
								{
									$rowsubform_label->addElement('note', 'label_30'.$vdata['name'], array(
											'value' => '',
											'decorators' => $elementDecorators
									));
								
									//$elementDecorators[$i][$j]['width'] = '65%';
									$elementDecorators[$i][$j]['colspan'] = '2';
									$rowsubform_data->addElement('note', 'label_'.$vdata['name'], array(
											'value' => $this->translate('label_'.$vdata['name']),
											'decorators' => $elementDecorators
									));
									
									
								}
						}
						else 
						{
							$elementDecorators[$i][$j]['colspan'] = '4';
							$rowsubform_label->addElement('note', 'label_10'.$vdata['name'], array(
									'value' => '',
									'decorators' => $elementDecorators
							));
						}
						
						switch ($vdata['type']) 
                        {
                        	case 'string':
                            case 'integer':
	                            if($vdata['name'] == 'stufe_text')
	                            {
	                            	$elementDecorators[$i][$j]['style'] = 'border-bottom: 1px solid #000';
	                                $label = 'Stufe';
	                          	}
	                                                       	
	                            $rowsubform_data->addElement('text', $vdata['name'], array(
	                                             			'value'        => $vdata['value'],
	                                              			'required'     => false,
	                                                        'filters'      => array('StringTrim'),
	                                   						'label' => $label,
	                                        				'class' => 'kh_stufe',
	                            							'style' => 'border: 0px; background: #fff;',
	                                                        'decorators' => $elementDecorators
	                             ));
	                             $elementDecorators[$i][$j]['style'] = 'none';
                             	break;
                          	case 'text':
                                                                
                                $rowsubform_data->addElement('textarea', $vdata['name'], array(
                                							'value'        => $vdata['value'],
                                                            'required'     => false,
                                                            'rows' => "2",
                                                            'filters'      => array('StringTrim'),
                                                            'decorators' => $elementDecorators
	                            ));
                                break;
                            case 'enum':
                                                                
                            
                               	$rowsubform_data->addElement('radio', $vdata['name'], array(
                                                           'label' => null,
                                                           'required'   => false,
                                                           'multiOptions'=>$vdata['names'],
                                                           'value' => $vdata['value'],
                                                           'decorators' => $elementDecorators,
                                                           'escape'     => $vdata['escape'],
                                                           'separator'  => str_repeat('&nbsp;', $vdata['separator']),                                                 
                               	));                                                              
                               	break;
                           	case 'object':
                                                                        
                                $rowsubform_data->addElement('multiCheckbox', $vdata['name'], array(
                                			                 'label'      => null,
                                                	         'required'   => false,
                                            	             'multiOptions' => $vdata['names'],
                                                             'value' => $vdata['value'],
                                                             'class' => ($vdata['onevalue'] ? 'onevalue' : ''),
                                                             'decorators' => $elementDecorators,
                                                             'escape'     => $vdata['escape'],
                                                             'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
                                                                        
                                  ));
                                  
                                  break;
                               case 'timestamp':
                                                                
                                  $rowsubform_data->addElement('text', $vdata['name'], array(
                                                               'value'        => $vdata['value'],
                                                               'required'     => false,
                                                               'filters'      => array('StringTrim'),                               
                                                                'decorators' => $elementDecorators,
                                                                'class' => "kh_date"
                                  ));
                                  break;
                                                                
                              	default:
                                	 $elementDecorators[$i][$j]['colspan'] = '4';
                                     $rowsubform_data->addElement('note', $vdata['name'], array(
                                                                 'value' => $vdata['value'],
                                                                'decorators' => $elementDecorators
                                     ));
                                     break;
                          }
					}
					elseif($vdata['label_after'])
					{
						if(!$vdata['no_label'])
						{
							if (!$vdata['label_on_same_row'])
							{
								$elementDecorators[$i][$j]['style'] = 'border-top: 1px solid #000';
								if(count($vrow) > 1)
								{
									//$elementDecorators[$i][$j]['width'] = '65%';
									$rowsubform_data->addElement('note', 'label_1'.$vdata['name'], array(
											'value' => $this->translate('label_'.$vdata['name']),
											'decorators' => $elementDecorators
									));
									$elementDecorators[$i][$j]['style'] = 'border-top: none';
//									$elementDecorators[$i][$j]['width'] = '15%';
									$rowsubform_data->addElement('note', 'label_2'.$vdata['name'], array(
											'value' => '&nbsp;',
											'decorators' => $elementDecorators
									));
								}
								else 
								{
									$elementDecorators[$i][$j]['colspan'] = '4';
									$rowsubform_data->addElement('note', 'label_'.$vdata['name'], array(
											'value' => $this->translate('label_'.$vdata['name']),
											'decorators' => $elementDecorators
									));
									$elementDecorators[$i][$j]['style'] = 'border-top: none';
								}
								
							
							}
						}
						else 
						{
							if (!$vdata['label_on_same_row'])
							{
								$elementDecorators[$i][$j]['style'] = 'border-top: 1px solid #000';
								$elementDecorators[$i][$j]['colspan'] = '4';
								$rowsubform_data->addElement('note', 'label_'.$vdata['name'], array(
										'value' => '$nbsp;',
										'decorators' => $elementDecorators
								));
								$elementDecorators[$i][$j]['style'] = 'border-top: none';
							}
						}
						
						switch ($vdata['type'])
						{
							case 'string':
							case 'integer':
								
								$rowsubform_label->addElement('text', $vdata['name'], array(
								'value'        => $vdata['value'],
								'required'     => false,
								'filters'      => array('StringTrim'),
								// 		    'validators'   => array('NotEmpty'),
								'style' => 'border: 0px; background: #fff;',
								'decorators' => $elementDecorators
								));
								
								break;
							case 'text':
									
								$rowsubform_label->addElement('textarea', $vdata['name'], array(
								'value'        => $vdata['value'],
								'required'     => false,
								'rows' => "2",
								'filters'      => array('StringTrim'),
								// 		    'validators'   => array('NotEmpty'),
								'decorators' => $elementDecorators
								));
								break;
							case 'enum':
                                                                
								$rowsubform_data->addElement('radio', $vdata['name'], array(
										'label' => null,
										'required'   => false,
										'multiOptions'=>$vdata['names'],
										'value' => $vdata['value'],
										'decorators' => $elementDecorators,
										'escape'     => $vdata['escape'],
										'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
													
								));
								break;
								
                                                        case 'object':
                                                        
                                                                $rowsubform_data->addElement('multiCheckbox', $vdata['name'], array(
                                                                'label'      => null,
                                                                'required'   => false,
                                                                'multiOptions' => $vdata['names'],
                                                                'value' => $vdata['value'],
                                                                'class' => ($vdata['onevalue'] ? 'onevalue' : ''),
                                                                'decorators' => $elementDecorators,
                                                                'escape'     => $vdata['escape'],
                                                                'separator'  => str_repeat('&nbsp;', $vdata['separator']), //&nbsp;',
                                                                        
                                                                ));
                                                                //var_dump($elementDecorators); exit;
                                                                break;
									
							case 'timestamp':
									
								$rowsubform_label->addElement('text', $vdata['name'], array(
								'value'        => $vdata['value'],
								'required'     => false,
								'filters'      => array('StringTrim'),
								// 		    'validators'   => array('NotEmpty'),
								'style' => 'border: 0px; background: #fff;',
								'decorators' => $elementDecorators,
								'class' => "kh_date"
										));
								break;
									
							default:
								$elementDecorators[$i][$j]['colspan'] = '4';
								$rowsubform_label->addElement('note', $vdata['name'], array(
								'value' => $vdata['value'],
								'decorators' => $elementDecorators
								));
								break;
					
					
					//print_r($elementDecorators); exit;
                                                }
                                                if(count($vrow) > 1)
                                                {
                                                        $rowsubform_label->addElement('note', 'label_3'.$vdata['name'], array(
                                                                        'value' => '&nbsp;',
                                                                        'decorators' => $elementDecorators
                                                        ));
                                                }
				
					}
			
				
                                }
                                $subform->addSubForm($rowsubform_label, $krow.'label');
                                $subform->addSubForm($rowsubform_data, $krow.'data');
                                if($firstrow)
                                {
                                        $rowsubform_label->addDecorator(array('head' => 'HtmlTag'), array('tag' => 'tbody', 'openOnly' => true));
                                        $firstrow = false;
                                }
                        }
			
		}

		return $subform;
			
		}
		
		public function save_form_kindersapvhospiz($ipid = null, array $data = array())
		{
			if (empty($ipid)) {
				throw new Exception('Contact Admin, formular cannot be saved.', 0);
			}
			 //print_r($data);exit;
			//$kindersapvhospiz = null;
			 $id = $data['id'];
			 unset($data['id']);
			//formular will be saved first so we have a id
			if ( ! empty($data)) {
				foreach($data as $kdata=>$vdata)
				{
					foreach($vdata as $kcol=>$vcol)
					{						
						//$khdata[$kcol] = $vcol;
						
						if($kcol == 'form_date' || $kcol == 'expected_recording_date')
						{
							if($vcol != '')
							{
								$khdata[$kcol] = date('Y-m-d H:i:s', strtotime($vcol));
							}
							else 
							{
								$khdata[$kcol] = '0000-00-00 00:00:00';
							}
						}
						else
						{
                                                    $khdata[$kcol] = $vcol;
						}
						
					}
					
				}
				
				//print_r($khdata); exit;
				$entitykh  = new KinderSapvHospiz();
				$kindersapvhospiz =  $entitykh->findOrCreateOneByIpidAndId($ipid, $id, $khdata);
				if($kindersapvhospiz->id)
				{
					if($id)
					{
						$custcourse = new PatientCourse();
						$custcourse->ipid = $ipid;
						$custcourse->course_date = date("Y-m-d H:i:s", time());
						$custcourse->course_type = Pms_CommonData::aesEncrypt(KinderSapvHospiz::PATIENT_COURSE_TYPE);
						$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes(KinderSapvHospiz::PATIENT_COURSE_TITLE_EDIT));
						$custcourse->user_id = $data['userid'];
						$custcourse->tabname = Pms_CommonData::aesEncrypt(KinderSapvHospiz::PATIENT_COURSE_TABNAME_SAVE);
						$custcourse->recordid = $kindersapvhospiz->id;
						$custcourse->done_name = Pms_CommonData::aesEncrypt(KinderSapvHospiz::PATIENT_COURSE_TABNAME_SAVE);
						$custcourse->done_id = $kindersapvhospiz->id;
						$custcourse->save();
					}
					else 
					{
						$custcourse = new PatientCourse();
						$custcourse->ipid = $ipid;
						$custcourse->course_date = date("Y-m-d H:i:s", time());
						$custcourse->course_type = Pms_CommonData::aesEncrypt(KinderSapvHospiz::PATIENT_COURSE_TYPE);
						$custcourse->course_title = Pms_CommonData::aesEncrypt(addslashes(KinderSapvHospiz::PATIENT_COURSE_TITLE_CREATE));
						$custcourse->user_id = $data['userid'];
						$custcourse->tabname = Pms_CommonData::aesEncrypt(KinderSapvHospiz::PATIENT_COURSE_TABNAME_SAVE);
						$custcourse->recordid = $kindersapvhospiz->id;
						$custcourse->done_name = Pms_CommonData::aesEncrypt(KinderSapvHospiz::PATIENT_COURSE_TABNAME_SAVE);
						$custcourse->done_id = $kindersapvhospiz->id;
						$custcourse->save();
					}
				}
				
				return $kindersapvhospiz;
			}
		}
	
}