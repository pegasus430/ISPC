<?php

	Doctrine_Manager::getInstance()->bindComponent('DtaLocations', 'SYSDAT');

	class DtaLocations extends BaseDtaLocations {

		public static function get_dta_locations($clientid, $isdrop)
		{
			$tr = new Zend_View_Helper_Translate();
			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('DtaLocations')
				->where("client='" . $clientid . "'")
				->andWhere('isdelete=0')
				->orderBy('id ASC');
			$locationarray = $fdoc->fetchArray();

			if($isdrop == 1)
			{
				$locations = array("" => $tr->translate('selectlocation'));

				foreach($locationarray as $location)
				{
					$locations[$location['id']] = $location['name'];
				}
				return $locations;
			}
			elseif($isdrop == 2)
			{
				foreach($locationarray as $location)
				{
					$locations[$location['id']] = $location['name'];
				}
				return $locations;
			}
			elseif($isdrop == 3)
			{
				foreach($locationarray as $location)
				{
					$locations[$location['id']] = $location;
				}
				return $locations;
			}
			//reversed (type(location_master_id)>id(dta_id)) used in dta mapping for xml
			elseif($isdrop == 4)
			{
				$dta_ids[] = '9999999999999';
				foreach($locationarray as $k_dta_loc => $v_dta_loc)
				{
					$dta_ids[] = $v_dta_loc['id'];
				}

				$dta_locations = Locations2dta::get_location2dta_multiple($dta_ids);

				foreach($dta_locations as $k_loc => $v_loc)
				{
					$locations_master2dta[$v_loc['location']] = $v_loc['dta'];
				}
				
				return $locations_master2dta;
			}
			else
			{
				return $locationarray;
			}
		}

		public function get_location($location)
		{
			$loc = Doctrine_Query::create()
				->select('*')
				->from('DtaLocations')
				->where('id="' . $location . '"')
				->andWhere('isdelete = "0"');
			$loc_res = $loc->fetchArray();

			if($loc_res)
			{
				return $loc_res[0];
			}
			else
			{
				return false;
			}
		}

		public function get_client_selected_locations()
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			$dta_locations = DtaLocations::get_client_dta_locations($clientid);
			if($dta_locations)
			{
				$dta_loc_ids[] = '999999999999';
				foreach($dta_locations as $k_dta_loc => $v_dta_loc)
				{
					$dta_loc_ids[] = $v_dta_loc['id'];
				}

				//get selected locations
				$dta_selected = Locations2dta::get_location2dta_multiple($dta_loc_ids);

				return $dta_selected;
			}
			else
			{
				return false;
			}
		}

		public function get_client_dta_locations($clientid)
		{
			$loc = Doctrine_Query::create()
				->select('*')
				->from('DtaLocations')
				->where('client="' . $clientid . '"')
				->andWhere('isdelete = "0"');
			$loc_res = $loc->fetchArray();

			if($loc_res)
			{
				foreach($loc_res as $k_loc => $v_loc)
				{
					$locations[$v_loc['id']] = $v_loc;
				}

				return $locations;
			}
			else
			{
				return false;
			}
		}

	}

?>
