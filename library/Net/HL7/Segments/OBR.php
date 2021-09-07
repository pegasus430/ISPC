<?php

/**
 * OBR segment class
 *
 */
class Net_HL7_Segments_OBR extends Net_HL7_Segment {

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
        parent::__construct("OBR", $fields);
        
        if (!isset($fields)) {

		}
    }
	

	
    
    
    //getter for most interesting fields

    function getPlacerOrderNumber()
    {
	return $this->getField(2);
    }
    
    function getUniversalServiceId()
    {
        $field = $this->getField(4);
        $names = array('code', 'tet', 'catalogue');
        $out = parent::nameSubsegements($field, $names);
        return $out;
    }
    
    /*
     * @return string YYYYMMDDHHMMSS
     */
    function getObservationTime()
    {
        return parent::convertDate($this->getField(7));
    }
    
     /*
     * @return string komma separated items
     */
    function getDangerCode()
    {
        return $this->getField(12);
    }
    
    
    function getOrderingProvider()
    {
        $field = $this->getField(16);
        $names = array('vma', 'name1', 'name2');
        $out = parent::nameSubsegements($field, $names);
        return $out;
    } 
    function getPlacerField1()
    {
	return $this->getField(18);
    }
    function getPlacerField2()
    {
        $field = $this->getField(19);
        $names = array('short', 'text');
        $out = parent::nameSubsegements($field, $names);
        return $out;
    }
    
    function getAdditionalInfo()
    {
        $field = $this->getField(13);
        $names = array('zustand', 'schmerzen', 'atemnot', 'uebelkeit', 'erbrechen', 'verwirrtheit', 'angst', 'andere', 'patientbesorgt', 'angehoerigebesorgt','funktionsstatus');
        $out = parent::nameSubsegements($field, $names);
        return $out;
    }   
    
    function getQuantityTiming()
    {
        $field = $this->getField(27);
        $names = array('', '', 'time');
        $out = parent::nameSubsegements($field, $names);
        return parent::convertDate($out->time);
    }   
}

?>
