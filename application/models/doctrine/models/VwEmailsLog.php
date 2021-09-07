<?php

Doctrine_Manager::getInstance()->bindComponent('VwEmailsLog', 'SYSDAT');

class VwEmailsLog extends BaseVwEmailsLog {

	/**
	 * 
	 * Jul 24, 2017 @claudiu 
	 * 
	 * @param number $clientid
	 * @param unknown $filter_by
	 * @return Ambigous <multitype:, Doctrine_Collection>
	 */
	public function get_grouped_log( $clientid = 0 , $filter_by = array())
	{
		$salt = Zend_Registry::get('salt');
	
		$result_q = $this->getTable()->createQuery()
		->select("*,
				AES_DECRYPT( title, '".$salt."' )  as title_plain,
				AES_DECRYPT( content, '".$salt."' )  as content_plain,
				IF(batch_id !=0 , batch_id, CONCAT_WS(' ', sender, recipients, DATE(date) ) ) as my_group_by"
		)
		->where(" clientid = ? ", $clientid)
		->groupBy('my_group_by')
		->orderBy('date DESC');
	
	
		if (! empty($filter_by) && is_array($filter_by) ){
	
			foreach ($filter_by as $k => $v){
	
				if ($k == 'limit') {
					$result_q->limit((int)$v);
						
				} elseif ($k == 'offset') {
					$result_q->offset((int)$v);
						
				} else {
						
					if (! is_array($v)){
						$v = array($v);
					}
					$result_q->andWhereIn($k, $v);
				}
			}
	
		}
	
	
		$R = $result_q->fetchArray();
			
		return $R;
			
	}
	
	/**
	 * 
	 * Jul 24, 2017 @claudiu 
	 * 
	 * @param number $clientid
	 * @return number
	 */
	public function get_grouped_log_count( $clientid = 0 )
	{
	
		return $this->get_grouped_log_filtered_count($clientid);
		
// 		$result_q = $this->getTable()->createQuery()		
// 		->select("IF(batch_id !=0 , batch_id, CONCAT_WS(' ', sender, recipients, DATE(date) ) ) as my_group_by"	)
// 		->where("clientid = :clientid")
// 		->groupBy('my_group_by')
// 		->fetchArray(array(
// 				"clientid"=>$clientid
// 		));
			
// 		return count($result_q);
			
	}
	
	/**
	 * 
	 * Jul 24, 2017 @claudiu 
	 * 
	 * @param number $clientid
	 * @param unknown $filter_by
	 * @return number
	 */
	public function get_grouped_log_filtered_count( $clientid = 0 , $filter_by = array())
	{

		$result_q = $this->getTable()->createQuery()
		->select("id,recipient, IF(batch_id !=0 , batch_id, CONCAT_WS(' ', sender, recipients, DATE(date) ) ) as my_group_by"	)
		->where("clientid = ?" , $clientid);
	
		if (! empty($filter_by) && is_array($filter_by) ){
				
			foreach ($filter_by as $k => $v){
	
				if (in_array($k, array('limit', 'offset'))){
					continue;
				}
	
				if (! is_array($v)){
					$v = array($v);
				}
				$result_q->andWhereIn($k, $v);
			}
				
		}
		$result_q->groupBy('my_group_by');
		$r = $result_q->fetchArray();
	
	
		return count($r);
			
	}
	
}

?>