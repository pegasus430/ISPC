<?php
// Maria:: Migration ISPC to CISPC 08.08.2020
	Doctrine_Manager::getInstance()->bindComponent('PatientLocation', 'IDAT');

	class PatientLocation extends BasePatientLocation {

		public $triggerformid = 5;
		public $triggerformname = "frmpatientlocation";

		public function getLastLocation($pid)
		{
			$epid = Pms_CommonData::getEpidFromId($pid);
			$ipid = Pms_CommonData::getIpId($pid);

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $ipid . '" and isdelete="0"')
				->where('ipid =?', $ipid)
				->andWhere('isdelete="0"')
				->limit(1)
				->orderBy('id DESC');
			$patlocs = $patloc->execute();

			if($patlocs)
			{
				$patlocarray = $patlocs->toArray();
				return $patlocarray;
			}
		}

		public function getFirstLocation($pid = false, $p_ipid = false)
		{
			if($pid)
			{
				$epid = Pms_CommonData::getEpidFromId($pid);
				$ipid = Pms_CommonData::getIpId($pid);
			}
			else
			{
				$ipid = $p_ipid;
			}

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $ipid . '"  and isdelete="0"')
				->where('ipid =?', $ipid)
				->andWhere('isdelete="0"')
				->limit(1)
				->orderBy('id ASC');
			$patlocs = $patloc->execute();

			if($patlocs)
			{
				$patlocarray = $patlocs->toArray();
				return $patlocarray;
			}
		}

		public function getOrderbyLocation($patienidtarray, $clientid)
		{
			$loc = new Locations();
			$locationsall = $loc->getLocations($clientid, 2);
			
			//TODO-3501 Ancuta 14.10.2020
			$location_data = array();
			$locationsall_data = $loc->getLocations($clientid);
			foreach($locationsall_data as $k=>$loc_data){
			    $location_data[$loc_data['id']]['name'] = $loc_data['location'];
			    
   			    $address_str = "";
			    if(!empty($loc_data['street'])){
    			    $address_str = "<br/>";
    			    $address_str .= $loc_data['street'].'<br/>';
    			    
    			    if(!empty($loc_data['zip'])){
                        $address_str .= $loc_data['zip'];
    			    }
    			    
    			    if(!empty($loc_data['city'])){
        			    $address_str .= ', '.$loc_data['city'];
    			    }
			    }
			    $location_data[$loc_data['id']]['address'] = $address_str;
			}
			// -- 
			
			
			$locations_types = $loc->getLocations($clientid, 3);

			foreach($patienidtarray as $patientid)
			{
				$patientids[$patientid['ipid']] = $patientid['ipid'];
			}

			$contact = new ContactPersonMaster();
			$contactpersons_loc_array = $contact->get_contact_persons_by_ipids($patientids, false, false); //get_contact_persons_by_ipids ( $ipids_array = false, $group_by = false, $hide_deleted = true )

			$patlocs = $this->getActiveLocations($patienidtarray);
// 			print_R($patlocs); exit;
			
			foreach($patlocs as $k=>$std){
			    $staion_ids[] =$std['station']; 
			}
			if(empty($staion_ids)){
			    $staion_ids[] = "XXXXXXX";
			}
			//get location station if available
			if(!empty($staion_ids))
			{
			    $loc_stations = new LocationsStations();
			    $location_station = $loc_stations->getLocationsStationsByIds($clientid, false, $staion_ids);
			}
			
			
			$patient2home = array();
			foreach($patlocs as $location_id => $patientlocation)
			{
				$locid = substr($patientlocation['location_id'], 0, 4);
				if($locid == "8888")
				{
					//check if location it is "contact"
					$z = 1;
					// display contact number
					$cnt_number = 1;
					foreach($contactpersons_loc_array[$patientlocation['ipid']] as $k => $value)
					{
						if($value['isdelete'] == '0')
						{
							$locationsarray[$patientlocation['ipid']]['8888' . $z] = 'bei Kontaktperson '.$cnt_number.'('.$value['cnt_last_name'].', '.$value['cnt_first_name'].')';
							//TODO-3831 Ancuta 08.02.2021
							$locationsarray[$patientlocation['ipid']]['8888' . $z] .= (strlen($value['cnt_street1'])>0) ? "<br/>".$value['cnt_street1'].'<br/>' : "";
						    $locationsarray[$patientlocation['ipid']]['8888' . $z] .= (strlen($value['cnt_zip']) > 0) ? $value['cnt_zip'].',' : "";
						    $locationsarray[$patientlocation['ipid']]['8888' . $z] .= (strlen($value['cnt_city']) > 0 ) ? " ".$value['cnt_city'] : "";
							//-- 
							$cnt_number++;
						}
						else
						{
							$locationsarray[$patientlocation['ipid']]['8888' . $z] = 'bei Kontaktperson';
						}
						$z++;
					}
					$patient2location[$patientlocation['ipid']] = $locationsarray[$patientlocation['ipid']][$patientlocation['location_id']];
					$patient2location_id[$patientlocation['ipid']] = $patientlocation['location_id'];
				}
				else if(array_key_exists($patientlocation['location_id'], $locationsall) && $locations_types[$patientlocation['location_id']] == "5")
				{
					$patient2location[$patientlocation['ipid']] = $locationsall[$patientlocation['location_id']];
					$patient2location_id[$patientlocation['ipid']] = $patientlocation['location_id'];
					$patient2home[] = $patientlocation['ipid'];
				}
				else if(array_key_exists($patientlocation['location_id'], $locationsall) && $locations_types[$patientlocation['location_id']] != "5")
				{
				    $patient2location[$patientlocation['ipid']] = $locationsall[$patientlocation['location_id']].''.$location_data[$patientlocation['location_id']]['address'];//TODO-3501 Ancuta 14.10.2020 Added Address
					$patient2location_id[$patientlocation['ipid']] = $patientlocation['location_id'];
				}
				
				// location comment
    			$patient2commlocation[$patientlocation['ipid']]= $patientlocation['comment'];
    			
    			// staion
    			if($patientlocation['station'] != '0' && !empty($location_station[$patientlocation['station']]))
    			{
        			$patient2station[$patientlocation['ipid']]= $location_station[$patientlocation['station']]['station'];
    			}
			
			}

			// @TODO optimize me
			foreach($locationsall as $loc_id => $locorder)
			{
				foreach($patlocs as $patloc)
				{
					if($patloc['location_id'] == $loc_id)
					{
						$orderbylocation[] = $patloc['ipid'];
					}
				}
			}
			$return['orderbylocation'] = $orderbylocation;
			$return['patient2location'] = $patient2location;
			$return['patient2location_id'] = $patient2location_id;
			$return['patient2home'] = $patient2home;
			$return['patient2commlocation'] = $patient2commlocation;
			$return['patient2station'] = $patient2station;

			return $return;
		}

		public function old_getOrderbyLocation($patienidtarray, $clientid)
		{
			$loc = new Locations();
			$locationsall = $loc->getLocations($clientid, 2);

			foreach($patienidtarray as $patientid)
			{
				$patientids[$patientid['ipid']] = $patientid['ipid'];
			}

			$patlocs = $this->getActiveLocations($patienidtarray);


			foreach($patlocs as $location_id => $patientlocation)
			{
				if(array_key_exists($patientlocation['location_id'], $locationsall))
				{
					$patient2location[$patientlocation['ipid']] = $locationsall[$patientlocation['location_id']];
				}
			}

			// @TODO optimize me
			foreach($locationsall as $loc_id => $locorder)
			{
				foreach($patlocs as $patloc)
				{
					if($patloc['location_id'] == $loc_id)
					{
						$orderbylocation[] = $patloc['ipid'];
					}
				}
			}
			$return['orderbylocation'] = $orderbylocation;
			$return['patient2location'] = $patient2location;
			$return['patient2zuHouse'] = $patient2location;

			return $return;
		}

		public function getActiveLocation($pid)
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $pid . '"  and isdelete="0"')
				->where('ipid =?', $pid)
				->andWhere('isdelete="0"')
				->limit(1)
				->orderBy('id DESC')
				->andWhere("valid_till='0000-00-00 00:00:00'");
			$patlocs = $patloc->execute();

			if($patlocs)
			{
				$patlocarray = $patlocs->toArray();
				$loc = new Locations();
				$locarray = $loc->getLocationbyId($patlocarray[0]['location_id']);

				$loc = $locarray[0]['location'];
				return $loc;
			}
		}

		public function getActiveLocationPatInfo($pid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $pid . '"  and isdelete="0"')
				->where('ipid =?', $pid)
				->andWhere('isdelete="0"')
				->limit(1)
				->orderBy('id DESC')
				->andWhere("valid_till='0000-00-00 00:00:00'");
			$patlocarray = $patloc->fetchArray();

			if($patlocarray)
			{
				//get location station if available
				if($patlocarray[0]['station'] != '0')
				{
					$loc_stations = new LocationsStations();
					$location_station = $loc_stations->getLocationsStationsById($clientid, false, $patlocarray[0]['station']);

					foreach($location_station as $v_loc_station)
					{
						$active_location_station[$v_loc_station['location_id']] = $v_loc_station;
					}
				}

				$locid = substr($patlocarray[0]['location_id'], 0, 4);
				if($locid == "8888")
				{
					$cpr = new ContactPersonMaster();
					$cprarr = $cpr->getPatientContact($pid, false);
					$z = 1;
					$cnt_number = 1; // display contact number
					foreach($cprarr as $value)
					{
						if($value['isdelete'] == '0')
						{
							$locationsarray['8888' . $z]['location'] = 'bei Kontaktperson '.$cnt_number.' ('.$value['cnt_last_name'].', '.$value['cnt_first_name'].')';
							$locationsarray['8888' . $z]['street'] = $value['cnt_street1'];
							$locationsarray['8888' . $z]['zip'] = $value['cnt_zip'];
							$locationsarray['8888' . $z]['city'] = $value['cnt_city'];
							$locationsarray['8888' . $z]['phone'] = $value['cnt_phone'];
							$locationsarray['8888' . $z]['mobile'] = $value['cnt_mobile'];
							$cnt_number++;
						}
						else
						{
							$locationsarray['8888' . $z]['location'] = 'bei Kontaktperson';
						}
						$z++;
					}
					$locarray[0] = $locationsarray[$patlocarray[0]['location_id']];
					$locarray[0]['pat_loc_comment'] = $patlocarray[0]['comment'];
				}
				else
				{
					$loc = new Locations();
					$locarray = $loc->getLocationbyId($patlocarray[0]['location_id']);

					if($patlocarray[0]['station'] != '0' && !empty($active_location_station))
					{
						$locarray[0]['station'] = $active_location_station[$patlocarray[0]['location_id']];
					}
					$locarray[0]['pat_loc_comment'] = $patlocarray[0]['comment'];
				}
				return $locarray;
			}
		}

		public function getActiveLocationPatInfoold($pid)
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $pid . '"  and isdelete="0"')
				->where('ipid =?', $pid)
				->andWhere('isdelete="0"')
				->limit(1)
				->orderBy('id DESC')
				->andWhere("valid_till='0000-00-00 00:00:00'");
			$patlocs = $patloc->execute();

			if($patlocs)
			{
				$patlocarray = $patlocs->toArray();
				$loc = new Locations();
				$locarray = $loc->getLocationbyId($patlocarray[0]['location_id']);


				return $locarray;
			}
		}

		/* get active locations for an array of patients */

		public function getActiveLocations($pids)
		{
			foreach($pids as $pid)
			{
				$pid_str .= '"' . $pid['ipid'] . '",';
				$ipidz[$pid['ipid']] = $pid;
				$ipidz_simple[] = $pid['ipid'];
			}

			$patloc = Doctrine_Query::create()
				->select('location_id,ipid,comment,station')
				->from('PatientLocation')
				->where('isdelete="0"')
				->andWhereIn('ipid', $ipidz_simple)
				->andWhere("valid_till='0000-00-00 00:00:00'")
				->orderBy('id DESC');

			$patlocs = $patloc->fetchArray();

			return $patlocs;
		}

		private function get_raw_sql($query) {
			if(!($query instanceof Doctrine_Query)) {
				throw new Exception('Not an instanse of a Doctrine Query');
			}
		
			$query->limit(0);
		
			if(is_callable(array($query, 'buildSqlQuery'))) {
				$queryString = $query->buildSqlQuery();
				$query_params = $query->getParams();
				$params = $query_params['where'];
			} else {
				$queryString = $query->getSql();
				$params = $query->getParams();
			}
		
			$queryStringParts = split('\?', $queryString);
			$iQC = 0;
		
			$queryString = "";
		
			foreach($params as $param) {
				if(is_numeric($param)) {
					$queryString .= $queryStringParts[$iQC] . $param;
				} elseif(is_bool($param)) {
					$queryString .= $queryStringParts[$iQC] . $param*1;
				} else {
					$queryString .= $queryStringParts[$iQC] . '\'' . $param . '\'';
				}
		
				$iQC++;
			}
			for($iQC;$iQC < count($queryStringParts);$iQC++) {
				$queryString .= $queryStringParts[$iQC];
			}
		
			echo $queryString."\n\n\n";
		}
		
		//ispc-1533
		public function get_all_location_from_day( $pids , $day,  $clientid)
		{
			if (!is_array($pids)){
				$pids = array($pids);
				if(count($pids) == 0)
				{
					$pids[] = '99999999999';
				}
			}


			$patloc = Doctrine_Query::create()
			->select('location_id, ipid ')
			->from('PatientLocation')
			->where('isdelete="0"')
			->andWhereIn('ipid', $pids)			
			->andWhere("DATE(`valid_from`) <= DATE('".$day."')")
			->andWhere(" (`valid_till` = '0000-00-00 00:00:00' OR DATE(`valid_till`) >= DATE('".$day."') ) ");
			$patlocs = $patloc->fetchArray();
			
		
			return $patlocs;
		}
		
		//ispc-1533
		//$date_interval = array( "start"=>date("Y-m-d"), "end"=>date("Y-m-d"))
		public function get_all_location_by_date_interval( $pids , $date_interval = array( "start"=> false, "end"=>false ) ,  $clientid)
		{
			if (!isset($date_interval['start'], $date_interval['end']) ) return false;
			
			if (!is_array($pids)){
				$pids = array($pids);
				if(count($pids) == 0)
				{
					$pids[] = '99999999999';
				}
			}
		
		
			$patloc = Doctrine_Query::create()
			->select('location_id, ipid , "'.$day.'" as myday ,  valid_from, valid_till')
			->from('PatientLocation')
			->where('isdelete="0"')
			->andWhereIn('ipid', $pids)
			
			->andWhere( "(( DATE(valid_from) <= DATE('".$date_interval['start']."') OR DATE(valid_from) BETWEEN DATE('".$date_interval['start']."') and DATE('".$date_interval['end']."') )"
						." AND " 
						."( DATE(valid_till) BETWEEN DATE('".$date_interval['start']."') and DATE('".$date_interval['end']."') OR	DATE(valid_till) >= DATE('".$date_interval['end']."') OR DATE(valid_till) = '0000-00-00 00:00:00' ))")

			->orderBy("id DESC");
			//echo $patloc->getSqlQuery();
			$patlocs = $patloc->fetchArray();
				
		
			return $patlocs;
		}
				
		public function getActiveLocationOpt($pid, $locationnames = '')
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $pid . '"  and isdelete="0"')
				->where('ipid =?', $pid)
				->andWhere('isdelete="0"')
				->andWhere("valid_till='0000-00-00 00:00:00'")
				->limit(1)
				->orderBy('id DESC');
			$patlocarray = $patloc->fetchArray();
			return $patlocarray;
		}

		public function getLastLocationData($pid)
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $pid . '"  and isdelete="0"')
				->where('ipid =?', $pid)
				->andWhere('isdelete="0"')
				->andWhere("valid_till='0000-00-00 00:00:00'")
				->limit(1)
				->orderBy('id DESC');
