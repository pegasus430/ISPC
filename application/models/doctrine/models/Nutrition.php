<?php

	Doctrine_Manager::getInstance()->bindComponent('Nutrition', 'SYSDAT');

	class Nutrition extends BaseNutrition{
		
		private static $mandatory_columns = array(
				'clientid',
				'name',
		);

		public static function getMedicationNutritionById($mid)
		{
			if (empty($mid)) {
				return array();
			}
			if(!is_array($mid))
			{
				$mid = array($mid);
			}

			$medicarr = Doctrine_Query::create()
				->select('*')
				->from('Nutrition')
				->whereIn("id", $mid)
				->fetchArray();
			
			return $medicarr;
		}

		public function master_medications_nutrition_get($ids = array(), $remove_delete = true)
		{
		    if (empty($ids) || ! is_array($ids)) {
		        return false;
		    }
			$medication_ids = array_values(array_unique(array_map('intval', $ids)));
			
			$medic = Doctrine_Query::create()
				->select('id, name')
				->from('Nutrition')
				->whereIn("id", $medication_ids);
			if($remove_delete)
			{
				$medic->andWhere('isdelete = "0"');
			}
			
			$medics = $medic->fetchArray();

			if($medics)
			{
			    $medications =  array();
			    
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
			$master_medi = $this->getMedicationNutritionById($mid);
			
			if($master_medi)
			{
				foreach($master_medi as $medi)
				{
					$ins_medi = new Nutrition();
					$ins_medi->clientid = $target_client;
					$ins_medi->name = $medi['name'];
					$ins_medi->pzn = $medi['pzn'];
					$ins_medi->description = $medi['description'];
					$ins_medi->package_size = $medi['package_size'];
					$ins_medi->amount_unit = $medi['amount_unit'];
					$ins_medi->price = $medi['price'];
					$ins_medi->manufacturer = $medi['manufacturer'];
					$ins_medi->package_amount = $medi['package_amount'];
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