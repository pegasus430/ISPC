<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 21, 2018
 * Changes added by Carmen for ISPC-2470  // Maria:: Migration ISPC to CISPC 08.08.2020	
 */
class Application_Form_FormBlockInfusiontimes extends Pms_Form
{
    
    protected $_model = 'FormBlockInfusiontimes';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockInfusiontimes::TRIGGER_FORMID;
    private $triggerformname = FormBlockInfusiontimes::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockInfusiontimes::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    


    public function getColumnMapping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
            'canceled' => ['yes' =>  'Ja', 'no' =>  'Nein'],
             
        ];
    
    
        $values = FormBlockInfusiontimesTable::getInstance()->getEnumValues($fieldName);
    
         
        $values = array_combine($values, array_map("self::translate", $values));
    
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
    
        return $values;
    
    }

    
    
    
	public function create_form_infusiontimes ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_infusiontimes");
	
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend('Infusiontimes');
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    
	    $subform->addElement('hidden', 'id', array(
	        'label'        => null,
	        'value'        => ! empty($values['id']) ? $values['id'] : '',
	        'required'     => false,
	        'readonly'     => true,
	        'filters'      => array('StringTrim'),
	        'decorators' => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 2,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'class'    => 'dontPrint',
	            )),
	        ),
	    ));
	    
	    
	
// 	    $subform->addElement('note', 'startNote', array(
// 	        'value'        => $this->translate('start'),
	    
// 	        'decorators'   => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array(
// 	                'tag' => 'td',
// 	            )),
// 	            array(array('row' => 'HtmlTag'), array(
// 	                'tag' => 'tr',
// 	                'openOnly' => true,
// 	            )),
// 	        ),
// 	    ));
// 	    $subform->addElement('note', 'endNote', array(
// 	        'value'        => $this->translate('end'),
// 	        'decorators'   => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array(
// 	                'tag' => 'td',
// 	            )),
// 	            array(array('row' => 'HtmlTag'), array(
// 	                'tag' => 'tr',
// 	                'closeOnly' => true,
// 	            )),
// 	        ),
// 	    ));
	    
	    
	    $subform->addElement('text', 'start', array(
	        'value'        => ! empty($values['start']) ? $values['start'] : null,
	        'label'        => 'start',
	        'placeholder'  => '__:__',
	         
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array(
	                'class'    => 'label_same_size_80',
	            )),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'openOnly' => true,
	            )),
	        ),
	        'class' => 'timepicker',
	    ));
	    
	    $subform->addElement('text', 'end', array(
	        'value'        => ! empty($values['end']) ? $values['end'] : null,	  
	        'label'        => 'end',       
	        'placeholder'  => '__:__',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array(
	                'class'    => 'label_same_size_80',
	            )),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
	        'class' => 'timepicker',
	    ));
	    
	    
	    
	    

// 	    $subform->addElement('note', 'pausedFromNote', array(
// 	        'value'        => $this->translate('paused from'),
	         
