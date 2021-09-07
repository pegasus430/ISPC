<?php

require_once("Pms/Form.php");
class Application_Form_PatientDrugPlanAltAlt extends Pms_Form{
	public function validate($post)
	{

	}

	public function InsertData($post)
	{
		foreach ($post['medication'] as $key => $val)
		{
			if (strlen($post['medication'][$key]) > 0)
			{
				if ($post['hidd_medication'][$key] == "")
				{
					$post['hidd_medication'][$key] = $post['newhidd_medication'][$key];
				}

				$comments = $post['comments'][$key];
				$dosage = $post['dosage'][$key];

				$cust = new PatientDrugPlanAlt();
				$cust->ipid = $post['ipid'];
				$cust->dosage = $dosage;
				$cust->comments = $comments;
				$cust->isbedarfs = $post['isbedarfs'];
				$cust->iscrisis = $post['iscrisis'];
				$cust->isivmed = $post['isivmed'];
				$cust->treatment_care = $post['treatment_care'];
				$cust->isnutrition = $post['isnutrition'];
				$cust->medication_master_id = $post['hidd_medication'][$key];
				$cust->medication_change = date('Y-m-d 00:00:00');
				$cust->save();
			}
		}
	}

	public function UpdateData($post)
	{
		$meds = Doctrine::getTable('PatientDrugPlanAlt')->find($_GET['mid']);
		$meds->medication_master_id = $post['hidd_medication'];
		$meds->dosage = $post['dosage'];
		$meds->comments = $post['comments'];
		$meds->save();
	}

