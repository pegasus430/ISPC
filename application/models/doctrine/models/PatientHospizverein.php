<?php

Doctrine_Manager::getInstance()->bindComponent('PatientHospizverein', 'IDAT');

class PatientHospizverein extends BasePatientHospizverein {

	public function getHospizvereinData($ipid)
	{
		$drop = Doctrine_Query::create()
			->select("*")
			->from('PatientHospizverein')
			->where("ipid='" . $ipid . "'");

		$loc = $drop->execute();

		if($loc)
		{
			$livearr = $loc->toArray();
			return $livearr;
		}
	}
	
	
	
	/**
	 * 
	 * @param string $ipid
	 * @param unknown $hydrateMode
	 * @return mixed
	 */
	public function findOneByIpid($ipid = '', $hydrateMode = Doctrine_Core::HYDRATE_ARRAY)
	{
	    if (empty($ipid) || !is_string($ipid)) {
	        return;
	    }
	    
	    return $this->getTable()->createQuery()
	    ->select('*')
	    ->where('ipid = :ipid')
	    ->fetchOne(array('ipid' => $ipid), $hydrateMode)
	    ;
	    
	    
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Pms_Doctrine_Record::findOrCreateOneBy()
	 * @deprecated
	 */
// 	public function findOrCreateOneBy($fieldName, $value, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
// 	{
// 	    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
// 	        $entity = $this->getTable()->create(array( $fieldName => $value));
// 	    }
	
// 	    $entity->fromArray($data); //update
	
// 	    $entity->save(); //at least one field must be dirty in order to persist
	
// 	    return $entity;
// 	}
	
	

	
}

?>