<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 21, 2018
 * Changes added by Carmen for ISPC-2470   // Maria:: Migration ISPC to CISPC 08.08.2020	
 */
class Application_Form_FormBlockInfusion extends Pms_Form
{
    
    protected $_model = 'FormBlockInfusion';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockInfusion::TRIGGER_FORMID;
    private $triggerformname = FormBlockInfusion::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockInfusion::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    //TODO-3235 Carmen 02.07.2020
    protected $_block_preparation_in_pharmacy_unit = null; 
    
    public function __construct($options = null)
    {
    	$this->_block_preparation_in_pharmacy_unit = array('mg' => 'MG', 'ie' => 'Einheiten');
    	
    	parent::__construct($options);
    
    }

	//--
    public function getColumnMapping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
            'preparation_in_pharmacy' => ['yes' =>  'Ja', 'no' =>  'Nein'],
    
        ];
    
    
        $values = FormBlockInfusionTable::getInstance()->getEnumValues($fieldName);
    
         
        $values = array_combine($values, array_map("self::translate", $values));
    
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
    
        return $values;
    
    }
    
    
    
    
	public function create_form_infusion ($values =  array() , $elementsBelongTo = null)
	{
// 	    dd($values);
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_infusion");
	
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend('Infusion');
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
	                'colspan' => 4, //TODO-3235
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'class'    => 'dontPrint',
	            )),
	        ),
	    ));
	
	    $subform->addElement('text', 'drug_name', array(
	        'value'        => ! empty($values['drug_name']) ? $values['drug_name'] : null,
	        'label'        => 'Drug Name',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3, //TODO-3235
	                'class' => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first'
	        
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	    ));
	    
	    
	    $subform->addElement('radio', 'preparation_in_pharmacy', array(
	        'value'        => ! empty($values['preparation_in_pharmacy']) ? $values['preparation_in_pharmacy'] : null,
	        'multiOptions' => $this->getColumnMapping('preparation_in_pharmacy'),
// 	        'separator'    => '',
	        'label'        => 'Preparation in pharmacy',
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3,//TODO-3235
	                'class'    => 'cbrdList',
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tagClass'=>'print_column_first'
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        

	        'labelClass' => "",
	        'labelUnwrapp' => true,
	        'labelPlacement' => 'append',
	         
	        
	        //'onChange' => "if (this.value == 'yes') { $('.rowselector_yes input').attr('disabled', false); $(this).parents('table').find('.selector_preparation_in_pharmacy_no').addClass('display_none'); $(this).parents('table').find('.selector_preparation_in_pharmacy_yes').removeClass('display_none');  $(this).parents('table').find('.selector_extra_rows_ampoules').removeAttr('style').addClass('display_none'); $(this).parents('table').find('.selector_extra_rows_ampoules input').attr('disabled', 'disabled'); $(this).parents('table').find('.selector_preparation_in_pharmacy_no.rowselector_no input').attr('disabled', 'disabled'); $('.selector_ampoules').val('');} else { $('.rowselector_no input').attr('disabled', false); $(this).parents('table').find('.selector_preparation_in_pharmacy_yes').addClass('display_none'); $(this).parents('table').find('.selector_preparation_in_pharmacy_no').removeClass('display_none'); $(this).parents('table').find('.selector_extra_rows_ampoules').removeAttr('style').addClass('display_none'); $(this).parents('table').find('.selector_extra_rows_ampoules input').attr('disabled', 'disabled'); $(this).parents('table').find('.selector_preparation_in_pharmacy_yes.rowselector_yes input').attr('disabled', 'disabled'); $('.selector_ampoules').val('');};",
	    	'onChange'	=> "if (this.value == 'yes') {
	    			$('.rowselector_yes input').attr('disabled', false);
	    			$(this).parents('table').find('.selector_preparation_in_pharmacy_no').addClass('display_none');
	    			$(this).parents('table').find('.selector_preparation_in_pharmacy_yes').removeClass('display_none');
	    			$(this).parents('table').find('.selector_extra_rows_ampoules').removeAttr('style').addClass('display_none');
	    			$(this).parents('table').find('.selector_extra_rows_ampoules input').attr('disabled', 'disabled');
	    		
	    			$(this).parents('table').find('.selector_preparation_in_pharmacy_no.rowselector_no input').attr('disabled', 'disabled');
	    		
	    			$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());
		            
           			 $('.selector_ampoules').val('');
	    		
			    	}
			    	else {
			            $('.rowselector_no input').attr('disabled', false);
			            $(this).parents('table').find('.selector_preparation_in_pharmacy_yes').addClass('display_none');
			            $(this).parents('table').find('.selector_preparation_in_pharmacy_no').removeClass('display_none');
			            $(this).parents('table').find('.selector_extra_rows_ampoules').removeAttr('style').addClass('display_none');
			            $(this).parents('table').find('.selector_extra_rows_ampoules input').attr('disabled', 'disabled');
	    			
			            $(this).parents('table').find('.selector_preparation_in_pharmacy_yes.rowselector_yes input').attr('disabled', 'disabled');
	    				$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());
				    			
				    			$('.selector_ampoules').val('');
				    		};",
	    ));
	    
	   	//$display_none = $values['preparation_in_pharmacy'] != '' ? '' : 'display_none';
	    
	    /* $subform->addElement('text', 'batch_number', array(
	        'value'        => ! empty($values['batch_number']) ? $values['batch_number'] : null,
	        'label'        => 'Batchalert( _extra_form); number ready bag',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'colspan'  => 2,	
	                'class' => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag'      => 'td', 
	                'tagClass' =>'print_column_first',
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'class'    => "selector_preparation_in_pharmacy_yes {$display_none}",
	            )),
	        ),
	    )); */
	
	    $subformAmpoulesExtra = new Zend_Form_SubForm();
	    $subformAmpoulesExtra->setDecorators(array('FormElements'));
	    
	    if($values['preparation_in_pharmacy'] == 'yes')
	    {
	    $extra_form = 'yes';
	    $cnt_row = 0;
	    $display_none = '';
	    
	    $row_head = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_" . $extra_form . " rowselector_" . $extra_form . " {$display_none}",]);
	   //TODO-3235 Carmen 02.07.2020
	    $row_head->addElement('select', 'preparation_in_pharmacy_unit', array(
	    		//'label' 	   => self::translate('positioning_type'),
	    		'multiOptions' => $this->_block_preparation_in_pharmacy_unit,
	    		'value'        => isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'decorators' =>   array(
	    				'ViewHelper',
			            array(array('data' => 'HtmlTag'), array(
			                'tag' => 'td',
			                'colspan' => 1,
			            	'align' => 'right','class'    => 'dontPrint',  'style' => 'width: 200px;'
			            )),
	    		),
	    		'onChange'	=> "if($(this).closest('tr').prev().find('td input:radio').is(':checked')) {
	    						var radioval = $(this).closest('tr').prev().find('td :radio:checked').val();
	    		$(this).closest('td').next().find('label').text($('option:selected',this).text()+':');	    		
	    						$('#FormBlockInfusion-ampoules_extra-'+radioval+'-mg').attr(\"placeholder\", $(this).val());
	    		var selectval = $('option:selected',this).val();
	    		$(this).closest('td').next().next().find('input').attr(\"data-inputmask\", \"'alias':'numeric', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true, 'suffix' : ' \"+selectval+\"' \" );
				$(this).closest('td').next().next().find('input').inputmask();
	    		}"
	    ));
	    
	    $row_head->addElement('text', 'mg', array(
	        'value'        => isset($values['ampoules_extra'][$extra_form]['mg']) ? ($values['ampoules_sufix'] ? $values['ampoules_extra'][$extra_form]['mg'].' ' .(isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : ' mg') : $values['ampoules_extra'][$extra_form]['mg']) : ($values['ampoules_extra'] ? (isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : ' mg') : null),
	        'label'        => $this->_block_preparation_in_pharmacy_unit[isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg'].":",
	        'placeholder'  => isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 1,
	            	'align' => 'right'
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'align_right', 'class'    => 'dontPrint',
	            )),
	            /* array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'openOnly' => true,
	                'class'    => "selector_preparation_in_pharmacy_no {$display_none}",
	            )), */
	        ),
	        

	        'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':' ". (isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg') ."' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        'pattern'          => "^[0-9,]*( ". (isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg') .")$",
	        
	    ));
	    //--
	    $row_head->addElement('text', 'ampoules', array(
	        'value'        => isset($values['ampoules_extra'][$extra_form]['ampoules']) ? ($values['ampoules_sufix'] ? $values['ampoules_extra'][$extra_form]['ampoules'].' Amp.Anzahl' : $values['ampoules_extra'][$extra_form]['ampoules']) : ($values['ampoules_sufix'] ? 'Amp.Anzahl' : null),
	    	'label'        => 'Anzahl Chargen:',
	        //'placeholder'  => $this->translate('Ampoules (quantity)'),
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	        		array('Label', array(
	        				'tag' => 'div',
	        				'tagClass'=>'align_right labeldiv dontPrint', 'class'    => 'dontPrint'
	        		)),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 2,//TODO-3235
	            	'align' => 'right'
	            )),
	        		
	            /* array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'closeOnly' => true,
	            )), */
	        ),
	        
	        //'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules (quantity)') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    	'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        //'pattern'          => "^[0-9\s]*( " . $this->getView()->escape($this->translate('Ampoules (quantity)')) . ")$",
	    		'pattern'          => "^[0-9\s]*$",
	        
	        'class'    => 'selector_ampoules',
	    	'data-extraform' => $extra_form,
	    ));
	    
	    $this->__setElementsBelongTo($row_head,  $extra_form);
	    $subformAmpoulesExtra->addSubForm($row_head,  'row_head'.$cnt_row++);

    	//add one single row with empty values so we have what to clone
    	$values['ampoules_extra']['extra_no'] = [0 => []];//the empty one is used as a clone
	    
    	$display_none = '';
	    if (empty($values['ampoules_extra'][$extra_form]['ampoules']) || empty($values['ampoules_extra']['extra_'.$extra_form])) {
	        
	        $display_none = 'display_none';
	        $values['ampoules_extra']['extra_yes'] = [0 => []];//the empty one is used as a clone
	    }

	    $cnt_extra_rows = 0;
	    
	    foreach ($values['ampoules_extra']['extra_'.$extra_form] as $one_ampoule_extra) 
	    {   
	        if (isset($values['ampoules_extra'][$extra_form]['ampoules']) && $cnt_extra_rows >= $values['ampoules_extra'][$extra_form]['ampoules'] && !empty($values['ampoules_extra'][$extra_form]['ampoules'])) {
	            break;
	        }
	        $extra_row = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_" . $extra_form . " {$display_none} selector_extra_rows_ampoules",]);
	        //TODO-3235 Carmen 02.07.2020
	        $extra_row->addElement('note', 'col_sep1', array(
	        		'value'            => '',
	        		'decorators'       => array(
	        				'ViewHelper',
	        				array(array('data' => 'HtmlTag'), array(
	        						'tag' => 'td', 'class' => 'dontPrint'
	        				)),
	        		),
	        ));
	        //--
	        $extra_row->addElement('text', 'batch_number', array(
	            'value'        => isset($values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['batch_number']) ? $values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['batch_number'] : null,
 	            'label'        => 'Chargen Nr:',
	            //'placeholder'  => $this->translate('Batch number'),
	            'required'     => false,
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	                array(array('data' => 'HtmlTag'), array(
	                    'tag'      => 'td',
	                    'colspan'  => '1',
	                	'align' => 'right'
	                )),
	                array('Label', array(
	                    'tag'      => 'td',
	                    'tagClass' =>'align_right', 'class'    => ' ',
	                )),
	            ),
	            'class' => 'align_right'
	            //'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Batch number') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':32, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	            //'pattern'          => "^[0-9]*( " . $this->translate('Batch number') . ")$",
	             
	            
	        ));
	         
	        
	        
	        $extra_row->addElement('text', 'ampoules', array(
	            'value'        => isset($values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['ampoules']) ? ($values['ampoules_sufix'] ? $values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['ampoules'].' Amp' : $values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['ampoules']) : null,
 	            'label'        => 'Amp',
	            //'placeholder'  => $this->translate('Ampoules'),
	            'required'     => false,
	            'filters'      => array('StringTrim'),
	            'validators'   => array('NotEmpty'),
	            'decorators'   => array(
	                'ViewHelper',
	            		array('Label', array(
	            				'tag'      => 'div',
	            				'tagClass' =>'align_right labeldiv dontPrint', 'class'    => 'dontPrint', 'style' => 'display: inline-block',
	            		)),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag'      => 'td',
	                    'colspan'  => 2,//TODO-3235
	                	'align' => 'right'
	                )),
	            		
	            ),
	            
	            //'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        		'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	            //'pattern'          => "^[0-9]*( " . $this->translate('Ampoules') . ")$",
	        		'pattern'          => "^[0-9]*$",
	             
	            
	            'class'            => 'selector_ampoules_extra',
	        ));
	        
	        $this->__setElementsBelongTo($extra_row,  "extra_" . $extra_form . "[{$cnt_extra_rows}]");
	        $subformAmpoulesExtra->addSubForm($extra_row, $extra_form . "_{$cnt_extra_rows}");
	        $cnt_extra_rows++;  
	        
	    }
	    $display_none = '';
	    $row_volum = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_" . $extra_form . " rowselector_" . $extra_form . " {$display_none}",]);
	    
	    $row_volum->addElement('text', 'volum_reconstituted', array(
	        'value'        => isset($values['ampoules_extra'][$extra_form]['volum_reconstituted']) ? $values['ampoules_extra'][$extra_form]['volum_reconstituted'] : null,
	        'label'        => 'Volume reconstituted drug (ml)',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3,//TODO-3235
	                'class' => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first',
	  
	            )),
	           /*  array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'class'    => "selector_preparation_in_pharmacy_" . $extra_form . " {$display_none}",
	            )), */
	        ),
	        'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        'pattern'          => "^[0-9,]*$",
	        'class'    => 'selector_toaddsum',
	        'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    )); 
	    
	    $this->__setElementsBelongTo($row_volum,  $extra_form);
	    $subformAmpoulesExtra->addSubForm($row_volum,  'row_volum'.$cnt_row++);
	    
	    $row_carrier = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_" . $extra_form . " rowselector_" . $extra_form . " {$display_none}",]);
	    
	    $row_carrier->addElement('text', 'carrier_solution', array(
	        'value'        => isset($values['ampoules_extra'][$extra_form]['carrier_solution']) ? $values['ampoules_extra'][$extra_form]['carrier_solution'] : null,
	        'label'        => 'Carrier solution (ml)',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3,//TODO-3235
	                'class' => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first',
	            )),
	           /*  array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	                'class'    => "selector_preparation_in_pharmacy_" . $extra_form. " {$display_none}",
	            )), */
	        ),
	        'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        'pattern'          => "^[0-9,]*$",
	        'class'    => 'selector_toaddsum',
	        'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    ));
	    
	    $this->__setElementsBelongTo($row_carrier,  $extra_form);
	    $subformAmpoulesExtra->addSubForm($row_carrier,  'row_carrier'.$cnt_row++);
	    
	    $display_none = 'display_none';
	    
	    $row_head = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_no rowselector_no {$display_none}",]);
	    //TODO-3235 Carmen 02.07.2020 
	    $row_head->addElement('select', 'preparation_in_pharmacy_unit', array(
	    		//'label' 	   => self::translate('positioning_type'),
	    		'multiOptions' => $this->_block_preparation_in_pharmacy_unit,
	    		'value'        => isset($values['ampoules_extra']['no']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['no']['preparation_in_pharmacy_unit'] : 'mg',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'decorators' =>   array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    						'align' => 'right','class'    => 'dontPrint', 'style' => 'width: 200px;'
	    				)),
	    		),
	    		'onChange'	=> "if($(this).closest('tr').prevUntil(':visible').prev().find('td input:radio').is(':checked')) {
	    						var radioval = $(this).closest('tr').prevUntil(':visible').prev().find('td :radio:checked').val();
	    		$(this).closest('td').next().find('label').text($('option:selected',this).text()+':');
	    						$('#FormBlockInfusion-ampoules_extra-'+radioval+'-mg').attr(\"placeholder\", $(this).val());
	    		var selectval = $('option:selected',this).val();
	    		$(this).closest('td').next().next().find('input').attr(\"data-inputmask\", \"'alias':'numeric', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true, 'suffix' : ' \"+selectval+\"' \" );
				$(this).closest('td').next().next().find('input').inputmask();
	    		}"
	    ));
	     
	    $row_head->addElement('text', 'mg', array(
	    		'value'        => isset($values['ampoules_extra']['no']['mg']) ? $values['ampoules_extra']['no']['mg'] : null,
	    		'label'        => $this->_block_preparation_in_pharmacy_unit[isset($values['ampoules_extra']['no']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['no']['preparation_in_pharmacy_unit'] : 'mg'].":",
	    		'placeholder'  => isset($values['ampoules_extra']['no']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['no']['preparation_in_pharmacy_unit'] : 'mg',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'align_right', 'class'    => 'dontPrint',
	    				)),
	    				/* array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'openOnly' => true,
	    						'class'    => "selector_preparation_in_pharmacy_no {$display_none}",
	    				)), */
	    		),
	    		 
	    
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':' ". (isset($values['ampoules_extra']['no']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['no']['preparation_in_pharmacy_unit'] : 'mg') ."', 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*(  ". (isset($values['ampoules_extra']['no']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['no']['preparation_in_pharmacy_unit'] : 'mg') .")$",
	    		 
	    ));
	    //-- 
	    $row_head->addElement('text', 'ampoules', array(
	    		'value'        => isset($values['ampoules_extra']['no']['ampoules']) ? $values['ampoules_extra']['no']['ampoules'] : null,
	    		'label'        => 'Anzahl Chargen:',
	    		//'placeholder'  => $this->translate('Ampoules (quantity)'),
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array('Label', array(
	    						'tag' => 'div',
	    						'tagClass'=>'align_right labeldiv', 'class'    => 'dontPrint',
	    				)),
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 2,//TODO-3235
	    				)),
	    				
	    				/* array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'closeOnly' => true,
	    				)), */
	    		),
	    		 
	    		//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules (quantity)') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		//'pattern'          => "^[0-9\s]*( " . $this->getView()->escape($this->translate('Ampoules (quantity)')) . ")$",
	    		'pattern'          => "^[0-9\s]*$",
	    		 
	    		'class'    => 'selector_ampoules',
	    		'data-extraform' => "no",
	    ));
	     
	    $this->__setElementsBelongTo($row_head,  'no');
	    $subformAmpoulesExtra->addSubForm($row_head,  'row_head'.$cnt_row++);
	    $cnt_extra_rows = 0;
	    // print_r($values); exit;
	    foreach ($values['ampoules_extra']['extra_no'] as $one_ampoule_extra)
	    {
	    	/* if (isset($values['ampoules_extra'][$extra_form]['ampoules']) && $cnt_extra_rows >= $values['ampoules_extra'][$extra_form]['ampoules']) {
	    		break;
	    	} */
	    	$extra_row = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_no" . " {$display_none} selector_extra_rows_ampoules",]);
	    	
	    	//TODO-3235 Carmen 02.07.2020
	    	$extra_row->addElement('note', 'col_sep1', array(
	    			'value'            => '',
	    			'decorators'       => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td', 'class' => 'dontPrint'
	    					)),
	    			),
	    	));
	    	//--
	    	
	    	$extra_row->addElement('text', 'batch_number', array(
	    			'value'        => isset($values['ampoules_extra']['extra_no'][$cnt_extra_rows]['batch_number']) ? $values['ampoules_extra']['extra_no'][$cnt_extra_rows]['batch_number'] : null,
	    			'label'        => 'Chargen Nr:',
	    			//'placeholder'  => $this->translate('Batch number'),
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag'      => 'td',
	    							'colspan'  => '1',
	    					)),
	    					array('Label', array(
	    							'tag'      => 'td',
	    							'tagClass' =>'align_right', 'class'    => '',
	    					)),
	    			),
	    			 
	    			//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Batch number') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':32, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			//'pattern'          => "^[0-9]*( " . $this->translate('Batch number') . ")$",
	    
	    			 
	    	));
	    
	    	 
	    	 
	    	$extra_row->addElement('text', 'ampoules', array(
	    			'value'        => isset($values['ampoules_extra']['extra_no'][$cnt_extra_rows]['ampoules']) ? $values['ampoules_extra']['extra_no'][$cnt_extra_rows]['ampoules'] : null,
	    			'label'        => 'Amp',
	    			//'placeholder'  => $this->translate('Ampoules'),
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array('Label', array(
	    							'tag'      => 'div',
	    							'tagClass' =>'align_right labeldiv', 'class'    => 'dontPrint',
	    					)),
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag'      => 'td',
	    							'colspan'  => 2,//TODO-3235
	    					)),
	    					
	    			),
	    			 
	    			//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			//'pattern'          => "^[0-9]*( " . $this->translate('Ampoules') . ")$",
	    			'pattern'          => "^[0-9]*$",
	    
	    			 
	    			'class'            => 'selector_ampoules_extra',
	    	));
	    	 
	    	$this->__setElementsBelongTo($extra_row,  "extra_no[{$cnt_extra_rows}]");
	    	$subformAmpoulesExtra->addSubForm($extra_row, "no_{$cnt_extra_rows}");
	    	$cnt_extra_rows++;
	    	 
	    }
	    $row_volum = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_no rowselector_no {$display_none}",]);
	     
	    $row_volum->addElement('text', 'volum_reconstituted', array(
	    		'value'        => isset($values['ampoules_extra']['no']['volum_reconstituted']) ? $values['ampoules_extra']['no']['volum_reconstituted'] : null,
	    		'label'        => 'Volume reconstituted drug (ml)',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 3,//TODO-3235
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'print_column_first',
	    						 
	    				)),
	    				/*  array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'class'    => "selector_preparation_in_pharmacy_" . $extra_form . " {$display_none}",
	    				)), */
	    		),
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*$",
	    		'class'    => 'selector_toaddsum',
	    		'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    ));
	     
	    $this->__setElementsBelongTo($row_volum,  'no');
	    $subformAmpoulesExtra->addSubForm($row_volum,  'row_volum'.$cnt_row++);
	     
	    $row_carrier = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_no rowselector_no {$display_none}",]);
	     
	    $row_carrier->addElement('text', 'carrier_solution', array(
	    		'value'        => isset($values['ampoules_extra']['no']['carrier_solution']) ? $values['ampoules_extra']['no']['carrier_solution'] : null,
	    		'label'        => 'Carrier solution (ml)',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 3,//TODO-3235
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'print_column_first',
	    				)),
	    				/*  array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'class'    => "selector_preparation_in_pharmacy_" . $extra_form. " {$display_none}",
	    				)), */
	    		),
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*$",
	    		'class'    => 'selector_toaddsum',
	    		'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    ));
	     
	    $this->__setElementsBelongTo($row_carrier,  'no');
	    $subformAmpoulesExtra->addSubForm($row_carrier,  'row_carrier'.$cnt_row++);
	    }
	    else if($values['preparation_in_pharmacy'] == 'no')
	    {
	    $extra_form = 'no';
	    $cnt_row = 0;
	    $display_none = '';
	     
	    $row_head = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_" . $extra_form . " rowselector_" . $extra_form . " {$display_none}",]);
	    //TODO-3235 Carmen 02.07.2020 
	    $row_head->addElement('select', 'preparation_in_pharmacy_unit', array(
	    		//'label' 	   => self::translate('positioning_type'),
	    		'multiOptions' => $this->_block_preparation_in_pharmacy_unit,
	    		'value'        => isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'decorators' =>   array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    						'align' => 'right','class'    => 'dontPrint', 'style' => 'width: 200px;'
	    				)),
	    		),
	    		'onChange'	=> "if($(this).closest('tr').prev().find('td input:radio').is(':checked')) {
	    						var radioval = $(this).closest('tr').prev().find('td :radio:checked').val();
	    		$(this).closest('td').next().find('label').text($('option:selected',this).text()+':');
	    						$('#FormBlockInfusion-ampoules_extra-'+radioval+'-mg').attr(\"placeholder\", $(this).val());
	    		var selectval = $('option:selected',this).val();
	    		$(this).closest('td').next().next().find('input').attr(\"data-inputmask\", \"'alias':'numeric', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true, 'suffix' : ' \"+selectval+\"' \" );
				$(this).closest('td').next().next().find('input').inputmask();
	    		}"
	    ));
	     
	    $row_head->addElement('text', 'mg', array(
	    		'value'        => isset($values['ampoules_extra'][$extra_form]['mg']) ? ($values['ampoules_sufix'] ? $values['ampoules_extra'][$extra_form]['mg'].' '.(isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : ' mg')  : $values['ampoules_extra'][$extra_form]['mg']) : ($values['ampoules_extra'] ? (isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : ' mg') : null),
	    		'label'        => $this->_block_preparation_in_pharmacy_unit[isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg'].":",
	    		'placeholder'  => isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    						'align' => 'right'
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'align_right', 'class'    => 'dontPrint',
	    				)),
	    				/* array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'openOnly' => true,
	    						'class'    => "selector_preparation_in_pharmacy_no {$display_none}",
	    				)), */
	    		),
	    		 
	    
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':' ". (isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg') ."' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*(  ". (isset($values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit']) ? $values['ampoules_extra'][$extra_form]['preparation_in_pharmacy_unit'] : 'mg') .")$",
	    		 
	    ));
	    //-- 
	    $row_head->addElement('text', 'ampoules', array(
	    		'value'        => isset($values['ampoules_extra'][$extra_form]['ampoules']) ? ($values['ampoules_sufix'] ? $values['ampoules_extra'][$extra_form]['ampoules'].' Amp.Anzahl' : $values['ampoules_extra'][$extra_form]['ampoules']) : ($values['ampoules_sufix'] ? 'Amp.Anzahl' : null),
	    		'label'        => 'Anzahl Chargen:',
	    		//'placeholder'  => $this->translate('Ampoules (quantity)'),
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array('Label', array(
	    						'tag' => 'div',
	    						'tagClass'=>'align_right labeldiv dontPrint', 'class'    => 'dontPrint',
	    				)),
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 2,//TODO-3235
	    						'align' => 'right'
	    				)),
	    				
	    				/* array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'closeOnly' => true,
	    				)), */
	    		),
	    		 
	    		//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules (quantity)') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		//'pattern'          => "^[0-9\s]*( " . $this->getView()->escape($this->translate('Ampoules (quantity)')) . ")$",
	    		'pattern'          => "^[0-9\s]*$",
	    		 
	    		'class'    => 'selector_ampoules',
	    		'data-extraform' => $extra_form,
	    ));
	     
	    $this->__setElementsBelongTo($row_head,  $extra_form);
	    $subformAmpoulesExtra->addSubForm($row_head,  'row_head'.$cnt_row++);

    	//add one single row with empty values so we have what to clone
	    $values['ampoules_extra']['extra_yes'] = [0 => []];//the empty one is used as a clone
	   
	    $display_none = '';
	    if (empty($values['ampoules_extra'][$extra_form]['ampoules']) || empty($values['ampoules_extra']['extra_'.$extra_form])) {
	    	 
	    	$display_none = 'display_none';
	    	$values['ampoules_extra']['extra_no'] = [0 => []];//the empty one is used as a clone
	    }
	    
	    $cnt_extra_rows = 0;
	    // print_r($values); exit;
	    foreach ($values['ampoules_extra']['extra_'.$extra_form] as $one_ampoule_extra)
	    {
	    	if (isset($values['ampoules_extra'][$extra_form]['ampoules']) && $cnt_extra_rows >= $values['ampoules_extra'][$extra_form]['ampoules'] && !empty($values['ampoules_extra'][$extra_form]['ampoules'])) {
	    		break;
	    	}
	    	$extra_row = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_" . $extra_form . " {$display_none} selector_extra_rows_ampoules",]);
	    	 
	    	//TODO-3235 Carmen 02.07.2020
	    	$extra_row->addElement('note', 'col_sep1', array(
	    			'value'            => '',
	    			'decorators'       => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td', 'class' => 'dontPrint'
	    					)),
	    			),
	    	));
	    	//--
	    	
	    	$extra_row->addElement('text', 'batch_number', array(
	    			'value'        => isset($values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['batch_number']) ? $values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['batch_number'] : null,
	    			'label'        => 'Chargen Nr:',
	    			//'placeholder'  => $this->translate('Batch number'),
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag'      => 'td',
	    							'colspan'  => '1',
	    							'align' => 'right'
	    					)),
	    					array('Label', array(
	    							'tag'      => 'td',
	    							'tagClass' =>'align_right', 'class'    => '',
	    					)),
	    			),
	    			'class' => 'align_right'
	    			//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Batch number') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':32, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			//'pattern'          => "^[0-9]*( " . $this->translate('Batch number') . ")$",
	    
	    			 
	    	));
	    
	    	 
	    	 
	    	$extra_row->addElement('text', 'ampoules', array(
	    			'value'        => isset($values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['ampoules']) ? ($values['ampoules_sufix'] ? $values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['ampoules'].' Amp' : $values['ampoules_extra']['extra_'.$extra_form][$cnt_extra_rows]['ampoules']) : null,
	    			'label'        => 'Amp',
	    			//'placeholder'  => $this->translate('Ampoules'),
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array('Label', array(
	    							'tag'      => 'div',
	    							'tagClass' =>'align_right labeldiv dontPrint', 'class'    => 'dontPrint',
	    					)),
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag'      => 'td',
	    							'colspan'  => 2,//TODO-3235
	    							'align' => 'right'
	    					)),
	    					
	    			),
	    			 
	    			//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			//'pattern'          => "^[0-9]*( " . $this->translate('Ampoules') . ")$",
	    			'pattern'          => "^[0-9]*$",
	    
	    			 
	    			'class'            => 'selector_ampoules_extra',
	    	));
	    	 
	    	$this->__setElementsBelongTo($extra_row,  "extra_" . $extra_form . "[{$cnt_extra_rows}]");
	    	$subformAmpoulesExtra->addSubForm($extra_row, $extra_form . "_{$cnt_extra_rows}");
	    	$cnt_extra_rows++;
	    	 
	    }
	    $display_none = '';
	    $row_volum = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_" . $extra_form . " rowselector_" . $extra_form . " {$display_none}",]);
	     
	    $row_volum->addElement('text', 'volum_reconstituted', array(
	    		'value'        => isset($values['ampoules_extra'][$extra_form]['volum_reconstituted']) ? $values['ampoules_extra'][$extra_form]['volum_reconstituted'] : null,
	    		'label'        => 'Volume reconstituted drug (ml)',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 3,//TODO-3235
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'print_column_first',
	    						 
	    				)),
	    				/*  array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'class'    => "selector_preparation_in_pharmacy_" . $extra_form . " {$display_none}",
	    				)), */
	    		),
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*$",
	    		'class'    => 'selector_toaddsum',
	    		'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    ));
	     
	    $this->__setElementsBelongTo($row_volum,  $extra_form);
	    $subformAmpoulesExtra->addSubForm($row_volum,  'row_volum'.$cnt_row++);
	     
	    $row_carrier = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_" . $extra_form . " rowselector_" . $extra_form . " {$display_none}",]);
	     
	    $row_carrier->addElement('text', 'carrier_solution', array(
	    		'value'        => isset($values['ampoules_extra'][$extra_form]['carrier_solution']) ? $values['ampoules_extra'][$extra_form]['carrier_solution'] : null,
	    		'label'        => 'Carrier solution (ml)',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 3,//TODO-3235
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'print_column_first',
	    				)),
	    				/*  array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'class'    => "selector_preparation_in_pharmacy_" . $extra_form. " {$display_none}",
	    				)), */
	    		),
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*$",
	    		'class'    => 'selector_toaddsum',
	    		'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    ));
	     
	    $this->__setElementsBelongTo($row_carrier,  $extra_form);
	    $subformAmpoulesExtra->addSubForm($row_carrier,  'row_carrier'.$cnt_row++);
	     
	    $display_none = 'display_none';
	    
	    $row_head = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_yes rowselector_yes {$display_none}",]);
	    //TODO-3235 Carmen 02.07.2020
	    $row_head->addElement('select', 'preparation_in_pharmacy_unit', array(
	    		//'label' 	   => self::translate('positioning_type'),
	    		'multiOptions' => $this->_block_preparation_in_pharmacy_unit,
	    		'value'        => isset($values['ampoules_extra']['yes']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['yes']['preparation_in_pharmacy_unit'] : 'mg',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'decorators' =>   array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    						'align' => 'right','class'    => 'dontPrint', 'style' => 'width: 200px;'
	    				)),
	    		),
	    		'onChange'	=> "if($(this).closest('tr').prevUntil(':visible').prev().find('td input:radio').is(':checked')) {
	    						var radioval = $(this).closest('tr').prevUntil(':visible').prev().find('td :radio:checked').val();
	    		$(this).closest('td').next().find('label').text($('option:selected',this).text()+':');
	    						$('#FormBlockInfusion-ampoules_extra-'+radioval+'-mg').attr(\"placeholder\", $(this).val());
	    		var selectval = $('option:selected', this).val();
	    		$(this).closest('td').next().next().find('input').attr(\"data-inputmask\", \"'alias':'numeric', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true, 'suffix' : ' \"+selectval+\"' \" );
				$(this).closest('td').next().next().find('input').inputmask();
	    		}"
	    ));
	    
	    $row_head->addElement('text', 'mg', array(
	    		'value'        => isset($values['ampoules_extra']['yes']['mg']) ? $values['ampoules_extra']['yes']['mg'] : null,
	    		'label'        => $this->_block_preparation_in_pharmacy_unit[isset($values['ampoules_extra']['yes']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['yes']['preparation_in_pharmacy_unit'] : 'mg'].":",
	    		'placeholder'  => isset($values['ampoules_extra']['yes']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['yes']['preparation_in_pharmacy_unit'] : 'mg',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 1,
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'align_right', 'class'    => 'dontPrint',
	    				)),
	    				/* array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'openOnly' => true,
	    						'class'    => "selector_preparation_in_pharmacy_no {$display_none}",
	    				)), */
	    		),
	    
	    	  
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':' ". (isset($values['ampoules_extra']['yes']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['yes']['preparation_in_pharmacy_unit'] : 'mg') ."', 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*(  ". (isset($values['ampoules_extra']['yes']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['yes']['preparation_in_pharmacy_unit'] : 'mg') .")$",
	    
	    ));
	    //--
	    $row_head->addElement('text', 'ampoules', array(
	    		'value'        => isset($values['ampoules_extra']['yes']['ampoules']) ? $values['ampoules_extra']['yes']['ampoules'] : null,
	    		'label'        => 'Anzahl Chargen:',
	    		//'placeholder'  => $this->translate('Ampoules (quantity)'),
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array('Label', array(
	    						'tag' => 'div',
	    						'tagClass'=>'align_right labeldiv', 'class'    => 'dontPrint',
	    				)),
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 2,//TODO-3235
	    				)),
	    				
	    				/* array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'closeOnly' => true,
	    				)), */
	    		),
	    
	    		//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules (quantity)') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		//'pattern'          => "^[0-9\s]*( " . $this->getView()->escape($this->translate('Ampoules (quantity)')) . ")$",
	    		'pattern'          => "^[0-9\s]*$",
	    
	    		'class'    => 'selector_ampoules',
	    		'data-extraform' => 'yes',
	    ));
	    
	    $this->__setElementsBelongTo($row_head,  'yes');
	    $subformAmpoulesExtra->addSubForm($row_head,  'row_head'.$cnt_row++);
	    
	    $cnt_extra_rows = 0;
	    // print_r($values); exit;
	    foreach ($values['ampoules_extra']['extra_yes'] as $one_ampoule_extra)
	    {
	    	/* if (isset($values['ampoules_extra'][$extra_form]['ampoules']) && $cnt_extra_rows >= $values['ampoules_extra'][$extra_form]['ampoules']) {
	    	 break;
	    	 } */
	    	$extra_row = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_yes" . " {$display_none} selector_extra_rows_ampoules",]);
	    	 
	    	//TODO-3235 Carmen 02.07.2020
	    	$extra_row->addElement('note', 'col_sep1', array(
	    			'value'            => '',
	    			'decorators'       => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td', 'class' => 'dontPrint'
	    					)),
	    			),
	    	));
	    	//--
	    	
	    	$extra_row->addElement('text', 'batch_number', array(
	    			'value'        => isset($values['ampoules_extra']['extra_yes'][$cnt_extra_rows]['batch_number']) ? $values['ampoules_extra']['extra_yes'][$cnt_extra_rows]['batch_number'] : null,
	    			'label'        => 'Chargen Nr:',
	    			//'placeholder'  => $this->translate('Batch number'),
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag'      => 'td',
	    							'colspan'  => '1',
	    					)),
	    					array('Label', array(
	    							'tag'      => 'td',
	    							'tagClass' =>'align_right', 'class'    => '',
	    					)),
	    			),
	    			 
	    			//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Batch number') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':32, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			//'pattern'          => "^[0-9]*( " . $this->translate('Batch number') . ")$",
	    			 
	    			 
	    	));
	    	 
	    	 
	    	 
	    	$extra_row->addElement('text', 'ampoules', array(
	    			'value'        => isset($values['ampoules_extra']['extra_yes'][$cnt_extra_rows]['ampoules']) ? $values['ampoules_extra']['extra_yes'][$cnt_extra_rows]['ampoules'] : null,
	    			'label'        => 'Amp',
	    			//'placeholder'  => $this->translate('Ampoules'),
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array('Label', array(
	    							'tag'      => 'div',
	    							'tagClass' =>'align_right labeldiv', 'class'    => 'dontPrint',
	    					)),
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag'      => 'td',
	    							'colspan'  => 2,//TODO-3235
	    					)),
	    					
	    			),
	    			 
	    			//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			//'pattern'          => "^[0-9]*( " . $this->translate('Ampoules') . ")$",
	    			'pattern'          => "^[0-9]*$",
	    			 
	    			 
	    			'class'            => 'selector_ampoules_extra',
	    	));
	    	 
	    	$this->__setElementsBelongTo($extra_row,  "extra_yes[{$cnt_extra_rows}]");
	    	$subformAmpoulesExtra->addSubForm($extra_row, "yes_{$cnt_extra_rows}");
	    	$cnt_extra_rows++;
	    	 
	    }
	    $row_volum = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_yes rowselector_yes {$display_none}",]);
	    
	    $row_volum->addElement('text', 'volum_reconstituted', array(
	    		'value'        => isset($values['ampoules_extra']['yes']['volum_reconstituted']) ? $values['ampoules_extra']['yes']['volum_reconstituted'] : null,
	    		'label'        => 'Volume reconstituted drug (ml)',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 3,//TODO-3235
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'print_column_first',
	    
	    				)),
	    				/*  array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'class'    => "selector_preparation_in_pharmacy_" . $extra_form . " {$display_none}",
	    				)), */
	    		),
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*$",
	    		'class'    => 'selector_toaddsum',
	    		'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    ));
	    
	    $this->__setElementsBelongTo($row_volum,  'yes');
	    $subformAmpoulesExtra->addSubForm($row_volum,  'row_volum'.$cnt_row++);
	    
	    $row_carrier = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_yes rowselector_yes {$display_none}",]);
	    
	    $row_carrier->addElement('text', 'carrier_solution', array(
	    		'value'        => isset($values['ampoules_extra']['yes']['carrier_solution']) ? $values['ampoules_extra']['yes']['carrier_solution'] : null,
	    		'label'        => 'Carrier solution (ml)',
	    		'required'     => false,
	    		'filters'      => array('StringTrim'),
	    		'validators'   => array('NotEmpty'),
	    		'decorators'   => array(
	    				'ViewHelper',
	    				array(array('data' => 'HtmlTag'), array(
	    						'tag' => 'td',
	    						'colspan' => 3,//TODO-3235
	    						'class' => 'cell_on_newline',
	    				)),
	    				array('Label', array(
	    						'tag' => 'td',
	    						'tagClass'=>'print_column_first',
	    				)),
	    				/*  array(array('row' => 'HtmlTag'), array(
	    				 'tag'      => 'tr',
	    						'class'    => "selector_preparation_in_pharmacy_" . $extra_form. " {$display_none}",
	    				)), */
	    		),
	    		'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    		'pattern'          => "^[0-9,]*$",
	    		'class'    => 'selector_toaddsum',
	    		'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    ));
	    
	    $this->__setElementsBelongTo($row_carrier,  'yes');
	    $subformAmpoulesExtra->addSubForm($row_carrier,  'row_carrier'.$cnt_row++);
	    }
	    else 
	    {
	    	$cnt_row = 0;
	    	$display_none = 'display_none';
	    	 
	    	$row_head = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_yes rowselector_yes {$display_none}",]);
	    	//TODO-3235 Carmen 02.07.2020
	    	$row_head->addElement('select', 'preparation_in_pharmacy_unit', array(
	    			//'label' 	   => self::translate('positioning_type'),
	    			'multiOptions' => $this->_block_preparation_in_pharmacy_unit,
	    			'value'        => isset($values['ampoules_extra']['yes']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['yes']['preparation_in_pharmacy_unit'] : 'mg',
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'decorators' =>   array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 1,
	    							'align' => 'right','class'    => 'dontPrint', 'style' => 'width: 200px;',
	    					)),
	    			),
	    			'onChange'	=> "if($(this).closest('tr').prev().find('td input:radio').is(':checked')) {
	    						var radioval = $(this).closest('tr').prev().find('td :radio:checked').val();	    			
	    		$(this).closest('td').next().find('label').text($('option:selected',this).text()+':');
	    						$('#FormBlockInfusion-ampoules_extra-'+radioval+'-mg').attr(\"placeholder\", $(this).val());
	    			var selectval = $('option:selected',this).val();
	    		$(this).closest('td').next().next().find('input').attr(\"data-inputmask\", \"'alias':'numeric', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true, 'suffix' : ' \"+selectval+\"' \" );
				$(this).closest('td').next().next().find('input').inputmask();
	    		}"
	    	));
	    	 
	    	$row_head->addElement('text', 'mg', array(
	    			'value'        => isset($values['ampoules_extra']['yes']['mg']) ? $values['ampoules_extra']['yes']['mg'] : null,
	    			'label'        => $this->_block_preparation_in_pharmacy_unit[isset($values['ampoules_extra']['yes']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['yes']['preparation_in_pharmacy_unit'] : 'mg'].":",
	    			'placeholder'  => isset($values['ampoules_extra']['yes']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['yes']['preparation_in_pharmacy_unit'] : 'mg',
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 1,
	    					)),
	    					array('Label', array(
	    							'tag' => 'td',
	    							'tagClass'=>'align_right', 'class'    => 'dontPrint',
	    					)),
	    					/* array(array('row' => 'HtmlTag'), array(
	    					 'tag'      => 'tr',
	    							'openOnly' => true,
	    							'class'    => "selector_preparation_in_pharmacy_no {$display_none}",
	    					)), */
	    			),
	    			 
	    	
	    			'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix': ' mg' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'pattern'          => "^[0-9,]*( mg)$",
	    			 
	    	));
	    	//-- 
	    	$row_head->addElement('text', 'ampoules', array(
	    			'value'        => isset($values['ampoules_extra']['yes']['ampoules']) ? $values['ampoules_extra']['yes']['ampoules'] : null,
	    			'label'        => 'Anzahl Chargen:',
	    			//'placeholder'  => $this->translate('Ampoules (quantity)'),
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array('Label', array(
	    							'tag' => 'div',
	    							'tagClass'=>'align_right labeldiv', 'class'    => 'dontPrint',
	    					)),
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 2,//TODO-3235
	    					)),
	    					
	    					/* array(array('row' => 'HtmlTag'), array(
	    					 'tag'      => 'tr',
	    							'closeOnly' => true,
	    					)), */
	    			),
	    			 
	    			//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules (quantity)') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			//'pattern'          => "^[0-9\s]*( " . $this->getView()->escape($this->translate('Ampoules (quantity)')) . ")$",
	    			'pattern'          => "^[0-9\s]*$",
	    			 
	    			'class'    => 'selector_ampoules',
	    			'data-extraform' => 'yes',
	    	));
	    	 
	    	$this->__setElementsBelongTo($row_head,  'yes');
	    	$subformAmpoulesExtra->addSubForm($row_head,  'row_head'.$cnt_row++);
	    	
	    	//add one single row with empty values so we have what to clone
	    	$values['ampoules_extra']['extra_yes'] = [0 => []];//the empty one is used as a clone
	    	
	    	$cnt_extra_rows = 0;
	    	// print_r($values); exit;
	    	foreach ($values['ampoules_extra']['extra_yes'] as $one_ampoule_extra)
	    	{
	    		/* if (isset($values['ampoules_extra']['yes']['ampoules']) && $cnt_extra_rows >= $values['ampoules_extra'][$extra_form]['ampoules']) {
	    			break;
	    		} */
	    		$extra_row = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_yes {$display_none} selector_extra_rows_ampoules",]);
	    		 
	    		//TODO-3235 Carmen 02.07.2020
	    		$extra_row->addElement('note', 'col_sep1', array(
	    				'value'            => '',
	    				'decorators'       => array(
	    						'ViewHelper',
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag' => 'td', 'class' => 'dontPrint'
	    						)),
	    				),
	    		));
	    		//--
	    		
	    		$extra_row->addElement('text', 'batch_number', array(
	    				'value'        => isset($values['ampoules_extra']['extra_yes'][$cnt_extra_rows]['batch_number']) ? $values['ampoules_extra']['extra_yes'][$cnt_extra_rows]['batch_number'] : null,
	    				'label'        => 'Chargen Nr:',
	    				//'placeholder'  => $this->translate('Batch number'),
	    				'required'     => false,
	    				'filters'      => array('StringTrim'),
	    				'validators'   => array('NotEmpty'),
	    				'decorators'   => array(
	    						'ViewHelper',
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag'      => 'td',
	    								'colspan'  => '1',
	    						)),
	    						array('Label', array(
	    								'tag'      => 'td',
	    								'tagClass' =>'align_right', 'class'    => '',
	    						)),
	    				),
	    				 
	    				//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Batch number') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':32, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    				//'pattern'          => "^[0-9]*( " . $this->translate('Batch number') . ")$",
	    	
	    				 
	    		));
	    	
	    		 
	    		 
	    		$extra_row->addElement('text', 'ampoules', array(
	    				'value'        => isset($values['ampoules_extra']['extra_yes'][$cnt_extra_rows]['ampoules']) ? $values['ampoules_extra']['extra_yes'][$cnt_extra_rows]['ampoules'] : null,
	    				'label'        => 'Amp',
	    				//'placeholder'  => $this->translate('Ampoules'),
	    				'required'     => false,
	    				'filters'      => array('StringTrim'),
	    				'validators'   => array('NotEmpty'),
	    				'decorators'   => array(
	    						'ViewHelper',
	    						array('Label', array(
	    								'tag'      => 'div',
	    								'tagClass' =>'align_right labeldiv', 'class'    => 'dontPrint',
	    						)),
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag'      => 'td',
	    								'colspan'  => 2,//TODO-3235
	    						)),
	    						
	    				),
	    				 
	    				//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    				'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    				//'pattern'          => "^[0-9]*( " . $this->translate('Ampoules') . ")$",
	    				'pattern'          => "^[0-9]*$",
	    	
	    				 
	    				'class'            => 'selector_ampoules_extra',
	    		));
	    		 
	    		$this->__setElementsBelongTo($extra_row,  "extra_yes[{$cnt_extra_rows}]");
	    		$subformAmpoulesExtra->addSubForm($extra_row, "yes_{$cnt_extra_rows}");
	    		$cnt_extra_rows++;
	    		 
	    	}
	    	$row_volum = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_yes rowselector_yes {$display_none}",]);
	    	 
	    	$row_volum->addElement('text', 'volum_reconstituted', array(
	    			'value'        => isset($values['ampoules_extra']['yes']['volum_reconstituted']) ? $values['ampoules_extra']['yes']['volum_reconstituted'] : null,
	    			'label'        => 'Volume reconstituted drug (ml)',
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 3,//TODO-3235
	    							'class' => 'cell_on_newline',
	    					)),
	    					array('Label', array(
	    							'tag' => 'td',
	    							'tagClass'=>'print_column_first',
	    							 
	    					)),
	    					/*  array(array('row' => 'HtmlTag'), array(
	    					 'tag'      => 'tr',
	    							'class'    => "selector_preparation_in_pharmacy_" . $extra_form . " {$display_none}",
	    					)), */
	    			),
	    			'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'pattern'          => "^[0-9,]*$",
	    			'class'    => 'selector_toaddsum',
	    			'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    	));
	    	 
	    	$this->__setElementsBelongTo($row_volum,  'yes');
	    	$subformAmpoulesExtra->addSubForm($row_volum,  'row_volum'.$cnt_row++);
	    	 
	    	$row_carrier = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_yes rowselector_yes {$display_none}",]);
	    	 
	    	$row_carrier->addElement('text', 'carrier_solution', array(
	    			'value'        => isset($values['ampoules_extra']['yes']['carrier_solution']) ? $values['ampoules_extra']['yes']['carrier_solution'] : null,
	    			'label'        => 'Carrier solution (ml)',
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 3,//TODO-3235
	    							'class' => 'cell_on_newline',
	    					)),
	    					array('Label', array(
	    							'tag' => 'td',
	    							'tagClass'=>'print_column_first',
	    					)),
	    					/*  array(array('row' => 'HtmlTag'), array(
	    					 'tag'      => 'tr',
	    							'class'    => "selector_preparation_in_pharmacy_" . $extra_form. " {$display_none}",
	    					)), */
	    			),
	    			'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'pattern'          => "^[0-9,]*$",
	    			'class'    => 'selector_toaddsum',
	    			'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    	));
	    	 
	    	$this->__setElementsBelongTo($row_carrier,  'yes');
	    	$subformAmpoulesExtra->addSubForm($row_carrier,  'row_carrier'.$cnt_row++);
	    	 
	    	$row_head = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_no rowselector_no {$display_none}",]);
	    	//TODO-3235 Carmen 02.07.2020
	    	$row_head->addElement('select', 'preparation_in_pharmacy_unit', array(
	    			//'label' 	   => self::translate('positioning_type'),
	    			'multiOptions' => $this->_block_preparation_in_pharmacy_unit,
	    			'value'        => isset($values['ampoules_extra']['no']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['no']['preparation_in_pharmacy_unit'] : 'mg',
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'decorators' =>   array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 1,
	    							'align' => 'right','class'    => 'dontPrint', 'style' => 'width: 200px;'
	    					)),
	    			),
	    			'onChange'	=> "if($(this).closest('tr').prevUntil(':visible').prev().find('td input:radio').is(':checked')) {
	    						var radioval = $(this).closest('tr').prevUntil(':visible').prev().find('td :radio:checked').val();	    			
	    		$(this).closest('td').next().find('label').text($('option:selected',this).text()+':');
	    						$('#FormBlockInfusion-ampoules_extra-'+radioval+'-mg').attr(\"placeholder\", $(this).val());
	    			var selectval = $('option:selected',this).val();
	    		$(this).closest('td').next().next().find('input').attr(\"data-inputmask\", \"'alias':'numeric', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true, 'suffix' : ' \"+selectval+\"' \" );
				$(this).closest('td').next().next().find('input').inputmask();
	    		}"
	    	));
	    	
	    	$row_head->addElement('text', 'mg', array(
	    			'value'        => isset($values['ampoules_extra']['no']['mg']) ? $values['ampoules_extra']['no']['mg'] : null,
	    			'label'        => $this->_block_preparation_in_pharmacy_unit[isset($values['ampoules_extra']['no']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['no']['preparation_in_pharmacy_unit'] : 'mg'].":",
	    			'placeholder'  => isset($values['ampoules_extra']['no']['preparation_in_pharmacy_unit']) ? $values['ampoules_extra']['no']['preparation_in_pharmacy_unit'] : 'mg',
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 1,
	    					)),
	    					array('Label', array(
	    							'tag' => 'td',
	    							'tagClass'=>'align_right', 'class'    => 'dontPrint',
	    					)),
	    					/* array(array('row' => 'HtmlTag'), array(
	    					 'tag'      => 'tr',
	    							'openOnly' => true,
	    							'class'    => "selector_preparation_in_pharmacy_no {$display_none}",
	    					)), */
	    			),
	    	
	    		  
	    			'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix': ' mg' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'pattern'          => "^[0-9,]*( mg)$",
	    	
	    	));
	    	//--
	    	$row_head->addElement('text', 'ampoules', array(
	    			'value'        => isset($values['ampoules_extra']['no']['ampoules']) ? $values['ampoules_extra']['no']['ampoules'] : null,
	    			'label'        => 'Anzahl Chargen:',
	    			//'placeholder'  => $this->translate('Ampoules (quantity)'),
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array('Label', array(
	    							'tag' => 'div',
	    							'tagClass'=>'align_right labeldiv', 'class'    => 'dontPrint',
	    					)),
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 2,//TODO-3235
	    					)),
	    					
	    					/* array(array('row' => 'HtmlTag'), array(
	    					 'tag'      => 'tr',
	    							'closeOnly' => true,
	    					)), */
	    			),
	    	
	    			//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules (quantity)') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'data-inputmask'   => "'alias':'integer', 'min':0, 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			//'pattern'          => "^[0-9\s]*( " . $this->getView()->escape($this->translate('Ampoules (quantity)')) . ")$",
	    			'pattern'          => "^[0-9\s]*$",
	    	
	    			'class'    => 'selector_ampoules',
	    			'data-extraform' => "no",
	    	));
	    	
	    	$this->__setElementsBelongTo($row_head,  'no');
	    	$subformAmpoulesExtra->addSubForm($row_head,  'row_head'.$cnt_row++);
	    	
	    	//add one single row with empty values so we have what to clone
	    	$values['ampoules_extra']['extra_no'] = [0 => []];//the empty one is used as a clone
	    	
	    	$cnt_extra_rows = 0;
	    	// print_r($values); exit;
	    	foreach ($values['ampoules_extra']['extra_no'] as $one_ampoule_extra)
	    	{
	    		/* if (isset($values['ampoules_extra'][$extra_form]['ampoules']) && $cnt_extra_rows >= $values['ampoules_extra'][$extra_form]['ampoules']) {
	    		 break;
	    		 } */
	    		$extra_row = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_no" . " {$display_none} selector_extra_rows_ampoules",]);
	    		 
	    		//TODO-3235 Carmen 02.07.2020
	    		$extra_row->addElement('note', 'col_sep1', array(
	    				'value'            => '',
	    				'decorators'       => array(
	    						'ViewHelper',
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag' => 'td', 'class' => 'dontPrint'
	    						)),
	    				),
	    		));
	    		//--
	    		
	    		$extra_row->addElement('text', 'batch_number', array(
	    				'value'        => isset($values['ampoules_extra']['extra_no'][$cnt_extra_rows]['batch_number']) ? $values['ampoules_extra']['extra_no'][$cnt_extra_rows]['batch_number'] : null,
	    				'label'        => 'Chargen Nr:',
	    				//'placeholder'  => $this->translate('Batch number'),
	    				'required'     => false,
	    				'filters'      => array('StringTrim'),
	    				'validators'   => array('NotEmpty'),
	    				'decorators'   => array(
	    						'ViewHelper',
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag'      => 'td',
	    								'colspan'  => '1',
	    						)),
	    						array('Label', array(
	    								'tag'      => 'td',
	    								'tagClass' =>'align_right', 'class'    => ' ',
	    						)),
	    				),
	    				 
	    				//'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Batch number') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':32, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    				//'pattern'          => "^[0-9]*( " . $this->translate('Batch number') . ")$",
	    				 
	    				 
	    		));
	    		 
	    		 
	    		 
	    		$extra_row->addElement('text', 'ampoules', array(
	    				'value'        => isset($values['ampoules_extra']['extra_no'][$cnt_extra_rows]['ampoules']) ? $values['ampoules_extra']['extra_no'][$cnt_extra_rows]['ampoules'] : null,
	    				'label'        => 'Amp',
	    				//'placeholder'  => $this->translate('Ampoules'),
	    				'required'     => false,
	    				'filters'      => array('StringTrim'),
	    				'validators'   => array('NotEmpty'),
	    				'decorators'   => array(
	    						'ViewHelper',
	    						array('Label', array(
	    								'tag'      => 'div',
	    								'tagClass' =>'align_right labeldiv', 'class'    => 'dontPrint',
	    						)),
	    						array(array('data' => 'HtmlTag'), array(
	    								'tag'      => 'td',
	    								'colspan'  => 2,//TODO-3235
	    						)),
	    						
	    				),
	    				 
	    				'data-inputmask'   => "'alias':'integer', 'min':0, 'suffix':' " . $this->translate('Ampoules') . "' , 'prefix': '', 'radixPoint': ',', 'integerDigits':4, 'digits':0, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    				'pattern'          => "^[0-9]*( " . $this->translate('Ampoules') . ")$",
	    				 
	    				 
	    				'class'            => 'selector_ampoules_extra',
	    		));
	    		 
	    		$this->__setElementsBelongTo($extra_row,  "extra_no[{$cnt_extra_rows}]");
	    		$subformAmpoulesExtra->addSubForm($extra_row, "no_{$cnt_extra_rows}");
	    		$cnt_extra_rows++;
	    		 
	    	}
	    	$row_volum = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_no rowselector_no {$display_none}",]);
	    	
	    	$row_volum->addElement('text', 'volum_reconstituted', array(
	    			'value'        => isset($values['ampoules_extra']['no']['volum_reconstituted']) ? $values['ampoules_extra']['no']['volum_reconstituted'] : null,
	    			'label'        => 'Volume reconstituted drug (ml)',
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 3,//TODO-3235
	    							'class' => 'cell_on_newline',
	    					)),
	    					array('Label', array(
	    							'tag' => 'td',
	    							'tagClass'=>'print_column_first',
	    	
	    					)),
	    					/*  array(array('row' => 'HtmlTag'), array(
	    					 'tag'      => 'tr',
	    							'class'    => "selector_preparation_in_pharmacy_" . $extra_form . " {$display_none}",
	    					)), */
	    			),
	    			'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'pattern'          => "^[0-9,]*$",
	    			'class'    => 'selector_toaddsum',
	    			'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    	));
	    	
	    	$this->__setElementsBelongTo($row_volum,  'no');
	    	$subformAmpoulesExtra->addSubForm($row_volum,  'row_volum'.$cnt_row++);
	    	
	    	$row_carrier = $this->subFormTableRow(['class' => "selector_preparation_in_pharmacy_no rowselector_no {$display_none}",]);
	    	
	    	$row_carrier->addElement('text', 'carrier_solution', array(
	    			'value'        => isset($values['ampoules_extra']['no']['carrier_solution']) ? $values['ampoules_extra']['no']['carrier_solution'] : null,
	    			'label'        => 'Carrier solution (ml)',
	    			'required'     => false,
	    			'filters'      => array('StringTrim'),
	    			'validators'   => array('NotEmpty'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag' => 'td',
	    							'colspan' => 3,//TODO-3235
	    							'class' => 'cell_on_newline',
	    					)),
	    					array('Label', array(
	    							'tag' => 'td',
	    							'tagClass'=>'print_column_first',
	    					)),
	    					/*  array(array('row' => 'HtmlTag'), array(
	    					 'tag'      => 'tr',
	    							'class'    => "selector_preparation_in_pharmacy_" . $extra_form. " {$display_none}",
	    					)), */
	    			),
	    			'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	    			'pattern'          => "^[0-9,]*$",
	    			'class'    => 'selector_toaddsum',
	    			'onChange' => "$(this).parents('table').eq(0).find('.selector_total_volume').val($('.selector_toaddsum:visible', $(this).parents('table').eq(0)).sum());",
	    	));
	    	
	    	$this->__setElementsBelongTo($row_carrier,  'no');
	    	$subformAmpoulesExtra->addSubForm($row_carrier,  'row_carrier'.$cnt_row++);
	    }
	    
	   	$subform->addSubForm($subformAmpoulesExtra, 'ampoules_extra');

	    $subform->addElement('text', 'total_volume', array(
	        'value'        => isset($values['total_volume']) ? $values['total_volume'] : null,
	        'label'        => 'Total volume (ml)',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
// 	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3,//TODO-3235
	                'class' => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag' => 'td',
	                'tagClass'=>'print_column_first',
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        'data-inputmask'   => "'alias':'numeric', 'min':0, 'suffix':'' , 'prefix': '', 'radixPoint': ',', 'integerDigits':5, 'digits':2, 'digitsOptional':true, 'placeholder':'', 'positionCaretOnClick': 'none', 'greedy':false, 'autoGroup': false , 'groupSeparator': '', 'autoUnmask': true, 'unmaskAsNumber': true, 'removeMaskOnSubmit':true",
	        'pattern'          => "^[0-9,]*$",
	        'class'            => 'selector_total_volume',
// 	        'readonly'         => true,
	    )); 
	    
	    
	    
	    $subform->addElement('text', 'preparation_time', array(
	        'value'        => isset($values['preparation_time']) ? $values['preparation_time'] : null,
	        'label'        => 'Preparation time (min)',
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3,//TODO-3235	     
	                'class' => 'cell_on_newline',
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tagClass'=>'print_column_first',
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        'data-inputmask'   => "'alias':'integer', 'min':0, 'integerDigits':5",
	        'pattern'          => "^[0-9]*$",
	    ));
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	public function save_form_infusion ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_infusion_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_infusion_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_infusion_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	    
	    
	    $entity = FormBlockInfusionTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
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
	private function __save_form_infusion_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || in_array('infusion', $data['__formular']['allowed_blocks'])
	    )
	    {
	        return;
	    }
	     
	     
	    $oldValues = FormBlockInfusionTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	         
	        unset($oldValues[FormBlockInfusionTable::getInstance()->getIdentifier()]);
	        	
	        $data = array_merge($data, $oldValues);
	    }
	     
	}
	
	/**
	 * write or erase the patientcourse text
	 *
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_infusion_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || ! in_array('infusion', $data['__formular']['allowed_blocks']))
	    {
	        return;
	    }
	     
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	     
	    if ( ! in_array('infusion', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	     
	     
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_infusion_patient_course_format($data);
	     
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	         
	        $oldValues = FormBlockInfusionTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	        	
	        if (empty($oldValues)) {
	             
	            //missing previous values, so we save
	            $save_2_PC = true ;
	             
	        } else {
	             
	            $course_arr_OLD =  $this->__save_form_infusion_patient_course_format($oldValues);
	             
	            if ($course_arr_OLD === $course_arr) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	             
	        }
	         
	    }
	     
	     
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockInfusionTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
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
	        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockInfusion::PATIENT_COURSE_TABNAME);
	
	    }
	     
	}
	
	
	/**
	 * format the patientcourse title message
	 *
	 * @param unknown $data
	 * @return multitype:string
	 */
	private function __save_form_infusion_patient_course_format($data = [])
	{
	    $course_arr = [];
	    
	    if ( ! empty($data['drug_name'])) {
	        $course_arr[] = $this->translate('Drug Name') . ": " . $data['drug_name'];
	    }
	    if ( ! empty($data['preparation_in_pharmacy'])) {
	        $course_arr[] = $this->translate('Preparation in pharmacy') . ": " . ($data['preparation_in_pharmacy'] == "yes" ? "Ja" : "Nein");
	    }
	    if ( ! empty($data['batch_number'])) {
	        $course_arr[] = $this->translate('Batch number ready bag') . ": " . $data['batch_number'];
	    }
	    
	    if ( ! empty($data['mg']) || ! empty($data['ampoules'])) {
	        $course_arr[] = ($data['mg'] ? $data['mg'] . " ". $this->translate('mg') : '') . ($data['ampoules'] ? " = " . $data['ampoules'] . " " . $this->translate('ampoules') : '');
	    }
	    
// 	    if ( ! empty($data['ampoules_extra'])) {
// 	        $course_arr[] = $this->translate('ampoules_extra') . ": ";
// 	        foreach ($data['ampoules_extra'] as $row) {
// 	            $course_arr[] = ''; $this->translate($row) . " " . $data['ampoules_extra'][$this->filterName($row)];
// 	        }
// 	    }
	     
	    return $course_arr;
	}
	
	/**
	 * set isdelete = 1 for the old block
	 *
	 * @param string $ipid
	 * @param number $contact_form_id
	 * @return boolean
	 */
	private function __save_form_infusion_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockInfusionTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	         
	        return true;
	    }
	}
	
	
	
	
	
	
	
	
	
}