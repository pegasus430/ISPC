<?php

/**
 * NK1 (Next of kin / associated parties) segment class
 *
 */
class Net_HL7_Segments_NK1 extends Net_HL7_Segment {

    /**
     * Create an instance of the NK1 segment. 
     *
     * If an array argument is provided, all fields will be filled
     * from that array. Note that for composed fields and
     * subcomponents, the array may hold subarrays and
     * subsubarrays. 
     */
    function __construct($fields = NULL)
    {
        parent::__construct("NK1", $fields);
        
        if (!isset($fields)) {
			//set set-ID
			$this->setSetId("1");
		}
    }

	//Setter for most required fields
	
	/**
	 * @param number 1 or 2
	 */
	function setSetId($number)
	{
		return $this->setField(1, $number);
	}
	
	function setNKName($lastname, $firstname)
	{
		return $this->setField(2, array ($lastname, $firstname));
	}

	function setRelationship($code, $text="")
	{
		return $this->setField(3 , array($code, $text, "ISH"));
	}	
	
	function setAddress($street, $city, $zip, $country="D")
	{
		return $this->setField(4 , array($street, '', $city, '', $zip, $country));
	}	
	
	function setPhoneNumber($number)
	{
		return $this->setField(5 , array($number,"PH") );
	}	
	
	
    
    
    //getter for most interesting fields
    function getSetId()
    {
		return $this->getField(1);
	}
	
    function getNKName()
    {
		$field = $this->getField(2);
		$names = array('lastname', 'firstname');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}
	
    function getRelationship()
    {
		$field = $this->getField(3);
		$names = array('code', 'text', 'scheme');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}	
	
    function getAddress()
    {
		$field = $this->getField(4);
		$names = array('street', '', 'city', '', 'zip', 'country');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}	
	
	function getPhoneNumber()
    {
		$field = $this->getField(5);
		$names = array('number', 'ph');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}	
}

?>
