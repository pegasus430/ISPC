<?php

require_once("Pms/Form.php");
class Application_Form_PatientXbdtActions extends Pms_Form{
	public function validate($post)
	{
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    $Tr = new Zend_View_Helper_Translate();
	    
	    $error = 0;
	    $val = new Pms_Validation();
	     
	    if(!empty($post['item']))
	    {
	        foreach($post['item'] as $item_row =>$item)
	        {

	            if(empty($item['master_action_id'])){
	                $this->error_message["actions"] = $Tr->translate('no custom actions are allowed');
	                $error = 1;
	            }
	            
	            if(empty($item['date'])){
	                $this->error_message["date"] = $Tr->translate('date is mandatory');
	                $error = 2;
	            }
	            
	            if(!empty($item['date'])){
	                if(strlen($item['date'])){
	                    $post_date_array = explode(".",$item['date']);
	                    $day = $post_date_array[0];
	                    $month = $post_date_array[1];
	                    $year = $post_date_array[2];
	                }
	                
	                if(checkdate($month,$day,$year) === false)
	                {
	                    $this->error_message['date'] = $Tr->translate('date_error_invalid');
	                    $error = 3;
	                }
	               
	                if(checkdate($month,$day,$year) && strtotime($item['date']) > strtotime(date("d.m.Y", time())) )
	                {
	                    $this->error_message['date'] = $Tr->translate('err_datefuture');
	                    $error = 4;
	                }
	                if(preg_match("/(2[0-3]|[01][0-9]):([0-5][0-9])/", $item['time'])){
	                    $time_arr = explode(":",$item['time']);
	                    $full_date = mktime ( $time_arr[0], $time_arr[1],"0",  date("n",strtotime($item['date'])),date("j",strtotime($item['date'])), date("Y",strtotime($item['date'])) );
	                    if(checkdate($month,$day,$year) && $full_date > strtotime(date("d.m.Y H:i", time())) )
	                    {
	                        $this->error_message['date'] = $Tr->translate('err_datefuture');
	                        $error = 5;
	                    }
	                }
	                
	                if(empty($item['action_id']) || empty($item['action_name'])){
	                    $this->error_message["actions"] = $Tr->translate('action details are mandatory');
	                    $error = 6;
	                }
	            }
	        }
	    }

	    
	    if($error == 0)
	    {
	        return true;
	    }
	    else
	    {
	        return false;
	    }
	}

  
    public function update_multiple_data($post){
        $logininfo = new Zend_Session_Namespace('Login_Info');
        $clientid = $logininfo->clientid;
        $userid = $logininfo->userid;
        $decid = Pms_Uuid::decrypt($_GET['id']);
        $ipid = Pms_CommonData::getIpid($decid);
    
        // user details
        $users = new User();
        $userarray = $users->getUserByClientid($clientid);
        $users_array["-1"] = "Team";
        foreach ($userarray as $user)
        {
            $users_array[$user['id']] = trim($user['user_title'])." ".trim($user['last_name']).", ".trim($user['first_name']);
        }
        // client action details
        
        $actions_data = XbdtActions::client_xbdt_actions($clientid);
        foreach($actions_data as $k=>$ac){
            $action_details[$ac['id']] = $ac;
        }
        
        if(!empty($post['item']))
        {
            foreach($post['item'] as $item_row =>$item)
            {
                if(isset($item['db_row_id']) && !empty($item['db_row_id']) && $item['db_row_id'] != "undefined"){
                    $existing = Doctrine::getTable('PatientXbdtActions')->find($item['db_row_id']);
                    
                    if($existing){

                        if($item['deleted'] == "1") // DELETE EXISTING
                        {
                            $existing->isdelete = "1";
                            $existing->edited_from = "list";
                            $existing->save();
                            
                            // add in verlauf
                            $attach = "Eine Leistung wurde gelöscht. \n ".$item['action_id'].' | '.$item['action_name'].' | '.$item['date'].' '.$item['time'];
                            $insert_pc = new PatientCourse();
                            $insert_pc->ipid = $ipid;
                            $insert_pc->course_date = date("Y-m-d H:i:s", time());
                            $insert_pc->course_type = Pms_CommonData::aesEncrypt('LE');
                            $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_xbdt_actions");
                            $insert_pc->recordid = $item['db_row_id'];
                            $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                            $insert_pc->user_id = $userid;
                            $insert_pc->save();
                            
                        }
                        elseif($item['edited'] == "1") // EDIT EXISTING
                        {
                            //  get existing data and form a string
                            $existing_string = "";
                            $existing_string .= $existing->action; // master action id 
                            $existing_string .= ' '.$existing->userid;
                            $existing_string .= ' '.date('d.m.Y',strtotime($existing->action_date));
                            $existing_string .= ' '.date('H:i',strtotime($existing->action_date));
                            $existing_string .= ' '.$existing->file_id;
                            
                            // Get posted data and form a string
                            $post_string = "";
                            $post_string .= trim($item['master_action_id']); // master action id 
                            $post_string .= ' '.trim($item['action_user']);
                            $post_string .= ' '.trim($item['date']);
                            $post_string .= ' '.trim($item['time']);
                            $post_string .= ' '.trim($item['action_billed']);

                            if($existing_string != $post_string){
                                // UPDATE 
                                
                                $old_entry = "";
                                $master_action_id =  $existing->action;
                                $old_entry .= $action_details[$master_action_id]['action_id'];
                                $old_entry .= ' | '.$action_details[$master_action_id]['name'];
                                $old_entry .= ' | '.$users_array[$existing->userid];
                                $old_entry .= ' | '.date("d.m.Y H:i",strtotime($existing->action_date));
                                
                                
                                
                                
                                $existing->userid = $item['action_user'];
                                if($item['action_user'] == "-1"){
                                    $existing->team = "1";
                                } else{
                                    $existing->team = "0";
                                }
                                
                                if(!empty($item['action_billed'])){
                                	$existing->file_id = $item['action_billed'];
                                } else {
                                	$existing->file_id = "0";
                                }
                                $existing->edited_from = "list";
                                $existing->action = $item['master_action_id'];
                                $existing->action_date = date("Y-m-d H:i:s",strtotime($item['date'].' '.$item['time'].':00'));
                                $existing->save();
                                
                                
                                // add in verlauf
                                $new_entry ="" ;
                                $new_entry .= $item['action_id'] ;
                                $new_entry .= ' | '.$item['action_name'] ;
                                $new_entry .= ' | '.$users_array[$item['action_user']];
                                $new_entry .= ' | '.$item['date'].' '.$item['time'] ;
                                
                                $attach = "Eine Leistung wurde bearbeitet. \n " . $old_entry . '  -> ' .  $new_entry.'';
                                $insert_pc = new PatientCourse();
                                $insert_pc->ipid = $ipid;
                                $insert_pc->course_date = date("Y-m-d H:i:s", time());
                                $insert_pc->course_type = Pms_CommonData::aesEncrypt("LE");
                                $insert_pc->tabname = Pms_CommonData::aesEncrypt("patient_xbdt_actions");
                                $insert_pc->recordid = $item['db_row_id'];
                                $insert_pc->course_title = Pms_CommonData::aesEncrypt(addslashes($attach));
                                $insert_pc->user_id = $userid;
                                $insert_pc->save();
                            }
                        }
                    }
                }
                else // ADD NEW
                {
                    if($item['new'] == "1"){

                        $done_date = date("Y-m-d H:i:s",strtotime($item['date'].' '.$item['time'].':00'));
                        $xbdtact_insert = new PatientXbdtActions();
                        $xbdtact_insert->clientid = $clientid;
                        $xbdtact_insert->userid = $item['action_user'];
                        
                        if($item['action_user'] == "-1") {
                            $xbdtact_insert->team = "1";
                        } else {
                            $xbdtact_insert->team = "0";
                        }
                        
                        if(!empty($item['action_billed'])){
                        	$xbdtact_insert->file_id = $item['action_billed'];
                        } else{
                        	$xbdtact_insert->file_id = "0";
                        }
                        
                        $xbdtact_insert->ipid = $ipid;
                        $xbdtact_insert->action = $item['master_action_id'];
                        $xbdtact_insert->action_date = $done_date;
                        $xbdtact_insert->edited_from = "list";
                        $xbdtact_insert->save();
                    
                        $xbdtaction_id = $xbdtact_insert->id;
                        
                        
                        // insert in patient course
                        
                        $comment = '';
                        $course_comment = $item['master_action_id'].' |____| '.$item['action_id'].' |____| '.$item['action_name'].' |____| '.$item['action_user'].' |____| '.$item['date'].' '.$item['time'] ;
                        
                        $cust = new PatientCourse();
                        $cust->ipid = $ipid;
                        $cust->course_date = date("Y-m-d H:i:s", time());
                        $cust->course_type = Pms_CommonData::aesEncrypt("LE");
                        $cust->course_title = Pms_CommonData::aesEncrypt(addslashes($course_comment));
                        $cust->user_id = $userid;
                        if($xbdtaction_id){
                            $cust->recordid = $xbdtaction_id;
                        }
                        $cust->done_date = $done_date; // action date
                        $cust->done_name = "le_verlauf";
                        $cust->save();
                        
                        $insid = $cust->id;
                        
                        
                        if ($xbdtaction_id)
                        {
                            $update_xbdtaction = Doctrine::getTable('PatientXbdtActions')->find($xbdtaction_id);
                            $update_xbdtaction->course_id = $insid;
                            $update_xbdtaction->save();
                        }
                    }
                }
            }
        }
        
    }  
}
?>
