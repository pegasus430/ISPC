<?php
require_once ("Pms/Form.php");
/**
 * @update Jan 25, 2018: @author claudiu, checked/modified for ISPC-2071
 * SGB XI Leistungen = sgbxi_actions = FormBlockSgbxiActions
 * 
 * changed: bypass Trigger() on PC
 * fixed: on re-edit, DashboardEvents were not created for new checked options
 * fixed: adding this block to a saved cf would not save to PC the first time
 * fixing/changeing: insert only one PC for the entire block - 
 * changed: this block will NO longer save all the client list FormBlocksSettings values, will just save the ones you checked
 */
class Application_Form_FormBlockSgbxiActions extends Pms_Form
{

    public function clear_block_data($ipid = '', $contact_form_id = 0)
    {
        if (! empty($contact_form_id)) {
            $Q = Doctrine_Query::create()->update('FormBlockSgbxiActions')
                ->set('isdelete', '1')
                ->where("contact_form_id = ?", $contact_form_id)
                ->andWhere('ipid = ?', $ipid);
            $Q->execute();
            
            return true;
        } else {
            return false;
        }
    }

    public function InsertData($post, $allowed_blocks, $patient_details = false, $user_dateils = false)
    {
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $sgbxi_actions_block = new FormBlockSgbxiActions();
        
        $blocks_settings = new FormBlocksSettings();
        $block_sgbxi_actions_values = $blocks_settings->get_block($clientid, 'sgbxi_actions');
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        
        
        $client_sgbxi_actions_values =  array();
        
        foreach($block_sgbxi_actions_values as $client_list_value) {
            $client_sgbxi_actions_values[$client_list_value['id']] = $client_list_value;
        }
       
        
        // get koordinator usergroups users
        $usr = new User();
        $usergroup = new Usergroup();
        
        $pqarr = $usr->getUserByClientid($clientid);
        $comma = ",";
        $userval = "'0'";
        
        $records_actions = array();
        
        
        foreach ($pqarr as $key => $val) {
            
            $userval .= $comma . "'" . $val['id'] . "'";
            $comma = ",";
        }
        $groupid = $usergroup->getMastergroupGroups($clientid, array(
            '6'
        ));
        $users = $usr->getuserbyidsandGroupId($userval, $groupid, 0);
        
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
        
      
        
//         foreach ($block_sgbxi_actions_values as $key => $action_values) {
            
//             if ($post['egblock']['sgbxi_actions'][$action_values['id']] > 0) {
//                 $value = 1;
//             } else {
                
//                 $value = 0;
//             }
            
//             $records[] = array(
//                 "ipid" => $post['ipid'],
//                 "contact_form_id" => $post['contact_form_id'],
//                 "action_id" => $action_values['id'],
//                 "action_value" => $value
//             );
            
//             if ($post['old_contact_form_id'] == 0) {
//                 if ($post['egblock']['sgbxi_actions'][$action_values['id']] > 0) {
//                     $cust = new PatientCourse();
//                     $cust->ipid = $post['ipid'];
//                     $cust->course_date = date("Y-m-d H:i:s", time());
//                     $cust->course_type = Pms_CommonData::aesEncrypt("K");
//                     $cust->course_title = Pms_CommonData::aesEncrypt("SGB XI Leistungen : " . $action_values['option_name']);
//                     $cust->user_id = $userid;
//                     $cust->done_date = $done_date;
//                     $cust->done_name = Pms_CommonData::aesEncrypt("contact_form_measures");
//                     $cust->done_id = $post['contact_form_id'];
//                     $cust->save();
//                 }
                
//                 if ($post['egblock']['sgbxi_actions'][$action_values['id']] > 0) {
                    
//                     $dashboard_action_title = 'Die Leistung "' . $action_values['option_name'] . '" (SGB XI) wurde von ' . $user_dateils['first_name'] . ' ' . $user_dateils['last_name'] . ' bei ' . $patient_details['first_name'] . ' ' . $patient_details['last_name'] . ' ausgeführt.';
                    
//                     if ($action_values['coordinator_notification'] == 1) {
//                         foreach ($users as $k_usr => $v_usr) {
//                             if ($k_usr > '0') {
//                                 $records_actions[] = array(
//                                     'client_id' => $clientid,
//                                     'user_id' => $k_usr,
//                                     'group_id' => '0',
//                                     'ipid' => $post['ipid'],
//                                     'tabname' => 'sgbxi',
//                                     'triggered_by' => 'system_sgbxi_action_' . $action_values['id'],
//                                     'title' => $dashboard_action_title,
//                                     'isdelete' => '0',
//                                     'iscompleted' => '0',
//                                     'create_date' => date("Y-m-d H:i:s", time()),
//                                     'until_date' => $done_date
//                                 );
//                             }
//                         }
//                     }
//                 }
//             }
//         }
        
        
        
        
        //create FormBlockSgbxiActions records, only checked ones will be saved(cause this is how POST works.. you want the uncheked add a hidden)
        if (in_array('sgbxi_actions', $allowed_blocks)) { //this if was added to prevent using the same variable names in the post, for different blocks
            foreach ($post['egblock']['sgbxi_actions'] as $k=>$inserted_value) {
                if (isset($client_sgbxi_actions_values[$k])) {
                    $records[] = array(
                        "ipid"              => $post['ipid'],
                        "contact_form_id"   => $post['contact_form_id'],
                        "action_id"         => $k,
                        "action_value"      => $inserted_value,
                    );
                }
            }
        }

        
        if ( ! empty($post['old_contact_form_id']))
        { 
            $change_date = $post['contact_form_change_date'];
            
            $sgbxi_actions_old_data = $sgbxi_actions_block->getPatientFormBlockSgbxiActions($post['ipid'], $post['old_contact_form_id'], true);
            
            if ( ! in_array('sgbxi_actions', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                if ( ! empty($sgbxi_actions_old_data)) {
        
                    $records = array();
                    foreach ($sgbxi_actions_old_data as $inserted_value)
                    {
                        
                        $records[] = array(
                            "ipid"              => $post['ipid'],
                            "contact_form_id"   => $post['contact_form_id'],
                            "action_id"         => $inserted_value['action_id'],
                            "action_value"      => $inserted_value['action_value'],
//                             "isdelete"          => $inserted_value['isdelete'], //add this too?
                            
                        );
                        
                    }
                }
            }
            else {
                //we have permissions and cf is being edited
                //write changes in PatientCourse is something was changed
                    
                if ( ! empty($sgbxi_actions_old_data)) {
                    

                    if (count($sgbxi_actions_old_data) != count($post['egblock']['sgbxi_actions'])) {
                        $save_2_PC = true;
                    }
                    
                    foreach ($post['egblock']['sgbxi_actions'] as $k=>$inserted_value) {
                        if ($inserted_value > 0 && ( ! isset($sgbxi_actions_old_data[$k]) || $sgbxi_actions_old_data[$k] != $inserted_value)) {
                            // not same keys or value, this was un-cheked last time..

                            $save_2_PC = true;
                                
                            //create Dasboard records
                            if ($client_sgbxi_actions_values[$k]['coordinator_notification'] == 1) {

                                $dashboard_action_title = 'Die Leistung "' . $client_sgbxi_actions_values[$k]['option_name'] . '" (SGB XI) wurde von ' . $user_dateils['first_name'] . ' ' . $user_dateils['last_name'] . ' bei ' . $patient_details['first_name'] . ' ' . $patient_details['last_name'] . ' ausgeführt.';
                                
                                foreach ($users as $k_usr => $v_usr) {
                                    if ($k_usr > '0') {
                                        $records_actions[] = array(
                                            'client_id' => $clientid,
                                            'user_id' => $k_usr,
                                            'group_id' => '0',
                                            'ipid' => $post['ipid'],
                                            'tabname' => 'sgbxi',
                                            'triggered_by' => 'system_sgbxi_action_' . $k,
                                            'title' => $dashboard_action_title,
                                            'isdelete' => '0',
                                            'iscompleted' => '0',
                                            'create_date' => date("Y-m-d H:i:s", time()),
                                            'until_date' => $done_date
                                        );
                                    }
                                }
                            }
                        }
                    }
                    
                            
                }
                else {
                    //nothing was edited last time, or this block was added after the form was created
                    $save_2_PC = true;
                    $change_date = '';
                    
                    //create Dasboard records
                    foreach ($post['egblock']['sgbxi_actions'] as $k=>$inserted_value) {
                        if ($inserted_value > 0 && $client_sgbxi_actions_values[$k]['coordinator_notification'] == 1) {
                            
                            $dashboard_action_title = 'Die Leistung "' . $client_sgbxi_actions_values[$k]['option_name'] . '" (SGB XI) wurde von ' . $user_dateils['first_name'] . ' ' . $user_dateils['last_name'] . ' bei ' . $patient_details['first_name'] . ' ' . $patient_details['last_name'] . ' ausgeführt.';
                
                            foreach ($users as $k_usr => $v_usr) {
                                if ($k_usr > '0') {
                                    $records_actions[] = array(
                                        'client_id' => $clientid,
                                        'user_id' => $k_usr,
                                        'group_id' => '0',
                                        'ipid' => $post['ipid'],
                                        'tabname' => 'sgbxi',
                                        'triggered_by' => 'system_sgbxi_action_' . $k,
                                        'title' => $dashboard_action_title,
                                        'isdelete' => '0',
                                        'iscompleted' => '0',
                                        'create_date' => date("Y-m-d H:i:s", time()),
                                        'until_date' => $done_date
                                    );
                                }
                            }                   
                        }
                    }
                     
                }
            }
        } else {
            //new cf, save
            $save_2_PC = true;
            
            //create Dasboard records
            foreach ($post['egblock']['sgbxi_actions'] as $k=>$inserted_value) {
                
                if ($inserted_value > 0 && $client_sgbxi_actions_values[$k]['coordinator_notification'] == 1) {

                    $dashboard_action_title = 'Die Leistung "' . $client_sgbxi_actions_values[$k]['option_name'] . '" (SGB XI) wurde von ' . $user_dateils['first_name'] . ' ' . $user_dateils['last_name'] . ' bei ' . $patient_details['first_name'] . ' ' . $patient_details['last_name'] . ' ausgeführt.';
            
                    foreach ($users as $k_usr => $v_usr) {
                        if ($k_usr > '0') {
                            $records_actions[] = array(
                                'client_id' => $clientid,
                                'user_id' => $k_usr,
                                'group_id' => '0',
                                'ipid' => $post['ipid'],
                                'tabname' => 'sgbxi',
                                'triggered_by' => 'system_sgbxi_action_' . $k,
                                'title' => $dashboard_action_title,
                                'isdelete' => '0',
                                'iscompleted' => '0',
                                'create_date' => date("Y-m-d H:i:s", time()),
                                'until_date' => $done_date
                            );
                        }
                    }
                }
            }
            
            
        }
        
        
        //save Dasboard records
        if (! empty($records_actions)) {
            $collection = new Doctrine_Collection('DashboardEvents');
            $collection->fromArray($records_actions);
            $collection->save();
        }
        
        
        //set the old block values as isdelete
        $clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
        
        
        $pc_recorddata =  array();
        
        if ( ! empty($records)) {
            $collection = new Doctrine_Collection('FormBlockSgbxiActions');
            $collection->fromArray($records);
            $collection->save();
            
            $pc_recorddata = $collection->getPrimaryKeys();
            
        }
        
        
        
        if ($save_2_PC && in_array('sgbxi_actions', $allowed_blocks)) {
        
            if (empty($records)) {
                //you unchecked all the options
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
        
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockSgbxiActions');
                }
        
            } else {
        
                $course_title_line = array();
                foreach ($records as $row) {
                    if (isset($client_sgbxi_actions_values [$row['action_id']]))
                        $course_title_line[] = $client_sgbxi_actions_values [$row['action_id']] ['option_name'];
                }
        
                if ( ! empty($course_title_line)) {
                    //save to PC
                    $course_str = "SGB XI Leistungen: \n" . implode("\n", $course_title_line);
                    //ebmii edited entry in verlauf
                    $change_date = "";//removed from pc; ISPC-2071
                    
                    $cust = new PatientCourse();
                     
                    //skip Trigger()
                    $cust->triggerformid = null;
                    $cust->triggerformname = null;
                     
                    $cust->ipid = $post['ipid'];
                    $cust->course_date = date("Y-m-d H:i:s", time());
                    $cust->course_type = Pms_CommonData::aesEncrypt( FormBlockSgbxiActions::PATIENT_COURSE_TYPE );
                    $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str).$change_date);
                    $cust->user_id = $userid;
                    // 		            $cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
                    $cust->done_date = $done_date;
                    $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
                    $cust->done_id = $post['contact_form_id'];
                     
                    $cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
                     
                    // ISPC-2071 - added tabname, this entry must be grouped/sorted
                    $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockSgbxiActions");
                     
                    $cust->save();
                    
                    
                }
        
            }
        
        }
        
        
        
