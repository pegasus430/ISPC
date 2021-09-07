<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 21, 2018
 *
 */
class Application_Form_FormBlockPuncture extends Pms_Form
{
    
    protected $_model = 'FormBlockPuncture';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockPuncture::TRIGGER_FORMID;
    private $triggerformname = FormBlockPuncture::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockPuncture::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    


    public function getColumnMapping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
    
             
        ];
    
    
        $values = FormBlockPunctureTable::getInstance()->getEnumValues($fieldName);
    
         
        $values = array_combine($values, array_map("self::translate", $values));
    
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
    
        return $values;
    
    }
    
    
	public function create_form_puncture ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_puncture");
	    
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    
	    $subform->setLegend('Puncture');
	    //cbrdList is the one for mobile
	    $subform->setAttrib("class", "label_same_size_auto {$__fnName}"); 
	    
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
	                'colspan' => 3,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'class'    =>'dontPrint'
	            )),
	        ),
	    ));
	   
	    
	    $subform->addElement('radio', 'place', array(
	        'value'        => ! empty($values['place']) ? $values['place'] : null,
	        'multiOptions' => $this->getColumnMapping('place'),
// 	        'separator'    => '',
	        'label'        => $this->translate('Puncture'),
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'class' => 'cbrdList',
	                'colspan' => 2,
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tagClass'=>'print_column_first'
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        'labelClass' => 'radios',
	        'labelUnwrapp' => true,
	        'labelPlacement' => 'append',
	    ));
	    
	    $subform->addElement('text', 'testing', array(
	        'value'        => ! empty($values['testing']) ? $values['testing'] : null,
	        
	        'label'        => $this->translate('Puncture testing'),
	        
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 2,	                
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tagClass'=>'print_column_first'
	                
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	            )),
	        ),
	        
	        'data-inputmask'   => "'mask': '9', 'repeat': 10, 'greedy': false",
	        'pattern'          => "[0-9]*"
	    ));
	
	
	    $needles = $this->getColumnMapping('needle');
	    $rows = [];
	    $cnt = 0;
	    
	    foreach ($needles as $needle => $tr) {
	        
	        $row = $this->subFormTableRow();
	        $cb = $subform->createElement('multiCheckbox', 'needle', array(
	            'multiOptions'     => [$needle => $tr],
	            'value'            => ! empty($values['needle']) ? $values['needle'] : null,
	            'label'            => $tr, //$this->translate($needle),
	            'decorators'       => array(
	                'ViewHelper',
	                array('Errors'),
	                
	                
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                    'class'    => 'td_is_block print_column_first',
	                )),
	                
// 	                array('Label', array(
// 	                    'tag' => 'td',
// 	                    'tagClass'=>'print_column_first'
	    
// 	                )),
	                
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr',
	                    'class' => 'cbrdList',
	                    'openOnly' => true,
	                )),
	            ),
	            
	            'labelClass' => "checkboxes",
	            'labelUnwrapp' => true,
	            'labelPlacement' => 'append',
	            
	            
	            'onChange' => 'if (this.checked) {$(this).parents(\'tr\').find(\'.selector_needle_freetext\').removeClass(\'display_none\');} else {$(this).parents(\'tr\').find(\'.selector_needle_freetext\').addClass(\'display_none\')}'
	        )); 
	        $subform->addElement($cb, "cb{$cnt}");
	        
	        $display_none =  in_array($needle, $values['needle']) ? "" : 'display_none';
	        
	        $subformtxt = new Zend_Form_SubForm();
	        $subformtxt->clearDecorators();
	        $subformtxt->setDecorators(array('FormElements'));

	        $txt = $subformtxt->addElement('text', $needle, array(
	            'checkedValue'     => 'yes',
	            'uncheckedValue'   => 'no',
	            'value'            => ! empty($values['needle_freetext'][$this->filterName($needle)]) ? $values['needle_freetext'][$this->filterName($needle)] : null,
	            'decorators'       => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag'      => 'td',
	                    'class'    => "selector_needle_freetext  {$display_none}",
	                    'colspan'  => 2,
	                )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag'          => 'tr',
	                    'closeOnly'    => true,
	                )),
	            ),
	        ));
	        $subformtxt->setElementsBelongTo('needle_freetext');  
	        $subform->addSubForm($subformtxt, "txt{$cnt}");
	        
	        
	        $cnt++ ;
	        
	    }
	    
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
		
	public function save_form_puncture ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_puncture_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_puncture_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_puncture_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	    
	    
	    $entity = FormBlockPunctureTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
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
	private function __save_form_puncture_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data) 
	        || in_array('puncture', $data['__formular']['allowed_blocks'])
	        ) 
	    {
	        return;
	    }
	    
	    
	    $oldValues = FormBlockPunctureTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	     
	    if ( ! empty($oldValues)) {
	        
	        unset($oldValues[FormBlockPunctureTable::getInstance()->getIdentifier()]);
    	    
	        $data = array_merge($data, $oldValues);
	    }
	    
	}

	/**
	 * write or erase the patientcourse text
	 * 
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_puncture_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data) 
	        || ! in_array('puncture', $data['__formular']['allowed_blocks'])) 
	    {
	        return;
	    }
	    
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse 
	    $formular          = $data['__formular'];
	    
	    if ( ! in_array('puncture', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	    
	    
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_puncture_patient_course_format($data);
	    
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	        
    	    $oldValues = FormBlockPunctureTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
    	    
    	    if (empty($oldValues)) { 
    	        
    	        //missing previous values, so we save
    	        $save_2_PC = true ;
    	        
    	    } else {
    	        
    	        $course_arr_OLD =  $this->__save_form_puncture_patient_course_format($oldValues);
    	        
    	        if ($course_arr_OLD === $course_arr) {
    	            //same pc... nothing to insert
    	        } else {
    	            $save_2_PC = true ;
    	        }
    	        
    	    }
	        
	    }
	    
	    
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockPunctureTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
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
            $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockPuncture::PATIENT_COURSE_TABNAME);
        
	    }
	    
	}
	
	
	/**
	 * format the patientcourse title message
	 * 
	 * @param unknown $data
	 * @return multitype:string
	 */
	private function __save_form_puncture_patient_course_format($data = []) 
	{
	    $course_arr = [];
	     
	    if ( ! empty($data['place'])) {
	        $course_arr[] = $this->translate('Puncture') . ": " . $this->translate($data['place']);
	    }
	    if ( ! empty($data['testing'])) {
	        $course_arr[] = $this->translate('Puncture testing') . ": " . $data['testing'];
	    }
	    if ( ! empty($data['needle'])) {
	        // 	        $course_arr[] = $this->translate('Puncture needle') . ": ";
	        foreach ($data['needle'] as $needle) {
	            $course_arr[] = $this->translate($needle) . " " . $data['needle_freetext'][$this->filterName($needle)];
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
	private function __save_form_puncture_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid)) 
	    {
	        FormBlockPunctureTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	        
	        return true;
	    }
	}
	
	
	
}