<?php
/**
 * 
 * @author claudiu✍ 
 * Dec 21, 2018
 *
 */
class Application_Form_FormBlockAdverseevents extends Pms_Form
{
    
    protected $_model = 'FormBlockAdverseevents';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockAdverseevents::TRIGGER_FORMID;
    private $triggerformname = FormBlockAdverseevents::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockAdverseevents::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    


    public function getColumnMapping($fieldName, $revers = false)
    {
    
        //             $fieldName => [ value => translation]
        $overwriteMapping = [
            'today' => ['yes' =>  'Ja', 'no' =>  'Nein'],
            'lastperiod' => ['yes' =>  'Ja', 'no' =>  'Nein'],
        ];
    
    
        $values = FormBlockAdverseeventsTable::getInstance()->getEnumValues($fieldName);
    
         
        $values = array_combine($values, array_map("self::translate", $values));
    
        if (isset($overwriteMapping[$fieldName])) {
            $values = $overwriteMapping[$fieldName] + $values;
        }
    
        return $values;
    
    }

    
    
	public function create_form_adverseevents ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_adverseevents");
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend('Adverseevents');
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
	                'colspan' => 5,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'class'    => 'dontPrint',
	            )),
	        ),
	    ));
	
	   
	    
	    $subform->addElement('radio', 'today', array(
	        'value'        => ! empty($values['today']) ? $values['today'] : null,
	        'multiOptions' => $this->getColumnMapping('today'),
// 	        'separator'    => '',
	        'label'        => "Did infusion-related reactions occur today?",
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'colspan'  => 2,
	                'class'    => 'cbrdList',
	            )),
	            array('Label', array(
	                'tag' => 'td', 
	                'tagClass'=>'print_column_first'
	            )),      
	            array(array('row' => 'HtmlTag'), array(
	                'tag'      => 'tr',
	            )),
	        ),
	        
	        'labelClass' => "",
	        'labelUnwrapp' => true,
	        'labelPlacement' => 'append',
	        
    	    'onChange' => 'if (this.value == "yes") {$(".selector_today_comment", $(this).parents(\'table\')).removeClass(\'display_none\');} else {$(".selector_today_comment", $(this).parents(\'table\')).addClass(\'display_none\');}',
	    ));
	    
	    
	    $display_none = $values['today'] == 'yes' ? '' : "display_none";
	    
	    
	    $subform->addElement('textarea', 'today_comment', array(
	        'value'        => ! empty($values['today_comment']) ? $values['today_comment'] : null,
	        
	        'placeholder'        => $this->translate("If yes, please specify here and fill out AE registration form!"),
	        
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3,	                
	            )),   
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'class' => "selector_today_comment {$display_none}",
	            )),
	        ),
	        'rows' => 3,
	    ));
	    $subform->addElement('radio', 'lastperiod', array(
	        'value'        => ! empty($values['lastperiod']) ? $values['lastperiod'] : null,
	        'multiOptions' => $this->getColumnMapping('lastperiod'),
// 	        'separator'    => '',
	        'label'        => "Did any adverse events occur during the last period of time to the last infusion?",
	        'decorators' => array(
	            'ViewHelper',
	            array('Errors'),
	            array(array('data' => 'HtmlTag'), array(
	                'tag'      => 'td',
	                'colspan'  => 2,
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
	        
	        'onChange' => 'if (this.value == "yes") {$(".selector_lastperiod_comment", $(this).parents(\'table\')).removeClass(\'display_none\');} else {$(".selector_lastperiod_comment", $(this).parents(\'table\')).addClass(\'display_none\');}',
	         
	    ));
	    
	    $display_none = $values['lastperiod'] == 'yes' ? '' : "display_none";
	    
	    $subform->addElement('textarea', 'lastperiod_comment', array(
	        'value'        => ! empty($values['lastperiod_comment']) ? $values['lastperiod_comment'] : null,
	        
	        'placeholder'  => $this->translate("If yes, please specify here and fill out AE registration form!"),
	        
	        'required'     => false,
	        'filters'      => array('StringTrim'),
	        'validators'   => array('NotEmpty'),
	        'decorators'   => array(
	            'ViewHelper',
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 3,	                
	            )),
	            
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'class' => "selector_lastperiod_comment {$display_none}",
	            )),
	        ),
	        'rows' => 3,
	    ));
	
	
	    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	
	
	
	
	
	
	
	
	public function save_form_adverseevents ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_adverseevents_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_adverseevents_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_adverseevents_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	    
	    
	    $entity = FormBlockAdverseeventsTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
	    
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
	private function __save_form_adverseevents_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || in_array('adverseevents', $data['__formular']['allowed_blocks'])
	    )
	    {
	        return;
	    }
	
	
	    $oldValues = FormBlockAdverseeventsTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	
	        unset($oldValues[FormBlockAdverseeventsTable::getInstance()->getIdentifier()]);
	
	        $data = array_merge($data, $oldValues);
	    }
	
	}
	
	/**
	 * write or erase the patientcourse text
	 * // Maria:: Migration ISPC to CISPC 08.08.2020	
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_adverseevents_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)
	        || ! in_array('adverseevents', $data['__formular']['allowed_blocks']))
	    {
	        return;
	    }
	
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	
	    if ( ! in_array('adverseevents', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	
	
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_adverseevents_patient_course_format($data);
	
	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	
	        $oldValues = FormBlockAdverseeventsTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	        if (empty($oldValues)) {
	
	            //missing previous values, so we save
	            $save_2_PC = true ;
	
	        } else {
	
	            $course_arr_OLD =  $this->__save_form_adverseevents_patient_course_format($oldValues);
	
	            if ($course_arr_OLD === $course_arr) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	
	        }
	
	    }
	
	
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockAdverseeventsTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
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
	        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockAdverseevents::PATIENT_COURSE_TABNAME);
	
	    }
	
	}
	
	
	/**
	 * format the patientcourse title message
	 *
	 * @param unknown $data
	 * @return multitype:string
	 */
	private function __save_form_adverseevents_patient_course_format($data = [])
	{
	    $course_arr = [];
	    

	    if ( ! empty($data['today'])) {
	         
	        $course_arr[] = $this->translate('Did infusion-related reactions occur today?')
	        . ": " . ($data['today'] == "yes" ? "Ja" : "Nein")
	        . ($data['today'] == "yes" && ! empty($data['today_comment']) ? "; {$data['today_comment']}" : '');
	    }
	     
	    if ( ! empty($data['lastperiod'])) {
	    
	        $course_arr[] = $this->translate('Did any adverse events occur during the last period of time to the last infusion?')
	        . ": " . ($data['lastperiod'] == "yes" ? "Ja" : "Nein")
	        . ($data['lastperiod'] == "yes" && ! empty($data['lastperiod_comment']) ? "; {$data['lastperiod_comment']}" : '');
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
	private function __save_form_adverseevents_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockAdverseeventsTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	
	        return true;
	    }
	}
	
	
	
}