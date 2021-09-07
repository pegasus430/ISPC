<?php

require_once("Pms/Form.php");

class Application_Form_Stammblattlmu extends Pms_Form
{
	public function insert_data($post)
	{
		$stmb = new Stammblattlmu();
		$stmb->ipid = $post['ipid'];
		$stmb->pattel = $post['pattel'];
		$stmb->cntpers1name = $post['cntpers1name'];
		$stmb->cntpers1tel = $post['cntpers1tel'];
		$stmb->cntpers1handy = $post['cntpers1handy'];
		$stmb->patientenverfugung = $post['patientenverfugung'];
		$stmb->bevollmachtigter = $post['bevollmachtigter'];
		$stmb->angehorige = $post['angehorige'];
		$stmb->diagnosis = $post['diagnosis'];
		$stmb->therapy = $post['therapy'];
		$stmb->wirdversorgt = $post['wirdversorgt'];
		$stmb->notruf = $post['notruf'];
		$stmb->morephones = $post['morephones'];
		//ISPC-2327 24.01.2019
		$stmb->client_working_schedule = $post['client_working_schedule'];
		//--
// 		$stmb->fachdienst_entry = base64_encode(serialize($post['fachdienst_entry']));
		$stmb->fachdienst_entry = serialize($post['fachdienst_entry']);
		$stmb->save();
	}
}

?>