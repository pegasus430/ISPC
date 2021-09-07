<?php
/**
 * 
 * @author carmen
 * 
 * 29.03.2019
 * Demstepcare_upload - 10.09.2019 Ancuta
 */
class Application_Form_PatientRubinForms extends Pms_Form
{
	protected $title_center = null;
	
	public function __construct($options = null)
	{
		
		//print_r($this->_patientMasterData); exit;
		parent::__construct($options);
		$this->title_center = '<h2 class="form_title">'.$this->translate($options['_page_name']).'</h2>';
		
	}
	
	public function isValid($data)
	{
        return parent::isValid($data);
	}

	
	public function getColumnMapping($fieldName, $revers = false)
	{
	
		//             $fieldName => [ value => translation]
		$overwriteMapping = [
				'question1' => ['severe_anorexia' =>  'schwere Anorexie (1)',
								'slight_anorexia' =>  'leichte Anorexie (2)',
								'no_anorexia' => 'keine Anorexie (3)',
								],
				'question2' => ['weight_loss' =>  'Gewichtsverlust > 3 kg (1)',
								'do_not_know' =>  'weiß es nicht (2)',
								'weight_loss_between_1_3_kg' => 'Gewichtsverlust zwischen 1 und 3 kg (3)',
								'no_weight_loss' => 'kein Gewichtsverlust (4)',
								],
				'question3' => ['from_bed_to_chair' =>  'bettlägerig oder in einem Stuhl mobilisiert (1)',
								'mobile_in_apartment' =>  'in der Lage, sich in der Wohnung zu bewegen (2)',
								'leaves_the_apartment' => 'Verlässt die Wohnung (3)',
								],
				'question4' => ['yes' =>  'Ja (1)',
								'no' =>  'Nein (2)',
								],								
				'question5' => ['severe_dementia_or_depression' =>  'schwere Demenz oder Depression (1)',
								'mild_dementia_or_depression' =>  'leichte Demenz oder Depression (2)',
								'no_problem' => 'keine Probleme (3)',
								],
				'question6' => [
								'bmi_lt_19' =>  'BMI < 19 (1)',
								'bmi_between_19_21' => '19 ≤ BMI < 21 (2)',
								'bmi_between_21_23' => '21 ≤ BMI < 23 (3)',
								'bmi_gt_23' => 'BMI ≥ 23 (4)',
								],
				'question7' => ['no' =>  'Nein (1)',
								'yes' =>  'Ja (2)',
				],
				'question8' => ['yes' =>  'Ja (0)',
								'no' =>  'Nein (3)',
				],
				'question9' => ['yes' =>  'Ja (0)',
								'no' =>  'Nein (1)',
				],
				'question10' => ['1meal' =>  '1 Mahlzeit (1)',
								'2meals' =>  '2 Mahlzeiten (2)',
								'3meals' => '3 Mahlzeiten (3)',
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
				'question15' => ['serious_malnutrition' =>  'schwerwiegende Unter-/Mangelernährung (1)',
								'do_not_know_or_slight_malnutrition' =>  'weiß es nicht oder leichte Unter-/Mangelernährung (2)',
								'well_fed' => 'gut ernährt (3)',
				],
				'question16' => ['worse' =>  'schlechter (0)',
								'do_not_know' =>  'weiß es nicht (0,5)',
								'equally_good' => 'gleich gut (1)',
								'better' => 'besser (2)',
				],
				'question17' => ['oau_lt_21' =>  'OAU < 21 (0)',
								'oau_between_21_22' =>  '21 ≤ OAU ≤ 22 (0,5)',
								'oau_gt_22' => 'OAU > 22 (1)',
				],
				'question18' => ['wu_lt_31' =>  'WU < 31 (0)',
								'wu_gt_31' =>  'WU ≥ 31 (1)',
				],
		];
	
	
		$values = FormBlockAdverseeventsTable::getInstance()->getEnumValues($fieldName);
	
		 
		$values = array_combine($values, array_map("self::translate", $values));
	
		if (isset($overwriteMapping[$fieldName])) {
			$values = $overwriteMapping[$fieldName] + $values;
		}
	
		return $values;
	
	}
	

	public function nosger_scores($labels = true,$mapping = false)
	{
	    $score_calculation =array(
	        'score_memory'=> array(8,12,16,22,27),
	        'score_iadl'=> array(2,6,9,11,19),
	        'score_adl'=>array(1,7,14,18,24),
	        'score_mood'=>array(3,10,13,25,28),
	        'score_social'=>array(5,17,21,26,29),
	        'score_disturbing'=>array(4,15,20,23,30),
	    );
	    if($mapping){
	        return $score_calculation;
	    }
	    
	    $scores= array(
	        'score_memory'=>"Gedächtnis",
	        'score_iadl'=>"Instrumental Activities of Daily Life (IADL)",
	        'score_adl'=>"Körperpflege (Activities of Daily Life, ADL)",
	        'score_mood'=>"Stimmung",
	        'score_social'=>"Soziales Verhalten",
	        'score_disturbing'=>"Störendes Verhalten"
	    );
	    
	    if($labels){
	        return $scores;
	    }
	    
	    
	}
	
	
	public function create_form( $options = array(), $elementsBelongTo = null)
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
		
		$this->addElement('note', 'label_form_title_center', array(
				'value' => $this->title_center,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'fulldiv center'))
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
		
		$this->addElement('hidden', 'form_ident', array(
				'value'        => $elementsBelongTo,
				'required'     => false,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class' => 'fulldiv'))
				),
		));
		
        // add line for "custom form"
		switch ($elementsBelongTo){
		    case 'tug':
		    case 'iadl':
		    case 'whoqol':
		    case 'demtect':
		    case 'carerelated':   //ISPC-2492 Lore 02.12.2019
		    case 'carepatient':   //ISPC-2493 Lore 03.12.2019
		    case 'npi':
		        break;
		
		    case 'gds':
		    case 'mmst'://TODO-2621
		        $empty_form = $this->_create_empty_form($options,$elementsBelongTo,'custom');
		        $this->addSubform($empty_form, 'empty_form');
		        break;
		    case 'bdi':
		        
// 		        $empty_form = $this->_create_empty_form($options,$elementsBelongTo,'custom');
// 		        $this->addSubform($empty_form, 'empty_form');
		        break;
		
		    default:
		
		        break;
		}
		
		// special title 
		
		switch ($elementsBelongTo){
		    case 'badl':
    		$this->addElement('note', 'subtitleh3', array(
    		    'value' => 'Skala zur Einschätzung der Alltagskompetenz',
    		    'decorators' => array(
    		        'ViewHelper',
    		        array(array('ltag' => 'HtmlTag'), array('tag' => 'h3', 'class'=>'fulldiv left'))
    		    ),
    		));
    		$this->addElement('note', 'subtitleh4', array(
    		    'value' => 'Fremdeinschätzung',
    		    'decorators' => array(
    		        'ViewHelper',
    		        array(array('ltag' => 'HtmlTag'), array('tag' => 'h4', 'class'=>'fulldiv left'))
    		    ),
    		));
		break;
		
		default:
		
		    break;
		}
		
		
		$patient_details = $this->_create_patient_details($options,$elementsBelongTo);
		$this->addSubform($patient_details, 'patient_details_header');
		
		//create 

 
		unset($options['ipid']);
		unset($options['create_date']);
		unset($options['create_user']);
		unset($options['change_date']);
		unset($options['change_user']);
		unset($options['isdelete']);
		
		$idq = 1;
 
		switch ($elementsBelongTo){
		    
		    case 'tug':
		        $form_content = $this->_create_formular_content_tug($options['formular'] , 'tug');
		        break;
		      
		        
		    case 'mmst':
		        $form_content = $this->_create_formular_content_mmst($options['formular'] , 'mmst');
		        break;
		        
		    case 'iadl':
		        $form_content = $this->_create_formular_content_iadl($options['formular'] , 'iadl');
		        break;
		        
		    case 'whoqol':
		        $form_content = $this->_create_formular_content_whoqol($options['formular'] , 'whoqol');
		        break;
		        
		    case 'demtect':
		        $form_content = $this->_create_formular_content_demtect($options['formular'] , 'demtect');
		        break;
		        
		    case 'carerelated':   //ISPC-2492 Lore 02.12.2019
		        $form_content = $this->_create_formular_content_carerelated($options['formular'] , 'carerelated');
		        break;
		    
		    case 'carepatient':   //ISPC-2493 Lore 03.12.2019
		        $form_content = $this->_create_formular_content_carepatient($options['formular'] , 'carepatient');
		        break;

	        case 'gds':
	            $form_content = $this->_create_formular_content_gds($options['formular'] , 'gds');
	            break;		        
	        case 'npi':
	            $form_content = $this->_create_formular_content_npi($options['formular'] , 'npi');
	            break;		        
		        
	        case 'bdi':
	            
	            //-- add new line
// 	            $empty_form = $this->_create_empty_form($options,$elementsBelongTo,'custom');
// 	            $this->addSubform($empty_form, 'empty_form');
	            
	            $form_content = $this->_create_formular_content_bdi($options['formular'] , 'bdi');
	            break;	
	            	        
	        case 'cmai':
	            $form_content = $this->_create_formular_content_cmai($options['formular'] , 'cmai');
	            break;		        
		        
	            
	        case 'nosger':
	            $form_content = $this->_create_formular_content_nosger($options['formular'] , 'nosger');
	            break;		        
		        
	            
	        case 'demstepcare':
	            $form_content = $this->_create_formular_content_demstepcare($options['formular'] , 'demstepcare', $options['extra_forms']);
	            break;		        
		        
	            
	            
	        case 'dsv':
	            // ISPC-2423
	            $form_content = $this->_create_formular_content_dsv($options['formular'] , 'dsv');
	            break;		        
		        
	        case 'badl':
	            // ISPC-2455
	            $form_content = $this->_create_formular_content_badl($options['formular'] , 'badl');
	            break;	

	        case 'cmscale':
	            //ISPC-2456
	            $form_content = $this->_create_formular_content_cmscale($options['formular'] , 'cmscale');
	            break;	

	        case 'dscdsv':
	            // ISPC-2509 Lore 06.01.2020
	            $form_content = $this->_create_formular_content_dscdsv($options['formular'] , 'dscdsv');
	            
	            break;
	            
		    default:  
		          		    
		        break;
		}
		
		//add form content with questions
		$this->addSubform($form_content, 'form_content');

		
		//add action buttons
		$actions = $this->_create_formular_actions($options['formular'] , 'formular');
		$this->addSubform($actions, 'form_actions');
	
		return $this;
	
	
	}
	
	
	/**
	 * @author Ancuta 
	 * 23.04.2019
	 * ISPC-2376 RUBIN - IADL
	 * @param unknown $options
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	
	private function _create_formular_content_iadl($options = array(), $elementsBelongTo = null){
	    
	    
// 	    dd($options);
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
	    ));
	    
	    $form_question[$elementsBelongTo] = array();
	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);

	    
	    
	    
	    
	    
	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);

	       $qtotal[$qinfo['question_id']] = 0 ; 
	       foreach($options[$qinfo['question_id']] as $opt_id=>$op_Value){
	           if($op_Value['checked'] == "yes" && $qtotal[$qinfo['question_id']] < 1){
	               $qtotal[$qinfo['question_id']] += $op_Value['value'];
	           }
	           
	       }
	        
	        
	        $subform->addElement('hidden', 'total_'.$qinfo['question_id'], array(
	            'value' => $qtotal[$qinfo['question_id']],
	            'class'=>'total_'.$qinfo['question_id'],
	            'decorators' => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'div_total_'.$qinfo['question_id']))
	            ),
	        ));
	         
	        
	    }
	    
	  /*   
	    $subform->addElement('note', 'ceva'.$options['id'], array(
	        'value' => 'Summe: <span class="total_slot">'.$options['form_total'].'</span> ',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
	        ),
	    ));
	    
	    $subform->addElement('hidden', 'form_total', array(
	        'value' => $options['form_total'],
	        'class'=>'form_total'
	    ));
	    
	     */
	    
	    



	    $subform->addElement('text', 'form_total', array(
	        'label' => 'Summe',
	        'value' => $options['form_total'],
	        'readonly'=> true,
	        'class'=> 'form_total',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
	        ),
	    ));
	     
	    
	    
	    return $subform;
	    
	}
	
	
	/**
	 * @author Ancuta
	 * 23.04.2019
	 *  ISPC-2375 RUBIN - MMST
	 * @param unknown $options
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	
	private function _create_formular_content_mmst($options = array(), $elementsBelongTo = null){
// 	    dd($options);
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
	    ));
	    
 
	    
	    $form_question[$elementsBelongTo] = array();
	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);

	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
	    }
	    

// 	    $subform->addElement('note', 'question_img', array(
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'q_img')),
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'q_img_block'))
// 	        ),
// 	    ));
	    //form_total
	    
// 	    $subform->addElement('note', 'total_row', array(
// 	        'value' => 'Gesamtpunktzahl <span class="total_slot">'.$options['form_total'].'</span> (max. 30)',
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
// 	        ),
// 	    ));
	    

// 	    $subform->addElement('hidden', 'form_total', array(
// 	        'value' => $options['form_total'],
// 	        'class'=>'form_total'
// 	    ));
	     
	    
	    
	    
	    
	    $subform->addElement('text', 'form_total', array(
	        'label' => 'Gesamtpunktzahl (max 30)',
	        'value' => $options['form_total'],
	        'readonly'=> true,
	        'class'=> 'form_total',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
	        ),
	    ));
	    
	    
	    
	    
	    $score_description = '<h5>Interpretation des Testergebnisses</h5>';
	    $score_description .= '<table class="score_desc" cellpadding="0" cellspacing="0">';
	    $score_description .= '<tr>';
	    $score_description .= '<th>Punkte</th>';
	    $score_description .= '<th>Beurteilung</th>';
	    $score_description .= '</tr>';
	    
	    $score_description .= '<tr>';
	    $score_description .= '<td>30-27</td>';
	    $score_description .= '<td>Keine Demenz</td>';
	    $score_description .= '</tr>';
	    
	    $score_description .= '<tr>';
	    $score_description .= '<td>26-20</td>';
	    $score_description .= '<td>Leichte  Demenz</td>';
	    $score_description .= '</tr>';
	    
	    $score_description .= '<tr>';
	    $score_description .= '<td>19-10</td>';
	    $score_description .= '<td>Mittelschwere Demenz</td>';
	    $score_description .= '</tr>';
	    
	    $score_description .= '<tr>';
	    $score_description .= '<td> &lt;= 9</td>';
	    $score_description .= '<td>Schwere Demenz</td>';
	    $score_description .= '</tr>';
	    
	    
	    $score_description .= '</table>';
	    
	    $subform->addElement('note', 'score_description', array(
	        'value' => $score_description,
	        'decorators' => array(
	            'ViewHelper',
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'score_description_block'))
	        ),
	    ));
	    
	    return $subform;
	    
	}

	/**
	 * @author Ancuta
	 * 23.04.2019
	 * ISPC-2377 RUBIN - WHOQOL
	 * @param unknown $options
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */	
	private function _create_formular_content_whoqol($options = array(), $elementsBelongTo = null){
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
	    ));
	    
	    $form_question[$elementsBelongTo] = array();
	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);

	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
	    }
 
	    
// 	    $subform->addElement('note', 'q_27', array(
// 	    	        'value' => 'Wie lange hat es gedauert, den Fragebogen auszufüllen?<input type="text" name="whoqol[form_content][q_27][opt_1]" id="whoqol-form_content-q_27-opt_1" value="'.$options['q_27']['opt_1']['value'].'" /> Minuten',
// 	    	        'decorators' => array(
// 	        	            'ViewHelper',
// 	        	            array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
// 	        	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'formular_questions qid-q_27'))
// 	        	        ),
// 	    	    ));
	    
	    
	    
	    
	    $subtable = new Zend_Form_SubForm();
	    //$subform->removeDecorator('Fieldset');
	    $subtable->setDecorators( array(
	        'FormElements',
	        array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'dem_tect_tablez', 'cellpadding'=>"0", 'cellspacing'=>"0")),
	    ));
	    
	    // frist row
	    $subsubrow1 = $this->subFormTableRow();
	    $elementDecorators = array();
	    $i = 0;
	    $elementDecorators[$i] = 'ViewHelper';
	    $i++;
	    $elementDecorators[$i] = array('Label', array('placement' => 'APPEND'));
	    $i++;
	    $elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
	    $i++;
	    $j = 0;
	    $elementDecorators[$i][$j]['data'] = 'HtmlTag';
	    $j++;
	    $elementDecorators[$i][$j]['tag'] = 'td';
	     
	    $subsubrow1->addElement('note', 'q27_text1', array(
	        'value' => 'Wie lange hat es gedauert, den Fragebogen auszufüllen?',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow1->addElement('text', 'opt_1', array(
	        'label'        => false,
	        'value'        => $options['q_27']['q_27_text']['value'],
	        'required'     => false,
	        'decorators' => $elementDecorators,
	    ));
	    
	    $subsubrow1->addElement('note', 'q27_text2', array(
	        'value' => 'Minuten',
	        'decorators' => $elementDecorators
	    ));
	    $subtable->addSubForm($subsubrow1, 'q_27_text');
	    
	    
	    $subform->addSubForm($subtable, 'q_27');
	     
	    
	    return $subform;
	}
	
	
	/**
	 * @author Ancuta 
	 * 23.04.2019
	 * ISPC-2377 RUBIN - TUG
	 * @param unknown $options
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	private function _create_formular_content_tug($options = array(), $elementsBelongTo = null){

	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
	    ));
	    
	    
	    
	    $subform->addElement('note', 'space', array(
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	     
	    //Form - text
	    $subform->addElement('note', 'tug_p1', array(
	        'value' => $this->translate('tug_p1'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
	        ),
	    ));
	    
	    $subform->addElement('note', 'tug_p2', array(
	        'value' => $this->translate('tug_p2'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
	        ),
	    ));
	    $subform->addElement('note', 'tug_p3', array(
	        'value' => $this->translate('tug_p3'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
	        ),
	    ));
	    
	    $subform->addElement('note', 'tug_p4', array(
	        'value' => $this->translate('tug_p4'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
	        ),
	    ));
	    
	    
	    $subform->addElement('note', 'space2', array(
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	    
	    
 
	    
	    // Question 1
	    $form_q1 = $this->_create_tug_q1($options  , 'tug');
	    $subform->addSubform($form_q1, 'q_1');
	    
	    // Question 2
	    $form_q2 = $this->_create_tug_q2($options  , 'tug');
	    $subform->addSubform($form_q2, 'q_2');
	     
	    
	    // Question 3
	    $form_q3 = $this->_create_tug_q3($options  , 'tug');
	    $subform->addSubform($form_q3, 'q_3');
	    
	    // Question 4
	    $form_q4 = $this->_create_tug_q4($options  , 'tug');
	    $subform->addSubform($form_q4, 'q_4');
	    
	     
	    
	    
	    $subform->addElement('note', 'space5', array(
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	    
	    return $subform;
	}
	
	
	private function _create_tug_q1($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_1')),
	    ));
	    
	     
 
	    
	    //LABEL
	    $subform->addElement('note', 'q_1_label', array(
	        'value' => 'Durchführung des TUG möglich?',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
	        ),
	    ));
	     
	    // RADIO
	    $subform->addElement('radio', 'opt_1', array(
	        'value' => $options['q_1']['opt_1']['checked'] == 'yes' ? $options['q_1']['opt_1']['value'] : 0,
	        'multiOptions' => array('1' => 'Nein','2' => 'Ja'),
	        'class' => 'calcscore yesno',
	        'onChange' => "calcscore(\$(this))",
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
	        ),
	    ));
	    
	    
		return $subform;
	
	}
	
	
	private function _create_tug_q2($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_2 tug_q2')),
	    ));
	    
	    //Patient hat _____ Sekunden gebraucht. (this is a text box, numbers only)
	    
	    $subform->addElement('note', 'q_2_front_text', array(
	        'value' => 'Patient hat ',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'q_2_front_text'))
	        ),
	    ));
	    
	    $subform->addElement('text', 'opt_1', array(
	        'label' => false,
	        'value' => !empty($options['q_2']['opt_1']['value']) ? $options['q_2']['opt_1']['value'] : '',
	        'onkeyup'=>"this.value=this.value.replace(/[^\d]/,'')",
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array('Errors'),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'q2_text')),
	        ),
	    ));
	     
	    
	    
	    $subform->addElement('note', 'q_2_end_text', array(
	        'value' => 'Sekunden gebraucht',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'q_2_end_text'))
	        ),
	    ));
	    
// 	    $subform->addElement('note', 'q_2', array(
// 	        'value' => 'Patient hat <input type="text" name="tug[form_content][q_2][opt_1]" value="'.$options['q_2']['opt_1']['value'].'">  Sekunden gebraucht',
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
// 	        ),
// 	    ));
	    
	    
	    
	    
// 	    $subform->addElement('note', 'q_2', array(
// 	        'value' => 'Patient hat <input type="text" name="tug[form_content][q_2][opt_1]" value="'.$options['q_2']['opt_1']['value'].'">  Sekunden gebraucht',
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
// 	        ),
// 	    ));
	     
 
		return $subform;
	
	}
	
	private function _create_tug_q3($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_3')),
	    ));
	    

	    //LABEL
	    $subform->addElement('note', 'q_3_label', array(
	        'value' => 'Hat der Patient eine Gehhilfe benutzt? ',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
	        ),
	    ));
	    
	    // RADIO
	    $subform->addElement('radio', 'opt_1', array(
	        'label'      => null,//$this->translate('enable/disable module'),
	        'required'   => false,
	        'value' => $options['q_3']['opt_1']['checked'] == 'yes' ? $options['q_3']['opt_1']['value'] : 0,
	        'multiOptions' => array('1' => 'Nein','2' => 'Ja'),
	        'class' => 'calcscore',
	        'onChange' => "show_extra(\$(this))",
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_APPEND')),
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
	        )
	    ));
	    
	    
	    //Patient hat folgende Gehhilfe benutzt:
	    
	    //Patient hat _____ Sekunden gebraucht. (this is a text box, numbers only)
// 	    $subform->addElement('text', 'extra_value', array(
// 	        'label' => 'Patient hat folgende Gehhilfe benutzt:',
// 	        'value' => !empty($options['q_3']['extra_value']['extra_value']) ? $options['q_3']['extra_value']['extra_value'] : '',
// 	            'decorators'   => array(
// 	                'ViewHelper',
// 	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
// 	                array('Errors'),
// 	                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'q_3_yes_text')),
// 	            ),
// 	    ));
	    
	    
		return $subform;
	
	}
	
	
	private function _create_tug_q4($options = array(), $elementsBelongTo = null)
	{
	    $vis_class = $options['q_3']['opt_1']['value'] == "2" ? "extraVisible " : "extrahiddenn ";
	        
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_4 '.$vis_class .' ')),
	    ));
	    

	    //Patient hat folgende Gehhilfe benutzt:
	    
	    //Patient hat _____ Sekunden gebraucht. (this is a text box, numbers only)
	    
	    $subform->addElement('text', 'opt_1', array(
	        'label' => 'Patient hat folgende Gehhilfe benutzt:',
	        'value' => !empty($options['q_4']['opt_1']['value']) ? $options['q_4']['opt_1']['value'] : '',
// 	        'class' => $options['q_3']['opt_1']['value'] == "2" ? "extraVisible " : "extrahiddenn " ,
	            'decorators'   => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'q_4_yes_text')),
	            ),
	    ));
	    
	    
		return $subform;
	
	}
	
	
	
	

	/**
	 * @author Ancuta
	 * 23.04.2019
	 * ISPC-2377 RUBIN - DemTect
	 * @param unknown $options
	 * @param string $elementsBelongTo
	 * @return Zend_Form_SubForm
	 */
	private function _create_formular_content_demtect($options = array(), $elementsBelongTo = null){
	
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
	    ));
	    
	     
	    // Question 1
	    $form_q1 = $this->_create_demtect_q1($options  , 'demtect');
	    $subform->addSubform($form_q1, 'demtect');
	    
	    // Question 2
	    $form_q2 = $this->_create_demtect_q2($options  , 'demtect');
	    $subform->addSubform($form_q2, 'form_content2');
	     
	    // Question 3
	    $form_q3 = $this->_create_demtect_q3($options  , 'demtect');
	    $subform->addSubform($form_q3, 'form_content3');
	     
	    // Question 4
	    $form_q4 = $this->_create_demtect_q4($options  , 'demtect');
	    $subform->addSubform($form_q4, 'form_content4');
	
	    // Question 5
	    $form_q5 = $this->_create_demtect_q5($options  , 'demtect');
	    $subform->addSubform($form_q5, 'form_content5');
	     
	     
	    $subform->addElement('note', 'space5', array(
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	     
	    

	    $subform->addElement('text', 'form_total', array(
	        'label' => 'Gesamtpunktzahl',
	        'value' => $options['form_total'],
	        'readonly'=> true,
	        'class'=> 'form_total',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
	        ),
	    ));
	     
	     
	     
	     
	    
	    
	    
	    
// 	    $subform->addElement('note', 'total_row', array(
// 	        'value' => 'Gesamtpunktzahl <span class="total_slot"></span>',
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
// 	        ),
// 	    ));
	     
// 	    $subform->addElement('hidden', 'form_total', array(
// 	        'value' => $options['total'],
// 	        'class'=>'form_total'
// 	    ));
	     
	     

	    $subform->addElement('note', 'space_demtec_qddddd1a', array(
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	     
// 	    $score_description = '<h5>Interpretation des Testergebnisses</h5>';
	    $score_description = '<br/>';
	    $score_description .= '<br/><br/><br/>';
	    $score_description .= '<table class="score_desc" cellpadding="0" cellspacing="0">';
	    $score_description .= '<tr>';
	    $score_description .= '<th>Punktzahl</th>';
	    $score_description .= '<th>Diagnose</th>';
	    $score_description .= '<th>Handlungsempfehlung</th>';
	    $score_description .= '</tr>';
	     
	    $score_description .= '<tr>';
	    $score_description .= '<td>13-18</td>';
	    $score_description .= '<td>Altersgemäße kognitive Leistung</td>';
	    $score_description .= '<td>Nach 12 Monaten bzw. bei Auftreten von Problemen erneut testen</td>';
	    $score_description .= '</tr>';
	     
	    $score_description .= '<tr>';
	    $score_description .= '<td>9-12</td>';
	    $score_description .= '<td>Leichte Kognitive Beeinträchtigung</td>';
	    $score_description .= '<td>Nach 6 Monaten erneut testen – Verlauf beobachten</td>';
	    $score_description .= '</tr>';
	     
	    $score_description .= '<tr>';
	    $score_description .= '<td>1 &lt; 8 </td>';
	    $score_description .= '<td>Demenzverdacht</td>';
	    $score_description .= '<td>Weitere diagnostische Abklärung, Therapie einleiten</td>';
	    $score_description .= '</tr>';
	    $score_description .= '</table>';

 
	    $subform->addElement('note', 'score_description', array(
	        'value' => $score_description,
	        'decorators' => array(
	            'ViewHelper',
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'score_description_block'))
	        ),
	    ));
	     
	    
	    return $subform;
	}
	
	private function _create_demtect_q1($options = array() )
	{

	    $demtect['q_1'] = array(
	        "Teller",
	        "Hund",
	        "Lampe",
	        "Brief",
	        "Apfel",
	        "Hose",
	        "Tisch",
	        "Wiese",
	        "Glas",
	        "Baum"
	    );
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_1')),
	    ));
	     
	
 
	    //LABEL
        $subform->addElement('note', 'q_1_label', array(
	        'value' => '1. Wortliste',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text paddingLeft'))
	        ),
	    ));
        
	    $subform->addElement('note', 'q_1_subtext', array(
	        'value' => '"Ich werde Ihnen jetzt langsam eine Liste von 10 Worten vorlesen. Danach wiederholen Sie bitte möglichst viele dieser Worte. Auf die Reihenfolge kommt es nicht an." (erste Wortliste)',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_free_text')),
	        ),
	    ));
	    	
	    
	    $subform_chk = new Zend_Form_SubForm();
