<?php

	Doctrine_Manager::getInstance()->bindComponent('FamilyDegree', 'SYSDAT');

	class FamilyDegree extends BaseFamilyDegree {

		public function getFamilyDegrees($isdrop, $pat_ipid = false)
		{

			$Tr = new Zend_View_Helper_Translate();
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$hidemagic = Zend_Registry::get('hidemagic');

			if($_REQUEST['id'])
			{
				$decid = Pms_Uuid::decrypt($_REQUEST['id']);
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$ipid = Pms_CommonData::getIpid($decid);
			}
			else
			{
				$ipid = $pat_ipid;
			}
			$sql = "*";
			$adminvisible = PatientMaster::getAdminVisibility($ipid);

			if($logininfo->usertype == 'SA' && !$adminvisible)
			{
				$sql = "*,'" . $hidemagic . "' as family_degree";
			}

			//ISPC-2612 Ancuta 27.06.2020
			$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('FamilyDegree',$logininfo->clientid);
			
			
			$fdoc = Doctrine_Query::create()
				->select($sql)
				->from('FamilyDegree')
				->where('isdelete=0')
				->andwhere('clientid=' . $logininfo->clientid);
				if($client_is_follower){
				    $fdoc->andWhere('connection_id is NOT null');
				    $fdoc->andWhere('master_id is NOT null');
				}
				$fdoc->orderBy('family_degree ASC');
			$loc = $fdoc->execute();

			if($loc)
			{
				$locationarray = $loc->toArray();
			}

			if($isdrop == 1)
			{
				$locations = array("" => $Tr->translate('selectfamilydegree'));

				foreach($locationarray as $location)
				{
// 					$locations[$location['id']] = utf8_encode($location['family_degree']);
					$locations[$location['id']] = $location['family_degree'];
				}
				return $locations;
			}
			else
			{
			    return($locationarray);
			}
		}

		/**
		 * ISPC-2609 + ISPC-2000 Ancuta 28.09.2020
		 * @param unknown $clientid
		 * @param unknown $isdrop
		 * @param boolean $pat_ipid
		 * @return string[]|Zend_View_Helper_Translate[]|NULL[]|NULL[]
		 */
		public function getClientFamilyDegrees($clientid,$isdrop, $pat_ipid = false)
		{

			$Tr = new Zend_View_Helper_Translate();
			
			//ISPC-2612 Ancuta 27.06.2020
			$client_is_follower = ConnectionMasterTable::_check_client_connection_follower('FamilyDegree',$clientid);
			
			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('FamilyDegree')
				->where('isdelete=0')
				->andwhere('clientid=' . $clientid);
				if($client_is_follower){
				    $fdoc->andWhere('connection_id is NOT null');
				    $fdoc->andWhere('master_id is NOT null');
				}
				$fdoc->orderBy('family_degree ASC');
			$loc = $fdoc->execute();

			if($loc)
			{
				$locationarray = $loc->toArray();
			}

			if($isdrop == 1)
			{
				$locations = array("" => $Tr->translate('selectfamilydegree'));

				foreach($locationarray as $location)
				{
					$locations[$location['id']] = $location['family_degree'];
				}
				return $locations;
			}
			else
			{
			    return($locationarray);
			}
		}

		public function get_relation($id)
		{
			$drop = Doctrine_Query::create()
			->select('*')
			->from('FamilyDegree')
			->where("id='" . $id . "'");
			$droparray = $drop->fetchArray();
		
			if($droparray)
			{
				return $droparray;
			}
			else
			{
				return false;
			}
		}
		
		public function getfamilydegreebyId($lid)
		{

			$fdoc = Doctrine_Query::create()
				->select('*')
				->from('FamilyDegree')
				->where('id =' . $lid)
				->orderBy('family_degree ASC');
			// echo $fdoc->getSqlQuery();
			$loc = $fdoc->execute();

			if($loc)
			{
				$locationarray = $loc->toArray();
				// print_r($locationarray);
				return $locationarray;
			}
		}

	}

?>
