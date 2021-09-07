<?php

	Doctrine_Manager::getInstance()->bindComponent('CareservicesGroups', 'SYSDAT');

	class CareservicesGroups extends BaseCareservicesGroups {

		public function get_client_groups($clientid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('CareservicesGroups')
				->where("client=?", $clientid)
				->andWhere("isdelete = 0");
			$droparray = $drop->fetchArray();

			if($droparray)
			{
				return $droparray;
			}
			else
			{
				return false;
			}
		}

// 		public function get_all_aid($ids)
// 		{
// 			if(is_array($ids))
// 			{
// 				$array_ids = $ids;
// 			}
// 			else
// 			{
// 				$array_ids = array($ids);
// 			}

// 			$drop = Doctrine_Query::create()
// 				->select('*')
// 				->from('Aid')
// 				->whereIn("id", $array_ids);
// 			$droparray = $drop->fetchArray();

// 			return $droparray;
// 		}

	}

?>