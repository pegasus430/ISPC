<?php

	class LocationInsert extends Doctrine_Template {

		public function setTableDefinition()
		{
			$this->addListener(new InsertListener());
		}

	}

?>
