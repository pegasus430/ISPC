<?php

	Doctrine_Manager::getInstance()->bindComponent('Member2Memberships', 'SYSDAT');

	class Member2Memberships extends BaseMember2Memberships {

		public function get_memberships_history($clientid = false, $members = false, $actual = false)
		{
		    
		    if($members){
		        if(is_array($members)){
		            $members_array_sql = $members; 
		        } else{
		            $members_array_sql = array($members); 
		        }
		    }
		    
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Member2Memberships')
				->Where("isdelete='0'");
				
			if($clientid !== false){
				$medic->andWhere("clientid = '" . $clientid . "'");
				
			}
			if($members){
			    $medic->andWhereIn('member',$members_array_sql);
			}
			if($actual){
			    $medic->andWhere('CURDATE() BETWEEN date(start_date) and date(end_date) OR end_date ="0000-00-00 00:00:00" ' );
			}
			$medic->orderBy('start_date ASC');
			$medics = $medic->fetchArray();

			if($medics)
			{
				return $medics;
			}else{
				return false;
			}
		}

		public function get_memberships_data($members,$connection_id,$exclud_deleted = true)
		{
		    
		    if($members){
		        if(is_array($members)){
		            $members_array_sql = $members; 
		        } else{
		            $members_array_sql = array($members); 
		        }
		    }
		    
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Member2Memberships')
				->where("id = '" . $connection_id . "'");
			if($exclud_deleted){
				$medic->andWhere("isdelete='0'");
			}
			if($members){
			    $medic->andWhereIn('member',$members_array_sql);
			}
			$medics = $medic->fetchArray();

			if($medics)
			{
				return $medics;
			}
		}
	}

?>