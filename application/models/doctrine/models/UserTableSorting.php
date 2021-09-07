<?php
	Doctrine_Manager::getInstance()->bindComponent('UserTableSorting', 'SYSDAT');

	class UserTableSorting extends BaseUserTableSorting { 
	    
	    public function user_saved_sorting($user_id = false, $page = false, $ipid = false,$name = false){
	        
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        	
	        if(!$user_id){
	            $user_id = $logininfo->userid;
	            $user_array = array($user_id);
	        } else{
	            $user_array = array($user_id);
	        }
	        	
	        $uss = Doctrine_Query::create()
	        ->select('*')
	        ->from('UserTableSorting')
	        ->whereIn('user',$user_array )
	        ->andWhere('client = '.$logininfo->clientid);
	        if($page){
	          $uss->andWhere('page = "'.$page.'"');
	        }
	        if($ipid){
    	       $uss ->andWhere('ipid = "'.$ipid.'"' );
	        }
	        if($name){
    	       $uss ->andWhere('name = "'.$name.'"' );
	        }
	        $uss_arr = $uss->fetchArray();
	        
	        if( !empty($uss_arr) ) {
	            return $uss_arr;
	        } else{
	            return false;
	        }
	        	
	    }
	    
	}

?>