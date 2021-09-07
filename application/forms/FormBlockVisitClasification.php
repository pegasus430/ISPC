<?php
/**
 * 
 * @author Ancuta 
 * 23.05.2019
 *
 */
class Application_Form_FormBlockVisitClasification extends Pms_Form
{
    
    protected $_model = 'FormBlockVisitClasification';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockVisitClasification::TRIGGER_FORMID;
    private $triggerformname = FormBlockVisitClasification::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockVisitClasification::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    


    private function __visit_clasificationColumns()
    {
        $visit_clasification = array(
            //0 => 'doc_travel_time', // translate :  clasification_doc_travel_time
            0 => 'assessment', // translate :  clasification_assessment
            1 => 'consultation', // translate :  clasification_consultation
            2 => 'training', 	//translate: clasification_training
            3 => 'maintenance_check', //translate: clasification_maintenance_check
            4 => 'monitoring', //translate: clasification_monitoring
            5 => 'applications_opposition', //translate: clasification_applications_opposition
            6 => 'nurse_home_visit', //translate: clasification_nurse_home_visit
            7 => 'other', //translate: clasification_other
            8 => 'medication_plan_check', //translate: clasification_medication_plan_check  ISPC-2387 - Lore 14.08.2019
        );
         
        return $visit_clasification;
    }
    private function __visit_clasificationShortcuts()
    {
        $visit_clasification = array(
            'assessment'=>'MA', // translate :  clasification_assessment
            'consultation'=>'MB', // translate :  clasification_consultation
            'training'=>'MS', 	//translate: clasification_training
            'maintenance_check'=>'MC', //translate: clasification_maintenance_check
            'monitoring'=>'MM', //translate: clasification_monitoring
            'applications_opposition'=>'MW', //translate: clasification_applications_opposition
            'nurse_home_visit'=>'MP', //translate: clasification_nurse_home_visit
            'other'=>'MT', //translate: clasification_other
            'medication_plan_check'=>'MU', //translate: clasification_medication_plan_check  ISPC-2387 - Lore 14.08.2019
        );
         
        return $visit_clasification;
    }
    public function getColumnMapping($fieldName, $revers = false)
    {
    
        $values = FormBlockVisitClasificationTable::getInstance()->getEnumValues($fieldName);
    
        $values = array_combine($values, array_map("self::translate", $values));
   
        return $values;
    
    }

    
    
	public function create_form_visitclasification ($values =  array() , $elementsBelongTo = null)
	{
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_visitclasification");
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend('visitclasification');
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
	                'colspan' => 4,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'class'    => 'dontPrint',
	            )),
	        ),
	    ));
	    
	    $visit_clasification = $this->__visit_clasificationColumns();
	    $cnt= 0;
	    
	    
	    foreach($visit_clasification as $k=>$clasification_value){
	          $row = $this->subFormTableRow();
	          $cb = $subform->createElement('checkbox', $clasification_value, array(
	            'checkedValue'    => '1',
	            'uncheckedValue'  => '0',
	            'label'      => 'clasification_'.$clasification_value,
	            'value' => $values[$clasification_value],
	            'class'            => 'classification_minutes_chk',
	            'id'               => 'mambo_'.$clasification_value.'_minutes_chk',
	            'data-opt'               => $clasification_value.'_minutes',
	            'decorators' => array(
	                'ViewHelper',
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag' => 'td',
	                    'class'    => 'cbrdList',
	                )),
	                array('Label', array(
	                    'tag' => 'td',
	                    'tagClass'=>'print_column_first',
	                     'placement' => Zend_Form_Decorator_Abstract::PREPEND
	                    
	                )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag' => 'tr',
	                    'class' => 'cbrdList',
	                    'openOnly' => true,
	                )),
	            ),
	              'labelClass' => "checkboxes",
	              'labelUnwrapp' => true,
	              'labelPlacement' => 'append',
	               
	           'onChange' => 'if (this.checked) {$(this).parents(\'tr\').find(\'.selector_vc_extra\').removeClass(\'display_none\');} else {$(this).parents(\'tr\').find(\'.selector_vc_extra\').addClass(\'display_none\')}'
	        ));
	        $subform->addElement($cb, "cb{$cnt}");
	        
	        
	        
	        $display_none =  $values[$clasification_value] == '1' ? "" : 'display_none';
	        
	        $subform->addElement('text', $clasification_value.'_minutes', array(
	            'placeholder'      => $this->translate("clasification_timespent"),
	            'value'            => !empty($values[$clasification_value.'_minutes'])? $values[$clasification_value.'_minutes'] : null,
	            'class'            => 'classification_minutes',
	            'id'               => 'mambo_'.$clasification_value.'_minutes',
	            'decorators'       => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag'      => 'td',
	                    'class'    => "selector_vc_extra {$display_none}",
	                )),
	            ),
	        ));

 
	        $display_none =  $values[$clasification_value] == '1' ? "" : 'display_none dontPrint';
	        
	        
	        $subform->addElement('textarea', $clasification_value.'_comment', array(
	            'value'            => !empty($values[$clasification_value.'_comment']) ? $values[$clasification_value.'_comment'] : null,
	            'placeholder'      => $this->translate("clasification_comment"),
	            'class'            => 'mambo_comment',
	            'decorators'       => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag'      => 'td',
	                    'class'    => "selector_vc_extra {$display_none}",
	                )),
