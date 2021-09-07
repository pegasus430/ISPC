<?php

	abstract class BasePatientSuppliers extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_suppliers');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('supplier_id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('supplier_comment', 'string', NULL, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    $this->actAs(new Softdelete());
		    
			$this->hasOne('Suppliers', array(
			    'local'      => 'supplier_id',
			    'foreign'    => 'id',
			    'owningSide' => true,
			    'cascade'    => array('delete'),
			));

			$this->actAs(new Timestamp());
			
			//ISPC-2614 Ancuta 19.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			)), "IntenseConnectionListener");
			//
		}

	}

?>