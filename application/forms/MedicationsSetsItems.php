<?php
/**
 * 
 * @author carmen
 * 
 * 26.10.2018 ISPC-2247
 *
 */
class Application_Form_MedicationsSetsItems extends Pms_Form
{
	protected $_client_modules = null;
	
	protected $_medication_dosage = null;
	
	protected $_medication_dosageform_mmi = null; //ISPC-2554 pct.1 Carmen 06.04.2020
	
	protected $_medication_frequency = null;
	
	protected $_medication_types = null;
	
	protected $_med_dos_opt = null;	

	protected $_med_dos_opt_mmi = null; //ISPC-2554 pct.1 Carmen 06.04.2020
	
	protected $_med_freq_opt = null;
	
	protected $_med_typ_opt = null;
	
	protected $_med_dos_opt_id = null;
	
	protected $_med_dos_opt_mmi_id = null; //ISPC-2554 pct.1 Carmen 07.04.2020
	
	protected $_med_freq_opt_id = null;
	
	protected $_med_typ_opt_id = null;
	
	protected $_countmedfreq = null;
	
	protected $_countmeddosage = null;
	
	protected $_countmedtypes = null;
	
	
	protected $_medication_indications = null;
		
	protected $_sets_time_scheme = null;     ////ISPC-2247 pct.1 Lore 07.05.2020
	
	
	public function __construct($options = null)
	{		
		if (isset($options['_client_modules']))
		{
			$this->_client_modules = $options['_client_modules'];
			unset($options['_client_modules']);
		}
		

		//Darreichungsform
		if (isset($options['_medication_dosage']))
		{
			$this->_medication_dosage = $options['_medication_dosage'];
			foreach($this->_medication_dosage as $dosage_id=>$data_dosage_form)
			{
				if($this->_client_modules['87'])
				{
					if($data_dosage_form['extra'] == 0)
					{
						$this->_med_dos_opt[$data_dosage_form['id']] = $data_dosage_form['dosage_form'];
					}				
					$this->_med_dos_opt_id[$dosage_id] = $data_dosage_form['id'];
				}
				else 
				{
					if($data_dosage_form['extra'] == 0 || $data_dosage_form['isfrommmi'] == '1')
					{
						$this->_med_dos_opt[$data_dosage_form['id']] = $data_dosage_form['dosage_form'];
					}
					$this->_med_dos_opt_id[$dosage_id] = $data_dosage_form['id'];
				}
			}
			unset($options['_medication_dosage']);
		}
		
		//ISPC-2554 pct.1 Carmen 06.04.2020
		if (isset($options['_medication_dosageform_mmi']))
		{
			$this->_medication_dosageform_mmi = $options['_medication_dosageform_mmi'];
			
			foreach($this->_medication_dosageform_mmi as $dosage_id=>$data_dosage_form)
			{
				$this->_med_dos_opt_mmi[$dosage_id] = $data_dosage_form;
			}
			$this->_med_dos_opt_mmi_id[] = $dosage_id;
			unset($options['_medication_dosageform_mmi']);
		}
		//--
		
		//ISPC-2247 pct.1 Lore 07.05.2020
		if (isset($options['_sets_time_scheme']))
		{
		    $this->_sets_time_scheme = $options['_sets_time_scheme'];
		}
		//.
		
		//Intervall
		if (isset($options['_medication_frequency']))
		{
			$this->_medication_frequency = $options['_medication_frequency'];
			
			foreach($this->_medication_frequency as $freq_id =>$data_frequ)
			{
				if($data_frequ['extra'] == 0)
				{
					$this->_med_freq_opt[$data_frequ['id']] = $data_frequ['frequency'];
				}				
				$this->_med_freq_opt_id[$freq_id] = $data_frequ['id'];
			}
			
			unset($options['_medication_frequency']);
		}
		
		
		//Applikationsweg
		if (isset($options['_medication_types']))
		{
			$this->_medication_types = $options['_medication_types'];
			foreach($this->_medication_types as $mtype_id =>$data_mtype)
			{
				if($data_mtype['extra'] == 0)
				{
					$this->_med_typ_opt[$data_mtype['id']] = $data_mtype['type'];
				}
				$this->_med_typ_opt_id[$mtype_id] = $data_mtype['id'];
			}
				
			unset($options['_medication_types']);
		}
		
		
		//INDICATION - Ancuta
		if (isset($options['_medication_indications']))
		{
			$this->_medication_indications = $options['_medication_indications'];
			unset($options['_medication_indications']);// ? why
		}
		
		
		
		
		$this->_countmedfreq = count($this->_med_freq_opt);
		$this->_countmeddosage = count($this->_med_dos_opt)+count($this->_med_dos_opt_mmi);
		$this->_countmedtypes = count($this->_med_typ_opt);
		
		parent::__construct($options);
		
	}
	
	public function isValid($data)
	{
		 
		return parent::isValid($data);
	}
	
