<?php

Doctrine_Manager::getInstance()->bindComponent('NutritionFormularList', 'SYSDAT');


class NutritionFormularList extends BaseNutritionFormularList {

	public function get_by_id_and_clientid($id = 0, $clientid = 0)
	{
	    //ISPC-2612 Ancuta 27.06.2020
	    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('NutritionFormularList',$clientid);
	    
		$query = Doctrine_Query::create()
		->select('*')
		->from('NutritionFormularList')
		->where('id =  ?', $id)
		->andwhere('clientid =  ?', $clientid)
		->andWhere('isdelete = 0');
		if($client_is_follower){//ISPC-2612 Ancuta 27.06.2020
		    $query->andWhere('connection_id id NOT null');
		    $query->andWhere('master_id id NOT null');
		}
 		$q_res = $query->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

		if($q_res )
		{
			return $q_res;
		}
		else
		{
			return false;
		}
	}
	
	
	public function get_client_application_list($clientid = 0)
	{
	    //ISPC-2612 Ancuta 27.06.2020
	    $client_is_follower = ConnectionMasterTable::_check_client_connection_follower('NutritionFormularList',$clientid);
	    
		$query = Doctrine_Query::create()
		->select('id, field_value')
		->from('NutritionFormularList')
		->andwhere('clientid =  ?', $clientid)
		->andWhere('isdelete = 0')
		->andWhere("field_name = 'application'");
		if($client_is_follower){//ISPC-2612 Ancuta 27.06.2020
		    $query->andWhere('connection_id is NOT null');
		    $query->andWhere('master_id is NOT null');
		}
		$q_res = $query->fetchArray();
		
		if($q_res )
		{
			return $q_res;
		}
		else
		{
			return false;
		}
	}
	
	public function delete_by_id_and_clientid($id = 0, $clientid = 0)
	{
		if ( $update = Doctrine::getTable('NutritionFormularList')->findOneByIdAndClientid($id, $clientid)) {
			$update->isdelete = 1;
			$update->save();
		}
	}
	
	public function set_new_row($clientid = 0 , $field_value = '', $field_name = 'application')
	{
		$this->id = null;
		$this->clientid = $clientid;
		$this->field_name = $field_name;
		$this->field_value = $field_value;
		$this->isdelete = 0;
		$this->save();
		return $this->id;
		

	}
	
	public function set_old_row($clientid = 0 , $field_value = '', $id = 0)
	{
		if ( $update = Doctrine::getTable('NutritionFormularList')->findOneByIdAndClientid($id, $clientid)) {
			$update->field_value = $field_value;
			$update->save();
		}
		
	
	
	}
	
}
?>