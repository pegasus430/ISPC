<?php

	Doctrine_Manager::getInstance()->bindComponent('Menus', 'SYSDAT');

	class Menus extends BaseMenus {

		public function getPosition()
		{
			$posarray = array();
			$posarray = array('' => 'select position', '0' => 'Top Menu', '1' => 'Left Menu');
			return $posarray;
		}

		public function getWinOption()
		{
			$winarray = array();
			$winarray = array('0' => 'Same Window', '1' => 'New Window');
			return $winarray;
		}

		public function getMenus()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('Menus')
				->where('isdelete = ?', 0)
				->andWhere('parent_id=0')
				->andWhere('left_position=1');
			$menuarray = $fdoc->fetchArray(); // proper way

			$menus = array();
			$menus = array("0" => "Select Parent");

			foreach($menuarray as $menu)
			{
				$menus[$menu[id]] = $menu[menu_title];
			}
			return $menus;
		}

		/**
		 * @cla on 19.09.2018
		 * + $user on 19.09.2018, so we can fetch for any user
		 * + static
		 * + $custom_ids, so we can fetch any id from Menus not just with top_position =1, as lons as you are allowed to it 
		 * 
		 * $pos & $adm ... because why not
		 *  
		 * @param string $pos
		 * @param string $adm
		 * @param User $user
		 * @param array $custom_ids
		 * @return Ambigous <multitype:, Doctrine_Collection>
		 */
		public static function getTopParentMenus($pos = null, $adm = null, $user = null, $custom_ids = null)
		{
		    
		    if (empty($user) || ! ($user instanceof User)) {
		    
    			$logininfo   = new Zend_Session_Namespace('Login_Info');
    			$clientid    = $logininfo->clientid;
    			$user_id     = $logininfo->userid;
    			$group_id    = $logininfo->groupid;
		        $usertype    = $logininfo->usertype;
		         
		    } else {
		    
		        $clientid     = $user->clientid;
		        $user_id      = $user->id;
		        $group_id     = $user->groupid;
		        $usertype     = $user->usertype;
		    }
		    
			$mnc = new MenuClient();
			$qsd = $mnc->getMenusByClient($clientid);

			if($usertype != "SA" && $usertype != "CA")
			{
    			$master_group_id = Usergroup::getMasterGroup($group_id);
    			
    			$chkclient = 'id in (' . $qsd . ')';
				
				$user_perm = UserDefaultPermissions::getPermissionsByUserAndClientSystem($user_id, $clientid);

				if( ! empty($user_perm)) {
				    $user_menus = array_keys($user_perm);
				} else {	
				    $group_perm = GroupDefaultPermissions::getPermissionsByGroupAndClientSystem($master_group_id, $clientid);
					$user_menus = array_keys($group_perm);
				}
				
				if ( ! empty($custom_ids) && is_array($custom_ids)) {
				    $top_position_sql = '1';
				    $user_menus = array_intersect($custom_ids, $user_menus);
				} else {
				    $top_position_sql = 'top_position=1';
				}
				
				$user_menus = ! empty($user_menus) ? implode(',', $user_menus) : '0'; 
				
				$user_menus_sql = 'id in (' . $user_menus . ')';
			} 
			elseif ( $usertype == "CA") {
			    
			    $chkclient = 'id in (' . $qsd . ')';

			    if ( ! empty($custom_ids) && is_array($custom_ids)) {
			        $top_position_sql = '1';
			        $user_menus = $custom_ids;
			        
			        $user_menus = ! empty($user_menus) ? implode(',', $user_menus) : '0';
			         
			        $user_menus_sql = 'id in (' . $user_menus . ')';
			        
			    } else {
			        $top_position_sql = 'top_position=1';
			        $user_menus_sql = 1;
			    }
			    
			}
			else
			{
				$chkclient = 1;
				$user_menus_sql = 1;
				$top_position_sql = 'top_position=1';
			}

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('Menus')
				->where('isdelete = 0')
				
				->andWhere($top_position_sql)
				
				->andWhere($chkclient)
				->andWhere($user_menus_sql)
				
				->orderBy('top_position DESC, sortorder_top Asc');
			$menuarray = $fdoc->fetchArray();


			return $menuarray;
		}

		
		
		/**
		 * @cla on 19.09.2018
		 * + $user on 19.09.2018, so we can fetch for any user
		 * + static
		 * 
		 *  
		 * @param string $adm
		 * @param User $user
		 * @return Ambigous <multitype:, Doctrine_Collection>
		 */
		public static function getLeftParentMenus($adm = null, $user = null)
		{
		    if (empty($user) || ! ($user instanceof User)) {
		        
    			$logininfo   = new Zend_Session_Namespace('Login_Info');
    			$clientid    = $logininfo->clientid;
    			$user_id     = $logininfo->userid;
    			$group_id    = $logininfo->groupid;
    			$usertype    = $logininfo->usertype;
    			
		    } else {
		        
		        $clientid     = $user->clientid;
		        $user_id      = $user->id;
		        $group_id     = $user->groupid;
		        $usertype     = $user->usertype;
		        
		        if (is_null($adm)) {
		            if($usertype == 'SA') {
		                $adm = 1;
		            } elseif($usertype == 'CA' || $usertype == 'SCA') {
		                $adm = 2;
		            } else {
		                $adm = 0;
		            }
		        }
		    }
		    
		    if (empty($user_id)){
		        return; //fail-safe
		    }
		    
		    
			$master_group_id = Usergroup::getMasterGroup($group_id);
			$mnc = new MenuClient();
			$qsd = $mnc->getMenusByClient($clientid);
				
			if($usertype != "SA")
			{
				$chkclient = 'id in (' . $qsd . ')';
			}
			else
			{
				$chkclient = 1;
			}

			if($adm == 1)
			{
				$adms = "forsuperadmin = 1 or foradmin = 1 or foradmin = 0";
			}
			else if($adm == 2)
			{
				$adms = "foradmin = 1 or foradmin = 0 and forsuperadmin != 1";
			}
			else
			{
				$user_perm = UserDefaultPermissions::getPermissionsByUserAndClientSystem($user_id, $clientid);

				if(!empty($user_perm))
				{
					$user_menus = implode(',', array_keys($user_perm));
				}
				else
				{
					$group_perm = GroupDefaultPermissions::getPermissionsByGroupAndClientSystem($master_group_id, $clientid);
					$user_menus = implode(',', array_keys($group_perm));
				}

				if(empty($user_menus))
				{
					$user_menus = '0';
				}

//				$adms = "foradmin = 0 and forsuperadmin = 0 and id in (" . $user_menus . ")";
				$adms = "forsuperadmin = 0 and id in (" . $user_menus . ")";
			}
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('Menus')
				->where('isdelete = 0')
				->andWhere('left_position=1')
				->andWhere('parent_id=0')
				->andWhere($chkclient)
				->andWhere($adms)
				->orderBy('sortorder Asc');
			$menuarray = $fdoc->fetchArray(); // proper way

			if(!$menuarray)
			{
				//PatientPermissions::LogRightsError(2,$fdoc->getSqlQuery());
			}

			//var_dump($menuarray);


			return $menuarray;
		}

		public function getLeftSubMenus($partid)
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$user_id = $logininfo->userid;
			$master_group_id = Usergroup::getMasterGroup($logininfo->groupid);

			$mnc = Doctrine_Query::create()
				->select('menu_id as menus_id')
				->from('MenuClient')
				->where('clientid=' . $clientid . '');
			$qsd = $mnc->getDql();
			//		}

			if($logininfo->usertype != "SA")
			{
				$chkclient = 'id in (' . $qsd . ')';
			}
			else
			{
				$chkclient = "1";
			}

			if($logininfo->usertype == 'SA')
			{
				$adms = "forsuperadmin = 1 or foradmin = 1 or foradmin = 0";
			}
			else if($logininfo->usertype == 'CA')
			{
				$adms = "foradmin = 1 or foradmin = 0 and forsuperadmin != 1";
			}
			else
			{
				$user_perm = UserDefaultPermissions::getPermissionsByUserAndClientSystem($user_id, $clientid);

				if(!empty($user_perm))
				{
					$user_menus = implode(',', array_keys($user_perm));
				}
				else
				{
					$group_perm = GroupDefaultPermissions::getPermissionsByGroupAndClientSystem($master_group_id, $clientid);
					$user_menus = implode(',', array_keys($group_perm));
				}

				if(empty($user_menus))
				{
					$user_menus = '0';
				}
//				$adms = "foradmin = 0 and forsuperadmin = 0 and id in (" . $user_menus . ")";
				$adms = "forsuperadmin = 0 and id in (" . $user_menus . ")";
			}
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('Menus')
				->where('isdelete = 0')
				->andWhere('left_position=1')
				->andWhere('parent_id=' . $partid)
				->andWhere($chkclient)
				->andWhere($adms)
				->orderBy('sortorder Asc');
			$menuarray = $fdoc->fetchArray();
			//var_dump($menuarray);
			
			if(!$menuarray)
			{
			}
			if(count($menuarray) > 0)
			{
				return $menuarray;
			}
		}

		/**
		 * @cla on 19.09.2018
		 * + $user on 19.09.2018, so we can fetch for any user
		 * + static
		 * 
		 * 
		 * @param User $user
		 * @return void|Ambigous <multitype:, Doctrine_Collection>
		 */
		public static function getAllLeftSubMenus($user = null)
		{   
		    if (empty($user) || ! ($user instanceof User)) {
		    
		        $logininfo   = new Zend_Session_Namespace('Login_Info');
		        $clientid    = $logininfo->clientid;
		        $user_id     = $logininfo->userid;
		        $group_id    = $logininfo->groupid;
		        $usertype    = $logininfo->usertype;
		         
		    } else {
		    
		        $clientid     = $user->clientid;
		        $user_id      = $user->id;
		        $group_id     = $user->groupid;
		        $usertype     = $user->usertype;
		    }
		    
		    if (empty($user_id)){
		        return; //fail-safe
		    }
		    
			$master_group_id = Usergroup::getMasterGroup($group_id);

			$mnc = Doctrine_Query::create()
				->select('mc.menu_id')
				->from('MenuClient mc')
				->where('mc.clientid="' . $clientid . '"');
			$qsd = $mnc->getDql();
//			print_r($qsd);
//			exit;
			if($logininfo->usertype != "SA")
			{
				$chkclient = 'm.id in (' . $qsd . ')';
			}
			else
			{
				$chkclient = "1";
			}

			if($logininfo->usertype == 'SA')
			{
				$adms = "m.forsuperadmin = 1 or m.foradmin = 1 or m.foradmin = 0";
			}
			else if($logininfo->usertype == 'CA')
			{
				$adms = "m.foradmin = 1 or m.foradmin = 0 and m.forsuperadmin != 1";
			}
			else
			{
				$user_perm = UserDefaultPermissions::getPermissionsByUserAndClientSystem($user_id, $clientid);

				if(!empty($user_perm))
				{
					$user_menus = implode(',', array_keys($user_perm));
				}
				else
				{
					$group_perm = GroupDefaultPermissions::getPermissionsByGroupAndClientSystem($master_group_id, $clientid);
					$user_menus = implode(',', array_keys($group_perm));
				}

				if(empty($user_menus))
				{
					$user_menus = '0';
				}
//				$adms = "foradmin = 0 and forsuperadmin = 0 and id in (" . $user_menus . ")";
				$adms = "forsuperadmin = 0 and id in (" . $user_menus . ")";
			}
			$fdoc = Doctrine_Query::create()
				->select('*, menu_link as menu_link')
				->from('Menus m')
				->where('m.isdelete = 0')
				->andWhere('m.left_position=1')
				->andWhere('m.parent_id != 0')
				->andWhere($chkclient)
				->andWhere($adms)
				->orderBy('m.sortorder Asc');
			$menuarray = $fdoc->fetchArray(); // proper way

			if(count($menuarray) > 0)
			{
				foreach($menuarray as $menu_item)
				{
					$allmenuarr[$menu_item['parent_id']][] = $menu_item;
				}
				return $allmenuarr;
			}
		}

		public function getMenubyLink($link)
		{

			$folder = Doctrine_Query::create()
				->select('*')
				->from('Menus')
				->where("menu_link='" . $link . "'");
			$folderexec = $folder->execute();
			if($folderexec)
			{
				$folderarray = $folderexec->toArray();
				
				return $folderarray;
			}
		}

		public function getMenuLinkbyIds( $ids = array())
		{
			if (empty($ids) || ! is_array($ids)) {
				return ;
			}
		
			$fdoc = Doctrine_Query::create()
			->select('id, menu_link')
			->from('Menus')
			->where('isdelete = ?', 0)
			->andWhereIn('id', $ids);
			
			$menuarray = $fdoc->fetchArray(); 

			return $menuarray;
		}
	}
?>