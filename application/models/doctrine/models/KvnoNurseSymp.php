<?php

	Doctrine_Manager::getInstance()->bindComponent('KvnoNurseSymp', 'MDAT');

	class KvnoNurseSymp extends BaseKvnoNurseSymp {

		public function getKvnoNurseSymp($kvno_nurse_id, $ipid)
		{
			$symps = Doctrine_Query::create()
				->select('*')
				->from('KvnoNurseSymp kvs')
				->where('kvs.kdf_id ="' . $kvno_nurse_id . '" AND kvs.ipid ="' . $ipid . '"');
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