<?php

	Doctrine_Manager::getInstance()->bindComponent('OrgStepsPermissions', 'SYSDAT');

	class OrgStepsPermissions extends BaseOrgStepsPermissions {

		public function get_group_permissions($client, $group, $blocks = false)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('OrgStepsPermissions')
				->where('clientid="' . $client . '"')
				->andWhere('groupid="' . $group . '"');

			if($blocks && count($blocks) > 0)
			{
				$perms->andWhereIn('block', $blocks);
			}
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					$group_perms[$v_perm['step']] = $v_perm['value'];
				}

				return $group_perms;
			}
			else
			{
				return false;
			}
		}


		/**
		 * ISPC-2302 pct.3 @Lore 25.10.2019
		 * @author Lore
		 * @param unknown $client
		 * @param boolean $blocks
		 * @return unknown|boolean
		 */
		public function get_orgsteps_permissions($client, $blocks = false)
		{
		    $perms = Doctrine_Query::create()
		    ->select('*')
		    ->from('OrgStepsPermissions')
		    ->where('clientid = ?', $client);
		    		    
		    if($blocks && count($blocks) > 0)
		    {
		        $perms->andWhereIn('block', $blocks);
		    }
		    $perms_res = $perms->fetchArray();
		    
		    if($perms_res)
		    {
		        foreach($perms_res as $k_perm => $v_perm)
		        {
		            $group_perms[$v_perm['groupid']][$v_perm['step']] = $v_perm['value'];
		        }
		        
		        return $group_perms;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		public function groups2steps($client)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('OrgStepsPermissions')
				->where('clientid="' . $client . '"');
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					if($v_perm['value'] == 1)
					{
						$group_perms[$v_perm['groupid']][] = $v_perm['step'];
					}
				}

				return $group_perms;
			}
			else
			{
				return false;
			}
		}

		public function steps2groups($client)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('OrgStepsPermissions')
				->where('clientid="' . $client . '"');
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					if($v_perm['value'] == 1)
					{
						$group_perms[$v_perm['step']][] = $v_perm['groupid'];
					}
				}

				return $group_perms;
			}
			else
			{
				return false;
			}
		}

		public function clients_steps2groups($clients_array, $master_groups = false)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('OrgStepsPermissions')
				->whereIn("clientid", $clients_array);
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					if($v_perm['value'] == 1)
					{
						if($master_groups)
						{
							if(empty($group_perms[$v_perm['clientid']][$v_perm['step']]))
							{
								$group_perms[$v_perm['clientid']][$v_perm['step']] = array();
							}

							$group_perms[$v_perm['clientid']][$v_perm['step']] = array_merge($group_perms[$v_perm['clientid']][$v_perm['step']], $master_groups[$v_perm['clientid']][$v_perm['groupid']]);
						}
						else
						{
							$group_perms[$v_perm['clientid']][$v_perm['step']][] = $v_perm['groupid'];
						}
					}
				}

				return $group_perms;
			}
			else
			{
				return false;
			}
		}

		public function clients_steps2groups_second($clients_array, $grup_details)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('OrgStepsPermissions')
				->whereIn("clientid", $clients_array);
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					if($v_perm['value'] == 1 && strlen($grup_details[$v_perm['clientid']][$v_perm['groupid']][0]) > '0')
					{
						$group_perms[$v_perm['clientid']][$v_perm['step']][] = $grup_details[$v_perm['clientid']][$v_perm['groupid']][0];
					}
				}

				return $group_perms;
			}
			else
			{
				return false;
			}
		}

	}

?>