<?php

	Doctrine_Manager::getInstance()->bindComponent('Aid', 'SYSDAT');

	class Aid extends BaseAid {

		public function get_aid($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Aid')
				->where("id='" . $id . "'");
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

		public function get_all_aid($ids)
		{
			if(is_array($ids))
			{
				$array_ids = $ids;
			}
			else
			{
				$array_ids = array($ids);
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Aid')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}
		
		//ISPC-2381 Carmen 25.02.2020
		public static function get_default_aids($clientid, $client_is_follower = false){
			$drop = Doctrine_Query::create()
			->select('*')
			->from('Aid')
			->where("clientid =?",$clientid)
			->andWhere('isdelete=0')
			->andWhere('favourite=1');
			
			if ($client_is_follower) {// ISPC-2612 Ancuta 29.06.2020
			    $drop->andWhere('connection_id is NOT null');
			    $drop->andWhere('master_id is NOT null');
			}
			$droparray = $drop->fetchArray();
		
			if($droparray){
				$out=array_combine(array_column($droparray,'id'), array_column($droparray,'name'));
				return $out;
			}
			return array();
		}

	}

?>