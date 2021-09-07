<?php

	Doctrine_Manager::getInstance()->bindComponent('VoluntaryWorkersSecondaryStatuses', 'SYSDAT');

	class VoluntaryWorkersSecondaryStatuses extends BaseVoluntaryWorkersSecondaryStatuses {
		 
		public function get_secondarystatuses($client = 0, $newstat = false)
		{
			$fdocarray = array();
			
			$fdoc = Doctrine_Query::create()
				->select('*, description as status')
				->from('VoluntaryWorkersSecondaryStatuses')
				->where('isdelete = "0"')
				->andWhere('clientid = ?', $client);
			$fdocarr = $fdoc->fetchArray();
			
			if($newstat === true)
			{
				foreach($fdocarr as $kd=>$vd)
				{
					$fdocarray[$vd['id']] = $vd['status'];
				}
			
				return $fdocarray;
			}
			else 
			{
				return $fdocarr;
			}
		}
		
	}
	
?>	