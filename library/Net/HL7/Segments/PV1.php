<?php

/**
 * PV1 (Patient visit) segment class
 *
 */
class Net_HL7_Segments_PV1 extends Net_HL7_Segment 
{

	/**
	 * Index of this segment. Incremented for every new segment of this class created
	 * @var int
	 */
	protected static $setID = 1;
	
    /**
     * Create an instance of the PV1 segment. 
     *
     * If an array argument is provided, all fields will be filled
     * from that array. Note that for composed fields and
     * subcomponents, the array may hold subarrays and
     * subsubarrays. 
     */
    function __construct($fields = NULL)
    {
        parent::__construct("PV1", $fields);
        
        if ( ! isset($fields)) {
        	
			$this->setAdmissionType("R");
			
			$this->setSetID($this::$setID++);
		}		
    }
    
    public function __destruct()
    {
        $this->setSetID($this::$setID--);
    }
    
	//Setter for most required fields
	
    
    /**
     * Definition: This field contains the number that identifies this transaction. 
     * For the first occurrence of the segment, the sequence number shall be one, for the second occurrence, the sequence number shall be two, etc.
     *
     * @param int $value
     * @param int $position
     * @return boolean
     */
    public function setSetID($value = 1, $position = 1)
    {
        return $this->setField($position, $value);
    }
    
    
	/**
	 * I  = stationär
     * O = ambulant
	 * T = teilstationär
	 * @param code "I"|"O"|"T"
	 */
	function setPatientClass($code)
	{
		return $this->setField(2, $code);
	}
	
	function setAssignedPatientLocation($org1, $room, $bed, $org2)
	{
		return $this->setField(3, array($org1, $room, $bed, $org2));
	}	

	/**
	 * L = Entbindung, R = Normalaufnahme ==> always R
	 */
	function setAdmissionType($code="R")
	{
		return $this->setField(4, $code);
	}		
	
	function setPriorPatientLocation($pointOfCare, $room, $bed)
	{
		return $this->setField(6, array($pointOfCare, $room, $bed));
	}
	
	function setAttendingDoctor($id, $lastname, $firstname, $degree="" ,$prefix="", $suffix="" )
	{
		return $this->setField(7, array($id, $lastname, $firstname, $suffix, $prefix, $degree));
	}	
    
	function setReferringDoctor($id, $lastname, $firstname, $zip, $city, $street, $phone, $fax, $formOfAddress, $country="D", $degree="" ,$prefix="", $suffix="" )
	{
		return $this->setField(8, array($id, $lastname, $firstname, $suffix, $prefix, "", $degree, $country, array($zip, $city), $street, $phone, $fax, "", $formOfAddress));
	}	
	
 	function setConsultingDoctor($id, $lastname, $firstname, $zip, $city, $street, $phone, $fax, $formOfAddress, $country="D", $degree="" ,$prefix="", $suffix="" )
	{
		return $this->setField(9, array($id, $lastname, $firstname, $suffix, $prefix, "", $degree, $country, array($zip, $city), $street, $phone, $fax, "", $formOfAddress));
	}   
	
	/**
	 * Definition: This field contains the treatment or type of surgery that the patient is scheduled to receive. 
	 * It is a required field with trigger events A01 (admit/visit notification), A02 (transfer a patient), A14 (pending admit), A15 (pending transfer). 
	 * Refer to User-defined Table 0069 - Hospital Service in Chapter 2C, Code Tables, for suggested values.
	 * 
	 * @param string $value
	 * @param number $position
	 * @return boolean
	 */
	public function setHospitalService(string $value = null, $position = 10)
	{
		return $this->setField($position, $value);
	}
	
	/**
	 * Definition: This field indicates from where the patient was admitted. 
	 * Refer to User-defined Table 0023 - Admit Source in Chapter 2C, Code Tables, for suggested values. 
	 * In the US, this field should use the Official Uniform Billing (UB) 04 2008 numeric codes found on form locator 15. 
	 * Refer to External Table UB04FL15 Source of Origin for valid values. 
	 * The UB has redefined the Admission Source as the Point of Origin for Admission or Visit. 
	 * The new UB definition is the code indicating the Point of Origin for this Admission or Visit
	 * 
	 * @param unknown $id
	 * @return boolean
	 */
 	function setAdmitsource($id)
	{
		return $this->setField(14, $id);
	} 
	
	/**
	 * Definition: For backward compatibility, a NM data type may be sent, but HL7 recommends that new implementations use the CX data type. 
	 * This field contains the unique number assigned to each patient visit. 
	 * The assigning authority and identifier type code are strongly recommended for all CX data types.
	 * 
	 * @param unknown $id
	 * @param unknown $check
	 * @param string $scheme
	 * @return boolean
	 */
  	function setVisitNumber($id, $check, $scheme="M11")
	{
		return $this->setField(19, array($id, $check, $scheme));
	}    
	
