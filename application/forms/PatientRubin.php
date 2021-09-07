<?php
/**
 * 
 * @author carmen
 * 
 * 29.03.2019
 *
 */
class Application_Form_PatientRubin extends Pms_Form
{
	protected $title_center = null;
	
	protected $_client_data = null;
	
	public function __construct($options = null)
	{
		if($options['_client_data'])
		{
			$this->_client_data = $options['_client_data'];
			unset($options['_client_data']);
		}
		
		parent::__construct($options);
		//print_r($this->_client_data); exit;
		$this->title_center = '<h2>RUBIN - Mini Nutritional Assessment</h2>';
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function getColumnMapping($fieldName, $revers = false)
	{
	
		//             $fieldName => [ value => translation]
		$overwriteMapping = [
				'question1' => ['severe_anorexia' =>  'schwere Anorexie (0)',
								'slight_anorexia' =>  'leichte Anorexie (1)',
								'no_anorexia' => 'keine Anorexie (2)',
								],
				'question2' => ['weight_loss' =>  'Gewichtsverlust > 3 kg (0)',
								'do_not_know' =>  'weiß es nicht (1)',
								'weight_loss_between_1_3_kg' => 'Gewichtsverlust zwischen 1 und 3 kg (2)',
								'no_weight_loss' => 'kein Gewichtsverlust (3)',
								],
				'question3' => ['from_bed_to_chair' =>  'bettlägerig oder in einem Stuhl mobilisiert (0)',
								'mobile_in_apartment' =>  'in der Lage, sich in der Wohnung zu bewegen (1)',
								'leaves_the_apartment' => 'Verlässt die Wohnung (2)',
								],
				'question4' => ['yes' =>  'Ja (0)',
								'no' =>  'Nein (2)',
								],								
				'question5' => ['severe_dementia_or_depression' =>  'schwere Demenz oder Depression (0)',
								'mild_dementia_or_depression' =>  'leichte Demenz oder Depression (1)',
								'no_problem' => 'keine Probleme (2)',
								],
				'question6' => [
								'bmi_lt_19' =>  'BMI < 19 (0)',
								'bmi_between_19_21' => '19 <= BMI < 21 (1)',
								'bmi_between_21_23' => '21 <= BMI < 23 (2)',
								'bmi_gt_23' => 'BMI >= 23 (3)',
								],
				'question7' => ['no' =>  'Nein (0)',
								'yes' =>  'Ja (1)',
				],
				'question8' => ['yes' =>  'Ja (0)',
								'no' =>  'Nein (1)',
				],
				'question9' => ['yes' =>  'Ja (0)',
								'no' =>  'Nein (1)',
				],
				'question10' => ['1meal' =>  '1 Mahlzeit (0)',
								'2meals' =>  '2 Mahlzeiten (1)',
								'3meals' => '3 Mahlzeiten (2)',
				],
				'question11' => ['dairy_products_at_least_once_a_day' =>  'mindestens einmal pro Tag Milchprodukte?',
								'at_least_once_or_twice_a_week_legumes_or_eggs' =>  'mindestens ein- bis zweimal pro Woche Hülsenfrüchte oder Eier?',
								'every_day_meat_fish_or_poultry' => 'jeden Tag Fleisch, Fisch oder Geflügel?',
				],
				'question12' => ['no' =>  'Nein (0)',
						'yes' =>  'Ja (1)',
				],
				'question13' => ['less_than_3_glasses_or_cups' =>  'weniger als 3 Gläser / Tassen (0)',
								'3_to_5_glasses_or_cups' =>  '3 bis 5 Gläser / Tassen (0,5)',
								'more_than_5_glasses_or_cups' => 'mehr als 5 Gläser / Tassen (1)',
				],
				'question14' => ['needs_help_with_food' =>  'braucht Hilfe beim Essen (0)',
								'eats_without_help_but_with_difficulty' =>  'isst ohne Hilfe, aber mit Schwierigkeiten (1)',
								'eats_without_help_no_trouble' => 'isst ohne Hilfe, keine Schwierigkeiten (2)',
				],
				'question15' => ['serious_malnutrition' =>  'schwerwiegende Unter-/Mangelernährung (0)',
								'do_not_know_or_slight_malnutrition' =>  'weiß es nicht oder leichte Unter-/Mangelernährung (1)',
								'well_fed' => 'gut ernährt (2)',
				],
				'question16' => ['worse' =>  'schlechter (0)',
								'do_not_know' =>  'weiß es nicht (0,5)',
								'equally_good' => 'gleich gut (1)',
								'better' => 'besser (2)',
				],
				'question17' => ['oau_lt_21' =>  'OAU < 21 (0)',
								'oau_between_21_22' =>  '21 <= OAU <= 22 (0,5)',
								'oau_gt_22' => 'OAU > 22 (1)',
				],
				'question18' => ['wu_lt_31' =>  'WU < 31 (0)',
								'wu_gt_31' =>  'WU >= 31 (1)',
				],
		];
	
	
		$values = FormBlockAdverseeventsTable::getInstance()->getEnumValues($fieldName);
	
		 
		$values = array_combine($values, array_map("self::translate", $values));
	
		if (isset($overwriteMapping[$fieldName])) {
			$values = $overwriteMapping[$fieldName] + $values;
		}
	
		return $values;
	
	}
	
	public function create_form_rubin( $options = array(), $elementsBelongTo = null)
	{
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		
		$this->mapValidateFunction($__fnName , "create_form_isValid");
			
		$this->mapSaveFunction($__fnName , "save_form_rubin");
		 
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		//$this->addDecorator('Fieldset', array());
		$this->addDecorator('Form');
		
		$this->__setElementsBelongTo($this, $elementsBelongTo);
		
		$this->addElement('note', 'label_bortop', array(
				'value' => '',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv bordtop'))
				),
		));
		
		$this->addElement('note', 'label_form_title_center', array(
				'value' => $this->title_center,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv center'))
				),
		));
		
