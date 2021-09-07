<?php

require_once("Pms/Form.php");

class Application_Form_VoluntaryworkersCourse extends Pms_Form
{
	public function validate($post) {
	    $logininfo = new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();

		$error = 0;
		$val = new Pms_Validation();

		
		$post_date_array = array();
		$day = "";
		$month = "";
		$year = "";
		
		print_r($post); exit;
    	$special_date_shortcuts = array('XT');
       		
		
		foreach($post['vw_course_type'] as $k_course_type => $course_type)
		{
		    
			
			
			if( in_array(strtoupper($course_type),$special_date_shortcuts))
			{
		        $full_date_string[$k_course_type] = trim($course_title_arr[$k_course_type][2]); 
		        $full_date_array[$k_course_type] = explode(" ",trim($course_title_arr[$k_course_type][2]));
		        
		        $date[$k_course_type] = $full_date_array[$k_course_type][0]; 
		        $time[$k_course_type] = $full_date_array[$k_course_type][1];
		         

 
			    if(empty($date[$k_course_type]))
			    {
			        $this->error_message['date'] = $Tr->translate('date_is_mandatory');
			        $error = 2;
			    }
			    
			    if(strlen($date[$k_course_type])){
			        $post_date_array = explode(".",$date[$k_course_type]);
			        $day = $post_date_array[0];
			        $month = $post_date_array[1];
			        $year = $post_date_array[2];
			    }
			    	
			    if(checkdate($month,$day,$year) === false)
			    {
			        $this->error_message['date'] = $Tr->translate('date_error_invalid');
			        $error = 3;
			    }

			    if(date('Y', strtotime($date[$k_course_type])) < '2008')
			    {
			        $this->error_message['date'] = $Tr->translate('date_error_before_2008');
			        $error = 7;
			    }

			    if(strtoupper($course_type) == 'XT')
			    {
    			    if(checkdate($month,$day,$year) && strtotime($date[$k_course_type]) > strtotime(date("d.m.Y", time())))
    			    {
    			        $this->error_message['date'] = $Tr->translate('err_datefuture');
    			        $error = 4;
    			    }
    			    
    			    if(preg_match("/(2[0-3]|[01][0-9]):([0-5][0-9])/", $time[$k_course_type])){
    			        $time_arr = explode(":",$time[$k_course_type]);
    			        $full_date = mktime ( $time_arr[0], $time_arr[1],"0",  date("n",strtotime($date[$k_course_type])),date("j",strtotime($date[$k_course_type])), date("Y",strtotime($date[$k_course_type])) );
    			        if(checkdate($month,$day,$year) && $full_date > strtotime(date("d.m.Y H:i", time())) )
    			        {
    			            $this->error_message['date'] = $Tr->translate('err_datefuture');
    			            $error = 5;
    			        }
    			    }
    			    
			    }
			    
			    if(!preg_match("/(2[0-3]|[01][0-9]):([0-5][0-9])/", $time[$k_course_type])){
			        $this->error_message['date'] = $Tr->translate('time_error_invalid');
			        $error = 6;
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

	public function InsertData($post,$vw_id)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$Tr = new Zend_View_Helper_Translate();
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;
        //ISPC-2908,Elena,21.05.2021
		$special_shortcuts = array('XT', 'W');
		$tab_names = array("XT" => "phone_verlauf", "W" => "TODO");
		
		
		for ($i = 0; $i < sizeof($post['vw_course_type']); $i++)
		{
			if (strlen($post['vw_course_type'][$i]) > 0)
			{
				if (in_array($post['vw_course_type'][$i], $special_shortcuts))
				{
 
					$course_title_arr = explode(' | ', $post['vw_course_title'][$i]);

					if (count($course_title_arr) > 0)
					{
						if(in_array($post['vw_course_type'][$i], $special_shortcuts)) //if shortcut is a special one... get inserted date and time
						{
						    if(date('Y', strtotime($course_title_arr[count($course_title_arr) - 1])) != "1970"){
    							$done_date = date('Y-m-d H:i:s', strtotime($course_title_arr[count($course_title_arr) - 1]));
						    } else{
						        $done_date = date('Y-m-d H:i:s', time());
						    } 
						}
						else
						{
							$done_date = date('Y-m-d', strtotime($course_title_arr[count($course_title_arr) - 1]));
							$done_date = $done_date . ' ' . date('H:i:s', time());
						}
					}
					else
					{
						$done_date = date('Y-m-d H:i:s', time());
					}
				}
				
				// !!!!!!!!!!!!!!!!!!!!!!
                // INSERT IN PATIENT COURSE 
				// !!!!!!!!!!!!!!!!!!!!!!
				
				$cust = new VoluntaryworkersCourse();
				$cust->vw_id = $vw_id;
				$cust->course_date = date("Y-m-d H:i:s", time());
				$cust->course_type = $post['vw_course_type'][$i];
				$cust->course_title = $post['vw_course_title'][$i];
// 				$cust->course_type = Pms_CommonData::aesEncrypt($post['vw_course_type'][$i]);
// 				$cust->course_title = Pms_CommonData::aesEncrypt(addslashes($post['vw_course_title'][$i]));
				$cust->user_id = $userid;
				$cust->done_date = $done_date;
				$cust->done_name = $tab_names[$post['vw_course_type'][$i]];
				$cust->save();
                //ISPC-2908,Elena,21.05.2021
                //if it is TODO, insert in todos table
				if($post['vw_course_type'][$i] == 'W'){
				    $todo_text = $course_title_arr[0] ;
				    $todo_text_arr = ['todo_text' => $todo_text,
                        'vw_id' => $vw_id
                        ];
 				    $todo_text_for_table  = json_encode($todo_text_arr);

				    $a_user = json_decode(trim($course_title_arr[1]), true);
				    foreach($a_user as $usershortcut){
				        $kind_of = substr($usershortcut, 0, 1);
				        $user_todo_id = 0;
				        $group_todo_id = 0;
				        //user or group?
				        if($kind_of == 'u'){
				            $user_todo_id = intval(substr($usershortcut, 1));
                        }elseif($kind_of == 'g'){
                            $group_todo_id = intval(substr($usershortcut, 1));
                        }

                        $ins = new ToDos();
                        $ins->client_id = $clientid;
                        $ins->user_id = $user_todo_id;
                        $ins->group_id = $group_todo_id;
                        $ins->ipid = 'XXXXXXXXX';//faked ipid, it means todo for voluntaryworkers
                        $ins->todo = $todo_text_for_table;
                        $ins->create_date = date("Y-m-d H:i:s");
                        $ins->until_date = $done_date;//date("Y-m-d H:i:s", strtotime($date));
                        //$ins->record_id = $record_id;
                        $ins->course_id = $cust->id;
                        $ins->additional_info = '';//implode(";",$additional_info_ids);
                        $ins->triggered_by ="VoluntaryWorkers";
                        $ins->save();


                    }


                }



				$insid = $cust->id;
			}
		}
	}


	public function UpdateWrongEntry ( $post )
	{
		$exarr = explode(",", $post['ids']);
		foreach ($exarr as $key => $value)
		{
		    if(strlen($value) > 0 ){
		        
    			$cust = Doctrine::getTable('VoluntaryworkersCourse')->find($value);
    			$cust->wrong = $post['val'];
    
    			if ($post['val'] == 1)
    			{
    				$cust->wrongcomment = $post['comment'];
    			}
    			else
    			{
    				$cust->wrongcomment = "";
    			}
    			$cust->save();
		    }
		}

		return $cust;
	}

	 

 
	
	
 
}

?>