<?php

Doctrine_Manager::getInstance()->bindComponent('PatientPflegedienste', 'SYSDAT');

class PatientPflegedienste extends BasePatientPflegedienste 
{

	    public static function getDefaultsPflegeEmergency(){
	        return array(
    	        '0' => 'Nur Pflege',
    	        '1' => 'Hausnotruf & Pflege',
    	        '2' => 'nur Hausnotruf',
	       );
	    }
	    
		public function getPatientPflegedienste($ipid, $pflid = false)
		{
			$sql = "*, id as pf_id";
			$sql .=",nursing as pf_nursing";
			$sql .=",pflege_comment as pf_com";
			$sql .=",phone_practice as pf_phone_practice";
			$sql .=",pflege_emergency as pflege_emergency";
			$sql .=",pflege_emergency_comment as pflege_emergency_comment";
			$sql .=",phone_emergency as pf_phone_emergency";
			$sql .=",fax as pf_fax";


			/*if($pflid)
			 {
			 $q = "PatientPflegedienste.pflid = " . $pflid . " and";
			 }
			 else
			 {
			 $q = "";
			 }*/
			
			$drop = Doctrine_Query::create()
			->select($sql)
			->from('Pflegedienstes')
			->leftJoin('PatientPflegedienste')
			//->where("" . $q . " PatientPflegedienste.pflid = Pflegedienstes.id and PatientPflegedienste.ipid='" . $ipid . "' and PatientPflegedienste.isdelete = 0 ");
			->where("PatientPflegedienste.pflid = Pflegedienstes.id")
			->andWhere("PatientPflegedienste.ipid=?", $ipid)
			->andWhere("PatientPflegedienste.isdelete = 0");
			if($pflid)
			{
				$drop->andWhere("PatientPflegedienste.pflid =?", $pflid);
			}
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();
			//var_dump($droparray); exit;
			return $droparray;
		}

		//used in reports
		public function get_multiple_patient_pflegedienste($ipid, $pflid = false,$ppd = false)
		{
			$sql = "*, id as pf_id";
			$sql .=",nursing as pf_nursing";
			$sql .=",pflege_comment as pf_com";
			$sql .=",phone_practice as pf_phone_practice";
			$sql .=",pflege_emergency as pflege_emergency";
			$sql .=",pflege_emergency_comment as pflege_emergency_comment";
			$sql .=",phone_emergency as pf_phone_emergency";
			$sql .=",fax as pf_fax";
			$sql .=",ipid asi ipid";

			/*if($pflid)
			{
				$q = "PatientPflegedienste.pflid = " . $pflid . " and";
			}
			else
			{
				$q = "";
			}*/

			if($ppd)
			{
				$q_ppd = " Pflegedienstes.palliativpflegedienst = 1 and";
			}
			else
			{
				$q_ppd = "";
			}

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Pflegedienstes')
				->leftJoin('PatientPflegedienste')
				//->where("" . $q . "" . $q_ppd . " PatientPflegedienste.pflid = Pflegedienstes.id and PatientPflegedienste.isdelete = 0 ")
				->where("" . $q_ppd . " PatientPflegedienste.pflid = Pflegedienstes.id")
				->andWhere("PatientPflegedienste.isdelete = 0 ")
				->andWhereIn('PatientPflegedienste.ipid', $ipid);
			if($pflid)
			{
				$drop->andWhere("PatientPflegedienste.pflid =?", $pflid);	
			}
			$droparray = $drop->fetchArray();

			foreach($droparray as $k_drop => $v_drop)
			{
				$nursing_array['results'][$v_drop['ipid']][] = $v_drop;
				$nursing_array['counted'][$v_drop['ipid']] += '1';
			}
			
			return $nursing_array;
		}

		public function getPatientLastPflegedienste($ipid)
		{
			$drop = Doctrine_Query::create()
				->select('*')
				->from('PatientPflegedienste')
				->where("ipid=?", $ipid)
				->andWhere("isdelete = 0")
				->orderBy('id desc')
				->limit(1);
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			return $droparray;
		}

