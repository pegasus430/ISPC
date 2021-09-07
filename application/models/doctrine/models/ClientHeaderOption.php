<?php

//ISPC-2593 Lore 19.05.2020
//#ISPC-2512PatientCharts
    Doctrine_Manager::getInstance()->bindComponent('ClientHeaderOption', 'MDAT');

	class ClientHeaderOption extends BaseClientHeaderOption {

	    
	    public function findById( $id = '', $hydrationMode = Doctrine_Core::HYDRATE_ARRAY )
	    {
	        if (empty($id) || !is_string($id)) {
	            
	            return;
	            
	        } else {
	            return $this->getTable()->findBy('id', $id, $hydrationMode);
	            
	        }
	    }
	    
	    public function findOrCreateOneById($id = 0 , array $data = array(), $hydrationMode = Doctrine_Core::HYDRATE_RECORD)
	    {
	        $primaryKey = $this->getTable()->getIdentifier();
	        
	        /*
	         * do not allow to overwrite the $primaryKey
	         */
	        if (isset($data[$primaryKey])) {
	            unset($data[$primaryKey]);
	        }
	        
	        /*
	         * prevent changes to fields populated by Timestamp Listener
	         */
	        if (isset($data['create_date']) || isset($data['change_date']) || isset($data['create_user']) || isset($data['change_user'])) {
	            unset($data['create_date'], $data['change_date'], $data['create_user'], $data['change_user']);
	        }
	        
	        
	        if ( empty($id)) {
	            
	            $entity = $this->getTable()->create();
	            
	            $entity->assignDefaultValues(false);
	            
	        } else {
	            /*
	             * this is an update of $entity
	             */
	            $entity = $this->getTable()->findOneBy('id', $id, Doctrine_Core::HYDRATE_RECORD);
	        }
	        
	        
	        
	        
	        //$this->_encryptData($data); // encrypt model->_encypted_columns
	        
	        //TODO maybe add a check ??? empty($data) is_array($data) count($data, COUNT_RECURSIVE)) ... what?
	        $entity->fromArray($data); //update
	        
	        
	        $entity->save(); //at least one field must be dirty in order to persist
	        
	        return $entity;
	    }
	    
	    
	    public static function get_client_header_option($clientid)
		{
			$q = Doctrine_Query::create()
				->select('*')
				->from("ClientHeaderOption")
				->where("clientid= ?", $clientid )
				->andWhere("isdelete = 0");
			
			$header_option = $q->fetchArray();

			if(!empty($header_option))
			{
		        foreach($header_option as $k=>$s_value)
				{
				    $header_option_arr = $s_value['header_option'];
				}

			}
			else
			{
			    $header_option_arr = 'show_location';
			    
			}
			
			return $header_option_arr;
			
		}
		

	}

?>