<?php

// require_once 'Pms/Triggers.php';

class application_Triggers_hl7Message extends Pms_Triggers
{

	public function triggerhl7Message($event,$inputs,$fieldname,$fieldid,$eventid,$gpost)
	{
		echo "<BR><font color='green'> Prepare for hl7-Message for ".$fieldname."</font>";
	}

}

?>