		public function getPatientLastPflegediensteNew($ipid, $ppd = false)
		{
			if($ppd)
			{
				$q = "Pflegedienstes.palliativpflegedienst = 1 and";
			}
			else
			{
				$q = "Pflegedienstes.palliativpflegedienst = 0 and";
			}
			$drop = Doctrine_Query::create()
				->select('Pflegedienstes.id as pflege_id')
				->from('Pflegedienstes')
				->leftJoin('PatientPflegedienste')
				->where("" . $q . " PatientPflegedienste.pflid = Pflegedienstes.id")
				->andWhere("PatientPflegedienste.ipid=?", $ipid)
				->andWhere("PatientPflegedienste.isdelete  = 0")
				->orderBy('PatientPflegedienste.change_date DESC')
				->limit(1);
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			return $droparray;
		}

		public function getPatientLastPflegediensteDetails($ipid)
		{
			$sql = "*, id as pf_id";
			$sql .=",nursing as pf_nursing";
			$sql .=",pflege_comment as pf_com";
			$sql .=",phone_practice as pf_phone_practice";
			$sql .=",pflege_emergency as pflege_emergency";
			$sql .=",pflege_emergency_comment as pflege_emergency_comment";
			$sql .=",phone_emergency as pf_phone_emergency";
			$sql .=",fax as pf_fax";

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Pflegedienstes')
				->leftJoin('PatientPflegedienste')
				->where("PatientPflegedienste.pflid = Pflegedienstes.id")
				->andWhere("PatientPflegedienste.ipid=?", $ipid)
				->andWhere("PatientPflegedienste.isdelete = 0")
				->orderBy('PatientPflegedienste.create_date DESC')
				->limit(1);
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			if($droparray)
			{
				return $droparray[0];
			}
			else
			{
				return false;
			}
		}

		public function getPatientFirstPflegediensteDetails($ipid)
		{
			$sql = "*, id as pf_id";
			$sql .=",nursing as pf_nursing";
			$sql .=",pflege_comment as pf_com";
			$sql .=",phone_practice as pf_phone_practice";
			$sql .=",pflege_emergency as pflege_emergency";
			$sql .=",pflege_emergency_comment as pflege_emergency_comment";
			$sql .=",phone_emergency as pf_phone_emergency";
			$sql .=",fax as pf_fax";

			$drop = Doctrine_Query::create()
				->select($sql)
				->from('Pflegedienstes')
				->leftJoin('PatientPflegedienste')
				->where("PatientPflegedienste.pflid = Pflegedienstes.id")
				->andWhere("PatientPflegedienste.ipid=?", $ipid)
				->andWhere("PatientPflegedienste.isdelete = 0")
				->orderBy('PatientPflegedienste.create_date ASC')
				->limit(1);
			$dropexec = $drop->execute();
			$droparray = $dropexec->toArray();

			if($droparray)
			{
				return $droparray[0];
			}
			else
			{
				return false;
			}
		}
		/**
		 * ISPC-2614 Ancuta 19.07.2020 Renamed function to *_old
		 * @param unknown $ipid
		 * @param unknown $target_ipid
		 * @param unknown $target_client
		 * @return unknown
		 */
		public function clone_records_old($ipid, $target_ipid, $target_client)
		{
			//get patient pflegedienstes
			$pfleges = $this->getPatientPflegedienste($ipid);
			if($pfleges)
			{
				foreach($pfleges as $k_pfl => $v_pfl)
				{
					$pfl = new Pflegedienstes();
					$master_pfl = $pfl->clone_record($v_pfl['id'], $target_client);

					if($master_pfl)
					{
						$pfl_cl = new PatientPflegedienste();
						$pfl_cl->ipid = $target_ipid;
						$pfl_cl->pflid = $master_pfl;
						$pfl_cl->pflege_comment = $v_pfl['pflege_comment'];
						$pfl_cl->pflege_emergency = $v_pfl['pflege_emergency'];
						$pfl_cl->pflege_emergency_comment = $v_pfl['pflege_emergency_comment'];
						$pfl_cl->save();
					}
				}

				return $pfl->id;
			}
		}
		
