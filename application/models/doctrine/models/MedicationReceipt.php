<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationReceipt', 'SYSDAT');

	class MedicationReceipt extends BaseMedicationReceipt {

		public function getReceiptMedicationById($mid)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('Medication')
				->where("id = '" . $mid . "'");

			$medics = $medic->execute();
			if($medics)
			{
				$medicarr = $medics->toArray();
				return $medicarr;
			}
		}

	}

?>