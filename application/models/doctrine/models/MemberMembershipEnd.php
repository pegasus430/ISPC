<?php

Doctrine_Manager::getInstance()->bindComponent('MemberMembershipEnd', 'SYSDAT');

class MemberMembershipEnd extends BaseMemberMembershipEnd {
	 
	public static function get_list($client = 0 , $isdeleted = 0)
	{
	    
		$fdoc = Doctrine_Query::create()
		->select("*")
		->from("MemberMembershipEnd indexBy id")
		->where('clientid = ?', $client)
		->andWhere('isdelete = ?', $isdeleted)
		->fetchArray();
		
		
		return $fdoc;
	}
	
}