	public function UpdateMultiData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		foreach ($post['hidd_medication'] as $i => $med_item)
		{
			$update_medication[$i] = "0";

			if ($post['hidd_medication'][$i] > 0)
			{
				$medid = $post['hidd_medication'][$i];
			}
			else
			{
				$medid = $post['newhidd_medication'][$i];
			}

			if (empty($post['verordnetvon'][$i]))
			{
				$post['verordnetvon'][$i] = 0;
			}

				
			$cust = Doctrine::getTable('PatientDrugPlanAlt')->find($post['drid'][$i]);
			if($cust){
				if (( strtotime(date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$i]) ||
						$cust->dosage != $post['dosage'][$i] ||
						$cust->medication_master_id != $medid ||
						$cust->verordnetvon != $post['verordnetvon'][$i] ||
						$cust->comments != $post['comments'][$i]) &&
						$post['edited'][$i] == '1'
				)
				{ //check to update only what's modified

					$update_medication[$i] = "1";

					if(!empty($post['medication_change'][$i])){
						//check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
						if ($cust->dosage != $post['dosage'][$i] ||
								$cust->medication_master_id != $medid ||
								$cust->verordnetvon != $post['verordnetvon'][$i] ||
								$cust->comments != $post['comments'][$i])
						{
							if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
								$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
							} elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
								$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
							} elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
								$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
							} else {
								$medication_change_date[$i] = date('Y-m-d 00:00:00');
							}

							// if no medication details were modified - check in the "last edit date" was edited
						} else if(
								( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
								( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
								( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
						{

							$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));

						} else if(
								( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
								( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
								( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
						)
						{
							$update_medication[$i] = "0";
						}

						// if "last edit date was edited - save current date"
					} else {
						$medication_change_date[$i] = date('Y-m-d 00:00:00');
					}
				}
				else {
					$update_medication[$i] = "0";
				}
				/* ================= Save in patient drugplan history ====================*/
				if(	$cust->dosage != $post['dosage'][$i] ||
						$cust->medication_master_id != $medid ||
						$cust->verordnetvon != $post['verordnetvon'][$i] ||
						$cust->comments != $post['comments'][$i]){
					$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
					$medication_old_medication_name[$i] = $old_med->name;
					$medication_old_medication_id[$i] =  $old_med->id;

					$history = new PatientDrugPlanAltHistory();
					$history->ipid = $ipid;
					$history->pd_id = $cust->id;
					$history->pd_medication_master_id = $cust->medication_master_id ;
					$history->pd_medication_name = $medication_old_medication_name[$i] ;
					$history->pd_medication =  $cust->medication;
					$history->pd_dosage = $cust->dosage;
					$history->pd_comments = $cust->comments ;
					$history->pd_isbedarfs = $cust->isbedarfs;
					$history->pd_iscrisis = $cust->iscrisis;
					$history->pd_isivmed = $cust->isivmed;
					$history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
					$history->pd_cocktailid= $cust->cocktailid;
					$history->pd_treatment_care = $cust->treatment_care;
					$history->pd_isnutrition = $cust->isnutrition;
					$history->pd_edit_type = $cust->edit_type;
					$history->pd_verordnetvon = $cust->verordnetvon;
					$history->pd_medication_change = $cust->medication_change;
					$history->pd_create_date = $cust->create_date;
					$history->pd_create_user = $cust->create_user;
					$history->pd_change_date = $cust->change_date;
					$history->pd_change_user = $cust->change_user;
					$history->pd_isdelete = $cust->isdelete;
					$history->save();
				}

				/* ================= Update patient drugplan item ====================*/
				if($update_medication[$i] == "1"){
					$cust->dosage.' '.
							$cust->ipid = $ipid;
					$cust->dosage = $post['dosage'][$i];
					$cust->medication_master_id = $medid;
					$cust->verordnetvon = $post['verordnetvon'][$i];
					$cust->comments = $post['comments'][$i];
					$cust->medication_change = $medication_change_date[$i];
					$cust->save();
				}
			}
			else
			{//insert new
				if($medid > '0')
				{
					$cust = new PatientDrugPlanAlt();
					$cust->ipid = $ipid;
					$cust->dosage = $post['dosage'][$i];
					$cust->medication_master_id = $medid;
					$cust->isbedarfs = $post['isbedarfs'];
					$cust->iscrisis = $post['iscrisis'];
					$cust->isivmed = $post['isivmed'];
					$cust->treatment_care = $post['treatment_care'];
					$cust->isnutrition = $post['isnutrition'];
					$cust->verordnetvon = $post['verordnetvon'][$i];
					$cust->comments = $post['comments'][$i];
					if(!empty($post['medication_change'][$i])){
						$cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
					} else{
						$cust->medication_change = date('Y-m-d 00:00:00');
					}
					$cust->save();
				}
			}
		}
	}

	public function InsertMultiData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		if ($post['isschmerzpumpe'] == 1)
		{
			//insert cocktail procedure
			$mc = new PatientDrugPlanAltCocktails();
			$mc->userid = $userid;
			$mc->clientid = $clientid;
			$mc->ipid = $ipid;
			$mc->description = $post['cocktailDescription'];
			$mc->bolus = $post['bolus'];
			$mc->flussrate = $post['flussrate'];
			$mc->sperrzeit = $post['sperrzeit'];
			$mc->save();
			//get cocktail id
			$cocktailId = $mc->id;
		}