// 	    $subform_chk->removeDecorator('Fieldset');
	    $subform_chk->addDecorator('HtmlTag', array('tag' => 'div', 'class'=>'fake_table demtect_q1' ));
	    $subform_chk->setAttrib("class", "fake_table_fieldset");
	    $demtect['q_1'] = array(
	        "Teller",
	        "Hund",
	        "Lampe",
	        "Brief",
	        "Apfel",
	        "Hose",
	        "Tisch",
	        "Wiese",
	        "Glas",
	        "Baum"
	    );

	    $qident = 1;
	    foreach ( $demtect['q_1']  as $q_opt1_k => $q_opt) {
	        $subform_chk->addElement('checkbox', 'opt_'.$qident, array(
	            'checkedValue'    => 1,
	            'uncheckedValue'  => '0',
	            'label'      => '<span>'.$q_opt.'</span><br/>', // NOT OK - Please FIX !!!
	            'required'   => false,
	            'value' => $options['q_1']['opt_'.$qident]['checked'] == 'yes' ? $options['q_1']['opt_'.$qident]['value'] : 0,
	            'decorators'   => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'inline-chk')),
	            ),
	            'class' =>'calculate_score',
	            'data-score' => 1,
	            'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_1')"
	        ));
	        $qident++;
	    }
	    
	    $subform->addSubform($subform_chk, 'q_1');
	    
	    $subform->addElement('note', 'space_demtec_q1a', array(
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	    
	    $subform->addElement('note', 'q_1_subtext2', array(
	        'value' => '"Vielen Dank. Nun nenne ich Ihnen die gleichen 10 Worte ein zweites Mal. Auch danach sollen Sie wieder möglichst viele Worte wiederholen."',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_free_text')),
	        ),
	    ));
	    
	    
	    
	    //Q 1 A
	    $subform_chka = new Zend_Form_SubForm();
	    $subform_chka->removeDecorator('Fieldset');
	    $subform_chka->addDecorator('HtmlTag', array('tag' => 'div', 'class'=>'fake_table demtect_q1a' ));
	    $subform_chka->setAttrib("class", "label_same_sizeb {$__fnName}");
	    $demtect['q_1'] = array(
	        "Teller",
	        "Hund",
	        "Lampe",
	        "Brief",
	        "Apfel",
	        "Hose",
	        "Tisch",
	        "Wiese",
	        "Glas",
	        "Baum"
	    );
	    
	    $q2ident = 1;
	    foreach ( $demtect['q_1']  as $q_opt_k => $q_opt) {
	        $subform_chka->addElement('checkbox', 'opt_'.$q2ident, array(
	            'checkedValue'    => 1,
	            'uncheckedValue'  => '0',
	            'label'      => '<span>'.$q_opt.'</span><br/>', // NOT OK - Please FIX !!!
	            'required'   => false,
                'value' => $options['q_1_a']['opt_'.$q2ident]['checked'] == 'yes' ? $options['q_1_a']['opt_'.$q2ident]['value'] : 0,
	            'decorators'   => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'inline-chk')),
	            ),
	            'class' =>'calculate_score',
	            'data-score' => 1,
	            'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_1')"
	        ));
	        $q2ident++;
	    }
	     
