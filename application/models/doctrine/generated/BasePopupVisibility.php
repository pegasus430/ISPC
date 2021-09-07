<?php

	abstract class BasePopupVisibility extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('popup_visibility');

			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('popup', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('userid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('clientid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('newsid', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			//$this->actAs(new Createtimestamp());
			//ISPC - 2300
			$this->actAs(new Softdelete());
			
			$this->actAs(new Timestamp());
		}

	}

?>