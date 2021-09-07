<?php

	abstract class BasePatientSpecialists extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_specialists');
			$this->hasColumn('id', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'integer', 'length' => 255));
			$this->hasColumn('sp_id', 'integer', 11, array('type' => 'integer', 'length' => 11));
			$this->hasColumn('comment', 'text', NULL, array('type' => 'text', 'length' => NUll));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
		    
		    $this->hasOne('Specialists', array(
		        'local' => 'sp_id',
		        'foreign' => 'id',
// 		        'table' => new Specialists(),
// 		        'owningSide'    => true,
		        //'cascade' => array('delete'),
		    ));
		    
		    $this->actAs(new Softdelete());
		    
		    
		    /*
		    'class'       => true,
		    'type'        => true,
		    'deferred'    => null,
		    'deferrable'  => null,
		    'constraint'  => null,
		    'equal'       => false,
		    'refClassRelationAlias' => null,
		    'foreignKeyName' => null,
		    */
		    
		    
			$this->actAs(new Createtimestamp());
			
			//ISPC-2614 Ancuta 19.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			)), "IntenseConnectionListener");
			//
		}

	}

?>