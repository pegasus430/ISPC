<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockDrivetimedoc', 'MDAT');

	class FormBlockDrivetimedoc extends BaseFormBlockDrivetimedoc {

		public function getPatientFormBlockDrivetimedoc($ipid = '', $contact_form_id = 0, $allow_deleted = false)
		{
		    if (empty($ipid) || empty($contact_form_id)) {
		        return false;
		    }
		    
			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockDrivetimedoc')
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
		
		public function get_contact_form($contact_form, $allow_deleted = false)
		{
			$select = Doctrine_Query::create()
			->select('*')
			->from('FormBlockDrivetimedoc')
			->where('id="' . $contact_form . '"');
			if(!$allow_deleted)
			{
				$select->andWhere('isdelete="0"');
			}
		
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

		public function get_multiple_FormBlockDrivetimedoc($ipid, $contact_forms_ids)
		{
		    
		    if (empty($ipid) || empty($contact_forms_ids)) {
		        return false;
		    }
		    
			//$contact_forms_ids[] = '999999999';

			$block_data = Doctrine_Query::create()
				->select('*')
				->from('FormBlockDrivetimedoc')
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
		
		
		
		public function get_multiple_block_drivetimedoc($ipid, $contact_forms_ids)
		{
		    if (empty($ipid) || empty($contact_forms_ids)) {
		        return false;
		    }
		
// 			$contact_forms_ids[] = '999999999';
			if(is_array($ipid))
			{
				$ipids_arr = $ipid;
			}
			else
			{
				$ipids_arr[] = $ipid;
			}
				
// 			$ipids_arr[] = '99999999';
				
			$block_data = Doctrine_Query::create()
			->select('*')
			->from('FormBlockDrivetimedoc')
			->andWhereIn('ipid', $ipids_arr)
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