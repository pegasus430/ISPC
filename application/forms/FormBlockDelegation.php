<?php
/**
 * 
 * @author Lore
 * ISPC-2488 Lore 22.11.2019 // Maria:: Migration ISPC to CISPC 08.08.2020	
 *
 */   
class Application_Form_FormBlockDelegation extends Pms_Form
{
    
    protected $_model = 'FormBlockDelegation';

    //define the name and id, if you want to piggyback some triggers
    private $triggerformid = FormBlockDelegation::TRIGGER_FORMID;
    private $triggerformname = FormBlockDelegation::TRIGGER_FORMNAME;
    
    //define this if you grouped the translations into an array for this form
    protected $_translate_lang_array = FormBlockDelegation::LANGUAGE_ARRAY;
    
    
    protected $_block_name_allowed_inputs =  array();
    protected $_block_feedback_options =  array();
    


    private function __delegationColumns()
    {
        $delegation = array(
            0 => 'medication_check_sgbv', // translate :  delegation_medication_check_sgbv
            1 => 'wound_care_sgbv', // translate :  delegation_wound_care_sgbv
            2 => 'catheter_replacement_sgbv', 	//translate: delegation_catheter_replacement_sgbv
            3 => 'blood_collection_sgbv', //translate: delegation_blood_collection_sgbv
            4 => 'inr_measurement_sgbv', //translate: delegation_inr_measurement_sgbv
            5 => 'bz_measurement_sgbv', //translate: delegation_bz_measurement_sgbv
            6 => 'injection_sgbv', //translate: delegation_injection_sgbv
            7 => 'vaccination_sgbv', //translate: delegation_vaccination_sgbv
        );
         
        return $delegation;
    }

    
    public function getColumnMapping($fieldName, $revers = false)
    {
    
        $values = FormBlockDelegationTable::getInstance()->getEnumValues($fieldName);
    
        $values = array_combine($values, array_map("self::translate", $values));
   
        return $values;
    
    }

    
    
