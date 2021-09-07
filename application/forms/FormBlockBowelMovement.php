<?php

// require_once("Pms/Form.php");
/**
 *
 *
 * @update Jan 22, 2018: @author claudiu, checked for ISPC-2071
 * letzter Stuhlgang = FormBlockBowelMovement
 * 
 * changed: bypass Trigger() on PC
 * fixed: adding this block to a saved cf would not save to PC the first time
 */
class Application_Form_FormBlockBowelMovement extends Pms_Form 
{

	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if ( ! empty($contact_form_id))
		{
			$Q = Doctrine_Query::create()
				->update('FormBlockBowelMovement')
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
		
		if (empty($post['ipid'])) {
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);
		} else {
		    $ipid = $post['ipid'];
		}
		
		$save_2_PC = false; //if we have insert or update on PatientCourse
		$change_date = '';
		
		$bowel_movement_block = new FormBlockBowelMovement();

		if (isset($post['bowel_movement']['bowel_movement_date'])){
			$datetime = DateTime::createFromFormat("d.m.Y", $post['bowel_movement']['bowel_movement_date']);
			$post['bowel_movement']['bowel_movement_date'] = $datetime->format("Y-m-d H:i:s");
		}

		//set the old block values as isdelete
		$clear_block_entryes = $this->clear_block_data($ipid, $post['old_contact_form_id']);
		
		if ( ! empty($post['old_contact_form_id'])) {
			
		    $change_date = $post['contact_form_change_date'];
			
			$befund_old_data = $bowel_movement_block->getPatientFormBlockBowelMovement($ipid, $post['old_contact_form_id'], true);
			
			if ( ! in_array('bowel_movement', $allowed_blocks) ) {
			    // override post data if no permissions on block
			    // PatientCourse will NOT be inserted
			    if ( ! empty($befund_old_data)) {
    			    $post['bowel_movement']['bowel_movement'] = $befund_old_data[0]['bowel_movement'];
    			    $post['bowel_movement']['bowel_movement_date'] = $befund_old_data[0]['bowel_movement_date'];
    			    $post['bowel_movement']['bowel_movement_description'] = $befund_old_data[0]['bowel_movement_description'];
			    }
			}
			else {
				//we have permissions and cf is being edited
				//write changes in PatientCourse is something was changed
				if ( ! empty($befund_old_data)) {
			        
			        if ((int)$post['bowel_movement']['bowel_movement'] != $befund_old_data[0]['bowel_movement'] 
			            || date('d.m.Y', strtotime($post['bowel_movement']['bowel_movement_date'])) != date('d.m.Y', strtotime($befund_old_data[0]['bowel_movement_date'])) 
			            || $post['bowel_movement']['bowel_movement_description'] != $befund_old_data[0]['bowel_movement_description'])
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
		
		
		
		$coursecomment = '';
		if (in_array('bowel_movement', $allowed_blocks)) {
			if($post['bowel_movement']['bowel_movement'] == '1')
			{
			    if($post['bowel_movement']['bowel_movement_description'] != '')
			    {
			        $coursecomment = " letzter Stuhlgang: Ja - ". date('d.m.Y', strtotime($post['bowel_movement']['bowel_movement_date'])) ." - ". $post['bowel_movement']['bowel_movement_description'];
			    }
			    else
			    {
			        $coursecomment = " letzter Stuhlgang: Ja - ". date('d.m.Y', strtotime($post['bowel_movement']['bowel_movement_date']));
			    }
			}
			else
			{
			    if($post['bowel_movement']['bowel_movement_description'] != '')
			    {
			        $coursecomment = " letzter Stuhlgang: Nein - ". date('d.m.Y', strtotime($post['bowel_movement']['bowel_movement_date'])) ." - ". $post['bowel_movement']['bowel_movement_description'];
			    }
			    else
			    {
			        $coursecomment = "";
			    }
			}
		}

		$cust = new FormBlockBowelMovement();
		$cust->ipid = $ipid;
		$cust->contact_form_id = $post['contact_form_id'];
		
		$cust->bowel_movement = $post['bowel_movement']['bowel_movement'] == '1' ? 1: 0;
		
		$cust->bowel_movement_date = $post['bowel_movement']['bowel_movement_date'];
		$cust->bowel_movement_description = $post['bowel_movement']['bowel_movement_description'];			
		
		
		$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));

		if ($save_2_PC
		    && in_array('bowel_movement', $allowed_blocks)
		    && ! empty($coursecomment)
		    && ($pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse')) )
		{
		    $change_date = "";//removed from pc; ISPC-2071
		    $pc_listener->setOption('disabled', false);
		    $pc_listener->setOption('course_title', $coursecomment . $change_date);
		    $pc_listener->setOption('done_date', $done_date);
		    $pc_listener->setOption('user_id', $userid);
		     
		}
		
		$cust->save();
					
		
// 			if ($save_2_PC 
// 			    && ! empty($coursecomment) 
// 			    && in_array('bowel_movement', $allowed_blocks))
// 			{
// 				$cust = new PatientCourse();
// 				$cust->ipid = $ipid;
// 				$cust->course_date = date("Y-m-d H:i:s", time());
// 				$cust->course_type = Pms_CommonData::aesEncrypt("K");
// 				$cust->course_title = Pms_CommonData::aesEncrypt($coursecomment.$change_date);
// 				$cust->user_id = $userid;
// 				$cust->done_date = $done_date;
// 				$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
// 				$cust->done_id = $post['contact_form_id'];
				
// 				// ISPC-2071 - added tabname, this entry must be grouped/sorted
// 				$cust->tabname = Pms_CommonData::aesEncrypt("FormBlockBowelMovement");
				
// 				$cust->save();
// 			}
			
	}

}

?>