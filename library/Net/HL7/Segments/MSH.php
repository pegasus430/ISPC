<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 D.A.Dokter                                        |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: D.A.Dokter <dokter@w20e.com>                                |
// +----------------------------------------------------------------------+
//
// $Id: MSH.php,v 1.8 2004/07/05 15:41:29 wyldebeast Exp $



/**
 * MSH (message header) segment class
 *
 * Usage:
 * <code>
 * $seg =& new Net_HL7_Segments_MSH();
 *
 * $seg->setField(9, "ADT^A24");
 * echo $seg->getField(1);
 * </code>
 *
 * The Net_HL7_Segments_MSH is an implementation of the
 * Net_HL7_Segment class. The MSH segment is a bit different from
 * other segments, in that the first field is the field separator
 * after the segment name. Other fields thus start counting from 2!
 * The setting for the field separator for a whole message can be
 * changed by the setField method on index 1 of the MSH for that
 * message.  The MSH segment also contains the default settings for
 * field 2, COMPONENT_SEPARATOR, REPETITION_SEPARATOR,
 * ESCAPE_CHARACTER and SUBCOMPONENT_SEPARATOR. These fields default
 * to ^, ~, \ and & respectively.
 *
 * @version    $Revision: 1.8 $
 * @author     D.A.Dokter <dokter@w20e.com>
 * @access     public
 * @category   Networking
 * @package    Net_HL7
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */
class Net_HL7_Segments_MSH extends Net_HL7_Segment {

    /**
     * Create an instance of the MSH segment. 
     *
     * If an array argument is provided, all fields will be filled
     * from that array. Note that for composed fields and
     * subcomponents, the array may hold subarrays and
     * subsubarrays. If the reference is not given, the MSH segment
     * will be created with the MSH 1,2,7,10 and 12 fields filled in
     * for convenience.
     */
    function __construct($fields = NULL, $hl7Globals = NULL)
    {
        parent::__construct("MSH", $fields);
    
        // Only fill default fields if no fields array is given 
        //
        if (!isset($fields)) {
      
            if (!is_array($hl7Globals)) {
                $this->setField(1, '|');
                $this->setField(2, '^~\\&');
                $this->setField(7, strftime("%Y%m%d%H%M%S"));
                
                // Set ID field
                //
                $this->setField(10, $this->getField(7) . rand(10000, 99999));
                $this->setField(12, '2.3');
            }
            else {
                $this->setField(1, $hl7Globals['FIELD_SEPARATOR']);
                $this->setField(2, 
                                $hl7Globals['COMPONENT_SEPARATOR'] .
                                $hl7Globals['REPETITION_SEPARATOR'] .
                                $hl7Globals['ESCAPE_CHARACTER'] .
                                $hl7Globals['SUBCOMPONENT_SEPARATOR']
                                );
                $this->setField(7, strftime("%Y%m%d%H%M%S"));
                
                // Set ID field
                //
                $this->setField(10, $this->getField(7) . rand(10000, 99999));
                $this->setField(12, $hl7Globals['HL7_VERSION']);
            }
            
            /*
             * ISCP APP specific settings
             */
            $this->setSendingApplication('ISPC');
//             $this->setField(3,"ISPC");
            $this->setDateTimeOfMessage();
            
        }
    }


