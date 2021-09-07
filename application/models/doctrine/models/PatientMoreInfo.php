<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientMoreInfo', 'IDAT');

	class PatientMoreInfo extends BasePatientMoreInfo {

		public function getpatientMoreInfoData($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientMoreInfo')
				->where("ipid= ? " , $ipid);

			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

		public function clone_record($ipid, $target_ipid)
		{
			$patient_moreinfo = $this->getpatientMoreInfoData($ipid);

			if($patient_moreinfo)
			{
				foreach($patient_moreinfo as $k_moreinfo => $v_moreinfo)
				{
					$pmi = new PatientMoreInfo();
					$pmi->ipid = $target_ipid;
					$pmi->dk = $v_moreinfo['dk'];
					$pmi->peg = $v_moreinfo['peg'];
					$pmi->port = $v_moreinfo['port'];
					$pmi->pumps = $v_moreinfo['pumps'];
					$pmi->zvk = $v_moreinfo['zvk'];
					$pmi->magensonde = $v_moreinfo['magensonde'];
					$pmi->pegmore = $v_moreinfo['pegmore'];
					$pmi->portmore = $v_moreinfo['portmore'];
					$pmi->save();
				}
			}
		}
		
		public function findOrCreateOneByIpidAndId($ipid = '', $id = 0, array $data = array())
		{
		//var_dump($data);exit;
			if ( empty($id) || ! $entity = $this->getTable()->findOneByIpidAndId($ipid, $id )) {
		
				$entity = $this->getTable()->create(array('ipid' => $ipid));
			}
		
			unset($data[$this->getTable()->getIdentifier()]);
		
			$entity->fromArray($data); //update
		
			$entity->save(); //at least one field must be dirty in order to persist
		
			return $entity;
		}

	}

?>