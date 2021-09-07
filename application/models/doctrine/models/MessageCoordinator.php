<?php
Doctrine_Manager::getInstance()->bindComponent('MessageCoordinator', 'MDAT');

class MessageCoordinator extends BaseMessageCoordinator {

	public function get_message_coordinator_data($ipid)
	{
		$selector = Doctrine_Query::create()
		->select('*')
		->from('MessageCoordinator')
		->where('isdelete="0"')
		->andWhere('ipid = "' . $ipid . '"');
		$selector_res = $selector->fetchArray();
		
		if($selector_res)
		{
			return $selector_res[0];
		}
		else
		{
			return false;
		}
	}

}
