<?php

	Doctrine_Manager::getInstance()->bindComponent('Locations', 'SYSDAT');

	class Locations extends BaseLocations {

		public static function getLocations($clientid, $isdrop = 0,$show_only_from_master = false)
		{
			$Tr = new Zend_View_Helper_Translate();
			$fdoc = Doctrine_Query::create()
				->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
				->from('Locations l ')
				->where("l.client_id=?", $clientid)
				->andwhere("l.location_type!= 6 ")
				->andWhere('l.isdelete=0');
				if($show_only_from_master){//ISPC-2612 Ancuta 27.06.2020
				    $fdoc->andWhere('l.connection_id is NOT null');
				    $fdoc->andWhere('l.master_id is NOT null');
				}
				$fdoc->orderBy('l__0 ASC');
			$locationarray = $fdoc->fetchArray();

			if($isdrop == 1)
			{
				$locations = array("" => $Tr->translate('selectlocation'));

				foreach($locationarray as $location)
				{
// 				    $locations[$location['id']] = $location['location']."-".$location['master_id'];
				    $locations[$location['id']] = $location['location'];
				}
				return $locations;
			}
			elseif($isdrop == 2)
			{
				foreach($locationarray as $location)
				{
					$locations[$location['id']] = $location['location'];
				}
				return $locations;
			}
			elseif($isdrop == 3)
			{
				foreach($locationarray as $location)
				{
					$locations[$location['id']] = $location['location_type'];
				}
				return $locations;
			}
			else
			{
				return $locationarray;
			}
		}

		public function getLocationByPatLocId($plid)
		{
			$patloc = new PatientLocation();
			$patlocarrr = $patloc->getLocationById($plid);

			$fdoc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where("id=?", $patlocarrr[0]['location_id'])
				->andWhere('isdelete=0')
				->orderBy('location ASC');
			$locationMasterArr = $fdoc->fetchArray();

			return $locationMasterArr;
		}

		public function getLocationbyId($lid)
		{
			$Tr = new Zend_View_Helper_Translate();

			$fdoc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('Locations')
				->where("id=?", $lid)
				//->andWhere('isdelete=0')// ISPC-2612 Ancuta 27.06.2020 Locx
				->orderBy('location ASC');
			return $fdoc->fetchArray();
		}

		public function checkLocationsClientByType($clientid, $type)
		{
			$Tr = new Zend_View_Helper_Translate();

			$fdoc = Doctrine_Query::create()
				->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
				->from('Locations l ')
				->where("l.client_id=?", $clientid)
				->andwhere("l.location_type=?", $type)
				//->andWhere('l.isdelete=0')//ISPC-2612 Ancuta 27.06.2020 Locx
				->orderBy('l__0 ASC');
			$locationarray = $fdoc->fetchArray();

			return $locationarray;
		}

		public function getLocationTypes()
		{
			$Tr = new Zend_View_Helper_Translate();
			$ignoretype = $Tr->translate('ignoretype');
			$hospital = $Tr->translate('hospital');
			$hospice = $Tr->translate('Hospice');
			$nursing_home = $Tr->translate('nursinghome');
			$retirement_home = $Tr->translate('retirementhome');
			$at_home = $Tr->translate('athome');
			$cntpersons = $Tr->translate('location_cnt');
			$paliative_station = $Tr->translate('paliative_station');
			$assisted_living = $Tr->translate('assisted_living');
			$temporary_care = $Tr->translate('temporary_care');
			//ISPC-2549 Carmen 17.02.2020
			$integration_assistance_for_disabled_people = $Tr->translate('integration_assistance_for_disabled_people');
			//--
			
			$holiday = $Tr->translate('holiday_location');            //ISPC-1948 Lore 20.08.2020
			
			$locationarray = array();
			$locationarray = array(
					'0' => $ignoretype,
					'1' => $hospital,
					'2' => $hospice,
					'3' => $nursing_home,
					'4' => $retirement_home,
					'5' => $at_home,
					'6' => $cntpersons,
					'7' => $paliative_station,
					'8' => $assisted_living,
					'9' => $temporary_care,
					'10' => $integration_assistance_for_disabled_people,
			        '11' => $holiday,                        //ISPC-1948 Lore 20.08.2020
			    
			);
			return $locationarray;
		}

		public function getAllLocations($ipid = false, $letter = false, $keyword = false, $arrayids = false, $useclient = true)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipid != false)
			{
				$patientsupplies = new PatientLocation();
				$patsupplies = $patientsupplies->getPatientLocations($ipid);

				if(count($patsupplies) > 0)
				{
					foreach($patsupplies as $keyph => $valueph)
					{
						$pharry[$keyph] = $valueph['location_id'];
					}
					$ids = implode(",", $pharry);

					$ipid_sql .= " AND id IN (" . $ids . ")";
				}
				else
				{
					$ipid_sql .= " AND id IN (0)";
				}
			}
			else
			{
				$ipid_sql = " AND  isdelete = 0 ";
			}

			if($keyword != false)
			{
				$keyword_sql = " AND location like '%" . ($keyword) . "%'";
			}

			if($letter != false)
			{
				$keyword_sql = " AND location like '" . ($letter) . "%'";
			}

			if($arrayids != false)
			{
				$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
				$ipid_sql = '';
			}

			if($useclient)
			{
				$client_sql = 'client_id= "' . $clientid . '" AND ';
			}
			else
			{
				$client_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
				->from('Locations')
				->where($client_sql . " location != '' " . $ipid_sql . $keyword_sql . $array_sql);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getLocationbyIds($location_ids = false)
		{
			$Tr = new Zend_View_Helper_Translate();

			if(is_array($location_ids))
			{
				$loc_ids = $location_ids;
			}
			else if($location_ids != false)
			{
				$loc_ids = array($location_ids);
			}
			
			$locations_details = array();
			if(count($loc_ids) != '0')
			{
				//$loc_ids[] = '9999999999';
			//}

				$fdoc = Doctrine_Query::create()
					->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
					->from('Locations')
					->whereIn("id", $loc_ids)
					->andWhere('isdelete=0')
					->orderBy('location ASC');
				$loc_arr = $fdoc->fetchArray();

				if($loc_arr)
				{
					foreach($loc_arr as $k_loc => $v_loc)
					{
						$locations_details[$v_loc['id']] = $v_loc;
					}
				}
			}
			return $locations_details;
		}

		
		
		/**
		 * @author Ancuta 
		 * ISPC-2612 Ancuta 27.06.2020 Locx
		 * @param unknown $clientid
		 * @param array $types
		 * @return array|array|Doctrine_Collection
		 */
		public function get_locationByClientAndTypes($clientid, $types = array())
		{
		    if(empty($clientid)){
		        return array();
		    }
		    
		    $loc_q = Doctrine_Query::create()
		    ->select("*,(CONVERT(AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') using latin1)) as location")
		    ->from('Locations l ')
		    ->where("l.client_id=?", $clientid);
		    if(!empty($types)){
		        $loc_q->andwhereIn("l.location_type", $types);
		    }
		    $loc_q->orderBy('l__0 ASC');
		    $locationarray = $loc_q->fetchArray();
		    
		    return $locationarray;
		}
		
		
		
	}

?>
