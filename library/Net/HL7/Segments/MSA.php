<?php
/**
 * 
 * @author claudiu✍ 
 * Feb 5, 2019
 * 
 * MSA - message acknowledgment segment (2.14.8)
 * 
 * Reference: http://www.hl7.eu/refactored/segMSA.html
 * Reference: http://hl7-definition.caristix.com:9010/HL7%20v2.3/segment/MSA
 *
 */
class Net_HL7_Segments_MSA extends Net_HL7_Segment
{
	
    public function __construct(array $fields = null)
    {
        parent::__construct('MSA', $fields);
    }
    
    
    public function setAcknowledgementCode($value, $position = 1)
    {
        return $this->setField($position, $value);
    }
    
    
    public function setMessageControlID($value, $position = 2)
    {
        return $this->setField($position, $value);
    }
    
    
    public function setTextMessage($value, $position = 3)
    {
        return $this->setField($position, $value);
    }
    
    
    public function setExpectedSequenceNumber($value, $position = 4)
    {
        return $this->setField($position, $value);
    }
    
    
    public function setDelayedAcknowledgementType($value, $position = 5)
    {
        return $this->setField($position, $value);
    }
    
    
    public function setErrorCondition($value, $position = 6)
    {
        return $this->setField($position, $value);
    }
    
    
    
    
    
    
    
    /**
     * Getter Methods
     */
    
    public function getAcknowledgementCode($position = 1)
    {
        return $this->getField($position);
    }
    
    
    public function getMessageControlID($position = 2)
    {
        return $this->getField($position);
    }
    
    
    public function getTextMessage($position = 3)
    {
        return $this->getField($position);
    }
    
    
    public function getExpectedSequenceNumber($position = 4)
    {
        return $this->getField($position);
    }
    
    
    public function getDelayedAcknowledgementType($position = 5)
    {
        return $this->getField($position);
    }
    
    
    public function getErrorCondition($position = 6)
    {
        return $this->getField($position);
    }
    
}
