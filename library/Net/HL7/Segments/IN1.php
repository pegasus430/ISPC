<?php

/**
 * IN1 (Insurance) segment class
 *
 */
class Net_HL7_Segments_IN1 extends Net_HL7_Segment {

    /**
     * Create an instance of the IN1 segment. 
     *
     * If an array argument is provided, all fields will be filled
     * from that array. Note that for composed fields and
     * subcomponents, the array may hold subarrays and
     * subsubarrays. 
     */
    function __construct($fields = NULL)
    {
        parent::__construct("IN1", $fields);
        
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
	
	function setCompanyId($id)
	{
		return $this->setField(3, $id);
	}
	
	function setCompanyName($text)
	{
		return $this->setField(4, $text);
	}
	
	function setCompanyAddress($street, $city, $zip, $country="D")
	{
		return $this->setField(5, array($street, $city, $zip, $country));
	}	

    
    /**
     * @param $type "1"|"3"|"5"
     */
 	function setPlanType($type)
	{
		return $this->setField(15, $type);
	}	
	
	function setNameOfInsured($lastname, $firstname, $suffix="", $prefix="", $degree="")
	{
		return $this->setField(16, array($lastname, $firstname, $suffix, $prefix, $degree));
	}	
    
    /**
     * @param $timecode in format YYYYMMDD
     */
  	function setInsuredsDateOfBirth($timecode)
	{
		return $this->setField(18, $timecode);
	}	

  	function setInsuredsAddress($street, $city, $zip, $country="D", $phone="")
	{
		return $this->setField(19, array($street, $city, $zip, $country, $phone));
	}	
	
	function setPolicyNumber($number)
	{
		return $this->setField(36, $number);
	}	
	     
	     
	     
    //getter for most interesting fields
    
    function getSetId()
    {
		return $this->getField(1);
	}
	
    function getCompanyId()
    {
		return $this->getField(3);
	}

    function getCompanyName()
    {
		return $this->getField(4);
	}
	
	function getCompanyAddress()
	{	
		$field = $this->getField(5);
		$names = array('street', 'empty1','city', 'empty2','zip', 'country');//last update for LMU 2019-03-13
		$out = parent::nameSubsegements($field, $names);
		return $out;	
	}
	
	function getPlanType()
	{
		return $this->getField(15);		
	}
	
	function getNameOfInsured()
	{
		$field = $this->getField(16);
		$names = array('lastname', 'firstname', 'suffix', 'prefix', 'degree');
		$out = parent::nameSubsegements($field, $names);
		return $out;	
	}
	
	function getInsuredsDateOfBirth()
	{
		return $this->getField(18);		
	}
	
	function getInsuredsAddress()
	{
		$field = $this->getField(19);
		$names = array('street', 'city', 'zip', 'country', 'phone');
		$out = parent::nameSubsegements($field, $names);
		return $out;	
	}
	
	function getPolicyNumber()
	{
		return $this->getField(36);		
	}
}

?>
