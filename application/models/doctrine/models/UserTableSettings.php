<?php
	Doctrine_Manager::getInstance()->bindComponent('UserTableSettings', 'SYSDAT');

	class UserTableSettings extends BaseUserTableSettings { 
	    
	    public function user_saved_settings($user_id = false, $page, $column = false, $tab = false,$order = false){
	        $logininfo = new Zend_Session_Namespace('Login_Info');
	        	
	        if(!$user_id){
	            $user_id = $logininfo->userid;
	            $user_array = array($user_id);
	        } else{
	            $user_array = array($user_id);
	        }
	        	
	        $uss = Doctrine_Query::create()
	        ->select('*')
	        ->from('UserTableSettings')
	        ->whereIn('user',$user_array )
	        ->andWhere('client = '.$logininfo->clientid)
	        ->andWhere('page = "'.$page.'"');
	        
	        if($column || strlen($column) > 0 ){
    	       $uss ->andWhere('column_id ="'.$column.'"' );
	        }
	        if($tab!==false){
    	       $uss ->andWhere('tab = "'.$tab.'"' );
	        }
	        
	        if($order){
    	       $uss ->orderBy('column_order ASC');
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