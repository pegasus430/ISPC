<?php

require_once("Pms/Form.php");

class Application_Form_UsersAssociation extends Pms_Form {

	public function add_association($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$user_id = $logininfo->userid;
		$clientid = $logininfo->clientid;


		$clear_data = $this->clear_data($user_id, $clientid);
		if($clear_data)
		{
			if($post['associated_user'] != '0')
			{
				$user2adm = new UsersAssociation();
				$user2adm->user = $user_id;
				$user2adm->client = $clientid;
				$user2adm->associate = $post['associated_user'];
				$user2adm->save();
			}
		}

		return true;
	}

	public function add_association_useredit($post, $clientid = false, $user_id = false)
	{
		if($clientid && $user_id)
		{
			$clear_data = $this->clear_data($user_id, $clientid);
			if($clear_data)
			{
				if($post['associated_user'] != '0')
				{
					$user2adm = new UsersAssociation();
					$user2adm->user = $user_id;
					$user2adm->client = $clientid;
					$user2adm->associate = $post['associated_user'];
					$user2adm->save();
				}
			}

			return true;
		}
	}

	public function clear_data($user_id, $client)
	{
		if(strlen($user_id) > 0 && strlen($client) > 0)
		{
			$Q = Doctrine_Query::create()
			->delete('UsersAssociation')
			->where("client='" . $client . "'")
			->andWhere("user='" . $user_id . "'");
			$Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}

}

?>