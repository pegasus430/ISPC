<?php

	Doctrine_Manager::getInstance()->bindComponent('BraAnlage5Weeks', 'MDAT');

	class BraAnlage5Weeks extends BaseBraAnlage5Weeks {

	    public function get_bra_anlage5weeks($ipid,$anlage5_id=false)
	    {
	        $braq = Doctrine_Query::create()
	        ->select("*")
	        ->from('BraAnlage5Weeks')
	        ->where("ipid='" . $ipid . "'");
	        
	        if($anlage5_id){
    	       $braq->andWhere("anlage5_id='" . $anlage5_id . "'");
	        }
	        
	        $bra_array = $braq->fetchArray();
	         
            return $bra_array;
	        
	    }
	}

?>