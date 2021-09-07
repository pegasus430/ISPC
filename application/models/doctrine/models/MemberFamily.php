<?php

	Doctrine_Manager::getInstance()->bindComponent('MemberFamily', 'SYSDAT');

	class MemberFamily extends BaseMemberFamily {

		/*
		 * ::getMemberFamilyDetails(int)
		 * get all family members of one member_id
		 * 
		 * ::getMemberFamilyDetails(array(int))
		 * get all family members of all the members_ids in the array
		 */
		public function getMemberFamilyDetails($memberid = 0, $active = false){
				
				$usr = Doctrine_Query::create()
				->select('*')
				->from('MemberFamily')
				->where("type = 'family'")
				->andWhere('isdelete=0')
				->orderBy('id DESC');
				if(is_array($memberid)){
					$usr->andWhereIn("member_id", $memberid );
				}
				else{
					$usr->andwhere("member_id='" . $memberid . "'");
				}
				if($active)
				{
					$usr->andWhere('inactive=0');
				}
				$memberarr = $usr->fetchArray();
			
				foreach ($memberarr as $k=>$member){
					$member_array[$member['id']] = $member;
				}
					
				if($member_array)
				{
					return $member_array;
				}else return false;
				
			
		}
		
	
	}

?>