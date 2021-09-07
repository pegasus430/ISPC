<?php

Doctrine_Manager::getInstance()->bindComponent('PatientDiagnosis', 'MDAT');

class PatientDiagnosis extends BasePatientDiagnosis
{

	public $triggerformid = 9;
	public $triggerformname = "frmpatientdiagnosis";

		public function getPatientData($ipid, $tabname, $vls)
		{
			if($ipid)
			{
				$cust = Doctrine_Query::create()
					->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
					->from('PatientDiagnosis')
					->where('ipid = ?', $ipid)
					->andWhere('diagnosis_type_id in (' . $vls . ')')
					->andwhere("tabname =?", addslashes(Pms_CommonData::aesEncrypt($tabname)))
					->orderBy('id ASC');
				$track = $cust->execute();
				$darray = $track->toArray();

				for($i = 0; $i < sizeof($darray); $i++)
				{
					if($darray[$i]['icd_id'] > 0)
					{
						$dg = Doctrine_Query::create()
							->select('*')
							->from('Diagnosis')
							->where("id= ?", $darray[$i]['icd_id']);

						$dg->getSqlQuery();
						$res1 = $dg->execute();
						if($res1)
						{
							$trial = $res1->toArray();
							$darray[$i]['icd_primary'] = $trial[0]['icd_primary'];
						}
					}
				}

				if($darray)
				{
					return $darray;
				}
			}
		}

		public function getPatientDiagnosisData($ipid)
		{
			$cust = Doctrine_Query::create()
				->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
				->from('PatientDiagnosis')
				->where('ipid = ?', $ipid)
				->orderBy('diagnosis_id ASC');
			$track = $cust->execute();

			if($track)
			{
				$darray = $track->toArray();
				return $darray;
			}
		}

		public function formDiagno($arrs)
		{
			if($arrs)
			{
				foreach($arrs as $key => $val)
				{
					$ret[$key]['description'] = $val['free_name'];
					$ret[$key]['id'] = $val['id'];
					$ret[$key]['icd_primary'] = $val['icd_primary'];
					$ret[$key]['terminal'] = $val['terminal'];
					$ret[$key]['rating'] = $val['rating'];
					$ret[$key]['icd_star'] = $val['icd_star'];
					$ret[$key]['create_date'] = $val['create_date'];
					$ret[$key]['diagnosis_type_id'] = $val['diagnosis_type_id'];
					$ret[$key]['error1'] = $val['free_desc'];
				}
			}
			else
			{
				$ret = array();
			}
			return $ret;
		}

