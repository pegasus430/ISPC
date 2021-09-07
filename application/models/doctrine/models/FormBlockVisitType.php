<?php
	
	Doctrine_Manager::getInstance()->bindComponent('FormBlockVisitType', 'MDAT');
	
	class FormBlockVisitType extends BaseFormBlockVisitType {
		
		
		public function getPatientFormBlockVisitType($ipid, $contact_form_id )
		{
			$vitalsigns = Doctrine_Query::create()
			 ->select('*')
			 ->from('FormBlockVisitType')
			 ->where('ipid = "' . $ipid . '"')
			 ->andWhere('contact_form_id = "' . $contact_form_id . '"')
			 ->andWhere('isdelete = "0"');
			$vt_array = $vitalsigns->fetchArray();
			
			if($vt_array)
			{
				return $vt_array;
			}
			 
		}
		
		

		public function getPatientFormBlockVisitType_multiple($ipid, $contact_form_id, $allow_deleted = false, $grouped = false)
		{
		    
// 		    print_R($ipid);
// 		    print_R($contact_form_ids);
		    
		    $contact_form_ids = array();
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
		    }
		
		    $groups_sql = Doctrine_Query::create()
		    ->select('*')
		    ->from('FormBlockVisitType')
		    ->whereIn("ipid", $ipids)
		    ->andWhereIn('contact_form_id', $contact_form_ids);
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
?>