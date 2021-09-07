<?
class Application_Form_PatientDischargePlanningAid extends Pms_Form{
	public function clear_aid_data($ipid, $plan_id )
	{
		if (!empty($plan_id))
		{
			$Q = Doctrine_Query::create()
			->update('PatientDischargePlanningAid')
			->set('isdelete','1')
			->where("plan_id='" . $plan_id. "'")
			->andWhere('ipid LIKE "' . $ipid . '"');
			$result = $Q->execute();

			return true;
		}
		else
		{
			return false;
		}
	}

	public function InsertData($post,$plan_id)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$clear_block_entryes = $this->clear_aid_data( $ipid, $plan_id);
		$records = array();
			
		foreach($post['supplier'] as $aid_key  => $supplier_values){
			$aid_supp_values[$aid_key] = $supplier_values['supplier'];
		}
		foreach ($post['aid'] as $aid_id => $aid_values)
		{
			$records[] = array(
					"ipid" => $ipid,
					"plan_id" => $plan_id,
					"aid_item" => $aid_values['aid_item'],
					"aid_item_id" => $aid_values['aid_item_id'],
					"aid_type" => $aid_values['aid_type'],
					"aid_company" => $aid_supp_values[$aid_id]
			);
		}
			
		$collection = new Doctrine_Collection('PatientDischargePlanningAid');
		$collection->fromArray($records);
		$collection->save();
	}
}

?>