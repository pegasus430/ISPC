<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockHospizimex', 'MDAT');

	class FormBlockHospizimex extends BaseFormBlockHospizimex {

		public function getPatientFormBlockHospizimex($ipid, $contact_form_id, $allow_deleted = false)
		{

			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockHospizimex')
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

		public function get_multiple_FormBlockHospizimex($ipid, $contact_forms_ids)
		{

			$contact_forms_ids[] = '999999999';

			$block_data = Doctrine_Query::create()
				->select('*')
				->from('FormBlockHospizimex')
				->where('ipid LIKE "' . $ipid . '"')
				->andWhereIn('contact_form_id', $contact_forms_ids)
				->andWhere('isdelete="0"');
			$block_data_res = $block_data->fetchArray();

			if($block_data_res)
			{
				foreach($block_data_res as $k_block_res => $v_block_res)
				{
					$block_data_arr[$v_block_res['contact_form_id']] = $v_block_res;
				}

				return $block_data_arr;
			}
			else
			{
				return false;
			}
		}

	}

?>