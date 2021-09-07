<?php

Doctrine_Manager::getInstance()->bindComponent('Medication', 'SYSDAT');

class Medication extends BaseMedication 
{

		/**
		 * 
		 * @var methodsarr is used in the BTM
		 * 
		 * methodid = 0 in medication_client_stock, is when you insert a new medicationid
		 * methodid = 0 in medication_patient_history, is when you insert a new medicationid to the ipid
		 */
		
		protected static $methodsarr = array(
				"0" => "Neu",
				//methodid = 0 in medication_client_stock, is when you insert a new medicationid
				//methodid = 0 in medication_patient_history, is when you insert a new medicationid to the ipid
				
				"1" => "Übergabe von ... an",
				//plus button dialog
				 
				//take from group and add to the userid
				//medication_client_stock : negative amount + userid <=> group transfers from his stock to userid stock
				//medication_client_history : positive amount + stdi(stock_id) from the transfer, userid amount increased
				
				//take from user and add to the group
				//medication_client_stock : positive amount + userid <=> userid transfers from his stock back to group stock
				//medication_client_history : negative amount + stdi(stock_id) from the transfer, userid amount decreased
				
				//take from user and add to user(add to by taking from)
				//medication_client_history : negative/positive amount + self_id (self_id ties the 2)
				
				
				"2" => "Lieferung",
				//medication_client_stock : positive amount <=> master add to the group stock this amount - do work only in this table
				//medication_client_history : positive amount <=> master add to this userid this amount  - do work only in this table, group amount is not affected
				
				"3" => "Sonstiges", 
				//plus button dialog
				//medication_client_stock : positive amount <=> master add to the group stock this amount (from )  - do work only in this table
								
				"4" => "Übergabe an Benutzer",
				//minus button dialog
				
				//take from group and add to the userid
				//medication_client_stock : negative amount + userid <=> group transfers from his stock to userid stock
				//medication_client_history : positive amount + stdi(stock_id) from the transfer, userid amount increased
				
				//take from user and add to the group
				//medication_client_stock : positive amount + userid <=> group transfers from userid stock back to group stock, group amount increased 
				//medication_client_history : negative amount + stdi(stock_id) from the transfer, userid amount decreased
				
				
				//take from user and add to user(take from and add to)
				//medication_client_history : positive/negative amount + self_id (self_id ties the 2)
				
				"5" => "Abgabe an Patienten",
				//minus button dialog
				//take from userid and add to the ipid
				//medication_client_history : negative amount + ipid + patient_stock_id, ipid amount increased, userid stock decreased
				//medication_patient_history : positive amount + ipid, ipid amount increased, userid stock decreased
				
				
				
				"6" => "Sonstiges", 
				//minus dialog
				//medication_client_stock: negative amount <=> master substract from group (blackhole amount)  - do work only in this table
				
				//medication_client_history: negative amount <=> master substract from userid (blackhole amount)- do work only in this table
				
				"7" => "Abgabe an Patienten",
				//patient btm icon dialog , BTM Zugang >> Übergabe
				//get from userid and add to ipid
				//medication_client_history : negative amount + ipid + patient_stock_id, ipid amount increased, userid stock decreased
				//medication_patient_history : positive amount + ipid, ipid amount increased, userid stock decreased
					
				
				
				"8" => "Verbrauch",
				//patient btm icon dialog , BTM Abgabe >> Verbrauch				
				
				//source = p patient takes drub from his own stock
				//medication_patient_history : negative amount + ipid, ipid amount decreased -  only this table
				
				//source = u patiend takes the drug from user stock
				//medication_client_history : negative amount + ipid + patient_stock_id, ipid amount increased, userid stock decreased
				//medication_patient_history : positive amount + ipid, ipid amount increased, userid stock decreased
				// + 1 second entry in same table : negative amount, ipid consumed the drug
							
				
				
				"9" => "Rückgabe an Benutzer",
				//patient btm icon dialog , BTM Abgabe >>  Rückgabe an Benutzer  transfer from patient to user
				//medication_patient_history : negative amount + ipid, ipid amount decresase, userid stock increase
				//medication_client_history : positive amount + ipid + patient_stock_id, ipid amount decrease, userid stock increased				
				
				
				'10' => 'Lieferung', //'BTM Zugang >> Lieferung',
				//patient btm icon dialog , BTM Zugang >> Lieferung
				//btm buch plus button dialog
				//medication_patient_history : positive amount <=> master add to this ipid this amount  - do work only in this table(no foreign key)
				
				
				'11' => 'Sonstiges', //BTM_Abgabe >> Sonstiges
				//BTM Abgabe >> Sonstiges
				//medication_patient_history : negative amount + ipid, ipid amount decreased - only this table
								
				
				'12' => 'Rücknahme von Patienten', 
				//plus button dialog
				//transfer from patient 2 user
				
				'13' => 'Correction Event',
				//introduced on ispc 1864
		);
		

	private static $mandatory_columns = array(
			'clientid',
			'name',
	);
		
		
		public static function get_methodsarr()
		{
			return self::$methodsarr;
		}	
		
		public static function getMedicationById($mid, $indexbyid =  false)
		{
			if (empty($mid)) {
				return array();
			}
			
			if(!is_array($mid))
			{
				$mid = array($mid);
			}
			
			$mid = array_values(array_unique(array_map('intval', $mid)));
			
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->whereIn("id", $mid)
				->fetchArray();
// 			$medics = $medic->execute();

			$result = array();
			
			if ($indexbyid === true) {
				foreach($medic as $row) {
					unset($row['clientid'], $row['create_date'], $row['create_user'], $row['change_date'], $row['change_user'] );//remove info about user
					$result[$row['id']] = $row;
				}
			} else {
// 				$medicarr = $medics->toArray();
				$result = $medic;
			}
			
			return $result;
		}

		public function master_medications_get($ids = array(), $remove_delete = true , $include_Medication = false)
		{
			//this fn returns just the medication-name
			//use getMedicationById to fetch full row
			if ( ! is_array($ids) || empty($ids)) {
				return false;
			}
			
			$medication_ids = array_values(array_unique(array_map('intval', $ids)));
// 			$medication_ids = array_map('intval', $ids);
// 			$medication_ids_str = implode(',',$medication_ids);
			
// 			if(strlen($medication_ids_str) < 1){
// 				$medication_ids_str = '999999999999';
				
// 				return false;
// 			}
			
			$medic = Doctrine_Query::create()
				->select('*') // @claudiu i've changed it back to *
// 				->select('id, name')
				->from('Medication INDEXBY id')
// 				->where("id IN (".$medication_ids_str.")");
				->whereIn("id", $medication_ids);
			if($remove_delete)
			{
				$medic->andWhere('isdelete = "0"');
			}
			$medics = $medic->fetchArray();
			
			if($medics)
			{
				$medications = array();
				foreach($medics as $k_medi => $v_medi)
				{
					$medications[$v_medi['id']] = $v_medi['name'];
				}

				if ($include_Medication) {
					$medications ['Medication'] = $medics; //@claudiu
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
			$master_medi = $this->getMedicationById($mid);
			if($master_medi)
			{
				foreach($master_medi as $medi)
				{
					$ins_medi = new Medication();
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
	 * be aware, the fn name may be misleading - this is how Doctrine works!
	 * this fn will insert new if there is no db-record object in our class... 
	 * if you called second time, or you fetchOne, it will update! 
	 * fn was intended for single record, not collection
	 * @param array $params
	 * @return boolean|number
	 * return $this->id | false if you don't have the mandatory_columns in the params 
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