<?php

	class PatientFile2tag extends Doctrine_Template {

		public function setTableDefinition()
		{
			$this->addListener(new PatientFile2tagListener());
		}

	}

?>
