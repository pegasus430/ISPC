<?php

Doctrine_Manager::getInstance()->bindComponent('Specialists', 'SYSDAT');

class Specialists extends BaseSpecialists {

		public function get_specialist($ids)
		{
			if(is_array($ids))
			{
				$array_ids = $ids;
			}
			else
			{
				$array_ids = array($ids);
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Specialists')
				->whereIn("id", $array_ids);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function get_specialists_addressbook($ipid = false, $letter = false, $keyword = false, $arrayids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipid != false)
			{
				$pat_specialists = new PatientSpecialists();
				$pat_specialists_array = $pat_specialists->get_patient_specialists($ipid, false);

				if(count($pat_specialists_array) > 0)
				{
					foreach($pat_specialists_array as $keyps => $valueps)
					{
						$pat_spec_array[$keyps] = $valueps['sp_id'];
					}
					$ids = implode(",", $pat_spec_array);

					$ipid_sql .= " AND id IN (" . $ids . ")";
				}
				else
				{
					$ipid_sql .= " AND id IN (0)";
				}
			}
			else
			{
				$ipid_sql = " AND indrop=0 AND isdelete =0 AND valid_till = '0000-00-00'";
			}

			if($keyword != false)
			{
				$keyword_sql = " AND (practice like '%" . ($keyword) . "%' OR first_name like '%" . ($keyword) . "%' OR last_name like '%" . ($keyword) . "%')";
			}

			if($letter != false)
			{
				$keyword_sql = " AND practice like '" . ($letter) . "%'";
			}

			if($arrayids != false)
			{
				$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
				$ipid_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Specialists')
				->where("clientid='" . $clientid . "' AND (practice != '' or first_name != '' or last_name != '') " . $ipid_sql . $keyword_sql . $array_sql)
// 			    ->orderBy('last_name ASC')
			;
			$droparray = $drop->fetchArray();

			return $droparray;
		}
		
		public function clone_record($spec_id, $target_client)
		{
			//get curent specialist
			$spec = $this->get_specialist($spec_id);
		
			if($spec)
			{
				$sdoc = new Specialists();
				$sdoc->clientid = $target_client;
				$sdoc->practice = $spec[0]['practice'];
				$sdoc->last_name = $spec[0]['last_name'];
				$sdoc->first_name = $spec[0]['first_name'];
				$sdoc->title = $spec[0]['title'];
				$sdoc->salutation = $spec[0]['salutation'];
				$sdoc->title_letter = $spec[0]['title_letter'];
				$sdoc->salutation_letter = $spec[0]['salutation_letter'];
				$sdoc->street1 = $spec[0]['street1'];
				$sdoc->street2 = $spec[0]['street2'];
				$sdoc->zip = $spec[0]['zip'];
				$sdoc->city = $spec[0]['city'];
				$sdoc->doctornumber = $spec[0]['doctornumber'];
				$sdoc->phone_practice = $spec[0]['phone_practice'];
				$sdoc->phone_private = $spec[0]['phone_private'];
				$sdoc->phone_cell = $spec[0]['phone_cell'];
				$sdoc->fax = $spec[0]['fax'];
				$sdoc->email = $spec[0]['email'];
				$sdoc->kv_no = $spec[0]['kv_no'];
				$sdoc->medical_speciality = $spec[0]['medical_speciality'];
				$sdoc->comments = $spec[0]['comments'];
				$sdoc->valid_from = $spec[0]['valid_from'];
				$sdoc->valid_till = $spec[0]['valid_till'];
				$sdoc->indrop = '1';
				$sdoc->save();
		
				if($sdoc)
				{
					return $sdoc->id;
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

		
	/*
	public function findOrCreateOneBy($fieldName, $value, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	{
	    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
	
	        if ($fieldName != $this->getTable()->getIdentifier()) {
	            $entity = $this->getTable()->create(array( $fieldName => $value));
	        } else {
	            $entity = $this->getTable()->create();
	        }
	        $data['indrop'] = isset($data['indrop']) ? $data['indrop'] : 1;
	
	    }
        unset($data[$this->getTable()->getIdentifier()]);
	
	    $entity->fromArray($data); //update
	
	    $entity->save(); //at least one field must be dirty in order to persist
	
	    return $entity;
	}
	*/
		
}

?>