// 	                array(array('row' => 'HtmlTag'), array(
// 	                    'tag'          => 'tr',
// 	                    'closeOnly'    => true,
// 	                )),
	            ),
	        ));
	        
	        $subform->addElement('note', $clasification_value.'_note', array(
	            'label'            => "",
	            'value'            => '',
	            
	            'decorators'       => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag'      => 'td',
	                    'class'    => "selector_vc_extra ",
	                )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag'          => 'tr',
	                    'closeOnly'    => true,
	                )),
	            ),
	        ));
	        
	        
 
	        $cnt++;
	        
	    }	    
	    
	    $subform->addElement('text', 'remain_minutes', array(
	        'label'            => 'zu verteilen',
	        'value'            => !empty($values['remain_minutes'])? $values['remain_minutes'] : 0,
	        'id'               => 'mambo_time_division_remain',
	        'readonly'        => true,
	        'decorators' => array(
	            'ViewHelper',
	            array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	            array(array('data' => 'HtmlTag'), array(
	                'tag' => 'td',
	                'colspan' => 4,
	            )),
	            array(array('row' => 'HtmlTag'), array(
	                'tag' => 'tr',
	                'class'    => 'dontPrint',
	            )),
	        ),
	    ));
	     
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	public function save_form_visitclasification ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_visitclasification_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_visitclasification_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_visitclasification_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	    
	    
	    $entity = FormBlockVisitClasificationTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
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
	private function __save_form_visitclasification_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data) 
	        || in_array('visitclasification', $data['__formular']['allowed_blocks'])
	        )
	    {
	        return;
	    }
	
	
	    $oldValues = FormBlockVisitClasificationTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	
	        unset($oldValues[FormBlockVisitClasificationTable::getInstance()->getIdentifier()]);
	
	        $data = array_merge($data, $oldValues);
	    }
	
	}
	
	/**
	 * write or erase the patientcourse text
	 *
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_visitclasification_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data) 
	         || ! in_array('visitclasification', $data['__formular']['allowed_blocks'])) 
	        {
	        return;
	    }
	
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	
	    if ( ! in_array('visitclasification', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	     
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_visitclasification_patient_course_format($data);


	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	
	        $oldValues = FormBlockVisitClasificationTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	        if (empty($oldValues)) {
	
	            //missing previous values, so we save
	            $save_2_PC = true ;
	
	        } else {
	
	            $course_arr_OLD =  $this->__save_form_visitclasification_patient_course_format($oldValues);
	           
                $changes = 0;
                
                foreach($course_arr_OLD as $old_cls=>$old_valuses){
                    
                    if( ! isset($course_arr[$old_cls])){
                        // remove this from PC
                        $pc_entity = new PatientCourse();
                        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockVisitClasification::PATIENT_COURSE_TABNAME,'yes',true,$old_valuses['shortcut']);
                    }
                }
                
                foreach($course_arr as $cls=>$vcs_val){
                    
                   if(!isset($course_arr_OLD[$cls])){
                       $changes++;
                   } else {
                       
                       if($vcs_val['minutes'] != $course_arr_OLD[$cls]['minutes'] || $vcs_val['comment'] != $course_arr_OLD[$cls]['comment'] ) 
                       {
                           $changes++;
                       }   
                   }
                }

	            if ( $changes == 0 ) {
	                //same pc... nothing to insert
	            } else {
	                $save_2_PC = true ;
	            }
	
	        }
	
	    }
	
	
	    if ($save_2_PC
	        && ! empty($course_arr)
	        && ($pc_listener = FormBlockVisitClasificationTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
	    {
	
	        foreach($course_arr as $clasification => $vc_values){
	             
	            $course_str = $vc_values['name'];
	            if(strlen($vc_values['minutes'])){
	                $course_str .= ' : '.$vc_values['minutes'];
	            }
	            if(strlen($vc_values['comment'])){
	                $course_str .= ' | '.$vc_values['comment'];
	            }
	            
	            $change_date = "";//removed from pc; ISPC-2071

	            $cust = new PatientCourse();
	            //skip Trigger()
	            $cust->triggerformid = null;
	            $cust->triggerformname = null;
	             
	            $cust->ipid = $ipid;
	            $cust->course_date = date("Y-m-d H:i:s", time());
	            $cust->course_type = Pms_CommonData::aesEncrypt($vc_values['shortcut']);
	            $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str).$change_date);
	            $cust->user_id = $this->logininfo->userid;
	            $cust->done_date = $done_date;
	            $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
	            $cust->done_id = $data['contact_form_id'];
	            //$cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
	            // ISPC-2071 - added tabname, this entry must be grouped/sorted
	            $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockVisitClasification::PATIENT_COURSE_TABNAME);
	            
	            $cust->save();	            
	            
	        }
	        
	    } elseif ($save_2_PC
	        && empty($course_arr)
	        && ! empty($formular['old_contact_form_id']))
	    {
	        //must manualy remove from PC this option
	        $pc_entity = new PatientCourse();
	        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockVisitClasification::PATIENT_COURSE_TABNAME);
	
	    }
	
	}
	
	
	/**
	 * format the patientcourse title message
	 *
	 * @param unknown $data
	 * @return multitype:string
	 */
	private function __save_form_visitclasification_patient_course_format($data = [])
	{
	    $course_arr = [];
	    
        $visit_clasification = $this->__visit_clasificationColumns();
        $visit_clasification_sh = $this->__visit_clasificationShortcuts();
        
        
        foreach($visit_clasification as $k=>$clasification){
            if($data[$clasification] == "1"){
                $course_arr[$clasification]['shortcut'] = $visit_clasification_sh[$clasification];
                $course_arr[$clasification]['name'] = $this->translate('clasification_'.$clasification);
                $course_arr[$clasification]['minutes'] = $data[$clasification.'_minutes'];
                $course_arr[$clasification]['comment'] = $data[$clasification.'_comment'];
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
	private function __save_form_visitclasification_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockVisitClasificationTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	
	        return true;
	    }
	}
	
	
	
}