// 	        'decorators'   => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array(
// 	                'tag' => 'td',
// 	            )),
// 	            array(array('row' => 'HtmlTag'), array(
// 	                'tag' => 'tr',
// 	                'openOnly' => true,
// 	            )),
// 	        ),
// 	    ));
// 	    $subform->addElement('note', 'pausedTillNote', array(
// 	        'value'        => $this->translate('paused till'),
// 	        'decorators'   => array(
// 	            'ViewHelper',
// 	            array(array('data' => 'HtmlTag'), array(
// 	                'tag' => 'td',
// 	            )),
// 	            array(array('row' => 'HtmlTag'), array(
// 	                'tag' => 'tr',
// 	                'closeOnly' => true,
// 	            )),
// 	        ),
// 	    ));
	    
	    $subform->addElement('text', 'paused_from', array(
	        'value'        => ! empty($values['paused_from']) ? $values['paused_from'] : null,
	        'label' => 'paused from',
	        'placeholder'  => '__:__',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array(
	                'class'    => 'label_same_size_80',
	            )),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'openOnly' => true,
	            )),
	        ),
	        'class' => 'timepicker',
	    ));
	    $subform->addElement('text', 'paused_till', array(
	        'value'        => ! empty($values['paused_till']) ? $values['paused_till'] : null,
	        'label'    => 'paused till',
	        'placeholder'  => '__:__',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array('Label', array(
	                'class'    => 'label_same_size_80',
	            )),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'closeOnly' => true,
	            )),
	        ),
	        'class' => 'timepicker',
	    ));
	    
	    
	    $subform->addElement('text', 'reason_interruption', array(
	        'value'        => ! empty($values['reason_interruption']) ? $values['reason_interruption'] : null,
	         
	        'label'        => 'Reason for interruption',
	         
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'colspan'  => 1,
	                'class'    => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass' =>'cell_on_newline',
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	    ));
	    
	    $subform->addElement('radio', 'canceled', array(
	        'value'        => ! empty($values['canceled']) ? $values['canceled'] : null,
	        'multiOptions' => $this->getColumnMapping('canceled'),
// 	        'separator'    => '',
	        'label'        => 'canceled',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 1,
	                'class' =>'cell_on_newline cbrdList',
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tagClass' =>'cell_on_newline',
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        
	        'labelClass' => "",
	        'labelUnwrapp' => true,
	        'labelPlacement' => 'append',
	        
	        'onChange' => "if (this.value == 'yes') { $(this).parents('table').find('.selector_reason_demolition').show();} else { $(this).parents('table').find('.selector_reason_demolition').hide();};",
	         
	        
	    ));
	    
	    
	    $display = $values['canceled'] == "yes" ? "" : 'display:none';
	    
	    $subform->addElement('text', 'reason_demolition', array(
	        'value'        => ! empty($values['reason_demolition']) ? $values['reason_demolition'] : null,
	    
	        'label'        => 'Reason for demolition',
	    
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 1,
	                'class' => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass' => 'cell_on_newline'
	 
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'style'    => $display,
	                'class'    => 'selector_reason_demolition'
	            )),
	        ),
	    ));
	    
	    /*
	    $subform->addElement('text', 'infusion_rate', array(
	        'value'        => ! empty($values['infusion_rate']) ? $values['infusion_rate'] : null,
	    
	        'label'        => 'Infusion Rate',
	    
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3,
	            )),
	            array('Label', array(
	                'tag' => 'td',
	 
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        'data-inputmask'   => "'mask': '9', 'repeat': 4, 'greedy': false",
	        'pattern'          => "[0-9]*",
	    ));
	    */
	    
	    
	    $subformInfusionRate = new Zend_Form_SubForm();
	    $subformInfusionRate->clearDecorators()
	    ->setDecorators( array(
	        'FormElements',
	        array(array('data' => 'HtmlTag'), array(
	            'tag' => 'table',
	            'class' => 'SimpleTable'
	        )),
	        array(array('rowTD' => 'HtmlTag'), array(
	            'tag'      => 'td',
	            'colspan'  => 2
	        )),
	        array(array('rowTR' => 'HtmlTag'), array(
	            'tag'      => 'tr',
	        )),
	        
	    ));

	    
        $inlineJS = <<<EOT
        function calc_infusiontimes_dosage_rate(that) 
        {
            try{
                //that is this input
                var _tr = $(that).parents('tr').get(0);
                
                var dosage_ml = $("input[name*='\[dosage_ml\]']", _tr).val();
                var dosage_time = $("input[name*='\[dosage_time]'\]", _tr).val();
                var dosage_rate = '';
                
                if ( ! isNaN(dosage_ml) && ! isNaN(dosage_time) && dosage_time>0) {
                    dosage_rate = parseFloat(dosage_ml / dosage_time * 60);
    	        }
               
                $("input[name*='\[dosage_rate]'\]", _tr).val(dosage_rate);
                
                
                var _table = $(that).parents('table').get(0);
                
                var _sum = 0;
                $("input[name*='\[dosage_time]'\]", _table).each(function() {
                    _sum += Number($(this).val());
                });
               
                $('.selector_infusion_ttime_e').val(_sum);
        		$('.selector_infusion_ttime').val((isNaN(parseInt($('.selector_infusion_ttime_t').val())) ? 0 : parseInt($('.selector_infusion_ttime_t').val())) + _sum);
            } catch (e) {
                console.log(e);
	        }
            
    	}
EOT;
        
        $this->getView()->headScript()->appendScript($inlineJS, $type = 'text/javascript', $attrs = array());
	    
	   
