<?php
/**
 * 
 * @author claudiu
 *
 */
class MedicationClientStockSeal extends BaseMedicationClientStockSeal 
{
	/**
	 * default seal_date
	 * this "constant" is used for those that don't have any bmt seal_date defined
	 * @return number
	 */
	public static function get_default_seal_timestamp()
	{
		return strtotime ("-10 Years");
	}
	
	/**
	 * 
	 * @param number $clientid
	 * @param number $isdelete
	 * @return Ambigous <multitype:, Doctrine_Collection>
	 */
	public function get_client_history ( $clientid = 0 , $isdelete = 0)
	{
		$result = Doctrine_Query::create()
		->select('*')
		->from('MedicationClientStockSeal')
		->Where('clientid = ? ', $clientid)
		->andWhere('isdelete = ?', $isdelete)
		->orderBy('id DESC')
		->fetchArray();
		
		return $result;
	}
	
	
	/**
	 * 
	 * @param number $clientid
	 * @return mixed
	 */
	public function get_client_last_seal ( $clientid = 0 )
	{
		$result = Doctrine_Query::create()
		->select('*')
		->from('MedicationClientStockSeal')
		->Where('clientid = ? ', $clientid)
		->andWhere('isdelete = 0')
		->orderBy('seal_date DESC, id DESC')
		->limit(1)
		->fetchOne(null, Doctrine_Core::HYDRATE_ARRAY);
	
		return $result;
	}
	

}
?>