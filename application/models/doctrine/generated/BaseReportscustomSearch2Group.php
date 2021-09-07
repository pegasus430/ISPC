<?php

	abstract class BaseReportscustomSearch2Group extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('cr_search2group');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('group_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('search_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('negation', 'enum', null, array('type' => 'enum', 'notnull' => false, 'values' => array('0', '1')));
			$this->hasColumn('options', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>
