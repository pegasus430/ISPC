<?php

class Net_HL7_Messages_DFT extends Net_HL7_Message {

 
    /**
     *
	 * MSH|^~\&|Palliativ|SIEMENS|EXTERNAL|FINANCIAL|201307020506||DFT^P03|IWM20130702050616184|P|2.3.1|||AL|||8859/1
     * EVN|P03|201307020506||||201307020506
     * PID|1||0021862372^^^IWM_Issuer||Name^Vorname|Geburtsname|19620823|M|||Staße/Hausnummer^^Ludwigsburg^^71640^D||Telefon
     * PV1|1|a|PAGKO|||||PAAL|||||||||||0050340882|K||||||||||||||||||||||||20130627||||||KV
     * FT1|1||3098592|201306271318|20130627|CG|ILPAKONS^^ILV|||540||||||^^^RAGCT||||RAGCT|URGP||KGRADG009H2013062709484641||t--ct--^^^CT
     */
    function __construct($inputarray)
    {
        parent::__construct();
		
        $msh = new Net_HL7_Segments_MSH();
        $evn = new Net_HL7_Segment("EVN");
		$pid =$inputarray['pid'];
		$pv1 = new Net_HL7_Segment("PV1");

				
        $msh->setField(6, "FINANCIAL");
        $msh->setField(5, "EXTERNAL");
        $msh->setField(4, "SIEMENS");
        $msh->setField(9, "DFT^".$inputarray['msgtype']);
        
        $evn->setField(1, $inputarray['msgtype']);
		$evn->setField(2, $msh->getField(7));

		
		$pid->setField(18, $inputarray['casenumber']);
		$pv1->setField(19, $inputarray['casenumber']);
		$pid->setField(1, 1);
		$pv1->setField(1, 1);
		
		$pv1->setField(3, $inputarray['anf_oe_pflegerisch']);
		$pv1->setField(8, $inputarray['anf_oe_fachlich']);
		$this->addSegment($msh);
        $this->addSegment($evn);
		$this->addSegment($pid);
		$this->addSegment($pv1);	
		
		$ftcount=0;
		foreach ($inputarray['mins'] as $mina){
			if($mina[1]>0) {
                $ftcount = $ftcount + 1;
                $ft1 = new Net_HL7_Segment("FT1");
                $ft1->setField(1, $ftcount);
                //3 ???
                $ft1->setField(4, $inputarray['date']);
                $ft1->setField(5, strftime('%Y%m%d', time()));
                //5=admitdate
                //6=??
                //$grp=str_replace("'","",$grp);

                $ft1->setField(7, $mina[0]);
				$ft1->setField(10,$mina[1]);
				
				$ft1->setField(20,array($inputarray['erb_oe_pflegerisch'], $inputarray['erb_oe_fachlich']));
                $ft1->setField(21,array($inputarray['anf_oe_pflegerisch'], $inputarray['anf_oe_fachlich']));
                $this->addSegment($ft1);
				}
			

			}
				
		
    }
}

?>
