<?php
/**
 * 
 * @author claudiu
 *
 */

class MemberAutoNumber extends BaseMemberAutoNumber 
{
	private static $mandatory_columns = array(
			'clientid',
			'start_number',
	);
	
	public function delete_row( $id = null )
	{
		if (( ! is_null($id)) && ($obj = $this->getTable()->find($id)))
		{
			$obj->delete();
			return true;
				
		} else {
			return false;
		}
	}
	
	public function delete_by_clientid ( $clientid = null )
	{
		if (( ! is_null($clientid)) && ($obj = $this->getTable()->findByClientidAndIsdelete($clientid , 0))) {
			$obj->delete();
			
		}
	}
	
	/**
	 * be aware, the fn name may be misleading - this is how Doctrine works!
	 * this fn will insert new if there is no db-record object in our class...
	 * if you called second time, or you fetchOne, it will update!
	 * fn was intended for single record, not collection
	 * @param array $params
	 * @return boolean|number
	 * return $this->id | false if you don't have the mandatory_columns in the params
	 */
	public function set_new_record($params = array())
	{
	
		if (empty($params) || !is_array($params)) {
			return false;// something went wrong
		}
	
		foreach (self::$mandatory_columns as $column) {
			if ( ! isset($params[$column]) || empty($params[$column]) ) {
				return false;
			}
		}
	
		foreach ($params as $k => $v)
			if (isset($this->{$k})) {
	
				//next columns should be encrypted
				switch ($k) {
					case "column_name_example1":
					case "column_name_example2":
						$v = Pms_CommonData::aesEncrypt($v);
						break;
				}
				$this->{$k} = $v;
	
			}
	
		$this->save();
		return $this->id;
	
	}
		
	public function get_start_number( $clientid =  0)
	{

		$result = false;
		
		$dq = $this->getTable()->createQuery()
		->select("id, start_number, CAST(start_number AS DECIMAL) AS dec_start_number")
		->where("clientid = ?" , $clientid )
		->andWhere('isdelete = 0')
		->orderBy('create_date DESC')
		->limit(1)
		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
		
		if( $dq ) {
			$result = $dq;
		}
	
		return $result;
	}
	

}

?>