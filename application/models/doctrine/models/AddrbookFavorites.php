<?php

	Doctrine_Manager::getInstance()->bindComponent('AddrbookFavorites', 'SYSDAT');

	class AddrbookFavorites extends BaseAddrbookFavorites {

		public function getFavorites($userid)
		{
			$fav = Doctrine_Query::create()
				->select('*')
				->from('AddrbookFavorites')
				->where("user_id = '" . $userid . "'")
				->orderby('type');

			$favarr = $fav->fetchArray();
			if($favarr)
			{
				$type = false;
				foreach($favarr as $fav_item)
				{
					$returnarr[$fav_item['type']][] = $fav_item['fav_id'];
				}

				return $returnarr;
			}
		}

	}

?>