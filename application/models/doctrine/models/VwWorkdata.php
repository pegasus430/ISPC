<?php

	Doctrine_Manager::getInstance()->bindComponent('VwWorkdata', 'SYSDAT');

	class VwWorkdata extends BaseVwWorkdata {

		public function get_vw_work_data($id, $type = "n", $history = false)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('VwWorkdata');
			if($history === false)
			{
			    $drop->where('isdelete="0"');
			}
							
				$drop->andWhere("vw_id=?", $id);
				$drop->andWhere('type=?', $type);
				$drop->orderBy('work_date ASC');
			$droparray = $drop->fetchArray();
			return $droparray;
		}
		
		
		public function get_work_data($id, $type = "n", $history = false)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('VwWorkdata');
			if($history === false)
			{
			    $drop->where('isdelete="0"');
			}
							
				$drop->andWhere("id=?", $id);
				$drop->andWhere('type=?', $type);
				$drop->orderBy('work_date ASC');
			$droparray = $drop->fetchArray();
			return $droparray;
		}
		
	}

?>