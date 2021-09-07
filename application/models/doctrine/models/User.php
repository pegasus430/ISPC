<?php

Doctrine_Manager::getInstance()->bindComponent('User', 'SYSDAT');

class User extends BaseUser 
{

		public function getuserbyidsandGroupId($userval, $groupid, $isdrop)
		{
			if(is_array($groupid))
			{
				$groupids = $groupid;
			}
			else
			{
				$groupids = array($groupid);
			}

			$groupids[] = '9999999999999';
			$Tr = new Zend_View_Helper_Translate();

			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('id in(' . $userval . ')')
				->andWhereIn('groupid', $groupids)
				->orderby("last_name ASC");
			$dr = $usr->execute();

			if($dr)
			{
				$userarr = $dr->toArray();

				if($isdrop == 1)
				{
					$users = array("0" => $Tr->translate('select'));

					foreach($userarr as $user)
					{
						$users[$user['id']] = trim($user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name']);
					}
//					asort($users); //- Wrongfully added
					$users["-1"] = 'Anderer Arzt';
					$users["-2"] = 'Hausarzt';
					$users["-3"] = 'Facharzt';
					$users["-4"] = 'Krankenhaus'; //ISPC - 2284
					$users["-5"] = 'Selbstmedikation'; //ISPC-2329
				}
				else
				{
					foreach($userarr as $user)
					{
						$users[$user['id']] = $user;
					}
				}
				return $users;
			}
		}

		public function getUserByClientid($cid, $isdropdwn = 0, $sadmin = false, $exclude_inactive = true,$user_array = false)
		{
			$translate = new Zend_View_Helper_Translate();
			$usr = Doctrine_Query::create()
				->select('*')
				->from('User');
			
			if($sadmin)
			{
				//$usr->where('(clientid = "' . $cid . '" OR clientid=0) OR usertype = "SA"');
				$usr->where('clientid =? OR clientid = ? OR usertype = ?', array($cid, "0", "SA"));
			}
			else
			{
				$usr->where("clientid=?", $cid);
			}

			if($user_array){
				if(!is_array($user_array)){
					$user_array = array($user_array);
				}
				$usr->andWhereIn("id",$user_array);
			}
			
			$usr->andWhere("isdelete=0");
			
			if($exclude_inactive)
			{
    			$usr->andWhere("isactive=0");
			}	


			$usr->orderBy('last_name ASC');
//			print_r($usr->getSqlQuery());
			$userarr = $usr->fetchArray();

			if($isdropdwn == 1)
			{
				$usersS = array("0" => $translate->translate('select'));

				foreach($userarr as $user)
				{
					$usersS[$user['id']] = trim($user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name']);
				}

				$usersS["-1"] = 'Anderer Arzt';
				return $usersS;
			}
			elseif($isdropdwn == 2)
			{
				$users_select = array("0" => $translate->translate('select'));

				foreach($userarr as $user)
				{
					$users_select[$user['id']] = trim($user['user_title'] . " " . $user['last_name'] . ", " . $user['first_name']);
				}

				return $users_select;
			}
			elseif($isdropdwn == 3)
			{
				$users_select = array("0" => $translate->translate('select'));

				foreach($userarr as $user)
				{
					$users_select[$user['id']] = trim( $user['first_name'] . " " . $user['last_name']);
				}

				return $users_select;
			}
			else
			{
				return $userarr;
			}
		}

		public function get_client_users($cid, $isdropdwn = 0, $sadmin = false)
		{
			$translate = new Zend_View_Helper_Translate();
			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where("isdelete=0 and isactive=0");
			if($sadmin)
			{
				//$usr->andWhere('(clientid = "' . $cid . '" OR clientid=0) OR usertype = "SA"');
				$usr->andWhere('clientid = ? OR clientid = ? OR usertype = ?', array($cid, "0", "SA"));
			}
			else
			{
				$usr->andWhere("clientid=?", $cid);
			}
			$usr->orderBy('last_name ASC');
			$userarr = $usr->fetchArray();

			if($isdropdwn == 1)
			{
				foreach($userarr as $user)
				{
					$usersS[$user['id']] = $user['last_name'] . ", " . $user['first_name'];
				}
				return $usersS;
			}
			else
			{
				return $userarr;
			}
		}

		/*
		 * update @author claudiu 01.02.2018
		 * added join UserSettings(allways, *)
		 */
		public function getUserDetails($userid, $active = false)
		{
			$usr = Doctrine_Query::create()
				->select('u.*, us.*')
				->from('User u')
				->leftJoin("u.UserSettings us")
				->where("id = ?" , $userid )
				->andWhere('isdelete=0')
				->orderBy('last_name ASC');
			if($active)
			{
				$usr->andWhere('isactive=0');
			}
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				return $userarr;
			}
		}

		public function getMultipleUserDetails($userids, $deleted_diplicated=false,$clientid = false)
		{
		  
			if (empty($userids) || ! is_array($userids)) {
				return array();
			}
			
		    if($deleted_diplicated){
		        $dd_sql = " OR (isdelete = 1 AND duplicated_user != 0 )"; 
		    } else {
		        $dd_sql = " ";
		    }
		    
		    
			//$userids[] = '99999999999';
			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->whereIn("id", $userids)
				->andWhere('isdelete=0 '.$dd_sql.' ');
				if($clientid){
					$usr->andWhere('(clientid = "' . $clientid . '" OR clientid=0) OR usertype = "SA"');
				}
				$usr->orderBy('last_name ASC');
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				foreach($userarr as $user)
				{
					$userarray[$user['id']] = $user;
				}
				return $userarray;
			}
		}

