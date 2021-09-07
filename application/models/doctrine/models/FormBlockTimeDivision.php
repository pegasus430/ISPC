<?php
	Doctrine_Manager::getInstance()->bindComponent('FormBlockTimeDivision', 'MDAT');
	
	class FormBlockTimeDivision extends BaseFormBlockTimeDivision {
		
		public function getPatientFormBlockTimeDivision($ipid, $contact_form_id, $allow_deleted = false, $grouped = false)
		{
			$groups_sql = Doctrine_Query::create()
				->select('*')
				->from('FormBlockTimeDivision')
				->Where("contact_form_id = ?", $contact_form_id )
				->andWhere("ipid = ?", $ipid);				
			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}
			$groupsarray = $groups_sql->fetchArray();

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

				return $user_array;
			}
		}
		
	}
	
	