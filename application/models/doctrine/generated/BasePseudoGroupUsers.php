<?php

	abstract class BasePseudoGroupUsers extends Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('pseudogroup_users');
			$this->hasColumn('id', 'integer', 11, array('type' => 'integer', 'length' => 11, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('clientid', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('pseudo_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('user_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('isdelete', 'integer', 11, array('type' => 'integer', 'length' => 11));
			
			$this->index('id', array(
					'fields' => array('id'),
					'primary' => true
			));
			
			$this->index('clientid', array(
					'fields' => array('clientid')
			));
			
			$this->index('pseudo_id', array(
					'fields' => array('pseudo_id')
			));
			
		}

		function setUp()
		{
		    $this->hasOne('UserPseudoGroup', array(
		        'local' => 'pseudo_id',
		        'foreign' => 'id'
		    ));
		    
			$this->actAs(new Timestamp());
		}

	}

?>