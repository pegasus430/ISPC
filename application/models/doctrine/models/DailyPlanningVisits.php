<?php

	Doctrine_Manager::getInstance()->bindComponent('DailyPlanningVisits', 'SYSDAT');

	class DailyPlanningVisits extends BaseDailyPlanningVisits {

		function get_patients_visits($clientid, $date, $user_id = false)
		{
			$days = Doctrine_Query::create()
				->select('*')
				->from('DailyPlanningVisits')
				->where("DATE(start_date) =  DATE('" . $date . "')")
				->andWhere("clientid = " . $clientid)
				->andWhere("isdelete = 0");
			if($user_id && strlen($user_id) > 0)
			{
				$days->andWhere(" userid = " . $user_id);
			}
			$days->orderBy('start_date ASC');
			$users_q_array = $days->fetchArray();

			if($users_q_array)
			{
				return $users_q_array;
			}
			else
			{
				return false;
			}
		}

		function get_patients_visits_v2($clientid, $date, $user_id = false , $ipids = false)
		{
			$days = Doctrine_Query::create()
			->select('id, date, userid,userid_type,  clientid, ipid, orderid, hour, comment, is_autoassigned, start_date, end_date')
			->from('DailyPlanningVisits')
			->where("DATE(`date`) =  DATE('" . $date . "')")
			->andWhere("clientid = " . $clientid)
			->andWhere("isdelete = 0");
			if($user_id !== false )
			{
				if (!is_array($user_id)) $user_id = array($user_id);
				$days->andWhereIn(" userid  " , $user_id);
			}
			if ($ipids !== false ){
				if (!is_array($ipids)) $ipids = array($ipids);
				$days->andWhereIn("ipid" , $ipids);
			}
			
			$days->orderBy('userid, date, hour, orderid ASC');
			$users_q_array = $days->fetchArray();
		
			if($users_q_array)
			{
				return $users_q_array;
			}
			else
			{
				return false;
			}
		}
		
		//$date_interval = array( "start"=>date("Y-m-d"), "end"=>date("Y-m-d"))
		public function get_patients_visits_by_date_interval($clientid, $date_interval = array( "start"=> false, "end"=>false ) , $user_id = false , $ipids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			if (!isset($date_interval['start'], $date_interval['end']) ) return false;
				
			$boxcols = BoxOrder::boxcol_Defaults()['dayplanningnewAction'];
			
			$boxorder = Doctrine_Query::create()
			->from('BoxOrder')
			->select("boxcol, boxid, boxorder")
			->whereIn("boxcol", $boxcols)
			->andWhere("boxid != '0'")
			->andWhere("userid = ?", $logininfo->userid)
			->orderBy("boxcol ASC, boxorder ASC")
			->fetchArray();
			//var_dump($boxorder); exit;
			
			$days = Doctrine_Query::create()
			->select('dpv.date, dpv.userid, dpv.userid_type, dpv.ipid, dpv.orderid, dpv.hour, dpv.comment, dpv.start_date, dpv.end_date')
			->from('DailyPlanningVisits dpv')
			->Where("dpv.clientid = " . $clientid)
			->andWhere("dpv.isdelete = 0")
			->andWhere("dpv.date>= ?" , $date_interval['start'] )
			->andWhere("dpv.date<= ?" , $date_interval['end']);
			
			if($user_id !== false )
			{
				if (!is_array($user_id)) $user_id = array($user_id);
				$days->andWhereIn("dpv.userid  " , $user_id);
			}
			
			if ($ipids !== false ){
				if (!is_array($ipids)) $ipids = array($ipids);
				$days->andWhereIn("dpv.ipid" , $ipids);
			}
			
			if(empty($boxorder))
			{
				$days->leftJoin("dpv.User u");
				$days->andWhere('u.id = dpv.userid');
				$days->orderBy('dpv.userid_type DESC, u.last_name, dpv.date, dpv.hour, dpv.orderid ASC');
				$users_q_array = $days->fetchArray();
				//var_dump($users_q_array); exit;
			}
			else 
			{
				//$days->orderBy('userid_type DESC, userid, date, hour, orderid ASC');
				$days->orderBy('userid_type DESC, date, hour, orderid ASC');
				$users_q_array = $days->fetchArray();
				//var_dump($users_q_array); exit;
				//ISPC - 2369
				$users_to_be_printed = array_unique(array_column($users_q_array, 'userid'));
			
				$users_in_boxorder =  array_unique(array_column($boxorder, 'boxid'));			
				
				$users_not_in_boxorder = array_filter($users_to_be_printed, function($i, $k) use($users_in_boxorder) {return ! in_array($k, $users_in_boxorder);}, ARRAY_FILTER_USE_BOTH);
	
				$box303_index = 0;
				$box306_index = 0;
				foreach($boxorder as $kr => $vr)
				{
					if(in_array($vr['boxid'], $users_not_in_boxorder))
					{
						if($vr['boxcol'] == '303')
						{
							$box303_index = (int)$vr['boxorder'];
						}
						if($vr['boxcol'] == '306')
						{
							$box306_index = (int)$vr['boxorder'];
						}
					}
					else 
					{
						unset($boxorder[$kr]);
					}
				}
				
				foreach($users_not_in_boxorder as $kr => $vr)
				{
					if(in_array($vr, $users_in_boxorder))
					{
						continue;
					}
					else
					{
						$user_type = $users_q_array[array_search($vr, array_column($users_q_array, 'userid'))]['userid_type'];
						if($user_type == 'user')
						{
							$box303_index++;
						}
						else 
						{
							$box306_index++;
						}
	
						$boxorder[] = [
								'id' => null,
								'boxcol' => $user_type == 'user' ? '303' : '306',
								'boxid' => $vr,
								'boxorder' => $user_type == 'user' ? (string)$box303_index : (string)$box306_index
						];
					}
				}

			
				foreach($boxorder as $ko => $vo)
				{
					if($vo['boxcol'] <= 303 && $vo['boxid'] != '0')
					{
						$userorder[] = $vo['boxid'];
					}
					else if($vo['boxcol'] <= 306 && $vo['boxcol'] >= 304 && $vo['boxid'] != '0')
					{
						$pseudoorder[] = $vo['boxid'];
					}
				}
			
				if(!empty($pseudoorder))
				{
					foreach($pseudoorder as $kuo => $vo)
					{
						foreach($users_q_array as $k => $v)
						{
			
							if($v['userid_type'] == 'pseudogrups')
							{
								if($v['userid'] == $vo)
								{
									$users_q_array_sorted[] = $v;
								}
							}
						}
			
					}
				}
				else
				{
					foreach($users_q_array as $k => $v)
					{
						if($v['userid_type'] == 'pseudogrups')
						{
							$users_q_array_sorted[] = $v;
						}
					}
				}
			
				if(!empty($userorder))
				{
					foreach($userorder as $kuo => $vo)
					{
						foreach($users_q_array as $k => $v)
						{
			
							if($v['userid_type'] == 'user')
							{
								if($v['userid'] == $vo)
								{
									$users_q_array_sorted[] = $v;
								}
							}
						}
							
					}
				}
				else
				{
					foreach($users_q_array as $k => $v)
					{
						if($v['userid_type'] == 'user')
						{
							$users_q_array_sorted[] = $v;
						}
					}
				}
			
				$users_q_array = $users_q_array_sorted;
			}
			
			//ISPC - 2369
			if($users_q_array)
			{
				return $users_q_array;
			}
			else
			{
				return false;
			}
		}
		
		function get_last_patients_visits($clientid, $date, $user_id = false)
		{
			$days = Doctrine_Query::create()
				->select('*')
				->from('DailyPlanningVisits')
				->where("DATE(start_date) =  DATE('" . $date . "')")
				->andWhere("clientid = " . $clientid)
				->andWhere("isdelete = 0");
			if($user_id && strlen($user_id) > 0)
			{
				$days->andWhere(" userid = " . $user_id);
			}

			$days->orderBy('end_date DESC');
			$days->limit(1);
			$users_q_array = $days->fetchArray();

			if($users_q_array)
			{
				return $users_q_array[0];
			}
			else
			{
				return false;
			}
		}

		
		
		//delete some autoassigned visits
		//is_autoassigned must be 1
		public function set_autoasign_visits_delete($clientid = false , $date = false , $userid=false , $ipid= false, $limit = false , $start_date = false,  $start_hour = false, $userid_type = false , $is_autoassigned = true){
		
			$docid = Doctrine_Query::create()
			->update('DailyPlanningVisits')		
			->set('isdelete', 1)
			->where('clientid = ?' , $clientid)
			->andWhere('isdelete =?', 0);
			
			if ($is_autoassigned == true){
				$docid->andWhere('is_autoassigned = 1 ');
			}
			if ($date!==false){
				$docid->andWhere("DATE(date) = DATE('" . $date . "') ")	;
			}	
			if ($start_date!==false){
				$docid->andWhere("DATE(date) > DATE('" . $start_date . "') ");			
				//$start_hour
			}
			if ($userid!==false){
				$docid->andWhere('userid = ?', $userid);
			}
			if ($userid_type!==false){
				$docid->andWhere('userid_type = ?', $userid_type);
			}		
			
			if ($ipid!==false){
				$docid->andWhere('ipid =?', $ipid);
			}
			
			if ($limit!==false){
				$docid->orderBy( "orderid DESC" );
				$docid->limit( (int)$limit );
			}
			
			$docid->execute();

			return;
		}
		
		public function set_autoasign_visits_cronjob( $clientid = false , $date = false , $ipid= false , $is_cronjob = false)
		{

			if  ( $clientid === false || $date === false ) return false;
			
			//get all client shifts
			$c_shifts = new ClientShifts();
			$client_shifts = $c_shifts->get_client_shifts( $clientid );
			
			//get users allready assigned to some patients
			//this doctors allredy are assigned to some patients
			$user_planning = new DailyPlanningUsers();
			$users_details2date = $user_planning->get_users_by_date_interval($clientid, array("start"=>date("Y-m-d 00:00:00", strtotime($date)), "end"=>date("Y-m-d 23:59:59", strtotime($date))));		
		
			//echo $date;

			//get pseudogroup vith visiting rights
			$UserPseudoGroup = UserPseudoGroup :: get_userpseudo_with_make_visits($clientid);
			//print_r($UserPseudoGroup);
			
			//get groups  that are allowed to shou in dienstplan  - the second param of this function			
			$groups = Usergroup :: getClientGroups($clientid , 1);
			$groups_ids = array_column($groups, "id");
			if (empty($groups_ids)) $groups_ids = array(999999);
			
			//get all Users from this groups that can make visits (users that are allowed to visit)
			$docarray = array();
			if ( count($groups_ids) > 0 ){
				$doc = Doctrine_Query::create()
				->select('id')
				->from('User')
				->Where('clientid= ? ' , $clientid)
				
				//->where('isactive=0') // this should be rechecked with OR for day?
				->andWhere('( isactive=0 or (DATE(isactive_date) > DATE(\''.$date . '\') ) )')
				->andWhere('isdelete= ? ',0)
				->andWhere('usertype!="SA"')
				->andWhereIn('groupid', $groups_ids)
				->andWhere('makes_visits = ? ', 1);
				//->orderBy('last_name ASC');
				$docarray = $doc->fetchArray();
			}
			$client_users_ids = array_column($docarray, "id");
			//print_r($docarray);
			
			foreach($users_details2date as $k=>$user){
				if( $user['userid_type'] == 'user' && !in_array($user['userid'], $client_users_ids)){
					unset($users_details2date[$k] );
				}			
			}
			//print_r($users_details2date);
			
			//get users - duty roster for today for user that are active and make_visits
			if (!empty($client_users_ids) && count($client_users_ids) > 0 ){
				$docid = Doctrine_Query::create()
				->select('id, userid, shift')
				->from('Roster')
				->where('clientid = ' . $clientid)
				->andWhereIn('userid', $client_users_ids)
				->andWhere("DATE(duty_date) = DATE('" . $date . "') ")
				->andWhere('isdelete = "0"');
				$rostarray = $docid->fetchArray();
			}
			$userids2date = (is_array($rostarray)) ? array_column($rostarray , "userid") : array();
			$rostarray_shifts = array();
			if (is_array($rostarray))
			foreach($rostarray as $user){
				$rostarray_shifts [ $user['userid'] ]  = $user['shift'];
			}

			//print_r($rostarray_shifts);
			//@TODO: eliminate some ipids... that are inactive, or are in hospital, or are active... and semething ?
			
			// get all patients that have visits_settings_enabled
			$PatientVisit = PatientVisitsSettings :: get_Patients_with_VisitsSettings_of_client($clientid ,  $date , array("user","pseudogrups") , $ipid);
			$assigned_ipids = array_column($PatientVisit , "ipid");
			//print_r($PatientVisit);
			foreach($PatientVisit as $k => $visits){

				if (($visits['visitor_type'] == 'user' && !in_array($visits['visitor_id'], $client_users_ids))
					||
					($visits['visitor_type'] == 'pseudogrups' && !array_key_exists($visits['visitor_id'], $UserPseudoGroup))
				){
					//check againt today available users and pseudogroups , so we don't make a false assign
					//echo "\nunset ".$visits['visitor_id'];
					unset($PatientVisit[$k]);
					
				}else{
					//echo "\nOK ".$visits['visitor_id'];
					$setings [ $visits['ipid'] ] [ $visits['visitor_type'] ] [ $visits['visitor_id'] ]   = $visits;
				}
			}
			//print_r($setings);
			//die();

			//get patients with allready assigned visit plan
			$allready_visits = self::get_patients_visits_v2($clientid, $date, false, $assigned_ipids);
			//print_r($allready_visits);
			
			$setings_allready = array();
			if (is_array($allready_visits))
			foreach($allready_visits as $visits){
				$setings_allready [ $visits['ipid'] ] [ $visits['userid_type'] ] [ $visits['userid'] ]   ++;				
			}
			//print_r($setings_allready);

			if (is_array($setings))
			foreach ($setings as $ipid => $set){
				//foreach user
				foreach($setings_allready[$ipid]['user'] as $user_id => $val_allready){
				
					$diff = (int)$val_allready - (int)$set['user'][$user_id]['visits_per_day'];
					if ($diff>0){												
						self :: set_autoasign_visits_delete($clientid , $date , $user_id , $ipid , $diff, false, false ,'user' );
						unset ($setings[$ipid]['user'][$user_id]);
					}else{
						$setings[$ipid]['user'][$user_id]['visits_per_day'] = abs($diff);
					}
				}
				
				
				//foreach pseudogrups
				foreach($setings_allready[$ipid]['pseudogrups'] as $user_id => $val_allready){
				
					$diff = (int)$val_allready - (int)$set['pseudogrups'][$user_id]['visits_per_day'];
					if ($diff>0){
						self :: set_autoasign_visits_delete($clientid , $date , $user_id , $ipid , $diff, false, false ,'pseudogrups' );
						unset ($setings[$ipid]['pseudogrups'][$user_id]);
					}else{
						$setings[$ipid]['pseudogrups'][$user_id]['visits_per_day'] = abs($diff);
					}
				}
			}
			
			
			// compare visits_settings_enabled with allready assigned
			$todayPlanningUsers = array();
			$autovisit = array();
			$counter = 0;
			if (is_array($setings))
			foreach($setings as $ipid => $types)
			{
				foreach ($types as $types_id=>$visitors)
				{
					foreach ( $visitors as $visitor_id){
	
						//print_r($visitor_id);
						/* if(!in_array($visitor_id['visitor_id'] , $client_users_ids)){
							continue;
						} */
						//use default shift start-hour if this is empty
						//@TODO if multiple shifts in the same day ?
						if ($visitor_id['visitor_type'] == 'user' && $visitor_id['visit_hour'] == "" &&  !empty($rostarray_shifts [$visitor_id['visitor_id']]) ){	
								$start_h=($client_shifts [ $rostarray_shifts [$visitor_id['visitor_id']] ] ['start'] );
								$start_h = date("G", strtotime($start_h));
								$visitor_id['visit_hour'] = $start_h;
						} 
						if ($visitor_id['visit_hour'] == ""){
							$visitor_id['visit_hour'] = 8;
						}
						//echo $visitor_id['visits_per_day'] ."/  ";	
						//echo $setings_allready [ $ipid ] [ $visitor_id['visitor_id'] ] ."/  ";
						
						//redundant visit check
						//$visit_number_to_autoassign = (int)$visitor_id['visits_per_day'] - (int)$setings_allready [ $ipid ] [ $visitor_id['visitor_type'] ] [ $visitor_id['visitor_id'] ];
						$visit_number_to_autoassign = (int)$visitor_id['visits_per_day'];
						
						//echo $ipid." has ".$visit_number_to_autoassign ."visits from doctor: ". $visitor_id['visitor_id']."<hr>";
						if ((int)$visit_number_to_autoassign > 0 ){
							//assign userid visit to patient ipid
							for ($i=0; $i<(int)$visit_number_to_autoassign; $i++){
								
								$counter++;
								
								$autovisit[$counter] =  array(
									"date" => date("Y-m-d 00:00:00" , strtotime($date) ),
									"userid" => $visitor_id['visitor_id'],
									"userid_type" => $visitor_id['visitor_type'],
									"clientid" => $clientid,
									"ipid" => $ipid,
									"orderid" => $i, //this shuld be modified to use some type of sizeof(allready assigned) + i
									"hour" => (int)$visitor_id['visit_hour'],
									"comment" => "",
									"is_autoassigned" => 1
								);
								if ($is_cronjob){
									$autovisit [$counter] ["create_user"] = "-1";
								}
							}
													
							$todayPlanningUsers [$visitor_id['visitor_type']] [$visitor_id['visitor_id']] = $visitor_id['visitor_id'];
						}
					}
				}
			}
			//print_r($autovisit);
			//save this visits
			if(count($autovisit) > 0){
				$collection = new Doctrine_Collection('DailyPlanningVisits');
				$collection->fromArray($autovisit);
				$collection->save();
			}
			
			//print_r($todayPlanningUsers);
			
			foreach ($users_details2date as $user_with_plan){
				$users_2date[ $user_with_plan['userid_type'] ] [ $user_with_plan['userid'] ] = $user_with_plan;
			}
			//print_r($users_2date);
			//mark this users as having visits at this date
			$records_users_plan = array();
			foreach($todayPlanningUsers as $userid_type=>$users)
			{
				foreach ($users as $user_id){
					if(!array_key_exists($user_id, $users_2date[$userid_type] ))
					{
						$records_users_plan[] = array(
								"clientid" => $clientid,
								"userid" => $user_id,
								"userid_type" => $userid_type,
								"date" => $date
						);
					}
				}
			}
			
			if(count($records_users_plan) > 0)
			{
				//print_r($records_users_plan);
				$collection = new Doctrine_Collection('DailyPlanningUsers');
				$collection->fromArray($records_users_plan);
				$collection->save();
			}
			
			// ... and other condition..
			
			//die();
			return "DailyPlanningVisits=" . count($autovisit) . ' DailyPlanningUsers='. count($records_users_plan);
			
		}
		
		
		
		
	}

?>