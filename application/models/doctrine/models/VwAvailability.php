<?php

	Doctrine_Manager::getInstance()->bindComponent('VwAvailability', 'SYSDAT');

	class VwAvailability extends BaseVwAvailability {

		public function get_vw_availability($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('VwAvailability')
				->where("vw_id='" . $id . "'  and isdelete = 0")
				->orderBy('week_day, start_time ASC');
			$droparray = $drop->fetchArray();
			return $droparray;
		}

	}

?>