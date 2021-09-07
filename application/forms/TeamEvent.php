<?php

	require_once("Pms/Form.php");

	class Application_Form_TeamEvent extends Pms_Form {

		public $allusers;

		public function validate($post)
		{
			
// 			print_r($post); exit;
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$Tr = new Zend_View_Helper_Translate();
			$error = '0';
			$val = new Pms_Validation();

			
			if(!$val->isstring($post['event_name']))
			{
				$this->error_message['event_name'] = $Tr->translate('event_fill_name');
				$error = '1';
			}
			
			if(!$val->isstring($post['event_type']))
			{
				$this->error_message['event_type'] = $Tr->translate('event_select_type');
				$error = '2';
			}
			
			if(!$val->isstring($post['from_time']))
			{
				$this->error_message['from_time'] = $Tr->translate('event_fill_start_time');
				$error = '3';
			}

			if(!$val->isstring($post['till_time']))
			{
				$this->error_message['till_time'] = $Tr->translate('event_fill_end_time');
				$error = '4';
			}

			$start_date_time = strtotime(date('Y-m-d', time()) . ' ' . $post['from_time'] . ':00');
			$end_date_time = strtotime(date('Y-m-d', time()) . ' ' . $post['till_time'] . ':00');

			if($start_date_time >= $end_date_time && $error == "0")
			{
				$this->error_message['time'] = $Tr->translate('event_start_lower_than_end');
				$error = '5';
			}

			if(!$val->isstring($post['date']))
			{
				$this->error_message['date'] = $Tr->translate('event_fill_date');
				$error = '6';
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

		public function insert_event_data($post)
		{
			//print_r($post);
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$first_time_completed = false;
			//event date formated db style Y-m-d H:i:s
			$event_date_fdb = date('Y-m-d H:i:s', strtotime($post['date']));

			//event date formated Y-m-d
			$event_date_f = date('Y-m-d', strtotime($post['date']));

			$start_time = $event_date_f . ' ' . $post['from_time'] . ':00';
			$end_time = $event_date_f . ' ' . $post['till_time'] . ':00';

			if(empty($post['mid']) || $post['mid'] == '0')
			{
				//save basic event data
				$ins = new TeamEvent();
				$ins->client = $clientid;
				$ins->event_name = trim(rtrim($post['event_name']));
				$ins->event_type = $post['event_type'];
				$ins->date = $event_date_fdb;
				$ins->from_time = date('Y-m-d H:i:s', strtotime($start_time));
				$ins->till_time = date('Y-m-d H:i:s', strtotime($end_time));
				$ins->organizational = trim(rtrim($post['organizational']));
				if($post['completed'])
				{
					$ins->completed = "1";
					$ins->first_time = "1";
					$first_time_completed = true;
				}
				else
				{
					$ins->completed = "0";
					$first_time_completed = false;
				}
				$ins->voluntary_event = $post['voluntary_event'];
				$ins->save();

				$event_id = $ins->id;
			}
			elseif($post['mid'] > '0')
			{
				//update event master data on edit
				$update = Doctrine::getTable('TeamEvent')->findOneByIdAndClient($post['mid'], $clientid);
				if($update)
				{
					$found_event = $update->toArray();

					if($found_event['completed'] == "0" && $found_event['first_time'] == "0" && $post['completed'] == '1')
					{
						$first_time_completed = true;
					}
					else
					{
						$first_time_completed = false;
					}

					$update->event_name = trim(rtrim($post['event_name']));
					$update->event_type = $post['event_type'];
					$update->date = $event_date_fdb;
					$update->from_time = date('Y-m-d H:i:s', strtotime($start_time));
					$update->till_time = date('Y-m-d H:i:s', strtotime($end_time));
					$update->organizational = trim(rtrim($post['organizational']));
					if($post['completed'])
					{
						$update->completed = "1";
						$update->first_time = "1";
					}
					else
					{
						$update->completed = "0";
					}
					$update->voluntary_event = $post['voluntary_event'];
					$update->save();
				}

				$event_id = $post['mid'];
				
				$clear_attending_users = $this->clear_attending_users($clientid, $event_id);
				$clear_attending_vw = $this->clear_attending_vw($clientid, $event_id);
			}

			
			if($post['voluntary_event'] == "1"){
    			//  attending voluntary 
    			foreach($post['attending_vw'] as $k_vw => $v_vw)
    			{
    				$mutiple_attending_vw_data[] = array(
    					'client' => $clientid,
    					'event' => $event_id,
    					'vw_id' => $v_vw
    				);
    			}
    
    			if($mutiple_attending_vw_data)
    			{
    				$collection_vw = new Doctrine_Collection('TeamEventAttendingVoluntaryW');
    				$collection_vw->fromArray($mutiple_attending_vw_data);
    				$collection_vw->save();
    			}
			    
			} else{
			    
    			//  attending users
    			foreach($post['attending_users'] as $k_usr => $v_usr)
    			{
    				$mutiple_attending_users_data[] = array(
    					'client' => $clientid,
    					'event' => $event_id,
    					'user' => $v_usr
    				);
    			}
    
    			if($mutiple_attending_users_data)
    			{
    				$collection_u = new Doctrine_Collection('TeamEventAttendingUsers');
    				$collection_u->fromArray($mutiple_attending_users_data);
    				$collection_u->save();
    			}
			}
			
			
			
			###############################################
			
			
			
			if($first_time_completed) {
			    // add to voluntary worker activities

			    $duration = 0;
			    ///get event duration
			    $from_time  = strtotime($start_time);
			    $till_time = strtotime($end_time);
			     
			    if($till_time > $from_time){
			        $result_time = $till_time - $from_time  ;
			        $duration = $result_time / 60;
			    }
			    foreach($post['attending_vw'] as $k => $a_vw_id)
			    {
		            $vw_activities_data_array[] = array(
		                'vw_id' => $a_vw_id,
		                'clientid' => $clientid,
		                'date' => date('Y-m-d H:i:s', strtotime($start_time)),
		                'activity' => trim(rtrim($post['event_name'])),
		                'team_event' => "1",
		                'team_event_id' => $event_id,
		                'team_event_type' =>  $post['event_type'],
		                'comment' => "",
		                'duration' => $duration
		            );
			    }
			    
			    $collection_a = new Doctrine_Collection('VoluntaryworkersActivities');
			    $collection_a->fromArray($vw_activities_data_array);
			    $collection_a->save();
			}
			
			return $event_id;
		}

		public function clear_attending_users($client, $event)
		{
			if($client && $event)
			{
				$q = Doctrine_Query::create()
					->update('TeamEventAttendingUsers')
					->set('isdelete', "1")
					->where('client = "' . $client . '"')
					->andWhere('event = "' . $event . '"')
					->andWhere('isdelete = "0"');
				$q->execute();

				return true;
			}
			else
			{
				return false;
			}
		}

		public function clear_attending_vw($client, $event)
		{
		    
			if($client && $event)
			{
				$q = Doctrine_Query::create()
					->update('TeamEventAttendingVoluntaryW')
					->set('isdelete', "1")
					->where('client = "' . $client . '"')
					->andWhere('event = "' . $event . '"')
					->andWhere('isdelete = "0"');
				$q->execute();

				return true;
			}
			else
			{
				return false;
			}
		}
	}
?>