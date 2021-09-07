<?php
// require_once 'Pms/Triggers.php';

class application_Triggers_addDefaultMedication extends Pms_Triggers{

	public function triggeraddDefaultMedication($event,$inputs,$fieldname,$fieldid,$eventid,$gpost)
	{
		$ipid= $event->getInvoker()->ipid;
		$logininfo= new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;
		$clientid = $logininfo->clientid;

		if($clientid == '46') { //Witten
			$isbedarfs = 0;
			$iscrisis = 0;
			$isivmed = 0;
			$post_medication = $_POST['medication']; //prevent patientdrugplan trigger
			unset($_POST['medication']);

			$default_medis = array(

					array('shortcut' => 'N', 'med' => 'Fentanyl nasal | 20 Tr. bis zu 2stl. | bei Schmerzen'),
					array('shortcut' => 'N', 'med' => 'Fentanyl nasal | 1 Hub bis zu 5 mtl. | bei Schmerzen'),
					array('shortcut' => 'N', 'med' => 'Tavor s.l. | 1 mg s.l. Tbl. Bis zu 4 stl. | Bei Unruhe/ Angst'),
					array('shortcut' => 'N', 'med' => 'Midazolam | 1 Amp. bis zu 2stl. | Bei Unruhe/ Angst'),
					array('shortcut' => 'N', 'med' => 'MCP Tr. | 40 Tr. bis zu 4stl. | Bei Übelkeit'),
					array('shortcut' => 'N', 'med' => 'Ondansetron | 1 s.l. Tbl. Bis zu 12 stdl. | Bei Übelkeit'),
					array('shortcut' => 'N', 'med' => 'Fentanyl nasal | 1 Hub bis zu 5 mtl. | Bei Atemnot'),
					array('shortcut' => 'N', 'med' => 'Midazolam nasal | Notfall 1ml/5mg bis zu 1stl.| Bei Atemnot'),
					array('shortcut' => 'N', 'med' => 'Tavor expidet 2,5 |1 bis zu 4 stl. | Bei Krämpfen'),
					array('shortcut' => 'N', 'med' => 'Movicol |3*1-2 Eßl. | Bei Obstipation --> Mind. Alle 3 Tage abführen'),
					array('shortcut' => 'N', 'med' => 'Laxoberal |10-20 Tr. Z.N. | Bei Obstipation --> Mind. Alle 3 Tage abführen'),
					array('shortcut' => 'N', 'med' => 'Glycilax sup.|1 | Bei Obstipation --> Mind. Alle 3 Tage abführen'),
					array('shortcut' => 'N', 'med' => 'Freka Klys Einlauf|1 | Bei Obstipation --> Mind. Alle 3 Tage abführen'),
					array('shortcut' => 'N', 'med' => 'Bromazep Tbl.|1 Tbl.| Bei Schlafstörung ')

			);

			foreach($default_medis as $default_med) {
				unset($med);
				unset($meds);
				unset($cust);
				if($default_med['shortcut'] == 'N'){
					$isbedarfs = 1;
				} else {
					$isbedarfs = 0;
				}

				$course_titlearr = explode("|",$default_med['med']);
				$mednamem = trim($course_titlearr[0]);
				$meddosagee = trim($course_titlearr[1]);
				$medcomment = trim($course_titlearr[2]);

				$med = new Medication();
				$med->name = $mednamem;
				$med->extra = 1;
				$med->clientid = $clientid;
				$med->save();

				$meds= new PatientDrugPlan();
				$meds->medication_master_id = $med->id;
				$meds->ipid = $ipid;
				$meds->isbedarfs = $isbedarfs;
				$meds->iscrisis = $iscrisis;
				$meds->dosage = $meddosagee;
				$meds->comments = $medcomment;
				$meds->save();

				$cust = new PatientCourse();
				$cust->ipid = $ipid;
				$cust->course_date = date("Y-m-d H:i:s",time());
				$cust->course_type=Pms_CommonData::aesEncrypt($default_med['shortcut']);
				$cust->course_title=Pms_CommonData::aesEncrypt($default_med['med']);
				$cust->user_id = $userid;
				$cust->tabname=Pms_CommonData::aesEncrypt("patient_drugplan");
				$cust->recordid = $meds->id;
				$cust->save();
			}

			$_POST['medication'] = $post_medication; //restore post medication
		}
	}
}
?>