		/**
		 * ISPC-2614 Ancuta 19.07.2020
		 * copy of function clone_records_old
		 * changed so all columns are retrived
		 * @param unknown $ipid
		 * @param unknown $target_ipid
		 * @param unknown $target_client
		 * @return unknown
		 */
		public function clone_records($ipid, $target_ipid, $target_client)
		{
			//get patient pflegedienstes
			$pfleges = $this->getPatientPflegedienste($ipid);
			if($pfleges)
			{
			    $obj = new PatientPflegedienste();
			    $obj_columns = $obj->getTable()->getColumns();
			    
				foreach($pfleges as $k_pfl => $v_pfl)
				{
					$pfl = new Pflegedienstes();
					$master_pfl = $pfl->clone_record($v_pfl['id'], $target_client);

					if($master_pfl)
					{
						$pfl_cl = new PatientPflegedienste();
						//ISPC-2614 Ancuta 16.07.2020 :: deactivate listner for clone
						$pc_listener = $pfl_cl->getListener()->get('IntenseConnectionListener');
						$pc_listener->setOption('disabled', true);
						//--
						foreach($obj_columns as $column_name =>$column_info){
						    if(!in_array($column_name,array('id','ipid','pflid','create_date'))) {
						        $pfl_cl->$column_name = $v_pfl[$column_name];
						    }
						    $pfl_cl->pflege_comment = $v_pfl['pf_com'];
    						$pfl_cl->ipid = $target_ipid;
    						$pfl_cl->pflid = $master_pfl;
						}
						$pfl_cl->save();
						//ISPC-2614 Ancuta 16.07.2020	:: activate lister after clone
						$pc_listener->setOption('disabled', false);
						//--
					}
				}

				return $pfl->id;
			}
		}
		
	public static function beautifyName( &$usrarray )
	{
	    if ( empty($usrarray) || ! is_array($usrarray)) {
	        return;
	    }
			     
	    foreach ( $usrarray as &$k )
	    {
	
	        if ( ! is_array($k) || isset($k['nice_name'])) {
	            continue; // varaible allready exists, use another name for the variable
	        }
	        
	        if (isset($k['Pflegedienstes'])) {
	            $k ['nice_name']  = trim($k['Pflegedienstes']['title']) != "" ? trim($k['Pflegedienstes']['title']) . " " : "";
	            $k ['nice_name']  .= trim($k['Pflegedienstes']['last_name']);
	            $k ['nice_name']  .= trim($k['Pflegedienstes']['first_name']) != "" ? (", " . trim($k['Pflegedienstes']['first_name'])) : "";
	            $k ['nice_name']  .= trim($k['Pflegedienstes']['nursing']) != "" ? " (" . trim($k['Pflegedienstes']['nursing']) . ')' : "";

	        } else {
    	        $k ['nice_name']  = trim($k['title']) != "" ? trim($k['title']) . " " : "";
    	        $k ['nice_name']  .= trim($k['last_name']);
    	        $k ['nice_name']  .= trim($k['first_name']) != "" ? (", " . trim($k['first_name'])) : "";
    	        $k ['nice_name']  .= trim($k['nursing']) != "" ? " (" . trim($k['nursing']) . ')' : ""; 
	        }
	            
	
	    }
	}
	
	
	public function findOrCreateOneByIpidAndId($ipid = '', $id = 0, array $data = array())
	{
	
	    if ( empty($id) || ! $entity = $this->getTable()->findOneByIpidAndId($ipid, $id)) {
	
	        $entity = $this->getTable()->create(array('ipid' => $ipid));
	        unset($data[$this->getTable()->getIdentifier()]);
	    }
	
	    $entity->fromArray($data); //update
	
	    $entity->save(); //at least one field must be dirty in order to persist
	
	    return $entity;
	}

	
	
	public function gatAllPatientPflegedienstes($ipid = '')
	{
	    if (empty($ipid)) {
	        return; //fail-safe
	    }
	    
	    $result = Doctrine_Query::create()
	    ->select('*, p.*')
	    ->from('PatientPflegedienste pp')
	    ->leftJoin('pp.Pflegedienstes p ON pp.pflid = p.id')
	    ->andWhere("pp.ipid= ? ", $ipid)
	    ->andWhere("pp.isdelete = 0")
	    ->fetchArray();
	    
	    self::beautifyName($result);
	    
	    return $result;
	}
}

?>