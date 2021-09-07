<?php

	class Trigger extends Doctrine_Template {

		public function setTableDefinition()
		{
			$this->addListener(new TriggerListener());
		}

	}

?>