		foreach ($post['hidd_medication'] as $key => $val)
		{
			if ($post['hidd_medication'][$key] > 0)
			{
				$medid = $post['hidd_medication'][$key];
			}
			else
			{
				$medid = $post['newhidd_medication'][$key];
			}

			if ($medid > 0)
			{
				$cust = new PatientDrugPlanAlt();
				$cust->ipid = $ipid;
				$cust->dosage = $post['dosage'][$key];
				$cust->medication_master_id = $medid;
				$cust->isbedarfs = $post['isbedarfs'];
				$cust->iscrisis = $post['iscrisis'];
				$cust->isivmed = $post['isivmed'];
				if ($post['isschmerzpumpe'] == 1)
				{
					$cust->isschmerzpumpe = $post['isschmerzpumpe'];
					$cust->cocktailid = $cocktailId;
				}
				$cust->treatment_care = $post['treatment_care'];
				$cust->isnutrition = $post['isnutrition'];

				$cust->verordnetvon = $post['verordnetvon'][$key];
				$cust->comments = $post['comments'][$key];
				
				if($post['done_date'])
				{
					$cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
				}
				else
				{
					$cust->medication_change = date('Y-m-d 00:00:00');
				}
				
				$cust->save();
				$insertedIds[] = $cust->id;
			}
		}
	}

	public function UpdateBedarfsMultiData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		for ($i = 1; $i <= sizeof($post['hidd_medication']); $i++)
		{
			if ($post['hidd_medication'][$i] > 0)
			{
				$medid = $post['hidd_medication'][$i];
			}
			else
			{
				$medid = $post['newhidd_medication'][$i];
			}
			
			$cust = Doctrine::getTable('PatientDrugPlanAlt')->find($post['drid'][$i]);
			$cust->ipid = $ipid;
			$cust->dosage = $post['dosage'][$i];
			$cust->medication_master_id = $medid;
			$cust->verordnetvon = $post['verordnetvon'][$i];
			$cust->comments = $post['comments'][$i];
			$cust->save();
		}
	}

	public function UpdateSchmerzepumpeMultiData($post)
	{
		$decid = Pms_Uuid::decrypt($_GET['id']);
		$ipid = Pms_CommonData::getIpid($decid);
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		$userid = $logininfo->userid;

		foreach ($post['hidd_medication'] as $keym => $valm)
		{
			$update_sh_medication[$keym] = "0";

			if ($post['hidd_medication'][$keym] > 0)
			{
				$medid = $post['hidd_medication'][$keym];
			}
			else
			{
				$medid = $post['newhidd_medication'][$keym];
			}

			if ($post['drid'][$keym] > 0)
			{
				$cust = Doctrine::getTable('PatientDrugPlanAlt')->find($post['drid'][$keym]);
				if ($cust){
					if (strtotime( date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$keym]) ||
							$cust->dosage != $post['dosage'][$keym] ||
							$cust->medication_master_id != $medid ||
							$cust->verordnetvon != $post['verordnetvon'][$keym])
					{//check to update only what's modified

						$update_sh_medication[$keym] = "1";

						if(!empty($post['medication_change'][$keym])){
							//check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
							if ($cust->dosage != $post['dosage'][$keym] ||
									$cust->medication_master_id != $medid ||
									$cust->verordnetvon != $post['verordnetvon'][$keym])
							{

								if ($post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
									$medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
								} elseif ($post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
									$medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
								} elseif ($post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
									$medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
								} else {
									$medication_change_date[$keym] = date('Y-m-d 00:00:00');
								}

								// if no medication details were modified - check in the "last edit date" was edited
							} else if(
									( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
									( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
									( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) != strtotime( date('d.m.Y',strtotime($cust->create_date))))) )
							{

								$medication_change_date[$keym] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));

							} else if(
									( $post['replace_with'][$keym] == 'none' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
									( $post['replace_with'][$keym] == 'change' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
									( $post['replace_with'][$keym] == 'create' && ( strtotime($post['medication_change'][$keym]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
							{

								$update_sh_medication[$keym] = "0";
							}

							// if "last edit date was edited - save current date"
						} else {
							$medication_change_date[$keym] = date('Y-m-d 00:00:00');
						}
					} else{
						$update_sh_medication[$keym] = "0";
					}
					/* ================= Save in patient drugplan history ====================*/
					if(		$cust->dosage != $post['dosage'][$i] ||
							$cust->medication_master_id != $medid ||
							$cust->verordnetvon != $post['verordnetvon'][$i]){


						$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
						$medication_old_medication_name[$i] = $old_med->name;
						$medication_old_medication_id[$i] =  $old_med->id;

						$cocktail = Doctrine::getTable('PatientDrugPlanAltCocktails')->find($cust->cocktailid);
						$history = new PatientDrugPlanAltHistory();
						$history->ipid = $ipid;
						$history->pd_id = $cust->id;
						$history->pd_medication_master_id = $cust->medication_master_id ;
						$history->pd_medication_name = $medication_old_medication_name[$i] ;
						$history->pd_medication =  $cust->medication;
						$history->pd_dosage = $cust->dosage;
						$history->pd_comments = $cust->comments ;
						$history->pd_isbedarfs = $cust->isbedarfs;
						$history->pd_iscrisis = $cust->iscrisis;
						$history->pd_isivmed = $cust->isivmed;
						$history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
						$history->pd_cocktailid= $cust->cocktailid;
						$history->pd_treatment_care = $cust->treatment_care;
						$history->pd_isnutrition = $cust->isnutrition;
						$history->pd_cocktail_comment = $cocktail->description ;
						$history->pd_cocktail_bolus = $cocktail->bolus;
						$history->pd_cocktail_flussrate =$cocktail->flussrate;
						$history->pd_cocktail_sperrzeit =$cocktail->sperrzeit;
						$history->pd_edit_type = $cust->edit_type;
						$history->pd_verordnetvon = $cust->verordnetvon;
						$history->pd_medication_change = $cust->medication_change;
						$history->pd_create_date = $cust->create_date;
						$history->pd_create_user = $cust->create_user;
						$history->pd_change_date = $cust->change_date;
						$history->pd_change_user = $cust->change_user;
						$history->pd_isdelete = $cust->isdelete;
						$history->save();
					}

					/* ================= Update patient drugplan item====================*/
					if($update_sh_medication[$keym] == "1"){
						$cust->ipid = $ipid;
						$cust->dosage = $post['dosage'][$keym];
						$cust->medication_master_id = $medid;
						$cust->verordnetvon = $post['verordnetvon'][$keym];
						$cust->medication_change= $medication_change_date[$keym];
						$cust->save();
					}

				}
			}
			else if (!empty($post['medication'][$keym]))
			{
				
				$cust = new PatientDrugPlanAlt();
				$cust->ipid = $ipid;
				$cust->dosage = $post['dosage'][$keym];
				$cust->medication_master_id = $medid;
				$cust->verordnetvon = $post['verordnetvon'][$keym];

				// medication_change
				if(!empty($post['medication_change'][$keym]))
				{
					$cust->medication_change = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$keym]));
				}
				elseif(!empty($post['done_date']))
				{
					$cust->medication_change = date('Y-m-d H:i:s', strtotime($post['done_date']));
				}
				else
				{
					$cust->medication_change = date('Y-m-d 00:00:00');
				}

				$cust->isbedarfs = 0;
				//$cust->iscrisis = 0; // ispc 1823 ??
				$cust->isivmed = 0;
				$cust->isschmerzpumpe = 1;
				$cust->cocktailid = $post['cocktailhid'];
				$cust->save();
			}
		}
		//update cocktailid
		$cust = Doctrine::getTable('PatientDrugPlanAltCocktails')->find($post['cocktailhid']);
		$cust->ipid = $ipid;
		$cust->userid = $userid;
		$cust->clientid = $clientid;
		$cust->description = $post['cocktailDescription'];
		$cust->bolus = $post['bolus'];
		$cust->flussrate = $post['flussrate'];
		$cust->sperrzeit = $post['sperrzeit'];
		$cust->save();
	}

	public function UpdateFromAdmissionData($post)
	{
		foreach ($post['medication'] as $i => $value)
		{
			$update_medication[$i] = "0";
			
			if (strlen($post['medication'][$i]) > 0)
			{
				if ($post['drid'][$i] > 0)
				{

					if ($post['hidd_medication'][$i] == "")
					{
						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
					}
					
					$cust = Doctrine::getTable('PatientDrugPlanAlt')->find($post['drid'][$i]);
					if($cust){
						if ($cust->dosage != $post['dosage'][$i] ||	$cust->medication_master_id != $post['hidd_medication'][$i] ||	$cust->comments != $post['comments'][$i]){
							$update_medication[$i] = "1";
						} else{
							$update_medication[$i] = "0";
						}
						
						/* ================= Update patient drugplan item ====================*/
						if($update_medication[$i] == "1"){
							$cust->ipid = $post['ipid'];
							$cust->dosage = $post['dosage'][$i];
							$cust->medication_master_id = $post['hidd_medication'][$i];
							$cust->comments = $post['comments'][$i];
							$cust->medication_change = date('Y-m-d 00:00:00');
							$cust->save();
						}
					}
					
					
// 					$cust = Doctrine::getTable('PatientDrugPlanAlt')->find($post['drid'][$i]);
// 					$cust->dosage = $post['dosage'][$i];
// 					$cust->medication_master_id = $post['hidd_medication'][$i];
// 					$cust->comments = $post['comments'][$i];
// 					$cust->isbedarfs = $post['isbedarfs'];
// 					$cust->medication_change = date('Y-m-d 00:00:00');
// 					$cust->save();
				}
				else
				{

					if ($post['hidd_medication'][$i] == "")
					{
						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
					}
 
					
					$cust = new PatientDrugPlanAlt();
					$cust->ipid = $post['ipid'];
					$cust->dosage = $post['dosage'][$i];
					$cust->comments = $post['comments'][$i];
					$cust->medication_master_id = $post['hidd_medication'][$i];
					$cust->isbedarfs = $post['isbedarfs'];
					$cust->iscrisis = $post['iscrisis'];
					$cust->medication_change = date('Y-m-d 00:00:00');
					$cust->save();
				}
			}
			
		}
	}
	
	public function UpdateFromAdmissionData_old($post)
	{
		foreach ($post['medication'] as $i => $value)
		{
			if (strlen($post['medication'][$i]) > 0)
			{
				if ($post['drid'][$i] > 0)
				{
	
					if ($post['hidd_medication'][$i] == "")
					{
						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
					}
					$cust = Doctrine::getTable('PatientDrugPlanAlt')->find($post['drid'][$i]);
					$cust->dosage = $post['dosage'][$i];
					$cust->medication_master_id = $post['hidd_medication'][$i];
					$cust->comments = $post['comments'][$i];
					$cust->isbedarfs = $post['isbedarfs'];
					$cust->iscrisis = $post['iscrisis'];
					$cust->medication_change = date('Y-m-d 00:00:00');
					$cust->save();
				}
				else
				{
	
					if ($post['hidd_medication'][$i] == "")
					{
						$post['hidd_medication'][$i] = $post['newhidd_medication'][$i];
					}
	
					$cust = new PatientDrugPlanAlt();
					$cust->ipid = $post['ipid'];
					$cust->dosage = $post['dosage'][$i];
					$cust->comments = $post['comments'][$i];
					$cust->medication_master_id = $post['hidd_medication'][$i];
					$cust->isbedarfs = $post['isbedarfs'];
					$cust->iscrisis = $post['iscrisis'];
					$cust->medication_change = date('Y-m-d 00:00:00');
					$cust->save();
				}
			}
		}
	}
	
	public function InsertNewData($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$userid = $logininfo->userid;

		if (strlen($post['hidd_medication']) > 0)
		{
			$meds = new PatientDrugPlanAlt();
			$meds->medication_master_id = $post['hidd_medication'];
			$meds->ipid = $post['ipid'];
			$meds->dosage = $post['dosage'];
			$meds->isbedarfs = $post['isbedarfs'];
			$meds->iscrisis = $post['iscrisis'];
			$meds->comments = $post['comments'];
			$meds->verordnetvon = $post['verordnetvon'];
			$meds->medication_change = date('Y-m-d 00:00:00');
			$meds->save();
		}

		if (strlen($post['newhidd_medication']) > 0)
		{
			$pcarr = explode("|", $post['medication']);
			if (count($pcarr) > 0)
			{
				$dosage = $pcarr[1];
			}
			else
			{
				$dosage = $post['dosage'];
			}
			$meds = new PatientDrugPlanAlt();
			$meds->medication_master_id = $post['newhidd_medication'];
			$meds->ipid = $post['ipid'];
			$meds->dosage = $post['dosage'];
			$meds->isbedarfs = $post['isbedarfs'];
			$meds->iscrisis = $post['iscrisis'];
			$meds->verordnetvon = $post['verordnetvon'];
			$meds->comments = $post['comments'];
			$meds->medication_change = date('Y-m-d 00:00:00');
			$meds->save();
		}
	}

	public function UpdateMultiDataMedicationsVerlauf($post)
	{
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$decid = Pms_Uuid::decrypt($_REQUEST['id']);
		$ipid = Pms_CommonData::getIpid($decid);

		foreach ($post['id_medication'] as $i => $med_item)
		{
			if ($post['course_type'][$i] == 'P')
			{
				if (empty($post['delete'][$post['drid'][$i]]))
				{
					$update_medication[$i] = "0";
					//medication master medi name changed -> new entry in medi master
					$cust_mm = Doctrine::getTable('Medication')->find($med_item);
					if ($post['medication'][$i] != $cust_mm->name)
					{
						$med = new Medication();
						$med->name = $post['medication'][$i];
						$med->extra = 1;
						$med->clientid = $clientid;
						$med->save();
						$med_master_id = $med->id;
					}

					if($post['drid'][$i]){// check if any medication is in post
						//edit patient drug plan and edit medication_master id if medi name is changed
						$cust = Doctrine::getTable('PatientDrugPlanAlt')->find($post['drid'][$i]);
							
						if (
								strtotime(date('d.m.Y',strtotime($cust->medication_change))) != strtotime($post['medication_change'][$i]) ||
								$cust->dosage != $post['dosage'][$i] ||
								$post['medication'][$i] != $cust_mm->name ||
								$cust->comments != $post['comments'][$i])
						{ //check to update only if something was changed (dosage or comment or medi master name and id)
							$update_medication[$i] = "1";

							//medication-change code of hell
							if(!empty($post['medication_change'][$i])){

								//check if medication details were modified (medication_master_id ,dosage,verordnetvon,comments)
								if ($cust->dosage != $post['dosage'][$i] ||
										$post['medication'][$i] != $cust_mm->name ||
										$cust->comments != $post['comments'][$i])
								{
									if ($post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ){
										$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
									} elseif ($post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ){
										$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
									} elseif ($post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ){
										$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));
									} else {
										$medication_change_date[$i] = date('Y-m-d 00:00:00');
									}

									// if no medication details were modified - check in the "last edit date" was edited
								} else if(
										( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
										( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
										( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) != strtotime( date('d.m.Y',strtotime($cust->create_date)))) ))
								{

									$medication_change_date[$i] = date('Y-m-d 00:00:00', strtotime($post['medication_change'][$i]));

								} else if(
										( $post['replace_with'][$i] == 'none' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->medication_change)))) ) ||
										( $post['replace_with'][$i] == 'change' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->change_date)))) ) ||
										( $post['replace_with'][$i] == 'create' && ( strtotime($post['medication_change'][$i]) == strtotime( date('d.m.Y',strtotime($cust->create_date)))) )
								)
								{
									$update_medication[$i] = "0";
								}
								// if "last edit date was edited - save current date"
							} else {
								$medication_change_date[$i] = date('Y-m-d 00:00:00');
							}
							/* ================= Save in patient drugplan history ====================*/
							if(	$cust->dosage != $post['dosage'][$i] ||
									$cust->medication_master_id != $medid ||
									$cust->verordnetvon != $post['verordnetvon'][$i] ||
									$cust->comments != $post['comments'][$i]){


								$old_med = Doctrine::getTable('Medication')->find($cust->medication_master_id );
								$medication_old_medication_name[$i] = $old_med->name;
								$medication_old_medication_id[$i] =  $old_med->id;

								$history = new PatientDrugPlanAltHistory();
								$history->ipid = $ipid;
								$history->pd_id = $cust->id;
								$history->pd_medication_master_id = $cust->medication_master_id ;
								$history->pd_medication_name = $medication_old_medication_name[$i] ;
								$history->pd_medication =  $cust->medication;
								$history->pd_dosage = $cust->dosage;
								$history->pd_comments = $cust->comments ;
								$history->pd_isbedarfs = $cust->isbedarfs;
								$history->pd_iscrisis = $cust->iscrisis;
								$history->pd_isivmed = $cust->isivmed;
								$history->pd_isschmerzpumpe = $cust->isschmerzpumpe;
								$history->pd_cocktailid = $cust->cocktailid;
								$history->pd_treatment_care = $cust->treatment_care;
								$history->pd_isnutrition = $cust->isnutrition;
								$history->pd_edit_type = $cust->edit_type;
								$history->pd_verordnetvon = $cust->verordnetvon;
								$history->pd_medication_change = $cust->medication_change;
								$history->pd_create_date = $cust->create_date;
								$history->pd_create_user = $cust->create_user;
								$history->pd_change_date = $cust->change_date;
								$history->pd_change_user = $cust->change_user;
								$history->pd_isdelete = $cust->isdelete;
								$history->save();
							}

							if($update_medication[$i] == "1"){

								if (!empty($med_master_id))
								{
									$cust->medication_master_id = $med_master_id;
								}
								$cust->medication_change= $medication_change_date[$i];
								$cust->dosage = $post['dosage'][$i];
								$cust->comments = $post['comments'][$i];
								$cust->edit_type = $post['course_type'][$i];
								$cust->save();
							}
						}
					}
				}
				else
				{
					$cust_del = Doctrine::getTable('PatientDrugPlanAlt')->find($post['delete'][$post['drid'][$i]]);
					if ($cust_del->id)
					{

						/* ================= Save in patient drugplan history ====================*/
						$old_med = Doctrine::getTable('Medication')->find($cust_del->medication_master_id );
						$medication_old_medication_name[$i] = $old_med->name;
						$medication_old_medication_id[$i] =  $old_med->id;
						$history = new PatientDrugPlanAltHistory();
						$history->ipid = $ipid;
						$history->pd_id = $cust_del->id;
						$history->pd_medication_master_id = $cust_del->medication_master_id ;
						$history->pd_medication_name = $medication_old_medication_name[$i] ;
						$history->pd_medication =  $cust_del->medication;
						$history->pd_dosage = $cust_del->dosage;
						$history->pd_comments = $cust_del->comments ;
						$history->pd_isbedarfs = $cust_del->isbedarfs;
						$history->pd_iscrisis = $cust_del->iscrisis;
						$history->pd_isivmed = $cust_del->isivmed;
						$history->pd_isschmerzpumpe = $cust_del->isschmerzpumpe;
						$history->pd_cocktailid = $cust_del->cocktailid;
						$history->pd_treatment_care = $cust_del->treatment_care;
						$history->pd_isnutrition = $cust_del->isnutrition;
						$history->pd_edit_type = $cust_del->edit_type;
						$history->pd_verordnetvon = $cust_del->verordnetvon;
						$history->pd_medication_change = $cust_del->medication_change;
						$history->pd_create_date = $cust_del->create_date;
						$history->pd_create_user = $cust_del->create_user;
						$history->pd_change_date = $cust_del->change_date;
						$history->pd_change_user = $cust_del->change_user;
						$history->pd_isdelete = $cust_del->isdelete;
						$history->save();

						$cust_del->isdelete = '1';
						$cust_del->edit_type = $post['course_type'][$i];
						$cust_del->save();
					}
				}
			}
		}
	}
}
?>
