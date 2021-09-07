<?php

	Doctrine_Manager::getInstance()->bindComponent('DischargeLocation', 'MDAT');

	class DischargeLocation extends BaseDischargeLocation {

	    public function getDischargeLocation($clientid, $isdrop = 0 ,$sorted = false, $show_only_from_master =  false)
		{
			$Tr = new Zend_View_Helper_Translate();
			$loc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('DischargeLocation')
				->where('clientid=' . $clientid . ' and isdelete=0');
				if($show_only_from_master){//ISPC-2612 Ancuta 28.06.2020
				    $loc->andWhere('connection_id is NOT null');
				    $loc->andWhere('master_id is NOT null');
				}
			$epidexec = $loc->execute();
			$disarray = $epidexec->toArray();

			if($isdrop == 1)
			{
			    
			    if($sorted)
			    {
			        foreach($disarray as $discharge)
			        {
			            $discharges_array[$discharge['id']] = $discharge['location'];
			        }
			        $sorted_array = Pms_CommonData::a_sort($discharges_array);
			        
			        $discharges = array("" => $Tr->translate('selectdischargelocation'));
			        
			        foreach($sorted_array as $did =>$dvalue){
			            $discharges[$did] = $dvalue;
			        } 
			    } 
			    else
			    {
			    
    				$discharges = array("" => $Tr->translate('selectdischargelocation'));
    
    				foreach($disarray as $discharge)
    				{
    					$discharges[$discharge['id']] = $discharge['location'];
    				}
			    } 
				
				return $discharges;
			}
			else
			{
				return $disarray;
			}
		}
		
		public function getClientsDischargeLocation($client_array)
		{
		    if(!empty($client_array)){
    			$Tr = new Zend_View_Helper_Translate();
    			
    			$loc = Doctrine_Query::create()
    				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
    				->from('DischargeLocation')
    				->where('isdelete=0')
    				->andWhere('clientid',$client_array);
    			$epidexec = $loc->execute();
    			$disarray = $epidexec->toArray();
    
    			foreach($disarray as $k=>$ddata){
    			    $discharge_locations[$ddata['clientid']][] = $ddata; 
    			    
    			}
    			return $discharge_locations;
		    }
		}

		public function getDischargeLocationbyId($lid, $clientid)
		{
			$Tr = new Zend_View_Helper_Translate();
			$fdoc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('DischargeLocation')
				->where("id='" . $lid . "'")
				->andWhere('clientid=' . $clientid . ' and isdelete=0')
				->orderBy('location ASC');
//			print_r($fdoc->getSqlQuery());
			return $fdoc->fetchArray();
		}

		public function getDischargeLocationbyType($tid, $clientid)
		{
			$Tr = new Zend_View_Helper_Translate();
			$fdoc = Doctrine_Query::create()
				->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
				->from('DischargeLocation')
				->where("type='" . $tid . "'")
				->andWhere('clientid=' . $clientid . ' and isdelete=0')
				->orderBy('location ASC');

			return $fdoc->fetchArray();
		}

		
		/**
		 * @author Ancuta 
		 * ISPC-2612 Ancuta 28.06.2020
		 * @param unknown $lid
		 * @return array|Doctrine_Collection
		 */
		public function get_Discharge_Location_by_Id($lid)
		{
		    $Tr = new Zend_View_Helper_Translate();
		    $fdoc = Doctrine_Query::create()
		    ->select("*,AES_DECRYPT(location,'" . Zend_Registry::get('salt') . "') as location")
		    ->from('DischargeLocation')
		    ->where("id=?", $lid)
		    ->orderBy('location ASC');
		    return $fdoc->fetchArray();
		}
		
	}

?>