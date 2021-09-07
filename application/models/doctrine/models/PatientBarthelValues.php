<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientBarthelValues', 'MDAT');

	class PatientBarthelValues extends BasePatientBarthelValues {

		public function get_barthel_values($form)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientBarthelValues')
				->where('form = "' . $form . '"')
				->andWhere('isdelete ="0"');
			$q_res = $q->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_data => $v_data)
				{
					$form_values[$v_data['section']][] = $v_data['value'];
				}

				if($form_values)
				{
					return $form_values;
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

		public function delete_barthel_values($form)
		{
			$u = Doctrine_Query::create()
				->update('PatientBarthelValues')
				->set('isdelete', '1')
				->where('form = "' . $form . '"');
			$u_exec = $u->execute();
		}

	}

?>