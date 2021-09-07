<?php
class Application_Form_BedarfsmedicationMaster extends Pms_Form
{
	public function validate ( $post )
	{
		$Tr = new Zend_View_Helper_Translate();
		$error = 0;
		$val = new Pms_Validation();
		if (!$val->isstring($post['title']))
		{
			$this->error_message['title'] = $Tr->translate('title_error');
			$error = 1;
		}
		if ($error == 0)
		{
			return true;
		}
		return false;
	}

	public function InsertData ( $post ) 
	{
		//deprecated do not use ISPC-2554 Carmen 07.04.2020 - moved to UpdateData	   // Maria:: Migration ISPC to CISPC 08.08.2020
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;

		$cust = new BedarfsmedicationMaster();
		$cust->clientid = $clientid;
		$cust->title = $post['title'];
		$cust->save();

		foreach ($post['medication'] as $key => $val)
		{
			if (strlen($post['medication'][$key]) > 0)
			{
				if (($post['hidd_medication'][$key]) < 1)
				{
					$post['hidd_medication'][$key] = $post['newhidd_medication'][$key];
				}
				
				/*$pcarr = explode("|", $post['newmedication'][$key]);
				if (count($pcarr) > 0)
				{
					$dosage = trim($pcarr[1]);
				}
				else
				{
					$dosage = "";
				}

				$comments = "";
				if (strlen($pcarr[2]) > 0)
				{
					$comments = $pcarr[2];
				}*/
				
				$bm = new Bedarfsmedication();
				$bm->bid = $cust->id;
				$bm->medication_id = $post['hidd_medication'][$key];
				//$bm->dosage = $dosage;
				//$bm->comments = $comments;
				$bm->dosage = $post['dosage'][$key];
				$bm->comments = $post['comments'][$key];
				$bm->drug = $post['drug'][$key];
				$bm->indication = $post['indication'][$key];
				$bm->verordnetvon = $post['verordnetvon'][$key];
				$bm->concentration = $post['concentration'][$key];
				$bm->unit = $post['unit'][$key];
				$bm->type = $post['type'][$key];
				$bm->dosage_form = $post['dosage_form'][$key];
				$bm->importance = $post['importance'][$key];
				$bm->save();
			}
		}
		return $cust->id;
	}

