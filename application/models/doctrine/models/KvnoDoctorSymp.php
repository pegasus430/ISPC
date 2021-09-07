<?php

	Doctrine_Manager::getInstance()->bindComponent('KvnoDoctorSymp', 'MDAT');

	class KvnoDoctorSymp extends BaseKvnoDoctorSymp {

		public function getKvnoDoctorSymp($kvno_doc_id, $ipid)
		{
			$symps = Doctrine_Query::create()
				->select('*')
				->from('KvnoDoctorSymp kvs')
				->where('kvs.kdf_id ="' . $kvno_doc_id . '" AND kvs.ipid ="' . $ipid . '"');
			$symarr = $symps->fetchArray();
			
			if(sizeof($symarr) > 0)
			{
				foreach($symarr as $symp)
				{
					$newsymarr[$symp['symp_id']] = $symp;
				}
				return $newsymarr;
			}
		}

	}

?>