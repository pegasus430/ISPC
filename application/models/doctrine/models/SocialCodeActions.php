<?php

	Doctrine_Manager::getInstance()->bindComponent('SocialCodeActions', 'SYSDAT');

	class SocialCodeActions extends BaseSocialCodeActions {

		public function getCientSocialCodeActions($clientid)
		{
			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid=" . $clientid . "")
				->andWhere('isdelete = 0')
				->andWhere('custom = 0')
				->orderBy('internal_nr');
			$actionsarray = $actions_sql->fetchArray();

			if($actionsarray)
			{
				return $actionsarray;
			}
		}

		public function getAllAvailableActions($clientid)
		{
			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid=" . $clientid . "")
				->andWhere('isdelete = 0')
				->andWhere('custom = 0 OR (custom = 1 AND available  = 1 AND extra = 1 )')
				->orderBy('internal_nr');
			$actionsarray = $actions_sql->fetchArray();

			if($actionsarray)
			{
				return $actionsarray;
			}
		}

		public function getSocialCodeAction($actionid)
		{
			$action_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("id = '" . $actionid . "'")
				->andWhere('isdelete = 0');
			$action_details = $action_sql->fetchArray();

			if($action_details)
			{
				return $action_details;
			}
		}

		public function getActionsFormConditions($clientid)
		{
			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid=" . $clientid . "")
				->andWhere('isdelete = 0')
				->orderBy('id');
			$actionsarray = $actions_sql->fetchArray();

			foreach($actionsarray as $actk => $actv)
			{
				$conditions[$actv['id']] = $actv['form_condition'];
				if($actv['custom'] == 1)
				{
					$conditions[$actv['id']] = $conditions[$actv['parent']];
				}
			}

			if($conditions)
			{
				return $conditions;
			}
		}

		public function getAllCientSgbvActions($clientid, $exclude_deleted = true)
		{
			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid=" . $clientid . "");
			if($exclude_deleted === true)
			{
				$actions_sql->andWhere('isdelete = 0');
			}
			$actionsarray = $actions_sql->fetchArray();

			if($actionsarray)
			{
				return $actionsarray;
			}
		}

		public function official_actions_details($clientid)
		{
			if(empty($actions_ids_array))
			{
				$actions_ids_array[] = '99999';
			}

			$actions_sql = Doctrine_Query::create()
				->select('*')
				->from('SocialCodeActions')
				->where("clientid=" . $clientid . "");
			$actions_sql->andWhere('isdelete = 0');
			$actionsarray = $actions_sql->fetchArray();

			foreach($actionsarray as $key_actions => $value_actions)
			{
				$actions_array['group'][$value_actions['id']] = $value_actions['groupid'];
				$actions_array['action_name'][$value_actions['id']] = $value_actions['action_name'];

				if($value_actions['custom'] == '1' && $value_actions['parent'] != '0' && $value_actions['groupid'] == "0")
				{
					$actions_array['group'][$value_actions['id']] = $actions_array['group'][$value_actions['parent']];
				}
				elseif($value_actions['custom'] == '1' && $value_actions['parent'] == '0' && $value_actions['groupid'] == "0")
				{
					$actions_array['group'][$value_actions['id']] = '0';
				}

				if($value_actions['custom'] == '1' && $value_actions['parent'] != '0')
				{
					$actions_array['action_name'][$value_actions['id']] = $actions_array['action_name'][$value_actions['parent']];
				}
			}

			if($actions_array)
			{
				return $actions_array;
			}
		}

	}

?>