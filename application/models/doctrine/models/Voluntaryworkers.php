<?php

	Doctrine_Manager::getInstance()->bindComponent('Voluntaryworkers', 'SYSDAT');
	// Maria:: Migration ISPC to CISPC 08.08.2020
	class Voluntaryworkers extends BaseVoluntaryworkers {

		public function getVoluntaryworkers($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Voluntaryworkers')
				->where("id=?", $id)
				->andWhere("isdelete = 0");
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public static function getClientsVoluntaryworkers($clientid = null, $vw_ids = false)
		{
		    
		    if (empty($clientid)) {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		    }
		    
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Voluntaryworkers')
				->where("clientid=?", $clientid)
				->andWhere("isdelete = 0")
				->andWhere("indrop = 0");
			 if($vw_ids && is_array($vw_ids)){
				//$drop->andWhereIn("id",$vw_ids);
			 	$drop->orWhereIn("id",$vw_ids);
			 }	
			$drop->orderBy('last_name ASC');
			$droparray = $drop->fetchArray();

			return $droparray;
		}
		
		public function getClientsVoluntaryworkersSort($clientid, $vw_ids = false, $sort = "ASC")
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Voluntaryworkers')
				->where("clientid=?", $clientid)
				->andWhere("isdelete = 0")
				->andWhere("indrop = 0");
			 if($vw_ids && is_array($vw_ids)){
				$drop->orWhereIn("id",$vw_ids);
			 }	
			$drop->orderBy('last_name '.$sort.' ');
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		

        /**
         * TODO-2742 // Maria:: Migration ISPC to CISPC 08.08.2020
         * Ancuta - renamed function to  *_OLD
         * NOT USED
         * @param unknown $clientid
         * @param unknown $vw_parents
         * @return unknown
         */
		
		public function parent2child_workers_OLD($clientid, $vw_parents)
		{
		    $drop = Doctrine_Query::create()
		    ->select('*')
		    ->from('Voluntaryworkers')
		    ->where("clientid=?", $clientid)
		    ->andWhere("isdelete = 0")
		    ->andWhere("indrop = 1")
		    ->andWhereIn("parent_id", $vw_parents);
		    $droparray = $drop->fetchArray();
		
		    foreach($droparray as $k => $val)
		    {
		        $parent2worker['details'][$val['parent_id']][] = $val;
		        $parent2worker['parent2vwid'][$val['parent_id']][] = $val['id'];
		        $parent2worker['vwid2parent'][$val['id']] = $val['parent_id'];
		        $parent2worker['vw_ids'][] = $val['id'];
		    }
		    return $parent2worker;
		}
		
		
		
		
		/**
         * TODO-2742
         * Ancuta - renamed function to  *_OLD
         * NOT USED
		 * @param unknown $clientid
		 * @return unknown
		 */
		public function get_all_parent2child_workers_OLD($clientid)
		{
		    $drop = Doctrine_Query::create()
		    ->select('*')
		    ->from('Voluntaryworkers')
		    ->where("clientid=?", $clientid)
		    ->andWhere("isdelete = 0")
		    ->andWhere("indrop = 1")
		    ->andWhere("parent_id != 0");
		    $droparray = $drop->fetchArray();
		
		    foreach($droparray as $k => $val)
		    {
		        $parent2worker['details'][$val['parent_id']][] = $val;
		        $parent2worker['parent2vwid'][$val['parent_id']][] = $val['id'];
		        $parent2worker['vwid2parent'][$val['id']] = $val['parent_id'];
		        $parent2worker['vw_ids'][] = $val['id'];
		    }
		    return $parent2worker;
		}
		
		
		
		
		/**
		 * on 06.12.2019 Ancuta copy of fn parent2child_workers_OLD
		 * included current client  to the search-
		 * Voluntary addded to patients - in a child client  - are created with child/logged client  
		 * @param unknown $clientid
		 * @param unknown $vw_parents
		 * @return unknown
		 */
		
		public function parent2child_workers($clientid, $vw_parents)
		{
		    if(is_array($clientid))
		    {
		        $client_arr = $clientid;
		    }
		    else
		    {
		        $client_arr = array($clientid);
		    }
		    $logininfo = new Zend_Session_Namespace('Login_Info');
	        $current_clientid = $logininfo->clientid;
		    
	        $client_arr[] = $current_clientid;
	        $client_arr = array_unique($client_arr);
	        
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Voluntaryworkers')
				->whereIn("clientid", $client_arr)
				->andWhere("isdelete = 0")
				->andWhere("indrop = 1")
				->andWhereIn("parent_id", $vw_parents);
			$droparray = $drop->fetchArray();

			foreach($droparray as $k => $val)
			{
				$parent2worker['details'][$val['parent_id']][] = $val;
				$parent2worker['parent2vwid'][$val['parent_id']][] = $val['id'];
				$parent2worker['vwid2parent'][$val['id']] = $val['parent_id'];
				$parent2worker['vw_ids'][] = $val['id'];
			}
			return $parent2worker;
		}
		
		/**
		 * 
		 * on 06.12.2019 Ancuta copy of fn get_all_parent2child_workers_OLD
		 * included current client  to the search-
		 * Voluntary addded to patients - in a child client  - are created with child/logged client
		 * @param unknown $clientid
		 * @return unknown
		 */
		public function get_all_parent2child_workers($clientid)
		{
		    if(is_array($clientid))
		    {
		        $client_arr = $clientid;
		    }
		    else
		    {
		        $client_arr = array($clientid);
		    }
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $current_clientid = $logininfo->clientid;
		    
		    $client_arr[] = $current_clientid;
		    $client_arr = array_unique($client_arr);
		    
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Voluntaryworkers')
				->whereIn("clientid", $client_arr)
				->andWhere("isdelete = 0")
				->andWhere("indrop = 1")
				->andWhere("parent_id != 0");
			$droparray = $drop->fetchArray();

			foreach($droparray as $k => $val)
			{
				$parent2worker['details'][$val['parent_id']][] = $val;
				$parent2worker['parent2vwid'][$val['parent_id']][] = $val['id'];
				$parent2worker['vwid2parent'][$val['id']] = $val['parent_id'];
				$parent2worker['vw_ids'][] = $val['id'];
			}
			return $parent2worker;
		}

		public function clone_record($id, $target_client)
		{
			$voluntary = $this->getVoluntaryworkers($id);
			if($voluntary)
			{
				foreach($voluntary as $k_volunteer => $v_volunteer)
				{
					$volunteer = new Voluntaryworkers();
					$volunteer->clientid = $target_client;
					$volunteer->parent_id = $id;//ISPC-2614
					$volunteer->hospice_association = '0';
					$volunteer->salutation = $v_volunteer['salutation'];
					$volunteer->title = $v_volunteer['title'];
					$volunteer->status = $v_volunteer['status'];
					$volunteer->status_color = $v_volunteer['status_color'];
					$volunteer->last_name = $v_volunteer['last_name'];
					$volunteer->first_name = $v_volunteer['first_name'];
					$volunteer->birthdate = $v_volunteer['birthdate'];
					$volunteer->street = $v_volunteer['street'];
					$volunteer->zip = $v_volunteer['zip'];
					$volunteer->city = $v_volunteer['city'];
					$volunteer->phone = $v_volunteer['phone'];
					$volunteer->mobile = $v_volunteer['mobile'];
					$volunteer->email = $v_volunteer['email'];
					$volunteer->indrop = '1';
					$volunteer->comments = $v_volunteer['comments'];
					$volunteer->comments_availability = $v_volunteer['comments_availability'];     //ISPC-2617 Lore 22.07.2020
					$volunteer->children = $v_volunteer['children'];
					$volunteer->profession = $v_volunteer['profession'];
					$volunteer->appellation = $v_volunteer['appellation'];
					$volunteer->edication_hobbies = $v_volunteer['edication_hobbies'];
					$volunteer->working_week_days = $v_volunteer['working_week_days'];
					$volunteer->working_hours = $v_volunteer['working_hours'];
					$volunteer->has_car = $v_volunteer['has_car'];
					$volunteer->special_skils = $v_volunteer['special_skils'];
					$volunteer->save();

					return $volunteer->id;
				}
			}
		}

		public function get_voluntaryworkers($ids)
		{
			if(is_array($ids))
			{
				$array_ids = $ids;
			}
			else
			{
				$array_ids = array($ids);
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Voluntaryworkers')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		
		/**
		 * ISPC-2609 Ancuta 15.09.2020 Added $clientid parame
		 * @param unknown $vws_ids
		 * @param string $sortby
		 * @param string $sortdir
		 * @param boolean $upcoming_birthdays
		 * @param number $clientid
		 * @return array|string
		 */
		public function get_vws_multiple_details($vws_ids, $sortby = "first_name", $sortdir = "ASC",$upcoming_birthdays=false, $client_ident = false ){

		    
		    $voluntary_workers = new Voluntaryworkers();
		    $patient_voluntary_workers = new PatientVoluntaryworkers();
		    
		    $order_by_str = $sortby . ' ' . $sortdir . ' ';
		    
		    // ################################################
		    // get associated clients of current clientid START
		    // ###############################################

		    
		    //ISPC-2609 Ancuta 15.09.2020 
		    if(isset($client_ident) && !empty($client_ident)){
		        $check_client = $client_ident ;
		    } else {
                $logininfo = new Zend_Session_Namespace('Login_Info');
                $check_client = $logininfo->clientid ;
		    }
		    //--
		    
		    
		    $connected_client = VwGroupAssociatedClients::connected_parent($check_client);
		    if ($connected_client) {
		        $clientid = $connected_client;
		    } else {
		        $clientid = $check_client;
		    }
		    // ################################################
		    // get associated clients of current clientid END
		    
		    if ($clientid > 0) {
		        $where = ' and clientid=' . $clientid;
		    } else {
		        $where = ' and clientid=0';
		    }
		    
		    // Hospice association details
		    $h_association = Doctrine_Query::create()->select('*')
		    ->from('Hospiceassociation')
		    ->where('indrop= 0 and isdelete = 0 and clientid=' . $clientid);
		    $h_association_array = $h_association->fetchArray();
		    
		    foreach ($h_association_array as $khas => $h_assoc_item) {
		        $h_assoc_data[$h_assoc_item['id']] = $h_assoc_item['hospice_association'];
		    }
		    
		    $color_firtered_details = VwColorStatuses::get_vw_ids_color_statuses_filter(false, $clientid, true, $filter_color_status_array);
		    
		    $color_filterd_ids = $color_firtered_details['filter_ids'];
		    
		    if (strlen($filter_color_sql) > 0) {
		        $filter_color_sql_final = $filter_color_sql;
		    }
		    
		    // ########################################
		    // ##### Query for details ###############
		    
		    $vw_sql = '*,';
		    $vw_sql .= 'IF(has_car = "1","Ja","Nein") as has_car,';
		    $vw_sql .= '(YEAR(NOW()) - YEAR(birthdate)) as age,';
		    $vw_sql .= " DATEDIFF(birthdate + INTERVAL YEAR(now()) - YEAR(birthdate) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthdate, '%m%d'), 1, 0) YEAR, now()) AS days_to_birthd,";
		    $vw_sql .= "IF(birthdate != '0000-00-00',DATE_FORMAT(birthdate,'%d\.%m\.%Y'),'') as birthdate,";
		    $vw_sql .= "IF((gc_certificate_date != '0000-00-00 00:00:00' && gc_certificate = 2) ,Concat('Ja',' (',DATE_FORMAT(gc_certificate_date,'%d\.%m\.%Y'),')'  ),'Nein') as gc_certificate_date,";
		    
		    //ISPC-1900 order by upcoming birthdays
		    if($upcoming_birthdays) {
		        $vw_sql .= " DATE_FORMAT(birthdate, '%m%d') as birthdate_daymonth, ";
		        $vw_sql .= " DATE_FORMAT(birthdate, '%Y') as birthdate_year, ";
		        
		        $order_by_str = "birthdate_daymonth  ASC, birthdate_year ASC, " . $order_by_str;
		    }
		    
		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select($vw_sql);
		    $fdoc1->from('Voluntaryworkers');
		    $fdoc1->where("isdelete = 0  " . $where);
		    $fdoc1->andWhere("indrop = 0  ");
		    if($upcoming_birthdays){
		        //                $fdoc1->andWhere("date_format( `birthdate` , '%m-%d' ) BETWEEN date_format(now() ,'%m-%d') AND date_format(date_add( now() , INTERVAL 50 DAY ) , '%m-%d' )");
		        $fdoc1->andWhere(" DATEDIFF(birthdate + INTERVAL YEAR(now()) - YEAR(birthdate) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthdate, '%m%d'), 1, 0) YEAR, now()) < 90");
		    } else{
		        $fdoc1->andWhereIn("id", $vws_ids);
		    }
		    //             $fdoc1->Orderby($sortby.' '.$sortdir);
		    $fdoc1->Orderby($order_by_str);
		    //             echo $fdoc1->getSqlQuery(); exit;
		    $fdoclimitexec = $fdoc1->execute();
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
		    
		    $voluntary_workers_ids[] = '99999999999999';
		    foreach ($fdoclimit as $key => $voluntary_worker_item) {
		        $fdoclimit_arr[$voluntary_worker_item['id']] = $voluntary_worker_item;
		        
		        if ($voluntary_worker_item['hospice_association'] > 0) {
		            $fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = $h_assoc_data[$voluntary_worker_item['hospice_association']];
		        } else {
		            $fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = '-';
		        }
		        
		        $voluntary_workers_ids[] = $voluntary_worker_item['id'];
		    }
		    $primary_status_arr = Pms_CommonData::get_primary_voluntary_statuses();
		    $status_arr = Pms_CommonData::getVoluntaryWorkersStatuses();
		    
		    foreach ($status_arr as $k_status => $v_status) {
		        $statuses[$v_status['id']] = $v_status['status'];
		    }
		    //             $this->view->status_array = $statuses;
		    $view = Zend_Layout::getMvcInstance()->getView();
		    $view->status_array = $statuses;
		    
		    
		    foreach ($primary_status_arr as $k_pri_status => $v_pri_status) {
		        $statuses[$v_pri_status['id']] = $v_pri_status['status'];
		    }
		    
		    foreach ($fdoclimit as $keysst => $voluntary_worker_item_cst) {
		        if (! empty($color_firtered_details['statuses'][$voluntary_worker_item_cst['id']])) {
		            $fdoclimit_arr[$voluntary_worker_item_cst['id']]['status_color'] = $color_firtered_details['statuses'][$voluntary_worker_item_cst['id']][0]['status'];
		        } else {
		            $fdoclimit_arr[$voluntary_worker_item_cst['id']]['status_color'] = $voluntary_worker_item_cst['status_color'];
		        }
		    }
		    
		    // get active patients
		    $patietns_act = Doctrine_Query::create()->select("p.ipid,
    				e.epid,
    				AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
    				AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
    				")
    				->from('EpidIpidMapping e')
    				->leftJoin('e.PatientMaster p')
    				->where('e.ipid = p.ipid')
    				->andWhere('e.clientid = "' . $logininfo->clientid . '"')
    				->andWhere('p.isstandby = "0"')
    				->andWhere('p.isdischarged = "0"')
    				->andWhere('p.isdelete = "0"')
    				->andWhere('p.isstandbydelete = "0"');
    				$active_patients = $patietns_act->fetchArray();
    				
    				if (! empty($active_patients)) {
    				    foreach ($active_patients as $k_patient => $v_patient) {
    				        $patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); // used in patients dropdown
    				        $patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; // used to match patient_master id with ipid
    				        $active_ipids[] = $v_patient['PatientMaster']['ipid'];
    				        $patient_details[$v_patient['PatientMaster']['ipid']] = $v_patient['PatientMaster']['last_name'] . ' ' . $v_patient['PatientMaster']['first_name'];
    				    }
    				}
    				
    				if (empty($active_ipids)) {
    				    $active_ipids[] = "XXXXXX";
    				}
    				
    				$parent2child = $voluntary_workers->parent2child_workers($clientid, $voluntary_workers_ids);
    				$worker2activepatients = $patient_voluntary_workers->get_workers2patients($parent2child['vw_ids'], $active_ipids, false, $currently_connected = true);
    				
    				foreach ($worker2activepatients as $vwid => $patipid) {
    				    if (! in_array($patient_details[$patipid], $pat2master[$parent2child['vwid2parent'][$vwid]])) {
    				        $pat2master[$parent2child['vwid2parent'][$vwid]][] = $patient_details[$patipid];
    				    }
    				}
    				
    				$voluntary_workers_statuses = new VoluntaryworkersStatuses();
    				$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($voluntary_workers_ids, $clientid);
    				
    				foreach ($worker_statuses as $k_data => $v_data) {
    				    foreach ($v_data as $k_vdata => $v_vdata) {
    				        $worker_statuses_arr[$k_data][] = $statuses[$v_vdata];
    				    }
    				}
    				
    				foreach ($worker_statuses_arr as $k_vw_id => $v_vw_statuses) {
    				    $fdoclimit_arr[$k_vw_id]['statuses'] = $v_vw_statuses;
    				}
    				
    				foreach ($fdoclimit as $key => $voluntary_worker_item) {
    				    if (! empty($pat2master[$voluntary_worker_item['id']])) {
    				        $fdoclimit_arr[$voluntary_worker_item['id']]['patients'] = implode(',<br />', $pat2master[$voluntary_worker_item['id']]);
    				    } else {
    				        $fdoclimit_arr[$voluntary_worker_item['id']]['patients'] = '-';
    				    }
    				}
    				
    				$cokoordinators_arr = array(); //this will hold the co-koordinators association with a nicename
    				$vw_cok_obj = new VoluntaryworkersCoKoordinator();
    				$cokoordinators = $vw_cok_obj->get_multiple_cokoordinators_by_vwid($voluntary_workers_ids);
    				if (is_array($cokoordinators) && count($cokoordinators)>0) {
    				    $vw_id_koordinator_arr = array_unique(array_column($cokoordinators, 'vw_id_koordinator'));
    				    
    				    $vw_id_koordinator_arr = Voluntaryworkers::getVoluntaryworkersNiceName($vw_id_koordinator_arr , $clientid);
    				    
    				    foreach ($cokoordinators as $row) {
    				        $cokoordinators_arr[$row['vw_id']] = $vw_id_koordinator_arr[$row['vw_id_koordinator']] ['nice_name'];
    				    }
    				}
    				
    				
    				
    				
    				/*$color_statuses = array(
    				    'g' => 'ready_green',
    				    'y' => 'ready_yellow',
    				    'r' => 'ready_red',
    				    'b' => 'ready_black'
    				);*/
    				
    				$all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
    				foreach ($all_colors as $status_id => $col_status_name){
    				    $color_statuses[$status_id] = 'ready_'.$status_id;
    				}
    				    				
    				
    				$link = "";
    				$resulted_data = array();
    				
    				$Tr = new Zend_View_Helper_Translate();
    				
    				foreach ($fdoclimit_arr as $v_id => $vw_data) {
    				    $link = '%s';
    				    $resulted_data[$vw_data['id']]['status_color'] = $Tr->translate('color_'.$vw_data['status_color']);
    				    $resulted_data[$vw_data['id']]['hospice_association'] = sprintf($link, $vw_data['hospice_association']);
    				    $resulted_data[$vw_data['id']]['status'] = sprintf($link, implode(', ', $vw_data['statuses']));
    				    $resulted_data[$vw_data['id']]['salutation'] = sprintf($link, $vw_data['salutation']);
    				    $resulted_data[$vw_data['id']]['last_name'] = sprintf($link, $vw_data['last_name']);
    				    $resulted_data[$vw_data['id']]['first_name'] = sprintf($link, $vw_data['first_name']);
    				    
    				    if($upcoming_birthdays){
    				        
    				        if($vw_data['birthdate'] != "0000-00-00"){
    				            
    				            $bday = new DateTime($vw_data['birthdate']);
    				            $today = new DateTime(date("Y-m-d")); // for testing purposes
    				            
    				            $diff = $today->diff($bday);
    				            $vw_data['age'] =  $diff->y;
    				            
    				            
    				            //added +1 because it's upcoming, not allready
    				            if((($vw_data['age']+1) % 10) == 0) {
    				                $age = " <b>(".$vw_data['age']." &raquo; ".($vw_data['age']+1).")</b>";
    				                
    				                //added to check if today it's the day
    				            } elseif ((($vw_data['age']) % 10) == 0 && $bday->format('m-d') == $today->format('m-d')){
    				                $age = " <b>(".$vw_data['age'].")</b>";
    				            } else{
    				                $age = " (".$vw_data['age'] . " &raquo; ". ($vw_data['age']+1) . ")";
    				            }
    				            $resulted_data[$vw_data['id']]['birthdate'] =  date('d.m.Y',strtotime($vw_data['birthdate'])).$age;
    				        }else{
    				            $resulted_data[$vw_data['id']]['birthdate'] =  "";
    				            
    				        }
    				        
    				        
    				        //                     if((($vw_data['age'] % 10) == 0)){
    				        //                         $age = " <b>(".$vw_data['age'].")</b>";
    				        //                     } else{
    				        //                         $age = " (".$vw_data['age'].")";
    				        //                     }
    				        
    				        //                     $resulted_data[$vw_data['id']]['birthdate'] = $vw_data['birthdate'].$age;
    				        
    				    } else {
    				        $resulted_data[$vw_data['id']]['birthdate'] = sprintf($link, $vw_data['birthdate']);
    				    }
    				    
    				    $resulted_data[$vw_data['id']]['street'] = sprintf($link, $vw_data['street']);
    				    $resulted_data[$vw_data['id']]['zip'] = sprintf($link, $vw_data['zip']);
    				    $resulted_data[$vw_data['id']]['city'] = sprintf($link, $vw_data['city']);
    				    $resulted_data[$vw_data['id']]['phone'] = sprintf($link, $vw_data['phone']);
    				    $resulted_data[$vw_data['id']]['mobile'] = sprintf($link, $vw_data['mobile']);
    				    $resulted_data[$vw_data['id']]['email'] = sprintf($link, $vw_data['email']);
    				    $resulted_data[$vw_data['id']]['comments'] = sprintf($link, $vw_data['comments']);
    				    $resulted_data[$vw_data['id']]['comments_availability'] = sprintf($link, $vw_data['comments_availability']);    //ISPC-2617 Lore 22.07.2020
    				    $resulted_data[$vw_data['id']]['children'] = sprintf($link, $vw_data['children']);
    				    $resulted_data[$vw_data['id']]['profession'] = sprintf($link, $vw_data['profession']);
    				    $resulted_data[$vw_data['id']]['appellation'] = sprintf($link, $vw_data['appellation']);
    				    $resulted_data[$vw_data['id']]['edication_hobbies'] = sprintf($link, $vw_data['edication_hobbies']);
    				    $resulted_data[$vw_data['id']]['special_skils'] = sprintf($link, $vw_data['special_skils']);
    				    $resulted_data[$vw_data['id']]['gc_certificate_date'] = sprintf($link, $vw_data['gc_certificate_date']);
    				    $resulted_data[$vw_data['id']]['has_car'] = sprintf($link, $vw_data['has_car']);
    				    $resulted_data[$vw_data['id']]['patients'] = sprintf($link, $vw_data['patients']);
    				    $resulted_data[$vw_data['id']]['cokoordinator'] = sprintf($link, $cokoordinators_arr[$vw_data['id']]);
    				    //ISPC - 2231 -p.1+2
    				    if($vw_data['img_deleted'] != 1 && $vw_data['img_path'] != "")
    				    {
    				        $resulted_data[$vw_data['id']]['image'] =  '<img src="'.RES_FILE_PATH.'/icons_system/'.$vw_data['img_path'].'" />';
    				    }
    				    else
    				    {
    				        $resulted_data[$vw_data['id']]['image'] =  '';
    				    }
    				    if($vw_data['change_date'] != "0000-00-00 00:00:00")
    				    {
    				        $resulted_data[$vw_data['id']]['change_date'] =  sprintf($link, date('d.m.Y', strtotime($vw_data['change_date'])));
    				    }
    				    else
    				    {
    				        $resulted_data[$vw_data['id']]['change_date'] =  '';
    				    }
    				    //ISPC - 2231 -p.1+2
    				}
    				return $resulted_data;
    				
		}
		
		// ISPC-2401 pct 1  Lore
		public function get_vws_multiple_details_allyear($vws_ids, $sortby = "first_name", $sortdir = "ASC",$allyear_birthdays=false){
		    
  
	 
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    
		    $voluntary_workers = new Voluntaryworkers();
		    $patient_voluntary_workers = new PatientVoluntaryworkers();
		    
		    $order_by_str = $sortby . ' ' . $sortdir . ' ';
		    
		    // ################################################
		    // get associated clients of current clientid START
		    // ###############################################
		    $logininfo = new Zend_Session_Namespace('Login_Info');
		    $connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
		    if ($connected_client) {
		        $clientid = $connected_client;
		    } else {
		        $clientid = $logininfo->clientid;
		    }
		    // ################################################
		    // get associated clients of current clientid END
		    
		    if ($clientid > 0) {
		        $where = ' and clientid=' . $clientid;
		    } else {
		        $where = ' and clientid=0';
		    }
		    
		    // Hospice association details
		    $h_association = Doctrine_Query::create()->select('*')
		    ->from('Hospiceassociation')
		    ->where('indrop= 0 and isdelete = 0 and clientid=' . $clientid);
		    $h_association_array = $h_association->fetchArray();
		    
		    foreach ($h_association_array as $khas => $h_assoc_item) {
		        $h_assoc_data[$h_assoc_item['id']] = $h_assoc_item['hospice_association'];
		    }
		    
		    $color_firtered_details = VwColorStatuses::get_vw_ids_color_statuses_filter(false, $clientid, true, $filter_color_status_array);
		    
		    $color_filterd_ids = $color_firtered_details['filter_ids'];
		    
		    if (strlen($filter_color_sql) > 0) {
		        $filter_color_sql_final = $filter_color_sql;
		    }
		    
		    // ########################################
		    // ##### Query for details ###############
		    
		    $vw_sql = '*,';
		    $vw_sql .= 'IF(has_car = "1","Ja","Nein") as has_car,';
		    $vw_sql .= '(YEAR(NOW()) - YEAR(birthdate)) as age,';
		    $vw_sql .= " DATEDIFF(birthdate + INTERVAL YEAR(now()) - YEAR(birthdate) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthdate, '%m%d'), 1, 0) YEAR, now()) AS days_to_birthd,";
		    $vw_sql .= "IF(birthdate != '0000-00-00',DATE_FORMAT(birthdate,'%d\.%m\.%Y'),'') as birthdate,";
		    $vw_sql .= "IF((gc_certificate_date != '0000-00-00 00:00:00' && gc_certificate = 2) ,Concat('Ja',' (',DATE_FORMAT(gc_certificate_date,'%d\.%m\.%Y'),')'  ),'Nein') as gc_certificate_date,";
		    
		    //ISPC-1900 order by upcoming birthdays
		    if($allyear_birthdays) {
		        $vw_sql .= " DATE_FORMAT(birthdate, '%m%d') as birthdate_daymonth, ";
		        $vw_sql .= " DATE_FORMAT(birthdate, '%Y') as birthdate_year, ";
		        
		        $order_by_str = "birthdate_daymonth  ASC, birthdate_year ASC, " . $order_by_str;
		    }
		    
		    $fdoc1 = Doctrine_Query::create();
		    $fdoc1->select($vw_sql);
		    $fdoc1->from('Voluntaryworkers');
		    $fdoc1->where("isdelete = 0  " . $where);
		    $fdoc1->andWhere("indrop = 0  ");
		    if($allyear_birthdays){
		        //                $fdoc1->andWhere("date_format( `birthdate` , '%m-%d' ) BETWEEN date_format(now() ,'%m-%d') AND date_format(date_add( now() , INTERVAL 50 DAY ) , '%m-%d' )");
		        $fdoc1->andWhere(" DATEDIFF(birthdate + INTERVAL YEAR(now()) - YEAR(birthdate) + IF(DATE_FORMAT(now(), '%m%d') > DATE_FORMAT(birthdate, '%m%d'), 1, 0) YEAR, now()) < 365");
		    } else{
		        $fdoc1->andWhereIn("id", $vws_ids);
		    }
		    //             $fdoc1->Orderby($sortby.' '.$sortdir);
		    $fdoc1->Orderby($order_by_str);
		             // echo $fdoc1->getSqlQuery(); exit;
		    $fdoclimitexec = $fdoc1->execute();
		    $fdoclimit = Pms_CommonData::array_stripslashes($fdoclimitexec->toArray());
		    //var_dump($allyear_birthdays);
		    
		    //print_R($fdoclimit); exit;
		    
		    $voluntary_workers_ids[] = '99999999999999';
		    foreach ($fdoclimit as $key => $voluntary_worker_item) {
		        $fdoclimit_arr[$voluntary_worker_item['id']] = $voluntary_worker_item;
		        
		        if ($voluntary_worker_item['hospice_association'] > 0) {
		            $fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = $h_assoc_data[$voluntary_worker_item['hospice_association']];
		        } else {
		            $fdoclimit_arr[$voluntary_worker_item['id']]['hospice_association'] = '-';
		        }
		        
		        $voluntary_workers_ids[] = $voluntary_worker_item['id'];
		    }
		    $primary_status_arr = Pms_CommonData::get_primary_voluntary_statuses();
		    $status_arr = Pms_CommonData::getVoluntaryWorkersStatuses();

		    foreach ($status_arr as $k_status => $v_status) {
		        $statuses[$v_status['id']] = $v_status['status'];
		    }
		    //             $this->view->status_array = $statuses;
		    $view = Zend_Layout::getMvcInstance()->getView();
		    $view->status_array = $statuses;
		    
		    
		    foreach ($primary_status_arr as $k_pri_status => $v_pri_status) {
		        $statuses[$v_pri_status['id']] = $v_pri_status['status'];
		    }
		    
		    foreach ($fdoclimit as $keysst => $voluntary_worker_item_cst) {
		        if (! empty($color_firtered_details['statuses'][$voluntary_worker_item_cst['id']])) {
		            $fdoclimit_arr[$voluntary_worker_item_cst['id']]['status_color'] = $color_firtered_details['statuses'][$voluntary_worker_item_cst['id']][0]['status'];
		        } else {
		            $fdoclimit_arr[$voluntary_worker_item_cst['id']]['status_color'] = $voluntary_worker_item_cst['status_color'];
		        }
		    }

		    
		    // get active patients
		    $patietns_act = Doctrine_Query::create()->select("p.ipid,
    				e.epid,
    				AES_DECRYPT(p.first_name,'" . Zend_Registry::get('salt') . "') as first_name,
    				AES_DECRYPT(p.last_name,'" . Zend_Registry::get('salt') . "') as last_name
    				")
    				->from('EpidIpidMapping e')
    				->leftJoin('e.PatientMaster p')
    				->where('e.ipid = p.ipid')
    				->andWhere('e.clientid = "' . $logininfo->clientid . '"')
    				->andWhere('p.isstandby = "0"')
    				->andWhere('p.isdischarged = "0"')
    				->andWhere('p.isdelete = "0"')
    				->andWhere('p.isstandbydelete = "0"');
    				$active_patients = $patietns_act->fetchArray();
    				
    				if (! empty($active_patients)) {
    				    foreach ($active_patients as $k_patient => $v_patient) {
    				        $patients_epids_selector[$v_patient['PatientMaster']['id']] = strtoupper($v_patient['epid']); // used in patients dropdown
    				        $patients_ipids_selector[$v_patient['PatientMaster']['id']] = $v_patient['PatientMaster']['ipid']; // used to match patient_master id with ipid
    				        $active_ipids[] = $v_patient['PatientMaster']['ipid'];
    				        $patient_details[$v_patient['PatientMaster']['ipid']] = $v_patient['PatientMaster']['last_name'] . ' ' . $v_patient['PatientMaster']['first_name'];
    				    }
    				}
    				
    				if (empty($active_ipids)) {
    				    $active_ipids[] = "XXXXXX";
    				}
    				
    				$parent2child = $voluntary_workers->parent2child_workers($clientid, $voluntary_workers_ids);
    				$worker2activepatients = $patient_voluntary_workers->get_workers2patients($parent2child['vw_ids'], $active_ipids, false, $currently_connected = true);
    				
    				foreach ($worker2activepatients as $vwid => $patipid) {
    				    if (! in_array($patient_details[$patipid], $pat2master[$parent2child['vwid2parent'][$vwid]])) {
    				        $pat2master[$parent2child['vwid2parent'][$vwid]][] = $patient_details[$patipid];
    				    }
    				}
    				
    				$voluntary_workers_statuses = new VoluntaryworkersStatuses();
    				$worker_statuses = $voluntary_workers_statuses->get_voluntaryworker_statuses($voluntary_workers_ids, $clientid);
    				
    				foreach ($worker_statuses as $k_data => $v_data) {
    				    foreach ($v_data as $k_vdata => $v_vdata) {
    				        $worker_statuses_arr[$k_data][] = $statuses[$v_vdata];
    				    }
    				}
    				
    				foreach ($worker_statuses_arr as $k_vw_id => $v_vw_statuses) {
    				    $fdoclimit_arr[$k_vw_id]['statuses'] = $v_vw_statuses;
    				}
    				
    				foreach ($fdoclimit as $key => $voluntary_worker_item) {
    				    if (! empty($pat2master[$voluntary_worker_item['id']])) {
    				        $fdoclimit_arr[$voluntary_worker_item['id']]['patients'] = implode(',<br />', $pat2master[$voluntary_worker_item['id']]);
    				    } else {
    				        $fdoclimit_arr[$voluntary_worker_item['id']]['patients'] = '-';
    				    }
    				}
    				
    				$cokoordinators_arr = array(); //this will hold the co-koordinators association with a nicename
    				$vw_cok_obj = new VoluntaryworkersCoKoordinator();
    				$cokoordinators = $vw_cok_obj->get_multiple_cokoordinators_by_vwid($voluntary_workers_ids);
    				if (is_array($cokoordinators) && count($cokoordinators)>0) {
    				    $vw_id_koordinator_arr = array_unique(array_column($cokoordinators, 'vw_id_koordinator'));
    				    
    				    $vw_id_koordinator_arr = Voluntaryworkers::getVoluntaryworkersNiceName($vw_id_koordinator_arr , $clientid);
    				    
    				    foreach ($cokoordinators as $row) {
    				        $cokoordinators_arr[$row['vw_id']] = $vw_id_koordinator_arr[$row['vw_id_koordinator']] ['nice_name'];
    				    }
    				}
    				
    				
    				
    				
    				/*$color_statuses = array(
    				    'g' => 'ready_green',
    				    'y' => 'ready_yellow',
    				    'r' => 'ready_red',
    				    'b' => 'ready_black'
    				);*/
    				
    				$all_colors = VoluntaryworkersColorAliasesTable::get_coloralias_array($clientid);
    				foreach ($all_colors as $status_id => $col_status_name){
    				    $color_statuses[$status_id] = 'ready_'.$status_id;
    				}
    				
    				$link = "";
    				$resulted_data = array();
    				
    				$Tr = new Zend_View_Helper_Translate();
    				
    				foreach ($fdoclimit_arr as $v_id => $vw_data) {
    				    $link = '%s';
    				    $resulted_data[$vw_data['id']]['status_color'] = $Tr->translate('color_'.$vw_data['status_color']);
    				    $resulted_data[$vw_data['id']]['hospice_association'] = sprintf($link, $vw_data['hospice_association']);
    				    $resulted_data[$vw_data['id']]['status'] = sprintf($link, implode(', ', $vw_data['statuses']));
    				    $resulted_data[$vw_data['id']]['salutation'] = sprintf($link, $vw_data['salutation']);
    				    $resulted_data[$vw_data['id']]['last_name'] = sprintf($link, $vw_data['last_name']);
    				    $resulted_data[$vw_data['id']]['first_name'] = sprintf($link, $vw_data['first_name']);
    				    
    				    if($allyear_birthdays){
    				        
    				        if($vw_data['birthdate'] != "0000-00-00"){
    				            
    				            $bday = new DateTime($vw_data['birthdate']);
    				            $today = new DateTime(date("Y-m-d")); // for testing purposes
    				            
    				            $diff = $today->diff($bday);
    				            $vw_data['age'] =  $diff->y;
    				            
    				            
    				            //added +1 because it's upcoming, not allready
    				            if((($vw_data['age']+1) % 10) == 0) {
    				                $age = " <b>(".$vw_data['age']." &raquo; ".($vw_data['age']+1).")</b>";
    				                
    				                //added to check if today it's the day
    				            } elseif ((($vw_data['age']) % 10) == 0 && $bday->format('m-d') == $today->format('m-d')){
    				                $age = " <b>(".$vw_data['age'].")</b>";
    				            } else{
    				                $age = " (".$vw_data['age'] . " &raquo; ". ($vw_data['age']+1) . ")";
    				            }
    				            $resulted_data[$vw_data['id']]['birthdate'] =  date('d.m.Y',strtotime($vw_data['birthdate'])).$age;
    				        }else{
    				            $resulted_data[$vw_data['id']]['birthdate'] =  "";
    				            
    				        }
    				        
    				        
    				        //                     if((($vw_data['age'] % 10) == 0)){
    				        //                         $age = " <b>(".$vw_data['age'].")</b>";
    				        //                     } else{
    				        //                         $age = " (".$vw_data['age'].")";
    				        //                     }
    				        
    				        //                     $resulted_data[$vw_data['id']]['birthdate'] = $vw_data['birthdate'].$age;
    				        
    				    } else {
    				        $resulted_data[$vw_data['id']]['birthdate'] = sprintf($link, $vw_data['birthdate']);
    				    }
    				    
    				    $resulted_data[$vw_data['id']]['street'] = sprintf($link, $vw_data['street']);
    				    $resulted_data[$vw_data['id']]['zip'] = sprintf($link, $vw_data['zip']);
    				    $resulted_data[$vw_data['id']]['city'] = sprintf($link, $vw_data['city']);
    				    $resulted_data[$vw_data['id']]['phone'] = sprintf($link, $vw_data['phone']);
    				    $resulted_data[$vw_data['id']]['mobile'] = sprintf($link, $vw_data['mobile']);
    				    $resulted_data[$vw_data['id']]['email'] = sprintf($link, $vw_data['email']);
    				    $resulted_data[$vw_data['id']]['comments'] = sprintf($link, $vw_data['comments']);
    				    $resulted_data[$vw_data['id']]['comments_availability'] = sprintf($link, $vw_data['comments_availability']);    //ISPC-2617 Lore 22.07.2020
    				    $resulted_data[$vw_data['id']]['children'] = sprintf($link, $vw_data['children']);
    				    $resulted_data[$vw_data['id']]['profession'] = sprintf($link, $vw_data['profession']);
    				    $resulted_data[$vw_data['id']]['appellation'] = sprintf($link, $vw_data['appellation']);
    				    $resulted_data[$vw_data['id']]['edication_hobbies'] = sprintf($link, $vw_data['edication_hobbies']);
    				    $resulted_data[$vw_data['id']]['special_skils'] = sprintf($link, $vw_data['special_skils']);
    				    $resulted_data[$vw_data['id']]['gc_certificate_date'] = sprintf($link, $vw_data['gc_certificate_date']);
    				    $resulted_data[$vw_data['id']]['has_car'] = sprintf($link, $vw_data['has_car']);
    				    $resulted_data[$vw_data['id']]['patients'] = sprintf($link, $vw_data['patients']);
    				    $resulted_data[$vw_data['id']]['cokoordinator'] = sprintf($link, $cokoordinators_arr[$vw_data['id']]);
    				    //ISPC - 2231 -p.1+2
    				    if($vw_data['img_deleted'] != 1 && $vw_data['img_path'] != "")
    				    {
    				        $resulted_data[$vw_data['id']]['image'] =  '<img src="'.RES_FILE_PATH.'/icons_system/'.$vw_data['img_path'].'" />';
    				    }
    				    else
    				    {
    				        $resulted_data[$vw_data['id']]['image'] =  '';
    				    }
    				    if($vw_data['change_date'] != "0000-00-00 00:00:00")
    				    {
    				        $resulted_data[$vw_data['id']]['change_date'] =  sprintf($link, date('d.m.Y', strtotime($vw_data['change_date'])));
    				    }
    				    else
    				    {
    				        $resulted_data[$vw_data['id']]['change_date'] =  '';
    				    }
    				    //ISPC - 2231 -p.1+2
    				}
    				return $resulted_data;
    				
		}
		
    	
    	public static function get_koordinators( $clientid = 0 )
    	{
    			
    		$q = Doctrine_Query::create()
    		->select('id, last_name, first_name')
    		->from('Voluntaryworkers')
			->where('clientid = ?', $clientid)
    		->andWhere('isdelete = 0')
    		->andWhere('indrop = 0')
    		->andWhere('status = ?', 'k')
    		->orderBy('last_name ASC')
    		->fetchArray();
    		    	
    		return $q;
    	
    	}
	
    	/**
    	 * 
    	 * example:
		 * Voluntaryworkers::getVoluntaryworkersNiceName(array(1,2,3) , 1)
		 * Voluntaryworkers::getVoluntaryworkersNiceName(array(1,2,3) , 1, "*")
		 * Voluntaryworkers::getVoluntaryworkersNiceName(array(1,2,3) , 1, "create_date, change_date")
		 * Voluntaryworkers::getVoluntaryworkersNiceName(array(1,2,3) , 1, array("create_date, change_date"))
		 * 
    	 * Jul 24, 2017 @claudiu 
    	 * 
    	 * @param array $vw_id_arr
    	 * @param number $clientid
    	 * @param string|array $extra_columns
    	 * @return boolean|multitype:Ambigous <multitype:, Doctrine_Collection>
    	 */
    	public static function getVoluntaryworkersNiceName( $vw_id_arr = array(), $clientid = 0, $extra_columns = null)
    	{
    		if( empty($vw_id_arr) || ! is_array($vw_id_arr)) {
    			return false;
    		}
    		
    	   if (empty($clientid)) {
		        $logininfo = new Zend_Session_Namespace('Login_Info');
		        $clientid = $logininfo->clientid;
		    }
    		
    		$vw_id_arr = array_values(array_unique($vw_id_arr));
    		
    		$extra_columns_sql = '';
    		if( ! empty($extra_columns) ) {
    			if (is_array($extra_columns)) {
    				$extra_columns = implode(", ", $extra_columns) ;
    			}
    			$extra_columns_sql = $extra_columns . ", ";
    		}
    			
    		$vwarray = Doctrine_Query::create()
    		->select($extra_columns_sql . 'id, salutation, last_name, first_name, inactive, email, isdelete')
    		->from('Voluntaryworkers')
    		->whereIn('id', $vw_id_arr)
    		->andWhere('clientid = ?' , (int)$clientid )
    		->fetchArray();	
    		
    		self::beautifyName($vwarray);
    		
    		$vw_names_array =  array();
    		
    		foreach( $vwarray as $row ) {
    			$vw_names_array [ $row['id'] ] = $row ;
    		}
    		
    		return $vw_names_array;
    	}
    	
    	public static function beautifyName( &$vwarray )
    	{
    		//mb_convert_case(nice_name, MB_CASE_TITLE, 'UTF-8'); ?
    		foreach ( $vwarray as &$k )
    		{
    			if ( ! is_array($k) || isset($k['nice_name'])) {
    				continue; // varaible allready exists, use another name for the variable
    			}

    			if (isset($k['Voluntaryworkers'])){
    			    $k['nice_name']  =  trim( $k['Voluntaryworkers']['salutation']) != "" ? (trim($k['Voluntaryworkers']['salutation']) . " ") : "";
    			    $k['nice_name']  .= trim( $k['Voluntaryworkers']['last_name']);
    			    $k['nice_name']  .= trim( $k['Voluntaryworkers']['first_name']) != "" ? (", " . trim($k['Voluntaryworkers']['first_name'])) : "";
    			    
    			} else {
        			$k['nice_name']  =  trim( $k['salutation']) != "" ? (trim($k['salutation']) . " ") : "";
        			$k['nice_name']  .= trim( $k['last_name']);
        			$k['nice_name']  .= trim( $k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
    			}
    		}
    	}
    	
    	public function get_all_parent2child_workers_ids($clientid = 0 )
    	{
    		$result = array();
    		
    		$drop = Doctrine_Query::create()
    		->select('id, parent_id')
    		->from('Voluntaryworkers')
    		->where("clientid= ?" , $clientid )
    		->andWhere("isdelete = 0")
    		->andWhere("indrop = 1")
    		->andWhere("parent_id != 0")
    		->fetchArray();
    	
    		foreach($drop as $k => $val)
    		{
    			$result['parent2vwid'][$val['parent_id']][] = $val['id'];
    			$result['vwid2parent'][$val['id']] = $val['parent_id'];
    			$result['vw_ids'][] = $val['id'];
    		}
    		return $result;
    	}
    	 
    	/**
    	 * fn is first time used in vw email history datatable filter
    	 * Jul 24, 2017 @claudiu 
    	 * 
    	 * @param string $search_string
    	 * @param number $clientid
    	 * @return array of vw ids
    	 */
    	public static function search_vwids( $search_string = "", $clientid = 0)
    	{
    		$result = array();
    			
    		$search_string = trim($search_string);
    		
    		$searchstr = "%".$search_string . "%";

    			
    		$q_ids = Doctrine_Query::create()
    		->select('id')
    		->from('Voluntaryworkers')
    		->where('clientid = :clientid' )

    		->andWhere('CONVERT(CAST(CONCAT_WS(
					" ",
					salutation,
					title,
					first_name,
					last_name,
    				first_name,
					email					
    				) as BINARY) USING utf8 ) COLLATE utf8_general_ci
    		
					LIKE CONVERT(CAST(:searchstr as BINARY) USING utf8)');
    		
    		
    		
//     		Pms_CommonData::value_patternation($search_string);
//     		$regexp = mb_strtolower($search_string, 'UTF-8');
//     		->andWhere('LOWER(CONVERT(CONCAT_WS(
// 					" ",
// 					salutation,
// 					title,
// 					first_name,
// 					last_name,
// 					birthdate,
// 					street,
// 					zip,
// 					city,
// 					phone,
// 					mobile,
// 					email,
// 					comments,
// 					comments_availability,
// 					children,
// 					profession   				
//     				) USING utf8 )) COLLATE utf8_general_ci
    				
// 					REGEXP :reg_exp');
    		
    		$q_ids = $q_ids->fetchArray( array(
    						"clientid"	=> $clientid,
//     						"reg_exp"	=> $regexp,
    						"searchstr"	=> $searchstr
    				));
    		
    							
    		if (! empty($q_ids)) {
    			$result =  array_column($q_ids, 'id');
    		}
    							
    		return $result;
    	
    	}
    	
    	
    	/**
    	 * TODO : add $connected_client = VwGroupAssociatedClients::connected_parent($logininfo->clientid);
    	 * 
    	 * @param string $str
    	 * @param string $clientid
    	 * @param string $sadmin
    	 * @return boolean|Ambigous <multitype:, Doctrine_Collection>
    	 */
    	public static function livesearch_voluntaryworkers($str = '', $clientid = null, $limit = 100)
    	{
    	    if ( empty($clientid)){
    	        $logininfo = new Zend_Session_Namespace('Login_Info');
    	        $clientid = $logininfo->clientid;
    	    }
    	    
    	    //TODO-3763 Ancuta 19.01.2021
    	    $connected_client = VwGroupAssociatedClients::connected_parent($clientid);
    	    if ($connected_client) {
    	        $clientid = $connected_client;
    	    } else {
    	        $clientid = $clientid;
    	    }
    	    //--
    	    
    	    $str = trim($str);
    	
    	
    	    if (empty($str)) {
    	        return false;
    	    }
    	
    	    Pms_CommonData::value_patternation($str);
    	
    	    $usr = Doctrine_Query::create()
    	    ->select('*')
    	    ->from('Voluntaryworkers')
    	    ->where("clientid = ?", $clientid)
    	    ->andWhere('isdelete = 0 ')
    	    ->andWhere('indrop = 0 ')
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
    	
	}

?>