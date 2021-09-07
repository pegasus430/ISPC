<?php

	Doctrine_Manager::getInstance()->bindComponent('NationalHolidays', 'SYSDAT');

	class NationalHolidays extends BaseNationalHolidays {

		public function getNationalHoliday($clientid, $day = false, $month = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clist = Doctrine_Query::create()
				->select("id,country")
				->from('Client')
				->where('isdelete=0')
				->andWhere('id="' . $clientid . '"');
			$clientlist = $clist->fetchArray();

			if(!empty($clientlist))
			{
				$nationalhd2state = Doctrine_Query::create();
				$nationalhd2state->select("s.*,n.*");
				$nationalhd2state->from('NationalHolidays2State s');
				$nationalhd2state->where("s.isdelete  = 0 ");
				$nationalhd2state->andWhere('s.state  = "' . $clientlist[0]['country'] . '"');
				$nationalhd2state->leftJoin('s.NationalHolidays n');
				$nationalhd2state->andWhere('s.holiday_id = n.id');
				$nationalhd2state->andWhere('n.isdelete = 0');
				if($day && !$month)
				{
					$date = date('Y-m-d 00:00:00', strtotime($day));
					$nationalhd2state->andwhere("date(n.date) = date('" . $date . "')");
				}

				if($month && $day)
				{
					$year = date('Y', strtotime($day));
					$month = date('m', strtotime($day));

					$nationalhd2state->andwhere("MONTH(n.date) = '" . $month . "' AND YEAR(n.date) = '" . $year . "'");
				}
//				echo $nationalhd2state->getSqlQuery();
//				exit;
				$nationalhd2state_array = $nationalhd2state->fetchArray();

				if($day && !empty($nationalhd2state_array) && !$month)
				{
					return true;
				}
				elseif($day && empty($nationalhd2state_array) && !$month)
				{
					return false;
				}
				else
				{
					return $nationalhd2state_array;
				}
			}
			else
			{

				return false;
			}
		}

	}

?>