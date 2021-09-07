<?php

require_once("Pms/Form.php");

/**
 *
 *
 * @update Jan 24, 2018: @author claudiu, checked/modified for ISPC-2071
 * Ein- / Ausfuhr = FormBlockHospizimex
 *
 *
 * changed: bypass Trigger() on PC
 * fixing: adding this block to a saved cf would not save to PC the first time
 *
 */
class Application_Form_FormBlockHospizimex extends Pms_Form 
{

	public function clear_block_data($ipid = '', $contact_form_id = 0)
	{
		if ( ! empty($contact_form_id))
		{
			$Q = Doctrine_Query::create()
				->update('FormBlockHospizimex')
				->set('isdelete', '1')
				->where("contact_form_id = ?", $contact_form_id)
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
        
        
        $save_2_PC = false; //if we have insert or update on PatientCourse
        $change_date = '';
        $course_str = '';
        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));       
        
        
        //set the old block values as isdelete
        $clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
        

        if ( ! empty($post['old_contact_form_id'])) {
        
            $change_date = $post['contact_form_change_date'];
        
            $bowel_movement_block = new FormBlockHospizimex();
            $befund_old_data = $bowel_movement_block->getPatientFormBlockHospizimex($post['ipid'], $post['old_contact_form_id'], true);
        
            if ( ! in_array('hospiz_imex', $allowed_blocks)) {
                // override post data if no permissions on block
                // PatientCourse will NOT be inserted
                if ( ! empty($befund_old_data)) {
        
                    $post['import'] = $befund_old_data[0]['import'];
                    $post['export'] = $befund_old_data[0]['export'];
        
                }
            }
            else {
                //we have permissions and cf is being edited
                //write changes in PatientCourse is something was changed
                if ( ! empty($befund_old_data)) {
        
                    if ($post['import'] != $befund_old_data[0]['import']
                        || $post['export'] != $befund_old_data[0]['export'] )
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
        
        
        
        
        $cust = new FormBlockHospizimex();
        $cust->ipid = $post['ipid'];
        $cust->contact_form_id = $post['contact_form_id'];
        $cust->import = $post['import'];
        $cust->export = $post['export'];
        
        if ($save_2_PC && in_array('hospiz_imex', $allowed_blocks)) {
        
           
            if (empty($post['import']) && empty($post['export'] ))
            {
                //must remove from PC this option
                //manualy remove and set $save_2_PC false
                $save_2_PC =  false;
                if ( ! empty($post['old_contact_form_id'])) {
                    $pc_entity = new PatientCourse();
                    $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockHospizimex');
                }
        
            } else {
        
                $course_title_lines =  array();
        

                if ( ! empty($post['import'])) {
                    $course_title_lines[] = 'Einfuhr: ' .$post['import'];
                }
                
                if ( ! empty($post['export'])) {
                    $course_title_lines[] = 'Ausfuhr: ' . $post['export'] ;
                }
                
                
                if ( ! empty($course_title_lines)
                    && ($pc_listener = $cust->getListener()->get('PostInsertWriteToPatientCourse')) )
                {

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