<?php

	Doctrine_Manager::getInstance()->bindComponent('SymptomatologyMaster', 'SYSDAT');

	class SymptomatologyMaster extends BaseSymptomatologyMaster {

		public function getSymptpomatology($cid)
		{

			$cust = Doctrine_Query::create()
				->select('*')
				->from('SymptomatologyMaster')
				->where('isdelete = 0')
				->andWhere('clientid = "' . $cid . '"');
			$track = $cust->fetchArray();
			if($track)
			{

				foreach($track as $k_sym => $v_sym)
				{
					$darray[$v_sym['id']] = $v_sym;
					$darray[$v_sym['id']]['set'] = '0';
				}
				return $darray;
			}
		}

	}

?>