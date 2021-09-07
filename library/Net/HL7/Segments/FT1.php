<?php
/**
 * 
 * @author claudiu✍ 
 * Jan 16, 2019
 * 
 * FT1 – FINANCIAL TRANSACTION / BUCHUNGSINFORMATIONEN
 * 
 * Reference: http://www.hl7.eu/refactored/segFT1.html
 * Reference: http://wiki.hl7.de/index.php?title=Segment_FT1
 * 
 * php Reference : https://github.com/senaranya/HL7
 *
 */
class Net_HL7_Segments_FT1 extends Net_HL7_Segment 
{
	
	
    /**
	 * Index of this segment. Incremented for every new segment of this class created
	 * @var int
	 */
	protected static $setID = 1;
	
	
    /**
     * Create an instance of the FT1 segment. 
     */
    public function __construct(array $fields = null)
    {
        parent::__construct("FT1", $fields);
        
        if (is_null($fields)) {
			$this->setSetID($this::$setID++);
		}
    }
    
    
    public function __destruct()
    {
        $this->setSetID($this::$setID--);
    }
    
    
    
    
    /*
     * ---------------- Setter Methods ----------------
     */
    
    /**
     * Definition: This field contains the number that identifies this transaction. 
     * For the first occurrence of the segment the sequence number shall be 1, for the second occurrence it shall be 2, etc.
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
	 * Definition: This field contains a number assigned by the sending system for control purposes. 
	 * The number can be returned by the receiving system to identify errors
	 * 
	 * @param int $value
     * @param int $position
	 * @return boolean
	 */
	public function setTransactionID($value = 1, $position = 2)
	{
		return $this->setField($position, $value);
	}
		
	
	/**
	 * Definition: This field uniquely identifies the batch in which this transaction belongs.
	 * 
	 * @param int $value
     * @param int $position
	 * @return boolean
	 */
	public function setTransactionBatchID($value = 1, $position = 3)
	{
		return $this->setField($position, $value);
	}
	
	
	/**
	 * Definition: This field contains the date/time or date/time range of the transaction. 
	 * For example, this field would be used to identify the date a procedure, item, or test was conducted or used. 
	 * It may be defaulted to today's date. To specify a single point in time, only the first component is valued. 
	 * When the second component is valued, the field specifies a time interval during which the transaction took place.
	 * 
	 * @param string $value 
	 * Components: <Range Start Date/Time (DTM)> ^ <Range End Date/Time (DTM)>
	 * 
     * @param int $position
	 * @return boolean
	 */
	public function setTransactionDate($value, $position = 4)
	{
		return $this->setField($position, $value);
	}
	
	
	/**
	 * Definition: This field contains the code that identifies the type of transaction. 
	 * Refer to User-defined Table 0017 - Transaction Type in Chapter 2C, Code Tables, for suggested values.
	 * 
	 * @param unknown $value
	 * Components: <Identifier (ST)> ^ <Text (ST)> ^ <Name of Coding System (ID)> ^ <Alternate Identifier (ST)> ^ <Alternate Text (ST)> ^ <Name of Alternate Coding System (ID)> ^ <Coding System Version ID (ST)> ^ <Alternate Coding System Version ID (ST)> ^ <Original Text (ST)> ^ <Second Alternate Identifier (ST)> ^ <Second Alternate Text (ST)> ^ <Name of Second Alternate Coding System (ID)> ^ <Second Alternate Coding System Version ID (ST)> ^ <Coding System OID (ST)> ^ <Value Set OID (ST)> ^ <Value Set Version ID (DTM)> ^ <Alternate Coding System OID (ST)> ^ <Alternate Value Set OID (ST)> ^ <Alternate Value Set Version ID (DTM)> ^ <Second Alternate Coding System OID (ST)> ^ <Second Alternate Value Set OID (ST)> ^ <Second Alternate Value Set Version ID (DTM)>
	 * 
	 * @param int $position
	 * @return boolean
	 */
	public function setTransactionType($value, $position = 6)
	{
// 		AJ 	Adjustment 	Korrekturbuchung
// 		CD 	Credit 	Entlassungsbuchung (Haben)
// 		CG 	Charge 	Belastungsbuchung (Soll)
// 		CO 	Co-payment 	nicht verwendet
// 		PY 	Payment 	Zahlung

		return $this->setField($position, $value);
	}
	
	
	