//         $fmtNo = numfmt_create( 'de_DE', NumberFormatter::DECIMAL );
        
        $subformInfusionRate->addElement('note', 'headlineInfoenzyme', array(
        		'value'        => 'Infusionsschema Enzym',
        		'decorators'   => array(
        				'ViewHelper',
        				array(array('data' => 'HtmlTag'), array(
        						'tag' => 'td', 'style' => 'width: 100%; font-weight: bold; font-seize: 14px;', 'colspan' => '3'
        				)),
        				array(array('row' => 'HtmlTag'), array(
        						'tag'      => 'tr',
        				)),
        		),
        ));
        
        $subformInfusionRate->addElement('note', 'NoteInfoMl', array(
            'value'        => $this->translate('Dosage(ml)'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                    'openOnly' => true,
                    'class' => 'notes_InfusionRate'
                )),
            ),
        ));
        $subformInfusionRate->addElement('note', 'NoteInfoMin', array(
            'value'        => $this->translate('over') . ' ' . $this->translate('min'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
            ),
        ));
        
        $subformInfusionRate->addElement('note', 'noteInfoRate', array(
            'value'        => $this->translate('ml/h'),
            'decorators'   => array(
                'ViewHelper',
                array(array('data' => 'HtmlTag'), array(
                    'tag' => 'td',
                )),
                array(array('row' => 'HtmlTag'), array(
                    'tag'      => 'tr',
                    'closeOnly' => true
                )),
            ),
        ));
        
        
	    for($i=0; $i<8; $i++)
	    {
	        
	        $extra_row = $this->subFormTableRow(['class' => ""]);
	        if($_COOKIE['mobile_ver'] == 'yes')
	        {
	        	$extra_row->addElement('text', 'dosage_ml', array(
	        			'value'        => isset($values['infusion_rate'][$i]['dosage_ml']) ? $values['infusion_rate'][$i]['dosage_ml'] : null,
	        			'placeholder'  => 'ml',
	        			'required'     => false,
	        			'filters'      => array('StringTrim'),
	        			'validators'   => array('NotEmpty'),
	        			'decorators'   => array(
	        					'ViewHelper',
	        					array(array('data' => 'HtmlTag'), array(
	        							'tag'      => 'td',
	        					)),
	        			),
	        	
	        			'data-inputmask'   => "'alias':'numeric', 'suffix':' ml' , 'prefix': '', 'radixPoint': '.', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        			'pattern'          => "^[0-9.]*( ml)$",
	        	
	        	
	        	
	        			'onChange' => "calc_infusiontimes_dosage_rate(this);",
	        	));
	        }
	        else 
	        {
		        $extra_row->addElement('text', 'dosage_ml', array(
		            'value'        => isset($values['infusion_rate'][$i]['dosage_ml']) ? $values['infusion_rate'][$i]['dosage_ml'] : null,
		            'placeholder'  => 'ml',
		            'required'     => false,
		            'filters'      => array('StringTrim'),
		            'validators'   => array('NotEmpty'),
		            'decorators'   => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array(
		                    'tag'      => 'td',
		                )),
		            ),
		            
		            'data-inputmask'   => "'alias':'numeric', 'suffix':' ml' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
		            'pattern'          => "^[0-9,]*( ml)$",
		            
		            
		            
		            'onChange' => "calc_infusiontimes_dosage_rate(this);",
		        ));
	        }
	        
	        if($_COOKIE['mobile_ver'] == 'yes')
	        {
	        	$extra_row->addElement('text', 'dosage_time', array(
	        			'value'        => isset($values['infusion_rate'][$i]['dosage_time']) ? $values['infusion_rate'][$i]['dosage_time'] : null,
	        			'placeholder'  => 'min',
	        			'required'     => false,
	        			'filters'      => array('StringTrim'),
	        			'validators'   => array('NotEmpty'),
	        			'decorators'   => array(
	        					'ViewHelper',
	        					array(array('data' => 'HtmlTag'), array(
	        							'tag'      => 'td',
	        					)),
	        			),
	        	
	        			'data-inputmask'   => "'alias':'numeric', 'suffix':' min' , 'prefix': '', 'radixPoint': '.', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        			'pattern'          => "^[0-9]*( min)$",
	        	
	        			'onChange' => "calc_infusiontimes_dosage_rate(this);",
	        	));
	        }
	        else 
	        {
		        $extra_row->addElement('text', 'dosage_time', array(
		            'value'        => isset($values['infusion_rate'][$i]['dosage_time']) ? $values['infusion_rate'][$i]['dosage_time'] : null,
		            'placeholder'  => 'min',
		            'required'     => false,
		            'filters'      => array('StringTrim'),
		            'validators'   => array('NotEmpty'),
		            'decorators'   => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array(
		                    'tag'      => 'td',
		                )),
		            ),
		            
		            'data-inputmask'   => "'alias':'numeric', 'suffix':' min' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",             
		            'pattern'          => "^[0-9]*( min)$",
		            
		            'onChange' => "calc_infusiontimes_dosage_rate(this);",
		        ));
	        }
	        
	        if($_COOKIE['mobile_ver'] == 'yes')
	        {
	        	$extra_row->addElement('text', 'dosage_rate', array(
	        			// 	            'value'        => ! empty ($values['infusion_rate'][$i]['dosage_time']) && ! empty($values['infusion_rate'][$i]['dosage_ml']) ? round(numfmt_parse($fmtNo, $values['infusion_rate'][$i]['dosage_ml']) / $values['infusion_rate'][$i]['dosage_time'] * 60 , 2)  : null,
	        			//   'value'        => ! empty ($values['infusion_rate'][$i]['dosage_time']) && ! empty($values['infusion_rate'][$i]['dosage_ml']) ? round(floatval(str_replace(',', '.', str_replace('.', '', $values['infusion_rate'][$i]['dosage_ml']))) / $values['infusion_rate'][$i]['dosage_time'] * 60 , 2)  : null,
	        			'value'	=> isset($values['infusion_rate'][$i]['dosage_rate']) ? $values['infusion_rate'][$i]['dosage_rate'] : null,
	        			'placeholder'  => '',
	        			'required'     => false,
	        			'filters'      => array('StringTrim'),
	        			'validators'   => array('NotEmpty'),
	        			'decorators'   => array(
	        					'ViewHelper',
	        					array(array('data' => 'HtmlTag'), array(
	        							'tag'      => 'td',
	        					)),
	        			),
	        			'readonly'         => true,
	        			'data-inputmask'   => "'alias':'numeric', 'suffix':' ml/h' , 'prefix': '', 'radixPoint': '.', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        			 
	        	
	        	));
	        }
	        else 
	        {
		        $extra_row->addElement('text', 'dosage_rate', array(
	// 	            'value'        => ! empty ($values['infusion_rate'][$i]['dosage_time']) && ! empty($values['infusion_rate'][$i]['dosage_ml']) ? round(numfmt_parse($fmtNo, $values['infusion_rate'][$i]['dosage_ml']) / $values['infusion_rate'][$i]['dosage_time'] * 60 , 2)  : null,
		         //   'value'        => ! empty ($values['infusion_rate'][$i]['dosage_time']) && ! empty($values['infusion_rate'][$i]['dosage_ml']) ? round(floatval(str_replace(',', '.', str_replace('.', '', $values['infusion_rate'][$i]['dosage_ml']))) / $values['infusion_rate'][$i]['dosage_time'] * 60 , 2)  : null,
		        	'value'	=> isset($values['infusion_rate'][$i]['dosage_rate']) ? $values['infusion_rate'][$i]['dosage_rate'] : null,
		            'placeholder'  => '',
		            'required'     => false,
		            'filters'      => array('StringTrim'),
		            'validators'   => array('NotEmpty'),
		            'decorators'   => array(
		                'ViewHelper',
		                array(array('data' => 'HtmlTag'), array(
		                    'tag'      => 'td',
		                )),
		            ),
		            'readonly'         => true,
		            'data-inputmask'   => "'alias':'numeric', 'suffix':' ml/h' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
		             
		            
		        ));
	        }
	        
	        
	        
	        
	        $subformInfusionRate->addSubForm($extra_row, $i);
	    }
	    
	    $subformInfusionRate->addElement('text', 'infusion_ttime_e', array(
	    		'value'        => ! empty($values['infusion_rate']['infusion_ttime_e']) ? $values['infusion_rate']['infusion_ttime_e'] : null,
	    	  
	    		'label'        => 'Infusionsdauer(Enzym;min)',
	    	  
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass' => 'cell_on_newline',
	    
	    				)),
	    				array(array('row' => 'HtmlTag'), array(
	    						'tag' => 'tr',
	    				)),
	    		),
	    		'class'    => 'selector_infusion_ttime_e',
	    		 
	    		'data-inputmask'   => "'alias':'integer'",
	    
	    ));
	    
	    $subform->addSubForm($subformInfusionRate, 'infusion_rate');
	    
	    
	    
	    $inlineJS = <<<EOT
        function calc_infusiontimes_dosage_rate_t(that)
        {
            try{
                //that is this input
                var _tr = $(that).parents('tr').get(0);
	    
                var dosage_ml_t = $("input[name*='\[dosage_ml_t\]']", _tr).val();
                var dosage_time_t = $("input[name*='\[dosage_time_t]'\]", _tr).val();
                var dosage_rate_t = '';
	    
                if ( ! isNaN(dosage_ml_t) && ! isNaN(dosage_time_t) && dosage_time_t>0) {
                    dosage_rate_t = parseFloat(dosage_ml_t / dosage_time_t * 60);
    	        }
        
                $("input[name*='\[dosage_rate_t]'\]", _tr).val(dosage_rate_t);
	    
	    
                var _table = $(that).parents('table').get(0);
	    
                var _sum_t = 0;
                $("input[name*='\[dosage_time_t]'\]", _table).each(function() {
                    _sum_t += Number($(this).val());
                });
	    
                $('.selector_infusion_ttime_t').val(_sum_t);
	    		$('.selector_infusion_ttime').val((isNaN(parseInt($('.selector_infusion_ttime_e').val())) ? 0 : parseInt($('.selector_infusion_ttime_e').val())) + _sum_t);
            } catch (e) {
                console.log(e);
	        }
	    
    	}
