<?php
require_once("Pms/Form.php");
class Application_Form_DashboardActions extends Pms_Form
{

	public function add_done_entry ( $data )
	{
		//print_r($data);

		if(strlen($data['done_date'])>0)
		{
			$done_date = date('Y-m-d H:i:s', strtotime($data['done_date']));
		}
		else
		{
			$done_date = '0000-00-00 00:00:00';
		}
		$done_label = new DashboardActionsDone();
		$done_label->client = $data['client'];
		$done_label->user = $data['user'];
		$done_label->event = $data['event'];
		$done_label->tabname = $data['tabname'];
		if($data['extra']){
    		$done_label->extra = $data['extra'];
		}
		$done_label->done = '1';
		$done_label->done_date = $done_date;
		$done_label->source = $data['source'];
		$done_label->save();

		if ($done_label->id)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function delete_done_entry ( $done_entry )
	{
		$q = Doctrine_Query::create()
		->delete('DashboardActionsDone dad')
		->where("dad.event ='" . $done_entry . "'");
		$q->execute();

		if ($q)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}
?>