	public function UpdateData ( $post )
	{
		//ISPC - 2124 - add indikation, concentration, etc like as adding a new line in pacient medication
	    // Maria:: Migration ISPC to CISPC 08.08.2020
		//ISPC-2554 Carmen 07.04.2020
		$logininfo = new Zend_Session_Namespace('Login_Info');
		$clientid = $logininfo->clientid;
		//--
		if($_GET['bid'])
		{
			$cust = Doctrine::getTable('BedarfsmedicationMaster')->find($_GET['bid']);
			$cust->title = $post['title'];
			$cust->save();
		}
		else 
		{
			$cust = new BedarfsmedicationMaster();
			$cust->clientid = $clientid;
			$cust->title = $post['title'];
			$cust->save();
		}
		
        // ISPC-2612 Ancuta 01.07.2020
		if($cust->id){
		    // clear data to insert again 
		    $entityset = new Bedarfsmedication();
		    $q = $entityset->getTable()->createQuery()
		    ->delete()
		    ->where('bid = ?', $cust->id)
		    ->execute();
		    
		    
		    $followers  = ConnectionMasterTable::_find_parent_followers2connectionType('BedarfsmedicationMaster', $clientid,'ids');
		 
		    if(!empty($followers )){
		        
		        $data_sql = Doctrine_Query::create()
		        ->select("*")
		        ->from('BedarfsmedicationMaster')
		        ->where('connection_id  is not null')
		        ->andWhere('master_id = ?', $cust->id)
		        ->andWhereIn('clientid', $followers );
		        $folower_data_array = $data_sql->fetchArray();
		 
		        $connected_bids = array();
		        foreach($folower_data_array as $fm=>$fml){
		            $connected_bids[] = $fml['id'];
		        }
		        
		        if($connected_bids){
		            $q2 = $entityset->getTable()->createQuery()
		            ->delete()
		            ->whereIn('bid', $connected_bids)
		            ->execute();
		        }
		    }
		}
	    // --
		
		
		
		//ISPC-2554 pct.1 Carmen 03.04.2020
		$modules = new Modules();
		if($modules->checkModulePrivileges("87", $clientid))//mmi activated
		{
			$dosageformmmi = MedicationDosageformMmiTable::getInstance()->getfrommmi();
		}
		//--
		
		//ISPC-2554 pct.3 Carmen 27.03.2020
		$atcindex = 0;
		$toupdate = array();
		//--
		
	//var_dump($post)	; exit;
		foreach ($post['medication'] as $key => $val)
		{
			if (strlen($post['medication'][$key]) > 0)
			{
				//ISPC-2554
				/*if (strlen($post['drid'][$key]) > 0)
				{
					if (($post['hidd_medication'][$key]) < 1)
					{
						$post['hidd_medication'][$key] = $post['newhidd_medication'][$key];
					}
					else
					{
						$post['newmedication'][$key] = $post['medication'][$key];
					}*/
				//--
					/*$pcarr = array();
					$pcarr = explode("|", $post['newmedication'][$key]);


					if (count($pcarr) > 0)
					{
						$dosage = trim($pcarr[1]);
					}
					else
					{
						$dosage = "";
					}

					$comments = "";
					if (strlen($pcarr[2]) > 0)
					{
						$comments = $pcarr[2];
					}*/
					
				//ISPC-2554
					/*$bm = Doctrine::getTable('Bedarfsmedication')->find($post['drid'][$key]);
					$bm->medication_id = $post['hidd_medication'][$key];
					//$bm->dosage = $dosage;
					//$bm->comments = $comments;
					$bm->dosage = $post['dosage'][$key];
					$bm->comments = $post['comments'][$key];
					$bm->drug = $post['drug'][$key];
					$bm->indication = $post['indication'][$key];
					$bm->verordnetvon = $post['verordnetvon'][$key];
					$bm->concentration = $post['concentration'][$key];
					$bm->unit = $post['unit'][$key];
					$bm->type = $post['type'][$key];
					$bm->dosage_form = $post['dosage_form'][$key];
					$bm->importance = $post['importance'][$key];
					$bm->save();
				}
				else
				{*/
				//--
					if (($post['hidd_medication'][$key]) < 1)
					{
						$post['hidd_medication'][$key] = $post['newhidd_medication'][$key];
					}

					/*$pcarr = explode("|", $post['newmedication'][$key]);
					if (count($pcarr) > 0)
					{
						$dosage = trim($pcarr[1]);
					}
					else
					{
						$dosage = "";
					}

					$comments = "";
					if (strlen($pcarr[2]) > 0)
					{
						$comments = $pcarr[2];
					}*/
					//ISPC-2554 Carmen 07.04.2020
					$post['drid'][$key] ="";
					if($_GET['bid'] && strlen($post['drid'][$key]) > 0)
					{
						$bm = Doctrine::getTable('Bedarfsmedication')->find($post['drid'][$key]);
					}
					else 
					{
						$bm = new Bedarfsmedication();
					}
					//--
					$bm->bid = $cust->id;
					$bm->medication_id = $post['hidd_medication'][$key];
					//$bm->dosage = $dosage;
					//$bm->comments = $comments;
					$bm->dosage = $post['dosage'][$key];
					$bm->comments = $post['comments'][$key];
					$bm->drug = $post['drug'][$key];
					$bm->indication = $post['indication'][$key];
					$bm->verordnetvon = $post['verordnetvon'][$key];
					$bm->concentration = $post['concentration'][$key];
					$bm->unit = $post['unit'][$key];
					$bm->type = $post['type'][$key];
					//ISPC-2554 pct.1 Carmen 03.04.2020
					if(substr($post['dosage_form'][$key], 0, 3) == 'mmi')
					{
						$dosform = new MedicationDosageForm();
						$dosform->clientid = $clientid;
						$dosform->isfrommmi = '1';
						$dosform->mmi_code = substr($post['dosage_form'][$key], 4);
						$dosform->extra = '1';
						$dosform->dosage_form = $dosageformmmi[substr($post['dosage_form'][$key], 4)]['dosageform_name'];
						$dosform->save();
		
						if($dosform->id)
						{
							$bm->dosage_form = $dosform->id;
						}
					}
					else
					{
						$bm->dosage_form = $post['dosage_form'][$key];
					}
					//--					
					$bm->importance = $post['importance'][$key];
					$bm->save();
				//} ISPC-2554
				
					//ISPC-2554 pct.3 Carmen 07.04.2020
					$atcarr = (array)json_decode(html_entity_decode($post[$key]['atc']));
					
					if(!empty($atcarr))
					{
						$toupdate[$atcindex]['ipid'] = $ipid;
						$toupdate[$atcindex]['drugplan_id'] = $drugplanid;
						$toupdate[$atcindex]['medication_master_id'] = $medmasterid;
						$toupdate[$atcindex]['atc_code'] = $atcarr['atc_code'];
						$toupdate[$atcindex]['atc_description'] = $atcarr['atc_description'];
						$toupdate[$atcindex]['atc_groupe_code'] = $atcarr['atc_groupe_code'];
						$toupdate[$atcindex]['atc_groupe_description'] = $atcarr['atc_groupe_description'];
						$atcindex++;
					}
					//--
			}
		}
		
		//ISPC-2554 pct.3 Carmen 07.04.2020
		if(!empty($toupdate))
		{
			$atccollection = new Doctrine_Collection('Bedarfsmedication');
			$atccollection->fromArray($toupdate);
			$atccollection->save();
		}
		//--
	}

}
?>