<?php

require_once("Pms/Form.php");

class Application_Form_FormBlockXbdtEbmii extends Pms_Form
{
	public function clear_block_data($ipid, $contact_form_id )
	{
		if (!empty($contact_form_id))
		{
			$Q = Doctrine_Query::create()
			->update('FormBlockXbdtEbmii')
			->set('isdelete','1')
			->where("contact_form_id='" . $contact_form_id. "'")
			->andWhere('ipid LIKE "' . $ipid . '"');
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
		$ebmii_block = new FormBlockXbdtEbmii();
		
		$modules = new Modules();
		$modulepriv_le = $modules->checkModulePrivileges("128", $clientid);
		
		// user details
		$users = new User();
		$userarray = $users->getUserByClientid($clientid);
		$users_array["-1"] = "Team";
		foreach ($userarray as $user)
		{
		    $users_array[$user['id']] = trim($user['user_title'])." ".trim($user['last_name']).", ".trim($user['first_name']);
		}
		
		$xam = new XbdtActions();
		$xa_array = $xam->client_xbdt_actions($clientid);
		
		foreach($xa_array as $k=>$xadet){
		    $xa_details[$xadet['id']] = $xadet; 
		}

        $done_date = date('Y-m-d H:i:s', strtotime($post['date'] . ' ' . $post['begin_date_h'] . ':' . $post['begin_date_m'] . ':00'));
        $ipid = $post['ipid'];
        
        // clear old entries 
        $clear_block_entryes = $this->clear_block_data( $post['ipid'], $post['old_contact_form_id']);

        $fdoc1 = Doctrine_Query::create();
        $fdoc1->select('*');
        $fdoc1->from('XbdtActions');
        $fdoc1->where("isdelete = 0  ");
        $fdoc1->andWhere("clientid = ".$clientid);
        $fdoc1->andWhere("block_option_id != 0");
        $existing_ebmii = $fdoc1->fetchArray();
        
        $used_ebmii = array();
        foreach($existing_ebmii as $k=>$data){
            $used_ebmii[] = $data['block_option_id'];
        }
        
        
		if($post['old_contact_form_id'] == "0"){ // NEW  FORM
    		    
    		foreach($xa_array as $key => $action_values){
    
    			if($post['egblock']['ebmii'][$action_values['id']]['value'] > 0){
    				$value = 1;
    				// insert in patient
    				
    				// check if $action_values['id'] in system xbdt actions
    				
    				if($post['egblock']['ebmii'][$action_values['id']]['source'] == "xbdt"){
    				    
        				$xbdtact_insert = new PatientXbdtActions();
        				$xbdtact_insert->clientid = $clientid;
        				$xbdtact_insert->userid = $userid;
        				$xbdtact_insert->team = "0";
        				$xbdtact_insert->ipid = $ipid;
        				$xbdtact_insert->action = $action_values['id'];
        				$xbdtact_insert->action_date = $done_date;;
        				$xbdtact_insert->edited_from = "contactform";;
        				$xbdtact_insert->save();
        				$xbdtaction_id = $xbdtact_insert->id;
        				 
        				// insert in patient course
        				$course_comment ="";
        				$course_comment = $action_values['action_id'].' |____| '.$action_values['action_id'].' |____| '.$action_values['name'].' |____| '.$userid.' |____| '.date('d.m.Y H:i',strtotime($done_date)) ;
        				$cust = new PatientCourse();
        				$cust->ipid = $ipid;
        				$cust->course_date = date("Y-m-d H:i:s", time());
        				$cust->course_type = Pms_CommonData::aesEncrypt("LE");
        				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($course_comment));
        				$cust->user_id = $userid;
        				if($modulepriv_le && $xbdtaction_id){
        				    $cust->recordid = $xbdtaction_id;
        				}
        				$cust->done_date = $done_date;
        				$cust->done_name = "le_verlauf";
        				$cust->save();
        				$insid = $cust->id;
        				
        				// update pateint course
        				if ($xbdtaction_id && $modulepriv_le)
        				{
        				    $update_xbdtaction = Doctrine::getTable('PatientXbdtActions')->find($xbdtaction_id);
        				    $update_xbdtaction->course_id = $insid;
        				    $update_xbdtaction->save();
        				}
        
        				// insert in  FormBlockXbdtEbmii
        				$fbxg_insert = new FormBlockXbdtEbmii();
        				$fbxg_insert->ipid =  $post['ipid'];
        				$fbxg_insert->contact_form_id = $post['contact_form_id'];
        				$fbxg_insert->action_id = $action_values['id'];
        				$fbxg_insert->patient_xbdt_action_id = $xbdtaction_id;
        				$fbxg_insert->action_value = "1";
        				$fbxg_insert->save();
    				}
    			} else {
    				
    				// insert in  FormBlockXbdtEbmii
    				$fbxg_insert = new FormBlockXbdtEbmii();
    				$fbxg_insert->ipid =  $post['ipid'];
    				$fbxg_insert->contact_form_id = $post['contact_form_id'];
    				$fbxg_insert->action_id = $action_values['id'];
    				$fbxg_insert->patient_xbdt_action_id = "0";
    				$fbxg_insert->action_value = "0";
    				$fbxg_insert->save();
    			}
    		}
		}
		else
		{
		    $ebmii_old_data = $ebmii_block->getPatientFormBlockXbdtEbmii($post['ipid'], $post['old_contact_form_id'], true);
		    
		    if ($ebmii_old_data )
		    {
		        // overide post data if no permissions on goa block
		        if (!in_array('ebmii', $allowed_blocks))
		        {
		            $records = array();
		            foreach ($xa_array as $ke => $action_values)
		            {
		                if ($ebmii_old_data [$action_values['id']] > 0)
		                {
		                    $value = 1;
		                }
		                else
		                {
		        
		                    $value = 0;
		                }
		                $old_records[] = array(
		                    "ipid" => $post['ipid'],
		                    "contact_form_id" => $post['contact_form_id'],
		                    "action_id" => $action_values['id'],
		                    "action_value" => $value,
		                    "patient_xbdt_action_id" => $action_values['patient_xbdt_action_id']
		                );
		            }
		            	
		            // insert with the old data
		            $collection = new Doctrine_Collection('FormBlockXbdtEbmii');
		            $collection->fromArray($old_records);
		            $collection->save();
		            	
		            // Do nothisng to Patient XBDT  ACTIONS
		        } 
		        else 
		        {
		          // get all xbdt actions from form

		           $pxam = new PatientXbdtActions();
		           $saved_pxa = $pxam->get_patient_actions($ipid,"contactform");
		           
		           $saved_patient_xbdt_actions[] = "999999999";
		           foreach($saved_pxa as $k=>$spxa){
		               $saved_patient_xbdt_actions[] = $spxa['id']; 
		           }
		            
		            foreach($post['egblock']['ebmii'] as $act_id=>$act_details){
		                
		                if($act_details['source'] == "xbdt"){
		                    
    		                if($act_details['patient_xbdt_action_id'] != 0 && (in_array($act_details['patient_xbdt_action_id'],$saved_patient_xbdt_actions) && $act_details['value'] == "1")){
    		                    // update pateint xbdt action
    		                    
    		                    // date time 
    	                        $existing = Doctrine::getTable('PatientXbdtActions')->find($act_details['patient_xbdt_action_id']);
                                if($existing){
    		                          if($existing->action_date != $done_date){
    		                              // update
    		                                  $old_entry = "";
            		                        $master_action_id =  $existing->action;
            		                        $old_entry .= $xa_details[$master_action_id]['action_id'];
            		                        $old_entry .= ' | '.$xa_details[$master_action_id]['name'];
            		                        $old_entry .= ' | '.$users_array[$existing->userid];
            		                        $old_entry .= ' | '.date("d.m.Y H:i",strtotime($existing->action_date));
            		                        $existing->action_date = $done_date;
            		                        $existing->save();
            		                        
            		                        
            		                        // add in verlauf
            		                        $new_entry ="" ;
            		                        $new_entry .= $xa_details[$existing->action]['action_id'] ;
            		                        $new_entry .= ' | '. $xa_details[$existing->action]['name'] ;
            		                        $new_entry .= ' | '.$users_array[$userid];
            		                        $new_entry .= ' | '.date("d.m.Y H:i",strtotime($done_date)) ;
            		                        
            		                        $attach = "Eine Leistung wurde bearbeitet. \n " . $old_entry . '  -> ' .  $new_entry.'';
            		                        $insert_pc = new PatientCourse();
            		                        $insert_pc->ipid = $ipid;
            		                        $insert_pc->course_date = date("Y-m-d H:i:s", time());
            		                        $insert_pc->course_type = Pms_CommonData::aesEncrypt("LE");
            		                        $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_xbdt_actions");
            		                        $insert_pc->recordid = $existing->id;
            		                        $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
            		                        $insert_pc->user_id = $userid;
            		                        $insert_pc->save();
    		                           }		                      
    		                      }
    		                      
    		                      // insert in  FormBlockXbdtEbmii
    		                      $fbxg_insert = new FormBlockXbdtEbmii();
    		                      $fbxg_insert->ipid =  $post['ipid'];
    		                      $fbxg_insert->contact_form_id = $post['contact_form_id'];
    		                      $fbxg_insert->action_id = $act_id;
    		                      $fbxg_insert->patient_xbdt_action_id = $act_details['patient_xbdt_action_id'];
    		                      $fbxg_insert->action_value = "1";
    		                      $fbxg_insert->save();
    		                      
    		                }
                            elseif($act_details['patient_xbdt_action_id'] != 0 && (in_array($act_details['patient_xbdt_action_id'],$saved_patient_xbdt_actions) && !isset($act_details['value'])))
                            {
                                // update pateint xbdt action - mark as deleted.   
                                $existing_del = Doctrine::getTable('PatientXbdtActions')->find($act_details['patient_xbdt_action_id']);
                                if($existing_del){
                                    $delete_act = $existing_del->action;
                                    $existing_del->isdelete = "1";
                                    $existing_del->save();
                                    
                                    // add in verlauf
                                    $attach = "Eine Leistung wurde gelöscht. \n ".$xa_details[$delete_act]['action_id'].' | '.$xa_details[$delete_act]['name'].' | '.date("d.m.Y H:i",strtotime($existing_del->action_date));
                                    $insert_pc = new PatientCourse();
                                    $insert_pc->ipid = $ipid;
                                    $insert_pc->course_date = date("Y-m-d H:i:s", time());//???
                                    $insert_pc->course_type = Pms_CommonData::aesEncrypt('LE');
                                    $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_xbdt_actions");
                                    $insert_pc->recordid = $act_details['patient_xbdt_action_id'];
                                    $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                                    $insert_pc->user_id = $userid;
                                    $insert_pc->save();
                                }
                                
                                $fbxg_insert = new FormBlockXbdtEbmii();
                                $fbxg_insert->ipid =  $post['ipid'];
                                $fbxg_insert->contact_form_id = $post['contact_form_id'];
                                $fbxg_insert->action_id = $act_id;
                                $fbxg_insert->patient_xbdt_action_id = $act_details['patient_xbdt_action_id'];
                                $fbxg_insert->action_value = "0";
                                $fbxg_insert->save();
                                
                            }
                            elseif($act_details['patient_xbdt_action_id'] == 0 && $act_details['value'] == "1")
                            {
                            	                
                                // insert new action in patient XBDT
                                // insert in patient
                                $xbdtact_insert = new PatientXbdtActions();
                                $xbdtact_insert->clientid = $clientid;
                                $xbdtact_insert->userid = $userid;
                                $xbdtact_insert->team = "0";
                                $xbdtact_insert->ipid = $ipid;
                                $xbdtact_insert->action = $act_id;
                                $xbdtact_insert->action_date = $done_date;
                                $xbdtact_insert->edited_from = "contactform";;
                                $xbdtact_insert->save();
                                $xbdtaction_id = $xbdtact_insert->id;
                                	
                                // insert in patient course
                                $course_comment ="";
                                $course_comment = $xa_details[$act_id]['action_id'].' |____| '.$xa_details[$act_id]['action_id'].' |____| '.$xa_details[$act_id]['name'].' |____| '.$userid.' |____| '.date('d.m.Y H:i',strtotime($done_date)) ;
                                $cust = new PatientCourse();
                                $cust->ipid = $ipid;
                                $cust->course_date = date("Y-m-d H:i:s", time());
                                $cust->course_type = Pms_CommonData::aesEncrypt("LE");
                                $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($course_comment));
                                $cust->user_id = $userid;
                                if($modulepriv_le && $xbdtaction_id){
                                    $cust->recordid = $xbdtaction_id;
                                }
                                $cust->done_date = $done_date;
                                $cust->done_name = "le_verlauf";
                                $cust->save();
                                $insid = $cust->id;
                                
                                // update pateint course
                                if ($xbdtaction_id && $modulepriv_le)
                                {
                                    $update_xbdtaction = Doctrine::getTable('PatientXbdtActions')->find($xbdtaction_id);
                                    $update_xbdtaction->course_id = $insid;
                                    $update_xbdtaction->save();
                                }
                                
                                // insert in  FormBlockXbdtEbmii
                                $fbxg_insert = new FormBlockXbdtEbmii();
                                $fbxg_insert->ipid =  $post['ipid'];
                                $fbxg_insert->contact_form_id = $post['contact_form_id'];
                                $fbxg_insert->action_id = $act_id;
                                $fbxg_insert->patient_xbdt_action_id = $xbdtaction_id;
                                $fbxg_insert->action_value = "1";
                                $fbxg_insert->save();
                            } 
                            else
                            {
                                $fbxg_insert = new FormBlockXbdtEbmii();
                                $fbxg_insert->ipid =  $post['ipid'];
                                $fbxg_insert->contact_form_id = $post['contact_form_id'];
                                $fbxg_insert->action_id = $act_id;
                                $fbxg_insert->patient_xbdt_action_id = 0;
                                $fbxg_insert->action_value = "0";
                                $fbxg_insert->save();
                            }
    		            }
		            }
		        }
		    }
		}
	}
}

?>