	/**
	 * Definition: This field contains the code assigned by the institution for the purpose of uniquely identifying the transaction based on the Transaction Type (FT1-6). 
	 * For example, this field would be used to uniquely identify a procedure, supply item, or test for charges, or to identify the payment medium for payments. 
	 * Refer to User-defined Table 0132 - Transaction Code in Chapter 2C, Code Tables, for suggested values. 
	 * See Chapter 7 for a discussion of the universal service ID for charges.
	 * 
	 * @param unknown $value
	 * Components: <Identifier (ST)> ^ <Text (ST)> ^ <Name of Coding System (ID)> ^ <Alternate Identifier (ST)> ^ <Alternate Text (ST)> ^ <Name of Alternate Coding System (ID)> ^ <Coding System Version ID (ST)> ^ <Alternate Coding System Version ID (ST)> ^ <Original Text (ST)> ^ <Second Alternate Identifier (ST)> ^ <Second Alternate Text (ST)> ^ <Name of Second Alternate Coding System (ID)> ^ <Second Alternate Coding System Version ID (ST)> ^ <Coding System OID (ST)> ^ <Value Set OID (ST)> ^ <Value Set Version ID (DTM)> ^ <Alternate Coding System OID (ST)> ^ <Alternate Value Set OID (ST)> ^ <Alternate Value Set Version ID (DTM)> ^ <Second Alternate Coding System OID (ST)> ^ <Second Alternate Value Set OID (ST)> ^ <Second Alternate Value Set Version ID (DTM)>
	 * 
	 * @param int $position
	 * @return boolean
	 */
	public function setTransactionCode($value, $position = 7)
	{
// 		EBM2000plus 	EBM 	einheitlicher Bewertungsmaßstab
// 		GOÄ2003 	GOÄ 	Gebührenordnung für Ärzte
// 		UVGOÄ2001 	GOÄ 	Unfallversicherungsträger (GOÄ für BG-Fälle)
// 		DKGNT2004 	DKG-NT 	Deutsche Krankenhausgesellschaft - Normaltarif
// 		H 		Hauskatalog
		return $this->setField($position, $value);
	}

	
	/**
	 * Definition: This field contains the quantity of items associated with this transaction.
	 * 
	 * @param string $value
	 * @param int $position
	 * @return boolean
	 */
	public function setTransactionQuantity($value, $position = 10)
	{
		return $this->setField($position, $value);
	}
	
	
	/**
	 * Definition: This field contains the amount of a transaction. 
	 * It may be left blank if the transaction is automatically priced. 
	 * Total price for multiple items.
	 * 
	 * @param unknown $value
	 * Components: <Price (MO)> ^ <Price Type (ID)> ^ <From Value (NM)> ^ <To Value (NM)> ^ <Range Units (CWE)> ^ <Range Type (ID)>
	 * Subcomponents for Price (MO): <Quantity (NM)> & <Denomination (ID)>
	 * Subcomponents for Range Units (CWE): <Identifier (ST)> & <Text (ST)> & <Name of Coding System (ID)> & <Alternate Identifier (ST)> & <Alternate Text (ST)> & <Name of Alternate Coding System (ID)> & <Coding System Version ID (ST)> & <Alternate Coding System Version ID (ST)> & <Original Text (ST)> & <Second Alternate Identifier (ST)> & <Second Alternate Text (ST)> & <Name of Second Alternate Coding System (ID)> & <Second Alternate Coding System Version ID (ST)> & <Coding System OID (ST)> & <Value Set OID (ST)> & <Value Set Version ID (DTM)> & <Alternate Coding System OID (ST)> & <Alternate Value Set OID (ST)> & <Alternate Value Set Version ID (DTM)> & <Second Alternate Coding System OID (ST)> & <Second Alternate Value Set OID (ST)> & <Second Alternate Value Set Version ID (DTM)>
	 * 
	 * @param int $position
	 * @return boolean
	 */
	public function setTransactionAmount($value, $position = 11)
	{
		return $this->setField($position, $value);
	}
	
	
	/**
	 * Definition: This field contains the unit price of a transaction. 
	 * Price of a single item.
	 * 
	 * @param array $amountUnit
	 * Components: <Price (MO)> ^ <Price Type (ID)> ^ <From Value (NM)> ^ <To Value (NM)> ^ <Range Units (CWE)> ^ <Range Type (ID)>
	 * Subcomponents for Price (MO): <Quantity (NM)> & <Denomination (ID)>
	 * Subcomponents for Range Units (CWE): <Identifier (ST)> & <Text (ST)> & <Name of Coding System (ID)> & <Alternate Identifier (ST)> & <Alternate Text (ST)> & <Name of Alternate Coding System (ID)> & <Coding System Version ID (ST)> & <Alternate Coding System Version ID (ST)> & <Original Text (ST)> & <Second Alternate Identifier (ST)> & <Second Alternate Text (ST)> & <Name of Second Alternate Coding System (ID)> & <Second Alternate Coding System Version ID (ST)> & <Coding System OID (ST)> & <Value Set OID (ST)> & <Value Set Version ID (DTM)> & <Alternate Coding System OID (ST)> & <Alternate Value Set OID (ST)> & <Alternate Value Set Version ID (DTM)> & <Second Alternate Coding System OID (ST)> & <Second Alternate Value Set OID (ST)> & <Second Alternate Value Set Version ID (DTM)>
	 * 
	 * @param int $position
	 * @return boolean
	 */
	public function setTransactionAmountUnit($value, $position = 12)
	{
		return $this->setField($position, $value);
	}
	
		
	
	
	
	
	
	
	
	     
    /*
     * ---------------- Getter Methods ----------------
     */
	
