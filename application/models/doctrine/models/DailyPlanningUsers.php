<?php

	Doctrine_Manager::getInstance()->bindComponent('DailyPlanningUsers', 'SYSDAT');

	class DailyPlanningUsers extends BaseDailyPlanningUsers {

		function get_users_by_date($clientid, $date, $user_id = false, $ids_only = false, $allowed_deleted = false)
		{
			$days = Doctrine_Query::create()
				->select('id, date, clientid, userid, userid_type, view_mode')
				->from('DailyPlanningUsers')
				->where("DATE(date)=  DATE('" . $date . "')")
				->andWhere("clientid = " . $clientid);
			if(!$allowed_deleted)
			{
				$days->andWhere("isdelete = 0");
			}

			if($user_id && strlen($user_id) > 0)
			{
				$days->andWhere("userid=" . $user_id);
			}

			$days->orderBy('date ASC');
			$users_q_array = $days->fetchArray();

			if($ids_only)
			{
				foreach($users_q_array as $key => $value)
				{
					$users2date[] = $value['userid'];
				}
				$users_array = $users2date;
			}
			else
			{
				foreach($users_q_array as $key => $value)
				{
					$users_details2date[$value['userid']] = $value;
				}
				$users_array = $users_details2date;
			}

			if($users_array)
			{
				return $users_array;
			}
			else
			{
				return false;
			}
		}
		
		
		//$date_interval = array( "start"=>date("Y-m-d"), "end"=>date("Y-m-d")) 
		public function get_users_by_date_interval( $clientid , $date_interval = array( "start"=> false, "end"=>false ) , $user_id = false , $userid_type = false){
			
			if (!isset($date_interval['start'], $date_interval['end']) ) return false;
			
			$days = Doctrine_Query::create()
			->select('id, date, clientid, userid, userid_type, view_mode')
			->from('DailyPlanningUsers')
			->Where("clientid = ? " , $clientid)
			->andWhere("isdelete = ? ", 0)
			->andWhere("`date`>= ?" , $date_interval['start'] )
			->andWhere("`date`<= ?" , $date_interval['end']);
			
			
			if ($user_id !== false){
				if(!is_array($user_id))
				{
					$user_id = array($user_id);
				}
				$days->andWhereIn("userid" , $user_id);
			
			}
			if($userid_type !== false){
				$days->andWhere("userid_type = ? " , $userid_type);
			}
			
			$users_q_array = $days->fetchArray();
				
			return $users_q_array;
		}
	}

?>