	function setChargePriceIndicator($code)
	{
		return $this->setField(21, $code);
	}  
	
	/**
	 * Definition: This field identifies the type of contract entered into by the healthcare facility and the guarantor for the purpose of settling outstanding account balances. 
	 * Refer to User-defined Table 0044 - Contract Code in Chapter 2C, Code Tables, for suggested values.
	 * 
	 * @param unknown $code
	 * @return boolean
	 */
	function setContractCode($code)
	{
		return $this->setField(24, $code);
	}  
	
	/**
	 * Definition: This field contains the admit date/time. 
	 * It is to be used if the event date/time is different than the admit date and time, i.e., a retroactive update. 
	 * This field is also used to reflect the date/time of an outpatient/emergency patient registration.
	 * 
	 * @param unknown $timecode
	 * @return boolean
	 */
	function setAdmitDate($timecode)
	{
		return $this->setField(44, $timecode);
	}  
	
	function setTotalPayments($count)
	{
		return $this->setField(49, $count);
	}  
	
	function setAlternateVisitID($id)
	{
		return $this->setField(50, $id);
	}  
	
	/**
	 * Definition: This field specifies the level on which data are being sent. 
	 * It is the indicator used to send data at two levels, visit and account. 
	 * HL7 recommends sending an 'A' or no value when the data in the message are at the account level, or 'V' to indicate that the data sent in the message are at the visit level. 
	 * Refer to User-defined Table 0326 - Visit Indicator in Chapter 2C, Code Tables, for suggested values.
	 * The value of this element affects the context of data sent in PV1, PV2 and any associated hierarchical segments (e.g., DB1, AL1, DG1, etc.).
	 * 
	 * @param unknown $code
	 * @return boolean
	 */
	function setVisitIndicator($code)
	{
		return $this->setField(51, $code);
	}  	
	
    
    
    
    
    
    //getter for most interesting fields
    
	public function getSetID($position = 1)
	{
	    return $this->getField($position);
	}
	
    function getPatientClass()
    {
		return $this->getField(2);
	}
	
	function getAssignedPatientLocation()
	{
		$field = $this->getField(3);
		$names = array('org1', 'room', 'bed', 'org2');
		$out = parent::nameSubsegements($field, $names);
		//return (object) array('a'=>1,'b'=>2);
		return $out;
	}
		
	function getAdmissionType()
	{
		return $this->getField(4);
	}		
	
	function getPriorPatientLocation()
	{
		$field = $this->getField(6);
		$names = array('pointOfCare', 'room', 'bed');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}
	
	function getAttendingDoctor()
	{
		$field = $this->getField(7);
		$names = array('id', 'lastname', 'firstname','middlename', 'suffix', 'prefix', 'degree');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}
		
	function getReferringDoctor()
	{	
		$field = $this->getField(8);
		$names = array('id', 'lastname', 'firstname','middlename', 'suffix', 'prefix', 'degree', 'country', 'zipcity', 'street', 'phone', 'fax', 'formkey', 'formOfAddress');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}	
	
	function getConsultingDoctor()
	{
		$field = $this->getField(9);
		$names = array('id', 'lastname', 'firstname', 'middlename', 'suffix', 'prefix', 'degree', 'country', 'zipcity', 'street', 'phone', 'fax', 'formkey', 'formOfAddress');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}	
	
	function getAdmitSource()
	{
		return $this->getField(14);
	}
        
        function getAmbulantorystatus()
	{
		return $this->getField(15);
	}
	
	function getVisitNumber()
	{	
		$field = $this->getField(19);             
		$names = array('id', 'check', 'method');
                if (sizeof($field)==3){
                    $out = parent::nameSubsegements($field, $names);
                    } else{
                    $out = parent::nameSubsegements(array($field,), $names);   
                    }
		return $out;
	}  
	
	//P=Privatpatient, K=Krankenkasse
	function getChargePriceIndicator()
	{
		return $this->getField(21);
	}  
	
	function getContractCode()
	{
		return $this->getField(24);
	}  
	
	function getAdmitDate()	
	{
		return parent::convertDate($this->getField(44));
	}  
	
	function getDischargeLocation()	
	{
		return $this->getField(37);
	}  
	
	//YYYYMMDDhhmmss
	function getDischargeDate()	
	{
		return parent::convertDate($this->getField(45));
	}  
	
	function getTotalPayments()
	{
		return $this->getField(49);
	}  
	
	function getAlternateVisitID()
	{
		return $this->getField(50);
	}  
	
	function getVisitIndicator()
	{
		return $this->getField(51);
	}  
	
}
