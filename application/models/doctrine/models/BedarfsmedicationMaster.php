<?php

	Doctrine_Manager::getInstance()->bindComponent('BedarfsmedicationMaster', 'SYSDAT');

	class BedarfsmedicationMaster extends BaseBedarfsmedicationMaster {

		public function getbedarfsmedication($cid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('BedarfsmedicationMaster')
				->where("clientid=?", $cid)
				->andWhere("isdelete=0");

			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

		public function getbedarfsmedicationDrop($cid)
		{
			$Tr = new Zend_View_Helper_Translate();
			$drop = Doctrine_Query::create()
				->select("*")
				->from('BedarfsmedicationMaster')
				->where("clientid=?", $cid)
				->andWhere("isdelete=0");
			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				$locations = array("" => $Tr->translate('select'));

				foreach($livearr as $location)
				{
					$locations[$location[id]] = $location['title'];
				}
				
				return $locations;
			}
		}

		public function getbedarfsmedicationById($bid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('BedarfsmedicationMaster')
				->where("id=?", $bid);
			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

	}

?>