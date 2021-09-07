<?php

	abstract class BaseFbFormClients extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('fb_formclients');
			$this->hasColumn('id', 'integer', 8, array('type' => 'integer', 'length' => 8, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('formid', 'integer', 8, array('type' => 'integer', 'length' => 8));
			$this->hasColumn('clientid', 'integer', 8, array('type' => 'integer', 'length' => 8));
		}

		function setUp()
		{
			$this->actAs(new Timestamp());
			$this->hasOne('FbForms', array(
				'local' => 'formid',
				'foreign' => 'id'
			));
		}

	}

?>