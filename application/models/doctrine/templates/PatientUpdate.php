<?php

	class PatientUpdate extends Doctrine_Template {

		public function setTableDefinition()
		{
			$this->addListener(new PatientUpdateListener());
		}

	}

?>
