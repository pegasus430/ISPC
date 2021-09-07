<?php

	Doctrine_Manager::getInstance()->bindComponent('BayernDoctorSymp', 'MDAT');

	class BayernDoctorSymp extends BaseBayernDoctorSymp {

		public function getBayernDoctorSymp($bayern_doc_id, $ipid)
		{
			$symps = Doctrine_Query::create()
				->select('*')
				->from('BayernDoctorSymp bvs')
				->where('bvs.bdf_id ="' . $bayern_doc_id . '" AND bvs.ipid ="' . $ipid . '"');
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