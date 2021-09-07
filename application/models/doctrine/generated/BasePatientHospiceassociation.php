<?php

	abstract class BasePatientHospiceassociation extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_hospice_association');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('h_association_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('h_association_comment', 'string', NULL, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->actAs(new Softdelete());
		    
			$this->hasOne('Hospiceassociation', array(
			    'local'      => 'h_association_id',
			    'foreign'    => 'id',
			    'owningSide' => true,
			    'cascade'    => array('delete'),
			));

			$this->actAs(new Timestamp());
			
			// ISPC-2614 Ancuta 20.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			)), "IntenseConnectionListener");
			//
			
		}

	}

?>