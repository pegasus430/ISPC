<?php

	Doctrine_Manager::getInstance()->bindComponent('TabMenus', 'SYSDAT');

	class TabMenus extends BaseTabMenus {

		public function getMenus()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($_GET['id'] > 0)
			{
				$clientcond = 'id!="' . $_GET['id'] . '"';
			}
			else
			{
				$clientcond = 1;
			}

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('TabMenus')
				->where('isdelete = ?', 0)
				->andWhere($clientcond)
				->orderBy('sortorder ASC');
			$menuarray = $fdoc->fetchArray(); // proper way

			$menus = array();
			$menus = array("0" => "Select Parent");

			foreach($menuarray as $menu)
			{
				$menus[$menu[id]] = $menu[menu_title];
			}
			return $menus;
		}

		public function getMenuTabs( $returnHtmlAndArray = false )
		{

			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$userid = $logininfo->userid;
			$groupid = $logininfo->groupid;

			$decid = Pms_Uuid::decrypt($_REQUEST['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			/*
			 * @cla removed
			$epid = Pms_CommonData::getEpid($ipid);
			*/

			$pt = Doctrine_Query::create()
				->select('ishospiz, isstandby, isstandbydelete')
				->from('PatientMaster')
				->where('ipid = ? ', $ipid );
			$patient_status_array = $pt->fetchArray();

			/* HOSPIZ TAB HACK */
			$hospizperms = Pms_CommonData::getHospizMenus();

			if($patient_status_array[0]['ishospiz'] == '1')
			{
				$hospizsql = '';
			}
			else
			{
				$hospizsql = ' AND menu_id NOT IN (' . implode(',', $hospizperms['patientishospizonly']) . ')';
			}
			/* HOSPIZ TAB HACK END */

			$standbymenus = Pms_CommonData::getStandbyDisabledMenus();
			if($patient_status_array[0]['isstandby'] == '1' || $patient_status_array[0]['isstandbydelete'] == '1')
			{
				$standbysql = ' AND menu_id NOT IN (' . implode(',', $standbymenus['disabled_standby']) . ')';
			}
			else
			{
				$standbysql = '';
			}

			/* USER PERMISSIONS */
			$iskeyuser = PatientUsers::checkKeyUserPatient($userid, $ipid);

			if($logininfo->usertype != 'SA' && $logininfo->usertype != 'CA' && !$iskeyuser)
			{
				$user_perms = PatientPermissions::getPatientPermisionsAll($userid, $ipid, false);
				if(!$user_perms)
				{

					$user_det = User::getUserDetails($userid);
					$group_id = Usergroup::getMasterGroup($user_det[0]['groupid']);

					$user_perms = PatientGroupPermissions::getPatientGroupPermisionsAll($group_id, $ipid, false);

					if(!$user_perms)
					{

						$user_perms = GroupDefaultPermissions::getPermissionsByGroupAndClientAll($group_id, $user_det[0]['clientid']);
					}
				}
				$user_perms[999999] = 999999; // yafp .. why use an if when you can do a sql query..
				$permsql = ' AND menu_id IN (' . implode(',', $user_perms) . ')';
			}
			else
			{
				$permsql = '';
			}
			/* USER PERMISSIONS END */

			
			// ISPC-1345 Remove hospiz group hack 150526
			$hospizsql= "";
			
// 			if($logininfo->hospiz != 1 || $logininfo->clientid == 32)
// 			{
				$tcs = Doctrine_Query::create()
					->select('*')
					->from('TabMenuClient')
					->andWhere('clientid="' . $clientid . '"' . $hospizsql . $standbysql . $permsql);
				$tbm = $tcs->execute();
				if($tbm)
				{
					$tabmarr = $tbm->toArray();
					$comma = ",";
					$ipidval = "'0'";
					$ipidval_arr[] = "999999999";// yafp .. why use an if when you can do a sql query..
					if(is_array($tabmarr))
					{
						foreach($tabmarr as $key => $val)
						{
							$ipidval .= $comma . "'" . $val['menu_id'] . "'";
							$comma = ",";
							
							$ipidval_arr[] = $val['menu_id'];
						}
					}
				}
// 			}
// 			else
// 			{
// 				$hospizperms = Pms_CommonData::getHospizMenus();
// 				$ipidval = implode(',', $hospizperms['patient']);
// 				$ipidval_arr = array_values(array_unique($hospizperms['patient']));
// 				$ipidval_arr[] = '99999999999999';
// 			}

			//get all allowed menus (parents and childs)
			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('TabMenus')
				->where('isdelete = "0"')
//				->andWhere('parent_id = 0')
				->andWhereIn("id", $ipidval_arr) // yafp .. dev glitched and forgot the 99999.. but if error he will add 999999
				->andWhere('efa_menu = 0')//ISPC-2827 Ancuta 06.05.2021
				->orderBy('sortorder ASC');
			$menuarray = $fdoc->fetchArray(); // proper way

			foreach($menuarray as $k_menu=>$v_menu)
			{
				if($v_menu['parent_id'] == '0')
				{
					$first_menu[$v_menu['id']] = $v_menu;
				}				
				elseif($v_menu['parent_id']>'0')
				{
					$second_menu[$v_menu['parent_id']][] = $v_menu;
				}
			}
			/* ISPC-2712 Ancuta 12.11.2020 */
			$controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
			$actionname = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			$current_page = $controller.'/'.$actionname;
			//--		
			
			$grid = new Pms_Grid($first_menu, 1, count($first_menu), "tabmenus.html");
			$grid->second_menu = $second_menu;
			$grid->current_page = $current_page;/* ISPC-2712 Ancuta 12.11.2020 */
			$menugrid = $grid->renderGrid();
			
			$ptarray['tabmenus'] = $menugrid;
			$tabmenus = Pms_Template::createTemplate($ptarray, 'templates/tabmenus.html');

			if ($returnHtmlAndArray === true) {
			    //this is used by device=mobile
			    return [
			        'html' => $tabmenus,
			        'first_menu' => $first_menu,
			        'second_menu' => $second_menu,  
			    ];
			} else {
			    
    			return $tabmenus;
			}
		}

// 		flowerpower
// 		public function getMenubyLink($link)
// 		{
// 			$logininfo = new Zend_Session_Namespace('Login_Info');
// 			$clientid = $logininfo->clientid;

// 			$folder = Doctrine_Query::create()
// 				->select('*')
// 				->from('TabMenus')
// 				->Where("isdelete = 0");
// 			if( ! is_array($link))
// 			{
// 				$folder->andWhere("menu_link= ? " , $link);
// 			}
// 			else
// 			{
// 				$folder->andWhereIn("menu_link", $link);
// 			}

// //		New version
// 			$folder_arr = $folder->fetchArray();

// // 			flowerpower
// 			$menu_ids[] = '9999999999';
// 			foreach($folder_arr as $k_link => $v_link)
// 			{
// 				$menu_ids[] = $v_link['id'];
// 			}

// 			$client_menu = Doctrine_Query::create()
// 				->select('*, id as tmc_id, menu_id as id') //output menu_id as id which is used to check for permissions
// 				->from('TabMenuClient')
// 				->where('clientid="' . $clientid . '"')
// 				->andWhereIn('menu_id', $menu_ids);
// 			$client_menu_arr = $client_menu->fetchArray();

// 			if(count($client_menu_arr) > '0')
// 			{
// 				return $client_menu_arr;
// 			}
// 			else
// 			{
// 				return false;
// 			}
// 		}
		
		//flowerpower rewrite
		public static function getMenubyLink( $link = array() ) {
			
			if ( empty($link)) {
				return false; //force exit
			}

			if( ! is_array($link)) {
				$link = array($link); //if you send a object you will breakit
			}
			
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$client_menu = Doctrine_Query::create()
				->select('*, id as tmc_id, menu_id as id,tm.menu_link')//output menu_id as id which is used to check for permissions
				->from('TabMenuClient tmc')
				->where("tmc.clientid = ? ", $clientid)
				->innerJoin("tmc.TabMenus tm ON (tmc.menu_id = tm.id AND tm.isdelete = 0)")
				->andWhereIn('tm.menu_link', $link)
				->fetchArray();		
			if(count($client_menu) > 0) {
				return $client_menu;
			}
			else {
				return false;
			}
			
		}

		public function get_menus_details($menu_ids, $get_parent = false)
		{
			$menu = Doctrine_Query::create()
				->select('*')
				->from('TabMenus')
				->Where("isdelete = 0");
			if(!is_array($menu_ids))
			{
				$menu->andWhere("id='" . $menu_ids . "'");
			}
			else
			{
				$menu->andWhereIn("id", $menu_ids);
			}

			$menu_arr = $menu->fetchArray();

			if($menu_arr)
			{
				if($get_parent)
				{
					$parent_ids[] = '99999999999';
					foreach($menu_arr as $k_menus => $v_menus)
					{
						$parent_ids[] = $v_menus['parent_id'];
					}

					$p_menu = Doctrine_Query::create()
						->select('*')
						->from('TabMenus')
						->where('isdelete = "0"')
						->andWhereIn('id', $parent_ids);
					$p_menu_res = $p_menu->fetchArray();

					foreach($p_menu_res as $k_parent => $v_parent)
					{
						$parents_arr[$v_parent['id']] = $v_parent;
					}

					foreach($menu_arr as $k_menu => $v_menu)
					{

						$childrens[$v_menu['parent_id']][] = $v_menu;
						$menus['parents'][$v_menu['parent_id']] = $parents_arr[$v_menu['parent_id']];
						$menus['parents'][$v_menu['parent_id']]['childrens'] = $childrens[$v_menu['parent_id']];
					}
				}
				else
				{
					foreach($menu_arr as $k_menu => $v_menu)
					{
						$menus[] = $v_menu;
					}
				}

				return $menus;
			}
			else
			{
				return false;
			}
		}

	}

?>