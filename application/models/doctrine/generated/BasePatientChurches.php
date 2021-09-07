<?php

abstract class BasePatientChurches extends Pms_Doctrine_Record {

	function setTableDefinition()
	{
		$this->setTableName('patient_churches');
		$this->hasColumn('id', 'integer', 10, array('type' => 'integer', 'length' => 10, 'primary' => true, 'autoincrement' => true));
		$this->hasColumn('ipid', 'string', 255, array('type' => 'string', 'length' => 255));
		$this->hasColumn('chid', 'integer', 10, array('type' => 'integer', 'length' => 10));		
		$this->hasColumn('isdelete', 'integer', 1, array('type' => 'integer', 'length' => 1));
		//$this->hasColumn('church_comment', 'string', NULL, array('type' => 'string', 'length' => 255));
		$this->hasColumn('church_comment', 'text', NULL, array('type' => 'text', 'length' => NULL, 'nullable'=>true));
	}

	function setUp()
	{
	    $this->actAs(new Softdelete());
	    
		$this->hasOne('Churches', array(
		    'local'       => 'chid',
		    'foreign'     => 'id',
		    'owningSide'  => true,
		    'cascade'     => array('delete'),
		));

		$this->actAs(new Timestamp());
		
		//ISPC-2614 Ancuta 19.07.2020
		$this->addListener(new IntenseConnectionListener(array(
		)), "IntenseConnectionListener");
		//
	}

}

?>