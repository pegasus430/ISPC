<?php

	Doctrine_Manager::getInstance()->bindComponent('BraAnlage5Products', 'MDAT');

	class BraAnlage5Products extends BaseBraAnlage5Products {
	    
	    public function get_bra_anlage5products($ipid,$anlage5_id)
	    {
	        $braq = Doctrine_Query::create()
	        ->select("*")
	        ->from('BraAnlage5Products')
	        ->where("ipid='" . $ipid . "'")
	        ->andWhere("anlage5_id='" . $anlage5_id . "'")
	        ->andWhere("isdelete= 0");
	        $bra_array = $braq->fetchArray();
	    
            return $bra_array;
	    }

	}

?>