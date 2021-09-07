<?php

	Doctrine_Manager::getInstance()->bindComponent('FormSapvBlockPermissions', 'SYSDAT');

	class FormSapvBlockPermissions extends BaseFormSapvBlockPermissions {

		public function get_sapv_permissions($client, $blocks = false)
		{
			$perms = Doctrine_Query::create()
				->select('*')
				->from('FormSapvBlockPermissions')
				->where('clientid="' . $client . '"');
			if($blocks && count($blocks) > 0)
			{
				$perms->andWhereIn('block', $blocks);
			}
			$perms_res = $perms->fetchArray();

			if($perms_res)
			{
				foreach($perms_res as $k_perm => $v_perm)
				{
					$group_perms[$v_perm['block']] = $v_perm['block'];
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