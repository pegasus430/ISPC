<?php
Doctrine_Manager::getInstance ()->bindComponent ( 'SystemsSyncPatients', 'SYSDAT' );
class SystemsSyncPatients extends BaseSystemsSyncPatients {
	public static function getPatients($clientid, $connection, $include_names = 1, $only_enabled = 1) {
		$sql = Doctrine_Query::create ()->select ( '*' )->from ( 'SystemsSyncPatients' )->where ( 'connection=?', $connection )->andWhere ( 'clientid=?', $clientid );
		if ($only_enabled) {
			$sql->andWhere ( 'sync_enable=1' );
		}
		$patients = $sql->fetchArray ();
		
		if ($include_names) {
			$ipids = array ();
			if ($patients) {
				foreach ( $patients as $pat ) {
					$ipids [] = $pat ['ipid_here'];
				}
				
				$hidemagic = Zend_Registry::get ( 'hidemagic' );
				
				/*
				 * $sql = "*,AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') as first_name";
				 * $sql .=",AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') as middle_name";
				 * $sql .= ",AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') as last_name";
				 * $sql .= ",AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title";
				 * $sql .= ",AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') as salutation";
				 * $sql .= ",AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') as street1";
				 * $sql .= ",AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') as street2";
				 * $sql .= ",AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') as zip";
				 * $sql .= ",AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') as city";
				 * $sql .= ",AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') as phone";
				 * $sql .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber";
				 * $sql .= ",kontactnumbertype as kontactnumbertype";
				 * $sql .= ",AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') as kontactnumber_dec";
				 * $sql .= ",AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') as mobile";
				 * $sql .= ",AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') as sex";
				 */
				
				$sql = "ipid, CONCAT(AES_DECRYPT(first_name,'" . Zend_Registry::get ( 'salt' ) . "'), ' ', AES_DECRYPT(last_name,'" . Zend_Registry::get ( 'salt' ) . "')) as name";
				
				$isadmin = 0;
				// if super admin check if patient is visible or not
				if ($logininfo->usertype == 'SA' && $clone === false) {
					/*
					 * $sql = "*,";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as first_name, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(middle_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as middle_name, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(last_name,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as last_name, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as title, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(salutation,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as salutation, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street1,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street1, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(street2,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as street2, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(zip,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as zip, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(city,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as city, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(kontactnumber,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as kontactnumber, ";
					 * $sql .= "AES_DECRYPT(kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber_dec,";
					 * $sql .= "AES_DECRYPT(kontactnumber, '" . Zend_Registry::get('salt') . "') as kontactnumber,";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(phone,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as phone, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(mobile,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as mobile, ";
					 * $sql .= "IF(isadminvisible = 1,CONVERT(AES_DECRYPT(sex,'" . Zend_Registry::get('salt') . "') using latin1),'" . $hidemagic . "') as sex, ";
					 */
					$sql = "ipid, IF(isadminvisible = 1,CONVERT(AES_DECRYPT(first_name,'" . Zend_Registry::get ( 'salt' ) . "') using latin1),'" . $hidemagic . "') as name, ";
				}
				
				$pt = Doctrine_Query::create ()->select ( $sql )->from ( 'PatientMaster' )->whereIn ( 'ipid', $ipids );
				
				$patarray = $pt->fetchArray ();
				
				foreach ( $patarray as $db_pat ) {
					$names [$db_pat ['ipid']] = $db_pat ['name'];
				}
				
				foreach ( $patients as $k => $pat ) {
					$patients [$k] ['name'] = $names [$pat ['ipid_here']];
				}
			}
		}
		
		return array (
				'patients' => $patients,
				'ipids' => $ipids 
		);
	}
	public static function addConnection($connection, $ipid, $clientid) {
		$pat = Doctrine::getTable ( 'SystemsSyncPatients' )->findOneByIpid_hereAndConnectionAndClientid ( $ipid, $connection, $clientid );
		
		if (! $pat) {
			$new = new SystemsSyncPatients ();
			$new->clientid = $clientid;
			$new->connection = $connection;
			$new->ipid_here = $ipid;
			$new->sync_enable = 1;
			$new->save ();
			return $new;
		}
		
		return $pat;
	}
}
?>