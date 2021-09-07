<?php
// require_once "Zend/Acl.php";
class Pms_Acl_Assertion extends Zend_Acl {
	
	public function __construct() {
		// Add Resources
	}
	
	public function isLogin() {
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		
		if (isset ( $logininfo->userid )) {
			return true;
		}
	}
	
	/**
	 * @deprecated - because you allways return true
	 */
	public function checkPrevilege($module, $userid, $previlege) {
		return true; //this enables new rights permissions
		$user = Doctrine::getTable ( 'User' )->find ( $userid );
		$userarray = $user->toArray ();
		
		if ($userarray ['usertype'] == 'SA') {
			return true;
		}
		
		$logininfo = new Zend_Session_Namespace ( 'Login_Info' );
		
		if ($logininfo->loginclientid > 0) {
			$mod = Doctrine::getTable ( 'Modules' )->findOneBy ( "module", $module );
			if (! $mod)
				return false;
			$modulearray = $mod->toArray ();
//			if ($logininfo->hospiz != 1) {
				
				$query = Doctrine_Query::create ()->select ( '*' )->from ( 'ClientModules' )->where ( "clientid='" . $logininfo->loginclientid . "' and moduleid='" . $modulearray ['id'] . "' and canaccess='1'" );
				//echo $query->getSqlQuery();
				$prev = $query->execute ();
				
				$previlegearray = $prev->toArray ();
				
				if (count ( $previlegearray ) < 1) {
					return false;
				}
//			} else {
//				$hospizperms = Pms_CommonData::getHospizMenus ();
//				if (! in_array ( $modulearray ['id'], $hospizperms ['modules'] )) {
//					return false;
//				}
//			}
		
		}
		
		$voidpermissions = Pms_CommonData::getPatientVoidPermissions();
		
		if ( in_array ( $modulearray ['id'], $voidpermissions ['patient'] )) {
			return true;
		}
		
		return true; //this enables new rights permissions
		
		$usrquery = Doctrine_Query::create ()->select ( '*' )->from ( 'UserPrevileges' )->where ( 'userid="' . $userid . '" and moduleid="' . $modulearray ['id'] . '" and ' . $previlege . '="1"' );
		//		$query->getSqlQuery();
		

		$usrprevilege = $usrquery->execute ();
		
		$userprevilegearray = $usrprevilege->toArray ();
		
		if (count ( $userprevilegearray ) < 1) {
			$module = Doctrine::getTable ( 'Modules' )->findOneBy ( "module", $module );
			$modulearray = $module->toArray ();
			
			$grpquery = Doctrine_Query::create ()->select ( '*' )->from ( 'GroupPrevileges' )->where ( "clientid='" . $logininfo->loginclientid . "' and moduleid='" . $modulearray ['id'] . "' and " . $previlege . "='1'" );
			//echo $query->getSqlQuery();
			$grpprev = $grpquery->execute ();
			
			$grpprevilegearray = $grpprev->toArray ();
			
			if (count ( $grpprevilegearray ) > 0) {
				return true;
			}
			return false;
		} else {
			
			$module = Doctrine::getTable ( 'Modules' )->findOneBy ( "module", $module );
			$modulearray = $module->toArray ();
			
			/*$usrquery = Doctrine_Query::create()
								->select('*')
								->from('UserPrevileges')
								->where('userid="'.$userid.'" and moduleid="'.$modulearray['id'].'" and '.$previlege.'="1"');
										$query->getSqlQuery();
										
						$usrprevilege = $usrquery->execute();
						
						$userprevilegearray = $usrprevilege->toArray();*/
			
			return true;
		
		//return false;
		

		}
	
		//return false;
	

	}

}

?>