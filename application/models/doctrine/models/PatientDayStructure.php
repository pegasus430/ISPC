<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDayStructure', 'MDAT');

	class PatientDayStructure extends BasePatientDayStructure {

		public function get_patient_day_structure($ipid, $form_id = false)
		{
			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('PatientDayStructure')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete = 0');
			if($form_id)
			{
				$groups_sql->andWhere('form_id ="' . $form_id . '"');
			}
			$groupsarray = $groups_sql->fetchArray();


			if($groupsarray)
			{
				return $groupsarray;
			}
		}

	}

?>