// 	    dd($options);
	    // calculate value
	    
	     
	    $subform->addSubform($subform_chka, 'q_1_a');

	    $subform->addElement('text', 'q_1_total', array(
	        'label' => 'Richtig erinnerte Begriffe (max. 20)',
	        'value' => $options['q_1']['total']['value'],
	        'readonly'=> true,
	        'class'=> 'q_1_total all_q_total',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total_div')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total'))
	        ),
	    ));
	     
	    
	     
	    return $subform;
	
	}
		
	
	
	
	
	
	
	private function _create_demtect_q2($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_2')),
	    ));
	     
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	     
	    //LABEL
	    $subform->addElement('note', 'q_2_label', array(
	        'value' => '2. Zahlen-Umwandeln',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text paddingLeft'))
	        ),
	    ));
	    
	    $subform->addElement('note', 'q_2_subtext', array(
	        'value' => '"Wie Sie in dem Beispiel sehen können, kann man die Ziffer "5" auch als Wort "fünf" schreiben und das Wort "drei" auch als Ziffer "3" schreiben. Ein Teil der Aufgabe ist so, wie wenn Sie einen Scheck ausfüllen würden. Ich bitte Sie nun, die Ziffern in Worte und die Worte in Ziffern zu schreiben." Beispiel 5 -> fünf drei -> 3',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
	        ),
	    ));
	    	
	    
	    
	    
	    $subform_chka = new Zend_Form_SubForm();
	    $subform_chka->removeDecorator('Fieldset');
	    $subform_chka->addDecorator('HtmlTag', array('tag' => 'div', 'class'=>'fake_table demtect_q1a' ));
	    $subform_chka->setAttrib("class", "label_same_sizeb {$__fnName}");
	    
	    $demtect['q_2'] = array(
             "209",
            "4054",
            "Sechshunderteinundachtzig",
            "Zweitausendsiebenundzwanzig",
	    );
	    
	    // CHECKBOXES
	    $q_ident = 1 ;
	    foreach ($demtect['q_2'] as $q_opt_k => $q_opt) {
	        $subform_chka->addElement('checkbox', 'opt_'.$q_ident, array(
	            'checkedValue'    => 1,
	            'uncheckedValue'  => '0',
	            'label'      => $q_opt,
	            'required'   => false,
	            'value' => $options['q_2']['opt_'.$q_ident]['checked'] == 'yes' ? $options['q_2']['opt_'.$q_ident]['value'] : 0,
	            'decorators'   => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND' )),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_large_row')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'inline-large-chk')),
	            ),
	            'class' =>'calculate_score',
	            'data-score' => 1,
	            'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_2')"
	        ));
	        $q_ident++;
	    }
	    
	    $subform->addSubform($subform_chka, 'q_2');
	    
 
	    $subform->addElement('note', 'space_q2ab', array(
	        'value'=>'<br/><br/>',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	    
	    
	    $subform->addElement('text', 'q_2_total', array(
	        'label' => 'Richtige Antwort (max. 4)',
	        'value' => $options['q_2']['total']['value'],
	        'readonly'=> true,
	        'class'=> 'q_2_total all_q_total',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total_div')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total'))
	        ),
	    ));
	    
	    

	    $subform->addElement('note', 'space_q2a', array(
	        'value'=>'',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	     
	    
	     
	    return $subform;
	}
		
	
	
	private function _create_demtect_q3($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_3')),
	    ));
	     
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	     
	    //LABEL
	    $subform->addElement('note', 'q_3_label', array(
	        'value' => '3. Supermarktaufgabe',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text paddingLeft'))
	        ),
	    ));
	    
	    $subform->addElement('note', 'q_3_subtext', array(
	        'value' => '"Nennen Sie mir bitte so viele Dinge wie möglich, die man im Supermarkt kaufen kann. Sie haben dafür eine Minute Zeit."',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
	        ),
	    ));
	    
	    $subform_increment = new Zend_Form_SubForm();
	    $subform_increment->removeDecorator('Fieldset');
	    $subform_increment->addDecorator('HtmlTag', array('tag' => 'table', 'class'=>'demtec_table_supermarket','cellspacing'=>'0','cellpadding'=>'0' ));
	    $subform_increment->setAttrib("class", "label_same_size {$__fnName}");
	    
	    // Minus button
	    $subform_increment->addElement('note', 'q3minus', array(
	        'value' => "-",
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('tag' => 'button','type'=>'button', 'class'=>'q3minus_button',
	                
	                'data-score' => 1,
	                'data-action'=>'substract',
	                'onClick'=>"calculate_q_score(\$(this),'q_3')")),
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'td', 'class'=>'check_td'))
	        ),
	        'class' =>'calculate_score',
	        'data-score' => 1,
	    ));
	    
	    
	    // Score input
	    $subform_increment->addElement('text', 'opt_1', array(
	        'value' => isset($options['q_3']['opt_1']['value']) && !empty($options['q_3']['opt_1']['value']) ? $options['q_3']['opt_1']['value'] : '0',
	        'readonly'=> true,
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array('class'=>'input_q3_text')),
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'td', 'class'=>'check_td'))
	        ),
	        'class' =>'demTec_q3input'
	    ));
	    
	    
        // Plus Button
	    $subform_increment->addElement('note', 'q3plus', array(
	        'value' => "+",
	        'decorators'   => array(
	            'ViewHelper',
	             array(array('data' => 'HtmlTag'), array('tag' => 'button','type'=>'button', 'class'=>'q3plus_button',
	                 'data-score' => 1,
	                 'data-action'=>'add',
	                 'onClick'=>"calculate_q_score(\$(this),'q_3')")),
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'td', 'class'=>'check_td'))
	        ),
	        'class' =>'calculate_score ',
	        'data-score' => 1,
	    ));

	    $subform->addSubform($subform_increment, 'q_3');
	    

	    $subform->addElement('text', 'q_3_total', array(
	        'label' => 'Genannte Begriffe (max. 30)',
	        'value' => isset($options['q_3']['opt_1']['value']) ? $options['q_3']['opt_1']['value'] : '0',
	        'readonly'=> true,
	        'class'=> 'q_3_total all_q_total',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total_div')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total'))
	        ),
	    ));
	    
	     
	    
	    
	    return $subform;
	
	}
		
	
	private function _create_demtect_q4($options = array(), $elementsBelongTo = null)
	{

	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_4')),
	    ));
	     
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	     
	    //LABEL
	    $subform->addElement('note', 'q_4_label', array(
	        'value' => '4. Zahlenfolge rückwärts',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text paddingLeft'))
	        ),
	    ));
	    
	    $subform->addElement('note', 'q_4_subtext', array(
	        'value' => '"Ich werde Ihnen jetzt eine Zahlenreihe nennen, die Sie mir dann bitte in Umgekehrter Reihenfolge wiederholen sollen. Wenn ich beispielsweise "vier-fünf" sage, dann sagen Sie bitte "fünf-vier" "',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
	        ),
	    ));
	    

	    
	    
	    

	    $subtable = new Zend_Form_SubForm();
	    //$subform->removeDecorator('Fieldset');
	    $subtable->setDecorators( array(
	        'FormElements',
	        array('table' => 'HtmlTag', array('tag'=>'table', 'class'=>'dem_tect_table', 'cellpadding'=>"0", 'cellspacing'=>"0", "style"=>"width: 400px;")),
	        //'Fieldset',
	    ));
	    
	    $subsubrow = $this->subFormTableRow();
	    	
	    $elementDecorators = array();
	    $i = 0;
	    $elementDecorators[$i] = 'ViewHelper';
	    $i++;
	    $elementDecorators[$i] = array('Label', array('placement' => 'APPEND'));
	    $i++;
	    $elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
	    $i++;
	    $j = 0;
	    $elementDecorators[$i][$j]['data'] = 'HtmlTag';
	    $j++;
	    
	    $elementDecorators[$i][$j]['tag'] = 'th';
	    $subsubrow->addElement('note', 'q4_header_o1', array(
	        'value' => '1. Versuch',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow->addElement('note', 'q4_header_o2', array(
	        'value' => '2. Versuch',
	        'decorators' => $elementDecorators
	    ));
	    $subsubrow->addElement('note', 'q4_header_p', array(
	        'value' => 'Punkte',
	        'decorators' => $elementDecorators
	    ));
	    $subsubrow->addElement('note', 'q4_header_chk', array(
	        'value' => '&nbsp;',
	        'decorators' => $elementDecorators
	    ));
	    $subtable->addSubForm($subsubrow, 'q4_table_header');

	    
	    
	   // frist row 
	    $subsubrow1 = $this->subFormTableRow();
	    $elementDecorators = array();
	    $i = 0;
	    $elementDecorators[$i] = 'ViewHelper';
	    $i++;
	    $elementDecorators[$i] = array('Label', array('placement' => 'APPEND'));
	    $i++;
	    $elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
	    $i++;
	    $j = 0;
	    $elementDecorators[$i][$j]['data'] = 'HtmlTag';
	    $j++;
	    $elementDecorators[$i][$j]['tag'] = 'td';
	    	
	    $subsubrow1->addElement('note', 'q4_1_o1', array(
	        'value' => '7-2',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow1->addElement('note', 'q4_1_o2', array(
	        'value' => '8-6',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow1->addElement('note', 'q4_1_p', array(
	        'value' => '2',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow1->addElement('checkbox', 'opt_1', array(
	        'label'        => false,
	        'checkedValue'    => 2,
	        'uncheckedValue'  => '0',
	        'value' => $options['q_4']['opt_1']['checked'] == 'yes' ? $options['q_4']['opt_1']['value'] : 0,
	        'required'     => false,
	        'class'     => 'q_4_calc',
	        'decorators' => $elementDecorators,
	        'style' => 'margin: 0px; border: 0px; background: #fff; font-size: 16px; padding: 0px;border-bottom: 1px solid #000; width: 20px;',
	        'data-score' => '2',
	        'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_4')"
	    ));
	    $subtable->addSubForm($subsubrow1, 'opt_1');
	    
	    
	   
	   // second row 
	    $subsubrow2 = $this->subFormTableRow();
	    $elementDecorators = array();
	    $i = 0;
	    $elementDecorators[$i] = 'ViewHelper';
	    $i++;
	    $elementDecorators[$i] = array('Label', array('placement' => 'APPEND'));
	    $i++;
	    $elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
	    $i++;
	    $j = 0;
	    $elementDecorators[$i][$j]['data'] = 'HtmlTag';
	    $j++;
	    $elementDecorators[$i][$j]['tag'] = 'td';
	    	
	    $subsubrow2->addElement('note', 'q4_2_o1', array(
	        'value' => '4-7-9 ',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('note', 'q4_2_o2', array(
	        'value' => '3-1-5',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('note', 'q4_2_p', array(
	        'value' => '3',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('checkbox', 'opt_2', array(
	        'label'        => false,
	        'checkedValue'    => 3,
	        'uncheckedValue'  => '0',
	        'value' => $options['q_4']['opt_2']['checked'] == 'yes' ? $options['q_4']['opt_2']['value'] : 0,
	        'required'     => false,
	        'class'     => 'q_4_calc',
	        'decorators' => $elementDecorators,
	        'style' => 'margin: 0px; border: 0px; background: #fff; font-size: 16px; padding: 0px;border-bottom: 1px solid #000; width: 20px;',
	        'data-score' => '3',
	        'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_4')"
	    ));
	    $subtable->addSubForm($subsubrow2, 'opt_2');
	    
	   // third row 
	    $subsubrow2 = $this->subFormTableRow();
	    $elementDecorators = array();
	    $i = 0;
	    $elementDecorators[$i] = 'ViewHelper';
	    $i++;
	    $elementDecorators[$i] = array('Label', array('placement' => 'APPEND'));
	    $i++;
	    $elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
	    $i++;
	    $j = 0;
	    $elementDecorators[$i][$j]['data'] = 'HtmlTag';
	    $j++;
	    $elementDecorators[$i][$j]['tag'] = 'td';
	    	
	    $subsubrow2->addElement('note', 'q4_3_o1', array(
	        'value' => '5-4-9-6',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('note', 'q4_3_o2', array(
	        'value' => '1-9-7-4',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('note', 'q4_3_p', array(
	        'value' => '4',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('checkbox', 'opt_3', array(
	        'label'        => false,
	        'checkedValue'    => 4,
	        'uncheckedValue'  => '0',
	        'value' => $options['q_4']['opt_3']['checked'] == 'yes' ? $options['q_4']['opt_3']['value'] : 0,
	        'required'     => false,
	        'class'     => 'q_4_calc',
	        'decorators' => $elementDecorators,
	        'style' => 'margin: 0px; border: 0px; background: #fff; font-size: 16px; padding: 0px;border-bottom: 1px solid #000; width: 20px;',
	        'data-score' => '4',
	        'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_4')"
	    ));
	    $subtable->addSubForm($subsubrow2, 'opt_3');
	    
	   // forth row 
	    $subsubrow2 = $this->subFormTableRow();
	    $elementDecorators = array();
	    $i = 0;
	    $elementDecorators[$i] = 'ViewHelper';
	    $i++;
	    $elementDecorators[$i] = array('Label', array('placement' => 'APPEND'));
	    $i++;
	    $elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
	    $i++;
	    $j = 0;
	    $elementDecorators[$i][$j]['data'] = 'HtmlTag';
	    $j++;
	    $elementDecorators[$i][$j]['tag'] = 'td';
	    	
	    $subsubrow2->addElement('note', 'q4_4_o1', array(
	        'value' => '2-7-5-3-6',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('note', 'q4_4_o2', array(
	        'value' => '1-3-5-4-8',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('note', 'q4_4_p', array(
	        'value' => '5',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('checkbox', 'opt_4', array(
	        'label'        => false,
	        'checkedValue'    => 5,
	        'uncheckedValue'  => '0',
            'value' => $options['q_4']['opt_4']['checked'] == 'yes' ? $options['q_4']['opt_4']['value'] : 0,
	        'required'     => false,
	        'class'     => 'q_4_calc',
	        'decorators' => $elementDecorators,
	        'style' => 'margin: 0px; border: 0px; background: #fff; font-size: 16px; padding: 0px;border-bottom: 1px solid #000; width: 20px;',
	        'data-score' => '5',
	        'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_4')"
	    ));
	    $subtable->addSubForm($subsubrow2, 'opt_4');
	    
	    
	    
	   // fifth row 
	    $subsubrow2 = $this->subFormTableRow();
	    $elementDecorators = array();
	    $i = 0;
	    $elementDecorators[$i] = 'ViewHelper';
	    $i++;
	    $elementDecorators[$i] = array('Label', array('placement' => 'APPEND'));
	    $i++;
	    $elementDecorators[$i] = array(array('adiv' => 'HtmlTag'), array('tag' => 'div', 'style' => 'width: 100%;'));
	    $i++;
	    $j = 0;
	    $elementDecorators[$i][$j]['data'] = 'HtmlTag';
	    $j++;
	    $elementDecorators[$i][$j]['tag'] = 'td';
	    	
	    $subsubrow2->addElement('note', 'q4_5_o1', array(
	        'value' => '8-1-3-5-4-2',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('note', 'q4_5_o2', array(
	        'value' => '4-1-2-7-9-5',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('note', 'q4_5_p', array(
	        'value' => '6',
	        'decorators' => $elementDecorators
	    ));
	    
	    $subsubrow2->addElement('checkbox', 'opt_5', array(
	        'label'        => false,
            'checkedValue'    => 6,
            'uncheckedValue'  => '0',
	        'value' => $options['q_4']['opt_5']['checked'] == 'yes' ? $options['q_4']['opt_5']['value'] : 0,
	        'required'     => false,
	        'class'     => 'q_4_calc',
	        'decorators' => $elementDecorators,
	        'style' => 'margin: 0px; border: 0px; background: #fff; font-size: 16px; padding: 0px;border-bottom: 1px solid #000; width: 20px;',
	        'data-score' => '6',
	        'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_4')"
	        
	    ));
	    $subtable->addSubForm($subsubrow2, 'opt_5');
	    
	    $subform->addSubForm($subtable, 'q_4');
	    
	    
	    

	    $subform->addElement('note', 'space_q4zzzzzzz', array(
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
	        ),
	    ));
	    
// 	    $subform->addElement('note', 'br', array(
// 	        'value' => '<br/>',
// 	        'decorators' => array(
// 	            'ViewHelper',
// 	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_inline_total'))
// 	        ),
// 	    ));
	    
// 	    $subform->addElement('hidden', 'q_4_total', array(
// 	        'value' => $options['total'],
// 	        'class'=>'form_total'
// 	    ));
	    
	    
	    $subform->addElement('text', 'q_4_total', array(
	        'label' => 'Längste richtige rückwärts wiederholte Zahlenfolge (max. 6)',
	        'value' => $options['q_4']['total']['value'],
	        'readonly'=> true,
	        'class'=> 'q_4_total all_q_total',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total_div')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total'))
	        ),
	    ));
	    
	     
	    
	    
	    
	    return $subform;
	}
	
	
	
	
	private function _create_demtect_q5($options = array(), $elementsBelongTo = null)
	{
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-q_5')),
	    ));
	     
	
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	     
	    //LABEL
	    $subform->addElement('note', 'q_5_label', array(
	        'value' => '5. Erneute Abfrage der Wortliste',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text paddingLeft'))
	        ),
	    ));
	    
	    $subform->addElement('note', 'q_5_subtext', array(
	        'value' => '"Ganz am Anfang des Tests habe ich Ihnen 10 Worte genannt. Können sie sich noch an diese Worte erinnern?"',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('ltag' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
	        ),
	    ));
	    
	    
	    


	    $subform_chka = new Zend_Form_SubForm();
	    $subform_chka->removeDecorator('Fieldset');
	    $subform_chka->addDecorator('HtmlTag', array('tag' => 'div', 'class'=>'fake_table demtect_q5' ));
	    $subform_chka->setAttrib("class", "q5 {$__fnName}");
	    $demtect['q_1'] = array(
	        "Teller",
	        "Hund",
	        "Lampe",
	        "Brief",
	        "Apfel",
	        "Hose",
	        "Tisch",
	        "Wiese",
	        "Glas",
	        "Baum"
	    );
	     
	    $qident=1;
	    foreach ( $demtect['q_1']  as $q_opt_k => $q_opt) {
	        $subform_chka->addElement('checkbox', 'opt_'.$qident, array(
	            'checkedValue'    => 1,
	            'uncheckedValue'  => '0',
	            'label'      => '<span>'.$q_opt.'</span><br/>', // NOT OK - Please FIX !!!
	            'required'   => false,
	            //'value' => isset($values[$cb]) ? $values[$cb] : 0,
	            'value' => $options['q_5']['opt_'.$qident]['checked'] == 'yes' ? $options['q_5']['opt_'.$qident]['value'] : 0,
	            'decorators'   => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
	                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'inline-chk')),
	            ),
	            'class' =>'calculate_score',
	            'data-score' => 1,
    	        'onChange' => "calculate_score(\$(this),'checkbox'), calculate_q_score(\$(this),'q_5')"
	        ));
	        $qident++;
	    }
	    
	    $subform->addSubform($subform_chka, 'q_5');
	    
	    /* 
	    $subform->addElement('note', 'q5_total_row', array(
	        'value' => 'Richtig erinnerte Begriffe (max. 10) <span class="inline_total_slot ARight"></span>',
	        'decorators' => array(
	            'ViewHelper',
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_inline_total'))
	        ),
	    ));
	    
	    $subform->addElement('hidden', 'q_5_total', array(
	        'value' => $options['total'],
	        'class'=>'form_total'
	    ));
	    
	     */

	    $subform->addElement('text', 'q_5_total', array(
	        'label' => 'Richtig erinnerte Begriffe (max. 10)',
	        'value' => $options['q_5']['total']['value'],
	        'readonly'=> true,
	        'class'=> 'q_5_total all_q_total',
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total_div')),
	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'question_total'))
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
			
// 		 $el = $this->createElement('button', 'button_action', array(
// 			 'type'         => 'submit',
// 			 'value'        => 'printpdf',
// 			 // 	        'content'      => $this->translate('submit'),
// 			 'label'        => $this->translator->translate('kh_generate_pdf'),
// 			 // 	        'onclick'      => '$(this).parents("form").attr("target", "_self"); return checkclientchanged(\'wlassessment_form\');',
// 			 'onclick'      => '$(this).parents("form").attr("target", "_self"); window.formular_button_action = this.value;',
// 			 'decorators'   => array('ViewHelper'),
	
// 		));
// 		$subform->addElement($el, 'printpdf'); 
			
			
		return $subform;
	
	}
	
	public function save_form_rubin($ipid = null, array $data = array())
	{
		if (empty($ipid)) {
			throw new Exception('Contact Admin, formular cannot be saved.', 0);
		}
		
// 		dd(func_get_args());
// 		dd($ipid,$data);
		//print_r($data); exit;
		if($data['form_id'] == '')
		{
			$data['form_id'] = null;
		}
		
		switch ($data['form_ident']){
		    case 'mmst':
		    case 'iadl':
		    case 'whoqol':
		    case 'tug':
        		$from_data['form_id'] = $data['form_id'];
        		$from_data['form_type'] = $data['form_ident'];
        		$from_data['form_total'] = $data['form_content']['form_total'];
        		$from_data['form_date'] = !empty($data[$data['form_ident']]['form_date']) ? date("Y-m-d H:i:s",strtotime($data[$data['form_ident']]['form_date'])) : date("Y-m-d H:i:s");

        		// save in forms
        		$entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
        		
        		// save in form answer
        		$form_id = $entity->id;
        		
            	if(!empty($form_id)){
            	    
            	    // 
            	    
            	    $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormId($form_id);
            	    if($answers_array){
            	        
                	    foreach ($answers_array as $answer_tab){
                	        $answer_tab->delete();
                	    }
            	    }
            	    
            		$answers = array();
//             		dd($data['form_content']);
            		foreach($data['form_content'] as $q_ident=>$option_vals){
            		     foreach($option_vals as $opt_ident=>$option_value){
            		         
            		         if($data['form_ident'] =='whoqol' && $q_ident == "q_27" && is_array($option_value) ){
            		             $option_value = $option_value['opt_1']; // STUPID HACK Please Change
            		         }
            		         
            		         
            		         $answers[]=array(
    
            		             'ipid'=>$ipid,
            		             'form_type'=>$from_data['form_type'],
            		             'form_id'=>$form_id,
            		             'question_id'=>$q_ident,
            		             'question_option'=>$opt_ident,
            		             'option_checked'=> $option_value != 0 ? 'yes' :'no',
            		             'option_value'=> $option_value,
            		             
            		         );
            		     }
            		}
            		$obj = new Doctrine_Collection('PatientRubinFormsAnswers');
            		$obj->fromArray($answers);
            		$obj->save();
        		}
        		

        		return $entity;
        		
		        break;
		        
		    case 'demtect':
        		$from_data['form_id'] = $data['form_id'];
        		$from_data['form_type'] = $data['form_ident'];
        		$from_data['form_content'] = $data['form_content']['demtect'];
        		$from_data['form_total'] = $data['form_content']['form_total'];
        		
//                 dd($data);
        		
        		// save in forms
        		$entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
        		
        		// save in form answer
        		$form_id = $entity->id;
        		
            	if(!empty($form_id)){
            	    
            	    // 
            	    
            	    $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormIdAndFormType($form_id,$data['form_ident']);
            	    if($answers_array){
            	        
                	    foreach ($answers_array as $answer_tab){
                	        $answer_tab->delete();
                	    }
            	    }
            	    
            		$answers = array();

            		foreach($from_data['form_content'] as $q_ident=>$option_vals){
            		    if(strpos($q_ident,'total') === false)
            		    {
            		     foreach($option_vals as $opt_ident=>$option_value){
            		     if($q_ident == 'q_4'&& is_array($option_value)){

            		         foreach($option_value as $s_opt_ident=>$s_option_value){
            		             
            		         $answers[]=array(
            		             'ipid'=>$ipid,
            		             'form_type'=>$from_data['form_type'],
            		             'form_id'=>$form_id,
            		             'question_id'=>$q_ident,
            		             'question_option'=>$s_opt_ident,
            		             'option_checked'=> $s_option_value != 0 ? 'yes' :'no',
            		             'option_value'=> $s_option_value,
            		         );
            		         }
            		         
            		         
            		     } else{
            		         
            		         $answers[]=array(
            		             'ipid'=>$ipid,
            		             'form_type'=>$from_data['form_type'],
            		             'form_id'=>$form_id,
            		             'question_id'=>$q_ident,
            		             'question_option'=>$opt_ident,
            		             'option_checked'=> $option_value != 0 ? 'yes' :'no',
            		             'option_value'=> $option_value,
            		         );
            		     }
            		     }
            		  } else {
            		      
            		      $q_ident_total = str_replace('_total',"", $q_ident);
            		      
            		      $answers[]=array(
            		          'ipid'=>$ipid,
            		          'form_type'=>$from_data['form_type'],
            		          'form_id'=>$form_id,
            		          'question_id'=> $q_ident_total,
            		          'question_option'=>'total',
            		          'option_checked'=> $option_vals != 0 ? 'yes' :'no',
            		          'option_value'=> $option_vals,
            		      );
            		      
            		      
            		  }
            		  
            		}
            		
            		$obj = new Doctrine_Collection('PatientRubinFormsAnswers');
            		$obj->fromArray($answers);
            		$obj->save();
        		}
        		

        		return $entity;
        		
		        break;
		        
		        //ISPC-2492 Lore 02.12.2019
		    case 'carerelated':
		        
		       $from_data['form_id'] = $data['form_id'];
		       $from_data['form_type'] = $data['form_ident'];
		       $from_data['form_total'] = $data['form_content']['form_total'];
		       $from_data['form_date'] = !empty($data[$data['form_ident']]['form_date']) ? date("Y-m-d H:i:s",strtotime($data[$data['form_ident']]['form_date'])) : date("Y-m-d H:i:s");
		            
		       
		       // save in forms
		       $entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
		       
		       // save in form answer
		       $form_id = $entity->id;
		       
		       if(!empty($form_id)){
		           
		           $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormId($form_id);
		           if($answers_array){
		               
		               foreach ($answers_array as $answer_tab){
		                   $answer_tab->delete();
		               }
		           }
		           
		           $answers = array();
		           
		           foreach($data['form_content'] as $q_ident=>$option_vals){
		               
		               if($q_ident != "nosger_scores"){
		                   
		                   foreach($option_vals as $opt_ident=>$option_value){
		                       
		                       $answers[]=array(
		                           'ipid'=>$ipid,
		                           'form_type'=>$from_data['form_type'],
		                           'form_id'=>$form_id,
		                           'question_id'=>$q_ident,
		                           'question_option'=>$opt_ident,
		                           'option_checked'=> $option_value != 0 ? 'yes' :'no',
		                           'option_value'=> $option_value,
		                       );
		                   }
		               }
		           }
		           $obj = new Doctrine_Collection('PatientRubinFormsAnswers');
		           $obj->fromArray($answers);
		           $obj->save();
		       }
		       
		       
		       return $entity;
		       
		       break;
		        
		   //ISPC-2493 Lore 03.12.2019
		    case 'carepatient':
		       
		        $from_data['form_id'] = $data['form_id'];
		        $from_data['form_type'] = $data['form_ident'];
		        $from_data['form_total'] = $data['form_content']['form_total'];
		        $from_data['form_date'] = !empty($data[$data['form_ident']]['form_date']) ? date("Y-m-d H:i:s",strtotime($data[$data['form_ident']]['form_date'])) : date("Y-m-d H:i:s");
		        
		        
		        // save in forms
		        $entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
		        
		        // save in form answer
		        $form_id = $entity->id;
		        
		        if(!empty($form_id)){
		            
		            $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormId($form_id);
		            if($answers_array){
		                
		                foreach ($answers_array as $answer_tab){
		                    $answer_tab->delete();
		                }
		            }
		            
		            $answers = array();
		            
		            foreach($data['form_content'] as $q_ident=>$option_vals){
		                
		                if($q_ident != "nosger_scores"){
		                    
		                    foreach($option_vals as $opt_ident=>$option_value){
		                        
		                        $answers[]=array(
		                            'ipid'=>$ipid,
		                            'form_type'=>$from_data['form_type'],
		                            'form_id'=>$form_id,
		                            'question_id'=>$q_ident,
		                            'question_option'=>$opt_ident,
		                            'option_checked'=> $option_value != 0 ? 'yes' :'no',
		                            'option_value'=> $option_value,
		                        );
		                    }
		                }
		            }
		            $obj = new Doctrine_Collection('PatientRubinFormsAnswers');
		            $obj->fromArray($answers);
		            $obj->save();
		        }
		        
		        
		        return $entity;
		        
		        break;
		        
		        
		        
		        
		        
		        
		    default:
		        break;
		}
		
		

        
//         dd($entity->id);
		// save in forms
		
		
		
		
		
		

	}
	
	
	/**
	 * DemStepCare
	 * @auth Ancuta
	 * 12.07.2019
	 * ISPC-2402
	 * ISPC-2403
	 * ISPC-2404
	 * ISPC-2455 Lore (badl)
	 * @param string $ipid
	 * @param array $data
	 * @throws Exception
	 * @return Doctrine_Record
	 */
	public function save_form_DemStepCare($ipid = null, array $data = array())
	{
	   // dd(func_get_args());
		if (empty($ipid)) {
			throw new Exception('Contact Admin, formular cannot be saved.', 0);
		}
		
		if($data['form_id'] == '')
		{
			$data['form_id'] = null;
		}
		
		switch ($data['form_ident']){
		    case 'npi':
		    case 'bdi':
		    case 'gds':
		    case 'cmai':
		    case 'badl':
		    case 'cmscale':    
		    case 'nosger':
        		$from_data['form_id'] = $data['form_id'];
        		$from_data['form_type'] = $data['form_ident'];
        		$from_data['form_total'] = $data['form_content']['form_total'];
        		$from_data['form_date'] = !empty($data[$data['form_ident']]['form_date']) ? date("Y-m-d H:i:s",strtotime($data[$data['form_ident']]['form_date'])) : date("Y-m-d H:i:s");

        		if($data['form_ident'] == "nosger"){
        		    
        		    $from_data['score_memory']     = $data['form_content']['nosger_scores']['score_memory'];
        		    $from_data['score_iadl']       = $data['form_content']['nosger_scores']['score_iadl'];
        		    $from_data['score_adl']        = $data['form_content']['nosger_scores']['score_adl'];
        		    $from_data['score_mood']       = $data['form_content']['nosger_scores']['score_mood'];
        		    $from_data['score_social']     = $data['form_content']['nosger_scores']['score_social'];
        		    $from_data['score_disturbing'] = $data['form_content']['nosger_scores']['score_disturbing'];
        		}
        		
        		
        		// save in forms
        		$entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
        		
        		// save in form answer
        		$form_id = $entity->id;
        		
            	if(!empty($form_id)){
            	    
            	    // 
            	    
            	    $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormId($form_id);
            	    if($answers_array){
            	        
                	    foreach ($answers_array as $answer_tab){
                	        $answer_tab->delete();
                	    }
            	    }
            	    
            		$answers = array();
            		
            		foreach($data['form_content'] as $q_ident=>$option_vals){

            		    if($q_ident != "nosger_scores"){
            		        
                            foreach($option_vals as $opt_ident=>$option_value){
                                
                                 $answers[]=array(
                                     'ipid'=>$ipid,
                                     'form_type'=>$from_data['form_type'],
                                     'form_id'=>$form_id,
                                     'question_id'=>$q_ident,
                                     'question_option'=>$opt_ident,
                                     'option_checked'=> $option_value != 0 ? 'yes' :'no',
                                     'option_value'=> $option_value,
                                 );
                            }
            		    }
            		}
            		$obj = new Doctrine_Collection('PatientRubinFormsAnswers');
            		$obj->fromArray($answers);
            		$obj->save();
        		}
        		

        		return $entity;
        		
		        break;

		    case 'dsv':
        		$from_data['form_id'] = $data['form_id'];
        		$from_data['form_type'] = $data['form_ident'];
        		$from_data['form_total'] = $data['form_content']['form_total'];
        		$from_data['form_date'] = !empty($data[$data['form_ident']]['form_date']) ? date("Y-m-d H:i:s",strtotime($data[$data['form_ident']]['form_date'])) : date("Y-m-d H:i:s");
        		$from_data['form_content'] = $data[$data['form_ident']];
        		// save in forms
        		$entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
//         		dd($from_data);
        		// save in form answer
        		$form_id = $entity->id;
        		
            	if(!empty($form_id)){
            	    
            	    $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormId($form_id);
            	    if($answers_array){
            	        
                	    foreach ($answers_array as $answer_tab){
                	        $answer_tab->delete();
                	    }
            	    }
            	    
            		$answers = array();
//             		dd($form_id,$from_data['form_content']);
            		foreach($from_data['form_content'] as $q_ident=>$option_vals)
            		{
            		    if($q_ident != "form_date")
            		    {
            		        
                            foreach($option_vals as $opt_ident=>$option_value)
                            {
//                                 $extra_value = "";
//             		            if($q_ident == "q_2" && $opt_ident=="extra_value" &&  $option_vals['opt_1'] == "5"){
//             		                $extra_value= $option_vals;
//             		            }
            		            
//             		            if($q_ident == "q_5" && $opt_ident=="extra_value" &&  $option_vals['opt_4'] == "1"){
//             		                $extra_value= $option_vals;
//             		            }
                                
                                 $answers[]=array(
                                     'ipid'=>$ipid,
                                     'form_type'=>$from_data['form_type'],
                                     'form_id'=>$form_id,
                                     'question_id'=>$q_ident,
                                     'question_option'=>$opt_ident,
                                     'option_checked'=> $option_value != 0 ? 'yes' :'no',
                                     'option_value'=> $option_value,
                                     'extra_value'=> $extra_value,
                                 );
                            }
                        }
            		}
            		$obj = new Doctrine_Collection('PatientRubinFormsAnswers');
            		$obj->fromArray($answers);
            		$obj->save();
        		}
        		

        		return $entity;
        		
		        break;
		        
		    case 'demtect':
        		$from_data['form_id'] = $data['form_id'];
        		$from_data['form_type'] = $data['form_ident'];
        		$from_data['form_content'] = $data['form_content']['demtect'];
        		$from_data['form_total'] = $data['form_content']['form_total'];
        		
//                 dd($data);
        		
        		// save in forms
        		$entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
        		
        		// save in form answer
        		$form_id = $entity->id;
        		
            	if(!empty($form_id)){
            	    
            	    // 
            	    
            	    $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormIdAndFormType($form_id,$data['form_ident']);
            	    if($answers_array){
            	        
                	    foreach ($answers_array as $answer_tab){
                	        $answer_tab->delete();
                	    }
            	    }
            	    
            		$answers = array();

            		foreach($from_data['form_content'] as $q_ident=>$option_vals){
            		    if(strpos($q_ident,'total') === false)
            		    {
            		     foreach($option_vals as $opt_ident=>$option_value){
            		     if($q_ident == 'q_4'&& is_array($option_value)){

            		         foreach($option_value as $s_opt_ident=>$s_option_value){
            		             
            		         $answers[]=array(
            		             'ipid'=>$ipid,
            		             'form_type'=>$from_data['form_type'],
            		             'form_id'=>$form_id,
            		             'question_id'=>$q_ident,
            		             'question_option'=>$s_opt_ident,
            		             'option_checked'=> $s_option_value != 0 ? 'yes' :'no',
            		             'option_value'=> $s_option_value,
            		         );
            		         }
            		         
            		         
            		     } else{
            		         
            		         $answers[]=array(
            		             'ipid'=>$ipid,
            		             'form_type'=>$from_data['form_type'],
            		             'form_id'=>$form_id,
            		             'question_id'=>$q_ident,
            		             'question_option'=>$opt_ident,
            		             'option_checked'=> $option_value != 0 ? 'yes' :'no',
            		             'option_value'=> $option_value,
            		         );
            		     }
            		     }
            		  } else {
            		      
            		      $q_ident_total = str_replace('_total',"", $q_ident);
            		      
            		      $answers[]=array(
            		          'ipid'=>$ipid,
            		          'form_type'=>$from_data['form_type'],
            		          'form_id'=>$form_id,
            		          'question_id'=> $q_ident_total,
            		          'question_option'=>'total',
            		          'option_checked'=> $option_vals != 0 ? 'yes' :'no',
            		          'option_value'=> $option_vals,
            		      );
            		      
            		      
            		  }
            		  
            		}
            		
            		$obj = new Doctrine_Collection('PatientRubinFormsAnswers');
            		$obj->fromArray($answers);
            		$obj->save();
        		}
        		

        		return $entity;
        		
		        break;
		        
		    //ISPC-2492 Lore 02.12.2019
		    case 'carerelated':
		        $from_data['form_id'] = $data['form_id'];
		        $from_data['form_type'] = $data['form_ident'];
		        $from_data['form_content'] = $data['form_content']['carerelated'];
		        $from_data['form_total'] = $data['form_content']['form_total'];
		        
		        // save in forms
		        $entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
		        
		        // save in form answer
		        $form_id = $entity->id;
		        
		        if(!empty($form_id)){
		            		            
		            $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormIdAndFormType($form_id,$data['form_ident']);
		            if($answers_array){
		                
		                foreach ($answers_array as $answer_tab){
		                    $answer_tab->delete();
		                }
		            }
		            
		            $answers = array();
		            
		            foreach($from_data['form_content'] as $q_ident=>$option_vals){
		                if(strpos($q_ident,'total') === false)
		                {
		                    foreach($option_vals as $opt_ident=>$option_value){
		                        if($q_ident == 'q_4'&& is_array($option_value)){
		                            
		                            foreach($option_value as $s_opt_ident=>$s_option_value){
		                                
		                                $answers[]=array(
		                                    'ipid'=>$ipid,
		                                    'form_type'=>$from_data['form_type'],
		                                    'form_id'=>$form_id,
		                                    'question_id'=>$q_ident,
		                                    'question_option'=>$s_opt_ident,
		                                    'option_checked'=> $s_option_value != 0 ? 'yes' :'no',
		                                    'option_value'=> $s_option_value,
		                                );
		                            }
		                            
		                            
		                        } else{
		                            
		                            $answers[]=array(
		                                'ipid'=>$ipid,
		                                'form_type'=>$from_data['form_type'],
		                                'form_id'=>$form_id,
		                                'question_id'=>$q_ident,
		                                'question_option'=>$opt_ident,
		                                'option_checked'=> $option_value != 0 ? 'yes' :'no',
		                                'option_value'=> $option_value,
		                            );
		                        }
		                    }
		                } else {
		                    
		                    $q_ident_total = str_replace('_total',"", $q_ident);
		                    
		                    $answers[]=array(
		                        'ipid'=>$ipid,
		                        'form_type'=>$from_data['form_type'],
		                        'form_id'=>$form_id,
		                        'question_id'=> $q_ident_total,
		                        'question_option'=>'total',
		                        'option_checked'=> $option_vals != 0 ? 'yes' :'no',
		                        'option_value'=> $option_vals,
		                    );
		                    
		                    
		                }
		                
		            }
		            
		            $obj = new Doctrine_Collection('PatientRubinFormsAnswers');
		            $obj->fromArray($answers);
		            $obj->save();
		        }
		        
		        
		        return $entity;
		        
		        break;
		        
		    //ISPC-2493 Lore 03.12.2019
		    case 'carepatient':
		        $from_data['form_id'] = $data['form_id'];
		        $from_data['form_type'] = $data['form_ident'];
		        $from_data['form_content'] = $data['form_content']['carepatient'];
		        $from_data['form_total'] = $data['form_content']['form_total'];
		        
		        
		        // save in forms
		        $entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
		        
		        // save in form answer
		        $form_id = $entity->id;
		        
		        if(!empty($form_id)){
		            
		            $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormIdAndFormType($form_id,$data['form_ident']);
		            if($answers_array){
		                
		                foreach ($answers_array as $answer_tab){
		                    $answer_tab->delete();
		                }
		            }
		            
		            $answers = array();
		            
		            foreach($from_data['form_content'] as $q_ident=>$option_vals){
		                if(strpos($q_ident,'total') === false)
		                {
		                    foreach($option_vals as $opt_ident=>$option_value){
		                        if($q_ident == 'q_4'&& is_array($option_value)){
		                            
		                            foreach($option_value as $s_opt_ident=>$s_option_value){
		                                
		                                $answers[]=array(
		                                    'ipid'=>$ipid,
		                                    'form_type'=>$from_data['form_type'],
		                                    'form_id'=>$form_id,
		                                    'question_id'=>$q_ident,
		                                    'question_option'=>$s_opt_ident,
		                                    'option_checked'=> $s_option_value != 0 ? 'yes' :'no',
		                                    'option_value'=> $s_option_value,
		                                );
		                            }
		                            
		                            
		                        } else{
		                            
		                            $answers[]=array(
		                                'ipid'=>$ipid,
		                                'form_type'=>$from_data['form_type'],
		                                'form_id'=>$form_id,
		                                'question_id'=>$q_ident,
		                                'question_option'=>$opt_ident,
		                                'option_checked'=> $option_value != 0 ? 'yes' :'no',
		                                'option_value'=> $option_value,
		                            );
		                        }
		                    }
		                } else {
		                    
		                    $q_ident_total = str_replace('_total',"", $q_ident);
		                    
		                    $answers[]=array(
		                        'ipid'=>$ipid,
		                        'form_type'=>$from_data['form_type'],
		                        'form_id'=>$form_id,
		                        'question_id'=> $q_ident_total,
		                        'question_option'=>'total',
		                        'option_checked'=> $option_vals != 0 ? 'yes' :'no',
		                        'option_value'=> $option_vals,
		                    );
		                    
		                    
		                }
		                
		            }
		            
		            $obj = new Doctrine_Collection('PatientRubinFormsAnswers');
		            $obj->fromArray($answers);
		            $obj->save();
		        }
		        
		        
		        return $entity;
		        
		        break;

		    case 'dscdsv':
		       
		        $from_data['form_id'] = $data['form_id'];
		        $from_data['form_type'] = $data['form_ident'];
		        $from_data['form_total'] = $data['form_content']['form_total'];
		        $from_data['form_date'] = !empty($data[$data['form_ident']]['form_date']) ? date("Y-m-d H:i:s",strtotime($data[$data['form_ident']]['form_date'])) : date("Y-m-d H:i:s");
		        $from_data['form_content'] = $data[$data['form_ident']];
		        // save in forms
		        $entity = PatientRubinFormsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
		        //         		dd($from_data);
		        // save in form answer
		        $form_id = $entity->id;
		        
		        if(!empty($form_id)){
		            
		            $answers_array = Doctrine::getTable('PatientRubinFormsAnswers')->findByFormId($form_id);
		            if($answers_array){
		                
		                foreach ($answers_array as $answer_tab){
		                    $answer_tab->delete();
		                }
		            }
		            
		            $answers = array();
		            //             		dd($form_id,$from_data['form_content']);
		            foreach($from_data['form_content'] as $q_ident=>$option_vals)
		            {
		                if($q_ident != "form_date")
		                {
		                    
		                    foreach($option_vals as $opt_ident=>$option_value)
		                    {
		                        //                                 $extra_value = "";
		                        //             		            if($q_ident == "q_2" && $opt_ident=="extra_value" &&  $option_vals['opt_1'] == "5"){
		                        //             		                $extra_value= $option_vals;
		                        //             		            }
		                            
		                        //             		            if($q_ident == "q_5" && $opt_ident=="extra_value" &&  $option_vals['opt_4'] == "1"){
		                        //             		                $extra_value= $option_vals;
		                        //             		            }
		                        
		                        $answers[]=array(
		                            'ipid'=>$ipid,
		                            'form_type'=>$from_data['form_type'],
		                            'form_id'=>$form_id,
		                            'question_id'=>$q_ident,
		                            'question_option'=>$opt_ident,
		                            'option_checked'=> $option_value != 0 ? 'yes' :'no',
		                            'option_value'=> $option_value,
		                            'extra_value'=> $extra_value,
		                        );
		                    }
		                }
		            }
		            $obj = new Doctrine_Collection('PatientRubinFormsAnswers');
		            $obj->fromArray($answers);
		            $obj->save();
		        }
		        
		        
		        return $entity;
		        
		        break;
		        
		    case 'demstepcare':
// 		        dd($data);
        		$from_data = $data['demstepcare'];
        		$from_data['form_id'] = $data['form_id'];
        		
        		$from_data['form_date'] = !empty($data['demstepcare']['form_date']) ? date("Y-m-d",strtotime($data['demstepcare']['form_date'])) :"";
 
        		// save in forms
        		$entity = PatientDemstepcareTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$from_data['form_id'], $ipid], $from_data);
        		$form_id = $entity->id;
        		
        		
        		return $entity;
        		
		        break;
		        
		        
		    default:
		        break;
		}

	}
	
	
	
	
	private function _create_question($options = array(),$question_info = array(), $elementsBelongTo = null)
	{
	    
	    if(!empty($question_info['PatientRubinQuestionsOptions'])){
	        foreach($question_info['PatientRubinQuestionsOptions'] as $optk => $opt){
	            
    	        //$questions_options[$opt['question_id']][$opt['id']] = $opt['option_label'];
//     	        $questions_options[$opt['question_id']][$opt['option_value']] = $opt['option_label'].'('. $opt['option_score_value'].')';
    	        $questions_options[$opt['question_id']][$opt['option_value']] = $opt['option_label'];
    	        
    	        $question_options[$opt['question_id']][$opt['id']]['label'] = $opt['option_label'];
    	        $question_options[$opt['question_id']][$opt['id']]['value'] = $opt['option_value'];
	        }
	    } else {
	        
	       // SPECIAL FOR CMAID 
	       if($question_info['question_type'] == 'textarea'){
    	        $question_options[$question_info['question_id']]['value'] = $options[$question_info['question_id']][$question_info['question_id']]['value'];
	           
	       }
	        
	    }
	    
// 	    dd($question_options);
	    
	    $subform = new Zend_Form_SubForm();
	    $subform->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
	    ));
	    	
	    
	    if ( ! is_null($elementsBelongTo)) {
	        $subform->setOptions(array(
	            'elementsBelongTo' => $elementsBelongTo
	        ));
	    }
	    
     
         switch ($question_info['question_type']){
    
            case 'radio':     
        	    // LABEL
                $subform->addElement('note', 'ceva'.$question_info['id'], array(
        	        'value' => $question_info['question_text'],
        	        'decorators' => array(
        	            'ViewHelper',
        	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
        	        ),
        	    ));
        	    // RADIO
        	    $subform->addElement('radio', 'opt_1', array(
        	        'label'      => null,//$this->translate('enable/disable module'),
        	        'required'   => false,
        	        'multiOptions' =>  $questions_options[$question_info['question_id']],
        	        'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
                    'decorators'   => array(
                             'ViewHelper',
                             array('Label', array('placement'=> 'IMPLICIT_APPEND' )),
                             array('Errors'),
                             array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
                         ),
                    'class' =>'calculate_score',
                    'data-score' => $q_opt['value'],
                    'onChange' => "calcscore(\$(this),'radio')"
        	    ));
    	       break;
    	       
            case 'checkbox':
                 // LABEL
                 $subform->addElement('note', 'ceva'.$question_info['id'], array(
                     'value' => $question_info['question_text'],
                     'decorators' => array(
                         'ViewHelper',
                         array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
                     ),
                 ));
                 // CHECKBOXES

                 
                 $q_ident = 1 ;
                 foreach ($question_options[$question_info['question_id']] as $q_opt_k => $q_opt) {
                     $subform->addElement('checkbox', 'opt_'.$q_ident, array(
//                          'checkedValue'    => $q_opt['value'],
//                          'uncheckedValue'  => '0',
                         'label'      => $q_opt['label'],
                         'required'   => false,
                         'value' =>  $options[$question_info['question_id']]['opt_'.$q_ident]['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_'.$q_ident]['value'] : 0,
                         'decorators'   => array(
                             'ViewHelper',
                             array('Label', array('placement'=> 'IMPLICIT_APPEND')),
                             array('Errors'),
                             array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
                         ),
                         'class' =>'calculate_score '.$question_info['question_id'],
                         'data-score' => $q_opt['value'],
                         'data-question_id' => $question_info['question_id'],
                         'onChange' => "calculate_score(\$(this),'checkbox')"
                     ));
                     $q_ident++;
                 }
    	       break;
    	    
             case 'html':
                 $subform->addElement('note', $question_info['question_id'], array(
                     'value' => $question_info['question_text'],
                     'decorators' => array(
                         'ViewHelper',
                         array(array('row' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text-html'))
                     ),
                 ));
                 break;
                 
                 
             case 'text':
                 $subform->addElement('note', $question_info['question_id'], array(
                     'value' => $question_info['question_text'],
                     'decorators' => array(
                         'ViewHelper',
                         array(array('row' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
                     ),
                 ));
    	    break;
    	    
    	    
             case 'text-input':
                 $subform->addElement('text', $question_info['question_id'], array(
                     'label' => $question_info['question_text'],
                     'value' => '',
                     'required' => false,
                     'readOnly' => false,
                     'filters'      => array('StringTrim'),
                     'description'=>'Minuten',
    					'decorators' => array(
		    								'ViewHelper',
		    								array('Label', array('tag' => 'div' , 'placement' => 'PREPEND', 'style' => 'width: 100%;')),
		    								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => $options['formular_type'] == 'pdf' ? 'width: 35%;  display: inline-block; border-left: 1px solid green;' : 'width: 35%;  display: inline-block; backbround: green')),
 
		    						),
                
                 ));
    	    break;
    	    
    	    
             case 'textarea':
                 $subform->addElement('textarea', $question_info['question_id'], array(
                     'label' => $question_info['question_text'],
                     'value' => !empty($question_options[$question_info['question_id']]['value']) ? $question_options[$question_info['question_id']]['value'] : "",
                     'filters'      => array('StringTrim'),
                     'description'=>'Minuten',
                     'decorators' => array(
                         'ViewHelper',
                         array('Label', array('tag' => 'div' , 'placement' => 'PREPEND',   'class'=>"textarea_label")),
                         array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_textarea_block')),
                 
                     ),
                 
                 ));
    	     break;
            }
    	    	
    	    return $subform;
    	}
    	 
    	
    	private function _create_patient_details($options = array(),$elementsBelongTo ){
    	    
    	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn


    	    $patient_master = $this->_patientMasterData;
    	    $clientid = $this->logininfo->clientid;
    	    $client_data = Pms_CommonData::getClientData($clientid);
    	    $clientinfo = $client_data[0];
    	    $date = $options['create_date']['value'] ? date("d.m.Y",strtotime($options['create_date']['value'])) : date("d.m.Y");
    	    

    	    $subform = new Zend_Form_SubForm();
    	    $subform->removeDecorator('Fieldset');
    	    $subform->addDecorator('HtmlTag', array('tag' => 'table', 'class'=>'patient_info_header','cellspacing'=>'0','cellpadding'=>'0' ));
    	    $subform->setAttrib("class", "label_same_size {$__fnName}");
    	     
    	     
    	    $this->__setElementsBelongTo($subform, $elementsBelongTo);

    	    if($elementsBelongTo!="demstepcare"){
	        $subform->addElement('note', 'nice_name_epid', array(
	            'value'        => $patient_master['nice_name_epid'] ,
	            'label'        => $this->translate('rubin_patient_name'),
    	        'required'     => true,
    	        'filters'      => array('StringTrim'),
    	        'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'phd_value_column' )),
	                array('Label', array('tag' => 'td', 'tagClass'=>'phd_label_column'  )),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	    
	            ),
	        ));
    	    }
	    
	        $subform->addElement('text', 'form_date', array(
	            'value'        => (!empty($options['formular']['form_date'])) ? $options['formular']['form_date'] : date("d.m.Y"),
	            'label'        => $this->translate('rubin_form_date'),
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'phd_value_column' )),
	                array('Label', array('tag' => 'td', 'tagClass'=>'phd_label_column'  )),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	    
	            ),
	            'class'=>'form_date',
	            'readonly'=> true
	        ));
	        

	        if($elementsBelongTo!="demstepcare"){
	            

	        $subform->addElement('note', 'form_client', array(
	            'value'        => $clientinfo['team_name'] ,
	            'label'        => $this->translate('rubin_form_client'),
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'phd_value_column' )),
	                array('Label', array('tag' => 'td', 'tagClass'=>'phd_label_column'  )),
	                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
	                	
	            ),
	        ));
	    
	        }
	        return $subform;
// 	        return $this->filter_by_block_name($subform, $__fnName);
    	    
    	}
    	
    	
    	

    	/**
    	 * @author Ancuta
    	 *  
    	 *   
    	 * @param unknown $options
    	 * @param string $elementsBelongTo
    	 * @return Zend_Form_SubForm
    	 */
    	
    	private function _create_formular_content_gds($options = array(), $elementsBelongTo = null){

    	    $subform = new Zend_Form_SubForm();
    	    $subform->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	    ));
    	     
    	
    	     
    	    $form_question[$elementsBelongTo] = array();
    	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
    	
    	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
    	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    	    }
    	     
 
    	     
    	     
    	         $subform->addElement('text', 'form_total', array(
	        'label' => 'Gesamtpunktzahl:',
    		            'value' => $options['form_total'],
    		            'readonly'=> true,
    		            'class'=> 'form_total',
	        'decorators' => array(
    		            'ViewHelper',
    		            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
    	    ),
    	    ));
    	     
    	     
	  
	  
    	    $score_description = '<h5>Interpretation des Testergebnisses</h5>';
    	    $score_description .= '<table class="score_desc" cellpadding="0" cellspacing="0">';
            $score_description .= '<tr>';
            $score_description .= '<th>Punkte</th>';
            $score_description .= '<th>Beurteilung</th>';
            $score_description .= '</tr>';
            
            $score_description .= '<tr>';
            $score_description .= '<td>0-5</td>';
            $score_description .= '<td>unauffällig</td>';
            $score_description .= '</tr>';
            
            $score_description .= '<tr>';
            $score_description .= '<td>6-10</td>';
            $score_description .= '<td>Verdacht auf leicht bis mäßige depressive Symptomatik</td>';
            $score_description .= '</tr>';
            
            $score_description .= '<tr>';
            $score_description .= '<td>11-15</td>';
            $score_description .= '<td>Verdacht auf schwere depressive Symptomatik</td>';
            $score_description .= '</tr>';
		    $score_description .= '</table>';
    		         
	    $subform->addElement('note', 'score_description', array(
    		        'value' => $score_description,
    		        'decorators' => array(
    		            'ViewHelper',
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'score_description_block'))
    		    ),
    		    ));
    		     
    		    return $subform;
    		  
    		}
    	   
    	

    	/**
    	 * @author Ancuta
    	 *  
    	 *   
    	 * @param unknown $options
    	 * @param string $elementsBelongTo
    	 * @return Zend_Form_SubForm
    	 */
    	
    	private function _create_formular_content_npi($options = array(), $elementsBelongTo = null){

    	    $subform = new Zend_Form_SubForm();
    	    $subform->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content '.$elementsBelongTo)),
    	    ));
    	     
    	
    	     
    	    $form_question[$elementsBelongTo] = array();
    	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
    	
    	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
    	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    	    }
    	     
 
    	     
    	     
    	         $subform->addElement('text', 'form_total', array(
	        'label' => 'Gesamtpunktzahl',
    		            'value' => $options['form_total'],
    		            'readonly'=> true,
    		            'class'=> 'form_total',
	        'decorators' => array(
    		            'ViewHelper',
    		            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
    	    ),
    	    ));
    	     
    	     
	   
    		     
    		    return $subform;
    		  
    		}
    	   
    		
    		
    	/**
    	 * @author Ancuta
    	 *  
    	 *   
    	 * @param unknown $options
    	 * @param string $elementsBelongTo
    	 * @return Zend_Form_SubForm
    	 */
    	
    	private function _create_formular_content_bdi($options = array(), $elementsBelongTo = null){

    	    $subform = new Zend_Form_SubForm();
    	    $subform->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	    ));
    	     
    	
    	     
    	    $form_question[$elementsBelongTo] = array();
    	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
    	
    	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
    	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    	    }
    	     
 
    	     
    	     
    	         $subform->addElement('text', 'form_total', array(
	        'label' => 'Gesamtpunktzahl',
    		            'value' => $options['form_total'],
    		            'readonly'=> true,
    		            'class'=> 'form_total',
	        'decorators' => array(
    		            'ViewHelper',
    		            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
    	    ),
    	    ));
    	     
    	     
	  
	  
	    $score_description = '<h5>Interpretation des Testergebnisses</h5>';
	    $score_description .= '<table class="score_desc" cellpadding="0" cellspacing="0">';
        $score_description .= '<tr>';
        $score_description .= '<th>Punkte</th>';
        $score_description .= '<th>Beurteilung</th>';
        $score_description .= '</tr>';
            		     
        $score_description .= '<tr>';
        $score_description .= '<td>0-8</td>';
        $score_description .= '<td>Keine Depression</td>';
        $score_description .= '</tr>';
            		     
        $score_description .= '<tr>';
        $score_description .= '<td>9-13</td>';
        $score_description .= '<td>Minimale depressive Symptomatik</td>';
        $score_description .= '</tr>';
            		     
        $score_description .= '<tr>';
        $score_description .= '<td>14-19</td>';
        $score_description .= '<td>Leichte depressive Symptomatik</td>';
        $score_description .= '</tr>';
            		     
        $score_description .= '<tr>';
        $score_description .= '<td>20-28</td>';
        $score_description .= '<td>Mittelschwere depressive Symptomatik</td>';
        $score_description .= '</tr>';
        	    
        $score_description .= '<tr>';
        $score_description .= '<td>29-63</td>';
        $score_description .= '<td>Schwere depressive Symptomatik</td>';
        $score_description .= '</tr>';
        $score_description .= '</table>';
    		         
	    $subform->addElement('note', 'score_description', array(
    		        'value' => $score_description,
    		        'decorators' => array(
    		            'ViewHelper',
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'score_description_block'))
    		    ),
    		    ));
    		     
    		    return $subform;
    		  
    		}
    		
    		
    	/**
    	 * Ancuta 
    	 * 28.08.2019
    	 * @param unknown $options
    	 * @param string $elementsBelongTo
    	 * @return Zend_Form_SubForm
    	 */
    	private function _create_formular_content_cmai($options = array(), $elementsBelongTo = null){

    	    $subform = new Zend_Form_SubForm();
    	    $subform->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	    ));
    	     
