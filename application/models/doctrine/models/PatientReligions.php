<?php

Doctrine_Manager::getInstance()->bindComponent('PatientReligions', 'IDAT');

class PatientReligions extends BasePatientReligions {

		public function getReligionsData($ipid)
		{
			$drop = Doctrine_Query::create()
				->select("*")
				->from('PatientReligions')
				->where("ipid='" . $ipid . "'");

			$loc = $drop->execute();

			if($loc)
			{
				$livearr = $loc->toArray();
				return $livearr;
			}
		}

		public static function getReligionsNames($drop = false )
		{
			$evangelisch = self::translate('evangelisch');
			$katholisch = self::translate('katholisch');
			$orthodox = self::translate('orthodox');
			$judisch = self::translate('judisch');
			$muslimisch = self::translate('muslimisch');
			$keine = self::translate('keine');
			$sonstige = self::translate('sonstige');

			if($drop){
    			$relisionarray = array(
    			    '0'=> self::translate('select'), 
    			    '1' => $evangelisch, 
    			    '2' => $katholisch, 
    			    '3' => $orthodox, 
    			    '4' => $judisch, 
    			    '5' => $muslimisch, 
    			    '6' => $keine, 
    			    '7' => $sonstige,
    			);
			    
			} else {
    			$relisionarray = array(
    			    '1' => $evangelisch, 
    			    '2' => $katholisch, 
    			    '3' => $orthodox, 
    			    '4' => $judisch, 
    			    '5' => $muslimisch, 
    			    '6' => $keine, 
    			    '7' => $sonstige,
    			);
			}

			return $relisionarray;
		}

		public function clone_record($ipid, $target_ipid)
		{
			$pat_religions = $this->getReligionsData($ipid);

			if($pat_religions)
			{
				foreach($pat_religions as $k_religion => $v_religion)
				{
					$prel = new PatientReligions();
					$prel->ipid = $target_ipid;
					$prel->religion = $v_religion['religion'];
					$prel->save();
				}
			}
		}

		
	public function findOrCreateOneBy($fieldName, $value, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	{
	    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
	
	        if ($fieldName != $this->getTable()->getIdentifier()) {
	            $entity = $this->getTable()->create(array( $fieldName => $value));
	        } else {
	            $entity = $this->getTable()->create();
	        }
	    }
	    unset($data[$this->getTable()->getIdentifier()]); // just in case
	
	    $entity->fromArray($data); //update
	
	    $entity->save(); //at least one field must be dirty in order to persist
	
	    return $entity;
	}
}

?>