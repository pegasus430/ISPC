<?php

Doctrine_Manager::getInstance()->bindComponent('DiagnosisText', 'SYSDAT');

class DiagnosisText extends BaseDiagnosisText 
{

	public function getDiagnosisTextData($ipval)
	{

		$drugs = Doctrine_Query::create()
			->select('*')
			->from('DiagnosisText')
			->where("id in (" . $ipval . ")");

		$dr = $drugs->execute();

		if($dr)
		{
			$drugsarray = $dr->toArray();
			return $drugsarray;
		}
	}

		
    /**
     * 
     * @param string $fieldName
     * @param string $value
     * @param array $data
     * @param unknown $hydrationMode
     * @return Doctrine_Record
     */       
    public function findOrCreateOneBy($fieldName = '', $value = null, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	{
	    if ( is_null($value) || ! $entity = $this->getTable()->findOneBy($fieldName, $value, $hydrationMode)) {
	
	        if ($fieldName != $this->getTable()->getIdentifier()) {
	            $entity = $this->getTable()->create(array( $fieldName => $value));
	        } else {
	            $entity = $this->getTable()->create();
	        }	
	    }
	    unset($data[$this->getTable()->getIdentifier()]);
	
	    $entity->fromArray($data); //update
	
	    $entity->save(); //at least one field must be dirty in order to persist
	
	    return $entity;
	}
}

?>