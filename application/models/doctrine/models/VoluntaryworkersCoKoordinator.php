<?php
/**
 * 
 * @author claudiu
 *
 */
class VoluntaryworkersCoKoordinator extends BaseVoluntaryworkersCoKoordinator
{

	private static $mandatory_columns = array(
			
			'clientid',
			'vw_id',
			'vw_id_koordinator',
			
	);
	
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
	
	
	public function get_co_koordinator_by_vwid( $vw_id = 0, $clientid = 0) 
	{
		$result = false;
		$q = Doctrine_Query::create()
		->select('id, vw_id_koordinator')
		->from('VoluntaryworkersCoKoordinator')
		->where('clientid = ?', $clientid)
		->andWhere('vw_id = ?' , $vw_id)
		->andWhere('isdelete = 0')
		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);

		if ( ! empty($q)) {
			$result =  $q['vw_id_koordinator'];
		}
		
		return $result;
	}

	public function get_multiple_cokoordinators_by_vwid( $vw_ids = array() ) 
	{
		
		$result = false;
		
		if ( empty($vw_ids) || ! is_array($vw_ids)) {
			return $result;
		}
		
		$q = Doctrine_Query::create()
		->select('id, vw_id, vw_id_koordinator')
		->from('VoluntaryworkersCoKoordinator')
// 		->where('clientid = ?', $clientid)
		->WhereIn('vw_id' , $vw_ids)
		->andWhere('isdelete = 0')
		->fetchArray();
		
		if ( ! empty($q)) {
			
			$result = $q;
		}
				
		return $result;
	}
}
?>