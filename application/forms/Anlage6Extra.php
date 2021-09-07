<?php
require_once("Pms/Form.php");
class Application_Form_Anlage6Extra extends Pms_Form
{
	public function InsertData ( $ipid, $post )
	{
		$clear_period_extra = $this->clear_period_extra($ipid, $post);

		$insert_extra = new Anlage6Extra();
		$insert_extra->ipid = $ipid;
		$insert_extra->period = $post['select_month'];
		$insert_extra->related_users = $post['involved_users'];
		$insert_extra->save();

		if ($insert_extra->id)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function clear_period_extra ( $ipid, $post )
	{
		$Q = Doctrine_Query::create()
		->delete('Anlage6Extra')
		->where('ipid LIKE "' . $ipid . '"')
		->andWhere('period = "' . $post['select_month'] . '"');
		$Q->execute();

		return true;
	}

}
?>