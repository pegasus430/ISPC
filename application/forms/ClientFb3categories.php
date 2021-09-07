<?php

require_once("Pms/Form.php");

class Application_Form_ClientFb3categories extends Pms_Form{

	public function insertFb3categories($post,$ctid){

		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		$Qur = Doctrine_Query::create()
		->delete('ClientFb3categories')
		->where("categoryid='".$ctid."'")
		->andwhere("clientid='".$clientid."'");
		$Qur->execute();

		$stmb = new ClientFb3categories();
		$stmb->clientid = $clientid;
		$stmb->categoryid = $ctid;
		$stmb->category_title =$post['category_title'];
		$stmb->save();


		if($ins->id>0){
			return true;
		}else{
			return false;
		}
	}

}

?>