<?php
/**
 * 
 * @author claudiu✍ 
 * Mar 4, 2019
 * 
 * Segment ZBE
 * https://wiki.hl7.de/index.php?title=Segment_ZBE
 */
class Net_HL7_Segments_ZBE extends Net_HL7_Segment {


    public function __construct($fields = NULL)
    {
        parent::__construct("ZBE", $fields);
    }
    
    
    /**
	 * ZBE-1 Bewegungs-ID
	 * This field contains the identification of the movement. 
	 * @param number $position
	 * @return mixed
	 */
    public function setMovementId($value , $position = 1)
	{
		return $this->setField($position, $value);
	}
	/**
	 * Timestamp for the beginning of the movement. 
	 *  
	 * @param unknown $value
	 * @param number $position
	 * @return boolean
	 */
    public function setEventOccurredDateStart($value , $position = 2)
	{
		return $this->setField($position, $value);
	}
	
	/**
	 * Timestamp for the end of the movement. 
	 *  
	 * @param unknown $value
	 * @param number $position
	 * @return boolean
	 */
    public function setEventOccurredDateEnd($value , $position = 3)
	{
		return $this->setField($position, $value);
	}

	/**
	 * This field identifies, what the receiving application shall do with this information. 
	 * Following values are defined:
	 * INSERT 	insert 	einfügen (es handelt sich um eine neu eingeführte Bewegung)
	 * UPDATE 	change 	ändern (von Attributen, nicht der Bewegungs-ID)
	 * DELETE 	delete 	löschen (die Informationen zur Bewegung werden nicht mehr benötigt)
	 * CANCEL 	cancel 	Mit den Stornonachrichten (cancel) zu verwenden
	 * REFERENCE 	use as a reference 	Referenz/Information (keine Veränderung, dient nur zur Information) 
	 * 
	 * @param unknown $value
	 * @param number $position
	 * @return boolean
	 */
	public function setEventType($value , $position = 4)
	{
	    return $this->setField($position, $value);
	}
	
	/**
	 * This field indicates if the movement refers to outdated data.
	 * 
	 * @param unknown $value
	 * @param number $position
	 * @return boolean
	 */
	public function setHistoricalMovement($value , $position = 5)
	{
	    return $this->setField($position, $value);
	}
	
	/**
	 * This filed contains the event code, that was used in the original message. 
	 * This is required if a change (ZBE4 = "UPDATE" or "CANCEL") should be applied. 
	 * Example: if an update to movement data should be corrected, "A08" shall be used.
	 *  
	 * @param unknown $value
	 * @param number $position
	 * @return boolean
	 */
	public function setOriginalEventCode($value , $position = 6)
	{
	    return $this->setField($position, $value);
	}
	 
	/**
	 * This field contains the responsible ward. 
	 *  
	 * @param unknown $value
	 * @param number $position
	 * @return boolean
	 */
	public function setEventFacility($value , $position = 7)
	{
	    return $this->setField($position, $value);
	}
	
	
	
	
	
	
	
	/*
	 * This field contains the identification of the movement. 
	 */
	public function getMovementId($position = 1)
	{
	    return $this->getField($position);
	}
	/*
	 * Timestamp for the beginning of the movement. 
	 */
	public function getEventOccurredDateStart($position = 2)
	{
	    return parent::convertDate($this->getField($position));
	}
	public function getEventOccurredDateEnd($position = 3)
	{
	    return parent::convertDate($this->getField($position));
	}
	public function getEventType($position = 4)
	{
	    return $this->getField($position);
	}
	public function getHistoricalMovement($position = 5)
	{
	    return $this->getField($position);
	}
	public function getOriginalEventCode($position = 6)
	{
	    return $this->getField($position);
	}
	public function getEventFacility($position = 7)
	{
	    return $this->getField($position);
	}
	
	
}