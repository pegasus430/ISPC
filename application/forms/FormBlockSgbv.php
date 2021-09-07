<?php
// require_once ("Pms/Form.php");

/**
 * @update Jan 25, 2018: @author claudiu, checked/modified for ISPC-2071
 * Leistungseingabe = sgbv = FormBlockSgbv
 *
 * changed: bypass Trigger() on PC
 * fixed: adding this block to a saved cf would not save to PC the first time
 * changed: this block will NO longer save all the patient+client list = PatientCustomActions values, will just save the ones you checked
 */
class Application_Form_FormBlockSgbv extends Pms_Form
{

    public function clear_block_data($ipid = '', $contact_form_id = 0)
    {
        if (! empty($contact_form_id)) {
            $Q = Doctrine_Query::create()->update('FormBlockSgbv')
                ->set('isdelete', '1')
                ->where("contact_form_id = ?", $contact_form_id)
                ->andWhere('ipid = ?', $ipid);
            $Q->execute();
            
            return true;
        } else {
            return false;
        }
    }

    public function InsertData($post, $allowed_blocks, $price_list, $used_actions)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $sgbv_block = new FormBlockSgbv();
        
        $patient_sgbv_actions = new PatientCustomActions();
        $pat_sgbv_actions = $patient_sgbv_actions->getAllSgbvActionsPatient($clientid, $post['ipid'], $price_list, $used_actions);
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
        
        
       
        $pat_sgbv_actions_by_id = array();
        foreach ($pat_sgbv_actions as $action_values) {
            $pat_sgbv_actions_by_id [$action_values['id']] = $action_values;
        }
        
        
        $records = array();
        //create FormBlockSgbv records, only checked ones will be saved(cause this is how POST works.. you want the uncheked add a hidden)
        if (in_array('sgbv', $allowed_blocks)) { //this if was added to prevent using the same variable names in the post, for different blocks
            foreach ($post['social_action'] as $action_id => $action_value) {
                if (isset($pat_sgbv_actions_by_id[$action_id])) {
                    $records[] = array(
                        "ipid"              => $post['ipid'],
                        "contact_form_id"   => $post['contact_form_id'],
                        "action_id"         => $action_id,
                        "action_value"      => $action_value,
                    );
                }
            }
        }
        
        
        
        if ( ! empty($post['old_contact_form_id'])) {
        
            $change_date = $post['contact_form_change_date'];
        
            $sgbv_old_data = $sgbv_block->getPatientFormBlockSgbv($post['ipid'], $post['old_contact_form_id'], true);
        
            if ( ! in_array('sgbv', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                if ( ! empty($sgbv_old_data)) {
        
                    $records = array();
        
                    foreach($sgbv_old_data as $saved_row) {
                         
                        $records[] = array(
                            "ipid"               => $post['ipid'],
                            "contact_form_id"    => $post['contact_form_id'],
                            "action_id"          => $saved_row['action_id'],
                            "action_value"       => $saved_row['action_value'],
//                             "isdelete"           => $saved_row['isdelete'],  // copy this too?
                        );
                    }
        
                }
            }
            else {
                //we have permissions and cf is being edited
                //write changes in PatientCourse is something was changed
                if ( ! empty($sgbv_old_data)) {
        
                    if (count($sgbv_old_data) != count($post['social_action'])) {
                        //something changed
                        $save_2_PC = true;
        
                    } else {
        
                        foreach ($post['social_action'] as $k=>$inserted_value) {
        
                            if ( ! isset($sgbv_old_data[$k]) || (int)$inserted_value != (int)$sgbv_old_data[$k]) {
                                // not same keys, or not same value, something changed
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
        
            $collection = new Doctrine_Collection('FormBlockSgbv');
            $collection->fromArray($records);
            $collection->save();
        
            $pc_recorddata = $collection->getPrimaryKeys();
        }
        

        if ($save_2_PC && in_array('sgbv', $allowed_blocks)) {
        
            if (empty($records)) {
                //you unchecked all the options
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
        
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockSgbv');
                }
        
            } else {
        
                $course_title_line = array();
        
                foreach ($records as $row) {
                    if (isset($pat_sgbv_actions_by_id [$row['action_id']]))
                        $course_title_line[] = $pat_sgbv_actions_by_id [$row['action_id']] ['action_name'];
        
                }
                                
        
                if ( ! empty($course_title_line)) {
                    //save to PC
                    $course_str = "Leistungseingabe: \n" . implode("\n", $course_title_line);
                    //goa edited entry in verlauf
                    $change_date = "";//removed from pc; ISPC-2071
                    
                    $cust = new PatientCourse();
                     
                    //skip Trigger()
                    $cust->triggerformid = null;
                    $cust->triggerformname = null;
                     
                    $cust->ipid = $post['ipid'];
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt( FormBlockSgbv::PATIENT_COURSE_TYPE );
                    $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str).$change_date);
                    $cust->user_id = $userid;
                    // 		            $cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
                    $cust->done_date = $done_date;
                    $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                    $cust->done_id = $post['contact_form_id'];
                     
                    $cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
                     
                    // ISPC-2071 - added tabname, this entry must be grouped/sorted
                    $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockSgbv");
                     
                    $cust->save();
                }
        
            }
        
        }
        
        
    }
}

?>