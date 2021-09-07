<?php

// require_once("Pms/Form.php");
/**
 *
 *
 * @update Jan 23, 2018: @author claudiu, checked/modified for ISPC-2071
 * + Fahrtzeit / Dokumentationszeit =  FormBlockDrivetimedoc
 * 
 * before it was $cust->done_name = Pms_CommonData::aesEncrypt("contact_form_drivetimedoc");, now is the component name
 * changed: bypass Trigger() on PC
 * fixed: adding empty values to PC
 * 
 * TODO ask: ISPC-1384 add a text field (numbers only) for "Dokumentationszit" -> input is not mask(number)
 * TODO ask: Dokumentationszit or Dokumentationszeit ?? block and PC have different labels
 *
 */
class Application_Form_FormBlockDrivetimedoc extends Pms_Form 
{

	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if ( ! empty($contact_form_id))
		{
			$Q = Doctrine_Query::create()
				->update('FormBlockDrivetimedoc')
				->set('isdelete', '1')
				->where("contact_form_id = ?", $contact_form_id )
				->andWhere('ipid = ?', $ipid);
			$result = $Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}

	public function InsertData($post, $allowed_blocks)
	{
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        
        $bowel_movement_block = new FormBlockDrivetimedoc();
        
        //set the old block values as isdelete
        $clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
        
        
        if ( ! empty($post['old_contact_form_id'])) {
            
            $change_date = $post['contact_form_change_date'];
        
            $befund_old_data = $bowel_movement_block->getPatientFormBlockDrivetimedoc($post['ipid'], $post['old_contact_form_id'], true);
            
            if ( ! in_array('drivetime_doc', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                if ( ! empty($befund_old_data)) {
                    
                    $post['fahrtzeit1'] = $befund_old_data[0]['fahrtzeit1'];
                    $post['fahrtstreke_km1'] = $befund_old_data[0]['fahrtstreke_km1'];
                    $post['fahrt_doc1'] = $befund_old_data[0]['fahrt_doc1'];
                    
                }
            }
            else {
                //we have permissions and cf is being edited
                //write changes in PatientCourse is something was changed
                if ( ! empty($befund_old_data)) {
                    
                    if ((int)$post['fahrtzeit1'] != (int)$befund_old_data[0]['fahrtzeit1']
                        || $post['fahrtstreke_km1'] != $befund_old_data[0]['fahrtstreke_km1']
                        || $post['fahrt_doc1'] != $befund_old_data[0]['fahrt_doc1'] ) 
                    {
                        //something was edited, we must insert into PC
                        $save_2_PC = true;
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
        
        
        
        $cust = new FormBlockDrivetimedoc();
        $cust->ipid = $post['ipid'];
        $cust->contact_form_id = $post['contact_form_id'];
        $cust->fahrtzeit1 = $post['fahrtzeit1'];
        $cust->fahrtstreke_km1 = $post['fahrtstreke_km1'];
        $cust->fahrt_doc1 = $post['fahrt_doc1'];
        
        
        if ($save_2_PC && in_array('drivetime_doc', $allowed_blocks)) {
            
            if (($post['fahrtzeit1'] == '--' || empty($post['fahrtzeit1']))
                && ($post['fahrtstreke_km1'] == '0.00' || empty($post['fahrtstreke_km1']))
                && empty($post['fahrt_doc1'] ))
            {
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockDrivetimedoc');
                }
                
            } else {
                
                $course_title_lines =  array();
                
                if ($post['fahrtzeit1'] != '--') {
                    $course_title_lines[] = "Fahrtzeit:" . $post['fahrtzeit1'] ;
                }
                
                if ($post['fahrtstreke_km1'] != '0.00' && ! empty($post['fahrtstreke_km1'])) {
                    $course_title_lines[] = "Fahrtstrecke:" . $post['fahrtstreke_km1'] ;
                }
                
                if ( ! empty($post['fahrt_doc1'])) {
                    $course_title_lines[] = "Dokumentationszeit:" . $post['fahrt_doc1'] ;
                }
                
                
                
                if ( ! empty($course_title_lines)
                    && ($pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse')) )
                {
//                     $course_str = "Fahrtzeit / Dokumentationszeit: \n". implode("\n", $course_title_lines);
                    $course_str =  implode("\n", $course_title_lines);
                    $change_date = "";//removed from pc; ISPC-2071
                    
                    $pc_listener->setOption('disabled', false);
                    $pc_listener->setOption('course_title', $course_str . $change_date);
                    $pc_listener->setOption('done_date', $done_date);
                    $pc_listener->setOption('user_id', $userid);
                     
                }
                
            }

        }

        
        $cust->save();
        

    }

}

?>