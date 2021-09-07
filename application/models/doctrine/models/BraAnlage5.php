<?php

	Doctrine_Manager::getInstance()->bindComponent('BraAnlage5', 'MDAT');

	class BraAnlage5 extends BaseBraAnlage5 {

	    public function get_bra_anlage5($ipid, $date = false)
	    {
	        $braq = Doctrine_Query::create()
	        ->select("*")
	        ->from('BraAnlage5')
	        ->where("ipid='" . $ipid . "'");
	    
	        if($date)
	        {
	            $braq->andWhere('DATE(  `start_date` ) = "' . date('Y-m-d', strtotime($date)) . '"');
	        }
	        $braq->OrderBy('start_date ASC');
	        $bra_array = $braq->fetchArray();
	    
	        return $bra_array ;
	    }

	    public function get_bra_anlage5_by_id($ipid,$id)
	    {
	        $braq = Doctrine_Query::create()
	        ->select("*")
	        ->from('BraAnlage5')
	        ->where("ipid='" . $ipid . "'")
	        ->andWhere("id='" . $id . "'");
	        $braq->OrderBy('start_date ASC');
	        $bra_array = $braq->fetchArray();
	        
    	    if($bra_array){
    	        return $bra_array[0] ;
    	    } else {
    	        return false;
    	    }
	    }
	     
	}

?>