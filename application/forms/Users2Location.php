<?php

require_once("Pms/Form.php");

class Application_Form_Users2Location extends Pms_Form {

	public function assign_user($post)
	{
			if($post['leader'] == "1"){
				$this->remove_leader($post);
			}
			
		$user2loc = new Users2Location();
		$user2loc->client = $post['client'] ;
		$user2loc->user = $post['user'];
		$user2loc->location = $post['location'];
		$user2loc->leader = $post['leader'];
		$user2loc->save();
		
		return true;
	}
	public function update_leader($post)
	{
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$user_id = $logininfo->userid;
		
		if(!empty($post['location']) && !empty($post['user'])){

			if($post['leader'] == "1"){
				$this->remove_leader($post);
			}
			
			$update = Doctrine_Query::create()
			->update("Users2Location")
			->set('leader', '1')
			
			->set('change_date', '"'.date('Y-m-d H:i:s').'"')
			->set('change_user', $user_id)
			
			->where('user ="' . $post['user'] . '"')
			->andWhere('location = "'.$post['location'].'"')
			->andWhere('client = "'.$post['client'].'"')
			->andWhere('isdelete = "0"');
			$update->execute();
			
			if($update)
			{
				return true;
			}
			else
			{
				return false;
			}
		} 
		else
		{
			return false;
		}
	}

	
	public function remove_leader($post)
	{
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$user_id = $logininfo->userid;
		
		if(!empty($post['location']) ){
			
			$update = Doctrine_Query::create()
			->update("Users2Location")
			->set('leader', '0')
			
			->set('change_date', '"'.date('Y-m-d H:i:s').'"')
			->set('change_user', $user_id)
			
			->where('location = "'.$post['location'].'"')
			->andWhere('client = "'.$post['client'].'"')
			->andWhere('isdelete = "0"');
			$update->execute();
			
			if($update)
			{
				return true;
			}
			else
			{
				return false;
			}
		} 
		else
		{
			return false;
		}
		
	}

	
	public function remove_user($post)
	{

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$user_id = $logininfo->userid;
		
		if(!empty($post['location']) && !empty($post['user'])){
			$update = Doctrine_Query::create()
			->update("Users2Location")
			->set('isdelete', '1')
			
			->set('change_date', '"'.date('Y-m-d H:i:s').'"')
			->set('change_user', $user_id)
				
			->where('user ="' . $post['user'] . '"')
			->andWhere('location = "'.$post['location'].'"')
			->andWhere('client = "'.$post['client'].'"')
			->andWhere('isdelete = "0"');
			$update->execute();
	
			if($update)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
}
?>