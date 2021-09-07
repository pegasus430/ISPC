<?php

	Doctrine_Manager::getInstance()->bindComponent('BtmGroupPermissions', 'SYSDAT');

	class BtmGroupPermissions extends BaseBtmGroupPermissions {
		
		/**
		 * 
		 * 2017.04.25
		 * 
		 * @var array $has_access
		 * 
		 * the methods for add/remove a btm drug to a group/user/patient
		 * this if JUST for info, as it is not used anyware in the app (!yet)
		 * 
		 */
		protected static $has_access  = array(
			"use" => 0,
			"method_lieferung" => 0,
			"method_sonstiges" => 0,
			"method_ubergabe_abgabe" => 0,
			"method_verbrauch" => 0,
			"method_rucknahme_ruckgabe" => 0,
		);
		
		public function get_group_permissions($client, $group)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('BtmGroupPermissions')
				->where('clientid = ?' , $client )
				->andWhere('groupid = ?' ,  $group );
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					$group_perms[$v_perm['name']] = $v_perm['value'];
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
		 * @return unknown|boolean
		 */
		public function get_all_groups_permissions($client)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('BtmGroupPermissions')
				->where('clientid= ?' , $client );
			
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					$group_perms[$v_perm['groupid']][$v_perm['name']] = $v_perm['value'];
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