    public function create_form_delegation ($values =  array() , $elementsBelongTo = null)
	{
        
	  
	    $__fnName = __FUNCTION__; //important, do not re-use this var on this fn
	
	    $this->mapValidateFunction($__fnName , "create_form_isValid");
	
	    $this->mapSaveFunction($__fnName , "save_form_delegation");
	
	    $subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
	    $subform->setLegend('delegation');
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
	    
	    $delegation = $this->__delegationColumns();
	    $cnt= 0;
	    
	    foreach($delegation as $k=>$delegation_value){
	          $row = $this->subFormTableRow();
	          $cb = $subform->createElement('checkbox', $delegation_value, array(
	            'checkedValue'    => '1',
	            'uncheckedValue'  => '0',
	              'label'      => 'delegation_'.$delegation_value,
	              'value' => $values[$delegation_value],
	            'class'            => 'delegation_minutes_chk',
	              'id'               => 'delegation_'.$delegation_value.'_minutes_chk',
	              'data-opt'               => $delegation_value.'_minutes',
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
	               
	           'onChange' => 'if (this.checked) {$(this).parents(\'tr\').find(\'.selector_d_extra\').removeClass(\'display_none\');} else {$(this).parents(\'tr\').find(\'.selector_d_extra\').addClass(\'display_none\')}'
	        ));
	        $subform->addElement($cb, "cb{$cnt}");
	        
	        
	        
	        $display_none =  $values[$delegation_value] == '1' ? "" : 'display_none';
	        
	        //ISPC-2488 Lore 21.01.2020  allow a decimal value in INR field (no limitation - just decimal like 888,4)
	        if($delegation_value == 'inr_measurement_sgbv'){
	            $subform->addElement('text', $delegation_value.'_minutes', array(
	                'value'            => !empty($values[$delegation_value.'_minutes'])? $values[$delegation_value.'_minutes'] : null,
	                'class'            => 'delegation_minutes_inr',
	                'id'               => 'delegation_'.$delegation_value.'_minutes',
	                'decorators'       => array(
	                    'ViewHelper',
	                    array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                    array('Errors'),
	                    array(array('data' => 'HtmlTag'), array(
	                        'tag'      => 'td',
	                        'class'    => "selector_d_extra {$display_none}",
	                    )),
	                    ),
	            ));
	        } else {
	            $subform->addElement('text', $delegation_value.'_minutes', array(
	                'value'            => !empty($values[$delegation_value.'_minutes'])? $values[$delegation_value.'_minutes'] : null,
	                'class'            => 'delegation_minutes',
	                'id'               => 'delegation_'.$delegation_value.'_minutes',
	                'decorators'       => array(
	                    'ViewHelper',
	                    array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                    array('Errors'),
	                    array(array('data' => 'HtmlTag'), array(
	                        'tag'      => 'td',
	                        'class'    => "selector_d_extra {$display_none}",
	                    )),
	                    ),
	            ));
	        }
            //.

 
	        $display_none =  $values[$delegation_value] == '1' ? "" : 'display_none dontPrint';
	        
	        
	        $subform->addElement('textarea', $delegation_value.'_comment', array(
	            'value'            => !empty($values[$delegation_value.'_comment']) ? $values[$delegation_value.'_comment'] : null,
	            'placeholder'      => $this->translate("delegation_comment"),
	            'class'            => 'delegation_comment',
	            'decorators'       => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag'      => 'td',
	                    'class'    => "selector_d_extra {$display_none}",
	                )),
// 	                array(array('row' => 'HtmlTag'), array(
// 	                    'tag'          => 'tr',
// 	                    'closeOnly'    => true,
// 	                )),
	            ),
	        ));
	        
	        $subform->addElement('note', $delegation_value.'_note', array(
	            'label'            => "",
	            'value'            => '',
	            
	            'decorators'       => array(
	                'ViewHelper',
	                array('Label', array('placement'=> 'IMPLICIT_PREPEND')),
	                array('Errors'),
	                array(array('data' => 'HtmlTag'), array(
	                    'tag'      => 'td',
	                    'class'    => "selector_d_extra ",
	                )),
	                array(array('row' => 'HtmlTag'), array(
	                    'tag'          => 'tr',
	                    'closeOnly'    => true,
	                )),
	            ),
	        ));
	        
	        
 
	        $cnt++;
	        
	    }	    
	    
    
	    
	    return $this->filter_by_block_name($subform, $__fnName);
	}
	
	
	
	public function save_form_delegation ($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data)) {
	        return;
	    }
	    
	    
	    //if user not alowed to this form, duplicate the block
	    $this->__save_form_delegation_copy_old_if_not_allowed($ipid , $data);
	    
	    //create patientcourse
	    $this->__save_form_delegation_patient_course($ipid , $data);
	    
	    //set the old block values as isdelete
	    $this->__save_form_delegation_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
	    
	    
	    $entity = FormBlockDelegationTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
	    
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
	private function __save_form_delegation_copy_old_if_not_allowed($ipid =  null , &$data =  array())
	{
	    if (empty($ipid) || empty($data) 
	        || in_array('delegation', $data['__formular']['allowed_blocks'])
	        )
	    {
	        return;
	    }
	
	
	    $oldValues = FormBlockDelegationTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	    if ( ! empty($oldValues)) {
	
	        unset($oldValues[FormBlockDelegationTable::getInstance()->getIdentifier()]);
	
	        $data = array_merge($data, $oldValues);
	    }
	
	}
	
	/**
	 * write or erase the patientcourse text
	 *
	 * @param string $ipid
	 * @param unknown $data
	 */
	private function __save_form_delegation_patient_course($ipid =  null , $data =  array())
	{
	    if (empty($ipid) || empty($data) 
	         || ! in_array('delegation', $data['__formular']['allowed_blocks'])) 
	        {
	        return;
	    }
	
	    $save_2_PC         = false;// 2 save or not 2 save into PatientCourse
	    $formular          = $data['__formular'];
	
	    if ( ! in_array('delegation', $data['__formular']['allowed_blocks'])) {
	        return;
	    }
	     
	    $course_arr_OLD    = [];
	    $course_arr        = $this->__save_form_delegation_patient_course_format($data);


	    if (empty($data['__formular']['old_contact_form_id'])) {
	        //this is from a new cf, so we add to patient_course
	        $save_2_PC = true ;
	    } else {
	
	        $oldValues = FormBlockDelegationTable::getInstance()->findOneByContactFormIdAndIpid($data['__formular']['old_contact_form_id'], $ipid, Doctrine_Core::HYDRATE_ARRAY);
	
	        if (empty($oldValues)) {
	
	            //missing previous values, so we save
	            $save_2_PC = true ;
	
	        } else {
	
	            $course_arr_OLD =  $this->__save_form_delegation_patient_course_format($oldValues);
	           
                $changes = 0;
                
                foreach($course_arr_OLD as $old_cls=>$old_valuses){
                    
                    if( ! isset($course_arr[$old_cls])){
                        // remove this from PC
                        $pc_entity = new PatientCourse();
                        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockDelegation::PATIENT_COURSE_TABNAME,'yes',true,$old_valuses['shortcut']);
                    }
                }
                
                foreach($course_arr as $cls=>$vcs_val){
                    
                   if(!isset($course_arr_OLD[$cls])){
                       $changes++;
                   } else {
                       
                       //ISPC-2488 Lore 08.07.2020
                       //if($vcs_val['minutes'] != $course_arr_OLD[$cls]['minutes'] || $vcs_val['comment'] != $course_arr_OLD[$cls]['comment'] ) 
                       if($vcs_val['check'] == 1 || $vcs_val['minutes'] != $course_arr_OLD[$cls]['minutes'] || $vcs_val['comment'] != $course_arr_OLD[$cls]['comment'] )
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
	        && ($pc_listener = FormBlockDelegationTable::getInstance()->getRecordListener()->get('PostInsertWriteToPatientCourse')) )
	    {

	        $course_str = '';
	        $course_str_check = '';
	        
	        foreach($course_arr as $clasification => $vc_values){
	            
	            if (strlen($vc_values['minutes'])>0 || strlen($vc_values['comment'])>0 ) {
	                $course_str .= $vc_values['name'];
	                if(strlen($vc_values['minutes'])>0){
	                    $course_str .= ' : '.$vc_values['minutes'];	                    
	                }
	                if(strlen($vc_values['comment'])>0){
	                    $course_str .= ' | '.$vc_values['comment']."\r\n";
	                }
	            }
	            
	            //ISPC-2488 Lore 08.07.2020
	            if ($vc_values['check'] == 1 && strlen($vc_values['minutes']) == 0  ) {           
	                $course_str_check .= $vc_values['name']."\r\n";
	            }
	                

	            
	        }
	           
  	            $vc_values['shortcut'] = 'DG' ;
  	            
	            $change_date = "";//removed from pc; ISPC-2071

	            if(strlen($course_str) > 0 ){
	                
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
    	            $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockDelegation::PATIENT_COURSE_TABNAME);
    	            
    	            $cust->save();	            
	            }
	            
	            //ISPC-2488 Lore 08.07.2020
	            if(strlen($course_str_check) > 0 ){
	                $vc_values['shortcut'] = 'RD' ;
	                
	                $cust = new PatientCourse();
	                //skip Trigger()
	                $cust->triggerformid = null;
	                $cust->triggerformname = null;
	                
	                $cust->ipid = $ipid;
	                $cust->course_date = date("Y-m-d H:i:s", time());
	                $cust->course_type = Pms_CommonData::aesEncrypt($vc_values['shortcut']);
	                $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str_check).$change_date);
	                $cust->user_id = $this->logininfo->userid;
	                $cust->done_date = $done_date;
	                $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
	                $cust->done_id = $data['contact_form_id'];
	                //$cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
	                // ISPC-2071 - added tabname, this entry must be grouped/sorted
	                $cust->tabname = Pms_CommonData::aesEncrypt(FormBlockDelegation::PATIENT_COURSE_TABNAME);
	                
	                $cust->save();
	            }
	            
	        
	    } elseif ($save_2_PC
	        && empty($course_arr)
	        && ! empty($formular['old_contact_form_id']))
	    {
	        //must manualy remove from PC this option
	        $pc_entity = new PatientCourse();
	        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($ipid, $data['__formular']['old_contact_form_id'],  FormBlockDelegation::PATIENT_COURSE_TABNAME);
	
	    }
	
	}
	
	
	/**
	 * format the patientcourse title message
	 *
	 * @param unknown $data
	 * @return multitype:string
	 */
	private function __save_form_delegation_patient_course_format($data = [])
	{
	    $course_arr = [];
	    
	    $delegation = $this->__delegationColumns();
            
	    foreach($delegation as $k=>$clasification){
	        
            $course_arr[$clasification]['name'] = $this->translate('delegation_'.$clasification);
            $course_arr[$clasification]['check'] = $data[$clasification];       //ISPC-2488 Lore 08.07.2020
            $course_arr[$clasification]['minutes'] = $data[$clasification.'_minutes'];
            $course_arr[$clasification]['comment'] = $data[$clasification.'_comment'];
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
	private function __save_form_delegation_clear_block_data($ipid = '', $contact_form_id = 0)
	{
	    if ( ! empty($contact_form_id) && ! empty($ipid))
	    {
	        FormBlockDelegationTable::getInstance()->createQuery('del')
	        ->delete()
	        ->where("contact_form_id = ?", $contact_form_id)
	        ->andWhere('ipid = ?', $ipid)
	        ->execute();
	
	        return true;
	    }
	}
	
	
	
}