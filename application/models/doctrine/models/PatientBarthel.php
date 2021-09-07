<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientBarthel', 'MDAT');

	class PatientBarthel extends BasePatientBarthel {

		public function get_patient_form_data($form, $ipid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientBarthel')
				->where('id = "' . $form . '" ')
				->andWhere('ipid LIKE "' . $ipid . '"')
				->andWhere('isdelete = "0"');
			$q_res = $q->fetchArray();

			if($q_res)
			{
				$form_data = $q_res[0];
				$values = PatientBarthelValues::get_barthel_values($q_res[0]['id']);

				if($values)
				{
					$form_data['values'] = $values;
				}

				return $form_data;
			}
			else
			{
				return false;
			}
		}

		public function get_patient_lastform_data($ipid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientBarthel')
				->where('ipid LIKE "' . $ipid . '" ')
				->andWhere('isdelete = "0"')
				->orderBy('create_date DESC')
				->limit('1');
			$q_res = $q->fetchArray();

			if($q_res)
			{
				$form_data = $q_res[0];

				$values = PatientBarthelValues::get_barthel_values($q_res[0]['id']);

				if($values)
				{
					$form_data['values'] = $values;
				}

				return $form_data;
			}
			else
			{
				return false;
			}
		}

		public function get_patient_forms_ids($ipid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from('PatientBarthel')
				->where('ipid LIKE "' . $ipid . '" ')
				->orderBy('create_date ASC');
			$q_res = $q->fetchArray();

			if($q_res)
			{
				foreach($q_res as $k_res => $v_res)
				{
					$forms_ids[] = $v_res['id'];
				}

				return $forms_ids;
			}
			else
			{
				return false;
			}
		}

		public function delete_barthel_form($ipid, $form)
		{
			$u = Doctrine_Query::create()
				->update('PatientBarthel')
				->set('isdelete', '1')
				->where('form = "' . $form . '"')
				->andWhere('ipid = "' . $ipid . '"');
			$u_exec = $u->execute();
		}

	}

?>