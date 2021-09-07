<?php

	abstract class BaseQuestionnaireC extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('questionnaire_c');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row', 'integer', 2, array('type' => 'integer', 'length' => 2));
			$this->hasColumn('value', 'string', null, array('type' => 'integer', 'length' => null));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->actAs(new Createtimestamp());
		}

	}

?>