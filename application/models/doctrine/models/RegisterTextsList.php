<?php

class RegisterTextsList extends BaseRegisterTextsList {

	private static $mandatory_columns = array(
			'clientid',
			'field_name',
			'field_value',
	);
	
	public function set_new_record($params = array())
	{
	
		if (empty($params) || !is_array($params)) {
			return false;// something went wrong
		}
	
		foreach (self::$mandatory_columns as $column) {
	
			if ( empty($params[$column]) &&  empty($this->{$column}) )
			{
				return false;
			}
		}
	
		foreach ($params as $k => $v)
			if (isset($this->{$k})) {
				$this->{$k} = $v;
			}
	
		$this->save();

		return $this->id;
	
	}

	public function delete_row( $id = null ,$clientid = 0 )
	{
		if (( ! is_null($id)) && ($obj = $this->getTable()->findOneByIdAndClientid($id,$clientid)))
		{
			$obj->delete();
			return true;
	
		} else {
			return false;
		}
	}
	
	public function set_old_record($params = array()){
		
		$columns = $this->getTable()->getColumns();
		$columns_names = array_keys($columns);
		
		if(! is_null($params['record_id']) && ($obj = $this->getTable()->findOneByIdAndClientid($params['record_id'], $params['clientid']))){

			foreach($params as $field=>$value){
				if(in_array($field,$columns_names) && isset($value)){
					$obj->{$field} = nl2br($value);
				}
			}
			$obj->save();
		}
	}
	
	public function get_by_id_and_clientid($id = 0, $clientid = 0)
	{
		$query = $this->getTable()->createQuery()
		->select('*')
		->where('id = ?', $id)
		->andwhere('clientid = ?', $clientid)
		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
	
		if($query )
		{
			return $query;
		}
		else
		{
			return false;
		}
	}
	
	
	public function get_client_list($clientid = 0, $field_name = '')
	{
		$query = $this->getTable()->createQuery()
		->select('id, field_value, field_name')
		->andwhere('clientid =  ?', $clientid);
		if(isset($field_name) && !empty($field_name)){
			$query->andWhere("field_name = ?",$field_name);
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
}
?>