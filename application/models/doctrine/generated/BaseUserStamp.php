<?php

	abstract class BaseUserStamp extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('user_stamp');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('userid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('stamp_name', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row1', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row2', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row3', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row4', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row5', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row6', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('row7', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('stamp_lanr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('stamp_bsnr', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
			$this->hasColumn('valid_from', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
			$this->hasColumn('valid_till', 'datetime', NULL, array('type' => 'datetime', 'length' => NULL));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>