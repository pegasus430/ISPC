<?php

	class PatientInsert extends Doctrine_Template {

		public function setTableDefinition()
		{
			$this->addListener(new PatientInsertListener() , "PatientInsertListener");
		}

	}

?>
