<?php

class Net_HL7_Messages_MDMWuerzburg extends Net_HL7_Message {


    /**
    *01 TXA|0|
    *02 ACC|
    *03 PDF|
    *04 20160203130405|
    *05 |
    *06 20160203130405|
    *07 |
    *08 |
    *09 |
    *10 |
    *11 |
    *12 2a720b28-4703-4962-ba40-5980a682ea6301^com.thieme.ecp|
    *13 ParentDocument|
    *14 01369611|
    *15 |
    *16 PC-Gyn39_2002939717_01.03.1959_Kootz.pdf|
    *17 LA|
    *18 U
    *
    *
    *   OBX|
    *01 0|
    *02 TX|
    *03 PC-Gyn39^Blasenschrittmacher^LANF|
    *04 |
    *05 akzeptiert|
    *06 |
    *07 N|
    *08 |
    *09 |
    *10 |
    *11 F|
    *12 |
    *13 |
    *14 20160203130405|
    *15 ECP
    *
    */
    function __construct(
        $pidsegment,
        $casenumber = "0045005141",
        $doctype	= "PA_B_KONS",
        $timestamp	= "20130507160347",
        $staffNo 	= "M16008",
        $uniqDocId	= "ISPC.Uniq.Docid.222021003831",
        $docDescr	= "Doc.Beschreibung",
        $uniqLocalFileName = "ISPC.UniqLokalFilename222021003831.pdf",
        $base64cont = "JVBE0 ... lJUVPRgo=",
        $stelle		= "PAGKO",
        $msgtype = "T02",
        $mime= "application/pdf"
		) 
    {
        parent::Net_HL7_Message();
			
        $msh = new Net_HL7_Segments_MSH();
        $evn = new Net_HL7_Segment("EVN");
		$pid = $pidsegment;
		$pv1 = new Net_HL7_Segment("PV1");
		$txa = new Net_HL7_Segment("TXA");
		$obx = new Net_HL7_Segment("OBX");
		
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
		

		$txa->setField(2, 'ACC');
		$txa->setField(3, 'PDF');
		$txa->setField(4, $timestamp);
		$txa->setField(6, $timestamp);

        //$txa->setField(11, '');
		$txa->setField(12, $uniqDocId);
		$txa->setField(13, $uniqDocId);

		$txa->setfield(16, $uniqLocalFileName);

        $txa->setfield(17, 'LA');
		$txa->setfield(18, "U");
		
		
		$obx->setField(2,"TX");
		$obx->setField(3, $docDescr);
		$obx->setField(5,"akzeptiert");
		//$obx->setField(7, "N");
		$obx->setField(11,"F");
        $obx->setField(14,$timestamp);
    }
    
    
    function setCaseNumber($casenumber)
    {
		$pid = $this->getSegmentByIndex(2);
		$pv1 = $this->getSegmentByIndex(3);
		$pid->setField(18, $casenumber);
		$pv1->setField(19, $casenumber);
	}
	

}

?>
