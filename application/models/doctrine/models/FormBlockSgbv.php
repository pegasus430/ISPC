<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockSgbv', 'MDAT');

	class FormBlockSgbv extends BaseFormBlockSgbv {

		public function getPatientFormBlockSgbv($ipid, $contact_form_id, $allow_deleted = false)
		{
			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockSgbv')
				->where("ipid='" . $ipid . "'")
				->andWhere('contact_form_id ="' . $contact_form_id . '"');
			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}
			$groupsarray = $groups_sql->fetchArray();

			if($groupsarray)
			{
				foreach($groupsarray as $key => $action_details)
				{
					$patient_actions[$action_details['action_id']] = $action_details['action_value'];
				}

				return $patient_actions;
			}
		}

		public function getFormsSavedActions($ipid, $contact_forms)
		{
			$contact_forms[] = '99999999';
			$actions_q = Doctrine_Query::create()
				->select('*')
				->from('FormBlockSgbv')
				->where("ipid='" . $ipid . "'")
				->andWhereIn('contact_form_id', $contact_forms)
				->andWhere('action_value = "1"')
				->andWhere('isdelete = "0"')
				->andWhere('unpaid = "0"');

			$actions_res = $actions_q->fetchArray();

			if($actions_res)
			{
				return $actions_res;
			}
			else
			{
				return false;
			}
		}

		public function getPatientFormSavedActions($ipid, $contact_form, $exclude_deleted = true)
		{
			$contact_forms[] = '99999999';
			$actions_q = Doctrine_Query::create()
				->select('*')
				->from('FormBlockSgbv')
				->where("ipid='" . $ipid . "'")
				->andWhere("contact_form_id='" . $contact_form . "'")
				->andWhere('action_value = "1"');
			if($exclude_deleted === true)
			{
				$actions_q->andWhere('isdelete = "0"');
			}
			$actions_q->andWhere('unpaid = "0"');

			$actions_res = $actions_q->fetchArray();

			if($actions_res)
			{
				return $actions_res;
			}
			else
			{
				return false;
			}
		}

		public function getAllPatientFormSavedActions($ipid, $contact_form, $exclude_deleted = true)
		{
			$contact_forms[] = '99999999';
			$actions_q = Doctrine_Query::create()
				->select('*')
				->from('FormBlockSgbv')
				->where("ipid='" . $ipid . "'")
				->andWhere("contact_form_id='" . $contact_form . "'")
				->andWhere('action_value = "1"');
			if($exclude_deleted === true)
			{
				$actions_q->andWhere('isdelete = "0"');
			}
			$actions_res = $actions_q->fetchArray();

			if($actions_res)
			{
				return $actions_res;
			}
			else
			{
				return false;
			}
		}
		
		//ISPC-2746 Carmen 03.12.2020
		public function getPatientsFormsSavedActions($contact_forms)
		{
			$actions_q = Doctrine_Query::create()
			->select('*')
			->from('FormBlockSgbv')
			//->whereIn("ipid", $ipids)
			->whereIn('contact_form_id', $contact_forms)
			->andWhere('action_value = "1"')
			->andWhere('isdelete = "0"')
			->andWhere('unpaid = "0"');
		
			$actions_res = $actions_q->fetchArray();
		
			if($actions_res)
			{
				return $actions_res;
			}
			else
			{
				return false;
			}
		}
		//--

	}

?>