//     	        dd($options);
    	    $form_question[$elementsBelongTo] = array();
    	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
//     	    dd($form_question);
//     	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
//     	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
//     	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
//     	    }

    	    $opt_labels = array(
    	        "1"=>"nie", 	
    	        "2"=>"unter 1 x pro Woche", 	
    	        "3"=>"1 x od. 2x pro Woche", 	
    	        "4"=>"mehrmals pro Woche",
    	        "5"=>"1x od. 2x pro Tag", 	
    	        "6"=>"mehrmals pro Tag", 	
    	        "7"=>"mehrmals pro Stunde"
    	    );
    	    
    	    
    	    if(empty($options) && !empty($_POST[$elementsBelongTo])){
    	        $options = $_POST[$elementsBelongTo]['form_content'];
    	        $options['form_type'] = $elementsBelongTo;
    	    }
    	    
    	    $html_str = "";
    	    $html_str .= '<table class="cmai SimpleTable" cellpadding="0" cellspacing="0"> ';
    	    $html_str .= '<tr>';
    	    $html_str .= '<th alight="left" class="qtext_th"></th>';
    	    foreach($opt_labels as $ov => $label){
        	    $html_str .= '<th class="q_opt_text_th">'.$label.'</th>';
    	    }
    	    $html_str .= '</tr>';
    	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    	        if($qinfo['question_type'] == 'radio'){
            	    $html_str .= '<tr>';
            	    $html_str .= '<th class="qtext_td">'.$qinfo['question_text'].'</th>';
            	    $opt_ident = 1;
            	    foreach($qinfo['PatientRubinQuestionsOptions'] as $q_op=>$q_op_val){
            	        $checked ="";
            	        if($options[$qinfo['question_id']]['opt_1']['value'] ==  $q_op_val['option_value']
                            || $options[$qinfo['question_id']]['opt_1'] ==  $q_op_val['option_value'] 
            	            ){
            	            
            	           $checked ='checked="checked"';
            	        }
            	        
            	        //'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
            	        $html_str .= '<td align="center" class="q_opt_text_td">';
            	        $html_str .= '<input type="radio" class="calculate_score" onchange="calcscore($(this),\'radio\')" id="cmai-form_content-'.$qinfo['question_id'].'-opt_1-'.$q_op_val['option_value'].'" name="cmai[form_content]['.$qinfo['question_id'].'][opt_1]" value="'.$q_op_val['option_value'].'" '.$checked.'  />';
            	        $html_str .= '</td>';
            	    }
        	       $html_str .= '</tr>';
    	       }
    	    }
    	    $html_str .= '</table>';

    	    $subform->addElement('note', 'sblock', array(
    	        'value' => $html_str,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'cmai_form_block'))
    	        ),
    	    ));

            foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
                if($qinfo['question_type'] == 'textarea'){
                    
        	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
        	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
                }
    	    }
    	    
    	    
    	    
    	         $subform->addElement('text', 'form_total', array(
	        'label' => 'Gesamtpunktzahl:',
    		            'value' => $options['form_total'],
    		            'readonly'=> true,
    		            'class'=> 'form_total',
	        'decorators' => array(
    		            'ViewHelper',
    		            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
    	    ),
    	    ));
    	     
    	     
	  
	  
/* 	    $score_description = '<h5>Interpretation des Testergebnisses</h5>';
	    $score_description .= '<table class="score_desc" cellpadding="0" cellspacing="0">';
        $score_description .= '<tr>';
        $score_description .= '<th>Punkte</th>';
        $score_description .= '<th>Beurteilung</th>';
        $score_description .= '</tr>';
            		     
        $score_description .= '<tr>';
        $score_description .= '<td>0-8</td>';
        $score_description .= '<td>Keine Depression</td>';
        $score_description .= '</tr>';
            		     
        $score_description .= '<tr>';
        $score_description .= '<td>9-13</td>';
        $score_description .= '<td>Minimale depressive Symptomatik</td>';
        $score_description .= '</tr>';
            		     
        $score_description .= '<tr>';
        $score_description .= '<td>14-19</td>';
        $score_description .= '<td>Leichte depressive Symptomatik</td>';
        $score_description .= '</tr>';
            		     
        $score_description .= '<tr>';
        $score_description .= '<td>20-28</td>';
        $score_description .= '<td>Mittelschwere depressive Symptomatik</td>';
        $score_description .= '</tr>';
        	    
        $score_description .= '<tr>';
        $score_description .= '<td>29-63</td>';
        $score_description .= '<td>Schwere depressive Symptomatik</td>';
        $score_description .= '</tr>';
        $score_description .= '</table>';
    		         
	    $subform->addElement('note', 'score_description', array(
    		        'value' => $score_description,
    		        'decorators' => array(
    		            'ViewHelper',
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'score_description_block'))
    		    ),
    		    )); */
    		     
    		    return $subform;
    		  
    		}

    		
    		
    	/**
    	 * Ancuta 
    	 * 28.08.2019
    	 * @param unknown $options
    	 * @param string $elementsBelongTo
    	 * @return Zend_Form_SubForm
    	 */
    	private function _create_formular_content_nosger($options = array(), $elementsBelongTo = null){

    	    $subform = new Zend_Form_SubForm();
    	    $subform->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	    ));
    	     
    	        
    	    $form_question[$elementsBelongTo] = array();
    	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
//     	    dd($form_question);
    	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    	        if($qinfo['question_type'] == "html"){
        	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
        	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    	        } else {
//         	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
//         	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    	        }
    	    }

    	    
    	    
    	    $opt_labels = array(
    	        "1"=>"immer",
    	        "2"=>"meistens",
    	        "3"=>"oft",
    	        "4"=>"hie und da",
    	        "5"=>"nie"
    	    );
    	    	
    	    
    	    // nosger special calculation
    	    $score_calculation  = $this->nosger_scores(false,true);
    	     
//     	    $score_calculation =array(
//     	        'score_memory'=> array(8,12,16,22,27),
//     	        'score_iadl'=> array(2,6,9,11,19),
//     	        'score_adl'=>array(1,7,14,18,24),
//     	        'score_mood'=>array(3,10,13,25,28),
//     	        'score_social'=>array(5,17,21,26,29),
//     	        'score_disturbing'=>array(4,15,20,23,30),
//     	    );
    	    foreach($score_calculation as $score_ident=> $q_array){
    	        foreach($q_array as $q_ident){
    	            $score_mapping['q_'.$q_ident] = $score_ident;
    	        }
    	    }
    	    
    	    	
    	    if(empty($options) && !empty($_POST[$elementsBelongTo])){
    	        $options = $_POST[$elementsBelongTo]['form_content'];
    	        $options['form_type'] = $elementsBelongTo;
    	    }
    	    	
    	    $html_str = "";
    	    $html_str .= '<table class="nosger SimpleTable" cellpadding="0" cellspacing="0" style="width: 100%;"> ';
    	    $html_str .= '<thead>';
    	    $html_str .= '<tr>';
    	    $html_str .= '<th alight="left" class="nqtext_th"  ></th>';
    	    foreach($opt_labels as $ov => $label){
    	        $html_str .= '<th class="nq_opt_text_th"  >'.$label.'</th>';
    	    }
    	    $html_str .= '</tr>';
    	    $html_str .= '</thead>';
    	    $html_str .= '</tbody>';
    	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    	        if($qinfo['question_type'] == "radio"){
    	        $html_str .= '<tr>';
    	        $html_str .= '<th class="nqtext_td">'.$qinfo['question_text'].'</th>';
    	        $opt_ident = 1;
    	        foreach($qinfo['PatientRubinQuestionsOptions'] as $q_op=>$q_op_val){
    	            $checked ="";
    	            if($options[$qinfo['question_id']]['opt_1']['value'] ==  $q_op_val['option_value']
    	                || $options[$qinfo['question_id']]['opt_1'] ==  $q_op_val['option_value']
    	            ){
    	                 
    	                $checked ='checked="checked"';
    	            }
    	            	
    	            //'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
    	            $html_str .= '<td align="center"   class="nq_opt_text_td">';
    	            $html_str .= '<input type="radio" class="calculate_score '.$score_mapping[$qinfo['question_id']].'  '.$qinfo['question_id'].' " onchange="calcscore($(this),\'radio\'), special_calcscore($(this),\'radio\', \''.$score_mapping[$qinfo['question_id']].'\' )  "   id="nosger-form_content-'.$qinfo['question_id'].'-opt_1-'.$q_op_val['option_value'].'" name="nosger[form_content]['.$qinfo['question_id'].'][opt_1]" value="'.$q_op_val['option_value'].'" '.$checked.'  />';
    	            $html_str .= '</td>';
    	        }
    	        $html_str .= '</tr>';
    	        }
    	    }
    	    $html_str .= '</tbody>';
    	    $html_str .= '</table>';
    	    
    	    $subform->addElement('note', 'sblock', array(
    	        'value' => $html_str,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'nosger_form_block'))
    	        ),
    	    ));
    	    	
    	    
    	    
    	    

    	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    	        if($qinfo['question_type'] == 'textarea'){
    	    
    	            $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
    	            $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    	        }
    	    }
    	    	
    	    	
    	    
