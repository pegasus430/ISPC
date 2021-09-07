<?php

// require_once("Pms/Form.php");
/**
 * @update Jan 24, 2018: @author claudiu, checked/modified for ISPC-2071
 * hospiz_medi = Medikamente stellen / verabreichen = FormBlockHospizmedi
 *
 * changed: bypass Trigger() on PC
 * fixed: empty ipid
 * TODO : 2save or not 2save the empty medications? opted to save ONLY the checked
 */
class Application_Form_FormBlockHospizmedi extends Pms_Form
{

    public function clear_block_data($ipid = '', $contact_form_id = 0)
    {
        if ( ! empty($contact_form_id)) {
            $Q = Doctrine_Query::create()->update('FormBlockHospizmedi')
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
        // print_r($post); exit;
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
        
        $new_values = "";
        $records = array();
        
        $pc_sprepared = array();
        $pc_given = array();
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        
        // create pc and block records
        foreach ($post['hospiz_med'] as $med_id => $data) {
            
            $data['sprepared']  = isset($data['sprepared']) ? 1 : 0;
            $data['given']      = isset($data['given']) ? 1 : 0;
           
            
            if ($data['sprepared'] == 1 || $data['given'] == 1) {
                // changed to save only the checked ones
                $records[] = array(
                    "ipid" => $post['ipid'],
                    "contact_form_id" => $post['contact_form_id'],
                    "medication" => $med_id,
                    "sprepared" => $data['sprepared'],
                    "given" => $data['given']
                );
                
                // this are for pc
                if ($data['sprepared'] == 1) {
                    $pc_sprepared[] = $data['medication'] . ' ' . $data['dosage'];
                }
                
                if ($data['given'] == 1) {
                    $pc_given[] = $data['medication'] . ' ' . $data['dosage'];
                }
            }
            
            $new_values .= $med_id . $data['sprepared'] . $data['given'];
        }
        
        
        if (! empty($post['old_contact_form_id'])) {
            
            $change_date = $post['contact_form_change_date'];
            
            $hospiz_medi_block = new FormBlockHospizmedi();
            $befund_old_data = $hospiz_medi_block->getPatientFormBlockHospizmedi($post['ipid'], $post['old_contact_form_id'], true);
            
            if (! in_array('hospiz_medi', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                if (! empty($befund_old_data)) {
                    
                    $records = array();
                    
                    foreach ($befund_old_data as $med_id => $data) {
                        $records[] = array(
                            "ipid" => $post['ipid'],
                            "contact_form_id" => $post['contact_form_id'],
                            "medication" => $med_id,
                            "sprepared" => $data['sprepared'],
                            "given" => $data['given'],
//                             "isdelete" => $data['isdelete'] // copy this too?
                        );
                    }
                }
            } else {
                // we have permissions and cf is being edited
                // write changes in PatientCourse is something was changed
                if (! empty($befund_old_data)) {
                    
                    if (count($befund_old_data) != count($records)) {
                        // something changed
                        $save_2_PC = true;
                    } else {
                        
                        foreach ($records as $row) {
                            
                            if (! isset($befund_old_data[$row['medication']])) {
                                // not same keys, something changed
                                $save_2_PC = true;
                                break;
                                
                            } elseif ((int) $row['sprepared'] != (int) $befund_old_data[$row['medication']]['sprepared'] 
                                || (int) $row['given'] != (int) $befund_old_data[$row['medication']]['given']) 
                            {
                                // compared each value to check if something changed, not same values
                                $save_2_PC = true;
                                break;
                            }
                        }
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
        
        
        
        //save the block
        $pc_recorddata =  array();
        
        if ( ! empty($records)) {
            $collection = new Doctrine_Collection('FormBlockHospizmedi');
            $collection->fromArray($records);
            $collection->save();
            
		    $pc_recorddata = $collection->getPrimaryKeys();
        }
        
        
        //save into pc
        if ($save_2_PC && in_array('hospiz_medi', $allowed_blocks)) {
            
            //$pc_sprepared =>  MG
            if ( ! empty($pc_sprepared)) {
                
                $change_date = "";//removed from pc; ISPC-2071
                
                $cust = new PatientCourse();
                
                //skip Trigger()
                $cust->triggerformid = null;
                $cust->triggerformname = null;
                
                $cust->ipid = $post['ipid'];
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt("MG");
                $cust->course_title = Pms_CommonData::aesEncrypt("Medikamente gestellt : " . implode(', ', $pc_sprepared) . $change_date);
                $cust->user_id = $userid;
                $cust->done_date = $done_date;
                $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                $cust->done_id = $post['contact_form_id'];
                
                // ISPC-2071 - added tabname, this entry must be grouped/sorted
                $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockHospizmedi");
                
                $cust->save();
                
                
            } else {
                //you unchecked all the options
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
                
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockHospizmedi', $is_removed = 'yes', $remove_childrens = true, $course_types = 'MG');
                }
            }
            
            //$pc_given => MV
            if ( ! empty($pc_given)) {
            
                $change_date = "";//removed from pc; ISPC-2071
                
                $cust = new PatientCourse();
            
                //skip Trigger()
                $cust->triggerformid = null;
                $cust->triggerformname = null;
            
                $cust->ipid = $post['ipid'];
                $cust->course_date = date("Y-m-d H:i:s", time());
                $cust->course_type = Pms_CommonData::aesEncrypt("MV");
                $cust->course_title = Pms_CommonData::aesEncrypt("Medikamente  verabreicht : " .implode(', ', $pc_given) . $change_date);
                $cust->user_id = $userid;
                $cust->done_date = $done_date;
                $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                $cust->done_id = $post['contact_form_id'];
            
                // ISPC-2071 - added tabname, this entry must be grouped/sorted
                $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockHospizmedi");
            
                $cust->save();
            
            
            } else {
                //you unchecked all the options
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
            
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockHospizmedi', $is_removed = 'yes', $remove_childrens = true, $course_types = 'MV');
                }
            }
        }
        
      
    }
}

?>