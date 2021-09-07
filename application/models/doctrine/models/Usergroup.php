<?php

	Doctrine_Manager::getInstance()->bindComponent('Usergroup', 'SYSDAT');

	class Usergroup extends BaseUsergroup {

		public function getUserGroupData($groupid)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where("id = '" . (int)$groupid . "'");

			$medics = $medic->execute();
			if($medics)
			{
				$medicarr = $medics->toArray();
				return $medicarr;
			}
		}

		public function getUserGroupMultiple($groupids)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->whereIn("id", $groupids);
			$medics = $medic->fetchArray();
			if($medics)
			{
				foreach($medics as $k_med => $v_med)
				{
					$medics_arr[$v_med['id']] = $v_med;
				}
				return $medics_arr;
			}
			else
			{
				return false;
			}
		}

		public function getDoctorGroupid($clientid)
		{
			$medic = Doctrine_Query::create()
// 				->select('*')
				->select('id')
				->from('Usergroup')
				->where("clientid=" . (int)$clientid . " and (groupname='Doctor' or groupname='Hausarzt' or groupname='Doktor' or groupname='Arzt' or groupname='QPA')");
			$medics = $medic->execute();

			if($medics)
			{
				$medicarr = $medics->toArray();
				$groupid = $medicarr[0]['id'];
				return $groupid;
			}
		}

		public function getMastergroupGroups($clientid, $mastergroups)
		{
			if(is_array($mastergroups))
			{
				$mastergroups_arr = $mastergroups;
			}
			else
			{
				$mastergroups_arr = array($mastergroups);
			}

			$mastergroups_arr[] = '999999999';
			$groups = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where('clientid = "' . (int)$clientid . '"')
				->andWhereIn('groupmaster', $mastergroups_arr);
			$user_groups = $groups->fetchArray();

			if($user_groups)
			{
				foreach($user_groups as $k_usr_gr => $v_usr_gr)
				{
					$usr_grups[] = $v_usr_gr['id'];
				}
			}
			else
			{
				$usr_grups[] = '999999999';
			}

			return $usr_grups;
		}

		public function getCordinatorGroupid($clientid)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where("clientid=" . (int)$clientid . " and (groupname='Coordinators' or groupname='Koordinatoren')");
			$medics = $medic->execute();
			if($medics)
			{
				$medicarr = $medics->toArray();
				$groupid = $medicarr[0]['id'];
				return $groupid;
			}
		}

		public function getPflegeGroupid($clientid)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where("clientid=" . (int)$clientid . " and groupname LIKE '%Pflege%'");
			$medics = $medic->execute();
			if($medics)
			{
				$medicarr = $medics->toArray();
				$groupid = $medicarr[0]['id'];
				return $groupid;
			}
		}

		//ispc-1533 - isactive was added as filter
		public function getClientGroups($clientid , $isactive = false)
		{
			$group = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where("clientid = ? AND isdelete = ?", array((int)$clientid, "0"))
				->orderBy('groupname ASC');
			
			if( $isactive !== false){				
				$group->andWhere('isactive = ?', $isactive);
			}
				
			$groups = $group->execute();
			if($groups)
			{
				$grouparr = $groups->toArray();
				return $grouparr;
			}
		}

		public static function getMasterGroup($group_id)
		{
			$group = Doctrine_Query::create()
// 				->select('*')
				->select('id, groupmaster')
				->from('Usergroup')
				->where("id = ?" , (int)$group_id );
			$gm = $group->fetchArray();
			if($gm)
			{
				return $gm[0]['groupmaster'];
			}
		}

		/*
		public function getUserGroups($masterGroupIds, $clients = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if(is_array($masterGroupIds))
			{

				$comma = ",";
				$groupids = "'999999999'";
				foreach($masterGroupIds as $group)
				{
					$groupids .= $comma . "'" . $group . "'";
					$comma = ",";
				}
			}
			else
			{
				$groupids = '"' . $masterGroupIds . '"';
			}

			$group = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where("groupmaster IN (" . $groupids . ") ")
				->andWhere('isdelete = 0');

			if($clients)
			{
				$group->andWhereIn('clientid', $clients);
			}
			else
			{
				$group->andWhere('clientid = ' . $logininfo->clientid . '');
			}

			$gm = $group->fetchArray();
			if($gm)
			{

				return $gm;
			}
		}
		*/
		
		/**
		 * @cla on 12.07.2018 
		 * re-write
		 * 
		 * @param string|array $masterGroupIds
		 * @param array $clients , optional
		 */
		public static function getUserGroups($masterGroupIds = array(), $clients = null)
		{
		    if (empty($masterGroupIds)) {
		        return; //fail-safe
		    }
		    
		    $masterGroupIds = is_array($masterGroupIds) ? $masterGroupIds : [$masterGroupIds];
		    
		    $group = Doctrine_Query::create()
		    ->select('*')
		    ->from('Usergroup')
		    ->whereIn("groupmaster", $masterGroupIds)
		    ->andWhere('isdelete = 0')
		    ;
		    
		    if ( ! is_null($clients) && is_array($clients)) {
		        $group->andWhereIn('clientid', $clients);
		    } else {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $group->andWhere('clientid  = ?', $logininfo->clientid);
		    }
		    
		    $gm = $group->fetchArray();
		    
		    if ($gm) {
		        return $gm;
		    }
		}
		
		
		public function get_clients_groups($client_ids_array = array())
		{
			if (empty($client_ids_array) || ! is_array($client_ids_array)) {
				return;
			}

			$group = Doctrine_Query::create()
				->select('*')
				->from('Usergroup')
				->where("isdelete=0")
				->andWhereIn("clientid", $client_ids_array)
				->orderBy('groupname ASC');
			$grouparr = $group->fetchArray();

			if($grouparr)
			{
				return $grouparr;
			}
		}

		public function get_groups_users($groupids = false, $clientid = false,$no_group_id_key=false)
		{
			if($clientid)
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('clientid=' . (int)$clientid)
					->andWhere('isdelete=0 and isactive=0')
					->andWhereIn('groupid', $groupids)
					->orderBy('last_name ASC');
				$user_res = $user->fetchArray();

				if($user_res)
				{
					foreach($user_res as $k_usr => $v_usr)
					{
					    if($no_group_id_key){
    						$users_ids[] = $v_usr['id'];
					    } 
					    else
					    {
    						$users_ids[$v_usr['groupid']][] = $v_usr['id'];
					    }
					}

					return $users_ids;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		//multi client functions (accepts multiple clients as parameter) START

		public function get_clients_mastergroup_users($mastergroups = false, $clientids = false)
		{
			if($mastergroups && $clientids)
			{
				if(is_array($mastergroups))
				{
					$mastergroups_arr = $mastergroups;
				}
				else
				{
					$mastergroups_arr = array($mastergroups);
				}

				$mastergroups_arr[] = '999999999';
				$groups = Doctrine_Query::create()
					->select('*')
					->from('Usergroup')
					->whereIn('clientid', $clientids)
					->andWhereIn('groupmaster', $mastergroups_arr);
				$user_groups = $groups->fetchArray();

				$usr_grups[] = '999999999999';
				if($user_groups)
				{
					foreach($user_groups as $k_usr_gr => $v_usr_gr)
					{
						$usr_grups[] = $v_usr_gr['id'];
					}

					$users_gr = Doctrine_Query::create()
						->select('*')
						->from('User')
						->whereIn('clientid', $clientids)
						->andWhere('isdelete=0 and isactive=0')
						->andWhereIn('groupid', $usr_grups);
					$users_gr_res = $users_gr->fetchArray();

					if($users_gr_res)
					{
						return $users_gr_res;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		public function get_clients_groups_users($groupids = false, $clientids = false)
		{
			if($clientids && $groupids)
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->whereIn('clientid', $clientids)
					->andWhere('isdelete=0 and isactive=0')
					->andWhereIn('groupid', $groupids)
					->orderBy('last_name ASC');
				$user_res = $user->fetchArray();

				if($user_res)
				{
					foreach($user_res as $k_usr => $v_usr)
					{
						$users_ids[$v_usr['clientid']][$v_usr['groupid']][] = $v_usr['id'];
					}

					return $users_ids;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

		//multi client functions (accepts multiple clients as parameter) END
		
		public function get_current_group_master($userid,$clientid)
		{
			if($clientid && $userid)
			{
				$user = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where('id = "'.(int)$userid.'" ')
					->andWhere('isdelete=0 and isactive=0');
				$user_res = $user->fetchArray();

				if($user_res)
				{
					foreach($user_res as $k_usr => $v_usr)
					{
						$user_group = $v_usr['groupid'];
						$return[$userid]['user_group'] =  $v_usr['groupid'];;
					}
				    
				    if($user_group)
				    {
    				    $groups = Doctrine_Query::create()
    				    ->select('*')
    				    ->from('Usergroup')
    				    ->where('clientid = "' . (int)$clientid . '"')
    				    ->andWhere('id = "'.(int)$user_group.'"');
    				    $user_groups = $groups->fetchArray();
    				    if($user_groups){
    				           foreach($user_groups as $k=>$gd){
    				               $return[$userid]['user_mastergroup'] =  $gd['groupmaster'];;
    				        }
    				    }
				    }

					return $return;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}


		//Maria:: Migration CISPC to ISPC 22.07.2020
        public static function getClientGroupsMap($clientid) {
            $group = Doctrine_Query::create()
                ->select('*')
                ->from('Usergroup')
                ->where("clientid=" . $clientid . " and isdelete=0")
                ->orderBy('groupname ASC');


            // echo $medic->getSqlQuery();
            $groups = $group->execute();
            if ($groups) {
                $grouparr = $groups->toArray();
                $output=array();
                foreach ($grouparr as $group){
                    $output[$group['id']]=$group;
                }

                return $output;
            }
        }
	}

?>