<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockPermissions', 'SYSDAT');

	class FormBlockPermissions extends BaseFormBlockPermissions {

		public function get_group_permissions($client, $group, $blocks = false)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('FormBlockPermissions')
				->where('clientid="' . $client . '"')
				->andWhere('groupid="' . $group . '"');
			if($blocks && count($blocks) > 0)
			{
				$perms->andWhereIn('block', $blocks);
			}
//			print_r($perms->getSqlQuery());exit;
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					$group_perms[$v_perm['block']] = $v_perm['value'];
				}

				return $group_perms;
			}
			else
			{
				return false;
			}
		}


		/**
		 * @author Loredana
		 * ISPC-2302 pct.3 @Lore 24.10.2019
		 * @param unknown $client
		 * @param boolean $blocks
		 * @return unknown|boolean
		 */
		public function get_client_formblock_permissions($client, $blocks = false)
		{
		    $perms = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockPermissions')
		    ->where('clientid= ?' , $client );
		    
		    if($blocks && count($blocks) > 0)
		    {
		        $perms->andWhereIn('block', $blocks);
		    }
		    $perms_res = $perms->fetchArray();
		    
		    if($perms_res)
		    {
		        foreach($perms_res as $k_perm => $v_perm)
		        {
		          $group_perms[$v_perm['groupid']][$v_perm['block']] = $v_perm['value'];
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