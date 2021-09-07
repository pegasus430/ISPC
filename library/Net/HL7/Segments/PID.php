<?php

/**
 * PID (Patient identification) segment class
 *
 */
class Net_HL7_Segments_PID extends Net_HL7_Segment {

    /**
     * Create an instance of the PID segment. 
     *
     * If an array argument is provided, all fields will be filled
     * from that array. Note that for composed fields and
     * subcomponents, the array may hold subarrays and
     * subsubarrays. 
     */
    function __construct($fields = NULL)
    {
        parent::__construct("PID", $fields);
    }

	//Setter for most required fields

	function setPatientID($id)
	{
		return $this->setField(3,array($id, 0, 0));
	}

	function setPatientName($lastname, $firstname, $suffix="", $prefix="", $degree="")
	{
		return $this->setField(5 , array($lastname, $firstname, $suffix, $prefix, $degree));
	}
		
	/**
	 * @param birthdate in format YYYYMMDD
	 */
	function setDateOfBirth($birthdate)
	{
		return $this->setField(7 , $birthdate);
	}
	
	function setMothersMaidenName($name)
	{
		return $this->setField(6, $name);
	}	
		
	/**
	 * @param sex 'M'|'F'
	 */
	function setSex($sex)
	{
		return $this->setField(8 , $sex);
	}	
	
	function setAddress($street, $city, $zip, $country="D")
	{
		return $this->setField(11 , array($street, "", $city, "", $zip, $country));
	}	
	
	function setPhoneNumber($number)
	{
		return $this->setField(13 , $number);
	}	
	
	/**
	 * @param rel Religionsschlüssel (00-99)
	 */
	function setReligion($rel)
	{
		return $this->setField(17, $rel);
	}	
	
	function setCitizenship($name)
	{
		return $this->setField(26, $name);
	}
	

    
    
    //getter for most interesting fields
    function getPatientID()
    {
		$field = $this->getField(3);             
		$names = array('id', 'check', 'method');
                if (sizeof($field)==3){
                    $out = parent::nameSubsegements($field, $names);
                    } else{
                    $out = parent::nameSubsegements(array($field,), $names);   
                    }
		return $out;
	}
		
    function getPatientName()
    {	
		$field = $this->getField(5);
		$names = array('lastname', 'firstname', 'suffix', 'prefix', 'degree');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}
		
	function getDateOfBirth()
	{
		return $this->getField(7);
	}
		
	function getMothersMaidenName()
	{
		return $this->getField(6);
	}	
			
	function getSex(){
		return $this->getField(8);
		}
		
	function getAddress()
	{
		$field = $this->getField(11);
		$names = array('street', '', 'city', '', 'zip', 'country');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}	
	
	function getPhoneNumber()
	{
		return $this->getField(13);
	}	
		
	function getReligion()
	{
		return $this->getField(17);
	}	
	
	function getCitizenship()
	{
		return $this->getField(26);
	}
}

?>
