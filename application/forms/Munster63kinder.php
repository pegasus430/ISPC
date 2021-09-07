<?php
require_once("Pms/Form.php");
class Application_Form_Munster63kinder extends Pms_Form
{

	public function insert_data ( $ipid, $post )
	{
		
		$logininfo = new Zend_Session_Namespace('Login_Info');
		//radio
		/* $post['erst_verordnung'] = ($post['erst_verordnung'] == '1' ? '1' : '0');
		$post['folge_verordnung'] = ($post['folge_verordnung'] == '1' ? '1' : '0'); */
		$post['erst_verordnung'] = ($post['fe_verordnung'] == 'erst' ? '1' : '0');
		$post['folge_verordnung'] = ($post['fe_verordnung'] == 'folge' ? '1' : '0');
		//checkboxes
		$post['unfall_unfallfolgen'] = ($post['unfall_unfallfolgen'] == '1' ? '1' : '0');

		$post['vom'] = (strlen($post['vom']) > '0' ? date('Y-m-d H:i:s', strtotime($post['vom'])) : '0000-00-00 00:00:00');
		$post['bis'] = (strlen($post['bis']) > '0' ? date('Y-m-d H:i:s', strtotime($post['bis'])) : '0000-00-00 00:00:00');

		$post['ausgepragte_schmerzsymptomatik'] = ($post['ausgepragte_schmerzsymptomatik'] == '1' ? '1' : '0');
		$post['ausgepragte_urogenitale_symptomatik'] = ($post['ausgepragte_urogenitale_symptomatik'] == '1' ? '1' : '0');
		$post['ausgepragte_respiratorische'] = ($post['ausgepragte_respiratorische'] == '1' ? '1' : '0');
		$post['ausgepragte_gastrointestinale_symptomatik'] = ($post['ausgepragte_gastrointestinale_symptomatik'] == '1' ? '1' : '0');
		$post['ausgepragte_ulzerierende_exulzerierende'] = ($post['ausgepragte_ulzerierende_exulzerierende'] == '1' ? '1' : '0');
		$post['ausgepragte_neurologische_psychiatrische'] = ($post['ausgepragte_neurologische_psychiatrische'] == '1' ? '1' : '0');
		$post['sonstiges_komplexes_symptomgeschehen'] = ($post['sonstiges_komplexes_symptomgeschehen'] == '1' ? '1' : '0');

		$post['folgende_beratung'] = ($post['folgende_beratung'] == '1' ? '1' : '0');
		$post['des_behandelnden_arztes'] = ($post['des_behandelnden_arztes'] == '1' ? '1' : '0');
		$post['der_behandelnden_pflegefachkraft'] = ($post['der_behandelnden_pflegefachkraft'] == '1' ? '1' : '0');
		$post['des_patienten_der_angehorigen'] = ($post['des_patienten_der_angehorigen'] == '1' ? '1' : '0');
		$post['koordination_der_palliativversorgung'] = ($post['koordination_der_palliativversorgung'] == '1' ? '1' : '0');

		$post['additiv_unterstutzende_teilversorgung'] = ($post['additiv_unterstutzende_teilversorgung'] == '1' ? '1' : '0');
		$post['vollstandige_versorgung'] = ($post['vollstandige_versorgung'] == '1' ? '1' : '0');

		$insert_data = new Munster63kinder();
		$insert_data->ipid = $ipid;

		$insert_data->erst_verordnung = $post['erst_verordnung'];
		$insert_data->folge_verordnung = $post['folge_verordnung'];
		$insert_data->unfall_unfallfolgen = $post['unfall_unfallfolgen'];
		$insert_data->vom = $post['vom'];
		$insert_data->bis = $post['bis'];
		$insert_data->verordnungsrelevante_diagnose = $post['verordnungsrelevante_diagnose'];

		$insert_data->ausgepragte_schmerzsymptomatik = $post['ausgepragte_schmerzsymptomatik'];
		$insert_data->ausgepragte_urogenitale_symptomatik = $post['ausgepragte_urogenitale_symptomatik'];
		$insert_data->ausgepragte_respiratorische = $post['ausgepragte_respiratorische'];
		$insert_data->ausgepragte_gastrointestinale_symptomatik = $post['ausgepragte_gastrointestinale_symptomatik'];
		$insert_data->ausgepragte_ulzerierende_exulzerierende = $post['ausgepragte_ulzerierende_exulzerierende'];
		$insert_data->ausgepragte_neurologische_psychiatrische = $post['ausgepragte_neurologische_psychiatrische'];
		$insert_data->sonstiges_komplexes_symptomgeschehen = $post['sonstiges_komplexes_symptomgeschehen'];

		$insert_data->nahere_beschreibung = $post['nahere_beschreibung'];
		$insert_data->aktuelle_medikation = $post['aktuelle_medikation'];

		$insert_data->folgende_beratung = $post['folgende_beratung'];
		$insert_data->des_behandelnden_arztes = $post['des_behandelnden_arztes'];
		$insert_data->der_behandelnden_pflegefachkraft = $post['der_behandelnden_pflegefachkraft'];
		$insert_data->des_patienten_der_angehorigen = $post['des_patienten_der_angehorigen'];
		$insert_data->koordination_der_palliativversorgung = $post['koordination_der_palliativversorgung'];

		$insert_data->mit_folgender_inhaltlicher = $post['mit_folgender_inhaltlicher'];
		$insert_data->additiv_unterstutzende_teilversorgung = $post['additiv_unterstutzende_teilversorgung'];
		$insert_data->vollstandige_versorgung = $post['vollstandige_versorgung'];

		$insert_data->nahere_angaben_zu_den_notwendigen = $post['nahere_angaben_zu_den_notwendigen'];
		$insert_data->bra_options = implode(',',$post['bra_options']);
		$insert_data->save();

		$id = $insert_data->id;

		if ($id)
		{
			$comment = "Formular Muster 63 - Kinder hinzugefügt.";
			$patient_file = new PatientCourse();
			$patient_file->ipid = $ipid;
			$patient_file->course_date = date("Y-m-d H:i:s", time());
			$patient_file->course_type = Pms_CommonData::aesEncrypt("F");
			$patient_file->course_title = Pms_CommonData::aesEncrypt($comment);
			$patient_file->user_id = $logininfo->userid;
			$patient_file->done_name = Pms_CommonData::aesEncrypt('muster_63kinder_form');
			$patient_file->tabname = Pms_CommonData::aesEncrypt('muster_63kinder_form');
			$patient_file->done_id = $id;
			$patient_file->save();
				
			return $id;
		}
		else
		{
			return false;
		}
	}

