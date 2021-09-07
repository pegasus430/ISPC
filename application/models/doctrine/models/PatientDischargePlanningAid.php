<?php

	class PatientDischargePlanningAid extends BasePatientDischargePlanningAid {

		public function get_discharge_planning_aid($ipid)
		{
			$select = Doctrine_Query::create()
				->select('*')
				->from('PatientDischargePlanningAid')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete="0"');
			$select_res = $select->fetchArray();

			if($select_res)
			{
				return $select_res;
			}
			else
			{
				return false;
			}
		}

	}

?>