<?php

// require_once("Pms/Form.php");
/**
 * @update Jan 25, 2018: @author claudiu, checked/modified for ISPC-2071
 * ventilation = Beatmung = FormBlockVentilation
 *
 * changed: bypass Trigger() on PC
 */
class Application_Form_FormBlockVentilation extends Pms_Form 
{

    protected $_translate_lang_array = FormBlockVentilation::LANGUAGE_ARRAY;
    
	public function clear_block_data($ipid, $contact_form_id)
	{
		$fdoc = Doctrine::getTable('FormBlockVentilation')->findOneByIpidAndContactFormId($ipid, $contact_form_id, $value);
		if ($fdoc) {
			$fdoc->delete();
			return true;
		} else {
			return true;
		}
	}

	
	public function InsertData($post, $allowed_blocks)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
		
		$lang_block_ventilation = $this->translate('lang_block_ventilation');
		
		$ventilation_items = array(
		    'modus',
		    'f_tot',
		    'vt',
		    'mv',
		    'peep',
		    'pip',
		    'o2_l_min',
		    'i_e',
		    'freetext'
		);
		
		$save_2_PC = false; //if we have insert or update on PatientCourse
		$change_date = '';
		$course_str = '';
		$done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':' . date('s', time())));
		 
		
		
		if ( ! empty($post['old_contact_form_id'])) {
		
		    $change_date = $post['contact_form_change_date'];
		
		    $FormBlockVentilation = new FormBlockVentilation();
		    $old_values = $FormBlockVentilation->get_by_ipid_and_formularid($post['ipid'], $post['old_contact_form_id'], true);
		    
		    if ( ! in_array('ventilation', $allowed_blocks)) {
		        // override post data if no permissions on block
		        // PatientCourse will NOT be inserted
		        if ( ! empty($old_values)) {
		
		            foreach($ventilation_items as $item){
		                $post['block_ventilation'][$item] = $old_values[$item];
		            }
		            
		        }
		    }
		    else {
		        //we have permissions and cf is being edited
		        //write changes in PatientCourse is something was changed
		        if ( ! empty($old_values)) {
		
		            foreach($ventilation_items as $item){
		                if ($post['block_ventilation'][$item] != $old_values[$item]) {
		                    //something was edited, we must insert into PC
		                    $save_2_PC = true;
		                    break;
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
		
		
		
		
		$empty_record = true;//this block is empty
		foreach($ventilation_items as $item){
		    if (strlen($post['block_ventilation'][$item]) > 0) {
		        $empty_record  = false;
		        break;
		    }
		}
		
		

	    $fbv_obj = new FormBlockVentilation();
	    
	    $fbv_obj->ipid = $post['ipid'];
	    $fbv_obj->contact_form_id = $post['contact_form_id'];
	    
	    $fbv_obj->modus = $post['block_ventilation']['modus'];
	    $fbv_obj->f_tot = $post['block_ventilation']['f_tot'];
	    $fbv_obj->vt = $post['block_ventilation']['vt'];
	    $fbv_obj->mv = $post['block_ventilation']['mv'];
	    $fbv_obj->peep = $post['block_ventilation']['peep'];
	    $fbv_obj->pip = $post['block_ventilation']['pip'];
	    $fbv_obj->o2_l_min = $post['block_ventilation']['o2_l_min'];
	    $fbv_obj->i_e = $post['block_ventilation']['i_e'];
	    $fbv_obj->freetext = $post['block_ventilation']['freetext'];
	    $fbv_obj->isdelete = 0;
		    
		    
	    if ($save_2_PC && in_array('ventilation', $allowed_blocks)) {
	    
	        
	        if ($empty_record) {
	            //must remove from PC this option
	            //manualy remove and set $save_2_PC false
	            $save_2_PC =  false;
	            if ( ! empty($post['old_contact_form_id'])) {
	                $pc_entity = new PatientCourse();
	                $pc_entity->setIsRemovedByIpidAndContactFormAndTabname($post['ipid'], $post['old_contact_form_id'], 'FormBlockVentilation');
	            }
	    
	        } else {
	    
	            $course_title_line =  array();
	            foreach($ventilation_items as $item){
	                if ( strlen($post['block_ventilation'][$item])>0) {
	                    $course_title_line[] =  $this->translate($item) . ": ". $post['block_ventilation'][$item];
	                }
	            }
	            
	            if ( ! empty($course_title_line)
	                && ($pc_listener = $fbv_obj->getListener()->get('PostInsertWriteToPatientCourse')) )
	            {
	                //                     $course_str = "Fahrtzeit / Dokumentationszeit: \n". implode("\n", $course_title_lines);
	                $course_str =  "Beatmung: " . implode(", ", $course_title_line);
	                $change_date = "";//removed from pc; ISPC-2071
	                
	                $pc_listener->setOption('disabled', false);
	                $pc_listener->setOption('course_title', $course_str . $change_date);
	                $pc_listener->setOption('done_date', $done_date);
	                $pc_listener->setOption('user_id', $userid);
	                 
	            }
	    
	        }
	    }
		    
	    $fbv_obj->save();
	    $ventilation_entry_id = $fbv_obj->id;
		
		
// 		$allow_edit_course = array();
// 		if(!empty($post['old_contact_form_id']) && strlen($post['old_contact_form_id']) > 0) {
			
// 			$change_date = $post['contact_form_change_date'];
			
// 			$old_values = $FormBlockVentilation->get_by_ipid_and_formularid($post['ipid'], $post['old_contact_form_id']);
			
// 			foreach($ventilation_items as $k=>$item){
// 				if(strlen($post['block_ventilation'][$item])>0 && $post['block_ventilation'][$item] != $old_values[$item]){
// 					$allow_edit_course[]= "1";
// 				}
// 			}
			
// 			$clear_block_entryes = $this->clear_block_data($post['ipid'], $post['old_contact_form_id']);
			
// 		} else {
// 			$allow_edit_course[] = "1";
// 		} 
		
		
// 		foreach($ventilation_items as $k=>$item){
// 			if(strlen($post['block_ventilation'][$item])>0){
// 				$tocourse[$item] =  $lang_block_ventilation[$item].": ". $post['block_ventilation'][$item];
// 			}
// 		}
		
// 		if(!empty($tocourse) )
// 		{
// 			$coursecomment = " Beatmung: ". implode(', ',$tocourse);
// 		}
		
// 		$fbv_obj = new FormBlockVentilation();
		
// 		$fbv_obj->ipid = $post['ipid'];
// 		$fbv_obj->contact_form_id = $post['contact_form_id'];
		
// 		$fbv_obj->modus = $post['block_ventilation']['modus'];
// 		$fbv_obj->f_tot = $post['block_ventilation']['f_tot'];
// 		$fbv_obj->vt = $post['block_ventilation']['vt'];
// 		$fbv_obj->mv = $post['block_ventilation']['mv'];
// 		$fbv_obj->peep = $post['block_ventilation']['peep'];
// 		$fbv_obj->pip = $post['block_ventilation']['pip'];
// 		$fbv_obj->o2_l_min = $post['block_ventilation']['o2_l_min'];
// 		$fbv_obj->i_e = $post['block_ventilation']['i_e'];
// 		$fbv_obj->freetext = $post['block_ventilation']['freetext'];
		
// 		$fbv_obj->isdelete = 0;
		

// 		$fbv_obj->save();

// 		$ventilation_entry_id =$fbv_obj->id; 
		
// 		if(!empty($coursecomment) && !empty($allow_edit_course))
// 		{
// 			$cust = new PatientCourse();
// 			$cust->ipid = $post['ipid'];
// 			$cust->course_date = date("Y-m-d H:i:s", time());
// 			$cust->course_type = Pms_CommonData::aesEncrypt('K');
// 			$cust->course_title = Pms_CommonData::aesEncrypt($coursecomment.$change_date);
// 			$cust->isserialized = 1;
// 			$cust->user_id = $userid;
// 			$cust->done_date = $done_date;
// 			$cust->done_name = Pms_CommonData::aesEncrypt("contact_form");
// 			$cust->done_id = $post['contact_form_id'];
			
// 			// ISPC-2071 - added tabname, this entry must be grouped/sorted
// 			$cust->tabname = Pms_CommonData::aesEncrypt("FormBlockVentilation");
			
// 			$cust->save();
// 		}
		
		
		
		return $ventilation_entry_id ;
	}

}

?>