//         if (strlen($post['old_contact_form_id']) > 0) {
//             $sgbxi_actions_old_data = $sgbxi_actions_block->getPatientFormBlockSgbxiActions($post['ipid'], $post['old_contact_form_id'], true);
            
//             if ($sgbxi_actions_old_data) {
//                 // overide post data if no permissions on sgbxi_actions block
//                 if (! in_array('sgbxi_actions', $allowed_blocks)) {
//                     $records = array();
//                     foreach ($block_sgbxi_actions_values as $ke => $action_values) {
//                         if ($sgbxi_actions_old_data[$action_values['id']] > 0) {
//                             $value = 1;
//                         } else {
//                             $value = 0;
//                         }
                        
//                         $records[] = array(
//                             "ipid" => $post['ipid'],
//                             "contact_form_id" => $post['contact_form_id'],
//                             "action_id" => $action_values['id'],
//                             "action_value" => $value
//                         );
//                     }
//                 } else {
//                     $course_str = "SGB XI Leistungen: \n";
                    
//                     $options = array();
//                     foreach ($block_sgbxi_actions_values as $ke => $action_values) {
//                         // allow only checked values and those which are not in old cf
//                         if ($post['egblock']['sgbxi_actions'][$action_values['id']] == '1' && $sgbxi_actions_old_data[$action_values['id']] != '1') {
//                             $options[] = '1';
//                             $course_str .= $action_values['option_name'] . "\n";
//                         }
//                     }
                    
//                     if (! empty($options)) {
//                         // sgbxi edited entry in verlauf
//                         $cust = new PatientCourse();
//                         $cust->ipid = $post['ipid'];
//                         $cust->course_date = date("Y-m-d H:i:s", time());
//                         $cust->course_type = Pms_CommonData::aesEncrypt("K");
//                         $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str));
//                         $cust->user_id = $userid;
//                         $cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
//                         $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
//                         $cust->done_id = $post['contact_form_id'];
//                         $cust->save();
//                     }
//                 }
//             }
//         }
        
        
        
        
        
    }
    
}

?>