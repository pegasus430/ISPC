<?php
Doctrine_Manager::getInstance()->bindComponent('BayEmergencyPlan', 'MDAT');

class BayEmergencyPlan extends BaseBayEmergencyPlan 
{
	
	function get_bayemergencyplan_details($ipid)
	{
		$drop = Doctrine_Query::create()
		->select('*')
		->from('BayEmergencyPlan')
		->where("ipid='" . $ipid . "'")
		->andWhere("isdelete = 0");
		$droparray = $drop->fetchArray();
			
		return $droparray;
	}
}