<?php
// require_once("Pms/Form.php");
/**
 * @update Jan 24, 2018: @author claudiu, checked/modified for ISPC-2071
 * this file was copied from https://jira.significo.de/secure/attachment/12437/BA_more_files.zip
 *
 * lmu_visit = Status = FormBlockLmuVisit
 *
 * changed: bypass Trigger() on PC
 * fixed/changed: removed the insert pc <div /> , this block does NOT save into PC (uncomment if you want PC)
 *
 */
class Application_Form_FormBlockLmuVisit extends Pms_Form
{

    public function clear_block_data($ipid, $contact_form_id)
    {
        if (! empty($contact_form_id)) {
            
            $Q = Doctrine_Query::create()->update('FormBlockLmuVisit')
                ->set('isdelete', '1')
                ->where("contact_form_id = ?", $contact_form_id)
                ->andWhere('ipid = ?', $ipid);
            $result = $Q->execute();
            
            return true;
        } else {
            return false;
        }
    }

    public function InsertData($post, $allowed_blocks)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $cols = array(
            'phase',
            'karnofsky',
            'bewusstsein',
            'ort',
            'person',
            'situation',
            'zeit',
            'keineorient',
            'klau_diag',
            'klau_frage',
            'linkedklau'
        );
        
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));       
        
        
        
        //uncomment if you want PC
        /*
        $course_title_lines = array(); //holds the patient_course_title
        
        if (in_array('lmu_visit', $allowed_blocks)) {
            
            if (isset($post['lmu_visit']['phase']) && $post['lmu_visit']['phase'] != "") {
    
                $karnof = "";
                if ($post['lmu_visit']['karnofsky'] != "") {
                    $karnof = " (" . $post['lmu_visit']['karnofsky'] . "%)";
                }
                
                $phase_map = array(
                    '1' => 'stabil',
                    '2' => 'fluktuierende Symptome',
                    '3' => 'erwartet verschlechternd',
                    '4' => 'sterbend'
                );
                $course_title_lines[] = "Krankheitsphase: " . $phase_map[$post['lmu_visit']['phase']] . $karnof ;
            }
            
            if (isset($post['lmu_visit']['bewusstsein']) && $post['lmu_visit']['bewusstsein'] != "") {
                $course_title_lines[] = "Bewusstsein: " . $post['lmu_visit']['bewusstsein'] ;
            }
            
                        
            if (isset($post['lmu_visit']['keineorient']) && $post['lmu_visit']['keineorient']) {
                $course_title_lines[] = "Orientierung: keine";
            } else {
                
                $cbs = array(
                    "Ort",
                    "Person",
                    "Situation",
                    "Zeit"
                );
                $orient = '';
                foreach ($cbs as $val) {
                    if (isset($post['lmu_visit'][strtolower($val)]) && $post['lmu_visit'][strtolower($val)] == 1) {
                        if (strlen($orient) > 0) {
                            $orient = $orient . ", " . $val;
                        } else {
                            $orient = $val;
                        }
                    }
                }
                
                if ($orient != '') {
                    $course_title_lines[] = "Orientierung: " . $orient ;
                }
            }
        
        }
        */
        
        
        if (! empty($post['old_contact_form_id'])) {
            
            $change_date = $post['contact_form_change_date'];
            
            $tw_block = new FormBlockLmuVisit();
            $tw_old_data = $tw_block->getPatientFormBlockLmuVisit($post['ipid'], $post['old_contact_form_id'], true);
            
            if (! in_array('lmu_visit', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                
                $post['lmu_visit'] = array();
                
                if (! empty($tw_old_data[0])) {
                    
                    $post['lmu_visit'] = array(
                        "contact_form_id" => $post['contact_form_id'],
                        "ipid" => $post['ipid'],
                        
                        "phase" => $tw_old_data[0]['phase'],
                        "karnofsky" => $tw_old_data[0]['karnofsky'],
                        "bewusstsein" => $tw_old_data[0]['bewusstsein'],
                        "ort" => $tw_old_data[0]['ort'],
                        "person" => $tw_old_data[0]['person'],
                        "situation" => $tw_old_data[0]['situation'],
                        "zeit" => $tw_old_data[0]['zeit'],
                        "keineorient" => $tw_old_data[0]['keineorient'],
                        "klau_diag" => $tw_old_data[0]['klau_diag'],
                        "klau_frage" => $tw_old_data[0]['klau_frage'],
                        "linkedklau" => $tw_old_data[0]['linkedklau'],
                        "isdelete" => $tw_old_data[0]['isdelete']
                    );
                }
            } else {
                // we have permissions and cf is being edited
                // write changes in PatientCourse is something was changed
                if (! empty($tw_old_data[0])) {
                    
                    if ($post['lmu_visit']['phase'] != $tw_old_data[0]['phase'] 
                        || $post['lmu_visit']['karnofsky'] != $tw_old_data[0]['karnofsky'] 
                        || $post['lmu_visit']['bewusstsein'] != $tw_old_data[0]['bewusstsein'] 
                        || (int) $post['lmu_visit']['ort'] != (int) $tw_old_data[0]['ort'] 
                        || (int) $post['lmu_visit']['person'] != (int) $tw_old_data[0]['person'] 
                        || (int) $post['lmu_visit']['situation'] != (int) $tw_old_data[0]['situation'] 
                        || (int) $post['lmu_visit']['zeit'] != (int) $tw_old_data[0]['zeit'] 
                        || (int) $post['lmu_visit']['keineorient'] != (int) $tw_old_data[0]['keineorient'] 
                        || $post['lmu_visit']['klau_diag'] != $tw_old_data[0]['klau_diag']
                        || $post['lmu_visit']['klau_frage'] != $tw_old_data[0]['klau_frage']
//                         || $post['lmu_visit']['linkedklau'] != $tw_old_data[0]['linkedklau'] //this is not in your form-post
//                         || $post['lmu_visit']['iposenabled'] != $tw_old_data[0]['iposenabled'] //this is in your form-post and not in table
                        ) 
                    {
                        // something was edited, we must insert into PC
                        $save_2_PC = true;
                    }
                } else {
                    // nothing was edited last time, or this block was added after the form was created
                    $save_2_PC = true;
                    $change_date = '';
                }
            }
        } else {
            // new cf, save
            $save_2_PC = true;
        }
        
        
        
        //set the old block values as isdelete
        $clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
        
        
        
        $cust = new FormBlockLmuVisit();
        $cust->ipid = $post['ipid'];
        $cust->contact_form_id = $post['contact_form_id'];
        //ISPC-2683 Carmen 16.10.2020
        $aw_date = explode(".", $post['date']);        
        $cust->vigilance_awareness_date = $aw_date[2] . "-" . $aw_date[1] . "-" . $aw_date[0] . ' ' . date("H") . ':' . date("i") . ":00";
        //--
        
        foreach ($cols as $col) {
            $cust->$col = $post['lmu_visit'][$col];
        }
        
        //uncomment if you want PC
        /*
        if ($save_2_PC && in_array('lmu_visit', $allowed_blocks)) {
            
            if (empty($course_title_lines)) {
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockLmuVisit');
                }
                
            } elseif ( ! empty($course_title_lines)
                    && ($pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse')) )
                {
                    $course_str =  implode("\n", $course_title_lines);
                    $change_date = "";//removed from pc; ISPC-2071 
            
                    $pc_listener->setOption('disabled', false);
                    $pc_listener->setOption('course_title', $course_str . $change_date);
                    $pc_listener->setOption('done_date', $done_date);
                    $pc_listener->setOption('user_id', $userid);
                     
                }
               
        }
        */
        
        $cust->save();
        
    }
    
    public function getColumnMapping($fieldName, $revers = false)
    {
    
    	//             $fieldName => [ value => translation]
    	$overwriteMapping = [
    			'awareness' => array(
    				'' => $this->translate('select'),
    				'wach' => $this->translate('aw_awake'),
    				'somnolent' => $this->translate('aw_somnolent'),
    				'soporös' => $this->translate('aw_soporous'),
    				'komatös' => $this->translate('aw_comatose')
    			),
    			'orientation' => array(
    				'ort' => $this->translate('aw_ort'),
    				'person' => $this->translate('aw_person'),
    				'situation' => $this->translate('aw_situation'),
    				'zeit' => $this->translate('aw_zeit'),
    				'keineorient' => $this->translate('aw_keineorient'),
    			),
    	];
    
    	//$values = FormBlockLmuVisitTable::getInstance()->getEnumValues($fieldName);
    	$values = array();
    	
    	if (isset($overwriteMapping[$fieldName])) {
    		$values = $overwriteMapping[$fieldName] + $values;
    	}
   
    	return $values;
    
    }
    
    
    
    
    public function create_form_block_vigilance_awareness ($values =  array() , $elementsBelongTo = null)
    {
    	// 	    dd($values);
    	$__fnName = __FUNCTION__; //important, do not re-use this var on this fn
    
    	$this->mapValidateFunction($__fnName , "create_form_isValid");
    
    	$this->mapSaveFunction($__fnName , "save_form_block_vigilance_awareness");
    
    
    	$subform = $this->subFormContactformBlock($this->getFnOptions($__fnName));
    	$subform->setLegend($this->translate('vigilance_awareness'));
    	$subform->setAttrib("class", "label_same_size_auto  {$__fnName}");
    	$subform->addDecorator('Form');
    	 
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
    	
    	//Bewusstsein
    	$subform->addElement('select', 'awareness', array(
    			'label' 	   => self::translate('awareness').":",
    			'multiOptions' => $this->getColumnMapping('awareness'),
    			'value'        => $values['bewusstsein'],
    			'required'     => true,
    			'filters'      => array('StringTrim'),
    			'decorators' =>   array(
    					'ViewHelper',
    					array(array('data' => 'HtmlTag'), array('tag' => 'td')),
    					array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first required')),
    					array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
    			),
    	));
    	
    	$subform->addElement('note', 'Note_awareness_type_err', array(
    			'value'        => $this->translate('awareness_name_type_err'),
    			'decorators'   => array(
    					'ViewHelper',
    					array(array('data' => 'HtmlTag'), array(
    							'tag' => 'td', 'colspan' => 2,
    					)),
    					array(array('row' => 'HtmlTag'), array(
    							'tag'      => 'tr', 'id' => 'awareness_name_type_error',
    					)),
    			),
    	));
    
    	//Orientierung(Ort, Person, Situation, Zeit, keine Orientierung)
        $subform->addElement('multiCheckbox', 'orientation', array(
            'multiOptions' => $this->getColumnMapping('orientation'),
            'value'        => $values['orientation'],
            'label'        => self::translate('aw_orientation').":",
            'required'   => false,
            'decorators' => array(
                'ViewHelper',
                array('Errors'),
                array(array('data' => 'HtmlTag'), array('tag' => 'td', )),
                array('Label', array('tag' => 'td', 'tagClass'=>'print_column_first required')),
                array(array('row' => 'HtmlTag'), array('tag' => 'tr')),
            ),
        		'onChange' => 'if ($(this).val() == "keineorient" && $(this).is(":checked")) {$(this).parent().parent().find(":input").not(this).each(function(){if($(this).is(":checked")){$(this).removeAttr("checked");}});};
        					   if ($(this).val() != "keineorient") {if($("#orientation-keineorient").is(":checked")) {$("#orientation-keineorient").removeAttr("checked");}};',
        ));
        
        $subform->addElement('text', 'vigilance_awareness_date', array(
        		'label'        => self::translate('vigilance_awareness_date').":",
        		'value'        => ! empty($values['vigilance_awareness_date']) ? date('d.m.Y', strtotime($values['vigilance_awareness_date'])) : date('d.m.Y'),
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
         
        $vigilance_awareness_time = ! empty($values['vigilance_awareness_date']) ? date('H:i:s', strtotime($values['vigilance_awareness_date'])) : date("H:i");
        $subform->addElement('text', 'vigilance_awareness_time', array(
        		//'label'        => self::translate('clock:'),
        		'value'        => $vigilance_awareness_time,
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
    
    	return $this->filter_by_block_name($subform, $__fnName);
    }
    
    public function save_form_block_vigilance_awareness ($ipid =  null , $data =  array())
    {
    	if (empty($ipid) || empty($data)) {
    		return;
    	}
    
    	if(!$data['contact_form_id'])
    	{
    		//$data['suckoff_date'] = date('Y-m-d H:i:s', time());
    		if($data['vigilance_awareness_time'] != "")
    		{
    			$vigilance_awareness_time = $data['vigilance_awareness_time'] . ":00";
    		}
    		else
    		{
    			$vigilance_awareness_time = '00:00:00';
    		}
    		 
    		if($data['vigilance_awareness_date'] != "")
    		{
    			$data['vigilance_awareness_date'] = date('Y-m-d H:i:s', strtotime($data['vigilance_awareness_date'] . ' ' . $vigilance_awareness_time));
    		}
    		else
    		{
    			$data['vigilance_awareness_date'] = '0000-00-00 00:00:00';
    		}
    
    	}
    	$data['ipid'] = $ipid;
    	$data['source'] = "charts";
    	$data['bewusstsein'] = $data['awareness'];
    	
    	foreach($this->getColumnMapping('orientation') as $kdo =>$vdo)
    	{
    		if(in_array($kdo, $data['orientation']))
    		{
    			$data[$kdo] = 1;
    		}
    		else 
    		{
    			$data[$kdo] = 0;
    		}
    	}
    	
    	unset($data['awareness']);
    	unset($data['orientation']);
    	unset($data['vigilance_awareness_time']);
    	
    	//if not from charts
    	if($data['contact_form_id'])
    	{
    		//if user not alowed to this form, duplicate the block
    		$this->__save_form_vigilance_awareness_copy_old_if_not_allowed($ipid , $data);
    	  
    		//create patientcourse
    		$this->__save_form_vigilance_awareness_patient_course($ipid , $data);
    	  
    		//set the old block values as isdelete
    		$this->__save_form_vigilance_awareness_clear_block_data($ipid, $data['__formular']['old_contact_form_id']);
    	}
    	// TODO-4158 Ancuta 26.05.2021
    	else
    	{
    	    $this->__save_awareness_patient_course($ipid , $data);
    	}
    	//-- 
    
    	$entity = FormBlockLmuVisitTable::getInstance()->findOrCreateOneBy(['id', 'ipid'], [$data['id'], $ipid], $data);
    	 
    	 
    	return $entity;
    }
    
    /**
     * TODO-4158 Ancuta 26.05.2021
     * @param unknown $ipid
     * @param array $data
     */
    private function __save_awareness_patient_course($ipid =  null , $data =  array())
    {
        
        if (empty($ipid) || empty($data) )
        {
            return;
        }
        
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $cl_opt_details = $this->getColumnMapping('awareness');
        
        
        if(empty($data['id'])){
            $comment = "Ein Eintrag für Vigilanz & Bewusstsein wurde erfasst: ".$cl_opt_details[$data['bewusstsein']];
        } else{
            $comment = "Ein Eintrag für Vigilanz & Bewusstsein wurde geändert : ".$cl_opt_details[$data['bewusstsein']];
        }
        
        $cust = new PatientCourse();
        $cust->ipid = $ipid;
        $cust->course_date = date("Y-m-d H:i:s", time());
        $cust->course_type = Pms_CommonData::aesEncrypt('K');
        $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($comment));
        $cust->tabname = Pms_CommonData::aesEncrypt(addslashes('FormBlockLmuVisit'));
        $cust->user_id = $userid;
        $cust->save();
        
    }
    
    
}

?>
