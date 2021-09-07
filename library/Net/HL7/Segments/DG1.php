<?php

/**
 * DG1 (Diagnosis) segment class
 *
 */
class Net_HL7_Segments_DG1 extends Net_HL7_Segment {

    
    /**
     * Index of this segment. Incremented for every new segment of this class created
     * @var int
     */
    protected static $setID = 1;
    
    /**
     * Create an instance of the DG1 segment. 
     *
     * If an array argument is provided, all fields will be filled
     * from that array. Note that for composed fields and
     * subcomponents, the array may hold subarrays and
     * subsubarrays. 
     */
    function __construct($fields = NULL)
    {
        parent::__construct("DG1", $fields);
        
        if ( ! isset($fields)) {
			//set set-ID
			$this->setSetId($this::$setID++);
			$this->setDiagnosisType("A");
		}
    }
    
    
    public function __destruct()
    {
        $this->setSetId($this::$setID--);
    }
    

	//Setter for most required fields
	
	/**
	 * @param number 1 or 2
	 */
	function setSetId($number)
	{
		return $this->setField(1, $number);
	}
	
	/**
	 * Definition: DG1-3 - Diagnosis Code - DG1 contains the diagnosis code assigned to this diagnosis. 
	 * Refer to User-defined Table 0051 - Diagnosis Code in Chapter 2C, Code Tables, for suggested values. 
	 * This field is a CWE data type for compatibility with clinical and ancillary systems. 
	 * Either DG1-3.1-Identifier or DG1-3.2-Text is required. 
	 * When a code is used in DG1-3.1-Identifier, a coding system is required in DG1-3.3-Name of Coding System.
	 * Names of various diagnosis coding systems are listed in Chapter 2, Section 2.16.4, "Coding system table."

	 * @param array $value , ['code', 'text', 'scheme']
	 * @param number $position
	 * @return boolean
	 */
	public function setDiagnosisCode($value , $position = 3)
	{
		return $this->setField(3, $value);
	}
	
	/**
	 * Definition: This field contains a code that identifies the type of diagnosis being sent. 
	 * Refer to User-defined Table 0052 - Diagnosis Type in Chapter 2C, Code Tables, for suggested values. 
	 * This field should no longer be used to indicate "DRG" because the DRG fields have moved to the new DRG segment.
	 * A	Admitting
	 * W	Working
	 * F	Final
	 * 
	 * @param string $value
	 * @param int $position
	 * @return boolean
	 */
	public function setDiagnosisType($value , $position = 6)
	{
	    return $this->setField($position, $value);
	}
	
	/**
	 * Definition: This field contains the number that identifies the significance or priority of the diagnosis code. 
	 * Refer to HL7 Table 0359 - Diagnosis Priority in Chapter 2C, Code Tables, for suggested values.
	 * 
	 * Note: As of v 2.7, the data type has been changed to numeric. 
	 * The meaning of the values remains the same as those in HL7 Table 0418 - Procedure Priority, The value 0 conveys that this procedure is not included in the ranking.
	 * The value 1 means that this is the primary procedure. Values 2-99 convey ranked secondary procedures.
	 * 
	 * @param string $value
	 * @param int $position
	 * @return boolean
	 */
	public function setDiagnosisPriority($value , $position = 15)
	{
	    return $this->setField($position, $value);
	}
	
    
    //getter for most interesting fields
    function getSetId()
    {
		return $this->getField(1);
	}
	
    function getDiagnosisCode()
    {	
		$field = $this->getField(3);
		$names = array('code', 'text', 'scheme');
		$out = parent::nameSubsegements($field, $names);
		return $out;
	}	
	
	function getDiagnosisPriority()
    {	
		//1=Hauptdiagnose
		return $this->getField(15);
	}	
	

	public function getDiagnosisType($position = 6)
    {	
		//A=Admission, F=Discharge
		return $this->getField($position);
	}	
	
}

