<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientMobility', 'IDAT');

	class PatientMobility extends BasePatientMobility {

		public function getpatientMobilityData($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMobility')
				->where("ipid='" . $ipid . "'");
			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

		public function clone_record($ipid, $target_ipid)
		{
			$mobility = $this->getpatientMobilityData($ipid);

			if($mobility)
			{
				foreach($mobility as $k_mob => $v_mob)
				{
					$pmobility = new PatientMobility();
					$pmobility->ipid = $target_ipid;
					$pmobility->bed = $v_mob['bed'];
					$pmobility->walker = $v_mob['walker'];
					$pmobility->wheelchair = $v_mob['wheelchair'];
					$pmobility->goable = $v_mob['goable'];
					$pmobility->nachtstuhl = $v_mob['nachtstuhl'];
					$pmobility->wechseldruckmatraze = $v_mob['wechseldruckmatraze'];
					$pmobility->bedmore = $v_mob['bedmore'];
					$pmobility->walkermore = $v_mob['walkermore'];
					$pmobility->wheelchairmore = $v_mob['wheelchairmore'];
					$pmobility->goablemore = $v_mob['goablemore'];
					$pmobility->nachtstuhlmore = $v_mob['nachtstuhlmore'];
					$pmobility->wechseldruckmatrazemore = $v_mob['wechseldruckmatrazemore'];
					$pmobility->save();

					return $pmobility;
				}
			}
		}

	}

?>