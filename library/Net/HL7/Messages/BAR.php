<?php

class Net_HL7_Messages_BAR extends Net_HL7_Message {

 
    /**
     *
	 * MSH|^~\&|IWM|SIEMENS|EXTERNAL|FINANCIAL|201307020501||BAR^P01|IWM20130702050038718|P|2.3.1|||AL|||8859/1
	 * EVN|P01|201307020501
	 * PID|1||0022044606^^^IWM_Issuer||Name^Vorname|Geburtsname|19761015|F|||Straße/Hausnummer^^München^^80687^D
	 * PV1|1|s|PAGL23|||||PAAL|||||||||||0050324060|K||||||||||||||||||||||||20130626|20130701|||||H
	 * PR1|1|OPS|8-98E.0^^^||201306270036
     */
    function __construct($inputarray)
    {
        parent::__construct();
		
        $msh = new Net_HL7_Segments_MSH();
        $evn = new Net_HL7_Segment("EVN");
		$pid =$inputarray['pid'];
		$pv1 = new Net_HL7_Segment("PV1");
		$pr1 = new Net_HL7_Segment("PR1");
				
        $msh->setField(9, "BAR^".$inputarray['msgtype']);
        $evn->setField(1, $inputarray['msgtype']);
		$evn->setField(2, $msh->getField(7));

		
		$pid->setField(18, $inputarray['casenumber']);
		$pv1->setField(19, $inputarray['casenumber']);
		$pid->setField(1, 1);
		$pv1->setField(1, 1);

        $pv1->setField(44, $inputarray['startdate']);
        $pv1->setField(45, $inputarray['enddate']);

		$pv1->setField(3, $inputarray['erb_oe_pflegerisch']);
		$pv1->setField(8, $inputarray['erb_oe_fachlich']);
		
		//pv1-19 K?
		//pv1-43
		//pv1-44
		//pv1-49 H?
		
		$pr1->setField(1,"1");
		$pr1->setField(2,"OPS");
		$pr1->setField(3,$inputarray['opscode']);
		$pr1->setField(4,"");
		//$pr1->setField(5,$inputarray['opsdate']);
        $pr1->setField(5,$inputarray['startdate']."0000");
		
		$this->addSegment($msh);
        $this->addSegment($evn);
		$this->addSegment($pid);
		$this->addSegment($pv1);
		$this->addSegment($pr1);
    }
    
    

	
	
}

?>
