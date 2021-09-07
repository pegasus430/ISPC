<?php
	Doctrine_Manager::getInstance()->bindComponent('FormBlockTracheostomy', 'MDAT');
	
	class FormBlockTracheostomy extends BaseFormBlockTracheostomy {
		
		public function getPatientFormBlockTracheostomy($ipid, $contact_form_id = false, $allow_deleted = false, $grouped = false, $limit = false)
		{
			$groups_sql = Doctrine_Query::create()
			->select('*')
			->from('FormBlockTracheostomy')
			->Where("ipid = '". $ipid ."'");
			if($contact_form_id !== false){
				$groups_sql->andWhere("contact_form_id = ?" , $contact_form_id);
			}else{
				$groups_sql->orderBy("id DESC");
			}
			if($limit !== false){
				$groups_sql->limit((int)$limit);
				
			}
						
			if(!$allow_deleted)
			{
				$groups_sql->andWhere('isdelete = 0');
			}
			//echo $groups_sql->getSqlQuery();die();
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
	
	
?>
	