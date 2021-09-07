<?php

	Doctrine_Manager::getInstance()->bindComponent('BtmPermissions', 'SYSDAT');

	class BtmPermissions extends BaseBtmPermissions {

		public function btmPermissionsByClient($client)
		{
			$bt = Doctrine_Query::create()
				->select('*')
				->from('BtmPermissions')
				->where('client="' . $client . '"');
			$btarray = $bt->fetchArray();

			if($btarray)
			{
				foreach($btarray as $k_perms => $v_perms)
				{
					$permissions[$v_perms['user']]['canadd'] = $v_perms['canadd'];
					$permissions[$v_perms['user']]['candelete'] = $v_perms['candelete'];
				}
			}

			return $permissions;
		}
	}

?>