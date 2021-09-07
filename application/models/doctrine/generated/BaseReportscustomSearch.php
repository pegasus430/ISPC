<?php

	abstract class BaseReportscustomSearch extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('cr_search_criterias');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('search', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('description', 'text', NULL, array('type' => 'varchar', 'length' => NULL));
			$this->hasColumn('search_options', 'enum', NULL, array('type' => 'enum', 'notnull' => false, 'values' => array('none','hi_live','icd_live','dm_select','sapv_status_select','sapv_type_select','plain_text')));
			$this->hasColumn('type', 'enum', NULL, array('type' => 'enum', 'notnull' => false, 'values' => array('patient', 'invoice', 'user', 'teammeeting','member','voluntary_worker')));
			$this->hasColumn('db_name', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('table_name', 'varchar', 255, array('type' => 'varchar', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->actAs(new Timestamp());
		}

	}

?>