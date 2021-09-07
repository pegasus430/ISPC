<?php

	Doctrine_Manager::getInstance()->bindComponent('MedicationIndex', 'SYSDAT');

	class MedicationIndex extends BaseMedicationIndex {

		public function getIndexMedicationById($imid)
		{
			$medic = Doctrine_Query::create()
				->select('*')
				->from('MedicationIndex')
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