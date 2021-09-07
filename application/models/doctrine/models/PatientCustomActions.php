<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientCustomActions', 'MDAT');

	class PatientCustomActions extends BasePatientCustomActions {

		public function getPatientSocialCodeActions($clientid, $ipid)
		{
			$patactions_sql = Doctrine_Query::create()
				->select('*')
				->from('PatientCustomActions')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete = 0');
			$patactionsarray = $patactions_sql->fetchArray();

			$comma = ",";
			$patient_action_str = "'0'";

			foreach($patactionsarray as $ac => $action_details)
			{
				$patient_action_str .= $comma . "'" . $action_details['action_id'] . "'";
				$comma = ",";
			}

			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid=" . $clientid . "")
				->andWhere('custom = 0 OR (custom = 1 AND id in(' . $patient_action_str . ') )')
				->andWhere('isdelete = 0');
			$actionsarray = $actions_sql->fetchArray();

			return $actionsarray;
		}

		public function getAllPatientSocialCodeActions($clientid, $price_list, $ipid, $used_actions)
		{
			if(empty($used_actions))
			{
				$used_actions[] = '9999';
			}

			$comma = ",";
			$patient_used_actions_str = "'0'";
			foreach($used_actions as $k_ua => $v_ua)
			{
				$patient_used_actions_str.= $comma . "'" . $v_ua . "'";
				$comma = ",";
			}

			$patactions_sql = Doctrine_Query::create()
				->select('*')
				->from('PatientCustomActions')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete = 0 OR (action_id IN (' . $patient_used_actions_str . ') )');

			if($_REQUEST['used'])
			{
				echo $patactions_sql->getSqlQuery();
				print_r("\n client \n");
			}
			$patactionsarray = $patactions_sql->fetchArray();

			$comma = ",";
			$patient_action_str = "'0'";

			foreach($patactionsarray as $ac => $action_details)
			{
				$patient_action_str .= $comma . "'" . $action_details['action_id'] . "'";
				$comma = ",";
			}

			$pl_actions_q = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceActions')
				->where("clientid='" . $clientid . "'")
				->andWhere('list = "' . $price_list . '"')
				->orderBy('aorder ASC');
			$pl_actions = $pl_actions_q->fetchArray();

			$comma = ",";
			$price_list_actions_str = "'0'";

			foreach($pl_actions as $k_pl => $v_pl)
			{
				$price_list_actions_str.= $comma . "'" . $v_pl['actionid'] . "'";
				$comma = ",";
			}

			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid=" . $clientid . "")
				->andWhere('custom = 0 OR (custom = 1 AND id in(' . $patient_action_str . ')   OR  (custom = 1 AND extra = 1 AND id IN(' . $price_list_actions_str . ')))')
				->andWhere('isdelete = 0 OR (id IN (' . $patient_used_actions_str . ') )');
			if($_REQUEST['used'])
			{
				echo $actions_sql->getSqlQuery();
			}

			$actionsarray = $actions_sql->fetchArray();

			return $actionsarray;
		}

		public function getSgbvActionsPatient($clientid, $ipid, $price_list)
		{

			$patactions_sql = Doctrine_Query::create()
				->select('*')
				->from('PatientCustomActions')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete = 0');

			$patactionsarray = $patactions_sql->fetchArray();

			$comma = ",";
			$patient_action_str = "'0'";

			foreach($patactionsarray as $ac => $action_details)
			{
				$patient_action_str .= $comma . "'" . $action_details['action_id'] . "'";
				$comma = ",";
			}


			$pl_actions_q = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceActions')
				->where("clientid='" . $clientid . "'")
				->andWhere('list = "' . $price_list . '"');
			$pl_actions = $pl_actions_q->fetchArray();

			$comma = ",";
			$price_list_actions_str = "'0'";

			foreach($pl_actions as $k_pl => $v_pl)
			{
				$price_list_actions_str.= $comma . "'" . $v_pl['actionid'] . "'";
				$comma = ",";
			}

			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid=" . $clientid . "")
				->andWhere('id in(' . $price_list_actions_str . ')  or id in(' . $patient_action_str . ')')
				->andWhere('isdelete = 0');
			$actionsarray = $actions_sql->fetchArray();

			if($price_list)
			{
				return $actionsarray;
			}
			else
			{
				return false;
			}
		}

		public function getAllSgbvActionsPatient($clientid, $ipid, $price_list, $used_actions)
		{
			if(empty($used_actions))
			{
				$used_actions[] = '9999';
			}

			$comma = ",";
			$patient_used_actions_str = "'0'";
			foreach($used_actions as $k_ua => $v_ua)
			{
				$patient_used_actions_str.= $comma . "'" . $v_ua . "'";
				$comma = ",";
			}

			$patactions_sql = Doctrine_Query::create()
				->select('*')
				->from('PatientCustomActions')
				->where('ipid LIKE "' . $ipid . '"');
			$patactions_sql->andWhere('isdelete = 0 OR (action_id IN (' . $patient_used_actions_str . ') )');
			$patactionsarray = $patactions_sql->fetchArray();


			$comma = ",";
			$patient_action_str = "'0'";

			foreach($patactionsarray as $ac => $action_details)
			{
				$patient_action_str .= $comma . "'" . $action_details['action_id'] . "'";
				$comma = ",";
			}

			if(is_array($price_list))
			{
				$price_list_arr = $price_list;
			}
			else
			{
				$price_list_arr = array($price_list);
			}

			$pl_actions_q = Doctrine_Query::create()
				->select("*")
				->from('SocialCodePriceActions')
				->where("clientid='" . $clientid . "'")
				->andWhereIn('list', $price_list_arr)
				->orderBy('list, aorder ASC');
			$pl_actions = $pl_actions_q->fetchArray();


			$comma = ",";
			$price_list_actions_str = "'0'";

			$price_list_actions_arr[] = '99999999999';
			foreach($pl_actions as $k_pl => $v_pl)
			{
				$price_list_actions_arr[] = $v_pl['actionid'];
				$price_list_actions_str.= $comma . "'" . $v_pl['actionid'] . "'";
				$comma = ",";
			}


			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid = " . $clientid . "")
				->andWhere('id IN(' . $price_list_actions_str . ')  OR id IN(' . $patient_action_str . ')');
			$actions_sql->andWhere('isdelete = 0 OR (id IN (' . $patient_used_actions_str . ') )');

			$actionsarray = $actions_sql->fetchArray();

			//maintain order using $price_list_actions_arr which is ordered by aorder
			$price_list_actions_arr = array_flip($price_list_actions_arr);

			foreach($actionsarray as $k_action => $v_action)
			{
				$actions_arr[$v_action['id']] = $v_action;
			}

			$ordered = array();
			foreach($price_list_actions_arr as $k_price_list_actions => $v_price_list_actions)
			{
				if(array_key_exists($k_price_list_actions, $actions_arr))
				{
					$ordered[$k_price_list_actions] = $actions_arr[$k_price_list_actions];
					unset($actions_arr[$k_price_list_actions]);
				}
			}


			$actions_array_final = array_merge($ordered, $actions_arr);

			if($price_list)
			{
				return $actions_array_final;
			}
			else
			{
				return false;
			}
		}

	}

?>