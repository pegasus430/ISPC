<?php

	Doctrine_Manager::getInstance()->bindComponent('Diagnosis', 'SYSDAT');

	class Diagnosis extends BaseDiagnosis {

		public function getDiagnosisData($ipval)
		{
			$drugs = Doctrine_Query::create()
				->select('*')
				->from('Diagnosis')
				->where("id in (" . $ipval . ")");
			$dr = $drugs->execute();

			if($dr)
			{
				$drugsarray = $dr->toArray();
				return $drugsarray;
			}
		}

		public function clone_record($did, $target_client, $tabname)
		{
			if($did)
			{
				switch($tabname)
				{
					case 'diagnosis':
						$diagnosis_data = $this->getDiagnosisData($did);
						$icd_primary = $diagnosis_data[0]['icd_primary'];
						$name = $diagnosis_data[0]['description'];
						$diagnosis_data['tabname'] = $tabname;
						break;

					case 'diagnosis_icd':
						$diag_icd = new DiagnosisIcd();
						$diagnosis_data = $diag_icd->getDiagnosisDataById($did);

						$icd_primary = $diagnosis_data[0]['icd_primary'];
						$name = $diagnosis_data[0]['description'];
						$diagnosis_data['tabname'] = $tabname;

						break;

					case 'diagnosis_freetext':
						$diag_txt = new DiagnosisText();
						$diagnosis_data = $diag_txt->getDiagnosisTextData($did);

						$icd_primary = $diagnosis_data[0]['icd_primary'];
						$name = $diagnosis_data[0]['free_name'];
						$description = $diagnosis_data[0]['free_desc'];

						$diagnosis_data['tabname'] = $tabname;
						break;

					default:
						$diagnosis_data = $this->getDiagnosisData($did);
						$icd_primary = $diagnosis_data[0]['icd_primary'];
						$name = $diagnosis_data[0]['description'];
						$diagnosis_data['tabname'] = $tabname;
						break;
				}
			}

			$diag_txt = new DiagnosisText();
			$diag_txt->clientid = $target_client;
			$diag_txt->sys_id = '0';
			$diag_txt->icd_primary = $icd_primary;
			$diag_txt->free_name = $name;
			$diag_txt->free_desc = $description;
			$diag_txt->save();

			return $diag_txt->id;
		}

	}

?>