<?php

	Doctrine_Manager::getInstance()->bindComponent('Remedies', 'SYSDAT');

	class Remedies extends BaseRemedies {

		public function get_remedy($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Remedies')
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

		public function get_remedies($ids)
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
				->from('Remedies')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

	}

?>