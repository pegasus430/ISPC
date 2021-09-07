<?php

require_once("Pms/Form.php");
/**
 *
 *
 * @update Jan 23, 2018: @author claudiu, checked/modified for ISPC-2071
 * EBM Hausbesuch =  FormBlockEbmii
 *
 *
 * changed: bypass Trigger() on PC
 * fixing: adding this block to a saved cf would not save to PC the first time
 * fixing: this block will NO longer save all the client list FormBlocksSettings values, will just save the ones you checked
 *
 */
class Application_Form_FormBlockEbmii extends Pms_Form
{


	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if ( ! empty($contact_form_id))
		{
			$Q = Doctrine_Query::create()
			->update('FormBlockEbmii')
			->set('isdelete','1')
			->where("contact_form_id = ?", $contact_form_id)
			->andWhere('ipid = ?', $ipid);
			$Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}

	
	
	public function InsertData($post,$allowed_blocks)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$ebmii_block = new FormBlockEbmii();

		$blocks_settings = new FormBlocksSettings();
		$block_ebmii_values = $blocks_settings->get_block($clientid,'ebmii');

		$save_2_PC = false; //if we have insert or update on PatientCourse
		$change_date = '';
		$course_str = '';
		$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
		
		
		$client_ebmii_values =  array();
		
		foreach($block_ebmii_values as $client_list_value) {
		    $client_ebmii_values[$client_list_value['id']] = $client_list_value;

		}
		
		$records = array();
		
		foreach ($post['egblock']['ebmii'] as $action_id => $action_value) {
		    
		    //this block will NO longer save all the client list FormBlocksSettings values, will just save the ones you checked
		    if ($action_value['value'] !=1) {
		        continue;
		    }
		    
		    $records[] = array(
		        "ipid"               => $post['ipid'],
		        "contact_form_id"    => $post['contact_form_id'],
		        "action_id"          => $action_id,
		        "action_value"       => $action_value,
		    );
		}
		

		
		
		if ( ! empty($post['old_contact_form_id'])) {
		
		    $change_date = $post['contact_form_change_date'];
		
		    $ebmii_old_data = $ebmii_block->getPatientFormBlockEbmii($post['ipid'], $post['old_contact_form_id'], true);
		
		    if ( ! in_array('ebmii', $allowed_blocks)) {
		        // override post data if no permissions on block
		        // PatientCourse will NOT be inserted
		        if ( ! empty($ebmii_old_data)) {
		
		            $records = array();
		
		            foreach($ebmii_old_data as $saved_row) {
		                	
		                $records[] = array(
		                    "ipid"               => $post['ipid'],
		                    "contact_form_id"    => $post['contact_form_id'],
		                    "action_id"          => $saved_row['action_id'],
		                    "action_value"       => $saved_row['action_value'],
// 		                    "isdelete"           => $saved_row['isdelete'], // copy isdelete also?
		                );
		            }
		
		        }
		    }
		    else {
		        //we have permissions and cf is being edited
		        //write changes in PatientCourse is something was changed
		        if ( ! empty($ebmii_old_data)) {
		
		
		            if (count($ebmii_old_data) != count($post['egblock']['ebmii'])) {
		                //something changed
		                $save_2_PC = true;
		
		            } else {
		
		                foreach ($post['egblock']['ebmii'] as $k=>$inserted_value) {
		
		                    if ( ! isset($ebmii_old_data[$k])) {
		                        // not same keys, something changed
		                        $save_2_PC = true;
		                        break;
		
		                    } elseif ((int)$inserted_value != (int)$ebmii_old_data[$k]) {
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
		$clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id']);
		
		
		
		$pc_recorddata =  array();
		
		if ( ! empty($records)) {
		    
		    $collection = new Doctrine_Collection('FormBlockEbmii');
		    $collection->fromArray($records);
		    $collection->save();
		
		    $pc_recorddata = $collection->getPrimaryKeys();
		}
		
		
		
		if ($save_2_PC && in_array('ebmii', $allowed_blocks)) {
		
		    if (empty($records)) {
		        //you unchecked all the options
		        //must remove from PC this option
		        //manualy remove and set $save_2_PC false
		        $save_2_PC =  false;
		
		        if ( ! empty($post['old_contact_form_id'])) {
		            $pc_entity = new PatientCourse();
		            $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockEbmii');
		        }
		
		    } else {
		
		        $course_title_line = array();
		
		        foreach ($records as $row) {
		
		            if (isset($client_ebmii_values [$row['action_id']]))
		                $course_title_line[] = $client_ebmii_values [$row['action_id']] ['option_name'];
		
		        }
		
		        if ( ! empty($course_title_line)) {
		            //save to PC
		            $course_str = "EBM Hausbesuch: \n" . implode("\n", $course_title_line);
		            //ebmii edited entry in verlauf
		            $change_date = "";//removed from pc; ISPC-2071
		            
		            $cust = new PatientCourse();
		             
		            //skip Trigger()
		            $cust->triggerformid = null;
		            $cust->triggerformname = null;
		             
		            $cust->ipid = $post['ipid'];
		            $cust->course_date = date("Y-m-d H:i:s", time());
		            $cust->course_type = Pms_CommonData::aesEncrypt( FormBlockEbmii::PATIENT_COURSE_TYPE );
		            $cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str).$change_date);
		            $cust->user_id = $userid;
// 		            $cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
		            $cust->done_date = $done_date;
		            $cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
		            $cust->done_id = $post['contact_form_id'];
		             
		            $cust->recorddata = ( ! empty($pc_recorddata)) ?  serialize($pc_recorddata) : null;
		             
		            // ISPC-2071 - added tabname, this entry must be grouped/sorted
		            $cust->tabname = Pms_CommonData::aesEncrypt("FormBlockEbmii");
		             
		            $cust->save();
		        }
		
		    }
		
		}
		
		
	}
}

?>