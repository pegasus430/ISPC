<?php
/**
 * 
 * @author carmen
 * #ISPC-2512PatientCharts
 * Apr 09, 2020 ISPC-2516
 */
class Application_Form_FormBlockAwakeSleepingStatus extends Pms_Form
{
    
    protected $_model = 'FormBlockAwakeSleepingStatus';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockAwakeSleepingStatus::TRIGGER_FORMID;
    private $triggerformname = FormBlockAwakeSleepingStatus::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockAwakeSleepingStatus::LANGUAGE_ARRAY;
    
    
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
    
    
        $values = FormBlockAwakeSleepingStatusTable::getInstance()->getEnumValues($fieldName);
    
        $values = array_combine($values, array_map("self::translate", $values));
    
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
    
        return $values;
    
    }
    
    
    
    
	public function create_form_block_awake_sleeping_status ($values =  array() , $elementsBelongTo = null)
	{
// 	    dd($values);
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_block_awake_sleeping_status");
	
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend('Verhaltensbeobachtung');
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    $subform->addDecorator('Form'); //ISPC-2661 pct.13 Carmen 10.09.2020
	    
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
		    
		   
		    $subform->addElement('radio', 'awake_sleep_status', array(
		    		'belongsTo' => 'awakesleeping_events[0]',//ISPC-2661 pct.18 carmen
		    		'value'        => ! empty($values['awake_sleep_status']) ? $values['awake_sleep_status'] : null,
		    		'multiOptions' => $this->getColumnMapping('awake_sleep_status'),
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
		    //ISPC-2661 pct.13 Carmen 07.09.2020
		    /* $subform->addElement('text', 'status_date', array(
		    		'label'        => self::translate('status_date'),
		    		'value'        => ! empty($values['status_date']) ? date('d.m.Y', strtotime($values['status_date'])) : date('d.m.Y'),
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
		   
		    $status_time = ! empty($values['status_date']) ? date('H:i:s', strtotime($values['status_date'])) : date("H:i");
		    $subform->addElement('text', 'status_time', array(
		    		//'label'        => self::translate('clock:'),
		    		'value'        => $status_time,
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
		    		'belongsTo' => 'awakesleeping_events[0]',
		    		'label'        => self::translate('status_date') .' von ',
		    		'value'        => (! empty($values['form_start_date']) && $values['form_start_date'] != '0000-00-00 00:00:00') ? date('d.m.Y', strtotime($values['form_start_date'])) : date('d.m.Y'),
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
		     
		    $form_start_time = (! empty($values['form_start_date']) && $values['form_start_date'] != '0000-00-00 00:00:00') ? date('H:i:s', strtotime($values['form_start_date'])) : date("H:i");
		    $subform->addElement('text', 'form_start_time', array(
		    		'belongsTo' => 'awakesleeping_events[0]',
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
    				'belongsTo' => 'awakesleeping_events[0]',
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
    						array('Label', array('tag' => 'span', 'tagClass'=>'print_column_first', 'style' => $display,)), //ISPC-2661 Carmen
    						//array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'openOnly' => true)),
    				),
    				'style' => $display, //ISPC-2661 Carmen
    		));
		    		 
    		$form_end_time = (! empty($values['form_end_date']) && $values['form_end_date'] != '0000-00-00 00:00:00') ? date('H:i:s', strtotime($values['form_end_date'])) : '';
    		$subform->addElement('text', 'form_end_time', array(
    				'belongsTo' => 'awakesleeping_events[0]',
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
    		/* $subform->addElement('checkbox', 'isenduncertain', array(
    				'belongsTo' => 'awakesleeping_events[0]',
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
    				'value' => '<span title="'.$this->translate("add new interval").'" onclick="addformawakesleepingnewrow(this, \'awakesleeping_events\')"><img src="'.RES_FILE_PATH.'/images/btttt_plus.png" style="margin-right: 5px;"/></span>',
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
    				'belongsTo' => 'awakesleeping_events[0]',
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
    				//ISPC-2661 Carmen
    				'onchange' => 'if($(this).is(":checked")) {
			    		    	/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").attr("disabled","disabled") ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').hide();
				  			   	$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").attr("disabled","disabled"); */
    							$(this).closest("tr").find(\'input[name*="form_end_date"]\').val("").removeClass("hasDatepicker").hide() ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').hide(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').hide();
				  			   	$(this).closest("tr").find(\'input[name*="form_end_time"]\').val("").removeClass("hasTimepicker").hide();
							}
							else
							{
								/* $(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").removeAttr("disabled") ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').show(); $(this).closest("tr").find(\'input[name*="form_end_date"]\').prev(\'.print_column_first\').find(\'label\').show();
							    $(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").removeAttr("disabled") ; */
    							$(this).closest("tr").find(\'input[name*="form_end_date"]\').addClass("hasDatepicker").show() ; $(this).closest("tr").find(\'img.ui\-datepicker\-trigger:eq(1)\').show();
							    $(this).closest("tr").find(\'input[name*="form_end_time"]\').addClass("hasTimepicker").show() ;
							 }
							 ',
    				//--
    				'title' => 'Ende ungewiss',
    				'style' => 'display: block;',
    		));
	//--
    		$subform->addElement('note', 'Note_awake_sleeping_type_err', array(
    				'value'        => $this->translate('awake_sleeping_date_time_type_err'),
    				'decorators'   => array(
    						'ViewHelper',
    						array(array('data' => 'HtmlTag'), array(
    								'tag' => 'td', 'colspan' => 2,
    						)),
    						array(array('row' => 'HtmlTag'), array(
    								'tag'      => 'tr', 'id' => 'awake_sleeping_date_time_type_error',
    						)),
    				),
    		));

	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	public function save_form_block_awake_sleeping_status ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }

    	if(!$data['contact_form_id'])
    	{
    		//ISPC-2661 pct.13 Carmen 09.09.2020
	    	/* if($data['status_time'] != "")
	    	{
	    		$data['status_time'] = $data['status_time'] . ":00";
	    	}
	    	else
	    	{
	    		$data['status_time'] = '00:00:00';
	    	}
	    		
	    	if($data['status_date'] != "")
	    	{
	    		$data['status_date'] = date('Y-m-d H:i:s', strtotime($data['status_date'] . ' ' . $data['status_time']));
	    	}
	    	else
	    	{
	    		$data['status_date'] = '0000-00-00 00:00:00';
	    	} */
    		$data_to_save = array();
    		$data_to_add = array();
    		$addindex = 0;
    		foreach($data['awakesleeping_events'] as $kev => $vev)
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
    					//ISPC-2661 pct.18 Carmen
    					//$data_to_save['awake_sleep_status'] = $data['awake_sleep_status'];
    					$data_to_save['awake_sleep_status'] = $vev['awake_sleep_status'];
    					//--
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
    					//ISPC-2661 pct.18 Carmen 
    					//$data_to_add[$addindex]['awake_sleep_status'] = $data['awake_sleep_status'];
    					$data_to_add[$addindex]['awake_sleep_status'] = $vev['awake_sleep_status'];
    					//--
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
    				//ISPC-2661 pct.18 Carmen 
    				//$data_to_add[$addindex]['awake_sleep_status'] = $data['awake_sleep_status'];
    				$data_to_add[$addindex]['awake_sleep_status'] = $vev['awake_sleep_status'];
    				//--
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
    		
    	//var_dump($data_to_add); exit;

	    //if not from charts
	   if($data['contact_form_id'])
	   {
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_awakesleepingstatus_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_awakesleepingstatus_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_awakesleepingstatus_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	   }
	   // TODO-4158 Ancuta 26.05.2021
	   else
	   {
	       $this->__save_awakesleepingstatus_patient_course($ipid , $data);
	   }
	   //-- 
	   
	   //ISPC-2661 pct.13 Carmen 09.09.2020
	   if(!empty($data_to_save))
	   {
	    	$entity = FormBlockAwakeSleepingStatusTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data_to_save['id'], $ipid], $data_to_save);
	   }
	   
	   if(!empty($data_to_add))
	   {
		   	$collection = new Doctrine_Collection("FormBlockAwakeSleepingStatus");
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
	private function __save_form_awakesleepingstatus_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || in_array('artificial_entries_exits', $data['__formular']['allowed_blocks'])
	    )
	    {
	        return;
	    }
	     
	     
	    $oldValues = FormBlockAwakeSleepingStatusTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	         
	        unset($oldValues[FormBlockAwakeSleepingStatusTable::getInstance()->getIdentifier()]);
	        	
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
	private function __save_form_awakesleepingstatus_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || ! in_array('awake_sleeping_status', $data['__formular']['allowed_blocks']))
	    {
	        return;
	    }
	     
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	     
	    if ( ! in_array('awake_sleeping_status', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	     
	    
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_awakesleepingstatus_patient_course_format($data);
	   
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	         
	        $oldValues = FormBlockAwakeSleepingStatusTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	        
	        if (empty($oldValues)) {
	             
	            //missing previous values, so we save
	            $save_2_PC = true ;
	             
	        } else {
	             
	            $course_arr_OLD =  $this->__save_form_awakesleepingstatus_patient_course_format($oldValues);
	           
	            if ($course_arr_OLD === $course_arr) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	             
	        }
	         
	    }
	     
	     
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockAwakeSleepingStatusTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
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
	private function __save_form_awakesleepingstatus_patient_course_format($data = [])
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
	private function __save_form_awakesleepingstatus_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockAwakeSleepingStatusTable::getInstance()->createQuery('del')
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
	private function __save_awakesleepingstatus_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data) )
	    {
	        return;
	    }
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $logininfo->userid;
 
	    $cl_opt_details = $this->getColumnMapping('awake_sleep_status');
	    
	    if(empty($data['id'])){
	        $comment = "Ein Wach-Schlaf-Rythmus wurde erfasst";
	    } else{
	        $comment = "Ein Wach-Schlaf-Rythmus wurde geändert";
	    }
	    
	    $cust = new PatientCourse();
	    $cust->ipid = $ipid;
	    $cust->course_date = date("Y-m-d H:i:s", time());
	    $cust->course_type = Pms_CommonData::aesEncrypt('K');
	    $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
	    $cust->tabname = Pms_CommonData::aesEncrypt(addslashes('FormBlockAwakeSleepingStatus'));
	    $cust->user_id = $userid;
	    $cust->save();
	    
	}
	
	
	
	
	
	
}