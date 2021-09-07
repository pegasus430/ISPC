<?php

Doctrine_Manager::getInstance()->bindComponent('Pflegedienstes', 'SYSDAT');

class Pflegedienstes extends BasePflegedienstes {

		public function getPflegedienste($id)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('Pflegedienstes')
				->where("id = ? ", $id);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		public function getPflegedienstes($ipid = false, $letter = false, $keyword = false, $arrayids = false)
		{
			$logininfo = new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;

			if($ipid != false)
			{
//				var_dump($ipid);
				$pfl = new PatientPflegedienste();
				$pflarray = $pfl->getPatientPflegedienste($ipid);
//				var_dump($pflarray);
				if(count($pflarray) > 0)
				{
					foreach($pflarray as $keypfl => $valuepfl)
					{
						$pflarry[$keypfl] = $valuepfl['id'];
					}
					$ids = implode(",", $pflarry);

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
				$keyword_sql = " AND nursing like '%" . ($keyword) . "%'";
			}

			if($letter != false)
			{
				$keyword_sql = " AND nursing like '" . ($letter) . "%'";
			}

			if($arrayids != false)
			{
				$array_sql = " AND id IN (" . implode(",", $arrayids) . ")";
				$ipid_sql = '';
			}

			$drop = Doctrine_Query::create()
				->select('*')
				->from('Pflegedienstes')
				->where("clientid='" . $clientid . "' AND (nursing != '' or first_name != '' or last_name != '') " . $ipid_sql . $keyword_sql . $array_sql);
			$droparray = $drop->fetchArray();

			return $droparray;
		}

		/**
		 * ISPC-2614 Ancuta 19.07.2020
		 * renamed function
		 * @param unknown $id
		 * @param unknown $target_client
		 * @return unknown|boolean
		 */
		public function clone_record_old($id, $target_client)
		{
			$pflege = $this->getPflegedienste($id);

			if($pflege)
			{
				$fdoc = new Pflegedienstes();
				$fdoc->clientid = $target_client;
				$fdoc->nursing = $pflege[0]['nursing'];
				$fdoc->first_name = $pflege[0]['first_name'];
				$fdoc->last_name = $pflege[0]['last_name'];
				$fdoc->title = $pflege[0]['title'];
				$fdoc->salutation = $pflege[0]['salutation'];
				$fdoc->title_letter = $pflege[0]['title_letter'];
				$fdoc->salutation_letter = $pflege[0]['salutation_letter'];
				$fdoc->street1 = $pflege[0]['street1'];
				$fdoc->street2 = $pflege[0]['street2'];
				$fdoc->zip = $pflege[0]['zip'];
				$fdoc->city = $pflege[0]['city'];
				$fdoc->doctornumber = $pflege[0]['doctornumber'];
				$fdoc->phone_practice = $pflege[0]['phone_practice'];
				$fdoc->phone_emergency = $pflege[0]['phone_emergency'];
				$fdoc->fax = $pflege[0]['fax'];
				$fdoc->phone_private = $pflege[0]['phone_private'];
				$fdoc->email = $pflege[0]['email'];
				$fdoc->kv_no = $pflege[0]['kv_no'];
				$fdoc->medical_speciality = $pflege[0]['medical_speciality'];
				$fdoc->comments = $pflege[0]['comments'];
				$fdoc->valid_from = $pflege[0]['valid_from'];
				$fdoc->valid_till = $pflege[0]['valid_till'];
				$fdoc->isdelete = $pflege[0]['isdelete'];
				$fdoc->indrop = '1';
				$fdoc->palliativpflegedienst = $pflege[0]['palliativpflegedienst'];
				$fdoc->logo = $pflege[0]['logo'];
				$fdoc->ik_number = $pflege[0]['ik_number'];
				$fdoc->ppd = $pflege[0]['ppd'];
				$fdoc->save();

				if($fdoc)
				{
					return $fdoc->id;
				}
				else
				{
					return false;
				}
			}
		}
		
        /**
         * ISPC-2614 Ancuta 19.07.2020
         * @param unknown $id
         * @param unknown $target_client
         * @return unknown|boolean
         * changed function - in order to copy all data -  so function it is not altered every time a new field is added 
         */
		public function clone_record($id, $target_client)
		{
			$pflege = $this->getPflegedienste($id);

			if($pflege)
			{
			    $row = $pflege[0] ;
				$fdoc = new Pflegedienstes();
				
				foreach ($row as $column_name => $value){
				    if( ! in_array($column_name,array('id','clientid','indrop'))){
				        $fdoc->$column_name = $value;
				    }
				    $fdoc->clientid = $target_client;
				    $fdoc->indrop = '1';
				}
				$fdoc->save();

				if($fdoc)
				{
					return $fdoc->id;
				}
				else
				{
					return false;
				}
			}
		}
		
	/**
	 * (non-PHPdoc)
	 * @see Pms_Doctrine_Record::findOrCreateOneBy()
	 * @removed @cla on 19.10.2018
	 */	
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
	
	    $entity->ipid = $this->ipid;
	    
	    $entity->fromArray($data); //update
	
	    $entity->save(); //at least one field must be dirty in order to persist
	
	    return $entity;
	}
	*/

}

?>