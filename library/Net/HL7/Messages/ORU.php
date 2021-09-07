<?php

class Net_HL7_Messages_ORU extends Net_HL7_Message {

 
    /**
     *
     * MSH|^~\&|ISPC|TEST|SHA|FES|20130507160350||MDM^T02|1367935441|T|2.3| 
     * EVN|T02|20130507160350 
     * PID|||0021003831||Kraus^Karl||19210304|M||||||||||0045005141
     * PV1|||||||||||||||||||0045005141
     * TXA||^^PA_B_KONS^01^PA_B_KONS|AP^application/pdf|20130507160347||20130507160347|20130507160347|20130507160347|M16008^^^^^^^^^^^^^^^^^^PAGKO|||^^^^^ISPC.Uniq.Docid.222021003831^^^Doc.Beschreibung|^^^^^ISPC.Uniq.Docid.222021003831|||ISPC.UniqLokalFilename222021003831.pdf|AU^FR||AV
     * OBX||ED|42^Typ-42-Document|1|^application/pdf^^Base64^JVBE0 ... lJUVPRgo=||||||F
     * 
     * 0045005141 ist die Fallnummer, 0021003831 die Patientenid.
     * 
     * PA_B_KONS der Doktyp des Konsils, für den Arztbrief  käme PA_A_BRIEF
     * 
     * M16008 ist die Mitarbeiternummer. Entweder der freigebende Arzt (kann über ldap aus der Benutzerkennung hergeleitet werden) oder eine fixe technische Kennung
     * 
     * PAGKO ist die erbringende Stelle Konsildienst, PAGL23 für die Station, weitere möglich
     */
    function __construct()
    {
        parent::__construct();
		
		$msgtype = "T02";
		$casenumber = "0045005141";
		
        $msh =& new Net_HL7_Segments_MSH();
        $evn =& new Net_HL7_Segment("EVN");
		$pid =& new Net_HL7_Segment("PID");
		$pv1 =& new Net_HL7_Segment("PV1");
		$txa =& new Net_HL7_Segment("TXA");
		$obx =& new Net_HL7_Segment("OBX");
		
        $this->addSegment($msh);
        $this->addSegment($evn);
		$this->addSegment($pid);
		$this->addSegment($pv1);
		$this->addSegment($txa);
		$this->addSegment($obx);
		
        $msh->setField(9, "MDM^".$msgtype);
        $evn->setField(1, $msgtype);
		$evn->setField(2, $msh->getField(7));
		
		$this->setCaseNumber($casenumber);
		$this->setTXAandOBX();
    }
    
    
    function setCaseNumber($casenumber)
    {
		$pid =& $this->getSegmentByIndex(2);
		$pv1 =& $this->getSegmentByIndex(3);
		$pid->setField(18, $casenumber);
		$pv1->setField(19, $casenumber);
	}
	
	
	function setTXAandOBX()
	{
		$doctype	= "PA_B_KONS";//PA_A_BRIEF
		$timestamp	= "20130507160347";
		$staffNo 	= "M16008";
		$stelle		= "PAGKO";
		$uniqDocId	= "ISPC.Uniq.Docid.222021003831";
		$docDescr	= "Doc.Beschreibung";
		$uniqLocalFileName = "ISPC.UniqLokalFilename222021003831.pdf";
		$base64cont = "JVBE0 ... lJUVPRgo=";
		
		$txa =& $this->getSegmentByIndex(4);
		$txa->setField(2, array("","",$doctype,"01",$doctype));
		$txa->setField(3, array("AP", "application/pdf"));
		$txa->setField(4, $timestamp);
		$txa->setField(6, $timestamp);
		$txa->setField(7, $timestamp);
		$txa->setField(8, $timestamp);
		$txa->setField(9, array($staffNo,"","","","","","","","","","","","","","","","","",$stelle));
		$txa->setField(12, "^^^^^".$uniqDocId."^^^".$docDescr);
		$txa->setField(13, "^^^^^".$uniqDocId);
		$txa->setfield(16, $uniqLocalFileName);
		$txa->setfield(17, "AU^FR");
		$txa->setfield(19, "AV");
		
		$obx =& $this->getSegmentByIndex(5);
		$obx->setField(2,"ED");
		$obx->setField(3,"42^Typ-42-Document");
		$obx->setField(4,"1");
		$obx->setField(5,"^application/pdf^^Base64^".$base64cont);
		$obx->setField(11,"F");
	}
	
	
}

?>
