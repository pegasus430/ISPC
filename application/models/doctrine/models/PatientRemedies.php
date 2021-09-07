<?php

Doctrine_Manager::getInstance()->bindComponent('PatientRemedies', 'MDAT');

class PatientRemedies extends BasePatientRemedies {
	
		public function get_remedies($ipid)
		{
			$drop = Doctrine_Query::create()
			->select('*')
			->from('PatientRemedies')
			->where("ipid = ?", $ipid )
			->andWhere('isdelete = 0')
			;
			$droparray = $drop->fetchArray();
		
			if($droparray)
			{
				return $droparray;
				var_dump($$droparray);
			}
			else
			{
				return false;
			}
		}
		
		
		
		public function insert_remedies($post)
		{
			$decid = Pms_Uuid::decrypt($_GET['id']);
			$ipid = Pms_CommonData::getIpid($decid);
			
			
			
			$rm= new PatientRemedies();
			$rm->ipid = $ipid;
			$rm->remedies = $post['remedies'];
			$rm->supplier = $post['supplier'];
			$rm->save();
			return $rm;
			
			
		}
		
		public function update_remedies($post)
		{ 
			
		}
		
		
		
	/**
	 * default remedies for some checkbox- introduced 20.11.2017
	 * @var array
	 */
// 	public static function getDefaultRemedies()
// 	{
// 	    $Tr = new Zend_View_Helper_Translate();
// 	    $lang = $Tr->translate('Form_PatientRemedy');
// 	    return array(
// 	        'care bed' => $lang['care bed'],
// 	        'electrical insert frame' => $lang['electrical insert frame'],
// 	        'IV pole' => $lang['IV pole'],
// 	        'commode chair' => $lang['commode chair'],
// 	        'Toilet riser' => $lang['Toilet riser'],
// 	        'breathing apparatus' => $lang['breathing apparatus'],
// 	        'wheelchair' => $lang['wheelchair'],
// 	        'rollator' => $lang['rollator'],
// 	        'Alternating pressure mattress' => $lang['Alternating pressure mattress'],
// 	        'Soft bedding mattress' => $lang['Soft bedding mattress'],
// 	        'other' => $lang['other'],
// 	    );
	     
// 	}
	
	public static function getDefaultRemedies()
	{
	    $Tr = new Zend_View_Helper_Translate();
	    $lang = $Tr->translate('Form_PatientRemedy');
	    return array(
	        $lang['care bed'] => $lang['care bed'],
	        $lang['electrical insert frame'] => $lang['electrical insert frame'],
	        $lang['IV pole'] => $lang['IV pole'],
	        $lang['commode chair'] => $lang['commode chair'],
	        $lang['Toilet riser'] => $lang['Toilet riser'],
	        $lang['breathing apparatus'] => $lang['breathing apparatus'],
	        $lang['wheelchair'] => $lang['wheelchair'],
	        $lang['rollator'] => $lang['rollator'],
	        $lang['Alternating pressure mattress'] => $lang['Alternating pressure mattress'],
	        $lang['Soft bedding mattress'] => $lang['Soft bedding mattress'],
	        $lang['other'] => $lang['other'],
	    );
	
	}
	
	public function deleteRemedies($ipid= '', $remedies = array())
	{
	    if (empty($ipid) || empty($remedies) || ! is_array($remedies)){
	        return ;
	    }
	    
	    $q = $this->getTable()->createQuery()
	    ->update()
	    ->set('isdelete', 1)
	    ->where('ipid = ?' , $ipid)
	    ->andWhereIn('remedies', $remedies)
	    ->execute();
	    
	    return $q;
	}
	
	public function findOrCreateOneByIpidAndRemedies($ipid, $remedies, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	{
	    if ( is_null($ipid) || is_null($remedies) || ! $entity = $this->getTable()->findOneByIpidAndRemedies($ipid, $remedies)) {
	        	
            $entity = $this->getTable()->create(array( 'ipid' => $ipid, 'remedies' => $remedies));
            
	    }
	    unset($data[$this->getTable()->getIdentifier()]); // just in case
	
	    $entity->fromArray($data); //update
	
	    $entity->save(); //at least one field must be dirty in order to persist
	
	    return $entity;
	}
}
	
?>