<?php
require_once("Pms/Form.php");
class Application_Form_PatientApprovedVisitTypes extends Pms_Form
{

	public function validate ( $post )
	{

	}

	public function InsertData ( $post )
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$pavt = new PatientApprovedVisitTypes();
		$last_avt = $pavt->get_last_patient_approved_visit_type ( $post['ipid'] );

		if(empty($last_avt) && (strtotime($post['pavt_default']['start_date']) != strtotime($post['start_date'])))
		{

			if(strtotime($post['pavt_default']['start_date']) != strtotime($post['start_date'])) // if start date  =  admission date  don't add default visit type
			{
				$start_date = date('Y-m-d 00:00:00',strtotime($post['pavt_default']['start_date']));
				$end_date = date("Y-m-d 00:00:00",mktime(0,0,0,date("m",strtotime($post['start_date'])),date("d",strtotime($post['start_date']))-1,date("Y",strtotime($post['start_date']))));
					
				if(strtotime($start_date) > strtotime($end_date)){
					$end_date = date("Y-m-d 00:00:00",strtotime($post['start_date']));
				}

				//insert default
				$cust = new PatientApprovedVisitTypes();
				$cust->ipid = $post['ipid'];
				$cust->start_date = $start_date;
				$cust->end_date = $end_date;
				$cust->visit_type= $post['pavt_default']['visit_type'];
				$cust->save();
			}
				
				
		}
		else
		{
			if(!empty($last_avt) && $last_avt['end_date'] == "0000-00-00 00:00:00")
			{
				$cust = Doctrine::getTable('PatientApprovedVisitTypes')->find($last_avt['id']);
				$cust->end_date = date("Y-m-d 00:00:00",mktime(0,0,0,date("m",strtotime($post['start_date'])),date("d",strtotime($post['start_date']))-1,date("Y",strtotime($post['start_date']))));
				$cust->save();
			}
		}

		$cust = new PatientApprovedVisitTypes();
		$cust->ipid = $post['ipid'];
		$cust->start_date = date('Y-m-d 00:00:00',strtotime($post['start_date']));
		if(!empty($post['end_date']))
		{
			$cust->end_date = date('Y-m-d 00:00:00',strtotime($post['end_date']));
		}
		$cust->visit_type= $post['visit_type'];
		$cust->save();

	}

	public function UpdateData ( $post )
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$post_data =  $post ['pavt_edit'];

		$cust = Doctrine::getTable('PatientApprovedVisitTypes')->find($post_data['id']);
		$cust->start_date = date('Y-m-d 00:00:00',strtotime($post_data['start_date']));
		if(!empty($post_data['end_date']))
		{
			$cust->end_date = date('Y-m-d 00:00:00',strtotime($post_data['end_date']));
		}
		else
		{
			$cust->end_date = "";
		}
		$cust->save();

	}

	public function DeleteData ( $pavt_id )
	{

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$cust = Doctrine::getTable('PatientApprovedVisitTypes')->find($pavt_id);
		$cust->isdelete = 1;
		$cust->save();


	}

}
?>