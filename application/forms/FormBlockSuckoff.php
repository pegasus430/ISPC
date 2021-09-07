<?php
/**
 * 
 * @author carmen
 * Apr 09, 2020 ISPC-2523
 * #ISPC-2512PatientCharts
 */
class Application_Form_FormBlockSuckoff extends Pms_Form
{
    
    protected $_model = 'FormBlockSuckoff';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockSuckoff::TRIGGER_FORMID;
    private $triggerformname = FormBlockSuckoff::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockSuckoff::LANGUAGE_ARRAY;
    
    
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
        
        $values = FormBlockSuckoffTable::getInstance()->getEnumValues($fieldName);
   
        if($fieldName != 'suckoff_consistency' && $fieldName != 'suckoff_color')
        {
        $values = array_combine($values, array_map("self::translate", $values));
       	
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
        }
        if($fieldName == 'suckoff_consistency' || $fieldName == 'suckoff_color')
        {
        	$values_empty[''] = $this->translate('select');
        	$values = $values_empty+ $values;
        }
        
        
        return $values;
    
    }
    
    
    
    
	public function create_form_block_suckoff ($values =  array() , $elementsBelongTo = null)
	{
// 	    dd($values);
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_block_suckoff");
	
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend($this->translate('suckoff'));
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
		    
		    $subform->addElement('text', 'suckoff_secretion', array(
		    		'label'        => self::translate('suckoff_secretion'),
		    		'value'        => $values['suckoff_secretion'],
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		'placeholder'  => 'ml',
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'style' => $display )),
		    		),
		    		 
		    ));
		    
		    $suckoff_consistency_index = array_search($values['suckoff_consistency'], $this->getColumnMapping('suckoff_consistency'));
		    $subform->addElement('select', 'suckoff_consistency', array(
    			'label' 	   => self::translate('suckoff_consistency'),
    			'multiOptions' => $this->getColumnMapping('suckoff_consistency'),
    			'value'        => $suckoff_consistency_index,
    			'required'     => false,
    			'filters'      => array('StringTrim'),
    			'decorators' =>   array(
    					'ViewHelper',
    					array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first required')),
    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			),
		    		'onChange' => 'if ($(this).val() == "4") { $(".suckoff_consistency_more", $(this).parents("table")).show();} else {$(".suckoff_consistency_more", $(this).parents("table")).hide();}',
    		));
		    
		    $display = $suckoff_consistency_index != 4 ? 'display:none' : null;
		    $subform->addElement('text', 'suckoff_consistency_text', array(
		    		'label' 	   => self::translate('suckoff_consistency_text'),
		    		'value'        => $values['suckoff_consistency_text'],
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		//'placeholder'  => $this->translate('freetext'),
		    		'decorators'   => array(
		    				'ViewHelper',
		    				array('Errors'),
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr', 'class'=>'suckoff_consistency_more', 'style' => $display )),
		    		),
		    	  
		    ));
		    
		    $suckoff_color_index = array_search($values['suckoff_color'], $this->getColumnMapping('suckoff_color'));
		    $subform->addElement('select', 'suckoff_color', array(
		    		'label' 	   => self::translate('suckoff_color'),
		    		'multiOptions' => $this->getColumnMapping('suckoff_color'),
		    		'value'        => $suckoff_color_index,
		    		'required'     => false,
		    		'filters'      => array('StringTrim'),
		    		'decorators' =>   array(
		    				'ViewHelper',
		    				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
		    				array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first')),
		    				array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		    		),
		    ));
		    
		    $subform->addElement('text', 'suckoff_date', array(
		    		'label'        => self::translate('suckoff_date'),
		    		'value'        => ! empty($values['suckoff_date']) ? date('d.m.Y', strtotime($values['suckoff_date'])) : date('d.m.Y'),
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
		     
		    $suckoff_time = ! empty($values['suckoff_date']) ? date('H:i:s', strtotime($values['suckoff_date'])) : date("H:i");
		    $subform->addElement('text', 'suckoff_time', array(
		    		//'label'        => self::translate('clock:'),
		    		'value'        => $suckoff_time,
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
		    ));
	
		    //ISPC-2523 Lore 14.05.2020
		    $subform->addElement('checkbox', 'suckoff_soothing', array(
		        'value'        => $values['suckoff_soothing'],
		        'label'        => self::translate('suckoff_soothing'),
		        'required'   => false,
		        'decorators' => array(
		            'ViewHelper',
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
		            array('Label', array('tag' => 'td')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		        ),
		    ));
		    
		    $subform->addElement('checkbox', 'suckoff_possible', array(
		        'value'        => $values['suckoff_possible'],
		        'label'        => self::translate('suckoff_possible'),
		        'required'   => false,
		        'decorators' => array(
		            'ViewHelper',
		            array('Errors'),
		            array(array('data' => 'HtmlTag'), array('tag' => 'td', 'colspan'=>3)),
		            array('Label', array('tag' => 'td')),
		            array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
		        ),
		    ));
		    //.

	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	public function save_form_block_suckoff ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }

    	if(!$data['contact_form_id'])
    	{
    		//$data['suckoff_date'] = date('Y-m-d H:i:s', time());
    		if($data['suckoff_time'] != "")
    		{
    			$data['suckoff_time'] = $data['suckoff_time'] . ":00";
    		}
    		else
    		{
    			$data['suckoff_time'] = '00:00:00';
    		}
    		 
    		if($data['suckoff_date'] != "")
    		{
    			$data['suckoff_date'] = date('Y-m-d H:i:s', strtotime($data['suckoff_date'] . ' ' . $data['suckoff_time']));
    		}
    		else
    		{
    			$data['suckoff_date'] = '0000-00-00 00:00:00';
    		}
	    	
    	}
    	$data['ipid'] = $ipid;

	    	
	    //var_dump($data); exit;
	    //if not from charts
	   if($data['contact_form_id'])
	   {
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_suckoff_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_suckoff_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_suckoff_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	   }
	   // TODO-4158 Ancuta 26.05.2021
	   else
	   {
	       $this->__save_suckoff_patient_course($ipid , $data);
	   }
	   
	   
	    $entity = FormBlockSuckoffTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
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
	private function __save_form_suckoff_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || in_array('suckoff', $data['__formular']['allowed_blocks'])
	    )
	    {
	        return;
	    }
	     
	     
	    $oldValues = FormBlockSuckoffTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	         
	        unset($oldValues[FormBlockSuckoffTable::getInstance()->getIdentifier()]);
	        	
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
	private function __save_form_suckoff_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || ! in_array('suckoff', $data['__formular']['allowed_blocks']))
	    {
	        return;
	    }
	     
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	     
	    if ( ! in_array('suckoff', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	     
	    
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_suckoff_patient_course_format($data);
	   
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	         
	        $oldValues = FormBlockSuckoffTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	        
	        if (empty($oldValues)) {
	             
	            //missing previous values, so we save
	            $save_2_PC = true ;
	             
	        } else {
	             
	            $course_arr_OLD =  $this->__save_form_suckoff_patient_course_format($oldValues);
	           
	            if ($course_arr_OLD === $course_arr) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	             
	        }
	         
	    }
	     
	     
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockSuckoffTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
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
	private function __save_form_suckoff_patient_course_format($data = [])
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
	private function __save_form_suckoff_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockSuckoffTable::getInstance()->createQuery('del')
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
	private function __save_suckoff_patient_course($ipid =  null , $data =  array())
	{
	    
	    if (empty($ipid) || empty($data) )
	    {
	        return;
	    }
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $userid = $logininfo->userid;

	    
	    if(empty($data['id'])){
	        $comment = "Eine Absaugung wurde durchgeführt";
	    } else{
	        $comment = "Eine Absaugung wurde geändert";
	    }
	    
	    $cust = new PatientCourse();
	    $cust->ipid = $ipid;
	    $cust->course_date = date("Y-m-d H:i:s", time());
	    $cust->course_type = Pms_CommonData::aesEncrypt('K');
	    $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
	    $cust->tabname = Pms_CommonData::aesEncrypt(addslashes('FormBlockSuckoff'));
	    $cust->user_id = $userid;
	    $cust->save();
	    
	}
	
	
	
	
	
	
	
	
}