//     	         $subform->addElement('text', 'form_total', array(
// 	        'label' => 'Gesamtpunktzahl:',
//     		            'value' => $options['form_total'],
//     		            'readonly'=> true,
//     		            'class'=> 'form_total',
// 	        'decorators' => array(
//     		            'ViewHelper',
//     		            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
//     		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
//     	    ),
//     	    ));
    	     
	    
    	    $empty_form = $this->_nosger_additional_scores($options,$elementsBelongTo,'nosger_scores');
    	    $subform->addSubform($empty_form, 'nosger_scores');
	    
    		    return $subform;
    		  
    		}

    	private function _nosger_additional_scores($options = array(),$elementsBelongToMaster,$elementsBelongTo ){
    	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
    	
    	    $subform = new Zend_Form_SubForm();
    	    $subform->removeDecorator('Fieldset');
    	    $subform->addDecorator('HtmlTag', array('tag' => 'div', 'class'=>'additional_scores_form' ));
    	    $subform->setAttrib("class", "label_same_size {$__fnName}");
    	
    	    
    	    // nosger special scores
    	    $scores = $this->nosger_scores(true,false);
    	    
    	    
//     	    $scores= array(
//     	        'score_memory'=>"Gedächtnis",
//     	        'score_iadl'=>"Instrumental Activities of Daily Life (IADL)",
//     	        'score_adl'=>"Körperpflege (Activities of Daily Life, ADL)",
//     	        'score_mood'=>"Stimmung",
//     	        'score_social'=>"Soziales Verhalten",
//     	        'score_disturbing'=>"Störendes Verhalten"
//     	    );
    	    
    	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
 
    	    foreach($scores as $score_ident => $score_label){
    	        
    	        $subform->addElement('text', $score_ident, array(
    	            'label' => '<span>'.$score_label.'</span>',
    	            'value' => $options[$score_ident],
//     	            'readonly'=> true,
    	            'class'=> 'form_total_'.$score_ident,
    	            'decorators' => array(
    	                'ViewHelper',
    	                array('Label', array('placement'=> 'IMPLICIT_APPEND')),
    	                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'add_score_line'))
    	            ),
    	        ));
    	        
    	    }
    	    	
    	    return $subform;
    	    		
    	}    		
    		
    		
    		
    		
    		

       private function _create_empty_form($options = array(),$elementsBelongToMaster,$elementsBelongTo ){
    		    	
    		    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
    		
    		    $patient_master = $this->_patientMasterData;
    		    $clientid = $this->logininfo->clientid;
    		    $client_data = Pms_CommonData::getClientData($clientid);
    		    $clientinfo = $client_data[0];
    		    $date = $options['create_date']['value'] ? date("d.m.Y",strtotime($options['create_date']['value'])) : date("d.m.Y");
    		    	
    		
    		    $subform = new Zend_Form_SubForm();
    		    $subform->removeDecorator('Fieldset');
    		    $subform->addDecorator('HtmlTag', array('tag' => 'table', 'class'=>'custom_form','cellspacing'=>'0','cellpadding'=>'0' ));
    		    $subform->setAttrib("class", "label_same_size {$__fnName}");
    		
    		
    		    $this->__setElementsBelongTo($subform, $elementsBelongTo);
    		     
    		    $subform->addElement('note', 'custom_form_title', array(
    		        'value' => $this->translate('Add Score without filling form(transfer from paper)'),
    		        'decorators' => array(
    		            'ViewHelper',
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'custom_form_title'))
    		        ),
    		    ));
    		    
    		    $subform->addElement('note', 'custom_form_status', array(
    		        'value' => '',
    		        'decorators' => array(
    		            'ViewHelper',
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'custom_form_status'))
    		        ),
    		    ));
    		    
    		    $subform->addElement('text', 'form_date', array(
    		        'value'        => '',
    		        'label'        => $this->translate('rubin_custom_from_date'),
    		        'filters'      => array('StringTrim'),
    		        'validators'   => array('NotEmpty'),
    		        'decorators'   => array(
    		            'ViewHelper',
    		            array('Errors'),
    		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'custom_value_column' )),
    		            array('Label', array('tag' => 'td', 'tagClass'=>'custom_label_column'  )),
    		            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    		            
    		        ),
    		        'class'=>'form_date',
    		        'readonly'=> true
    		        
              ));
    		    
    		    $subform->addElement('text', 'form_total', array(
    		        'value'        => '',
    		        'label'        => $this->translate('rubin_custom_score'),
    		        'filters'      => array('StringTrim'),
    		        'validators'   => array('NotEmpty'),
    		        'decorators'   => array(
    		            'ViewHelper',
    		            array('Errors'),
    		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'custom_value_column' )),
    		            array('Label', array('tag' => 'td', 'tagClass'=>'custom_label_column'  )),
    		             
    		        ),
    		        'pattern'          => "^[0-9]*$",
    		    ));
 

    		    $subform->addElement('note', 'empty-slot', array(
    		        'value'        => '',
    		        'label'        => '',
    		        'filters'      => array('StringTrim'),
    		        'decorators'   => array(
    		            'ViewHelper',
    		            array('Errors'),
    		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'custom_value_column' )),
    		            array('Label', array('tag' => 'td', 'tagClass'=>'custom_label_column'  )),
    		             
    		        ),
    		    ));
 
    		     
 
    		    $el = $this->createElement('button', 'button_action', array(
    		        'type'         => 'button',
    		        'value'        => 'save',
    		        'label'        => $this->translator->translate('submit'),
    		        'onclick'      => 'save_custom_form("'.$elementsBelongToMaster.'");',
    		        'decorators'   => array(
    		            'ViewHelper',
    		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'custom_value_column' )),
    		            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    		        ),
    		    
    		    ));
    		    $subform->addElement($el, 'save');
    		    
    		    
    		    
    		    
    		    
    		     
    		    return $subform;
    		    // 	        return $this->filter_by_block_name($subform, $__fnName);
    		    	
    		}
    		     		
    	/**
    	 * @author Ancuta
    	 * 03.09.2019
    	 * @param unknown $options
    	 * @param string $elementsBelongTo
    	 * @return Zend_Form_SubForm
    	 */
    	private function _create_formular_content_demstepcare($options = array(), $elementsBelongTo = null, $extra_forms_values = array()){

     
    	    $subform = $this->subFormTable(array(
    	        'columns' => null,
    	        'class' => 'PatientDemstepcareTable',
    	    ));
    	    $subform->removeDecorator('Fieldset');
    	    $subform->setAttrib("class", "label_same_size_auto");
    	    
    	    if ( ! is_null($elementsBelongTo)) {
    	        $subform->setOptions(array(
    	            'elementsBelongTo' => $elementsBelongTo
    	        ));
    	    }
 
    	    $subform->addElement('note', 'label_dementia_diagnosis', array(
    	        'value' => $this->translate('dementia_diagnosis'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	        ),
    	    ));
    	    $subform->addElement('select', 'dementia_diagnosis', array(
    	        'multiOptions' => PatientDemstepcare::getDefaults('dementia_diagnosis'),
    	        'value'        => $options['dementia_diagnosis'],
    	        'required'     => false,
    	        'filters'      => array('StringTrim'),
    	        // 		    'validators'   => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    	        ),
    	        'separator' => PHP_EOL,
    	    ));
    	    
    	    
    	    
    	    
    	    $subform->addElement('note', 'label_cerebral_imaging', array(
    	        'value' => $this->translate('cerebral_imaging'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	        ),
    	    ));
    	    $subform->addElement('select', 'cerebral_imaging', array(
    	        'multiOptions' => PatientDemstepcare::getDefaults('cerebral_imaging'),
    	        'value'        => $options['cerebral_imaging'],
    	        'required'     => false,
    	        'filters'      => array('StringTrim'),
    	        // 		    'validators'   => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    	        ),
    	        'separator' => PHP_EOL,
    	    ));
    	    
    	    $subform->addElement('note', 'label_laboratory', array(
    	        'value' => $this->translate('laboratory'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	        ),
    	    ));
    	    $subform->addElement('select', 'laboratory', array(
    	        'multiOptions' => PatientDemstepcare::getDefaults('laboratory'),
    	        'value'        => $options['laboratory'],
    	        'required'     => false,
    	        'filters'      => array('StringTrim'),
    	        // 		    'validators'   => array('NotEmpty'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    	        ),
    	        'separator' => PHP_EOL,
    	    ));
    	    
    	    $display = 'yes';
    	    $subform->addElement('note',  'qq_file_uploader_label', array(
    	        'label'        => null,
    	        'required'     => false,
    	        'value'        => $this->translate('Datei des Labor Befunds hochladen'),
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2', 'class' => 'qq_file_uploader_label')),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide', 'style' => $display)),
    	        ),
    	    ));
 
    	    
    	    $subform->addElement('note', 'qq_file_uploader', array(
    	        'label'	=> $this->translate('Upload new file'),
    	        'value'	=> '<div id="demstepcare" class="qq_file_uploader"><noscript>' . $this->translate('Please enable JavaScript to use file uploader.') . '</noscript></div>',
    	        'decorators' => array(
    	            'ViewHelper',
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>'2',
    	                'class' => 'qq_file_uploader_placeholder',
    	                'id' => 'qq_file_uploader_placeholder',
    	                'data-parent' => 'table',
    	            )),
    	            array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide dontPrint', 'style' => $display)),
    	        ),
    	    ));
    	    
    	    
    	    
    	    
   	        $pat_enc_id =  ! empty($this->_patientMasterData['id_encrypted']) ? $this->_patientMasterData['id_encrypted'] : 0;
   	        
    	    if ( ! empty($options['files'])) {
    	        /*list all files*/
    	        foreach ($options['files'] as $file) {
    	    
    	            $filename =  $file['title'];
    	            $filename = '<a href="stats/patientfileupload?doc_id='. $file['id'] . '&id=' . $pat_enc_id . '">' . $filename . "</a>";
    	            $filedate = !empty($file['file_date']) ? '<div class="dem_data">' .date("d.m.Y", strtotime($file['file_date'])) . '</div>' : '<div class="dem_data">' . date("d.m.Y", strtotime($file['create_date'])) . '</div>';   	            
    	            $filedate .= '<div class="dem_del"><a href="javascript:void(0);"  class="delete" data-doc="'.$file['id'] .'" data-pid="' .$pat_enc_id. '"><img border="0" src="'.RES_FILE_PATH.'/images/action_delete.png"></a></div>';
    	            
//     	            $text .= <<<EOT
//     	               <tr>
// 	                       <td> {$filename} </td>
// 	                       <td>{$filedate}</td>
// 	                   </tr>
// EOT;
    	    
    	            $text .= <<<EOT
	                    <div class="fileitem">
	                    <div class="input">
	                    <span class="dem_ipad">{$filename}</span>
	                    <span class="filesize">{$filedate}</span>
	           </div>
	        </div>
EOT;
    	    
    	        }
    	        $text = '<div class="fileupload">' . $text . '</div>';
//     	        $text = '<table class="datatable"> ' . $text . '</table>';
    	         
    	    
    	        $subform->addElement('note',  'files_label', array(
    	            'label'        => null,
    	            'required'     => false,
    	            'value'        => $this->translate('demstepcare dfiles'),
    	            'decorators' => array(
    	                'ViewHelper',
    	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>2 , 'class' => 'files_label')),
    	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class' => 'show_hide dontPrint', 'style' => $display)),
    	            ),
    	        ));
    	         
    	        $subform->addElement('note',  'files', array(
    	            'label'        => null,
    	            'required'     => false,
    	            'value'        => $text,
    	            'escape'       => false,
    	            'decorators' => array(
    	                'ViewHelper',
    	                array('Errors'),
    	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan' => 2)),
    	                array(array('row' => 'HtmlTag'), array('tag' => 'tr' , 'class' => 'show_hide dontPrint', 'style' => $display)),
    	            ),
    	        ));
    	    
    	    }
    	    
 
    	    $extra_forms_idents = array('mmst','gds' );
    	 
    	    foreach($extra_forms_idents  as $ef_ident){
    	        
    	        $subform->addElement('note', 'label_'.$ef_ident, array(
    	            'value' => $this->translate($ef_ident.' title'),
    	            'decorators' => array(
    	                'ViewHelper',
    	                array(array('data' => 'HtmlTag'), array('tag' => 'td', 'class'=>'print_column_first')),
    	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    	            ),
    	        ));
    	        
    	      
    	        if( empty($extra_forms_values[$ef_ident]) || $extra_forms_values[$ef_ident] == false ){
    	            $filename =  $this->translate("Go to ".$ef_ident." link");
    	            $filename = '<a href="rubin/'.$ef_ident.'?id=' . $pat_enc_id . '">' . $filename . "</a>";
    	            
    	        } else{
    	            
    	            $form_total = 0;
    	            $form_total = $extra_forms_values[$ef_ident]['form_total'];
    	            $score_text[$ef_ident] = '';
    	            
    	            if($ef_ident == 'gds'){
    	            
    	                if ($form_total <= 5 )
    	                {
    	                    $score_text[$ef_ident] = "unauffällig";
    	                }
    	                elseif ($form_total >=6 && $form_total <= 10)
    	                {
    	                    $score_text[$ef_ident] = "Verdacht auf leicht bis mäßige depressive Symptomatik";
    	                }
    	                elseif ($form_total >=11 && $form_total <= 15)
    	                {
    	                    $score_text[$ef_ident] = "Verdacht auf schwere depressive Symptomatik";
    	                }
    	            } 
    	            else if($ef_ident == 'mmst'){
    	                if ($form_total <= 9 )
    	                {
    	                    $score_text[$ef_ident] = "Schwere Demenz";
    	                }
    	                elseif ($form_total >=10 && $form_total <= 19)
    	                {
    	                    $score_text[$ef_ident] = "Mittelschwere Demenz";
    	                }
    	                elseif ($form_total >= 20 && $form_total <= 26)
    	                {
    	                    $score_text[$ef_ident] = "Leichte Demenz";
    	                }
    	                elseif ($form_total >=27 && $form_total <= 30)
    	                {
    	                    $score_text[$ef_ident] = "Keine Demenz";
    	                }
    	            }
    	            
    	            
                    if(!empty($score_text[$ef_ident])){
                        $score_text[$ef_ident] = ' ('.$score_text[$ef_ident].')';
                    }
                    
    	            $filename = $form_total.$score_text[$ef_ident];
    	        }
    	        
    	        
    	        $subform->addElement('note', $ef_ident.'val', array(
    	            'value'        => $filename,
    	            'required'     => false,
    	            'filters'      => array('StringTrim'),
    	            // 		    'validators'   => array('NotEmpty'),
    	            'decorators' => array(
    	                'ViewHelper',
    	                array('Errors'),
    	                array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    	                array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
    	            ),
    	            'separator' => PHP_EOL,
    	        ));
    	            
    	    
    	    }
    	    
    	    
    	    return $subform;
    		  
   		}
    		
    		
        /**
         * @author Ancuta
         * 06.09.2019
         * ISPC-2423
         * @param unknown $options
         * @param string $elementsBelongTo
         * @return Zend_Form_SubForm
         */    		
    	private function _create_formular_content_dsv($options = array(), $elementsBelongTo = null){


    	    $subform = new Zend_Form_SubForm();
    	    $subform->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	    ));
    	     
    	
    	    if ( ! is_null($elementsBelongTo)) {
    	        $subform->setOptions(array(
    	            'elementsBelongTo' => $elementsBelongTo
    	        ));
    	    }
 
            //TODO-3549 Lore 14.12.2020
    	    if(!($_REQUEST['form_id'] && !empty($_REQUEST['form_id']))){
    	        foreach($options as $key_q=>$vals_q){
    	            if(is_array($vals_q)){
    	                foreach($vals_q as $key_opt=>$vals_opt){
    	                    //dd($key_q,$key_opt,$vals_opt,$options);
    	                    $options[$key_q][$key_opt]['checked'] = "";
    	                    $options[$key_q][$key_opt]['value'] = "";
    	                    $options[$key_q][$key_opt]['extra_value'] = "";
    	                }
    	            }
    	        }
    	    }
    	    //.   


    	    /*-------------------------------*/
    	    /*-----------PAGE 1 -------------*/
    	    /*-------------------------------*/
    	        	
    	    
    	    
    	    $subform->addElement('note', 'Page_1_title', array(
    	        'value' => "Angaben zur Person der / des Pflegenden",
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h2', 'class'=>'page-title'))
    	        ),
    	    ));
    	    
    	    
    	    
    	    
    	    
    	    /* -------------------------- question 1 ------------------------------------------*/
    	    $question_info = array();
    	    $questions_options = array();
    	    $qid = 1;
    	    $question_info['id'] = $qid;
    	    $question_info['question_id'] = "q_1";
    	    $question_info['question_text'] = "1. Angaben zum Geschlecht der / des Pflegenden";
    	    $questions_options['q_1']  = array("1"=>"Männlich","2"=>"Weiblich");

    	    
    	    
    	    $question = new Zend_Form_SubForm();
    	    $question->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	    ));
            // LABEL
            $question->addElement('note', 'q_Text_'.$question_info['id'], array(
            'value' => $question_info['question_text'],
            'decorators' => array(
            'ViewHelper',
            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
            ),
            ));
            // RADIO
            $question->addElement('radio', 'opt_1', array(
                'label'      => null, 
                'required'   => false,
                'multiOptions' =>  $questions_options[$question_info['question_id']],
                'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
                'decorators'   => array(
                    'ViewHelper',
                    array('Label', array('placement'=> 'IMPLICIT_APPEND' )),
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
                ),
            ));
    	    $subform->addSubform($question, $question_info['question_id']);
    	    
    	     
    	    /* -------------------------- question 2 ------------------------------------------*/
    	     $question_info = array();
    	    $questions_options = array();
    	    $qid = 2;
    	    $question_info['id'] = $qid;
    	    $question_info['question_id'] = "q_2";
    	    $question_info['question_text'] = "2. In welchem verwandtschaftlichen Verhältnis stehen Sie zum Demenzkranken?";
    	    $questions_options['q_2']  = array(
    	        "1"=>"Ehefrau / Ehemann",
    	        "2"=>"Schwester / Bruder",
    	        "3"=>"Tochter / Sohn",
    	        "4"=>"Schwiegertochter / Schwiegersohn",
    	        "5"=>"Sonstiges, nämlich",
    	    );
    	     
    	    $question = new Zend_Form_SubForm();
    	    $question->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	    ));
 
 
            // LABEL
            $question->addElement('note', 'q_Text_'.$question_info['id'], array(
            'value' => $question_info['question_text'],
            'decorators' => array(
            'ViewHelper',
            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
            ),
            ));
            // RADIO
            $question->addElement('radio', 'opt_1', array(
                'label'      => null, 
                'required'   => false,
                'multiOptions' =>  $questions_options[$question_info['question_id']],
                'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
                'decorators'   => array(
                    'ViewHelper',
                    array('Label', array('placement'=> 'IMPLICIT_APPEND' )),
                    array('Errors'),
                    array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
                ),
            ));

                $question->addElement('text', 'opt_2', array(
                    'value' =>$options[$question_info['question_id']]['opt_2']['value'],
                    'decorators' => array(
                    'ViewHelper',
                    array(array('row' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
                    ),
                ));
            
            
            $subform->addSubform($question, $question_info['question_id']);
    	    
    	    /* -------------------------- question 3 ------------------------------------------*/
    	    $question_info = array();
    	    $questions_options = array();
    	    $qid = 3;
    	    $question_info['id'] = $qid;
    	    $question_info['question_id'] = "q_3";
    	    $question_info['question_text'] = "3. Leben Sie mit Ihrem Angehörigen zusammen in einem Haushalt?";
    	    $questions_options['q_3']  = array("1"=>"Ja","2"=>"Nein");
    	    
    	    	
    	    	
    	    $question = new Zend_Form_SubForm();
    	    $question->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	    ));
    	    	
 
    	    
    	    // LABEL
    	    $question->addElement('note', 'q_Text_'.$question_info['id'], array(
    	        'value' => $question_info['question_text'],
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	        ),
    	    ));
    	    // RADIO
    	    $question->addElement('radio', 'opt_1', array(
    	        'label'      => null, 
    	        'required'   => false,
    	        'multiOptions' =>  $questions_options[$question_info['question_id']],
    	        'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
    	        'decorators'   => array(
    	            'ViewHelper',
    	            array('Label', array('placement'=> 'IMPLICIT_APPEND' )),
    	            array('Errors'),
    	            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
    	        ),
    	    ));
    	    $subform->addSubform($question, $question_info['question_id']);
    	    	    	    
    	    
    	    
    	    
    	    
    	    
    	    /* -------------------------- question 4 ------------------------------------------*/
    	    $question_info = array();
    	    $questions_options = array();
    	    $qid = 4;
    	    $question_info['id'] = $qid;
    	    $question_info['question_id'] = "q_4";
    	    $question_info['question_text'] = "4. Sind Sie zurzeit berufstätig?";
    	    $questions_options[$question_info['question_id']]  = array("1"=>"Ja","2"=>"Nein");
    	    	
    	    $question = new Zend_Form_SubForm();
    	    $question->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	    ));
 
    	    // LABEL
    	    $question->addElement('note', 'q_Text_'.$question_info['id'], array(
    	        'value' => $question_info['question_text'],
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	        ),
    	    ));
    	    
    	    
    	    // GET DATA from POST for PDF
    	    $options_q4 = array();
    	    if( !empty($_POST[$elementsBelongTo])){
    	        $options_q4 = $_POST[$elementsBelongTo][$elementsBelongTo];
    	    }

    	    $yes_checked ="";
    	    $no_checked ="";
    	    if($options[$question_info['question_id']]['opt_1']['value'] ==  '1'  ) {
    	        $yes_checked ='checked="checked"';
    	        
    	    } 

    	    $no_checked ="";
    	    if($options[$question_info['question_id']]['opt_1']['value'] ==  '2'  ) {
    	        $no_checked ='checked="checked"';
    	    } 
     
    	    $q_4_opt_2= "";
    	    $q_4_opt_2 = $options[$question_info['question_id']]['opt_2']['value'];
    	    $q_4_a_opt_2="";
    	    if(!empty($no_checked)){
        	    $q_4_a_opt_2 = $options[$question_info['question_id'].'_a']['opt_2']['value'];
    	    }
    	    
    	    if(!empty($options_q4)){
    	        
        	    if($options_q4[$question_info['question_id']]['opt_1'] ==  '1'  ) {
        	        $yes_checked ='checked="checked"';
        	    } else {
        	        $yes_checked ="";
        	    }
    	        
        	    if($options_q4[$question_info['question_id']]['opt_1'] ==  '2'  ) {
        	        $no_checked ='checked="checked"';
        	    } else {
        	        $no_checked ="";
        	    }
    	        
        	    if( ! is_array($options_q4[$question_info['question_id']]['opt_2']) ){
        	        $q_4_opt_2 = $options_q4[$question_info['question_id']]['opt_2'];
        	    } else{
        	        $q_4_opt_2 = $options_q4[$question_info['question_id']]['opt_2']['value'];
        	    }
         
        	    if(!empty($no_checked)){
        	        
            	    if( ! is_array($options_q4[$question_info['question_id'].'_a']['opt_2']) ){
            	        $q_4_a_opt_2 = $options_q4[$question_info['question_id'].'_a']['opt_2'];
            	    } else{
            	        $q_4_a_opt_2 = $options_q4[$question_info['question_id'].'_a']['opt_2']['value'];
            	    }
        	    } else{ 
        	    }
        	    
        	    
    	    } 
    	    $html_str = "";
    	    $html_str .= '<div class="main_option"> ';
    	    $html_str .= '<input type="radio"  name="dsv[dsv][q_4][opt_1]" value="1" '.$yes_checked.' />';
    	    
    	    $html_str .= '<label>Ja, mit <input type="text"  name="dsv[dsv][q_4][opt_2]" value="'.$q_4_opt_2.'"  id="dsv-dsv-q_4-opt_2"  placeholder="'.$this->translate("integer 1-80").'"  onkeyup="isInteger(this,1,80)"  />  Std./Woche </label>';
    	    $html_str .= '</div>';
    	    

    	    $question->addElement('note', 'q_4_opt_1-1', array(
    	        'value' => $html_str,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'complex_options'))
    	        ),
    	    ));
    	    
    	    $html_str = "";
    	    $html_str .= '<div class="main_option"> ';
    	    $html_str .= '<label> ';
    	    $html_str .= '<input type="radio" name="dsv[dsv][q_4][opt_1]" value="2"  '.$no_checked.' />';
    	    $html_str .= 'Nein, denn ich bin: ';
    	    $html_str .= '</label>';
    	    $html_str .= '</div>';
    	    
    	    $question->addElement('note', 'q_4_opt_1-2', array(
    	        'value' => $html_str,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'complex_options'))
    	        ),
    	    ));

    	    
    	    if(!empty($no_checked)){
    	        
        	    $q4a1 ="";
        	    $q4a2 ="";
        	    $q4a3 ="";
        	    if ( $options[$question_info['question_id'].'_a']['opt_1']['value'] ==  '1'  ) {
        	        $q4a1 ='checked="checked"';
        	    } else if($options[$question_info['question_id'].'_a']['opt_1']['value'] ==  '2'  ) {
        	        $q4a2 ='checked="checked"';
        	    } else if($options[$question_info['question_id'].'_a']['opt_1']['value'] ==  '3'  ) {
        	        $q4a3 ='checked="checked"';
        	    }
        	    
        	    if(!empty($options_q4)){
        	         
        	        //TODO-3966 Lore 17.03.2021  era $options_q4[$question_info['question_id']]['opt_1']
        	        if($options_q4[$question_info['question_id'].'_a']['opt_1'] ==  '1'  ) {
        	            $q4a1 ='checked="checked"';
        	        }
        	        elseif($options_q4[$question_info['question_id'].'_a']['opt_1'] ==  '2'  ) {
        	            $q4a2 ='checked="checked"';
        	        }
        	        elseif($options_q4[$question_info['question_id'].'_a']['opt_1'] ==  '3'  ) {
        	            $q4a3 ='checked="checked"';
        	        }
        	    }
    	    }
    	    
    	    $html_str = "";
    	    $html_str .= '<div class="side_option"> ';
    	    $html_str .= '<input type="radio" name="dsv[dsv][q_4_a][opt_1]" value="1" '.$q4a1.'/>';
    	    $html_str .= '<label>';
    	    $html_str .= 'Rentner/in';
    	    $html_str .= '</label> ';
    	    $html_str .= '</div> ';
    	    
    	    $question->addElement('note', 'q_4_a_opt_1-1', array(
    	        'value' => $html_str,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'complex_options'))
    	        ),
    	    ));
    	    
    	    
    	    
    	    $html_str = "";    	    
    	    $html_str .= '<div class="side_option"> ';
    	    $html_str .= '<input type="radio"  name="dsv[dsv][q_4_a][opt_1]"  value="2" '.$q4a2.'/>';
    	    $html_str .= '<label>';
    	    $html_str .= 'Arbeitslos';
    	    $html_str .= '</label> ';
    	    $html_str .= '</div> ';
    	    $question->addElement('note', 'q_4_a_opt_1-2', array(
    	        'value' => $html_str,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'complex_options'))
    	        ),
    	    ));
    	    
    	    
    	    $html_str = "";    	    
    	    $html_str .= '<div class="side_option"> ';
    	    $html_str .= '<input type="radio"  name="dsv[dsv][q_4_a][opt_1]"  value="3" '.$q4a3.' />';
    	    $html_str .= '<label>';
    	    $html_str .= 'Sonstiges, nämlich<input type="text" name="dsv[dsv][q_4_a][opt_2]" value="'.$q_4_a_opt_2.'">';
    	    $html_str .= '</label> ';
    	    $html_str .= '</div> ';
    	    
    	    
    	    $question->addElement('note', 'q_4_a_opt_1-3', array(
    	        'value' => $html_str,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'complex_options'))
    	        ),
    	    ));
    	    	
    	     $subform->addSubform($question, $question_info['question_id']);
    	    	    	    
    	    
    	     /* -------------------------- question 5 ------------------------------------------*/
    	     $question_info = array();
    	     $questions_options = array();
    	     $qid = 5;
    	     $question_info['id'] = $qid;
    	     $question_info['question_id'] = "q_5";
    	     $question_info['question_text'] = "5. Welche Unterstützungsangebote für pflegende Angehörige nehmen Sie zurzeit wahr? ";
    	     $questions_options[$question_info['question_id']]  = array(
    	         "1"=>"Keine",
    	         "2"=>"Tagespflege",
    	         "3"=>"Gesprächsgruppe",
    	         "4"=>"Sonstiges",
    	     );
    	     	
    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	     ));
    	     
    	     
    	     
    	     // LABEL
    	     $question->addElement('note', 'q_Text'.$question_info['id'], array(
    	         'value' => $question_info['question_text'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	         ),
    	     ));
    	     
    	     // CHECKBOXES
    	     $q_ident = 1 ;
    	     foreach ($questions_options[$question_info['question_id']] as $q_opt_k => $q_opt) {
    	         $question->addElement('checkbox', 'opt_'.$q_ident, array(
    	             'label'      => $q_opt,
    	             'required'   => false,
    	             'value' =>  $options[$question_info['question_id']]['opt_'.$q_ident]['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_'.$q_ident]['value'] : 0,
    	             'decorators'   => array(
    	                 'ViewHelper',
    	                 array('Label', array('placement'=> 'IMPLICIT_APPEND')),
    	                 array('Errors'),
    	                 array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
    	             ),
    	         ));
    	         $q_ident++;
    	     }
    	     
    	     $question->addElement('text', 'opt_6', array(
    	         'value' =>$options[$question_info['question_id']]['opt_6']['value'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('row' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
    	         ),
    	     ));
    	     
    	      
    	     $subform->addSubform($question, $question_info['question_id']);
    	     	
    	     
    	    
    	     /* -------------------------- question 6 ------------------------------------------*/
    	     $question_info = array();
    	     $questions_options = array();
    	     $qid = 6;
    	     $question_info['id'] = $qid;
    	     $question_info['question_id'] = "q_6";
    	     $question_info['question_text'] = "6. Wie viel Zeit wenden Sie pro Woche für die Pflege Ihres Angehörigen auf?";
    	     $questions_options[$question_info['question_id']]  = array(
    	         "1"=>"Keine",
    	         "2"=>"Tagespflege",
    	         "3"=>"Gesprächsgruppe",
    	         "4"=>"Sonstiges",
    	     );
    	     	
    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	     ));
    	     
    	     // LABEL
    	     $question->addElement('note', 'q_Text'.$question_info['id'], array(
    	         'value' => $question_info['question_text'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	         ),
    	     ));
    	     
    	     $question->addElement('text', 'opt_1', array(
    	         'value' =>$options[$question_info['question_id']]['opt_1']['value'],
    	         'label' =>"Std. / Woche",
    	         'decorators' => array(
    	             'ViewHelper',
    	             array('Label', array('placement'=> 'IMPLICIT_APPEND')),
    	             array(array('row' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
    	         ),
    	         'placeholder'=>$this->translate("integer 0-168"),
    	         'onkeyup' => "isInteger(this,0,168)"
    	     ));
    	      
    	     $subform->addSubform($question, $question_info['question_id']);
    	     	



    	     /* -------------------------- question 7 ------------------------------------------*/
    	     $question_info = array();
    	     $questions_options = array();
    	     $qid = 7;
    	     $question_info['id'] = $qid;
    	     $question_info['question_id'] = "q_7";
    	     $question_info['question_text'] = "7. Sind weitere Privatpersonen an der Pflege Ihres Angehörigen beteiligt? ";
    	     $questions_options[$question_info['question_id']]  = array("1"=>"Nein","2"=>"Ja, und zwar:");
    	     
    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	     ));
    	     
    	     // LABEL
    	     $question->addElement('note', 'q_Text_'.$question_info['id'], array(
    	         'value' => $question_info['question_text'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	         ),
    	     ));
    	     	
    	     // RADIO
    	     $question->addElement('radio', 'opt_1', array(
    	         'label'      => null,
    	         'required'   => false,
    	         'multiOptions' =>  $questions_options[$question_info['question_id']],
    	         'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
    	         'decorators'   => array(
    	             'ViewHelper',
    	             array('Label', array('placement'=> 'IMPLICIT_APPEND' )),
    	             array('Errors'),
    	             array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
    	         ),
    	         'class'=>'question_7'
    	     ));
    	     $subform->addSubform($question, $question_info['question_id']);
    	     	
    	     
    	     
    	     // GET DATA from POST for PDF
    	     $options_q7 = array();
    	     if( !empty($_POST[$elementsBelongTo])){
    	         $options_q7 = $_POST[$elementsBelongTo][$elementsBelongTo];
    	     }

    	     $q7a_cnt_persons ="";
    	     if($options[$question_info['question_id'].'_a']['opt_1']['value'] ==  '1' || (!empty($options_q7) && $options_q7[$question_info['question_id'].'_a']['opt_1'] ==  '1' )   ) {
    	         $q7a_cnt_persons ='checked="checked"';
    	     }
    	     
    	     $q7a_other_persons ="";
    	     if($options[$question_info['question_id'].'_a']['opt_1']['value'] ==  '2' || (!empty($options_q7) && $options_q7[$question_info['question_id'].'_a']['opt_1'] ==  '2' )   ) {
    	         $q7a_other_persons ='checked="checked"';
    	     }
    	     
    	     
    	     $q_7_a_opt_2="";
    	     $q_7_a_opt_2 = $options[$question_info['question_id'].'_a']['opt_2']['value'];
    	     $q_7_a_opt_3="";
    	     $q_7_a_opt_3 = $options[$question_info['question_id'].'_a']['opt_3']['value'];

    	     
    	     if(!empty($options_q7) ){
        	     if( ! is_array($options_q7[$question_info['question_id'].'_a']['opt_2']) ){
        	         $q_7_a_opt_2 = $options_q7[$question_info['question_id'].'_a']['opt_2'];
        	     } else{
        	         $q_7_a_opt_2 = $options_q7[$question_info['question_id'].'_a']['opt_2']['value'];
        	     }
        
        	     if( ! is_array($options_q7[$question_info['question_id'].'_a']['opt_3']) ){
        	         $q_7_a_opt_3 = $options_q7[$question_info['question_id'].'_a']['opt_3'];
        	     } else{
        	         $q_7_a_opt_3 = $options_q7[$question_info['question_id'].'_a']['opt_3']['value'];
        	     }
    	     }
    	     $display ="";
    	     if($options[$question_info['question_id']]['opt_1']['checked'] == 'yes' && $options[$question_info['question_id']]['opt_1']['value'] == "2"){
    	         $display = 'display_block';
    	     }    	     
    	     
    	     
    	     $html_str = "";
    	     $html_str .= '<div class="side_option"> ';
    	     $html_str .= '<input type="radio" class="q7extraradio" name="dsv[dsv]['.$question_info['question_id'].'_a][opt_1]" value="1"  '.$q7a_cnt_persons.'/>';
    	     $html_str .= '<label>';
    	     $html_str .= 'Anzahl weiterer Personen: <input type="text" name="dsv[dsv]['.$question_info['question_id'].'_a][opt_2]" value="'.$q_7_a_opt_2.'"   id="dsv-dsv-'.$question_info['question_id'].'_a-opt_2"  placeholder="'.$this->translate("integer only").'"  onkeyup="isInteger(this,0,false)">';
    	     $html_str .= '</label> ';
    	     $html_str .= '</div> ';
    	     $question->addElement('note', ''.$question_info['question_id'].'_a_opt_1-1', array(
    	         'value' => $html_str,
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'complex_options question_7_extra '.$display.' '))
    	         ),
    	     ));    	     
    	     
    	     	
    	     $html_str = "";
    	     $html_str .= '<div class="side_option"> ';
    	     $html_str .= '<input type="radio" class="q7extraradio"  name="dsv[dsv]['.$question_info['question_id'].'_a][opt_1]"  value="2" '.$q7a_other_persons.'/>';
    	     $html_str .= '<label>';
    	     $html_str .= 'Pflegeaufwand der weiteren Personen: <input type="text" name="dsv[dsv]['.$question_info['question_id'].'_a][opt_3]" value="'.$q_7_a_opt_3.'" id="dsv-dsv-'.$question_info['question_id'].'_a-opt_3"  placeholder="'.$this->translate("integer only").'"  onkeyup="isInteger(this,0,false)" >Std. / Woche';
    	     $html_str .= '</label> ';
    	     $html_str .= '</div> ';
    	     $question->addElement('note', ''.$question_info['question_id'].'_a_opt_1-2', array(
    	         'value' => $html_str,
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'complex_options question_7_extra '.$display.' '))
    	         ),
    	     ));
    	     	
    	     $subform->addSubform($question, $question_info['question_id']);
    	    
    	    /*-------------------------------*/
    	    /*-----------PAGE 2 -------------*/
    	    /*-------------------------------*/

    	     $subform->addElement('note', 'Page_2_title', array(
    	         'value' => "Angaben zum demenzkranken Menschen",
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h2', 'class'=>'page-title pt2','style'=>'page-break-before: always;'))
    	         ),
    	     ));
    	     
    	     
    	     /* -------------------------- question 8- 1 from Page 2  ------------------------------------------*/
    	     $question_info = array();
    	     $questions_options = array();
    	     $qid = 8;
    	     $question_info['id'] = $qid;
    	     $question_info['question_id'] = "q_8";
    	     $question_info['question_text'] = "1. Angaben zum Geschlecht des demenzkranken Menschen ";
    	     $questions_options[$question_info['question_id']]  = array("1"=>"Männlich","2"=>"Weiblich");
    	     	
    	     	
    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	     ));
    	     
    	     // LABEL
    	     $question->addElement('note', 'q_Text_'.$question_info['id'], array(
    	         'value' => $question_info['question_text'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	         ),
    	     ));
    	     // RADIO
    	     $question->addElement('radio', 'opt_1', array(
    	         'label'      => null,
    	         'required'   => false,
    	         'multiOptions' =>  $questions_options[$question_info['question_id']],
    	         'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
    	         'decorators'   => array(
    	             'ViewHelper',
    	             array('Label', array('placement'=> 'IMPLICIT_APPEND' )),
    	             array('Errors'),
    	             array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
    	         ),
    	     ));
    	     $subform->addSubform($question, $question_info['question_id']);
    	     	
    	     

    	     /* -------------------------- question 9 - 2 frpm page 2  ------------------------------------------*/
    	     $question_info = array();
    	     $questions_options = array();
    	     $qid = 9;
    	     $question_info['id'] = $qid;
    	     $question_info['question_id'] = "q_9";
    	     $question_info['question_text'] = "2. Geburtsdatum:";
 
    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	     ));
    	     
    	     // LABEL
    	     $question->addElement('note', 'q_Text'.$question_info['id'], array(
    	         'value' => $question_info['question_text'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	         ),
    	     ));
    	     
    	     $question->addElement('text', 'opt_1', array(
    	         'value' =>$options[$question_info['question_id']]['opt_1']['value'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array('Label', array('placement'=> 'IMPLICIT_APPEND')),
    	             array(array('row' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
    	         ),
    	         'class'=>'date'
    	     ));
    	      
    	     $subform->addSubform($question, $question_info['question_id']);


    	     /* -------------------------- question 10 - 3 frpm page 2  ------------------------------------------*/
    	     $question_info = array();
    	     $questions_options = array();
    	     $qid = 10;
    	     $question_info['id'] = $qid;
    	     $question_info['question_id'] = "q_10";
    	     $question_info['question_text'] = "3. Seit wann müssen Sie sich verstärkt um Ihren Angehörigen kümmern? :";
    	     
    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	     ));
    	     
    	     // LABEL
    	     $question->addElement('note', 'q_Text'.$question_info['id'], array(
    	         'value' => $question_info['question_text'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	         ),
    	     ));
    	     
    	     
    	     
    	     
    	     //AICI
    	     // GET DATA from POST for PDF
    	     $options_q10 = array();
    	     if( !empty($_POST[$elementsBelongTo])){
    	         $options_q10 = $_POST[$elementsBelongTo][$elementsBelongTo];
    	     }
    	     
    	     $q_10_a_opt_1="";
    	     $q_10_a_opt_1 = $options[$question_info['question_id']]['opt_1']['value'];
    	     if(!empty($options_q10) ){
    	         if( ! is_array($options_q10[$question_info['question_id']]['opt_1']) ){
    	             $q_10_a_opt_1 = $options_q10[$question_info['question_id']]['opt_1'];
    	         } else{
    	             $q_10_a_opt_1 = $options_q10[$question_info['question_id']]['opt_1']['value'];
    	         }
    	     }
    	     
    	     
    	     
    	     $html_str = "";
    	     $html_str .= '<label>';
    	     $html_str .= 'Seit: <input type="text" name="dsv[dsv][q_10][opt_1]" value="'.$q_10_a_opt_1.'" class="year_slot" id="dsv-dsv-'.$question_info['question_id'].'-opt_2"  placeholder="'.$this->translate("integer 0-99").'"  onkeyup="isInteger(this,0,99)" />Jahren';
    	     $html_str .= '</label> ';
    	     $question->addElement('note', 'q_10_opt_1', array(
    	         'value' => $html_str,
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>''))
    	         ),
    	     ));
    	     $subform->addSubform($question, $question_info['question_id']);
    	     
    	     /* -------------------------- question 11 - 4 from Page 2  ------------------------------------------*/
    	     $question_info = array();
    	     $questions_options = array();
    	     $qid = 11;
    	     $question_info['id'] = $qid;
    	     $question_info['question_id'] = "q_11";
    	     $question_info['question_text'] = "4. Liegt eine ärztliche Demenzdiagnose vor?";
    	     $questions_options[$question_info['question_id']]  = array("1"=>"Ja","2"=>"Nein");
    	      
    	      
    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	     ));
    	     
    	     // LABEL
    	     $question->addElement('note', 'q_Text_'.$question_info['id'], array(
    	         'value' => $question_info['question_text'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	         ),
    	     ));
    	     // RADIO
    	     $question->addElement('radio', 'opt_1', array(
    	         'label'      => null,
    	         'required'   => false,
    	         'multiOptions' =>  $questions_options[$question_info['question_id']],
    	         'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
    	         'decorators'   => array(
    	             'ViewHelper',
    	             array('Label', array('placement'=> 'IMPLICIT_APPEND' )),
    	             array('Errors'),
    	             array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
    	         ),
    	     ));
    	     $subform->addSubform($question, $question_info['question_id']);
    	      
    	         	     
    	     
    	     /* -------------------------- question 12 - 5 from Page 2  ------------------------------------------*/
    	     $question_info = array();
    	     $questions_options = array();
    	     $qid = 12;
    	     $question_info['id'] = $qid;
    	     $question_info['question_id'] = "q_12";
    	     $question_info['question_text'] = "5. Welchen Pflegegrad hat Ihr Angehöriger? ";
    	     $questions_options[$question_info['question_id']]  = array(
    	         "1"=>"Grad 1",
    	         "2"=>"Grad 2",
    	         "3"=>"Grad 3",
    	         "4"=>"Grad 4",
    	         "5"=>"Grad 5",
    	         
    	     );
    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'formular_questions qid-'.$question_info['question_id'])),
    	     ));
    	     
    	     // LABEL
    	     $question->addElement('note', 'q_Text_'.$question_info['id'], array(
    	         'value' => $question_info['question_text'],
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'question_text'))
    	         ),
    	     ));
    	     // RADIO
    	     $question->addElement('radio', 'opt_1', array(
    	         'label'      => null,
    	         'required'   => false,
    	         'multiOptions' =>  $questions_options[$question_info['question_id']],
    	         //'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
    	         'value' => 0,     //TODO-3549 Lore 23.10.2020
    	         'decorators'   => array(
    	             'ViewHelper',
    	             array('Label', array('placement'=> 'IMPLICIT_APPEND' )),
    	             array('Errors'),
    	             array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class'=>'check_row')),
    	         ),
    	     ));
    	     $subform->addSubform($question, $question_info['question_id']);
    	      
    	     
    	     /*-------------------------------*/
    	     /*-----------PAGE 3 -------------*/
    	     /*-------------------------------*/
    	     
    	     $subform->addElement('note', 'Page_3_title', array(
    	         'value' => "Demenzspezifisches Screening zur Versorgungssituation (DSV)",
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'h2', 'class'=>'page-title pt3','style'=>'page-break-before: always;'))
    	         ),
    	     ));
    	     
    	     
    	     
    	     
 

    	     
    	     
    	     

    	     $question = new Zend_Form_SubForm();
    	     $question->clearDecorators()
    	     ->setDecorators( array(
    	         'FormElements',
    	         array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	     ));
    	     
 
    	     
    	     
    	     $table_question_info = array();
    	     $table_question_info = array(
    	         'qt_1' => array(
    	             'question_id'=>'qt_1',
    	             'nr'=>'1',
    	             'category'=>'Informelles Hilfesystem ',
    	             'description'=>'<b>Diese Frage wird gestellt, wenn sich die/der Betroffene alleine (ohne Begleitung) in der Arztpraxis befindet: </b> <br/>Haben Sie in Ihrem Umfeld Menschen, die Ihnen bei Bedarf helfen?',
    	             'info_row'=>'<u>Antwort spricht für das Fehlen eines informellen Hilfesystems <b>-> NEIN</b></u><br/>(z.B. "Nein, von meinen Verwandten/Bekannten ist keiner in der Lage/bereit, mich zu unterstützen", "Nein, ich habe keine Verwandte/Bekannte, die in der Nähe leben") Antwort spricht für das Vorhandensein eines informellen Hilfesystems ODER der/die Betroffene kommt in Begleitung von Verwandten/Bekannten in die Arztpraxis <b>-> JA</b></u>'
    	         ),
    	          
    	         'qt_2' => array(
    	             'question_id'=>'qt_2',
    	             'nr'=>'2',
    	             'category'=>'Mobilität',
    	             'description'=>'<i>Haben Sie in den letzten 4 Wochen Beeinträchtigungen in Bezug auf die Mobilität des/der Betroffenen festgestellt? (Können Sie das genauer beschreiben?)</i>',
    	             'info_row'=>'<u>Antwort spricht für eine deutlich eingeschränkte Mobilität <b>-> JA</b></u><br/><i>(z.B. einmal oder mehrfach gestürzt, viele blaue Flecke sind vorhanden, steht alleine auf ohne die Balance halten zu können)</i> '
    	         ),
    	         'qt_3' => array(
    	             'question_id'=>'qt_3',
    	             'nr'=>'3',
    	             'category'=>'Kontinenz',
    	             'description'=>'<i>Haben Sie in den letzten 4 Wochen Beeinträchtigungen im Zusammenhang mit der Kontrolle über Körperausscheidungen beobachtet? (Können Sie das genauer beschreiben?)</i>',
    	             'info_row'=>'<u>Antwort spricht für bedeutsame Beeinträchtigungen aufgrund von Inkontinenz <b>-> JA</b></u><br/><i>(z.B. der Urin/Stuhlgang kann nicht mehr gehalten werden, der/die Betroffene muss mehrmals am Tag aufgrund der Inkontinenz seine Kleidung wechseln)</i>'
    	         ),
    	         'qt_4' => array(
    	             'question_id'=>'qt_4',
    	             'nr'=>'4',
    	             'category'=>'Ernährung',
    	             'description'=>'<i>Haben Sie im Zusammenhang mit der Nahrungsaufnahme in den letzten 4 Wochen Beeinträchtigungen bemerkt? (Können Sie das genauer beschreiben?)</i>',
    	             'info_row'=>'<u>Antwort spricht für deutliche Probleme mit der Nahrungsaufnahme <b>-> JA</b></u><br/><i>(z.B. der/die Betroffene trinkt weniger als 1300 ml pro Tag, es sind Schluckstörungen aufgetreten, der/die Betroffene weiß mit der Nahrung nichts anzufangen)</i>'
    	         ),
    	          
    	         'qt_5' => array(
    	             'question_id'=>'qt_5',
    	             'nr'=>'5',
    	             'category'=>'Ungewohnte Verhaltensweisen',
    	             'description'=>'<i>Haben Sie in den letzten 4 Wochen ungewohnte Verhaltensweisen beobachtet? (Können Sie das genauer beschreiben?)</i>',
    	             'info_row'=>'<u>Antwort spricht für auffällige Verhaltensweisen <b>-> JA</b></u><br/> <i>(z.B. der/die Betroffene vergisst den Herd auszumachen, der/die Betroffene läuft ohne erkennbaren Grund und/oder adäquate Kleidung auf die Straße, der/die Betroffene wird plötzlich aggressiv, der/die Betroffene schreit laut ohne erkennbaren Grund)</i>'
    	         ),
    	         'qt_6' => array(
    	             'question_id'=>'qt_6',
    	             'nr'=>'6',
    	             'category'=>'Hilfesystem',
    	             'description'=>'<i>Haben sich in den letzten 4 Wochen Probleme mit dem arrangierten Pflegesystem ergeben? (Können Sie das genauer beschreiben?)</i>',
    	             'info_row'=>'<u>Antwort spricht für deutliche Defizite im Hilfesystem <b>-> JA</b></u><br/><i>(z.B. Nachbarn/Freunde/weitere Angehörige, die bei der Versorgung unterstützen sind ausgefallen, die privat finanzierte 24h Hilfe steht nicht mehr zur Verfügung, der Pflegedienst kann seine zugesagten Leistungen nicht mehr erbringen)</i>'
    	         ),
    	          
    	         'qt_7' => array(
    	             'question_id'=>'qt_7',
    	             'nr'=>'7',
    	             'category'=>'Persönliche Situation der Hauptpflegeperson ',
    	             'description'=>'<i>Welche Auswirkungen hatte die Pflege der/des Betroffenen auf ihr Leben und ihre eigene gesundheitliche Situation in den letzten 4 Wochen? (Können Sie das genauer beschreiben?)</i>',
    	             'info_row'=>'<u>Antwort spricht für deutliche Beeinträchtigungen in gesundheitlichen, sozialen, beruflichen oder anderen wichtigen Lebensbereichen <b>-> JA</b></u><br/><i>(z.B. ich schlafe schlecht, ich fühle mich überlastet und weiß nicht wie lange ich die Pflegesituation noch tragen kann, manchmal muss ich mich sehr zurücknehmen, damit ich selbst nicht aggressiv werde, ich befinde mich auf Grund der belastenden Situation in ärztlicher Behandlung)</i>'
    	         ),
    	         'qt_8a' => array(
    	             'question_id'=>'qt_8a',
    	             'nr'=>'8a',
    	             'category'=>'Versorgungssituation ',
    	             'description'=>'<b>Bei den folgenden zwei Fragen handelt es sich um die fachliche Einschätzung der Interviewer:</b> <br/><i>Anhand meines persönlichen Eindrucks der/des Betroffenen und/oder der Hauptpflegeperson sehe ich ein erhöhtes Versorgungsrisiko oder eine Versorgungskrise.</i>',
    	             'info_row'=>''
    	         ),
    	         'qt_8b' => array(
    	             'question_id'=>'qt_8b',
    	             'nr'=>'8b',
    	             'category'=>'',
    	             'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen:',
    	             'info_row'=>''
    	         ),
    	          

    	         
    	     );
 
    	     	
    	     
    	     if(  !empty($_POST[$elementsBelongTo])){
    	         $options = $_POST[$elementsBelongTo][$elementsBelongTo];
    	         $options['form_type'] = $elementsBelongTo;
    	     }
    	     	
    	     $html_str = "";
    	     $html_str .= '<table class="dsv SimpleTable" cellpadding="0" cellspacing="0"> ';
    	     $html_str .= '<tr>';
    	     $html_str .= '<th width="5%" alight="left" class="qtext_th">#</th>';
   	         $html_str .= '<th width="15%" class="q_opt_text_th">Kategorie</th>';
   	         $html_str .= '<th width="60%" class="q_opt_text_th">Exploration</th>';
   	         $html_str .= '<th width="10%" class="q_opt_text_th">Nein</th>';
   	         $html_str .= '<th width="10%" class="q_opt_text_th">JA</th>';
    	     $html_str .= '</tr>';

    	     foreach($table_question_info as $question_id=>$qinfo){
        	     // row 1 
    	         $yes_class = "td_red";
    	         $no_class = "td_green";
    	         
    	         if($qinfo['question_id'] == "qt_1"){
    	             $yes_class = "td_green";
    	             $no_class = "td_red";
    	         }
    	         
    	         $no_checked ="";
    	         if($options[$qinfo['question_id']]['opt_1']['value'] ==  '1'  ) {
    	             $no_checked ='checked="checked"';
    	         }
    	         $yes_checked ="";
    	         if($options[$qinfo['question_id']]['opt_1']['value'] ==  '2'  ) {
    	             $yes_checked ='checked="checked"';
    	         }
    	         
    	         
    	         $html_str .= '<tr>';
        	     $html_str .= '<td>'.$qinfo["nr"].'</td>';
        	     $html_str .= '<td>'.$qinfo["category"].'</td>';
        	     if($qinfo['question_id'] == "qt_8b"){
        	         $special_height = "style='vertical-align: top; height: 150px;'";
        	     }
        	     $html_str .= '<td '.$special_height.'>';
        	     if($qinfo['question_id'] != "qt_8b" && $qinfo['question_id'] != "qt_8a"){
        	       $html_str .= '<span class="info_triger dontPrint"></span>';
        	     }
        	     $html_str .= '<span class="description">'.$qinfo["description"].'</span>';
        	     $html_str .= '<span class="row_info dontPrint">'.$qinfo["info_row"].'</span>';
        	     
        	     if($qinfo['question_id'] == "qt_8b"){
        	         $html_str .= '<div class="tfree_texts">';

        	         if( ! is_array($options[$qinfo['question_id'].'_1']['opt_1']) ){
        	             $qt_8b_1_opt_1 = $options[$qinfo['question_id'].'_1']['opt_1'];
        	         } else{
        	             $qt_8b_1_opt_1 = $options[$qinfo['question_id'].'_1']['opt_1']['value'];
        	         } 
        	         if( ! is_array($options[$qinfo['question_id'].'_1']['opt_2']) ){
        	             $qt_8b_1_opt_2 = $options[$qinfo['question_id'].'_1']['opt_2'];
        	         } else{
        	             $qt_8b_1_opt_2 = $options[$qinfo['question_id'].'_1']['opt_2']['value'];
        	         } 
        	         if( ! is_array($options[$qinfo['question_id'].'_1']['opt_3']) ){
        	             $qt_8b_1_opt_3 = $options[$qinfo['question_id'].'_1']['opt_3'];
        	         } else{
        	             $qt_8b_1_opt_3 = $options[$qinfo['question_id'].'_1']['opt_3']['value'];
        	         } 
 
        	         $html_str .= '<textarea rows="1" cols="50" name="dsv[dsv]['.$qinfo['question_id'].'_1][opt_1]" style="text-align: left;">'.$qt_8b_1_opt_1.'</textarea>';
        	         $html_str .= '<textarea rows="1" cols="50" name="dsv[dsv]['.$qinfo['question_id'].'_1][opt_2]"  style="text-align: left;">'.$qt_8b_1_opt_2.'</textarea>';
        	         $html_str .= '<textarea rows="1" cols="50"  name="dsv[dsv]['.$qinfo['question_id'].'_1][opt_3]"  style="text-align: left;">'.$qt_8b_1_opt_3.'</textarea>';
        	         $html_str .= '</div>';
        	     }
        	     $html_str .= '</td>';
        	     
        	     $html_str .= '<td class="'.$no_class.'">';
//         	     $html_str .= '<input type="radio" class="calculate_score cs_yes '.$qinfo['question_id'].'" onchange="calcscore($(this),\''.$qinfo['question_id'].'\',\'radio\')" id="dsv-form_content-'.$qinfo['question_id'].'-opt_1-1" name="dsv[dsv]['.$qinfo['question_id'].'][opt_1]" value="1" '.$no_checked.'  />';
        	     $html_str .= '<input type="radio" class="calculate_score cs_yes '.$qinfo['question_id'].'" onchange="calcscore($(this),\''.$qinfo['question_id'].'\',\'radio\')" id="'.$qinfo['question_id'].'-no" name="dsv[dsv]['.$qinfo['question_id'].'][opt_1]" value="1" '.$no_checked.'  />';
        	     $html_str .= '</td>';
        	     $html_str .= '<td class="'.$yes_class.'">';
//         	     $html_str .= '<input type="radio" class="calculate_score cs_no '.$qinfo['question_id'].'" onchange="calcscore($(this),\''.$qinfo['question_id'].'\',\'radio\')" id="dsv-form_content-'.$qinfo['question_id'].'-opt_1-2" name="dsv[dsv]['.$qinfo['question_id'].'][opt_1]" value="2" '.$yes_checked.'  />';
        	     $html_str .= '<input type="radio" class="calculate_score cs_no '.$qinfo['question_id'].'" onchange="calcscore($(this),\''.$qinfo['question_id'].'\',\'radio\')" id="'.$qinfo['question_id'].'-yes" name="dsv[dsv]['.$qinfo['question_id'].'][opt_1]" value="2" '.$yes_checked.'  />';
        	     $html_str .= '</td>';
        	     $html_str .= '</tr>';
        	     if($qinfo['question_id'] == "qt_7"){
        	         $html_str .= '<tr><td colspan="5"></td></tr>';
        	     }
    	     }
    	      
    	     $html_str .= '</table>';
    	     
    	     $question->addElement('note', 'sblock', array(
    	         'value' => $html_str,
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'cmai_form_block'))
    	         ),
    	     ));
    	     
    	     $subform->addSubform($question, "big_table");
    	     
    	     

    	     $subform->addElement('note', 'score_title', array(
    	         'value' => "Entscheidungsalgorithmus:",
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('ltag' => 'HtmlTag'), array('tag' => 'b', 'class'=>'score-title'))
    	         ),
    	     ));
    	     // SCORE CALCULATION
    	    
    	     
    	     $use_old_score = false; // Bypass old score  on 13.03.2020
    	     if($use_old_score){
        	     /*
        	     GREEN (Stabile Versorgung) if
        	     Question 1 = yes
        	     AND
        	     maximum TWO questions are YES (question 2-5)
        	     AND
        	     Question 6 AND 7 are NO
        	     RED (Erhöhtes Versorgungsrisiko oder Versorgungskrise)
        	     */
        	    
                  $green_score=0;
                  $red_score=0;
                  //maximum TWO questions are YES (question 2-5)
                  $yes_question = 0 ;
                  if($options['qt_2']['opt_1']['value'] == 2){
                      $yes_question++;
                  }
                  if($options['qt_3']['opt_1']['value'] == 2){
                      $yes_question++;
                  }
                  if($options['qt_4']['opt_1']['value'] == 2){
                      $yes_question++;
                  }
                  if($options['qt_5']['opt_1']['value'] == 2){
                      $yes_question++;
                  }
                  
        	     if( $options['qt_1']['opt_1']['value'] == 2
        	         && $yes_question <=2
        	         && ($options['qt_6']['opt_1']['value'] == 1 && $options['qt_7']['opt_1']['value'] == 1 && $options['qt_8a']['opt_1']['value'] == 1 )
                    ){
        	         $green_score = 1;
        	     } else{
        	         $red_score = 1;
        	     }
    	     } 
    	     else
    	     {
    	         //Ancuta 13.03.2020
    	         // NEW RULE: We change so   ONLY   8a is taken into consideration
    	         // Status is GREEN if 8a is no
    	         // Status is RED if 8a is yes
    	         
    	         $green_score=0;
    	         $red_score=0;
    	         if(  $options['qt_8a']['opt_1']['value'] == 1 ){
    	             $green_score = 1;
    	         } else{
    	             $red_score = 1;
    	         }
    	     }

    	     
    	     $score_display_green = "";
    	     $score_display_red = "";
    	     $score_info = "";
    	     
    	     if($green_score ==1){
    	         $score_info = "green";
    	         $score_display_green = ' display_block';
    	         $score_display_red = ' ';
    	     }
    	     if($red_score ==1){
    	         $score_info = "red";
    	         $score_display_green = ' ';
    	         $score_display_red = 'display_block ';
    	     }
    	     
    	     

    	     
    	     $html_str= "";
    	     $html_str.= '<div class="dsv_green_score '.$score_display_green.'">';
    	     $html_str.= '<p><b>Stabile Versorgung</b> wenn Frage 8a mit Nein beantwortet.</p>';
