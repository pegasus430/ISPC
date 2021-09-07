<?php

// require_once("Pms/Form.php");
/**
 *
 *
 * @update Jan 22, 2018: @author claudiu, checked for ISPC-2071
 * BRA - SAPV Team - Hausarzt Einsatz =  FormBlockBraSapv
 * 
 * changed: bypass Trigger() on PC
 *
 */
class Application_Form_FormBlockBraSapv extends Pms_Form 
{

	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if(!empty($contact_form_id))
		{

			$Q = Doctrine_Query::create()
				->update('FormBlockBraSapv')
				->set('isdelete', '1')
				->where("contact_form_id= ?", $contact_form_id)
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
		$form_bra_sapv = new FormBlockBraSapv();

		$save_2_PC = false; //if we have insert or update on PatientCourse
		$change_date = '';
		$course_str = '';
		
		//set the old block values as isdelete
		$clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
		
		if ( ! empty($post['old_contact_form_id']))
		{
			$bra_sapv_old_data = $form_bra_sapv->getPatientFormBlockBraSapv($post['ipid'], $post['old_contact_form_id'], true);

			if ( ! in_array('bra_sapv', $allowed_blocks)) {
			    // override post data if no permissions on block
			    // PatientCourse will NOT be inserted
			    if ( ! empty($bra_sapv_old_data)) {
				    $post['sapv_team_doctor'] = $bra_sapv_old_data[0]['sapv_team_doctor'];
			    }
			}
			else {
				//we have permissions and cf is being edited
				//write changes in PatientCourse is something was changed
			    if ( ! empty($bra_sapv_old_data)) {
			        
			        if ((int)$post['sapv_team_doctor'] != (int)$bra_sapv_old_data[0]['sapv_team_doctor']) {
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

		
		if ($save_2_PC && in_array('bra_sapv', $allowed_blocks)) {
		    
		    if ((int)$post['sapv_team_doctor'] == 1) {
		        
		        //add new to PC
		        $course_str = "BRA - SAPV Team - Hausarzt Einsatz: \n";
		        $course_str .= "Einsatz SAPV Team mit Hausarzt (PCT05 - HF02) \n";
		        		        
		    } else {
		        //must remove from PC this option
		        //manualy remove and set $save_2_PC false
		        $save_2_PC =  false;
		        if ( ! empty($post['old_contact_form_id'])) {
    		        $pc_entity = new PatientCourse();
    		        $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockBraSapv');
		        }
		    }
		}	
					
// 					if(!empty($options))
// 					{
// 						//sapv team doctor edited entry in verlauf
// 						$cust = new PatientCourse();
// 						$cust->ipid = $post['ipid'];
// 						$cust->course_date = date("Y-m-d H:i:s", time());
// 						$cust->course_type = Pms_CommonData::aesEncrypt("K");
// 						$cust->course_title = Pms_CommonData::aesEncrypt(htmlspecialchars($course_str));
// 						$cust->user_id = $userid;
// 						$cust->done_date = date('Y-m-d H:i:s', strtotime($post['date']));
// 						$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
// 						$cust->done_id = $post['contact_form_id'];
						
// 						// ISPC-2071 - added tabname, this entry must be grouped/sorted
// 						$cust->tabname = Pms_CommonData::aesEncrypt("FormBlockBraSapv");
						
// 						$cust->save();
// 					}

		$cust = new FormBlockBraSapv();
		$cust->ipid = $post['ipid'];
		$cust->contact_form_id = $post['contact_form_id'];
		$cust->sapv_team_doctor = $post['sapv_team_doctor'];
		

		if ($save_2_PC
		    && in_array('bra_sapv', $allowed_blocks)
		    && ! empty($course_str)
		    && ($pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse')) )
		{
		    $change_date = "";//removed from pc; ISPC-2071
		    $pc_listener->setOption('disabled', false);
		    $pc_listener->setOption('course_title', $course_str . $change_date);
		    $pc_listener->setOption('done_date', date('Y-m-d H:i:s', strtotime($post['date'])));
		    $pc_listener->setOption('user_id', $userid);
		     
		}

		$cust->save();
	}

}

?>