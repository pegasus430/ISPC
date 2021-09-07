<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientReferredBy', 'SYSDAT');

	class PatientReferredBy extends BasePatientReferredBy {

		public function getPatientReferredBy($clientid, $isdrop)
		{
		    
		    //ISPC-2612 Ancuta 27.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('PatientReferredBy',$clientid);

			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientReferredBy')
				->where("clientid =" . $clientid)
				->andWhere('isdelete=0');
				if($client_is_follower){//ISPC-2612 Ancuta 29.06.2020
				    $drop->andWhere('connection_id is NOT null');
				    $drop->andWhere('master_id is NOT null');
				}
				$drop->orderBy('referred_name ASC');

			$dropexec = $drop->execute();
			$referedby = array();

			if($dropexec)
			{
				if($isdrop == 1)
				{
					$referedby = array("" => "");
					foreach($dropexec->toArray() as $key => $val)
					{
						$referedby[$val['id']] = $val['referred_name'];
					}
				}
				else
				{
					$referedby = $dropexec->toArray();
				}

				return $referedby;
			}
		}

		public function getPatientReferredByreport($clientid, $isdrop)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientReferredBy')
				->where("clientid =" . $clientid)
				->orderBy('referred_name ASC');
			$dropexec = $drop->execute();
			$referedby = array();

			if($dropexec)
			{
				if($isdrop == 1)
				{
					$referedby = array("" => "");
					foreach($dropexec->toArray() as $key => $val)
					{
						$referedby[$val['id']] = $val['referred_name'];
					}
				}
				else
				{
					$referedby = $dropexec->toArray();
				}

				return $referedby;
			}
		}

	}

?>