//     	     $html_str.= '<b>Stabile Versorgung:</b> wenn Frage 8a mit Nein beantwortet.';
//     	     $html_str.= '<p> - Frage 1. wurde mit Ja beantwortet <br/>und<br/> - max. zwei Fragen (2.–5.) wurden mit Ja beantwortet<br/>und<br/>- die Fragen 6. und 7. wurden mit Nein beantwortet.</p>';
    	     $html_str.= '</div>';
    	     	
    	     $html_str.= '<div class="dsv_red_score '.$score_display_red.'">';
    	     $html_str.= '<h5>Erhöhtes Versorgungsrisiko oder Versorgungskrise:</h5>';
    	     $html_str.= '<p> Alle Antwortkombinationen die nicht der Antwortkombinationen der stabilen Versorgung entsprechen.</p>';
    	     $html_str.= '</div>';
    	     	
    	     $subform->addElement('note', 'score_block', array(
    	         'value' => $html_str,
    	         'decorators' => array(
    	             'ViewHelper',
    	             array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'bottom_score'))
    	         ),
    	     ));
    	     
    	     $subform->addElement('hidden', 'score_info', array(
    	         'value' =>$score_info,
    	         'label' =>null,
    	         'decorators' => array(
    	             'ViewHelper',
    	             array('Label', array('placement'=> 'IMPLICIT_APPEND')),
    	             array(array('row' => 'HtmlTag'), array('tag' => 'p', 'class'=>'question_free_text'))
    	         ),
    	         'class'=>'score_info'
    	     ));
    	     
    	    return $subform;
    		}
 
    		/**
    		 * @ Lore
    		 * ISPC-2455 RUBIN - BADL
    	     * @param unknown $options
    	     * @param string $elementsBelongTo
    	     * @return Zend_Form_SubForm
    	     */
    		private function _create_formular_content_badl($options = array(), $elementsBelongTo = null){
    		    
    		    $subform = new Zend_Form_SubForm();
    		    $subform->clearDecorators()
    		    ->setDecorators( array(
    		        'FormElements',
    		        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    		    ));
    		    

    		    
    		    $form_question[$elementsBelongTo] = array();
    		    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
    		    //     	    dd($form_question);
    		    //     	    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    		    //     	        $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
    		    //     	        $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    		    //     	    }
    		    
    		    

    		    
    		    
    		    $opt_labels = array(
    		        "0" => "nie",
    		        "1" => "1",
    		        "2" => "2",
    		        "3" => "3",
    		        "4" => "4",
    		        "5" => "5",
    		        "6" => "6",
    		        "7" => "7",
    		        "8" => "8",
    		        "9" => "9",
    		        "10" => "10",
    		        "11" => "immer",
    		        "12" => "entfällt",
    		        "13" => "weiß nicht",
    		    );
    		    
    		    // Hack
    		    if(empty($options) && !empty($_POST[$elementsBelongTo])){
    		        $options = $_POST[$elementsBelongTo]['form_content'];
    		        $options['form_type'] = $elementsBelongTo;
    		    }
    		    
    		    
    		    
    		    $html_str = "";
    		    $html_str .= '<table class="badl SimpleTable" cellpadding="0" cellspacing="0"> ';
    		    $html_str .= '<thead>';
    		    $html_str .= '<tr>';
    		    $html_str .= '<th style="text-align: left;">Hat die Person Schwierigkeiten ...</th>';
    		    foreach($opt_labels as $ov => $label){
    		        $html_str .= '<th class="q_opt_text_th"> '.$label.' </th>';
    		    }
    		    $html_str .= '</tr>';
    		    $html_str .= '</thead>';
    		    
    		    $html_str .= '<tbody>';
    		    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    		        if($qinfo['question_type'] == 'radio'){
    		            $html_str .= '<tr>';
    		            $html_str .= '<th class="qtext_td" style="width: 20%">'.$qinfo['question_text'].'</th>';
    		            $opt_ident = 1;
    		            
    		            
    		            for ($i=0;$i<14;$i++){
    		                $q_op_val = array();
    		                $q_op_val = array('question_id'=>'q_'.$i, 'option_label'=>$i, 'option_value' => $i);
    		                
    		                $checked ="";
    		                if( (( empty($_POST) && $options[$qinfo['question_id']]['opt_1']['value'] ==  $q_op_val['option_value'] ))  || $options[$qinfo['question_id']]['opt_1'] ==  $q_op_val['option_value'] ){
   		                        $checked ='checked="checked"';
    		                }
    		                $rows_total=0;
    		                $html_str .= '<td align="center" class="" style="width: 4%">';
    		                if (($i > '0' && $i < '11') ){
    		                      $html_str .= '<input type="radio"  data-score = "'.$i.'"   class="calculate_score" onchange="calcscore($(this),\'radio\')" id="'.$elementsBelongTo.'-form_content-'.$qinfo['question_id'].'-opt_1-'.$q_op_val['option_value'].'" name="'.$elementsBelongTo.'[form_content]['.$qinfo['question_id'].'][opt_1]" value="'.$i.'" '.$checked.'  />';
    		                }else if ($i == '12'){
    		                    $html_str .= '<input type="radio"   data-score = "0"  class="calculate_score" onchange="calcscore($(this),\'radio\')"  id="'.$elementsBelongTo.'-form_content-'.$qinfo['question_id'].'-opt_1-'.$q_op_val['option_value'].'" name="'.$elementsBelongTo.'[form_content]['.$qinfo['question_id'].'][opt_1]" value="12" '.$checked.'  />';
    		                } else if ($i == '13'){
    		                    $html_str .= '<input type="radio"   data-score = "0"  class="calculate_score" onchange="calcscore($(this),\'radio\')"  id="'.$elementsBelongTo.'-form_content-'.$qinfo['question_id'].'-opt_1-'.$q_op_val['option_value'].'" name="'.$elementsBelongTo.'[form_content]['.$qinfo['question_id'].'][opt_1]" value="13" '.$checked.'  />';
    		                } else {
    		                    $html_str .= '';
    		                }
    		                $html_str .= '</td>';
    		            
    		            } 
    		           // var_dump($q_op_val);exit();
    		            $html_str .= '</tr>';
    		        }
    		    }
    		    $html_str .= '</tbody>';
    		    
    		    $html_str .= '</table>';
    		    
    		 
    		    
    		    $subform->addElement('note', 'sblock', array(
    		        'value' => $html_str,
    		        'decorators' => array(
    		            'ViewHelper',
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'badl_form_block'))
    		        ),
    		    ));
    		    
    		    foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    		        if($qinfo['question_type'] == 'textarea'){
    		            
    		            $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
    		            $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    		        }
    		    }
    		    
    		    
    		    
    		    $subform->addElement('text', 'form_total', array(
    		        'label' => 'Gesamtpunktzahl:',
    		        'value' => $options['form_total'],
    		        'readonly'=> true,
    		        'class'=> 'form_total',
    		        'decorators' => array(
    		            'ViewHelper',
    		            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
    		            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
    		        ),
    		    ));
    		    
    		    
    		    
    		    return $subform;
    		    
    		    }
    		    
    		    /*
    		     * @auth Lore 16.09.2019
    		     * ISPC-2456
    		     */
    		    private function _create_formular_content_cmscale($options = array(), $elementsBelongTo = null){
    		        
    		        $subform = new Zend_Form_SubForm();
    		        $subform->clearDecorators()
    		        ->setDecorators( array(
    		            'FormElements',
    		            array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    		        ));
    		        
    		        $form_question[$elementsBelongTo] = array();
    		        $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
    		        
    		        
    		        $subform->addElement('note', 'generic_question_text', array(
    		            'value' => 'Bitte kreuzen sie die Art und die Häufigkeit des herausfordernden Verhaltens an, wie es in der letzten Woche beobachtet wurde',
    		            'decorators' => array(
    		                'ViewHelper',
    		                array(array('ltag' => 'HtmlTag'), array('tag' => 'h5', 'class'=>'fulldiv left'))
    		            ),
    		        ));
    		        
    		        $opt_labels = array(
    		            "1"=>"nie",
    		            "2"=>"1 x pro Woche",
    		            "3"=>"mehrmals wöchentlich",
    		            "4"=>"1-2 x pro Tag",
    		            "5"=>"mehrmals täglich",
    		            "6"=>"mehrmals stündlich"
    		        );
    		        
    		        
    		        if(empty($options) && !empty($_POST[$elementsBelongTo])){
    		            $options = $_POST[$elementsBelongTo]['form_content'];
    		            $options['form_type'] = $elementsBelongTo;
    		        }
    		        
    		        $html_str = "";
    		        $html_str .= '<table class="cmscale SimpleTable" cellpadding="0" cellspacing="0"> ';
    		        $html_str .= '<thead>';
    		        $html_str .= '<tr>';
    		        $html_str .= '<th alight="left" class="qtext_th"></th>';
    		        foreach($opt_labels as $ov => $label){
    		            $html_str .= '<th class="q_opt_text_th">'.$label.'</th>';
    		        }
    		        $html_str .= '</tr>';
    		        $html_str .= '</thead>';
    		        $html_str .= '<tbody>';
    		        foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    		            if($qinfo['question_type'] == 'radio'){
    		                $html_str .= '<tr>';
    		                $html_str .= '<th class="qtext_td">'.$qinfo['question_text'].'</th>';
    		                $opt_ident = 1;
    		                foreach($qinfo['PatientRubinQuestionsOptions'] as $q_op=>$q_op_val){
    		                    $checked ="";
    		                    if($options[$qinfo['question_id']]['opt_1']['value'] ==  $q_op_val['option_value']
    		                        || $options[$qinfo['question_id']]['opt_1'] ==  $q_op_val['option_value']
    		                        ){
    		                            
    		                            $checked ='checked="checked"';
    		                    }
    		                    
    		                    //'value' =>  $options[$question_info['question_id']]['opt_1']['checked'] == 'yes' ? $options[$question_info['question_id']]['opt_1']['value'] : 0,
    		                    $html_str .= '<td align="center" class="q_opt_text_td">';
    		                    $html_str .= '<input type="radio" class="calculate_score" onchange="calcscore($(this),\'radio\')" id="cmscale-form_content-'.$qinfo['question_id'].'-opt_1-'.$q_op_val['option_value'].'"              name="cmscale[form_content]['.$qinfo['question_id'].'][opt_1]" value="'.$q_op_val['option_value'].'" '.$checked.'  data-score = "'.$q_op_val['option_score_value'].'" />';
    		                    $html_str .= '</td>';
    		                }
    		                $html_str .= '</tr>';
    		            }
    		        }
    		        $html_str .= '</tbody>';
    		        $html_str .= '</table>';
    		        
    		        $subform->addElement('note', 'sblock', array(
    		            'value' => $html_str,
    		            'decorators' => array(
    		                'ViewHelper',
    		                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'cmscale_form_block'))
    		            ),
    		        ));
    		        
    		        foreach($form_question[$elementsBelongTo] as $kq=>$qinfo){
    		            if($qinfo['question_type'] == 'textarea'  ){
    		                
    		                $question = $this->_create_question($options, $qinfo, $qinfo['question_id']);
    		                $subform->addSubform($question, 'formular_questions'.$qinfo['id']);
    		            }
    		        }
    		        
    		        
    		        
    		        $subform->addElement('text', 'form_total', array(
    		            'label' => 'Summe:',
    		            'value' => $options['form_total'],
    		            'readonly'=> true,
    		            'class'=> 'form_total',
    		            'decorators' => array(
    		                'ViewHelper',
    		                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
    		                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'form_total_block'))
    		            ),
    		        ));
    		        
    		        
    		        
    		        
    		        /* 	    $score_description = '<h5>Interpretation des Testergebnisses</h5>';
    		         $score_description .= '<table class="score_desc" cellpadding="0" cellspacing="0">';
    		         $score_description .= '<tr>';
    		         $score_description .= '<th>Punkte</th>';
    		         $score_description .= '<th>Beurteilung</th>';
    		         $score_description .= '</tr>';
    		         
    		         $score_description .= '<tr>';
    		         $score_description .= '<td>0-8</td>';
    		         $score_description .= '<td>Keine Depression</td>';
    		         $score_description .= '</tr>';
    		         
    		         $score_description .= '<tr>';
    		         $score_description .= '<td>9-13</td>';
    		         $score_description .= '<td>Minimale depressive Symptomatik</td>';
    		         $score_description .= '</tr>';
    		         
    		         $score_description .= '<tr>';
    		         $score_description .= '<td>14-19</td>';
    		         $score_description .= '<td>Leichte depressive Symptomatik</td>';
    		         $score_description .= '</tr>';
    		         
    		         $score_description .= '<tr>';
    		         $score_description .= '<td>20-28</td>';
    		         $score_description .= '<td>Mittelschwere depressive Symptomatik</td>';
    		         $score_description .= '</tr>';
    		         
    		         $score_description .= '<tr>';
    		         $score_description .= '<td>29-63</td>';
    		         $score_description .= '<td>Schwere depressive Symptomatik</td>';
    		         $score_description .= '</tr>';
    		         $score_description .= '</table>';
    		         
    		         $subform->addElement('note', 'score_description', array(
    		         'value' => $score_description,
    		         'decorators' => array(
    		         'ViewHelper',
    		         array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'score_description_block'))
    		         ),
    		         )); */
    		        
    		        return $subform;
    		        
    		        }
    		        
    		        /**
    		         * ISPC-2492 Lore 02.12.2019
    		         * @param array $options
    		         * @param unknown $elementsBelongTo
    		         * @return Zend_Form_SubForm
    		         */
    		        private function _create_formular_content_carerelated($options = array(), $elementsBelongTo = null){
    		            
    		            $subform = new Zend_Form_SubForm();
    		            $subform->clearDecorators()
    		            ->setDecorators( array(
    		                'FormElements',
    		                array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    		            ));

    		            
    		            if ( ! is_null($elementsBelongTo)) {
    		                $subform->setOptions(array(
    		                    'elementsBelongTo' => $elementsBelongTo
    		                ));
    		            }
    		            
    		            $form_question[$elementsBelongTo] = array();
    		            $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
    		            
    		          
    		            if(empty($options) && !empty($_POST[$elementsBelongTo])){
    		                $options = $_POST[$elementsBelongTo]['form_content'];
    		                $options['form_type'] = $elementsBelongTo;
    		            }
    		            
    		            
    		            foreach($form_question[$elementsBelongTo] as $kid =>$qinfo){
    		                	   
    		                $subformq = new Zend_Form_SubForm();
    		                $subformq->clearDecorators()
    		                ->setDecorators( array(
    		                    'FormElements',
    		                    array('HtmlTag',array('tag'=>'div', 'class' => ' ')),
    		                ));
    		                
    		                //LABEL
    		                $subformq->addElement('note', $qinfo['question_id'].'_label', array(
    		                    'value' => $qinfo['question_text'],
    		                    'decorators' => array(
    		                        'ViewHelper',
    		                        array(array('ltag' => 'HtmlTag'), array('tag' => 'h3', 'class'=>'question_text paddingLeft', 'style'=>'line-height : 26px '))
    		                    ),
    		                ));
    		                foreach($qinfo['PatientRubinQuestionsOptions'] as $optid =>$optvals){
    		                    $opt_labels[$optvals['question_id']][] = $optvals['option_label'];
    		                }

    		                    
    		                $html_str = "";
    		                $html_str .= '<table class="carerelated SimpleTable" cellpadding="0" cellspacing="0" border="1px solid"> ';
    		                $html_str .= '<thead>';
    		                $html_str .= '<tr>';
    		                
		                    foreach($opt_labels[ $qinfo['question_id']] as $ovid => $labelval){
		                        $html_str .= '<th>'.$labelval.'</th>';
		                    }
    		                
    		                $html_str .= '</tr>';
    		                $html_str .= '</thead>';
    		                $html_str .= '<tbody>';
    		                
    		                    
		                    if($qinfo['question_type'] == 'checkbox' ){
		                        $html_str .= '<tr>';
		                        
		                        foreach($qinfo['PatientRubinQuestionsOptions'] as $q_op=>$q_op_val){
		                            $checked ="";
		                            if($options[$qinfo['question_id']][ 'opt_'.$q_op_val['option_value'] ]['value'] ==  $q_op_val['option_value']
		                                || $options[$qinfo['question_id']]['opt_'.$q_op_val['option_value'] ] ==  $q_op_val['option_value'] )
		                            {
		                                    $checked ='checked="checked"';
		                            }

		                            $html_str .= '<td align="center" class="care_related_'.$qinfo['question_id'].'"  >';

		                            if ($qinfo['question_id'] != 'q_5'){
/* 		                                if($q_op_val['option_value'] == '0'  && $qinfo['question_id']=='q_1' ){
		                                    $html_str .= '<label for="carerelated-form_content-'.$qinfo['question_id'].'-opt_'.$q_op_val['option_value'].'-'.$q_op_val['option_value'].'"><font color="red">*</font>'.$q_op_val['option_value'].'</label>';
		                                } else {
		                                    $html_str .= '<label for="carerelated-form_content-'.$qinfo['question_id'].'-opt_'.$q_op_val['option_value'].'-'.$q_op_val['option_value'].'">'.$q_op_val['option_value'].'</label>';
		                                } */
		                                $html_str .= '<label for="carerelated-form_content-'.$qinfo['question_id'].'-opt_'.$q_op_val['option_value'].'-'.$q_op_val['option_value'].'">'.$q_op_val['option_value'].'&nbsp;</label>';
		                                
		                            }

		                            $html_str .= '<input type="checkbox" class="calculate_score" onchange="calcscore($(this),\'checkbox\')" id="carerelated-form_content-'.$qinfo['question_id'].'-opt_'.$q_op_val['option_value'].'-'.$q_op_val['option_value'].'"    name="carerelated[form_content]['.$qinfo['question_id'].'][opt_'.$q_op_val['option_value'].']" value="'.$q_op_val['option_value'].'" '.$checked.'  />';
		                            $html_str .= '</td>';
		                        }
		                        $html_str .= '</tr>';
		                    }
		                
    		                
		                $html_str .= '</tbody>';
		                $html_str .= '</table>';
		            
		                $subformq->addElement('note', $qinfo['question_id'].'sblock', array(
		                    'value' => $html_str,
		                    'decorators' => array(
		                        'ViewHelper',
		                        array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'carerelated_form_block'))
		                    ),
		                ));
		                