//			echo $patloc->getSqlQuery();
			$patlocs = $patloc->execute();

			if($patlocs)
			{
				$patlocarray = $patlocs->toArray();
				return $patlocarray;
			}
		}

		public function getLastLocationDataFromAdmissionUpdate($pid)
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $pid . '"  and isdelete="0"')
				->where('ipid =?', $pid)
				->andWhere('isdelete="0"')
				->limit(1)
				->orderBy('id DESC');
			$patlocs = $patloc->execute();

			if($patlocs)
			{
				$patlocarray = $patlocs->toArray();
				return $patlocarray;
			}
		}

		/**
		 * 
		 * @param int $pid
		 * @param int $hydrationMode
		 * @return array|Doctrine_Collection
		 */
		public function getLocationById($pid , $hydrationMode = Doctrine_Core::HYDRATE_ARRAY)
		{
			$patloc = Doctrine_Query::create()
			->select('*')
			->from('PatientLocation')
			//->where('id ="' . $pid . '"  and isdelete="0"');
			->where('id = ?', $pid)
			->andWhere('isdelete = 0')
		    ->execute(null, $hydrationMode);

			if($patloc)
			{
				return $patloc;
			}
		}

		/**
		 * 
		 * @param string $fromdate
		 * @param string $ipid
		 * @param unknown $hydrationMode
		 * @return array|Doctrine_Collection
		 */
		public function getNextLocation($fromdate, $ipid, $hydrationMode = Doctrine_Core::HYDRATE_ARRAY)
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('valid_from>?', $fromdate)
				->andWhere('ipid =? and isdelete=?', array($ipid, "0"))
				->limit(1)
				->orderBy('valid_from');
			$patlocs = $patloc->execute(null, $hydrationMode);

			if($patlocs)
			{
// 				$patlocarray = $patlocs->toArray();
// 				return $patlocarray;
			    return $patlocs;
			}
		}

		public function getReasonsold()
		{
			$Tr = new Zend_View_Helper_Translate();
			$blank = $Tr->translate('selectreason');

			$titlearray = array();
			$titlearray = array(
				'' => $blank,
				'1' => 'Notfall',
				'2' => 'palliative Chemo/Radiatio',
				'3' => 'unbekannt',
				'4' => 'Sonstiges',
				'5' => 'Sturzereignis/Verletzung',
				'6' => 'psychosoz. Gründe',
				'7' => 'Atemnot',
				'8' => 'Schmerz ',
				'9' => 'palliativer Eingriff',
				'10' => 'Bluttransfusion'
			);

			return $titlearray;
		}
		
		public function getReasons($clientid = false)
		{
			if(empty($clientid))
			{
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$clientid = $logininfo->clientid;
			}
			
			$Tr = new Zend_View_Helper_Translate();
			$blank = $Tr->translate('selectreason');
			
			$titlearray = array();
			
			$hr = new HospitalReasons;
			$hrc = $hr->getclienthospreasons($clientid, true);
				
			$titlearray = array('' => $blank);
			foreach($hrc as $khrc=>$vhrc)
			{
				$titlearray[$vhrc['id']] = $vhrc['reason'];
			}
		
			return $titlearray;
		}

		public function getHospDocs()
		{
			$Tr = new Zend_View_Helper_Translate();
			$blank = $Tr->translate('selectdoctor');

			$titlearray = array();
			$titlearray = array(
				'' => $blank,
				'1' => 'teil.HA oder Facharzt',
				'2' => 'QPA',
				'3' => 'Notarzt',
				'4' => 'unbekannt',
				'5' => 'SAPV',
				'6' => 'Selbsteinweisung / Krankenhausarzt'
			);
			return $titlearray;
		}

		public function getTransports()
		{
			$Tr = new Zend_View_Helper_Translate();
			$blank = $Tr->translate('selecttransport');

			$titlearray = array();
			$titlearray = array(
				'' => $blank,
				'1' => 'KTW',
				'2' => 'RTW',
				'3' => 'TAXI',
				'4' => 'Privatwagen',
				'5' => 'unbekannt'
			);
			return $titlearray;
		}

		public function getpatientLocation($ipid)
		{
			$loca = Doctrine::getTable('PatientLocation')->findBy('ipid', $ipid);
			$locaarray = $loca->toArray();

			if(is_array($locaarray))
			{
				return $locaarray;
			}
		}

		public function getPatientLocations($ipid, $master_data = false)
		{
		    /* ISPC-1775,ISPC-1678 */
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $ipid . '"  and isdelete="0"')
				->where('ipid =?', $ipid)
				->andWhere('isdelete="0"')
				->orderBy('valid_from,id ASC');
			$patient_locations_res = $patloc->fetchArray();
			
			if($patient_locations_res)
			{
				if($master_data)
				{
					//$patient_locations_ids[] = '99999999999';
					foreach($patient_locations_res as $k_res => $v_res)
					{
						$patient_locations_ids[] = $v_res['location_id'];
					}
					$patient_locations_ids = array_values(array_unique($patient_locations_ids));

					$locmaster = Doctrine_Query::create()
						->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
						->from('Locations')
						->whereIn('id', $patient_locations_ids)
						->orderBy('id ASC');
					$locations_master_res = $locmaster->fetchArray();

					foreach($locations_master_res as $k_lmaster => $v_lmaster)
					{
						$master_locations[$v_lmaster['id']] = $v_lmaster;
					}

					
					$locid = '';
					//TODO-3413 Ancuta 25.11.2020 
					$patientmaster = new PatientMaster();
					//--
					foreach($patient_locations_res as $k_res_pat => $v_res_pat)
					{
						$patient_locations[$k_res_pat] = $v_res_pat;
						
						$locid = substr($v_res_pat['location_id'], 0, 4);
						if($locid == "8888")
						{
						    $patient_locations[$k_res_pat]['master_location']['location_type'] = "6";
						} else {
    						$patient_locations[$k_res_pat]['master_location'] = $master_locations[$v_res_pat['location_id']];
						}
						
						//TODO-3413 Ancuta 25.11.2020 Change wohnsituation take "location at day of admission"::  GEt days of locations
						if($v_res_pat['valid_till'] == '0000-00-00 00:00:00')
						{
						    $v_res_pat['valid_till'] = date("Y-m-d H:i:s");
						}
						
						$patient_locations[$k_res_pat]['days']= $patientmaster->getDaysInBetween(date("Y-m-d", strtotime($v_res_pat['valid_from'])), date("Y-m-d", strtotime($v_res_pat['valid_till'])),false,'d.m.Y' );
                        // --						
						
					}

					return $patient_locations;
				}
				else
				{
					return $patient_locations_res;
				}
			}
		}

		public function getPatientLocationsNotarz($ipid)
		{

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid =?', $ipid)
				->andWhere('isdelete="0"')
				->andWhere('hospdoc="3"')
				->orderBy('valid_from,id ASC');
//			print_r($patloc->getSqlQuery());
			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				return $patlocs;
			}
		}

        /**
         * ISPC-2391, elena, 10.09.2020
         *
         * @param $ipid
         * @return array
         */
		public static function getPatientsLocationsNotarztHospital($clientid)
		{
            $hospital_locations = Locations::get_locationByClientAndTypes($clientid, [1]); // 1 is hospital
            $hospital_locations_ids = [];
            foreach($hospital_locations as $location){
                $hospital_locations_ids[] = $location['id'];
            }

		    $patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('isdelete="0"')
                ->andWhereIn('location_id', $hospital_locations_ids)
				->andWhere('hospdoc="3"')
				->orderBy('valid_from,id ASC');
//			print_r($patloc->getSqlQuery());
			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				return $patlocs;
			}
		}

        /**
         * ISPC-2391, elena, 10.09.2020
         *
         * @param $ipid
         * @return array
         */
		public static function getPatientsLocationsHospital($clientid)
		{
            $hospital_locations = Locations::get_locationByClientAndTypes($clientid, [1]); // 1 is hospital
            $hospital_locations_ids = [];
            foreach($hospital_locations as $location){
                $hospital_locations_ids[] = $location['id'];
            }

		    $patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('isdelete="0"')
                ->andWhereIn('location_id', $hospital_locations_ids)
				->orderBy('valid_from,id ASC');
//			print_r($patloc->getSqlQuery());
			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				return $patlocs;
			}
		}

		public function GetTreatedDays($admission, $discharge, $isArray = NULL)
		{
			$Tr = new Zend_View_Helper_Translate();
			$trans_days = $Tr->translate('days');
			$trans_months = $Tr->translate('months');
			$trans_year = $Tr->translate('year');
			list($addYear, $addMonth, $addDay) = explode("-", date("Y-m-d", strtotime($admission)));

			list($disYear, $disMonth, $disDay) = explode("-", date("Y-m-d", strtotime($discharge)));

			if($disMonth < 1)
			{
				$diffstr = '-';
				return $diffstr;
			}

			$distime = mktime(23, 59, 59, $disMonth, $disDay, $disYear);

			$addtime = mktime(0, 0, 0, $addMonth, $addDay, $addYear);



			$years = (int) ($months / 12);

			if($addDay > $disDay)
			{
				$months = $this->diff_in_months($admission, $discharge, -1);
				$months = $months < 0 ? 0 : $months;
				$years = (int) ($months / 12);
				$months = $months % 12;
				$newtime = mktime(0, 0, 0, $addMonth + $months, $addDay, $addYear + $years);
				$days = date("t", $newtime) - date("d", $newtime) + date("d", $distime) + 1;
			}
			else
			{
				$months = $this->diff_in_months($admission, $discharge);
				$months = $months < 0 ? 0 : $months;
				$years = (int) ($months / 12);
				$months = $months % 12;
				$newtime = mktime(0, 0, 0, $addMonth + $months, $addDay, $addYear + $years);
				$days = date("d", $distime) - date("d", $newtime);
			}

			if($addDay == $disDay)
			{
				$days++;
			}

			$diffarr = array();
			$diffarr['years'] = 0;
			$diffarr['months'] = 0;
			$diffarr['days'] = 0;
			if($years > 0)
			{
				$diffstr .= $years . " " . $trans_year . " ,";
				$diffarr['years'] = $years;
			}
			if($months > 0)
			{
				$diffstr .= $months . " " . $Tr->translate('months') . " ,";
				$diffarr['months'] = $months;
			}

			if($days > 0)
			{
				$diffstr .= $days . " " . $trans_days;
				$diffarr['days'] = $days;
			}

			if($isArray == 1)
			{
				return $diffarr;
			}


			return $diffstr; // ."&nbsp;Jahre";
		}

		private function diff_in_months($start_date, $end_date, $additional_months = 0)
		{
			$start_year = date("Y", strtotime($start_date));
			$start_month = date("n", strtotime($start_date));

			$end_year = date("Y", strtotime($end_date));
			$end_month = date("n", strtotime($end_date));

			$diff_in_months = (($end_year - $start_year) * 12 ) - $start_month + $end_month + $additional_months;


			return $diff_in_months;
		}

		public function getPatientLocationsByStations($stations)
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('isdelete = "0"');
			if(is_array($stations))
			{
				$patloc->andWhereIn('station', $stations);
			}
			else
			{
				$patloc->andWhere('station = "' . $stations . '"');
			}
			$patloc->orderBy('valid_from,id ASC');

			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				return $patlocs;
			}
		}

		public function getLocationsAssignedToPatients($locations_ids)
		{
			$locations_ids[] = '999999999';
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->whereIn('location_id', $locations_ids)
				->andwhere('isdelete="0"')
				->groupBy('location_id');
			$patlocarray = $patloc->fetchArray();

			return $patlocarray;
		}

		public function patient_hospital_hospiz_locations($ipid)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
			$patientmaster = new PatientMaster();


			$clocation_q = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where('isdelete = 0')
				->andWhere('client_id =?', $clientid);
			$clocation_array = $clocation_q->fetchArray();

			foreach($clocation_array as $lkey => $lvalue)
			{
				$master_locations[$lvalue['id']]['type'] = $lvalue['location_type'];
				$master_locations[$lvalue['id']]['location_name'] = $lvalue['location'];
			}

			/* ----------------Patient - Get LOCATIONS -------------------------------------- */
			$patloc_q = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid =?', $ipid)
				->andWhere('isdelete="0"')
				->orderBy('valid_from,id ASC');
			$patloc_array = $patloc_q->fetchArray();

			$o = 1;
			$location['hh_intervals'] = array();
			$location['hh_continuous_day'] = array();
			$location['hh_active_location'] = '0';

			foreach($patloc_array as $plockey => $plocvalue)
			{
				$location['patient_locations'][$plocvalue['location_id']]['name'] = $master_locations[$plocvalue['location_id']]['location_name'];
				$location['patient_locations'][$plocvalue['location_id']]['type'] = $master_locations[$plocvalue['location_id']]['type'];
				$location['patient_locations'][$plocvalue['location_id']]['from'] = $plocvalue['valid_from'];
				$location['patient_locations'][$plocvalue['location_id']]['till'] = $plocvalue['valid_till'];

				if($location['patient_locations'][$plocvalue['location_id']]['type'] == '1' || $location['patient_locations'][$plocvalue['location_id']]['type'] == '2')
				{
					$location['hh_intervals'][$o]['start'] = $plocvalue['valid_from'];
					$location['hh_intervals'][$o]['end'] = $plocvalue['valid_till'];

					if($plocvalue['valid_till'] == '0000-00-00 00:00:00')
					{
						$plocvalue['valid_till'] = date("Y-m-d H:i:s");
						$location['hh_active_location'] = '1';
					}
					else
					{
						$location['hh_active_location'] = '0';
					}

					$hdeactivate_interval[$o]['start'] = date("Y-m-d", strtotime("+1 day", strtotime($plocvalue['valid_from'])));
					$hdeactivate_interval[$o]['end'] = date("Y-m-d", strtotime("-1 day", strtotime($plocvalue['valid_till'])));


					$location_details['hh_all_days'][] = $patientmaster->getDaysInBetween($hdeactivate_interval[$o]['start'], $hdeactivate_interval[$o]['end']);

					if($location['patient_locations'][$plocvalue['location_id']]['type'] == '1')
					{
						$location_details['all_hospital_days'][] = $patientmaster->getDaysInBetween($hdeactivate_interval[$o]['start'], $hdeactivate_interval[$o]['end']);
					}
					if($location['patient_locations'][$plocvalue['location_id']]['type'] == '2')
					{
						$location_details['all_hospiz_days'][] = $patientmaster->getDaysInBetween($hdeactivate_interval[$o]['start'], $hdeactivate_interval[$o]['end']);
					}

					$current_location = date('Y-m-d', strtotime($patloc_array[$plockey]['valid_till']));
					$nextlocation = date('Y-m-d', strtotime($patloc_array[$plockey + 1]['valid_from']));

					if($master_locations[$patloc_array[$plockey + 1]['location_id']]['type'] == '1' || $master_locations[$patloc_array[$plockey + 1]['location_id']]['type'] == '2')
					{
						if($current_location == $nextlocation)
						{
							$location['hh_continuous_day'][] = $nextlocation;
						}
					}
				}
				else
				{
					$location['hh_active_location'] = '0';
				}

				$o++;
			}

			if(!empty($location['hh_continuous_day']))
			{
				array_push($location_details['all_hospital_days'], $location['hh_continuous_day']);
				array_push($location_details['all_hospiz_days'], $location['hh_continuous_day']);
				array_push($location_details['hh_all_days'], $location['hh_continuous_day']);
			}

			foreach($location_details['all_hospital_days'] as $kho => $ho_days)
			{
				foreach($ho_days as $hok => $hovalue)
				{
					$location['hospital_days'][] = $hovalue;
				}
			}
			asort($location['hospital_days']);

			foreach($location_details['all_hospiz_days'] as $khz => $hz_days)
			{
				foreach($hz_days as $hzk => $hzvalue)
				{
					$location['hospiz_days'][] = $hzvalue;
				}
			}
			asort($location['hospiz_days']);

			foreach($location_details['hh_all_days'] as $kh => $hh_days)
			{
				foreach($hh_days as $hhk => $hhvalue)
				{
					$location['hh_days'][] = $hhvalue;
				}
			}

			asort($location['hh_days']);

			return $location;
		}

		public function get_first_location($ipid, $date)
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid =?', $ipid)
				->andWhere('isdelete="0"')
				//->andWhere('DATE("' . date('Y-m-d', strtotime($date)) . '") >= DATE(valid_from) AND (valid_till = "0000-00-00 00:00:00" OR DATE("' . date('Y-m-d', strtotime($date)) . '") <= DATE(valid_till))')
				->andWhere('DATE(valid_from)<=? and (valid_till =? or DATE(valid_till) >=?)', array(date('Y-m-d', strtotime($date)), "0000-00-00 00:00:00", date('Y-m-d', strtotime($date))))
				->limit(1)
				->orderBy('id ASC');
			if($_REQUEST['dbga'])
			{
				echo $patloc->getSqlQuery();
			}
			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				return $patlocs;
			}
		}

		public function get_valid_patient_locations($ipid)
		{
			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				//->where('ipid ="' . $ipid . '"  and isdelete="0"')
				->where('ipid =? and isdelete =?', array($ipid,"0"))
				->andWhere('discharge_location = "0"')
				->orderBy('valid_from,id ASC');
			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				return $patlocs;
			}
		}

		public function get_valid_patients_locations($ipids, $master = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if(is_array($ipids))
			{
				$ipids_arr = $ipids;
			}
			else
			{
				$ipids_arr = array($ipids);
			}

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->whereIn('ipid', $ipids)
				->andWhere('isdelete="0"')
				->andWhere('discharge_location = "0"')
				->orderBy('valid_from,id ASC');
			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				if($master)
				{
					$pat_loc_ids[] = '9999999999999';
					foreach($patlocs as $kpatloc => $vpatloc)
					{
						$pat_loc_ids[] = $vpatloc['location_id'];
						$pat_ipid2patlocid[$vpatloc['ipid']][] = $vpatloc['location_id'];
					}
					$pat_loc_ids = array_values(array_unique($pat_loc_ids));

					$drop = Doctrine_Query::create()
						->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
						->from('Locations')
						->where('client_id = ?', $clientid)
						->andWhereIn('id', $pat_loc_ids)
						->andWhere('isdelete = 0');
					$droparray = $drop->fetchArray();

					foreach($droparray as $k_drop => $v_drop)
					{
						$master_loc_res[$v_drop['id']] = $v_drop['location_type'];
						$master_loc_name_res[$v_drop['id']] = $v_drop['location'];
					}
				}

				foreach($patlocs as $k_pat_loc => $v_pat_loc)
				{
					if($master)
					{
						$v_pat_loc['master_location_type'] = $master_loc_res[$v_pat_loc['location_id']];
						$v_pat_loc['master_location_name'] = $master_loc_name_res[$v_pat_loc['location_id']];
					}

					$patients_locations[$v_pat_loc['ipid']][] = $v_pat_loc;
				}

				return $patients_locations;
			}
		}

		public function getPatientLocationsPeriods($ipid, $period = false)
		{
			$pm = new PatientMaster();
			$locations = new Locations();

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid ="' . $ipid . '"  and isdelete="0"')
				->orderBy('valid_from,id ASC');

			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				if($period)
				{
					$curent_period = $pm->getDaysInBetween($period['start'], $period['end']);
				}

				foreach($patlocs as $k_loc => $v_loc)
				{
					if($v_loc['valid_till'] == '0000-00-00 00:00:00')
					{
						$till = date('Y-m-d');
					}
					else
					{
						$till = $v_loc['valid_till'];
					}

					$loc_days = $pm->getDaysInBetween($v_loc['valid_from'], $till);
					$location_allowed_days = array_intersect($loc_days, $curent_period);

					if(!empty($location_allowed_days))
					{
						$locations_details[$v_loc['id']] = $v_loc;
						$loc_period = array_values(array_unique($location_allowed_days));
						$locations_details[$v_loc['id']]['days']['start'] = $loc_period[0];
						$locations_details[$v_loc['id']]['days']['end'] = end($loc_period);
						$locations_details[$v_loc['id']]['counted_days'] = count($loc_period);
						$locations_details[$v_loc['id']]['all_days'] = $loc_period;

						if($v_loc['discharge_location'] != '1')
						{
							$location_ids[] = $v_loc['location_id'];
						}
					}
				}

				//get locations master
				$master_details = $locations->getLocationbyIds($location_ids);

				foreach($locations_details as $k_loc => $v_loc)
				{
					$locations_details[$k_loc]['master_details'] = $master_details[$v_loc['location_id']];
				}

				$locations_details = array_values($locations_details);

				return $locations_details;
			}
		}

		public function get_patient_last_hospital($ipid, $clientid)
		{
			$master_locations = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where('isdelete = 0')
				->andWhere('client_id ="' . $clientid . '"')
				->andWhere('location_type = "1"'); //hospitals

			$master_loc_res = $master_locations->fetchArray();

			$master_locations_ids[] = '999999999999';
			if($master_loc_res)
			{
				foreach($master_loc_res as $k_master_loc => $v_master_loc)
				{
					$master_locations_ids[] = $v_master_loc['id'];
					$master_locations_arr[$v_master_loc['id']] = $v_master_loc;
				}
			}

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid ="' . $ipid . '"')
				->andWhere('isdelete="0"')
				->andWhereIn('location_id', $master_locations_ids)
				->orderBy('valid_from DESC')
				->limit('1');
			$pat_loc_res = $patloc->fetchArray();

			if($pat_loc_res)
			{
				$pat_loc_res[0]['location_name'] = $master_locations_arr[$pat_loc_res[0]['location_id']]['location'];

				return $pat_loc_res;
			}
			else
			{
				return false;
			}
		}

		public function get_period_locations($ipid, $period = false, $location_type = false)
		{
			$pm = new PatientMaster();
			$locations = new Locations();

			$patloc = Doctrine_Query::create()
				->select('*')
				->from('PatientLocation')
				->where('ipid ="' . $ipid . '"  and isdelete="0"')
				->orderBy('valid_from,id ASC');

			$patlocs = $patloc->fetchArray();

			if($patlocs)
			{
				if($period)
				{
					$curent_period = $pm->getAllDaysInBetween($period['start'], $period['end']);
				}

				foreach($patlocs as $k_loc => $v_loc)
				{
					if($v_loc['valid_till'] == '0000-00-00 00:00:00')
					{
						$till = date('Y-m-d');
					}
					else
					{
						$till = $v_loc['valid_till'];
					}

					$loc_days = $pm->getAllDaysInBetween($v_loc['valid_from'], $till);
					$location_allowed_days = array_intersect($loc_days, $curent_period);

					if(!empty($location_allowed_days))
					{
						$locations_details[$v_loc['id']] = $v_loc;
						$loc_period = array_values(array_unique($location_allowed_days));
						$locations_details[$v_loc['id']]['days']['start'] = $loc_period[0];
						$locations_details[$v_loc['id']]['days']['end'] = end($loc_period);
						$locations_details[$v_loc['id']]['counted_days'] = count($loc_period);
						$locations_details[$v_loc['id']]['all_days'] = $loc_period;

						if($v_loc['discharge_location'] != '1')
						{
							$location_ids[] = $v_loc['location_id'];
						}
					}
				}

				//get locations master
				$master_details = $locations->getLocationbyIds($location_ids);

				foreach($locations_details as $k_loc => $v_loc)
				{
					if($location_type !== false && $master_details[$v_loc['location_id']]['location_type'] != $location_type)
					{
						unset($locations_details[$k_loc]);
					}
					if(count($locations_details[$k_loc]))
					{
						$locations_details[$k_loc]['master_details'] = $master_details[$v_loc['location_id']];
					}
				}

				$locations_details = array_values($locations_details);

				return $locations_details;
			}
		}
		
		public static function get_multiple_period_locations($ipid = false, $period = false, $location_type = false)
		{
			$pm = new PatientMaster();
			$locations = new Locations();

			
			if($ipid)
			{
				if(is_array($ipid))
				{
					$ipids = $ipid;
				}
				else
				{
					$ipids = array($ipid);
				}

// 				$ipids[] = '999999999999';
				
				// get patient details for zu-hause location
    			$patients_details  = $pm->get_multiple_patients_details($ipids);
				
				
				
				$patloc = Doctrine_Query::create()
					->select('*')
					->from('PatientLocation')
//					->where('ipid ="' . $ipid . '"  and isdelete="0"')
					->where('isdelete="0"')
					->andWhereIn('ipid', $ipids)
					->orderBy('valid_from,id ASC');
				$patlocs = $patloc->fetchArray();

				if($patlocs)
				{
					if($period)
					{
						$curent_period = $pm->getAllDaysInBetween($period['start'], $period['end']);
					}

					foreach($patlocs as $k_loc => $v_loc)
					{
						if($v_loc['valid_till'] == '0000-00-00 00:00:00')
						{
							$till = date('Y-m-d');
						}
						else
						{
							$till = $v_loc['valid_till'];
						}

						$loc_days = $pm->getAllDaysInBetween($v_loc['valid_from'], $till);
						$location_allowed_days = array_intersect($loc_days, $curent_period);

						if(!empty($location_allowed_days))
						{
							$locations_details[$v_loc['id']] = $v_loc;
							$loc_period = array_values(array_unique($location_allowed_days));
							$locations_details[$v_loc['id']]['days']['start'] = $loc_period[0];
							$locations_details[$v_loc['id']]['days']['end'] = end($loc_period);
							$locations_details[$v_loc['id']]['counted_days'] = count($loc_period);
							$locations_details[$v_loc['id']]['all_days'] = $loc_period;

							if($v_loc['discharge_location'] != '1')
							{
								$location_ids[] = $v_loc['location_id'];
							}
						}
					}

					//get locations master
					$master_details = $locations->getLocationbyIds($location_ids);

					foreach($locations_details as $k_loc => $v_loc)
					{
						if($location_type !== false && $master_details[$v_loc['location_id']]['location_type'] != $location_type)
						{
							unset($locations_details[$k_loc]);
						}
						if(count($locations_details[$k_loc]))
						{
   							$locations_details[$k_loc]['master_details'] = $master_details[$v_loc['location_id']];

						    if( $master_details[$v_loc['location_id']]['location_type'] == "5"){ // If patient is at  home - get patient address 
    							$locations_details[$k_loc]['master_details']['street'] = $patients_details[$v_loc['ipid']]['street1'];
    							$locations_details[$k_loc]['master_details']['zip'] =  $patients_details[$v_loc['ipid']]['zip'];
    							$locations_details[$k_loc]['master_details']['city'] =  $patients_details[$v_loc['ipid']]['city'];
    							$locations_details[$k_loc]['master_details']['location_type'] =  $master_details[$v_loc['location_id']]['location_type'];
						    }
						}
					}

					$locations_details = array_values($locations_details);

					//ISPC-1981 - 8888%d 
					$mark_of_the_beast = "8888";
					$cp_ipids = array();
					foreach($locations_details as $row) {
						if ( empty($row['master_details'])
							&&
							substr($row['location_id'], 0, strlen($mark_of_the_beast)) == $mark_of_the_beast
						) {
							$cp_ipids[] =  $row['ipid'];
							$locid = substr($row['location_id'], 0, 4);
						}
					}
					if ( ! empty($cp_ipids)) {
						$cp_ipids = array_unique($cp_ipids);
						$cpm_obj = new ContactPersonMaster();
						$cprarr = $cpm_obj->getAllPatientContact($cp_ipids, false);
						
						
						foreach($locations_details as &$row) {
							if ( empty($row['master_details'])
									&&
									substr($row['location_id'], 0, strlen($mark_of_the_beast)) == $mark_of_the_beast
									&&
									isset($cprarr[ $row['ipid']])
							) {
								$cp_count = substr($row['location_id'], strlen($mark_of_the_beast), strlen($row['location_id']) - strlen($mark_of_the_beast)); 
								
								if (isset($cprarr[ $row['ipid']] [($cp_count-1)])) {
									
									$contact = $cprarr[ $row['ipid']] [($cp_count-1)];
									
									$location = array(
											'location'	=> 'bei Kontaktperson ('.$contact['cnt_last_name'].', '.$contact['cnt_first_name'] .')',
											'street'	=> $contact['cnt_street1'],
											'zip'		=> $contact['cnt_zip'],
											'city'		=> $contact['cnt_city'],
											'phone1'	=> $contact['cnt_phone'],
											'phone2'	=> $contact['cnt_mobile'],
									);
									$row['master_details'] = $location;
								}
								
							}
						}
					}
					
				
					
					return $locations_details;
				}
			}
		}

		
		/**
		 * this will return the last valid location... NOT the current location
		 * current location needs have also valid_till < CURRENT_DATE();
		 * @param string $ipid
		 * @return void|Ambigous <NULL, mixed>|boolean
		 */
		public static function getIpidLastLocationDetails($ipid = '', $returnPatLocObj = false)
		{
			if ( empty($ipid)) {
				return;
			}
			
			$master_locations = null;
			
			$patloc = Doctrine_Query::create()
			->select('*, id, location_id, is_contact')
			->from('PatientLocation')
			->where('ipid = ? ' , $ipid )
			->andWhere('isdelete = ? ', 0)
// 			->orderBy('id DESC')
			->orderBy('DATE(valid_from) DESC, id DESC')
			->limit(1)
			->fetchOne(null, Doctrine_Core::HYDRATE_RECORD);
			
			if ($returnPatLocObj) {
			    return $patloc; //premature return
			}
			
			
			
// 			if ($patloc && ! empty($patloc->location_id)) {
			if ($patloc && ! empty($patloc['location_id'])) {
				
			    $patloc =  $patloc->toArray();
			    
				$locid = substr($patloc['location_id'], 0, 4);
				if($locid == "8888")
				{
					//wacky way to say the location is bey a contact person

					$contact = new ContactPersonMaster();
					$contactpersons_loc_array = $contact->get_contact_persons_by_ipids(array($ipid), false, false); 
					//get_contact_persons_by_ipids ( $ipids_array = false, $group_by = false, $hide_deleted = true )
								
					$locid = substr($patloc['location_id'], 4);
					
					if ( isset($contactpersons_loc_array[$ipid][ (int)$locid -1 ])) {
						$master_locations = array(
								"id" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['id'],
								"first_name" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['cnt_first_name'],
								"last_name" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['cnt_last_name'],
								"phone" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['cnt_phone'],
								"mobile" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['cnt_mobile'],
								"other_name" => null,
								"ComponentName" => "ContactPersonMaster",
								"is_contact" => $patloc['is_contact'],
								"ComponentName_is_contact" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['is_contact'],
								
						);
						
					}
					
				} else{
					
					$master_locations_query = Doctrine_Query::create()
					->select("* , AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
					->from('Locations l')
					->where('id = ?', $patloc['location_id']);
// 					->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
					
					/*
					 * ISPC-2121
					 * @claudiu 05.01.2018
					 * get the phone number of the station
					 */
					if ($patloc['station'] > 0 ) { 
					    $master_locations_query->leftJoin('l.LocationsStations ls ON l.id = ls.location_id AND ls.id = ?', $patloc['station']);
					    $master_locations_query->addSelect("ls.*, AES_DECRYPT(ls.station,'" . Zend_Registry::get('salt') . "') as station, ");
					}
					
					$master_locations = $master_locations_query->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
					
					
					if ($master_locations) {
						
						if ( $master_locations['location_type'] == 5 ) {
							
							$pm_obj = new PatientMaster();
							$ipid_MasterData = $pm_obj->getMasterData(null, null, null, $ipid);
							
							$master_locations["ComponentName"] = "PatientMaster";
							$master_locations["is_contact"] = $patloc['is_contact'];
							$master_locations['id'] = $ipid_MasterData['id'];
							
							$master_locations["first_name"] = $ipid_MasterData['first_name'];
							$master_locations["last_name"] = $ipid_MasterData['last_name'];
							$master_locations["other_name"] =  null;
							$master_locations["phone"] = $ipid_MasterData['phone'];
							$master_locations["mobile"] = $ipid_MasterData['mobile'];
							$master_locations["ComponentName_is_contact"] = $ipid_MasterData ['is_contact'];
							
							
							
							
						} else {
							$master_locations["ComponentName"] = "Locations";
							$master_locations["is_contact"] = $patloc['is_contact'];
							$master_locations["first_name"] = null;
							$master_locations["last_name"] = null;
							$master_locations["other_name"] =  $master_locations['location'];
							$master_locations["phone"] = $master_locations['phone1'];
							$master_locations["mobile"] = $master_locations['phone2'];
							$master_locations["fax"] = $master_locations['fax'];
							$master_locations["ComponentName_is_contact"] = $master_locations ['is_contact'];
							
							/*
							 * ISPC-2121
							 * set the phone number of the station as the contact-phone
							 */
							if ( ! empty($master_locations['LocationsStations'][0])) {
							    
							    $master_locations["first_name"] = $master_locations["other_name"];
							    $master_locations["other_name"] = "Station: ". $master_locations['LocationsStations'][0]['station'];
							    
							    if ( ! empty($master_locations['LocationsStations'][0]['phone1']) 
							        || ! empty($master_locations['LocationsStations'][0]['phone2'])) 
							    {        
							        $master_locations["phone"] = $master_locations['LocationsStations'][0]['phone1'];
							        $master_locations["mobile"] = $master_locations['LocationsStations'][0]['phone2'];
							        $master_locations["fax"] = $master_locations['LocationsStations'][0]['fax'];
							    }
							}
							
						}						
					}
				}				
			} 

			if ($master_locations) {
			    
			    $master_locations['PatientLocation_id'] = isset($patloc['id']) ? $patloc['id'] : null;
			    $master_locations["valid_till"] = $patloc['valid_till'];
			    
				return $master_locations;
			} else {
				return false;
			}
			
		
		}
		
		
		/**
		 * used only in contactphoneListener
		 * @param string $ipid
		 * @return void|Ambigous <multitype:, multitype:string unknown , multitype:string mixed >
		 */
		public function getTodaysLocationType( $ipid = '')
		{
		    if (empty($ipid)) {
		        return;
		    }
		    $todays_location = array();
		    
		    $patloc = Doctrine_Query::create()
		    ->select('id, ipid, location_id , is_contact ')
		    ->from('PatientLocation')
		    ->where('ipid = ?', $ipid)
		    ->andWhere('isdelete = 0')
		    ->andWhere("DATE(`valid_from`) <= CURDATE()")
		    ->andWhere("(`valid_till` = '0000-00-00 00:00:00' OR DATE(`valid_till`) >= CURDATE() ) ")
		    ->orderBy('DATE(valid_from) DESC, id DESC')
		    ->limit(1)
		    ->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		    
		    
		    if ( ! empty($patloc['location_id'])) {
		        $locid = substr($patloc['location_id'], 0, 4);
		        if($locid == "8888")
		        {
		            //wacky way to say the location is bey a contact person
		            $todays_location = array(		                
    		            "ComponentName" => "ContactPersonMaster",
    		            "is_contact" => $patloc['is_contact'],
		            );
		            
		        } else {
					
					$master_locations = Doctrine_Query::create()
					->select("id, location_type")
					->from('Locations')
					->where('id = ?', $patloc['location_id'])
					->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
					
					
					if ($master_locations) {
						
						if ( $master_locations['location_type'] == 5 ) {
						    $todays_location = array(
    						    "ComponentName" => "PatientMaster",
    						    "is_contact" => $patloc['is_contact'],
							);
							
						} else {
						    $todays_location = array(
						        "ComponentName" => "Locations",
						        "is_contact" => $patloc['is_contact'],
						    );
							
							
						}						
					}
				}			
		    }
		    return $todays_location;
		}
		
		
		
		public function getPatientActiveLocation($pid)
		{
			$patloc = Doctrine_Query::create()
			->select('*')
			->from('PatientLocation')
			//->where('ipid ="' . $pid . '"  and isdelete="0"')
			->where('ipid =?', $pid)
			->andWhere('isdelete="0"')
			->limit(1)
			->orderBy('id DESC')
			->andWhere("valid_till='0000-00-00 00:00:00'");
			$patlocs = $patloc->execute();
		
			if($patlocs)
			{
				$patlocarray = $patlocs->toArray();
		
				return $patlocarray;
			}
		}	
		
		
	public function findOrCreateOneById($value, array $data = array(), $hydrationMode = null)
	{
		if(empty($data['ipid']) ){
			return;
		}
		if(empty($data) || empty($data['location_id']) ){
			return;
		}
		
		$data['admission_date'] =  date('Y-m-d H:i:s');
		$location_form = new Application_Form_PatientLocation();
		$entity = $location_form->UpdateData($data);
		
	}
	
	public function get_patient_hospreasons_attached()
	{
		//get hospital reasons attached
		$drop = Doctrine_Query::create()
		->select('DISTINCT(reason) as reason')
		->from('PatientLocation')
		->where('isdelete = "0"');
	
		$droparray = $drop->fetchArray();
		//var_dump($droparray); exit;
	
		return $droparray;
	}

	public function getPatientLocationNames(){
	    
	}
	
	
	
	/**
	 * this includes a discharge_location =1 => hardcoded Entlassen
	 * 
	 * @param string $ipid
	 * @param string $returnPatLocObj
	 * @return void|Doctrine_Collection|Ambigous <NULL, Doctrine_Collection>
	 */
	public static function getIpidAllLocationDetails($ipid = '', $returnPatLocObj = false)
	{
	    if ( empty($ipid)) {
	        return;
	    }
	    	
	    $master_locations_ALL = null;
	    
	    //ISPC-2320 - patient locations info in SapvEvaluationII form 
	    $pt = new PatientLocation();
	    $reasons = $pt->getReasons();
	    
	    $patlocALL = Doctrine_Query::create()
	    ->select('*, id, location_id, is_contact')
	    ->from('PatientLocation')
	    ->where('ipid = ? ' , $ipid )
	    ->andWhere('isdelete = ? ', 0)
	    // 			->orderBy('id DESC')
	    ->orderBy('DATE(valid_from) ASC, id ASC')
	    ->execute(null, Doctrine_Core::HYDRATE_RECORD);
	    	
	    if ($returnPatLocObj) {
	        return $patlocALL; //premature return
	    }
	    	
	    	
	    if ($patlocALL) {
	
	    	
	        foreach ($patlocALL as $patloc) {
	            $patloc =  $patloc->toArray();
	            
	            //add discharge locations
	            if (empty($patloc['location_id']) && $patloc['discharge_location'] == 1) {
	                
                    $master_locations = array(
                        "nice_name" => "Entlassen", 
                    );
	                
	            } else {
    	                
    	            
    	            
    	            if (empty($patloc['location_id'])) {
    	                continue;
    	            }
        	         
        	        $master_locations = null;
        	        
        	        $locid = substr($patloc['location_id'], 0, 4);
        	        if($locid == "8888")
        	        {
        	            //wacky way to say the location is bey a contact person
        	
        	            $contact = new ContactPersonMaster();
        	            $contactpersons_loc_array = $contact->get_contact_persons_by_ipids(array($ipid), false, false);
        	            //get_contact_persons_by_ipids ( $ipids_array = false, $group_by = false, $hide_deleted = true )
        	
        	            
        	            $locid = substr($patloc['location_id'], 4);
        	            	
        	            if ( isset($contactpersons_loc_array[$ipid][ (int)$locid -1 ])) {
        	                
        	                ContactPersonMaster::beautifyName($contactpersons_loc_array[$ipid]);
        	                
        	                $master_locations = array(
        	                    
        	                    "nice_name" => "bei Kontaktperson" . " (".$contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['nice_name'] .")",
        	                    
        	                    "id" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['id'],
        	                    "first_name" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['cnt_first_name'],
        	                    "last_name" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['cnt_last_name'],
        	                    "phone" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['cnt_phone'],
        	                    "mobile" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['cnt_mobile'],
        	                    "other_name" => null,
        	                    "ComponentName" => "ContactPersonMaster",
        	                    "is_contact" => $patloc['is_contact'],
        	                    "ComponentName_is_contact" => $contactpersons_loc_array[$ipid][ (int)$locid - 1 ] ['is_contact'],
        	
        	                );
        	
        	            }
        	            	
        	        } else{
        	            	
        	            $master_locations_query = Doctrine_Query::create()
        	            ->select("* , AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
        	            ->from('Locations l')
        	            ->where('id = ?', $patloc['location_id']);
        	            // 					->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
        	            	
        	            /*
        	             * ISPC-2121
        	             * @claudiu 05.01.2018
        	             * get the phone number of the station
        	            */
        	            if ($patloc['station'] > 0 ) {
        	                $master_locations_query->leftJoin('l.LocationsStations ls ON l.id = ls.location_id AND ls.id = ?', $patloc['station']);
        	                $master_locations_query->addSelect("ls.*, AES_DECRYPT(ls.station,'" . Zend_Registry::get('salt') . "') as station, ");
        	            }
        	            	
        	            $master_locations = $master_locations_query->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
        	            	
        	            	
        	            if ($master_locations) {
        	
        	                if ( $master_locations['location_type'] == 5 ) {
        	                    	
        	                    $pm_obj = new PatientMaster();
        	                    $ipid_MasterData = $pm_obj->getMasterData(null, null, null, $ipid);
        	                    
        	                    
        	                    $master_locations["nice_name"] = $master_locations['location'];
        	                    
        	                    $master_locations["ComponentName"] = "PatientMaster";
        	                    $master_locations["is_contact"] = $patloc['is_contact'];
        	                    $master_locations['id'] = $ipid_MasterData['id'];
        	                    	
        	                    $master_locations["first_name"] = $ipid_MasterData['first_name'];
        	                    $master_locations["last_name"] = $ipid_MasterData['last_name'];
        	                    $master_locations["other_name"] =  null;
        	                    $master_locations["phone"] = $ipid_MasterData['phone'];
        	                    $master_locations["mobile"] = $ipid_MasterData['mobile'];
        	                    $master_locations["ComponentName_is_contact"] = $ipid_MasterData ['is_contact'];
        	                    	
        	                } else {
        	                    
        	                    $master_locations["nice_name"] = $master_locations['location'];
        	                    $master_locations["ComponentName"] = "Locations";
        	                    $master_locations["is_contact"] = $patloc['is_contact'];
        	                    $master_locations["first_name"] = null;
        	                    $master_locations["last_name"] = null;
        	                    $master_locations["other_name"] =  $master_locations['location'];
        	                    $master_locations["phone"] = $master_locations['phone1'];
        	                    $master_locations["mobile"] = $master_locations['phone2'];
        	                    $master_locations["ComponentName_is_contact"] = $master_locations ['is_contact'];
        	                    if ( $master_locations['location_type'] == 1 )
        	                    {
        	                    	$master_locations["reason_nice_name"] = ($reasons[$patloc['reason']] ? $reasons[$patloc['reason']] : ""); //ISPC-2320 - patient locations info in SapvEvaluationII form
        	                    }
        	                    else 
        	                    {
        	                    	$master_locations["reason_nice_name"] = "";
        	                    }
        	                    
        	                    	
        	                    /*
        	                     * ISPC-2121
        	                     * set the phone number of the station as the contact-phone
        	                    */
        	                    if ( ! empty($master_locations['LocationsStations'][0])) {
        	                        	
        	                        $master_locations["first_name"] = $master_locations["other_name"];
        	                        $master_locations["other_name"] = "Station: ". $master_locations['LocationsStations'][0]['station'];
        	                        	
        	                        if ( ! empty($master_locations['LocationsStations'][0]['phone1'])
        	                            || ! empty($master_locations['LocationsStations'][0]['phone2']))
        	                        {
        	                            $master_locations["phone"] = $master_locations['LocationsStations'][0]['phone1'];
        	                            $master_locations["mobile"] = $master_locations['LocationsStations'][0]['phone2'];
        	                        }
        	                    }
        	                    	
        	                }
        	            }
        	        }
	            }
        	        
    	        
    	        
    	        if ($master_locations) {
    	            
    	            $master_locations['PatientLocation_id'] = isset($patloc['id']) ? $patloc['id'] : null;
    	            $master_locations["valid_till"] = $patloc['valid_till'];
    	            $master_locations["__PatientLocation"] = $patloc;

    	            $master_locations_ALL[]  = $master_locations;
    	        }
    	        
    	        
    	    }
	    }
// dd($master_locations_ALL);
	    return $master_locations_ALL;
	    	
	
	}
	
	/**
	 * ISPC-2614 Ancuta 19.07.2020 
	 * @param unknown $ipid
	 * @param unknown $target_ipid
	 * @param unknown $target_client
	 */
	public function clone_records($ipid,$target_ipid,$target_client){
	    
	    //get all locations of pateint
	    $patient_locations  = $this->getPatientLocations($ipid);
	    
	    if($patient_locations)
	    {
	        foreach($patient_locations as $k => $loc_data)
	        {
	            $pph = new PatientLocation();
	            $obj_columns = $pph->getTable()->getColumns();
	            //ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
	            $pc_listener = $pph->getListener()->get('IntenseConnectionListener');
	            $pc_listener->setOption('disabled', true);
	            //--
	            foreach($obj_columns as $column_name=>$column_info){
	                if(!in_array($column_name,array('id','ipid'))){
	                    $pph->$column_name = $loc_data[$column_name];
	                }
	            }
	            $pph->ipid = $target_ipid;
	            $pph->save();
	            
	            //ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
	            $pc_listener->setOption('disabled', false);
	            //--
	        }
	    }
	    
	}
	
}
	

?>