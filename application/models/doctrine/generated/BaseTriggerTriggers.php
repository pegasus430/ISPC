<?php

	abstract class BaseTriggerTriggers extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('trigger_triggers');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('triggername', 'string', 255, array('type' => 'string', 'length' => 255));
		}

	}

?>