<?php

	Doctrine_Manager::getInstance()->bindComponent('LocationsStations', 'SYSDAT');

	class LocationsStations extends BaseLocationsStations {

		public function getLocationsStationsByLocation($clientid, $location_id)
		{
			$Tr = new Zend_View_Helper_Translate();

			$fdoc = Doctrine_Query::create()
				->select("*,(CONVERT(AES_DECRYPT(station,'" . Zend_Registry::get('salt') . "') using latin1)) as station")
				->from('LocationsStations l ')
				->where("l.location_id='" . $location_id . "'")
				->andWhere("l.client_id='" . $clientid . "'")
				->andWhere('l.isdelete=0');
			$locationarray = $fdoc->fetchArray();

			return $locationarray;
		}

		public function getLocationsStationsById($clientid, $location_id = false, $station_id)
		{

			$Tr = new Zend_View_Helper_Translate();

			$fdoc = Doctrine_Query::create()
				->select("*,(CONVERT(AES_DECRYPT(station,'" . Zend_Registry::get('salt') . "') using latin1)) as station,phone1,phone2,location_id")
				->from('LocationsStations  ')
				->where("id='" . $station_id . "'");
			if($location_id)
			{
				$fdoc->andWhere("location_id='" . $location_id . "'");
			}
			$fdoc->andWhere("client_id='" . $clientid . "'")
				->andWhere('isdelete=0');
			$stationarray = $fdoc->fetchArray();

			return $stationarray;
		}

		
		public function getLocationsStationsByIds($clientid, $location_id = false, $station_ids)
		{

			$Tr = new Zend_View_Helper_Translate();

			$fdoc = Doctrine_Query::create()
				->select("*,(CONVERT(AES_DECRYPT(station,'" . Zend_Registry::get('salt') . "') using latin1)) as station,phone1,phone2,location_id")
				->from('LocationsStations  ')
				->whereIn("id", $station_ids); 
			if($location_id)
			{
				$fdoc->andWhere("location_id='" . $location_id . "'");
			}
			$fdoc->andWhere("client_id='" . $clientid . "'")
				->andWhere('isdelete=0');
			$stationarray = $fdoc->fetchArray();
			if($stationarray){
			    foreach($stationarray as $kds=>$vsvalue){
			        $station_array[$vsvalue['id']] = $vsvalue;
			    } 
			}

			return $station_array;
		}

		public function getAllLocationsStationsByLocations($clientid, $location_array)
		{
			if(count($location_array) == 0)
			{
				$location_array[] = '999999999';
			}

			$fdoc = Doctrine_Query::create()
				->select("*,(CONVERT(AES_DECRYPT(station,'" . Zend_Registry::get('salt') . "') using latin1)) as station,phone1,phone2,location_id")
				->from('LocationsStations  ')
				->whereIn('location_id', $location_array)
				->andWhere("client_id='" . $clientid . "'")
				->andWhere('isdelete=0');
			$stationarray = $fdoc->fetchArray();

			foreach($stationarray as $st_key => $st_value)
			{
				$locations_stations[$st_value['location_id']][] = $st_value;
			}

			return $locations_stations;
		}

	}

?>