		/**
		 * 
		 * Jul 7, 2017 @claudiu
		 * changed to remove the 9999999999
		 * 
		 * @param string|array $groupid
		 * @param int $clientid
		 * @param bool $only_active
		 * @return void|multitype:NULL
		 */
		public function getuserbyGroupId($groupid = null, $clientid = 0, $only_active = false)
		{
			if ( empty($groupid)) {
				return; //9999999999
			}

			//added if group id is array
			if( ! is_array($groupid))
			{
				$groupid = array($groupid);
			}
			
			$groupid = array_values(array_unique($groupid));

			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isdelete=0')
				->andWhereIn("groupid", $groupid)
				->andWhere("clientid = ?" , $clientid )
				->orderby("last_name ASC");
			
			if($only_active)
			{
				$usr->andWhere(" isactive = 0 ");
			}
			
			$dr = $usr->fetchArray();
			if($dr)
			{
				return $dr;
			}
		}

		/**
		 * 
		 * Jul 11, 2017 @claudiu 
		 * changed to remove the 9999999999999
		 * fixed $clients can be also string
		 * changed to join Notifications since both are on sysdat
		 * 
		 * @param string|array $clients
		 * @param bool $sadmin
		 * @return boolean|multitype:
		 */
		public function getClientsUsers($clients, $sadmin = false)
		{
			if (empty($clients)) {
				return false; //$clients_arr[] = '9999999999999';
			}
			$clients_arr = array();
			
			$users_arr = array();
			
			if(is_array($clients))
			{
				$clients_arr = $clients;
			}
			else
			{
				$clients_arr[] = $clients;
			}
// 			$clients_arr[] = '9999999999999';

			$usrs = Doctrine_Query::create()
				->select('u.*, n.*')
				->from('User u INDEXBY id')
				->leftJoin("u.Notifications n")
				->where('isdelete=0')
				->andWhere('isactive=0')
				->andWhereIn("clientid", $clients_arr);
				
			if($sadmin)
			{
				$usrs->orWhere('(clientid=0 AND usertype = "SA")');
			}
			$usrs->orderBy('last_name ASC');
			$userarr = $usrs->fetchArray();

			if($userarr)
			{
// 				$notifications = new Notifications();

				$users_ids_arr = array_column($userarr, 'id');
				foreach($userarr as &$user)
				{
					$user['notifications'] = $user['Notifications'];
					unset($user['Notifications']);
// 					$users_arr[$user['id']] = $user;
// 					$users_arr['ids'][] = $user['id'];
				}

// 				$notification_settings = $notifications->get_notification_settings($users_arr['ids']);

// 				foreach($users_arr as $user)
// 				{
// 					$users_arr[$user['id']]['notifications'] = $notification_settings[$user['id']];
// 				}

// 				return $users_arr;

				$userarr['ids'] =  $users_ids_arr;

				return $userarr;
				
			}
			else
			{
				return false;
			}
		}

