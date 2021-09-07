<?php
/**
 * 
 * @author  Dec 10, 2020  Alex
 *
 */
	abstract class BaseTableLog extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('table_log');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('model', 'string', 128, array('type' => 'string', 'length' => 128));
			$this->hasColumn('record_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('old', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('modified', 'text', NULL, array('type' => 'text', 'length' => NULL));
			$this->hasColumn('row', 'text', NULL, array('type' => 'text', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>