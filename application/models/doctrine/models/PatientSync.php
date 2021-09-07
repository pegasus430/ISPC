<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientSync', 'IDAT');

	class PatientSync extends BasePatientSync {

		public function get_patient_sync($clientid = false, $userid = false)
		{
			if($clientid && $userid)
			{
				$q = Doctrine_Query::create()
					->select('*')
					->from('PatientSync')
					->where('userid = "' . $userid . '"')
					->andWhere('client = "' . $clientid . '"');
				$q_res = $q->fetchArray();

				if($q_res)
				{
					return $q_res;
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}

	}

?>