EOT;
	    
	    $this->getView()->headScript()->appendScript($inlineJS, $type = 'text/javascript', $attrs = array());
	     
	    
	    //         $fmtNo = numfmt_create( 'de_DE', NumberFormatter::DECIMAL );
	    
	    $subformInfusionRate->addElement('note', 'headlineInfotrailing', array(
	    		'value'        => 'Infusionsschema Nachlauf',
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td', 'style' => 'width: 100%; font-weight: bold; font-seize: 14px;', 'colspan' => '3'
	    				)),
	    				array(array('row' => 'HtmlTag'), array(
	    						'tag'      => 'tr',
	    				)),
	    		),
	    ));
	    
	    $subformInfusionRate->addElement('note', 'NoteInfoMlt', array(
	    		'value'        => $this->translate('Dosage(ml)'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    				)),
	    				array(array('row' => 'HtmlTag'), array(
	    						'tag'      => 'tr',
	    						'openOnly' => true,
	    						'class' => 'notes_InfusionRate'
	    				)),
	    		),
	    ));
	    $subformInfusionRate->addElement('note', 'NoteInfoMint', array(
	    		'value'        => $this->translate('over') . ' ' . $this->translate('min'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    				)),
	    		),
	    ));
	    
	    $subformInfusionRate->addElement('note', 'noteInfoRatet', array(
	    		'value'        => $this->translate('ml/h'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    				)),
	    				array(array('row' => 'HtmlTag'), array(
	    						'tag'      => 'tr',
	    						'closeOnly' => true
	    				)),
	    		),
	    ));
	    
	    
	    for($i=8; $i<9; $i++)
	    {
	    	 
	    	$extra_row = $this->subFormTableRow(['class' => ""]);
	    	if($_COOKIE['mobile_ver'] == 'yes')
	    	{
	    		$extra_row->addElement('text', 'dosage_ml_t', array(
	    				'value'        => isset($values['infusion_rate'][$i]['dosage_ml_t']) ? $values['infusion_rate'][$i]['dosage_ml_t'] : null,
	    				'placeholder'  => 'ml',
	    				'required'     => false,
	    				'filters'      => array('StringTrim'),
	    				'validators'   => array('NotEmpty'),
	    				'decorators'   => array(
	    						'ViewHelper',
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag'      => 'td',
	    						)),
	    				),
	    		
	    				'data-inputmask'   => "'alias':'numeric', 'suffix':' ml' , 'prefix': '', 'radixPoint': '.', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    				'pattern'          => "^[0-9.]*( ml)$",
	    		
	    		
	    		
	    				'onChange' => "calc_infusiontimes_dosage_rate_t(this);",
	    		));
	    	}
	    	else
	    	{
		    	$extra_row->addElement('text', 'dosage_ml_t', array(
		    			'value'        => isset($values['infusion_rate'][$i]['dosage_ml_t']) ? $values['infusion_rate'][$i]['dosage_ml_t'] : null,
		    			'placeholder'  => 'ml',
		    			'required'     => false,
		    			'filters'      => array('StringTrim'),
		    			'validators'   => array('NotEmpty'),
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td',
		    					)),
		    			),
		    			 
		    			'data-inputmask'   => "'alias':'numeric', 'suffix':' ml' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
		    			'pattern'          => "^[0-9,]*( ml)$",
		    			 
		    			 
		    			 
		    			'onChange' => "calc_infusiontimes_dosage_rate_t(this);",
		    	));
	    	}
	    	if($_COOKIE['mobile_ver'] == 'yes')
	    	{
	    		$extra_row->addElement('text', 'dosage_time_t', array(
	    				'value'        => isset($values['infusion_rate'][$i]['dosage_time_t']) ? $values['infusion_rate'][$i]['dosage_time_t'] : null,
	    				'placeholder'  => 'min',
	    				'required'     => false,
	    				'filters'      => array('StringTrim'),
	    				'validators'   => array('NotEmpty'),
	    				'decorators'   => array(
	    						'ViewHelper',
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag'      => 'td',
	    						)),
	    				),
	    		
	    				'data-inputmask'   => "'alias':'numeric', 'suffix':' min' , 'prefix': '', 'radixPoint': '.', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    				'pattern'          => "^[0-9]*( min)$",
	    		
	    				'onChange' => "calc_infusiontimes_dosage_rate_t(this);",
	    		));
	    	}
	    	else
	    	{
		    	$extra_row->addElement('text', 'dosage_time_t', array(
		    			'value'        => isset($values['infusion_rate'][$i]['dosage_time_t']) ? $values['infusion_rate'][$i]['dosage_time_t'] : null,
		    			'placeholder'  => 'min',
		    			'required'     => false,
		    			'filters'      => array('StringTrim'),
		    			'validators'   => array('NotEmpty'),
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td',
		    					)),
		    			),
		    			 
		    			'data-inputmask'   => "'alias':'numeric', 'suffix':' min' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
		    			'pattern'          => "^[0-9]*( min)$",
		    			 
		    			'onChange' => "calc_infusiontimes_dosage_rate_t(this);",
		    	));
	    	}
	    	 
	    	if($_COOKIE['mobile_ver'] == 'yes')
	    	{
	    		$extra_row->addElement('text', 'dosage_rate_t', array(
	    				// 	            'value'        => ! empty ($values['infusion_rate'][$i]['dosage_time']) && ! empty($values['infusion_rate'][$i]['dosage_ml']) ? round(numfmt_parse($fmtNo, $values['infusion_rate'][$i]['dosage_ml']) / $values['infusion_rate'][$i]['dosage_time'] * 60 , 2)  : null,
	    				'value'	=> isset($values['infusion_rate'][$i]['dosage_rate_t']) ? $values['infusion_rate'][$i]['dosage_rate_t'] : null,
	    				'placeholder'  => '',
	    				'required'     => false,
	    				'filters'      => array('StringTrim'),
	    				'validators'   => array('NotEmpty'),
	    				'decorators'   => array(
	    						'ViewHelper',
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag'      => 'td',
	    						)),
	    				),
	    				'readonly'         => true,
	    				'data-inputmask'   => "'alias':'numeric', 'suffix':' ml/h' , 'prefix': '', 'radixPoint': '.', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		
	    		
	    		));
	    	}
	    	else
	    	{
		    	$extra_row->addElement('text', 'dosage_rate_t', array(
		    	// 	            'value'        => ! empty ($values['infusion_rate'][$i]['dosage_time']) && ! empty($values['infusion_rate'][$i]['dosage_ml']) ? round(numfmt_parse($fmtNo, $values['infusion_rate'][$i]['dosage_ml']) / $values['infusion_rate'][$i]['dosage_time'] * 60 , 2)  : null,
		    			'value'	=> isset($values['infusion_rate'][$i]['dosage_rate_t']) ? $values['infusion_rate'][$i]['dosage_rate_t'] : null,
		    			'placeholder'  => '',
		    			'required'     => false,
		    			'filters'      => array('StringTrim'),
		    			'validators'   => array('NotEmpty'),
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td',
		    					)),
		    			),
		    			'readonly'         => true,
		    			'data-inputmask'   => "'alias':'numeric', 'suffix':' ml/h' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
		    
		    			 
		    	));
	    	}
	    	 
	    	 
	    	 
	    	 
	    	$subformInfusionRate->addSubForm($extra_row, $i);
	    }
	     
	    $subformInfusionRate->addElement('hidden', 'infusion_ttime_t', array(
	    		'value'        => ! empty($values['infusion_ttime_t']) ? $values['infusion_ttime_t'] : null,
	    
	    		//'label'        => 'Total infusion time',
	    
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass' => 'cell_on_newline',
	    						 
	    				)),
	    				array(array('row' => 'HtmlTag'), array(
	    						'tag' => 'tr', 'class' =>  ' display_none'
	    				)),
	    		),
	    		'class'    => 'selector_infusion_ttime_t',
	    
	    		'data-inputmask'   => "'alias':'integer'",
	    	  
	    ));
	     
	    $subform->addSubForm($subformInfusionRate, 'infusion_rate');
	     
	     
	     
	     
	    $subform->addElement('text', 'infusion_ttime', array(
	    		'value'        => ! empty($values['infusion_ttime']) ? $values['infusion_ttime'] : null,
	    	  
	    		'label'        => 'Gesamtinfusionsdauer(Enzym + Nachlauf;min)',
	    	  
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass' => 'cell_on_newline',
	    
	    				)),
	    				array(array('row' => 'HtmlTag'), array(
	    						'tag' => 'tr',
	    				)),
	    		),
	    		'class'    => 'selector_infusion_ttime',
	    		 
	    		'data-inputmask'   => "'alias':'integer'",
	    
	    ));
	    
	    
	    $subform->addElement('textarea', 'reason_failure', array(
	        'value'        => ! empty($values['reason_failure']) ? $values['reason_failure'] : null,
	        
	        'label'        => 'Reason for failure or time shift',
	        
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 1,	   
	                'class' => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tabClass' => 'cell_on_newline',
	                
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        'rows' => 3,
	        'cols' => 60
	    ));
	
	
	    
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	
	
	
	
	
	
	
	
	public function save_form_infusiontimes ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_infusiontimes_copy_old_if_not_allowed($ipid , $data);
	     
	    //create patientcourse
	    $this->__save_form_infusiontimes_patient_course($ipid , $data);
	     
	    //set the old block values as isdelete
	    $this->__save_form_infusiontimes_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	    $entity = FormBlockInfusiontimesTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    //print_R($entity); exit;
	    return $entity;
	}

	
	/**
	 * !! $data used by reference
	 *
	 * copy-paste the old saved values of the block, when this user has no access to this block
	 *
	 * @param string $ipid
	 * @param array $data
	 */
	private function __save_form_infusiontimes_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || in_array('infusiontimes', $data['__formular']['allowed_blocks'])
	    )
	    {
	        return;
	    }
	
	
	    $oldValues = FormBlockInfusiontimesTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	
	        unset($oldValues[FormBlockInfusiontimesTable::getInstance()->getIdentifier()]);
	
	        $data = array_merge($data, $oldValues);
	    }
	
	}
	
	/**
	 * write or erase the patientcourse text
	 *
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_infusiontimes_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || ! in_array('infusiontimes', $data['__formular']['allowed_blocks']))
	    {
	        return;
	    }
	
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	
	    if ( ! in_array('infusiontimes', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	
	
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_infusiontimes_patient_course_format($data);
	
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	
	        $oldValues = FormBlockInfusiontimesTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	        if (empty($oldValues)) {
	
	            //missing previous values, so we save
	            $save_2_PC = true ;
	
	        } else {
	
	            $course_arr_OLD =  $this->__save_form_infusiontimes_patient_course_format($oldValues);
	
	            if ($course_arr_OLD === $course_arr) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	
	        }
	
	    }
	
	
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockInfusiontimesTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
	    {
	        $course_str =  implode(PHP_EOL, $course_arr);
	        $pc_listener->setOption('disabled', false);
	        $pc_listener->setOption('course_title', $course_str);
	        $pc_listener->setOption('done_date', date('Y-m-d H:i:s', strtotime($data['__formular']['date'] . ' ' . $data['__formular']['begin_date_h'] . ':' . $data['__formular']['begin_date_m'] . ':00' )));
	        $pc_listener->setOption('user_id', $this->logininfo->userid);
	
	    } elseif ($save_2_PC
	        && empty($course_arr)
	        && ! empty($formular['old_contact_form_id']))
	    {
	        //must manualy remove from PC this option
	        $pc_entity = new PatientCourse();
	        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockInfusiontimes::PATIENT_COURSE_TABNAME);
	
	    }
	
	}
	
	
	/**
	 * format the patientcourse title message
	 *
	 * @param unknown $data
	 * @return multitype:string
	 */
	private function __save_form_infusiontimes_patient_course_format($data = [])
	{
	    $course_arr = [];
	    
	    if ( ! empty($data['start']) || ! empty($data['end']))
	    {
	        $course_arr[] =
	        ( ! empty($data['start']) ? $this->translate('start') . " " . $data['start'] . ' ' . $this->translate('hours_label')  : '') .
	        ( ! empty($data['end']) ? ' ' .$this->translate('end') . " " . $data['end'] . ' ' . $this->translate('hours_label')  : '')
	        ;
	    }
	     
	    if ( ! empty($data['paused_from']) || ! empty($data['paused_till']))
	    {
	        $course_arr[] =
	        ( ! empty($data['paused_from']) ? $this->translate('paused from') . " " . $data['paused_from'] . ' ' . $this->translate('hours_label')   : '') .
	        ( ! empty($data['paused_till']) ? ' ' . $this->translate('paused till') . ": " . $data['paused_till'] . ' ' . $this->translate('hours_label')  : '')
	        ;
	    }
	     
	    if ( ! empty($data['reason_interruption'])) {
	        $course_arr[] = $this->translate('Reason for interruption') . ": " . $data['reason_interruption'];
	    }
	     
	    if ( ! empty($data['canceled'])) {
	        $course_arr[] = $this->translate('canceled') . ": " . ($data['canceled'] == "yes" ? "Ja" : "Nein");
	    }
	     
	    if ( ! empty($data['reason_demolition'])) {
	        $course_arr[] = $this->translate('Reason for demolition') . ": " . $data['reason_demolition'];
	    }
	     
	    if ( ! empty($data['infusion_rate'])) {
// 	        $course_arr[] = $this->translate('Infusion Rate') . ": " . $data['infusion_rate'];
            // ISPC-2470 Ancuta 06.11.2019
	        $str = "";
	        foreach($data['infusion_rate'] as $line=>$vals){
	            if(!empty($vals['dosage_ml']) || !empty($vals['dosage_time']) || !empty($vals['dosage_rate']) ){
	                $str .= $vals['dosage_ml']."ml | ".$vals['dosage_time'].'min | '.$vals['dosage_rate']."ml/h \n";  
	            }
	        }
	        if(strlen($str) > 0){
    	        $course_arr[] = $this->translate('Infusion Rate') . ": " . $str;
	        }
	    }
	    if ( ! empty($data['infusion_ttime'])) {
	        $course_arr[] = $this->translate('Total infusion time') . ": " . $data['infusion_ttime'];
	    }
	    if ( ! empty($data['reason_failure'])) {
	        $course_arr[] = $this->translate('Reason for failure or time shift') . ": " . $data['reason_failure'];
	    }
	     
	    
	    return $course_arr;
	}
	
	/**
	 * set isdelete = 1 for the old block
	 *
	 * @param string $ipid
	 * @param number $contact_form_id
	 * @return boolean
	 */
	private function __save_form_infusiontimes_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockInfusiontimesTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	
	        return true;
	    }
	}
	
	
	
	
	
	
	
}