<?php

	abstract class BaseSapfiveImagetags extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('sapv_five_imagetags');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('tagname', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('leftpos', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('toppos', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('boxwidth', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('boxheight', 'string', 255, array('type' => 'string', 'length' => 255));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
		}

	}

?>