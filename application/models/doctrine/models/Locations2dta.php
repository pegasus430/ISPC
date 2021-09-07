<?php

	Doctrine_Manager::getInstance()->bindComponent('Locations2dta', 'SYSDAT');

	class Locations2dta extends BaseLocations2dta {

		public function get_location2dta($location)
		{
			$loc = Doctrine_Query::create()
				->select('*')
				->from('Locations2dta')
				->where('location="' . $location . '"');
			$loc_res = $loc->fetchArray();

			if($loc_res)
			{
				return $loc_res;
			}
			else
			{
				return false;
			}
		}
		
		public function get_dta_locations($dta)
		{
			$loc = Doctrine_Query::create()
				->select('*')
				->from('Locations2dta')
				->where('dta="' . $dta . '"');
			$loc_res = $loc->fetchArray();

			if($loc_res)
			{
				return $loc_res;
			}
			else
			{
				return false;
			}
		}
		
		public function get_location2dta_multiple($dta_ids)
		{
			if($dta_ids)
			{
				$loc = Doctrine_Query::create()
					->select('*')
					->from('Locations2dta')
					->whereIn('dta', $dta_ids);
				$loc_res = $loc->fetchArray();

				if($loc_res)
				{
					return $loc_res;
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
	}

?>
