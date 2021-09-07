<?php

	abstract class BaseFormTypeActions extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('form_types_actions');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('name', 'string', 255, array('type' => 'string', 'length' => 255));
		}

	}

?>