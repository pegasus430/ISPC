<?php

require_once("Pms/Form.php");

class Application_Form_EmergencyPlan extends Pms_Form
{
	public function insertEmergencyPlan($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$Qur = Doctrine_Query::create()
		->delete('EmergencyPlan')
		->where("ipid='".$ipid."'");
		$Qur->execute();

		$stmb = new EmergencyPlan();
		$stmb->ipid = $ipid;
		$stmb->gesetzliche=$post['gesetzliche'];
		$stmb->besonderheiten=$post['besonderheiten'];
		$stmb->vollmatch=$post['vollmatch'];
		$stmb->mogliche1=$post['mogliche1'];
		$stmb->mogliche2=$post['mogliche2'];
		$stmb->mogliche3=$post['mogliche3'];
		$stmb->mogliche4=$post['mogliche4'];
		$stmb->mogliche5=$post['mogliche5'];
		$stmb->mogliche6=$post['mogliche6'];
		$stmb->mogliche7=$post['mogliche7'];
		$stmb->vompatient1=$post['vompatient1'];
		$stmb->vompatient2=$post['vompatient2'];
		$stmb->vompatient3=$post['vompatient3'];
		$stmb->vompatient4=$post['vompatient4'];
		$stmb->vompatient5=$post['vompatient5'];
		$stmb->vompatient6=$post['vompatient6'];
		$stmb->vompatient7=$post['vompatient7'];
		$stmb->save();

		if($stmb->id>0)
		{
			return true;
		}else{
			return false;
		}

	}
}

?>