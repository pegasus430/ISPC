<?php

	abstract class BaseGroupSecrecyVisibility extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('group_secrecy_visibility');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('master_group_id', 'integer', 5, array('type' => 'integer', 'length' => 5));
			$this->hasColumn('groupid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('create_user', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			$this->hasColumn('create_date', 'timestamp', null, array(
			    'type' => 'timestamp',
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			$this->hasColumn('change_user', 'integer', 4, array(
			    'type' => 'integer',
			    'length' => 4,
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
			$this->hasColumn('change_date', 'timestamp', null, array(
			    'type' => 'timestamp',
			    'fixed' => false,
			    'unsigned' => false,
			    'primary' => false,
			    'notnull' => false,
			    'autoincrement' => false,
			));
		}
		
		
		public function setUp()
		{
		    parent::setUp();
		    /*
		     *  auto-added by builder
		     */
		    
		    /*
		     *  auto-added by builder
		     */
		    $this->actAs(new Timestamp());
		}

	}

?>