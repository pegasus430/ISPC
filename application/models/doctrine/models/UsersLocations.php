<?php

	Doctrine_Manager::getInstance()->bindComponent('UsersLocations', 'SYSDAT');

	class UsersLocations extends BaseUsersLocations {

		public function getUserLocation($location)
		{
			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('UsersLocations ul')
				->where("ul.id='" . $location . "'")
				->andWhere('ul.isdelete=0')
				->orderBy('ul.first_name ASC');
			$userLocations = $fdoc->fetchArray();

			return $userLocations;
		}

		public function getUserLocations($clientid)
		{
			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('UsersLocations ul ')
				->where("ul.client_id='" . $clientid . "'")
				->andWhere('ul.isdelete=0')
				->orderBy('ul.first_name ASC');
			$userLocations = $fdoc->fetchArray();

			return $userLocations;
		}

	}

?>