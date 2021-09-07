<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockGoa', 'MDAT');

	class FormBlockGoa extends BaseFormBlockGoa {

		public function getPatientFormBlockGoa($ipid, $contact_form_id, $allow_deleted = false)
		{
			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockGoa')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhere('contact_form_id ="' . $contact_form_id . '"');
			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}

			$groupsarray = $groups_sql->fetchArray();

			if($groupsarray)
			{
				return $groupsarray;
			}
		}

		public function get_patients_form_block_goa($ipids, $contact_form_ids, $only_checked = false, $allow_deleted = false)
		{
			if(!is_array($ipids))
			{
				$ipids_array = array($ipids);
			}
			else
			{
				$ipids_array = $ipids;
			}

			if(!is_array($contact_form_ids))
			{
				$contact_form_ids_array = array($contact_form_ids);
			}
			else
			{
				$contact_form_ids_array = $contact_form_ids;
			}

			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockGoa')
				->whereIn("ipid", $ipids_array)
				->andWhereIn("contact_form_id", $contact_form_ids_array);
			if($only_checked === true)
			{
				$groups_sql->andWhere('action_value = 1');
			}

			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}

			$groupsarray = $groups_sql->fetchArray();

			if($groupsarray)
			{
				foreach($groupsarray as $key => $action_details)
				{
					$patient_actions[$action_details['ipid']][$action_details['contact_form_id']][] = $action_details['action_id'];
				}
				return $patient_actions;
			}
		}

	}

?>