<?php

// require_once 'Pms/Triggers.php';

class application_Triggers_AddValuetoTodos extends Pms_Triggers{

	protected $_last_insert_ids = array(); //$this->_last_insert_ids[$course_id][] = $ins->id;

	public function get_last_insert_ids() 
	{
		return $this->_last_insert_ids;
	}
	
	
	/**
	 * 
	 * Jul 12, 2017 @claudiu 
	 * 
	 * this fn is piggybacked by:
	 * frmDoctorRecipeRequest - from a trigger set in dbf by trigger/listfields
	 * frmFormBlockTodos - direct call in frmFormBlockTodos
	 * frmTeamMeeting - direct call in Application_Form_TeamMeeting
	 * frm_mamboassessment - direct call in Application_Form_MamboAssessment
	 * 
	 * 
	 * @param Doctrine_Event $event
	 * @param array $inputs - this are from sysdat>>field_triggers>>inputs
	 * @param string $fieldname - from dbf or from private $triggerformname
	 * @param string $fieldid - from dbf or from private $triggerformid
	 * @param int $eventid - 1=insert 2=update?
	 * @param array $gpost - $_POST
	 */
	public function triggerAddValuetoTodos($event, $inputs, $fieldname, $fieldid, $eventid, $gpost)
	{
		
		$logininfo = $this->logininfo;
		$clientid = $logininfo->clientid;
		
		$usrs = new User();
		$post_users = $usrs->get_client_users($clientid);
		foreach($post_users as $userdata) {
			$usersdata[$userdata['id']] = $userdata;
			
			$user_details[$userdata['id']]['name'] = "";
			if(strlen($userdata['title']) > 0 ){
    			$user_details[$userdata['id']]['name'] = $userdata['title'].' '; 
			}
			$user_details[$userdata['id']]['name'] .= $userdata['last_name'].', '.$userdata['first_name'] ;
		}
		
		$groups = new Usergroup();
		$groupsdata = $groups->getClientGroups($clientid);
		
		foreach($groupsdata as $kg => $gr_details){
			$group_details[$gr_details['id']]['groupname'] = $gr_details['groupname'];
		}
		
		$user_pseudo_obj =  new UserPseudoGroup();
		$user_pseudo_arr =  $user_pseudo_obj->get_pseudogroups_for_todo($clientid);
		
		if(Modules::checkModulePrivileges("115", $clientid)) {
			$alien = isset($event->getinvoker()->alien) ? $event->getinvoker()->alien : 0;
			if($alien == 1) {
				$notfromsync = false; //this came from syncing, no change here
			} else {
				$notfromsync = true;
			}
		} else {
			$notfromsync = true;
		}
		
		if ($fieldname == "course_type" && !empty($gpost["course_title"]) && $notfromsync
				|| ($fieldname == "frmDoctorRecipeRequest" && !empty($gpost["phuser"]) && $notfromsync) 
				|| ($fieldname == "frm_mamboassessment" && !empty($gpost["todo_users"]) && $notfromsync) 
				|| ($fieldname == "frmFormBlockTodos" && !empty($gpost["todo_users"]) && $notfromsync) 
				|| ($fieldname == "frmTeamMeeting" && !empty($gpost["todo_users"]) && $notfromsync) 
		){

			//frmDoctorRecipeRequest has for now the course_type=Q
			//OLD WAY
			$course_type = $event->getinvoker()->course_type;
			$course_type = Pms_CommonData::aesDecrypt($course_type);
			
			if ($course_type == "W"
					|| ($fieldname == "frmDoctorRecipeRequest" && $course_type == "Q")
					|| ($fieldname == "frm_mamboassessment" && $course_type == "W")
					|| ($fieldname == "frmFormBlockTodos" && $course_type == "W")
					|| ($fieldname == "frmTeamMeeting" && $course_type == "W")
			) {
				
				$course_title = isset($event->getinvoker()->course_title) ? Pms_CommonData::aesDecrypt($event->getinvoker()->course_title) : ""; //this is geting the error when a unknown charater in course title
				
				$ipid = isset($event->getinvoker()->ipid) ? $event->getinvoker()->ipid : "";
				
				$course_id = $event->getinvoker()->id;

				$record_id = isset($event->getinvoker()->recordid) ? $event->getinvoker()->recordid : 0;
				
				switch($fieldname) 
				{
					case "frmDoctorRecipeRequest" :{
						//piggyback
						$td = array(
								2 => $event->getinvoker()->course_date,
								3 => 0
						);
						$text = $gpost['course_title'];
						$users = $gpost['phuser'];
						
					}break;
					
										
					case "frm_mamboassessment" :
					case "frmFormBlockTodos" :
					case "frmTeamMeeting" :{
						//piggyback
						$td = array(
								2 => $gpost['todo_date'],
								3 => 0
						);
						$text = $gpost['todo_text'];
						$users = $gpost['todo_users'];
					}break;
					
					default:{
						$td = explode("|---------|", $course_title);
						$text = htmlspecialchars($td[0]); //fix for foreign characters like %
						$users = explode(',', trim($td[1]));
					}break;
					
				}
				
				$users_array = array();
				$group_array = array();
				$pseudogroup_array = array();
				
				$additional_info = array();
				$additional_info_ids = array();
				
				$selectbox_separator_string = Pms_CommonData::get_users_selectbox_separator_string();
				
				if(in_array($selectbox_separator_string["all"], $users)){
				    $additional_info[] = "alle";
				}    

				
				foreach($users as $user)
				{
				    $additional_info_ids[] = $user;

				    
// 					if(substr($user,0,1) == 'g')
					if(strpos($user, $selectbox_separator_string["group"], 0) === 0)
					{
						$groupid = substr($user, strlen($selectbox_separator_string["group"]));
						$group_array[] = $groupid;
						$additional_info[] = $group_details[$groupid]['groupname'];
					}
// 					elseif(substr($user,0,1) == 'u')
					elseif(strpos($user, $selectbox_separator_string["user"], 0) === 0)
					{
					    $userid = substr($user, strlen($selectbox_separator_string["user"]));
					    $users_array[] = $userid;
					    $additional_info[] = $user_details[$userid]['name'];
					} 
// 					elseif(strpos($user, "pseudogroup_", 0) === 0) 
					elseif(strpos($user, $selectbox_separator_string["pseudogroup"], 0) === 0) 
					{
						$pseudoid = substr($user, strlen($selectbox_separator_string["pseudogroup"]));
						$pseudogroup_array[] = $pseudoid;
						$additional_info[] = $user_pseudo_arr[$pseudoid]['servicesname'];
						
					}
				}

				if(in_array($selectbox_separator_string["all"], $users)){ // send to all

					foreach($groupsdata as $keygrp=>$datagrp)
				    {
				        $date = trim(htmlspecialchars($td[2]));
				        	
				        $ins = new ToDos();
				        $ins->client_id = $clientid;
				        $ins->user_id = '0';
				        $ins->group_id = $datagrp['id'];
				        $ins->ipid = $ipid;
				        $ins->todo = $text;
				        $ins->create_date = date("Y-m-d H:i:s");
				        $ins->until_date = date("Y-m-d H:i:s", strtotime($date));
				        $ins->record_id = $record_id;
				        $ins->course_id = $course_id;
				        $ins->additional_info = implode(";",$additional_info_ids);
				        $ins->triggered_by = $fieldname;
				        $ins->save();
				        
				        $this->_last_insert_ids[$course_id][] = $ins->id;
				    }
				    
				    $users_array = array();
				    $group_array = array();
				    $pseudogroup_array = array();
				}
				
				
				
				$posted_groups = array();
				if(!empty($users_array))
				{
				    foreach($users_array as $k => $user_id)
				    {
				        $date = trim(htmlspecialchars($td[2]));
				        $group_switch = $td[3];
				        $user_group = $usersdata[$user_id]['groupid'];
				        
				        if(in_array($user_group,$group_array) && !in_array($user_group,$posted_groups))
				        {
				            $posted_groups[] = $user_group;
				            $groupid = $user_group;
				        }
				        else
				        {
				            $groupid = '0';
				        }
				        
				        $ins = new ToDos();
				        $ins->client_id = $clientid;
				        $ins->user_id = $user_id;
				        $ins->group_id = $groupid;
				        $ins->ipid = $ipid;
				        $ins->todo = $text;
				        $ins->create_date = date("Y-m-d H:i:s");
				        $ins->until_date = date("Y-m-d H:i:s", strtotime($date));
				        $ins->record_id = $record_id;
				        $ins->course_id = $course_id;
				        $ins->additional_info = implode(";",$additional_info_ids);
				        $ins->triggered_by = $fieldname;
				        $ins->save();
				        
				        $this->_last_insert_ids[$course_id][] = $ins->id;
				    }
				}
				
				if(!empty($group_array)){
				    
				    foreach($group_array as $k=>$group_id){

				        if(!in_array($group_id, $posted_groups)){
				            $posted_groups[] =  $group_id;
				            $date = trim(htmlspecialchars($td[2]));
				            $ins = new ToDos();
				            $ins->client_id = $clientid;
				            $ins->user_id = '0';
				            $ins->group_id = $group_id;
				            $ins->ipid = $ipid;
				            $ins->todo = $text;
				            $ins->create_date = date("Y-m-d H:i:s");
				            $ins->until_date = date("Y-m-d H:i:s", strtotime($date));
				            $ins->record_id = $record_id;
				            $ins->course_id = $course_id;
				            $ins->additional_info = implode(";",$additional_info_ids);
				            $ins->triggered_by = $fieldname;
				            $ins->save();
				            
				            $this->_last_insert_ids[$course_id][] = $ins->id;
				        }
				    }
				}
				
				
				if ( ! empty($pseudogroup_array)) {
					
					
					$pgu_obj = new PseudoGroupUsers();
					$users_in_pseudogroups = $pgu_obj->get_users_by_groups($pseudogroup_array);
					
					foreach($pseudogroup_array as $group) {
						
						foreach ($users_in_pseudogroups[$group] as $group_user) {

							if( ! in_array($group_user['user_id'], $users_array) && ! in_array($usersdata[$group_user['user_id']]['groupid'], $posted_groups))
							{
								
								$users_array[] =  $group_user['user_id'];
								$date = trim(htmlspecialchars($td[2]));
								
								$ins = new ToDos();
								$ins->client_id = $clientid;
								$ins->user_id = $group_user['user_id'];
								$ins->group_id = 0;//$usersdata[$group_user['user_id']]['groupid'];
								$ins->ipid = $ipid;
								$ins->todo = $text;
								$ins->create_date = date("Y-m-d H:i:s");
								$ins->until_date = date("Y-m-d H:i:s", strtotime($date));
								$ins->record_id = $record_id;
								$ins->course_id = $course_id;
								$ins->additional_info = implode(";",$additional_info_ids);
								$ins->triggered_by = $fieldname;
								$ins->save();
								
								$this->_last_insert_ids[$course_id][] = $ins->id;
							}
						}
					}

				}
				
				
				/* 
				$posted_groups = array();
				foreach($users as $user)
				{
					if(substr($user,0,1) == 'u')
					{
						$date = trim(htmlspecialchars($td[2]));
						$group_switch = $td[3];
						
		                $user_group = $usersdata[substr($user,1)]['groupid'];
						
						if(in_array($user_group,$group_array) && !in_array($user_group,$posted_groups))
						{
						    $posted_groups[] = $user_group;
							$groupid = $user_group;
						}
						else
						{
							$groupid = '0';
						}
		
		
						$ins = new ToDos();
						$ins->client_id = $clientid;
						$ins->user_id = substr($user,1);
						$ins->group_id = $groupid;
						$ins->ipid = $ipid;
						$ins->todo = $text;
						$ins->create_date = date("Y-m-d H:i:s");
						$ins->until_date = date("Y-m-d H:i:s", strtotime($date));
						$ins->course_id = $course_id;
						$ins->save();
					}
					elseif(substr($user,0,1) == 'g') 
					{
					    $posted_group_id = substr($user,1); 
					    if(!in_array($posted_group_id, $posted_groups)){
					        $posted_groups[] =  $posted_group_id; 
    						$date = trim(htmlspecialchars($td[2]));
    					
    						$ins = new ToDos();
    						$ins->client_id = $clientid;
    						$ins->user_id = '0';
    						$ins->group_id = substr($user,1);
    						$ins->ipid = $ipid;
    						$ins->todo = $text;
    						$ins->create_date = date("Y-m-d H:i:s");
    						$ins->until_date = date("Y-m-d H:i:s", strtotime($date));
    						$ins->course_id = $course_id;
    						$ins->save();
					    }
					}
					else 
					{					
						foreach($groupsdata as $keygrp=>$datagrp)
						{
							$date = trim(htmlspecialchars($td[2]));
							
							$ins = new ToDos();
							$ins->client_id = $clientid;
							$ins->user_id = '0';
							$ins->group_id = $datagrp['id'];
							$ins->ipid = $ipid;
							$ins->todo = $text;
							$ins->create_date = date("Y-m-d H:i:s");
							$ins->until_date = date("Y-m-d H:i:s", strtotime($date));
							$ins->course_id = $course_id;
							$ins->save();
						}
					}
				} */
			}
			
			
			
			
			
			if ($course_type == "WOLD")
			{
			    $course_title = Pms_CommonData::aesDecrypt($event->getinvoker()->course_title); //this is geting the error when a unknown charater in course title
			    $ipid = $event->getinvoker()->ipid;
			
			    $td = explode("|---------|", $course_title);
			    $text = htmlspecialchars($td[0]); //fix for foreign characters like %
			    $user = trim($td[1]);
			    $date = trim(htmlspecialchars($td[2]));
			    $group_switch = $td[3];
			
			    if ($group_switch == '1')
			    {
			        $groupid = Pms_CommonData::get_user_groupid($user);
			    }
			    else
			    {
			        $groupid = '0';
			    }
			
			
			    $ins = new ToDos();
			    $ins->client_id = $clientid;
			    $ins->user_id = $user;
			    $ins->group_id = $groupid;
			    $ins->ipid = $ipid;
			    $ins->todo = $text;
			    $ins->create_date = date("Y-m-d H:i:s");
			    $ins->until_date = date("Y-m-d H:i:s", strtotime($date));
			    $ins->save();
			    
			}
			
			
			
			
			
		}
	}
	

}
?>