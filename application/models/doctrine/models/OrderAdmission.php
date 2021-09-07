<?php

	Doctrine_Manager::getInstance()->bindComponent('OrderAdmission', 'MDAT');

	class OrderAdmission extends BaseOrderAdmission {

		public function getOrderAdmissionbyClient($clientid)
		{
			//ISPC-2612 Ancuta 30.06.2020
		    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('OrderAdmission',$clientid);
		    
			$Tr = new Zend_View_Helper_Translate();

			$fdoc = Doctrine_Query::create()
				->select("*")
				->from('OrderAdmission l ')
				->where("l.clientid='" . $clientid . "'");
    		    if($client_is_follower){
    		        $fdoc->andWhere("l.connection_id is NOT null");
    		        $fdoc->andWhere("l.master_id is NOT null");
    		    }
    		    $fdoc->andWhere('l.isdelete=0');

			$locationarray = $fdoc->fetchArray();

			return $locationarray;
		}

	}

?>