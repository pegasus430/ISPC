<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDayStructureActions', 'MDAT');

	class PatientDayStructureActions extends BasePatientDayStructureActions {

		public function get_patient_day_structure_action($ipid, $form_id)
		{
			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('PatientDayStructureActions')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('form_id ="' . $form_id . '"')
				->andWhere('isdelete = 0')
				->orderBy('start ASC');
			$groupsarray = $groups_sql->fetchArray();

			if($groupsarray)
			{
				return $groupsarray;
			}
		}

	}

?>