<?php

Doctrine_Manager::getInstance()->bindComponent('WoundDocumentation', 'MDAT');

class WoundDocumentation extends BaseWoundDocumentation {

	    public static function getDefaultsWoundType() {
	        return array(
	            '1' => 'Exulcerierende Tumorwunde', //ulceration_wound
	            '2' => 'Dekubitus Grad', //wound_grade
	            '3' => 'Sonstiges', //other_wound
	        );
	    }

	    
		public function get_patient_wound_documentations($ipid,$exclude_closed = false)
		{
			
			$sel = Doctrine_Query::create()
				->select('*')
				->from('WoundDocumentation')
				->where("ipid LIKE '" . $ipid . "'  ")
				->andWhere("isdelete = 0");
			if($exclude_closed){
				$sel->andWhere("w_isclosed != 1");
			}
				$sel->orderBy('create_date DESC');
			$sel_res = $sel->fetchArray();
			
			return $sel_res;
		}

		public function get_wound_documentation($formid)
		{
			$sel = Doctrine_Query::create()
				->select('*')
				->from('WoundDocumentation')
				->where("id='" . $formid . "'  and isdelete = 0");
			$sel_res = $sel->fetchArray();
			
			return $sel_res;
		}

		
		
		
	public function findOrCreateOneByIpidAndId($ipid = '', $id = 0, array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	{
	    
	    if ( empty($id) || ! $entity = $this->getTable()->findOneByIpidAndId($ipid, $id)) {
           $entity = $this->getTable()->create(array( 'ipid' => $ipid));
	    }
	    unset($data[$this->getTable()->getIdentifier()]);
	
	    $entity->fromArray($data); //update
	
	    $entity->save(); //at least one field must be dirty in order to persist
	
	    return $entity;
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
	
	public static function _getWoundDescriptionOptions() {
		//ISPC-2465
		return array( 
				'Wundheilungsphase' =>
					array(
						'0' => 'Exsudationsphase',
						'1' => 'Granulationsphase',
						'2' => 'Epithelisierungsphase',
						),
				'Wundrand' =>
					array(
							'0' => 'abgegrenzt',
							'1' => 'unterminiert',
							'2' => 'eingerollt',
							'3' => 'wulstartig verdickt',
							'4' => 'mazeriert',
							'5' => 'gerötet',
					),
				'Wundtasche' => '',
				'Wundgeruch' =>
					array(
							'0' => 'süßlich',
							'1' => 'fäkulent',
							'2' => 'faulig',
							'3' => 'sehr stark',
							'4' => 'stark',
							'5' => 'mäßig',
							'6' => 'wenig',
							'7' => 'kein',
					),
				'Wundexsudat' =>
					array(
							'0' => 'gelblich',
							'1' => 'blutig',
							'2' => 'bräunlich',
							'3' => 'grünlich',
							'4' => 'sehr viel',
							'5' => 'viel',
							'6' => 'mäßig',
							'7' => 'wenig',
							'8' => 'kein',
					),
				'Wundgrund' =>
					array(
							'0' => 'Nekrose schwarz',
							'1' => 'Fibrin',
							'2' => 'Hypergranulation',
							'3' => 'Granulation',
							'4' => 'Epithel',
							'5' => 'Muskel',
							'6' => 'Sehnen',
							'7' => 'Knochen',
					),
				'Nekrose' =>
					array(
							'0' => 'feucht',
							'1' => 'Infiziert',
							'2' => 'trocken',
					),
		);
	}
	
	
	/**
	 * ISPC-2891 ANcuta 20.04.2021
	 * @param unknown $ipids
	 * @param boolean $exclude_closed
	 * @return void|unknown
	 */
	public function get_multiple_patients_wound_documentations($ipids,$exclude_closed = false)
	{
	    if(empty($ipids)){
	        return;
	    }
	    $sel = Doctrine_Query::create()
	    ->select('*')
	    ->from('WoundDocumentation')
	    ->whereIn("ipid",$ipids)
	    ->andWhere("isdelete = 0");
	    if($exclude_closed){
	        $sel->andWhere("w_isclosed != 1");
	    }
	    $sel->orderBy('create_date DESC');
	    $sel_res = $sel->fetchArray();
	    
	    return $sel_res;
	}
	
				
}

?>