/* 		                if($qinfo['question_id'] == 'q_1'){
		                    $subformq->addElement('note', 'q_1_subtext', array(
		                        'value' => '<font color="red"><i>* Die Kästchen müssen im Formular angeklickt werden können.</i></font>',
		                        'decorators' => array(
		                            'ViewHelper',
		                            array(array('ltag' => 'HtmlTag'), array('tag' => 'p','class'=>'question_text paddingLeft'))
		                        ),
		                    ));
		                } */
   		                
		                $subformq->addElement('note', $qinfo['question_id'].'space_q2ab', array(
		                    'value'=>'<br/>',
		                    'decorators' => array(
		                        'ViewHelper',
		                        array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
		                    ),
		                ));
    		                
    		                
		                $subform->addSubform($subformq, $qinfo['question_id']);
    		        }
   		                
    		                
    		         return $subform;
    	}
    	

    	/**
    	 * ISPC-2493 Lore 03.12.2019
    	 * @param array $options
    	 * @param unknown $elementsBelongTo
    	 * @return Zend_Form_SubForm
    	 */
    	private function _create_formular_content_carepatient($options = array(), $elementsBelongTo = null){
    	    
    	    $subform = new Zend_Form_SubForm();
    	    $subform->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	    ));
    	    
    	    
    	    if ( ! is_null($elementsBelongTo)) {
    	        $subform->setOptions(array(
    	            'elementsBelongTo' => $elementsBelongTo
    	        ));
    	    }
    	    
    	    $form_question[$elementsBelongTo] = array();
    	    $form_question[$elementsBelongTo] = PatientRubinQuestionsTable::find_questions($elementsBelongTo);
    	    
    	    
    	    if(empty($options) && !empty($_POST[$elementsBelongTo])){
    	        $options = $_POST[$elementsBelongTo]['form_content'];
    	        $options['form_type'] = $elementsBelongTo;
    	    }
    	    
    	    
    	    foreach($form_question[$elementsBelongTo] as $kid =>$qinfo){
    	        
    	        $subformq = new Zend_Form_SubForm();
    	        $subformq->clearDecorators()
    	        ->setDecorators( array(
    	            'FormElements',
    	            array('HtmlTag',array('tag'=>'div', 'class' => ' ')),
    	        ));
    	        
    	        //LABEL
    	        $subformq->addElement('note', $qinfo['question_id'].'_label', array(
    	            'value' => $qinfo['question_text'],
    	            'decorators' => array(
    	                'ViewHelper',
    	                array(array('ltag' => 'HtmlTag'), array('tag' => 'h3', 'class'=>'question_text paddingLeft', 'style'=>'line-height : 26px '))
    	            ),
    	        ));
    	        foreach($qinfo['PatientRubinQuestionsOptions'] as $optid =>$optvals){
    	            $opt_labels[$optvals['question_id']][] = $optvals['option_label'];
    	        }
    	        
    	        
    	        $html_str = "";
    	        $html_str .= '<table class="carepatient SimpleTable" cellpadding="0" cellspacing="0" border="1px solid"> ';
    	        $html_str .= '<thead>';
    	        $html_str .= '<tr>';
    	        
    	        foreach($opt_labels[ $qinfo['question_id']] as $ovid => $labelval){
    	            $html_str .= '<th >'.$labelval.'</th>';
    	        }
    	        
    	        $html_str .= '</tr>';
    	        $html_str .= '</thead>';
    	        $html_str .= '<tbody>';
    	        
    	        
    	        if($qinfo['question_type'] == 'checkbox' ){
    	            $html_str .= '<tr>';
    	            
    	            foreach($qinfo['PatientRubinQuestionsOptions'] as $q_op=>$q_op_val){
    	                $checked ="";
    	                if($options[$qinfo['question_id']][ 'opt_'.$q_op_val['option_value'] ]['value'] ==  $q_op_val['option_value']
    	                    || $options[$qinfo['question_id']]['opt_'.$q_op_val['option_value'] ] ==  $q_op_val['option_value'] )
    	                {
    	                    $checked ='checked="checked"';
    	                }
    	                $html_str .= '<td align="center" class="care_patient_'.$qinfo['question_id'].'" >';
    	                
    	                if ($qinfo['question_id'] != 'q_5'){
/*         	                if($q_op_val['option_value'] == '1'  && $qinfo['question_id']=='q_1' ){
        	                    $html_str .= '<label for="carepatient-form_content-'.$qinfo['question_id'].'-opt_'.$q_op_val['option_value'].'-'.$q_op_val['option_value'].'"><font color="red">*</font>'.$q_op_val['option_value'].'&nbsp;</label>';
        	                } else {
        	                    $html_str .= '<label for="carepatient-form_content-'.$qinfo['question_id'].'-opt_'.$q_op_val['option_value'].'-'.$q_op_val['option_value'].'">'.$q_op_val['option_value'].'&nbsp;</label>';
        	                } */
        	                $html_str .= '<label for="carepatient-form_content-'.$qinfo['question_id'].'-opt_'.$q_op_val['option_value'].'-'.$q_op_val['option_value'].'">'.$q_op_val['option_value'].'&nbsp;</label>';
        	                
    	                }
    	                
    	                $html_str .= '<input type="checkbox" class="calculate_score" onchange="calcscore($(this),\'checkbox\')" id="carepatient-form_content-'.$qinfo['question_id'].'-opt_'.$q_op_val['option_value'].'-'.$q_op_val['option_value'].'"    name="carepatient[form_content]['.$qinfo['question_id'].'][opt_'.$q_op_val['option_value'].']" value="'.$q_op_val['option_value'].'" '.$checked.'  />';
    	                $html_str .= '</td>';
    	            }
    	            $html_str .= '</tr>';
    	        }
    	        
    	        
    	        $html_str .= '</tbody>';
    	        $html_str .= '</table>';
    	        
    	        $subformq->addElement('note', $qinfo['question_id'].'sblock', array(
    	            'value' => $html_str,
    	            'decorators' => array(
    	                'ViewHelper',
    	                array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'carepatient_form_block'))
    	            ),
    	        ));
    	        
/*     	        if($qinfo['question_id'] == 'q_1'){
    	            $subformq->addElement('note', 'q_1_subtext', array(
    	                'value' => '<font color="red"><i>* Kästchen müssen im gesamten Formular angeklickt werden können.</i></font>',
    	                'decorators' => array(
    	                    'ViewHelper',
    	                    array(array('ltag' => 'HtmlTag'), array('tag' => 'p','class'=>'question_text paddingLeft'))
    	                ),
    	            ));
    	        } */
    	        
    	        $subformq->addElement('note', $qinfo['question_id'].'space_q2ab', array(
    	            'value'=>'<br/>',
    	            'decorators' => array(
    	                'ViewHelper',
    	                array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
    	            ),
    	        ));
    	        
    	        
    	        $subform->addSubform($subformq, $qinfo['question_id']);
    	    }
    	    
    	    
    	    return $subform;
    	}
    	
