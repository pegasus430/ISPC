<?php

	Doctrine_Manager::getInstance()->bindComponent('PatientDrugPlanAllergies', 'MDAT');

	class PatientDrugPlanAllergies extends BasePatientDrugPlanAllergies {

		public function getPatientDrugPlanAllergies($pid = 0 , $ipid = null)
		{
			if ( is_null($ipid)) {
				$logininfo = new Zend_Session_Namespace('Login_Info');
				$ipid = Pms_CommonData::getIpid($pid);
			}

			$drugs = Doctrine_Query::create()
				->select('*')
				->from('PatientDrugPlanAllergies')
				->where("ipid = ? ", $ipid)
				->andWhere('allergies_comment NOT LIKE " Keine Allergien / Kommentare%" ')   //TODO-2853 Lore 27.01.2020
				->orderBy("id ASC");
			$drugsarray = $drugs->fetchArray();

			return $drugsarray;
		}
		
		
		
		
		

		public function get_multiple_patient_allergies($ipids = array())
		{
			
			if ( empty($ipids) || ! is_array($ipids)) {
				return;
			}
 
		
			$drop = Doctrine_Query::create()
			->select("*")
			->from('PatientDrugPlanAllergies')
			->whereIn("ipid", $ipids)
			->andWhere('allergies_comment NOT LIKE " Keine Allergien / Kommentare%" '); // remove the patients that have the default allergies saved.
			$allergies_res = $drop->fetchArray();
			
			if($allergies_res)
			{
				foreach($allergies_res as $k_allergies => $v_allergies)
				{
					$allergies_arr[$v_allergies['ipid']] = $v_allergies['allergies_comment'];
				}
		
				return $allergies_arr;
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
	
		unset($data[$this->getTable()->getIdentifier()]);
	
		$entity->fromArray($data); //update
	
		$entity->save(); //at least one field must be dirty in order to persist
	
		return $entity;
	}
	
	
	/**
	 *
	 * @param string|array $ipid
	 * @param int $hydrationMode
	 */
	public function findByIpid( $ipid = '', $hydrationMode = Doctrine_Core::HYDRATE_ARRAY )
	{
	    if (empty($ipid) || !is_string($ipid)) {
	
	        return;
	
	    } else {
	        return $this->getTable()->findBy('ipid', $ipid, $hydrationMode);
	
	    }
	}
}
?>