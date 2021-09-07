<?php
require_once("Pms/Form.php");

class Application_Form_SetTriggers extends Pms_Form
{
	public function InsertData($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
			
		$q = Doctrine_Query::create()
		->delete("FieldTrigger")
		->where("fieldid='".$post['fieldid']."' and formid='".$post['formid']."' and event='".$post['event']."' and clientid = '".$logininfo->clientid."'");
		$q->execute();

		foreach($post['trigger_'.$post['event']] as $key=>$trid)
		{
			$trr = new FieldTrigger();
			$trr->fieldid=$post['fieldid'];
			$trr->clientid = $logininfo->clientid;
			$trr->triggerid = $trid;
			$trr->formid = $post['formid'];
			$trr->event = $post['event'];
			$trr->operator = $post['operator_'.$post['event']."_".$trid];
			$trr->operand = $post['operand_'.$post['event']."_".$trid];
			if(is_array($post['event_'.$post['event']."_".$trid]))
			{
				$inputs = Pms_CommonData::array_addslashes($post['event_'.$post['event']."_".$trid]);
				$trr->inputs = serialize($inputs);
			}
			$trr->save();
				
				
		}
	}
}

?>