    /**
     * Set the field specified by index to value. 
     *
     * Indices start at 1, to stay with the HL7 standard. Trying to
     * set the value at index 0 has no effect. Setting the value on
     * index 1, will effectively change the value of FIELD_SEPARATOR
     * for the message containing this segment, if the value has
     * length 1; setting the field on index 2 will change the values
     * of COMPONENT_SEPARATOR, REPETITION_SEPARATOR, ESCAPE_CHARACTER
     * and SUBCOMPONENT_SEPARATOR for the message, if the string is of
     * length 4.
     * 
     * @param int Index of field
     * @param mixed Value
     * @return boolean
     * @access public
     */
    function setField($index, $value) 
    {  
        if ($index == 1) {
            if (strlen($value) != 1) {
                return false;
            }
        }
    
        if ($index == 2) {
            if (strlen($value) != 4) {
                return false;
            }
        }
    
        return parent::setField($index, $value);
    }

    
    /**
     * Definition: This field uniquely identifies the sending application among all other applications within the network enterprise. 
     * The network enterprise consists of all those applications that participate in the exchange of HL7 messages within the enterprise. 
     * Entirely site-defined. 
     * User-defined Table 0361- Application in Chapter 2C, Code Tables, is used as the user-defined table of values for the first component.
     * 
     * @param unknown $value
     * Components: <Namespace ID (IS)> ^ <Universal ID (ST)> ^ <Universal ID Type (ID)>
     * 
     * @param int $position
     * @return boolean
     */
    public function setSendingApplication($value, $position = 3)
    {
        return $this->setField($position, $value);
    }
    
    /**
     * Definition: This field further describes the sending application, MSH-3 Sending Application. 
     * With the promotion of this field to an HD data type, the usage has been broadened to include not just the sending facility but other organizational entities such as a) the organizational entity responsible for sending application; b) the responsible unit; c) a product or vendor's identifier, etc. 
     * Entirely site-defined. 
     * User-defined Table 0362 - Facility in Chapter 2C, Code Tables, is used as the HL7 identifier for the user-defined table of values for the first component.
     * 
     * @param unknown $value
     * Components: <Namespace ID (IS)> ^ <Universal ID (ST)> ^ <Universal ID Type (ID)>
     * 
     * @param int $position
     * @return boolean
     */
    public function setSendingFacility($value, $position = 4)
    {
        return $this->setField($position, $value);
    }
    
    /**
     * Definition: This field uniquely identifies the receiving application among all other applications within the network enterprise. 
     * The network enterprise consists of all those applications that participate in the exchange of HL7 messages within the enterprise. 
     * Entirely site-defined 
     * User-defined Table 0361- Application in Chapter 2C, Code Tables, is used as the HL7 identifier for the user-defined table of values for the first component.
     * 
     * @param unknown $value
     * Components: <Namespace ID (IS)> ^ <Universal ID (ST)> ^ <Universal ID Type (ID)>
     * 
     * @param int $position
     * @return boolean
     */
    public function setReceivingApplication($value, $position = 5)
    {
        return $this->setField($position, $value);
    }
    
    /**
     * Definition: This field identifies the receiving application among multiple identical instances of the application running on behalf of different organizations. 
     * User-defined Table 0362 - Facility in Chapter 2C, Code Tables, is used as the HL7 identifier for the user-defined table of values for the first component. 
     * Entirely site-defined.
     * 
     * @param unknown $value
     * Components: <Namespace ID (IS)> ^ <Universal ID (ST)> ^ <Universal ID Type (ID)>
     * @param int $position
     * @return boolean
     */
    public function setReceivingFacility($value, $position = 6)
    {
        return $this->setField($position, $value);
    }
    
    /**
     * Definition: This field contains the date/time that the sending system created the message. 
     * If the time zone is specified, it will be used throughout the message as the default time zone.
     * 
     * Note: This field was made required in version 2.4. 
     * Messages with versions prior to 2.4 are not required to value this field. 
     * This usage supports backward compatibility.
     * 
     * Note that if the time zone is not included, the time zone defaults to that of the local time zone of the sender.
     * 
     * @param unknown $value
     * @param int $position
     * @return boolean
     */
    public function setDateTimeOfMessage($value = '', $position = 7)
    {
    	if (empty($value)) {
    		$value = date('YmdHis');
    	}
    	
        return $this->setField($position, $value);
    }
    
    
    public function setSecurity($value, $position = 8)
    {
        return $this->setField($position, $value);
    }
    
