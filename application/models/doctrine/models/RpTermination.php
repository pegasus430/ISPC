<?php

	Doctrine_Manager::getInstance()->bindComponent('RpTermination', 'MDAT');

	class RpTermination extends BaseRpTermination {

		public function get_patient_rp_termination($ipid)
		{
			$rp_termination = Doctrine_Query::create()
				->select("*")
				->from('RpTermination')
				->where("ipid='" . $ipid . "'")
				->orderBy('id DESC')
				->limit('1');
			$rp_terminationarray = $rp_termination->fetchArray();

			if($rp_terminationarray)
			{
				return $rp_terminationarray[0];
			}
			else
			{
				return false;
			}
		}
	}

?>