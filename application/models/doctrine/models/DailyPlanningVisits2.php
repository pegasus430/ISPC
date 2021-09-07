<?php

	Doctrine_Manager::getInstance()->bindComponent('DailyPlanningVisits2', 'SYSDAT');

	class DailyPlanningVisits2 extends BaseDailyPlanningVisits2 {

		function get_patients_visits($clientid, $date, $user_id = false)
		{
			$days = Doctrine_Query::create()
				->select('*')
				->from('DailyPlanningVisits2')
				->where("DATE(date) =  DATE('" . $date . "')")
				->andWhere("clientid = " . $clientid)
				->andWhere("isdelete = 0");
			if($user_id && strlen($user_id) > 0)
			{
				$days->andWhere(" userid = " . $user_id);
			}
			$days->orderBy('order_number ASC');
			$users_q_array = $days->fetchArray();

			if($users_q_array)
			{
				return $users_q_array;
			}
			else
			{
				return false;
			}
		}

		function get_last_patients_visits($clientid, $date, $user_id = false)
		{
			$days = Doctrine_Query::create()
				->select('*')
				->from('DailyPlanningVisits2')
				->where("DATE(date) =  DATE('" . $date . "')")
				->andWhere("clientid = " . $clientid)
				->andWhere("isdelete = 0");
			if($user_id && strlen($user_id) > 0)
			{
				$days->andWhere(" userid = " . $user_id);
			}

			$days->orderBy('date  DESC');
			$days->limit(1);
			$users_q_array = $days->fetchArray();

			if($users_q_array)
			{
				return $users_q_array[0];
			}
			else
			{
				return false;
			}
		}

	}

?>