<?php

	Doctrine_Manager::getInstance()->bindComponent('DiagnosisType', 'SYSDAT');

	class DiagnosisType extends BaseDiagnosisType {

		public function getDiagnosisTypes($client_id, $abbrs)
		{
			if($client_id)
			{
				$dtype = Doctrine_Query::create()
					->select('*')
					->from('DiagnosisType')
					->where("clientid=?", $client_id)
					->andWhere('abbrevation in(' . $abbrs . ')')
					//RWH 10.07.14 - ISPC-950
					->orderBy('abbrevation ASC');
					//RWH end
				$darr = $dtype->fetchArray();
			}

			if($darr)
			{
				return $darr;
			}
		}
		
		public function get_client_diagnosistypes($client_id)
		{
			if($client_id)
			{
				$dtype = Doctrine_Query::create()
					->select('*')
					->from('DiagnosisType')
					->where("clientid=?", $client_id)
					//RWH 10.07.14 - ISPC-950
					->orderBy('abbrevation ASC');
					//RWH end
				$darr = $dtype->fetchArray();
			}

			if($darr)
			{
				foreach($darr as $k_diag_type => $v_diag_type)
				{
					$diag_types_arr[$v_diag_type['id']] = $v_diag_type;
				}
				return $diag_types_arr;
			}
		}

		public function getDiagnosisTypesById($did)
		{
			$dtype = Doctrine_Query::create()
				->select('*')
				->from('DiagnosisType')
				->where("id=?", $did);

			$dq = $dtype->execute();
			$darr = $dq->toArray();

			if($darr)
			{
				return $darr;
			}
		}

	}

?>