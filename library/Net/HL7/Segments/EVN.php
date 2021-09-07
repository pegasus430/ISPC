<?php
/**
 * 
 * @author claudiu✍ 
 * Feb 1, 2019
 * 
 * 16.7.49 EVN - Event Type Segment (3.4.1)
 * http://www.hl7.eu/refactored/segEVN.html#100
 *
 */
class Net_HL7_Segments_EVN extends Net_HL7_Segment {


    public function __construct($fields = NULL)
    {
        parent::__construct("EVN", $fields); 
        
        if (empty($fields)) {
            $this->setRecordedDate(date('YmdHis'));
        }
        
    }
    
    /**
     * Attention: The EVN-1 field was retained for backward compatibilty only as of v2.5 and the detail was withdrawn and removed from the standard as of v2.7.
     * 
     * @param unknown $value
     * @param unknown $position
     */
    public function setEventTypeCode($value , $position = 1)
	{
		return $this->setField($position, $value);
	}
	/**
	 * Definition: Most systems will default to the system date/time when the transaction was entered, but they should also permit an override.
	 * 
	 * @param unknown $value
	 * @param unknown $position
	 */
    public function setRecordedDate($value , $position = 2)
	{
		return $this->setField($position, $value);
	}
	
	/**
	 * Definition: This field contains the date/time that the event is planned. 
	 * We recommend that 
	 * PV2-8 - Expected Admit Date/Time, 
	 * PV2-9 - Expected Discharge Date/Time 
	 * or PV2-47 - Expected LOA Return date/time 
	 * be used whenever possible.
	 * 
	 * @param unknown $value
	 * @param unknown $position
	 */
    public function setDatePlannedEvent($value , $position = 3)
	{
		return $this->setField($position, $value);
	}
	
	/**
	 * Definition: This field contains the reason for this event. 
	 * Refer to User-defined Table 0062 - Event Reason in Chapter 2C, Code Tables, for suggested values.
	 * @param unknown $value
	 * @param unknown $position
	 */
    public function setEventReasonCode($value , $position = 4)
	{
		return $this->setField($position, $value);
	}
	
	/**
	 * Definition: This field identifies the individual responsible for triggering the event. 
	 * Refer to User-defined Table 0188 - Operator ID in Chapter 2C, Code Tables, for suggested values.
	 * 
	 * @param unknown $value
	 * @param unknown $position
	 */
    public function setOperatorID($value , $position = 5)
	{
		return $this->setField($position, $value);
	}
	
	/**
	 * Definition: This field contains the date/time that the event actually occurred. 
	 * For example, on a transfer (A02 transfer a patient), this field would contain the date/time the patient was actually transferred. 
	 * On a cancellation event, this field should contain the date/time that the event being cancelled occurred.
	 * 
	 * @param unknown $value
	 * @param unknown $position
	 */
    public function setEventOccurred($value , $position = 6)
	{
		return $this->setField($position, $value);
	}
	
	/**
	 * Definition: This field identifies the actual facility where the event occurred as differentiated from the sending facility (MSH-4). 
	 * It would be the facility at which the Operator (EVN-5) has entered the event.
	 * 
	 * Use Case: System A is where the patient is originally registered. 
	 * This registration message is sent to an MPI, System B. 
	 * The MPI needs to broadcast the event of this update and would become the sending facility. 
	 * This new field would allow for retention of knowledge of the originating facility where the event occurred. 
	 * The MPI could be the assigning authority for the ID number as well which means that it is performing the function of assigning authority for the facility originating the event.
	 * 
	 * @param unknown $value
	 * @param number $position
	 * @return boolean
	 */
    public function setEventFacility($value , $position = 7)
	{
		return $this->setField($position, $value);
	}

	
	
	
	
	
	
	
	public function getEventTypeCode($position = 1)
	{
	    return $this->getField($position);
	}
	public function getRecordedDate($position = 2)
	{
	    return $this->getField($position);
	}
	public function getDatePlannedEvent($position = 3)
	{
	    return $this->getField($position);
	}
	public function getEventReasonCode($position = 4)
	{
	    return $this->getField($position);
	}
	public function getOperatorID($position = 5)
	{
	    return $this->getField($position);
	}
	public function getEventOccurred($position = 6)
	{
	    return $this->getField($position);
	}
	public function getEventFacility($position = 7)
	{
	    return $this->getField($position);
	}
	
	
}