	public function update_data ($ipid, $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		
		//radio
		$post['erst_verordnung'] = ($post['fe_verordnung'] == 'erst' ? '1' : '0');
		$post['folge_verordnung'] = ($post['fe_verordnung'] == 'folge' ? '1' : '0');
		//checkboxes
		$post['unfall_unfallfolgen'] = ($post['unfall_unfallfolgen'] == '1' ? '1' : '0');

		$post['vom'] = (strlen($post['vom']) > '0' ? date('Y-m-d H:i:s', strtotime($post['vom'])) : '0000-00-00 00:00:00');
		$post['bis'] = (strlen($post['bis']) > '0' ? date('Y-m-d H:i:s', strtotime($post['bis'])) : '0000-00-00 00:00:00');

		$post['ausgepragte_schmerzsymptomatik'] = ($post['ausgepragte_schmerzsymptomatik'] == '1' ? '1' : '0');
		$post['ausgepragte_urogenitale_symptomatik'] = ($post['ausgepragte_urogenitale_symptomatik'] == '1' ? '1' : '0');
		$post['ausgepragte_respiratorische'] = ($post['ausgepragte_respiratorische'] == '1' ? '1' : '0');
		$post['ausgepragte_gastrointestinale_symptomatik'] = ($post['ausgepragte_gastrointestinale_symptomatik'] == '1' ? '1' : '0');
		$post['ausgepragte_ulzerierende_exulzerierende'] = ($post['ausgepragte_ulzerierende_exulzerierende'] == '1' ? '1' : '0');
		$post['ausgepragte_neurologische_psychiatrische'] = ($post['ausgepragte_neurologische_psychiatrische'] == '1' ? '1' : '0');
		$post['sonstiges_komplexes_symptomgeschehen'] = ($post['sonstiges_komplexes_symptomgeschehen'] == '1' ? '1' : '0');

		$post['folgende_beratung'] = ($post['folgende_beratung'] == '1' ? '1' : '0');
		$post['des_behandelnden_arztes'] = ($post['des_behandelnden_arztes'] == '1' ? '1' : '0');
		$post['der_behandelnden_pflegefachkraft'] = ($post['der_behandelnden_pflegefachkraft'] == '1' ? '1' : '0');
		$post['des_patienten_der_angehorigen'] = ($post['des_patienten_der_angehorigen'] == '1' ? '1' : '0');
		$post['koordination_der_palliativversorgung'] = ($post['koordination_der_palliativversorgung'] == '1' ? '1' : '0');

		$post['additiv_unterstutzende_teilversorgung'] = ($post['additiv_unterstutzende_teilversorgung'] == '1' ? '1' : '0');
		$post['vollstandige_versorgung'] = ($post['vollstandige_versorgung'] == '1' ? '1' : '0');


		$update_data = Doctrine::getTable('Munster63kinder')->findOneById($post['saved_id']);

		$update_data->erst_verordnung = $post['erst_verordnung'];
		$update_data->folge_verordnung = $post['folge_verordnung'];
		$update_data->unfall_unfallfolgen = $post['unfall_unfallfolgen'];

		$update_data->vom = $post['vom'];
		$update_data->bis = $post['bis'];
		$update_data->verordnungsrelevante_diagnose = $post['verordnungsrelevante_diagnose'];

		$update_data->ausgepragte_schmerzsymptomatik = $post['ausgepragte_schmerzsymptomatik'];
		$update_data->ausgepragte_urogenitale_symptomatik = $post['ausgepragte_urogenitale_symptomatik'];
		$update_data->ausgepragte_respiratorische = $post['ausgepragte_respiratorische'];
		$update_data->ausgepragte_gastrointestinale_symptomatik = $post['ausgepragte_gastrointestinale_symptomatik'];
		$update_data->ausgepragte_ulzerierende_exulzerierende = $post['ausgepragte_ulzerierende_exulzerierende'];
		$update_data->ausgepragte_neurologische_psychiatrische = $post['ausgepragte_neurologische_psychiatrische'];
		$update_data->sonstiges_komplexes_symptomgeschehen = $post['sonstiges_komplexes_symptomgeschehen'];
		$update_data->nahere_beschreibung = $post['nahere_beschreibung'];
		$update_data->aktuelle_medikation = $post['aktuelle_medikation'];

		$update_data->folgende_beratung = $post['folgende_beratung'];
		$update_data->des_behandelnden_arztes = $post['des_behandelnden_arztes'];
		$update_data->der_behandelnden_pflegefachkraft = $post['der_behandelnden_pflegefachkraft'];
		$update_data->des_patienten_der_angehorigen = $post['des_patienten_der_angehorigen'];
		$update_data->koordination_der_palliativversorgung = $post['koordination_der_palliativversorgung'];
		$update_data->mit_folgender_inhaltlicher = $post['mit_folgender_inhaltlicher'];

		$update_data->additiv_unterstutzende_teilversorgung = $post['additiv_unterstutzende_teilversorgung'];
		$update_data->vollstandige_versorgung = $post['vollstandige_versorgung'];
		$update_data->nahere_angaben_zu_den_notwendigen = $post['nahere_angaben_zu_den_notwendigen'];
		$insert_data->bra_options = implode(',',$post['bra_options']);
		$update_data->save();

		$comment = "Formular Muster 63 - Kinder wurde editiert.";
		$patient_file = new PatientCourse();
		$patient_file->ipid = $ipid;
		$patient_file->course_date = date("Y-m-d H:i:s", time());
		$patient_file->course_type = Pms_CommonData::aesEncrypt("F");
		$patient_file->course_title = Pms_CommonData::aesEncrypt($comment);
		$patient_file->user_id = $logininfo->userid;
		$patient_file->done_name = Pms_CommonData::aesEncrypt('muster_63kinder_form');
		$patient_file->tabname = Pms_CommonData::aesEncrypt('muster_63kinder_form');
		$patient_file->done_id = $post['saved_id'];
		$patient_file->save();
		$id = $patient_file->id;
		
		return true;
	}

	public function mark_as_completed ( $post )
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');

		$cust = Doctrine::getTable('Munster63kinder')->findOneById($post['saved_id']);
		$cust->iscompleted = 1;
		$cust->completed_date= date("Y-m-d H:i:s", time());
		$cust->save();
		
		return true;
		}
}
?>