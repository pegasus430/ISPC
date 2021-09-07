<?php
/**
 * 
 * @author carmen
 * Jan 22, 2020 ISPC-2508
 * Ancuta added to Clinic ISPC (CISPC)  on 18.03.2020
 */
class Application_Form_FormBlockArtificialEntriesExits extends Pms_Form
{
    
    protected $_model = 'FormBlockArtificialEntriesExits';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockArtificialEntriesExits::TRIGGER_FORMID;
    private $triggerformname = FormBlockArtificialEntriesExits::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockArtificialEntriesExits::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    
	protected $_patient_artificial_options = array();
	protected $_client_artificial_options = array();
	
    public function __construct($options = null)
    {
    	if($options['_patient_artificial_options'])
    	{
    			
    		$this->_patient_artificial_options = $options['_patient_artificial_options'];
    		unset($options['_patient_artificial_options']);
    	}
    
    	if($options['_client_artificial_options'])
    	{
    		 
    		$this->_client_artificial_options = $options['_client_artificial_options'];
    		unset($options['_client_artificial_options']);
    	}
    	//var_dump($this->_client_artificial_options); exit;
    	parent::__construct($options);
    
    }


    public function getColumnMapping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
            'option_status' => array('ok' => 'in Ordnung', 'not ok' => 'Nicht in Ordnung')
    
        ];
    
    
        $values = FormBlockArtificialEntriesExitsTable::getInstance()->getEnumValues($fieldName);
    
         
        $values = array_combine($values, array_map("self::translate", $values));
    
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
    
        return $values;
    
    }
    
    
    
    
	public function create_form_block_artificial_entries_exits ($values =  array() , $elementsBelongTo = null)
	{
// 	    dd($values);
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_block_artificial_entries_exits");
	
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend('Künstliche Zugänge - Ausgänge');
	    $subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
	    
	    $this->__setElementsBelongTo($subform, $elementsBelongTo);
	    if(!empty($values))
	    {
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
		                //'colspan' => 2,
		            )),
		            array(array('row' => 'HtmlTag'), array(
		                'tag' => 'tr',
		                'class'    => 'dontPrint',
		            )),
		        ),
		    ));
	
		    $subformartificial = new Zend_Form_SubForm();
		    $subformartificial->clearDecorators()
		    ->setDecorators( array(
		    		'FormElements',
		    		array(array('data' => 'HtmlTag'), array(
		    				'tag' => 'table',
		    				'class' => 'SimpleTable artificial_table',
		    				'id' => 'artificial_content',
		    				'cellpadding'=>"2",
		    				'cellspacing'=>"0",
		    		)),
		    		 
		    ));
	
		    $subformartificial->addElement('note', 'NoteOptionname', array(
		    		'value'        => $this->translate('artificial_option_name'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    				)),
		    				array(array('row' => 'HtmlTag'), array(
		    						'tag'      => 'tr',
		    						'openOnly' => true,
		    				)),
		    		),
		    ));
		    $subformartificial->addElement('note', 'NoteOptionDate', array(
		    		'value'        => $this->translate('artificial_option_date'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    				)),
		    		),
		    ));
		    
		    $subformartificial->addElement('note', 'NoteOptionAge', array(
		    		'value'        => $this->translate('artificial_option_age'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    				)),
		    				
		    		),
		    ));
		    
		    /* $subformartificial->addElement('note', 'NoteOptionContactformDate', array(
		    		'value'        => $this->translate('artificial_contactform_date'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    				)),
		    		),
		    )); */
		    
		    $subformartificial->addElement('note', 'NoteOptionStatus', array(
		    		'value'        => $this->translate('artificial_option_status'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    				)),
		    				
		    		),
		    ));
		    
		    $subformartificial->addElement('note', 'NoteOptionComment', array(
		    		'value'        => $this->translate('artificial_option_comment'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td',
		    				)),
		    		),
		    ));
		    $subformartificial->addElement('note', 'NoteActions', array(
		    		'value'        => $this->translate('actions'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag' => 'td', 'class' => "dontPrint",
		    				)),
		    				array(array('row' => 'HtmlTag'), array(
		    						'tag'      => 'tr',
		    						'closeOnly' => true
		    				)),
		    		),
		    ));
		    //var_dump($values); exit;
		    unset($values['id']);
		    foreach($values as $krow => $vrow)
		    {
		    	$extra_row = $this->subFormTableRow(['class' => "", 'id' => 'fpart_'.$vrow['patient_option_id']]);
		    	
		    	 $extra_row->addElement('hidden', 'patient_option_id', array(
		    			'label'        => null,
		    			'value'        => ! empty($vrow['patient_option_id']) ? $vrow['patient_option_id'] : '',
		    			'required'     => false,
		    			'readonly'     => true,
		    			'filters'      => array('StringTrim'),
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td',
		    							'openOnly' => true,
		    					)),
		    			),
		    	));
		    	 
		    	 $extra_row->addElement('hidden', 'action_option', array(
		    	 		'label'        => null,
		    	 		'value'        => '',
		    	 		'required'     => false,
		    	 		'readonly'     => true,
		    	 		'filters'      => array('StringTrim'),
		    	 		'decorators'   => array(
		    	 				'ViewHelper',	
		    	 		),
		    	 		'id' => 'action_'.$vrow['patient_option_id'],
		    	 ));
		    	 
		    	 $extra_row->addElement('hidden', 'option_id', array(
		    	 		'label'        => null,
		    	 		'value'        => $vrow['option_id'],
		    	 		'required'     => false,
		    	 		'readonly'     => true,
		    	 		'filters'      => array('StringTrim'),
		    	 		'decorators'   => array(
		    	 				'ViewHelper',
		    	 		),
		    	 		'id' => 'option_id_'.$vrow['patient_option_id'],
		    	 ));
		    	 
		    	 $extra_row->addElement('hidden', 'option_date', array(
		    	 		'label'        => null,
		    	 		'value'        => $vrow['option_date'],
		    	 		'required'     => false,
		    	 		'readonly'     => true,
		    	 		'filters'      => array('StringTrim'),
		    	 		'decorators'   => array(
		    	 				'ViewHelper',
		    	 		),
		    	 		'id' => 'option_date_'.$vrow['patient_option_id'],
		    	 ));
		    	 
		    	 $extra_row->addElement('hidden', 'remove_date', array(
		    	 		'label'        => null,
		    	 		'value'        => '',
		    	 		'required'     => false,
		    	 		'readonly'     => true,
		    	 		'filters'      => array('StringTrim'),
		    	 		'decorators'   => array(
		    	 				'ViewHelper',
		    	 		),
		    	 		'id' => 'remove_date_'.$vrow['patient_option_id'],
		    	 ));
		    	 
		    	 $extra_row->addElement('hidden', 'option_localization', array(
		    	 		'label'        => null,
		    	 		'value'        => $vrow['option_localization'],
		    	 		'required'     => false,
		    	 		'readonly'     => true,
		    	 		'filters'      => array('StringTrim'),
		    	 		'decorators'   => array(
		    	 				'ViewHelper',
		    	 		),
		    	 		'id' => 'option_localization_'.$vrow['patient_option_id'],
		    	 ));
		    	 
		    	 $extra_row->addElement('hidden', 'option_availability', array(
		    	 		'label'        => null,
		    	 		'value'        => $vrow['option_availability'],
		    	 		'required'     => false,
		    	 		'readonly'     => true,
		    	 		'filters'      => array('StringTrim'),
		    	 		'decorators'   => array(
		    	 				'ViewHelper',
		    	 		),
		    	 		'id' => 'option_availability_'.$vrow['patient_option_id'],
		    	 ));
		    	 	
		    	$extra_row->addElement('note', 'Noteoptnamevalue', array(
		    			'value'        => ! empty($vrow['option_name']) ? $vrow['option_name'] : '',
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('tagname' => 'HtmlTag'), array(
		    							'tag'      => 'span',
		    					)),
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td',
		    							'closeOnly' => true
		    					)),
		    			),
		    	));
		    	
		    	$extra_row->addElement('note', 'Noteoptdatevalue', array(
		    			'value'        => ! empty($vrow['option_date']) ? date('d.m.Y', strtotime($vrow['option_date'])) : '',
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td',
		    					)),
		    			),
		    	));
		    	
		    	if($vrow['option_age'] > 0)
		    	{
			    	$extra_row->addElement('note', 'Noteoptagevalue', array(
			    			'value'        => ($vrow['option_availability'] > 0 && $vrow['option_age'] > $vrow['option_availability']) ? '<span><font style="color:red;">!</font></span> '.$vrow['option_age'] . ' '. $this->translate('days') : $vrow['option_age'] . ' ' .$this->translate('days'),
			    			'decorators'   => array(
			    					'ViewHelper',
			    					array(array('data' => 'HtmlTag'), array(
			    							'tag'      => 'td',
			    					)),
			    			),
			    	));
		    	}
		    	else
		    	{
		    		$extra_row->addElement('note', 'Noteoptagevalue', array(
		    				'value'        => $this->translate('today new'),
		    				'decorators'   => array(
		    						'ViewHelper',
		    						array(array('data' => 'HtmlTag'), array(
		    								'tag'      => 'td',
		    						)),
		    				),
		    		));
		    	}
		    	
		    	/* $extra_row->addElement('note', 'Noteoptcontactformdatevalue', array(
		    			'value'        => ! empty($vrow['option_contactform_date']) ? date('d.m.Y', strtotime($vrow['option_contactform_date'])) : '',
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td', 'class' => 'contactformdate'
		    					)),
		    			),
		    	)); */
		    	
		    	$extra_row->addElement('radio', 'option_status', array(
		    			'multiOptions'=> $this->getColumnMapping('option_status'),
		    			'value'        => isset($vrow['option_status']) ? $vrow['option_status'] : null,
		    			//'separator' => '&nbsp;',
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td',
		    					)),
		    			),
		    		  
		    	));
		    	
		    	$extra_row->addElement('textarea', 'option_comment', array(
		    			'value'        => isset($vrow['option_comment']) ? $vrow['option_comment'] : null,
		    			'required'     => false,
		    			'filters'      => array('StringTrim'),
		    			'validators'   => array('NotEmpty'),
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td',
		    					)),
		    			),
		    			'style' => "width: 95%; overflow: hidden; height: 44px;",
		    			 
		    	));
		    	//ISPC-2508 Carmen 21.05.2020 new design
		    	$extra_row->addElement('note', 'Noteoptcontactformactions', array(
		    			
		    			'value'        => /* '<span class="edit_patient_artificial_setting" data-action ="edit" data-setting_id = "' . $vrow['patient_option_id'] . '"><img title="'.$this->translate("edit").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /></span>'
		    							  .'<span class="set_fromform_patient_artificial_setting" data-action ="remove" data-setting_id = "' . $vrow['patient_option_id'] . '"><img title="'.$this->translate("notneeded").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_remove.png" /></span>'
		    							  .'<span class="edit_patient_artificial_setting" data-action="refresh" data-setting_id = "' . $vrow['patient_option_id'] . '"><img title="'.$this->translate("refresh").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_renew.png" /></span>'
		    							  .'<span class="set_fromform_patient_artificial_setting" data-action="delete" data-setting_id = "' . $vrow['patient_option_id'] . '"><img title="'.$this->translate("delete").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_delete.png" /></span>', */
		    			'<span class="patient_contactform_actions_modal_button" data-recid = "' . $vrow['patient_option_id'] . '"><img title="'.$this->translate("edit").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/edit.png" /></span>',
		    			
		    			'decorators'   => array(
		    					'ViewHelper',
		    					array(array('data' => 'HtmlTag'), array(
		    							'tag'      => 'td', 'class' => "dontPrint",
		    					)),
		    			),
		    	));
		    	//--
		    	
		    	$subformartificial->addSubForm($extra_row, $krow);
		    }
		    
			$krow++;
		    $extra_row = $this->subFormTableRow(['class' => "ibutton addbutton"]);
		     
		    $extra_row->addElement('note', 'add_new_entry_exit', array(
		    		//'belongsTo' => 'set['.$krow.']',
		    		'value' => '<span class="ibutton edit_patient_artificial_setting" data-action="edit" data-setting_id = ""><img src="'.RES_FILE_PATH.'/images/btttt_plus.png" style="display: block; float: left; margin-right: 5px;"/>'.$this->translator->translate('block_artificial_entries_exits_add').'</span>',
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array(
		    						'tag'      => 'td', 'class' => "dontPrint", 'colspan' => '6',
		    				)),
		    		),
		    ));
		
		    $subformartificial->addSubForm($extra_row, $krow);
		    $subform->addSubForm($subformartificial, 'artificial_content');
	    }
	    else 
	    {
	    	$subformartificial = new Zend_Form_SubForm();
	    	$subformartificial->clearDecorators()
	    	->setDecorators( array(
	    			'FormElements',
	    			array(array('data' => 'HtmlTag'), array(
	    					'tag' => 'table',
	    					'class' => 'SimpleTable artificial_table',
	    					'id' => 'artificial_content',
	    					'cellpadding'=>"2",
	    					'cellspacing'=>"0",
	    			)),
	    			 
	    	));
	    	$subformartificial->addElement('note', 'Noteemptyblock', array(
	    			'value'        => $this->translate('no_patient_artificial_entries_exits_seetings'),
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag'      => 'td',
	    					)),
	    					array(array('row' => 'HtmlTag'), array(
	    							'tag'      => 'tr',
	    							'id' => 'no_entries',
	    					)),
	    			),
	    	));
	    	$subformartificial->addElement('note', 'add_new_entry_exit', array(
	    			//'belongsTo' => 'set['.$krow.']',
	    			'value' => '<span class="ibutton edit_patient_artificial_setting" data-action="edit" data-setting_id = ""><img src="'.RES_FILE_PATH.'/images/btttt_plus.png" style="display: block; float: left; margin-right: 5px;"/>'.$this->translator->translate('block_artificial_entries_exits_add').'</span>',
	    			'decorators'   => array(
	    					'ViewHelper',
	    					array(array('data' => 'HtmlTag'), array(
	    							'tag'      => 'td',
	    							'colspan' => '6',
	    					)),
	    					array(array('row' => 'HtmlTag'), array(
	    							'tag'      => 'tr',
	    					)),
	    			),
	    	));
	    	
	    	$subform->addSubForm($subformartificial, 'artificial_content');
	    }
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	public function create_form_block_artificial_entries_exits_row ($values =  array() , $elementsBelongTo = null)
	{
		$subform = new Zend_Form_SubForm();
		$subform->clearDecorators()
		->setDecorators( array(
				'FormElements',
				//array('HtmlTag',array('tag'=>'table', 'class' => 'formular_actions', 'style' => 'border: 1px solid #000;')),
		));
		$this->__setElementsBelongTo($subform, $elementsBelongTo);
		
		$patient_option_id = $values['pat_opt_id'];
		
		//$extra_row = $this->subFormTableRow(['class' => "", 'id' => 'fpart_'.$patient_option_id]);
		 
		$subform->addElement('hidden', 'patient_option_id', array(
				'label'        => null,
				'value'        => '',
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag'      => 'td',
								'openOnly' => true,
						)),
						array(array('row' => 'HtmlTag'), array(
								'tag'      => 'tr',
								'openOnly' => true,
								'id' => 'fpart_'.$patient_option_id,
						)),
				),
		));
		
		$subform->addElement('hidden', 'action_option', array(
				'label'        => null,
				'value'        => '',
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
				),
				'id' => 'action_'.$patient_option_id,
		));
		
		$subform->addElement('hidden', 'option_id', array(
				'label'        => null,
				'value'        => $vrow['option_id'],
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
				),
				'id' => 'option_id_'.$patient_option_id,
		));
		
		$subform->addElement('hidden', 'option_date', array(
				'label'        => null,
				'value'        => $vrow['option_date'],
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
				),
				'id' => 'option_date_'.$patient_option_id,
		));
		
		$subform->addElement('hidden', 'option_localization', array(
				'label'        => null,
				'value'        => $vrow['option_localization'],
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
				),
				'id' => 'option_localization_'.$patient_option_id,
		));
		
		$subform->addElement('hidden', 'option_availability', array(
				'label'        => null,
				'value'        => $vrow['option_availability'],
				'required'     => false,
				'readonly'     => true,
				'filters'      => array('StringTrim'),
				'decorators'   => array(
						'ViewHelper',
				),
				'id' => 'option_availability_'.$patient_option_id,
		));
		 
		$subform->addElement('note', 'Noteoptnamevalue', array(
				'value'        => '',
				'decorators'   => array(
						'ViewHelper',
						array(array('tagname' => 'HtmlTag'), array(
								'tag'      => 'span',
						)),
						array(array('data' => 'HtmlTag'), array(
								'tag'      => 'td',
								'closeOnly' => true
						)),
				),
		));
		 
		$subform->addElement('note', 'Noteoptdatevalue', array(
				'value'        => '',
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag'      => 'td',
						)),
				),
		));
		 
		
		$subform->addElement('note', 'Noteoptagevalue', array(
				'value'        => '',
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag'      => 'td',
						)),
				),
		));
		 
		/* $extra_row->addElement('note', 'Noteoptcontactformdatevalue', array(
		 'value'        => ! empty($vrow['option_contactform_date']) ? date('d.m.Y', strtotime($vrow['option_contactform_date'])) : '',
		 'decorators'   => array(
		 'ViewHelper',
		 array(array('data' => 'HtmlTag'), array(
		 'tag'      => 'td', 'class' => 'contactformdate'
		 )),
		 ),
		 )); */
		 
		$subform->addElement('radio', 'option_status', array(
				'multiOptions'=> $this->getColumnMapping('option_status'),
				'value'        => null,
				//'separator' => '&nbsp;',
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag'      => 'td',
						)),
				),
		
		));
		 
		$subform->addElement('textarea', 'option_comment', array(
				'value'        => null,
				'required'     => false,
				'filters'      => array('StringTrim'),
				'validators'   => array('NotEmpty'),
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag'      => 'td',
						)),
				),
				'style' => "width: 95%; overflow: hidden; height: 44px;",
		
		));
		 
		$subform->addElement('note', 'Noteoptcontactformactions', array(
				 
				'value'        => '<span class="delete_new_entry_exit"><img title="'.$this->translate("delete").'" width="16" height="16" border="0" src="'.RES_FILE_PATH.'/images/action_delete.png" /></span>',
				'decorators'   => array(
						'ViewHelper',
						array(array('data' => 'HtmlTag'), array(
								'tag'      => 'td', 'class' => "dontPrint",
						)),
						array(array('row' => 'HtmlTag'), array(
								'tag'      => 'tr',
								'closeOnly' => true,
								'id' => 'fpart_'.$patient_option_id,
						)),
				),
		));
		 
		//$subform->addSubForm($extra_row, $patient_option_id);
		
		return $subform;
	}
	
	
	
	public function save_form_block_artificial_entries_exits ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    //var_dump($data); exit;
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_artificialentriesexits_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_artificialentriesexits_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_artificialentriesexits_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	   
	    foreach($data['artificial_content'] as $kr => $vr)
	    {
	    	unset($data['artificial_content'][$kr]['action_option']);
	    	unset($data['artificial_content'][$kr]['option_id']);
	    	unset($data['artificial_content'][$kr]['option_date']);
	    	unset($data['artificial_content'][$kr]['option_localization']);
	    	unset($data['artificial_content'][$kr]['option_availability']);
	    }
	    
	    $entity = FormBlockArtificialEntriesExitsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
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
	private function __save_form_artificialentriesexits_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || in_array('artificial_entries_exits', $data['__formular']['allowed_blocks'])
	    )
	    {
	        return;
	    }
	     
	     
	    $oldValues = FormBlockArtificialEntriesExitsTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	         
	        unset($oldValues[FormBlockArtificialEntriesExitsTable::getInstance()->getIdentifier()]);
	        	
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
	private function __save_form_artificialentriesexits_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || ! in_array('artificial_entries_exits', $data['__formular']['allowed_blocks']))
	    {
	        return;
	    }
	     
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	     
	    if ( ! in_array('artificial_entries_exits', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	     
	    
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_artificialentriesexits_patient_course_format($data);
	   
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	         
	        $oldValues = FormBlockArtificialEntriesExitsTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	        foreach($oldValues['artificial_content'] as $kr => &$vr)
	        {
	        	$vr['option_id'] = $this->_patient_artificial_options[$kr]['option_id'];
	        }
	       
	        if (empty($oldValues)) {
	             
	            //missing previous values, so we save
	            $save_2_PC = true ;
	             
	        } else {
	             
	            $course_arr_OLD =  $this->__save_form_artificialentriesexits_patient_course_format($oldValues);
	           
	            if ($course_arr_OLD === $course_arr) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	             
	        }
	         
	    }
	     
	     
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockArtificialEntriesExitsTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
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
	private function __save_form_artificialentriesexits_patient_course_format($data = [])
	{
	    $course_arr = [];
	   
	    foreach($data['artificial_content'] as $ko => $vo)
	    {
	    	if(!empty($vo['option_status']) || !empty($vo['option_comment']))
	    	{
			    if ( ! empty($vo['option_status'])) {
			        $course_arr[$ko] = $this->_client_artificial_options[$vo['option_id']]['name'] . '|' . date('d.m.Y', strtotime($vo['option_date'])) . '|' . $this->getColumnMapping('option_status')[$vo['option_status']];
			    }
			    else 
			    {
			    	$course_arr[$ko] = $this->_client_artificial_options[$vo['option_id']]['name'] . '|' . date('d.m.Y', strtotime($vo['option_date']));
			    }
			    if ( ! empty($vo['option_comment'])) {
			    	$course_arr[$ko] .= '|'. $vo['option_comment'];
			    }
	    	}
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
	private function __save_form_artificialentriesexits_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockArtificialEntriesExitsTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	         
	        return true;
	    }
	}
	
	
	
	
	
	
	
	
	
}