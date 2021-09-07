<?php

/**
 * ORC (Order Request) segment class
 *
 */
class Net_HL7_Segments_ORC extends Net_HL7_Segment {

    /**
     * Create an instance of the ORC segment. 
     *
     * If an array argument is provided, all fields will be filled
     * from that array. Note that for composed fields and
     * subcomponents, the array may hold subarrays and
     * subsubarrays. 
     */
    function __construct($fields = NULL)
    {
        parent::__construct("ORC", $fields);
        
        if (!isset($fields)) {

		}
    }
	

	
    
    
    //getter for most interesting fields
    
    /*
     * Anforderungs-Funktionscode
     *'NW': Anlegen
     *'XO': Ändern
     *'CA': Stornieren
     */
    function getOrderControl()
    {
	return $this->getField(1);
    }
    function getPlacerOrderNumber()
    {
	return $this->getField(2);
    }
    function getPlacerGroupNumber()
    {
	return $this->getField(4);
    }
    function getOrderStatus()
    {
	return $this->getField(5);
    }   
    function getQuantityTiming()
    {
        $field = $this->getField(7);
        $names = array('unused', 'unused2', 'duration', 'begin', 'begin2', 'priority');
        $out = parent::nameSubsegements($field, $names);
        $out->begin=parent::convertDate($out->begin);
        return $out;
    }    
    function getTimeOfTransaction()
    {
        return parent::convertDate($this->getField(9));
    } 
    function getOrderingProvider()
    {
        $field = $this->getField(12);
        $names = array('vma', 'name1', 'name2');
        $out = parent::nameSubsegements($field, $names);
        return $out;
    }  
    function getReason()
    {
        $field = $this->getField(16);
        $out = str_replace("^","; ",$field);
        return $out;
    }  
    
    function getPhone()
    {
        $field = $this->getField(14);
        return $field;
    }  
}

?>
