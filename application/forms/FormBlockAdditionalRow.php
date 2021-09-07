<?php
/**
 * 
 * @author carmen
 * Sep, 2020
 * for ISPC-2661 pct.13
 */
class Application_Form_FormBlockAdditionalRow extends Pms_Form
{
	
	public function __construct($options = null)
	{
	
		
		parent::__construct($options);
		
		if (isset($options['elementsBelongTo'])) {
			$this->_elementsBelongTo = $options['elementsBelongTo'];
			unset($options['elementsBelongTo']);
		}
	
	}
	
	public function isValid($data)
	{
	    return parent::isValid($data);   
	}
	
	
	
	public function create_form_additional_row($options = array(), $elementsBelongTo = null)
	{	    
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	    
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	    
	    $this->mapSaveFunction($__fnName , "save_form_additional_row");	     
	   
	    
		$subform = new Zend_Form_SubForm();
	    $subform->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
		));
	    
		if($this->_elementsBelongTo)
			{
				$this->__setElementsBelongTo($subform, $this->_elementsBelongTo );
			}
		else if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
	    
	    $subform->addElement('text', 'form_start_date', array(
		    		'label'        => self::translate('form_start_date') .' von ',
		    		'value'        => date('d.m.Y'),
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
		    
		    $subform->addElement('text', 'form_start_time', array(
		    		//'label'        => self::translate('clock:'),
		    		'value'        => date("H:i"),
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
		    
		    $subform->addElement('text', 'form_end_date', array(
		    		'label'        => ' bis ',
		    		'value'        => '',
		    		'required'     => true,
		    		'filters'      => array('StringTrim'),
		    		'validators'   => array('NotEmpty'),
		    		'class'        => 'date option_date',
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array('Errors'),
		    				//array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
		    				array('Label', array('tag' => 'span', 'tagClass'=>'print_column_first')),
		    				//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
		    		),
		    
		    ));
		    
		    $subform->addElement('text', 'form_end_time', array(
		    		//'label'        => self::translate('clock:'),
		    		'value'        => '',
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

		    //ISPC-2661 Carmen
		    /* $subform->addElement('checkbox', 'isenduncertain', array(
		    		'value'        => '',
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
		    		
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled"); 
					}
		    		else
		    		{		    		
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ;
		    		}		    		
		    		',
		    		'title' => 'Ende ungewiss',
		    )); */
		    
		    $subform->addElement('note', 'deleteformnewrow', array(
		    		//'belongsTo' => 'set['.$krow.']',
		    		'value' => '<span title="'.$this->translate("delete new interval").'" onclick="$(this).closest(\'tr\').remove()"><img src="'.RES_FILE_PATH.'/images/action_delete.png" style="margin-right: 5px;"/></span>',
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
		    		'value'        => '',
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
		    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled"); */
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").hide() ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").hide();
		    		
					}
		    		else
		    		{
		    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ; */
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").show() ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").show() ;
		    		}
		    		',
		    		//--
		    		    		'title' => 'Ende ungewiss',
		    					'style' => 'display: block;'
		    		    		));
		    //--
	    return $this->filter_by_block_name($subform , __FUNCTION__);	    
	     
	}
	
	//ISPC-2661 pct.18 Carmen
	public function create_form_awake_sleeping_additional_row($options = array(), $elementsBelongTo = null)
	{
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		 
		$this->mapValidateFunction($__fnName , "create_form_isValid");
		 
		$this->mapSaveFunction($__fnName , "save_form_additional_row");
	
		 
		$subform = new Zend_Form_SubForm();
		$subform->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
		));
		 
		if($this->_elementsBelongTo)
		{
			$this->__setElementsBelongTo($subform, $this->_elementsBelongTo );
		}
		else if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		
		$subform->addElement('radio', 'awake_sleep_status', array(
				'value'        => ! empty($values['awake_sleep_status']) ? $values['awake_sleep_status'] : null,
				'multiOptions' => $this->getColumnMappingAwakeSleeping('awake_sleep_status'),
				'separator'    => '',
				'label'        => self::translate('awake_sleep_status'),
				'decorators' => array(
						'ViewHelper',
						array('Errors'),
						array(array('data' => 'HtmlTag'), array(
								'tag' => 'td',
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
		 
		$subform->addElement('text', 'form_start_date', array(
				'label'        => self::translate('form_start_date') .' von ',
				'value'        => date('d.m.Y'),
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
	
		$subform->addElement('text', 'form_start_time', array(
				//'label'        => self::translate('clock:'),
				'value'        => date("H:i"),
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
	
				$subform->addElement('text', 'form_end_date', array(
						'label'        => ' bis ',
						'value'        => '',
						'required'     => true,
						'filters'      => array('StringTrim'),
						'validators'   => array('NotEmpty'),
						'class'        => 'date option_date',
						'decorators' =>   array(
								'ViewHelper',
								array('Errors'),
								//array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
								array('Label', array('tag' => 'span', 'tagClass'=>'print_column_first')),
								//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
						),
	
				));
	
				$subform->addElement('text', 'form_end_time', array(
						//'label'        => self::translate('clock:'),
						'value'        => '',
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
	//ISPC-2661 Carmen
						/* $subform->addElement('checkbox', 'isenduncertain', array(
								'value'        => '',
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
	
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled");
					}
		    		else
		    		{
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ;
		    		}
		    		',
			    		'title' => 'Ende ungewiss',
			    		)); */
	
			    		$subform->addElement('note', 'deleteformnewrow', array(
			    				//'belongsTo' => 'set['.$krow.']',
			    				'value' => '<span title="'.$this->translate("delete new interval").'" onclick="$(this).closest(\'tr\').prev().remove(); $(this).closest(\'tr\').remove();"><img src="'.RES_FILE_PATH.'/images/action_delete.png" style="margin-right: 5px;"/></span>',
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
			    				'value'        => '',
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
			    		
					    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide();
					    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled"); */
			    				$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").hide() ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').hide();
					    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").hide();
								}
					    		else
					    		{
					    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').show();
					    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ; */
			    				$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").show() ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show();
					    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").show() ;
					    		}
					    		',
			    				    		'title' => 'Ende ungewiss',
			    				'style' => 'display: block;',
			    				    		));
			    		//--		 
			    				return $this->filter_by_block_name($subform , __FUNCTION__);
	
	}
	
	public function create_form_additionalcustomevents_row($options = array(), $elementsBelongTo = null)
	{
		$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
		 
		$this->mapValidateFunction($__fnName , "create_form_isValid");
		 
		$this->mapSaveFunction($__fnName , "save_form_additional_row");
	
		 
		$subform = new Zend_Form_SubForm();
		$subform->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
		));
		 
		if($this->_elementsBelongTo)
		{
			$this->__setElementsBelongTo($subform, $this->_elementsBelongTo );
		}
		else if ( ! is_null($elementsBelongTo)) {
			$subform->setOptions(array(
					'elementsBelongTo' => $elementsBelongTo
			));
		}
		 
		$subform->addElement('text', 'form_start_date', array(
				'label'        => self::translate('form_start_date') .' von ',
				'value'        => date('d.m.Y'),
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
	
		$subform->addElement('text', 'form_start_time', array(
				//'label'        => self::translate('clock:'),
				'value'        => date("H:i"),
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
	
				$subform->addElement('text', 'form_end_date', array(
						'label'        => ' bis ',
						'value'        => '',
						'required'     => true,
						'filters'      => array('StringTrim'),
						'validators'   => array('NotEmpty'),
						'class'        => 'date option_date',
						'decorators' =>   array(
								'ViewHelper',
								array('Errors'),
								//array(array('data' => 'HtmlTag'), array('tag' => 'td', "openOnly" => true)),
								array('Label', array('tag' => 'span', 'tagClass'=>'print_column_first')),
								//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
						),
	
				));
	
				$subform->addElement('text', 'form_end_time', array(
						//'label'        => self::translate('clock:'),
						'value'        => '',
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
	
						//ISPC-2661 Carmen
						/* $subform->addElement('checkbox', 'isenduncertain', array(
						'value'        => '',
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
	
						$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide();
						$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled");
						}
						else
						{
						$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show();
						$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ;
						}
						',
						'title' => 'Ende ungewiss',
						)); */
	
				$subform->addElement('note', 'deleteformnewrow', array(
						//'belongsTo' => 'set['.$krow.']',
						'value' => '<span title="'.$this->translate("delete new interval").'" onclick="$(this).closest(\'tr\').remove()"><img src="'.RES_FILE_PATH.'/images/action_delete.png" style="margin-right: 5px;"/></span>',
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
				
				$subform->addElement('checkbox', 'onetimeevent', array(
						'value'        => '',
						'label'        => 'punktuelles Ereignis - kein Zeitraum',
						'required'   => false,
						'decorators' => array(
								'ViewHelper',
								array('Errors'),
								//array(array('data' => 'HtmlTag'), array('tag' => 'td', "closeOnly" => true)),
								array('Label', array('placement' => 'PREPEND', 'style' => 'display:block; float: right; margin-right: 58%; margin-top: 3px; height: 16px; line-height: 16px;')),
								//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'closeOnly' => true)),
						),
						'onchange' => 'if($(this).is(":checked")) {
		    		//ISPC-2661 Carmen
		    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled"); */
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").hide() ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").hide();
				
					}
		    		else
		    		{
		    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ; */
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").show() ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").show() ;
		    		}
		    		',
						//--
						'title' => 'punktuelles Ereignis - kein Zeitraum',
						'style' => 'display: block;'
				));
						 
						$subform->addElement('checkbox', 'isenduncertain', array(
								'value'        => '',
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
		    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled"); */
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").hide() ; $(this).closest("tr").find(\'input[name*="form_end_date"]\').next(\'.ui-datepicker-trigger\').hide(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').hide();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").hide();
	
					}
		    		else
		    		{
		    		/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ; */
		    		$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").show() ; $(this).closest("tr").find(\'img.ui-datepicker-trigger\').show();
		    		$(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").show() ;
		    		}
		    		',
								//--
								'title' => 'Ende ungewiss',
								'style' => 'display: block;'
						));
						//--
		    return $this->filter_by_block_name($subform , __FUNCTION__);
	
	}
	//--
	public function save_form_save_form_additional_row()
	{
	    
	}
	
	
	
	
	
	public function getColumnMappingAwakeSleeping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
           
    
        ];
    
    
        $values = FormBlockAwakeSleepingStatusTable::getInstance()->getEnumValues($fieldName);
    
        $values = array_combine($values, array_map("self::translate", $values));
    
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
    
        return $values;
    
    }
        
   
    
    
}