		public function getFinalData($ipid, $ival, $ipid_list = false)
		{
			$freeadrr = array();
			$diagnoaddr = array();
			$a_diagno = array();

			if($ipid_list)
			{
				$q_ipid = 'ipid IN (' . $ipid . ')';
			}
			else
			{
				$q_ipid = "ipid = '" . $ipid ."'";
			}
			$cust = Doctrine_Query::create()
				->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
				->from('PatientDiagnosis')
				->where($q_ipid)
				->andWhere('diagnosis_type_id in (' . $ival . ')')
				->orderBy('id ASC');
			$track = $cust->execute();
			$darray = $track->toArray();

			foreach($darray as $key => $val)
			{
				$dg = Doctrine_Query::create()
					->select('*')
					->from('Diagnosis')
					->where("id= ?", $val['icd_id']);
				$dg->getSqlQuery();
				$res1 = $dg->execute();

				if($res1)
				{
					$trial = $res1->toArray();
					$darray[$key]['icd_primary'] = $trial[0]['icd_primary'];
				}

				if($val['a_tabname'] == 'diagnosis_freetext')
				{
					$darray[$key]['tabname'] = 'text';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('DiagnosisText')
						->where('id in (' . $val['diagnosis_id'] . ')');
					$res = $dg->execute();
					if($res)
					{
						$try1 = $res->toArray();
						$darray[$key]['description'] = $try1[0]['free_name'];
						$darray[$key]['diagno_comment'] = $try1[0]['free_desc'];
						$darray[$key]['icd_primary'] = $try1[0]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis')
				{
					$darray[$key]['tabname'] = 'dig';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('Diagnosis')
						->where('id in (' . $val['diagnosis_id'] . ')')
						->orderBy('id ASC');

					$dg->getSqlQuery();
					$res1 = $dg->execute();
					if($res1)
					{
						$try2 = $res1->toArray();
						$darray[$key]['description'] = $try2[0]['description'];
						$darray[$key]['icd_primary'] = $try2[0]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis_icd')
				{
					$darray[$key]['tabname'] = 'diagnosis_icd';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('DiagnosisIcd')
						->where("id = ?", $val['diagnosis_id'])
						->orderBy('id ASC');
					$dg->getSqlQuery();
					$res1 = $dg->execute();
					if($res1)
					{
						$try3 = $res1->toArray();

						$darray[$key]['description'] = $try3[0]['description'];
						$darray[$key]['icd_primary'] = $try3[0]['icd_primary'];
					}
				}
			}

			foreach($darray as $key => $val)
			{
				$a_diagno[$key]['ipid'] = $val['ipid'];
				$a_diagno[$key]['icdnumber'] = trim($val['icd_primary']);
				$a_diagno[$key]['diagnosis'] = $val['description'];
				$a_diagno[$key]['hidd_diagnosis'] = $val['diagnosis_id'];
				$a_diagno[$key]['hidd_icdnumber'] = $val['icd_id'];
				$a_diagno[$key]['diagnosis_type_id'] = $val['diagnosis_type_id'];
				$a_diagno[$key]['pdid'] = $val['id'];
				$a_diagno[$key]['create_date'] = $val['create_date'];
				$a_diagno[$key]['tabname'] = $val['tabname'];
				$a_diagno[$key]['diagnosis_from'] = $val['diagnosis_from']; //ISPC - 2364
				$a_diagno[$key]['comments'] = $val['comments']; //ISPC - 2364
				$a_diagno[$key]['diagno_comment'] = $val['diagno_comment'];
			}

			return ($a_diagno);
		}

		public function get_latest_diagnosis($ipid, $ival)
		{
			if(empty($ipid) || empty($ival)){
				return;
			}			
			
			$freeadrr = array();
			$diagnoaddr = array();
			$a_diagno = array();

			$cust = Doctrine_Query::create()
				->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
				->from('PatientDiagnosis')
				->where('ipid = ?', $ipid)
				->andWhere('diagnosis_type_id in (' . $ival . ')')
				->orderBy('create_date DESC')
			    ->limit(1);
			$track = $cust->execute();
			$darray = $track->toArray();

			foreach($darray as $key => $val)
			{
				$dg = Doctrine_Query::create()
					->select('*')
					->from('Diagnosis')
					->where("id= ?", $val['icd_id']);
				$dg->getSqlQuery();
				$res1 = $dg->execute();

				if($res1)
				{
					$trial = $res1->toArray();
					$darray[$key]['icd_primary'] = $trial[0]['icd_primary'];
				}

				if($val['a_tabname'] == 'diagnosis_freetext')
				{
					$darray[$key]['tabname'] = 'text';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('DiagnosisText')
						->where('id in (' . $val['diagnosis_id'] . ')');
					$res = $dg->execute();
					if($res)
					{
						$try1 = $res->toArray();
						$darray[$key]['description'] = $try1[0]['free_name'];
						$darray[$key]['diagno_comment'] = $try1[0]['free_desc'];
						$darray[$key]['icd_primary'] = $try1[0]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis')
				{
					$darray[$key]['tabname'] = 'dig';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('Diagnosis')
						->where('id in (' . $val['diagnosis_id'] . ')')
						->orderBy('id ASC');

					$dg->getSqlQuery();
					$res1 = $dg->execute();
					if($res1)
					{
						$try2 = $res1->toArray();
						$darray[$key]['description'] = $try2[0]['description'];
						$darray[$key]['icd_primary'] = $try2[0]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis_icd')
				{
					$darray[$key]['tabname'] = 'diagnosis_icd';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('DiagnosisIcd')
						->where("id = ?", $val['diagnosis_id'])
						->orderBy('id ASC');
					$dg->getSqlQuery();
					$res1 = $dg->execute();
					if($res1)
					{
						$try3 = $res1->toArray();

						$darray[$key]['description'] = $try3[0]['description'];
						$darray[$key]['icd_primary'] = $try3[0]['icd_primary'];
					}
				}
			}

			foreach($darray as $key => $val)
			{
				$a_diagno[$key]['ipid'] = $val['ipid'];
				$a_diagno[$key]['icdnumber'] = trim($val['icd_primary']);
				$a_diagno[$key]['diagnosis'] = $val['description'];
				$a_diagno[$key]['hidd_diagnosis'] = $val['diagnosis_id'];
				$a_diagno[$key]['hidd_icdnumber'] = $val['icd_id'];
				$a_diagno[$key]['diagnosis_type_id'] = $val['diagnosis_type_id'];
				$a_diagno[$key]['pdid'] = $val['id'];
				$a_diagno[$key]['create_date'] = $val['create_date'];
				$a_diagno[$key]['tabname'] = $val['tabname'];
				$a_diagno[$key]['diagno_comment'] = $val['diagno_comment'];
			}

				
			return ($a_diagno);
		}
		

		public function get_final_diagnosis($ipid, $ival)
		{
			if(empty($ipid) || empty($ival)){
				return;
			}
			
			$freeadrr = array();
			$diagnoaddr = array();
			$a_diagno = array();
			
			if(is_array($ipid))
			{
				$ipid_arr = $ipid;
			}
			else
			{
				$ipid_arr = array($ipid);
			}

			if(is_array($ival))
			{
				$ival_arr = $ival;
			}
			else
			{
				$ival_arr = array($ival);
			}

			$cust = Doctrine_Query::create()
				->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
				->from('PatientDiagnosis')
				->whereIn('diagnosis_type_id', $ival_arr)
				->andWhereIn('ipid', $ipid_arr)
				->orderBy('id ASC');
			$darray = $cust->fetchArray();

			foreach($darray as $key => $val)
			{
				$dg = Doctrine_Query::create()
					->select('*')
					->from('Diagnosis')
					->where("id= ?", $val['icd_id']);
				$trial = $dg->fetchArray();

				if($trial)
				{
					$darray[$key]['icd_primary'] = $trial[0]['icd_primary'];
				}

				if($val['a_tabname'] == 'diagnosis_freetext')
				{
					$darray[$key]['tabname'] = 'text';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('DiagnosisText')
						->where('id in (' . $val['diagnosis_id'] . ')');
					$try1 = $dg->fetchArray();

					if($try1)
					{
						$darray[$key]['description'] = $try1[0]['free_name'];
						$darray[$key]['diagno_comment'] = $try1[0]['free_desc'];
						$darray[$key]['icd_primary'] = $try1[0]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis')
				{
					$darray[$key]['tabname'] = 'dig';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('Diagnosis')
						->where('id in (' . $val['diagnosis_id'] . ')')
						->orderBy('id ASC');
					$try2 = $dg->fetchArray();

					if($try2)
					{
						$darray[$key]['description'] = $try2[0]['description'];
						$darray[$key]['icd_primary'] = $try2[0]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis_icd')
				{
					$darray[$key]['tabname'] = 'diagnosis_icd';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('DiagnosisIcd')
						->where("id = ?", $val['diagnosis_id'])
						->orderBy('id ASC');
					$try3 = $dg->fetchArray();

					if($try3)
					{
						$darray[$key]['description'] = $try3[0]['description'];
						$darray[$key]['icd_primary'] = $try3[0]['icd_primary'];
					}
				}
			}


			foreach($darray as $key => $val)
			{
				$a_diagno[$key]['ipid'] = $val['ipid'];
				$a_diagno[$key]['icdnumber'] = $val['icd_primary'];
				$a_diagno[$key]['diagnosis'] = $val['description'];
				$a_diagno[$key]['hidd_diagnosis'] = $val['diagnosis_id'];
				$a_diagno[$key]['hidd_icdnumber'] = $val['icd_id'];
				$a_diagno[$key]['diagnosis_type_id'] = $val['diagnosis_type_id'];
				$a_diagno[$key]['pdid'] = $val['id'];
				$a_diagno[$key]['create_date'] = $val['create_date'];
				$a_diagno[$key]['tabname'] = $val['tabname'];
				$a_diagno[$key]['diagno_comment'] = $val['diagno_comment'];
			}

			return $a_diagno;
		}

		public function getPatientMainDiagnosis($ipid, $tabname)
		{
			if($ipid)
			{
				$cust = Doctrine_Query::create()
					->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
					->from('PatientDiagnosis')
					->where('ipid = ?', $ipid)
					->andWhere("tabname =?", addslashes(Pms_CommonData::aesEncrypt($tabname)))
					->orderBy('diagnosis_id ASC');
				$track = $cust->execute();

				if($track)
				{
					$darray = $track->toArray();
					return $darray;
				}
			}
		}

		public function getDiagnosisIcd($diagnoid)
		{
			$cust = Doctrine_Query::create()
				->select("*")
				->from('Diagnosis')
				->where('id = ?', $diagnoid)
				->limit("1");
			$diagnosis = $cust->execute();

			if($diagnosis)
			{
				$darray = $diagnosis->toArray();
				return $darray;
			}
		}

		public function clone_records($ipid, $client, $target_ipid, $target_client)
		{
		    
			$abb = "'HD','ND'";
			$dg = new DiagnosisType();
			$dtsarr = $dg->getDiagnosisTypes($client, $abb);
			$dttarr = $dg->getDiagnosisTypes($target_client, $abb);

			foreach($dtsarr as $k_dts_arr => $v_dts_arr)
			{
				foreach($dttarr as $k_dtt_arr => $v_dtt_arr)
				{
					if($v_dts_arr['abbrevation'] == $v_dtt_arr['abbrevation'])
					{
						$mapped_diag_types[$v_dts_arr['id']] = $v_dtt_arr['id'];
					}
				}
			}

			$cust = Doctrine_Query::create()
				->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
				->from('PatientDiagnosis')
				->where('ipid = ?', $ipid)
				->orderBy('id ASC');
			$darray = $cust->fetchArray();

			
			//ISPC-2831 Ancuta 07.05.2021
			$clinical_arr = Doctrine_Query::create()
			->select("*")
			->from('PatientDiagnosisClinical')
			->where('ipid = ?', $ipid)
			->orderBy('id ASC')
		    ->fetchArray();

		    $clinical = array();
		    if(!empty($clinical_arr)){
		        foreach($clinical_arr as $k=>$pdc){
		            $clinical[$pdc['ipid']][$pdc['patient_diagnosis_id']] = $pdc;
		        }
		    }
			// --
			$master_diag = new Diagnosis();

			foreach($darray as $diag)
			{
			    $_POST['clone'] = '1';
				$save_diag = $master_diag->clone_record($diag['diagnosis_id'], $target_client, $diag['tabname']);


				$pdiag = new PatientDiagnosis();
				// Maria:: Migration ISPC to CISPC 08.08.2020
				//ISPC-2614 Ancuta 08.08.2020 :: deactivate listner for clone
				$pc_listener = $pdiag->getListener()->get('IntenseDiagnosisConnectionListener');
				$pc_listener->setOption('disabled', true);
				//--
				$pdiag->ipid = $target_ipid;
				$pdiag->diagnosis_type_id = $mapped_diag_types[$diag['diagnosis_type_id']];
				$pdiag->diagnosis_id = $save_diag;
				$pdiag->comments = $diag['comments'];//ISPC-2831 Ancuta 07.05.2021
				$pdiag->tabname = Pms_CommonData::aesEncrypt("diagnosis_freetext");
				$pdiag->save();
				$new_diagnosis_id[$diag['id']] = $pdiag->id;
				
				//ISPC-2831 Ancuta 07.05.2021
				if(!empty($clinical[$diag['ipid']][$diag['id']])){
				    $diagno_clinical = array();
				    $diagno_clinical = $clinical[$diag['ipid']][$diag['id']];
 
				    $pdiagc = new PatientDiagnosisClinical();
				    $pdiagc->patient_diagnosis_id = $pdiag->id;
				    $pdiagc->ipid = $target_ipid;
				    $pdiagc->parent_id = $diagno_clinical['parent_id'];
				    $pdiagc->main_category = $diagno_clinical['main_category'];
				    $pdiagc->symptoms = $diagno_clinical['symptoms'];
				    $pdiagc->archived = $diagno_clinical['archived'];
				    $pdiagc->side_diagnosis = $diagno_clinical['side_diagnosis'];
				    $pdiagc->relevant2hospitalstay = $diagno_clinical['relevant2hospitalstay'];
				    $pdiagc->relevant2admission = $diagno_clinical['relevant2admission'];
				    $pdiagc->start_date = $diagno_clinical['start_date'];
				    $pdiagc->end_date = $diagno_clinical['end_date'];
				    $pdiagc->save();
				}
				// --
				
				$pc_listener->setOption('disabled', false);
 
			}

			//copy diagnosis meta
			$cust_meta = Doctrine_Query::create()
				->select("*")
				->from('PatientDiagnosisMeta')
				->where('ipid = ?', $ipid)
				->orderBy('id ASC');
			$meta_diag = $cust_meta->fetchArray();

			if($new_diagnosis_id && $meta_diag)
			{
				foreach($meta_diag as $k_meta => $v_meta)
				{
					$pmdiag = new PatientDiagnosisMeta();
					$pmdiag->ipid = $target_ipid;
					$pmdiag->metaid = $v_meta['metaid'];
					$pmdiag->diagnoid = $new_diagnosis_id[$v_meta['diagnoid']];
					$pmdiag->save();
				}
			}
		}

		public function get_multiple_finaldata($ipids, $ival = false)
		{
			if(empty($ipids)){
				return;
			}
			
			$freeadrr = array();
			$diagnoaddr = array();
			$a_diagno = array();

			if(!is_array($ipids))
			{
				$ipid_list = array($ipids);
			}
			else
			{
				$ipid_list = $ipids;
			}
			
			$cust = Doctrine_Query::create()
				->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
				->from('PatientDiagnosis');
			if($ival)
			{
				$cust->andWhereIn('diagnosis_type_id', $ival);
			}
			$cust->andWhereIn('ipid', $ipid_list);
			$cust->orderBy('id ASC');
			$darray = $cust->fetchArray();

			foreach($darray as $key => $val)
			{
				$dg = Doctrine_Query::create()
					->select('*')
					->from('Diagnosis')
					->where("id= '" . $val['icd_id'] . "'");
				$trial = $dg->fetchArray();

				if($trial)
				{
					$darray[$key]['icd_primary'] = $trial[0]['icd_primary'];
				}

				if($val['a_tabname'] == 'diagnosis_freetext')
				{
					$darray[$key]['tabname'] = 'text';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('DiagnosisText')
						->where('id in (' . $val['diagnosis_id'] . ')');

					$try1 = $dg->fetchArray();
					if($try1)
					{
						$darray[$key]['description'] = $try1[0]['free_name'];
						$darray[$key]['diagno_comment'] = $try1[0]['free_desc'];
						$darray[$key]['icd_primary'] = $try1[0]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis')
				{
					$darray[$key]['tabname'] = 'dig';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('Diagnosis')
						->where('id in (' . $val['diagnosis_id'] . ')')
						->orderBy('id ASC');
					$try2 = $dg->fetchArray();
					if($try2)
					{
						$darray[$key]['description'] = $try2[0]['description'];
						$darray[$key]['icd_primary'] = $try2[0]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis_icd')
				{
					$darray[$key]['tabname'] = 'diagnosis_icd';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					$dg = Doctrine_Query::create()
						->select('*')
						->from('DiagnosisIcd')
						->where("id = ?",$val['diagnosis_id'])
						->orderBy('id ASC');
					$try3 = $dg->fetchArray();
					if($try3)
					{

						$darray[$key]['description'] = $try3[0]['description'];
						$darray[$key]['icd_primary'] = $try3[0]['icd_primary'];
					}
				}
			}

			foreach($darray as $key => $val)
			{
				$a_diagno[$key]['ipid'] = $val['ipid'];
				$a_diagno[$key]['icdnumber'] = $val['icd_primary'];
				$a_diagno[$key]['diagnosis'] = $val['description'];
				$a_diagno[$key]['hidd_diagnosis'] = $val['diagnosis_id'];
				$a_diagno[$key]['hidd_icdnumber'] = $val['icd_id'];
				$a_diagno[$key]['diagnosis_type_id'] = $val['diagnosis_type_id'];
				$a_diagno[$key]['pdid'] = $val['id'];
				$a_diagno[$key]['create_date'] = $val['create_date'];
				$a_diagno[$key]['tabname'] = $val['tabname'];
				$a_diagno[$key]['diagno_comment'] = $val['diagno_comment'];
			}
			
			
			return $a_diagno;
			
		}

		public function get_multiple_patients_diagnosis($ipids, $ival = false)
		{
			if(empty($ipids)){
				return;
			}
			
			$freeadrr = array();
			$diagnoaddr = array();
			$a_diagno = array();
			
			if(!is_array($ipids))
			{
				$ipid_list = array($ipids);
			}
			else
			{
				$ipid_list = $ipids;
			}
			
			$cust = Doctrine_Query::create()
				->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as a_tabname")
				->from('PatientDiagnosis');
			if($ival)
			{
				$cust->andWhereIn('diagnosis_type_id', $ival);
			}
			$cust->andWhereIn('ipid', $ipid_list);
			$cust->orderBy('id ASC');
			$darray = $cust->fetchArray();

			foreach($darray as $key => $val)
			{
				$icds_ids[] = $val['icd_id'];
				$disgnosis_ids[] = $val['diagnosis_id'];
			}
			if(empty($disgnosis_ids))
			{
				$disgnosis_ids[] = "99999999999";
			}

			if(empty($icds_ids))
			{
				$icds_ids[] = "99999999999";
			}

			$dg = Doctrine_Query::create()
				->select('id,icd_primary')
				->from('Diagnosis')
				->whereIn("id", $icds_ids);
			$trial = $dg->fetchArray();

			foreach($trial as $t => $tv)
			{
				$trial_array[$tv['id']]['icd_primary'] = $tv['icd_primary'];
			}

			$dg = Doctrine_Query::create()
				->select('id,free_desc,free_name,icd_primary')
				->from('DiagnosisText')
				->whereIn('id', $disgnosis_ids);
			$try1 = $dg->fetchArray();

			foreach($try1 as $t1 => $t1v)
			{
				$try1_array[$t1v['id']]['free_name'] = $t1v['free_name'];
				$try1_array[$t1v['id']]['free_desc'] = $t1v['free_desc'];
				$try1_array[$t1v['id']]['icd_primary'] = $t1v['icd_primary'];
			}

			$dg = Doctrine_Query::create()
				->select('id,description,icd_primary')
				->from('Diagnosis')
				->whereIn("id", $disgnosis_ids);
			$try2 = $dg->fetchArray();

			foreach($try2 as $t2 => $t2val)
			{
				$try2_array[$t2val['id']]['description'] = $t2val['description'];
				$try2_array[$t2val['id']]['icd_primary'] = $t2val['icd_primary'];
			}

			$dg = Doctrine_Query::create()
				->select('id,description,icd_primary')
				->from('DiagnosisIcd')
				->whereIn('id', $disgnosis_ids)
				->orderBy('id ASC');
			$try3 = $dg->fetchArray();

			foreach($try3 as $t3 => $t3val)
			{
				$try3_array[$t3val['id']]['description'] = $t3val['description'];
				$try3_array[$t3val['id']]['icd_primary'] = $t3val['icd_primary'];
			}

			foreach($darray as $key => $val)
			{

				if($trial_array[$val['icd_id']]['icd_primary'])
				{
					$darray[$key]['icd_primary'] = $trial_array[$val['icd_id']]['icd_primary'];
				}

				if($val['a_tabname'] == 'diagnosis_freetext')
				{
					$darray[$key]['tabname'] = 'text';

					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					if($try1_array[$val['diagnosis_id']])
					{
						$darray[$key]['description'] = $try1_array[$val['diagnosis_id']]['free_name'];
						$darray[$key]['diagno_comment'] = $try1_array[$val['diagnosis_id']]['free_desc'];
						$darray[$key]['icd_primary'] = $try1_array[$val['diagnosis_id']]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis')
				{
					$darray[$key]['tabname'] = 'dig';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}

					if($try2_array[$val['diagnosis_id']])
					{
						$darray[$key]['description'] = $try2_array[$val['diagnosis_id']]['description'];
						$darray[$key]['icd_primary'] = $try2_array[$val['diagnosis_id']]['icd_primary'];
					}
				}

				if($val['a_tabname'] == 'diagnosis_icd')
				{
					$darray[$key]['tabname'] = 'diagnosis_icd';
					if($val['diagnosis_id'] == "")
					{
						$val['diagnosis_id'] = '0';
					}
					if($try3_array[$val['diagnosis_id']])
					{
						$darray[$key]['description'] = $try3_array[$val['diagnosis_id']]['description'];
						$darray[$key]['icd_primary'] = $try3_array[$val['diagnosis_id']]['icd_primary'];
					}
				}
			}

			foreach($darray as $key => $val)
			{
				$a_diagno[$key]['ipid'] = $val['ipid'];
				$a_diagno[$key]['icdnumber'] = trim($val['icd_primary']);
				$a_diagno[$key]['diagnosis'] = $val['description'];
				$a_diagno[$key]['hidd_diagnosis'] = $val['diagnosis_id'];
				$a_diagno[$key]['hidd_icdnumber'] = $val['icd_id'];
				$a_diagno[$key]['diagnosis_type_id'] = $val['diagnosis_type_id'];
				$a_diagno[$key]['pdid'] = $val['id'];
				$a_diagno[$key]['create_date'] = $val['create_date'];
				$a_diagno[$key]['tabname'] = $val['tabname'];
				$a_diagno[$key]['diagno_comment'] = $val['diagno_comment'];
			}
			
			return $a_diagno;
		}

		public function check_hs_diagnosis($client, $ipid)
		{
			$abb = "'HS'";
			$dg = new DiagnosisType();
			$dtsarr = $dg->getDiagnosisTypes($client, $abb);
			$darray = array();

			//$dt_arr[] = '99999999';
			foreach($dtsarr as $k_dts_arr => $v_dts_arr)
			{
				if($v_dts_arr['abbrevation'] == 'HS')
				{
					$dt_arr[] = $v_dts_arr['id'];
				}
			}
			if(!empty($dt_arr))
			{
				$cust = Doctrine_Query::create()
					->select("*,AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
					->from('PatientDiagnosis')
					->where('ipid = ?', $ipid)
					->andWhereIn('diagnosis_type_id', $dt_arr)
					->orderBy('id ASC')
					->limit('1');
				$darray = $cust->fetchArray();
			}
			
			if($darray)
			{
				return true;
			}
			else
			{
				return false;
			}			
		}
		
		public function get_main_diagnosis($ipid, $clientid)
		{
			$dg = new DiagnosisType();
			$abb2 = "'HD'";
			$ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
			$comma = ",";
			$typeid = "'0'";
			foreach($ddarr2 as $key => $valdia)
			{
				$typeid .=$comma . "'" . $valdia['id'] . "'";
				$comma = ",";
			}
			$dianoarray = self::getFinalData($ipid, $typeid);

			$patientmeta = new PatientDiagnosisMeta();
			$metaids = $patientmeta->getPatientDiagnosismeta($ipid);

			if(count($metaids) > 0)
			{
				$diagnosismeta = new DiagnosisMeta();
				$comma = ",";
				$metadiagnosis = "";
				foreach($metaids as $keymeta => $valmeta)
				{
					$metaarray = $diagnosismeta->getDiagnosisMetaDataById($valmeta['metaid']);

					foreach($metaarray as $keytit => $metatitle)
					{
						$metadiagnosis .= $comma . $metatitle['meta_title'];
						$comma = ",";
					}
				}
			}

			if(count($dianoarray) > 0)
			{
				$diagnosis = array();
				foreach($dianoarray as $key => $valdia)
				{
					if(strlen($valdia['diagnosis']) > 0)
					{
						$diagnosis['all_str'][]= $valdia['diagnosis'] . ' (' . $valdia['icdnumber'] . ')';
						$diagnosis['diagnosis'][] = $valdia['diagnosis'];
						$diagnosis['icd'][] = $valdia['icdnumber'];
					}
				}
			}

			if(count($diagnosis) > 0 || strlen($metadiagnosis) > 0)
			{
				return $diagnosis;
			}
			else
			{
				return false;
			}
		}
		
		public function get_side_diagnosis($ipid, $clientid)
		{
			$dg = new DiagnosisType();
			$abb = "'ND','AD','DD'";
			$dg = new DiagnosisType();
			$ddarr = $dg->getDiagnosisTypes($clientid, $abb);
			
			if(!$ddarr[0]['id'])
			{
				$ddarr[0]['id'] = 0;
			}
			$comma = "";
			$other_diagnosis = array();
			foreach($ddarr as $key1 => $val1)
			{
				$stam_diagno = array();
				$stam_diagno = self::getFinalData($ipid, $val1['id']);

				$dia = 1;
				foreach($stam_diagno as $key => $val)
				{
					if(strlen($val['diagnosis']) > 0)
					{
						if(strlen($val['icdnumber']) > 0)
						{
							$other_diagnosis['all_str'][] = $val['diagnosis'] . ' (' . $val['icdnumber'] . ')';
						}
						else
						{
							$other_diagnosis['all_str'][] = $val['diagnosis'];
						}
						
						$other_diagnosis['diagnosis'][] = $val['diagnosis'];
						$other_diagnosis['icd'][] = $val['icdnumber'];
						$dia++;
					}
				}
			}
			
			if($other_diagnosis)
			{
				return $other_diagnosis;
			}
			else
			{
				return false;
			}
		}

		/**
		 * 
		 * Sent by Nico - Added by Ancuta 23.08.2017 
		 * 
		 * @param unknown $ipid
		 * @param unknown $to_db
		 * @return multitype:multitype:unknown Ambigous <> Ambigous <Ambigous <>> NULL
		 */
		
		
		public static function get_exportdata($ipid, $to_db){
			$logininfo= new Zend_Session_Namespace('Login_Info');
			$clientid = $logininfo->clientid;
		
			$dt=new DiagnosisType();
			$types=$dt->getDiagnosisTypes($clientid, "'hd', 'HD', 'nd', 'ND'");
			$type_ids=array();
			$type_id_to_type=array();
		
			foreach ($types as $type){
				$type_ids[]="'" . $type['id'] . "'";
				$type_id_to_type[$type['id']]=$type['abbrevation'];
			}
		
			$typeids_str=implode(',',$type_ids);
			$pd=new PatientDiagnosis();
			$diags=$pd->getFinalData($ipid, $typeids_str);
		
		
			$cust_meta = Doctrine_Query::create()
			->select("*")
			->from('PatientDiagnosisMeta')
			->where('ipid = "' . $ipid . '"')
			->orderBy('id ASC');
			$meta_diag = $cust_meta->fetchArray();
		
			$diag_to_metas=array();
		
			$metamod=new DiagnosisMeta();
		
			foreach ($meta_diag as $meta) {
				$mymeta = $metamod->getDiagnosisMetaDataById($meta['metaid']);
				if (count($mymeta) > 0) {
					$mymeta = $mymeta[0];
				}
				$diag_to_metas[$meta['diagnoid']][]=$mymeta['meta_title'];
			}
		
			$outarr=array();
			foreach ($diags as $diag){
				$outarr[]=array(
						'type'=>$type_id_to_type[$diag['diagnosis_type_id']],
						'diagnosis'=>$diag['diagnosis'],
						'icdnumber'=>$diag['icdnumber'],
						'metas'=>$diag_to_metas[$diag['pdid']]
		
				);
			}
		
			$act = Doctrine::getTable('PatientDiagnosisAct')->findOneByIpidAndIsdelete($ipid, 0);
			$act_val=null;
			if($act)
			{
				$act_val=$act->act;
			}
		
		
			if(count($outarr)>0 && $to_db){
				SystemsSyncPackets::createPacket($ipid, array('diags'=>$outarr, 'act'=>$act_val, 'date'=>date('d.m.Y')), "diag", 1);
			}
		
			return $outarr;
		}
		
		
		
		

    /**
     * get the patient disgnosis, grouped by ipid
     * used first time in wlassessmentAction @claudiu 04.01.2018
     * 
     * @param array|string $ipids
     * @return array
     */
	public function getAllDiagnosis($ipids = array())
	{
        $result = array();
	    
	    if (empty($ipids)) {
	        return $result;
	    }
	    
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    
	    
	    $pat_diag_arr = $this->getTable()->createQuery('p')
	    ->select("p.*, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
	    ->leftJoin('p.PatientDiagnosisParticipants pdp')
	    ->addSelect('pdp.*')
	    ->whereIn('ipid', $ipids)
	    ->orderBy('id ASC')
	    ->fetchArray();
	    
	    
	    if ( ! empty($pat_diag_arr)) {
	        
	        array_walk($pat_diag_arr, function(&$pat_diag){
	            if (empty($pat_diag['diagnosis_id'])) { $pat_diag['diagnosis_id'] = '0'; }
	        });
	        
	        $diagnosis_ids = array_values(array_unique(
	            array_merge(
	               array_column($pat_diag_arr, 'icd_id'), 
	               array_column($pat_diag_arr, 'diagnosis_id')
                )
            ));
	        
	        $pat_diag_arr_freetext = array_filter($pat_diag_arr, function($row) {
	            return $row['tabname'] == 'diagnosis_freetext';
	        });
            $pat_diag_arr_icd = array_filter($pat_diag_arr, function($row) {
                return $row['tabname'] == 'diagnosis_icd';
            });
            $pat_diag_arr_diagnosis = array_filter($pat_diag_arr, function($row) {
                return $row['tabname'] == 'diagnosis';
            });
            
            $Diagnosis = array();
            if ( ! empty($diagnosis_ids)) {
                $Diagnosis = Doctrine_Query::create()
                ->select('*')
                ->from('Diagnosis INDEXBY id')
                ->whereIn("id", $diagnosis_ids)
                ->fetchArray();
            }
            
	        $DiagnosisText = array();
	        if ( ! empty($pat_diag_arr_freetext)) {
	            $diagnosis_ids = array_column($pat_diag_arr_freetext, 'diagnosis_id');
	            $DiagnosisText = Doctrine_Query::create()
	            ->select('*')
	            ->from('DiagnosisText INDEXBY id')
	            ->whereIn('id', $diagnosis_ids)
	            ->fetchArray();
	        }
	        
	        $DiagnosisIcd = array();
	        if ( ! empty($pat_diag_arr_icd)) {
	            $diagnosis_ids = array_column($pat_diag_arr_icd, 'diagnosis_id');
	            $DiagnosisIcd = Doctrine_Query::create()
	            ->select('*')
	            ->from('DiagnosisIcd INDEXBY id')
	            ->whereIn('id', $diagnosis_ids)
	            ->fetchArray();
	        }
	        
	        
	        foreach ($pat_diag_arr as &$pat_diag) {
	            
	           $pat_diag['icd_primary'] = $Diagnosis [$pat_diag['icd_id']] ['icd_primary'];
	           
	           switch ($pat_diag['tabname']) {
	               
	               case "diagnosis":
	                   $pat_diag['tabname_html']   = 'dig'; //do not use this!
	                   $pat_diag['description']    = $Diagnosis [$pat_diag['diagnosis_id']] ['description'];
	                   $pat_diag['icd_primary']    = $Diagnosis [$pat_diag['diagnosis_id']] ['icd_primary'];
                   break;
	                   
	               case "diagnosis_freetext":
	                   $pat_diag['tabname_html']   = 'text';//do not use this!
	                   $pat_diag['description']    = $DiagnosisText [$pat_diag['diagnosis_id']] ['free_name'];
	                   $pat_diag['diagno_comment'] = $DiagnosisText [$pat_diag['diagnosis_id']] ['free_desc'];
	                   $pat_diag['icd_primary']    = $DiagnosisText [$pat_diag['diagnosis_id']] ['icd_primary'];
                   break;
	                   
	               case "diagnosis_icd":
	                   $pat_diag['tabname_html']   = 'diagnosis_icd';//do not use this!
	                   $pat_diag['description']    = $DiagnosisIcd [$pat_diag['diagnosis_id']] ['description'];
	                   $pat_diag['icd_primary']    = $DiagnosisIcd [$pat_diag['diagnosis_id']] ['icd_primary']; 
                   break;
	                   
	           }
	        }
	       
	        //result is grouped by ipid 
	        foreach ($pat_diag_arr as $row) {
	            $result[$row['ipid']][$row['id']] = $row;
	            
	        }
	    }
	
// 	    foreach($darray as $key => $val)
// 	    {
// 	        $a_diagno[$key]['ipid'] = $val['ipid'];
// 	        $a_diagno[$key]['icdnumber'] = trim($val['icd_primary']);
// 	        $a_diagno[$key]['diagnosis'] = $val['description'];
// 	        $a_diagno[$key]['hidd_diagnosis'] = $val['diagnosis_id'];
// 	        $a_diagno[$key]['hidd_icdnumber'] = $val['icd_id'];
// 	        $a_diagno[$key]['diagnosis_type_id'] = $val['diagnosis_type_id'];
// 	        $a_diagno[$key]['pdid'] = $val['id'];
// 	        $a_diagno[$key]['create_date'] = $val['create_date'];
// 	        $a_diagno[$key]['tabname'] = $val['tabname'];
// 	        $a_diagno[$key]['diagno_comment'] = $val['diagno_comment'];
// 	    }

        return $result;
	}
	
	/**
	 * ISPC-2654 Ancuta 07.10.2020
	 * Copy of getAllDiagnosis
	 * @param array $ipids
	 * @return array|array|Doctrine_Collection
	 */
	public function getAllDiagnosisClinical($ipids = array(),$clientid, $filter_data = array(),$row_id = 0)
	{
        $result = array();
	    
	    if (empty($ipids)) {
	        return $result;
	    }
	    $dg = new DiagnosisType();
	    $abb2 = "'HD','ND'";
	    $ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
	    
	    $map_oldTypes2newCategory = array();
	    $map_new2OLDCategory = array();
	    foreach($ddarr2 as $key => $valdia)
	    {
	        if($valdia['abbrevation'] == 'HD'){
	            $map_oldTypes2newCategory[$valdia['id']] =  'main_diagnosis';
	            $map_new2OLDCategory['main_diagnosis'] =  $valdia['id']; // ?????? ?? ? ? ? ? ?  ?
	        } elseif($valdia['abbrevation'] == 'ND'){
	            $map_oldTypes2newCategory[$valdia['id']] =  'secondary_disease'; // ?????? ?? ? ? ? ? ?  ?
	            $map_new2OLDCategory['secondary_disease'] =  $valdia['id']; // ?????? ?? ? ? ? ? ?  ?
	        }
	        
	    }
	    
	    $ipids = is_array($ipids) ? $ipids : array($ipids);
	    
	    if(!empty($filter_data)){
	        
	    }

	    $pat_diag_q = $this->getTable()->createQuery('p')
	    ->select("p.*, AES_DECRYPT(tabname,'" . Zend_Registry::get('salt') . "') as tabname")
	    ->leftJoin('p.PatientDiagnosisParticipants pdp')
	    ->leftJoin('p.PatientDiagnosisClinical pdc')
	    ->addSelect('pdp.*')
	    ->addSelect('pdc.*')
	    ->whereIn('p.ipid', $ipids);
	    if(!empty($row_id)){
	        $pat_diag_q->andWhere('p.id =?', $row_id);
	    }
	    
	    $main_mapping = array();
	    if(!empty($filter_data['main_category'])){
	        $main_str = "";
	        foreach($filter_data['main_category'] as $main_f){
	            $main_str .= '"'.$main_f.'",';
	            if(isset($map_new2OLDCategory[$main_f])){
	                $main_mapping[] = $map_new2OLDCategory[$main_f];
	           }
	        }
	        $main_str = substr($main_str, 0, -1);
	        
	        $or_oldsql="";
	        if(!empty($main_mapping)){
    	        foreach($main_mapping as $k=>$main_old_id){
    	            $pat_diag_q->andWhere('p.diagnosis_type_id = ?',$main_old_id);
    	        }
	        }
	        
	        if($filter_data['main_category'][0] == 'primary_disease'){
    	        foreach($filter_data['main_category'] as $lk=>$mc){
    	            $pat_diag_q->andWhere('pdc.main_category =?',$mc);
    	        }
	        }
	        
	    }
	    if(!empty($filter_data['secondary_categories'])){
	        foreach($filter_data['secondary_categories'] as $fcolumn){
	            $pat_diag_q->andWhere('pdc.'.$fcolumn.' =? ','yes');
	        }
	    }
	    
        $pat_diag_q->orderBy('p.custom_order ASC');
//         dd($filter_data['main_category'][0] == 'primary_disease');
	    $pat_diag_arr = $pat_diag_q->fetchArray();

	    
	    
	    if ( ! empty($pat_diag_arr)) {
	        
	        array_walk($pat_diag_arr, function(&$pat_diag){
	            if (empty($pat_diag['diagnosis_id'])) { $pat_diag['diagnosis_id'] = '0'; }
	        });
	        
	        $diagnosis_ids = array_values(array_unique(
	            array_merge(
	               array_column($pat_diag_arr, 'icd_id'), 
	               array_column($pat_diag_arr, 'diagnosis_id')
                )
            ));
	        
	        $pat_diag_arr_freetext = array_filter($pat_diag_arr, function($row) {
	            return $row['tabname'] == 'diagnosis_freetext';
	        });
            $pat_diag_arr_icd = array_filter($pat_diag_arr, function($row) {
                return $row['tabname'] == 'diagnosis_icd';
            });
            $pat_diag_arr_diagnosis = array_filter($pat_diag_arr, function($row) {
                return $row['tabname'] == 'diagnosis';
            });
            
            $Diagnosis = array();
            if ( ! empty($diagnosis_ids)) {
                $Diagnosis = Doctrine_Query::create()
                ->select('*')
                ->from('Diagnosis INDEXBY id')
                ->whereIn("id", $diagnosis_ids)
                ->fetchArray();
            }
            
	        $DiagnosisText = array();
	        if ( ! empty($pat_diag_arr_freetext)) {
	            $diagnosis_ids = array_column($pat_diag_arr_freetext, 'diagnosis_id');
	            $DiagnosisText = Doctrine_Query::create()
	            ->select('*')
	            ->from('DiagnosisText INDEXBY id')
	            ->whereIn('id', $diagnosis_ids)
	            ->fetchArray();
	        }
	        
	        $DiagnosisIcd = array();
	        if ( ! empty($pat_diag_arr_icd)) {
	            $diagnosis_ids = array_column($pat_diag_arr_icd, 'diagnosis_id');
	            $DiagnosisIcd = Doctrine_Query::create()
	            ->select('*')
	            ->from('DiagnosisIcd INDEXBY id')
	            ->whereIn('id', $diagnosis_ids)
	            ->fetchArray();
	        }
	        
	        
	        foreach ($pat_diag_arr as &$pat_diag) {
	            
	           $pat_diag['icd_primary'] = $Diagnosis [$pat_diag['icd_id']] ['icd_primary'];

	           switch ($pat_diag['tabname']) {
	               
	               case "diagnosis":
	                   $pat_diag['tabname_html']   = 'dig'; //do not use this!
	                   $pat_diag['description']    = $Diagnosis [$pat_diag['diagnosis_id']] ['description'];
	                   $pat_diag['icd_primary']    = $Diagnosis [$pat_diag['diagnosis_id']] ['icd_primary'];
                   break;
	                   
	               case "diagnosis_freetext":
	                   $pat_diag['tabname_html']   = 'text';//do not use this!
	                   $pat_diag['description']    = $DiagnosisText [$pat_diag['diagnosis_id']] ['free_name'];
	                   $pat_diag['diagno_comment'] = $DiagnosisText [$pat_diag['diagnosis_id']] ['free_desc'];
	                   $pat_diag['icd_primary']    = $DiagnosisText [$pat_diag['diagnosis_id']] ['icd_primary'];
                   break;
	                   
	               case "diagnosis_icd":
	                   $pat_diag['tabname_html']   = 'diagnosis_icd';//do not use this!
	                   $pat_diag['description']    = $DiagnosisIcd [$pat_diag['diagnosis_id']] ['description'];
	                   $pat_diag['icd_primary']    = $DiagnosisIcd [$pat_diag['diagnosis_id']] ['icd_primary']; 
                   break;
	                   
	           }
	           
	           
	           
	           if(empty($pat_diag['PatientDiagnosisClinical']['main_category'])){
	               $pat_diag['PatientDiagnosisClinical']['main_category']   = $map_oldTypes2newCategory[$pat_diag['diagnosis_type_id']];
	           }
	           if(empty($pat_diag['PatientDiagnosisClinical']['start_date'])){
	               $pat_diag['PatientDiagnosisClinical']['start_date']   = $pat_diag['create_date'];
	           }
	           if(empty($pat_diag['PatientDiagnosisClinical']['icd_code'])){
	               $pat_diag['PatientDiagnosisClinical']['icd_code']   = $pat_diag['icd_primary'];
	           }
	           if(empty($pat_diag['PatientDiagnosisClinical']['icd_description'])){
	               $pat_diag['PatientDiagnosisClinical']['icd_description']   = $pat_diag['description'];
	           }
	           
	           if(empty($pat_diag['PatientDiagnosisClinical']['icd_comment'])){
	               $pat_diag['PatientDiagnosisClinical']['icd_comment'] = $pat_diag['comments'];
	           }
	        }
	       
	        //result is grouped by ipid 
	        foreach ($pat_diag_arr as $row) {
	            $result[$row['ipid']][$row['id']] = $row;
	            
	        }
	    }
	    
   	    return $result;
	   
	}
	

    /**
     * get HD for ipids and client
     * IM-59 //Maria:: Migration CISPC to ISPC 22.07.2020
     *
     * @param $active_ipids
     * @param $clientid
     * @return array
     */
	public static function get_multiple_patients_main_diagnosis($active_ipids, $clientid){
        // IM-59 - elena
        //get all patients diagnosis ISPC-1169
        //Get diagnosis type
        $dg = new DiagnosisType();
        $abbr = "'HD'";
        $abbr_arr = $dg->getDiagnosisTypes($clientid, $abbr);

        $comma = ",";
        $typeid = "'0'";
        $typeids[] = '999999999999';
        foreach($abbr_arr as $key => $valdia)
        {
            $typeids = $valdia['id'];
            $typeid .=$comma . "'" . $valdia['id'] . "'";
            $comma = ", ";
        }

        $patdia = new PatientDiagnosis();
//			$patients_diagnosis_arr = $patdia->getFinalData('"' . implode('","', $active_ipids) . '"', $typeid, true);
        $patients_diagnosis_arr = $patdia->get_multiple_patients_diagnosis($active_ipids, $typeids);

        $patients_diagnosis=array();

        foreach($patients_diagnosis_arr as $k_diag => $v_diag)
        {
            $temp_diag_parts = array();
            $temp_diag = '';
            if(strlen(trim(rtrim($v_diag['icdnumber']))) > '0')
            {
                $temp_diag_parts[] = trim(rtrim($v_diag['icdnumber']));
            }

            if(strlen(trim(rtrim($v_diag['diagnosis']))) > '0')
            {
                $temp_diag_parts[] = trim(rtrim($v_diag['diagnosis']));
            }

            if(!empty($temp_diag_parts))
            {
                $temp_diag = implode(' - ', $temp_diag_parts);

                $patients_diagnosis[$v_diag['ipid']][] = $temp_diag;
            }
        }

        return $patients_diagnosis;
    }




	// Maria:: Migration ISPC to CISPC 08.08.2020
	public function get_ipids_main_diagnosis($ipids, $clientid)
	{
		$dg = new DiagnosisType();
		$abb2 = "'HD'";
		$ddarr2 = $dg->getDiagnosisTypes($clientid, $abb2);
		$comma = ",";
		$typeid = "'0'";
		$ipids_list ="";
		foreach($ddarr2 as $key => $valdia)
		{
			$typeid .=$comma . "'" . $valdia['id'] . "'";
			$comma = ",";
		}
	
		$comma ="";
		foreach($ipids as $key => $val)
		{
			$ipids_list .=$comma . "'" . $val . "'";
			$comma = ",";
		}
	
		$dianoarray = self::getFinalData($ipids_list, $typeid, true);
	
		$patientmeta = new PatientDiagnosisMeta();
		$metaids = $patientmeta->get_multiple($ipids);
	
		$metadiagnosis = array();
		if($metaids)
		{
			$diagnosismeta = new DiagnosisMeta();
			//$comma = "";
			//$metadiagnosis = "";
			foreach($metaids as $keymeta => $valmeta)
			{
				$metaarray = $diagnosismeta->getDiagnosisMetaDataById($valmeta['metaid']);
	
				foreach($metaarray as $keytit => $metatitle)
				{
					//$metadiagnosis .= $comma . $metatitle['meta_title'];
					if($metadiagnosis[$valmeta['ipid']])
					{
						$metadiagnosis[$valmeta['ipid']] .= $comma . $metatitle['meta_title'];
					}
					else
					{
						$comma = "";
						$metadiagnosis[$valmeta['ipid']] = $comma . $metatitle['meta_title'];
						$comma = ",";
					}
				}
			}
		}
	
		if(count($dianoarray) > 0)
		{
			$diagnosis = array();
			foreach($dianoarray as $key=> $valdia)
			{
				if(strlen($valdia['diagnosis']) > 0)
				{
					$diagnosis[$valdia['ipid']]['all_str'][]= $valdia['diagnosis'] . ' (' . $valdia['icdnumber'] . ')';
					$diagnosis[$valdia['ipid']]['diagnosis'][] = $valdia['diagnosis'];
					$diagnosis[$valdia['ipid']]['icd'][] = $valdia['icdnumber'];
				}

				$diagnosis[$valdia['ipid']]['metadiagnosis'] = $metadiagnosis[$valdia['ipid']];
			}
		}
	
		if(count($diagnosis) > 0 || strlen($metadiagnosis) > 0)
		{
			return $diagnosis;
		}
		else
		{
			return false;
		}
	}

	/**
	* ISPC-2809 Ancuta 11.02.2021
	* Code from Nico
	*/   	
	public static function get_multiple_patients_hd($ipids, $icd=0){
	    $logininfo= new Zend_Session_Namespace('Login_Info');
	    $clientid = $logininfo->clientid;
	    
	    $dt=new DiagnosisType();
	    $types=$dt->getDiagnosisTypes($clientid, "'hd', 'HD'");
	    $type_id=$types[0]['id'];
	    
	    $diags=PatientDiagnosis::get_multiple_patients_diagnosis($ipids, $type_id);
	    
	    $ipid_to_diag=array();
	    foreach ($diags as $diag){
	        if($icd){
	            $ipid_to_diag[$diag['ipid']][] = $diag;
	        }else {
	            $ipid_to_diag[$diag['ipid']][] = $diag['diagnosis'];
	        }
	    }
	    return $ipid_to_diag;
	}


    //Find out if diagnosis is onkological
    //ICDs C00-D48 are onko
    //TODO-4163
    public static function is_onko($icd_primary){
        $onkononko="";
        if(strlen($icd_primary)){
            $onkononko=0;
            if(substr($icd_primary,0,1)=="C"){
                $onkononko=1;
            }else{
                if(substr($icd_primary,0,1)=="D" && strlen($icd_primary>2)){
                    $no=substr($icd_primary,1,2);
                    $no=intval($no);
                    if($no<49){
                        $onkononko=1;
                    }
                }
            }
        }
        return $onkononko;
    }
}

?>