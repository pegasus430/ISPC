<?php

	Doctrine_Manager::getInstance()->bindComponent('FormBlockAdditionalUsers', 'MDAT');

	class FormBlockAdditionalUsers extends BaseFormBlockAdditionalUsers {

		public function getPatientFormBlockAdditionalUsers($ipids, $contact_form_ids = false, $allow_deleted = false, $grouped = false)
		{
			if(!is_array($ipids))
			{
				$ipids = array($ipids);
			}
			
			if(empty($ipids))
			{
				return array();
			}
			
			if(!$contact_form_ids)
			{
				return array();
			}
			
			if(!is_array($contact_form_ids))
			{
				$contact_form_ids = array($contact_form_ids);
			}
			/*$contact_form_ids = array();
			if(is_array($contact_form_id))
			{
				$contact_form_ids = $contact_form_id;
			}
			else
			{
				$contact_form_ids = array($contact_form_id);
			}

			if(count($contact_form_ids) == '0')
			{
				$contact_form_ids[] = '9999999999';
			}

			if(is_array($ipid))
			{
				$ipids = $ipid;
			}
			else
			{
				$ipids = array($ipid);
			}*/

			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockAdditionalUsers')
				->whereIn("ipid", $ipids)
				->andWhereIn('contact_form_id', $contact_form_ids);
			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}

			$groupsarray = $groups_sql->fetchArray();
			
			$user_array = array();
			if($groupsarray)
			{
				foreach($groupsarray as $key => $action_details)
				{
					if($grouped)
					{
						$user_array[$action_details['contact_form_id']][] = $action_details;
					}
					else
					{
						$user_array[] = $action_details;
					}
				}
			}

			return $user_array;
			
		}

	}

?>