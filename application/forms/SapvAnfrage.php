<?php

require_once("Pms/Form.php");

class Application_Form_SapvAnfrage extends Pms_Form
{
	public function insertSapvAnfrage($post)
	{
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
			
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		$Qur = Doctrine_Query::create()
		->delete('SapAnfrage')
		->where("ipid='".$ipid."'");
		$Qur->execute();

		$stmb = new SapAnfrage();
		$stmb->ipid = $ipid;
		$stmb->datum_der_anfrage = $post['sapv_date'];
		$stmb->beziehung_zum_patient = $post['beziehung_zum_patient'];
		$stmb->grunde_fur_die_anfrage = join(",",$post['grunde_fur_die_anfrage']);
		$stmb->kommentar_spez =  $post['kommentar_spez_wunsche'];
		$stmb->relevante_diagnosen = $post['relevante_diagnosen'];
		$stmb->relevante_nebendiagnosen = $post['relevante_nebendiagnosen'];
		$stmb->vermittelt_von = $post['vermittelt_von'];
		$stmb->hausarzt_praxis = $post['hausarzt_praxis'];
		$stmb->hausarzt_name = $post['hausarzt_name'];
		$stmb->hausarzt_mobil = $post['hausarzt_mobil'];
		$stmb->palliativarzt_mobil = $post['palliativarzt_mobil'];
		$stmb->palliativkraft_mobil = $post['palliativkraft_mobil'];
		$stmb->pflegedienst_tel = $post['pflegedienst_tel'];
		$stmb->hospizhelfer_tel = $post['hospizhelfer_tel'];
		$stmb->klinikum_tel = $post['klinikum_tel'];
		$stmb->krankenkasse_tel = $post['krankenkasse_tel'];
		$stmb->besonderheiten = $post['besonderheiten'];
		$stmb->patient_einverstanden_mit_anfrage = $post['patient_einverstanden_mit_anfrage'];
		$stmb->aktueller_aufenthalt_des_patienten = $post['aktueller_aufenthalt_des_patienten'];
		$stmb->empfehlung = join(",",$post['empfehlung']);
		$stmb->procedere = $post['verordnungsanfrage'];
		if($post['vermittelt_von']==4){
			$stmb->ehrenamtliche = trim($post['othervalues']);
		}else{
			$stmb->ehrenamtliche = "";
		}
		$stmb->anfragende_person  = $post['hdnlogname'];
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