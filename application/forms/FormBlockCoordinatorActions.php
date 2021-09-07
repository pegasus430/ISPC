<?php

// require_once("Pms/Form.php");
/**
 * Copy of FormBlockMeasures
 * Leistung / Koordination = coordinator_actions = FormBlockCoordinatorActions
 * //ISPC-2487 // Maria:: Migration ISPC to CISPC 08.08.2020	
 *
 * changed: bypass Trigger() on PC
 * fixed: adding this block to a saved cf would not save to PC the first time
 * fixed/changed: insert only one PC for the entire block
 * changed: this block will NO longer save all the client list FormBlocksSettings values, will just save the ones you checked
 */
class Application_Form_FormBlockCoordinatorActions extends Pms_Form
{

    public function clear_block_data($ipid = '', $contact_form_id = 0)
    {
        if (! empty($contact_form_id)) {
            $Q = Doctrine_Query::create()->update('FormBlockCoordinatorActions')
                ->set('isdelete', '1')
                ->where("contact_form_id = ?", $contact_form_id)
                ->andWhere('ipid = ?', $ipid);
            $Q->execute();
            
            return true;
        } else {
            return false;
        }
    }

    public function InsertData($post, $allowed_blocks)
    {
        
//         dd($post);
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $coordinator_actions_block = new FormBlockCoordinatorActions();
        
        $blocks_settings = new FormBlocksSettings();
        $block_coordinator_actions_values = $blocks_settings->get_block($clientid, 'coordinator_actions');
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
        
        
        $client_coordinator_actions_values =  array();
        
        foreach($block_coordinator_actions_values as $client_list_value) {
            $client_coordinator_actions_values[$client_list_value['id']] = $client_list_value;
        }
        
        $records = array();
		
		foreach ($post['egblock']['coordinator_actions'] as $action_id => $action_value) {
		    
		    if( !empty($action_value['receives_services'])
		        || !empty($action_value['is_requested'])
		        || !empty($action_value['redirected'])
		        || !empty($action_value['informed'])
		        || !empty($action_value['action_comment'])
		        || !empty($action_value['hand_strength_training'])
		        || !empty($action_value['pain_diary'])
		        || !empty($action_value['sleep_diary'])
		        || !empty($action_value['incontinence_protocol'])
		        )
		    {
		        
    		    $records[] = array(
    		        "ipid"                    => $post['ipid'],
    		        "contact_form_id"         => $post['contact_form_id'],
    		        "action_id"               => $action_id,
    		        
    		        "receives_services"       => !empty($action_value['receives_services'])? $action_value['receives_services'] : "0",
    		        "is_requested"            => !empty($action_value['is_requested'])? $action_value['is_requested'] : "0",
    		        "redirected"              => !empty($action_value['redirected'])? $action_value['redirected'] : "0",
    		        "informed"                => !empty($action_value['informed'])? $action_value['informed'] : "0",
    		        "action_comment"          => !empty($action_value['action_comment'])? $action_value['action_comment'] : "",
    		        "hand_strength_training"  => !empty($action_value['hand_strength_training'])? $action_value['hand_strength_training'] : "0",
    		        "pain_diary"              => !empty($action_value['pain_diary'])? $action_value['pain_diary'] : "0",
    		        "sleep_diary"             => !empty($action_value['sleep_diary'])? $action_value['sleep_diary'] : "0",
    		        "incontinence_protocol"   => !empty($action_value['incontinence_protocol'])? $action_value['incontinence_protocol'] : "0"
    		    );
		    }		    
		}
        
        
        if ( ! empty($post['old_contact_form_id'])) {
        
            $change_date = $post['contact_form_change_date'];
        
            $coordinator_actions_old_data = $coordinator_actions_block->getPatientFormBlockCoordinatorActions($post['ipid'], $post['old_contact_form_id'], true);
            
        
            if ( ! in_array('coordinator_actions', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                if ( ! empty($coordinator_actions_old_data)) {
        
                    $records = array();
        
                    foreach($coordinator_actions_old_data as $saved_row) {
                         
                        $records[] = array(
                            "ipid"                  => $post['ipid'],
                            "contact_form_id"       => $post['contact_form_id'],
                            "action_id"             => $saved_row['action_id'],
                            "receives_services"     => $saved_row['receives_services'],
                            "is_requested"          => $saved_row['is_requested'],
                            "redirected"            => $saved_row['redirected'],
                            "informed"              => $saved_row['informed'],
                            "action_comment"        => $saved_row['action_comment'],
                            "hand_strength_training"=> $saved_row['hand_strength_training'],
                            "pain_diary"            => $saved_row['pain_diary'],
                            "sleep_diary"           => $saved_row['sleep_diary'],
                            "incontinence_protocol" => $saved_row['incontinence_protocol'],

                        );
                    }
        
                }
            }
            else {
                //we have permissions and cf is being edited
                //write changes in PatientCourse is something was changed
                if ( ! empty($coordinator_actions_old_data)) {
        
        
                    if (count($coordinator_actions_old_data) != count($post['egblock']['coordinator_actions'])) {
                        //something changed
                        $save_2_PC = true;
        
                    } else {
        
                        foreach ($post['egblock']['coordinator_actions'] as $k=>$inserted_value) {
        
                            if ( ! isset($coordinator_actions_old_data[$k])) {
                                // not same keys, something changed
                                $save_2_PC = true;
                                break;
        
                            } elseif ((int)$inserted_value != (int)$coordinator_actions_old_data[$k]) {
                                //compare each value to check if something changed, not same values
                                $save_2_PC = true;
                                break;
                            }
                        }
                    }
        
                }
                else {
                    //nothing was edited last time, or this block was added after the form was created
                    $save_2_PC = true;
                    $change_date = '';
                     
                }
            }
        } else {
            //new cf, save
            $save_2_PC = true;
        }
        
        
        //set the old block values as isdelete
        $clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);        
        
        

        $pc_recorddata =  array();
        
        if ( ! empty($records)) {
        
            $collection = new Doctrine_Collection('FormBlockCoordinatorActions');
            $collection->fromArray($records);
            $collection->save();
        
            $pc_recorddata = $collection->getPrimaryKeys();
        }
        
        
        

        if ($save_2_PC && in_array('coordinator_actions', $allowed_blocks)) {
        
            if (empty($records)) {
                //you unchecked all the options
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
        
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockCoordinatorActions');
                }
        
            } else {
        
                $course_title_line = array();
 
                foreach ($records as $row) {
        
                    if (isset($client_coordinator_actions_values [$row['action_id']]))
                        $action_line_option_name = $client_coordinator_actions_values [$row['action_id']] ['option_name'];
                        
                       $action_line = array();
                       
                       if($row['receives_services'] == "1"){
                           $action_line[] = $this->translate('ca_receives_services');
                       }
                       if($row['is_requested'] == "1"){
                           $action_line[] = $this->translate('ca_is_requested');
                       }
                       
                       if($row['redirected'] == "1"){
                           $action_line[] = $this->translate('ca_redirected');
                       }
                       
                       if($row['informed'] == "1"){
                           $action_line[] = $this->translate('ca_informed');
                       }
                       
                       if($row['hand_strength_training'] == "1"){
                           $action_line[] = $this->translate('ca_hand_strength_training');
                       }
                       
                       if($row['pain_diary'] == "1"){
                           $action_line[] = $this->translate('ca_pain_diary');
                       }
                       if($row['sleep_diary'] == "1"){
                           $action_line[] = $this->translate('ca_sleep_diary');
                       }
                       if($row['incontinence_protocol'] == "1"){
                           $action_line[] = $this->translate('ca_incontinence_protocol');
                       }
                       
                       if(!empty($row['action_comment']) ) {
                           $action_line[] = $row['action_comment'];
                       }
                        
                        $course_title_line[] = $action_line_option_name.': '.implode(', ',$action_line);
        
                }
        
                if ( ! empty($course_title_line)) {
                    //save to PC
                    $course_str = "Leistung / Koordination: \n" . implode("\n", $course_title_line);
                    //goa edited entry in verlauf
                    $change_date = "";//removed from pc; ISPC-2071
        
                    $cust = new PatientCourse();
                     
                    //skip Trigger()
                    $cust->triggerformid = null;
                    $cust->triggerformname = null;
                     
                    $cust->ipid = $post['ipid'];
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt( FormBlockCoordinatorActions::PATIENT_COURSE_TYPE );
                    $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str).$change_date);
                    $cust->user_id = $userid;
                    // 		            $cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
                    $cust->done_date = $done_date;
                    $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                    $cust->done_id = $post['contact_form_id'];
                     
                    $cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
                     
                    // ISPC-2071 - added tabname, this entry must be grouped/sorted
                    $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockCoordinatorActions");
                     
                    $cust->save();
                }
        
            }
        
        }
        
        
        
    }
}

?>