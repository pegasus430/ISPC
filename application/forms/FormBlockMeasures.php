<?php

// require_once("Pms/Form.php");
/**
 * @update Jan 24, 2018: @author claudiu, checked/modified for ISPC-2071
 * Maßnahmen = measures = FormBlockMeasures
 *
 *
 * changed: bypass Trigger() on PC
 * fixed: adding this block to a saved cf would not save to PC the first time
 * fixed/changed: insert only one PC for the entire block
 * changed: this block will NO longer save all the client list FormBlocksSettings values, will just save the ones you checked
 */
class Application_Form_FormBlockMeasures extends Pms_Form
{

    public function clear_block_data($ipid = '', $contact_form_id = 0)
    {
        if (! empty($contact_form_id)) {
            $Q = Doctrine_Query::create()->update('FormBlockMeasures')
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
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $measures_block = new FormBlockMeasures();
        
        $blocks_settings = new FormBlocksSettings();
        $block_measures_values = $blocks_settings->get_block($clientid, 'measures');
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
        
        
        $client_measures_values =  array();
        
        foreach($block_measures_values as $client_list_value) {
            $client_measures_values[$client_list_value['id']] = $client_list_value;
        }
        
        $records = array();
		
		foreach ($post['egblock']['measures'] as $action_id => $action_value) {
		    $records[] = array(
		        "ipid"               => $post['ipid'],
		        "contact_form_id"    => $post['contact_form_id'],
		        "action_id"          => $action_id,
		        "action_value"       => $action_value,
		    );
		}
        
        
        if ( ! empty($post['old_contact_form_id'])) {
        
            $change_date = $post['contact_form_change_date'];
        
            $measures_old_data = $measures_block->getPatientFormBlockMeasures($post['ipid'], $post['old_contact_form_id'], true);
            
        
            if ( ! in_array('measures', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                if ( ! empty($measures_old_data)) {
        
                    $records = array();
        
                    foreach($measures_old_data as $saved_row) {
                         
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
                if ( ! empty($measures_old_data)) {
        
        
                    if (count($measures_old_data) != count($post['egblock']['measures'])) {
                        //something changed
                        $save_2_PC = true;
        
                    } else {
        
                        foreach ($post['egblock']['measures'] as $k=>$inserted_value) {
        
                            if ( ! isset($measures_old_data[$k])) {
                                // not same keys, something changed
                                $save_2_PC = true;
                                break;
        
                            } elseif ((int)$inserted_value != (int)$measures_old_data[$k]) {
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
        
            $collection = new Doctrine_Collection('FormBlockMeasures');
            $collection->fromArray($records);
            $collection->save();
        
            $pc_recorddata = $collection->getPrimaryKeys();
        }
        
        
        

        if ($save_2_PC && in_array('measures', $allowed_blocks)) {
        
            if (empty($records)) {
                //you unchecked all the options
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
        
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockMeasures');
                }
        
            } else {
        
                $course_title_line = array();
        
                foreach ($records as $row) {
        
                    if (isset($client_measures_values [$row['action_id']]))
                        $course_title_line[] = $client_measures_values [$row['action_id']] ['option_name'];
        
                }
        
                if ( ! empty($course_title_line)) {
                    //save to PC
                    $course_str = "Maßnahmen: \n" . implode("\n", $course_title_line);
                    //goa edited entry in verlauf
                    $change_date = "";//removed from pc; ISPC-2071
        
                    $cust = new PatientCourse();
                     
                    //skip Trigger()
                    $cust->triggerformid = null;
                    $cust->triggerformname = null;
                     
                    $cust->ipid = $post['ipid'];
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt( FormBlockMeasures::PATIENT_COURSE_TYPE );
                    $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str).$change_date);
                    $cust->user_id = $userid;
                    // 		            $cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
                    $cust->done_date = $done_date;
                    $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                    $cust->done_id = $post['contact_form_id'];
                     
                    $cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
                     
                    // ISPC-2071 - added tabname, this entry must be grouped/sorted
                    $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockMeasures");
                     
                    $cust->save();
                }
        
            }
        
        }
        
        
        
    }
}

?>