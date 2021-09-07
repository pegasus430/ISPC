<?php

Doctrine_Manager::getInstance()->bindComponent('FtpPutQueue', 'SYSDAT');


class FtpPutQueue extends BaseFtpPutQueue {

	
	public static function get_file_name_by_id(  $id = 0 )
	{
		$salt = Zend_Registry::get('salt');
		
		$result = false;
		
		$q = Doctrine_Query::create()
		->select('id, AES_DECRYPT( file_name, :salt ) as file_name'	)
		->from('FtpPutQueue')
		->Where('id = :id')
// 		->limit(1)
		->fetchOne(array(
				"id"=>$id,
				"salt"=>$salt
		) ,
		Doctrine_Core::HYDRATE_ARRAY
		);

		if (! empty($q['file_name'])) {
			$result = $q['file_name'];
		}
		return $result;
	}
	
}