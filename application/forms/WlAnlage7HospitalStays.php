<?php
require_once("Pms/Form.php");
class Application_Form_WlAnlage7HospitalStays extends Pms_Form
{
	public function insert_data( $post, $anlage7_form_id)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);



		if(!empty($anlage7_form_id)){
				
			if(count($post) > 0)
			{
				foreach($post as $key=>$values)
				{
					$records[] = array(
							"ipid" => $ipid,
							"anlage7_form_id" => $anlage7_form_id,
							"period" => $values['period'],
							"reason" => $values['reason']
					);
				}
			}
		}


		if(count($records) > 0)
		{
			$collection = new Doctrine_Collection('WlAnlage7HospitalStays');
			$collection->fromArray($records);
			$collection->save();
		}

	}
	
	
	public function update_data( $post, $anlage7_form_id)
	{
 

		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);


		if(!empty($anlage7_form_id)){
			
			$this->clear_form_data ($ipid,$anlage7_form_id);
				
			if(count($post) > 0)
			{
				foreach($post as $key=>$values)
				{
					$records[] = array(
							"ipid" => $ipid,
							"anlage7_form_id" => $anlage7_form_id,
							"period" => $values['period'],
							"reason" => $values['reason']
					);
				}
			}
		}

		if(count($records) > 0)
		{
			$collection = new Doctrine_Collection('WlAnlage7HospitalStays');
			$collection->fromArray($records);
			$collection->save();
		}

	}
	
	function clear_form_data ( $ipid, $anlage7_form_id )
	{
		if (!empty($ipid) && !empty($anlage7_form_id) )
		{
			$Q = Doctrine_Query::create()
			->update('WlAnlage7HospitalStays')
			->set("isdelete",'1')
			->where("anlage7_form_id='" . $anlage7_form_id . "'")
			->andWhere('ipid LIKE "' . $ipid . '"');
			$Q->execute();
	
			return true;
		}
		else
		{
			return false;
		}
	}
	
}
?>