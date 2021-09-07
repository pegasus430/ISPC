<?php

	Doctrine_Manager::getInstance()->bindComponent('XbdtActions', 'MDAT');

	class XbdtActions extends BaseXbdtActions {
	    
	    public function client_xbdt_actions($client,$exclude_deleted = true ,$only_cf_available = false,$contact_form_block = false)
	    {
	        $query = Doctrine_Query::create()
	        ->select('*')
	        ->from('XbdtActions')
	        ->where('clientid =  '.$client);
	        if($exclude_deleted){
    	        $query->andWhere('isdelete = 0');
	        }
	        
	        if($only_cf_available){
    	        $query->andWhere('available = "1"');
	        }
	        
	        if($contact_form_block){
    	        $query->andWhere('contact_form_block= ?',$contact_form_block);
	        }
	        
	        $query->andWhere('extra = 0');
	        $q_res = $query->fetchArray();
	        
	        if($q_res )
	        {
	           return $q_res;
	        }
	        else
	        {
	           return false;    
	        }
	    }
	    
	    
	    /**
	     * TODO-1414
	     *  and ISPC-1780
	     */
	    
	    public function xbdt_contact_form_blocks() {
	    	$Tr = new Zend_View_Helper_Translate();
	    	
	    	
	    	$allowed_blocks = 
	    		array(
	    			"xbdt_groups" => array(
						"ebmii" => $Tr->translate('ebmii_xbdt_action_group'),
						"goaii" => $Tr->translate('goaii_xbdt_action_group'),
	    			),
	    			"contact_form_blocks" => array(
						"ebmii" => $Tr->translate('block_ebmii'),
						"goaii" => $Tr->translate('block_goaii'),
	    			),
			);
	    	

	    	return $allowed_blocks;
		}
	    
	}

?>