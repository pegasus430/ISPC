<?php

	Doctrine_Manager::getInstance()->bindComponent('FormTypePermissions', 'SYSDAT');

	class FormTypePermissions extends BaseFormTypePermissions {

		public function get_group_permissions($client, $group, $blocks = false)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('FormTypePermissions')
				->where('clientid= ?' , $client )
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
					$group_perms[$v_perm['type']] = $v_perm['value'];
				}

				return $group_perms;
			}
			else
			{
				return false;
			}
		}
		
		public function get_client_permissions($client, $blocks = false)
		{
		    $perms = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormTypePermissions')
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
		            $group_perms[$v_perm['groupid']][$v_perm['type']] = $v_perm['value'];
		        }
		        
		        return $group_perms;
		    }
		    else
		    {
		        return false;
		    }
		}
		
		public function get_groups_permissions($client, $groups, $blocks = false)
		{
		    if(!empty($groups)){
    		    if(!is_array($groups)){
    		        $group_array =  array($groups);
    		    }  else{
    		        $group_array = $groups;
    		    }
		    } else{
		        $group_array[] = "999999999";
		    }
			$perms = Doctrine_Query::create()
				->select('*')
				->from('FormTypePermissions')
				->where('clientid= ?', $client )
				->andWhereIn('groupid',$group_array);

			if($blocks && count($blocks) > 0)
			{
				$perms->andWhereIn('block', $blocks);
			}
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
				    if($v_perm['value'] == "1"){
    					$group_perms[$v_perm['groupid']] [] = $v_perm['type'];
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