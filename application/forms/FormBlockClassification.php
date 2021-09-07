<?php

// 	require_once("Pms/Form.php");
/**
 *
 *
 * @update Jan 23, 2018: @author claudiu, checked for ISPC-2071
 * Klassifizierung =  FormBlockClassification
 * 
 * changed: bypass Trigger() on PC
 * fixed: adding this block to a saved cf would not save to PC the first time
 *
 */
class Application_Form_FormBlockClassification extends Pms_Form 
{

	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if ( ! empty($contact_form_id))
		{

			$Q = Doctrine_Query::create()
				->update('FormBlockClassification')
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
		$form_classification = new FormBlockClassification();


		$save_2_PC = false; //if we have insert or update on PatientCourse
		$change_date = '';
		$course_str = '';
		
		//set the old block values as isdelete
		$clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
		

		if ( ! empty($post['old_contact_form_id']))
		{
		    $change_date = $post['contact_form_change_date'];
		    
		    $classification_old_data = $form_classification->getPatientFormBlockClassification($post['ipid'], $post['old_contact_form_id'], true);
		
		    if ( ! in_array('classification', $allowed_blocks)) {
		        // override post data if no permissions on block
		        // PatientCourse will NOT be inserted
		        if ( ! empty($classification_old_data)) {
		            $post['beratung'] = $classification_old_data[0]['beratung'];
		            $post['koordination'] = $classification_old_data[0]['koordination'];
		            $post['intern'] = $classification_old_data[0]['intern'];
		            $post['psyhosocial'] = $classification_old_data[0]['psyhosocial'];
		            $post['grief_endcall'] = $classification_old_data[0]['grief_endcall'];
		        }
		    }
		    else {
		        //we have permissions and cf is being edited
		        //write changes in PatientCourse is something was changed
		        if ( ! empty($classification_old_data)) {
		             
		            if ((int)$post['beratung'] != (int)$classification_old_data[0]['beratung']
		                || (int)$post['koordination'] != (int)$classification_old_data[0]['koordination']
	                    || (int)$post['intern'] != (int)$classification_old_data[0]['intern']
                        || (int)$post['psyhosocial'] != (int) $classification_old_data[0]['psyhosocial']
                        || (int)$post['grief_endcall'] != (int)$classification_old_data[0]['grief_endcall'])
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
		
		
		
		if ($save_2_PC && in_array('classification', $allowed_blocks)) {
		    
			if ($post['beratung'] == '1') {
				$course_str .= "Beratung \n";
			}
			
			if ($post['koordination'] == '1' ) {
				$course_str .= "Koordination \n";
			}
			
			if ($post['intern'] == '1' ) {
				$course_str .= "intern \n";
			}
			
			if ($post['psyhosocial'] == '1' ) {
				$course_str .= "Psychosoziale Betreuung \n";
			}
			
			if ($post['grief_endcall'] == '1' ) {
				$course_str .= "Trauer/-Abschlussgespräch \n";
			}
			
			if (empty($course_str)) {
			    //you unchecked all the options
			    //must remove from PC this option
			    //manualy remove and set $save_2_PC false
			    $save_2_PC =  false;
			    if ( ! empty($post['old_contact_form_id'])) {
    			    $pc_entity = new PatientCourse();
    			    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockClassification');
			    }
			}
			
		}

		
		$cust = new FormBlockClassification();
		$cust->ipid = $post['ipid'];
		$cust->contact_form_id = $post['contact_form_id'];
		$cust->beratung = $post['beratung'];
		$cust->koordination = $post['koordination'];
		$cust->intern = $post['intern'];
		$cust->psyhosocial = $post['psyhosocial'];
		$cust->grief_endcall = $post['grief_endcall'];
		

		if ($save_2_PC
		    && in_array('classification', $allowed_blocks)
		    && ! empty($course_str)
		    && ($pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse')) )
		{
		    $course_str = "Klassifizierung: \n".$course_str;
		    $change_date = "";//removed from pc; ISPC-2071
		    
		    $pc_listener->setOption('disabled', false);
		    $pc_listener->setOption('course_title', $course_str . $change_date);
		    $pc_listener->setOption('done_date', date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':'.date('s', $now))));
		    $pc_listener->setOption('user_id', $userid);
		     
		}
		
		$cust->save();
		
			
	}

}

?>