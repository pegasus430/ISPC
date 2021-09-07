<?php

	Doctrine_Manager::getInstance()->bindComponent('IconsClient', 'SYSDAT');

	class IconsClient extends BaseIconsClient {

		public function get_client_icons($clientid, $allowed_ids = false, $type = false)
		{
		    if ($type !== false && strlen($type)) {
		        //icons from icons_member | icons_vw
		    } elseif ($allowed_ids === false ) {
		        //original do not filter by icons
		    } elseif (is_null($allowed_ids) || (is_array($allowed_ids) && empty($allowed_ids))) {
		        return;
		        //return was created for ISPC-2138 -> PatientMaster::getMasterData()->$sys_icons->get_system_icons()
		        //also fixes group not having icon permisions
		    }
		    
		    
			$icns = Doctrine_Query::create()
				->select('*')
				->from('IconsClient')
				->where('isdelete = 0')
				->andWhere('client_id ="' . $clientid . '"')
				->andWhere('icon_id =0');
				if($type){
				$icns->andWhere('type =?',$type);
				} else {
    				$icns->andWhere('type ="patient" ');
				}
				$icns->orderBy('id ASC');
			if($allowed_ids)
			{
				if(is_array($allowed_ids))
				{
					$icns->andWhereIn('id', $allowed_ids);
				}
				else
				{
					$icns->andWhere('id = ?', $allowed_ids);
				}
			}
			$icons = $icns->fetchArray();

			$icons_client = array();
			foreach($icons as $k_icon => $v_icon)
			{
				$icons_client[$v_icon['id']] = $v_icon;
			}

			return $icons_client;
		}

		public function get_client_icon($clientid, $icon)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('IconsClient')
				->where('isdelete = 0')
				->andWhere('client_id ="' . $clientid . '"')
				->andWhere('id ="' . $icon . '"')
				->orderBy('id ASC');
			$icon = $icns->fetchArray();

			if($icon)
			{
				return $icon;
			}
		}

		public function count_client_icons($clientid)
		{
			$icns = Doctrine_Query::create()
				->select('*, count(id) as counter')
				->from('IconsClient')
				->where('isdelete = 0')
				->andWhere('client_id ="' . $clientid . '"')
				->andWhere('icon_id = 0')
				->orderBy('id ASC');
			$icon = $icns->fetchArray();

			return $icon;
		}

		public function get_client_system_icons($clientid, $master_icon_ids)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('IconsClient')
				->where('isdelete = 0')
				->andWhere('client_id ="' . $clientid . '"')
				->andWhereIn('icon_id', $master_icon_ids)
				->orderBy('id ASC');
			$icons = $icns->fetchArray();

			foreach($icons as $k_icon => $v_icon)
			{
				$icons_client[$v_icon['icon_id']] = $v_icon;
			}

			return $icons_client;
		}

		public function get_clients_icons($clients)
		{
			$icns = Doctrine_Query::create()
				->select('*')
				->from('IconsClient')
				->where('isdelete = 0')
				->andWhereIn('client_id', $clients)
				->andWhere('isdelete = "0"')
				->andWhere('icon_id = "0"') //exclude system icons custom images(only custom icons)
				->orderBy('id ASC');
			$icons = $icns->fetchArray();

			foreach($icons as $k_icon => $v_icon)
			{
				$icons_client[$v_icon['client_id']][] = $v_icon['id'];
			}
			ksort($icons_client);

			return $icons_client;
		}
		
		//get only the column `icon_settings``, for one single icon_id
		public static function get_client_icon_settings($icon_id = 0, $client_id = 0)
		{
			$icns = Doctrine_Query::create()
			->select('id, icon_settings')
			->from('IconsClient')
			->where('client_id = ?', $client_id)
			->andWhere('icon_id = ?', $icon_id)
			->andWhere('isdelete = 0')
			->orderBy('id ASC');
			$icon = $icns->fetchOne(null,  Doctrine_Core::HYDRATE_ARRAY);

			return $icon;
		}
		
		
	}

?>