	public function create_form_medicationssetsitems( $options = array(), $elementsBelongTo = null)
	{
		 
		$this->clearDecorators();
		//$this->addDecorator('HtmlTag', array('tag' => 'table'));
		$this->addDecorator('FormElements');
		$this->addDecorator('Fieldset', array('legend' => $options['bid']['value'] != '' ? $this->translator->translate('medicationssets_edit') : $this->translator->translate('medicationssets_add')));
		$this->addDecorator('Form');
	
		if ( ! is_null($elementsBelongTo)) {
			$this->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		//print_r($options); exit;		
		//add hidden
		$this->addElement('hidden', 'bid', array(
				'value' => $options['bid'] != '' ? $options['bid'] : '',
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
		
		
		
		$this->addElement('text', 'title', array(
				'label' 	   => $this->translator->translate('title'), 
				'value'        => $options['bid'] != '' ? $options['title'] : '',
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'PREPEND')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;', 'class' => 'titlediv'))
				),
				
		));
		
		$this->addElement('select', 'med_type', array(
				'label' 	   => $this->translator->translate('med_type'),
		    'multiOptions' => array('isbedarfs' => 'Bedarfs', 'iscrisis' => 'Krise', 'actual' => 'Aktuell' ),
				'value'        => $options['med_type'],
				'required'     => false,
				'filters'      => array('StringTrim'),
		       // 'onchange' => 'window.alert(this.value);',
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'PREPEND')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));

		
		
		// 08.11.2018
		$this->addElement('select', 'set_indication', array(
				'label' 	   => $this->translator->translate('set indication'),
				'multiOptions' => $this->_medication_indications,
				'value'        => $options['set_indication'],
				'required'     => false,
				'filters'      => array('StringTrim'),
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'PREPEND')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
				),
		));
	
		$setrows = count($options['data']);
		
		$this->addElement('hidden', 'setcount', array(
				'value' => ($setrows > 0) ? $setrows : 0,
				'readonly' => true,
				'id' => 'setcount',
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
						array(array('sdiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 100%; float: left; height: 100%;',
								'id' => 'set',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						)
				),
				
		));
		
		