/**
 * @Lore 06.01.2020
 * ISPC-2509 
 * @param array $options
 * @param unknown $elementsBelongTo
 * @return Zend_Form_SubForm
 */
    	private function _create_formular_content_dscdsv($options = array(), $elementsBelongTo = null){
    	    
    	    $problemlagen= array(
    	        '0'=>"keine",
    	        '1'=>"mäßig",
    	        '2'=>"erheblich",
    	        '3'=>"(sehr) hoch",
    	        '9'=>"unbekannt"
    	    );
  
    	    $ressourcen= array(
    	        '0'=>"keine",
    	        '1'=>"knapp",
    	        '2'=>"ausreichend",
    	        '3'=>"(sehr) gut",
    	        '9'=>"unbekannt"
    	    );
    	    
    	    $interventionsbedarf= array(
    	        '0'=>"keiner",
    	        '1'=>"mäßig",
    	        '2'=>"erheblich",
    	        '3'=>"(sehr) hoch",
    	        '9'=>"unbekannt"
    	    );
    	    
    	    $text_explication = '<b>Aktuelle(r) Problemlagen/Ressourcen/Interventionsbedarf im Klientensystem</b><br>
Die Angaben beziehen sich auf den Zeitpunkt des Beginns der Patientenbegleitung! Bitte treffen Sie unbedingt zu
jedem Bereich eine Einschätzung – auch wenn in einem Bereich keine Probleme oder Ressourcen vorliegen oder erkennbar sind bzw. kein Interventionsbedarf besteht. Für diesen Fall ist an der entsprechenden Stelle bereits der Wert „0 = keine(r)“ voreingestellt, so dass Sie hier keine Änderung vornehmen müssen. Im Falle erkannter Problemlagen, Ressourcen oder Interventionsbedarfe klicken Sie bitte auf die jeweilige Dropdownliste und wählen den entsprechenden Wert aus.<br>
Bitte nutzen Sie dabei die Auswahlmöglichkeit „9 = unbekannt“ so selten wie möglich! Grundsätzlich gilt, dass die <b>Angabe einer Kategorie der Nennung „unbekannt“ vorzuziehen</b> ist, da Codierungen mit „unbekannt“ in jedem Fall mit einem Informationsverlust verbunden sind.<br>
<br>Benutzen Sie zur Einschätzung bitte die folgenden Skalen:<br><br>
<b>Problemlagen</b><br>
Die <b>Einschätzung von Problemlagen bezieht sich im Bereich „PERSON“ (Kategorien 1-12) ausschließlich auf den Patienten selbst.</b> Ggf. vorhandene Ressourcen in den zu beurteilenden Teilbereichen sind bei der Beurteilung nicht zu berücksichtigen!
<br>0 = <b>keine:</b> Es liegt kein Problem vor bzw. vorhandene Schwierigkeiten werden von Patientenbegleiter und Klient/Klientensystem gleichermaßen als nicht problematisch bzw. belastend eingeschätzt.
<br>1 = <b>mäßig:</b> Das Problem führt zu einzelnen Funktionseinschränkungen des Klientensystems, führt aber nicht zur generellen Funktionseinschränkung.
<br>2 = <b>erheblich:</b> Das Problem ist ausgeprägt und schwer veränderbar. Das Klientensystem bzw. Teile des Klientensystems leiden darunter oder Dritte haben wiederholt auf das Problem hingewiesen (Auffälligkeit, Meldung, Befunde).
<br>3 = <b>(sehr) hoch:</b> Das Problem wird für den Bereich durchgängig als problematisch angesehen und beeinträchtigt die Funktionsweise in hohem Maße.
<br>9 = <b>unbekannt:</b> Eine Problemlage ist in dem betreffenden Bereich erkennbar, aber der Schweregrad ist zum Stichtag der aktuellen Erhebung (z. B. aufgrund mangelnder Informationslage) nicht hinreichend einschätzbar.
<br><br><b>Ressourcen</b>
<br>Ressourcen sind zum einen vorhandene persönliche Fähigkeiten bzw. Kompetenzen des Patienten, auf die er selbst oder helfende Personen in seinem Umfeld zurückgreifen können. Zum anderen können aber auch Personen, (instrumentelle) Unterstützungsquellen oder Hilfen im Umfeld des Patienten (auch auf professioneller Ebene [Ärzte, Therapeuten, Pflegedienst etc.]) Ressourcen darstellen. Die <b>Ressourceneinschätzungen in den Kategorien 1-12 (Bereich „PERSON“) sind also – anders als bei den Problemlageneinschätzungen – nur mittelbar auf den Patienten selbst zu beziehen!</b>
<br>0 = <b>keine:</b> Eigene Stärken/Fähigkeiten oder fremde (im Helfersystem vorhandene) Unterstützungsmöglichkeiten sind für die Problemlösung nicht erkennbar oder können nicht genutzt werden.
<br>1 = <b>knapp:</b> Eigene Stärken/Fähigkeiten oder fremde (im Helfersystem vorhandene) Unterstützungsmöglichkeiten sind für Teilbereiche vorhanden, reichen aber zur Problemlösung nicht aus oder müssen gezielt (planvoll) erschlossen werden.
<br>2 = <b>ausreichend:</b> Eigene Stärken/Fähigkeiten oder fremde im Helfersystem vorhandene) Unterstützungsmöglichkeiten sind vorhanden. Die Erschließung gelingt mit hoher Wahrscheinlichkeit mit der Unterstützung der Patientenbegleitung.
<br>3 = <b>(sehr) gut:</b> Eigene Stärken/Fähigkeiten oder fremde (im Helfersystem vorhandene) Unterstützungsmöglichkeiten sind rasch identifizierbar und können schnell genutzt werden. Das Klientensystem verfügt über (eigene) Fähigkeiten, diese heranzuziehen.
<br>9 = <b>unbekannt:</b> Es liegen Ressourcen in dem betreffenden Bereich vor, diese sind aber zum Stichtag der aktuellen Erhebung (z. B. aufgrund mangelnder Informationslage) nicht hinreichend einschätzbar.
<br><br><b>Interventionsbedarf</b>
<br>Die <b>Einschätzung des Interventionsbedarfs orientiert sich nicht allein an der Aufgabe bzw. dem Zuständigkeitsbereich des Patientenbegleiters!</b> Er kennzeichnet vielmehr den <b>Umfang, in dem der Patient und/oder sein persönliches Umfeld</b> (Betreuungsperson, Familienangehörige etc.) <b>Unterstützung durch Personen, Institutionen o. Ä. außerhalb des Klientensystems benötigen.</b> Der Interventionsbedarf ergibt sich in jedem Untersuchungsbereich aus der gemeinsamen Betrachtung vorliegender Problemlagen und Ressourcen.
Aber: <b>Der Interventionsbedarf errechnet sich <u>nicht</u> mathematisch aus der Differenz zwischen Schweregrad der Problemlagen und Höhe der Ressourcen</b>, da z. B. das Vorliegen von Ressourcen nicht automatisch auch deren selbstständige Nutzung durch den Patienten bzw. das Klientensystem bedeutet. Auch bei einer relativ gut beurteilten Ressourcenlage kann deshalb ein Interventionsbedarf bestehen, um vorhandene Ressourcen zu aktivieren und nutzbar zu machen. Darüber hinaus besteht die Möglichkeit, dass vorhandene Problemlagen in einem Teilbereich auch bei einer (sehr) guten Ressourcenlage des Klienten-/Helfersystems nicht vollständig bzw. nicht nachhaltig bewältigt werden können und dadurch eine zusätzliche Unterstützung von außen notwendig ist. Ein solcher Interventionsbedarf müsste dann an der entsprechenden Stelle benannt werden.
<br>0 = <b>keiner:</b> Es liegen keine interventionsbedürftigen Problemlagen vor oder bestehende Probleme werden vom Klientensystem unter Nutzung vorhandener Ressourcen selbstständig bewältigt. Eine Unterstützung von außen ist nicht notwendig.
<br>1 = <b>mäßig:</b> Das Klientensystem kann die Situation weitgehend selbstständig bewältigen. Eine Unterstützung von außen (z. B. in Form von Anregungen zur Ressourcennutzung) ist aber teilweise sinnvoll bzw. erforderlich.
<br>2 = <b>erheblich:</b> Das Klientensystem kann vorhandene Probleme kaum selbstständig bewältigen. Eine gezielte Intervention von außen, ggf. unter Nutzung bzw. Aktivierung vorhandener Ressourcen, ist notwendig.
<br>3 = <b>(sehr) hoch:</b> Das Klientensystem ist nicht in der Lage, vorhandene Probleme selbstständig zu bewältigen. Eine gezielte Intervention von außen, ggf. unter Nutzung bzw. Aktivierung vorhandener Ressourcen, ist dringend notwendig. Ansonsten besteht ein hohes Risiko zur Verschlimmerung bzw. Eskalation der Situation.
<br>9 = <b>unbekannt:</b> Ein Interventionsbedarf ist erkennbar. Das Ausmaß des Bedarfs kann allerdings zum Stichtag der aktuellen Erhebung (z. B. aufgrund mangelnder Informationen) nicht hinreichend eingeschätzt werden.<br/>';

    	    
    	    $subform = new Zend_Form_SubForm();
    	    $subform->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	    ));
    	    
    	    
    	    if ( ! is_null($elementsBelongTo)) {
    	        $subform->setOptions(array(
    	            'elementsBelongTo' => $elementsBelongTo
    	        ));
    	    }
    	    
    	    
    	    $question = new Zend_Form_SubForm();
    	    $question->clearDecorators()
    	    ->setDecorators( array(
    	        'FormElements',
    	        array('HtmlTag',array('tag'=>'div', 'class' => 'form_content')),
    	    ));
    	    

    	    $subform->addElement('note', 'Page_title', array(
    	        'value' => "DemStepCare Assessment zur Versorgungssituation (in Anlehnung an DSV und CM4Demenz)",
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h2', 'class'=>'page-title'))
    	        ),
    	    ));
 
    	    
    	    $subform->addElement('note', 'question0', array(
    	        'value' => " ",
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h7', 'class'=>'toggle_text' ))
    	        ),
    	    ));
    	    
    	    $subform->addElement('note', 'sblock', array(
    	        'value' => $text_explication,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'div', 'class'=>'content dontPrint'))
    	        ),
    	    ));

    	    
    	    $subform->addElement('note', 'question1', array(
    	        'value' => "<br/> Aktuelle(r) Problemlagen/Ressourcen/Interventionsbedarf im Klientensystem. (Bitte treffen Sie zu jedem Bereich eine Einschätzung anhand der jeweiligen Skalen.",
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h7', 'class'=>'question_text'))
    	        ),
    	    ));
    	    
    	    $subform->addElement('note', 'question2', array(
    	        'value' => "<br/> *Bei der Einschätzung vorhandener Ressourcen sollen in allen Untersuchungsbereichen auch die Kompetenzen, Fähigkeiten und/oder Möglichkeiten des Umfelds bzw. Helfersystems mit einbezogen werden. Die Ressourceneinschätzungen in den Kategorien 1-12 sind also – anders als bei den Problemlageneinschätzungen – nur mittelbar auf die Person zu beziehen!)",
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'h7', 'class'=>'question_text'))
    	        ),
    	    ));
    	    
    	    $subform->addElement('note', 'space_q', array(
    	        'value'=>'<br><br/>',
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('ltag' => 'HtmlTag'), array('tag' => 'span', 'class'=>'question_free_space'))
    	        ),
    	    ));
    	    
    	    $table_question_info = array();
    	    $table_question_info = array(
    	        'qt_1' => array(
    	            'question_id'=>'qt_1',
    	            'nr'=>'1',
    	            'category'=>'<u><b>Informelles Hilfesystem</b></u> ',
    	            'description'=>'<i>Haben Sie in Ihrem Umfeld Menschen, die Ihnen bei Bedarf bei der Pflege Ihres Angehörigen helfen? (familiäre Bindungen/Partnerschaft, familiäre Unterstützung)</i>',
    	            'info_row'=>'(z.B. "Nein, von meinen Verwandten/Bekannten ist keiner in der Lage/bereit, mich zu unterstützen", "Nein, ich habe keine Verwandte/Bekannte, die in der Nähe leben"; "Ja, ich habe Freunde, die mich regelmäßig besuchen kommen"; "Ja, die Nachbarin hilft bei..."; "Frau X von der Selbsthilfegruppe .....")'
    	        ),
    	        'qt_1b' => array(
    	            'question_id'=>'qt_1b',
    	            'nr'=>'1b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
        	        
    	        'qt_2' => array(
    	            'question_id'=>'qt_2',
    	            'nr'=>'2',
    	            'category'=>'<u><b>Mobilität/ Körperliche Gesundheit</b></u>',
    	            'description'=>'<i>Haben Sie in den letzten 4 Wochen Veränderung in Bezug auf die Mobilität des/der Betroffenen festgestellt? (Können Sie das genauer beschreiben?) (Bewegungsapparat, Herz-Kreislaufsystem, Atmungssystem, Sinnesorgane etc.)</i>',
    	            'info_row'=>'(z.B. einmal oder mehrfach gestürzt, viele blaue Flecke sind vorhanden, steht alleine auf, ohne die Balance halten zu können/kann Balance halten, habe/keine Kreislaufprobleme)'
    	        ),
    	        'qt_2b' => array(
    	            'question_id'=>'qt_2b',
    	            'nr'=>'2b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	        
    	        'qt_3' => array(
    	            'question_id'=>'qt_3',
    	            'nr'=>'3',
    	            'category'=>'<u><b>Kontinenz</b></u>',
    	            'description'=>'<i>Haben Sie in den letzten 4 Wochen Veränderungen im Zusammenhang mit der Kontrolle über Körperausscheidungen beobachtet? (Können Sie das genauer beschreiben?)</i>',
    	            'info_row'=>'(z.B. der Urin/Stuhlgang kann/nicht mehr gehalten werden, der/die Betroffene muss mehrmals am Tag aufgrund der Inkontinenz seine Kleidung wechseln oder hat wieder mehr Kontrolle über Körperausscheidungen)'
    	        ),
    	        'qt_3b' => array(
    	            'question_id'=>'qt_3b',
    	            'nr'=>'3b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	        
    	        'qt_4' => array(
    	            'question_id'=>'qt_4',
    	            'nr'=>'4',
    	            'category'=>'<u><b>Ernährung, Gesundheits-verhalten</b></u>',
    	            'description'=>'<i>Haben Sie im Zusammenhang mit der Nahrungsaufnahme oder Genussmittegelgebrauch in den letzten 4 Wochen Beeinträchtigungen bemerkt?</i>',
    	            'info_row'=>'(z.B. der/die Betroffene trinkt weniger als 1300 ml pro Tag, es sind Schluckstörungen aufgetreten, der/die Betroffene weiß mit der Nahrung nichts anzufangen, wird bei Nahrungsaufnahme unterstützt)'
    	        ),
    	        'qt_4b' => array(
    	            'question_id'=>'qt_4b',
    	            'nr'=>'4b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	        
    	        'qt_5a1' => array(
    	            'question_id'=>'qt_5a1',
    	            'nr'=>'5',
    	            'category'=>'<u><b>(Ungewohnte) Verhaltens-weisen</b></u>',
    	            'description'=>'<i>Haben Sie in den letzten 4 Wochen ungewohnte Verhaltensweisen beobachtet? (Können Sie das genauer beschreiben?)</i>',
    	            'info_row'=>'(z.B. der/die Betroffene vergisst den Herd auszumachen, der/die Betroffene läuft ohne erkennbaren Grund und/oder adäquate Kleidung auf die Straße, der/die Betroffene wird plötzlich aggressiv, der/die Betroffene schreit laut ohne erkennbaren Grund oder verhält sich angemessen)'
    	        ),
    	        'qt_5a2' => array(
    	            'question_id'=>'qt_5a2',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Tag- und Nacht-Rhythmus',
    	            'info_row'=>'(Ein-/Durchschlafen, Wachheit/Aktivität am Tag etc.)'
    	        ),
    	        'qt_5a3' => array(
    	            'question_id'=>'qt_5a3',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Affektivität/Gefühl/Emotion',
    	            'info_row'=>'(z.B situationsangemessene Gefühlsausbrüche/Mimik/Gestik, Fähigkeit, Gefühle auszudrücken etc.)'
    	        ),
    	        'qt_5a4' => array(
    	            'question_id'=>'qt_5a4',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Sozialverhalten',
    	            'info_row'=>'(z. B. soziale Beziehungen, sozial-kommunikative Kompetenzen, Empathiefähigkeit etc.)'
    	        ),
    	        'qt_5b' => array(
    	            'question_id'=>'qt_5b',
    	            'nr'=>'5b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	        
    	        'qt_6a1' => array(
    	            'question_id'=>'qt_6a1',
    	            'nr'=>'6',
    	            'category'=>'<u><b>Hilfesystem</b></u>',
    	            'description'=>'<i>Haben sich in den letzten 4 Wochen Veränderungen mit dem arrangierten Pflegesystem ergeben? (Können Sie das genauer beschreiben?)</i>',
    	            'info_row'=>'(z.B. Nachbarn/Freunde/weitere Angehörige, die bei der Versorgung unterstützen sind ausgefallen/hinzugekommen, die privat finanzierte 24h Hilfe steht nicht mehr zur Verfügung, der Pflegedienst kann seine zugesagten Leistungen nicht mehr erbringen/bietet neue Leistungen an)'
    	        ),
    	        'qt_6a2' => array(
    	            'question_id'=>'qt_6a2',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Zugänglichkeit von Dienstleistungsangeboten',
    	            'info_row'=>'(z.B. medizinisch-therapeutische Angebote in örtlicher Nähe etc.)'
    	        ),
    	        'qt_6a3' => array(
    	            'question_id'=>'qt_6a3',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Nutzung von Dienstleistungsangeboten',
    	            'info_row'=>'(z.B. Auswahl und Nutzung geeigneter Hilfsangebote etc.) '
    	        ),
    	        'qt_6a4' => array(
    	            'question_id'=>'qt_6a4',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Infrastruktur',
    	            'info_row'=>'(z.B. ÖNV, Anzahl Einrichtungen/Dienstleister, Einkaufsmöglichkeiten)'
    	        ),
    	        'qt_6b' => array(
    	            'question_id'=>'qt_6b',
    	            'nr'=>'6b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	        
    	        'qt_7a1' => array(
    	            'question_id'=>'qt_7a1',
    	            'nr'=>'7',
    	            'category'=>'<u><b>Persönliche Situation der Hauptpflege-person</b></u>',
    	            'description'=>'<i>Welche Auswirkungen hatte die Pflege der/des Betroffenen auf ihr Leben und ihre eigene gesundheitliche Situation in den letzten 4 Wochen? (Können Sie das genauer beschreiben?)</i>',
    	            'info_row'=>'(z.B. ich schlafe schlecht/besser, ich fühle mich überlastet und weiß nicht wie lange ich die Pflegesituation noch tragen kann, manchmal muss ich mich sehr zurücknehmen, damit ich selbst nicht aggressiv werde, ich befinde mich auf Grund der belastenden Situation in ärztlicher Behandlung, ich komme zunehmend besser mit der Situation zurecht)'
    	        ),
    	        'qt_7a2' => array(
    	            'question_id'=>'qt_7a2',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Umgang von Familie/Umfeld mit Belastungen durch die Pflegesituation',
    	            'info_row'=>'(z.B. Copingstrategien, Frustrationstoleranz, Kommunikation etc.)'
    	        ),
    	        'qt_7a3' => array(
    	            'question_id'=>'qt_7a3',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Körperliche und/oder seelische Gesundheit von Pflegepersonen in Familie/Umfeld',
    	            'info_row'=>'(z.B. körperliche Belastbarkeit, Stressresistenz, Psychosomatik etc.)'
    	        ),
    	        'qt_7a4' => array(
    	            'question_id'=>'qt_7a4',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Problemsicht von pflegenden Personen in Familie/Umfeld',
    	            'info_row'=>'(z.B. Einsicht in Schwere der Erkrankung, Problemsicht etc.)'
    	        ),
    	        'qt_7a5' => array(
    	            'question_id'=>'qt_7a5',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Umgang mit belastenden Situationen',
    	            'info_row'=>'(z.B. Frustrationstoleranz, Copingstrategien, Optimismus etc.)'
    	        ),
    	        'qt_7b' => array(
    	            'question_id'=>'qt_7b',
    	            'nr'=>'7b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
  
    	        'qt_8a' => array(
    	            'question_id'=>'qt_8a',
    	            'nr'=>'8a',
    	            'category'=>'<u><b>Versorgungs- situation</b></u>',
    	            'description'=>'<i>Bei den folgenden zwei Fragen handelt es sich um die fachliche Gesamt-Einschätzung der Interviewer:
                    Anhand meines persönlichen Eindrucks der/des Betroffenen und/oder der Hauptpflegeperson sehe ich ein/kein erhöhtes Versorgungsrisiko oder eine Versorgungskrise.</i>',
    	            'info_row'=>''
    	        ),
    	        'qt_8b' => array(
    	            'question_id'=>'qt_8b',
    	            'nr'=>'8b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	        
    	        'qt_9a1' => array(
    	            'question_id'=>'qt_9a1',
    	            'nr'=>'9',
    	            'category'=>'<u><b>Materielles System</b></u>',
    	            'description'=>'<i>Gibt es Anzeichen für materielle Mängellage oder ist die Ausstattung ausreichend?</i>',
    	            'info_row'=>'(z.B. es gibt fehlt eine angemessene Grundausstattung, die materielle Basis ist ausreichen/komfortabel)'
    	        ),
    	        'qt_9a2' => array(
    	            'question_id'=>'qt_9a2',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Finanzen',
    	            'info_row'=>'(z.B. Einkommenslage, Erwerbstätigkeit der Angehörigen etc.)'
    	        ),
    	        'qt_9a3' => array(
    	            'question_id'=>'qt_9a3',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Wohnen',
    	            'info_row'=>'(z.B. Größe der Wohnung, barrierefreie Ausstattung etc.)'
    	        ),
    	        'qt_9b' => array(
    	            'question_id'=>'qt_9b',
    	            'nr'=>'9b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	        
    	        'qt_10a1' => array(
    	            'question_id'=>'qt_10a1',
    	            'nr'=>'10',
    	            'category'=>'<u><b>Persönliches System</b></u>',
    	            'description'=>'<i>Lebensführung</i>',
    	            'info_row'=>'(z.B. Selbständigkeit im Alltag, Körperhygiene, äußeres Erscheinungsbild, Tagesstruktur etc.)'
    	        ),
    	        'qt_10a2' => array(
    	            'question_id'=>'qt_10a2',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Orientierung',
    	            'info_row'=>'(z.B. räumliche, zeitliche, situative, personenbezogene Orientierung)'
    	        ),
    	        'qt_10a3' => array(
    	            'question_id'=>'qt_10a3',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Gedächtnis',
    	            'info_row'=>'(z.B. Kurz-/Langzeitgedächtnis, Merkfähigkeit etc.)'
    	        ),
    	        'qt_10a4' => array(
    	            'question_id'=>'qt_10a4',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'sonstige kognitive Funktionen',
    	            'info_row'=>'(z.B. Auseinandersetzung mit der Umwelt, Urteilsvermögen, Aufmerksamkeits-/ Konzentrationsfähigkeit, Lernfähigkeit, Kreativität, Körperwahrnehmung/ -schema etc.)'
    	        ),
    	        'qt_10a5' => array(
    	            'question_id'=>'qt_10a5',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Motivation/ innerer Antrieb',
    	            'info_row'=>'(z.B. Begeisterungsfähigkeit, Eigeninitiative, Sinnorientierung etc.)'
    	        ),
    	        'qt_10a6' => array(
    	            'question_id'=>'qt_10a6',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Selbstwertgefühl',
    	            'info_row'=>'(z.B. Zutrauen, Selbsteinschätzung Selbstbild etc.)'
    	        ),
    	        'qt_10a7' => array(
    	            'question_id'=>'qt_10a7',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Krankheitseinsicht',
    	            'info_row'=>'(z.B. Begeisterungsfähigkeit, Eigeninitiative, Sinnorientierung etc.)'
    	        ),
    	        'qt_10a8' => array(
    	            'question_id'=>'qt_10a8',
    	            'nr'=>'',
    	            'category'=>'',
    	            'description'=>'Kommunikation',
    	            'info_row'=>'(z.B. verbale/nonverbale Informationsaustauschfähigkeit, Sprachlicher Ausdruck etc.)'
    	        ),
    	        'qt_10b' => array(
    	            'question_id'=>'qt_10b',
    	            'nr'=>'10b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	       
    	        'qt_11' => array(
    	            'question_id'=>'qt_11',
    	            'nr'=>'11',
    	            'category'=>'<u><b>Teilhabe</b></u>',
    	            'description'=>'<i>Gesellschaftliche Teilhabe des Patienten.Ist die Teilhabe (in Teilbereichen) gesichert/gefährdet?</i>',
    	            'info_row'=>'(z.B. Teilnahme und Teilhabe am gesellschaftlichen Leben, Interesse an öffentlichen Veranstaltungen etc.)'
    	        ),
    	        'qt_11b' => array(
    	            'question_id'=>'qt_11b',
    	            'nr'=>'11b',
    	            'category'=>'',
    	            'description'=>'Bitte nennen Sie nachfolgend stichpunktartig die Aspekte, an denen Sie Ihren Eindruck festmachen',
    	            'info_row'=>''
    	        ),
    	        
    	    );
    	    
    	    
    	    
    	    if(  !empty($_POST[$elementsBelongTo])){
    	        $options = $_POST[$elementsBelongTo][$elementsBelongTo];
    	        $options['form_type'] = $elementsBelongTo;
    	    }
    	    
    	    $html_str = "";
    	    $html_str .= '<table class="dscdsv SimpleTable" cellpadding="0" cellspacing="0" border="1"> ';
    	    $html_str .= '<thead>';
    	    $html_str .= '<tr>';
    	    $html_str .= '<th class="q_opt_text_th_0">Nr.</th>';
    	    $html_str .= '<th ">Kategorie</th>';
    	    $html_str .= '<th ">Exploration</th>';
    	    $html_str .= '<th ">Problemlagen</th>';
    	    $html_str .= '<th ">Ressourcen</th>';
    	    $html_str .= '<th ">Interv. Bedarf</th>';
    	    $html_str .= '</tr>';
    	    $html_str .= '</thead>';
    	    $html_str .= '<tbody>';
    	    foreach($table_question_info as $question_id=>$qinfo){
    	        
    	        if( substr($qinfo['question_id'], -1) == "b" ){
    	            
   	                $cls_special="specialb";

    	            $html_str .= '<tr style="page-break-inside: avoid;">';
    	            
    	            $html_str .= '<td class="q_opt_text_th_0" >'.$qinfo["nr"].'</td>';
    	            
    	            $html_str .= '<td  colspan="5" class="specialb_height" >';
    	         
    	      
    	            $html_str .= '<span class="description '.$cls_special.'" >'.$qinfo["description"].'</span>';
    	            $html_str .= '<span class="row_info dontPrint">'.$qinfo["info_row"].'</span>';
    	            $html_str .= '<span class="row_info dontPrint">'.$qinfo["info_row"].'</span>';
    	            
    	            

	                $html_str .= '<div class="tfree_texts_dsc">';
	                
	                if( ! is_array($options[$qinfo['question_id']]['opt_1']) ){
	                    $qt_xb_1_opt_1 = $options[$qinfo['question_id']]['opt_1'];
	                    $html_str .= '<textarea rows="3" cols="50" name="dscdsv[dscdsv]['.$qinfo['question_id'].'][opt_1]" >'.$qt_xb_1_opt_1.'</textarea>';
	                } else{
	                    $qt_xb_1_opt_1 = $options[$qinfo['question_id']]['opt_1']['value'];
	                    $html_str .= '<textarea rows="3" cols="50" name="dscdsv[dscdsv]['.$qinfo['question_id'].'][opt_1]" >'.$qt_xb_1_opt_1.'</textarea>';
	                }
	                
	                $html_str .= '<br/> &nbsp;';
	                $html_str .= '</div>';

    	            $html_str .= '</td>';
    	            
    	            $html_str .= '</tr>';
    	        }
    	        else
    	        {
    	            $cls_special="";

       	            $html_str .= '<tr style="page-break-inside: avoid;">';
    	            
    	            $html_str .= '<td class="q_opt_text_th_0" >'.$qinfo["nr"].'</td>';
    	            
    	            $html_str .= '<td class="q_opt_text_th_1" >'.$qinfo["category"].'</td>';

    	            $html_str .= '<td class="q_opt_text_th_2" >';

    	            
    	            if( $qinfo['question_id'] != "qt_8a" ){
    	                $html_str .= '<span class="info_triger dontPrint"></span>';
    	            }
    	            $html_str .= '<span class="description '.$cls_special.' ">'.$qinfo["description"].'</span>';
    	            $html_str .= '<span class="row_info dontPrint">'.$qinfo["info_row"].'</span>';
    	            
    	            $html_str .= '</td>';
    	            
	                $html_str .= '<td class="q_opt_text_th_3" >';
	                foreach($problemlagen as $keyp=> $valp){
	                    $checkedp ="";
	                    if($options[$qinfo['question_id']]['opt_1']['value'] ==  $keyp && $options[$qinfo['question_id']]['opt_1']['value'] != NULL ){
	                        $checkedp ='checked="checked"';
	                    }
	                    $html_str .= '<input type="radio" style="margin-right: 0px;"  id="'.$qinfo['question_id'].'[opt_1]['.$keyp.']" name="dscdsv[dscdsv]['.$qinfo['question_id'].'][opt_1]" value='.$keyp.' '.$checkedp.'  /> '  ;
	                    $html_str .= '<label for="'.$qinfo['question_id'].'[opt_1]['.$keyp.']" style="padding-left: 2px;" >'.$valp.'</label><br>';
	                }
	                $html_str .= '</td>';
	                
	                $html_str .= '<td class="q_opt_text_th_4" >';
	                foreach($ressourcen as $keyr=> $valr){
	                    $checkedr ="";
	                    if($options[$qinfo['question_id']]['opt_2']['value'] ==  $keyr && $options[$qinfo['question_id']]['opt_2']['value'] != NULL ){
	                        $checkedr ='checked="checked"';
	                    }
	                    $html_str .= '<input type="radio" style="margin-right: 0px;"  id="'.$qinfo['question_id'].'[opt_2]['.$keyr.']" name="dscdsv[dscdsv]['.$qinfo['question_id'].'][opt_2]" value='.$keyr.' '.$checkedr.'  /> '  ;
	                    $html_str .= '<label for="'.$qinfo['question_id'].'[opt_2]['.$keyr.']" style="padding-left: 2px;" >'.$valr.'</label><br>';
	                }
	                $html_str .= '</td>';
	                
	                $html_str .= '<td class="q_opt_text_th_5" >';
	                foreach($interventionsbedarf as $keyi=> $vali){
	                    $checkedi ="";
	                    if($options[$qinfo['question_id']]['opt_3']['value'] ==  $keyi && $options[$qinfo['question_id']]['opt_3']['value'] != NULL ){
	                        $checkedi ='checked="checked"';
	                    }
	                    $html_str .= '<input type="radio" style="margin-right: 0px;" id="'.$qinfo['question_id'].'[opt_3]['.$keyi.']" name="dscdsv[dscdsv]['.$qinfo['question_id'].'][opt_3]" value='.$keyi.' '.$checkedi.'  /> '  ;
	                    $html_str .= '<label for="'.$qinfo['question_id'].'[opt_3]['.$keyi.']" style="padding-left: 2px;" >'.$vali.'</label><br>';
	                }
	                $html_str .= '</td>';

    	            $html_str .= '</tr>';
    	        }
    	    }
    	    
    	    $html_str .= '</tbody>';
    	    $html_str .= '</table>';
    	    
    	    $question->addElement('note', 'sblock', array(
    	        'value' => $html_str,
    	        'decorators' => array(
    	            'ViewHelper',
    	            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class'=>'dscdsv_form_block'))
    	        ),
    	    ));
    	    
    	    $subform->addSubform($question, "big_table");
    	    

    	    
    	    return $subform;
    	}
    	
    		       
    		        
}

