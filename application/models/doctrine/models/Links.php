<?php

	Doctrine_Manager::getInstance()->bindComponent('Links', 'SYSDAT');

	class Links extends BaseLinks {

		public function getlinkmenu($id)
		{
			
		}

		public static function checkLinkPermission()
		{
			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
			$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			$link2check = strtolower($controller . '/' . $action);
				
			if ($link2check == "usersessions/checknew") {
				return true;
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_id = $logininfo->userid;
			$client_id = $logininfo->clientid;
			$master_group_id = Usergroup::getMasterGroup($logininfo->groupid);

			
			//ISPC-2612 Ancuta 26.06.2020 
			//check in client is a child - if child do not allow to see list
			$lists = Pms_CommonData::connection_lists();
			$conection2check = "";
			foreach($lists as $model => $list_data){
			    if($list_data['link'] == $link2check){
			        $conection2check = $model;
			    }
			}
			
		    if($link2check == 'specialists/specialiststypes'){
		        $conection2check ="Specialists";
		    }
			if($conection2check){
			    
			    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower($conection2check,$client_id);
			    if($client_is_follower){
  			        return false; // A follower to a connection it is not allowed to enter the synced list 
			    }
			}
            // --			
			
			if($logininfo->usertype == 'SA')
			{
				return true; //sadmin does/sees everything, the Master of the Universe
			}

			
			$post = Zend_Controller_Front::getInstance()->getRequest()->isPost();
			

			$link = Doctrine_Query::create()
				->select("*")
				->from('Links')
				->where("link= ?", $link2check);
			$link_det = $link->fetchArray();
			$link_det = $link_det[0];

			if($link_det)
			{
				if($link_det['isffa'] == 1 || $link_det['ispatientonly'] == 1)
				{
					return true; //free for all links, patient permissions are handled elsewhere
				}
				else
				{
					if($link_det['issadmin'] == 1)
					{
						return false; //bail out non-sadmins from sadmins links
					}
					elseif($logininfo->usertype == 'CA')
					{
						return true; //cadmin does/sees everything client-related, an Apprentice Master of the Universe
					}
					else
					{
						//mere mortals here
						$user_perm = UserDefaultPermissions::getPermissionsByUserAndClientSystem($user_id, $client_id);

						if(!empty($user_perm))
						{
							if(array_key_exists($link_det['menu'], $user_perm))
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
							$group_perm = GroupDefaultPermissions::getPermissionsByGroupAndClientSystem($master_group_id, $client_id);
							if(array_key_exists($link_det['menu'], $group_perm))
							{
								return true;
							}
							else
							{
								return false;
							}
						}
					}
				}
			}
			else
			{
				return true;
			}
		}

		public function checkLinkActionsPermission()
		{
			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
			$action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			$link2check = strtolower($controller . '/' . $action);
			
			if ($link2check == "usersessions/checknew") {
				return true;
			}

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$user_id = $logininfo->userid;
			$client_id = $logininfo->clientid;
			$master_group_id = Usergroup::getMasterGroup($logininfo->groupid);

			if($logininfo->usertype == 'SA')
			{
				return true; //sadmin does/sees everything, the Master of the Universe
			}

			$post = Zend_Controller_Front::getInstance()->getRequest()->isPost();

			

			$link = Doctrine_Query::create()
				->select("*")
				->from('Links')
				->where("link= ?" , $link2check );
			$link_det = $link->fetchArray();
			$link_det = $link_det[0];

			if($link_det)
			{
				if($link_det['isffa'] == 1 || $link_det['ispatientonly'] == 1)
				{
					return true; //free for all links, patient permissions are handled elsewhere
				}
				else
				{
					if($link_det['issadmin'] == 1)
					{
						return false; //bail out non-sadmins from sadmins links
					}
					elseif($logininfo->usertype == 'CA')
					{
						return true; //cadmin does/sees everything client-related, an Apprentice Master of the Universe
					}
					else
					{
						//mere mortals here
						$user_perm = UserDefaultPermissions::getPermissionsByUserAndClientSystem($user_id, $client_id);
						if(!empty($user_perm))
						{
							if(array_key_exists($link_det['menu'], $user_perm))
							{
								$user_actions_perm = UserDefaultPermissions::verifyPermissionByUserAndClient($user_id, $client_id, $link_det['menu'], $permission = 'canedit');
								if(!$user_actions_perm || empty($user_actions_perm))
								{
									return false;
								}
								else
								{
									return true;
								}
							}
							else
							{
								return false;
							}
						}
						else
						{
							$group_perm = GroupDefaultPermissions::getPermissionsByGroupAndClientSystem($master_group_id, $client_id);
							if(array_key_exists($link_det['menu'], $group_perm))
							{
								$group_actions_perm = GroupDefaultPermissions::verifyPermissionByGroupAndClient($master_group_id, $client_id, $link_det['menu'], $permission = 'canedit', $perm = 'menu_id');
//								var_Dump("group permission");
//								var_Dump($group_actions_perm );exit;
								if(!$group_actions_perm || empty($group_actions_perm))
								{
									return false;
								}
								else
								{
									return true;
								}
							}
							else
							{
								return false;
							}
						}
					}
				}
			}
			else
			{
				return true;
			}
		}

		
		
		public function assert_links_exists ( $link = '', $menuid = false ) 
		{
			if (empty($link)) {
				return ;
			}

			$q = Doctrine_Query::create()
			->select("id")
			->from('Links')
			->where("link = ?" , $link );
			if ( $menuid !== false ) {
				$q->andWhere("menu = ? ", $menuid);	
			}
			
			//Pms_DoctrineUtil::get_raw_sql($q, true);
			
			$link_det = $q->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

			if ($link_det && $link_det['id'] > 0) {
				return true; // link exist
			} else {
				return false;// link missing exist
			}
			
		}
	}

?>