<?php

	abstract class BasePatientHomecare extends Pms_Doctrine_Record {

		function setTableDefinition()
		{
			$this->setTableName('patient_homecare');
			$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
			$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
			$this->hasColumn('homeid', 'bigint', NULL, array('type' => 'bigint', 'length' => NULL));
			$this->hasColumn('home_comment', 'string', NULL, array('type' => 'string', 'length' => 255));
			$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		}

		function setUp()
		{
			$this->hasOne('Homecare', array(
				'local'         => 'homeid',
				'foreign'       => 'id',
			    'owningSide'    => true,
 		        'cascade'       => array('delete'),
			));
			
		    $this->actAs(new Softdelete());

			$this->actAs(new Timestamp());
			
			//ISPC-2614 Ancuta 16.07.2020
			$this->addListener(new IntenseConnectionListener(array(
			)), "IntenseConnectionListener");
			//
		}

	}

?>