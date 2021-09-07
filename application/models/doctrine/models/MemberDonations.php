<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberDonations', 'SYSDAT');

	class MemberDonations extends BaseMemberDonations {

		public function get_donations_history($clientid, $members = null )
		{
		    
		    if($members){
		        if(is_array($members)){
		            $members_array_sql = $members; 
		        } else{
		            $members_array_sql = array($members); 
		        }
		    }
		    
			$medic = Doctrine_Query::create()
				->select("*,DATE_FORMAT(donation_date, '%d.%m.%Y') as donation_date ")
				->from('MemberDonations')
				->where("clientid = '" . $clientid . "'")
				->andWhere("isdelete='0'");
			if($members){
			    $medic->andWhereIn('member',$members_array_sql);
			}
			$medic->orderBy('donation_date ASC');
			$medics = $medic->fetchArray();

			if($medics)
			{
				return $medics;
			}
		}

		
	}

?>