		public static function getUsersDetails($users = array())
		{
			//anti-flowerpower
			if ( empty($users)) {
				return;
			}
			
			$users_arr = array();
			if(is_array($users))
			{
				$users_arr = $users;
			}
			else
			{
				$users_arr[] = $users;
			}
// 			flowerpower
// 			$users_arr[] = '9999999999999';

			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->whereIn("id", $users_arr)
				->andWhere('isdelete=0')
				->andWhere('isactive=0')
				->orderBy('last_name ASC');
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				$users_details = array();
				foreach($userarr as $k_usr => $v_usr)
				{
					$users_details[$v_usr['id']] = $v_usr;
				}

				return $users_details;
			}
		}

		public function users2groups($userids)
		{
			$userids[] = '99999999999';
			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->whereIn("id", $userids)
				->andWhere('isdelete=0');
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				foreach($userarr as $user)
				{
					$userarray[$user['id']] = $user['groupid'];
				}
				return $userarray;
			}
		}

		public function duplicate_user($id, $target_client, $group_master = false)
		{
			if($group_master)
			{
				$user_groups = Usergroup::getClientGroups($target_client);
				foreach($user_groups as $ku => $gr_details)
				{
					$related_group[$gr_details['groupmaster']] = $gr_details['id'];
				}
			}

			$user_details = User::getUserDetails($id, true);

			foreach($user_details as $k_u => $v_user)
			{
				$user = new User();
				$user->username = $v_user['username'];
				$user->password = $v_user['password'];
				$user->isadmin = $v_user['isadmin'];
				$user->issuperclientadmin = $v_user['issuperclientadmin'];
				$user->title = $v_user['title'];
				$user->user_title = $v_user['user_title'];
				$user->last_name = $v_user['last_name'];
				$user->first_name = $v_user['first_name'];
				$user->emailid = $v_user['emailid'];
				$user->fax = $v_user['fax'];
				$user->street1 = $v_user['street1'];
				$user->street2 = $v_user['street2'];
				$user->zip = $v_user['zip'];
				$user->city = $v_user['city'];
				$user->mobile = $v_user['mobile'];
				$user->phone = $v_user['phone'];
				$user->private_phone = $v_user['private_phone'];
				$user->betriebsstattennummer = $v_user['betriebsstattennummer'];
				$user->LANR = $v_user['LANR'];
				$user->clientid = $target_client;
				$user->isdelete = $v_user['isdelete'];
				$user->isactive = $v_user['isactive'];
				$user->usertype = $v_user['usertype'];
				$user->parentid = $id;
				$user->groupid = $related_group[$group_master];
				$user->notification = $v_user['notification'];
				$user->no10contactsbox = $v_user['no10contactsbox'];
				$user->onlyAssignedPatients = $v_user['onlyAssignedPatients'];
				$user->sixwnote = $v_user['sixwnote'];
				$user->fourwnote = $v_user['fourwnote'];
				$user->shortname = $v_user['shortname'];
				$user->usercolor = $v_user['usercolor'];
				$user->user_status = $v_user['user_status'];
				$user->verlauf_newest = $v_user['verlauf_newest'];
				$user->verlauf_fload = $v_user['verlauf_fload'];
				$user->verlauf_action = $v_user['verlauf_action'];
				$user->verlauf_entries = $v_user['verlauf_entries'];
				$user->bank_name = $v_user['bank_name'];
				$user->bank_account_number = $v_user['bank_account_number'];
				$user->bank_number = $v_user['bank_number'];
				$user->iban = $v_user['iban'];
				$user->bic = $v_user['bic'];
				$user->ikusernumber = $v_user['ikusernumber'];
				$user->dashboard_limit = $v_user['dashboard_limit'];
				$user->show_custom_events = $v_user['show_custom_events'];
				$user->allow_own_list_discharged = $v_user['allow_own_list_discharged'];
				$user->km_calculation_settings = $v_user['km_calculation_settings'];
				$user->assigned_standby = $v_user['assigned_standby'];
				$user->control_number = $v_user['control_number'];
				$user->duplicated_user = $id;
				$user->meeting_attendee = $v_user['meeting_attendee'];
				$user->roster_shortcut = $v_user['roster_shortcut'];
				$user->save();

				return $user->id;
			}
		}

		public function get_duplicated_users($userid)
		{
			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where("duplicated_user = " . $userid);
// 			echo $usr->getSqlQuery(); exit;
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				foreach($userarr as $user)
				{
					$userarray[$userid][$user['clientid']] = $user['id'];
				}
				return $userarray;
			}
			else
			{
				return false;
			}
		}

		public function get_active_duplicates($userid)
		{
			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where("duplicated_user = " . $userid)
				->andWhere("isactive = 0")
				->andWhere("isdelete = 0");
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				foreach($userarr as $user)
				{
					$userarray[] = $user['id'];
				}
				return $userarray;
			}
			else
			{
				return false;
			}
		}

		public function get_duplicated_users_on_client($userid, $clientid)
		{
			$usr = Doctrine_Query::create();
			$usr->select('*');
			$usr->from('User');
			$usr->where("duplicated_user = " . $userid . " OR id = " . $userid);
			$usr->andWhere("clientid = " . $clientid);
			$usr->andWhere('isdelete=0');
			$usr->andWhere('isactive=0');// TODO-1807 12.09.2018 Ancuta
// 			echo $usr->getSqlQuery(); exit;
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				foreach($userarr as $user)
				{
					$userarray = $user['id'];
				}
				return $userarray;
			}
		}

		public function get_connected_user_settings($userid, $target_client = false)
		{
			$usr = Doctrine_Query::create();
			$usr->select('*');
			$usr->from('User');
			$usr->andWhere('isactive=0');
			$usr->andWhere('isdelete=0');
			$usr->andWhere("duplicated_user = " . $userid . " OR parentid = " . $userid . "  OR id = " . $userid . " ");
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				foreach($userarr as $k => $user)
				{
					if($user['duplicated_user'] == "0")
					{
						$userarray[$user['id']]["status"] = "master";
						if($target_client)
						{
							$userarray[$user['id']]["connected"][$target_client] = $user['duplicated_user'];
						}
					}
					else
					{
						$userarray[$user['id']]["status"] = "slave";
						$userarray[$user['id']]["parent"] = $user['parentid'];
					}
				}

				return $userarray;
			}
		}

		/**
		 * @cla on 09.07.2018 re-write, 
		 * + changed to static
		 * + value_patternation
		 * + andWhere isactive = 0
		 * 
		 * @param string $str
		 * @param string $clientid
		 * @param string $sadmin
		 * @return boolean|array
		 */
		public static function livesearch_users($str = '', $clientid = null, $sadmin = false, $limit = 100)
		{
		    
		    if ( empty($clientid)){
    		    $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		    }
		    
		    $str = trim($str);

		    
		    if (empty($str)) {
		        return false;
		    }
		    
		    Pms_CommonData::value_patternation($str);
		    
			$usr = Doctrine_Query::create()
			->select('*')
			->from('User');

			if ($sadmin) {
				$usr->where('(clientid = ? OR clientid=0) OR usertype = "SA"' , $clientid);
			} else {
				$usr->where("clientid = ?", $clientid);
			}
			
			
			$usr->andWhere('isdelete = 0 ')
			->andWhere('isactive = 0')
			->andWhere("( last_name REGEXP ? OR  first_name REGEXP ? OR LOWER(last_name) REGEXP ? OR LOWER(first_name) REGEXP ? )" , array($str, $str, $str, $str))
			->limit((int)$limit)
			;
			
			$userarr = $usr->fetchArray();
			

			if ($userarr) {
			    
			    self::beautifyName($userarr);
			    
				return $userarr;
				
			} else {
			    
				return false;
			}
		}

		public function get_meeting_attendee_users($clientid = false)
		{
			if($clientid)
			{
				$usr = Doctrine_Query::create()
					->select('*')
					->from('User')
					->where("clientid = " . $clientid)
					->andWhere('meeting_attendee = "1"')
					->andWhere('isdelete=0')
					->andWhere('isactive=0')
					->orderBy('last_name ASC');
				$userarr = $usr->fetchArray();

				if($userarr)
				{
					foreach($userarr as $user)
					{
						$userarray[$user['id']] = $user;
					}
					return $userarray;
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

		public function get_clients_users($clientids)
		{
			if($clientids)
			{
				$clientids[] = '0';
				$usr = Doctrine_Query::create()
					->select('*')
					->from('User')
					->whereIn("clientid", $clientids)
					->andWhere('isdelete = "0"')
					->orderBy('last_name ASC');
				$userarr = $usr->fetchArray();

				if($userarr)
				{
					foreach($userarr as $user)
					{
						$userarray[$user['id']] = $user;
					}
					return $userarray;
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
		
		public function get_all_users_shortname()
		{
		 
			$usr = Doctrine_Query::create()
				->select('*')
				->from('User')
				->where('isdelete = "0"')
				->orderBy('last_name ASC');
			$userarr = $usr->fetchArray();

			if($userarr)
			{
				foreach($userarr as $user)
				{
					
					//extract initials
					if(empty($user['shortname']))
					{
						$userarray[$user['id']]['initials'] = mb_substr(trim($user['first_name']), 0, 1, "UTF-8") . "" . mb_substr(trim($user['last_name']), 0, 1, "UTF-8");
					}
					else
					{
						$userarray[$user['id']]['initials'] = $user['shortname'];
					}
					
					$userarray[$user['id']]['details'] = $user;
				}
				return $userarray;
			}
			else
			{
				return false;
			}
		 
		}

		//get all groups that can visit the pattient user in those groups that can visit - can be in tourenplanung/ rooster->dayplanningnew
		public function get_all_visiting_users_and_groups( $clientid = 0 , $makes_visits = true, $isactive = true, $sql_filters = array() )
		{
			$final = array();
			
			$usergrp = Usergroup :: getClientGroups($clientid , 1);
			foreach($usergrp as $v){
				$user_group[$v['id']] = $v;
			}
			$groups_ids= array_column($usergrp, "id");
			$user=array();
			if ( count($groups_ids) > 0 ){
				$user = Doctrine_Query::create()
				->select('user_title, last_name, first_name, groupid, makes_visits , isactive, DATE(isactive_date) as isactive_date')
				->from('User')
				->where('clientid= ? ', $clientid)
				->andWhere('isdelete = ?', 0)
				->andWhere('usertype != ?', 'SA')
				->andWhereIn('groupid', $groups_ids);
				
				if ( $isactive === true ){
					$user->andWhere('( isactive=0 or (isactive=1 AND DATE(isactive_date) > DATE(\''.date("Y-m-d") . '\') ) )');
				}
				if ( $makes_visits  === true ){
					$user->andWhere('makes_visits = ?', 1);
				}
				
				$user = $user->fetchArray();
			}

			/* pseudogroups 
			 * makes_visits == 0 => no visit right
			 * makes_visits == 1 => tourenplanung and dayplanningnew display + Behandelt durch + manually assign visits
			 * makes_visits == tours => tourenplanung display + auto-assign visits - group can be associated with a shift
			 */
			$userpseudo = Doctrine_Query::create()
			->select('*')
			->from('UserPseudoGroup')
			->where('clientid= ? ', $clientid)
			->andWhere('isdelete= ?', 0);
			if (isset($sql_filters['pseudogroups_with_visits'])){
				
				$userpseudo->andWhereIn("makes_visits", array('1', 'tours'));
				
			}elseif (isset($sql_filters['pseudogroups_with_tours'])){
				
				$userpseudo->andWhere("makes_visits = 'tours'");
				
			}else{
				$userpseudo->andWhere("makes_visits != '0'");
			}
			
			
			$userpseudo = $userpseudo->fetchArray();
				
			foreach( $user as $k => $v ){
			
				$final['grups'][ $user_group[ $v['groupid'] ]['id'] ][ 'groupname' ]  =  $user_group[ $v['groupid'] ][ 'groupname' ] ;
			
				$final['grups'][ $user_group[ $v['groupid'] ]['id'] ] [$v['id']] = $v['user_title']." " .$v['first_name'] ." ".$v['last_name'] ;
			
				$final['user_details'][ $v['id'] ] = $v;
				
			}
		
			if (count($userpseudo)>0){
				$Tr = new Zend_View_Helper_Translate();
				$final['pseudogrups']['groupname'] = $Tr->translate("userpseudo");
				foreach( $userpseudo as $v ){
					$final['pseudogrups'][$v['id']] = $v['servicesname'];
				}
			}

			
			return $final;
		}
		
		
		public function get_all_client_users($client_id, $isdropdwn = 0, $sadmin = false, $only_now_active = false, $period = false)
		{
		    $translate = new Zend_View_Helper_Translate();
		    $patientmaster = new PatientMaster();
		    
		    $usr = Doctrine_Query::create()
		    ->select('*')
		    ->from('User')
		    ->where("isdelete=0");
		    
		    if($sadmin)
		    {
		        $usr->andWhere('(clientid = "' . $client_id . '" OR clientid=0) OR usertype = "SA"');
		    }
		    else
		    {
		        $usr->andWhere(" isactive = '0' ");
		    }
		
		    if($now_active == "1"){
		        $usr->andWhere("clientid='" . $cid . "'");
		    }
		    
		    if($user_array){
		        if(!is_array($user_array)){
		            $user_array = array($user_array);
		        }
		        $usr->andWhereIn("id",$user_array);
		    }
		    $usr->orderBy('last_name ASC');
		    $userarr = $usr->fetchArray();

		    
		    foreach($userarr as  $k=>$udata)
		    {
		        $use_derails[$udata['id']] = $udata;

		        if($udata['create_date'] == "0000-00-00 00:00:00"){
		            $udata['create_date'] = "2008-01-01 00:00:00";
		        }
		        
		        if($udata['isactive'] == "1" && $udata['isactive_date'] != "0000-00-00")
		        { // if incative date - get acive period - from create to inactive date or current date
		            
		            if(strtotime($udata['isactive_date']) <= strtotime(date('Y-m-d',strtotime($udata['create_date']))))
		            {// FULL INCATIVE  
                        $use_derails[$udata['id']]['active_period']['start'] = date('Y-m-d',strtotime($udata['create_date']));
                        $use_derails[$udata['id']]['active_period']['end'] = date('Y-m-d',strtotime($udata['create_date']));
		            } 
		            elseif(strtotime($udata['isactive_date']) > strtotime(date('Y-m-d',strtotime($udata['create_date']))) && strtotime($udata['isactive_date']) > strtotime(date('Y-m-d',time())))
		            {// from create to current date
		                $use_derails[$udata['id']]['active_period']['start'] = date('Y-m-d',strtotime($udata['create_date']));
		                $use_derails[$udata['id']]['active_period']['end'] = date('Y-m-d',time());
		            } 
		            elseif(strtotime($udata['isactive_date']) > strtotime(date('Y-m-d',strtotime($udata['create_date']))) && strtotime($udata['isactive_date']) <= strtotime(date('Y-m-d',time())))
		            {// from create to inactive date
		                $use_derails[$udata['id']]['active_period']['start'] = date('Y-m-d',strtotime($udata['create_date']));
		                $use_derails[$udata['id']]['active_period']['end'] = $udata['isactive_date'];
		            }
		            else
		            {
    		            // FULL INCATIVE  
                        $use_derails[$udata['id']]['active_period']['start'] = date('Y-m-d',strtotime($udata['create_date']));
                        $use_derails[$udata['id']]['active_period']['end'] = date('Y-m-d',strtotime($udata['create_date']));
		            }
		        }
		        elseif($udata['isactive'] == "1" && $udata['isactive_date'] == "0000-00-00")
		        {
		            // FULL INCATIVE  
                    $use_derails[$udata['id']]['active_period']['start'] = date('Y-m-d',strtotime($udata['create_date']));
                    $use_derails[$udata['id']]['active_period']['end'] = date('Y-m-d',strtotime($udata['create_date']));
		                
		        } else {
		            // FULL ACTIVE  
                    $use_derails[$udata['id']]['active_period']['start'] = date('Y-m-d',strtotime($udata['create_date']));
                    $use_derails[$udata['id']]['active_period']['end'] = date('Y-m-d',time());
		        }
		    }
 
		    return $use_derails;
		}
		
		/**
		 * update 31.01.2018 : only SA can fetch the users with clientid=0;
		 * update 31.01.2018 : indexBy id, and commented the foreach;
		 * update 31.01.2018 : return is ordered ASC by last_name via uasort( _strcmp); 
		 * order could be via mysql ->orderBy("last_name ASC"); or via uasort( _strnatcmp), decided to use that cause we sort arrays in php
		 * 		 
		 * 
		 * this fn dosen't care if user is deleted(at this time SoftDelete behavior is not added) or disabled.. all it takes is the id and clientid to be correct(clientid can be empty)
		 * filter in your phpscript if you don't want the isdelete or striketrough the isactive
		 * @param array $user_id_arr
		 * @param number $clientid
		 * @param string|array $extra_columns
		 * @return boolean|array
		 */
		public static function getUsersNiceName( $user_id_arr = array(), $clientid = 0, $extra_columns = null) 
		{
			if (empty($user_id_arr) || ! is_array($user_id_arr)) {
				return;
			}
			
			
			$logininfo = new Zend_Session_Namespace('Login_Info');

			if ( empty($clientid)){
				$clientid = $logininfo->clientid;				
			}
			
			$sql_sadmin_users = '';

			if ($logininfo->usertype == 'SA' 
// 			    || $logininfo->usertype == 'CA'
                ) 
			{
			    $sql_sadmin_users = ' OR clientid=0 ';
			}
			
			$user_id_arr = array_values(array_unique($user_id_arr));
			
			$extra_columns_sql = '';
			if ( ! empty($extra_columns) ) {
				if (is_array($extra_columns)) {
					$extra_columns = implode(", ", $extra_columns) ;
				}
				$extra_columns_sql = $extra_columns . ", ";
			}
			
			
			$usrarray = Doctrine_Query::create()
			->select( $extra_columns_sql . 'id, title, user_title, last_name, first_name, emailid, shortname, isdelete, isactive, isactive_date')
			->from('User indexBy id')
			->whereIn('id', $user_id_arr)
			->andWhere(' (clientid = ? '.$sql_sadmin_users.' ) ' , (int)$clientid )
			->andWhere('(isdelete = 0 OR isdelete = 1)')
// 			->orderBy("last_name ASC")
			->fetchArray();
						
			self::beautifyName($usrarray);
			
			uasort($usrarray, array(new Pms_Sorter('last_name'), "_strcmp"));
			
// 			$user_names_array =  array();
// 			foreach ( $usrarray as $k ) {
// 				$user_names_array [ $k['id'] ] = $k; //INDEXBY id
// 			}
			return $usrarray;
		}
		
		/*
		 * by reference !
		 */
		public static function beautifyName( &$usrarray )
		{   
			//mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
		    if (is_array($usrarray)) 
    			foreach ( $usrarray as &$k )
    			{
    				if ( ! is_array($k) || isset($k['nice_name']) || isset($k['nice_initials'])) {
    					continue; // variable allready exists, use another name for the variable
    				}
    		
    				if( empty($k['shortname']) || trim($k['shortname']) == "") //shortname 0 is not allowed in here
    				{
    					$k['shortname'] = mb_substr(trim($k['first_name']), 0, 1, "UTF-8") . "" . mb_substr(trim($k['last_name']), 0, 1, "UTF-8");
    				}
    				$k['initials'] = $k['nice_initials'] = $k['shortname'];
    				$k['nice_name']	= trim($k['user_title'] . " " . $k['last_name']);
    				$k['nice_name']	.= trim( $k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
    			}
		}
		
		

		public function get_clients_users_active($clients, $sadmin = false, $include_inactive = false)
		{
			$clients_arr = array();
			if(is_array($clients))
			{
				$clients_arr = $clients;
			}
			else
			{
				$clients_arr[] = $clients;
			}
			$clients_arr[] = '9999999999999';
		
			$usrs = Doctrine_Query::create()
			->select('*')
			->from('User')
			->where('isdelete=0');
			if(!$include_inactive){
				$usrs->andWhere('isactive=0');
			}
			$usrs->andWhereIn("clientid", $clients);
			if($sadmin)
			{
				$usrs->orWhere('clientid=0 AND usertype = "SA"');
			}
			$usrs->orderBy('last_name ASC');
			$userarr = $usrs->fetchArray();
		
			if($userarr)
			{
				$notifications = new Notifications();
		
				foreach($userarr as $user)
				{
					$users_arr[$user['id']] = $user;
					$users_arr['ids'][] = $user['id'];
				}
		
				$notification_settings = $notifications->get_notification_settings($users_arr['ids']);
		
				foreach($users_arr as $user)
				{
					$users_arr[$user['id']]['notifications'] = $notification_settings[$user['id']];
				}
		
				return $users_arr;
			}
			else
			{
				return false;
			}
		}
		
		
		
		
		
	/**
	 * update 17.04.2018 + ->leftJoin("u.UserSettings us")
	 * add in $extra_columns = us.* if you want also from the UserSettings
	 * 
	 * it return ALL users of clientid  + if you are SA the ones from cleintid=0
	 * this fn dosen't care if user is deleted or disabled.. all it takes is the clientid to be correct(it can me empty, and then is $logininfo)
	 * filter in your phpscript if you don't want the isdelete or striketrough the isactive
	 * 
	 * remove (isactive=1 or isdelete=1 or clientid < 1)  <=>  kep the ones with (isdelete=0 and isactive=0 and clientid > 0)
	 * $users = array_filter($users, function($user) {
	 *     return ( ! $user['isdelete']) && ( ! $user['isactive']) && ($user['clientid'] > 0);
	 * });
	 * 
	 * @author claudiu 31.01.2018
	 * @param array $user_id_arr
	 * @param number $clientid
	 * @param string|array $extra_columns .. add here if you ant more columns
	 * @return boolean|array
	 */
	public static function get_AllByClientid( $clientid = null, $extra_columns = null)
	{
	    
	    $logininfo = new Zend_Session_Namespace('Login_Info');
	    
	    if ( empty($clientid)){
	        $clientid = $logininfo->clientid;
	    }
	    	
	    
       $sql_sadmin_users = '';

		if ($logininfo->usertype == 'SA' 
// 		    || $logininfo->usertype == 'CA'
            ) 
		{
		    $sql_sadmin_users = ' OR clientid=0 ';
		}
			
	    
	    	
	    $extra_columns_sql = '';
	    if ( ! empty($extra_columns) ) {
	        if (is_array($extra_columns)) {
	            $extra_columns = implode(", ", $extra_columns) ;
	        }
	        $extra_columns_sql = $extra_columns . ", ";
	    }
	    	
	    	
	    $usrarray = Doctrine_Query::create()
	    ->select( $extra_columns_sql . 'id, clientid, title, user_title, last_name, first_name, emailid, shortname, isdelete, isactive, isactive_date')
	    ->from('User u indexBy u.id')
	    ->leftJoin("u.UserSettings us")
	    ->where(' (clientid = ? '.$sql_sadmin_users.' ) ' , (int)$clientid )
// 	    ->orderBy("last_name ASC")
	    ->fetchArray()
	    ;
	    self::beautifyName($usrarray);
	
	    uasort($usrarray, array(new Pms_Sorter('last_name'), "_strcmp"));
	    
	    return $usrarray;
	}
	
	
	/**
	 * get the cleintid and groupid of the $logininfo
	 * optional $id to fetch another user
	 * @author claudiu 01.02.2018
	 * @param string $id
	 * @return array
	 */
	public static function get_ClientidAndGroupid( $id = null)
	{
	    $result = array(
	        'clientid' => 0,
	        'groupid'  => 0,  
	    );
	    
        $logininfo = new Zend_Session_Namespace('Login_Info');
	    
	    if (is_null($id) 
	        || ($logininfo->userid == $id && ! empty($logininfo->groupid) && ! empty($logininfo->clientid))) //you requested the loghedin user 
	    {
	        $result = array(
	            'clientid' => $logininfo->clientid,
	            'groupid'  => $logininfo->groupid,
	        );
	        
	    } else {
	        
	        $usrarray = Doctrine_Query::create()->select('id, groupid, clientid')
	        ->from('User')
	        ->where('id = :userid')
	        ->fetchOne(array("userid"=>$id), Doctrine_Core::HYDRATE_ARRAY);
	        $groupid = $usrarray['groupid'];
	        
	        $result = array(
	            'clientid' => $usrarray['clientid'],
	            'groupid'  => $usrarray['groupid'],
	        );
	        
	    }
	     
	    
	    return $result;
	    
	}
	
	
	
	public static function assert_User_belongsTo_Client( $userid = array() , $clientid = 0)
	{
	    if (empty($userid) ) {
	        return false;
	    }
	    
	    $userid = is_array($userid) ? $userid : array($userid);
	    $userid = array_values(array_unique($userid));
	    
	    if (empty($clientid)) {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	    }
	    
	    $usrarray = Doctrine_Query::create()
	    ->select('u.id')
	    ->from('User u')
	    ->where("clientid = ?" , $clientid )
	    ->andWhereIn('id', $userid)
	    ->fetchArray();
	    
	    
	    return boolval(count($usrarray) == count($userid));
	    
	}
	public function getUsersFast(){
	    
	}
	
	
	/**
	 * @cla on 17.07.2018
	 * !! $only_active = true
	 * 
	 * @param string $clientid
	 * @param bool $only_active
	 * @return Ambigous <void, multitype:NULL >
	 */
	public function fetchKoordinatorsUsers($clientid = null, $only_active = true) 
	{
	    $masterGroup = array("6");
	    
	    if (empty($clientid)) {
            $logininfo = new Zend_Session_Namespace('Login_Info');
            $clientid = $logininfo->clientid;
	    }
	    
	    $clientid = is_array($clientid) ? $clientid : [$clientid];
	    
	    
	    $usergroup = new Usergroup();
	    $cleintGroups = $usergroup->getUserGroups($masterGroup, $clientid);
	    
	    $cleintGroupsIDS = ! empty($cleintGroups) ? array_column($cleintGroups, 'id') : [];
	    
	    $usersArray = $this->getuserbyGroupId($cleintGroupsIDS, $clientid, $only_active);
	    
	    $this->beautifyName($usersArray);
	    
	    return $usersArray;
	    
	}
	
	
	/**
	 * @cla on 17.07.2018
	 * !! $only_active = true
	 * 
	 * @param string $clientid
	 * @param bool $only_active
	 * @return Ambigous <void, multitype:NULL >
	 */
	public function fetchHospizUsers($clientid = null, $only_active = true)
	{
	    $masterGroup = array("7");
	     
	    if (empty($clientid)) {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	    }
	     
	    $clientid = is_array($clientid) ? $clientid : [$clientid];
	     
	     
	    $usergroup = new Usergroup();
	    $cleintGroups = $usergroup->getUserGroups($masterGroup, $clientid);
	     
	    $cleintGroupsIDS = ! empty($cleintGroups) ? array_column($cleintGroups, 'id') : [];
	     
	    $usersArray = $this->getuserbyGroupId($cleintGroupsIDS, $clientid, $only_active);
	     
	    $this->beautifyName($usersArray);
	    
	    return $usersArray;
	     
	}
	
	
	/**
	 * @cla on 17.07.2018
	 * !! $only_active = true
	 * 
	 * @param string $clientid
	 * @param bool $only_active
	 * @return Ambigous <void, multitype:NULL >
	 */
	public function fetchHospizvereinUsers($clientid = null, $only_active = true)
	{
	    $masterGroup = array("10");
	     
	    if (empty($clientid)) {
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        $clientid = $logininfo->clientid;
	    }
	     
	    $clientid = is_array($clientid) ? $clientid : [$clientid];
	     
	     
	    $usergroup = new Usergroup();
	    $cleintGroups = $usergroup->getUserGroups($masterGroup, $clientid);
	     
	    $cleintGroupsIDS = ! empty($cleintGroups) ? array_column($cleintGroups, 'id') : [];
	     
	    $usersArray = $this->getuserbyGroupId($cleintGroupsIDS, $clientid, $only_active);
	     
	    $this->beautifyName($usersArray);
	     
	    return $usersArray;
	     
	}
	
	//Maria:: Migration CISPC to ISPC 22.07.2020
    public static function getUsersWithGroupnameFast ($clientid, $onlyactive=false)
    {
        $grps=Usergroup::getClientGroupsMap($clientid);


        $name=array();
        $name[1] = array('username'=>'admin', 'name'=>'Administrator');
        if($clientid){
            $usr = Doctrine_Query::create()
                ->select('id, username, first_name, last_name, groupid')
                ->from('User')
                ->where("clientid=?", $clientid);
            if($onlyactive){
                $usr->andwhere('isdelete=0');
            }
            $userarr = $usr->fetchArray();

            self::beautifyName($userarr);

            foreach ($userarr as $user)
            {
                $groupname=$grps[$user['groupid']]['groupname'];
                $name[$user['id']] = array('userid'=>$user['id'],'username'=>$user['username'], 'name'=>$user['first_name'] . " " . $user['last_name'],'nice_name'=>$user['nice_name'], 'group'=>$groupname, 'groupid'=>$user['groupid']);
            }
        }
        return $name;
    }
	
	
	

	
	
}

?>