	public function getSetID($position = 1)
	{
	    return $this->getField($position);
	}
	
	public function getTransactionID($position = 2)
	{
	    return $this->getField($position);
	}
	
	public function getTransactionBatchID($position = 3)
	{
		return $this->getField($position);
	}
    
	public function getTransactionDate($position = 4)
	{
		$components = [
		    'Range Start Date/Time',
		    'Range End Date/Time'
		];
		// 		$out = parent::nameSubsegements($field, $components);
		
	    return $this->getField($position);
	}
	
	public function getTransactionType($position = 6)
	{
		$components = [
		    'Identifier',
		    'Text',
		    'Name of Coding System',
		    //Alternate
		    //Second Alternate
		];
		// 		$out = parent::nameSubsegements($field, $components);
		
	    return $this->getField($position);
	}
	
	public function getTransactionCode($position = 7)
	{
		$components = [
	        'Identifier',
	        'Text',
	        'Name of Coding System',
			//Alternate
			//Second Alternate
		];
		// 		$out = parent::nameSubsegements($field, $components);
		
	    return $this->getField($position);
	}
	
	public function getTransactionQuantity($position = 10)
	{
	    return $this->getField($position);
	}
	
	public function getTransactionAmount($position = 11)
	{
		$components = [
			'Price' => [
				'Quantity',
				'Denomination'
			],
			'Price Type',
			'From Value',
			'To Value',
			'Range Units' => [
				'Identifier',
				'Text',
				'Name of Coding System',
				//Alternate
				//Second Alternate
			],
			'Range Type'
			
		];
		// 		$out = parent::nameSubsegements($field, $components);
		
	    return $this->getField($position);
	}
	
	public function getTransactionAmountUnit($position = 12)
	{
		$components = [
			'Price' => [
				'Quantity',
				'Denomination'
			], 
			'Price Type',
			'From Value',
			'To Value',
			'Range Units' => [
				'Identifier',
				'Text',
				'Name of Coding System',
				//Alternate
				//Second Alternate
			],
			'Range Type'
			
		];
		// 		$out = parent::nameSubsegements($field, $components);
				
	    return $this->getField($position);
	}
}

