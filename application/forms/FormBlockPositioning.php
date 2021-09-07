<?php
/**
 * 
 * @author carmen
 * Apr 09, 2020 ISPC-2522
 * 
 * #ISPC-2512PatientCharts
 */
class Application_Form_FormBlockPositioning extends Pms_Form
{
    
    protected $_model = 'FormBlockPositioning';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockPositioning::TRIGGER_FORMID;
    private $triggerformname = FormBlockPositioning::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockPositioning::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    
    public function __construct($options = null)
    {
    	parent::__construct($options);
    
    }


    public function getColumnMapping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
    
        ];
        
        $values = FormBlockPositioningTable::getInstance()->getEnumValues($fieldName);
   
        if($fieldName != 'positioning_additional_info' && $fieldName != 'positioning_type')
        {
        $values = array_combine($values, array_map("self::translate", $values));
       	
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
        }
        
        //if($fieldName == 'positioning_additional_info' || $fieldName == 'positioning_type')
        if( $fieldName == 'positioning_type')
        {
        	$values_empty[''] = self::translate('select'); //ISPC-266 carmen 31.08.2020
        	$values = $values_empty+ $values;
        }
        
        //ISPC-2662 Carmen 28.08.2020
        //ISPC-2522 Lore 15.05.2020
       /*  if($fieldName == 'positioning_additional_info'){
        	$new_values = array();
        	foreach($values as $key => $vals){
        		$new_values[$key] = $vals.'°';
        	}
        	$values_empty[''] = $this->translate('select');
        	$values = $values_empty + $new_values ;
        } */
        //.
        
        if($fieldName == 'positioning_additional_info'){
            $new_values = array();
            
            foreach($values as $key => $vals){
            	$new_values = array();
            	$values_empty[''] = self::translate('select');
            	if($key == 'storage')
            	{
	            	foreach($vals as $kval => $vval)
	            	{
	                	$new_values[$kval] = $vval.'°';
	            	}
	            	$values[$key] = $values_empty + $new_values ;
            	}
            	else if($key == 'storage_support')
            	{
            		foreach($vals as $kval => $vval)
            		{
            			$new_values[$kval] = $vval;
            		}
            		$values[$key] = $values_empty + $new_values ;
            	}            	
            }
        }
        //--
        
        return $values;
    
    }
    
    
    
    
	public function create_form_block_positioning ($values =  array() , $elementsBelongTo = null)
	{
// 	    dd($values);
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_block_positioning");
	
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend($this->translate('positioning'));
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    $subform->addDecorator('Form'); //ISPC-2662 Carmen 31.08.2020
   
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
		  
		 	$positionind_type_index = array_search($values['positioning_type'], $this->getColumnMapping('positioning_type'));
		    $subform->addElement('select', 'positioning_type', array(
    			'label' 	   => self::translate('positioning_type'),
    			'multiOptions' => $this->getColumnMapping('positioning_type'),
    			'value'        => $positionind_type_index,
    			'required'     => false,
    			'filters'      => array('StringTrim'),
    			'decorators' =>   array(
    					'ViewHelper',
    					array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first required')),
    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			),
		    	'onChange' => 'if ($(this).val() == "1") { $(".positioning_type_more", $(this).parents("table")).show(); $(".storage", $(this).parents("table")).hide(); $(".storage_support", $(this).parents("table")).hide(); $(".storage_support_more", $(this).parents("table")).hide(); $("#positioning_additional_info-storage").val(""); $("#positioning_additional_info-storage_support").val("");$("#positioning_additional_info-storage_suport_free_text").val("");} else {$(".positioning_type_more", $(this).parents("table")).hide(); $("#positioning_additional_info-no_storage_free_text").val("");}
		    				   if ($(this).val() == "2" || $(this).val() == "" || $(this).val() == "12") { $(".storage", $(this).parents("table")).hide(); $(".storage_support", $(this).parents("table")).hide(); $(".storage_support_more", $(this).parents("table")).hide(); $("#positioning_additional_info-storage").val(""); $("#positioning_additional_info-storage_support").val("");$("#positioning_additional_info-storage_suport_free_text").val("");}
		    				   if ($(this).val() != "1" && $(this).val() != "2" && $(this).val() != "" && $(this).val() != "12") { $(".storage, .storage_support", $(this).parents("table")).show(); if($(".storage_support").val() == "3") {$(".storage_support_more", $(this).parents("table")).show();}}',
    		));
		    
		    $subform->addElement('note', 'Note_positioning_type_err', array(
		    		'value'        => $this->translate('positioning_type_err'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td', 'colspan' => 2,
		    				)),
		    				array(array('row' => 'HtmlTag'), array(
		    						'tag'      => 'tr', 'id' => 'positioning_type_error',
		    				)),
		    		),
		    ));
		    
		    //ISPC-2662 Carmen 31.08.2020
		    /* $positionind_additinfo_index = array_search($values['positioning_additional_info'].'°', $this->getColumnMapping('positioning_additional_info'));
		    $subform->addElement('select', 'positioning_additional_info', array(
		    		'label' 	   => self::translate('positioning_aditional_info'),
		    		'multiOptions' => $this->getColumnMapping('positioning_additional_info'),
		    		'value'        => $positionind_additinfo_index,
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    		),
		    )); */
		    
		    $display = $positionind_type_index != 1 ? 'display:none' : null;
		    $subform->addElement('text', 'no_storage_free_text', array(
		    		'belongsTo' => 'positioning_additional_info',
		    		'label' 	   => self::translate('no_storage_free_text'),
		    		'value'        => $values['positioning_additional_info']['no_storage_free_text'],
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		//'placeholder'  => $this->translate('freetext'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'positioning_type_more', 'style' => $display )),
		    		),
		    		 
		    ));
		    
		    $display = ($positionind_type_index == 1 || $positionind_type_index == 2 || $positionind_type_index == '' || $positionind_type_index == '12') ? 'display:none' : null;		    
		    $subform->addElement('select', 'storage', array(
		    		'belongsTo' => 'positioning_additional_info',
		    		'label' 	   => self::translate('positioning_storage'),
		    		'multiOptions' => $this->getColumnMapping('positioning_additional_info')['storage'],
		    		'value'        => $values['positioning_additional_info']['storage'],
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'storage', 'style' => $display )),
		    		),
		    ));
		    
		    $subform->addElement('select', 'storage_support', array(
		    		'belongsTo' => 'positioning_additional_info',
		    		'label' 	   => self::translate('positioning_storage_support'),
		    		'multiOptions' => $this->getColumnMapping('positioning_additional_info')['storage_support'],
		    		'value'        => $values['positioning_additional_info']['storage_support'],
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'storage_support', 'style' => $display )),
		    		),
		    		'onChange' => 'if ($(this).val() == "3") { $(".storage_support_more", $(this).parents("table")).show();} else {$(".storage_support_more", $(this).parents("table")).hide();}',
		    ));
		    
		    $display_sub = $values['positioning_additional_info']['storage_support'] != 3 ? 'display:none' : null;
		    $subform->addElement('text', 'storage_suport_free_text', array(
		    		'belongsTo' => 'positioning_additional_info',
		    		'label' 	   => self::translate('storage_suport_free_text'),
		    		'value'        => $values['positioning_additional_info']['storage_suport_free_text'],
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		//'placeholder'  => $this->translate('freetext'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'storage_support_more', 'style' => $display_sub )),
		    		),
		    		 
		    ));
		    //--
		    //ISPC-2661 pct.13 Carmen 07.09.2020
		    /* $subform->addElement('text', 'positioning_date', array(
		    		'label'        => self::translate('positioning_date'),
		    		'value'        => ! empty($values['positioning_date']) ? date('d.m.Y', strtotime($values['positioning_date'])) : date('d.m.Y'),
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'date option_date',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    		),
		    
		    ));
		   
		    $positioning_time = ! empty($values['positioning_date']) ? date('H:i:s', strtotime($values['positioning_date'])) : date("H:i");
		    $subform->addElement('text', 'positioning_time', array(
		    		//'label'        => self::translate('clock:'),
		    		'value'        => $positioning_time,
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'time option_time',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
		    				//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    		),
		    )); */
		    
		    $subform->addElement('text', 'form_start_date', array(
		    		'belongsTo' => 'positioning_events[0]',
		    		'label'        => self::translate('positioning_date') .' von ',
		    		'value'        => (! empty($values['form_start_date']) && $values['form_start_date'] != '0000-00-00 00:00:00') ? date('d.m.Y', strtotime($values['form_start_date'])) : date('d.m.Y'),
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'date option_date',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    		        array(array('data' => 'HtmlTag'), array('tag' => 'td', 'style'=>'width: 80%!important;',"openOnly" => true)),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    		),
		    
		    ));
		     
		    $form_start_time = (! empty($values['form_start_date']) && $values['form_start_date'] != '0000-00-00 00:00:00') ? date('H:i:s', strtotime($values['form_start_date'])) : date("H:i");
		    $subform->addElement('text', 'form_start_time', array(
		    		'belongsTo' => 'positioning_events[0]',
		    		//'label'        => self::translate('clock:'),
		    		'value'        => $form_start_time,
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'time option_time',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    				//array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
		    				//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    		),
		    ));
		    
		    $display = !empty($values['isenduncertain']) ? 'display: none' : ''; //ISPC-2661 Carmen
		    $subform->addElement('text', 'form_end_date', array(
		    		'belongsTo' => 'positioning_events[0]',
		    		'label'        => ' bis ',
		    		'value'        => (! empty($values['form_end_date']) && $values['form_end_date'] != '0000-00-00 00:00:00') ? date('d.m.Y', strtotime($values['form_end_date'])) : '',
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'date option_date',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    				//array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
		    				array('Label', array('tag' => 'span', 'tagClass'=>'print_column_first','style' => $display,)), //ISPC-2661 Carmen
		    				//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    		),
		    		'style' => $display, //ISPC-2661 Carmen
		    ));
		   
		    $form_end_time = (! empty($values['form_end_date']) && $values['form_end_date'] != '0000-00-00 00:00:00') ? date('H:i:s', strtotime($values['form_end_date'])) : '';
		    $subform->addElement('text', 'form_end_time', array(
		    		'belongsTo' => 'positioning_events[0]',
		    		//'label'        => self::translate('clock:'),
		    		'value'        => $form_end_time,
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'time option_time',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    				//array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
		    				//array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    		),
		    		'style' => $display, //ISPC-2661 Carmen
		    ));
			//ISPC-2661 Carmen
		   /*  $subform->addElement('checkbox', 'isenduncertain', array(
		    		'belongsTo' => 'positioning_events[0]',
		    		'checkedValue'    => '1',
		    		'uncheckedValue'  => '0',
		    		'value'        => !empty($values['isenduncertain']) ? $values['isenduncertain'] : '0',
		    		//'label'        => '',
		    		'required'   => false,
		    		'decorators' => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				//array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
		    				//array('Label', array('tag' => 'td')),
		    				//array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    		),
		    		'onchange' => 'if($(this).is(":checked")) {
		    		
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled"); 
					}
		    		else
		    		{		    		
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ;
		    		}		    		
		    		',
		    		'title' => 'Ende ungewiss',
		    )); */
		   
		    $subform->addElement('note', 'addformnewrow', array(
		    		//'belongsTo' => 'set['.$krow.']',
		    		'value' => '<span title="'.$this->translate("add new interval").'" onclick="addformnewrow(this, \'positioning_events\')"><img src="'.RES_FILE_PATH.'/images/btttt_plus.png" style="margin-right: 5px;"/></span>',
		    		'decorators' => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				//array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
		    				//array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
		    				//array('Label', array('tag' => 'td')),
		    				//array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    				//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    		),
		    ));
		    
		    $subform->addElement('checkbox', 'isenduncertain', array(
		    		'belongsTo' => 'positioning_events[0]',
		    		'checkedValue'    => '1',
		    		'uncheckedValue'  => '0',
		    		'value'        => !empty($values['isenduncertain']) ? $values['isenduncertain'] : '0',
		    		'label'        => 'Ende ungewiss',
		    		'required'   => false,
		    		'decorators' => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
		    				array('Label', array('placement' => 'PREPEND', 'style' => 'display:block; float: right; margin-right: 80%; margin-top: 3px; height: 16px; line-height: 16px;')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
		    		),
		    		'onchange' => 'if($(this).is(":checked")) {
		    		//ISPC-2661 Carmen
		    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled"); */
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").hide() ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').hide(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").hide();
					}
		    		else
		    		{
		    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ; */
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").show() ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').show(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").show() ;
		    		
		    		}
		    		',
		    		//--
		    		    		'title' => 'Ende ungewiss',
		    		'style' => 'display: block;'
		    		    		));
		//--
		    $subform->addElement('note', 'Note_positioning_date_type_err', array(
		    		'value'        => $this->translate('positioning_date_time_type_err'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td', 'colspan' => 2,
		    				)),
		    				array(array('row' => 'HtmlTag'), array(
		    						'tag'      => 'tr', 'id' => 'positioning_date_time_type_error',
		    				)),
		    		),
		    ));
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	public function save_form_block_positioning ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }

    	if(!$data['contact_form_id'])
    	{
    		//ISPC-2661 pct.13 Carmen 08.09.2020
    		/* //$data['positioning_date'] = date('Y-m-d H:i:s', time());
    		if($data['positioning_time'] != "")
    		{
    			$data['positioning_time'] = $data['positioning_time'] . ":00";
    		}
    		else
    		{
    			$data['positioning_time'] = '00:00:00';
    		}
    		 
    		if($data['positioning_date'] != "")
    		{
    			$data['positioning_date'] = date('Y-m-d H:i:s', strtotime($data['positioning_date'] . ' ' . $data['positioning_time']));
    		}
    		else
    		{
    			$data['positioning_date'] = '0000-00-00 00:00:00';
    		} */
    		$data_to_save = array();
    		$data_to_add = array();
    		$addindex = 0;
    		foreach($data['positioning_events'] as $kev => $vev)
    		{
    			if($vev['form_start_time'] != "")
    			{
    				$form_start_time = $vev['form_start_time'] . ":00";
    			}
    			else
    			{
    				$form_start_time = '00:00:00';
    			}
    			
    			if($vev['form_end_time'] != "")
    			{
    				$form_end_time = $vev['form_end_time'] . ":00";
    			}
    			else
    			{
    				$form_end_time = '00:00:00';
    			}
    			 
    			if($vev['form_start_date'] != "")
    			{
    				$form_start_date = date('Y-m-d H:i:s', strtotime($vev['form_start_date'] . ' ' . $form_start_time));
    			}
    			else
    			{
    				$form_start_date = '0000-00-00 00:00:00';
    			}
    			
    			if($vev['form_end_date'] != "")
    			{
    				$form_end_date = date('Y-m-d H:i:s', strtotime($vev['form_end_date'] . ' ' . $form_end_time));
    			}
    			else
    			{
    				$form_end_date = '0000-00-00 00:00:00';
    			}
    			
    			if($data['id'] != '')
    			{
    				if($kev == "0")
    				{
	    				$data_to_save['id'] = $data['id'];
	    				$data_to_save['ipid'] = $ipid;
	    				$data_to_save['positioning_type'] = $data['positioning_type'];
	    				$data_to_save['positioning_additional_info'] = $data['positioning_additional_info'];
	    				$data_to_save['form_start_date'] = $form_start_date;
	    				if($vev['isenduncertain'] != 1)
	    				{
	    					$data_to_save['form_end_date'] = $form_end_date;
	    					$data_to_save['isenduncertain'] = 0;
	    				}
	    				else
	    				{
	    					$data_to_save['isenduncertain'] = 1;
	    					$data_to_save['form_end_date'] = '0000-00-00 00:00:00';
	    				} 
    				}
    				else 
    				{
		    			$data_to_add[$addindex]['id'] = '';
		    			$data_to_add[$addindex]['ipid'] = $ipid;
		    			$data_to_add[$addindex]['positioning_type'] = $data['positioning_type'];
		    			$data_to_add[$addindex]['positioning_additional_info'] = $data['positioning_additional_info'];
		    			$data_to_add[$addindex]['form_start_date'] = $form_start_date;
		    			if($vev['isenduncertain'] != 1)
		    			{
		    				$data_to_add[$addindex]['form_end_date'] = $form_end_date;
		    				$data_to_add[$addindex]['isenduncertain'] = 0;}
		    			else
		    			{
		    				$data_to_add[$addindex]['isenduncertain'] = 1;
		    				$data_to_add[$addindex]['form_end_date'] = '0000-00-00 00:00:00';
		    			}
		    			$addindex++;
		    			
	    			}
    			}
    			else 
    			{
    				$data_to_add[$addindex]['id'] = '';
    				$data_to_add[$addindex]['ipid'] = $ipid;
    				$data_to_add[$addindex]['positioning_type'] = $data['positioning_type'];
    				$data_to_add[$addindex]['positioning_additional_info'] = $data['positioning_additional_info'];
    				$data_to_add[$addindex]['form_start_date'] = $form_start_date;
    				if($vev['isenduncertain'] != 1)
    				{
    					$data_to_add[$addindex]['form_end_date'] = $form_end_date;
    					$data_to_add[$addindex]['isenduncertain'] = 0;
    				}
    				else
    				{
    					$data_to_add[$addindex]['isenduncertain'] = 1;
    					$data_to_add[$addindex]['form_end_date'] = '0000-00-00 00:00:00';
    				}
    				$addindex++;
    			}
    			
    		}
    	}
		//$data['ipid'] = $ipid;
		//--

	    //if not from charts
	   if($data['contact_form_id'])
	   {
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_positioning_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_positioning_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_positioning_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	   }
	   // TODO-4158 Ancuta 26.05.2021
	   else
	   {
	       $this->__save_positioning_patient_course($ipid , $data);
	   }
	   //-- 
	   //ISPC-2661 pct.13 Carmen 08.09.2020
	   if(!empty($data_to_save))
	   {
	    	$entity = FormBlockPositioningTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data_to_save['id'], $ipid], $data_to_save);
	   }
	  
	   if(!empty($data_to_add))
	   {
	   		$collection = new Doctrine_Collection("FormBlockPositioning");
	   		$collection->fromArray($data_to_add);
	   		$collection->save();
	   }
	   //--
	    
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
	private function __save_form_positioning_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || in_array('positioning', $data['__formular']['allowed_blocks'])
	    )
	    {
	        return;
	    }
	     
	     
	    $oldValues = FormBlockPositioningTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	         
	        unset($oldValues[FormBlockPositioningTable::getInstance()->getIdentifier()]);
	        	
	        $data = array_merge($data, $oldValues);
	        $data['contact_form_id'] = $data['__formular']['contact_form_id'];
	       
	    }
	     
	}
	
	/**
	 * write or erase the patientcourse text
	 *
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_positioning_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || ! in_array('positioning', $data['__formular']['allowed_blocks']))
	    {
	        return;
	    }
	     
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	     
	    if ( ! in_array('positioning', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	     
	    
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_positioning_patient_course_format($data);
	   
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	         
	        $oldValues = FormBlockPositioningTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	        
	        if (empty($oldValues)) {
	             
	            //missing previous values, so we save
	            $save_2_PC = true ;
	             
	        } else {
	             
	            $course_arr_OLD =  $this->__save_form_positioning_patient_course_format($oldValues);
	           
	            if ($course_arr_OLD === $course_arr) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	             
	        }
	         
	    }
	     
	     
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockPositioningTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
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
	private function __save_form_positioning_patient_course_format($data = [])
	{
	    $course_arr = [];
	   
	    
	    
	    return $course_arr;
	}
	
	/**
	 * set isdelete = 1 for the old block
	 *
	 * @param string $ipid
	 * @param number $contact_form_id
	 * @return boolean
	 */
	private function __save_form_positioning_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockPositioningTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	         
	        return true;
	    }
	}
	
	
	/**
	 * TODO-4158 Ancuta 26.05.2021
	 * @param unknown $ipid
	 * @param array $data
	 */
	private function __save_positioning_patient_course($ipid =  null , $data =  array())
	{ 
	    
	    if (empty($ipid) || empty($data) )
	    {
	        return;
	    }
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    $userid = $logininfo->userid;
	    $cl_opt_details = $this->getColumnMapping('positioning_type');
	    
	    if(empty($data['id'])){
	        $comment = "Eine Positionierung wurde erfasst: ".$cl_opt_details[$data['positioning_type']];
	    } else{
	        $comment = "Eine Positionierung wurde geändert: ".$cl_opt_details[$data['positioning_type']];
	    }
	    
	    $cust = new PatientCourse();
	    $cust->ipid = $ipid;
	    $cust->course_date = date("Y-m-d H:i:s", time());
	    $cust->course_type = Pms_CommonData::aesEncrypt('K');
	    $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
	    $cust->tabname = Pms_CommonData::aesEncrypt(addslashes('FormBlockPositioning'));
	    $cust->user_id = $userid;
	    $cust->save();
	    
	}
	
	
	
}