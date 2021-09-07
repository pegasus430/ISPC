<?php

require_once("Pms/Form.php");

class Application_Form_SetTriggersall extends Pms_Form
{
	public function InsertData($post)
	{
		$eventsarray = array(1=>'update',2=>'insert',3=>'fetch');
			
		foreach($eventsarray as $id=>$val)
		{
			$q = Doctrine_Query::create()
			->delete("FieldTrigger")
			->where("formid= ?", $_GET['frmid'])
			->andWhere("event= ?", $id);
			$q->execute();

			if(!is_array($post['trigger_'.$id])) continue;

			foreach($post['trigger_'.$id] as $key=>$trid)
			{
				$trr = new FieldTrigger();
				$trr->triggerid = $trid;
				$trr->formid = $_GET['frmid'];
				$trr->event = $id;
				$trr->operator = $post['operator_'.$id."_".$trid];
				$trr->operand = $post['operand_'.$id."_".$trid];
				if(is_array($post['event_'.$id."_".$trid]))
				{
					$inputs = Pms_CommonData::array_addslashes($post['event_'.$id."_".$trid]);
					$trr->inputs = serialize($inputs);
				}
				$trr->save();
			}
		}
	}
}

?>