if(!empty($options['med_type'])){
		    
		    
		    

		 //var_dump($this->_medication_dosageform_mmi); exit;
		//print_r($options['data']); exit;
		foreach($options['data'] as $krow=>$vrow)
		{
			$subform = new Zend_Form_SubForm();
			$subform->setElementsBelongTo("set[".$krow."]");
			$subform->clearDecorators()
			->setDecorators( array(
					'FormElements',
					//array('HtmlTag',array('tag'=>'div', 'class' => 'formular_actions')),
			));
			
			if ( ! is_null($elementsBelongTo)) {
				$subform->setOptions(array(
						'elementsBelongTo' => $elementsBelongTo
				));
			}
		        $freqval = array();
		        $freqval_custom_id = 0;
		        $freqval_custom_text = "";
				foreach($vrow['frequency']['value'] as $kr=>$vr)
				{					
						$freqval[] =  array_search($vr, $this->_med_freq_opt_id);
						
						if($this->_medication_frequency[array_search($vr, $this->_med_freq_opt_id)]['extra'] == 1)
						{
						    $freqval_custom_id = $vr;
						    $freqval_custom_text = $this->_medication_frequency[array_search($vr, $this->_med_freq_opt_id)]['frequency'];
							//$this->_med_freq_opt[array_search($vr, $this->_med_freq_opt_id)] = $this->_medication_frequency[array_search($vr, $this->_med_freq_opt_id)]['frequency'];
						}
				}

				$meddosval = array();
				$meddosvalmmi = array();
				$dosage_form_custom_id = 0;
				$dosage_form_custom_text = '';
				
				foreach($vrow['med_dosage_form']['value'] as $kr=>$vr)
				{
					$meddosval[] =  array_search($vr, $this->_med_dos_opt_id);
					if($this->_medication_dosage[array_search($vr, $this->_med_dos_opt_id)]['extra'] == 1 && $this->_medication_dosage[array_search($vr, $this->_med_dos_opt_id)]['isfrommmi'] == '0')
					{
					    $dosage_form_custom_id = $vr;
					    $dosage_form_custom_text = $this->_medication_dosage[array_search($vr, $this->_med_dos_opt_id)]['dosage_form'];
						//$this->_med_dos_opt[array_search($vr, $this->_med_dos_opt_id)] = $this->_medication_dosage[array_search($vr, $this->_med_dos_opt_id)]['dosage_form'];
					}
					//ISPC-2554 pct.1 Carmen 07.04.2020
					if($this->_client_modules['87'] && array_key_exists($vr, $this->_medication_dosageform_mmi))
					{
						$meddosvalmmi[] = $vr;
						//$this->_med_dos_opt[array_search($vr, $this->_med_dos_opt_id)] = $this->_medication_dosage[array_search($vr, $this->_med_dos_opt_id)]['dosage_form'];
					}
					else
					{
						if($this->_medication_dosage[array_search($vr, $this->_med_dos_opt_id)]['isfrommmi'] == 1)
						{
							$meddosvalmmi[] = $vr;
							//$this->_med_dos_opt[array_search($vr, $this->_med_dos_opt_id)] = $this->_medication_dosage[array_search($vr, $this->_med_dos_opt_id)]['dosage_form'];
						}
					}
				}
				//var_dump($meddosvalmmi); exit;
				$medtypval = array();
				$type_custom_id = 0;
				$type_custom_text = '';
				foreach($vrow['type']['value'] as $kr=>$vr)
				{
					$medtypval[] =  array_search($vr, $this->_med_typ_opt_id);
					if($this->_medication_types[array_search($vr, $this->_med_typ_opt_id)]['extra'] == 1)
					{
					    
					    $type_custom_id = $vr;
					    $type_custom_text = $this->_medication_types[array_search($vr, $this->_med_typ_opt_id)]['type'];
						//$this->_med_typ_opt[array_search($vr, $this->_med_typ_opt_id)] = $this->_medication_types[array_search($vr, $this->_med_typ_opt_id)]['type'];
					}
				}
				//print_r($vrow['frequency']['value']); exit;
				//$countmedfreq = count($this->_medication_frequency);				
				//$countmeddosage = count($this->_medication_dosage);
				
				$countdosage = count($vrow['dosage']['value']);
				$textarearows = ($this->_countmeddosage >= $this->_countmedfreq) ? $this->_countmeddosage : $this->_countmedfreq;
				$textarearows = ($textarearows >= $this->_countmedtypes) ? $textarearows : $this->_countmedtypes;
				$textarearows = ($textarearows >= $countdosage) ? $textarearows : $countdosage;
				if($textarearows == 0)
				{
					$textarearows = 3;
				}
				$textarearows += 7;
				$divheight  = $textarearows*24;
				
				if($vrow['atc_code']['value'] != '')
				{
					$medication_atc = array(
							'atc_code' => $vrow['atc_code']['value'],
							'atc_description' => $vrow['atc_description']['value'],
							'atc_groupe_code' => $vrow['atc_groupe_code']['value'],
							'atc_groupe_description' => $vrow['atc_groupe_description']['value'],
					);
				}
				else 
				{
					$medication_atc = array();
				}
				
				$subform->addElement('note', 'label_drug'.$krow, array(
						'value' => $this->translate('label_drug'),
						'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
						array(array('idiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 15%; float: left; border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						),
						array(array('ediv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 100%; border: 1px solid #000; float: left;',
								'openOnly' => true,
								'class' => 'rowdiv',
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						),
						),				
				));
				
				//add hidden
				$subform->addElement('hidden', 'id', array(
						'value' => ($vrow['id']['value']) ? $vrow['id']['value'] : '',
						'readonly' => true,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
						),
				));
				
				$subform->addElement('text', 'drug', array(
						'belongsTo' => 'set['.$krow.']',
						'value'        => $vrow['drug']['value'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'class' => 'form_drug',						
						'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 80%; float: left;')),
						),
						'style' => 'width: 95%;'
				));
				
				if($this->_client_modules["87"])
				{
					$subform->addElement('text', 'add_mmi', array(
							'belongsTo' => 'set['.$krow.']',
							'value' => 'MMI',
							'class' => 'mmi_search_button',
							'data-row' => $krow,
							'readonly' => true,
							'decorators' => array(
									'ViewHelper',
									array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 25%;')),
									array(array('cleartag' => 'HtmlTag'), array(
											'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
									),
							'style' => 'width: 90%;'
							));
				}
				
				$subform->addElement('note', 'label_medication'.$krow, array(
						'value' => $this->translate('label_medication'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
						),
				));
				//add hidden medications
				$subform->addElement('hidden', 'hidd_medication', array(
						'belongsTo' => 'set['.$krow.']',
						'value' => $vrow['medication_id']['value'] ? $vrow['medication_id']['value'] : '',
						'readonly' => true,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
						),
				));
				
				$subform->addElement('hidden', 'pzn', array(
						'belongsTo' => 'set['.$krow.']',
						'value' => $vrow['MedicationMaster']['value']['pzn'],
						'readonly' => true,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
						),
				));
				
				$subform->addElement('hidden', 'source', array(
						'belongsTo' => 'set['.$krow.']',
						'value' => $vrow['MedicationMaster']['value']['source'],
						'readonly' => true,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
						),
				));
				
				$subform->addElement('hidden', 'dbf_id', array(
						'belongsTo' => 'set['.$krow.']',
						'value' => $vrow['MedicationMaster']['value']['dbf_id'],
						'readonly' => true,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
						),
				));
				
				//ISPC-2554 pct.3 Carmen 06.04.2020
				$subform->addElement('hidden', 'atc', array(
						'belongsTo' => 'set['.$krow.']',
						'value' => !empty($medication_atc) ? json_encode($medication_atc) : '',
						'readonly' => true,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
						),
				));
				//ISPC-2554 Carmen 13.05.2020
				$subform->addElement('hidden', 'unit', array(
						'belongsTo' => 'set['.$krow.']',
						'value' => $vrow['unit']['value'],
						'readonly' => true,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
						),
				));
				//--
				/*$subform->addElement('hidden', 'comment', array(
						'belongsTo' => 'set['.$krow.']',
						'value' => '',
						'readonly' => true,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;'))
						),
				));*/
				
				$subform->addElement('text', 'medication', array(
						'belongsTo' => 'set['.$krow.']',
						'value'        => $vrow['medication']['value'],
						'required'     => false,
						'filters'      => array('StringTrim'),
						'class' => 'livesearchmedinp med',
						'data-row' => $krow,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 80%;')),
								array(array('idiv' => 'HtmlTag'), array(
										'tag' => 'div',
										'style' => 'width: 25%; float: left; border-right: 1px solid #000;',
										'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND),
								),
						),
						'style' => 'width: 95%;'
				));
				
				$subform->addElement('note', 'label_dosage'.$krow, array(
						'value' => $this->translate('label_dosage'),
						'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
						array(array('ddiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 100%; float: left; padding-left: 5px; padding-top: 5px;',
								'openOnly' => true,
								'id' => 'dosage'.$krow,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						),
						array(array('idiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 10%; float: left; border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						),
						),				
				));
				
				$subform->addElement('hidden', 'dosagecount'.$krow, array(
						'value' => ($countdosage > 0) ? $countdosage : 1,
						'readonly' => true,
						'id' => 'new_dosage'.$krow,
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;'))
						),
				));
				//print_r($vrow['dosage']['value']); exit;
				if($vrow['dosage']['value'])
				{
					$enddosage = end($vrow['dosage']['value']);
					$t= 0 ;
					$row_count = 0;
					foreach($vrow['dosage']['value'] as $kval=>$vval)
					{  
					   // if($vval == $enddosage )
					    // ISPC-2247 pct.1 Lore 30.04.2020 
					    if($vval == $enddosage && max(array_keys($vrow['dosage']['value'])) == $row_count )
						{
						    if(isset($vrow['dosage']['schema'])){
						        $subform->addElement('note', 'schema'.$kval, array(
						            'value' => $vrow['dosage']['schema'][$kval],
						            'decorators' => array(
						                'ViewHelper',
						                array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 30%; color: green;')),
						            ),
						        ));
						    }

						    
						    $subform->addElement('text', 'dosage', array(
    								//'belongsTo' => 'set['.$krow.'][dosage]',
    								'isArray' => true,
    								'value'        => $vval,
    								'required'     => false,
    								'filters'      => array('StringTrim'),
    								'class' => 'form_dosage',
    								'id' => 'dosage-'.$krow.'-'.$kval,
    								'decorators' => array(
    											'ViewHelper',
    											array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
    										
    											array(array('ddiv' => 'HtmlTag'), array(
    												'tag' => 'div',
    												'style' => 'width: 100%; float: left; padding-left: 5px; padding-top: 5px;',
    												'closeOnly' => true,
    												'id' => 'dosage'.$krow,
    												'placement' => Zend_Form_Decorator_Abstract::APPEND),
    												array(array('cleartag' => 'HtmlTag'), array(
    															'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
    										),
    						),
    								'style' => 'width: 65%;',
    						));
						}
						else 
						{
						    if(isset($vrow['dosage']['schema'])){
						        $subform->addElement('note', 'schema'.$kval, array(
						            'value' => $vrow['dosage']['schema'][$kval],
						            'decorators' => array(
						                'ViewHelper',
						                array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 30%; color: green;')),
						            ),
						        ));
						    }
						    
							$empty = $subform->createElement('text', 'dosage[]', array(
									//'belongsTo' => 'set['.$krow.'][dosage]',
									'isArray' => true,
									'value'        => $vval,
									'required'     => false,
									'filters'      => array('StringTrim'),
									'class' => 'form_dosage',
									'id' => 'dosage-'.$krow.'-'.$kval,
									'decorators' => array(
											'ViewHelper',
											array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
									),
									'style' => 'width: 65%;',
							));
							
							$subform->addElement($empty,'dosage_arr_'.$t++);
							
						}
						$row_count++;
					}
				}
				else 
				{	

 				    $empty_slot = $subform->createElement('text', 'dosage[]', array(
				        // 					$subform->addElement('text', 'dosage', array(
				        //'belongsTo' => 'set['.$krow.'][dosage]',,
				        'isArray' => true,
				        'value'        => '',
				        'required'     => false,
				        'filters'      => array('StringTrim'),
				        'class' => 'form_dosage',
				        'id' => 'dosage-'.$krow.'-0',
				        'decorators' => array(
				            'ViewHelper',
				            array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
				            array(array('cleartag' => 'HtmlTag'), array(
				                'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				            array(array('ddiv' => 'HtmlTag'), array(
				                'tag' => 'div',
				                'style' => 'width: 100%; float: left; padding-left: 5px; padding-top: 5px;',
				                'closeOnly' => true,
				                'placement' => Zend_Form_Decorator_Abstract::APPEND),
				            ),
				        ),
				        'style' => 'width: 85%;',
				    ));
				    
				    $subform->addElement($empty_slot,'dosage_arr_empty_'.$t++); 
					//. 
				}
				
				// ISPC-2247 pct.1 Lore 05.05.2020
				if(!isset($vrow['dosage']['schema'])){
				    $subform->addElement('note', 'add_dosage'.$krow, array(
				        'value' => '<img src="'.RES_FILE_PATH.'/images/btttt_plus.png" class="add_dosage" data-row="'.$krow.'" />',
				        'decorators' => array(
				            'ViewHelper',
				            array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
				            array(array('idiv' => 'HtmlTag'), array(
				                'tag' => 'div',
				                'style' => 'width: 13%; float: left; border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
				                'closeOnly' => true,
				                'placement' => Zend_Form_Decorator_Abstract::APPEND),
				            ),
				        )));
				} else {
				    $subform->addElement('note', 'add_dosage'.$krow, array(
				        'value' => '',
				        'decorators' => array(
				            'ViewHelper',
				            array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
				            array(array('idiv' => 'HtmlTag'), array(
				                'tag' => 'div',
				                'style' => 'width: 13%; float: left; border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
				                'closeOnly' => true,
				                'placement' => Zend_Form_Decorator_Abstract::APPEND),
				            ),
				        )));
				}
/* 				$subform->addElement('note', 'add_dosage'.$krow, array(
						//'belongsTo' => 'set['.$krow.']',
						'value' => '<img src="'.RES_FILE_PATH.'/images/btttt_plus.png" class="add_dosage" data-row="'.$krow.'" />',
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
								array(array('idiv' => 'HtmlTag'), array(
										'tag' => 'div',
										'style' => 'width: 13%; float: left; border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
										'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND),
								),
				))); */
				
				
				
				// Intervall
				$subform->addElement('note', 'label_frequency'.$krow, array(
						'value' => $this->translate('label_frequency'),
						'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
						array(array('idiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 14%; float: left;  border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
								'openOnly' => true,
								'placement' => Zend_Form_Decorator_Abstract::PREPEND),
						),
						),				
				));
				
				
				if( is_array($this->_med_freq_opt) && ! empty($this->_med_freq_opt)){
    				$subform->addElement('multiCheckbox', 'frequency', array(
    						//'belongsTo' => 'set['.$krow.']',
    						'label'      => null,
    						'required'   => false,
    						'multiOptions' => $this->_med_freq_opt,
    						'value' => $freqval,
    						'decorators' => array(
    						'ViewHelper',
    						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
    						),
    						'escape'     => null,
    				
    				));
				}
				
				$subform->addElement('checkbox', 'frequency_custom', array(
						'belongsTo'  => 'set['.$krow.']',
						//'isArray' => true,
						'value'      => ($freqval_custom_id != '0') ? $freqval_custom_id : '',
				        //'checked'    => ($freqval_custom_id != 0 && strlen($freqval_custom_text) > 0 ) ? true : false,
				        'checkedValue' => $freqval_custom_id,
						'uncheckedValue' => 'no',
						'class' => 'frequency_custom',
						'data-row' => $krow,
						'required'   => false,
						'filters'    => array('StringTrim'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 15%; float: left;', 'class' => 'ipad_chk')),
								),
						));
				
				$subform->addElement('text', 'frequency_custom_text', array(
						//'belongsTo' => 'set['.$krow.']',
				        'value'        => ( strlen($freqval_custom_text) > 0 ) ? $freqval_custom_text : '',
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 80%; float: left;', 'class' => 'ipad_text')),
								array(array('cleartag' => 'HtmlTag'), array(
										'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
								array(array('idiv' => 'HtmlTag'), array(
										'tag' => 'div',
										'style' => 'width: 15%; float: left;  border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
										'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND),
								),
								
						),
						'style' => 'width: 95%;'
						
				));
				
				
				
				// Darreichungsform
				$subform->addElement('note', 'label_med_dosage_form'.$krow, array(
						'value' => $this->translate('label_med_dosage_form'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
								array(array('idiv' => 'HtmlTag'), array(
										'tag' => 'div',
										'style' => 'width: 30%; float: left;  border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
										'openOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::PREPEND),
								),
						),
				));
				
				if($this->_client_modules["87"] && ! empty($this->_med_dos_opt_mmi))
				{
					$subform->addElement('note', 'label_med_dosage_form_clientlist'.$krow, array(
							'value' => $this->translate('client dosageform list'),
							'decorators' => array(
									'ViewHelper',
									array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; font-weight: bold;')),
							),
					));
				}
				
				if( is_array($this->_med_dos_opt) && ! empty($this->_med_dos_opt)){
    				$subform->addElement('multiCheckbox', 'med_dosage_form', array(
    						//'belongsTo' => 'set['.$krow.']',
    						'label'      => null,
    						'required'   => false,
    						'multiOptions' => $this->_med_dos_opt,
    						'value' => $meddosval,
    						'decorators' => array(
    								'ViewHelper',
    								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
    						),
    						'escape'     => null,
    				
    				));
				}
				if($this->_client_modules["87"] && ! empty($this->_med_dos_opt_mmi))
				{
					$subform->addElement('note', 'label_med_dosage_form_mmilist'.$krow, array(
							'value' => $this->translate('mmi dosageform list'),
							'decorators' => array(
									'ViewHelper',
									array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; font-weight: bold;')),
									
							),
					));
					
				if( is_array($this->_med_dos_opt_mmi) && ! empty($this->_med_dos_opt_mmi)){
					$subform->addElement('multiCheckbox', 'med_dosage_form_mmi', array(
							//'belongsTo' => 'set['.$krow.']',
							'label'      => null,
							'required'   => false,
							'multiOptions' => $this->_med_dos_opt_mmi,
							'value' => $meddosvalmmi,
							'decorators' => array(
									'ViewHelper',
									array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
							),
							'escape'     => null,
				
					));
				}
				}
				
				$subform->addElement('checkbox', 'med_dosage_form_custom', array(
						//'belongsTo' => 'set['.$krow.']',
						'value'     => ($dosage_form_custom_id != '0') ? $dosage_form_custom_id : '',
				        //'checked'   => ($dosage_form_custom_id != 0 && strlen($dosage_form_custom_text) > 0 ) ? true : false,
				        'checkedValue' => $dosage_form_custom_id,
						'uncheckedValue' => 'no',
						'class' => 'med_dosage_form_custom',
						'data-row' => $krow,
						'required'  => false,
						'filters'   => array('StringTrim'),
						'decorators'=> array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 15%; float: left;', 'class' => 'ipad_chk')),
						),
				));
				
				$subform->addElement('text', 'med_dosage_form_custom_text', array(
						//'belongsTo' => 'set['.$krow.']',
						'value'        => ( strlen($dosage_form_custom_text) > 0 ) ? $dosage_form_custom_text : '',
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 80%; float: left;', 'class' => 'ipad_text')),
								array(array('cleartag' => 'HtmlTag'), array(
										'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
								array(array('idiv' => 'HtmlTag'), array(
										'tag' => 'div',
										'style' => 'width: 14%; float: left;  border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
										'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND),
								),
				
						),
						'style' => 'width: 95%;'
				));
				
				
				
				// Applikationsweg 
				$subform->addElement('note', 'label_types'.$krow, array(
						'value' => $this->translate('label_types'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
								array(array('idiv' => 'HtmlTag'), array(
										'tag' => 'div',
										'style' => 'width: 12%; float: left;  border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
										'openOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::PREPEND),
								),
						),
				));
				
				if( is_array($this->_med_typ_opt) && ! empty($this->_med_typ_opt)){
    				$subform->addElement('multiCheckbox', 'type', array(
    						//'belongsTo' => 'set['.$krow.']',
    						'label'      => null,
    						'required'   => false,
    						'multiOptions' => $this->_med_typ_opt,
    						'value' => $medtypval,
    						'decorators' => array(
    								'ViewHelper',
    								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
    						),
    						'escape'     => null,
    				
    				));
				}
				
				$subform->addElement('checkbox', 'type_custom', array(
						//'belongsTo' => 'set['.$krow.']',
				        'value'     => ($type_custom_id != '0') ? $type_custom_id : '',
				        //'checked'   => ($type_custom_id != 0 && strlen($type_custom_text) > 0 ) ? true : false,
				        'checkedValue' => $type_custom_id,
						'uncheckedValue' => 'no',
						'class' => 'type_custom',
						'data-row' => $krow,
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 15%; float: left;', 'class' => 'ipad_chk')),
						),
				));
				
				$subform->addElement('text', 'type_custom_text', array(
						//'belongsTo' => 'set['.$krow.']',
				        'value'        => (strlen($type_custom_text) > 0 ) ? $type_custom_text : '',
						'required'     => false,
						'filters'      => array('StringTrim'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 80%; float: left;', 'class' => 'ipad_text')),
								array(array('cleartag' => 'HtmlTag'), array(
										'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
								array(array('idiv' => 'HtmlTag'), array(
										'tag' => 'div',
										'style' => 'width: 14%; float: left;  border-right: 1px solid #000; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;',
										'closeOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::APPEND),
								),
				
						),
						'style' => 'width: 95%;'
				));
				
				
				
				// Kommentare
				$subform->addElement('note', 'label_comments'.$krow, array(
						'value' => $this->translate('label_comments'),
						'decorators' => array(
								'ViewHelper',
								array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
								array(array('idiv' => 'HtmlTag'), array(
										'tag' => 'div',
										'style' => 'width: 10%; float: left;  border-right: 1px solid #000; padding-left: 5px; padding-top: 5px; height: '.$divheight.'px;',
										'openOnly' => true,
										'placement' => Zend_Form_Decorator_Abstract::PREPEND),
								),
						),
				));
				//if($krow < $setcount)
				//{
					$subform->addElement('textarea', 'comments', array(
							//'belongsTo' => 'set['.$krow.']',
							'value'        => $vrow['comments']['value'],
							'required'     => false,
							'rows' => $textarearows,
							'filters'      => array('StringTrim'),
							'decorators' => array(
									'ViewHelper',
									array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
									array(array('idiv' => 'HtmlTag'), array(
											'tag' => 'div',
											'style' => 'width: 10%; float: left;  border-right: 1px solid #000;  padding-left: 5px; padding-top: 5px; height: '.$divheight.'px;',
											'closeOnly' => true,
											'placement' => Zend_Form_Decorator_Abstract::APPEND),
									),
									
									
									
							),
							'style' => 'width: 90%;'
					));
					
					$subform->addElement('note', 'del'.$krow, array(
							//'belongsTo' => 'set['.$krow.']',
							'value' => '<img src="'.RES_FILE_PATH.'/images/btttt_minus.png" class="delrow" data-row="'.$krow.'" />',
							'decorators' => array(
									'ViewHelper',
									array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left;')),
									array(array('idiv' => 'HtmlTag'), array(
											'tag' => 'div',
											'style' => 'width: 3%; float: left; height: '.$divheight.'px; padding-left: 5px; padding-top: 5px;'),
									),
									array(array('ediv' => 'HtmlTag'), array(
											'tag' => 'div',
											'style' => 'width: 100%; border: 1px solid #000; float: left; height: 100%;',
											'closeOnly' => true,
											'placement' => Zend_Form_Decorator_Abstract::APPEND),
									),
									array(array('cleartag' => 'HtmlTag'), array(
											'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
												
										
							)));
				//}
				
				$this->addSubform($subform, $krow);
}
//...
		
		}
		
		//add hidden
		$this->addElement('hidden', 'clientid', array(
				'value' => $options['clientid'] != '' ? $options['clientid'] : $this->logininfo->clientid,
				'readonly' => true,
				'decorators' => array(
						'ViewHelper',
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%;')),
						
						array(array('sdiv' => 'HtmlTag'), array(
								'tag' => 'div',
								'style' => 'width: 100%; border: 1px solid #000; float: left; height: 100%;',
								'closeOnly' => true,
								'id' => 'set',
								'placement' => Zend_Form_Decorator_Abstract::APPEND)),
								array(array('cleartag' => 'HtmlTag'), array(
										'tag' => 'div', 'style'=>'clear: both;', 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
				),
		));
		//exit;
		
		$this->addElement('note', 'add_new_row', array(
				//'belongsTo' => 'set['.$krow.']',
				'label' => $this->translator->translate('add_new_medication_row'),
				'value' => '<img src="'.RES_FILE_PATH.'/images/btttt_plus.png" class="ibutton addbutton" style="display: block; float: left; margin-right: 5px;"/>',
				'decorators' => array(
						'ViewHelper',
						array('Label', array('placement' => 'APPEND', 'class' => 'addbutton ipad_label', 'style' => 'display: block; height: 22px; line-height: 22px;')),
						array(array('ltag' => 'HtmlTag'), array('tag' => 'div',	'style' => 'width: 100%; float: left; padding-top: 10px; padding-bottom: 10px;')),
				)));
		
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
	
	public function save_form_medicationset(array $data = array())
	{
	    
// 	    dd($data);
		//print_r($data);exit;
		//$id = $data['id'];
		//ISPC-2554 pct.1 Carmen 03.04.2020
		$modules = new Modules();
		if($modules->checkModulePrivileges("87", $clientid))//mmi activated
		{
			$dosageformmmi = MedicationDosageformMmiTable::getInstance()->getfrommmi();
		}
		//--
		$bid = $data['bid'];
		
		$entity  = new MedicationsSetsList();
		$setdata['id'] = $bid;		
		$setdata['clientid'] = $data['clientid'];
		$setdata['med_type'] = $data['med_type'];
		$setdata['title'] = $data['title'];
		$setdata['set_indication'] = $data['set_indication'];
		
		$medset =  $entity->findOrCreateOneById($bid, $setdata, true);
		
		if($medset->id)
		{
			$entityset = new MedicationsSetsItems();
			
			$q = $entityset->getTable()->createQuery()
			->delete()
			->where('bid = ?', $medset->id)
			->execute();
			
			
			// ISPC-2612 Ancuta 01.07.2020
			$followers  = ConnectionMasterTable::_find_parent_followers2connectionType('MedicationsSetsList', $data['clientid'],'ids');
			if(!empty($followers )){
			    $data_sql = Doctrine_Query::create()
			    ->select("*")
			    ->from('MedicationsSetsList')
			    ->where('connection_id  is not null')
			    ->andWhere('master_id = ?', $medset->id)
			    ->andWhereIn('clientid', $followers );
			    $folower_data_array = $data_sql->fetchArray();
			    
			    $connected_bids = array();
			    foreach($folower_data_array as $fm=>$fml){
			        $connected_bids[] = $fml['id'];
			    }
			    
			    if($connected_bids){
			        $q2 = $entityset->getTable()->createQuery()
			        ->delete()
			        ->whereIn('bid', $connected_bids)
			        ->execute();
			    }
			}
			// --
			
			
		
			$medsetitems = array();
			//$kset = 0;
			if ( ! empty($data['set'])) {				
				
				$lastid_freq = end($this->_medication_frequency)['id'];
				$lastid_meddos = end($this->_medication_dosage)['id'];
				$lastid_medtyp = end($this->_medication_types)['id'];
				
				//$kset = 1;
				foreach ($data['set'] as $set => $values) {
					if($values['hidd_medication'] != '' || $data['newhidd_medication'][$set] != '') //ISPC-2430 Carmen 05.12.2019
					{
					
						$meddoscust = array();
						$medtypcust = array();
						
						$medsetitems[$set] = array();
						$medsetitems[$set]['bid'] = $medset->id;
						$medsetitems[$set]['drug'] = ($values['drug']) ? $values['drug'] : '';
						if($values['hidd_medication'] == '')
						{
							$medsetitems[$set]['medication_id'] = $data['newhidd_medication'][$set];
						}
						else 
						{
							$medsetitems[$set]['medication_id'] = $values['hidd_medication'];
						}
						
						$medsetitems[$set]['medication'] = ($values['medication']) ? $values['medication'] : '';					
						
						foreach($values['dosage'] as $kr=>$vr)
						{							
							if($vr != '')
							{								
								$medsetitems[$set]['dosage'][] = $vr;
							}
						}
						
						// FREQUENCY [Intervall]
						$freqarr = array();
						$freqcust[$set] = array();
						
						//print_r($values['frequency']);
						if(!empty($values['frequency'])){
						    $freqarr = $values['frequency'];
						}
						
						/* foreach($values['frequency'] as $kr=>$vr)
						{
							$freqarr[] = $this->_medication_frequency[$vr]['id'];
						} */
					
						if(isset($values['frequency_custom']) && !empty($values['frequency_custom_text']))
						{
							/* $freqcust[$set]['frequency'] = $values['frequency_custom_text'];
							$freqcust[$set]['extra'] = 1;
							$freqcust[$set]['clientid'] = $this->logininfo->clientid;
							$freqarr[] = $lastid_freq+1; */
							$req_cust = Doctrine::getTable('MedicationFrequency')->find($values['frequency_custom']);
							if($req_cust)
							{
								$req_cust->isdelete = '1';
								$req_cust->save();
							}						
							
						    // MANUALLY add custom frequency
						    $new_frq_id = 0;
							$med_frq = new MedicationFrequency();
							$med_frq->frequency = $values['frequency_custom_text'];
							$med_frq->extra = 1;
							$med_frq->clientid = $this->logininfo->clientid;
							$med_frq->save();
							$new_frq_id = $med_frq->id;
							if(!empty($new_frq_id)){
	    						$freqarr[] = $new_frq_id;
							}
						}
						else
						{
							$req_cust = Doctrine::getTable('MedicationFrequency')->find($values['frequency_custom']);
							if($req_cust)
							{
								$req_cust->isdelete = '1';
								$req_cust->save();
							}
						}
						
						$medsetitems[$set]['frequency'] = ($freqarr) ? $freqarr : '';
						
	
						
						// MEDICATION DOSAGE [Darreichungsform]
						$meddosarr = array();
						$meddoscust[$set] = array();
						
						if(!empty($values['med_dosage_form'])){
						    $meddosarr = $values['med_dosage_form'];
						}
	// 					foreach($values['med_dosage_form'] as $kr=>$vr)
	// 					{
	// 						$meddosarr[] = $this->_medication_dosage[$vr]['id'];
	// 					}
	
						//ISPC-2554 pct.1 Carmen 07.04.2020
						if(!empty($values['med_dosage_form_mmi']))
						{
							foreach($values['med_dosage_form_mmi'] as $kr => $vr)
							{
								if(substr($vr, 0, 3) == 'mmi')
								{
									$dosform = new MedicationDosageForm();
									$dosform->clientid = $this->logininfo->clientid;
									$dosform->isfrommmi = '1';
									$dosform->mmi_code = substr($vr, 4);
									$dosform->extra = '1';
									$dosform->dosage_form = $dosageformmmi[substr($vr, 4)]['dosageform_name'];
									$dosform->save();
								
									if($dosform->id)
									{
										$meddosarr[] = $dosform->id;
									}
								}
								else
								{
									$meddosarr[] = $vr;
								}
							}
						}
						//--
							
						if(isset($values['med_dosage_form_custom']) && !empty($values['med_dosage_form_custom_text']))
						{
							/* $meddoscust[$set]['dosage_form'] = $values['med_dosage_form_custom_text'];
							$meddoscust[$set]['extra'] = 1;
							$meddoscust[$set]['clientid'] = $this->logininfo->clientid;
							$meddosarr[] = $lastid_meddos+1; */
							$req_cust = Doctrine::getTable('MedicationDosageform')->find($values['med_dosage_form_custom']);
							if($req_cust)
							{
								$req_cust->isdelete = '1';
								$req_cust->save();
							}
							
						    // MANUALLY add med_dosage_form_custom
						    $new_df_id = 0;
							$med_df = new MedicationDosageform();
							$med_df->dosage_form = $values['med_dosage_form_custom_text'];
							$med_df->extra = 1;
							$med_df->clientid = $this->logininfo->clientid;
							$med_df->save();
							$new_df_id = $med_df->id;
							
							if(!empty($new_df_id)){
	    						$meddosarr[] = $new_df_id;
							}
							
						}
						else
						{
							$req_cust = Doctrine::getTable('MedicationDosageform')->find($values['med_dosage_form_custom']);
							if($req_cust)
							{
								$req_cust->isdelete = '1';
								$req_cust->save();
							}
						}
						
						$medsetitems[$set]['med_dosage_form'] = ($meddosarr) ? $meddosarr : '';
						
						
						// MEDICATION TYPES [Applikationsweg]
						$medtyparr = array();
						$medtypcust[$set] = array();
						
						if(!empty($values['type'])){
						    $medtyparr = $values['type'];
						}
	
						
						
						/* foreach($values['type'] as $kr=>$vr)
						{
							$medtyparr[] = $this->_medication_types[$vr]['id'];
						} */
						
						if(isset($values['type_custom']) && !empty($values['type_custom_text']))
						{
							/* $medtypcust[$set]['type'] = $values['type_custom_text'];
							$medtypcust[$set]['extra'] = 1;
							$medtypcust[$set]['clientid'] = $this->logininfo->clientid;
							$medtyparr[] = $lastid_medtyp+1; */
							$req_cust = Doctrine::getTable('MedicationType')->find($values['type_custom']);
							if($req_cust)
							{
								$req_cust->isdelete = '1';
								$req_cust->save();
							}
							
						    // MANUALLY add type_custom_text
						    $new_mt_id = 0;
						    $med_mt = new MedicationType();
						    $med_mt->type = $values['type_custom_text'];
						    $med_mt->extra = 1;
						    $med_mt->clientid = $this->logininfo->clientid;
						    $med_mt->save();
						    $new_mt_id = $med_mt->id;
						    
						    if(!empty($new_mt_id)){
						        $medtyparr[] = $new_mt_id;
						    }
						}
						else 
						{
							$req_cust = Doctrine::getTable('MedicationType')->find($values['type_custom']);
							if($req_cust)
							{
								$req_cust->isdelete = '1';
								$req_cust->save();
							}
						}
							
						$medsetitems[$set]['type'] = ($medtyparr) ? $medtyparr : '';
						
						$medsetitems[$set]['comments'] = ($values['comments']) ? $values['comments'] : '';
						//$kset++;
						
						//ISPC-2554 pct.3 Carmen 08.04.2020
						$medication_atc = (array)json_decode($values['atc']);
						
						$medsetitems[$set]['atc_code'] = $medication_atc['atc_code'];
						$medsetitems[$set]['atc_description'] = $medication_atc['atc_description'];
						$medsetitems[$set]['atc_groupe_code'] = $medication_atc['atc_groupe_code'];
						$medsetitems[$set]['atc_groupe_description'] = $medication_atc['atc_groupe_description'];
						//--
						$medsetitems[$set]['unit'] = $values['unit']; //ISPC-2554 Carmen 14.05.2020
					}
				}
				
				//exit;
				if (empty($medset->id) || empty($medsetitems)) {
					return; //nothing to save
				}
				
				
// 				dd($medsetitems );
// 				$records_freq =  new Doctrine_Collection('MedicationFrequency');
				
// 				$records_freq->synchronizeWithArray($freqcust);
				
// 				$records_freq->save();
				
// 				$records_meddos =  new Doctrine_Collection('MedicationDosageform');
				
// 				$records_meddos->synchronizeWithArray($meddoscust);
				
// 				$records_meddos->save();
				
// 				$records_type =  new Doctrine_Collection('MedicationType');
				
// 				$records_type->synchronizeWithArray($medtypcust);
				
// 				$records_type->save();
				//print_r($medsetitems);exit;
				$records =  new Doctrine_Collection('MedicationsSetsItems');
				
				$records->synchronizeWithArray($medsetitems);
				
				$records->save();
				
				//return $records;	
				return $medset->id;     // ISPC-2247 pct.1 Lore 06.05.2020
			}
			
		}
		
		return $medset->id;
	}
	
}