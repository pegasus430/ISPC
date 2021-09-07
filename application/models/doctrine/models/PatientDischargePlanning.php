<?php

	class PatientDischargePlanning extends BasePatientDischargePlanning {

		public function get_discharge_planning($ipid)
		{
			$select = Doctrine_Query::create()
				->select('*')
				->from('PatientDischargePlanning')
				->where('ipid LIKE "' . $ipid . '"');
			$select->andWhere('isdelete="0"');
			$select_res = $select->fetchArray();

			if($select_res)
			{
				return $select_res[0];
			}
			else
			{
				return false;
			}
		}

	}

?>