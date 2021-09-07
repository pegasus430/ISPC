<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationTreatmentCare', 'SYSDAT');

	class MedicationTreatmentCare extends BaseMedicationTreatmentCare {
		
		private static $mandatory_columns = array(
				'clientid',
				'name',
		);

		public static function getMedicationTreatmentCareById($mid)
		{
			if (empty($mid)) {
				return array();
			}
			if(!is_array($mid))
			{
				$mid = array($mid);
			}

			$medic = Doctrine_Query::create()
				->select('*')
				->from('MedicationTreatmentCare')
				->whereIn("id", $mid)
				->fetchArray();

			if ( ! empty($medic))
			{
				return $medic;
			}
		}

		public function master_medications_get($ids, $remove_delete = true)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('MedicationTreatmentCare')
				->whereIn("id", $ids);
			if($remove_delete)
			{
				$medic->andWhere('isdelete = "0"');
			}
			$medics = $medic->fetchArray();

			if($medics)
			{
				foreach($medics as $k_medi => $v_medi)
				{
					$medications[$v_medi['id']] = $v_medi['name'];
				}

				return $medications;
			}
			else
			{
				return false;
			}
		}

		public function clone_record($mid, $target_client)
		{
			$master_medi = $this->getMedicationTreatmentCareById($mid);
			if($master_medi)
			{
				foreach($master_medi as $medi)
				{
					$ins_medi = new MedicationTreatmentCare();
					$ins_medi->clientid = $target_client;
					$ins_medi->name = $medi['name'];
					$ins_medi->extra = '1';
					$ins_medi->isdelete = '0';
					$ins_medi->save();

					return $ins_medi->id;
				}
			}
			else
			{
				return false;
			}
		}


	
		/**
		 * be aware, misleading fn name check docu on other scripts
		 * this was made to insert + update
		 */
		public function set_new_record($params = array())
		{
		
			if (empty($params) || !is_array($params)) {
				return false;// something went wrong
			}
		
			foreach (self::$mandatory_columns as $column) {
				if ( ! isset($params[$column]) || empty($params[$column]) ) {
					return false;
				}
			}
		
			foreach ($params as $k => $v)
				if (isset($this->{$k})) {
		
					$this->{$k} = $v;
		
				}
		
			$this->save();
			return $this->id;
		
		}	
		
		
		
	}

?>