		$this->addElement('note', 'label_bordbott', array(
				'value' => '',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv bordbott'))
				),
		));
		
		$this->addElement('note', 'label_empty', array(
				'value' => '',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv empty'))
				),
		));
		
		$this->addElement('note', 'label_patient_name', array(
				'value' => 'Name',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'div20')),
						array(array('ftag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND))
				),
		));
		
		$this->addElement('note', 'patient_name', array(
				'value' => $this->_patientMasterData['first_name'] . ',' . $this->_patientMasterData['last_name'] . ' - ' . strtoupper($this->_patientMasterData['epid']),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'div40')),
						array(array('ftag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
		));
		
		$this->addElement('note', 'label_empty_1', array(
				'value' => '',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv empty'))
				),
		));
		
		$this->addElement('note', 'label_form_date', array(
				'value' => 'Datum',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'div20')),
						array(array('ftag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND))
				),
		));
		
		$this->addElement('text', 'form_date', array(
				'value'        => date('d.m.Y'),
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'div40')),
						array(array('ftag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
				'class' => 'form_date',
				//'style' => 'width: 100px; padding-left: 10px; border: 0px; border-bottom: 1px solid #000; background: #fff; font-size: 14px;  height: 16px;',
		));
		
		$this->addElement('note', 'label_empty_2', array(
				'value' => '',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv empty'))
				),
		));
		
		$this->addElement('note', 'label_client_name', array(
				'value' => 'Client',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'div20')),
						array(array('ftag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv', 'openOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::PREPEND))
				),
		));
		
		$this->addElement('note', 'client_name', array(
				'value' => $this->_client_data['team_name'],
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'div40')),
						array(array('ftag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv', 'closeOnly' => true, 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
		));
		
		$this->addElement('note', 'label_bordbott_1', array(
				'value' => '',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv bordbott'))
				),
		));
		
		$this->addElement('hidden', 'form_id', array(
				'value'        => $options['form_id'] ? $options['form_id'] : null,
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fulldiv'))
				),
		));
		
		$this->addElement('note', 'label_empty_3', array(
				'value' => '',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv empty'))
				),
		));
		
		unset($options['ipid']);
		unset($options['create_date']);
		unset($options['form_date']);
		unset($options['create_user']);
		unset($options['change_date']);
		unset($options['change_user']);
		unset($options['isdelete']);
		
		$idq = 1;
		foreach($options as $kcat=>$vcat)
		{
			
			$subtable = new Zend_Form_SubForm();
			//$subform->removeDecorator('Fieldset');
			if($kcat == 'anamnesis' || $kcat == 'anamnesis_total')
			{
				$subtable->setDecorators( array(
						'FormElements',
						array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable anamnesis', 'cellpadding'=>"0", 'cellspacing'=>"0")),
						//'Fieldset',
				));
			}
			else 
			{
				$subtable->setDecorators( array(
						'FormElements',
						array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'SimpleTable', 'cellpadding'=>"0", 'cellspacing'=>"0")),
						//'Fieldset',
				));
			}
			
			if($kcat == 'id') continue;
			
			$subsubrow = $this->subFormTableRow();
			
			$elementDecorators = array();
			$i = 0;
			$elementDecorators[$i] = 'ViewHelper';
			$i++;
			$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'innerdiv'));
			$i++;
			$j = 0;
			$elementDecorators[$i][$j]['data'] = 'HtmlTag';
			$j++;
				
			$elementDecorators[$i][$j]['tag'] = 'td';

			$elementDecorators[$i][$j]['class'] = 'titletd cl_'.$kcat;
			
			$subsubrow->addElement('note', 'label_'.$kcat, array(
					'value' => $this->translate($kcat),
					'decorators' => $elementDecorators
			));
			
			if($kcat == 'before_anamnesis_total' || $kcat == 'anamnesis_total' || $kcat == 'total')
            {
    			$elementDecorators = array();
    			$i = 0;
    			$elementDecorators[$i] = 'ViewHelper';
    			$i++;
    			$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'innerdiv cl_'.$kcat));
    			$i++;
    			$j = 0;
    			$elementDecorators[$i][$j]['data'] = 'HtmlTag';
    			$j++;
    				
    			$elementDecorators[$i][$j]['tag'] =  'td';
    			$elementDecorators[$i][$j]['openOnly'] =  true;
    			
    			$elementDecorators[$i][$j]['class'] = 'normaltd';
    			
    			$subsubrow->addElement('text', $kcat, array(
    					'value'        => $vcat['value'] ? $vcat['value'] : '0',
    					'required'     => false,
    					'readOnly' => true,
    					'filters'      => array('StringTrim'),
    					'decorators' => $elementDecorators,
    					'id' => $kcat,
    			));
    			$subtable->addSubForm($subsubrow, $kcat.'head'); 
    			
    			if($kcat == 'before_anamnesis_total' || $kcat == 'total')
    			{
    				$subsubrow = $this->subFormTableRow();
    				$elementDecorators = array();
    				$i = 0;
    				$elementDecorators[$i] = 'ViewHelper';
    				$i++;
    				$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'innerdiv totaltd'));
    				$i++;
    				$j = 0;
    				$elementDecorators[$i][$j]['data'] = 'HtmlTag';
    				$j++;
    				
    				$elementDecorators[$i][$j]['tag'] =  'td';
    				$elementDecorators[$i][$j]['colspan'] =  '2';
    				$elementDecorators[$i][$j]['openOnly'] =  true;
    					
    				$elementDecorators[$i][$j]['class'] = 'normaltd';
    					
    				$subsubrow->addElement('text', 'text_'.$kcat, array(
    						'value' => '',
    						'required'     => false,
    						'readOnly' => true,
    						'filters'      => array('StringTrim'),
    						'decorators' => $elementDecorators,
    						'class' => 'text_total',
    						'id' => 'text_'.$kcat,
    				));
    				$subtable->addSubForm($subsubrow, $kcat.'text');
    			}
    		}
			else 
			{
				$subtable->addSubForm($subsubrow, $kcat.'head');
			
				foreach($vcat['colprop']['values'] as $krow=>$vrow)
				{		
					
					$subsubrow = $this->subFormTableRow();
			
					$elementDecorators = array();
					$i = 0;
					$elementDecorators[$i] = 'ViewHelper';
					$i++;
					$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'innerdiv'));
					$i++;
					$j = 0;
					$elementDecorators[$i][$j]['data'] = 'HtmlTag';
					$j++;
						
					$elementDecorators[$i][$j]['tag'] = 'td';
					
					$elementDecorators[$i][$j]['class'] = 'normaltd title_td';
					
					$nrq = substr($krow, 8);
					
					$subsubrow->addElement('note', 'label_'.$krow, array(
							'value' => '<b>Anfrage '. $nrq. ' - </b>'. $this->translate($krow),
							'decorators' => $elementDecorators
					));
					$subtable->addSubForm($subsubrow, $krow.'label');
					
					if($krow == 'question6')
					{
						$subsubrow = $this->subFormTableRow();
						
						$elementDecorators = array();
						$i = 0;
						$elementDecorators[$i] = 'ViewHelper';
						$i++;
						$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'labeldiv'));
						$i++;
						$elementDecorators[$i] = array('Description', array('class' => 'description', 'escape' => false));
						$i++;
						$elementDecorators[$i] = array('Label', array('placement' => 'PREPEND'));
						$i++;
						$j = 0;
						$elementDecorators[$i][$j]['data'] = 'HtmlTag';
						$j++;
							
						$elementDecorators[$i][$j]['tag'] =  'td';
						$elementDecorators[$i][$j]['openOnly'] =  true;
						
						$elementDecorators[$i][$j]['class'] = 'normaltd';
						
						$subsubrow->addElement('text', 'weight', array(
								'label' => 'Körpergewicht(kg)',
								'value' => $vcat['value'][$krow]['weight'] ? $vcat['value'][$krow]['weight'] : '',
								'required'     => false,
								'filters'      => array('StringTrim'),
								'decorators' => $elementDecorators,
								'description' => '<br />',
								'id' => 'weight',
								'onChange' => "calcbmi(\$(this))",
						));
						$elementDecorators = array();
						$i = 0;
						$elementDecorators[$i] = 'ViewHelper';
						$i++;
						$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'labeldiv'));
						$i++;
						$elementDecorators[$i] = array('Description', array('class' => 'description', 'escape' => false));
						$i++;
						$elementDecorators[$i] = array('Label', array('placement' => 'PREPEND'));
						$i++;
						$j = 0;
						$elementDecorators[$i][$j]['data'] = 'HtmlTag';
						$j++;
						
						$subsubrow->addElement('text', 'height', array(
								'label' => 'Körpergröße(m)',
								'value' => $vcat['value'][$krow]['height'] ? $vcat['value'][$krow]['height'] : '',
								'required'     => false,
								'filters'      => array('StringTrim'),
								'decorators' => $elementDecorators,
								'description' => '<br />',
								'id' => 'height',
								'onChange' => "calcbmi(\$(this))",
						));
						$elementDecorators = array();
						$i = 0;
						$elementDecorators[$i] = 'ViewHelper';
						$i++;
						$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'labeldiv'));
						$i++;
						$elementDecorators[$i] = array('Description', array('class' => 'description', 'escape' => false));
						$i++;
						$elementDecorators[$i] = array('Label', array('placement' => 'PREPEND'));
						$i++;
						$j = 0;
						$elementDecorators[$i][$j]['data'] = 'HtmlTag';
						$j++;
						$elementDecorators[$i][$j] =  array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class' => 'normaltd'));
						
						$subsubrow->addElement('text', 'bmi', array(
								'label' => 'BMI(kg/m2)',
								'value' => $vcat['value'][$krow]['bmi'] ? $vcat['value'][$krow]['bmi'] : '',
								'required'     => false,
								'readOnly' => true,
								'filters'      => array('StringTrim'),
								'id' => 'bmi',
								'decorators' => $elementDecorators,
								'description' => '<br />'
						));
						
						$elementDecorators = array();
						$i = 0;
						$elementDecorators[$i] = 'ViewHelper';
						$i++;
						$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'innerdiv'));
						$i++;
						$j = 0;
						$elementDecorators[$i][$j]['data'] = 'HtmlTag';
						$j++;
							
						$elementDecorators[$i][$j]['tag'] =  'td';
						$elementDecorators[$i][$j]['closeOnly'] =  true;
						
						$elementDecorators[$i][$j]['class'] = 'normaltd';
						$subsubrow->addElement('radio', $krow, array(
								'value' => $vcat['value'][$krow][$krow] ? $vcat['value'][$krow][$krow] : '',
								'multiOptions' => $this->getColumnMapping('question'.$idq),
								'decorators' => $elementDecorators,
								'data-score' => json_encode($vrow),
								'data-cat' => $kcat,
								'class' => 'calcscore',
								'onChange' => "calcscore(\$(this))",
						));
						
						$subtable->addSubForm($subsubrow, $krow);
					}
					else if($krow == 'question11')
					{
						
						$radio_options = $this->getColumnMapping('question'.$idq);
						$t = 0;
						foreach($radio_options as $kr=>$vr)
						{
							$subsubrow = $this->subFormTableRow();
							
							$elementDecorators = array();
							$i = 0;
							$elementDecorators[$i] = 'ViewHelper';
							$i++;
							$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'innerdiv'));
							$i++;
							$elementDecorators[$i] = array('Description', array('class' => 'description', 'escape' => false));
							$i++;
							$j = 0;
							$elementDecorators[$i][$j]['data'] = 'HtmlTag';
							$j++;
								
							$elementDecorators[$i][$j]['tag'] =  'td';
							$elementDecorators[$i][$j]['openOnly'] =  true;
							
							$elementDecorators[$i][$j]['class'] = 'normaltd';
							$subsubrow->addElement('radio', 'o_'.$t, array(
									'value' => $vcat['value'][$krow.'_'.$t]['o_'.$t] ? $vcat['value'][$krow.'_'.$t]['o_'.$t] : '0',
									'multiOptions' => array('1'=>$vr),
									'id' => $kr,
									"class" => "question11",
									'decorators' => $elementDecorators,
							    //'onChange' => "setunset(\$(this))",		//ISPC-2353 @Lore 31.10.2019
							));
							
							$elementDecorators = array();
							$i = 0;
							$elementDecorators[$i] = 'ViewHelper';
							$i++;
							$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'innerdiv inner extra_space'));
							$i++;
							$elementDecorators[$i] = array('Description', array('class' => 'description', 'escape' => false));
							$i++;
							$j = 0;
							$elementDecorators[$i][$j]['data'] = 'HtmlTag';
							$j++;
							
							$elementDecorators[$i][$j]['tag'] =  'td';
							$elementDecorators[$i][$j]['closeOnly'] =  true;
								
							$elementDecorators[$i][$j]['class'] = 'normaltd';
							$subsubrow->addElement('radio', $kr, array(
									'value' => $vcat['value'][$krow.'_'.$t][$kr] ? $vcat['value'][$krow.'_'.$t][$kr] : '',
// 									'multiOptions' => array('yes' => 'Ja('.$vrow[$kr]['yes'].')', 'no' => 'Nein(0)'),
									'multiOptions' => array('yes' => 'Ja', 'no' => 'Nein'),
									'decorators' => $elementDecorators,
									'data-score' => json_encode($vrow[$kr]),
									'data-cat' => $kcat,
									'data-parent' => $kr.'-1',
									'data-question' => 'question11',
									'class' => 'yesno sub_question11',
									'onChange' => "calcscoreq11(\$(this))",
							    
							));
							$subtable->addSubForm($subsubrow, $krow.'_'.$t);
							$t++;
						}
						
						$this->addElement('hidden', 'q11_hidden', array(
						    'value' => '0',
						    'data-score' => json_encode(array('0'=>'0','1'=>'0','2'=>'0.5','3'=>'1')),
						    'data-cat' => $kcat,
						    'class' => 'calcscore',
						));
						//exit;
					}
					else 
					{
					
					$subsubrow = $this->subFormTableRow();
					
					$elementDecorators = array();
					$i = 0;
					$elementDecorators[$i] = 'ViewHelper';
					$i++;
					$elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'class' => 'innerdiv'));
					$i++;
					$j = 0;
					$elementDecorators[$i][$j]['data'] = 'HtmlTag';
					$j++;
					
					$elementDecorators[$i][$j]['tag'] = 'td';
						
					$elementDecorators[$i][$j]['class'] = 'normaltd';
					$subsubrow->addElement('radio', $krow, array(
							'value' => $vcat['value'][$krow][$krow] ? $vcat['value'][$krow][$krow] : '',
					        'multiOptions' => $this->getColumnMapping('question'.$idq),
					        'decorators' => $elementDecorators,
							'data-score' => json_encode($vrow),
							'data-cat' => $kcat,
							'class' => 'calcscore',
							'onChange' => "calcscore(\$(this))",
			    	));					
				
				$subtable->addSubForm($subsubrow, $krow);
					}
				$idq++;			
		}
			}
				$this->addSubForm($subtable, $kcat);
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
	
	public function save_form_rubin($ipid = null, array $data = array())
	{
		if (empty($ipid)) {
			throw new Exception('Contact Admin, formular cannot be saved.', 0);
		}
		//print_r($data); exit;
		if($data['form_id'] == '')
		{
			$data['form_id'] = null;
		}
		
		if($data['form_date'] != "")
		{
			$data['form_date'] = date('Y-m-d', strtotime($data['form_date']));
		}

		//print_r($data); exit;
		$entity = PatientRubinTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['form_id'], $ipid], $data);
	
		return $entity;
	}
	
}