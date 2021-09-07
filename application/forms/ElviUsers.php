<?php
/**
 * 
 * @author claudiu
 * 
 * ISPC-2060
 * authenticationFlow.pdf :: maxim 5 failed logins/10minut 
 * [(failed_logins_counter +1) mod 5] * 10min = banned time ? 
 *
 */
class Application_Form_ElviUsers extends Pms_Form {
    
    public function validate($data = array())
    {
        //@todo: connect to elVi and verify if user+password combination allready exists ??  
        //@update 14.11.2017: NO validation is needed?
        
        if (empty($data['action'])) {
            return false;
        }
        
        if ($data['action'] == 'connect' && empty($data['username'])) {
            return false;
        }
        
        return true;
    }
    
	
    /**
     * for now, we only create for USER, but this may change to create elvi account for Pflegedients, Hausartz .. etc .. profileType 
     * 
     * @param unknown $data
     * @param User $ispc_user
     * @return Ambigous <NULL, boolean>
     */
	public function save($data = array(), User $ispc_user ) 
	{
	    
	    $result = [];

	    $elviManager = new ElviService();
	    
	    switch ($data['action']) {
	        
	        case "create":
	             
	            $result['_group_create'] = $elviManager->_group_create($ispc_user);
	            
	            $result['_user_create'] = $elviManager->_user_create($ispc_user);
	            
	            $result['_user_info'] = $elviManager->_user_info();
	             
	            break;
	             
	        case "connect":
	            
	            $result['_user_create'] = $elviManager->_user_create($ispc_user);
	            
	            $result['_user_connect'] = $elviManager->_user_connect($data['username']);
	            
	            $result['_user_info'] = $elviManager->_user_info();
	            
	            break;
	            
            case "update": // this is the same as connect.. added extra IF so we not send unneeded messages to elVi
                
                
                //update the group
//                 $elviGroup = ElviGroupsTable::getInstance()->findOneByIspcClientidAndIspcGroupid($ispc_user->clientid, $ispc_user->groupid);
                
//                 $elviManager->_user_connect($ispc_user, $elviUser);
                
//                 dd("xx", $elviGroup->toArray());
                
                
                $result['_user_info'] = $elviManager->_user_info($ispc_user);
                
                //update the connected username
                if ($elviUser = ElviUsersTable::getInstance()->findOneBy('user_id', $ispc_user->id)) 
                {
                    if ($elviUser->username != $data['username']) {
                        
                    }
                    
                    
                    
                    if ($elviUser->username != $data['username']) {
                        $elviUser->username = $data['username'];
                        $elviUser->save();
                        
                        $result['_user_connect'] = $elviManager->_user_connect($ispc_user, $elviUser);
                        
                        $result['_user_info'] = $elviManager->_user_info();
                        
                        $result['_user_info'] = $elviManager->_group_addMember();
                    }
                }
                
                
                
                
                
                
	            break;
	            
	      
	    }
	    
	    return $result;
	    
	}
	
}