    /**
     *
     * Sets message type to MSH segment.
     *
     * If trigger event is already set, then it is preserved
     *
     * Example:
     *
     * If field value is ORU^R01 and you call
     *
     * ```
     * $msh->setMessageType('ORM');
     * ```
     *
     * Then the new field value will be ORM^R01.
     * If it was empty then the new value will be just ORM.
     *
     * @param string $value
     * @param int $position
     * @return bool
     */
    public function setMessageType($value, $position = 9)
    {
        $typeField = $this->getField($position);
        if (is_array($typeField) && !empty($typeField[1])) {
            $value = [$value, $typeField[1]];
        }
        return $this->setField($position, $value);
    }
    
    /**
     *
     * Sets trigger event to MSH segment.
     *
     * If meessage type is already set, then it is preserved
     *
     * Example:
     *
     * If field value is ORU^R01 and you call
     *
     * ```
     * $msh->setTriggerEvent('R30');
     * ```
     *
     * Then the new field value will be ORU^R30.
     * If trigger event was not set then it will set the new value.
     *
     * @param string $value
     * @param int $position
     * @return bool
     */
    public function setTriggerEvent($value, $position = 9)
    {
        $typeField = $this->getField($position);
        if (is_array($typeField) && !empty($typeField[0])) {
            $value = [$typeField[0], $value];
        } else {
            $value = [$typeField, $value];
        }
        return $this->setField($position, $value);
    }
    
    public function setMessageControlId($value, $position = 10)
    {
        return $this->setField($position, $value);
    }
    public function setProcessingId($value, $position = 11)
    {
        return $this->setField($position, $value);
    }
    public function setVersionId($value, $position = 12)
    {
        return $this->setField($position, $value);
    }
    public function setSequenceNumber($value, $position = 13)
    {
        return $this->setField($position, $value);
    }
    public function setContinuationPointer($value, $position = 14)
    {
        return $this->setField($position, $value);
    }
    public function setAcceptAcknowledgementType($value, $position = 15)
    {
        return $this->setField($position, $value);
    }
    public function setApplicationAcknowledgementType($value, $position = 16)
    {
        return $this->setField($position, $value);
    }
    public function setCountryCode($value, $position = 17)
    {
        return $this->setField($position, $value);
    }
    public function setCharacterSet($value, $position = 18)
    {
        return $this->setField($position, $value);
    }
    public function setPrincipalLanguage($value, $position = 19)
    {
        return $this->setField($position, $value);
    }
    
    
    
    
    // -------------------- Getter Methods ------------------------------
    public function getSendingApplication($position = 3)
    {
        return $this->getField($position);
    }
    public function getSendingFacility($position = 4)
    {
        return $this->getField($position);
    }
    public function getReceivingApplication($position = 5)
    {
        return $this->getField($position);
    }
    public function getReceivingFacility($position = 6)
    {
        return $this->getField($position);
    }
    public function getDateTimeOfMessage($position = 7)
    {
        return $this->getField($position);
    }
    /**
     * ORM / ORU etc.
     * @param int $position
     * @return string
     */
    public function getMessageType($position = 9)
    {
        $typeField = $this->getField($position);
        if (!empty($typeField) && is_array($typeField)) {
            return (string) $typeField[0];
        }
        return (string) $typeField;
    }
    public function getTriggerEvent($position = 9)
    {
        $triggerField = $this->getField($position);
        if (!empty($triggerField[1]) && is_array($triggerField)) {
            return $triggerField[1];
        }
        return false;
    }
    public function getMessageControlId($position = 10)
    {
        return $this->getField($position);
    }
    public function getProcessingId($position = 11)
    {
        return $this->getField($position);
    }
    /**
     * Get HL7 version, e.g. 2.1, 2.3, 3.0 etc.
     * @param int $position
     * @return array|null|string
     */
    public function getVersionId($position = 12)
    {
        return $this->getField($position);
    }
    
}

?>
