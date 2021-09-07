<?php

	Doctrine_Manager::getInstance()->bindComponent('XbdtFiles', 'MDAT');

	class XbdtFiles extends BaseXbdtFiles {
	    
	    public function client_xbdt_files($client,$fileid = false)
	    {
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('XbdtFiles')
	        ->where('clientid =  '.$client)
	        ->andWhere('isdelete = 0');
	        if($fileid){
	           $query ->andWhere('id = "'.$fileid.'"');
	        }
	        $q_res = $query->fetchArray();
	        
	        if($q_res )
	        {
                if($fileid){
                    
                    foreach( $q_res as $k=>$fdata){
                        $q_res_file[$fdata['id']] = $fdata;
                    }
                    
                    return $q_res_file;
                    
                } else {
                    
    	            return $q_res;
    	            
                }
	            
	        }
	        else
	        {
	           return false;    
	        }
	    }
	    
	}

?>